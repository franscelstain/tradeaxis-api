<?php
/**
 * MD-B10-A001 deployed MariaDB proof for controlled current-projection repair.
 *
 * Safety model:
 * - refuses APP_ENV=production;
 * - requires an already-clean current projection before the probe starts;
 * - injects one eligibility-lineage mismatch only inside an outer DB transaction;
 * - calls the real PublicationProjectionRepairService against that mismatch;
 * - proves the repair restores the projection from immutable history;
 * - rolls the outer transaction back so the injected mismatch and repair are not persisted;
 * - re-runs independent reconciliation after rollback and requires PASS.
 *
 * Immutable history, publication rows, pointer state, and run identity are read-only throughout.
 */

use App\Application\MarketData\Services\PublicationProjectionReconciliationService;
use App\Application\MarketData\Services\PublicationProjectionRepairService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

$root = realpath(dirname(__DIR__, 2));
if ($root === false || ! is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "B10_PROJECTION_REPAIR_PROBE_ROOT_UNRESOLVED\n");
    exit(2);
}

$tradeDate = null;
foreach ($argv as $arg) {
    if (strpos((string) $arg, '--trade-date=') === 0) {
        $tradeDate = trim(substr((string) $arg, strlen('--trade-date=')));
    }
}
if (! is_string($tradeDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tradeDate)) {
    fwrite(STDERR, "Use --trade-date=YYYY-MM-DD\n");
    exit(2);
}

/** @var \Laravel\Lumen\Application $app */
$app = require $root.'/bootstrap/app.php';
/** @var ConsoleKernel $kernel */
$kernel = $app->make(ConsoleKernel::class);
if (method_exists($kernel, 'bootstrap')) {
    $kernel->bootstrap();
}

$environment = strtolower(trim((string) $app->environment()));
$result = [
    'probe' => 'MarketDataB10ProjectionRepairDeployedProbe',
    'stage' => 'MD-B10',
    'attempt' => 'MD-B10-A001',
    'trade_date' => $tradeDate,
    'environment' => $environment,
    'status' => 'FAIL',
    'publication_id' => null,
    'run_id' => null,
    'ticker_id' => null,
    'baseline_reconciliation_state' => null,
    'baseline_mismatch_count' => null,
    'injected_reconciliation_state' => null,
    'injected_mismatch_count' => null,
    'repair_state' => null,
    'repair_after_state' => null,
    'repair_after_mismatch_count' => null,
    'lineage_restored' => false,
    'history_unchanged' => false,
    'outer_transaction_rolled_back' => false,
    'post_rollback_reconciliation_state' => null,
    'post_rollback_mismatch_count' => null,
    'errors' => [],
];

if ($environment === 'production') {
    $result['errors'][] = 'PRODUCTION_ENVIRONMENT_REFUSED';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(2);
}

/** @var EodPublicationRepository $publications */
$publications = $app->make(EodPublicationRepository::class);
/** @var PublicationProjectionReconciliationService $reconciliation */
$reconciliation = $app->make(PublicationProjectionReconciliationService::class);
/** @var PublicationProjectionRepairService $repair */
$repair = $app->make(PublicationProjectionRepairService::class);

$connection = DB::connection();
$transactionStarted = false;

