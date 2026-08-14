<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use PHPUnit\Framework\TestCase;

/**
 * Every field replay compares must be classified, and classified correctly.
 *
 * Replay is how this platform proves a dataset can be re-derived identically. When a replay
 * fails, the reason code attached to each mismatch is what tells an operator what diverged —
 * whether the source changed, the coverage moved, an artifact hash differs, or the pointer landed
 * somewhere else. Thirty-eight such codes exist. Five were ever produced by a test.
 *
 * The classification lives in one ordered chain of thirty-three conditions, and the order carries
 * weight that is invisible from reading any single line:
 *
 *     if (strpos($field, 'source_file_hash') !== false) return REPLAY_SOURCE_FILE_HASH_MISMATCH;
 *     ...
 *     if (strpos($field, 'source_')         !== false) return REPLAY_SOURCE_IDENTITY_MISMATCH;
 *
 * Move the second above the first and every source-file-hash divergence is reported as a source
 * identity problem. The replay still fails, the run is still held, and the operator investigates
 * the wrong thing. Nothing about that is visible in a passing suite.
 *
 * These tests derive the field list from the comparison method itself, so a field added there is
 * covered without editing anything here.
 */
class ReplayMismatchClassificationTest extends TestCase
{
    /** @var string[]|null */
    private static $fields = null;

    private function classify(string $field): string
    {
        $method = new ReflectionMethod(ReplayVerificationService::class, 'reasonCodeForField');
        $method->setAccessible(true);

        return (string) $method->invoke(
            (new ReflectionClass(ReplayVerificationService::class))->newInstanceWithoutConstructor(),
            $field
        );
    }

    /**
     * The field names the comparator actually passes, read out of the comparison method.
     *
     * Two shapes appear there: a literal second argument to compareField and its variants, and a
     * foreach over an array of names. Both are collected; nothing is listed by hand.
     *
     * @return string[]
     */
    private function comparedFields(): array
    {
        if (self::$fields !== null) {
            return self::$fields;
        }

        $source = file_get_contents(
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'app/Application/MarketData/Services/ReplayVerificationService.php')
        );

        $start = strpos($source, 'private function compareExpectedAndActual(');
        $this->assertNotFalse($start, 'The comparison method must exist.');

        $end = strpos($source, "\n    private function ", $start + 10);
        $body = substr($source, $start, $end - $start);

        $fields = [];

        // A literal second argument, but not the concatenated form: one loop passes
        // 'replay_'.$field, and treating that prefix as a field name would both invent a field
        // called "replay_" and hide the real names, which carry the prefix twice.
        preg_match_all("/compare(?:Field|FieldAllowNull|NumericFieldAllowNull)\(\\\$mismatches, '([a-z0-9_.]+)'\s*,/", $body, $literals);
        $fields = array_merge($fields, $literals[1]);

        // Loops. Each is paired with the way its compare call names the field, because one of
        // them prefixes: `compareFieldAllowNull($mismatches, 'replay_'.$field, ...)` over a list
        // that already begins with replay_ produces replay_replay_actual_resolution_mode.
        preg_match_all(
            "/foreach \(\[((?:'[a-z0-9_]+',?\s*)+)\] as \\\$field\)\s*\{(.*?)\n        \}/s",
            $body,
            $loops,
            PREG_SET_ORDER
        );

        foreach ($loops as $loop) {
            preg_match_all("/'([a-z0-9_]+)'/", $loop[1], $names);

            $prefix = '';
            if (preg_match("/compare[A-Za-z]*\(\\\$mismatches, '([a-z0-9_]+)'\s*\.\s*\\\$field/", $loop[2], $prefixMatch)) {
                $prefix = $prefixMatch[1];
            }

            foreach ($names[1] as $name) {
                $fields[] = $prefix.$name;
            }
        }

        $fields = array_values(array_unique($fields));
        sort($fields);

