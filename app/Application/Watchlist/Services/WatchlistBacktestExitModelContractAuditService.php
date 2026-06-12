<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestExitModelContractAuditService
{
    public const DEFAULT_C10_SUMMARY_PATH = 'storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c11-exit-model-contract-audit.json';

    public function execute(string $summaryPath, string $outputPath, array $options = []): array
    {
        if (! is_file($summaryPath)) {
            return $this->blocked('OPERATOR_ARTIFACT_REQUIRED', 'C10 exit-model summary CSV is required before an exit-model catalog can be designed.', [
                'summary_path' => $summaryPath,
            ]);
        }

        $rows = $this->readCsv($summaryPath);
        if ($rows === []) {
            return $this->blocked('WS_BT_C11_SUMMARY_EMPTY', 'C10 exit-model summary CSV has no data rows.', [
                'summary_path' => $summaryPath,
            ]);
        }

        $generatedAt = (string) ($options['generated_at'] ?? date(DATE_ATOM));
        $summarySha1 = sha1_file($summaryPath) ?: '';
        $metricRanges = $this->metricRanges($rows);
        $exitTotals = $this->exitTotals($rows);
        $catalog = $this->catalogSummary($rows);
        $codeContract = $this->codeContractAudit();
        $quality = $this->qualityGateSummary($metricRanges);

        $artifact = [
            'meta' => [
                'artifact_version' => 'WATCHLIST_C11_EXIT_MODEL_CONTRACT_AUDIT_V1',
                'generated_at' => $generatedAt,
                'scope' => 'C11_EXIT_MODEL_CONTRACT_AUDIT_IS_ONLY',
                'source_summary_path' => $summaryPath,
                'source_summary_sha1' => $summarySha1,
                'oos_executed' => false,
                'strategy_catalog_created' => false,
                'production_ready' => false,
            ],
            'source_catalog' => $catalog,
            'c10_summary' => [
                'row_count' => count($rows),
                'metric_ranges' => $metricRanges,
                'exit_totals' => $exitTotals,
                'target_hit_share' => $this->ratio($exitTotals['hit_target_total'], $exitTotals['known_exit_outcome_total']),
                'stop_or_timeout_share' => $this->ratio(
                    $exitTotals['hit_stop_total'] + $exitTotals['timeout_hold_expired_total'],
                    $exitTotals['known_exit_outcome_total']
                ),
                'missing_runtime_evidence_fields' => $this->uniqueValues($rows, 'missing_runtime_evidence_fields'),
                'nullable_runtime_no_positive_evidence_fields' => $this->uniqueValues($rows, 'nullable_runtime_no_positive_evidence_fields'),
                'next_focus_values' => $this->uniqueValues($rows, 'next_focus'),
                'next_decision_values' => $this->uniqueValues($rows, 'next_decision'),
                'oos_executed_values' => $this->uniqueValues($rows, 'oos_executed'),
                'production_ready_values' => $this->uniqueValues($rows, 'production_ready'),
            ],
            'runtime_axis_support' => [
                [
                    'axis' => 'risk.stop_atr_mult',
                    'runtime_support' => 'SUPPORTED_BY_GRID_SCHEMA_FACTORY_AND_METRICS',
                    'current_catalog_status' => 'FIXED_FOR_R1_R2_C01_C02_C03_C04_C05_C06_C07',
                    'future_use_requirement' => 'REQUIRES_NEW_EXPLICIT_CATALOG_CONTRACT_AND_FACTORY_DEFINITION',
                ],
                [
                    'axis' => 'risk.min_rr',
                    'runtime_support' => 'SUPPORTED_BY_GRID_SCHEMA_FACTORY_AND_METRICS',
                    'current_catalog_status' => 'FIXED_FOR_R1_R2_C01_C02_C03_C04_C05_C06_C07',
                    'future_use_requirement' => 'REQUIRES_NEW_EXPLICIT_CATALOG_CONTRACT_AND_FACTORY_DEFINITION',
                ],
                [
                    'axis' => 'backtest.holding_days',
                    'runtime_support' => 'METRICS_SERVICE_CONSUMES_VALUE',
                    'current_catalog_status' => 'PUBLISHED_PRICE_RUNTIME_FORCES_HOLD_5',
                    'future_use_requirement' => 'REQUIRES_BOUNDARY_CENSORING_CONTRACT_CHANGE_AND_TEST_PROOF',
                ],
                [
                    'axis' => 'backtest.target_pct|backtest.stop_pct',
                    'runtime_support' => 'METRICS_SERVICE_CONSUMES_VALUES_WHEN_PRESENT',
                    'current_catalog_status' => 'NOT_PRESENT_IN_PARAM_GRID_SCHEMA_OR_CURATED_FACTORY_ROWS',
                    'future_use_requirement' => 'REQUIRES_SCHEMA_OR_APPROVED_EXTENSION_CONTRACT_BEFORE_CATALOG_USE',
                ],
            ],
            'code_contract_audit' => $codeContract,
            'strategy_quality_gate_summary' => $quality,
            'decision' => [
                'status' => 'EXIT_MODEL_CATALOG_NOT_AUTHORIZED',
                'reason_code' => 'WS_BT_C11_EXIT_MODEL_CONTRACT_REQUIRED_BEFORE_CATALOG',
                'exit_model_catalog_authorized' => false,
                'strategy_catalog_created' => false,
                'next_decision' => 'NEXT_CATALOG_NOT_DESIGNED',
                'oos_eligible' => false,
                'production_ready' => false,
                'blocking_reasons' => $this->blockingReasons($codeContract, $quality, $exitTotals),
            ],
            'no_oos_leakage_summary' => [
                'oos_service_invoked' => false,
                'oos_repository_invoked' => false,
                'oos_executed' => false,
                'production_ready' => false,
            ],
            'validation' => [
                'source_summary_exists' => true,
                'source_summary_sha1' => $summarySha1,
                'source_summary_has_rows' => count($rows) > 0,
                'all_rows_oos_not_run' => $this->allRowsEqual($rows, 'oos_executed', '0'),
                'all_rows_not_production_ready' => $this->allRowsEqual($rows, 'production_ready', '0'),
                'catalog_not_mutated' => true,
                'oos_not_run' => true,
                'artifact_hash' => null,
            ],
        ];

        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];

        $write = $this->writeJson($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked((string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), 'C11 contract audit artifact write failed.', [
                'summary_path' => $summaryPath,
                'output_path' => $outputPath,
            ]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'status' => 'DONE',
            'reason_code' => 'WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_READY',
            'artifact_hash' => $artifact['meta']['artifact_hash'],
            'artifact' => $artifact,
            'write' => $write,
            'production_ready' => false,
        ];
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers) || $headers === []) {
            fclose($handle);

            return [];
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (! is_array($values) || $values === []) {
                continue;
            }
            $row = [];
            foreach ($headers as $index => $header) {
                $row[(string) $header] = (string) ($values[$index] ?? '');
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function catalogSummary(array $rows): array
    {
        return [
            'catalog_code' => $this->singleValue($rows, 'catalog_code'),
            'catalog_version' => $this->singleValue($rows, 'catalog_version'),
            'catalog_count' => $this->singleValue($rows, 'catalog_count'),
            'catalog_hash' => $this->singleValue($rows, 'catalog_hash'),
            'catalog_codes' => $this->uniqueValues($rows, 'catalog_code'),
            'catalog_versions' => $this->uniqueValues($rows, 'catalog_version'),
            'catalog_hashes' => $this->uniqueValues($rows, 'catalog_hash'),
            'strategy_catalog_created' => false,
        ];
    }

    private function metricRanges(array $rows): array
    {
        $fields = [
            'picks_count',
            'median_ret_net_top',
            'p25_ret_net_top',
            'month_win_rate_min',
            'hit_target_count',
            'hit_stop_count',
            'timeout_hold_expired_count',
        ];
        $ranges = [];
        foreach ($fields as $field) {
            $values = $this->numericValues($rows, $field);
            $ranges[$field] = [
                'min' => $values === [] ? null : min($values),
                'max' => $values === [] ? null : max($values),
            ];
        }

        return $ranges;
    }

    private function exitTotals(array $rows): array
    {
        $target = $this->sumInt($rows, 'hit_target_count');
        $stop = $this->sumInt($rows, 'hit_stop_count');
        $timeout = $this->sumInt($rows, 'timeout_hold_expired_count');

        return [
            'hit_target_total' => $target,
            'hit_stop_total' => $stop,
            'timeout_hold_expired_total' => $timeout,
            'known_exit_outcome_total' => $target + $stop + $timeout,
            'stop_plus_timeout_gt_target' => ($stop + $timeout) > $target,
        ];
    }

    private function qualityGateSummary(array $ranges): array
    {
        $bestMedian = $ranges['median_ret_net_top']['max'];
        $bestP25 = $ranges['p25_ret_net_top']['max'];
        $bestMonthlyWin = $ranges['month_win_rate_min']['max'];

        return [
            'median_return_gate' => '>= 0',
            'best_median_ret_net_top' => $bestMedian,
            'best_median_passes_gate' => $bestMedian !== null && $bestMedian >= 0,
            'p25_downside_gate' => '>= -0.03',
            'best_p25_ret_net_top' => $bestP25,
            'best_p25_passes_gate' => $bestP25 !== null && $bestP25 >= -0.03,
            'monthly_win_rate_gate' => '>= 0.45',
            'best_month_win_rate_min' => $bestMonthlyWin,
            'best_month_win_rate_passes_gate' => $bestMonthlyWin !== null && $bestMonthlyWin >= 0.45,
        ];
    }

    private function codeContractAudit(): array
    {
        $factory = $this->fileContents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));
        $runtime = $this->fileContents(base_path('app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php'));
        $metrics = $this->fileContents(base_path('app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php'));
        $repository = $this->fileContents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));

        return [
            'factory_rejects_fixed_execution_snapshot_drift' => strpos($factory, 'fixed execution/grouping snapshot drifted') !== false,
            'factory_defines_c07_as_fixed_execution_catalog' => strpos($factory, 'WatchlistBacktestC07ParamGridCatalog::FIXED_STOP_ATR_MULT') !== false
                && strpos($factory, 'WatchlistBacktestC07ParamGridCatalog::FIXED_MIN_RR') !== false,
            'metrics_consumes_stop_atr_mult_and_min_rr' => strpos($metrics, 'stop_atr_mult') !== false
                && strpos($metrics, 'min_rr') !== false
                && strpos($metrics, 'ATR_RR_FALLBACK') !== false,
            'metrics_consumes_target_stop_pct_when_present' => strpos($metrics, "target_pct") !== false
                && strpos($metrics, "stop_pct") !== false
                && strpos($metrics, 'BACKTEST_FIXED_PERCENT') !== false,
            'metrics_consumes_holding_days' => strpos($metrics, "holding_days") !== false,
            'published_runtime_forces_holding_days_5' => strpos($runtime, "'holding_days' => 5") !== false,
            'param_grid_schema_exposes_stop_atr_mult_and_min_rr' => strpos($repository, "'stop_atr_mult', 'min_rr'") !== false,
            'param_grid_schema_exposes_target_stop_pct' => strpos($repository, 'target_pct') !== false
                || strpos($repository, 'stop_pct') !== false,
            'oos_service_dependency' => false,
            'oos_repository_dependency' => false,
        ];
    }

    private function blockingReasons(array $codeContract, array $quality, array $exitTotals): array
    {
        $reasons = [];
        if (! ($codeContract['factory_rejects_fixed_execution_snapshot_drift'] ?? false)) {
            $reasons[] = 'FACTORY_FIXED_EXECUTION_GUARD_NOT_PROVEN';
        } else {
            $reasons[] = 'C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT';
        }
        if ($codeContract['published_runtime_forces_holding_days_5'] ?? false) {
            $reasons[] = 'PUBLISHED_RUNTIME_FORCES_HOLD_5';
        }
        if (! ($codeContract['param_grid_schema_exposes_target_stop_pct'] ?? false)) {
            $reasons[] = 'PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS';
        }
        if (! ($quality['best_median_passes_gate'] ?? false)
            || ! ($quality['best_p25_passes_gate'] ?? false)
            || ! ($quality['best_month_win_rate_passes_gate'] ?? false)) {
            $reasons[] = 'C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES';
        }
        if ($exitTotals['stop_plus_timeout_gt_target'] ?? false) {
            $reasons[] = 'C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET';
        }

        return $reasons;
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

    private function numericValues(array $rows, string $field): array
    {
        $values = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && is_numeric($value)) {
                $values[] = (float) $value;
            }
        }

        return $values;
    }

    private function sumInt(array $rows, string $field): int
    {
        $sum = 0;
        foreach ($rows as $row) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && is_numeric($value)) {
                $sum += (int) $value;
            }
        }

        return $sum;
    }

    private function singleValue(array $rows, string $field): string
    {
        $values = $this->uniqueValues($rows, $field);

        return count($values) === 1 ? $values[0] : '';
    }

    private function uniqueValues(array $rows, string $field): array
    {
        $values = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $values[$value] = $value;
            }
        }
        ksort($values, SORT_STRING);

        return array_values($values);
    }

    private function allRowsEqual(array $rows, string $field, string $expected): bool
    {
        foreach ($rows as $row) {
            if ((string) ($row[$field] ?? '') !== $expected) {
                return false;
            }
        }

        return true;
    }

    private function ratio(int $numerator, int $denominator): ?float
    {
        if ($denominator <= 0) {
            return null;
        }

        return $numerator / $denominator;
    }

    private function fileContents(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
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
