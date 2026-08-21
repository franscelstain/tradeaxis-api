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

    /**
     * The safe-mode surface is read from the command's own signature rather than matched as
     * source text. The defaults are the safety property here: one ticker, a short timeout and
     * no retries keep a smoke test from turning into a full provider sweep. Reflection proves
     * those values however the signature is formatted.
     */
    public function test_provider_smoke_command_is_registered_with_safe_single_ticker_surface(): void
    {
        $this->assertStringContainsString(
            'ProviderSmokeCommand::class',
            $this->read('app/Console/Kernel.php'),
            'Provider smoke must be registered in the kernel.'
        );

        $signature = $this->commandSignature(\App\Console\Commands\MarketData\ProviderSmokeCommand::class);

        $this->assertStringStartsWith('market-data:provider:smoke', $signature);

        foreach (['ticker=', 'trade_date=', 'dry-run', 'json'] as $option) {
            $this->assertStringContainsString('{--'.$option.'}', $signature, '--'.$option.' must exist.');
        }

        // Defaults that keep the command safe.
        foreach ([
            'max-tickers=1' => 'a smoke test must never fan out beyond one ticker',
            'retry-max=0' => 'retries would turn a smoke test into repeated provider load',
            'timeout=10' => 'a short timeout keeps a hung provider from stalling the operator',
            'provider=yahoo' => 'the default provider must be explicit',
        ] as $option => $why) {
            $this->assertStringContainsString('{--'.$option.'}', $signature, $why);
        }
    }

    private function commandSignature(string $commandClass): string
    {
        $property = (new ReflectionClass($commandClass))->getProperty('signature');
        $property->setAccessible(true);

        return (string) $property->getValue(
            (new ReflectionClass($commandClass))->newInstanceWithoutConstructor()
        );
    }

    /**
     * The command must not reach for any publishing machinery at all.
     *
     * That a run writes nothing is now proven by ProviderSmokeSafetyTest, which counts rows in
     * every artifact table before and after. This keeps the complementary half: the symbols that
     * would make writing possible must not appear, which execution cannot show — a path that is
     * never taken in a test still exists.
     */
    public function test_provider_smoke_cannot_reach_publishing_machinery(): void
    {
        $command = $this->read('app/Console/Commands/MarketData/ProviderSmokeCommand.php');

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
        $registry = $this->read('docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md');

        foreach ([
            'PROVIDER_SMOKE_OK',
            'PROVIDER_RATE_LIMITED',
            'PROVIDER_REQUEST_HEADER_CONTEXT_MISMATCH',
            'PROVIDER_TIMEOUT',
            'PROVIDER_NETWORK_ERROR',
            'PROVIDER_RESPONSE_PARSE_FAILED',
            'PROVIDER_EMPTY_OR_INVALID_RESPONSE',
            'PROVIDER_TRADE_DATE_NOT_FOUND_IN_RESPONSE',
            'PROVIDER_SMOKE_TICKER_REQUIRED',
            'PROVIDER_SMOKE_INVALID_TICKER',
            'provider_smoke_status',
            'reason_code',
            'request_url',
            'http_status',
            'response_body_sample',
            'adapter_reason_code',
            'retry_max',
            'BLOCKED',
            'FAILED',
            'PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }

        // The reason codes the command can emit are read out of the command itself and
        // checked against the registry. The former version listed them a second time by hand
        // and also checked the seed file, which is now redundant: ReasonCodeSeedExecutionTest
        // executes the seed and proves every registry code reaches eod_reason_codes.
        preg_match_all("/'(PROVIDER_[A-Z0-9_]+)'/", $command, $matches);

        $emitted = array_values(array_unique($matches[1]));
        sort($emitted);

        $this->assertNotEmpty($emitted, 'Provider smoke must emit at least one reason code.');

        $unregistered = array_values(array_filter($emitted, function ($code) use ($registry) {
            return strpos($registry, '`'.$code.'`') === false;
        }));

        $this->assertSame([], $unregistered, 'Reason codes emitted by provider smoke but missing from the registry.');
    }


    // The BLOCKED-not-FAILED classification was pinned here by matching two exact lines of
    // formatted PHP, which would break on an indentation change while proving nothing about
    // behaviour. ProviderSmokeSafetyTest drives every classification through the real command
    // with a mocked adapter, and asserts the exit codes a scheduler actually reads: BLOCKED is 2
    // and means retry, FAILED is 1 and means look at the platform.

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
            $this->assertStringContainsString('seal_executed=false', $document);
            $this->assertStringContainsString('finalize_executed=false', $document);
            $this->assertStringContainsString('pointer_switched=false', $document);
            $this->assertStringContainsString('readable_publication_created=false', $document);
            $this->assertStringContainsString('full_universe_fetch=false', $document);
        }

        foreach ([
            'request_url=',
            'http_status=',
            'response_body_sample=',
            'adapter_reason_code=',
            'retry_max=',
            'attempt_count=',
            'timeout_seconds=',
        ] as $needle) {
            $this->assertStringContainsString($needle, $artifact);
        }

        $combinedDocs = $status.$tracker.$proofPack.$inventory.$ops;
        $this->assertStringContainsString('OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE', $combinedDocs);
        $this->assertStringContainsString('PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED', $combinedDocs);
        $this->assertStringContainsString('PROVIDER_SMOKE_OK', $combinedDocs);
        $this->assertStringContainsString('FINAL_PROVIDER_SMOKE=PASSED', $combinedDocs);
        $this->assertStringContainsString('OPS_RUNTIME_PARITY_PASSED', $combinedDocs);
        
        if (preg_match('/(\[SESSION_STATUS\] OPS_RUNTIME_PARITY_PASSED|Ops rollout\/runtime parity: `OPS_RUNTIME_PARITY_PASSED`|Current rollout status is `OPS_RUNTIME_PARITY_PASSED`)/', $combinedDocs)) {
            $this->assertStringContainsString('provider_smoke_status=PASS', $artifact);
            $this->assertStringContainsString('reason_code=PROVIDER_SMOKE_OK', $artifact);
        } else {
            $this->assertStringNotContainsString('[PROVIDER_SMOKE_STATUS] PROVIDER_SMOKE_PROOF_PASSED', $combinedDocs);
        }
    }

    public function test_public_api_adapter_uses_browser_like_header_context(): void
    {
        $adapter = $this->read('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');

        foreach ([
            'MARKET_DATA_SOURCE_API_USER_AGENT',
            'market_data.source.api.user_agent',
            'User-Agent: ',
            'Accept: application/json,text/plain,*/*',
            'Accept-Language: en-US,en;q=0.9,id;q=0.8',
            'Connection: close',
        ] as $needle) {
            $this->assertStringContainsString($needle, $adapter.$config.$envExample.$envTesting);
        }

        $this->assertStringContainsString('{period1}', $adapter);
        $this->assertStringContainsString('{period2}', $adapter);
    }
}
