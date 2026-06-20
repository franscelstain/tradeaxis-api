<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;

class WatchlistBacktestC33DataPathReplayProofService
{
    public const RUN_CODE = 'C33_DATA_PATH_REPLAY_PROOF';
    public const ARTIFACT_TYPE = 'C33_DATA_PATH_REPLAY_PROOF';
    public const DEFAULT_C32_ARTIFACT = 'storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json';
    public const DEFAULT_EXPECTED_C32_HASH = '4bd92dfcf70dd0b02398d3ecf62d08c0356292ab';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c33-data-path-replay-proof.json';
    public const EXPECTED_C32_STATUS = 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED';
    public const EXPECTED_C32_CONCLUSION = 'C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED';
    public const EXPECTED_C32_DATA_PATH_STATUS = 'C32_DATA_PATH_REMEDIATION_REQUIRED';

    public function execute(
        string $c32Artifact = self::DEFAULT_C32_ARTIFACT,
        string $expectedC32Hash = self::DEFAULT_EXPECTED_C32_HASH,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c32Artifact = trim($c32Artifact) !== '' ? trim($c32Artifact) : self::DEFAULT_C32_ARTIFACT;
        $expectedC32Hash = trim($expectedC32Hash) !== '' ? trim($expectedC32Hash) : self::DEFAULT_EXPECTED_C32_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c32Artifact, $expectedC32Hash, null, null, null, null, $createdAt);

