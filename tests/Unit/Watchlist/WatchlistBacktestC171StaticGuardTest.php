<?php

namespace Tests\Unit\Watchlist;

use TestCase;

class WatchlistBacktestC171StaticGuardTest extends TestCase
{
    public function testC171CommandIsRegisteredAndOfficialEvidenceSchemaIsProductionVersioned(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $migration = (string) file_get_contents(base_path('database/migrations/2026_07_25_000001_version_watchlist_official_backtest_evidence_and_paramset_identity.php'));

        $this->assertStringContainsString('RunBacktestC171VersionedOfficialIsEvidenceCommand::class', $kernel);
        foreach (['watchlist_bt_picks_ws','watchlist_bt_universe_ws','watchlist_bt_cutoffs_ws','eval_id','evidence_manifest_hash','params_hash'] as $token) {
            $this->assertStringContainsString($token, $migration);
        }
    }


    public function testC171MigrationAlignsActualMysqlForeignKeyTypesAndCanResumeAfterPartialDdl(): void
    {
        $migration = (string) file_get_contents(base_path('database/migrations/2026_07_25_000001_version_watchlist_official_backtest_evidence_and_paramset_identity.php'));

        foreach ([
            'prepareMysqlForeignKey',
            'mysqlColumnType',
            'ensureMysqlInnoDb',
            'mysqlForeignKeyExists',
            'createMysqlIndexIfMissing',
            'replaceMysqlPrimaryKey',
            'WS_C171_FOREIGN_KEY_CREATE_FAILED',
        ] as $token) {
            $this->assertStringContainsString($token, $migration);
        }

        $this->assertStringNotContainsString(
            "ALTER TABLE watchlist_bt_picks_ws MODIFY eval_id BIGINT UNSIGNED NOT NULL",
            $migration
        );
        $this->assertStringContainsString(
            "Historical owner DDL defines some identifiers as signed BIGINT/INT",
            $migration
        );
    }

    public function testC171UsesExactDraftIdentityAndDoesNotInvokeOosPromotionPlanOrRollout(): void
    {
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171VersionedOfficialIsEvidenceService.php'));

        foreach (['identity_paramset_hash_by_param_id','require_official_evidence','strict_is_boundary','CANONICAL_IS_FROM','CANONICAL_IS_TO','execution_route_proof','trade_candidates_frozen_before_price_read','future_price_used_for_evaluation_only','strategy_payload_immutable','oos_runtime_invoked','paramset_promoted','plan_run_created','controlled_rollout_executed'] as $token) {
            $this->assertStringContainsString($token, $service);
        }
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
    }

    public function testC171ForcesTheCanonicalIsWindowAndHardMarketDataBoundaryEvenForLegacyR1(): void
    {
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171VersionedOfficialIsEvidenceService.php'));
        $calibration = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'));

        $this->assertStringContainsString("CANONICAL_IS_FROM = '2023-01-02'", $service);
        $this->assertStringContainsString("CANONICAL_IS_TO = '2025-05-21'", $service);
        $this->assertStringContainsString("'strict_is_boundary' => true", $service);
        $this->assertStringContainsString('! empty($options[\'strict_is_boundary\'])', $calibration);
        $this->assertStringContainsString('$runtimeOptions[\'hard_market_data_to_date\'] = $to', $calibration);
    }

    public function testPromotionRequiresFullParamsetEvaluationAndEvidenceManifestIdentity(): void
    {
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingParamsetPromotionService.php'));
        foreach (['WS_PARAMSET_PROMOTION_FULL_PARAMSET_HASH_MISMATCH','WS_PARAMSET_PROMOTION_IS_IDENTITY_MISMATCH','WS_PARAMSET_PROMOTION_OOS_IDENTITY_MISMATCH','WS_PARAMSET_PROMOTION_OFFICIAL_EVIDENCE_HASH_MISMATCH'] as $token) {
            $this->assertStringContainsString($token, $service);
        }
    }

