<?php

use App\Application\MarketData\Services\MarketDataReadinessService;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataReadinessServiceTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_readiness_returns_ready_only_for_current_sealed_success_readable_pass_pointer(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2);

        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertSame('READABLE_PUBLICATION_RESOLVED', $result['reason_code']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame(3, $result['run_id']);
        $this->assertSame('SUCCESS', $result['terminal_status']);
        $this->assertSame('READABLE', $result['publishability_state']);
        $this->assertSame('PASS', $result['coverage_gate_state']);
        $this->assertSame('SEALED', $result['seal_state']);
        $this->assertSame('RESOLVED_READABLE_CURRENT', $result['pointer_resolve_status']);
        $this->assertSame(1.0, $result['coverage_ratio']);
        $this->assertSame(2, $result['expected_count']);
        $this->assertSame(2, $result['available_count']);
        $this->assertSame(0, $result['missing_count']);
    }

    public function test_readiness_returns_reason_for_missing_pointer(): void
    {
        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['reason_code']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $result['pointer_resolve_status']);
    }

    public function test_readiness_returns_reason_for_unsealed_publication(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2, 1, [
            'seal_state' => 'UNSEALED',
        ]);

        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('PUBLICATION_NOT_SEALED', $result['reason_code']);
    }

    public function test_readiness_returns_reason_for_non_success_run(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2, 1, [
            'terminal_status' => 'HELD',
        ]);

        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('RUN_TERMINAL_STATUS_NOT_SUCCESS', $result['reason_code']);
    }

    public function test_readiness_returns_reason_for_non_readable_run(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2, 1, [
            'publishability_state' => 'NOT_READABLE',
        ]);

        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('RUN_PUBLISHABILITY_NOT_READABLE', $result['reason_code']);
    }

    public function test_readiness_returns_reason_for_coverage_fail(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2, 1, [
            'coverage_gate_state' => 'FAIL',
        ]);

        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('RUN_COVERAGE_GATE_NOT_PASS', $result['reason_code']);
    }
}
