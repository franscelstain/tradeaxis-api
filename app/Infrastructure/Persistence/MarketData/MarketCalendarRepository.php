<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Domain\MarketData\MarketDataScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-side boundary for the governed calendar revision set.
 *
 * It never manufactures completion from wall-clock time or from the compatibility calendar.
 */
class MarketCalendarRepository
{
    public function assertCompletedRegularSession($tradeDate, $knownAt = null)
    {
        $context = $this->sessionContext($tradeDate, $knownAt);

        if ($context['provenance_tier'] !== 'VERIFIED') {
            throw new \RuntimeException(
                'MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED: '.$tradeDate.' resolves from a '
                .($context['provenance_tier'] ?: 'UNKNOWN').' calendar row; bar expectation is UNKNOWN, never EXPECTED.'
            );
        }
        foreach (['source_ref', 'source_version', 'reconciled_at', 'reconciliation_source_ref'] as $field) {
            if (trim((string) ($context[$field] ?? '')) === '') {
                throw new \RuntimeException('MARKET_CALENDAR_VERIFICATION_EVIDENCE_INCOMPLETE: '.$field.' is missing for '.$tradeDate.'.');
            }
        }
        if ($context['is_trading_day'] !== true) {
            throw new \RuntimeException('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE: requested date is not an IDX Regular-Market trading day.');
        }
        if ($context['session_state'] !== 'COMPLETED') {
            throw new \RuntimeException('MARKET_SESSION_NOT_COMPLETED: requested IDX Regular-Market session is '.$context['session_state'].'.');
        }
        foreach (['session_open_at', 'session_close_at', 'completed_at'] as $field) {
            if (trim((string) ($context[$field] ?? '')) === '') {
                throw new \RuntimeException('MARKET_SESSION_COMPLETION_EVIDENCE_INCOMPLETE: '.$field.' is missing for '.$tradeDate.'.');
            }
        }
        if ((string) $context['completed_at'] < (string) $context['session_close_at']
            || (string) $context['recorded_at'] < (string) $context['completed_at']) {
            throw new \RuntimeException('MARKET_SESSION_COMPLETION_EVIDENCE_INCONSISTENT: completion chronology is invalid for '.$tradeDate.'.');
        }

        return $context;
    }

    public function sessionContext($tradeDate, $knownAt = null)
    {
        $tradeDate = MarketDataScope::fromConfig()->assertRequestedDate($tradeDate);
        $rows = $this->terminalRevisionRowsForDate($tradeDate, $knownAt);
        if ($rows->isEmpty()) {
            throw new \RuntimeException('MARKET_CALENDAR_EVIDENCE_MISSING: '.$tradeDate.'.');
        }
        if ($rows->count() !== 1) {
            throw new \RuntimeException('MARKET_CALENDAR_REVISION_CONFLICT: '.$tradeDate.' has multiple active governed revisions.');
        }

        $row = $rows->first();
        $marketCode = (string) $row->market_code;
        $marketSegment = (string) $row->market_segment;
        $timezone = (string) $row->timezone;
        if ($marketCode !== config('market_data.scope.market_code', 'IDX')
            || $marketSegment !== config('market_data.scope.market_segment', 'REGULAR')
            || $timezone !== config('market_data.platform.timezone', 'Asia/Jakarta')) {
            throw new \RuntimeException('MARKET_CALENDAR_SCOPE_MISMATCH: calendar revision is outside the locked IDX Regular-Market Asia/Jakarta scope.');
        }

        return [
            'calendar_revision_id' => (int) $row->calendar_revision_id,
            'revision_uid' => (string) $row->revision_uid,
            'trade_date' => (string) $row->cal_date,
            'market_code' => $marketCode,
            'market_segment' => $marketSegment,
            'timezone' => $timezone,
            'is_trading_day' => (bool) $row->is_trading_day,
            'is_half_day' => (bool) $row->is_half_day,
            'session_state' => (string) $row->session_state,
            'session_open_at' => $row->session_open_at,
            'session_close_at' => $row->session_close_at,
            'completed_at' => $row->completed_at,
            'recorded_at' => $row->recorded_at,
            'source_observation_id' => $row->source_observation_id !== null ? (int) $row->source_observation_id : null,
            'source_ref' => $row->source_ref,
            'source_version' => $row->source_version,
            'provenance_tier' => isset($row->provenance_tier) ? (string) $row->provenance_tier : '',
            'reconciled_at' => $row->reconciled_at ?? null,
            'reconciliation_source_ref' => $row->reconciliation_source_ref ?? null,
            'prev_trading_day' => $this->nearestTradingDay($tradeDate, true, $knownAt),
            'next_trading_day' => $this->nearestTradingDay($tradeDate, false, $knownAt),
        ];
    }

