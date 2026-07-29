<?php

declare(strict_types=1);

use App\Application\Watchlist\Services\WeeklySwingC171VersionedOfficialIsEvidenceService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

/** @var WeeklySwingC171VersionedOfficialIsEvidenceService $service */
$service = $app->make(
    WeeklySwingC171VersionedOfficialIsEvidenceService::class
);

$outputPath = __DIR__
    .'/storage/app/watchlist/backtest/'
    .'c171-versioned-official-is-evidence.json';

$result = $service->execute(
    1,
    '2023-01-02',
    '2025-05-21',
    'C171_OPERATOR_APPROVED_OFFICIAL_IS_EVIDENCE_ONLY',
    true,
    $outputPath,
    [
        'overwrite' => true,
        'executed_at' => '2025-05-21T23:59:59+07:00',
    ]
);

$diagnosticPath = __DIR__
    .'/storage/app/watchlist/backtest/'
    .'c171-debug-full.json';

$json = json_encode(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if ($json === false) {
    fwrite(STDERR, "JSON encoding failed: ".json_last_error_msg().PHP_EOL);
    exit(1);
}

if (file_put_contents($diagnosticPath, $json.PHP_EOL, LOCK_EX) === false) {
    fwrite(STDERR, "Failed writing diagnostic file.".PHP_EOL);
    exit(1);
}

$evaluation = is_array($result['evaluation'] ?? null)
    ? $result['evaluation']
    : [];

echo 'status='.($result['status'] ?? '').PHP_EOL;
echo 'reason_code='.($result['reason_code'] ?? '').PHP_EOL;
echo 'calibration_status='
    .($result['calibration_status'] ?? '').PHP_EOL;
echo 'calibration_reason_code='
    .($result['calibration_reason_code'] ?? '').PHP_EOL;
echo 'evaluation_status='
    .($result['evaluation_status'] ?? ($evaluation['status'] ?? '')).PHP_EOL;
echo 'evaluation_reason_code='
    .($result['evaluation_reason_code'] ?? ($evaluation['reason_code'] ?? '')).PHP_EOL;
echo 'eval_id='.($evaluation['eval_id'] ?? '').PHP_EOL;
echo 'persistence_status='
    .($evaluation['persistence_status'] ?? '').PHP_EOL;
echo 'official_evidence_persistence_status='
    .($evaluation['official_evidence_persistence_status'] ?? '').PHP_EOL;
echo 'diagnostic_path='.$diagnosticPath.PHP_EOL;

exit(0);