    public function testC171UsesStreamingOfficialEvidenceAndChunkedManifestValidation(): void
    {
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171VersionedOfficialIsEvidenceService.php'));
        $strategy = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php'));
        $repository = (string) file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestOfficialEvidenceRepository.php'));
        $identity = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingBacktestEvidenceIdentityService.php'));

        foreach (['official_evidence_spool', 'compact_replay_items', 'JSONL_SPOOL'] as $token) {
            $this->assertStringContainsString($token, $service.$strategy);
        }
        foreach (['canonicalizeSpool', 'insertJsonlRows', 'databaseTableDigest', 'INSERT_CHUNK_SIZE', 'READ_CHUNK_SIZE'] as $token) {
            $this->assertStringContainsString($token, $repository);
        }
        $this->assertStringContainsString('stableListHash', $identity);
        $this->assertStringNotContainsString('memory_limit=-1', $service.$strategy.$repository);
    }

    public function testC171ArtifactWritesAtomicallyAndParamsetPayloadIsDatabaseImmutable(): void
    {
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171VersionedOfficialIsEvidenceService.php'));
        $migration = (string) file_get_contents(base_path('database/migrations/2026_07_25_000001_version_watchlist_official_backtest_evidence_and_paramset_identity.php'));

        $this->assertStringContainsString('file_put_contents($temp, $json, LOCK_EX)', $service);
        $this->assertStringContainsString('rename($temp, $path)', $service);
        $this->assertStringContainsString('watchlist_param_sets payload is immutable', $migration);
        $this->assertStringContainsString('immutable official evidence (UPDATE blocked)', $migration);
        $this->assertStringContainsString('immutable official evidence (DELETE blocked)', $migration);
        $this->assertStringContainsString("OLD.status = 'DRAFT' AND NEW.status = 'ACTIVE'", $migration);
    }
    public function testC171UniverseVolRatioPrecisionSupportsHistoricalExtremeRatios(): void
    {
        $remediation = (string) file_get_contents(base_path('database/migrations/2026_07_27_000001_widen_watchlist_backtest_universe_vol_ratio_precision.php'));
        $createSchema = (string) file_get_contents(base_path('database/migrations/2026_06_09_000001_create_watchlist_backtest_oos_schema.php'));
        $sqliteSchema = (string) file_get_contents(base_path('tests/Support/UsesWatchlistRuntimeSqlite.php'));
        $ownerDdl = (string) file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/db/BACKTEST_SCHEMA_DDL.sql'));

        foreach (['watchlist_bt_universe_ws', 'vol_ratio', 'DECIMAL(20,6)', 'information_schema.COLUMNS'] as $token) {
            $this->assertStringContainsString($token, $remediation);
        }
        $this->assertStringContainsString("decimal('vol_ratio', 20, 6)", $createSchema);
        $this->assertStringContainsString("decimal('vol_ratio', 20, 6)", $sqliteSchema);
        $this->assertStringContainsString('vol_ratio       DECIMAL(20,6) NULL', $ownerDdl);
        $this->assertStringNotContainsString("decimal('vol_ratio', 10, 6)", $createSchema);
    }

    public function testC171TradeEvidenceDiagnosticIsRegisteredAndCannotInvokeOosPromotionPlanOrDraftMutation(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171TradeEvidenceDiagnosticService.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC171TradeEvidenceDiagnosticCommand.php'));

        $this->assertStringContainsString('RunBacktestC171TradeEvidenceDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c171-trade-evidence-diagnostic', $command);
        foreach ([
            'official_pick_parity',
            'requires_market_data_review',
            'STRATEGY_QUALITY_FAILURE_CONFIRMED',
            'MIXED_DATA_AND_STRATEGY_REMEDIATION_REQUIRED',
            'DATA_QUALITY_REVIEW_REQUIRED',
            "'draft_paramset_created' => false",
            "'oos_runtime_invoked' => false",
            "'paramset_promoted' => false",
            "'plan_run_created' => false",
            "'production_ready' => false",
        ] as $token) {
            $this->assertStringContainsString($token, $service);
        }
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('persistDraft(', $service);
    }