        if (! is_file($c32Artifact)) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_MISSING_C32_ARTIFACT',
                'WS_BT_C33_C32_ARTIFACT_MISSING',
                'C33 requires the locked C32 diagnostic split artifact, but the file is missing.',
                $outputPath,
                ['input_c32_artifact' => $c32Artifact]
            );
        }

        $c32 = json_decode((string) file_get_contents($c32Artifact), true);
        if (! is_array($c32)) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_MISSING_C32_ARTIFACT',
                'WS_BT_C33_C32_ARTIFACT_UNREADABLE',
                'C32 artifact is not readable JSON.',
                $outputPath,
                ['input_c32_artifact' => $c32Artifact]
            );
        }

        $actualC32Hash = $this->stableHash($c32);
        $artifact = $this->baseArtifact(
            $c32Artifact,
            $expectedC32Hash,
            $actualC32Hash,
            $c32['status'] ?? null,
            $c32['diagnostic_conclusion'] ?? null,
            $c32['data_path_remediation_status'] ?? null,
            $createdAt
        );

        if ($actualC32Hash !== $expectedC32Hash) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_C32_HASH_MISMATCH',
                'WS_BT_C33_C32_ARTIFACT_HASH_MISMATCH',
                'C32 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c32_artifact_hash_field' => $c32['artifact_hash'] ?? null]
            );
        }

        if (($c32['status'] ?? null) !== self::EXPECTED_C32_STATUS) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_UNEXPECTED_C32_STATUS',
                'WS_BT_C33_UNEXPECTED_C32_STATUS',
                'C33 requires a completed C32 data-path and bad-month diagnostic artifact.',
                $outputPath,
                ['expected_c32_status' => self::EXPECTED_C32_STATUS]
            );
        }

        if (($c32['production_ready'] ?? false) !== false && (int) ($c32['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_UNEXPECTED_C32_STATUS',
                'WS_BT_C33_C32_PRODUCTION_READY_UNEXPECTED',
                'C33 requires C32 production_ready=false before replay proof.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (($c32['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C32_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_UNEXPECTED_C32_CONCLUSION',
                'WS_BT_C33_UNEXPECTED_C32_CONCLUSION',
                'C33 requires C32 to confirm data-path remediation and bad-month robustness diagnostics are required.',
                $outputPath,
                ['expected_c32_conclusion' => self::EXPECTED_C32_CONCLUSION]
            );
        }

        if (($c32['data_path_remediation_status'] ?? null) !== self::EXPECTED_C32_DATA_PATH_STATUS) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_UNEXPECTED_C32_DATA_PATH_STATUS',
                'WS_BT_C33_UNEXPECTED_C32_DATA_PATH_STATUS',
                'C33 requires C32 data-path remediation status before replay proof.',
                $outputPath,
                ['expected_c32_data_path_status' => self::EXPECTED_C32_DATA_PATH_STATUS]
            );
        }

        $scopeRows = $this->scopeRows($c32);
        if (count($scopeRows) === 0) {
            return $this->blocked(
                $artifact,
                'C33_BLOCKED_NO_DATA_PATH_REPLAY_SCOPE',
                'WS_BT_C33_NO_DATA_PATH_REPLAY_SCOPE',
                'C33 requires at least one C32 missing_path_replay_rows item.',
                $outputPath
            );
        }

        $replayRows = [];
        foreach ($scopeRows as $row) {
            $replayRows[] = $this->replayRow($row, $options);
        }

        $summary = $this->replaySummary($replayRows);
        $conclusion = $this->replayConclusion($summary);
        $dataCompletenessGate = $this->dataCompletenessGate($summary);

        $artifact = array_replace_recursive($artifact, [
            'source_c32_data_path_remediation_scope' => is_array($c32['data_path_remediation_scope'] ?? null)
                ? $c32['data_path_remediation_scope']
                : [],
            'source_c32_split_decision' => is_array($c32['split_decision'] ?? null)
                ? $c32['split_decision']
                : [],
            'source_c32_next_step' => $c32['next_step'] ?? null,
            'replay_scope' => $this->replayScope($scopeRows),
            'replay_rows' => $replayRows,
            'replay_summary' => $summary,
            'data_path_replay_status' => $summary['data_path_replay_status'],
            'data_completeness_gate_after_replay' => $dataCompletenessGate,
            'diagnostic_conclusion' => $conclusion,
            'next_step' => $this->nextStep($summary),
            'status' => 'C33_DATA_PATH_REPLAY_PROOF_COMPLETED',
            'diagnostics' => $this->completedDiagnostics($summary, $conclusion),
        ]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C33_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C33 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
            ];
        }

        return [
            'status' => 'C33_DATA_PATH_REPLAY_PROOF_COMPLETED',
            'reason_code' => 'C33_DATA_PATH_REPLAY_PROOF_COMPLETED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c32_hash' => $expectedC32Hash,
            'actual_c32_hash' => $actualC32Hash,
            'c32_hash_match' => true,
            'c32_status' => $c32['status'] ?? null,
            'c32_diagnostic_conclusion' => $c32['diagnostic_conclusion'] ?? null,
            'c32_data_path_remediation_status' => $c32['data_path_remediation_status'] ?? null,
            'data_path_replay_status' => $summary['data_path_replay_status'],
            'data_completeness_gate_after_replay' => $dataCompletenessGate['status'],
            'replay_pass_count' => $summary['replay_pass_count'],
            'replay_fail_count' => $summary['replay_fail_count'],
            'replay_blocked_count' => $summary['replay_blocked_count'],
            'replay_row_count' => $summary['replay_row_count'],
            'diagnostic_conclusion' => $conclusion,
            'next_step' => $artifact['next_step'],
        ];
    }

    private function scopeRows(array $c32): array
    {
        $rows = array_values(array_filter($c32['missing_path_replay_rows'] ?? [], 'is_array'));
        usort($rows, function (array $a, array $b): int {
            return strcmp($this->rowKey($a), $this->rowKey($b));
        });
        return $rows;
    }

    private function replayScope(array $rows): array
    {
        $tradeDates = [];
        $entryDates = [];
        $tickers = [];
        $paramIds = [];
        $sourceCodes = [];
        $reasonCounts = [];

        foreach ($rows as $row) {
            $this->collect($tradeDates, $row['trade_date'] ?? null);
            $this->collect($entryDates, $row['entry_date'] ?? null);
            $this->collect($tickers, $row['ticker'] ?? null);
            $this->collect($paramIds, $row['param_id'] ?? null);
            $this->collect($sourceCodes, $row['selected_source_code'] ?? null);
            $reason = (string) ($row['missing_path_reason_code'] ?? 'UNKNOWN');
            $reasonCounts[$reason] = ($reasonCounts[$reason] ?? 0) + 1;
        }

        ksort($reasonCounts, SORT_STRING);

        return [
            'required_path_scope' => 'D1_TO_D5_RAW_OHLC_PATH',
            'required_path_days' => 5,
            'source_scope_count' => count($rows),
            'affected_trade_dates' => array_values($tradeDates),
            'affected_entry_dates' => array_values($entryDates),
            'affected_tickers' => array_values($tickers),
            'affected_param_ids' => array_values($paramIds),
            'affected_source_codes' => array_values($sourceCodes),
            'missing_path_reason_counts' => $reasonCounts,
            'can_claim_data_completeness_pass_before_replay' => false,
            'can_claim_oos_pass_before_replay' => false,
        ];
    }

    private function replayRow(array $row, array $options): array
    {
        $entryDate = trim((string) ($row['entry_date'] ?? ''));
        $ticker = strtoupper(trim((string) ($row['ticker'] ?? '')));
        $tickerId = $this->positiveInt($row['ticker_id'] ?? null);

        $base = [
            'trade_month' => $row['trade_month'] ?? null,
            'trade_date' => $row['trade_date'] ?? null,
            'entry_date' => $entryDate !== '' ? $entryDate : null,
            'ticker' => $ticker !== '' ? $ticker : null,
            'ticker_id' => $tickerId,
            'param_id' => $row['param_id'] ?? null,
            'row_code' => $row['row_code'] ?? null,
            'selected_source_code' => $row['selected_source_code'] ?? null,
            'selected_source_reason' => $row['selected_source_reason'] ?? null,
            'required_path_scope' => 'D1_TO_D5_RAW_OHLC_PATH',
            'required_path_days' => 5,
            'required_path_dates' => [],
            'available_path_dates' => [],
            'missing_path_dates' => [],
            'invalid_path_dates' => [],
            'path_bars' => [],
            'raw_ohlc_replay_status' => 'BLOCKED',
            'raw_ohlc_validated_flag' => false,
            'missing_path_data_flag' => true,
            'replay_reason_code' => 'WS_BT_C33_ROW_REPLAY_PENDING',
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];

        if ($entryDate === '' || $ticker === '') {
            return array_replace($base, [
                'raw_ohlc_replay_status' => 'BLOCKED',
                'replay_reason_code' => 'WS_BT_C33_INCOMPLETE_C32_REPLAY_ROW',
            ]);
        }

        $calendar = $this->pathDates($entryDate, $options);
        if (! ($calendar['ok'] ?? false)) {
            return array_replace($base, [
                'raw_ohlc_replay_status' => 'BLOCKED',
                'replay_reason_code' => $calendar['reason_code'] ?? 'WS_BT_C33_MARKET_CALENDAR_UNAVAILABLE',
            ]);
        }

        $requiredDates = array_values($calendar['dates']);
        $base['required_path_dates'] = $requiredDates;

        $barsResult = $this->pathBars($ticker, $tickerId, $requiredDates, $options);
        if (! ($barsResult['ok'] ?? false)) {
            return array_replace($base, [
                'raw_ohlc_replay_status' => 'BLOCKED',
                'replay_reason_code' => $barsResult['reason_code'] ?? 'WS_BT_C33_EOD_BARS_READ_UNAVAILABLE',
            ]);
        }

        $barsByDate = [];
        foreach ($barsResult['bars'] as $bar) {
            $barsByDate[(string) ($bar['trade_date'] ?? '')] = $bar;
        }

        $pathBars = [];
        $availableDates = [];
        $missingDates = [];
        $invalidDates = [];

        foreach ($requiredDates as $index => $date) {
            $bar = $barsByDate[$date] ?? null;
            if (! is_array($bar)) {
                $missingDates[] = $date;
                $pathBars[] = [
                    'day_offset' => $index + 1,
                    'trade_date' => $date,
                    'bar_available' => false,
                    'raw_ohlc_valid' => false,
                    'invalid_reason_code' => 'WS_BT_C33_D1_TO_D5_RAW_OHLC_BAR_MISSING',
                ];
                continue;
            }

            $validation = $this->validateCanonicalBar($bar);
            if (! $validation['valid']) {
                $invalidDates[] = $date;
            } else {
                $availableDates[] = $date;
            }

            $pathBars[] = array_merge([
                'day_offset' => $index + 1,
                'trade_date' => $date,
                'bar_available' => true,
                'raw_ohlc_valid' => $validation['valid'],
                'invalid_reason_code' => $validation['reason_code'],
            ], $this->barEvidence($bar));
        }

        $pass = count($missingDates) === 0 && count($invalidDates) === 0 && count($pathBars) === 5;

        return array_replace($base, [
            'required_path_dates' => $requiredDates,
            'available_path_dates' => $availableDates,
            'missing_path_dates' => $missingDates,
            'invalid_path_dates' => $invalidDates,
            'path_bars' => $pathBars,
            'raw_ohlc_replay_status' => $pass ? 'PASS' : 'FAIL',
            'raw_ohlc_validated_flag' => $pass,
            'missing_path_data_flag' => ! $pass,
            'replay_reason_code' => $pass
                ? 'WS_BT_C33_D1_TO_D5_RAW_OHLC_PATH_REPLAY_PASS'
                : 'WS_BT_C33_D1_TO_D5_RAW_OHLC_PATH_REPLAY_FAILED',
        ]);
    }

    private function pathDates(string $entryDate, array $options): array
    {
        if (array_key_exists('calendar_dates_fixture', $options)) {
            $dates = $this->fixturePathDates($entryDate, $options['calendar_dates_fixture']);
            if (count($dates) < 5) {
                return [
                    'ok' => false,
                    'reason_code' => 'WS_BT_C33_MARKET_CALENDAR_INSUFFICIENT_D1_D5_WINDOW',
                ];
            }
            return ['ok' => true, 'dates' => array_slice($dates, 0, 5)];
        }

        try {
            $dates = DB::table('market_calendar')
                ->where('cal_date', '>=', $entryDate)
                ->where('is_trading_day', 1)
                ->orderBy('cal_date')
                ->limit(5)
                ->pluck('cal_date')
                ->map(function ($value) {
                    return (string) $value;
                })
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'reason_code' => 'WS_BT_C33_MARKET_CALENDAR_UNAVAILABLE',
            ];
        }

        if (count($dates) < 5) {
            return [
                'ok' => false,
                'reason_code' => 'WS_BT_C33_MARKET_CALENDAR_INSUFFICIENT_D1_D5_WINDOW',
            ];
        }

        return ['ok' => true, 'dates' => $dates];
    }

    private function fixturePathDates(string $entryDate, $fixture): array
    {
        if (! is_array($fixture)) {
            return [];
        }

        $source = [];
        if (array_key_exists($entryDate, $fixture) && is_array($fixture[$entryDate])) {
            $source = $fixture[$entryDate];
        } else {
            $source = $fixture;
        }

        $dates = [];
        foreach ($source as $value) {
            if (is_array($value)) {
                $value = $value['cal_date'] ?? ($value['trade_date'] ?? null);
            }
            $date = trim((string) $value);
            if ($date !== '' && $date >= $entryDate) {
                $dates[$date] = $date;
            }
        }

        ksort($dates, SORT_STRING);
        return array_values($dates);
    }

    private function pathBars(string $ticker, ?int $tickerId, array $dates, array $options): array
    {
        if (array_key_exists('bars_fixture', $options)) {
            return ['ok' => true, 'bars' => $this->fixtureBars($ticker, $tickerId, $dates, $options['bars_fixture'])];
        }

        try {
            $query = DB::table('eod_bars as bar')
                ->whereIn('bar.trade_date', $dates);

            if ($tickerId !== null) {
                $query->where('bar.ticker_id', $tickerId);
            } else {
                $tickersTable = config('market_data.tickers.table', 'tickers');
                $tickerIdColumn = config('market_data.tickers.id_column', 'ticker_id');
                $tickerCodeColumn = config('market_data.tickers.code_column', 'ticker_code');
                $query->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'bar.ticker_id')
                    ->where('tick.'.$tickerCodeColumn, $ticker);
            }

            $rows = $query
                ->select(
                    'bar.trade_date',
                    'bar.ticker_id',
                    'bar.open',
                    'bar.high',
                    'bar.low',
                    'bar.close',
                    'bar.volume',
                    'bar.adj_close',
                    'bar.source',
                    'bar.run_id',
                    'bar.publication_id'
                )
                ->orderBy('bar.trade_date')
                ->get()
                ->map(function ($row) use ($ticker) {
                    return [
                        'trade_date' => (string) $row->trade_date,
                        'ticker_id' => (int) $row->ticker_id,
                        'ticker' => $ticker,
                        'open' => $row->open,
                        'high' => $row->high,
                        'low' => $row->low,
                        'close' => $row->close,
                        'volume' => $row->volume,
                        'adj_close' => $row->adj_close,
                        'source_name' => $row->source,
                        'run_id' => $row->run_id !== null ? (int) $row->run_id : null,
                        'publication_id' => $row->publication_id !== null ? (int) $row->publication_id : null,
                    ];
                })
                ->all();
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'reason_code' => 'WS_BT_C33_EOD_BARS_READ_UNAVAILABLE',
            ];
        }

        return ['ok' => true, 'bars' => $rows];
    }

    private function fixtureBars(string $ticker, ?int $tickerId, array $dates, $fixture): array
    {
        if (! is_array($fixture)) {
            return [];
        }

        $bars = [];
        foreach ($dates as $date) {
            $bar = $this->fixtureBarFor($ticker, $tickerId, $date, $fixture);
            if ($bar === null) {
                continue;
            }
            $bars[] = array_replace([
                'trade_date' => $date,
                'ticker' => $ticker,
                'ticker_id' => $tickerId,
                'source_name' => null,
                'run_id' => null,
                'publication_id' => null,
            ], $bar);
        }

        usort($bars, function (array $a, array $b): int {
            return strcmp((string) ($a['trade_date'] ?? ''), (string) ($b['trade_date'] ?? ''));
        });
        return $bars;
    }

    private function fixtureBarFor(string $ticker, ?int $tickerId, string $date, array $fixture): ?array
    {
        $keys = [
            strtoupper($ticker).'|'.$date,
            ($tickerId !== null ? (string) $tickerId : '').'|'.$date,
            $date.'|'.strtoupper($ticker),
            $date.'|'.($tickerId !== null ? (string) $tickerId : ''),
        ];

        foreach ($keys as $key) {
            if ($key !== '|' && isset($fixture[$key]) && is_array($fixture[$key])) {
                return $fixture[$key];
            }
        }

        if (isset($fixture[$ticker]) && is_array($fixture[$ticker]) && isset($fixture[$ticker][$date]) && is_array($fixture[$ticker][$date])) {
            return $fixture[$ticker][$date];
        }

        if ($tickerId !== null && isset($fixture[$tickerId]) && is_array($fixture[$tickerId]) && isset($fixture[$tickerId][$date]) && is_array($fixture[$tickerId][$date])) {
            return $fixture[$tickerId][$date];
        }

        foreach ($fixture as $bar) {
            if (! is_array($bar)) {
                continue;
            }
            $barTicker = strtoupper(trim((string) ($bar['ticker'] ?? ($bar['ticker_code'] ?? ''))));
            $barTickerId = $this->positiveInt($bar['ticker_id'] ?? null);
            $barDate = (string) ($bar['trade_date'] ?? '');
            if ($barDate === $date && ($barTicker === $ticker || ($tickerId !== null && $barTickerId === $tickerId))) {
                return $bar;
            }
        }

        return null;
    }

    private function validateCanonicalBar(array $bar): array
    {
        $open = $this->num($bar['open'] ?? null);
        $high = $this->num($bar['high'] ?? null);
        $low = $this->num($bar['low'] ?? null);
        $close = $this->num($bar['close'] ?? null);
        $volume = $this->num($bar['volume'] ?? null);
        $adjClose = $this->num($bar['adj_close'] ?? null);

        if ($open === null || $high === null || $low === null || $close === null
            || $open <= 0 || $high <= 0 || $low <= 0 || $close <= 0) {
            return ['valid' => false, 'reason_code' => 'WS_BT_C33_CANONICAL_OHLC_NON_POSITIVE_OR_NULL'];
        }
        if ($high < max($open, $close) || $low > min($open, $close) || $high < $low) {
            return ['valid' => false, 'reason_code' => 'WS_BT_C33_CANONICAL_OHLC_INCONSISTENT'];
        }
        if ($volume === null || $volume < 0) {
            return ['valid' => false, 'reason_code' => 'WS_BT_C33_CANONICAL_VOLUME_NULL_OR_NEGATIVE'];
        }
        if (array_key_exists('adj_close', $bar) && $bar['adj_close'] !== null && $adjClose !== null && $adjClose <= 0) {
            return ['valid' => false, 'reason_code' => 'WS_BT_C33_CANONICAL_ADJ_CLOSE_NON_POSITIVE'];
        }
        if (! array_key_exists('publication_id', $bar) || $bar['publication_id'] === null) {
            return ['valid' => false, 'reason_code' => 'WS_BT_C33_CANONICAL_PUBLICATION_ID_MISSING'];
        }
        if (! array_key_exists('run_id', $bar) || $bar['run_id'] === null) {
            return ['valid' => false, 'reason_code' => 'WS_BT_C33_CANONICAL_RUN_ID_MISSING'];
        }

        return ['valid' => true, 'reason_code' => null];
    }

    private function barEvidence(array $bar): array
    {
        return [
            'ticker_id' => $this->positiveInt($bar['ticker_id'] ?? null),
            'open' => $this->num($bar['open'] ?? null),
            'high' => $this->num($bar['high'] ?? null),
            'low' => $this->num($bar['low'] ?? null),
            'close' => $this->num($bar['close'] ?? null),
            'volume' => $this->num($bar['volume'] ?? null),
            'adj_close' => $this->num($bar['adj_close'] ?? null),
            'source_name' => $bar['source_name'] ?? ($bar['source'] ?? null),
            'run_id' => $bar['run_id'] ?? null,
            'publication_id' => $bar['publication_id'] ?? null,
        ];
    }

    private function replaySummary(array $rows): array
    {
        $pass = 0;
        $fail = 0;
        $blocked = 0;
        $missingDates = [];
        $invalidDates = [];
        $reasonCounts = [];

        foreach ($rows as $row) {
            $status = (string) ($row['raw_ohlc_replay_status'] ?? 'BLOCKED');
            if ($status === 'PASS') {
                $pass++;
            } elseif ($status === 'FAIL') {
                $fail++;
            } else {
                $blocked++;
            }

            foreach ($row['missing_path_dates'] ?? [] as $date) {
                $this->collect($missingDates, $date);
            }
            foreach ($row['invalid_path_dates'] ?? [] as $date) {
                $this->collect($invalidDates, $date);
            }
            $reason = (string) ($row['replay_reason_code'] ?? 'UNKNOWN');
            $reasonCounts[$reason] = ($reasonCounts[$reason] ?? 0) + 1;
        }

        ksort($reasonCounts, SORT_STRING);

        return [
            'replay_row_count' => count($rows),
            'replay_pass_count' => $pass,
            'replay_fail_count' => $fail,
            'replay_blocked_count' => $blocked,
            'missing_path_date_count' => count($missingDates),
            'invalid_path_date_count' => count($invalidDates),
            'missing_path_dates' => array_values($missingDates),
            'invalid_path_dates' => array_values($invalidDates),
            'replay_reason_counts' => $reasonCounts,
            'data_path_replay_status' => $this->dataPathReplayStatus($pass, $fail, $blocked, count($rows)),
            'actual_lookahead_fix_required' => false,
            'selection_leak_fix_required' => false,
            'oos_tuning_allowed' => false,
            'profile_reselection_allowed' => false,
            'production_promotion_allowed' => false,
            'production_ready' => false,
        ];
    }

    private function dataPathReplayStatus(int $pass, int $fail, int $blocked, int $total): string
    {
        if ($total > 0 && $pass === $total && $fail === 0 && $blocked === 0) {
            return 'C33_DATA_PATH_REPLAY_PASS';
        }
        if ($blocked > 0) {
            return 'C33_DATA_PATH_REPLAY_BLOCKED_RUNTIME_OR_CALENDAR_UNAVAILABLE';
        }
        return 'C33_DATA_PATH_REPLAY_FAILED_MISSING_OR_INVALID_PATH';
    }

    private function dataCompletenessGate(array $summary): array
    {
        $status = 'FAIL';
        if (($summary['data_path_replay_status'] ?? null) === 'C33_DATA_PATH_REPLAY_PASS') {
            $status = 'PASS';
        } elseif (($summary['replay_blocked_count'] ?? 0) > 0) {
            $status = 'BLOCKED';
        }

        return [
            'status' => $status,
            'reason_code' => $status === 'PASS'
                ? 'C33_DATA_COMPLETENESS_GATE_PASS_D1_TO_D5_RAW_OHLC_REPLAY'
                : ($status === 'BLOCKED'
                    ? 'C33_DATA_COMPLETENESS_GATE_BLOCKED_REPLAY_UNAVAILABLE'
                    : 'C33_DATA_COMPLETENESS_GATE_FAIL_MISSING_OR_INVALID_PATH'),
            'can_claim_data_completeness_pass' => $status === 'PASS',
            'can_claim_oos_pass' => false,
        ];
    }

    private function replayConclusion(array $summary): string
    {
        if (($summary['data_path_replay_status'] ?? null) === 'C33_DATA_PATH_REPLAY_PASS') {
            return 'C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE';
        }
        if (($summary['replay_blocked_count'] ?? 0) > 0) {
            return 'C33_DATA_PATH_REPLAY_BLOCKED_RUNTIME_OR_CALENDAR_UNAVAILABLE';
        }
        return 'C33_DATA_PATH_REPLAY_CONFIRMED_MISSING_OR_INVALID_D1_TO_D5_RAW_OHLC';
    }

    private function nextStep(array $summary): string
    {
        if (($summary['data_path_replay_status'] ?? null) === 'C33_DATA_PATH_REPLAY_PASS') {
            return 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_AFTER_C33_NO_OOS_TUNING';
        }
        if (($summary['replay_blocked_count'] ?? 0) > 0) {
            return 'RESOLVE_C33_RUNTIME_BLOCKER_THEN_RERUN_C33';
        }
        return 'REMEDIATE_MISSING_D1_TO_D5_RAW_OHLC_PATH_THEN_RERUN_C33';
    }

    private function completedDiagnostics(array $summary, string $conclusion): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C33_DATA_PATH_REPLAY_PROOF_COMPLETED',
                'message' => 'C33 replayed the C32 missing-path scope against exact D1-D5 raw OHLC dates without tuning or production promotion.',
                'fatal' => false,
                'extra' => $summary,
            ],
            [
                'reason_code' => $conclusion,
                'message' => 'C33 data-path replay conclusion.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C33_NO_OOS_TUNING_ALLOWED',
                'message' => 'C33 does not allow OOS tuning, profile reselection, best-of-OOS, production catalog creation, source acquisition, bar ingest, or eod_bars writes.',
                'fatal' => false,
                'extra' => [
                    'source_acquisition_executed' => false,
                    'bar_ingest_executed' => false,
                    'source_master_write_executed' => false,
                    'eod_bars_write_executed' => false,
                    'oos_tuning_allowed' => false,
                    'profile_reselection_allowed' => false,
                    'production_promotion_allowed' => false,
                    'production_ready' => false,
                ],
            ],
        ];
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, array $extra = []): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['diagnostic_conclusion'] = 'C33_DATA_PATH_REPLAY_BLOCKED';
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact, true);
        }

        return [
            'status' => $status,
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'expected_c32_hash' => $artifact['expected_c32_hash'] ?? null,
            'actual_c32_hash' => $artifact['actual_c32_hash'] ?? null,
            'c32_hash_match' => $artifact['c32_hash_match'] ?? false,
            'c32_status' => $artifact['c32_status'] ?? null,
            'c32_diagnostic_conclusion' => $artifact['c32_diagnostic_conclusion'] ?? null,
            'c32_data_path_remediation_status' => $artifact['c32_data_path_remediation_status'] ?? null,
            'production_ready' => 0,
        ];
    }

    private function baseArtifact(
        string $inputC32Path,
        string $expectedC32Hash,
        ?string $actualC32Hash,
        $c32Status,
        $c32Conclusion,
        $c32DataPathStatus,
        string $createdAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C33_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c32_artifact' => $inputC32Path,
            'expected_c32_hash' => $expectedC32Hash,
            'actual_c32_hash' => $actualC32Hash,
            'c32_hash_match' => $actualC32Hash !== null && $actualC32Hash === $expectedC32Hash,
            'c32_status' => $c32Status,
            'c32_diagnostic_conclusion' => $c32Conclusion,
            'c32_data_path_remediation_status' => $c32DataPathStatus,
            'source_c32_data_path_remediation_scope' => [],
            'source_c32_split_decision' => [],
            'source_c32_next_step' => null,
            'replay_scope' => [],
            'replay_rows' => [],
            'replay_summary' => [
                'actual_lookahead_fix_required' => false,
                'selection_leak_fix_required' => false,
                'oos_tuning_allowed' => false,
                'profile_reselection_allowed' => false,
                'production_promotion_allowed' => false,
                'production_ready' => false,
            ],
            'data_path_replay_status' => 'C33_DATA_PATH_REPLAY_PENDING',
            'data_completeness_gate_after_replay' => [
                'status' => 'PENDING',
                'can_claim_data_completeness_pass' => false,
                'can_claim_oos_pass' => false,
            ],
            'diagnostic_conclusion' => 'C33_DATA_PATH_REPLAY_PENDING',
            'next_step' => 'C33_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                'DATA_PATH_REPLAY_PROOF_ONLY' => true,
                'READ_ONLY_CURRENT_EOD_BARS_REPLAY_PROOF' => true,
                'NO_SOURCE_ACQUISITION' => true,
                'NO_BAR_INGEST' => true,
                'NO_SOURCE_MASTER_WRITE' => true,
                'NO_EOD_BARS_WRITE' => true,
                'source_acquisition_executed' => false,
                'bar_ingest_executed' => false,
                'source_master_write_executed' => false,
                'eod_bars_write_executed' => false,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C32_MUTATION' => true,
                'production_ready' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_profile_selection' => false,
            ],
            'execution_model' => [
                'entry' => 'NEXT_OPEN',
                'exit' => 'STOP_TP_OR_TIME',
                'hold' => 5,
                'fee' => 'IDR_FIXED',
                'slip' => 0,
                'gap' => 'OPEN',
                'px' => 'IDX_BANDS',
            ],
            'created_at' => $createdAt,
        ];
    }

    private function rowKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            strtoupper((string) ($row['ticker'] ?? '')),
            (string) ($row['param_id'] ?? ''),
            strtoupper((string) ($row['selected_source_code'] ?? '')),
            (string) ($row['row_code'] ?? ''),
        ]);
    }

    private function collect(array &$values, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $values[(string) $value] = $value;
        ksort($values, SORT_STRING);
    }

    private function positiveInt($value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }
        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function num($value): ?float
    {
        if ($value === '' || $value === null || ! is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) {
                return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.'];
            }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C33 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
