<?php
/**
 * PHP 7.3+; governed re-derivation of `coverage_requirement` for rows that section 2 of
 * `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` does not permit to be reference context.
 *
 * `MD-B01-A004` corrected one direction of `F-MD-B01-A001-001`: non-predicate rows carrying a
 * required obligation were demoted, and 817 predicate-bearing children of those demoted introducers
 * were promoted. The other direction the finding states — that the classification "excludes rules
 * that both can and should be" required — was never measured.
 *
 * This tool corrects the objectively provable part of that half: an enumerated run whose members
 * carry mixed classification. When the matrix itself marks some members of one homogeneous list
 * REQUIRED, the list demonstrably carries obligations, so a sibling demoted to REFERENCE_ONLY is
 * inconsistent by the matrix's own hand rather than by an outside judgement.
 *
 * Grammatical mood is never a criterion. The defect being corrected is precisely that rows stating
 * an obligation without a deontic modal ("Eligibility is not ranking", "Promote owns indicators")
 * were demoted while their modal-bearing siblings were kept.
 *
 * Usage:
 *   --apply             write the matrix (default is a dry run)
 *   --stage=MD-B01      stage whose mixed runs are re-derived
 *   --attempt=...       attempt id recorded in the row note
 *   --bind-a014-proof   additionally bind A014 evidence to the rows this attempt proved; the
 *                       binding is atomic and refuses to run against a row that is not an active
 *                       required rule. Promotion never advances a lifecycle on its own.
 *
 * Idempotent: re-running after a successful apply reports 0 promotions, because no mixed
 * classification run remains for the stage.
 */

$matrixPath = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';

function opt($n, $d = null)
{
    global $argv;
    foreach ($argv as $a) {
        if (strpos($a, '--'.$n.'=') === 0) {
            return substr($a, strlen($n) + 3);
        }
        if ($a === '--'.$n) {
            return true;
        }
    }

    return $d;
}

$apply = (bool) opt('apply', false);
$stage = opt('stage', 'MD-B01');
$attempt = opt('attempt', 'MD-B01-A014');
$bindProof = (bool) opt('bind-a014-proof', false);

/**
 * Proof binding for rows this attempt both promoted and proved. Promotion alone never advances a
 * lifecycle; a row appears here only when an executed, mutation-proven test establishes the whole
 * normalized predicate, not one clause of it.
 */
$proofBindings = [
    'MD-S020-R0068' => [
        'evidence' => 'E-MD-B01-A014-001',
        'proofs' => [
            'AliasNamingAndMeaningBoundaryTest::test_data_usable_is_the_canonical_field_and_eligible_is_only_its_alias',
            'ScopeProductAndTimeBoundaryTest::test_the_eligible_alias_is_derived_from_data_usable_and_never_independent',
        ],
        'surfaces' => ['config/market_data.php', 'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php'],
    ],
    'MD-S020-R0071' => [
        'evidence' => 'E-MD-B01-A014-001',
        'proofs' => [
            'AliasNamingAndMeaningBoundaryTest::test_no_configuration_key_or_api_field_is_named_with_the_alias',
            'AliasNamingAndMeaningBoundaryTest::test_a_data_usability_surface_states_the_canonical_name_beside_the_alias',
            'ScopeProductAndTimeBoundaryTest::test_the_eligible_alias_is_not_propagated_to_a_new_surface',
        ],
        'surfaces' => [
            'docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql',
            'docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql',
            'config/market_data.php',
            'app/Console/Commands/MarketData',
            'app/Http',
        ],
    ],
];

/**
 * Proof-owner moves, hand-verified per section 4. A row stays with the stage its already-REQUIRED
 * siblings hold unless its evidence plainly belongs elsewhere. A keyword classifier was written for
 * this at `MD-B01-A005` and rejected for proposing roughly a third wrong moves; the same applies here.
 */
$ownershipMoves = [
    'MD-S001-R0060' => ['MD-B15', 'delivery-coverage gate arithmetic; sibling MD-S001-R0059 already owns the 98% prerequisite at MD-B15'],
    'MD-S001-R0063' => ['MD-B02', 'provider source order and the manual one-date rescue path are the Yahoo bootstrap/provider-port contract'],
    'MD-S001-R0064' => ['MD-B09', 'conflict resolution is the dedup/conflict persistence contract'],
    'MD-S020-R0009' => ['MD-B12', 'semantic correctness and stable units/bases are proven by the coherent price-product engine'],
    'MD-S020-R0010' => ['MD-B07', 'source provenance, licensing disclosure, and schema validation belong to source observations/adapters'],
    'MD-S020-R0011' => ['MD-B05', 'temporal identity/universe correctness is the temporal mapping foundation; calendar/status supported by MD-B06'],
    'MD-S020-R0012' => ['MD-B15', 'coverage/integrity/correction safety is proven by the coverage expectation and delivery gate'],
    'MD-S020-R0013' => ['MD-B14', 'deterministic indicators and versioned configuration are the indicator engine contract'],
];

/**
 * Applicability per section 4. Everything defaults to MANDATORY; a row is conditional only where the
 * strategy states the obligation itself exists because a condition holds.
 */
