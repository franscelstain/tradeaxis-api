<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingParamsetPromotionService
{
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;
    private MarketDataTradingCalendarReadService $calendar;

    public function __construct(
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null,
        MarketDataTradingCalendarReadService $calendar = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
    }

    public function execute(int $paramSetId, int $btParamId, int $oosId): array
    {
        foreach ([
            'watchlist_param_sets',
            'watchlist_bt_param_grid',
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_cutoffs_ws',
            'watchlist_bt_oos_eval_ws',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('WS_PARAMSET_PROMOTION_SCHEMA_MISSING', ['missing_table' => $table]);
            }
        }

        $lockName = 'WS:PARAMSET';
        $mysql = DB::connection()->getDriverName() === 'mysql';
        if ($mysql) {
            $lock = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);
            if ((int) ($lock->acquired ?? 0) !== 1) {
                return $this->blocked('WS_PARAMSET_PROMOTION_LOCK_UNAVAILABLE');
            }
        }

        try {
            return DB::transaction(function () use ($paramSetId, $btParamId, $oosId): array {
                $target = DB::table('watchlist_param_sets')
                    ->where('param_set_id', $paramSetId)
                    ->lockForUpdate()
                    ->first();
                if (! $target || (string) $target->status !== 'DRAFT' || (string) $target->policy_code !== 'WS') {
                    return $this->blocked('WS_PARAMSET_PROMOTION_TARGET_NOT_DRAFT');
                }

                $payload = json_decode((string) $target->params_json, true);
                $provenance = json_decode((string) $target->provenance_json, true);
                if (! is_array($payload) || ! is_array($provenance)) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_PAYLOAD_INVALID');
                }
                $validation = $this->validator->validate($payload);
                if (! $validation['valid']) {
                    return $this->blocked('WS_PARAMSET_VALIDATION_FAILED', ['validation' => $validation]);
                }

                $binding = $provenance['bt_binding'] ?? [];
                if ((int) ($binding['bt_param_id'] ?? 0) !== $btParamId) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_BINDING_MISMATCH');
                }

                $currentBinding = $this->bindingVerifier->verify(
                    $validation['canonical_payload'],
                    $btParamId,
                    (string) ($binding['catalog_code'] ?? '')
                );
                if (! $currentBinding['valid']) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_BINDING_MISMATCH', [
                        'binding_verification' => $currentBinding,
                    ]);
                }
                foreach (['bt_param_id', 'catalog_code', 'catalog_version', 'catalog_hash', 'row_code', 'row_hash'] as $key) {
                    if ((string) ($binding[$key] ?? '') !== (string) ($currentBinding[$key] ?? '')) {
                        return $this->blocked('WS_PARAMSET_PROMOTION_BINDING_DRIFT', [
                            'binding_field' => $key,
                            'expected' => $binding[$key] ?? null,
                            'actual' => $currentBinding[$key] ?? null,
                        ]);
                    }
                }

                $oos = DB::table('watchlist_bt_oos_eval_ws')
                    ->where('oos_id', $oosId)
                    ->where('policy_code', 'WS')
                    ->where('policy_version', (string) $target->policy_version)
                    ->where('param_id_best_is', $btParamId)
                    ->first();
                if (! $oos) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_OOS_PROOF_MISSING');
                }

                $isEval = DB::table('watchlist_bt_eval')
                    ->where('eval_id', (int) $oos->is_eval_id)
                    ->where('policy_code', 'WS')
                    ->where('param_id', $btParamId)
                    ->first();
                if (! $isEval) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_IS_EVAL_MISSING');
                }

                $officialEvidence = $this->officialSupportEvidence((array) $isEval);
                if (! $officialEvidence['pass']) {
                    return $this->blocked($officialEvidence['reason_code'], [
                        'official_support_evidence' => $officialEvidence,
                    ]);
                }

                $isGate = $this->isAcceptance((array) $isEval, $payload);
                if (! $isGate['pass']) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_IS_GATE_FAILED', ['is_acceptance' => $isGate]);
                }
                $oosGate = $this->oosAcceptance((array) $oos, $payload);
                if (! $oosGate['pass']) {
                    return $this->blocked('WS_PARAMSET_PROMOTION_OOS_GATE_FAILED', ['oos_acceptance' => $oosGate]);
                }

                $now = date('Y-m-d H:i:s');
                $deprecated = DB::table('watchlist_param_sets')
                    ->where('policy_code', 'WS')
                    ->where('status', 'ACTIVE')
                    ->update(['status' => 'DEPRECATED', 'updated_at' => $now]);
                $promoted = DB::table('watchlist_param_sets')
                    ->where('param_set_id', $paramSetId)
                    ->where('status', 'DRAFT')
                    ->update(['status' => 'ACTIVE', 'updated_at' => $now]);
                if ($promoted !== 1) {
                    throw new \RuntimeException('WS_PARAMSET_PROMOTION_WRITE_FAILED: target row was not promoted.');
                }

                return [
                    'status' => 'PROMOTED',
                    'reason_code' => 'WS_PARAMSET_PROMOTED_ACTIVE',
                    'param_set_id' => $paramSetId,
                    'bt_param_id' => $btParamId,
                    'oos_id' => $oosId,
                    'deprecated_active_count' => $deprecated,
                    'is_acceptance' => $isGate,
                    'oos_acceptance' => $oosGate,
                    'official_support_evidence' => $officialEvidence,
                    'production_ready' => false,
                ];
            });
        } finally {
            if ($mysql) {
                DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
            }
        }
    }

    private function isAcceptance(array $row, array $payload): array
    {
        $minTrades = (int) ($payload['eval']['min_trades']['value'] ?? 120);
        $configuredMinDays = (int) ($payload['eval']['min_days_covered']['value'] ?? 0);
        $calendar = $this->calendar->resolveTradingDates(
            (string) ($row['from_date'] ?? ''),
            (string) ($row['to_date'] ?? '')
        );
        $totalTradingDays = count($calendar['trade_dates'] ?? []);
        $effectiveMinDays = $configuredMinDays > 0
            ? $configuredMinDays
            : (int) ceil(0.70 * $totalTradingDays);
        $coverageResolvable = ($calendar['is_ready'] ?? false) === true && $effectiveMinDays > 0;
        $gates = [
            'minimum_trades' => (int) ($row['picks_count'] ?? 0) >= $minTrades,
            'minimum_days_covered' => $coverageResolvable
                && (int) ($row['days_covered'] ?? 0) >= $effectiveMinDays,
            'average_return_positive' => (float) ($row['avg_ret_net_top'] ?? 0) > 0,
            'median_return_positive' => (float) ($row['median_ret_net_top'] ?? 0) > 0,
            'p25_downside_bound' => (float) ($row['p25_ret_net_top'] ?? -1) >= (float) ($payload['eval']['min_p25_ret_net_top']['value'] ?? -0.03),
            'monthly_win_rate_floor' => (float) ($row['month_win_rate_min'] ?? 0) >= (float) ($payload['eval']['min_month_win_rate_min']['value'] ?? 0.45),
            'monthly_average_return_floor' => (float) ($row['month_avg_ret_net_min'] ?? -1) >= (float) ($payload['eval']['min_month_avg_ret_net_min']['value'] ?? -0.01),
        ];

        return [
            'pass' => ! in_array(false, $gates, true),
            'gates' => $gates,
            'coverage' => [
                'calendar_ready' => $coverageResolvable,
                'calendar_reason_code' => $calendar['reason_code'] ?? null,
                'configured_min_days_covered' => $configuredMinDays,
                'total_trading_days_in_window' => $totalTradingDays,
                'effective_min_days_covered' => $effectiveMinDays,
                'actual_days_covered' => (int) ($row['days_covered'] ?? 0),
            ],
        ];
    }

    private function oosAcceptance(array $row, array $payload): array
    {
        $minTrades = (int) ($payload['eval']['min_trades_oos']['value'] ?? 40);
        $gates = [
            'minimum_oos_trades' => (int) ($row['picks_count_oos'] ?? 0) >= $minTrades,
            'average_return_positive' => (float) ($row['avg_ret_net_top_oos'] ?? 0) > 0,
            'median_return_non_negative' => (float) ($row['median_ret_net_top_oos'] ?? -1) >= 0,
            'monthly_win_rate_floor' => (float) ($row['month_win_rate_min_oos'] ?? 0) >= 0.45,
            'p25_downside_bound' => (float) ($row['p25_ret_net_top_oos'] ?? -1) >= -0.03,
        ];

        return ['pass' => ! in_array(false, $gates, true), 'gates' => $gates];
    }

    private function officialSupportEvidence(array $isEval): array
    {
        $tables = [
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_cutoffs_ws',
        ];
        $missingIdentity = [];
        foreach ($tables as $table) {
            if (! Schema::hasColumn($table, 'eval_id')) {
                $missingIdentity[] = $table.'.eval_id';
            }
        }
        if ($missingIdentity !== []) {
            return [
                'pass' => false,
                'reason_code' => 'WS_PARAMSET_PROMOTION_OFFICIAL_EVIDENCE_SCHEMA_UNVERSIONED',
                'eval_id' => (int) ($isEval['eval_id'] ?? 0),
                'missing_identity_columns' => $missingIdentity,
                'message' => 'Official picks, universe, and cutoff evidence must be version-bound to the exact IS eval_id.',
            ];
        }

        $evalId = (int) ($isEval['eval_id'] ?? 0);
        $counts = [];
        foreach ($tables as $table) {
            $counts[$table] = DB::table($table)->where('eval_id', $evalId)->count();
        }
        $picksMatch = $counts['watchlist_bt_picks_ws'] === (int) ($isEval['picks_count'] ?? 0);
        $coveragePresent = $counts['watchlist_bt_universe_ws'] > 0
            && $counts['watchlist_bt_cutoffs_ws'] > 0;
        if (! $picksMatch || ! $coveragePresent) {
            return [
                'pass' => false,
                'reason_code' => 'WS_PARAMSET_PROMOTION_OFFICIAL_SUPPORT_EVIDENCE_MISSING',
                'eval_id' => $evalId,
                'expected_picks_count' => (int) ($isEval['picks_count'] ?? 0),
                'counts' => $counts,
                'picks_count_matches_eval' => $picksMatch,
                'universe_and_cutoffs_present' => $coveragePresent,
            ];
        }

        return [
            'pass' => true,
            'reason_code' => 'WS_PARAMSET_PROMOTION_OFFICIAL_SUPPORT_EVIDENCE_VALID',
            'eval_id' => $evalId,
            'counts' => $counts,
            'picks_count_matches_eval' => true,
            'universe_and_cutoffs_present' => true,
        ];
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'production_ready' => false,
        ], $context);
    }
}
