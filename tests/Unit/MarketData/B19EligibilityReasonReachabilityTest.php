<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — `MD-S075-R0144` ("blocked rows must carry registered reason codes"): WHERE the registered-reason boundary is, and
 * whether an unregistered reason can reach an admissible publication and its `eligibility_export.csv`.
 *
 * Findings, each proved here by execution or by a census of `app/`:
 *
 *  1. DOWNSTREAM LAYERS DO NOT CHECK. `replaceEligibility()` accepts an unregistered member inside a valid list, the evidence
 *     export of a SEALED publication then carries it unchanged. Nothing between the write and the CSV consults the registry
 *     (the semantic hash covers `eligibility_reasons_json` as an opaque value). So the registered-reason boundary is the
 *     PRODUCER, not persistence, sealing or export.
 *  2. THE PRODUCER BOUNDARY IS CLOSED. The only production caller of `replaceEligibility()` is `EodEligibilityBuildService`;
 *     the only place that assigns `eligibility_reasons_json` is the same service; the members of its set come from exactly two
 *     expressions - the literal `ELIG_TRADING_SUSPENDED` and the reason of `EligibilityDecisionService::decide()` - and every
 *     `ELIG_*` code in those producers is a registered, active code (`B19EligibilityReasonRegistrationTest`). Files that write the
 *     eligibility tables are a closed set, and the others only copy rows (snapshot/promote) or update market-structure states.
 *  3. THE CLOSURE IS ENFORCED BY CENSUS. A new caller, a new place that builds a reason set, or a new writer of the eligibility
 *     tables fails the census below and must be reviewed against R0144 before it is accepted. That is a tripwire on the boundary,
 *     not a registry lookup.
 *
 * What is NOT claimed: that persistence rejects an unregistered code (it does not, see the tripwire), or that a future caller
 * would be stopped at run time. Whether the write or the seal should consult the registry - and which registry, the live
 * `eod_reason_codes` table or the snapshot bound to the publication's config - is an authority choice recorded as an optional
 * hardening question, not needed for the current system to satisfy R0144.
 */
