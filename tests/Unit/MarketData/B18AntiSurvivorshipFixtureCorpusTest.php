<?php

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` — the eight anti-survivorship fixtures `MD-S050` requires by name.
 *
 * `Replay_Verification_Contract_LOCKED.md` (`MD-S050`) says "Required fixtures include:" and lists
 * eight cases. `MD-S050-R0019`..`R0026` are those eight members. `F-MD-B19-A001-002` recorded all
 * eight as `UNSUPPORTED`: they had been bound to
 * `AsKnownReplayBoundaryTest::test_identity_recorded_after_the_cutoff_is_invisible`, which executes
 * none of them as fixtures.
 *
 * Each case needs a fixture that actually runs. Three of the eight — the identity cases — had no
 * executing fixture anywhere and are implemented here. The other five already have behavioural
 * guards that execute exactly their scenario, and this class binds each case to the guard that runs
 * it and asserts that guard still exists, so a rename or deletion fails rather than silently
 * removing a required fixture from the corpus.
 *
 * The identity cases are the ones survivorship bias actually travels through: a universe rebuilt
 * from today's listings loses the delisted, and a universe keyed on symbol text silently swaps one
 * company for another when a ticker is reused.
 */
class B18AntiSurvivorshipFixtureCorpusTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const CONTRACT = 'docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md';

    private const INTRODUCER = 'Required fixtures include:';

    /**
     * Reviewed map: the contract's own wording => the test method that executes that fixture.
     *
     * @return array<string,string>
     */
    private function fixtureMap(): array
    {
        return [
            'a listing active at historical T but inactive today;' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'a symbol change and provider-symbol mapping transition;' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date',
            'symbol text reused by another listing;' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date',
            'a calendar/status fact corrected after T;' =>
                'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'a corporate action learned or verified later;' =>
                'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'a configuration/formula change after T;' =>
                'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
            'an original and corrected immutable publication; and' =>
                'ReplayVerificationServiceTest::test_verify_replay_resolves_historical_publication_without_current_pointer_fallback',
            'a provider outage that cannot disappear through dormancy/current-universe filtering.' =>
                'SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /** @return array<int,string> the eight numbered members, read from the frozen contract */
    private function contractCases(): array
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $lines = preg_split('/\R/', (string) file_get_contents($path));

        $start = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === self::INTRODUCER) {
                $start = $i;
                break;
            }
        }
        $this->assertNotNull($start, 'the required-fixtures introducer is no longer in MD-S050; this '
            .'guard is bound to text that moved and must be re-read, not relaxed');

        $cases = [];
        for ($i = $start + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^\d+\.\s+(.*)$/', $line, $m) !== 1) {
                break;
            }
            $cases[] = trim($m[1]);
        }

        return $cases;
    }

    public function test_the_contract_list_and_the_reviewed_fixture_map_cover_exactly_the_same_cases(): void
    {
        $cases = $this->contractCases();
        $mapped = array_keys($this->fixtureMap());
        sort($cases);
        sort($mapped);

        $this->assertSame($cases, $mapped,
            'MD-S050 and the reviewed fixture map disagree; a required fixture added to the contract '
                .'would otherwise have no executing guard and nobody would be told');

        // Population assertion: eight is the number the contract states, and a map that silently
        // shrank to seven would still match a contract list that shrank with it. This pins the
        // count so a change has to be deliberate.
        $this->assertCount(8, $cases, 'MD-S050 no longer lists eight required fixtures');
    }

    /**
     * Every named fixture guard must exist. Without this the map is a list of intentions: a guard
     * renamed or deleted elsewhere would leave its contract case with no executing fixture and this
     * class would stay green.
     */
    public function test_every_named_fixture_guard_exists_and_is_executable(): void
    {
        $missing = [];
        foreach ($this->fixtureMap() as $case => $ref) {
            [$class, $method] = explode('::', $ref);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file)) {
                $missing[] = $case.' -> '.$class.' (file not found)';

                continue;
            }
            if (strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $case.' -> '.$ref;
            }
        }

        $this->assertSame([], $missing,
            'these required fixtures name a guard that no longer exists');
    }

    /**
     * `MD-S050-R0040` opens with a classification: "The eight anti-survivorship fixtures required
     * below **are as-known fixtures**." That is a stronger statement than the map above, which only
     * says each case is executed by something. An as-known fixture is one whose answer is decided by
     * a declared knowledge cutoff -- change the cutoff and the answer changes -- and a fixture that
     * proves only which facts were *effective* on a trade date satisfies the map without satisfying
     * this sentence.
     *
     * `F-MD-B18-A002-002` reported two of the eight as unable to be as-known fixtures: a delisting
     * had no knowledge time of its own, and no publication resolver took a cutoff. Both were closed
     * in this attempt -- `md_listings.delisted_recorded_at` and
     * `EodEvidenceRepository::resolvePublicationAsKnownAt()` -- so all eight now are.
     *
     * Each row names the guard that decides the case by a cutoff and the cutoff-bounded runtime
     * entry point that guard drives. Both must exist, and the guard must actually name the resolver:
     * a fixture rewritten to stop passing a cutoff, or a resolver deleted from the runtime, takes
     * this red rather than leaving the classification standing on its own wording.
     *
     * The third column is the argument count that carries the cutoff. Every one of these
     * resolvers answers both questions -- `readProjectedUniverseAsOf($tradeDate)` is the
     * effective-time read and `readProjectedUniverseAsOf($tradeDate, $knownAt)` the as-known
     * one -- so naming the method is not enough to tell the two apart. Without the arity this
     * guard would stay green if a row were repointed at the effective-time fixture sitting
     * beside it, which is the whole distinction `MD-S050-R0040` turns on.
     *
     * @return array<string,array{0:string,1:string,2:int}> contract wording => [guard, resolver, cutoff arity]
     */
    private function asKnownFixtureMap(): array
    {
        return [
            'a listing active at historical T but inactive today;' => [
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_delisting_recorded_later_is_invisible_to_an_earlier_cutoff',
                'TemporalIdentityRepository::readProjectedUniverseAsOf',
                2,
            ],
            'a symbol change and provider-symbol mapping transition;' => [
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_recorded_after_the_cutoff_is_not_yet_visible_to_it',
                'TemporalIdentityRepository::readProjectedUniverseAsOf',
                2,
            ],
            'symbol text reused by another listing;' => [
                'B18AntiSurvivorshipFixtureCorpusTest::test_reused_symbol_text_resolves_to_the_holder_known_at_the_cutoff',
                'TemporalIdentityRepository::resolveProviderContext',
                4,
            ],
            'a calendar/status fact corrected after T;' => [
                'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
                'MarketCalendarRepository::sessionContext',
                2,
            ],
            'a corporate action learned or verified later;' => [
                'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
                'EventRiskSourceRepository::resolveEventRiskContextForTickerIds',
                3,
            ],
            'a configuration/formula change after T;' => [
                'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
                'MarketDataConfigSnapshotRepository::resolveForRun',
                2,
            ],
            'an original and corrected immutable publication; and' => [
                'B18CorrectionReadPathScenarioTest::test_a_correction_sealed_later_is_invisible_to_an_earlier_cutoff',
                'EodEvidenceRepository::resolvePublicationAsKnownAt',
                2,
            ],
            'a provider outage that cannot disappear through dormancy/current-universe filtering.' => [
                'SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_in_as_known_observation_manifest',
                'SourceObservationRepository::observationManifestAsKnown',
                2,
            ],
        ];
    }

    public function test_every_required_fixture_is_also_an_as_known_fixture(): void
    {
        $cases = $this->contractCases();
        $mapped = array_keys($this->asKnownFixtureMap());
        sort($cases);
        sort($mapped);

        $this->assertSame($cases, $mapped,
            'MD-S050 requires every one of the eight to be an as-known fixture; a case with no '
                .'cutoff-decided guard makes that sentence false');

        $violations = [];
        foreach ($this->asKnownFixtureMap() as $case => [$ref, $resolver, $cutoffArity]) {
            [$class, $method] = explode('::', $ref);
            [$resolverClass, $resolverMethod] = explode('::', $resolver);

            $guardFile = __DIR__.'/'.$class.'.php';
            $resolverFile = dirname(__DIR__, 3)
                .'/app/Infrastructure/Persistence/MarketData/'.$resolverClass.'.php';

            if (! is_file($guardFile)) {
                $violations[] = $case.' -> '.$class.' (guard file not found)';

                continue;
            }
            if (! is_file($resolverFile)) {
                $violations[] = $case.' -> '.$resolverClass.' (resolver file not found)';

                continue;
            }

            $guardSource = (string) file_get_contents($guardFile);
            if (strpos($guardSource, 'function '.$method.'(') === false) {
                $violations[] = $case.' -> '.$ref.' (guard method not found)';

                continue;
            }
            if (strpos((string) file_get_contents($resolverFile), 'function '.$resolverMethod.'(') === false) {
                $violations[] = $case.' -> '.$resolver.' (resolver method not found)';

                continue;
            }
            $body = $this->methodBody($guardSource, $method);
            if ($body === null) {
                $violations[] = $case.' -> '.$ref.' (guard body not parseable)';

                continue;
            }

            $arities = $this->callArities($body, $resolverMethod);
            if ($arities === []) {
                $violations[] = $case.' -> '.$ref.' no longer drives '.$resolver;

                continue;
            }
            if (max($arities) < $cutoffArity) {
                $violations[] = $case.' -> '.$ref.': its widest call to '.$resolver.' passes '
                    .max($arities).' of the '.$cutoffArity.' arguments the cutoff needs, so this '
                    .'fixture answers the effective-time question instead';
            }
        }

        $this->assertSame([], $violations,
            'these required fixtures no longer decide their case by a declared knowledge cutoff');
    }

    /** The body of one method, brace-matched, so a call in a neighbouring fixture cannot count. */
    private function methodBody(string $source, string $method): ?string
    {
        $at = strpos($source, 'function '.$method.'(');
        if ($at === false) {
            return null;
        }
        $open = strpos($source, '{', $at);
        if ($open === false) {
            return null;
        }

        $depth = 0;
        for ($i = $open; $i < strlen($source); $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $open, $i - $open + 1);
                }
            }
        }

        return null;
    }

    /**
     * How many arguments each call to `$method` in `$body` passes. Commas inside nested calls,
     * arrays and string literals are not argument separators, so they are stepped over rather than
     * counted -- `resolveEventRiskContextForTickerIds([7], $date, $cutoff)` passes three.
     *
     * @return array<int,int>
     */
    private function callArities(string $body, string $method): array
    {
        $arities = [];
        $needle = '->'.$method.'(';
        $offset = 0;

        while (($at = strpos($body, $needle, $offset)) !== false) {
            $i = $at + strlen($needle);
            $offset = $i;

            $depth = 1;
            $commas = 0;
            $sawContent = false;
            $quote = null;

            for (; $i < strlen($body); $i++) {
                $c = $body[$i];

                if ($quote !== null) {
                    if ($c === '\\') {
                        $i++;
                    } elseif ($c === $quote) {
                        $quote = null;
                    }

                    continue;
                }

                if ($c === "'" || $c === '"') {
                    $quote = $c;
                    $sawContent = true;

                    continue;
                }
                if ($c === '(' || $c === '[') {
                    $depth++;
                    $sawContent = true;

                    continue;
                }
                if ($c === ')' || $c === ']') {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }

                    continue;
                }
                if ($c === ',' && $depth === 1) {
                    $commas++;

                    continue;
                }
                if (trim($c) !== '') {
                    $sawContent = true;
                }
            }

            $arities[] = $sawContent ? $commas + 1 : 0;
        }

        return $arities;
    }
    /**
     * `MD-S050-R0019` — a listing active at historical T but inactive today.
     *
     * The canonical survivorship fixture. A universe rebuilt from today's listings loses every
     * company that has since delisted, and a backtest over that universe reads as though only
     * survivors ever traded.
     */
    public function test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe(): void
    {
        $listingId = $this->seedListing(1, ['delisted_date' => '2025-06-30', 'listing_state' => 'DELISTED']);
        $this->seedSymbol($listingId, 'GONE', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null);

        $survivor = $this->seedListing(2);
        $this->seedSymbol($survivor, 'ALIVE', '2023-01-02 00:00:00', null);
        $this->seedBoard($survivor, 'MAIN', '2023-01-02 00:00:00', null);

        $repo = new TemporalIdentityRepository();
        $atT = array_column($repo->readProjectedUniverseAsOf('2024-05-02'), 'ticker_code');
        $today = array_column($repo->readProjectedUniverseAsOf('2026-03-02'), 'ticker_code');

        $this->assertContains('GONE', $atT,
            'a listing that was active on the trade date is missing from the historical universe');
        $this->assertContains('ALIVE', $atT);
        $this->assertNotContains('GONE', $today,
            'the delisted listing is still in a current read, so this fixture does not actually '
                .'exercise survivorship');
        $this->assertContains('ALIVE', $today);
    }

    /**
     * `MD-S050-R0020` — a symbol change and provider-symbol mapping transition.
     *
     * Both sides are asserted. Resolving the new symbol for an old trade date must not succeed
     * either, or the fixture would pass against an implementation that ignores effective intervals.
     */
    public function test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date(): void
    {
        $listingId = $this->seedListing(3);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null);
        $this->seedSymbol($listingId, 'OLDSYM', '2023-01-02 00:00:00', '2024-07-01 00:00:00');
        $this->seedSymbol($listingId, 'NEWSYM', '2024-07-01 00:00:00', null);
        $this->seedMapping($listingId, 'OLDSYM.JK', '2023-01-02 00:00:00', '2024-07-01 00:00:00');
        $this->seedMapping($listingId, 'NEWSYM.JK', '2024-07-01 00:00:00', null);

        $repo = new TemporalIdentityRepository();

        $before = $repo->resolveProviderContext('OLDSYM', 'yahoo_finance', '2024-05-02');
        $this->assertNotNull($before, 'the pre-change symbol does not resolve on a pre-change date');
        $this->assertSame($listingId, (int) $before['listing_id']);
        $this->assertSame('OLDSYM.JK', $before['provider_symbol']);

        $after = $repo->resolveProviderContext('NEWSYM', 'yahoo_finance', '2024-08-02');
        $this->assertNotNull($after, 'the post-change symbol does not resolve on a post-change date');
        $this->assertSame($listingId, (int) $after['listing_id'],
            'the symbol change moved the listing identity; identity must survive a rename');
        $this->assertSame('NEWSYM.JK', $after['provider_symbol']);

        // The other direction, and the resolver is stricter than "returns nothing": asking for the
        // post-change symbol on a pre-change date refuses outright. That is the fail-closed
        // behaviour MD-S050 wants -- an unresolved identity must not become a soft miss that a
        // caller can read as "no data". This expectation was written as assertNull first and the
        // implementation was right.
        $refused = null;
        try {
            $repo->resolveProviderContext('NEWSYM', 'yahoo_finance', '2024-05-02');
        } catch (\RuntimeException $e) {
            $refused = $e->getMessage();
        }
        $this->assertNotNull($refused,
            'the post-change symbol resolved on a pre-change date, so effective intervals are '
                .'not being honoured and the fixture proves nothing');
        $this->assertStringContainsString('PROVIDER_SYMBOL_MAPPING_MISSING', $refused);
    }

    /**
     * `MD-S050-R0021` — symbol text reused by another listing.
     *
     * The case that makes symbol text unusable as identity. `REUSED` belongs to one company before
     * the handover and a different company after; a lookup keyed on the text alone silently returns
     * the wrong company's history.
     */
    public function test_reused_symbol_text_resolves_to_the_listing_that_held_it_on_the_trade_date(): void
    {
        $first = $this->seedListing(4);
        $this->seedBoard($first, 'MAIN', '2023-01-02 00:00:00', null);
        $this->seedSymbol($first, 'REUSED', '2023-01-02 00:00:00', '2024-07-01 00:00:00');
        $this->seedMapping($first, 'REUSED.JK', '2023-01-02 00:00:00', '2024-07-01 00:00:00');

        $second = $this->seedListing(5);
        $this->seedBoard($second, 'MAIN', '2024-07-01 00:00:00', null);
        $this->seedSymbol($second, 'REUSED', '2024-07-01 00:00:00', null);
        $this->seedMapping($second, 'REUSED.JK', '2024-07-01 00:00:00', null);

        $repo = new TemporalIdentityRepository();

        $held = $repo->resolveProviderContext('REUSED', 'yahoo_finance', '2024-05-02');
        $this->assertNotNull($held);
        $this->assertSame($first, (int) $held['listing_id'],
            'the reused symbol resolved to the later listing on a date the earlier one held it');

        $taken = $repo->resolveProviderContext('REUSED', 'yahoo_finance', '2024-08-02');
        $this->assertNotNull($taken);
        $this->assertSame($second, (int) $taken['listing_id'],
            'the reused symbol resolved to the earlier listing on a date the later one held it');

        $this->assertNotSame((int) $held['listing_id'], (int) $taken['listing_id'],
            'both dates resolved to the same listing, so this fixture is not exercising symbol reuse');

        // Resolution above travels through the provider mapping, whose effective interval carries
        // the result. The projected universe reads the symbol interval directly, so asserting it
        // here makes the symbol table load-bearing too -- otherwise a defect in symbol interval
        // handling alone would leave this fixture green. A probe that removed the symbol upper
        // bound was caught by nothing until this assertion existed.
        $repoUniverse = new TemporalIdentityRepository();
        $atHeld = $repoUniverse->readProjectedUniverseAsOf('2024-05-02');
        $atTaken = $repoUniverse->readProjectedUniverseAsOf('2024-08-02');

        $heldRow = $this->rowForSymbol($atHeld, 'REUSED');
        $takenRow = $this->rowForSymbol($atTaken, 'REUSED');

        $this->assertSame($first, (int) $heldRow['listing_id'],
            'the projected universe attributes the reused symbol to the wrong listing before the handover');
        $this->assertSame($second, (int) $takenRow['listing_id'],
            'the projected universe attributes the reused symbol to the wrong listing after the handover');
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function rowForSymbol(array $rows, string $symbol): array
    {
        $matched = [];
        foreach ($rows as $row) {
            if (($row['ticker_code'] ?? null) === $symbol) {
                $matched[] = $row;
            }
        }

        $this->assertCount(1, $matched,
            'expected exactly one listing to hold '.$symbol.' on that date; got '.count($matched)
                .'. Two would mean the reused symbol resolves to both companies at once.');

        return $matched[0];
    }

    /**
     * `MD-S050-R0040` -- the same symbol-change case, as an **as-known** fixture.
     *
     * `MD-S050` classifies the eight required fixtures as as-known fixtures, and the effective-time
     * guard above is not one: it passes no knowledge cutoff, so it proves the symbol effective on a
     * trade date and nothing about what the platform could have known.
     *
     * Here the change takes effect on 1 July but is only recorded on 1 September. A replay reading
     * trade date 2 August as known on 1 August must still resolve `OLDSYM`, because the change was
     * already effective but not yet on record. Without the cutoff the same read returns `NEWSYM`,
     * so the cutoff is what decides -- which is the difference between an effective-time fixture
     * and an as-known one.
     */
    public function test_a_symbol_change_recorded_after_the_cutoff_is_not_yet_visible_to_it(): void
    {
        $listingId = $this->seedListing(6);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null);
        // On record since 2023 as an open interval, and retracted on 1 September when the
        // platform learned the symbol had actually changed on 1 July. Closing the interval is
        // itself a later-learned fact, so it has to arrive as a retraction plus a replacement
        // revision -- writing the closed interval directly would backdate the knowledge.
        $this->seedSymbolKnownAt($listingId, 'OLDSYM2', '2023-01-02 00:00:00', null, '2023-01-02 00:00:00', '2024-09-01 00:00:00');
        $this->seedSymbolKnownAt($listingId, 'OLDSYM2', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');
        $this->seedSymbolKnownAt($listingId, 'NEWSYM2', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');
        $this->seedMappingKnownAt($listingId, 'OLDSYM2.JK', '2023-01-02 00:00:00', null, '2023-01-02 00:00:00', '2024-09-01 00:00:00');
        $this->seedMappingKnownAt($listingId, 'OLDSYM2.JK', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');
        $this->seedMappingKnownAt($listingId, 'NEWSYM2.JK', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');

        $repo = new TemporalIdentityRepository();

        $asKnown = array_column($repo->readProjectedUniverseAsOf('2024-08-02', '2024-08-01 00:00:00'), 'ticker_code');
        $learned = array_column($repo->readProjectedUniverseAsOf('2024-08-02', '2024-10-01 00:00:00'), 'ticker_code');
        $today = array_column($repo->readProjectedUniverseAsOf('2024-08-02'), 'ticker_code');

        $this->assertContains('OLDSYM2', $asKnown,
            'the rename was effective but not yet recorded, so a replay as known on 1 August must '
                .'still see the old symbol');
        $this->assertNotContains('NEWSYM2', $asKnown,
            'a symbol revision recorded on 1 September cannot be visible to a cutoff of 1 August');

        // The third read is what makes the retraction load-bearing. The first two are decided by
        // recorded_at alone -- the new revision simply did not exist yet -- so a retraction clause
        // that did nothing would leave them green. Once the cutoff has learned the rename, the
        // interval that was retracted must stop resolving, or the old and new symbol both answer
        // for the same listing on the same date.
        $this->assertNotContains('OLDSYM2', $learned,
            'a cutoff that has learned the rename still resolved the retracted open interval');
        $this->assertContains('NEWSYM2', $learned,
            'and it must resolve the revision recorded in its place');

        $this->assertContains('NEWSYM2', $today,
            'and without a cutoff the effective symbol wins, so the cutoff is what decides here');
        $this->assertNotContains('OLDSYM2', $today,
            'an uncut read must not see the retracted interval either');

        // The other half of the case: the provider-symbol mapping transitions with the rename, and
        // the retracted mapping must stop answering once the transition is on record. Without this
        // the mapping rows are seeded and never read, and every assertion above would hold with no
        // knowledge-time handling on md_provider_symbol_mappings at all.
        $this->assertSame('OLDSYM2.JK',
            $repo->resolveProviderContext('OLDSYM2', 'yahoo_finance', '2024-08-02', '2024-08-01 00:00:00')['provider_symbol'],
            'as known on 1 August the provider still called this listing by its old symbol');
        $this->assertSame('NEWSYM2.JK',
            $repo->resolveProviderContext('NEWSYM2', 'yahoo_finance', '2024-08-02', '2024-10-01 00:00:00')['provider_symbol'],
            'once the transition is on record the retracted mapping must not answer beside it');
    }

    /**
     * `MD-S050-R0040` -- symbol reuse, as an **as-known** fixture.
     *
     * `REUSED2` is handed from one listing to another effective 1 July, recorded 1 September. A
     * replay as known on 1 August must still resolve the text to the listing that held it then.
     * Resolving to the new holder would attribute one company's history to another on the strength
     * of a fact the platform had not yet learned.
     */
    public function test_reused_symbol_text_resolves_to_the_holder_known_at_the_cutoff(): void
    {
        $first = $this->seedListing(7);
        $this->seedBoard($first, 'MAIN', '2023-01-02 00:00:00', null);
        $this->seedSymbolKnownAt($first, 'REUSED2', '2023-01-02 00:00:00', null, '2023-01-02 00:00:00', '2024-09-01 00:00:00');
        $this->seedSymbolKnownAt($first, 'REUSED2', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');
        $this->seedMappingKnownAt($first, 'REUSED2.JK', '2023-01-02 00:00:00', null, '2023-01-02 00:00:00', '2024-09-01 00:00:00');
        $this->seedMappingKnownAt($first, 'REUSED2.JK', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');

        $second = $this->seedListing(8);
        $this->seedBoard($second, 'MAIN', '2024-07-01 00:00:00', null);
        $this->seedSymbolKnownAt($second, 'REUSED2', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');
        $this->seedMappingKnownAt($second, 'REUSED2.JK', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');

        $repo = new TemporalIdentityRepository();

        $asKnown = $repo->resolveProviderContext('REUSED2', 'yahoo_finance', '2024-08-02', '2024-08-01 00:00:00');
        $this->assertNotNull($asKnown);
        $this->assertSame($first, (int) $asKnown['listing_id'],
            'as known on 1 August the handover was not yet recorded, so the text still belongs to '
                .'the listing that held it');

        // Once the handover is on record the retracted mapping must stop answering, or both
        // listings claim the same provider symbol for the same date. This is the read the
        // retraction decides; the one above is decided by recorded_at alone.
        $learned = $repo->resolveProviderContext('REUSED2', 'yahoo_finance', '2024-08-02', '2024-10-01 00:00:00');
        $this->assertSame($second, (int) $learned['listing_id'],
            'a cutoff that has learned the handover still resolved the retracted mapping');

        $today = $repo->resolveProviderContext('REUSED2', 'yahoo_finance', '2024-08-02');
        $this->assertSame($second, (int) $today['listing_id'],
            'and without a cutoff it resolves to the new holder, so the cutoff is load-bearing');
    }

    /**
     * `MD-S050-R0019` / `MD-S050-R0040` -- the delisted-listing case, as an **as-known** fixture.
     *
     * The effective-time guard above proves a listing delisted in 2025 is absent from a 2026 read
     * and present at 2024. It passes no cutoff, so it says nothing about what the platform could
     * have known -- and survivorship bias is a knowledge-time phenomenon, not an effective-time one.
     * A universe rebuilt for a 2026 date as known in January 2025 must still contain the company,
     * because the delisting had not been recorded yet.
     *
     * This is the case `F-MD-B18-A002-002` reported as impossible: `md_listings.delisted_date` had
     * no knowledge time of its own. `delisted_recorded_at` was added for it.
     */
    public function test_a_delisting_recorded_later_is_invisible_to_an_earlier_cutoff(): void
    {
        $listingId = $this->seedListing(9, [
            'delisted_date' => '2025-06-30',
            'listing_state' => 'DELISTED',
            // Delisted at the end of June, learned by the platform on 1 July.
            'delisted_recorded_at' => '2025-07-01 00:00:00',
        ]);
        $this->seedSymbol($listingId, 'LATEDELIST', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null);

        $repo = new TemporalIdentityRepository();
        $tradeDate = '2025-12-01';

        $asKnownBefore = array_column($repo->readProjectedUniverseAsOf($tradeDate, '2025-01-15 00:00:00'), 'ticker_code');
        $asKnownAfter = array_column($repo->readProjectedUniverseAsOf($tradeDate, '2025-08-01 00:00:00'), 'ticker_code');
        $today = array_column($repo->readProjectedUniverseAsOf($tradeDate), 'ticker_code');

        $this->assertContains('LATEDELIST', $asKnownBefore,
            'a delisting recorded on 1 July cannot remove a company from a universe read as known '
                .'in January; doing so is survivorship bias introduced by the query itself');
        $this->assertNotContains('LATEDELIST', $asKnownAfter,
            'once the delisting is on record the same trade date must lose it');
        $this->assertNotContains('LATEDELIST', $today,
            'and an uncut read agrees with the later cutoff, so the fixture is real');
    }

    /**
     * A delisting that was never recorded can never be visible to a bounded read. `NULL` there means
     * "not learned", and treating it as "learned at the dawn of time" would reintroduce exactly the
     * bias the column exists to remove.
     */
    public function test_a_delisting_with_no_recorded_time_is_never_visible_to_a_cutoff(): void
    {
        $listingId = $this->seedListing(10, [
            'delisted_date' => '2025-06-30',
            'listing_state' => 'DELISTED',
            'delisted_recorded_at' => null,
        ]);
        $this->seedSymbol($listingId, 'UNRECORDED', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null);

        $repo = new TemporalIdentityRepository();

        $this->assertContains('UNRECORDED',
            array_column($repo->readProjectedUniverseAsOf('2025-12-01', '2026-01-01 00:00:00'), 'ticker_code'),
            'a delisting nobody recorded has no knowledge time, so no cutoff can see it');
        $this->assertNotContains('UNRECORDED',
            array_column($repo->readProjectedUniverseAsOf('2025-12-01'), 'ticker_code'),
            'an uncut read still applies the delisting, which is what makes the cutoff the difference');
    }
    private function seedListing(int $n, array $override = []): int
    {
        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'AS-ISSUER-'.$n, 'legal_name' => 'Issuer '.$n,
            'source_ref' => 'fixture', 'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);
        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'AS-INSTRUMENT-'.$n, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);

        return (int) DB::table('md_listings')->insertGetId(array_merge([
            'listing_uid' => 'AS-LISTING-'.$n, 'legacy_ticker_id' => 800 + $n, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'listed_date' => '2023-01-02', 'delisted_date' => null, 'listing_state' => 'LISTED',
            'source_ref' => 'fixture', 'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ], $override));
    }

    /**
     * A symbol whose knowledge time differs from its effective time: the change took effect on
     * `$from` but the platform only recorded it on `$recordedAt`.
     */
    private function seedSymbolKnownAt(int $listingId, string $symbol, string $from, ?string $to, string $recordedAt, ?string $retractedAt = null): void
    {
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX', 'effective_from' => $from, 'effective_to' => $to,
            'recorded_at' => $recordedAt, 'retracted_at' => $retractedAt,
            'source_ref' => 'fixture', 'change_reason' => 'SYMBOL_CHANGE',
        ]);
    }

    private function seedMappingKnownAt(int $listingId, string $providerSymbol, string $from, ?string $to, string $recordedAt, ?string $retractedAt = null): void
    {
        DB::table('md_provider_symbol_mappings')->insert([
            'listing_id' => $listingId, 'provider' => 'yahoo_finance', 'provider_symbol' => $providerSymbol,
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $recordedAt,
            'retracted_at' => $retractedAt,
            'mapping_revision' => 'temporal_provider_mapping_v1', 'source_ref' => 'fixture',
            'change_reason' => 'PROVIDER_MAPPING',
        ]);
    }

    private function seedSymbol(int $listingId, string $symbol, string $from, ?string $to): void
    {
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX', 'effective_from' => $from, 'effective_to' => $to,
            'recorded_at' => $from, 'source_ref' => 'fixture', 'change_reason' => 'SYMBOL_CHANGE',
        ]);
    }

    private function seedBoard(int $listingId, string $board, string $from, ?string $to): void
    {
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => $board,
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $from,
            'source_ref' => 'fixture', 'change_reason' => 'BOARD_MOVEMENT',
        ]);
    }

    private function seedMapping(int $listingId, string $providerSymbol, string $from, ?string $to): void
    {
        DB::table('md_provider_symbol_mappings')->insert([
            'listing_id' => $listingId, 'provider' => 'yahoo_finance', 'provider_symbol' => $providerSymbol,
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $from,
            'mapping_revision' => 'temporal_provider_mapping_v1', 'source_ref' => 'fixture',
            'change_reason' => 'PROVIDER_MAPPING',
        ]);
    }
}
