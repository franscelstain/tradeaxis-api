<?php

use App\Application\Watchlist\Services\WeeklySwingWatchlistRuntimeService;
use Illuminate\Support\Facades\DB;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class WeeklySwingWatchlistRuntimeServiceTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    private string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
        $this->outputPath = 'storage/app/watchlist/runtime/.tmp-c168-weekly-swing-runtime-test.json';
        @unlink($this->outputPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->outputPath);
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_c168_executes_real_market_data_to_ticker_watchlist_pipeline(): void
    {
        $this->seedReadyStock('BBCA', 1, 9000.0);
        $this->seedReadyStock('TLKM', 2, 3200.0, [
            'roc20' => '3.1000000000',
            'vol_ratio' => '1.2000000000',
        ]);

        $result = (new WeeklySwingWatchlistRuntimeService())->execute(
            '2026-05-19',
            $this->outputPath,
            [
                'created_at' => '2026-07-24T12:00:00Z',
                'paramset' => $this->permissiveRuntimeParamset(),
                'paramset_source' => 'C168_INTEGRATION_TEST_PARAMSET',
            ]
        );

        $this->assertSame(
            'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_PASSED_REAL_TICKER_WATCHLIST_GENERATED',
            $result['status'],
            json_encode($result['pipeline_stages'])
        );
        $this->assertTrue($result['pipeline_pass']);
        $this->assertTrue($result['real_runtime_integration_executed']);
        $this->assertTrue($result['real_market_data_consumed']);
        $this->assertTrue($result['real_stock_output_generated']);
        $this->assertFalse($result['production_runtime_activated']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['controlled_rollout_executed']);
        $this->assertFalse($result['official_output_published']);
        $this->assertSame(2, $result['source_lineage']['publication_id']);
        $this->assertSame(1, $result['source_lineage']['publication_version']);
        $this->assertSame(3, $result['source_lineage']['run_id']);
        $this->assertSame('2026-05-19', $result['trade_date_effective']);
        $this->assertSame('RESOLVED_READABLE_CURRENT', $result['source_lineage']['pointer_resolve_status']);
        $this->assertSame('C168_INTEGRATION_TEST_PARAMSET', $result['paramset_source']);
        $this->assertNotEmpty($result['watchlist_rows']);
        $this->assertSame($result['watchlist_tickers'], $result['summary']['recommended_tickers']);
        $this->assertContains('BBCA', $result['watchlist_tickers']);
        $this->assertNotContains('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $result['watchlist_tickers']);
        $this->assertSame([], $result['invalid_output_rows']);

        foreach ([
            'market_data_consumer_read',
            'candidate_universe',
            'scoring',
            'plan_grouping',
            'recommendation',
        ] as $stage) {
            $this->assertTrue($result['pipeline_stages'][$stage]['invoked'], $stage);
            $this->assertTrue($result['pipeline_stages'][$stage]['is_ready'], $stage);
        }

        foreach ($result['watchlist_rows'] as $row) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9][A-Z0-9.\-]{0,19}$/D', $row['ticker_code']);
            $this->assertGreaterThan(0, $row['ticker_id']);
            $this->assertGreaterThan(0, $row['close_price']);
            $this->assertSame(2, $row['publication_id']);
            $this->assertSame(1, $row['publication_version']);
            $this->assertSame(3, $row['run_id']);
            $this->assertSame('2026-05-19', $row['trade_date_effective']);
            $this->assertIsNumeric($row['recommendation_score']);
            $this->assertArrayHasKey('dv20_idr', $row['market_metrics']);
            $this->assertNotEmpty($row['reason_codes']);
            $this->assertArrayNotHasKey('candidate_code', $row);
        }

        $this->assertFileExists($this->outputPath);
        $persisted = json_decode((string) file_get_contents($this->outputPath), true);
        $this->assertSame($result['output_hash'], $persisted['output_hash']);
        $this->assertSame($result['watchlist_tickers'], $persisted['watchlist_tickers']);
        $this->assertSame($result['idempotency_key'], $persisted['idempotency_key']);
    }

    public function test_c168_is_idempotent_for_the_same_publication_and_paramset(): void
    {
        $this->seedReadyStock('BBCA', 1, 9000.0);
        $options = [
            'created_at' => '2026-07-24T12:00:00Z',
            'paramset' => $this->permissiveRuntimeParamset(),
            'paramset_source' => 'C168_INTEGRATION_TEST_PARAMSET',
        ];

        $first = (new WeeklySwingWatchlistRuntimeService())->execute('2026-05-19', $this->outputPath, $options);
        $second = (new WeeklySwingWatchlistRuntimeService())->execute(
            '2026-05-19',
            $this->outputPath,
            array_merge($options, ['created_at' => '2026-07-24T13:00:00Z'])
        );

        $this->assertSame($first['status'], $second['status']);
        $this->assertSame($first['idempotency_key'], $second['idempotency_key']);
        $this->assertSame($first['output_hash'], $second['output_hash']);
        $this->assertSame($first['watchlist_tickers'], $second['watchlist_tickers']);
        $this->assertTrue($second['write_skipped_existing_output']);
    }

    public function test_c168_paramset_hash_is_stable_for_equivalent_key_order(): void
    {
        $this->seedReadyStock('BBCA', 1, 9000.0);
        $service = new WeeklySwingWatchlistRuntimeService();
        $left = $service->execute('2026-05-19', $this->outputPath, [
            'created_at' => '2026-07-24T12:00:00Z',
            'paramset' => [
                'paramset_code' => 'C168_CANONICAL_HASH_TEST',
                'grouping' => [
                    'top_picks' => [
                        'min_score_total' => 0.0,
                        'max_items' => 10,
                    ],
                ],
            ],
        ]);

        $right = $service->execute('2026-05-19', $this->outputPath, [
            'created_at' => '2026-07-24T13:00:00Z',
            'paramset' => [
                'grouping' => [
                    'top_picks' => [
                        'max_items' => 10,
                        'min_score_total' => 0.0,
                    ],
                ],
                'paramset_code' => 'C168_CANONICAL_HASH_TEST',
            ],
        ]);

        $this->assertSame($left['paramset_hash'], $right['paramset_hash']);
        $this->assertSame($left['idempotency_key'], $right['idempotency_key']);
        $this->assertTrue($right['write_skipped_existing_output']);
    }

    public function test_c168_fails_closed_without_a_current_readable_market_data_publication(): void
    {
        $result = (new WeeklySwingWatchlistRuntimeService())->execute(
            '2026-05-19',
            $this->outputPath,
            ['created_at' => '2026-07-24T12:00:00Z']
        );

        $this->assertSame(
            'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_MARKET_DATA_NOT_READY',
            $result['status']
        );
        $this->assertFalse($result['pipeline_pass']);
        $this->assertFalse($result['real_runtime_integration_executed']);
        $this->assertFalse($result['real_stock_output_generated']);
        $this->assertSame([], $result['watchlist_rows']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['pipeline_stages']['market_data_consumer_read']['reason_code']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['controlled_rollout_executed']);
        $this->assertFileExists($this->outputPath);
    }

    public function test_c168_rejects_invalid_trade_date_before_reading_market_data(): void
    {
        $result = (new WeeklySwingWatchlistRuntimeService())->execute(
            '2026-02-30',
            $this->outputPath,
            ['created_at' => '2026-07-24T12:00:00Z']
        );

        $this->assertSame(
            'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_INVALID_TRADE_DATE',
            $result['status']
        );
        $this->assertSame([], $result['pipeline_stages']);
        $this->assertFalse($result['pipeline_pass']);
    }

    private function seedReadyStock(string $tickerCode, int $tickerId, float $close, array $indicatorOverrides = []): void
    {
        if (! DB::table('eod_runs')->where('run_id', 3)->exists()) {
            $this->seedReadablePublication('2026-05-19', 3, 2);
        }
        $this->seedTicker($tickerId, $tickerCode, $tickerCode.' Tbk');
        $this->seedBar('2026-05-19', $tickerId, 3, 2, $close, 200000, $close);
        $this->seedIndicator('2026-05-19', $tickerId, 3, 2, $indicatorOverrides);
        $this->seedEligibility('2026-05-19', $tickerId, 3, 2, 1);
    }

    private function permissiveRuntimeParamset(): array
    {
        return [
            'paramset_code' => 'C168_INTEGRATION_TEST',
            'grouping' => [
                'top_picks' => [
                    'min_score_total' => 0.0,
                    'max_items' => 10,
                ],
                'secondary' => [
                    'min_score_total' => 0.0,
                    'max_items' => 10,
                ],
                'watch_only' => [
                    'min_score_total' => 0.0,
                    'max_items' => 10,
                ],
                'avoid' => [
                    'max_score_total_below' => 0.0,
                ],
            ],
            'recommendation' => [
                'min_recommendation_score' => 0.0,
                'borderline_min_score' => 0.0,
                'max_recommended_items' => 10,
            ],
        ];
    }
}