$conditional = [
    'MD-S020-R0068' => 'condition_true: the eligible compatibility alias is still present on current schema and read surfaces, so the canonical/alias split it states is live',
    'MD-S020-R0071' => 'condition_true: retirement has not occurred, so the until-retirement propagation prohibition binds now; matches the basis recorded for MD-S020-R0173',
];

function loadMatrix($path)
{
    $fh = fopen($path, 'r');
    $head = fgetcsv($fh);
    $bom = false;
    if (isset($head[0]) && strpos($head[0], "\xEF\xBB\xBF") === 0) {
        $head[0] = substr($head[0], 3);
        $bom = true;
    }
    $rows = [];
    while (($r = fgetcsv($fh)) !== false) {
        if (count($r) < 3) {
            continue;
        }
        $rows[] = array_combine($head, array_pad(array_slice($r, 0, count($head)), count($head), ''));
    }
    fclose($fh);

    return [$head, $rows, $bom];
}

/** Structural class per section 2, or null when the row states a predicate. Mood is not consulted. */
function structuralClass($raw)
{
    $t = trim($raw);
    if ($t === '') {
        return 'EMPTY';
    }
    if ($t[0] === '#') {
        return 'HEADING';
    }
    if ($t[0] === '|') {
        return 'TABLE_ROW';
    }
    if (preg_match('/^>\s*\*\*Owner\b/i', $t)) {
        return 'OWNER_POINTER';
    }
    if (substr($t, -1) === ':') {
        return 'LIST_INTRODUCER';
    }
    if (preg_match('/^\*\*[^*]+\*\*[.:;]?$/', $t)) {
        return 'BARE_LABEL';
    }
    $body = preg_replace('/^([-*]|\d+\.)\s+/', '', $t);
    if (preg_match('/^`?[A-Za-z0-9_\/\.\- ]+\.(md|json|csv|sql|php)`?$/i', $body)) {
        return 'BARE_DOC_REF';
    }
    if (preg_match('/^`[^`]+`[.;]?$/', $body)) {
        return 'BARE_IDENTIFIER';
    }

    return null;
}

/** 'dash' | 'num' | null — the enumerated-list marker a row carries. */
function listMarker($raw)
{
    $t = ltrim($raw);
    if (preg_match('/^-\s+\S/', $t)) {
        return 'dash';
    }
    if (preg_match('/^\d+\.\s+\S/', $t)) {
        return 'num';
    }

    return null;
}

function stripMarker($raw)
{
    return trim(preg_replace('/^\s*([-*]|\d+\.)\s+/', '', trim($raw)));
}

/** Contiguous runs of same-marker list items inside one document/section. */
function detectRuns(array $rows)
{
    $runs = [];
    $cur = null;
    $flush = function () use (&$cur, &$runs) {
        if ($cur !== null) {
            $runs[] = $cur;
            $cur = null;
        }
    };
    foreach ($rows as $i => $r) {
        if (strtoupper(trim($r['active'])) !== 'YES') {
            $flush();
            continue;
        }
        $m = listMarker($r['rule_text']);
        if ($m === null) {
            $flush();
            continue;
        }
        $key = $r['strategy_owner'].'|'.$r['section'].'|'.$m;
        if ($cur === null || $cur['key'] !== $key || (int) $r['source_line'] > $cur['last_line'] + 3) {
            $flush();
            $cur = ['key' => $key, 'items' => [], 'last_line' => 0];
        }
        $cur['items'][] = $i;
        $cur['last_line'] = (int) $r['source_line'];
    }
    $flush();

    return $runs;
}

/** Nearest preceding active row in the same document/section that introduces the run. */
function governingParent(array $rows, $firstIndex)
{
    $doc = $rows[$firstIndex]['strategy_owner'];
    $section = $rows[$firstIndex]['section'];
    for ($k = $firstIndex - 1; $k >= 0 && $k > $firstIndex - 40; $k--) {
        $c = $rows[$k];
        if ($c['strategy_owner'] !== $doc) {
            break;
        }
        if (strtoupper(trim($c['active'])) !== 'YES') {
            continue;
        }
        if (listMarker($c['rule_text']) !== null) {
            continue;
        }
        if ($c['section'] !== $section) {
            break;
        }

        return $c;
    }

    return null;
}

[$head, $rows, $bom] = loadMatrix($matrixPath);
$runs = detectRuns($rows);

$promotions = [];
$skipped = [];

