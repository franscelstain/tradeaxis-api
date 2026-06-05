<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class EventRiskSourceRepository
{
    public function suspendedTickerIdsAsOf(array $tickerIds, $tradeDate): array
    {
        $contexts = $this->resolveEventRiskContextForTickerIds($tickerIds, $tradeDate);
        $suspended = [];

        foreach ($contexts as $tickerId => $context) {
            if ((int) ($context['is_suspended'] ?? 0) === 1) {
                $suspended[(int) $tickerId] = true;
            }
        }

        ksort($suspended);

        return array_keys($suspended);
    }

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
            ->select('ticker_id', 'trade_date', 'status_code', 'is_suspended', 'is_uma')
            ->whereIn('ticker_id', $tickerIds)
            ->where('trade_date', '<=', $tradeDate)
            ->orderBy('ticker_id')
            ->orderBy('trade_date')
            ->orderBy('status_code')
            ->get();

        $carryForwardStates = [];

        foreach ($tradingStatuses as $row) {
            $tickerId = (int) $row->ticker_id;
            $statusCode = $this->normalizeCode($row->status_code);

            if ((string) $row->trade_date === (string) $tradeDate) {
                $context = $contexts[$tickerId] ?? $this->emptyContext();
                $context = $this->applyTradingStatusRowToContext($context, $row, $statusCode);
                $contexts[$tickerId] = $context;
            }

            $carryForwardStates[$tickerId] = $this->applyCarryForwardTransition(
                $carryForwardStates[$tickerId] ?? $this->emptyCarryForwardState(),
                $row,
                $statusCode
            );
        }

        foreach ($carryForwardStates as $tickerId => $state) {
            $context = $contexts[$tickerId] ?? $this->emptyContext();
            $context = $this->applyCarryForwardStateToContext($context, $state);

            if (! empty($context['_trading_status_codes']) || $context['is_suspended'] !== null || $context['is_uma'] !== null) {
                $contexts[$tickerId] = $context;
            }
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

    private function applyTradingStatusRowToContext(array $context, $row, string $statusCode): array
    {
        if ($statusCode !== '') {
            $context['_trading_status_codes'][$statusCode] = true;
            if ($this->isRiskyStatusCode($statusCode)) {
                $context['event_risk_flag'] = 1;
                $context['_event_risk_reasons']['TRADING_STATUS:'.$statusCode] = true;
            }
        }

        if ($this->isGlobalNormalStatusCode($statusCode)) {
            $context['is_suspended'] = 0;
            $context['is_uma'] = 0;
        } elseif ($this->isSuspensionEndStatusCode($statusCode)) {
            $context['is_suspended'] = 0;
        }

        if ($row->is_suspended !== null) {
            $context['is_suspended'] = (int) $row->is_suspended === 1 ? 1 : 0;
        } elseif ($this->isSuspensionStartStatusCode($statusCode)) {
            $context['is_suspended'] = 1;
        }

        if ($context['is_suspended'] === 1) {
            $context['event_risk_flag'] = 1;
            $context['_event_risk_reasons']['SUSPENDED'] = true;
        }

        if ($row->is_uma !== null) {
            $context['is_uma'] = (int) $row->is_uma === 1 ? 1 : 0;
        } elseif (strpos($statusCode, 'UMA') !== false) {
            $context['is_uma'] = 1;
        }

        if ($context['is_uma'] === 1) {
            $context['event_risk_flag'] = 1;
            $context['_event_risk_reasons']['UMA'] = true;
        }

        return $context;
    }

    private function applyCarryForwardTransition(array $state, $row, string $statusCode): array
    {
        if ($statusCode === '') {
            return $state;
        }

        if ($this->isGlobalNormalStatusCode($statusCode)) {
            $state['suspension'] = null;
            $state['normal'] = $this->carryForwardStateRow($row, $statusCode, 0, 0);

            return $state;
        }

        if ($this->isSuspensionEndStatusCode($statusCode)) {
            $state['suspension'] = null;
            $state['normal'] = $this->carryForwardStateRow($row, $statusCode, 0, null);

            return $state;
        }

        if ($this->isSpecialMonitoringEndStatusCode($statusCode)) {
            $state['special_monitoring'] = null;
            $state['normal'] = $this->carryForwardStateRow($row, $statusCode, null, null);

            return $state;
        }

        if ((int) $row->is_suspended === 1 || $this->isSuspensionStartStatusCode($statusCode)) {
            $state['suspension'] = $this->carryForwardStateRow($row, $statusCode, 1, null);
            $state['normal'] = null;
        }

        if ($this->isSpecialMonitoringStartStatusCode($statusCode)) {
            $state['special_monitoring'] = $this->carryForwardStateRow($row, $statusCode, null, null);
            $state['normal'] = null;
        }

        return $state;
    }

    private function applyCarryForwardStateToContext(array $context, array $state): array
    {
        $hasRiskState = false;

        if ($state['normal'] !== null) {
            $context = $this->applyTradingStatusStateToContext($context, $state['normal']);
        }

        foreach (['suspension', 'special_monitoring'] as $stateKey) {
            if ($state[$stateKey] === null) {
                continue;
            }

            $context = $this->applyTradingStatusStateToContext($context, $state[$stateKey]);
            $hasRiskState = true;
        }

        return $context;
    }

    private function applyTradingStatusStateToContext(array $context, array $state): array
    {
        $statusCode = $state['status_code'];

        if ($statusCode !== '') {
            $context['_trading_status_codes'][$statusCode] = true;
            if ($this->isRiskyStatusCode($statusCode)) {
                $context['event_risk_flag'] = 1;
                $context['_event_risk_reasons']['TRADING_STATUS:'.$statusCode] = true;
            }
        }

        if ($state['is_suspended'] !== null) {
            $context['is_suspended'] = (int) $state['is_suspended'] === 1 ? 1 : 0;
            if ((int) $state['is_suspended'] === 1) {
                $context['event_risk_flag'] = 1;
                $context['_event_risk_reasons']['SUSPENDED'] = true;
            }
        }

        if ($state['is_uma'] !== null) {
            $context['is_uma'] = (int) $state['is_uma'] === 1 ? 1 : 0;
            if ((int) $state['is_uma'] === 1) {
                $context['event_risk_flag'] = 1;
                $context['_event_risk_reasons']['UMA'] = true;
            }
        }

        return $context;
    }

    private function emptyCarryForwardState(): array
    {
        return [
            'suspension' => null,
            'special_monitoring' => null,
            'normal' => null,
        ];
    }

    private function carryForwardStateRow($row, string $statusCode, $isSuspended, $isUma): array
    {
        return [
            'trade_date' => (string) $row->trade_date,
            'status_code' => $statusCode,
            'is_suspended' => $row->is_suspended !== null ? (int) $row->is_suspended : $isSuspended,
            'is_uma' => $row->is_uma !== null ? (int) $row->is_uma : $isUma,
        ];
    }

    private function isRiskyStatusCode($statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        if ($statusCode === '' || $this->isNormalStatusCode($statusCode)) {
            return false;
        }

        foreach (['SUSPEND', 'SUSPENDED', 'UMA', 'HALT', 'HALTED', 'SPECIAL_MONITORING', 'SPECIAL_NOTATION', 'NOTASI_KHUSUS', 'WATCHLIST'] as $needle) {
            if (strpos($statusCode, $needle) !== false) {
                return true;
            }
        }

        return true;
    }

    private function isNormalStatusCode(string $statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        return $this->isGlobalNormalStatusCode($statusCode)
            || $this->isSuspensionEndStatusCode($statusCode)
            || $this->isSpecialMonitoringEndStatusCode($statusCode);
    }

    private function isGlobalNormalStatusCode(string $statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        return in_array($statusCode, ['ACTIVE', 'NORMAL', 'OPEN', 'REGULAR', 'RESUMED', 'RESUME_TRADING'], true);
    }

    private function isSuspensionStartStatusCode(string $statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        return (strpos($statusCode, 'SUSPEND') !== false || strpos($statusCode, 'HALT') !== false)
            && ! $this->isSuspensionEndStatusCode($statusCode);
    }

    private function isSuspensionEndStatusCode(string $statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        foreach (['UNSUSPEND', 'RESUME', 'SUSPENSION_LIFTED', 'SUSPEND_LIFTED', 'LIFTED_SUSPENSION'] as $needle) {
            if (strpos($statusCode, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isSpecialMonitoringStartStatusCode(string $statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        if ($this->isSpecialMonitoringEndStatusCode($statusCode)) {
            return false;
        }

        foreach (['SPECIAL_MONITORING', 'SPECIAL_NOTATION', 'NOTASI_KHUSUS', 'WATCHLIST'] as $needle) {
            if (strpos($statusCode, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isSpecialMonitoringEndStatusCode(string $statusCode): bool
    {
        $statusCode = $this->normalizeCode($statusCode);

        foreach (['SPECIAL_MONITORING_EXIT', 'SPECIAL_MONITORING_REMOVED', 'REMOVED_FROM_SPECIAL_MONITORING', 'WATCHLIST_EXIT', 'WATCHLIST_REMOVED', 'SPECIAL_NOTATION_EXIT', 'SPECIAL_NOTATION_REMOVED', 'NOTASI_KHUSUS_EXIT', 'NOTASI_KHUSUS_REMOVED'] as $needle) {
            if (strpos($statusCode, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function normalizeCode($value): string
    {
        $code = strtoupper(trim((string) $value));
        $code = preg_replace('/[^A-Z0-9]+/', '_', $code);

        return trim((string) $code, '_');
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
