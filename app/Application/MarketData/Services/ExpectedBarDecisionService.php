<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;

/** Explainable listing/date expectation from separate temporal calendar, identity, and status facts. */
class ExpectedBarDecisionService
{
    public const DECISION_VERSION = 'expected-bar-calendar-status-v1';

    private $calendar;
    private $statuses;
    private $calendarContexts = [];
    private $completedCalendarContexts = [];

    public function __construct(
        MarketCalendarRepository $calendar = null,
        TemporalTradingStatusRepository $statuses = null
    ) {
        $this->calendar = $calendar ?: new MarketCalendarRepository();
        $this->statuses = $statuses ?: new TemporalTradingStatusRepository();
    }

    public function decideForListing($listingId, $tradeDate, $knownAt = null): array
    {
        $listing = DB::table('md_listings')->where('listing_id', (int) $listingId)->first();
        if (! $listing
            || (string) $listing->exchange_code !== config('market_data.scope.market_code', 'IDX')
            || (string) $listing->market_segment !== config('market_data.scope.market_segment', 'REGULAR')
            || ($listing->listed_date !== null && (string) $listing->listed_date > (string) $tradeDate)
            || ($listing->delisted_date !== null && (string) $listing->delisted_date < (string) $tradeDate)
            || ($knownAt !== null && isset($listing->recorded_at) && (string) $listing->recorded_at > (string) $knownAt)) {
            return $this->unknown($tradeDate, (int) $listingId, null, null, 'EXPECTED_BAR_TEMPORAL_LISTING_INVALID');
        }

        try {
            $calendar = $this->calendarContext($tradeDate, $knownAt);
        } catch (\Throwable $e) {
            return $this->unknown($tradeDate, (int) $listingId, (int) $listing->instrument_id, null, $this->calendarReason($e));
        }

        if ((string) $calendar['provenance_tier'] !== 'VERIFIED') {
            return $this->unknown($tradeDate, (int) $listingId, (int) $listing->instrument_id, $calendar, 'EXPECTED_BAR_CALENDAR_PROJECTED_OR_UNCLASSIFIED');
        }
        foreach (['source_ref', 'source_version', 'reconciled_at', 'reconciliation_source_ref'] as $field) {
            if (trim((string) ($calendar[$field] ?? '')) === '') {
                return $this->unknown($tradeDate, (int) $listingId, (int) $listing->instrument_id, $calendar, 'EXPECTED_BAR_CALENDAR_EVIDENCE_INCOMPLETE');
            }
        }
        if ($calendar['is_trading_day'] !== true) {
            return $this->result('NOT_EXPECTED', 'EXPECTED_BAR_NON_TRADING_DAY', $tradeDate, $listing, $calendar, null);
        }

        try {
            $calendar = $this->completedCalendarContext($tradeDate, $knownAt);
        } catch (\Throwable $e) {
            return $this->unknown($tradeDate, (int) $listingId, (int) $listing->instrument_id, $calendar, $this->calendarReason($e));
        }

        $status = $this->statuses->resolveForListing((int) $listingId, $tradeDate, $knownAt);
        if ((string) $status['bar_expectation_state'] === 'BAR_NOT_EXPECTED') {
            return $this->result('NOT_EXPECTED', 'EXPECTED_BAR_VERIFIED_FULL_SESSION_STATUS', $tradeDate, $listing, $calendar, $status);
        }
        if ((string) $status['bar_expectation_state'] === 'BAR_EXPECTED') {
            return $this->result('EXPECTED', 'EXPECTED_BAR_VERIFIED_CALENDAR_LISTING_STATUS', $tradeDate, $listing, $calendar, $status);
        }

        return $this->unknown(
            $tradeDate,
            (int) $listingId,
            (int) $listing->instrument_id,
            $calendar,
            (string) ($status['reason_code'] ?? 'EXPECTED_BAR_STATUS_UNKNOWN'),
            $status
        );
    }

