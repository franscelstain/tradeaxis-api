<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataWatchlistReadService;

class WatchlistMarketDataConsumerReadService
{
    public const REQUIRED_INDICATOR_FIELDS = [
        'close_price',
        'volume',
        'dv20idr',
        'atr14_pct',
        'vol_ratio',
        'roc_20',
        'hh20',
        'ma20',
        'ma50',
        'close_to_hh20_pct',
        'close_vs_ma20_pct',
        'close_vs_ma50_pct',
        'ma20_slope_pct',
        'rs_20_vs_ihsg',
        'indicator_set_version',
    ];

    private MarketDataWatchlistReadService $marketData;

    public function __construct(MarketDataWatchlistReadService $marketData = null)
    {
        $this->marketData = $marketData ?: new MarketDataWatchlistReadService();
    }

    public function getCandidateUniverseForTradeDate(string $tradeDate): array
    {
        $marketDataPayload = $this->marketData->getWatchlistMarketDataForTradeDate($tradeDate);
        $payload = $this->basePayload($marketDataPayload, $tradeDate);

        if (! ($marketDataPayload['is_ready'] ?? false)) {
            $payload['is_ready'] = false;
            $payload['reason_code'] = $marketDataPayload['reason_code'] ?? 'MARKET_DATA_NOT_READY';
            $payload['watchlist_reason_code'] = 'MARKET_DATA_NOT_READY';
            $payload['pointer_resolve_status'] = $marketDataPayload['pointer_resolve_status'] ?? 'NOT_RESOLVED_READABLE_CURRENT';

            return $payload;
        }

        foreach (($marketDataPayload['rows'] ?? []) as $row) {
            $violations = $this->candidateViolationCodes($row);

            if ($violations !== []) {
                $payload['excluded_rows'][] = [
                    'ticker_code' => isset($row['ticker_code']) ? strtoupper(trim((string) $row['ticker_code'])) : null,
                    'reason_codes' => $violations,
                ];
                continue;
            }

            $payload['candidates'][] = $this->normalizeCandidate($row, $payload);
        }

        $payload['candidate_count'] = count($payload['candidates']);
        $payload['excluded_count'] = count($payload['excluded_rows']);

        if ($payload['candidate_count'] === 0) {
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_MARKET_DATA_NO_VALID_CANDIDATES';
            $payload['watchlist_reason_code'] = 'WATCHLIST_MARKET_DATA_NO_VALID_CANDIDATES';

            return $payload;
        }

        $payload['is_ready'] = true;
        $payload['reason_code'] = $payload['excluded_count'] > 0
            ? 'WATCHLIST_MARKET_DATA_READY_WITH_EXCLUSIONS'
            : 'WATCHLIST_MARKET_DATA_READY';
        $payload['watchlist_reason_code'] = $payload['reason_code'];

        return $payload;
    }

    public static function requiredIndicatorFields(): array
    {
        return self::REQUIRED_INDICATOR_FIELDS;
    }

    private function basePayload(array $marketDataPayload, string $tradeDate): array
    {
        return [
            'trade_date' => isset($marketDataPayload['trade_date']) ? (string) $marketDataPayload['trade_date'] : $tradeDate,
            'trade_date_effective' => $marketDataPayload['trade_date_effective'] ?? null,
            'publication_id' => $marketDataPayload['publication_id'] ?? null,
            'publication_version' => $marketDataPayload['publication_version'] ?? null,
            'run_id' => $marketDataPayload['run_id'] ?? null,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_MARKET_DATA_NOT_EVALUATED',
            'watchlist_reason_code' => 'WATCHLIST_MARKET_DATA_NOT_EVALUATED',
            'pointer_resolve_status' => $marketDataPayload['pointer_resolve_status'] ?? null,
            'source_contract' => [
                'source' => 'market-data',
                'resolution_mode' => 'current_readable_publication_pointer',
                'requires_sealed_publication' => true,
                'requires_success_run' => true,
                'requires_readable_publication' => true,
                'requires_coverage_pass' => true,
                'requires_pointer_valid' => true,
                'requires_publication_run_mirror_valid' => true,
                'event_risk_snapshot_source' => 'published_indicator_snapshot',
                'trading_status_code_semantics' => 'market_data_trading_status_event_types.event_type_code_snapshot',
                'forbids_raw_staging_latest_max_date_bypass' => true,
                'forbids_raw_trading_status_event_join' => true,
            ],
            'required_indicator_fields' => self::REQUIRED_INDICATOR_FIELDS,
            'candidates' => [],
            'candidate_count' => 0,
            'excluded_rows' => [],
            'excluded_count' => 0,
        ];
    }

