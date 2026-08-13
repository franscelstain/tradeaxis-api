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

    /**
     * Fallback taxonomy used when the dictionary table is unavailable.
     *
     * Owner contract: docs/market_data/registry/Corporate_Action_Type_Registry_LOCKED.md
     */
    private const DEFAULT_CORPORATE_ACTION_TYPES = [
        // Share unit redefined: historical price and volume are in different units.
        'STOCK_SPLIT' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'],
        'REVERSE_STOCK_SPLIT' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'],
        'BONUS_SHARE' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'],
        'STOCK_DIVIDEND' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'],
        'MERGER' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'],

        // Price series rescaled, share unit unchanged.
        'RIGHTS_ISSUE' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'NONE'],
        'CASH_DIVIDEND' => ['price_continuity_impact' => 'GAP_UNKNOWN_MAGNITUDE', 'volume_continuity_impact' => 'NONE'],

        // Dilution only: new shares issued at the existing unit, no ex-price adjustment.
        'PRIVATE_PLACEMENT' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'NON_PREEMPTIVE_RIGHTS_ISSUE' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'WARRANT' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'WARRANT_EXERCISE' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'MANDATORY_CONVERTIBLE_BOND' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'ESOP_MSOP' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],

        // Lifecycle and identity events: no continuity to break.
        'IPO' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'DELISTING' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'PARTIAL_DELISTING' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'PARTIAL_RELISTING' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'CAPITAL_DEFICIENCY' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'TICKER_CODE_CHANGE' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],
        'COMPANY_NAME_CHANGE' => ['price_continuity_impact' => 'NONE', 'volume_continuity_impact' => 'NONE'],

        // Proven by the price series, exact type not yet confirmed by an operator.
        'PRICE_RESCALE_UNCLASSIFIED' => ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'],
    ];

    /**
     * Fail-safe impact for an action type that has no dictionary row.
     *
     * An unmapped corporate action is an unknown, not a safe one. Treating it as
     * non-breaking would silently publish contaminated arithmetic.
     */
    private const UNMAPPED_CORPORATE_ACTION_IMPACT = [
        'price_continuity_impact' => 'SCALED',
        'volume_continuity_impact' => 'SCALED',
    ];

    /**
     * Closed vocabulary for where an adjustment factor came from.
     *
     * The column existed with no declared vocabulary at all: only DERIVED_FROM_PRICE_SERIES was
     * ever written, by the platform's own inference, and isAdjustable() refused exactly that. The
     * result was a closed loop — the only factors the platform could produce were the ones it
     * refused to use — and no free-text value would have been caught, because nothing defined what
     * a valid value was.
     *
     * DERIVED_FROM_PRICE_SERIES is listed here so it is a known member, not so it is usable. It is
     * deliberately absent from the authoritative set, and the importer refuses it outright: a
     * platform inference must not be launderable into a source by writing it in a CSV.
     */
    public const ADJUSTMENT_SOURCES = [
        'EXCHANGE_ANNOUNCEMENT',
        'DEPOSITORY_SCHEDULE',
        'OPERATOR_ENTERED',
        'DERIVED_FROM_PRICE_SERIES',
    ];

    public const AUTHORITATIVE_ADJUSTMENT_SOURCES = [
        'EXCHANGE_ANNOUNCEMENT',
        'DEPOSITORY_SCHEDULE',
        'OPERATOR_ENTERED',
    ];

    private ?array $tradingStatusEventTypes = null;

    private ?array $corporateActionTypes = null;

    /**
     * Restrict a query to what the platform knew at $knownAt.
     *
     * A NULL recorded_at is excluded rather than assumed to predate the cutoff. An undated row
     * cannot be placed on the knowledge timeline at all, and assuming it is old enough would let it
     * leak into every replay — which is the exact failure this cutoff exists to stop.
     */
    private function applyKnowledgeCutoff($query, $knownAt, string $column = 'recorded_at')
    {
        if ($knownAt === null || $knownAt === '') {
            return $query;
        }

        return $query->whereNotNull($column)->where($column, '<=', $knownAt);
    }

    public function suspendedTickerIdsAsOf(array $tickerIds, $tradeDate, $knownAt = null): array
    {
        $contexts = $this->resolveEventRiskContextForTickerIds($tickerIds, $tradeDate, $knownAt);
        $suspended = [];

        foreach ($contexts as $tickerId => $context) {
            /*
             * A listing whose expectation evidence is incomplete may not be excluded from the
             * denominator, even when a suspension flag was also derived for it. It is `UNKNOWN`,
             * and `Coverage_Universe_Definition_LOCKED.md:21` keeps `UNKNOWN` in and visible —
             * only verified `NOT_EXPECTED` may leave.
             */
            if ((int) ($context['expectation_unknown'] ?? 0) === 1) {
                continue;
            }

            if ((int) ($context['is_suspended'] ?? 0) === 1) {
                $suspended[(int) $tickerId] = true;
            }
        }

        ksort($suspended);

        return array_keys($suspended);
    }

    /**
     * Listings whose expectation evidence is incomplete or conflicting on the date.
     *
     * `Coverage_Universe_Definition_LOCKED.md:52` requires the `UNKNOWN` count to be recorded
     * alongside `EXPECTED` and `NOT_EXPECTED`. It was never computed, so the pipeline wrote a hard
     * zero and the state was indistinguishable from `EXPECTED`.
     */
    public function expectationUnknownTickerIdsAsOf(array $tickerIds, $tradeDate, $knownAt = null): array
    {
        $unknown = [];

        foreach ($this->resolveEventRiskContextForTickerIds($tickerIds, $tradeDate, $knownAt) as $tickerId => $context) {
            if ((int) ($context['expectation_unknown'] ?? 0) === 1) {
                $unknown[(int) $tickerId] = true;
            }
        }

        ksort($unknown);

        return array_keys($unknown);
    }

    public function resolveEventRiskContextForTickerIds(array $tickerIds, $tradeDate, $knownAt = null): array
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        if (empty($tickerIds)) {
            return [];
        }

        $contexts = [];

        $corporateActions = $this->applyKnowledgeCutoff(
            DB::table($this->corporateActionsTable())
                ->select('ticker_id', 'action_type')
                ->whereIn('ticker_id', $tickerIds)
                ->where('action_date', $tradeDate),
            $knownAt
        )
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

        $tradingStatuses = $this->applyKnowledgeCutoff(
            DB::table($this->tradingStatusesTable())
                ->select(['ticker_id', 'trade_date', 'event_type_code'])
                ->whereIn('ticker_id', $tickerIds)
                ->where('trade_date', '<=', $tradeDate),
            $knownAt
        )
            ->orderBy('ticker_id')
            ->orderBy('trade_date')
            ->orderBy('event_type_code')
            ->get();

        $carryForwardStates = [];

        foreach ($tradingStatuses as $row) {
            $tickerId = (int) $row->ticker_id;
            $eventTypeCode = $this->normalizeCode($row->event_type_code);
            $eventType = $this->tradingStatusEventType($eventTypeCode);

            /*
             * An event type the dictionary does not define is evidence the platform holds and
             * cannot interpret, which `Coverage_Universe_Definition_LOCKED.md:19` calls incomplete
             * expectation evidence — `UNKNOWN`, not absence.
             *
             * It used to `continue`, which discarded the row silently and left the listing looking
             * plainly `EXPECTED`. The corporate-action path already treats an unmapped type
             * fail-safe; this one dropped it. `:21` forbids exactly that direction of silence.
             */
            if ($eventType === null) {
                $context = $contexts[$tickerId] ?? $this->emptyContext();
                $context['expectation_unknown'] = 1;
                $context['event_risk_flag'] = 1;
                $context['_event_risk_reasons']['TRADING_STATUS_TYPE_UNMAPPED:'.$eventTypeCode] = true;
                $contexts[$tickerId] = $context;

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

    /**
     * Resolve corporate actions whose price/volume discontinuity may still poison an
     * indicator window ending on the last date of $tradingDates.
     *
     * Owner contract: docs/market_data/registry/Indicator_Registry_Baseline_LOCKED.md
     * (Amendment 2026-07-29 - Corporate action window contamination)
     *
     * $tradingDates must be the ascending canonical trading-day sequence ending on the
     * requested date. Depth is expressed in trading days back from that date, where the
     * requested date itself is depth 0. An indicator with contamination horizon W is
     * contaminated by an entry when depth < W.
     *
     * Actions carrying no price and no volume impact are omitted: they cannot contaminate
     * anything, so returning them would only create noise in the audit trail.
     *
     * @return array<int, array<int, array>> keyed by ticker_id
     */
    public function resolveCorporateActionContaminationForTickerIds(array $tickerIds, array $tradingDates, $knownAt = null): array
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        $tradingDates = array_values(array_map('strval', $tradingDates));

        if (empty($tickerIds) || empty($tradingDates)) {
            return [];
        }

        $windowStart = $tradingDates[0];
        $windowEnd = $tradingDates[count($tradingDates) - 1];

        $rows = $this->applyKnowledgeCutoff(
            DB::table($this->corporateActionsTable())
                ->select(['ticker_id', 'action_date', 'ex_date', 'action_type', 'price_adjustment_factor', 'continuity_check_status', 'adjustment_source'])
                ->whereIn('ticker_id', $tickerIds)
                ->where('action_date', '>=', $windowStart)
                ->where('action_date', '<=', $windowEnd),
            $knownAt
        )
            ->orderBy('ticker_id')
            ->orderBy('action_date')
            ->orderBy('action_type')
            ->get();

        /*
         * One event can be recorded twice: once as the platform's own price-series hypothesis, and
         * again when the exchange terms arrive. The hypothesis is not adjustable and would keep
         * quarantining a window that the authoritative factor now adjusts correctly — the series
         * would be rescaled and then nulled anyway, which is the worst of both.
         *
         * A hypothesis about an event is answered once that event's terms are known. So a row that
         * cannot adjust is set aside when another row for the same instrument, type and effective
         * date can.
         */
        $coveredEvents = [];
        foreach ($rows as $row) {
            if ($this->isAdjustable($row)) {
                $coveredEvents[$this->corporateActionEventKey($row)] = true;
            }
        }

        $types = $this->corporateActionTypes();
        $contamination = [];

        foreach ($rows as $row) {
            $actionTypeCode = $this->normalizeCode($row->action_type);

            if ($actionTypeCode === '') {
                continue;
            }

            if (! $this->isAdjustable($row) && isset($coveredEvents[$this->corporateActionEventKey($row)])) {
                continue;
            }

            // An event carrying a usable factor is adjusted in the indicator window, so the
            // series is continuous and there is nothing left to quarantine. Recording the
            // action alone never achieves this; only the factor does.
            if ($this->isAdjustable($row)) {
                continue;
            }

            // The series was checked and shows no material discontinuity at this event. The
            // declared impact is an expectation; the observed series is evidence, and
            // quarantining a demonstrably continuous window protects nothing.
            //
            // Only NO_MATERIAL_GAP releases. A release keyed on "the break detector found
            // nothing" was tried and reverted: detection has a floor of min_ratio 1.7, about a
            // 41% move, and every ambiguous action sits below it. Absence of a detection there is
            // not evidence of absence — it is the detector not looking.
            if (property_exists($row, 'continuity_check_status') && $row->continuity_check_status === 'NO_MATERIAL_GAP') {
                continue;
            }

            $depth = $this->tradingDayDepth($tradingDates, (string) $row->action_date);

            if ($depth === null) {
                continue;
            }

            $isUnmapped = ! isset($types[$actionTypeCode]);
            $impact = $isUnmapped ? self::UNMAPPED_CORPORATE_ACTION_IMPACT : $types[$actionTypeCode];

            $breaksPrice = $impact['price_continuity_impact'] !== 'NONE';
            $breaksVolume = $impact['volume_continuity_impact'] !== 'NONE';

            if (! $breaksPrice && ! $breaksVolume) {
                continue;
            }

            $contamination[(int) $row->ticker_id][] = [
                'action_type_code' => $actionTypeCode,
                'action_date' => (string) $row->action_date,
                'depth' => $depth,
                'breaks_price_continuity' => $breaksPrice,
                'breaks_volume_continuity' => $breaksVolume,
                'is_unmapped_type' => $isUnmapped,
            ];
        }

        ksort($contamination);

        return $contamination;
    }

    /**
     * Price adjustment factors effective inside the window, keyed by ticker_id.
     *
     * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
     *
     * Keyed on ex_date rather than action_date because the recorded action date does not
     * reliably equal the date the scale changed. RMKE's split is recorded on 2026-07-17
     * against a price break on 2026-07-15.
     *
     * @return array<int, array<int, array{ex_date:string, price_factor:float, volume_factor:float}>>
     */
    public function resolveAdjustmentFactorsForTickerIds(array $tickerIds, $windowStart, $windowEnd, $knownAt = null): array
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        if (empty($tickerIds)) {
            return [];
        }

        $rows = $this->applyKnowledgeCutoff(
            DB::table($this->corporateActionsTable().' as action')
                ->leftJoin('md_listings as listing', 'listing.legacy_ticker_id', '=', 'action.ticker_id'),
            $knownAt,
            'action.recorded_at'
        )
            ->select([
                'action.corporate_action_id', 'action.ticker_id', 'listing.listing_id',
                'action.ex_date', 'action.action_date', 'action.price_adjustment_factor',
                'action.volume_adjustment_factor', 'action.adjustment_source', 'action.updated_at',
            ])
            ->whereIn('action.ticker_id', $tickerIds)
            ->whereNotNull('action.price_adjustment_factor')
            ->where('action.price_adjustment_factor', '>', 0)
            ->where('action.price_adjustment_factor', '<>', 1)
            // Only an attributed factor may adjust published output. This is the same positive
            // allowlist isAdjustable() applies, stated once in both places so the query cannot
            // admit a row the guard would refuse. A factor the platform inferred from the price
            // series, and a factor with no declared source at all, are both excluded: the row
            // stays quarantined instead of being silently smoothed.
            ->whereIn('action.adjustment_source', self::AUTHORITATIVE_ADJUSTMENT_SOURCES)
            ->orderBy('action.ticker_id')
            ->get();

        $factors = [];

        foreach ($rows as $row) {
            // ex_date is authoritative; action_date is only a fallback for rows recorded
            // before the quantitative payload existed.
            $effectiveDate = $row->ex_date ?: $row->action_date;

            if ($effectiveDate === null) {
                continue;
            }

            $effectiveDate = (string) $effectiveDate;

            if ($effectiveDate <= (string) $windowStart || $effectiveDate > (string) $windowEnd) {
                continue;
            }

            $priceFactor = (float) $row->price_adjustment_factor;
            $volumeFactor = $row->volume_adjustment_factor !== null
                ? (float) $row->volume_adjustment_factor
                : 1.0;

            $factors[(int) $row->ticker_id][] = [
                'listing_id' => $row->listing_id === null ? null : (int) $row->listing_id,
                'factor_revision_ref' => 'legacy-corporate-action:'.(int) $row->corporate_action_id.':'.(string) $row->updated_at,
                'ex_date' => $effectiveDate,
                'price_factor' => $priceFactor,
                'volume_factor' => $volumeFactor > 0 ? $volumeFactor : 1.0,
            ];
        }

        foreach ($factors as $tickerId => $list) {
            usort($list, function ($a, $b) {
                return strcmp($a['ex_date'], $b['ex_date']);
            });
            $factors[$tickerId] = $list;
        }

        ksort($factors);

        return $factors;
    }

    /**
     * A factor of exactly 1, zero, or NULL adjusts nothing and must not suppress quarantine.
     *
     * Neither may a factor inferred from the price series itself. A discontinuity proves that
     * something happened; it cannot establish what, on which terms, or effective when. Treating
     * the inferred ratio as an adjustment closes the loop on the platform's own guess — the series
     * is smoothed using a number derived from the very gap being explained, and the result is
     * indistinguishable from a sourced adjustment.
     *
     * Owner: Corporate_Action_and_Adjustment_Policy.md and
     * ../registry/Price_Scale_Break_Detection_LOCKED.md — a price anomaly is candidate evidence
     * only and may never become a verified event or factor.
     */
    /**
     * Identity of the corporate event a row describes, independent of who recorded it.
     *
     * `ex_date` is what places the event on the timeline, with `action_date` as the fallback the
     * adjustment path already uses for rows recorded before the quantitative payload existed.
     */
    private function corporateActionEventKey($row): string
    {
        $effectiveDate = (string) (($row->ex_date ?? null) ?: ($row->action_date ?? ''));

        return (int) $row->ticker_id.'|'.$this->normalizeCode($row->action_type).'|'.$effectiveDate;
    }

    private function isAdjustable($row): bool
    {
        if (! property_exists($row, 'price_adjustment_factor') || $row->price_adjustment_factor === null) {
            return false;
        }

        // A positive allowlist, not merely an exclusion of the derived source. An unattributed
        // factor — adjustment_source NULL or a value nobody declared — must not adjust published
        // output either: excluding only the one known-bad value would let an unknown one through,
        // and the platform cannot say where such a factor came from.
        $source = $this->normalizeCode($row->adjustment_source ?? '');
        if (! in_array($source, self::AUTHORITATIVE_ADJUSTMENT_SOURCES, true)) {
            return false;
        }

        $factor = (float) $row->price_adjustment_factor;

        return $factor > 0 && abs($factor - 1.0) > 1e-9;
    }

    public function corporateActionTypes(): array
    {
        if ($this->corporateActionTypes !== null) {
            return $this->corporateActionTypes;
        }

        $types = self::DEFAULT_CORPORATE_ACTION_TYPES;

        try {
            $rows = DB::table($this->corporateActionTypesTable())
                ->select(['action_type_code', 'price_continuity_impact', 'volume_continuity_impact'])
                ->orderBy('action_type_code')
                ->get();

            if (count($rows) > 0) {
                $types = [];
                foreach ($rows as $row) {
                    $types[$this->normalizeCode($row->action_type_code)] = [
                        'price_continuity_impact' => $this->normalizeContinuityImpact($row->price_continuity_impact),
                        'volume_continuity_impact' => $this->normalizeContinuityImpact($row->volume_continuity_impact),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $types = self::DEFAULT_CORPORATE_ACTION_TYPES;
        }

        $this->corporateActionTypes = $types;

        return $this->corporateActionTypes;
    }

    /**
     * Trading days back from the end of the window, requested date being depth 0.
     *
     * An action dated on a non-trading day takes effect on the first trading day on or
     * after it, so the lookup resolves forward rather than requiring an exact match.
     * Returns null when the action falls after the window end, which keeps future-dated
     * actions from influencing the current row.
     */
    private function tradingDayDepth(array $tradingDates, string $actionDate): ?int
    {
        $lastIndex = count($tradingDates) - 1;

        for ($index = 0; $index <= $lastIndex; $index++) {
            if ($tradingDates[$index] >= $actionDate) {
                return $lastIndex - $index;
            }
        }

        return null;
    }

    private function normalizeContinuityImpact($value): string
    {
        $impact = $this->normalizeCode($value);

        return in_array($impact, ['NONE', 'SCALED', 'GAP_UNKNOWN_MAGNITUDE'], true)
            ? $impact
            : 'SCALED';
    }

    public function upsertCorporateAction(array $row): bool
    {
        $now = $row['updated_at'] ?? date('Y-m-d H:i:s');

        $keys = [
            'ticker_id' => (int) $row['ticker_id'],
            'action_date' => $row['action_date'],
            'action_type' => $this->normalizeCode($row['action_type']),
            'source_name' => $row['source_name'],
        ];

        return DB::table($this->corporateActionsTable())->updateOrInsert(
            $keys,
            $this->corporateActionUpsertValues($row, $now, $keys)
        );
    }

    /**
     * The values an upsert writes, with absent columns left alone.
     *
     * Writing the quantitative payload as `$row[...] ?? null` destroyed data. `updateOrInsert`
     * writes every value it is given, so re-importing an event from a CSV that omits the factor
     * columns — the three-column minimum the importer documents as valid — silently replaced a
     * stored `price_adjustment_factor` and its `adjustment_source` with NULL. Measured on the MLPT
     * row inside a rolled-back transaction: 0.04 became NULL and EXCHANGE_ANNOUNCEMENT became NULL,
     * with no error, and the next recompute would have restored the fabricated -95.7% roc20.
     *
     * So absence and emptiness are separated. A key that is not present leaves the stored value
     * untouched; a key present with an explicit null clears it. Erasing an attributed factor stays
     * possible, but only as something the caller asked for rather than something it forgot to say.
     */
    private function corporateActionUpsertValues(array $row, $now, array $keys): array
    {
        $values = $this->withDurableCreationTimestamps(
            [
                'ticker_code' => $this->normalizeCode($row['ticker_code']),
                'updated_at' => $now,
            ],
            $row,
            $this->corporateActionsTable(),
            $keys,
            $now
        );

        return $this->withPreservedOptionalFields($values, $row, [
            'source_ref', 'notes', 'ex_date', 'cum_date', 'ratio_from', 'ratio_to',
            'price_adjustment_factor', 'volume_adjustment_factor', 'dividend_per_share',
            'adjustment_source', 'adjustment_note',
        ]);
    }

    /**
     * The absent-preserves rule, stated once for every upsert in this repository.
     *
     * `F-040` fixed the corporate-action upsert and left its sibling carrying the same defect, so
     * `F-041` had to repeat the work a day later. The rule now lives in one place: a third upsert
     * cannot diverge from it by being written independently.
     *
     * A key that is not present leaves the stored value untouched. A key present with an explicit
     * null clears it. `updateOrInsert` writes every value it is handed, so passing `?? null` for an
     * optional field silently destroys stored data whenever the caller simply had nothing to say
     * about that column.
     */
    /**
     * Timestamps that describe when a row came into being, kept durable across a re-import.
     *
     * `created_at` and `recorded_at` were written unconditionally, so re-importing an event moved
     * both to now. For `recorded_at` that inverts the protection `F-028` was built for: an event
     * genuinely known in June, re-imported in August, becomes invisible to every cutoff before
     * August — the platform would understate what it knew, which is as wrong for replay as the
     * original leak that overstated it.
     *
     * `updated_at` is deliberately not here. It means "when this row was last written", so writing
     * it on every upsert is correct.
     *
     * Row existence has to be read first because `updateOrInsert` does not tell its caller which
     * branch it took. The window between the check and the write is the same one `updateOrInsert`
     * already carries internally; a loser in that race sets a creation timestamp a moment late,
     * which is bounded and visible, unlike silently rewriting one that was already correct.
     */
    private function withDurableCreationTimestamps(array $values, array $row, string $table, array $keys, $now): array
    {
        $isNewRow = ! DB::table($table)->where($keys)->exists();

        if (array_key_exists('created_at', $row)) {
            $values['created_at'] = $row['created_at'];
        } elseif ($isNewRow) {
            $values['created_at'] = $now;
        }

        if (array_key_exists('recorded_at', $row)) {
            $values['recorded_at'] = $row['recorded_at'];
        } elseif ($isNewRow) {
            $values['recorded_at'] = $row['created_at'] ?? $now;
        }

        return $values;
    }

    private function withPreservedOptionalFields(array $values, array $row, array $optionalFields): array
    {
        foreach ($optionalFields as $field) {
            if (array_key_exists($field, $row)) {
                $values[$field] = $row[$field];
            }
        }

        return $values;
    }

    public function upsertTradingStatusEvent(array $row): bool
    {
        $now = $row['updated_at'] ?? date('Y-m-d H:i:s');
        $eventTypeCode = $this->normalizeCode($row['event_type_code'] ?? '');

        if ($this->tradingStatusEventType($eventTypeCode) === null) {
            throw new \InvalidArgumentException('Unknown trading status event_type_code: '.$eventTypeCode);
        }

        $keys = [
            'ticker_id' => (int) $row['ticker_id'],
            'trade_date' => $row['trade_date'],
            'event_type_code' => $eventTypeCode,
            'source_name' => $row['source_name'],
        ];

        return DB::table($this->tradingStatusesTable())->updateOrInsert(
            $keys,
            $this->withPreservedOptionalFields(
                $this->withDurableCreationTimestamps(
                    [
                        'ticker_code' => $this->normalizeCode($row['ticker_code']),
                        'updated_at' => $now,
                    ],
                    $row,
                    $this->tradingStatusesTable(),
                    $keys,
                    $now
                ),
                $row,
                ['source_ref', 'notes']
            )
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
            // Coverage_Universe_Definition_LOCKED.md:19 — expectation evidence that is incomplete
            // or conflicting resolves UNKNOWN, which stays in the denominator and stays visible.
            'expectation_unknown' => null,
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

    private function corporateActionTypesTable(): string
    {
        return config('market_data.event_risk.corporate_action_types_table', 'market_data_corporate_action_types');
    }
}
