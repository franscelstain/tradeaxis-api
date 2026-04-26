<?php

class ReadSideAntiBypassStaticContractTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function test_official_publication_gateway_is_pointer_only_and_readable_current(): void
    {
        $source = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');

        $this->assertStringContainsString('function resolveCurrentReadablePublicationForTradeDate', $source);
        $this->assertStringContainsString("eod_current_publication_pointer as ptr", $source);
        $this->assertStringContainsString("run.terminal_status', 'SUCCESS", $source);
        $this->assertStringContainsString("run.publishability_state', 'READABLE", $source);
        $this->assertStringContainsString("run.is_current_publication', 1", $source);
        $this->assertStringContainsString('whereColumn(\'ptr.run_id\', \'pub.run_id\')', $source);
        $this->assertStringContainsString('whereColumn(\'ptr.publication_version\', \'pub.publication_version\')', $source);

        $this->assertMatchesRegularExpression(
            '/function\s+findCurrentPublicationForTradeDate\s*\([^)]*\)\s*\{[^}]*resolveCurrentReadablePublicationForTradeDate/s',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/function\s+findPointerResolvedPublicationForTradeDate\s*\([^)]*\)\s*\{[^}]*resolveCurrentReadablePublicationForTradeDate/s',
            $source
        );
    }

    public function test_consumer_read_repositories_do_not_use_latest_or_non_pointer_current_shortcuts(): void
    {
        $consumerFiles = [
            'app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php',
            'app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php',
            'app/Application/MarketData/Services/SessionSnapshotService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Application/MarketData/Services/ReplayBackfillService.php',
            'app/Application/MarketData/Services/ReplaySmokeSuiteService.php',
            'app/Console/Commands/MarketData/ExportEvidenceCommand.php',
            'app/Console/Commands/MarketData/VerifyReplayCommand.php',
            'app/Console/Commands/MarketData/ReplaySmokeSuiteCommand.php',
        ];

        foreach ($consumerFiles as $file) {
            $source = $this->read($file);

            $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*(trade_date|publication_id)\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'"](trade_date|publication_id)[\'"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/latest(Current|Publication|TradeDate)|unsafeLatest/i', $source, $file);

            if (preg_match('/eod_(bars|eligibility|indicators)\b/', $source)) {
                $this->assertStringContainsString('eod_current_publication_pointer', $source, $file.' must pointer-resolve artifact reads.');
                $this->assertStringContainsString('publishability_state', $source, $file.' must enforce readable publication state.');
                $this->assertStringContainsString('READABLE', $source, $file.' must enforce READABLE publication state.');
            }
        }
    }

    public function test_read_side_contract_document_is_locked_and_audit_governed(): void
    {
        $contract = $this->read('docs/market_data/book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md');

        $this->assertStringContainsString('Status: LOCKED', $contract);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $contract);
        $this->assertStringContainsString('Forbidden Bypass Rule', $contract);
        $this->assertStringContainsString('Fail-Safe Rule', $contract);
        $this->assertStringContainsString('AUDIT_UPDATE_GOVERNANCE.md', $contract);
    }
}
