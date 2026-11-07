<?php

use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\CorrectionCommandPreflightService;
use App\Console\Commands\MarketData\RunCorrectionCommand;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
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
        // The shared test database may contain a later snapshot for the default testing
        // profile. Keep this frozen March 25 execution in its own profile so a November
        // snapshot cannot be mistaken for configuration known at the run cutoff.
        config()->set('market_data.governance.environment_profile', 'e049-correction-command-20260325');
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

    public function test_historical_value_rollback_uses_a_new_sealed_correction_publication(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-historical-value-rollback-20260325');
        $originalResponse = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $response = $originalResponse;
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$response) {
            return ['status' => 200, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $date = R0025SyntheticV2World::world()['trade_date'];
        $repo = app(EodCorrectionRepository::class);
        $original = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $originalClose = DB::table('eod_bars')->where('trade_date', $date)->value('close');

        $changedBody = json_decode($originalResponse, true);
        $changedBody['chart']['result'][0]['indicators']['quote'][0]['close'][0] = 106;
        $response = json_encode($changedBody);
        $forward = $repo->createRequest($date, 'DATASET_HASH_CREATED', 'Publish corrected price', 'operator-proof',
            (int) $original->publication_id, (int) $original->run_id);
        $repo->approve($forward->correction_id, 'approver-proof');
        [$forwardCode, $forwardOutput] = $this->runCorrection((int) $forward->correction_id, $date);
        $changed = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $changedClose = DB::table('eod_bars')->where('trade_date', $date)->value('close');

        $response = $originalResponse;
        $reverse = $repo->createRequest($date, 'DATASET_HASH_CREATED', 'Restore previously published values through new correction',
            'operator-proof', (int) $changed->publication_id, (int) $changed->run_id);
        $repo->approve($reverse->correction_id, 'approver-proof');
        [$reverseCode, $reverseOutput] = $this->runCorrection((int) $reverse->correction_id, $date);
        $current = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $publications = DB::table('eod_publications')->where('trade_date', $date)
            ->orderBy('publication_version')->get();

        $this->assertSame(0, $forwardCode, $forwardOutput);
        $this->assertSame(0, $reverseCode, $reverseOutput);
        $this->assertNotSame((int) $original->publication_id, (int) $changed->publication_id);
        $this->assertNotSame((int) $original->publication_id, (int) $current->publication_id);
        $this->assertNotSame((int) $changed->publication_id, (int) $current->publication_id);
        $this->assertNotSame((string) $originalClose, (string) $changedClose);
        $this->assertSame((string) $originalClose, (string) DB::table('eod_bars')->where('trade_date', $date)->value('close'));
        $this->assertCount(3, $publications);
        $this->assertSame([1, 2, 3], $publications->pluck('publication_version')->map(function ($version) { return (int) $version; })->all());
        $this->assertSame((int) $changed->publication_id, (int) $publications[2]->supersedes_publication_id);
        $this->assertSame(['SEALED', 'SEALED', 'SEALED'], $publications->pluck('seal_state')->all());
        $this->assertSame([0, 0, 1], $publications->pluck('is_current')->map(function ($currentFlag) { return (int) $currentFlag; })->all());
        $this->assertStringContainsString('correction_outcome=PUBLISHED', $reverseOutput);
        $this->assertStringContainsString('candidate_publication_switch=true', $reverseOutput);
        foreach ([$forwardOutput, $reverseOutput] as $output) {
            $this->assertStringStartsWith('operation_mode=PRE_APPLY_DISCLOSURE', $output);
            $this->assertStringContainsString('new_run_id=PENDING_ALLOCATION', $output);
            $this->assertStringContainsString('new_config_snapshot_id=PENDING_ALLOCATION', $output);
            $this->assertStringContainsString('source_identity=api:', $output);
            $this->assertStringContainsString('pointer_impact=CONDITIONAL_NEW_SEALED_PUBLICATION_OR_PRIOR_CURRENT_PRESERVED', $output);
            $this->assertMatchesRegularExpression('/candidate_config_content_hash=([a-f0-9]{64})/', $output);
            preg_match('/candidate_config_content_hash=([a-f0-9]{64})/', $output, $hash);
            $this->assertStringContainsString('actual_config_hash='.$hash[1], $output);
            $this->assertLessThan(strpos($output, "\nrun_id="), strpos($output, 'operation_mode=PRE_APPLY_DISCLOSURE'));
        }

        if ($stateDir = getenv('B19_E049_STATE_DIR')) {
            if (! is_dir($stateDir)) mkdir($stateDir, 0777, true);
            file_put_contents($stateDir.'/correction_historical_value_rollback.json', json_encode([
                'trade_date' => $date,
                'original_pointer' => (array) $original,
                'changed_pointer' => (array) $changed,
                'final_pointer' => (array) $current,
                'original_close' => $originalClose,
                'changed_close' => $changedClose,
                'final_close' => DB::table('eod_bars')->where('trade_date', $date)->value('close'),
                'publications' => $publications->map(function ($publication) { return (array) $publication; })->all(),
                'forward_command_exit' => $forwardCode,
                'reverse_command_exit' => $reverseCode,
                'forward_command_output' => $forwardOutput,
                'reverse_command_output' => $reverseOutput,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Recovery may repair an unsafe pointer, but it must not bypass the
        // correction/reseal chain by repointing a valid completed publication.
        $recovery = (new EodRunRepository())->acquireLinkedRecoveryOwner($date, 'api', (int) $current->run_id);
        $before = [
            'pointer' => (array) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first(),
            'publications' => DB::table('eod_publications')->where('trade_date', $date)->orderBy('publication_id')->get()->toArray(),
            'runs' => DB::table('eod_runs')->where('trade_date_requested', $date)->orderBy('run_id')->get()->toArray(),
        ];
        $refusal = null;
        try {
            (new EodPublicationRepository())->restorePriorCurrentPublication(
                $date, (int) $changed->publication_id, (int) $changed->run_id, $recovery
            );
        } catch (\Throwable $e) {
            $refusal = $e->getMessage();
        }
        $after = [
            'pointer' => (array) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first(),
            'publications' => DB::table('eod_publications')->where('trade_date', $date)->orderBy('publication_id')->get()->toArray(),
            'runs' => DB::table('eod_runs')->where('trade_date_requested', $date)->orderBy('run_id')->get()->toArray(),
        ];
        if ($stateDir = getenv('B19_E049_STATE_DIR')) {
            file_put_contents($stateDir.'/direct_historical_pointer_refusal.json', json_encode([
                'acting_recovery_run_id' => (int) $recovery->run_id,
                'target_superseded_publication_id' => (int) $changed->publication_id,
                'refusal' => $refusal, 'before' => $before, 'after' => $after,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        $this->assertNotNull($refusal, 'Direct historical pointer rollback was accepted without a new correction publication.');
        $this->assertStringContainsString('POINTER_HISTORY_ROLLBACK_REQUIRES_CORRECTION', $refusal);
        $this->assertSame(json_encode($before), json_encode($after));
    }

    public function test_correction_preapply_mismatch_refuses_before_first_run_or_snapshot_write_and_valid_retry_works(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-correction-preapply-refusal-20260325');
        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$response) {
            return ['status' => 200, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $this->app->instance(MarketDataPipelineService::class, $world['pipeline']);
        $date = R0025SyntheticV2World::world()['trade_date'];
        $prior = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $repo = app(EodCorrectionRepository::class);
        $correction = $repo->createRequest($date, 'DATASET_HASH_CREATED', 'Preapply mismatch refusal', 'operator-proof',
            (int) $prior->publication_id, (int) $prior->run_id);
        $repo->approve($correction->correction_id, 'approver-proof');
        $expected = app(CorrectionCommandPreflightService::class)->inspect((int) $correction->correction_id, $date, 'api');
        $snapshot = function () use ($date, $correction) {
            return json_encode([
                'runs' => DB::table('eod_runs')->where('trade_date_requested', $date)->orderBy('run_id')->get()->all(),
                'fence' => DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first(),
                'events' => DB::table('eod_run_events')->orderBy('event_id')->get()->all(),
                'config' => DB::table('md_config_snapshots')->orderBy('config_snapshot_id')->get()->all(),
                'correction' => DB::table('eod_dataset_corrections')->where('correction_id', $correction->correction_id)->first(),
                'pointer' => DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first(),
            ]);
        };
        $before = $snapshot();
        foreach ([
            ['candidate_config_content_hash', str_repeat('0', 64)],
            ['source_identity', 'api:unapproved-provider'],
            ['baseline_publication_id', -1],
        ] as [$field, $wrong]) {
            $tampered = $expected;
            $tampered[$field] = $wrong;
            try {
                (new EodRunRepository())->getOrCreateOwningRun($date, 'api', 'INGEST_BARS', (int) $prior->run_id, 'correction', $tampered);
                $this->fail('Undisclosed '.$field.' was accepted before acquisition.');
            } catch (RuntimeException $error) {
                $this->assertStringContainsString('COMMAND_CORRECTION_PREFLIGHT_CHANGED', $error->getMessage());
            }
            $this->assertSame($before, $snapshot(), 'Preapply mismatch partially persisted: '.$field);
        }
        $changed = json_decode($response, true);
        $changed['chart']['result'][0]['indicators']['quote'][0]['close'][0] = 106;
        $response = json_encode($changed);
        [$code, $output] = $this->runCorrection((int) $correction->correction_id, $date);
        $this->assertSame(0, $code, $output);
        $this->assertStringStartsWith('operation_mode=PRE_APPLY_DISCLOSURE', $output);
        $this->assertNotSame((int) $prior->publication_id,
            (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->value('publication_id'));
        preg_match('/actual_run_id=(\d+)/', $output, $allocated);
        $this->assertNotEmpty($allocated);
        $created = DB::table('eod_run_events')->where('run_id', (int) $allocated[1])
            ->where('event_type', 'RUN_CREATED')->first();
        $this->assertNotNull($created);
        $payload = json_decode($created->event_payload_json, true);
        $this->assertSame(hash('sha256', \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::canonicalJson($expected)),
            $payload['pre_apply_disclosure_sha256']);
        $this->assertSame($expected['candidate_config_content_hash'], $payload['config_hash']);
    }

}
