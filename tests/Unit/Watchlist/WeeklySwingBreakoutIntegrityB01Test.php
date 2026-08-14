<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01DiagnosticService;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01DraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01IsIdentityReviewService;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService;
use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01ActiveShadowService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;
use TestCase;

class WeeklySwingBreakoutIntegrityB01Test extends TestCase
{
    public function test_diagnostic_authorizes_only_the_locked_neg5_candidate(): void
    {
        $rows = [];
        for ($index = 0; $index < 160; $index++) {
            $far = $index < 20;
            $rows[] = [
                'trade_date' => (new \DateTimeImmutable('2023-01-02'))
                    ->modify('+'.$index.' days')
                    ->format('Y-m-d'),
                'ret_net' => $far ? -0.10 : 0.01,
                'close_to_hh20_pct' => $far ? -6.0 : -4.0,
                'range_position_20_pct' => 70.0,
            ];
        }

        $analysis = (new WeeklySwingBreakoutIntegrityB01DiagnosticService())
            ->analyzeEvidence($rows, 498);

        $this->assertSame([
            'B01_C1_CLOSE_TO_HH20_FLOOR_NEG5',
            'B01_C2_CLOSE_TO_HH20_FLOOR_NEG2',
            'B01_C3_RANGE_POSITION_20_GTE_80',
        ], array_column($analysis['candidate_diagnostics'], 'candidate_code'));
        $this->assertSame(
            ['B01_C1_CLOSE_TO_HH20_FLOOR_NEG5'],
            array_column($analysis['candidate_design_allowed'], 'candidate_code')
        );
        $this->assertSame(498, $analysis['source_metrics']['days_covered']);
        foreach ($analysis['candidate_diagnostics'] as $candidate) {
            $this->assertTrue($candidate['decision_time_fields_only']);
            $this->assertFalse($candidate['future_return_as_runtime_input']);
            $this->assertFalse($candidate['oos_used']);
        }
    }

    public function test_catalog_and_draft_keep_the_p01_core_and_add_only_breakout_floor(): void
    {
        $rows = WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::rows();
        $this->assertCount(1, $rows);
        $this->assertSame(
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::ROW_CODE,
            $rows[0]['row_code']
        );

        $source = json_decode((string) file_get_contents(base_path(
            'storage/app/watchlist/backtest/ws-price-quality-p01-draft-catalog/'
            .'p01_c1_min_signal_price_50.json'
        )), true);
        $this->assertIsArray($source);
        $payload = (new WeeklySwingBreakoutIntegrityB01DraftCatalogService())
            ->buildCandidatePayload($source, $rows[0]);
        $validation = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue(
            $validation['valid'],
            json_encode($validation['errors'])
        );
        $selection = $validation['canonical_payload']['research_selection'];
        $this->assertSame(
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::RULE_CODE,
            $selection['rule_code']
        );
        $this->assertSame(
            -0.05,
            $selection['thresholds']['min_close_to_hh20_pct']
        );
        $this->assertSame(50, $selection['thresholds']['min_signal_close_price']);
        $this->assertSame(
            ['STRONG', 'MIXED'],
            $selection['thresholds']['allowed_regimes']
        );
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt(
            $validation['canonical_payload']
        );
        $this->assertSame(
            'WS_R02_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN_TIME',
            $runtime['backtest']['exit_model']
        );
    }

