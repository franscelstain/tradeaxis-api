<?php

use App\Application\MarketData\Services\EodBarsIngestService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B19` — where the rows of `invalid_bars_export.csv` come from (`MD-S075-R0147..R0152`, `R0156`, `R0157`).
 *
 * The export is only as good as the rows the real ingest persists as rejected. This guard feeds the REAL `EodBarsIngestService`
 * a batch of source rows that this file writes by hand, so the expectation for each rejected row is its own input: its row
 * reference, its observation id, its ticker and the defect it was given. Nothing is derived from the exporter or from the
 * producer. For every row it then checks what the ingest handed to the artifact writer:
 *
 *  - a defective row is written as REJECTED with the reason its defect names, the trade date, the ticker (or NULL when the
 *    ticker is unknown), the LISTING when the point-in-time mapping resolved and NULL when it did not (never a stand-in), the
 *    immutable source observation it arrived with (NULL only when it arrived with none), the canonical source code and its own
 *    source row reference;
 *  - none of the rejected rows reaches the canonical bars (`R0157`), and the one clean row is canonical and not rejected;
 *  - every reason a rejected row can carry is a registered, active code of the reason-code registry seed (`R0152`).
 */
class B19InvalidBarsProducerProvenanceTest extends TestCase
{
    use \Tests\Support\MocksProducerInputCapture;
    use InteractsWithMarketDataConfig;

    private const REQUESTED = '2026-03-24';

    /** @var array{valid:array<int,array>,invalid:array<int,array>} */
    private $captured = ['valid' => [], 'invalid' => []];

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        parent::tearDown();
    }

    private function row(array $override): array
    {
        return array_merge([
            'trade_date' => self::REQUESTED, 'open' => 100, 'high' => 110, 'low' => 99, 'close' => 108, 'volume' => 1000, 'adj_close' => 104,
            'source_name' => 'YAHOO_FINANCE', 'captured_at' => '2026-03-24T17:00:00+07:00', 'source_observation_persisted' => true,
        ], $override);
    }

    /** The hand-written input: one clean row and one row per defect, each with its own ref and observation id. */
    private function input(): array
    {
        return [
            $this->row(['ticker_code' => 'CLEAN', 'source_row_ref' => 'ref_clean', 'source_observation_id' => 9001]),
            $this->row(['ticker_code' => 'OHLC', 'source_row_ref' => 'ref_ohlc', 'source_observation_id' => 9002, 'high' => 90, 'low' => 99]),
            $this->row(['ticker_code' => 'ZEROPX', 'source_row_ref' => 'ref_nonpositive', 'source_observation_id' => 9003, 'close' => 0]),
            $this->row(['ticker_code' => 'GHOST', 'source_row_ref' => 'ref_unknown_ticker', 'source_observation_id' => 9004]),
            $this->row(['ticker_code' => 'NOLISTING', 'source_row_ref' => 'ref_no_listing', 'source_observation_id' => 9005]),
            $this->row(['ticker_code' => 'NOOBS', 'source_row_ref' => 'ref_no_observation', 'source_observation_id' => null, 'source_observation_persisted' => false]),
            $this->row(['ticker_code' => 'NOTACCEPTED', 'source_row_ref' => 'ref_not_accepted', 'source_observation_id' => 9007]),
            $this->row(['ticker_code' => 'ZEROVOL', 'source_row_ref' => 'ref_zero_volume', 'source_observation_id' => 9008, 'volume' => 0]),
        ];
    }

    /** What each defective input must become: ref => [reason, ticker_id, listing_id, source_observation_id]. */
    private function expectedRejections(): array
    {
        return [
            'ref_ohlc' => ['BAR_INVALID_OHLC_ORDER', 2, 7002, 9002],
            'ref_nonpositive' => ['BAR_NON_POSITIVE_PRICE', 3, 7003, 9003],
            'ref_unknown_ticker' => ['BAR_TICKER_MAPPING_MISSING', null, null, 9004],
            'ref_no_listing' => ['BAR_TEMPORAL_LISTING_MAPPING_MISSING', 5, null, 9005],
            'ref_no_observation' => ['BAR_SOURCE_OBSERVATION_MISSING', 6, 7006, null],
            'ref_not_accepted' => ['BAR_SOURCE_OBSERVATION_NOT_ACCEPTED', 7, 7007, 9007],
            'ref_zero_volume' => ['BAR_ZERO_VOLUME_PRICE_MOVEMENT', 8, 7008, 9008],
        ];
    }

    private function ingest(array $sourceRows): void
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'YAHOO_FINANCE', 'canonicalization_version' => 'canon_v1'],
                'scope' => ['raw_product_code' => 'RAW'],
            ],
        ]);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);
        $observations = $this->createMock(SourceObservationRepository::class);

        // ticker master: GHOST is unknown. Point-in-time listings: NOLISTING has none.
        $tickers->method('resolveTickerIdsByCodes')->willReturn(['CLEAN' => 1, 'OHLC' => 2, 'ZEROPX' => 3, 'NOLISTING' => 5, 'NOOBS' => 6, 'NOTACCEPTED' => 7, 'ZEROVOL' => 8]);
        $tickers->method('resolveTemporalContextsByCodes')->willReturn([
            'CLEAN' => ['listing_id' => 7001, 'board_code' => 'RG'], 'OHLC' => ['listing_id' => 7002, 'board_code' => 'RG'],
            'ZEROPX' => ['listing_id' => 7003, 'board_code' => 'RG'], 'NOOBS' => ['listing_id' => 7006, 'board_code' => 'RG'],
            'NOTACCEPTED' => ['listing_id' => 7007, 'board_code' => 'RG'], 'ZEROVOL' => ['listing_id' => 7008, 'board_code' => 'RG'],
        ]);
        $publications->method('findCurrentPublicationForTradeDate')->willReturn(null);
        $publications->method('getOrCreateCandidatePublication')->willReturn((object) ['publication_id' => 990, 'publication_version' => 1]);
        $observations->method('existsAccepted')->willReturnCallback(function ($id) {
            return (int) $id !== 9007;
        });
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $this->captured = ['valid' => [], 'invalid' => []];
        $artifacts->method('replaceBars')->willReturnCallback(function ($date, $pubId, $runId, array $valid, array $invalid) {
            $this->captured = ['valid' => $valid, 'invalid' => $invalid];

            return null;
        });
        $run = new EodRun(['run_id' => 91, 'trade_date_requested' => self::REQUESTED, 'knowledge_cutoff_at' => self::REQUESTED.' 18:00:00']);

        (new EodBarsIngestService($this->createMock(LocalFileEodBarsAdapter::class), $this->createMock(PublicApiEodBarsAdapter::class), $tickers, $artifacts, $publications, null, $observations, null, null, $this->mockProducerInputCapture()))
            ->ingestAcquiredRows($run, self::REQUESTED, 'api', $sourceRows, ['source_acquisition_state' => 'SUCCESS']);
    }

    /** @return array<string,array> rejected rows keyed by their source row reference */
    private function rejected(): array
    {
        $this->ingest($this->input());
        $byRef = [];
        foreach ($this->captured['invalid'] as $row) {
            $this->assertArrayNotHasKey($row['source_row_ref'], $byRef, 'one rejected row per source row here');
            $byRef[$row['source_row_ref']] = $row;
        }

        return $byRef;
    }

    /** `R0147..R0152`: each rejected row carries its own provenance, as written in the input. */
    public function test_each_rejected_row_carries_the_provenance_of_the_input_row_it_came_from(): void
    {
        $byRef = $this->rejected();
        $this->assertEqualsCanonicalizing(array_keys($this->expectedRejections()), array_keys($byRef), 'exactly the seven defective inputs are rejected, by their own references');
        foreach ($this->expectedRejections() as $ref => [$reason, $ticker, $listing, $observation]) {
            $row = $byRef[$ref];
            $this->assertSame($reason, $row['invalid_reason_code'], $ref.' reason (R0152)');
            $this->assertSame(self::REQUESTED, $row['trade_date'], $ref.' trade date (R0147)');
            $this->assertSame($listing, $row['listing_id'], $ref.' listing: resolved or NULL, never a stand-in (R0148)');
            $this->assertSame($ticker, $row['ticker_id'], $ref.' ticker (R0149)');
            $this->assertSame($observation, $row['source_observation_id'], $ref.' observation (R0150)');
            $this->assertSame('YAHOO_FINANCE', $row['source'], $ref.' source');
            $this->assertSame($ref, $row['source_row_ref'], $ref.' source row reference (R0151)');
            $this->assertSame(91, $row['run_id']);
        }
    }

    /** `R0157`: no rejected row is a canonical bar, and the clean row is canonical and not rejected. */
    public function test_rejected_rows_are_never_canonical_and_the_clean_row_is_not_rejected(): void
    {
        $byRef = $this->rejected();
        $this->assertSame([1], array_column($this->captured['valid'], 'ticker_id'), 'only the clean ticker is canonical');
        $this->assertSame([], array_values(array_intersect(array_column($this->captured['valid'], 'ticker_id'), array_filter(array_column($byRef, 'ticker_id')))));
        $this->assertArrayNotHasKey('ref_clean', $byRef);
        $validObservations = array_column($this->captured['valid'], 'source_observation_id');
        foreach ($byRef as $ref => $row) {
            if ($row['source_observation_id'] !== null) {
                $this->assertNotContains($row['source_observation_id'], $validObservations, $ref.': a rejected row and a canonical bar never share an observation here');
            }
        }
    }

    /** The seven independently seeded rejection paths use active reason codes. */
    public function test_each_seeded_rejection_reason_is_a_registered_active_code(): void
    {
        $sql = (string) file_get_contents(dirname(__DIR__, 3).'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        preg_match_all("/^\('([A-Z0-9_]+)', '[A-Z_]+', '(?:[^'\\\\]|\\\\.|'')*', '(?:INFO|WARN|HARD)', ([01])\)/m", $sql, $m, PREG_SET_ORDER);
        $registry = [];
        foreach ($m as $r) {
            $registry[$r[1]] = $r[2] === '1';
        }
        foreach (array_column($this->rejected(), 'invalid_reason_code') as $code) {
            $this->assertArrayHasKey($code, $registry, $code.' is registered');
            $this->assertTrue($registry[$code], $code.' is active');
        }
    }

    /** Current bounded defect: the conflict path emits a reason absent from the locked registry. */
    public function test_conflicting_source_rows_expose_the_unregistered_reason_owner_gap(): void
    {
        $this->ingest([
            $this->row(['ticker_code' => 'CLEAN', 'source_row_ref' => 'ref_clean', 'source_observation_id' => 9001]),
            $this->row(['ticker_code' => 'OHLC', 'source_row_ref' => 'ref_conflict_a', 'source_observation_id' => 9002]),
            $this->row(['ticker_code' => 'OHLC', 'source_row_ref' => 'ref_conflict_b', 'source_observation_id' => 9012, 'close' => 106]),
        ]);
        $this->assertSame([1], array_column($this->captured['valid'], 'ticker_id'));
        $this->assertSame(['BAR_CONFLICTING_SOURCE_ROWS', 'BAR_CONFLICTING_SOURCE_ROWS'], array_column($this->captured['invalid'], 'invalid_reason_code'));
        $registry = (string) file_get_contents(dirname(__DIR__, 3).'/docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md');
        $this->assertFalse(strpos($registry, '`BAR_CONFLICTING_SOURCE_ROWS`'), 'the code has no locked registry entry; do not admit the reason predicate');
        $seed = (string) file_get_contents(dirname(__DIR__, 3).'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        $this->assertFalse(strpos($seed, "('BAR_CONFLICTING_SOURCE_ROWS',"), 'the code has no executable registry seed');
    }
}
