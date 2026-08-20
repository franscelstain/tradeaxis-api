<?php
/**
 * PHP 7.3+; create an immutable Work Baseline Lock JSON.
 *
 * `WORK_BASELINE_LOCK_STANDARD.md` requires every lock to bind, at minimum: strategy freeze ID and
 * fingerprints, governance fingerprints, verification epoch, traceability matrix hash, repository
 * revision, working-tree state, schema/config/dependency/toolchain identity, and relevant external
 * dependency contract identity.
 *
 * The earlier version emitted eleven fields and omitted four of those, so a lock produced by running
 * the tool as documented was not standard-conformant and every attempt had to augment its output by
 * hand. Recorded as F-MD-B00-A001-004. This version derives the missing four itself.
 *
 * Required:  --stage --attempt --baseline --output
 * Optional:  --revision --working-tree --schema-config --toolchain --note
 *            (revision and working-tree are derived from git when omitted)
 */

function opt($n, $d = null)
{
    global $argv;
    $p = '--'.$n.'=';
    foreach ($argv as $a) {
        if (strpos($a, $p) === 0) {
            return substr($a, strlen($p));
        }
    }

    return $d;
}

function failx($m)
{
    fwrite(STDERR, $m.PHP_EOL);
    exit(2);
}

function sh($p)
{
    return is_file($p) ? strtoupper(sha1_file($p)) : 'ABSENT';
}

/** Deterministic fingerprint of a directory tree: sorted "<posix path>\0<UPPER SHA1>\n" concatenation. */
function treeFingerprint($dir)
{
    if (! is_dir($dir)) {
        return ['sha1' => 'ABSENT', 'count' => 0];
    }
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile()) {
            $files[] = str_replace('\\', '/', $f->getPathname());
        }
    }
    sort($files);
    $acc = '';
    foreach ($files as $p) {
        $acc .= $p."\0".strtoupper(sha1_file($p))."\n";
    }

    return ['sha1' => strtoupper(sha1($acc)), 'count' => count($files)];
}

function git($args, $cwd)
{
    $out = [];
    $code = 0;
    exec('git -C '.escapeshellarg($cwd).' '.$args.' 2>&1', $out, $code);

    return $code === 0 ? trim(implode("\n", $out)) : null;
}

$md = realpath(dirname(__DIR__, 3));
if (! $md) {
    failx('Cannot resolve market_data root.');
}
$repo = realpath($md.'/../..');

$stage = opt('stage');
$attempt = opt('attempt');
$baseline = opt('baseline');
$out = opt('output');

if (! $stage || ! $attempt || ! $baseline || ! $out) {
    failx('Required --stage --attempt --baseline --output');
}
if (! preg_match('/^MD-B\d{2}$/', $stage) || ! preg_match('/^MD-B\d{2}-A\d{3}$/', $attempt)) {
    failx('Invalid Stage/Attempt ID.');
}
if (strpos($baseline, $attempt) !== 0) {
    failx('Baseline ID must begin with its Attempt ID, got '.$baseline.' for '.$attempt.'.');
}

$epochPath = $md.'/authority/governance/CURRENT_VERIFICATION_EPOCH.json';
$manifestPath = $md.'/authority/governance/MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json';
$matrixPath = $md.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$depPath = $md.'/development/implementation/MD_DEPENDENCY_REGISTRY.csv';

$epoch = json_decode(file_get_contents($epochPath), true);
$manifest = json_decode(file_get_contents($manifestPath), true);
if (! $epoch || ! $manifest) {
    failx('Cannot parse verification epoch or strategy freeze manifest.');
}

// external dependency contract identity, read from the registry rather than asserted
$dependencies = ['registry' => 'development/implementation/MD_DEPENDENCY_REGISTRY.csv', 'registered' => 0, 'open_blocking' => []];
if (is_file($depPath)) {
    $f = fopen($depPath, 'r');
    $h = fgetcsv($f);
    if (isset($h[0])) {
        $h[0] = preg_replace('/^\xEF\xBB\xBF/', '', $h[0]);
    }
    $idx = array_flip($h);
    while (($r = fgetcsv($f)) !== false) {
        if (count($r) < count($h)) {
            continue;
        }
        $dependencies['registered']++;
        if (isset($idx['status'], $idx['dependency_id']) && strpos($r[$idx['status']], 'OPEN_BLOCKING') === 0) {
            $dependencies['open_blocking'][] = $r[$idx['dependency_id']];
        }
    }
    fclose($f);
}

$revision = opt('revision');
if ($revision === null) {
    $revision = git('rev-parse HEAD', $repo) ?: 'UNKNOWN';
}

$workingTree = opt('working-tree');
if ($workingTree === null) {
    $porcelain = git('status --porcelain', $repo);
    $workingTree = ($porcelain === null || $porcelain === '') ? 'CLEAN' : 'DIRTY+'.strtoupper(sha1($porcelain));
}

$strategyTree = treeFingerprint($md.'/authority/strategy');
$governanceTree = treeFingerprint($md.'/authority/governance');
$appTree = treeFingerprint($repo.'/app');
$testTree = treeFingerprint($repo.'/tests');

$data = [
    'baseline_id' => $baseline,
    'stage_id' => $stage,
    'attempt_id' => $attempt,
    'verification_epoch' => $epoch['verification_epoch'],
    'verification_epoch_sha1' => sh($epochPath),
    'strategy_freeze_id' => isset($manifest['freeze_id']) ? $manifest['freeze_id'] : 'UNKNOWN',
    'strategy_freeze_manifest_sha1' => sh($manifestPath),
    'strategy_tree_fingerprint_sha1' => $strategyTree['sha1'],
    'strategy_document_count' => isset($manifest['strategy_document_count']) ? $manifest['strategy_document_count'] : count($manifest['documents']),
    'governance_fingerprint_sha1' => $governanceTree['sha1'],
    'governance_document_count' => $governanceTree['count'],
    'traceability_matrix_sha1' => sh($matrixPath),
    'source_revision' => $revision,
    'source_revision_branch' => git('rev-parse --abbrev-ref HEAD', $repo) ?: 'UNKNOWN',
    'working_tree' => $workingTree,
    'app_code_fingerprint_sha1' => $appTree['sha1'],
    'tests_fingerprint_sha1' => $testTree['sha1'],
    'schema_config_identity' => opt('schema-config', implode(';', [
        'schema:'.sh($md.'/development/implementation/db/Database_Schema_MariaDB.sql'),
        'config:'.sh($repo.'/config/market_data.php'),
        'migrations:'.treeFingerprint($repo.'/database/migrations')['sha1'],
    ])),
    'dependency_identity' => [
        'composer_lock_sha1' => sh($repo.'/composer.lock'),
        'phpunit_xml_sha1' => sh($repo.'/phpunit.xml'),
    ],
    'toolchain_identity' => opt('toolchain', 'PHP '.PHP_VERSION),
    'external_dependency_contract_identity' => $dependencies,
    'fingerprint_method' => 'SHA1 over sorted "<posix_path>\\0<UPPER_SHA1>\\n" concatenation for tree fingerprints; plain SHA1 for single files.',
    'mutability' => 'IMMUTABLE_AFTER_ISSUE',
    'created_at' => date(DATE_ATOM),
];

$note = opt('note');
if ($note !== null && $note !== '') {
    $data['issuance_note'] = $note;
}

if (file_exists($out)) {
    failx('Output already exists: '.$out);
}
if (! is_dir(dirname($out))) {
    mkdir(dirname($out), 0777, true);
}
file_put_contents($out, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
echo $out.PHP_EOL;
