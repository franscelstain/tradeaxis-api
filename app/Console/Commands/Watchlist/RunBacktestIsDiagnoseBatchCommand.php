<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;

class RunBacktestIsDiagnoseBatchCommand extends Command
{
    protected $signature = 'watchlist:backtest-is-diagnose-batch
        {--catalog-code= : Explicit immutable IS calibration catalog code}
        {--from= : Explicit historical IS start date in YYYY-MM-DD}
        {--to= : Explicit historical IS end date in YYYY-MM-DD}
        {--param-ids= : Optional comma-separated param_id allowlist for scoped batch drilldown}
        {--output-dir= : Explicit directory for per-param JSON drilldown artifacts}
        {--summary= : Explicit CSV summary artifact path}
        {--overwrite : Explicitly replace existing output files}';

    protected $description = 'Run deterministic batched Weekly Swing in-sample-only failure drilldown without OOS read/write/promotion.';

    public function handle(): int
    {
        $catalogCode = trim((string) $this->option('catalog-code'));
        $fromDate = trim((string) $this->option('from'));
        $toDate = trim((string) $this->option('to'));
        $outputDir = trim((string) $this->option('output-dir'));
        $summary = trim((string) $this->option('summary'));

        if ($catalogCode === '') {
            return $this->blocked('WS_BT_CATALOG_CODE_REQUIRED', 'Explicit --catalog-code is required. No latest/active/default catalog fallback is allowed.');
        }
        if (! $this->validDate($fromDate) || ! $this->validDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Explicit --from and --to must be valid YYYY-MM-DD dates with --from <= --to.');
        }
        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked(
                strcmp($toDate, WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) > 0
                    ? 'WS_BT_C01_IS_BOUNDARY_VIOLATION'
                    : 'WS_BT_C01_IS_WINDOW_MISMATCH',
                'IS drilldown batch requires the exact window '.WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
                    .' through '.WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE.'.'
            );
        }
        if ($outputDir === '' || $summary === '') {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit --output-dir and --summary are required.');
        }

