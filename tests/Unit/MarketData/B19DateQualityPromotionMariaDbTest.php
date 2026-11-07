<?php

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/** Actual provider adapter -> immutable observation -> canonical -> promote quality world. */
class B19DateQualityPromotionMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); }
    protected function tearDown(): void { Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    public function test_flat_zero_volume_finding_holds_promotion_while_equal_share_remains_readable(): void
    {
        // Bounded configured population: 1/5 neighbours, 2/5 target, factor 2;
        // both are below the independently declared 0.90 absolute thresholds.
        config(['market_data.quality_gates.date_level_anomaly.minimum_rows' => 5,
            'market_data.quality_gates.date_level_anomaly.zero_volume_share_max' => 0.90,
            'market_data.quality_gates.date_level_anomaly.flat_bar_share_max' => 0.90]);
        $selectedDate = '2026-03-16';
        $requests = [];
        $barsBefore = null;
        $historyBefore = null;
        $baseline = null;
        $imports = [];
        $world = R0025SyntheticV2World::build([
            'profile' => 'market-data-row-hash/v1',
            'http_fetcher' => function ($url) use (&$selectedDate, &$requests) {
                preg_match('/E048(\d+)/', $url, $match);
                $ordinal = isset($match[1]) ? (int) $match[1] : 1;
                $symbol = $ordinal === 1 ? 'SYNV2.JK' : sprintf('E048%02d.JK', $ordinal);
                $flat = $ordinal <= ($selectedDate === '2026-03-23' ? 2 : 1);
                $requests[] = [$selectedDate, $symbol];
                $body = ['chart' => ['result' => [[
                    'meta' => ['symbol' => $symbol, 'exchangeTimezoneName' => 'Asia/Jakarta'],
                    'timestamp' => [(new DateTimeImmutable($selectedDate.' 09:00:00', new DateTimeZone('Asia/Jakarta')))->getTimestamp()],
                    'indicators' => ['quote' => [[
                        'open' => [100], 'high' => [$flat ? 100 : 110], 'low' => [$flat ? 100 : 95],
                        'close' => [$flat ? 100 : 105], 'volume' => [$flat ? 0 : 1000],
                    ]]],
                ]], 'error' => null]];
                return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($body)];
            },
            'run_workflow' => function ($pipeline) use (&$selectedDate, &$barsBefore, &$historyBefore, &$baseline, &$imports) {
                for ($i = 2; $i <= 5; $i++) {
                    DB::table('tickers')->insert([
                        'ticker_id' => 975099 + $i, 'ticker_code' => sprintf('E048%02d', $i),
                        'company_name' => 'Independent E048 listing '.$i, 'is_active' => 1,
                        'listed_date' => '2020-01-01', 'created_at' => '2020-01-01 00:00:00', 'updated_at' => '2020-01-01 00:00:00',
                    ]);
                }
                (new TemporalIdentityRepository())->ensureLegacyProjection();
                foreach (['2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20', '2026-03-23'] as $date) {
                    $selectedDate = $date;
                    $imports[$date] = $pipeline->importDaily($date, 'api');
                }
                $baseline = $pipeline->promoteDaily('2026-03-20', 'api', $imports['2026-03-20']->run_id);
                $this->assertSame('READABLE', $baseline->publishability_state, 'Equal 1/5 share is a negative concentration control.');
                $historyBefore = DB::table('eod_bars_history')->where('publication_id', $baseline->publication_id)->orderBy('ticker_id')->get()->toJson();
                $barsBefore = $this->canonicalIdentity('2026-03-23');
                return $pipeline->promoteDaily('2026-03-23', 'api', $imports['2026-03-23']->run_id);
            },
        ]);
        $run = DB::table('eod_runs')->where('run_id', $world['run_id'])->first();
        $this->assertSame('PASS', $run->coverage_gate_state);
        $this->assertSame('BLOCKED', $run->quality_gate_state, 'A date finding must survive coverage PASS and block final quality.');
        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('2026-03-20', $run->trade_date_effective);
        $this->assertNull($run->sealed_at);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->exists());
        $this->assertSame((int) $baseline->publication_id, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->value('publication_id'));
        $this->assertSame($historyBefore, DB::table('eod_bars_history')->where('publication_id', $baseline->publication_id)->orderBy('ticker_id')->get()->toJson());
        $this->assertSame($barsBefore, $this->canonicalIdentity('2026-03-23'));
        $events = DB::table('eod_run_events')->where('run_id', $run->run_id)->orderBy('event_id')->get();
        $reports = [];
        foreach ($events as $event) {
            $context = json_decode($event->event_payload_json ?? '{}', true);
            if (isset($context['date_level_anomaly'])) $reports[] = [$event, $context['date_level_anomaly']];
        }
        $this->assertNotEmpty($reports);
        [$event, $report] = $reports[0];
        $this->assertSame('PUBLISH_BARS', $event->stage);
        $this->assertSame('STAGE_COMPLETED', $event->event_type);
        $this->assertSame('FINDING', $report['date_level_anomaly_state']);
        $this->assertSame(0.4, $report['zero_volume_share']);
        $this->assertSame(0.2, $report['zero_volume_neighbour_baseline']);
        $this->assertContains('ZERO_VOLUME_SHARE_ELEVATED_AGAINST_NEIGHBOURS', $report['date_level_anomaly_findings']);
        $this->assertNotContains('ZERO_VOLUME_SHARE_ABOVE_THRESHOLD', $report['date_level_anomaly_findings']);
        $this->assertSame((int) $run->config_snapshot_id, $report['date_level_anomaly_config_snapshot_id']);
        $this->assertSame($run->config_hash, $report['date_level_anomaly_config_hash']);
        $this->assertSame(5, $report['date_level_anomaly_thresholds']['minimum_rows']);
        $this->assertSame(0.9, $report['date_level_anomaly_thresholds']['zero_volume_share_max']);
        $this->assertSame(['PUBLISH_BARS', 'BUILD_ELIGIBILITY'], array_map(fn ($r) => $r[0]->stage, $reports));
        foreach ($reports as [$reportedEvent, $reportedAnomaly]) {
            $payload = json_decode($reportedEvent->event_payload_json, true);
            $this->assertSame('BLOCKED', $payload['quality_gate_state']);
            $this->assertSame('FINDING', $reportedAnomaly['date_level_anomaly_state']);
            $this->assertSame((int) $run->run_id, (int) $reportedEvent->run_id);
            $this->assertSame('2026-03-23', $reportedEvent->trade_date_requested);
            $this->assertSame(5, $reportedAnomaly['date_level_anomaly_delivered_count']);
        }
        $this->assertSame(5, DB::table('eod_bars_history')->where('publication_id', $run->publication_id)->count(),
            'The eligibility-stage quality check exercised a complete five-row candidate history.');
        $equalEvent = DB::table('eod_run_events')->where('run_id', $baseline->run_id)->where('stage', 'PUBLISH_BARS')->where('event_type', 'STAGE_COMPLETED')->first();
        $equalReport = json_decode($equalEvent->event_payload_json, true)['date_level_anomaly'];
        $this->assertSame('CLEAN', $equalReport['date_level_anomaly_state']);
        $this->assertSame(0.2, $equalReport['zero_volume_share']);
        $this->assertSame(0.2, $equalReport['zero_volume_neighbour_baseline']);
        $this->assertSame(30, count($requests));
        foreach (json_decode($barsBefore, true) as $bar) {
            $observation = DB::table('md_source_observation_rows')->where('source_observation_id', $bar['source_observation_id'])->where('listing_id', $bar['listing_id'])->where('trade_date', '2026-03-23')->first();
            $this->assertNotNull($observation);
            $this->assertSame((float) $bar['close'], (float) $observation->close_value);
        }
        if (getenv('E048_CAPTURE')) file_put_contents(getenv('E048_CAPTURE').'/quality_persisted.json', json_encode([
            'database_version' => DB::selectOne('SELECT VERSION() AS version')->version,
            'run' => $run, 'imports' => array_map(fn ($r) => $r->run_id, $imports),
            'reports' => $reports, 'canonical_before_and_after' => json_decode($barsBefore, true),
            'provider_request_identities' => $requests,
        ], JSON_PRETTY_PRINT));
    }

    private function canonicalIdentity(string $date): string
    {
        return DB::table('eod_bars')->where('trade_date', $date)->orderBy('ticker_id')
            ->get(['ticker_id', 'listing_id', 'trade_date', 'open', 'high', 'low', 'close', 'volume', 'source_observation_id'])->toJson();
    }
}
