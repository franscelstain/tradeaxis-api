<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC95StaticGuardTest extends TestCase
{
    public function test_c95_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand;', $kernel);
    }

    public function test_c95_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand.php');
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

    public function test_c95_docs_state_audit_archive_completion_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C95_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C95 validates C94 artifact hash and file SHA1.',
            'C95 validates C94 audit archive state.',
            'C95 requires --operator-approved.',
            'C95 requires non-empty --approval-reference.',
            'C95 confirms no temporary negative test artifact remains.',
            'C95 records post-activation audit archive completion only.',
            'C95 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C95 does not deploy live production.',
            'C95 does not mutate PLAN/CONFIRM.',
            'C95 does not change PLAN/CONFIRM output.',
            'C95 keeps production_ready=false.',
            'C95 keeps production_catalog_runtime_wired=false.',
            'C95 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C95 keeps controlled_parallel_run_active=false.',
            'C95 keeps controlled_rollout_active=false.',
            'C95 keeps post_activation_audit_archive_context_persisted_to_live_runtime=false.',
            'C95 keeps post_activation_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C95 keeps production_deployment_allowed=false.',
            'C95 keeps production_deployment_executed=false.',
            'C95 keeps plan_confirm_mutation_allowed=false.',
            'C95 keeps plan_confirm_mutated=false.',
            'C95 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C95 keeps live_plan_confirm_rollout_allowed=false.',
            'C95 keeps live_plan_confirm_rollout_executed=false.',
            'C95 keeps pilot_runtime_active=false.',
            'C95 keeps shadow_runtime_active=false.',
            'C95 keeps runtime_bridge_active=false.',
            'C95 post-activation audit archive completion means continue to C96 audit archive closure seal review only.',
            'C95 post-activation audit archive completion record is not production deployment.',
            'C95 post-activation audit archive completion record is not PLAN/CONFIRM live rollout.',
            'C95 post-activation audit archive completion record is not runtime bridge activation.',
            'storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c95_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('post_activation_audit_archive_completion_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('C95 audit archive completion is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.', $combined);
    }
}
