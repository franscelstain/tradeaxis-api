<?php

use App\Application\MarketData\Ports\AuthoritativeTradingStatusEvidenceVerifier;
use App\Application\MarketData\Services\AuthoritativeTradingStatusSnapshotService;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class AuthoritativeTradingStatusSnapshotServiceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $manifestPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedListing();
        $this->manifestPath = tempnam(sys_get_temp_dir(), 'status-manifest-');
        file_put_contents($this->manifestPath, json_encode([
            'schema_version' => AuthoritativeTradingStatusSnapshotService::MANIFEST_SCHEMA,
            'scope_id' => 'status-snapshot-test', 'record_only' => true,
            'snapshot_source' => [
                'authority_name' => 'IDX', 'authority_class' => 'EXCHANGE_AUTHORITATIVE',
                'document_url' => 'https://block.idx.id/id/long-suspension',
                'content_type' => 'text/html', 'observed_as_of' => '2026-06-01',
            ],
            'transition_search_source' => [
                'authority_name' => 'IDX', 'authority_class' => 'EXCHANGE_AUTHORITATIVE',
                'document_url' => 'https://www.idx.id/primary/TradingSummary/GetSuspension',
                'content_type' => 'application/json', 'search_start' => '2026-06-01',
                'search_end' => '2026-06-01', 'expected_in_scope_events' => [],
            ],
            'entries' => [['ticker_code' => 'BBCA', 'reported_suspension_date' => '2025-01-02']],
        ], JSON_UNESCAPED_SLASHES));
    }

    protected function tearDown(): void
    {
        if (is_file($this->manifestPath)) unlink($this->manifestPath);
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_apply_binds_authoritative_status_to_immutable_observation_and_stable_ids(): void
    {
        $result = $this->service($this->validVerifier())->process($this->manifestPath, true);
        $row = DB::table('md_trading_status_revisions')->first();

        $this->assertTrue($result['applied']);
        $this->assertSame(1, $result['inserted_revision_count']);
        $this->assertSame(101, (int) $row->instrument_id);
        $this->assertSame('SUSPENSION_OBSERVED', $row->status_type_code);
        $this->assertSame('IDX_LONG_SUSPENSION_SNAPSHOT', $row->source_name);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $row->status_event_uid);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $row->source_payload_hash);
        $this->assertSame(
            $row->source_payload_hash,
            DB::table('md_source_observations')->where('source_observation_id', $row->source_observation_id)->value('payload_hash')
        );

        $resolved = (new TemporalTradingStatusRepository())->resolveForListing(201, '2026-06-01');
        $this->assertSame('BAR_NOT_EXPECTED', $resolved['bar_expectation_state']);
        $this->assertSame([201], [$resolved['listing_id']]);
    }

    public function test_invalid_verifier_result_fails_before_any_status_or_observation_write(): void
    {
        $verifier = new class implements AuthoritativeTradingStatusEvidenceVerifier {
            public function verifySnapshot(array $source, array $expectedEntries) { return ['http_status' => 200]; }
            public function verifyTransitionSearch(array $source, array $tickerCodes) { return ['http_status' => 200]; }
        };

        try {
            $this->service($verifier)->process($this->manifestPath, true);
            $this->fail('Invalid evidence must fail closed.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('STAGE8_STATUS_VERIFICATION_RESULT_INVALID', $e->getMessage());
        }
        $this->assertSame(0, DB::table('md_trading_status_revisions')->count());
        $this->assertSame(0, DB::table('md_source_observations')->count());
    }

    private function service(AuthoritativeTradingStatusEvidenceVerifier $verifier): AuthoritativeTradingStatusSnapshotService
    {
        return new AuthoritativeTradingStatusSnapshotService(new SourceObservationRepository(), $verifier);
    }

    private function validVerifier(): AuthoritativeTradingStatusEvidenceVerifier
    {
        return new class implements AuthoritativeTradingStatusEvidenceVerifier {
            public function verifySnapshot(array $source, array $expectedEntries) { return $this->result('snapshot'); }
            public function verifyTransitionSearch(array $source, array $tickerCodes) { return $this->result('transitions'); }
            private function result($body): array {
                $hash = hash('sha256', $body);
                return [
                    'document_sha256' => $hash, 'document_byte_length' => strlen($body),
                    'content_type' => 'application/octet-stream', 'http_status' => 200,
                    'schema_fingerprint' => hash('sha256', 'schema|'.$body),
                    'payload_ref' => 'sha256:'.$hash,
                    'bounded_payload_body' => base64_encode($body), 'semantic' => [],
                ];
            }
        };
    }

    private function seedListing(): void
    {
        DB::table('md_issuers')->insert([
            'issuer_id' => 1, 'issuer_uid' => 'issuer-1', 'legal_name' => 'Issuer 1',
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_instruments')->insert([
            'instrument_id' => 101, 'instrument_uid' => 'instrument-101', 'issuer_id' => 1,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR',
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_listings')->insert([
            'listing_id' => 201, 'listing_uid' => 'listing-201', 'legacy_ticker_id' => 301,
            'instrument_id' => 101, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
            'board_code' => 'RG', 'listed_date' => '2023-01-02', 'listing_state' => 'LISTED',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => 201, 'symbol' => 'BBCA', 'symbol_type' => 'EXCHANGE',
            'effective_from' => '2023-01-02 00:00:00', 'recorded_at' => '2023-01-02 00:00:00',
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => 201, 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'retracted_at' => null,
            'source_ref' => 'idx', 'change_reason' => 'TEST_FIXTURE',
        ]);
    }
}
