<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC94StaticGuardTest extends TestCase
{
    public function test_c94_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand;', $kernel);
    }

    public function test_c94_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand.php');
        $combined = $service."\n".$command;

        foreach ([
            "latest('trade_date')",
            'latest("trade_date")',
            "orderByDesc('trade_date')",
            'orderByDesc("trade_date")',
            'MAX(trade_date)',
            "max('trade_date')",
            'returnFieldsUsedForSelection',
            'futurePathUsedForSelection',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $combined, $forbidden);
        }
    }

    public function test_c94_docs_state_audit_archive_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C94_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C94 validates C93 artifact hash and file SHA1.',
            'C94 validates C93 closure seal state.',
            'C94 requires --operator-approved.',
            'C94 requires non-empty --approval-reference.',
            'C94 confirms no temporary negative test artifact remains.',
            'C94 records post-activation audit archive only.',
            'C94 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C94 does not deploy live production.',
            'C94 does not mutate PLAN/CONFIRM.',
            'C94 does not change PLAN/CONFIRM output.',
            'C94 keeps production_ready=false.',
            'C94 keeps production_catalog_runtime_wired=false.',
            'C94 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C94 keeps controlled_parallel_run_active=false.',
            'C94 keeps controlled_rollout_active=false.',
            'C94 keeps post_activation_audit_archive_context_persisted_to_live_runtime=false.',
            'C94 keeps production_deployment_allowed=false.',
            'C94 keeps production_deployment_executed=false.',
            'C94 keeps plan_confirm_mutation_allowed=false.',
            'C94 keeps plan_confirm_mutated=false.',
            'C94 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C94 keeps live_plan_confirm_rollout_allowed=false.',
            'C94 keeps live_plan_confirm_rollout_executed=false.',
            'C94 keeps pilot_runtime_active=false.',
            'C94 keeps shadow_runtime_active=false.',
            'C94 keeps runtime_bridge_active=false.',
            'C94 post-activation audit archive means continue to C95 audit archive completion review only.',
            'C94 post-activation audit archive record is not production deployment.',
            'C94 post-activation audit archive record is not PLAN/CONFIRM live rollout.',
            'C94 post-activation audit archive record is not runtime bridge activation.',
            'storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c94_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('post_activation_audit_archive_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('C94 audit archive is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.', $combined);
    }
}
