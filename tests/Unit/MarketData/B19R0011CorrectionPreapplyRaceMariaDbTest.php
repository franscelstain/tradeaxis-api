<?php

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

use App\Application\MarketData\Services\CorrectionCommandPreflightService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Application\MarketData\Services\ManualSourceInputContext;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;

/** A committed second connection revokes approval after the first transactional read. */
class B19R0011CorrectionPreapplyRaceMariaDbTest extends B19OwnerFenceMariaDbTest
{
    public function test_correction_approval_rejection_between_disclosure_and_acquisition_cannot_use_stale_snapshot(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-correction-preapply-race-20260325');
        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        R0025SyntheticV2World::build(['http_fetcher' => static function () use ($response) {
            return ['status' => 200, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $date = R0025SyntheticV2World::world()['trade_date'];
        $prior = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $repo = app(EodCorrectionRepository::class);
        $correction = $repo->createRequest($date, 'DATASET_HASH_CREATED', 'Two-connection preapply authority race',
            'operator-proof', (int) $prior->publication_id, (int) $prior->run_id);
        $repo->approve($correction->correction_id, 'approver-proof');
        $preflight = app(CorrectionCommandPreflightService::class)->inspect((int) $correction->correction_id, $date, 'api');
        $before = [
            'runs' => DB::table('eod_runs')->count(),
            'events' => DB::table('eod_run_events')->count(),
            'snapshots' => DB::table('md_config_snapshots')->count(),
            'fence' => DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first(),
            'pointer' => DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first(),
        ];
        $service = new E049CorrectionPreflightRaceService(app(EodCorrectionRepository::class),
            app(EodPublicationRepository::class), app(MarketDataConfigSnapshotRepository::class),
            app(ManualSourceInputContext::class));
        $rejected = false;
        $blocked = false;
        $service->afterFirstTransactionalInspect = function () use ($correction, &$rejected, &$blocked) {
            $this->onIndependentOwnerConnection(function () use ($correction, &$rejected, &$blocked) {
                DB::statement('SET SESSION innodb_lock_wait_timeout = 1');
                try {
                    (new EodCorrectionRepository())->markRejected((int) $correction->correction_id,
                        'Independent approver revoked before allocation');
                    $rejected = true;
                } catch (\Illuminate\Database\QueryException $error) {
                    $this->assertStringContainsString('Lock wait timeout exceeded', $error->getMessage());
                    $blocked = true;
                }
            });
        };
        app()->instance(CorrectionCommandPreflightService::class, $service);

        $acquired = null;
        $refusal = null;
        try {
            $acquired = (new EodRunRepository())->getOrCreateOwningRun($date, 'api', 'INGEST_BARS',
                (int) $prior->run_id, 'correction', $preflight);
        } catch (RuntimeException $error) {
            $refusal = $error->getMessage();
        }
        if ($rejected) {
            $this->assertNotNull($refusal, 'Revoked correction authorization was accepted after a stale transaction read.');
            $this->assertStringContainsString('COMMAND_CORRECTION_PREFLIGHT', $refusal);
            $this->assertSame('REJECTED', (string) DB::table('eod_dataset_corrections')
                ->where('correction_id', $correction->correction_id)->value('status'));
            $this->assertSame($before['runs'], DB::table('eod_runs')->count());
            $this->assertSame($before['events'], DB::table('eod_run_events')->count());
            $this->assertSame($before['snapshots'], DB::table('md_config_snapshots')->count());
            $this->assertEquals($before['fence'], DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first());
        } else {
            $this->assertTrue($blocked, 'Independent rejection was neither committed nor blocked by an authorization lock.');
            $this->assertNull($refusal);
            $this->assertNotNull($acquired);
            $this->assertSame('APPROVED', (string) DB::table('eod_dataset_corrections')
                ->where('correction_id', $correction->correction_id)->value('status'));
            $this->assertSame($before['runs'] + 1, DB::table('eod_runs')->count());
            $this->assertSame($before['events'] + 1, DB::table('eod_run_events')->count());
        }
        $this->assertEquals($before['pointer'], DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first());
    }

    public function test_manual_file_correction_midflight_post_allocation_mismatch_rolls_back_all_persisted_effects(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-manual-file-midflight-rollback-20260325');
        $tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tradeaxis_r0011_mariadb_f2_'.uniqid();
        @mkdir($tmpDir, 0777, true);
        config()->set('market_data.source.local_directory', $tmpDir);

        $inputContext = new ManualSourceInputContext();
        $this->app->instance(ManualSourceInputContext::class, $inputContext);

        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $world = R0025SyntheticV2World::build(['http_fetcher' => static function () use ($response) {
            return ['status' => 200, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $this->app->instance(\App\Application\MarketData\Services\MarketDataPipelineService::class, $world['pipeline']);

        $date = R0025SyntheticV2World::world()['trade_date'];
        $manualJsonPath = $tmpDir.DIRECTORY_SEPARATOR.'eod_bars_'.$date.'.json';
        $validPayload = json_encode([
            [
                'trade_date' => $date,
                'ticker_code' => 'SYNV2',
                'open' => 100,
                'high' => 110,
                'low' => 95,
                'close' => 106,
                'volume' => 1000,
                'adj_close' => 106,
                'source' => 'MANUAL_FILE',
                'published_at' => $date.'T17:10:00+07:00',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($manualJsonPath, $validPayload);
        $disclosedSha = hash('sha256', $validPayload);
        $inputContext->set($manualJsonPath);

        $prior = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
        $repo = app(EodCorrectionRepository::class);
        $correction = $repo->createRequest(
            $date,
            'DATASET_HASH_CREATED',
            'Mid-flight post-allocation manual_file mismatch rollback proof',
            'operator-proof',
            (int) $prior->publication_id,
            (int) $prior->run_id
        );
        $repo->approve($correction->correction_id, 'approver-proof');

        $before = [
            'runs' => DB::table('eod_runs')->orderBy('run_id')->get()->all(),
            'events' => DB::table('eod_run_events')->orderBy('event_id')->get()->all(),
            'snapshots' => DB::table('md_config_snapshots')->orderBy('config_snapshot_id')->get()->all(),
            'captures' => DB::table('md_run_input_captures')->orderBy('run_id')->get()->all(),
            'observations' => DB::table('md_source_observations')->orderBy('source_observation_id')->get()->all(),
            'fence' => DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first(),
            'correction' => DB::table('eod_dataset_corrections')->where('correction_id', $correction->correction_id)->first(),
            'pointer' => DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first(),
            'publications' => DB::table('eod_publications')->where('trade_date', $date)->orderBy('publication_id')->get()->all(),
        ];

        $midFlightMutated = false;
        $midFlightRunId = null;
        $midFlightCorrectionStatus = null;
        \App\Models\EodRunEvent::created(function ($event) use (
            &$midFlightMutated,
            &$midFlightRunId,
            &$midFlightCorrectionStatus,
            $correction,
            $manualJsonPath,
            $date
        ) {
            if (! $midFlightMutated && (string) $event->event_type === 'ACQUISITION_STARTED') {
                $midFlightMutated = true;
                $midFlightRunId = (int) $event->run_id;
                $midFlightCorrectionStatus = (string) DB::table('eod_dataset_corrections')
                    ->where('correction_id', $correction->correction_id)
                    ->value('status');
                file_put_contents($manualJsonPath, json_encode([
                    [
                        'trade_date' => $date,
                        'ticker_code' => 'SYNV2',
                        'open' => 100,
                        'high' => 110,
                        'low' => 95,
                        'close' => 999,
                        'volume' => 1000,
                        'adj_close' => 999,
                        'source' => 'MANUAL_FILE',
                        'published_at' => $date.'T17:10:00+07:00',
                    ],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        });

        try {
            $command = new \App\Console\Commands\MarketData\RunCorrectionCommand();
            $command->setLaravel($this->app);
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $exitCode = $tester->execute([
                'correction_id' => (int) $correction->correction_id,
                '--requested_date' => $date,
                '--source_mode' => 'manual_file',
            ]);
            $output = $tester->getDisplay();
        } finally {
            \App\Models\EodRunEvent::flushEventListeners();
            @unlink($manualJsonPath);
            @rmdir($tmpDir);
        }

        $this->assertTrue($midFlightMutated, 'Mid-flight hook after run allocation and ACQUISITION_STARTED must have fired.');
        $this->assertGreaterThan(0, (int) $midFlightRunId);
        $this->assertSame('EXECUTING', $midFlightCorrectionStatus);
        $this->assertSame(2, $exitCode, $output);
        $this->assertStringContainsString('operation_mode=PRE_APPLY_DISCLOSURE', $output);
        $this->assertStringContainsString(':sha256='.$disclosedSha, $output);
        $this->assertStringContainsString('reason_code=COMMAND_CORRECTION_PREFLIGHT_CHANGED', $output);

        $this->assertEquals($before['runs'], DB::table('eod_runs')->orderBy('run_id')->get()->all());
        $this->assertEquals($before['events'], DB::table('eod_run_events')->orderBy('event_id')->get()->all());
        $this->assertEquals($before['snapshots'], DB::table('md_config_snapshots')->orderBy('config_snapshot_id')->get()->all());
        $this->assertEquals($before['captures'], DB::table('md_run_input_captures')->orderBy('run_id')->get()->all());
        $this->assertEquals($before['observations'], DB::table('md_source_observations')->orderBy('source_observation_id')->get()->all());
        $this->assertEquals($before['fence'], DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first());
        $this->assertEquals($before['correction'], DB::table('eod_dataset_corrections')->where('correction_id', $correction->correction_id)->first());
        $this->assertEquals($before['pointer'], DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first());
        $this->assertEquals($before['publications'], DB::table('eod_publications')->where('trade_date', $date)->orderBy('publication_id')->get()->all());

        $this->onIndependentOwnerConnection(function () use ($before, $date, $correction) {
            $this->assertEquals($before['runs'], DB::table('eod_runs')->orderBy('run_id')->get()->all());
            $this->assertEquals($before['events'], DB::table('eod_run_events')->orderBy('event_id')->get()->all());
            $this->assertEquals($before['snapshots'], DB::table('md_config_snapshots')->orderBy('config_snapshot_id')->get()->all());
            $this->assertEquals($before['captures'], DB::table('md_run_input_captures')->orderBy('run_id')->get()->all());
            $this->assertEquals($before['observations'], DB::table('md_source_observations')->orderBy('source_observation_id')->get()->all());
            $this->assertEquals($before['fence'], DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first());
            $this->assertEquals($before['correction'], DB::table('eod_dataset_corrections')->where('correction_id', $correction->correction_id)->first());
            $this->assertEquals($before['pointer'], DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first());
            $this->assertEquals($before['publications'], DB::table('eod_publications')->where('trade_date', $date)->orderBy('publication_id')->get()->all());
        });
    }

    public function test_manual_file_correction_single_read_payload_prevents_post_capture_toctou_in_json_and_csv(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-manual-file-single-read-20260325');
        $tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tradeaxis_r0011_mariadb_f1_'.uniqid();
        @mkdir($tmpDir, 0777, true);
        config()->set('market_data.source.local_directory', $tmpDir);

        $inputContext = new ManualSourceInputContext();
        $this->app->instance(ManualSourceInputContext::class, $inputContext);

        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $world = R0025SyntheticV2World::build([
            'profile' => \App\Application\MarketData\Services\ArtifactSemanticHashService::LEGACY_PROFILE_V1,
            'http_fetcher' => static function () use ($response) {
                return ['status' => 200, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
            },
        ]);
        $this->app->instance(\App\Application\MarketData\Services\MarketDataPipelineService::class, $world['pipeline']);

        $date = R0025SyntheticV2World::world()['trade_date'];
        $manualJsonPath = $tmpDir.DIRECTORY_SEPARATOR.'eod_bars_'.$date.'.json';
        $manualCsvPath = $tmpDir.DIRECTORY_SEPARATOR.'eod_bars_'.$date.'.csv';

        try {
            // 1. JSON single-read verification: mutate file on disk right after md_source_observations insert (inside persistCapture, before loadJson).
            $jsonPayload = json_encode([
                [
                    'trade_date' => $date,
                    'ticker_code' => 'SYNV2',
                    'open' => 100,
                    'high' => 110,
                    'low' => 95,
                    'close' => 106,
                    'volume' => 1000,
                    'adj_close' => 106,
                    'source' => 'MANUAL_FILE',
                    'published_at' => $date.'T17:10:00+07:00',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($manualJsonPath, $jsonPayload);
            $expectedJsonSha = hash('sha256', $jsonPayload);
            $expectedJsonSize = strlen($jsonPayload);
            $inputContext->set($manualJsonPath);

            $prior = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
            $repo = app(EodCorrectionRepository::class);
            $jsonCorrection = $repo->createRequest(
                $date,
                'DATASET_HASH_CREATED',
                'Single-read JSON TOCTOU prevention proof',
                'operator-proof',
                (int) $prior->publication_id,
                (int) $prior->run_id
            );
            $repo->approve($jsonCorrection->correction_id, 'approver-proof');

            $activeHookMode = 'json';
            $jsonPostCaptureMutated = false;
            $csvPostCaptureMutated = false;
            DB::listen(function ($query) use (
                &$activeHookMode,
                &$jsonPostCaptureMutated,
                &$csvPostCaptureMutated,
                $manualJsonPath,
                $manualCsvPath,
                $date
            ) {
                $sql = strtolower((string) $query->sql);
                if (strpos($sql, 'insert into') !== false && strpos($sql, 'md_source_observations') !== false) {
                    if ($activeHookMode === 'json' && ! $jsonPostCaptureMutated) {
                        $jsonPostCaptureMutated = true;
                        file_put_contents($manualJsonPath, json_encode([
                            [
                                'trade_date' => $date,
                                'ticker_code' => 'SYNV2',
                                'open' => 100,
                                'high' => 110,
                                'low' => 95,
                                'close' => 109,
                                'volume' => 1000,
                                'adj_close' => 109,
                                'source' => 'MANUAL_FILE',
                                'published_at' => $date.'T17:10:00+07:00',
                            ],
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    } elseif ($activeHookMode === 'csv' && ! $csvPostCaptureMutated) {
                        $csvPostCaptureMutated = true;
                        file_put_contents(
                            $manualCsvPath,
                            "trade_date,ticker_code,open,high,low,close,volume,adj_close,source,published_at\n".
                            "{$date},SYNV2,100,110,95,109,1000,109,MANUAL_FILE,{$date}T17:10:00+07:00\n"
                        );
                    }
                }
            });

            $command = new \App\Console\Commands\MarketData\RunCorrectionCommand();
            $command->setLaravel($this->app);
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $jsonCode = $tester->execute([
                'correction_id' => (int) $jsonCorrection->correction_id,
                '--requested_date' => $date,
                '--source_mode' => 'manual_file',
            ]);
            $jsonOutput = $tester->getDisplay();

            $this->assertTrue($jsonPostCaptureMutated, 'Post-capture JSON disk mutation hook must have fired.');
            $this->assertSame(0, $jsonCode, $jsonOutput);
            $this->assertStringContainsString(':sha256='.$expectedJsonSha, $jsonOutput);

            $this->onIndependentOwnerConnection(function () use ($date, $expectedJsonSha, $expectedJsonSize) {
                $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
                $barClose = (float) DB::table('eod_bars')
                    ->where('trade_date', $date)
                    ->where('publication_id', $pointer->publication_id)
                    ->value('close');
                $runRow = DB::table('eod_runs')->where('run_id', $pointer->run_id)->first();
                $obsRow = DB::table('md_source_observations')->where('run_id', $pointer->run_id)->first();
                $this->assertSame(106.0, $barClose, 'JSON parsing re-read the post-capture mutated file from disk (109) instead of the verified payload (106).');
                $this->assertSame($expectedJsonSha, (string) $obsRow->payload_hash);
                $this->assertSame($expectedJsonSha, (string) $runRow->source_file_hash);
                $this->assertSame($expectedJsonSize, (int) $runRow->source_file_size_bytes);
            });

            // 2. CSV single-read verification: mutate CSV file on disk right after md_source_observations insert (inside persistCapture, before loadCsv).
            @unlink($manualJsonPath);
            $csvPayload = "trade_date,ticker_code,open,high,low,close,volume,adj_close,source,published_at\n".
                "{$date},SYNV2,100,110,95,108,1000,108,MANUAL_FILE,{$date}T17:10:00+07:00\n";
            file_put_contents($manualCsvPath, $csvPayload);
            $expectedCsvSha = hash('sha256', $csvPayload);
            $expectedCsvSize = strlen($csvPayload);
            $inputContext->set($manualCsvPath);

            $afterJsonPointer = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
            $csvCorrection = $repo->createRequest(
                $date,
                'DATASET_HASH_CREATED',
                'Single-read CSV TOCTOU prevention proof',
                'operator-proof',
                (int) $afterJsonPointer->publication_id,
                (int) $afterJsonPointer->run_id
            );
            $repo->approve($csvCorrection->correction_id, 'approver-proof');

            $activeHookMode = 'csv';
            $csvCode = $tester->execute([
                'correction_id' => (int) $csvCorrection->correction_id,
                '--requested_date' => $date,
                '--source_mode' => 'manual_file',
            ]);
            $csvOutput = $tester->getDisplay();

            $this->assertTrue($csvPostCaptureMutated, 'Post-capture CSV disk mutation hook must have fired.');
            $this->assertSame(0, $csvCode, $csvOutput);
            $this->assertStringContainsString(':sha256='.$expectedCsvSha, $csvOutput);

            $this->onIndependentOwnerConnection(function () use ($date, $expectedCsvSha, $expectedCsvSize) {
                $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
                $barClose = (float) DB::table('eod_bars')
                    ->where('trade_date', $date)
                    ->where('publication_id', $pointer->publication_id)
                    ->value('close');
                $runRow = DB::table('eod_runs')->where('run_id', $pointer->run_id)->first();
                $obsRow = DB::table('md_source_observations')->where('run_id', $pointer->run_id)->first();
                $this->assertSame(108.0, $barClose, 'CSV parsing re-read the post-capture mutated file from disk (109) instead of the verified payload (108).');
                $this->assertSame($expectedCsvSha, (string) $obsRow->payload_hash);
                $this->assertSame($expectedCsvSha, (string) $runRow->source_file_hash);
                $this->assertSame($expectedCsvSize, (int) $runRow->source_file_size_bytes);
            });
        } finally {
            @unlink($manualJsonPath);
            @unlink($manualCsvPath);
            @rmdir($tmpDir);
        }
    }
}

class E049CorrectionPreflightRaceService extends CorrectionCommandPreflightService
{
    public $afterFirstTransactionalInspect;

    public function inspect(int $correctionId, string $date, string $sourceMode, bool $lockBaseline = false): array
    {
        $result = parent::inspect($correctionId, $date, $sourceMode, $lockBaseline);
        if (DB::transactionLevel() > 0 && $this->afterFirstTransactionalInspect) {
            $callback = $this->afterFirstTransactionalInspect;
            $this->afterFirstTransactionalInspect = null;
            $callback();
        }
        return $result;
    }
}
