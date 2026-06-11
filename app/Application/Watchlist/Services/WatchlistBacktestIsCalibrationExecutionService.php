<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WatchlistBacktestIsCalibrationExecutionService
{
    public const R2_MIN_IS_DATE = '2023-01-02';
    public const R2_MAX_IS_DATE = '2025-05-21';
    public const ARTIFACT_VERSION = 'WATCHLIST_R2_IS_CALIBRATION_V1';

    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestIsCalibrationService $calibration;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestRuntimeArtifactService $artifacts;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestIsCalibrationService $calibration = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestRuntimeArtifactService $artifacts = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->calibration = $calibration ?: new WatchlistBacktestIsCalibrationService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->artifacts = $artifacts ?: new WatchlistBacktestRuntimeArtifactService();
    }

    public function execute(
        string $catalogCode,
        string $fromDate,
        string $toDate,
        string $outputPath,
        array $options = []
    ): array {
        $catalogCode = trim($catalogCode);
        if ($catalogCode === '') {
            return $this->blocked('WS_BT_R2_CATALOG_MISSING', 'Explicit catalog code is required.');
        }
        try {
            $definition = $this->catalogDefinition($catalogCode);
        } catch (\Throwable $e) {
            return $this->blocked('WS_BT_R2_CATALOG_INVALID', $e->getMessage());
        }
        if (strcmp($toDate, self::R2_MAX_IS_DATE) > 0) {
            return $this->blocked(
                'WS_BT_R2_IS_BOUNDARY_VIOLATION',
                'IS calibration may not request or read any date after '.self::R2_MAX_IS_DATE.'.'
            );
        }
        if ($fromDate !== self::R2_MIN_IS_DATE || $toDate !== self::R2_MAX_IS_DATE) {
            return $this->blocked(
                'WS_BT_R2_IS_WINDOW_MISMATCH',
                'Immutable IS calibration requires the exact window '.self::R2_MIN_IS_DATE.' through '.self::R2_MAX_IS_DATE.'.'
            );
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked(
                $calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE',
                'Official IS trading calendar could not be resolved.',
                ['calendar' => $calendar]
            );
        }

        try {
            $r1Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            $catalogSnapshot = $this->paramGrid->catalogSnapshot($catalogCode);
            $r2Before = $catalogCode === WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE
                ? $catalogSnapshot
                : $this->paramGrid->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
            $oosBefore = $this->oosTableSnapshot();
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e, 'WS_BT_R2_CATALOG_INVALID'), $e->getMessage());
        }

        if ((int) $r1Before['catalog_count'] !== WatchlistBacktestParamGridCatalog::CATALOG_COUNT
            || (string) $r1Before['catalog_hash'] !== WatchlistBacktestParamGridCatalog::hash()) {
            return $this->blocked('WS_BT_R1_MUTATION_REJECTED', 'Immutable R1 catalog count/hash is not intact.');
        }
        if ((int) $catalogSnapshot['catalog_count'] !== $definition['catalog_count']
            || (string) $catalogSnapshot['catalog_hash'] !== $definition['catalog_hash']) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Persisted catalog count/hash differs from the immutable catalog.');
        }
        if ((int) $r2Before['catalog_count'] !== WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT
            || (string) $r2Before['catalog_hash'] !== WatchlistBacktestR2ParamGridCatalog::hash()) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable R2 catalog count/hash is not intact.');
        }

        $executedAt = $toDate.'T23:59:59+07:00';
        try {
            $calibration = $this->calibration->calibrate($calendar['trade_dates'], [
                'policy_code' => 'WS',
                'catalog_code' => $catalogCode,
                'executed_at' => $executedAt,
            ]);
            $r1After = $this->paramGrid->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            $r2After = $this->paramGrid->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
            $oosAfter = $this->oosTableSnapshot();
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e, 'WS_BT_R2_CALIBRATION_FAILED'), $e->getMessage());
        }

        if ($r1Before !== $r1After) {
            return $this->blocked('WS_BT_R1_MUTATION_REJECTED', 'R1 snapshot changed during IS-only calibration.');
        }
        if ($r2Before !== $r2After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'R2 snapshot changed during IS-only calibration.');
        }
        if ($oosBefore !== $oosAfter) {
            return $this->blocked('WS_BT_R2_OOS_PERSISTENCE_MUTATION', 'watchlist_bt_oos_eval_ws changed during IS-only calibration.');
        }

        $artifact = $this->buildArtifact(
            $definition,
            $catalogCode,
            $fromDate,
            $toDate,
            $executedAt,
            $calendar,
            $calibration,
            $r1Before,
            $r1After,
            $r2Before,
            $r2After,
            $catalogSnapshot,
            $oosBefore,
            $oosAfter
        );

        if (is_file($outputPath)) {
            if (! ($options['overwrite'] ?? false)) {
                return $this->blocked(
                    'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID',
                    'Output already exists; explicit --overwrite is required.',
                    ['artifact' => $artifact]
                );
            }
            if (! @unlink($outputPath)) {
                return $this->blocked(
                    'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                    'Existing output could not be removed for explicit overwrite.',
                    ['artifact' => $artifact]
                );
            }
        }

        $write = $this->artifacts->writeJsonArtifact($artifact, $outputPath);
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked(
                $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'IS evidence artifact could not be written.',
                ['artifact' => $artifact, 'write' => $write]
            );
        }

        $allRowsReachedCanonicalGates = (bool) ($artifact['validation']['all_rows_reached_canonical_gates'] ?? false);
        if (! $allRowsReachedCanonicalGates) {
            $firstFailure = $this->firstNonGateFailure($calibration['evaluations'] ?? []);

            return [
                'ready' => false,
                'is_ready' => false,
                'status' => 'BLOCKED',
                'reason_code' => $firstFailure['reason_code'] ?? 'WS_BT_R2_CALIBRATION_FAILED',
                'diagnostics' => [[
                    'reason_code' => $firstFailure['reason_code'] ?? 'WS_BT_R2_CALIBRATION_FAILED',
                    'message' => 'At least one catalog row did not reach canonical metric-gate evaluation. The artifact was written for honest diagnosis, but this is not an IS-quality verdict.',
                    'fatal' => true,
                ]],
                'calendar' => $calendar,
                'calibration' => $calibration,
                'artifact' => $artifact,
                'artifact_hash' => $artifact['validation']['artifact_hash'],
                'write' => $write,
                'production_ready' => false,
            ];
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'status' => ($calibration['is_valid_param_count'] ?? 0) > 0 ? 'PASS' : $definition['failed_status'],
            'reason_code' => $calibration['reason_code'],
            'calendar' => $calendar,
            'calibration' => $calibration,
            'artifact' => $artifact,
            'artifact_hash' => $artifact['validation']['artifact_hash'],
            'write' => $write,
            'production_ready' => false,
        ];
    }

    private function buildArtifact(
        array $definition,
        string $catalogCode,
        string $fromDate,
        string $toDate,
        string $executedAt,
        array $calendar,
        array $calibration,
        array $r1Before,
        array $r1After,
        array $r2Before,
        array $r2After,
        array $catalogSnapshot,
        array $oosBefore,
        array $oosAfter
    ): array {
        $evaluations = array_values(array_map(function (array $evaluation): array {
            unset($evaluation['persistence_status'], $evaluation['paramset_snapshot'], $evaluation['trade_evidence']);

            return $evaluation;
        }, $calibration['evaluations'] ?? []));

        $maxRequestedDate = null;
        $strictBoundaryAll = true;
        $calendarHashes = [];
        $priceHashes = [];
        $publicationHashes = [];
        $evalIds = [];
        foreach ($evaluations as $evaluation) {
            $date = $evaluation['max_requested_market_data_date'] ?? null;
            if (is_string($date) && ($maxRequestedDate === null || strcmp($date, $maxRequestedDate) > 0)) {
                $maxRequestedDate = $date;
            }
            $strictBoundaryAll = $strictBoundaryAll
                && (($evaluation['strict_is_boundary'] ?? false) === true);
            foreach ([
                'calendar_hash' => &$calendarHashes,
                'price_payload_hash' => &$priceHashes,
                'publication_manifest_hash' => &$publicationHashes,
            ] as $field => &$bucket) {
                $value = $evaluation[$field] ?? null;
                if (is_string($value) && $value !== '') {
                    $bucket[$value] = $value;
                }
            }
            unset($bucket);
            if (isset($evaluation['eval_id']) && $evaluation['eval_id'] !== null) {
                $evalIds[] = (int) $evaluation['eval_id'];
            }
        }
        sort($evalIds, SORT_NUMERIC);

        $failureDistribution = [];
        $diagnosticDistribution = [];
        foreach ($calibration['evaluations'] ?? [] as $evaluation) {
            foreach ((array) ($evaluation['reason_codes'] ?? [$evaluation['reason_code'] ?? null]) as $code) {
                if (is_string($code) && $code !== '') {
                    $failureDistribution[$code] = ($failureDistribution[$code] ?? 0) + 1;
                }
            }
            foreach (($evaluation['diagnostics'] ?? []) as $diagnostic) {
                $code = (string) ($diagnostic['reason_code'] ?? 'UNKNOWN');
                $diagnosticDistribution[$code] = ($diagnosticDistribution[$code] ?? 0) + 1;
            }
        }
        ksort($failureDistribution, SORT_STRING);
        ksort($diagnosticDistribution, SORT_STRING);

        $control = null;
        foreach ($evaluations as $evaluation) {
            if (($evaluation['row_code'] ?? null) === $definition['reference_row_code']) {
                $control = $evaluation;
                break;
            }
        }

        $artifact = [
            'meta' => [
                'artifact_version' => $definition['artifact_version'],
                'generated_at' => $executedAt,
                'scope' => $definition['artifact_scope'],
                'oos_executed' => false,
                'paramset_promoted' => false,
                'production_ready' => false,
            ],
            'catalog_manifest' => [
                'policy_code' => 'WS',
                'catalog_code' => $catalogCode,
                'catalog_version' => $definition['catalog_version'],
                'catalog_count' => $definition['catalog_count'],
                'catalog_hash' => $definition['catalog_hash'],
                'ordered_row_hashes' => $catalogSnapshot['ordered_row_hashes'],
                'ordered_param_hashes' => $calibration['ordered_param_hashes'] ?? [],
                'provenance' => $definition['provenance'],
                'curated_rows' => $definition['manifest_rows'],
                'immutable_after_first_execution' => true,
                'random_or_bayesian_search' => false,
                'control_row_code' => $definition['reference_row_code'],
            ],
            'parameter_axes' => [
                'entry_quality_axes' => $definition['parameter_axes'],
                'axis_rationale' => $definition['axis_rationale'],
                'fixed_execution_axes' => [
                    'risk.stop_atr_mult' => $definition['fixed_stop_atr_mult'],
                    'risk.min_rr' => $definition['fixed_min_rr'],
                    'grouping.top_picks_target' => $definition['fixed_top_picks_target'],
                    'grouping.secondary_target' => $definition['fixed_secondary_target'],
                    'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
                ],
            ],
            'r1_control_reference' => [
                'r1_catalog_code' => WatchlistBacktestParamGridCatalog::CATALOG_CODE,
                'r1_catalog_hash' => WatchlistBacktestParamGridCatalog::hash(),
                'r1_reference_row' => '01_BASELINE',
                'catalog_reference_row_code' => $definition['reference_row_code'],
                'control_evaluation' => $control,
                'control_is_not_automatically_valid_or_best' => true,
            ],
            'is_window_manifest' => [
                'requested_from' => $fromDate,
                'requested_to' => $toDate,
                'trading_date_count' => count($calendar['trade_dates'] ?? []),
                'ordered_trading_date_hash' => $calibration['is_trading_date_hash'] ?? null,
                'calendar_hash' => $calendar['calendar_hash'] ?? null,
                'reserved_oos_from' => '2025-05-22',
                'reserved_oos_to' => '2026-05-29',
                'boundary_censoring_rule' => 'EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PRICE_READS_WITHIN_IS',
            ],
            'market_data_lineage' => [
                'calendar_sources' => $calendar['calendar_sources'] ?? [],
                'calendar_hashes' => array_values($calendarHashes),
                'price_payload_hashes' => array_values($priceHashes),
                'publication_manifest_hashes' => array_values($publicationHashes),
                'official_publication_read_surface_only' => true,
                'no_raw_market_data' => true,
                'no_latest_or_max_date_fallback' => true,
            ],
            'all_evaluations' => $evaluations,
            'best_is_binding' => $calibration['best_is_binding'] ?? null,
            'gate_summary' => [
                'valid_count' => (int) ($calibration['is_valid_param_count'] ?? 0),
                'failed_count' => (int) ($calibration['is_failed_param_count'] ?? 0),
                'failure_reason_distribution' => $failureDistribution,
                'canonical_gates_unchanged' => true,
                'best_of_failed_forbidden' => true,
            ],
            'diagnostic_summary' => [
                'distribution' => $diagnosticDistribution,
                'count' => array_sum($diagnosticDistribution),
            ],
            'persistence_manifest' => [
                'official_table' => 'watchlist_bt_eval',
                'eval_ids' => $evalIds,
                'eval_count' => count($evalIds),
                'identity_fields' => [
                    'policy_code', 'catalog_code', 'catalog_version', 'param_id',
                    'eval_model', 'paramset_hash', 'from_date', 'to_date',
                ],
                'exact_rerun_idempotent' => true,
                'conflicting_duplicate_fails_closed' => true,
            ],
            'r1_immutability_proof' => [
                'before' => $r1Before,
                'after' => $r1After,
                'equal' => $r1Before === $r1After,
            ],
            'r2_immutability_proof' => [
                'before' => $r2Before,
                'after' => $r2After,
                'equal' => $r2Before === $r2After,
            ],
            'no_oos_read_proof' => [
                'strict_is_boundary_all_evaluations' => $strictBoundaryAll,
                'max_requested_market_data_date' => $maxRequestedDate,
                'max_allowed_market_data_date' => self::R2_MAX_IS_DATE,
                'max_date_within_boundary' => $maxRequestedDate === null || strcmp($maxRequestedDate, self::R2_MAX_IS_DATE) <= 0,
                'oos_service_invoked' => false,
                'oos_repository_invoked' => false,
                'oos_table_before' => $oosBefore,
                'oos_table_after' => $oosAfter,
                'oos_table_unchanged' => $oosBefore === $oosAfter,
            ],
            'validation' => [],
        ];

        $artifact['validation'] = [
            'catalog_count_matches' => $catalogSnapshot['catalog_count'] === $definition['catalog_count'],
            'catalog_hash_matches' => $catalogSnapshot['catalog_hash'] === $definition['catalog_hash'],
            'r1_immutable' => $r1Before === $r1After,
            'r2_immutable' => $r2Before === $r2After,
            'no_oos_table_mutation' => $oosBefore === $oosAfter,
            'no_oos_market_data_read' => ($maxRequestedDate === null || strcmp($maxRequestedDate, self::R2_MAX_IS_DATE) <= 0)
                && $strictBoundaryAll,
            'all_rows_evaluated_or_reason_coded' => count($evaluations) === $definition['catalog_count'],
            'all_rows_reached_canonical_gates' => count($evaluations) === $definition['catalog_count']
                && count(array_filter($evaluations, function (array $evaluation): bool {
                    return in_array((string) ($evaluation['status'] ?? ''), ['VALID', 'GATES_FAILED'], true);
                })) === $definition['catalog_count'],
            'best_binding_only_when_valid' => ($calibration['best_is_binding'] ?? null) === null
                ? ((int) ($calibration['is_valid_param_count'] ?? 0) === 0)
                : ((int) ($calibration['is_valid_param_count'] ?? 0) > 0),
            'production_ready' => false,
            'artifact_hash' => null,
        ];
        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];

        return $artifact;
    }


    private function firstNonGateFailure(array $evaluations): array
    {
        foreach ($evaluations as $evaluation) {
            if (! in_array((string) ($evaluation['status'] ?? ''), ['VALID', 'GATES_FAILED'], true)) {
                return is_array($evaluation) ? $evaluation : [];
            }
        }

        return [];
    }

    private function oosTableSnapshot(): array
    {
        if (! Schema::hasTable('watchlist_bt_oos_eval_ws')) {
            return ['table_exists' => false, 'count' => 0, 'hash' => sha1('[]')];
        }
        $rows = array_values(array_map(function ($row): array {
            return (array) $row;
        }, DB::table('watchlist_bt_oos_eval_ws')->orderBy('oos_id', 'asc')->get()->all()));

        return [
            'table_exists' => true,
            'count' => count($rows),
            'hash' => $this->stableHash($rows),
        ];
    }

    private function artifactForHash(array $artifact): array
    {
        unset($artifact['validation']['artifact_hash'], $artifact['meta']['artifact_hash']);

        return $artifact;
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_merge([
            'ready' => false,
            'is_ready' => false,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'diagnostics' => [[
                'reason_code' => $reasonCode,
                'message' => $message,
                'fatal' => true,
            ]],
            'production_ready' => false,
        ], $extra);
    }

    private function reasonCode(\Throwable $e, string $fallback): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return $fallback;
    }

    private function catalogDefinition(string $catalogCode): array
    {
        $definitions = [
            WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestR2ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestR2ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestR2ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestR2ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestR2ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestR2ParamGridCatalog::R1_CONTROL_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestR2ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestR2ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestR2ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestR2ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => self::ARTIFACT_VERSION,
                'artifact_scope' => 'WEEKLY_SWING_R2_ENTRY_QUALITY_IS_ONLY',
                'failed_status' => 'R2_GRID_FAILED_IS_QUALITY',
            ],
            WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC01ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC01ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC01ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC01ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC01ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC01ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC01ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC01ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC01ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC01ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C01_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C01_IS_ONLY',
                'failed_status' => 'C01_GRID_FAILED_IS_QUALITY',
            ],
        ];

        if (! isset($definitions[$catalogCode])) {
            throw new \RuntimeException('catalog_code is not an approved immutable IS calibration catalog.');
        }

        return $definitions[$catalogCode];
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalize($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
