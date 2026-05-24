<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;

class MarketDataReadinessService
{
    private EodPublicationRepository $publications;

    public function __construct(EodPublicationRepository $publications = null)
    {
        $this->publications = $publications ?: new EodPublicationRepository();
    }

    public function readinessForTradeDate(string $tradeDate): array
    {
        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);

        if ($publication) {
            return $this->readyPayload($tradeDate, $publication);
        }

        return $this->blockedPayload($tradeDate);
    }

    private function readyPayload(string $tradeDate, $publication): array
    {
        $run = DB::table('eod_runs')->where('run_id', $publication->run_id)->first();

        return [
            'trade_date' => $tradeDate,
            'trade_date_effective' => $run ? (string) $run->trade_date_effective : (string) $publication->trade_date,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
            'publication_id' => (int) $publication->publication_id,
            'publication_version' => (int) $publication->publication_version,
            'run_id' => (int) $publication->run_id,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'seal_state' => 'SEALED',
            'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'source_name' => $run ? $run->source_name : null,
            'coverage_ratio' => $publication->run_coverage_ratio !== null ? (float) $publication->run_coverage_ratio : null,
            'expected_count' => $publication->run_coverage_universe_count !== null ? (int) $publication->run_coverage_universe_count : null,
            'available_count' => $publication->run_coverage_available_count !== null ? (int) $publication->run_coverage_available_count : null,
            'missing_count' => $publication->run_coverage_missing_count !== null ? (int) $publication->run_coverage_missing_count : null,
        ];
    }

    private function blockedPayload(string $tradeDate): array
    {
        $pointerState = $this->publications->findRawCurrentPublicationStateForTradeDate($tradeDate);
        $reasonCode = $this->blockedReasonCode($pointerState);

        return [
            'trade_date' => $tradeDate,
            'trade_date_effective' => null,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'publication_id' => $pointerState && $pointerState->publication_id !== null ? (int) $pointerState->publication_id : null,
            'publication_version' => $pointerState && $pointerState->publication_version !== null ? (int) $pointerState->publication_version : null,
            'run_id' => $pointerState && $pointerState->run_id !== null ? (int) $pointerState->run_id : null,
            'terminal_status' => $pointerState ? $pointerState->terminal_status : null,
            'publishability_state' => $pointerState ? $pointerState->publishability_state : null,
            'coverage_gate_state' => $pointerState ? $pointerState->coverage_gate_state : null,
            'seal_state' => $pointerState ? $pointerState->seal_state : null,
            'pointer_resolve_status' => 'NOT_RESOLVED_READABLE_CURRENT',
            'source_name' => null,
            'coverage_ratio' => $pointerState && $pointerState->coverage_ratio !== null ? (float) $pointerState->coverage_ratio : null,
            'expected_count' => $pointerState && $pointerState->coverage_universe_count !== null ? (int) $pointerState->coverage_universe_count : null,
            'available_count' => $pointerState && $pointerState->coverage_available_count !== null ? (int) $pointerState->coverage_available_count : null,
            'missing_count' => $pointerState && $pointerState->coverage_missing_count !== null ? (int) $pointerState->coverage_missing_count : null,
        ];
    }

    private function blockedReasonCode($pointerState): string
    {
        if (! $pointerState) {
            return 'NO_READABLE_PUBLICATION';
        }

        if (empty($pointerState->publication_id)) {
            return 'PUBLICATION_ROW_MISSING';
        }

        if ((string) ($pointerState->trade_date ?? '') !== (string) ($pointerState->pointer_trade_date ?? '')) {
            return 'POINTER_PUBLICATION_TRADE_DATE_MISMATCH';
        }

        if ((int) ($pointerState->is_current ?? 0) !== 1) {
            return 'PUBLICATION_NOT_MARKED_CURRENT';
        }

        if ((string) ($pointerState->seal_state ?? '') !== 'SEALED') {
            return 'PUBLICATION_NOT_SEALED';
        }

        if (empty($pointerState->sealed_at) || empty($pointerState->pointer_sealed_at)) {
            return 'PUBLICATION_SEALED_AT_MISSING';
        }

        if (empty($pointerState->run_id)) {
            return 'RUN_ROW_MISSING';
        }

        if ((string) ($pointerState->run_id ?? '') !== (string) ($pointerState->pointer_run_id ?? '')) {
            return 'POINTER_RUN_ID_MISMATCH';
        }

        if ((string) ($pointerState->publication_version ?? '') !== (string) ($pointerState->pointer_publication_version ?? '')) {
            return 'POINTER_PUBLICATION_VERSION_MISMATCH';
        }

        if ((string) ($pointerState->terminal_status ?? '') !== 'SUCCESS') {
            return 'RUN_TERMINAL_STATUS_NOT_SUCCESS';
        }

        if ((string) ($pointerState->publishability_state ?? '') !== 'READABLE') {
            return 'RUN_PUBLISHABILITY_NOT_READABLE';
        }

        if ((string) ($pointerState->coverage_gate_state ?? '') !== 'PASS') {
            return 'RUN_COVERAGE_GATE_NOT_PASS';
        }

        if ((int) ($pointerState->is_current_publication ?? 0) !== 1) {
            return 'RUN_CURRENT_MIRROR_NOT_SET';
        }

        if ((string) ($pointerState->run_publication_id ?? '') !== (string) ($pointerState->pointer_publication_id ?? '')) {
            return 'RUN_PUBLICATION_ID_MISMATCH';
        }

        if ((string) ($pointerState->run_publication_version ?? '') !== (string) ($pointerState->pointer_publication_version ?? '')) {
            return 'RUN_PUBLICATION_VERSION_MISMATCH';
        }

        return 'NO_READABLE_PUBLICATION';
    }
}
