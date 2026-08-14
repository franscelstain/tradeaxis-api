<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\StageEightCorpusReconstructionService;

class ReconstructCurrentCorpusCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:corpus:reconstruct-current {--dry-run} {--apply} {--resume} {--output_dir=}';

    protected $description = 'Run the bounded Stage 8 Yahoo re-ingest/recompute/republication campaign without replay.';

    public function handle()
    {
        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.');
            return 1;
        }
        if ((bool) $this->option('resume') && ! (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_RESUME_REQUIRES_APPLY', '--resume requires --apply.');
            return 1;
        }

        try {
            $service = app(StageEightCorpusReconstructionService::class);
            $result = (bool) $this->option('apply')
                ? $service->execute([
                    'resume' => (bool) $this->option('resume'),
                    'output_dir' => $this->option('output_dir') ?: null,
                ])
                : $service->plan();
        } catch (\Throwable $e) {
            $reasonCode = preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)
                ? $matches[1]
                : 'COMMAND_EXECUTION_FAILED';
            $this->renderCommandBlocked($reasonCode, $e->getMessage());
            return 1;
        }

        $this->info('status='.(string) $result['status']);
        foreach (['campaign_id', 'campaign_uid', 'scope_start', 'scope_end', 'target_date_count', 'ticker_count', 'baseline_max_publication_id', 'acquisition_batch_size', 'acquisition_batch_count', 'admission_decision_id', 'intentional_dataset_start', 'admitted_from', 'stage_9_replay'] as $field) {
            if (array_key_exists($field, $result)) {
                $this->line($field.'='.(string) $result[$field]);
            }
        }
        if (isset($result['source_plan'])) {
            foreach (['window_count', 'estimated_http_requests', 'configured_concurrency'] as $field) {
                if (array_key_exists($field, $result['source_plan'])) {
                    $this->line('source_'.$field.'='.(string) $result['source_plan'][$field]);
                }
            }
        }
        if (isset($result['oracle'])) {
            $this->line('oracle_violation_count='.(int) $result['oracle']['violation_count']);
            foreach ($result['oracle']['violations'] as $field => $count) {
                $this->line($field.'='.(int) $count);
            }
        }

        return in_array($result['status'], ['PLAN_ONLY', 'COMPLETE', 'ALREADY_COMPLETE'], true) ? 0 : 1;
    }
}
