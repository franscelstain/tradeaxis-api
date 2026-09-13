<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` -- the replay comparison predicates that were `PARTIAL` because the existing guards
 * exhibited a case rather than establishing a prohibition:
 *
 *  - `MD-S050-R0029` PASS requires *all* of values, null reasons, states, lineages, content hashes,
 *    manifest and seal to match. `ReplayVerificationServiceTest` proves a matching fixture yields
 *    PASS and that two particular divergences yield MISMATCH. Neither establishes that each named
 *    class of assertion is load-bearing, which is what "all ... match" claims.
 *  - `MD-S036-R0007` / `MD-S036-R0031` evidence and replay must compare request mode, import
 *    status, promote status, source mode, pointer switch status and publication state, and an
 *    unexpected import promotion must be a mismatch rather than a silent pass.
 *  - `MD-S040-R0080` no replay flow may treat `manual_file` as readable merely because import
 *    succeeded.
 *  - `MD-S085-R0452` replay reason codes are proof outcomes; they do not create readable
 *    publications.
 *
 * The method is one baseline fixture that matches, and then one perturbation per named class of
 * assertion, each asserted to turn the verdict from PASS into a reason-coded mismatch. A field that
 * is not compared shows up here as a perturbation that still passes.
 *
 * Writing this surfaced a real gap rather than only a missing test: `REPLAY_IMPORT_PROMOTE_MISMATCH`
 * was a registered reason code with no code path able to emit it, because `compareField()` skips a
 * null expectation and the fixture schema leaves `import_status`, `promote_status`, `promoted` and
 * `pointer_switched` optional. A fixture declaring `request_mode: import_only` therefore had its
 * promotion state checked by nothing. `appendImportPromotionPolicyMismatches()` was added for it.
 */
class B18ReplayComparisonExhaustivenessTest extends TestCase
{
    private const RUN_ID = 91;

    private const TRADE_DATE = '2026-03-20';

    private const PUBLICATION_ID = 44;

    /** @var array<string,mixed>|null what upsertMetric was actually given */
    private $persistedMetric = null;

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * One perturbation per class of assertion `MD-S050-R0029` names, plus the import/promote and
     * source-mode comparisons `MD-S036` names. The key is the class being probed; the value is what
     * to change in the fixture expectation.
     *
     * @return array<string,array{0:array<string,mixed>,1:string}>
     */
    public function perturbations(): array
    {
        return [
            // "all expected values ... match"
            'value: an artifact row count' => [['bars_rows_written' => 9], 'bars_rows_written'],
            // "... null reasons ..."
            'null reason: the final reason code' => [['final_reason_code' => 'RUN_PARTIAL_DATA'], 'final_reason_code'],
            // "... states ..."
            'state: publishability' => [['publishability_state' => 'NOT_READABLE'], 'publishability_state'],
            'state: terminal status' => [['terminal_status' => 'HELD'], 'terminal_status'],
            'state: coverage gate' => [['coverage_gate_state' => 'FAIL'], 'coverage_gate_state'],
            // "... lineages ..."
            'lineage: the publishing run' => [['publication_run_id' => 92], 'publication_run_id'],
            'lineage: the publication version' => [['publication_version' => 5], 'publication_version'],
            // "... content hashes ..."
            'content hash: bars batch' => [['bars_batch_hash' => 'A2'], 'bars_batch_hash'],
            'content hash: indicators batch' => [['indicators_batch_hash' => 'B2'], 'indicators_batch_hash'],
            'content hash: eligibility batch' => [['eligibility_batch_hash' => 'C2'], 'eligibility_batch_hash'],
            // "... and seal assertions match"
            'seal: the seal state' => [['seal_state' => 'UNSEALED'], 'seal_state'],
            // MD-S036: request mode, source mode, publication state
            'MD-S036: request mode' => [['request_mode' => 'correction'], 'request_mode'],
            'MD-S036: source mode' => [['source_mode' => 'api', 'source_identity' => 'mode=api'], 'source_mode'],
            'MD-S036: publication identity' => [['publication_id' => 45], 'publication_id'],
        ];
    }

    /**
     * The control. Without it every perturbation below could be failing for a reason that has
     * nothing to do with the field it changed.
     */
    public function test_the_unperturbed_fixture_passes(): void
    {
        $result = $this->verify();

        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('PASS', $result['replay_status']);
        $this->assertSame(0, $result['mismatch_count']);
    }

    /**
     * `MD-S050-R0029` -- each named class of assertion is load-bearing for PASS.
     *
     * @dataProvider perturbations
     *
     * @param array<string,mixed> $override
     */
    public function test_a_divergence_in_any_named_assertion_class_denies_pass(array $override, string $field): void
    {
        $result = $this->verify($override);

        $this->assertNotSame('PASS', $result['replay_status'],
            'a fixture diverging on '.$field.' still passed, so that assertion is not compared');
        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertGreaterThan(0, $result['mismatch_count']);
        $this->assertNotSame([], $result['mismatch_reason_codes'],
            'a mismatch must carry a reason code, or an operator cannot tell which assertion failed');
    }

    /**
     * The perturbation table must cover every class `MD-S050-R0029` lists, or the test above proves
     * exhaustiveness over whatever subset happened to be written down.
     */
    public function test_the_perturbation_table_covers_every_class_the_contract_names(): void
    {
        $covered = [];
        foreach (array_keys($this->perturbations()) as $label) {
            $covered[explode(':', $label)[0]] = true;
        }

        foreach (['value', 'null reason', 'state', 'lineage', 'content hash', 'seal'] as $class) {
            $this->assertArrayHasKey($class, $covered,
                'MD-S050-R0029 names "'.$class.'" among the things a PASS requires to match, and no '
                    .'perturbation probes it');
        }
    }

    /**
     * `MD-S050-R0029` -- the manifest half.
     *
     * A fixture whose manifest declares a file it does not carry is refused outright rather than
     * compared, so a PASS can never rest on a manifest that does not describe the fixture.
     */
    public function test_a_manifest_declaring_a_file_the_fixture_does_not_carry_is_refused(): void
    {
        $fixtureDir = $this->fixtureDir();
        $manifestPath = $fixtureDir.'/manifest.json';
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $manifest['files'][] = 'expected/expected_absent_file.json';
        file_put_contents($manifestPath, json_encode($manifest));

        $this->expectException(\RuntimeException::class);
        $this->service($this->mocks(false))->verifyRunAgainstFixture(self::RUN_ID, $fixtureDir);
    }

    /**
     * `MD-S036-R0031` -- unexpected import promotion is a mismatch, not a silent pass.
     *
     * The fixture declares `request_mode: import_only`. The run behind it promoted: SUCCESS,
     * READABLE, holding the current publication. Before `appendImportPromotionPolicyMismatches()`
     * this combination produced a mismatch on the states it happened to declare and nothing at all
     * about the promotion itself, and `REPLAY_IMPORT_PROMOTE_MISMATCH` had no path that could emit
     * it despite being a registered reason code.
     */
    public function test_an_import_only_expectation_is_not_satisfied_by_a_run_that_promoted(): void
    {
        $result = $this->verify(['request_mode' => 'import_only'], ['request_mode' => 'import_only']);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertContains('REPLAY_IMPORT_PROMOTE_MISMATCH', $result['mismatch_reason_codes'],
            'an import-only expectation satisfied by a promoted run must name the promotion, not '
                .'merely differ on some downstream state');

        $fields = array_column($result['mismatches'], 'field');
        $this->assertContains('import_only_promote_status_policy', $fields);
        $this->assertContains('import_only_pointer_switch_policy', $fields);
    }

    /**
     * The control for the rule above: the same import-only expectation against a run that did not
     * promote carries no promotion mismatch. Without this the rule would be indistinguishable from
     * one that fails every import-only fixture.
     */
    public function test_an_import_only_expectation_against_an_unpromoted_run_raises_no_promotion_mismatch(): void
    {
        $result = $this->verify(
            [
                'request_mode' => 'import_only',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'publication_is_current' => false,
                'final_reason_code' => 'RUN_IMPORT_ONLY_NOT_PROMOTED',
            ],
            [
                'request_mode' => 'import_only',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'final_reason_code' => 'RUN_IMPORT_ONLY_NOT_PROMOTED',
            ],
            ['is_current' => 0],
            false
        );

        $this->assertNotContains('REPLAY_IMPORT_PROMOTE_MISMATCH', $result['mismatch_reason_codes'],
            'an import-only run that did not promote must not be accused of promoting');
    }

    /**
     * `MD-S040-R0080` and `MD-S085-R0452` -- a successful `manual_file` import does not become
     * readable, and no replay reason code makes it so.
     *
     * The run imported successfully and claims READABLE while its coverage gate did not pass. The
     * replay must refuse that rather than record a proof outcome that reads as a publication.
     */
    public function test_a_manual_file_run_readable_without_a_coverage_pass_is_a_mismatch(): void
    {
        $result = $this->verify(
            ['coverage_gate_state' => 'FAIL', 'coverage_reason_code' => 'RUN_COVERAGE_LOW'],
            ['coverage_gate_state' => 'FAIL']
        );

        $fields = array_column($result['mismatches'], 'field');
        $this->assertContains('manual_file_readable_coverage_policy', $fields,
            'import success is not readability; a manual_file run claiming READABLE without a '
                .'coverage PASS must be refused');
        $this->assertSame('FAIL', $result['replay_status']);

        // MD-S085-R0452: the outcome is a proof verdict, not a publication event. The publication
        // repository mock carries no expectations at all, so any seal or pointer move the replay
        // attempted would have failed this test before reaching here; the reason code below is
        // therefore a comparison outcome and nothing else.
        $this->assertContains($result['replay_status'], ['FAIL', 'BLOCKED'],
            'a replay reason code records a verdict; it never upgrades the run it judged');
        $this->assertSame('MISMATCH', $result['comparison_result']);
    }


    /**
     * `MD-S050-R0016` and `MD-S082-R0015` -- a missing bound input is `BLOCKED`, and a
     * `CONFIG_UNBOUND` publication is `BLOCKED` rather than `PASS`.
     *
     * The run and the publication both carry no configuration snapshot, so the configuration the
     * run executed under cannot be recovered and reproducibility cannot be evidenced. Everything
     * else about the fixture still matches. A comparison that reported PASS here would be saying
     * the run reproduces, on the strength of inputs it never bound.
     */
    public function test_a_publication_with_no_configuration_snapshot_is_blocked_rather_than_passed(): void
    {
        $result = $this->verify([], ['config_snapshot_id' => null], ['config_snapshot_id' => null]);

        $this->assertSame('BLOCKED', $result['replay_status'],
            'a missing bound input is BLOCKED; FAIL would say the comparison ran and disagreed, '
                .'and PASS would say it ran and agreed');
        $this->assertSame('NOT_ADMISSIBLE', $result['comparison_result']);
        $this->assertStringContainsString('REPLAY_CONFIG_UNBOUND', (string) $result['mismatch_summary'],
            'the block must name the input that was absent, or CONFIG_UNBOUND is cited without '
                .'being stated');
    }

    /**
     * The blocked outcome is recorded rather than discarded. `MD-S050-R0016` distinguishes BLOCKED
     * from a failure precisely so the corpus keeps the fact that the comparison never ran; a block
     * that vanished would leave the predicate looking untested rather than unavailable.
     */
    public function test_a_blocked_replay_is_recorded_with_its_admission_state(): void
    {
        $this->verify([], ['config_snapshot_id' => null], ['config_snapshot_id' => null]);

        $this->assertNotNull($this->persistedMetric, 'a blocked replay must still be persisted');
        $this->assertSame('NOT_ADMISSIBLE', $this->persistedMetric['admission_state']);
        $this->assertSame('BLOCKED', $this->persistedMetric['replay_status']);
        $this->assertSame('PUBLICATION_EXACT', $this->persistedMetric['replay_mode'],
            'and it still records which mode produced it, so a blocked result cannot become the '
                .'unmoded row MD-S050-R0035 forbids');
    }

    /**
     * The other half of `MD-S050-R0016`: a missing input is not permission to go and look at
     * current state. The publication is resolved through the explicit fixture selector, and the
     * evidence repository is asserted never to be asked for a current-pointer resolution -- so the
     * block is a refusal to proceed rather than a fallback.
     */
    public function test_a_blocked_replay_does_not_fall_back_to_the_current_publication(): void
    {
        $selectors = [];
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->andReturn((object) array_merge($this->runRow(), ['config_snapshot_id' => null]));
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')
            ->andReturnUsing(function ($selector) use (&$selectors) {
                $selectors[] = $selector['type'] ?? 'unknown';

                return (object) array_merge($this->publicationRow(), ['config_snapshot_id' => null]);
            });
        $evidence->shouldReceive('dominantReasonCodes')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->andReturn([]);
        $replays->shouldReceive('nextReplayId')->andReturn(3102);
        $replays->shouldReceive('upsertMetric')->andReturnNull();
        $replays->shouldReceive('replaceReasonCodeCounts')->andReturnNull();

        $result = (new ReplayVerificationService($evidence, $publications, $replays))
            ->verifyRunAgainstFixture(self::RUN_ID, $this->fixtureDir());

        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertSame(['replay_fixture_explicit_publication'], array_values(array_unique($selectors)),
            'the only publication a blocked replay may resolve is the one the fixture names; '
                .'reaching for the current pointer would be answering from latest state');
    }

    // ---- MD-S050-R0002: publication replay uses exactly the inputs frozen with the publication ---

    /**
     * The frozen inputs, read from the contract sentence itself so the table cannot quietly cover a
     * smaller set than `MD-S050` names.
     *
     * @return array<string,string> contract phrase => the bound-input field that carries it
     */
    private function frozenInputMap(): array
    {
        return [
            'observations' => 'source_observation_manifest_hash',
            'temporal master revisions' => 'temporal_identity_hash',
            'calendar/status revisions' => 'calendar_status_hash',
            'event/factor revisions' => 'event_factor_hash',
            'configuration snapshot' => 'config_snapshot_hash',
            'formulas/registries' => 'formula_registry_hash',
            'build/adapter versions' => 'executable_build_identity',
            'serialization rules' => 'serialization_version',
            'publication manifest' => 'fixture_manifest_hash',
        ];
    }

    /**
     * `MD-S050-R0002` -- the map and the contract must name the same frozen inputs.
     *
     * The sentence is parsed rather than paraphrased: an input added to `MD-S050` with no field
     * behind it fails here instead of leaving the perturbation table quietly short.
     */
    public function test_the_frozen_input_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md';
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);

        $this->assertSame(1, preg_match(
            '/Reproduces or verifies one historical immutable publication using exactly the (.+?) frozen with it\./s',
            $source,
            $match
        ), 'the MD-S050 publication-replay sentence moved; re-read it rather than weakening this map');

        $phrases = preg_split('/,\s*and\s+|,\s*/', trim($match[1]));
        $phrases = array_values(array_filter(array_map('trim', $phrases)));
        $mapped = array_keys($this->frozenInputMap());
        sort($phrases);
        sort($mapped);

        $this->assertSame($phrases, $mapped,
            'MD-S050 and the frozen-input map disagree about which inputs publication replay must '
                .'use exactly');
    }

    /**
     * The control: a fixture declaring every frozen input at the value the replay actually resolved
     * still matches. Without it the perturbations below would pass equally well against a
     * comparison that rejects any fixture carrying a bound-input block at all.
     */
    public function test_a_fixture_declaring_every_frozen_input_correctly_still_passes(): void
    {
        $result = $this->verify(['expected_bound_input_context' => $this->resolvedBoundInputs()]);

        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('PASS', $result['replay_status']);
        $this->assertSame(0, $result['mismatch_count']);
    }

    /**
     * `MD-S050-R0002` -- every frozen input is load-bearing.
     *
     * One fixture declaring all eleven bound inputs, with one of them changed. Publication replay
     * runs against the inputs frozen with the publication or it is not publication replay, so a
     * divergence in any of them must deny PASS and name the input that moved.
     *
     * Before `appendBoundInput` comparison existed these identities were written into the stored
     * result and never checked, so a replay resolving today's indicator registry or today's build
     * reported MATCH.
     *
     * @dataProvider frozenInputFields
     */
    public function test_a_divergence_in_any_frozen_input_denies_pass(string $field): void
    {
        $bound = $this->resolvedBoundInputs();
        $this->assertArrayHasKey($field, $bound);
        $bound[$field] = 'diverged-'.$field;

        $result = $this->verify(['expected_bound_input_context' => $bound]);

        $this->assertNotSame('PASS', $result['replay_status'],
            'a replay running against a different '.$field.' than the fixture records as frozen '
                .'with the publication still passed, so that input is not compared');
        $this->assertSame('MISMATCH', $result['comparison_result']);
        // The reason code comes from the registry as it stands: config identity has its own, the
        // rest fall through to REPLAY_NON_DETERMINISTIC_OUTPUT, which the registry defines as a
        // deterministic-field mismatch with no more specific code. What identifies the input is the
        // mismatch field, asserted below. A bespoke code would need a governed revision of the
        // STRATEGY reason-code registry and is deliberately not assumed here.
        $this->assertNotSame([], $result['mismatch_reason_codes'],
            'a frozen-input divergence must carry a reason code');
        $this->assertContains('bound_input_'.$field, array_column($result['mismatches'], 'field'),
            'the mismatch must name which frozen input moved');
    }

    /** @return array<int,array{0:string}> */
    public function frozenInputFields(): array
    {
        $fields = [
            'source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash',
            'calendar_status_hash', 'event_factor_hash', 'config_snapshot_hash',
            'formula_registry_hash', 'reason_registry_hash', 'read_model_version',
            'serialization_version', 'executable_build_identity',
        ];

        return array_map(static function (string $f) { return [$f]; }, $fields);
    }

    /**
     * Every field the provider names must exist in the block the service builds, or the test above
     * would silently skip an input by misspelling it.
     */
    public function test_every_named_frozen_input_field_exists_in_the_resolved_block(): void
    {
        $bound = $this->resolvedBoundInputs();

        foreach ($this->frozenInputFields() as [$field]) {
            $this->assertArrayHasKey($field, $bound, $field.' is not a bound input the service resolves');
        }
        $this->assertCount(count($this->frozenInputFields()), $bound,
            'the service resolves a bound input the perturbation table does not probe');
    }

    /**
     * The frozen inputs the replay actually resolved, taken from the result it persisted.
     *
     * This is the fixture's job in production: it records what was frozen with the publication. The
     * proof here is not that these values match -- it is that changing one denies PASS.
     *
     * @return array<string,string>
     */
    private function resolvedBoundInputs(): array
    {
        $this->verify();
        $this->assertNotNull($this->persistedMetric, 'the baseline run persisted no metric');

        $bound = [];
        foreach ($this->frozenInputFields() as [$field]) {
            $bound[$field] = $this->persistedMetric[$field];
        }

        return $bound;
    }

    // ---- MD-S003-R0003: exact publication verification covers every named item ------------------

    /**
     * `MD-S003` "verify frozen observations, temporal revisions, config, factors, formulas,
     * artifacts, hashes, manifest, seal, reasons, and terminal state" -- each item mapped to the
     * guard in this class that makes it load-bearing.
     *
     * @return array<string,string> contract item => the test that would fail if it stopped being verified
     */
    private function exactVerificationMap(): array
    {
        return [
            'frozen observations' => 'test_a_divergence_in_any_frozen_input_denies_pass',
            'temporal revisions' => 'test_a_divergence_in_any_frozen_input_denies_pass',
            'config' => 'test_a_divergence_in_any_frozen_input_denies_pass',
            'factors' => 'test_a_divergence_in_any_frozen_input_denies_pass',
            'formulas' => 'test_a_divergence_in_any_frozen_input_denies_pass',
            'artifacts' => 'test_a_divergence_in_any_named_assertion_class_denies_pass',
            'hashes' => 'test_a_divergence_in_any_named_assertion_class_denies_pass',
            'manifest' => 'test_a_manifest_declaring_a_file_the_fixture_does_not_carry_is_refused',
            'seal' => 'test_a_divergence_in_any_named_assertion_class_denies_pass',
            'reasons' => 'test_a_divergence_in_any_named_assertion_class_denies_pass',
            'terminal state' => 'test_a_divergence_in_any_named_assertion_class_denies_pass',
        ];
    }

    /**
     * `MD-S003-R0003` -- the map and the contract list must name the same items.
     *
     * Parsed from the contract rather than transcribed, so an item added to the exact-verification
     * line with nothing verifying it fails here instead of leaving the claim quietly short.
     */
    public function test_the_exact_verification_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md';
        $this->assertFileExists($path);

        $this->assertSame(1, preg_match(
            '/^- verify (.+?);$/m',
            (string) file_get_contents($path),
            $match
        ), 'the MD-S003 exact-verification line moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*and\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->exactVerificationMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S003 and the exact-verification map disagree about what publication verification '
                .'must check');
    }

    /**
     * Every guard the map names must exist in this class. Without this the map is a list of
     * intentions that a rename would silently empty.
     */
    public function test_every_guard_the_exact_verification_map_names_exists(): void
    {
        $source = (string) file_get_contents(__FILE__);
        $missing = [];

        foreach ($this->exactVerificationMap() as $item => $method) {
            if (strpos($source, 'function '.$method.'(') === false) {
                $missing[] = $item.' -> '.$method;
            }
        }

        $this->assertSame([], $missing, 'these contract items name a guard that no longer exists');
    }
    // ---- fixture construction -------------------------------------------------------------

    /**
     * Runs one verification. `$expected` perturbs the fixture expectation, `$run` perturbs the run
     * the evidence repository returns, `$publication` perturbs the resolved publication.
     *
     * @param array<string,mixed> $expected
     * @param array<string,mixed> $run
     * @param array<string,mixed> $publication
     * @return array<string,mixed>
     */
    private function verify(array $expected = [], array $run = [], array $publication = [], bool $pinPublication = true): array
    {
        $fixtureDir = $this->fixtureDir($expected, $pinPublication);

        return $this->service($this->mocks(true, $run, $publication))
            ->verifyRunAgainstFixture(self::RUN_ID, $fixtureDir);
    }

    /** @param array{0:object,1:object,2:object} $mocks */
    private function service(array $mocks): ReplayVerificationService
    {
        return new ReplayVerificationService($mocks[0], $mocks[1], $mocks[2]);
    }

    /**
     * @param array<string,mixed> $runOverride
     * @param array<string,mixed> $publicationOverride
     * @return array{0:object,1:object,2:object}
     */
    private function mocks(bool $expectWrite, array $runOverride = [], array $publicationOverride = []): array
    {
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->andReturn((object) array_merge($this->runRow(), $runOverride));
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')
            ->andReturn((object) array_merge($this->publicationRow(), $publicationOverride));
        $evidence->shouldReceive('dominantReasonCodes')->andReturn([
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
        ]);
        $evidence->shouldReceive('exportEligibilityRows')->andReturn(array_merge(
            array_fill(0, 7, ['eligible' => 1]),
            array_fill(0, 3, ['eligible' => 0])
        ));

        if ($expectWrite) {
            $replays->shouldReceive('nextReplayId')->andReturn(3101);
            $replays->shouldReceive('upsertMetric')->andReturnUsing(function (array $metric) {
                $this->persistedMetric = $metric;

                return null;
            });
            $replays->shouldReceive('replaceReasonCodeCounts')->andReturnNull();
        }

        return [$evidence, $publications, $replays];
    }

    /** @return array<string,mixed> */
    private function runRow(): array
    {
        return [
            'run_id' => self::RUN_ID,
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'config_version' => 'v1',
            'config_snapshot_id' => 7001,
            'publication_version' => 4,
            'coverage_universe_count' => 10,
            'coverage_expected_count' => 10,
            'coverage_available_count' => 10,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'bars_rows_written' => 10,
            'indicators_rows_written' => 10,
            'eligibility_rows_written' => 10,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            // The frozen inputs MD-S050 names. Without them on the run the bound-input block
            // would be empty strings and a perturbation would have nothing to diverge from.
            'observation_manifest_hash' => str_repeat('1', 64),
            'temporal_identity_hash' => str_repeat('2', 64),
            'calendar_status_hash' => str_repeat('3', 64),
            'factor_set_hash' => str_repeat('4', 64),
            'sealed_at' => self::TRADE_DATE.' 17:30:00',
        ];
    }

    /** @return array<string,mixed> */
    private function publicationRow(): array
    {
        return [
            'publication_id' => self::PUBLICATION_ID,
            'run_id' => self::RUN_ID,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => self::TRADE_DATE.' 17:30:00',
        ];
    }

    /** @param array<string,mixed> $override */
    private function fixtureDir(array $override = [], bool $pinPublication = true): string
    {
        $files = [
            'expected/expected_replay_result.json' => $this->expectedReplayResult($override),
            'expected/expected_run_summary.json' => [
                'bars_rows_written' => 10,
                'indicators_rows_written' => 10,
                'eligibility_rows_written' => 10,
                'eligible_count' => 7,
                'invalid_bar_count' => 0,
                'invalid_indicator_count' => 0,
                'warning_count' => 0,
                'hard_reject_count' => 0,
            ],
            'expected/expected_hashes.json' => [
                'bars_batch_hash' => 'A1',
                'indicators_batch_hash' => 'B1',
                'eligibility_batch_hash' => 'C1',
            ],
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
            ],
        ];

        $manifestFiles = array_keys($files);
        $manifest = [
            'fixture_id' => 'fixture_b18_comparison_exhaustiveness',
            'fixture_family' => 'fixture_b18_comparison_exhaustiveness',
            'fixture_version' => 'v2',
            'fixture_schema_version' => 'replay_fixture_v2',
            'fixture_created_at' => '2026-05-07T00:00:00+07:00',
            'fixture_source' => 'unit_test',
            'version' => 'v2',
            'contract_areas' => ['replay_verification', 'replay_determinism'],
            'files' => $manifestFiles,
            'assertion_layers' => ['run', 'source', 'coverage', 'hash', 'publication', 'pointer', 'fallback', 'correction', 'lineage', 'replay'],
        ];

        // PUBLICATION_EXACT accepts an explicit publication only for a run that became readable,
        // so the held import-only fixture asks for it to be left out.
        if ($pinPublication) {
            $manifest['publication_id'] = $files['expected/expected_replay_result.json']['expected_publication_context']['publication_id'];
        }

        $dir = sys_get_temp_dir().'/md_b18_exhaustiveness_'.uniqid();
        mkdir($dir, 0775, true);
        foreach ($files as $relative => $payload) {
            $path = $dir.'/'.$relative;
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0775, true);
            }
            file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $dir;
    }

    /**
     * @param array<string,mixed> $overrides
     * @return array<string,mixed>
     */
    private function expectedReplayResult(array $overrides = []): array
    {
        $v = array_merge([
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'request_mode' => null,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'source_mode' => 'manual_file',
            'source_identity' => 'mode=manual_file',
            'publication_id' => self::PUBLICATION_ID,
            'publication_run_id' => self::RUN_ID,
            'publication_version' => 4,
            'publication_is_current' => true,
            'coverage_universe_count' => 10,
            'coverage_available_count' => 10,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'factor_set_hash' => str_repeat('4', 64),
            'bars_rows_written' => 10,
            'indicators_rows_written' => 10,
            'eligibility_rows_written' => 10,
            'eligible_count' => 7,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
        ], $overrides);

        $seal = $v['seal_state'] ?? ($v['terminal_status'] === 'SUCCESS' ? 'SEALED' : 'UNSEALED');

        return [
            'comparison_result' => 'MATCH',
            'comparison_note' => 'deterministic replay fixture expectation',
            'expected_run_context' => [
                'run_id' => $v['publication_run_id'],
                'trade_date_requested' => $v['trade_date_requested'],
                'trade_date_effective' => $v['trade_date_effective'],
                'request_mode' => $v['request_mode'],
                'terminal_status' => $v['terminal_status'],
                'publishability_state' => $v['publishability_state'],
                'final_reason_code' => $v['final_reason_code'],
            ],
            'expected_source_context' => [
                'source_mode' => $v['source_mode'],
                'source_name' => null,
                'source_provider' => null,
                'provider' => null,
                'source_identity' => $v['source_identity'],
                'source_file_hash' => null,
                'source_final_reason_code' => null,
                'source_file_row_count' => null,
                'accepted_row_count' => $v['bars_rows_written'],
                'rejected_row_count' => $v['invalid_bar_count'],
                'invalid_row_count' => $v['invalid_bar_count'],
            ],
            'expected_coverage_context' => [
                'coverage_universe_count' => $v['coverage_universe_count'],
                'coverage_expected_count' => $v['coverage_universe_count'],
                'coverage_available_count' => $v['coverage_available_count'],
                'coverage_missing_count' => $v['coverage_missing_count'],
                'expected_bar_count' => $v['coverage_universe_count'],
                'available_bar_count' => $v['coverage_available_count'],
                'missing_bar_count' => $v['coverage_missing_count'],
                'coverage_ratio' => $v['coverage_ratio'],
                'coverage_min_threshold' => $v['coverage_min_threshold'],
                'coverage_gate_state' => $v['coverage_gate_state'],
                'coverage_reason_code' => $v['coverage_reason_code'],
                'coverage_threshold_mode' => $v['coverage_threshold_mode'],
                'coverage_universe_basis' => $v['coverage_universe_basis'],
                'coverage_contract_version' => $v['coverage_contract_version'],
                'coverage_missing_sample' => [],
            ],
            'expected_artifact_context' => [
                'bars_rows_written' => $v['bars_rows_written'],
                'indicators_rows_written' => $v['indicators_rows_written'],
                'eligibility_rows_written' => $v['eligibility_rows_written'],
                'eligible_count' => $v['eligible_count'],
                'invalid_bar_count' => $v['invalid_bar_count'],
                'invalid_indicator_count' => $v['invalid_indicator_count'],
                'warning_count' => $v['warning_count'],
                'hard_reject_count' => $v['hard_reject_count'],
                'bars_batch_hash' => $v['bars_batch_hash'],
                'indicators_batch_hash' => $v['indicators_batch_hash'],
                'eligibility_batch_hash' => $v['eligibility_batch_hash'],
            ],
            'expected_seal_context' => ['seal_state' => $seal],
            'expected_publication_context' => [
                'publication_id' => $v['publication_id'],
                'current_publication_id' => $v['publication_is_current'] ? $v['publication_id'] : null,
                'publication_run_id' => $v['publication_run_id'],
                'publication_version' => $v['publication_version'],
                'publication_terminal_status' => $v['terminal_status'],
                'publication_publishability_state' => $v['publishability_state'],
                'publication_is_current' => $v['publication_is_current'],
                'publication_seal_state' => $seal,
                'factor_set_hash' => $v['factor_set_hash'],
            ],
            'expected_pointer_context' => [
                'pointer_publication_id' => $v['publication_id'],
                'pointer_run_id' => $v['publication_run_id'],
                'pointer_publication_version' => $v['publication_version'],
                'pointer_resolve_status' => $v['publishability_state'] === 'READABLE' && $v['publication_is_current']
                    ? 'RESOLVED_READABLE_CURRENT'
                    : 'NOT_RESOLVED_READABLE_CURRENT',
                'pointer_switched' => $v['publication_is_current'],
            ],
            'expected_fallback_context' => [
                'fallback_used' => false,
                'fallback_publication_id' => null,
                'fallback_run_id' => null,
            ],
            'expected_correction_context' => [
                'correction_id' => null,
                'correction_status' => null,
                'correction_outcome' => null,
                'correction_reseal_status' => null,
                'correction_publication_switch' => null,
                'baseline_publication_id' => null,
                'candidate_publication_id' => null,
            ],
            'expected_final_state' => [
                'terminal_status' => $v['terminal_status'],
                'publishability_state' => $v['publishability_state'],
                'final_reason_code' => $v['final_reason_code'],
            ],
            'expected_reason_code' => $v['final_reason_code'],
            'expected_bound_input_context' => $v['expected_bound_input_context'] ?? [],
            'expected_lineage' => [
                'run_id' => $v['publication_run_id'],
                'publication_id' => $v['publication_id'],
                'current_publication_id' => $v['publication_is_current'] ? $v['publication_id'] : null,
                'publication_run_id' => $v['publication_run_id'],
                'correction_id' => null,
                'source_file_hash' => null,
                'bars_batch_hash' => $v['bars_batch_hash'],
                'indicators_batch_hash' => $v['indicators_batch_hash'],
                'eligibility_batch_hash' => $v['eligibility_batch_hash'],
                'factor_set_hash' => $v['factor_set_hash'],
                // The lineage carries the analytical product identity alongside the factor hash;
                // the run under test declares none, so these are null rather than absent.
                'factor_set_id' => null,
                'price_product_code' => null,
                'price_product_version' => null,
                'final_reason_code' => $v['final_reason_code'],
            ],
        ];
    }
}
