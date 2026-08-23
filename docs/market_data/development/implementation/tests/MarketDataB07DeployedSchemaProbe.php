<?php
/**
 * MD-B07-A001 deployed MariaDB schema proof.
 *
 * This is intentionally standalone: it reads only DB_* values from the repository .env, never
 * prints credentials, queries the configured MariaDB information_schema and migration ledger, and
 * performs no writes. It exists because a migration-file/applied-ledger check alone cannot prove
 * that the deployed tables, columns and indexes still match the B07 migrations.
 *
 * Usage: php MarketDataB07DeployedSchemaProbe.php
 * Exit: 0 PASS; 1 reachable database but schema/migration mismatch; 2 environment/database unavailable.
 */

$root = realpath(dirname(__DIR__, 5));
if ($root === false) {
    fwrite(STDERR, "B07_SCHEMA_PROBE_ROOT_UNRESOLVED\n");
    exit(2);
}

function b07Env($root)
{
    $config = [];
    $path = $root.'/.env';
    if (! is_file($path)) {
        return $config;
    }
    foreach (file($path) as $line) {
        if (preg_match('/^(DB_[A-Z_]+)=(.*)$/', trim($line), $m)) {
            $config[$m[1]] = trim($m[2], "\"'");
        }
    }

    return $config;
}

function b07ColumnSpec($type, $nullable, $length = null, $unsigned = false, $autoIncrement = false)
{
    return [
        'type' => $type,
        'nullable' => $nullable ? 'YES' : 'NO',
        'length' => $length,
        'unsigned' => $unsigned,
        'auto_increment' => $autoIncrement,
    ];
}

$expectedColumns = [
    'md_source_observations' => [
        'acquisition_batch_id' => b07ColumnSpec('varchar', true, 128),
        'acquisition_checkpoint_id' => b07ColumnSpec('varchar', true, 128),
    ],
    'md_source_observation_rows' => [
        'source_observation_row_id' => b07ColumnSpec('bigint', false, null, true, true),
        'source_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'capture_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'source_row_ref' => b07ColumnSpec('varchar', false, 255),
        'listing_id' => b07ColumnSpec('bigint', true, null, true),
        'provider' => b07ColumnSpec('varchar', true, 64),
        'provider_symbol' => b07ColumnSpec('varchar', true, 128),
        'provider_mapping_id' => b07ColumnSpec('bigint', true, null, true),
        'mapping_revision' => b07ColumnSpec('varchar', true, 64),
        'ticker_code' => b07ColumnSpec('varchar', false, 32),
        'trade_date' => b07ColumnSpec('date', false),
        'source_timestamp' => b07ColumnSpec('datetime', true),
        'open_value' => b07ColumnSpec('varchar', false, 64),
        'high_value' => b07ColumnSpec('varchar', false, 64),
        'low_value' => b07ColumnSpec('varchar', false, 64),
        'close_value' => b07ColumnSpec('varchar', false, 64),
        'volume_value' => b07ColumnSpec('varchar', false, 64),
        'adj_close_value' => b07ColumnSpec('varchar', true, 64),
        'row_fingerprint' => b07ColumnSpec('char', false, 64),
        'created_at' => b07ColumnSpec('datetime', false),
    ],
    'md_source_observation_identity_bindings' => [
        'source_observation_identity_binding_id' => b07ColumnSpec('bigint', false, null, true, true),
        'source_observation_row_id' => b07ColumnSpec('bigint', false, null, true),
        'source_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'listing_id' => b07ColumnSpec('bigint', false, null, true),
        'provider_mapping_id' => b07ColumnSpec('bigint', true, null, true),
        'mapping_revision' => b07ColumnSpec('varchar', false, 64),
        'effective_trade_date' => b07ColumnSpec('date', false),
        'recorded_at' => b07ColumnSpec('datetime', false),
    ],
    'md_source_observation_revision_comparisons' => [
        'source_observation_comparison_id' => b07ColumnSpec('bigint', false, null, true, true),
        'comparison_uid' => b07ColumnSpec('varchar', false, 64),
        'prior_source_observation_row_id' => b07ColumnSpec('bigint', false, null, true),
        'current_source_observation_row_id' => b07ColumnSpec('bigint', false, null, true),
        'prior_source_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'current_source_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'listing_id' => b07ColumnSpec('bigint', true, null, true),
        'provider' => b07ColumnSpec('varchar', true, 64),
        'provider_symbol' => b07ColumnSpec('varchar', true, 128),
        'ticker_code' => b07ColumnSpec('varchar', false, 32),
        'trade_date' => b07ColumnSpec('date', false),
        'comparison_state' => b07ColumnSpec('varchar', false, 32),
        'divergence_finding_uid' => b07ColumnSpec('varchar', true, 64),
        'finding_state' => b07ColumnSpec('varchar', false, 32),
        'differing_fields_json' => b07ColumnSpec('text', true),
        'prior_values_json' => b07ColumnSpec('text', false),
        'current_values_json' => b07ColumnSpec('text', false),
        'value_deltas_json' => b07ColumnSpec('text', false),
        'created_at' => b07ColumnSpec('datetime', false),
    ],
    'md_source_observation_rejected_rows' => [
        'source_observation_rejected_row_id' => b07ColumnSpec('bigint', false, null, true, true),
        'source_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'capture_observation_id' => b07ColumnSpec('bigint', false, null, true),
        'source_row_ref' => b07ColumnSpec('varchar', false, 255),
        'instrument_code' => b07ColumnSpec('varchar', false, 32),
        'provider_symbol' => b07ColumnSpec('varchar', true, 128),
        'trade_date' => b07ColumnSpec('date', false),
        'open_value' => b07ColumnSpec('varchar', true, 64),
        'high_value' => b07ColumnSpec('varchar', true, 64),
        'low_value' => b07ColumnSpec('varchar', true, 64),
        'close_value' => b07ColumnSpec('varchar', true, 64),
        'volume_value' => b07ColumnSpec('varchar', true, 64),
        'adj_close_value' => b07ColumnSpec('varchar', true, 64),
        'reason_code' => b07ColumnSpec('varchar', false, 64),
        'reason_note' => b07ColumnSpec('varchar', false, 255),
        'created_at' => b07ColumnSpec('datetime', false),
    ],
];

