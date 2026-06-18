<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService
{
    public const ARTIFACT_TYPE = 'C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC';
    public const DEFAULT_INPUT_PATH = 'storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json';
    public const DEFAULT_CANDIDATE_PROFILE = 'C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0';

    public function execute(string $inputPath = '', string $outputPath = '', array $options = []): array
    {
        $inputPath = trim($inputPath) !== '' ? trim($inputPath) : self::DEFAULT_INPUT_PATH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        $candidateProfile = strtoupper(trim((string) ($options['candidate_profile_code'] ?? self::DEFAULT_CANDIDATE_PROFILE)));

        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $c23 = $this->readJson($inputPath);
        if ($c23 === null) {
            return $this->blocked('WS_BT_C24_C23_ARTIFACT_UNREADABLE', 'C24 requires a readable C23 all-param diagnostic artifact.');
        }
        if (($c23['artifact_type'] ?? null) !== 'C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC' || ($c23['status'] ?? null) !== 'PASS') {
            return $this->blocked('WS_BT_C24_C23_ARTIFACT_INVALID', 'C24 requires a PASS C23 first-profit-capture diagnostic artifact.');
        }

        $profileSummary = $this->profileSummary($c23, $candidateProfile);
        if ($profileSummary === []) {
            return $this->blocked('WS_BT_C24_CANDIDATE_PROFILE_MISSING', 'C24 candidate profile was not found in the C23 artifact.');
        }

        $rows = $this->candidateRows($c23, $candidateProfile);
        if ($rows === []) {
            return $this->blocked('WS_BT_C24_CANDIDATE_ROWS_EMPTY', 'C24 candidate profile has no evaluated pick-rule rows.');
        }

        $canonical = is_array($c23['canonical_summary'] ?? null) ? $c23['canonical_summary'] : [];
        $c22 = is_array($c23['c22_shadow_s06_summary'] ?? null) ? $c23['c22_shadow_s06_summary'] : [];
        $c23Decision = is_array($c23['decision'] ?? null) ? $c23['decision'] : [];

        $metricBridge = $this->metricBridgeSummary($canonical, $profileSummary, $c22);
        $rowGap = $this->rowGapSummary($rows);
        $gapComponents = $this->gapComponentSummary($rows);
        $segmentSummaries = [
            'by_param' => $this->segmentSummary($rows, 'param_id'),
            'by_month' => $this->monthSummary($rows),
            'by_rule_signal_day_offset' => $this->segmentSummary($rows, 'rule_signal_day_offset', 'NO_SIGNAL'),
            'by_c22_shadow_s06_exit_day_offset' => $this->segmentSummary($rows, 'c22_shadow_s06_exit_day_offset', 'NONE'),
            'by_rule_exit_reason' => $this->segmentSummary($rows, 'rule_exit_reason', 'NONE'),
            'by_gap_component' => $this->componentRowsSummary($rows),
        ];
        $decision = $this->decision($c23Decision, $metricBridge, $rowGap, $gapComponents);

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'source_evidence' => [
                'c23_artifact_path' => $inputPath,
                'c23_artifact_hash' => $c23['artifact_hash'] ?? null,
                'c23_decision_status' => $c23Decision['decision_status'] ?? null,
                'c23_non_lookahead_rule_candidate_found' => (bool) ($c23Decision['non_lookahead_rule_candidate_found'] ?? false),
                'c23_c22_shadow_gap_acceptable' => (bool) ($c23Decision['c22_shadow_gap_acceptable'] ?? false),
                'c22_shadow_s06_benchmark_source' => 'C23_RECOMPUTED_C22_S06_MEASUREMENT',
            ],
            'candidate_profile_code' => $candidateProfile,
            'candidate_profile_summary' => $profileSummary,
            'canonical_summary' => $canonical,
            'c22_shadow_s06_summary' => $c22,
            'metric_bridge_summary' => $metricBridge,
            'row_gap_summary' => $rowGap,
            'gap_component_summary' => $gapComponents,
            'segment_summaries' => $segmentSummaries,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'c23_artifact_hash' => $c23['artifact_hash'] ?? null,
            'candidate_profile_code' => $candidateProfile,
            'evaluated_picks_count' => count($rows),
            'candidate_avg_ret_net' => $profileSummary['avg_ret_net'] ?? null,
            'candidate_median_ret_net' => $profileSummary['median_ret_net'] ?? null,
            'candidate_p25_ret_net' => $profileSummary['p25_ret_net'] ?? null,
            'candidate_win_rate' => $profileSummary['win_rate'] ?? null,
            'c22_shadow_s06_avg_ret_net' => $c22['c22_shadow_s06_avg_ret_net'] ?? null,
            'c22_shadow_s06_median_ret_net' => $c22['c22_shadow_s06_median_ret_net'] ?? null,
            'c22_shadow_s06_p25_ret_net' => $c22['c22_shadow_s06_p25_ret_net'] ?? null,
            'c22_shadow_s06_win_rate' => $c22['c22_shadow_s06_win_rate'] ?? null,
            'avg_gap_vs_c22_s06' => $metricBridge['avg_gap_vs_c22_s06'] ?? null,
            'median_gap_vs_c22_s06' => $metricBridge['median_gap_vs_c22_s06'] ?? null,
            'p25_gap_vs_c22_s06' => $metricBridge['p25_gap_vs_c22_s06'] ?? null,
            'win_rate_gap_vs_c22_s06' => $metricBridge['win_rate_gap_vs_c22_s06'] ?? null,
            'avg_capture_ratio_vs_c22_s06' => $metricBridge['avg_capture_ratio_vs_c22_s06'] ?? null,
            'median_capture_ratio_vs_c22_s06' => $metricBridge['median_capture_ratio_vs_c22_s06'] ?? null,
            'p25_capture_ratio_vs_c22_s06' => $metricBridge['p25_capture_ratio_vs_c22_s06'] ?? null,
            'win_rate_capture_ratio_vs_c22_s06' => $metricBridge['win_rate_capture_ratio_vs_c22_s06'] ?? null,
            'rows_where_c22_beats_candidate_rate' => $rowGap['rows_where_c22_beats_candidate_rate'] ?? null,
            'dominant_gap_component' => $gapComponents['dominant_component'] ?? null,
            'c24_gap_bridge_explained' => $decision['c24_gap_bridge_explained'] ? 1 : 0,
            'c24_catalog_implementation_deferred' => 1,
            'c24_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function profileSummary(array $artifact, string $profileCode): array
    {
        foreach (($artifact['rule_profile_summary'] ?? []) as $summary) {
            if (is_array($summary) && ($summary['profile_code'] ?? null) === $profileCode) {
                return $summary;
            }
        }
        return [];
    }

    private function candidateRows(array $artifact, string $profileCode): array
    {
        $rows = [];
        foreach (($artifact['pick_rule_rows'] ?? []) as $row) {
            if (! is_array($row)
                || ($row['rule_profile_code'] ?? null) !== $profileCode
                || ($row['missing_path_data_flag'] ?? true) !== false
                || ! is_numeric($row['rule_ret_net'] ?? null)
                || ! is_numeric($row['c22_shadow_s06_ret_net'] ?? null)) {
                continue;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function metricBridgeSummary(array $canonical, array $candidate, array $c22): array
    {
        $canonicalAvg = $this->num($canonical['canonical_avg_ret_net'] ?? null);
        $canonicalMedian = $this->num($canonical['canonical_median_ret_net'] ?? null);
        $canonicalP25 = $this->num($canonical['canonical_p25_ret_net'] ?? null);
        $canonicalWin = $this->num($canonical['canonical_win_rate'] ?? null);
        $candidateAvg = $this->num($candidate['avg_ret_net'] ?? null);
        $candidateMedian = $this->num($candidate['median_ret_net'] ?? null);
        $candidateP25 = $this->num($candidate['p25_ret_net'] ?? null);
        $candidateWin = $this->num($candidate['win_rate'] ?? null);
        $c22Avg = $this->num($c22['c22_shadow_s06_avg_ret_net'] ?? null);
        $c22Median = $this->num($c22['c22_shadow_s06_median_ret_net'] ?? null);
        $c22P25 = $this->num($c22['c22_shadow_s06_p25_ret_net'] ?? null);
        $c22Win = $this->num($c22['c22_shadow_s06_win_rate'] ?? null);

        return [
            'avg_gap_vs_c22_s06' => $this->gap($candidateAvg, $c22Avg),
            'median_gap_vs_c22_s06' => $this->gap($candidateMedian, $c22Median),
            'p25_gap_vs_c22_s06' => $this->gap($candidateP25, $c22P25),
            'win_rate_gap_vs_c22_s06' => $this->gap($candidateWin, $c22Win),
            'avg_capture_ratio_vs_c22_s06' => $this->captureRatio($canonicalAvg, $candidateAvg, $c22Avg),
            'median_capture_ratio_vs_c22_s06' => $this->captureRatio($canonicalMedian, $candidateMedian, $c22Median),
            'p25_capture_ratio_vs_c22_s06' => $this->captureRatio($canonicalP25, $candidateP25, $c22P25),
            'win_rate_capture_ratio_vs_c22_s06' => $this->captureRatio($canonicalWin, $candidateWin, $c22Win),
            'candidate_closes_avg_gap' => $this->metricClosesGap($canonicalAvg, $candidateAvg, $c22Avg),
            'candidate_closes_median_gap' => $this->metricClosesGap($canonicalMedian, $candidateMedian, $c22Median),
            'candidate_closes_p25_gap' => $this->metricClosesGap($canonicalP25, $candidateP25, $c22P25),
            'candidate_closes_win_rate_gap' => $this->metricClosesGap($canonicalWin, $candidateWin, $c22Win),
        ];
    }

    private function rowGapSummary(array $rows): array
    {
        $c22Beats = 0;
        $candidateBeats = 0;
        $ties = 0;
        $gaps = [];
        foreach ($rows as $row) {
            $gap = (float) $row['c22_shadow_s06_ret_net'] - (float) $row['rule_ret_net'];
            $gaps[] = $gap;
            if ($gap > 0.0000001) {
                $c22Beats++;
            } elseif ($gap < -0.0000001) {
                $candidateBeats++;
            } else {
                $ties++;
            }
        }
        return [
            'evaluated_picks_count' => count($rows),
            'rows_where_c22_beats_candidate_count' => $c22Beats,
            'rows_where_candidate_beats_c22_count' => $candidateBeats,
            'rows_where_candidate_ties_c22_count' => $ties,
            'rows_where_c22_beats_candidate_rate' => count($rows) > 0 ? $c22Beats / count($rows) : null,
            'rows_where_candidate_beats_c22_rate' => count($rows) > 0 ? $candidateBeats / count($rows) : null,
            'avg_row_gap_c22_minus_candidate' => $this->avg($gaps),
            'median_row_gap_c22_minus_candidate' => $this->median($gaps),
            'p75_row_gap_c22_minus_candidate' => $this->percentile($gaps, 0.75),
        ];
    }

    private function gapComponentSummary(array $rows): array
    {
        $components = $this->componentRowsSummary($rows);
        $dominantGap = null;
        $nonGap = null;
        foreach ($components as $component) {
            if (($component['component'] ?? null) === 'candidate_matches_or_beats_c22') {
                $nonGap = $component;
                continue;
            }
            if ($dominantGap === null || (int) $component['count'] > (int) $dominantGap['count']) {
                $dominantGap = $component;
            }
        }
        return [
            'dominant_component' => $dominantGap['component'] ?? null,
            'non_gap_component' => $nonGap,
            'components' => $components,
        ];
    }

    private function componentRowsSummary(array $rows): array
    {
        $byComponent = [];
        foreach ($rows as $row) {
            $component = $this->gapComponent($row);
            $byComponent[$component][] = $row;
        }
        ksort($byComponent, SORT_STRING);
        $out = [];
        foreach ($byComponent as $component => $componentRows) {
            $out[] = $this->rowsSummary($componentRows, ['component' => $component]);
        }
        return $out;
    }

    private function gapComponent(array $row): string
    {
        $signalOffset = $row['rule_signal_day_offset'] ?? null;
        $ruleExitOffset = $row['rule_exit_day_offset'] ?? null;
        $c22ExitOffset = $row['c22_shadow_s06_exit_day_offset'] ?? null;
        $gap = $this->num($row['c22_shadow_s06_ret_net'] ?? null) - $this->num($row['rule_ret_net'] ?? null);
        if ($gap <= 0.0000001) {
            return 'candidate_matches_or_beats_c22';
        }
        if ($signalOffset === null) {
            return 'no_rule_profit_signal_before_fallback';
        }
        if (is_numeric($signalOffset) && is_numeric($c22ExitOffset) && (int) $signalOffset > (int) $c22ExitOffset) {
            return 'late_rule_signal_after_c22_s06';
        }
        if (is_numeric($signalOffset) && is_numeric($c22ExitOffset) && (int) $signalOffset === (int) $c22ExitOffset
            && is_numeric($ruleExitOffset) && (int) $ruleExitOffset > (int) $c22ExitOffset) {
            return 'next_open_delay_after_close_signal';
        }
        return 'other_c22_gap';
    }

    private function segmentSummary(array $rows, string $key, string $nullLabel = 'NONE'): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            $label = $value === null || $value === '' ? $nullLabel : (string) $value;
            $groups[$label][] = $row;
        }
        ksort($groups, SORT_STRING);
        $out = [];
        foreach ($groups as $label => $groupRows) {
            $out[] = $this->rowsSummary($groupRows, [$key => $label]);
        }
        return $out;
    }

    private function monthSummary(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $month = substr((string) ($row['trade_date'] ?? ''), 0, 7);
            $groups[$month !== '' ? $month : 'UNKNOWN'][] = $row;
        }
        ksort($groups, SORT_STRING);
        $out = [];
        foreach ($groups as $month => $groupRows) {
            $out[] = $this->rowsSummary($groupRows, ['month' => $month]);
        }
        return $out;
    }

    private function rowsSummary(array $rows, array $prefix): array
    {
        $ruleReturns = $this->values($rows, 'rule_ret_net');
        $c22Returns = $this->values($rows, 'c22_shadow_s06_ret_net');
        $canonicalReturns = $this->values($rows, 'canonical_ret_net');
        $gaps = [];
        $c22Beats = 0;
        foreach ($rows as $row) {
            $gap = (float) $row['c22_shadow_s06_ret_net'] - (float) $row['rule_ret_net'];
            $gaps[] = $gap;
            if ($gap > 0.0000001) {
                $c22Beats++;
            }
        }
        return array_merge($prefix, [
            'count' => count($rows),
            'candidate_avg_ret_net' => $this->avg($ruleReturns),
            'c22_shadow_s06_avg_ret_net' => $this->avg($c22Returns),
            'canonical_avg_ret_net' => $this->avg($canonicalReturns),
            'avg_gap_c22_minus_candidate' => $this->avg($gaps),
            'median_gap_c22_minus_candidate' => $this->median($gaps),
            'rows_where_c22_beats_candidate_rate' => count($rows) > 0 ? $c22Beats / count($rows) : null,
        ]);
    }

    private function decision(array $c23Decision, array $metricBridge, array $rowGap, array $gapComponents): array
    {
        $gapStillMaterial = (($metricBridge['median_gap_vs_c22_s06'] ?? 0) > 0.0025)
            || (($metricBridge['p25_gap_vs_c22_s06'] ?? 0) > 0.005)
            || (($metricBridge['win_rate_gap_vs_c22_s06'] ?? 0) > 0.05);
        return [
            'decision_status' => $gapStillMaterial ? 'C24_C22_SHADOW_GAP_STILL_MATERIAL' : 'C24_C22_SHADOW_GAP_MOSTLY_BRIDGED',
            'c24_gap_bridge_explained' => true,
            'c23_non_lookahead_rule_candidate_found' => (bool) ($c23Decision['non_lookahead_rule_candidate_found'] ?? false),
            'c22_shadow_gap_still_material' => $gapStillMaterial,
            'dominant_gap_component' => $gapComponents['dominant_component'] ?? null,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $gapStillMaterial
                ? 'Do not create catalog or run OOS; use C24 only to decide whether a later diagnostic should study next-open delay and no-signal fallback cases.'
                : 'Even if the gap is mostly bridged, C24 remains diagnostic-only; separate approval is required before any catalog path.',
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C24_CATALOG_CODE' => 'NOT_CREATED',
            'C24_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_C01_TO_C23_MUTATION' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'NO_C22_REOPEN' => true,
            'NO_C23_REOPEN' => true,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'reads_c23_artifact_only' => true,
            'future_path_price_used_for_selection' => false,
            'candidate_ret_used_for_selection' => false,
            'c22_shadow_s06_used_for_selection' => false,
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

    private function captureRatio(?float $canonical, ?float $candidate, ?float $c22): ?float
    {
        if ($canonical === null || $candidate === null || $c22 === null) {
            return null;
        }
        $denominator = $c22 - $canonical;
        if (abs($denominator) < 0.0000001) {
            return null;
        }
        return ($candidate - $canonical) / $denominator;
    }

    private function metricClosesGap(?float $canonical, ?float $candidate, ?float $c22): ?bool
    {
        $ratio = $this->captureRatio($canonical, $candidate, $c22);
        return $ratio === null ? null : $ratio >= 0.50;
    }

    private function gap(?float $candidate, ?float $c22): ?float
    {
        return $candidate === null || $c22 === null ? null : $c22 - $candidate;
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
        $values = array_values(array_filter($values, 'is_numeric'));
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function median(array $values): ?float
    {
        $values = array_values(array_filter($values, 'is_numeric'));
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);
        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }
        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    private function percentile(array $values, float $percentile): ?float
    {
        $values = array_values(array_filter($values, 'is_numeric'));
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $index = (count($values) - 1) * $percentile;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $index - $lower;
        return (float) $values[$lower] + (((float) $values[$upper] - (float) $values[$lower]) * $weight);
    }

    private function num($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_replace_recursive([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'c24_catalog_implementation_deferred' => 1,
            'c24_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
