<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService
{
    public const ARTIFACT_TYPE = 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC';
    public const DEFAULT_C27_INPUT_PATH = 'storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;

    public const PRIMARY_PROFILE = 'C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY';

    public static function diagnosticProfiles(): array
    {
        return [
            'C28_G00_RAW_R09_BASELINE' => [
                'family' => 'baseline',
                'candidate_role' => 'raw_r09_baseline',
                'source' => 'r09',
                'description' => 'Raw R09 from C27.',
            ],
            'C28_G01_RAW_G21_ORIGINAL' => [
                'family' => 'original_candidate',
                'candidate_role' => 'raw_g21_original',
                'source' => 'g21',
                'description' => 'Original C27 raw G21 candidate.',
            ],
            'C28_G02_RAW_G13_GLOBAL' => [
                'family' => 'global_comparator',
                'candidate_role' => 'raw_g13_global',
                'source' => 'g13',
                'description' => 'Raw G13 global target comparator.',
            ],
            'C28_G03_RAW_G16_GLOBAL' => [
                'family' => 'global_comparator',
                'candidate_role' => 'raw_g16_global',
                'source' => 'g16',
                'description' => 'Raw G16 global target comparator.',
            ],
            'C28_G04_G21_PROBLEM_BUCKETS_ELSE_R09' => [
                'family' => 'bucket_revision',
                'candidate_role' => 'g21_on_no_signal_and_delay_else_r09',
                'source' => 'g21_problem_buckets_else_r09',
                'description' => 'Use G21 only on no-signal and next-open-delay buckets; keep R09 on already-stable candidate bucket.',
            ],
            self::PRIMARY_PROFILE => [
                'family' => 'bucket_revision',
                'candidate_role' => 'r09_stable_g21_no_signal_g16_delay',
                'source' => 'r09_stable_g21_no_signal_g16_delay',
                'description' => 'Use R09 on stable bucket, G21 on no-signal fallback bucket, and G16 on next-open-delay bucket.',
            ],
            'C28_G06_BUCKET_TIEBREAK_R09_STABLE_G13_NO_SIGNAL_G16_DELAY' => [
                'family' => 'bucket_revision',
                'candidate_role' => 'r09_stable_g13_no_signal_g16_delay',
                'source' => 'r09_stable_g13_no_signal_g16_delay',
                'description' => 'Use R09 on stable bucket, G13 on no-signal fallback bucket, and G16 on next-open-delay bucket.',
            ],
            'C28_G07_G13_GLOBAL_WITH_G21_NO_SIGNAL' => [
                'family' => 'hybrid_comparator',
                'candidate_role' => 'g13_global_g21_no_signal',
                'source' => 'g13_global_g21_no_signal',
                'description' => 'Use G21 on no-signal fallback bucket; otherwise use G13.',
            ],
            'C28_G08_G16_GLOBAL_WITH_G21_NO_SIGNAL' => [
                'family' => 'hybrid_comparator',
                'candidate_role' => 'g16_global_g21_no_signal',
                'source' => 'g16_global_g21_no_signal',
                'description' => 'Use G21 on no-signal fallback bucket; otherwise use G16.',
            ],
            'C28_G09_PRIMARY_READINESS_SCORE' => [
                'family' => 'readiness_score',
                'candidate_role' => 'primary_readiness_score',
                'source' => 'r09_stable_g21_no_signal_g16_delay',
                'description' => 'Readiness score row for the explicit C28 primary revised candidate.',
            ],
        ];
    }

    public function execute(string $c27InputPath = '', string $outputPath = '', array $options = []): array
    {
        $catalogCode = (string) ($options['catalog_code'] ?? self::DEFAULT_SOURCE_CATALOG_CODE);
        $fromDate = (string) ($options['from'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $toDate = (string) ($options['to'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $c27InputPath = trim($c27InputPath) !== '' ? trim($c27InputPath) : self::DEFAULT_C27_INPUT_PATH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        $candidateProfile = strtoupper(trim((string) ($options['candidate_profile_code'] ?? self::PRIMARY_PROFILE)));

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C28_IS_ONLY_WINDOW_MISMATCH', 'C28 rule revision/tiebreak diagnostic requires the frozen IS window only.', $outputPath, $options, [
                'catalog_code' => $catalogCode,
                'from' => $fromDate,
                'to' => $toDate,
            ]);
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.', '', $options);
        }

        $profiles = self::diagnosticProfiles();
        if (! isset($profiles[$candidateProfile])) {
            return $this->blocked('WS_BT_C28_CANDIDATE_PROFILE_INVALID', 'candidate-profile-code must be a known C28 diagnostic profile.', $outputPath, $options, [
                'candidate_profile_code' => $candidateProfile,
            ]);
        }

        $c27 = $this->readJson($c27InputPath);
        if ($c27 === null) {
            return $this->blocked('WS_BT_C28_C27_ARTIFACT_UNREADABLE', 'C28 requires a readable C27 raw OHLC validation artifact.', $outputPath, $options, [
                'c27_all_param_artifact_path' => $c27InputPath,
            ]);
        }
        if (($c27['artifact_type'] ?? null) !== 'C27_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION' || ($c27['status'] ?? null) !== 'PASS') {
            return $this->blocked('WS_BT_C28_C27_ARTIFACT_INVALID', 'C28 requires a PASS C27 raw OHLC validation artifact.', $outputPath, $options, [
                'c27_artifact_type' => $c27['artifact_type'] ?? null,
                'c27_status' => $c27['status'] ?? null,
            ]);
        }
        if (($c27['raw_ohlc_validation_summary']['raw_ohlc_validation_pass'] ?? false) !== true) {
            return $this->blocked('WS_BT_C28_C27_RAW_OHLC_NOT_VALIDATED', 'C28 requires C27 raw OHLC validation to pass before rule revision diagnostics.', $outputPath, $options);
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C28_PARAM_IDS_INVALID', 'param-ids must be a comma-separated list of positive integers.', $outputPath, $options);
        }
        $profileCodes = $this->profileCodes($options['diagnostic_profile_codes'] ?? ($options['profile_codes'] ?? null));
        if ($profileCodes === false) {
            return $this->blocked('WS_BT_C28_PROFILE_INVALID', 'diagnostic-profile-codes/profile-codes must contain known C28 diagnostic profile codes only.', $outputPath, $options);
        }
        $profileScope = 'EXPLICIT';
        if ($profileCodes === []) {
            $profileCodes = array_keys($profiles);
            $profileScope = 'ALL_DEFAULT';
        }
        $maxProfiles = $this->positiveIntOrNull($options['max_diagnostic_profiles'] ?? ($options['max_profiles'] ?? null));
        if ($maxProfiles !== null) {
            $profileCodes = array_slice($profileCodes, 0, $maxProfiles);
            $profileScope .= '_MAX_'.$maxProfiles;
        }

        $rawRows = $this->rawRows($c27, $paramIds);
        $maxParams = $this->positiveIntOrNull($options['max_params'] ?? null);
        if ($maxParams !== null && $paramIds === []) {
            $allowed = $this->firstParamIds($rawRows, $maxParams);
            $rawRows = array_values(array_filter($rawRows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            $profileScope .= '_MAX_PARAMS_'.$maxParams;
        }
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        if ($maxPicks !== null) {
            $rawRows = array_slice($rawRows, 0, $maxPicks);
            $profileScope .= '_MAX_PICKS_'.$maxPicks;
        }
        if ($rawRows === []) {
            return $this->blocked('WS_BT_C28_RAW_ROWS_EMPTY', 'C28 requires raw validated C27 rows after filtering.', $outputPath, $options);
        }

        $pickRows = [];
        $total = count($rawRows);
        foreach ($rawRows as $index => $rawRow) {
            if (! empty($options['progress_callback']) && is_callable($options['progress_callback']) && ($index % 250 === 0)) {
                ($options['progress_callback'])('[C28] pick '.($index + 1).'/'.$total.' profiles='.count($profileCodes));
            }
            foreach ($profileCodes as $profileCode) {
                $pickRows[] = $this->profileRow($profileCode, $rawRow);
            }
        }

        $profileSummary = $this->profileSummary($pickRows);
        $candidateRows = array_values(array_filter($pickRows, function (array $row) use ($candidateProfile): bool {
            return ($row['profile_code'] ?? null) === $candidateProfile;
        }));
        $dataQuality = $this->dataQualitySummary($c27, $rawRows);
        $paramConsistency = $this->groupStabilitySummary($candidateRows, 'param_id', 'param_consistency');
        $monthStability = $this->groupStabilitySummary($candidateRows, 'trade_month', 'month_stability');
        $bucketStability = $this->groupStabilitySummary($candidateRows, 'bucket_code', 'bucket_stability');
        $lookaheadSafety = $this->lookaheadSafetySummary($candidateRows);
        $candidateSummary = $this->candidateSummary($candidateProfile, $profileSummary);
        $readiness = $this->readinessSummary($candidateSummary, $dataQuality, $paramConsistency, $monthStability, $bucketStability, $lookaheadSafety);
        $decision = $this->decision($readiness);

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'mutated' => false,
            ],
            'source_evidence' => $this->sourceEvidence($c27, $c27InputPath),
            'is_window' => ['from' => $fromDate, 'to' => $toDate],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'diagnostic_profiles' => $this->profileDefinitions($profileCodes),
            'profile_scope' => $profileScope,
            'candidate_profile_code' => $candidateProfile,
            'evaluated_picks_count' => count($rawRows),
            'pick_diagnostic_rows' => $pickRows,
            'profile_summary' => $profileSummary,
            'candidate_summary' => $candidateSummary,
            'data_quality_summary' => $dataQuality,
            'param_consistency_summary' => $paramConsistency,
            'month_stability_summary' => $monthStability,
            'bucket_stability_summary' => $bucketStability,
            'lookahead_safety_summary' => $lookaheadSafety,
            'candidate_readiness_summary' => $readiness,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(
                $this->routeUsesFuturePath((string) ($profiles[$candidateProfile]['source'] ?? ''))
            ),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message'], '', $options);
        }

        $r09 = $profileSummary['C28_G00_RAW_R09_BASELINE'] ?? [];
        $g21 = $profileSummary['C28_G01_RAW_G21_ORIGINAL'] ?? [];
        $candidate = $profileSummary[$candidateProfile] ?? [];

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'diagnostic_profile_count' => count($profileCodes),
            'profile_scope' => $profileScope,
            'candidate_profile_code' => $candidateProfile,
            'evaluated_picks_count' => count($rawRows),
            'c27_input_artifact_hash' => $c27['artifact_hash'] ?? null,
            'raw_ohlc_validation_pass' => $dataQuality['raw_ohlc_validation_pass'] ? 1 : 0,
            'raw_r09_avg_ret_net' => $r09['avg_ret_net'] ?? null,
            'raw_r09_median_ret_net' => $r09['median_ret_net'] ?? null,
            'raw_r09_p25_ret_net' => $r09['p25_ret_net'] ?? null,
            'raw_g21_avg_ret_net' => $g21['avg_ret_net'] ?? null,
            'raw_g21_median_ret_net' => $g21['median_ret_net'] ?? null,
            'raw_g21_p25_ret_net' => $g21['p25_ret_net'] ?? null,
            'candidate_avg_ret_net' => $candidate['avg_ret_net'] ?? null,
            'candidate_median_ret_net' => $candidate['median_ret_net'] ?? null,
            'candidate_p25_ret_net' => $candidate['p25_ret_net'] ?? null,
            'candidate_win_rate' => $candidate['win_rate'] ?? null,
            'candidate_avg_delta_vs_r09' => $candidateSummary['candidate_avg_delta_vs_r09'] ?? null,
            'candidate_median_delta_vs_r09' => $candidateSummary['candidate_median_delta_vs_r09'] ?? null,
            'candidate_p25_delta_vs_r09' => $candidateSummary['candidate_p25_delta_vs_r09'] ?? null,
            'candidate_param_pass_count' => $paramConsistency['param_consistency_pass_count'] ?? 0,
            'candidate_param_fail_count' => $paramConsistency['param_consistency_fail_count'] ?? 0,
            'candidate_month_pass_count' => $monthStability['month_stability_pass_count'] ?? 0,
            'candidate_month_fail_count' => $monthStability['month_stability_fail_count'] ?? 0,
            'candidate_bucket_pass_count' => $bucketStability['bucket_stability_pass_count'] ?? 0,
            'candidate_bucket_fail_count' => $bucketStability['bucket_stability_fail_count'] ?? 0,
            'lookahead_violation_count' => $lookaheadSafety['lookahead_violation_count'] ?? 0,
            'future_derived_route_count' => $lookaheadSafety['future_derived_route_count'] ?? 0,
            'execution_time_route_availability_pass' => ($readiness['execution_time_route_availability_pass'] ?? false) ? 1 : 0,
            'candidate_failure_reason_codes' => implode(',', $readiness['failure_reason_codes'] ?? []),
            'c28_revised_candidate_ready' => $readiness['c28_revised_candidate_ready'] ? 1 : 0,
            'c29_oos_proof_recommended' => $readiness['c29_oos_proof_recommended'] ? 1 : 0,
            'c28_catalog_implementation_deferred' => 1,
            'c28_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function profileRow(string $profileCode, array $raw): array
    {
        $profile = self::diagnosticProfiles()[$profileCode] ?? self::diagnosticProfiles()[self::PRIMARY_PROFILE];
        $profileSource = (string) ($profile['source'] ?? 'r09');
        $selection = $this->selectExit($profileSource, $raw);
        $exit = $selection['exit'];
        $futureDerivedRoute = $this->routeUsesFuturePath($profileSource);
        $exitLookaheadSafe = (bool) ($exit['lookahead_safe'] ?? false);
        $ret = $this->num($exit['ret_net'] ?? null);
        $r09Ret = $this->num($raw['r09']['ret_net'] ?? null);
        $g21Ret = $this->num($raw['g21']['ret_net'] ?? null);
        $g13Ret = $this->num($raw['g13']['ret_net'] ?? null);
        $g16Ret = $this->num($raw['g16']['ret_net'] ?? null);

        return [
            'trade_date' => $raw['trade_date'] ?? null,
            'trade_month' => $raw['trade_month'] ?? null,
            'ticker_id' => $raw['ticker_id'] ?? null,
            'ticker' => $raw['ticker'] ?? null,
            'param_id' => $raw['param_id'] ?? null,
            'row_code' => $raw['row_code'] ?? null,
            'entry_date' => $raw['entry_date'] ?? null,
            'entry_price' => $raw['raw_entry_price'] ?? null,
            'bucket_code' => $raw['bucket_code'] ?? null,
            'bucket_reason' => $raw['bucket_reason'] ?? null,
            'profile_code' => $profileCode,
            'profile_family' => $profile['family'] ?? null,
            'candidate_role' => $profile['candidate_role'] ?? null,
            'selected_source_code' => $selection['selected_source_code'],
            'selected_source_reason' => $selection['selected_source_reason'],
            'profile_exit_date' => $exit['exit_date'] ?? null,
            'profile_exit_price' => $exit['exit_price'] ?? null,
            'profile_exit_day_offset' => $exit['exit_day_offset'] ?? null,
            'profile_exit_reason' => $exit['exit_reason'] ?? null,
            'profile_ret_net' => $ret,
            'raw_r09_ret_net' => $r09Ret,
            'raw_g21_ret_net' => $g21Ret,
            'raw_g13_ret_net' => $g13Ret,
            'raw_g16_ret_net' => $g16Ret,
            'delta_vs_raw_r09' => $this->delta($ret, $r09Ret),
            'delta_vs_raw_g21' => $this->delta($ret, $g21Ret),
            'delta_vs_raw_g13' => $this->delta($ret, $g13Ret),
            'delta_vs_raw_g16' => $this->delta($ret, $g16Ret),
            'win_flag' => $ret !== null && $ret > 0,
            'loss_reduced_vs_r09_flag' => $r09Ret !== null && $r09Ret < 0 && $ret !== null && $ret > $r09Ret,
            'p25_protection_source' => $selection['selected_source_code'],
            'raw_ohlc_validated_flag' => (bool) ($raw['raw_ohlc_validated_flag'] ?? false),
            'lookahead_safe' => $exitLookaheadSafe && ! $futureDerivedRoute,
            'lookahead_violation_reason' => $futureDerivedRoute
                ? 'WS_BT_C28_FUTURE_DERIVED_BUCKET_ROUTE'
                : ($exit['lookahead_violation_reason'] ?? null),
            'route_decision_available_before_entry' => ! $futureDerivedRoute,
            'route_decision_available_at' => $futureDerivedRoute
                ? 'AFTER_D1_TO_D5_PATH_EVALUATION'
                : 'PREDECLARED_BEFORE_PATH_EVALUATION',
            'future_path_price_used_for_rule_routing' => $futureDerivedRoute,
            'missing_path_data_flag' => (bool) ($raw['missing_path_data_flag'] ?? false) || (bool) ($exit['missing_path_data_flag'] ?? false),
            'missing_path_reason_code' => $raw['missing_path_reason_code'] ?? ($exit['missing_path_reason_code'] ?? null),
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => $futureDerivedRoute,
            'profile_ret_net_used_for_selection' => false,
            'oos_executed' => false,
            'production_ready' => 0,
        ];
    }

    private function selectExit(string $source, array $raw): array
    {
        $bucket = (string) ($raw['bucket_code'] ?? '');
        if (in_array($source, ['r09', 'g21', 'g13', 'g16'], true)) {
            return [
                'selected_source_code' => strtoupper($source),
                'selected_source_reason' => 'direct_'.$source,
                'exit' => is_array($raw[$source] ?? null) ? $raw[$source] : $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
            ];
        }
        if ($source === 'g21_problem_buckets_else_r09') {
            $selected = in_array($bucket, ['no_rule_profit_signal_before_fallback', 'next_open_delay_after_close_signal'], true) ? 'g21' : 'r09';
            return [
                'selected_source_code' => strtoupper($selected),
                'selected_source_reason' => $selected === 'g21' ? 'g21_on_problem_bucket' : 'r09_on_stable_bucket',
                'exit' => $raw[$selected] ?? $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
            ];
        }
        if ($source === 'r09_stable_g21_no_signal_g16_delay') {
            if ($bucket === 'no_rule_profit_signal_before_fallback') {
                $selected = 'g21';
                $reason = 'g21_no_signal_d3_damage_control';
            } elseif ($bucket === 'next_open_delay_after_close_signal') {
                $selected = 'g16';
                $reason = 'g16_next_open_delay_target_component';
            } else {
                $selected = 'r09';
                $reason = 'r09_stable_candidate_bucket';
            }
            return [
                'selected_source_code' => strtoupper($selected),
                'selected_source_reason' => $reason,
                'exit' => $raw[$selected] ?? $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
            ];
        }
        if ($source === 'r09_stable_g13_no_signal_g16_delay') {
            if ($bucket === 'no_rule_profit_signal_before_fallback') {
                $selected = 'g13';
                $reason = 'g13_no_signal_defensive_target';
            } elseif ($bucket === 'next_open_delay_after_close_signal') {
                $selected = 'g16';
                $reason = 'g16_next_open_delay_target_component';
            } else {
                $selected = 'r09';
                $reason = 'r09_stable_candidate_bucket';
            }
            return [
                'selected_source_code' => strtoupper($selected),
                'selected_source_reason' => $reason,
                'exit' => $raw[$selected] ?? $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
            ];
        }
        if ($source === 'g13_global_g21_no_signal') {
            $selected = $bucket === 'no_rule_profit_signal_before_fallback' ? 'g21' : 'g13';
            return [
                'selected_source_code' => strtoupper($selected),
                'selected_source_reason' => $selected === 'g21' ? 'g21_no_signal_d3_damage_control' : 'g13_global_defensive_target',
                'exit' => $raw[$selected] ?? $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
            ];
        }
        if ($source === 'g16_global_g21_no_signal') {
            $selected = $bucket === 'no_rule_profit_signal_before_fallback' ? 'g21' : 'g16';
            return [
                'selected_source_code' => strtoupper($selected),
                'selected_source_reason' => $selected === 'g21' ? 'g21_no_signal_d3_damage_control' : 'g16_global_target',
                'exit' => $raw[$selected] ?? $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
            ];
        }

        return [
            'selected_source_code' => 'UNKNOWN',
            'selected_source_reason' => 'source_unknown',
            'exit' => $this->missingExit('WS_BT_C28_SOURCE_EXIT_MISSING'),
        ];
    }

    private function routeUsesFuturePath(string $source): bool
    {
        return in_array($source, [
            'g21_problem_buckets_else_r09',
            'r09_stable_g21_no_signal_g16_delay',
            'r09_stable_g13_no_signal_g16_delay',
            'g13_global_g21_no_signal',
            'g16_global_g21_no_signal',
        ], true);
    }

    private function rawRows(array $c27, array $paramIds): array
    {
        $allowed = $paramIds === [] ? [] : array_fill_keys($paramIds, true);
        $rows = [];
        foreach (($c27['raw_pick_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (($row['raw_ohlc_validated_flag'] ?? false) !== true || ($row['missing_path_data_flag'] ?? false) === true) {
                continue;
            }
            $paramId = (int) ($row['param_id'] ?? 0);
            if ($allowed !== [] && ! isset($allowed[$paramId])) {
                continue;
            }
            foreach (['r09', 'g21', 'g13', 'g16'] as $source) {
                if (! is_numeric($row[$source]['ret_net'] ?? null)) {
                    continue 2;
                }
            }
            $rows[] = $row;
        }
        usort($rows, function (array $left, array $right): int {
            foreach (['trade_date', 'param_id', 'ticker'] as $key) {
                $cmp = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });
        return $rows;
    }

    private function profileSummary(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[(string) ($row['profile_code'] ?? 'UNKNOWN')][] = $row;
        }
        ksort($groups, SORT_STRING);
        $out = [];
        foreach ($groups as $profileCode => $profileRows) {
            $returns = $this->values($profileRows, 'profile_ret_net');
            $wins = count(array_filter($returns, function (float $value): bool {
                return $value > 0;
            }));
            $lookahead = count(array_filter($profileRows, function (array $row): bool {
                return ($row['lookahead_safe'] ?? true) !== true;
            }));
            $missing = count(array_filter($profileRows, function (array $row): bool {
                return ($row['missing_path_data_flag'] ?? false) === true;
            }));
            $profile = self::diagnosticProfiles()[$profileCode] ?? [];
            $out[$profileCode] = [
                'profile_code' => $profileCode,
                'profile_family' => $profile['family'] ?? null,
                'candidate_role' => $profile['candidate_role'] ?? null,
                'evaluated_picks_count' => count($profileRows),
                'valid_ret_count' => count($returns),
                'missing_path_count' => $missing,
                'avg_ret_net' => $this->avg($returns),
                'median_ret_net' => $this->median($returns),
                'p25_ret_net' => $this->percentile($returns, 0.25),
                'win_rate' => count($returns) > 0 ? $wins / count($returns) : null,
                'avg_delta_vs_raw_r09' => $this->avg($this->values($profileRows, 'delta_vs_raw_r09')),
                'median_delta_vs_raw_r09' => $this->median($this->values($profileRows, 'delta_vs_raw_r09')),
                'p25_delta_vs_raw_r09' => $this->percentile($this->values($profileRows, 'delta_vs_raw_r09'), 0.25),
                'avg_delta_vs_raw_g21' => $this->avg($this->values($profileRows, 'delta_vs_raw_g21')),
                'lookahead_violation_count' => $lookahead,
                'selected_source_distribution' => $this->distribution($profileRows, 'selected_source_code', 'NONE'),
            ];
        }
        return $out;
    }

    private function candidateSummary(string $candidateProfile, array $profiles): array
    {
        $candidate = $profiles[$candidateProfile] ?? [];
        $r09 = $profiles['C28_G00_RAW_R09_BASELINE'] ?? [];
        $g21 = $profiles['C28_G01_RAW_G21_ORIGINAL'] ?? [];
        $g13 = $profiles['C28_G02_RAW_G13_GLOBAL'] ?? [];
        $g16 = $profiles['C28_G03_RAW_G16_GLOBAL'] ?? [];

        return [
            'candidate_profile_code' => $candidateProfile,
            'candidate_summary' => $candidate,
            'raw_r09_summary' => $r09,
            'raw_g21_original_summary' => $g21,
            'raw_g13_summary' => $g13,
            'raw_g16_summary' => $g16,
            'best_profile_code_by_avg' => $this->bestByMetric($profiles, 'avg_ret_net'),
            'best_profile_code_by_median' => $this->bestByMetric($profiles, 'median_ret_net'),
            'best_profile_code_by_p25' => $this->bestByMetric($profiles, 'p25_ret_net'),
            'candidate_avg_delta_vs_r09' => $this->delta($candidate['avg_ret_net'] ?? null, $r09['avg_ret_net'] ?? null),
            'candidate_median_delta_vs_r09' => $this->delta($candidate['median_ret_net'] ?? null, $r09['median_ret_net'] ?? null),
            'candidate_p25_delta_vs_r09' => $this->delta($candidate['p25_ret_net'] ?? null, $r09['p25_ret_net'] ?? null),
            'candidate_avg_delta_vs_g21_original' => $this->delta($candidate['avg_ret_net'] ?? null, $g21['avg_ret_net'] ?? null),
            'candidate_p25_delta_vs_g21_original' => $this->delta($candidate['p25_ret_net'] ?? null, $g21['p25_ret_net'] ?? null),
            'candidate_rule_is_explicit_bucket_tiebreak' => $candidateProfile === self::PRIMARY_PROFILE,
            'best_profile_binding_allowed' => false,
        ];
    }

    private function dataQualitySummary(array $c27, array $rawRows): array
    {
        return [
            'c27_artifact_available' => true,
            'c27_raw_ohlc_validation_pass' => (bool) ($c27['raw_ohlc_validation_summary']['raw_ohlc_validation_pass'] ?? false),
            'raw_ohlc_validation_pass' => (bool) ($c27['raw_ohlc_validation_summary']['raw_ohlc_validation_pass'] ?? false) && $rawRows !== [],
            'evaluated_picks_count' => count($rawRows),
            'raw_ohlc_validated_count' => count($rawRows),
            'raw_ohlc_missing_count' => 0,
            'derived_mfe_mae_used_for_execution' => false,
            'raw_high_low_used_for_execution' => true,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
        ];
    }

    private function groupStabilitySummary(array $rows, string $key, string $label): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groupKey = (string) ($row[$key] ?? 'UNKNOWN');
            $groups[$groupKey][] = $row;
        }
        ksort($groups, SORT_STRING);

        $items = [];
        $pass = 0;
        $fail = 0;
        foreach ($groups as $groupKey => $groupRows) {
            $candidate = $this->values($groupRows, 'profile_ret_net');
            $r09 = $this->values($groupRows, 'raw_r09_ret_net');
            $avgDelta = $this->delta($this->avg($candidate), $this->avg($r09));
            $p25Delta = $this->delta($this->percentile($candidate, 0.25), $this->percentile($r09, 0.25));
            $ok = $avgDelta !== null && $p25Delta !== null && $avgDelta >= -0.000001 && $p25Delta >= -0.000001;
            $ok ? $pass++ : $fail++;
            $items[] = [
                $label.'_key' => $groupKey,
                'count' => count($groupRows),
                'candidate_avg_ret_net' => $this->avg($candidate),
                'raw_r09_avg_ret_net' => $this->avg($r09),
                'candidate_p25_ret_net' => $this->percentile($candidate, 0.25),
                'raw_r09_p25_ret_net' => $this->percentile($r09, 0.25),
                'avg_delta_vs_raw_r09' => $avgDelta,
                'p25_delta_vs_raw_r09' => $p25Delta,
                'pass_flag' => $ok,
            ];
        }

        return [
            $label.'_pass_count' => $pass,
            $label.'_fail_count' => $fail,
            $label.'_rows' => $items,
        ];
    }

    private function lookaheadSafetySummary(array $rows): array
    {
        $violations = 0;
        $missing = 0;
        foreach ($rows as $row) {
            if (($row['lookahead_safe'] ?? true) !== true) {
                $violations++;
            }
            if (($row['missing_path_data_flag'] ?? false) === true) {
                $missing++;
            }
        }

        $futureDerivedRoutes = count(array_filter($rows, function (array $row): bool {
            return ($row['future_path_price_used_for_rule_routing'] ?? false) === true;
        }));

        return [
            'lookahead_violation_count' => $violations,
            'missing_path_count' => $missing,
            'future_derived_route_count' => $futureDerivedRoutes,
            'execution_time_route_availability_pass' => $futureDerivedRoutes === 0,
            'lookahead_safe' => $violations === 0 && $futureDerivedRoutes === 0,
            'preplanned_order_threshold_fixed_before_path_evaluation' => true,
            'close_signal_same_day_exit_allowed' => false,
            'close_signal_next_open_rule_checks' => [
                'd1_close_signal_min_exit_day' => 2,
                'd2_close_signal_min_exit_day' => 3,
                'd3_close_signal_min_exit_day' => 4,
            ],
            'future_path_price_used_for_selection' => $futureDerivedRoutes > 0,
            'future_path_price_used_for_rule_routing' => $futureDerivedRoutes > 0,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function readinessSummary(array $candidate, array $quality, array $param, array $month, array $bucket, array $lookahead): array
    {
        $avgDelta = $this->num($candidate['candidate_avg_delta_vs_r09'] ?? null);
        $medianDelta = $this->num($candidate['candidate_median_delta_vs_r09'] ?? null);
        $p25Delta = $this->num($candidate['candidate_p25_delta_vs_r09'] ?? null);
        $qualityOk = (bool) ($quality['raw_ohlc_validation_pass'] ?? false)
            && (bool) ($quality['raw_high_low_used_for_execution'] ?? false)
            && ! (bool) ($quality['derived_mfe_mae_used_for_execution'] ?? true);
        $distributionOk = $avgDelta !== null && $medianDelta !== null && $p25Delta !== null
            && $avgDelta >= 0.0
            && $medianDelta >= 0.0
            && $p25Delta >= 0.0;
        $paramOk = (int) ($param['param_consistency_pass_count'] ?? 0) > 0
            && (int) ($param['param_consistency_fail_count'] ?? 0) === 0;
        $monthOk = (int) ($month['month_stability_pass_count'] ?? 0) > 0
            && (int) ($month['month_stability_fail_count'] ?? 0) === 0;
        $bucketOk = (int) ($bucket['bucket_stability_pass_count'] ?? 0) > 0
            && (int) ($bucket['bucket_stability_fail_count'] ?? 0) === 0;
        $routeAvailabilityOk = (bool) ($lookahead['execution_time_route_availability_pass'] ?? false);
        $lookaheadOk = (int) ($lookahead['lookahead_violation_count'] ?? 0) === 0
            && (int) ($lookahead['missing_path_count'] ?? 0) === 0
            && $routeAvailabilityOk;
        $explicitRule = (bool) ($candidate['candidate_rule_is_explicit_bucket_tiebreak'] ?? false);
        $ready = $qualityOk && $distributionOk && $paramOk && $monthOk && $bucketOk && $lookaheadOk && $explicitRule;

        $failures = [];
        if (! $qualityOk) {
            $failures[] = 'RAW_OHLC_QUALITY_NOT_READY';
        }
        if (! $distributionOk) {
            $failures[] = 'CANDIDATE_DISTRIBUTION_NOT_BETTER_THAN_R09';
        }
        if (! $paramOk) {
            $failures[] = 'PARAM_STABILITY_WEAK';
        }
        if (! $monthOk) {
            $failures[] = 'MONTH_STABILITY_WEAK';
        }
        if (! $bucketOk) {
            $failures[] = 'BUCKET_STABILITY_WEAK';
        }
        if (! $lookaheadOk) {
            $failures[] = 'LOOKAHEAD_OR_PATH_SAFETY_WEAK';
        }
        if (! $routeAvailabilityOk) {
            $failures[] = 'FUTURE_DERIVED_BUCKET_ROUTE_NOT_EXECUTABLE';
        }
        if (! $explicitRule) {
            $failures[] = 'CANDIDATE_RULE_NOT_EXPLICIT_PRIMARY_TIEBREAK';
        }

        return [
            'raw_ohlc_validation_pass' => $qualityOk,
            'derived_mfe_mae_dependency_removed' => true,
            'candidate_distribution_beats_r09' => $distributionOk,
            'candidate_param_stability_pass' => $paramOk,
            'candidate_month_stability_pass' => $monthOk,
            'candidate_bucket_stability_pass' => $bucketOk,
            'lookahead_safety_pass' => $lookaheadOk,
            'execution_time_route_availability_pass' => $routeAvailabilityOk,
            'explicit_bucket_tiebreak_rule' => $explicitRule,
            'c28_revised_candidate_ready' => $ready,
            'failure_reason_codes' => $failures,
            'c29_oos_proof_recommended' => $ready,
            'c29_requires_c28_artifact_hash_lock' => $ready,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'production_ready' => 0,
        ];
    }

    private function decision(array $readiness): array
    {
        $ready = (bool) ($readiness['c28_revised_candidate_ready'] ?? false);
        return [
            'decision_status' => $ready
                ? 'C28_REVISED_RAW_CANDIDATE_READY_FOR_C29_OOS_PROOF'
                : 'C28_REVISED_RAW_CANDIDATE_NOT_READY',
            'c28_revised_candidate_ready' => $ready,
            'c29_oos_proof_recommended' => (bool) ($readiness['c29_oos_proof_recommended'] ?? false),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'production_ready' => 0,
            'next_step' => $ready
                ? 'C29 may run OOS proof against the locked C28 IS artifact; C28 itself does not create a production catalog.'
                : 'Keep C28 as IS-only revision evidence and continue candidate revision before OOS.',
        ];
    }

    private function sourceEvidence(array $c27, string $c27Path): array
    {
        return [
            'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
            'c20_final_status' => 'C20_DATE_GATE_NOT_ENOUGH',
            'c21_final_status' => 'C21_EXECUTION_SIGNAL_FOUND',
            'c22_final_status' => 'C22_EXIT_CAPTURE_SIGNAL_FOUND',
            'c23_final_status' => 'C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND_BUT_SHADOW_GAP_NOT_ACCEPTABLE',
            'c24_final_status' => 'C24_GAP_BRIDGE_EXPLAINED',
            'c25_final_status' => 'C25_C26_CATALOG_CANDIDATE_DIAGNOSTIC_RECOMMENDED',
            'c26_final_status' => 'C26_RAW_OHLC_VALIDATION_REQUIRED',
            'c27_final_status' => 'C27_RAW_OHLC_VALIDATED_BUT_CANDIDATE_NOT_READY',
            'c27_all_param_artifact_path' => $c27Path,
            'c27_all_param_artifact_hash' => $c27['artifact_hash'] ?? null,
        ];
    }

    private function safetyBoundaries(bool $futureDerivedRoute = true): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C28_CATALOG_CODE' => 'NOT_CREATED',
            'C28_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_C01_TO_C27_MUTATION' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'NO_C22_REOPEN' => true,
            'NO_C23_REOPEN' => true,
            'NO_C24_REOPEN' => true,
            'NO_C25_REOPEN' => true,
            'NO_C26_REOPEN' => true,
            'NO_C27_REOPEN' => true,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'canonical_model_unchanged' => $this->canonicalEvaluationModel(),
            'raw_ohlc_used_for_execution' => true,
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => $futureDerivedRoute,
            'future_path_price_used_for_rule_routing' => $futureDerivedRoute,
            'execution_time_route_availability_pass' => ! $futureDerivedRoute,
            'profile_ret_net_used_for_selection' => false,
            'diagnostic_profiles_used_as_production_rule' => false,
            'best_profile_binding_allowed' => false,
        ];
    }

    private function canonicalEvaluationModel(): array
    {
        return [
            'ENTRY' => 'NEXT_OPEN',
            'EXIT' => 'STOP_TP_OR_TIME',
            'HOLD' => 5,
            'FEE' => 'IDR_FIXED',
            'SLIP' => 0,
            'GAP' => 'OPEN',
            'PX' => 'IDX_BANDS',
            'canonical_model_changed' => false,
            'rule_revision_diagnostic_only' => true,
        ];
    }

    private function missingExit(string $reasonCode): array
    {
        return [
            'exit_date' => null,
            'exit_price' => null,
            'exit_day_offset' => null,
            'exit_reason' => null,
            'ret_net' => null,
            'lookahead_safe' => false,
            'lookahead_violation_reason' => null,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => $reasonCode,
        ];
    }

    private function firstParamIds(array $rows, int $max): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['param_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = true;
            }
            if (count($ids) >= $max) {
                break;
            }
        }
        return $ids;
    }

    private function profileDefinitions(array $profileCodes): array
    {
        $profiles = self::diagnosticProfiles();
        $out = [];
        foreach ($profileCodes as $code) {
            $out[] = array_merge(['profile_code' => $code], $profiles[$code] ?? []);
        }
        return $out;
    }

    private function bestByMetric(array $profiles, string $metric): ?string
    {
        $bestCode = null;
        $bestValue = null;
        foreach ($profiles as $code => $profile) {
            if (! is_numeric($profile[$metric] ?? null)) {
                continue;
            }
            $value = (float) $profile[$metric];
            if ($bestValue === null || $value > $bestValue) {
                $bestCode = (string) $code;
                $bestValue = $value;
            }
        }
        return $bestCode;
    }

    private function distribution(array $rows, string $key, string $nullLabel): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            $label = $value === null || $value === '' ? $nullLabel : (string) $value;
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }
        ksort($counts, SORT_STRING);
        return $counts;
    }

    private function values(array $rows, string $key): array
    {
        $values = [];
        foreach ($rows as $row) {
            if (is_numeric($row[$key] ?? null)) {
                $values[] = (float) $row[$key];
            }
        }
        return $values;
    }

    private function avg(array $values): ?float
    {
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function median(array $values): ?float
    {
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);
        return $count % 2 === 1 ? (float) $values[$middle] : ((float) $values[$middle - 1] + (float) $values[$middle]) / 2.0;
    }

    private function percentile(array $values, float $percentile): ?float
    {
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        if ($count === 1) {
            return (float) $values[0];
        }
        $position = ($count - 1) * $percentile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $position - $lower;
        return ((float) $values[$lower] * (1.0 - $weight)) + ((float) $values[$upper] * $weight);
    }

    private function delta($left, $right): ?float
    {
        $left = $this->num($left);
        $right = $this->num($right);
        return $left !== null && $right !== null ? $left - $right : null;
    }

    private function num($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    private function profileCodes($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $known = self::diagnosticProfiles();
        $codes = [];
        foreach ($value as $item) {
            $code = strtoupper(trim((string) $item));
            if ($code === '' || ! isset($known[$code])) {
                return false;
            }
            $codes[] = $code;
        }
        return array_values(array_unique($codes));
    }

    private function paramIds($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $ids = [];
        foreach ($value as $item) {
            if (! is_numeric($item) || (int) $item <= 0) {
                return false;
            }
            $ids[] = (int) $item;
        }
        return array_values(array_unique($ids));
    }

    private function positiveIntOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || (int) $value <= 0) {
            return null;
        }
        return (int) $value;
    }

    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function writeArtifact(string $path, array $artifact): array
    {
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C28 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function blocked(string $reasonCode, string $message, string $outputPath = '', array $options = [], array $extra = []): array
    {
        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'scope' => 'IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'decision' => [
                'decision_status' => 'C28_DIAGNOSTIC_BLOCKED',
                'catalog_allowed' => false,
                'oos_allowed' => false,
                'production_ready' => 0,
            ],
            'safety_boundaries' => $this->safetyBoundaries(),
            'extra' => $extra,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact);
        }
        return [
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'c28_catalog_implementation_deferred' => 1,
            'c28_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }
}
