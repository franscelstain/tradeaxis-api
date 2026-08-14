<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
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
            /*
             * Ready in which world. `MarketDataScope::stateFor()` has always been able to answer
             * this and nothing ever called it, so a date processed during development reported the
             * same readiness as one produced under an operational guarantee. With
             * `operational_start_date` unset — as it is for all 71,917 runs — every date is
             * DEVELOPMENT, and saying so is the difference between a freshness claim and a
             * statement about where the build happens to have reached.
             */
            'activation_state' => MarketDataScope::fromConfig()->stateFor($tradeDate),
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
            'activation_state' => MarketDataScope::fromConfig()->stateFor($tradeDate),
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

    /**
     * Why the day is not readable, in the platform's own words.
     *
     * This used to re-derive the diagnosis from the pointer row with its own chain of checks.
     * That chain had drifted: it had no equivalent for RUN_COVERAGE_TELEMETRY_INVALID, so a
     * publication whose run could not prove its coverage fell through every check and was
     * reported to consumers as NO_READABLE_PUBLICATION — "nothing was published for this date",
     * which is what a holiday looks like, rather than "a publication exists and is faulty".
     *
     * The repository owns this judgement. Asking it is the only way the answer stays true.
     */
    private function blockedReasonCode($pointerState): string
    {
        if (! $pointerState) {
            return 'NO_READABLE_PUBLICATION';
        }

        $reasons = $this->publications->determineCurrentIntegrityViolationReasons($pointerState);

        // The pointer row exists and the integrity scan is satisfied, yet the gateway still
        // refused it. Nothing more specific can be said, so the generic code is honest here.
        return $reasons === [] ? 'NO_READABLE_PUBLICATION' : $reasons[0];
    }
}
