<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataBackfillService;

class BackfillMarketDataCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:backfill {start_date} {end_date} {--source_mode=} {--input_file=} {--output_dir=} {--continue_on_error}';

    protected $description = 'Historical import-only backfill per trading-date range.';

    public function handle()
    {
        $previousInputFile = config('market_data.source.local_input_file');
        $configuredOverride = false;

        if ($this->sourceMode() === 'manual_file' && $this->option('input_file')) {
            config()->set('market_data.source.local_input_file', $this->option('input_file'));
            $configuredOverride = true;
        }

        try {
            $summary = app(MarketDataBackfillService::class)->execute(
                $this->argument('start_date'),
                $this->argument('end_date'),
                $this->sourceMode(),
                $this->option('output_dir') ?: null,
                (bool) $this->option('continue_on_error')
            );
        } finally {
            if ($configuredOverride) {
                config()->set('market_data.source.local_input_file', $previousInputFile);
            }
        }

        $this->info('suite='.$summary['suite']);
        $this->line('start_date='.$summary['range']['start_date']);
        $this->line('end_date='.$summary['range']['end_date']);
        $this->line('source_mode='.(string) $summary['source_mode']);
        if (isset($summary['request_mode'])) {
            $this->line('request_mode='.(string) $summary['request_mode']);
        }
        if (array_key_exists('all_imported', $summary)) {
            $this->line('all_imported='.(int) $summary['all_imported']);
        }
        $this->line('all_passed='.(int) $summary['all_passed']);
        $this->line('output_dir='.$this->normalizePathForDisplay($summary['output_dir']));
        if ($configuredOverride) {
            $this->line('input_file='.$this->normalizeOptionalPathForDisplay((string) $this->option('input_file')));
        }

        if (isset($summary['source_attempt_telemetry_artifact']) && $summary['source_attempt_telemetry_artifact'] !== null && $summary['source_attempt_telemetry_artifact'] !== '') {
            $this->line('source_attempt_telemetry_artifact='.$this->normalizePathForDisplay($summary['source_attempt_telemetry_artifact']));
        }

        foreach ($summary['cases'] as $case) {
            $this->line(
                'requested_date='.$case['requested_date']
                .' | status='.$case['status']
                .(isset($case['import_status']) ? ' | import_status='.$case['import_status'] : '')
                .(isset($case['run_id']) ? ' | run_id='.$case['run_id'] : '')
                .(isset($case['import_stage_reached']) ? ' | import_stage_reached='.$case['import_stage_reached'] : '')
                .(isset($case['import_bars_rows_written']) ? ' | import_bars_rows_written='.$case['import_bars_rows_written'] : '')
                .(isset($case['import_invalid_bar_count']) ? ' | import_invalid_bar_count='.$case['import_invalid_bar_count'] : '')
                .(isset($case['source_name']) ? ' | source_name='.$case['source_name'] : '')
                .(isset($case['source_input_file']) ? ' | source_input_file='.$this->normalizeOptionalPathForDisplay($case['source_input_file']) : '')
                .(isset($case['source_attempt_event_type']) ? ' | source_attempt_event_type='.$case['source_attempt_event_type'] : '')
                .(isset($case['source_attempt_count']) ? ' | source_attempt_count='.$case['source_attempt_count'] : '')
                .(isset($case['source_summary']) ? ' | source_summary='.$case['source_summary'] : '')
                .(isset($case['final_outcome_note']) ? ' | final_outcome_note='.$case['final_outcome_note'] : '')
                .(isset($case['error_message']) ? ' | error='.$case['error_message'] : '')
            );
        }

        return $summary['all_passed'] ? 0 : 1;
    }
}
