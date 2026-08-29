<?php

require_once __DIR__.'/MarketDataSourceResilienceProofGate.php';

/**
 * Standalone mutation self-test for MD-B08 traceability and proof fail-closed behaviour.
 *
 * Two defects were repaired here alongside the gate itself.
 *
 * The gate entrypoint never read `$argv`, so `--bound` was accepted and ignored and readiness
 * semantics were applied to a stage closed weeks earlier, giving `premature_satisfied: 139` and a
 * verdict that could never be anything but FAIL.
 *
 * This self-test had the matching defect: `baseline_proof_readiness` ran readiness against the
 * current closed rows, so the control was already red, and the two readiness mutations below it were
 * passing vacuously — the gate was FAIL before the mutation and FAIL after it, which demonstrates
 * nothing. Readiness is now exercised against a reconstruction of the pre-proof state, so its
 * control is green and its mutations mean what they claim.
 */
final class MarketDataSourceResilienceProofSelfTest
{
    private static function mutate(array $rows, string $rule, callable $mutation): array
    {
        foreach ($rows as $index => $row) {
            if ($row['rule_id'] === $rule) {
                $rows[$index] = $mutation($row);
                return $rows;
            }
        }
        throw new RuntimeException('Mutation target not found: '.$rule);
    }

    public static function run(string $root): array
    {
        $matrix = MarketDataClassificationConsistencyGate::readMatrix(
            $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'
        );
        $rows = $matrix['rows'];
        $checks = [];

        // Readiness describes the stage before it returned local runtime proof. MD-B08 is closed,
        // so that state has to be reconstructed rather than read: judging a closed stage by
        // pre-proof rules is the defect this file exists to stop repeating.
        $proofMap = MarketDataSourceResilienceProofSpec::proofMap();
        $preProof = [];
        foreach ($rows as $row) {
            if (isset($proofMap[$row['rule_id']])) {
                $row['coverage_status'] = 'NOT_ASSESSED';
                $row['current_evidence_ids'] = '';
            }
            $preProof[] = $row;
        }

        $baselineTrace = MarketDataSourceResilienceTraceabilityGate::validate($rows);
        $checks['baseline_traceability'] = $baselineTrace['status'] === 'PASS' ? 'CONTROL_OK' : 'CONTROL_FAILED';

        $baselineProof = MarketDataSourceResilienceProofGate::validateReadiness($preProof, $root);
        $checks['baseline_proof_readiness'] = $baselineProof['status'] === 'PASS' ? 'CONTROL_OK' : 'CONTROL_FAILED';

        $baselineBound = MarketDataSourceResilienceProofGate::validateBound($rows, $root);
        $checks['baseline_proof_bound'] = $baselineBound['status'] === 'PASS' ? 'CONTROL_OK' : 'CONTROL_FAILED';

        $baselineImplementation = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root);
        $checks['baseline_implementation_invariants'] = $baselineImplementation['status'] === 'PASS' ? 'CONTROL_OK' : 'CONTROL_FAILED';

