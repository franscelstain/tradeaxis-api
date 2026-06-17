<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC19QualityRecoveryDiagnosticService
{
    public const ARTIFACT_TYPE = 'C19_QUALITY_RECOVERY_TUNING_DIAGNOSTIC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-run-1.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE;
    public const CANONICAL_SAMPLE_TARGET = WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::CANONICAL_SAMPLE_TARGET;
    public const FAST_DEFAULT_PROFILES = [
        WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::DEFAULT_QUALITY_PROFILE,
        'Q05_DOWNSIDE_AWARE_SCORE_120',
    ];

    private WatchlistBacktestC19ProposedSelectionPriceDiagnosticService $priceDiagnostic;

    public function __construct(WatchlistBacktestC19ProposedSelectionPriceDiagnosticService $priceDiagnostic = null)
    {
        $this->priceDiagnostic = $priceDiagnostic ?: new WatchlistBacktestC19ProposedSelectionPriceDiagnosticService();
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $profiles = $this->profileCodes($options['profiles'] ?? null);
        if ($profiles === false) {
            return $this->blocked('WS_BT_C19_QUALITY_PROFILE_INVALID', 'profiles must be a comma-separated list of known C19 quality profile codes.');
        }
        $allProfileCodes = array_keys(WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::qualityProfiles());
        $profile_scope = 'EXPLICIT';
        if ($profiles === []) {
            if (! empty($options['all_profiles'])) {
                $profiles = $allProfileCodes;
                $profile_scope = 'ALL_PROFILES_EXPLICIT';
            } else {
                $profiles = self::FAST_DEFAULT_PROFILES;
                $profile_scope = 'FAST_DEFAULT';
            }
        }
        $maxProfiles = $this->positiveIntOrNull($options['max_profiles'] ?? null);
        if ($maxProfiles !== null) {
            $profiles = array_slice($profiles, 0, $maxProfiles);
            $profile_scope .= '_MAX_'.$maxProfiles;
        }
        if ($profiles === []) {
            return $this->blocked('WS_BT_C19_QUALITY_PROFILE_EMPTY', 'No C19 quality profile is selected for execution.');
        }

        $maxParams = $this->positiveIntOrNull($options['max_params'] ?? null);
        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $sharedSelectionPath = $this->sharedSelectionOutputPath($outputPath);
        $profileRuns = [];
        $progress = $options['progress_callback'] ?? null;
        $profileIndex = 0;
        foreach ($profiles as $profile) {
            $profileIndex++;
            if (is_callable($progress)) {
                $progress('[C19 Tahap 5] profile '.$profileIndex.'/'.count($profiles).': '.$profile);
            }
            $profilePath = $this->profileOutputPath($outputPath, $profile);
            $result = $this->priceDiagnostic->execute($catalogCode, $fromDate, $toDate, $profilePath, [
                'param_ids' => $options['param_ids'] ?? '',
                'max_params' => $maxParams,
                'overwrite' => true,
                'quality_profile' => $profile,
                'selection_output_path' => $sharedSelectionPath,
                'reuse_selection_artifact' => $profileIndex > 1,
                'executed_at' => $executedAt,
            ]);
            if (($result['status'] ?? null) !== 'PASS') {
                return $this->blocked($result['reason_code'] ?? 'WS_BT_C19_QUALITY_PRICE_PROFILE_BLOCKED', 'Underlying price diagnostic did not pass for profile '.$profile.'.', [
                    'profile_code' => $profile,
                    'profile_result' => $result,
                ]);
            }
            $artifact = $this->readJson($profilePath);
            if ($artifact === null) {
                return $this->blocked('WS_BT_C19_QUALITY_PROFILE_ARTIFACT_UNREADABLE', 'Unable to read profile artifact: '.$profilePath, [
                    'profile_code' => $profile,
                ]);
            }
            $profileRuns[] = $this->summarizeProfileArtifact($profile, $profilePath, $result, $artifact);
        }

        $baseline = $this->baselineSummary($profileRuns);
        $ranked = $this->rankProfileSummaries($profileRuns);
        $recommendedNext = $this->recommendedNextStep($ranked, $baseline);
        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_QUALITY_RECOVERY_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC',
            'generated_at' => $executedAt,
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'quality_profiles_tested' => $profiles,
            'profile_scope' => $profile_scope,
            'max_params' => $maxParams,
            'shared_selection_artifact_path' => $sharedSelectionPath,
            'baseline_profile_code' => WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::DEFAULT_QUALITY_PROFILE,
            'quality_gate_definition' => $this->qualityGateDefinition(),
            'profile_summaries' => $ranked,
            'best_profile_summary' => $ranked[0] ?? null,
            'baseline_summary' => $baseline,
            'recommended_next_step' => $recommendedNext,
            'evidence_for_next_step_only' => $recommendedNext['evidence_for_next_step_only'] ?? [],
            'c19_catalog_decision' => [
                'C19_CATALOG_IMPLEMENTATION_DEFERRED' => true,
                'C19_IMPLEMENTED_SOURCE_LEVEL' => false,
                'C19_CATALOG_CODE' => 'NOT_CREATED',
                'defer_reason' => 'Tahap 5 is quality recovery tuning diagnostic only. Catalog creation requires a quality-positive IS candidate and repeat proof; do not promote a merely sample-complete profile.',
            ],
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_QUALITY_RECOVERY_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'profile_count' => count($ranked),
            'profile_scope' => $profile_scope,
            'max_params' => $maxParams,
            'best_profile_code' => (string) ($ranked[0]['profile_code'] ?? ''),
            'best_avg_ret_net_top' => $ranked[0]['best_param']['avg_ret_net_top'] ?? null,
            'best_evaluated_picks_count' => $ranked[0]['best_param']['evaluated_picks_count'] ?? null,
            'profiles_with_sample_target_reached' => count(array_filter($ranked, function (array $summary): bool {
                return ($summary['best_param']['evaluated_sample_target_reached'] ?? false) === true;
            })),
            'profiles_with_quality_improvement' => count(array_filter($ranked, function (array $summary): bool {
                return ($summary['quality_improvement_vs_baseline']['avg_ret_net_improved'] ?? false) === true;
            })),
            'profiles_with_quality_target_reached' => count(array_filter($ranked, function (array $summary): bool {
                return ($summary['quality_gate']['quality_target_reached'] ?? false) === true;
            })),
            'c19_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function summarizeProfileArtifact(string $profile, string $path, array $result, array $artifact): array
    {
        $paramSummaries = [];
        foreach (($artifact['diagnostics'] ?? []) as $diag) {
            if (! is_array($diag)) {
                continue;
            }
            $paramSummaries[] = $this->summarizeParam($diag);
        }
        $paramSummaries = $this->rankParamSummaries($paramSummaries);
        return [
            'profile_code' => $profile,
            'profile_definition' => WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::qualityProfiles()[$profile] ?? [],
            'artifact_path' => $path,
            'artifact_hash' => $result['artifact_hash'] ?? ($artifact['artifact_hash'] ?? null),
            'param_count' => count($paramSummaries),
            'best_param' => $paramSummaries[0] ?? [],
            'param_summaries' => $paramSummaries,
            'price_diagnostic_summary' => [
                'max_proposed_recommended_count' => $result['max_proposed_recommended_count'] ?? null,
                'max_evaluated_picks_count' => $result['max_evaluated_picks_count'] ?? null,
                'max_price_missing_count' => $result['max_price_missing_count'] ?? null,
                'params_with_evaluated_sample_target_reached' => $result['params_with_evaluated_sample_target_reached'] ?? null,
            ],
        ];
    }

    private function summarizeParam(array $diag): array
    {
        $counts = is_array($diag['price_evaluation_counts'] ?? null) ? $diag['price_evaluation_counts'] : [];
        $selection = is_array($diag['selection_counts'] ?? null) ? $diag['selection_counts'] : [];
        $metrics = is_array($diag['return_metrics'] ?? null) ? $diag['return_metrics'] : [];
        $reason = is_array($diag['reason_code_distribution'] ?? null) ? $diag['reason_code_distribution'] : [];
        $stop = (int) ($reason['WATCHLIST_BACKTEST_EXIT_STOP'] ?? 0);
        $target = (int) ($reason['WATCHLIST_BACKTEST_EXIT_TARGET'] ?? 0);
        $evaluated = (int) ($counts['evaluated_picks_count'] ?? 0);
        $avg = $this->numericOrNull($metrics['avg_ret_net_top'] ?? null);
        $median = $this->numericOrNull($metrics['median_ret_net_top'] ?? null);
        $p25 = $this->numericOrNull($metrics['p25_ret_net_top'] ?? null);
        $win = $this->numericOrNull($metrics['win_rate_top'] ?? null);
        $periodFail = is_numeric($metrics['period_fail_count'] ?? null) ? (int) $metrics['period_fail_count'] : null;
        $sampleReached = $evaluated >= self::CANONICAL_SAMPLE_TARGET;
        return [
            'param_id' => (int) ($diag['param_id'] ?? 0),
            'row_code' => (string) ($diag['row_code'] ?? ''),
            'baseline_proposed_recommended_count' => (int) ($selection['baseline_proposed_recommended_count'] ?? $selection['proposed_recommended_count'] ?? 0),
            'proposed_recommended_count' => (int) ($selection['proposed_recommended_count'] ?? 0),
            'quality_profile_removed_count' => (int) ($selection['quality_profile_removed_count'] ?? 0),
            'requested_pairs_count' => (int) ($counts['requested_pairs_count'] ?? 0),
            'evaluated_picks_count' => $evaluated,
            'price_missing_count' => (int) ($counts['price_missing_count'] ?? 0),
            'avg_ret_net_top' => $avg,
            'median_ret_net_top' => $median,
            'p25_ret_net_top' => $p25,
            'win_rate_top' => $win,
            'month_win_rate_min' => $this->numericOrNull($metrics['month_win_rate_min'] ?? null),
            'month_avg_ret_net_min' => $this->numericOrNull($metrics['month_avg_ret_net_min'] ?? null),
            'period_fail_count' => $periodFail,
            'stop_count' => $stop,
            'target_count' => $target,
            'stop_to_target_ratio' => $target > 0 ? $stop / $target : null,
            'evaluated_sample_target_reached' => $sampleReached,
            'quality_target_reached' => $sampleReached && $avg !== null && $avg >= 0.0 && $median !== null && $median >= 0.0 && $win !== null && $win >= 0.45,
            'profile_removed_reason_counts' => $diag['quality_profile_diagnostic']['removed_reason_counts'] ?? [],
        ];
    }

    private function rankParamSummaries(array $summaries): array
    {
        usort($summaries, function (array $left, array $right): int {
            foreach ([
                ['evaluated_sample_target_reached', false],
                ['avg_ret_net_top', false],
                ['median_ret_net_top', false],
                ['win_rate_top', false],
                ['p25_ret_net_top', false],
                ['period_fail_count', true],
                ['evaluated_picks_count', false],
                ['param_id', true],
            ] as $sort) {
                [$key, $ascending] = $sort;
                $a = $left[$key] ?? ($ascending ? PHP_INT_MAX : -INF);
                $b = $right[$key] ?? ($ascending ? PHP_INT_MAX : -INF);
                if ($a == $b) {
                    continue;
                }
                $cmp = $a <=> $b;
                return $ascending ? $cmp : -$cmp;
            }
            return 0;
        });
        return array_values($summaries);
    }

    private function rankProfileSummaries(array $summaries): array
    {
        $baseline = $this->baselineSummary($summaries);
        foreach ($summaries as &$summary) {
            $best = $summary['best_param'] ?? [];
            $summary['quality_improvement_vs_baseline'] = $this->qualityImprovement($best, $baseline['best_param'] ?? []);
            $summary['quality_gate'] = [
                'sample_target_reached' => ($best['evaluated_sample_target_reached'] ?? false) === true,
                'quality_target_reached' => ($best['quality_target_reached'] ?? false) === true,
                'catalog_allowed' => false,
                'reason' => ($best['quality_target_reached'] ?? false) === true
                    ? 'Quality target reached for IS-only diagnostic; still requires repeat proof before catalog.'
                    : 'Quality target not reached; keep diagnostic only and do not create catalog.',
            ];
        }
        unset($summary);
        usort($summaries, function (array $left, array $right): int {
            $l = $left['best_param'] ?? [];
            $r = $right['best_param'] ?? [];
            foreach ([
                ['quality_target_reached', false],
                ['evaluated_sample_target_reached', false],
                ['avg_ret_net_top', false],
                ['median_ret_net_top', false],
                ['win_rate_top', false],
                ['p25_ret_net_top', false],
                ['period_fail_count', true],
                ['evaluated_picks_count', false],
            ] as $sort) {
                [$key, $ascending] = $sort;
                $a = $l[$key] ?? ($ascending ? PHP_INT_MAX : -INF);
                $b = $r[$key] ?? ($ascending ? PHP_INT_MAX : -INF);
                if ($a == $b) {
                    continue;
                }
                $cmp = $a <=> $b;
                return $ascending ? $cmp : -$cmp;
            }
            return strcmp((string) ($left['profile_code'] ?? ''), (string) ($right['profile_code'] ?? ''));
        });
        return array_values($summaries);
    }

    private function qualityImprovement(array $best, array $baseline): array
    {
        $avg = $this->numericOrNull($best['avg_ret_net_top'] ?? null);
        $baseAvg = $this->numericOrNull($baseline['avg_ret_net_top'] ?? null);
        $median = $this->numericOrNull($best['median_ret_net_top'] ?? null);
        $baseMedian = $this->numericOrNull($baseline['median_ret_net_top'] ?? null);
        $p25 = $this->numericOrNull($best['p25_ret_net_top'] ?? null);
        $baseP25 = $this->numericOrNull($baseline['p25_ret_net_top'] ?? null);
        return [
            'avg_ret_net_delta' => ($avg !== null && $baseAvg !== null) ? $avg - $baseAvg : null,
            'median_ret_net_delta' => ($median !== null && $baseMedian !== null) ? $median - $baseMedian : null,
            'p25_ret_net_delta' => ($p25 !== null && $baseP25 !== null) ? $p25 - $baseP25 : null,
            'avg_ret_net_improved' => ($avg !== null && $baseAvg !== null) ? $avg > $baseAvg : false,
            'sample_preserved' => ($best['evaluated_sample_target_reached'] ?? false) === true,
        ];
    }

    private function baselineSummary(array $summaries): array
    {
        foreach ($summaries as $summary) {
            if (($summary['profile_code'] ?? '') === WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::DEFAULT_QUALITY_PROFILE) {
                return $summary;
            }
        }
        return $summaries[0] ?? [];
    }

    private function recommendedNextStep(array $ranked, array $baseline): array
    {
        $best = $ranked[0] ?? [];
        $bestParam = $best['best_param'] ?? [];
        if (($best['quality_gate']['quality_target_reached'] ?? false) === true) {
            return [
                'decision' => 'CONTINUE_TO_REPEAT_IS_PROOF_WITH_BEST_PROFILE',
                'profile_code' => $best['profile_code'] ?? null,
                'param_id' => $bestParam['param_id'] ?? null,
                'reason' => 'A quality profile preserved sample and reached non-negative avg/median with acceptable win-rate in IS diagnostic only.',
                'evidence_for_next_step_only' => $best,
            ];
        }
        return [
            'decision' => 'DO_NOT_CREATE_CATALOG_CONTINUE_QUALITY_REDESIGN',
            'profile_code' => $best['profile_code'] ?? null,
            'param_id' => $bestParam['param_id'] ?? null,
            'reason' => 'No profile reached the quality target. Record only improvements that help the next redesign; do not promote a merely sample-complete profile.',
            'evidence_for_next_step_only' => $best,
        ];
    }

    private function qualityGateDefinition(): array
    {
        return [
            'sample_target' => self::CANONICAL_SAMPLE_TARGET,
            'avg_ret_net_top_min' => 0.0,
            'median_ret_net_top_min' => 0.0,
            'win_rate_top_min' => 0.45,
            'catalog_allowed_from_tahap_5' => false,
            'note' => 'Tahap 5 can only identify an IS diagnostic candidate or useful negative evidence. Catalog still requires separate repeat proof.',
        ];
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
        $allowed = WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::qualityProfiles();
        $out = [];
        foreach ($value as $profile) {
            $profile = strtoupper(trim((string) $profile));
            if ($profile === '' || ! isset($allowed[$profile])) {
                return false;
            }
            $out[] = $profile;
        }
        return array_values(array_unique($out));
    }

    private function profileOutputPath(string $outputPath, string $profile): string
    {
        $info = pathinfo($outputPath);
        $dir = $info['dirname'] ?? '.';
        $filename = $info['filename'] ?? 'c19-quality-recovery-tuning-diagnostic';
        return rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename.'.'.$profile.'.price-diagnostic.json';
    }

    private function sharedSelectionOutputPath(string $outputPath): string
    {
        $info = pathinfo($outputPath);
        $dir = $info['dirname'] ?? '.';
        $filename = $info['filename'] ?? 'c19-quality-recovery-tuning-diagnostic';
        return rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename.'.shared-selection-analysis.json';
    }

    private function safetyBoundaries(): array
    {
        return [
            'C19_QUALITY_RECOVERY_TUNING_DIAGNOSTIC' => true,
            'C19_PRICE_EVALUATION_DIAGNOSTIC_IMPLEMENTED' => true,
            'C19_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'C19_CATALOG_CODE' => 'NOT_CREATED',
            'C18_UNCHANGED' => true,
            'C01_TO_C18_IMMUTABLE' => true,
            'WATCHLIST_SCOPE_ONLY' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'no_best_of_failed_binding' => true,
            'no_ticker_blacklist' => true,
            'no_month_blacklist' => true,
            'no_sector_whitelist' => true,
            'quality_profiles_use_price_outcome_for_selection' => false,
            'fast_default_profiles' => self::FAST_DEFAULT_PROFILES,
            'all_profiles_requires_explicit_flag' => true,
            'catalog_allowed' => false,
        ];
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
        if ($dir !== '' && ! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write artifact file.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        $normalized = $payload;
        unset($normalized['artifact_hash'], $normalized['generated_at']);
        return sha1(json_encode($this->normalize($normalized), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        $keys = array_keys($value);
        if ($keys !== range(0, count($value) - 1)) {
            ksort($value);
        }
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
    }

    private function positiveIntOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }
        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function numericOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_replace_recursive([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'c19_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
