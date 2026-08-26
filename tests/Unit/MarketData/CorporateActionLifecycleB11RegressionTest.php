<?php

class CorporateActionLifecycleB11RegressionTest extends TestCase
{
    public function test_detector_is_candidate_only_and_uses_v2_ex_date_linkage(): void
    {
        $source=file_get_contents(base_path('app/Application/MarketData/Services/PriceScaleBreakDetectionService.php'));
        $this->assertStringContainsString("md_corporate_action_revisions",$source);
        $this->assertStringContainsString("revision.ex_date",$source);
        $this->assertStringContainsString("REVISION_LINKAGE_CANDIDATE",$source);
        $this->assertStringNotContainsString("'EXPLAINED'",$source);
        $this->assertStringNotContainsString("ca.action_date",$source);
    }

    public function test_direct_price_derived_event_and_bar_repair_commands_are_non_mutating(): void
    {
        $derive=file_get_contents(base_path('app/Console/Commands/MarketData/DeriveCorporateActionsCommand.php'));
        $repair=file_get_contents(base_path('app/Console/Commands/MarketData/RepairPriceScaleStretchesCommand.php'));
        $this->assertStringContainsString('mutation_performed=false',$derive);
        $this->assertStringContainsString('CORPORATE_ACTION_AUTHORITATIVE_EVIDENCE_REQUIRED',$derive);
        $this->assertStringContainsString('mutation_performed=false',$repair);
        $this->assertStringContainsString('IMMUTABLE_HISTORY_CORRECTION_REQUIRED',$repair);
        $this->assertStringNotContainsString('repair($this->option(\'ticker\') ?: null, $apply)',$repair);
    }

    public function test_external_reconciliation_requires_explicit_full_scope_before_action_complete_claim(): void
    {
        $source=file_get_contents(base_path('app/Application/MarketData/Services/CorporateActionExternalReconciliationService.php'));
        $this->assertStringContainsString('scope_complete',$source);
        $this->assertStringContainsString('AUTHORITY_SCOPE_INCOMPLETE',$source);
        $this->assertStringContainsString('missing_platform_count',$source);
        $this->assertStringContainsString('unexpected_platform_count',$source);
        $this->assertStringContainsString('MarketDataScope::DATASET_START',$source);
    }

    public function test_event_risk_has_no_silent_action_date_to_ex_date_promotion(): void
    {
        $source=file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php'));
        $this->assertStringContainsString('LEGACY_ACTION_DATE_RISK_ONLY',$source);
        $this->assertStringContainsString('revision.ex_date',$source);
        $this->assertStringNotContainsString('effectiveDate = (string) (($row->ex_date ?? null) ?: ($row->action_date ?? \'\'))',$source);
        $this->assertStringContainsString('Legacy rows never become adjustment authority.',$source);
    }
}
