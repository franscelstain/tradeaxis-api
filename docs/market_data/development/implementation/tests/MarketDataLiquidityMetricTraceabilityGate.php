<?php

require_once __DIR__.'/MarketDataLiquidityMetricTraceabilitySpec.php';

/**
 * MD-B13 traceability/applicability gate.
 *
 * Three modes, because B13 passes through three states rather than two:
 *
 *  - `PRE_RUNTIME` — entry. Mandatory rows are NOT_ASSESSED and unbound; the fifteen conditional
 *    rows are still APPLICABILITY_PENDING.
 *  - `APPLICABILITY_RESOLVED` — every conditional row has been decided with evidence, so nothing
 *    is pending. Mandatory rows may still be unbound.
 *  - `BOUND_CLOSURE` — mandatory rows are SATISFIED against B13 evidence and no applicability
 *    remains pending.
 *
 * The middle mode exists because resolving applicability and proving predicates are separate
 * obligations that fail for separate reasons. Collapsing them would let a stage that had proven
 * every mandatory predicate close while fifteen rows still had no decision behind them.
 */
final class MarketDataLiquidityMetricTraceabilityGate
{
    public static function validate(string $root, bool $bound = false, bool $requireApplicabilityResolved = false): array
    {
        $spec = MarketDataLiquidityMetricTraceabilitySpec::class;
        $rows = $spec::stageRows($root);
        $mandatory = $spec::mandatory($root);
        $reference = $spec::reference($root);
        $conditionalPending = $spec::conditionalPending($root);
        $conditionalResolved = $spec::conditionalResolved($root);
        $transitional = $spec::transitional($root);

        $errors = [];
        $invalid = [];

        foreach ($mandatory as $row) {
            $evidence = trim((string) ($row['current_evidence_ids'] ?? ''));
            if ($bound) {
                if (($row['coverage_status'] ?? '') !== 'SATISFIED' || ! preg_match('/^E-MD-B13-A001-\d{3}$/', $evidence)) {
                    $invalid[] = $row['rule_id'];
                }
            } elseif (($row['coverage_status'] ?? '') !== 'NOT_ASSESSED' || $evidence !== '') {
                $invalid[] = $row['rule_id'];
            }
        }

        // A conditional row's coverage status is derived from its applicability, and a mismatch
        // between the two is how a NOT_APPLICABLE row quietly becomes a hidden pass.
        $statusMismatch = [];
        $expectedStatus = [
            'CONDITIONAL_PENDING' => 'APPLICABILITY_PENDING',
            'CONDITIONAL_NOT_APPLICABLE' => 'NOT_APPLICABLE',
        ];
        foreach (array_merge($conditionalPending, $conditionalResolved) as $row) {
            $applicability = (string) ($row['applicability'] ?? '');
            $status = (string) ($row['coverage_status'] ?? '');
            if (isset($expectedStatus[$applicability])) {
                if ($status !== $expectedStatus[$applicability]) {
                    $statusMismatch[] = $row['rule_id'].':'.$applicability.'/'.$status;
                }

                continue;
            }
            // CONDITIONAL_APPLICABLE follows the mandatory lifecycle.
            if (! in_array($status, ['NOT_ASSESSED', 'SATISFIED'], true)) {
                $statusMismatch[] = $row['rule_id'].':'.$applicability.'/'.$status;
            }
        }

        // A NOT_APPLICABLE row must record the evidenced false condition. Section 4 calls
        // NOT_APPLICABLE a terminal outcome requiring evidence, not a synonym for satisfied.
        $unevidencedNotApplicable = [];
        foreach ($conditionalResolved as $row) {
            if (($row['applicability'] ?? '') !== 'CONDITIONAL_NOT_APPLICABLE') {
                continue;
            }
            $notes = (string) ($row['notes'] ?? '');
            if (strpos($notes, 'applicability_basis=condition_false_evidenced') === false
                || ! preg_match('/applicability_evidence=E-MD-B13-A001-\d{3}/', $notes)) {
                $unevidencedNotApplicable[] = $row['rule_id'];
            }
        }

        if (count($mandatory) !== MarketDataLiquidityMetricTraceabilitySpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'MANDATORY_DENOMINATOR_MISMATCH';
        }
        if (count($reference) !== MarketDataLiquidityMetricTraceabilitySpec::EXPECTED_REFERENCE) {
            $errors[] = 'REFERENCE_COUNT_MISMATCH';
        }
        if (count($rows) !== MarketDataLiquidityMetricTraceabilitySpec::EXPECTED_DENOMINATOR
            + MarketDataLiquidityMetricTraceabilitySpec::EXPECTED_CONDITIONAL_PENDING
            + MarketDataLiquidityMetricTraceabilitySpec::EXPECTED_REFERENCE) {
            $errors[] = 'STAGE_PARTITION_MISMATCH';
        }
        if ($transitional !== []) {
            $errors[] = 'TRANSITIONAL_APPLICABILITY_PRESENT';
        }
        if ($statusMismatch !== []) {
            $errors[] = 'APPLICABILITY_STATUS_MISMATCH';
        }
        if ($unevidencedNotApplicable !== []) {
            $errors[] = 'NOT_APPLICABLE_WITHOUT_EVIDENCED_CONDITION';
        }
        if ($invalid !== []) {
            $errors[] = $bound ? 'BOUND_MANDATORY_STATE_INVALID' : 'PREMATURE_MANDATORY_STATE';
        }
        if (($bound || $requireApplicabilityResolved) && $conditionalPending !== []) {
            $errors[] = 'APPLICABILITY_PENDING_BLOCKS_CLOSURE';
        }
        if (! $bound && ! $requireApplicabilityResolved
            && count($conditionalPending) !== MarketDataLiquidityMetricTraceabilitySpec::EXPECTED_CONDITIONAL_PENDING) {
            $errors[] = 'ENTRY_CONDITIONAL_PENDING_MISMATCH';
        }

        $mode = $bound ? 'BOUND_CLOSURE' : ($requireApplicabilityResolved ? 'APPLICABILITY_RESOLVED' : 'PRE_RUNTIME');

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'mode' => $mode,
            'stage_rows' => count($rows),
            'mandatory' => count($mandatory),
            'reference' => count($reference),
            'conditional_pending' => count($conditionalPending),
            'conditional_resolved' => count($conditionalResolved),
            'transitional' => count($transitional),
            'invalid_mandatory_state' => count($invalid),
            'applicability_status_mismatch' => $statusMismatch,
            'unevidenced_not_applicable' => $unevidencedNotApplicable,
            'runtime_satisfied' => $bound ? count($mandatory) : 0,
            'errors' => $errors,
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $result = MarketDataLiquidityMetricTraceabilityGate::validate(
        dirname(__DIR__, 5),
        in_array('--bound', $argv, true),
        in_array('--applicability-resolved', $argv, true)
    );
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
