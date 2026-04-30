<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EligibilitySnapshotScopeRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class ReadablePublicationReadContractIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        config()->set('market_data.tickers.table', 'tickers');
        config()->set('market_data.tickers.id_column', 'ticker_id');
        config()->set('market_data.tickers.code_column', 'ticker_code');
        config()->set('market_data.session_snapshot.scope_default', 'universe_only');

        DB::table('tickers')->insert([
            ['ticker_id' => 1, 'ticker_code' => 'BBCA', 'is_active' => 'Yes'],
            ['ticker_id' => 2, 'ticker_code' => 'BMRI', 'is_active' => 'Yes'],
        ]);

        DB::table('eod_runs')->insert([
            'run_id' => 25,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => '2026-03-20',
            'run_id' => 25,
            'publication_version' => 1,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-20',
            'publication_id' => 10,
            'run_id' => 25,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_eligibility')->insert([
            [
                'trade_date' => '2026-03-20',
                'ticker_id' => 1,
                'eligible' => 1,
                'reason_code' => null,
                'run_id' => 25,
                'publication_id' => 10,
                'created_at' => '2026-03-20 17:20:00',
            ],
            [
                'trade_date' => '2026-03-20',
                'ticker_id' => 2,
                'eligible' => 0,
                'reason_code' => 'ELIG_NOT_ENOUGH_HISTORY',
                'run_id' => 25,
                'publication_id' => 10,
                'created_at' => '2026-03-20 17:20:00',
            ],
            [
                'trade_date' => '2026-03-20',
                'ticker_id' => 999,
                'eligible' => 1,
                'reason_code' => 'SHOULD_NOT_LEAK',
                'run_id' => 99,
                'publication_id' => 999,
                'created_at' => '2026-03-20 17:20:00',
            ],
        ]);
    }

    public function test_scope_repository_reads_only_pointer_resolved_readable_publication_rows(): void
    {
        $repository = new EligibilitySnapshotScopeRepository();

        $rows = $repository->getScopeForTradeDate('2026-03-20');

        $this->assertCount(2, $rows);
        $this->assertSame(['1', '2'], array_map('strval', array_column($rows, 'ticker_id')));
    }

    public function test_scope_repository_returns_empty_when_current_pointer_run_is_not_readable(): void
    {
        DB::table('eod_runs')->where('run_id', 25)->update([
            'publishability_state' => 'NOT_READABLE',
        ]);

        $repository = new EligibilitySnapshotScopeRepository();

        $this->assertSame([], $repository->getScopeForTradeDate('2026-03-20'));
    }

    public function test_evidence_repository_exports_only_pointer_resolved_readable_publication_rows(): void
    {
        $repository = new EodEvidenceRepository();

        $rows = $repository->exportEligibilityRows('2026-03-20', 10);
        $reasons = $repository->dominantReasonCodes(25, '2026-03-20', 10);

        $this->assertCount(2, $rows);
        $this->assertSame(['1', '2'], array_map('strval', array_column($rows, 'ticker_id')));
        $this->assertSame('ELIG_NOT_ENOUGH_HISTORY', $reasons[0]['reason_code']);
        $this->assertSame(1, $reasons[0]['count']);
    }

    public function test_evidence_repository_does_not_leak_rows_when_publication_is_not_current_pointer(): void
    {
        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->update([
            'publication_id' => 11,
            'run_id' => 26,
            'publication_version' => 2,
        ]);

        $repository = new EodEvidenceRepository();

        $this->assertSame([], $repository->exportEligibilityRows('2026-03-20', 10));
        $this->assertSame([], $repository->dominantReasonCodes(25, '2026-03-20', 10));
    }
}
