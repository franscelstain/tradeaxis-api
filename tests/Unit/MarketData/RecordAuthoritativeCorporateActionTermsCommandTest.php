<?php

use App\Console\Commands\MarketData\RecordAuthoritativeCorporateActionTermsCommand;
use App\Application\MarketData\Ports\AuthoritativeDocumentEvidenceVerifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

class FakeStageSixAuthoritativeDocumentEvidenceVerifier implements AuthoritativeDocumentEvidenceVerifier
{
    public $calls = [];
    public $failure = null;

    public function verify(array $sourceDocument)
    {
        $this->calls[] = $sourceDocument;

        if ($this->failure !== null) {
            throw new RuntimeException($this->failure);
        }

        return [
            'document_sha256' => $sourceDocument['document_sha256'],
            'document_byte_length' => $sourceDocument['document_byte_length'],
            'content_type' => $sourceDocument['content_type'],
            'http_status' => 200,
        ];
    }
}

class RecordAuthoritativeCorporateActionTermsCommandTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $manifestPaths = [];
    private $documentEvidence;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow(Carbon::parse('2026-08-13 12:00:00', 'Asia/Jakarta'));
        $this->documentEvidence = new FakeStageSixAuthoritativeDocumentEvidenceVerifier();
        $this->app->instance(AuthoritativeDocumentEvidenceVerifier::class, $this->documentEvidence);

        DB::table('tickers')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'MLPT',
            'company_name' => 'Multipolar Technology',
            'listed_date' => '2013-07-08',
            'exchange_code' => 'IDX',
            'is_active' => 1,
        ]);
        DB::table('md_issuers')->insert([
            'issuer_id' => 1,
            'issuer_uid' => hash('sha256', 'issuer|mlpt'),
            'legal_name' => 'PT Multipolar Technology Tbk',
            'source_ref' => 'test-identity',
            'recorded_at' => '2025-12-27 12:54:00',
            'created_at' => '2025-12-27 12:54:00',
        ]);
        DB::table('md_instruments')->insert([
            'instrument_id' => 1,
            'instrument_uid' => hash('sha256', 'instrument|mlpt'),
            'issuer_id' => 1,
            'instrument_type' => 'EQUITY',
            'currency_code' => 'IDR',
            'source_ref' => 'test-identity',
            'recorded_at' => '2025-12-27 12:54:00',
            'created_at' => '2025-12-27 12:54:00',
        ]);
        DB::table('md_listings')->insert([
            'listing_id' => 1,
            'listing_uid' => hash('sha256', 'listing|idx|mlpt'),
            'legacy_ticker_id' => 1,
            'instrument_id' => 1,
            'exchange_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'board_code' => null,
            'listed_date' => '2013-07-08',
            'delisted_date' => null,
            'source_ref' => 'test-identity',
            'listing_state' => 'LISTED',
            'recorded_at' => '2025-12-27 12:54:00',
            'created_at' => '2025-12-27 12:54:00',
        ]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => 1,
            'symbol' => 'MLPT',
            'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX',
            'effective_from' => '2013-07-08 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2025-12-27 12:54:00',
            'retracted_at' => null,
            'source_observation_id' => null,
            'source_ref' => 'test-identity',
            'change_reason' => 'TEST_FIXTURE',
        ]);

        // These rows model the legacy factor/application state that Stage 6 is forbidden to alter.
        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'MLPT',
            'action_date' => '2026-07-21',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'manual_corporate_action_csv',
            'source_ref' => 'legacy-third-party-reference',
            'recorded_at' => '2026-08-11 15:08:07',
            'price_adjustment_factor' => 0.04,
            'volume_adjustment_factor' => 25,
            'ex_date' => '2026-07-15',
            'ratio_from' => 1,
            'ratio_to' => 25,
            'adjustment_source' => 'EXCHANGE_ANNOUNCEMENT',
        ]);
        DB::table('md_adjustment_factor_sets')->insert([
            'factor_set_id' => 1,
            'factor_set_uid' => hash('sha256', 'existing-factor-set'),
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'factor_formula_version' => 'test-v1',
            'config_snapshot_id' => 1,
            'state' => 'DRAFT',
            'content_hash' => hash('sha256', 'existing-factor-content'),
            'recorded_at' => '2026-08-11 15:08:07',
            'created_at' => '2026-08-11 15:08:07',
        ]);
        DB::table('md_adjustment_factors')->insert([
            'adjustment_factor_id' => 1,
            'factor_set_id' => 1,
            'listing_id' => 1,
            'effective_from' => '2026-07-15',
            'effective_to' => null,
            'price_factor' => 0.04,
            'volume_factor' => 25,
            'corporate_action_revision_id' => 999,
            'created_at' => '2026-08-11 15:08:07',
        ]);
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

    public function test_dry_run_validates_the_declared_scope_without_writing(): void
    {
        $tester = $this->executeCommand($this->manifest());

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=DRY_RUN', $tester->getDisplay());
        $this->assertStringContainsString('inserted_revision_count=1', $tester->getDisplay());
        $this->assertStringContainsString('series_application_count=0', $tester->getDisplay());
        $this->assertSame(0, DB::table('md_corporate_action_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
        $this->assertCount(0, $this->documentEvidence->calls, 'dry-run must not make remote evidence a hidden write prerequisite');
    }

    public function test_apply_appends_terms_and_immutable_evidence_without_touching_application_state(): void
    {
        $legacyBefore = (array) DB::table('market_data_corporate_actions')->first();
        $factorSetBefore = (array) DB::table('md_adjustment_factor_sets')->first();
        $factorBefore = (array) DB::table('md_adjustment_factors')->first();
        $publicationCountBefore = DB::table('eod_publications')->count();
        $barCountBefore = DB::table('eod_bars')->count();

        $tester = $this->executeCommand($this->manifest(), true);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('status=APPLIED', $tester->getDisplay());
        $this->assertStringContainsString('inserted_revision_count=1', $tester->getDisplay());
        $this->assertStringContainsString('source_observation_insert_count=2', $tester->getDisplay());
        $this->assertStringContainsString('series_application_count=0', $tester->getDisplay());

        $revision = DB::table('md_corporate_action_revisions')->first();
        $this->assertSame(1, (int) $revision->listing_id);
        $this->assertSame('STOCK_SPLIT', $revision->action_type_code);
        $this->assertSame('EFFECTIVE', $revision->lifecycle_state);
        $this->assertSame('AUTHORITATIVE_VERIFIED', $revision->verification_state);
        $this->assertSame('2026-07-20', $revision->cum_date);
        $this->assertSame('2026-07-21', $revision->ex_date);
        $this->assertSame('2026-07-22', $revision->record_date);
        $this->assertSame('2026-07-23', $revision->payment_date);
        $this->assertSame('2026-07-21 00:00:00', $revision->effective_at);
        $this->assertSame('2026-08-13 12:00:00', $revision->recorded_at);
        $this->assertNull($revision->supersedes_revision_id);

        $terms = json_decode($revision->terms_json, true);
        $this->assertNull($terms['announcement_at'], 'unknown announcement time must remain NULL');
        $this->assertSame(['from' => 1, 'to' => 25], $terms['ratio']);
        $this->assertSame(['currency_code' => 'IDR', 'new' => 4, 'old' => 100], $terms['nominal_value']);
        $this->assertSame('3d98ae958b06fa191ed21e5e2bc89ad4695631aaaad345e2c814d60252c25b11', $terms['source_document']['document_sha256']);
        $this->assertStringNotContainsString('price_factor', $revision->terms_json);
        $this->assertStringNotContainsString('volume_factor', $revision->terms_json);

        $accepted = DB::table('md_source_observations')->where('source_observation_id', $revision->source_observation_id)->first();
        $capture = DB::table('md_source_observations')->where('source_observation_id', $accepted->parent_observation_id)->first();
        $this->assertSame('ACCEPTED', $accepted->outcome_state);
        $this->assertSame('PASSED', $accepted->validation_state);
        $this->assertSame('CAPTURED', $capture->outcome_state);
        $this->assertSame($capture->payload_hash, $accepted->payload_hash);
        $this->assertStringContainsString($terms['source_document']['document_sha256'], $capture->bounded_payload_body);
        $this->assertSame('https://web.ksei.co.id/Announcement/Files/MLPT_MCONV_20260722_ID.pdf', $accepted->sanitized_request_identity);

        $this->assertSame($legacyBefore, (array) DB::table('market_data_corporate_actions')->first());
        $this->assertSame($factorSetBefore, (array) DB::table('md_adjustment_factor_sets')->first());
        $this->assertSame($factorBefore, (array) DB::table('md_adjustment_factors')->first());
        $this->assertSame($publicationCountBefore, DB::table('eod_publications')->count());
        $this->assertSame($barCountBefore, DB::table('eod_bars')->count());
        $this->assertCount(1, $this->documentEvidence->calls);
        $this->assertSame('3d98ae958b06fa191ed21e5e2bc89ad4695631aaaad345e2c814d60252c25b11', $this->documentEvidence->calls[0]['document_sha256']);
    }

    public function test_reapply_is_a_true_noop_and_does_not_duplicate_source_observations(): void
    {
        $manifest = $this->manifest();
        $first = $this->executeCommand($manifest, true);
        $revision = DB::table('md_corporate_action_revisions')->first();

        Carbon::setTestNow(Carbon::parse('2026-08-14 09:00:00', 'Asia/Jakarta'));
        $second = $this->executeCommand($manifest, true);

        $this->assertSame(0, $first->getStatusCode());
        $this->assertSame(0, $second->getStatusCode());
        $this->assertStringContainsString('inserted_revision_count=0', $second->getDisplay());
        $this->assertStringContainsString('unchanged_revision_count=1', $second->getDisplay());
        $this->assertStringContainsString('source_observation_insert_count=0', $second->getDisplay());
        $this->assertSame(1, DB::table('md_corporate_action_revisions')->count());
        $this->assertSame(2, DB::table('md_source_observations')->count());
        $this->assertSame($revision->recorded_at, DB::table('md_corporate_action_revisions')->value('recorded_at'));
        $this->assertCount(1, $this->documentEvidence->calls, 'an immutable no-op must not depend on the authority site remaining online');
    }

    public function test_remote_document_byte_mismatch_blocks_the_insert_atomically(): void
    {
        $this->documentEvidence->failure = 'STAGE_6_DOCUMENT_HASH_MISMATCH: KSEI response hash differs from the manifest.';

        $tester = $this->executeCommand($this->manifest(), true);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('STAGE_6_DOCUMENT_HASH_MISMATCH', $tester->getDisplay());
        $this->assertCount(1, $this->documentEvidence->calls);
        $this->assertSame(0, DB::table('md_corporate_action_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
    }

    public function test_conflicting_reimport_is_blocked_instead_of_overwriting_the_revision(): void
    {
        $manifest = $this->manifest();
        $this->executeCommand($manifest, true);
        $before = (array) DB::table('md_corporate_action_revisions')->first();

        $changed = $this->manifestData();
        $changed['events'][0]['source']['document_sha256'] = str_repeat('a', 64);
        $tester = $this->executeCommand($this->writeManifest($changed), true);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('STAGE_6_REVISION_CONFLICT', $tester->getDisplay());
        $this->assertSame($before, (array) DB::table('md_corporate_action_revisions')->first());
        $this->assertSame(2, DB::table('md_source_observations')->count());
    }

    public function test_non_authoritative_provider_state_or_document_url_is_rejected_atomically(): void
    {
        $cases = [];
        $thirdParty = $this->manifestData();
        $thirdParty['events'][0]['source']['document_url'] = 'https://www.new.sahamidx.com/stock-split';
        $cases[] = [$thirdParty, 'STAGE_6_SOURCE_NOT_AUTHORITATIVE'];

        $providerReported = $this->manifestData();
        $providerReported['events'][0]['verification_state'] = 'PROVIDER_REPORTED';
        $cases[] = [$providerReported, 'STAGE_6_VERIFICATION_INVALID'];

        foreach ($cases as $case) {
            $tester = $this->executeCommand($this->writeManifest($case[0]), true);
            $this->assertSame(1, $tester->getStatusCode());
            $this->assertStringContainsString($case[1], $tester->getDisplay());
        }

        $this->assertSame(0, DB::table('md_corporate_action_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
    }

    public function test_inconsistent_terms_unknown_fabrication_and_manifest_schema_drift_are_rejected(): void
    {
        $cases = [];
        $ratioConflict = $this->manifestData();
        $ratioConflict['events'][0]['new_nominal_value'] = 5;
        $cases[] = [$ratioConflict, 'STAGE_6_TERMS_CONFLICT'];

        $fabricatedUnknown = $this->manifestData();
        $fabricatedUnknown['events'][0]['announcement_at'] = '2026-07-15';
        $cases[] = [$fabricatedUnknown, 'STAGE_6_ANNOUNCEMENT_INVALID'];

        $schemaDrift = $this->manifestData();
        $schemaDrift['events'][0]['price_factor'] = 0.04;
        $cases[] = [$schemaDrift, 'STAGE_6_SCHEMA_DRIFT'];

        foreach ($cases as $case) {
            $tester = $this->executeCommand($this->writeManifest($case[0]), true);
            $this->assertSame(1, $tester->getStatusCode());
            $this->assertStringContainsString($case[1], $tester->getDisplay());
        }

        $this->assertSame(0, DB::table('md_corporate_action_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
    }

    public function test_stage_six_writer_has_no_stage_eight_table_surface(): void
    {
        $service = file_get_contents(base_path('app/Application/MarketData/Services/AuthoritativeCorporateActionTermsService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/MarketData/RecordAuthoritativeCorporateActionTermsCommand.php'));
        $source = $service."\n".$command;

        foreach ([
            'eod_runs', 'eod_publications', 'eod_bars', 'eod_bars_history', 'eod_indicators',
            'eod_eligibility', 'md_adjustment_factor_sets', 'md_adjustment_factors',
            'md_publication_lineage_bindings', 'market_data_corporate_actions',
        ] as $forbiddenTable) {
            $this->assertStringNotContainsString("DB::table('".$forbiddenTable."')", $source);
        }

        $this->assertStringContainsString("DB::table('md_corporate_action_revisions')", $service);
        $this->assertStringContainsString('SourceObservationRepository', $service);
        $this->assertStringContainsString('AuthoritativeDocumentEvidenceVerifier', $service);
        $this->assertLessThan(
            strpos($service, 'return DB::transaction'),
            strpos($service, '$this->documentEvidence->verify'),
            'remote authority verification must finish before the database transaction/locks begin'
        );

        $verifier = file_get_contents(base_path('app/Infrastructure/MarketData/Source/KseiAuthoritativeDocumentEvidenceVerifier.php'));
        foreach (['CURLOPT_FOLLOWLOCATION => false', 'CURLOPT_SSL_VERIFYPEER => true', 'CURLINFO_HTTP_CODE', 'application/pdf', "hash('sha256', \$body)", 'STAGE_6_DOCUMENT_HASH_MISMATCH', 'STAGE_6_DOCUMENT_LENGTH_MISMATCH'] as $guard) {
            $this->assertStringContainsString($guard, $verifier);
        }
    }

    private function executeCommand($manifest, $apply = false): CommandTester
    {
        $command = new RecordAuthoritativeCorporateActionTermsCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $input = ['manifest' => $manifest];
        if ($apply) {
            $input['--apply'] = true;
        }
        $tester->execute($input);

        return $tester;
    }

    private function manifest(): string
    {
        return $this->writeManifest($this->manifestData());
    }

    private function manifestData(): array
    {
        return [
            'schema_version' => 'market-data-authoritative-corporate-action-terms/v1',
            'scope_id' => 'stage-6-test-scope-v1',
            'scope_statement' => 'One KSEI stock split fixture; terms recording only.',
            'record_only' => true,
            'event_count' => 1,
            'events' => [[
                'event_uid' => hash('sha256', 'corporate-action|KSEI|ID1000128408|KSEI-18691/JKU/0726'),
                'revision_number' => 1,
                'supersedes_revision_number' => null,
                'ticker_code' => 'MLPT',
                'isin' => 'ID1000128408',
                'exchange_code' => 'IDX',
                'market_segment' => 'REGULAR',
                'action_type_code' => 'STOCK_SPLIT',
                'lifecycle_state' => 'EFFECTIVE',
                'verification_state' => 'AUTHORITATIVE_VERIFIED',
                'announcement_at' => null,
                'cum_date' => '2026-07-20',
                'ex_date' => '2026-07-21',
                'record_date' => '2026-07-22',
                'distribution_date' => '2026-07-23',
                'effective_date' => '2026-07-21',
                'ratio_from' => 1,
                'ratio_to' => 25,
                'old_nominal_value' => 100,
                'new_nominal_value' => 4,
                'currency_code' => 'IDR',
                'source' => [
                    'authority_name' => 'KSEI',
                    'authority_class' => 'CSD',
                    'document_number' => 'KSEI-18691/JKU/0726',
                    'document_date' => '2026-07-15',
                    'document_url' => 'https://web.ksei.co.id/Announcement/Files/MLPT_MCONV_20260722_ID.pdf',
                    'document_sha256' => '3d98ae958b06fa191ed21e5e2bc89ad4695631aaaad345e2c814d60252c25b11',
                    'document_byte_length' => 38882,
                    'content_type' => 'application/pdf',
                ],
            ]],
        ];
    }

    private function writeManifest(array $manifest): string
    {
        $path = tempnam(sys_get_temp_dir(), 'stage-6-terms-');
        file_put_contents($path, json_encode($manifest, JSON_UNESCAPED_SLASHES));
        $this->manifestPaths[] = $path;

        return $path;
    }
}
