<?php

namespace App\Application\Watchlist\Services;

class WatchlistScoringService
{
    public const DEFAULT_PARAMSET = [
        'policy_code' => 'WS',
        'policy_version' => 'WS_EOD_RUNTIME',
        'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
        'setup' => [
            'roc_lo' => 0.02,
            'roc_hi' => 0.15,
            'mom_roc20_soft_min' => 0.0,
            'bo_near_below_pct' => 0.02,
            'bo_max_ext_pct' => 0.05,
        ],
        'liquidity' => [
            'min_dv20_idr' => 1000000000.0,
            'dv20_strong_idr' => 5000000000.0,
        ],
        'volume' => [
            'min_vol_ratio' => 1.2,
            'strong_vol_ratio' => 2.5,
        ],
        'risk' => [
            'min_atr14_pct' => 0.02,
            'max_atr14_pct' => 0.12,
            'atr_ideal_low' => 0.035,
            'atr_ideal_high' => 0.075,
            'stop_atr_mult' => 1.5,
            'min_rr' => 1.5,
        ],
        'scoring' => [
            'combine_mode' => 'WEIGHTED_MEAN',
            'weights' => [
                'momentum' => 0.30,
                'breakout' => 0.30,
                'volume' => 0.20,
                'risk' => 0.20,
            ],
        ],
    ];

    private const SCORE_SORT_KEYS = [
        'score_total_desc',
        'score_breakout_desc',
        'score_momentum_desc',
        'dv20_idr_desc',
        'atr14_pct_asc',
        'ticker_id_asc',
    ];

    private const REQUIRED_SCORE_METRICS = [
        'dv20_idr',
        'atr14_pct',
        'vol_ratio',
        'roc20',
        'hh20',
        'close_to_hh20_pct',
        'close_vs_ma20_pct',
        'close_vs_ma50_pct',
        'ma20_slope_pct',
        'rs_20_vs_ihsg',
    ];

    private WatchlistCandidateUniverseService $candidateUniverse;

    public function __construct(WatchlistCandidateUniverseService $candidateUniverse = null)
    {
        $this->candidateUniverse = $candidateUniverse ?: new WatchlistCandidateUniverseService();
    }

    public function scoreForTradeDate(string $tradeDate, array $paramset = []): array
    {
        $resolvedParamset = $this->resolveParamset($paramset);
        $universe = $this->candidateUniverse->buildCandidateUniverseForTradeDate($tradeDate, $resolvedParamset);

        return $this->scoreCandidateUniverse($universe, $resolvedParamset, $tradeDate);
    }

    public function scoreCandidateUniverse(array $universe, array $paramset = [], string $tradeDate = ''): array
    {
        $resolvedParamset = $this->resolveParamset($paramset);
        $payload = $this->basePayload($universe, $resolvedParamset, $tradeDate);
        $paramsetErrors = $this->validateParamset($resolvedParamset);

        if ($paramsetErrors !== []) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_SCORING_PARAMSET_INVALID';
            $payload['scoring_reason_code'] = 'WATCHLIST_SCORING_PARAMSET_INVALID';
            $payload['paramset_errors'] = $paramsetErrors;

            return $payload;
        }

        if (! ($universe['is_ready'] ?? false)) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = $universe['reason_code'] ?? 'WATCHLIST_CANDIDATE_UNIVERSE_NOT_READY';
            $payload['scoring_reason_code'] = 'WATCHLIST_SCORING_SOURCE_NOT_READY';

