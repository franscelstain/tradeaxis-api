<?php

use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use Tests\Support\UsesMarketDataSqlite;

class EventRiskSourceRepositoryTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_resolves_exact_date_uma_without_carry_forward(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'UMA',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-19');

        $this->assertSame('UMA', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['is_uma']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:UMA', $context[1]['event_risk_reasons']);

        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([1], '2026-05-20'));
    }

    public function test_carries_suspended_until_unsuspended(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-20');

        $this->assertSame('SUSPENDED', $context[1]['trading_status_code']);
        $this->assertSame(1, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertSame([1], $repository->suspendedTickerIdsAsOf([1], '2026-05-20'));

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'event_type_code' => 'UNSUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-21');

        $this->assertSame('UNSUSPENDED', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(0, $context[1]['event_risk_flag']);
        $this->assertSame([], $repository->suspendedTickerIdsAsOf([1], '2026-05-22'));
        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([1], '2026-05-22'));
    }

    public function test_carries_special_monitoring_until_special_monitoring_end(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SPECIAL_MONITORING_START',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([2], '2026-05-20');

        $this->assertSame('SPECIAL_MONITORING_START', $context[2]['trading_status_code']);
        $this->assertSame(0, $context[2]['is_suspended']);
        $this->assertSame(0, $context[2]['is_uma']);
        $this->assertSame(1, $context[2]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SPECIAL_MONITORING_START', $context[2]['event_risk_reasons']);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-21',
            'event_type_code' => 'SPECIAL_MONITORING_END',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([2], '2026-05-21');

        $this->assertSame('SPECIAL_MONITORING_END', $context[2]['trading_status_code']);
        $this->assertSame(0, $context[2]['is_suspended']);
        $this->assertSame(0, $context[2]['is_uma']);
        $this->assertSame(0, $context[2]['event_risk_flag']);
        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([2], '2026-05-22'));
    }

    public function test_unsuspended_only_clears_suspension_not_special_monitoring(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SPECIAL_MONITORING_START',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-20',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'event_type_code' => 'UNSUSPENDED',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-22');

        $this->assertSame('SPECIAL_MONITORING_START', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(0, $context[1]['is_uma']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
    }

    public function test_projection_uses_single_primary_canonical_code_and_moves_composite_context_to_reasons(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 3,
            'ticker_code' => 'TEST',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SPECIAL_MONITORING_START',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 3,
            'ticker_code' => 'TEST',
            'trade_date' => '2026-05-20',
            'event_type_code' => 'UMA',
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([3], '2026-05-20');

        $this->assertSame('UMA', $context[3]['trading_status_code']);
        $this->assertSame(0, $context[3]['is_suspended']);
        $this->assertSame(1, $context[3]['is_uma']);
        $this->assertSame(1, $context[3]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SPECIAL_MONITORING_START', $context[3]['event_risk_reasons']);
        $this->assertStringContainsString('TRADING_STATUS:UMA', $context[3]['event_risk_reasons']);
        $this->assertStringNotContainsString(',', $context[3]['trading_status_code']);
    }

    public function test_suspension_observed_projects_as_suspended_risk_without_becoming_suspended_start(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 4,
            'ticker_code' => 'LONG',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'SUSPENSION_OBSERVED',
            'source_name' => 'idx_suspension_gt_6m',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([4], '2026-05-20');

        $this->assertSame('SUSPENSION_OBSERVED', $context[4]['trading_status_code']);
        $this->assertSame(1, $context[4]['is_suspended']);
        $this->assertSame(0, $context[4]['is_uma']);
        $this->assertSame(1, $context[4]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SUSPENSION_OBSERVED', $context[4]['event_risk_reasons']);
    }

    public function test_no_source_data_does_not_fabricate_active_or_unsuspended_projection(): void
    {
        $repository = new EventRiskSourceRepository();

        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([5], '2026-05-20'));
    }

    public function test_repository_rejects_unknown_event_type_code(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EventRiskSourceRepository())->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'event_type_code' => 'ACTIVE',
            'source_name' => 'idx_manual',
        ]);
    }

    /**
     * Ascending trading-day sequence ending on 2026-05-25, skipping weekends so the depth
     * assertions below exercise trading-day traversal rather than calendar subtraction.
     */
    private function tradingDates(): array
    {
        return [
            '2026-05-11', '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15',
            '2026-05-18', '2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22',
            '2026-05-25',
        ];
    }

    private function insertCorporateAction($tickerId, $actionDate, $actionType): void
    {
        (new EventRiskSourceRepository())->upsertCorporateAction([
            'ticker_id' => $tickerId,
            'ticker_code' => 'TEST',
            'action_date' => $actionDate,
            'action_type' => $actionType,
            'source_name' => 'idx_manual',
        ]);
    }

    public function test_contamination_depth_counts_trading_days_not_calendar_days(): void
    {
        $this->insertCorporateAction(10, '2026-05-20', 'STOCK_SPLIT');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([10], $this->tradingDates());

        $this->assertCount(1, $contamination[10]);
        $this->assertSame('STOCK_SPLIT', $contamination[10][0]['action_type_code']);
        // 2026-05-20 -> 21, 22, 25 = three trading days back, though five calendar days.
        $this->assertSame(3, $contamination[10][0]['depth']);
        $this->assertTrue($contamination[10][0]['breaks_price_continuity']);
        $this->assertTrue($contamination[10][0]['breaks_volume_continuity']);
        $this->assertFalse($contamination[10][0]['is_unmapped_type']);
    }

    public function test_action_on_requested_date_has_depth_zero(): void
    {
        $this->insertCorporateAction(11, '2026-05-25', 'BONUS_SHARE');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([11], $this->tradingDates());

        $this->assertSame(0, $contamination[11][0]['depth']);
    }

    /**
     * A non-trading action date takes effect on the first trading day on or after it.
     */
    public function test_action_on_non_trading_day_resolves_forward_to_next_trading_day(): void
    {
        // 2026-05-16 and 2026-05-17 are absent from the sequence; the next trading day is 05-18.
        $this->insertCorporateAction(12, '2026-05-16', 'STOCK_SPLIT');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([12], $this->tradingDates());

        $this->assertSame(5, $contamination[12][0]['depth'], '2026-05-18 is five trading days before 2026-05-25');
    }

    public function test_unmapped_action_type_is_treated_as_breaking_both_continuities(): void
    {
        $this->insertCorporateAction(13, '2026-05-20', 'SOMETHING_NEW_FROM_IDX');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([13], $this->tradingDates());

        $this->assertTrue($contamination[13][0]['is_unmapped_type']);
        $this->assertTrue($contamination[13][0]['breaks_price_continuity']);
        $this->assertTrue($contamination[13][0]['breaks_volume_continuity']);
    }

    public function test_non_breaking_action_types_are_omitted_entirely(): void
    {
        $this->insertCorporateAction(14, '2026-05-20', 'TICKER_CODE_CHANGE');
        $this->insertCorporateAction(14, '2026-05-21', 'COMPANY_NAME_CHANGE');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([14], $this->tradingDates());

        $this->assertSame([], $contamination, 'identity-only actions cannot contaminate anything');
    }

    /**
     * Dilution issues new shares denominated in the existing unit, so historical price and
     * volume stay directly comparable. Only unit redefinition breaks the arithmetic.
     */
    public function test_dilution_action_types_do_not_contaminate_anything(): void
    {
        $repository = new EventRiskSourceRepository();

        foreach ([
            'PRIVATE_PLACEMENT',
            'NON_PREEMPTIVE_RIGHTS_ISSUE',
            'WARRANT',
            'WARRANT_EXERCISE',
            'MANDATORY_CONVERTIBLE_BOND',
            'ESOP_MSOP',
        ] as $index => $actionType) {
            $tickerId = 200 + $index;
            $this->insertCorporateAction($tickerId, '2026-05-20', $actionType);

            $contamination = $repository->resolveCorporateActionContaminationForTickerIds(
                [$tickerId],
                $this->tradingDates()
            );

            $this->assertSame([], $contamination, $actionType.' is dilution, not redenomination');
        }
    }

    public function test_lifecycle_action_types_do_not_contaminate_anything(): void
    {
        $repository = new EventRiskSourceRepository();

        foreach (['IPO', 'DELISTING', 'PARTIAL_DELISTING', 'PARTIAL_RELISTING', 'CAPITAL_DEFICIENCY'] as $index => $actionType) {
            $tickerId = 300 + $index;
            $this->insertCorporateAction($tickerId, '2026-05-20', $actionType);

            $contamination = $repository->resolveCorporateActionContaminationForTickerIds(
                [$tickerId],
                $this->tradingDates()
            );

            $this->assertSame([], $contamination, $actionType.' has no continuity to break');
        }
    }

    /**
     * A rights issue rescales the price series through the ex-rights adjustment, but no
     * holding is multiplied automatically, so the volume series keeps its unit.
     */
    public function test_rights_issue_breaks_price_continuity_but_not_volume(): void
    {
        $this->insertCorporateAction(15, '2026-05-20', 'RIGHTS_ISSUE');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([15], $this->tradingDates());

        $this->assertTrue($contamination[15][0]['breaks_price_continuity']);
        $this->assertFalse($contamination[15][0]['breaks_volume_continuity']);
    }

    public function test_unit_redefining_action_types_break_both_continuities(): void
    {
        $repository = new EventRiskSourceRepository();

        foreach (['STOCK_SPLIT', 'REVERSE_STOCK_SPLIT', 'BONUS_SHARE', 'STOCK_DIVIDEND', 'MERGER'] as $index => $actionType) {
            $tickerId = 400 + $index;
            $this->insertCorporateAction($tickerId, '2026-05-20', $actionType);

            $contamination = $repository->resolveCorporateActionContaminationForTickerIds(
                [$tickerId],
                $this->tradingDates()
            );

            $this->assertTrue($contamination[$tickerId][0]['breaks_price_continuity'], $actionType);
            $this->assertTrue($contamination[$tickerId][0]['breaks_volume_continuity'], $actionType);
        }
    }

    public function test_action_outside_the_window_is_not_returned(): void
    {
        $this->insertCorporateAction(16, '2026-04-30', 'STOCK_SPLIT');
        $this->insertCorporateAction(16, '2026-06-01', 'STOCK_SPLIT');

        $contamination = (new EventRiskSourceRepository())
            ->resolveCorporateActionContaminationForTickerIds([16], $this->tradingDates());

        $this->assertSame([], $contamination, 'past-window and future-dated actions must both be excluded');
    }
}
