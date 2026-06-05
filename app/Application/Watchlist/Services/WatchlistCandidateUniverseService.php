<?php

namespace App\Application\Watchlist\Services;

class WatchlistCandidateUniverseService
{
    public const DEFAULT_PARAMSET = [
        'policy_code' => 'WS',
        'policy_version' => 'WS_EOD_RUNTIME',
        'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
        'liquidity' => [
            'min_dv20_idr' => 1000000000.0,
            'dv20_strong_idr' => 5000000000.0,
        ],
        'volume' => [
            'min_vol_ratio' => 1.2,
        ],
        'risk' => [
            'min_atr14_pct' => 0.02,
            'max_atr14_pct' => 0.12,
            'atr_ideal_low' => 0.035,
            'atr_ideal_high' => 0.075,
        ],
    ];

    private WatchlistMarketDataConsumerReadService $readModel;

    public function __construct(WatchlistMarketDataConsumerReadService $readModel = null)
    {
        $this->readModel = $readModel ?: new WatchlistMarketDataConsumerReadService();
    }

    public function buildCandidateUniverseForTradeDate(string $tradeDate, array $paramset = []): array
    {
        $source = $this->readModel->getCandidateUniverseForTradeDate($tradeDate);
        $resolvedParamset = $this->resolveParamset($paramset);
        $payload = $this->basePayload($source, $resolvedParamset, $tradeDate);

        if (! ($source['is_ready'] ?? false)) {
            $payload['is_ready'] = false;
            $payload['reason_code'] = $source['watchlist_reason_code'] ?? $source['reason_code'] ?? 'WATCHLIST_MARKET_DATA_NOT_READY';
            $payload['candidate_universe_reason_code'] = 'WATCHLIST_CANDIDATE_UNIVERSE_SOURCE_NOT_READY';

            return $payload;
        }

        $paramsetErrors = $this->validateParamset($resolvedParamset);
        if ($paramsetErrors !== []) {
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_PARAMSET_INVALID';
            $payload['candidate_universe_reason_code'] = 'WATCHLIST_PARAMSET_INVALID';
            $payload['paramset_errors'] = $paramsetErrors;

            return $payload;
        }

        foreach (($source['candidates'] ?? []) as $candidate) {
            $evaluation = $this->evaluateCandidate($candidate, $resolvedParamset);
            $payload['universe_rows'][] = $evaluation;

            if ($evaluation['eligible_plan']) {
                $payload['eligible_candidates'][] = $evaluation;
            } else {
                $payload['rejected_candidates'][] = $evaluation;
            }
        }

        $payload['input_candidate_count'] = count($source['candidates'] ?? []);
        $payload['eligible_count'] = count($payload['eligible_candidates']);
        $payload['rejected_count'] = count($payload['rejected_candidates']);
        $payload['reason_counts'] = $this->reasonCounts($payload['universe_rows']);
        $payload['has_eligible_candidates'] = $payload['eligible_count'] > 0;
        $payload['is_ready'] = true;
        $payload['reason_code'] = $payload['has_eligible_candidates']
            ? 'WATCHLIST_CANDIDATE_UNIVERSE_READY'
            : 'WATCHLIST_CANDIDATE_UNIVERSE_EMPTY';
        $payload['candidate_universe_reason_code'] = $payload['reason_code'];

        return $payload;
    }

    public static function defaultParamset(): array
    {
        return self::DEFAULT_PARAMSET;
    }

