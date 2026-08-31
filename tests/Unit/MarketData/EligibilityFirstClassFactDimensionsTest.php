<?php

use App\Application\MarketData\Services\EligibilityDecisionService;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;

/**
 * `MD-B16-A001` guard for the required fact dimensions of
 * `EOD_Eligibility_Snapshot_Contract_LOCKED.md`.
 *
 * The contract enumerates the dimensions every row must persist separately and states the
 * consequence of not doing so without hedging:
 *
 *   > Absence of the first-class facts is a **defect against this contract**, never a licence to
 *   > overload `reason_code`.
 *
 * Seven dimensions were persisted. Source and provenance state, analytical price basis,
 * contamination state, and indicator validity with warm-up and nullability were not — although
 * every input was already in memory when the row was built. A consumer could not tell a traceable
 * observation from an untraceable one, a contaminated window from a clean one, or a warm-up null
 * from an invalid row, without opening the bar and indicator tables. The acceptance criterion rules
 * that out: the consumer must inspect each dimension "without ... reading internal tables".
 */
class EligibilityFirstClassFactDimensionsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @param  array<int,array<string,mixed>>  $bars
     * @param  array<int,array<string,mixed>>  $indicators
     * @return array<int,array<string,mixed>>
     */
    private function buildRows(array $universe, array $bars, array $delivered, array $indicators, array $decisions): array
    {
        $tickers = Mockery::mock(TickerMasterRepository::class);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->andReturn($universe);

        $captured = null;
        $artifacts = Mockery::mock(EodArtifactRepository::class);
        $artifacts->shouldReceive('loadDormantTickerIds')->once()->andReturn([]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->andReturn($bars);
        $artifacts->shouldReceive('loadDeliveredObservationTickerIdsForTradeDate')->once()->andReturn($delivered);
        $artifacts->shouldReceive('loadIndicatorsForTradeDate')->once()->andReturn($indicators);
        $artifacts->shouldReceive('replaceEligibility')->once()->withArgs(function ($date, $runId, $rows) use (&$captured) {
            $captured = $rows;

            return true;
        });

        $publications = Mockery::mock(EodPublicationRepository::class);
        $publications->shouldReceive('getOrCreateCandidatePublication')->once()->andReturn((object) [
            'publication_id' => 44, 'publication_version' => 1, 'supersedes_publication_id' => null,
            'previous_publication_id' => null, 'replaced_publication_id' => null,
        ]);

        $decisionService = Mockery::mock(EligibilityDecisionService::class);
        $decisionService->shouldReceive('decide')->times(count($universe))->andReturn(...$decisions);

        $run = new EodRun();
        $run->run_id = 12;

        (new EodEligibilityBuildService($tickers, $artifacts, $publications, $decisionService))
            ->build($run, '2026-08-12');

        return $captured;
    }

    /**
     * MD-S027-R0009, R0012, R0013: the row carries the dimensions rather than pointing at the
     * tables they came from.
     */
    public function test_every_row_persists_the_four_previously_absent_dimensions(): void
    {
        $rows = $this->buildRows(
            [['ticker_id' => 1, 'listing_id' => 101]],
            [1 => ['quality_state' => 'VALIDATED', 'source_observation_id' => 501]],
            [1],
            [1 => ['event_risk_flag' => 0, 'is_valid' => 1, 'price_product_code' => 'STRUCTURAL_ADJUSTED',
                'corporate_action_window_reasons' => null, 'null_reasons_json' => null]],
            [['eligible' => 1, 'reason_code' => null]]
        );

        foreach (['source_provenance_state', 'price_basis_state', 'contamination_state', 'indicator_state'] as $field) {
            $this->assertArrayHasKey($field, $rows[0], $field.' is not persisted on the eligibility row');
            $this->assertNotNull($rows[0][$field], $field.' is present but empty');
        }

        $this->assertSame('SOURCE_TRACEABLE', $rows[0]['source_provenance_state']);
        $this->assertSame('STRUCTURAL_ADJUSTED', $rows[0]['price_basis_state']);
        $this->assertSame('NO_CONTAMINATION_DETECTED', $rows[0]['contamination_state']);
        $this->assertSame('VALID', $rows[0]['indicator_state']);
    }

    /**
     * The dimensions are facts about this row, not constants. Each must move with its input, or the
     * column would be present and say nothing.
     */
    public function test_each_dimension_follows_its_own_input(): void
    {
        $rows = $this->buildRows(
            [
                ['ticker_id' => 1, 'listing_id' => 101],
                ['ticker_id' => 2, 'listing_id' => 102],
                ['ticker_id' => 3, 'listing_id' => 103],
            ],
            [
                // Delivered but with no source observation: untraceable, which is a different fact
                // from an absent bar and must not be reported as the same thing.
                1 => ['quality_state' => 'VALIDATED', 'source_observation_id' => null],
                2 => ['quality_state' => 'VALIDATED', 'source_observation_id' => 777],
            ],
            [1, 2],
            [
                1 => ['event_risk_flag' => 0, 'is_valid' => 1, 'price_product_code' => 'RAW',
                    'corporate_action_window_reasons' => 'STOCK_SPLIT@2026-08-01', 'null_reasons_json' => null],
                2 => ['event_risk_flag' => 0, 'is_valid' => 0, 'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY',
                    'price_product_code' => '', 'corporate_action_window_reasons' => null,
                    'null_reasons_json' => '{"ma50":["IND_INSUFFICIENT_HISTORY"]}'],
            ],
            [
                ['eligible' => 1, 'reason_code' => null],
                ['eligible' => 0, 'reason_code' => 'ELIG_INSUFFICIENT_HISTORY'],
                ['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR'],
            ]
        );

        $this->assertSame('UNTRACEABLE', $rows[0]['source_provenance_state'],
            'a delivered bar with no source observation must not read as traceable');
        $this->assertSame('CONTAMINATED', $rows[0]['contamination_state']);
        $this->assertSame('RAW', $rows[0]['price_basis_state']);

        $this->assertSame('SOURCE_TRACEABLE', $rows[1]['source_provenance_state']);
        $this->assertSame('BASIS_UNRECORDED', $rows[1]['price_basis_state'],
            'an indicator row that never recorded its price product must not read as a known basis');
        $this->assertSame('INVALID', $rows[1]['indicator_state']);

        // No bar and no indicator at all: absence fails safe and is named, not defaulted.
        $this->assertSame('NO_OBSERVATION', $rows[2]['source_provenance_state']);
        $this->assertSame('UNKNOWN', $rows[2]['price_basis_state']);
        $this->assertSame('UNKNOWN', $rows[2]['contamination_state']);
        $this->assertSame('NO_INDICATOR_ROW', $rows[2]['indicator_state']);
    }

    /**
     * MD-S031-R0007 and MD-S027-R0031: a warm-up null on an otherwise valid row is visible. The
     * contract is explicit that explanation is not reserved for blocked rows — a consumer must be
     * able to see why a usable row is usable.
     */
    public function test_a_valid_row_with_field_nulls_is_distinguishable_from_a_fully_populated_one(): void
    {
        $rows = $this->buildRows(
            [['ticker_id' => 1, 'listing_id' => 101], ['ticker_id' => 2, 'listing_id' => 102]],
            [
                1 => ['quality_state' => 'VALIDATED', 'source_observation_id' => 501],
                2 => ['quality_state' => 'VALIDATED', 'source_observation_id' => 502],
            ],
            [1, 2],
            [
                1 => ['event_risk_flag' => 0, 'is_valid' => 1, 'price_product_code' => 'STRUCTURAL_ADJUSTED',
                    'corporate_action_window_reasons' => null,
                    'null_reasons_json' => '{"ma50":["IND_INSUFFICIENT_HISTORY"]}'],
                2 => ['event_risk_flag' => 0, 'is_valid' => 1, 'price_product_code' => 'STRUCTURAL_ADJUSTED',
                    'corporate_action_window_reasons' => null, 'null_reasons_json' => null],
            ],
            [['eligible' => 1, 'reason_code' => null], ['eligible' => 1, 'reason_code' => null]]
        );

        $this->assertSame('VALID_WITH_FIELD_NULLS', $rows[0]['indicator_state']);
        $this->assertSame('VALID', $rows[1]['indicator_state']);
        $this->assertNotSame(
            $rows[0]['indicator_state'],
            $rows[1]['indicator_state'],
            'a warm-up null and a fully populated row must not carry the same indicator state'
        );

        // Both are usable. The dimension explains the usable row, which is the point of R0031.
        $this->assertSame(1, $rows[0]['eligible']);
        $this->assertSame(1, $rows[1]['eligible']);
    }

    /**
     * MD-S027-R0025: no dimension may be reconstructed from a single overloaded `reason_code`. A
     * dimension packed into a delimited string is a smaller version of the same mistake, and the
     * deterministic hash serializer refuses a delimiter inside a hashed field.
     */
    public function test_no_dimension_is_a_delimited_composite(): void
    {
        $rows = $this->buildRows(
            [['ticker_id' => 1, 'listing_id' => 101]],
            [1 => ['quality_state' => 'VALIDATED', 'source_observation_id' => 501]],
            [1],
            [1 => ['event_risk_flag' => 1, 'is_valid' => 0, 'invalid_reason_code' => 'IND_MISSING_DEPENDENCY_BAR',
                'price_product_code' => 'STRUCTURAL_ADJUSTED',
                'corporate_action_window_reasons' => 'STOCK_SPLIT@2026-08-01', 'null_reasons_json' => null]],
            [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR']]
        );

        foreach (['source_provenance_state', 'price_basis_state', 'contamination_state', 'indicator_state'] as $field) {
            foreach (['|', ';', ','] as $delimiter) {
                $this->assertStringNotContainsString(
                    $delimiter,
                    (string) $rows[0][$field],
                    $field.' packs more than one fact into one value, which is the overloading the contract forbids'
                );
            }
        }

        // The invalid reason still reaches the consumer, through the ordered reason set that owns it.
        $this->assertStringContainsString('ELIG_MISSING_BAR', (string) $rows[0]['eligibility_reasons_json']);
    }

    /** Every new dimension is a protected write field, so it cannot be dropped on the way to history. */
    public function test_the_new_dimensions_are_protected_against_being_dropped_in_snapshot_or_promote(): void
    {
        foreach (['source_provenance_state', 'price_basis_state', 'contamination_state', 'indicator_state'] as $field) {
            $this->assertContains(
                $field,
                EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS,
                $field.' is written but not protected, so a snapshot or promote could silently drop it'
            );
        }
    }
}
