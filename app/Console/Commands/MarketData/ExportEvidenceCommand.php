<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataEvidenceExportService;

class ExportEvidenceCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:evidence:export {--run_id=} {--correction_id=} {--replay_id=} {--trade_date=} {--output_dir=}';

    protected $description = 'Export requested-date run, correction, or replay evidence pack artifacts.';

    public function handle()
    {
        $runId = $this->option('run_id');
        $correctionId = $this->option('correction_id');
        $replayId = $this->option('replay_id');

        $selected = array_filter([
            'run_id' => $runId,
            'correction_id' => $correctionId,
            'replay_id' => $replayId,
        ], function ($value) {
            return $value !== null && $value !== '';
        });

        if (count($selected) === 0) {
            $this->error('Exactly one of --run_id, --correction_id, or --replay_id must be provided.');
            return 1;
        }

        if (count($selected) > 1) {
            $this->error('Evidence export selector is ambiguous. Provide exactly one of --run_id, --correction_id, or --replay_id.');
            return 1;
        }

        $service = app(MarketDataEvidenceExportService::class);
        if ($runId) {
            $result = $service->exportRunEvidence($runId, $this->option('output_dir') ?: null);
        } elseif ($correctionId) {
            $result = $service->exportCorrectionEvidence($correctionId, $this->option('output_dir') ?: null);
        } else {
            $result = $service->exportReplayEvidence($replayId, $this->option('trade_date') ?: null, $this->option('output_dir') ?: null);
        }

        $this->info('output_dir='.$result['output_dir']);
        $this->line('files='.implode(',', $result['files']));

        return 0;
    }
}
