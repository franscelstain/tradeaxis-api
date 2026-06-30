<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC111StaticGuardTest extends TestCase
{
    public function test_c111_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewCommand;', $kernel);
    }

    public function test_c111_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewCommand.php');
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

    public function test_c111_docs_state_handoff_audit_archive_completion_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C111_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C111 validates C110 artifact hash and file SHA1.',
            'C111 validates C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal state.',
            'C111 validates C104-C110 handoff lineage is carried forward as final-closed.',
            'C111 requires --operator-approved.',
            'C111 requires non-empty --approval-reference.',
            'C111 confirms no temporary negative test artifact remains.',
            'C111 final closes weekly swing watchlist non-live rehearsal handoff audit archive only.',
            'C111 marks handoff audit archive final closed for E02 and B01 only.',
            'C111 keeps A01 comparator-only and does not promote A01.',
            'C111 creates artifact-only non-live rehearsal handoff audit archive final closure manifest.',
            'C111 does not run OOS rerank.',
            'C111 does not rebuild signal quality.',
            'C111 does not change candidate selection.',
            'C111 does not rerank candidate.',
            'C111 does not retune strategy.',
            'C111 does not change scoring logic.',
            'C111 does not change catalog selection.',
            'C111 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C111 does not deploy live production.',
            'C111 does not mutate PLAN/CONFIRM.',
            'C111 does not change PLAN/CONFIRM output.',
            'C111 does not activate controlled rollout.',
            'C111 does not activate pilot runtime.',
            'C111 does not activate shadow runtime.',
            'C111 does not activate runtime bridge.',
            'C111 does not activate weekly swing watchlist runtime.',
            'C111 does not create weekly swing live output.',
            'C111 does not generate official weekly swing recommendation.',
            'C111 does not publish weekly swing output.',
            'C111 keeps production_ready=false.',
            'C111 keeps production_catalog_runtime_wired=false.',
            'C111 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C111 keeps controlled_parallel_run_active=false.',
            'C111 keeps controlled_rollout_active=false.',
            'C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime=false.',
            'C111 keeps handoff_audit_archive_context_persisted_to_live_runtime=false.',
            'C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C111 keeps handoff_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime=false.',
            'C111 keeps handoff_audit_archive_completion_seal_context_persisted_to_live_runtime=false.',
            'C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_persisted_to_live_runtime=false.',
            'C111 keeps handoff_audit_archive_final_closure_context_persisted_to_live_runtime=false.',
            'C111 keeps production_deployment_allowed=false.',
            'C111 keeps production_deployment_executed=false.',
            'C111 keeps plan_confirm_mutation_allowed=false.',
            'C111 keeps plan_confirm_mutated=false.',
            'C111 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C111 keeps live_plan_confirm_rollout_allowed=false.',
            'C111 keeps live_plan_confirm_rollout_executed=false.',
            'C111 keeps pilot_runtime_active=false.',
            'C111 keeps shadow_runtime_active=false.',
            'C111 keeps runtime_bridge_active=false.',
            'C111 keeps weekly_swing_watchlist_runtime_active=false.',
            'C111 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C111 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C111 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C111 keeps weekly_swing_watchlist_official_output_published=false.',
            'C111 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review means the non-live audit archive package is closed; it is not a production deployment or live rollout.',
            'C111 handoff audit archive final closure record is not production deployment.',
            'C111 handoff audit archive final closure record is not PLAN/CONFIRM live rollout.',
            'C111 handoff audit archive final closure record is not runtime bridge activation.',
            'C111 handoff audit archive final closure record is not weekly swing live output.',
            'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c111_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_audit_archive_completion_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_audit_archive_final_closure_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }

    public function test_c111_documentation_hygiene_guard_preserves_scoped_c110_c109_sha1_keys(): void
    {
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewService.php');
        $combined = $status."\n".$service;

        $this->assertStringContainsString('C110_EXPECTED_C109_FILE_SHA1=FC3A0F67BFEBC28131F0D3403C62AC68BEB945CB', $combined);
        $this->assertStringContainsString('EXPECTED_C109_FILE_SHA1=FC3A0F67BFEBC28131F0D3403C62AC68BEB945CB', $combined);
        $this->assertStringContainsString('scoped_c110_expected_c109_file_sha1_key_preserved', $service);
        $this->assertStringContainsString('scoped_expected_c109_file_sha1_key_preserved', $service);
    }
}
