<?php
/**
 * MD-B10-A001 deployed MariaDB proof for sealed-history immutability and reconciliation schema.
 *
 * The mutation probe is rollback-safe and only targets an already sealed publication. Each direct
 * INSERT/UPDATE/DELETE must fail before mutation with the canonical database signal. No credentials
 * or row values are printed.
 */
$root = realpath(dirname(__DIR__, 5));
if ($root === false) {
    fwrite(STDERR, "B10_SCHEMA_PROBE_ROOT_UNRESOLVED\n");
    exit(2);
}

function b10Env($root)
{
    $out = [];
    $path = $root.'/.env';
    if (! is_file($path)) {
        return $out;
    }
    foreach (file($path) as $line) {
        if (preg_match('/^(DB_[A-Z_]+)=(.*)$/', trim($line), $m)) {
            $out[$m[1]] = trim($m[2], "\"'");
        }
    }
    return $out;
}

$historyTables = ['eod_bars_history', 'eod_indicators_history', 'eod_eligibility_history'];
$expectedTriggers = [];
foreach ($historyTables as $table) {
    foreach (['bi' => 'INSERT', 'bu' => 'UPDATE', 'bd' => 'DELETE'] as $suffix => $event) {
        $expectedTriggers['trg_'.$table.'_'.$suffix.'_sealed_immutable'] = [$table, $event];
    }
}
$requiredColumns = [
    'reconciliation_uid', 'trade_date', 'publication_id', 'run_id', 'publication_version',
    'pointer_state', 'reconciliation_state', 'bars_projection_count', 'bars_history_count',
    'bars_missing_history_count', 'bars_missing_projection_count', 'bars_value_mismatch_count',
    'indicators_projection_count', 'indicators_history_count', 'indicators_missing_history_count',
    'indicators_missing_projection_count', 'indicators_value_mismatch_count',
    'eligibility_projection_count', 'eligibility_history_count', 'eligibility_missing_history_count',
    'eligibility_missing_projection_count', 'eligibility_value_mismatch_count',
    'orphan_projection_row_count', 'mismatch_count', 'mismatch_sample_json', 'reconciliation_hash', 'checked_at',
];

$result = [
    'probe' => 'MarketDataB10DeployedSchemaProbe',
    'stage' => 'MD-B10',
    'attempt' => 'MD-B10-A001',
    'status' => 'FAIL',
    'database_reachable' => false,
    'migration' => 'MISSING',
    'reconciliation_table' => 'FAIL',
    'trigger_count' => 0,
    'trigger_checks' => [],
    'unexpected_history_triggers' => [],
    'mutation_attempts' => 0,
    'canonical_blocks' => 0,
    'errors' => [],
];

