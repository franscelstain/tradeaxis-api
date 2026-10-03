<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\FoundationService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * Controls of the CANDIDATE independent R0025 golden fixture (package `tests/fixtures/replay/r0025-synthetic-v2-candidate-v1`).
 *
 * The package is `CANDIDATE_AWAITING_INDEPENDENT_REVIEW`: nothing here approves it, and none of these tests is the governed R0025
 * proof. They show that the synthetic V2 world runs through the real production path, that its retained identities and semantic
 * hashes do not move with local allocation, that the reference oracle reproduces the package and is sensitive to its frozen inputs,
 * that the package is not self-generated, that target-bound operational identities cannot become wildcards, and that the verifier
 * keeps refusing the package as proof until an approval is bound to its fingerprint.
 */
class R0025SyntheticV2CandidateFixtureTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private $copies = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        foreach ($this->copies as $dir) {
            $this->removeTree($dir);
        }
        Carbon::setTestNow();
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    // ------------------------------------------------------------------------------------------------------ helpers

    private function package(): string
    {
        return R0025SyntheticV2World::packagePath();
    }

    private function packageJson(string $relative): array
    {
        return json_decode((string) file_get_contents($this->package().'/'.$relative), true);
    }

    private function verifier(): ReplayVerificationService
    {
        return new ReplayVerificationService(new EodEvidenceRepository(), new EodPublicationRepository(), new ReplayResultRepository());
    }

    /** A byte copy of the package outside the repository, so a control can damage it without touching the candidate. */
    private function copyPackage(): string
    {
        $dir = storage_path('framework/testing/r0025-candidate-copy-'.bin2hex(random_bytes(4)));
        $this->copies[] = $dir;
        $this->copyTree($this->package(), $dir);

        return $dir;
    }

    private function copyTree(string $from, string $to): void
    {
        @mkdir($to, 0777, true);
        foreach (scandir($from) as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            is_dir($from.'/'.$name) ? $this->copyTree($from.'/'.$name, $to.'/'.$name) : copy($from.'/'.$name, $to.'/'.$name);
        }
    }

    private function removeTree(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $name) {
            if ($name !== '.' && $name !== '..') {
                is_dir($dir.'/'.$name) ? $this->removeTree($dir.'/'.$name) : @unlink($dir.'/'.$name);
            }
        }
        @rmdir($dir);
    }

    /** Runs the standalone oracle of a package directory (a copy or the package) and returns its output document. */
    private function runOracle(string $dir): array
    {
        $output = [];
        $code = 0;
        exec('"'.PHP_BINARY.'" '.escapeshellarg($dir.'/derivation/reference_oracle.php').' 2>&1', $output, $code);
        $this->assertSame(0, $code, 'the reference oracle failed: '.implode("\n", $output));

        return json_decode((string) file_get_contents($dir.'/derivation/oracle_output.json'), true);
    }

    /** @return array<string,mixed> the semantic identity of one built world */
    private function semanticIdentity(array $world): array
    {
        $db = DB::connection();
        $publication = $db->table('eod_publications')->where('publication_id', $world['publication_id'])->first();
        $lineage = $db->table('md_publication_lineage_bindings')->where('publication_id', $world['publication_id'])->first();
        $result = $this->verifier()->verifyRunAgainstFixture($world['run_id'], $this->package(), null, $world['publication_id']);
        $semantic = [];
        foreach (\App\Application\MarketData\Services\SemanticNestedIdentityService::LINEAGE_COLUMNS as $column) {
            $semantic[$column] = $lineage->{$column};
        }

        return [
            'artifacts' => ['bars' => $publication->bars_batch_hash, 'indicators' => $publication->indicators_batch_hash, 'eligibility' => $publication->eligibility_batch_hash],
            'nested' => $semantic,
            'bound' => array_intersect_key($result['actual_context']['actual_bound_input_context'], array_flip(['source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash', 'calendar_status_hash', 'config_snapshot_hash', 'read_model_version', 'serialization_version'])),
            'factor_set_hash' => $result['actual_context']['actual_publication_context']['factor_set_hash'],
            'operational' => ['run_id' => $world['run_id'], 'publication_id' => $world['publication_id'], 'listing_id' => (int) $db->table('eod_bars')->where('run_id', $world['run_id'])->value('listing_id'),
                'config_snapshot_id' => (int) $publication->config_snapshot_id, 'factor_set_id' => (int) $publication->factor_set_id, 'ticker_id' => $world['ticker_id']],
            'mismatch_count' => $result['mismatch_count'],
        ];
    }

    private function freshTransaction(): void
    {
        $db = DB::connection();
        $db->rollBack();
        $db->beginTransaction();
        Carbon::setTestNow();
    }

    // ------------------------------------------------------------------------------- 1. synthetic retained identity

    public function test_the_synthetic_retained_identity_resolves_only_through_the_foundation_restore_path(): void
    {
        $world = R0025SyntheticV2World::world();
        $this->assertSame('SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT', $world['label']);
        $registryPath = $this->package().'/inputs/foundation_registry.json';
        $service = new FoundationService(new FoundationRepository(DB::connection()));
        $service->restore($registryPath, hash_file('sha256', $registryPath));

        $listing = $world['listing'];
        $resolved = $service->resolve($listing['provider_namespace'], $listing['provider_symbol'], '2026-03-25 03:30:00', '2026-03-25 03:30:00');
        $this->assertSame('RESOLVED', $resolved->state);
        $this->assertSame($world['retained_roots']['issuer'], $resolved->issuerId);
        $this->assertSame($world['retained_roots']['instrument'], $resolved->instrumentId);
        $this->assertSame($world['retained_roots']['listing'], $resolved->listingId);
        $this->assertSame('HELD', $service->resolve($listing['provider_namespace'], 'NOT.SYNTHETIC', '2026-03-25 03:30:00', '2026-03-25 03:30:00')->state, 'a symbol the registry does not retain must be held');
        $this->assertSame('HELD', $service->resolve($listing['provider_namespace'], $listing['provider_symbol'], '2019-12-31 00:00:00', '2026-03-25 03:30:00')->state, 'before the validity interval the listing must be held');
        $this->assertSame('HELD', $service->resolve($listing['provider_namespace'], $listing['provider_symbol'], '2026-03-25 03:30:00', '2019-12-31 00:00:00')->state, 'before the knowledge time the listing must be held');

        // The restore path refuses a registry whose bytes do not match the approved fingerprint. What the call throws is captured by a
        // helper that wraps only the production call; every assertion is made outside it, so PHPUnit's own failure can never be caught
        // here and the control cannot pass because a failure was swallowed.
        $thrown = $this->thrownBy(function () use ($registryPath) {
            (new FoundationService(new FoundationRepository(DB::connection())))->restore($registryPath, str_repeat('0', 64));
        });
        $this->assertNotNull($thrown, 'a wrong fingerprint was accepted');
        $this->assertSame(\DomainException::class, get_class($thrown), 'the refusal must be the domain refusal, not an unrelated failure');
        $this->assertSame('REGISTRY_FINGERPRINT_MISMATCH', $thrown->getMessage());

        // and a fingerprint that is not a SHA-256 is refused for what it is, not as a mismatch
        $malformed = $this->thrownBy(function () use ($registryPath) {
            (new FoundationService(new FoundationRepository(DB::connection())))->restore($registryPath, 'not-a-sha256');
        });
        $this->assertNotNull($malformed, 'a malformed fingerprint was accepted');
        $this->assertSame(\DomainException::class, get_class($malformed));
        $this->assertSame('REGISTRY_APPROVED_FINGERPRINT_REQUIRED', $malformed->getMessage());
    }

    /** Runs the production call and returns what it threw, or null when it threw nothing. Contains no assertion. */
    private function thrownBy(callable $call): ?\Throwable
    {
        try {
            $call();
        } catch (\Throwable $e) {
            return $e;
        }

        return null;
    }

    // ------------------------------------------------------------------------------- 2. real production path

    public function test_the_world_runs_the_real_v2_pipeline_and_seals_a_publication(): void
    {
        $w = R0025SyntheticV2World::build();
        $db = DB::connection();
        $run = $db->table('eod_runs')->where('run_id', $w['run_id'])->first();
        $publication = $w['publication'];
        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $run->artifact_hash_profile);
        $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $publication->artifact_hash_profile);
        $lineage = $db->table('md_publication_lineage_bindings')->where('publication_id', $w['publication_id'])->first();
        $this->assertSame('market-data-semantic-nested/v2', $lineage->semantic_nested_identity_version);
        $this->assertSame(1, $db->table('eod_bars')->where('run_id', $w['run_id'])->count());
        // the source is the provider path, with the provider and symbol the retained registry names
        $row = $db->table('md_source_observation_rows')->whereIn('source_observation_id', $db->table('md_source_observations')->where('run_id', $w['run_id'])->pluck('source_observation_id')->all())->first();
        $this->assertSame('yahoo_finance', $row->provider);
        $this->assertSame('SYNV2.JK', $row->provider_symbol);
    }

    // ------------------------------------------------------------------------------- 3. candidate versus actual (diagnostic)

    public function test_candidate_matches_the_actual_publication_but_is_not_admitted_as_proof(): void
    {
        $w = R0025SyntheticV2World::build();
        $before = $this->fileHashes();
        $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);

        // DIAGNOSTIC: the independently authored expectation equals the actual publication field for field ...
        $this->assertSame([], $result['mismatches'], 'candidate versus actual: '.json_encode($result['mismatches']));
        $this->assertSame(0, $result['mismatch_count']);
        // ... and it is still not proof: no review and no owner approval is bound to the package fingerprint.
        $this->assertSame('NOT_ADMISSIBLE', $result['admission_state']);
        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertSame('NOT_ADMISSIBLE', $result['comparison_result']);
        $this->assertStringStartsWith('REPLAY_INDEPENDENT_REVIEW_REQUIRED', $result['mismatch_summary']);
        $this->assertStringContainsString($this->verifier()->fixturePackageFingerprint($this->package()), $result['mismatch_summary']);
        $this->assertSame($before, $this->fileHashes(), 'verification must not change a package file');
    }

    public function test_the_admission_mechanism_admits_only_the_exact_fingerprint_and_refuses_any_damaged_package(): void
    {
        // MECHANISM CONTROL. The fingerprint below is supplied by the test, so this is not an approval and proves nothing about R0025;
        // it shows what the verifier does once a governed approval exists, and that nothing weaker is accepted.
        $w = R0025SyntheticV2World::build();
        $verifier = $this->verifier();
        $fingerprint = $verifier->fixturePackageFingerprint($this->package());
        $this->assertSame(trim((string) file_get_contents(dirname($this->package()).'/r0025-synthetic-v2-candidate-v1.fingerprint.txt')), $fingerprint, 'the recorded fingerprint is not the package fingerprint');

        $admitted = $verifier->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'], $fingerprint);
        $this->assertSame('PASS', $admitted['replay_status']);
        $this->assertSame('MATCH', $admitted['comparison_result']);
        $this->assertSame('ADMISSIBLE', $admitted['admission_state']);

        $other = $verifier->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'], hash('sha256', 'another package'));
        $this->assertSame('BLOCKED', $other['replay_status']);
        $this->assertStringStartsWith('REPLAY_INDEPENDENT_REVIEW_REQUIRED', $other['mismatch_summary']);

        // A changed byte in a bound file, an unbound file and a missing provenance each refuse the package under their own reason.
        $changed = $this->copyPackage();
        file_put_contents($changed.'/expected/expected_reason_code_counts.json', str_replace('"reason_count": 1', '"reason_count": 2', (string) file_get_contents($changed.'/expected/expected_reason_code_counts.json')));
        $tampered = $verifier->verifyRunAgainstFixture($w['run_id'], $changed, null, $w['publication_id'], $verifier->fixturePackageFingerprint($changed));
        $this->assertSame('BLOCKED', $tampered['replay_status']);
        $this->assertStringStartsWith('REPLAY_FIXTURE_PACKAGE_TAMPERED', $tampered['mismatch_summary'], 'approval of a changed package must not admit it');

        $extra = $this->copyPackage();
        file_put_contents($extra.'/notes/unbound.txt', 'not bound');
        $unbound = $verifier->verifyRunAgainstFixture($w['run_id'], $extra, null, $w['publication_id'], $verifier->fixturePackageFingerprint($extra));
        $this->assertStringStartsWith('REPLAY_FIXTURE_PACKAGE_TAMPERED', $unbound['mismatch_summary']);

        $noProvenance = $this->copyPackage();
        $manifest = json_decode((string) file_get_contents($noProvenance.'/manifest.json'), true);
        unset($manifest['independent_provenance']['oracle_version']);
        file_put_contents($noProvenance.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $missing = $verifier->verifyRunAgainstFixture($w['run_id'], $noProvenance, null, $w['publication_id'], $verifier->fixturePackageFingerprint($noProvenance));
        $this->assertStringStartsWith('REPLAY_FIXTURE_PROVENANCE_INCOMPLETE', $missing['mismatch_summary']);
    }

    // ------------------------------------------------------------------------------- 4. two allocation layouts

    public function test_two_allocation_layouts_give_identical_semantic_identities_and_the_oracle_literals(): void
    {
        $oracle = $this->packageJson('derivation/oracle_output.json');
        $a = $this->semanticIdentity(R0025SyntheticV2World::build());
        $this->freshTransaction();
        $b = $this->semanticIdentity(R0025SyntheticV2World::build(['ticker_id' => 975777, 'preconsume' => 41, 'calendar_order' => 'desc']));

        // retained semantic values: identical across layouts
        $this->assertSame($a['artifacts'], $b['artifacts']);
        $this->assertSame($a['nested'], $b['nested']);
        $this->assertSame($a['bound'], $b['bound']);
        $this->assertSame($a['factor_set_hash'], $b['factor_set_hash']);
        $this->assertSame(0, $a['mismatch_count']);
        $this->assertSame(0, $b['mismatch_count']);
        // and equal to the literals the standalone oracle derived from the frozen inputs
        $this->assertSame(['bars' => $oracle['artifacts']['bars']['sha256'], 'indicators' => $oracle['artifacts']['indicators']['sha256'], 'eligibility' => $oracle['artifacts']['eligibility']['sha256']], $a['artifacts']);
        $this->assertSame(array_values($oracle['nested_members']), array_values($a['nested']));
        $this->assertSame($oracle['replay_composites']['temporal_identity_hash'], $a['bound']['temporal_identity_hash']);
        $this->assertSame($oracle['replay_composites']['calendar_status_hash'], $a['bound']['calendar_status_hash']);
        // operational identities: allowed (and, here, shown) to differ
        foreach (['run_id', 'publication_id', 'listing_id', 'config_snapshot_id', 'factor_set_id', 'ticker_id'] as $key) {
            $this->assertNotSame($a['operational'][$key], $b['operational'][$key], $key.' did not differ, so the layouts did not vary it');
        }
    }

    // ------------------------------------------------------------------------------- 5. reference oracle

    public function test_the_standalone_oracle_reproduces_the_package_and_includes_no_application_code(): void
    {
        $copy = $this->copyPackage();
        $reproduced = $this->runOracle($copy);
        foreach (['expected/expected_replay_result.json', 'expected/expected_reason_code_counts.json', 'derivation/oracle_output.json'] as $file) {
            $this->assertSame(hash_file('sha256', $this->package().'/'.$file), hash_file('sha256', $copy.'/'.$file), $file.' is not reproduced from the frozen inputs');
        }
        $this->assertSame($this->packageJson('derivation/oracle_output.json')['nested_members'], $reproduced['nested_members']);

        $source = (string) file_get_contents($this->package().'/derivation/reference_oracle.php');
        $codeOnly = preg_replace('~^\s*//.*$~m', '', preg_replace('~/\*.*?\*/~s', '', $source));
        $this->assertSame(0, preg_match("/\\b(?:require|include)(?:_once)?\\b\\s*[\\('\"]/", $codeOnly), 'the oracle must not load other code');
        foreach (['use App', 'DB::', 'PDO', 'mysqli', 'App\\\\', 'ArtifactSemanticHashService', 'SemanticNestedIdentityService', 'SemanticObservationIdentityService',
            'DeterministicHashService', 'ReplayVerificationService', 'PublicationSemanticIdentityService', 'eod_publications', 'md_publication_lineage_bindings', 'md_source_observations'] as $forbidden) {
            $code = preg_replace('~/\*.*?\*/~s', '', $source);
            $code = preg_replace('~^\s*//.*$~m', '', $code);
            $this->assertStringNotContainsString($forbidden, $code, 'the oracle must not reference '.$forbidden);
        }
    }

    public function test_the_oracle_expectation_changes_where_authority_requires_it_and_only_there(): void
    {
        $packageBefore = $this->fileHashes();
        $base = $this->packageJson('derivation/oracle_output.json');
        $mutate = function (callable $change, string $what) use ($base): array {
            $copy = $this->copyPackage();
            $change($copy);
            $out = $this->runOracle($copy);
            $diff = [];
            foreach (['bars', 'indicators', 'eligibility'] as $artifact) {
                if ($out['artifacts'][$artifact]['sha256'] !== $base['artifacts'][$artifact]['sha256']) {
                    $diff[] = 'artifact:'.$artifact;
                }
            }
            foreach ($out['nested_members'] as $member => $hash) {
                if ($hash !== $base['nested_members'][$member]) {
                    $diff[] = 'nested:'.$member;
                }
            }
            foreach ($out['replay_composites'] as $name => $hash) {
                if ($hash !== $base['replay_composites'][$name]) {
                    $diff[] = 'composite:'.$name;
                }
            }
            sort($diff);
            $this->assertNotSame([], $diff, $what.' did not move any expectation');

            return $diff;
        };
        $world = function (string $copy, callable $edit) {
            $w = json_decode((string) file_get_contents($copy.'/inputs/synthetic_world.json'), true);
            $edit($w);
            file_put_contents($copy.'/inputs/synthetic_world.json', json_encode($w, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        };

        // bar price: only the bars artifact holds the price (the indicator row is invalid for lack of history whatever the price is)
        $price = $mutate(function ($c) use ($world) { $world($c, function (&$w) { $w['bar']['close'] = 106; }); }, 'a changed bar close');
        $this->assertSame(['artifact:bars'], $price);
        // bar volume
        $volume = $mutate(function ($c) use ($world) { $world($c, function (&$w) { $w['bar']['volume'] = 1001; }); }, 'a changed bar volume');
        $this->assertSame(['artifact:bars'], $volume);
        // retained listing identity: every artifact and every identity-bearing set carries the roots
        $root = $mutate(function ($c) use ($world) { $world($c, function (&$w) { $w['retained_roots']['listing'] = '5a000000-0000-4000-8000-0000000000ff'; }); }, 'a different retained listing root');
        $this->assertContains('artifact:bars', $root);
        $this->assertContains('artifact:indicators', $root);
        $this->assertContains('artifact:eligibility', $root);
        $this->assertContains('nested:identity_revision_set_hash', $root);
        $this->assertContains('composite:temporal_identity_hash', $root);
        $this->assertNotContains('nested:event_revision_set_hash', $root, 'an empty event set does not depend on the listing');
        // temporal revision: the calendar revision of the trade date
        $calendar = $mutate(function ($c) use ($world) { $world($c, function (&$w) { $w['calendar']['session_close'] = '15:50:00'; }); }, 'a changed calendar session close');
        $this->assertSame(['composite:calendar_status_hash', 'nested:calendar_revision_set_hash'], $calendar);
        // configuration semantic content
        $config = $mutate(function ($c) { $f = $c.'/inputs/frozen_config_content.txt'; file_put_contents($f, str_replace('"min_ratio":0.98', '"min_ratio":0.97', (string) file_get_contents($f))); }, 'a changed configuration value');
        $this->assertContains('artifact:bars', $config);
        $this->assertContains('artifact:indicators', $config);
        $this->assertContains('artifact:eligibility', $config);
        $this->assertContains('nested:factor_set_hash', $config);
        // provider response (the observation)
        $response = $mutate(function ($c) { $f = $c.'/inputs/provider_response.json'; file_put_contents($f, str_replace('"close":[105]', '"close":[106]', (string) file_get_contents($f))); }, 'a changed provider response');
        $this->assertContains('nested:observation_manifest_hash', $response);
        $this->assertContains('artifact:bars', $response);
        $this->assertContains('artifact:indicators', $response);
        // every mutation ran on a byte copy; the candidate package is byte-identical afterwards
        $this->assertSame($packageBefore, $this->fileHashes());
    }

    // ------------------------------------------------------------------------------- 6. anti-circularity

    public function test_the_package_is_not_self_generated_and_the_actual_output_cannot_rewrite_it(): void
    {
        $manifest = $this->packageJson('manifest.json');
        $this->assertSame('independent_golden_synthetic_v2', $manifest['fixture_family']);
        $this->assertStringStartsNotWith('runtime_generated', $manifest['fixture_family']);
        $this->assertDoesNotMatchRegularExpression('/(?:^|_)run_\d+(?:_|$)/', $manifest['fixture_source']);
        $this->assertStringStartsWith('independent_derivation:', $manifest['fixture_source']);
        $this->assertSame('CANDIDATE_AWAITING_INDEPENDENT_REVIEW', $manifest['fixture_status']);
        $this->assertNull($manifest['independent_provenance']['independent_reviewer']);
        $this->assertNull($manifest['independent_provenance']['owner_approval']);

        $before = $this->fileHashes();
        $w = R0025SyntheticV2World::build(['tamper_response' => str_replace('"close":[105]', '"close":[107]', (string) file_get_contents($this->package().'/inputs/provider_response.json'))]);
        $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
        // A different actual output is judged against the SAME expectation: it mismatches, and the expectation is untouched.
        $this->assertGreaterThan(0, $result['mismatch_count'], 'a different actual output must not match an independent expectation');
        $fields = array_column($result['mismatches'], 'field');
        $this->assertContains('bars_batch_hash', $fields);
        $this->assertContains('bound_input_source_observation_manifest_hash', $fields);
        $this->assertSame($before, $this->fileHashes(), 'the actual output rewrote the expected package');
    }

    public function test_a_fixture_built_from_the_run_under_verification_is_still_refused_as_self_generated(): void
    {
        $w = R0025SyntheticV2World::build();
        $dir = storage_path('framework/testing/r0025-self-generated-'.bin2hex(random_bytes(4)));
        $this->copies[] = $dir;
        $verifier = $this->verifier();
        $verifier->generateFixtureFromRun($w['run_id'], $dir, 'self', $w['publication_id']);
        $result = $verifier->verifyRunAgainstFixture($w['run_id'], $dir, null, $w['publication_id']);
        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertStringStartsWith('REPLAY_FIXTURE_SELF_GENERATED', $result['mismatch_summary']);

        // renaming does not help: a self-generated expectation relabelled as an independent package is refused for its source
        $relabelled = storage_path('framework/testing/r0025-relabelled-'.bin2hex(random_bytes(4)));
        $this->copies[] = $relabelled;
        $this->copyTree($dir, $relabelled);
        $manifest = json_decode((string) file_get_contents($relabelled.'/manifest.json'), true);
        $manifest['fixture_family'] = 'independent_golden_synthetic_v2';
        file_put_contents($relabelled.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $second = $verifier->verifyRunAgainstFixture($w['run_id'], $relabelled, null, $w['publication_id']);
        $this->assertSame('BLOCKED', $second['replay_status']);
        $this->assertStringStartsWith('REPLAY_FIXTURE_SELF_GENERATED', $second['mismatch_summary'], 'a relabelled self-generated package must still be refused as self-generated');

        // and a source that no longer names the run does not help while the generated family says what it is
        $reworded = storage_path('framework/testing/r0025-reworded-'.bin2hex(random_bytes(4)));
        $this->copies[] = $reworded;
        $this->copyTree($dir, $reworded);
        $manifest = json_decode((string) file_get_contents($reworded.'/manifest.json'), true);
        $manifest['fixture_source'] = 'hand_authored_for_review';
        file_put_contents($reworded.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $third = $verifier->verifyRunAgainstFixture($w['run_id'], $reworded, null, $w['publication_id']);
        $this->assertStringStartsWith('REPLAY_FIXTURE_SELF_GENERATED', $third['mismatch_summary'], 'the generated family alone must keep a package self-generated');
    }

    // ------------------------------------------------------------------------------- 7. target-bound identities

    public function test_a_target_marker_is_a_closed_list_and_never_a_wildcard(): void
    {
        $w = R0025SyntheticV2World::build();
        $verifier = $this->verifier();
        $expectSchemaError = function (callable $edit, string $why) use ($w, $verifier) {
            $copy = $this->copyPackage();
            $expected = json_decode((string) file_get_contents($copy.'/expected/expected_replay_result.json'), true);
            $manifest = json_decode((string) file_get_contents($copy.'/manifest.json'), true);
            $edit($expected, $manifest);
            file_put_contents($copy.'/expected/expected_replay_result.json', json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            file_put_contents($copy.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $thrown = $this->thrownBy(function () use ($verifier, $w, $copy) {
                $verifier->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id']);
            });
            $this->assertNotNull($thrown, $why.' was not refused');
            $this->assertStringContainsString('REPLAY_FIXTURE_SCHEMA_MISMATCH', $thrown->getMessage(), $why);
        };
        // a semantic field may not be replaced by a marker
        $expectSchemaError(function (&$e, &$m) { $e['expected_final_state']['terminal_status'] = '@TARGET:publication_id'; $m['target_bound_fields']['expected_final_state.terminal_status'] = 'publication_id'; }, 'a marker on a semantic field');
        // a hash may not be replaced by a marker
        $expectSchemaError(function (&$e, &$m) { $e['expected_artifact_context']['bars_batch_hash'] = '@TARGET:artifact_scope'; $m['target_bound_fields']['expected_artifact_context.bars_batch_hash'] = 'artifact_scope'; }, 'a marker on a hash');
        // the kind must match the path
        $expectSchemaError(function (&$e, &$m) { $e['expected_lineage']['run_id'] = '@TARGET:publication_id'; $m['target_bound_fields']['expected_lineage.run_id'] = 'publication_id'; }, 'a marker of the wrong kind');
        // the declaration must equal the markers present
        $expectSchemaError(function (&$e, &$m) { unset($m['target_bound_fields']['expected_lineage.run_id']); }, 'an undeclared marker');
        $expectSchemaError(function (&$e, &$m) { $m['target_bound_fields']['expected_pointer_context.pointer_switched'] = 'run_id'; }, 'a declared path with no marker');

        // The binding is from the target named by the caller: naming another run's publication does not match this run.
        $fixture = $this->package();
        $this->freshTransaction();
        $other = R0025SyntheticV2World::build(['ticker_id' => 975800, 'preconsume' => 5]);
        $result = null;
        $thrown = $this->thrownBy(function () use ($verifier, $w, $fixture, $other, &$result) {
            $result = $verifier->verifyRunAgainstFixture($w['run_id'], $fixture, null, $other['publication_id']);
        });
        if ($thrown !== null) {
            $this->assertStringContainsString('REPLAY', $thrown->getMessage());
        } else {
            $this->assertGreaterThan(0, $result['mismatch_count'], 'a publication that is not the run\'s must not verify');
        }
    }

    // ------------------------------------------------------------------------------- 8. V2 fails closed, V1 unchanged

    public function test_a_v2_run_without_a_retained_identity_fails_closed_and_never_falls_back_to_v1(): void
    {
        $w = null;
        $thrown = $this->thrownBy(function () use (&$w) {
            $w = R0025SyntheticV2World::build(['retained_registry' => false]);
        });
        if ($thrown !== null) {
            $failed = true;
            $this->assertMatchesRegularExpression('/FOUNDATION_IDENTITY|ARTIFACT_/', $thrown->getMessage(), 'the V2 run failed for an unexpected reason: '.$thrown->getMessage());
        } else {
            $run = DB::connection()->table('eod_runs')->where('run_id', $w['run_id'])->first();
            $failed = $run->terminal_status !== 'SUCCESS' || ($w['publication'] && $w['publication']->seal_state !== 'SEALED');
            $this->assertNotSame('market-row-hash/v1', (string) ($run->artifact_hash_profile ?? ''));
            $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $run->artifact_hash_profile, 'the run was silently downgraded');
        }
        $this->assertTrue($failed, 'a V2 run with no retained identity must not publish');
        $this->assertSame(0, DB::connection()->table('eod_publications')->where('seal_state', 'SEALED')->where('artifact_hash_profile', ArtifactSemanticHashService::PROFILE_V2)->where('trade_date', '2026-03-23')->count());
    }

    public function test_the_v2_replay_identities_fail_closed_when_a_v2_publication_lacks_a_semantic_member(): void
    {
        $w = R0025SyntheticV2World::build();
        // damage one semantic member of the lineage binding out of band (the test transaction is rolled back)
        DB::connection()->table('md_publication_lineage_bindings')->where('publication_id', $w['publication_id'])->update(['semantic_identity_revision_set_hash' => null]);
        $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertSame('', $result['actual_context']['actual_bound_input_context']['temporal_identity_hash'], 'the V1 identity was substituted for a missing V2 member');
        $this->assertSame('', $result['actual_context']['actual_bound_input_context']['calendar_status_hash'], 'an incomplete V2 lineage must leave every composite identity unavailable');
        $this->assertSame('', $result['actual_context']['actual_bound_input_context']['event_factor_hash']);
        $this->assertStringStartsWith('REPLAY_BOUND_INPUT_INCOMPLETE', $result['mismatch_summary'] ?? '', 'a missing frozen identity must block the replay');
    }

    public function test_a_target_bound_factor_set_must_be_the_one_the_lineage_binding_names(): void
    {
        $w = R0025SyntheticV2World::build();
        DB::connection()->table('md_publication_lineage_bindings')->where('publication_id', $w['publication_id'])->update(['factor_set_id' => (int) $w['publication']->factor_set_id + 1000000]);
        $thrown = $this->thrownBy(function () use ($w) {
            $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
        });
        $this->assertNotNull($thrown, 'a factor set that the lineage binding does not name was bound');
        $this->assertStringContainsString('REPLAY_TARGET_BOUND_REFERENCE_INVALID', $thrown->getMessage());
    }

    public function test_a_v1_profile_run_keeps_its_historical_interpretation(): void
    {
        $w = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1']);
        $db = DB::connection();
        $publication = $db->table('eod_publications')->where('publication_id', $w['publication_id'])->first();
        $this->assertSame('market-data-row-hash/v1', $publication->artifact_hash_profile);
        // V1 eligibility rows keep their historical NULL configuration binding, so V1 hashes stay byte-identical
        $this->assertNull($db->table('eod_eligibility')->where('run_id', $w['run_id'])->value('config_snapshot_id'));
        $dir = storage_path('framework/testing/r0025-v1-generated-'.bin2hex(random_bytes(4)));
        $this->copies[] = $dir;
        $verifier = $this->verifier();
        $verifier->generateFixtureFromRun($w['run_id'], $dir, 'v1', $w['publication_id']);
        $result = $verifier->verifyRunAgainstFixture($w['run_id'], $dir, null, $w['publication_id']);
        $bound = $result['actual_context']['actual_bound_input_context'];
        $run = $db->table('eod_runs')->where('run_id', $w['run_id'])->first();
        $this->assertSame((string) $run->observation_manifest_hash, $bound['source_observation_manifest_hash'], 'V1 uses the V1 observation manifest hash');
        $this->assertSame((string) $publication->factor_set_hash, $result['actual_context']['actual_publication_context']['factor_set_hash'], 'V1 uses the V1 factor-set hash');
    }

    // ------------------------------------------------------------------------------- 9. fingerprints

    public function test_the_manifest_binds_every_file_and_the_recorded_fingerprint_is_the_package_fingerprint(): void
    {
        $manifest = $this->packageJson('manifest.json');
        $actual = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->package(), FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $this->package())) + 1);
            if ($file->isFile() && $relative !== 'manifest.json') {
                $actual[$relative] = hash_file('sha256', $file->getPathname());
            }
        }
        ksort($actual, SORT_STRING);
        $this->assertSame($actual, $manifest['files_sha256']);
        $this->assertSame(trim((string) file_get_contents(dirname($this->package()).'/r0025-synthetic-v2-candidate-v1.fingerprint.txt')), $this->verifier()->fixturePackageFingerprint($this->package()));
        $classification = $this->packageJson('derivation/field_classification.json');
        $this->assertSame(['LITERAL_SEMANTIC_EXPECTATION', 'DERIVED_FROM_FROZEN_INPUT', 'TARGET_BOUND_OPERATIONAL'], array_keys(array_intersect_key($classification['counts'], array_flip(['LITERAL_SEMANTIC_EXPECTATION', 'DERIVED_FROM_FROZEN_INPUT', 'TARGET_BOUND_OPERATIONAL']))));
        $expected = $this->packageJson('expected/expected_replay_result.json');
        $leaves = 0;
        $walk = function ($node) use (&$walk, &$leaves) {
            if (is_array($node) && $node !== [] && array_keys($node) !== range(0, count($node) - 1)) {
                foreach ($node as $child) {
                    $walk($child);
                }

                return;
            }
            $leaves++;
        };
        $walk($expected);
        $this->assertSame($leaves - 1, $classification['counts']['LITERAL_SEMANTIC_EXPECTATION'] + $classification['counts']['DERIVED_FROM_FROZEN_INPUT'] + $classification['counts']['TARGET_BOUND_OPERATIONAL'],
            'every expected field (except the comparison note) must be classified: no silent field omission');
        $this->assertSame(array_keys($manifest['target_bound_fields']), array_keys($classification['TARGET_BOUND_OPERATIONAL']));
    }

    /** @return array<string,string> */
    private function fileHashes(): array
    {
        $hashes = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->package(), FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $hashes[substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $this->package())) + 1)] = hash_file('sha256', $file->getPathname());
            }
        }
        ksort($hashes);

        return $hashes;
    }
}
