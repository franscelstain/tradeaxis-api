<?php

use PHPUnit\Framework\TestCase;

class WatchlistAuditGovernanceStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function readProjectFile(string $path): string
    {
        $fullPath = $this->projectPath($path);
        $this->assertFileExists($fullPath);

        return file_get_contents($fullPath);
    }

    public function test_watchlist_audit_governance_foundation_files_exist(): void
    {
        $this->assertFileExists($this->projectPath('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md'));
        $this->assertFileExists($this->projectPath('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md'));
        $this->assertFileExists($this->projectPath('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md'));
    }

    public function test_watchlist_status_does_not_claim_whole_module_readiness(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');

        $this->assertStringContainsString('PHASE_1_READ_MODEL_DONE / NOT_PRODUCTION_READY', $status);
        $this->assertStringContainsString('Production readiness | `NOT_READY`', $status);
        $this->assertStringContainsString('Watchlist is not production-ready.', $status);
        $this->assertStringNotContainsString('FULLY'.'_PRODUCTION_READY', $status);
        $this->assertStringNotContainsString('Production readiness | `'.'READY'.'`', $status);
    }

    public function test_watchlist_contract_tracker_contains_required_baseline_contracts(): void
    {
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');

        $requiredContracts = [
            'WL-CONTRACT-001 — MARKET-DATA PUBLICATION READ CONTRACT',
            'WL-CONTRACT-002 — NO RAW MARKET-DATA BYPASS',
            'WL-CONTRACT-003 — NO MAX-DATE / LATEST SHORTCUT',
            'WL-CONTRACT-004 — INDICATOR VALIDITY CONTRACT',
            'WL-CONTRACT-005 — ELIGIBILITY CONTRACT',
            'WL-CONTRACT-006 — SCORING DETERMINISM CONTRACT',
            'WL-CONTRACT-007 — PARAMSET VERSION CONTRACT',
            'WL-CONTRACT-008 — SIGNAL EXPLAINABILITY CONTRACT',
            'WL-CONTRACT-009 — BACKTEST NO-LOOKAHEAD CONTRACT',
            'WL-CONTRACT-010 — BACKTEST REPRODUCIBILITY CONTRACT',
            'WL-CONTRACT-011 — RISK GATE CONTRACT',
            'WL-CONTRACT-012 — PORTFOLIO AWARENESS BOUNDARY',
            'WL-CONTRACT-013 — AUDIT ARTIFACT CONTRACT',
            'WL-CONTRACT-014 — DOCS SYNC CONTRACT',
            'WL-CONTRACT-015 — PRODUCTION READINESS CONTRACT',
        ];

        foreach ($requiredContracts as $contract) {
            $this->assertStringContainsString($contract, $tracker);
        }
    }

    public function test_watchlist_market_data_dependency_and_bypass_rules_are_recorded(): void
    {
        $governance = $this->readProjectFile('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $governance.$status.$tracker;

        $this->assertStringContainsString('sealed publication', $combined);
        $this->assertStringContainsString('`SUCCESS` run', $combined);
        $this->assertStringContainsString('`READABLE` publication', $combined);
        $this->assertStringContainsString('coverage `PASS`', $combined);
        $this->assertStringContainsString('valid current publication pointer', $combined);
        $this->assertStringContainsString('raw provider response', $combined);
        $this->assertStringContainsString('raw staging table', $combined);
        $this->assertStringContainsString('`MAX(trade_date)` shortcut', $combined);
        $this->assertStringContainsString('latest available row without publication pointer', $combined);
        $this->assertStringContainsString('indicator rows with required null values', $combined);
    }

    public function test_watchlist_active_session_is_aligned_between_status_and_tracker(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');

        $session = 'WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION';

        $this->assertStringContainsString($session, $status);
        $this->assertStringContainsString($session, $tracker);
        $this->assertStringContainsString('ACTIVE SESSION', $status);
        $this->assertStringContainsString('ACTIVE SESSION', $tracker);
    }
}
