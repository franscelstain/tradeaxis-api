<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\AuthoritativeExchangeMarketStructureService;

class RecordAuthoritativeExchangeMarketStructureCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:market-structure:record-authoritative-rules {manifest?} {--dry-run} {--apply}';

    protected $description = 'Validate or append authoritative IDX market-structure tiers without applying them to any series.';

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
            'docs/market_data/evidence/market_structure/stage_7_idx_regular_market_structure_v1.json'
        );
        $apply = (bool) $this->option('apply');

        try {
            $result = app(AuthoritativeExchangeMarketStructureService::class)->process($manifest, $apply);
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
        $this->line('evidence_correction_revision_count='.$result['evidence_correction_revision_count']);
        $this->line('inserted_price_band_tier_count='.$result['inserted_price_band_tier_count']);
        $this->line('inserted_tick_size_tier_count='.$result['inserted_tick_size_tier_count']);
        $this->line('source_observation_insert_count='.$result['source_observation_insert_count']);
        $this->line('series_application_count=0');
        $this->line('publication_write_count=0');
        $this->line('next_action='.($apply
            ? 'Audit Stage 7 revisions, tiers, coverage scope, and immutable evidence. Do not resolve or apply tiers to any series in this stage.'
            : 'Review the frozen Stage 7 scope, then re-run with --apply to append authority records only.'));

        return 0;
    }
}
