<?php
require_once __DIR__.'/MarketDataB10SuccessorBinding.php';
require_once __DIR__.'/MarketDataPublicationLifecycleProofGate.php';
require_once __DIR__.'/MarketDataPublicationLifecycleTraceabilityGate.php';

/**
 * Fail-closed proof for MarketDataB10SuccessorBinder (F-MD-B10-A002-006) and the successor-aware B10 gates.
 *
 * Every case drives the real binder process against temporary copies of the matrix, the evidence records and the
 * relationship registry; the canonical matrix is read, never written. A negative case passes only when the binder
 * refuses with the diagnostic named for that case AND the temporary matrix is byte-identical afterwards. The controls
 * run first: if a control is red every verdict after it is meaningless, and the run says so instead of reporting them.
 *
 * Usage: php MarketDataB10SuccessorBinderSelfTest.php [--skip-package-copy] [--only=name,name] [--pristine-matrix=PATH]
 * Once the canonical matrix has been promoted it is no longer the pristine A002 starting state the cases mutate, so a
 * regression run supplies that state (the promotion package keeps it as promotion/matrix_before.csv) and the self-test
 * additionally proves that the promoted canonical matrix is recognised as already bound.
 * --only runs the controls and the named negative/atomicity cases only (used to mutation-probe the binder).
 */
$root = dirname(__DIR__, 5);
$binder = __DIR__.'/MarketDataB10SuccessorBinder.php';
$attempt = 'MD-B10-A002';
$layers = ['successors' => MarketDataB10SuccessorBinding::profilesUpTo($attempt)];
$evidenceId = 'E-MD-B10-A002-017';
$canonicalMatrix = MarketDataB10SuccessorBinding::canonicalMatrix($root);
$canonicalBefore = hash_file('sha256', $canonicalMatrix);
$sourceMatrix = $canonicalMatrix;
foreach ($argv as $arg) {
    if (strpos($arg, '--pristine-matrix=') === 0) {
        $sourceMatrix = substr($arg, strlen('--pristine-matrix='));
    }
}
$evidenceSource = $root.'/docs/market_data/records/evidence';
$relationshipSource = $root.'/docs/market_data/records/WORK_RELATIONSHIP_REGISTRY.csv';

function sb_rrmdir(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) {
        $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
    }
    @rmdir($dir);
}

$work = sys_get_temp_dir().DIRECTORY_SEPARATOR.'md_b10_binder_selftest_'.bin2hex(random_bytes(4));
mkdir($work, 0777, true);
register_shutdown_function(static function () use ($work) { sb_rrmdir($work); });

/** A fresh world: matrix, the three evidence records and the relationship registry, copied. */
function sb_world(string $work, string $name, string $canonicalMatrix, string $evidenceSource, string $relationshipSource): array
{
    $dir = $work.DIRECTORY_SEPARATOR.$name;
    mkdir($dir.DIRECTORY_SEPARATOR.'evidence', 0777, true);
    copy($canonicalMatrix, $dir.'/matrix.csv');
    foreach (['E-MD-B10-A001-001', 'E-MD-B10-A002-001', 'E-MD-B10-A002-017'] as $id) {
        foreach (glob($evidenceSource.'/'.$id.'_*') as $file) {
            copy($file, $dir.'/evidence/'.basename($file));
        }
    }
    copy($relationshipSource, $dir.'/relationships.csv');

    return ['dir' => $dir, 'matrix' => $dir.'/matrix.csv', 'evidence' => $dir.'/evidence', 'relationships' => $dir.'/relationships.csv'];
}

function sb_run(array $world, string $binder, string $attempt, string $evidenceId, array $extra = [], string $packageBase = null, array $env = []): array
{
    global $root;
    $args = ['--attempt='.$attempt, '--evidence-id='.$evidenceId, '--matrix='.$world['matrix'], '--evidence-dir='.$world['evidence'],
        '--relationships='.$world['relationships'], '--package-base='.($packageBase ?? $root)];
    $cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg($binder).' '.implode(' ', array_map('escapeshellarg', array_merge($args, $extra))).' 2>&1';
    foreach ($env as $k => $v) {
        putenv($k.'='.$v);
    }
    exec($cmd, $output, $exit);
    foreach ($env as $k => $v) {
        putenv($k);
    }
    $text = implode("\n", $output);
    $json = json_decode($text, true);

    return ['exit' => $exit, 'json' => is_array($json) ? $json : null, 'text' => $text];
}

