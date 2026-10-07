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
 * Controls of the CANDIDATE independent R0025 golden fixture, candidate-v5 (package `tests/fixtures/replay/r0025-synthetic-v2-candidate-v5`), a PRE-ACTIVATION candidate authored against the FINAL
 * post-A003 build (the configuration snapshot content carries the derived reason_registry member). Candidate-v4 (reviewed PASS, approved, admitted, then invalidated for the final build by
 * E-MD-B18-A002-108) and candidate-v1, -v2 and -v3 (reviewed CHANGES REQUIRED, never approved) are retained untouched; their preservation is controlled below.
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

    /** The eleven bound inputs of Replay_Verification_Contract_LOCKED.md that a replay compares; every one is a literal in candidate-v2. */
    private const ELEVEN = ['source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash', 'calendar_status_hash', 'event_factor_hash', 'config_snapshot_hash',
        'formula_registry_hash', 'reason_registry_hash', 'read_model_version', 'serialization_version', 'executable_build_identity'];

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
            'bound' => array_intersect_key($result['actual_context']['actual_bound_input_context'], array_flip(self::ELEVEN)),
            'factor_set_hash' => $result['actual_context']['actual_publication_context']['factor_set_hash'],
            'manifest' => $result['actual_context']['actual_publication_context']['publication_manifest_hash'],
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
        // ALL ELEVEN bound inputs were compared (none skipped as a NULL expectation) and each actual value equals its literal expectation
        $compared = array_unique($result['deterministic_fields_checked']);
        foreach (self::ELEVEN as $field) {
            $this->assertContains('bound_input_'.$field, $compared, $field.' was not compared');
            $this->assertNotSame('', (string) $result['actual_context']['actual_bound_input_context'][$field], $field.' is unavailable on the actual side');
            $this->assertSame($this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'][$field], $result['actual_context']['actual_bound_input_context'][$field], $field);
        }
        // the frozen publication manifest hash was compared too (F-MD-B18-A002-031)
        $this->assertContains('publication_manifest_hash', $compared);
        $this->assertSame($this->packageJson('expected/expected_replay_result.json')['expected_publication_context']['publication_manifest_hash'], $result['actual_context']['actual_publication_context']['publication_manifest_hash']);
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
        $this->assertSame(trim((string) file_get_contents(dirname($this->package()).'/r0025-synthetic-v2-candidate-v5.fingerprint.txt')), $fingerprint, 'the recorded fingerprint is not the package fingerprint');

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
        $this->assertCount(11, $a['bound'], 'all eleven bound inputs must be compared');
        foreach (self::ELEVEN as $field) {
            $this->assertSame($a['bound'][$field], $b['bound'][$field], $field.' moved with the allocation layout');
            $this->assertSame($this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'][$field], $a['bound'][$field], $field.' differs from its literal expectation');
        }
        $this->assertSame($a['factor_set_hash'], $b['factor_set_hash']);
        $this->assertSame($a['manifest'], $b['manifest'], 'the publication manifest hash moved with the allocation layout');
        $this->assertSame($oracle['publication_manifest']['hash'], $a['manifest'], 'the publication manifest hash differs from the oracle literal');
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
            if ($out['publication_manifest']['hash'] !== $base['publication_manifest']['hash']) {
                $diff[] = 'publication:publication_manifest_hash';
            }
            $expectedBefore = $this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'];
            $expectedAfter = json_decode((string) file_get_contents($copy.'/expected/expected_replay_result.json'), true)['expected_bound_input_context'];
            foreach (array_keys($expectedBefore) as $field) {
                if ($expectedBefore[$field] !== $expectedAfter[$field]) {
                    $diff[] = 'bound:'.$field;
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
        $this->assertSame(['artifact:bars', 'bound:canonical_raw_input_hash', 'publication:publication_manifest_hash'], $price);
        // bar volume
        $volume = $mutate(function ($c) use ($world) { $world($c, function (&$w) { $w['bar']['volume'] = 1001; }); }, 'a changed bar volume');
        $this->assertSame(['artifact:bars', 'bound:canonical_raw_input_hash', 'publication:publication_manifest_hash'], $volume);
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
        $this->assertSame(['bound:calendar_status_hash', 'composite:calendar_status_hash', 'nested:calendar_revision_set_hash', 'publication:publication_manifest_hash'], $calendar);
        // configuration semantic content
        $config = $mutate(function ($c) { $f = $c.'/inputs/frozen_config_content.txt'; file_put_contents($f, str_replace('"min_ratio":0.98', '"min_ratio":0.97', (string) file_get_contents($f))); }, 'a changed configuration value');
        $this->assertContains('artifact:bars', $config);
        $this->assertContains('artifact:indicators', $config);
        $this->assertContains('artifact:eligibility', $config);
        $this->assertContains('nested:factor_set_hash', $config);
        $this->assertContains('bound:event_factor_hash', $config, 'the factor set carries the configuration content, so the event/factor identity moves with it');
        $this->assertContains('bound:config_snapshot_hash', $config);
        // provider response (the observation)
        $response = $mutate(function ($c) { $f = $c.'/inputs/provider_response.json'; file_put_contents($f, str_replace('"close":[105]', '"close":[106]', (string) file_get_contents($f))); }, 'a changed provider response');
        $this->assertContains('nested:observation_manifest_hash', $response);
        $this->assertContains('artifact:bars', $response);
        $this->assertContains('artifact:indicators', $response);
        // formula registry: a changed registry member moves the formula identity and nothing else (the literal is a frozen registry input)
        $formula = $mutate(function ($c) { $f = $c.'/inputs/frozen_registry_literals.json'; file_put_contents($f, str_replace('producer_registry_content_v1', 'producer_registry_content_v2', (string) file_get_contents($f))); }, 'a changed registry member');
        $this->assertSame(['bound:formula_registry_hash'], $formula);
        // the read-model version is its own bound input and also a member of the formula identity
        $readModel = $mutate(function ($c) { $f = $c.'/inputs/frozen_registry_literals.json'; file_put_contents($f, str_replace('market_data_read_product_v1', 'market_data_read_product_v2', (string) file_get_contents($f))); }, 'a changed read-model version');
        $this->assertContains('bound:read_model_version', $readModel);
        $this->assertContains('bound:formula_registry_hash', $readModel);
        $this->assertNotContains('bound:reason_registry_hash', $readModel, 'the reason registry does not depend on the formula registry');
        // reason registry: one entry of the frozen registry changes its identity AND, since the final build (D-MD-B18-A002-018 Q9 = A1, E-MD-B04-A003-002), the configuration snapshot content
        // that carries the identity as a member; so the config hash moves and with it every value that binds the config hash. Candidate-v4 expected this to move the identity alone.
        $reason = $mutate(function ($c) { $f = $c.'/inputs/frozen_reason_registry.json'; $j = json_decode((string) file_get_contents($f), true); $j['entries'][0]['severity'] = 'ZZ'.$j['entries'][0]['severity']; file_put_contents($f, json_encode($j)); }, 'a changed reason entry');
        $this->assertSame(['artifact:bars', 'artifact:eligibility', 'artifact:indicators', 'bound:canonical_raw_input_hash', 'bound:config_snapshot_hash', 'bound:event_factor_hash', 'bound:reason_registry_hash',
            'nested:factor_set_hash', 'publication:publication_manifest_hash'], $reason);
        foreach (['bound:formula_registry_hash', 'bound:executable_build_identity', 'bound:source_observation_manifest_hash', 'bound:temporal_identity_hash', 'bound:calendar_status_hash'] as $unmoved) {
            $this->assertNotContains($unmoved, $reason, $unmoved.' does not depend on the reason registry content');
        }
        // executable build: the frozen identity moves the build input and nothing else
        $build = $mutate(function ($c) { $f = $c.'/inputs/frozen_build_identity.json'; $j = json_decode((string) file_get_contents($f), true); $j['content_hash'] = str_repeat('a', 64); $j['build_id'] = 'sha256:'.str_repeat('a', 64); file_put_contents($f, json_encode($j)); }, 'a different frozen build identity');
        $this->assertSame(['bound:executable_build_identity'], $build);
        // provider observation instant: the observation manifest, the bars artifact (which binds the manifest) and what binds those move; the identity sets do not
        $instant = $mutate(function ($c) use ($world) {
            $f = $c.'/inputs/provider_response.json';
            file_put_contents($f, str_replace('1774231200', '1774231260', (string) file_get_contents($f)));
            $world($c, function (&$w) { $w['bar']['provider_timestamp_unix'] = 1774231260; });
        }, 'a changed provider instant');
        $this->assertContains('nested:observation_manifest_hash', $instant);
        $this->assertContains('artifact:bars', $instant);
        $this->assertContains('bound:source_observation_manifest_hash', $instant);
        foreach (['nested:identity_revision_set_hash', 'nested:calendar_revision_set_hash', 'nested:event_revision_set_hash', 'nested:factor_set_hash', 'bound:event_factor_hash', 'bound:formula_registry_hash', 'bound:reason_registry_hash', 'bound:executable_build_identity'] as $unmoved) {
            $this->assertNotContains($unmoved, $instant, $unmoved.' must not depend on the provider instant');
        }
        // an instant outside the trade date is not the observation of the requested date: the frozen world must be refused, not silently NULLed
        $refused = $this->copyPackage();
        $f = $refused.'/inputs/provider_response.json';
        file_put_contents($f, str_replace('1774231200', '1774317600', (string) file_get_contents($f)));
        $out = []; $code = 0;
        exec('"'.PHP_BINARY.'" '.escapeshellarg($refused.'/derivation/reference_oracle.php').' 2>&1', $out, $code);
        $this->assertSame(2, $code, 'the oracle must refuse a provider response with no observed instant for the trade date');
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

    public function test_an_unavailable_contamination_projection_leaves_the_event_factor_identity_unavailable(): void
    {
        $w = R0025SyntheticV2World::build();
        $db = DB::connection();
        $run = $db->table('eod_runs')->where('run_id', $w['run_id'])->first();
        $publication = $db->table('eod_publications')->where('publication_id', $w['publication_id'])->first();
        $verifier = $this->verifier();
        $contamination = new \ReflectionMethod(ReplayVerificationService::class, 'semanticV2ContaminationIdentity');
        $contamination->setAccessible(true);
        // no indicator-dependency capture among the components: nothing to project, so unavailable (never the empty state by default)
        $this->assertNull($contamination->invoke($verifier, $run, $publication, []));
        $this->assertNull($contamination->invoke($verifier, $run, $publication, [['component_key' => 'ancillary', 'stage_code' => 'INDICATORS', 'slot_hash' => str_repeat('0', 64), 'source_run_id' => $w['run_id']]]));
        // and an unavailable projection leaves the composite empty, with the lineage complete
        $lineage = new \ReflectionMethod(ReplayVerificationService::class, 'semanticV2Lineage');
        $lineage->setAccessible(true);
        $v2 = $lineage->invoke($verifier, $publication);
        $this->assertTrue($v2['complete']);
        $eventFactor = new \ReflectionMethod(ReplayVerificationService::class, 'semanticV2EventFactor');
        $eventFactor->setAccessible(true);
        $this->assertSame('', $eventFactor->invoke($verifier, $v2, null));
        $this->assertSame('', $eventFactor->invoke($verifier, $v2, ''));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $eventFactor->invoke($verifier, $v2, str_repeat('a', 64)));
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
        // V1 keeps the historical whole-payload registry identity (D-MD-B10-A002-005): both identities are the registry capture payload hash
        $registryHash = $db->table('md_run_input_captures')->where('run_id', $w['run_id'])->where('component_key', 'registry_versions')->value('payload_hash');
        $this->assertSame($registryHash, $bound['formula_registry_hash'], 'V1 formula identity is the whole registry capture');
        $this->assertSame($registryHash, $bound['reason_registry_hash'], 'V1 reason identity is the whole registry capture');
    }

    // ------------------------------------------------------------------------------- 8b. eleven literal bound inputs, one control each

    /** Recomputes the sha256 of every file of a package copy into its manifest, so that a control reaches the check it targets and not the tamper check. */
    private function reseal(string $dir): void
    {
        $manifest = json_decode((string) file_get_contents($dir.'/manifest.json'), true);
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $dir)) + 1);
            if ($file->isFile() && $relative !== 'manifest.json') {
                $files[$relative] = hash_file('sha256', $file->getPathname());
            }
        }
        ksort($files, SORT_STRING);
        $manifest['files_sha256'] = $files;
        file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /** The package copy with its expected bound inputs edited and resealed; the verifier is then given that copy's own fingerprint as the mechanism control. */
    private function verifyEdited(array $world, callable $editBound): array
    {
        $copy = $this->copyPackage();
        $expected = json_decode((string) file_get_contents($copy.'/expected/expected_replay_result.json'), true);
        $editBound($expected['expected_bound_input_context']);
        file_put_contents($copy.'/expected/expected_replay_result.json', json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->reseal($copy);

        return $this->verifier()->verifyRunAgainstFixture($world['run_id'], $copy, null, $world['publication_id'], $this->verifier()->fixturePackageFingerprint($copy));
    }

    public function test_every_one_of_the_eleven_bound_inputs_is_a_literal_in_the_package(): void
    {
        $bound = $this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'];
        $this->assertSame(self::ELEVEN, array_keys(array_intersect_key(array_flip(self::ELEVEN), $bound)), 'the package must assert exactly the eleven bound inputs');
        $this->assertCount(11, $bound);
        foreach (self::ELEVEN as $field) {
            $this->assertIsString($bound[$field], $field);
            $this->assertNotSame('', trim($bound[$field]), $field.' is empty');
            $this->assertStringStartsNotWith('@TARGET:', $bound[$field], $field.' must not be target-bound');
        }
        $manifest = $this->packageJson('manifest.json');
        $this->assertSame(self::ELEVEN, $manifest['asserted_literal_bound_inputs']);
        $this->assertArrayNotHasKey('not_asserted_bound_inputs', $manifest, 'candidate-v2 leaves no bound input unasserted');
        foreach (array_keys($manifest['target_bound_fields']) as $path) {
            $this->assertStringStartsNotWith('expected_bound_input_context.', $path, 'no bound input may be target-bound');
        }
        $this->assertMatchesRegularExpression('/^sha256:[a-f0-9]{64}$/', $bound['executable_build_identity']);
        $this->assertSame($this->packageJson('inputs/frozen_build_identity.json')['build_id'], $bound['executable_build_identity'], 'the build identity is the frozen literal');
    }

    public function test_a_missing_or_empty_bound_input_blocks_an_independent_package_even_with_an_approval_supplied(): void
    {
        $w = R0025SyntheticV2World::build();
        foreach (self::ELEVEN as $field) {
            foreach (['removed' => function (&$b) use ($field) { unset($b[$field]); }, 'null' => function (&$b) use ($field) { $b[$field] = null; }, 'empty' => function (&$b) use ($field) { $b[$field] = ''; }] as $how => $edit) {
                $result = $this->verifyEdited($w, $edit);
                $this->assertSame('BLOCKED', $result['replay_status'], $field.' '.$how);
                $this->assertStringStartsWith('REPLAY_INDEPENDENT_BOUND_INPUT_REQUIRED: expected_bound_input_context.'.$field.' ', $result['mismatch_summary'], $field.' '.$how);
                $this->assertSame('NOT_ADMISSIBLE', $result['admission_state'], $field.' '.$how);
            }
        }
    }

    public function test_a_target_marker_cannot_stand_in_for_any_of_the_eleven_bound_inputs(): void
    {
        $w = R0025SyntheticV2World::build();
        foreach (self::ELEVEN as $field) {
            $copy = $this->copyPackage();
            $expected = json_decode((string) file_get_contents($copy.'/expected/expected_replay_result.json'), true);
            $manifest = json_decode((string) file_get_contents($copy.'/manifest.json'), true);
            $expected['expected_bound_input_context'][$field] = '@TARGET:run_id';
            $manifest['target_bound_fields']['expected_bound_input_context.'.$field] = 'run_id';
            file_put_contents($copy.'/expected/expected_replay_result.json', json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            file_put_contents($copy.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->reseal($copy);
            $thrown = $this->thrownBy(function () use ($w, $copy) {
                $this->verifier()->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id']);
            });
            $this->assertNotNull($thrown, $field.' was made target-bound');
            $this->assertStringContainsString('REPLAY_FIXTURE_SCHEMA_MISMATCH', $thrown->getMessage(), $field);
            $this->assertStringContainsString('expected_bound_input_context.'.$field, $thrown->getMessage(), $field);
        }
    }

    public function test_a_wrong_literal_in_any_one_bound_input_is_the_only_mismatch_it_causes(): void
    {
        $w = R0025SyntheticV2World::build();
        $bad = ['source_observation_manifest_hash' => str_repeat('1', 64), 'canonical_raw_input_hash' => str_repeat('2', 64), 'temporal_identity_hash' => str_repeat('3', 64),
            'calendar_status_hash' => str_repeat('4', 64), 'event_factor_hash' => str_repeat('5', 64), 'config_snapshot_hash' => str_repeat('6', 64),
            'formula_registry_hash' => str_repeat('7', 64), 'reason_registry_hash' => str_repeat('8', 64), 'read_model_version' => 'market_data_read_product_v9',
            'serialization_version' => 'canonical_json_v9', 'executable_build_identity' => 'sha256:'.str_repeat('9', 64)];
        foreach (self::ELEVEN as $field) {
            $result = $this->verifyEdited($w, function (&$b) use ($field, $bad) { $b[$field] = $bad[$field]; });
            $fields = array_column($result['mismatches'], 'field');
            $this->assertSame(['bound_input_'.$field], $fields, $field.': exactly its own comparison must fail');
            $this->assertNotSame('PASS', $result['replay_status'], $field);
        }
    }

    public function test_each_semantic_member_of_the_event_factor_identity_moves_it(): void
    {
        $db = DB::connection();
        $columns = \App\Application\MarketData\Services\SemanticNestedIdentityService::LINEAGE_COLUMNS;
        $expected = $this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'];
        $w = R0025SyntheticV2World::build();
        $base = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'], $this->verifier()->fixturePackageFingerprint($this->package()));
        $this->assertSame('PASS', $base['replay_status']);
        $this->assertSame($expected['event_factor_hash'], $base['actual_context']['actual_bound_input_context']['event_factor_hash']);
        foreach (['event_revision_set_hash', 'source_scale_assessment_set_hash', 'factor_decision_set_hash', 'factor_set_hash'] as $member) {
            $this->freshTransaction();
            $w = R0025SyntheticV2World::build();
            $db->table('md_publication_lineage_bindings')->where('publication_id', $w['publication_id'])->update([$columns[$member] => str_repeat('e', 64)]);
            $actual = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'])['actual_context']['actual_bound_input_context'];
            $this->assertNotSame($expected['event_factor_hash'], $actual['event_factor_hash'], $member.' must move the event/factor identity');
            $this->assertNotSame('', $actual['event_factor_hash'], $member);
            $this->assertSame($expected['temporal_identity_hash'], $actual['temporal_identity_hash'], 'the temporal identity is not an event/factor member');
        }
    }

    public function test_the_contamination_decision_member_binds_semantic_facts_found_through_the_retained_root_and_not_local_ids(): void
    {
        $captures = function (array $world, array $contamination, array $breaks) {
            $db = DB::connection();
            $repository = \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::class;
            static $serial = 0;
            $selection = ['operation' => 'indicator-materialized-dependencies/v1', 'control' => 'contamination-projection', 'serial' => ++$serial, 'bar_load_window' => 60];
            $payload = $repository::canonicalJson(['schema_version' => 'md_producer_capture_v1', 'component_key' => 'ancillary',
                'selection_context' => $selection, 'rows' => [['contamination' => $contamination, 'price_scale_breaks' => $breaks]], 'empty_basis' => null]);
            $slot = $repository::slotHash('ancillary', $selection);
            $db->table('md_run_input_captures')->insert(['run_id' => $world['run_id'], 'stage_code' => 'INDICATORS', 'component_key' => 'ancillary', 'slot_hash' => $slot, 'capture_schema_version' => 'md_producer_capture_v1',
                'selection_context_json' => $repository::canonicalJson($selection), 'semantic_payload_json' => $payload, 'payload_hash' => hash('sha256', $payload),
                'member_count' => 1, 'empty_basis_json' => null, 'audit_context_json' => '{}', 'captured_at' => '2026-03-25 10:30:00.000000']);
            $run = $db->table('eod_runs')->where('run_id', $world['run_id'])->first();
            $publication = $db->table('eod_publications')->where('publication_id', $world['publication_id'])->first();
            $method = new \ReflectionMethod(ReplayVerificationService::class, 'semanticV2ContaminationIdentity');
            $method->setAccessible(true);

            return $method->invoke($this->verifier(), $run, $publication, [['component_key' => 'ancillary', 'stage_code' => 'INDICATORS', 'slot_hash' => $slot, 'source_run_id' => $world['run_id']]]);
        };
        $entry = function (int $revisionId) {
            return ['corporate_action_revision_id' => $revisionId, 'action_type_code' => 'SPLIT', 'verification_state' => 'AUTHORITATIVE_VERIFIED', 'ex_date' => '2026-03-20', 'action_date' => '2026-03-20',
                'anchor_state' => 'VERIFIED_EX_DATE_REVISION', 'depth' => 1, 'breaks_price_continuity' => true, 'breaks_volume_continuity' => true, 'is_unmapped_type' => false];
        };
        $root = R0025SyntheticV2World::world()['retained_roots']['listing'];
        $oracle = $this->packageJson('derivation/oracle_output.json')['candidate_v2_identities']['contamination_decision_set']['hash'];

        $a = R0025SyntheticV2World::build();
        $emptyA = $captures($a, [], []);
        $this->assertSame($oracle, $emptyA, 'the empty contamination state is the oracle literal');
        $decidedA = $captures($a, [$a['ticker_id'] => [$entry(41)]], []);
        $this->assertSame(\App\Application\MarketData\Services\ReplayV2IdentityProjection::contaminationDecisionIdentity([1 => [$entry(41)]], [], [1 => $root]), $decidedA, 'the identity is keyed by the retained listing root');
        $this->assertNotSame($emptyA, $decidedA, 'a contamination decision must move the identity');

        $this->freshTransaction();
        $b = R0025SyntheticV2World::build(['ticker_id' => 975777, 'preconsume' => 41, 'calendar_order' => 'desc']);
        $this->assertNotSame($a['ticker_id'], $b['ticker_id']);
        $decidedB = $captures($b, [$b['ticker_id'] => [$entry(9001)]], []);
        $this->assertSame($decidedA, $decidedB, 'another ticker id and another revision id with the same semantic decision must give the same identity');
        $changedB = $captures($b, [$b['ticker_id'] => [array_merge($entry(9001), ['depth' => 2])]], []);
        $this->assertNotSame($decidedB, $changedB, 'a changed semantic fact must move the identity');
    }

    public function test_the_registry_identities_are_separate_and_the_build_is_in_neither(): void
    {
        $w = R0025SyntheticV2World::build();
        $bound = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'])['actual_context']['actual_bound_input_context'];
        $this->assertNotSame($bound['formula_registry_hash'], $bound['reason_registry_hash'], 'the formula and reason identities are separate domains');
        $registry = DB::connection()->table('md_run_input_captures')->where('run_id', $w['run_id'])->where('component_key', 'registry_versions')->first();
        $this->assertNotSame($registry->payload_hash, $bound['formula_registry_hash'], 'the whole registry capture (which holds the build) is not the formula identity');
        $this->assertNotSame($registry->payload_hash, $bound['reason_registry_hash'], 'the whole registry capture (which holds the build) is not the reason identity');
        $payload = json_decode($registry->semantic_payload_json, true)['rows'][0];
        $this->assertSame('sha256:'.$payload['executable_build']['content_hash'], $bound['executable_build_identity'], 'the build identity is the publication-bound captured build');
        // rebuilding the identities from the captured content with every build member replaced gives the same values
        $stripped = $payload;
        $stripped['executable_build'] = ['build_id' => 'sha256:'.str_repeat('0', 64), 'php_version' => '0', 'files' => []];
        $stripped['implementation_identities'] = ['x' => str_repeat('0', 64)];
        $this->assertSame($bound['formula_registry_hash'], \App\Application\MarketData\Services\ReplayV2IdentityProjection::formulaRegistryIdentity($stripped));
        $this->assertSame($bound['reason_registry_hash'], \App\Application\MarketData\Services\ReplayV2IdentityProjection::reasonRegistryIdentity($stripped));
    }

    public function test_each_semantic_bound_input_equals_its_own_literal_on_the_real_path(): void
    {
        // The ten semantic inputs. The build identity is its own control below: it is a property of the executing tree, so a mutation of a
        // production file moves it as well, and it must not hide which semantic identity a mutation actually reached.
        $w = R0025SyntheticV2World::build();
        $actual = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'])['actual_context']['actual_bound_input_context'];
        $expected = $this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'];
        $unequal = [];
        foreach (array_diff(self::ELEVEN, ['executable_build_identity']) as $field) {
            if ($expected[$field] !== $actual[$field]) {
                $unequal[] = $field;
            }
        }
        $this->assertSame([], $unequal, 'bound inputs that differ from their literal expectation: '.implode(', ', $unequal));
    }

    public function test_the_publication_bound_build_identity_is_the_frozen_build_identity(): void
    {
        // Q5 = B: the expectation is the frozen literal. The actual side is the build captured with the publication; if the tree that produced
        // it is not the frozen build the candidate does not apply to it, and this control (and the diagnostic) says so.
        $w = R0025SyntheticV2World::build();
        $actual = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'])['actual_context']['actual_bound_input_context']['executable_build_identity'];
        $frozen = $this->packageJson('inputs/frozen_build_identity.json');
        $this->assertSame($frozen['build_id'], $this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context']['executable_build_identity']);
        $this->assertSame($frozen['build_id'], $actual, 'the executing build is not the build candidate-v5 was frozen from');
        // the frozen manifest is the manifest of this tree, file by file
        $root = base_path();
        $checked = 0;
        foreach (explode("
", rtrim((string) file_get_contents($this->package().'/inputs/frozen_build_manifest.txt'), "
")) as $line) {
            [$sha, $path] = explode('  ', $line, 2);
            $this->assertSame($sha, hash_file('sha256', $root.'/'.$path), $path.' differs from the frozen build manifest');
            $checked++;
        }
        $this->assertSame($frozen['file_count'], $checked);
    }

    public function test_candidate_v1_to_v4_are_preserved_byte_for_byte_and_are_different_packages(): void
    {
        $history = [R0025SyntheticV2World::PACKAGE_V1 => '05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0', R0025SyntheticV2World::PACKAGE_V2 => 'bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f',
            R0025SyntheticV2World::PACKAGE_V3 => '8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e',
            R0025SyntheticV2World::PACKAGE_V4 => 'd0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00'];
        foreach ($history as $path => $fingerprint) {
            $dir = base_path($path);
            $this->assertSame($fingerprint, $this->verifier()->fixturePackageFingerprint($dir), $path.' changed; a superseded candidate is immutable historical evidence and must not be edited');
            $this->assertSame($fingerprint, trim((string) file_get_contents(dirname($dir).'/'.basename($dir).'.fingerprint.txt')), $path);
            $manifest = json_decode((string) file_get_contents($dir.'/manifest.json'), true);
            foreach ($manifest['files_sha256'] as $relative => $sha) {
                $this->assertSame($sha, hash_file('sha256', $dir.'/'.$relative), $path.'/'.$relative);
            }
            $this->assertNotSame($fingerprint, $this->verifier()->fixturePackageFingerprint($this->package()), 'candidate-v5 must be a distinct package');
        }
        $this->assertSame('ed1d102ced928d7143f29e717f93dd1a6a8f6872406de7a103741520ca08bb42', hash_file('sha256', base_path(R0025SyntheticV2World::PACKAGE_V2).'/manifest.json'));
        $this->assertSame('266009f117ebcefdd9a0827b02f037eba415f43e655fd684fed3bcf89a1a5cf5', hash_file('sha256', base_path(R0025SyntheticV2World::PACKAGE_V3).'/manifest.json'));
        $this->assertSame('6ce59c64e458638691504ea1de2d7726e4dae15ae53cfb77d65b1e2c9c2d8c32', hash_file('sha256', base_path(R0025SyntheticV2World::PACKAGE_V4).'/manifest.json'));
        $manifest = $this->packageJson('manifest.json');
        $this->assertSame('candidate-5', $manifest['fixture_version']);
        $this->assertSame('d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00', $manifest['supersedes_candidate']['fingerprint']);
        $this->assertSame('8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e', $manifest['supersedes_candidate']['earlier']['fingerprint']);
        $this->assertSame('bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f', $manifest['supersedes_candidate']['earlier']['earlier']['fingerprint']);
        $this->assertSame('05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0', $manifest['supersedes_candidate']['earlier']['earlier']['earlier']['fingerprint']);
    }

    // ------------------------------------------------------------------------------- 8c. candidate-v3: locked assertion layers (F-MD-B18-A002-030)

    /** The locked assertion layer values, read from the authority document itself and not from the package or the verifier. */
    private function lockedAssertionLayers(): array
    {
        $doc = (string) file_get_contents(base_path('docs/market_data/development/implementation/tests/specs/Fixture_Package_Manifest_LOCKED.md'));
        $this->assertSame(1, preg_match('/## Assertion layer values\s+Allowed values:\s+((?:- `[a-z_]+`\s+)+)/', $doc, $m), 'the locked assertion layer list could not be read');
        preg_match_all('/`([a-z_]+)`/', $m[1], $values);

        return $values[1];
    }

    public function test_the_package_declares_only_the_locked_assertion_layers(): void
    {
        $locked = $this->lockedAssertionLayers();
        $this->assertSame(['row', 'run', 'hash', 'publication', 'replay'], $locked, 'the authority list changed; the verifier constant and this control must follow it');
        $this->assertSame($locked, ReplayVerificationService::LOCKED_ASSERTION_LAYERS, 'the verifier does not carry the locked vocabulary');
        $layers = $this->packageJson('manifest.json')['assertion_layers'];
        $this->assertSame(['run', 'hash', 'publication', 'replay'], $layers);
        $this->assertSame([], array_values(array_diff($layers, $locked)), 'the package declares a value outside the locked vocabulary');
        $this->assertContains('replay', $layers);
    }

    public function test_an_independent_package_with_any_other_assertion_layer_is_blocked_even_with_an_approval_supplied(): void
    {
        $w = R0025SyntheticV2World::build();
        $verifier = $this->verifier();
        foreach (['source', 'coverage', 'seal', 'pointer', 'fallback', 'lineage', 'correction', 'observation', 'read_model', 'Run', ''] as $extra) {
            $copy = $this->copyPackage();
            $manifest = json_decode((string) file_get_contents($copy.'/manifest.json'), true);
            $manifest['assertion_layers'][] = $extra;
            file_put_contents($copy.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $result = $verifier->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id'], $verifier->fixturePackageFingerprint($copy));
            $this->assertSame('BLOCKED', $result['replay_status'], '"'.$extra.'"');
            $this->assertStringStartsWith('REPLAY_INDEPENDENT_ASSERTION_LAYER_INVALID', $result['mismatch_summary'], '"'.$extra.'"');
            $this->assertStringContainsString('"'.$extra.'"', $result['mismatch_summary']);
        }
        // a value that is not a list containing 'replay' fails the manifest schema before the gate is reached
        foreach ([[], null, 'replay', [7], ['run', ['hash']]] as $shape) {
            $copy = $this->copyPackage();
            $manifest = json_decode((string) file_get_contents($copy.'/manifest.json'), true);
            $manifest['assertion_layers'] = $shape;
            file_put_contents($copy.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $thrown = $this->thrownBy(function () use ($verifier, $w, $copy) {
                $verifier->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id'], $verifier->fixturePackageFingerprint($copy));
            });
            $this->assertNotNull($thrown, json_encode($shape).' was accepted');
            $this->assertStringContainsString('REPLAY_FIXTURE_SCHEMA_MISMATCH', $thrown->getMessage(), json_encode($shape));
        }
        // 'replay' with a member that is not a string is refused by the gate under the layer reason
        foreach ([['replay', 7], ['replay', ['hash']], ['replay', null]] as $shape) {
            $copy = $this->copyPackage();
            $manifest = json_decode((string) file_get_contents($copy.'/manifest.json'), true);
            $manifest['assertion_layers'] = $shape;
            file_put_contents($copy.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $result = $verifier->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id'], $verifier->fixturePackageFingerprint($copy));
            $this->assertSame('BLOCKED', $result['replay_status'], json_encode($shape));
            $this->assertStringStartsWith('REPLAY_INDEPENDENT_ASSERTION_LAYER_INVALID', $result['mismatch_summary'], json_encode($shape));
        }
        // the layers the package declares are admitted: with the exact approval supplied the mechanism control passes
        $admitted = $verifier->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'], $verifier->fixturePackageFingerprint($this->package()));
        $this->assertSame('PASS', $admitted['replay_status']);
    }

    // ------------------------------------------------------------------------------- 8d. candidate-v3: the frozen publication manifest hash (F-MD-B18-A002-031)

    public function test_the_publication_manifest_hash_is_a_literal_derived_by_the_oracle_and_bound_to_the_frozen_manifest_payload(): void
    {
        $expected = $this->packageJson('expected/expected_replay_result.json')['expected_publication_context']['publication_manifest_hash'];
        $oracle = $this->packageJson('derivation/oracle_output.json')['publication_manifest'];
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $expected);
        $this->assertSame($oracle['hash'], $expected, 'the expectation is the oracle derivation');
        // the frozen manifest payload is the manifest-shaped object of the publication, with no operational id
        $payload = $oracle['payload'];
        $this->assertSame('IDX_REGULAR_EOD', $payload['market_scope']);
        foreach (array_keys($payload) as $member) {
            $this->assertDoesNotMatchRegularExpression('/(^|_)(run|publication|correction|config_snapshot|factor_set|source_observation)_id$/', $member, 'the frozen manifest carries an operational id: '.$member);
        }
        // the document the oracle hashes is exactly {semantic_profile, domain, payload} and its hash is the literal (recomputable with sha256sum)
        $this->assertSame($expected, hash('sha256', $oracle['preimage']));
        $decoded = json_decode($oracle['preimage'], true);
        $this->assertSame(['domain', 'payload', 'semantic_profile'], array_keys($decoded));
        $this->assertSame('publication_manifest', $decoded['domain']);
        // the frozen manifest is also an expected artifact of the package, bound by the manifest's expected-content identity
        $artifact = $this->packageJson('expected/expected_publication_manifest.json');
        $this->assertSame($expected, $artifact['publication_manifest_hash']);
        $this->assertSame($oracle['preimage'], $artifact['preimage']);
        $this->assertSame($payload, $artifact['payload']);
        $this->assertSame($expected, hash('sha256', $artifact['preimage']));
        $provenance = $this->packageJson('manifest.json')['independent_provenance'];
        $this->assertSame(hash_file('sha256', $this->package().'/expected/expected_publication_manifest.json'), $provenance['expected_content_identity']['expected/expected_publication_manifest.json']);
        // every hash member of the manifest is the literal the package already asserts elsewhere
        $bound = $this->packageJson('expected/expected_replay_result.json');
        $this->assertSame($bound['expected_artifact_context']['bars_batch_hash'], $payload['artifacts']['bars']);
        $this->assertSame($bound['expected_artifact_context']['indicators_batch_hash'], $payload['artifacts']['indicators']);
        $this->assertSame($bound['expected_artifact_context']['eligibility_batch_hash'], $payload['artifacts']['eligibility']);
        $this->assertSame($bound['expected_bound_input_context']['source_observation_manifest_hash'], $payload['observation_manifest_hash']);
        $this->assertSame($bound['expected_bound_input_context']['config_snapshot_hash'], $payload['config_content_hash']);
        $this->assertSame($bound['expected_bound_input_context']['read_model_version'], $payload['read_model_version']);
        $this->assertSame($bound['expected_publication_context']['factor_set_hash'], $payload['factor_set_hash']);
    }

    public function test_the_actual_publication_manifest_hash_equals_the_independent_literal_and_is_stable_across_layouts(): void
    {
        $literal = $this->packageJson('expected/expected_replay_result.json')['expected_publication_context']['publication_manifest_hash'];
        $a = R0025SyntheticV2World::build();
        $resultA = $this->verifier()->verifyRunAgainstFixture($a['run_id'], $this->package(), null, $a['publication_id']);
        $this->assertSame($literal, $resultA['actual_context']['actual_publication_context']['publication_manifest_hash'], 'the target manifest hash is not the independent literal');
        $this->assertContains('publication_manifest_hash', array_unique($resultA['deterministic_fields_checked']), 'the manifest hash was not compared');
        $stored = DB::connection()->table('eod_publications')->where('publication_id', $a['publication_id'])->value('publication_manifest_hash');
        $this->assertSame($literal, $stored);
        $this->freshTransaction();
        $b = R0025SyntheticV2World::build(['ticker_id' => 975777, 'preconsume' => 41, 'calendar_order' => 'desc']);
        $resultB = $this->verifier()->verifyRunAgainstFixture($b['run_id'], $this->package(), null, $b['publication_id']);
        $this->assertSame($literal, $resultB['actual_context']['actual_publication_context']['publication_manifest_hash'], 'the manifest hash moved with the allocation layout');
        $this->assertNotSame($a['publication_id'], $b['publication_id']);
    }

    public function test_a_wrong_publication_manifest_literal_is_the_only_mismatch_it_causes(): void
    {
        $w = R0025SyntheticV2World::build();
        $copy = $this->copyPackage();
        $expected = json_decode((string) file_get_contents($copy.'/expected/expected_replay_result.json'), true);
        $expected['expected_publication_context']['publication_manifest_hash'] = str_repeat('a', 64);
        file_put_contents($copy.'/expected/expected_replay_result.json', json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->reseal($copy);
        $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id'], $this->verifier()->fixturePackageFingerprint($copy));
        $this->assertSame(['publication_manifest_hash'], array_column($result['mismatches'], 'field'), 'exactly the manifest comparison must fail');
        $this->assertNotSame('PASS', $result['replay_status']);
    }

    public function test_a_missing_or_malformed_publication_manifest_expectation_blocks_an_independent_package(): void
    {
        $w = R0025SyntheticV2World::build();
        foreach (['removed', 'null', 'empty', 'short', 'upper', 'marker'] as $how) {
            $copy = $this->copyPackage();
            $expected = json_decode((string) file_get_contents($copy.'/expected/expected_replay_result.json'), true);
            $literal = $expected['expected_publication_context']['publication_manifest_hash'];
            if ($how === 'removed') {
                unset($expected['expected_publication_context']['publication_manifest_hash']);
            } else {
                $expected['expected_publication_context']['publication_manifest_hash'] = ['null' => null, 'empty' => '', 'short' => substr($literal, 0, 63), 'upper' => strtoupper($literal), 'marker' => '@TARGET:publication_id'][$how];
            }
            file_put_contents($copy.'/expected/expected_replay_result.json', json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $manifest = json_decode((string) file_get_contents($copy.'/manifest.json'), true);
            if ($how === 'marker') {
                $manifest['target_bound_fields']['expected_publication_context.publication_manifest_hash'] = 'publication_id';
                file_put_contents($copy.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            $this->reseal($copy);
            $thrown = $this->thrownBy(function () use ($w, $copy, &$result) {
                $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $copy, null, $w['publication_id'], $this->verifier()->fixturePackageFingerprint($copy));
            });
            if ($how === 'marker') {
                $this->assertNotNull($thrown, 'a target marker was accepted for the manifest hash');
                $this->assertStringContainsString('REPLAY_FIXTURE_SCHEMA_MISMATCH', $thrown->getMessage());
            } else {
                $this->assertNull($thrown, $how.': '.($thrown ? $thrown->getMessage() : ''));
                $this->assertSame('BLOCKED', $result['replay_status'], $how);
                $this->assertStringStartsWith('REPLAY_INDEPENDENT_PUBLICATION_MANIFEST_REQUIRED', $result['mismatch_summary'], $how);
            }
        }
    }

    public function test_damage_to_the_target_manifest_inputs_or_stored_hash_is_a_manifest_mismatch_against_the_independent_literal(): void
    {
        $db = DB::connection();
        $literal = $this->packageJson('expected/expected_replay_result.json')['expected_publication_context']['publication_manifest_hash'];
        $verify = function (array $w) {
            return $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
        };
        // a manifest input of the target changes after the seal: the repository re-derives a different hash, so the stored one cannot be verified
        foreach ([
            'quality_gate_state' => ['eod_runs', 'run_id', 'FAIL'], 'freshness_state' => ['eod_runs', 'run_id', 'FRESH'], 'bars_rows_written' => ['eod_runs', 'run_id', 7],
            'coverage_ratio' => ['eod_runs', 'run_id', '0.5000'],
        ] as $column => [$table, $key, $value]) {
            $this->freshTransaction();
            $w = R0025SyntheticV2World::build();
            $db->table($table)->where($key, $w['run_id'])->update([$column => $value]);
            $result = $verify($w);
            $this->assertSame('', $result['actual_context']['actual_publication_context']['publication_manifest_hash'], $column.': an unverifiable stored manifest hash must not be reported as the actual one');
            $this->assertContains('publication_manifest_hash', array_column($result['mismatches'], 'field'), $column);
        }
        // the stored hash itself is replaced
        $this->freshTransaction();
        $w = R0025SyntheticV2World::build();
        $db->table('eod_publications')->where('publication_id', $w['publication_id'])->update(['publication_manifest_hash' => str_repeat('b', 64)]);
        $result = $verify($w);
        $this->assertSame('', $result['actual_context']['actual_publication_context']['publication_manifest_hash']);
        $this->assertContains('publication_manifest_hash', array_column($result['mismatches'], 'field'));
        $this->assertNotSame($literal, str_repeat('b', 64));
    }

    public function test_the_oracle_manifest_moves_with_each_input_that_authority_makes_a_manifest_member(): void
    {
        $base = $this->packageJson('derivation/oracle_output.json')['publication_manifest']['hash'];
        $moved = function (callable $change) use ($base): array {
            $copy = $this->copyPackage();
            $change($copy);
            $out = $this->runOracle($copy);

            return [$out['publication_manifest']['hash'] !== $base, $out];
        };
        $world = function (string $copy, callable $edit) {
            $w = json_decode((string) file_get_contents($copy.'/inputs/synthetic_world.json'), true);
            $edit($w);
            file_put_contents($copy.'/inputs/synthetic_world.json', json_encode($w, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        };
        $cases = [
            'the bar close (bars artifact)' => function ($c) use ($world) { $world($c, function (&$w) { $w['bar']['close'] = 106; }); },
            'the retained listing root' => function ($c) use ($world) { $world($c, function (&$w) { $w['retained_roots']['listing'] = '5a000000-0000-4000-8000-0000000000ff'; }); },
            'the calendar session close' => function ($c) use ($world) { $world($c, function (&$w) { $w['calendar']['session_close'] = '15:50:00'; }); },
            'the trade date metadata of the frozen run clock is not a manifest member' => null,
            'the configuration content' => function ($c) { $f = $c.'/inputs/frozen_config_content.txt'; file_put_contents($f, str_replace('"min_ratio":0.98', '"min_ratio":0.97', (string) file_get_contents($f))); },
            'the config registry revision' => function ($c) { $f = $c.'/inputs/frozen_config_content.txt'; file_put_contents($f, str_replace('platform_config_registry_v2', 'platform_config_registry_v3', (string) file_get_contents($f))); },
            'the read-model version' => function ($c) { $f = $c.'/inputs/frozen_registry_literals.json'; file_put_contents($f, str_replace('market_data_read_product_v1', 'market_data_read_product_v2', (string) file_get_contents($f))); },
            'the provider response (observation manifest)' => function ($c) { $f = $c.'/inputs/provider_response.json'; file_put_contents($f, str_replace('"close":[105]', '"close":[106]', (string) file_get_contents($f))); },
        ];
        foreach ($cases as $what => $change) {
            if ($change === null) {
                continue;
            }
            [$didMove] = $moved($change);
            $this->assertTrue($didMove, $what.' did not move the expected manifest hash');
        }
        // the reason registry content is a manifest input only THROUGH the configuration snapshot content (its member moves config_content_hash, a manifest member); the frozen build identity is not one
        [$reasonMoved] = $moved(function ($c) { $f = $c.'/inputs/frozen_reason_registry.json'; $j = json_decode((string) file_get_contents($f), true); $j['entries'][0]['severity'] = 'ZZ'.$j['entries'][0]['severity']; file_put_contents($f, json_encode($j)); });
        $this->assertTrue($reasonMoved, 'the reason registry content moves the manifest through the config content hash of the snapshot member');
        [$buildMoved] = $moved(function ($c) { $f = $c.'/inputs/frozen_build_identity.json'; $j = json_decode((string) file_get_contents($f), true); $j['content_hash'] = str_repeat('a', 64); $j['build_id'] = 'sha256:'.str_repeat('a', 64); file_put_contents($f, json_encode($j)); });
        $this->assertFalse($buildMoved, 'the executable build is not a publication manifest member');
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
        $this->assertSame(trim((string) file_get_contents(dirname($this->package()).'/r0025-synthetic-v2-candidate-v5.fingerprint.txt')), $this->verifier()->fixturePackageFingerprint($this->package()));
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

    // ------------------------------------------------------------------------------- 10. candidate-v5: the reason-registry member of the configuration snapshot (MD-B04-A003)

    /** The semantic identity of a reason registry as THIS TEST computes it (own ordering and encoding): not the oracle, not the application. */
    private function testReasonIdentity(array $entries): string
    {
        $rows = [];
        foreach ($entries as $e) {
            $rows[$e['code']] = ['category' => $e['category'], 'code' => $e['code'], 'description' => $e['description'], 'is_active' => (bool) $e['is_active'], 'severity' => $e['severity']];
        }
        ksort($rows, SORT_STRING);

        return hash('sha256', json_encode(['entries' => array_values($rows), 'schema_version' => 'market-data-reason-registry/v2'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    public function test_the_frozen_snapshot_content_carries_the_independently_derived_reason_registry_member(): void
    {
        $bytes = (string) file_get_contents($this->package().'/inputs/frozen_config_content.txt');
        $config = json_decode($bytes, true);
        $this->assertSame(['reason_registry', 'resolved_config', 'semantic_bindings'], array_keys($config), 'the snapshot content has exactly the three members of the final build');
        $registry = $this->packageJson('inputs/frozen_reason_registry.json');
        $identity = $this->testReasonIdentity($registry['entries']);
        $this->assertSame(['identity_contract' => 'market-data-reason-registry/v2', 'semantic_identity' => $identity], $config['reason_registry'], 'the member is the identity contract and the semantic identity of the frozen registry content');
        $expected = $this->packageJson('expected/expected_replay_result.json');
        $this->assertSame($identity, $expected['expected_bound_input_context']['reason_registry_hash']);
        $this->assertSame(hash('sha256', $bytes), $expected['expected_bound_input_context']['config_snapshot_hash'], 'config_snapshot_hash is the SHA-256 of the three-member content');
        $this->assertSame(hash('sha256', $bytes), $expected['expected_config_identity']);
        $out = $this->packageJson('derivation/oracle_output.json');
        $this->assertTrue($out['frozen_input_facts']['config_content_matches_derivation'], 'the oracle assembled exactly the frozen bytes');
        $this->assertTrue($out['candidate_v2_identities']['configuration_snapshot_content']['frozen_file_equals_assembly']);
        $this->assertSame($identity, $out['candidate_v2_identities']['configuration_snapshot_content']['members']['reason_registry']['semantic_identity']);
        $this->assertSame(437, $registry['entry_count']);

        // against candidate-v4: the same two base members, no reason_registry, a different config identity
        $v4 = json_decode((string) file_get_contents(base_path(R0025SyntheticV2World::PACKAGE_V4).'/inputs/frozen_config_content.txt'), true);
        $v4Expected = json_decode((string) file_get_contents(base_path(R0025SyntheticV2World::PACKAGE_V4).'/expected/expected_replay_result.json'), true);
        $this->assertSame(['resolved_config', 'semantic_bindings'], array_keys($v4));
        $this->assertSame($v4['resolved_config'], $config['resolved_config']);
        $this->assertSame($v4['semantic_bindings'], $config['semantic_bindings']);
        $this->assertSame('8d0dd54f449b119c4691426612512e152c7dd22c151c358a4acb320f2160a4c0', $v4Expected['expected_bound_input_context']['config_snapshot_hash'], 'control: candidate-v4 literal');
        $this->assertNotSame($v4Expected['expected_bound_input_context']['config_snapshot_hash'], $expected['expected_bound_input_context']['config_snapshot_hash']);
        $this->assertSame($v4Expected['expected_bound_input_context']['reason_registry_hash'], $expected['expected_bound_input_context']['reason_registry_hash'], 'the registry content is the same, so its identity is the same');
        $this->assertNotSame($v4Expected['expected_bound_input_context']['executable_build_identity'], $expected['expected_bound_input_context']['executable_build_identity']);
    }

    public function test_the_actual_snapshot_row_is_the_frozen_content_and_carries_the_frozen_reason_member(): void
    {
        $w = R0025SyntheticV2World::build();
        $publication = DB::table('eod_publications')->where('publication_id', $w['publication_id'])->first();
        $row = DB::table('md_config_snapshots')->where('config_snapshot_id', $publication->config_snapshot_id)->first();
        $frozen = (string) file_get_contents($this->package().'/inputs/frozen_config_content.txt');
        $this->assertSame(hash('sha256', $frozen), $row->config_hash, 'the stored config hash is the independently derived literal');
        $this->assertSame($frozen, $row->resolved_config_json, 'the stored snapshot content is the frozen content, byte for byte');
        $this->assertSame(json_decode($frozen, true)['reason_registry'], json_decode($row->resolved_config_json, true)['reason_registry']);
    }

    public function test_a_reason_registry_content_change_moves_the_identity_the_config_hash_and_what_binds_it_and_an_order_or_allocation_change_moves_nothing(): void
    {
        $before = $this->fileHashes();
        $base = $this->packageJson('derivation/oracle_output.json');
        $baseExpected = (string) file_get_contents($this->package().'/expected/expected_replay_result.json');
        $baseManifest = (string) file_get_contents($this->package().'/expected/expected_publication_manifest.json');
        $values = static function (array $o): array {
            return ['reason' => $o['candidate_v2_identities']['reason_registry_hash']['hash'], 'config' => $o['frozen_input_facts']['config_content_sha256'], 'bars' => $o['artifacts']['bars']['sha256'],
                'indicators' => $o['artifacts']['indicators']['sha256'], 'eligibility' => $o['artifacts']['eligibility']['sha256'], 'factor_set' => $o['nested_members']['factor_set_hash'],
                'manifest' => $o['publication_manifest']['hash'], 'event_factor' => $o['candidate_v2_identities']['event_factor_hash']['hash']];
        };
        $unrelated = static function (array $o): array {
            $members = $o['nested_members'];
            unset($members['factor_set_hash']);

            return ['members' => $members, 'composites' => $o['replay_composites'], 'formula' => $o['candidate_v2_identities']['formula_registry_hash']['hash']];
        };

        // (a) order and allocation only: the entries reversed, a row id and audit columns added to every entry
        $order = $this->copyPackage();
        $file = $order.'/inputs/frozen_reason_registry.json';
        $registry = json_decode((string) file_get_contents($file), true);
        $entries = array_reverse($registry['entries']);
        foreach ($entries as $i => $entry) {
            $entries[$i] = ['id' => 9000 + $i, 'created_at' => '2031-01-01 00:00:00', 'updated_at' => '2032-02-02 00:00:00'] + $entry;
        }
        $registry['entries'] = $entries;
        file_put_contents($file, json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $a = $this->runOracleRaw($order);
        $this->assertSame(0, $a['exit'], $a['text']);
        $this->assertTrue($a['output']['frozen_input_facts']['config_content_matches_derivation'], 'an order or allocation change leaves the frozen snapshot content valid');
        $this->assertSame($values($base), $values($a['output']), 'order and allocation move no identity');
        $this->assertSame($baseExpected, (string) file_get_contents($order.'/expected/expected_replay_result.json'));
        $this->assertSame($baseManifest, (string) file_get_contents($order.'/expected/expected_publication_manifest.json'));

        // (b) a content change of one entry (the frozen snapshot content is now stale, which the oracle flags)
        $content = $this->copyPackage();
        $file = $content.'/inputs/frozen_reason_registry.json';
        $registry = json_decode((string) file_get_contents($file), true);
        $registry['entries'][0]['description'] .= ' (changed)';
        file_put_contents($file, json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $b = $this->runOracleRaw($content);
        $this->assertSame(0, $b['exit'], $b['text']);
        $this->assertFalse($b['output']['frozen_input_facts']['config_content_matches_derivation'], 'the frozen snapshot content no longer equals the assembly of the changed registry');
        $baseValues = $values($base);
        $moved = $values($b['output']);
        foreach ($baseValues as $name => $value) {
            $this->assertNotSame($value, $moved[$name], $name.' must move with the reason registry content');
        }
        $this->assertSame($unrelated($base), $unrelated($b['output']), 'identities that bind neither the registry nor the config do not move');

        // (c) the same content change with the frozen snapshot content re-authored: consistent, and the same moved values
        $out = [];
        $code = 0;
        exec('"'.PHP_BINARY.'" '.escapeshellarg($content.'/derivation/author_config_content.php').' 2>&1', $out, $code);
        $this->assertSame(0, $code, implode("\n", $out));
        $c = $this->runOracleRaw($content);
        $this->assertSame(0, $c['exit'], $c['text']);
        $this->assertTrue($c['output']['frozen_input_facts']['config_content_matches_derivation']);
        $this->assertSame($moved, $values($c['output']), 're-authoring the content does not change what the oracle derives');
        $this->assertSame($moved['reason'], json_decode((string) file_get_contents($content.'/inputs/frozen_config_content.txt'), true)['reason_registry']['semantic_identity']);
        $this->assertSame($before, $this->fileHashes(), 'the package itself is unchanged');
    }

    public function test_a_reason_registry_content_change_on_the_real_path_moves_the_actual_reason_identity_and_config_hash_while_an_audit_only_change_moves_nothing(): void
    {
        $expected = $this->packageJson('expected/expected_replay_result.json')['expected_bound_input_context'];
        $code = DB::table('eod_reason_codes')->orderBy('code')->value('code');

        // an audit-only change (no registry content member) is not a content change
        $columns = array_column(DB::select('show columns from eod_reason_codes'), 'Field');
        $audit = array_values(array_intersect(['updated_at', 'created_at'], $columns));
        $this->assertNotSame([], $audit, 'control: the registry table has an audit column to change');
        DB::table('eod_reason_codes')->where('code', $code)->update([$audit[0] => '2031-01-01 00:00:00']);
        $w = R0025SyntheticV2World::build();
        $actual = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id'])['actual_context']['actual_bound_input_context'];
        $this->assertSame($expected['reason_registry_hash'], $actual['reason_registry_hash'], 'an audit-only change does not move the reason identity');
        $this->assertSame($expected['config_snapshot_hash'], $actual['config_snapshot_hash'], 'an audit-only change does not move the config hash');

        // a content change does
        $this->freshTransaction();
        DB::table('eod_reason_codes')->where('code', $code)->update(['description' => DB::raw("CONCAT(description, ' (changed)')")]);
        $w = R0025SyntheticV2World::build();
        $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
        $actual = $result['actual_context']['actual_bound_input_context'];
        $this->assertNotSame($expected['reason_registry_hash'], $actual['reason_registry_hash'], 'a content change moves the reason identity');
        $this->assertNotSame($expected['config_snapshot_hash'], $actual['config_snapshot_hash'], 'a content change moves the config hash through the snapshot member');
        $this->assertSame($expected['formula_registry_hash'], $actual['formula_registry_hash'], 'the formula identity is a separate domain');
        $this->assertSame($expected['source_observation_manifest_hash'], $actual['source_observation_manifest_hash']);
        $this->assertGreaterThan(0, $result['mismatch_count'], 'the verifier reports the divergence from the independent literals');
    }
    // ------------------------------------------------------------------------------- 9. candidate-v5: the pre-activation freshness expectation (F-MD-B18-A002-032)
    //
    // Owner decision D-MD-B18-A002-015 and the controlled correction DOC-CHG-20261005-001: a READABLE publication whose requested trade date precedes the
    // effective activation marker is NOT_APPLICABLE. Candidate-v4 freezes the activation context and the publication facts; the independent oracle derives
    // the state from them and from the authority's ordered table. These controls prove the derivation, its sensitivity, that the oracle cannot take the value
    // from the target, and that the real target (MD-B10-A003 runtime) produces it. They prove no activated-world evaluation (FRESH, STALE, DEGRADED).

    private function runOracleRaw(string $dir): array
    {
        $out = [];
        $code = 0;
        exec('"'.PHP_BINARY.'" '.escapeshellarg($dir.'/derivation/reference_oracle.php').' 2>&1', $out, $code);

        return ['exit' => $code, 'text' => implode("\n", $out), 'output' => $code === 0 ? json_decode((string) file_get_contents($dir.'/derivation/oracle_output.json'), true) : null];
    }

    /** Edits the frozen activation context of a package copy: the world declaration and the frozen configuration together (the oracle refuses them apart). */
    private function setActivation(string $dir, ?string $marker, ?bool $gates = null, array $facts = []): void
    {
        $world = json_decode((string) file_get_contents($dir.'/inputs/synthetic_world.json'), true);
        $world['operational_activation']['operational_start_date'] = $marker;
        $world['publication_facts']['activated_operational_freshness_gates_pass'] = $gates;
        foreach ($facts as $key => $value) {
            $world['publication_facts'][$key] = $value;
        }
        file_put_contents($dir.'/inputs/synthetic_world.json', json_encode($world, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $config = (string) file_get_contents($dir.'/inputs/frozen_config_content.txt');
        $this->assertSame(1, substr_count($config, '"operational_start_date":null'), 'control: the frozen configuration carries the marker once');
        file_put_contents($dir.'/inputs/frozen_config_content.txt', str_replace('"operational_start_date":null', '"operational_start_date":'.($marker === null ? 'null' : '"'.$marker.'"'), $config));
    }

    public function test_candidate_v5_freezes_a_pre_activation_context_and_expects_the_independently_derived_not_applicable(): void
    {
        $world = $this->packageJson('inputs/synthetic_world.json');
        $this->assertNull($world['operational_activation']['operational_start_date']);
        $this->assertSame('NO_MARKER_EFFECTIVE', $world['operational_activation']['marker_state']);
        $this->assertSame('2026-03-23', $world['trade_date'], 'the requested trade date is frozen');
        $facts = $world['publication_facts'];
        $this->assertTrue($facts['requested_publication_is_returned']);
        $this->assertTrue($facts['all_readable_conditions_hold']);
        $this->assertFalse($facts['prior_date_fallback_returned']);
        $this->assertFalse($facts['activated_degraded_condition_declared']);
        $this->assertNull($facts['activated_operational_freshness_gates_pass'], 'no gate is assessed while operational freshness is not in force');
        $config = json_decode((string) file_get_contents($this->package().'/inputs/frozen_config_content.txt'), true);
        $this->assertArrayHasKey('operational_start_date', $config['resolved_config']['scope']);
        $this->assertNull($config['resolved_config']['scope']['operational_start_date'], 'the frozen configuration agrees with the world declaration');

        $out = $this->packageJson('derivation/oracle_output.json');
        $this->assertSame('NOT_APPLICABLE', $out['freshness_derivation']['state']);
        $this->assertSame(4, $out['freshness_derivation']['row'], 'row 4 of the ordered table decides: READABLE, no degraded condition, freshness not in force');
        $this->assertFalse($out['freshness_derivation']['in_force']);
        $payload = $this->packageJson('expected/expected_publication_manifest.json')['payload'];
        $this->assertSame('NOT_APPLICABLE', $payload['freshness_state']);
        $this->assertSame('READABLE', $payload['readiness_state']);
        $this->assertCount(35, $payload, 'the manifest inventory is unchanged');
        $this->assertNotSame('NOT_AVAILABLE', $payload['freshness_state']);

        $expected = $this->packageJson('expected/expected_replay_result.json');
        $this->assertSame($out['publication_manifest']['hash'], $expected['expected_publication_context']['publication_manifest_hash']);
        $this->assertSame($out['artifacts']['eligibility']['sha256'], $expected['expected_artifact_context']['eligibility_batch_hash']);
        // No earlier candidate's literal is reused: candidate-v3 (NOT_AVAILABLE) and candidate-v4 (the same freshness, the previous configuration content and build).
        $v3 = json_decode((string) file_get_contents(base_path(R0025SyntheticV2World::PACKAGE_V3).'/expected/expected_replay_result.json'), true);
        $v4 = json_decode((string) file_get_contents(base_path(R0025SyntheticV2World::PACKAGE_V4).'/expected/expected_replay_result.json'), true);
        $this->assertSame('56e44a75683bf3a1734c333c10855be3f3b123d6ba5ac2c1e192cb537a11c2f2', $v3['expected_publication_context']['publication_manifest_hash'], 'control: candidate-v3 literal');
        $this->assertSame('17f6a5177f18b8e811052aa9eb11abec62c9d0d193e10b7e198b6ed668848e55', $v4['expected_publication_context']['publication_manifest_hash'], 'control: candidate-v4 literal');
        foreach (['v3' => $v3, 'v4' => $v4] as $name => $old) {
            $this->assertNotSame($old['expected_publication_context']['publication_manifest_hash'], $expected['expected_publication_context']['publication_manifest_hash'], 'no candidate-'.$name.' manifest hash is reused');
            foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $bound) {
                $this->assertNotSame($old['expected_artifact_context'][$bound], $expected['expected_artifact_context'][$bound], 'no candidate-'.$name.' '.$bound.' is reused: every artifact row binds the config content hash');
            }
        }
        // the freshness of candidate-v5 is the freshness of candidate-v4: only the configuration content and the build moved the values
        $this->assertSame($v4['expected_final_state'], $expected['expected_final_state']);
        $this->assertSame($v4['expected_bound_input_context']['reason_registry_hash'], $expected['expected_bound_input_context']['reason_registry_hash'], 'the registry content, and so its semantic identity, did not change');
    }

    public function test_the_real_target_emits_the_corrected_freshness_and_equals_the_independent_expectation(): void
    {
        $w = R0025SyntheticV2World::build();
        $run = DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $this->assertSame('NOT_APPLICABLE', $run->freshness_state, 'the production run creator decides NOT_APPLICABLE for a pre-activation date');
        $this->assertNull($run->operational_start_date);

        $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
        $this->assertSame([], array_column($result['mismatches'], 'field'), 'expected NOT_APPLICABLE against actual NOT_APPLICABLE: no field may differ');
        $this->assertSame(0, $result['mismatch_count']);
        $expected = $this->packageJson('expected/expected_replay_result.json');
        $this->assertSame($expected['expected_artifact_context']['eligibility_batch_hash'], $result['actual_context']['actual_artifact_context']['eligibility_batch_hash']);
        $this->assertSame($expected['expected_publication_context']['publication_manifest_hash'], $result['actual_context']['actual_publication_context']['publication_manifest_hash']);
        $view = (array) (new EodPublicationRepository())->buildManifestByPublicationId($w['publication_id']);
        $this->assertSame('NOT_APPLICABLE', $view['freshness_state']);
        $this->assertSame('READABLE', $view['readiness_state']);
        // the expectation is the oracle's, written before any run, and the target was not allowed to edit it
        $this->assertSame($this->packageJson('derivation/oracle_output.json')['freshness_derivation']['state'], $run->freshness_state);
    }

    public function test_a_target_with_the_wrong_freshness_state_gives_field_specific_mismatches_and_no_build_noise(): void
    {
        $first = true;
        foreach (['NOT_AVAILABLE', 'FRESH', 'STALE', 'DEGRADED'] as $wrong) {
            if (! $first) {
                $this->freshTransaction();
            }
            $first = false;
            $w = R0025SyntheticV2World::build(['run_freshness_label' => $wrong]);
            $this->assertSame($wrong, DB::table('eod_runs')->where('run_id', $w['run_id'])->value('freshness_state'), 'control: the pipeline sealed a publication with the deliberately wrong state');
            $result = $this->verifier()->verifyRunAgainstFixture($w['run_id'], $this->package(), null, $w['publication_id']);
            // The executing build is a separately controlled bound input (test_the_publication_bound_build_identity_is_the_frozen_build_identity). It is excluded here only so that this
            // control stays red/green for freshness alone when a probe edits application code, which changes the build identity and nothing else.
            $byField = [];
            foreach ($result['mismatches'] as $mismatch) {
                $byField[$mismatch['field']] = $mismatch;
            }
            unset($byField['executable_build_identity']);
            $fields = array_keys($byField);
            sort($fields);
            $this->assertSame($this->wrongFreshnessMismatchFields(), $fields, $wrong.': exactly these fields differ, and no other');
            $expected = $this->packageJson('expected/expected_replay_result.json');
            $publication = DB::table('eod_publications')->where('publication_id', $w['publication_id'])->first();
            $this->assertSame($expected['expected_artifact_context']['eligibility_batch_hash'], $byField['eligibility_batch_hash']['expected'], $wrong.': the expectation is the independent literal');
            $this->assertSame($publication->eligibility_batch_hash, $byField['eligibility_batch_hash']['actual'], $wrong.': the actual is what the pipeline sealed for that state');
            $this->assertSame($expected['expected_publication_context']['publication_manifest_hash'], $byField['publication_manifest_hash']['expected']);
            $this->assertSame($publication->publication_manifest_hash, $byField['publication_manifest_hash']['actual']);
            $this->assertNotSame($byField['publication_manifest_hash']['expected'], $byField['publication_manifest_hash']['actual']);
            $this->assertNotSame('', (string) $byField['eligibility_batch_hash']['reason_code']);
            $this->assertNotSame('PASS', $result['replay_status']);
        }
    }

    /** The fields a deliberately wrong freshness state makes differ (the eligibility artifact hash and what binds it, and the manifest hash). */
    private function wrongFreshnessMismatchFields(): array
    {
        return ['eligibility_batch_hash', 'lineage', 'publication_manifest_hash'];
    }

    public function test_the_actual_eligibility_and_manifest_hashes_follow_the_governed_freshness_binding(): void
    {
        $hashes = [];
        $first = true;
        foreach (['NOT_APPLICABLE' => null, 'NOT_AVAILABLE' => 'NOT_AVAILABLE', 'FRESH' => 'FRESH', 'STALE' => 'STALE', 'DEGRADED' => 'DEGRADED'] as $state => $forced) {
            if (! $first) {
                $this->freshTransaction();
            }
            $first = false;
            $w = R0025SyntheticV2World::build($forced === null ? [] : ['run_freshness_label' => $forced]);
            $publication = DB::table('eod_publications')->where('publication_id', $w['publication_id'])->first();
            $hashes[$state] = ['eligibility' => $publication->eligibility_batch_hash, 'bars' => $publication->bars_batch_hash, 'indicators' => $publication->indicators_batch_hash, 'manifest' => $publication->publication_manifest_hash];
        }
        $this->assertCount(5, array_unique(array_column($hashes, 'eligibility')), 'every governed state gives its own eligibility batch hash');
        $this->assertCount(5, array_unique(array_column($hashes, 'manifest')), 'every governed state gives its own publication manifest hash');
        $this->assertCount(1, array_unique(array_column($hashes, 'bars')), 'the bars artifact does not bind freshness');
        $this->assertCount(1, array_unique(array_column($hashes, 'indicators')), 'the indicators artifact does not bind freshness');
        // NOT_APPLICABLE and NOT_AVAILABLE are distinguishable. The NOT_AVAILABLE eligibility binding of this world is what the independent oracle derives for a world whose requested
        // publication is not returned (candidate-v3 expected that state under the previous configuration content; its literals cannot equal a binding of the new content).
        $this->assertNotSame($hashes['NOT_APPLICABLE']['manifest'], $hashes['NOT_AVAILABLE']['manifest']);
        $notAvailable = $this->copyPackage();
        $this->setActivation($notAvailable, null, null, ['requested_publication_is_returned' => false]);
        $derived = $this->runOracleRaw($notAvailable);
        $this->assertSame(0, $derived['exit'], $derived['text']);
        $this->assertSame('NOT_AVAILABLE', $derived['output']['freshness_derivation']['state']);
        $this->assertSame($derived['output']['artifacts']['eligibility']['sha256'], $hashes['NOT_AVAILABLE']['eligibility'], 'the oracle-derived NOT_AVAILABLE eligibility hash is the NOT_AVAILABLE binding of this world');
        // and candidate-v5 is the NOT_APPLICABLE binding
        $expected = $this->packageJson('expected/expected_replay_result.json');
        $this->assertSame($expected['expected_artifact_context']['eligibility_batch_hash'], $hashes['NOT_APPLICABLE']['eligibility']);
        $this->assertSame($expected['expected_publication_context']['publication_manifest_hash'], $hashes['NOT_APPLICABLE']['manifest']);
    }

    public function test_the_oracle_freshness_follows_the_frozen_activation_context_and_only_through_freshness(): void
    {
        $packageBefore = $this->fileHashes();
        $base = $this->packageJson('derivation/oracle_output.json');

        // control: a marker after the requested date leaves freshness NOT_APPLICABLE (never backdated); the configuration moves, freshness does not
        $control = $this->copyPackage();
        $this->setActivation($control, '2026-04-01');
        $a = $this->runOracleRaw($control);
        $this->assertSame(0, $a['exit'], $a['text']);
        $this->assertSame('NOT_APPLICABLE', $a['output']['freshness_derivation']['state']);
        $this->assertFalse($a['output']['freshness_derivation']['in_force']);

        // the activation applicability changes: in force from 2026-03-01 with the activated gates passing
        $active = $this->copyPackage();
        $this->setActivation($active, '2026-03-01', true);
        $b = $this->runOracleRaw($active);
        $this->assertSame(0, $b['exit'], $b['text']);
        $this->assertSame('FRESH', $b['output']['freshness_derivation']['state'], 'the derivation moves with the frozen activation applicability');
        $this->assertSame(5, $b['output']['freshness_derivation']['row']);
        $this->assertTrue($b['output']['freshness_derivation']['in_force']);
        $this->assertSame('FRESH', $b['output']['publication_manifest']['payload']['freshness_state']);

        // between these two worlds (same configuration change, different freshness) exactly the freshness-dependent values differ
        $this->assertNotSame($a['output']['artifacts']['eligibility']['sha256'], $b['output']['artifacts']['eligibility']['sha256'], 'the eligibility batch hash moves with the governed freshness binding');
        $this->assertNotSame($a['output']['publication_manifest']['hash'], $b['output']['publication_manifest']['hash'], 'the publication manifest hash moves accordingly');
        // the frozen configuration content is itself a bound row value, so a changed marker moves it: isolate freshness with two worlds that share one configuration
        $degraded = $this->copyPackage();
        $this->setActivation($degraded, '2026-03-01', true, ['activated_degraded_condition_declared' => true]);
        $d = $this->runOracleRaw($degraded);
        $this->assertSame(0, $d['exit'], $d['text']);
        $this->assertSame('DEGRADED', $d['output']['freshness_derivation']['state']);
        $this->assertSame(3, $d['output']['freshness_derivation']['row']);
        $this->assertNotSame($b['output']['artifacts']['eligibility']['sha256'], $d['output']['artifacts']['eligibility']['sha256'], 'same configuration, other freshness: the eligibility hash moves');
        $this->assertNotSame($b['output']['publication_manifest']['hash'], $d['output']['publication_manifest']['hash'], 'same configuration, other freshness: the manifest hash moves');
        $this->assertSame($b['output']['artifacts']['bars']['sha256'], $d['output']['artifacts']['bars']['sha256'], 'bars do not bind freshness');
        $this->assertSame($b['output']['artifacts']['indicators']['sha256'], $d['output']['artifacts']['indicators']['sha256'], 'indicators do not bind freshness');
        $this->assertSame($b['output']['nested_members'], $d['output']['nested_members'], 'no nested identity binds freshness');
        $this->assertNotSame($base['publication_manifest']['hash'], $b['output']['publication_manifest']['hash']);

        // the boundary and the unrepresented combinations are refused, never guessed
        foreach (['2026-03-23' => 'the marker date itself is in force and no gate is declared', '2026-03-01' => 'in force with no gate result declared'] as $marker => $why) {
            $copy = $this->copyPackage();
            $this->setActivation($copy, $marker, null);
            $refused = $this->runOracleRaw($copy);
            $this->assertSame(3, $refused['exit'], $why.': '.$refused['text']);
            $this->assertStringContainsString('fail-safe default', $refused['text']);
        }
        $fallback = $this->copyPackage();
        $this->setActivation($fallback, null, null, ['prior_date_fallback_returned' => true]);
        $this->assertSame(3, $this->runOracleRaw($fallback)['exit'], 'a prior-date fallback decides STALE or DEGRADED by facts the world does not declare');
        $disagree = $this->copyPackage();
        $world = json_decode((string) file_get_contents($disagree.'/inputs/synthetic_world.json'), true);
        $world['operational_activation']['operational_start_date'] = '2026-04-01';
        file_put_contents($disagree.'/inputs/synthetic_world.json', json_encode($world, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(2, $this->runOracleRaw($disagree)['exit'], 'the world and the frozen configuration must agree on the marker');

        $this->assertSame($packageBefore, $this->fileHashes(), 'every probe ran on a byte copy');
    }

    public function test_not_applicable_and_not_available_are_distinguishable_in_the_oracle_and_the_old_literal_is_the_not_available_derivation(): void
    {
        $notReadable = $this->copyPackage();
        $this->setActivation($notReadable, null, null, ['requested_publication_is_returned' => false]);
        $r = $this->runOracleRaw($notReadable);
        $this->assertSame(0, $r['exit'], $r['text']);
        $this->assertSame('NOT_AVAILABLE', $r['output']['freshness_derivation']['state']);
        $this->assertSame(1, $r['output']['freshness_derivation']['row']);
        $this->assertSame('NOT_READABLE', $r['output']['publication_manifest']['payload']['readiness_state']);
        $base = $this->packageJson('derivation/oracle_output.json');
        $this->assertNotSame($base['artifacts']['eligibility']['sha256'], $r['output']['artifacts']['eligibility']['sha256']);
        $this->assertNotSame($base['publication_manifest']['hash'], $r['output']['publication_manifest']['hash']);
        // the eligibility row of that world is candidate-v3's row (which expected NOT_AVAILABLE) in every token but the config content hash, which moved with the configuration content
        $v3 = json_decode((string) file_get_contents(base_path(R0025SyntheticV2World::PACKAGE_V3).'/derivation/oracle_output.json'), true);
        $old = $v3['artifacts']['eligibility']['row_tokens'][0];
        $new = $r['output']['artifacts']['eligibility']['row_tokens'][0];
        $this->assertSame(['config_content_hash'], array_keys(array_diff_assoc($new, $old)), 'only the config content hash token differs from the candidate-v3 eligibility row');
        $this->assertSame($old['freshness_state'], $new['freshness_state']);
    }

    public function test_the_oracle_cannot_obtain_the_freshness_state_from_the_target(): void
    {
        $source = (string) file_get_contents($this->package().'/derivation/reference_oracle.php');
        $code = preg_replace('~^\s*//.*$~m', '', preg_replace('~/\*.*?\*/~s', '', $source));
        foreach (['normalizeFreshnessState', 'runLabelFor', 'FreshnessState', 'eod_runs', 'eod_publications', 'buildExpectedReplayResultFromActual', 'verifyRunAgainstFixture', 'storage_path', 'base_path', 'config(', 'actual_context', 'actual_publication_context'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $code, 'the oracle must not reference '.$forbidden);
        }
        $this->assertSame(0, preg_match("/@TARGET:[a-z_]*manifest/", (string) file_get_contents($this->package().'/expected/expected_replay_result.json')), 'the manifest hash is never target-bound');
        // the only assignments of a freshness value derive it from the table
        $this->assertSame(2, preg_match_all("/'freshness_state' => \\\$freshness\['state'\]/", $code), 'the eligibility row and the manifest member take the derived state');
        $this->assertSame(0, preg_match("/'freshness_state' => '[A-Z_]{4,}'/", $code), 'no freshness literal may be written into a row or the manifest');
        $this->assertSame(1, preg_match('/function deriveFreshness.*?\n}\n/s', $code, $fn), 'the derivation is one function');
        $this->assertSame(4, preg_match_all("/'state' => '(NOT_AVAILABLE|NOT_APPLICABLE|DEGRADED|FRESH)'/", $code), 'state literals exist only as the rows of the ordered table');
        $this->assertSame(4, preg_match_all("/'state' => '(NOT_AVAILABLE|NOT_APPLICABLE|DEGRADED|FRESH)'/", $fn[0]), 'and all of them are inside the derivation function');
        // the oracle runs from a directory outside the repository, with the frozen inputs alone, and reproduces the package
        $outside = sys_get_temp_dir().DIRECTORY_SEPARATOR.'r0025-v4-oracle-'.bin2hex(random_bytes(4));
        $this->copies[] = $outside;
        $this->copyTree($this->package(), $outside);
        $r = $this->runOracleRaw($outside);
        $this->assertSame(0, $r['exit'], $r['text']);
        $this->assertSame(hash_file('sha256', $this->package().'/derivation/oracle_output.json'), hash_file('sha256', $outside.'/derivation/oracle_output.json'));
        $this->assertSame(hash_file('sha256', $this->package().'/expected/expected_replay_result.json'), hash_file('sha256', $outside.'/expected/expected_replay_result.json'));
    }

    public function test_the_publication_version_representation_and_the_canonicalization_version_are_frozen_inputs_with_a_basis(): void
    {
        $declaration = $this->packageJson('inputs/frozen_manifest_member_representation.json')['members']['publication_version'];
        $this->assertSame('TEXT_BASE10', $declaration['representation']);
        $producer = $declaration['producer_source'];
        // the declaration agrees with the frozen build manifest
        $line = null;
        foreach (explode("\n", (string) file_get_contents($this->package().'/inputs/frozen_build_manifest.txt')) as $l) {
            if (substr($l, 66) === $producer['path']) {
                $line = $l;
            }
        }
        $this->assertNotNull($line, 'the producer file is part of the frozen build');
        $this->assertSame($producer['sha256_in_frozen_build_manifest'], substr($line, 0, 64));
        // and with the producer source of this tree (the tree the candidate is frozen to)
        $sourceFile = base_path($producer['path']);
        $this->assertSame($producer['sha256_in_frozen_build_manifest'], hash_file('sha256', $sourceFile), 'the producer is not the frozen build');
        $lines = explode("\n", (string) file_get_contents($sourceFile));
        $this->assertSame($producer['text'], trim($lines[$producer['line'] - 1]), 'the recorded rendering line is the producer line');
        $this->assertStringStartsWith("'publication_version' => (string)", $producer['text']);
        $this->assertSame('1', $this->packageJson('expected/expected_publication_manifest.json')['payload']['publication_version']);

        // the declaration is load-bearing: the other representation gives another preimage; a declaration that disagrees with the frozen build is refused
        $integer = $this->copyPackage();
        $decl = json_decode((string) file_get_contents($integer.'/inputs/frozen_manifest_member_representation.json'), true);
        $decl['members']['publication_version']['representation'] = 'INTEGER';
        file_put_contents($integer.'/inputs/frozen_manifest_member_representation.json', json_encode($decl, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $r = $this->runOracleRaw($integer);
        $this->assertSame(0, $r['exit'], $r['text']);
        $this->assertSame(1, $r['output']['publication_manifest']['payload']['publication_version']);
        $this->assertNotSame($this->packageJson('derivation/oracle_output.json')['publication_manifest']['hash'], $r['output']['publication_manifest']['hash']);
        $wrong = $this->copyPackage();
        $decl = json_decode((string) file_get_contents($wrong.'/inputs/frozen_manifest_member_representation.json'), true);
        $decl['members']['publication_version']['producer_source']['sha256_in_frozen_build_manifest'] = str_repeat('0', 64);
        file_put_contents($wrong.'/inputs/frozen_manifest_member_representation.json', json_encode($decl, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(2, $this->runOracleRaw($wrong)['exit']);

        // canonicalization_version comes from the frozen configuration
        $config = $this->copyPackage();
        $f = $config.'/inputs/frozen_config_content.txt';
        $this->assertSame(1, substr_count((string) file_get_contents($f), '"canonicalization_version":"idx_regular_raw_v2"'));
        file_put_contents($f, str_replace('"canonicalization_version":"idx_regular_raw_v2"', '"canonicalization_version":"idx_regular_raw_v3"', (string) file_get_contents($f)));
        $c = $this->runOracleRaw($config);
        $this->assertSame(0, $c['exit'], $c['text']);
        $this->assertSame('idx_regular_raw_v3', $c['output']['publication_manifest']['payload']['canonicalization_version']);
        $this->assertNotSame($this->packageJson('derivation/oracle_output.json')['artifacts']['bars']['sha256'], $c['output']['artifacts']['bars']['sha256'], 'the bars row carries the frozen canonicalization version');
        $absent = $this->copyPackage();
        $f = $absent.'/inputs/frozen_config_content.txt';
        file_put_contents($f, str_replace('"canonicalization_version":"idx_regular_raw_v2",', '', (string) file_get_contents($f)));
        $this->assertSame(2, $this->runOracleRaw($absent)['exit'], 'a frozen configuration without the version is refused, not defaulted');
        $this->assertSame(0, preg_match("/'idx_regular_raw_v\d'/", (string) file_get_contents($this->package().'/derivation/reference_oracle.php')), 'no canonicalization version literal in the oracle');
    }
}
