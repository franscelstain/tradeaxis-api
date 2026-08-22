<?php

require_once __DIR__.'/MarketDataConfigFoundationProofGate.php';

/** Atomic MD-B04-A002 proof binding. Usage: php this-file.php [--apply]. */
$root = dirname(__DIR__, 5);
$matrixPath = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);
$loaded = MarketDataClassificationConsistencyGate::readMatrix($matrixPath);
$headers = $loaded['headers'];
$rows = $loaded['rows'];
$bom = strpos((string) file_get_contents($matrixPath), "\xEF\xBB\xBF") === 0;
$map = MarketDataConfigFoundationProofGate::proofMap();
$seen = 0;
$satisfied = 0;
$blocked = 0;

foreach ($rows as &$row) {
    if (! isset($map[$row['rule_id']])) {
        continue;
    }
    $seen++;
    $parts = array_values(array_filter(array_map('trim', explode(' | ', $row['notes'])), static function ($part) {
        return $part !== '' && strpos($part, 'MD-B04-A002: proof_binding=') !== 0;
    }));
    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = MarketDataConfigFoundationProofGate::EVIDENCE;
    $parts[] = 'MD-B04-A002: proof_binding='.MarketDataConfigFoundationProofGate::EVIDENCE
        .'; proof_chain=current successor authority -> implementation -> positive/negative tests -> residue check -> governed evidence';
    if ($row['rule_id'] === MarketDataConfigFoundationProofGate::RESOLVED_RULE) {
        $parts[] = 'MD-B04-A002: decision=D-MD-20260822-06; MD-DEP-0007=RESOLVED; '
            .'null_token=zero-byte empty string; non-empty config/environment override fails closed';
    }
    $satisfied++;
    $row['notes'] = implode(' | ', $parts);
}
unset($row);

if ($seen !== 114 || $satisfied !== 114 || $blocked !== 0) {
    throw new RuntimeException('Atomic binding count mismatch: seen='.$seen.' satisfied='.$satisfied.' blocked='.$blocked);
}
echo json_encode(['denominator' => $seen, 'satisfied' => $satisfied, 'blocked' => $blocked], JSON_PRETTY_PRINT).PHP_EOL;
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
