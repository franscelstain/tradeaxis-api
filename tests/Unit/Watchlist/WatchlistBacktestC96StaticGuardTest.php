<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC96StaticGuardTest extends TestCase
{
    public function test_c96_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewCommand;', $kernel);
    }

    public function test_c96_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewCommand.php');
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

    public function test_c96_docs_state_audit_archive_closure_seal_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C96_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C96 validates C95 artifact hash and file SHA1.',
            'C96 validates C95 audit archive completion state.',
            'C96 requires --operator-approved.',
            'C96 requires non-empty --approval-reference.',
            'C96 confirms no temporary negative test artifact remains.',
            'C96 records post-activation audit archive closure seal only.',
            'C96 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C96 does not deploy live production.',
            'C96 does not mutate PLAN/CONFIRM.',
            'C96 does not change PLAN/CONFIRM output.',
            'C96 keeps production_ready=false.',
            'C96 keeps production_catalog_runtime_wired=false.',
            'C96 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C96 keeps controlled_parallel_run_active=false.',
            'C96 keeps controlled_rollout_active=false.',
            'C96 keeps post_activation_audit_archive_context_persisted_to_live_runtime=false.',
            'C96 keeps post_activation_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C96 keeps post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime=false.',
            'C96 keeps production_deployment_allowed=false.',
            'C96 keeps production_deployment_executed=false.',
            'C96 keeps plan_confirm_mutation_allowed=false.',
            'C96 keeps plan_confirm_mutated=false.',
            'C96 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C96 keeps live_plan_confirm_rollout_allowed=false.',
            'C96 keeps live_plan_confirm_rollout_executed=false.',
            'C96 keeps pilot_runtime_active=false.',
            'C96 keeps shadow_runtime_active=false.',
            'C96 keeps runtime_bridge_active=false.',
            'C96 post-activation audit archive closure seal means continue to C97 audit archive finalization review only.',
            'C96 post-activation audit archive closure seal record is not production deployment.',
            'C96 post-activation audit archive closure seal record is not PLAN/CONFIRM live rollout.',
            'C96 post-activation audit archive closure seal record is not runtime bridge activation.',
            'storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c96_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('C96 audit archive closure seal is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.', $combined);
    }
}
