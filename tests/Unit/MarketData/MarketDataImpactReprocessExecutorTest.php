<?php

use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Application\MarketData\Services\EodIndicatorsComputeService;
use App\Application\MarketData\Services\MarketDataImpactReprocessExecutor;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MarketDataImpactReprocessExecutorTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_changed_out_of_order_bar_executes_indicator_and_eligibility_for_downstream_dates(): void
    {
        $runs = m::mock(EodRunRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);
        $originRun = $this->makeRunModel(11, '2026-05-01');
        $downstreamRun = $this->makeRunModel(12, '2026-05-08');

        $publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->twice()
            ->andReturn(null);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($downstreamRun);
        $runs->shouldReceive('appendEvent')->once();

        $indicators->shouldReceive('compute')
            ->once()
            ->with($originRun, '2026-05-01', false);
        $indicators->shouldReceive('compute')
            ->once()
            ->with($downstreamRun, '2026-05-08', false);
        $eligibility->shouldReceive('build')
            ->once()
            ->with($originRun, '2026-05-01', false);
        $eligibility->shouldReceive('build')
            ->once()
            ->with($downstreamRun, '2026-05-08', false);

        $summary = (new MarketDataImpactReprocessExecutor($runs, $publications, $indicators, $eligibility))->execute(
            $originRun,
            'api',
            ['changed_bar_count' => 1],
            [
                'affected_trade_dates' => ['2026-05-01', '2026-05-08'],
                'affected_trade_date_count' => 2,
            ],
            ['publication_impact_state' => 'NOOP'],
            ['source_mode' => 'api', 'requested_date' => '2026-05-01']
        );

        $this->assertSame('EXECUTED', $summary['indicator_reprocess_execution_summary']['execution_state']);
        $this->assertSame(2, $summary['indicator_reprocess_execution_summary']['reprocessed_trade_date_count']);
        $this->assertSame('EXECUTED', $summary['eligibility_reprocess_execution_summary']['execution_state']);
        $this->assertSame('PENDING_PROMOTE', $summary['publication_reprocess_summary']['execution_state']);
        $this->assertSame(['2026-05-01', '2026-05-08'], $summary['publication_reprocess_summary']['candidate_trade_dates']);
    }

    public function test_unchanged_upsert_does_not_recompute_indicators_or_eligibility(): void
    {
        $runs = m::mock(EodRunRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);

        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $runs->shouldNotReceive('findLatestForRequestedDate');
        $runs->shouldNotReceive('appendEvent');
        $indicators->shouldNotReceive('compute');
        $eligibility->shouldNotReceive('build');

        $summary = (new MarketDataImpactReprocessExecutor($runs, $publications, $indicators, $eligibility))->execute(
            $this->makeRunModel(11, '2026-05-01'),
            'manual_file',
            ['changed_bar_count' => 0],
            ['affected_trade_dates' => []],
            ['publication_impact_state' => 'NOOP']
        );

        $this->assertSame('NOOP', $summary['indicator_reprocess_execution_summary']['execution_state']);
        $this->assertSame('NOOP_UNCHANGED_BARS', $summary['indicator_reprocess_execution_summary']['blocked_reason_code']);
        $this->assertSame('NOOP', $summary['eligibility_reprocess_execution_summary']['execution_state']);
    }

    public function test_readable_affected_date_becomes_correction_publication_candidate_without_silent_recompute(): void
    {
        $runs = m::mock(EodRunRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);

        $publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->never();
        $runs->shouldReceive('appendEvent')->once();
        $runs->shouldNotReceive('findLatestForRequestedDate');
        $indicators->shouldNotReceive('compute');
        $eligibility->shouldNotReceive('build');

        $summary = (new MarketDataImpactReprocessExecutor($runs, $publications, $indicators, $eligibility))->execute(
            $this->makeRunModel(11, '2026-05-01'),
            'api',
            ['changed_bar_count' => 1],
            ['affected_trade_dates' => ['2026-05-08']],
            [
                'publication_impact_state' => 'REQUIRES_REPUBLICATION',
                'impacted_readable_trade_dates' => ['2026-05-08'],
            ]
        );

        $this->assertSame('BLOCKED', $summary['indicator_reprocess_execution_summary']['execution_state']);
        $this->assertSame(['2026-05-08'], $summary['indicator_reprocess_execution_summary']['blocked_trade_dates']);
        $this->assertSame('PENDING_PROMOTE', $summary['publication_reprocess_summary']['execution_state']);
        $this->assertSame(['2026-05-08'], $summary['publication_reprocess_summary']['candidate_trade_dates']);
        $this->assertSame(['2026-05-08'], $summary['publication_reprocess_summary']['readable_correction_candidate_trade_dates']);
        $this->assertSame([], $summary['publication_reprocess_summary']['blocked_trade_dates']);
        $this->assertSame('PENDING_READABLE_CORRECTION', $summary['publication_reprocess_summary']['republication_mode']);
    }

    public function test_mixed_readable_and_non_readable_affected_dates_remain_publication_candidates(): void
    {
        $runs = m::mock(EodRunRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);
        $originRun = $this->makeRunModel(11, '2026-05-01');
        $downstreamRun = $this->makeRunModel(12, '2026-05-08');

        $publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->once()
            ->with('2026-05-08')
            ->andReturn(null);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($downstreamRun);
        $runs->shouldReceive('appendEvent')->once();
        $indicators->shouldReceive('compute')
            ->once()
            ->with($downstreamRun, '2026-05-08', false);
        $eligibility->shouldReceive('build')
            ->once()
            ->with($downstreamRun, '2026-05-08', false);

        $summary = (new MarketDataImpactReprocessExecutor($runs, $publications, $indicators, $eligibility))->execute(
            $originRun,
            'api',
            ['changed_bar_count' => 1],
            ['affected_trade_dates' => ['2026-05-08', '2026-05-09']],
            [
                'publication_impact_state' => 'REQUIRES_REPUBLICATION',
                'impacted_readable_trade_dates' => ['2026-05-09'],
            ]
        );

        $this->assertSame('BLOCKED', $summary['indicator_reprocess_execution_summary']['execution_state']);
        $this->assertSame(['2026-05-08'], $summary['indicator_reprocess_execution_summary']['reprocessed_trade_dates']);
        $this->assertSame(['2026-05-09'], $summary['indicator_reprocess_execution_summary']['blocked_trade_dates']);
        $this->assertSame('PENDING_PROMOTE', $summary['publication_reprocess_summary']['execution_state']);
        $this->assertSame(['2026-05-08', '2026-05-09'], $summary['publication_reprocess_summary']['candidate_trade_dates']);
        $this->assertSame(['2026-05-09'], $summary['publication_reprocess_summary']['readable_correction_candidate_trade_dates']);
        $this->assertSame('PENDING_MIXED_IMPACT_REPUBLICATION', $summary['publication_reprocess_summary']['republication_mode']);
    }

    private function makeRunModel(int $runId, string $date): EodRun
    {
        $run = new EodRun();
        $run->run_id = $runId;
        $run->trade_date_requested = $date;

        return $run;
    }
}