$config = b10Env($root);
try {
    $pdo = new PDO(
        'mysql:host='.($config['DB_HOST'] ?? '127.0.0.1').';port='.($config['DB_PORT'] ?? '3306').';dbname='.($config['DB_DATABASE'] ?? 'tradeaxis').';charset=utf8mb4',
        $config['DB_USERNAME'] ?? 'root',
        $config['DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
    );
} catch (Throwable $e) {
    $result['errors'][] = 'DB_CONNECTION_FAILED';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(2);
}
$result['database_reachable'] = true;
$schema = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
    $stmt->execute(['2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation']);
    if ((int) $stmt->fetchColumn() === 1) {
        $result['migration'] = 'APPLIED';
    } else {
        $result['errors'][] = 'MIGRATION_NOT_APPLIED';
    }

    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?');
    $stmt->execute([$schema, 'md_publication_projection_reconciliations']);
    $columns = array_flip(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
    $missingColumns = array_values(array_filter($requiredColumns, function ($column) use ($columns) {
        return ! isset($columns[$column]);
    }));
    if ($missingColumns === []) {
        $result['reconciliation_table'] = 'PASS';
    } else {
        $result['errors'][] = 'RECONCILIATION_COLUMNS_MISSING: '.implode(',', $missingColumns);
    }

    $stmt = $pdo->prepare(
        'SELECT TRIGGER_NAME,EVENT_OBJECT_TABLE,EVENT_MANIPULATION,ACTION_TIMING,ACTION_STATEMENT '
        .'FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=?'
    );
    $stmt->execute([$schema]);
    $actualTriggers = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $actualTriggers[$row['TRIGGER_NAME']] = $row;
    }
    foreach ($expectedTriggers as $name => $expected) {
        $row = $actualTriggers[$name] ?? null;
        $ok = $row
            && $row['EVENT_OBJECT_TABLE'] === $expected[0]
            && strtoupper($row['EVENT_MANIPULATION']) === $expected[1]
            && strtoupper($row['ACTION_TIMING']) === 'BEFORE'
            && strpos((string) $row['ACTION_STATEMENT'], 'SEALED_PUBLICATION_IMMUTABLE') !== false;
        $result['trigger_checks'][$name] = $ok ? 'PASS' : 'FAIL';
        if (! $ok) {
            $result['errors'][] = 'TRIGGER_MISSING_OR_DRIFTED: '.$name;
        }
    }
    $result['trigger_count'] = count(array_filter($result['trigger_checks'], function ($state) { return $state === 'PASS'; }));

    foreach ($actualTriggers as $name => $row) {
        if (in_array($row['EVENT_OBJECT_TABLE'], $historyTables, true) && ! array_key_exists($name, $expectedTriggers)) {
            $result['unexpected_history_triggers'][] = [
                'name' => $name,
                'table' => $row['EVENT_OBJECT_TABLE'],
                'event' => strtoupper((string) $row['EVENT_MANIPULATION']),
            ];
        }
    }
    if ($result['unexpected_history_triggers'] !== []) {
        $result['errors'][] = 'UNEXPECTED_HISTORY_TRIGGERS_PRESENT: '.count($result['unexpected_history_triggers']);
    }

    if ($result['trigger_count'] === 9 && $result['unexpected_history_triggers'] === []) {
        $pdo->beginTransaction();
        try {
            foreach ($historyTables as $table) {
                $fixtureStmt = $pdo->query(
                    'SELECT h.publication_id,h.trade_date,h.ticker_id FROM `'.$table.'` h '
                    ."JOIN eod_publications p ON p.publication_id=h.publication_id WHERE p.seal_state='SEALED' LIMIT 1"
                );
                $fixture = $fixtureStmt->fetch(PDO::FETCH_ASSOC);
                if (! $fixture) {
                    $result['errors'][] = 'SEALED_HISTORY_FIXTURE_MISSING: '.$table;
                    continue;
                }

                $publicationId = (int) $fixture['publication_id'];
                $tradeDate = (string) $fixture['trade_date'];
                $tickerId = (int) $fixture['ticker_id'];
                $attempts = [
                    'INSERT' => [
                        'INSERT INTO `'.$table.'` SELECT * FROM `'.$table.'` WHERE publication_id=? AND trade_date=? AND ticker_id=?',
                        [$publicationId, $tradeDate, $tickerId],
                    ],
                    'UPDATE' => [
                        'UPDATE `'.$table.'` SET publication_id=publication_id WHERE publication_id=? AND trade_date=? AND ticker_id=?',
                        [$publicationId, $tradeDate, $tickerId],
                    ],
                    'DELETE' => [
                        'DELETE FROM `'.$table.'` WHERE publication_id=? AND trade_date=? AND ticker_id=?',
                        [$publicationId, $tradeDate, $tickerId],
                    ],
                ];
                foreach ($attempts as $event => $attempt) {
                    $result['mutation_attempts']++;
                    try {
                        $mutation = $pdo->prepare($attempt[0]);
                        $mutation->execute($attempt[1]);
                        $result['errors'][] = 'DIRECT_MUTATION_NOT_BLOCKED: '.$table.' '.$event;
                    } catch (Throwable $e) {
                        if (strpos($e->getMessage(), 'SEALED_PUBLICATION_IMMUTABLE') !== false) {
                            $result['canonical_blocks']++;
                        } else {
                            $sqlState = method_exists($e, 'getCode') ? (string) $e->getCode() : 'UNKNOWN';
                            $message = preg_replace('/\s+/', ' ', (string) $e->getMessage());
                            $result['errors'][] = 'WRONG_DIRECT_MUTATION_FAILURE: '.$table.' '.$event
                                .' sqlstate='.$sqlState.' detail='.substr($message, 0, 180);
                        }
                    }
                }
            }
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }
} catch (Throwable $e) {
    $result['errors'][] = 'PROBE_QUERY_FAILED: '.get_class($e);
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

if ($result['mutation_attempts'] !== 9 || $result['canonical_blocks'] !== 9) {
    $result['errors'][] = 'DIRECT_MUTATION_PROOF_INCOMPLETE';
}
$result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
