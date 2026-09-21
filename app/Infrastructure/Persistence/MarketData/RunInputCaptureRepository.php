<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Immutable materialized producer inputs, not a reconstruction at publication seal. */
class RunInputCaptureRepository
{
    public const VERSION = 'md_producer_capture_v1';
    public const COMPONENTS = ['run_config', 'universe_identity', 'provider_mapping', 'calendar_session',
        'status_expectation', 'source_observations', 'raw_history', 'event_factor',
        'ancillary', 'registry_versions', 'market_structure', 'completion'];

    public static function canonicalJson($value): string
    {
        $normalize = function ($value) use (&$normalize) {
            if (is_object($value)) {
                if (! $value instanceof \stdClass) {
                    throw new \InvalidArgumentException('INPUT_CAPTURE_UNSUPPORTED_OBJECT');
                }
                $value = (array) $value;
            }
            if (is_array($value)) {
                if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) ksort($value, SORT_STRING);
                foreach ($value as $key => $child) $value[$key] = $normalize($child);
            }
            return $value;
        };
        $json = json_encode($normalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if ($json === false) throw new \InvalidArgumentException('INPUT_CAPTURE_SERIALIZATION_FAILED: '.json_last_error_msg());
        return $json;
    }

    public static function slotHash(string $component, array $selection): string
    {
        return hash('sha256', self::canonicalJson(['schema_version' => self::VERSION, 'component_key' => $component, 'selection_context' => $selection]));
    }

    public function capture(int $runId, string $stage, string $component, array $selection, array $rows, ?array $emptyBasis = null, array $audit = []): array
    {
        if ($runId <= 0 || ! preg_match('/^[A-Z][A-Z0-9_]{0,31}$/D', $stage)
            || ! in_array($component, self::COMPONENTS, true)
            || empty($selection['operation']) || ! is_string($selection['operation'])) {
            throw new \InvalidArgumentException('INPUT_CAPTURE_IDENTITY_REQUIRED');
        }
        if ($rows === [] && (empty($emptyBasis['reason']) || empty($emptyBasis['source']) || ! array_key_exists('evaluated_population', $emptyBasis))) {
            throw new \RuntimeException('INPUT_CAPTURE_EMPTY_BASIS_REQUIRED: '.$component);
        }
        if ($rows !== [] && $emptyBasis !== null) throw new \InvalidArgumentException('INPUT_CAPTURE_EMPTY_BASIS_WITH_MEMBERS');
        $selectionJson = self::canonicalJson($selection);
        $payload = self::canonicalJson(['schema_version' => self::VERSION, 'component_key' => $component,
            'selection_context' => $selection, 'rows' => array_values($rows), 'empty_basis' => $emptyBasis]);
        $slot = self::slotHash($component, $selection);
        $hash = hash('sha256', $payload);

        return DB::transaction(function () use ($runId, $stage, $component, $selectionJson, $payload, $slot, $hash, $rows, $emptyBasis, $audit) {
            // Serialize slot issuance on the owning run, also used by publication binding/seal.
            $run = DB::table('eod_runs')->where('run_id', $runId)->lockForUpdate()->first();
            if (! $run) throw new \RuntimeException('INPUT_CAPTURE_RUN_NOT_FOUND');
            $query = DB::table('md_run_input_captures')->where('run_id', $runId)->where('stage_code', $stage)
                ->where('component_key', $component)->where('slot_hash', $slot);
            $existing = $query->first();
            if ($existing) {
                $this->verify((array) $existing);
                if ((string) $existing->semantic_payload_json !== $payload || (string) $existing->payload_hash !== $hash) {
                    throw new \RuntimeException('INPUT_CAPTURE_CONFLICT: '.$stage.'.'.$component.'.'.$slot);
                }
                return (array) $existing;
            }
            if (DB::table('eod_publications')->where('run_id', $runId)->where('seal_state', 'SEALED')->exists()) {
                throw new \RuntimeException('INPUT_CAPTURE_AFTER_SEAL');
            }
            $id = DB::table('md_run_input_captures')->insertGetId([
                'run_id' => $runId, 'stage_code' => $stage, 'component_key' => $component, 'slot_hash' => $slot,
                'capture_schema_version' => self::VERSION, 'selection_context_json' => $selectionJson,
                'semantic_payload_json' => $payload, 'payload_hash' => $hash, 'member_count' => count($rows),
                'empty_basis_json' => $emptyBasis === null ? null : self::canonicalJson($emptyBasis),
                'audit_context_json' => self::canonicalJson($audit),
                'captured_at' => Carbon::now('UTC')->format('Y-m-d H:i:s.u'),
            ], 'input_capture_id');
            return (array) DB::table('md_run_input_captures')->where('input_capture_id', $id)->first();
        });
    }

    public function forRun(int $runId): array
    {
        $rows = DB::table('md_run_input_captures')->where('run_id', $runId)
            ->orderBy('stage_code')->orderBy('component_key')->orderBy('slot_hash')->get()->all();
        return array_map(function ($row) { $row = (array) $row; $this->verify($row); return $row; }, $rows);
    }