        $outputPath = rtrim($this->absolutePath($outputDir), '\\/');
        $summaryPath = $this->absolutePath($summary);
        if (is_file($outputPath) || is_dir($summaryPath)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Output directory or summary path is invalid.');
        }
        if (is_file($summaryPath) && ! (bool) $this->option('overwrite')) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Summary file already exists. Use --overwrite explicitly.');
        }

        $paramIds = $this->paramIds((string) $this->option('param-ids'));
        if ($paramIds === false) {
            return $this->blocked('WATCHLIST_BT_IS_DRILLDOWN_BATCH_PARAM_IDS_INVALID', 'Optional --param-ids must be a comma-separated list of positive integers.');
        }

        try {
            $repository = $this->laravel
                ? $this->laravel->make(WatchlistBacktestParamGridRepository::class)
                : app(WatchlistBacktestParamGridRepository::class);
            $service = $this->laravel
                ? $this->laravel->make(WatchlistBacktestIsFailureDrilldownService::class)
                : app(WatchlistBacktestIsFailureDrilldownService::class);
            $rows = $repository->allForCatalog($catalogCode, 'WS');
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        if ($rows === []) {
            return $this->blocked('WS_BT_C01_CATALOG_MISSING', 'No rows found for explicit catalog_code.');
        }
        $fullCatalogCount = count($rows);
        $fullCatalogVersion = $this->singleValue($rows, 'catalog_version');
        $fullCatalogHash = $this->singleValue($rows, 'catalog_hash');
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_IS_DRILLDOWN_ROW_FILTER_NO_MATCH', 'No catalog rows matched --param-ids.');
            }
        }

        usort($rows, function (array $left, array $right): int {
            $comparison = strcmp((string) ($left['row_code'] ?? ''), (string) ($right['row_code'] ?? ''));
            if ($comparison !== 0) {
                return $comparison;
            }

            return ((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0));
        });

        $summaryRows = [];
        $readyCount = 0;
        foreach ($rows as $row) {
            $paramId = (int) ($row['param_id'] ?? 0);
            $rowCode = (string) ($row['row_code'] ?? '');
            $artifactPath = $outputPath.DIRECTORY_SEPARATOR.$this->artifactFileName($catalogCode, $paramId, $rowCode);

            try {
                $result = $service->execute($catalogCode, $fromDate, $toDate, $artifactPath, [
                    'overwrite' => (bool) $this->option('overwrite'),
                    'param_id' => $paramId,
                ]);
            } catch (\Throwable $e) {
                $result = [
                    'is_ready' => false,
                    'status' => 'BLOCKED',
                    'reason_code' => $this->reasonCode($e),
                    'message' => $e->getMessage(),
                    'artifact_path' => $artifactPath,
                ];
            }

            if (($result['is_ready'] ?? false) === true) {
                $readyCount++;
            }
            $summaryRows[] = $this->summaryRow($catalogCode, $fullCatalogVersion, $fullCatalogCount, $fullCatalogHash, $paramId, $rowCode, $result, $artifactPath);
        }

        $write = $this->writeSummary($summaryPath, $summaryRows, (bool) $this->option('overwrite'));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked((string) $write['reason_code'], 'Batch summary artifact write failed.');
        }

        $allReady = $readyCount === count($summaryRows);
        $this->line('status='.($allReady ? 'PASS' : 'BLOCKED'));
        $this->line('reason_code='.($allReady ? 'WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY' : 'WS_BT_IS_FAILURE_DRILLDOWN_BATCH_INCOMPLETE'));
        $this->line('catalog_code='.$catalogCode);
        $this->line('catalog_version='.$fullCatalogVersion);
        $this->line('catalog_count='.$fullCatalogCount);
        $this->line('catalog_hash='.$fullCatalogHash);
        $this->line('diagnostic_param_count='.count($summaryRows));
        $this->line('ready_count='.$readyCount);
        $this->line('blocked_count='.(count($summaryRows) - $readyCount));
        $this->line('summary_path='.$summaryPath);
        $this->line('output_dir='.$outputPath);
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
        $this->line('oos_table_unchanged=1');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return $allReady ? 0 : 1;
    }

    private function summaryRow(string $catalogCode, string $catalogVersion, int $catalogCount, string $catalogHash, int $paramId, string $rowCode, array $result, string $artifactPath): array
    {
        $artifact = is_array($result['artifact'] ?? null) ? $result['artifact'] : [];
        $noOos = is_array($artifact['no_oos_leakage_summary'] ?? null) ? $artifact['no_oos_leakage_summary'] : [];
        $next = is_array($artifact['next_focus_recommendation'] ?? null) ? $artifact['next_focus_recommendation'] : [];
        $metrics = $this->firstRow($artifact['per_param_key_metrics'] ?? []);
        $missing = is_array($next['missing_runtime_evidence_fields'] ?? null)
            ? implode('|', $next['missing_runtime_evidence_fields'])
            : '';

        return [
            'scope' => 'IS_ONLY_BATCHED_FAILURE_DRILLDOWN',
            'catalog_code' => $catalogCode,
            'catalog_version' => $catalogVersion,
            'catalog_count' => (string) $catalogCount,
            'catalog_hash' => $catalogHash,
            'scoped_artifact_catalog_count' => (string) ($artifact['catalog_count'] ?? ''),
            'param_id' => (string) $paramId,
            'row_code' => $rowCode,
            'status' => (string) ($result['status'] ?? 'BLOCKED'),
            'reason_code' => (string) ($result['reason_code'] ?? ''),
            'artifact_hash' => (string) ($result['artifact_hash'] ?? ''),
            'canonical_artifact_hash' => (string) ($artifact['canonical_artifact_hash'] ?? ''),
            'file_sha1' => (string) ($result['write']['sha1'] ?? (is_file($artifactPath) ? sha1_file($artifactPath) : '')),
            'artifact_path' => (string) ($result['write']['path'] ?? $artifactPath),
            'is_trading_date_count' => (string) ($artifact['is_trading_date_count'] ?? ''),
            'is_trading_date_hash' => (string) ($artifact['is_trading_date_hash'] ?? ''),
            'picks_count' => (string) ($metrics['picks_count'] ?? ''),
            'days_covered' => (string) ($metrics['days_covered'] ?? ''),
            'avg_ret_net_top' => (string) ($metrics['avg_ret_net_top'] ?? ''),
            'median_ret_net_top' => (string) ($metrics['median_ret_net_top'] ?? ''),
            'p25_ret_net_top' => (string) ($metrics['p25_ret_net_top'] ?? ''),
            'month_win_rate_min' => (string) ($metrics['month_win_rate_min'] ?? ''),
            'month_avg_ret_net_min' => (string) ($metrics['month_avg_ret_net_min'] ?? ''),
            'missing_runtime_evidence_fields' => $missing,
            'next_focus' => (string) ($next['focus'] ?? ''),
            'next_decision' => (string) ($next['decision'] ?? ''),
            'oos_service_invoked' => ! empty($noOos['oos_service_invoked']) ? '1' : '0',
            'oos_repository_invoked' => ! empty($noOos['oos_repository_invoked']) ? '1' : '0',
            'oos_executed' => ! empty($noOos['oos_executed']) ? '1' : '0',
            'production_ready' => ! empty($artifact['meta']['production_ready']) ? '1' : '0',
        ];
    }

    private function firstRow($rows): array
    {
        if (! is_array($rows)) {
            return [];
        }
        foreach ($rows as $row) {
            if (is_array($row)) {
                return $row;
            }
        }

        return [];
    }

    private function writeSummary(string $summaryPath, array $rows, bool $overwrite): array
    {
        if (is_file($summaryPath) && ! $overwrite) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID'];
        }
        $directory = dirname($summaryPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_DIRECTORY_UNAVAILABLE'];
        }
        $headers = array_keys($rows[0] ?? []);
        $tmp = $summaryPath.'.tmp';
        $handle = fopen($tmp, 'wb');
        if ($handle === false) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'];
        }
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, array_map('strval', array_values($row)));
        }
        fclose($handle);
        if (! rename($tmp, $summaryPath)) {
            @unlink($tmp);

            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_RENAME_FAILED'];
        }

        return ['is_ready' => true, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITTEN'];
    }

    private function singleValue(array $rows, string $field): string
    {
        $values = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? '');
            if ($value !== '') {
                $values[$value] = $value;
            }
        }

        return count($values) === 1 ? (string) array_key_first($values) : '';
    }

    private function paramIds(string $raw)
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $ids = [];
        foreach (explode(',', $raw) as $part) {
            $part = trim($part);
            if ($part === '' || ! ctype_digit($part) || (int) $part < 1) {
                return false;
            }
            $ids[(int) $part] = (int) $part;
        }

        return array_values($ids);
    }

    private function artifactFileName(string $catalogCode, int $paramId, string $rowCode): string
    {
        $safeCatalogCode = preg_replace('/[^A-Za-z0-9_-]+/', '_', $catalogCode) ?: 'catalog';
        $safeRowCode = preg_replace('/[^A-Za-z0-9_-]+/', '_', $rowCode) ?: 'row';

        return strtolower($safeCatalogCode).'-param-'.$paramId.'-'.$safeRowCode.'.json';
    }

    private function blocked(string $reasonCode, string $message): int
    {
        $this->error('status=BLOCKED');
        $this->line('reason_code='.$reasonCode);
        $this->line('error='.$message);
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 1;
    }

    private function validDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
    }

    private function absolutePath(string $path): string
    {
        $isUnixAbsolute = substr($path, 0, 1) === '/';
        $isUncAbsolute = substr($path, 0, 2) === '\\\\';
        $isWindowsAbsolute = strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && in_array($path[2], ['\\', '/'], true);

        if ($isUnixAbsolute || $isUncAbsolute || $isWindowsAbsolute) {
            return $path;
        }

        return base_path($path);
    }

    private function reasonCode(\Throwable $e): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'WS_BT_IS_FAILURE_DRILLDOWN_BATCH_FAILED';
    }
}