    public function test_runtime_and_persistence_contracts_are_oos_free(): void
    {
        $grouping = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'
        ));
        $draft = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingBreakoutIntegrityB01DraftCatalogService.php'
        ));

        $this->assertStringContainsString(
            'WATCHLIST_B01_BREAKOUT_INTEGRITY_FAIL',
            $grouping
        );
        $this->assertStringContainsString(
            "['min_close_to_hh20_pct']",
            $grouping
        );
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $draft);
        $this->assertStringContainsString("'oos_table_read' => false", $draft);
        $this->assertStringContainsString(
            "'rejected_candidates_not_persisted' =>",
            $draft
        );

        $official = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService.php'
        ));
        $this->assertSame(
            '2023-01-02',
            WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                ::CANONICAL_IS_FROM
        );
        $this->assertSame(
            '2025-05-21',
            WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                ::CANONICAL_IS_TO
        );
        $this->assertStringNotContainsString(
            "DB::table('watchlist_bt_oos_eval_ws')",
            $official
        );
        $this->assertStringContainsString(
            "'oos_table_read' => false",
            $official
        );
    }

    public function test_passing_is_identity_review_is_locked_and_oos_free(): void
    {
        $review = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingBreakoutIntegrityB01IsIdentityReviewService.php'
        ));
        $kernel = (string) file_get_contents(base_path(
            'app/Console/Kernel.php'
        ));
        $lock = (string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/_refs/'
            .'WS_BREAKOUT_INTEGRITY_B01_IS_IDENTITY_REVIEW_LOCK.md'
        ));

        $this->assertSame(
            220,
            WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                ::EXPECTED_IS_EVAL_ID
        );
        $this->assertSame(
            'adf7ec1ba705a4823f4c8590967ffba08fcbd5d8',
            WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                ::EXPECTED_SOURCE_ARTIFACT_HASH
        );
        $this->assertStringNotContainsString(
            "DB::table('watchlist_bt_oos_eval_ws')",
            $review
        );
        $this->assertStringContainsString(
            "'oos_table_read' => false",
            $review
        );
        $this->assertStringContainsString(
            'databaseManifest(',
            $review
        );
        $this->assertStringContainsString(
            'RunWeeklySwingBreakoutIntegrityB01IsIdentityReviewCommand::class',
            $kernel
        );
        $this->assertStringContainsString(
            'OOS_TABLE_READ=0',
            $lock
        );
    }

    public function test_single_official_oos_is_locked_to_verified_identity(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService.php'
        ));
        $repository = (string) file_get_contents(base_path(
            'app/Infrastructure/Persistence/Watchlist/'
            .'WatchlistBacktestOosEvaluationRepository.php'
        ));
        $kernel = (string) file_get_contents(base_path(
            'app/Console/Kernel.php'
        ));
        $lock = (string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/_refs/'
            .'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_LOCK.md'
        ));

        $this->assertSame(
            '2025-05-22',
            WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService::OOS_FROM
        );
        $this->assertSame(
            '2026-05-29',
            WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService::OOS_TO
        );
        $this->assertStringContainsString(
            'EXPECTED_IDENTITY_REVIEW_ARTIFACT_HASH',
            $service
        );
        $this->assertStringContainsString(
            "'retuning_performed' => false",
            $service
        );
        $this->assertStringContainsString(
            "'paramset_promoted' => false",
            $service
        );
        foreach ([
            'paramset_hash', 'eval_model_hash', 'implementation_hash',
            'is_evidence_manifest_hash', 'implementation_version',
        ] as $field) {
            $this->assertStringContainsString("'".$field."'", $repository);
        }
        $this->assertStringContainsString(
            'RunWeeklySwingBreakoutIntegrityB01OfficialOosCommand::class',
            $kernel
        );
        $this->assertStringContainsString('RETUNING_ALLOWED=0', $lock);
    }

    public function test_promotion_review_is_read_only_and_locks_exact_oos(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService.php'
        ));
        $kernel = (string) file_get_contents(base_path(
            'app/Console/Kernel.php'
        ));
        $lock = (string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/_refs/'
            .'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_READINESS_LOCK.md'
        ));

        $this->assertSame(
            1,
            WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
                ::EXPECTED_OOS_ID
        );
        $this->assertSame(
            '0be1ef09abfb4ba332dc3f0605af90a5d3a565df',
            WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
                ::EXPECTED_OOS_ARTIFACT_HASH
        );
        $this->assertStringNotContainsString(
            "->update(['status' => 'ACTIVE'",
            $service
        );
        $this->assertStringContainsString(
            "'promotion_executed' => false",
            $service
        );
        $this->assertStringContainsString(
            'RunWeeklySwingBreakoutIntegrityB01PromotionReadinessReviewCommand::class',
            $kernel
        );
        $this->assertStringContainsString(
            'Controlled runtime remains a',
            $lock
        );
    }

    public function test_active_shadow_is_locked_to_active_paramset_and_non_mutating(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingBreakoutIntegrityB01ActiveShadowService.php'
        ));
        $kernel = (string) file_get_contents(base_path(
            'app/Console/Kernel.php'
        ));
        $lock = (string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/_refs/'
            .'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_LOCK.md'
        ));

        $this->assertSame(
            '2026-07-28',
            WeeklySwingBreakoutIntegrityB01ActiveShadowService
                ::SHADOW_TRADE_DATE
        );
        $this->assertSame(
            'd71e7287f86bd3fcccf8db0ae01486fbaae0f4d7',
            WeeklySwingBreakoutIntegrityB01ActiveShadowService
                ::EXPECTED_PROMOTION_REVIEW_ARTIFACT_HASH
        );
        $this->assertStringContainsString(
            "->where('status', 'ACTIVE')",
            $service
        );
        $this->assertStringContainsString(
            'WeeklySwingParamsetRuntimeAdapter',
            $service
        );
        $this->assertStringContainsString(
            "'official_output_published' => false",
            $service
        );
        $this->assertStringContainsString(
            "'strategy_identity_changed' => false",
            $service
        );
        $this->assertDoesNotMatchRegularExpression(
            '/DB::table\\([^\\n]+\\)->(?:insert|update|delete|upsert)/',
            $service
        );
        $this->assertStringContainsString(
            'RunWeeklySwingBreakoutIntegrityB01ActiveShadowCommand::class',
            $kernel
        );
        $this->assertStringContainsString(
            'default_paramset_substitution_allowed=0',
            $lock
        );
        $this->assertStringContainsString(
            'Production readiness remains false',
            $lock
        );
    }
}
