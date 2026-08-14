<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Domain\MarketData\MarketDataScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketCalendarRepository
{
    public function assertCompletedRegularSession($tradeDate, $knownAt = null)
    {
        $context = $this->sessionContext($tradeDate, $knownAt);

        // A projected row is an assumption about a date, not a governed session. Treating it as
        // expected produces a missing-bar finding on every public holiday beyond the exchange's
        // published horizon, and those failures read as provider faults rather than calendar
        // assumptions. Owner: Market_Calendar_Requirements_Contract.md, provenance tiers.
        if ($context['provenance_tier'] !== 'VERIFIED') {
            throw new \RuntimeException(
                'MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED: '.$tradeDate.' resolves from a '
                .($context['provenance_tier'] ?: 'UNKNOWN').' calendar row; bar expectation is UNKNOWN, never EXPECTED.'
            );
        }

        if ($context['is_trading_day'] !== true) {
            throw new \RuntimeException('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE: requested date is not an IDX Regular-Market trading day.');
        }

        if ($context['session_state'] !== 'COMPLETED') {
            throw new \RuntimeException('MARKET_SESSION_NOT_COMPLETED: requested IDX Regular-Market session is '.$context['session_state'].'.');
        }

        return $context;
    }

    public function sessionContext($tradeDate, $knownAt = null)
    {
        $tradeDate = MarketDataScope::fromConfig()->assertRequestedDate($tradeDate);
        $this->ensureRevision($tradeDate);
        $query = DB::table('md_market_calendar_revisions')
            ->where('market_code', config('market_data.scope.market_code', 'IDX'))
            ->where('market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->where('cal_date', $tradeDate);

        if ($knownAt !== null) {
            $query->where('recorded_at', '<=', $knownAt);
        }

        $row = $query->orderByDesc('recorded_at')->first();
        if (! $row) {
            throw new \RuntimeException('MARKET_CALENDAR_EVIDENCE_MISSING: '.$tradeDate.'.');
        }

        return [
            'calendar_revision_id' => (int) $row->calendar_revision_id,
            'revision_uid' => (string) $row->revision_uid,
            'trade_date' => (string) $row->cal_date,
            'market_code' => (string) $row->market_code,
            'market_segment' => (string) $row->market_segment,
            'timezone' => (string) $row->timezone,
            'is_trading_day' => (bool) $row->is_trading_day,
            'session_state' => (string) $row->session_state,
            'session_open_at' => $row->session_open_at,
            'session_close_at' => $row->session_close_at,
            'completed_at' => $row->completed_at,
            'recorded_at' => $row->recorded_at,
            'source_ref' => $row->source_ref,
            'provenance_tier' => isset($row->provenance_tier) ? (string) $row->provenance_tier : '',
        ];
    }

    public function tradingDatesBetween($startDate, $endDate)
    {
        MarketDataScope::fromConfig()->assertRequestedRange($startDate, $endDate);

        return DB::table('market_calendar')
            ->whereBetween('cal_date', [$startDate, $endDate])
            ->where('is_trading_day', 1)
            ->orderBy('cal_date')
            ->pluck('cal_date')
            ->map(function ($value) {
                return (string) $value;
            })
            ->values()
            ->all();
    }

    public function tradingDateWindowStart($endDate, $requiredTradingDates, $allowPartialWindow = true)
    {
        MarketDataScope::fromConfig()->assertRequestedDate($endDate);
        $requiredTradingDates = max(1, (int) $requiredTradingDates);

        $dates = DB::table('market_calendar')
            ->where('cal_date', '<=', $endDate)
            ->where('is_trading_day', 1)
            ->orderBy('cal_date', 'desc')
            ->limit($requiredTradingDates)
            ->pluck('cal_date')
            ->map(function ($value) {
                return (string) $value;
            })
            ->values()
            ->all();

        if (empty($dates) || (string) $dates[0] !== (string) $endDate) {
            throw new \RuntimeException('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE: requested date is not an active trading day in market_calendar.');
        }

        if (count($dates) < $requiredTradingDates && ! $allowPartialWindow) {
            throw new \RuntimeException('MARKET_CALENDAR_INSUFFICIENT_TRADING_WINDOW: market_calendar does not contain enough prior trading dates for the requested indicator window.');
        }

        return (string) $dates[count($dates) - 1];
    }

    private function ensureRevision($tradeDate)
    {
        if (! Schema::hasTable('md_market_calendar_revisions')) {
            throw new \RuntimeException('MARKET_CALENDAR_REVISION_FOUNDATION_MISSING.');
        }

        $existing = DB::table('md_market_calendar_revisions')
            ->where('market_code', config('market_data.scope.market_code', 'IDX'))
            ->where('market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->where('cal_date', $tradeDate)
            ->orderByDesc('recorded_at')
            ->first();

        if ($existing) {
            $timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
            $now = Carbon::now($timezone);
            if ((string) $existing->session_state !== 'SCHEDULED'
                || empty($existing->session_close_at)
                || $now->lessThan(Carbon::parse($existing->session_close_at, $timezone))) {
                return;
            }

            DB::table('md_market_calendar_revisions')->insert([
                'market_code' => (string) $existing->market_code,
                'market_segment' => (string) $existing->market_segment,
                'cal_date' => (string) $existing->cal_date,
                'revision_uid' => hash('sha256', (string) $existing->revision_uid.'|COMPLETED|'.$now->toDateTimeString()),
                'timezone' => (string) $existing->timezone,
                'is_trading_day' => (int) $existing->is_trading_day,
                'is_half_day' => (int) $existing->is_half_day,
                'session_state' => 'COMPLETED',
                'session_open_at' => $existing->session_open_at,
                'session_close_at' => $existing->session_close_at,
                'completed_at' => $existing->session_close_at,
                'recorded_at' => $now->toDateTimeString(),
                'source_observation_id' => $existing->source_observation_id,
                'supersedes_revision_id' => $existing->calendar_revision_id,
                'source_ref' => $existing->source_ref,
                'source_version' => $existing->source_version,
                'provenance_tier' => $existing->provenance_tier ?? null,
                'reconciled_at' => $existing->reconciled_at ?? null,
                'reconciliation_source_ref' => $existing->reconciliation_source_ref ?? null,
            ]);

            return;
        }

        $legacy = DB::table('market_calendar')->where('cal_date', $tradeDate)->first();
        if (! $legacy) {
            throw new \RuntimeException('MARKET_CALENDAR_EVIDENCE_MISSING: '.$tradeDate.'.');
        }

        $timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
        $isTradingDay = (int) $legacy->is_trading_day === 1;
        $sessionOpen = $legacy->session_open_time ? $tradeDate.' '.$legacy->session_open_time.':00' : null;
        $sessionClose = $legacy->session_close_time ? $tradeDate.' '.$legacy->session_close_time.':00' : $tradeDate.' '.config('market_data.platform.cutoff_time', '17:15:00');
        $now = Carbon::now($timezone);
        $sessionState = ! $isTradingDay
            ? 'CLOSED'
            : ($now->greaterThanOrEqualTo(Carbon::parse($sessionClose, $timezone)) ? 'COMPLETED' : 'SCHEDULED');
        $recordedAt = isset($legacy->updated_at) && $legacy->updated_at ? (string) $legacy->updated_at : $now->toDateTimeString();
        $sourceRef = 'legacy_market_calendar:'.(string) ($legacy->source ?? 'unknown').':'.$tradeDate;

        DB::table('md_market_calendar_revisions')->insert([
            'market_code' => config('market_data.scope.market_code', 'IDX'),
            'market_segment' => config('market_data.scope.market_segment', 'REGULAR'),
            'cal_date' => $tradeDate,
            'revision_uid' => hash('sha256', $sourceRef.'|'.$isTradingDay.'|'.$sessionState.'|'.$sessionOpen.'|'.$sessionClose),
            'timezone' => $timezone,
            'is_trading_day' => $isTradingDay ? 1 : 0,
            'is_half_day' => 0,
            'session_state' => $sessionState,
            'session_open_at' => $sessionOpen,
            'session_close_at' => $sessionClose,
            'completed_at' => $sessionState === 'COMPLETED' ? $sessionClose : null,
            'recorded_at' => $recordedAt,
            'source_observation_id' => null,
            'supersedes_revision_id' => null,
            'provenance_tier' => isset($legacy->provenance_tier) && $legacy->provenance_tier !== null
                ? (string) $legacy->provenance_tier
                : 'PROJECTED',
            'reconciled_at' => $legacy->reconciled_at ?? null,
            'reconciliation_source_ref' => $legacy->reconciliation_source_ref ?? null,
            'source_ref' => $sourceRef,
            'source_version' => 'legacy_calendar_projection_v1',
        ]);
    }
}
