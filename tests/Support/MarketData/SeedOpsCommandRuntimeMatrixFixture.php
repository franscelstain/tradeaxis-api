<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../../../vendor/autoload.php';

$app = require __DIR__.'/../../../bootstrap/app.php';
$app->boot();

$timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
$now = Carbon::now($timezone)->toDateTimeString();

$runtimeRoot = base_path('storage/app/market-data/ops-command-surface-runtime-matrix-production-ready');
$inputRoot = $runtimeRoot.'/input';
$barsRoot = base_path(config('market_data.source.local_directory', 'storage/app/market_data/eod_bars'));

File::ensureDirectoryExists($runtimeRoot);
File::ensureDirectoryExists($inputRoot);
File::ensureDirectoryExists($barsRoot);

$dates = [
    'daily_success' => '2026-05-11',
    'backfill_success' => '2026-05-12',
    'stage_success' => '2026-05-13',
    'promote_success' => '2026-05-14',
    'lock_conflict' => '2026-05-15',
    'held_partial' => '2026-05-18',
    'failed_empty' => '2026-05-19',
    'repair_invalid_pointer' => '2026-05-20',
];

$targetDates = array_values($dates);
$historyDates = array_values(array_filter(dateRange('2026-04-01', '2026-05-10'), static function ($date) {
    return (int) date('N', strtotime($date)) <= 5;
}));
$calendarDates = array_merge($historyDates, $targetDates);

$tickers = DB::table(config('market_data.tickers.table', 'tickers'))
    ->select(
        config('market_data.tickers.id_column', 'ticker_id').' as ticker_id',
        config('market_data.tickers.code_column', 'ticker_code').' as ticker_code'
    )
    ->where(config('market_data.tickers.active_column', 'is_active'), config('market_data.tickers.active_value', 1))
    ->orderBy(config('market_data.tickers.id_column', 'ticker_id'))
    ->get()
    ->map(function ($row) {
        return [
            'ticker_id' => (int) $row->ticker_id,
            'ticker_code' => strtoupper(trim((string) $row->ticker_code)),
        ];
    })
    ->all();

if ($tickers === []) {
    throw new RuntimeException('Ops command runtime matrix fixture requires active ticker rows.');
}

cleanupTargetDates($targetDates);
seedCalendar($calendarDates, $now);
seedHistoricalBars($historyDates, $tickers, $now);

$writtenFiles = [];
foreach (['daily_success', 'backfill_success', 'stage_success', 'promote_success', 'lock_conflict'] as $case) {
    $date = $dates[$case];
    $rows = buildBarRows($tickers, $date);
    $path = $barsRoot.'/'.$date.'.json';
    writeJson($path, $rows);
    writeJson($inputRoot.'/eod-bars-'.$date.'.json', $rows);
    $writtenFiles[$case] = normalizePath($path);
}

$partialDate = $dates['held_partial'];
$partialRows = buildBarRows($tickers, $partialDate, 5);
writeJson($barsRoot.'/'.$partialDate.'.json', $partialRows);
writeJson($inputRoot.'/eod-bars-'.$partialDate.'-partial.json', $partialRows);
$writtenFiles['held_partial'] = normalizePath($barsRoot.'/'.$partialDate.'.json');

$emptyDate = $dates['failed_empty'];
writeJson($inputRoot.'/eod-bars-'.$emptyDate.'-empty.json', []);
$writtenFiles['failed_empty'] = normalizePath($inputRoot.'/eod-bars-'.$emptyDate.'-empty.json');

$snapshotRows = buildSnapshotRows($tickers, $dates['promote_success']);
$snapshotPath = $inputRoot.'/session-snapshot-'.$dates['promote_success'].'.json';
writeJson($snapshotPath, $snapshotRows);
$writtenFiles['session_snapshot'] = normalizePath($snapshotPath);

$repairIds = seedInvalidCurrentPointer($dates['repair_invalid_pointer'], $now);

$manifest = [
    'generated_at' => $now,
    'database' => DB::connection()->getDatabaseName(),
    'runtime_root' => normalizePath($runtimeRoot),
    'ticker_count' => count($tickers),
    'dates' => $dates,
    'manual_input_files' => $writtenFiles,
    'history_seed_date_range' => [
        'start' => reset($historyDates),
        'end' => end($historyDates),
        'date_count' => count($historyDates),
        'rows_per_date' => count($tickers),
    ],
    'repair_invalid_pointer_fixture' => $repairIds,
    'guard' => 'Only ops runtime matrix fixture dates 2026-05-11..2026-05-20 are cleaned before seeding.',
];

writeJson($runtimeRoot.'/fixture_manifest.json', $manifest);