function sb_json_edit(string $file, callable $edit): void
{
    $payload = json_decode((string) file_get_contents($file), true);
    $edit($payload);
    file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
}

function sb_evidence_file(array $world, string $id): string
{
    return glob($world['evidence'].'/'.$id.'_*')[0];
}

/** Rewrite the first matrix line of $rule using a plain string replacement on that line only. */
function sb_matrix_line(array $world, string $rule, string $from, string $to): void
{
    $raw = file_get_contents($world['matrix']);
    $pos = strpos($raw, "\n".$rule.',');
    if ($pos === false) {
        throw new RuntimeException('rule not found '.$rule);
    }
    $end = strpos($raw, "\n", $pos + 1);
    $line = substr($raw, $pos + 1, $end - $pos - 1);
    if (strpos($line, $from) === false) {
        throw new RuntimeException('anchor not on line '.$rule.': '.$from);
    }
    file_put_contents($world['matrix'], substr($raw, 0, $pos + 1).str_replace($from, $to, $line).substr($raw, $end));
}

$affected = [];
$e = [];
$scope = MarketDataB10SuccessorBinding::loadScope(MarketDataB10SuccessorBinding::paths($root), MarketDataB10SuccessorBinding::profile($attempt), $e);
$affected = $scope['affected'];
$affectedRule = $affected[10];
$unaffectedRule = null;
foreach ($scope['review'] as $rule => $entry) {
    if (! in_array($rule, $affected, true) && ($entry['required_current_status'] ?? '') === 'SATISFIED') {
        $unaffectedRule = $rule;
        break;
    }
}

$only = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--only=') === 0) {
        $only = explode(',', substr($arg, 7));
    }
}
$wanted = static function (string $name) use ($only): bool {
    return $only === null || in_array($name, $only, true);
};
$checks = [];
$notes = [];
$check = static function (string $name, bool $ok, string $note = '') use (&$checks, &$notes) {
    $checks[$name] = $ok;
    if (! $ok && $note !== '') {
        $notes[$name] = $note;
    }
};
// The diagnostic must be the one the binder itself raised for that hazard: it has to START the error entry. A token that
// merely appears inside a later gate's message ("POST_STATE_GATE: proof gate: ...") would let a removed guard hide behind
// the closure gates that repeat it.
$refuses = static function (array $r, string $token): bool {
    $errors = $r['json']['errors'] ?? [];
    $hit = false;
    foreach ($errors as $err) {
        if (strpos($err, $token) === 0) {
            $hit = true;
        }
    }

    return $r['exit'] !== 0 && $hit;
};

// ---- Controls (must be green or nothing after them is read) -----------------------------------------------------
$w = sb_world($work, 'control_validate', $sourceMatrix, $evidenceSource, $relationshipSource);
$before = hash_file('sha256', $w['matrix']);
$r = sb_run($w, $binder, $attempt, $evidenceId);
$check('control_validate_only_passes_and_writes_nothing', $r['exit'] === 0 && ($r['json']['status'] ?? '') === 'PASS'
    && ($r['json']['diagnostics']['affected'] ?? 0) === count($affected) && hash_file('sha256', $w['matrix']) === $before, $r['text']);
$plannedAfter = $r['json']['matrix_sha256_planned_after'] ?? null;

$w = sb_world($work, 'control_apply', $sourceMatrix, $evidenceSource, $relationshipSource);
$beforeRaw = file_get_contents($w['matrix']);
$r = sb_run($w, $binder, $attempt, $evidenceId, ['--apply']);
$afterRaw = file_get_contents($w['matrix']);
$beforeLines = explode("\n", $beforeRaw);
$afterLines = explode("\n", $afterRaw);
$changedRules = [];
foreach ($afterLines as $i => $line) {
    if (($beforeLines[$i] ?? null) !== $line) {
        $changedRules[] = explode(',', $line, 2)[0];
    }
}
sort($changedRules);
$expectedRules = $affected;
sort($expectedRules);
$check('control_apply_promotes_exactly_the_reopened_rules', $r['exit'] === 0 && ($r['json']['status'] ?? '') === 'PASS'
    && $changedRules === $expectedRules && count($afterLines) === count($beforeLines)
    && hash('sha256', $afterRaw) === $plannedAfter, $r['text']);
