<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

require_once __DIR__.'/B18ReplayPersistedEvidenceBindingTest.php';

/** B19 semantics through real comparator, database persistence, and on-disk JSON. */
class B19ReplayResultSemanticPipelineTest extends TestCase
{
    use UsesMarketDataSqlite;

    private $paths = [];
    private $literals;

    protected function setUp(): void
    {
        parent::setUp();
        $this->literals = new B18ReplayComparisonExhaustivenessTest();
    }

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (! is_dir($path)) continue;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
            rmdir($path);
        }
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function fixture(string $version = 'v1', array $overrides = []): string
    {
        // Load expected literals before creating or reading the actual database world.
        $definition = json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-semantic-context-'.$version.'.json'), true);
        $method = new ReflectionMethod($this->literals, 'fixtureDir');
        $method->setAccessible(true);
        $dir = $method->invoke($this->literals, array_merge($definition['expected_overrides'], $overrides));
        $this->paths[] = $dir;
        $manifest = json_decode(file_get_contents($dir.'/manifest.json'), true);
        $manifest['fixture_id'] = $definition['fixture_id'];
        $manifest['fixture_family'] = 'b19_semantic_contract_probe';
        $manifest['fixture_version'] = $definition['fixture_version'];
        $manifest['fixture_source'] = 'hand_authored_contract_literals';
        $manifest['replay_mode'] = 'PUBLICATION_EXACT';
        $manifest['oracle_version'] = $definition['fixture_version'];
        foreach ($manifest['files'] as $relative) $manifest['files_sha256'][$relative] = hash_file('sha256', $dir.'/'.$relative);
        file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $dir;
    }

    private function world(array $run = []): void
    {
        // Reuse the existing real persisted publication/capture/lineage fixture, without altering it.
        $world = new B18ReplayPersistedEvidenceBindingTest();
        $property = new ReflectionProperty($world, 'fixtures');
        $property->setAccessible(true);
        $property->setValue($world, $this->literals);
        $method = new ReflectionMethod($world, 'persistWorld');
        $method->setAccessible(true);
        $method->invoke($world, $run, [], null);
    }

    private function verifier(): ReplayVerificationService
    {
        return new ReplayVerificationService(new EodEvidenceRepository(), new EodPublicationRepository(), new ReplayResultRepository());
    }

    private function exporter(): MarketDataEvidenceExportService
    {
        return new MarketDataEvidenceExportService(new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository());
    }

    private function execute(string $fixture): array
    {
        $before = hash_file('sha256', $fixture.'/expected/expected_replay_result.json');
        $metric = $this->verifier()->verifyRunAgainstFixture(91, $fixture, 6191, 44);
        $row = DB::table('md_replay_daily_metrics')->where('replay_id', 6191)->where('trade_date', '2026-03-20')->first();
        $dir = sys_get_temp_dir().'/b19_semantics_'.uniqid('', true);
        $this->paths[] = $dir;
        $this->exporter()->exportReplayEvidence(6191, '2026-03-20', $dir);
        $result = json_decode(file_get_contents($dir.'/replay_result.json'), true);
        $expected = json_decode(file_get_contents($dir.'/replay_expected_state.json'), true);
        $this->assertSame($before, hash_file('sha256', $fixture.'/expected/expected_replay_result.json'));
        if (getenv('B19_SEMANTIC_ARTIFACT_DIR')) {
            $label = $this->getName();
            $capture = getenv('B19_SEMANTIC_ARTIFACT_DIR').'/'.preg_replace('/[^a-zA-Z0-9_-]/', '_', $label);
            if (! is_dir($capture)) mkdir($capture, 0775, true);
            foreach (glob($dir.'/*.json') as $file) copy($file, $capture.'/'.basename($file));
            file_put_contents($capture.'/persisted_metric.json', json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            file_put_contents($capture.'/fixture_manifest.json', file_get_contents($fixture.'/manifest.json'));
            file_put_contents($capture.'/fixture_expected.json', file_get_contents($fixture.'/expected/expected_replay_result.json'));
        }

        return [$metric, $row, $result, $expected];
    }

    public static function classificationCases(): array
    {
        return [
            'match' => ['MATCH', [], 'MATCH', 'PASS'],
            'expected degrade' => ['EXPECTED_DEGRADE', [], 'EXPECTED_DEGRADE', 'PASS'],
            'mismatch' => ['MATCH', ['bars_batch_hash' => 'independent-actual-A9'], 'MISMATCH', 'FAIL'],
            'unexpected divergence' => ['EXPECTED_DEGRADE', ['bars_batch_hash' => 'independent-actual-A9'], 'UNEXPECTED', 'FAIL'],
        ];
    }

    /** @dataProvider classificationCases */
    public function test_real_comparison_is_classified_persisted_and_exported(string $expectedClass, array $actualChange, string $comparison, string $status): void
    {
        $fixture = $this->fixture('v1', ['comparison_result' => $expectedClass]);
        $this->world($actualChange);
        [$metric, $row, $result] = $this->execute($fixture);
        foreach ([$metric, (array) $row, $result] as $stage) {
            $this->assertSame('NOT_ADMISSIBLE', $stage['comparison_result'], 'legacy fixture has no verified publication-manifest prerequisites under D006');
            $this->assertSame('BLOCKED', $stage['replay_status']);
            $this->assertSame('SUCCESS', $stage['status'], 'run status is separate from replay classification');
        }
        if ($expectedClass === 'EXPECTED_DEGRADE') {
            // This fixture has a readable publication with seven usable listings and three
            // independently declared insufficient-history holds, not an all-clean population.
            $this->assertSame(7, $result['eligible_count']);
            $this->assertSame([['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3]], $metric['reason_code_counts']);
        }
    }

    public static function unavailableCases(): array
    {
        return ['fixture context' => ['fixture'], 'NULL fixture context' => ['fixture_null'], 'runtime input identity' => ['binding'], 'self-generated oracle' => ['self']];
    }

    /** @dataProvider unavailableCases */
    public function test_unavailable_prerequisites_block_the_real_pipeline(string $missing): void
    {
        $fixture = $this->fixture();
        $this->world($missing === 'binding' ? ['observation_manifest_hash' => ''] : []);
        if ($missing === 'fixture' || $missing === 'fixture_null') {
            $path = $fixture.'/expected/expected_replay_result.json';
            $r = json_decode(file_get_contents($path), true);
            unset($r['expected_final_state'], $r['expected_run_context']['terminal_status'], $r['expected_run_context']['trade_date_effective'], $r['expected_seal_context']);
            if ($missing === 'fixture_null') {
                $r['expected_final_state'] = null;
                $r['expected_run_context'] = null;
                $r['expected_seal_context'] = null;
            }
            file_put_contents($path, json_encode($r));
        } elseif ($missing === 'self') {
            $path = $fixture.'/manifest.json';
            $m = json_decode(file_get_contents($path), true);
            $m['fixture_source'] = 'generated_from_run_91_publication_44';
            file_put_contents($path, json_encode($m));
        }
        [$metric, $row, $result, $expected] = $this->execute($fixture);
        foreach ([$metric, (array) $row, $result] as $stage) {
            $this->assertSame('NOT_ADMISSIBLE', $stage['comparison_result']);
            $this->assertSame('BLOCKED', $stage['replay_status']);
        }
        if ($missing === 'fixture' || $missing === 'fixture_null') {
            foreach (['expected_status', 'expected_trade_date_effective', 'expected_seal_state'] as $field) $this->assertNull($result[$field]);
            $this->assertNull($expected['status']);
            $this->assertNull($expected['trade_date_effective']);
            $this->assertNull($expected['seal_state']);
        }
    }

    public function test_versioned_expectations_remain_independent_of_actual_state(): void
    {
        $fixture = $this->fixture('v2');
        $literal = json_decode(file_get_contents($fixture.'/expected/expected_replay_result.json'), true);
        $this->world();
        [$metric, $row, $result, $expected] = $this->execute($fixture);
        $this->assertSame('NOT_ADMISSIBLE', $metric['comparison_result']);
        $this->assertSame('BLOCKED', $result['replay_status']);
        foreach ([$row, (object) $result] as $state) {
            $this->assertSame('b19-semantic-comparison-context', $state->fixture_id);
            $this->assertSame('b19-v2', $state->fixture_version);
            $this->assertSame($literal['expected_final_state']['terminal_status'], $state->expected_status);
            $this->assertSame($literal['expected_run_context']['trade_date_effective'], $state->expected_trade_date_effective);
            $this->assertSame($literal['expected_seal_context']['seal_state'], $state->expected_seal_state);
        }
        $this->assertSame('b19-v2', $expected['fixture_version']);
        $this->assertSame('hand_authored_contract_literals', $row->fixture_source);
        $manifest = json_decode(file_get_contents($fixture.'/manifest.json'), true);
        $this->assertSame(hash_file('sha256', $fixture.'/expected/expected_replay_result.json'), $manifest['files_sha256']['expected/expected_replay_result.json']);
        $this->assertSame($row->fixture_manifest_hash, $result['fixture_manifest_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $row->fixture_manifest_hash);
        $this->assertSame('HELD', $expected['status']);
        $this->assertSame('2026-03-19', $expected['trade_date_effective']);
        $this->assertSame('UNSEALED', $expected['seal_state']);
        foreach (['status' => 'expected_status', 'trade_date_effective' => 'expected_trade_date_effective', 'seal_state' => 'expected_seal_state'] as $actual => $wanted) {
            $this->assertNotSame($result[$actual], $result[$wanted]);
        }
        $this->assertSame($literal['expected_final_state'], json_decode($row->expected_context_json, true)['expected_final_state']);
        foreach (['expected_run_context', 'expected_final_state', 'expected_seal_context', 'expected_publication_context', 'expected_lineage'] as $context) {
            foreach ($literal[$context] as $field => $value) {
                $this->assertSame($value, $result['expected_context'][$context][$field], $context.'.'.$field);
                $this->assertSame($value, $expected['expected_context'][$context][$field], $context.'.'.$field);
            }
        }
        $this->assertSame(44, $expected['publication_context']['publication_id']);
        $this->assertSame(4, $expected['publication_version']);
        $this->assertSame('A1', $expected['bars_batch_hash']);
        $this->assertSame(str_repeat('4', 64), $expected['expected_context']['expected_lineage']['factor_set_hash']);
        $this->assertSame('b19-v1', json_decode(file_get_contents(__DIR__.'/../../fixtures/replay/b19-semantic-context-v1.json'), true)['fixture_version']);
    }

    public static function invalidPairs(): array
    {
        return ['match fails' => ['MATCH', 'FAIL'], 'mismatch passes' => ['MISMATCH', 'PASS'],
            'match blocked' => ['MATCH', 'BLOCKED'], 'inadmissible passes' => ['NOT_ADMISSIBLE', 'PASS'],
            'degrade fails' => ['EXPECTED_DEGRADE', 'FAIL'], 'unexpected passes' => ['UNEXPECTED', 'PASS'],
            'invalid status' => ['MATCH', 'GARBAGE'], 'empty classification' => ['', 'BLOCKED'],
            'unknown classification' => ['BOGUS', 'BLOCKED']];
    }

    /** @dataProvider invalidPairs */
    public function test_contradictory_or_invalid_classification_is_refused_at_write(string $comparison, string $status): void
    {
        $fixture = $this->fixture();
        $this->world();
        [$metric] = $this->execute($fixture);
        $metric['comparison_result'] = $comparison;
        $metric['replay_status'] = $status;
        $this->assertClassificationRefused(function () use ($metric) { (new ReplayResultRepository())->upsertMetric($metric); });
        $this->assertSame('BLOCKED', DB::table('md_replay_daily_metrics')->where('replay_id', 6191)->value('replay_status'));
    }

    /** @dataProvider invalidPairs */
    public function test_contradictory_or_invalid_persisted_classification_is_refused_at_export(string $comparison, string $status): void
    {
        $fixture = $this->fixture();
        $this->world();
        $this->execute($fixture);
        DB::table('md_replay_daily_metrics')->where('replay_id', 6191)->update(['comparison_result' => $comparison, 'replay_status' => $status]);
        $invalidDir = sys_get_temp_dir().'/b19_invalid_'.uniqid('', true);
        $this->paths[] = $invalidDir;
        $this->assertClassificationRefused(function () use ($invalidDir) { $this->exporter()->exportReplayEvidence(6191, '2026-03-20', $invalidDir); });
        $this->assertDirectoryDoesNotExist($invalidDir, 'invalid classification must be refused before any export is written');
    }

    private function assertClassificationRefused(callable $action): void
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('REPLAY_CLASSIFICATION_', $e->getMessage());
            return;
        }
        $this->fail('invalid replay classification was accepted');
    }

    public function test_legacy_null_status_is_derived_from_the_valid_persisted_comparison(): void
    {
        $fixture = $this->fixture();
        $this->world();
        [$metric] = $this->execute($fixture);
        // Explicit pre-D006 historical row, not a new v1 identity proof. Never backfill its context.
        $legacyActual = json_decode($metric['actual_context_json'], true);
        unset($legacyActual['replay_artifact_identity_schema_version'], $legacyActual['actual_publication_manifest_identity']);
        $metric['actual_context_json'] = json_encode($legacyActual);
        $metric['comparison_result'] = 'MATCH';
        DB::table('md_replay_daily_metrics')->where('replay_id', 6191)->update(['comparison_result' => 'MATCH', 'replay_status' => null, 'actual_context_json' => $metric['actual_context_json']]);
        $dir = sys_get_temp_dir().'/b19_legacy_status_'.uniqid('', true);
        $this->paths[] = $dir;
        $this->exporter()->exportReplayEvidence(6191, '2026-03-20', $dir);
        $this->assertSame('PASS', json_decode(file_get_contents($dir.'/replay_result.json'), true)['replay_status']);
        $this->assertNull(DB::table('md_replay_daily_metrics')->where('replay_id', 6191)->value('replay_status'), 'historical read must not rewrite stored data');
        $metric['replay_status'] = null;
        (new ReplayResultRepository())->upsertMetric($metric);
        $this->assertSame('PASS', DB::table('md_replay_daily_metrics')->where('replay_id', 6191)->value('replay_status'));
    }
}
