<?php

require_once __DIR__.'/MarketDataCalendarStatusProofGate.php';

/** Atomic MD-B06-A001 proof binding. Usage: php this-file.php [--apply]. */
$root = dirname(__DIR__, 5);
$matrixPath = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);
$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), "\xEF\xBB\xBF") === 0;
$map = MarketDataCalendarStatusProofGate::proofMap();
$expected = MarketDataCalendarStatusTraceabilitySpec::EXPECTED_B06_DENOMINATOR;
$seen = 0;
$remediated = 0;

foreach ($rows as &$row) {
    if (! isset($map[$row['rule_id']])) {
        continue;
    }
    if ($row['primary_stage'] !== MarketDataCalendarStatusTraceabilitySpec::STAGE
        || $row['coverage_requirement'] !== 'REQUIRED') {
        throw new RuntimeException('Proof map names a non-current B06 row: '.$row['rule_id']);
    }
    $seen++;
    $parts = array_values(array_filter(array_map('trim', explode(' | ', $row['notes'])), static function ($part) {
        return $part !== '' && strpos($part, 'MD-B06-A001: proof_binding=') !== 0;
    }));
    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = MarketDataCalendarStatusProofGate::EVIDENCE;
    $binding = 'MD-B06-A001: proof_binding='.MarketDataCalendarStatusProofGate::EVIDENCE
        .'; proof_family='.MarketDataCalendarStatusProofGate::familyAssignment()[$row['rule_id']]
        .'; proof_chain=current authority -> actual implementation -> positive and fail-closed tests -> residue check -> governed evidence';
    if (isset(MarketDataCalendarStatusProofGate::REMEDIATED_RULES[$row['rule_id']])) {
        $binding .= '; remediated_at=MD-B06-A001 ('.MarketDataCalendarStatusProofGate::REMEDIATED_RULES[$row['rule_id']].')';
        $remediated++;
    }
    $parts[] = $binding;
    $row['notes'] = implode(' | ', $parts);
}
unset($row);

if ($seen !== $expected || $remediated !== count(MarketDataCalendarStatusProofGate::REMEDIATED_RULES)) {
    throw new RuntimeException('Atomic B06 binding mismatch: seen='.$seen.' remediated='.$remediated);
}
echo json_encode(['denominator' => $seen, 'satisfied' => $seen, 'remediated_annotated' => $remediated], JSON_PRETTY_PRINT).PHP_EOL;
if (! $apply) {
    echo "DRY RUN — pass --apply to write.\n";
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
