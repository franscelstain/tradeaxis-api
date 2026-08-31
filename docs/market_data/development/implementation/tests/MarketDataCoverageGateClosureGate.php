<?php

require_once __DIR__.'/MarketDataCoverageGateProofGate.php';

/**
 * `MD-B15` stage-closure condition gate.
 *
 * `STAGE_CLOSURE_MANIFEST_STANDARD.md` states six traceability conditions a terminal stage closure
 * must report and satisfy, and four circumstances in which closure must not claim execution
 * sufficiency for external raw proof. This evaluates each of them against the live matrix and the
 * live artifact store rather than against the shape of a previous stage's closure.
 *
 * It is a gate, not a report: any unmet condition exits non-zero. It is also proven able to fail on
 * each condition independently before it is relied on — a closure gate that has only ever returned
 * PASS is a stamp, not a check.
 */
final class MarketDataCoverageGateClosureGate
{
    public const EVIDENCE = 'E-MD-B15-A001-001';

    public const ARTIFACT_DIR = 'storage/app/market-data/evidence/MD-B15-A001';

    /** @return array<string,mixed> */
    public static function validate(string $root): array
    {
        $spec = 'MarketDataCoverageGateProofSpec';
        $traceability = 'MarketDataCoverageGateTraceabilitySpec';

        $stageRows = $traceability::stageRows($root);
        $conditions = [];
        $errors = [];

        // ---- condition 1: no transitional applicability survives.
        $transitional = [];
        foreach ($stageRows as $row) {
            if ($row['applicability'] === 'MANDATORY_OR_CONDITIONAL') {
                $transitional[] = $row['rule_id'];
            }
        }
        $conditions['no_transitional_applicability'] = [
            'required' => 'zero required rows with transitional MANDATORY_OR_CONDITIONAL',
            'observed' => count($transitional),
            'met' => $transitional === [],
            'offenders' => $transitional,
        ];

        // ---- condition 2: nothing left pending.
        $pending = [];
        foreach ($stageRows as $row) {
            if ($row['applicability'] === 'CONDITIONAL_PENDING' || $row['coverage_status'] === 'APPLICABILITY_PENDING') {
                $pending[] = $row['rule_id'];
            }
        }
        $conditions['no_pending_applicability'] = [
            'required' => 'zero CONDITIONAL_PENDING or APPLICABILITY_PENDING rows',
            'observed' => count($pending),
            'met' => $pending === [],
            'offenders' => $pending,
        ];

        // ---- condition 3: the whole denominator is satisfied against current evidence.
        $denominator = [];
        $unsatisfied = [];
        foreach ($stageRows as $row) {
            if ($row['coverage_requirement'] !== 'REQUIRED'
                || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)) {
                continue;
            }
            $denominator[] = $row['rule_id'];
            if ($row['coverage_status'] !== 'SATISFIED'
                || preg_match(MarketDataCoverageGateProofGate::EVIDENCE_PATTERN,
                    trim((string) $row['current_evidence_ids'])) !== 1) {
                $unsatisfied[] = $row['rule_id'];
            }
        }
        $conditions['denominator_fully_satisfied'] = [
            'required' => 'all MANDATORY and CONDITIONAL_APPLICABLE denominator rows SATISFIED against current stage evidence',
            'denominator' => count($denominator),
            'satisfied' => count($denominator) - count($unsatisfied),
            'met' => $unsatisfied === [] && count($denominator) === $spec::EXPECTED_DENOMINATOR,
            'offenders' => array_slice($unsatisfied, 0, 10),
        ];

        // ---- condition 4: every not-applicable row proves its condition false.
        $notApplicable = [];
        $unevidenced = [];
        foreach ($stageRows as $row) {
            if ($row['applicability'] !== 'CONDITIONAL_NOT_APPLICABLE') {
                continue;
            }
            $notApplicable[] = $row['rule_id'];
            $notes = (string) $row['notes'];
            if (strpos($notes, 'applicability_basis=condition_absent') === false
                || strpos($notes, 'condition_guard=') === false) {
                $unevidenced[] = $row['rule_id'];
            }
        }
        $conditions['not_applicable_rows_are_evidenced'] = [
            'required' => 'every CONDITIONAL_NOT_APPLICABLE row carries a false-condition basis and a standing guard on that condition',
            'observed' => count($notApplicable),
            'met' => $unevidenced === [],
            'offenders' => $unevidenced,
        ];

