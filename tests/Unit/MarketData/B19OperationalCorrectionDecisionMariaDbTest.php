<?php

use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Console\Commands\MarketData\RunCorrectionCommand;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19OperationalCorrectionDecisionMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    private function runCorrection(int $correctionId, string $date): array
    {
        $command = new RunCorrectionCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $code = $tester->execute([
            'correction_id' => $correctionId,
            '--requested_date' => $date,
            '--source_mode' => 'api',
        ]);

        return [$code, $tester->getDisplay()];
    }

    public function test_real_correction_command_reports_switch_and_preserves_prior_on_no_switch(): void
    {
        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $status = 200;
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$response, &$status) {
            return ['status' => $status, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $date = R0025SyntheticV2World::world()['trade_date'];
        $repository = app(EodCorrectionRepository::class);
        $prior = (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id');
        $priorRun = (int) DB::table('eod_publications')->where('publication_id', $prior)->value('run_id');
        $changedBody = json_decode($response, true);
        $changedBody['chart']['result'][0]['indicators']['quote'][0]['close'][0] = 106;
        $response = json_encode($changedBody);

        $changed = $repository->createRequest($date, 'DATASET_HASH_CREATED', 'Operator correction command proof', 'operator-proof', $prior, $priorRun);
        $repository->approve($changed->correction_id, 'approver-proof');
        [$changedCode, $changedOutput] = $this->runCorrection((int) $changed->correction_id, $date);
        $changedPointer = (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id');
        $this->assertSame(0, $changedCode, $changedOutput);
        $this->assertStringContainsString('correction_outcome=PUBLISHED', $changedOutput);
        $this->assertStringContainsString('candidate_publication_switch=true', $changedOutput);
        $this->assertNotSame($prior, $changedPointer);

        $unchanged = $repository->createRequest($date, 'DATASET_HASH_CREATED', 'Operator no-op correction proof', 'operator-proof', $changedPointer, (int) DB::table('eod_publications')->where('publication_id', $changedPointer)->value('run_id'));
        $repository->approve($unchanged->correction_id, 'approver-proof');
        [$unchangedCode, $unchangedOutput] = $this->runCorrection((int) $unchanged->correction_id, $date);
        $this->assertSame(0, $unchangedCode, $unchangedOutput);
        $this->assertStringContainsString('correction_outcome=UNCHANGED', $unchangedOutput);
        $this->assertStringContainsString('candidate_publication_switch=false', $unchangedOutput);
        $this->assertSame($changedPointer, (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id'));

        $failed = $repository->createRequest($date, 'DATASET_HASH_CREATED', 'Operator failed correction proof', 'operator-proof', $changedPointer, (int) DB::table('eod_publications')->where('publication_id', $changedPointer)->value('run_id'));
        $repository->approve($failed->correction_id, 'approver-proof');
        $status = 401;
        [$failedCode, $failedOutput] = $this->runCorrection((int) $failed->correction_id, $date);
        $this->assertSame(1, $failedCode, $failedOutput);
        $this->assertStringContainsString('correction_outcome=FAILED', $failedOutput);
        $this->assertStringContainsString('candidate_publication_switch=false', $failedOutput);
        $this->assertSame($changedPointer, (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id'));

        if ($path = getenv('B19_E039_CORRECTION_OUTPUT')) {
            file_put_contents($path, json_encode([
                'date' => $date, 'prior_publication_id' => $prior, 'changed_pointer_id' => $changedPointer,
                'changed_code' => $changedCode, 'changed_output' => $changedOutput,
                'unchanged_code' => $unchangedCode, 'unchanged_output' => $unchangedOutput,
                'failed_code' => $failedCode, 'failed_output' => $failedOutput,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
