<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

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
 * W09 — import-only and canonical RAW, stage 8.
 *
 * Exit gate: "zero placeholder, provider `adj_close` sebagai RAW close, direct publish, dan
 * untraceable row tidak mungkin masuk canonical readable path."
 *
 * Owner contracts:
 *   docs/market_data/book/Import_Promote_Separation_Contract.md
 *   docs/market_data/book/Canonicalization_Contract_EOD_Bars.md
 *   docs/market_data/book/EOD_Bars_Contract.md
 *   docs/market_data/book/Invalid_Bar_Storage_Policy_LOCKED.md
 *
 * Each prohibition is asserted on what the ingest service actually hands to the artifact writer,
 * because that is the only place a canonical row can be created. Asserting on stored rows instead
 * would prove the current corpus is clean without proving the next row cannot dirty it.
 */
class CanonicalRawImportBoundaryTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    /** @var array */
    private $captured;

    /** @var int */
    private $sealCalls = 0;

    /** @var int */
    private $promoteCalls = 0;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        parent::tearDown();
    }

    private function baseRow(array $override = []): array
    {
        return array_merge([
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-24',
            'open' => 100,
            'high' => 110,
            'low' => 99,
            'close' => 108,
            'volume' => 1000,
            'adj_close' => 104,
            'source_name' => 'YAHOO_FINANCE',
            'source_row_ref' => 'yahoo:BBCA:2026-03-24',
            'captured_at' => '2026-03-24T17:00:00+07:00',
            'source_observation_id' => 901,
            'source_observation_persisted' => true,
        ], $override);
    }

    /**
     * Runs one ingest and returns what reached the artifact writer, so a test can inspect the
     * canonical rows and the rejected rows separately.
     */
    private function ingest(array $sourceRows, array $tickerIds, $acceptedObservation = true): array
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

        $tickers->method('resolveTickerIdsByCodes')->willReturn($tickerIds);
        $tickers->method('resolveTemporalContextsByCodes')->willReturnCallback(function (array $codes) {
            $contexts = [];
            foreach ($codes as $index => $code) {
                $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
            }

            return $contexts;
        });

        $publications->method('findCurrentPublicationForTradeDate')->willReturn(null);
        $publications->method('getOrCreateCandidatePublication')->willReturn((object) [
            'publication_id' => 990,
            'publication_version' => 1,
        ]);

        $publications->method('sealCandidatePublication')->willReturnCallback(function () {
            $this->sealCalls++;
        });
        $publications->method('promoteCandidateToCurrent')->willReturnCallback(function () {
            $this->promoteCalls++;
        });

        $observations->method('existsAccepted')->willReturn($acceptedObservation);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $this->captured = ['valid' => [], 'invalid' => []];
        $artifacts->method('replaceBars')->willReturnCallback(function ($date, $pubId, $runId, array $valid, array $invalid) {
            $this->captured = ['valid' => $valid, 'invalid' => $invalid];

            return null;
        });

        $run = new EodRun(['run_id' => 91, 'trade_date_requested' => '2026-03-24']);
        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations);

        try {
            $service->ingestAcquiredRows($run, '2026-03-24', 'api', $sourceRows, ['source_acquisition_state' => 'SUCCESS']);
        } catch (\App\Infrastructure\MarketData\Source\SourceAcquisitionException $e) {
            /*
             * A run where no row survives never reaches the artifact writer — it refuses first, and
             * reports the rejections through the exception context instead. That is the same
             * refusal, so the reason codes are read from there.
             */
            $context = $e->context();
            $this->captured['exception'] = $e->getMessage();
            $this->captured['reason_summary'] = $context['invalid_reason_summary'] ?? [];
        }

        return $this->captured;
    }

    /**
     * Reason codes reach the test by one of two routes depending on whether any row survived, so
     * both are collected: the invalid set handed to the writer, and the refusal context raised
     * when nothing survived at all.
     */
    private function reasonCodes(array $result): array
    {
        $codes = array_map(function ($row) {
            return is_array($row) ? ($row['invalid_reason_code'] ?? $row['reason_code'] ?? null) : null;
        }, $result['invalid'] ?? []);

        return array_values(array_filter(array_merge($codes, array_keys($result['reason_summary'] ?? []))));
    }

    /**
     * A zero price is not a cheap price; it is the absence of one wearing a number. Publishing it
     * would put a -100% return into every window that touches the date.
     */
    public function test_a_zero_price_placeholder_cannot_become_canonical(): void
    {
        $result = $this->ingest([$this->baseRow(['close' => 0])], ['BBCA' => 1]);

        $this->assertCount(0, $result['valid']);
        $this->assertContains('BAR_NON_POSITIVE_PRICE', $this->reasonCodes($result), 'the row is rejected with a named reason, not dropped');
    }

    /**
     * Provider adjusted close stays in observation evidence. Letting it into the canonical row is
     * how an adjusted value silently becomes a raw one two columns later.
     */
    public function test_provider_adjusted_close_never_reaches_the_canonical_row(): void
    {
        $result = $this->ingest([$this->baseRow(['close' => 108, 'adj_close' => 104])], ['BBCA' => 1]);

        $this->assertCount(1, $result['valid']);
        $this->assertNull($result['valid'][0]['adj_close']);
        $this->assertSame(108, $result['valid'][0]['close'], 'the raw close is untouched by the provider adjusted series');
    }

    /**
     * A row that cannot name the observation it came from is not publishable. This is the
     * prohibition that was unenforceable before: the guard was gated on configuration binding,
     * which has never existed, so it never ran.
     */
    public function test_a_row_without_a_source_observation_cannot_become_canonical(): void
    {
        $result = $this->ingest([
            $this->baseRow(['source_observation_id' => null, 'source_observation_persisted' => null]),
        ], ['BBCA' => 1]);

        $this->assertCount(0, $result['valid']);
        $this->assertContains('BAR_SOURCE_OBSERVATION_MISSING', $this->reasonCodes($result));
    }

    /**
     * Naming an observation is not enough; it must be one the platform actually accepted. An id
     * pointing at a failed or unresolvable observation looks like lineage while proving nothing.
     */
    public function test_a_row_citing_a_non_accepted_observation_cannot_become_canonical(): void
    {
        $result = $this->ingest([$this->baseRow()], ['BBCA' => 1], false);

        $this->assertCount(0, $result['valid']);
        $this->assertContains('BAR_SOURCE_OBSERVATION_NOT_ACCEPTED', $this->reasonCodes($result));
    }

    /**
     * A canonical row carries its full traceability, not a subset. Each field answers a different
     * question — which instrument, which acquisition, which rules, which product.
     */
    public function test_a_canonical_row_carries_complete_traceability(): void
    {
        $result = $this->ingest([$this->baseRow()], ['BBCA' => 1]);

        $this->assertCount(1, $result['valid']);
        $row = $result['valid'][0];

        $this->assertNotEmpty($row['listing_id'], 'which instrument, as of this date');
        $this->assertSame(901, $row['source_observation_id'], 'which acquisition');
        $this->assertSame('canon_v1', $row['canonicalization_version'], 'which rules produced it');
        $this->assertSame('RAW', $row['price_product_code'], 'which price product it is');
        $this->assertSame('VALIDATED', $row['quality_state']);
        $this->assertSame('REGULAR', $row['session_code']);
    }

    /**
     * Traceability must not depend on configuration binding. Config binding has never been
     * populated, so a canonical row that required it would never be traceable — which is exactly
     * the state all 756,329 existing rows are in.
     */
    public function test_traceability_does_not_depend_on_configuration_binding(): void
    {
        $result = $this->ingest([$this->baseRow()], ['BBCA' => 1]);

        $this->assertCount(1, $result['valid']);
        $this->assertNull($result['valid'][0]['config_snapshot_id'], 'CONFIG_UNBOUND is recorded, not fatal at import');
        $this->assertSame(901, $result['valid'][0]['source_observation_id'], 'and lineage is present regardless');
    }

    /**
     * One unresolvable instrument must not discard the rest of the market. Rejection is per row,
     * which is what keeps missing separable from invalid.
     */
    public function test_one_untraceable_row_does_not_discard_the_traceable_ones(): void
    {
        $result = $this->ingest([
            $this->baseRow(),
            $this->baseRow(['ticker_code' => 'BBRI', 'source_observation_id' => null, 'source_observation_persisted' => null]),
        ], ['BBCA' => 1, 'BBRI' => 2]);

        $this->assertCount(1, $result['valid']);
        $this->assertSame(1, $result['valid'][0]['ticker_id']);
        $this->assertContains('BAR_SOURCE_OBSERVATION_MISSING', $this->reasonCodes($result));
    }

    /**
     * Import creates a candidate and stops. Sealing and pointer movement are a separate decision
     * with their own gates; an import that could publish would bypass every one of them.
     */
    public function test_import_creates_a_candidate_without_sealing_or_switching_the_pointer(): void
    {
        $this->sealCalls = 0;
        $this->promoteCalls = 0;

        $result = $this->ingest([$this->baseRow()], ['BBCA' => 1]);

        $this->assertCount(1, $result['valid'], 'a successful import still produced a canonical candidate row');
        $this->assertSame(0, $this->sealCalls, 'import must not seal');
        $this->assertSame(0, $this->promoteCalls, 'import must not move the current pointer');
    }
}
