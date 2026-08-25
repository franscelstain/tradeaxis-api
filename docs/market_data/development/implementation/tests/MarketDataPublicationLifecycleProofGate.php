<?php
require_once __DIR__.'/MarketDataPublicationLifecycleProofSpec.php';
require_once __DIR__.'/MarketDataB10MigrationStaticGate.php';

final class MarketDataPublicationLifecycleProofGate
{
    public static function validate(string $root, bool $bound = false, array $overrides = []): array
    {
        $rows = isset($overrides['rows']) ? $overrides['rows'] : MarketDataPublicationLifecycleTraceabilitySpec::rows($root);
        $denominator = array_values(array_filter($rows, static function ($r) {
            return ($r['active'] ?? '') === 'YES'
                && ($r['primary_stage'] ?? '') === MarketDataPublicationLifecycleProofSpec::STAGE
                && ($r['coverage_requirement'] ?? '') === 'REQUIRED'
                && ($r['applicability'] ?? '') === 'MANDATORY';
        }));
        $entries = isset($overrides['entries']) ? $overrides['entries'] : MarketDataPublicationLifecycleProofSpec::entries($root);
        $families = isset($overrides['families']) ? $overrides['families'] : MarketDataPublicationLifecycleProofSpec::families();
        $errors = [];

        if (count($denominator) !== MarketDataPublicationLifecycleProofSpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'DENOMINATOR_MISMATCH: '.count($denominator);
        }
        if (count($entries) !== count($denominator)) {
            $errors[] = 'PROOF_MAP_COUNT_MISMATCH: '.count($entries).' vs '.count($denominator);
        }

        $denominatorByRule = [];
        foreach ($denominator as $row) {
            $rid = (string) ($row['rule_id'] ?? '');
            if ($rid === '' || isset($denominatorByRule[$rid])) {
                $errors[] = 'DUPLICATE_OR_EMPTY_DENOMINATOR_RULE: '.$rid;
                continue;
            }
            $denominatorByRule[$rid] = $row;

            $expectedStatus = $bound ? 'SATISFIED' : 'NOT_ASSESSED';
            if (($row['coverage_status'] ?? '') !== $expectedStatus) {
                $errors[] = 'COVERAGE_STATUS_INVALID: '.$rid.'='.$row['coverage_status'].' expected='.$expectedStatus;
            }
            $evidence = trim((string) ($row['current_evidence_ids'] ?? ''));
            if (! $bound && $evidence !== '') {
                $errors[] = 'PREMATURE_EVIDENCE_BINDING: '.$rid;
            }
            if ($bound) {
                if (! preg_match('/^E-MD-B10-A001-\d{3}$/', $evidence)) {
                    $errors[] = 'BOUND_EVIDENCE_ID_INVALID: '.$rid.'='.$evidence;
                }
            }
        }

        $seen = [];
        $usedFamilies = [];
        foreach ($entries as $entry) {
            $rid = (string) ($entry['rule_id'] ?? '');
            if ($rid === '' || isset($seen[$rid])) {
                $errors[] = 'DUPLICATE_PROOF_BINDING: '.$rid;
                continue;
            }
            $seen[$rid] = true;
            if (! isset($denominatorByRule[$rid])) {
                $errors[] = 'ORPHAN_PROOF_MAPPING: '.$rid;
                continue;
            }

            $documentId = (string) ($entry['strategy_document_id'] ?? '');
            if ($documentId !== (string) $denominatorByRule[$rid]['strategy_document_id']) {
                $errors[] = 'PROOF_DOCUMENT_MISMATCH: '.$rid;
            }
            $expectedFamily = MarketDataPublicationLifecycleProofSpec::expectedFamilyForDocument($documentId);
            $familyId = (string) ($entry['family'] ?? '');
            if ($expectedFamily === null || $familyId !== $expectedFamily) {
                $errors[] = 'WRONG_PROOF_FAMILY: '.$rid.'='.$familyId.' expected='.(string) $expectedFamily;
                continue;
            }
            if (! isset($families[$familyId])) {
                $errors[] = 'MISSING_PROOF_FAMILY: '.$rid.'='.$familyId;
                continue;
            }
            $family = $families[$familyId];
            $usedFamilies[$familyId] = true;

            if (strpos((string) ($family['owner'] ?? ''), 'MD-B10:') !== 0) {
                $errors[] = 'WRONG_PROOF_OWNER: '.$familyId;
            }
            if (empty($family['runtime_required'])) {
                $errors[] = 'RUNTIME_REQUIREMENT_MISSING: '.$familyId;
            }

            foreach ((array) ($family['implementation'] ?? []) as $implementationFile) {
                if (! is_file($root.'/'.$implementationFile)) {
                    $errors[] = 'MISSING_IMPLEMENTATION_REFERENCE: '.$familyId.'='.$implementationFile;
                }
            }
            foreach (['positive', 'negative'] as $kind) {
                $ref = $family[$kind] ?? null;
                if (! is_array($ref) || count($ref) !== 2) {
                    $errors[] = 'MALFORMED_'.$kind.'_PROOF_REFERENCE: '.$familyId;
                    continue;
                }
                [$testFile, $method] = $ref;
                $testPath = $root.'/'.$testFile;
                if (! is_file($testPath)) {
                    $errors[] = 'MISSING_TEST_REFERENCE: '.$familyId.'='.$testFile;
                    continue;
                }
                $src = file_get_contents($testPath);
                if (strpos($src, 'function '.$method.'(') === false) {
                    $errors[] = 'MISSING_TEST_METHOD: '.$familyId.'='.$method;
                }
            }
            foreach ((array) ($family['runtime_scripts'] ?? []) as $runtimeScript) {
                if (! is_file($root.'/'.$runtimeScript)) {
                    $errors[] = 'MISSING_RUNTIME_SCRIPT: '.$familyId.'='.$runtimeScript;
                }
            }
        }

        foreach ($denominatorByRule as $rid => $_row) {
            if (! isset($seen[$rid])) {
                $errors[] = 'MANDATORY_PREDICATE_UNBOUND_IN_PLAN: '.$rid;
            }
        }

        // Critical mapping invariants that cannot be weakened by remapping a contract to a generic family.
        $critical = [
            'MD-S007' => ['history_versioning', 'MarketDataB10DeployedSchemaProbe.php'],
            'MD-S018' => ['seal_freeze', null],
            'MD-S045' => ['manifest', null],
            'MD-S046' => ['traceability_reconciliation', 'MarketDataB10DeployedSchemaProbe.php'],
        ];
        foreach ($critical as $docId => $required) {
            $familyId = MarketDataPublicationLifecycleProofSpec::expectedFamilyForDocument($docId);
            if ($familyId !== $required[0] || ! isset($families[$familyId])) {
                $errors[] = 'CRITICAL_FAMILY_MAPPING_INVALID: '.$docId;
                continue;
            }
            if ($required[1] !== null) {
                $found = false;
                foreach ((array) ($families[$familyId]['runtime_scripts'] ?? []) as $script) {
                    if (basename($script) === $required[1]) {
                        $found = true;
                    }
                }
                if (! $found) {
                    $errors[] = 'CRITICAL_RUNTIME_PROOF_MISSING: '.$docId;
                }
            }
        }

        // Source-level proof invariants for the actual B10 remediation surface.
        self::checkImplementationInvariants($root, $errors);

        if ($bound) {
            $evidenceIds = array_values(array_unique(array_map(static function ($r) {
                return trim((string) ($r['current_evidence_ids'] ?? ''));
            }, $denominator)));
            if (count($evidenceIds) !== 1 || ! preg_match('/^E-MD-B10-A001-\d{3}$/', $evidenceIds[0] ?? '')) {
                $errors[] = 'BOUND_EVIDENCE_NOT_ATOMIC';
            } else {
                $payload = $overrides['evidence_payload'] ?? self::loadEvidence($root, $evidenceIds[0], $errors);
                if (is_array($payload)) {
                    self::validateEvidencePayload($payload, $evidenceIds[0], $errors);
                    $relationships = $overrides['relationships'] ?? self::loadRelationships($root);
                    self::validateBoundRelationships($relationships, $evidenceIds[0], $errors);
                }
            }
        }

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'denominator' => count($denominator),
            'proof_map_count' => count($entries),
            'proof_families_used' => count($usedFamilies),
            'bound' => $bound,
            'runtime_pending' => $bound ? 0 : count($denominator),
            'unbound_plan_rows' => count(array_diff_key($denominatorByRule, $seen)),
            'premature_satisfied' => $bound ? 0 : count(array_filter($denominator, static function ($r) {
                return ($r['coverage_status'] ?? '') === 'SATISFIED' || trim((string) ($r['current_evidence_ids'] ?? '')) !== '';
            })),
            'errors' => array_values(array_unique($errors)),
        ];
    }

    private static function checkImplementationInvariants(string $root, array &$errors): void
    {
        $pipeline = file_get_contents($root.'/app/Application/MarketData/Services/MarketDataPipelineService.php');
        $publication = file_get_contents($root.'/app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $artifact = file_get_contents($root.'/app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $reconciliation = file_get_contents($root.'/app/Application/MarketData/Services/PublicationProjectionReconciliationService.php');
        $repair = file_get_contents($root.'/app/Application/MarketData/Services/PublicationProjectionRepairService.php');

        $snapshotPos = strpos($pipeline, 'snapshotPublicationFromCurrentTables(');
        $completePos = strpos($pipeline, 'assertPublicationSnapshotComplete(');
        $manifestPos = strpos($pipeline, 'prepareCandidateManifestForSeal(');
        $sealPos = strpos($pipeline, 'sealCandidatePublication(');
        if ($snapshotPos === false || $completePos === false || $manifestPos === false || $sealPos === false
            || ! ($snapshotPos < $completePos && $completePos < $manifestPos && $manifestPos < $sealPos)) {
            $errors[] = 'SEAL_ORDER_INVARIANT_MISSING';
        }

        foreach (['SEALED_PUBLICATION_IMMUTABLE', 'prepareCandidateManifestForSeal', 'assertPublicationManifestHashValid', 'publication_manifest_hash'] as $needle) {
            if (strpos($publication.$artifact, $needle) === false) {
                $errors[] = 'PUBLICATION_IMMUTABILITY_OR_MANIFEST_INVARIANT_MISSING: '.$needle;
            }
        }

        // Reuse the dedicated B10 migration/ops/schema gate instead of duplicating an obsolete
        // source-shape assertion here. The migration legitimately evolved from helper-per-event
        // generation to an explicit trigger-definition map after the first MariaDB deployment
        // exposed non-transactional DDL/index-name behavior. Proof readiness must follow the
        // current executable contract, not a stale implementation shape.
        $migrationGate = MarketDataB10MigrationStaticGate::run($root);
        if (($migrationGate['status'] ?? 'FAIL') !== 'PASS') {
            $detail = implode(',', (array) ($migrationGate['errors'] ?? []));
            $errors[] = 'DATABASE_IMMUTABILITY_MIGRATION_INCOMPLETE'.($detail !== '' ? ': '.$detail : '');
        }

        foreach (['eod_current_publication_pointer', 'MISSING_HISTORY', 'MISSING_PROJECTION', 'VALUE_MISMATCH',
                  'orphan_projection_row_count', 'reconciliation_state', 'reconciliation_hash',
                  'md_publication_projection_reconciliations'] as $needle) {
            if (strpos($reconciliation, $needle) === false) {
                $errors[] = 'RECONCILIATION_INVARIANT_MISSING: '.$needle;
            }
        }
        if (preg_match('/DB::table\([^)]+\)->(?:update|delete)\s*\(/', $reconciliation)) {
            $errors[] = 'RECONCILIATION_SILENT_REPAIR_PATH_PRESENT';
        }


        foreach (['resolveCurrentReadablePublicationForTradeDate', 'assertPublicationSnapshotComplete',
                  'promotePublicationHistoryToCurrent', 'reconcileTradeDate',
                  'PROJECTION_REPAIR_CURRENT_PUBLICATION_UNRESOLVED', 'PROJECTION_REPAIR_HISTORY_IDENTITY_INVALID',
                  'PROJECTION_REPAIR_RECONCILIATION_FAILED'] as $needle) {
            if (strpos($repair, $needle) === false) {
                $errors[] = 'PROJECTION_REPAIR_INVARIANT_MISSING: '.$needle;
            }
        }
        if (preg_match('/DB::table\(["\']eod_(?:bars|indicators|eligibility)_history["\']\)->(?:update|delete)\s*\(/', $repair)) {
            $errors[] = 'PROJECTION_REPAIR_HISTORY_MUTATION_PATH_PRESENT';
        }
    }

    private static function loadEvidence(string $root, string $evidenceId, array &$errors)
    {
        $matches = glob($root.'/docs/market_data/records/evidence/'.$evidenceId.'_*');
        if (count($matches) !== 1) {
            $errors[] = 'GOVERNED_EVIDENCE_CARDINALITY_INVALID: '.$evidenceId;
            return null;
        }
        $payload = json_decode(file_get_contents($matches[0]), true);
        if (! is_array($payload)) {
            $errors[] = 'GOVERNED_EVIDENCE_JSON_INVALID: '.$evidenceId;
            return null;
        }
        return $payload;
    }

    private static function validateEvidencePayload(array $payload, string $evidenceId, array &$errors): void
    {
        $expected = [
            'evidence_id' => $evidenceId,
            'stage_id' => MarketDataPublicationLifecycleProofSpec::STAGE,
            'attempt_id' => MarketDataPublicationLifecycleProofSpec::ATTEMPT,
            'baseline_id' => MarketDataPublicationLifecycleProofSpec::BASELINE,
            'change_impact_declaration' => MarketDataPublicationLifecycleProofSpec::CI,
        ];
        foreach ($expected as $field => $value) {
            if (($payload[$field] ?? null) !== $value) {
                $errors[] = 'STALE_OR_WRONG_SCOPE_EVIDENCE: '.$field;
            }
        }
        if (($payload['mutability'] ?? '') !== 'IMMUTABLE_AFTER_ISSUE') {
            $errors[] = 'EVIDENCE_NOT_IMMUTABLE';
        }
        if (($payload['proof_admission']['status'] ?? '') !== 'PASS') {
            $errors[] = 'EVIDENCE_NOT_ADMITTED';
        }
        if (($payload['proof_admission']['mandatory_satisfied'] ?? null) !== MarketDataPublicationLifecycleProofSpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'EVIDENCE_DENOMINATOR_MISMATCH';
        }
    }

    private static function loadRelationships(string $root): array
    {
        $path = $root.'/docs/market_data/records/WORK_RELATIONSHIP_REGISTRY.csv';
        $h = fopen($path, 'r');
        if (! $h) {
            return [];
        }
        $headers = fgetcsv($h);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($row = fgetcsv($h)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($h);
        return $rows;
    }

    private static function validateBoundRelationships(array $relationships, string $evidenceId, array &$errors): void
    {
        $requiredTargets = [
            MarketDataPublicationLifecycleProofSpec::BASELINE => false,
            MarketDataPublicationLifecycleProofSpec::CI => false,
        ];
        foreach ($relationships as $r) {
            if (($r['source_record_id'] ?? '') !== $evidenceId) {
                continue;
            }
            $target = (string) ($r['target_record_id'] ?? '');
            if (array_key_exists($target, $requiredTargets) && in_array(($r['relationship_type'] ?? ''), ['DEPENDS_ON', 'CARRIED_FROM'], true)) {
                $requiredTargets[$target] = true;
            }
        }
        foreach ($requiredTargets as $target => $found) {
            if (! $found) {
                $errors[] = 'BOUND_EVIDENCE_RELATIONSHIP_MISSING: '.$target;
            }
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $rows = MarketDataPublicationLifecycleTraceabilitySpec::mandatory($root);
    $allBound = $rows !== [];
    foreach ($rows as $row) {
        if (($row['coverage_status'] ?? '') !== 'SATISFIED') {
            $allBound = false;
            break;
        }
    }
    $bound = in_array('--bound', $argv, true) || $allBound;
    $result = MarketDataPublicationLifecycleProofGate::validate($root, $bound);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