    private function basePayload(array $source, array $paramset, string $tradeDate): array
    {
        return [
            'trade_date' => isset($source['trade_date']) ? (string) $source['trade_date'] : $tradeDate,
            'trade_date_effective' => $source['trade_date_effective'] ?? null,
            'publication_id' => $source['publication_id'] ?? null,
            'publication_version' => $source['publication_version'] ?? null,
            'run_id' => $source['run_id'] ?? null,
            'policy_code' => $paramset['policy_code'],
            'policy_version' => $paramset['policy_version'],
            'paramset_code' => $paramset['paramset_code'],
            'is_ready' => false,
            'has_eligible_candidates' => false,
            'reason_code' => 'WATCHLIST_CANDIDATE_UNIVERSE_NOT_EVALUATED',
            'candidate_universe_reason_code' => 'WATCHLIST_CANDIDATE_UNIVERSE_NOT_EVALUATED',
            'source_contract' => $source['source_contract'] ?? [],
            'universe_contract' => [
                'source' => 'watchlist-market-data-consumer-read-model',
                'resolution_mode' => 'current_readable_publication_pointer',
                'forbids_raw_staging_latest_max_date_bypass' => true,
                'applies_liquidity_guard' => true,
                'applies_risk_guard' => true,
                'applies_volume_participation_guard' => true,
                'does_not_score' => true,
                'does_not_recommend' => true,
                'does_not_backtest' => true,
            ],
            'paramset' => $paramset,
            'input_candidate_count' => 0,
            'eligible_candidates' => [],
            'eligible_count' => 0,
            'rejected_candidates' => [],
            'rejected_count' => 0,
            'universe_rows' => [],
            'reason_counts' => [],
            'paramset_errors' => [],
        ];
    }

    private function evaluateCandidate(array $candidate, array $paramset): array
    {
        $metrics = $this->extractMetrics($candidate);
        $missingFields = $this->missingGuardFields($metrics);
        $failReasons = [];
        $infoReasons = [];

        if ($missingFields !== []) {
            $failReasons[] = 'WS_DATA_MISSING';
        }

        if ($missingFields === []) {
            if ($metrics['dv20_idr'] < $paramset['liquidity']['min_dv20_idr']) {
                $failReasons[] = 'WS_LIQ_FAIL';
            }

            if ($metrics['atr14_pct'] < $paramset['risk']['min_atr14_pct']) {
                $failReasons[] = 'WS_ATR_LOW';
            }

            if ($metrics['atr14_pct'] > $paramset['risk']['max_atr14_pct']) {
                $failReasons[] = 'WS_ATR_HIGH';
            }

            if ($metrics['vol_ratio'] < $paramset['volume']['min_vol_ratio']) {
                $failReasons[] = 'WS_VOLR_FAIL';
            }
        }

        if ($missingFields === [] && $metrics['dv20_idr'] >= $paramset['liquidity']['min_dv20_idr']) {
            $infoReasons[] = $metrics['dv20_idr'] >= $paramset['liquidity']['dv20_strong_idr']
                ? 'WS_LIQ_STRONG'
                : 'WS_LIQ_BORDER';
        }

        if ($missingFields === [] && $metrics['atr14_pct'] >= $paramset['risk']['min_atr14_pct'] && $metrics['atr14_pct'] <= $paramset['risk']['max_atr14_pct']) {
            if ($metrics['atr14_pct'] >= $paramset['risk']['atr_ideal_low'] && $metrics['atr14_pct'] <= $paramset['risk']['atr_ideal_high']) {
                $infoReasons[] = 'WS_RISK_IDEAL';
            } elseif ($metrics['atr14_pct'] < $paramset['risk']['atr_ideal_low']) {
                $infoReasons[] = 'WS_RISK_LOW';
            } else {
                $infoReasons[] = 'WS_RISK_HIGH';
            }
        }

        $canonicalFailReason = $this->canonicalFailReason($failReasons);
        $eligible = $canonicalFailReason === null;

        return [
            'asof_eod_date' => (string) ($candidate['trade_date_effective'] ?? $candidate['trade_date'] ?? ''),
            'trade_date' => (string) ($candidate['trade_date'] ?? ''),
            'trade_date_effective' => $candidate['trade_date_effective'] ?? null,
            'publication_id' => $candidate['publication_id'] ?? null,
            'publication_version' => $candidate['publication_version'] ?? null,
            'run_id' => $candidate['run_id'] ?? null,
            'policy_code' => $paramset['policy_code'],
            'policy_version' => $paramset['policy_version'],
            'paramset_code' => $paramset['paramset_code'],
            'ticker_id' => $candidate['ticker_id'] ?? null,
            'ticker_code' => strtoupper(trim((string) ($candidate['ticker_code'] ?? ''))),
            'ticker_name' => $candidate['ticker_name'] ?? null,
            'close_price' => $candidate['close_price'] ?? null,
            'required_ok' => $missingFields === [],
            'guard_ok' => $failReasons === [],
            'eligible_plan' => $eligible,
            'canonical_fail_reason_code' => $canonicalFailReason,
            'reason_codes' => array_values(array_unique(array_merge($failReasons, $infoReasons))),
            'missing_fields' => $missingFields,
            'gate_metrics' => $metrics,
            'gate_thresholds' => [
                'min_dv20_idr' => $paramset['liquidity']['min_dv20_idr'],
                'dv20_strong_idr' => $paramset['liquidity']['dv20_strong_idr'],
                'min_atr14_pct' => $paramset['risk']['min_atr14_pct'],
                'max_atr14_pct' => $paramset['risk']['max_atr14_pct'],
                'atr_ideal_low' => $paramset['risk']['atr_ideal_low'],
                'atr_ideal_high' => $paramset['risk']['atr_ideal_high'],
                'min_vol_ratio' => $paramset['volume']['min_vol_ratio'],
            ],
        ];
    }

