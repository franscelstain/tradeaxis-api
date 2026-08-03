<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use PHPUnit\Framework\TestCase;

/**
 * Replay must actually detect a divergence in every dimension it claims to compare.
 *
 * ReplayMismatchClassificationTest proves that a detected difference is given the right name.
 * This proves the difference is detected at all — the prior question, and the one that decides
 * whether replay is a determinism proof or a routine that returns MATCH.
 *
 * The comparison spans roughly seventy fields across eleven context blocks: run identity, the
 * import/promote split, source identity and provider telemetry, coverage, artifact hashes and row
 * counts, seal, publication, pointer, fallback, correction lineage and the replay resolution
 * context. A branch that never fires in a test could be inverted, unreachable, or reading a key
 * that no longer exists, and replay would report MATCH on a dataset that had genuinely moved.
 *
 * Every case here starts from a fully matched pair that produces zero mismatches, changes exactly
 * one thing on the actual side, and asserts the comparison notices it and names it correctly. The
 * matched baseline is what makes each mutation meaningful: without it, a comparator that flagged
 * everything would pass every mutation test.
 */
class ReplayComparisonDetectsDivergenceTest extends TestCase
{
    private function service(): ReplayVerificationService
    {
        return (new ReflectionClass(ReplayVerificationService::class))->newInstanceWithoutConstructor();
    }

    /**
     * @return array<int, array<string, mixed>> the mismatch list the comparator produced
     */
    private function compare(array $actualOverrides = [], array $contextOverrides = [], array $fixtureOverrides = []): array
    {
        $method = new ReflectionMethod(ReplayVerificationService::class, 'compareExpectedAndActual');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->service(),
            $this->applyOverrides($this->fixture(), $fixtureOverrides),
            $this->applyOverrides($this->actual(), $actualOverrides),
            $this->applyOverrides($this->expectedContext(), $contextOverrides)
        );