        $mutated = self::mutate($rows, 'MD-S029-R0067', static function ($row) {
            $row['primary_stage'] = 'MD-B09';
            return $row;
        });
        $checks['wrong_b08_proof_owner'] = MarketDataSourceResilienceTraceabilityGate::validate($mutated)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $mutated = self::mutate($rows, 'MD-S029-R0071', static function ($row) {
            $row['coverage_requirement'] = 'REFERENCE_ONLY';
            $row['applicability'] = 'REFERENCE_ONLY';
            $row['coverage_status'] = 'REFERENCE_ONLY';
            return $row;
        });
        $checks['breaker_predicate_denominator_reduction'] = MarketDataSourceResilienceTraceabilityGate::validate($mutated)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $mutated = self::mutate($rows, 'MD-S029-R0158', static function ($row) {
            $row['notes'] = str_replace('predicate_context=SELF_CONTAINED', 'predicate_context=MD-S029-R0150', $row['notes']);
            return $row;
        });
        $checks['traceability_context_drift'] = MarketDataSourceResilienceTraceabilityGate::validate($mutated)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $mutated = self::mutate($preProof, 'MD-S029-R0197', static function ($row) {
            $row['coverage_status'] = 'SATISFIED';
            $row['current_evidence_ids'] = 'E-MD-B08-A001-999';
            return $row;
        });
        $checks['premature_runtime_satisfaction'] = MarketDataSourceResilienceProofGate::validateReadiness($mutated, $root)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $mutated = self::mutate($preProof, 'MD-S029-R0197', static function ($row) {
            $row['current_evidence_ids'] = MarketDataSourceResilienceProofGate::PRIMARY_EVIDENCE;
            return $row;
        });
        $checks['premature_evidence_without_satisfied_status'] = MarketDataSourceResilienceProofGate::validateReadiness($mutated, $root)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $adapterPath = $root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php';
        $adapterSource = (string) file_get_contents($adapterPath);
        $mutatedAdapter = preg_replace(
            "/return 'RUN_SOURCE_TIMEOUT'/",
            "return 'RUN_SOURCE_CIRCUIT_BREAKER_OPEN'",
            $adapterSource,
            1
        );
        if ($mutatedAdapter === null || $mutatedAdapter === $adapterSource) {
            throw new RuntimeException('Unable to construct B08 reason-code mutation.');
        }
        $checks['unregistered_breaker_reason_code'] = MarketDataSourceResilienceProofGate::validateB08ReasonCodeScope($root, $mutatedAdapter)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $adapterRelative = 'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php';
        $pipelineRelative = 'app/Application/MarketData/Services/MarketDataPipelineService.php';
        $backfillRelative = 'app/Application/MarketData/Services/MarketDataBackfillService.php';

        $retryClamp = str_replace(
            "return max(0, (int) config('market_data.provider.api_retry_max'));",
            "return min(3, max(0, (int) config('market_data.provider.api_retry_max')));",
            $adapterSource
        );
        $checks['hidden_retry_budget_clamp'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $adapterRelative => $retryClamp,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $sampleFloor = str_replace('$universeCount <= 0', '$universeCount < 5', $adapterSource);
        $checks['hidden_breaker_sample_floor'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $adapterRelative => $sampleFloor,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $attemptRatio = str_replace(
            '($failureCount / $universeCount) > $threshold',
            '($failureCount / max(1, ($failureCount + $successCount))) > $threshold',
            $adapterSource
        );
        $checks['wrong_breaker_attempts_denominator'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $adapterRelative => $attemptRatio,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $fanoutBypass = str_replace('circuitBreakerTelemetry', 'circuitBreakerTelemetry_DISABLED', $adapterSource);
        $checks['missing_fanout_source_protection'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $adapterRelative => $fanoutBypass,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $apiFreeMisclassified = str_replace("['api', 'api_free']", "['api']", $adapterSource);
        $checks['api_free_misclassified_as_secondary'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $adapterRelative => $apiFreeMisclassified,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $evidenceRelative = 'app/Application/MarketData/Services/MarketDataEvidenceExportService.php';
        $evidenceSource = (string) file_get_contents($root.'/'.$evidenceRelative);
        $summaryTelemetryBypass = str_replace("'source_priority' => 'source_priority',", "'source_priority_REMOVED' => 'source_priority_REMOVED',", $evidenceSource);
        $checks['missing_evidence_summary_audit_field'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $evidenceRelative => $summaryTelemetryBypass,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $backfillSource = (string) file_get_contents($root.'/'.$backfillRelative);
        $telemetryBypass = str_replace('source_priority', 'source_priority_REMOVED', $backfillSource);
        $checks['missing_required_audit_telemetry'] = MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
            $backfillRelative => $telemetryBypass,
        ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $map = $proofMap;
        unset($map['MD-S029-R0197']);
        $checks['missing_proof_map_rule'] = MarketDataSourceResilienceProofGate::validateReadiness($preProof, $root, $map)['status'] === 'FAIL'
            ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        // ---- bound mode. The two modes must disagree about the same state, or the flag is decoration.
        $bound = static function (array $mutatedRows) use ($root) {
            return MarketDataSourceResilienceProofGate::validateBound($mutatedRows, $root)['status'] === 'FAIL'
                ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';
        };

        $checks['modes_disagree_about_the_closed_state'] =
            (MarketDataSourceResilienceProofGate::validateReadiness($rows, $root)['status'] === 'FAIL'
                && MarketDataSourceResilienceProofGate::validateBound($rows, $root)['status'] === 'PASS')
                ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $checks['modes_disagree_about_the_pre_proof_state'] =
            (MarketDataSourceResilienceProofGate::validateReadiness($preProof, $root)['status'] === 'PASS'
                && MarketDataSourceResilienceProofGate::validateBound($preProof, $root)['status'] === 'FAIL')
                ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $primaryRule = 'MD-S029-R0197';
        $remediatedRule = array_keys(MarketDataSourceResilienceTraceabilitySpec::REMEDIATED_RULES)[0];

        $checks['bound_rejects_a_non_satisfied_predicate'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['coverage_status'] = 'NOT_ASSESSED';
                return $row;
            }));

        $checks['bound_rejects_missing_evidence'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['current_evidence_ids'] = '';
                return $row;
            }));

        $checks['bound_rejects_another_stage_evidence'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['current_evidence_ids'] = 'E-MD-B09-A001-001';
                return $row;
            }));

