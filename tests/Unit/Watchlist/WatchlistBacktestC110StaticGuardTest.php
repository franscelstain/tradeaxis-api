<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC110StaticGuardTest extends TestCase
{
    public function test_c110_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewCommand;', $kernel);
    }

    public function test_c110_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewCommand.php');
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

    public function test_c110_docs_state_handoff_audit_archive_completion_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C110_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C110_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C110 validates C109 artifact hash and file SHA1.',
            'C110 validates C109 weekly swing watchlist non-live rehearsal handoff audit archive completion ready state.',
            'C110 validates C104-C109 handoff lineage is carried forward as sealed-complete.',
            'C110 requires --operator-approved.',
            'C110 requires non-empty --approval-reference.',
            'C110 confirms no temporary negative test artifact remains.',
            'C110 seals weekly swing watchlist non-live rehearsal handoff audit archive completion only.',
            'C110 marks handoff audit archive completion sealed for E02 and B01 only.',
            'C110 keeps A01 comparator-only and does not promote A01.',
            'C110 creates artifact-only non-live rehearsal handoff audit archive completion seal manifest.',
            'C110 does not run OOS rerank.',
            'C110 does not rebuild signal quality.',
            'C110 does not change candidate selection.',
            'C110 does not rerank candidate.',
            'C110 does not retune strategy.',
            'C110 does not change scoring logic.',
            'C110 does not change catalog selection.',
            'C110 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C110 does not deploy live production.',
            'C110 does not mutate PLAN/CONFIRM.',
            'C110 does not change PLAN/CONFIRM output.',
            'C110 does not activate controlled rollout.',
            'C110 does not activate pilot runtime.',
            'C110 does not activate shadow runtime.',
            'C110 does not activate runtime bridge.',
            'C110 does not activate weekly swing watchlist runtime.',
            'C110 does not create weekly swing live output.',
            'C110 does not generate official weekly swing recommendation.',
            'C110 does not publish weekly swing output.',
            'C110 keeps production_ready=false.',
            'C110 keeps production_catalog_runtime_wired=false.',
            'C110 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C110 keeps controlled_parallel_run_active=false.',
            'C110 keeps controlled_rollout_active=false.',
            'C110 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime=false.',
            'C110 keeps handoff_audit_archive_context_persisted_to_live_runtime=false.',
            'C110 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C110 keeps handoff_audit_archive_completion_context_persisted_to_live_runtime=false.',
            'C110 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime=false.',
            'C110 keeps handoff_audit_archive_completion_seal_context_persisted_to_live_runtime=false.',
            'C110 keeps production_deployment_allowed=false.',
            'C110 keeps production_deployment_executed=false.',
            'C110 keeps plan_confirm_mutation_allowed=false.',
            'C110 keeps plan_confirm_mutated=false.',
            'C110 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C110 keeps live_plan_confirm_rollout_allowed=false.',
            'C110 keeps live_plan_confirm_rollout_executed=false.',
            'C110 keeps pilot_runtime_active=false.',
            'C110 keeps shadow_runtime_active=false.',
            'C110 keeps runtime_bridge_active=false.',
            'C110 keeps weekly_swing_watchlist_runtime_active=false.',
            'C110 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C110 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C110 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C110 keeps weekly_swing_watchlist_official_output_published=false.',
            'C110 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal review means continue to C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review only.',
            'C110 handoff audit archive completion record is not production deployment.',
            'C110 handoff audit archive completion record is not PLAN/CONFIRM live rollout.',
            'C110 handoff audit archive completion record is not runtime bridge activation.',
            'C110 handoff audit archive completion record is not weekly swing live output.',
            'storage/app/watchlist/backtest/c110-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-seal-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c110_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_audit_archive_completion_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_audit_archive_completion_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }

    public function test_c110_documentation_hygiene_guard_preserves_scoped_c109_c108_sha1_keys(): void
    {
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewService.php');
        $combined = $status."\n".$service;

        $this->assertStringContainsString('C109_EXPECTED_C108_FILE_SHA1=591BF25C2A1E7678B2C9335ECBEF1938BDAF990C', $combined);
        $this->assertStringContainsString('EXPECTED_C108_FILE_SHA1=591BF25C2A1E7678B2C9335ECBEF1938BDAF990C', $combined);
        $this->assertStringContainsString('scoped_c109_expected_c108_file_sha1_key_preserved', $service);
        $this->assertStringContainsString('scoped_expected_c108_file_sha1_key_preserved', $service);
    }
}
