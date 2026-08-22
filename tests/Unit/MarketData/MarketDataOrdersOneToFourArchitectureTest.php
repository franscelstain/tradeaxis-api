<?php

use PHPUnit\Framework\TestCase;

class MarketDataOrdersOneToFourArchitectureTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    public function test_application_services_depend_on_provider_neutral_source_ports(): void
    {
        foreach ([
            'app/Application/MarketData/Services/EodBarsIngestService.php',
            'app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php',
            'app/Application/MarketData/Services/BenchmarkBarsIngestService.php',
            'app/Application/MarketData/Services/SectorIndexApiIngestService.php',
        ] as $relative) {
            $source = file_get_contents($this->root().'/'.$relative);
            $this->assertStringNotContainsString('use App\\Infrastructure\\MarketData\\Source\\PublicApiEodBarsAdapter', $source, $relative);
            $this->assertStringContainsString('ApiEodBarsSource', $source, $relative);
        }
    }

    /**
     * `MD-S020-R0172` forbids flagging `candidate` on the word alone: it carries a legitimate
     * upstream sense, and `candidate_publication_id` is the boundary contract's own example. This
     * guard used to match bare `Candidate`, so a `CandidatePublicationRepository` would have been
     * reported as a downstream artifact. `Candidate` now requires a downstream-sense compound;
     * the other four terms carry no upstream sense in this domain and stay whole-word.
     */
    private function downstreamNamedArtifact(): string
    {
        return '/(Watchlist|Portfolio|Ranking|EntrySignal|Candidate(Rank|Score|Selection|Screening|Pick))/i';
    }

    public function test_market_data_application_has_no_watchlist_or_portfolio_named_artifact(): void
    {
        $violations = [];
        $scanned = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/app'));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') continue;
            $normalized = str_replace('\\', '/', $file->getPathname());
            if (strpos($normalized, '/MarketData/') === false) continue;
            $scanned++;
            if (preg_match($this->downstreamNamedArtifact(), $file->getFilename())) {
                $violations[] = $normalized;
            }
        }

        $this->assertGreaterThan(100, $scanned, 'the artifact-name scan must reach the market-data tree');
        $this->assertSame([], $violations);
    }

    /**
     * Both directions, as `MD-S020-R0172` requires: the downstream sense is still caught, and the
     * legitimate upstream name is not.
     */
    public function test_the_artifact_name_guard_targets_the_downstream_sense_not_the_word(): void
    {
        foreach (['WatchlistService.php', 'PortfolioActionJob.php', 'RankingRepository.php', 'EntrySignalBuilder.php', 'CandidateRankService.php', 'CandidateSelectionPolicy.php'] as $downstream) {
            $this->assertSame(1, preg_match($this->downstreamNamedArtifact(), $downstream), $downstream.' states the downstream sense and must be flagged');
        }

        foreach (['CandidatePublicationRepository.php', 'CandidatePriceFactorService.php', 'PublishTargetResolver.php', 'CoveragePolicyEvaluator.php'] as $upstream) {
            $this->assertSame(0, preg_match($this->downstreamNamedArtifact(), $upstream), $upstream.' carries the legitimate upstream sense and must not be flagged');
        }
    }

    public function test_read_product_does_not_apply_strategy_screening_or_current_active_state(): void
    {
        $source = file_get_contents($this->root().'/app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');

        $this->assertStringNotContainsString("where('elig.eligible', 1)", $source);
        $this->assertStringNotContainsString('active_column', $source);
        $this->assertStringNotContainsString('REQUIRED_WATCHLIST', $source);
        $this->assertStringContainsString("'data_usable'", $source);
    }

    public function test_yahoo_capability_boundary_is_executable_and_adjusted_close_has_no_close_fallback(): void
    {
        $source = file_get_contents($this->root().'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertStringContainsString('provides_actual_traded_value', $source);
        $this->assertStringContainsString('provides_official_board_or_trading_status', $source);
        $this->assertStringContainsString("'adj_close' => \$adjclose[\$position] ?? null", $source);
        $this->assertStringNotContainsString("\$adjclose[\$position] ?? (\$quote['close'][\$position]", $source);
    }
}