$after = MarketDataB10SuccessorBinding::readMatrix($w['matrix']);
$sat = 0;
$affectedBound = 0;
foreach ($after['rows'] as $row) {
    if ($row['active'] === 'YES' && $row['primary_stage'] === 'MD-B10' && $row['coverage_requirement'] === 'REQUIRED' && $row['applicability'] === 'MANDATORY') {
        $sat += $row['coverage_status'] === 'SATISFIED' ? 1 : 0;
        $affectedBound += in_array($row['rule_id'], $affected, true) && $row['current_evidence_ids'] === $evidenceId ? 1 : 0;
    }
}
$check('control_apply_end_state_is_1072_satisfied_with_the_successor_evidence_on_the_reopened_rows', $sat === 1072 && $affectedBound === count($affected));
$beforeParsed = MarketDataB10SuccessorBinding::readMatrix($sourceMatrix)['rows'];
$foreignFieldChanges = 0;
$changedFieldNames = [];
foreach ($after['rows'] as $i => $row) {
    foreach ($row as $field => $value) {
        if ($value !== $beforeParsed[$i][$field]) {
            $changedFieldNames[$field] = true;
            if (! in_array($field, MarketDataB10SuccessorBinding::CHANGED_FIELDS, true)) {
                $foreignFieldChanges++;
            }
        }
    }
}
$check('control_apply_changes_only_the_three_governed_fields', $foreignFieldChanges === 0
    && array_keys($changedFieldNames) !== [] && count(array_diff(array_keys($changedFieldNames), MarketDataB10SuccessorBinding::CHANGED_FIELDS)) === 0);
$idem = sb_run($w, $binder, $attempt, $evidenceId, ['--apply']);
$check('control_apply_is_idempotent', $idem['exit'] === 0 && ($idem['json']['already_bound'] ?? false) === true && file_get_contents($w['matrix']) === $afterRaw, $idem['text']);
$boundRows = $after['rows'];
$gateProof = MarketDataPublicationLifecycleProofGate::validate($root, true, $layers + ['rows' => $boundRows]);
$gateTrace = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, $layers + ['rows' => $boundRows]);
$check('control_post_state_satisfies_the_b10_proof_gate_bound', $gateProof['status'] === 'PASS', json_encode($gateProof['errors']));
$check('control_post_state_satisfies_the_b10_traceability_gate_bound', $gateTrace['status'] === 'PASS', json_encode($gateTrace['errors']));

if ($sourceMatrix !== $canonicalMatrix) {
    $canonicalWorld = ['matrix' => $canonicalMatrix, 'evidence' => $evidenceSource, 'relationships' => $relationshipSource];
    $latest = array_values(MarketDataB10SuccessorBinding::profiles());
    $latest = end($latest);
    // the latest layer's proof evidence is E-MD-B10-<attempt>-002 by this attempt's convention (scope evidence is -001)
    $latestEvidence = $latest['attempt'] === $attempt ? $evidenceId : 'E-'.$latest['attempt'].'-002';
    $already = sb_run($canonicalWorld, $binder, $latest['attempt'], $latestEvidence);
    $check('control_promoted_canonical_matrix_is_recognised_as_already_bound', $already['exit'] === 0 && ($already['json']['already_bound'] ?? false) === true
        && hash_file('sha256', $canonicalMatrix) === $canonicalBefore, $already['text']);
}

$controlsGreen = ! in_array(false, $checks, true);
if (! $controlsGreen) {
    $out = ['status' => 'FAIL', 'verdict' => 'CONTROL_RED: no later verdict is meaningful', 'checks' => $checks, 'notes' => $notes];
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(1);
}

