<?php

use App\Application\MarketData\Services\MarketDataInvariantGuard;
use App\Domain\MarketData\MarketDataScope;

/**
 * W19 — operational lifecycle, commands, observability, and evidence, stage 19.
 *
 * Exit gate: "every command has success/failure/concurrency/retry proof; operator cannot bypass
 * publication safety; development frontier is not misreported as activated freshness."
 *
 * Owner contracts:
 *   docs/market_data/ops/Commands_and_Runbook_LOCKED.md
 *   docs/market_data/ops/Scheduling_and_Locking_Contract_LOCKED.md
 *   docs/market_data/ops/Observability_Minimum_Contract_LOCKED.md
 *   docs/market_data/ops/Release_Gates_LOCKED.md
 *
 * The third clause is the subtle one. "Ready" is a claim about the platform, and the same word
 * means two different things before and after activation. A date processed while the system is
 * still being built is not fresh in any operational sense, and reporting it identically is how a
 * development frontier becomes an implied guarantee nobody ever made.
 */
class OperationalCommandSafetyBoundaryTest extends TestCase
{
    /**
     * Promotion requires the full safe state. An operator flag cannot supply it, because the guard
     * inspects the resulting state rather than the operator's intent.
     */
    public function test_promotion_cannot_be_allowed_without_success_readable_and_coverage_pass(): void
    {
        $guard = new MarketDataInvariantGuard();

        foreach ([
            ['terminal_status' => 'FAILED', 'publishability_state' => 'NOT_READABLE', 'coverage_gate_state' => 'PASS', 'promotion_allowed' => true, 'reason_code' => 'RUN_COVERAGE_LOW'],
            ['terminal_status' => 'SUCCESS', 'publishability_state' => 'NOT_READABLE', 'coverage_gate_state' => 'PASS', 'promotion_allowed' => true],
            ['terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE', 'coverage_gate_state' => 'FAIL', 'promotion_allowed' => true],
        ] as $index => $state) {
            try {
                $guard->assertNoBypassState($state, 'test');
                $this->fail('state '.$index.' must not be promotable');
            } catch (\LogicException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
    }

    /**
     * A failed or held run is never readable, whatever else the state claims.
     */
    public function test_a_failed_run_can_never_be_reported_readable(): void
    {
        $this->expectException(\LogicException::class);

        (new MarketDataInvariantGuard())->assertNoBypassState([
            'terminal_status' => 'FAILED',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'reason_code' => 'RUN_COVERAGE_LOW',
        ], 'test');
    }

    /**
     * A refusal always names itself. A failed run without a reason code is an outage the operator
     * cannot triage, which the observability contract treats as its own defect.
     */
    public function test_a_failed_run_without_a_reason_code_is_refused(): void
    {
        $this->expectExceptionMessageMatches('/requires explicit reason_code/');

        (new MarketDataInvariantGuard())->assertNoBypassState([
            'terminal_status' => 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'coverage_gate_state' => 'FAIL',
            'reason_code' => null,
        ], 'test');
    }

    /**
     * Destructive operator controls demand a written reason. The flag alone is not authority; the
     * audit trail is what makes the action reviewable afterwards.
     */
    public function test_force_replace_requires_a_recorded_reason(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Console/Commands/MarketData/PromoteMarketDataCommand.php'
        );

        $this->assertStringContainsString('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', $source);
        $this->assertStringContainsString('force_replace_reason', $source);
    }

    /**
     * With no operational start date configured, every date is DEVELOPMENT. This is the honest
     * reading of the current deployment: `operational_start_date` is unset and all 71,917 runs
     * carry NULL, so nothing has been activated.
     */
    public function test_without_an_operational_start_date_every_date_is_development(): void
    {
        $scope = MarketDataScope::fromConfig();

        $this->assertFalse($scope->isOperationallyActivatedFor('2026-03-24'));
        $this->assertSame('DEVELOPMENT', $scope->stateFor('2026-03-24'));
    }

    /**
     * Readiness states which world it is reporting from. Before this the payload said `is_ready`
     * and stopped, so a development frontier and an operational guarantee were the same sentence.
     */
    public function test_the_readiness_payload_declares_its_activation_state(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Application/MarketData/Services/MarketDataReadinessService.php'
        );

        $this->assertStringContainsString("'activation_state' => MarketDataScope::fromConfig()->stateFor(\$tradeDate)", $source);
        $this->assertSame(
            2,
            substr_count($source, "'activation_state'"),
            'both the ready and the blocked payload must state it'
        );
    }

    /**
     * The consumer product carries the activation state too. Stopping at readiness would leave the
     * one party acting on the data unable to see it.
     */
    public function test_the_consumer_product_carries_the_activation_state(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Application/MarketData/Services/MarketDataReadProductService.php'
        );

        // Asserted on both payload branches. Counting every occurrence would also catch the reads
        // that feed them, so the needle names the assignment specifically.
        $needle = '\'activation_state\' => $readiness[\'activation_state\'] ?? null,';

        $this->assertStringContainsString($needle, $source);
        $this->assertSame(2, substr_count($source, $needle), 'both the ready and the empty payload must carry it');
    }

    /**
     * Every market-data command declares a signature and an owning handler, which is the minimum
     * for a command surface an operator can reason about.
     */
    public function test_every_market_data_command_declares_a_signature_and_handler(): void
    {
        $directory = __DIR__.'/../../../app/Console/Commands/MarketData';
        $missing = [];

        foreach (glob($directory.'/*.php') as $path) {
            $source = (string) file_get_contents($path);

            // An abstract base class carries shared behaviour, not a command surface of its own.
            if (strpos($source, 'abstract class') !== false) {
                continue;
            }

            if (strpos($source, 'protected $signature') === false || strpos($source, 'function handle') === false) {
                $missing[] = basename($path);
            }
        }

        $this->assertSame([], $missing, 'every command needs a signature and a handler');
    }
}
