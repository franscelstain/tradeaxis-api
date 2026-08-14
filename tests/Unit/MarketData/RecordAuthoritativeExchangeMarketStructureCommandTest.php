<?php

use App\Application\MarketData\Ports\ExchangeMarketStructureEvidenceVerifier;
use App\Console\Commands\MarketData\RecordAuthoritativeExchangeMarketStructureCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class FakeStageSevenEvidenceVerifier implements ExchangeMarketStructureEvidenceVerifier
{
    public $calls = [];
    public $failure = null;

    public function verify(array $sourceDocument)
    {
        $this->calls[] = $sourceDocument;
        if ($this->failure !== null) {
            throw new \RuntimeException($this->failure);
        }

        $sample = 'verified-response-'.$sourceDocument['source_uid'];

        return [
            'document_sha256' => $sourceDocument['document_sha256'],
            'document_byte_length' => $sourceDocument['document_byte_length'],
            'content_type' => $sourceDocument['content_type'],
            'http_status' => 200,
            'schema_fingerprint' => hash('sha256', 'schema|'.$sourceDocument['content_type']),
            'payload_ref' => 'sha256:'.$sourceDocument['document_sha256'],
            'bounded_payload_body' => json_encode([
                'encoding' => 'base64',
                'sample_byte_length' => strlen($sample),
                'sample_sha256' => hash('sha256', $sample),
                'sample_base64' => base64_encode($sample),
            ], JSON_UNESCAPED_SLASHES),
        ];
    }
}