        return $result['mismatches'] ?? [];
    }

    /**
     * @return string[] the reason codes attached to the mismatches
     */
    private function reasonCodes(array $mismatches): array
    {
        $codes = array_values(array_unique(array_map(function ($mismatch) {
            return $mismatch['reason_code'];
        }, $mismatches)));

        sort($codes);

        return $codes;
    }

    /**
     * @return string[] the field names that diverged
     */
    private function fields(array $mismatches): array
    {
        return array_map(function ($mismatch) {
            return $mismatch['field'];
        }, $mismatches);
    }

    private function applyOverrides(array $base, array $overrides): array
    {
        foreach ($overrides as $path => $value) {
            $cursor = &$base;

            foreach (explode('.', $path) as $segment) {
                if (! is_array($cursor)) {
                    $cursor = [];
                }

                if (! array_key_exists($segment, $cursor)) {
                    $cursor[$segment] = [];
                }

                $cursor = &$cursor[$segment];
            }

            $cursor = $value;
            unset($cursor);
        }

        return $base;
    }

    private function fixture(): array
    {
        return [
            'expected_replay_result' => [
                'comparison_result' => 'MATCH',
                'comparison_note' => 'matched',
                'config_identity' => 'ind_v1|coverage_gate_v1',
            ],
            'expected_run_summary' => [],
            'expected_hashes' => [],
            'expected_reason_code_counts' => [],
            'expected_reason_code_counts_present' => false,
            'expected_proof_missing' => [],
        ];
    }

    private function expectedContext(): array
    {
        return [
            'expected_run_context' => [
                'trade_date_requested' => '2026-03-20',
                'trade_date_effective' => '2026-03-20',
                'request_mode' => 'promote',
                'promote_mode' => 'full_publish',
                'publish_target' => 'current_replace',
                'import_status' => 'IMPORTED',
                'promote_status' => 'PROMOTED',
                'promoted' => true,
                'pointer_switched' => true,
            ],
            'expected_final_state' => [
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'final_reason_code' => 'RUN_COMPLETED',
            ],
            'expected_reason_code' => 'RUN_COMPLETED',
            'expected_seal_context' => ['seal_state' => 'SEALED'],
            // A real fixture's expected source context is a copy of the actual one, so its keys
            // always line up. `provider` is present alongside `source_provider` for that reason:
            // the comparator checks both names and reads the actual for each from
            // actual_source_context['source_provider'], so an expected context carrying only one
            // of them reports a divergence against itself.
            'expected_source_context' => [
                'source_mode' => 'api',
                'source_name' => 'API_FREE',
                'source_provider' => 'yahoo_finance',
                'provider' => 'yahoo_finance',
                'source_file_hash' => str_repeat('a', 64),
                'source_file_hash_algorithm' => 'SHA-256',
                'source_retry_max' => 0,
                'source_attempt_count' => 1,
                'source_final_http_status' => 200,
                'accepted_row_count' => 803,
                'rejected_row_count' => 0,
                'invalid_row_count' => 0,
            ],
            'expected_coverage_context' => [
                'coverage_universe_count' => 803,
                'coverage_available_count' => 803,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.000000',
                'coverage_min_threshold' => '0.980000',
                'coverage_gate_state' => 'PASS',
                'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                'coverage_contract_version' => 'coverage_gate_v1',
                'coverage_missing_sample' => [],
            ],
            'expected_artifact_context' => [
                'bars_batch_hash' => str_repeat('b', 64),
                'indicators_batch_hash' => str_repeat('c', 64),
                'eligibility_batch_hash' => str_repeat('d', 64),
                'bars_rows_written' => 803,
                'indicators_rows_written' => 803,
                'eligibility_rows_written' => 803,
                'artifact_scope' => 'current',
            ],
            'expected_publication_context' => [
                'publication_id' => 10,
                'publication_run_id' => 25,
                'publication_version' => 1,
                'publication_is_current' => 1,
                'publication_seal_state' => 'SEALED',
            ],
            'expected_pointer_context' => [
                'pointer_publication_id' => 10,
                'pointer_run_id' => 25,
                'pointer_publication_version' => 1,
                'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
                'pointer_switched' => true,
            ],
            'expected_fallback_context' => [
                'fallback_used' => false,
                'fallback_publication_id' => null,
            ],
            'expected_correction_context' => [
                'correction_id' => null,
                'correction_status' => null,
                'baseline_publication_id' => null,
                'candidate_publication_id' => null,
            ],
            'expected_replay_resolution_context' => [
                'replay_actual_resolution_mode' => 'CURRENT_PUBLICATION',
                'replay_selector_type' => 'run',
                'replay_selector_id' => 25,
                'current_pointer_required' => true,
                'is_current_publication' => 1,
                'lineage_verification_status' => 'VERIFIED',
                'run_id' => 25,
            ],
            'expected_lineage' => ['run' => 25, 'publication' => 10],
        ];
    }

    private function actual(): array
    {
        return [
            'trade_date' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'terminal_status' => 'SUCCESS',
            'status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'seal_state' => 'SEALED',
            'config_identity' => 'ind_v1|coverage_gate_v1',
            'source_mode' => 'api',
            'source_name' => 'API_FREE',
            'source_provider' => 'yahoo_finance',
            'bars_rows_written' => 803,
            'indicators_rows_written' => 803,
            'eligibility_rows_written' => 803,
            'invalid_bar_count' => null,
            'invalid_indicator_count' => null,
            'warning_count' => null,
            'hard_reject_count' => null,
            'eligible_count' => null,
            'accepted_row_count' => 803,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'bars_batch_hash' => str_repeat('b', 64),
            'indicators_batch_hash' => str_repeat('c', 64),
            'eligibility_batch_hash' => str_repeat('d', 64),
            'reason_code_counts' => [],
            'context' => [
                'actual_run_context' => [
                    'request_mode' => 'promote',
                    'promote_mode' => 'full_publish',
                    'publish_target' => 'current_replace',
                ],
                'actual_import_promote_context' => [
                    'import_status' => 'IMPORTED',
                    'promote_status' => 'PROMOTED',
                    'promoted' => true,
                    'pointer_switched' => true,
                ],
                'actual_source_context' => [
                    'source_mode' => 'api',
                    'source_name' => 'API_FREE',
                    'source_provider' => 'yahoo_finance',
                    'source_file_hash' => str_repeat('a', 64),
                    'source_file_hash_algorithm' => 'SHA-256',
                    'source_retry_max' => 0,
                    'source_attempt_count' => 1,
                    'source_final_http_status' => 200,
                    // These three are compared twice: once here, once against the top-level
                    // actual. Both readings must agree with the expectation.
                    'accepted_row_count' => 803,
                    'rejected_row_count' => 0,
                    'invalid_row_count' => 0,
                ],
                'actual_coverage_context' => [
                    'coverage_universe_count' => 803,
                    'coverage_available_count' => 803,
                    'coverage_missing_count' => 0,
                    'coverage_ratio' => '1.000000',
                    'coverage_min_threshold' => '0.980000',
                    'coverage_gate_state' => 'PASS',
                    'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
                    'coverage_threshold_mode' => 'MIN_RATIO',
                    'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                    'coverage_contract_version' => 'coverage_gate_v1',
                    'coverage_missing_sample' => [],
                ],
                'actual_artifact_context' => ['artifact_scope' => 'current'],
                'actual_publication_context' => [
                    'publication_id' => 10,
                    'publication_run_id' => 25,
                    'publication_version' => 1,
                    'publication_is_current' => 1,
                    'publication_seal_state' => 'SEALED',
                ],
                'actual_pointer_context' => [
                    'pointer_publication_id' => 10,
                    'pointer_run_id' => 25,
                    'pointer_publication_version' => 1,
                    'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
                    'pointer_switched' => true,
                ],
                'actual_fallback_context' => [
                    'fallback_used' => false,
                    'fallback_publication_id' => null,
                ],
                'actual_correction_context' => [
                    'correction_id' => null,
                    'correction_status' => null,
                    'baseline_publication_id' => null,
                    'candidate_publication_id' => null,
                ],
                'actual_replay_resolution_context' => [
                    'replay_actual_resolution_mode' => 'CURRENT_PUBLICATION',
                    'replay_selector_type' => 'run',
                    'replay_selector_id' => 25,
                    'current_pointer_required' => true,
                    'is_current_publication' => 1,
                    'lineage_verification_status' => 'VERIFIED',
                    'run_id' => 25,
                ],
                'actual_final_state' => ['final_reason_code' => 'RUN_COMPLETED'],
                'actual_lineage' => ['run' => 25, 'publication' => 10],
            ],
        ];
    }

    /**
     * The baseline. Without a pair that genuinely agrees, every mutation test below would pass
     * against a comparator that simply flagged everything.
     */
    public function test_a_matched_pair_produces_no_mismatches(): void
    {
        $mismatches = $this->compare();

        $this->assertSame(
            [],
            $this->fields($mismatches),
            'A run replayed identically must compare clean.'
        );
    }

    /**
     * Each case changes exactly one thing on the actual side.
     *
     * @dataProvider divergences
     */
    public function test_a_divergence_is_detected_and_named(array $actualOverrides, string $expectedField, string $expectedCode, string $why): void
    {
        $mismatches = $this->compare($actualOverrides);

        $this->assertNotSame([], $mismatches, 'Replay must notice: '.$why);
        $this->assertContains($expectedField, $this->fields($mismatches), 'Expected field to diverge: '.$why);
        $this->assertContains($expectedCode, $this->reasonCodes($mismatches), 'Expected reason code for: '.$why);
    }

    public function divergences(): array
    {
        return [
            'the effective trade date moved' => [
                ['trade_date_effective' => '2026-03-19'],
                'trade_date_effective',
                'REPLAY_EFFECTIVE_DATE_MISMATCH',
                'the run resolved a different trading day',
            ],
            'the run ended differently' => [
                ['terminal_status' => 'HELD', 'status' => 'HELD'],
                'terminal_status',
                'REPLAY_FINAL_STATUS_MISMATCH',
                'a run that succeeded before was held on replay',
            ],
            'the dataset stopped being readable' => [
                ['publishability_state' => 'NOT_READABLE'],
                'publishability_state',
                'REPLAY_FINAL_STATUS_MISMATCH',
                'the replayed run produced a non-readable dataset',
            ],
            'the promote no longer happened' => [
                ['context.actual_import_promote_context.promote_status' => 'NOT_PROMOTED'],
                'promote_status',
                'REPLAY_PROMOTE_STATUS_MISMATCH',
                'the promote half of the run did not repeat',
            ],
            'the pointer switch did not repeat' => [
                ['context.actual_import_promote_context.pointer_switched' => false],
                'pointer_switched',
                'REPLAY_UNEXPECTED_PUBLICATION_PROMOTION',
                'the current pointer moved on the original run and not on the replay',
            ],
            'the source file changed underneath' => [
                ['context.actual_source_context.source_file_hash' => str_repeat('9', 64)],
                'source_file_hash',
                'REPLAY_SOURCE_FILE_HASH_MISMATCH',
                'the same date was rebuilt from a different input file',
            ],
            'the source mode changed' => [
                ['source_mode' => 'manual_file', 'context.actual_source_context.source_mode' => 'manual_file'],
                'source_mode',
                'REPLAY_SOURCE_MODE_MISMATCH',
                'an API run was replayed from a manual file',
            ],
            'the provider changed' => [
                ['source_provider' => 'other_provider', 'context.actual_source_context.source_provider' => 'other_provider'],
                'source_provider',
                'REPLAY_PROVIDER_CONTEXT_MISMATCH',
                'the data came from a different provider',
            ],
            'the coverage verdict changed' => [
                ['context.actual_coverage_context.coverage_gate_state' => 'FAIL'],
                'coverage_gate_state',
                'REPLAY_COVERAGE_STATE_MISMATCH',
                'a day that passed the coverage gate now fails it',
            ],
            'the coverage ratio moved' => [
                ['context.actual_coverage_context.coverage_ratio' => '0.990000'],
                'coverage_ratio',
                'REPLAY_COVERAGE_RATIO_MISMATCH',
                'the same day covered a different share of the universe',
            ],
            'the coverage reason changed' => [
                ['context.actual_coverage_context.coverage_reason_code' => 'COVERAGE_BELOW_THRESHOLD'],
                'coverage_reason_code',
                'REPLAY_COVERAGE_REASON_MISMATCH',
                'coverage was explained by a different reason',
            ],
            'the bars hash changed' => [
                ['bars_batch_hash' => str_repeat('9', 64)],
                'bars_batch_hash',
                'REPLAY_ARTIFACT_HASH_MISMATCH',
                'the bars artifact is not byte-identical',
            ],
            'the indicators hash changed' => [
                ['indicators_batch_hash' => str_repeat('9', 64)],
                'indicators_batch_hash',
                'REPLAY_ARTIFACT_HASH_MISMATCH',
                'the indicators artifact is not byte-identical',
            ],
            'fewer rows were written' => [
                ['bars_rows_written' => 800],
                'bars_rows_written',
                'REPLAY_ARTIFACT_HASH_MISMATCH',
                'the replay produced three fewer bars',
            ],
            'the seal state changed' => [
                ['seal_state' => 'UNSEALED'],
                'seal_state',
                'REPLAY_SEAL_STATE_MISMATCH',
                'a sealed publication replayed unsealed',
            ],
            'the publication version moved' => [
                ['context.actual_publication_context.publication_version' => 2],
                'publication_version',
                'REPLAY_PUBLICATION_VERSION_MISMATCH',
                'the replay landed on a different publication version',
            ],
            'the publication stopped being current' => [
                ['context.actual_publication_context.publication_is_current' => 0],
                'publication_is_current',
                'REPLAY_PUBLICATION_STATE_MISMATCH',
                'the publication is no longer the current one',
            ],
            'the pointer resolves differently' => [
                ['context.actual_pointer_context.pointer_resolve_status' => 'NOT_RESOLVED_READABLE_CURRENT'],
                'pointer_resolve_status',
                'REPLAY_POINTER_RESOLUTION_MISMATCH',
                'the pointer no longer resolves to a readable publication',
            ],
            'the pointer targets another publication' => [
                ['context.actual_pointer_context.pointer_publication_id' => 99],
                'pointer_publication_id',
                'REPLAY_POINTER_TARGET_MISMATCH',
                'the pointer names a different publication',
            ],
            'a fallback was used this time' => [
                ['context.actual_fallback_context.fallback_used' => true],
                'fallback_used',
                'REPLAY_FALLBACK_CONTEXT_MISMATCH',
                'the replay fell back to an earlier day when the original did not',
            ],
            'a correction appeared' => [
                ['context.actual_correction_context.correction_id' => 7],
                'correction_id',
                'REPLAY_CORRECTION_BASELINE_MISMATCH',
                'the replay involved a correction the original did not',
            ],
            'the final reason code changed' => [
                ['context.actual_final_state.final_reason_code' => 'RUN_PARTIAL_DATA'],
                'final_reason_code',
                'REPLAY_FINAL_REASON_CODE_MISMATCH',
                'the run closed with a different reason',
            ],
            'the lineage changed' => [
                ['context.actual_lineage' => ['run' => 99, 'publication' => 10]],
                'lineage',
                'REPLAY_LINEAGE_MISMATCH',
                'the dataset traces back to a different run',
            ],
            'the configuration changed' => [
                ['config_identity' => 'ind_v2|coverage_gate_v1'],
                'config_identity',
                'REPLAY_CONFIG_IDENTITY_MISMATCH',
                'the replay ran under a different indicator set version',
            ],
            'the replay resolved through a different selector' => [
                ['context.actual_replay_resolution_context.replay_selector_type' => 'correction'],
                'replay_replay_selector_type',
                'REPLAY_EXPECTED_HISTORICAL_ACTUAL_CURRENT_MISMATCH',
                'the actual state was resolved by a different selector',
            ],
            'the resolution mode changed' => [
                ['context.actual_replay_resolution_context.replay_actual_resolution_mode' => 'HISTORICAL_PUBLICATION_AUDIT'],
                'replay_replay_actual_resolution_mode',
                'REPLAY_EXPECTED_HISTORICAL_ACTUAL_CURRENT_MISMATCH',
                'a current-pointer replay resolved as a historical audit',
            ],
        ];
    }

    /**
     * The comparator treats a missing expectation differently depending on the field, and the
     * split is deliberate.
     *
     * Run-identity fields go through compareField, which skips when the expectation is null: a
     * fixture that never recorded a promote_mode is not asserting anything about it.
     *
     * Context-block fields go through compareFieldAllowNull, which does not skip. There, null is
     * itself an expectation — "this run had no correction", "no fallback was used" — so an actual
     * value where the fixture recorded none is a real divergence rather than a gap in the proof.
     *
     * Getting this backwards in either direction would be quiet: skipping too much shrinks the
     * proof without saying so, and skipping too little makes every partial fixture look broken.
     */
    public function test_a_missing_expectation_is_skipped_for_run_identity_and_compared_for_context(): void
    {
        // compareField: a null expectation means the fixture asserts nothing here.
        $skipped = $this->compare(
            ['context.actual_run_context.promote_mode' => 'correction'],
            ['expected_run_context.promote_mode' => null]
        );

        $this->assertNotContains('promote_mode', $this->fields($skipped));

        // compareFieldAllowNull: a null expectation means the fixture asserts absence.
        $compared = $this->compare(
            ['context.actual_correction_context.correction_status' => 'PUBLISHED'],
            ['expected_correction_context.correction_status' => null]
        );

        $this->assertContains('correction_status', $this->fields($compared));
        $this->assertContains('REPLAY_CORRECTION_BASELINE_MISMATCH', $this->reasonCodes($compared));
    }

    /**
     * Missing expected proof is itself reported, so a fixture that lost a section does not read
     * as a clean match.
     */
    public function test_missing_expected_proof_is_reported_rather_than_ignored(): void
    {
        $mismatches = $this->compare([], [], ['expected_proof_missing' => ['expected_run_summary']]);

        $this->assertContains('expected_proof.expected_run_summary', $this->fields($mismatches));
        $this->assertContains('REPLAY_EXPECTED_PROOF_INCOMPLETE', $this->reasonCodes($mismatches));
    }
}