foreach ($runs as $run) {
    if (count($run['items']) < 2) {
        continue;
    }
    $classes = [];
    $stages = [];
    foreach ($run['items'] as $i) {
        $classes[$rows[$i]['coverage_requirement']] = true;
        $stages[$rows[$i]['primary_stage']] = true;
    }
    if (count($classes) < 2 || ! isset($stages[$stage])) {
        continue;
    }

    $parent = governingParent($rows, $run['items'][0]);
    foreach ($run['items'] as $i) {
        $r = $rows[$i];
        if ($r['coverage_requirement'] !== 'REFERENCE_ONLY' || $r['primary_stage'] !== $stage) {
            continue;
        }
        $sc = structuralClass($r['rule_text']);
        if ($sc !== null) {
            $skipped[] = [$r['rule_id'], $sc];
            continue;
        }

        $child = stripMarker($r['rule_text']);
        if ($parent !== null) {
            $context = $parent['rule_id'];
            $lead = rtrim(trim($parent['rule_text']), ':');
            $normalized = $lead.' '.lcfirst_safe($child);
        } else {
            $context = 'SELF';
            $normalized = $child;
        }

        $promotions[] = [
            'index' => $i,
            'rule_id' => $r['rule_id'],
            'context' => $context,
            'normalized' => $normalized,
        ];
    }
}

function lcfirst_safe($s)
{
    // Never lowercase an identifier, an acronym, or a backticked token.
    if ($s === '' || $s[0] === '`' || preg_match('/^[A-Z]{2,}/', $s) || preg_match('/^\*\*/', $s)) {
        return $s;
    }

    return lcfirst($s);
}

$applied = 0;
foreach ($promotions as $p) {
    $i = $p['index'];
    $ruleId = $p['rule_id'];
    $isConditional = isset($conditional[$ruleId]);
    $move = isset($ownershipMoves[$ruleId]) ? $ownershipMoves[$ruleId] : null;

    $note = $attempt.': reclassified REFERENCE_ONLY -> REQUIRED — the enumerated run this row belongs to '
        .'already carries REQUIRED siblings, so the list bears obligations and demoting this member is '
        .'inconsistent under traceability standard section 2, which permits reference classification only '
        .'for headings, introducers, labels, examples, and context-dependent bare fragments, never for '
        .'grammatical mood; predicate_context='.$p['context'].'; normalized_predicate='.$p['normalized'].'; '
        .'applicability_basis='.($isConditional ? $conditional[$ruleId] : 'always_applicable: the obligation exists now and does not depend on an external condition')
        .'; proof_owner_confirmed='.($move ? $move[0] : $rows[$i]['primary_stage']);
    if ($move) {
        $note .= ' (moved from '.$rows[$i]['primary_stage'].': '.$move[1].')';
    }

    $rows[$i]['coverage_requirement'] = 'REQUIRED';
    $rows[$i]['applicability'] = $isConditional ? 'CONDITIONAL_APPLICABLE' : 'MANDATORY';
    $rows[$i]['coverage_status'] = 'NOT_ASSESSED';
    $rows[$i]['current_evidence_ids'] = '';
    if ($move) {
        $supporting = array_filter(array_map('trim', explode(';', $rows[$i]['supporting_stages'])));
        if (! in_array($stage, $supporting, true)) {
            $supporting[] = $stage;
        }
        $rows[$i]['supporting_stages'] = implode(';', $supporting);
        $rows[$i]['primary_stage'] = $move[0];
    }
    $rows[$i]['notes'] = trim($rows[$i]['notes']) === '' ? $note : trim($rows[$i]['notes']).' | '.$note;
    $applied++;
}

$bound = 0;
if ($bindProof) {
    foreach ($rows as $i => $row) {
        $id = $row['rule_id'];
        if (! isset($proofBindings[$id])) {
            continue;
        }
        if ($row['coverage_requirement'] !== 'REQUIRED' || strtoupper(trim($row['active'])) !== 'YES') {
            failx($id.': cannot bind proof to a row that is not an active required rule.');
        }
        $spec = $proofBindings[$id];
        $marker = $attempt.': proof='.implode('&', $spec['proofs'])
            .'; implementation_surface='.implode(',', $spec['surfaces']);
        if (strpos($rows[$i]['notes'], $attempt.': proof=') === false) {
            $rows[$i]['notes'] = trim($rows[$i]['notes']).' | '.$marker;
        }
        $rows[$i]['coverage_status'] = 'SATISFIED';
        $rows[$i]['current_evidence_ids'] = $spec['evidence'];
        $bound++;
    }
    if ($bound !== count($proofBindings)) {
        failx('A014 proof binding must be atomic: '.$bound.' of '.count($proofBindings).' rows bound.');
    }
}

echo 'stage='.$stage.' promotions='.$applied.' proof_bound='.$bound.' skipped_structural='.count($skipped).PHP_EOL;
foreach ($skipped as $s) {
    echo '  skipped '.$s[0].' ('.$s[1].')'.PHP_EOL;
}
foreach ($promotions as $p) {
    echo '  + '.$p['rule_id'].' ctx='.$p['context'].PHP_EOL;
}

if (! $apply) {
    echo 'DRY RUN — pass --apply to write.'.PHP_EOL;
    exit(0);
}

$fh = fopen($matrixPath, 'w');
fputcsv($fh, $bom ? array_merge(["\xEF\xBB\xBF".$head[0]], array_slice($head, 1)) : $head);
foreach ($rows as $r) {
    $line = [];
    foreach ($head as $col) {
        $line[] = $r[$col];
    }
    fputcsv($fh, $line);
}
fclose($fh);
echo 'WROTE '.$matrixPath.PHP_EOL;
