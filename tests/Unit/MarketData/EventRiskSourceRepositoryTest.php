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
}
