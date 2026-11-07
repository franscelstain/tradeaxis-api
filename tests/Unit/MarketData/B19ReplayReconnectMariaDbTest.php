<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ReplayIsolation;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

class B19ReplayReconnectMariaDbTest extends B19OwnerFenceMariaDbTest
{
    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    private function projection($connection): array
    {
        $state = [];
        foreach ($connection->select('SHOW TABLES') as $entry) {
            $table = (string) array_values((array) $entry)[0];
            if (!preg_match('/^(eod_|md_)[a-z0-9_]+$/D', $table)) continue;
            $rows = array_map(static fn ($row) => (array) $row, $connection->table($table)->get()->all());
            usort($rows, static fn ($a, $b) => strcmp(json_encode($a), json_encode($b)));
            $state[$table] = $rows;
        }
        ksort($state);
        return $state;
    }

    private function retainReconnect(string $kind, array $evidence): void
    {
        $directory = getenv('B19_E049_STATE_DIR');
        if (!$directory) return;
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        file_put_contents($directory.'/N1_'.$kind.'.json', json_encode($evidence, JSON_PRETTY_PRINT));
    }

    private function probeReconnect(string $mode, EodRun $historical, $source): void
    {
        $before = $this->projection($source);
        $sourceId = (int) $source->selectOne('SELECT CONNECTION_ID() AS id')->id;
        $inside = null; $scopeId = null; $reconnectError = null; $writeError = null;
        $outerError = null; $visibleFromSource = null; $levelAfterReconnect = null;
        try {
            ReplayIsolation::run(function () use ($mode, $historical, $source, $sourceId, &$inside, &$scopeId,
                &$reconnectError, &$writeError, &$visibleFromSource, &$levelAfterReconnect) {
                $scopeId = (int) DB::selectOne('SELECT CONNECTION_ID() AS id')->id;
                $this->assertNotSame($sourceId, $scopeId);
                $run = (new EodRunRepository())->createAsKnownReplayRun(
                    $historical->trade_date_requested, $historical->knowledge_cutoff_at, 'api'
                );
                (new EodPublicationRepository())->getOrCreateCandidatePublication($run);
                $visibleFromSource = $source->table('eod_runs')->where('run_id', $run->run_id)->count();
                $this->assertSame(0, $visibleFromSource, 'Replay is uncommitted on the independent connection.');
                $inside = $this->projection(DB::connection());
                $scope = DB::connection();
                try {
                    if ($mode === 'manager_default') DB::reconnect();
                    elseif ($mode === 'manager_named') DB::reconnect(DB::getDefaultConnection());
                    elseif ($mode === 'connection') $scope->reconnect();
                    elseif ($mode === 'disconnect_retry') DB::disconnect();
                    elseif ($mode === 'purge_retry') DB::purge();
                    else throw new RuntimeException('UNKNOWN_RECONNECT_CONTROL');
                } catch (RuntimeException | InvalidArgumentException $e) {
                    $reconnectError = $e->getMessage();
                }
                // A caller catching infrastructure failure must not silently write on a fresh autocommit PDO.
                $levelAfterReconnect = $scope->transactionLevel();
                try {
                    DB::table('eod_runs')->where('run_id', $historical->run_id)
                        ->update(['notes' => 'N1_RECONNECT_DURABLE_'.$mode]);
                } catch (RuntimeException | InvalidArgumentException $e) {
                    $writeError = $e->getMessage();
                }
            });
        } catch (RuntimeException | InvalidArgumentException $e) {
            $outerError = $e->getMessage();
        }
        $after = $this->projection($source);
        $this->retainReconnect($mode, compact('before', 'inside', 'after', 'sourceId', 'scopeId',
            'reconnectError', 'writeError', 'outerError', 'visibleFromSource', 'levelAfterReconnect'));
        $this->assertSame($before, $after, 'Reconnect persisted replay projection: '.$mode);
        $this->assertNotNull($reconnectError ?: $writeError ?: $outerError, 'Reconnect/retry gained implicit authority: '.$mode);
        $this->assertSame($source, DB::connection());
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_explicit_manager_reconnect_cannot_make_replay_projection_write_durable(): void
    {
        $world = R0025SyntheticV2World::build();
        $run = EodRun::findOrFail($world['run_id']);
        $this->probeReconnect('manager_default', $run, DB::connection());
        $publication = (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($run->trade_date_requested);
        $this->assertSame((int) $world['publication_id'], (int) $publication->publication_id);
    }

    public function test_named_connection_disconnect_and_purge_cannot_recreate_replay_transport(): void
    {
        $world = R0025SyntheticV2World::build();
        $run = EodRun::findOrFail($world['run_id']);
        $source = DB::connection();
        foreach (['manager_named', 'connection', 'disconnect_retry', 'purge_retry'] as $mode) {
            $this->probeReconnect($mode, $run, $source);
        }
        $publication = (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($run->trade_date_requested);
        $this->assertSame((int) $world['publication_id'], (int) $publication->publication_id);
    }
}
