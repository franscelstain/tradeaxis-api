<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC97StaticGuardTest extends TestCase
{
    public function test_c97_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewCommand;', $kernel);
    }

    public function test_c97_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewCommand.php');
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

    public function test_c97_docs_state_audit_archive_finalization_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C97_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C97 validates C96 artifact hash and file SHA1.',
            'C97 validates C96 audit archive closure seal state.',
            'C97 requires --operator-approved.',
            'C97 requires non-empty --approval-reference.',
            'C97 confirms no temporary negative test artifact remains.',
            'C97 records audit archive finalization only.',
            'C97 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C97 does not deploy live production.',
            'C97 does not mutate PLAN/CONFIRM.',
            'C97 does not change PLAN/CONFIRM output.',
            'C97 does not activate pilot runtime.',
            'C97 does not activate shadow runtime.',
            'C97 does not activate runtime bridge.',
            'C97 does not activate weekly swing watchlist runtime.',
            'C97 does not create weekly swing live output.',
            'C97 keeps production_ready=false.',
            'C97 keeps production_catalog_runtime_wired=false.',
            'C97 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C97 keeps controlled_parallel_run_active=false.',
            'C97 keeps controlled_rollout_active=false.',
            'C97 keeps audit_archive_finalization_context_persisted_to_live_runtime=false.',
            'C97 keeps production_deployment_allowed=false.',
            'C97 keeps production_deployment_executed=false.',
            'C97 keeps plan_confirm_mutation_allowed=false.',
            'C97 keeps plan_confirm_mutated=false.',
            'C97 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C97 keeps live_plan_confirm_rollout_allowed=false.',
            'C97 keeps live_plan_confirm_rollout_executed=false.',
            'C97 keeps pilot_runtime_active=false.',
            'C97 keeps shadow_runtime_active=false.',
            'C97 keeps runtime_bridge_active=false.',
            'C97 keeps weekly_swing_watchlist_runtime_active=false.',
            'C97 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C97 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C97 audit archive finalization means continue to C98 weekly swing watchlist non-live rehearsal review only.',
            'C97 audit archive finalization record is not production deployment.',
            'C97 audit archive finalization record is not PLAN/CONFIRM live rollout.',
            'C97 audit archive finalization record is not runtime bridge activation.',
            'C97 audit archive finalization record is not weekly swing live output.',
            'storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c97_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('audit_archive_finalization_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C97 audit archive finalization is not production deployment, not live rollout, not runtime bridge activation, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }
}
