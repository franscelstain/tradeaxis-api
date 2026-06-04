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
}
