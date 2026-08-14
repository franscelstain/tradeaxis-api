<?php

namespace App\Application\MarketData\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;

/**
 * Materializes the point-in-time governance context of a candidate publication.
 */
class PublicationGovernanceBindingService
{
    private $artifacts;

    public function __construct(EodArtifactRepository $artifacts = null)
    {
        $this->artifacts = $artifacts ?: new EodArtifactRepository();
    }

    public function bind($run, $publication, $tradeDate): array
    {
        if ((string) ($publication->seal_state ?? '') === 'SEALED') {
            throw new \RuntimeException('SEALED_PUBLICATION_IMMUTABLE: governance binding must precede seal.');
        }

        $eligibilityRows = $this->artifacts->loadCandidateEligibilityForGovernance(
            (int) $publication->publication_id,
            (string) $tradeDate
        );

        $bindings = $this->bindMarketStructure(
            (int) $publication->publication_id,
            (string) $tradeDate,
            $eligibilityRows,
            $run->started_at ?? $run->created_at ?? null
        );

        $factorDecisions = DB::table('md_adjustment_factor_decisions')
            ->where('factor_set_id', (int) $publication->factor_set_id)
            ->orderBy('corporate_action_revision_id')
            ->get()
            ->all();

        $sourceScaleHash = $this->hashRows('source-scale-assessment-set/v1', array_map(function ($row) {
            return [
                'corporate_action_revision_id' => (int) $row->corporate_action_revision_id,
                'source_scale_assessment_id' => $row->source_scale_assessment_id === null ? null : (int) $row->source_scale_assessment_id,
                'decision_state' => (string) $row->decision_state,
            ];
        }, $factorDecisions));

        $factorDecisionHash = $this->hashRows('factor-decision-set/v1', array_map(function ($row) {
            return [
                'corporate_action_revision_id' => (int) $row->corporate_action_revision_id,
                'decision_state' => (string) $row->decision_state,
                'candidate_price_factor' => $row->candidate_price_factor === null ? null : (string) $row->candidate_price_factor,
                'candidate_volume_factor' => $row->candidate_volume_factor === null ? null : (string) $row->candidate_volume_factor,
                'reason_code' => (string) $row->reason_code,
            ];
        }, $factorDecisions));

        $marketStructureHash = $this->hashRows('market-structure-resolution-set/v1', $bindings);
        $identityHash = $this->identityRevisionSetHash($bindings);
        $calendarHash = $this->calendarRevisionSetHash($tradeDate);
        $statusHash = $this->statusRevisionSetHash($eligibilityRows);
        $eventHash = $this->hashRows('event-revision-set/v1', array_map(function ($row) {
            return (int) $row->corporate_action_revision_id;
        }, $factorDecisions));
        $configSnapshot = DB::table('md_config_snapshots')
            ->where('config_snapshot_id', (int) $run->config_snapshot_id)
            ->first();

        if (! $configSnapshot) {
            throw new \RuntimeException('CONFIG_SNAPSHOT_NOT_FOUND: governance binding requires the run snapshot.');
        }

        $observationManifestHash = (string) ($publication->observation_manifest_hash ?? $run->observation_manifest_hash ?? '');
        if (! preg_match('/^[a-f0-9]{64}$/', $observationManifestHash)) {
            throw new \RuntimeException('OBSERVATION_MANIFEST_HASH_MISSING: governance binding is incomplete.');
        }

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $payload = [
            'publication_id' => (int) $publication->publication_id,
            'corpus_admission_decision_id' => empty($run->corpus_admission_decision_id)
                ? null
                : (int) $run->corpus_admission_decision_id,
            'config_snapshot_id' => (int) $run->config_snapshot_id,
            'factor_set_id' => (int) $publication->factor_set_id,
            'observation_manifest_hash' => $observationManifestHash,
            'identity_revision_set_hash' => $identityHash,
            'calendar_revision_set_hash' => $calendarHash,
            'status_revision_set_hash' => $statusHash,
            'event_revision_set_hash' => $eventHash,
            'source_scale_assessment_set_hash' => $sourceScaleHash,
            'market_structure_revision_set_hash' => $marketStructureHash,
            'factor_decision_set_hash' => $factorDecisionHash,
            'formula_version' => (string) config('market_data.indicators.set_version'),
            'build_id' => trim((string) ($configSnapshot->build_id ?? '')) !== ''
                ? (string) $configSnapshot->build_id
                : 'config-snapshot:'.(int) $run->config_snapshot_id.':'.(string) $configSnapshot->config_hash,
            'read_model_version' => (string) ($publication->read_model_version ?? 'market_data_read_product_v1'),
            'created_at' => $now,
        ];

        DB::transaction(function () use ($publication, $payload, $sourceScaleHash, $marketStructureHash, $factorDecisionHash, $now) {
            DB::table('md_publication_lineage_bindings')->updateOrInsert(
                ['publication_id' => (int) $publication->publication_id],
                $payload
            );

            DB::table('eod_publications')->where('publication_id', $publication->publication_id)->update([
                'corpus_admission_decision_id' => $payload['corpus_admission_decision_id'],
                'source_scale_assessment_set_hash' => $sourceScaleHash,
                'market_structure_revision_set_hash' => $marketStructureHash,
                'factor_decision_set_hash' => $factorDecisionHash,
                'updated_at' => $now,
            ]);
        });

        return [
            'source_scale_assessment_set_hash' => $sourceScaleHash,
            'market_structure_revision_set_hash' => $marketStructureHash,
            'factor_decision_set_hash' => $factorDecisionHash,
            'market_structure_binding_count' => count($bindings),
        ];
    }

