<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * The snapshot and promote paths copy indicator rows through explicit column lists rather
 * than SELECT *, so a column added to the write path is silently dropped unless both lists
 * are updated too.
 *
 * That is exactly what happened to corporate_action_window_reasons: it reached
 * eod_indicators_history but never eod_indicators, leaving rows that reported a
 * contamination reason code with no record of which action caused it.
 */
class IndicatorArtifactColumnParityTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function indicatorRow(array $overrides = []): array
    {
        return $overrides + [
            'trade_date' => '2026-07-15',
            'ticker_id' => 1,
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_CORPORATE_ACTION_DISCONTINUITY',
            'indicator_set_version' => 'v1',
            'corporate_action_window_reasons' => 'STOCK_SPLIT@2026-07-15',
            'run_id' => 9001,
            'publication_id' => 55,
            'created_at' => '2026-07-15 18:00:00',
        ];
    }

    public function test_snapshot_to_history_preserves_the_contamination_trail(): void
    {
        DB::table('eod_indicators')->insert($this->indicatorRow());

        (new EodArtifactRepository())->snapshotPublicationFromCurrentTables('2026-07-15', 55, 9001);

        $this->assertSame(
            'STOCK_SPLIT@2026-07-15',
            DB::table('eod_indicators_history')
                ->where('publication_id', 55)
                ->where('trade_date', '2026-07-15')
                ->value('corporate_action_window_reasons')
        );
    }

    public function test_promote_to_current_preserves_the_contamination_trail(): void
    {
        DB::table('eod_indicators_history')->insert($this->indicatorRow());

        (new EodArtifactRepository())->promotePublicationHistoryToCurrent('2026-07-15', 55, 9001);

        $this->assertSame(
            'STOCK_SPLIT@2026-07-15',
            DB::table('eod_indicators')
                ->where('trade_date', '2026-07-15')
                ->value('corporate_action_window_reasons')
        );
    }

    /**
     * Guards the whole class of defect rather than this one column: any column present on
     * eod_indicators must also exist on eod_indicators_history, so the two artifacts cannot
     * drift apart again.
     */
    public function test_current_and_history_indicator_tables_expose_the_same_columns(): void
    {
        $current = Schema::getColumnListing('eod_indicators');
        $history = Schema::getColumnListing('eod_indicators_history');

        sort($current);
        sort($history);

        $this->assertSame(
            [],
            array_values(array_diff($current, $history)),
            'columns on eod_indicators that eod_indicators_history is missing'
        );

        $this->assertSame(
            [],
            array_values(array_diff($history, $current)),
            'columns on eod_indicators_history that eod_indicators is missing'
        );
    }
}
