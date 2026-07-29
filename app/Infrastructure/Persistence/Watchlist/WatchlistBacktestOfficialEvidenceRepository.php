<?php

namespace App\Infrastructure\Persistence\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WatchlistBacktestOfficialEvidenceRepository
{
    private const SCHEMA_VERSION = 'WS_OFFICIAL_IS_EVIDENCE_C171_V1';
    private const INSERT_CHUNK_SIZE = 500;
    private const SQLITE_SAFE_BIND_LIMIT = 900;
    private const SQLSERVER_SAFE_BIND_LIMIT = 2000;
    private const READ_CHUNK_SIZE = 1000;

    private WeeklySwingBacktestEvidenceIdentityService $identity;

    public function __construct(WeeklySwingBacktestEvidenceIdentityService $identity = null)
    {
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
    }

    public function buildManifest(
        string $policyCode,
        int $paramId,
        array $backtestPayload,
        array $evaluatedTrades
    ): array {
        if (($backtestPayload['official_evidence']['storage_mode'] ?? null) === 'JSONL_SPOOL') {
            return $this->buildStreamingManifest($policyCode, $paramId, $backtestPayload, $evaluatedTrades);
        }

        return $this->buildInMemoryManifest($policyCode, $paramId, $backtestPayload, $evaluatedTrades);
    }

    public function persist(int $evalId, array $evidence): array
    {
        $this->assertSchema();
        $result = DB::transaction(function () use ($evalId, $evidence): array {
            $eval = DB::table('watchlist_bt_eval')->where('eval_id', $evalId)->lockForUpdate()->first();
            if (! $eval) {
                throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_EVAL_MISSING: eval_id='.$evalId);
            }
            $existing = DB::table('watchlist_bt_picks_ws')->where('eval_id', $evalId)->count()
                + DB::table('watchlist_bt_universe_ws')->where('eval_id', $evalId)->count()
                + DB::table('watchlist_bt_cutoffs_ws')->where('eval_id', $evalId)->count();
            if ($existing > 0) {
                $actual = $this->databaseManifest($evalId);
                if ($actual !== $evidence['manifest']) {
                    throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_IDENTITY_CONFLICT: persisted evidence differs for eval_id='.$evalId);
                }

                return ['status' => 'IDEMPOTENT', 'eval_id' => $evalId, 'manifest' => $actual];
            }

            $this->insertRows('watchlist_bt_picks_ws', $evalId, $evidence['picks'] ?? []);
            if (($evidence['storage_mode'] ?? 'IN_MEMORY') === 'JSONL_SPOOL') {
                $this->insertJsonlRows('watchlist_bt_universe_ws', $evalId, (string) ($evidence['universe_spool_path'] ?? ''));
                $this->insertJsonlRows('watchlist_bt_cutoffs_ws', $evalId, (string) ($evidence['cutoffs_spool_path'] ?? ''));
            } else {
                $this->insertRows('watchlist_bt_universe_ws', $evalId, $evidence['universe'] ?? []);
                $this->insertRows('watchlist_bt_cutoffs_ws', $evalId, $evidence['cutoffs'] ?? []);
            }

            $actual = $this->databaseManifest($evalId);
            if ($actual !== $evidence['manifest']) {
                throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_WRITE_MISMATCH: persisted evidence hash mismatch.');
            }

            return ['status' => 'INSERTED', 'eval_id' => $evalId, 'manifest' => $actual];
        });

        if (in_array((string) ($result['status'] ?? ''), ['INSERTED', 'IDEMPOTENT'], true)) {
            $result['spool_cleanup'] = $this->cleanupEvidenceSpools($evidence);
        }

        return $result;
    }

