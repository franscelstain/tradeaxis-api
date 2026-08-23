<?php
require_once __DIR__.'/MarketDataCanonicalRawImportTraceabilitySpec.php';
final class MarketDataCanonicalRawImportTraceabilityGate
{
    public static function validate(string $root): array
    {
        $rows = MarketDataCanonicalRawImportTraceabilitySpec::rows($root);
        $mandatory = MarketDataCanonicalRawImportTraceabilitySpec::denominator($root);
        $optional = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES' && $r['primary_stage'] === 'MD-B09' && $r['applicability'] === 'OPTIONAL_CAPABILITY';
        });
        $moved = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES' && strpos((string) $r['supporting_stages'], 'MD-B09') !== false && $r['primary_stage'] !== 'MD-B09';
        });
        $pending = array_filter($rows, static function ($r) {
            return $r['active'] === 'YES' && $r['primary_stage'] === 'MD-B09' && in_array($r['applicability'], ['MANDATORY_OR_CONDITIONAL', 'CONDITIONAL_PENDING', 'APPLICABILITY_PENDING'], true);
        });
        $errors = [];
        if (count($mandatory) !== 139) $errors[] = 'mandatory denominator must be 139';
        if (count($optional) !== 12) $errors[] = 'optional capability count must be 12';
        if (count($moved) !== 46) $errors[] = 'moved downstream count must be 46';
        if (count($pending) !== 0) $errors[] = 'B09 applicability pending must be zero';
        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'mandatory' => count($mandatory),
            'optional' => count($optional),
            'moved' => count($moved),
            'pending_applicability' => count($pending),
            'errors' => $errors,
        ];
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $result = MarketDataCanonicalRawImportTraceabilityGate::validate($root);
    echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
