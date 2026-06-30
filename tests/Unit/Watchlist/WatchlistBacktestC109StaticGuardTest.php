<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC109StaticGuardTest extends TestCase
{
    public function test_c109_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewCommand;', $kernel);
    }

    public function test_c109_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewCommand.php');
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

    public function test_c109_docs_state_handoff_audit_archive_completion_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C109_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C109 validates C108 artifact hash and file SHA1.',
            'C109 validates C108 weekly swing watchlist non-live rehearsal handoff audit archive state.',
            'C109 validates C104-C108 handoff lineage is carried forward as complete.',
            'C109 requires --operator-approved.',
            'C109 requires non-empty --approval-reference.',
            'C109 confirms no temporary negative test artifact remains.',
            'C109 marks weekly swing watchlist non-live rehearsal handoff audit archive completion readiness only.',
            'C109 marks handoff audit archive completion readiness for E02 and B01 only.',
            'C109 keeps A01 comparator-only and does not promote A01.',
            'C109 creates artifact-only non-live rehearsal handoff audit archive completion manifest.',
            'C109 does not run OOS rerank.',
            'C109 does not rebuild signal quality.',
            'C109 does not change candidate selection.',
            'C109 does not rerank candidate.',
            'C109 does not retune strategy.',
            'C109 does not change scoring logic.',
            'C109 does not change catalog selection.',
            'C109 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C109 does not deploy live production.',
            'C109 does not mutate PLAN/CONFIRM.',
            'C109 does not change PLAN/CONFIRM output.',
            'C109 does not activate controlled rollout.',
            'C109 does not activate pilot runtime.',
            'C109 does not activate shadow runtime.',
            'C109 does not activate runtime bridge.',
            'C109 does not activate weekly swing watchlist runtime.',
            'C109 does not create weekly swing live output.',
            'C109 does not generate official weekly swing recommendation.',
            'C109 does not publish weekly swing output.',
            'C109 keeps production_ready=false.',
            'C109 keeps production_catalog_runtime_wired=false.',
            'C109 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C109 keeps controlled_parallel_run_active=false.',
            'C109 keeps controlled_rollout_active=false.',
            'C109 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime=false.',
            'C109 keeps handoff_audit_archive_context_persisted_to_live_runtime=false.',
            'C109 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C109 keeps handoff_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C109 keeps production_deployment_allowed=false.',
            'C109 keeps production_deployment_executed=false.',
            'C109 keeps plan_confirm_mutation_allowed=false.',
            'C109 keeps plan_confirm_mutated=false.',
            'C109 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C109 keeps live_plan_confirm_rollout_allowed=false.',
            'C109 keeps live_plan_confirm_rollout_executed=false.',
            'C109 keeps pilot_runtime_active=false.',
            'C109 keeps shadow_runtime_active=false.',
            'C109 keeps runtime_bridge_active=false.',
            'C109 keeps weekly_swing_watchlist_runtime_active=false.',
            'C109 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C109 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C109 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C109 keeps weekly_swing_watchlist_official_output_published=false.',
            'C109 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review means continue to C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal review only.',
            'C109 handoff audit archive completion record is not production deployment.',
            'C109 handoff audit archive completion record is not PLAN/CONFIRM live rollout.',
            'C109 handoff audit archive completion record is not runtime bridge activation.',
            'C109 handoff audit archive completion record is not weekly swing live output.',
            'storage/app/watchlist/backtest/c109-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c109_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_audit_archive_completion_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }

    public function test_c109_documentation_hygiene_guard_preserves_scoped_c108_c107_sha1_keys(): void
    {
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService.php');
        $combined = $status."\n".$service;

        $this->assertStringContainsString('C108_EXPECTED_C107_FILE_SHA1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8', $combined);
        $this->assertStringContainsString('EXPECTED_C107_FILE_SHA1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8', $combined);
        $this->assertStringContainsString('scoped_c108_expected_c107_file_sha1_key_preserved', $service);
        $this->assertStringContainsString('scoped_expected_c107_file_sha1_key_preserved', $service);
    }
}