    public function databaseManifest(int $evalId): array
    {
        $lineage = [];
        $picks = $this->databaseTableDigest(
            'watchlist_bt_picks_ws',
            $evalId,
            ['asof_eod_date', 'ticker_id'],
            $lineage
        );
        $universe = $this->databaseTableDigest(
            'watchlist_bt_universe_ws',
            $evalId,
            ['asof_eod_date', 'ticker_id'],
            $lineage
        );
        $cutoffs = $this->databaseTableDigest(
            'watchlist_bt_cutoffs_ws',
            $evalId,
            ['asof_eod_date'],
            $lineage
        );

        $lineageValues = array_keys($lineage);
        sort($lineageValues, SORT_STRING);
        $manifest = [
            'schema_version' => self::SCHEMA_VERSION,
            'picks_count' => $picks['count'],
            'picks_hash' => $picks['hash'],
            'universe_count' => $universe['count'],
            'universe_hash' => $universe['hash'],
            'cutoff_count' => $cutoffs['count'],
            'cutoffs_hash' => $cutoffs['hash'],
            'market_data_lineage_hash' => $this->identity->stableHash($lineageValues),
        ];
        $manifest['evidence_manifest_hash'] = $this->identity->stableHash($manifest);

        return $manifest;
    }

    private function buildInMemoryManifest(
        string $policyCode,
        int $paramId,
        array $backtestPayload,
        array $evaluatedTrades
    ): array {
        $tradeIndex = $this->tradeIndex($backtestPayload['trades'] ?? []);
        $picks = $this->buildPicks($policyCode, $paramId, $evaluatedTrades, $tradeIndex);

        $universe = [];
        foreach (($backtestPayload['official_evidence']['universe'] ?? []) as $item) {
            $universe[] = $this->universeRow($policyCode, $paramId, $item);
        }
        $cutoffs = [];
        foreach (($backtestPayload['official_evidence']['cutoffs'] ?? []) as $item) {
            $cutoffs[] = $this->cutoffRow($policyCode, $paramId, $item);
        }

        $this->assertUniqueRows($picks, ['asof_eod_date', 'ticker_id'], 'PICK');
        $this->assertUniqueRows($universe, ['asof_eod_date', 'ticker_id'], 'UNIVERSE');
        $this->assertUniqueRows($cutoffs, ['asof_eod_date'], 'CUTOFF');
        $this->assertUniverseCutoffCoverage($universe, $cutoffs);
        $this->sortRows($picks, ['asof_eod_date', 'ticker_id']);
        $this->sortRows($universe, ['asof_eod_date', 'ticker_id']);
        $this->sortRows($cutoffs, ['asof_eod_date']);
        $lineage = $this->lineageFromRows(array_merge($picks, $universe, $cutoffs));

        $manifest = $this->manifest(
            count($picks),
            $this->identity->stableHash($picks),
            count($universe),
            $this->identity->stableHash($universe),
            count($cutoffs),
            $this->identity->stableHash($cutoffs),
            $this->identity->stableHash($lineage)
        );

        return [
            'storage_mode' => 'IN_MEMORY',
            'manifest' => $manifest,
            'picks' => $picks,
            'universe' => $universe,
            'cutoffs' => $cutoffs,
        ];
    }

