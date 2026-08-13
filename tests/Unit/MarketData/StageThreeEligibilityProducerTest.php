<?php

use App\Application\MarketData\Services\EligibilityDecisionService;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;

/**
 * Pure producer oracle for stage 3. Persistence guards are proved separately against SQLite.
 */
class StageThreeEligibilityProducerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_producer_writes_explicit_facts_without_changing_the_decision(): void
    {
        $tickers = Mockery::mock(TickerMasterRepository::class);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->andReturn([
            ['ticker_id' => 1, 'listing_id' => 101],
            ['ticker_id' => 2, 'listing_id' => 102],
        ]);

        $capturedRows = null;
        $artifacts = Mockery::mock(EodArtifactRepository::class);
        $artifacts->shouldReceive('loadDormantTickerIds')->once()->andReturn([]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->andReturn([
            1 => ['quality_state' => 'VALIDATED'],
        ]);
        $artifacts->shouldReceive('loadIndicatorsForTradeDate')->once()->andReturn([
            1 => ['event_risk_flag' => 0],
        ]);
        $artifacts->shouldReceive('replaceEligibility')->once()->withArgs(function ($date, $runId, $rows) use (&$capturedRows) {
            $capturedRows = $rows;

            return $date === '2026-08-12' && $runId === 12;
        });

        $publications = Mockery::mock(EodPublicationRepository::class);
        $publications->shouldReceive('getOrCreateCandidatePublication')->once()->andReturn((object) [
            'publication_id' => 44,
            'publication_version' => 1,
            'supersedes_publication_id' => null,
            'previous_publication_id' => null,
            'replaced_publication_id' => null,
        ]);

        $decisions = Mockery::mock(EligibilityDecisionService::class);
        $decisions->shouldReceive('decide')->twice()->andReturn(
            ['eligible' => 1, 'reason_code' => null],
            ['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR']
        );

        $run = new EodRun();
        $run->run_id = 12;

        $service = new EodEligibilityBuildService($tickers, $artifacts, $publications, $decisions);
        $service->build($run, '2026-08-12');

        $this->assertCount(2, $capturedRows);
        foreach ($capturedRows as $row) {
            foreach (EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS as $field) {
                $this->assertArrayHasKey($field, $row);
                $this->assertNotNull($row[$field]);
            }
        }

        $this->assertSame('VALIDATED', $capturedRows[0]['canonical_quality_state']);
        $this->assertSame('CLEAR', $capturedRows[0]['event_risk_state']);
        $this->assertSame('[]', $capturedRows[0]['eligibility_reasons_json']);
        $this->assertSame(1, $capturedRows[0]['eligible']);

        $this->assertSame('UNAVAILABLE', $capturedRows[1]['canonical_quality_state']);
        $this->assertSame('UNKNOWN', $capturedRows[1]['event_risk_state']);
        $this->assertSame('["ELIG_MISSING_BAR"]', $capturedRows[1]['eligibility_reasons_json']);
        $this->assertSame(0, $capturedRows[1]['eligible']);
    }
}
