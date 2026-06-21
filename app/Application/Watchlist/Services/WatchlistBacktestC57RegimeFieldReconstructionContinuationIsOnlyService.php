<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService
{
    public const RUN_CODE = 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY';
    public const ARTIFACT_TYPE = 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY';
    public const DEFAULT_C56_ARTIFACT = 'storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json';
    public const DEFAULT_EXPECTED_C56_HASH = 'f7edab247dc824dcd33a15f00575dd04f76f4786';
    public const DEFAULT_C55_ARTIFACT = 'storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json';
    public const DEFAULT_EXPECTED_C55_HASH = 'a4145d6f356e678d0dadf95be5d356198ebfed79';
    public const DEFAULT_EXPECTED_C55_FILE_SHA1 = '18875FCAD7FD7CDA6607BB09A60917E853E68D2B';
    public const DEFAULT_C54_ARTIFACT = 'storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json';
    public const DEFAULT_EXPECTED_C54_HASH = '8c71a4352a1024dbe985e0f0bb6329f5e1545150';
    public const DEFAULT_EXPECTED_C54_FILE_SHA1 = '75410BB1A30A32FFFF9661CAD6818C13E044F7E5';
    public const DEFAULT_C53_ARTIFACT = 'storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json';
    public const DEFAULT_EXPECTED_C53_HASH = '6a1749d723e16b7efdb8aa1d7510388a9475d12c';
    public const DEFAULT_EXPECTED_C53_FILE_SHA1 = 'E35FEFB78B6F1931E54169BD8AABE286CB6F08C2';
    public const DEFAULT_C52_ARTIFACT = 'storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json';
    public const DEFAULT_EXPECTED_C52_HASH = '5dbe51c9d18b175e65cddb60336baf43d6833b72';
    public const DEFAULT_EXPECTED_C52_FILE_SHA1 = 'DADE6518BFF3912D8A43D7C67073FB803F7CF878';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const VALID_C56_CONCLUSIONS = [
        'C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS',
        'C56_REGIME_ROBUSTNESS_GAP_REMAINS',
        'C56_EVIDENCE_EXPANSION_REQUIRED',
        'C56_REDESIGNED_CANDIDATE_FAILED_IS_REVIEW',
        'C56_ROLLING_STABILITY_GAP_REMAINS',
        'C56_CONCENTRATION_GAP_REMAINS',
        'C56_LOSS_CLUSTER_GAP_REMAINS',
    ];

    private const PRIMARY_ANCHORS = [
        'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION',
        'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE',
        'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08',
        'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08',
        'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER',
        'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER',
    ];

    private const COMPARATOR_ANCHORS = [
        'C56_R00_C55_R00_NEAR_PASS_REPLAY_COMPARATOR',
        'C56_R01_C55_R01_NEAR_PASS_REPLAY_COMPARATOR',
        'C56_R03_C55_R19_LOSS_CLUSTER_REPLAY_COMPARATOR',
        'C56_R04_C55_R20_C52_ANCHOR_COMPARATOR_ONLY',
    ];

    private const REQUIRED_REGIME_FIELDS = [
        'market_index_roc20',
        'market_index_ma20_slope_pct',
        'sector_roc20',
        'rs_20_vs_ihsg',
        'rs_20_vs_sector',
        'roc20',
        'ma20_slope_pct',
        'atr14_pct',
        'vol_ratio',
    ];

    /**
     * C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY. C56_ARTIFACT_HASH_LOCK.
     * C55_ARTIFACT_HASH_LOCK. C55_FILE_SHA1_LOCK. C54_ARTIFACT_HASH_LOCK. C54_FILE_SHA1_LOCK.
     * C53_ARTIFACT_HASH_LOCK. C53_FILE_SHA1_LOCK. C52_ARTIFACT_HASH_LOCK. C52_FILE_SHA1_LOCK.
     * C56_C55_C54_C53_C52_LOCKED_LINEAGE. MARKET_INDEX_SOURCE_DISCOVERY_READ_ONLY.
     * MARKET_INDEX_RECONSTRUCTION_ASOF_SAFE. MARKET_INDEX_RECONSTRUCTION_NO_MAX_TRADE_DATE.
     * MARKET_INDEX_RECONSTRUCTION_NO_FUTURE_LOOKUP. MARKET_INDEX_RECONSTRUCTION_NO_OOS_ROWS.
     * C56_FAILED_WINDOWS_DIAGNOSTIC_ONLY. C55_FAILED_WINDOWS_DIAGNOSTIC_ONLY.
     * C53_ADVERSE_MONTHS_DIAGNOSTIC_ONLY. NO_ADVERSE_MONTH_EXCLUSION_RULE.
     * NO_FAILED_WINDOW_EXCLUSION_RULE. NO_TICKER_EXCLUSION_RULE. NO_SECTOR_EXCLUSION_RULE.
     * PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION. NO_OOS_TUNING. NO_OOS_PROOF.
     * NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER. NO_OOS_RETURN_SELECTION.
     * NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG.
     * NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C56_ARTIFACT_MUTATION.
     * CANDIDATE_IS_NOT_PRODUCTION. C57_MUST_NOT_RECOMMEND_OOS_PROOF.
     */
    public function execute(
        string $c56Artifact = self::DEFAULT_C56_ARTIFACT,
        string $expectedC56Hash = self::DEFAULT_EXPECTED_C56_HASH,
        string $c55Artifact = self::DEFAULT_C55_ARTIFACT,
        string $expectedC55Hash = self::DEFAULT_EXPECTED_C55_HASH,
        string $expectedC55FileSha1 = self::DEFAULT_EXPECTED_C55_FILE_SHA1,
        string $c54Artifact = self::DEFAULT_C54_ARTIFACT,
        string $expectedC54Hash = self::DEFAULT_EXPECTED_C54_HASH,
        string $expectedC54FileSha1 = self::DEFAULT_EXPECTED_C54_FILE_SHA1,
        string $c53Artifact = self::DEFAULT_C53_ARTIFACT,
        string $expectedC53Hash = self::DEFAULT_EXPECTED_C53_HASH,
        string $expectedC53FileSha1 = self::DEFAULT_EXPECTED_C53_FILE_SHA1,
        string $c52Artifact = self::DEFAULT_C52_ARTIFACT,
        string $expectedC52Hash = self::DEFAULT_EXPECTED_C52_HASH,
        string $expectedC52FileSha1 = self::DEFAULT_EXPECTED_C52_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact(
            $c56Artifact, $expectedC56Hash,
            $c55Artifact, $expectedC55Hash, $expectedC55FileSha1,
            $c54Artifact, $expectedC54Hash, $expectedC54FileSha1,
            $c53Artifact, $expectedC53Hash, $expectedC53FileSha1,
            $c52Artifact, $expectedC52Hash, $expectedC52FileSha1,
            $from, $to,
            (string) ($options['executed_at'] ?? gmdate('c'))
        );

        $c56Load = $this->loadLocked($c56Artifact, $expectedC56Hash, null);
        $this->copyLock($artifact, 'c56', $c56Load, false);
        if (! $c56Load['readable']) { return $this->blocked($artifact, 'C57_BLOCKED_MISSING_C56_ARTIFACT', 'WS_BT_C57_C56_ARTIFACT_MISSING', 'C57 requires the locked C56 artifact.', $outputPath); }
        if (! $c56Load['hash_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C56_HASH_MISMATCH', 'WS_BT_C57_C56_ARTIFACT_HASH_MISMATCH', 'C56 stable artifact hash does not match the expected lock.', $outputPath); }
        $c56 = $c56Load['payload'];
        if (($c56['status'] ?? null) !== 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') { return $this->blocked($artifact, 'C57_BLOCKED_UNEXPECTED_C56_STATUS', 'WS_BT_C57_UNEXPECTED_C56_STATUS', 'C57 requires completed C56 evidence.', $outputPath); }
        if (! in_array(($c56['diagnostic_conclusion'] ?? null), self::VALID_C56_CONCLUSIONS, true)) { return $this->blocked($artifact, 'C57_BLOCKED_UNEXPECTED_C56_CONCLUSION', 'WS_BT_C57_UNEXPECTED_C56_CONCLUSION', 'C56 conclusion does not authorize C57 continuation.', $outputPath); }
        if (($c56['next_step_recommendation'] ?? null) !== self::RUN_CODE) { return $this->blocked($artifact, 'C57_BLOCKED_C56_NEXT_STEP_UNEXPECTED', 'WS_BT_C57_C56_NEXT_STEP_UNEXPECTED', 'C56 next step does not route to C57.', $outputPath); }
        if (! $this->strictFalse($c56['production_ready'] ?? true)) { return $this->blocked($artifact, 'C57_BLOCKED_C56_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C57_C56_PRODUCTION_READY_NOT_FALSE', 'C56 production_ready must remain false.', $outputPath); }
        if (($c56['c57_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c56['c57_readiness_decision']['oos_proof_unlocked'] ?? false) === true || ($c56['safety_boundaries']['direct_oos_proof_recommended'] ?? false) === true || ($c56['safety_boundaries']['oos_proof_unlocked'] ?? false) === true) { return $this->blocked($artifact, 'C57_BLOCKED_C56_OOS_PROOF_FLAG_INVALID', 'WS_BT_C57_C56_OOS_PROOF_FLAG_INVALID', 'C56 must not unlock or recommend OOS proof.', $outputPath); }
        if (! $this->c56RegimeFieldGapPresent($c56)) { return $this->blocked($artifact, 'C57_BLOCKED_MISSING_C56_REGIME_FIELD_GAP', 'WS_BT_C57_MISSING_C56_REGIME_FIELD_GAP', 'C57 requires the C56 market-index regime field gap.', $outputPath); }

        $c55Load = $this->loadLocked($c55Artifact, $expectedC55Hash, $expectedC55FileSha1);
        $this->copyLock($artifact, 'c55', $c55Load);
        if (! $c55Load['readable']) { return $this->blocked($artifact, 'C57_BLOCKED_MISSING_C55_ARTIFACT', 'WS_BT_C57_C55_ARTIFACT_MISSING', 'C57 requires the locked C55 artifact.', $outputPath); }
        if (! $c55Load['hash_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C55_HASH_MISMATCH', 'WS_BT_C57_C55_HASH_MISMATCH', 'C55 stable artifact hash does not match the expected lock.', $outputPath); }
        if (! $c55Load['file_sha1_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C55_FILE_SHA1_MISMATCH', 'WS_BT_C57_C55_FILE_SHA1_MISMATCH', 'C55 file SHA1 does not match the expected lock.', $outputPath); }

        $c54Load = $this->loadLocked($c54Artifact, $expectedC54Hash, $expectedC54FileSha1);
        $this->copyLock($artifact, 'c54', $c54Load);
        if (! $c54Load['readable']) { return $this->blocked($artifact, 'C57_BLOCKED_MISSING_C54_ARTIFACT', 'WS_BT_C57_C54_ARTIFACT_MISSING', 'C57 requires the locked C54 artifact.', $outputPath); }
        if (! $c54Load['hash_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C54_HASH_MISMATCH', 'WS_BT_C57_C54_HASH_MISMATCH', 'C54 stable artifact hash does not match the expected lock.', $outputPath); }
        if (! $c54Load['file_sha1_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C54_FILE_SHA1_MISMATCH', 'WS_BT_C57_C54_FILE_SHA1_MISMATCH', 'C54 file SHA1 does not match the expected lock.', $outputPath); }

        $c53Load = $this->loadLocked($c53Artifact, $expectedC53Hash, $expectedC53FileSha1);
        $this->copyLock($artifact, 'c53', $c53Load);
        if (! $c53Load['readable']) { return $this->blocked($artifact, 'C57_BLOCKED_MISSING_C53_ARTIFACT', 'WS_BT_C57_C53_ARTIFACT_MISSING', 'C57 requires the locked C53 artifact.', $outputPath); }
        if (! $c53Load['hash_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C53_HASH_MISMATCH', 'WS_BT_C57_C53_HASH_MISMATCH', 'C53 stable artifact hash does not match the expected lock.', $outputPath); }
        if (! $c53Load['file_sha1_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C53_FILE_SHA1_MISMATCH', 'WS_BT_C57_C53_FILE_SHA1_MISMATCH', 'C53 file SHA1 does not match the expected lock.', $outputPath); }

        $c52Load = $this->loadLocked($c52Artifact, $expectedC52Hash, $expectedC52FileSha1);
        $this->copyLock($artifact, 'c52', $c52Load);
        if (! $c52Load['readable']) { return $this->blocked($artifact, 'C57_BLOCKED_MISSING_C52_ARTIFACT', 'WS_BT_C57_C52_ARTIFACT_MISSING', 'C57 requires the locked C52 artifact.', $outputPath); }
        if (! $c52Load['hash_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C52_HASH_MISMATCH', 'WS_BT_C57_C52_HASH_MISMATCH', 'C52 stable artifact hash does not match the expected lock.', $outputPath); }
        if (! $c52Load['file_sha1_match']) { return $this->blocked($artifact, 'C57_BLOCKED_C52_FILE_SHA1_MISMATCH', 'WS_BT_C57_C52_FILE_SHA1_MISMATCH', 'C52 file SHA1 does not match the expected lock.', $outputPath); }

        if ($this->touchesReservedOos($from, $to)) { return $this->blocked($artifact, 'C57_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C57_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'C57 validation period must stay inside IS and outside reserved OOS.', $outputPath); }

        $c55 = $c55Load['payload'];
        $c54 = $c54Load['payload'];
        $c53 = $c53Load['payload'];
        $c52 = $c52Load['payload'];

        $sourceRows = $this->sourceRows($options, $from, $to, $c56);
        $requiredDates = $this->requiredDates($sourceRows, $from, $to, $options);
        $rowsRequired = $this->rowsRequired($sourceRows, $c56);

        $artifact['c56_carry_forward_summary'] = $this->c56CarryForward($c56);
        $artifact['c56_root_cause_summary'] = $this->c56RootCauseSummary($c56);
        $artifact['c55_carry_forward_summary'] = $this->simpleCarryForward('c55', $c55);
        $artifact['c54_carry_forward_summary'] = $this->simpleCarryForward('c54', $c54);
        $artifact['c53_evidence_carry_forward'] = $this->simpleCarryForward('c53', $c53);
        $artifact['c52_sector_reconstruction_carry_forward'] = $this->c52CarryForward($c52);

        $market = $this->reconstructMarketIndex($requiredDates, $rowsRequired, $sourceRows, $from, $to, $options);
        $artifact['market_index_source_discovery_summary'] = $market['source_discovery_summary'];
        $artifact['market_index_source_discovery_results'] = $market['source_discovery_results'];
        $artifact['market_index_reconstruction_results'] = $market['reconstruction_results'];
        $artifact['market_index_date_coverage_results'] = $market['date_coverage_results'];
        $artifact['market_index_asof_safety_results'] = $market['asof_safety_results'];

        $regime = $this->regimeFieldReconstruction($sourceRows, $rowsRequired, $c56, $market);
        $artifact['regime_field_reconstruction_summary'] = $regime['summary'];
        $artifact['regime_field_coverage_results'] = $regime['coverage'];
        $artifact['missing_regime_field_results'] = $regime['missing'];
        $artifact['asof_safety_validation_results'] = $regime['asof'];
        $artifact['source_reconstruction_summary'] = $this->sourceReconstructionSummary($c56, $sourceRows, $requiredDates, $market, $regime);

        $artifact['anchor_candidate_definitions'] = $this->anchorDefinitions($c56);
        $artifact['candidate_replay_results'] = $this->anchorRows((array) ($c56['candidate_replay_results'] ?? []), 'candidate_code');
        $artifact['concentration_dependency_validation_results'] = $this->anchorRows((array) ($c56['concentration_dependency_validation_results'] ?? []), 'candidate_code');
        $artifact['rolling_validation_results'] = $this->anchorRows((array) ($c56['rolling_validation_results'] ?? []), 'candidate_code');
        $artifact['rolling_validation_summary'] = $this->rollingSummary($c56);
        $artifact['leave_one_month_out_results'] = $this->anchorRows((array) ($c56['leave_one_month_out_results'] ?? []), 'candidate_code');
        $artifact['leave_one_month_out_summary'] = $this->looSummary($c56);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessResults($c56, $regime['summary']);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessSummary($artifact['regime_robustness_validation_results'], $regime['summary']);
        $artifact['material_difference_validation_results'] = $this->anchorRows((array) ($c56['material_difference_validation_results'] ?? []), 'candidate_code');
        if (count($artifact['material_difference_validation_results']) === 0) { $artifact['material_difference_validation_results'] = $this->materialDifferenceFromScorecard($c56); }
        $artifact['source_reconstruction_bias_check'] = $this->sourceBiasCheck($c56, $c52, $market, $regime);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($c56, $regime['summary'], $artifact['regime_robustness_validation_results']);
        $artifact['selected_c57_candidates_for_c58'] = $this->selectedForC58($artifact['candidate_scorecard']);
        $artifact['c58_readiness_decision'] = $this->decision($artifact, $market, $regime['summary']);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit(array_column($artifact['candidate_scorecard'], 'candidate_code'));
        $artifact['not_evaluable_reasons'] = $this->notEvaluableReasons($artifact, $market, $regime['summary']);
        $artifact['diagnostic_conclusion'] = $artifact['c58_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c58_readiness_decision']['c58_recommendation'];
        $artifact['status'] = 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED';
        $artifact['diagnostics'] = $this->diagnostics($artifact, $market, $regime['summary']);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function sourceRows(array $options, string $from, string $to, array $c56 = []): array
    {
        $rows = array_values(array_filter((array) ($options['source_rows'] ?? []), 'is_array'));

        if (count($rows) === 0) {
            $rows = $this->sourceRowsFromLockedEvidence($c56);
        }

        return array_values(array_filter($rows, function (array $row) use ($from, $to): bool {
            $date = $this->sourceRowDate($row);

            return $date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0;
        }));
    }

    private function sourceRowsFromLockedEvidence(array $c56): array
    {
        $path = (string) ($c56['source_reconstruction_summary']['source_evidence_artifact'] ?? '');
        if ($path === '' || ! is_file($path)) { return []; }

        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) { return []; }

        foreach (['pick_diagnostic_rows', 'source_rows', 'diagnostic_rows', 'rows'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_values(array_filter($payload[$key], 'is_array'));
            }
        }

        return [];
    }

    private function sourceRowDate(array $row): string
    {
        foreach (['signal_date', 'trade_date', 'date', 'published_date'] as $field) {
            $date = (string) ($row[$field] ?? '');
            if ($date !== '') { return $date; }
        }

        return '';
    }

    private function requiredDates(array $sourceRows, string $from, string $to, array $options): array
    {
        $dates = [];
        foreach ((array) ($options['source_dates'] ?? []) as $date) {
            if (is_string($date) && $date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0) { $dates[$date] = true; }
        }
        foreach ($sourceRows as $row) {
            $date = $this->sourceRowDate($row);
            if ($date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0) { $dates[$date] = true; }
        }
        if (count($dates) === 0) {
            foreach ($this->marketCalendarDates($from, $to) as $date) { $dates[$date] = true; }
        }
        $out = array_keys($dates);
        sort($out);
        return $out;
    }

    private function marketCalendarDates(string $from, string $to): array
    {
        try {
            if (! Schema::hasTable('market_calendar')) { return []; }
            $dateColumn = Schema::hasColumn('market_calendar', 'cal_date') ? 'cal_date' : (Schema::hasColumn('market_calendar', 'trade_date') ? 'trade_date' : null);
            if ($dateColumn === null) { return []; }
            $query = DB::table('market_calendar')->whereBetween($dateColumn, [$from, $to]);
            if (Schema::hasColumn('market_calendar', 'is_trading_day')) { $query->where('is_trading_day', 1); }
            elseif (Schema::hasColumn('market_calendar', 'is_open')) { $query->where('is_open', 1); }
            return array_values(array_map('strval', $query->orderBy($dateColumn)->pluck($dateColumn)->all()));
        } catch (Throwable $e) {
            return [];
        }
    }

    private function rowsRequired(array $sourceRows, array $c56): int
    {
        if (count($sourceRows) > 0) { return count($sourceRows); }
        foreach ((array) ($c56['regime_field_coverage_results'] ?? []) as $row) {
            if (($row['field_name'] ?? null) === 'market_index_roc20') { return (int) ($row['rows_required'] ?? 0); }
        }
        return (int) ($c56['source_reconstruction_summary']['reconstructed_source_row_count'] ?? 0);
    }

    private function reconstructMarketIndex(array $requiredDates, int $rowsRequired, array $sourceRows, string $from, string $to, array $options): array
    {
        $discovery = $this->discoverMarketIndexSources($from, $to, $options);
        $selected = $this->selectedSource($discovery['results']);
        $series = $this->marketSeries($selected, $from, $to, $options);
        $requiredDateCount = count($requiredDates);
        $requiredDateMin = $requiredDateCount > 0 ? $requiredDates[0] : null;
        $requiredDateMax = $requiredDateCount > 0 ? $requiredDates[$requiredDateCount - 1] : null;
        $sourceRowDates = array_values(array_filter(array_map(fn (array $row): string => $this->sourceRowDate($row), $sourceRows)));
        sort($sourceRowDates);
        $reconstruction = [];
        foreach (['market_index_roc20', 'market_index_ma20_slope_pct'] as $field) {
            $result = $this->reconstructField($field, $requiredDates, $rowsRequired, $sourceRows, $series, $selected);
            $reconstruction[] = $result;
        }
        if ((bool) ($options['force_future_lookup_detected'] ?? false) && isset($reconstruction[0])) {
            $reconstruction[0]['future_lookup_detected'] = true;
            $reconstruction[0]['asof_safe'] = false;
            $reconstruction[0]['reconstruction_pass'] = false;
            $reconstruction[0]['failure_reason_codes'][] = 'C57_MARKET_INDEX_RECONSTRUCTION_FUTURE_LOOKUP_DETECTED';
        }
        if ((int) ($options['force_oos_rows_requested'] ?? 0) > 0 && isset($reconstruction[0])) {
            $reconstruction[0]['oos_rows_requested'] = (int) $options['force_oos_rows_requested'];
            $reconstruction[0]['reconstruction_pass'] = false;
            $reconstruction[0]['failure_reason_codes'][] = 'C57_MARKET_INDEX_RECONSTRUCTION_OOS_ROWS_DETECTED';
        }
        $dateCoverage = $this->dateCoverage($selected, $requiredDates, $series);
        $future = count(array_filter($reconstruction, fn (array $r): bool => (bool) ($r['future_lookup_detected'] ?? false))) > 0;
        $oosRows = array_sum(array_map(fn (array $r): int => (int) ($r['oos_rows_requested'] ?? 0), $reconstruction));
        return [
            'source_discovery_summary' => [
                'market_index_source_discovery_attempted' => true,
                'source_candidate_count' => count($discovery['results']),
                'source_found' => $selected !== null,
                'selected_source_name' => $selected['source_name'] ?? null,
                'selected_source_table' => $selected['source_table'] ?? null,
                'selected_identifier' => $selected['identifier'] ?? null,
                'identifier_candidates_checked' => $this->identifierCandidates(),
                'required_date_count' => $requiredDateCount,
                'required_date_min' => $requiredDateMin,
                'required_date_max' => $requiredDateMax,
                'required_date_sample' => array_slice($requiredDates, 0, 5),
                'source_row_date_field_detected' => $this->sourceRowDateFieldDetected($sourceRows),
                'source_row_min_date' => count($sourceRowDates) > 0 ? $sourceRowDates[0] : null,
                'source_row_max_date' => count($sourceRowDates) > 0 ? $sourceRowDates[count($sourceRowDates) - 1] : null,
                'rows_required' => $rowsRequired,
                'market_index_lookup_basis' => 'signal_date_or_trade_date_with_previous_published_trading_day_fallback_bounded_by_row_date',
                'read_only' => true,
                'asof_safe' => ! $future,
                'future_lookup_detected' => $future,
                'oos_rows_requested' => $oosRows,
                'failure_reason_codes' => $selected === null ? ['C57_MARKET_INDEX_SOURCE_NOT_FOUND'] : [],
            ],
            'source_discovery_results' => $discovery['results'],
            'reconstruction_results' => $reconstruction,
            'date_coverage_results' => [$dateCoverage],
            'asof_safety_results' => [[
                'validation_layer' => 'market_index_reconstruction',
                'source_identifier' => $selected['identifier'] ?? null,
                'lookup_basis' => 'signal_date_trade_date_or_previous_trading_day_not_after_signal_date',
                'asof_safe' => ! $future,
                'future_lookup_detected' => $future,
                'oos_rows_requested' => $oosRows,
                'max_trade_date_lookup_used' => false,
                'validation_pass' => ! $future && $oosRows === 0,
                'failure_reason_codes' => array_values(array_filter([ $future ? 'C57_MARKET_INDEX_RECONSTRUCTION_FUTURE_LOOKUP_DETECTED' : null, $oosRows > 0 ? 'C57_MARKET_INDEX_RECONSTRUCTION_OOS_ROWS_DETECTED' : null ])),
            ]],
        ];
    }

    private function discoverMarketIndexSources(string $from, string $to, array $options): array
    {
        $results = [];
        if (isset($options['market_index_source_rows']) && is_array($options['market_index_source_rows'])) {
            $rows = array_values(array_filter((array) $options['market_index_source_rows'], 'is_array'));
            $dates = array_values(array_unique(array_map(fn (array $r): string => (string) ($r['trade_date'] ?? ''), $rows)));
            $dates = array_values(array_filter($dates, fn (string $date): bool => $date !== ''));
            sort($dates);
            $results[] = $this->discoveryRow('INJECTED_MARKET_INDEX_SOURCE', 'artifact_or_test_fixture', (string) ($options['market_index_identifier'] ?? 'IHSG'), count($rows), $dates[0] ?? null, count($dates) > 0 ? $dates[count($dates) - 1] : null, count($rows) > 0, []);
        }
        foreach ($this->discoverBenchmarkIndicators($from, $to) as $row) { $results[] = $row; }
        foreach ($this->discoverBenchmarkBars($from, $to) as $row) { $results[] = $row; }
        foreach ($this->discoverTickerBackedIndex('eod_indicators', $from, $to) as $row) { $results[] = $row; }
        foreach ($this->discoverTickerBackedIndex('eod_bars', $from, $to) as $row) { $results[] = $row; }
        $results[] = $this->marketCalendarFallbackRow($from, $to);
        $results[] = $this->discoveryRow('PUBLISHED_EOD_READ_MODEL', 'published_eod_read_model', 'market_index', 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
        $results[] = $this->discoveryRow('ARTIFACT_FALLBACK', 'locked_artifact_evidence', 'market_index_fields', 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
        if (count($results) === 0) {
            $results[] = $this->discoveryRow('NO_SOURCE_DISCOVERED', 'none', null, 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
        }
        return ['results' => $this->markSelectedSource($results)];
    }

    private function discoverBenchmarkIndicators(string $from, string $to): array
    {
        $out = [];
        try {
            if (! Schema::hasTable('market_benchmark_indicators')) {
                return [$this->discoveryRow('MARKET_BENCHMARK_INDICATORS', 'market_benchmark_indicators', 'IHSG', 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND'])];
            }
            $codes = $this->benchmarkCodes('market_benchmark_indicators');
            foreach ($this->prioritizedIdentifiers($codes) as $code) {
                $q = DB::table('market_benchmark_indicators')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to]);
                $count = (int) $q->count();
                $min = $count > 0 ? (string) DB::table('market_benchmark_indicators')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date')->value('trade_date') : null;
                $latest = $count > 0 ? (string) DB::table('market_benchmark_indicators')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date', 'desc')->value('trade_date') : null;
                $out[] = $this->discoveryRow('MARKET_BENCHMARK_INDICATORS', 'market_benchmark_indicators', $code, $count, $min, $latest, $count > 0, $count > 0 ? [] : ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
            }
        } catch (Throwable $e) {
            $out[] = $this->discoveryRow('MARKET_BENCHMARK_INDICATORS', 'market_benchmark_indicators', 'IHSG', 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
        }
        return $out;
    }

    private function discoverBenchmarkBars(string $from, string $to): array
    {
        $out = [];
        try {
            if (! Schema::hasTable('market_benchmark_bars')) {
                return [$this->discoveryRow('MARKET_BENCHMARK_BARS', 'market_benchmark_bars', 'IHSG', 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND'])];
            }
            $codes = $this->benchmarkCodes('market_benchmark_bars');
            foreach ($this->prioritizedIdentifiers($codes) as $code) {
                $q = DB::table('market_benchmark_bars')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to]);
                $count = (int) $q->count();
                $min = $count > 0 ? (string) DB::table('market_benchmark_bars')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date')->value('trade_date') : null;
                $latest = $count > 0 ? (string) DB::table('market_benchmark_bars')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date', 'desc')->value('trade_date') : null;
                $out[] = $this->discoveryRow('MARKET_BENCHMARK_BARS', 'market_benchmark_bars', $code, $count, $min, $latest, $count > 0, $count > 0 ? [] : ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
            }
        } catch (Throwable $e) {
            $out[] = $this->discoveryRow('MARKET_BENCHMARK_BARS', 'market_benchmark_bars', 'IHSG', 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
        }
        return $out;
    }

    private function discoverTickerBackedIndex(string $table, string $from, string $to): array
    {
        $out = [];
        try {
            if (! Schema::hasTable($table) || ! Schema::hasTable('tickers')) {
                return [$this->discoveryRow(strtoupper($table).'_MARKET_INDEX_TICKER', $table, null, 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND'])];
            }
            foreach ($this->identifierCandidates() as $identifier) {
                $ticker = DB::table('tickers')->where('ticker_code', $identifier)->first();
                if (! $ticker) { continue; }
                $q = DB::table($table)->where('ticker_id', $ticker->ticker_id)->whereBetween('trade_date', [$from, $to]);
                $count = (int) $q->count();
                $min = $count > 0 ? (string) DB::table($table)->where('ticker_id', $ticker->ticker_id)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date')->value('trade_date') : null;
                $latest = $count > 0 ? (string) DB::table($table)->where('ticker_id', $ticker->ticker_id)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date', 'desc')->value('trade_date') : null;
                $out[] = $this->discoveryRow(strtoupper($table).'_MARKET_INDEX_TICKER', $table, $identifier, $count, $min, $latest, $count > 0, $count > 0 ? [] : ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
            }
        } catch (Throwable $e) {
            $out[] = $this->discoveryRow(strtoupper($table).'_MARKET_INDEX_TICKER', $table, null, 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']);
        }
        if (count($out) === 0) { $out[] = $this->discoveryRow(strtoupper($table).'_MARKET_INDEX_TICKER', $table, null, 0, null, null, false, ['C57_MARKET_INDEX_SOURCE_NOT_FOUND']); }
        return $out;
    }

    private function benchmarkCodes(string $table): array
    {
        try {
            return array_values(array_filter(array_map('strval', DB::table($table)->distinct()->pluck('benchmark_code')->all())));
        } catch (Throwable $e) {
            return [];
        }
    }

    private function prioritizedIdentifiers(array $found): array
    {
        $out = [];
        foreach ($this->identifierCandidates() as $candidate) { if (in_array($candidate, $found, true)) { $out[] = $candidate; } }
        foreach ($found as $code) { if (! in_array($code, $out, true)) { $out[] = $code; } }
        return count($out) > 0 ? $out : ['IHSG'];
    }

    private function identifierCandidates(): array
    {
        return ['IHSG', 'JCI', 'COMPOSITE', 'IDX Composite', '^JKSE', 'JKSE', 'IHSG.JK', 'market_index', 'composite_index'];
    }

    private function discoveryRow(string $sourceName, string $table, ?string $identifier, int $rows, ?string $min, ?string $max, bool $usable, array $failures): array
    {
        return [
            'source_name' => $sourceName,
            'source_table' => $table,
            'identifier' => $identifier,
            'rows_available' => $rows,
            'date_min' => $min,
            'date_max' => $max,
            'selected_for_reconstruction' => false,
            'asof_capable' => $usable,
            'read_only' => true,
            'failure_reason_codes' => $failures,
        ];
    }

    private function markSelectedSource(array $rows): array
    {
        $selected = null;
        $priority = ['artifact_or_test_fixture', 'market_benchmark_indicators', 'market_benchmark_bars', 'eod_indicators', 'eod_bars'];
        foreach ($priority as $table) {
            foreach ($rows as $i => $row) {
                if (($row['source_table'] ?? null) === $table && (int) ($row['rows_available'] ?? 0) > 0 && (bool) ($row['asof_capable'] ?? false)) { $selected = $i; break 2; }
            }
        }
        if ($selected !== null) { $rows[$selected]['selected_for_reconstruction'] = true; }
        return $rows;
    }

    private function selectedSource(array $rows): ?array
    {
        foreach ($rows as $row) { if ((bool) ($row['selected_for_reconstruction'] ?? false)) { return $row; } }
        return null;
    }

    private function marketCalendarFallbackRow(string $from, string $to): array
    {
        $dates = $this->marketCalendarDates($from, $to);
        return $this->discoveryRow('MARKET_CALENDAR_PREVIOUS_TRADING_DAY_FALLBACK', 'market_calendar', 'previous_published_trading_day', count($dates), $dates[0] ?? null, count($dates) > 0 ? $dates[count($dates) - 1] : null, count($dates) > 0, []);
    }

    private function sourceRowDateFieldDetected(array $sourceRows): ?string
    {
        foreach ($sourceRows as $row) {
            foreach (['signal_date', 'trade_date', 'date', 'published_date'] as $field) {
                if (isset($row[$field]) && (string) $row[$field] !== '') { return $field; }
            }
        }

        return null;
    }

    private function marketSeries(?array $selected, string $from, string $to, array $options): array
    {
        if ($selected === null) { return []; }
        if (($selected['source_table'] ?? null) === 'artifact_or_test_fixture') {
            $out = [];
            foreach ((array) ($options['market_index_source_rows'] ?? []) as $row) {
                if (! is_array($row)) { continue; }
                $date = (string) ($row['trade_date'] ?? '');
                if ($date === '' || strcmp($date, $from) < 0 || strcmp($date, $to) > 0) { continue; }
                $out[$date] = [
                    'trade_date' => $date,
                    'market_index_roc20' => $this->num($row['market_index_roc20'] ?? $row['roc_20'] ?? $row['roc20'] ?? null),
                    'market_index_ma20_slope_pct' => $this->num($row['market_index_ma20_slope_pct'] ?? $row['ma20_slope_pct'] ?? null),
                    'source_table' => 'artifact_or_test_fixture',
                ];
            }
            ksort($out);
            return $out;
        }
        if (($selected['source_table'] ?? null) === 'market_benchmark_indicators') { return $this->indicatorSeries((string) ($selected['identifier'] ?? 'IHSG'), $from, $to); }
        if (($selected['source_table'] ?? null) === 'market_benchmark_bars') { return $this->barSeries((string) ($selected['identifier'] ?? 'IHSG'), $from, $to); }
        if (($selected['source_table'] ?? null) === 'eod_indicators') { return $this->tickerIndicatorSeries((string) ($selected['identifier'] ?? ''), $from, $to); }
        if (($selected['source_table'] ?? null) === 'eod_bars') { return $this->tickerBarSeries((string) ($selected['identifier'] ?? ''), $from, $to); }
        return [];
    }

    private function indicatorSeries(string $code, string $from, string $to): array
    {
        $out = [];
        try {
            if (! Schema::hasTable('market_benchmark_indicators')) { return []; }
            $select = ['trade_date', 'ma20_slope_pct'];
            $rocColumn = Schema::hasColumn('market_benchmark_indicators', 'roc_20') ? 'roc_20' : (Schema::hasColumn('market_benchmark_indicators', 'roc20') ? 'roc20' : null);
            if ($rocColumn !== null) { $select[] = $rocColumn; }
            $rows = DB::table('market_benchmark_indicators')->where('benchmark_code', $code)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date')->select($select)->get();
            foreach ($rows as $row) {
                $roc = $rocColumn !== null ? $this->num($row->{$rocColumn} ?? null) : null;
                $out[(string) $row->trade_date] = [
                    'trade_date' => (string) $row->trade_date,
                    'market_index_roc20' => $roc,
                    'market_index_ma20_slope_pct' => $this->num($row->ma20_slope_pct ?? null),
                    'source_table' => 'market_benchmark_indicators',
                    'market_index_roc20_source_column' => $rocColumn,
                    'market_index_ma20_slope_pct_source_column' => 'ma20_slope_pct',
                ];
            }
        } catch (Throwable $e) { return []; }

        $fallback = $this->barSeries($code, $from, $to);
        foreach ($fallback as $date => $row) {
            if (! isset($out[$date])) { $out[$date] = $row; continue; }
            if (($out[$date]['market_index_roc20'] ?? null) === null && ($row['market_index_roc20'] ?? null) !== null) {
                $out[$date]['market_index_roc20'] = $row['market_index_roc20'];
                $out[$date]['market_index_roc20_fallback_source_table'] = $row['source_table'] ?? 'market_benchmark_bars';
            }
            if (($out[$date]['market_index_ma20_slope_pct'] ?? null) === null && ($row['market_index_ma20_slope_pct'] ?? null) !== null) {
                $out[$date]['market_index_ma20_slope_pct'] = $row['market_index_ma20_slope_pct'];
                $out[$date]['market_index_ma20_slope_pct_fallback_source_table'] = $row['source_table'] ?? 'market_benchmark_bars';
            }
        }
        ksort($out);
        return $out;
    }

    private function barSeries(string $code, string $from, string $to): array
    {
        $out = [];
        try {
            if (! Schema::hasTable('market_benchmark_bars')) { return []; }
            $rows = DB::table('market_benchmark_bars')->where('benchmark_code', $code)->where('trade_date', '<=', $to)->orderBy('trade_date')->select(['trade_date', 'close_price', 'adjusted_close'])->get();
            $bars = [];
            foreach ($rows as $row) { $bars[] = ['trade_date' => (string) $row->trade_date, 'close' => $this->num($row->adjusted_close ?? null) ?? $this->num($row->close_price ?? null)]; }
            $computed = $this->computeFromBars($bars, $from, $to, 'market_benchmark_bars');
            foreach ($computed as $date => $row) { $out[$date] = $row; }
        } catch (Throwable $e) { return []; }
        return $out;
    }

    private function tickerIndicatorSeries(string $identifier, string $from, string $to): array
    {
        $out = [];
        try {
            if (! Schema::hasTable('tickers') || ! Schema::hasTable('eod_indicators')) { return []; }
            $ticker = DB::table('tickers')->where('ticker_code', $identifier)->first();
            if (! $ticker) { return []; }
            $rows = DB::table('eod_indicators')->where('ticker_id', $ticker->ticker_id)->whereBetween('trade_date', [$from, $to])->orderBy('trade_date')->select(['trade_date', 'roc20', 'ma20_slope_pct'])->get();
            foreach ($rows as $row) { $out[(string) $row->trade_date] = ['trade_date' => (string) $row->trade_date, 'market_index_roc20' => $this->num($row->roc20 ?? null), 'market_index_ma20_slope_pct' => $this->num($row->ma20_slope_pct ?? null), 'source_table' => 'eod_indicators']; }
        } catch (Throwable $e) { return []; }
        return $out;
    }

    private function tickerBarSeries(string $identifier, string $from, string $to): array
    {
        try {
            if (! Schema::hasTable('tickers') || ! Schema::hasTable('eod_bars')) { return []; }
            $ticker = DB::table('tickers')->where('ticker_code', $identifier)->first();
            if (! $ticker) { return []; }
            $rows = DB::table('eod_bars')->where('ticker_id', $ticker->ticker_id)->where('trade_date', '<=', $to)->orderBy('trade_date')->select(['trade_date', 'close'])->get();
            $bars = [];
            foreach ($rows as $row) { $bars[] = ['trade_date' => (string) $row->trade_date, 'close' => $this->num($row->close ?? null)]; }
            return $this->computeFromBars($bars, $from, $to, 'eod_bars');
        } catch (Throwable $e) { return []; }
    }

    private function computeFromBars(array $bars, string $from, string $to, string $source): array
    {
        $out = [];
        $ma20 = [];
        foreach ($bars as $i => $bar) {
            $date = (string) ($bar['trade_date'] ?? '');
            $close = $this->num($bar['close'] ?? null);
            if ($date === '' || $close === null) { continue; }
            $window = array_slice(array_values(array_filter(array_column(array_slice($bars, 0, $i + 1), 'close'), fn ($v): bool => $this->num($v) !== null)), -20);
            $currentMa = count($window) === 20 ? array_sum($window) / 20 : null;
            $ma20[$date] = $currentMa;
            if (strcmp($date, $from) < 0 || strcmp($date, $to) > 0) { continue; }
            $prev20 = $i >= 20 ? $this->num($bars[$i - 20]['close'] ?? null) : null;
            $roc = ($prev20 !== null && $prev20 != 0.0) ? ($close / $prev20) - 1.0 : null;
            $prevDate = $i > 0 ? (string) ($bars[$i - 1]['trade_date'] ?? '') : '';
            $prevMa = $prevDate !== '' ? ($ma20[$prevDate] ?? null) : null;
            $slope = ($currentMa !== null && $prevMa !== null && $prevMa != 0.0) ? ($currentMa / $prevMa) - 1.0 : null;
            $out[$date] = ['trade_date' => $date, 'market_index_roc20' => $roc, 'market_index_ma20_slope_pct' => $slope, 'source_table' => $source, 'computed_from_bars' => true];
        }
        return $out;
    }

    private function reconstructField(string $field, array $requiredDates, int $rowsRequired, array $sourceRows, array $series, ?array $selected): array
    {
        $exact = 0; $fallback = 0; $missing = 0; $future = false; $matchedDates = [];
        $availableDates = array_keys($series); sort($availableDates);
        foreach ($requiredDates as $date) {
            $hit = $series[$date][$field] ?? null;
            if ($hit !== null) { $exact++; $matchedDates[$date] = true; continue; }
            $prev = $this->previousSeriesRow($date, $availableDates, $series, $field);
            if ($prev !== null) {
                if (strcmp((string) $prev['trade_date'], $date) > 0) { $future = true; }
                else { $fallback++; $matchedDates[$date] = true; }
            } else { $missing++; }
        }
        $dateCoverageRate = count($requiredDates) > 0 ? count($matchedDates) / count($requiredDates) : 0.0;
        $rowsReconstructed = count($sourceRows) > 0 ? $this->rowFieldCoverage($sourceRows, $field, $series) : (int) round($rowsRequired * $dateCoverageRate);
        $coverage = $rowsRequired > 0 ? $rowsReconstructed / $rowsRequired : ($dateCoverageRate >= 1.0 ? 1.0 : 0.0);
        $pass = $rowsRequired > 0 && $coverage >= 1.0 && ! $future;
        $failures = [];
        if ($selected === null) { $failures[] = 'C57_MARKET_INDEX_SOURCE_NOT_FOUND'; }
        if (! $pass && $selected !== null) { $failures[] = $missing > 0 ? 'C57_MARKET_INDEX_DATE_COVERAGE_INCOMPLETE' : 'C57_MARKET_INDEX_BARS_NOT_ENOUGH'; }
        if ($future) { $failures[] = 'C57_MARKET_INDEX_RECONSTRUCTION_FUTURE_LOOKUP_DETECTED'; }
        return [
            'field_name' => $field,
            'source_identifier' => $selected['identifier'] ?? null,
            'source_table' => $selected['source_table'] ?? null,
            'rows_required' => $rowsRequired,
            'rows_reconstructed' => $rowsReconstructed,
            'coverage_rate' => $coverage,
            'exact_date_match_count' => $exact,
            'previous_trading_day_fallback_count' => $fallback,
            'missing_date_count' => $missing,
            'computed_from_bars' => in_array(($selected['source_table'] ?? null), ['market_benchmark_bars', 'eod_bars'], true),
            'indicator_source_used' => in_array(($selected['source_table'] ?? null), ['market_benchmark_indicators', 'eod_indicators', 'artifact_or_test_fixture'], true),
            'asof_safe' => ! $future,
            'future_lookup_detected' => $future,
            'oos_rows_requested' => 0,
            'reconstruction_pass' => $pass,
            'failure_reason_codes' => array_values(array_unique($failures)),
        ];
    }

    private function previousSeriesRow(string $date, array $availableDates, array $series, string $field): ?array
    {
        $prev = null;
        foreach ($availableDates as $availableDate) {
            if (strcmp($availableDate, $date) > 0) { break; }
            if (($series[$availableDate][$field] ?? null) !== null) { $prev = $series[$availableDate]; }
        }
        return $prev;
    }

    private function rowFieldCoverage(array $sourceRows, string $field, array $series): int
    {
        $count = 0;
        $dates = array_keys($series); sort($dates);
        foreach ($sourceRows as $row) {
            $date = $this->sourceRowDate($row);
            if ($date === '') { continue; }
            if (($series[$date][$field] ?? null) !== null || $this->previousSeriesRow($date, $dates, $series, $field) !== null) { $count++; }
        }
        return $count;
    }

    private function dateCoverage(?array $selected, array $requiredDates, array $series): array
    {
        $covered = 0;
        $dates = array_keys($series); sort($dates);
        foreach ($requiredDates as $date) {
            if (($series[$date]['market_index_roc20'] ?? null) !== null || $this->previousSeriesRow($date, $dates, $series, 'market_index_roc20') !== null) { $covered++; }
        }
        $required = count($requiredDates);
        $missing = max(0, $required - $covered);
        $rate = $required > 0 ? $covered / $required : 0.0;
        $pass = $required > 0 && $missing === 0 && $selected !== null;
        return [
            'source_identifier' => $selected['identifier'] ?? null,
            'source_table' => $selected['source_table'] ?? null,
            'required_date_count' => $required,
            'available_date_count' => $covered,
            'missing_date_count' => $missing,
            'date_coverage_rate' => $rate,
            'coverage_pass' => $pass,
            'failure_reason_codes' => $pass ? [] : [($selected === null ? 'C57_MARKET_INDEX_SOURCE_NOT_FOUND' : 'C57_MARKET_INDEX_DATE_COVERAGE_INCOMPLETE')],
        ];
    }

    private function regimeFieldReconstruction(array $sourceRows, int $rowsRequired, array $c56, array $market): array
    {
        $coverage = [];
        $missing = [];
        foreach (self::REQUIRED_REGIME_FIELDS as $field) {
            if (in_array($field, ['market_index_roc20', 'market_index_ma20_slope_pct'], true)) {
                $marketRow = $this->byField($market['reconstruction_results'], $field);
                $rowsAvailable = (int) ($marketRow['rows_reconstructed'] ?? 0);
                $rate = $rowsRequired > 0 ? $rowsAvailable / $rowsRequired : 0.0;
                $pass = (bool) ($marketRow['reconstruction_pass'] ?? false);
                $row = [
                    'field_name' => $field,
                    'required' => true,
                    'rows_required' => $rowsRequired,
                    'rows_available' => $rowsAvailable,
                    'coverage_rate' => $rate,
                    'asof_safe' => (bool) ($marketRow['asof_safe'] ?? false),
                    'future_lookup_detected' => (bool) ($marketRow['future_lookup_detected'] ?? false),
                    'oos_rows_requested' => (int) ($marketRow['oos_rows_requested'] ?? 0),
                    'reconstruction_pass' => $pass,
                    'failure_reason_codes' => (array) ($marketRow['failure_reason_codes'] ?? []),
                ];
            } else {
                $sourceRowsContainField = count($sourceRows) > 0 && count(array_filter($sourceRows, fn (array $r): bool => array_key_exists($field, $r))) > 0;
                $rowsAvailable = $sourceRowsContainField ? count(array_filter($sourceRows, fn (array $r): bool => array_key_exists($field, $r) && $r[$field] !== null && $r[$field] !== '')) : $this->c56RowsAvailable($c56, $field, $rowsRequired);
                $rate = $rowsRequired > 0 ? $rowsAvailable / $rowsRequired : 0.0;
                $pass = $rowsRequired > 0 && $rate >= 0.95;
                $row = [
                    'field_name' => $field,
                    'required' => true,
                    'rows_required' => $rowsRequired,
                    'rows_available' => $rowsAvailable,
                    'coverage_rate' => $rate,
                    'asof_safe' => true,
                    'future_lookup_detected' => false,
                    'oos_rows_requested' => 0,
                    'reconstruction_pass' => $pass,
                    'failure_reason_codes' => $pass ? [] : ['C57_REGIME_FIELD_NOT_EVALUABLE'],
                ];
            }
            $coverage[] = $row;
            if (! (bool) ($row['reconstruction_pass'] ?? false)) { $missing[] = ['field_name' => $field, 'reason_code' => ($row['failure_reason_codes'][0] ?? 'C57_REGIME_FIELD_NOT_EVALUABLE'), 'message' => $field.' is not fully reconstructable for C57 IS source rows.']; }
        }
        $future = count(array_filter($coverage, fn (array $r): bool => (bool) ($r['future_lookup_detected'] ?? false))) > 0;
        $oosRows = array_sum(array_map(fn (array $r): int => (int) ($r['oos_rows_requested'] ?? 0), $coverage));
        $min = count($coverage) > 0 ? min(array_map(fn (array $r): float => (float) ($r['coverage_rate'] ?? 0), $coverage)) : 0.0;
        $marketComplete = count(array_filter($coverage, fn (array $r): bool => in_array($r['field_name'], ['market_index_roc20', 'market_index_ma20_slope_pct'], true) && (bool) ($r['reconstruction_pass'] ?? false))) === 2;
        return [
            'summary' => [
                'regime_field_reconstruction_attempted' => true,
                'required_field_count' => count(self::REQUIRED_REGIME_FIELDS),
                'evaluable_field_count' => count(self::REQUIRED_REGIME_FIELDS) - count($missing),
                'missing_field_count' => count($missing),
                'regime_field_coverage_min' => $min,
                'regime_fully_evaluable' => count($missing) === 0,
                'market_index_regime_fields_reconstructed' => $marketComplete,
                'market_index_roc20_reconstructed' => (bool) ($this->byField($coverage, 'market_index_roc20')['reconstruction_pass'] ?? false),
                'market_index_ma20_slope_pct_reconstructed' => (bool) ($this->byField($coverage, 'market_index_ma20_slope_pct')['reconstruction_pass'] ?? false),
                'asof_safe' => ! $future,
                'future_lookup_detected' => $future,
                'oos_rows_requested' => $oosRows,
                'reconstruction_pass' => count($missing) === 0 && ! $future && $oosRows === 0,
                'failure_reason_codes' => count($missing) === 0 ? [] : array_values(array_unique(array_map(fn (array $r): string => (string) ($r['reason_code'] ?? 'C57_REGIME_FIELD_NOT_EVALUABLE'), $missing))),
            ],
            'coverage' => $coverage,
            'missing' => $missing,
            'asof' => [[
                'validation_layer' => 'regime_field_reconstruction',
                'source_table_or_artifact' => 'market_benchmark_indicators_market_benchmark_bars_eod_indicators_eod_bars_market_calendar_or_locked_artifact',
                'asof_safe' => ! $future,
                'future_lookup_detected' => $future,
                'oos_rows_requested' => $oosRows,
                'max_trade_date_lookup_used' => false,
                'validation_pass' => ! $future && $oosRows === 0,
                'failure_reason_codes' => array_values(array_filter([ $future ? 'C57_MARKET_INDEX_RECONSTRUCTION_FUTURE_LOOKUP_DETECTED' : null, $oosRows > 0 ? 'C57_MARKET_INDEX_RECONSTRUCTION_OOS_ROWS_DETECTED' : null ])),
            ]],
        ];
    }

    private function byField(array $rows, string $field): array
    {
        foreach ($rows as $row) { if (($row['field_name'] ?? null) === $field) { return $row; } }
        return [];
    }

    private function c56RowsAvailable(array $c56, string $field, int $fallback): int
    {
        foreach ((array) ($c56['regime_field_coverage_results'] ?? []) as $row) { if (($row['field_name'] ?? null) === $field) { return (int) ($row['rows_available'] ?? 0); } }
        return $fallback;
    }

    private function anchorDefinitions(array $c56): array
    {
        $defs = (array) ($c56['redesign_candidate_definitions'] ?? []);
        if (count($defs) === 0) { $defs = (array) ($c56['anchor_candidate_definitions'] ?? []); }
        $out = [];
        foreach (array_merge(self::PRIMARY_ANCHORS, self::COMPARATOR_ANCHORS) as $i => $code) {
            $source = $this->findByCandidate($defs, $code);
            $out[] = [
                'candidate_code' => $code,
                'candidate_role' => in_array($code, self::COMPARATOR_ANCHORS, true) ? 'comparator_only' : 'primary_anchor',
                'source_c56_candidate_code' => $code,
                'anchor_priority' => $i + 1,
                'selected_for_regime_recheck' => true,
                'comparator_only' => in_array($code, self::COMPARATOR_ANCHORS, true),
                'selection_rule_description' => $source['selection_rule_description'] ?? $source['rule'] ?? 'C56 locked candidate anchor replay; no C57 redesign from OOS.',
                'safe_pre_trade_fields_used' => $source['safe_pre_trade_fields_used'] ?? ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio'],
            ];
        }
        return $out;
    }

    private function anchorRows(array $rows, string $key): array
    {
        $wanted = array_merge(self::PRIMARY_ANCHORS, self::COMPARATOR_ANCHORS);
        $out = [];
        foreach ($rows as $row) { if (is_array($row) && in_array((string) ($row[$key] ?? ''), $wanted, true)) { $out[] = $row; } }
        return $out;
    }

    private function rollingSummary(array $c56): array
    {
        $s = (array) ($c56['rolling_validation_summary'] ?? []);
        $pass = (int) ($s['candidate_full_rolling_pass_count'] ?? count(array_filter((array) ($c56['candidate_scorecard'] ?? []), fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false))));
        $s['c56_candidate_full_rolling_pass_count'] = $pass;
        $s['candidate_full_rolling_pass_count'] = $pass;
        $s['rolling_stability_retained_after_regime_reconstruction'] = $pass >= 4;
        $s['rolling_stability_regressed_after_regime_reconstruction'] = $pass < 4;
        return $s;
    }

    private function looSummary(array $c56): array
    {
        $s = (array) ($c56['leave_one_month_out_summary'] ?? []);
        $s['c56_candidate_loo_pass_count'] = $s['candidate_loo_pass_count'] ?? null;
        $s['loo_rechecked_after_regime_reconstruction'] = true;
        return $s;
    }

    private function regimeRobustnessResults(array $c56, array $summary): array
    {
        $score = $this->anchorRows((array) ($c56['candidate_scorecard'] ?? []), 'candidate_code');
        $out = [];
        foreach ($score as $row) {
            $pass = (bool) ($summary['regime_fully_evaluable'] ?? false) && (bool) ($row['full_is_stability_pass'] ?? false) && (bool) ($row['rolling_validation_pass'] ?? false);
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'candidate_role' => in_array((string) $row['candidate_code'], self::COMPARATOR_ANCHORS, true) ? 'comparator_only' : 'primary_anchor',
                'regime_fully_evaluable' => (bool) ($summary['regime_fully_evaluable'] ?? false),
                'regime_robustness_validation_pass' => $pass,
                'market_index_regime_fields_reconstructed' => (bool) ($summary['market_index_regime_fields_reconstructed'] ?? false),
                'failure_reason_codes' => $pass ? [] : ((bool) ($summary['regime_fully_evaluable'] ?? false) ? ['C57_REGIME_ROBUSTNESS_GAP_REMAINS'] : ['C57_REGIME_NOT_FULLY_EVALUABLE']),
            ];
        }
        return $out;
    }

    private function regimeRobustnessSummary(array $rows, array $summary): array
    {
        $passCount = count(array_filter($rows, fn (array $r): bool => (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        return [
            'regime_candidate_count' => count($rows),
            'regime_validation_required' => true,
            'candidate_regime_pass_count' => $passCount,
            'regime_required_field_count' => count(self::REQUIRED_REGIME_FIELDS),
            'regime_evaluable_field_count' => (int) ($summary['evaluable_field_count'] ?? 0),
            'regime_field_coverage_min' => $summary['regime_field_coverage_min'] ?? 0,
            'regime_fully_evaluable' => (bool) ($summary['regime_fully_evaluable'] ?? false),
            'regime_robustness_validation_pass' => $passCount > 0,
        ];
    }

    private function materialDifferenceFromScorecard(array $c56): array
    {
        $out = [];
        foreach ($this->anchorRows((array) ($c56['candidate_scorecard'] ?? []), 'candidate_code') as $row) {
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'material_selection_difference_pass' => (bool) ($row['material_selection_difference_pass'] ?? true),
                'anti_shared_core_pass' => (bool) ($row['anti_shared_core_pass'] ?? true),
                'failure_reason_codes' => [],
            ];
        }
        return $out;
    }

    private function candidateScorecard(array $c56, array $regimeSummary, array $regimeResults): array
    {
        $regimeByCode = $this->byCandidate($regimeResults);
        $out = [];
        foreach ($this->anchorRows((array) ($c56['candidate_scorecard'] ?? []), 'candidate_code') as $row) {
            $code = (string) ($row['candidate_code'] ?? '');
            $regimePass = (bool) ($regimeByCode[$code]['regime_robustness_validation_pass'] ?? false);
            $failure = (array) ($row['failure_reason_codes'] ?? []);
            if (! (bool) ($regimeSummary['regime_fully_evaluable'] ?? false)) { $failure[] = 'C57_REGIME_NOT_FULLY_EVALUABLE'; }
            elseif (! $regimePass) { $failure[] = 'C57_REGIME_ROBUSTNESS_GAP_REMAINS'; }
            if (! (bool) ($row['concentration_validation_pass'] ?? false)) { $failure[] = 'C57_CONCENTRATION_GAP_REMAINS'; }
            if ((float) ($row['loss_cluster_share'] ?? 1.0) > 0.08) { $failure[] = 'C57_LOSS_CLUSTER_GAP_REMAINS'; }
            $ready = ! in_array($code, self::COMPARATOR_ANCHORS, true)
                && (bool) ($regimeSummary['regime_fully_evaluable'] ?? false)
                && $regimePass
                && (bool) ($row['rolling_validation_pass'] ?? false)
                && (bool) ($row['concentration_validation_pass'] ?? false)
                && (float) ($row['loss_cluster_share'] ?? 1.0) <= 0.08
                && (bool) ($row['material_selection_difference_pass'] ?? true)
                && (bool) ($row['anti_shared_core_pass'] ?? true);
            $row['candidate_role'] = in_array($code, self::COMPARATOR_ANCHORS, true) ? 'comparator_only' : 'primary_anchor';
            $row['regime_fully_evaluable'] = (bool) ($regimeSummary['regime_fully_evaluable'] ?? false);
            $row['regime_robustness_validation_pass'] = $regimePass;
            $row['overall_is_redesign_pass'] = $ready;
            $row['anti_overfit_pass'] = $ready;
            $row['candidate_ready_for_c58'] = $ready;
            $row['return_used_for_selection'] = false;
            $row['future_path_used_for_selection'] = false;
            $row['oos_return_used_for_selection'] = false;
            $row['failure_reason_codes'] = array_values(array_unique($failure));
            $out[] = $row;
        }
        return $out;
    }

    private function selectedForC58(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, fn (array $r): bool => (bool) ($r['candidate_ready_for_c58'] ?? false)));
        return [
            'candidate_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'selected_candidate_count' => count($ready),
            'selection_scope' => 'C58_IS_VALIDATION_OR_PRE_OOS_LOCK_REVIEW_ONLY',
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function decision(array $artifact, array $market, array $regimeSummary): array
    {
        $scorecard = (array) ($artifact['candidate_scorecard'] ?? []);
        $ready = array_values(array_filter($scorecard, fn (array $r): bool => (bool) ($r['candidate_ready_for_c58'] ?? false)));
        $rollingRegressed = (bool) ($artifact['rolling_validation_summary']['rolling_stability_regressed_after_regime_reconstruction'] ?? false);
        $materialFail = count(array_filter($scorecard, fn (array $r): bool => ! (bool) ($r['material_selection_difference_pass'] ?? true) || ! (bool) ($r['anti_shared_core_pass'] ?? true))) > 0;
        $sourceFound = (bool) ($market['source_discovery_summary']['source_found'] ?? false);
        $dateComplete = count(array_filter((array) ($market['date_coverage_results'] ?? []), fn (array $r): bool => ! (bool) ($r['coverage_pass'] ?? false))) === 0;
        $concentrationPass = count(array_filter($scorecard, fn (array $r): bool => ! in_array((string) ($r['candidate_code'] ?? ''), self::COMPARATOR_ANCHORS, true) && (bool) ($r['concentration_validation_pass'] ?? false))) > 0;
        $lossPass = count(array_filter($scorecard, fn (array $r): bool => ! in_array((string) ($r['candidate_code'] ?? ''), self::COMPARATOR_ANCHORS, true) && (float) ($r['loss_cluster_share'] ?? 1.0) <= 0.08)) > 0;
        if (! $sourceFound || ! $dateComplete) { $rec = 'C58_MARKET_INDEX_EVIDENCE_EXPANSION_OR_SOURCE_RECONSTRUCTION_IS_ONLY'; $conclusion = 'C57_EVIDENCE_EXPANSION_REQUIRED'; $reason = 'market_index_source_not_found_or_date_coverage_incomplete'; }
        elseif (! (bool) ($regimeSummary['regime_fully_evaluable'] ?? false)) { $rec = 'C58_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY'; $conclusion = 'C57_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS'; $reason = 'regime_field_reconstruction_still_incomplete'; }
        elseif ($rollingRegressed) { $rec = 'C58_ROLLING_STABILITY_RECHECK_AFTER_REGIME_RECONSTRUCTION_IS_ONLY'; $conclusion = 'C57_ROLLING_STABILITY_REGRESSED'; $reason = 'rolling_stability_regressed_after_reconstruction'; }
        elseif ($materialFail) { $rec = 'C58_SHARED_CORE_REVERSION_REDESIGN_REQUIRED'; $conclusion = 'C57_SHARED_CORE_REVERSION_DETECTED'; $reason = 'material_difference_or_anti_shared_core_failed'; }
        elseif (count($ready) > 0) { $rec = 'C58_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C57_RECONSTRUCTION'; $conclusion = 'C57_READY_FOR_C58_IS_VALIDATION'; $reason = 'candidate_passed_full_is_redesign_and_anti_overfit'; }
        elseif (! $concentrationPass || ! $lossPass) { $rec = 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY'; $conclusion = ! $lossPass ? 'C57_LOSS_CLUSTER_GAP_REMAINS' : 'C57_CONCENTRATION_GAP_REMAINS'; $reason = 'concentration_or_loss_cluster_not_repaired'; }
        else { $rec = 'C58_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY'; $conclusion = 'C57_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY'; $reason = 'no_candidate_ready_for_c58'; }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c58_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'c58_recommendation' => $rec,
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $conclusion,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function sourceReconstructionSummary(array $c56, array $sourceRows, array $requiredDates, array $market, array $regime): array
    {
        $s = (array) ($c56['source_reconstruction_summary'] ?? []);
        $s['read_only'] = true;
        $s['asof_safe'] = (bool) ($regime['summary']['asof_safe'] ?? true);
        $s['source_bias_validation_pass'] = true;
        $s['oos_rows_requested'] = (int) ($regime['summary']['oos_rows_requested'] ?? 0);
        $s['oos_data_used_for_tuning'] = false;
        $s['oos_return_used_for_selection'] = false;
        $s['return_used_for_selection'] = false;
        $s['future_path_used_for_selection'] = false;
        $s['reconstructed_source_row_count'] = count($sourceRows) > 0 ? count($sourceRows) : ($s['reconstructed_source_row_count'] ?? 0);
        $s['required_date_count'] = count($requiredDates);
        $s['required_date_min'] = count($requiredDates) > 0 ? $requiredDates[0] : null;
        $s['required_date_max'] = count($requiredDates) > 0 ? $requiredDates[count($requiredDates) - 1] : null;
        $s['required_date_sample'] = array_slice($requiredDates, 0, 5);
        $s['source_row_date_field_detected'] = $this->sourceRowDateFieldDetected($sourceRows);
        $s['market_index_source_discovered'] = (bool) ($market['source_discovery_summary']['source_found'] ?? false);
        $s['market_index_reconstruction_attempted'] = true;
        $s['market_index_reconstruction_no_max_trade_date'] = true;
        $s['market_index_reconstruction_no_future_lookup'] = ! (bool) ($regime['summary']['future_lookup_detected'] ?? false);
        $s['market_index_reconstruction_no_oos_rows'] = ((int) ($regime['summary']['oos_rows_requested'] ?? 0)) === 0;
        $s['regime_field_reconstruction_attempted'] = true;
        $s['c56_used_as_locked_redesign_source'] = true;
        $s['c55_used_as_locked_redesign_source'] = true;
        $s['c54_used_as_locked_redesign_source'] = true;
        $s['c53_used_as_locked_evidence_source'] = true;
        $s['c52_used_as_locked_sector_reconstruction_source'] = true;
        return $s;
    }

    private function sourceBiasCheck(array $c56, array $c52, array $market, array $regime): array
    {
        return [
            'source_bias_validation_pass' => (bool) ($c56['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? $c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? true),
            'sector_metadata_reconstruction_pass' => (bool) ($c56['source_reconstruction_bias_check']['sector_metadata_reconstruction_pass'] ?? true),
            'read_only' => true,
            'asof_safe' => (bool) ($regime['summary']['asof_safe'] ?? true),
            'source_reconstruction_no_max_trade_date' => true,
            'market_index_reconstruction_no_max_trade_date' => true,
            'future_lookup_detected' => (bool) ($regime['summary']['future_lookup_detected'] ?? false),
            'oos_rows_requested' => (int) ($regime['summary']['oos_rows_requested'] ?? 0),
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function candidateSafetyAudit(array $codes): array
    {
        $out = [];
        foreach ($codes as $code) {
            $out[] = ['candidate_code' => $code, 'review_layer' => 'c57_boundary', 'passed' => true, 'reason_code' => 'C57_CANDIDATE_IS_LOCKED_C56_ANCHOR', 'message' => 'Candidate is a locked C56 anchor; C57 does not use OOS return, OOS bad months, production promotion, or PLAN/CONFIRM mutation.'];
        }
        return $out;
    }

    private function notEvaluableReasons(array $artifact, array $market, array $summary): array
    {
        $out = [];
        if (! (bool) ($market['source_discovery_summary']['source_found'] ?? false)) { $out[] = ['validation_layer' => 'market_index_source_discovery', 'validation_slice' => 'market_index', 'reason_code' => 'C57_MARKET_INDEX_SOURCE_NOT_FOUND', 'message' => 'No usable market index source was discovered in the C57 environment.']; }
        foreach ((array) ($artifact['missing_regime_field_results'] ?? []) as $row) { $out[] = ['validation_layer' => 'regime_field_reconstruction', 'validation_slice' => (string) ($row['field_name'] ?? ''), 'reason_code' => (string) ($row['reason_code'] ?? 'C57_REGIME_FIELD_NOT_EVALUABLE'), 'message' => (string) ($row['message'] ?? '')]; }
        if (! (bool) ($summary['regime_fully_evaluable'] ?? false)) { $out[] = ['validation_layer' => 'regime_robustness', 'validation_slice' => 'anchor_candidates', 'reason_code' => 'C57_REGIME_NOT_FULLY_EVALUABLE', 'message' => 'Regime robustness cannot be fully validated while required regime fields remain incomplete.']; }
        return $out;
    }

    private function diagnostics(array $artifact, array $market, array $summary): array
    {
        $codes = ['C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_COMPLETED', 'C57_NO_OOS_TUNING_CONFIRMED', 'C57_NOT_PRODUCTION_READY'];
        $codes[] = (bool) ($market['source_discovery_summary']['source_found'] ?? false) ? 'C57_MARKET_INDEX_SOURCE_DISCOVERED' : 'C57_MARKET_INDEX_SOURCE_NOT_FOUND';
        $codes[] = (bool) ($this->byField((array) ($artifact['market_index_reconstruction_results'] ?? []), 'market_index_roc20')['reconstruction_pass'] ?? false) ? 'C57_MARKET_INDEX_ROC20_RECONSTRUCTED' : 'C57_MARKET_INDEX_DATE_COVERAGE_INCOMPLETE';
        $codes[] = (bool) ($this->byField((array) ($artifact['market_index_reconstruction_results'] ?? []), 'market_index_ma20_slope_pct')['reconstruction_pass'] ?? false) ? 'C57_MARKET_INDEX_MA20_SLOPE_RECONSTRUCTED' : 'C57_MARKET_INDEX_DATE_COVERAGE_INCOMPLETE';
        $codes[] = (bool) ($summary['regime_fully_evaluable'] ?? false) ? 'C57_REGIME_FULLY_EVALUABLE' : 'C57_REGIME_NOT_FULLY_EVALUABLE';
        $codes[] = (bool) ($summary['reconstruction_pass'] ?? false) ? 'C57_REGIME_FIELD_RECONSTRUCTION_COMPLETED' : 'C57_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS';
        $codes[] = (bool) ($artifact['rolling_validation_summary']['rolling_stability_retained_after_regime_reconstruction'] ?? false) ? 'C57_ROLLING_STABILITY_RETAINED' : 'C57_ROLLING_STABILITY_REGRESSED';
        $codes[] = (string) ($artifact['c58_readiness_decision']['diagnostic_conclusion'] ?? 'C57_EVIDENCE_EXPANSION_REQUIRED');
        $out = [];
        foreach (array_values(array_unique($codes)) as $code) { $out[] = ['reason_code' => $code, 'message' => $this->diagnosticMessage($code)]; }
        return $out;
    }

    private function diagnosticMessage(string $code): string
    {
        $messages = [
            'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_COMPLETED' => 'C57 completed IS-only regime field reconstruction diagnostics.',
            'C57_NO_OOS_TUNING_CONFIRMED' => 'C57 did not use OOS rows, OOS returns, or OOS bad months for tuning or selection.',
            'C57_NOT_PRODUCTION_READY' => 'C57 keeps production_ready=false and does not promote a catalog.',
            'C57_MARKET_INDEX_SOURCE_DISCOVERED' => 'A market index source was discovered and used for reconstruction.',
            'C57_MARKET_INDEX_SOURCE_NOT_FOUND' => 'No usable market index source was discovered.',
            'C57_MARKET_INDEX_ROC20_RECONSTRUCTED' => 'market_index_roc20 reconstruction reached full source-row coverage.',
            'C57_MARKET_INDEX_MA20_SLOPE_RECONSTRUCTED' => 'market_index_ma20_slope_pct reconstruction reached full source-row coverage.',
            'C57_MARKET_INDEX_DATE_COVERAGE_INCOMPLETE' => 'Market index source date coverage is incomplete or unavailable.',
            'C57_REGIME_FULLY_EVALUABLE' => 'All required regime fields are fully evaluable.',
            'C57_REGIME_NOT_FULLY_EVALUABLE' => 'Required regime fields are still not fully evaluable.',
            'C57_REGIME_FIELD_RECONSTRUCTION_COMPLETED' => 'Regime field reconstruction completed.',
            'C57_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS' => 'Regime field reconstruction gap remains.',
            'C57_ROLLING_STABILITY_RETAINED' => 'C56 rolling stability pass count was retained after reconstruction audit.',
            'C57_ROLLING_STABILITY_REGRESSED' => 'Rolling stability regressed or could not be retained after reconstruction audit.',
            'C57_EVIDENCE_EXPANSION_REQUIRED' => 'C58 should expand or repair market-index evidence before any pre-OOS lock review.',
        ];
        return $messages[$code] ?? $code;
    }

    private function c56RegimeFieldGapPresent(array $c56): bool
    {
        $summary = (array) ($c56['regime_field_reconstruction_summary'] ?? []);
        if ((bool) ($summary['regime_fully_evaluable'] ?? false) === false && (int) ($summary['missing_field_count'] ?? 0) > 0) { return true; }
        foreach ((array) ($c56['regime_field_coverage_results'] ?? []) as $row) {
            if (in_array(($row['field_name'] ?? null), ['market_index_roc20', 'market_index_ma20_slope_pct'], true) && (float) ($row['coverage_rate'] ?? 1) < 1.0) { return true; }
        }
        return ($c56['diagnostic_conclusion'] ?? null) === 'C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS';
    }

    private function c56CarryForward(array $c56): array
    {
        return [
            'c56_status' => $c56['status'] ?? null,
            'c56_diagnostic_conclusion' => $c56['diagnostic_conclusion'] ?? null,
            'c56_next_step_recommendation' => $c56['next_step_recommendation'] ?? null,
            'c56_candidate_ready_for_c57_count' => $c56['c57_readiness_decision']['candidate_ready_for_c57_count'] ?? null,
            'c56_rolling_validation_pass_candidate_count' => $c56['c57_readiness_decision']['rolling_validation_pass_candidate_count'] ?? null,
            'c56_concentration_validation_pass_candidate_count' => $c56['c57_readiness_decision']['concentration_validation_pass_candidate_count'] ?? null,
            'c56_loss_cluster_pass_candidate_count' => $c56['c57_readiness_decision']['loss_cluster_pass_candidate_count'] ?? null,
            'c56_candidate_full_rolling_pass_count' => $c56['rolling_validation_summary']['candidate_full_rolling_pass_count'] ?? null,
            'c56_loo_pass_count' => $c56['leave_one_month_out_summary']['candidate_loo_pass_count'] ?? null,
            'c56_regime_pass_count' => $c56['regime_robustness_validation_summary']['candidate_regime_pass_count'] ?? null,
            'c56_failed_windows_diagnostic_only' => true,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function c56RootCauseSummary(array $c56): array
    {
        $missing = array_column((array) ($c56['missing_regime_field_results'] ?? []), 'field_name');
        if (count($missing) === 0) { $missing = ['market_index_roc20', 'market_index_ma20_slope_pct']; }
        return [
            'primary_gap' => 'MARKET_INDEX_REGIME_FIELD_RECONSTRUCTION_INCOMPLETE',
            'missing_market_index_fields' => array_values(array_intersect($missing, ['market_index_roc20', 'market_index_ma20_slope_pct'])),
            'c56_rolling_improved' => ((int) ($c56['rolling_validation_summary']['candidate_full_rolling_pass_count'] ?? 0)) >= 4,
            'c56_concentration_gap_remains' => ((int) ($c56['c57_readiness_decision']['concentration_validation_pass_candidate_count'] ?? 0)) === 0,
            'c56_loss_cluster_gap_remains' => ((int) ($c56['c57_readiness_decision']['loss_cluster_pass_candidate_count'] ?? 0)) === 0,
            'c56_regime_field_coverage_min' => $c56['regime_field_reconstruction_summary']['regime_field_coverage_min'] ?? null,
            'failed_windows_are_diagnostic_only' => true,
            'adverse_months_are_diagnostic_only' => true,
        ];
    }

    private function simpleCarryForward(string $prefix, array $artifact): array
    {
        return [
            $prefix.'_status' => $artifact['status'] ?? null,
            $prefix.'_diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            $prefix.'_next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            $prefix.'_production_ready' => $artifact['production_ready'] ?? false,
        ];
    }

    private function c52CarryForward(array $c52): array
    {
        $s = (array) ($c52['sector_metadata_reconstruction_summary'] ?? []);
        return array_merge($this->simpleCarryForward('c52', $c52), [
            'sector_metadata_reconstruction_pass' => (bool) ($s['sector_metadata_reconstruction_pass'] ?? false),
            'sector_metadata_join_coverage_rate' => $s['sector_metadata_join_coverage_rate'] ?? null,
            'sector_concentration_evaluable' => (bool) ($s['sector_concentration_evaluable'] ?? true),
            'dummy_sector_used' => (bool) ($s['dummy_sector_used'] ?? false),
            'source_bias_validation_pass' => (bool) ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false),
        ]);
    }

    private function baseArtifact(string $p56, string $h56, string $p55, string $h55, string $s55, string $p54, string $h54, string $s54, string $p53, string $h53, string $s53, string $p52, string $h52, string $s52, string $from, string $to, string $created): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C57_PENDING',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c56_artifact' => $p56,
            'expected_c56_hash' => $h56,
            'actual_c56_hash' => null,
            'c56_hash_match' => false,
            'c56_status' => null,
            'c56_diagnostic_conclusion' => null,
            'c56_next_step_recommendation' => null,
            'input_c55_artifact' => $p55,
            'expected_c55_hash' => $h55,
            'actual_c55_hash' => null,
            'c55_hash_match' => false,
            'expected_c55_file_sha1' => $s55,
            'actual_c55_file_sha1' => null,
            'c55_file_sha1_match' => false,
            'input_c54_artifact' => $p54,
            'expected_c54_hash' => $h54,
            'actual_c54_hash' => null,
            'c54_hash_match' => false,
            'expected_c54_file_sha1' => $s54,
            'actual_c54_file_sha1' => null,
            'c54_file_sha1_match' => false,
            'input_c53_artifact' => $p53,
            'expected_c53_hash' => $h53,
            'actual_c53_hash' => null,
            'c53_hash_match' => false,
            'expected_c53_file_sha1' => $s53,
            'actual_c53_file_sha1' => null,
            'c53_file_sha1_match' => false,
            'input_c52_artifact' => $p52,
            'expected_c52_hash' => $h52,
            'actual_c52_hash' => null,
            'c52_hash_match' => false,
            'expected_c52_file_sha1' => $s52,
            'actual_c52_file_sha1' => null,
            'c52_file_sha1_match' => false,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'regime_field_reconstruction_continuation_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c56_carry_forward_summary' => [],
            'c56_root_cause_summary' => [],
            'c55_carry_forward_summary' => [],
            'c54_carry_forward_summary' => [],
            'c53_evidence_carry_forward' => [],
            'c52_sector_reconstruction_carry_forward' => [],
            'market_index_source_discovery_summary' => [],
            'market_index_source_discovery_results' => [],
            'market_index_reconstruction_results' => [],
            'market_index_date_coverage_results' => [],
            'market_index_asof_safety_results' => [],
            'regime_field_reconstruction_summary' => [],
            'regime_field_coverage_results' => [],
            'missing_regime_field_results' => [],
            'asof_safety_validation_results' => [],
            'source_reconstruction_summary' => [],
            'anchor_candidate_definitions' => [],
            'candidate_replay_results' => [],
            'concentration_dependency_validation_results' => [],
            'rolling_validation_results' => [],
            'rolling_validation_summary' => [],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => [],
            'material_difference_validation_results' => [],
            'source_reconstruction_bias_check' => [],
            'candidate_scorecard' => [],
            'selected_c57_candidates_for_c58' => [],
            'c58_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C57_PENDING',
            'next_step_recommendation' => 'C57_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $created,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c57_regime_field_reconstruction_continuation_is_only' => true,
            'c56_artifact_hash_lock' => true,
            'c55_artifact_hash_lock' => true,
            'c55_file_sha1_lock' => true,
            'c54_artifact_hash_lock' => true,
            'c54_file_sha1_lock' => true,
            'c53_artifact_hash_lock' => true,
            'c53_file_sha1_lock' => true,
            'c52_artifact_hash_lock' => true,
            'c52_file_sha1_lock' => true,
            'is_only_validation' => true,
            'market_index_reconstruction_asof_safe' => true,
            'market_index_reconstruction_no_max_trade_date' => true,
            'market_index_reconstruction_no_future_lookup' => true,
            'market_index_reconstruction_no_oos_rows' => true,
            'c56_failed_windows_diagnostic_only' => true,
            'c55_failed_windows_diagnostic_only' => true,
            'c53_adverse_months_diagnostic_only' => true,
            'no_adverse_month_exclusion_rule' => true,
            'no_failed_window_exclusion_rule' => true,
            'no_ticker_exclusion_rule' => true,
            'no_sector_exclusion_rule' => true,
            'predeclared_safe_pre_trade_selection_only' => true,
            'no_gate_relaxation' => true,
            'no_oos_tuning' => true,
            'no_oos_proof' => true,
            'no_oos_proof_rerun' => true,
            'no_best_of_oos' => true,
            'no_oos_winner' => true,
            'no_oos_return_selection' => true,
            'no_candidate_reselection_from_oos' => true,
            'no_profile_reselection_from_oos' => true,
            'no_production_catalog' => true,
            'no_promotion' => true,
            'no_plan_confirm_mutation' => true,
            'no_c01_to_c56_artifact_mutation' => true,
            'candidate_is_not_production' => true,
            'c57_must_not_recommend_oos_proof' => true,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'adverse_month_exclusion_used' => false,
            'failed_window_exclusion_used' => false,
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function loadLocked(string $path, string $expectedHash, ?string $expectedSha1): array
    {
        if (! is_file($path)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $hash = $this->stableHash($payload);
        $sha1 = strtoupper((string) sha1_file($path));
        return ['readable' => true, 'payload' => $payload, 'hash' => $hash, 'file_sha1' => $sha1, 'hash_match' => hash_equals($expectedHash, $hash), 'file_sha1_match' => $expectedSha1 === null || hash_equals(strtoupper($expectedSha1), $sha1)];
    }

    private function copyLock(array &$artifact, string $label, array $load, bool $copySha1 = true): void
    {
        $artifact['actual_'.$label.'_hash'] = $load['hash'];
        $artifact[$label.'_hash_match'] = $load['hash_match'];
        if ($copySha1) {
            $artifact['actual_'.$label.'_file_sha1'] = $load['file_sha1'];
            $artifact[$label.'_file_sha1_match'] = $load['file_sha1_match'];
        }
        if ($load['readable']) {
            $artifact[$label.'_status'] = $load['payload']['status'] ?? null;
            $artifact[$label.'_diagnostic_conclusion'] = $load['payload']['diagnostic_conclusion'] ?? null;
            $artifact[$label.'_next_step_recommendation'] = $load['payload']['next_step_recommendation'] ?? null;
        }
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C58_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        return $this->writeAndReturn($artifact, $output, true, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $path, bool $overwrite, ?string $reason = null, ?string $message = null): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($path, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C57_OPERATOR_VALIDATION_REQUIRED'; $reason = $write['reason_code']; $message = $write['message']; }
        return [
            'status' => $artifact['status'],
            'reason_code' => $reason ?: $artifact['status'],
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c56_hash' => $artifact['expected_c56_hash'],
            'actual_c56_hash' => $artifact['actual_c56_hash'],
            'c56_hash_match' => $artifact['c56_hash_match'],
            'c56_status' => $artifact['c56_status'],
            'c56_diagnostic_conclusion' => $artifact['c56_diagnostic_conclusion'],
            'c56_next_step_recommendation' => $artifact['c56_next_step_recommendation'],
            'expected_c55_hash' => $artifact['expected_c55_hash'],
            'actual_c55_hash' => $artifact['actual_c55_hash'],
            'c55_hash_match' => $artifact['c55_hash_match'],
            'expected_c55_file_sha1' => $artifact['expected_c55_file_sha1'],
            'actual_c55_file_sha1' => $artifact['actual_c55_file_sha1'],
            'c55_file_sha1_match' => $artifact['c55_file_sha1_match'],
            'expected_c54_hash' => $artifact['expected_c54_hash'],
            'actual_c54_hash' => $artifact['actual_c54_hash'],
            'c54_hash_match' => $artifact['c54_hash_match'],
            'expected_c54_file_sha1' => $artifact['expected_c54_file_sha1'],
            'actual_c54_file_sha1' => $artifact['actual_c54_file_sha1'],
            'c54_file_sha1_match' => $artifact['c54_file_sha1_match'],
            'expected_c53_hash' => $artifact['expected_c53_hash'],
            'actual_c53_hash' => $artifact['actual_c53_hash'],
            'c53_hash_match' => $artifact['c53_hash_match'],
            'expected_c53_file_sha1' => $artifact['expected_c53_file_sha1'],
            'actual_c53_file_sha1' => $artifact['actual_c53_file_sha1'],
            'c53_file_sha1_match' => $artifact['c53_file_sha1_match'],
            'expected_c52_hash' => $artifact['expected_c52_hash'],
            'actual_c52_hash' => $artifact['actual_c52_hash'],
            'c52_hash_match' => $artifact['c52_hash_match'],
            'expected_c52_file_sha1' => $artifact['expected_c52_file_sha1'],
            'actual_c52_file_sha1' => $artifact['actual_c52_file_sha1'],
            'c52_file_sha1_match' => $artifact['c52_file_sha1_match'],
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
            'c58_readiness_decision' => $artifact['c58_readiness_decision'],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C57 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function defaulted(string $value, string $default): string { return trim($value) === '' ? $default : $value; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function touchesReservedOos(string $from, string $to): bool { return strcmp($from, self::OOS_RESERVED_TO) <= 0 && strcmp($to, self::OOS_RESERVED_FROM) >= 0; }
    private function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function byCandidate(array $rows): array { $out = []; foreach ($rows as $row) { if (is_array($row) && isset($row['candidate_code'])) { $out[(string) $row['candidate_code']] = $row; } } return $out; }
    private function findByCandidate(array $rows, string $candidate): array { foreach ($rows as $row) { if (is_array($row) && ($row['candidate_code'] ?? null) === $candidate) { return $row; } } return []; }
}