    public function testC171ImmutableRemediationDraftCatalogIsRegisteredAndCannotRunIsOosPromotionOrPlan(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171RemediationDraftCatalogService.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/PersistBacktestC171RemediationDraftCatalogCommand.php'));
        $migration = (string) file_get_contents(base_path('database/migrations/2026_07_27_000002_add_c171_real_is_remediation_catalog_bounds.php'));

        $this->assertStringContainsString('PersistBacktestC171RemediationDraftCatalogCommand::class', $kernel);
        $this->assertStringContainsString('SeedBacktestC171RemediationParamGridCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c171-persist-remediation-draft-catalog', $command);
        foreach ([
            'max_dv20_idr', 'max_vol_ratio', 'top_max_score_total',
            'SOURCE_EVAL_ID = 188', 'SOURCE_PARAM_SET_ID = 1',
            'STRATEGY_QUALITY_FAILURE_CONFIRMED',
            'deriveExpectedCandidateHashes',
            'DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW',
            "'official_is_runtime_invoked' => false",
            "'oos_runtime_invoked' => false",
            "'paramset_promoted' => false",
            "'plan_run_created' => false",
            "'production_ready' => false",
        ] as $token) {
            $this->assertStringContainsString($token, $service.$migration);
        }
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('WatchlistPlanRunRepository', $service);
        $this->assertStringNotContainsString('EXPECTED_PARAMSET_HASHES', $service.(string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestC171RemediationParamGridCatalog.php'
        )));
        foreach (['JSON_UNESCAPED_UNICODE', 'JSON_PRESERVE_ZERO_FRACTION', 'C171_REMEDIATION_DESIGN_HASH_ENCODING_FAILED'] as $token) {
            $this->assertStringContainsString($token, $service);
        }
        $reasonOwner = (string) file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md'));
        $reasonSeed = (string) file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql'));
        $coverage = (string) file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/14_WS_BT_COVERAGE_MATRIX_LOCKED.md'));
        foreach (['WS_LIQ_HIGH', 'WS_VOLR_HIGH'] as $reasonCode) {
            $this->assertStringContainsString($reasonCode, $reasonOwner);
            $this->assertStringContainsString($reasonCode, $reasonSeed);
        }
        foreach (['max_dv20_idr', 'max_vol_ratio', 'top_max_score_total'] as $column) {
            $this->assertStringContainsString($column, $coverage);
        }
    }


    public function testC171LowPriceExecutionQualityC01CatalogIsRegisteredAndCannotRunIsOosPromotionOrPlan(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171LowPriceExecutionQualityDraftCatalogService.php'));
        $catalog = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog.php'));
        $tickRisk = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingDecisionTimeTickRiskService.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/PersistBacktestC171LowPriceExecutionQualityDraftCatalogCommand.php'));
        $migration = (string) file_get_contents(base_path('database/migrations/2026_07_28_000001_add_c171_low_price_execution_quality_catalog_fields.php'));

        foreach ([
            'PersistBacktestC171LowPriceExecutionQualityDraftCatalogCommand::class',
            'SeedBacktestC171LowPriceExecutionQualityParamGridCommand::class',
        ] as $token) {
            $this->assertStringContainsString($token, $kernel);
        }
        $this->assertStringContainsString('watchlist:backtest-c171-persist-low-price-execution-quality-c01-draft-catalog', $command);
        foreach ([
            'SOURCE_EVAL_ID = 192',
            'SOURCE_PARAM_SET_ID = 5',
            'LOW_PRICE_TICK_RISK_DECISION_TIME_GUARD',
            'SCORE_RANKING_RECALIBRATION',
            'DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW',
            "'official_is_runtime_invoked' => false",
            "'oos_runtime_invoked' => false",
            "'oos_table_read' => false",
            "'paramset_promoted' => false",
            "'plan_run_created' => false",
            "'production_ready' => false",
        ] as $token) {
            $this->assertStringContainsString($token, $service.$catalog.$command);
        }
        foreach (['max_signal_tick_risk_expansion_pct', 'signal_close_price', 'signal_tick_risk_expansion_pct'] as $token) {
            $this->assertStringContainsString($token, $migration.$service.$catalog);
        }
        $this->assertStringContainsString('SIGNAL_CLOSE_ATR_STOP_NORMALIZED_WITH_IDX_EQUITY_PRICE_BANDS', $tickRisk);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('WatchlistPlanRunRepository', $service);
        $this->assertStringNotContainsString('EXPECTED_PARAMSET_HASHES', $service.$catalog);
    }

    public function testC171C01TickRiskExecutionAndEvidencePropagationRepairIsVersionedAndFailClosed(): void
    {
        $scoring = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistScoringService.php'));
        $strategy = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php'));
        $identity = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingBacktestEvidenceIdentityService.php'));
        $officialIs = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171VersionedOfficialIsEvidenceService.php'));
        $evaluationRepository = (string) file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestEvaluationRepository.php'));
        $migration = (string) file_get_contents(base_path('database/migrations/2026_07_28_000002_version_c171_tick_risk_evidence_pipeline.php'));
        $sqlite = (string) file_get_contents(base_path('tests/Support/UsesWatchlistRuntimeSqlite.php'));

        foreach ([
            "'signal_close_price' => \$this->metricOrNull",
            "'signal_tick_risk_expansion_pct' => \$this->metricOrNull",
            'theoretical_stop_risk_pct',
            'normalized_stop_risk_pct',
        ] as $token) {
            $this->assertStringContainsString($token, $scoring);
        }
        foreach ([
            "'max_dv20_idr' => \$this->optionalParamValue",
            "'max_vol_ratio' => \$this->optionalParamValue",
            "'stop_atr_mult' => \$this->paramValue",
            "'min_rr' => \$this->paramValue",
            "'max_signal_tick_risk_expansion_pct' => \$this->optionalParamValue",
        ] as $token) {
            $this->assertStringContainsString($token, $scoring);
        }
        foreach ([
            'C171_C01_DECISION_TIME_TICK_RISK_GUARD_EXECUTION_AND_EVIDENCE_PROPAGATION_V3',
            'above_threshold_before_guard_count',
            'above_threshold_without_tick_reason_count',
            'tick_only_rejected_count',
            'tick_multi_reason_rejected_count',
            'eligible_above_threshold_after_guard_count',
            'WS_C171_TICK_RISK_GUARD_EXECUTION_OR_EVIDENCE_PROPAGATION_FAILED',
        ] as $token) {
            $this->assertStringContainsString($token, $strategy);
        }
        foreach ([
            'EVIDENCE_PIPELINE_VERSION',
            'EVIDENCE_PIPELINE_CONTRACT',
            'evidencePipelineHash',
            'WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3',
        ] as $token) {
            $this->assertStringContainsString($token, $identity.$officialIs.$evaluationRepository);
        }
        foreach (['evidence_pipeline_version', 'evidence_pipeline_hash', 'UQ_bt_eval_catalog_param_window'] as $token) {
            $this->assertStringContainsString($token, $migration.$sqlite.$evaluationRepository.$officialIs);
        }
        $this->assertStringContainsString(
            'C171_REJECTED_TICK_RISK_GUARD_EXECUTION_OR_EVIDENCE_PROPAGATION_NOT_PROVEN',
            $officialIs
        );
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $officialIs);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $officialIs);
    }


    public function testC171C01EvidencePipelineMigrationBackfillsMetadataWithoutPermanentlyWeakeningImmutability(): void
    {
        $migration = (string) file_get_contents(base_path(
            'database/migrations/2026_07_28_000002_version_c171_tick_risk_evidence_pipeline.php'
        ));

        foreach ([
            "MYSQL_UPDATE_GUARD = 'trg_wbe_eval_no_update'",
            'immutablePayloadFingerprint',
            'WS_C171_EVIDENCE_PIPELINE_IMMUTABLE_PAYLOAD_CHANGED',
            'DROP TRIGGER IF EXISTS',
            'restoreMysqlEvaluationUpdateGuard',
            'finally',
            'immutable official evidence (UPDATE blocked)',
            'CREATE UNIQUE INDEX IF NOT EXISTS',
            'mysqlIndexExists',
        ] as $token) {
            $this->assertStringContainsString($token, $migration);
        }

        $this->assertStringNotContainsString('DROP TRIGGER IF EXISTS trg_wbe_eval_no_delete', $migration);
        $this->assertStringNotContainsString('DRAFT_PARAMSET', $migration);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $migration);
    }


    public function testC171FinalBoundedRemediationCatalogIsRegisteredAndLocksNoPassClosure(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171FinalBoundedRemediationDraftCatalogService.php'));
        $catalog = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/PersistBacktestC171FinalBoundedRemediationDraftCatalogCommand.php'));
        $decision = (string) file_get_contents(base_path('docs/watchlist/audit/_artifacts/c171-final-bounded-remediation-catalog-decision.json'));

        foreach ([
            'PersistBacktestC171FinalBoundedRemediationDraftCatalogCommand::class',
            'watchlist:backtest-c171-persist-final-bounded-remediation-draft-catalog',
            'SOURCE_EVAL_ID = 204',
            'SOURCE_PARAM_SET_ID = 11',
            'ONE_FINAL_BOUNDED_REMEDIATION_ALLOWED',
            'C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION',
            'additional_c171_candidate_catalog_allowed',
            "'official_is_runtime_invoked' => false",
            "'oos_runtime_invoked' => false",
            "'oos_table_read' => false",
            "'paramset_promoted' => false",
            "'plan_run_created' => false",
            "'production_ready' => false",
        ] as $token) {
            $this->assertStringContainsString($token, $kernel.$service.$catalog.$command.$decision);
        }
        $this->assertStringContainsString('CATALOG_COUNT = 3', $catalog);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('WatchlistPlanRunRepository', $service);
        $this->assertStringNotContainsString('exclude_tickers', $catalog);
    }


    public function testC171FinalFailedNotReadyClosureIsRegisteredReadOnlyAndForbidsFurtherRemediation(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171FinalFailedNotReadyClosureService.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/SealBacktestC171FinalFailedNotReadyClosureCommand.php'));
        $decision = (string) file_get_contents(base_path('docs/watchlist/audit/_artifacts/c171-final-failed-not-ready-closure-decision.json'));

        foreach ([
            'SealBacktestC171FinalFailedNotReadyClosureCommand::class',
            'watchlist:backtest-c171-seal-final-failed-not-ready-closure',
            'C171_FINAL_FAILED_NOT_READY_CLOSURE_AND_EVIDENCE_SEAL',
            'C171_FINAL_FAILED_NOT_READY_CLOSURE_SEALED',
            'C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION',
            'C171_NO_FINAL_CANDIDATE_PASSED_CANONICAL_IS_GATES',
            'FINAL_EVIDENCE',
            'eval_id' . "' => 205",
            'eval_id' . "' => 206",
            'eval_id' . "' => 207",
            "'additional_c171_candidate_catalog_allowed' => false",
            "'oos_allowed' => false",
            "'c172_allowed' => false",
            "'promotion_allowed' => false",
            "'plan_allowed' => false",
            "'production_ready' => false",
            "'oos_table_read' => false",
            'readSummaryCsv',
            'str_getcsv',
            "preg_replace('/^\\xEF\\xBB\\xBF/'",
        ] as $token) {
            $this->assertStringContainsString($token, $kernel.$service.$command.$decision);
        }
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('WatchlistPlanRunRepository', $service);
        $this->assertStringNotContainsString('insert(', $service);
        $this->assertStringNotContainsString('update(', $service);
        $this->assertStringNotContainsString('delete(', $service);
    }

}
