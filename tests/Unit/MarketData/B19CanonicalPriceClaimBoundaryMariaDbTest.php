<?php

use App\Application\MarketData\Services\MarketDataPriceReadService;
use App\Application\MarketData\Services\MarketDataReadProductService;
use App\Application\MarketData\Services\MarketDataReadinessService;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Console\Commands\MarketData\OperationalStatusCommand;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19CanonicalPriceClaimBoundaryMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    public function test_internally_admissible_fabricated_price_is_not_claimed_as_market_verified(): void
    {
        $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
        $quote =& $body['chart']['result'][0]['indicators']['quote'][0];
        foreach (['open' => 123, 'high' => 130, 'low' => 120, 'close' => 125, 'volume' => 1500] as $field => $value) {
            $quote[$field] = [$value];
        }
        $world = R0025SyntheticV2World::build(['tamper_response' => json_encode($body)]);
        $run = DB::table('eod_runs')->where('run_id', $world['run_id'])->first();
        $date = (string) $run->trade_date_requested;
        $bar = DB::table('eod_bars')->where('run_id', $world['run_id'])->first();
        $this->assertNotNull($bar);
        $this->assertSame(125.0, (float) $bar->close);
        $this->assertSame('VALIDATED', (string) $bar->quality_state);
        $this->assertNotNull($bar->source_observation_id);
        $this->assertSame((int) $world['publication_id'], (int) $bar->publication_id);

        $price = (new MarketDataPriceReadService())->getPriceFacts($date, ['SYNV2']);
        $this->assertTrue($price['is_ready']);
        $this->assertSame(125.0, $price['prices'][0]['raw_close']);
        $product = (new MarketDataReadProductService())->getReadProductForTradeDate($date);
        $this->assertTrue($product['is_ready']);
        $this->assertSame(125.0, $product['rows'][0]['close_price']);
        $readiness = (new MarketDataReadinessService())->readinessForTradeDate($date);
        $this->assertTrue($readiness['is_ready']);
        $this->assertSame((int) $world['publication_id'], $readiness['publication_id']);
        $statusCommand = new OperationalStatusCommand();
        $statusCommand->setLaravel($this->app);
        $statusTester = new \Symfony\Component\Console\Tester\CommandTester($statusCommand);
        $this->assertContains($statusTester->execute(['--requested_date' => $date, '--run_id' => $world['run_id']]), [0, 2]);
        $status = json_decode(trim($statusTester->getDisplay()), true);
        $this->assertIsArray($status);
        $this->assertSame((int) $world['run_id'], $status['run_id']);
        $this->assertSame((int) $world['publication_id'], $status['publication_id']);

        $dir = sys_get_temp_dir().'/e049_price_claim_'.uniqid('', true);
        try {
            $command = new ExportEvidenceCommand();
            $command->setLaravel($this->app);
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $this->assertSame(0, $tester->execute(['--run_id' => $world['run_id'], '--output_dir' => $dir]));
            $this->assertFileExists($dir.'/run_summary.json');
            $payloads = [$price, $product, $readiness, $status, $statusTester->getDisplay(), $tester->getDisplay()];
            foreach (glob($dir.'/*') as $path) {
                if (is_file($path) && preg_match('/\.(json|csv|md)$/', $path)) {
                    $payloads[] = file_get_contents($path);
                }
            }
            foreach ($payloads as $payload) {
                $text = is_string($payload) ? $payload : json_encode($payload);
                $this->assertDoesNotMatchRegularExpression('/market.price.(verified|correct)|verified.against.the.market|price.is.right|market_price_verified|price_correct/i', $text);
            }
        } finally {
            foreach (glob($dir.'/*') ?: [] as $path) if (is_file($path)) unlink($path);
            if (is_dir($dir)) rmdir($dir);
        }
    }
}
