<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-S075-R0144` — the registration boundary of the eligibility write, stated as a tripwire and not as a requirement.
 *
 * `EodArtifactRepository::replaceEligibility()` requires a blocked row's reason set to be a non-empty JSON LIST of non-blank
 * strings. It does NOT look the members up in the reason-code registry: that proof is carried by the producer
 * (`B19EligibilityReasonRegistrationTest`: every code the decision and build services can emit is a registered, active code)
 * and by the blocked row of a real run (`B19EligibilityExportRealRunProvenanceTest`). An arbitrary unregistered string handed
 * directly to the write API is therefore outside the current proof scope.
 *
 * This test records that limitation so it cannot be mistaken for enforcement. If the write path ever starts to check the
 * registry, this test fails and must be replaced by the positive guard together with the basis text of R0144.
 */
class B19EligibilityWriteRegistrationBoundaryTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow('2026-08-13 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_the_write_does_not_look_reason_codes_up_in_the_registry(): void
    {
        $row = [
            'trade_date' => '2026-08-12', 'ticker_id' => 1, 'listing_id' => 101, 'eligible' => 0, 'reason_code' => null,
            'universe_membership_state' => 'MEMBER', 'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN', 'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED', 'liquidity_state' => 'ACTIVE', 'temporal_status_state' => 'UNKNOWN', 'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'SOURCE_TRACEABLE', 'price_basis_state' => 'STRUCTURAL_ADJUSTED', 'contamination_state' => 'NO_CONTAMINATION_DETECTED',
            'indicator_state' => 'VALID', 'eligibility_reasons_json' => '["NOT_A_REGISTERED_REASON_CODE"]', 'run_id' => 12, 'publication_id' => 44,
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
        $this->assertFalse(DB::table('eod_reason_codes')->where('code', 'NOT_A_REGISTERED_REASON_CODE')->exists(), 'precondition: the code is not in the registry');

        (new EodArtifactRepository())->replaceEligibility('2026-08-12', 12, [$row], 44);

        $this->assertSame('["NOT_A_REGISTERED_REASON_CODE"]', DB::table('eod_eligibility')->value('eligibility_reasons_json'), 'known limitation: registry membership is not enforced at the write');
    }
}
