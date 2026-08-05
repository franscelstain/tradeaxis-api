<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Domain\MarketData\MarketDataScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketDataConfigSnapshotRepository
{
    public function resolveForRun($requestedDate)
    {
        $scope = MarketDataScope::fromConfig();
        $requestedDate = $scope->assertRequestedDate($requestedDate);
        $resolved = $this->canonicalize($this->redact(config('market_data', [])));
        $json = json_encode($resolved, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

        if ($json === false) {
            throw new \RuntimeException('CONFIG_SNAPSHOT_SERIALIZATION_FAILED: market-data config cannot be serialized canonically.');
        }

        $hash = hash('sha256', $json);
        $effectiveAt = $requestedDate.' 00:00:00';
        $schemaVersion = (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1');
        $environmentProfile = (string) config('market_data.governance.environment_profile', 'local');

        $existing = DB::table('md_config_snapshots')
            ->where('config_hash', $hash)
            ->where('snapshot_schema_version', $schemaVersion)
            ->where('effective_at', $effectiveAt)
            ->where('environment_profile', $environmentProfile)
            ->orderBy('config_snapshot_id')
            ->first();

        if ($existing) {
            return (array) $existing;
        }

        $now = Carbon::now($scope->timezone())->toDateTimeString();
        $snapshotUid = hash('sha256', $schemaVersion.'|'.$effectiveAt.'|'.$environmentProfile.'|'.$hash);
        $id = DB::table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => $snapshotUid,
            'snapshot_schema_version' => $schemaVersion,
            'serialization_version' => (string) config('market_data.governance.config_serialization_version', 'canonical_json_v1'),
            'resolved_config_json' => $json,
            'config_hash' => $hash,
            'registry_revision' => (string) config('market_data.governance.config_registry_revision', 'platform_config_registry_v2'),
            'effective_at' => $effectiveAt,
            'recorded_at' => $now,
            'build_id' => (string) config('market_data.governance.build_id', 'development-worktree'),
            'environment_profile' => $environmentProfile,
            'resolver_version' => (string) config('market_data.governance.config_resolver_version', 'market_data_config_resolver_v1'),
            'created_at' => $now,
        ]);

        return (array) DB::table('md_config_snapshots')->where('config_snapshot_id', $id)->first();
    }

    public function find($snapshotId)
    {
        $row = DB::table('md_config_snapshots')->where('config_snapshot_id', $snapshotId)->first();

        return $row ? (array) $row : null;
    }

    private function redact($value, $key = '')
    {
        if ($this->isSecretKey($key)) {
            return '[REDACTED:'.(string) config('market_data.governance.credential_profile', 'configured-secret').']';
        }

        if (! is_array($value)) {
            return $value;
        }

        $redacted = [];
        foreach ($value as $childKey => $childValue) {
            $redacted[$childKey] = $this->redact($childValue, (string) $childKey);
        }

        return $redacted;
    }

    private function isSecretKey($key)
    {
        return preg_match('/(^|_)(token|secret|password|cookie|authorization|api_key|private_key)($|_)/i', (string) $key) === 1;
    }

    private function canonicalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        if (! $isList) {
            ksort($value, SORT_STRING);
        }

        foreach ($value as $key => $child) {
            $value[$key] = $this->canonicalize($child);
        }

        return $value;
    }
}