    private function buildStreamingManifest(
        string $policyCode,
        int $paramId,
        array $backtestPayload,
        array $evaluatedTrades
    ): array {
        $source = is_array($backtestPayload['official_evidence'] ?? null)
            ? $backtestPayload['official_evidence']
            : [];
        if (($source['finalized'] ?? false) !== true) {
            throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_NOT_FINALIZED');
        }

        $tradeIndex = $this->tradeIndex($backtestPayload['trades'] ?? []);
        $picks = $this->buildPicks($policyCode, $paramId, $evaluatedTrades, $tradeIndex);
        $this->assertUniqueRows($picks, ['asof_eod_date', 'ticker_id'], 'PICK');
        $this->sortRows($picks, ['asof_eod_date', 'ticker_id']);

        $universeRaw = $this->assertReadableSpool((string) ($source['universe_spool_path'] ?? ''), 'UNIVERSE');
        $cutoffsRaw = $this->assertReadableSpool((string) ($source['cutoffs_spool_path'] ?? ''), 'CUTOFF');
        $universeCanonical = $universeRaw.'.canonical';
        $cutoffsCanonical = $cutoffsRaw.'.canonical';
        $lineage = [];
        $universeDates = [];
        $cutoffDates = [];

        $universeDigest = $this->canonicalizeSpool(
            $universeRaw,
            $universeCanonical,
            function (array $item) use ($policyCode, $paramId): array {
                return $this->universeRow($policyCode, $paramId, $item);
            },
            ['asof_eod_date', 'ticker_id'],
            'UNIVERSE',
            $lineage,
            $universeDates
        );
        $cutoffDigest = $this->canonicalizeSpool(
            $cutoffsRaw,
            $cutoffsCanonical,
            function (array $item) use ($policyCode, $paramId): array {
                return $this->cutoffRow($policyCode, $paramId, $item);
            },
            ['asof_eod_date'],
            'CUTOFF',
            $lineage,
            $cutoffDates
        );

        if ((int) ($source['universe_count'] ?? -1) !== $universeDigest['count']
            || (int) ($source['cutoff_count'] ?? -1) !== $cutoffDigest['count']) {
            throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_COUNT_MISMATCH');
        }
        $universeDateValues = array_keys($universeDates);
        $cutoffDateValues = array_keys($cutoffDates);
        sort($universeDateValues, SORT_STRING);
        sort($cutoffDateValues, SORT_STRING);
        if ($universeDateValues === [] || $universeDateValues !== $cutoffDateValues) {
            throw new \RuntimeException(
                'WS_C171_OFFICIAL_EVIDENCE_DATE_COVERAGE_MISMATCH: every universe date requires exactly one cutoff row.'
            );
        }

        foreach ($picks as $pick) {
            $this->collectLineage($lineage, $pick);
        }
        $lineageValues = array_keys($lineage);
        sort($lineageValues, SORT_STRING);
        $manifest = $this->manifest(
            count($picks),
            $this->identity->stableHash($picks),
            $universeDigest['count'],
            $universeDigest['hash'],
            $cutoffDigest['count'],
            $cutoffDigest['hash'],
            $this->identity->stableHash($lineageValues)
        );

        return [
            'storage_mode' => 'JSONL_SPOOL',
            'manifest' => $manifest,
            'picks' => $picks,
            'universe_spool_path' => $universeCanonical,
            'cutoffs_spool_path' => $cutoffsCanonical,
            'source_spool_paths' => [$universeRaw, $cutoffsRaw],
        ];
    }

    private function canonicalizeSpool(
        string $sourcePath,
        string $targetPath,
        callable $mapper,
        array $identityKeys,
        string $scope,
        array &$lineage,
        array &$dates
    ): array {
        $source = fopen($sourcePath, 'rb');
        $target = fopen($targetPath, 'wb');
        if ($source === false || $target === false) {
            if (is_resource($source)) fclose($source);
            if (is_resource($target)) fclose($target);
            throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_SPOOL_OPEN_FAILED');
        }

        $hash = hash_init('sha1');
        hash_update($hash, '[');
        $first = true;
        $count = 0;
        $previousIdentity = null;
        try {
            while (($line = fgets($source)) !== false) {
                $line = trim($line);
                if ($line === '') continue;
                $decoded = json_decode($line, true);
                if (! is_array($decoded)) {
                    throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_SPOOL_JSON_INVALID');
                }
                $row = $mapper($decoded);
                $identity = $this->rowIdentity($row, $identityKeys);
                if ($previousIdentity !== null && strcmp($identity, $previousIdentity) <= 0) {
                    $reason = $identity === $previousIdentity ? 'DUPLICATE_IDENTITY' : 'ORDER_INVALID';
                    throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_'.$reason.': '.$identity);
                }
                $previousIdentity = $identity;
                $this->collectLineage($lineage, $row);
                $dates[(string) $row['asof_eod_date']] = true;

                $json = json_encode($row, JSON_UNESCAPED_SLASHES);
                if ($json === false || fwrite($target, $json.PHP_EOL) === false) {
                    throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_CANONICAL_SPOOL_WRITE_FAILED');
                }
                if (! $first) hash_update($hash, ',');
                hash_update($hash, $this->identity->canonicalJson($row));
                $first = false;
                $count++;
            }
        } finally {
            fclose($source);
            fclose($target);
        }
        hash_update($hash, ']');

        return ['count' => $count, 'hash' => hash_final($hash)];
    }

