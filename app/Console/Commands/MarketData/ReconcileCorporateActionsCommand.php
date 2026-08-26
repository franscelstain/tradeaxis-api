<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\CorporateActionExternalReconciliationService;

class ReconcileCorporateActionsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:reconcile:corporate-actions {manifest : Path to an authoritative exchange/CSD corpus manifest} {--dry-run} {--apply}';
    protected $description = 'Bidirectionally reconcile verified corporate actions against an authoritative exchange/CSD corpus.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $dryRun = (bool) $this->option('dry-run');
        if ($apply && $dryRun) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', []);
            return 1;
        }
        if (! $apply && ! $dryRun) {
            $this->renderCommandBlocked('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', 'Pass --dry-run to inspect or --apply to persist reconciliation evidence.', []);
            return 1;
        }

        $path = (string) $this->argument('manifest');
        $isAbsolute = preg_match('~^[A-Za-z]:[\\/]~', $path) === 1
            || substr($path, 0, 1) === '/'
            || substr($path, 0, 1) === '\\';
        if (! $isAbsolute) {
            $path = base_path($path);
        }

        try {
            $result = app(CorporateActionExternalReconciliationService::class)->reconcileFile($path, $apply);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), []);
            return 1;
        }

        $this->info('status=PASS');
        $this->line('operation_mode='.($apply ? 'APPLIED' : 'DRY_RUN'));
        foreach (['authority_name','authority_class','scope_start','scope_end','manifest_sha256','manifest_event_count','platform_event_count','missing_platform_count','unexpected_platform_count','mismatch_count','reconciliation_state'] as $key) {
            $value = $result[$key];
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            $this->line($key.'='.$value);
        }
        $this->line('scope_complete='.($result['scope_complete'] ? 'true' : 'false'));
        $this->line('persisted='.($result['persisted'] ? 'true' : 'false'));
        $this->line('qualification='.($result['details']['qualification'] ?? 'UNKNOWN'));

        if ($result['reconciliation_state'] === 'FAIL') {
            return 2;
        }

        return 0;
    }
}