echo 'status=FIXTURE_READY'.PHP_EOL;
echo 'database='.DB::connection()->getDatabaseName().PHP_EOL;
echo 'runtime_root='.normalizePath($runtimeRoot).PHP_EOL;
echo 'ticker_count='.count($tickers).PHP_EOL;
echo 'history_date_count='.count($historyDates).PHP_EOL;
echo 'target_dates='.implode(',', $targetDates).PHP_EOL;
echo 'repair_fixture_run_id='.$repairIds['run_id'].PHP_EOL;
echo 'repair_fixture_publication_id='.$repairIds['publication_id'].PHP_EOL;
echo 'manifest='.normalizePath($runtimeRoot.'/fixture_manifest.json').PHP_EOL;

function cleanupTargetDates(array $targetDates): void
{
    $runIds = Schema::hasTable('eod_runs')
        ? DB::table('eod_runs')->whereIn('trade_date_requested', $targetDates)->pluck('run_id')->all()
        : [];

    foreach ([
        'md_session_snapshots',
        'eod_bars_history',
        'eod_indicators_history',
        'eod_eligibility_history',
        'eod_invalid_bars',
        'eod_eligibility',
        'eod_indicators',
        'eod_bars',
        'eod_current_publication_pointer',
        'eod_publications',
    ] as $table) {
        if (! Schema::hasTable($table)) {
            continue;
        }

        if (Schema::hasColumn($table, 'trade_date')) {
            DB::table($table)->whereIn('trade_date', $targetDates)->delete();
        } elseif (Schema::hasColumn($table, 'requested_date')) {
            DB::table($table)->whereIn('requested_date', $targetDates)->delete();
        }
    }

    if ($runIds !== [] && Schema::hasTable('eod_run_events')) {
        DB::table('eod_run_events')->whereIn('run_id', $runIds)->delete();
    }

    if (Schema::hasTable('eod_runs')) {
        DB::table('eod_runs')->whereIn('trade_date_requested', $targetDates)->delete();
    }
}

