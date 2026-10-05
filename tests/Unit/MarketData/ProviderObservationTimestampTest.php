<?php

use App\Application\MarketData\Services\SemanticNestedIdentityService;
use App\Application\MarketData\Services\SemanticObservationIdentityService;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * F-MD-B18-A002-027 / D-MD-B18-A002-014 (Q6 = B): the provider chart-series instant is a source fact of the immutable observation envelope and of the
 * observation manifest; the platform acquisition time is a separate fact; the canonical bar keeps a NULL source timestamp; and the acquisition clock is
 * never stored under the provider-timestamp name.
 */
class ProviderObservationTimestampTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private const INSTANT = 1774231200; // 2026-03-23 02:00:00 UTC = 2026-03-23 09:00:00 Asia/Jakarta

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    private function freshTransaction(): void
    {
        $db = DB::connection();
        $db->rollBack();
        $db->beginTransaction();
        Carbon::setTestNow();
    }

    private function frozenResponse(): string
    {
        return (string) file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
    }

    private function chart(array $instants, string $timezone = 'Asia/Jakarta'): string
    {
        $n = count($instants);

        return json_encode(['chart' => ['result' => [['meta' => ['symbol' => 'SYNV2.JK', 'exchangeTimezoneName' => $timezone], 'timestamp' => $instants,
            'indicators' => ['quote' => [['open' => array_fill(0, $n, 100), 'high' => array_fill(0, $n, 110), 'low' => array_fill(0, $n, 95), 'close' => array_fill(0, $n, 105), 'volume' => array_fill(0, $n, 1000)]]]]], 'error' => null]]);
    }

    /** @return string|null */
    private function extract($payload, array $context)
    {
        $adapter = new PublicApiEodBarsAdapter(static function () { return ['status' => 200, 'body' => '', 'headers' => []]; });
        $method = new \ReflectionMethod(PublicApiEodBarsAdapter::class, 'providerObservationTimestamp');
        $method->setAccessible(true);

        return $method->invoke($adapter, $payload, $context);
    }

    /** @return array<string,mixed> the envelope, normalized, bar and semantic facts of one built world */
    private function facts(array $layout = []): array
    {
        $w = R0025SyntheticV2World::build($layout);
        $db = DB::connection();
        $hash = hash('sha256', (string) ($layout['tamper_response'] ?? $this->frozenResponse()));
        $envelopes = $db->table('md_source_observations')->where('payload_hash', $hash)->orderBy('source_observation_id')->get()->all();
        $ids = array_map(static function ($r) { return (int) $r->source_observation_id; }, $envelopes);
        $rows = $db->table('md_source_observation_rows')->whereIn('source_observation_id', $ids)->get()->all();
        $bars = $db->table('eod_bars')->where('run_id', $w['run_id'])->get()->all();
        $lineage = $db->table('md_publication_lineage_bindings')->where('publication_id', $w['publication_id'])->first();
        $nested = [];
        foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $member => $column) {
            $nested[$member] = $lineage->{$column};
        }
        $publication = $db->table('eod_publications')->where('publication_id', $w['publication_id'])->first();

        return ['world' => $w, 'envelopes' => $envelopes, 'ids' => $ids, 'rows' => $rows, 'bars' => $bars, 'nested' => $nested,
            'artifacts' => ['bars' => $publication->bars_batch_hash, 'indicators' => $publication->indicators_batch_hash, 'eligibility' => $publication->eligibility_batch_hash]];
    }

    // ------------------------------------------------------------------------------- the extraction rule

    public function test_the_provider_instant_of_a_single_date_request_is_rendered_in_the_platform_timezone(): void
    {
        $context = ['trade_date' => '2026-03-23', 'ticker_code' => 'SYNV2'];
        $this->assertSame('2026-03-23 09:00:00', $this->extract($this->frozenResponse(), $context));
        // 03:00 Jakarta on the trade date is still 20:00 UTC of the day before: the exchange-local date decides, not the UTC date
        $this->assertSame('2026-03-23 03:00:00', $this->extract($this->chart([self::INSTANT - 6 * 3600]), $context));
        // the other instants of a chart request window (the neighbouring sessions) are not the observed date
        $this->assertSame('2026-03-23 09:00:00', $this->extract($this->chart([self::INSTANT - 86400, self::INSTANT, self::INSTANT + 86400]), $context));
    }

    public function test_there_is_no_single_provider_instant_where_the_payload_cannot_give_one(): void
    {
        $context = ['trade_date' => '2026-03-23', 'ticker_code' => 'SYNV2'];
        $this->assertNull($this->extract($this->frozenResponse(), ['source_acquisition_mode' => 'range_window', 'trade_date' => '2026-03-23']), 'a range request observes several dates');
        $this->assertNull($this->extract($this->frozenResponse(), ['ticker_code' => 'SYNV2']), 'a request with no trade date');
        $this->assertNull($this->extract('<html>error</html>', $context), 'a body that is not JSON');
        $this->assertNull($this->extract('{"chart":{"result":null,"error":{"code":"Not Found"}}}', $context), 'a chart error body');
        $this->assertNull($this->extract('{"quotes":[{"time":1774231200}]}', $context), 'a payload that is not a chart series');
        $this->assertNull($this->extract($this->chart([self::INSTANT], 'America/New_York'), $context), 'another exchange timezone is not the market boundary');
        $this->assertNull($this->extract($this->chart([self::INSTANT, self::INSTANT + 60]), $context), 'two instants on the trade date are ambiguous');
        $this->assertNull($this->extract($this->chart([self::INSTANT + 86400]), $context), 'no instant on the trade date');
        $this->assertNull($this->extract($this->chart([]), $context), 'an empty series');
        $this->assertNull($this->extract(null, $context), 'a failed transport has no payload');
    }

    // ------------------------------------------------------------------------------- the real path

    public function test_the_envelope_binds_the_provider_instant_and_the_acquisition_clock_separately_and_the_bar_keeps_none(): void
    {
        $f = $this->facts();
        $this->assertCount(2, $f['envelopes'], 'the capture and the accepted envelope rows');
        foreach ($f['envelopes'] as $envelope) {
            $this->assertSame('2026-03-23 09:00:00', (string) $envelope->source_timestamp, 'the provider instant is bound to the envelope');
            $this->assertSame('2026-03-25 10:30:00', (string) $envelope->acquired_at, 'the platform acquisition clock stays in acquired_at');
            $this->assertNotSame((string) $envelope->source_timestamp, (string) $envelope->acquired_at, 'the two facts are not conflated');
        }
        $this->assertCount(1, $f['rows']);
        $this->assertNull($f['rows'][0]->source_timestamp, 'the acquisition clock is never stored as the provider timestamp of a normalized row');
        $this->assertCount(1, $f['bars']);
        $this->assertNull($f['bars'][0]->source_timestamp, 'Q6 = B: the canonical bar keeps a NULL source timestamp');
        $this->assertSame('2026-03-25 10:30:00', (string) $f['bars'][0]->acquired_at);
    }

    public function test_an_envelope_without_a_single_provider_instant_has_none_and_never_the_acquisition_clock(): void
    {
        // Two series instants on the trade date: there is no single observed instant, so the envelope has none. The payload still carries both.
        $ambiguous = str_replace('"timestamp":['.self::INSTANT.']', '"timestamp":['.self::INSTANT.','.(self::INSTANT + 60).']', $this->frozenResponse());
        $ambiguous = str_replace(['"open":[100]', '"high":[110]', '"low":[95]', '"close":[105]', '"volume":[1000]'], ['"open":[100,100]', '"high":[110,110]', '"low":[95,95]', '"close":[105,105]', '"volume":[1000,1000]'], $ambiguous);
        $this->assertNotSame($this->frozenResponse(), $ambiguous);
        $f = $this->facts(['tamper_response' => $ambiguous]);
        $this->assertNotSame([], $f['envelopes'], 'the world did not capture the response');
        foreach ($f['envelopes'] as $envelope) {
            $this->assertNull($envelope->source_timestamp, 'no single provider instant: the envelope has none, and never the acquisition clock');
            $this->assertSame('2026-03-25 10:30:00', (string) $envelope->acquired_at);
        }
    }
    public function test_a_changed_provider_instant_moves_the_observation_manifest_and_what_binds_it_and_nothing_else(): void
    {
        $a = $this->facts();
        $this->freshTransaction();
        $changed = str_replace((string) self::INSTANT, (string) (self::INSTANT + 60), $this->frozenResponse());
        $b = $this->facts(['tamper_response' => $changed]);
        $this->assertSame('2026-03-23 09:01:00', (string) $b['envelopes'][0]->source_timestamp);
        $moved = [];
        foreach ($a['nested'] as $member => $hash) {
            if ($hash !== $b['nested'][$member]) {
                $moved[] = $member;
            }
        }
        $this->assertSame(['observation_manifest_hash'], $moved, 'only the observation manifest, among the nested identities, depends on the provider instant');
        $this->assertNotSame($a['artifacts']['bars'], $b['artifacts']['bars'], 'the bars artifact binds the observation manifest');
        foreach (['open', 'high', 'low', 'close', 'volume'] as $value) {
            $this->assertSame($a['bars'][0]->{$value}, $b['bars'][0]->{$value}, 'the canonical bar '.$value.' does not change');
        }
        $this->assertNull($b['bars'][0]->source_timestamp);
    }

    public function test_the_provider_instant_alone_moves_the_observation_identity_and_so_does_the_acquisition_time_alone(): void
    {
        // Same payload bytes, so the payload hash is constant: only the stored timestamps differ.
        $f = $this->facts();
        $service = new SemanticObservationIdentityService();
        $accepted = (int) end($f['ids']);
        $base = $service->manifestHashForObservationIds([$accepted]);
        $db = DB::connection();
        $update = function (array $columns) use ($db, $f) {
            foreach ($f['ids'] as $id) {
                $db->table('md_source_observations')->where('source_observation_id', $id)->update($columns);
            }
        };
        $update(['source_timestamp' => '2026-03-23 09:01:00']);
        $providerOnly = $service->manifestHashForObservationIds([$accepted]);
        $update(['source_timestamp' => '2026-03-23 09:00:00', 'acquired_at' => '2026-03-25 10:31:00']);
        $acquisitionOnly = $service->manifestHashForObservationIds([$accepted]);
        $update(['source_timestamp' => null, 'acquired_at' => '2026-03-25 10:30:00']);
        $noProvider = $service->manifestHashForObservationIds([$accepted]);
        $update(['source_timestamp' => '2026-03-23 09:00:00', 'acquired_at' => '2026-03-25 10:30:00']);
        $restored = $service->manifestHashForObservationIds([$accepted]);

        $this->assertSame($base, $restored, 'the control restored the rows it changed');
        $this->assertCount(4, array_unique([$base, $providerOnly, $acquisitionOnly, $noProvider]), 'provider instant, acquisition time and a missing provider instant are four different identities');
        // the same acquisition time under a different provider instant is not the same observation
        $this->assertNotSame($providerOnly, $base);
        $this->assertNotSame($acquisitionOnly, $base);
    }

    public function test_the_acquisition_clock_alone_moves_the_acquisition_semantics_and_not_the_provider_or_bar_facts(): void
    {
        $a = $this->facts();
        $this->freshTransaction();
        $b = $this->facts(['run_clock' => '2026-03-25 10:31:00']);
        foreach ($b['envelopes'] as $envelope) {
            $this->assertSame('2026-03-25 10:31:00', (string) $envelope->acquired_at);
            $this->assertSame('2026-03-23 09:00:00', (string) $envelope->source_timestamp, 'the provider instant does not depend on the acquisition clock');
        }
        $this->assertNull($b['bars'][0]->source_timestamp, 'the acquisition clock does not enter the bar source timestamp');
        $this->assertSame('2026-03-25 10:31:00', (string) $b['bars'][0]->acquired_at);
        $this->assertNull($b['rows'][0]->source_timestamp, 'the acquisition clock does not enter the normalized row source timestamp');
        $moved = [];
        foreach ($a['nested'] as $member => $hash) {
            if ($hash !== $b['nested'][$member]) {
                $moved[] = $member;
            }
        }
        $this->assertContains('observation_manifest_hash', $moved, 'acquired_at is part of the observation entry');
        $this->assertNotSame($a['artifacts']['bars'], $b['artifacts']['bars'], 'acquired_at is a bars artifact column');
        $this->assertSame($a['bars'][0]->close, $b['bars'][0]->close);
    }
}
