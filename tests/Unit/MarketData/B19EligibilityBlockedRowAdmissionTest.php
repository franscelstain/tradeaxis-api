<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — what stands between the eligibility producer and `eligibility_export.csv` for the rows the export contract
 * speaks about (`MD-S075-R0144`, with the boundary cases of `R0134` and `R0138`).
 *
 * The export reports the persisted row. It can neither invent a reason nor refuse to describe what was stored, so the
 * locked rule "blocked rows must carry registered reason codes" can only hold for the artifact if the rows that get
 * persisted obey the snapshot rule that is already locked: "No `eligible=false` row may have an empty reason set"
 * (Eligibility_Partial_Data_Behavior_LOCKED). The producer obeys it by construction (`B19EligibilityReasonRegistrationTest`);
 * until now NOTHING in the persistence path enforced it. This guard proves the persistence-side enforcement:
 *
 *  - a blocked row without a usable reason set is refused at the eligibility write, before the stored rows are touched;
 *  - usable rows, and blocked rows with a real set, are still written;
 *  - a NULL reason set was already refused by the write completeness guard (`StageThreeWriteCompletenessGuardTest`);
 *  - a row without a listing identity cannot be part of a V2 semantic artifact: the semantic identity key refuses it.
 *
 * Rows that pre-date the write guard (legacy or bypassed) are still exported exactly as stored; that behaviour is pinned in
 * `B19EligibilityExportRowProvenanceTest` and is a faithful report of defective data, not conformance.
 */
