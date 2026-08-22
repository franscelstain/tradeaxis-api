<?php

use App\Console\Commands\MarketData\IngestSectorIndexBarsApiCommand;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class IngestSectorIndexBarsApiCommandTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        $this->seedVerifiedMarketCalendarDate('2026-05-19');
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_dry_run_fetches_sector_index_bars_without_writing_rows(): void
    {
        $this->bindYahooSectorFetcher();

        $tester = $this->executeCommand(['start_date' => '2026-05-19']);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $display);
        $this->assertStringContainsString('reason_code=COMMAND_DRY_RUN_ONLY', $display);
        $this->assertStringContainsString('requested_benchmark_count=11', $display);
        $this->assertStringContainsString('fetched_row_count=11', $display);
        $this->assertStringContainsString('upserted_count=0', $display);
        $this->assertStringContainsString('IDXFINANCE:IDXFINANCE.JK', $display);
        $this->assertSame(0, DB::table('market_benchmark_bars')->count());
    }

    public function test_apply_writes_sector_index_bars_from_api_source(): void
    {
        $this->bindYahooSectorFetcher();

        $tester = $this->executeCommand([
            'start_date' => '2026-05-19',
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $display);
        $this->assertStringContainsString('upserted_count=11', $display);
        $this->assertSame(11, DB::table('market_benchmark_bars')->count());

        $row = DB::table('market_benchmark_bars')->where('benchmark_code', 'IDXFINANCE')->first();

        $this->assertNotNull($row);
        $this->assertSame('2026-05-19', $row->trade_date);
        $this->assertSame(1225.0, (float) $row->close_price);
        $this->assertSame('yahoo_finance', $row->provider);
        $this->assertSame('IDXFINANCE.JK', $row->provider_symbol);
    }

    public function test_missing_provider_rows_block_apply_without_writing(): void
    {
        $this->app->instance(PublicApiEodBarsAdapter::class, new PublicApiEodBarsAdapter(function ($url) {
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                            'timestamp' => [],
                            'indicators' => ['quote' => [['open' => [], 'high' => [], 'low' => [], 'close' => [], 'volume' => []]]],
                        ]],
                        'error' => null,
                    ],
                ]),
            ];
        }));

        $tester = $this->executeCommand([
            'start_date' => '2026-05-19',
            '--apply' => true,
        ]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('reason_code=SECTOR_INDEX_API_INGEST_INCOMPLETE', $display);
        $this->assertStringContainsString('RUN_SOURCE_NO_VALID_DATA', $display);
        $this->assertSame(0, DB::table('market_benchmark_bars')->count());
    }

    private function executeCommand(array $input): CommandTester
    {
        $command = new IngestSectorIndexBarsApiCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }

    private function bindYahooSectorFetcher(): void
    {
        $timestamp = Carbon::parse('2026-05-19', 'Asia/Jakarta')->timestamp;

        $this->app->instance(PublicApiEodBarsAdapter::class, new PublicApiEodBarsAdapter(function ($url) use ($timestamp) {
            preg_match('#/chart/([^?]+)#', $url, $matches);
            $symbol = isset($matches[1]) ? urldecode($matches[1]) : 'UNKNOWN';

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'symbol' => $symbol,
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [$timestamp],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [1200.0],
                                    'high' => [1230.0],
                                    'low' => [1190.0],
                                    'close' => [1225.0],
                                    'volume' => [0],
                                ]],
                                'adjclose' => [[
                                    'adjclose' => [1225.0],
                                ]],
                            ],
                        ]],
                        'error' => null,
                    ],
                ]),
            ];
        }));
    }
}