    private function normalizeCandidate(array $row, array $payload): array
    {
        return [
            'trade_date' => (string) ($row['trade_date'] ?? $payload['trade_date']),
            'trade_date_effective' => $payload['trade_date_effective'],
            'publication_id' => $payload['publication_id'],
            'publication_version' => $payload['publication_version'],
            'run_id' => $payload['run_id'],
            'ticker_id' => isset($row['ticker_id']) ? (int) $row['ticker_id'] : null,
            'ticker_code' => strtoupper(trim((string) $row['ticker_code'])),
            'ticker_name' => $row['ticker_name'] ?? null,
            'sector_code' => $this->sectorCodeOrNull($row['sector_code'] ?? null),
            'close_price' => (float) $row['close_price'],
            'volume' => (int) $row['volume'],
            'source_name' => $row['source_name'] ?? null,
            'eligibility_state' => $row['eligibility_state'] ?? 'ELIGIBLE',
            'eligibility_reason_code' => $row['eligibility_reason_code'] ?? null,
            'indicator_set_version' => (string) $row['indicator_set_version'],
            'indicators' => [
                'dv20idr' => (float) $row['dv20idr'],
                'atr14_pct' => (float) $row['atr14_pct'],
                'vol_ratio' => (float) $row['vol_ratio'],
                'roc_5' => $this->floatOrNull($row['roc_5'] ?? null),
                'roc_10' => $this->floatOrNull($row['roc_10'] ?? null),
                'roc_20' => (float) $row['roc_20'],
                'hh20' => (float) $row['hh20'],
                'll20' => $this->floatOrNull($row['ll20'] ?? null),
                'ma20' => (float) $row['ma20'],
                'ma50' => (float) $row['ma50'],
                'close_to_hh20_pct' => (float) $row['close_to_hh20_pct'],
                'close_to_ll20_pct' => $this->floatOrNull($row['close_to_ll20_pct'] ?? null),
                'range_20_pct' => $this->floatOrNull($row['range_20_pct'] ?? null),
                'range_position_20_pct' => $this->floatOrNull($row['range_position_20_pct'] ?? null),
                'close_vs_ma20_pct' => (float) $row['close_vs_ma20_pct'],
                'close_vs_ma50_pct' => (float) $row['close_vs_ma50_pct'],
                'ma20_slope_pct' => (float) $row['ma20_slope_pct'],
                'rs_20_vs_ihsg' => (float) $row['rs_20_vs_ihsg'],
                'sector_roc20' => $this->floatOrNull($row['sector_roc20'] ?? null),
                'rs_20_vs_sector' => $this->floatOrNull($row['rs_20_vs_sector'] ?? null),
                'sector_rs_20_vs_ihsg' => $this->floatOrNull($row['sector_rs_20_vs_ihsg'] ?? null),
                'corporate_action_flag' => $this->intFlagOrNull($row['corporate_action_flag'] ?? null),
                'corporate_action_types' => $this->stringOrNull($row['corporate_action_types'] ?? null),
                'trading_status_code' => WatchlistTradingStatusSnapshotNormalizer::normalize($row['trading_status_code'] ?? null),
                'is_suspended' => $this->intFlagOrNull($row['is_suspended'] ?? null),
                'is_uma' => $this->intFlagOrNull($row['is_uma'] ?? null),
                'event_risk_flag' => $this->intFlagOrNull($row['event_risk_flag'] ?? null),
                'event_risk_reasons' => $this->stringOrNull($row['event_risk_reasons'] ?? null),
            ],
        ];
    }

    private function candidateViolationCodes(array $row): array
    {
        $violations = [];

        if (($row['eligibility_state'] ?? 'ELIGIBLE') !== 'ELIGIBLE') {
            $violations[] = 'WATCHLIST_ELIGIBILITY_NOT_ELIGIBLE';
        }

        if (array_key_exists('indicator_is_valid', $row) && (int) $row['indicator_is_valid'] !== 1) {
            $violations[] = 'WATCHLIST_INDICATOR_INVALID';
        }

        if (! empty($row['indicator_invalid_reason_code'] ?? null)) {
            $violations[] = 'WATCHLIST_INDICATOR_INVALID_REASON_PRESENT';
        }

        foreach (self::REQUIRED_INDICATOR_FIELDS as $field) {
            if (! array_key_exists($field, $row) || $row[$field] === null || $row[$field] === '') {
                $violations[] = 'WATCHLIST_REQUIRED_FIELD_MISSING:'.$field;
            }
        }

        if (isset($row['close_price']) && (float) $row['close_price'] <= 0) {
            $violations[] = 'WATCHLIST_CLOSE_PRICE_NOT_POSITIVE';
        }

        if (isset($row['volume']) && (int) $row['volume'] <= 0) {
            $violations[] = 'WATCHLIST_VOLUME_NOT_POSITIVE';
        }

        return array_values(array_unique($violations));
    }

    private function sectorCodeOrNull($value): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $sectorCode = strtoupper(trim((string) $value));

        return $sectorCode === '' ? null : $sectorCode;
    }

    private function floatOrNull($value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function stringOrNull($value): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function intFlagOrNull($value): ?int
    {
        return $value === null || $value === '' ? null : ((int) $value === 1 ? 1 : 0);
    }
}
