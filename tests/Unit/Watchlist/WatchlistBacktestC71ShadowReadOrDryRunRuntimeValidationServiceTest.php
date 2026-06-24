<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationServiceTest extends TestCase
{
    private string $output = 'storage/app/watchlist/backtest/.tmp-c71-test-output.json';
    private array $tmpFiles = [];

    protected function tearDown(): void
    {
        foreach (array_merge([$this->output], $this->tmpFiles) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_c71_runtime_passes_primary_and_backup_when_locked_inputs_match(): void
    {
        $result = $this->runService();

        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['reason_code']);
        $this->assertTrue($result['shadow_read_or_dry_run_runtime_validation_executed']);
        $this->assertTrue($result['shadow_read_or_dry_run_runtime_validation_allowed']);
        $this->assertTrue($result['shadow_read_or_dry_run_runtime_validation_pass']);
        $this->assertFalse($result['production_ready']);
        $this->assertFalse($result['production_catalog_runtime_wired']);
        $this->assertFalse($result['shadow_read_runtime_active']);
        $this->assertFalse($result['dry_run_runtime_active']);
        $this->assertFalse($result['production_deployment_allowed']);
        $this->assertFalse($result['production_deployment_executed']);
        $this->assertFalse($result['plan_confirm_mutation_allowed']);
        $this->assertFalse($result['plan_confirm_mutated']);
        $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog']);
        $this->assertFalse($result['live_plan_confirm_rollout_allowed']);
        $this->assertFalse($result['live_plan_confirm_rollout_executed']);
        $this->assertSame('C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION', $result['next_step_recommendation']);
        $this->assertFileExists($this->output);
    }

    public function test_c71_records_required_sections(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'source_artifact_locks',
            'database_dictionary_read_summary',
            'c70_lock_validation_summary',
            'c69_lineage_validation_summary',
            'c68_lineage_validation_summary',
            'c67_lineage_validation_summary',
            'c66_lineage_validation_summary',
            'c65_lineage_validation_summary',
            'c64_lineage_validation_summary',
            'c63_lineage_validation_summary',
            'c62_lineage_validation_summary',
            'c61_lineage_validation_summary',
            'c60_lineage_validation_summary',
            'candidate_scope_freeze_summary',
            'shadow_read_or_dry_run_runtime_validation_candidate_scorecard',
            'shadow_read_or_dry_run_runtime_validation_decision',
            'runtime_path_inspection_summary',
            'feature_flag_kill_switch_runtime_validation_summary',
            'shadow_read_execution_summary',
            'dry_run_execution_summary',
            'plan_confirm_baseline_non_mutation_summary',
            'fallback_behavior_runtime_validation_summary',
            'bad_month_runtime_validation_review_results',
            'weak_regime_runtime_validation_review_results',
            'source_bias_shared_core_runtime_validation_summary',
            'production_mutation_safety_summary',
            'documentation_governance_summary',
            'c65_cleanup_note_summary',
            'c72_readiness_decision',
            'failure_attribution_summary',
            'diagnostics',
        ] as $section) {
            $this->assertArrayHasKey($section, $run, $section);
        }
    }

    public function test_c71_validates_c70_artifact_hash_and_file_sha1(): void
    {
        $hashResult = $this->execute(['expectedC70Hash' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C71_BLOCKED_C70_ARTIFACT_LOCK_MISMATCH', $hashResult['status']);
        $this->assertFalse($hashResult['c70_hash_match']);

        $shaResult = $this->execute(['expectedC70FileSha1' => '0000000000000000000000000000000000000000']);
        $this->assertSame('C71_BLOCKED_C70_FILE_SHA1_LOCK_MISMATCH', $shaResult['status']);
        $this->assertFalse($shaResult['c70_file_sha1_match']);
    }

    public function test_c71_rejects_missing_c70_artifact(): void
    {
        $result = $this->execute(['c70Artifact' => 'storage/app/watchlist/backtest/missing-c70.json']);

        $this->assertSame('C71_BLOCKED_C70_ARTIFACT_LOCK_MISMATCH', $result['status']);
        $this->assertFalse($result['shadow_read_or_dry_run_runtime_validation_executed']);
    }

    public function test_c71_rejects_c70_status_and_reason_mismatches(): void
    {
        $status = $this->mutateC70AndExecute(function (array $c70): array {
            $c70['status'] = 'BROKEN_STATUS';
            return $c70;
        }, 'c70-status-mismatch');
        $this->assertSame('C71_BLOCKED_C70_STATUS_OR_REASON_MISMATCH', $status['status']);

        $reason = $this->mutateC70AndExecute(function (array $c70): array {
            $c70['reason_code'] = 'BROKEN_REASON';
            return $c70;
        }, 'c70-reason-mismatch');
        $this->assertSame('C71_BLOCKED_C70_STATUS_OR_REASON_MISMATCH', $reason['status']);
    }

    public function test_c71_rejects_c70_required_gate_mismatches(): void
    {
        $cases = [
            ['production_deployment_execution_review_pass', false, 'C71_BLOCKED_C70_DEPLOYMENT_EXECUTION_REVIEW_NOT_PASSED'],
            ['controlled_production_deployment_execution_review_pass', false, 'C71_BLOCKED_C70_CONTROLLED_EXECUTION_REVIEW_NOT_PASSED'],
            ['production_catalog_runtime_wired', true, 'C71_BLOCKED_C70_RUNTIME_ALREADY_WIRED'],
            ['production_deployment_allowed', true, 'C71_BLOCKED_C70_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            ['production_deployment_executed', true, 'C71_BLOCKED_C70_DEPLOYMENT_ALREADY_EXECUTED'],
            ['plan_confirm_mutation_allowed', true, 'C71_BLOCKED_C70_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            ['plan_confirm_mutated', true, 'C71_BLOCKED_C70_PLAN_CONFIRM_ALREADY_MUTATED'],
            ['plan_confirm_runtime_reads_activated_catalog', true, 'C71_BLOCKED_C70_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
            ['live_plan_confirm_rollout_allowed', true, 'C71_BLOCKED_C70_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED'],
            ['live_plan_confirm_rollout_executed', true, 'C71_BLOCKED_C70_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED'],
        ];

        foreach ($cases as $case) {
            $result = $this->mutateC70AndExecute(function (array $c70) use ($case): array {
                $c70[$case[0]] = $case[1];
                return $c70;
            }, 'c70-'.$case[0]);
            $this->assertSame($case[2], $result['status'], $case[0]);
        }
    }

    public function test_c71_validates_nested_c71_readiness_path_not_top_level_aliases(): void
    {
        $result = $this->mutateC70AndExecute(function (array $c70): array {
            $c70['candidate_ready_for_c71_count'] = 0;
            $c70['c71_recommendation'] = 'BROKEN_TOP_LEVEL_ALIAS';
            return $c70;
        }, 'c70-top-level-alias');

        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP', $result['status']);
        $this->assertTrue($result['c70_lock_validation_summary']['c71_readiness_nested_path_validated']);
        $this->assertFalse($result['c70_lock_validation_summary']['top_level_alias_used_for_c70_source_validation']);
    }

    public function test_c71_rejects_nested_c71_readiness_mismatches(): void
    {
        $count = $this->mutateC70AndExecute(function (array $c70): array {
            $c70['c71_readiness_decision']['candidate_ready_for_c71_count'] = 1;
            return $c70;
        }, 'c70-nested-count');
        $this->assertSame('C71_BLOCKED_C70_C71_READINESS_COUNT_MISMATCH', $count['status']);

        $recommendation = $this->mutateC70AndExecute(function (array $c70): array {
            $c70['c71_readiness_decision']['c71_recommendation'] = 'BROKEN_C71';
            return $c70;
        }, 'c70-nested-recommendation');
        $this->assertSame('C71_BLOCKED_C70_RECOMMENDATION_MISMATCH', $recommendation['status']);
    }

    public function test_c71_validates_all_lineage_artifacts(): void
    {
        foreach ([
            'expectedC69Hash',
            'expectedC68Hash',
            'expectedC67Hash',
            'expectedC66Hash',
            'expectedC65Hash',
            'expectedC64Hash',
            'expectedC63Hash',
            'expectedC62Hash',
            'expectedC61Hash',
            'expectedC60Hash',
        ] as $key) {
            $result = $this->execute([$key => '0000000000000000000000000000000000000000']);
            $this->assertSame('C71_BLOCKED_LINEAGE_LOCK_MISMATCH', $result['status'], $key);
        }
    }

    public function test_c71_records_database_dictionary_rule(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_rule_acknowledged']);
        $this->assertTrue($run['database_dictionary_read_summary']['dictionary_read_rule_complied']);
        $this->assertFalse($run['database_dictionary_read_summary']['dictionary_missing_coverage_detected']);
        $this->assertSame('market_benchmark_indicators.roc_20', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_roc20_source']);
        $this->assertSame('market_benchmark_indicators.ma20_slope_pct', $run['database_dictionary_read_summary']['market_index_mapping']['market_index_ma20_slope_pct_source']);
        $this->assertSame('IHSG', $run['database_dictionary_read_summary']['market_index_mapping']['benchmark_code']);
    }

    public function test_c71_candidate_scope_freeze_comes_from_c70_locked_decision(): void
    {
        $this->runService();
        $run = $this->readOutput();
        $scope = $run['candidate_scope_freeze_summary'];

        $this->assertTrue($scope['candidate_scope_freeze_completed']);
        $this->assertSame('C70_LOCKED_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_DECISION', $scope['candidate_scope_source']);
        $this->assertSame('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $scope['primary_candidate_code']);
        $this->assertSame(['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION'], $scope['backup_candidate_codes']);
        $this->assertSame(['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST'], $scope['comparator_only_candidate_codes']);
        $this->assertFalse($scope['candidate_scope_changed_after_c70']);
        $this->assertFalse($scope['new_candidate_created']);
        $this->assertFalse($scope['selection_rule_changed']);
        $this->assertFalse($scope['parameter_changed']);
        $this->assertFalse($scope['oos_result_used_for_new_ranking']);
        $this->assertFalse($scope['a01_promoted']);
    }

    public function test_c71_rejects_candidate_scope_mismatch_or_a01_promotion(): void
    {
        $primary = $this->mutateC70AndExecute(function (array $c70): array {
            foreach ($c70['production_deployment_execution_candidate_scorecard'] as &$row) {
                if ($row['candidate_code'] === 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE') {
                    $row['c70_role'] = 'BROKEN_PRIMARY';
                }
            }
            unset($row);
            return $c70;
        }, 'c70-primary-scope');
        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $primary['status']);

        $a01 = $this->execute(['options' => ['force_a01_promoted' => true]]);
        $this->assertSame('C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH', $a01['status']);
    }

    public function test_c71_summaries_and_scorecards_are_generated(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertTrue($run['runtime_path_inspection_summary']['runtime_path_inspection_completed']);
        $this->assertTrue($run['feature_flag_kill_switch_runtime_validation_summary']['default_off_feature_flag_pass']);
        $this->assertTrue($run['feature_flag_kill_switch_runtime_validation_summary']['kill_switch_runtime_validation_pass']);
        $this->assertTrue($run['shadow_read_execution_summary']['shadow_read_execution_proof_pass']);
        $this->assertTrue($run['dry_run_execution_summary']['dry_run_execution_proof_pass']);
        $this->assertTrue($run['plan_confirm_baseline_non_mutation_summary']['plan_confirm_output_non_mutation_pass']);
        $this->assertTrue($run['fallback_behavior_runtime_validation_summary']['fallback_behavior_runtime_validation_pass']);
        $this->assertTrue($run['source_bias_shared_core_runtime_validation_summary']['source_bias_governance_pass']);
        $this->assertTrue($run['source_bias_shared_core_runtime_validation_summary']['shared_core_governance_pass']);
        $this->assertTrue($run['production_mutation_safety_summary']['production_mutation_safety_pass']);
        $this->assertTrue($run['documentation_governance_summary']['documentation_governance_pass']);

        $rows = $this->indexByCode($run['shadow_read_or_dry_run_runtime_validation_candidate_scorecard']);
        $this->assertArrayHasKey('C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', $rows);
        $this->assertArrayHasKey('C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', $rows);
        $this->assertArrayHasKey('C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', $rows);
        $this->assertTrue($rows['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE']['shadow_read_or_dry_run_runtime_validation_pass']);
        $this->assertTrue($rows['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION']['shadow_read_or_dry_run_runtime_validation_pass']);
        $this->assertFalse($rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['shadow_read_or_dry_run_runtime_validation_pass']);
        $this->assertSame(['C71_A01_REMAINS_COMPARATOR_ONLY'], $rows['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST']['failure_reason_codes']);
    }

    public function test_c71_rejects_shadow_dry_run_runtime_gate_failures(): void
    {
        $cases = [
            ['force_feature_flag_default_on', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            ['force_shadow_read_flag_default_on', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            ['force_dry_run_flag_default_on', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            ['force_kill_switch_missing', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH'],
            ['force_shadow_read_missing', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_SHADOW_READ_PROOF_MISSING'],
            ['force_dry_run_missing', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_DRY_RUN_PROOF_MISSING'],
            ['force_plan_confirm_output_changed', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'],
            ['force_baseline_hash_changed', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_BASELINE_HASH_CHANGED'],
            ['force_fallback_missing', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FALLBACK_BEHAVIOR_MISSING'],
            ['force_bad_month_missing', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_BAD_MONTH_GOVERNANCE'],
            ['force_weak_regime_missing', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_WEAK_REGIME_GOVERNANCE'],
            ['force_source_bias_high', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            ['force_shared_core_high', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE'],
            ['force_production_mutation', 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_PRODUCTION_MUTATION'],
        ];

        foreach ($cases as $case) {
            $result = $this->execute(['options' => [$case[0] => true]]);
            $this->assertSame($case[1], $result['status'], $case[0]);
            $this->assertFalse($result['production_deployment_allowed'], $case[0]);
            $this->assertFalse($result['plan_confirm_runtime_reads_activated_catalog'], $case[0]);
        }
    }

    public function test_c71_bad_month_and_weak_regime_risks_are_retained(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ($run['bad_month_runtime_validation_review_results'] as $row) {
            $this->assertTrue($row['documented_bad_month_risk_retained']);
            $this->assertSame('MODERATE', $row['bad_month_risk_level']);
            $this->assertSame('PASS_WITH_DOCUMENTED_RISK', $row['bad_month_governance_decision']);
            $this->assertFalse($row['shadow_read_or_dry_run_runtime_validation_risk_free_claim']);
        }
        foreach ($run['weak_regime_runtime_validation_review_results'] as $row) {
            $this->assertTrue($row['weak_regime_retained']);
            $this->assertSame('market_down_or_sideways_high_vol', $row['weak_regime']);
            $this->assertSame('SUFFICIENT', $row['weak_regime_sample_status']);
            $this->assertFalse($row['weak_regime_sample_collapse_detected']);
        }
    }

    public function test_c71_c72_readiness_is_controlled_opt_in_only_when_passed(): void
    {
        $this->runService();
        $run = $this->readOutput();

        $this->assertSame(2, $run['c72_readiness_decision']['candidate_ready_for_c72_count']);
        $this->assertSame([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
        ], $run['c72_readiness_decision']['candidate_codes']);
        $this->assertSame('C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION', $run['c72_readiness_decision']['c72_recommendation']);
        $this->assertFalse($run['c72_readiness_decision']['production_deployment_allowed']);
        $this->assertFalse($run['c72_readiness_decision']['plan_confirm_runtime_reads_activated_catalog']);
    }

    public function test_c71_always_keeps_live_deployment_and_plan_confirm_flags_false(): void
    {
        $this->runService();
        $run = $this->readOutput();

        foreach ([
            'production_catalog_runtime_wired',
            'shadow_read_runtime_active',
            'dry_run_runtime_active',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $field) {
            $this->assertFalse($run[$field], $field);
            $this->assertFalse($run['shadow_read_or_dry_run_runtime_validation_decision'][$field], $field);
            $this->assertFalse($run['c72_readiness_decision'][$field], $field);
        }
    }

    private function runService(): array
    {
        return $this->execute();
    }

    private function execute(array $override = []): array
    {
        $service = new WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService();
        $locks = $this->actualLocks();
        $options = array_merge(['overwrite' => true], (array) ($override['options'] ?? []));

        return $service->execute(
            (string) ($override['c70Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C70_ARTIFACT),
            (string) ($override['expectedC70Hash'] ?? $locks['c70'][0]),
            (string) ($override['expectedC70FileSha1'] ?? $locks['c70'][1]),
            (string) ($override['c69Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C69_ARTIFACT),
            (string) ($override['expectedC69Hash'] ?? $locks['c69'][0]),
            (string) ($override['expectedC69FileSha1'] ?? $locks['c69'][1]),
            (string) ($override['c68Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C68_ARTIFACT),
            (string) ($override['expectedC68Hash'] ?? $locks['c68'][0]),
            (string) ($override['expectedC68FileSha1'] ?? $locks['c68'][1]),
            (string) ($override['c67Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C67_ARTIFACT),
            (string) ($override['expectedC67Hash'] ?? $locks['c67'][0]),
            (string) ($override['expectedC67FileSha1'] ?? $locks['c67'][1]),
            (string) ($override['c66Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C66_ARTIFACT),
            (string) ($override['expectedC66Hash'] ?? $locks['c66'][0]),
            (string) ($override['expectedC66FileSha1'] ?? $locks['c66'][1]),
            (string) ($override['c65Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C65_ARTIFACT),
            (string) ($override['expectedC65Hash'] ?? $locks['c65'][0]),
            (string) ($override['expectedC65FileSha1'] ?? $locks['c65'][1]),
            (string) ($override['c64Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C64_ARTIFACT),
            (string) ($override['expectedC64Hash'] ?? $locks['c64'][0]),
            (string) ($override['expectedC64FileSha1'] ?? $locks['c64'][1]),
            (string) ($override['c63Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C63_ARTIFACT),
            (string) ($override['expectedC63Hash'] ?? $locks['c63'][0]),
            (string) ($override['expectedC63FileSha1'] ?? $locks['c63'][1]),
            (string) ($override['c62Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C62_ARTIFACT),
            (string) ($override['expectedC62Hash'] ?? $locks['c62'][0]),
            (string) ($override['expectedC62FileSha1'] ?? $locks['c62'][1]),
            (string) ($override['c61Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C61_ARTIFACT),
            (string) ($override['expectedC61Hash'] ?? $locks['c61'][0]),
            (string) ($override['expectedC61FileSha1'] ?? $locks['c61'][1]),
            (string) ($override['c60Artifact'] ?? WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C60_ARTIFACT),
            (string) ($override['expectedC60Hash'] ?? $locks['c60'][0]),
            (string) ($override['expectedC60FileSha1'] ?? $locks['c60'][1]),
            $this->output,
            $options
        );
    }

    private function mutateC70AndExecute(callable $mutator, string $name): array
    {
        [$path, $hash, $sha1] = $this->writeMutatedArtifact(WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C70_ARTIFACT, $mutator, $name);
        return $this->execute([
            'c70Artifact' => $path,
            'expectedC70Hash' => $hash,
            'expectedC70FileSha1' => $sha1,
        ]);
    }

    private function writeMutatedArtifact(string $source, callable $mutator, string $name): array
    {
        $payload = json_decode((string) file_get_contents($source), true);
        $payload = $mutator(is_array($payload) ? $payload : []);
        $path = 'storage/app/watchlist/backtest/.tmp-'.$name.'.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->tmpFiles[] = $path;
        return [$path, (string) ($payload['artifact_hash'] ?? ''), strtoupper((string) sha1_file($path))];
    }

    private function readOutput(): array
    {
        return json_decode((string) file_get_contents($this->output), true);
    }

    private function actualLocks(): array
    {
        $paths = [
            'c70' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C70_ARTIFACT,
            'c69' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C69_ARTIFACT,
            'c68' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C68_ARTIFACT,
            'c67' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C67_ARTIFACT,
            'c66' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C66_ARTIFACT,
            'c65' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C65_ARTIFACT,
            'c64' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C64_ARTIFACT,
            'c63' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C63_ARTIFACT,
            'c62' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C62_ARTIFACT,
            'c61' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C61_ARTIFACT,
            'c60' => WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService::DEFAULT_C60_ARTIFACT,
        ];
        $locks = [];
        foreach ($paths as $key => $path) {
            $payload = json_decode((string) file_get_contents($path), true);
            $locks[$key] = [(string) ($payload['artifact_hash'] ?? ''), strtoupper((string) sha1_file($path))];
        }
        return $locks;
    }

    private function indexByCode(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['candidate_code']] = $row;
        }
        return $indexed;
    }
}
