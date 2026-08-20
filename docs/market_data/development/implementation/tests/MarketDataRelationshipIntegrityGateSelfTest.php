<?php
/**
 * PHP 7.3+; executable self-test for the Market Data integrity gates.
 *
 * The previous version of this file held nine invariant descriptions in an array, printed
 * "PASS <description>" for each, and exited 0 unconditionally. It never loaded a gate, never
 * constructed a violating fixture, and never asserted anything, so an operator reading nine green
 * lines learned nothing. Recorded as F-MD-B00-A001-002.
 *
 * This version proves the opposite property, which is the one that matters: each gate FAILS when
 * its invariant is broken. It copies the market_data tree to a temporary location, mutates the
 * copy, runs the real gate against it, and asserts a non-zero exit — plus an unmutated control that
 * must exit zero. The repository is never modified.
 *
 * Usage: php MarketDataRelationshipIntegrityGateSelfTest.php
 * Exit:  0 when every gate failed closed on every mutation and passed on the control.
 */

$md = realpath(dirname(__DIR__, 3));
if ($md === false) {
    fwrite(STDERR, "SELF_TEST_ROOT_UNRESOLVED\n");
    exit(2);
}

$work = sys_get_temp_dir().DIRECTORY_SEPARATOR.'md_gate_selftest_'.getmypid();

function rrmdir($dir)
{
    if (! is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) {
        $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
    }
    @rmdir($dir);
}

function rcopy($src, $dst)
{
    @mkdir($dst, 0777, true);
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $f) {
        $target = $dst.DIRECTORY_SEPARATOR.$it->getSubPathName();
        if ($f->isDir()) {
            @mkdir($target, 0777, true);
        } else {
            copy($f->getPathname(), $target);
        }
    }
}

rrmdir($work);
rcopy($md, $work);

$docGate = $work.'/development/implementation/tests/MarketDataDocumentationIntegrityGate.php';
$relGate = $work.'/development/implementation/tests/MarketDataRelationshipIntegrityGate.php';

if (! is_file($docGate) || ! is_file($relGate)) {
    fwrite(STDERR, "SELF_TEST_GATE_NOT_COPIED\n");
    rrmdir($work);
    exit(2);
}

function runGate($gate)
{
    $out = [];
    $code = 0;
    exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($gate).' 2>&1', $out, $code);

    return $code;
}

/** Rewrite one CSV cell, addressed by column name, on rows whose document/record id matches. */
function csvSet($path, $matchColumn, $matchNeedle, $setColumn, $value)
{
    $rows = [];
    $f = fopen($path, 'r');
    while (($r = fgetcsv($f)) !== false) {
        $rows[] = $r;
    }
    fclose($f);
    $header = $rows[0];
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    $idx = array_flip($header);
    if (! isset($idx[$matchColumn]) || ! isset($idx[$setColumn])) {
        return false;
    }
    $hit = false;
    foreach ($rows as $i => &$r) {
        if ($i === 0) {
            continue;
        }
        if (! isset($r[$idx[$matchColumn]]) || strpos($r[$idx[$matchColumn]], $matchNeedle) === false) {
            continue;
        }
        $r[$idx[$setColumn]] = $value;
        $hit = true;
    }
    unset($r);
    if (! $hit) {
        return false;
    }
    $o = fopen($path, 'w');
    foreach ($rows as $r) {
        fputcsv($o, $r);
    }
    fclose($o);

    return true;
}

$results = [];
$failed = false;

function record(&$results, &$failed, $name, $expected, $observed, $applied = true)
{
    $ok = $applied && $expected === $observed;
    if (! $ok) {
        $failed = true;
    }
    $results[] = [
        'mutation' => $name,
        'expected' => $expected === 0 ? 'PASS' : 'FAIL',
        'observed' => $observed === 0 ? 'PASS' : 'FAIL',
        'mutation_applied' => $applied,
        'verdict' => $ok ? ($expected === 0 ? 'CONTROL_OK' : 'FAILS_CLOSED') : ($applied ? 'GATE_DID_NOT_REACT' : 'MUTATION_NOT_APPLIED'),
    ];
}

// ---- control -------------------------------------------------------------
record($results, $failed, 'control: unmutated documentation gate', 0, runGate($docGate));
record($results, $failed, 'control: unmutated relationship gate', 0, runGate($relGate));

// ---- documentation gate --------------------------------------------------
$stray = $work.'/development/implementation/SELF_TEST_UNREGISTERED.md';
file_put_contents($stray, "stray\n");
record($results, $failed, 'unregistered physical document added', 1, runGate($docGate), is_file($stray));
@unlink($stray);

