<?php

use App\Application\MarketData\Services\FinalizeDecisionService;

/**
 * W08 — resilience, retry/backoff/rate limit, and failure taxonomy, stage 5.
 *
 * Exit gate: "outage/partial/empty/wrong-date/schema-change fixtures tidak menghasilkan silent
 * readable publication atau denominator shrink."
 *
 * Blueprint outcome: "provider failure tidak menghasilkan synthetic data atau silent readable
 * state."
 *
 * Owner contracts:
 *   docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md
 *   docs/market_data/book/Error_Taxonomy_and_Run_Status_Decision_Table_LOCKED.md
 *   docs/market_data/book/Failure_Playbook_LOCKED.md
 *
 * The denominator is the load-bearing quantity here. Coverage is available/expected, so a
 * provider failure that also shrank the expected count would raise the ratio precisely when
 * fewer instruments were retrieved — the gate would read healthiest at its worst moment.
 */
class SourceFailureResilienceTest extends TestCase
{
    private const UNIVERSE = 900;

    private function finalize(array $coverage, array $promote = [], $fallbackTradeDate = null): array
    {
        return (new FinalizeDecisionService())->evaluate(
            true,
            true,
            'SEALED',
            array_merge([
                'expected_universe_count' => self::UNIVERSE,
                'coverage_universe_basis' => 'ACTIVE_LISTED_UNIVERSE',
                'coverage_threshold_value' => 0.98,
                'coverage_threshold_mode' => 'RATIO',
                'coverage_contract_version' => 'coverage_v1',
            ], $coverage),
            $fallbackTradeDate,
            $promote
        );
    }

    /**
     * Each fixture is a distinct way the provider can fail. They are asserted together because
     * the gate is about the shared outcome, not about any one taxonomy branch: none of them may
     * end readable, none may go unexplained, and none may move the denominator.
     */
    public function failureFixtures(): array
    {
        return [
            // Nothing was retrieved at all. The run has no dataset to publish.
            'outage' => [
                ['coverage_gate_status' => 'NOT_EVALUABLE', 'available_eod_count' => 0, 'missing_eod_count' => self::UNIVERSE],
                ['source_final_reason_code' => 'RUN_SOURCE_TIMEOUT', 'valid_data_count' => 0],
            ],
            // Some instruments returned. Partial retrieval is the case most likely to be mistaken
            // for success, because the rows that did arrive are individually valid.
            'partial' => [
                ['coverage_gate_status' => 'FAIL', 'available_eod_count' => 611, 'missing_eod_count' => 289, 'coverage_ratio' => 0.6789],
                ['source_final_reason_code' => 'RUN_SOURCE_PARTIAL_RESPONSE'],
            ],
            // The provider answered successfully with an empty series. A 200 response is not data.
            'empty' => [
                ['coverage_gate_status' => 'NOT_EVALUABLE', 'available_eod_count' => 0, 'missing_eod_count' => self::UNIVERSE],
                ['source_final_reason_code' => 'RUN_SOURCE_NO_VALID_DATA', 'valid_data_count' => 0],
            ],
            // The payload carried a different date than the one requested. Accepting it would
            // publish one session's prices under another session's date.
            'wrong_date' => [
                ['coverage_gate_status' => 'NOT_EVALUABLE', 'available_eod_count' => 0, 'missing_eod_count' => self::UNIVERSE],
                ['source_final_reason_code' => 'RUN_SOURCE_PROVIDER_REJECTED_RANGE', 'valid_data_count' => 0],
            ],
            // The provider's response shape changed. Parsing it under the old assumption is how a
            // field silently becomes the wrong field.
            'schema_change' => [
                ['coverage_gate_status' => 'NOT_EVALUABLE', 'available_eod_count' => 0, 'missing_eod_count' => self::UNIVERSE],
                ['source_final_reason_code' => 'RUN_SOURCE_RESPONSE_CHANGED', 'valid_data_count' => 0],
            ],
        ];
    }

    /**
     * @dataProvider failureFixtures
     */
    public function test_a_provider_failure_never_produces_a_readable_publication(array $coverage, array $promote): void
    {
        $decision = $this->finalize($coverage, $promote);

        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertNotSame('SUCCESS', $decision['terminal_status']);
        $this->assertFalse($decision['promotion_allowed'], 'a failed acquisition must not be promotable');
    }

    /**
     * @dataProvider failureFixtures
     */
    public function test_a_provider_failure_is_never_silent(array $coverage, array $promote): void
    {
        $decision = $this->finalize($coverage, $promote);

        $this->assertNotEmpty($decision['reason_code'], 'every refusal names why it refused');
        $this->assertNotEmpty($decision['coverage_summary']['coverage_reason_code']);
        $this->assertNotEmpty($decision['message']);
    }

    /**
     * @dataProvider failureFixtures
     */
    public function test_a_provider_failure_never_shrinks_the_denominator(array $coverage, array $promote): void
    {
        $decision = $this->finalize($coverage, $promote);

        $this->assertSame(
            self::UNIVERSE,
            $decision['coverage_summary']['expected_universe_count'],
            'the expected universe is a property of the market, not of what the provider returned'
        );
        $this->assertSame('ACTIVE_LISTED_UNIVERSE', $decision['coverage_summary']['coverage_universe_basis']);
    }

    /**
     * A held run is still not readable. Holding to an earlier date protects the reader from a bad
     * current publication; it must not be reported as if the requested date succeeded.
     */
    public function test_holding_to_a_fallback_date_is_still_not_readable(): void
    {
        $decision = $this->finalize(
            ['coverage_gate_status' => 'FAIL', 'available_eod_count' => 611, 'missing_eod_count' => 289, 'coverage_ratio' => 0.6789],
            ['source_final_reason_code' => 'RUN_SOURCE_PARTIAL_RESPONSE'],
            '2026-07-27'
        );

        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('2026-07-27', $decision['trade_date_effective']);
        $this->assertSame(self::UNIVERSE, $decision['coverage_summary']['expected_universe_count']);
    }

    /**
     * A coverage summary claiming PASS while carrying no counts cannot be believed. Without this
     * the cheapest way to publish a broken run would be to report nothing at all.
     */
    public function test_a_pass_claim_without_counts_is_downgraded_rather_than_trusted(): void
    {
        $decision = (new FinalizeDecisionService())->evaluate(true, true, 'SEALED', [
            'coverage_gate_status' => 'PASS',
            'expected_universe_count' => null,
            'available_eod_count' => null,
        ], null, []);

        $this->assertSame('NOT_EVALUABLE', $decision['coverage_gate_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $decision['reason_code']);
    }

    /**
     * Positive control. Without it every assertion above would pass on a service that refused
     * unconditionally, which would prove nothing about failure handling.
     */
    public function test_a_healthy_run_still_reaches_a_readable_publication(): void
    {
        $decision = $this->finalize([
            'coverage_gate_status' => 'PASS',
            'available_eod_count' => self::UNIVERSE,
            'missing_eod_count' => 0,
            'coverage_ratio' => 1.0,
        ]);

        $this->assertSame('SUCCESS', $decision['terminal_status']);
        $this->assertSame('READABLE', $decision['publishability_state']);
        $this->assertTrue($decision['promotion_allowed']);
        $this->assertSame(self::UNIVERSE, $decision['coverage_summary']['expected_universe_count']);
    }
}
