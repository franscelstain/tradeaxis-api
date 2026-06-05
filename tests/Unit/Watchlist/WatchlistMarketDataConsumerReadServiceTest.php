<?php

use App\Application\MarketData\Services\MarketDataWatchlistReadService;
use App\Application\Watchlist\Services\WatchlistMarketDataConsumerReadService;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class WatchlistMarketDataConsumerReadServiceTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_watchlist_consumer_read_model_returns_only_valid_candidates_from_current_readable_publication(): void
    {
        $this->seedTicker(1, 'BBCA', 'Bank Central Asia');
        $this->seedTicker(2, 'BBRI', 'Bank Rakyat Indonesia');
        $this->seedReadablePublication('2026-05-19', 3, 2);

        $this->seedBar('2026-05-19', 1, 3, 2, 9000, 123456, 9000);
        $this->seedIndicator('2026-05-19', 1, 3, 2);
        $this->seedEligibility('2026-05-19', 1, 3, 2, 1);

        $this->seedBar('2026-05-19', 2, 3, 2, 4000, 100000, 4000);
        $this->seedIndicator('2026-05-19', 2, 3, 2, [
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY',
        ]);
        $this->seedEligibility('2026-05-19', 2, 3, 2, 1);

        $result = (new WatchlistMarketDataConsumerReadService())->getCandidateUniverseForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WATCHLIST_MARKET_DATA_READY', $result['reason_code']);
        $this->assertSame('current_readable_publication_pointer', $result['source_contract']['resolution_mode']);
        $this->assertTrue($result['source_contract']['forbids_raw_staging_latest_max_date_bypass']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame(3, $result['run_id']);
        $this->assertSame(1, $result['candidate_count']);
        $this->assertSame(0, $result['excluded_count']);

        $candidate = $result['candidates'][0];
        $this->assertSame('BBCA', $candidate['ticker_code']);
        $this->assertSame('ELIGIBLE', $candidate['eligibility_state']);
        $this->assertSame('v1', $candidate['indicator_set_version']);
        $this->assertSame(123456789000.0, $candidate['indicators']['dv20idr']);
        $this->assertArrayNotHasKey('score', $candidate);
        $this->assertArrayNotHasKey('recommendation', $candidate);
    }

    public function test_watchlist_consumer_read_model_fails_closed_when_market_data_is_not_readable(): void
    {
        $this->seedTicker(1, 'BBCA', 'Bank Central Asia');
        $this->seedReadablePublication('2026-05-18', 2, 1);
        $this->seedBar('2026-05-18', 1, 2, 1, 8900);
        $this->seedIndicator('2026-05-18', 1, 2, 1);
        $this->seedEligibility('2026-05-18', 1, 2, 1, 1);

        $result = (new WatchlistMarketDataConsumerReadService())->getCandidateUniverseForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['reason_code']);
        $this->assertSame('MARKET_DATA_NOT_READY', $result['watchlist_reason_code']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $result['pointer_resolve_status']);
        $this->assertSame([], $result['candidates']);
        $this->assertSame(0, $result['candidate_count']);
    }

    public function test_watchlist_consumer_service_rejects_invalid_or_incomplete_rows_even_if_upstream_payload_contains_them(): void
    {
        $payload = [
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
            'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'rows' => [
                $this->marketDataPayloadRow('BBCA'),
                array_merge($this->marketDataPayloadRow('BBRI'), [
                    'eligibility_state' => 'NOT_ELIGIBLE',
                    'indicator_is_valid' => 0,
                    'indicator_invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY',
                    'ma50' => null,
                ]),
            ],
        ];

        $service = new WatchlistMarketDataConsumerReadService(new class($payload) extends MarketDataWatchlistReadService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function getWatchlistMarketDataForTradeDate(string $tradeDate): array
            {
                return $this->payload;
            }
        });

        $result = $service->getCandidateUniverseForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WATCHLIST_MARKET_DATA_READY_WITH_EXCLUSIONS', $result['reason_code']);
        $this->assertSame(1, $result['candidate_count']);
        $this->assertSame(1, $result['excluded_count']);
        $this->assertSame('BBCA', $result['candidates'][0]['ticker_code']);
        $this->assertSame('BBRI', $result['excluded_rows'][0]['ticker_code']);
        $this->assertContains('WATCHLIST_ELIGIBILITY_NOT_ELIGIBLE', $result['excluded_rows'][0]['reason_codes']);
        $this->assertContains('WATCHLIST_INDICATOR_INVALID', $result['excluded_rows'][0]['reason_codes']);
        $this->assertContains('WATCHLIST_INDICATOR_INVALID_REASON_PRESENT', $result['excluded_rows'][0]['reason_codes']);
        $this->assertContains('WATCHLIST_REQUIRED_FIELD_MISSING:ma50', $result['excluded_rows'][0]['reason_codes']);
    }

    private function marketDataPayloadRow(string $tickerCode): array
    {
        return [
            'trade_date' => '2026-05-19',
            'ticker_code' => $tickerCode,
            'ticker_name' => $tickerCode.' Tbk',
            'close_price' => 9000.0,
            'volume' => 123456,
            'eligibility_state' => 'ELIGIBLE',
            'eligibility_reason_code' => null,
            'indicator_is_valid' => 1,
            'indicator_invalid_reason_code' => null,
            'dv20idr' => 123456789000.0,
            'atr14_pct' => 2.1,
            'vol_ratio' => 1.5,
            'roc_20' => 5.2,
            'hh20' => 9100.0,
            'ma20' => 8750.0,
            'ma50' => 8600.0,
            'close_to_hh20_pct' => -1.1,
            'close_vs_ma20_pct' => 2.8,
            'close_vs_ma50_pct' => 4.6,
            'ma20_slope_pct' => 1.2,
            'rs_20_vs_ihsg' => 3.4,
            'indicator_set_version' => 'v1',
            'source_name' => 'API_FREE',
        ];
    }
}
