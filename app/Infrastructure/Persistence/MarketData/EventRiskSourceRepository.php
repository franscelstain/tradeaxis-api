<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class EventRiskSourceRepository
{
    public function resolveEventRiskContextForTickerIds(array $tickerIds, $tradeDate): array
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        if (empty($tickerIds)) {
            return [];
        }

        $contexts = [];

        $corporateActions = DB::table($this->corporateActionsTable())
            ->select('ticker_id', 'action_type')
            ->whereIn('ticker_id', $tickerIds)
            ->where('action_date', $tradeDate)
            ->orderBy('ticker_id')
            ->orderBy('action_type')
            ->get();

        foreach ($corporateActions as $row) {
            $tickerId = (int) $row->ticker_id;
            $context = $contexts[$tickerId] ?? $this->emptyContext();
            $actionType = $this->normalizeCode($row->action_type);

            if ($actionType !== '') {
                $context['corporate_action_flag'] = 1;
                $context['_corporate_action_types'][$actionType] = true;
                $context['_event_risk_reasons']['CORPORATE_ACTION:'.$actionType] = true;
                $context['event_risk_flag'] = 1;
            }

            $contexts[$tickerId] = $context;
        }

        $tradingStatuses = DB::table($this->tradingStatusesTable())
            ->select('ticker_id', 'status_code', 'is_suspended', 'is_uma')
            ->whereIn('ticker_id', $tickerIds)
            ->where('trade_date', $tradeDate)
            ->orderBy('ticker_id')
            ->orderBy('status_code')
            ->get();

        foreach ($tradingStatuses as $row) {
            $tickerId = (int) $row->ticker_id;
            $context = $contexts[$tickerId] ?? $this->emptyContext();
            $statusCode = $this->normalizeCode($row->status_code);

            if ($statusCode !== '') {
                $context['_trading_status_codes'][$statusCode] = true;
                if ($this->isRiskyStatusCode($statusCode)) {
                    $context['event_risk_flag'] = 1;
                    $context['_event_risk_reasons']['TRADING_STATUS:'.$statusCode] = true;
                }
            }

            if ($row->is_suspended !== null) {
                $context['is_suspended'] = (int) $row->is_suspended === 1 ? 1 : 0;
                if ((int) $row->is_suspended === 1) {
                    $context['event_risk_flag'] = 1;
                    $context['_event_risk_reasons']['SUSPENDED'] = true;
                }
            }

            if ($row->is_uma !== null) {
                $context['is_uma'] = (int) $row->is_uma === 1 ? 1 : 0;
                if ((int) $row->is_uma === 1) {
                    $context['event_risk_flag'] = 1;
                    $context['_event_risk_reasons']['UMA'] = true;
                }
            }

            $contexts[$tickerId] = $context;
        }

        foreach ($contexts as $tickerId => $context) {
            $contexts[$tickerId] = $this->finalizeContext($context);
        }

        ksort($contexts);

        return $contexts;
    }

    public function upsertCorporateAction(array $row): bool
    {
        $now = $row['updated_at'] ?? date('Y-m-d H:i:s');

        return DB::table($this->corporateActionsTable())->updateOrInsert(
            [
                'ticker_id' => (int) $row['ticker_id'],
                'action_date' => $row['action_date'],
                'action_type' => $this->normalizeCode($row['action_type']),
                'source_name' => $row['source_name'],
            ],
            [
                'ticker_code' => $this->normalizeCode($row['ticker_code']),
                'source_ref' => $row['source_ref'] ?? null,
                'notes' => $row['notes'] ?? null,
                'created_at' => $row['created_at'] ?? $now,
                'updated_at' => $now,
            ]
        );
    }

    public function upsertTradingStatusEvent(array $row): bool
    {
        $now = $row['updated_at'] ?? date('Y-m-d H:i:s');

        return DB::table($this->tradingStatusesTable())->updateOrInsert(
            [
                'ticker_id' => (int) $row['ticker_id'],
                'trade_date' => $row['trade_date'],
                'status_code' => $this->normalizeCode($row['status_code']),
                'source_name' => $row['source_name'],
            ],
            [
                'ticker_code' => $this->normalizeCode($row['ticker_code']),
                'is_suspended' => $row['is_suspended'],
                'is_uma' => $row['is_uma'],
                'source_ref' => $row['source_ref'] ?? null,
                'notes' => $row['notes'] ?? null,
                'created_at' => $row['created_at'] ?? $now,
                'updated_at' => $now,
            ]
        );
    }

    private function emptyContext(): array
    {
        return [
            'corporate_action_flag' => null,
            'corporate_action_types' => null,
            'trading_status_code' => null,
            'is_suspended' => null,
            'is_uma' => null,
            'event_risk_flag' => null,
            'event_risk_reasons' => null,
            '_corporate_action_types' => [],
            '_trading_status_codes' => [],
            '_event_risk_reasons' => [],
        ];
    }

    private function finalizeContext(array $context): array
    {
        $corporateActionTypes = array_keys($context['_corporate_action_types']);
        $tradingStatusCodes = array_keys($context['_trading_status_codes']);
        $eventRiskReasons = array_keys($context['_event_risk_reasons']);

        sort($corporateActionTypes);
        sort($tradingStatusCodes);
        sort($eventRiskReasons);

        $context['corporate_action_types'] = ! empty($corporateActionTypes) ? implode(',', $corporateActionTypes) : null;
        $context['trading_status_code'] = ! empty($tradingStatusCodes) ? implode(',', $tradingStatusCodes) : null;
        $context['event_risk_reasons'] = ! empty($eventRiskReasons) ? implode(',', $eventRiskReasons) : null;

        if ($context['event_risk_flag'] === null && (! empty($tradingStatusCodes) || $context['is_suspended'] !== null || $context['is_uma'] !== null)) {
            $context['event_risk_flag'] = 0;
        }

        unset($context['_corporate_action_types'], $context['_trading_status_codes'], $context['_event_risk_reasons']);

        return $context;
    }

    private function isRiskyStatusCode($statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        if ($statusCode === '' || in_array($statusCode, ['ACTIVE', 'NORMAL', 'OPEN', 'REGULAR'], true)) {
            return false;
        }

        foreach (['SUSPEND', 'SUSPENDED', 'UMA', 'HALT', 'HALTED', 'SPECIAL_MONITORING', 'WATCHLIST'] as $needle) {
            if (strpos($statusCode, $needle) !== false) {
                return true;
            }
        }

        return true;
    }

    private function normalizeCode($value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function corporateActionsTable(): string
    {
        return config('market_data.event_risk.corporate_actions_table', 'market_data_corporate_actions');
    }

    private function tradingStatusesTable(): string
    {
        return config('market_data.event_risk.trading_status_events_table', 'market_data_trading_status_events');
    }
}
