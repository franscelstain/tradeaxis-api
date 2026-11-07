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
            $service = app(CorporateActionExternalReconciliationService::class);
            $disclosed = $service->inspectFile($path);
            if ($apply) {
                $this->line('operation_mode=PRE_APPLY_DISCLOSURE');
                $this->line('scope=corporate_action_reconciliation:'.$disclosed['scope_start'].'..'.$disclosed['scope_end']);
                $this->line('resolved_immutable_ids=manifest_sha256='.$disclosed['manifest_sha256'].';reconciliation_uid='.$disclosed['reconciliation_uid']);
                $this->line('expected_row_effects=append at most one idempotent reconciliation evidence row; canonical actions unchanged');
                $this->line('config_hash='.$disclosed['config_hash']);
                $this->line('pointer_impact=UNCHANGED');
                $this->line('reason=authoritative_corpus_reconciliation:'.$disclosed['authority_name']);
                $result = $service->applyDisclosedFile($path, $disclosed);
            } else {
                $result = $disclosed;
            }
        } catch (\Throwable $e) {
            $message = (string) $e->getMessage();
            $reason = strpos($message, 'COMMAND_CORPORATE_ACTION_RECONCILIATION_PREFLIGHT_CHANGED') !== false
                ? 'COMMAND_CORPORATE_ACTION_RECONCILIATION_PREFLIGHT_CHANGED'
                : 'COMMAND_EXECUTION_FAILED';
            $this->renderCommandBlocked($reason, $message, []);
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
