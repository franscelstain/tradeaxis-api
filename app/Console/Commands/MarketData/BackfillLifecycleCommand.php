<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\ManualSourceInputContext;

use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;

class BackfillLifecycleCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:backfill:lifecycle
        {start_date?}
        {end_date?}
        {--source_mode=api}
        {--input_file=}
        {--output_dir=}
        {--plan}
        {--resume}
        {--only-failed}
        {--continue-on-error}
        {--stop-on-error}
        {--collect-all-errors}
        {--max-dates-per-run=}
        {--with-evidence}
        {--with-replay}
        {--no-replay}
        {--diagnose-source}';

    protected $description = 'Run API range-window backfill through import, promote, evidence, replay fixture, and replay verify per trading date.';

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

        $inputContext = app(ManualSourceInputContext::class);
        $previousInputFile = $inputContext->path();
        $configuredOverride = false;

        if (($this->option('source_mode') ?: 'api') === 'manual_file' && $this->option('input_file')) {
            $inputContext->set($this->option('input_file'));
            $configuredOverride = true;
        }

        try {
            $summary = app(BackfillLifecycleOrchestrator::class)->execute(
                $this->argument('start_date'),
                $this->argument('end_date'),
                $this->option('source_mode') ?: 'api',
                [
                    'input_file' => $this->option('input_file') ?: null,
                    'output_dir' => $this->option('output_dir') ?: null,
                    'plan' => (bool) $this->option('plan'),
                    'resume' => (bool) $this->option('resume'),
                    'only_failed' => (bool) $this->option('only-failed'),
                    'continue_on_error' => (bool) $this->option('continue-on-error'),
                    'stop_on_error' => (bool) $this->option('stop-on-error'),
                    'collect_all_errors' => (bool) $this->option('collect-all-errors'),
                    'max_dates_per_run' => $this->option('max-dates-per-run') !== null && $this->option('max-dates-per-run') !== ''
                        ? (int) $this->option('max-dates-per-run')
                        : null,
                    'with_evidence' => (bool) $this->option('with-evidence'),
                    'with_replay' => (bool) $this->option('with-replay'),
                    'no_replay' => (bool) $this->option('no-replay'),
                    'diagnose_source' => (bool) $this->option('diagnose-source'),
                ]
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage());

            return 1;
        } finally {
            if ($configuredOverride) {
                $inputContext->set($previousInputFile);
            }
        }

        $this->line('source_mode='.(string) $summary['source_mode']);
        $this->line('source_acquisition_mode='.(string) $summary['source_acquisition_mode']);
        if (! empty($summary['source_acquisition_batch_id'])) {
            $this->line('source_acquisition_batch_id='.(string) $summary['source_acquisition_batch_id']);
        }
        $this->line('requested_start='.(string) $summary['requested_start']);
        $this->line('requested_end='.(string) $summary['requested_end']);
        $this->line('warmup_start='.(string) $summary['warmup_start']);
        $this->line('window_count='.(string) $summary['window_count']);
        $this->line('estimated_http_requests='.(string) $summary['estimated_http_requests']);
        $this->line('configured_concurrency='.(string) ($summary['configured_concurrency'] ?? ''));
        $this->line('ticker_count='.(string) $summary['ticker_count']);
        $this->line('trading_dates='.(string) $summary['trading_date_count']);
        $this->line('mode='.(string) $summary['mode']);
        $this->line('with_evidence='.(empty($summary['with_evidence']) ? 'false' : 'true'));
        $this->line('with_replay='.(empty($summary['with_replay']) ? 'false' : 'true'));
        $this->line('output_dir='.$this->normalizePathForDisplay((string) $summary['output_dir']));
        if (! empty($summary['input_file'])) {
            $this->line('input_file='.$this->normalizeOptionalPathForDisplay((string) $summary['input_file']));
        }

        foreach (['stage', 'source_acquisition_state', 'source_final_status', 'publishability_state', 'reason_code', 'failure_scope', 'failed_ticker', 'failed_window_start', 'failed_window_end', 'http_status', 'failed_checkpoint_total', 'failed_checkpoint_eligible', 'failed_checkpoint_retried', 'failed_checkpoint_retry_success', 'failed_checkpoint_retry_failed', 'retry_success_count', 'retry_failed_count', 'failed_checkpoint_skipped', 'skipped_failed_checkpoint_count', 'skipped_failed_checkpoint_reasons', 'failed_ticker_count', 'failed_window_count', 'skipped_checkpoint_count', 'diagnostic_path'] as $field) {
            if (array_key_exists($field, $summary) && $summary[$field] !== null && $summary[$field] !== '') {
                $value = is_array($summary[$field]) ? json_encode($summary[$field], JSON_UNESCAPED_SLASHES) : (string) $summary[$field];
                if ($field === 'diagnostic_path') {
                    $value = $this->normalizePathForDisplay($value);
                }
                $this->line($field.'='.$value);
            }
        }
        if ($this->getOutput()->isVerbose() && ! empty($summary['provider_error_sample'])) {
            $this->line('provider_error_sample='.(string) $summary['provider_error_sample']);
        }
        if (! empty($summary['sanitized_url'])) {
            $this->line('sanitized_url='.(string) $summary['sanitized_url']);
        }

        if (($summary['status'] ?? null) === 'PLAN_ONLY') {
            $this->line('status=PLAN_ONLY');
            return 0;
        }

        if (in_array(($summary['status'] ?? null), ['BLOCKED', 'NOOP', 'SOURCE_RETRY_SUCCESS', 'SOURCE_DIAGNOSTIC_SUCCESS', 'SOURCE_DIAGNOSTIC_PARTIAL'], true)
            && empty($summary['cases'])) {
            $this->line('status='.(string) $summary['status']);
            return ! empty($summary['all_passed']) ? 0 : 1;
        }

        foreach ($summary['cases'] as $case) {
            $this->line(
                (string) $case['requested_date']
                .' | run_id='.(string) ($case['run_id'] ?? '')
                .' | tickers='.(string) ($case['tickers_success'] ?? '').'/'.(string) ($case['tickers_expected'] ?? '')
                .' | import='.(string) ($case['import_status'] ?? '')
                .' | coverage='.(string) ($case['coverage_gate_state'] ?? '')
                .' | promote='.(string) ($case['promote_status'] ?? '')
                .' | evidence='.(string) ($case['evidence_status'] ?? '')
                .' | fixture='.(string) ($case['fixture_status'] ?? '')
                .' | replay='.(string) ($case['replay_status'] ?? '')
                .' | readable='.(! empty($case['readable']) ? 'YES' : 'NO')
                .(isset($case['bar_mutation_changed_count']) ? ' | bar_mutation_changed_count='.(string) $case['bar_mutation_changed_count'] : '')
                .(isset($case['affected_trade_date_count']) ? ' | affected_trade_date_count='.(string) $case['affected_trade_date_count'] : '')
                .(isset($case['affected_start_date']) ? ' | affected_start_date='.(string) $case['affected_start_date'] : '')
                .(isset($case['affected_end_date']) ? ' | affected_end_date='.(string) $case['affected_end_date'] : '')
                .(isset($case['indicator_reprocess_state']) ? ' | indicator_reprocess_state='.(string) $case['indicator_reprocess_state'] : '')
                .(isset($case['indicator_reprocess_execution_state']) ? ' | indicator_reprocess_execution_state='.(string) $case['indicator_reprocess_execution_state'] : '')
                .(isset($case['indicator_reprocessed_trade_date_count']) ? ' | indicator_reprocessed_trade_date_count='.(string) $case['indicator_reprocessed_trade_date_count'] : '')
                .(isset($case['eligibility_reprocess_execution_state']) ? ' | eligibility_reprocess_execution_state='.(string) $case['eligibility_reprocess_execution_state'] : '')
                .(isset($case['publication_impact_state']) ? ' | publication_impact_state='.(string) $case['publication_impact_state'] : '')
                .(isset($case['publication_reprocess_state']) ? ' | publication_reprocess_state='.(string) $case['publication_reprocess_state'] : '')
                .(isset($case['publication_reprocess_republished_trade_date_count']) ? ' | publication_reprocess_republished_trade_date_count='.(string) $case['publication_reprocess_republished_trade_date_count'] : '')
                .(isset($case['publication_reprocess_republication_mode']) ? ' | publication_reprocess_republication_mode='.(string) $case['publication_reprocess_republication_mode'] : '')
                .(isset($case['publication_reprocess_correction_ids']) ? ' | publication_reprocess_correction_ids='.(is_array($case['publication_reprocess_correction_ids']) ? implode(',', $case['publication_reprocess_correction_ids']) : (string) $case['publication_reprocess_correction_ids']) : '')
                .(isset($case['publication_reprocess_correction_id']) ? ' | publication_reprocess_correction_id='.(string) $case['publication_reprocess_correction_id'] : '')
                .(isset($case['recovered_row_apply_state']) ? ' | recovered_row_apply_state='.(string) $case['recovered_row_apply_state'] : '')
                .(isset($case['recovered_row_count']) ? ' | recovered_row_count='.(string) $case['recovered_row_count'] : '')
                .(isset($case['reason_code']) && $case['reason_code'] !== null && $case['reason_code'] !== '' ? ' | reason='.(string) $case['reason_code'] : '')
            );
        }

        $this->line('summary:');
        foreach (['dates_total', 'dates_success', 'dates_held', 'dates_failed', 'ticker_failures', 'evidence_exported', 'fixtures_generated', 'replay_verified', 'bar_mutation_changed_count', 'affected_trade_date_count', 'indicator_reprocessed_trade_date_count', 'eligibility_reprocessed_trade_date_count', 'publication_reprocess_republished_trade_date_count', 'publication_reprocess_evidence_exported_count', 'publication_reprocess_fixtures_generated_count', 'publication_reprocess_replay_verified_count', 'recovered_row_count'] as $field) {
            $this->line($field.'='.(string) ($summary[$field] ?? 0));
        }
        if (isset($summary['publication_reprocess_republication_mode'])) {
            $this->line('publication_reprocess_republication_mode='.(string) $summary['publication_reprocess_republication_mode']);
        }
        if (! empty($summary['publication_reprocess_correction_ids'])) {
            $ids = is_array($summary['publication_reprocess_correction_ids']) ? implode(',', $summary['publication_reprocess_correction_ids']) : (string) $summary['publication_reprocess_correction_ids'];
            $this->line('publication_reprocess_correction_ids='.$ids);
        }

        return ! empty($summary['all_passed']) ? 0 : 1;
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if (method_exists($e, 'reasonCode')) {
            return $e->reasonCode();
        }

        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}