// ---- Negative cases: each refuses with its own diagnostic and leaves the matrix byte-identical -------------------
$negative = static function (string $name, string $token, callable $mutate, array $extra = [], array $args = null, array $env = [], string $packageBase = null)
    use (&$check, $wanted, $work, $sourceMatrix, $evidenceSource, $relationshipSource, $binder, $attempt, $evidenceId, $refuses) {
    if (! $wanted($name)) {
        return;
    }
    $w = sb_world($work, $name, $sourceMatrix, $evidenceSource, $relationshipSource);
    $mutate($w);
    $before = file_get_contents($w['matrix']);
    $r = sb_run($w, $binder, $args[0] ?? $attempt, $args[1] ?? $evidenceId, $extra, $packageBase, $env);
    $leftovers = glob($w['dir'].'/matrix.csv.*');
    $check($name, $refuses($r, $token) && file_get_contents($w['matrix']) === $before && $leftovers === [], $r['text'].' leftovers='.json_encode($leftovers));
};

$negative('wrong_attempt_not_registered', 'ATTEMPT_NOT_REGISTERED', static function ($w) {}, [], ['MD-B10-A004', 'E-MD-B10-A004-001']);
$negative('predecessor_attempt_is_not_a_successor', 'ATTEMPT_NOT_REGISTERED', static function ($w) {}, [], ['MD-B10-A001', 'E-MD-B10-A001-001']);
$negative('invalid_evidence_id', 'EVIDENCE_ID_NOT_A_MD-B10-A002_EVIDENCE', static function ($w) {}, [], [$attempt, 'BAD-EVIDENCE']);
$negative('predecessor_evidence_id_refused', 'EVIDENCE_ID_NOT_A_MD-B10-A002_EVIDENCE', static function ($w) {}, [], [$attempt, 'E-MD-B10-A001-001']);
$negative('wrong_baseline_in_successor_evidence', 'SUCCESSOR_EVIDENCE_WRONG_BASELINE_ID', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['baseline_id'] = 'MD-B10-A002-BL999'; });
});
$negative('wrong_attempt_in_successor_evidence', 'SUCCESSOR_EVIDENCE_WRONG_ATTEMPT_ID', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['attempt_id'] = 'MD-B10-A001'; });
});
$negative('wrong_change_impact_in_successor_evidence', 'SUCCESSOR_EVIDENCE_WRONG_CHANGE_IMPACT_DECLARATION', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['change_impact_declaration'] = 'CI-MD-B10-A001-001'; });
});
$negative('successor_evidence_not_immutable', 'SUCCESSOR_EVIDENCE_NOT_IMMUTABLE', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['mutability'] = 'MUTABLE_UNTIL_RESOLVED'; });
});
$negative('only_55_of_56_proven_in_the_evidence', 'SUCCESSOR_EVIDENCE_MISSING_RULES', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { array_pop($p['matrix']); });
});
$negative('evidence_proves_a_57th_rule', 'SUCCESSOR_EVIDENCE_EXTRA_RULES', static function ($w) use ($evidenceId, $unaffectedRule) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) use ($unaffectedRule) {
        $row = $p['matrix'][0];
        $row['rule_id'] = $unaffectedRule;
        $p['matrix'][] = $row;
    });
});
$negative('one_affected_rule_not_proven_in_the_evidence', 'SUCCESSOR_EVIDENCE_RULE_NOT_PROVEN', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['matrix'][3]['result'] = 'INCOMPLETE'; });
});
$negative('evidence_counts_report_an_incomplete_rule', 'SUCCESSOR_EVIDENCE_HAS_INCOMPLETE', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['result_counts']['INCOMPLETE'] = 1; $p['result_counts']['PROVEN_CURRENT'] = 55; });
});
$negative('scope_record_lists_only_55_affected', 'SCOPE_EVIDENCE_COUNTS_DISAGREE', static function ($w) {
    sb_json_edit(sb_evidence_file($w, 'E-MD-B10-A002-001'), static function (&$p) { array_pop($p['affected_current_predicates']); });
});
$negative('scope_record_adds_an_unaffected_rule_as_57th', 'AFFECTED_ROW_UNEXPECTED_STATE', static function ($w) use ($unaffectedRule) {
    sb_json_edit(sb_evidence_file($w, 'E-MD-B10-A002-001'), static function (&$p) use ($unaffectedRule) {
        $p['affected_current_predicates'][] = $unaffectedRule;
        $p['review_method']['affected'] = 57;
    });
});
$negative('one_affected_row_already_satisfied_on_the_predecessor', 'AFFECTED_ROW_UNEXPECTED_STATE', static function ($w) use ($affectedRule) {
    sb_matrix_line($w, $affectedRule, ',YES,NOT_ASSESSED,,', ',YES,SATISFIED,E-MD-B10-A001-001,');
});
$negative('one_affected_row_in_an_unknown_status', 'AFFECTED_ROW_UNEXPECTED_STATE', static function ($w) use ($affectedRule) {
    sb_matrix_line($w, $affectedRule, ',YES,NOT_ASSESSED,,', ',YES,IN_PROGRESS,,');
});
$negative('partial_promotion_state_is_refused', 'PARTIAL_PROMOTION_STATE', static function ($w) use ($affectedRule, $evidenceId) {
    sb_matrix_line($w, $affectedRule, ',YES,NOT_ASSESSED,,', ',YES,SATISFIED,'.$evidenceId.',');
});
$negative('denominator_mismatch_one_mandatory_row_deactivated', 'DENOMINATOR_MISMATCH', static function ($w) use ($unaffectedRule) {
    sb_matrix_line($w, $unaffectedRule, ',YES,SATISFIED,', ',NO,SATISFIED,');
});
$negative('unaffected_row_evidence_rewritten', 'UNAFFECTED_ROW_UNEXPECTED_STATE', static function ($w) use ($unaffectedRule) {
    sb_matrix_line($w, $unaffectedRule, ',YES,SATISFIED,E-MD-B10-A001-001,', ',YES,SATISFIED,E-MD-B10-A001-002,');
});
$negative('unaffected_row_returned_to_not_assessed', 'UNAFFECTED_ROW_UNEXPECTED_STATE', static function ($w) use ($unaffectedRule) {
    sb_matrix_line($w, $unaffectedRule, ',YES,SATISFIED,E-MD-B10-A001-001,', ',YES,NOT_ASSESSED,,');
});
$negative('unaffected_row_bound_to_the_successor_evidence', 'UNAFFECTED_ROW_UNEXPECTED_STATE', static function ($w) use ($unaffectedRule, $evidenceId) {
    sb_matrix_line($w, $unaffectedRule, ',YES,SATISFIED,E-MD-B10-A001-001,', ',YES,SATISFIED,'.$evidenceId.',');
});
$negative('matrix_has_crlf_line_endings', 'MATRIX_NOT_LF_ONLY', static function ($w) {
    file_put_contents($w['matrix'], str_replace("\n", "\r\n", file_get_contents($w['matrix'])));
});
$negative('successor_evidence_not_correlated_to_its_baseline', 'SUCCESSOR_EVIDENCE_RELATIONSHIP_MISSING', static function ($w) use ($evidenceId) {
    $lines = explode("\n", file_get_contents($w['relationships']));
    $kept = array_values(array_filter($lines, static function ($l) use ($evidenceId) {
        return strpos($l, ','.$evidenceId.',MD-B10-A002-BL001,') === false;
    }));
    file_put_contents($w['relationships'], implode("\n", $kept));
});
$negative('corrupted_manifest_hash_in_the_evidence', 'PACKAGE_MANIFEST_HASH_MISMATCH', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { $p['raw_proof']['manifest_sha256'] = str_repeat('0', 64); });
});
$negative('missing_raw_package_linkage', 'SUCCESSOR_EVIDENCE_RAW_LINKAGE_MISSING', static function ($w) use ($evidenceId) {
    sb_json_edit(sb_evidence_file($w, $evidenceId), static function (&$p) { unset($p['raw_proof']); });
});
$negative('missing_successor_evidence_record', 'GOVERNED_EVIDENCE_CARDINALITY_INVALID', static function ($w) use ($evidenceId) {
    unlink(sb_evidence_file($w, $evidenceId));
});