        // ---- condition 5: deterministic parent/context binding and a normalized predicate.
        $unbound = [];
        foreach ($stageRows as $row) {
            if ($row['coverage_requirement'] !== 'REQUIRED'
                || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)) {
                continue;
            }
            $notes = (string) $row['notes'];
            if (strpos($notes, 'predicate_context=') === false
                || strpos($notes, 'normalized_predicate=') === false
                || trim((string) $row['section']) === '') {
                $unbound[] = $row['rule_id'];
            }
        }
        $conditions['context_binding_and_normalized_predicate'] = [
            'required' => 'every context-dependent required fragment carries deterministic parent/context binding and a normalized predicate',
            'observed' => count($denominator),
            'met' => $unbound === [],
            'offenders' => array_slice($unbound, 0, 10),
        ];

        // ---- condition 6: no proof invalidated by this stage's corrections is still counted.
        $foreign = [];
        foreach ($traceability::rows($root) as $row) {
            if ($row['primary_stage'] === $spec::STAGE) {
                continue;
            }
            if (strpos((string) $row['notes'], $spec::ATTEMPT.':') !== false
                || trim((string) $row['current_evidence_ids']) === self::EVIDENCE) {
                $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
            }
        }
        $conditions['no_invalidated_proof_still_counted'] = [
            'required' => 'no proof invalidated by semantic-context or applicability correction remains counted, and no foreign row carries this stage evidence',
            'observed' => count($foreign),
            'met' => $foreign === [],
            'offenders' => array_slice($foreign, 0, 10),
        ];

        // ---- raw-artifact integrity: the four circumstances closure must not claim through.
        $manifestPath = $root.'/'.self::ARTIFACT_DIR.'/MANIFEST.json';
        $artifact = ['manifest_present' => is_file($manifestPath)];

        if (! $artifact['manifest_present']) {
            $errors[] = 'RAW_ARTIFACT_MANIFEST_MISSING';
            $artifact['met'] = false;
        } else {
            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            $mismatched = [];
            $unreadable = [];
            foreach (isset($manifest['artifacts']) ? $manifest['artifacts'] : [] as $entry) {
                $path = $root.'/'.$entry['path'];
                if (! is_file($path) || ! is_readable($path)) {
                    $unreadable[] = $entry['path'];

                    continue;
                }
                if (strtolower(hash_file('sha256', $path)) !== strtolower($entry['sha256'])) {
                    $mismatched[] = $entry['path'];
                }
            }
            $artifact['artifact_count'] = isset($manifest['artifact_count']) ? $manifest['artifact_count'] : 0;
            $artifact['unreadable'] = $unreadable;
            $artifact['hash_mismatched'] = $mismatched;
            $artifact['met'] = $unreadable === [] && $mismatched === []
                && $artifact['artifact_count'] > 0
                && count($manifest['artifacts']) === $artifact['artifact_count'];
        }
        $conditions['raw_artifact_integrity'] = $artifact + [
            'required' => 'every governed raw artifact is present, readable, and hashes to the value the manifest records',
        ];

        // ---- the governed evidence the closure points at must exist and be reachable.
        $evidenceMatches = glob($root.'/docs/market_data/records/evidence/'.self::EVIDENCE.'_*');
        $conditions['governed_evidence_reachable'] = [
            'required' => 'the stage evidence exists as exactly one governed record and links the raw artifact manifest',
            'met' => count($evidenceMatches) === 1,
            'observed' => count($evidenceMatches),
        ];
        if (count($evidenceMatches) === 1) {
            $record = json_decode((string) file_get_contents($evidenceMatches[0]), true);
            $linked = isset($record['raw_artifact_manifest']['manifest_path'])
                ? $record['raw_artifact_manifest']['manifest_path'] : '';
            $conditions['governed_evidence_reachable']['manifest_linked'] = $linked;
            $conditions['governed_evidence_reachable']['met'] =
                $conditions['governed_evidence_reachable']['met']
                && $linked !== ''
                && is_file($root.'/'.$linked);
        }

        foreach ($conditions as $name => $condition) {
            if (empty($condition['met'])) {
                $errors[] = 'CLOSURE_CONDITION_UNMET:'.$name;
            }
        }

        return [
            'gate' => 'MarketDataCoverageGateClosureGate',
            'stage_id' => $spec::STAGE,
            'attempt_id' => $spec::ATTEMPT,
            'evidence_id' => self::EVIDENCE,
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'conditions' => $conditions,
            'errors' => $errors,
        ];
    }
}

if (realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '') === __FILE__) {
    $result = MarketDataCoverageGateClosureGate::validate(dirname(__DIR__, 5));
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
