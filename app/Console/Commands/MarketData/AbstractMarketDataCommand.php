<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataPipelineService;
use Carbon\Carbon;
use Illuminate\Console\Command;

abstract class AbstractMarketDataCommand extends Command
{
    protected function requestedDate()
    {
        if ($this->option('requested_date')) {
            return $this->option('requested_date');
        }

        if ($this->option('latest')) {
            return Carbon::now(config('market_data.platform.timezone'))->toDateString();
        }

        return Carbon::now(config('market_data.platform.timezone'))->toDateString();
    }

    protected function sourceMode()
    {
        return $this->option('source_mode') ?: config('market_data.pipeline.default_source_mode');
    }

    protected function makeStageInput($stage)
    {
        return new MarketDataStageInput(
            $this->requestedDate(),
            $this->sourceMode(),
            $this->option('run_id') ?: null,
            $stage,
            $this->option('correction_id') ?: null
        );
    }

    protected function pipeline()
    {
        return app(MarketDataPipelineService::class);
    }

    protected function renderRunSummary($run)
    {
        $this->info('run_id='.(string) $this->runField($run, 'run_id', ''));
        $this->line('requested_date='.(string) $this->runField($run, 'trade_date_requested', ''));
        $this->line('stage='.(string) $this->runField($run, 'stage', ''));
        $this->line('lifecycle_state='.(string) $this->runField($run, 'lifecycle_state', ''));
        $this->line('terminal_status='.(string) $this->runField($run, 'terminal_status', ''));
        $this->line('publishability_state='.(string) $this->runField($run, 'publishability_state', ''));

        $this->renderCoverageSummary($run);

        $this->renderSourceSummary($run);

        $reasonCode = $this->runField($run, 'reason_code');
        if ($reasonCode !== null && $reasonCode !== '') {
            $this->line('reason_code='.(string) $reasonCode);
        }

        $notes = $this->runField($run, 'notes');
        if ($notes !== null && $notes !== '') {
            $this->line('notes='.(string) $notes);
        }
    }


    protected function renderSourceSummary($run)
    {
        $notesMap = $this->parseRunNotes((string) $this->runField($run, 'notes', ''));
        $sourceName = $notesMap['source_name'] ?? null;
        $inputFile = $notesMap['source_input_file'] ?? null;
        $attemptCount = $notesMap['source_attempt_count'] ?? null;
        $successAfterRetry = $notesMap['source_success_after_retry'] ?? null;
        $finalHttpStatus = $notesMap['source_final_http_status'] ?? null;

        if ($sourceName !== null && $sourceName !== '') {
            $this->line('source_name='.(string) $sourceName);
        }

        if ($inputFile !== null && $inputFile !== '') {
            $this->line('source_input_file='.(string) $inputFile);
        }

        $summaryParts = [];

        if ($attemptCount !== null && $attemptCount !== '') {
            $summaryParts[] = 'attempt_count='.(string) $attemptCount;
        }

        if ($successAfterRetry !== null && $successAfterRetry !== '') {
            $summaryParts[] = 'success_after_retry='.(string) $successAfterRetry;
        }

        if ($finalHttpStatus !== null && $finalHttpStatus !== '') {
            $summaryParts[] = 'final_http_status='.(string) $finalHttpStatus;
        }

        if ($summaryParts !== []) {
            $this->line('source_summary='.implode(' | ', $summaryParts));
        }
    }

    protected function parseRunNotes($notes)
    {
        if ($notes === '') {
            return [];
        }

        $segments = preg_split('/\s*;\s*/', $notes);
        if (! is_array($segments)) {
            return [];
        }

        $parsed = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '' || strpos($segment, '=') === false) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $key = trim((string) $key);
            $value = trim((string) $value);

            if ($key === '') {
                continue;
            }

            $parsed[$key] = $value;
        }

        return $parsed;
    }

    protected function renderCoverageSummary($run)
    {
        $state = $this->runField($run, 'coverage_gate_state');
        $ratio = $this->runField($run, 'coverage_ratio');
        $available = $this->runField($run, 'coverage_available_count');
        $universe = $this->runField($run, 'coverage_universe_count');
        $missing = $this->runField($run, 'coverage_missing_count');
        $threshold = $this->runField($run, 'coverage_min_threshold');
        $basis = $this->runField($run, 'coverage_universe_basis');
        $contract = $this->runField($run, 'coverage_contract_version');
        $coverageReasonCode = $this->resolveCoverageReasonCode($run, $state);

        if ($state === null && $ratio === null && $available === null && $universe === null && $missing === null && $threshold === null && $coverageReasonCode === null) {
            return;
        }

        if ($state !== null && $state !== '') {
            $this->line('coverage_gate_state='.(string) $state);
        }

        if ($coverageReasonCode !== null && $coverageReasonCode !== '') {
            $this->line('coverage_reason_code='.(string) $coverageReasonCode);
        }

        $summaryParts = [];

        if ($available !== null || $universe !== null) {
            $summaryParts[] = 'available='.(string) ($available ?? 'null').'/'.(string) ($universe ?? 'null');
        }

        if ($missing !== null) {
            $summaryParts[] = 'missing='.(string) $missing;
        }

        if ($ratio !== null) {
            $summaryParts[] = 'ratio='.$this->formatCoverageDecimal($ratio);
        }

        if ($threshold !== null) {
            $summaryParts[] = 'threshold='.$this->formatCoverageDecimal($threshold);
        }

        if ($basis !== null && $basis !== '') {
            $summaryParts[] = 'basis='.(string) $basis;
        }

        if ($contract !== null && $contract !== '') {
            $summaryParts[] = 'contract='.(string) $contract;
        }

        if ($summaryParts !== []) {
            $this->line('coverage_summary='.implode(' | ', $summaryParts));
        }

        $missingSample = $this->decodeMissingSample($this->runField($run, 'coverage_missing_sample_json'));
        if ($missingSample !== []) {
            $this->line('coverage_missing_sample='.implode(',', $missingSample));
        }
    }

    protected function resolveCoverageReasonCode($run, $coverageState)
    {
        $reasonCode = $this->runField($run, 'reason_code');

        if ($reasonCode === 'RUN_COVERAGE_LOW' || $reasonCode === 'RUN_COVERAGE_NOT_EVALUABLE') {
            return $reasonCode;
        }

        if ($coverageState === 'PASS') {
            return 'COVERAGE_THRESHOLD_MET';
        }

        if ($coverageState === 'FAIL') {
            return 'COVERAGE_BELOW_THRESHOLD';
        }

        if ($coverageState === 'NOT_EVALUABLE') {
            return 'RUN_COVERAGE_NOT_EVALUABLE';
        }

        return null;
    }

    protected function decodeMissingSample($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value), static function ($item) {
                return $item !== '';
            }));
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $decoded), static function ($item) {
            return $item !== '';
        }));
    }

    protected function formatCoverageDecimal($value)
    {
        if ($value === null || $value === '') {
            return 'null';
        }

        return number_format((float) $value, 4, '.', '');
    }

    protected function runField($run, $field, $default = null)
    {
        if (is_array($run) && array_key_exists($field, $run)) {
            return $run[$field];
        }

        if (is_object($run) && isset($run->{$field})) {
            return $run->{$field};
        }

        if (is_object($run) && property_exists($run, $field)) {
            return $run->{$field};
        }

        return $default;
    }
}
