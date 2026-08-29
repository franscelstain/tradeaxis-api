<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B14-A001` boundary guards for three obligations that were satisfied by construction and
 * proven by nothing, so a later edit could have removed them silently.
 *
 * The recompute command is forbidden to acquire source or write master data; the indicator and
 * liquidity measures are forbidden to normalise, exclude or reweight a shortened session without a
 * versioned declaration; and the hash and number-format contract must stay aligned with the three
 * sibling contracts it names.
 */
class IndicatorEngineBoundaryB14Test extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $this->assertFileExists($path, $relative.' must exist for this guard to mean anything');

        return (string) file_get_contents($path);
    }

    /**
     * MD-S038-R0007 to R0013 and MD-S061-R0031: the recompute path reads immutable source and
     * master revisions and writes none of them.
     */
    public function test_the_recompute_path_never_writes_a_source_or_master_table(): void
    {
        $surfaces = [
            'app/Console/Commands/MarketData/RecomputeCurrentIndicatorsCommand.php',
            'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
            'app/Application/MarketData/Services/IndicatorVectorService.php',
        ];

        $readOnly = [
            'eod_bars',
            'market_data_corporate_actions',
            'market_data_trading_status_events',
            'market_benchmark_bars',
            'market_benchmark_indicators',
            'market_data_ticker_sector_membership',
        ];

        $scanned = 0;
        foreach ($surfaces as $surface) {
            $source = $this->read($surface);
            $scanned++;

            foreach ($readOnly as $table) {
                // A write is a query builder statement that names the table and then mutates it.
                $pattern = '/table\(\s*[\'"]'.preg_quote($table, '/').'[\'"]\s*\)[^;]{0,400}?'
                    .'->\s*(insert|insertGetId|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/s';

                $this->assertSame(
                    0,
                    preg_match_all($pattern, $source, $hits),
                    $surface.' writes to the read-only source/master table '.$table
                );
            }
        }

        $this->assertSame(3, $scanned, 'every recompute surface must be scanned, not just the first');
    }

    /**
     * MD-S038-R0014: the recompute command must not run a source or import command. Naming one in
     * order to forbid it is not the same as calling it, so this looks for the call.
     */
    public function test_the_recompute_command_never_dispatches_a_source_or_import_command(): void
    {
        $source = $this->read('app/Console/Commands/MarketData/RecomputeCurrentIndicatorsCommand.php');

        $forbidden = [
            'market-data:import', 'market-data:ingest', 'market-data:backfill',
            'market-data:sector', 'market-data:corporate-actions', 'market-data:trading-status',
        ];

        foreach ($forbidden as $command) {
            $this->assertSame(
                0,
                preg_match_all('/(call|callSilent|Artisan::call)\s*\(\s*[\'"]'.preg_quote($command, '/').'/', $source),
                'the recompute command dispatches '.$command
            );
        }

        // The command already states this about itself; the statement must remain true and present.
        $this->assertStringContainsString(
            'eod_bars_write_executed',
            $source,
            'the command must keep declaring that it executed no bar write'
        );
    }

    /**
     * MD-S041-R0045, R0046 and R0049: a shortened session depresses volume and narrows range for
     * calendar reasons. The measures must not silently normalise, exclude or reweight it. No
     * treatment exists, so none is declared — and this fails the moment one appears undeclared.
     */
    public function test_no_measure_silently_treats_a_shortened_session(): void
    {
        $measures = [
            'app/Application/MarketData/Services/IndicatorVectorService.php',
            'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
            'app/Application/MarketData/Services/BenchmarkIndicatorVectorService.php',
        ];

        $scanned = 0;
        foreach ($measures as $measure) {
            $source = $this->read($measure);
            $scanned++;

            foreach (['is_half_day', 'half_day', 'halfDay', 'shortened_session', 'shortenedSession'] as $token) {
                $this->assertStringNotContainsString(
                    $token,
                    $source,
                    $measure.' reads '.$token.', which is a shortened-session treatment the measure contract '
                        .'does not declare as a versioned decision'
                );
            }
        }

        $this->assertSame(3, $scanned, 'every measure surface must be scanned');

        // The calendar still records the fact; the prohibition is on acting on it here, not on
        // knowing it. Without this the test above would also pass if the concept vanished entirely.
        $this->assertStringContainsString(
            'is_half_day',
            $this->read('app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php'),
            'the calendar must still carry the shortened-session fact for the prohibition to have a subject'
        );
    }

    /**
     * MD-S041-R0053: indicator, benchmark, mutation-impact, replay and API warm-up windows resolve
     * through one calendar identity, not five private notions of a trading day.
     */
    public function test_all_five_window_surfaces_resolve_through_one_calendar(): void
    {
        $surfaces = [
            'indicator' => 'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
            'benchmark' => 'app/Infrastructure/Persistence/MarketData/MarketBenchmarkRepository.php',
            'mutation-impact' => 'app/Application/MarketData/Services/EodBarsMutationImpactResolver.php',
            'replay' => 'app/Application/MarketData/Services/ReplayBackfillService.php',
            'api-warm-up' => 'app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php',
        ];

        foreach ($surfaces as $role => $path) {
            $this->assertStringContainsString(
                'MarketCalendarRepository',
                $this->read($path),
                $role.' does not resolve its window through the governed calendar'
            );
        }

        $this->assertCount(5, $surfaces, 'all five named surfaces must be checked');
    }

    /**
     * MD-S034-R0024 to R0026: the hash and number-format contract names three sibling contracts it
     * must stay aligned with. Each must resolve to a current document, and the locked formatting
     * rules must not be contradicted by any of them.
     */
    public function test_the_named_alignment_contracts_resolve_and_do_not_contradict_the_format_rule(): void
    {
        $hashContract = $this->read('docs/market_data/authority/strategy/book/Hash_Number_Formatting_LOCKED.md');

        // The paths inside the document are the pre-move strings the authority registry records as
        // preserved, so the targets are resolved by name rather than by literal relative path.
        $named = [
            'EOD_Indicators_Formula_Spec.md' =>
                'docs/market_data/authority/strategy/indicators/EOD_Indicators_Formula_Spec.md',
            'Indicator_Test_Vectors_LOCKED.md' =>
                'docs/market_data/development/implementation/tests/specs/Indicator_Test_Vectors_LOCKED.md',
            'Indicator_Expected_Output_Oracle_LOCKED.md' =>
                'docs/market_data/development/implementation/tests/specs/Indicator_Expected_Output_Oracle_LOCKED.md',
        ];

        foreach ($named as $name => $path) {
            $this->assertStringContainsString(
                $name,
                $hashContract,
                $name.' is no longer named by the alignment section this rule is drawn from'
            );
            $this->assertFileExists(
                $this->root().'/'.$path,
                $name.' is named as an alignment target but resolves to nothing'
            );
        }

        // Locale-dependent formatting is what the contract forbids. None of the aligned contracts
        // may state the opposite.
        foreach ($named as $name => $path) {
            $aligned = $this->read($path);
            foreach (['thousands separator', 'scientific notation'] as $forbidden) {
                if (stripos($aligned, $forbidden) === false) {
                    continue;
                }
                $this->assertMatchesRegularExpression(
                    '/(no|never|not|forbidden|prohibited)[^.\n]{0,80}'.preg_quote($forbidden, '/').'/i',
                    $aligned,
                    $name.' mentions '.$forbidden.' without forbidding it, which contradicts the locked format rule'
                );
            }
        }
    }
    /**
     * MD-S038-R0028 is conditional on a `--technical-only` recompute mode existing as an accepted
     * production command. The contract states it does not, and the console surface agrees, so the
     * obligation is not applicable today.
     *
     * It is asserted rather than assumed. The day someone introduces the mode this fails, and the
     * predicate becomes applicable again with its own obligation to prove: that the mode preserves
     * publication context from the prior current publication instead of re-resolving it from source
     * tables. A condition recorded as not applicable and never re-checked is how an obligation
     * disappears.
     */
    public function test_no_technical_only_recompute_mode_exists_to_carry_the_conditional_obligation(): void
    {
        $command = $this->read('app/Console/Commands/MarketData/RecomputeCurrentIndicatorsCommand.php');

        preg_match('/protected \$signature = \'(.*?)\';/s', $command, $signature);
        $this->assertNotEmpty($signature, 'the command signature must be readable for this to mean anything');
        $this->assertStringContainsString(
            'market-data:eod-indicators:recompute-current',
            $signature[1],
            'the guard must be reading the recompute command'
        );

        foreach (['technical-only', 'technical_only', 'technicalOnly'] as $token) {
            $this->assertStringNotContainsString(
                $token,
                $signature[1],
                'a technical-only mode now exists, so MD-S038-R0028 is applicable and must be proven '
                    .'rather than left recorded as not applicable'
            );
        }

        $surfaces = glob($this->root().'/app/Console/Commands/MarketData/*.php');
        $this->assertGreaterThan(5, count($surfaces), 'the console surface scan must reach the commands');

        foreach ($surfaces as $surface) {
            $this->assertStringNotContainsString(
                'technical-only',
                (string) file_get_contents($surface),
                basename($surface).' introduces a technical-only mode'
            );
        }
    }
}
