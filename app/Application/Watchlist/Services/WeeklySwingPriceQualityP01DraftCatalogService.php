<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingPriceQualityP01DraftCatalogService
{
    public const RUN_CODE = 'WS_PRICE_QUALITY_P01_TWO_ONE_IDEA_DRAFT_CATALOG';
    public const APPROVAL_REFERENCE = 'WS_PRICE_QUALITY_P01_OPERATOR_APPROVED_DRAFT_CATALOG';
    public const DIAGNOSTIC_ARTIFACT_HASH = '3e12d95c1a39673859aa95831c84017ca4b298c7';
    public const DIAGNOSTIC_FILE_SHA1 = '931440303da57995c0db169b5387f66c3a40c265';

    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetDraftImportService $draftImport;
    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WatchlistBacktestParamGridRepository $gridRepository;

    public function __construct(
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetDraftImportService $draftImport = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WatchlistBacktestParamGridRepository $gridRepository = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->draftImport = $draftImport ?: new WeeklySwingParamsetDraftImportService();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->gridRepository = $gridRepository ?: new WatchlistBacktestParamGridRepository();
    }

    public function execute(
        string $diagnosticArtifactPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputDirectory,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('WS_PRICE_QUALITY_P01_OPERATOR_APPROVAL_MISSING');
        }
        foreach (['watchlist_param_sets', 'watchlist_bt_eval', 'watchlist_bt_param_grid'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked(
                    'WS_PRICE_QUALITY_P01_SCHEMA_NOT_READY',
                    ['missing_table' => $table]
                );
            }
        }
        $diagnostic = $this->verifyDiagnostic($diagnosticArtifactPath);
        if (! ($diagnostic['valid'] ?? false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_DIAGNOSTIC_ARTIFACT_INVALID',
                $diagnostic
            );
        }

        $sourceDraft = DB::table('watchlist_param_sets')
            ->where(
                'param_set_id',
                WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAM_SET_ID
            )
            ->first();
        $sourceEval = DB::table('watchlist_bt_eval')
            ->where('eval_id', WeeklySwingPriceQualityP01DiagnosticService::SOURCE_EVAL_ID)
            ->first();
        if (! $sourceDraft || ! $sourceEval
            || (string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->params_hash
                !== WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAMSET_HASH
            || (int) $sourceEval->param_id
                !== WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAM_ID
            || (string) $sourceEval->paramset_hash
                !== WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAMSET_HASH) {
            return $this->blocked('WS_PRICE_QUALITY_P01_SOURCE_IDENTITY_INVALID');
        }
        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        $sourceValidation = is_array($sourcePayload)
            ? $this->validator->validate($sourcePayload)
            : ['valid' => false];
        if (! ($sourceValidation['valid'] ?? false)
            || ($sourceValidation['canonical_hash'] ?? '')
                !== WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAMSET_HASH) {
            return $this->blocked('WS_PRICE_QUALITY_P01_SOURCE_PARAMSET_INVALID', [
                'validation' => $sourceValidation,
            ]);
        }

        $boundariesBefore = $this->forbiddenBoundaryCounts();
        try {
            $seed = $this->gridRepository->seedCatalog(
                WatchlistBacktestPriceQualityP01ParamGridCatalog::rows()
            );
            $rows = $this->gridRepository->allForCatalog(
                WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_CODE
            );
            if (count($rows)
                !== WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_COUNT) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_CATALOG_COUNT_MISMATCH'
                );
            }

            $drafts = [];
            foreach ($rows as $row) {
                $payload = $this->buildCandidatePayload(
                    $sourceValidation['canonical_payload'],
                    $row
                );
                $selection = $payload['research_selection'];
                $execution = $payload['research_execution'];
                $source = [
                    'stage' => self::RUN_CODE,
                    'approval_reference' => $approvalReference,
                    'separate_new_strategy_scope' => true,
                    'c171_reopened' => false,
                    'r02_reopened' => false,
                    's01_reopened' => false,
                    'source_anchor_is_best_of_failed_binding' => false,
                    'source_eval_id' =>
                        WeeklySwingPriceQualityP01DiagnosticService::SOURCE_EVAL_ID,
                    'source_param_set_id' =>
                        WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAM_SET_ID,
                    'source_params_hash' =>
                        WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAMSET_HASH,
                    'source_official_is_artifact_hash' =>
                        WeeklySwingPriceQualityP01DiagnosticService::SOURCE_ARTIFACT_HASH,
                    'diagnostic_artifact_hash' => self::DIAGNOSTIC_ARTIFACT_HASH,
                    'diagnostic_file_sha1' => self::DIAGNOSTIC_FILE_SHA1,
                    'primary_hypothesis_code' =>
                        WatchlistBacktestPriceQualityP01ParamGridCatalog
                            ::PRIMARY_HYPOTHESIS_CODE,
                    'catalog_code' =>
                        WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_CODE,
                    'catalog_version' =>
                        WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_VERSION,
                    'catalog_hash' =>
                        WatchlistBacktestPriceQualityP01ParamGridCatalog::hash(),
                    'catalog_row_code' => (string) $row['row_code'],
                    'catalog_row_hash' => (string) $row['row_hash'],
                    'hypothesis_code' => (string) $selection['hypothesis_code'],
                    'research_rule_code' => (string) $selection['rule_code'],
                    'research_execution_rule_code' => (string) $execution['rule_code'],
                    'one_primary_idea' => true,
                    'decision_time_fields_only' => true,
                    'fixed_execution_before_entry' => true,
                    'future_derived_route_used' => false,
                    'oos_used' => false,
                    'canonical_gates_changed' => false,
                    'ticker_blacklist_used' => false,
                    'month_blacklist_used' => false,
                    'sector_whitelist_used' => false,
                    'entry_gap_as_runtime_input_used' => false,
                ];
                $import = $this->draftImport->execute(
                    $payload,
                    (int) $row['param_id'],
                    WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_CODE,
                    $source
                );
                if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                    || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                    throw new \RuntimeException(
                        'WS_PRICE_QUALITY_P01_DRAFT_PERSISTENCE_FAILED: '
                        .(string) ($import['reason_code'] ?? 'UNKNOWN')
                    );
                }
                $canonicalPath = rtrim($outputDirectory, '/\\')
                    .DIRECTORY_SEPARATOR.strtolower((string) $row['row_code']).'.json';
                $write = $this->writeJson(
                    $canonicalPath,
                    $import['validation']['canonical_payload'],
                    (bool) ($options['overwrite'] ?? false)
                );
                $drafts[] = [
                    'row_code' => (string) $row['row_code'],
                    'row_hash' => (string) $row['row_hash'],
                    'minimum_signal_close_price' =>
                        WatchlistBacktestPriceQualityP01ParamGridCatalog
                            ::minimumSignalClosePriceForRow((string) $row['row_code']),
                    'hypothesis_code' => (string) $selection['hypothesis_code'],
                    'research_rule_code' => (string) $selection['rule_code'],
                    'research_execution_rule_code' => (string) $execution['rule_code'],
                    'bt_param_id' => (int) $row['param_id'],
                    'param_set_id' => (int) $import['param_set_id'],
                    'paramset_status' => (string) $import['paramset_status'],
                    'params_hash' => (string) $import['validation']['canonical_hash'],
                    'persistence_status' =>
                        (string) ($import['persistence']['status'] ?? ''),
                    'canonical_file' => $write,
                    'official_is_run' => false,
                    'oos_allowed' => false,
                ];
            }
        } catch (\Throwable $exception) {
            return $this->blocked('WS_PRICE_QUALITY_P01_CATALOG_PERSISTENCE_FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }

        $boundariesAfter = $this->forbiddenBoundaryCounts();
        if ($boundariesBefore !== $boundariesAfter) {
            return $this->blocked('WS_PRICE_QUALITY_P01_FORBIDDEN_MUTATION_DETECTED', [
                'before' => $boundariesBefore,
                'after' => $boundariesAfter,
            ]);
        }
        usort($drafts, function (array $left, array $right): int {
            return strcmp($left['row_code'], $right['row_code']);
        });

        $result = [
            'schema_version' => 'WS_PRICE_QUALITY_P01_DRAFT_CATALOG_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'WS_PRICE_QUALITY_P01_TWO_MINIMAL_DRAFTS_PERSISTED',
            'reason_code' =>
                'WS_PRICE_QUALITY_P01_ONLY_DIAGNOSTIC_AUTHORIZED_DRAFTS_LOCKED',
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
            'source_anchor_is_best_of_failed_binding' => false,
            'source_eval_id' =>
                WeeklySwingPriceQualityP01DiagnosticService::SOURCE_EVAL_ID,
            'source_param_set_id' =>
                WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAM_SET_ID,
            'source_params_hash' =>
                WeeklySwingPriceQualityP01DiagnosticService::SOURCE_PARAMSET_HASH,
            'diagnostic_artifact_hash' => self::DIAGNOSTIC_ARTIFACT_HASH,
            'catalog_code' =>
                WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_CODE,
            'catalog_version' =>
                WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestPriceQualityP01ParamGridCatalog::hash(),
            'catalog_seed' => $seed,
            'catalog_row_count' => count($drafts),
            'authorized_candidate_codes' => [
                WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE,
                WatchlistBacktestPriceQualityP01ParamGridCatalog::C2_ROW_CODE,
            ],
            'rejected_candidate_not_persisted' =>
                'P01_C3_MIN_SIGNAL_PRICE_200',
            'max_hypothesis_count' => 1,
            'max_candidate_count' => 3,
            'actual_candidate_count' => count($drafts),
            'one_primary_idea_per_candidate' => true,
            'canonical_gates_changed' => false,
            'drafts' => $drafts,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' =>
                'WS_PRICE_QUALITY_P01_RUN_OFFICIAL_IS_FOR_EXACTLY_TWO_LOCKED_DRAFTS',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeJson(
            $outputPath,
            $result,
            (bool) ($options['overwrite'] ?? false)
        );

        return $result;
    }

    public function buildCandidatePayload(array $sourcePayload, array $row): array
    {
        $rowCode = (string) $row['row_code'];
        if (! WatchlistBacktestPriceQualityP01ParamGridCatalog::isKnownRow($rowCode)) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_UNKNOWN_CATALOG_ROW: '.$rowCode
            );
        }
        $payload = $sourcePayload;
        $payload['paramset_code'] = $rowCode;
        $payload['research_selection'] =
            WatchlistBacktestPriceQualityP01ParamGridCatalog
                ::researchSelectionForRow($rowCode);
        $payload['research_execution'] =
            WatchlistBacktestPriceQualityP01ParamGridCatalog::researchExecution();
        unset($payload['risk']['max_signal_tick_risk_expansion_pct']);

        return $payload;
    }

    private function verifyDiagnostic(string $path): array
    {
        if (! is_file($path)
            || strtolower((string) sha1_file($path)) !== self::DIAGNOSTIC_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'FILE_SHA1_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        $allowed = is_array($artifact['candidate_design_allowed'] ?? null)
            ? $artifact['candidate_design_allowed']
            : [];
        $allowedCodes = array_values(array_map(function (array $row): string {
            return (string) ($row['candidate_code'] ?? '');
        }, $allowed));
        if (! is_array($artifact)
            || ($artifact['artifact_hash'] ?? '') !== self::DIAGNOSTIC_ARTIFACT_HASH
            || ($artifact['status'] ?? '')
                !== WeeklySwingPriceQualityP01DiagnosticService::SUCCESS_STATUS
            || (int) ($artifact['candidate_design_allowed_count'] ?? 0) !== 2
            || $allowedCodes !== [
                WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE,
                WatchlistBacktestPriceQualityP01ParamGridCatalog::C2_ROW_CODE,
            ]
            || ($artifact['oos_table_read'] ?? true) !== false) {
            return ['valid' => false, 'reason_code' => 'ARTIFACT_IDENTITY_MISMATCH'];
        }
        $payload = $artifact;
        unset($payload['artifact_hash']);
        if ($this->identity->stableHash($payload) !== self::DIAGNOSTIC_ARTIFACT_HASH) {
            return [
                'valid' => false,
                'reason_code' => 'ARTIFACT_HASH_RECOMPUTE_MISMATCH',
            ];
        }

        return [
            'valid' => true,
            'reason_code' => 'WS_PRICE_QUALITY_P01_DIAGNOSTIC_VERIFIED',
        ];
    }

    private function forbiddenBoundaryCounts(): array
    {
        return [
            'watchlist_bt_eval' => DB::table('watchlist_bt_eval')->count(),
            'active_paramsets' => DB::table('watchlist_param_sets')
                ->where('policy_code', 'WS')
                ->where('status', 'ACTIVE')
                ->count(),
            'watchlist_plan_runs' => Schema::hasTable('watchlist_plan_runs')
                ? DB::table('watchlist_plan_runs')->count()
                : 0,
        ];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new \RuntimeException('WS_PRICE_QUALITY_P01_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_OUTPUT_DIRECTORY_CREATE_FAILED'
            );
        }
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return [
                    'status' => 'IDEMPOTENT',
                    'path' => $path,
                    'file_sha1' => sha1($existing),
                ];
            }
            if (! $overwrite) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_OUTPUT_EXISTS_USE_OVERWRITE'
                );
            }
        }
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('WS_PRICE_QUALITY_P01_OUTPUT_WRITE_FAILED');
        }

        return [
            'status' => 'WRITTEN',
            'path' => $path,
            'file_sha1' => sha1_file($path),
        ];
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
