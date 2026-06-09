<?php

use PHPUnit\Framework\TestCase;

class WatchlistBacktestRuntimeArtifactStaticGuardTest extends TestCase
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

    public function test_runtime_artifact_and_metrics_services_exist_with_required_shape_terms(): void
    {
        $artifact = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php');
        $metrics = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php');

        $this->assertStringContainsString('class WatchlistBacktestRuntimeArtifactService', $artifact);
        $this->assertStringContainsString('class WatchlistBacktestMetricsService', $metrics);
        $this->assertStringContainsString('buildArtifact', $artifact);
        $this->assertStringContainsString('buildMetrics', $metrics);
        $this->assertStringContainsString('input_manifest', $artifact);
        $this->assertStringContainsString('artifact_manifest', $artifact);
        $this->assertStringContainsString('validation', $artifact);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $metrics);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $metrics);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', $metrics);
        $this->assertStringContainsString('reason_code_distribution', $metrics);
    }

    public function test_runtime_artifact_and_metrics_do_not_read_raw_market_data_or_use_latest_shortcuts(): void
    {
        $combined = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php')
            .$this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php');

        $this->assertDoesNotMatchRegularExpression('/DB::table\s*\(/i', $combined);
        $this->assertDoesNotMatchRegularExpression('/\b(eod_bars|eod_indicators|eod_eligibility|eod_current_publication_pointer|staging|raw_provider)\b/i', $combined);
        $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $combined);
        $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $combined);
        $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(/i', $combined);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'"]trade_date[\'"]\s*\)/i', $combined);
        $this->assertDoesNotMatchRegularExpression('/->\s*orderBy\s*\(\s*[\'"]trade_date[\'"]\s*,\s*[\'"]desc[\'"]\s*\)/i', $combined);
    }

    public function test_runtime_artifact_and_metrics_preserve_no_api_command_schema_and_execution_boundary(): void
    {
        $combined = $this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php')
            .$this->readProjectFile('app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php');

        foreach ([
            'portfolio_allocation',
            'capital_allocation',
            'suggested_lots',
            'order_instruction',
            'execution_action',
            'broker_instruction',
            'entry_price_instruction',
            'take_profit_instruction',
            'stop_loss_instruction',
            'buy_signal',
            'sell_signal',
            'Controller',
            'Route::',
            'Artisan::',
            'Command extends',
            'Schema::',
            'Migration',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $combined, $forbidden);
        }
    }

    public function test_runtime_artifact_docs_are_synchronized_for_current_session(): void
    {
        $status = $this->readProjectFile('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $combined = $status.$tracker;

        $this->assertStringContainsString('BACKTEST RUNTIME ARTIFACT AND METRICS EXECUTION SESSION', $combined);
        $this->assertStringContainsString('WatchlistBacktestRuntimeArtifactService.php', $combined);
        $this->assertStringContainsString('WatchlistBacktestMetricsService.php', $combined);
        $this->assertStringContainsString('WatchlistBacktestRuntimeArtifactServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistBacktestMetricsServiceTest.php', $combined);
        $this->assertStringContainsString('WatchlistBacktestRuntimeArtifactStaticGuardTest.php', $combined);
        $this->assertStringContainsString('DONE for runtime artifact and metrics foundation unit/static scope', $combined);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $combined);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $combined);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', $combined);
        $this->assertStringContainsString('Watchlist Production Ready', $combined);
        $this->assertStringContainsString('NO', $combined);
    }
}
