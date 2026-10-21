<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Console\Commands\MarketData\SealDatasetCommand;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19OperatorHashDecisionMariaDbTest extends TestCase
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

    public static function mandatoryArtifactHashes(): array
    {
        return [
            'bars' => ['bars_batch_hash'],
            'indicators' => ['indicators_batch_hash'],
            'eligibility' => ['eligibility_batch_hash'],
        ];
    }

    private function worldThroughHash(): array
    {
        return R0025SyntheticV2World::build(['run_workflow' => function ($pipeline, $date) {
            $run = null;
            foreach (['INGEST_BARS' => 'completeIngest', 'COMPUTE_INDICATORS' => 'completeIndicators',
                'BUILD_ELIGIBILITY' => 'completeEligibility', 'HASH' => 'completeHash'] as $stage => $method) {
                $input = new MarketDataStageInput($date, 'api', $run ? $run->run_id : null,
                    $stage, null, false, null, 'full_publish');
                $run = $pipeline->{$method}($input);
            }

            return $run;
        }]);
    }

    public function test_complete_artifact_hashes_reach_a_sealed_readable_publication(): void
    {
        $world = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $run = DB::table('eod_runs')->where('run_id', $world['run_id'])->first();
        $publication = DB::table('eod_publications')->where('publication_id', $world['publication_id'])->first();
        foreach (array_column(self::mandatoryArtifactHashes(), 0) as $field) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $run->{$field});
            $this->assertSame($run->{$field}, $publication->{$field});
        }
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame($world['publication_id'], (int) DB::table('eod_current_publication_pointer')
            ->where('trade_date', $date)->value('publication_id'));
    }

    /** @dataProvider mandatoryArtifactHashes */
    public function test_a_missing_mandatory_hash_blocks_seal_and_requested_date_readability(string $missingHash): void
    {
        $world = $this->worldThroughHash();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $runId = $world['run_id'];
        $publicationId = $world['publication_id'];
        $this->assertNotNull($publicationId);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) DB::table('eod_runs')->where('run_id', $runId)->value($missingHash));
        DB::table('eod_runs')->where('run_id', $runId)->update([$missingHash => null]);
        $this->assertNull(DB::table('eod_runs')->where('run_id', $runId)->value($missingHash));

        try {
            $world['pipeline']->completeSeal(new MarketDataStageInput($date, 'api', $runId,
                'SEAL', null, false, null, 'full_publish'));
            $this->fail('The SEAL stage accepted a run missing '.$missingHash.'.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('RUN_SEAL_PRECONDITION_FAILED', $error->getMessage());
        }

        $run = EodRun::findOrFail($runId);
        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        $this->assertSame('UNSEALED', $publication->seal_state);
        $this->assertSame(0, (int) $publication->is_current);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', $date)->exists());
        $this->assertNotSame('READABLE', (string) $run->publishability_state);
        $this->assertTrue(DB::table('eod_run_events')->where('run_id', $runId)
            ->where('event_type', 'SEAL_BLOCKED')->where('severity', 'ERROR')
            ->where('reason_code', 'RUN_SEAL_PRECONDITION_FAILED')->exists());
    }

    public function test_missing_indicator_artifact_rows_refuse_seal_and_keep_the_requested_date_unreadable(): void
    {
        $world = $this->worldThroughHash();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $runId = $world['run_id'];
        $publicationId = $world['publication_id'];
        $before = DB::table('eod_indicators')->where('publication_id', $publicationId)->count();
        $this->assertGreaterThan(0, $before);
        $missingArtifact = (array) DB::table('eod_indicators')->where('publication_id', $publicationId)->first();
        DB::table('eod_indicators')->where('publication_id', $publicationId)->delete();
        $this->assertSame(0, DB::table('eod_indicators')->where('publication_id', $publicationId)->count());

        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $command = new SealDatasetCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $exit = $tester->execute(['--requested_date' => $date, '--source_mode' => 'api', '--run_id' => $runId]);
        $output = $tester->getDisplay();
        $this->assertSame(1, $exit, $output);
        $this->assertStringContainsString('status=BLOCKED', $output);
        $this->assertStringContainsString('reason_code=DATASET_MANIFEST_INVALID', $output);
        $this->assertStringContainsString('publication snapshot incomplete for indicators', $output);
        $this->assertStringContainsString('next_action=Repair upstream artifact completeness and revalidate before seal.', $output);

        $this->assertSame('UNSEALED', DB::table('eod_publications')->where('publication_id', $publicationId)->value('seal_state'));
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', $date)->exists());
        $this->assertNotSame('READABLE', (string) EodRun::findOrFail($runId)->publishability_state);

        DB::table('eod_indicators')->insert($missingArtifact);
        $retryCommand = new SealDatasetCommand();
        $retryCommand->setLaravel($this->app);
        $retryTester = new CommandTester($retryCommand);
        $retryExit = $retryTester->execute(['--requested_date' => $date, '--source_mode' => 'api', '--run_id' => $runId]);
        $this->assertSame(1, $retryExit, $retryTester->getDisplay());
        $this->assertStringContainsString('reason_code=RUN_OWNER_CONFLICT', $retryTester->getDisplay());
        $this->assertStringContainsString(
            'next_action=Verify the date owner and its evidence before governed recovery.',
            $retryTester->getDisplay()
        );
        $this->assertSame('UNSEALED', DB::table('eod_publications')->where('publication_id', $publicationId)->value('seal_state'));
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', $date)->exists());
    }
}