            return $payload;
        }

        foreach (($universe['eligible_candidates'] ?? []) as $candidate) {
            $payload['summary']['input_count']++;

            if (! ($candidate['eligible_plan'] ?? false) || ! ($candidate['guard_ok'] ?? false)) {
                $payload['excluded'][] = $this->excludedCandidate($candidate, ['WATCHLIST_SCORING_NOT_ELIGIBLE_PLAN']);
                continue;
            }

            $metrics = $this->extractMetrics($candidate, $resolvedParamset);
            $missing = $this->missingScoreFields($metrics);

            if ($missing !== []) {
                $payload['excluded'][] = $this->excludedCandidate($candidate, ['WS_DATA_MISSING'], $missing, $metrics);
                continue;
            }

            if ($metrics['atr14_pct'] > 1) {
                $payload['excluded'][] = $this->excludedCandidate(
                    $candidate,
                    ['WS_ATR_HIGH', 'WATCHLIST_SCORING_ATR_UNIT_DRIFT'],
                    ['atr14_pct'],
                    $metrics
                );
                continue;
            }

            $payload['items'][] = $this->scoreCandidate($candidate, $metrics, $resolvedParamset);
        }

        foreach (($universe['rejected_candidates'] ?? []) as $candidate) {
            $payload['summary']['input_count']++;
            $payload['excluded'][] = $this->excludedCandidate(
                $candidate,
                array_values($candidate['reason_codes'] ?? ['WATCHLIST_SCORING_NOT_ELIGIBLE_PLAN']),
                $candidate['missing_fields'] ?? [],
                $candidate['gate_metrics'] ?? []
            );
        }

        $payload['items'] = $this->sortItems($payload['items']);
        $payload['summary']['scored_count'] = count($payload['items']);
        $payload['summary']['excluded_count'] = count($payload['excluded']);
        $payload['ready'] = true;
        $payload['is_ready'] = true;
        $payload['has_scored_candidates'] = $payload['summary']['scored_count'] > 0;
        $payload['reason_code'] = $payload['has_scored_candidates']
            ? 'WATCHLIST_SCORING_READY'
            : 'WATCHLIST_SCORING_EMPTY';
        $payload['scoring_reason_code'] = $payload['reason_code'];

        return $payload;
    }

    public static function defaultParamset(): array
    {
        return self::DEFAULT_PARAMSET;
    }

    private function basePayload(array $universe, array $paramset, string $tradeDate): array
    {
        return [
            'ready' => false,
            'is_ready' => false,
            'has_scored_candidates' => false,
            'reason_code' => 'WATCHLIST_SCORING_NOT_EVALUATED',
            'scoring_reason_code' => 'WATCHLIST_SCORING_NOT_EVALUATED',
            'trade_date' => isset($universe['trade_date']) ? (string) $universe['trade_date'] : $tradeDate,
            'trade_date_effective' => $universe['trade_date_effective'] ?? null,
            'publication_id' => $universe['publication_id'] ?? null,
            'publication_version' => $universe['publication_version'] ?? null,
            'run_id' => $universe['run_id'] ?? null,
            'policy_code' => $paramset['policy_code'],
            'policy_version' => $paramset['policy_version'],
            'paramset_code' => $paramset['paramset_code'],
            'source_contract' => [
                'consumer' => 'WatchlistScoringService',
                'upstream' => 'WatchlistCandidateUniverseService',
                'no_raw_market_data' => true,
                'no_latest_shortcut' => true,
                'no_recommendation' => true,
                'no_confirm' => true,
                'no_execution' => true,
            ],
            'score_contract' => [
                'combine_mode' => $paramset['scoring']['combine_mode'],
                'range' => '0..1',
                'rounding_mode' => 'deterministic',
                'sort_keys' => self::SCORE_SORT_KEYS,
                'required_metrics' => self::REQUIRED_SCORE_METRICS,
            ],
            'paramset_snapshot' => [
                'policy_code' => $paramset['policy_code'],
                'policy_version' => $paramset['policy_version'],
                'paramset_code' => $paramset['paramset_code'],
                'scoring' => $paramset['scoring'],
                'setup' => $paramset['setup'],
                'liquidity' => $paramset['liquidity'],
                'volume' => $paramset['volume'],
                'risk' => $paramset['risk'],
            ],
            'items' => [],
            'excluded' => [],
            'summary' => [
                'input_count' => 0,
                'scored_count' => 0,
                'excluded_count' => 0,
            ],
            'paramset_errors' => [],
        ];
    }

    private function scoreCandidate(array $candidate, array $metrics, array $paramset): array
    {
        $momentum = $this->scoreMomentum($metrics, $paramset);
        $breakout = $this->scoreBreakout($metrics, $paramset);
        $volume = $this->scoreVolume($metrics, $paramset);
        $risk = $this->scoreRisk($metrics, $paramset);

        $components = [
            'score_momentum' => $momentum['score'],
            'score_breakout' => $breakout['score'],
            'score_volume' => $volume['score'],
            'score_risk' => $risk['score'],
        ];

        $weights = $paramset['scoring']['weights'];
        $scoreTotalRaw = (
            ($components['score_momentum'] * $weights['momentum']) +
            ($components['score_breakout'] * $weights['breakout']) +
            ($components['score_volume'] * $weights['volume']) +
            ($components['score_risk'] * $weights['risk'])
        ) / array_sum($weights);
        $scoreTotal = $this->roundScore($this->clamp01($scoreTotalRaw));

        $reasonCodes = array_values(array_unique(array_merge(
            $candidate['reason_codes'] ?? [],
            $momentum['reason_codes'],
            $breakout['reason_codes'],
            $volume['reason_codes'],
            $risk['reason_codes']
        )));

        return [
            'ticker_id' => $this->intOrNull($candidate['ticker_id'] ?? null),
            'ticker_code' => strtoupper(trim((string) ($candidate['ticker_code'] ?? ''))),
            'trade_date' => $candidate['trade_date'] ?? null,
            'trade_date_effective' => $candidate['trade_date_effective'] ?? null,
            'publication_id' => $candidate['publication_id'] ?? null,
            'publication_version' => $candidate['publication_version'] ?? null,
            'run_id' => $candidate['run_id'] ?? null,
            'policy_code' => $paramset['policy_code'],
            'policy_version' => $paramset['policy_version'],
            'paramset_code' => $paramset['paramset_code'],
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'score_total_raw' => $this->roundScore($scoreTotalRaw),
            'score_components' => $components,
            'score_weights' => $weights,
            'factor_breakdown' => [
                'momentum' => $momentum['breakdown'],
                'breakout' => $breakout['breakdown'],
                'volume' => $volume['breakdown'],
                'risk' => $risk['breakdown'],
            ],
            'reason_codes' => $reasonCodes,
            'ranking_keys' => [
                'score_total_desc' => $scoreTotal,
                'score_breakout_desc' => $components['score_breakout'],
                'score_momentum_desc' => $components['score_momentum'],
                'dv20_idr_desc' => $metrics['dv20_idr'],
                'atr14_pct_asc' => $metrics['atr14_pct'],
                'ticker_id_asc' => $this->intOrNull($candidate['ticker_id'] ?? null),
                'ticker_code_asc' => strtoupper(trim((string) ($candidate['ticker_code'] ?? ''))),
            ],
            'score_metrics' => $metrics,
        ];
    }

    private function scoreMomentum(array $metrics, array $paramset): array
    {
        $roc20 = $this->asFraction($metrics['roc20']);
        $ma20Slope = $this->asFraction($metrics['ma20_slope_pct']);
        $rs20 = $this->asFraction($metrics['rs_20_vs_ihsg']);
        $closeVsMa20 = $this->asFraction($metrics['close_vs_ma20_pct']);
        $closeVsMa50 = $this->asFraction($metrics['close_vs_ma50_pct']);

        $rocScore = $this->normalize($roc20, $paramset['setup']['roc_lo'], $paramset['setup']['roc_hi']);
        $slopeScore = $this->normalize($ma20Slope, -0.02, 0.05);
        $relativeStrengthScore = $this->normalize($rs20, -0.05, 0.10);
        $ma20Score = $this->normalize($closeVsMa20, -0.05, 0.05);
        $ma50Score = $this->normalize($closeVsMa50, -0.05, 0.08);

        $score = $this->roundScore($this->clamp01(
            ($rocScore * 0.35) +
            ($slopeScore * 0.20) +
            ($relativeStrengthScore * 0.25) +
            ($ma20Score * 0.10) +
            ($ma50Score * 0.10)
        ));

        $positiveTrendAlignment = $roc20 > $paramset['setup']['mom_roc20_soft_min']
            && $ma20Slope > 0
            && $rs20 > 0
            && $closeVsMa20 >= 0
            && $closeVsMa50 >= 0;

        $reason = ($score >= 0.65 || ($score >= 0.50 && $positiveTrendAlignment))
            ? 'WS_MOM_STRONG'
            : 'WS_MOM_WEAK';

        return [
            'score' => $score,
            'reason_codes' => [$reason],
            'breakdown' => [
                'roc20' => $roc20,
                'ma20_slope_pct' => $ma20Slope,
                'rs_20_vs_ihsg' => $rs20,
                'close_vs_ma20_pct' => $closeVsMa20,
                'close_vs_ma50_pct' => $closeVsMa50,
                'roc_score' => $this->roundScore($rocScore),
                'ma20_slope_score' => $this->roundScore($slopeScore),
                'relative_strength_score' => $this->roundScore($relativeStrengthScore),
                'close_vs_ma20_score' => $this->roundScore($ma20Score),
                'close_vs_ma50_score' => $this->roundScore($ma50Score),
            ],
        ];
    }

    private function scoreBreakout(array $metrics, array $paramset): array
    {
        $closeToHh20 = $this->asFraction($metrics['close_to_hh20_pct']);
        $nearBelow = $paramset['setup']['bo_near_below_pct'];
        $maxExtension = $paramset['setup']['bo_max_ext_pct'];
        $reason = 'WS_BO_FAR';

        if ($closeToHh20 > $maxExtension) {
            $excess = $closeToHh20 - $maxExtension;
            $score = $this->clamp01(0.55 - ($excess / max($maxExtension, 0.000001)));
            $reason = 'WS_BO_EXT';
        } elseif ($closeToHh20 > 0) {
            $score = 1.0 - (($closeToHh20 / max($maxExtension, 0.000001)) * 0.20);
            $reason = 'WS_BO_BREAK';
        } elseif ($closeToHh20 >= -$nearBelow) {
            $score = 0.80 + ((1 - (abs($closeToHh20) / max($nearBelow, 0.000001))) * 0.20);
            $reason = 'WS_BO_NEAR';
        } else {
            $farLimit = max($nearBelow * 3, 0.000001);
            $distanceBeyondNear = abs($closeToHh20) - $nearBelow;
            $score = $this->clamp01(0.60 - (($distanceBeyondNear / $farLimit) * 0.60));
            $reason = 'WS_BO_FAR';
        }

        return [
            'score' => $this->roundScore($score),
            'reason_codes' => [$reason],
            'breakdown' => [
                'hh20' => $metrics['hh20'],
                'close_to_hh20_pct' => $closeToHh20,
                'bo_near_below_pct' => $nearBelow,
                'bo_max_ext_pct' => $maxExtension,
            ],
        ];
    }

    private function scoreVolume(array $metrics, array $paramset): array
    {
        $minVolRatio = $paramset['volume']['min_vol_ratio'];
        $strongVolRatio = $paramset['volume']['strong_vol_ratio'];
        $minDv20Idr = $paramset['liquidity']['min_dv20_idr'];
        $strongDv20Idr = $paramset['liquidity']['dv20_strong_idr'];

        $volRatioScore = $this->normalize($metrics['vol_ratio'], $minVolRatio, $strongVolRatio);
        $liquidityScore = $this->normalize($metrics['dv20_idr'], $minDv20Idr, $strongDv20Idr);
        $score = $this->roundScore(($volRatioScore * 0.60) + ($liquidityScore * 0.40));

        $reasons = [];
        if ($metrics['vol_ratio'] < $minVolRatio) {
            $reasons[] = 'WS_VOLR_FAIL';
        }
        $reasons[] = $metrics['dv20_idr'] >= $strongDv20Idr ? 'WS_LIQ_STRONG' : 'WS_LIQ_BORDER';

        return [
            'score' => $score,
            'reason_codes' => array_values(array_unique($reasons)),
            'breakdown' => [
                'vol_ratio' => $metrics['vol_ratio'],
                'dv20_idr' => $metrics['dv20_idr'],
                'min_vol_ratio' => $minVolRatio,
                'strong_vol_ratio' => $strongVolRatio,
                'min_dv20_idr' => $minDv20Idr,
                'dv20_strong_idr' => $strongDv20Idr,
                'vol_ratio_score' => $this->roundScore($volRatioScore),
                'liquidity_score' => $this->roundScore($liquidityScore),
            ],
        ];
    }

    private function scoreRisk(array $metrics, array $paramset): array
    {
        $atr = $metrics['atr14_pct'];
        $min = $paramset['risk']['min_atr14_pct'];
        $max = $paramset['risk']['max_atr14_pct'];
        $idealLow = $paramset['risk']['atr_ideal_low'];
        $idealHigh = $paramset['risk']['atr_ideal_high'];

        if ($atr < $idealLow) {
            $score = 0.50 + ($this->normalize($atr, $min, $idealLow) * 0.50);
            $reason = 'WS_RISK_LOW';
        } elseif ($atr <= $idealHigh) {
            $score = 1.0;
            $reason = 'WS_RISK_IDEAL';
        } else {
            $score = 1.0 - ($this->normalize($atr, $idealHigh, $max) * 0.50);
            $reason = 'WS_RISK_HIGH';
        }

        return [
            'score' => $this->roundScore($this->clamp01($score)),
            'reason_codes' => [$reason],
            'breakdown' => [
                'atr14_pct' => $atr,
                'min_atr14_pct' => $min,
                'max_atr14_pct' => $max,
                'atr_ideal_low' => $idealLow,
                'atr_ideal_high' => $idealHigh,
            ],
        ];
    }

    private function excludedCandidate(array $candidate, array $reasonCodes, array $missingFields = [], array $metrics = []): array
    {
        return [
            'ticker_id' => $this->intOrNull($candidate['ticker_id'] ?? null),
            'ticker_code' => strtoupper(trim((string) ($candidate['ticker_code'] ?? ''))),
            'eligible_score' => false,
            'eligible_plan' => (bool) ($candidate['eligible_plan'] ?? false),
            'guard_ok' => (bool) ($candidate['guard_ok'] ?? false),
            'reason_codes' => array_values(array_unique($reasonCodes)),
            'missing_fields' => array_values($missingFields),
            'score_metrics' => $metrics,
        ];
    }

    private function sortItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            foreach ([
                ['score_total', 'desc'],
                ['score_breakout', 'desc', 'score_components'],
                ['score_momentum', 'desc', 'score_components'],
                ['dv20_idr', 'desc', 'score_metrics'],
                ['atr14_pct', 'asc', 'score_metrics'],
            ] as $rule) {
                $leftValue = $this->sortValue($left, $rule[0], $rule[2] ?? null);
                $rightValue = $this->sortValue($right, $rule[0], $rule[2] ?? null);
                if ($leftValue == $rightValue) {
                    continue;
                }

                if ($rule[1] === 'desc') {
                    return $leftValue < $rightValue ? 1 : -1;
                }

                return $leftValue > $rightValue ? 1 : -1;
            }

            $leftTickerId = $this->intOrNull($left['ticker_id'] ?? null);
            $rightTickerId = $this->intOrNull($right['ticker_id'] ?? null);
            if ($leftTickerId !== $rightTickerId) {
                if ($leftTickerId === null) {
                    return 1;
                }
                if ($rightTickerId === null) {
                    return -1;
                }

                return $leftTickerId < $rightTickerId ? -1 : 1;
            }

            return strcmp((string) ($left['ticker_code'] ?? ''), (string) ($right['ticker_code'] ?? ''));
        });

        return $items;
    }

    private function sortValue(array $item, string $key, ?string $nestedKey = null): float
    {
        if ($nestedKey !== null) {
            return (float) ($item[$nestedKey][$key] ?? 0.0);
        }

        return (float) ($item[$key] ?? 0.0);
    }

    private function extractMetrics(array $candidate, array $paramset = []): array
    {
        $gateMetrics = $candidate['gate_metrics'] ?? [];
        $indicators = $candidate['indicators'] ?? [];

        $metrics = [
            'dv20_idr' => $this->metricOrNull($gateMetrics['dv20_idr'] ?? $indicators['dv20idr'] ?? $indicators['dv20_idr'] ?? $candidate['dv20_idr'] ?? $candidate['dv20idr'] ?? null),
            'atr14_pct' => $this->metricOrNull($gateMetrics['atr14_pct'] ?? $indicators['atr14_pct'] ?? $candidate['atr14_pct'] ?? null),
            'vol_ratio' => $this->metricOrNull($gateMetrics['vol_ratio'] ?? $indicators['vol_ratio'] ?? $candidate['vol_ratio'] ?? null),
            'roc20' => $this->metricOrNull($gateMetrics['roc20'] ?? $indicators['roc_20'] ?? $indicators['roc20'] ?? $candidate['roc20'] ?? $candidate['roc_20'] ?? null),
            'hh20' => $this->metricOrNull($gateMetrics['hh20'] ?? $indicators['hh20'] ?? $candidate['hh20'] ?? null),
            'ma20' => $this->metricOrNull($gateMetrics['ma20'] ?? $indicators['ma20'] ?? $candidate['ma20'] ?? null),
            'ma50' => $this->metricOrNull($gateMetrics['ma50'] ?? $indicators['ma50'] ?? $candidate['ma50'] ?? null),
            'close_to_hh20_pct' => $this->metricOrNull($gateMetrics['close_to_hh20_pct'] ?? $indicators['close_to_hh20_pct'] ?? $candidate['close_to_hh20_pct'] ?? null),
            'close_vs_ma20_pct' => $this->metricOrNull($gateMetrics['close_vs_ma20_pct'] ?? $indicators['close_vs_ma20_pct'] ?? $candidate['close_vs_ma20_pct'] ?? null),
            'close_vs_ma50_pct' => $this->metricOrNull($gateMetrics['close_vs_ma50_pct'] ?? $indicators['close_vs_ma50_pct'] ?? $candidate['close_vs_ma50_pct'] ?? null),
            'ma20_slope_pct' => $this->metricOrNull($gateMetrics['ma20_slope_pct'] ?? $indicators['ma20_slope_pct'] ?? $candidate['ma20_slope_pct'] ?? null),
            'rs_20_vs_ihsg' => $this->metricOrNull($gateMetrics['rs_20_vs_ihsg'] ?? $indicators['rs_20_vs_ihsg'] ?? $candidate['rs_20_vs_ihsg'] ?? null),
            'sector_code' => $this->sectorCodeOrNull($candidate['sector_code'] ?? $gateMetrics['sector_code'] ?? $indicators['sector_code'] ?? null),
        ];

        if (($paramset['bt_catalog']['catalog_version'] ?? null) === 'C07') {
            $corporateActionTypes = $this->stringOrNull($gateMetrics['corporate_action_types'] ?? $indicators['corporate_action_types'] ?? $candidate['corporate_action_types'] ?? null);
            $metrics += [
                'roc5' => $this->metricOrNull($gateMetrics['roc5'] ?? $indicators['roc_5'] ?? $indicators['roc5'] ?? $candidate['roc_5'] ?? $candidate['roc5'] ?? null),
                'roc10' => $this->metricOrNull($gateMetrics['roc10'] ?? $indicators['roc_10'] ?? $indicators['roc10'] ?? $candidate['roc_10'] ?? $candidate['roc10'] ?? null),
                'll20' => $this->metricOrNull($gateMetrics['ll20'] ?? $indicators['ll20'] ?? $candidate['ll20'] ?? null),
                'close_to_ll20_pct' => $this->metricOrNull($gateMetrics['close_to_ll20_pct'] ?? $indicators['close_to_ll20_pct'] ?? $candidate['close_to_ll20_pct'] ?? null),
                'range_20_pct' => $this->metricOrNull($gateMetrics['range_20_pct'] ?? $indicators['range_20_pct'] ?? $candidate['range_20_pct'] ?? null),
                'range_position_20_pct' => $this->metricOrNull($gateMetrics['range_position_20_pct'] ?? $indicators['range_position_20_pct'] ?? $candidate['range_position_20_pct'] ?? null),
                'sector_roc20' => $this->metricOrNull($gateMetrics['sector_roc20'] ?? $indicators['sector_roc20'] ?? $candidate['sector_roc20'] ?? null),
                'rs_20_vs_sector' => $this->metricOrNull($gateMetrics['rs_20_vs_sector'] ?? $indicators['rs_20_vs_sector'] ?? $candidate['rs_20_vs_sector'] ?? null),
                'sector_rs_20_vs_ihsg' => $this->metricOrNull($gateMetrics['sector_rs_20_vs_ihsg'] ?? $indicators['sector_rs_20_vs_ihsg'] ?? $candidate['sector_rs_20_vs_ihsg'] ?? null),
                'corporate_action_flag' => $this->corporateActionFlagOrNull($gateMetrics['corporate_action_flag'] ?? $indicators['corporate_action_flag'] ?? $candidate['corporate_action_flag'] ?? null, $corporateActionTypes),
                'corporate_action_types' => $corporateActionTypes,
                'trading_status_code' => $this->stringOrNull($gateMetrics['trading_status_code'] ?? $indicators['trading_status_code'] ?? $candidate['trading_status_code'] ?? null),
                'is_suspended' => $this->flagOrNull($gateMetrics['is_suspended'] ?? $indicators['is_suspended'] ?? $candidate['is_suspended'] ?? null),
                'is_uma' => $this->flagOrNull($gateMetrics['is_uma'] ?? $indicators['is_uma'] ?? $candidate['is_uma'] ?? null),
                'event_risk_flag' => $this->flagOrNull($gateMetrics['event_risk_flag'] ?? $indicators['event_risk_flag'] ?? $candidate['event_risk_flag'] ?? null),
                'event_risk_reasons' => $this->stringOrNull($gateMetrics['event_risk_reasons'] ?? $indicators['event_risk_reasons'] ?? $candidate['event_risk_reasons'] ?? null),
            ];
        }

        return $metrics;
    }

    private function missingScoreFields(array $metrics): array
    {
        $missing = [];
        foreach (self::REQUIRED_SCORE_METRICS as $field) {
            if ($metrics[$field] === null) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    private function sectorCodeOrNull($value): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $sectorCode = strtoupper(trim((string) $value));

        return $sectorCode === '' ? null : $sectorCode;
    }

    private function resolveParamset(array $paramset): array
    {
        $defaults = self::DEFAULT_PARAMSET;
        $resolved = [
            'policy_code' => (string) ($paramset['policy_code'] ?? $defaults['policy_code']),
            'policy_version' => (string) ($paramset['policy_version'] ?? $defaults['policy_version']),
            'paramset_code' => (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']),
            'setup' => [
                'roc_lo' => $this->paramValue($paramset, ['setup', 'roc_lo'], $defaults['setup']['roc_lo']),
                'roc_hi' => $this->paramValue($paramset, ['setup', 'roc_hi'], $defaults['setup']['roc_hi']),
                'mom_roc20_soft_min' => $this->paramValue($paramset, ['setup', 'mom_roc20_soft_min'], $defaults['setup']['mom_roc20_soft_min']),
                'bo_near_below_pct' => $this->paramValue($paramset, ['setup', 'bo_near_below_pct'], $defaults['setup']['bo_near_below_pct']),
                'bo_max_ext_pct' => $this->paramValue($paramset, ['setup', 'bo_max_ext_pct'], $defaults['setup']['bo_max_ext_pct']),
            ],
            'liquidity' => [
                'min_dv20_idr' => $this->paramValue($paramset, ['liquidity', 'min_dv20_idr'], $defaults['liquidity']['min_dv20_idr']),
                'dv20_strong_idr' => $this->paramValue($paramset, ['liquidity', 'dv20_strong_idr'], $defaults['liquidity']['dv20_strong_idr']),
            ],
            'volume' => [
                'min_vol_ratio' => $this->paramValue($paramset, ['volume', 'min_vol_ratio'], $defaults['volume']['min_vol_ratio']),
                'strong_vol_ratio' => $this->paramValue($paramset, ['volume', 'strong_vol_ratio'], $defaults['volume']['strong_vol_ratio']),
            ],
            'risk' => [
                'min_atr14_pct' => $this->paramValue($paramset, ['risk', 'min_atr14_pct'], $defaults['risk']['min_atr14_pct']),
                'max_atr14_pct' => $this->paramValue($paramset, ['risk', 'max_atr14_pct'], $defaults['risk']['max_atr14_pct']),
                'atr_ideal_low' => $this->paramValue($paramset, ['risk', 'atr_ideal_low'], $defaults['risk']['atr_ideal_low']),
                'atr_ideal_high' => $this->paramValue($paramset, ['risk', 'atr_ideal_high'], $defaults['risk']['atr_ideal_high']),
            ],
            'scoring' => [
                'combine_mode' => (string) ($this->paramValueMixed($paramset, ['scoring', 'combine_mode'], $defaults['scoring']['combine_mode'])),
                'weights' => $this->resolveWeights($paramset, $defaults['scoring']['weights']),
            ],
        ];

        if (isset($paramset['bt_catalog'])) {
            $resolved['bt_catalog'] = $paramset['bt_catalog'];
        }
        if (isset($paramset['bt_grid_resolution'])) {
            $resolved['bt_grid_resolution'] = $paramset['bt_grid_resolution'];
        }

        return $resolved;
    }

    private function resolveWeights(array $paramset, array $defaults): array
    {
        $weights = $this->paramValueMixed($paramset, ['scoring', 'weights'], $defaults);
        if (is_array($weights) && array_key_exists('value', $weights) && is_array($weights['value'])) {
            $weights = $weights['value'];
        }
        if (! is_array($weights)) {
            $weights = [];
        }

        return [
            'momentum' => $this->numberFromArray($weights, 'momentum', $defaults['momentum']),
            'breakout' => $this->numberFromArray($weights, 'breakout', $defaults['breakout']),
            'volume' => $this->numberFromArray($weights, 'volume', $defaults['volume']),
            'risk' => $this->numberFromArray($weights, 'risk', $defaults['risk']),
        ];
    }

    private function validateParamset(array $paramset): array
    {
        $errors = [];

        if ($paramset['scoring']['combine_mode'] !== 'WEIGHTED_MEAN') {
            $errors[] = 'scoring.combine_mode must be WEIGHTED_MEAN';
        }

        foreach ($paramset['scoring']['weights'] as $name => $value) {
            if (! is_numeric($value) || $value < 0) {
                $errors[] = 'scoring.weights.value.'.$name.' must be numeric and >= 0';
            }
        }

        if (array_sum($paramset['scoring']['weights']) <= 0) {
            $errors[] = 'scoring.weights total must be > 0';
        }

        foreach ([
            'setup.roc_lo' => $paramset['setup']['roc_lo'],
            'setup.roc_hi' => $paramset['setup']['roc_hi'],
            'setup.bo_near_below_pct' => $paramset['setup']['bo_near_below_pct'],
            'setup.bo_max_ext_pct' => $paramset['setup']['bo_max_ext_pct'],
            'liquidity.min_dv20_idr' => $paramset['liquidity']['min_dv20_idr'],
            'liquidity.dv20_strong_idr' => $paramset['liquidity']['dv20_strong_idr'],
            'volume.min_vol_ratio' => $paramset['volume']['min_vol_ratio'],
            'volume.strong_vol_ratio' => $paramset['volume']['strong_vol_ratio'],
            'risk.min_atr14_pct' => $paramset['risk']['min_atr14_pct'],
            'risk.max_atr14_pct' => $paramset['risk']['max_atr14_pct'],
            'risk.atr_ideal_low' => $paramset['risk']['atr_ideal_low'],
            'risk.atr_ideal_high' => $paramset['risk']['atr_ideal_high'],
        ] as $name => $value) {
            if (! is_numeric($value)) {
                $errors[] = $name.' must be numeric';
            }
        }

        if ($paramset['setup']['roc_lo'] >= $paramset['setup']['roc_hi']) {
            $errors[] = 'setup.roc_lo must be < setup.roc_hi';
        }
        if ($paramset['liquidity']['dv20_strong_idr'] < $paramset['liquidity']['min_dv20_idr']) {
            $errors[] = 'liquidity.dv20_strong_idr must be >= liquidity.min_dv20_idr';
        }
        if ($paramset['volume']['strong_vol_ratio'] < $paramset['volume']['min_vol_ratio']) {
            $errors[] = 'volume.strong_vol_ratio must be >= volume.min_vol_ratio';
        }
        if ($paramset['risk']['min_atr14_pct'] > $paramset['risk']['max_atr14_pct']) {
            $errors[] = 'risk.min_atr14_pct must be <= risk.max_atr14_pct';
        }
        if ($paramset['risk']['atr_ideal_low'] < $paramset['risk']['min_atr14_pct']) {
            $errors[] = 'risk.atr_ideal_low must be >= risk.min_atr14_pct';
        }
        if ($paramset['risk']['atr_ideal_high'] > $paramset['risk']['max_atr14_pct']) {
            $errors[] = 'risk.atr_ideal_high must be <= risk.max_atr14_pct';
        }
        if ($paramset['risk']['atr_ideal_low'] > $paramset['risk']['atr_ideal_high']) {
            $errors[] = 'risk.atr_ideal_low must be <= risk.atr_ideal_high';
        }

        foreach (['risk.min_atr14_pct', 'risk.max_atr14_pct', 'risk.atr_ideal_low', 'risk.atr_ideal_high'] as $name) {
            $path = explode('.', $name);
            if ($paramset[$path[0]][$path[1]] > 1) {
                $errors[] = $name.' must be a fraction between 0 and 1, not percent-points';
            }
        }

        return $errors;
    }

    private function paramValue(array $paramset, array $path, float $default): float
    {
        $value = $this->paramValueMixed($paramset, $path, $default);

        return is_numeric($value) ? (float) $value : $default;
    }

    private function paramValueMixed(array $paramset, array $path, $default)
    {
        $cursor = $paramset;
        foreach ($path as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        if (is_array($cursor) && array_key_exists('value', $cursor)) {
            return $cursor['value'];
        }

        return $cursor;
    }

    private function numberFromArray(array $values, string $key, float $default): float
    {
        return isset($values[$key]) && is_numeric($values[$key]) ? (float) $values[$key] : $default;
    }

    private function normalize(float $value, float $min, float $max): float
    {
        if ($max <= $min) {
            return 0.0;
        }

        return $this->clamp01(($value - $min) / ($max - $min));
    }

    private function clamp01(float $value): float
    {
        if ($value < 0.0) {
            return 0.0;
        }
        if ($value > 1.0) {
            return 1.0;
        }

        return $value;
    }

    private function roundScore(float $value): float
    {
        return round($this->clamp01($value), 6);
    }

    private function asFraction(float $value): float
    {
        if (abs($value) > 1.0) {
            return $value / 100.0;
        }

        return $value;
    }

    private function metricOrNull($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
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

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
