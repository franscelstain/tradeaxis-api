<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use PHPUnit\Framework\TestCase;

class CoverageGateEvaluatorTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    public function test_evaluator_returns_pass_when_available_matches_expected_universe()
    {
        $this->bindCoverageGateConfig();

        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-04-03')
            ->willReturn($this->buildUniverseRows(900));

        $artifacts->expects($this->once())
            ->method('loadCanonicalBarTickerIdsForTradeDate')
            ->with('2026-04-03', null)
            ->willReturn(range(1, 900));

        $service = new CoverageGateEvaluator($tickers, $artifacts);
        $result = $service->evaluate('2026-04-03');

        $this->assertSame(900, $result['expected_universe_count']);
        $this->assertSame(900, $result['available_eod_count']);
        $this->assertSame(0, $result['missing_eod_count']);
        $this->assertSame('PASS', $result['coverage_gate_status']);
        $this->assertSame('COVERAGE_THRESHOLD_MET', $result['reason_code']);
        $this->assertSame('MIN_RATIO', $result['coverage_threshold_mode']);
        $this->assertSame('coverage_gate_v1', $result['coverage_calibration_version']);
        $this->assertEquals(1.0, $result['coverage_ratio']);
        $this->assertArrayNotHasKey('publishability_state', $result);
        $this->assertArrayNotHasKey('terminal_status', $result);
    }

    public function test_evaluator_returns_fail_when_available_is_below_threshold()
    {
        $this->bindCoverageGateConfig(['min_ratio' => 0.95]);

        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-04-03')
            ->willReturn($this->buildUniverseRows(900));

        $artifacts->expects($this->once())
            ->method('loadCanonicalBarTickerIdsForTradeDate')
            ->with('2026-04-03', null)
            ->willReturn(range(1, 854));

        $service = new CoverageGateEvaluator($tickers, $artifacts);
        $result = $service->evaluate('2026-04-03');

        $this->assertSame(900, $result['expected_universe_count']);
        $this->assertSame(854, $result['available_eod_count']);
        $this->assertSame(46, $result['missing_eod_count']);
        $this->assertSame('FAIL', $result['coverage_gate_status']);
        $this->assertSame('COVERAGE_BELOW_THRESHOLD', $result['reason_code']);
        $this->assertSame(0.95, $result['coverage_threshold_value']);
        $this->assertSame('MIN_RATIO', $result['coverage_threshold_mode']);
        $this->assertEquals(854 / 900, $result['coverage_ratio']);
        $this->assertCount(25, $result['missing_ticker_ids']);
        $this->assertCount(25, $result['missing_ticker_codes']);
        $this->assertSame('TKR0855', $result['missing_ticker_codes'][0]);
    }

    public function test_evaluator_returns_blocked_when_expected_universe_is_zero()
    {
        $this->bindCoverageGateConfig();

        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-04-03')
            ->willReturn([]);

        $artifacts->expects($this->never())
            ->method('loadCanonicalBarTickerIdsForTradeDate');

        $service = new CoverageGateEvaluator($tickers, $artifacts);
        $result = $service->evaluate('2026-04-03');

        $this->assertSame(0, $result['expected_universe_count']);
        $this->assertSame(0, $result['available_eod_count']);
        $this->assertSame(0, $result['missing_eod_count']);
        $this->assertNull($result['coverage_ratio']);
        $this->assertSame('NOT_EVALUABLE', $result['coverage_gate_status']);
        $this->assertSame('COVERAGE_UNIVERSE_EMPTY', $result['reason_code']);
    }

    public function test_evaluator_outputs_threshold_mode_value_and_requested_publication_context()
    {
        $this->bindCoverageGateConfig([
            'min_ratio' => 0.975,
            'threshold_mode' => 'MIN_RATIO',
            'contract_version' => 'coverage_gate_v1_calibrated',
        ]);

        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-04-03')
            ->willReturn($this->buildUniverseRows(10));

        $artifacts->expects($this->once())
            ->method('loadCanonicalBarTickerIdsForTradeDate')
            ->with('2026-04-03', 77)
            ->willReturn(range(1, 10));

        $service = new CoverageGateEvaluator($tickers, $artifacts);
        $result = $service->evaluate('2026-04-03', 77);

        $this->assertSame(0.975, $result['coverage_threshold_value']);
        $this->assertSame('MIN_RATIO', $result['coverage_threshold_mode']);
        $this->assertSame('coverage_gate_v1_calibrated', $result['coverage_calibration_version']);
    }

    protected function bindCoverageGateConfig(array $coverageOverrides = []): void
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => [
                    'coverage_min' => 0.98,
                ],
                'coverage_gate' => array_merge([
                    'min_ratio' => 0.98,
                    'threshold_mode' => 'MIN_RATIO',
                    'contract_version' => 'coverage_gate_v1',
                    'missing_sample_limit' => 25,
                ], $coverageOverrides),
            ],
        ]);
    }

    protected function buildUniverseRows(int $count): array
    {
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'ticker_id' => $i,
                'ticker_code' => sprintf('TKR%04d', $i),
            ];
        }

        return $rows;
    }
}