// A raw proof package whose member no longer matches its manifest (the manifest itself is intact).
if (! in_array('--skip-package-copy', $argv, true) && ($wanted('package_member_modified_after_issue') || $wanted('package_gains_an_unlisted_member'))) {
    $payload = json_decode((string) file_get_contents(sb_evidence_file(sb_world($work, 'probe_pkg', $sourceMatrix, $evidenceSource, $relationshipSource), $evidenceId)), true);
    $package = $payload['raw_proof']['package'];
    $copyBase = $work.DIRECTORY_SEPARATOR.'package_base';
    $target = $copyBase.'/'.$package;
    mkdir($target, 0777, true);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$package, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $entry) {
        $dest = $target.'/'.str_replace('\\', '/', $it->getSubPathname());
        if ($entry->isDir()) {
            @mkdir($dest, 0777, true);
        } else {
            copy($entry->getPathname(), $dest);
        }
    }
    $manifest = json_decode((string) file_get_contents($target.'/manifest.json'), true);
    $someMember = array_keys($manifest['files'])[5];
    file_put_contents($target.'/'.$someMember, 'tampered', FILE_APPEND);
    $negative('package_member_modified_after_issue', 'PACKAGE_MEMBER_HASH_MISMATCH', static function ($w) {}, [], null, [], $copyBase);
    file_put_contents($target.'/'.$someMember, substr((string) file_get_contents($target.'/'.$someMember), 0, -8));
    file_put_contents($target.'/an_unlisted_file.txt', 'x');
    $negative('package_gains_an_unlisted_member', 'PACKAGE_MEMBER_SET_CHANGED', static function ($w) {}, [], null, [], $copyBase);
}

