<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\Exceptions\NoReadablePublicationException;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FullRangeCurrentEvidenceReplayService
{
    private $calendar;
    private $publications;
    private $evidence;
    private $replays;

    public function __construct(
        MarketCalendarRepository $calendar,
        EodPublicationRepository $publications,
        MarketDataEvidenceExportService $evidence,
        ReplayVerificationService $replays
    ) {
        $this->calendar = $calendar;
        $this->publications = $publications;
        $this->evidence = $evidence;
        $this->replays = $replays;
    }

    public function execute($startDate = null, $endDate = null, array $options = [])
    {
        [$startDate, $endDate] = $this->resolveRange($startDate, $endDate);
        $this->guardDateRange($startDate, $endDate);

        $fixtureCase = (string) ($options['fixture_case'] ?? 'valid_case');
        $outputDir = $options['output_dir'] ?? $this->defaultOutputDir($startDate, $endDate);
        $continueOnError = ! empty($options['continue_on_error']);
        $maxDates = isset($options['max_dates']) && $options['max_dates'] !== null && $options['max_dates'] !== ''
            ? (int) $options['max_dates']
            : null;

        $dates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if ($dates === []) {
            throw new \RuntimeException('FULL_RANGE_CURRENT_REPLAY_REQUIRES_TRADING_DATES: market_calendar has no trading dates for requested range.');
        }

        if ($maxDates !== null && $maxDates > 0) {
            $dates = array_slice($dates, 0, $maxDates);
        }

        $this->ensureDirectory($outputDir);

        $summary = [
            'suite' => 'market_data_full_range_current_evidence_replay',
            'assertion_scope' => 'current_readable_publication_per_trading_date',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'fixture_case' => $fixtureCase,
            'trading_date_count' => count($dates),
            'continue_on_error' => $continueOnError,
            'max_dates' => $maxDates,
            'output_dir' => $this->normalizePathForDisplay($outputDir),
            'started_at' => Carbon::now(config('market_data.platform.timezone'))->toAtomString(),
            'finished_at' => null,
            'all_passed' => false,
            'success_count' => 0,
            'failed_count' => 0,
            'error_count' => 0,
            'processed_count' => 0,
            'cases' => [],
        ];

        foreach ($dates as $tradeDate) {
            $case = $this->processTradeDate($tradeDate, $fixtureCase, $outputDir);
            $summary['cases'][] = $case;
            $summary = $this->refreshCounters($summary);
            $this->writeSummary($outputDir, $summary);

            if (($case['passed'] ?? false) !== true && ! $continueOnError) {
                break;
            }
        }

        $summary = $this->refreshCounters($summary);
        $summary['finished_at'] = Carbon::now(config('market_data.platform.timezone'))->toAtomString();
        $summary['all_passed'] = $summary['processed_count'] === $summary['trading_date_count']
            && $summary['failed_count'] === 0
            && $summary['error_count'] === 0;
        $this->writeSummary($outputDir, $summary);

        return $summary;
    }

    private function processTradeDate($tradeDate, $fixtureCase, $outputDir)
    {
        try {
            $publication = $this->publications->findCurrentPublicationForTradeDate($tradeDate);
            if (! $publication) {
                throw new NoReadablePublicationException($tradeDate, 'Full-range current evidence/replay');
            }

            $runId = (int) $publication->run_id;
            $publicationId = (int) $publication->publication_id;
            $caseDir = rtrim($outputDir, '/\\').'/dates/'.$tradeDate.'/run_'.$runId.'_publication_'.$publicationId;

            $runEvidence = $this->evidence->exportRunEvidence($runId, $caseDir.'/run-evidence');
            $fixture = $this->replays->generateFixtureFromRun($runId, $caseDir.'/fixture', $fixtureCase, $publicationId);
            $replay = $this->replays->verifyRunAgainstFixture($runId, $fixture['fixture_path']);
            $replayEvidence = $this->evidence->exportReplayEvidence(
                $replay['replay_id'],
                $tradeDate,
                $caseDir.'/replay-evidence'
            );

            $passed = $this->casePassed($runEvidence, $replay, $replayEvidence);

            return [
                'trade_date' => $tradeDate,
                'status' => $passed ? 'SUCCESS' : 'FAILED',
                'passed' => $passed,
                'run_id' => $runId,
                'publication_id' => $publicationId,
                'publication_version' => isset($publication->publication_version) ? (int) $publication->publication_version : null,
                'run_evidence_admission_state' => $runEvidence['summary']['evidence_admission_state'] ?? null,
                'run_evidence_completeness_state' => $runEvidence['summary']['evidence_completeness_state'] ?? null,
                'run_evidence_output_dir' => $this->normalizePathForDisplay($runEvidence['output_dir'] ?? null),
                'run_evidence_file_count' => (int) ($runEvidence['file_count'] ?? count($runEvidence['files'] ?? [])),
                'fixture_status' => 'GENERATED',
                'fixture_path' => $this->normalizePathForDisplay($fixture['fixture_path'] ?? null),
                'replay_id' => (int) $replay['replay_id'],
                'comparison_result' => $replay['comparison_result'] ?? null,
                'replay_status' => $replay['replay_status'] ?? null,
                'mismatch_count' => (int) ($replay['mismatch_count'] ?? count($replay['mismatches'] ?? [])),
                'replay_evidence_admission_state' => $replayEvidence['summary']['evidence_admission_state'] ?? null,
                'replay_evidence_output_dir' => $this->normalizePathForDisplay($replayEvidence['output_dir'] ?? null),
                'replay_evidence_file_count' => (int) ($replayEvidence['file_count'] ?? count($replayEvidence['files'] ?? [])),
                'case_output_dir' => $this->normalizePathForDisplay($caseDir),
            ];
        } catch (\Throwable $e) {
            return [
                'trade_date' => $tradeDate,
                'status' => 'ERROR',
                'passed' => false,
                'reason_code' => $this->reasonCodeFromThrowable($e),
                'error_class' => get_class($e),
                'error_message' => $e->getMessage(),
            ];
        }
    }

    private function casePassed(array $runEvidence, array $replay, array $replayEvidence)
    {
        $mismatchCount = (int) ($replay['mismatch_count'] ?? count($replay['mismatches'] ?? []));

        return ($runEvidence['summary']['evidence_admission_state'] ?? null) === 'ADMITTED_COMPLETE'
            && ($runEvidence['summary']['evidence_completeness_state'] ?? null) === 'COMPLETE'
            && ($replay['comparison_result'] ?? null) === 'MATCH'
            && ($replay['replay_status'] ?? null) === 'PASS'
            && $mismatchCount === 0
            && ($replayEvidence['summary']['evidence_admission_state'] ?? null) === 'ADMITTED_COMPLETE';
    }

    private function resolveRange($startDate, $endDate)
    {
        if ($startDate !== null && $startDate !== '' && $endDate !== null && $endDate !== '') {
            return [(string) $startDate, (string) $endDate];
        }

        if (($startDate !== null && $startDate !== '') || ($endDate !== null && $endDate !== '')) {
            throw new \RuntimeException('COMMAND_MISSING_REQUIRED_INPUT: start_date and end_date must be provided together, or both omitted to use the current publication range.');
        }

        $range = DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->where('pub.is_current', 1)
            ->selectRaw('MIN(ptr.trade_date) as start_date, MAX(ptr.trade_date) as end_date')
            ->first();

        if (! $range || ! $range->start_date || ! $range->end_date) {
            throw new \RuntimeException('NO_READABLE_PUBLICATION: full-range current evidence/replay requires at least one current publication pointer.');
        }

        return [(string) $range->start_date, (string) $range->end_date];
    }

    private function guardDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate, config('market_data.platform.timezone'))->startOfDay();
        $end = Carbon::parse($endDate, config('market_data.platform.timezone'))->startOfDay();

        if ($end->lt($start)) {
            throw new \RuntimeException('COMMAND_INVALID_DATE_RANGE: end_date must be greater than or equal to start_date.');
        }
    }

    private function refreshCounters(array $summary)
    {
        $summary['processed_count'] = count($summary['cases']);
        $summary['success_count'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'SUCCESS' && ($case['passed'] ?? false) === true;
        }));
        $summary['failed_count'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'FAILED';
        }));
        $summary['error_count'] = count(array_filter($summary['cases'], function ($case) {
            return ($case['status'] ?? null) === 'ERROR';
        }));
        $summary['all_passed'] = $summary['processed_count'] === $summary['trading_date_count']
            && $summary['failed_count'] === 0
            && $summary['error_count'] === 0;

        return $summary;
    }

    private function writeSummary($outputDir, array $summary)
    {
        $this->ensureDirectory($outputDir);
        file_put_contents(
            rtrim($outputDir, '/\\').'/market_data_full_range_current_evidence_replay_summary.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function defaultOutputDir($startDate, $endDate)
    {
        return storage_path('app/market_data/evidence/full_range_current_evidence_replay/full_range_current_'.$startDate.'_to_'.$endDate.'_'.Carbon::now(config('market_data.platform.timezone'))->format('Ymd_His'));
    }

    private function ensureDirectory($dir)
    {
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new \RuntimeException('COMMAND_EXECUTION_FAILED: Unable to create output directory: '.$dir);
        }
    }

    private function reasonCodeFromThrowable(\Throwable $e)
    {
        if ($e instanceof NoReadablePublicationException) {
            return $e->reasonCode();
        }

        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }

    private function normalizePathForDisplay($path)
    {
        if ($path === null || $path === '') {
            return null;
        }

        return str_replace('\\', '/', (string) $path);
    }
}
