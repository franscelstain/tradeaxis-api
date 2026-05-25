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
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('run.run_id', $runId)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('ptr.run_id', 'pub.run_id')
            ->whereColumn('ptr.publication_version', 'pub.publication_version')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->select('pub.*')
            ->first();
    }


    public function resolvePublicationForEvidenceAudit(array $selector)
    {
        $selectorType = isset($selector['type']) ? (string) $selector['type'] : 'run_id';
        $runId = isset($selector['run_id']) && $selector['run_id'] !== null && $selector['run_id'] !== '' ? (int) $selector['run_id'] : null;
        $publicationId = isset($selector['publication_id']) && $selector['publication_id'] !== null && $selector['publication_id'] !== '' ? (int) $selector['publication_id'] : null;
        $tradeDate = isset($selector['trade_date']) && $selector['trade_date'] !== null && $selector['trade_date'] !== '' ? (string) $selector['trade_date'] : null;

        if ($runId === null && $publicationId === null) {
            throw new \RuntimeException('EVIDENCE_SELECTOR_MISSING: Historical publication audit resolution requires explicit run_id or publication_id.');
        }

        $query = DB::table('eod_publications as pub')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->leftJoin('eod_current_publication_pointer as ptr', function ($join) {
                $join->on('ptr.trade_date', '=', 'pub.trade_date');
            })
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.trade_date_requested as run_trade_date_requested',
                'run.trade_date_effective as run_trade_date_effective',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.coverage_universe_count as run_coverage_universe_count',
                'run.coverage_available_count as run_coverage_available_count',
                'run.coverage_missing_count as run_coverage_missing_count',
                'run.coverage_ratio as run_coverage_ratio',
                'run.coverage_min_threshold as run_coverage_min_threshold',
                'run.coverage_threshold_mode as run_coverage_threshold_mode',
                'run.coverage_universe_basis as run_coverage_universe_basis',
                'run.coverage_contract_version as run_coverage_contract_version',
                'run.sealed_at as run_sealed_at',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version',
                'run.is_current_publication as run_is_current_publication'
            );

        if ($runId !== null) {
            $query->where('run.run_id', $runId);
        }

        if ($publicationId !== null) {
            $query->where('pub.publication_id', $publicationId);
        }

        if ($tradeDate !== null) {
            $query->where('pub.trade_date', $tradeDate);
        }

        $row = $query->first();
        if (! $row) {
            throw new \RuntimeException('EVIDENCE_PUBLICATION_NOT_FOUND: No publication matched the explicit evidence selector.');
        }

        if ($runId !== null && (int) $row->run_id !== $runId) {
            throw new \RuntimeException('EVIDENCE_PUBLICATION_RUN_MISMATCH: Publication does not belong to the selected run.');
        }

        if ($publicationId !== null && (int) $row->publication_id !== $publicationId) {
            throw new \RuntimeException('EVIDENCE_PUBLICATION_SELECTOR_MISMATCH: Resolved publication does not match selected publication_id.');
        }

        if ((string) ($row->run_publication_id ?? '') !== (string) $row->publication_id
            || (string) ($row->run_publication_version ?? '') !== (string) $row->publication_version) {
            throw new \RuntimeException('EVIDENCE_RUN_PUBLICATION_MIRROR_MISMATCH: Run mirror does not match the publication being evidenced.');
        }

        if ((string) ($row->run_trade_date_requested ?? '') !== (string) $row->trade_date) {
            throw new \RuntimeException('EVIDENCE_PUBLICATION_TRADE_DATE_MISMATCH: Publication trade_date does not match run requested trade date.');
        }

        if ((string) ($row->seal_state ?? '') !== 'SEALED' || empty($row->sealed_at)) {
            throw new \RuntimeException('EVIDENCE_HISTORICAL_PUBLICATION_UNSEALED: Evidence artifact proof requires a sealed publication.');
        }

        if (empty($row->run_sealed_at)) {
            throw new \RuntimeException('EVIDENCE_RUN_SEAL_MISSING: Evidence artifact proof requires the source run to be sealed.');
        }

        if ((string) ($row->run_terminal_status ?? '') !== 'SUCCESS') {
            throw new \RuntimeException('EVIDENCE_RUN_TERMINAL_STATUS_INVALID: Historical publication evidence requires a successful source run.');
        }

        if ((string) ($row->run_publishability_state ?? '') !== 'READABLE') {
            throw new \RuntimeException('EVIDENCE_RUN_PUBLISHABILITY_INVALID: Historical publication evidence requires the source run to be readable.');
        }

        if ((string) ($row->run_coverage_gate_state ?? '') !== 'PASS') {
            throw new \RuntimeException('EVIDENCE_COVERAGE_CONTEXT_INVALID: Historical publication evidence requires PASS coverage context.');
        }

        foreach (['run_coverage_universe_count', 'run_coverage_available_count', 'run_coverage_missing_count', 'run_coverage_ratio', 'run_coverage_min_threshold', 'run_coverage_threshold_mode', 'run_coverage_universe_basis', 'run_coverage_contract_version'] as $coverageField) {
            if (! property_exists($row, $coverageField) || $row->{$coverageField} === null || $row->{$coverageField} === '') {
                throw new \RuntimeException('EVIDENCE_COVERAGE_CONTEXT_MISSING: Historical publication evidence requires complete coverage telemetry.');
            }
        }

        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $hashField) {
            if (! property_exists($row, $hashField) || $row->{$hashField} === null || $row->{$hashField} === '') {
                throw new \RuntimeException('EVIDENCE_PUBLICATION_ARTIFACT_HASH_MISSING: Historical publication evidence requires publication-scoped artifact hashes.');
            }
        }

        $isCurrentPointer = (string) ($row->pointer_publication_id ?? '') === (string) $row->publication_id
            && (string) ($row->pointer_run_id ?? '') === (string) $row->run_id
            && (string) ($row->pointer_publication_version ?? '') === (string) $row->publication_version
            && (int) ($row->is_current ?? 0) === 1
            && (int) ($row->run_is_current_publication ?? 0) === 1;

        $row->evidence_resolution_mode = $isCurrentPointer ? 'CURRENT_READABLE_PUBLICATION_AUDIT' : 'HISTORICAL_PUBLICATION_AUDIT';
        $row->evidence_publication_scope = $isCurrentPointer ? 'CURRENT_POINTER_PUBLICATION' : 'HISTORICAL_SEALED_PUBLICATION';
        $row->evidence_selector_type = $selectorType;
        $row->evidence_selector_id = $publicationId !== null ? $publicationId : $runId;
        $row->historical_publication_allowed = ! $isCurrentPointer;
        $row->current_pointer_required = $isCurrentPointer;
        $row->current_pointer_status = $isCurrentPointer ? 'RESOLVED_READABLE_CURRENT' : 'NOT_CURRENT_POINTER';
        $row->artifact_scope = 'PUBLICATION_SCOPED';
        $row->coverage_basis_publication_id = (int) $row->publication_id;
        $row->coverage_basis_run_id = (int) $row->run_id;
        $row->lineage_verification_status = 'LINEAGE_VERIFIED';
        $row->evidence_reason_code = $isCurrentPointer ? 'CURRENT_READABLE_PUBLICATION_RESOLVED' : 'HISTORICAL_SEALED_PUBLICATION_RESOLVED';

        return $row;
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
        if (! $this->readablePublicationContextExists($tradeDate, $publicationId, $runId)) {
            return [];
        }

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

        $eligibility = $this->readableEligibilityQuery($tradeDate, $publicationId)
            ->select('elig.reason_code', DB::raw('COUNT(*) as total'))
            ->whereNotNull('elig.reason_code');

        foreach ($eligibility->groupBy('elig.reason_code')->get() as $row) {
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
        return $this->readableEligibilityQuery($tradeDate, $publicationId)
            ->select('elig.trade_date', 'elig.ticker_id', 'elig.eligible', 'elig.reason_code')
            ->orderBy('elig.ticker_id')
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })->all();
    }

    private function readablePublicationContextExists($tradeDate, $publicationId = null, $runId = null)
    {
        $query = DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('ptr.run_id', 'pub.run_id')
            ->whereColumn('ptr.publication_version', 'pub.publication_version')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version');

        if ($publicationId !== null) {
            $query->where('ptr.publication_id', $publicationId);
        }

        if ($runId !== null) {
            $query->where('run.run_id', $runId);
        }

        return $query->exists();
    }

    private function readableEligibilityQuery($tradeDate, $publicationId = null)
    {
        $query = DB::table('eod_eligibility as elig')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'elig.publication_id')
            ->join('eod_current_publication_pointer as ptr', function ($join) {
                $join->on('ptr.trade_date', '=', 'elig.trade_date')
                    ->on('ptr.publication_id', '=', 'elig.publication_id')
                    ->on('ptr.run_id', '=', 'pub.run_id')
                    ->on('ptr.publication_version', '=', 'pub.publication_version');
            })
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('elig.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('pub.trade_date', 'elig.trade_date')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version');

        if ($publicationId !== null) {
            $query->where('elig.publication_id', $publicationId);
        }

        return $query;
    }


    public function dominantReasonCodesForEvidencePublication($runId, $tradeDate, $publicationId, $isCurrentPublication = false)
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

        $eligibilityQuery = $this->evidenceEligibilityQuery($tradeDate, $publicationId, $isCurrentPublication)
            ->select('elig.reason_code', DB::raw('COUNT(*) as total'))
            ->whereNotNull('elig.reason_code');

        foreach ($eligibilityQuery->groupBy('elig.reason_code')->get() as $row) {
            $counts[$row->reason_code] = isset($counts[$row->reason_code]) ? $counts[$row->reason_code] + (int) $row->total : (int) $row->total;
        }

        arsort($counts);
        $result = [];
        foreach ($counts as $reasonCode => $count) {
            $result[] = ['reason_code' => $reasonCode, 'count' => (int) $count];
        }

        return $result;
    }

    public function exportEligibilityRowsForEvidencePublication($tradeDate, $publicationId, $isCurrentPublication = false)
    {
        return $this->evidenceEligibilityQuery($tradeDate, $publicationId, $isCurrentPublication)
            ->select('elig.trade_date', 'elig.ticker_id', 'elig.eligible', 'elig.reason_code')
            ->orderBy('elig.ticker_id')
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })->all();
    }

    private function evidenceEligibilityQuery($tradeDate, $publicationId, $isCurrentPublication = false)
    {
        $historyExists = DB::table('eod_eligibility_history')
            ->where('trade_date', $tradeDate)
            ->where('publication_id', $publicationId)
            ->exists();

        $table = $historyExists || ! $isCurrentPublication ? 'eod_eligibility_history' : 'eod_eligibility';

        return DB::table($table.' as elig')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'elig.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('elig.trade_date', $tradeDate)
            ->where('elig.publication_id', $publicationId)
            ->whereColumn('pub.trade_date', 'elig.trade_date')
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.publication_id', 'pub.publication_id')
            ->whereColumn('run.publication_version', 'pub.publication_version');
    }

    public function exportInvalidBarsRows($tradeDate, $runId)
    {
        $limit = (int) config('market_data.evidence.invalid_bars_export_sample_limit', 1000);

        return DB::table('eod_invalid_bars')
            ->select('trade_date', 'ticker_id', 'source', 'source_row_ref', 'invalid_reason_code')
            ->where('trade_date', $tradeDate)
            ->where('run_id', $runId)
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
            'source_acquisition_state' => isset($selected['source_acquisition_state']) && $selected['source_acquisition_state'] !== '' ? (string) $selected['source_acquisition_state'] : null,
            'source_acquisition_mode' => isset($selected['source_acquisition_mode']) && $selected['source_acquisition_mode'] !== '' ? (string) $selected['source_acquisition_mode'] : null,
            'source_acquisition_batch_id' => isset($selected['source_acquisition_batch_id']) && $selected['source_acquisition_batch_id'] !== '' ? (string) $selected['source_acquisition_batch_id'] : null,
            'source_window_start' => isset($selected['source_window_start']) && $selected['source_window_start'] !== '' ? (string) $selected['source_window_start'] : null,
            'source_window_end' => isset($selected['source_window_end']) && $selected['source_window_end'] !== '' ? (string) $selected['source_window_end'] : null,
            'warmup_start' => isset($selected['warmup_start']) && $selected['warmup_start'] !== '' ? (string) $selected['warmup_start'] : null,
            'requested_start' => isset($selected['requested_start']) && $selected['requested_start'] !== '' ? (string) $selected['requested_start'] : null,
            'requested_end' => isset($selected['requested_end']) && $selected['requested_end'] !== '' ? (string) $selected['requested_end'] : null,
            'expected_ticker_count' => isset($selected['expected_ticker_count']) && $selected['expected_ticker_count'] !== null ? (int) $selected['expected_ticker_count'] : null,
            'success_ticker_count' => isset($selected['success_ticker_count']) && $selected['success_ticker_count'] !== null ? (int) $selected['success_ticker_count'] : null,
            'failed_ticker_count' => isset($selected['failed_ticker_count']) && $selected['failed_ticker_count'] !== null ? (int) $selected['failed_ticker_count'] : null,
            'max_failed_allowed_for_coverage' => isset($selected['max_failed_allowed_for_coverage']) && $selected['max_failed_allowed_for_coverage'] !== null ? (int) $selected['max_failed_allowed_for_coverage'] : null,
            'coverage_impossible' => isset($selected['coverage_impossible']) ? (bool) $selected['coverage_impossible'] : null,
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
        return DB::table('eod_dataset_corrections as corr')
            ->leftJoin('eod_runs as prior_run', 'prior_run.run_id', '=', 'corr.prior_run_id')
            ->leftJoin('eod_runs as new_run', 'new_run.run_id', '=', 'corr.new_run_id')
            ->leftJoin('eod_publications as prior_pub', 'prior_pub.publication_id', '=', 'prior_run.publication_id')
            ->leftJoin('eod_publications as new_pub', 'new_pub.publication_id', '=', 'new_run.publication_id')
            ->where('corr.correction_id', $correctionId)
            ->select([
                'corr.*',
                'prior_run.publication_id as prior_publication_id',
                'prior_run.publication_version as prior_publication_version',
                'prior_run.terminal_status as prior_run_terminal_status',
                'prior_run.publishability_state as prior_run_publishability_state',
                'prior_run.coverage_gate_state as prior_run_coverage_gate_state',
                'new_run.publication_id as new_publication_id',
                'new_run.publication_version as new_publication_version',
                'new_run.terminal_status as new_run_terminal_status',
                'new_run.publishability_state as new_run_publishability_state',
                'new_run.coverage_gate_state as new_run_coverage_gate_state',
                'new_run.notes as new_run_notes',
                'prior_pub.seal_state as prior_publication_seal_state',
                'prior_pub.is_current as prior_publication_is_current',
                'new_pub.seal_state as new_publication_seal_state',
                'new_pub.is_current as new_publication_is_current',
            ])
            ->first();
    }

    public function findCorrectionByRunId($runId)
    {
        return DB::table('eod_dataset_corrections as corr')
            ->leftJoin('eod_runs as prior_run', 'prior_run.run_id', '=', 'corr.prior_run_id')
            ->leftJoin('eod_runs as new_run', 'new_run.run_id', '=', 'corr.new_run_id')
            ->leftJoin('eod_publications as prior_pub', 'prior_pub.publication_id', '=', 'prior_run.publication_id')
            ->leftJoin('eod_publications as new_pub', 'new_pub.publication_id', '=', 'new_run.publication_id')
            ->where('corr.new_run_id', $runId)
            ->select([
                'corr.*',
                'prior_run.publication_id as prior_publication_id',
                'prior_run.publication_version as prior_publication_version',
                'prior_run.terminal_status as prior_run_terminal_status',
                'prior_run.publishability_state as prior_run_publishability_state',
                'prior_run.coverage_gate_state as prior_run_coverage_gate_state',
                'new_run.publication_id as new_publication_id',
                'new_run.publication_version as new_publication_version',
                'new_run.terminal_status as new_run_terminal_status',
                'new_run.publishability_state as new_run_publishability_state',
                'new_run.coverage_gate_state as new_run_coverage_gate_state',
                'new_run.notes as new_run_notes',
                'prior_pub.seal_state as prior_publication_seal_state',
                'prior_pub.is_current as prior_publication_is_current',
                'new_pub.seal_state as new_publication_seal_state',
                'new_pub.is_current as new_publication_is_current',
            ])
            ->first();
    }

    public function findPublicationById($publicationId)
    {
        return DB::table('eod_publications')->where('publication_id', $publicationId)->first();
    }

    public function findReplayMetric($replayId, $tradeDate = null)
    {
        if ($tradeDate === null || $tradeDate === '') {
            throw new \RuntimeException('Replay metric lookup requires explicit trade_date; latest-row resolution is not allowed.');
        }

        return DB::table('md_replay_daily_metrics')
            ->where('replay_id', $replayId)
            ->where('trade_date', $tradeDate)
            ->first();
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