$frozen = $work.'/authority/strategy/book/EOD_Bars_Contract.md';
$frozenBackup = file_get_contents($frozen);
file_put_contents($frozen, $frozenBackup."\nSELF_TEST_MUTATION\n");
record($results, $failed, 'frozen strategy byte mutated', 1, runGate($docGate), file_get_contents($frozen) !== $frozenBackup);
file_put_contents($frozen, $frozenBackup);

$verif = $work.'/authority/governance/CURRENT_VERIFICATION_REGISTRY.csv';
$verifBackup = file_get_contents($verif);
$applied = csvSet($verif, 'document_path', 'records/evidence/legacy/semantic/', 'current_proof_eligible', 'YES');
record($results, $failed, 'legacy evidence flipped to current_proof_eligible=YES', 1, runGate($docGate), $applied);
file_put_contents($verif, $verifBackup);

$readme = $work.'/development/implementation/README.md';
$readmeBackup = file_get_contents($readme);
file_put_contents($readme, $readmeBackup."\n[dead](./SELF_TEST_NO_SUCH_FILE.md)\n");
record($results, $failed, 'broken active markdown link introduced', 1, runGate($docGate), file_get_contents($readme) !== $readmeBackup);
file_put_contents($readme, $readmeBackup);

$extracts = glob($work.'/records/evidence/legacy/semantic/*.md');
if ($extracts) {
    $extract = $extracts[0];
    $extractBackup = file_get_contents($extract);
    $marker = "<!-- LEGACY_EXTRACT_BODY_START -->\n";
    $at = strpos($extractBackup, $marker);
    $mutated = $at === false
        ? $extractBackup
        : substr($extractBackup, 0, $at + strlen($marker))."SELF_TEST_TAMPER\n".substr($extractBackup, $at + strlen($marker));
    file_put_contents($extract, $mutated);
    record($results, $failed, 'legacy split extract body tampered inside the seal', 1, runGate($docGate), $mutated !== $extractBackup);
    file_put_contents($extract, $extractBackup);
} else {
    record($results, $failed, 'legacy split extract body tampered inside the seal', 1, 0, false);
}

// ---- relationship gate ---------------------------------------------------
$rec = $work.'/records/WORK_RECORD_REGISTRY.csv';
$recBackup = file_get_contents($rec);

file_put_contents($rec, $recBackup."SELF-TEST-1,EVIDENCE,MD-B00,MD-B00-A001,MD-B99-A999,BL,ISSUED,x.md,,,,,,2026-01-01T00:00:00+00:00,\n");
record($results, $failed, 'work_id differs from attempt_id', 1, runGate($relGate), strpos(file_get_contents($rec), 'SELF-TEST-1') !== false);
file_put_contents($rec, $recBackup);

file_put_contents($rec, $recBackup."SELF-TEST-2,EVIDENCE,MD-B00,MDB00A1,MDB00A1,BL,ISSUED,x.md,,,,,,2026-01-01T00:00:00+00:00,\n");
record($results, $failed, 'malformed attempt ID shape', 1, runGate($relGate), strpos(file_get_contents($rec), 'SELF-TEST-2') !== false);
file_put_contents($rec, $recBackup);

$rel = $work.'/records/WORK_RELATIONSHIP_REGISTRY.csv';
$relBackup = file_get_contents($rel);
file_put_contents($rel, $relBackup."SELF-TEST-R1,DOES-NOT-EXIST,ALSO-MISSING,SUPPORTS,self test,,2026-01-01T00:00:00+00:00,\n");
record($results, $failed, 'relationship references a non-existent record', 1, runGate($relGate), strpos(file_get_contents($rel), 'SELF-TEST-R1') !== false);
file_put_contents($rel, $relBackup);

// ---- final control: the copy must be back to a passing state -------------
record($results, $failed, 'post-restore control: documentation gate', 0, runGate($docGate));
record($results, $failed, 'post-restore control: relationship gate', 0, runGate($relGate));

rrmdir($work);

$summary = [
    'self_test' => 'MarketDataRelationshipIntegrityGateSelfTest',
    'status' => $failed ? 'FAIL' : 'PASS',
    'method' => 'Gates executed against a temporary copy of the market_data tree. Each mutation is verified as applied before the gate runs. The repository is never modified.',
    'mutations' => $results,
    'generated_at' => date(DATE_ATOM),
];

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed ? 1 : 0);
