<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\AuthoritativeTradingStatusSnapshotService;

class RecordAuthoritativeTradingStatusSnapshotCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:trading-status:record-authoritative-snapshot {manifest?} {--dry-run} {--apply}';

    protected $description = 'Record the Stage 8 source-backed IDX long-suspension snapshot and bounded transition evidence.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.');

            return 1;
        }

        $manifest = $this->argument('manifest') ?: base_path(
            'docs/market_data/evidence/trading_status/stage_8_idx_long_suspension_2026-06-30_v1.json'
        );
        $apply = (bool) $this->option('apply');
        try {
            $result = app(AuthoritativeTradingStatusSnapshotService::class)->process($manifest, $apply);
        } catch (\Throwable $e) {
            $reasonCode = preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)
                ? $matches[1]
                : 'COMMAND_EXECUTION_FAILED';
            $this->renderCommandBlocked($reasonCode, $e->getMessage(), [
                'manifest' => $this->normalizeOptionalPathForDisplay($manifest),
                'operation_mode' => $apply ? 'APPLY' : 'DRY_RUN',
            ]);

            return 1;
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        foreach ([
            'scope_id', 'entry_count', 'inserted_revision_count', 'unchanged_revision_count',
            'source_observation_insert_count', 'status_snapshot_observation_id',
            'transition_search_observation_id', 'observed_as_of', 'transition_search_end',
        ] as $field) {
            if (array_key_exists($field, $result) && $result[$field] !== null) {
                $this->line($field.'='.(string) $result[$field]);
            }
        }
        $this->line('series_application_count=0');
        $this->line('publication_write_count=0');

        return 0;
    }
}