        $checks['bound_rejects_an_evidence_id_with_no_governed_record'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['current_evidence_ids'] = 'E-MD-B08-A009-999';
                return $row;
            }));

        // MD-B08-A002 re-proved one predicate. Accepting the primary evidence on that row would be
        // the gate agreeing the remediation attempt never happened.
        $checks['bound_rejects_the_remediated_rule_bound_to_primary_evidence'] = $bound(self::mutate($rows, $remediatedRule,
            static function ($row) {
                $row['current_evidence_ids'] = MarketDataSourceResilienceProofGate::PRIMARY_EVIDENCE;
                return $row;
            }));

        $checks['bound_rejects_a_primary_rule_bound_to_remediation_evidence'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['current_evidence_ids'] = MarketDataSourceResilienceProofGate::REMEDIATION_EVIDENCE;
                return $row;
            }));

        $checks['bound_rejects_a_predicate_reassigned_to_another_stage'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['primary_stage'] = 'MD-B09';
                return $row;
            }));

        $checks['bound_rejects_a_predicate_downgraded_from_required'] = $bound(self::mutate($rows, $primaryRule,
            static function ($row) {
                $row['coverage_requirement'] = 'REFERENCE_ONLY';
                return $row;
            }));

        $shortDenominator = [];
        foreach ($rows as $row) {
            if ($row['rule_id'] !== $primaryRule) {
                $shortDenominator[] = $row;
            }
        }
        $checks['bound_rejects_a_shrunken_denominator'] = $bound($shortDenominator);

        $checks['bound_rejects_a_shrunken_proof_map'] =
            MarketDataSourceResilienceProofGate::validateBound($rows, $root, null, $map)['status'] === 'FAIL'
                ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        // A bound predicate whose named guard no longer exists is a false claim, not a historical one.
        $brokenMap = $proofMap;
        $brokenMap[$primaryRule]['methods'][] = [
            $proofMap[$primaryRule]['methods'][0][0], 'test_a_method_nobody_ever_wrote',
        ];
        $checks['bound_rejects_a_named_guard_that_does_not_exist'] =
            MarketDataSourceResilienceProofGate::validateBound($rows, $root, null, $brokenMap)['status'] === 'FAIL'
                ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $checks['bound_rejects_a_corrupted_implementation_surface'] =
            MarketDataSourceResilienceProofGate::validateImplementationInvariants($root, [
                'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php' => '<?php // emptied by the self-test',
            ])['status'] === 'FAIL' ? 'FAILS_CLOSED' : 'MUTATION_ESCAPED';

        $failed = array_filter($checks, static function ($verdict) {
            return ! in_array($verdict, ['CONTROL_OK', 'FAILS_CLOSED'], true);
        });

        return [
            'status' => $failed === [] ? 'PASS' : 'FAIL',
            'checks' => $checks,
            'counts' => [
                'total' => count($checks),
                'controls' => count(array_filter($checks, static fn($v) => $v === 'CONTROL_OK')),
                'fails_closed' => count(array_filter($checks, static fn($v) => $v === 'FAILS_CLOSED')),
            ],
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $result = MarketDataSourceResilienceProofSelfTest::run($root);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
