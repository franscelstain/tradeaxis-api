<?php

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;

/**
 * Concurrency proof for the sector membership import lock.
 *
 * This test deliberately does not run on the SQLite mirror. SQLite has no GET_LOCK, the suite runs
 * single-process, and SectorClassificationRepository::acquireImportLock() returns true there by
 * design — so a SQLite version of this test would assert that a no-op returned true and would keep
 * passing if the lock were deleted outright. That is the shape of defect this audit spine keeps
 * finding: a rule written correctly, then never actually exercised. Mutual exclusion is proven
 * against MariaDB or it is not proven.
 *
 * It also cannot be done on one connection. GET_LOCK is re-entrant for the session already holding
 * the lock, so a single-connection attempt would succeed and demonstrate nothing.
 */
class SectorMembershipImportLockTest extends TestCase
{
    private $connections = [];

    protected function tearDown(): void
    {
        foreach ($this->connections as $connection) {
            try {
                $this->release($connection);
            } catch (\Throwable $e) {
                // The connection is being discarded; the server frees any lock it still holds.
            }
        }
        $this->connections = [];

        parent::tearDown();
    }

    public function test_a_second_importer_is_refused_while_the_first_holds_the_lock(): void
    {
        $first = $this->importer();
        $second = $this->importer();

        $this->assertSame(1, $this->acquire($first, 10), 'the first importer must take the lock');

        $startedAt = microtime(true);
        $refused = $this->acquire($second, 1);
        $waited = microtime(true) - $startedAt;

        $this->assertSame(0, $refused, 'a second concurrent import must be refused, not admitted');
        $this->assertGreaterThanOrEqual(
            0.9,
            $waited,
            'the refusal must come from waiting out the timeout, not from an error answering instantly'
        );

        $this->assertSame(1, $this->release($first), 'the holder must be able to release');
        $this->assertSame(1, $this->acquire($second, 10), 'the lock must be reusable once released');
    }

    /**
     * An importer that dies mid-run must not leave every later import blocked. Session-scoped locks
     * give this for free, and the test pins it so a future move to a table-based lock cannot quietly
     * lose the property.
     */
    public function test_a_dropped_connection_does_not_strand_the_lock(): void
    {
        $crashed = $this->importer();
        $this->assertSame(1, $this->acquire($crashed, 10));

        $key = array_search($crashed, $this->connections, true);
        unset($this->connections[$key]);
        $crashed = null;

        $survivor = $this->importer();
        $this->assertSame(
            1,
            $this->acquire($survivor, 5),
            'the lock must be released by the server when its holder disconnects'
        );
    }

    private function importer(): PDO
    {
        try {
            $pdo = new PDO(
                'mysql:host=127.0.0.1;dbname=tradeaxis_testing',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
        } catch (\Throwable $e) {
            $this->markTestSkipped('no reachable MariaDB; advisory-lock behaviour cannot be proven on SQLite');
        }

        $this->connections[] = $pdo;

        return $pdo;
    }

    private function acquire(PDO $pdo, $timeoutSeconds)
    {
        $statement = $pdo->prepare('SELECT GET_LOCK(?, ?) AS acquired');
        $statement->execute([SectorClassificationRepository::IMPORT_LOCK_NAME, $timeoutSeconds]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row['acquired'] === null ? null : (int) $row['acquired'];
    }

    private function release(PDO $pdo)
    {
        $statement = $pdo->prepare('SELECT RELEASE_LOCK(?) AS released');
        $statement->execute([SectorClassificationRepository::IMPORT_LOCK_NAME]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row['released'] === null ? null : (int) $row['released'];
    }
}
