<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingBreakoutIntegrityB01DraftCatalogService
{
    public const RUN_CODE = 'WS_BREAKOUT_INTEGRITY_B01_ONE_IDEA_DRAFT_CATALOG';
    public const APPROVAL_REFERENCE =
        'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_APPROVED_DRAFT_CATALOG';
    public const DIAGNOSTIC_ARTIFACT_HASH =
        '1a328ea84d4468fe2124263f25be272286dfbf01';
    public const DIAGNOSTIC_FILE_SHA1 =
        'fe703103a768a68a8286d13dd8f7ac41f3f2446a';

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
        $this->draftImport = $draftImport
            ?: new WeeklySwingParamsetDraftImportService();
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->gridRepository = $gridRepository
            ?: new WatchlistBacktestParamGridRepository();
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
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_APPROVAL_MISSING'
            );
        }
        foreach ([
            'watchlist_param_sets', 'watchlist_bt_eval',
            'watchlist_bt_param_grid',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked(
                    'WS_BREAKOUT_INTEGRITY_B01_SCHEMA_NOT_READY',
                    ['missing_table' => $table]
                );
            }
        }
        $diagnostic = $this->verifyDiagnostic($diagnosticArtifactPath);
        if (! ($diagnostic['valid'] ?? false)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_DIAGNOSTIC_ARTIFACT_INVALID',
                $diagnostic
            );
        }

        $sourceDraft = DB::table('watchlist_param_sets')
            ->where(
                'param_set_id',
                WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAM_SET_ID
            )
            ->first();
        $sourceEval = DB::table('watchlist_bt_eval')
            ->where(
                'eval_id',
                WeeklySwingBreakoutIntegrityB01DiagnosticService::SOURCE_EVAL_ID
            )
            ->first();
        if (! $sourceDraft || ! $sourceEval
            || (string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->params_hash
                !== WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAMSET_HASH
            || (int) $sourceEval->param_id
                !== WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAM_ID
            || (string) $sourceEval->paramset_hash
                !== WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAMSET_HASH) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_SOURCE_IDENTITY_INVALID'
            );
        }
        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        $sourceValidation = is_array($sourcePayload)
            ? $this->validator->validate($sourcePayload)
            : ['valid' => false];
        if (! ($sourceValidation['valid'] ?? false)
            || ($sourceValidation['canonical_hash'] ?? '')
                !== WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAMSET_HASH) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_SOURCE_PARAMSET_INVALID',
                ['validation' => $sourceValidation]
            );
        }

        $boundariesBefore = $this->forbiddenBoundaryCounts();
        try {
            $seed = $this->gridRepository->seedCatalog(
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::rows()
            );
            $rows = $this->gridRepository->allForCatalog(
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::CATALOG_CODE
            );
            if (count($rows)
                !== WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::CATALOG_COUNT) {
                throw new \RuntimeException(
                    'WS_BREAKOUT_INTEGRITY_B01_CATALOG_COUNT_MISMATCH'
                );
            }
            $row = $rows[0];
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
                'p01_reopened' => false,
                'q01_reopened' => false,
                'm01_reopened' => false,
                'source_anchor_is_best_of_failed_binding' => false,
                'source_eval_id' =>
                    WeeklySwingBreakoutIntegrityB01DiagnosticService
                        ::SOURCE_EVAL_ID,
                'source_param_set_id' =>
                    WeeklySwingBreakoutIntegrityB01DiagnosticService
                        ::SOURCE_PARAM_SET_ID,
                'source_params_hash' =>
                    WeeklySwingBreakoutIntegrityB01DiagnosticService
                        ::SOURCE_PARAMSET_HASH,
                'source_official_is_artifact_hash' =>
                    WeeklySwingBreakoutIntegrityB01DiagnosticService
                        ::SOURCE_ARTIFACT_HASH,
                'diagnostic_artifact_hash' => self::DIAGNOSTIC_ARTIFACT_HASH,
                'diagnostic_file_sha1' => self::DIAGNOSTIC_FILE_SHA1,
                'primary_hypothesis_code' =>
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                        ::PRIMARY_HYPOTHESIS_CODE,
                'catalog_code' =>
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                        ::CATALOG_CODE,
                'catalog_version' =>
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                        ::CATALOG_VERSION,
                'catalog_hash' =>
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::hash(),
                'catalog_row_code' => (string) $row['row_code'],
                'catalog_row_hash' => (string) $row['row_hash'],
                'hypothesis_code' => (string) $selection['hypothesis_code'],
                'research_rule_code' => (string) $selection['rule_code'],
                'research_execution_rule_code' =>
                    (string) $execution['rule_code'],
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
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::CATALOG_CODE,
                $source
            );
            if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                throw new \RuntimeException(
                    'WS_BREAKOUT_INTEGRITY_B01_DRAFT_PERSISTENCE_FAILED: '
                    .(string) ($import['reason_code'] ?? 'UNKNOWN')
                );
            }
            $canonicalPath = rtrim($outputDirectory, '/\\')
                .DIRECTORY_SEPARATOR.strtolower((string) $row['row_code'])
                .'.json';
            $write = $this->writeJson(
                $canonicalPath,
                $import['validation']['canonical_payload'],
                (bool) ($options['overwrite'] ?? false)
            );
        } catch (\Throwable $exception) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_CATALOG_PERSISTENCE_FAILED',
                ['error' => $exception->getMessage()]
            );
        }
        $boundariesAfter = $this->forbiddenBoundaryCounts();
        if ($boundariesBefore !== $boundariesAfter) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_FORBIDDEN_MUTATION_DETECTED',
                ['before' => $boundariesBefore, 'after' => $boundariesAfter]
            );
        }

        $draft = [
            'row_code' => (string) $row['row_code'],
            'row_hash' => (string) $row['row_hash'],
            'minimum_close_to_hh20_pct' => -0.05,
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
        $result = [
            'schema_version' => 'WS_BREAKOUT_INTEGRITY_B01_DRAFT_CATALOG_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'WS_BREAKOUT_INTEGRITY_B01_ONE_MINIMAL_DRAFT_PERSISTED',
            'reason_code' =>
                'WS_BREAKOUT_INTEGRITY_B01_ONLY_DIAGNOSTIC_AUTHORIZED_DRAFT_LOCKED',
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'p01_reopened' => false,
            'source_eval_id' =>
                WeeklySwingBreakoutIntegrityB01DiagnosticService::SOURCE_EVAL_ID,
            'source_param_set_id' =>
                WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAM_SET_ID,
            'source_params_hash' =>
                WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SOURCE_PARAMSET_HASH,
            'diagnostic_artifact_hash' => self::DIAGNOSTIC_ARTIFACT_HASH,
            'catalog_code' =>
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::CATALOG_CODE,
            'catalog_version' =>
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::CATALOG_VERSION,
            'catalog_hash' =>
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::hash(),
            'catalog_seed' => $seed,
            'catalog_row_count' => 1,
            'authorized_candidate_codes' => [
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::ROW_CODE,
            ],
            'rejected_candidates_not_persisted' => [
                'B01_C2_CLOSE_TO_HH20_FLOOR_NEG2',
                'B01_C3_RANGE_POSITION_20_GTE_80',
            ],
            'one_primary_idea_per_candidate' => true,
            'canonical_gates_changed' => false,
            'draft' => $draft,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' =>
                'WS_BREAKOUT_INTEGRITY_B01_RUN_OFFICIAL_IS_FOR_EXACT_LOCKED_DRAFT',
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
        if (! WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::isKnownRow(
            (string) ($row['row_code'] ?? '')
        )) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_UNKNOWN_CATALOG_ROW'
            );
        }
        $payload = $sourcePayload;
        $payload['paramset_code'] =
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::ROW_CODE;
        $payload['research_selection'] =
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                ::researchSelection();
        $payload['research_execution'] =
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                ::researchExecution();
        unset($payload['risk']['max_signal_tick_risk_expansion_pct']);

        return $payload;
    }

    private function verifyDiagnostic(string $path): array
    {
        if (! is_file($path)
            || strtolower((string) sha1_file($path))
                !== self::DIAGNOSTIC_FILE_SHA1) {
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
            || ($artifact['artifact_hash'] ?? '')
                !== self::DIAGNOSTIC_ARTIFACT_HASH
            || ($artifact['status'] ?? '')
                !== WeeklySwingBreakoutIntegrityB01DiagnosticService
                    ::SUCCESS_STATUS
            || (int) ($artifact['candidate_design_allowed_count'] ?? 0) !== 1
            || $allowedCodes !== [
                WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::ROW_CODE,
            ]
            || ($artifact['oos_table_read'] ?? true) !== false) {
            return [
                'valid' => false,
                'reason_code' => 'ARTIFACT_IDENTITY_MISMATCH',
            ];
        }
        $payload = $artifact;
        unset($payload['artifact_hash']);
        if ($this->identity->stableHash($payload)
            !== self::DIAGNOSTIC_ARTIFACT_HASH) {
            return [
                'valid' => false,
                'reason_code' => 'ARTIFACT_HASH_RECOMPUTE_MISMATCH',
            ];
        }

        return ['valid' => true];
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
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_JSON_ENCODING_FAILED'
            );
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_OUTPUT_DIRECTORY_CREATE_FAILED'
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
                    'WS_BREAKOUT_INTEGRITY_B01_OUTPUT_EXISTS_USE_OVERWRITE'
                );
            }
        }
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_OUTPUT_WRITE_FAILED'
            );
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
            'p01_reopened' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
