<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;

class MarketDataBackfillService
{
    private $calendar;
    private $pipeline;
    private $runs;

    public function __construct(MarketCalendarRepository $calendar, MarketDataPipelineService $pipeline, EodRunRepository $runs = null)
    {
        $this->calendar = $calendar;
        $this->pipeline = $pipeline;
        $this->runs = $runs;
    }

    public function execute($startDate, $endDate, $sourceMode = null, $outputDir = null, $continueOnError = false)
    {
        $this->guardDateRange($startDate, $endDate);

        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode');
        $dates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if ($dates === []) {
            throw new \RuntimeException('Backfill requires at least one trading date in market_calendar for the requested range.');
        }

        $outputDir = $outputDir ?: storage_path('app/market_data/evidence/backfills/backfill_'.$startDate.'_to_'.$endDate.'_'.Carbon::now(config('market_data.platform.timezone'))->format('Ymd_His'));
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $cases = [];
        $allPassed = true;

        foreach ($dates as $requestedDate) {
            try {
                $run = $this->pipeline->runDaily($requestedDate, $sourceMode, null);
                $passed = $this->runCountsAsPass($run);
                if (! $passed) {
                    $allPassed = false;
                }

                $cases[] = [
                    'requested_date' => $requestedDate,
                    'status' => $passed ? 'PASS' : 'FAIL',
                    'run_id' => (int) $run->run_id,
                    'terminal_status' => (string) $run->terminal_status,
                    'publishability_state' => (string) $run->publishability_state,
                    'trade_date_effective' => $run->trade_date_effective !== null ? (string) $run->trade_date_effective : null,
                ] + $this->buildSourceContextFromRun($run);

                if (! $passed && ! $continueOnError) {
                    break;
                }
            } catch (\Throwable $e) {
                $allPassed = false;
                $failedRun = $this->runs ? $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode) : null;
                $case = [
                    'requested_date' => $requestedDate,
                    'status' => 'ERROR',
                    'error_class' => get_class($e),
                    'error_message' => $e->getMessage(),
                ];

                if ($failedRun) {
                    $case = array_merge($case, [
                        'run_id' => (int) $failedRun->run_id,
                        'terminal_status' => (string) $failedRun->terminal_status,
                        'publishability_state' => (string) $failedRun->publishability_state,
                        'trade_date_effective' => $failedRun->trade_date_effective !== null ? (string) $failedRun->trade_date_effective : null,
                    ], $this->buildSourceContextFromRun($failedRun));
                }

                $cases[] = $case;

                if (! $continueOnError) {
                    break;
                }
            }
        }

        $summary = [
            'suite' => 'market_data_backfill_minimum',
            'range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'source_mode' => $sourceMode,
            'trading_dates' => $dates,
            'all_passed' => $allPassed,
            'cases' => $cases,
            'output_dir' => $outputDir,
        ];

        file_put_contents(
            $outputDir.'/market_data_backfill_summary.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $summary;
    }

    private function buildSourceContextFromRun($run)
    {
        $notesMap = $this->parseRunNotes((string) ($run->notes ?? ''));
        $sourceContext = [];

        if (($notesMap['source_name'] ?? '') !== '') {
            $sourceContext['source_name'] = (string) $notesMap['source_name'];
        }

        if (($notesMap['source_input_file'] ?? '') !== '') {
            $sourceContext['source_input_file'] = (string) $notesMap['source_input_file'];
        }

        $sourceSummary = $this->buildSourceSummaryString($notesMap);
        if ($sourceSummary !== null) {
            $sourceContext['source_summary'] = $sourceSummary;
        }

        return $sourceContext;
    }

    private function buildSourceSummaryString(array $notesMap)
    {
        $summaryParts = [];

        foreach ([
            'source_attempt_count' => 'attempt_count',
            'source_success_after_retry' => 'success_after_retry',
            'source_final_http_status' => 'final_http_status',
            'source_final_reason_code' => 'final_reason_code',
        ] as $key => $label) {
            if (($notesMap[$key] ?? '') === '') {
                continue;
            }

            $summaryParts[] = $label.'='.(string) $notesMap[$key];
        }

        if ($summaryParts === []) {
            return null;
        }

        return implode(' | ', $summaryParts);
    }

    private function parseRunNotes($notes)
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

    private function runCountsAsPass($run)
    {
        return (string) $run->terminal_status === 'SUCCESS'
            && (string) $run->publishability_state === 'READABLE';
    }

    private function guardDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate, config('market_data.platform.timezone'))->startOfDay();
        $end = Carbon::parse($endDate, config('market_data.platform.timezone'))->startOfDay();

        if ($end->lt($start)) {
            throw new \RuntimeException('Backfill requires end_date >= start_date.');
        }
    }
}
