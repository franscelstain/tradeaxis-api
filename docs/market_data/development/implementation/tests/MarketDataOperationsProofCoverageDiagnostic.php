<?php

require_once __DIR__.'/MarketDataOperationsProofSpec.php';
require_once __DIR__.'/MarketDataOperationsProofBasis.php';

/** Read-only planning inventory. This never changes admission or binds a predicate. */
final class MarketDataOperationsProofCoverageDiagnostic
{
    public static function report(): array
    {
        $basis = MarketDataOperationsProofBasis::PROVEN;
        $families = MarketDataOperationsProofSpec::families();
        $rules = [];
        foreach (MarketDataOperationsProofSpec::RULE_FAMILIES as $id => $family) {
            $rules[$family][] = $id;
        }
        $result = [];
        foreach ($rules as $name => $ids) {
            $reviewed = array_values(array_intersect($ids, array_keys($basis)));
            $result[$name] = [
                'denominator' => count($ids),
                'reviewed_basis' => count($reviewed),
                'missing_ids' => array_values(array_diff($ids, $reviewed)),
                'implementation' => $families[$name]['implementation'],
                'family_guard_assigned' => count($families[$name]['positive']) === 2
                    && count($families[$name]['negative']) === 2,
            ];
        }

        return [
            'attempt' => MarketDataOperationsProofSpec::ATTEMPT,
            'denominator' => count(MarketDataOperationsProofSpec::RULE_FAMILIES),
            'reviewed_basis' => count($basis),
            'families' => $result,
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo json_encode(MarketDataOperationsProofCoverageDiagnostic::report(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
}