// ---- Atomicity: a failure at each stage of the write leaves the original bytes -------------------------------------
foreach (['after_temp', 'after_rename', 'after_readback'] as $stage) {
    if (! $wanted('failure_injected_'.$stage.'_rolls_back_to_the_original_bytes')) {
        continue;
    }
    $w = sb_world($work, 'atomic_'.$stage, $sourceMatrix, $evidenceSource, $relationshipSource);
    $before = file_get_contents($w['matrix']);
    $r = sb_run($w, $binder, $attempt, $evidenceId, ['--apply', '--inject='.$stage], null, ['MD_B10_BINDER_TEST' => '1']);
    $check('failure_injected_'.$stage.'_rolls_back_to_the_original_bytes', $r['exit'] !== 0 && file_get_contents($w['matrix']) === $before
        && glob($w['dir'].'/matrix.csv.*') === [] && strpos($r['text'], 'INJECTED_FAILURE_'.strtoupper($stage)) !== false, $r['text']);
}
if ($wanted('failure_injection_is_refused_outside_the_test_environment')) {
    $w = sb_world($work, 'atomic_refused_without_test_env', $sourceMatrix, $evidenceSource, $relationshipSource);
    $before = file_get_contents($w['matrix']);
    $r = sb_run($w, $binder, $attempt, $evidenceId, ['--apply', '--inject=after_rename']);
    $check('failure_injection_is_refused_outside_the_test_environment', $r['exit'] !== 0 && file_get_contents($w['matrix']) === $before
        && strpos($r['text'], 'FAILURE_INJECTION_REFUSED') !== false, $r['text']);
}

// A planned post-state that the B10 gates refuse is never written.
if ($wanted('plan_is_refused_when_the_post_state_gates_fail')) {
    $w = sb_world($work, 'post_state_gate_refusal', $sourceMatrix, $evidenceSource, $relationshipSource);
    $before = file_get_contents($w['matrix']);
    $gatePlan = MarketDataB10SuccessorBinding::plan($root, $attempt, $evidenceId, ['matrix' => $w['matrix'], 'evidence_dir' => $w['evidence'],
        'relationships' => $w['relationships'], 'post_gates' => static function (array $rows) { return ['simulated closure gate failure']; }]);
    $gateApply = MarketDataB10SuccessorBinding::apply($gatePlan, MarketDataB10SuccessorBinding::paths($root, ['matrix' => $w['matrix']]), ['canonical' => false]);
    $check('plan_is_refused_when_the_post_state_gates_fail', $gatePlan['status'] === 'FAIL' && in_array('POST_STATE_GATE: simulated closure gate failure', $gatePlan['errors'], true)
        && $gateApply['applied'] === false && file_get_contents($w['matrix']) === $before, json_encode($gatePlan['errors']));
}

