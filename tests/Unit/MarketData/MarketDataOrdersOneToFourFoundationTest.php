<?php

use App\Domain\MarketData\MarketDataScope;
use App\Application\MarketData\Ports\SourceObservationRecorder;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataOrdersOneToFourFoundationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        config()->set('market_data.scope.dataset_start', '2023-01-02');
        config()->set('market_data.scope.operational_start_date', null);
        config()->set('market_data.source.api.provider', 'yahoo_finance');
        config()->set('market_data.source.api.source_name', 'YAHOO_FINANCE');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_scope_enforces_intentional_dataset_start_without_claiming_operational_freshness(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        $this->assertSame('2023-01-02', $scope->datasetStart());
        $this->assertNull($scope->operationalStartDate());
        $this->assertSame('DEVELOPMENT', $scope->stateFor('2026-03-20'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MARKET_DATA_REQUEST_BEFORE_DATASET_START');
        $scope->assertRequestedDate('2023-01-01');
    }

    public function test_config_snapshot_is_deterministic_immutable_and_redacts_credentials(): void
    {
        config()->set('market_data.source.api.auth_token', 'super-secret-token');
        $repository = new MarketDataConfigSnapshotRepository();

        $first = $repository->resolveForRun('2026-03-20');
        $second = $repository->resolveForRun('2026-03-20');

        $this->assertSame($first['config_snapshot_id'], $second['config_snapshot_id']);
        $this->assertSame($first['config_hash'], hash('sha256', $first['resolved_config_json']));
        $this->assertStringNotContainsString('super-secret-token', $first['resolved_config_json']);
        $this->assertStringContainsString('[REDACTED:', $first['resolved_config_json']);
        $this->assertCount(1, DB::table('md_config_snapshots')->get());
    }

    public function test_runtime_cannot_silently_expand_canonical_market_scope(): void
    {
        config()->set('market_data.scope.market_segment', 'NEGOTIATED');
        try {
            MarketDataScope::fromConfig();
            $this->fail('Non-canonical market segment must be rejected.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('MARKET_DATA_SCOPE_INVALID', $e->getMessage());
        } finally {
            config()->set('market_data.scope.market_segment', 'REGULAR');
        }
    }

    public function test_temporal_identity_ignores_current_active_flag_and_resolves_explicit_provider_mapping(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => 11,
            'ticker_code' => 'BBCA',
            'company_name' => 'Bank Central Asia',
            'listed_date' => '2000-05-31',
            'delisted_date' => null,
            'exchange_code' => 'IDX',
            'is_active' => 0,
            'created_at' => '2023-01-02 00:00:00',
            'updated_at' => '2026-03-20 00:00:00',
        ]);

        $repository = new TemporalIdentityRepository();
        $universe = $repository->universeAsOf('2026-03-20', '2026-03-20 23:59:59');
        $mapping = $repository->resolveProviderContext('BBCA', 'yahoo_finance', '2026-03-20');

        $this->assertSame(['BBCA'], array_column($universe, 'ticker_code'));
        $this->assertSame(11, $universe[0]['ticker_id']);
        $this->assertSame($universe[0]['listing_id'], $mapping['listing_id']);
        $this->assertSame('BBCA.JK', $mapping['provider_symbol']);
        $this->assertSame('temporal_provider_mapping_v1', $mapping['mapping_revision']);
    }

    public function test_calendar_and_trading_status_are_point_in_time_and_fail_safe_on_missing_evidence(): void
    {
        Carbon::setTestNow('2026-03-21 09:00:00');
        DB::table('market_calendar')->insert([
            'cal_date' => '2026-03-20',
            'is_trading_day' => 1,
            'session_open_time' => '09:00',
            'session_close_time' => '16:00',
            'source' => 'operator_bootstrap',
            'created_at' => '2026-03-01 00:00:00',
            'updated_at' => '2026-03-01 00:00:00',
        ]);
        DB::table('tickers')->insert([
            'ticker_id' => 12,
            'ticker_code' => 'TLKM',
            'company_name' => 'Telkom',
            'listed_date' => '1995-11-14',
            'exchange_code' => 'IDX',
            'is_active' => 1,
            'created_at' => '2023-01-02 00:00:00',
            'updated_at' => '2023-01-02 00:00:00',
        ]);

        $calendar = (new MarketCalendarRepository())->assertCompletedRegularSession('2026-03-20');
        $listing = (new TemporalIdentityRepository())->universeAsOf('2026-03-20')[0];
        $statuses = new TemporalTradingStatusRepository();

        $this->assertSame('COMPLETED', $calendar['session_state']);
        $this->assertSame('UNKNOWN', $statuses->resolveForListing($listing['listing_id'], '2026-03-20')['status_code']);

        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => $listing['listing_id'],
            'status_code' => 'ACTIVE',
            'bar_expectation_state' => 'BAR_EXPECTED',
            'board_code' => 'RG',
            'authority_class' => 'EXCHANGE_OFFICIAL',
            'full_session_verified' => 1,
            'effective_from' => '2026-03-20 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2026-03-20 17:00:00',
            'retracted_at' => null,
            'source_observation_id' => null,
            'supersedes_revision_id' => null,
            'source_ref' => 'idx-status:2026-03-20',
            'verification_state' => 'VERIFIED',
            'observed_at' => '2026-03-20 16:00:00',
        ]);

        $this->assertSame('UNKNOWN', $statuses->resolveForListing($listing['listing_id'], '2026-03-20', '2026-03-20 16:59:59')['status_code']);
        $this->assertSame('ACTIVE', $statuses->resolveForListing($listing['listing_id'], '2026-03-20', '2026-03-20 17:00:00')['status_code']);
    }

    public function test_source_observation_is_append_only_redacted_and_manifest_bound(): void
    {
        $repository = new SourceObservationRepository();
        $capture = $repository->capture([
            'run_id' => 77,
            'requested_trade_date' => '2026-03-20',
            'source_mode' => 'api_free',
            'source_name' => 'YAHOO_FINANCE',
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'BBCA.JK',
            'sanitized_request_identity' => 'https://example.test/chart/BBCA.JK',
            'response_status' => 200,
            'content_type' => 'application/json',
            'adapter_version' => 'test-adapter-v1',
            'payload' => '{"token":"must-not-persist","chart":{"result":[]}}',
        ]);
        $accepted = $repository->recordOutcome($capture, 'ACCEPTED');

        $this->assertNotSame($capture['source_observation_id'], $accepted['source_observation_id']);
        $this->assertSame('CAPTURED', DB::table('md_source_observations')->where('source_observation_id', $capture['source_observation_id'])->value('outcome_state'));
        $this->assertSame('ACCEPTED', DB::table('md_source_observations')->where('source_observation_id', $accepted['source_observation_id'])->value('outcome_state'));
        $this->assertStringNotContainsString('must-not-persist', (string) DB::table('md_source_observations')->where('source_observation_id', $capture['source_observation_id'])->value('bounded_payload_body'));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $repository->manifestHashForRun(77));
    }

    public function test_calendar_completion_appends_revision_instead_of_freezing_scheduled_state(): void
    {
        DB::table('market_calendar')->insert([
            'cal_date' => '2026-03-20', 'is_trading_day' => 1,
            'session_open_time' => '09:00', 'session_close_time' => '16:00',
            'source' => 'operator_bootstrap', 'created_at' => '2026-03-01 00:00:00',
            'updated_at' => '2026-03-01 00:00:00',
        ]);
        $calendar = new MarketCalendarRepository();

        Carbon::setTestNow('2026-03-20 15:00:00');
        $this->assertSame('SCHEDULED', $calendar->sessionContext('2026-03-20')['session_state']);

        Carbon::setTestNow('2026-03-20 17:00:00');
        $completed = $calendar->assertCompletedRegularSession('2026-03-20');

        $this->assertSame('COMPLETED', $completed['session_state']);
        $this->assertSame(2, DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-20')->count());
        $this->assertNotNull(DB::table('md_market_calendar_revisions')->where('session_state', 'COMPLETED')->value('supersedes_revision_id'));
    }

    public function test_yahoo_missing_adjusted_close_remains_null_and_capabilities_disclose_permanent_gaps(): void
    {
        config()->set('market_data.source.api.endpoint_template', 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}');
        $timestamp = Carbon::parse('2026-03-20 12:00:00', 'Asia/Jakarta')->timestamp;
        $adapter = new PublicApiEodBarsAdapter(function () use ($timestamp) {
            return [
                'status' => 200,
                'headers' => ['Content-Type: application/json'],
                'body' => json_encode(['chart' => ['result' => [[
                    'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                    'timestamp' => [$timestamp],
                    'indicators' => ['quote' => [[
                        'open' => [100], 'high' => [110], 'low' => [99], 'close' => [108], 'volume' => [1000],
                    ]]],
                ]]]]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-20', 'api', ['BBCA']);
        $capabilities = $adapter->capabilities();

        $this->assertNull($rows[0]['adj_close']);
        $this->assertFalse($capabilities['provides_actual_traded_value']);
        $this->assertFalse($capabilities['provides_official_board_or_trading_status']);
        $this->assertContains('PROVIDER_ADJ_CLOSE', $capabilities['forbidden_canonical_basis']);
    }

    public function test_acquisition_fails_closed_when_raw_observation_cannot_be_persisted(): void
    {
        config()->set('market_data.source.api.endpoint_template', 'https://example.test/eod/{date}');
        config()->set('market_data.source.api.provider', 'generic');
        $recorder = new class implements SourceObservationRecorder {
            public function capture(array $envelope) { throw new RuntimeException('database unavailable'); }
            public function recordOutcome(array $capture, $outcomeState, $reasonCode = null, array $context = []) { return []; }
            public function recordTransportFailure(array $envelope, $reasonCode) { return []; }
        };
        $adapter = new PublicApiEodBarsAdapter(function () {
            return ['status' => 200, 'headers' => [], 'body' => '{"rows":[]}'];
        }, null, null, $recorder);

        try {
            $adapter->fetchOrLoadEodBars('2026-03-20', 'api', ['BBCA']);
            $this->fail('Acquisition must fail when raw capture persistence fails.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('SOURCE_OBSERVATION_PERSISTENCE_FAILED', $e->reasonCode());
        }
    }
}
