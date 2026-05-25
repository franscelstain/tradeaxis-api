<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use Carbon\Carbon;

class ApiBackfillRangeAcquisitionService
{
    private $apiSourceAdapter;

    public function __construct(PublicApiEodBarsAdapter $apiSourceAdapter)
    {
        $this->apiSourceAdapter = $apiSourceAdapter;
    }

    public function acquire($warmupStart, $requestedStart, $requestedEnd, array $tradingDates, array $tickerCodes, array $context = [])
    {
        $tickerCodes = $this->normalizeTickerCodes($tickerCodes);
        $tradingDates = $this->normalizeDates($tradingDates);
        $windows = $this->splitWindows($warmupStart, $requestedEnd, $this->windowDays());
        $batchId = $context['source_acquisition_batch_id'] ?? $this->makeBatchId($requestedStart, $requestedEnd);
        $resume = ! empty($context['resume']);
        $onlyFailed = ! empty($context['only_failed']);
        $checkpointRows = isset($context['source_acquisition_checkpoint']) && is_array($context['source_acquisition_checkpoint'])
            ? $context['source_acquisition_checkpoint']
            : [];
        $resumeAccounting = $resume && $onlyFailed
            ? $this->buildResumeOnlyFailedAccounting($checkpointRows, $windows, $tickerCodes)
            : [];

        $rowsByDate = array_fill_keys($tradingDates, []);
        $windowTelemetry = [];
        $sourceAcquisitionCheckpoints = [];
        $skippedCheckpointCount = 0;

        foreach ($windows as $index => $window) {
            $windowTradingDates = array_values(array_filter($tradingDates, function ($date) use ($window) {
                return strcmp($date, $window['start']) >= 0 && strcmp($date, $window['end']) <= 0;
            }));

            if ($windowTradingDates === []) {
                continue;
            }

            $effectiveTickerCodes = $this->tickersForWindow($tickerCodes, $window, $checkpointRows, $resume, $onlyFailed, $skippedCheckpointCount, $resumeAccounting);
            if ($effectiveTickerCodes === []) {
                continue;
            }

            $requestContext = array_merge($context, [
                'source_acquisition_batch_id' => $batchId,
                'source_acquisition_mode' => 'range_window',
                'source_window_start' => $window['start'],
                'source_window_end' => $window['end'],
                'warmup_start' => $warmupStart,
                'requested_start' => $requestedStart,
                'requested_end' => $requestedEnd,
                'window_index' => $index + 1,
                'window_count' => count($windows),
            ]);

            try {
                $windowRows = $this->apiSourceAdapter->fetchOrLoadEodBarsRange(
                    $window['start'],
                    $window['end'],
                    'api',
                    $effectiveTickerCodes,
                    $windowTradingDates,
                    $requestContext
                );
                $windowTelemetryEntry = $this->apiSourceAdapter->consumeLastAcquisitionTelemetry();
            } catch (SourceAcquisitionException $e) {
                if (! ($resume && $onlyFailed)) {
                    throw $e;
                }

                $windowRows = array_fill_keys($windowTradingDates, []);
                $windowTelemetryEntry = $this->buildRetryBlockedTelemetry($batchId, $window, $effectiveTickerCodes, $requestContext, $e);
            }

            foreach ($windowRows as $date => $rows) {
                if (! array_key_exists($date, $rowsByDate)) {
                    $rowsByDate[$date] = [];
                }

                $rowsByDate[$date] = array_merge($rowsByDate[$date], $rows);
            }

            $windowTelemetry[] = $windowTelemetryEntry;
            $sourceAcquisitionCheckpoints = array_merge(
                $sourceAcquisitionCheckpoints,
                $this->buildWindowCheckpoints($batchId, $window, $effectiveTickerCodes, $windowRows, $windowTelemetryEntry)
            );
        }

        if ($resume && $onlyFailed) {
            $resumeAccounting = $this->finalizeResumeOnlyFailedAccounting($resumeAccounting, $sourceAcquisitionCheckpoints);
        }

        $result = [
            'source_acquisition_batch_id' => $batchId,
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => $warmupStart,
            'requested_start' => $requestedStart,
            'requested_end' => $requestedEnd,
            'windows' => $windows,
            'window_count' => count($windows),
            'ticker_count' => count($tickerCodes),
            'configured_concurrency' => $this->concurrency(),
            'trading_dates' => $tradingDates,
            'estimated_http_requests' => count($windows) * count($tickerCodes),
            'rows_by_trade_date' => $rowsByDate,
            'window_telemetry' => $windowTelemetry,
            'source_acquisition_checkpoints' => $sourceAcquisitionCheckpoints,
            'skipped_checkpoint_count' => $skippedCheckpointCount,
            'date_telemetry' => $this->buildDateTelemetry($rowsByDate, $tickerCodes, $windows, $batchId, $warmupStart, $requestedStart, $requestedEnd),
        ];

        if ($resume && $onlyFailed) {
            $result = array_merge($result, $resumeAccounting);
            $result['source_acquisition_state'] = $this->hasSystemicWindowFailure($windowTelemetry)
                ? 'SYSTEMIC_FAILED'
                : $this->retryStateFromAccounting($resumeAccounting);
            $result['source_final_status'] = $result['source_acquisition_state'];
        }

        return $result;
    }

