<?php
/**
 * CreateWorkBaselineLock.php
 * PHP 7.3+ compatible, no external dependencies.
 *
 * Example:
 * php docs/watchlist/development/implementation/tests/CreateWorkBaselineLock.php \
 *   --stage=WS-B00 --attempt=WS-B00-A001 --baseline=WSBL-20260818-001 \
 *   --record=E-WS-B00-A001-001 \
 *   --output=docs/watchlist/records/evidence/runs/E-WS-B00-A001-001_WORK_BASELINE_LOCK.json
 */

function fail($message, $code = 2) {
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function opt($name, $default = null) {
    global $argv;
    $prefix = '--' . $name . '=';
    foreach ($argv as $arg) {
        if (strpos($arg, $prefix) === 0) {
            return substr($arg, strlen($prefix));
        }
    }
    return $default;
}

function sha1FileUpper($path) {
    return strtoupper(sha1_file($path));
}

function relPath($path, $root) {
    $path = str_replace('\\', '/', realpath($path));
    $root = rtrim(str_replace('\\', '/', realpath($root)), '/');
    if (strpos($path, $root . '/') === 0) {
        return substr($path, strlen($root) + 1);
    }
    return $path;
}

function runCmd($cmd, $cwd) {
    $old = getcwd();
    chdir($cwd);
    $out = array(); $rc = 0;
    exec($cmd . ' 2>&1', $out, $rc);
    chdir($old);
    return array($rc, trim(implode("\n", $out)));
}

$stage = opt('stage');
$attempt = opt('attempt');
$baselineId = opt('baseline');
$recordId = opt('record');
$output = opt('output');
$mode = strtoupper(opt('mode', 'IMPLEMENTATION'));

if (!$stage || !$attempt || !$baselineId || !$recordId || !$output) {
    fail('Required: --stage=WS-Bxx --attempt=... --baseline=WSBL-... --record=E-WS-... --output=...');
}
if (!in_array($mode, array('IMPLEMENTATION', 'AUDIT_READONLY', 'DOCUMENTATION_GOVERNANCE'), true)) {
    fail('Invalid --mode');
}
if (!preg_match('/^(WS-B\d{2}[A-Z]?)-A\d{3}$/', $attempt, $m) || $m[1] !== $stage) {
    fail('Attempt ID must match Stage ID, e.g. WS-B04-A003.');
}
if ($mode !== 'DOCUMENTATION_GOVERNANCE' && !preg_match('/^E-'.preg_quote($attempt, '/').'-\d{3}$/', $recordId)) {
    fail('Current record id must be correlation-first, e.g. E-' . $attempt . '-001');
}

$watchlistRoot = realpath(dirname(__DIR__, 3));
$repoRoot = realpath(dirname($watchlistRoot, 2));
if (!$watchlistRoot || !$repoRoot) fail('Cannot resolve repository root.');

$epochPath = $watchlistRoot . DIRECTORY_SEPARATOR . 'authority' . DIRECTORY_SEPARATOR . 'governance' . DIRECTORY_SEPARATOR . 'CURRENT_VERIFICATION_EPOCH.json';
if (!is_file($epochPath)) fail('Missing CURRENT_VERIFICATION_EPOCH.json');
$epochPayload = json_decode(file_get_contents($epochPath), true);
if (!is_array($epochPayload) || empty($epochPayload['verification_epoch'])) fail('Invalid current verification epoch registry.');
$verificationEpoch = $epochPayload['verification_epoch'];

$strategyDir = $watchlistRoot . DIRECTORY_SEPARATOR . 'authority' . DIRECTORY_SEPARATOR . 'strategy';
$strategy = array();
foreach (glob($strategyDir . DIRECTORY_SEPARATOR . '*.md') as $p) {
    if (basename($p) === 'README.md') continue;
    $strategy[] = array('path' => relPath($p, $repoRoot), 'sha1' => sha1FileUpper($p));
}
usort($strategy, function($a, $b) { return strcmp($a['path'], $b['path']); });

$govNames = array(
    'CURRENT_VERIFICATION_REBASELINE_STANDARD.md',
    'ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md',
    'DOCUMENTATION_ARCHITECTURE.md',
    'DOCUMENT_RECORDING_STANDARD.md',
    'WORK_BASELINE_LOCK_STANDARD.md',
    'WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md',
    'CHANGE_IMPACT_DECLARATION_STANDARD.md',
    'DEPENDENCY_REGISTRY_STANDARD.md',
    'STAGE_CLOSURE_MANIFEST_STANDARD.md',
    'CURRENT_STATE_SUMMARY_STANDARD.md',
    'STAGE_EXECUTION_AND_REWORK_STANDARD.md',
    'IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md',
    'STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md',
    'DOCUMENT_INTEGRITY_GATE_STANDARD.md',
    'DOCUMENT_CHANGE_POLICY.md',
    'WATCHLIST_DOCUMENT_AUTHORITY.md',
    'WATCHLIST_OWNER_MATRIX.md'
);
$govLock = array();
foreach ($govNames as $name) {
    $p = $watchlistRoot . DIRECTORY_SEPARATOR . 'authority' . DIRECTORY_SEPARATOR . 'governance' . DIRECTORY_SEPARATOR . $name;
    if (!is_file($p)) fail('Missing governance authority: ' . $p);
    $govLock[] = array('path' => relPath($p, $repoRoot), 'sha1' => sha1FileUpper($p));
}

$mdPaths = array(
    'docs/market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md',
    'docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md',
    'docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md',
    'docs/watchlist/authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md',
    'docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md'
);
$marketLock = array();
foreach ($mdPaths as $rel) {
    $p = $repoRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (is_file($p)) {
        $marketLock[] = array('path' => $rel, 'sha1' => sha1FileUpper($p));
    }
}

$matrixRel = 'docs/watchlist/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$matrixPath = $repoRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $matrixRel);
if (!is_file($matrixPath)) fail('Missing traceability matrix.');

$gitCommit = 'N/A'; $branch = 'N/A'; $tree = 'N/A'; $diffSha = 'N/A';
list($gitRc, $gitTop) = runCmd('git rev-parse --show-toplevel', $repoRoot);
if ($gitRc === 0 && $gitTop !== '') {
    list($rc1, $gitCommit) = runCmd('git rev-parse HEAD', $repoRoot);
    list($rc2, $branch) = runCmd('git rev-parse --abbrev-ref HEAD', $repoRoot);
    list($rc3, $status) = runCmd('git status --porcelain', $repoRoot);
    if ($rc1 !== 0 || $rc2 !== 0 || $rc3 !== 0) fail('Cannot read Git identity.');
    if ($status === '') {
        $tree = 'CLEAN';
    } else {
        $tree = 'DIRTY_DECLARED';
        $diffSha = strtoupper(sha1($status));
    }
} elseif ($mode !== 'DOCUMENTATION_GOVERNANCE') {
    fail('Git repository/source revision is required for IMPLEMENTATION/AUDIT_READONLY baseline.');
}

$composerRel = 'composer.lock';
$composerPath = $repoRoot . DIRECTORY_SEPARATOR . $composerRel;
$composerSha = is_file($composerPath) ? sha1FileUpper($composerPath) : 'N/A';

$payload = array(
    'document_type' => 'EVIDENCE',
    'status' => 'FINAL',
    'scope' => 'watchlist / weekly_swing',
    'verification_epoch' => $verificationEpoch,
    'record_id' => $recordId,
    'baseline_id' => $baselineId,
    'stage_id' => $stage,
    'attempt_id' => $attempt,
    'work_id' => $attempt,
    'baseline_mode' => $mode,
    'created_at' => date(DATE_ATOM),
    'source_repository' => array(
        'git_commit' => $gitCommit,
        'branch' => $branch,
        'working_tree' => $tree,
        'dirty_diff_sha1' => $diffSha,
        'repo_root' => str_replace('\\', '/', $repoRoot)
    ),
    'strategy_authority_lock' => $strategy,
    'governance_authority_lock' => $govLock,
    'market_data_contract_lock' => $marketLock,
    'traceability_matrix' => array(
        'path' => $matrixRel,
        'sha1' => sha1FileUpper($matrixPath),
        'verification_build_stage' => $stage,
        'rule_ids' => array()
    ),
    'dependency_lock' => array(
        'composer_lock_sha1' => $composerSha,
        'other' => array()
    ),
    'schema_migration_lock' => array(
        'migration_head' => 'N/A',
        'schema_identity' => 'N/A',
        'reason' => 'Populate when stage touches persistence/schema.'
    ),
    'toolchain' => array(
        'php' => PHP_VERSION,
        'database' => 'N/A',
        'os' => PHP_OS
    ),
    'notes' => 'Generated before implementation change. Issued baseline is immutable.'
);

$outPath = $output;
if (!preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $outPath)) {
    $outPath = $repoRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $outPath);
}
$dir = dirname($outPath);
if (!is_dir($dir) && !mkdir($dir, 0777, true)) fail('Cannot create output directory.');
if (is_file($outPath)) fail('Refusing to overwrite existing baseline evidence: ' . $outPath);
file_put_contents($outPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

// Register current baseline evidence for searchable Work/Attempt correlation.
$registry = $watchlistRoot . DIRECTORY_SEPARATOR . 'records' . DIRECTORY_SEPARATOR . 'WORK_RECORD_REGISTRY.csv';
if (is_file($registry) && $mode !== 'DOCUMENTATION_GOVERNANCE') {
    $fhRead = fopen($registry, 'r');
    $headers = fgetcsv($fhRead);
    if (isset($headers[0])) $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    while (($row = fgetcsv($fhRead)) !== false) {
        $a = array(); foreach ($headers as $i => $k) $a[$k] = isset($row[$i]) ? trim($row[$i]) : '';
        if ($a['record_id'] === $recordId) { fclose($fhRead); fail('Record ID already exists in Work Record Registry.'); }
        if ($a['baseline_id'] === $baselineId) { fclose($fhRead); fail('Baseline ID already exists and cannot be reused: ' . $baselineId); }
        if ($a['attempt_id'] === $attempt && $a['record_type'] === 'WORK_BASELINE_LOCK') { fclose($fhRead); fail('Attempt already has a WORK_BASELINE_LOCK: ' . $attempt); }
        if ($a['attempt_id'] === $attempt && $a['baseline_id'] !== '' && $a['baseline_id'] !== $baselineId) { fclose($fhRead); fail('Attempt already carries a different Baseline ID: ' . $a['baseline_id']); }
    }
    fclose($fhRead);
    $fh = fopen($registry, 'a');
    fputcsv($fh, array($recordId,'WORK_BASELINE_LOCK',$stage,$attempt,$attempt,$baselineId,'FINAL',relPath($outPath,$repoRoot),'','','','','',date(DATE_ATOM),'generated baseline'));
    fclose($fh);
}


// Register baseline physical document in one-role + current-verification registries.
$roleRegistry = $watchlistRoot . DIRECTORY_SEPARATOR . 'authority' . DIRECTORY_SEPARATOR . 'governance' . DIRECTORY_SEPARATOR . 'DOCUMENT_ROLE_REGISTRY.csv';
$verificationRegistry = $watchlistRoot . DIRECTORY_SEPARATOR . 'authority' . DIRECTORY_SEPARATOR . 'governance' . DIRECTORY_SEPARATOR . 'CURRENT_VERIFICATION_REGISTRY.csv';
if (!is_file($roleRegistry) || !is_file($verificationRegistry)) fail('Document role/current verification registry missing.');
$docRel = relPath($outPath, $repoRoot);
foreach (array($roleRegistry,$verificationRegistry) as $rp) {
    $fhCheck=fopen($rp,'r');$hh=fgetcsv($fhCheck);if(isset($hh[0]))$hh[0]=preg_replace('/^\xEF\xBB\xBF/','',$hh[0]);
    while(($rr=fgetcsv($fhCheck))!==false){$aa=array();foreach($hh as $i=>$k)$aa[$k]=isset($rr[$i])?trim($rr[$i]):'';if(isset($aa['document_path'])&&$aa['document_path']===$docRel){fclose($fhCheck);fail('Document already registered: '.$docRel);}}
    fclose($fhCheck);
}
$fh=fopen($roleRegistry,'a');fputcsv($fh,array($docRel,'EVIDENCE','IMMUTABLE_RESULT_OR_PROOF','IMMUTABLE_AFTER_ISSUE','YES','YES','BASELINE_GENERATOR',''));fclose($fh);
$fh=fopen($verificationRegistry,'a');fputcsv($fh,array($docRel,'EVIDENCE',$verificationEpoch,'NO','CURRENT_WORK_RECORD','CURRENT_ATTEMPT_EVIDENCE','NO','NO','','Current Work Baseline Lock; provenance evidence, not rule-satisfaction evidence by itself.'));fclose($fh);

echo 'BASELINE_CREATED ' . relPath($outPath, $repoRoot) . PHP_EOL;
echo 'BASELINE_SHA1 ' . sha1FileUpper($outPath) . PHP_EOL;