function seedCalendar(array $dates, string $now): void
{
    foreach ($dates as $date) {
        $isTradingDay = (int) date('N', strtotime($date)) <= 5;
        DB::table('md_market_calendar_revisions')->updateOrInsert(
            ['market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date],
            [
                'revision_uid' => hash('sha256', 'ops-runtime-calendar|'.$date.'|'.($isTradingDay ? 'TRADING' : 'CLOSED')),
                'timezone' => 'Asia/Jakarta',
                'is_trading_day' => $isTradingDay ? 1 : 0,
                'is_half_day' => 0,
                'session_state' => $isTradingDay ? 'COMPLETED' : 'CLOSED',
                'session_open_at' => $isTradingDay ? $date.' 09:00:00' : null,
                'session_close_at' => $isTradingDay ? $date.' 16:00:00' : null,
                'completed_at' => $isTradingDay ? $date.' 16:00:00' : null,
                'recorded_at' => $now,
                'source_observation_id' => null,
                'supersedes_revision_id' => null,
                'source_ref' => 'https://www.idx.co.id/ops-runtime-calendar/'.$date,
                'source_version' => 'idx-ops-runtime-v1',
                'provenance_tier' => 'VERIFIED',
                'reconciled_at' => $now,
                'reconciliation_source_ref' => 'https://www.idx.co.id/ops-runtime-calendar/'.$date,
            ]
        );
    }
}

function seedHistoricalBars(array $historyDates, array $tickers, string $now): void
{
    DB::table('eod_bars')->whereIn('trade_date', $historyDates)->delete();

    $rows = [];
    foreach ($historyDates as $date) {
        foreach ($tickers as $ticker) {
            $bar = makeBar($ticker, $date);
            $rows[] = [
                'trade_date' => $date,
                'ticker_id' => $ticker['ticker_id'],
                'open' => $bar['open'],
                'high' => $bar['high'],
                'low' => $bar['low'],
                'close' => $bar['close'],
                'volume' => $bar['volume'],
                'adj_close' => $bar['adj_close'],
                'source' => 'LOCAL_FILE',
                'run_id' => 0,
                'publication_id' => 0,
                'created_at' => $now,
            ];
        }
    }

    foreach (array_chunk($rows, 500) as $chunk) {
        DB::table('eod_bars')->insert($chunk);
    }
}

function seedInvalidCurrentPointer(string $tradeDate, string $now): array
{
    $runId = DB::table('eod_runs')->insertGetId([
        'trade_date_requested' => $tradeDate,
        'trade_date_effective' => $tradeDate,
        'lifecycle_state' => 'COMPLETED',
        'terminal_status' => 'HELD',
        'quality_gate_state' => 'FAIL',
        'publishability_state' => 'NOT_READABLE',
        'stage' => 'FINALIZE',
        'source' => 'manual_file',
        'request_mode' => 'promote',
        'coverage_universe_count' => 1,
        'coverage_available_count' => 0,
        'coverage_missing_count' => 1,
        'coverage_ratio' => 0,
        'coverage_min_threshold' => 0.98,
        'coverage_gate_state' => 'FAIL',
        'coverage_threshold_mode' => 'MIN_RATIO',
        'coverage_universe_basis' => 'ACTIVE_LISTED_EQUITY_AS_OF_DATE',
        'coverage_contract_version' => 'coverage_gate_v1',
        'is_current_publication' => 1,
        'final_reason_code' => 'OPS_MATRIX_INVALID_POINTER_FIXTURE',
        'notes' => 'ops_command_surface_runtime_matrix_invalid_pointer_fixture=true',
        'started_at' => $now,
        'finished_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
    ], 'run_id');

    $publicationId = DB::table('eod_publications')->insertGetId([
        'trade_date' => $tradeDate,
        'run_id' => $runId,
        'publication_version' => 1,
        'is_current' => 1,
        'seal_state' => 'UNSEALED',
        'bars_batch_hash' => null,
        'indicators_batch_hash' => null,
        'eligibility_batch_hash' => null,
        'sealed_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ], 'publication_id');

    DB::table('eod_runs')->where('run_id', $runId)->update([
        'publication_id' => $publicationId,
        'publication_version' => 1,
        'updated_at' => $now,
    ]);

    DB::table('eod_current_publication_pointer')->insert([
        'trade_date' => $tradeDate,
        'publication_id' => $publicationId,
        'run_id' => $runId,
        'publication_version' => 1,
        'sealed_at' => null,
        'updated_at' => $now,
    ]);

    return [
        'trade_date' => $tradeDate,
        'run_id' => (int) $runId,
        'publication_id' => (int) $publicationId,
        'violation_reasons' => [
            'PUBLICATION_NOT_SEALED',
            'POINTER_SEALED_AT_MISSING',
            'PUBLICATION_SEALED_AT_MISSING',
            'RUN_TERMINAL_STATUS_NOT_SUCCESS',
            'RUN_PUBLISHABILITY_NOT_READABLE',
            'RUN_SEALED_AT_MISSING',
        ],
    ];
}

function dateRange(string $startDate, string $endDate): array
{
    $dates = [];
    $cursor = Carbon::parse($startDate);
    $end = Carbon::parse($endDate);

    while ($cursor->lte($end)) {
        $dates[] = $cursor->toDateString();
        $cursor->addDay();
    }

    return $dates;
}

function buildBarRows(array $tickers, string $tradeDate, int $limit = null): array
{
    $rows = [];
    $selected = $limit === null ? $tickers : array_slice($tickers, 0, $limit);
    foreach ($selected as $ticker) {
        $bar = makeBar($ticker, $tradeDate);
        $rows[] = [
            'ticker_code' => $ticker['ticker_code'],
            'trade_date' => $tradeDate,
            'open' => $bar['open'],
            'high' => $bar['high'],
            'low' => $bar['low'],
            'close' => $bar['close'],
            'adj_close' => $bar['adj_close'],
            'volume' => $bar['volume'],
            'captured_at' => $tradeDate.' 17:20:00',
        ];
    }

    return $rows;
}

function buildSnapshotRows(array $tickers, string $tradeDate): array
{
    $rows = [];
    foreach ($tickers as $ticker) {
        $bar = makeBar($ticker, $tradeDate);
        $prev = max(1, $bar['close'] - 0.25);
        $rows[] = [
            'ticker_code' => $ticker['ticker_code'],
            'captured_at' => $tradeDate.' 09:10:00',
            'last_price' => $bar['close'],
            'prev_close' => $prev,
            'chg_pct' => round((($bar['close'] - $prev) / $prev) * 100, 4),
            'volume' => $bar['volume'],
            'day_high' => $bar['high'],
            'day_low' => $bar['low'],
        ];
    }

    return $rows;
}

function makeBar(array $ticker, string $tradeDate): array
{
    $dayOffset = Carbon::parse($tradeDate)->diffInDays(Carbon::parse('2026-04-01'));
    $tickerOffset = ($ticker['ticker_id'] % 97) / 10;
    $base = 100 + $tickerOffset + ($dayOffset * 0.15);
    $open = round($base, 4);
    $close = round($base + 0.35, 4);

    return [
        'open' => $open,
        'high' => round($base + 1.2, 4),
        'low' => round($base - 0.8, 4),
        'close' => $close,
        'adj_close' => $close,
        'volume' => 100000 + ($ticker['ticker_id'] * 10) + ($dayOffset * 100),
    ];
}

function writeJson(string $path, array $payload): void
{
    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
}

function normalizePath(string $path): string
{
    return str_replace('\\', '/', $path);
}
