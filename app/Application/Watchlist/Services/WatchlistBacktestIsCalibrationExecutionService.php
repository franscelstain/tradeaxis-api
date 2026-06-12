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
            $c01Before = [];
            if (! empty($definition['requires_c01_catalog'])) {
                $c01Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
            }
            $c02Before = [];
            if (! empty($definition['requires_c02_catalog'])) {
                $c02Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
            }
            $c03Before = [];
            if (! empty($definition['requires_c03_catalog'])) {
                $c03Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
            }
            $c04Before = [];
            if (! empty($definition['requires_c04_catalog'])) {
                $c04Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
            }
            $c05Before = [];
            if (! empty($definition['requires_c05_catalog'])) {
                $c05Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
            }
            $c06Before = [];
            if (! empty($definition['requires_c06_catalog'])) {
                $c06Before = $this->paramGrid->catalogSnapshot(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
            }
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
        if (! empty($definition['requires_c01_catalog'])
            && ((int) ($c01Before['catalog_count'] ?? 0) !== WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT
                || (string) ($c01Before['catalog_hash'] ?? '') !== WatchlistBacktestC01ParamGridCatalog::hash())) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable C01 catalog count/hash is not intact before curated calibration.');
        }
        if (! empty($definition['requires_c02_catalog'])
            && ((int) ($c02Before['catalog_count'] ?? 0) !== WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT
                || (string) ($c02Before['catalog_hash'] ?? '') !== WatchlistBacktestC02ParamGridCatalog::hash())) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable C02 catalog count/hash is not intact before curated calibration.');
        }
        if (! empty($definition['requires_c03_catalog'])
            && ((int) ($c03Before['catalog_count'] ?? 0) !== WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT
                || (string) ($c03Before['catalog_hash'] ?? '') !== WatchlistBacktestC03ParamGridCatalog::hash())) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable C03 catalog count/hash is not intact before C04 calibration.');
        }
        if (! empty($definition['requires_c04_catalog'])
            && ((int) ($c04Before['catalog_count'] ?? 0) !== WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT
                || (string) ($c04Before['catalog_hash'] ?? '') !== WatchlistBacktestC04ParamGridCatalog::hash())) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable C04 catalog count/hash is not intact before C05 calibration.');
        }
        if (! empty($definition['requires_c05_catalog'])
            && ((int) ($c05Before['catalog_count'] ?? 0) !== WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT
                || (string) ($c05Before['catalog_hash'] ?? '') !== WatchlistBacktestC05ParamGridCatalog::hash())) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable C05 catalog count/hash is not intact before C06 calibration.');
        }
        if (! empty($definition['requires_c06_catalog'])
            && ((int) ($c06Before['catalog_count'] ?? 0) !== WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT
                || (string) ($c06Before['catalog_hash'] ?? '') !== WatchlistBacktestC06ParamGridCatalog::hash())) {
            return $this->blocked('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH', 'Immutable C06 catalog count/hash is not intact before C07 calibration.');
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
            $c01After = [];
            if (! empty($definition['requires_c01_catalog'])) {
                $c01After = $this->paramGrid->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
            }
            $c02After = [];
            if (! empty($definition['requires_c02_catalog'])) {
                $c02After = $this->paramGrid->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
            }
            $c03After = [];
            if (! empty($definition['requires_c03_catalog'])) {
                $c03After = $this->paramGrid->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
            }
            $c04After = [];
            if (! empty($definition['requires_c04_catalog'])) {
                $c04After = $this->paramGrid->catalogSnapshot(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
            }
            $c05After = [];
            if (! empty($definition['requires_c05_catalog'])) {
                $c05After = $this->paramGrid->catalogSnapshot(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
            }
            $c06After = [];
            if (! empty($definition['requires_c06_catalog'])) {
                $c06After = $this->paramGrid->catalogSnapshot(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
            }
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
        if (! empty($definition['requires_c01_catalog']) && $c01Before !== $c01After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'C01 snapshot changed during curated IS-only calibration.');
        }
        if (! empty($definition['requires_c02_catalog']) && $c02Before !== $c02After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'C02 snapshot changed during curated IS-only calibration.');
        }
        if (! empty($definition['requires_c03_catalog']) && $c03Before !== $c03After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'C03 snapshot changed during C04 IS-only calibration.');
        }
        if (! empty($definition['requires_c04_catalog']) && $c04Before !== $c04After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'C04 snapshot changed during C05 IS-only calibration.');
        }
        if (! empty($definition['requires_c05_catalog']) && $c05Before !== $c05After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'C05 snapshot changed during C06 IS-only calibration.');
        }
        if (! empty($definition['requires_c06_catalog']) && $c06Before !== $c06After) {
            return $this->blocked('WS_BT_R2_CATALOG_IDENTITY_CONFLICT', 'C06 snapshot changed during C07 IS-only calibration.');
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
            $c01Before,
            $c01After,
            $c02Before ?? [],
            $c02After ?? [],
            $c03Before ?? [],
            $c03After ?? [],
            $c04Before ?? [],
            $c04After ?? [],
            $c05Before ?? [],
            $c05After ?? [],
            $c06Before ?? [],
            $c06After ?? [],
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
        array $c01Before,
        array $c01After,
        array $c02Before,
        array $c02After,
        array $c03Before,
        array $c03After,
        array $c04Before,
        array $c04After,
        array $c05Before,
        array $c05After,
        array $c06Before,
        array $c06After,
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
            'c01_immutability_proof' => [
                'required' => ! empty($definition['requires_c01_catalog']),
                'before' => $c01Before,
                'after' => $c01After,
                'equal' => $c01Before === $c01After,
            ],
            'c02_immutability_proof' => [
                'required' => ! empty($definition['requires_c02_catalog']),
                'before' => $c02Before,
                'after' => $c02After,
                'equal' => $c02Before === $c02After,
            ],
            'c03_immutability_proof' => [
                'required' => ! empty($definition['requires_c03_catalog']),
                'before' => $c03Before,
                'after' => $c03After,
                'equal' => $c03Before === $c03After,
            ],
            'c04_immutability_proof' => [
                'required' => ! empty($definition['requires_c04_catalog']),
                'before' => $c04Before,
                'after' => $c04After,
                'equal' => $c04Before === $c04After,
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
            'c01_immutable' => empty($definition['requires_c01_catalog']) || $c01Before === $c01After,
            'c02_immutable' => empty($definition['requires_c02_catalog']) || $c02Before === $c02After,
            'c03_immutable' => empty($definition['requires_c03_catalog']) || $c03Before === $c03After,
            'c04_immutable' => empty($definition['requires_c04_catalog']) || $c04Before === $c04After,
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
        if (! empty($definition['requires_c05_catalog'])) {
            $artifact['c05_immutability_proof'] = [
                'required' => true,
                'before' => $c05Before,
                'after' => $c05After,
                'equal' => $c05Before === $c05After,
            ];
            $artifact['validation']['c05_immutable'] = $c05Before === $c05After;
        }
        if (! empty($definition['requires_c06_catalog'])) {
            $artifact['c06_immutability_proof'] = [
                'required' => true,
                'before' => $c06Before,
                'after' => $c06After,
                'equal' => $c06Before === $c06After,
            ];
            $artifact['validation']['c06_immutable'] = $c06Before === $c06After;
        }
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
                'requires_c01_catalog' => false,
                'requires_c02_catalog' => false,
                'requires_c03_catalog' => false,
                'requires_c04_catalog' => false,
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
                'requires_c01_catalog' => false,
                'requires_c02_catalog' => false,
                'requires_c03_catalog' => false,
                'requires_c04_catalog' => false,
            ],
            WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC02ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC02ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC02ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC02ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC02ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC02ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC02ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC02ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC02ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC02ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC02ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C02_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C02_IS_ONLY',
                'failed_status' => 'C02_GRID_FAILED_IS_QUALITY',
                'requires_c01_catalog' => true,
                'requires_c02_catalog' => false,
                'requires_c03_catalog' => false,
                'requires_c04_catalog' => false,
            ],
            WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC03ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC03ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC03ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC03ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC03ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC03ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC03ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC03ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC03ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC03ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC03ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C03_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C03_IS_ONLY',
                'failed_status' => 'C03_GRID_FAILED_IS_QUALITY',
                'requires_c01_catalog' => true,
                'requires_c02_catalog' => true,
                'requires_c03_catalog' => false,
                'requires_c04_catalog' => false,
            ],
            WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC04ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC04ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC04ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC04ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC04ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC04ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC04ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC04ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC04ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC04ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC04ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C04_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C04_IS_ONLY',
                'failed_status' => 'C04_GRID_FAILED_IS_QUALITY',
                'requires_c01_catalog' => true,
                'requires_c02_catalog' => true,
                'requires_c03_catalog' => true,
                'requires_c04_catalog' => false,
            ],
            WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC05ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC05ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC05ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC05ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC05ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC05ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC05ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC05ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC05ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC05ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC05ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C05_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C05_IS_ONLY',
                'failed_status' => 'C05_GRID_FAILED_IS_QUALITY',
                'requires_c01_catalog' => true,
                'requires_c02_catalog' => true,
                'requires_c03_catalog' => true,
                'requires_c04_catalog' => true,
            ],
            WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC06ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC06ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC06ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC06ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC06ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC06ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC06ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC06ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC06ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC06ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC06ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C06_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C06_IS_ONLY',
                'failed_status' => 'C06_GRID_FAILED_IS_QUALITY',
                'requires_c01_catalog' => true,
                'requires_c02_catalog' => true,
                'requires_c03_catalog' => true,
                'requires_c04_catalog' => true,
                'requires_c05_catalog' => true,
            ],
            WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE => [
                'catalog_code' => WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE,
                'catalog_version' => WatchlistBacktestC07ParamGridCatalog::CATALOG_VERSION,
                'catalog_count' => WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT,
                'catalog_hash' => WatchlistBacktestC07ParamGridCatalog::hash(),
                'parameter_axes' => WatchlistBacktestC07ParamGridCatalog::parameterAxes(),
                'axis_rationale' => WatchlistBacktestC07ParamGridCatalog::axisRationale(),
                'provenance' => WatchlistBacktestC07ParamGridCatalog::provenance(),
                'manifest_rows' => WatchlistBacktestC07ParamGridCatalog::manifestRows(),
                'reference_row_code' => WatchlistBacktestC07ParamGridCatalog::REFERENCE_ROW_CODE,
                'fixed_stop_atr_mult' => WatchlistBacktestC07ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC07ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC07ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC07ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'artifact_version' => 'WATCHLIST_C07_IS_CALIBRATION_V1',
                'artifact_scope' => 'WEEKLY_SWING_DOWNSIDE_STABILITY_C07_IS_ONLY',
                'failed_status' => 'C07_GRID_FAILED_IS_QUALITY',
                'requires_c01_catalog' => true,
                'requires_c02_catalog' => true,
                'requires_c03_catalog' => true,
                'requires_c04_catalog' => true,
                'requires_c05_catalog' => true,
                'requires_c06_catalog' => true,
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
