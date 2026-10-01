<?php

use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * D-MD-B10-A002-005: identities derived from producer captures follow the captured content, not
 * the run-scope keys that make a capture slot local to the run that took it.
 *
 * Two runs capture the same producer input through the real writer, one under different
 * configuration snapshot, publication and run ids. Their slot and payload hashes differ, as a
 * per-run storage identity must; their semantic payload hashes agree. A change to the content or
 * to a semantic selection coordinate moves the semantic hash.
 */
class CaptureSemanticPayloadIdentityTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';

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

    public function test_run_scope_keys_do_not_reach_the_semantic_payload_hash(): void
    {
        $universe = [['ticker_id' => 1, 'listing_id' => 1001, 'ticker_code' => 'AAAA']];
        $first = $this->capture($this->owningRun(301, 7001), $universe, ['publication_id' => 44]);
        $second = $this->capture($this->owningRun(302, 7002), $universe, ['publication_id' => 45]);

        $this->assertNotSame($first['slot_hash'], $second['slot_hash'], 'the slot is a per-run storage identity');
        $this->assertNotSame($first['payload_hash'], $second['payload_hash'], 'the stored payload carries its run-scope selection');
        $this->assertSame($this->semantic($first), $this->semantic($second),
            'the same content under other local ids must have one semantic identity');
    }

    public function test_content_and_semantic_selection_move_the_semantic_payload_hash(): void
    {
        $universe = [['ticker_id' => 1, 'listing_id' => 1001, 'ticker_code' => 'AAAA']];
        $baseline = $this->semantic($this->capture($this->owningRun(311, 7001), $universe, []));
        $otherContent = $this->semantic($this->capture($this->owningRun(312, 7001),
            array_merge($universe, [['ticker_id' => 2, 'listing_id' => 1002, 'ticker_code' => 'BBBB']]), []));
        $otherCoordinate = $this->semantic($this->capture($this->owningRun(313, 7001), $universe, ['use_history' => true]));

        $this->assertNotSame($baseline, $otherContent, 'changed content must move the semantic identity');
        $this->assertNotSame($baseline, $otherCoordinate, 'a changed semantic selection coordinate must move it');
        $this->assertSame(['config_snapshot_id', 'publication_id', 'run_id'], RunInputCaptureRepository::RUN_SCOPE_SELECTION_KEYS);
    }

    private function owningRun(int $runId, int $configSnapshotId): object
    {
        DB::table('eod_runs')->insert([
            'run_id' => $runId, 'trade_date_requested' => self::TRADE_DATE, 'trade_date_effective' => self::TRADE_DATE,
            'knowledge_cutoff_at' => self::TRADE_DATE.' 17:00:00', 'lifecycle_state' => 'RUNNING', 'stage' => 'ELIGIBILITY',
            'source' => 'manual_file', 'config_snapshot_id' => $configSnapshotId,
            'created_at' => self::TRADE_DATE.' 17:01:00', 'updated_at' => self::TRADE_DATE.' 17:01:00',
        ]);

        return DB::table('eod_runs')->where('run_id', $runId)->first();
    }

    private function capture(object $run, array $rows, array $selection): array
    {
        return (new RunInputCaptureRepository())->captureForProducer($run, 'ELIGIBILITY', 'universe_identity', 'eligibility-universe/v1', $rows, $selection);
    }

    private function semantic(array $capture): string
    {
        return RunInputCaptureRepository::semanticPayloadHash((new RunInputCaptureRepository())->verify($capture));
    }
}
