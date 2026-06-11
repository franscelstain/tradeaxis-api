<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestIsCalibrationCommand extends Command
{
    protected $signature = 'watchlist:backtest-is-calibrate
        {--catalog-code= : Explicit immutable R2 catalog code}
        {--from= : Explicit historical IS start date in YYYY-MM-DD}
        {--to= : Explicit historical IS end date in YYYY-MM-DD}
        {--output= : Explicit JSON evidence output path}
        {--overwrite : Explicitly replace an existing output file}';

    protected $description = 'Run deterministic Weekly Swing R2 in-sample-only calibration without reading or writing OOS evidence.';

    public function handle(): int
    {
        $catalogCode = trim((string) $this->option('catalog-code'));
        $fromDate = trim((string) $this->option('from'));
        $toDate = trim((string) $this->option('to'));
        $output = trim((string) $this->option('output'));

        if ($catalogCode === '') {
            return $this->blocked('WS_BT_R2_CATALOG_MISSING', 'Explicit --catalog-code is required.');
        }
        if (! $this->validDate($fromDate) || ! $this->validDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked(
                'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE',
                'Explicit --from and --to must be valid YYYY-MM-DD dates with --from <= --to.'
            );
        }
        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked(
                strcmp($toDate, WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) > 0
                    ? 'WS_BT_R2_IS_BOUNDARY_VIOLATION'
                    : 'WS_BT_R2_IS_WINDOW_MISMATCH',
                'R2 requires the exact IS window '.WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
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
                ? $this->laravel->make(WatchlistBacktestIsCalibrationExecutionService::class)
                : app(WatchlistBacktestIsCalibrationExecutionService::class);
            $result = $service->execute($catalogCode, $fromDate, $toDate, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        $this->render($result, $catalogCode, $fromDate, $toDate, $outputPath);

        if (! ($result['is_ready'] ?? false)) {
            return 1;
        }

        return ((int) ($result['calibration']['is_valid_param_count'] ?? 0)) > 0 ? 0 : 1;
    }

    private function render(
        array $result,
        string $catalogCode,
        string $fromDate,
        string $toDate,
        string $outputPath
    ): void {
        $calibration = is_array($result['calibration'] ?? null) ? $result['calibration'] : [];
        $artifact = is_array($result['artifact'] ?? null) ? $result['artifact'] : [];
        $binding = is_array($calibration['best_is_binding'] ?? null) ? $calibration['best_is_binding'] : [];
        $noOos = is_array($artifact['no_oos_read_proof'] ?? null) ? $artifact['no_oos_read_proof'] : [];

        $this->line('status='.(string) ($result['status'] ?? 'BLOCKED'));
        $this->line('reason_code='.(string) ($result['reason_code'] ?? ''));
        $this->line('catalog_code='.$catalogCode);
        $this->line('catalog_version='.(string) ($calibration['catalog_version'] ?? ''));
        $this->line('catalog_count='.(string) ($calibration['param_grid_count'] ?? 0));
        $this->line('catalog_hash='.(string) ($calibration['catalog_hash'] ?? ''));
        $this->line('is_from='.$fromDate);
        $this->line('is_to='.$toDate);
        $this->line('is_trading_date_count='.(string) ($calibration['is_trading_date_count'] ?? 0));
        $this->line('is_trading_date_hash='.(string) ($calibration['is_trading_date_hash'] ?? ''));
        $this->line('is_valid_param_count='.(string) ($calibration['is_valid_param_count'] ?? 0));
        $this->line('is_failed_param_count='.(string) ($calibration['is_failed_param_count'] ?? 0));
        $this->line('is_failure_reason_codes='.implode(',', $calibration['is_failure_reason_codes'] ?? []));
        $this->line('param_id_best_is='.(string) ($binding['param_id_best_is'] ?? ''));
        $this->line('best_is_binding_hash='.(string) ($binding['binding_hash'] ?? ''));
        $this->line('max_requested_market_data_date='.(string) ($noOos['max_requested_market_data_date'] ?? ''));
        $this->line('max_allowed_market_data_date='.(string) ($noOos['max_allowed_market_data_date'] ?? ''));
        $this->line('strict_is_boundary_all_evaluations='.(! empty($noOos['strict_is_boundary_all_evaluations']) ? '1' : '0'));
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
        $this->line('oos_table_unchanged='.(! empty($noOos['oos_table_unchanged']) ? '1' : '0'));
        $this->line('oos_executed=0');
        $this->line('artifact_hash='.(string) ($result['artifact_hash'] ?? ''));
        $this->line('artifact_path='.(string) ($result['write']['path'] ?? $outputPath));
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

        return 'WS_BT_R2_CALIBRATION_FAILED';
    }
}
