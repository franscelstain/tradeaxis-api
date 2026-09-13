<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\EodBarsIngestService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` -- `MD-S003-R0007`, the four named observation defects of the degraded-acquisition
 * scenario family.
 *
 * `Historical_Replay_and_Data_Quality_Backtest.md` (`MD-S003`) requires, under "Required scenario
 * families / Degraded acquisition and expectation", that
 * "stale/schema-invalid/wrong-date/zero-price observations quarantine or hold". The heading makes
 * this an obligation on the replay suite: a scenario proving each defect must actually run.
 *
 * Three of the four already had executing guards elsewhere and are bound here to the guard that
 * runs them. The fourth -- stale/wrong-date -- did not. The only candidate,
 * `CoverageEdgeCaseBoundaryB15Test::test_a_row_outside_the_requested_trade_date_is_refused_as_stale`,
 * asserts that the string `RUN_STALE_DATA` appears in the ingest service source. That proves the
 * constant is spelled somewhere, not that a misdated row is refused, so it was not admissible as
 * this predicate's proof and is deliberately not named in the map below. The two behavioural
 * guards in this class execute the real `EodBarsIngestService` boundary instead.
 *
 * The map is paired with a control that ingests the *same* fixture dated on the requested day and
 * asserts it becomes canonical. Without that control a refusal proves only that the fixture is
 * unacceptable for some reason; with it, the trade date is the only difference, so the date is
 * what causes the refusal.
 */
class B18DegradedObservationDefectCorpusTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md';

    /** The requested trade date every fixture in this class is ingested against. */
    private const REQUESTED = '2026-03-24';

    /** @var array<string,array<int,array>> what reached the artifact writer */
    private $captured = ['valid' => [], 'invalid' => []];

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        parent::tearDown();
    }

    /**
     * Reviewed map: the contract's own defect token => the guard that executes it, and which of the
     * two permitted outcomes ("quarantine or hold") that guard proves.
     *
     * @return array<string,array<string,string>>
     */
    private function defectMap(): array
    {
        return [
            'stale' => [
                'guard' => 'B18DegradedObservationDefectCorpusTest::test_a_prior_date_row_holds_the_run_under_its_own_stale_reason',
                'outcome' => 'hold',
            ],
            'schema-invalid' => [
                'guard' => 'PublicApiEodBarsAdapterTest::test_range_response_persists_partial_invalid_row_evidence_instead_of_silently_skipping_it',
                'outcome' => 'quarantine',
            ],
            'wrong-date' => [
                'guard' => 'B18DegradedObservationDefectCorpusTest::test_a_row_dated_after_the_requested_date_holds_the_run_rather_than_being_relabelled',
                'outcome' => 'hold',
            ],
            'zero-price' => [
                'guard' => 'CanonicalRawImportBoundaryTest::test_a_zero_price_placeholder_cannot_become_canonical',
                'outcome' => 'quarantine',
            ],
        ];
    }

    /** @return array<int,string> the four defect tokens, read from the frozen contract line */
    private function contractDefects(): array
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $lines = preg_split('/\R/', (string) file_get_contents($path));

        $inSection = false;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '### ') === 0) {
                $inSection = ($trimmed === '### Degraded acquisition and expectation');

                continue;
            }
            if (! $inSection) {
                continue;
            }
            if (preg_match('/^-\s+(\S+)\s+observations quarantine or hold;$/', $trimmed, $m) === 1) {
                return explode('/', $m[1]);
            }
        }

        $this->fail('MD-S003 no longer carries the "observations quarantine or hold" member under '
            .'"Degraded acquisition and expectation"; this guard is bound to text that moved and '
            .'must be re-read against the current contract, not relaxed');
    }

    /**
     * The map may not drift from the contract. A defect added to `MD-S003` with no guard behind it
     * would otherwise leave `R0007` partly unproven while this class stayed green.
     */
    public function test_the_contract_line_and_the_reviewed_defect_map_name_exactly_the_same_defects(): void
    {
        $contract = $this->contractDefects();
        $mapped = array_keys($this->defectMap());
        sort($contract);
        sort($mapped);

        $this->assertSame($contract, $mapped,
            'MD-S003 and the reviewed defect map disagree about which observation defects must '
                .'quarantine or hold');

        // The count is pinned separately: a map that silently shrank to three would still match a
        // contract list that shrank with it, and matching each other is not the requirement.
        $this->assertCount(4, $contract, 'MD-S003 no longer names four observation defects');
    }

    /**
     * Every named guard must exist. Without this the map is a list of intentions -- a guard renamed
     * or deleted elsewhere would leave its defect with no executing scenario and nobody would be
     * told.
     */
    public function test_every_named_defect_guard_exists_and_is_executable(): void
    {
        $missing = [];
        foreach ($this->defectMap() as $defect => $entry) {
            [$class, $method] = explode('::', $entry['guard']);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file)) {
                $missing[] = $defect.' -> '.$class.' (file not found)';

                continue;
            }
            if (strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $defect.' -> '.$entry['guard'];
            }
        }

        $this->assertSame([], $missing,
            'these observation defects name a guard that no longer exists');
    }

    /**
     * "quarantine or hold" is a disjunction over two named outcomes, and a map that recorded a
     * third would be answering a different requirement.
     */
    public function test_every_defect_is_bound_to_one_of_the_two_permitted_outcomes(): void
    {
        $outcomes = [];
        foreach ($this->defectMap() as $defect => $entry) {
            $this->assertContains($entry['outcome'], ['quarantine', 'hold'],
                $defect.' is bound to an outcome MD-S003 does not permit');
            $outcomes[$entry['outcome']] = true;
        }

        // Both outcomes must actually be exercised, or the corpus proves only one half of the
        // disjunction and the other half is an untested claim.
        $this->assertArrayHasKey('quarantine', $outcomes, 'no defect in the corpus proves quarantine');
        $this->assertArrayHasKey('hold', $outcomes, 'no defect in the corpus proves hold');
    }

    /**
     * `MD-S003-R0007` -- stale.
     *
     * A row carrying the previous trading day is offered for the requested date. Accepting it would
     * republish yesterday's close as today's, which is the exact masquerade the sibling member
     * "no prior-date result masquerades as requested-date fresh data" forbids.
     */
    public function test_a_prior_date_row_holds_the_run_under_its_own_stale_reason(): void
    {
        $refusal = $this->ingestExpectingRefusal([$this->row(['trade_date' => '2026-03-23'])]);

        $this->assertSame('RUN_STALE_DATA', $refusal->reasonCode(),
            'a prior-date row must hold the run under RUN_STALE_DATA rather than be ingested for '
                .'the requested date');
        $this->assertSame(self::REQUESTED, $refusal->context()['requested_date']);
        $this->assertSame(['2026-03-23'], $refusal->context()['seen_trade_dates'],
            'the refusal must name the date it actually saw, or the operator cannot tell a stale '
                .'row from an empty response');
        $this->assertSame([], $this->captured['valid'],
            'no canonical row may be written by a run the stale boundary refused');
    }

    /**
     * `MD-S003-R0007` -- wrong-date.
     *
     * The mirror case: a row dated *after* the requested date. It is asserted separately because
     * the two are refused by the same comparison only as long as that comparison is equality; a
     * "not older than" rewrite would keep the stale guard green and let this one through.
     */
    public function test_a_row_dated_after_the_requested_date_holds_the_run_rather_than_being_relabelled(): void
    {
        $refusal = $this->ingestExpectingRefusal([$this->row(['trade_date' => '2026-03-25'])]);

        $this->assertSame('RUN_STALE_DATA', $refusal->reasonCode(),
            'a row dated outside the requested trade date must hold the run in either direction');
        $this->assertSame(['2026-03-25'], $refusal->context()['seen_trade_dates']);
        $this->assertSame([], $this->captured['valid'],
            'the misdated row must not be relabelled onto the requested date');
    }

    /**
     * A mixed batch is refused too, and this is the case a per-row filter would get wrong: dropping
     * the misdated row and publishing the good one looks like a clean partial success while the
     * run silently lost coverage it was never told about.
     */
    public function test_a_batch_mixing_the_requested_date_with_another_holds_the_whole_run(): void
    {
        $refusal = $this->ingestExpectingRefusal([
            $this->row(['ticker_code' => 'BBCA', 'source_row_ref' => 'yahoo:BBCA:good']),
            $this->row(['ticker_code' => 'BBRI', 'trade_date' => '2026-03-23', 'source_row_ref' => 'yahoo:BBRI:stale']),
        ]);

        $this->assertSame('RUN_STALE_DATA', $refusal->reasonCode());
        $this->assertSame([self::REQUESTED, '2026-03-23'], $refusal->context()['seen_trade_dates']);
        $this->assertSame([], $this->captured['valid'],
            'the well-dated row must not be published while its batch carried a misdated one');
    }

    /**
     * The control. The same fixture, dated on the requested day, becomes canonical.
     *
     * Without it the three guards above would pass equally well against an ingest that refuses
     * everything, and "refuses everything" is not what MD-S003 asks for.
     */
    public function test_the_same_fixture_dated_on_the_requested_day_becomes_canonical(): void
    {
        $this->ingest([$this->row()]);

        $this->assertCount(1, $this->captured['valid'],
            'the identical fixture on the requested date must be accepted, or the refusals above '
                .'prove nothing about the trade date');
        $this->assertSame(self::REQUESTED, $this->captured['valid'][0]['trade_date']);
        $this->assertSame([], $this->captured['invalid']);
    }

    /** One well-formed source row on the requested date, overridable field by field. */
    private function row(array $override = []): array
    {
        return array_merge([
            'ticker_code' => 'BBCA',
            'trade_date' => self::REQUESTED,
            'open' => 100,
            'high' => 110,
            'low' => 99,
            'close' => 108,
            'volume' => 1000,
            'adj_close' => 104,
            'source_name' => 'YAHOO_FINANCE',
            'source_row_ref' => 'yahoo:BBCA:'.self::REQUESTED,
            'captured_at' => '2026-03-24T17:00:00+07:00',
            'source_observation_id' => 901,
            'source_observation_persisted' => true,
        ], $override);
    }

    /** Runs the real ingest service and records what reached the artifact writer. */
    private function ingest(array $sourceRows): void
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'YAHOO_FINANCE', 'canonicalization_version' => 'canon_v1'],
                'scope' => ['raw_product_code' => 'RAW'],
            ],
        ]);

        $localSource = $this->createMock(LocalFileEodBarsAdapter::class);
        $apiSource = $this->createMock(PublicApiEodBarsAdapter::class);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);
        $observations = $this->createMock(SourceObservationRepository::class);

        $tickers->method('resolveTickerIdsByCodes')->willReturn(['BBCA' => 1, 'BBRI' => 2]);
        $tickers->method('resolveTemporalContextsByCodes')->willReturnCallback(function (array $codes) {
            $contexts = [];
            foreach (array_values($codes) as $index => $code) {
                $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
            }

            return $contexts;
        });

        $publications->method('findCurrentPublicationForTradeDate')->willReturn(null);
        $publications->method('getOrCreateCandidatePublication')->willReturn((object) [
            'publication_id' => 990,
            'publication_version' => 1,
        ]);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $this->captured = ['valid' => [], 'invalid' => []];
        $artifacts->method('replaceBars')->willReturnCallback(function ($date, $pubId, $runId, array $valid, array $invalid) {
            $this->captured = ['valid' => $valid, 'invalid' => $invalid];

            return null;
        });

        $run = new EodRun([
            'run_id' => 91,
            'trade_date_requested' => self::REQUESTED,
            'knowledge_cutoff_at' => self::REQUESTED.' 18:00:00',
        ]);

        (new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations))
            ->ingestAcquiredRows($run, self::REQUESTED, 'api', $sourceRows, ['source_acquisition_state' => 'SUCCESS']);
    }

    /** Runs the ingest and returns the refusal, failing if the rows were accepted instead. */
    private function ingestExpectingRefusal(array $sourceRows): SourceAcquisitionException
    {
        try {
            $this->ingest($sourceRows);
        } catch (SourceAcquisitionException $e) {
            return $e;
        }

        $this->fail('the ingest accepted rows it must have refused; '
            .count($this->captured['valid']).' canonical row(s) reached the artifact writer');
    }
}
