<?php

namespace Tests\Unit\Watchlist;

use TestCase;

class WeeklySwingParamsetRuntimeStaticGuardTest extends TestCase
{
    public function testRuntimeSchemaMatchesCanonicalParamsetAndPlanTableNames(): void
    {
        $migration = $this->read('database/migrations/2026_07_24_000001_create_watchlist_runtime_paramset_and_plan_schema.php');
        foreach ([
            'watchlist_fail_codes',
            'watchlist_reason_codes',
            'watchlist_param_sets',
            'watchlist_plan_runs',
            'watchlist_plan_items',
            'trg_wpr_guard_update',
            'trg_wpi_no_update',
        ] as $required) {
            $this->assertStringContainsString($required, $migration);
        }
    }

    public function testDraftImportAndPromotionCommandsAreRegisteredButNotScheduled(): void
    {
        $kernel = $this->read('app/Console/Kernel.php');
        $this->assertStringContainsString('ImportWeeklySwingParamsetDraftCommand::class', $kernel);
        $this->assertStringContainsString('PromoteWeeklySwingParamsetCommand::class', $kernel);

        $schedule = substr($kernel, (int) strpos($kernel, 'protected function schedule'));
        $this->assertStringNotContainsString('weekly-swing-paramset-import-draft', $schedule);
        $this->assertStringNotContainsString('weekly-swing-paramset-promote', $schedule);
    }

    public function testPromotionReadsOfficialIsAndOosTablesAndCannotPromoteWithoutProof(): void
    {
        $service = $this->read('app/Application/Watchlist/Services/WeeklySwingParamsetPromotionService.php');
        $this->assertStringContainsString("'watchlist_bt_eval'", $service);
        $this->assertStringContainsString("'watchlist_bt_picks_ws'", $service);
        $this->assertStringContainsString("'watchlist_bt_universe_ws'", $service);
        $this->assertStringContainsString("'watchlist_bt_cutoffs_ws'", $service);
        $this->assertStringContainsString("'watchlist_bt_oos_eval_ws'", $service);
        $this->assertStringContainsString('WS_PARAMSET_PROMOTION_OOS_PROOF_MISSING', $service);
        $this->assertStringContainsString('WS_PARAMSET_PROMOTION_OFFICIAL_EVIDENCE_SCHEMA_UNVERSIONED', $service);
        $this->assertStringContainsString('WS_PARAMSET_PROMOTION_OFFICIAL_SUPPORT_EVIDENCE_MISSING', $service);
        $this->assertStringContainsString("->where('status', 'DRAFT')", $service);
        $this->assertStringContainsString("'status' => 'ACTIVE'", $service);
    }

    public function testC64SyntheticProofCannotBeUsedAsCanonicalPromotionEvidence(): void
    {
        $service = $this->read('app/Application/Watchlist/Services/WatchlistBacktestC64PreOosOrOosProofExecutionService.php');
        $promotion = $this->read('app/Application/Watchlist/Services/WeeklySwingParamsetPromotionService.php');

        $this->assertStringContainsString('$scenario = (string) ($options[\'scenario\'] ?? \'pass\')', $service);
        $this->assertStringContainsString("'oos_evaluated_picks_count' => \$sampleCollapse ? 24 : 62", $service);
        $this->assertStringNotContainsString('C64', $promotion);
        $this->assertStringContainsString("->where('oos_id', \$oosId)", $promotion);
    }

    public function testC170StatusTrackerGovernanceAndNextSessionStaySynchronized(): void
    {
        $status = $this->read('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = $this->read('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $c170 = $this->read(
            'docs/watchlist/audit/WS_C170_WEEKLY_SWING_CANONICAL_IS_STRATEGY_AND_REAL_OOS_PROOF_REMEDIATION.md'
        );

        foreach ([
            'C170_CANONICAL_IS_STRATEGY_AND_REAL_OOS_PROOF_REMEDIATION',
            'WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN',
            'C171_WEEKLY_SWING_VERSIONED_OFFICIAL_BACKTEST_EVIDENCE_AND_EXECUTABLE_IS_STRATEGY_REMEDIATION',
        ] as $required) {
            $this->assertStringContainsString($required, $status.$tracker.$governance.$c170);
        }

        $this->assertStringContainsString('PARAMSET_ACTIVE_COUNT=0', $status);
        $this->assertStringContainsString('PLAN_RUN_COUNT=0', $status);
        $this->assertStringContainsString('WL-CONTRACT-C170-007`: NOT_READY', $tracker);
    }

    private function read(string $path): string
    {
        $contents = file_get_contents(base_path($path));
        $this->assertNotFalse($contents, $path);
        return (string) $contents;
    }
}
