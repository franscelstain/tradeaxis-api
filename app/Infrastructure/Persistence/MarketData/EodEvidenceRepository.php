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


    public function exportRunSourceAttemptTelemetry($runId)
    {
        $events = DB::table('eod_run_events')
            ->select('event_id', 'event_time', 'event_type', 'event_payload_json')
            ->where('run_id', $runId)
            ->whereNotNull('event_payload_json')
            ->orderBy('event_time')
            ->orderBy('event_id')
            ->get();

        $selected = null;

        foreach ($events as $event) {
            $payload = json_decode((string) $event->event_payload_json, true);
            if (! is_array($payload)) {
                continue;
            }

            $sourceAcquisition = null;
            if (isset($payload['source_acquisition']) && is_array($payload['source_acquisition'])) {
                $sourceAcquisition = $payload['source_acquisition'];
            } elseif (isset($payload['exception_context']) && is_array($payload['exception_context'])) {
                $sourceAcquisition = $payload['exception_context'];
            }

            if (! is_array($sourceAcquisition) || empty($sourceAcquisition['attempts']) || ! is_array($sourceAcquisition['attempts'])) {
                continue;
            }

            $selected = [
                'event_id' => (int) $event->event_id,
                'event_time' => (string) $event->event_time,
                'event_type' => (string) $event->event_type,
            ] + $sourceAcquisition;
        }

        if (! is_array($selected)) {
            return [];
        }

        return [
            'event_id' => $selected['event_id'],
            'event_time' => $selected['event_time'],
            'event_type' => $selected['event_type'],
            'provider' => isset($selected['provider']) && $selected['provider'] !== '' ? (string) $selected['provider'] : null,
            'source_name' => isset($selected['source_name']) && $selected['source_name'] !== '' ? (string) $selected['source_name'] : null,
            'source_name_resolved' => isset($selected['source_name_resolved']) && $selected['source_name_resolved'] !== '' ? (string) $selected['source_name_resolved'] : null,
            'timeout_seconds' => isset($selected['timeout_seconds']) && $selected['timeout_seconds'] !== null ? (int) $selected['timeout_seconds'] : null,
            'retry_max' => isset($selected['retry_max']) && $selected['retry_max'] !== null ? (int) $selected['retry_max'] : null,
            'attempt_count' => isset($selected['attempt_count']) && $selected['attempt_count'] !== null ? (int) $selected['attempt_count'] : count($selected['attempts']),
            'success_after_retry' => ! empty($selected['success_after_retry']) ? 'yes' : null,
            'final_http_status' => isset($selected['final_http_status']) && $selected['final_http_status'] !== null ? (int) $selected['final_http_status'] : null,
            'final_reason_code' => isset($selected['final_reason_code']) && $selected['final_reason_code'] !== '' ? (string) $selected['final_reason_code'] : null,
            'captured_at' => isset($selected['captured_at']) && $selected['captured_at'] !== '' ? (string) $selected['captured_at'] : (string) $selected['event_time'],
            'attempts' => array_values(array_map(function ($attempt) {
                $attempt = is_array($attempt) ? $attempt : [];

                return [
                    'attempt_number' => isset($attempt['attempt_number']) && $attempt['attempt_number'] !== null ? (int) $attempt['attempt_number'] : null,
                    'reason_code' => isset($attempt['reason_code']) && $attempt['reason_code'] !== '' ? (string) $attempt['reason_code'] : null,
                    'http_status' => isset($attempt['http_status']) && $attempt['http_status'] !== null ? (int) $attempt['http_status'] : null,
                    'throttle_delay_ms' => isset($attempt['throttle_delay_ms']) && $attempt['throttle_delay_ms'] !== null ? (int) $attempt['throttle_delay_ms'] : null,
                    'backoff_delay_ms' => isset($attempt['backoff_delay_ms']) && $attempt['backoff_delay_ms'] !== null ? (int) $attempt['backoff_delay_ms'] : null,
                    'will_retry' => isset($attempt['will_retry']) ? (bool) $attempt['will_retry'] : null,
                ];
            }, $selected['attempts'])),
        ];
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
