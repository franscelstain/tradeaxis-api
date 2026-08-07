<?php

use App\Application\MarketData\Services\EodBarsMutationImpactResolver;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for the out-of-order import impact resolver.
 *
 * `OutOfOrderImportImpactStaticGuardTest` asserted that the literal "50" appears inside
 * maxDependencyTradingDays() and that method names like tradingDatesBetween exist. Neither
 * shows that changing a historical bar actually flags the right downstream dates.
 *
 * The invariant matters because indicator windows reach backwards: a bar corrected today can
 * invalidate indicators for weeks of later dates, and any of those already published needs a
 * correction rather than a silent rewrite.
 */
class BarMutationImpactResolverTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedTradingCalendarAndBars();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /** 120 weekday trading dates from 2026-01-05, each carrying one canonical bar. */
    private function seedTradingCalendarAndBars(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => 1, 'ticker_code' => 'BBCA', 'company_name' => 'BBCA', 'is_active' => 1,
        ]);

        $calendar = [];
        $bars = [];
        $date = strtotime('2026-01-05');
        $added = 0;

        while ($added < 120) {
            if ((int) date('N', $date) <= 5) {
                $day = date('Y-m-d', $date);

                $calendar[] = [
                    'cal_date' => $day, 'is_trading_day' => 1, 'provenance_tier' => 'VERIFIED',
                    'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
                ];

                $bars[] = [
                    'trade_date' => $day, 'ticker_id' => 1,
                    'open' => 100, 'high' => 101, 'low' => 99, 'close' => 100,
                    'volume' => 1000, 'adj_close' => 100, 'source' => 'YAHOO_FINANCE',
                    'run_id' => 1, 'publication_id' => 1, 'created_at' => '2026-01-01 00:00:00',
                ];

                $added++;
            }

            $date = strtotime('+1 day', $date);
        }

        // Chunked: SQLite caps a statement at 999 bound variables, and 120 bars across
        // twelve columns overruns it in a single insert.
        foreach (array_chunk($calendar, 50) as $chunk) {
            DB::table('market_calendar')->insert($chunk);
        }

        foreach (array_chunk($bars, 50) as $chunk) {
            DB::table('eod_bars')->insert($chunk);
        }
    }

    private function tradingDates(): array
    {
        return DB::table('market_calendar')->where('is_trading_day', 1)
            ->orderBy('cal_date')->pluck('cal_date')
            ->map(function ($v) { return (string) $v; })->all();
    }

    private function resolve(array $summary): array
    {
        return app(EodBarsMutationImpactResolver::class)->resolve($summary);
    }

    public function test_unchanged_bars_produce_no_impact_at_all(): void
    {
        $result = $this->resolve([
            'changed_bar_count' => 0,
            'changed_trade_dates' => [],
            'changed_ticker_ids' => [],
        ]);

        $this->assertSame('NOOP_UNCHANGED_BARS', $result['indicator_impact_summary']['indicator_reprocess_state']);
        $this->assertSame(0, $result['indicator_impact_summary']['affected_trade_date_count']);
        $this->assertFalse($result['publication_impact_summary']['republication_required']);
    }

    /**
     * The horizon is asserted as an outcome rather than as a literal in the source: a bar
     * changed mid-series must drag the following dependency window with it.
     */
    public function test_a_changed_historical_bar_affects_the_full_dependency_horizon(): void
    {
        $dates = $this->tradingDates();
        $changed = $dates[10];

        $result = $this->resolve([
            'changed_bar_count' => 1,
            'changed_trade_dates' => [$changed],
            'changed_ticker_ids' => [1],
        ]);

        $summary = $result['indicator_impact_summary'];
        $horizon = (int) $summary['max_dependency_trading_days'];

        $this->assertGreaterThanOrEqual(50, $horizon, 'ma50 needs at least fifty trading days of history');
        $this->assertSame($changed, $summary['affected_start_date'], 'impact starts at the changed bar');
        $this->assertSame(
            $horizon + 1,
            $summary['affected_trade_date_count'],
            'the changed date plus its full forward dependency window must be affected'
        );
        $this->assertSame($dates[10 + $horizon], $summary['affected_end_date']);
    }

    /**
     * A bar changed at the very end of the series has no later dates to invalidate, so the
     * impact must not be padded with dates that do not exist.
     */
    public function test_impact_never_runs_past_the_last_available_trading_date(): void
    {
        $dates = $this->tradingDates();
        $changed = $dates[count($dates) - 3];

        $summary = $this->resolve([
            'changed_bar_count' => 1,
            'changed_trade_dates' => [$changed],
            'changed_ticker_ids' => [1],
        ])['indicator_impact_summary'];

        $this->assertSame(3, $summary['affected_trade_date_count']);
        $this->assertSame($dates[count($dates) - 1], $summary['affected_end_date']);
    }

    public function test_a_change_confined_to_the_last_date_reports_no_downstream_impact(): void
    {
        $dates = $this->tradingDates();

        $summary = $this->resolve([
            'changed_bar_count' => 1,
            'changed_trade_dates' => [$dates[count($dates) - 1]],
            'changed_ticker_ids' => [1],
        ])['indicator_impact_summary'];

        $this->assertSame('REPROCESS_REQUIRED_REQUESTED_DATES_ONLY', $summary['indicator_reprocess_state']);
        $this->assertSame('BAR_CHANGED_REQUESTED_DATE_ONLY', $summary['impact_reason']);
    }

    /**
     * Silent rewriting of a published dataset is what the correction flow exists to prevent,
     * so an affected readable publication must escalate rather than be reprocessed in place.
     */
    public function test_an_affected_readable_publication_requires_republication(): void
    {
        $dates = $this->tradingDates();
        $changed = $dates[10];

        DB::table('eod_runs')->insert([
            'run_id' => 25, 'trade_date_requested' => $dates[12], 'trade_date_effective' => $dates[12],
            'lifecycle_state' => 'COMPLETED', 'quality_gate_state' => 'PASS', 'stage' => 'FINALIZE',
            'source' => 'manual_file', 'publication_id' => 10, 'publication_version' => 1,
            'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS', 'is_current_publication' => 1,
            // Readable resolution requires seal proof on the run as well as the publication,
            // plus complete coverage telemetry. The read side refuses a publication whose
            // coverage context is only partly recorded.
            'sealed_at' => '2026-01-01 00:00:00',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.000000',
            'coverage_min_threshold' => '0.980000',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'started_at' => '2026-01-01 00:00:00', 'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10, 'trade_date' => $dates[12], 'run_id' => 25,
            'publication_version' => 1, 'is_current' => 1, 'seal_state' => 'SEALED',
            'sealed_at' => '2026-01-01 00:00:00', 'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $dates[12], 'publication_id' => 10, 'run_id' => 25,
            'publication_version' => 1, 'sealed_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);

        $publication = $this->resolve([
            'changed_bar_count' => 1,
            'changed_trade_dates' => [$changed],
            'changed_ticker_ids' => [1],
        ])['publication_impact_summary'];

        $this->assertTrue($publication['readable_publication_impacted']);
        $this->assertTrue($publication['republication_required']);
        $this->assertSame('REQUIRES_REPUBLICATION', $publication['publication_impact_state']);
        $this->assertContains($dates[12], $publication['impacted_readable_trade_dates']);
    }
}