        return self::$fields = $fields;
    }

    public function test_the_comparison_surface_is_discovered(): void
    {
        // Guards the guard: a refactor that changed the call shape would make every assertion
        // below pass against an empty field list.
        $fields = $this->comparedFields();

        $this->assertGreaterThan(50, count($fields));

        foreach (['trade_date_requested', 'bars_batch_hash', 'coverage_gate_state', 'pointer_resolve_status'] as $expected) {
            $this->assertContains($expected, $fields);
        }
    }

    /**
     * REPLAY_NON_DETERMINISTIC_OUTPUT is the chain's final fallback. Reaching it means replay
     * detected a difference it could not name — which tells an operator the computation is
     * unstable, when the truth may be something ordinary and fixable.
     *
     * A compared field landing there is a classification gap, not a genuine non-determinism.
     */
    public function test_no_compared_field_falls_through_to_the_catch_all(): void
    {
        $unclassified = [];

        foreach ($this->comparedFields() as $field) {
            if ($this->classify($field) === 'REPLAY_NON_DETERMINISTIC_OUTPUT') {
                $unclassified[] = $field;
            }
        }

        $this->assertSame(
            [],
            $unclassified,
            "These fields are compared but have no specific reason code.\n"
            ."A divergence in one of them is reported as non-deterministic output, which points the "
            ."operator at the computation rather than at what actually changed."
        );
    }

    /**
     * Whatever the chain returns must be a code the operator can look up.
     *
     * Read from the registry rather than by seeding a database. ReasonCodeSeedExecutionTest
     * already executes the seed and proves the registry and eod_reason_codes match exactly, so
     * the registry is the same set — and this test then needs no database at all, which keeps the
     * other twenty-one methods in this class from paying for a schema they never touch.
     */
    public function test_every_classification_is_a_registered_reason_code(): void
    {
        $registry = file_get_contents(
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/registry/Reason_Codes_Registry.md')
        );

        preg_match_all('/^\| `([A-Z0-9_]+)` \|/m', $registry, $matches);
        $registered = array_values(array_unique($matches[1]));

        $this->assertGreaterThan(200, count($registered), 'The registry parse must not come back near-empty.');

        $unregistered = [];

        foreach ($this->comparedFields() as $field) {
            $code = $this->classify($field);

            if (! in_array($code, $registered, true)) {
                $unregistered[] = $field.' -> '.$code;
            }
        }

        $this->assertSame([], $unregistered);
    }

    /**
     * The specific classifications whose correctness depends on chain order.
     *
     * Each pair below is a field that would be captured by a later, broader rule if the earlier
     * one were moved or removed. Pinning them is what makes a reordering fail here rather than
     * silently in production.
     *
     * @dataProvider orderSensitiveFields
     */
    public function test_order_sensitive_fields_keep_their_specific_classification(string $field, string $expected, string $wouldOtherwiseBe): void
    {
        $actual = $this->classify($field);

        $this->assertSame(
            $expected,
            $actual,
            $field.' must classify as '.$expected.'. If the chain is reordered it becomes '
            .$wouldOtherwiseBe.', and the operator is sent to the wrong place.'
        );
    }

    public function orderSensitiveFields(): array
    {
        return [
            // 'source_file_hash' contains 'source_', which a later rule catches.
            'source file hash' => ['source_file_hash', 'REPLAY_SOURCE_FILE_HASH_MISMATCH', 'REPLAY_SOURCE_IDENTITY_MISMATCH'],
            'source hash algorithm' => ['source_file_hash_algorithm', 'REPLAY_SOURCE_FILE_HASH_MISMATCH', 'REPLAY_SOURCE_IDENTITY_MISMATCH'],
            // 'source_mode' likewise.
            'source mode' => ['source_mode', 'REPLAY_SOURCE_MODE_MISMATCH', 'REPLAY_SOURCE_IDENTITY_MISMATCH'],
            // 'source_provider' likewise, and the provider rule also owns retry/timeout/attempt.
            'source provider' => ['source_provider', 'REPLAY_PROVIDER_CONTEXT_MISMATCH', 'REPLAY_SOURCE_IDENTITY_MISMATCH'],
            'retry ceiling' => ['source_retry_max', 'REPLAY_PROVIDER_CONTEXT_MISMATCH', 'REPLAY_SOURCE_IDENTITY_MISMATCH'],
            'attempt count' => ['source_attempt_count', 'REPLAY_PROVIDER_CONTEXT_MISMATCH', 'REPLAY_SOURCE_IDENTITY_MISMATCH'],
            // 'coverage_ratio' and the bar counts sit before the general 'coverage_' rule.
            'coverage ratio' => ['coverage_ratio', 'REPLAY_COVERAGE_RATIO_MISMATCH', 'REPLAY_COVERAGE_STATE_MISMATCH'],
            'coverage threshold' => ['coverage_min_threshold', 'REPLAY_COVERAGE_RATIO_MISMATCH', 'REPLAY_COVERAGE_STATE_MISMATCH'],
            'coverage reason' => ['coverage_reason_code', 'REPLAY_COVERAGE_REASON_MISMATCH', 'REPLAY_COVERAGE_STATE_MISMATCH'],
            // 'publication_version' contains 'publication_', which the next rule catches.
            'publication version' => ['publication_version', 'REPLAY_PUBLICATION_VERSION_MISMATCH', 'REPLAY_PUBLICATION_STATE_MISMATCH'],
            'pointer publication version' => ['pointer_publication_version', 'REPLAY_PUBLICATION_VERSION_MISMATCH', 'REPLAY_POINTER_TARGET_MISMATCH'],
            // 'pointer_resolve_status' sits before the general 'pointer_' rule.
            'pointer resolution' => ['pointer_resolve_status', 'REPLAY_POINTER_RESOLUTION_MISMATCH', 'REPLAY_POINTER_TARGET_MISMATCH'],
            // pointer_switched is claimed by an earlier, sharper rule than 'pointer_'.
            'pointer switched' => ['pointer_switched', 'REPLAY_UNEXPECTED_PUBLICATION_PROMOTION', 'REPLAY_POINTER_TARGET_MISMATCH'],
            // Artifact hashes are caught by 'batch_hash' before any publication rule.
            'bars hash' => ['bars_batch_hash', 'REPLAY_ARTIFACT_HASH_MISMATCH', 'REPLAY_NON_DETERMINISTIC_OUTPUT'],
            'indicators hash' => ['indicators_batch_hash', 'REPLAY_ARTIFACT_HASH_MISMATCH', 'REPLAY_NON_DETERMINISTIC_OUTPUT'],
        ];
    }

    /**
     * The three run-outcome fields must all report as a final-status divergence. They are the
     * fields an operator reads first, and splitting them across codes would fragment the one
     * question that matters: did this replay end the same way.
     *
     * @dataProvider finalStatusFields
     */
    public function test_run_outcome_fields_report_as_a_final_status_divergence(string $field): void
    {
        $this->assertSame('REPLAY_FINAL_STATUS_MISMATCH', $this->classify($field));
    }

    public function finalStatusFields(): array
    {
        return [
            'terminal status' => ['terminal_status'],
            'status' => ['status'],
            'publishability' => ['publishability_state'],
        ];
    }

    /**
     * Proof-completeness gaps are classified by prefix, so any missing expected or actual proof
     * path resolves without being enumerated.
     */
    public function test_missing_proof_paths_are_classified_by_side(): void
    {
        $this->assertSame('REPLAY_EXPECTED_PROOF_INCOMPLETE', $this->classify('expected_proof.expected_run_summary'));
        $this->assertSame('REPLAY_EXPECTED_PROOF_INCOMPLETE', $this->classify('expected_proof.anything_at_all'));
        $this->assertSame('REPLAY_ACTUAL_PROOF_INCOMPLETE', $this->classify('actual_proof.actual_run_context'));
    }
}
