<?php

use PHPUnit\Framework\TestCase;

class WeeklySwingWatchlistRuntimeStaticGuardTest extends TestCase
{
    public function test_c168_runtime_calls_each_real_watchlist_pipeline_stage(): void
    {
        $source = file_get_contents($this->projectPath('app/Application/Watchlist/Services/WeeklySwingWatchlistRuntimeService.php'));

        $this->assertStringContainsString('WatchlistMarketDataConsumerReadService', $source);
        $this->assertStringContainsString('getCandidateUniverseForTradeDate($tradeDate)', $source);
        $this->assertStringContainsString('buildCandidateUniverseFromConsumerPayload', $source);
        $this->assertStringContainsString('scoreCandidateUniverse', $source);
        $this->assertStringContainsString('groupScoredOutput', $source);
        $this->assertStringContainsString('recommendFromPlanOutput', $source);
        $this->assertStringContainsString("'ticker_code' => \$tickerCode", $source);
        $this->assertStringContainsString("'publication_id' => \$source['publication_id']", $source);
        $this->assertStringContainsString("'real_runtime_integration_executed' => false", $source);
    }

    public function test_c168_runtime_remains_non_mutating_and_does_not_claim_activation_or_rollout(): void
    {
        $source = file_get_contents($this->projectPath('app/Application/Watchlist/Services/WeeklySwingWatchlistRuntimeService.php'));

        foreach ([
            "'production_runtime_activated' => false",
            "'plan_confirm_mutation_allowed' => false",
            "'plan_confirm_mutated' => false",
            "'controlled_rollout_allowed' => false",
            "'controlled_rollout_executed' => false",
            "'official_output_published' => false",
            "'free_publication_allowed' => false",
            "'production_catalog_strategy_binding_state' => 'NOT_CLAIMED_BY_C168_RUNTIME_INTEGRATION_PROOF'",
        ] as $guard) {
            $this->assertStringContainsString($guard, $source);
        }

        $this->assertStringNotContainsString('DB::table', $source);
        $this->assertStringNotContainsString('config([', $source);
    }

    public function test_real_generation_command_is_registered_without_scheduler_activation(): void
    {
        $command = file_get_contents($this->projectPath('app/Console/Commands/Watchlist/GenerateWeeklySwingWatchlistCommand.php'));
        $kernel = file_get_contents($this->projectPath('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:weekly-swing-generate', $command);
        $this->assertStringContainsString('{--trade-date=', $command);
        $this->assertStringContainsString('GenerateWeeklySwingWatchlistCommand::class', $kernel);
        $this->assertStringNotContainsString("schedule->command('watchlist:weekly-swing-generate", $kernel);
    }

    public function test_legacy_activation_flags_remain_disabled_until_c168_output_is_validated(): void
    {
        $config = require $this->projectPath('config/watchlist.php');

        $this->assertFalse($config['production_catalog_runtime_bridge_enabled']);
        $this->assertFalse($config['production_catalog_controlled_rollout_enabled']);
        $this->assertFalse($config['production_catalog_shadow_read_enabled']);
        $this->assertFalse($config['production_catalog_dry_run_enabled']);
    }

    public function test_c168_and_c169_evidence_is_retained_while_c170_is_the_active_session(): void
    {
        $status = file_get_contents($this->projectPath('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md'));
        $tracker = file_get_contents($this->projectPath('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md'));

        $this->assertFileExists($this->projectPath(
            'docs/watchlist/audit/WS_C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION.md'
        ));
        $this->assertFileExists($this->projectPath(
            'docs/watchlist/audit/WS_C168_OPERATOR_VALIDATION_COMMANDS.md'
        ));
        $this->assertFileExists($this->projectPath(
            'docs/watchlist/audit/WS_C169_WEEKLY_SWING_CANONICAL_PARAMSET_PERSISTENCE_AND_REAL_OOS_PROMOTION_GATE_REMEDIATION.md'
        ));
        $this->assertFileExists($this->projectPath(
            'docs/watchlist/audit/WS_C169_OPERATOR_VALIDATION_COMMANDS.md'
        ));
        $this->assertStringContainsString(
            'WATCHLIST - C170 WEEKLY SWING CANONICAL IS STRATEGY AND REAL OOS PROOF REMEDIATION',
            $status
        );
        $this->assertStringContainsString(
            'WATCHLIST - C170 WEEKLY SWING CANONICAL IS STRATEGY AND REAL OOS PROOF REMEDIATION',
            $tracker
        );
        $this->assertStringContainsString('C168 remains the prior controlled runtime proof', $status);
        $this->assertStringContainsString('Prior C168 real Market Data-to-ticker proof remains valid', $tracker);
        $this->assertStringContainsString('WL-CONTRACT-C169-001', $tracker);
        $this->assertStringContainsString('C167_STATUS=INCOMPLETE', $tracker);
        $this->assertStringContainsString('PLAN_PERSISTENCE_EXECUTED=0', $status.$tracker);
        $this->assertStringContainsString('CONTROLLED_ROLLOUT_EXECUTED=0', $status.$tracker);
        $this->assertStringContainsString(
            'C170_WEEKLY_SWING_CANONICAL_IS_STRATEGY_AND_REAL_OOS_PROOF_REMEDIATION',
            $status
        );
    }

    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
