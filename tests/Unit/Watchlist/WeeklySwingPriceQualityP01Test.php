<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestNewStrategyR02RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestPriceQualityP01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestPriceQualityP01RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestStrategyService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;
use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01IdentityRepairDraftService;
use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01DraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01OfficialIsEvidenceService;
use TestCase;

class WeeklySwingPriceQualityP01Test extends TestCase
{
    public function test_catalog_contains_only_two_diagnostic_authorized_thresholds(): void
    {
        $rows = WatchlistBacktestPriceQualityP01ParamGridCatalog::rows();

        $this->assertCount(2, $rows);
        $this->assertSame([
            WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE,
            WatchlistBacktestPriceQualityP01ParamGridCatalog::C2_ROW_CODE,
        ], array_column($rows, 'row_code'));
        $this->assertCount(2, array_unique(array_column($rows, 'row_hash')));
        $this->assertCount(1, array_unique(array_column($rows, 'catalog_hash')));
        $this->assertSame(
            WatchlistBacktestPriceQualityP01ParamGridCatalog::hash(),
            $rows[0]['catalog_hash']
        );
        $this->assertSame(
            50,
            WatchlistBacktestPriceQualityP01ParamGridCatalog
                ::minimumSignalClosePriceForRow($rows[0]['row_code'])
        );
        $this->assertSame(
            100,
            WatchlistBacktestPriceQualityP01ParamGridCatalog
                ::minimumSignalClosePriceForRow($rows[1]['row_code'])
        );
        $this->assertFalse(
            WatchlistBacktestPriceQualityP01ParamGridCatalog
                ::isKnownRow('P01_C3_MIN_SIGNAL_PRICE_200')
        );
    }

