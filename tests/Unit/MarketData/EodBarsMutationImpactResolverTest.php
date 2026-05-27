<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\EodBarsMutationImpactResolver;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class EodBarsMutationImpactResolverTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        m::close();

        parent::tearDown();
    }

    public function test_unchanged_upsert_is_noop_for_indicator_and_publication_impact(): void
    {
        $this->bindMarketDataConfig();

        $calendar = m::mock(MarketCalendarRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);
        $publications = m::mock(EodPublicationRepository::class);

        $calendar->shouldNotReceive('tradingDatesBetween');
        $artifacts->shouldNotReceive('loadAvailableBarTradeDatesOnOrAfter');
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');

        $resolver = new EodBarsMutationImpactResolver($calendar, $artifacts, $publications);
        $impact = $resolver->resolve([
            'changed_bar_count' => 0,
            'inserted_bar_count' => 0,
            'updated_bar_count' => 0,
            'unchanged_bar_count' => 1,
            'changed_ticker_ids' => [],
            'changed_trade_dates' => [],
        ], '2026-05-01');

        $this->assertSame(0, $impact['bar_mutation_summary']['changed_bar_count']);
        $this->assertSame('NOOP_UNCHANGED_BARS', $impact['indicator_impact_summary']['indicator_reprocess_state']);
        $this->assertSame('NOOP', $impact['publication_impact_summary']['publication_impact_state']);
    }

    public function test_historical_changed_bar_resolves_downstream_trading_dates_with_indicator_horizon(): void
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'indicators' => [
                    'dv_window_days' => 20,
                    'atr_window_days' => 14,
                    'vol_ratio_lookback_days' => 20,
                    'roc_lookback_days' => 20,
                    'hh_window_days' => 20,
                ],
            ],
        ]);

        $calendar = m::mock(MarketCalendarRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $tradingDates = [];
        for ($day = 1; $day <= 8; $day++) {
            $tradingDates[] = sprintf('2026-05-%02d', $day);
        }

        $artifacts->shouldReceive('loadAvailableBarTradeDatesOnOrAfter')
            ->once()
            ->with('2026-05-01')
            ->andReturn($tradingDates);
        $calendar->shouldReceive('tradingDatesBetween')
            ->once()
            ->with('2026-05-01', '2026-05-08')
            ->andReturn($tradingDates);
        $publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->times(count($tradingDates))
            ->andReturn(null);

        $resolver = new EodBarsMutationImpactResolver($calendar, $artifacts, $publications);
        $impact = $resolver->resolve([
            'changed_bar_count' => 1,
            'inserted_bar_count' => 0,
            'updated_bar_count' => 1,
            'unchanged_bar_count' => 0,
            'changed_ticker_ids' => [101],
            'changed_trade_dates' => ['2026-05-01'],
        ], '2026-05-01');

        $this->assertSame(50, $impact['indicator_impact_summary']['max_dependency_trading_days']);
        $this->assertSame(8, $impact['indicator_impact_summary']['affected_trade_date_count']);
        $this->assertSame('2026-05-01', $impact['indicator_impact_summary']['affected_start_date']);
        $this->assertSame('2026-05-08', $impact['indicator_impact_summary']['affected_end_date']);
        $this->assertSame('REPROCESS_REQUIRED_WITH_DOWNSTREAM_IMPACT', $impact['indicator_impact_summary']['indicator_reprocess_state']);
        $this->assertSame('NOOP', $impact['publication_impact_summary']['publication_impact_state']);
    }

    public function test_readable_affected_publication_requires_republication_not_silent_update(): void
    {
        $this->bindMarketDataConfig();

        $calendar = m::mock(MarketCalendarRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);
        $publications = m::mock(EodPublicationRepository::class);

        $artifacts->shouldReceive('loadAvailableBarTradeDatesOnOrAfter')
            ->once()
            ->andReturn(['2026-05-01', '2026-05-04']);
        $calendar->shouldReceive('tradingDatesBetween')
            ->once()
            ->andReturn(['2026-05-01', '2026-05-04']);
        $publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->with('2026-05-01')
            ->once()
            ->andReturn(null);
        $publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->with('2026-05-04')
            ->once()
            ->andReturn((object) ['publication_id' => 44]);

        $resolver = new EodBarsMutationImpactResolver($calendar, $artifacts, $publications);
        $impact = $resolver->resolve([
            'changed_bar_count' => 1,
            'updated_bar_count' => 1,
            'changed_ticker_ids' => [101],
            'changed_trade_dates' => ['2026-05-01'],
        ], '2026-05-01');

        $this->assertTrue($impact['publication_impact_summary']['readable_publication_impacted']);
        $this->assertTrue($impact['publication_impact_summary']['republication_required']);
        $this->assertSame('REQUIRES_REPUBLICATION', $impact['publication_impact_summary']['publication_impact_state']);
        $this->assertSame('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $impact['publication_impact_summary']['reason_code']);
    }
}
