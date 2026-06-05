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

    public function test_resolves_source_backed_event_risk_context_without_faking_absence(): void
    {
        $repository = new EventRiskSourceRepository();

        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([1], '2026-05-19'));

        $repository->upsertCorporateAction([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'action_date' => '2026-05-19',
            'action_type' => 'DIVIDEND',
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'status_code' => 'UMA',
            'is_suspended' => 0,
            'is_uma' => 1,
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-19',
            'status_code' => 'ACTIVE',
            'is_suspended' => 0,
            'is_uma' => 0,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1, 2, 3], '2026-05-19');

        $this->assertArrayHasKey(1, $context);
        $this->assertSame(1, $context[1]['corporate_action_flag']);
        $this->assertSame('DIVIDEND', $context[1]['corporate_action_types']);
        $this->assertSame('UMA', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['is_uma']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertStringContainsString('CORPORATE_ACTION:DIVIDEND', $context[1]['event_risk_reasons']);
        $this->assertStringContainsString('UMA', $context[1]['event_risk_reasons']);

        $this->assertArrayHasKey(2, $context);
        $this->assertNull($context[2]['corporate_action_flag']);
        $this->assertSame('ACTIVE', $context[2]['trading_status_code']);
        $this->assertSame(0, $context[2]['is_suspended']);
        $this->assertSame(0, $context[2]['is_uma']);
        $this->assertSame(0, $context[2]['event_risk_flag']);
        $this->assertNull($context[2]['event_risk_reasons']);

        $this->assertArrayNotHasKey(3, $context);
    }

    public function test_carries_suspension_until_active_and_allows_resuspension(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'status_code' => 'SUSPENDED',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $this->assertSame([], $repository->resolveEventRiskContextForTickerIds([1], '2026-05-18'));

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-20');

        $this->assertArrayHasKey(1, $context);
        $this->assertSame('SUSPENDED', $context[1]['trading_status_code']);
        $this->assertSame(1, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertStringContainsString('SUSPENDED', $context[1]['event_risk_reasons']);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'status_code' => 'ACTIVE',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-22');

        $this->assertSame('ACTIVE', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(0, $context[1]['is_uma']);
        $this->assertSame(0, $context[1]['event_risk_flag']);
        $this->assertNull($context[1]['event_risk_reasons']);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-23',
            'status_code' => 'SUSPENDED',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-24');

        $this->assertSame('SUSPENDED', $context[1]['trading_status_code']);
        $this->assertSame(1, $context[1]['is_suspended']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
    }

    public function test_suspended_ticker_ids_as_of_returns_only_active_suspension_state(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'status_code' => 'SUSPENDED',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-19',
            'status_code' => 'UMA',
            'is_suspended' => 0,
            'is_uma' => 1,
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 3,
            'ticker_code' => 'BMRI',
            'trade_date' => '2026-05-19',
            'status_code' => 'SPECIAL_MONITORING',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $this->assertSame([1], $repository->suspendedTickerIdsAsOf([1, 2, 3], '2026-05-20'));

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'status_code' => 'ACTIVE',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $this->assertSame([], $repository->suspendedTickerIdsAsOf([1, 2, 3], '2026-05-22'));
    }

    public function test_carries_special_monitoring_until_exit(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-19',
            'status_code' => 'SPECIAL_MONITORING',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([2], '2026-05-20');

        $this->assertArrayHasKey(2, $context);
        $this->assertSame('SPECIAL_MONITORING', $context[2]['trading_status_code']);
        $this->assertNull($context[2]['is_suspended']);
        $this->assertSame(1, $context[2]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SPECIAL_MONITORING', $context[2]['event_risk_reasons']);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 2,
            'ticker_code' => 'BBRI',
            'trade_date' => '2026-05-21',
            'status_code' => 'SPECIAL_MONITORING_EXIT',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([2], '2026-05-22');

        $this->assertSame('SPECIAL_MONITORING_EXIT', $context[2]['trading_status_code']);
        $this->assertNull($context[2]['is_suspended']);
        $this->assertNull($context[2]['is_uma']);
        $this->assertSame(0, $context[2]['event_risk_flag']);
        $this->assertNull($context[2]['event_risk_reasons']);
    }

    public function test_active_clears_suspension_without_clearing_special_monitoring(): void
    {
        $repository = new EventRiskSourceRepository();

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-19',
            'status_code' => 'SPECIAL_MONITORING',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-20',
            'status_code' => 'SUSPENDED',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-21',
            'status_code' => 'ACTIVE',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-22');

        $this->assertArrayHasKey(1, $context);
        $this->assertSame('ACTIVE,SPECIAL_MONITORING', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['is_suspended']);
        $this->assertSame(0, $context[1]['is_uma']);
        $this->assertSame(1, $context[1]['event_risk_flag']);
        $this->assertStringContainsString('TRADING_STATUS:SPECIAL_MONITORING', $context[1]['event_risk_reasons']);
        $this->assertStringNotContainsString('SUSPENDED', $context[1]['event_risk_reasons']);

        $repository->upsertTradingStatusEvent([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-05-23',
            'status_code' => 'SPECIAL_MONITORING_EXIT',
            'is_suspended' => null,
            'is_uma' => null,
            'source_name' => 'idx_manual',
        ]);

        $context = $repository->resolveEventRiskContextForTickerIds([1], '2026-05-24');

        $this->assertSame('SPECIAL_MONITORING_EXIT', $context[1]['trading_status_code']);
        $this->assertSame(0, $context[1]['event_risk_flag']);
        $this->assertNull($context[1]['event_risk_reasons']);
    }
}