    public function tradingDatesBetween($startDate, $endDate, $knownAt = null)
    {
        MarketDataScope::fromConfig()->assertRequestedRange($startDate, $endDate);

        return $this->resolvedTradingDates($startDate, $endDate, $knownAt);
    }

    public function tradingDateWindowStart($endDate, $requiredTradingDates, $allowPartialWindow = true, $knownAt = null)
    {
        MarketDataScope::fromConfig()->assertRequestedDate($endDate);
        $requiredTradingDates = max(1, (int) $requiredTradingDates);
        $dates = $this->resolvedTradingDates(
            config('market_data.scope.dataset_start', '2023-01-02'),
            $endDate,
            $knownAt
        );

        if ($dates === [] || (string) end($dates) !== (string) $endDate) {
            throw new \RuntimeException('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE: requested date is not a verified active trading day in the governed calendar revision set.');
        }
        $window = array_slice($dates, -$requiredTradingDates);
        if (count($window) < $requiredTradingDates && ! $allowPartialWindow) {
            throw new \RuntimeException('MARKET_CALENDAR_INSUFFICIENT_TRADING_WINDOW: governed calendar does not contain enough prior verified trading dates for the requested indicator window.');
        }

        return (string) $window[0];
    }

    private function terminalRevisionRowsForDate($tradeDate, $knownAt)
    {
        if (! Schema::hasTable('md_market_calendar_revisions')) {
            throw new \RuntimeException('MARKET_CALENDAR_REVISION_FOUNDATION_MISSING.');
        }

        $query = DB::table('md_market_calendar_revisions as revision')
            ->leftJoin('md_market_calendar_revisions as newer', function ($join) use ($knownAt) {
                $join->on('newer.supersedes_revision_id', '=', 'revision.calendar_revision_id');
                if ($knownAt !== null && $knownAt !== '') {
                    $join->where('newer.recorded_at', '<=', $knownAt);
                }
            })
            ->whereNull('newer.calendar_revision_id')
            ->where('revision.market_code', config('market_data.scope.market_code', 'IDX'))
            ->where('revision.market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->where('revision.cal_date', $tradeDate);
        if ($knownAt !== null && $knownAt !== '') {
            $query->where('revision.recorded_at', '<=', $knownAt);
        }

        return $query->orderBy('revision.calendar_revision_id')->get(['revision.*']);
    }

    private function resolvedTradingDates($startDate, $endDate, $knownAt): array
    {
        if (! Schema::hasTable('md_market_calendar_revisions')) {
            throw new \RuntimeException('MARKET_CALENDAR_REVISION_FOUNDATION_MISSING.');
        }
        $query = DB::table('md_market_calendar_revisions as revision')
            ->leftJoin('md_market_calendar_revisions as newer', function ($join) use ($knownAt) {
                $join->on('newer.supersedes_revision_id', '=', 'revision.calendar_revision_id');
                if ($knownAt !== null && $knownAt !== '') {
                    $join->where('newer.recorded_at', '<=', $knownAt);
                }
            })
            ->whereNull('newer.calendar_revision_id')
            ->where('revision.market_code', config('market_data.scope.market_code', 'IDX'))
            ->where('revision.market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->whereBetween('revision.cal_date', [$startDate, $endDate]);
        if ($knownAt !== null && $knownAt !== '') {
            $query->where('revision.recorded_at', '<=', $knownAt);
        }

        $groups = $query->orderBy('revision.cal_date')->orderBy('revision.calendar_revision_id')
            ->get(['revision.*'])->groupBy('cal_date');
        $dates = [];
        foreach ($groups as $date => $rows) {
            if ($rows->count() !== 1) {
                throw new \RuntimeException('MARKET_CALENDAR_REVISION_CONFLICT: '.$date.' has multiple active governed revisions.');
            }
            $row = $rows->first();
            if ((string) $row->provenance_tier === 'VERIFIED'
                && (int) $row->is_trading_day === 1
                && trim((string) ($row->source_ref ?? '')) !== ''
                && trim((string) ($row->source_version ?? '')) !== ''
                && trim((string) ($row->reconciled_at ?? '')) !== ''
                && trim((string) ($row->reconciliation_source_ref ?? '')) !== '') {
                $dates[] = (string) $date;
            }
        }
        sort($dates, SORT_STRING);

        return $dates;
    }

    private function nearestTradingDay($tradeDate, bool $previous, $knownAt): ?string
    {
        $start = $previous
            ? config('market_data.scope.dataset_start', '2023-01-02')
            : date('Y-m-d', strtotime($tradeDate.' +1 day'));
        $end = $previous ? date('Y-m-d', strtotime($tradeDate.' -1 day')) : '2100-12-31';
        if ($start > $end) {
            return null;
        }
        $dates = $this->resolvedTradingDates($start, $end, $knownAt);

        return $dates === [] ? null : ($previous ? (string) end($dates) : (string) $dates[0]);
    }
}
