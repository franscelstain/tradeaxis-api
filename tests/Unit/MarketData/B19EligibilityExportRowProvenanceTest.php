<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — the rows of `eligibility_export.csv` (`MD-S075-R0133..R0138`, `R0143..R0145`, section 4 of
 * Run_Artifacts_Format_LOCKED.md; `F-MD-B01-A014-001`).
 *
 * The export is a row-level view of ONE publication's `eod_eligibility` snapshot. This guard seeds a history in which
 * the same trading date has a SUPERSEDED publication (rows in `eod_eligibility_history`), the CURRENT publication
 * (rows in `eod_eligibility`, the opposite usability for the same tickers and different listing ids), an UNSEALED
 * candidate and a publication of another date, then reads the rows the repository exports. Every expected value below is
 * a literal written from the seed — nothing is derived by the code under test.
 *
 * The reason set is exported as the persisted `eligibility_reasons_json` set, in its persisted order, rendered as a JSON
 * array; the contract prescribes no cell encoding, so no delimiter or re-sorting is introduced. A NULL persisted set is
 * exported as an empty cell (unknown), never as `[]` (a recorded empty set) and never rebuilt from the legacy
 * `reason_code`.
 */
class B19EligibilityExportRowProvenanceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const DATE = '2026-03-20';

    private const COLUMNS = ['trade_date', 'listing_id', 'ticker_id', 'publication_id', 'data_usable', 'reason_codes', 'eligible', 'reason_code'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedHistory();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedRun(int $id, string $date, int $publication, int $version): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => $id, 'trade_date_requested' => $date, 'trade_date_effective' => $date, 'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS', 'stage' => 'FINALIZE', 'source' => 'manual_file', 'publication_id' => $publication,
            'publication_version' => $version, 'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE', 'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 2, 'coverage_available_count' => 2, 'coverage_missing_count' => 0, 'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800', 'coverage_threshold_mode' => 'MIN_RATIO', 'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1', 'is_current_publication' => 0, 'sealed_at' => $date.' 17:20:00',
            'started_at' => $date.' 17:00:00', 'created_at' => $date.' 17:00:00', 'updated_at' => $date.' 17:20:00',
        ]);
    }

    private function publication(int $id, string $date, int $run, int $version, int $current, string $seal): void
    {
        DB::table('eod_publications')->insert([
            'publication_id' => $id, 'trade_date' => $date, 'run_id' => $run, 'publication_version' => $version, 'is_current' => $current,
            'seal_state' => $seal, 'sealed_at' => $seal === 'SEALED' ? $date.' 17:20:00' : null,
            'created_at' => $date.' 17:20:00', 'updated_at' => $date.' 17:20:00',
        ]);
    }

    private function row(string $table, string $date, int $ticker, ?int $listing, int $publication, int $run, int $eligible, ?string $reason, ?string $set): void
    {
        DB::table($table)->insert([
            'trade_date' => $date, 'ticker_id' => $ticker, 'listing_id' => $listing, 'publication_id' => $publication, 'run_id' => $run,
            'eligible' => $eligible, 'reason_code' => $reason, 'eligibility_reasons_json' => $set, 'created_at' => $date.' 17:20:00',
        ]);
    }

    private function seedHistory(): void
    {
        $d = self::DATE;
        // P10: SUPERSEDED (run 25, v1) - rows live in the history table
        $this->seedRun(25, $d, 10, 1);
        $this->publication(10, $d, 25, 1, 0, 'SEALED');
        // P11: CURRENT (run 26, v2) - rows live in the current table, pointer points at it
        $this->seedRun(26, $d, 11, 2);
        DB::table('eod_runs')->where('run_id', 26)->update(['is_current_publication' => 1]);
        $this->publication(11, $d, 26, 2, 1, 'SEALED');
        DB::table('eod_current_publication_pointer')->insert(['trade_date' => $d, 'publication_id' => 11, 'run_id' => 26, 'publication_version' => 2, 'sealed_at' => $d.' 17:20:00', 'updated_at' => $d.' 17:20:00']);
        // P12: another date, current
        $this->seedRun(27, '2026-03-19', 12, 1);
        $this->publication(12, '2026-03-19', 27, 1, 1, 'SEALED');
        // P13: UNSEALED candidate of the same date
        $this->seedRun(28, $d, 13, 3);
        $this->publication(13, $d, 28, 3, 0, 'UNSEALED');
        DB::table('eod_publications')->where('publication_id', 13)->update(['sealed_at' => $d.' 17:20:00']);   // only seal_state says it is not sealed
        // P14: a second SEALED, SUPERSEDED publication of the same date (run 29, v4) with rows of its own
        $this->seedRun(29, $d, 14, 4);
        $this->publication(14, $d, 29, 4, 0, 'SEALED');

        // superseded P10: ticker 1 usable, 2 blocked by one reason, 3 blocked by a set whose first member is not the legacy reason
        $this->row('eod_eligibility_history', $d, 3, 7003, 10, 25, 0, 'ELIG_MISSING_INDICATORS', '["ELIG_TRADING_SUSPENDED","ELIG_MISSING_INDICATORS"]');
        $this->row('eod_eligibility_history', $d, 1, 7001, 10, 25, 1, null, '[]');
        $this->row('eod_eligibility_history', $d, 2, 7002, 10, 25, 0, 'ELIG_MISSING_BAR', '["ELIG_MISSING_BAR"]');
        // current P11: the SAME tickers with the opposite usability and other listing ids
        $this->row('eod_eligibility', $d, 1, 8001, 11, 26, 0, 'ELIG_INSUFFICIENT_HISTORY', '["ELIG_INSUFFICIENT_HISTORY"]');
        $this->row('eod_eligibility', $d, 2, 8002, 11, 26, 1, null, '[]');
        $this->row('eod_eligibility', $d, 3, null, 11, 26, 0, null, '["ELIG_TRADING_SUSPENDED"]');   // legacy reason NULL, set present, no listing
        $this->row('eod_eligibility', $d, 4, 8004, 11, 26, 1, null, null);                                // usable, set never recorded
        $this->row('eod_eligibility', $d, 6, 8006, 11, 26, 1, null, '["IND_ANNOTATION_ONLY"]');          // usable AND a non-empty set (an annotation)
        $this->row('eod_eligibility', $d, 7, 8007, 11, 26, 0, null, '[]');                                // DEFECTIVE persisted data (blocked, empty set): not producible by the pipeline and now refused at the write (B19EligibilityBlockedRowAdmissionTest); a legacy or bypassed row is REPORTED as stored, not repaired and not conformant
        // decoys: other date, unsealed candidate
        $this->row('eod_eligibility_history', '2026-03-19', 1, 9001, 12, 27, 0, 'ELIG_MISSING_BAR', '["ELIG_MISSING_BAR"]');
        $this->row('eod_eligibility_history', $d, 1, 9101, 13, 28, 0, 'ELIG_MISSING_BAR', '["ELIG_MISSING_BAR"]');
        $this->row('eod_eligibility_history', $d, 5, 9105, 13, 28, 1, null, '[]');
        $this->row('eod_eligibility_history', $d, 1, 9201, 14, 29, 1, null, '[]');
        $this->row('eod_eligibility_history', $d, 9, 9209, 14, 29, 0, 'ELIG_MISSING_BAR', '["ELIG_MISSING_BAR"]');
    }

    private function repo(): EodEvidenceRepository
    {
        return new EodEvidenceRepository();
    }

    /** @return array<int,array<string,mixed>> */
    private function superseded(): array
    {
        return $this->repo()->exportEligibilityRowsForEvidencePublication(self::DATE, 10, false);
    }

    /** @return array<int,array<string,mixed>> */
    private function current(): array
    {
        return $this->repo()->exportEligibilityRowsForEvidencePublication(self::DATE, 11, true);
    }

    /** `R0133..R0138`: the columns, in the contract's order. */
    public function test_every_row_carries_the_contract_columns_in_order(): void
    {
        foreach ([$this->superseded(), $this->current(), $this->repo()->exportEligibilityRows(self::DATE, 11)] as $rows) {
            $this->assertNotSame([], $rows);
            foreach ($rows as $row) {
                $this->assertSame(self::COLUMNS, array_keys($row));
            }
        }
    }

    /** The whole superseded export equals the literal rows of publication 10 and nothing of publication 11, 12 or 13. */
    public function test_a_superseded_publication_exports_exactly_its_own_rows(): void
    {
        $this->assertSame([
            ['trade_date' => '2026-03-20', 'listing_id' => 7001, 'ticker_id' => 1, 'publication_id' => 10, 'data_usable' => 1, 'reason_codes' => '[]', 'eligible' => 1, 'reason_code' => null],
            ['trade_date' => '2026-03-20', 'listing_id' => 7002, 'ticker_id' => 2, 'publication_id' => 10, 'data_usable' => 0, 'reason_codes' => '["ELIG_MISSING_BAR"]', 'eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR'],
            ['trade_date' => '2026-03-20', 'listing_id' => 7003, 'ticker_id' => 3, 'publication_id' => 10, 'data_usable' => 0, 'reason_codes' => '["ELIG_TRADING_SUSPENDED","ELIG_MISSING_INDICATORS"]', 'eligible' => 0, 'reason_code' => 'ELIG_MISSING_INDICATORS'],
        ], $this->superseded());
    }

    /** The current export equals the literal rows of publication 11: same tickers, opposite usability, other listing ids. */
    public function test_the_current_publication_exports_exactly_its_own_rows(): void
    {
        $expected = [
            ['trade_date' => '2026-03-20', 'listing_id' => 8001, 'ticker_id' => 1, 'publication_id' => 11, 'data_usable' => 0, 'reason_codes' => '["ELIG_INSUFFICIENT_HISTORY"]', 'eligible' => 0, 'reason_code' => 'ELIG_INSUFFICIENT_HISTORY'],
            ['trade_date' => '2026-03-20', 'listing_id' => 8002, 'ticker_id' => 2, 'publication_id' => 11, 'data_usable' => 1, 'reason_codes' => '[]', 'eligible' => 1, 'reason_code' => null],
            ['trade_date' => '2026-03-20', 'listing_id' => null, 'ticker_id' => 3, 'publication_id' => 11, 'data_usable' => 0, 'reason_codes' => '["ELIG_TRADING_SUSPENDED"]', 'eligible' => 0, 'reason_code' => null],
            ['trade_date' => '2026-03-20', 'listing_id' => 8004, 'ticker_id' => 4, 'publication_id' => 11, 'data_usable' => 1, 'reason_codes' => null, 'eligible' => 1, 'reason_code' => null],
            ['trade_date' => '2026-03-20', 'listing_id' => 8006, 'ticker_id' => 6, 'publication_id' => 11, 'data_usable' => 1, 'reason_codes' => '["IND_ANNOTATION_ONLY"]', 'eligible' => 1, 'reason_code' => null],
            ['trade_date' => '2026-03-20', 'listing_id' => 8007, 'ticker_id' => 7, 'publication_id' => 11, 'data_usable' => 0, 'reason_codes' => '[]', 'eligible' => 0, 'reason_code' => null],
        ];
        $this->assertSame($expected, $this->current());
        $this->assertSame($expected, $this->repo()->exportEligibilityRows(self::DATE, 11), 'the pointer-resolved readable export has the same projection');
    }

    /** `R0145`: every row of each export names the requested publication only; the neighbours never appear. */
    public function test_no_row_of_another_publication_or_date_appears(): void
    {
        $this->assertSame([10], array_values(array_unique(array_column($this->superseded(), 'publication_id'))));
        $this->assertSame([11], array_values(array_unique(array_column($this->current(), 'publication_id'))));
        $this->assertSame(['2026-03-20'], array_values(array_unique(array_column(array_merge($this->superseded(), $this->current()), 'trade_date'))));
        $this->assertSame([], $this->repo()->exportEligibilityRowsForEvidencePublication(self::DATE, 13, false), 'an unsealed candidate has no evidence export');
        $this->assertNotContains(5, array_column($this->current(), 'ticker_id'));
        $this->assertNotContains(9101, array_column($this->superseded(), 'listing_id'));
        $this->assertNotContains(9201, array_column($this->superseded(), 'listing_id'), 'a second sealed superseded publication of the same date');
        $this->assertSame([1, 9], array_column($this->repo()->exportEligibilityRowsForEvidencePublication(self::DATE, 14, false), 'ticker_id'));
    }

    /** `R0145`: the readable (pointer) export of a publication that is no longer the pointer's is empty, not the other publication's rows. */
    public function test_the_readable_export_of_a_superseded_publication_is_empty(): void
    {
        $this->assertSame([], $this->repo()->exportEligibilityRows(self::DATE, 10));
    }

    /** `R0145`: a current publication that also has its complete history rows is exported from one table, not from both. */
    public function test_a_current_publication_with_history_rows_is_not_doubled(): void
    {
        foreach (DB::table('eod_eligibility')->where('publication_id', 11)->get() as $r) {
            $copy = (array) $r;
            DB::table('eod_eligibility_history')->insert($copy);
        }
        $rows = $this->current();
        $this->assertSame([1, 2, 3, 4, 6, 7], array_column($rows, 'ticker_id'), 'six rows, each once: the two tables are never unioned');
        $this->assertSame(array_map(fn ($r) => $r['listing_id'], $rows), [8001, 8002, null, 8004, 8006, 8007]);
    }

    /**
     * `R0137`: data_usable is the persisted usability and nothing else (not the legacy reason, not the set).
     *
     * Ticker 7 is DEFECTIVE persisted data — blocked with an empty reason set, which the locked snapshot rule forbids and the
     * eligibility write now refuses. It is seeded directly (as a legacy or bypassed row would be) only to show that the export
     * reports usability as stored and never derives it from the set; it is NOT an example of a conformant blocked row, and this
     * test makes no `MD-S075-R0144` claim about it.
     */
    public function test_data_usable_is_the_persisted_usability_independent_of_the_reasons(): void
    {
        $byTicker = array_column($this->current(), null, 'ticker_id');
        $this->assertSame(1, $byTicker[6]['data_usable'], 'usable although the set is not empty');
        $this->assertSame(0, $byTicker[7]['data_usable'], 'blocked although the set is empty: the persisted usability, not the set');
        $this->assertSame(0, $byTicker[3]['data_usable'], 'blocked although the legacy reason_code is NULL: the set explains it, the legacy projection is empty');
        $this->assertSame(1, $byTicker[4]['data_usable'], 'usable although no reason set was recorded');
        $this->assertSame(1, $byTicker[2]['data_usable']);
        $this->assertSame(0, $byTicker[1]['data_usable']);
        foreach (array_merge($this->superseded(), $this->current()) as $row) {
            $this->assertSame($row['eligible'], $row['data_usable'], 'data_usable and the compatibility alias agree');
            $this->assertIsInt($row['data_usable']);
        }
    }

    /** `R0138`: the complete reason set is exported in its persisted order, and the legacy single reason is only a projection of it. */
    public function test_the_complete_reason_set_is_exported_and_the_legacy_reason_is_only_a_projection(): void
    {
        $row = array_column($this->superseded(), null, 'ticker_id')[3];
        $this->assertSame('["ELIG_TRADING_SUSPENDED","ELIG_MISSING_INDICATORS"]', $row['reason_codes'], 'both members, persisted order, not re-sorted, not truncated to the legacy reason');
        $this->assertSame('ELIG_MISSING_INDICATORS', $row['reason_code']);
        $this->assertNotSame($row['reason_code'], json_decode($row['reason_codes'], true)[0]);
    }

    /** `R0138`: an unrecorded set is an empty cell; a recorded empty set is `[]`; the legacy reason never rebuilds a set. */
    public function test_an_unrecorded_reason_set_is_unknown_and_is_never_rebuilt(): void
    {
        $byTicker = array_column($this->current(), null, 'ticker_id');
        $this->assertNull($byTicker[4]['reason_codes']);
        $this->assertSame('[]', $byTicker[2]['reason_codes']);
        DB::table('eod_eligibility')->where('ticker_id', 1)->update(['eligibility_reasons_json' => null]);
        $this->assertNull(array_column($this->current(), null, 'ticker_id')[1]['reason_codes'], 'reason_code ELIG_INSUFFICIENT_HISTORY must not be promoted to a one-member set');
    }

    /** `R0134`, `R0135`: the listing identity is the persisted listing id, never the ticker id or the publication id; NULL stays NULL. */
    public function test_listing_identity_is_the_persisted_listing_and_ticker_is_only_a_compatibility_column(): void
    {
        $byTicker = array_column($this->current(), null, 'ticker_id');
        $this->assertSame(8001, $byTicker[1]['listing_id']);
        $this->assertNull($byTicker[3]['listing_id']);
        foreach (array_merge($this->superseded(), $this->current()) as $row) {
            $this->assertNotSame($row['listing_id'], $row['ticker_id']);
            $this->assertNotSame($row['listing_id'], $row['publication_id']);
        }
        $this->assertSame([1, 2, 3, 4, 6, 7], array_column($this->current(), 'ticker_id'));
    }

    /** `R0133`: the trade date is the publication's date. */
    public function test_the_trade_date_is_the_publication_trade_date(): void
    {
        $this->assertSame('2026-03-19', array_column($this->repo()->exportEligibilityRowsForEvidencePublication('2026-03-19', 12, true), 'trade_date')[0] ?? null);
        $this->assertSame([], $this->repo()->exportEligibilityRowsForEvidencePublication('2026-03-19', 10, false), 'publication 10 belongs to 2026-03-20');
    }

    /** `R0143`: the row order is deterministic (ticker id), whatever the insertion order, and repeats identically. */
    public function test_rows_are_ordered_deterministically_and_repeat_identically(): void
    {
        $first = $this->superseded();
        $this->assertSame([1, 2, 3], array_column($first, 'ticker_id'), 'inserted 3,1,2');
        $this->assertSame($first, $this->superseded());
    }
}