    private function extractMetrics(array $candidate): array
    {
        $indicators = $candidate['indicators'] ?? [];

        return [
            'dv20_idr' => $this->metricOrNull($indicators['dv20idr'] ?? $indicators['dv20_idr'] ?? $candidate['dv20idr'] ?? $candidate['dv20_idr'] ?? null),
            'atr14_pct' => $this->metricOrNull($indicators['atr14_pct'] ?? $candidate['atr14_pct'] ?? null),
            'vol_ratio' => $this->metricOrNull($indicators['vol_ratio'] ?? $candidate['vol_ratio'] ?? null),
            'roc20' => $this->metricOrNull($indicators['roc_20'] ?? $indicators['roc20'] ?? $candidate['roc_20'] ?? $candidate['roc20'] ?? null),
            'hh20' => $this->metricOrNull($indicators['hh20'] ?? $candidate['hh20'] ?? null),
            'ma20' => $this->metricOrNull($indicators['ma20'] ?? $candidate['ma20'] ?? null),
            'ma50' => $this->metricOrNull($indicators['ma50'] ?? $candidate['ma50'] ?? null),
            'close_to_hh20_pct' => $this->metricOrNull($indicators['close_to_hh20_pct'] ?? $candidate['close_to_hh20_pct'] ?? null),
            'close_vs_ma20_pct' => $this->metricOrNull($indicators['close_vs_ma20_pct'] ?? $candidate['close_vs_ma20_pct'] ?? null),
            'close_vs_ma50_pct' => $this->metricOrNull($indicators['close_vs_ma50_pct'] ?? $candidate['close_vs_ma50_pct'] ?? null),
            'ma20_slope_pct' => $this->metricOrNull($indicators['ma20_slope_pct'] ?? $candidate['ma20_slope_pct'] ?? null),
            'rs_20_vs_ihsg' => $this->metricOrNull($indicators['rs_20_vs_ihsg'] ?? $candidate['rs_20_vs_ihsg'] ?? null),
        ];
    }

