<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestOosProofService;
use Illuminate\Console\Command;

class RunBacktestOosProofCommand extends Command
{
    protected $signature = 'watchlist:backtest-oos-proof
        {--from= : Explicit historical start date in YYYY-MM-DD}
        {--to= : Explicit historical end date in YYYY-MM-DD}
        {--output= : Explicit JSON evidence output path}
        {--overwrite : Explicitly replace an existing output file}';

    protected $description = 'Run canonical chronological 70/30 Weekly Swing out-of-sample proof without promotion.';

    public function handle()
    {
        $fromDate = trim((string) $this->option('from'));
        $toDate = trim((string) $this->option('to'));
        $output = trim((string) $this->option('output'));

        if (! $this->validDate($fromDate) || ! $this->validDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Explicit --from and --to must be valid YYYY-MM-DD dates with --from <= --to.');
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
                ? $this->laravel->make(WatchlistBacktestOosProofService::class)
                : app(WatchlistBacktestOosProofService::class);
            $result = $service->execute($fromDate, $toDate, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        $this->render($result, $fromDate, $toDate, $outputPath);

        if (! ($result['is_ready'] ?? false)) {
            return 1;
        }

        return ! empty($result['oos_acceptance']['pass']) ? 0 : 1;
    }

    private function render(array $result, string $fromDate, string $toDate, string $outputPath): void
    {
        $split = $result['split'] ?? [];
        $calibration = $result['calibration'] ?? [];
        $binding = $result['best_is_binding'] ?? $calibration['best_is_binding'] ?? [];
        $oosMetrics = $result['oos_runtime']['artifact']['metrics']['canonical_eval_metrics'] ?? [];
        $acceptance = $result['oos_acceptance'] ?? $result['artifact']['oos_acceptance'] ?? [];
        $persistence = $result['persistence'] ?? [];

        $status = ! ($result['is_ready'] ?? false)
            ? 'BLOCKED'
            : (! empty($acceptance['pass']) ? 'PASS' : 'OOS_ACCEPTANCE_FAIL');
        $this->line('status='.$status);
        $this->line('reason_code='.(string) ($result['reason_code'] ?? 'WS_BT_OOS_PROOF_MISSING'));
        $this->line('requested_from='.$fromDate);
        $this->line('requested_to='.$toDate);
        $this->line('split_rule='.(string) ($split['split_rule'] ?? ''));
        $this->line('is_from='.(string) ($split['is_from'] ?? ''));
        $this->line('is_to='.(string) ($split['is_to'] ?? ''));
        $this->line('is_trading_date_count='.(string) ($split['is_trading_date_count'] ?? 0));
        $this->line('oos_from='.(string) ($split['oos_from'] ?? ''));
        $this->line('oos_to='.(string) ($split['oos_to'] ?? ''));
        $this->line('oos_trading_date_count='.(string) ($split['oos_trading_date_count'] ?? 0));
        $this->line('param_grid_count='.(string) ($calibration['param_grid_count'] ?? 0));
        $this->line('is_valid_param_count='.(string) ($calibration['is_valid_param_count'] ?? 0));
        $this->line('is_failed_param_count='.(string) ($calibration['is_failed_param_count'] ?? 0));
        $this->line('is_failure_reason_codes='.implode(',', $calibration['is_failure_reason_codes'] ?? []));
        $this->line('is_max_picks_count='.(string) ($calibration['is_max_picks_count'] ?? 0));
        $this->line('is_max_days_covered='.(string) ($calibration['is_max_days_covered'] ?? 0));
        $this->line('param_id_best_is='.(string) ($binding['param_id_best_is'] ?? ''));
        $this->line('is_eval_id='.(string) ($binding['is_eval_id'] ?? ''));
        $this->line('is_picks_count='.(string) ($binding['is_metrics']['picks_count'] ?? 0));
        $this->line('is_days_covered='.(string) ($binding['is_metrics']['days_covered'] ?? 0));
        $this->line('is_calibration_valid='.($binding !== [] ? '1' : '0'));
        $this->line('oos_id='.(string) ($persistence['oos_id'] ?? ''));
        $this->line('oos_picks_count='.(string) ($oosMetrics['picks_count'] ?? 0));
        $this->line('oos_days_covered='.(string) ($oosMetrics['days_covered'] ?? 0));
        $this->line('oos_acceptance_pass='.(! empty($acceptance['pass']) ? '1' : '0'));
        $this->line('failed_oos_gates='.implode(',', $acceptance['failed_gates'] ?? []));
        $this->line('artifact_hash='.(string) ($result['artifact_hash'] ?? ''));
        $this->line('artifact_path='.(string) ($result['write']['path'] ?? $outputPath));
        $this->line('production_ready=0');
    }

    private function blocked(string $reasonCode, string $message): int
    {
        $this->error('status=BLOCKED');
        $this->line('reason_code='.$reasonCode);
        $this->line('error='.$message);
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

        return 'WS_BT_OOS_PROOF_MISSING';
    }
}
