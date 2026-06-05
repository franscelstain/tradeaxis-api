<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataBackfillService;

class BackfillMarketDataCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:backfill {start_date?} {end_date?} {--source_mode=} {--input_file=} {--output_dir=} {--continue_on_error}';

    protected $description = 'Historical import-only backfill per trading-date range.';

    public function handle()
    {
        if (! $this->argument('start_date') || ! $this->argument('end_date')) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'start_date and end_date are required.', [
                'start_date' => $this->argument('start_date'),
                'end_date' => $this->argument('end_date'),
            ]);

            return 1;
        }

        if (! $this->validateDateString($this->argument('start_date'), 'start_date')
            || ! $this->validateDateString($this->argument('end_date'), 'end_date')
            || ! $this->validateSourceModeString($this->option('source_mode'))) {
            return 1;
        }

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
                .(isset($case['source_file_hash']) ? ' | source_file_hash='.$case['source_file_hash'] : '')
                .(isset($case['source_file_hash_algorithm']) ? ' | source_file_hash_algorithm='.$case['source_file_hash_algorithm'] : '')
                .(isset($case['source_file_size_bytes']) ? ' | source_file_size_bytes='.$case['source_file_size_bytes'] : '')
                .(isset($case['source_file_row_count']) ? ' | source_file_row_count='.$case['source_file_row_count'] : '')
                .(isset($case['accepted_row_count']) ? ' | accepted_row_count='.$case['accepted_row_count'] : '')
                .(isset($case['rejected_row_count']) ? ' | rejected_row_count='.$case['rejected_row_count'] : '')
                .(isset($case['invalid_row_count']) ? ' | invalid_row_count='.$case['invalid_row_count'] : '')
                .(isset($case['source_attempt_event_type']) ? ' | source_attempt_event_type='.$case['source_attempt_event_type'] : '')
                .(isset($case['source_attempt_count']) ? ' | source_attempt_count='.$case['source_attempt_count'] : '')
                .(isset($case['bar_mutation_changed_count']) ? ' | bar_mutation_changed_count='.$case['bar_mutation_changed_count'] : '')
                .(isset($case['bar_mutation_inserted_count']) ? ' | bar_mutation_inserted_count='.$case['bar_mutation_inserted_count'] : '')
                .(isset($case['bar_mutation_updated_count']) ? ' | bar_mutation_updated_count='.$case['bar_mutation_updated_count'] : '')
                .(isset($case['bar_mutation_unchanged_count']) ? ' | bar_mutation_unchanged_count='.$case['bar_mutation_unchanged_count'] : '')
                .(isset($case['affected_ticker_count']) ? ' | affected_ticker_count='.$case['affected_ticker_count'] : '')
                .(isset($case['affected_trade_date_count']) ? ' | affected_trade_date_count='.$case['affected_trade_date_count'] : '')
                .(isset($case['affected_start_date']) ? ' | affected_start_date='.$case['affected_start_date'] : '')
                .(isset($case['affected_end_date']) ? ' | affected_end_date='.$case['affected_end_date'] : '')
                .(isset($case['max_indicator_dependency_trading_days']) ? ' | max_indicator_dependency_trading_days='.$case['max_indicator_dependency_trading_days'] : '')
                .(isset($case['indicator_reprocess_state']) ? ' | indicator_reprocess_state='.$case['indicator_reprocess_state'] : '')
                .(isset($case['indicator_reprocess_execution_state']) ? ' | indicator_reprocess_execution_state='.$case['indicator_reprocess_execution_state'] : '')
                .(isset($case['indicator_reprocessed_trade_date_count']) ? ' | indicator_reprocessed_trade_date_count='.$case['indicator_reprocessed_trade_date_count'] : '')
                .(isset($case['indicator_reprocessed_trade_dates']) ? ' | indicator_reprocessed_trade_dates='.$case['indicator_reprocessed_trade_dates'] : '')
                .(isset($case['eligibility_reprocess_execution_state']) ? ' | eligibility_reprocess_execution_state='.$case['eligibility_reprocess_execution_state'] : '')
                .(isset($case['eligibility_reprocessed_trade_date_count']) ? ' | eligibility_reprocessed_trade_date_count='.$case['eligibility_reprocessed_trade_date_count'] : '')
                .(isset($case['eligibility_reprocessed_trade_dates']) ? ' | eligibility_reprocessed_trade_dates='.$case['eligibility_reprocessed_trade_dates'] : '')
                .(isset($case['publication_impact_state']) ? ' | publication_impact_state='.$case['publication_impact_state'] : '')
                .(isset($case['publication_reprocess_state']) ? ' | publication_reprocess_state='.$case['publication_reprocess_state'] : '')
                .(isset($case['publication_reprocess_republished_trade_date_count']) ? ' | publication_reprocess_republished_trade_date_count='.$case['publication_reprocess_republished_trade_date_count'] : '')
                .(isset($case['publication_reprocess_republished_trade_dates']) ? ' | publication_reprocess_republished_trade_dates='.$case['publication_reprocess_republished_trade_dates'] : '')
                .(isset($case['publication_reprocess_candidate_trade_dates']) ? ' | publication_reprocess_candidate_trade_dates='.$case['publication_reprocess_candidate_trade_dates'] : '')
                .(isset($case['publication_reprocess_readable_correction_candidate_trade_dates']) ? ' | publication_reprocess_readable_correction_candidate_trade_dates='.$case['publication_reprocess_readable_correction_candidate_trade_dates'] : '')
                .(isset($case['publication_reprocess_blocked_trade_dates']) ? ' | publication_reprocess_blocked_trade_dates='.$case['publication_reprocess_blocked_trade_dates'] : '')
                .(isset($case['publication_reprocess_failed_trade_dates']) ? ' | publication_reprocess_failed_trade_dates='.$case['publication_reprocess_failed_trade_dates'] : '')
                .(isset($case['publication_reprocess_blocked_reason_code']) ? ' | publication_reprocess_blocked_reason_code='.$case['publication_reprocess_blocked_reason_code'] : '')
                .(isset($case['publication_reprocess_failure_reason_code']) ? ' | publication_reprocess_failure_reason_code='.$case['publication_reprocess_failure_reason_code'] : '')
                .(isset($case['publication_reprocess_republication_mode']) ? ' | publication_reprocess_republication_mode='.$case['publication_reprocess_republication_mode'] : '')
                .(isset($case['publication_reprocess_correction_ids']) ? ' | publication_reprocess_correction_ids='.$case['publication_reprocess_correction_ids'] : '')
                .(isset($case['publication_reprocess_correction_id']) ? ' | publication_reprocess_correction_id='.$case['publication_reprocess_correction_id'] : '')
                .(isset($case['recovered_row_apply_state']) ? ' | recovered_row_apply_state='.$case['recovered_row_apply_state'] : '')
                .(isset($case['recovered_row_count']) ? ' | recovered_row_count='.$case['recovered_row_count'] : '')
                .(isset($case['coverage_reason_code']) ? ' | coverage_reason_code='.$case['coverage_reason_code'] : '')
                .(isset($case['source_summary']) ? ' | source_summary='.$case['source_summary'] : '')
                .(isset($case['final_outcome_note']) ? ' | final_outcome_note='.$case['final_outcome_note'] : '')
                .(isset($case['error_message']) ? ' | error='.$case['error_message'] : '')
            );
        }

        return $summary['all_passed'] ? 0 : 1;
    }
}
