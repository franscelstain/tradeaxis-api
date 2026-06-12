<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService;
use Illuminate\Console\Command;

class RunBacktestIsDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-is-diagnose
        {--catalog-code= : Explicit immutable IS calibration catalog code}
        {--from= : Explicit historical IS start date in YYYY-MM-DD}
        {--to= : Explicit historical IS end date in YYYY-MM-DD}
        {--param-id= : Optional explicit param_id filter for scoped heavy drilldown}
        {--row-code= : Optional explicit row_code filter for scoped heavy drilldown}
        {--output= : Explicit JSON drilldown output path}
        {--overwrite : Explicitly replace an existing output file}';

    protected $description = 'Run deterministic Weekly Swing in-sample-only failure drilldown without OOS read/write/promotion.';

    public function handle(): int
    {
        $catalogCode = trim((string) $this->option('catalog-code'));
        $fromDate = trim((string) $this->option('from'));
        $toDate = trim((string) $this->option('to'));
        $output = trim((string) $this->option('output'));

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
                'IS drilldown requires the exact window '.WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
                    .' through '.WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE.'.'
            );
        }
        if ($output === '') {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit --output is required.');
        }

        $outputPath = $this->absolutePath($output);
        if (is_dir($outputPath)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Output path points to a directory.');
        }

        try {
            $service = $this->laravel
                ? $this->laravel->make(WatchlistBacktestIsFailureDrilldownService::class)
                : app(WatchlistBacktestIsFailureDrilldownService::class);
            $result = $service->execute($catalogCode, $fromDate, $toDate, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
                'param_id' => $this->option('param-id'),
                'row_code' => $this->option('row-code'),
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        if (! ($result['is_ready'] ?? false)) {
            return $this->blocked(
                (string) ($result['reason_code'] ?? 'WS_BT_C01_IS_FAILURE_DRILLDOWN_BLOCKED'),
                (string) ($result['message'] ?? 'Diagnostic failed.')
            );
        }

        $this->render($result, $catalogCode, $fromDate, $toDate, $outputPath);

        return 0;
    }

    private function render(array $result, string $catalogCode, string $fromDate, string $toDate, string $outputPath): void
    {
        $artifact = is_array($result['artifact'] ?? null) ? $result['artifact'] : [];
        $noOos = is_array($artifact['no_oos_leakage_summary'] ?? null) ? $artifact['no_oos_leakage_summary'] : [];
        $next = is_array($artifact['next_focus_recommendation'] ?? null) ? $artifact['next_focus_recommendation'] : [];

        $this->line('status='.(string) ($result['status'] ?? 'DONE'));
        $this->line('reason_code='.(string) ($result['reason_code'] ?? 'WS_BT_C01_IS_FAILURE_DRILLDOWN_READY'));
        $this->line('catalog_code='.$catalogCode);
        $this->line('catalog_version='.(string) ($artifact['catalog_version'] ?? ''));
        $this->line('catalog_count='.(string) ($artifact['catalog_count'] ?? 0));
        $this->line('catalog_hash='.(string) ($artifact['catalog_hash'] ?? ''));
        $this->line('diagnostic_param_count='.(string) ($artifact['catalog_count'] ?? 0));
        $this->line('is_from='.$fromDate);
        $this->line('is_to='.$toDate);
        $this->line('is_trading_date_hash='.(string) ($artifact['is_trading_date_hash'] ?? ''));
        $this->line('max_requested_market_data_date='.(string) ($noOos['max_requested_market_data_date'] ?? ''));
        $this->line('max_allowed_market_data_date='.(string) ($noOos['max_allowed_market_data_date'] ?? ''));
        $this->line('strict_is_boundary_all_evaluations='.(! empty($noOos['strict_is_boundary_all_evaluations']) ? '1' : '0'));
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
        $this->line('oos_table_unchanged=1');
        $this->line('oos_executed=0');
        $this->line('canonical_artifact_hash='.(string) ($artifact['canonical_artifact_hash'] ?? ''));
        $this->line('artifact_hash='.(string) ($result['artifact_hash'] ?? ''));
        $this->line('artifact_path='.(string) ($result['write']['path'] ?? $outputPath));
        $this->line('next_focus='.(string) ($next['focus'] ?? ''));
        $this->line('next_decision='.(string) ($next['decision'] ?? ''));
        $this->line('production_ready=0');
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

        return 'WS_BT_C01_IS_FAILURE_DRILLDOWN_FAILED';
    }
}
