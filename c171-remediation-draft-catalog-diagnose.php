<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

if (method_exists($kernel, 'bootstrap')) {
    $kernel->bootstrap();
}

$outputDirectory = base_path('storage/app/watchlist/backtest/c171-remediation-draft-catalog');
$outputPath = base_path('storage/app/watchlist/backtest/c171-remediation-draft-catalog.json');
$diagnosticOutputPath = base_path(
    'storage/app/watchlist/backtest/c171-remediation-draft-catalog-diagnose-result.json'
);

$service = $app->make(
    \App\Application\Watchlist\Services\WeeklySwingC171RemediationDraftCatalogService::class
);

$result = $service->execute(
    188,
    1,
    base_path('storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json'),
    'C171_OPERATOR_APPROVED_IMMUTABLE_DRAFT_CATALOG_PERSISTENCE_ONLY',
    true,
    $outputDirectory,
    $outputPath,
    ['overwrite' => true]
);

$connection = \Illuminate\Support\Facades\DB::connection();
$driver = $connection->getDriverName();
$database = $connection->getDatabaseName();

$catalogCode = \App\Application\Watchlist\Services\WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE;

$catalogRows = \Illuminate\Support\Facades\DB::table('watchlist_bt_param_grid')
    ->where('policy_code', 'WS')
    ->where('catalog_code', $catalogCode)
    ->orderBy('row_code', 'asc')
    ->get([
        'param_id',
        'row_code',
        'row_hash',
        'catalog_hash',
        'max_dv20_idr',
        'max_vol_ratio',
        'top_max_score_total',
    ])
    ->map(function ($row) {
        return (array) $row;
    })
    ->all();

$draftRows = \Illuminate\Support\Facades\DB::table('watchlist_param_sets')
    ->where('policy_code', 'WS')
    ->where('provenance_json', 'like', '%'.$catalogCode.'%')
    ->orderBy('param_set_id', 'asc')
    ->get([
        'param_set_id',
        'status',
        'params_hash',
        'eval_model_hash',
        'implementation_version',
        'implementation_hash',
        'created_at',
        'updated_at',
    ])
    ->map(function ($row) {
        return (array) $row;
    })
    ->all();

$indexes = [];
if ($driver === 'mysql') {
    $indexes = array_map(function ($row) {
        return (array) $row;
    }, \Illuminate\Support\Facades\DB::select(
        'SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = ?
           AND TABLE_NAME = ?
         ORDER BY INDEX_NAME, SEQ_IN_INDEX',
        [$database, 'watchlist_bt_param_grid']
    ));
}

$files = [];
if (is_dir($outputDirectory)) {
    foreach (glob($outputDirectory.DIRECTORY_SEPARATOR.'*') ?: [] as $path) {
        if (! is_file($path)) {
            continue;
        }
        $files[] = [
            'name' => basename($path),
            'size' => filesize($path),
            'sha1' => sha1_file($path),
        ];
    }
}

$diagnostic = [
    'database' => [
        'driver' => $driver,
        'database' => $database,
    ],
    'service_result' => $result,
    'persisted_state' => [
        'catalog_code' => $catalogCode,
        'catalog_row_count' => count($catalogRows),
        'catalog_rows' => $catalogRows,
        'draft_row_count' => count($draftRows),
        'draft_rows' => $draftRows,
        'output_files' => $files,
        'param_grid_indexes' => $indexes,
    ],
];

$json = json_encode(
    $diagnostic,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($json === false) {
    fwrite(STDERR, 'JSON encoding failed: '.json_last_error_msg().PHP_EOL);
    exit(2);
}

$directory = dirname($diagnosticOutputPath);
if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
    fwrite(STDERR, 'Unable to create diagnostic output directory.'.PHP_EOL);
    exit(2);
}

file_put_contents($diagnosticOutputPath, $json.PHP_EOL, LOCK_EX);

echo $json.PHP_EOL;
echo 'diagnostic_output='.$diagnosticOutputPath.PHP_EOL;

exit(($result['status'] ?? '') === 'C171_IMMUTABLE_REMEDIATION_DRAFT_CATALOG_PERSISTED' ? 0 : 1);