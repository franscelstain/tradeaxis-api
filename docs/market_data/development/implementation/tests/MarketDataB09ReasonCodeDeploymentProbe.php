<?php
/**
 * MD-B09-A002 deployed reason-dictionary proof.
 *
 * Read-only standalone probe. Reads DB_* from repository .env, never prints credentials, and
 * verifies that the authorised BAR_ZERO_VOLUME_PRICE_MOVEMENT definition is deployed exactly.
 * Exit 0 PASS; 1 reachable DB but semantic mismatch/missing row; 2 environment/database unavailable.
 */
$root = realpath(dirname(__DIR__, 5));
if ($root === false) {
    fwrite(STDERR, "B09_REASON_PROBE_ROOT_UNRESOLVED\n");
    exit(2);
}

function b09ReasonEnv($root)
{
    $config = [];
    $path = $root.'/.env';
    if (! is_file($path)) return $config;
    foreach (file($path) as $line) {
        if (preg_match('/^(DB_[A-Z_]+)=(.*)$/', trim($line), $m)) {
            $config[$m[1]] = trim($m[2], "\"'");
        }
    }
    return $config;
}

$config = b09ReasonEnv($root);
$host = $config['DB_HOST'] ?? '127.0.0.1';
$port = $config['DB_PORT'] ?? '3306';
$name = $config['DB_DATABASE'] ?? 'tradeaxis';
$user = $config['DB_USERNAME'] ?? 'root';
$pass = $config['DB_PASSWORD'] ?? '';

$result = [
    'probe' => 'MarketDataB09ReasonCodeDeploymentProbe',
    'stage' => 'MD-B09',
    'attempt' => 'MD-B09-A002',
    'status' => 'FAIL',
    'database_reachable' => false,
    'database_name' => null,
    'server_version' => null,
    'expected' => [
        'code' => 'BAR_ZERO_VOLUME_PRICE_MOVEMENT',
        'category' => 'BAR',
        'severity' => 'HARD',
        'is_active' => 1,
    ],
    'actual' => null,
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

try {
    $stmt = $pdo->prepare('SELECT code,category,severity,is_active,description FROM eod_reason_codes WHERE code=?');
    $stmt->execute(['BAR_ZERO_VOLUME_PRICE_MOVEMENT']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        $result['errors'][] = 'REASON_CODE_NOT_DEPLOYED';
    } else {
        $result['actual'] = [
            'code' => (string) $row['code'],
            'category' => (string) $row['category'],
            'severity' => (string) $row['severity'],
            'is_active' => (int) $row['is_active'],
            'description' => (string) $row['description'],
        ];
        foreach (['code', 'category', 'severity', 'is_active'] as $field) {
            if ($result['actual'][$field] !== $result['expected'][$field]) {
                $result['errors'][] = 'REASON_CODE_METADATA_MISMATCH: '.$field;
            }
        }
        $description = strtolower($result['actual']['description']);
        foreach (['volume', 'invalid', 'canonical'] as $token) {
            if (strpos($description, $token) === false) {
                $result['errors'][] = 'REASON_CODE_DESCRIPTION_MISSING_SEMANTIC_TOKEN: '.$token;
            }
        }
    }
} catch (Throwable $e) {
    $result['errors'][] = 'REASON_CODE_QUERY_FAILED';
}

$result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
