<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestStrategyService
{
    public const DEFAULT_PARAMSET = [
        'policy_code' => 'WS',
        'policy_version' => 'WS_EOD_RUNTIME',
        'schema_version' => 'PARAMSET_JSON',
        'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
        'eval' => [
            'min_trades_oos' => [
                'value' => 40,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical OOS minimum trade-count floor from the locked OOS contract.',
                'change_triggers' => ['OOS window changes', 'Trade definition changes'],
            ],
            'min_trades' => [
                'value' => 120,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical in-sample minimum trade-count floor from the locked metric-sufficiency contract.',
                'change_triggers' => ['Backtest window changes', 'Trade definition changes'],
            ],
            'min_days_covered' => [
                'value' => 0,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Zero is a documented dynamic sentinel; runtime resolves it to ceil(70% of trading days in the evaluation window).',
                'change_triggers' => ['Coverage definition changes', 'Backtest window changes'],
            ],
            'min_p25_ret_net_top' => [
                'value' => -0.03,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical bootstrap downside floor for TOP picks.',
                'change_triggers' => ['Volatility regime changes', 'Risk tolerance changes'],
            ],
            'min_month_win_rate_min' => [
                'value' => 0.45,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical bootstrap monthly win-rate stability floor.',
                'change_triggers' => ['Evaluation period definition changes'],
            ],
            'min_month_avg_ret_net_min' => [
                'value' => -0.01,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical bootstrap monthly average-return stability floor.',
                'change_triggers' => ['Evaluation period definition changes', 'Risk tolerance changes'],
            ],
        ],
        'risk' => [
            'stop_atr_mult' => 1.5,
            'min_rr' => 1.5,
        ],
        'backtest' => [
            'engine_mode' => 'PLAN_RECOMMENDATION_REPLAY_FOUNDATION',
            'replay_mode' => 'EXPLICIT_TRADE_DATE_WINDOW',
            'entry_model' => 'D_PLUS_1_OPEN_DOCUMENTED',
            'exit_model' => 'WEEKLY_SWING_MAX_5_TRADING_DAYS_DOCUMENTED',
            'pricing_model' => 'PUBLISHED_EOD_OHLCV_REQUIRED_AT_RUNTIME',
            'fee_model' => 'IDR_FIXED',
            'fee_buy_idr' => 2500.0,
            'fee_sell_idr' => 2500.0,
            'notional_idr' => 10000000.0,
            'lot_size' => 100,
            'slippage_entry_pct' => 0.0,
            'slippage_exit_pct' => 0.0,
            'holding_days' => 5,
            'tradable_bar_rule' => 'POSITIVE_VOLUME_REQUIRED',
            'min_tradable_volume' => 1,
            'source_price_mode' => 'RAW_TRADABLE_OHLC_REQUIRED',
            'gap_fill_rule' => 'OPEN_IF_GAP_THROUGH_TRIGGER',
            'price_fraction_rule' => 'IDX_EQUITY_PRICE_BANDS',
            'price_fraction_reference' => 'THEORETICAL_LEVEL',
            'price_normalization_rule' => 'CONSERVATIVE_STOP_FLOOR_TARGET_CEIL',
            'sort_keys' => [
                'trade_date_asc',
                'recommendation_rank_asc',
                'plan_rank_asc',
                'ticker_id_asc',
            ],
        ],
    ];

    private WatchlistPlanGroupingService $planGroupingService;
    private WatchlistRecommendationService $recommendationService;
    private WatchlistConfirmOverlayService $confirmOverlayService;

    public function __construct(
        WatchlistPlanGroupingService $planGroupingService = null,
        WatchlistRecommendationService $recommendationService = null,
        WatchlistConfirmOverlayService $confirmOverlayService = null
    ) {
        $this->planGroupingService = $planGroupingService ?: new WatchlistPlanGroupingService();
        $this->recommendationService = $recommendationService ?: new WatchlistRecommendationService($this->planGroupingService);
        $this->confirmOverlayService = $confirmOverlayService
            ?: new WatchlistConfirmOverlayService($this->planGroupingService, $this->recommendationService);
    }

    public function backtestForReplayWindow(
        array $tradeDates,
        array $confirmInputsByTradeDate = [],
        array $paramset = [],
        array $capitalInput = []
    ): array {
        $resolvedParamset = $this->resolveParamset($paramset);
        $replayDates = $this->normalizeTradeDates($tradeDates);
        $payload = $this->basePayload($replayDates, $resolvedParamset);
        $this->initializeOfficialEvidenceSpool($payload, $resolvedParamset);

        if ($replayDates === []) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_BACKTEST_REPLAY_WINDOW_EMPTY';
            $payload['diagnostics'][] = $this->diagnosticItem(null, 'WATCHLIST_BACKTEST_REPLAY_WINDOW_EMPTY', [
                'message' => 'Explicit replay window contains no valid trade date.',
            ]);

            return $payload;
        }

        $fatalNoLookahead = false;
        $daysEvaluated = 0;

        foreach ($replayDates as $tradeDate) {
            $planOutput = $this->planGroupingService->groupForTradeDate($tradeDate, $resolvedParamset);
            $recommendationOutput = $this->recommendationService->recommendFromPlanOutput($planOutput, $resolvedParamset, $capitalInput);
            $confirmInputs = $confirmInputsByTradeDate[$tradeDate] ?? [];
            $confirmOutput = $this->confirmOverlayService->confirmFromPlanAndRecommendationOutput(
                $planOutput,
                $recommendationOutput,
                is_array($confirmInputs) ? $confirmInputs : []
            );

            $sourceReadiness = $this->validateSourceReadiness($tradeDate, $planOutput, $recommendationOutput, $confirmOutput);
            if ($sourceReadiness !== []) {
                foreach ($sourceReadiness as $diagnostic) {
                    $payload['diagnostics'][] = $diagnostic;
                    if (($diagnostic['fatal'] ?? false) === true) {
                        $fatalNoLookahead = true;
                    }
                }

                if ($fatalNoLookahead) {
                    continue;
                }
            }

            if (! ($planOutput['is_ready'] ?? $planOutput['ready'] ?? false)) {
                $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_PLAN_NOT_READY', [
                    'source_reason_code' => $planOutput['reason_code'] ?? null,
                ]);
                continue;
            }

            if (! ($recommendationOutput['is_ready'] ?? $recommendationOutput['ready'] ?? false)) {
                $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_RECOMMENDATION_NOT_READY', [
                    'source_reason_code' => $recommendationOutput['reason_code'] ?? null,
                ]);
                continue;
            }

            if (! ($confirmOutput['is_ready'] ?? $confirmOutput['ready'] ?? false)) {
                $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_CONFIRM_NOT_READY', [
                    'source_reason_code' => $confirmOutput['reason_code'] ?? null,
                ]);
                continue;
            }

            $daysEvaluated++;
            $this->appendDailyReplay($payload, $tradeDate, $planOutput, $recommendationOutput, $confirmOutput, $resolvedParamset);
        }

        $this->finalizeTickRiskGuardAudit($payload, $resolvedParamset);

        $payload['items'] = $this->sortItems($payload['items']);
        $payload['trades'] = $this->sortTrades($payload['trades']);
        $payload['evaluations'] = $this->sortEvaluations($payload['evaluations']);
        if (($payload['official_evidence']['storage_mode'] ?? 'IN_MEMORY') === 'JSONL_SPOOL') {
            $this->finalizeOfficialEvidenceSpool($payload);
        } else {
            $payload['official_evidence']['universe'] = $this->sortOfficialUniverse($payload['official_evidence']['universe']);
            $payload['official_evidence']['cutoffs'] = $this->sortOfficialCutoffs($payload['official_evidence']['cutoffs']);
        }
        $payload['summary'] = $this->buildSummary($payload, $daysEvaluated, $fatalNoLookahead);
        $payload['ready'] = ! $fatalNoLookahead && $daysEvaluated > 0;
        $payload['is_ready'] = $payload['ready'];
        $payload['reason_code'] = $this->resolveReasonCode($payload, $fatalNoLookahead, $daysEvaluated);
        $payload['backtest_reason_code'] = $payload['reason_code'];
        $payload['meta']['backtest_reason_code'] = $payload['reason_code'];

        return $payload;
    }

    public static function defaultParamset(): array
    {
        return self::DEFAULT_PARAMSET;
    }

    private function appendDailyReplay(
        array &$payload,
        string $tradeDate,
        array $planOutput,
        array $recommendationOutput,
        array $confirmOutput,
        array $paramset
    ): void {
        $this->collectOfficialEvidence($payload, $tradeDate, $planOutput, $paramset);
        $recommendationIndex = $this->indexByTickerIdentity($recommendationOutput['items'] ?? []);
        $confirmIndex = $this->indexByTickerIdentity($confirmOutput['items'] ?? []);
        $recommendedCountForDate = 0;

        if (! $this->compactReplayItems($paramset)) {
            foreach (($confirmOutput['items'] ?? []) as $confirmItem) {
                $recommendationItem = $this->firstItemForKeys($recommendationIndex, $this->identityKeys($confirmItem));
                $isRecommended = (bool) (($recommendationItem['recommended_flag'] ?? false) === true);

                $payload['items'][] = $this->replayItem($tradeDate, $confirmItem, $recommendationItem, $isRecommended);
            }
        }

        foreach (($recommendationOutput['items'] ?? []) as $recommendationItem) {
            if (($recommendationItem['recommended_flag'] ?? false) !== true) {
                continue;
            }

            $confirmItem = $this->firstItemForKeys($confirmIndex, $this->identityKeys($recommendationItem));
            $trade = $this->tradeCandidate($tradeDate, $recommendationItem, $confirmItem, $planOutput, $recommendationOutput, $paramset);
            $payload['trades'][] = $trade;
            $payload['evaluations'][] = $this->evaluationItem($tradeDate, $trade, $paramset);
            $recommendedCountForDate++;
        }

        foreach (($confirmOutput['excluded'] ?? []) as $excluded) {
            $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_CONFIRM_EVIDENCE_EXCLUDED', [
                'ticker_id' => $excluded['ticker_id'] ?? null,
                'ticker' => $excluded['ticker'] ?? $excluded['ticker_code'] ?? null,
                'reason_codes' => $excluded['reason_codes'] ?? [],
                'confirm_state' => $excluded['confirm_state'] ?? null,
                'active_trade_evaluation' => false,
            ]);
        }

        if ($recommendedCountForDate === 0) {
            $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID', [
                'reason_codes' => ['WS_REC_EMPTY_SET'],
                'active_trade_evaluation' => false,
            ]);
        }
    }


    private function collectOfficialEvidence(array &$payload, string $tradeDate, array $planOutput, array $paramset): void
    {
        $manifest = is_array($planOutput['cutoff_manifest'] ?? null) ? $planOutput['cutoff_manifest'] : [];
        $cutoff = [
            'asof_eod_date' => $tradeDate,
            'top_cutoff_score' => $this->floatOrNull($manifest['top_picks_min_score_total'] ?? null),
            'secondary_cutoff_score' => $this->floatOrNull($manifest['secondary_min_score_total'] ?? null),
            'cutoff_mode' => $manifest['mode'] ?? null,
            'score_count' => isset($manifest['score_count']) ? (int) $manifest['score_count'] : null,
            'score_payload_hash' => $manifest['score_payload_hash'] ?? null,
            'source_publication_id' => $planOutput['publication_id'] ?? null,
            'source_publication_version' => $planOutput['publication_version'] ?? null,
            'source_run_id' => $planOutput['run_id'] ?? null,
        ];

        $seen = [];
        $dailyUniverse = [];
        $evidenceGroups = is_array($planOutput['groups'] ?? null) ? $planOutput['groups'] : [];
        $evidenceGroups['AVOID_EXCLUDED'] = is_array($planOutput['excluded'] ?? null) ? $planOutput['excluded'] : [];
        foreach ($evidenceGroups as $group => $items) {
            foreach (is_array($items) ? $items : [] as $item) {
                $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
                if ($tickerId === null || isset($seen[$tickerId])) {
                    continue;
                }
                $seen[$tickerId] = true;
                $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
                $reasonCodes = array_values(array_unique(array_map('strval', $item['reason_codes'] ?? [])));
                $evidenceRow = [
                    'asof_eod_date' => $tradeDate,
                    'ticker_id' => $tickerId,
                    'ticker_code' => $this->tickerCode($item),
                    'required_ok' => (bool) ($item['required_ok'] ?? $item['required_fields_available'] ?? true),
                    'guard_ok' => (bool) ($item['guard_ok'] ?? $item['eligible_score'] ?? ! in_array((string) $group, ['AVOID', 'AVOID_EXCLUDED'], true)),
                    'eligible_ok' => (bool) ($item['eligible_ok'] ?? $item['eligible_score'] ?? ! in_array((string) $group, ['AVOID', 'AVOID_EXCLUDED'], true)),
                    'dv20_idr' => $metrics['dv20_idr'] ?? $item['dv20_idr'] ?? null,
                    'atr14_pct' => $metrics['atr14_pct'] ?? $item['atr14_pct'] ?? null,
                    'vol_ratio' => $metrics['vol_ratio'] ?? $item['vol_ratio'] ?? null,
                    'signal_close_price' => $metrics['signal_close_price'] ?? $item['signal_close_price'] ?? $item['close_price'] ?? null,
                    'signal_tick_risk_expansion_pct' => $metrics['signal_tick_risk_expansion_pct'] ?? $item['signal_tick_risk_expansion_pct'] ?? null,
                    'market_index_roc20' => $metrics['market_index_roc20'] ?? null,
                    'market_index_ma20_slope_pct' => $metrics['market_index_ma20_slope_pct'] ?? null,
                    'market_regime' => $metrics['market_regime'] ?? $item['market_regime'] ?? null,
                    'research_selection_rule_code' => $paramset['research_selection']['rule_code'] ?? null,
                    'reason_code' => $reasonCodes[0] ?? null,
                    'reason_codes' => $reasonCodes,
                    'plan_group' => (string) $group,
                    'score_total' => $this->floatOrNull($item['score_total'] ?? null),
                    'source_publication_id' => $planOutput['publication_id'] ?? null,
                    'source_publication_version' => $planOutput['publication_version'] ?? null,
                    'source_run_id' => $planOutput['run_id'] ?? null,
                ];
                $this->auditTickRiskEvidenceRow($payload, $evidenceRow, $item, (string) $group, $paramset);
                $dailyUniverse[] = $evidenceRow;
            }
        }
        usort($dailyUniverse, function (array $left, array $right): int {
            return ((int) ($left['ticker_id'] ?? 0)) <=> ((int) ($right['ticker_id'] ?? 0));
        });

        if (($payload['official_evidence']['storage_mode'] ?? 'IN_MEMORY') === 'JSONL_SPOOL') {
            $this->appendJsonLines((string) $payload['official_evidence']['cutoffs_spool_path'], [$cutoff]);
            $this->appendJsonLines((string) $payload['official_evidence']['universe_spool_path'], $dailyUniverse);
            $payload['official_evidence']['cutoff_count']++;
            $payload['official_evidence']['universe_count'] += count($dailyUniverse);
            return;
        }

        $payload['official_evidence']['cutoffs'][] = $cutoff;
        foreach ($dailyUniverse as $row) {
            $payload['official_evidence']['universe'][] = $row;
        }
    }

    private function initialTickRiskGuardAudit(array $paramset): array
    {
        $threshold = $this->floatOrNull($paramset['risk']['max_signal_tick_risk_expansion_pct'] ?? null);

        return [
            'contract' => 'C171_C01_DECISION_TIME_TICK_RISK_GUARD_EXECUTION_AND_EVIDENCE_PROPAGATION_V3',
            'enabled' => $threshold !== null,
            'threshold' => $threshold,
            'decision_time_fields_only' => true,
            'full_reason_codes_audited' => true,
            'scored_candidate_count' => 0,
            'metric_propagated_to_scored_candidates_count' => 0,
            'metric_missing_on_scored_candidates_count' => 0,
            'official_pick_count' => 0,
            'metric_propagated_to_official_picks_count' => 0,
            'metric_missing_on_official_picks_count' => 0,
            'above_threshold_before_guard_count' => 0,
            'above_threshold_without_tick_reason_count' => 0,
            'tick_only_rejected_count' => 0,
            'tick_multi_reason_rejected_count' => 0,
            'eligible_above_threshold_after_guard_count' => 0,
            'tick_risk_metric_propagated_to_scored_candidates' => $threshold === null,
            'tick_risk_metric_propagated_to_official_picks' => $threshold === null,
            'threshold_enforced_for_all_evidence_rows' => $threshold === null,
            'status' => $threshold === null ? 'NOT_APPLICABLE' : 'NOT_EVALUATED',
            'pass' => $threshold === null,
        ];
    }

    private function auditTickRiskEvidenceRow(
        array &$payload,
        array $row,
        array $item,
        string $group,
        array $paramset
    ): void {
        $audit = &$payload['official_evidence']['tick_risk_guard_audit'];
        if (! is_array($audit) || ! ($audit['enabled'] ?? false)) {
            return;
        }

        $threshold = $this->floatOrNull($paramset['risk']['max_signal_tick_risk_expansion_pct'] ?? null);
        if ($threshold === null) {
            throw new \RuntimeException('WS_C171_TICK_RISK_GUARD_AUDIT_THRESHOLD_MISSING');
        }

        $close = $this->floatOrNull($row['signal_close_price'] ?? null);
        $tickRisk = $this->floatOrNull($row['signal_tick_risk_expansion_pct'] ?? null);
        $hasMetric = $close !== null && $tickRisk !== null;
        $isScoredCandidate = (bool) ($item['eligible_score'] ?? false);
        $isOfficialPick = $group === 'TOP_PICKS';
        $eligible = (bool) ($row['eligible_ok'] ?? false);
        $reasonCodes = $this->uniqueReasonCodes(is_array($row['reason_codes'] ?? null) ? $row['reason_codes'] : []);
        $hasTickReason = in_array('WS_TICK_RISK_HIGH', $reasonCodes, true);

        if ($isScoredCandidate) {
            $audit['scored_candidate_count']++;
            if ($hasMetric) {
                $audit['metric_propagated_to_scored_candidates_count']++;
            } else {
                $audit['metric_missing_on_scored_candidates_count']++;
            }
        }
        if ($isOfficialPick) {
            $audit['official_pick_count']++;
            if ($hasMetric) {
                $audit['metric_propagated_to_official_picks_count']++;
            } else {
                $audit['metric_missing_on_official_picks_count']++;
            }
        }

        if ($tickRisk !== null && $tickRisk > $threshold) {
            $audit['above_threshold_before_guard_count']++;
            if (! $hasTickReason) {
                $audit['above_threshold_without_tick_reason_count']++;
            }
            if ($eligible) {
                $audit['eligible_above_threshold_after_guard_count']++;
            }
        }

        if ($hasTickReason) {
            $otherFailureReasons = array_values(array_filter(
                $reasonCodes,
                function (string $reason): bool {
                    return $this->isIndependentTickRiskFailureReason($reason);
                }
            ));
            if ($otherFailureReasons === []) {
                $audit['tick_only_rejected_count']++;
            } else {
                $audit['tick_multi_reason_rejected_count']++;
            }
        }
    }

    private function finalizeTickRiskGuardAudit(array &$payload, array $paramset): void
    {
        $audit = &$payload['official_evidence']['tick_risk_guard_audit'];
        if (! is_array($audit)) {
            throw new \RuntimeException('WS_C171_TICK_RISK_GUARD_AUDIT_MISSING');
        }
        if (! ($audit['enabled'] ?? false)) {
            $audit['status'] = 'NOT_APPLICABLE';
            $audit['pass'] = true;
            return;
        }

        $threshold = $this->floatOrNull($paramset['risk']['max_signal_tick_risk_expansion_pct'] ?? null);
        if ($threshold === null || abs((float) ($audit['threshold'] ?? -1) - $threshold) > 0.0000000001) {
            throw new \RuntimeException('WS_C171_TICK_RISK_GUARD_AUDIT_THRESHOLD_IDENTITY_MISMATCH');
        }

        $audit['tick_risk_metric_propagated_to_scored_candidates'] =
            (int) $audit['metric_missing_on_scored_candidates_count'] === 0
            && (int) $audit['metric_propagated_to_scored_candidates_count'] === (int) $audit['scored_candidate_count'];
        $audit['tick_risk_metric_propagated_to_official_picks'] =
            (int) $audit['metric_missing_on_official_picks_count'] === 0
            && (int) $audit['metric_propagated_to_official_picks_count'] === (int) $audit['official_pick_count'];
        $audit['threshold_enforced_for_all_evidence_rows'] =
            (int) $audit['above_threshold_without_tick_reason_count'] === 0
            && (int) $audit['eligible_above_threshold_after_guard_count'] === 0;
        $audit['pass'] = (bool) $audit['tick_risk_metric_propagated_to_scored_candidates']
            && (bool) $audit['tick_risk_metric_propagated_to_official_picks']
            && (bool) $audit['threshold_enforced_for_all_evidence_rows'];
        $audit['status'] = $audit['pass'] ? 'PASS' : 'FAIL';

        if (! $audit['pass']) {
            throw new \RuntimeException(
                'WS_C171_TICK_RISK_GUARD_EXECUTION_OR_EVIDENCE_PROPAGATION_FAILED: '.json_encode($audit)
            );
        }
    }

    private function isIndependentTickRiskFailureReason(string $reason): bool
    {
        if ($reason === 'WS_TICK_RISK_HIGH') {
            return false;
        }
        if (in_array($reason, $this->tickRiskFailureReasonCodes(), true)) {
            return true;
        }

        return substr($reason, -5) === '_FAIL';
    }

    private function tickRiskFailureReasonCodes(): array
    {
        return [
            'WS_DATA_MISSING',
            'WS_LIQ_FAIL',
            'WS_LIQ_HIGH',
            'WS_ATR_LOW',
            'WS_ATR_HIGH',
            'WS_VOLR_FAIL',
            'WS_VOLR_HIGH',
            'WS_TICK_RISK_HIGH',
        ];
    }

    private function initializeOfficialEvidenceSpool(array &$payload, array $paramset): void
    {
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        if (($backtest['official_evidence_storage_mode'] ?? null) !== 'JSONL_SPOOL') {
            return;
        }
        $directory = rtrim((string) ($backtest['official_evidence_spool_directory'] ?? ''), DIRECTORY_SEPARATOR);
        if ($directory === '') {
            throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_DIRECTORY_REQUIRED');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_DIRECTORY_CREATE_FAILED: '.$directory);
        }
        $runKey = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string) ($backtest['official_evidence_spool_run_key'] ?? 'c171'));
        $universePath = $directory.DIRECTORY_SEPARATOR.$runKey.'.universe.raw.jsonl';
        $cutoffsPath = $directory.DIRECTORY_SEPARATOR.$runKey.'.cutoffs.raw.jsonl';
        foreach ([$universePath, $cutoffsPath] as $path) {
            if (file_put_contents($path, '', LOCK_EX) === false) {
                throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_INITIALIZE_FAILED: '.$path);
            }
        }
        $tickRiskGuardAudit = $payload['official_evidence']['tick_risk_guard_audit']
            ?? $this->initialTickRiskGuardAudit($paramset);
        $payload['official_evidence'] = [
            'schema_version' => 'WS_OFFICIAL_IS_EVIDENCE_C171_V1',
            'storage_mode' => 'JSONL_SPOOL',
            'universe_spool_path' => $universePath,
            'cutoffs_spool_path' => $cutoffsPath,
            'universe_count' => 0,
            'cutoff_count' => 0,
            'finalized' => false,
            'tick_risk_guard_audit' => $tickRiskGuardAudit,
        ];
        $payload['summary']['official_evidence_storage_mode'] = 'JSONL_SPOOL';
        $payload['summary']['compact_replay_items'] = $this->compactReplayItems($paramset);
    }

    private function finalizeOfficialEvidenceSpool(array &$payload): void
    {
        foreach (['universe_spool_path', 'cutoffs_spool_path'] as $field) {
            $path = (string) ($payload['official_evidence'][$field] ?? '');
            if ($path === '' || ! is_file($path)) {
                throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_MISSING: '.$field);
            }
            $payload['official_evidence'][$field.'_sha1'] = sha1_file($path);
            $payload['official_evidence'][$field.'_bytes'] = filesize($path);
        }
        $payload['official_evidence']['finalized'] = true;
    }

    private function appendJsonLines(string $path, array $rows): void
    {
        if ($rows === []) {
            return;
        }
        $buffer = '';
        foreach ($rows as $row) {
            $json = json_encode($row, JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_JSON_ENCODING_FAILED: '.json_last_error_msg());
            }
            $buffer .= $json.PHP_EOL;
        }
        if (file_put_contents($path, $buffer, FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException('WS_C171_OFFICIAL_EVIDENCE_SPOOL_APPEND_FAILED: '.$path);
        }
    }

    private function compactReplayItems(array $paramset): bool
    {
        return (bool) ($paramset['backtest']['compact_replay_items'] ?? false);
    }

    private function sortOfficialUniverse(array $rows): array
    {
        usort($rows, function (array $left, array $right): int {
            return strcmp((string) ($left['asof_eod_date'] ?? ''), (string) ($right['asof_eod_date'] ?? ''))
                ?: (((int) ($left['ticker_id'] ?? 0)) <=> ((int) ($right['ticker_id'] ?? 0)));
        });
        return $rows;
    }

    private function sortOfficialCutoffs(array $rows): array
    {
        usort($rows, function (array $left, array $right): int {
            return strcmp((string) ($left['asof_eod_date'] ?? ''), (string) ($right['asof_eod_date'] ?? ''));
        });
        return $rows;
    }

    private function replayItem(string $tradeDate, array $confirmItem, ?array $recommendationItem, bool $isRecommended): array
    {
        $reasonCodes = array_merge($confirmItem['reason_codes'] ?? [], $confirmItem['confirm_reason_codes'] ?? []);
        if ($isRecommended) {
            $reasonCodes[] = 'WS_REC_SELECTED';
        }

        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $this->intOrNull($confirmItem['ticker_id'] ?? null),
            'ticker' => $this->tickerCode($confirmItem),
            'plan_group' => $confirmItem['plan_group'] ?? $confirmItem['group_semantic'] ?? null,
            'plan_rank' => $this->intOrNull($confirmItem['plan_rank'] ?? null),
            'recommendation_rank' => $this->intOrNull($recommendationItem['recommendation_rank'] ?? null),
            'recommended_flag' => $isRecommended,
            'confirm_state' => $confirmItem['confirm_state'] ?? 'NO_DATA',
            'confirm_overlay_applied' => (bool) (($confirmItem['confirm_overlay_applied'] ?? false) === true),
            'active_trade_evaluation' => $isRecommended,
            'reason_codes' => $this->uniqueReasonCodes($reasonCodes),
            'source_snapshot' => [
                'plan_binding_preserved' => true,
                'recommendation_membership_preserved' => true,
                'confirm_overlay_diagnostic_only' => true,
            ],
        ];
    }

    private function tradeCandidate(
        string $tradeDate,
        array $recommendationItem,
        ?array $confirmItem,
        array $planOutput,
        array $recommendationOutput,
        array $paramset
    ): array {
        $planReference = $recommendationItem['plan_reference'] ?? [];
        $planItem = $this->planItemForRecommendation($planOutput, $recommendationItem) ?? [];
        $scoreMetrics = $this->diagnosticScoreMetrics($planItem, is_array($planReference) ? $planReference : []);
        $scoreComponents = $this->diagnosticScoreComponents($planItem);
        $factorBreakdown = $this->diagnosticFactorBreakdown($planItem);
        $reasonCodes = $recommendationItem['reason_codes'] ?? [];
        $reasonCodes[] = 'WS_REC_SELECTED';

        if ($confirmItem !== null && ($confirmItem['confirm_state'] ?? null) === 'CONFIRMED') {
            $reasonCodes[] = 'WS_CONFIRM_APPLIED';
        }

        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $this->intOrNull($recommendationItem['ticker_id'] ?? null),
            'ticker' => $this->tickerCode($recommendationItem),
            'bucket_code' => $planReference['plan_group'] ?? $recommendationItem['plan_group_semantic'] ?? null,
            'plan_rank' => $this->intOrNull($recommendationItem['plan_rank'] ?? $planReference['plan_rank'] ?? null),
            'recommendation_rank' => $this->intOrNull($recommendationItem['recommendation_rank'] ?? null),
            'recommendation_score' => $this->floatOrNull($recommendationItem['recommendation_score'] ?? null),
            'score_total' => $this->floatOrNull($planItem['score_total'] ?? ($planReference['score_total'] ?? null)),
            'score_components' => $scoreComponents,
            'score_metrics' => $scoreMetrics,
            'factor_breakdown' => $factorBreakdown,
            'score_momentum' => $scoreComponents['score_momentum'] ?? null,
            'score_breakout' => $scoreComponents['score_breakout'] ?? null,
            'score_volume' => $scoreComponents['score_volume'] ?? null,
            'score_risk' => $scoreComponents['score_risk'] ?? null,
            'dv20_idr' => $scoreMetrics['dv20_idr'] ?? null,
            'vol_ratio' => $scoreMetrics['vol_ratio'] ?? null,
            'roc20' => $scoreMetrics['roc20'] ?? null,
            'close_to_hh20_pct' => $scoreMetrics['close_to_hh20_pct'] ?? null,
            'market_index_roc20' => $scoreMetrics['market_index_roc20'] ?? null,
            'market_index_ma20_slope_pct' => $scoreMetrics['market_index_ma20_slope_pct'] ?? null,
            'market_regime' => $scoreMetrics['market_regime'] ?? null,
            'research_selection_rule_code' => $paramset['research_selection']['rule_code'] ?? null,
            'sector_code' => $scoreMetrics['sector_code'] ?? null,
            'atr14_pct' => $this->floatOrNull($planReference['atr14_pct'] ?? ($scoreMetrics['atr14_pct'] ?? null)),
            'stop_price' => $this->floatOrNull($planReference['stop_price'] ?? null),
            'target_price' => $this->floatOrNull($planReference['target_price'] ?? null),
            'stop_atr_mult' => $this->floatOrNull($paramset['risk']['stop_atr_mult'] ?? null),
            'min_rr' => $this->floatOrNull($paramset['risk']['min_rr'] ?? null),
            'confirm_state' => $confirmItem['confirm_state'] ?? 'NO_DATA',
            'trade_state' => 'EVALUATION_CANDIDATE',
            'entry_model' => $paramset['backtest']['entry_model'],
            'exit_model' => $paramset['backtest']['exit_model'],
            'pricing_model' => $paramset['backtest']['pricing_model'],
            'source_price_mode' => $paramset['backtest']['source_price_mode'],
            'gap_fill_rule' => $paramset['backtest']['gap_fill_rule'],
            'price_fraction_rule' => $paramset['backtest']['price_fraction_rule'],
            'price_fraction_reference' => $paramset['backtest']['price_fraction_reference'],
            'price_normalization_rule' => $paramset['backtest']['price_normalization_rule'],
            'reason_codes' => $this->uniqueReasonCodes($reasonCodes),
            'source_reference' => [
                'plan_hash' => $planOutput['plan_hash'] ?? null,
                'recommendation_hash' => $recommendationOutput['recommendation_hash'] ?? null,
                'publication_id' => $planOutput['publication_id'] ?? $recommendationOutput['meta']['publication_id'] ?? null,
                'publication_version' => $planOutput['publication_version'] ?? $recommendationOutput['meta']['publication_version'] ?? null,
                'run_id' => $planOutput['run_id'] ?? $recommendationOutput['meta']['run_id'] ?? null,
                'diagnostic_feature_source' => $planItem === [] ? 'PLAN_REFERENCE_FALLBACK' : 'PLAN_GROUP_ITEM',
            ],
            'contract_flags' => [
                'from_recommendation_layer' => true,
                'confirm_does_not_create_recommendation' => true,
                'no_lookahead_price_used' => true,
                'not_broker_surface' => true,
            ],
        ];
    }

    private function evaluationItem(string $tradeDate, array $trade, array $paramset): array
    {
        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $trade['ticker_id'],
            'ticker' => $trade['ticker'],
            'bucket_code' => $trade['bucket_code'],
            'evaluation_state' => 'FOUNDATION_REPLAY_ONLY',
            'metrics_ready' => false,
            'eval_model' => $this->evalModel($paramset),
            'ret_net' => null,
            'is_win' => null,
            'reason_codes' => ['WS_BT_EVAL_METRICS_MISSING'],
            'explainability' => [
                'selected_by_recommendation' => true,
                'confirm_state_observed' => $trade['confirm_state'],
                'price_series_consumed' => false,
                'metrics_deferred_to_runtime_artifact' => true,
            ],
        ];
    }

    private function basePayload(array $replayDates, array $paramset): array
    {
        $sourceContract = [
            'consumer' => 'WatchlistBacktestStrategyService',
            'upstream' => [
                'WatchlistPlanGroupingService',
                'WatchlistRecommendationService',
                'WatchlistConfirmOverlayService',
            ],
            'plan_binding_source' => 'WatchlistPlanGroupingService',
            'recommendation_source' => 'WatchlistRecommendationService',
            'confirm_overlay_source' => 'WatchlistConfirmOverlayService',
            'recommendation_layer_only' => true,
            'confirm_overlay_diagnostic_only' => true,
            'no_raw_market_data' => true,
            'no_latest_shortcut' => true,
            'no_max_trade_date_shortcut' => true,
            'no_plan_mutation' => true,
            'no_recommendation_mutation' => true,
            'no_confirm_mutation' => true,
            'no_portfolio_state' => true,
            'no_broker_surface' => true,
            'no_order_automation' => true,
        ];

        $backtestContract = [
            'foundation_only' => true,
            'no_lookahead' => true,
            'deterministic_replay' => true,
            'publication_aware_replay' => true,
            'explicit_replay_window_only' => true,
            'same_trade_date_source_alignment' => true,
            'entry_exit_assumptions_documented' => true,
            'eod_only' => true,
            'price_series_consumed' => false,
            'metrics_ready' => false,
            'not_production_ready' => true,
        ];

        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_BACKTEST_NOT_EVALUATED',
            'backtest_reason_code' => 'WATCHLIST_BACKTEST_NOT_EVALUATED',
            'meta' => [
                'strategy_code' => $paramset['policy_code'],
                'policy_code' => $paramset['policy_code'],
                'policy_version' => $paramset['policy_version'],
                'paramset_code' => $paramset['paramset_code'],
                'engine' => 'WatchlistBacktestStrategyService',
                'backtest_reason_code' => 'WATCHLIST_BACKTEST_NOT_EVALUATED',
                'source_contract' => $sourceContract,
                'backtest_contract' => $backtestContract,
                'paramset_snapshot' => $paramset,
                'replay_window' => $this->replayWindow($replayDates),
            ],
            'source_contract' => $sourceContract,
            'backtest_contract' => $backtestContract,
            'paramset_snapshot' => $paramset,
            'replay_window' => $this->replayWindow($replayDates),
            'items' => [],
            'trades' => [],
            'evaluations' => [],
            'official_evidence' => [
                'schema_version' => 'WS_OFFICIAL_IS_EVIDENCE_C171_V1',
                'universe' => [],
                'cutoffs' => [],
                'tick_risk_guard_audit' => $this->initialTickRiskGuardAudit($paramset),
            ],
            'summary' => [
                'days_requested' => count($replayDates),
                'days_evaluated' => 0,
                'items_count' => 0,
                'picks_count' => 0,
                'evaluations_count' => 0,
                'empty_recommendation_days' => 0,
                'avg_ret_net_top' => null,
                'win_rate_top' => null,
                'metrics_ready' => false,
                'artifact_runtime_persistence' => false,
                'production_ready' => false,
                'reason_codes' => [],
            ],
            'diagnostics' => [],
            'artifact_manifest' => $this->artifactManifest(),
        ];
    }

    private function validateSourceReadiness(string $tradeDate, array $planOutput, array $recommendationOutput, array $confirmOutput): array
    {
        $diagnostics = [];
        foreach ([
            ['PLAN', $planOutput, $planOutput['trade_date'] ?? null, $planOutput['trade_date_effective'] ?? null],
            ['RECOMMENDATION', $recommendationOutput, $recommendationOutput['meta']['trade_date'] ?? null, $recommendationOutput['meta']['trade_date_effective'] ?? null],
            ['CONFIRM', $confirmOutput, $confirmOutput['trade_date'] ?? null, $confirmOutput['trade_date_effective'] ?? null],
        ] as $source) {
            [$sourceName, $output, $sourceTradeDate, $effectiveDate] = $source;

            if ($sourceTradeDate !== null && (string) $sourceTradeDate !== $tradeDate) {
                $diagnostics[] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_TRADE_DATE_MISMATCH', [
                    'source' => $sourceName,
                    'source_trade_date' => $sourceTradeDate,
                    'expected_trade_date' => $tradeDate,
                    'fatal' => true,
                ]);
            }

            if ($effectiveDate !== null && $this->isDateAfter((string) $effectiveDate, $tradeDate)) {
                $diagnostics[] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION', [
                    'source' => $sourceName,
                    'trade_date_effective' => $effectiveDate,
                    'expected_not_after' => $tradeDate,
                    'reason_codes' => ['WS_BT_OOS_PROOF_MISSING'],
                    'fatal' => true,
                ]);
            }
        }

        $planPublication = $planOutput['publication_id'] ?? null;
        $recommendationPublication = $recommendationOutput['meta']['publication_id'] ?? null;
        $confirmPublication = $confirmOutput['publication_id'] ?? null;

        if ($this->hasMismatch([$planPublication, $recommendationPublication, $confirmPublication])) {
            $diagnostics[] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_PUBLICATION_MISMATCH', [
                'publication_ids' => [$planPublication, $recommendationPublication, $confirmPublication],
                'fatal' => true,
            ]);
        }

        return $diagnostics;
    }

    private function buildSummary(array $payload, int $daysEvaluated, bool $fatalNoLookahead): array
    {
        $emptyRecommendationDays = 0;
        foreach ($payload['diagnostics'] as $diagnostic) {
            if (($diagnostic['reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID') {
                $emptyRecommendationDays++;
            }
        }

        $reasonCodes = [];
        if (count($payload['evaluations']) > 0) {
            $reasonCodes[] = 'WS_BT_EVAL_METRICS_MISSING';
        }
        if ($fatalNoLookahead) {
            $reasonCodes[] = 'WS_BT_OOS_PROOF_MISSING';
        }
        $reasonCodes[] = 'WS_BT_ARTIFACT_MISSING';

        return [
            'days_requested' => count($payload['replay_window']['trade_dates']),
            'days_evaluated' => $daysEvaluated,
            'items_count' => count($payload['items']),
            'picks_count' => count($payload['trades']),
            'evaluations_count' => count($payload['evaluations']),
            'empty_recommendation_days' => $emptyRecommendationDays,
            'avg_ret_net_top' => null,
            'win_rate_top' => null,
            'median_ret_net_top' => null,
            'p25_ret_net_top' => null,
            'p75_ret_net_top' => null,
            'min_ret_net_top' => null,
            'max_ret_net_top' => null,
            'month_win_rate_min' => null,
            'month_avg_ret_net_min' => null,
            'metrics_ready' => false,
            'artifact_runtime_persistence' => false,
            'production_ready' => false,
            'reason_codes' => $this->uniqueReasonCodes($reasonCodes),
        ];
    }

    private function resolveReasonCode(array $payload, bool $fatalNoLookahead, int $daysEvaluated): string
    {
        if ($fatalNoLookahead) {
            return 'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION';
        }

        if ($daysEvaluated === 0) {
            return 'WATCHLIST_BACKTEST_SOURCE_NOT_READY';
        }

        if (count($payload['trades']) === 0) {
            return 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION';
        }

        return 'WATCHLIST_BACKTEST_FOUNDATION_READY';
    }

    private function artifactManifest(): array
    {
        return [
            'official_backtest_tables' => [
                'watchlist_bt_param_grid',
                'watchlist_bt_eval',
                'watchlist_bt_picks_ws',
                'watchlist_bt_universe_ws',
                'watchlist_bt_cutoffs_ws',
                'watchlist_bt_oos_eval_ws',
            ],
            'production_proof_artifacts' => [
                'PLAN_UNIVERSE_SNAPSHOT_SCHEMA',
            ],
            'runtime_artifact_created' => false,
            'runtime_persistence_created' => false,
            'reason_codes' => ['WS_BT_ARTIFACT_MISSING'],
        ];
    }

    private function replayWindow(array $replayDates): array
    {
        return [
            'from_trade_date' => $replayDates[0] ?? null,
            'to_trade_date' => $replayDates === [] ? null : $replayDates[count($replayDates) - 1],
            'trade_dates' => $replayDates,
            'days_requested' => count($replayDates),
            'explicit_window' => true,
        ];
    }

    private function normalizeTradeDates(array $tradeDates): array
    {
        $normalized = [];
        foreach ($tradeDates as $tradeDate) {
            if (! is_scalar($tradeDate)) {
                continue;
            }

            $value = trim((string) $tradeDate);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                continue;
            }

            $normalized[$value] = $value;
        }

        sort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function resolveParamset(array $paramset): array
    {
        $defaults = self::DEFAULT_PARAMSET;
        $recommendationDefaults = WatchlistRecommendationService::defaultParamset();
        $backtestInput = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $recommendationInput = is_array($paramset['recommendation'] ?? null) ? $paramset['recommendation'] : [];
        $riskInput = is_array($paramset['risk'] ?? null) ? $paramset['risk'] : [];
        $evalInput = is_array($paramset['eval'] ?? null) ? $paramset['eval'] : [];

        $resolved = $paramset;
        $resolved['policy_code'] = (string) ($paramset['policy_code'] ?? $defaults['policy_code']);
        $resolved['policy_version'] = (string) ($paramset['policy_version'] ?? $defaults['policy_version']);
        $resolved['schema_version'] = (string) ($paramset['schema_version'] ?? $defaults['schema_version']);
        $resolved['paramset_code'] = (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']);
        $resolved['backtest'] = array_merge($defaults['backtest'], $backtestInput);
        $resolved['risk'] = array_replace_recursive($defaults['risk'], $riskInput);
        $resolved['recommendation'] = array_replace_recursive(
            $recommendationDefaults['recommendation'],
            $recommendationInput
        );
        $resolved['eval'] = array_replace_recursive($defaults['eval'], $evalInput);

        return $resolved;
    }

    private function planItemForRecommendation(array $planOutput, array $recommendationItem): ?array
    {
        $planItems = [];
        foreach (['TOP_PICKS', 'SECONDARY', 'WATCH_ONLY', 'AVOID'] as $group) {
            foreach (($planOutput['groups'][$group] ?? []) as $item) {
                if (is_array($item)) {
                    $planItems[] = $item;
                }
            }
        }

        foreach (($planOutput['excluded'] ?? []) as $item) {
            if (is_array($item)) {
                $planItems[] = $item;
            }
        }

        return $this->firstItemForKeys(
            $this->indexByTickerIdentity($planItems),
            $this->identityKeys($recommendationItem)
        );
    }

    private function diagnosticScoreMetrics(array $planItem, array $planReference): array
    {
        $source = is_array($planItem['score_metrics'] ?? null) ? $planItem['score_metrics'] : [];
        $numericFields = [
            'dv20_idr',
            'atr14_pct',
            'vol_ratio',
            'signal_close_price',
            'theoretical_stop_risk_pct',
            'normalized_stop_risk_pct',
            'signal_tick_risk_expansion_pct',
            'roc20',
            'hh20',
            'ma20',
            'ma50',
            'close_to_hh20_pct',
            'close_vs_ma20_pct',
            'close_vs_ma50_pct',
            'ma20_slope_pct',
            'rs_20_vs_ihsg',
            'roc5',
            'roc10',
            'll20',
            'close_to_ll20_pct',
            'range_20_pct',
            'range_position_20_pct',
            'sector_roc20',
            'rs_20_vs_sector',
            'sector_rs_20_vs_ihsg',
            'market_index_roc20',
            'market_index_ma20_slope_pct',
        ];
        $metrics = [];
        foreach ($numericFields as $field) {
            $metrics[$field] = $this->floatOrNull($source[$field] ?? $planReference[$field] ?? null);
        }

        $corporateActionTypes = $this->stringOrNull($source['corporate_action_types'] ?? $planReference['corporate_action_types'] ?? null);
        $metrics['corporate_action_flag'] = $this->corporateActionFlagOrNull($source['corporate_action_flag'] ?? $planReference['corporate_action_flag'] ?? null, $corporateActionTypes);
        $metrics['corporate_action_types'] = $corporateActionTypes;
        $metrics['trading_status_code'] = WatchlistTradingStatusSnapshotNormalizer::normalize($source['trading_status_code'] ?? $planReference['trading_status_code'] ?? null);
        $metrics['is_suspended'] = $this->flagOrNull($source['is_suspended'] ?? $planReference['is_suspended'] ?? null);
        $metrics['is_uma'] = $this->flagOrNull($source['is_uma'] ?? $planReference['is_uma'] ?? null);
        $metrics['event_risk_flag'] = $this->flagOrNull($source['event_risk_flag'] ?? $planReference['event_risk_flag'] ?? null);
        $metrics['event_risk_reasons'] = $this->stringOrNull($source['event_risk_reasons'] ?? $planReference['event_risk_reasons'] ?? null);
        $metrics['market_regime'] = $this->stringOrNull($source['market_regime'] ?? $planReference['market_regime'] ?? null);
        $metrics['market_indicator_set_version'] = $this->stringOrNull($source['market_indicator_set_version'] ?? null);

        $sectorCode = $this->stringOrNull($source['sector_code'] ?? $planReference['sector_code'] ?? $planItem['sector_code'] ?? null);
        $metrics['sector_code'] = $sectorCode === null ? null : strtoupper($sectorCode);

        return $metrics;
    }

    private function diagnosticScoreComponents(array $planItem): array
    {
        $source = is_array($planItem['score_components'] ?? null) ? $planItem['score_components'] : [];
        $components = [];
        foreach (['score_momentum', 'score_breakout', 'score_volume', 'score_risk'] as $field) {
            $components[$field] = $this->floatOrNull($source[$field] ?? null);
        }

        return $components;
    }

    private function diagnosticFactorBreakdown(array $planItem): array
    {
        $source = is_array($planItem['factor_breakdown'] ?? null) ? $planItem['factor_breakdown'] : [];
        $breakdown = [];
        foreach (['momentum', 'breakout', 'volume', 'risk'] as $component) {
            $breakdown[$component] = is_array($source[$component] ?? null) ? $source[$component] : [];
        }

        return $breakdown;
    }

    private function indexByTickerIdentity(array $items): array
    {
        $index = [];
        foreach ($items as $item) {
            foreach ($this->identityKeys($item) as $key) {
                $index[$key] = $item;
            }
        }

        return $index;
    }

    private function firstItemForKeys(array $index, array $keys): ?array
    {
        foreach ($keys as $key) {
            if (isset($index[$key])) {
                return $index[$key];
            }
        }

        return null;
    }

    private function identityKeys(array $item): array
    {
        $keys = [];
        $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
        if ($tickerId !== null) {
            $keys[] = 'id:'.$tickerId;
        }

        $tickerCode = $this->tickerCode($item);
        if ($tickerCode !== '') {
            $keys[] = 'code:'.$tickerCode;
        }

        return $keys;
    }

    private function tickerCode(array $item): string
    {
        return strtoupper(trim((string) ($item['ticker'] ?? $item['ticker_code'] ?? '')));
    }

    private function diagnosticItem(?string $tradeDate, string $reasonCode, array $extra = []): array
    {
        return array_merge([
            'trade_date' => $tradeDate,
            'reason_code' => $reasonCode,
            'fatal' => false,
        ], $extra);
    }

    private function sortItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            return $this->compareReplayRows($left, $right);
        });

        return $items;
    }

    private function sortTrades(array $trades): array
    {
        usort($trades, function (array $left, array $right): int {
            return $this->compareReplayRows($left, $right);
        });

        return $trades;
    }

    private function sortEvaluations(array $evaluations): array
    {
        usort($evaluations, function (array $left, array $right): int {
            return $this->compareReplayRows($left, $right);
        });

        return $evaluations;
    }

    private function compareReplayRows(array $left, array $right): int
    {
        $leftDate = (string) ($left['trade_date'] ?? '');
        $rightDate = (string) ($right['trade_date'] ?? '');
        if ($leftDate !== $rightDate) {
            return strcmp($leftDate, $rightDate);
        }

        foreach ([
            'recommendation_rank',
            'plan_rank',
            'ticker_id',
        ] as $key) {
            $leftValue = $this->intOrNull($left[$key] ?? null);
            $rightValue = $this->intOrNull($right[$key] ?? null);
            if ($leftValue === $rightValue) {
                continue;
            }
            if ($leftValue === null) {
                return 1;
            }
            if ($rightValue === null) {
                return -1;
            }

            return $leftValue < $rightValue ? -1 : 1;
        }

        return strcmp((string) ($left['ticker'] ?? ''), (string) ($right['ticker'] ?? ''));
    }

    public static function canonicalEvalModel(array $paramset): string
    {
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $slip = rtrim(rtrim(number_format((float) ($backtest['slippage_entry_pct'] ?? 0.0), 6, '.', ''), '0'), '.');
        $exit = 'STOP_TP_OR_TIME';
        if (($backtest['exit_model'] ?? null) === 'WS_R02_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN_TIME'
            && ($backtest['research_execution'] ?? null)
                === WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution()) {
            $exit = 'SEQ_TP05_OR_PCNO_OR_TIME';
        } elseif (($backtest['exit_model'] ?? null)
                === 'WS_S01_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEXT_OPEN_TIME'
            && ($backtest['research_execution'] ?? null)
                === WatchlistBacktestTailRiskS01ParamGridCatalog::lossContainmentExecution()) {
            $exit = 'SEQ_TP05_OR_PCLNO_OR_TIME';
        } elseif (($backtest['exit_model'] ?? null)
                === 'WS_S01M1_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEG1_NEXT_OPEN_TIME'
            && ($backtest['research_execution'] ?? null)
                === WatchlistBacktestTailRiskS01RemediationParamGridCatalog::researchExecution()) {
            $exit = 'SEQ_TP05_PCL1NO_TIME';
        } elseif (($backtest['exit_model'] ?? null)
                === 'WS_S01M1_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEG1_NEXT_OPEN_TIME'
            && ($backtest['research_execution'] ?? null)
                === WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::researchExecution()) {
            $exit = 'SEQ_TP05_PCL1NO_TIME';
        }

        return sprintf(
            'ENTRY=NEXT_OPEN;EXIT=%s;HOLD=%d;FEE=%s;SLIP=%s;GAP=OPEN;PX=IDX_BANDS',
            $exit,
            (int) ($backtest['holding_days'] ?? 5),
            (string) ($backtest['fee_model'] ?? 'IDR_FIXED'),
            $slip === '' ? '0' : $slip
        );
    }

    private function evalModel(array $paramset): string
    {
        return self::canonicalEvalModel($paramset);
    }

    private function hasMismatch(array $values): bool
    {
        $filtered = [];
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                $filtered[] = (string) $value;
            }
        }

        return count(array_unique($filtered)) > 1;
    }

    private function isDateAfter(string $leftDate, string $rightDate): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $leftDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rightDate)) {
            return false;
        }

        return strcmp($leftDate, $rightDate) > 0;
    }

    private function uniqueReasonCodes(array $reasonCodes): array
    {
        $normalized = [];
        foreach ($reasonCodes as $reasonCode) {
            if (! is_scalar($reasonCode)) {
                continue;
            }
            $value = trim((string) $reasonCode);
            if ($value !== '') {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function floatOrNull($value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function flagOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value === 1 ? 1 : 0;
    }

    private function corporateActionFlagOrNull($value, ?string $corporateActionTypes): ?int
    {
        $explicit = $this->flagOrNull($value);
        if ($explicit !== null) {
            return $explicit;
        }

        return $corporateActionTypes === null ? null : 1;
    }

    private function stringOrNull($value): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