    public function plan($warmupStart, $requestedStart, $requestedEnd, array $tradingDates, array $tickerCodes)
    {
        $tickerCodes = $this->normalizeTickerCodes($tickerCodes);
        $windows = $this->splitWindows($warmupStart, $requestedEnd, $this->windowDays());

        return [
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => $warmupStart,
            'requested_start' => $requestedStart,
            'requested_end' => $requestedEnd,
            'window_days' => $this->windowDays(),
            'windows' => $windows,
            'window_count' => count($windows),
            'ticker_count' => count($tickerCodes),
            'configured_concurrency' => $this->concurrency(),
            'trading_date_count' => count($this->normalizeDates($tradingDates)),
            'estimated_http_requests' => count($windows) * count($tickerCodes),
        ];
    }


    private function tickersForWindow(array $tickerCodes, array $window, array $checkpointRows, $resume, $onlyFailed, &$skippedCheckpointCount, array $resumeAccounting = [])
    {
        if (! $resume && ! $onlyFailed) {
            return $tickerCodes;
        }

        $result = [];
        foreach ($tickerCodes as $tickerCode) {
            $key = $this->checkpointKey($window['start'], $window['end'], $tickerCode);
            $state = isset($checkpointRows[$key]) ? (string) ($checkpointRows[$key]['state'] ?? '') : null;

            if ($onlyFailed) {
                if ($resume && isset($resumeAccounting['eligible_checkpoint_keys'][$key])) {
                    $result[] = $tickerCode;
                } else {
                    $skippedCheckpointCount++;
                }
                continue;
            }

            if ($state === 'SUCCESS') {
                $skippedCheckpointCount++;
                continue;
            }

            $result[] = $tickerCode;
        }

        return $result;
    }