    private function bindMarketStructure($publicationId, $tradeDate, array $rows, $knownAt): array
    {
        $revisions = $this->marketStructureRevisionsForDate($tradeDate, $knownAt);
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $canonical = [];

        DB::transaction(function () use ($rows, $revisions, $publicationId, $tradeDate, $knownAt, $now, &$canonical) {
            DB::table('md_publication_market_structure_bindings')->where('publication_id', $publicationId)->delete();

            foreach ($rows as $row) {
                $board = $this->normalizeBoard($row->board_code ?? null);
                $recordedDate = ! empty($row->board_identity_recorded_at)
                    ? substr((string) $row->board_identity_recorded_at, 0, 10)
                    : null;

                $bandId = null;
                $floorId = null;
                $tickId = null;
                if ($board === null) {
                    $state = 'FAIL_CLOSED_BOARD_UNKNOWN';
                    $reason = 'MARKET_STRUCTURE_BOARD_UNKNOWN';
                } elseif ($recordedDate === null
                    || $recordedDate > $tradeDate
                    || ($knownAt !== null && (string) $row->board_identity_recorded_at > (string) $knownAt)) {
                    $state = 'FAIL_CLOSED_BOARD_NOT_POINT_IN_TIME';
                    $reason = 'MARKET_STRUCTURE_BOARD_NOT_POINT_IN_TIME';
                } elseif (in_array($board, ['ACCELERATION', 'SPECIAL_MONITORING'], true)) {
                    $state = 'FAIL_CLOSED_NON_STANDARD_BOARD';
                    $reason = 'MARKET_STRUCTURE_SCOPE_EXCLUDED';
                } elseif (! in_array($board, ['MAIN', 'DEVELOPMENT', 'NEW_ECONOMY'], true)) {
                    $state = 'FAIL_CLOSED_BOARD_UNRECOGNIZED';
                    $reason = 'MARKET_STRUCTURE_BOARD_UNRECOGNIZED';
                } elseif (! isset($revisions['PRICE_BAND'], $revisions['MINIMUM_PRICE'], $revisions['TICK_SIZE'])) {
                    $state = 'FAIL_CLOSED_REVISION_MISSING';
                    $reason = 'MARKET_STRUCTURE_REVISION_MISSING';
                } else {
                    $state = 'RESOLVED_STANDARD_BOARD';
                    $reason = null;
                    $bandId = (int) $revisions['PRICE_BAND']->market_structure_revision_id;
                    $floorId = (int) $revisions['MINIMUM_PRICE']->market_structure_revision_id;
                    $tickId = (int) $revisions['TICK_SIZE']->market_structure_revision_id;
                }

                $binding = [
                    'publication_id' => $publicationId,
                    'listing_id' => (int) $row->listing_id,
                    'resolution_state' => $state,
                    'normalized_board_code' => $board,
                    'board_identity_recorded_at' => $row->board_identity_recorded_at,
                    'price_band_revision_id' => $bandId,
                    'minimum_price_revision_id' => $floorId,
                    'tick_size_revision_id' => $tickId,
                    'reason_code' => $reason,
                    'created_at' => $now,
                ];
                DB::table('md_publication_market_structure_bindings')->insert($binding);

                $this->artifacts->bindCandidateEligibilityMarketStructure(
                    $publicationId,
                    $tradeDate,
                    (int) $row->listing_id,
                    [
                        'market_structure_resolution_state' => $state,
                        'price_band_revision_id' => $bandId,
                        'minimum_price_revision_id' => $floorId,
                        'tick_size_revision_id' => $tickId,
                    ]
                );

                unset($binding['created_at'], $binding['publication_id']);
                $canonical[] = $binding;
            }
        });

        return $canonical;
    }