// A write that succeeds but fails post-write verification is rolled back by the library.
$w = sb_world($work, 'atomic_verify_failure', $sourceMatrix, $evidenceSource, $relationshipSource);
$before = file_get_contents($w['matrix']);
$plan = MarketDataB10SuccessorBinding::plan($root, $attempt, $evidenceId, ['matrix' => $w['matrix'], 'evidence_dir' => $w['evidence'], 'relationships' => $w['relationships']]);
$applied = MarketDataB10SuccessorBinding::apply($plan, MarketDataB10SuccessorBinding::paths($root, ['matrix' => $w['matrix']]), [
    'canonical' => false, 'verify' => static function () { return ['simulated post-write gate failure']; },
]);
$check('post_write_verification_failure_restores_the_original_bytes', $applied['applied'] === false && $applied['rolled_back'] === true
    && file_get_contents($w['matrix']) === $before, json_encode($applied));
$w = sb_world($work, 'atomic_matrix_moved', $sourceMatrix, $evidenceSource, $relationshipSource);
$plan = MarketDataB10SuccessorBinding::plan($root, $attempt, $evidenceId, ['matrix' => $w['matrix'], 'evidence_dir' => $w['evidence'], 'relationships' => $w['relationships']]);
file_put_contents($w['matrix'], file_get_contents($w['matrix'])."\n");
$moved = file_get_contents($w['matrix']);
$applied = MarketDataB10SuccessorBinding::apply($plan, MarketDataB10SuccessorBinding::paths($root, ['matrix' => $w['matrix']]), ['canonical' => false]);
$check('a_matrix_that_changed_since_the_plan_is_not_overwritten', $applied['applied'] === false && in_array('MATRIX_CHANGED_SINCE_PLAN', $applied['errors'], true)
    && file_get_contents($w['matrix']) === $moved);