class B19EligibilityBlockedRowAdmissionTest extends TestCase
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

    private function row(array $override = []): array
    {
        return array_merge([
            'trade_date' => '2026-08-12', 'ticker_id' => 1, 'listing_id' => 101, 'eligible' => 1, 'reason_code' => null,
            'universe_membership_state' => 'MEMBER', 'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN', 'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED', 'liquidity_state' => 'ACTIVE', 'temporal_status_state' => 'UNKNOWN', 'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'SOURCE_TRACEABLE', 'price_basis_state' => 'STRUCTURAL_ADJUSTED', 'contamination_state' => 'NO_CONTAMINATION_DETECTED',
            'indicator_state' => 'VALID', 'eligibility_reasons_json' => '[]', 'run_id' => 12, 'publication_id' => 44, 'created_at' => Carbon::now()->toDateTimeString(),
        ], $override);
    }

    private function write(array $rows): void
    {
        (new EodArtifactRepository())->replaceEligibility('2026-08-12', 12, $rows, 44);
    }

    private function seedExisting(): void
    {
        DB::table('eod_eligibility')->insert([$this->row(['ticker_id' => 9, 'listing_id' => 909])]);
    }

    /** @return array<string,array{0:array<string,mixed>}> */
    public static function defectiveBlockedRows(): array
    {
        return [
            'empty set' => [['eligible' => 0, 'reason_code' => null, 'eligibility_reasons_json' => '[]']],
            'empty set although a legacy reason is present' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '[]']],
            'not a JSON list' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => 'ELIG_MISSING_BAR']],
            'a JSON object' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '{"a":"ELIG_MISSING_BAR"}']],
            'a JSON object with one numeric key' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '{"0":"ELIG_MISSING_BAR"}']],
            'a JSON object with sequential numeric keys' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '{"0":"ELIG_MISSING_BAR","1":"ELIG_TRADING_SUSPENDED"}']],
            'an empty JSON object' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '{}']],
            'a JSON list of lists' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '[["ELIG_MISSING_BAR"]]']],
            'a blank member' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '[""]']],
            'a non-string member' => [['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '[7]']],
        ];
    }

    /**
     * `R0144`, `R0138`: the write refuses a blocked row that carries no usable reason set, before touching the stored rows.
     *
     * @dataProvider defectiveBlockedRows
     */
    public function test_a_blocked_row_without_a_usable_reason_set_is_refused_before_anything_is_replaced(array $override): void
    {
        $this->seedExisting();
        try {
            $this->write([$this->row(['ticker_id' => 1] + $override)]);
            $this->fail('the eligibility write accepted a blocked row without a usable reason set');
        } catch (LogicException $e) {
            $this->assertStringContainsString('ELIGIBILITY_WRITE_INCOMPLETE', $e->getMessage());
            $this->assertStringContainsString('eligibility_reasons_json', $e->getMessage());
        }
        $this->assertSame([9], DB::table('eod_eligibility')->pluck('ticker_id')->map(fn ($v) => (int) $v)->all(), 'the stored rows are untouched');
    }

    /**
     * Reproduction of the reviewer's finding on THIS runtime: json_decode(..., true) turns a JSON object with sequential numeric keys into a PHP
     * array that a sequential-key check cannot tell from a JSON list, which is why the root type must be read from a decode WITHOUT the associative flag.
     */
    public function test_the_runtime_decodes_a_numeric_key_object_like_a_list_when_asked_for_arrays(): void
    {
        $this->assertSame(array_keys(json_decode('["A","B"]', true)), array_keys(json_decode('{"0":"A","1":"B"}', true)));
        $this->assertTrue(is_array(json_decode('["A"]')));
        $this->assertFalse(is_array(json_decode('{"0":"A"}')), 'without the associative flag an object stays an object');
    }

    /** The guard is about blocked rows only: a mixed batch with one defective blocked row writes nothing. */
    public function test_one_defective_blocked_row_stops_the_whole_batch(): void
    {
        $this->seedExisting();
        $this->expectException(LogicException::class);
        try {
            $this->write([
                $this->row(['ticker_id' => 1]),
                $this->row(['ticker_id' => 2, 'listing_id' => 102, 'eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '["ELIG_MISSING_BAR"]']),
                $this->row(['ticker_id' => 3, 'listing_id' => 103, 'eligible' => 0, 'reason_code' => null, 'eligibility_reasons_json' => '[]']),
            ]);
        } finally {
            $this->assertSame([9], DB::table('eod_eligibility')->pluck('ticker_id')->map(fn ($v) => (int) $v)->all());
        }
    }

    /** `R0137`, `R0144`: usable rows (with or without an annotation set) and blocked rows with a real set are written as given. */
    public function test_usable_rows_and_blocked_rows_with_a_real_set_are_still_written(): void
    {
        $this->seedExisting();
        $this->write([
            $this->row(['ticker_id' => 1, 'listing_id' => 101, 'eligible' => 1, 'eligibility_reasons_json' => '[]']),
            $this->row(['ticker_id' => 2, 'listing_id' => 102, 'eligible' => 1, 'eligibility_reasons_json' => '["IND_ANNOTATION_ONLY"]']),
            $this->row(['ticker_id' => 3, 'listing_id' => 103, 'eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR', 'eligibility_reasons_json' => '["ELIG_MISSING_BAR"]']),
            $this->row(['ticker_id' => 4, 'listing_id' => 104, 'eligible' => 0, 'reason_code' => null, 'eligibility_reasons_json' => '["ELIG_TRADING_SUSPENDED"]']),
        ]);
        $this->assertSame([1, 2, 3, 4], DB::table('eod_eligibility')->orderBy('ticker_id')->pluck('ticker_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame('["ELIG_TRADING_SUSPENDED"]', DB::table('eod_eligibility')->where('ticker_id', 4)->value('eligibility_reasons_json'));
    }

    /** `R0138`: a NULL reason set is refused by the existing completeness guard — a NULL set is legacy-only, not producible. */
    public function test_a_null_reason_set_is_refused_at_the_write(): void
    {
        $this->seedExisting();
        $incoming = $this->row(['ticker_id' => 1]);
        $incoming['eligibility_reasons_json'] = null;
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ELIGIBILITY_WRITE_INCOMPLETE');
        $this->write([$incoming]);
    }

    /** @return array<string,array{0:mixed}> */
    public static function missingListings(): array
    {
        return ['null' => [null], 'zero' => [0], 'absent' => ['__absent__']];
    }

    /**
     * `R0134`: a V2 semantic artifact cannot be formed from a row without a listing identity. The identity key the semantic hash
     * navigates by refuses it, so a NULL listing id cannot belong to a V2 sealed publication; it can only be a legacy row,
     * which the export reports as NULL and never replaces with the ticker or the publication.
     *
     * @dataProvider missingListings
     *
     * @param mixed $listing
     */
    public function test_the_v2_semantic_identity_key_refuses_a_row_without_a_listing($listing): void
    {
        $service = (new ReflectionClass(ArtifactSemanticHashService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(ArtifactSemanticHashService::class, 'localIdentityKey');
        $method->setAccessible(true);
        $row = ['trade_date' => '2026-08-12', 'ticker_id' => 1];
        if ($listing !== '__absent__') {
            $row['listing_id'] = $listing;
        }
        try {
            $method->invoke($service, $row);
            $this->fail('a row without a listing id obtained a semantic identity key');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_LOCAL_NAVIGATION_KEY_MISSING', $e->getMessage());
        }
        $this->assertSame('2026-08-12|101', $method->invoke($service, ['trade_date' => '2026-08-12', 'listing_id' => 101, 'ticker_id' => 1]));
    }
}
