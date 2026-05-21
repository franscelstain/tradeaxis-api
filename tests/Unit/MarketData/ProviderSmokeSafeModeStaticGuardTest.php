<?php

use PHPUnit\Framework\TestCase;

class ProviderSmokeSafeModeStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path, $relativePath.' must exist.');

        return file_get_contents($path);
    }

    public function test_provider_smoke_command_is_registered_with_safe_single_ticker_surface(): void
    {
        $kernel = $this->read('app/Console/Kernel.php');
        $command = $this->read('app/Console/Commands/MarketData/ProviderSmokeCommand.php');

        $this->assertStringContainsString('ProviderSmokeCommand::class', $kernel);
        $this->assertStringContainsString('market-data:provider:smoke', $command);
        $this->assertStringContainsString('{--ticker=}', $command);
        $this->assertStringContainsString('{--trade_date=}', $command);
        $this->assertStringContainsString('{--dry-run}', $command);
        $this->assertStringContainsString('{--max-tickers=1}', $command);
        $this->assertStringContainsString('{--provider=yahoo}', $command);
        $this->assertStringContainsString('{--json}', $command);
        $this->assertStringContainsString('applyProviderOption', $command);
        $this->assertStringContainsString("config(['market_data.source.api.provider' => \$provider])", $command);
        $this->assertStringContainsString('json_encode($this->jsonPayload($result), JSON_UNESCAPED_SLASHES)', $command);
        $this->assertStringContainsString('fetchOrLoadEodBars($tradeDate, \'api\', [$ticker])', $command);
    }

    public function test_provider_smoke_blocks_full_universe_and_remains_non_destructive(): void
    {
        $command = $this->read('app/Console/Commands/MarketData/ProviderSmokeCommand.php');

        foreach ([
            'PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED',
            'full_universe_fetch',
            'write_mode',
            'publication_created',
            'seal_executed',
            'finalize_executed',
            'pointer_switched',
            'readable_publication_created',
            'false',
        ] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }

        foreach ([
            'FinalizeRunCommand',
            'FinalizeDecisionService',
            'PublicationFinalizeOutcomeService',
            'SealDatasetCommand',
            'SealDatasetService',
            'updateCurrentPublicationPointer',
            'switchCurrentPublication',
            'getOrCreateCandidatePublication',
            'replaceBars',
            'completeIngest',
            'completeFinalize',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $command, $forbidden.' must not be used by provider smoke.');
        }
    }

    public function test_provider_smoke_outputs_reason_coded_pass_fail_blocked_results(): void
    {
        $command = $this->read('app/Console/Commands/MarketData/ProviderSmokeCommand.php');
        $registry = $this->read('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->read('docs/market_data/registry/Reason_Codes_Seed.sql');

        foreach ([
            'PROVIDER_SMOKE_OK',
            'PROVIDER_RATE_LIMITED',
            'PROVIDER_TIMEOUT',
            'PROVIDER_NETWORK_ERROR',
            'PROVIDER_EMPTY_OR_INVALID_RESPONSE',
            'PROVIDER_SMOKE_TICKER_REQUIRED',
            'PROVIDER_SMOKE_INVALID_TICKER',
            'provider_smoke_status',
            'reason_code',
            'BLOCKED',
            'FAILED',
            'PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }

        foreach ([
            'PROVIDER_SMOKE_OK',
            'PROVIDER_RATE_LIMITED',
            'PROVIDER_TIMEOUT',
            'PROVIDER_NETWORK_ERROR',
            'PROVIDER_EMPTY_OR_INVALID_RESPONSE',
            'PROVIDER_SMOKE_TICKER_REQUIRED',
            'PROVIDER_SMOKE_INVALID_TICKER',
            'PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED',
        ] as $reasonCode) {
            $this->assertStringContainsString('`'.$reasonCode.'`', $registry, $reasonCode.' must exist in registry.');
            $this->assertStringContainsString("('".$reasonCode."'", $seed, $reasonCode.' must exist in seed SQL.');
        }
    }

    public function test_provider_smoke_artifact_and_docs_are_tracked_without_false_pass(): void
    {
        $artifact = $this->read('storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt');
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $proofPack = $this->read('docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md');
        $inventory = $this->read('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');
        $ops = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        foreach ([$artifact, $status, $tracker, $proofPack, $inventory, $ops] as $document) {
            $this->assertStringContainsString('provider_smoke_status=', $document);
            $this->assertStringContainsString('reason_code=', $document);
            $this->assertStringContainsString('publication_created=false', $document);
            $this->assertStringContainsString('pointer_switched=false', $document);
        }

        $combinedDocs = $status.$tracker.$proofPack.$inventory.$ops;
        $this->assertStringContainsString('OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE', $combinedDocs);
        $this->assertStringContainsString('PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED', $combinedDocs);
        $this->assertStringContainsString('PROVIDER_RATE_LIMITED', $combinedDocs);
        $this->assertStringContainsString('BLOCKED_PROVIDER_RATE_LIMITED', $combinedDocs);
        $this->assertStringContainsString('OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT', $combinedDocs);
        $this->assertStringNotContainsString('[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED', $combinedDocs);
        $this->assertStringNotContainsString('[FINAL_DECISION] OPS_RUNTIME_PARITY_PASSED', $combinedDocs);
        $this->assertStringNotContainsString('[PROVIDER_SMOKE_STATUS] PROVIDER_SMOKE_PROOF_PASSED', $combinedDocs);
    }
}
