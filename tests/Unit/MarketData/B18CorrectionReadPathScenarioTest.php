<?php

use App\Infrastructure\Persistence\MarketData\EligibilitySnapshotScopeRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- the `MD-S003` "Correction and read path" scenario family: `R0017` prior
 * immutable publication remains auditable, `R0018` a distinct corrected candidate becomes active
 * only after complete validation and reseal, `R0019` concurrent consumers read exactly one
 * publication.
 *
 * `ReadablePublicationReadContractIntegrationTest` looked like it already covered `R0019`: its
 * fixture carries a row marked `SHOULD_NOT_LEAK` and asserts the consumer returns two rows rather
 * than three. It does not. That row cites publication 999 and ticker 999, and neither exists, so it
 * is dropped by the inner joins on `eod_publications` and the ticker table before any pointer logic
 * runs -- deleting the pointer's own `publication_id` join condition leaves that test green, which
 * is the demonstration that it was never what was being proved.
 *
 * The scenario `MD-S003` asks for is a date that genuinely has two publications: an original and
 * the correction that superseded it, both sealed, each with its own immutable snapshot rows, with
 * the pointer on the newer one. On this schema the versioned rows live in `eod_eligibility_history`
 * -- `eod_eligibility` is a projection with a `(trade_date, ticker_id)` primary key and therefore
 * holds exactly one publication's worth by construction. So "exactly one publication" is enforced
 * in two places, and both are asserted here: the pointer admits one publication per date, and the
 * projection must agree with the pointer or the consumer is given nothing rather than a guess.
 */
class B18CorrectionReadPathScenarioTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';

    /** The superseded original. */
    private const ORIGINAL_PUBLICATION = 10;

    private const ORIGINAL_RUN = 25;

    /** The correction that took the pointer. */
    private const CORRECTED_PUBLICATION = 11;

    private const CORRECTED_RUN = 26;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        config()->set('market_data.tickers.table', 'tickers');
        config()->set('market_data.tickers.id_column', 'ticker_id');
        config()->set('market_data.tickers.code_column', 'ticker_code');
        config()->set('market_data.session_snapshot.scope_default', 'eligibility_set');

        DB::table('tickers')->insert([
            ['ticker_id' => 1, 'ticker_code' => 'BBCA', 'is_active' => 1],
            ['ticker_id' => 2, 'ticker_code' => 'BMRI', 'is_active' => 1],
        ]);

        // Both runs are complete, readable, sealed and coverage-passing. Neither publication is
        // disqualified by anything except which one the pointer names, so the pointer is the only
        // thing left that can decide the answer.
        $this->seedRun(self::ORIGINAL_RUN, self::ORIGINAL_PUBLICATION, 1, 0);
        $this->seedRun(self::CORRECTED_RUN, self::CORRECTED_PUBLICATION, 2, 1);
        $this->seedPublication(self::ORIGINAL_PUBLICATION, self::ORIGINAL_RUN, 1, 0);
        $this->seedPublication(self::CORRECTED_PUBLICATION, self::CORRECTED_RUN, 2, 1, self::ORIGINAL_PUBLICATION);
        $this->pointAt(self::CORRECTED_PUBLICATION, self::CORRECTED_RUN, 2);

        // The original said BBCA was eligible and BMRI was not. The correction reverses both, so
        // reading the wrong publication gives a different answer rather than the same one.
        $this->seedHistory(self::ORIGINAL_PUBLICATION, self::ORIGINAL_RUN, [1 => 1, 2 => 0]);
        $this->seedHistory(self::CORRECTED_PUBLICATION, self::CORRECTED_RUN, [1 => 0, 2 => 1]);
        $this->projectFrom(self::CORRECTED_PUBLICATION, self::CORRECTED_RUN, [1 => 0, 2 => 1]);
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedRun(int $runId, int $publicationId, int $version, int $isCurrent): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => $runId,
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'publication_id' => $publicationId,
            'publication_version' => $version,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 2,
            'coverage_available_count' => 2,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'is_current_publication' => $isCurrent,
            'sealed_at' => $this->sealedAt($version),
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 18:20:00',
        ]);
    }

    /**
     * @param int|null $supersedes the publication this one replaces, written on all three lineage
     *                             columns exactly as `createCandidatePublication` writes them
     */
    private function seedPublication(int $publicationId, int $runId, int $version, int $isCurrent, ?int $supersedes = null): void
    {
        DB::table('eod_publications')->insert([
            'publication_id' => $publicationId,
            'trade_date' => self::TRADE_DATE,
            'run_id' => $runId,
            'publication_version' => $version,
            'is_current' => $isCurrent,
            'supersedes_publication_id' => $supersedes,
            'previous_publication_id' => $supersedes,
            'replaced_publication_id' => $supersedes,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'hash-'.$publicationId,
            'sealed_at' => $this->sealedAt($version),
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 18:20:00',
        ]);
    }

    private function pointAt(int $publicationId, int $runId, int $version): void
    {
        DB::table('eod_current_publication_pointer')->where('trade_date', self::TRADE_DATE)->delete();
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => self::TRADE_DATE,
            'publication_id' => $publicationId,
            'run_id' => $runId,
            'publication_version' => $version,
            'sealed_at' => $this->sealedAt($version),
            'updated_at' => '2026-03-20 18:20:00',
        ]);
    }

    /** The immutable per-publication snapshot rows. @param array<int,int> $eligibleByTicker */
    private function seedHistory(int $publicationId, int $runId, array $eligibleByTicker): void
    {
        foreach ($eligibleByTicker as $tickerId => $eligible) {
            DB::table('eod_eligibility_history')->insert([
                'publication_id' => $publicationId,
                'trade_date' => self::TRADE_DATE,
                'ticker_id' => $tickerId,
                'eligible' => $eligible,
                'reason_code' => $eligible ? null : 'ELIG_NOT_ENOUGH_HISTORY',
                'run_id' => $runId,
                'created_at' => '2026-03-20 17:20:00',
            ]);
        }
    }

    /** The single-row-per-ticker projection the consumer actually reads. @param array<int,int> $eligibleByTicker */
    private function projectFrom(int $publicationId, int $runId, array $eligibleByTicker): void
    {
        DB::table('eod_eligibility')->where('trade_date', self::TRADE_DATE)->delete();
        foreach ($eligibleByTicker as $tickerId => $eligible) {
            DB::table('eod_eligibility')->insert([
                'trade_date' => self::TRADE_DATE,
                'ticker_id' => $tickerId,
                'eligible' => $eligible,
                'reason_code' => $eligible ? null : 'ELIG_NOT_ENOUGH_HISTORY',
                'run_id' => $runId,
                'publication_id' => $publicationId,
                'created_at' => '2026-03-20 17:20:00',
            ]);
        }
    }

    private function sealedAt(int $version): string
    {
        return '2026-03-20 1'.(7 + $version).':20:00';
    }

    /** @return array<string,int> ticker code => eligible flag, as a consumer sees it */
    private function consumerRead(): array
    {
        $out = [];
        foreach ((new EligibilitySnapshotScopeRepository())->getScopeForTradeDate(self::TRADE_DATE) as $row) {
            $out[$row['ticker_code']] = (int) $row['eligible'];
        }

        return $out;
    }

    /**
     * The fixture must really contain two complete publications with their own snapshot rows, or
     * every assertion below concerns a date that only ever had one and the leak it guards against
     * is unreachable.
     */
    public function test_the_fixture_carries_two_sealed_publications_each_with_its_own_snapshot_rows(): void
    {
        $this->assertSame(2, DB::table('eod_publications')->where('trade_date', self::TRADE_DATE)->count());
        $this->assertSame(2, DB::table('eod_publications')
            ->where('trade_date', self::TRADE_DATE)->where('seal_state', 'SEALED')->count(),
            'both are sealed, so seal state cannot be what separates them');

        $this->assertSame(2, DB::table('eod_eligibility_history')
            ->where('publication_id', self::ORIGINAL_PUBLICATION)->count());
        $this->assertSame(2, DB::table('eod_eligibility_history')
            ->where('publication_id', self::CORRECTED_PUBLICATION)->count());
        $this->assertSame(1, DB::table('eod_current_publication_pointer')
            ->where('trade_date', self::TRADE_DATE)->count());
    }

    /**
     * `MD-S003-R0019` -- concurrent consumers read exactly one publication.
     *
     * Two answers exist for this date. The consumer must return the corrected one, and the verdicts
     * are reversed between the publications so returning the wrong set shows up in the values
     * rather than only in a row count.
     */
    public function test_a_consumer_reads_the_publication_the_pointer_names(): void
    {
        $this->assertSame(['BBCA' => 0, 'BMRI' => 1], $this->consumerRead(),
            'the corrected publication reversed both verdicts; reading the original returns the '
                .'opposite pair');
    }

    /**
     * Moving the pointer moves the answer. Without this the guard above would pass equally well
     * against a consumer that ignores the pointer and always takes the highest publication id,
     * which gives the same answer on this fixture by coincidence.
     */
    public function test_moving_the_pointer_back_to_the_original_moves_the_answer_with_it(): void
    {
        DB::table('eod_publications')->where('publication_id', self::CORRECTED_PUBLICATION)->update(['is_current' => 0]);
        DB::table('eod_publications')->where('publication_id', self::ORIGINAL_PUBLICATION)->update(['is_current' => 1]);
        DB::table('eod_runs')->where('run_id', self::CORRECTED_RUN)->update(['is_current_publication' => 0]);
        DB::table('eod_runs')->where('run_id', self::ORIGINAL_RUN)->update(['is_current_publication' => 1]);
        $this->pointAt(self::ORIGINAL_PUBLICATION, self::ORIGINAL_RUN, 1);
        $this->projectFrom(self::ORIGINAL_PUBLICATION, self::ORIGINAL_RUN, [1 => 1, 2 => 0]);

        $this->assertSame(['BBCA' => 1, 'BMRI' => 0], $this->consumerRead(),
            'the consumer must follow the pointer rather than a publication id ordering');
    }

    /**
     * The enforcement half of `R0019`. If the projection still carries the superseded publication
     * while the pointer names the correction, the consumer must be given nothing -- not the stale
     * rows, and not a silent repair. Serving whichever version is present is exactly how two
     * concurrent readers end up on different publications.
     */
    public function test_a_projection_disagreeing_with_the_pointer_yields_nothing_rather_than_stale_rows(): void
    {
        $this->projectFrom(self::ORIGINAL_PUBLICATION, self::ORIGINAL_RUN, [1 => 1, 2 => 0]);

        $this->assertSame([], $this->consumerRead(),
            'a projection out of step with the pointer must refuse rather than serve the '
                .'superseded publication');
        $this->assertSame(2, DB::table('eod_eligibility')->where('trade_date', self::TRADE_DATE)->count(),
            'and the refusal is a read decision, not a deletion');
    }

    /**
     * `MD-S050-R0025` / `MD-S050-R0040` -- the original-and-corrected publication case, as an
     * **as-known** fixture.
     *
     * The pointer tests above prove which publication a reader gets *now*. They pass no cutoff, so
     * they say nothing about which publication a reader had at an earlier moment -- and a
     * correction sealed later silently becoming the answer for a moment that predated it is the
     * same future-state leak the identity and calendar roots are guarded against.
     *
     * The original was sealed at 18:20 and the correction at 19:20. A read as known at 18:30 must
     * return the original: at that moment the correction was still a candidate and no reader could
     * resolve it. The cutoffs are asserted to straddle the two seal times, because a cutoff that
     * fell outside them would make this fixture pass while proving nothing.
     *
     * This is the case `F-MD-B18-A002-002` reported as impossible; `resolvePublicationAsKnownAt()`
     * was added for it.
     */
    public function test_a_correction_sealed_later_is_invisible_to_an_earlier_cutoff(): void
    {
        $repository = new EodEvidenceRepository();

        $betweenSeals = '2026-03-20 18:30:00';
        $afterBothSeals = '2026-03-20 19:30:00';
        $this->assertTrue(
            $this->sealedAt(1) < $betweenSeals && $betweenSeals < $this->sealedAt(2)
                && $this->sealedAt(2) < $afterBothSeals,
            'the cutoffs must straddle the two seal times or the fixture decides nothing'
        );

        $before = $repository->resolvePublicationAsKnownAt(self::TRADE_DATE, $betweenSeals);
        $after = $repository->resolvePublicationAsKnownAt(self::TRADE_DATE, $afterBothSeals);

        $this->assertNotNull($before, 'the original was sealed at 18:20 and must resolve at 18:30');
        $this->assertSame(self::ORIGINAL_PUBLICATION, (int) $before->publication_id,
            'a correction sealed at 19:20 cannot be the publication a reader had at 18:30');
        $this->assertSame(1, (int) $before->publication_version);

        $this->assertSame(self::CORRECTED_PUBLICATION, (int) $after->publication_id,
            'and once the correction is sealed it becomes the answer, so the cutoff is what decides');
        $this->assertSame(2, (int) $after->publication_version);
    }

    /**
     * Before anything was sealed for the date there is no publication to resolve. Null is the honest
     * answer -- the date had no readable publication at that moment -- and returning the original
     * anyway would let a replay read an artifact that did not yet exist.
     */
    public function test_a_cutoff_before_any_seal_resolves_no_publication(): void
    {
        $beforeAnySeal = '2026-03-20 12:00:00';
        $this->assertTrue($beforeAnySeal < $this->sealedAt(1), 'the cutoff must precede the first seal');

        $this->assertNull(
            (new EodEvidenceRepository())->resolvePublicationAsKnownAt(self::TRADE_DATE, $beforeAnySeal),
            'a cutoff before the first seal must resolve nothing rather than the earliest row'
        );
    }

    /**
     * An unsealed candidate is never the as-known answer however recent it is: before the seal it
     * was not resolvable by any reader.
     */
    public function test_an_unsealed_candidate_is_not_the_as_known_publication(): void
    {
        DB::table('eod_publications')
            ->where('publication_id', self::CORRECTED_PUBLICATION)
            ->update(['seal_state' => 'UNSEALED']);

        $resolved = (new EodEvidenceRepository())
            ->resolvePublicationAsKnownAt(self::TRADE_DATE, '2026-03-20 19:30:00');

        $this->assertSame(self::ORIGINAL_PUBLICATION, (int) $resolved->publication_id,
            'an unsealed candidate was returned as the publication a reader had');
    }

    /**
     * The fail-closed half of the selector. Two publications sealed for the same date, neither
     * naming the other, are an unresolved supersession: nothing in the data says which one a reader
     * had. Returning the higher version would be the recency shortcut
     * `ReadPathShortcutProhibitionTest` bans from the consumer read repositories, and a replay that
     * silently read the wrong half of a correction pair would carry that guess into every number it
     * produced. The read stops instead.
     */
    public function test_two_sealed_publications_with_no_supersession_link_are_refused(): void
    {
        DB::table('eod_publications')
            ->where('publication_id', self::CORRECTED_PUBLICATION)
            ->update([
                'supersedes_publication_id' => null,
                'previous_publication_id' => null,
                'replaced_publication_id' => null,
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/EVIDENCE_AS_KNOWN_PUBLICATION_AMBIGUOUS/');
        (new EodEvidenceRepository())->resolvePublicationAsKnownAt(self::TRADE_DATE, '2026-03-20 19:30:00');
    }
    /**
     * The cutoff is mandatory. Calling this without one is a current-pointer read wearing an
     * as-known name, which is exactly the confusion the method exists to remove.
     */
    public function test_as_known_publication_resolution_requires_a_cutoff(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/EVIDENCE_SELECTOR_MISSING/');
        (new EodEvidenceRepository())->resolvePublicationAsKnownAt(self::TRADE_DATE, '');
    }
    /**
     * `MD-S003-R0017` -- the prior immutable publication remains auditable.
     *
     * Superseded is not deleted. The original's snapshot rows are still there, still saying what
     * they said, and the platform refuses to discard the sealed publication that carries them.
     */
    public function test_the_superseded_publication_keeps_its_rows_and_cannot_be_discarded(): void
    {
        $original = DB::table('eod_eligibility_history')
            ->where('publication_id', self::ORIGINAL_PUBLICATION)
            ->orderBy('ticker_id')
            ->pluck('eligible', 'ticker_id')
            ->all();

        $this->assertSame([1 => 1, 2 => 0], array_map('intval', $original),
            'the original publication still reports what it always reported');

        try {
            (new EodPublicationRepository())->discardCandidatePublication(self::ORIGINAL_PUBLICATION);
            $this->fail('a sealed publication must not be discardable; the audit trail is what it is for');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('SEALED', $e->getMessage());
        }

        $this->assertSame(2, DB::table('eod_eligibility_history')
            ->where('publication_id', self::ORIGINAL_PUBLICATION)->count(),
            'the refused discard must not have deleted anything on its way to failing');
        $this->assertNotNull(DB::table('eod_publications')
            ->where('publication_id', self::ORIGINAL_PUBLICATION)->first());
    }

    /**
     * `MD-S003-R0018` -- a distinct corrected candidate becomes active only after complete
     * validation and reseal.
     *
     * The correction is a separate publication with its own version and its own rows rather than an
     * edit of the original, and it does not become readable merely by holding the pointer: unseal
     * it and the consumer gets nothing.
     */
    public function test_an_unsealed_corrected_candidate_is_not_readable_even_while_holding_the_pointer(): void
    {
        $versions = DB::table('eod_publications')
            ->where('trade_date', self::TRADE_DATE)
            ->orderBy('publication_id')
            ->pluck('publication_version', 'publication_id')
            ->all();
        $this->assertSame(
            [self::ORIGINAL_PUBLICATION => 1, self::CORRECTED_PUBLICATION => 2],
            array_map('intval', $versions),
            'the correction is a distinct publication carrying its own version, not an edit'
        );

        DB::table('eod_publications')
            ->where('publication_id', self::CORRECTED_PUBLICATION)
            ->update(['seal_state' => 'UNSEALED']);

        $this->assertSame([], $this->consumerRead(),
            'an unsealed candidate is still being assembled; holding the pointer does not make it '
                .'readable, and falling back to the original would serve superseded data');
    }

    /**
     * A second pointer row for the same date cannot exist at all, so "exactly one" is a property of
     * the schema rather than of the query that happens to read it.
     */
    public function test_the_pointer_table_refuses_a_second_current_publication_for_the_date(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => self::TRADE_DATE,
            'publication_id' => self::ORIGINAL_PUBLICATION,
            'run_id' => self::ORIGINAL_RUN,
            'publication_version' => 1,
            'sealed_at' => $this->sealedAt(1),
            'updated_at' => '2026-03-20 18:20:00',
        ]);
    }
}