$expectedIndexes = [
    'md_source_observations' => [
        'idx_md_obs_acquisition_identity' => [false, ['acquisition_batch_id', 'acquisition_checkpoint_id']],
    ],
    'md_source_observation_rows' => [
        'uq_md_obs_row_observation_ref' => [true, ['source_observation_id', 'source_row_ref']],
        'idx_md_obs_row_listing_date' => [false, ['listing_id', 'trade_date', 'source_observation_row_id']],
        'idx_md_obs_row_provider_date' => [false, ['provider', 'provider_symbol', 'trade_date', 'source_observation_row_id']],
    ],
    'md_source_observation_identity_bindings' => [
        'uq_md_obs_identity_row' => [true, ['source_observation_row_id']],
        'idx_md_obs_identity_listing_date' => [false, ['listing_id', 'effective_trade_date']],
    ],
    'md_source_observation_revision_comparisons' => [
        'uq_md_obs_comparison_pair' => [true, ['prior_source_observation_row_id', 'current_source_observation_row_id']],
        'uq_md_obs_comparison_uid' => [true, ['comparison_uid']],
        'uq_md_obs_divergence_finding' => [true, ['divergence_finding_uid']],
        'idx_md_obs_comparison_listing_state' => [false, ['listing_id', 'trade_date', 'finding_state']],
        'idx_md_obs_comparison_state' => [false, ['comparison_state', 'finding_state']],
    ],
    'md_source_observation_rejected_rows' => [
        'uq_md_obs_rejected_row_ref' => [true, ['source_observation_id', 'source_row_ref']],
        'idx_md_obs_rejected_identity' => [false, ['instrument_code', 'trade_date', 'reason_code']],
    ],
];

$requiredMigrations = [
    '2026_08_22_000002_harden_source_observation_acquisition',
    '2026_08_22_000003_add_source_observation_rejected_rows',
];

$config = b07Env($root);
$host = $config['DB_HOST'] ?? '127.0.0.1';
$port = $config['DB_PORT'] ?? '3306';
$name = $config['DB_DATABASE'] ?? 'tradeaxis';
$user = $config['DB_USERNAME'] ?? 'root';
$pass = $config['DB_PASSWORD'] ?? '';

$result = [
    'probe' => 'MarketDataB07DeployedSchemaProbe',
    'stage' => 'MD-B07',
    'attempt' => 'MD-B07-A001',
    'status' => 'FAIL',
    'database_reachable' => false,
    'database_name' => null,
    'server_version' => null,
    'required_migrations' => [],
    'column_checks' => [],
    'index_checks' => [],
    'errors' => [],
];

try {
    $pdo = new PDO(
        'mysql:host='.$host.';port='.$port.';dbname='.$name.';charset=utf8mb4',
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
    );
} catch (Throwable $e) {
    $result['errors'][] = 'DB_CONNECTION_FAILED';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(2);
}

$result['database_reachable'] = true;
$result['database_name'] = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
$result['server_version'] = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
$schema = $result['database_name'];

