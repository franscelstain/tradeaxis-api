<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;

class ReplayBackfillService
{
    private $calendar;
    private $publications;
    private $replays;
    private $evidence;

    public function __construct(
        MarketCalendarRepository $calendar,
        EodPublicationRepository $publications,
        ReplayVerificationService $replays,
        MarketDataEvidenceExportService $evidence
    ) {
        $this->calendar = $calendar;
        $this->publications = $publications;
        $this->replays = $replays;
        $this->evidence = $evidence;
    }

    public function execute($startDate, $endDate, $fixtureCase = 'valid_case', $fixtureRoot = null, $outputDir = null, $continueOnError = false)
    {
        $this->guardDateRange($startDate, $endDate);

        $fixtureRoot = $fixtureRoot ?: storage_path('app/market_data/replay-fixtures');
        $fixturePath = rtrim($fixtureRoot, '/').'/'.$fixtureCase;
        if (! is_dir($fixturePath)) {
            throw new \RuntimeException('Replay backfill fixture case not found: '.$fixturePath);
        }

        $dates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if ($dates === []) {
            throw new \RuntimeException('Replay backfill requires at least one trading date in market_calendar for the requested range.');
        }

        $outputDir = $outputDir ?: storage_path('app/market_data/evidence/replay_backfills/replay_backfill_'.$fixtureCase.'_'.$startDate.'_to_'.$endDate.'_'.Carbon::now(config('market_data.platform.timezone'))->format('Ymd_His'));
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $expectedOutcome = $this->expectedOutcomeForFixtureCase($fixtureCase);
        $cases = [];
        $allPassed = true;

        foreach ($dates as $tradeDate) {
            try {
                $publication = $this->publications->findCurrentPublicationForTradeDate($tradeDate);
                if (! $publication) {
                    throw new \RuntimeException('Readable current publication not found for replay backfill trade date '.$tradeDate.'.');
                }

                $result = $this->replays->verifyRunAgainstFixture((int) $publication->run_id, $fixturePath);
                $evidence = $this->evidence->exportReplayEvidence($result['replay_id'], $result['trade_date'], rtrim($outputDir, '/').'/'.$tradeDate);

                $observedOutcome = $result['comparison_result'];
                $passed = $expectedOutcome ? $observedOutcome === $expectedOutcome : true;

                $cases[] = [
                    'trade_date' => $tradeDate,
                    'status' => 'SUCCESS',
                    'publication_id' => (int) $publication->publication_id,
                    'run_id' => (int) $publication->run_id,
                    'replay_id' => (int) $result['replay_id'],
                    'expected_outcome' => $expectedOutcome,
                    'observed_outcome' => $observedOutcome,
                    'comparison_note' => $result['comparison_note'],
                    'fixture_case' => $fixtureCase,
                    'passed' => $passed,
                    'evidence_output_dir' => $evidence['output_dir'],
                    'evidence_files' => $evidence['files'],
                ];

                if (! $passed) {
                    $allPassed = false;
                    if (! $continueOnError) {
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $passed = $expectedOutcome === 'ERROR';
                if (! $passed) {
                    $allPassed = false;
                }
                $cases[] = [
                    'trade_date' => $tradeDate,
                    'status' => 'ERROR',
                    'fixture_case' => $fixtureCase,
                    'expected_outcome' => $expectedOutcome,
                    'observed_outcome' => 'ERROR',
                    'passed' => $passed,
                    'error_class' => get_class($e),
                    'error_message' => $e->getMessage(),
                ];

                if (! $continueOnError) {
                    break;
                }
            }
        }

        $summary = [
            'suite' => 'market_data_replay_backfill_minimum',
            'range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'fixture_case' => $fixtureCase,
            'fixture_root' => $fixtureRoot,
            'fixture_path' => $fixturePath,
            'expected_outcome' => $expectedOutcome,
            'trading_dates' => $dates,
            'all_passed' => $allPassed,
            'cases' => $cases,
            'output_dir' => $outputDir,
        ];

        file_put_contents(
            $outputDir.'/market_data_replay_backfill_summary.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $summary;
    }

    private function guardDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate, config('market_data.platform.timezone'))->startOfDay();
        $end = Carbon::parse($endDate, config('market_data.platform.timezone'))->startOfDay();

        if ($end->lt($start)) {
            throw new \RuntimeException('Replay backfill requires end_date >= start_date.');
        }
    }

    private function expectedOutcomeForFixtureCase($fixtureCase)
    {
        $map = [
            'valid_case' => 'MATCH',
            'reason_code_mismatch_case' => 'MISMATCH',
            'broken_manifest_case' => 'ERROR',
            'missing_file_case' => 'ERROR',
        ];

        return $map[$fixtureCase] ?? null;
    }
}