    private function result($state, $reason, $tradeDate, $listing, array $calendar, ?array $status): array
    {
        return [
            'decision_version' => self::DECISION_VERSION,
            'trade_date' => (string) $tradeDate,
            'instrument_id' => (int) $listing->instrument_id,
            'listing_id' => (int) $listing->listing_id,
            'expectation_state' => $state,
            'bar_expectation_state' => 'BAR_'.$state,
            'reason_code' => $reason,
            'calendar_revision_id' => (int) $calendar['calendar_revision_id'],
            'calendar_revision_uid' => (string) $calendar['revision_uid'],
            'calendar_source_ref' => (string) $calendar['source_ref'],
            'calendar_source_version' => (string) $calendar['source_version'],
            'session_state' => (string) $calendar['session_state'],
            'is_half_day' => (bool) $calendar['is_half_day'],
            'session_open_at' => $calendar['session_open_at'],
            'session_close_at' => $calendar['session_close_at'],
            'trading_status_code' => $status['status_code'] ?? null,
            'trading_status_revision_ids' => $status['status_revision_ids'] ?? [],
            'trading_status_source_observation_ids' => $status['source_observation_ids'] ?? [],
            'trading_status_source_refs' => $status['source_refs'] ?? [],
        ];
    }

    private function unknown($tradeDate, $listingId, $instrumentId, ?array $calendar, $reason, ?array $status = null): array
    {
        return [
            'decision_version' => self::DECISION_VERSION,
            'trade_date' => (string) $tradeDate,
            'instrument_id' => $instrumentId,
            'listing_id' => $listingId,
            'expectation_state' => 'UNKNOWN',
            'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
            'reason_code' => (string) $reason,
            'calendar_revision_id' => $calendar['calendar_revision_id'] ?? null,
            'calendar_revision_uid' => $calendar['revision_uid'] ?? null,
            'calendar_source_ref' => $calendar['source_ref'] ?? null,
            'calendar_source_version' => $calendar['source_version'] ?? null,
            'session_state' => $calendar['session_state'] ?? 'UNKNOWN',
            'is_half_day' => $calendar['is_half_day'] ?? null,
            'session_open_at' => $calendar['session_open_at'] ?? null,
            'session_close_at' => $calendar['session_close_at'] ?? null,
            'trading_status_code' => $status['status_code'] ?? 'UNKNOWN',
            'trading_status_revision_ids' => $status['status_revision_ids'] ?? [],
            'trading_status_source_observation_ids' => $status['source_observation_ids'] ?? [],
            'trading_status_source_refs' => $status['source_refs'] ?? [],
        ];
    }

    private function calendarReason(\Throwable $error): string
    {
        foreach ([
            'MARKET_CALENDAR_REVISION_CONFLICT', 'MARKET_CALENDAR_EVIDENCE_MISSING',
            'MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED', 'MARKET_CALENDAR_VERIFICATION_EVIDENCE_INCOMPLETE',
            'MARKET_SESSION_NOT_COMPLETED', 'MARKET_SESSION_COMPLETION_EVIDENCE_INCOMPLETE',
            'MARKET_SESSION_COMPLETION_EVIDENCE_INCONSISTENT', 'MARKET_CALENDAR_SCOPE_MISMATCH',
        ] as $reason) {
            if (strpos($error->getMessage(), $reason) !== false) return $reason;
        }

        return 'EXPECTED_BAR_CALENDAR_UNKNOWN';
    }

    private function calendarContext($tradeDate, $knownAt): array
    {
        $key = (string) $tradeDate.'|'.(string) $knownAt;
        if (! array_key_exists($key, $this->calendarContexts)) {
            $this->calendarContexts[$key] = $this->calendar->sessionContext($tradeDate, $knownAt);
        }

        return $this->calendarContexts[$key];
    }

    private function completedCalendarContext($tradeDate, $knownAt): array
    {
        $key = (string) $tradeDate.'|'.(string) $knownAt;
        if (! array_key_exists($key, $this->completedCalendarContexts)) {
            $this->completedCalendarContexts[$key] = $this->calendar->assertCompletedRegularSession($tradeDate, $knownAt);
        }

        return $this->completedCalendarContexts[$key];
    }
}
