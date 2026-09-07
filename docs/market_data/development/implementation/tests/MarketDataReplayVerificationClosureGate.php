<?php

require_once __DIR__.'/MarketDataReplayVerificationProofGate.php';

/**
 * `MD-B18` stage-closure condition gate.
 *
 * `STAGE_CLOSURE_MANIFEST_STANDARD.md` states six traceability conditions a terminal stage closure
 * must report and satisfy, and four circumstances in which closure must not claim execution
 * sufficiency for external raw proof. This evaluates each of them against the live matrix and the
 * live artifact store rather than against the shape of a previous stage's closure.
 *
 * It is a gate, not a report: any unmet condition exits non-zero. It is also proven able to fail on
 * each condition independently before it is relied on -- a closure gate that has only ever returned
 * PASS is a stamp, not a check.
 */
final class MarketDataReplayVerificationClosureGate
{
    public const EVIDENCE = 'E-MD-B18-A001-001';

    public const ARTIFACT_DIR = 'storage/app/market-data/evidence/MD-B18-A001';

    /** Every active row owned by this stage, whatever its applicability. */
    public static function stageRows(string $root): array
    {
        $path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new RuntimeException('TRACEABILITY_MATRIX_UNREADABLE');
        }
        $header = fgetcsv($handle);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($header)) {
                continue;
            }
            $row = array_combine($header, $values);
            if ((isset($row['active']) ? $row['active'] : '') !== 'YES'
                || (isset($row['primary_stage']) ? $row['primary_stage'] : '') !== 'MD-B18') {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /** @return array<string,mixed> */
    public static function validate(string $root): array
    {
        $spec = 'MarketDataReplayVerificationProofSpec';
        $stageRows = self::stageRows($root);
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
            'offenders' => array_slice($transitional, 0, 10),
        ];

        // ---- condition 2: nothing is still pending a decision.
        $pending = [];
        foreach ($stageRows as $row) {
            if (in_array($row['applicability'], ['CONDITIONAL_PENDING', 'APPLICABILITY_PENDING'], true)) {
                $pending[] = $row['rule_id'];
            }
        }
        $conditions['no_pending_applicability'] = [
            'required' => 'zero CONDITIONAL_PENDING or APPLICABILITY_PENDING rows',
            'observed' => count($pending),
            'met' => $pending === [],
            'offenders' => array_slice($pending, 0, 10),
        ];

        // ---- condition 3: the whole denominator is satisfied against this stage's evidence.
        $denominator = [];
        $unsatisfied = [];
        foreach ($stageRows as $row) {
            if ($row['coverage_requirement'] !== 'REQUIRED'
                || ! in_array($row['applicability'], ['MANDATORY', 'CONDITIONAL_APPLICABLE'], true)) {
                continue;
            }
            $denominator[] = $row['rule_id'];
            $evidence = trim((string) $row['current_evidence_ids']);
            if ($row['coverage_status'] !== 'SATISFIED'
                || preg_match(MarketDataReplayVerificationProofGate::EVIDENCE_PATTERN, $evidence) !== 1) {
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

        // ---- condition 4: an optional or reference row must not be silently carrying an obligation.
        $undecided = [];
        foreach ($stageRows as $row) {
            if ($row['coverage_requirement'] === 'REQUIRED') {
                continue;
            }
            /*
             * The decision must name the attempt that made it and state a basis. The marker
             * vocabulary is not uniform across stages -- MD-B18 normalization wrote "reviewed
             * reference decision" where earlier stages wrote "stage_entry_review" -- so the check
             * accepts any of the recorded forms rather than one stage's wording. What it does not
             * accept is a bare note, which is how a reference row keeps an obligation nobody looked at.
             */
            $note = strtolower((string) $row['notes']);
            $decided = false;
            foreach (['reviewed reference decision', 'stage_entry_review', 'applicability_basis',
                'reference_owner_basis', 'applicability_normalized'] as $marker) {
                if (strpos($note, $marker) !== false) {
                    $decided = true;
                    break;
                }
            }
            if (! $decided || strpos($note, strtolower($spec::ATTEMPT)) === false) {
                $undecided[] = $row['rule_id'];
            }
        }
        $conditions['non_required_rows_are_decided'] = [
            'required' => 'every optional or reference row carries a recorded stage-entry decision',
            'observed' => count($undecided),
            'met' => $undecided === [],
            'offenders' => array_slice($undecided, 0, 10),
        ];

        // ---- condition 5: every bound row names the guard that proved it, not only the evidence.
        $unattributed = [];
        foreach ($stageRows as $row) {
            if ($row['coverage_status'] !== 'SATISFIED') {
                continue;
            }
            $note = (string) $row['notes'];
            if (strpos($note, 'proof_family=') === false
                || strpos($note, 'positive=') === false
                || strpos($note, 'negative=') === false) {
                $unattributed[] = $row['rule_id'];
            }
        }
        $conditions['bound_rows_name_their_guard'] = [
            'required' => 'every SATISFIED row records its proof family and both guard methods, not just the evidence id',
            'observed' => count($unattributed),
            'met' => $unattributed === [],
            'offenders' => array_slice($unattributed, 0, 10),
        ];

        // ---- condition 6: no foreign row carries this stage's evidence.
        $foreign = [];
        $path = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $handle = fopen($path, 'rb');
        $header = fgetcsv($handle);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($header)) {
                continue;
            }
            $row = array_combine($header, $values);
            if ((isset($row['primary_stage']) ? $row['primary_stage'] : '') === 'MD-B18') {
                continue;
            }
            if (strpos((string) $row['current_evidence_ids'], 'E-MD-B18-') !== false) {
                $foreign[] = $row['rule_id'].' ('.$row['primary_stage'].')';
            }
        }
        fclose($handle);
        $conditions['no_foreign_row_carries_this_evidence'] = [
            'required' => 'no row owned by another stage carries MD-B18 evidence',
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
                $entryPath = $root.'/'.$entry['path'];
                if (! is_file($entryPath) || ! is_readable($entryPath)) {
                    $unreadable[] = $entry['path'];

                    continue;
                }
                if (strtolower(hash_file('sha256', $entryPath)) !== strtolower($entry['sha256'])) {
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
            'gate' => 'MarketDataReplayVerificationClosureGate',
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
    $result = MarketDataReplayVerificationClosureGate::validate(dirname(__DIR__, 5));
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
