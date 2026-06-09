<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use Illuminate\Console\Command;

class RunBacktestPublishedPriceProofCommand extends Command
{
    protected $signature = 'watchlist:backtest-published-price-proof
        {--from= : Explicit replay start date in YYYY-MM-DD}
        {--to= : Explicit replay end date in YYYY-MM-DD}
        {--output= : Explicit JSON artifact output path}
        {--overwrite : Explicitly replace an existing output file}';

    protected $description = 'Run deterministic Weekly Swing backtest proof against official published EOD prices and trading calendar.';

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
                ? $this->laravel->make(WatchlistBacktestPublishedPriceRuntimeService::class)
                : app(WatchlistBacktestPublishedPriceRuntimeService::class);
            $result = $service->execute($fromDate, $toDate, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        if (! ($result['is_ready'] ?? false)) {
            return $this->blocked(
                $result['reason_code'] ?? 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_NOT_READY',
                $this->firstDiagnosticMessage($result['diagnostics'] ?? [])
            );
        }

        $artifact = $result['artifact'];
        $priceManifest = $result['price_read']['price_series_manifest'] ?? [];
        $calendarCoverage = $result['calendar']['coverage'] ?? [];

        $this->info('status=PASS');
        $this->line('reason_code='.(string) $result['reason_code']);
        $this->line('replay_from='.$fromDate);
        $this->line('replay_to='.$toDate);
        $this->line('replay_date_count='.(string) ($calendarCoverage['replay_date_count'] ?? 0));
        $this->line('calendar_date_count='.(string) ($calendarCoverage['calendar_date_count'] ?? 0));
        $this->line('required_price_date_count='.(string) ($priceManifest['required_price_date_count'] ?? 0));
        $this->line('resolved_price_date_count='.(string) ($priceManifest['resolved_price_date_count'] ?? 0));
        $this->line('evaluated_trade_count='.(string) ($result['evaluated_trade_count'] ?? 0));
        $this->line('diagnostic_count='.(string) ($result['diagnostic_count'] ?? 0));
        $this->line('metrics_ready='.(! empty($result['metrics_ready']) ? '1' : '0'));
        $this->line('metric_required_fields_available='.(! empty($result['metric_sufficiency_available']) ? '1' : '0'));
        $this->line('metric_thresholds_resolved='.(! empty($result['metric_thresholds_resolved']) ? '1' : '0'));
        $this->line('metric_calibration_valid='.(! empty($result['metric_calibration_valid']) ? '1' : '0'));
        $this->line('metric_min_trades='.(string) ($result['metric_gating_thresholds']['min_trades'] ?? ''));
        $this->line('metric_min_days_covered='.(string) ($result['metric_gating_thresholds']['min_days_covered'] ?? ''));
        $this->line('metric_coverage_threshold_rule='.(string) ($result['metric_coverage_threshold_rule'] ?? ''));
        $this->line('artifact_hash='.(string) ($result['artifact_hash'] ?? ''));
        $this->line('artifact_path='.(string) ($result['write']['path'] ?? $outputPath));
        $this->line('artifact_file_sha1='.(string) ($result['write']['sha1'] ?? ''));
        $this->line('production_ready=0');

        return 0;
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

    private function firstDiagnosticMessage(array $diagnostics): string
    {
        foreach ($diagnostics as $diagnostic) {
            if (! empty($diagnostic['message'])) {
                return (string) $diagnostic['message'];
            }
        }

        return 'Published-price runtime proof failed closed.';
    }

    private function reasonCode(\Throwable $e): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_NOT_READY';
    }
}
