<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Stage 3 freezes the four defective populations without repairing historical rows.
 *
 * Each required field has its own negative oracle. The copy/restore oracle proves that a field
 * cannot bypass the guard through publication lifecycle paths, and the producer oracle proves
 * eligibility writes explicit facts without turning event or liquidity observations into policy.
 */
class StageThreeWriteCompletenessGuardTest extends TestCase
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

    /**
     * @dataProvider canonicalBarRequiredFields
     */
    public function test_each_canonical_bar_lineage_and_product_field_is_required(string $missingField): void
    {
        $existing = $this->completeBar();
        DB::table('eod_bars')->insert([$existing]);

        $incoming = $this->completeBar(['close' => 101]);
        unset($incoming[$missingField]);

        try {
            (new EodArtifactRepository())->replaceBars('2026-08-12', 44, 12, [$incoming], []);
            $this->fail('the canonical bar write must reject a missing '.$missingField);
        } catch (LogicException $e) {
            $this->assertStringContainsString('CANONICAL_BAR_WRITE_INCOMPLETE', $e->getMessage());
            $this->assertStringContainsString($missingField, $e->getMessage());
        }

        $this->assertSame('100', $this->normalizedNumber(DB::table('eod_bars')->value('close')));
    }

    public function canonicalBarRequiredFields(): array
    {
        return array_map(function (string $field): array {
            return [$field];
        }, EodArtifactRepository::REQUIRED_CANONICAL_BAR_WRITE_FIELDS);
    }

    /**
     * @dataProvider coverageRequiredFields
     */
    public function test_each_coverage_evidence_field_is_required(string $missingField): void
    {
        $run = $this->runFixture();
        $telemetry = $this->completeCoverageTelemetry();
        unset($telemetry[$missingField]);

        try {
            (new EodRunRepository())->updateTelemetry($run, $telemetry);
            $this->fail('the coverage write must reject a missing '.$missingField);
        } catch (LogicException $e) {
            $this->assertStringContainsString('COVERAGE_EVIDENCE_WRITE_INCOMPLETE', $e->getMessage());
            $this->assertStringContainsString($missingField, $e->getMessage());
        }

        $stored = EodRun::query()->findOrFail($run->run_id);
        foreach (EodRunRepository::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS as $field) {
            $this->assertNull($stored->{$field});
        }
    }

    public function coverageRequiredFields(): array
    {
        return array_map(function (string $field): array {
            return [$field];
        }, EodRunRepository::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS);
    }

    public function test_measured_zero_is_valid_coverage_evidence_and_partial_later_updates_do_not_erase_it(): void
    {
        $run = $this->runFixture();
        $repository = new EodRunRepository();
        $stored = $repository->updateTelemetry($run, $this->completeCoverageTelemetry());

        foreach (EodRunRepository::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS as $field) {
            $this->assertSame(0, (int) $stored->{$field});
        }

        $stored = $repository->updateTelemetry($stored, ['coverage_gate_state' => 'NOT_EVALUABLE']);
        foreach (EodRunRepository::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS as $field) {
            $this->assertSame(0, (int) $stored->{$field});
        }
    }

    /**
     * @dataProvider eligibilityRequiredFields
     */
    public function test_each_eligibility_fact_field_is_required(string $missingField): void
    {
        $existing = $this->completeEligibility();
        DB::table('eod_eligibility')->insert([$existing]);

        $incoming = $this->completeEligibility(['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR']);
        unset($incoming[$missingField]);

        try {
            (new EodArtifactRepository())->replaceEligibility('2026-08-12', 12, [$incoming], 44);
            $this->fail('the eligibility write must reject a missing '.$missingField);
        } catch (LogicException $e) {
            $this->assertStringContainsString('ELIGIBILITY_WRITE_INCOMPLETE', $e->getMessage());
            $this->assertStringContainsString($missingField, $e->getMessage());
        }

        $this->assertSame(1, (int) DB::table('eod_eligibility')->value('eligible'));
    }

    public function eligibilityRequiredFields(): array
    {
        return array_map(function (string $field): array {
            return [$field];
        }, EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS);
    }

    public function test_snapshot_and_promote_preserve_every_protected_bar_and_eligibility_field(): void
    {
        DB::table('eod_bars')->insert([$this->completeBar()]);
        DB::table('eod_eligibility')->insert([$this->completeEligibility()]);

        $repository = new EodArtifactRepository();
        $repository->snapshotPublicationFromCurrentTables('2026-08-12', 44, 12);

        $historyBar = (array) DB::table('eod_bars_history')->first();
        $historyEligibility = (array) DB::table('eod_eligibility_history')->first();
        $this->assertProtectedValuesEqual($this->completeBar(), $historyBar, EodArtifactRepository::REQUIRED_CANONICAL_BAR_WRITE_FIELDS);
        $this->assertProtectedValuesEqual($this->completeEligibility(), $historyEligibility, EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS);

        DB::table('eod_bars')->delete();
        DB::table('eod_eligibility')->delete();
        $repository->promotePublicationHistoryToCurrent('2026-08-12', 44, 13);

        $promotedBar = (array) DB::table('eod_bars')->first();
        $promotedEligibility = (array) DB::table('eod_eligibility')->first();
        $this->assertProtectedValuesEqual($historyBar, $promotedBar, EodArtifactRepository::REQUIRED_CANONICAL_BAR_WRITE_FIELDS);
        $this->assertProtectedValuesEqual($historyEligibility, $promotedEligibility, EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS);
    }

    public function test_partial_upsert_rejects_incomplete_lineage_before_touching_the_existing_row(): void
    {
        DB::table('eod_bars')->insert([$this->completeBar()]);
        $incoming = $this->completeBar(['close' => 200]);
        unset($incoming['price_product_code']);

        try {
            (new EodArtifactRepository())->upsertBarsPartial('2026-08-12', 44, 12, [$incoming]);
            $this->fail('partial upsert must enforce the same canonical write boundary');
        } catch (LogicException $e) {
            $this->assertStringContainsString('CANONICAL_BAR_WRITE_INCOMPLETE', $e->getMessage());
        }

        $this->assertSame('100', $this->normalizedNumber(DB::table('eod_bars')->value('close')));
    }

    public function test_current_to_history_copy_rejects_incomplete_legacy_source(): void
    {
        $source = $this->completeBar();
        unset($source['source_observation_id']);
        DB::table('eod_bars')->insert([$source]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CANONICAL_BAR_WRITE_INCOMPLETE');

        try {
            (new EodArtifactRepository())->ensureBarsHistoryFromCurrentTradeDate('2026-08-12', 44, 12);
        } finally {
            $this->assertSame(0, DB::table('eod_bars_history')->count());
        }
    }

    public function test_history_copy_rejects_incomplete_source_before_deleting_the_target(): void
    {
        $source = $this->completeBar(['publication_id' => 43]);
        unset($source['quality_state']);
        DB::table('eod_bars_history')->insert([$source]);
        DB::table('eod_bars_history')->insert([
            $this->completeBar(['publication_id' => 44, 'close' => 222]),
        ]);

        try {
            (new EodArtifactRepository())->replaceBarsHistoryFromPublication('2026-08-12', 43, 44, 12);
            $this->fail('history copy must reject incomplete source evidence');
        } catch (LogicException $e) {
            $this->assertStringContainsString('CANONICAL_BAR_WRITE_INCOMPLETE', $e->getMessage());
        }

        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', 44)->count());
        $this->assertSame('222', $this->normalizedNumber(
            DB::table('eod_bars_history')->where('publication_id', 44)->value('close')
        ));
    }

    public function test_snapshot_rolls_back_bar_copy_when_eligibility_facts_are_incomplete(): void
    {
        DB::table('eod_bars')->insert([$this->completeBar()]);
        $eligibility = $this->completeEligibility();
        unset($eligibility['event_risk_state']);
        DB::table('eod_eligibility')->insert([$eligibility]);

        try {
            (new EodArtifactRepository())->snapshotPublicationFromCurrentTables('2026-08-12', 44, 12);
            $this->fail('snapshot must reject incomplete eligibility facts');
        } catch (LogicException $e) {
            $this->assertStringContainsString('ELIGIBILITY_WRITE_INCOMPLETE', $e->getMessage());
        }

        $this->assertSame(0, DB::table('eod_bars_history')->count());
        $this->assertSame(0, DB::table('eod_eligibility_history')->count());
    }

    public function test_promote_rolls_back_all_current_changes_when_history_is_incomplete(): void
    {
        DB::table('eod_bars')->insert([$this->completeBar(['close' => 111])]);
        DB::table('eod_eligibility')->insert([$this->completeEligibility(['eligible' => 1])]);
        DB::table('eod_bars_history')->insert([$this->completeBar(['close' => 222])]);
        $eligibility = $this->completeEligibility(['eligible' => 0]);
        unset($eligibility['eligibility_reasons_json']);
        DB::table('eod_eligibility_history')->insert([$eligibility]);

        try {
            (new EodArtifactRepository())->promotePublicationHistoryToCurrent('2026-08-12', 44, 13);
            $this->fail('promote must reject incomplete history');
        } catch (LogicException $e) {
            $this->assertStringContainsString('ELIGIBILITY_WRITE_INCOMPLETE', $e->getMessage());
        }

        $this->assertSame('111', $this->normalizedNumber(DB::table('eod_bars')->value('close')));
        $this->assertSame(1, (int) DB::table('eod_eligibility')->value('eligible'));
    }

    public function test_every_mutating_repository_path_invokes_the_relevant_guard(): void
    {
        foreach ([
            'replaceBars',
            'upsertBarsPartial',
            'ensureBarsHistoryFromCurrentTradeDate',
            'replaceBarsHistoryFromPublication',
            'snapshotPublicationFromCurrentTables',
            'promotePublicationHistoryToCurrent',
        ] as $method) {
            $this->assertStringContainsString('assertCompleteBarRows', $this->methodSource(EodArtifactRepository::class, $method), $method);
        }

        foreach ([
            'replaceEligibility',
            'snapshotPublicationFromCurrentTables',
            'promotePublicationHistoryToCurrent',
        ] as $method) {
            $this->assertStringContainsString('assertCompleteEligibilityRows', $this->methodSource(EodArtifactRepository::class, $method), $method);
        }

        $this->assertStringContainsString(
            'assertCompleteCoverageTelemetry',
            $this->methodSource(EodRunRepository::class, 'updateTelemetry')
        );
    }

    private function completeBar(array $override = []): array
    {
        return array_merge([
            'trade_date' => '2026-08-12',
            'ticker_id' => 1,
            'listing_id' => 101,
            'source_observation_id' => 501,
            'open' => 100,
            'high' => 100,
            'low' => 100,
            'close' => 100,
            'volume' => 0,
            'adj_close' => 100,
            'source' => 'API_FREE',
            'canonicalization_version' => 'eod_canonical_v1',
            'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED',
            'run_id' => 12,
            'publication_id' => 44,
            'created_at' => Carbon::now()->toDateTimeString(),
        ], $override);
    }

    private function completeEligibility(array $override = []): array
    {
        return array_merge([
            'trade_date' => '2026-08-12',
            'ticker_id' => 1,
            'listing_id' => 101,
            'eligible' => 1,
            'reason_code' => null,
            'universe_membership_state' => 'MEMBER',
            'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
            'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED',
            'liquidity_state' => 'ACTIVE',
            'temporal_status_state' => 'UNKNOWN',
            'event_risk_state' => 'CLEAR',
            'eligibility_reasons_json' => '[]',
            'run_id' => 12,
            'publication_id' => 44,
            'created_at' => Carbon::now()->toDateTimeString(),
        ], $override);
    }

    private function completeCoverageTelemetry(): array
    {
        return [
            'coverage_universe_count' => 0,
            'coverage_expected_count' => 0,
            'coverage_bar_not_expected_count' => 0,
            'coverage_expectation_unknown_count' => 0,
            'coverage_delivered_count' => 0,
            'coverage_delivered_valid_count' => 0,
            'coverage_gate_state' => 'NOT_EVALUABLE',
        ];
    }

    private function runFixture(): EodRun
    {
        return EodRun::query()->create([
            'trade_date_requested' => '2026-08-12',
            'source' => 'API_FREE',
            'created_at' => Carbon::now(),
        ]);
    }

    private function methodSource(string $class, string $method): string
    {
        $reflection = new ReflectionMethod($class, $method);
        $lines = file($reflection->getFileName());

        return implode('', array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - $reflection->getStartLine() + 1
        ));
    }

    private function assertProtectedValuesEqual(array $expected, array $actual, array $fields): void
    {
        foreach ($fields as $field) {
            $this->assertSame((string) $expected[$field], (string) $actual[$field], $field);
        }
    }

    private function normalizedNumber($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    }
}
