<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class EventRiskSourceRepository
{
    private const DEFAULT_TRADING_STATUS_EVENT_TYPES = [
        'SUSPENDED' => [
            'risk_family' => 'SUSPENSION',
            'transition_type' => 'START',
            'expected_bar_policy' => 'BAR_NOT_REQUIRED',
            'carries_forward' => 1,
            'clears_risk_family' => null,
        ],
        'SUSPENSION_OBSERVED' => [
            'risk_family' => 'SUSPENSION',
            'transition_type' => 'OBSERVED',
            'expected_bar_policy' => 'BAR_NOT_REQUIRED',
            'carries_forward' => 1,
            'clears_risk_family' => null,
        ],
        'UNSUSPENDED' => [
            'risk_family' => 'SUSPENSION',
            'transition_type' => 'END',
            'expected_bar_policy' => 'BAR_REQUIRED',
            'carries_forward' => 0,
            'clears_risk_family' => 'SUSPENSION',
        ],
        'SPECIAL_MONITORING_START' => [
            'risk_family' => 'SPECIAL_MONITORING',
            'transition_type' => 'START',
            'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK',
            'carries_forward' => 1,
            'clears_risk_family' => null,
        ],
        'SPECIAL_MONITORING_END' => [
            'risk_family' => 'SPECIAL_MONITORING',
            'transition_type' => 'END',
            'expected_bar_policy' => 'BAR_REQUIRED',
            'carries_forward' => 0,
            'clears_risk_family' => 'SPECIAL_MONITORING',
        ],
        'UMA' => [
            'risk_family' => 'UMA',
            'transition_type' => 'POINT_IN_TIME',
            'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK',
            'carries_forward' => 0,
            'clears_risk_family' => null,
        ],
    ];

    private ?array $tradingStatusEventTypes = null;

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
            ->select(['ticker_id', 'trade_date', 'event_type_code'])
            ->whereIn('ticker_id', $tickerIds)
            ->where('trade_date', '<=', $tradeDate)
            ->orderBy('ticker_id')
            ->orderBy('trade_date')
            ->orderBy('event_type_code')
            ->get();

        $carryForwardStates = [];

        foreach ($tradingStatuses as $row) {
            $tickerId = (int) $row->ticker_id;
            $eventTypeCode = $this->normalizeCode($row->event_type_code);
            $eventType = $this->tradingStatusEventType($eventTypeCode);

            if ($eventType === null) {
                continue;
            }

            if ((string) $row->trade_date === (string) $tradeDate) {
                $context = $contexts[$tickerId] ?? $this->emptyContext();
                $context = $this->applyTradingStatusEventToContext($context, $eventTypeCode, $eventType, true);
                $contexts[$tickerId] = $context;
            }

            $carryForwardStates[$tickerId] = $this->applyCarryForwardTransition(
                $carryForwardStates[$tickerId] ?? [],
                $row,
                $eventTypeCode,
                $eventType
            );
        }

        foreach ($carryForwardStates as $tickerId => $state) {
            $context = $contexts[$tickerId] ?? $this->emptyContext();

            foreach ($state as $eventTypeCode => $eventType) {
                $context = $this->applyTradingStatusEventToContext($context, $eventTypeCode, $eventType, false);
            }

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
        $eventTypeCode = $this->normalizeCode($row['event_type_code'] ?? '');

        if ($this->tradingStatusEventType($eventTypeCode) === null) {
            throw new \InvalidArgumentException('Unknown trading status event_type_code: '.$eventTypeCode);
        }

        return DB::table($this->tradingStatusesTable())->updateOrInsert(
            [
                'ticker_id' => (int) $row['ticker_id'],
                'trade_date' => $row['trade_date'],
                'event_type_code' => $eventTypeCode,
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

    public function tradingStatusEventTypeCodes(): array
    {
        $codes = array_keys($this->tradingStatusEventTypes());
        sort($codes);

        return $codes;
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
            '_trading_status_exact_codes' => [],
            '_event_risk_reasons' => [],
        ];
    }

    private function finalizeContext(array $context): array
    {
        $corporateActionTypes = array_keys($context['_corporate_action_types']);
        $tradingStatusCodes = array_keys($context['_trading_status_codes']);
        $exactTradingStatusCodes = array_keys($context['_trading_status_exact_codes']);
        $eventRiskReasons = array_keys($context['_event_risk_reasons']);

        sort($corporateActionTypes);
        sort($tradingStatusCodes);
        sort($exactTradingStatusCodes);
        sort($eventRiskReasons);

        $context['corporate_action_types'] = ! empty($corporateActionTypes) ? implode(',', $corporateActionTypes) : null;
        $context['trading_status_code'] = $this->primaryTradingStatusCode($tradingStatusCodes, $exactTradingStatusCodes);
        $context['event_risk_reasons'] = ! empty($eventRiskReasons) ? implode(',', $eventRiskReasons) : null;

        if (! empty($tradingStatusCodes)) {
            $context['is_suspended'] = $context['is_suspended'] !== null ? $context['is_suspended'] : 0;
            $context['is_uma'] = $context['is_uma'] !== null ? $context['is_uma'] : 0;
        }

        if ($context['event_risk_flag'] === null && (! empty($tradingStatusCodes) || $context['is_suspended'] !== null || $context['is_uma'] !== null)) {
            $context['event_risk_flag'] = 0;
        }

        unset($context['_corporate_action_types'], $context['_trading_status_codes'], $context['_trading_status_exact_codes'], $context['_event_risk_reasons']);

        return $context;
    }

    private function primaryTradingStatusCode(array $tradingStatusCodes, array $exactTradingStatusCodes): ?string
    {
        if (empty($tradingStatusCodes)) {
            return null;
        }

        $priority = [
            'SUSPENDED',
            'SUSPENSION_OBSERVED',
            'UNSUSPENDED',
            'SPECIAL_MONITORING_START',
            'SPECIAL_MONITORING_END',
            'UMA',
        ];

        $exactSet = array_fill_keys($exactTradingStatusCodes, true);
        foreach ($priority as $code) {
            if (isset($exactSet[$code])) {
                return $code;
            }
        }

        $statusSet = array_fill_keys($tradingStatusCodes, true);
        foreach ($priority as $code) {
            if (isset($statusSet[$code])) {
                return $code;
            }
        }

        sort($tradingStatusCodes);

        return $tradingStatusCodes[0] ?? null;
    }

    private function applyTradingStatusEventToContext(array $context, string $eventTypeCode, array $eventType, bool $isExactDateEvent = false): array
    {
        $context['_trading_status_codes'][$eventTypeCode] = true;

        if ($isExactDateEvent) {
            $context['_trading_status_exact_codes'][$eventTypeCode] = true;
        }

        if ($eventType['risk_family'] === 'SUSPENSION') {
            $context['is_suspended'] = in_array($eventType['transition_type'], ['START', 'OBSERVED'], true) ? 1 : 0;
        }

        if ($eventTypeCode === 'UMA') {
            $context['is_uma'] = 1;
        }

        if (in_array($eventType['expected_bar_policy'] ?? 'BAR_REQUIRED', ['BAR_NOT_REQUIRED', 'BAR_REQUIRED_WITH_RISK'], true)) {
            $context['event_risk_flag'] = 1;
            $context['_event_risk_reasons']['TRADING_STATUS:'.$eventTypeCode] = true;

            if ($eventType['risk_family'] === 'SUSPENSION') {
                $context['_event_risk_reasons']['SUSPENDED'] = true;
            }

            if ($eventTypeCode === 'UMA') {
                $context['_event_risk_reasons']['UMA'] = true;
            }
        }

        return $context;
    }

    private function applyCarryForwardTransition(array $state, $row, string $eventTypeCode, array $eventType): array
    {
        if ($eventType['transition_type'] === 'END' && $eventType['clears_risk_family'] !== null) {
            foreach ($state as $activeEventTypeCode => $activeEventType) {
                if ($activeEventType['risk_family'] === $eventType['clears_risk_family']) {
                    unset($state[$activeEventTypeCode]);
                }
            }

            return $state;
        }

        if ((int) $eventType['carries_forward'] === 1) {
            foreach ($state as $activeEventTypeCode => $activeEventType) {
                if ($activeEventType['risk_family'] === $eventType['risk_family']) {
                    unset($state[$activeEventTypeCode]);
                }
            }

            $state[$eventTypeCode] = $eventType;
        }

        ksort($state);

        return $state;
    }

    private function tradingStatusEventType(string $eventTypeCode): ?array
    {
        $eventTypes = $this->tradingStatusEventTypes();

        return $eventTypes[$eventTypeCode] ?? null;
    }

    private function tradingStatusEventTypes(): array
    {
        if ($this->tradingStatusEventTypes !== null) {
            return $this->tradingStatusEventTypes;
        }

        $eventTypes = self::DEFAULT_TRADING_STATUS_EVENT_TYPES;

        try {
            $policyColumn = $this->hasColumn($this->tradingStatusEventTypesTable(), 'expected_bar_policy')
                ? 'expected_bar_policy'
                : 'coverage_policy';

            $rows = DB::table($this->tradingStatusEventTypesTable())
                ->select(['event_type_code', 'risk_family', 'transition_type', $policyColumn, 'carries_forward', 'clears_risk_family'])
                ->orderBy('event_type_code')
                ->get();

            if (count($rows) > 0) {
                $eventTypes = [];
                foreach ($rows as $row) {
                    $eventTypes[$this->normalizeCode($row->event_type_code)] = [
                        'risk_family' => $this->normalizeCode($row->risk_family),
                        'transition_type' => $this->normalizeCode($row->transition_type),
                        'expected_bar_policy' => $this->normalizeExpectedBarPolicy($row->{$policyColumn}),
                        'carries_forward' => (int) $row->carries_forward === 1 ? 1 : 0,
                        'clears_risk_family' => $row->clears_risk_family !== null && trim((string) $row->clears_risk_family) !== ''
                            ? $this->normalizeCode($row->clears_risk_family)
                            : null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $eventTypes = self::DEFAULT_TRADING_STATUS_EVENT_TYPES;
        }

        $this->tradingStatusEventTypes = $eventTypes;

        return $this->tradingStatusEventTypes;
    }


    private function normalizeExpectedBarPolicy($value): string
    {
        $policy = $this->normalizeCode($value);

        if ($policy === 'EXCLUDE') {
            return 'BAR_NOT_REQUIRED';
        }

        if ($policy === 'INCLUDE_WITH_RISK') {
            return 'BAR_REQUIRED_WITH_RISK';
        }

        if ($policy === 'INCLUDE') {
            return 'BAR_REQUIRED';
        }

        if (in_array($policy, ['BAR_REQUIRED', 'BAR_NOT_REQUIRED', 'BAR_REQUIRED_WITH_RISK'], true)) {
            return $policy;
        }

        return 'BAR_REQUIRED';
    }

    private function hasColumn(string $tableName, string $columnName): bool
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                $rows = DB::select(
                    'SELECT COUNT(*) as aggregate FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$tableName, $columnName]
                );

                return isset($rows[0]) && (int) $rows[0]->aggregate > 0;
            }

            return \Illuminate\Support\Facades\Schema::hasColumn($tableName, $columnName);
        } catch (\Throwable $e) {
            return false;
        }
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

    private function tradingStatusEventTypesTable(): string
    {
        return config('market_data.event_risk.trading_status_event_types_table', 'market_data_trading_status_event_types');
    }
}
