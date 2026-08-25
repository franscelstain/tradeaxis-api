<?php
require_once __DIR__.'/MarketDataPublicationLifecycleProofGate.php';

$root = dirname(__DIR__, 5);
$apply = in_array('--apply', $argv, true);
$validateOnly = in_array('--validate-only', $argv, true);
$evidenceId = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--evidence-id=') === 0) {
        $evidenceId = substr($arg, strlen('--evidence-id='));
    }
}

if ($validateOnly) {
    $result = MarketDataPublicationLifecycleProofGate::validate($root, false);
    $out = [
        'status' => $result['status'],
        'mode' => 'VALIDATE_ONLY',
        'denominator' => $result['denominator'],
        'proof_map_count' => $result['proof_map_count'],
        'runtime_pending' => $result['runtime_pending'],
        'errors' => $result['errors'],
    ];
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}

if (! $evidenceId || ! preg_match('/^E-MD-B10-A001-\d{3}$/', $evidenceId)) {
    throw new RuntimeException('Use --validate-only or --evidence-id=E-MD-B10-A001-NNN [--apply].');
}

$matches = glob($root.'/docs/market_data/records/evidence/'.$evidenceId.'_*');
if (count($matches) !== 1) {
    throw new RuntimeException('Exactly one governed B10 evidence document must exist before binding.');
}
$payload = json_decode(file_get_contents($matches[0]), true);
if (! is_array($payload)) {
    throw new RuntimeException('Governed B10 evidence must be valid JSON.');
}

$matrixPath = MarketDataPublicationLifecycleTraceabilitySpec::matrixPath($root);
$h = fopen($matrixPath, 'r');
$headers = fgetcsv($h);
$bom = strpos($headers[0], "\xEF\xBB\xBF") === 0;
$headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
$rows = [];
while (($row = fgetcsv($h)) !== false) {
    if (count($row) === count($headers)) {
        $rows[] = array_combine($headers, $row);
    }
}
fclose($h);

$entries = MarketDataPublicationLifecycleProofSpec::entries($root);
$entryByRule = [];
foreach ($entries as $entry) {
    if (isset($entryByRule[$entry['rule_id']])) {
        throw new RuntimeException('Duplicate B10 proof entry: '.$entry['rule_id']);
    }
    $entryByRule[$entry['rule_id']] = $entry;
}

$seen = 0;
foreach ($rows as &$row) {
    $rid = (string) ($row['rule_id'] ?? '');
    if (! isset($entryByRule[$rid])) {
        continue;
    }
    if (($row['coverage_status'] ?? '') !== 'NOT_ASSESSED' || trim((string) ($row['current_evidence_ids'] ?? '')) !== '') {
        throw new RuntimeException('B10 binder refuses non-pristine predicate '.$rid);
    }
    $seen++;
    $row['coverage_status'] = 'SATISFIED';
    $row['current_evidence_ids'] = $evidenceId;
    $row['notes'] = trim(
        (string) ($row['notes'] ?? '')
        .' | MD-B10-A001: proof_binding='.$evidenceId
        .'; proof_family='.$entryByRule[$rid]['family']
        .'; proof_chain=current authority -> executable publication lifecycle -> positive/fail-closed returned local proof -> residue -> governed evidence',
        ' |'
    );
}
unset($row);

if ($seen !== MarketDataPublicationLifecycleProofSpec::EXPECTED_DENOMINATOR) {
    throw new RuntimeException('B10 binder count mismatch: '.$seen);
}

$boundResult = MarketDataPublicationLifecycleProofGate::validate($root, true, [
    'rows' => $rows,
    'evidence_payload' => $payload,
]);
if ($boundResult['status'] !== 'PASS') {
    throw new RuntimeException('B10 bound validation failed: '.implode('; ', $boundResult['errors']));
}

echo json_encode([
    'status' => 'PASS',
    'denominator' => $seen,
    'evidence_id' => $evidenceId,
    'apply' => $apply,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (! $apply) {
    exit(0);
}

$h = fopen($matrixPath, 'w');
$outHeaders = $headers;
if ($bom) {
    $outHeaders[0] = "\xEF\xBB\xBF".$outHeaders[0];
}
fputcsv($h, $outHeaders);
foreach ($rows as $row) {
    fputcsv($h, array_map(static function ($key) use ($row) {
        return $row[$key];
    }, $headers));
}
fclose($h);
