<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` -- the release-candidate criteria of
 * `Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md` (`MD-S002`).
 *
 * These predicates are unlike the others in this attempt: each is a claim about a **whole fixture
 * family** rather than about one behaviour. "All anti-survivorship and as-known isolation fixtures
 * passing" is satisfied by the corpus or by nothing, so a family-level guard is semantically
 * identical to the predicate here -- which is exactly what `F-MD-B19-A001-002` says a family-level
 * guard is *not*, when the predicate is a single behaviour. The distinction is the whole reason
 * these can be bound this way and the others could not.
 *
 * What a guard can establish is that the corpus each criterion names is complete and executable.
 * That every member is green is established by the suite run itself and recorded with the stage
 * evidence; it is not something a test can assert about itself without circularity.
 *
 * Each criterion therefore gets its own test naming its own corpus, so no two predicates rest on
 * the same assertion.
 */
class B18ReleaseCandidateCriteriaTest extends TestCase
{
    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md';

    /**
     * Each release-candidate bullet, mapped to the guards that establish it.
     *
     * @return array<string,array<int,string>>
     */
    private function criteriaMap(): array
    {
        return [
            'zero unexplained value, null-reason, lineage, config, factor, hash, seal, or publication mismatches in exact publication fixtures;' => [
                'B18ReplayComparisonExhaustivenessTest::test_a_divergence_in_any_named_assertion_class_denies_pass',
                'B18ReplayComparisonExhaustivenessTest::test_the_perturbation_table_covers_every_class_the_contract_names',
                'B18ReplayComparisonExhaustivenessTest::test_the_unperturbed_fixture_passes',
            ],
            // Two of this criterion's three dimensions now have a corpus; the runtime one does
            // not, and cannot from inside a single process. See the test below.
            'deterministic output across supported runtime/locale/concurrency conditions;' => [
                'B18DeterminismAcrossConditionsTest::test_an_owned_decimal_field_normalizes_identically_under_a_comma_decimal_locale',
                'B18DeterminismAcrossConditionsTest::test_a_batch_hash_is_identical_under_a_comma_decimal_locale',
                'B18DeterminismAcrossConditionsTest::test_a_canonical_document_hash_is_identical_under_a_comma_decimal_locale',
                'B18DeterminismAcrossConditionsTest::test_row_order_does_not_change_a_batch_hash',
            ],
            'all anti-survivorship and as-known isolation fixtures passing;' => [
                'B18AntiSurvivorshipFixtureCorpusTest::test_the_contract_list_and_the_reviewed_fixture_map_cover_exactly_the_same_cases',
                'B18AntiSurvivorshipFixtureCorpusTest::test_every_named_fixture_guard_exists_and_is_executable',
                'B18AsKnownSnapshotIsolationTest::test_every_later_revision_kind_is_bound_to_an_executing_guard',
                'B18AntiFutureResolutionTest::test_every_anti_future_guard_exists_and_is_executable',
            ],
            'all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denominator shrinkage;' => [
                'B18DegradedObservationDefectCorpusTest::test_every_named_defect_guard_exists_and_is_executable',
                'B18DegradedObservationDefectCorpusTest::test_every_defect_is_bound_to_one_of_the_two_permitted_outcomes',
                'EmptyDatasetFailSafeTest::test_an_available_fallback_holds_the_run_rather_than_failing_it',
                'EmptyDatasetFailSafeTest::test_no_fallback_fails_the_run_outright',
                'SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest',
            ],
            'long-chain ATR and corporate-action results matching independent oracles;' => [
                'B18LongChainWilderAtrOracleTest::test_a_two_hundred_session_chain_matches_the_independently_computed_wilder_atr',
                'B18LongChainWilderAtrOracleTest::test_a_correction_thirty_sessions_back_still_moves_the_atr_by_its_decayed_amount',
                'IndicatorIndependentOracleTest::test_correction_oracle_propagates_by_exactly_the_expected_amount',
                'AdjustmentFactorSetB11Test::test_only_authoritative_or_manual_verified_revisions_are_adjustment_active',
                'CoherentPriceProductBoundaryTest::test_every_ohlc_field_moves_on_the_same_scale',
            ],
            'corrected publications preserving their predecessors and switching atomically; and' => [
                'B18CorrectionReadPathScenarioTest::test_the_superseded_publication_keeps_its_rows_and_cannot_be_discarded',
                'B18CorrectionReadPathScenarioTest::test_a_projection_disagreeing_with_the_pointer_yields_nothing_rather_than_stale_rows',
                'PublicationSealPointerLifecycleTest::test_the_pointer_table_structurally_refuses_a_second_current_row',
            ],
            '`BLOCKED` treated as missing proof, never converted to pass.' => [
                'B18ReplayComparisonExhaustivenessTest::test_a_publication_with_no_configuration_snapshot_is_blocked_rather_than_passed',
                'B18ReplayComparisonExhaustivenessTest::test_a_blocked_replay_is_recorded_with_its_admission_state',
            ],
        ];
    }

    /**
     * The map and the contract must name the same criteria. A criterion added to `MD-S002` with no
     * corpus behind it fails here rather than leaving the release gate quietly short.
     */
    public function test_the_criteria_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);

        $lines = preg_split('/\R/', (string) file_get_contents($path));
        $start = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === 'A release candidate requires:') {
                $start = $i;
                break;
            }
        }
        $this->assertNotNull($start, 'the MD-S002 release-candidate introducer moved; re-read it '
            .'rather than weakening this map');

        $criteria = [];
        for ($i = $start + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            if (strpos($line, '- ') !== 0) {
                break;
            }
            $criteria[] = trim(substr($line, 2));
        }

        $mapped = array_keys($this->criteriaMap());
        sort($criteria);
        sort($mapped);

        $this->assertSame($criteria, $mapped,
            'MD-S002 and the reviewed criteria map disagree about what a release candidate requires');
    }

    /**
     * `MD-S002-R0003` -- zero unexplained mismatches in exact publication fixtures.
     */
    public function test_the_exact_publication_mismatch_corpus_is_complete(): void
    {
        $this->assertCorpusExecutable('zero unexplained value, null-reason, lineage, config, factor, hash, seal, or publication mismatches in exact publication fixtures;');
    }

    /**
     * `MD-S002-R0005` -- all anti-survivorship and as-known isolation fixtures.
     */
    public function test_the_anti_survivorship_and_as_known_isolation_corpus_is_complete(): void
    {
        $this->assertCorpusExecutable('all anti-survivorship and as-known isolation fixtures passing;');
    }

    /**
     * `MD-S002-R0006` -- all degraded and negative fixtures.
     */
    public function test_the_degraded_and_negative_corpus_is_complete(): void
    {
        $this->assertCorpusExecutable('all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denominator shrinkage;');
    }

    /**
     * `MD-S002-R0007` -- long-chain ATR and corporate-action oracles.
     */
    public function test_the_independent_oracle_corpus_is_complete(): void
    {
        $this->assertCorpusExecutable('long-chain ATR and corporate-action results matching independent oracles;');
    }

    /**
     * `MD-S002-R0008` -- corrected publications preserving predecessors and switching atomically.
     */
    public function test_the_corrected_publication_corpus_is_complete(): void
    {
        $this->assertCorpusExecutable('corrected publications preserving their predecessors and switching atomically; and');
    }

    /**
     * The criterion this attempt cannot yet satisfy, stated rather than omitted.
     *
     * "Deterministic output across supported runtime/locale/concurrency conditions" needs the
     * output compared across more than one runtime, locale and concurrency setting. The suite runs
     * in one of each, so nothing here can establish it, and a guard that asserted determinism
     * within a single environment would be claiming the predicate while testing something weaker.
     * The map records it with an empty corpus so the gap is visible and counted rather than absent.
     */
    public function test_the_runtime_dimension_of_the_determinism_criterion_is_recorded_as_having_no_corpus(): void
    {
        $corpus = $this->criteriaMap()['deterministic output across supported runtime/locale/concurrency conditions;'];

        // The locale and concurrency dimensions were executed in MD-B18-A002 and the locale one
        // found a real defect: float rendering went through `%g`, which is locale-aware, so every
        // artifact hash containing a float was a function of the host's locale. Both are bound
        // above. The runtime dimension is the one still carried, and it is carried for a reason a
        // test cannot remove: comparing two runtimes needs a second interpreter.
        $this->assertNotSame([], $corpus,
            'the locale and concurrency dimensions have an executed corpus and it has been removed');

        $names = implode(' ', $corpus);
        $this->assertStringNotContainsStringIgnoringCase('runtime', $names,
            'a guard claiming the runtime dimension was added to this corpus; if two runtimes are '
                .'genuinely compared now, bind MD-S002-R0004 rather than leaving it recorded as a '
                .'gap, and if they are not, the guard is claiming something it does not do');

        $this->assertTrue(
            is_file(__DIR__.'/B18DeterminismAcrossConditionsTest.php')
                && strpos((string) file_get_contents(__DIR__.'/B18DeterminismAcrossConditionsTest.php'),
                    'function test_the_supported_runtime_range_still_spans_more_than_one_runtime(') !== false,
            'the guard that records why the runtime dimension is unproven has been removed, which '
                .'would leave the gap unstated rather than closed'
        );
    }

    /**
     * Every guard every criterion names must exist, or the map is a list of intentions.
     */
    public function test_every_guard_named_by_any_criterion_exists(): void
    {
        $missing = [];

        foreach ($this->criteriaMap() as $criterion => $refs) {
            foreach ($refs as $ref) {
                [$class, $method] = explode('::', $ref);
                $file = __DIR__.'/'.$class.'.php';
                if (! is_file($file) || strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                    $missing[] = $criterion.' -> '.$ref;
                }
            }
        }

        $this->assertSame([], $missing, 'these release criteria name a guard that no longer exists');
    }

    /**
     * A criterion's corpus must be more than one guard, or "all ... fixtures" is being satisfied by
     * a single case. This is the check that keeps a family-level binding from collapsing into the
     * shape `F-MD-B19-A001-002` records.
     */
    private function assertCorpusExecutable(string $criterion): void
    {
        $map = $this->criteriaMap();
        $this->assertArrayHasKey($criterion, $map, 'the criterion is not in the reviewed map');

        $refs = $map[$criterion];
        $this->assertGreaterThanOrEqual(2, count($refs),
            'a criterion covering a whole fixture family is bound to fewer than two guards');

        $missing = [];
        foreach ($refs as $ref) {
            [$class, $method] = explode('::', $ref);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file) || strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $ref;
            }
        }

        $this->assertSame([], $missing, 'this criterion names a guard that no longer exists');
    }
}
