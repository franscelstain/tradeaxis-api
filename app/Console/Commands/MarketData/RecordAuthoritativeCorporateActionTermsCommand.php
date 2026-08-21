<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\AuthoritativeCorporateActionTermsService;

class RecordAuthoritativeCorporateActionTermsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:events:record-authoritative-terms {manifest?} {--dry-run} {--apply}';

    protected $description = 'Validate or append authoritative corporate-action terms without applying them to any market-data series.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked(
                'COMMAND_CONFLICTING_OPTIONS',
                '--dry-run and --apply cannot be used together.'
            );

            return 1;
        }

        $manifest = $this->argument('manifest') ?: base_path(
            'docs/market_data/records/evidence/legacy/corporate_actions/stage_6_ksei_stock_split_terms_v1.json'
        );
        $apply = (bool) $this->option('apply');

        try {
            $result = app(AuthoritativeCorporateActionTermsService::class)->process($manifest, $apply);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), [
                'manifest' => $this->normalizeOptionalPathForDisplay($manifest),
                'operation_mode' => $apply ? 'APPLY' : 'DRY_RUN',
            ]);

            return 1;
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->line('manifest='.$this->normalizeOptionalPathForDisplay($manifest));
        $this->line('operation_mode='.($apply ? 'APPLY' : 'DRY_RUN'));
        $this->line('scope_id='.$result['scope_id']);
        $this->line('scope_entry_count='.$result['scope_entry_count']);
        $this->line('inserted_revision_count='.$result['inserted_revision_count']);
        $this->line('unchanged_revision_count='.$result['unchanged_revision_count']);
        $this->line('source_observation_insert_count='.$result['source_observation_insert_count']);
        $this->line('series_application_count=0');
        $this->line('next_action='.($apply
            ? 'Audit the stored Stage 6 revisions and immutable source observations; do not apply factors or rebuild publications in this stage.'
            : 'Review the declared scope and validation result, then re-run with --apply to append only the authoritative records.'));

        return 0;
    }
}
