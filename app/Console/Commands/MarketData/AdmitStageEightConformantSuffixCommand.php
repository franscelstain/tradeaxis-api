<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\StageEightCorpusAdmissionService;

class AdmitStageEightConformantSuffixCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:corpus:admit-conformant-suffix {--campaign_id=} {--dry-run} {--apply}';

    protected $description = 'Measure and append the Stage 8 conformant suffix admission decision without reconstructing or replaying it.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.');

            return 1;
        }

        try {
            $result = app(StageEightCorpusAdmissionService::class)->evaluate(
                $this->option('campaign_id') ?: null,
                (bool) $this->option('apply')
            );
        } catch (\Throwable $e) {
            $reasonCode = preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)
                ? $matches[1]
                : 'COMMAND_EXECUTION_FAILED';
            $this->renderCommandBlocked($reasonCode, $e->getMessage());

            return 1;
        }

        $this->info('status='.$result['status']);
        foreach ([
            'admission_decision_id', 'decision_uid', 'source_campaign_id', 'measured_start',
            'measured_through', 'measured_date_count', 'admitted_from', 'admitted_date_count',
            'boundary_predecessor', 'boundary_predecessor_pass', 'frontier_ratio',
            'measurement_input_hash', 'status_revision_set_hash', 'stage_9_replay',
        ] as $field) {
            if (array_key_exists($field, $result) && $result[$field] !== null) {
                $value = is_bool($result[$field]) ? ($result[$field] ? 'true' : 'false') : $result[$field];
                $this->line($field.'='.(string) $value);
            }
        }
        $this->line('reconstruction_write_count=0');

        return 0;
    }
}
