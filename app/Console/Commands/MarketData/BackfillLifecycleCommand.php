<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;

class BackfillLifecycleCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:backfill:lifecycle
        {start_date?}
        {end_date?}
        {--source_mode=api}
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

        try {
            $summary = app(BackfillLifecycleOrchestrator::class)->execute(
                $this->argument('start_date'),
                $this->argument('end_date'),
                $this->option('source_mode') ?: 'api',
                [
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

        if (in_array(($summary['status'] ?? null), ['BLOCKED', 'NOOP', 'SOURCE_RETRY_SUCCESS', 'SOURCE_DIAGNOSTIC_SUCCESS', 'SOURCE_DIAGNOSTIC_PARTIAL'], true)) {
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
                .(isset($case['reason_code']) && $case['reason_code'] !== null && $case['reason_code'] !== '' ? ' | reason='.(string) $case['reason_code'] : '')
            );
        }

        $this->line('summary:');
        foreach (['dates_total', 'dates_success', 'dates_held', 'dates_failed', 'ticker_failures', 'evidence_exported', 'fixtures_generated', 'replay_verified'] as $field) {
            $this->line($field.'='.(string) ($summary[$field] ?? 0));
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