    /** Capture the selected immutable config, never a fresh resolution for an existing run. */
    public function captureRunConfiguration($run, array $snapshot): array
    {
        $json = (string) ($snapshot['resolved_config_json'] ?? '');
        $resolved = json_decode($json, true);
        if (! is_array($resolved) || ! isset($resolved['resolved_config'], $resolved['semantic_bindings'])
            || self::canonicalJson($resolved) !== $json
            || ! hash_equals(hash('sha256', $json), (string) ($snapshot['config_hash'] ?? ''))
            || (int) ($snapshot['config_snapshot_id'] ?? 0) !== (int) $run->config_snapshot_id
            || (string) ($snapshot['config_hash'] ?? '') !== (string) $run->config_hash
            || (string) ($snapshot['snapshot_uid'] ?? '') !== (string) $run->config_snapshot_ref) {
            throw new \RuntimeException('INPUT_CAPTURE_CONFIG_IDENTITY_MISMATCH');
        }
        return $this->capture((int) $run->run_id, 'RUN_CONTEXT', 'run_config', [
            'operation' => 'owning-run-config/v1', 'requested_date' => (string) $run->trade_date_requested,
            'knowledge_cutoff_at' => (string) $run->knowledge_cutoff_at,
            'source' => (string) $run->source, 'request_mode' => $run->request_mode,
        ], [$snapshot], null, ['run_id' => (int) $run->run_id]);
    }

    public function executeProducer($run, string $stage, string $operation, callable $producer, bool $atomic = true)
    {
        return ProducerInputScope::during($run, $stage, $operation, $producer, $this, $atomic);
    }

    public function owningRun(int $runId)
    {
        $run = \App\Models\EodRun::query()->find($runId);
        if (! $run) throw new \RuntimeException('INPUT_CAPTURE_RUN_NOT_FOUND');
        return $run;
    }

    /**
     * F-MD-B18-A002-020: the authoritative seed-run link for a derived promote run
     * (`EodRunRepository::createPromoteRunFromSeed`), read from the immutable `RUN_CREATED` event
     * already recorded for every such run -- nothing new is written here, only read. Returns null
     * for a run with no seed (the ordinary mainline case), never a guess or a fallback to the
     * current/latest run for the same trade date.
     */
    public function resolveSeedRunId(int $runId): ?int
    {
        $event = DB::table('eod_run_events')
            ->where('run_id', $runId)
            ->where('event_type', 'RUN_CREATED')
            ->orderBy('event_id')
            ->first();
        if (! $event) return null;
        $payload = json_decode((string) $event->event_payload_json, true);
        if (! is_array($payload) || empty($payload['seed_run_id'])) return null;
        return (int) $payload['seed_run_id'];
    }

    public function captureRegistryVersions($run): array
    {
        return (new ProducerRegistrySnapshot())->capture($run, $this);
    }

    public function assertConsumedConfiguration($run): array
    {
        $snapshots = new MarketDataConfigSnapshotRepository();
        $snapshot = $snapshots->find((int) $run->config_snapshot_id);
        $captured = $this->captureRunConfiguration($run, $snapshot ?: []);
        if ($snapshots->currentContent() !== $snapshot['resolved_config_json']) {
            throw new \RuntimeException('INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE: producer must execute its bound configuration.');
        }
        return $captured;
    }

    public function captureForProducer($run, string $stage, string $component, string $operation, array $rows, array $selection = [], ?array $emptyBasis = null): array
    {
        return $this->capture((int) $run->run_id, $stage, $component, [
            'operation' => $operation, 'requested_date' => (string) $run->trade_date_requested,
            'knowledge_cutoff_at' => (string) $run->knowledge_cutoff_at,
            'config_snapshot_id' => (int) $run->config_snapshot_id,
            'dataset_start' => (string) config('market_data.scope.dataset_start'),
        ] + $selection, $rows, $emptyBasis);
    }

    public function verify(array $row): array
    {
        $payload = json_decode((string) $row['semantic_payload_json'], true);
        $selection = json_decode((string) $row['selection_context_json'], true);
        if (! is_array($payload) || ! is_array($selection)
            || self::canonicalJson($payload) !== $row['semantic_payload_json']
            || self::canonicalJson($selection) !== $row['selection_context_json']
            || ($payload['schema_version'] ?? null) !== self::VERSION
            || ($row['capture_schema_version'] ?? null) !== self::VERSION
            || ($payload['component_key'] ?? null) !== $row['component_key']
            || ($payload['selection_context'] ?? null) !== $selection
            || ! isset($payload['rows']) || ! is_array($payload['rows'])
            || count($payload['rows']) !== (int) $row['member_count']
            || ! hash_equals(hash('sha256', $row['semantic_payload_json']), (string) $row['payload_hash'])
            || ! hash_equals(hash('sha256', self::canonicalJson(['schema_version' => self::VERSION,
                'component_key' => $row['component_key'], 'selection_context' => $selection])), (string) $row['slot_hash'])) {
            throw new \RuntimeException('INPUT_CAPTURE_INTEGRITY_FAILED: '.($row['input_capture_id'] ?? 'unknown'));
        }
        $empty = $payload['empty_basis'] ?? null;
        if (($payload['rows'] === [] && (empty($empty['reason']) || empty($empty['source']) || ! array_key_exists('evaluated_population', $empty)))
            || ($payload['rows'] !== [] && $empty !== null)
            || ($empty === null ? null : self::canonicalJson($empty)) !== $row['empty_basis_json']) {
            throw new \RuntimeException('INPUT_CAPTURE_EMPTY_BASIS_INVALID');
        }
        return $payload;
    }
}