    private function databaseTableDigest(string $table, int $evalId, array $orderBy, array &$lineage): array
    {
        $hash = hash_init('sha1');
        hash_update($hash, '[');
        $first = true;
        $count = 0;
        $query = DB::table($table)->where('eval_id', $evalId);
        foreach ($orderBy as $column) {
            $query->orderBy($column);
        }
        $query->chunk(self::READ_CHUNK_SIZE, function ($rows) use (&$hash, &$first, &$count, &$lineage): void {
            foreach ($rows as $row) {
                $canonical = $this->canonicalDatabaseRow((array) $row);
                $this->collectLineage($lineage, $canonical);
                if (! $first) hash_update($hash, ',');
                hash_update($hash, $this->identity->canonicalJson($canonical));
                $first = false;
                $count++;
            }
        });
        hash_update($hash, ']');

        return ['count' => $count, 'hash' => hash_final($hash)];
    }

    private function insertRows(string $table, int $evalId, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $firstPayloadRow = array_merge(['eval_id' => $evalId], $rows[0]);
        $chunkSize = $this->safeInsertChunkSize(count($firstPayloadRow));
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $payload = array_map(function (array $row) use ($evalId): array {
                return array_merge(['eval_id' => $evalId], $row);
            }, $chunk);
            if ($payload !== []) DB::table($table)->insert($payload);
        }
    }

    private function insertJsonlRows(string $table, int $evalId, string $path): void
    {
        $path = $this->assertReadableSpool($path, strtoupper($table));
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_CANONICAL_SPOOL_OPEN_FAILED: '.$path);
        }
        $batch = [];
        $batchLimit = null;
        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') continue;
                $row = json_decode($line, true);
                if (! is_array($row)) {
                    throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_CANONICAL_SPOOL_JSON_INVALID: '.$path);
                }
                $payloadRow = array_merge(['eval_id' => $evalId], $row);
                if ($batchLimit === null) {
                    $batchLimit = $this->safeInsertChunkSize(count($payloadRow));
                }
                $batch[] = $payloadRow;
                if (count($batch) >= $batchLimit) {
                    DB::table($table)->insert($batch);
                    $batch = [];
                }
            }
            if ($batch !== []) DB::table($table)->insert($batch);
        } finally {
            fclose($handle);
        }
    }

    private function safeInsertChunkSize(int $columnCount): int
    {
        $columnCount = max(1, $columnCount);
        $driver = DB::connection()->getDriverName();
        $bindLimit = null;
        if ($driver === 'sqlite') {
            $bindLimit = self::SQLITE_SAFE_BIND_LIMIT;
        } elseif ($driver === 'sqlsrv') {
            $bindLimit = self::SQLSERVER_SAFE_BIND_LIMIT;
        }

        if ($bindLimit === null) {
            return self::INSERT_CHUNK_SIZE;
        }

        return max(1, min(self::INSERT_CHUNK_SIZE, intdiv($bindLimit, $columnCount)));
    }

    private function buildPicks(string $policyCode, int $paramId, array $evaluatedTrades, array $tradeIndex): array
    {
        $picks = [];
        foreach ($evaluatedTrades as $evaluation) {
            $bucket = strtoupper((string) ($evaluation['bucket_code'] ?? 'TOP_PICKS'));
            if (($evaluation['metrics_ready'] ?? false) !== true
                || ! in_array($bucket, ['TOP', 'TOP_PICKS'], true)
                || ! is_numeric($evaluation['ret_net'] ?? null)) {
                continue;
            }
            $trade = $tradeIndex[$this->key($evaluation['trade_date'] ?? null, $evaluation['ticker_id'] ?? null)] ?? [];
            $row = [
                'policy_code' => $policyCode,
                'param_id' => $paramId,
                'asof_eod_date' => (string) ($evaluation['trade_date'] ?? ''),
                'ticker_id' => (int) ($evaluation['ticker_id'] ?? 0),
                'ticker_code' => (string) ($evaluation['ticker'] ?? ''),
                'bucket_code' => (string) ($evaluation['bucket_code'] ?? ''),
                'ret_net' => round((float) $evaluation['ret_net'], 6),
                'pass_guard' => 1,
                'score_total' => round((float) ($trade['score_total'] ?? 0.0), 6),
                'source_publication_id' => $evaluation['entry_publication_id'] ?? ($trade['source_reference']['publication_id'] ?? null),
                'source_publication_version' => $evaluation['entry_publication_version'] ?? ($trade['source_reference']['publication_version'] ?? null),
                'source_run_id' => $evaluation['entry_run_id'] ?? ($trade['source_reference']['run_id'] ?? null),
            ];
            if ($row['asof_eod_date'] === '' || $row['ticker_id'] < 1 || $row['ticker_code'] === '') {
                throw new \RuntimeException('WS_C171_OFFICIAL_PICK_IDENTITY_INVALID: every persisted pick requires trade date, ticker_id, and ticker_code.');
            }
            $this->assertMarketDataLineage($row, 'PICK');
            $row['row_hash'] = $this->identity->stableHash($row);
            $picks[] = $row;
        }

        return $picks;
    }

    private function universeRow(string $policyCode, int $paramId, array $item): array
    {
        $row = [
            'policy_code' => $policyCode,
            'param_id' => $paramId,
            'asof_eod_date' => (string) ($item['asof_eod_date'] ?? ''),
            'ticker_id' => (int) ($item['ticker_id'] ?? 0),
            'ticker_code' => (string) ($item['ticker_code'] ?? ''),
            'required_ok' => (bool) ($item['required_ok'] ?? false),
            'missing_fields' => $this->jsonOrNull($item['missing_fields'] ?? []),
            'guard_ok' => (bool) ($item['guard_ok'] ?? false),
            'eligible_ok' => (bool) ($item['eligible_ok'] ?? false),
            'dv20_idr' => $this->intOrNull($item['dv20_idr'] ?? null),
            'atr14_pct' => $this->floatOrNull($item['atr14_pct'] ?? null),
            'vol_ratio' => $this->floatOrNull($item['vol_ratio'] ?? null),
            'signal_close_price' => $this->floatOrNull($item['signal_close_price'] ?? null),
            'signal_tick_risk_expansion_pct' => $this->floatOrNull($item['signal_tick_risk_expansion_pct'] ?? null),
            'reason_code' => $this->reasonCode($item),
            'source_publication_id' => $this->intOrNull($item['source_publication_id'] ?? null),
            'source_publication_version' => $this->intOrNull($item['source_publication_version'] ?? null),
            'source_run_id' => $this->intOrNull($item['source_run_id'] ?? null),
        ];
        if ($row['asof_eod_date'] === '' || $row['ticker_id'] < 1 || $row['ticker_code'] === '') {
            throw new \RuntimeException('WS_C171_OFFICIAL_UNIVERSE_IDENTITY_INVALID: every universe row requires date, ticker_id, and ticker_code.');
        }
        $this->assertMarketDataLineage($row, 'UNIVERSE');
        $row['row_hash'] = $this->identity->stableHash($row);

        return $row;
    }

    private function cutoffRow(string $policyCode, int $paramId, array $item): array
    {
        if (! is_numeric($item['top_cutoff_score'] ?? null) || ! is_numeric($item['secondary_cutoff_score'] ?? null)) {
            throw new \RuntimeException('WS_C171_OFFICIAL_CUTOFF_INVALID: every evaluated date requires numeric TOP and SECONDARY cutoffs.');
        }
        $row = [
            'policy_code' => $policyCode,
            'param_id' => $paramId,
            'asof_eod_date' => (string) ($item['asof_eod_date'] ?? ''),
            'top_cutoff_score' => round((float) $item['top_cutoff_score'], 6),
            'secondary_cutoff_score' => round((float) $item['secondary_cutoff_score'], 6),
            'source_publication_id' => $this->intOrNull($item['source_publication_id'] ?? null),
            'source_publication_version' => $this->intOrNull($item['source_publication_version'] ?? null),
            'source_run_id' => $this->intOrNull($item['source_run_id'] ?? null),
        ];
        if ($row['asof_eod_date'] === '') {
            throw new \RuntimeException('WS_C171_OFFICIAL_CUTOFF_IDENTITY_INVALID: every cutoff requires asof_eod_date.');
        }
        $this->assertMarketDataLineage($row, 'CUTOFF');
        $row['row_hash'] = $this->identity->stableHash($row);

        return $row;
    }

    private function manifest(
        int $picksCount,
        string $picksHash,
        int $universeCount,
        string $universeHash,
        int $cutoffCount,
        string $cutoffsHash,
        string $lineageHash
    ): array {
        $manifest = [
            'schema_version' => self::SCHEMA_VERSION,
            'picks_count' => $picksCount,
            'picks_hash' => $picksHash,
            'universe_count' => $universeCount,
            'universe_hash' => $universeHash,
            'cutoff_count' => $cutoffCount,
            'cutoffs_hash' => $cutoffsHash,
            'market_data_lineage_hash' => $lineageHash,
        ];
        $manifest['evidence_manifest_hash'] = $this->identity->stableHash($manifest);

        return $manifest;
    }

    private function tradeIndex(array $trades): array
    {
        $index = [];
        foreach ($trades as $trade) {
            $index[$this->key($trade['trade_date'] ?? null, $trade['ticker_id'] ?? null)] = $trade;
        }

        return $index;
    }

    private function canonicalDatabaseRow(array $row): array
    {
        unset($row['eval_id'], $row['pick_id'], $row['created_at']);
        foreach (['required_ok','guard_ok','eligible_ok'] as $key) {
            if (array_key_exists($key, $row)) $row[$key] = (bool) $row[$key];
        }
        foreach (['param_id','ticker_id','pass_guard','source_publication_id','source_publication_version','source_run_id','dv20_idr'] as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null) $row[$key] = (int) $row[$key];
        }
        foreach (['ret_net','score_total','atr14_pct','vol_ratio','signal_close_price','signal_tick_risk_expansion_pct','top_cutoff_score','secondary_cutoff_score'] as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null) $row[$key] = round((float) $row[$key], 6);
        }

        return $row;
    }

    private function collectLineage(array &$lineage, array $row): void
    {
        if (($row['source_publication_id'] ?? null) === null) return;
        $key = implode(':', [
            $row['source_publication_id'],
            $row['source_publication_version'] ?? '',
            $row['source_run_id'] ?? '',
        ]);
        $lineage[$key] = true;
    }

    private function lineageFromRows(array $rows): array
    {
        $lineage = [];
        foreach ($rows as $row) $this->collectLineage($lineage, $row);
        $values = array_keys($lineage);
        sort($values, SORT_STRING);

        return $values;
    }

    private function cleanupEvidenceSpools(array $evidence): array
    {
        $paths = array_merge(
            is_array($evidence['source_spool_paths'] ?? null) ? $evidence['source_spool_paths'] : [],
            array_filter([
                $evidence['universe_spool_path'] ?? null,
                $evidence['cutoffs_spool_path'] ?? null,
            ], 'is_string')
        );
        $removed = [];
        $failed = [];
        foreach (array_values(array_unique($paths)) as $path) {
            if (! is_file($path)) continue;
            if (@unlink($path)) $removed[] = $path;
            else $failed[] = $path;
        }

        return ['removed' => $removed, 'failed' => $failed];
    }

    private function assertReadableSpool(string $path, string $scope): string
    {
        if ($path === '' || ! is_file($path) || ! is_readable($path)) {
            throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_SPOOL_UNAVAILABLE: '.$path);
        }

        return $path;
    }

    private function assertSchema(): void
    {
        foreach (['watchlist_bt_eval','watchlist_bt_picks_ws','watchlist_bt_universe_ws','watchlist_bt_cutoffs_ws'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'eval_id')) {
                throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SCHEMA_UNVERSIONED: '.$table.'.eval_id');
            }
        }
    }

    private function assertMarketDataLineage(array $row, string $scope): void
    {
        foreach (['source_publication_id', 'source_publication_version', 'source_run_id'] as $field) {
            if (! is_int($row[$field] ?? null) || (int) $row[$field] < 1) {
                throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_LINEAGE_MISSING: '.$field.' must be a positive integer.');
            }
        }
    }

    private function assertUniqueRows(array $rows, array $keys, string $scope): void
    {
        $seen = [];
        foreach ($rows as $row) {
            $identity = $this->rowIdentity($row, $keys);
            if (isset($seen[$identity])) {
                throw new \RuntimeException('WS_C171_OFFICIAL_'.$scope.'_DUPLICATE_IDENTITY: '.$identity);
            }
            $seen[$identity] = true;
        }
    }

    private function rowIdentity(array $row, array $keys): string
    {
        return implode('|', array_map(function (string $key) use ($row): string {
            $value = $row[$key] ?? '';
            if ($key === 'ticker_id') return str_pad((string) (int) $value, 20, '0', STR_PAD_LEFT);
            return (string) $value;
        }, $keys));
    }

    private function assertUniverseCutoffCoverage(array $universe, array $cutoffs): void
    {
        $universeDates = array_values(array_unique(array_map(function (array $row): string {
            return (string) $row['asof_eod_date'];
        }, $universe)));
        $cutoffDates = array_values(array_unique(array_map(function (array $row): string {
            return (string) $row['asof_eod_date'];
        }, $cutoffs)));
        sort($universeDates, SORT_STRING);
        sort($cutoffDates, SORT_STRING);
        if ($universeDates === [] || $universeDates !== $cutoffDates) {
            throw new \RuntimeException(
                'WS_C171_OFFICIAL_EVIDENCE_DATE_COVERAGE_MISMATCH: every universe date requires exactly one cutoff row.'
            );
        }
    }

    private function sortRows(array &$rows, array $keys): void
    {
        usort($rows, function (array $a, array $b) use ($keys): int {
            foreach ($keys as $key) {
                $cmp = ($a[$key] ?? '') <=> ($b[$key] ?? '');
                if ($cmp !== 0) return $cmp;
            }
            return 0;
        });
    }

    private function key($date, $tickerId): string { return (string) $date.'|'.(string) $tickerId; }
    private function intOrNull($value): ?int { return is_numeric($value) ? (int) $value : null; }
    private function floatOrNull($value): ?float { return is_numeric($value) ? round((float) $value, 6) : null; }
    private function jsonOrNull($value): ?string { return empty($value) ? null : json_encode($value, JSON_UNESCAPED_SLASHES); }
    private function reasonCode(array $item): ?string {
        if (is_string($item['reason_code'] ?? null) && $item['reason_code'] !== '') return $item['reason_code'];
        return isset($item['reason_codes'][0]) ? (string) $item['reason_codes'][0] : null;
    }
}
