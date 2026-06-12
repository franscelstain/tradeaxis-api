<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestExitModelRedesignContractService
{
    public const DEFAULT_C11_ARTIFACT_PATH = 'storage/app/watchlist/backtest/c11-exit-model-contract-audit.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json';

    public function execute(string $c11ArtifactPath, string $outputPath, array $options = []): array
    {
        if (! is_file($c11ArtifactPath)) {
            return $this->blocked('OPERATOR_ARTIFACT_REQUIRED', 'C11 exit-model contract audit artifact is required before C12 redesign contract can be produced.', [
                'c11_artifact_path' => $c11ArtifactPath,
            ]);
        }

        $c11 = $this->readJson($c11ArtifactPath);
        if ($c11 === []) {
            return $this->blocked('WS_BT_C12_C11_ARTIFACT_INVALID', 'C11 exit-model contract audit artifact is not valid JSON.', [
                'c11_artifact_path' => $c11ArtifactPath,
            ]);
        }

        $generatedAt = (string) ($options['generated_at'] ?? date(DATE_ATOM));
        $sourceDecision = is_array($c11['decision'] ?? null) ? $c11['decision'] : [];
        $sourceCatalog = is_array($c11['source_catalog'] ?? null) ? $c11['source_catalog'] : [];
        $sourceC10 = is_array($c11['c10_summary'] ?? null) ? $c11['c10_summary'] : [];
        $sourceQuality = is_array($c11['strategy_quality_gate_summary'] ?? null) ? $c11['strategy_quality_gate_summary'] : [];
        $sourceCodeContract = is_array($c11['code_contract_audit'] ?? null) ? $c11['code_contract_audit'] : [];

        $contract = $this->redesignContract($sourceDecision, $sourceC10, $sourceQuality, $sourceCodeContract);
        $artifact = [
            'meta' => [
                'artifact_version' => 'WATCHLIST_C12_EXIT_MODEL_REDESIGN_CONTRACT_V1',
                'generated_at' => $generatedAt,
                'scope' => 'C12_EXIT_MODEL_REDESIGN_CONTRACT_ONLY',
                'source_c11_artifact_path' => $c11ArtifactPath,
                'source_c11_file_sha1' => sha1_file($c11ArtifactPath) ?: '',
                'source_c11_artifact_hash' => (string) ($c11['meta']['artifact_hash'] ?? ''),
                'oos_executed' => false,
                'strategy_catalog_created' => false,
                'production_ready' => false,
            ],
            'source_catalog' => [
                'catalog_code' => (string) ($sourceCatalog['catalog_code'] ?? ''),
                'catalog_version' => (string) ($sourceCatalog['catalog_version'] ?? ''),
                'catalog_count' => (string) ($sourceCatalog['catalog_count'] ?? ''),
                'catalog_hash' => (string) ($sourceCatalog['catalog_hash'] ?? ''),
                'catalog_mutated' => false,
            ],
            'source_c11_decision' => [
                'status' => (string) ($sourceDecision['status'] ?? ''),
                'reason_code' => (string) ($sourceDecision['reason_code'] ?? ''),
                'exit_model_catalog_authorized' => (bool) ($sourceDecision['exit_model_catalog_authorized'] ?? false),
                'next_decision' => (string) ($sourceDecision['next_decision'] ?? ''),
                'blocking_reasons' => is_array($sourceDecision['blocking_reasons'] ?? null)
                    ? array_values($sourceDecision['blocking_reasons'])
                    : [],
            ],
            'source_c10_evidence' => [
                'target_hit_share' => $sourceC10['target_hit_share'] ?? null,
                'stop_or_timeout_share' => $sourceC10['stop_or_timeout_share'] ?? null,
                'exit_totals' => is_array($sourceC10['exit_totals'] ?? null) ? $sourceC10['exit_totals'] : [],
                'strategy_quality_gate_summary' => $sourceQuality,
            ],
            'redesign_contract' => $contract,
            'decision' => [
                'status' => 'EXIT_MODEL_REDESIGN_CONTRACT_READY',
                'reason_code' => 'WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY',
                'design_contract_ready' => true,
                'strategy_catalog_created' => false,
                'catalog_creation_authorized' => false,
                'exit_model_catalog_authorized' => false,
                'next_required_step' => 'IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG',
                'oos_eligible' => false,
                'production_ready' => false,
            ],
            'no_oos_leakage_summary' => [
                'oos_service_invoked' => false,
                'oos_repository_invoked' => false,
                'oos_executed' => false,
                'production_ready' => false,
            ],
            'validation' => [
                'source_c11_artifact_exists' => true,
                'source_c11_artifact_hash_present' => trim((string) ($c11['meta']['artifact_hash'] ?? '')) !== '',
                'source_c11_catalog_not_authorized' => ! (bool) ($sourceDecision['exit_model_catalog_authorized'] ?? false),
                'source_c11_next_catalog_not_designed' => (string) ($sourceDecision['next_decision'] ?? '') === 'NEXT_CATALOG_NOT_DESIGNED',
                'contract_has_allowed_axis_policy' => ($contract['allowed_first_phase_axis_policy'] ?? []) !== [],
                'contract_keeps_catalog_creation_blocked' => true,
                'contract_keeps_oos_blocked' => true,
                'artifact_hash' => null,
            ],
        ];

        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];

        $write = $this->writeJson($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked((string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), 'C12 redesign contract artifact write failed.', [
                'c11_artifact_path' => $c11ArtifactPath,
                'output_path' => $outputPath,
            ]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'status' => 'DONE',
            'reason_code' => 'WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY',
            'artifact_hash' => $artifact['meta']['artifact_hash'],
            'artifact' => $artifact,
            'write' => $write,
            'production_ready' => false,
        ];
    }

    private function redesignContract(array $sourceDecision, array $sourceC10, array $sourceQuality, array $sourceCodeContract): array
    {
        return [
            'objective' => 'Define a future exit-model or strategy-family implementation contract without creating a new catalog in this session.',
            'evidence_basis' => [
                'source_c11_status' => (string) ($sourceDecision['status'] ?? ''),
                'source_c11_blocking_reasons' => is_array($sourceDecision['blocking_reasons'] ?? null)
                    ? array_values($sourceDecision['blocking_reasons'])
                    : [],
                'target_hit_share' => $sourceC10['target_hit_share'] ?? null,
                'stop_or_timeout_share' => $sourceC10['stop_or_timeout_share'] ?? null,
                'best_median_ret_net_top' => $sourceQuality['best_median_ret_net_top'] ?? null,
                'best_p25_ret_net_top' => $sourceQuality['best_p25_ret_net_top'] ?? null,
                'best_month_win_rate_min' => $sourceQuality['best_month_win_rate_min'] ?? null,
            ],
            'hard_boundaries' => [
                'do_not_mutate_r1_r2_c01_c02_c03_c04_c05_c06_c07' => true,
                'do_not_change_locked_is_quality_gates' => true,
                'do_not_select_best_of_failed' => true,
                'do_not_run_oos' => true,
                'do_not_claim_production_ready' => true,
                'do_not_add_fake_sector_filter' => true,
            ],
            'allowed_first_phase_axis_policy' => [
                [
                    'axis' => 'risk.min_rr',
                    'authorization' => 'CONDITIONALLY_ALLOWED_FOR_FUTURE_IMPLEMENTATION',
                    'why' => 'C10 target-hit share is low while stop-or-timeout dominates; min_rr is runtime/schema supported but fixed in C01-C07.',
                    'required_before_catalog' => [
                        'new_catalog_definition_entry_in_paramset_factory',
                        'calibration_execution_definition_update',
                        'static_guard_showing_c01_c07_remain_fixed',
                        'two_run_is_only_seed_and_calibration_proof',
                    ],
                ],
                [
                    'axis' => 'risk.stop_atr_mult',
                    'authorization' => 'CONDITIONALLY_ALLOWED_FOR_FUTURE_IMPLEMENTATION',
                    'why' => 'Stop distance is runtime/schema supported, but changing it can worsen downside; any future use must keep locked p25 and stability gates unchanged.',
                    'required_before_catalog' => [
                        'new_catalog_definition_entry_in_paramset_factory',
                        'explicit_downside_risk_rationale_per_row',
                        'static_guard_showing_no_gate_relaxation',
                        'two_run_is_only_seed_and_calibration_proof',
                    ],
                ],
            ],
            'blocked_first_phase_axis_policy' => [
                [
                    'axis' => 'backtest.holding_days',
                    'authorization' => 'BLOCKED_FOR_FIRST_PHASE_CATALOG',
                    'why' => 'Metrics can consume holding_days, but published-price runtime currently forces HOLD=5 and boundary censoring must be redesigned before this axis can vary.',
                    'required_before_use' => [
                        'published_price_runtime_holding_days_override_contract',
                        'boundary_censoring_rule_update',
                        'strict_is_boundary_tests_for_each_holding_day',
                        'operator_artifact_showing_max_requested_market_data_date_within_is',
                    ],
                ],
                [
                    'axis' => 'backtest.target_pct|backtest.stop_pct',
                    'authorization' => 'BLOCKED_UNTIL_SCHEMA_OR_APPROVED_EXTENSION',
                    'why' => 'Metrics can consume target_pct and stop_pct when present, but the official param-grid schema and curated catalog rows do not expose them.',
                    'required_before_use' => [
                        'schema_or_approved_paramset_extension_contract',
                        'repository_persistence_support',
                        'factory_projection_support',
                        'artifact_manifest_support',
                    ],
                ],
            ],
            'required_implementation_sequence_before_any_catalog' => [
                'create_new_catalog_identity_only_after_contract_support_exists',
                'keep_c01_c07_fixed_execution_snapshot_guards',
                'add_factory_and_calibration_definitions_for_the_new_family_only',
                'add static/unit guards for no_oos_no_best_of_failed_no_gate_relaxation',
                'seed_new_catalog_idempotently',
                'run_is_calibration_twice_only',
                'allow_oos_only_if_is_valid_param_count_ge_1_and_best_binding_hash_is_non_empty',
            ],
            'current_code_observations' => [
                'factory_rejects_fixed_execution_snapshot_drift' => (bool) ($sourceCodeContract['factory_rejects_fixed_execution_snapshot_drift'] ?? false),
                'published_runtime_forces_holding_days_5' => (bool) ($sourceCodeContract['published_runtime_forces_holding_days_5'] ?? false),
                'param_grid_schema_exposes_target_stop_pct' => (bool) ($sourceCodeContract['param_grid_schema_exposes_target_stop_pct'] ?? false),
                'metrics_consumes_target_stop_pct_when_present' => (bool) ($sourceCodeContract['metrics_consumes_target_stop_pct_when_present'] ?? false),
            ],
        ];
    }

    private function readJson(string $path): array
    {
        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeJson(string $outputPath, array $artifact, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID'];
        }
        if (is_dir($outputPath)) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID'];
        }
        $directory = dirname($outputPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_DIRECTORY_UNAVAILABLE'];
        }

        $payload = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_JSON_ENCODE_FAILED'];
        }

        $tmp = $outputPath.'.tmp';
        if (file_put_contents($tmp, $payload.PHP_EOL) === false || ! rename($tmp, $outputPath)) {
            @unlink($tmp);

            return ['is_ready' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'];
        }

        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITTEN',
            'path' => $outputPath,
            'sha1' => sha1_file($outputPath) ?: '',
        ];
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_merge([
            'ready' => false,
            'is_ready' => false,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_hash' => null,
            'oos_executed' => false,
            'production_ready' => false,
        ], $extra);
    }

    private function artifactForHash(array $artifact): array
    {
        unset($artifact['meta']['generated_at'], $artifact['meta']['artifact_hash'], $artifact['validation']['artifact_hash']);

        return $artifact;
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalize($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