    private function metricOrNull($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function missingGuardFields(array $metrics): array
    {
        $missing = [];
        foreach (['dv20_idr', 'atr14_pct', 'vol_ratio'] as $field) {
            if ($metrics[$field] === null) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    private function canonicalFailReason(array $failReasons): ?string
    {
        foreach (['WS_DATA_MISSING', 'WS_LIQ_FAIL', 'WS_ATR_LOW', 'WS_ATR_HIGH', 'WS_VOLR_FAIL'] as $reason) {
            if (in_array($reason, $failReasons, true)) {
                return $reason;
            }
        }

        return null;
    }

    private function reasonCounts(array $rows): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $primary = $row['canonical_fail_reason_code'] ?: 'WS_ELIGIBLE';
            $counts[$primary] = ($counts[$primary] ?? 0) + 1;
        }

        $ordered = [];
        foreach (['WS_ELIGIBLE', 'WS_DATA_MISSING', 'WS_LIQ_FAIL', 'WS_ATR_LOW', 'WS_ATR_HIGH', 'WS_VOLR_FAIL'] as $reason) {
            if (array_key_exists($reason, $counts)) {
                $ordered[$reason] = $counts[$reason];
                unset($counts[$reason]);
            }
        }

        ksort($counts);

        return array_merge($ordered, $counts);
    }

    private function resolveParamset(array $paramset): array
    {
        $defaults = self::DEFAULT_PARAMSET;

        return [
            'policy_code' => (string) ($paramset['policy_code'] ?? $defaults['policy_code']),
            'policy_version' => (string) ($paramset['policy_version'] ?? $defaults['policy_version']),
            'paramset_code' => (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']),
            'liquidity' => [
                'min_dv20_idr' => $this->paramValue($paramset, ['liquidity', 'min_dv20_idr'], $defaults['liquidity']['min_dv20_idr']),
                'dv20_strong_idr' => $this->paramValue($paramset, ['liquidity', 'dv20_strong_idr'], $defaults['liquidity']['dv20_strong_idr']),
            ],
            'volume' => [
                'min_vol_ratio' => $this->paramValue($paramset, ['volume', 'min_vol_ratio'], $defaults['volume']['min_vol_ratio']),
            ],
            'risk' => [
                'min_atr14_pct' => $this->paramValue($paramset, ['risk', 'min_atr14_pct'], $defaults['risk']['min_atr14_pct']),
                'max_atr14_pct' => $this->paramValue($paramset, ['risk', 'max_atr14_pct'], $defaults['risk']['max_atr14_pct']),
                'atr_ideal_low' => $this->paramValue($paramset, ['risk', 'atr_ideal_low'], $defaults['risk']['atr_ideal_low']),
                'atr_ideal_high' => $this->paramValue($paramset, ['risk', 'atr_ideal_high'], $defaults['risk']['atr_ideal_high']),
            ],
        ];
    }

    private function paramValue(array $paramset, array $path, float $default): float
    {
        $cursor = $paramset;
        foreach ($path as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        if (is_array($cursor) && array_key_exists('value', $cursor)) {
            $cursor = $cursor['value'];
        }

        return is_numeric($cursor) ? (float) $cursor : $default;
    }

    private function validateParamset(array $paramset): array
    {
        $errors = [];

        foreach ([
            'liquidity.min_dv20_idr' => $paramset['liquidity']['min_dv20_idr'],
            'liquidity.dv20_strong_idr' => $paramset['liquidity']['dv20_strong_idr'],
            'volume.min_vol_ratio' => $paramset['volume']['min_vol_ratio'],
            'risk.min_atr14_pct' => $paramset['risk']['min_atr14_pct'],
            'risk.max_atr14_pct' => $paramset['risk']['max_atr14_pct'],
            'risk.atr_ideal_low' => $paramset['risk']['atr_ideal_low'],
            'risk.atr_ideal_high' => $paramset['risk']['atr_ideal_high'],
        ] as $name => $value) {
            if (! is_numeric($value) || (float) $value < 0) {
                $errors[] = $name.' must be numeric and >= 0';
            }
        }

        if ($paramset['liquidity']['dv20_strong_idr'] < $paramset['liquidity']['min_dv20_idr']) {
            $errors[] = 'liquidity.dv20_strong_idr must be >= liquidity.min_dv20_idr';
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
}
