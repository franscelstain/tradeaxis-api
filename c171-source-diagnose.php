<?php

declare(strict_types=1);

use App\Application\MarketData\Services\MarketDataWatchlistReadService;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistMarketDataConsumerReadService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistScoringService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$tradeDate = $argv[1] ?? '2023-01-02';
$paramSetId = isset($argv[2]) ? (int) $argv[2] : 1;

$draft = DB::table('watchlist_param_sets')
    ->where('param_set_id', $paramSetId)
    ->first();

if (!$draft) {
    fwrite(STDERR, "Paramset not found: {$paramSetId}".PHP_EOL);
    exit(1);
}

$canonical = json_decode((string) $draft->params_json, true);

if (!is_array($canonical)) {
    fwrite(STDERR, "Invalid params_json.".PHP_EOL);
    exit(1);
}

$validator = new WeeklySwingParamsetValidator();
$validation = $validator->validate($canonical);

if (!($validation['valid'] ?? false)) {
    fwrite(
        STDERR,
        json_encode($validation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
    );
    exit(1);
}

$runtimeParamset = (new WeeklySwingParamsetRuntimeAdapter())
    ->adapt($validation['canonical_payload']);

$marketData = (new MarketDataWatchlistReadService())
    ->getWatchlistMarketDataForTradeDate($tradeDate);

$consumer = (new WatchlistMarketDataConsumerReadService())
    ->getCandidateUniverseForTradeDate($tradeDate);

$universeService = new WatchlistCandidateUniverseService();
$universe = $universeService->buildCandidateUniverseFromConsumerPayload(
    $consumer,
    $tradeDate,
    $runtimeParamset
);

$scoringService = new WatchlistScoringService($universeService);
$scoring = $scoringService->scoreCandidateUniverse(
    $universe,
    $runtimeParamset,
    $tradeDate
);

$groupingService = new WatchlistPlanGroupingService($scoringService);
$grouping = $groupingService->groupScoredOutput(
    $scoring,
    $runtimeParamset,
    $tradeDate
);

$result = [
    'trade_date' => $tradeDate,
    'market_data' => [
        'is_ready' => $marketData['is_ready'] ?? null,
        'reason_code' => $marketData['reason_code'] ?? null,
        'pointer_resolve_status' => $marketData['pointer_resolve_status'] ?? null,
        'publication_id' => $marketData['publication_id'] ?? null,
        'publication_version' => $marketData['publication_version'] ?? null,
        'run_id' => $marketData['run_id'] ?? null,
        'row_count' => count($marketData['rows'] ?? []),
    ],
    'consumer' => [
        'is_ready' => $consumer['is_ready'] ?? null,
        'reason_code' => $consumer['reason_code'] ?? null,
        'watchlist_reason_code' => $consumer['watchlist_reason_code'] ?? null,
        'pointer_resolve_status' => $consumer['pointer_resolve_status'] ?? null,
        'candidate_count' => $consumer['candidate_count'] ?? null,
        'excluded_count' => $consumer['excluded_count'] ?? null,
    ],
    'candidate_universe' => [
        'is_ready' => $universe['is_ready'] ?? null,
        'reason_code' => $universe['reason_code'] ?? null,
        'candidate_universe_reason_code' =>
            $universe['candidate_universe_reason_code'] ?? null,
        'input_candidate_count' => $universe['input_candidate_count'] ?? null,
        'eligible_count' => $universe['eligible_count'] ?? null,
        'rejected_count' => $universe['rejected_count'] ?? null,
        'paramset_errors' => $universe['paramset_errors'] ?? [],
    ],
    'scoring' => [
        'is_ready' => $scoring['is_ready'] ?? null,
        'reason_code' => $scoring['reason_code'] ?? null,
        'scoring_reason_code' => $scoring['scoring_reason_code'] ?? null,
        'summary' => $scoring['summary'] ?? [],
        'paramset_errors' => $scoring['paramset_errors'] ?? [],
    ],
    'grouping' => [
        'is_ready' => $grouping['is_ready'] ?? null,
        'reason_code' => $grouping['reason_code'] ?? null,
        'plan_grouping_reason_code' =>
            $grouping['plan_grouping_reason_code'] ?? null,
        'source_reason_code' => $grouping['source_reason_code'] ?? null,
        'summary' => $grouping['summary'] ?? [],
        'paramset_errors' => $grouping['paramset_errors'] ?? [],
    ],
];

$output = json_encode(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

echo $output.PHP_EOL;

file_put_contents(
    __DIR__.'/storage/app/watchlist/backtest/c171-source-debug-'.$tradeDate.'.json',
    $output.PHP_EOL,
    LOCK_EX
);