    public function test_draft_builder_produces_valid_distinct_price_floor_paramsets(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'storage/app/watchlist/backtest/ws-tail-risk-s01-draft-catalog/'
            .'s01_h1_ihsg_non_weak_guard.json'
        )), true);
        $this->assertIsArray($source);

        $service = new WeeklySwingPriceQualityP01DraftCatalogService();
        $hashes = [];
        foreach (WatchlistBacktestPriceQualityP01ParamGridCatalog::rows() as $row) {
            $payload = $service->buildCandidatePayload($source, $row);
            $validation = (new WeeklySwingParamsetValidator())->validate($payload);
            $this->assertTrue($validation['valid'], json_encode($validation['errors']));
            $selection = $validation['canonical_payload']['research_selection'];
            $this->assertSame(
                WatchlistBacktestPriceQualityP01ParamGridCatalog::RULE_CODE,
                $selection['rule_code']
            );
            $this->assertSame(
                WatchlistBacktestPriceQualityP01ParamGridCatalog
                    ::minimumSignalClosePriceForRow($row['row_code']),
                $selection['thresholds']['min_signal_close_price']
            );
            $this->assertSame(['STRONG', 'MIXED'], $selection['thresholds']['allowed_regimes']);
            $this->assertTrue($selection['signal_date_only']);
            $this->assertFalse($selection['oos_used']);
            $expectedExecution =
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog
                    ::researchExecution();
            foreach ($expectedExecution as $key => $expected) {
                $this->assertArrayHasKey(
                    $key,
                    $validation['canonical_payload']['research_execution']
                );
                $this->assertSame(
                    $expected,
                    $validation['canonical_payload']['research_execution'][$key]
                );
            }
            $hashes[] = $validation['canonical_hash'];
        }
        $this->assertCount(2, array_unique($hashes));
    }

    public function test_runtime_keeps_fixed_sequential_execution_and_selection_contract(): void
    {
        $payload = json_decode((string) file_get_contents(base_path(
            'storage/app/watchlist/backtest/ws-tail-risk-s01-draft-catalog/'
            .'s01_h1_ihsg_non_weak_guard.json'
        )), true);
        $payload['paramset_code'] =
            WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE;
        $payload['research_selection'] =
            WatchlistBacktestPriceQualityP01ParamGridCatalog::researchSelectionForRow(
                WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE
            );
        $payload['research_execution'] =
            WatchlistBacktestPriceQualityP01ParamGridCatalog::researchExecution();

        $validation = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue($validation['valid'], json_encode($validation['errors']));
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt(
            $validation['canonical_payload']
        );

        $this->assertSame(
            50,
            $runtime['research_selection']['thresholds']['min_signal_close_price']
        );
        $this->assertSame(
            'WS_R02_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN_TIME',
            $runtime['backtest']['exit_model']
        );
        $this->assertTrue($runtime['research_execution']['fixed_before_entry']);
        $this->assertFalse($runtime['research_execution']['oos_used']);
    }

    public function test_runtime_filter_is_fail_closed_and_has_no_oos_or_gap_input(): void
    {
        $grouping = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'
        ));
        $draft = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingPriceQualityP01DraftCatalogService.php'
        ));

        $this->assertStringContainsString(
            'WATCHLIST_P01_MIN_SIGNAL_PRICE_QUALITY_FAIL',
            $grouping
        );
        $this->assertStringContainsString(
            "['min_signal_close_price']",
            $grouping
        );
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $draft);
        $this->assertStringContainsString(
            "'entry_gap_as_runtime_input_used' => false",
            $draft
        );
        $this->assertStringContainsString(
            "'rejected_candidate_not_persisted' =>",
            $draft
        );
    }

    public function test_official_is_contract_keeps_oos_unread_and_forbidden(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingPriceQualityP01OfficialIsEvidenceService.php'
        ));
        $command = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/'
            .'RunWeeklySwingPriceQualityP01OfficialIsCommand.php'
        ));

        $this->assertSame(
            '2023-01-02',
            WeeklySwingPriceQualityP01OfficialIsEvidenceService::CANONICAL_IS_FROM
        );
        $this->assertSame(
            '2025-05-21',
            WeeklySwingPriceQualityP01OfficialIsEvidenceService::CANONICAL_IS_TO
        );
        $this->assertStringNotContainsString(
            "DB::table('watchlist_bt_oos_eval_ws')",
            $service
        );
        $this->assertStringContainsString("'oos_table_read' => false", $service);
        $this->assertStringContainsString(
            'WATCHLIST_P01_MIN_SIGNAL_PRICE_QUALITY_FAIL',
            (string) file_get_contents(base_path(
                'app/Application/Watchlist/Services/'
                .'WatchlistPlanGroupingService.php'
            ))
        );
        $this->assertStringContainsString(
            'watchlist:weekly-swing-price-quality-p01-official-is',
            $command
        );
        $this->assertStringNotContainsString('{--oos', $command);
    }

    public function test_single_remediation_keeps_c1_selection_and_adds_only_loss_exit(): void
    {
        $this->assertCount(
            1,
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog::rows()
        );
        $selection =
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchSelection();
        $execution =
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchExecution();

        $this->assertSame(
            WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE,
            $selection['hypothesis_code']
        );
        $this->assertSame(50, $selection['thresholds']['min_signal_close_price']);
        $this->assertSame(-0.01, $execution['loss_close_threshold_pct']);
        $this->assertSame([1, 2, 3], $execution['loss_signal_day_offsets']);
        $this->assertSame(
            'NEXT_TRADING_DAY_OPEN',
            $execution['loss_signal_exit']
        );
        $this->assertTrue($execution['fixed_before_entry']);
        $this->assertFalse($execution['future_derived_route_used']);
        $this->assertFalse($execution['oos_used']);

        $payload = json_decode((string) file_get_contents(base_path(
            'storage/app/watchlist/backtest/'
            .'ws-price-quality-p01-draft-catalog/'
            .'p01_c1_min_signal_price_50.json'
        )), true);
        $payload['paramset_code'] =
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::ROW_CODE;
        $payload['research_selection'] = $selection;
        $payload['research_execution'] = $execution;
        $validation = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue($validation['valid'], json_encode($validation['errors']));
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt(
            $validation['canonical_payload']
        );
        $this->assertSame(
            'WS_S01M1_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEG1_NEXT_OPEN_TIME',
            $runtime['backtest']['exit_model']
        );
        $this->assertSame(
            'ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            WatchlistBacktestStrategyService::canonicalEvalModel($runtime)
        );
    }

    public function test_identity_repair_is_semantics_preserving_and_oos_free(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/'
            .'WeeklySwingPriceQualityP01IdentityRepairDraftService.php'
        ));

        $this->assertSame(
            218,
            WeeklySwingPriceQualityP01IdentityRepairDraftService::SOURCE_EVAL_ID
        );
        $this->assertSame(
            'ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            WeeklySwingPriceQualityP01IdentityRepairDraftService
                ::EXPECTED_EVAL_MODEL
        );
        $this->assertStringContainsString(
            "'strategy_semantics_changed' => false",
            $service
        );
        $this->assertStringContainsString(
            "'second_remediation_created' => false",
            $service
        );
        $this->assertStringNotContainsString(
            "DB::table('watchlist_bt_oos_eval_ws')",
            $service
        );
        $this->assertStringContainsString("'oos_table_read' => false", $service);
    }
}
