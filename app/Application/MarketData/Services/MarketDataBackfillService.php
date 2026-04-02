<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;

class MarketDataBackfillService
{
    private $calendar;
    private $pipeline;

    public function __construct(MarketCalendarRepository $calendar, MarketDataPipelineService $pipeline)
    {
        $this->calendar = $calendar;
        $this->pipeline = $pipeline;
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
                ];

                if (! $passed && ! $continueOnError) {
                    break;
                }
            } catch (\Throwable $e) {
                $allPassed = false;
                $cases[] = [
                    'requested_date' => $requestedDate,
                    'status' => 'ERROR',
                    'error_class' => get_class($e),
                    'error_message' => $e->getMessage(),
                ];

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