try {
    $publication = $publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
    if (! $publication) {
        throw new RuntimeException('CURRENT_READABLE_PUBLICATION_UNRESOLVED');
    }
    $publicationId = (int) $publication->publication_id;
    $runId = (int) $publication->run_id;
    $result['publication_id'] = $publicationId;
    $result['run_id'] = $runId;

    $historyRow = DB::table('eod_eligibility_history')
        ->where('trade_date', $tradeDate)
        ->where('publication_id', $publicationId)
        ->where('run_id', $runId)
        ->where(function ($query) {
            $query->whereNotNull('trading_status_revision_id')
                ->orWhereNotNull('trading_status_source_observation_id');
        })
        ->orderBy('ticker_id')
        ->first([
            'ticker_id',
            'trading_status_revision_id',
            'trading_status_source_observation_id',
        ]);
    if (! $historyRow) {
        throw new RuntimeException('ELIGIBILITY_LINEAGE_FIXTURE_UNAVAILABLE');
    }

    $tickerId = (int) $historyRow->ticker_id;
    $result['ticker_id'] = $tickerId;
    $historyBefore = [
        'trading_status_revision_id' => $historyRow->trading_status_revision_id,
        'trading_status_source_observation_id' => $historyRow->trading_status_source_observation_id,
    ];

    $projectionBefore = DB::table('eod_eligibility')
        ->where('trade_date', $tradeDate)
        ->where('ticker_id', $tickerId)
        ->first([
            'trading_status_revision_id',
            'trading_status_source_observation_id',
        ]);
    if (! $projectionBefore) {
        throw new RuntimeException('CURRENT_ELIGIBILITY_PROJECTION_FIXTURE_UNAVAILABLE');
    }
    if ((string) $projectionBefore->trading_status_revision_id !== (string) $historyBefore['trading_status_revision_id']
        || (string) $projectionBefore->trading_status_source_observation_id !== (string) $historyBefore['trading_status_source_observation_id']) {
        throw new RuntimeException('CURRENT_PROJECTION_NOT_CLEAN_BEFORE_PROBE');
    }

    $connection->beginTransaction();
    $transactionStarted = true;

    $baseline = $reconciliation->reconcileTradeDate($tradeDate);
    $result['baseline_reconciliation_state'] = (string) ($baseline['reconciliation_state'] ?? '');
    $result['baseline_mismatch_count'] = (int) ($baseline['mismatch_count'] ?? -1);
    if ($result['baseline_reconciliation_state'] !== 'PASS' || $result['baseline_mismatch_count'] !== 0) {
        throw new RuntimeException('BASELINE_RECONCILIATION_NOT_PASS');
    }

    $mutatedValues = [];
    if ($historyBefore['trading_status_revision_id'] !== null) {
        $mutatedValues['trading_status_revision_id'] = null;
    }
    if ($historyBefore['trading_status_source_observation_id'] !== null) {
        $mutatedValues['trading_status_source_observation_id'] = null;
    }
    if ($mutatedValues === []) {
        throw new RuntimeException('ELIGIBILITY_LINEAGE_FIXTURE_HAS_NO_MUTABLE_DIFFERENCE');
    }

    $updated = DB::table('eod_eligibility')
        ->where('trade_date', $tradeDate)
        ->where('ticker_id', $tickerId)
        ->update($mutatedValues);
    if ((int) $updated !== 1) {
        throw new RuntimeException('PROJECTION_MISMATCH_INJECTION_CARDINALITY_INVALID: '.$updated);
    }

    $injected = $reconciliation->reconcileTradeDate($tradeDate);
    $result['injected_reconciliation_state'] = (string) ($injected['reconciliation_state'] ?? '');
    $result['injected_mismatch_count'] = (int) ($injected['mismatch_count'] ?? -1);
    if ($result['injected_reconciliation_state'] !== 'FAIL' || $result['injected_mismatch_count'] < 1
        || (int) ($injected['eligibility_value_mismatch_count'] ?? 0) < 1) {
        throw new RuntimeException('CONTROLLED_MISMATCH_NOT_DETECTED');
    }

    $repairResult = $repair->repairTradeDate($tradeDate);
    $result['repair_state'] = (string) ($repairResult['repair_state'] ?? '');
    $result['repair_after_state'] = (string) ($repairResult['after']['reconciliation_state'] ?? '');
    $result['repair_after_mismatch_count'] = (int) ($repairResult['after']['mismatch_count'] ?? -1);
    if ($result['repair_state'] !== 'REBUILT_AND_VERIFIED'
        || $result['repair_after_state'] !== 'PASS'
        || $result['repair_after_mismatch_count'] !== 0) {
        throw new RuntimeException('DEPLOYED_REPAIR_DID_NOT_REBUILD_AND_VERIFY');
    }

    $projectionAfterRepair = DB::table('eod_eligibility')
        ->where('trade_date', $tradeDate)
        ->where('ticker_id', $tickerId)
        ->first([
            'trading_status_revision_id',
            'trading_status_source_observation_id',
        ]);
    $result['lineage_restored'] = $projectionAfterRepair
        && (string) $projectionAfterRepair->trading_status_revision_id === (string) $historyBefore['trading_status_revision_id']
        && (string) $projectionAfterRepair->trading_status_source_observation_id === (string) $historyBefore['trading_status_source_observation_id'];
    if (! $result['lineage_restored']) {
        throw new RuntimeException('REPAIRED_LINEAGE_NOT_RESTORED_FROM_HISTORY');
    }

    $historyAfterRepair = DB::table('eod_eligibility_history')
        ->where('trade_date', $tradeDate)
        ->where('publication_id', $publicationId)
        ->where('run_id', $runId)
        ->where('ticker_id', $tickerId)
        ->first([
            'trading_status_revision_id',
            'trading_status_source_observation_id',
        ]);
    $result['history_unchanged'] = $historyAfterRepair
        && (string) $historyAfterRepair->trading_status_revision_id === (string) $historyBefore['trading_status_revision_id']
        && (string) $historyAfterRepair->trading_status_source_observation_id === (string) $historyBefore['trading_status_source_observation_id'];
    if (! $result['history_unchanged']) {
        throw new RuntimeException('IMMUTABLE_HISTORY_CHANGED_DURING_REPAIR_PROBE');
    }
} catch (Throwable $e) {
    $result['errors'][] = get_class($e).': '.$e->getMessage();
} finally {
    if ($transactionStarted && $connection->transactionLevel() > 0) {
        while ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }
        $result['outer_transaction_rolled_back'] = true;
    }
}

try {
    $postRollback = $reconciliation->reconcileTradeDate($tradeDate);
    $result['post_rollback_reconciliation_state'] = (string) ($postRollback['reconciliation_state'] ?? '');
    $result['post_rollback_mismatch_count'] = (int) ($postRollback['mismatch_count'] ?? -1);
    if ($result['post_rollback_reconciliation_state'] !== 'PASS' || $result['post_rollback_mismatch_count'] !== 0) {
        $result['errors'][] = 'POST_ROLLBACK_RECONCILIATION_NOT_PASS';
    }
} catch (Throwable $e) {
    $result['errors'][] = 'POST_ROLLBACK_RECONCILIATION_FAILED: '.$e->getMessage();
}

if (! $result['outer_transaction_rolled_back']) {
    $result['errors'][] = 'OUTER_TRANSACTION_ROLLBACK_NOT_CONFIRMED';
}
if (! $result['lineage_restored']) {
    $result['errors'][] = 'LINEAGE_RESTORATION_NOT_PROVEN';
}
if (! $result['history_unchanged']) {
    $result['errors'][] = 'HISTORY_IMMUTABILITY_NOT_PROVEN';
}

$result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($result['status'] === 'PASS' ? 0 : 1);
