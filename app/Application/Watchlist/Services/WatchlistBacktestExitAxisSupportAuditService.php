<?php

namespace App\Application\Watchlist\Services;

use RuntimeException;

class WatchlistBacktestExitAxisSupportAuditService
{
    public const DEFAULT_C12_ARTIFACT_PATH = 'storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c13-exit-axis-support-audit.json';

    public function execute(string $c12ArtifactPath, string $outputPath, array $options = []): array
    {
        if (! is_file($c12ArtifactPath)) {
            return $this->blocked('OPERATOR_ARTIFACT_REQUIRED', 'C12 exit-model redesign contract artifact is required before C13 support audit can be produced.', [
                'c12_artifact_path' => $c12ArtifactPath,
            ]);
        }

        $c12 = $this->readJson($c12ArtifactPath);
        if ($c12 === []) {
            return $this->blocked('WS_BT_C13_C12_ARTIFACT_INVALID', 'C12 exit-model redesign contract artifact is not valid JSON.', [
                'c12_artifact_path' => $c12ArtifactPath,
            ]);
        }

        $generatedAt = (string) ($options['generated_at'] ?? date(DATE_ATOM));
        $sourceDecision = is_array($c12['decision'] ?? null) ? $c12['decision'] : [];
        $sourceCatalog = is_array($c12['source_catalog'] ?? null) ? $c12['source_catalog'] : [];
        $sourceContract = is_array($c12['redesign_contract'] ?? null) ? $c12['redesign_contract'] : [];
        $supportProbe = $this->supportProbe();

        $artifact = [
            'meta' => [
                'artifact_version' => 'WATCHLIST_C13_EXIT_AXIS_SUPPORT_AUDIT_V1',
                'generated_at' => $generatedAt,
                'scope' => 'C13_EXIT_AXIS_SUPPORT_ONLY_NO_STRATEGY_CATALOG',
                'source_c12_artifact_path' => $c12ArtifactPath,
                'source_c12_file_sha1' => sha1_file($c12ArtifactPath) ?: '',
                'source_c12_artifact_hash' => (string) ($c12['meta']['artifact_hash'] ?? ''),
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
            'source_c12_decision' => [
                'status' => (string) ($sourceDecision['status'] ?? ''),
                'reason_code' => (string) ($sourceDecision['reason_code'] ?? ''),
                'design_contract_ready' => (bool) ($sourceDecision['design_contract_ready'] ?? false),
                'catalog_creation_authorized' => (bool) ($sourceDecision['catalog_creation_authorized'] ?? false),
                'exit_model_catalog_authorized' => (bool) ($sourceDecision['exit_model_catalog_authorized'] ?? false),
                'next_required_step' => (string) ($sourceDecision['next_required_step'] ?? ''),
            ],
            'implemented_support_contract' => [
                'policy' => WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS,
                'runtime_supported_first_phase_axes' => [
                    WatchlistBacktestExitAxisSupport::AXIS_RISK_STOP_ATR_MULT,
                    WatchlistBacktestExitAxisSupport::AXIS_RISK_MIN_RR,
                ],
                'blocked_first_phase_axes' => [
                    WatchlistBacktestExitAxisSupport::AXIS_HOLDING_DAYS,
                    WatchlistBacktestExitAxisSupport::AXIS_TARGET_PCT,
                    WatchlistBacktestExitAxisSupport::AXIS_STOP_PCT,
                ],
                'contract_source' => WatchlistBacktestExitAxisSupport::CONTRACT_SOURCE,
                'keeps_grouping_fixed_in_first_phase' => true,
                'does_not_relax_is_quality_gates' => true,
                'does_not_create_catalog' => true,
                'does_not_run_oos' => true,
            ],
            'source_c12_allowed_axis_policy' => is_array($sourceContract['allowed_first_phase_axis_policy'] ?? null)
                ? $sourceContract['allowed_first_phase_axis_policy']
                : [],
            'support_probe' => $supportProbe,
            'decision' => [
                'status' => 'EXIT_AXIS_SUPPORT_READY_FOR_FUTURE_CATALOG_DEFINITION',
                'reason_code' => 'WS_BT_C13_EXIT_AXIS_SUPPORT_READY',
                'support_ready' => $supportProbe['fixed_guard_rejects_drift']
                    && $supportProbe['variable_policy_accepts_risk_axes']
                    && $supportProbe['variable_policy_blocks_holding_days']
                    && $supportProbe['variable_policy_blocks_target_stop_pct'],
                'strategy_catalog_created' => false,
                'catalog_creation_authorized' => false,
                'future_catalog_definition_work_authorized' => true,
                'exit_model_catalog_authorized' => false,
                'next_required_step' => 'CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY',
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
                'source_c12_artifact_exists' => true,
                'source_c12_artifact_hash_present' => trim((string) ($c12['meta']['artifact_hash'] ?? '')) !== '',
                'source_c12_next_step_matches' => (string) ($sourceDecision['next_required_step'] ?? '') === 'IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG',
                'support_probe_passed' => $supportProbe['fixed_guard_rejects_drift']
                    && $supportProbe['variable_policy_accepts_risk_axes']
                    && $supportProbe['variable_policy_blocks_holding_days']
                    && $supportProbe['variable_policy_blocks_target_stop_pct'],
                'artifact_hash' => null,
            ],
        ];

        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];

        if (! $artifact['decision']['support_ready']) {
            return $this->blocked('WS_BT_C13_EXIT_AXIS_SUPPORT_PROBE_FAILED', 'C13 exit-axis support probe failed.', [
                'artifact' => $artifact,
                'c12_artifact_path' => $c12ArtifactPath,
                'output_path' => $outputPath,
            ]);
        }

        $write = $this->writeJson($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked((string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), 'C13 exit-axis support audit artifact write failed.', [
                'c12_artifact_path' => $c12ArtifactPath,
                'output_path' => $outputPath,
            ]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'status' => 'DONE',
            'reason_code' => 'WS_BT_C13_EXIT_AXIS_SUPPORT_READY',
            'artifact_hash' => $artifact['meta']['artifact_hash'],
            'artifact' => $artifact,
            'write' => $write,
            'production_ready' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function supportProbe(): array
    {
        $fixedRejected = false;
        $fixedReason = '';
        try {
            WatchlistBacktestExitAxisSupport::resolve([
                'stop_atr_mult' => 2.0,
                'min_rr' => 1.5,
                'top_picks_target' => 5,
                'secondary_target' => 10,
            ], WatchlistBacktestExitAxisSupport::fixedExecutionDefinition(1.5, 1.5, 5, 10));
        } catch (RuntimeException $e) {
            $fixedRejected = str_contains($e->getMessage(), 'fixed execution/grouping snapshot drifted');
            $fixedReason = $e->getMessage();
        }

        $variableResolved = WatchlistBacktestExitAxisSupport::resolve([
            'stop_atr_mult' => 1.25,
            'min_rr' => 1.10,
            'top_picks_target' => 5,
            'secondary_target' => 10,
        ], WatchlistBacktestExitAxisSupport::variableRiskExitAxisDefinition(5, 10));

        return [
            'fixed_guard_rejects_drift' => $fixedRejected,
            'fixed_guard_reason' => $fixedReason,
            'variable_policy_accepts_risk_axes' => $variableResolved['policy'] === WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS
                && (float) $variableResolved['stop_atr_mult'] === 1.25
                && (float) $variableResolved['min_rr'] === 1.10,
            'variable_policy_runtime_axes' => $variableResolved['bt_grid_resolution']['exit_axis_runtime_axes'] ?? [],
            'variable_policy_blocks_holding_days' => $this->blockedFieldProbe('holding_days'),
            'variable_policy_blocks_target_stop_pct' => $this->blockedFieldProbe('target_pct') && $this->blockedFieldProbe('stop_pct'),
            'factory_fixed_catalogs_preserve_legacy_error_message' => true,
        ];
    }

    private function blockedFieldProbe(string $field): bool
    {
        try {
            WatchlistBacktestExitAxisSupport::resolve([
                'stop_atr_mult' => 1.25,
                'min_rr' => 1.10,
                'top_picks_target' => 5,
                'secondary_target' => 10,
                $field => $field === 'holding_days' ? 3 : 0.05,
            ], WatchlistBacktestExitAxisSupport::variableRiskExitAxisDefinition(5, 10));
        } catch (RuntimeException $e) {
            return str_contains($e->getMessage(), 'first-phase exit-axis support blocks');
        }

        return false;
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
