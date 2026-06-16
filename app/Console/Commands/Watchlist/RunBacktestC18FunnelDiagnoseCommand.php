<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC18FunnelDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC18FunnelDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c18-funnel-diagnose
        {--catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 : Source catalog to diagnose, defaulting to C17 final.}
        {--from=2023-01-02 : Frozen IS start date.}
        {--to=2025-05-21 : Frozen IS end date.}
        {--output=storage/app/watchlist/backtest/c18-funnel-diagnostic.json : Output JSON artifact path.}
        {--param-ids= : Optional comma-separated source param IDs.}
        {--deep-funnel : Run expensive per-date CandidateUniverse/Scoring/Grouping diagnostic.}
        {--progress-every=25 : Print progress every N trading dates during deep funnel.}
        {--overwrite : Overwrite output artifact if it already exists.}';

    protected $description = 'Run C18 IS-only diagnostic-first funnel and monthly coverage audit against the C17 source catalog.';

    public function handle(): int
    {
        $catalogCode = trim((string) $this->option('catalog-code'));
        $fromDate = trim((string) $this->option('from'));
        $toDate = trim((string) $this->option('to'));
        $outputPath = $this->absolutePath(trim((string) $this->option('output')));
        $paramIds = $this->paramIds((string) $this->option('param-ids'));
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C18_PARAM_IDS_INVALID', 'Optional --param-ids must be a comma-separated list of positive integers.');
        }

        try {
            $service = $this->laravel
                ? $this->laravel->make(WatchlistBacktestC18FunnelDiagnosticService::class)
                : app(WatchlistBacktestC18FunnelDiagnosticService::class);
            $result = $service->execute($catalogCode, $fromDate, $toDate, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
                'param_ids' => $paramIds,
                'executed_at' => $toDate.'T23:59:59+07:00',
                'deep_funnel' => (bool) $this->option('deep-funnel'),
                'progress_every' => max(1, (int) $this->option('progress-every')),
                'progress_callback' => function (string $message): void {
                    $this->line($message);
                },
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        if (! ($result['is_ready'] ?? false)) {
            return $this->blocked(
                (string) ($result['reason_code'] ?? 'WS_BT_C18_FUNNEL_DIAGNOSTIC_BLOCKED'),
                (string) ($result['message'] ?? 'C18 funnel diagnostic was blocked.')
            );
        }

        $artifact = is_array($result['artifact'] ?? null) ? $result['artifact'] : [];
        $summary = is_array($artifact['cross_param_summary'] ?? null) ? $artifact['cross_param_summary'] : [];
        $decision = is_array($artifact['c18_catalog_decision'] ?? null) ? $artifact['c18_catalog_decision'] : [];
        $write = is_array($result['write'] ?? null) ? $result['write'] : [];

        $this->line('status=PASS');
        $this->line('reason_code='.(string) ($result['reason_code'] ?? 'WS_BT_C18_FUNNEL_DIAGNOSTIC_READY'));
        $this->line('scope=IS_ONLY_DIAGNOSTIC');
        $this->line('source_catalog_code='.$catalogCode);
        $this->line('diagnostic_param_count='.(string) ($result['diagnostic_param_count'] ?? 0));
        $this->line('max_evaluated_picks_count='.(string) ($summary['max_evaluated_picks_count'] ?? 0));
        $this->line('max_recommended_count_before_price_evaluation='.(string) ($summary['max_recommended_count_before_price_evaluation'] ?? 0));
        $this->line('params_with_empty_evaluation_months='.(string) ($summary['params_with_empty_evaluation_months'] ?? 0));
        $this->line('c18_catalog_implementation_deferred='.(($decision['catalog_implementation_deferred'] ?? true) ? '1' : '0'));
        $this->line('c18_catalog_decision_status='.(string) ($decision['status'] ?? 'C18_CATALOG_IMPLEMENTATION_DEFERRED'));
        $this->line('artifact_hash='.(string) ($result['artifact_hash'] ?? ''));
        $this->line('output_path='.(string) ($write['path'] ?? $outputPath));
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 0;
    }

    private function blocked(string $reasonCode, string $message): int
    {
        $this->line('status=BLOCKED');
        $this->line('reason_code='.$reasonCode);
        $this->line('message='.$message);
        $this->line('scope=IS_ONLY_DIAGNOSTIC');
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 1;
    }

    private function paramIds(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }
        $ids = [];
        foreach (array_filter(array_map('trim', explode(',', $value))) as $item) {
            if (! ctype_digit($item) || (int) $item <= 0) {
                return false;
            }
            $ids[] = (int) $item;
        }

        return array_values(array_unique($ids));
    }

    private function absolutePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        return strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && ($path[2] === '\\' || $path[2] === '/');
    }

    private function reasonCode(\Throwable $e, string $fallback = 'WS_BT_C18_FUNNEL_DIAGNOSTIC_FAILED'): string
    {
        $message = strtoupper($e->getMessage());
        if (preg_match('/\b([A-Z0-9_]{8,})\b/', $message, $matches)) {
            return $matches[1];
        }

        return $fallback;
    }
}
