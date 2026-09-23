<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\Exceptions\NoReadablePublicationException;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;

class ReplayBackfillService
{
    /**
     * `F-MD-B18-A002-015` / C2 (consolidated remediation package): "case/fixture tidak dikenal ...
     * tolak sebelum bekerja; tidak memilih pointer atau menebak expected outcome." The single source
     * of truth for both the pre-execution rejection below and `expectedOutcomeForFixtureCase()`, so
     * the two can never diverge into two different notions of "known".
     */
    private const KNOWN_FIXTURE_CASES = [
        'valid_case' => 'MATCH',
        'reason_code_mismatch_case' => 'MISMATCH',
        'broken_manifest_case' => 'ERROR',
        'missing_file_case' => 'ERROR',
    ];

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
        // Rejected before any work: no calendar lookup, no directory creation, no replay execution
        // for any date. An unrecognised case must never silently become an "always passes" run
        // (`expectedOutcomeForFixtureCase()` returning null previously let exactly that happen) and
        // must never have an outcome guessed for it -- it is refused outright, the same way a
        // missing fixture root already is below.
        if (! array_key_exists($fixtureCase, self::KNOWN_FIXTURE_CASES)) {
            throw new \RuntimeException('REPLAY_BACKFILL_UNKNOWN_FIXTURE_CASE: '.$fixtureCase
                .'. Known fixture cases: '.implode(', ', array_keys(self::KNOWN_FIXTURE_CASES)).'.');
        }

        $this->guardDateRange($startDate, $endDate);

        $fixtureRoot = $fixtureRoot ?: storage_path('app/market_data/replay-fixtures');
        if (! is_dir($fixtureRoot)) {
            throw new \RuntimeException('REPLAY_INDEPENDENT_FIXTURE_ROOT_REQUIRED: '.$fixtureRoot);
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
                $explicitPublicationId = $this->explicitPublicationIdFromFixture($fixtureRoot, $tradeDate);
                $publication = $this->publications->buildManifestByPublicationId($explicitPublicationId);
                if (! $publication) {
                    throw new \RuntimeException('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_NOT_FOUND: declared publication_id '
                        .$explicitPublicationId.' for '.$tradeDate.' does not exist.');
                }
                if ((string) $publication['trade_date'] !== (string) $tradeDate) {
                    throw new \RuntimeException('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_TRADE_DATE_MISMATCH: fixture directory declares '
                        .$tradeDate.' but publication_id '.$explicitPublicationId.' belongs to '.$publication['trade_date'].'.');
                }

                $fixturePath = rtrim($fixtureRoot, '/\\').'/'.$tradeDate.'/publication_'.$explicitPublicationId;
                if (! is_dir($fixturePath)) {
                    throw new \RuntimeException('REPLAY_INDEPENDENT_FIXTURE_MISSING: '.$fixturePath);
                }
                $result = $this->replays->verifyRunAgainstFixture($publication['run_id'], $fixturePath, null, $explicitPublicationId);
                $evidence = $this->evidence->exportReplayEvidence($result['replay_id'], $result['trade_date'], rtrim($outputDir, '/').'/'.$tradeDate);

                $observedOutcome = $result['comparison_result'];
                $passed = $expectedOutcome ? $observedOutcome === $expectedOutcome : true;

                $cases[] = [
                    'trade_date' => $tradeDate,
                    'status' => 'SUCCESS',
                    'publication_id' => $explicitPublicationId,
                    'run_id' => $publication['run_id'],
                    'replay_id' => (int) $result['replay_id'],
                    'expected_outcome' => $expectedOutcome,
                    'observed_outcome' => $observedOutcome,
                    'replay_status' => $result['replay_status'] ?? null,
                    'comparison_note' => $result['comparison_note'],
                    'fixture_case' => $fixtureCase,
                    'fixture_path' => $this->normalizePathForDisplay($fixturePath),
                    'passed' => $passed,
                    'evidence_output_dir' => $this->normalizePathForDisplay($evidence['output_dir']),
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
                    'replay_status' => 'BLOCKED',
                    'passed' => $passed,
                    'reason_code' => $this->reasonCodeFromException($e),
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
            'fixture_root' => $this->normalizePathForDisplay($fixtureRoot),
            'fixture_path' => null,
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

    /**
     * `MD-S050-R0027`/`MD-S003-R0002` (D-MD-B18-A002-005 Q3): "historical/backfill verification uses
     * explicit publication/fixture identity ... No latest/current substitution." The fixture
     * directory itself is the declared manifest: `{fixtureRoot}/{tradeDate}/publication_{N}` names
     * the immutable publication this date's replay targets, frozen at fixture-creation time. This
     * never consults `EodPublicationRepository::findCurrentPublicationForTradeDate` or any other
     * pointer/current lookup, so a correction that moves the pointer after the fixture was created
     * cannot retarget which publication gets verified.
     */
    private function explicitPublicationIdFromFixture($fixtureRoot, $tradeDate)
    {
        $dateDir = rtrim($fixtureRoot, '/\\').'/'.$tradeDate;
        $matches = [];
        foreach (is_dir($dateDir) ? scandir($dateDir) : [] as $entry) {
            if (preg_match('/^publication_(\d+)$/', $entry, $m) && is_dir($dateDir.'/'.$entry)) {
                $matches[] = (int) $m[1];
            }
        }

        if ($matches === []) {
            throw new \RuntimeException('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_UNDECLARED: no publication_<id> fixture '
                .'directory declared under '.$dateDir.'. PUBLICATION_EXACT backfill requires an explicitly declared '
                .'immutable publication identity; the current pointer is never consulted.');
        }
        if (count($matches) > 1) {
            sort($matches);
            throw new \RuntimeException('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_AMBIGUOUS: '.count($matches).' publication_<id> '
                .'fixture directories declared under '.$dateDir.' ('.implode(', ', $matches).'); exactly one must be declared.');
        }

        return $matches[0];
    }

    private function expectedOutcomeForFixtureCase($fixtureCase)
    {
        // `execute()` already rejects any case not in `self::KNOWN_FIXTURE_CASES` before reaching
        // here, so `?? null` is a fail-closed default for a path that should now be unreachable, not
        // a second definition of "known" that could drift from the one above.
        return self::KNOWN_FIXTURE_CASES[$fixtureCase] ?? null;
    }

    private function normalizePathForDisplay($path)
    {
        return str_replace('\\', '/', (string) $path);
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if ($e instanceof NoReadablePublicationException) {
            return $e->reasonCode();
        }

        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}
