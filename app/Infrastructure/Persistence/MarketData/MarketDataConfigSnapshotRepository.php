<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Domain\MarketData\MarketDataSemanticBindings;
use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\MarketData\Config\PlatformConfigRegistry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketDataConfigSnapshotRepository
{
    /**
     * Resolve the configuration identity for a run.
     *
     * Without $knownAt this resolves the configuration as it stands now and records it if new,
     * which is what creating a run requires: the run executes under today's config.
     *
     * With $knownAt it becomes a lookup and never inserts. An as-known replay asks what the
     * platform knew at a past moment, and resolving-or-creating there would answer with a
     * configuration that did not exist yet — manufacturing the very knowledge the cutoff exists to
     * withhold. When nothing was recorded by that moment the answer is an error, not an invention.
     */
    public function resolveForRun($requestedDate, $knownAt = null)
    {
        $scope = MarketDataScope::fromConfig();
        $requestedDate = $scope->assertRequestedDate($requestedDate);

        if ($knownAt !== null && trim((string) $knownAt) !== '') {
            return $this->resolveAsKnown($requestedDate, $knownAt);
        }

        $marketDataConfig = config('market_data', []);
        (new PlatformConfigRegistry())->assertResolvedConfiguration($marketDataConfig);

        $resolved = $this->canonicalize([
            'resolved_config' => $this->redact($marketDataConfig),
            'semantic_bindings' => MarketDataSemanticBindings::snapshot(),
        ]);
        $json = json_encode($resolved, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

        if ($json === false) {
            throw new \RuntimeException('CONFIG_SNAPSHOT_SERIALIZATION_FAILED: market-data config cannot be serialized canonically.');
        }

        $hash = hash('sha256', $json);
        $effectiveAt = $requestedDate.' 00:00:00';
        $schemaVersion = (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1');
        $environmentProfile = (string) config('market_data.governance.environment_profile', 'local');

        /*
         * Both branches of this method now answer "which snapshot governs this date" the same way:
         * the latest snapshot effective on or before it. The range reading is the normative one.
         *
         * The exact-date match this replaced meant an unchanged configuration still produced a new
         * row for every date processed — a full-range recompute would have written one snapshot per
         * trade date, all carrying the same config_hash. That made config_snapshot_id a per-run
         * surrogate rather than the identity of a configuration, and it disagreed with the as-known
         * branch, which reads snapshots as intervals. Reusing the governing snapshot when the
         * content is unchanged makes the id stable and the two branches consistent.
         *
         * A new row is written only when the resolved configuration actually differs from the one
         * currently governing the date, and it takes effect from that date.
         */
        $governing = $this->governingSnapshot($requestedDate, $schemaVersion, $environmentProfile);

        if ($governing && (string) $governing->config_hash === $hash) {
            return (array) $governing;
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

    /**
     * The configuration recorded for this date at or before the cutoff, latest first.
     *
     * A snapshot recorded after the cutoff is invisible even though it is effective for the same
     * date, because the question is what was known, not what is true.
     */
    /**
     * The snapshot governing a date: the latest one effective on or before it.
     *
     * This is the single date-matching rule both branches use. `$knownAt` narrows it to what had
     * been recorded by a moment in time; without it the answer is the current one.
     */
    private function governingSnapshot($requestedDate, $schemaVersion, $environmentProfile, $knownAt = null)
    {
        $query = DB::table('md_config_snapshots')
            ->where('snapshot_schema_version', $schemaVersion)
            ->where('environment_profile', $environmentProfile)
            ->where('effective_at', '<=', $requestedDate.' 23:59:59');

        if ($knownAt !== null) {
            $query->where('recorded_at', '<=', $knownAt);
        }

        // effective_at picks the interval that governs the date; recorded_at picks the latest
        // revision of that interval; the id only breaks a genuine tie.
        return $query
            ->orderByDesc('effective_at')
            ->orderByDesc('recorded_at')
            ->orderByDesc('config_snapshot_id')
            ->first();
    }

    private function resolveAsKnown($requestedDate, $knownAt)
    {
        $schemaVersion = (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1');
        $environmentProfile = (string) config('market_data.governance.environment_profile', 'local');

        $row = $this->governingSnapshot($requestedDate, $schemaVersion, $environmentProfile, $knownAt);

        if (! $row) {
            throw new \RuntimeException(
                'CONFIG_SNAPSHOT_NOT_KNOWN_AT_CUTOFF: no configuration was recorded for '.$requestedDate
                .' at or before '.$knownAt.'. An as-known replay cannot resolve a configuration the '
                .'platform had not yet recorded; creating one here would fabricate the knowledge the '
                .'cutoff exists to withhold.'
            );
        }

        return (array) $row;
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