class RecordAuthoritativeExchangeMarketStructureCommandTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $manifestPaths = [];
    private $evidence;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow(Carbon::parse('2026-08-13 15:00:00', 'Asia/Jakarta'));
        $this->evidence = new FakeStageSevenEvidenceVerifier();
        $this->app->instance(ExchangeMarketStructureEvidenceVerifier::class, $this->evidence);
    }

    protected function tearDown(): void
    {
        foreach ($this->manifestPaths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_dry_run_validates_complete_scope_without_writing_or_fetching(): void
    {
        $tester = $this->executeCommand($this->manifestPath());

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $tester->getDisplay());
        $this->assertStringContainsString('inserted_revision_count=6', $tester->getDisplay());
        $this->assertStringContainsString('evidence_correction_revision_count=0', $tester->getDisplay());
        $this->assertStringContainsString('inserted_price_band_tier_count=12', $tester->getDisplay());
        $this->assertStringContainsString('inserted_tick_size_tier_count=5', $tester->getDisplay());
        $this->assertStringContainsString('series_application_count=0', $tester->getDisplay());
        $this->assertSame(0, DB::table('md_exchange_market_structure_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
        $this->assertCount(0, $this->evidence->calls);
    }

    public function test_apply_records_effective_dated_tiers_and_evidence_without_touching_output(): void
    {
        $runsBefore = DB::table('eod_runs')->count();
        $publicationsBefore = DB::table('eod_publications')->count();
        $barsBefore = DB::table('eod_bars')->count();

        $tester = $this->executeCommand($this->manifestPath(), true);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $tester->getDisplay());
        $this->assertStringContainsString('inserted_revision_count=6', $tester->getDisplay());
        $this->assertStringContainsString('source_observation_insert_count=10', $tester->getDisplay());
        $this->assertSame(6, DB::table('md_exchange_market_structure_revisions')->count());
        $this->assertSame(12, DB::table('md_exchange_price_band_tiers')->count());
        $this->assertSame(5, DB::table('md_exchange_tick_size_tiers')->count());
        $this->assertSame(10, DB::table('md_source_observations')->count());
        $this->assertCount(5, $this->evidence->calls);

        $sourcesByUrl = [];
        foreach ($this->manifest()['sources'] as $source) {
            $sourcesByUrl[$source['document_url']] = $source;
        }
        $captures = DB::table('md_source_observations')->where('outcome_state', 'CAPTURED')->get();
        $this->assertCount(5, $captures);
        foreach ($captures as $capture) {
            $source = $sourcesByUrl[$capture->sanitized_request_identity];
            $this->assertSame(200, (int) $capture->response_status);
            $this->assertSame($source['content_type'], $capture->content_type);
            $this->assertSame($source['document_sha256'], $capture->payload_hash);
            $this->assertSame('sha256:'.$source['document_sha256'], $capture->payload_ref);
            $this->assertSame($source['document_byte_length'], (int) $capture->payload_byte_length);
            $this->assertNotEmpty($capture->bounded_payload_body);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $capture->schema_fingerprint);
            $sample = json_decode($capture->bounded_payload_body, true);
            $sampleBytes = base64_decode($sample['sample_base64'], true);
            $this->assertSame('base64', $sample['encoding']);
            $this->assertNotFalse($sampleBytes);
            $this->assertSame($sample['sample_byte_length'], strlen($sampleBytes));
            $this->assertSame($sample['sample_sha256'], hash('sha256', $sampleBytes));

            $accepted = DB::table('md_source_observations')
                ->where('parent_observation_id', $capture->source_observation_id)->first();
            $this->assertNotNull($accepted);
            $this->assertSame('ACCEPTED', $accepted->outcome_state);
            $this->assertSame('PASSED', $accepted->validation_state);
            $this->assertSame(200, (int) $accepted->response_status);
            $this->assertSame($capture->content_type, $accepted->content_type);
            $this->assertSame($capture->payload_hash, $accepted->payload_hash);
            $this->assertSame($capture->payload_ref, $accepted->payload_ref);
            $this->assertSame($capture->payload_byte_length, $accepted->payload_byte_length);
            $this->assertSame($capture->schema_fingerprint, $accepted->schema_fingerprint);
            $this->assertNull($accepted->bounded_payload_body);
        }

        $bandRanges = DB::table('md_exchange_market_structure_revisions')
            ->where('rule_type', 'PRICE_BAND')->orderBy('effective_from')
            ->get()->map(function ($row) {
                return [$row->effective_from, $row->effective_to];
            })->all();
        $this->assertSame([
            ['2021-12-01', '2023-06-04'],
            ['2023-06-05', '2023-09-03'],
            ['2023-09-04', '2025-04-07'],
            ['2025-04-08', null],
        ], $bandRanges);

        $floor = DB::table('md_exchange_market_structure_revisions')->where('rule_type', 'MINIMUM_PRICE')->first();
        $this->assertEquals(50, $floor->minimum_price_idr);
        $this->assertSame('AUTHORITATIVE_VERIFIED', $floor->verification_state);
        $scope = json_decode($floor->coverage_scope_json, true);
        $this->assertSame(['MAIN', 'DEVELOPMENT', 'NEW_ECONOMY'], $scope['included_boards']);
        $this->assertSame(['ACCELERATION', 'SPECIAL_MONITORING'], $scope['excluded_boards']);
        $this->assertSame('FAIL_CLOSED', $scope['unresolved_board_policy']);

        $this->assertSame($runsBefore, DB::table('eod_runs')->count());
        $this->assertSame($publicationsBefore, DB::table('eod_publications')->count());
        $this->assertSame($barsBefore, DB::table('eod_bars')->count());
    }

    public function test_reapply_is_a_true_noop_and_does_not_refetch_or_duplicate_evidence(): void
    {
        $this->executeCommand($this->manifestPath(), true);
        $recordedAt = DB::table('md_exchange_market_structure_revisions')->orderBy('market_structure_revision_id')->value('recorded_at');
        Carbon::setTestNow(Carbon::parse('2026-08-14 09:00:00', 'Asia/Jakarta'));

        $second = $this->executeCommand($this->manifestPath(), true);

        $this->assertSame(0, $second->getStatusCode(), $second->getDisplay());
        $this->assertStringContainsString('inserted_revision_count=0', $second->getDisplay());
        $this->assertStringContainsString('unchanged_revision_count=6', $second->getDisplay());
        $this->assertStringContainsString('evidence_correction_revision_count=0', $second->getDisplay());
        $this->assertStringContainsString('source_observation_insert_count=0', $second->getDisplay());
        $this->assertSame(6, DB::table('md_exchange_market_structure_revisions')->count());
        $this->assertSame(10, DB::table('md_source_observations')->count());
        $this->assertSame($recordedAt, DB::table('md_exchange_market_structure_revisions')->orderBy('market_structure_revision_id')->value('recorded_at'));
        $this->assertCount(5, $this->evidence->calls, 'an immutable no-op must not depend on remote sites remaining online');
    }

    public function test_evidence_mismatch_blocks_all_inserts_atomically(): void
    {
        $this->evidence->failure = 'STAGE_7_DOCUMENT_HASH_MISMATCH: response hash differs from the manifest.';

        $tester = $this->executeCommand($this->manifestPath(), true);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('STAGE_7_DOCUMENT_HASH_MISMATCH', $tester->getDisplay());
        $this->assertSame(0, DB::table('md_exchange_market_structure_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
    }

    public function test_legacy_manifest_metadata_observations_are_superseded_by_append_only_evidence_revisions(): void
    {
        $this->executeCommand($this->manifestPath(), true);
        $revisionOneBefore = DB::table('md_exchange_market_structure_revisions')
            ->where('revision_number', 1)->orderBy('market_structure_revision_id')->get()->map(function ($row) {
                return (array) $row;
            })->all();
        $oldObservationMax = (int) DB::table('md_source_observations')->max('source_observation_id');

        DB::table('md_source_observations')->update([
            'response_status' => null,
            'payload_hash' => str_repeat('b', 64),
            'payload_ref' => 'sha256:'.str_repeat('b', 64),
            'payload_byte_length' => 500,
        ]);
        $legacyEvidenceBefore = DB::table('md_source_observations')
            ->orderBy('source_observation_id')->get()->map(function ($row) {
                return (array) $row;
            })->all();
        Carbon::setTestNow(Carbon::parse('2026-08-14 10:00:00', 'Asia/Jakarta'));

        $correction = $this->executeCommand($this->manifestPath(), true);

        $this->assertSame(0, $correction->getStatusCode(), $correction->getDisplay());
        $this->assertStringContainsString('inserted_revision_count=6', $correction->getDisplay());
        $this->assertStringContainsString('evidence_correction_revision_count=6', $correction->getDisplay());
        $this->assertStringContainsString('source_observation_insert_count=10', $correction->getDisplay());
        $this->assertSame(12, DB::table('md_exchange_market_structure_revisions')->count());
        $this->assertSame(24, DB::table('md_exchange_price_band_tiers')->count());
        $this->assertSame(10, DB::table('md_exchange_tick_size_tiers')->count());
        $this->assertSame(20, DB::table('md_source_observations')->count());
        $this->assertSame(6, DB::table('md_exchange_market_structure_revisions')
            ->where('revision_number', 2)->whereNotNull('supersedes_revision_id')->count());
        $this->assertSame($revisionOneBefore, DB::table('md_exchange_market_structure_revisions')
            ->where('revision_number', 1)->orderBy('market_structure_revision_id')->get()->map(function ($row) {
                return (array) $row;
            })->all());
        $this->assertSame($legacyEvidenceBefore, DB::table('md_source_observations')
            ->where('source_observation_id', '<=', $oldObservationMax)
            ->orderBy('source_observation_id')->get()->map(function ($row) {
                return (array) $row;
            })->all());
        $this->assertSame(10, DB::table('md_source_observations')
            ->where('source_observation_id', '>', $oldObservationMax)
            ->whereNotNull('supersedes_observation_id')->count());
        $this->assertSame(5, DB::table('md_source_observations')
            ->where('source_observation_id', '>', $oldObservationMax)
            ->where('outcome_state', 'CAPTURED')
            ->where('response_status', 200)
            ->whereNotNull('bounded_payload_body')
            ->count());
        $this->assertSame(5, DB::table('md_source_observations')
            ->where('source_observation_id', '>', $oldObservationMax)
            ->where('outcome_state', 'ACCEPTED')
            ->where('validation_state', 'PASSED')
            ->whereNull('bounded_payload_body')
            ->count());

        $third = $this->executeCommand($this->manifestPath(), true);
        $this->assertStringContainsString('inserted_revision_count=0', $third->getDisplay());
        $this->assertStringContainsString('unchanged_revision_count=6', $third->getDisplay());
        $this->assertStringContainsString('evidence_correction_revision_count=0', $third->getDisplay());
        $this->assertSame(20, DB::table('md_source_observations')->count());
        $this->assertCount(10, $this->evidence->calls);
    }

    public function test_incomplete_scope_board_drift_and_stage_eight_field_are_rejected(): void
    {
        $cases = [];
        $incomplete = $this->manifest();
        array_pop($incomplete['revisions']);
        $incomplete['revision_count'] = count($incomplete['revisions']);
        $cases[] = [$incomplete, 'STAGE_7_COVERAGE_INCOMPLETE'];

        $boardDrift = $this->manifest();
        $boardDrift['revisions'][0]['coverage_scope']['included_boards'][] = 'ACCELERATION';
        $cases[] = [$boardDrift, 'STAGE_7_COVERAGE_SCOPE_CONFLICT'];

        $stageEight = $this->manifest();
        $stageEight['revisions'][0]['factor_application'] = true;
        $cases[] = [$stageEight, 'STAGE_7_SCHEMA_DRIFT'];

        foreach ($cases as $case) {
            $tester = $this->executeCommand($this->writeManifest($case[0]), true);
            $this->assertSame(1, $tester->getStatusCode());
            $this->assertStringContainsString($case[1], $tester->getDisplay());
        }
        $this->assertSame(0, DB::table('md_exchange_market_structure_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
    }

    public function test_conflicting_reimport_is_blocked_instead_of_overwriting(): void
    {
        $this->executeCommand($this->manifestPath(), true);
        $before = (array) DB::table('md_exchange_market_structure_revisions')
            ->where('effective_from', '2025-04-08')->first();

        $changed = $this->manifest();
        $changedHash = str_repeat('a', 64);
        $changed['sources'][3]['document_sha256'] = $changedHash;
        $changedUid = hash('sha256', implode('|', [
            'market-structure-source', 'IDX', 'Kep-00003/BEI/04-2025', '2025-04-08', $changedHash,
        ]));
        $changed['sources'][3]['source_uid'] = $changedUid;
        $changed['revisions'][3]['source_uid'] = $changedUid;

        $tester = $this->executeCommand($this->writeManifest($changed), true);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('STAGE_7_REVISION_CONFLICT', $tester->getDisplay());
        $this->assertSame($before, (array) DB::table('md_exchange_market_structure_revisions')
            ->where('effective_from', '2025-04-08')->first());
        $this->assertSame(10, DB::table('md_source_observations')->count());
    }

    public function test_stage_seven_writer_has_no_stage_eight_application_surface(): void
    {
        $service = file_get_contents(base_path('app/Application/MarketData/Services/AuthoritativeExchangeMarketStructureService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/MarketData/RecordAuthoritativeExchangeMarketStructureCommand.php'));
        $source = $service."\n".$command;

        foreach ([
            'eod_runs', 'eod_publications', 'eod_bars', 'eod_bars_history', 'eod_indicators',
            'eod_eligibility', 'md_adjustment_factor_sets', 'md_adjustment_factors',
            'md_publication_lineage_bindings', 'market_data_corporate_actions',
        ] as $forbiddenTable) {
            $this->assertStringNotContainsString("DB::table('".$forbiddenTable."')", $source);
        }
        $this->assertStringContainsString("DB::table('md_exchange_market_structure_revisions')", $service);
        $this->assertStringContainsString("DB::table('md_exchange_price_band_tiers')", $service);
        $this->assertStringContainsString("DB::table('md_exchange_tick_size_tiers')", $service);
        $this->assertStringContainsString('SourceObservationRepository', $service);
        $this->assertStringContainsString('ExchangeMarketStructureEvidenceVerifier', $service);
        $this->assertLessThan(
            strpos($service, 'return DB::transaction'),
            strpos($service, '$this->evidenceVerifier->verify'),
            'remote evidence verification must finish before the transaction starts'
        );
    }

    private function executeCommand($manifest, $apply = false): CommandTester
    {
        $command = new RecordAuthoritativeExchangeMarketStructureCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $input = ['manifest' => $manifest];
        if ($apply) {
            $input['--apply'] = true;
        }
        $tester->execute($input);

        return $tester;
    }

    private function manifestPath(): string
    {
        return base_path('docs/market_data/evidence/market_structure/stage_7_idx_regular_market_structure_v1.json');
    }

    private function manifest(): array
    {
        return json_decode(file_get_contents($this->manifestPath()), true);
    }

    private function writeManifest(array $manifest): string
    {
        $path = tempnam(sys_get_temp_dir(), 'stage-7-market-structure-');
        file_put_contents($path, json_encode($manifest, JSON_UNESCAPED_SLASHES));
        $this->manifestPaths[] = $path;

        return $path;
    }
}