// ---- The successor-aware B10 gates fail closed on the same hazards ------------------------------------------------
$boundRows = $after['rows'];
$gateFails = static function (array $mutatedRows, string $token) use ($root, $layers): bool {
    $proof = MarketDataPublicationLifecycleProofGate::validate($root, true, $layers + ['rows' => $mutatedRows]);
    $trace = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, $layers + ['rows' => $mutatedRows]);
    $all = array_merge($proof['errors'], $trace['errors']);
    foreach ($all as $err) {
        if (strpos($err, $token) !== false) {
            return $proof['status'] === 'FAIL' || $trace['status'] === 'FAIL';
        }
    }

    return false;
};
$mutateRow = static function (array $rows, string $rule, callable $f): array {
    foreach ($rows as &$row) {
        if ($row['rule_id'] === $rule) {
            $f($row);
        }
    }
    unset($row);

    return $rows;
};
$check('gate_refuses_a_reopened_rule_left_on_the_predecessor_evidence', $gateFails($mutateRow($boundRows, $affectedRule, static function (&$r) { $r['current_evidence_ids'] = 'E-MD-B10-A001-001'; }), 'BOUND_EVIDENCE_ID_INVALID'));
$check('gate_refuses_an_unaffected_rule_bound_to_the_successor_evidence', $gateFails($mutateRow($boundRows, $unaffectedRule, static function (&$r) use ($evidenceId) { $r['current_evidence_ids'] = $evidenceId; }), 'BOUND_EVIDENCE_ID_INVALID'));
$check('gate_refuses_two_successor_evidence_ids', $gateFails($mutateRow($boundRows, $affectedRule, static function (&$r) { $r['current_evidence_ids'] = 'E-MD-B10-A002-018'; }), 'BOUND_EVIDENCE_NOT_ATOMIC: MD-B10-A002'));
$check('gate_refuses_a_not_assessed_row_in_the_closure_state', $gateFails($mutateRow($boundRows, $unaffectedRule, static function (&$r) { $r['coverage_status'] = 'NOT_ASSESSED'; $r['current_evidence_ids'] = ''; }), 'COVERAGE_STATUS_INVALID'));
$check('traceability_gate_refuses_a_successor_row_without_successor_evidence', (function () use ($root, $boundRows, $mutateRow, $affectedRule, $layers) {
    $r = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, $layers + ['rows' => $mutateRow($boundRows, $affectedRule, static function (&$row) { $row['current_evidence_ids'] = 'E-MD-B10-A001-001'; })]);

    return $r['status'] === 'FAIL' && $r['invalid_mandatory_state'] === 1;
})());
// F-MD-B10-A002-001: the moved-row count is the measured 0 and a genuinely moved row still fails closed.
$tracePlain = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, $layers + ['rows' => $boundRows]);
$movedErrors = array_filter($tracePlain['errors'], static function ($err) { return strpos($err, 'moved count') === 0; });
$check('f001_moved_count_is_zero_on_the_canonical_shape', $tracePlain['moved'] === 0 && $movedErrors === [] && $tracePlain['status'] === 'PASS', json_encode($tracePlain['errors']));
$movedRows = $boundRows;
foreach ($movedRows as &$row) {
    if ($row['active'] === 'YES' && $row['primary_stage'] !== 'MD-B10') {
        $row['supporting_stages'] = trim($row['supporting_stages'].';MD-B10', ';');
        break;
    }
}
unset($row);
$movedResult = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, $layers + ['rows' => $movedRows]);
$check('f001_a_genuinely_moved_row_still_fails_closed', $movedResult['status'] === 'FAIL' && in_array('moved count must be 0', $movedResult['errors'], true) && $movedResult['moved'] === 1);
$movedAndMandatory = $boundRows;
foreach ($movedAndMandatory as &$row) {
    if ($row['active'] === 'YES' && $row['primary_stage'] === 'MD-B10' && $row['applicability'] === 'OPTIONAL_CAPABILITY') {
        $row['active'] = 'NO';
        break;
    }
}
unset($row);
$check('f001_the_other_traceability_counts_are_still_enforced', MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, $layers + ['rows' => $movedAndMandatory])['status'] === 'FAIL');
// A001 behaviour is preserved: with no successor registry, the original one-evidence-id model still passes and still fails.
$legacyRows = $boundRows;
foreach ($legacyRows as &$row) {
    if ($row['active'] === 'YES' && $row['primary_stage'] === 'MD-B10' && $row['coverage_requirement'] === 'REQUIRED' && $row['applicability'] === 'MANDATORY') {
        $row['current_evidence_ids'] = 'E-MD-B10-A001-001';
    }
}
unset($row);
$legacyTrace = MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, ['rows' => $legacyRows, 'successors' => []]);
$check('a001_legacy_mode_all_rows_on_one_a001_record_still_passes_the_traceability_gate', $legacyTrace['status'] === 'PASS', json_encode($legacyTrace['errors']));
$legacyBroken = $mutateRow($legacyRows, $unaffectedRule, static function (&$r) { $r['current_evidence_ids'] = $r['current_evidence_ids'].';X'; });
$check('a001_legacy_mode_still_refuses_a_malformed_evidence_binding', MarketDataPublicationLifecycleTraceabilityGate::validate($root, true, ['rows' => $legacyBroken, 'successors' => []])['status'] === 'FAIL');
$check('canonical_matrix_was_never_written', hash_file('sha256', $canonicalMatrix) === $canonicalBefore);

$failed = array_keys(array_filter($checks, static function ($ok) { return ! $ok; }));
$controls = count(array_filter(array_keys($checks), static function ($n) { return strpos($n, 'control_') === 0; }));
$result = [
    'status' => $failed === [] ? 'PASS' : 'FAIL',
    'total' => count($checks),
    'controls' => $controls,
    'fail_closed_cases' => count($checks) - $controls,
    'failed_checks' => $failed,
    'notes' => array_intersect_key($notes, array_flip($failed)),
    'checks' => $checks,
    'canonical_matrix_sha256' => $canonicalBefore,
];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed === [] ? 0 : 1);
