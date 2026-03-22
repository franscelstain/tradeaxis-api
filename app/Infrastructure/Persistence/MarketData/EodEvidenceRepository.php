<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class EodEvidenceRepository
{
    public function findRunById($runId)
    {
        return DB::table('eod_runs')->where('run_id', $runId)->first();
    }

    public function findPublicationForRun($runId)
    {
        return DB::table('eod_publications')
            ->where('run_id', $runId)
            ->orderByDesc('publication_id')
            ->first();
    }

    public function summarizeRunEvents($runId)
    {
        $events = DB::table('eod_run_events')
            ->where('run_id', $runId)
            ->orderBy('event_time')
            ->orderBy('event_id')
            ->get();

        $eventCount = $events->count();
        $first = $eventCount ? $events->first() : null;
        $last = $eventCount ? $events->last() : null;
        $severityRank = ['INFO' => 1, 'WARN' => 2, 'ERROR' => 3];
        $highestSeverity = 'INFO';
        $highestRank = 0;

        foreach ($events as $event) {
            $rank = isset($severityRank[$event->severity]) ? $severityRank[$event->severity] : 0;
            if ($rank > $highestRank) {
                $highestRank = $rank;
                $highestSeverity = $event->severity;
            }
        }

        $stageCounts = [];
        $reasonCounts = [];
        foreach ($events as $event) {
            $stage = $event->stage ?: 'UNKNOWN';
            $stageCounts[$stage] = isset($stageCounts[$stage]) ? $stageCounts[$stage] + 1 : 1;
            if ($event->reason_code) {
                $reasonCounts[$event->reason_code] = isset($reasonCounts[$event->reason_code]) ? $reasonCounts[$event->reason_code] + 1 : 1;
            }
        }
        ksort($stageCounts);
        ksort($reasonCounts);

        return [
            'event_count' => $eventCount,
            'first_event_time' => $first ? (string) $first->event_time : null,
            'last_event_time' => $last ? (string) $last->event_time : null,
            'first_event_type' => $first ? $first->event_type : null,
            'last_event_type' => $last ? $last->event_type : null,
            'highest_severity' => $highestSeverity,
            'stage_counts' => $stageCounts,
            'reason_code_counts' => $reasonCounts,
        ];
    }

    public function dominantReasonCodes($runId, $tradeDate, $publicationId = null)
    {
        $counts = [];

        $eventReasons = DB::table('eod_run_events')
            ->select('reason_code', DB::raw('COUNT(*) as total'))
            ->where('run_id', $runId)
            ->whereNotNull('reason_code')
            ->groupBy('reason_code')
            ->get();

        foreach ($eventReasons as $row) {
            $counts[$row->reason_code] = (int) $row->total;
        }

        $eligibility = DB::table('eod_eligibility')
            ->select('reason_code', DB::raw('COUNT(*) as total'))
            ->where('trade_date', $tradeDate)
            ->whereNotNull('reason_code');
        if ($publicationId !== null) {
            $eligibility->where('publication_id', $publicationId);
        }
        foreach ($eligibility->groupBy('reason_code')->get() as $row) {
            $counts[$row->reason_code] = isset($counts[$row->reason_code]) ? $counts[$row->reason_code] + (int) $row->total : (int) $row->total;
        }

        arsort($counts);
        $result = [];
        foreach ($counts as $reasonCode => $count) {
            $result[] = ['reason_code' => $reasonCode, 'count' => $count];
        }

        return $result;
    }

    public function exportEligibilityRows($tradeDate, $publicationId = null)
    {
        $query = DB::table('eod_eligibility')
            ->select('trade_date', 'ticker_id', 'eligible', 'reason_code')
            ->where('trade_date', $tradeDate)
            ->orderBy('ticker_id');
        if ($publicationId !== null) {
            $query->where('publication_id', $publicationId);
        }
        return $query->get()->map(function ($row) {
            return (array) $row;
        })->all();
    }

    public function exportInvalidBarsRows($tradeDate)
    {
        $limit = (int) config('market_data.evidence.invalid_bars_export_sample_limit', 1000);
        return DB::table('eod_invalid_bars')
            ->select('trade_date', 'ticker_id', 'source', 'source_row_ref', 'invalid_reason_code')
            ->where('trade_date', $tradeDate)
            ->orderBy('ticker_id')
            ->orderBy('source_row_ref')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }

    public function findCorrectionById($correctionId)
    {
        return DB::table('eod_dataset_corrections')->where('correction_id', $correctionId)->first();
    }

    public function findPublicationById($publicationId)
    {
        return DB::table('eod_publications')->where('publication_id', $publicationId)->first();
    }

    public function findReplayMetric($replayId, $tradeDate = null)
    {
        $query = DB::table('md_replay_daily_metrics')->where('replay_id', $replayId);
        if ($tradeDate !== null) {
            return $query->where('trade_date', $tradeDate)->first();
        }

        return $query->orderByDesc('trade_date')->first();
    }

    public function replayReasonCodeCounts($replayId, $tradeDate)
    {
        return DB::table('md_replay_reason_code_counts')
            ->select('reason_code', 'reason_count')
            ->where('replay_id', $replayId)
            ->where('trade_date', $tradeDate)
            ->orderByDesc('reason_count')
            ->orderBy('reason_code')
            ->get()
            ->map(function ($row) {
                return [
                    'reason_code' => $row->reason_code,
                    'reason_count' => (int) $row->reason_count,
                ];
            })
            ->all();
    }
}
