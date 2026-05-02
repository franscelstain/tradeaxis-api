<?php

use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class ReplayResultRepositoryIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    public function test_replay_result_repository_persists_metric_and_reason_code_counts(): void
    {
        $repo = new ReplayResultRepository();

        $repo->upsertMetric([
            'replay_id' => 3001,
            'trade_date' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'fixture',
            'status' => 'COMPLETED',
            'comparison_result' => 'MATCH',
            'comparison_note' => 'matched all artifacts',
            'config_identity' => 'cfg-1',
            'publication_version' => 2,
            'publishability_state' => 'READABLE',
            'publication_id' => 44,
            'publication_run_id' => 91,
            'is_current_publication' => true,
            'bars_batch_hash' => 'bars-hash',
            'indicators_batch_hash' => 'ind-hash',
            'eligibility_batch_hash' => 'elig-hash',
            'seal_state' => 'SEALED',
            'expected_status' => 'COMPLETED',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_publication_id' => 44,
            'expected_publication_run_id' => 91,
            'expected_is_current_publication' => true,
            'expected_trade_date_effective' => '2026-03-20',
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'cfg-1',
            'expected_publication_version' => 2,
            'expected_bars_batch_hash' => 'bars-hash',
            'expected_indicators_batch_hash' => 'ind-hash',
            'expected_eligibility_batch_hash' => 'elig-hash',
            'expected_reason_code_counts_json' => json_encode([['reason_code' => 'NONE', 'reason_count' => 0]]),
            'mismatch_summary' => null,
        ]);

        $repo->replaceReasonCodeCounts(3001, '2026-03-20', [
            ['reason_code' => 'BAR_TICKER_MAPPING_MISSING', 'reason_count' => 2],
            ['reason_code' => 'NONE', 'reason_count' => 0],
        ]);

        $metric = DB::table('md_replay_daily_metrics')->where('replay_id', 3001)->where('trade_date', '2026-03-20')->first();
        $this->assertSame('MATCH', $metric->comparison_result);
        $this->assertSame('READABLE', $metric->publishability_state);
        $this->assertSame(44, (int) $metric->publication_id);
        $this->assertSame(91, (int) $metric->publication_run_id);
        $this->assertSame(1, (int) $metric->is_current_publication);
        $this->assertSame('cfg-1', $metric->expected_config_identity);
        $this->assertSame('SUCCESS', $metric->expected_terminal_status);
        $this->assertSame('READABLE', $metric->expected_publishability_state);
        $this->assertSame(44, (int) $metric->expected_publication_id);
        $this->assertSame(91, (int) $metric->expected_publication_run_id);
        $this->assertSame(1, (int) $metric->expected_is_current_publication);

        $counts = DB::table('md_replay_reason_code_counts')
            ->where('replay_id', 3001)
            ->where('trade_date', '2026-03-20')
            ->orderBy('reason_code')
            ->get();

        $this->assertCount(2, $counts);
        $this->assertSame('BAR_TICKER_MAPPING_MISSING', $counts[0]->reason_code);
        $this->assertSame(2, (int) $counts[0]->reason_count);
    }
}
