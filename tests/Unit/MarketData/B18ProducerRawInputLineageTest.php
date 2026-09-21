<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\ProducerRawInputLineage;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerRawInputLineageTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); \Carbon\Carbon::setTestNow('2026-03-25 10:30:00'); }
    protected function tearDown(): void { \Carbon\Carbon::setTestNow(); $this->tearDownMarketDataSqlite(); parent::tearDown(); }

    private function runContext() { return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'COMPUTE_INDICATORS', null, 'c07-raw'); }
    private function scoped($run, callable $read)
    {
        $repo = new class extends RunInputCaptureRepository { public function captureRegistryVersions($run): array { return []; } };
        return ProducerInputScope::during($run, 'COMPUTE_INDICATORS', 'c07-raw/v1', $read, $repo);
    }
    private function artifacts(): EodArtifactRepository
    {
        $calendar = new class extends MarketCalendarRepository { public function tradingDateWindowStart($date, $days, $allowPartialWindow = true, $knownAt = null) { return '2026-03-23'; } };
        return new EodArtifactRepository($calendar);
    }
    private function seed(string $date, int $pub, int $ticker = 1, string $table = 'eod_bars'): array
    {
        $source = new SourceObservationRepository();
        $row = ['listing_id' => 9000 + $ticker, 'provider_mapping_id' => 7000 + $ticker, 'mapping_revision' => 'v1',
            'ticker_code' => 'RAW'.$ticker, 'trade_date' => $date, 'open' => 100, 'high' => 110,
            'low' => 90, 'close' => 105, 'volume' => 1000, 'source_row_ref' => 'row:'.$ticker];
        $out = $source->recordAcceptedRows($source->capture(['acquisition_batch_id' => 501, 'attempt_uid' => 'raw-'.$pub.'-'.$ticker,
            'requested_trade_date' => $date, 'source_mode' => 'api', 'source_name' => 'fixture', 'provider' => 'fixture',
            'provider_symbol' => 'RAW'.$ticker, 'sanitized_request_identity' => 'fixture:RAW'.$ticker,
            'adapter_version' => 'v1', 'payload' => '{"close":105}', 'acquired_at' => $date.' 17:00:00']), [$row]);
        $source->bindResolvedIdentity($out['source_observation_id'], $row['source_row_ref'], [
            'listing_id' => $row['listing_id'], 'provider_mapping_id' => $row['provider_mapping_id'],
            'mapping_revision' => 'v1', 'trade_date' => $date]);
        if (! DB::table('eod_publications')->where('publication_id', $pub)->exists()) DB::table('eod_publications')->insert([
            'publication_id' => $pub, 'trade_date' => $date, 'run_id' => 1, 'publication_version' => $pub,
            'seal_state' => 'UNSEALED', 'created_at' => $date.' 18:00:00']);
        $raw = array_intersect_key($row, array_flip(['listing_id','trade_date','open','high','low','close','volume']));
        $raw += ['ticker_id' => $ticker, 'source' => 'API', 'run_id' => 1, 'publication_id' => $pub,
            'source_observation_id' => $out['source_observation_id'], 'canonicalization_version' => 'v1',
            'price_product_code' => 'RAW', 'quality_state' => 'VALID', 'created_at' => $date.' 18:00:00'];
        DB::table($table)->insert($raw);
        return $raw;
    }
    private function capsules($run): array
    {
        $repo = new RunInputCaptureRepository(); $out = [];
        foreach ($repo->forRun($run->run_id) as $capture) {
            $p = $repo->verify($capture);
            if (($p['selection_context']['operation'] ?? null) === 'raw-input-lineage/v1') $out[] = $p;
        }
        $this->assertNotEmpty($out); return $out;
    }

    public function test_atr_preserves_full_actual_lineage_before_four_column_projection(): void
    {
        $this->seed('2026-03-23', 11); $this->seed('2026-03-24', 12, 1, 'eod_bars_history');
        $run = $this->runContext(); $repo = $this->artifacts();
        $expected = $repo->loadAtrSeriesForTickerFromBoundary(1, '2026-03-24', '2026-03-23', 12);
        $actual = $this->scoped($run, function () use ($repo) { return $repo->loadAtrSeriesForTickerFromBoundary(1, '2026-03-24', '2026-03-23', 12); });
        $this->assertSame($expected, $actual); $this->assertCount(2, $actual);
        $this->assertSame(['trade_date','high','low','close'], array_keys($actual[0]));
        $p = $this->capsules($run)[0]; $c = $p['rows'][0];
        $this->assertCount(2, $c['raw_rows']); $this->assertCount(2, $c['publications']);
        $this->assertSame(['eod_bars','eod_bars_history'], array_column($c['links'], 'table'));
        foreach ($c['links'] as $link) $this->assertCount(1, $link['compatible_normalized_row_ids']);
        $this->assertCount(4, $c['source_population']['tables']['md_source_observations']);
        ProducerRawInputLineage::assertValid($c, $p['selection_context']);
    }

    public function test_window_and_date_consume_same_materialization_and_retry_is_identical(): void
    {
        $this->seed('2026-03-23', 11); $this->seed('2026-03-24', 12); $this->seed('2026-03-24', 12, 2);
        $run = $this->runContext(); $repo = $this->artifacts();
        $read = function () use ($repo) { return [$repo->loadBarsWindow('2026-03-24', 2), $repo->loadBarsForTradeDate('2026-03-24')]; };
        $expected = $read(); $this->assertSame($expected, $this->scoped($run, $read));
        $before = $this->capsules($run); $this->assertCount(2, $before);
        $this->assertSame($expected, $this->scoped($run, $read)); $this->assertSame($before, $this->capsules($run));
        DB::table('eod_bars')->where('ticker_id', 2)->update(['quality_state' => 'CHANGED']);
        try { $this->scoped($run, $read); $this->fail('One changed RAW row must conflict'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage()); }
        $this->assertSame($before, $this->capsules($run));
    }

    public function test_missing_or_mismatched_lineage_in_one_member_fails_closed(): void
    {
        $this->seed('2026-03-24', 12); $this->seed('2026-03-24', 12, 2);
        $run = $this->runContext(); $repo = $this->artifacts();
        foreach (['publication_id' => 0, 'source_observation_id' => null, 'listing_id' => 999, 'close' => 999, 'run_id' => 999] as $field => $value) {
            DB::beginTransaction(); DB::table('eod_bars')->where('ticker_id', 2)->update([$field => $value]);
            try { $this->scoped($run, function () use ($repo) { return $repo->loadBarsForTradeDate('2026-03-24'); }); $this->fail('Missing lineage accepted: '.$field); }
            catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_', $e->getMessage()); }
            finally { DB::rollBack(); }
        }
    }

    public function test_content_validator_rejects_single_population_reference_and_hash_damage(): void
    {
        $this->seed('2026-03-24', 12); $this->seed('2026-03-24', 12, 2);
        $run = $this->runContext(); $repo = $this->artifacts();
        $this->scoped($run, function () use ($repo) { return $repo->loadBarsForTradeDate('2026-03-24'); });
        $p = $this->capsules($run)[0];
        foreach (['raw','publication','link','source'] as $kind) {
            $c = $p['rows'][0];
            if ($kind === 'raw') $c['raw_hash'] = str_repeat('0', 64);
            if ($kind === 'publication') $c['publications'][0]['trade_date'] = '2026-03-23';
            if ($kind === 'link') $c['links'][1]['compatible_normalized_row_ids'] = [];
            if ($kind === 'source') array_pop($c['source_population']['tables']['md_source_observation_rows']);
            try { ProducerRawInputLineage::assertValid($c, $p['selection_context']); $this->fail('Damaged '.$kind.' accepted'); }
            catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_', $e->getMessage()); }
        }
    }

    public function test_empty_boundary_is_explicit_and_historical_current_substitution_is_blocked(): void
    {
        $run = $this->runContext(); $repo = $this->artifacts();
        $this->assertSame([], $this->scoped($run, function () use ($repo) { return $repo->loadBarsForTradeDate('2026-03-24'); }));
        $this->assertSame('NO_RAW_ROWS_IN_EXPLICIT_READ_BOUNDARY', $this->capsules($run)[0]['rows'][0]['empty_basis']);
        $run->request_mode = 'replay_verify';
        $this->expectExceptionMessage('INPUT_CAPTURE_RAW_HISTORICAL_BINDING_REQUIRED');
        $this->scoped($run, function () use ($repo) { return $repo->loadBarsWindow('2026-03-24', 2, 12); });
    }

    public function test_actual_history_copy_preserves_source_and_explicit_target_without_live_fallback(): void
    {
        $run = $this->runContext(); $this->seed('2026-03-24', 12, 1, 'eod_bars_history');
        DB::table('eod_publications')->insert(['publication_id' => 13, 'trade_date' => '2026-03-24',
            'run_id' => $run->run_id, 'publication_version' => 13, 'seal_state' => 'UNSEALED', 'created_at' => '2026-03-25 10:30:00']);
        $before = (array) DB::table('eod_bars_history')->where('publication_id', 12)->first();
        $this->artifacts()->replaceBarsHistoryFromPublication('2026-03-24', 12, 13, $run->run_id);
        $after = (array) DB::table('eod_bars_history')->where('publication_id', 13)->first();
        foreach ($before as $key => $value) if (! in_array($key, ['publication_id','run_id','created_at'], true)) $this->assertSame($value, $after[$key], $key);
        $this->assertSame($before, (array) DB::table('eod_bars_history')->where('publication_id', 12)->first());
        $p = $this->capsules($run)[0];
        $this->assertSame('history-copy', $p['selection_context']['read_kind']);
        $this->assertSame(13, (int) $p['rows'][0]['copy_target']['publication_id']);
        $this->assertSame('eod_bars_history', $p['rows'][0]['links'][0]['table']);
        $this->assertSame(RunInputCaptureRepository::canonicalJson([$before]), RunInputCaptureRepository::canonicalJson($p['rows'][0]['raw_rows']));
        $this->artifacts()->replaceBarsHistoryFromPublication('2026-03-24', 12, 13, $run->run_id);
        $this->assertSame($p, $this->capsules($run)[0]);
        $bad = $p['rows'][0]; $bad['copy_target']['publication_id'] = 99;
        $this->expectExceptionMessage('INPUT_CAPTURE_RAW_COPY_TARGET_IDENTITY');
        ProducerRawInputLineage::assertValid($bad, $p['selection_context']);
    }

    public function test_current_history_copy_captures_projection_and_missing_target_rolls_back(): void
    {
        $run = $this->runContext(); $this->seed('2026-03-24', 12);
        DB::table('eod_publications')->insert(['publication_id' => 13, 'trade_date' => '2026-03-24',
            'run_id' => $run->run_id, 'publication_version' => 13, 'seal_state' => 'UNSEALED', 'created_at' => '2026-03-25 10:30:00']);
        $repo = $this->artifacts(); $repo->ensureBarsHistoryFromCurrentTradeDate('2026-03-24', 13, $run->run_id);
        $p = $this->capsules($run)[0];
        $this->assertSame('history-copy-current', $p['selection_context']['read_kind']);
        $this->assertSame('eod_bars', $p['rows'][0]['links'][0]['table']);
        $this->assertSame(12, (int) $p['rows'][0]['raw_rows'][0]['publication_id']);
        $this->assertSame(13, (int) $p['rows'][0]['copy_target']['publication_id']);
        $count = DB::table('md_run_input_captures')->count();
        try { $repo->replaceBarsHistoryFromPublication('2026-03-24', 12, 99, $run->run_id); $this->fail('Missing copy target accepted'); }
        catch (RuntimeException $e) { $this->assertSame('INPUT_CAPTURE_RAW_COPY_TARGET_IDENTITY', $e->getMessage()); }
        $this->assertSame($count, DB::table('md_run_input_captures')->count());
        $this->assertSame(0, DB::table('eod_bars_history')->where('publication_id', 99)->count());
    }

    public function test_sealed_reference_requires_matching_immutable_history_for_every_consumed_row(): void
    {
        $this->runContext(); $this->seed('2026-03-24', 12); $this->seed('2026-03-24', 12, 2);
        $run = (new EodRunRepository())->getOrCreateOwningRun('2026-03-25', 'api', 'COMPUTE_INDICATORS', null, 'c07-next-day');
        foreach (DB::table('eod_bars')->get() as $row) DB::table('eod_bars_history')->insert((array) $row);
        DB::table('eod_publications')->where('publication_id', 12)->update(['seal_state' => 'SEALED', 'bars_batch_hash' => str_repeat('a', 64)]);
        $repo = $this->artifacts();
        $read = function () use ($repo) { return $repo->loadBarsForTradeDate('2026-03-24'); };
        $this->scoped($run, $read); $p = $this->capsules($run)[0];
        $this->assertCount(2, $p['rows'][0]['sealed_history_rows']);
        foreach (['quality_state' => 'DAMAGED', 'source_observation_id' => 999] as $field => $value) {
            $c = $p['rows'][0]; $c['sealed_history_rows'][1][$field] = $value;
            $c['sealed_history_hash'] = ProducerRawInputLineage::hash($c['sealed_history_rows']);
            try { ProducerRawInputLineage::assertValid($c, $p['selection_context']); $this->fail('Mismatched sealed history accepted'); }
            catch (RuntimeException $e) { $this->assertSame('INPUT_CAPTURE_RAW_SEALED_HISTORY_CONTENT', $e->getMessage()); }
        }
        DB::table('eod_bars_history')->where('ticker_id', 2)->delete();
        $this->expectExceptionMessage('INPUT_CAPTURE_RAW_SEALED_HISTORY_MISSING');
        $this->scoped($run, $read);
    }
    public function test_independent_c07_all_five_consumers_have_exact_read_lineage_and_projection_receipts(): void
    {
        $run = $this->runContext();
        $this->seed('2026-03-23', 11); $this->seed('2026-03-24', 12); $this->seed('2026-03-24', 12, 2);
        foreach ([13, 14] as $id) DB::table('eod_publications')->insert(['publication_id' => $id, 'trade_date' => '2026-03-24',
            'run_id' => $run->run_id, 'publication_version' => $id, 'seal_state' => 'UNSEALED', 'created_at' => '2026-03-25 10:30:00']);
        $repo = $this->artifacts();
        $this->scoped($run, function () use ($repo) {
            $this->assertCount(2, $repo->loadBarsWindow('2026-03-24', 2));
            $this->assertCount(2, $repo->loadAtrSeriesForTickerFromBoundary(1, '2026-03-24', '2026-03-23'));
            $this->assertCount(2, $repo->loadBarsForTradeDate('2026-03-24'));
        });
        $repo->ensureBarsHistoryFromCurrentTradeDate('2026-03-24', 13, $run->run_id);
        $repo->replaceBarsHistoryFromPublication('2026-03-24', 13, 14, $run->run_id);
        $capture = new RunInputCaptureRepository(); $rows = $capture->forRun($run->run_id);
        $validator = new \App\Infrastructure\Persistence\MarketData\ProducerRawInputCompleteness();
        $this->assertSame([], $validator->missing($rows, $capture));
        $kinds = []; $targets = [];
        foreach ($rows as $index => $row) {
            $p = $capture->verify($row); $op = $p['selection_context']['operation'];
            if (! in_array($op, ['raw-input-read-population/v1','raw-input-lineage/v1','raw-input-projection-audit/v1'], true)) continue;
            $targets[] = $index;
            if ($op === 'raw-input-read-population/v1') $kinds[] = $p['selection_context']['read_kind'];
        }
        sort($kinds); $this->assertSame(['atr','date','history-copy','history-copy-current','window'], $kinds);
        $this->assertCount(15, $targets);
        foreach ($targets as $index) {
            $bad = $rows; unset($bad[$index]);
            $this->assertNotEmpty($validator->missing(array_values($bad), $capture), 'One missing read/lineage/audit must fail');
        }
        foreach ($targets as $index) {
            $payload = $capture->verify($rows[$index]); $op = $payload['selection_context']['operation'];
            foreach (['missing', 'extraneous', 'mismatched'] as $fault) {
                $bad = $rows; $p = $payload;
                if ($op === 'raw-input-projection-audit/v1') {
                    if ($fault === 'missing') unset($p['rows'][0]['read_ref']);
                    elseif ($fault === 'extraneous') $p['rows'][] = $p['rows'][0];
                    else $p['rows'][0]['projection_hash'] = str_repeat('0', 64);
                } else {
                    $raw = $p['rows'][0]['raw_rows'];
                    if ($fault === 'missing') array_pop($raw);
                    elseif ($fault === 'extraneous') $raw[] = $raw[0];
                    else $raw[count($raw)-1]['quality_state'] = 'DAMAGED';
                    $p['rows'][0]['raw_rows'] = $raw;
                    if ($op === 'raw-input-lineage/v1') {
                        $p['rows'][0]['row_count'] = count($raw);
                        $p['rows'][0]['raw_hash'] = ProducerRawInputLineage::hash($raw);
                    }
                }
                // Recompute the outer envelope: a self-consistent hash cannot prove completeness.
                $bad[$index]['semantic_payload_json'] = RunInputCaptureRepository::canonicalJson($p);
                $bad[$index]['payload_hash'] = hash('sha256', $bad[$index]['semantic_payload_json']);
                $bad[$index]['member_count'] = count($p['rows']);
                $capture->verify($bad[$index]);
                $errors = $validator->missing($bad, $capture);
                $this->assertNotEmpty($errors, $op.':'.$fault);
                if ($op !== 'raw-input-projection-audit/v1') $this->assertStringContainsString('INPUT_CAPTURE_RAW_RETAINED_MISMATCH', implode('|', $errors));
            }
        }
        foreach ([13,14] as $id) $this->assertSame(2, DB::table('eod_bars_history')->where('publication_id', $id)->count());
    }

    public function test_independent_c07_single_projection_member_damage_is_rejected_for_every_shape(): void
    {
        $oracle = new \App\Infrastructure\Persistence\MarketData\ProducerRawInputCompleteness();
        $rows = [['ticker_id'=>1,'trade_date'=>'2026-03-24','high'=>10,'low'=>2,'close'=>8,'publication_id'=>12,'run_id'=>1,'quality_state'=>'VALID','created_at'=>'old'],
                 ['ticker_id'=>2,'trade_date'=>'2026-03-24','high'=>11,'low'=>3,'close'=>9,'publication_id'=>12,'run_id'=>1,'quality_state'=>'VALID','created_at'=>'old']];
        foreach (['window','atr','date','history-copy','history-copy-current'] as $kind) {
            $s = ['read_kind'=>$kind,'target_publication_id'=>13,'target_run_id'=>2];
            $expected = $oracle::projection($rows, $s); $this->assertCount(2, $expected);
            foreach (['missing','extraneous','mismatched'] as $fault) {
                $actual = $expected; $keys = array_keys($actual); $last = end($keys);
                if ($fault === 'missing') unset($actual[$last]);
                elseif ($fault === 'extraneous') $actual[999] = $actual[$last];
                elseif ($kind === 'window') $actual[$last][0]['quality_state'] = 'DAMAGED';
                else $actual[$last]['close'] = 99;
                try { $oracle::assertSamePopulation($expected, $actual, 'PROJECTION'); $this->fail($kind.':'.$fault); }
                catch (RuntimeException $e) { $this->assertSame('INPUT_CAPTURE_RAW_PROJECTION_MISMATCH', $e->getMessage()); }
            }
        }
    }

    public function test_independent_c07_missing_receipt_and_swallowed_projection_damage_roll_back(): void
    {
        $run = $this->runContext(); $this->seed('2026-03-24', 12); $this->seed('2026-03-24', 12, 2);
        $s = ['read_kind'=>'date','trade_date'=>'2026-03-24','start_date'=>'2026-03-24','publication_id'=>null];
        foreach (['missing','damaged'] as $fault) {
            $before = DB::table('md_run_input_captures')->count();
            try {
                $this->scoped($run, function () use ($s, $fault) {
                    ProducerRawInputLineage::consume($s, function () { return DB::table('eod_bars')->orderBy('ticker_id')->get()->map(function ($r) { return (array) $r; })->all(); });
                    if ($fault === 'damaged') try { ProducerInputScope::rawProjection($s, []); } catch (RuntimeException $e) { /* producer cannot swallow a failed read */ }
                });
                $this->fail('Incomplete consumption committed');
            } catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_RAW_PROJECTION_', $e->getMessage()); }
            $this->assertSame($before, DB::table('md_run_input_captures')->count());
        }
    }

    public function test_independent_c07_empty_explicit_read_and_identical_retry_are_complete(): void
    {
        $run = $this->runContext(); $repo = $this->artifacts();
        $read = function () use ($repo) { return $repo->loadBarsForTradeDate('2026-03-24'); };
        $this->assertSame([], $this->scoped($run, $read));
        $capture = new RunInputCaptureRepository(); $before = $capture->forRun($run->run_id);
        $this->assertSame([], $this->scoped($run, $read)); $this->assertSame($before, $capture->forRun($run->run_id));
        $validator = new \App\Infrastructure\Persistence\MarketData\ProducerRawInputCompleteness();
        $this->assertSame([], $validator->missing($before, $capture));
        $this->assertNotEmpty($validator->missing([], $capture));
    }

}
