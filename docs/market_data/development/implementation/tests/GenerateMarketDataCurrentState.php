<?php

/** PHP 7.3+; generate the canonical navigation summary from current registries. */
function csvRows($path)
{
    $out = [];
    $handle = fopen($path, 'r');
    $headers = fgetcsv($handle);
    if (isset($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    }
    while (($values = fgetcsv($handle)) !== false) {
        $row = [];
        foreach ($headers as $index => $key) {
            $row[$key] = isset($values[$index]) ? trim($values[$index]) : '';
        }
        $out[] = $row;
    }
    fclose($handle);

    return $out;
}

function traceabilityCounts(array $rows, $stage = null)
{
    $counts = [
        'required_rows' => 0, 'mandatory' => 0, 'conditional_applicable' => 0,
        'conditional_pending' => 0, 'conditional_not_applicable' => 0, 'transitional' => 0,
        'satisfied' => 0, 'not_assessed' => 0, 'not_applicable' => 0,
        'applicability_pending' => 0, 'optional' => 0,
    ];

    foreach ($rows as $row) {
        if ($row['active'] !== 'YES' || ($stage !== null && $row['primary_stage'] !== $stage)) {
            continue;
        }
        if ($row['applicability'] === 'OPTIONAL_CAPABILITY') {
            $counts['optional']++;
            continue;
        }
        if ($row['coverage_requirement'] !== 'REQUIRED') {
            continue;
        }
        $counts['required_rows']++;
        if ($row['applicability'] === 'MANDATORY') {
            $counts['mandatory']++;
        } elseif ($row['applicability'] === 'CONDITIONAL_APPLICABLE') {
            $counts['conditional_applicable']++;
        } elseif ($row['applicability'] === 'CONDITIONAL_PENDING') {
            $counts['conditional_pending']++;
        } elseif ($row['applicability'] === 'CONDITIONAL_NOT_APPLICABLE') {
            $counts['conditional_not_applicable']++;
        } else {
            $counts['transitional']++;
        }

        if ($row['coverage_status'] === 'SATISFIED') {
            $counts['satisfied']++;
        } elseif ($row['coverage_status'] === 'NOT_ASSESSED') {
            $counts['not_assessed']++;
        } elseif ($row['coverage_status'] === 'NOT_APPLICABLE') {
            $counts['not_applicable']++;
        } elseif ($row['coverage_status'] === 'APPLICABILITY_PENDING') {
            $counts['applicability_pending']++;
        }
    }

    $counts['denominator'] = $counts['mandatory'] + $counts['conditional_applicable'] + $counts['transitional'];
    $counts['coverage_percent'] = $counts['denominator'] > 0 ? round($counts['satisfied'] * 100 / $counts['denominator'], 2) : 100;
    $counts['coverage_state'] = $counts['transitional'] > 0 ? 'PROVISIONAL' : 'FINAL';

    return $counts;
}

/**
 * Reference-only rows still sitting in a mixed-classification enumerated run, per stage.
 *
 * `MD-B01-A014` found the stage register calling a denominator FINAL while 72 predicate rows were
 * excluded from it, so `FINAL` is qualified by what has actually been checked rather than printed
 * on the strength of applicability alone.
 *
 * @param  array<int,array<string,string>>  $rows
 * @return array<string,int>
 */
function classificationPending(array $rows): array
{
    $gate = dirname(__FILE__).'/MarketDataClassificationConsistencyGate.php';
    if (! is_file($gate)) {
        return [];
    }
    require_once $gate;

    return MarketDataClassificationConsistencyGate::validate($rows, [])['pending'];
}

/** @param array<string,int> $pending */
function denominatorQualifier(array $counts, string $stage, array $pending): string
{
    if ($counts['transitional'] > 0) {
        return 'PROVISIONAL — transitional applicability unresolved';
    }
    if (($pending[$stage] ?? 0) > 0) {
        return 'PROVISIONAL — '.$pending[$stage].' reference-only rows sit in mixed-classification runs';
    }

    return 'FINAL for every machine-checked criterion — no transitional applicability, no mixed-classification run';
}

$md = realpath(dirname(__DIR__, 3));
$epoch = json_decode(file_get_contents($md.'/authority/governance/CURRENT_VERIFICATION_EPOCH.json'), true);
$matrix = csvRows($md.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv');
$dependencies = csvRows($md.'/development/implementation/MD_DEPENDENCY_REGISTRY.csv');
$records = csvRows($md.'/records/WORK_RECORD_REGISTRY.csv');
$registerText = (string) file_get_contents($md.'/development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md');

$resume = 'UNRESOLVED — stage progression must stop';
if (preg_match('/\*\*Single exact next executable resume point:\*\*\s*([^\r\n]+)/', $registerText, $match)) {
    $resume = trim($match[1]);
}
$currentStage = 'UNRESOLVED';
if (preg_match('/open `?(MD-B\d{2})-A\d{3}`?/', $resume, $match)) {
    $currentStage = $match[1];
}

$global = traceabilityCounts($matrix);
$current = $currentStage === 'UNRESOLVED' ? null : traceabilityCounts($matrix, $currentStage);

$openDependencies = [];
foreach ($dependencies as $dependency) {
    if (strpos($dependency['status'], 'OPEN') === 0) {
        $openDependencies[] = '`'.$dependency['dependency_id'].'` — '.$dependency['status'].'; owner `'.$dependency['owner'].'`';
    }
}

$recordTypes = [];
foreach ($records as $record) {
    $type = $record['record_type'];
    $recordTypes[$type] = ($recordTypes[$type] ?? 0) + 1;
}
ksort($recordTypes);
$recordSummary = [];
foreach ($recordTypes as $type => $count) {
    $recordSummary[] = $type.'='.$count;
}

$stageRows = [];
$stageStates = [];
foreach (preg_split('/\R/', $registerText) as $line) {
    if (!preg_match('/^\| `(MD-B\d{2})` \|/', $line)) {
        continue;
    }
    $cells = array_map('trim', explode('|', trim($line, '|')));
    if (count($cells) >= 13) {
        $stageId = trim($cells[0], "` \t");
        $stageStates[$stageId] = [
            'lifecycle' => $cells[2], 'verdict' => $cells[3], 'attempt' => $cells[4],
            'baseline' => $cells[5], 'coverage' => $cells[6], 'residue' => $cells[7],
            'gate' => $cells[8], 'dependency' => $cells[9], 'finding' => $cells[10],
            'resume' => $cells[12],
        ];
        $stageRows[] = '| `'.$stageId.'` | '.$cells[2].' | '.$cells[3].' | '.$cells[4].' | '.$cells[5].' | '.$cells[8].' |';
    }
}

$currentStageState = $stageStates[$currentStage] ?? null;
$currentChangeImpacts = [];
if ($currentStageState !== null) {
    $attemptId = trim($currentStageState['attempt'], "` \t");
    foreach ($records as $record) {
        if ($record['attempt_id'] === $attemptId && $record['record_type'] === 'CHANGE_IMPACT_DECLARATION') {
            $currentChangeImpacts[] = '`'.$record['record_id'].'` — '.$record['status'];
        }
    }
}

$classificationPending = classificationPending($matrix);

$text = "# Market Data Current State\n\n> GENERATED — DO NOT EDIT MANUALLY\n\n";
$text .= "## Verification identity and coverage\n\n";
$text .= '- Verification epoch: `'.$epoch['verification_epoch']."`\n";
$text .= '- Required active traceability rows: **'.$global['required_rows']."**\n";
$text .= '- Coverage denominator: **'.$global['denominator'].'** ('.$global['coverage_state'].")\n";
$text .= '- SATISFIED: **'.$global['satisfied']."**\n";
$text .= '- NOT_ASSESSED inside denominator: **'.$global['not_assessed']."**\n";
$text .= '- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **'.$global['conditional_not_applicable'].' / '.$global['not_applicable']."**\n";
$text .= '- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **'.$global['conditional_pending'].' / '.$global['applicability_pending']."**\n";
$text .= '- Transitional MANDATORY_OR_CONDITIONAL: **'.$global['transitional']."**\n";
$text .= '- Verified coverage: **'.$global['coverage_percent'].'% '.$global['coverage_state']."**\n";
$text .= '- Optional capability rules: **'.$global['optional']."**\n";

if ($current !== null) {
    $text .= "\n## Current executable stage\n\n";
    $text .= '- Stage: `'.$currentStage."`\n";
    if ($currentStageState !== null) {
        $text .= '- Latest attempt / baseline: '.$currentStageState['attempt'].' / '.$currentStageState['baseline']."\n";
        $text .= '- State / verdict: '.$currentStageState['lifecycle'].' / '.$currentStageState['verdict']."\n";
        $text .= '- Residue/rework: '.$currentStageState['residue']."\n";
        $text .= '- Dependency: '.$currentStageState['dependency']."\n";
        $text .= '- Open finding: '.$currentStageState['finding']."\n";
        $text .= '- Change Impact Declaration: '.($currentChangeImpacts === [] ? '**missing**' : implode('; ', $currentChangeImpacts))."\n";
    }
    $text .= '- Denominator: **'.$current['denominator'].'** ('.denominatorQualifier($current, $currentStage, $classificationPending).")\n";
    $text .= '- SATISFIED / NOT_ASSESSED: **'.$current['satisfied'].' / '.$current['not_assessed']."**\n";
    $text .= '- Mandatory / conditional-applicable: **'.$current['mandatory'].' / '.$current['conditional_applicable']."**\n";
    $text .= '- Conditional-not-applicable / conditional-pending / transitional: **'.$current['conditional_not_applicable'].' / '.$current['conditional_pending'].' / '.$current['transitional']."**\n";
}

$text .= "\n## Stage state index\n\n";
$text .= "| Stage | Lifecycle | Verdict | Latest attempt | Baseline | Integrity gate |\n";
$text .= "|---|---|---|---|---|---|\n";
$text .= implode("\n", $stageRows)."\n";

$text .= "\n## Open dependencies and work records\n\n";
$text .= '- Open dependencies: '.($openDependencies === [] ? '**none**' : implode('; ', $openDependencies))."\n";
if ($classificationPending !== []) {
    $pendingParts = [];
    foreach ($classificationPending as $pendingStage => $pendingCount) {
        $pendingParts[] = '`'.$pendingStage.'` '.$pendingCount;
    }
    $text .= '- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: '
        .implode(', ', $pendingParts).' — total **'.array_sum($classificationPending)."**\n";
}
$text .= '- Registered current work records: **'.count($records).'** ('.implode(', ', $recordSummary).")\n";

$text .= "\n## Exact resume\n\n";
$text .= '- Single exact next executable resume point: '.$resume."\n";
$text .= '- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`' . "\n";
$text .= '- Pre-epoch W00..W22 verdicts: **historical-only**' . "\n";

file_put_contents($md.'/development/implementation/CURRENT_STATE.md', $text);
echo $md.'/development/implementation/CURRENT_STATE.md'.PHP_EOL;
