<?php
namespace App\Infrastructure\Persistence\MarketData;
use Illuminate\Support\Facades\DB;

/** Dedicated, rollback-only execution connection; never lends the caller its native PDO. */
final class ReplayIsolation
{
    private static $active;
    public static function assertResultPersistenceAllowed(): void
    {
        if(self::$active && self::$active->contains(DB::connection()))
            throw new \RuntimeException('REPLAY_RESULT_AUTHORITY_ISOLATION_FORBIDDEN: durable results require their own authority after projection rollback.');
    }
    public static function run(callable $work)
    {
        $manager=DB::getFacadeRoot();$previous=$manager->getDefaultConnection();$source=$manager->connection();
        if ($source->transactionLevel()!==0 || $source->getPdo()->inTransaction()) {
            throw new \RuntimeException('REPLAY_ISOLATION_ROOT_REQUIRED: replay cannot share a caller business transaction.');
        }
        $config=$source->getConfig();
        if (!in_array($config['driver'],['mysql','sqlite'],true)
            || ($config['driver']==='sqlite' && ($config['database']??'')===':memory:')) {
            throw new \RuntimeException('REPLAY_ISOLATION_SHARED_DATABASE_REQUIRED: replay requires independently connectable transactional storage.');
        }
        $name='market_data_replay_'.bin2hex(random_bytes(12));
        $config['name']=$name;
        $config['options'][\PDO::ATTR_PERSISTENT]=false; // Eloquent must bind to this dedicated connection as well.
        config()->set('database.connections.'.$name,$config);
        $connection=null;$pdo=null;$proxy=null;
        try {
            $connection=$manager->connection($name);$pdo=$connection->getPdo();
            $proxy=new ReplayRollbackPdo($pdo);$connection->setPdo($proxy)->setReadPdo($proxy);
            if (!self::$active)self::$active=new \SplObjectStorage();
            self::$active[$connection]=$proxy;
            // Framework retry must not replace the leased PDO with an autocommit transport.
            // Connection::reconnect uses this callback; DatabaseManager::reconnect makes a
            // fresh connection from config directly, so revoke that construction recipe too.
            $connection->setReconnector(static function () {
                throw new \RuntimeException('REPLAY_RECONNECT_FORBIDDEN: rollback-only transport cannot be recreated.');
            });
            $connections=config('database.connections');unset($connections[$name]);config()->set('database.connections',$connections);
            $manager->setDefaultConnection($name);
            $connection->beginTransaction();
            return $work();
        } finally {
            try {
                if($proxy)$proxy->revoke();
                if($pdo && $pdo->inTransaction())$pdo->rollBack();
            }
            finally {
                if($connection && self::$active && self::$active->contains($connection))self::$active->detach($connection);
                if($pdo)$pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS,[\PDOStatement::class]);
                $manager->setDefaultConnection($previous);$manager->purge($name);
                $connections=config('database.connections');unset($connections[$name]);config()->set('database.connections',$connections);
            }
        }
    }
    public static function assertActive(): void
    {
        $connection=DB::connection();
        if (!self::$active || !self::$active->contains($connection)
            || $connection->getRawPdo()!==self::$active[$connection]
            || !$connection->getPdo()->inTransaction()) {
            throw new \RuntimeException('REPLAY_ISOLATION_TRANSACTION_REQUIRED: managed rollback-only execution scope required; caller transaction is not replay authority.');
        }
    }
}
