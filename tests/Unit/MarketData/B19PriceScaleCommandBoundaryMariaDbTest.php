<?php

use App\Console\Commands\MarketData\RepairPriceScaleStretchesCommand;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19PriceScaleCommandBoundaryMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    private function executeRepair(array $arguments): array
    {
        $command = new RepairPriceScaleStretchesCommand();
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        return [$tester->execute($arguments), $tester->getDisplay()];
    }

    private function protectedState(): array
    {
        $result = [];
        foreach (['eod_bars', 'eod_bars_history', 'eod_publications',
            'eod_current_publication_pointer', 'eod_runs', 'market_data_price_scale_breaks'] as $table) {
            $result[$table] = DB::table($table)->orderBy(DB::raw('1'))->get()->toJson();
        }

        return $result;
    }

    public function test_price_scale_detection_is_read_only_and_apply_is_explicitly_refused(): void
    {
        $world = R0025SyntheticV2World::build();
        $bar = DB::table('eod_bars')->first();
        $this->assertNotNull($bar);
        DB::table('market_data_price_scale_breaks')->insert([
            'ticker_id' => $bar->ticker_id,
            'ticker_code' => 'B19PROBE',
            'trade_date' => $bar->trade_date,
            'previous_close' => 200,
            'open_price' => 100,
            'implied_ratio' => 2,
            'ratio_direction' => 'DOWN',
            'break_type' => 'SCALE_SHIFT',
            'match_status' => 'UNEXPLAINED',
            'review_status' => 'DETECTED',
            'detection_contract_version' => 'b19_probe_v1',
            'detected_at' => '2026-03-17 18:00:00',
        ]);

        $before = $this->protectedState();
        [$dryCode, $dryOutput] = $this->executeRepair(['--dry-run' => true]);
        $this->assertSame(0, $dryCode, $dryOutput);
        $this->assertStringContainsString('status=DETECTION_ONLY', $dryOutput);
        $this->assertStringContainsString('reason_code=IMMUTABLE_HISTORY_CORRECTION_REQUIRED', $dryOutput);
        $this->assertStringContainsString('skipped=1', $dryOutput);
        $this->assertSame($before, $this->protectedState(), 'Detection must not alter canonical, historical, seal, pointer, run or break state.');

        [$applyCode, $applyOutput] = $this->executeRepair(['--apply' => true]);
        $this->assertSame(1, $applyCode, $applyOutput);
        $this->assertStringContainsString('status=BLOCKED', $applyOutput);
        $this->assertStringContainsString('reason_code=IMMUTABLE_HISTORY_CORRECTION_REQUIRED', $applyOutput);
        $this->assertSame($before, $this->protectedState(), 'Prohibited apply must not mutate protected state.');

        [$conflictCode, $conflictOutput] = $this->executeRepair(['--dry-run' => true, '--apply' => true]);
        $this->assertSame(1, $conflictCode, $conflictOutput);
        $this->assertStringContainsString('reason_code=COMMAND_CONFLICTING_OPTIONS', $conflictOutput);
        $this->assertSame($before, $this->protectedState());

        $history = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->first();
        $this->assertNotNull($history);
        try {
            DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])
                ->where('ticker_id', $history->ticker_id)->update(['close' => 1]);
            $this->fail('Direct SQL rewrote sealed canonical history.');
        } catch (QueryException $error) {
            $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE', $error->getMessage());
        }
        foreach ([
            'bars_batch_hash' => str_repeat('0', 64),
            'publication_manifest_hash' => str_repeat('1', 64),
            'seal_state' => 'UNSEALED',
        ] as $field => $replacement) {
            try {
                DB::table('eod_publications')->where('publication_id', $world['publication_id'])
                    ->update([$field => $replacement]);
                $this->fail('Direct SQL rewrote sealed publication '.$field.'.');
            } catch (QueryException $error) {
                $this->assertStringContainsString('SEALED_PUBLICATION_IDENTITY_IMMUTABLE', $error->getMessage());
            }
        }
        try {
            DB::table('eod_publications')->where('publication_id', $world['publication_id'])->delete();
            $this->fail('Direct SQL deleted a sealed publication.');
        } catch (QueryException $error) {
            $this->assertStringContainsString('SEALED_PUBLICATION_IDENTITY_IMMUTABLE', $error->getMessage());
        }
        DB::table('eod_publications')->where('publication_id', $world['publication_id'])->update(['is_current' => 0]);
        DB::table('eod_publications')->where('publication_id', $world['publication_id'])->update(['is_current' => 1]);
        $this->assertSame($before, $this->protectedState());
    }
}