try {
    $applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
    $applied = array_flip(array_map('strval', $applied));
    foreach ($requiredMigrations as $migration) {
        $ok = isset($applied[$migration]);
        $result['required_migrations'][$migration] = $ok ? 'APPLIED' : 'MISSING';
        if (! $ok) {
            $result['errors'][] = 'MIGRATION_NOT_APPLIED: '.$migration;
        }
    }

    $tableNames = array_keys($expectedColumns);
    $quoted = implode(',', array_fill(0, count($tableNames), '?'));
    $stmt = $pdo->prepare(
        'SELECT TABLE_NAME,COLUMN_NAME,DATA_TYPE,COLUMN_TYPE,IS_NULLABLE,CHARACTER_MAXIMUM_LENGTH,EXTRA '
        .'FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ('.$quoted.')'
    );
    $stmt->execute(array_merge([$schema], $tableNames));
    $actualColumns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $actualColumns[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row;
    }

    foreach ($expectedColumns as $table => $columns) {
        $tableErrors = [];
        foreach ($columns as $column => $expected) {
            if (! isset($actualColumns[$table][$column])) {
                $tableErrors[] = 'missing '.$column;
                continue;
            }
            $actual = $actualColumns[$table][$column];
            if (strtolower((string) $actual['DATA_TYPE']) !== $expected['type']) {
                $tableErrors[] = $column.' type='.$actual['DATA_TYPE'].' expected='.$expected['type'];
            }
            if ((string) $actual['IS_NULLABLE'] !== $expected['nullable']) {
                $tableErrors[] = $column.' nullable='.$actual['IS_NULLABLE'].' expected='.$expected['nullable'];
            }
            if ($expected['length'] !== null && (int) $actual['CHARACTER_MAXIMUM_LENGTH'] !== (int) $expected['length']) {
                $tableErrors[] = $column.' length='.$actual['CHARACTER_MAXIMUM_LENGTH'].' expected='.$expected['length'];
            }
            $isUnsigned = stripos((string) $actual['COLUMN_TYPE'], 'unsigned') !== false;
            if ($isUnsigned !== $expected['unsigned']) {
                $tableErrors[] = $column.' unsigned='.($isUnsigned ? 'YES' : 'NO').' expected='.($expected['unsigned'] ? 'YES' : 'NO');
            }
            $isAuto = stripos((string) $actual['EXTRA'], 'auto_increment') !== false;
            if ($isAuto !== $expected['auto_increment']) {
                $tableErrors[] = $column.' auto_increment='.($isAuto ? 'YES' : 'NO').' expected='.($expected['auto_increment'] ? 'YES' : 'NO');
            }
        }
        $result['column_checks'][$table] = [
            'expected_columns' => count($columns),
            'status' => $tableErrors === [] ? 'PASS' : 'FAIL',
            'errors' => $tableErrors,
        ];
        foreach ($tableErrors as $error) {
            $result['errors'][] = 'COLUMN_DRIFT '.$table.': '.$error;
        }
    }

    $stmt = $pdo->prepare(
        'SELECT TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME '
        .'FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ('.$quoted.') '
        .'ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX'
    );
    $stmt->execute(array_merge([$schema], $tableNames));
    $actualIndexes = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $table = $row['TABLE_NAME'];
        $index = $row['INDEX_NAME'];
        if (! isset($actualIndexes[$table][$index])) {
            $actualIndexes[$table][$index] = ['unique' => ((int) $row['NON_UNIQUE']) === 0, 'columns' => []];
        }
        $actualIndexes[$table][$index]['columns'][] = $row['COLUMN_NAME'];
    }

    foreach ($expectedIndexes as $table => $indexes) {
        $tableErrors = [];
        foreach ($indexes as $index => $expected) {
            if (! isset($actualIndexes[$table][$index])) {
                $tableErrors[] = 'missing '.$index;
                continue;
            }
            $actual = $actualIndexes[$table][$index];
            if ($actual['unique'] !== $expected[0]) {
                $tableErrors[] = $index.' unique='.($actual['unique'] ? 'YES' : 'NO').' expected='.($expected[0] ? 'YES' : 'NO');
            }
            if ($actual['columns'] !== $expected[1]) {
                $tableErrors[] = $index.' columns='.implode(',', $actual['columns']).' expected='.implode(',', $expected[1]);
            }
        }
        $result['index_checks'][$table] = [
            'expected_indexes' => count($indexes),
            'status' => $tableErrors === [] ? 'PASS' : 'FAIL',
            'errors' => $tableErrors,
        ];
        foreach ($tableErrors as $error) {
            $result['errors'][] = 'INDEX_DRIFT '.$table.': '.$error;
        }
    }
} catch (Throwable $e) {
    $result['errors'][] = 'SCHEMA_PROBE_QUERY_FAILED';
}

$result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
