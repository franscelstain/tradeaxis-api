<?php

use App\Application\MarketData\Services\FinalizeDecisionService;
use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for the empty-dataset fail-safe.
 *
 * `FailSafeNoSilentFailureStaticGuardTest` asserted that the string "count($validRows) === 0"
 * appears in the ingest service and that a handful of reason-code strings appear in three files.
 * That is satisfied by code that computes the count and ignores it.
 *
 * The rule matters more than most for a weekly-swing platform, because the damage is not confined
 * to the day it happens on. A trading day that silently publishes zero bars sits inside every
 * 20-day window that spans it, so one silently empty day corrupts roughly a month of MA20, HH20,
 * ROC20 and ATR14 for the entire universe. The run reports success and every later indicator is
 * quietly wrong.
 *
 * The guard reads the row count from whichever of four keys the caller happened to populate.
 * Only one of them was ever tested. These tests drive all four, and pin the two decisions that
 * decide whether the guard fires at all: zero must be distinguishable from absent, and an
 * unreadable count must fail closed.
 */
class EmptyDatasetFailSafeTest extends TestCase
{
    private function coverageClaimingFullPass(): array
    {
        return [
            'coverage_gate_status' => 'PASS',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => 1.0,
            'coverage_threshold_value' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'expected_universe_count' => 100,
            'available_eod_count' => 100,
            'missing_eod_count' => 0,
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
        ];
    }

    private function finalize(array $promoteContext, $fallbackTradeDate = null): array
    {
        return (new FinalizeDecisionService())->evaluate(
            true,
            true,
            'SEALED',
            $this->coverageClaimingFullPass(),
            $fallbackTradeDate,
            $promoteContext
        );
    }

    /**
     * The count arrives under different names depending on which part of the pipeline reports it.
     * A key the guard does not read is a key through which an empty dataset reaches publication.
     *
     * @dataProvider rowCountKeys
     */
    public function test_a_zero_row_count_blocks_publication_whichever_key_reports_it(string $key): void
    {
        $decision = $this->finalize([$key => 0]);

        $this->assertTrue($decision['empty_artifact_blocked'], $key.' did not block an empty dataset');
        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('NOT_EVALUABLE', $decision['coverage_gate_status']);
    }

    public function rowCountKeys(): array
    {
        return [
            'valid_row_count' => ['valid_row_count'],
            'accepted_row_count' => ['accepted_row_count'],
            'bars_rows_written' => ['bars_rows_written'],
            'available_eod_count' => ['available_eod_count'],
        ];
    }

    /**
     * Zero and absent are different statements. Zero says the run counted and found nothing;
     * absent says the run did not report a count at all. Treating absent as zero would block
     * every caller that does not populate one of these keys, and treating zero as absent — which
     * is what an empty() check would do — would disable the guard entirely.
     */
    public function test_a_count_that_was_never_reported_does_not_trigger_the_guard(): void
    {
        $decision = $this->finalize([]);

        $this->assertArrayNotHasKey('empty_artifact_blocked', $decision);
        $this->assertTrue($decision['promotion_allowed']);
    }

    /**
     * @dataProvider unreadableCounts
     */
    public function test_a_count_that_cannot_be_read_as_a_number_fails_closed($value): void
    {
        $decision = $this->finalize(['bars_rows_written' => $value]);

        $this->assertTrue($decision['empty_artifact_blocked'], 'an unreadable count must not be treated as a positive one');
        $this->assertFalse($decision['promotion_allowed']);
    }

    public function unreadableCounts(): array
    {
        return [
            'a word' => ['unknown'],
            'an array' => [[]],
            'a boolean' => [true],
        ];
    }

    public function test_a_positive_row_count_is_not_blocked(): void
    {
        $decision = $this->finalize(['bars_rows_written' => 803]);

        $this->assertArrayNotHasKey('empty_artifact_blocked', $decision);
        $this->assertTrue($decision['promotion_allowed']);
    }

    /**
     * The coverage gate and the row count measure different things. Coverage compares tickers
     * present against tickers expected, and a universe that resolved to nothing can report a
     * clean PASS. The row count is the independent check that something was actually written.
     */
    public function test_zero_rows_blocks_even_when_the_coverage_gate_reports_a_clean_pass(): void
    {
        $decision = $this->finalize(['bars_rows_written' => 0]);

        $this->assertSame('PASS', $this->coverageClaimingFullPass()['coverage_gate_status']);
        $this->assertSame('NOT_EVALUABLE', $decision['coverage_gate_status']);
        $this->assertSame('NOT_EVALUABLE', $decision['coverage_summary']['coverage_gate_state']);
        $this->assertFalse($decision['promotion_allowed']);
    }

    /**
     * Whether a previous readable day exists changes how bad the outcome is, not whether the
     * empty day publishes. HELD leaves yesterday's publication serving; FAILED leaves the date
     * with nothing. Neither may be READABLE.
     */
    public function test_an_available_fallback_holds_the_run_rather_than_failing_it(): void
    {
        $decision = $this->finalize(['bars_rows_written' => 0], '2026-03-19');

        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('2026-03-19', $decision['trade_date_effective']);
    }

    public function test_no_fallback_fails_the_run_outright(): void
    {
        $decision = $this->finalize(['bars_rows_written' => 0]);

        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertNull($decision['trade_date_effective']);
    }

    /**
     * The blocked run must say why. A non-readable day with no reason code is indistinguishable
     * from a day that was never attempted.
     */
    public function test_the_blocked_run_carries_a_reason_code(): void
    {
        $withoutSourceReason = $this->finalize(['bars_rows_written' => 0]);
        $withSourceReason = $this->finalize([
            'bars_rows_written' => 0,
            'source_final_reason_code' => 'RUN_SOURCE_MANUAL_FILE_EMPTY',
        ]);

        $this->assertSame('RUN_SOURCE_NO_VALID_DATA', $withoutSourceReason['reason_code']);
        $this->assertSame('RUN_SOURCE_NO_VALID_DATA', $withoutSourceReason['coverage_summary']['coverage_reason_code']);

        // A more specific source reason survives rather than being flattened to the generic one.
        $this->assertSame('RUN_SOURCE_MANUAL_FILE_EMPTY', $withSourceReason['reason_code']);
    }
}