    private function marketStructureRevisionsForDate($tradeDate, $knownAt): array
    {
        $rows = DB::table('md_exchange_market_structure_revisions as revision')
            ->where('revision.verification_state', 'AUTHORITATIVE_VERIFIED')
            ->where('revision.instrument_scope_code', 'IDX_REGULAR_STANDARD_EQUITY')
            ->where('revision.effective_from', '<=', $tradeDate)
            ->where(function ($query) use ($tradeDate) {
                $query->whereNull('revision.effective_to')->orWhere('revision.effective_to', '>=', $tradeDate);
            })
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('md_exchange_market_structure_revisions as newer')
                    ->whereColumn('newer.supersedes_revision_id', 'revision.market_structure_revision_id');
            })
            ->orderBy('revision.rule_type')
            ->when($knownAt !== null && $knownAt !== '', function ($query) use ($knownAt) {
                $query->where('revision.recorded_at', '<=', $knownAt);
            })
            ->get();

        $resolved = [];
        foreach ($rows as $row) {
            if (isset($resolved[$row->rule_type])) {
                throw new \RuntimeException('MARKET_STRUCTURE_REVISION_AMBIGUOUS: multiple current revisions cover '.$tradeDate.'.');
            }
            $resolved[$row->rule_type] = $row;
        }

        return $resolved;
    }

    private function normalizeBoard($board)
    {
        $board = strtoupper(trim((string) $board));
        $aliases = [
            'MB' => 'MAIN',
            'DB' => 'DEVELOPMENT',
            'DEVELOPMEN' => 'DEVELOPMENT',
            'ACCELERATI' => 'ACCELERATION',
            'WATCHLIST' => 'SPECIAL_MONITORING',
        ];

        $board = $aliases[$board] ?? $board;

        return $board === '' ? null : $board;
    }

    private function identityRevisionSetHash(array $bindings): string
    {
        return $this->hashRows('identity-board-resolution-set/v1', array_map(function ($binding) {
            return [
                'listing_id' => $binding['listing_id'],
                'normalized_board_code' => $binding['normalized_board_code'],
                'board_identity_recorded_at' => $binding['board_identity_recorded_at'],
                'resolution_state' => $binding['resolution_state'],
            ];
        }, $bindings));
    }

    private function calendarRevisionSetHash($tradeDate): string
    {
        $rows = DB::table('md_market_calendar_revisions as revision')
            ->where('revision.cal_date', $tradeDate)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('md_market_calendar_revisions as newer')
                    ->whereColumn('newer.supersedes_revision_id', 'revision.calendar_revision_id');
            })
            ->orderBy('revision.calendar_revision_id')
            ->get()
            ->map(function ($row) {
                return [
                    'calendar_revision_id' => (int) $row->calendar_revision_id,
                    'revision_uid' => (string) $row->revision_uid,
                    'session_state' => (string) $row->session_state,
                ];
            })
            ->all();

        return $this->hashRows('calendar-revision-set/v1', $rows);
    }

    private function statusRevisionSetHash(array $rows): string
    {
        $rows = array_map(function ($row) {
                return [
                    'listing_id' => (int) $row->listing_id,
                    'bar_expectation_state' => (string) $row->bar_expectation_state,
                    'temporal_status_state' => (string) $row->temporal_status_state,
                    'trading_status_revision_id' => $row->trading_status_revision_id === null
                        ? null
                        : (int) $row->trading_status_revision_id,
                    'trading_status_source_observation_id' => $row->trading_status_source_observation_id === null
                        ? null
                        : (int) $row->trading_status_source_observation_id,
                ];
            }, $rows);

        return $this->hashRows('status-resolution-set/v1', $rows);
    }

    private function hashRows($schemaVersion, array $rows): string
    {
        return hash('sha256', json_encode([
            'schema_version' => $schemaVersion,
            'rows' => array_values($rows),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
