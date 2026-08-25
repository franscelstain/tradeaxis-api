<?php
require_once __DIR__.'/MarketDataPublicationLifecycleTraceabilitySpec.php';

final class MarketDataPublicationLifecycleTraceabilityGate
{
    public static function validate(string $root, bool $bound = false): array
    {
        $rows = MarketDataPublicationLifecycleTraceabilitySpec::rows($root);
        $mandatory = MarketDataPublicationLifecycleTraceabilitySpec::mandatory($root);
        $optional = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES' && $r['primary_stage'] === 'MD-B10'
                && $r['applicability'] === 'OPTIONAL_CAPABILITY';
        });
        $reference = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES' && $r['primary_stage'] === 'MD-B10'
                && $r['coverage_requirement'] === 'REFERENCE_ONLY';
        });
        $moved = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES'
                && $r['primary_stage'] !== 'MD-B10'
                && in_array('MD-B10', array_filter(explode(';', (string) $r['supporting_stages'])), true);
        });
        $pending = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES' && $r['primary_stage'] === 'MD-B10'
                && in_array($r['applicability'], ['MANDATORY_OR_CONDITIONAL', 'CONDITIONAL_PENDING', 'APPLICABILITY_PENDING'], true);
        });
        $invalidMandatory = array_filter($mandatory, static function ($r) use ($bound) {
            if ($bound) {
                return $r['coverage_status'] !== 'SATISFIED'
                    || ! preg_match('/^E-MD-B10-A001-\d{3}$/', trim((string) $r['current_evidence_ids']));
            }

            return $r['coverage_status'] !== 'NOT_ASSESSED' || trim((string) $r['current_evidence_ids']) !== '';
        });

        $errors = [];
        if (count($mandatory) !== MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'mandatory denominator must be '.MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_DENOMINATOR;
        }
        if (count($optional) !== MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_OPTIONAL) {
            $errors[] = 'optional count must be '.MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_OPTIONAL;
        }
        if (count($moved) !== MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_MOVED) {
            $errors[] = 'moved count must be '.MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_MOVED;
        }
        if (count($reference) !== MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_REFERENCE) {
            $errors[] = 'reference count must be '.MarketDataPublicationLifecycleTraceabilitySpec::EXPECTED_REFERENCE;
        }
        if (count($pending) !== 0) {
            $errors[] = 'B10 applicability pending must be zero';
        }
        if (count($invalidMandatory) !== 0) {
            $errors[] = $bound
                ? 'B10 closure mandatory rows must be SATISFIED and bound to one current B10 evidence ID'
                : 'B10 stage-entry mandatory rows must remain NOT_ASSESSED/unbound before runtime proof';
        }

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'mandatory' => count($mandatory),
            'optional' => count($optional),
            'moved' => count($moved),
            'reference' => count($reference),
            'pending_applicability' => count($pending),
            'mode' => $bound ? 'BOUND_CLOSURE' : 'PRE_RUNTIME',
            'invalid_mandatory_state' => count($invalidMandatory),
            'premature_satisfied_or_bound' => $bound ? 0 : count($invalidMandatory),
            'errors' => $errors,
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $bound = in_array('--bound', $argv, true);
    $result = MarketDataPublicationLifecycleTraceabilityGate::validate($root, $bound);
    echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
