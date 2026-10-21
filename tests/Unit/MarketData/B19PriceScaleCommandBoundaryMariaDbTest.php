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

        foreach (['eod_bars_history', 'eod_indicators_history', 'eod_eligibility_history'] as $historyTable) {
            $history = DB::table($historyTable)->where('publication_id', $world['publication_id'])->first();
            $this->assertNotNull($history, 'Expected protected sealed history in '.$historyTable);
            try {
                DB::table($historyTable)->where('publication_id', $world['publication_id'])->update(['created_at' => '2030-01-01 00:00:00']);
                $this->fail('Direct SQL rewrote sealed history in '.$historyTable.'.');
            } catch (QueryException $error) {
                $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE', $error->getMessage());
            }
            try {
                DB::table($historyTable)->where('publication_id', $world['publication_id'])->delete();
                $this->fail('Direct SQL deleted sealed history from '.$historyTable.'.');
            } catch (QueryException $error) {
                $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE', $error->getMessage());
            }
            $candidate = (array) $history;
            $autoIncrement = DB::select("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND EXTRA LIKE '%auto_increment%'", [$historyTable]);
            foreach ($autoIncrement as $key) {
                unset($candidate[$key->COLUMN_NAME]);
            }
            try {
                DB::table($historyTable)->insert($candidate);
                $this->fail('Direct SQL inserted sealed history into '.$historyTable.'.');
            } catch (QueryException $error) {
                $this->assertStringContainsString('SEALED_PUBLICATION_IMMUTABLE', $error->getMessage());
            }
        }
        $identityFields = [
            'trade_date', 'run_id', 'publication_version', 'seal_state',
            'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash',
            'source_file_hash', 'source_file_hash_algorithm', 'source_file_size_bytes', 'source_file_row_count',
            'config_snapshot_id', 'factor_set_id', 'factor_set_hash',
            'observation_manifest_hash', 'semantic_observation_manifest_hash', 'seal_provenance_scope',
            'publication_manifest_hash', 'price_product_code', 'price_product_version', 'read_model_version',
            'readiness_state', 'source_scale_assessment_set_hash', 'market_structure_revision_set_hash',
            'factor_decision_set_hash', 'sealed_at', 'artifact_hash_profile', 'publication_semantic_profile',
            'correction_semantic_hash', 'seal_fingerprint',
        ];
        $sealed = DB::table('eod_publications')->where('publication_id', $world['publication_id'])->first();
        foreach ($identityFields as $field) {
            $column = DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('TABLE_NAME', 'eod_publications')->where('COLUMN_NAME', $field)->first();
            $this->assertNotNull($column, 'Missing sealed identity column '.$field);
            $old = $sealed->{$field};
            if (in_array(strtolower($column->DATA_TYPE), ['int', 'bigint', 'smallint', 'mediumint', 'tinyint', 'decimal', 'float', 'double'], true)) {
                $replacement = (int) $old === 0 ? 1 : 0;
            } elseif ($field === 'seal_state') {
                $replacement = 'UNSEALED';
            } elseif (in_array(strtolower($column->DATA_TYPE), ['date', 'datetime', 'timestamp'], true)) {
                $replacement = $old === '2000-01-01' || $old === '2000-01-01 00:00:00' ? '2001-01-01 00:00:00' : '2000-01-01 00:00:00';
            } else {
                $replacement = $old === 'E038_PROBE' ? 'E038_OTHER' : 'E038_PROBE';
            }
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

        require_once base_path('database/migrations/2026_10_21_000001_require_pointer_before_current_publication.php');
        try {
            (new RequirePointerBeforeCurrentPublication())->down();
            $this->fail('Migration rollback removed the current-publication write fence while sealed publications exist.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('CURRENT_PUBLICATION_POINTER_ROLLBACK_WOULD_REMOVE_PROTECTION', $error->getMessage());
        }
    }
}
