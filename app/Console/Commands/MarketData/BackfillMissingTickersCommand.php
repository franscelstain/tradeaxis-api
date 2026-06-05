<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;

class BackfillMissingTickersCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:backfill:missing-tickers
        {start_date?}
        {end_date?}
        {--source_mode=api}
        {--ticker_codes=}
        {--input_file=}
        {--output_dir=}
        {--plan}
        {--resume}
        {--continue-on-error}
        {--stop-on-error}
        {--collect-all-errors}
        {--max-dates-per-run=}
        {--skip-publication-reprocess}
        {--with-evidence}
        {--with-replay}
        {--no-replay}';

    protected $description = 'Backfill only missing ticker/date bars through lifecycle promote, evidence, and replay using current bars plus API/manual source rows.';

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

        try {
            $summary = app(BackfillLifecycleOrchestrator::class)->executeMissingTickers(
                $this->argument('start_date'),
                $this->argument('end_date'),
                $this->option('source_mode') ?: 'api',
                [
                    'ticker_codes' => $this->option('ticker_codes') ?: null,
                    'input_file' => $this->option('input_file') ?: null,
                    'output_dir' => $this->option('output_dir') ?: null,
                    'plan' => (bool) $this->option('plan'),
                    'resume' => (bool) $this->option('resume'),
                    'continue_on_error' => (bool) $this->option('continue-on-error'),
                    'stop_on_error' => (bool) $this->option('stop-on-error'),
                    'collect_all_errors' => (bool) $this->option('collect-all-errors'),
                    'max_dates_per_run' => $this->option('max-dates-per-run') !== null && $this->option('max-dates-per-run') !== ''
                        ? (int) $this->option('max-dates-per-run')
                        : null,
                    'skip_publication_reprocess' => (bool) $this->option('skip-publication-reprocess'),
                    'with_evidence' => (bool) $this->option('with-evidence'),
                    'with_replay' => (bool) $this->option('with-replay'),
                    'no_replay' => (bool) $this->option('no-replay'),
                ]
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage());

            return 1;
        }

        $this->line('source_mode='.(string) $summary['source_mode']);
        $this->line('source_acquisition_mode='.(string) $summary['source_acquisition_mode']);
        if (! empty($summary['source_acquisition_batch_id'])) {
            $this->line('source_acquisition_batch_id='.(string) $summary['source_acquisition_batch_id']);
        }
        $this->line('requested_start='.(string) $summary['requested_start']);
        $this->line('requested_end='.(string) $summary['requested_end']);
        $this->line('window_count='.(string) $summary['window_count']);
        $this->line('estimated_http_requests='.(string) $summary['estimated_http_requests']);
        $this->line('ticker_count='.(string) $summary['ticker_count']);
        $this->line('missing_ticker_count='.(string) $summary['missing_ticker_count']);
        $this->line('missing_bar_count='.(string) $summary['missing_bar_count']);
        $this->line('missing_trade_date_count='.(string) $summary['missing_trade_date_count']);
        $this->line('trading_dates='.(string) $summary['trading_date_count']);
        $this->line('mode='.(string) $summary['mode']);
        $this->line('with_evidence='.(empty($summary['with_evidence']) ? 'false' : 'true'));
        $this->line('with_replay='.(empty($summary['with_replay']) ? 'false' : 'true'));
        if (array_key_exists('skip_publication_reprocess', $summary)) {
            $this->line('skip_publication_reprocess='.(empty($summary['skip_publication_reprocess']) ? 'false' : 'true'));
        }
        if (! empty($summary['input_file'])) {
            $this->line('input_file='.$this->normalizeOptionalPathForDisplay((string) $summary['input_file']));
        }
        $this->line('output_dir='.$this->normalizePathForDisplay((string) $summary['output_dir']));

        foreach (['stage', 'source_acquisition_state', 'source_final_status', 'reason_code', 'failed_ticker_count', 'failed_ticker_codes', 'failed_window_count', 'skipped_checkpoint_count', 'mutation_guard', 'diagnostic_path'] as $field) {
            if (array_key_exists($field, $summary) && $summary[$field] !== null && $summary[$field] !== '') {
                $value = is_array($summary[$field]) ? json_encode($summary[$field], JSON_UNESCAPED_SLASHES) : (string) $summary[$field];
                if ($field === 'diagnostic_path') {
                    $value = $this->normalizePathForDisplay($value);
                }
                $this->line($field.'='.$value);
            }
        }

        if (($summary['status'] ?? null) === 'PLAN_ONLY') {
            $this->line('status=PLAN_ONLY');
            return 0;
        }

        foreach ($summary['cases'] as $case) {
            $this->line(
                (string) $case['requested_date']
                .' | run_id='.(string) ($case['run_id'] ?? '')
                .' | missing_tickers='.(string) ($case['missing_ticker_count'] ?? 0)
                .' | tickers='.(string) ($case['tickers_success'] ?? '').'/'.(string) ($case['tickers_expected'] ?? '')
                .' | import='.(string) ($case['import_status'] ?? '')
                .' | coverage='.(string) ($case['coverage_gate_state'] ?? '')
                .' | promote='.(string) ($case['promote_status'] ?? '')
                .' | evidence='.(string) ($case['evidence_status'] ?? '')
                .' | fixture='.(string) ($case['fixture_status'] ?? '')
                .' | replay='.(string) ($case['replay_status'] ?? '')
                .' | readable='.(! empty($case['readable']) ? 'YES' : 'NO')
                .(isset($case['bar_mutation_changed_count']) ? ' | bar_mutation_changed_count='.(string) $case['bar_mutation_changed_count'] : '')
                .(isset($case['indicator_reprocess_execution_state']) ? ' | indicator_reprocess_execution_state='.(string) $case['indicator_reprocess_execution_state'] : '')
                .(isset($case['publication_reprocess_state']) ? ' | publication_reprocess_state='.(string) $case['publication_reprocess_state'] : '')
                .(isset($case['publication_reprocess_readable_correction_candidate_trade_dates']) ? ' | publication_reprocess_readable_correction_candidate_trade_dates='.(is_array($case['publication_reprocess_readable_correction_candidate_trade_dates']) ? implode(',', $case['publication_reprocess_readable_correction_candidate_trade_dates']) : (string) $case['publication_reprocess_readable_correction_candidate_trade_dates']) : '')
                .(isset($case['reason_code']) && $case['reason_code'] !== null && $case['reason_code'] !== '' ? ' | reason='.(string) $case['reason_code'] : '')
            );

            if ($this->getOutput()->isVerbose() && ! empty($case['missing_ticker_codes'])) {
                $this->line('missing_ticker_codes='.(is_array($case['missing_ticker_codes']) ? implode(',', $case['missing_ticker_codes']) : (string) $case['missing_ticker_codes']));
            }
        }

        $this->line('summary:');
        foreach (['dates_total', 'dates_success', 'dates_skipped', 'dates_held', 'dates_blocked', 'dates_failed', 'ticker_failures', 'missing_ticker_source_rows', 'candidate_source_row_count', 'evidence_exported', 'fixtures_generated', 'replay_verified', 'bar_mutation_changed_count', 'affected_trade_date_count', 'indicator_reprocessed_trade_date_count', 'eligibility_reprocessed_trade_date_count', 'publication_reprocess_republished_trade_date_count', 'publication_reprocess_evidence_exported_count', 'publication_reprocess_fixtures_generated_count', 'publication_reprocess_replay_verified_count'] as $field) {
            $this->line($field.'='.(string) ($summary[$field] ?? 0));
        }
        if (isset($summary['publication_reprocess_republication_mode'])) {
            $this->line('publication_reprocess_republication_mode='.(string) $summary['publication_reprocess_republication_mode']);
        }
        if (! empty($summary['publication_reprocess_correction_ids'])) {
            $ids = is_array($summary['publication_reprocess_correction_ids']) ? implode(',', $summary['publication_reprocess_correction_ids']) : (string) $summary['publication_reprocess_correction_ids'];
            $this->line('publication_reprocess_correction_ids='.$ids);
        }
        $this->line('status='.(string) ($summary['status'] ?? 'UNKNOWN'));

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
