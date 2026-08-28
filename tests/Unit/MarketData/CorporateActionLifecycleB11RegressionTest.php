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

    /**
     * MD-S011-R0070 and MD-S011-R0071: a continuity-check diagnostic may never justify an
     * adjustment-active factor, and `GAP_AMBIGUOUS` may never be cleared using evidence derived
     * from the price series -- including the absence of a detected break.
     *
     * The migration that adds `continuity_check_status` calls it diagnostic only. The current proof
     * of both prohibitions is stronger than an enforcement branch: no application code reads the
     * column at all, so the diagnostic cannot reach any decision. That is worth pinning, because the
     * cheapest way to violate either rule is to start consuming the column somewhere in the
     * adjustment or verification path and never notice that a diagnostic became an authority.
     *
     * If a future change needs to consume it, this test is the place that forces the prohibition to
     * be proven properly rather than quietly wired in.
     */
    public function test_the_continuity_diagnostic_never_reaches_an_authority_decision(): void
    {
        $diagnostic = ['continuity_check_status', 'observed_gap_pct', 'GAP_BEYOND_EXCHANGE_BAND', 'GAP_AMBIGUOUS'];

        $scanned = 0;
        $violations = [];
        $directory = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app')));
        foreach ($directory as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $scanned++;
            $source = (string) file_get_contents($file->getPathname());
            foreach ($diagnostic as $needle) {
                if (strpos($source, $needle) !== false) {
                    $violations[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()).' reads '.$needle;
                }
            }
        }

        // A scan that reaches nothing is indistinguishable from a clean tree.
        $this->assertGreaterThan(100, $scanned, 'the application scan did not reach the codebase');
        $this->assertSame([], $violations, 'the continuity diagnostic must stay diagnostic');
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
