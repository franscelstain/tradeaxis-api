<?php

use App\Application\Watchlist\Services\WeeklySwingActiveParamsetResolver;
use App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class WeeklySwingActiveParamsetResolverTest extends TestCase
{
    use UsesMarketDataSqlite;

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

    public function test_it_fails_closed_when_active_paramset_table_is_missing(): void
    {
        $result = (new WeeklySwingActiveParamsetResolver())->resolve();

        $this->assertFalse($result['valid']);
        $this->assertSame('WS_ACTIVE_PARAMSET_SCHEMA_MISSING', $result['reason_code']);
    }

    public function test_it_resolves_and_adapts_exactly_one_active_paramset(): void
    {
        $this->createParamsetTable();
        $payload = $this->canonicalPayload();
        $hash = (new WeeklySwingBacktestEvidenceIdentityService())->stableHash($payload);
        $this->insertParamset(29, 'ACTIVE', $payload, $hash);

        $result = (new WeeklySwingActiveParamsetResolver())->resolve();

        $this->assertTrue($result['valid'], json_encode($result));
        $this->assertSame(29, $result['param_set_id']);
        $this->assertSame('watchlist_param_sets:29:ACTIVE', $result['paramset_source']);
        $this->assertSame($hash, $result['canonical_hash']);
        $this->assertSame('WEIGHTED_MEAN', $result['paramset']['scoring']['combine_mode']);
        $this->assertSame(
            'PLAN_GROUPING_DETERMINISTIC',
            $result['paramset']['grouping']['grouping_mode']
        );
    }

    public function test_it_fails_closed_when_more_than_one_paramset_is_active(): void
    {
        $this->createParamsetTable();
        $payload = $this->canonicalPayload();
        $hash = (new WeeklySwingBacktestEvidenceIdentityService())->stableHash($payload);
        $this->insertParamset(29, 'ACTIVE', $payload, $hash);
        $this->insertParamset(30, 'ACTIVE', $payload, $hash);

        $result = (new WeeklySwingActiveParamsetResolver())->resolve();

        $this->assertFalse($result['valid']);
        $this->assertSame('WS_ACTIVE_PARAMSET_CARDINALITY_INVALID', $result['reason_code']);
    }

    public function test_it_rejects_an_active_row_with_a_mismatched_hash(): void
    {
        $this->createParamsetTable();
        $this->insertParamset(29, 'ACTIVE', $this->canonicalPayload(), str_repeat('0', 40));

        $result = (new WeeklySwingActiveParamsetResolver())->resolve();

        $this->assertFalse($result['valid']);
        $this->assertSame('WS_ACTIVE_PARAMSET_HASH_MISMATCH', $result['reason_code']);
    }

    private function createParamsetTable(): void
    {
        $this->schema()->create('watchlist_param_sets', function (Blueprint $table): void {
            $table->unsignedBigInteger('param_set_id')->primary();
            $table->string('policy_code', 16);
            $table->string('status', 16);
            $table->longText('params_json');
            $table->string('params_hash', 40)->nullable();
        });
    }

    private function insertParamset(int $id, string $status, array $payload, string $hash): void
    {
        DB::table('watchlist_param_sets')->insert([
            'param_set_id' => $id,
            'policy_code' => 'WS',
            'status' => $status,
            'params_json' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'params_hash' => $hash,
        ]);
    }

    private function canonicalPayload(): array
    {
        return json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
    }
}
