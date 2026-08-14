<?php

use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\CorpusAdmissionRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W18 — exact and as-known replay, stage 18.
 *
 * Exit gate: "exact replay matches values/nulls/reasons/lineage/hashes; as-known replay cannot see
 * later identity/status/event/config/factor revisions; no strategy P&L metric menjadi market-data
 * acceptance."
 *
 * Owner contracts:
 *   docs/market_data/book/Replay_Verification_Contract_LOCKED.md
 *   docs/market_data/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md
 *
 * As-known replay answers a different question from exact replay. Exact replay asks whether the
 * platform still produces what it produced. As-known replay asks what the platform could have
 * known at a moment — and the only way that can be wrong is by letting a fact recorded later leak
 * backwards, which is indistinguishable from foresight in a backtest.
 *
 * Each root of temporal truth is tested at its own boundary: identity, trading status, and
 * calendar. A cutoff honoured by two of three still leaks.
 */
class AsKnownReplayBoundaryTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const CUTOFF = '2026-04-15 00:00:00';

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

    /**
     * A listing recorded after the cutoff is invisible to a replay run as of that cutoff, even
     * though it is visible today. Without this a backtest would trade an instrument the platform
     * had not yet heard of.
     */
    public function test_identity_recorded_after_the_cutoff_is_invisible(): void
    {
        $this->seedTicker(1, 'EARLY', '2023-01-02', '2023-01-02 00:00:00');
        $this->seedTicker(2, 'LATE', '2023-01-02', '2026-06-01 00:00:00');

        $repository = new TemporalIdentityRepository();

        $asKnown = $repository->universeAsOf('2026-03-24', self::CUTOFF);
        $today = $repository->universeAsOf('2026-03-24');

        $asKnownCodes = array_column($asKnown, 'ticker_code');
        $todayCodes = array_column($today, 'ticker_code');

        $this->assertContains('EARLY', $asKnownCodes);
        $this->assertNotContains('LATE', $asKnownCodes, 'a listing recorded in June cannot be known in April');
        $this->assertContains('LATE', $todayCodes, 'and it is visible without a cutoff, so the fixture is real');
    }

    /**
     * A corporate action recorded after the cutoff is invisible — the root that had no cutoff at
     * all until `F-028`.
     *
     * This is the leak with the widest reach in the real corpus: all 530 production actions were
     * entered between 2026-06-07 and 2026-07-30 against a dataset starting 2023-01-02, so before
     * this every as-known replay saw every action regardless of its cutoff. The two assertions are
     * both load-bearing — the second proves the row exists at all, so a filter that hid everything
     * could not masquerade as a pass.
     */
    public function test_a_corporate_action_recorded_after_the_cutoff_is_invisible(): void
    {
        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 7,
            'ticker_code' => 'LATE',
            'action_date' => '2026-03-24',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'manual_corporate_action_csv',
            'recorded_at' => '2026-06-01 00:00:00',
            'created_at' => '2026-06-01 00:00:00',
        ]);

        $repository = new EventRiskSourceRepository();

        $asKnown = $repository->resolveEventRiskContextForTickerIds([7], '2026-03-24', self::CUTOFF);
        $today = $repository->resolveEventRiskContextForTickerIds([7], '2026-03-24');

        $this->assertSame(
            0,
            (int) ($asKnown[7]['corporate_action_flag'] ?? 0),
            'an action recorded in June cannot be known in April'
        );
        $this->assertSame(
            1,
            (int) ($today[7]['corporate_action_flag'] ?? 0),
            'and it is visible without a cutoff, so the fixture is real'
        );
    }

    /**
     * The pre-Stage-8 mutable factor table is no longer an admissible analytical root. Keeping the
     * method fail-closed preserves a loud boundary for callers that have not migrated to the
     * publication-bound immutable factor decision set.
     */
    public function test_legacy_adjustment_factor_resolution_is_fail_closed(): void
    {
        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 8,
            'ticker_code' => 'SPLIT',
            'action_date' => '2026-03-24',
            'ex_date' => '2026-03-24',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'manual_corporate_action_csv',
            'price_adjustment_factor' => 0.5,
            'volume_adjustment_factor' => 2.0,
            'adjustment_source' => 'EXCHANGE_ANNOUNCEMENT',
            'recorded_at' => '2026-06-01 00:00:00',
            'created_at' => '2026-06-01 00:00:00',
        ]);

        $repository = new EventRiskSourceRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('FACTOR_SET_CONTEXT_REQUIRED');

        $repository->resolveAdjustmentFactorsForTickerIds([8], '2026-01-01', '2026-12-31', self::CUTOFF);
    }

    /**
     * A configuration recorded after the cutoff is invisible, and asking as-known never creates one.
     *
     * The creating branch is right for a real run — it executes under today's config — and wrong
     * for a replay, where find-or-create would answer with a configuration that did not exist yet.
     * The third assertion is the one that matters most: the row count must be unchanged, because a
     * lookup that quietly inserts would manufacture exactly the knowledge the cutoff withholds.
     */
    public function test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        $early = $repository->resolveForRun('2026-03-24');
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $early['config_snapshot_id'])
            ->update(['recorded_at' => '2026-01-10 00:00:00']);

        config(['market_data.indicators.roc_lookback_days' => 21]);
        $late = $repository->resolveForRun('2026-03-24');
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $late['config_snapshot_id'])
            ->update(['recorded_at' => '2026-06-01 00:00:00']);

        $countBefore = (int) DB::table('md_config_snapshots')->count();

        $asKnown = $repository->resolveForRun('2026-03-24', self::CUTOFF);

        $this->assertSame(
            (int) $early['config_snapshot_id'],
            (int) $asKnown['config_snapshot_id'],
            'a configuration recorded in June cannot be known in April'
        );
        $this->assertNotSame((int) $late['config_snapshot_id'], (int) $asKnown['config_snapshot_id']);
        $this->assertSame(
            $countBefore,
            (int) DB::table('md_config_snapshots')->count(),
            'an as-known resolution must look up, never insert'
        );
    }

    public function test_an_as_known_config_resolution_refuses_rather_than_inventing_one(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $repository->resolveForRun('2026-03-24');
        DB::table('md_config_snapshots')->update(['recorded_at' => '2026-06-01 00:00:00']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CONFIG_SNAPSHOT_NOT_KNOWN_AT_CUTOFF');

        $repository->resolveForRun('2026-03-24', self::CUTOFF);
    }

    /**
     * A suspension recorded after the cutoff cannot describe an earlier replay. Knowing about it
     * would let a backtest avoid a loss it had no way to foresee.
     */
    public function test_a_status_revision_recorded_after_the_cutoff_is_invisible(): void
    {
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => 5150,
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2026-03-01 00:00:00',
            'effective_to' => null,
            // Recorded two months after it took effect, which is ordinary: the exchange announces,
            // the platform records later. The cutoff must respect when it was recorded.
            'recorded_at' => '2026-05-02 00:00:00',
            'source_ref' => 'test_fixture',
        ]);

        $repository = new TemporalTradingStatusRepository();

        $asKnown = $repository->resolveForListing(5150, '2026-03-24', self::CUTOFF);
        $today = $repository->resolveForListing(5150, '2026-03-24');

        $this->assertSame('UNKNOWN', $asKnown['status_code'], 'not yet recorded is not yet knowable');
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $asKnown['reason_code']);
        $this->assertSame('SUSPENSION', $today['status_code'], 'and it resolves without a cutoff');
    }

    /**
     * A calendar revision recorded after the cutoff is invisible too. The calendar is the root of
     * bar expectation, so a later correction leaking backwards would silently change which bars a
     * replay believed were owed.
     */
    public function test_a_calendar_revision_recorded_after_the_cutoff_is_invisible(): void
    {
        DB::table('market_calendar')->updateOrInsert(['cal_date' => '2026-03-24'], [
            'is_trading_day' => 1,
            'session_close_time' => '16:00',
            'provenance_tier' => 'VERIFIED',
            'source' => 'test_fixture',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00',
        ]);

        $repository = new MarketCalendarRepository();
        $repository->sessionContext('2026-03-24');

        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-24')->update([
            'recorded_at' => '2026-06-01 00:00:00',
        ]);

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_EVIDENCE_MISSING/');
        $repository->sessionContext('2026-03-24', self::CUTOFF);
    }

    /**
     * The three cutoffs are independent. A replay honouring identity but not status would still
     * leak, so the guard asserts every root accepts the parameter rather than assuming symmetry.
     */
    /**
     * Every temporal root, not a sample of three.
     *
     * The earlier version of this guard listed identity, status and calendar. The exit gate names
     * five kinds of revision — identity, status, event, config, factor — and by 2026-08-11 nine
     * repository methods already accepted a cutoff while only three were pinned here. Two roots had
     * drifted out of sight entirely: the event/factor root carried no cutoff at all (`F-028`), and
     * sector membership became temporal on 2026-08-10 without anyone adding it (`F-029`).
     *
     * A hand-written list cannot notice the next omission, which is why the companion test below
     * makes an unregistered root fail rather than pass quietly.
     */
    /**
     * The five revision kinds the stage-18 exit gate names, each mapped to the root that must be
     * able to answer as of a cutoff.
     *
     * This list is derived from the contract, not from the code. The sweep below catches a root
     * that gains a cutoff without being registered; it cannot catch a root the contract requires
     * that simply never grew one — which is how MarketDataConfigSnapshotRepository::resolveForRun
     * stayed invisible until `F-035`. Reading the requirement from the contract side closes that
     * direction: a kind with no cutoff-bearing root fails here.
     */
    public static function contractRequiredRoots(): array
    {
        return [
            'identity' => [TemporalIdentityRepository::class, 'universeAsOf'],
            'status' => [TemporalTradingStatusRepository::class, 'resolveForListing'],
            'event' => [EventRiskSourceRepository::class, 'resolveEventRiskContextForTickerIds'],
            'factor' => [EventRiskSourceRepository::class, 'resolveAdjustmentFactorsForTickerIds'],
            'config' => [MarketDataConfigSnapshotRepository::class, 'resolveForRun'],
        ];
    }

    public function test_every_revision_kind_named_by_the_exit_gate_has_a_cutoff_bearing_root(): void
    {
        foreach (self::contractRequiredRoots() as $kind => [$class, $method]) {
            $this->assertTrue(
                method_exists($class, $method),
                $kind.' has no root: '.$class.'::'.$method
            );

            $accepts = false;
            foreach ((new ReflectionMethod($class, $method))->getParameters() as $parameter) {
                if ($parameter->getName() === 'knownAt') {
                    $accepts = true;
                    break;
                }
            }

            $this->assertTrue(
                $accepts,
                $kind.' revisions must be resolvable as of a cutoff, but '.$class.'::'.$method.' accepts none'
            );
        }
    }

    public static function temporalRoots(): array
    {
        return [
            [MarketDataConfigSnapshotRepository::class, 'resolveForRun', 1],
            [TemporalIdentityRepository::class, 'universeAsOf', 1],
            [TemporalIdentityRepository::class, 'readProjectedUniverseAsOf', 1],
            [TemporalIdentityRepository::class, 'resolveProviderContext', 3],
            [TemporalIdentityRepository::class, 'resolveByTickerCodes', 2],
            [TemporalTradingStatusRepository::class, 'resolveForListing', 2],
            [MarketCalendarRepository::class, 'sessionContext', 1],
            [MarketCalendarRepository::class, 'assertCompletedRegularSession', 1],
            [TickerMasterRepository::class, 'resolveTemporalContextsByCodes', 2],
            [TickerMasterRepository::class, 'getUniverseForTradeDate', 1],
            [TickerMasterRepository::class, 'getProjectedUniverseForTradeDate', 1],
            [SectorClassificationRepository::class, 'resolveSectorCodesForTickerIds', 3],
            [SectorClassificationRepository::class, 'resolveSectorContextForTickerIds', 3],
            [EventRiskSourceRepository::class, 'resolveEventRiskContextForTickerIds', 2],
            [EventRiskSourceRepository::class, 'resolveCorporateActionContaminationForTickerIds', 2],
            [EventRiskSourceRepository::class, 'resolveAdjustmentFactorsForTickerIds', 3],
            [EventRiskSourceRepository::class, 'suspendedTickerIdsAsOf', 2],
            [EventRiskSourceRepository::class, 'expectationUnknownTickerIdsAsOf', 2],
            [CorpusAdmissionRepository::class, 'historyStartDateFor', 1],
        ];
    }

    public function test_every_temporal_root_accepts_a_knowledge_cutoff(): void
    {
        foreach (self::temporalRoots() as [$class, $method, $cutoffPosition]) {
            $parameters = (new ReflectionMethod($class, $method))->getParameters();

            $this->assertArrayHasKey($cutoffPosition, $parameters, $class.'::'.$method.' must accept a cutoff');
            $this->assertSame('knownAt', $parameters[$cutoffPosition]->getName(), $class.'::'.$method);
        }
    }

    /**
     * Fail-by-default: a method that gains a knowledge cutoff must be registered above.
     *
     * Reflection sweeps the market-data persistence layer for any public method carrying a
     * `knownAt` parameter and requires it to appear in temporalRoots(). Adding a new temporal root
     * without registering it now breaks this test instead of silently widening the surface that
     * nothing pins — which is exactly how sector membership slipped through.
     */
    public function test_a_new_temporal_root_cannot_be_added_without_being_registered(): void
    {
        $registered = [];
        foreach (self::temporalRoots() as [$class, $method, $position]) {
            $registered[$class.'::'.$method] = true;
        }

        $unregistered = [];
        foreach (glob(__DIR__.'/../../../app/Infrastructure/Persistence/MarketData/*.php') as $path) {
            $class = 'App\\Infrastructure\\Persistence\\MarketData\\'.basename($path, '.php');
            if (! class_exists($class)) {
                continue;
            }

            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class) {
                    continue;
                }

                foreach ($method->getParameters() as $parameter) {
                    if ($parameter->getName() !== 'knownAt') {
                        continue;
                    }

                    $key = $class.'::'.$method->getName();
                    if (! isset($registered[$key])) {
                        $unregistered[] = $key;
                    }
                }
            }
        }

        $this->assertSame(
            [],
            array_values(array_unique($unregistered)),
            'these methods accept a knowledge cutoff but are not registered in temporalRoots()'
        );
    }

    private function seedTicker(int $tickerId, string $code, string $listedDate, string $recordedAt): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $code,
            'company_name' => $code.' Tbk',
            'is_active' => 1,
            'listed_date' => $listedDate,
            'created_at' => $recordedAt,
        ]);
    }
}