class B19EligibilityReasonReachabilityTest extends TestCase
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

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** @return array<string,string> relative path => contents, every PHP file under app/ */
    private function appSources(): array
    {
        $out = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/app', FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                $out[str_replace('\\', '/', substr($file->getPathname(), strlen($this->root()) + 1))] = (string) file_get_contents($file->getPathname());
            }
        }
        ksort($out);

        return $out;
    }

    // ------------------------------------------------------------------ 1. downstream does not check

    public function test_an_unregistered_reason_written_through_the_public_method_reaches_the_export_of_a_sealed_publication(): void
    {
        $d = '2026-08-12';
        $this->assertFalse(DB::table('eod_reason_codes')->where('code', 'NOT_A_REGISTERED_REASON_CODE')->exists(), 'precondition');
        DB::table('eod_runs')->insert([
            'run_id' => 12, 'trade_date_requested' => $d, 'trade_date_effective' => $d, 'lifecycle_state' => 'COMPLETED', 'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE', 'source' => 'manual_file', 'publication_id' => 44, 'publication_version' => 1, 'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE', 'coverage_gate_state' => 'PASS', 'is_current_publication' => 0, 'sealed_at' => $d.' 17:20:00',
            'started_at' => $d.' 17:00:00', 'created_at' => $d.' 17:00:00', 'updated_at' => $d.' 17:20:00',
        ]);
        DB::table('eod_publications')->insert([
            'publication_id' => 44, 'trade_date' => $d, 'run_id' => 12, 'publication_version' => 1, 'is_current' => 0, 'seal_state' => 'SEALED',
            'sealed_at' => $d.' 17:20:00', 'created_at' => $d.' 17:20:00', 'updated_at' => $d.' 17:20:00',
        ]);
        $row = [
            'trade_date' => $d, 'ticker_id' => 1, 'listing_id' => 101, 'eligible' => 0, 'reason_code' => null,
            'universe_membership_state' => 'MEMBER', 'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN', 'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED', 'liquidity_state' => 'ACTIVE', 'temporal_status_state' => 'UNKNOWN', 'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'SOURCE_TRACEABLE', 'price_basis_state' => 'STRUCTURAL_ADJUSTED', 'contamination_state' => 'NO_CONTAMINATION_DETECTED',
            'indicator_state' => 'VALID', 'eligibility_reasons_json' => '["NOT_A_REGISTERED_REASON_CODE"]', 'run_id' => 12, 'publication_id' => 44,
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        (new EodArtifactRepository())->replaceEligibility($d, 12, [$row], 44, true);   // history table, as a candidate publication is written

        $exported = (new EodEvidenceRepository())->exportEligibilityRowsForEvidencePublication($d, 44, false);
        $this->assertCount(1, $exported);
        $this->assertSame('["NOT_A_REGISTERED_REASON_CODE"]', $exported[0]['reason_codes'], 'no layer between the write and the export consults the registry: the producer is the boundary');
    }

    // ------------------------------------------------------------------ 2./3. the producer boundary is closed (census)

    public function test_replace_eligibility_has_exactly_one_production_caller(): void
    {
        $callers = [];
        foreach ($this->appSources() as $path => $src) {
            if (preg_match('/->\s*replaceEligibility\s*\(/', $src) === 1) {
                $callers[] = $path;
            }
        }
        $this->assertSame(['app/Application/MarketData/Services/EodEligibilityBuildService.php'], $callers, 'a new caller of replaceEligibility() must be reviewed against MD-S075-R0144');
    }

    public function test_only_the_build_service_builds_an_eligibility_reason_set(): void
    {
        $builders = [];
        foreach ($this->appSources() as $path => $src) {
            if (preg_match("/'eligibility_reasons_json'\s*=>/", $src) === 1) {
                $builders[] = $path;
            }
        }
        $this->assertSame(['app/Application/MarketData/Services/EodEligibilityBuildService.php'], $builders, 'a new place that builds a reason set must be reviewed against MD-S075-R0144');
    }

    public function test_the_members_of_the_set_come_from_exactly_the_suspension_literal_and_the_decision_reason(): void
    {
        $whole = (string) file_get_contents($this->root().'/app/Application/MarketData/Services/EodEligibilityBuildService.php');
        $from = (int) strpos($whole, 'foreach ($universe as $ticker)');
        $to = (int) strpos($whole, '$this->artifacts->replaceEligibility');
        $this->assertTrue($from > 0 && $to > $from, 'the row loop precedes the write');
        $src = substr($whole, $from, $to - $from);
        preg_match_all('/\$reasons\[\]\s*=\s*([^;]+);/', $src, $m);
        $this->assertSame(["'ELIG_TRADING_SUSPENDED'", "\$decision['reason_code']"], array_map('trim', $m[1]), 'the members of the set');
        preg_match_all('/\$reasons\s*=\s*([^;]+);/', $src, $init);
        $this->assertSame(['[]'], array_map('trim', $init[1]), 'the set starts empty and is only appended to by the two expressions above');
        $this->assertMatchesRegularExpression('/\$decision\s*=\s*\$this->decisions->decide\(/', $src, 'the decision reason is the one EligibilityDecisionService returns');
    }

    public function test_the_files_that_write_the_eligibility_tables_are_a_closed_set(): void
    {
        $writers = [];
        foreach ($this->appSources() as $path => $src) {
            if (preg_match("/table\(\s*'eod_eligibility(?:_history)?'\s*\)[^;]*?->\s*(?:insert|insertOrIgnore|insertGetId|upsert|update)\s*\(/s", $src) === 1
                || preg_match('/(?:insert\s+(?:ignore\s+)?into|update|replace\s+into)\s+eod_eligibility/i', $src) === 1) {
                $writers[] = $path;
            }
        }
        // EodArtifactRepository writes through $table / table() variables (replace, snapshot, promote, market-structure bind) and is the only
        // file allowed to; a literal-table writer elsewhere is a new route into the eligibility rows.
        $writers = array_values(array_diff($writers, ['app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php']));
        $this->assertSame([], $writers, 'a new writer of eod_eligibility / eod_eligibility_history must be reviewed against MD-S075-R0144');
    }

    public function test_the_repository_writes_to_the_eligibility_tables_only_through_known_paths(): void
    {
        $src = (string) file_get_contents($this->root().'/app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $this->assertSame(1, preg_match_all('/public function replaceEligibility\(/', $src));
        // the copy paths read rows that were persisted by replaceEligibility() and do not touch the reason set
        foreach (['snapshotPublicationFromCurrentTables', 'promotePublicationHistoryToCurrent'] as $method) {
            $this->assertStringNotContainsString("'eligibility_reasons_json' =>", $src);
            $this->assertMatchesRegularExpression('/function '.$method.'\(/', $src);
        }
        // the market-structure bind updates states and revision ids only
        $this->assertMatchesRegularExpression('/function bindCandidateEligibilityMarketStructure\(.*?\)\s*:\s*void\s*\{.*?->update\(\$payload\)/s', $src);
        $gov = (string) file_get_contents($this->root().'/app/Application/MarketData/Services/PublicationGovernanceBindingService.php');
        $this->assertStringNotContainsString("'eligibility_reasons_json'", $gov, 'the market-structure payload does not carry a reason set');
        $this->assertStringNotContainsString("'eligible' =>", substr($gov, (int) strpos($gov, 'bindCandidateEligibilityMarketStructure')), 'nor the usability');
    }
}
