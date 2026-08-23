<?php

require_once __DIR__.'/MarketDataSourceResilienceProofGate.php';

/** Atomic post-runtime MD-B08-A001 proof binder. Never run before governed local-proof evidence exists. */
$root = dirname(__DIR__, 5);
$matrixPath = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);
$evidenceId = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--evidence-id=') === 0) {
        $evidenceId = trim(substr($arg, strlen('--evidence-id=')));
    }
}
if ($evidenceId === null || ! preg_match('/^E-MD-B08-A001-\d{3}$/', $evidenceId)) {
    throw new RuntimeException('Usage: php '.basename(__FILE__).' --evidence-id=E-MD-B08-A001-NNN [--apply]');
}
$evidenceMatches = glob($root.'/docs/market_data/records/evidence/'.$evidenceId.'_*');
if (! is_array($evidenceMatches) || count($evidenceMatches) !== 1) {
    throw new RuntimeException('Exactly one governed evidence file must exist before binding '.$evidenceId);
}

$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), "\xEF\xBB\xBF") === 0;
$map = MarketDataSourceResilienceProofSpec::proofMap();
$families = MarketDataSourceResilienceProofSpec::familyAssignment();
$seen = 0;

foreach ($rows as &$row) {
    if (! isset($map[$row['rule_id']])) {
        continue;
    }
    if ($row['primary_stage'] !== MarketDataSourceResilienceTraceabilitySpec::STAGE
        || $row['coverage_requirement'] !== 'REQUIRED') {
        throw new RuntimeException('Proof map names a non-current B08 row: '.$row['rule_id']);
    }
    $seen++;
    $parts = array_values(array_filter(array_map('trim', explode(' | ', $row['notes'])), static function ($part) {
        return $part !== '' && strpos($part, 'MD-B08-A001: proof_binding=') !== 0;
    }));
    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = $evidenceId;
    $parts[] = 'MD-B08-A001: proof_binding='.$evidenceId
        .'; proof_family='.$families[$row['rule_id']]
        .'; proof_chain=current authority -> actual source-resilience implementation -> positive/fail-closed runtime tests -> audit-visible telemetry -> residue -> governed evidence';
    $row['notes'] = implode(' | ', $parts);
}
unset($row);

if ($seen !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
    throw new RuntimeException('Atomic B08 binding mismatch: seen='.$seen);
}

echo json_encode(['denominator' => $seen, 'evidence_id' => $evidenceId, 'apply' => $apply], JSON_PRETTY_PRINT).PHP_EOL;
if (! $apply) {
    echo "DRY RUN — pass --apply only after returned local proof has been governed.\n";
    exit(0);
}

$handle = fopen($matrixPath, 'w');
$outHeaders = $headers;
if ($bom) {
    $outHeaders[0] = "\xEF\xBB\xBF".$outHeaders[0];
}
fputcsv($handle, $outHeaders);
foreach ($rows as $row) {
    fputcsv($handle, array_map(static function ($header) use ($row) {
        return $row[$header];
    }, $headers));
}
fclose($handle);

echo 'WROTE '.$matrixPath.PHP_EOL;
