<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestExitModelContractAuditService;
use Illuminate\Console\Command;

class RunBacktestExitModelContractAuditCommand extends Command
{
    protected $signature = 'watchlist:backtest-exit-model-contract-audit
        {--c10-summary= : Explicit C10 batched exit-model summary CSV path}
        {--output= : Explicit JSON contract-audit artifact path}
        {--overwrite : Explicitly replace an existing output file}';

    protected $description = 'Audit exit-model catalog readiness from IS-only C10 evidence without OOS read/write/promotion.';

    public function handle(): int
    {
        $summary = trim((string) $this->option('c10-summary'));
        $output = trim((string) $this->option('output'));
        if ($summary === '' || $output === '') {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit --c10-summary and --output are required.');
        }

        $summaryPath = $this->absolutePath($summary);
        $outputPath = $this->absolutePath($output);
        try {
            $service = $this->laravel
                ? $this->laravel->make(WatchlistBacktestExitModelContractAuditService::class)
                : app(WatchlistBacktestExitModelContractAuditService::class);
            $result = $service->execute($summaryPath, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        if (! ($result['is_ready'] ?? false)) {
            return $this->blocked(
                (string) ($result['reason_code'] ?? 'WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_BLOCKED'),
                (string) ($result['message'] ?? 'Exit-model contract audit failed.')
            );
        }

        $this->render($result, $summaryPath, $outputPath);

        return 0;
    }

    private function render(array $result, string $summaryPath, string $outputPath): void
    {
        $artifact = is_array($result['artifact'] ?? null) ? $result['artifact'] : [];
        $catalog = is_array($artifact['source_catalog'] ?? null) ? $artifact['source_catalog'] : [];
        $c10 = is_array($artifact['c10_summary'] ?? null) ? $artifact['c10_summary'] : [];
        $exitTotals = is_array($c10['exit_totals'] ?? null) ? $c10['exit_totals'] : [];
        $decision = is_array($artifact['decision'] ?? null) ? $artifact['decision'] : [];

        $this->line('status=PASS');
        $this->line('reason_code='.(string) ($result['reason_code'] ?? 'WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_READY'));
        $this->line('catalog_code='.(string) ($catalog['catalog_code'] ?? ''));
        $this->line('catalog_version='.(string) ($catalog['catalog_version'] ?? ''));
        $this->line('catalog_count='.(string) ($catalog['catalog_count'] ?? ''));
        $this->line('catalog_hash='.(string) ($catalog['catalog_hash'] ?? ''));
        $this->line('summary_row_count='.(string) ($c10['row_count'] ?? 0));
        $this->line('source_summary_path='.$summaryPath);
        $this->line('source_summary_sha1='.(string) ($artifact['meta']['source_summary_sha1'] ?? ''));
        $this->line('hit_target_total='.(string) ($exitTotals['hit_target_total'] ?? 0));
        $this->line('hit_stop_total='.(string) ($exitTotals['hit_stop_total'] ?? 0));
        $this->line('timeout_hold_expired_total='.(string) ($exitTotals['timeout_hold_expired_total'] ?? 0));
        $this->line('exit_model_catalog_authorized='.(! empty($decision['exit_model_catalog_authorized']) ? '1' : '0'));
        $this->line('next_decision='.(string) ($decision['next_decision'] ?? ''));
        $this->line('strategy_catalog_created=0');
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
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
        $this->line('strategy_catalog_created=0');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 1;
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

        return 'WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_FAILED';
    }
}
