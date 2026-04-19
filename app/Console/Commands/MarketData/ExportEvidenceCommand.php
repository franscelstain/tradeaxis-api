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

        if ($replayId && ! $this->option('trade_date')) {
            $this->error('Replay evidence export requires --trade_date; latest-row resolution is not allowed.');

            return 1;
        }

        try {
            $service = app(MarketDataEvidenceExportService::class);
            if ($runId) {
                $result = $service->exportRunEvidence($runId, $this->option('output_dir') ?: null);
            } elseif ($correctionId) {
                $result = $service->exportCorrectionEvidence($correctionId, $this->option('output_dir') ?: null);
            } else {
                $result = $service->exportReplayEvidence($replayId, $this->option('trade_date') ?: null, $this->option('output_dir') ?: null);
            }
        } catch (\Throwable $e) {
            $this->error('error='.$e->getMessage());

            return 1;
        }

        $selector = isset($result['selector']) && is_array($result['selector']) ? $result['selector'] : [];
        $summary = isset($result['summary']) && is_array($result['summary']) ? $result['summary'] : [];

        if (isset($selector['type'])) {
            $this->info('selector='.(string) $selector['type']);
        }

        if (isset($selector['id'])) {
            $this->line('selector_id='.(string) $selector['id']);
        }

        foreach ($summary as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $this->line($key.'='.$this->stringifyValue($value));
        }

        $this->line('output_dir='.$this->normalizePathForDisplay($result['output_dir']));
        $this->line('file_count='.(string) ($result['file_count'] ?? count($result['files'] ?? [])));
        $this->line('files='.implode(',', $result['files']));

        return 0;
    }

    private function stringifyValue($value)
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
