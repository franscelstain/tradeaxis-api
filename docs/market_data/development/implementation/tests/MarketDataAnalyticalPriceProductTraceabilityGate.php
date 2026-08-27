<?php
require_once __DIR__.'/MarketDataAnalyticalPriceProductTraceabilitySpec.php';
final class MarketDataAnalyticalPriceProductTraceabilityGate
{
    public static function validate(string $root, bool $bound = false): array
    {
        $rows = MarketDataAnalyticalPriceProductTraceabilitySpec::stageRows($root);
        $mandatory = MarketDataAnalyticalPriceProductTraceabilitySpec::mandatory($root);
        $reference = MarketDataAnalyticalPriceProductTraceabilitySpec::reference($root);
        $pending = array_filter($rows, static function ($r) {
            return in_array(($r['applicability'] ?? ''), ['MANDATORY_OR_CONDITIONAL', 'CONDITIONAL_PENDING', 'APPLICABILITY_PENDING', 'PENDING'], true);
        });
        $invalid = [];
        foreach ($mandatory as $r) {
            $evidence = trim((string) ($r['current_evidence_ids'] ?? ''));
            if ($bound) {
                if (($r['coverage_status'] ?? '') !== 'SATISFIED' || ! preg_match('/^E-MD-B12-A00[12]-\d{3}$/', $evidence)) {
                    $invalid[] = $r['rule_id'];
                }
            } elseif (($r['coverage_status'] ?? '') !== 'NOT_ASSESSED' || $evidence !== '') {
                $invalid[] = $r['rule_id'];
            }
        }
        $errors = [];
        if (count($mandatory) !== MarketDataAnalyticalPriceProductTraceabilitySpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'MANDATORY_DENOMINATOR_MISMATCH';
        }
        if (count($reference) !== MarketDataAnalyticalPriceProductTraceabilitySpec::EXPECTED_REFERENCE) {
            $errors[] = 'REFERENCE_COUNT_MISMATCH';
        }
        if (count($pending) !== 0) {
            $errors[] = 'APPLICABILITY_PENDING_NONZERO';
        }
        if ($invalid) {
            $errors[] = $bound ? 'BOUND_MANDATORY_STATE_INVALID' : 'PREMATURE_MANDATORY_STATE';
        }

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
            'stage_rows' => count($rows),
            'mandatory' => count($mandatory),
            'reference' => count($reference),
            'pending_applicability' => count($pending),
            'invalid_mandatory_state' => count($invalid),
            'runtime_satisfied' => $bound ? count($mandatory) : 0,
            'errors' => $errors,
        ];
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $r = MarketDataAnalyticalPriceProductTraceabilityGate::validate(dirname(__DIR__, 5), in_array('--bound', $argv, true));
    echo json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($r['status'] === 'PASS' ? 0 : 1);
}
