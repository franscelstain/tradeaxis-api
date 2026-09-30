<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * The artifact hash profile reaches run creation through config, never through env().
 *
 * EodRunRepository used to call env('MARKET_DATA_ARTIFACT_HASH_PROFILE') itself, which
 * ConfigIsTheOnlyEnvReaderTest forbids: a direct read skips config's default and returns null once
 * config is cached. The value now lives in config/market_data_runtime.php, outside the
 * config/market_data.php tree that is serialized into the resolved configuration snapshot.
 */
class ArtifactHashProfileConfigurationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const ENV_KEY = 'MARKET_DATA_ARTIFACT_HASH_PROFILE';

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

    public function test_governed_runs_default_to_v2_when_the_environment_is_silent(): void
    {
        $saved = [getenv(self::ENV_KEY), $_ENV[self::ENV_KEY] ?? null, $_SERVER[self::ENV_KEY] ?? null];
        putenv(self::ENV_KEY);
        unset($_ENV[self::ENV_KEY], $_SERVER[self::ENV_KEY]);
        try {
            $this->assertFalse(getenv(self::ENV_KEY), 'precondition: the variable must really be unset');
            $config = require base_path('config/market_data_runtime.php');
        } finally {
            if ($saved[0] !== false) {
                putenv(self::ENV_KEY.'='.$saved[0]);
            }
            if ($saved[1] !== null) {
                $_ENV[self::ENV_KEY] = $saved[1];
            }
            if ($saved[2] !== null) {
                $_SERVER[self::ENV_KEY] = $saved[2];
            }
        }

        $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $config['artifact_hash_profile']);
    }

    public function test_the_legacy_suite_opt_in_reaches_runtime_through_config_outside_the_snapshot_tree(): void
    {
        // phpunit.xml exports the legacy profile for the V1 regression suite.
        $this->assertSame(ArtifactSemanticHashService::LEGACY_PROFILE_V1, getenv(self::ENV_KEY));
        $this->assertSame(
            ArtifactSemanticHashService::LEGACY_PROFILE_V1,
            config('market_data_runtime.artifact_hash_profile')
        );
        $this->assertStringNotContainsString(
            self::ENV_KEY,
            (string) file_get_contents(base_path('config/market_data.php')),
            'the profile must stay out of the snapshot-hashed market_data tree'
        );
    }

    public function test_run_creation_uses_the_configured_profile_not_the_environment(): void
    {
        // The environment still says V1 (phpunit.xml). Only config says V2, so a V2 run shows the
        // repository reads config.
        config()->set('market_data_runtime.artifact_hash_profile', ArtifactSemanticHashService::PROFILE_V2);

        $run = (new EodRunRepository())->createPromoteRunFromSeed($this->seedRun(), 'COMPUTE_INDICATORS');

        $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $run->artifact_hash_profile);
        $this->assertSame(PublicationSemanticIdentityService::PROFILE_V2, $run->publication_semantic_profile);
    }

    /**
     * @dataProvider unsupportedProfiles
     */
    public function test_an_unknown_or_missing_profile_fails_run_creation_closed($profile): void
    {
        config()->set('market_data_runtime.artifact_hash_profile', $profile);
        $seed = $this->seedRun();
        $before = DB::table('eod_runs')->count();

        try {
            (new EodRunRepository())->createPromoteRunFromSeed($seed, 'COMPUTE_INDICATORS');
            $this->fail('run creation accepted an unsupported artifact hash profile');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('ARTIFACT_HASH_PROFILE_UNSUPPORTED', $e->getMessage());
        }

        $this->assertSame($before, DB::table('eod_runs')->count(), 'no run may be written');
    }

    public function unsupportedProfiles(): array
    {
        return [
            'unknown' => ['market-data-semantic-hash/v9'],
            'missing' => [null],
            'blank' => ['  '],
        ];
    }

    private function seedRun(): EodRun
    {
        $id = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => '2026-03-24',
            'lifecycle_state' => 'COMPLETED',
            'stage' => 'FINALIZE',
            'source' => 'api',
            'created_at' => '2026-03-24 18:00:00',
            'updated_at' => '2026-03-24 18:00:00',
        ]);

        return EodRun::query()->findOrFail($id);
    }
}