    private function buildWindowCheckpoints($batchId, array $window, array $tickerCodes, array $windowRows, array $telemetry)
    {
        $returnedTickers = [];
        $returnedRowCounts = [];
        foreach ($windowRows as $dateRows) {
            foreach ((array) $dateRows as $row) {
                if (($row['ticker_code'] ?? null) !== null && $row['ticker_code'] !== '') {
                    $tickerKey = strtoupper((string) $row['ticker_code']);
                    $returnedTickers[$tickerKey] = true;
                    $returnedRowCounts[$tickerKey] = ($returnedRowCounts[$tickerKey] ?? 0) + 1;
                }
            }
        }

        $failedTickers = [];
        foreach ((array) ($telemetry['failed_ticker_codes'] ?? []) as $tickerCode) {
            if ((string) $tickerCode !== '') {
                $failedTickers[strtoupper((string) $tickerCode)] = true;
            }
        }
        foreach ((array) ($telemetry['missing_ticker_codes'] ?? []) as $tickerCode) {
            if ((string) $tickerCode !== '') {
                $failedTickers[strtoupper((string) $tickerCode)] = true;
            }
        }

        $failedTickerContexts = [];
        $failedTickerContextsByCheckpointKey = [];
        foreach ((array) ($telemetry['failed_ticker_contexts'] ?? []) as $tickerCode => $context) {
            $key = strtoupper(trim((string) $tickerCode));
            if ($key !== '' && is_array($context)) {
                $failedTickerContexts[$key] = $context;
                $failedTickerContextsByCheckpointKey[$this->checkpointKey(
                    $context['source_window_start'] ?? $window['start'],
                    $context['source_window_end'] ?? $window['end'],
                    $key
                )] = $context;
            }
        }
        foreach ((array) ($telemetry['failures_sample'] ?? []) as $context) {
            if (! is_array($context)) {
                continue;
            }
            $key = strtoupper(trim((string) ($context['ticker_code'] ?? '')));
            if ($key !== '' && ! isset($failedTickerContexts[$key])) {
                $failedTickerContexts[$key] = $context;
                $failedTickerContextsByCheckpointKey[$this->checkpointKey(
                    $context['source_window_start'] ?? $context['window_start'] ?? $window['start'],
                    $context['source_window_end'] ?? $context['window_end'] ?? $window['end'],
                    $key
                )] = $context;
            }
        }

        $now = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
        $rows = [];
        foreach ($tickerCodes as $tickerCode) {
            $tickerCode = (string) $tickerCode;
            $tickerKey = strtoupper($tickerCode);
            $checkpointKey = $this->checkpointKey($window['start'], $window['end'], $tickerCode);
            $state = isset($returnedTickers[$tickerKey]) ? 'SUCCESS' : 'FAILED';
            $failureContext = $failedTickerContextsByCheckpointKey[$checkpointKey] ?? ($failedTickerContexts[$tickerKey] ?? []);
            $reasonCode = null;
            if ($state === 'FAILED') {
                $reasonCode = $failureContext['final_reason_code']
                    ?? (isset($failedTickers[$tickerKey]) ? ($telemetry['final_reason_code'] ?? 'RUN_SOURCE_PARTIAL_RESPONSE') : 'RUN_SOURCE_NO_VALID_DATA');
            }
            $httpStatus = $state === 'FAILED'
                ? (array_key_exists('http_status', $failureContext) ? $failureContext['http_status'] : (array_key_exists('final_http_status', $failureContext) ? $failureContext['final_http_status'] : null))
                : ($telemetry['final_http_status'] ?? ($telemetry['http_status'] ?? null));
            $errorSample = $state === 'FAILED'
                ? ($failureContext['error_sample'] ?? ($failureContext['provider_error_sample'] ?? ($failureContext['response_body_sample'] ?? null)))
                : null;
            $providerErrorSample = $state === 'FAILED'
                ? (array_key_exists('provider_error_sample', $failureContext) ? $failureContext['provider_error_sample'] : ($failureContext['response_body_sample'] ?? null))
                : null;
            $sanitizedUrl = $state === 'FAILED'
                ? ($failureContext['sanitized_url'] ?? ($failureContext['url'] ?? null))
                : null;
            $failureScope = $state === 'FAILED'
                ? ($failureContext['failure_scope'] ?? 'ticker')
                : null;

            $rows[$checkpointKey] = [
                'source_acquisition_batch_id' => $batchId,
                'source_mode' => 'api',
                'source_acquisition_mode' => 'range_window',
                'requested_start' => $telemetry['requested_start'] ?? null,
                'requested_end' => $telemetry['requested_end'] ?? null,
                'warmup_start' => $telemetry['warmup_start'] ?? null,
                'window_start' => $window['start'],
                'window_end' => $window['end'],
                'ticker_code' => $tickerCode,
                'state' => $state,
                'attempt_count' => (int) (($state === 'FAILED' ? ($failureContext['attempt_count'] ?? null) : null) ?? ($telemetry['attempt_count'] ?? 0)),
                'reason_code' => $reasonCode,
                'http_status' => $httpStatus,
                'error_sample' => $errorSample,
                'provider_error_sample' => $providerErrorSample,
                'sanitized_url' => $sanitizedUrl,
                'failure_scope' => $failureScope,
                'rows_count' => (int) ($returnedRowCounts[$tickerKey] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    private function buildRetryBlockedTelemetry($batchId, array $window, array $tickerCodes, array $requestContext, SourceAcquisitionException $e)
    {
        $context = $e->context();
        $reasonCode = $e->reasonCode();
        $failureScope = $context['failure_scope'] ?? (count($tickerCodes) === 1 ? 'ticker' : 'window');
        $state = $this->isSystemicRetryFailure($reasonCode, $failureScope) ? 'SYSTEMIC_FAILED' : 'FAILED_RETRY_BLOCKED';
        $failedTickerContexts = [];
        $failuresSample = [];

        foreach ($tickerCodes as $tickerCode) {
            $tickerKey = strtoupper((string) $tickerCode);
            $perTickerContext = is_array($context['failed_ticker_contexts'][$tickerKey] ?? null)
                ? $context['failed_ticker_contexts'][$tickerKey]
                : $context;
            $perTickerScope = $perTickerContext['failure_scope'] ?? $failureScope;
            $failureContext = array_merge($requestContext, $perTickerContext, [
                'source_acquisition_batch_id' => $batchId,
                'source_acquisition_mode' => 'range_window',
                'source_window_start' => $window['start'],
                'source_window_end' => $window['end'],
                'ticker_code' => $tickerKey,
                'final_reason_code' => $perTickerContext['final_reason_code'] ?? $reasonCode,
                'source_final_status' => 'FAILED',
                'failure_scope' => $perTickerScope === 'systemic' ? 'systemic' : 'ticker',
            ]);
            $failedTickerContexts[$tickerKey] = $failureContext;
            $failuresSample[] = $failureContext;
        }

        return array_merge($requestContext, $context, [
            'source_acquisition_batch_id' => $batchId,
            'source_acquisition_state' => $state,
            'source_final_status' => $state,
            'final_reason_code' => $reasonCode,
            'source_window_start' => $window['start'],
            'source_window_end' => $window['end'],
            'failed_ticker_count' => count($tickerCodes),
            'missing_ticker_count' => count($tickerCodes),
            'failed_ticker_codes' => array_values(array_map('strtoupper', $tickerCodes)),
            'missing_ticker_codes' => array_values(array_map('strtoupper', $tickerCodes)),
            'failed_ticker_contexts' => $failedTickerContexts,
            'failures_sample' => array_slice($failuresSample, 0, 10),
            'failure_reason_summary' => [$reasonCode => count($tickerCodes)],
            'returned_row_count' => 0,
            'accepted_row_count' => 0,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
        ]);
    }

    private function isSystemicRetryFailure($reasonCode, $failureScope)
    {
        if (in_array($failureScope, ['systemic', 'request'], true)) {
            return true;
        }

        return in_array($reasonCode, ['CONFIG_INVALID', 'RUN_SOURCE_AUTH_ERROR', 'RUN_SOURCE_RESPONSE_CHANGED', 'RUN_SOURCE_PROVIDER_REJECTED_RANGE'], true);
    }

    private function hasSystemicWindowFailure(array $windowTelemetry)
    {
        foreach ($windowTelemetry as $entry) {
            if (is_array($entry) && ($entry['source_acquisition_state'] ?? null) === 'SYSTEMIC_FAILED') {
                return true;
            }
        }

        return false;
    }

    private function buildResumeOnlyFailedAccounting(array $checkpointRows, array $windows, array $tickerCodes)
    {
        $windowSet = [];
        foreach ($windows as $window) {
            $windowSet[(string) ($window['start'] ?? '').'|'.(string) ($window['end'] ?? '')] = true;
        }
        $tickerSet = array_fill_keys(array_map('strtoupper', $tickerCodes), true);

        $accounting = [
            'failed_checkpoint_total' => 0,
            'failed_checkpoint_eligible' => 0,
            'failed_checkpoint_retried' => 0,
            'failed_checkpoint_retry_success' => 0,
            'failed_checkpoint_retry_failed' => 0,
            'retry_success_count' => 0,
            'retry_failed_count' => 0,
            'failed_checkpoint_skipped' => 0,
            'skipped_failed_checkpoint_count' => 0,
            'skipped_failed_checkpoint_reasons' => [],
            'eligible_checkpoint_keys' => [],
        ];

        foreach ($checkpointRows as $key => $row) {
            if (! is_array($row) || ! in_array(($row['state'] ?? null), ['FAILED', 'RETRYING'], true)) {
                continue;
            }

            $accounting['failed_checkpoint_total']++;
            $parsed = $this->parseCheckpointIdentity($key, $row);
            $skipReason = null;

            if ($parsed === null) {
                $skipReason = 'CHECKPOINT_CORRUPTED';
            } elseif (! isset($windowSet[$parsed['window_start'].'|'.$parsed['window_end']])) {
                $skipReason = 'WINDOW_OUT_OF_SCOPE';
            } elseif (! isset($tickerSet[$parsed['ticker_code']])) {
                $skipReason = 'TICKER_NOT_IN_CURRENT_UNIVERSE';
            }

            if ($skipReason !== null) {
                $accounting['failed_checkpoint_skipped']++;
                $accounting['skipped_failed_checkpoint_count']++;
                $accounting['skipped_failed_checkpoint_reasons'][$skipReason] = ($accounting['skipped_failed_checkpoint_reasons'][$skipReason] ?? 0) + 1;
                continue;
            }

            $checkpointKey = $this->checkpointKey($parsed['window_start'], $parsed['window_end'], $parsed['ticker_code']);
            $accounting['failed_checkpoint_eligible']++;
            $accounting['eligible_checkpoint_keys'][$checkpointKey] = true;
        }

        return $accounting;
    }

    private function finalizeResumeOnlyFailedAccounting(array $accounting, array $sourceAcquisitionCheckpoints)
    {
        $eligibleKeys = $accounting['eligible_checkpoint_keys'] ?? [];
        foreach ($eligibleKeys as $key => $_) {
            if (! isset($sourceAcquisitionCheckpoints[$key]) || ! is_array($sourceAcquisitionCheckpoints[$key])) {
                continue;
            }

            $state = (string) ($sourceAcquisitionCheckpoints[$key]['state'] ?? '');
            if ($state === 'SUCCESS') {
                $accounting['failed_checkpoint_retry_success']++;
                $accounting['retry_success_count']++;
                continue;
            }

            if ($state === 'FAILED') {
                $accounting['failed_checkpoint_retry_failed']++;
                $accounting['retry_failed_count']++;
            }
        }

        $accounting['failed_checkpoint_retried'] = $accounting['failed_checkpoint_retry_success'] + $accounting['failed_checkpoint_retry_failed'];
        $accounting['failed_checkpoint_skipped'] = (int) ($accounting['failed_checkpoint_skipped'] ?? 0)
            + max(0, (int) ($accounting['failed_checkpoint_eligible'] ?? 0) - (int) $accounting['failed_checkpoint_retried']);
        $accounting['skipped_failed_checkpoint_count'] = $accounting['failed_checkpoint_skipped'];

        return $accounting;
    }

    private function retryStateFromAccounting(array $accounting)
    {
        if ((int) ($accounting['failed_checkpoint_total'] ?? 0) === 0) {
            return 'NO_FAILED_CHECKPOINT';
        }

        if ((int) ($accounting['failed_checkpoint_retried'] ?? 0) === 0) {
            return 'FAILED_RETRY_BLOCKED';
        }

        $retrySuccess = (int) ($accounting['retry_success_count'] ?? 0);
        $retryFailed = (int) ($accounting['retry_failed_count'] ?? 0);

        if ($retryFailed === 0 && $retrySuccess > 0) {
            return 'RETRY_SUCCESS';
        }

        if ($retrySuccess > 0 && $retryFailed > 0) {
            return 'PARTIAL_RETRY_SUCCESS';
        }

        return 'FAILED_RETRY_BLOCKED';
    }

    private function parseCheckpointIdentity($key, array $row)
    {
        $parts = explode('|', (string) $key);
        $windowStart = $row['window_start'] ?? ($parts[0] ?? null);
        $windowEnd = $row['window_end'] ?? ($parts[1] ?? null);
        $tickerCode = strtoupper(trim((string) ($row['ticker_code'] ?? ($parts[2] ?? ''))));

        if ($windowStart === null || $windowEnd === null || $tickerCode === '') {
            return null;
        }

        return [
            'window_start' => (string) $windowStart,
            'window_end' => (string) $windowEnd,
            'ticker_code' => $tickerCode,
        ];
    }

    private function checkpointKey($windowStart, $windowEnd, $tickerCode)
    {
        return (string) $windowStart.'|'.(string) $windowEnd.'|'.strtoupper(trim((string) $tickerCode));
    }

    private function buildDateTelemetry(array $rowsByDate, array $tickerCodes, array $windows, $batchId, $warmupStart, $requestedStart, $requestedEnd)
    {
        $expectedTickerCount = count($tickerCodes);
        $dateTelemetry = [];

        foreach ($rowsByDate as $date => $rows) {
            $returnedTickers = [];
            foreach ($rows as $row) {
                if (($row['ticker_code'] ?? null) !== null && $row['ticker_code'] !== '') {
                    $returnedTickers[(string) $row['ticker_code']] = true;
                }
            }

            $successTickerCount = count($returnedTickers);
            $failedTickerCount = max(0, $expectedTickerCount - $successTickerCount);
            $maxFailedAllowed = $this->maxFailedAllowed($expectedTickerCount);
            $window = $this->windowForDate($windows, $date);

            $dateTelemetry[$date] = [
                'source_mode' => 'api',
                'source_acquisition_batch_id' => $batchId,
                'source_acquisition_mode' => 'range_window',
                'source_window_start' => $window['start'] ?? null,
                'source_window_end' => $window['end'] ?? null,
                'warmup_start' => $warmupStart,
                'requested_start' => $requestedStart,
                'requested_end' => $requestedEnd,
                'source_acquisition_state' => $successTickerCount === 0
                    ? 'FAILED'
                    : ($failedTickerCount === 0 ? 'SUCCESS' : 'PARTIAL_SUCCESS'),
                'source_final_status' => $successTickerCount === 0
                    ? 'FAILED'
                    : ($failedTickerCount === 0 ? 'SUCCESS' : 'PARTIAL'),
                'expected_ticker_count' => $expectedTickerCount,
                'success_ticker_count' => $successTickerCount,
                'failed_ticker_count' => $failedTickerCount,
                'max_failed_allowed_for_coverage' => $maxFailedAllowed,
                'coverage_impossible' => $failedTickerCount > $maxFailedAllowed,
                'returned_row_count' => count($rows),
                'accepted_row_count' => count($rows),
                'rejected_row_count' => 0,
                'invalid_row_count' => 0,
                'final_reason_code' => $successTickerCount === 0
                    ? 'RUN_SOURCE_NO_VALID_DATA'
                    : ($failedTickerCount > $maxFailedAllowed ? 'COVERAGE_BELOW_THRESHOLD' : ($failedTickerCount > 0 ? 'RUN_SOURCE_PARTIAL_RESPONSE' : null)),
            ];
        }

        return $dateTelemetry;
    }

    private function splitWindows($startDate, $endDate, $windowDays)
    {
        $timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
        $start = Carbon::parse($startDate, $timezone)->startOfDay();
        $end = Carbon::parse($endDate, $timezone)->startOfDay();
        $windowDays = max(1, (int) $windowDays);
        $windows = [];

        while ($start->lte($end)) {
            $windowStart = $start->copy();
            $windowEnd = $start->copy()->addDays($windowDays - 1);
            if ($windowEnd->gt($end)) {
                $windowEnd = $end->copy();
            }

            $windows[] = [
                'start' => $windowStart->toDateString(),
                'end' => $windowEnd->toDateString(),
            ];

            $start = $windowEnd->copy()->addDay();
        }

        return $windows;
    }

    private function windowForDate(array $windows, $date)
    {
        foreach ($windows as $window) {
            if (strcmp($date, $window['start']) >= 0 && strcmp($date, $window['end']) <= 0) {
                return $window;
            }
        }

        return null;
    }

    private function normalizeTickerCodes(array $tickerCodes)
    {
        $normalized = [];
        foreach ($tickerCodes as $tickerCode) {
            $tickerCode = strtoupper(trim((string) $tickerCode));
            if ($tickerCode !== '') {
                $normalized[$tickerCode] = true;
            }
        }

        $result = array_keys($normalized);
        sort($result);

        return $result;
    }

    private function normalizeDates(array $dates)
    {
        $normalized = array_values(array_unique(array_filter(array_map('strval', $dates))));
        sort($normalized);

        return $normalized;
    }

    private function makeBatchId($requestedStart, $requestedEnd)
    {
        return 'API_'.str_replace('-', '', (string) $requestedStart).'_'.str_replace('-', '', (string) $requestedEnd).'_001';
    }

    private function windowDays()
    {
        return max(1, (int) config('market_data.source.api_backfill.window_days', 90));
    }

    private function concurrency()
    {
        return max(1, (int) config('market_data.source.api_backfill.concurrency', 5));
    }

    private function maxFailedAllowed($expectedTickerCount)
    {
        $minRatio = (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min', 0.98));

        return max(0, (int) floor($expectedTickerCount * (1 - $minRatio)));
    }
}
