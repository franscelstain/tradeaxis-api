<?php

use App\Application\MarketData\Services\AdjustmentFactorSetService;
use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\PublicationGovernanceBindingService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use App\Application\MarketData\Services\SemanticNestedIdentityService;
use App\Application\MarketData\Services\SemanticObservationIdentityService;
use App\Application\SecurityIdentity\Contracts\IdentityResolution;
use App\Application\SecurityIdentity\Contracts\IdentityResolver;
use App\Application\SecurityIdentity\FoundationService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * V2 nested semantic identities through the production producers (D-MD-B10-A002-003).
 *
 * Each history is a fresh isolated MariaDB database whose tables are cloned from the migrated
 * test schema and whose only listing root is the retained IKPM foundation identity. The same
 * semantic facts are written with different local keys, in a different order, and the producers run
 * on a different wall clock. The real producers then build everything the V2 identities are
 * derived from: the observation manifests, the factor set with its pipeline-derived source-scale
 * assessments, the V1 governance bindings, the V2 nested identities, the V2 bars artifact and the V2
 * publication manifest. Nothing compared here is a document the test assembled.
 */
class SemanticNestedIdentityOnMariaDbTest extends TestCase
{
    private const TRADE_DATE = '2026-09-29';
    private const CUTOFF = '2026-09-29 00:38:15';

    private const CLONED_TABLES = [
        'eod_runs', 'eod_publications', 'eod_bars_history', 'eod_eligibility_history',
        'md_config_snapshots', 'md_listings', 'md_source_observations', 'md_source_observation_rows',
        'md_source_observation_rejected_rows', 'md_source_observation_identity_bindings',
        'md_source_observation_revision_comparisons', 'md_corporate_action_revisions',
        'market_data_corporate_action_types', 'md_source_scale_assessments', 'md_adjustment_factor_sets',
        'md_adjustment_factor_decisions', 'md_adjustment_factors', 'md_market_calendar_revisions',
        'md_trading_status_revisions', 'md_exchange_market_structure_revisions',
        'md_publication_market_structure_bindings', 'md_publication_lineage_bindings', 'md_run_input_captures',
        'eod_run_events',
    ];

    /** Producer-allocated tables whose next key is moved in the second history. */
    private const PRODUCER_ALLOCATED = [
        'md_source_scale_assessments', 'md_adjustment_factor_sets', 'md_adjustment_factor_decisions',
        'md_adjustment_factors', 'md_publication_market_structure_bindings', 'md_publication_lineage_bindings',
        'md_run_input_captures',
    ];

    public const ALLOCATION_A = [
        'listing' => 10, 'instrument' => 20, 'ticker' => 30, 'publication' => 40, 'run' => 50,
        'config' => 60, 'observation' => 100, 'event' => 200, 'calendar' => 300, 'status' => 400,
        'rule' => 500, 'mapping' => 600, 'producer_offset' => 0, 'reverse' => false,
        'clock' => '2026-09-29 01:00:00', 'uid' => 'history-a',
    ];

    public const ALLOCATION_B = [
        'listing' => 7010, 'instrument' => 7020, 'ticker' => 7030, 'publication' => 7040, 'run' => 7050,
        'config' => 7060, 'observation' => 7100, 'event' => 7200, 'calendar' => 7300, 'status' => 7400,
        'rule' => 7500, 'mapping' => 7600, 'producer_offset' => 9000, 'reverse' => true,
        'clock' => '2026-09-30 11:11:11', 'uid' => 'history-b',
    ];

    private array $owned = [];
    private $control;
    private ?IdentityResolver $resolverOverride = null;

    protected function setUp(): void
    {
        parent::setUp();
        $base = config('database.connections.mysql');
        config()->set('database.connections.semantic_nested_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('semantic_nested_control');
        $this->assertSame('tradeaxis_testing', $this->control->selectOne('SELECT DATABASE() AS name')->name);
        $this->assertTrue(
            $this->control->getSchemaBuilder()->hasColumn('md_publication_lineage_bindings', 'semantic_factor_set_hash'),
            'tradeaxis_testing must carry 2026_10_01_000001 before its tables can be cloned'
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        foreach (array_keys($this->owned) as $connection) {
            DB::purge($connection);
        }
        foreach ($this->owned as $name) {
            if (! preg_match('/^tradeaxis_testing_semantic_nested_[a-f0-9]{12}$/D', $name)) {
                throw new RuntimeException('UNSAFE_TEST_DATABASE_NAME');
            }
            $this->control->statement('DROP DATABASE IF EXISTS `'.$name.'`');
        }
        $this->owned = [];
        DB::purge('semantic_nested_control');
    }

    public function test_v2_nested_identities_ignore_allocation_insertion_order_and_execution_clock(): void
    {
        $a = $this->history(self::ALLOCATION_A);
        $b = $this->history(self::ALLOCATION_B);

        foreach ($a['keys'] as $name => $value) {
            $this->assertNotSame($value, $b['keys'][$name], 'precondition: '.$name.' must differ between histories');
        }
        foreach ($a['v2'] as $member => $value) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $value, $member);
        }

        // Non-vacuity: both histories produced the same governed content, and the producers wrote
        // their platform records after the run's knowledge cutoff, which is the case 1A governs.
        $this->assertSame($a['facts'], $b['facts']);
        $this->assertSame(['HELD_SOURCE_SCALE_UNKNOWN', 'HELD_SOURCE_SCALE_UNKNOWN'], $a['facts']['decision_states']);
        $this->assertSame(['UNKNOWN', 'UNKNOWN'], $a['facts']['assessment_states']);
        $this->assertSame(['RESOLVED_STANDARD_BOARD'], $a['facts']['binding_states']);
        $this->assertSame(1, $a['facts']['selected_calendar_revisions']);
        $this->assertSame(['0.200000000000', '2.000000000000'], $a['facts']['candidate_price_factors']);
        $this->assertGreaterThan(self::CUTOFF, $a['keys']['assessment_recorded_at']);
        $this->assertGreaterThan(self::CUTOFF, $b['keys']['assessment_recorded_at']);

        $this->assertSame($a['v2'], $b['v2'], 'V2 nested identities');
        $this->assertSame($a['bars'], $b['bars'], 'V2 bars artifact consumes only V2 nested identities');
        $this->assertSame($a['manifest'], $b['manifest'], 'V2 publication manifest');

        // The defect being repaired: every V1 nested hash below carries an allocation.
        foreach (['observation_manifest_hash', 'identity_revision_set_hash', 'calendar_revision_set_hash',
            'status_revision_set_hash', 'event_revision_set_hash', 'source_scale_assessment_set_hash',
            'market_structure_revision_set_hash', 'factor_decision_set_hash', 'factor_set_hash'] as $member) {
            $this->assertNotSame($a['v1'][$member], $b['v1'][$member], 'V1 '.$member.' was expected to be allocation-bearing');
        }
    }

    /**
     * @dataProvider semanticChanges
     */
    public function test_a_genuine_semantic_change_moves_exactly_the_owning_identities(string $variant, array $expectedChanged): void
    {
        $baseline = $this->history(self::ALLOCATION_A);
        $changed = $this->history(self::ALLOCATION_A, $variant);

        $moved = [];
        foreach ($baseline['v2'] as $member => $value) {
            if ($value !== $changed['v2'][$member]) {
                $moved[] = $member;
            }
        }
        sort($moved);
        sort($expectedChanged);
        $this->assertSame($expectedChanged, $moved, $variant);
        if ($expectedChanged !== []) {
            $this->assertNotSame($baseline['manifest'], $changed['manifest'], $variant.' must reach the publication manifest');
        } else {
            $this->assertSame($baseline['manifest'], $changed['manifest'], $variant.' must not reach the publication manifest');
        }
    }

    public function semanticChanges(): array
    {
        $evidence = ['observation_manifest_hash', 'source_scale_assessment_set_hash', 'factor_decision_set_hash', 'factor_set_hash'];

        return [
            // A refetch is a new observation identity (Audit_Hash:101); the assessment evidence follows it.
            'observation acquisition time' => ['observation_acquired_at', $evidence],
            'observation adapter version' => ['observation_adapter', $evidence],
            'rejected observation reason' => ['observation_reason', ['observation_manifest_hash']],
            'event terms' => ['event_terms', ['event_revision_set_hash', 'factor_decision_set_hash', 'factor_set_hash']],
            // 1B: source-fact knowledge time is part of the revision tuple.
            'event knowledge time' => ['event_recorded_at', ['event_revision_set_hash']],
            'calendar knowledge time' => ['calendar_recorded_at', ['calendar_revision_set_hash']],
            'calendar session close' => ['calendar_session', ['calendar_revision_set_hash']],
            'status knowledge time' => ['status_recorded_at', ['status_revision_set_hash']],
            'status effective interval' => ['status_effective_to', ['status_revision_set_hash']],
            'board identity knowledge time' => ['board_recorded_at', ['identity_revision_set_hash', 'market_structure_revision_set_hash']],
            'market-structure rule content' => ['rule_content', ['market_structure_revision_set_hash']],
            'market-structure rule knowledge time' => ['rule_recorded_at', ['market_structure_revision_set_hash']],
            // 1A: platform-created records hash content; their wall clock is provenance.
            'assessment and factor-set wall clock' => ['producer_clock', []],
        ];
    }

    public function test_a_different_retained_listing_root_moves_only_the_per_listing_sets(): void
    {
        $baseline = $this->history(self::ALLOCATION_A);
        $this->resolverOverride = new class implements IdentityResolver {
            public function resolve(string $namespace, string $symbol, string $effectiveAtUtc, string $knowledgeCutoffUtc): IdentityResolution
            {
                return new IdentityResolution('RESOLVED', 'TEST_ALTERNATE_ROOT', 'issuer-alternate', 'instrument-alternate', 'listing-alternate', [
                    'exchange_symbol' => 'IKPM', 'exchange_namespace' => 'IDX',
                    'provider_namespace' => $namespace, 'provider_symbol' => $symbol,
                    'venue' => 'IDX', 'market_segment' => 'REGULAR', 'board' => 'DEVELOPMENT',
                    'effective_at' => $effectiveAtUtc, 'knowledge_cutoff' => $knowledgeCutoffUtc,
                ]);
            }
        };
        $alternate = $this->history(self::ALLOCATION_A);

        $moved = [];
        foreach ($baseline['v2'] as $member => $value) {
            if ($value !== $alternate['v2'][$member]) {
                $moved[] = $member;
            }
        }
        sort($moved);
        $this->assertSame(['identity_revision_set_hash', 'market_structure_revision_set_hash', 'status_revision_set_hash'], $moved);
    }

    public function test_platform_record_timestamps_written_after_production_do_not_move_v2_identity(): void
    {
        $history = $this->history(self::ALLOCATION_A);
        $connection = DB::connection($history['connection']);
        $connection->table('md_source_scale_assessments')->update(['recorded_at' => '2031-01-01 00:00:00', 'created_at' => '2031-01-01 00:00:00']);
        $connection->table('md_adjustment_factor_sets')->update(['recorded_at' => '2031-01-01 00:00:00', 'created_at' => '2031-01-01 00:00:00']);
        $connection->table('md_adjustment_factor_decisions')->update(['created_at' => '2031-01-01 00:00:00']);
        $connection->table('md_adjustment_factors')->update(['created_at' => '2031-01-01 00:00:00']);

        $this->assertSame($history['v2'], $this->recompute($history));
    }

    public function test_the_manifest_binds_the_publication_scope_reason_set_and_refuses_a_contradicting_reason(): void
    {
        $history = $this->history(self::ALLOCATION_A);
        $repository = new EodPublicationRepository();
        $method = new ReflectionMethod(EodPublicationRepository::class, 'publicationManifestSemanticPayloadV2');
        $method->setAccessible(true);
        $context = (new ReflectionMethod(EodPublicationRepository::class, 'publicationManifestContext'));
        $context->setAccessible(true);
        $payload = $method->invoke($repository, $context->invoke($repository, $history['keys']['publication']), 'READABLE');

        $this->assertSame(['COVERAGE_THRESHOLD_MET'], $payload['semantic_reasons']);
        $this->assertSame(SemanticNestedIdentityService::VERSION, $payload['nested_identity_version']);
        foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $member => $column) {
            $this->assertSame($history['v2'][$member], $payload[$member], $member.' must be the V2 identity');
        }
        $this->assertTrue($repository->assertPublicationManifestHashValid($history['keys']['publication']));

        DB::table('eod_runs')->where('run_id', $history['keys']['run'])->update(['coverage_reason_code' => 'RUN_COVERAGE_LOW']);
        try {
            $repository->assertPublicationManifestHashValid($history['keys']['publication']);
            $this->fail('a coverage reason contradicting the bound coverage state was accepted');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('DATASET_MANIFEST_INVALID', $e->getMessage());
        }

        DB::table('eod_runs')->where('run_id', $history['keys']['run'])->update(['coverage_reason_code' => null]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('publication semantic reason set incomplete: coverage_reason_code');
        $repository->assertPublicationManifestHashValid($history['keys']['publication']);
    }

    public function test_out_of_band_nested_identity_changes_fail_closed(): void
    {
        $history = $this->history(self::ALLOCATION_A);
        $repository = new EodPublicationRepository();
        $publicationId = $history['keys']['publication'];

        DB::table('md_publication_lineage_bindings')->where('publication_id', $publicationId)
            ->update(['semantic_factor_set_hash' => hash('sha256', 'tampered-factor-set')]);
        try {
            $repository->assertPublicationManifestHashValid($publicationId);
            $this->fail('a changed V2 nested identity left the manifest valid');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('DATASET_MANIFEST_INVALID', $e->getMessage());
        }

        DB::table('md_adjustment_factor_decisions')->limit(1)->update(['candidate_price_factor' => '0.250000000000']);
        $this->assertFailsClosed(function () use ($history) {
            $this->recompute($history);
        }, 'SEMANTIC_FACTOR_SET_V1_CONTENT_MISMATCH');
    }

    public function test_a_sealed_publication_can_only_confirm_its_nested_identity(): void
    {
        $history = $this->history(self::ALLOCATION_A);
        $publicationId = $history['keys']['publication'];
        DB::table('eod_publications')->where('publication_id', $publicationId)->update(['seal_state' => 'SEALED']);

        $this->assertSame($history['v2'], $this->bindNested($history));

        DB::table('md_market_calendar_revisions')->where('calendar_revision_id', self::local(self::ALLOCATION_A, 'calendar', 1))
            ->update(['session_close_at' => self::TRADE_DATE.' 16:15:00']);
        $this->assertFailsClosed(function () use ($history) {
            $this->bindNested($history);
        }, 'SEMANTIC_NESTED_SEALED_PUBLICATION_IMMUTABLE');
    }

    public function test_missing_v2_inputs_fail_closed_instead_of_falling_back_to_v1(): void
    {
        $history = $this->history(self::ALLOCATION_A);
        $publicationId = $history['keys']['publication'];

        DB::table('eod_publications')->where('publication_id', $publicationId)->update(['semantic_observation_manifest_hash' => null]);
        $this->assertFailsClosed(function () use ($history) {
            $this->recompute($history);
        }, 'SEMANTIC_OBSERVATION_MANIFEST_MISSING');

        DB::table('md_publication_lineage_bindings')->where('publication_id', $publicationId)
            ->update(['semantic_nested_identity_version' => null]);
        $this->assertFailsClosed(function () use ($history) {
            $this->barsHash($history);
        }, 'ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: semantic_nested_identity_version');

        DB::table('md_source_observation_rows')->update(['provider_symbol' => 'UNKNOWN.JK']);
        DB::table('eod_publications')->where('publication_id', $publicationId)
            ->update(['semantic_observation_manifest_hash' => $history['v2']['observation_manifest_hash']]);
        $this->assertFailsClosed(function () use ($history) {
            $this->recompute($history);
        }, 'FOUNDATION_IDENTITY_HELD');
    }

    // ------------------------------------------------------------------ history construction

    /**
     * @return array{connection:string,keys:array,v1:array,v2:array,bars:string,manifest:string,alloc:array}
     */
    private function history(array $alloc, string $variant = 'baseline'): array
    {
        $connection = $this->createIsolatedDatabase();
        $this->seed($alloc, $variant);
        Carbon::setTestNow(Carbon::parse($variant === 'producer_clock' ? '2027-02-03 04:05:06' : $alloc['clock'], 'Asia/Jakarta'));

        $run = EodRun::query()->findOrFail($alloc['run']);
        $publicationId = $alloc['publication'];
        $roots = [self::local($alloc, 'observation', 2), self::local($alloc, 'observation', 4)];

        // Ingest-time manifests over the same root observations (EodBarsIngestService order).
        $v1Manifest = (new SourceObservationRepository())->manifestHashForObservationIds($roots);
        $v2Manifest = (new SemanticObservationIdentityService())->manifestHashForObservationIds($roots);
        $publications = new EodPublicationRepository();
        $publications->bindCandidateAcquisitionProvenance($publicationId, $run->run_id, $v1Manifest, $alloc['config'], $v2Manifest);

        // The real factor producer: event selection, pipeline-derived assessments, factor set.
        $producer = new ReflectionMethod(AdjustmentFactorSetService::class, 'produceForPublication');
        $producer->setAccessible(true);
        $factor = $producer->invoke(new AdjustmentFactorSetService(), $run->fresh(), $publicationId, self::TRADE_DATE, [
            $alloc['ticker'] => [['source_observation_id' => self::local($alloc, 'observation', 2)]],
        ]);
        $publications->bindCandidateAnalyticalProduct($publicationId, $run->run_id, 'STRUCTURAL_ADJUSTED', 'structural_adjusted_v1', $factor['factor_set_hash'], $factor['factor_set_id']);

        // The real V1 governance producer, then the V2 nested producer.
        (new PublicationGovernanceBindingService())->bind($run->fresh(), DB::table('eod_publications')->where('publication_id', $publicationId)->first(), self::TRADE_DATE);
        $history = ['connection' => $connection, 'alloc' => $alloc];
        $v2 = $this->bindNested($history);

        $bars = $this->barsHash($history);
        $publications->updateCandidateHashes($publicationId, [
            'bars_batch_hash' => $bars,
            'indicators_batch_hash' => hash('sha256', 'semantic-nested-indicators'),
            'eligibility_batch_hash' => hash('sha256', 'semantic-nested-eligibility'),
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
        ]);
        $publications->prepareCandidateManifestForSeal($run->fresh(), $publicationId);

        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->first();
        $assessment = DB::table('md_source_scale_assessments')->orderBy('source_scale_assessment_id')->first();

        return $history + [
            'keys' => [
                'listing' => $alloc['listing'],
                'publication' => (int) $publication->publication_id,
                'run' => (int) $run->run_id,
                'config' => (int) $publication->config_snapshot_id,
                'factor_set' => (int) $publication->factor_set_id,
                'assessment' => (int) $assessment->source_scale_assessment_id,
                'assessment_recorded_at' => (string) $assessment->recorded_at,
                'decision' => (int) DB::table('md_adjustment_factor_decisions')->min('factor_decision_id'),
                'binding' => (int) DB::table('md_publication_market_structure_bindings')->value('market_structure_binding_id'),
                'event_revision' => (int) DB::table('md_corporate_action_revisions')->max('corporate_action_revision_id'),
                'calendar_revision' => (int) DB::table('md_market_calendar_revisions')->max('calendar_revision_id'),
                'status_revision' => (int) DB::table('md_trading_status_revisions')->value('status_revision_id'),
                'observation_uid' => (string) DB::table('md_source_observations')->where('source_observation_id', self::local($alloc, 'observation', 2))->value('observation_uid'),
            ],
            'v1' => [
                'observation_manifest_hash' => (string) $publication->observation_manifest_hash,
                'identity_revision_set_hash' => (string) $lineage->identity_revision_set_hash,
                'calendar_revision_set_hash' => (string) $lineage->calendar_revision_set_hash,
                'status_revision_set_hash' => (string) $lineage->status_revision_set_hash,
                'event_revision_set_hash' => (string) $lineage->event_revision_set_hash,
                'source_scale_assessment_set_hash' => (string) $lineage->source_scale_assessment_set_hash,
                'market_structure_revision_set_hash' => (string) $lineage->market_structure_revision_set_hash,
                'factor_decision_set_hash' => (string) $lineage->factor_decision_set_hash,
                'factor_set_hash' => (string) $publication->factor_set_hash,
            ],
            'v2' => $v2,
            'facts' => [
                'decision_states' => $this->sortedColumn('md_adjustment_factor_decisions', 'decision_state'),
                'assessment_states' => $this->sortedColumn('md_source_scale_assessments', 'source_scale_state'),
                'binding_states' => $this->sortedColumn('md_publication_market_structure_bindings', 'resolution_state'),
                'candidate_price_factors' => array_map(static function ($value) {
                    return number_format((float) $value, 12, '.', '');
                }, $this->sortedColumn('md_adjustment_factor_decisions', 'candidate_price_factor')),
                'selected_calendar_revisions' => count((new PublicationGovernanceBindingService())->calendarRevisionsForTradeDate(self::TRADE_DATE, self::CUTOFF)),
            ],
            'bars' => $bars,
            'manifest' => (string) $publication->publication_manifest_hash,
        ];
    }

    /**
     * A local key. The second history also reverses the relative order of every multi-row key,
     * so a read ordered by primary key returns the same facts in the opposite order.
     */
    private static function local(array $alloc, string $base, int $offset): int
    {
        return $alloc[$base] + ($alloc['reverse'] ? 20 - $offset : $offset);
    }

    private function sortedColumn(string $table, string $column): array
    {
        $values = array_map('strval', DB::table($table)->pluck($column)->all());
        sort($values, SORT_STRING);

        return $values;
    }

    private function nestedService(): SemanticNestedIdentityService
    {
        $hashes = new DeterministicHashService();
        $resolver = $this->resolverOverride ?: new FoundationService(new FoundationRepository(DB::connection()));

        return new SemanticNestedIdentityService(
            $hashes,
            new ArtifactSemanticHashService($hashes, new TemporalIdentityRepository($resolver)),
            new EodArtifactRepository(),
            new PublicationGovernanceBindingService(),
            new SemanticObservationIdentityService($hashes)
        );
    }

    private function bindNested(array $history): array
    {
        $alloc = $history['alloc'];

        return $this->nestedService()->bind(
            EodRun::query()->findOrFail($alloc['run']),
            DB::table('eod_publications')->where('publication_id', $alloc['publication'])->first(),
            self::TRADE_DATE,
            true
        );
    }

    private function recompute(array $history): array
    {
        $alloc = $history['alloc'];

        return $this->nestedService()->compute(
            EodRun::query()->findOrFail($alloc['run']),
            DB::table('eod_publications')->where('publication_id', $alloc['publication'])->first(),
            self::TRADE_DATE,
            true
        );
    }

    private function barsHash(array $history): string
    {
        $alloc = $history['alloc'];
        $resolver = $this->resolverOverride ?: new FoundationService(new FoundationRepository(DB::connection()));
        $service = new ArtifactSemanticHashService(new DeterministicHashService(), new TemporalIdentityRepository($resolver));

        return $service->hashStoredArtifact(
            'bars',
            'eod_bars_history',
            self::TRADE_DATE,
            EodRun::query()->findOrFail($alloc['run']),
            DB::table('eod_publications')->where('publication_id', $alloc['publication'])->first(),
            ['publication_id' => $alloc['publication']]
        );
    }

    /**
     * PHPUnit's own failures are RuntimeExceptions, so calling fail() inside the try would be caught
     * here and, carrying the expected code in its message, would pass. Probe N11 showed exactly that;
     * the refusal is therefore captured first and asserted outside the try.
     */
    private function assertFailsClosed(callable $action, string $expected): void
    {
        $refusal = null;
        try {
            $action();
        } catch (\PHPUnit\Framework\Exception $assertion) {
            throw $assertion;
        } catch (RuntimeException $e) {
            $refusal = $e;
        }
        $this->assertNotNull($refusal, 'the action succeeded; expected a fail-closed refusal');
        $this->assertStringContainsString($expected, $refusal->getMessage());
    }

    private function createIsolatedDatabase(): string
    {
        $base = config('database.connections.mysql');
        $name = 'tradeaxis_testing_semantic_nested_'.bin2hex(random_bytes(6));
        $connection = 'semantic_nested_'.count($this->owned);
        $this->control->statement('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->owned[$connection] = $name;
        config()->set('database.connections.'.$connection, array_merge($base, ['database' => $name]));
        DB::purge($connection);
        config()->set('database.default', $connection);
        $this->assertSame($name, DB::connection($connection)->selectOne('SELECT DATABASE() AS name')->name);

        require_once base_path('database/migrations/2026_09_29_000001_create_shared_security_identity_foundation.php');
        (new CreateSharedSecurityIdentityFoundation())->up();
        foreach (self::CLONED_TABLES as $table) {
            DB::connection($connection)->statement('CREATE TABLE `'.$table.'` LIKE `tradeaxis_testing`.`'.$table.'`');
        }

        $foundation = new FoundationService(new FoundationRepository(DB::connection($connection)));
        foreach ([
            'foundation-source-basis-20260928-v1' => 'foundation-source-basis-20260928-v1',
            'foundation-source-basis-20260929-ikpm-listing-v2' => 'foundation-source-basis-20260929-ikpm-listing-v2',
            'foundation-source-basis-20260929-ikpm-current-window-v3' => 'foundation-source-basis-20260929-ikpm-current-window-v3',
        ] as $package => $registry) {
            $foundation->bootstrap(
                storage_path('app/market_data/evidence/MD-B10-A002/'.$package),
                base_path('resources/security_identity/'.$registry.'.registry.json')
            );
        }

        return $connection;
    }

    // ------------------------------------------------------------------ semantic facts

    private function seed(array $alloc, string $variant): void
    {
        $db = DB::connection();
        if ($alloc['producer_offset'] > 0) {
            foreach (self::PRODUCER_ALLOCATED as $table) {
                $db->statement('ALTER TABLE `'.$table.'` AUTO_INCREMENT = '.($alloc['producer_offset'] + 1));
            }
        }

        $o = $alloc['observation'];
        $configHash = hash('sha256', 'semantic-nested-config-content');
        $steps = [];

        $steps[] = function () use ($db, $alloc, $configHash) {
            $db->table('md_config_snapshots')->insert([
                'config_snapshot_id' => $alloc['config'], 'snapshot_uid' => hash('sha256', 'snapshot-'.$alloc['uid']),
                'snapshot_schema_version' => 'market_data_config_snapshot_v1', 'serialization_version' => 'canonical_json_v1',
                'resolved_config_json' => '{}', 'config_hash' => $configHash, 'registry_revision' => 'semantic-nested-registry-v1',
                'effective_at' => '2026-09-01 00:00:00', 'recorded_at' => '2026-09-01 00:00:00',
                'environment_profile' => 'testing', 'resolver_version' => 'test-resolver-v1', 'created_at' => '2026-09-01 00:00:00',
            ]);
        };
        $steps[] = function () use ($db, $alloc, $variant) {
            $db->table('md_listings')->insert([
                'listing_id' => $alloc['listing'], 'listing_uid' => 'IDX:IKPM', 'instrument_id' => $alloc['instrument'],
                'exchange_code' => 'IDX', 'board_code' => 'DEVELOPMENT', 'listed_date' => '2023-01-02',
                'recorded_at' => $variant === 'board_recorded_at' ? '2026-09-21 10:00:00' : '2026-09-20 10:00:00',
                'created_at' => '2026-09-20 10:00:00', 'legacy_ticker_id' => $alloc['ticker'], 'market_segment' => 'REGULAR',
            ]);
            $db->table('market_data_corporate_action_types')->insert([
                'action_type_code' => 'STOCK_SPLIT', 'price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED',
            ]);
        };
        $steps[] = function () use ($db, $alloc, $o, $variant) {
            foreach ($this->observationRows($alloc, $o, $variant) as $row) {
                $db->table('md_source_observations')->insert($row);
            }
            $db->table('md_source_observation_rows')->insert([
                'source_observation_id' => self::local($alloc, 'observation', 2), 'capture_observation_id' => self::local($alloc, 'observation', 1), 'source_row_ref' => 'row:IKPM',
                'listing_id' => $alloc['listing'], 'provider' => 'YAHOO_FINANCE', 'provider_symbol' => 'IKPM.JK',
                'provider_mapping_id' => $alloc['mapping'], 'mapping_revision' => 'mapping-r1', 'ticker_code' => 'IKPM',
                'trade_date' => self::TRADE_DATE, 'open_value' => '100', 'high_value' => '110', 'low_value' => '95',
                'close_value' => '105', 'volume_value' => '1000', 'row_fingerprint' => hash('sha256', 'row-fingerprint'),
                'created_at' => '2026-09-29 00:36:00',
            ]);
        };
        $steps[] = function () use ($db, $alloc, $variant) {
            $common = [
                'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => self::TRADE_DATE,
                'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
                'source_ref' => 'https://www.idx.co.id/calendar/2026', 'source_version' => 'idx-calendar-2026',
                'provenance_tier' => 'VERIFIED',
            ];
            $rows = [
                $common + ['calendar_revision_id' => self::local($alloc, 'calendar', 0), 'revision_uid' => hash('sha256', 'calendar-r0'), 'session_state' => 'SCHEDULED',
                    'session_open_at' => self::TRADE_DATE.' 09:00:00', 'session_close_at' => self::TRADE_DATE.' 16:00:00',
                    'recorded_at' => '2026-09-20 08:00:00', 'supersedes_revision_id' => null],
                $common + ['calendar_revision_id' => self::local($alloc, 'calendar', 1), 'revision_uid' => hash('sha256', 'calendar-r1'), 'session_state' => 'COMPLETED',
                    'session_open_at' => self::TRADE_DATE.' 09:00:00',
                    'session_close_at' => $variant === 'calendar_session' ? self::TRADE_DATE.' 15:00:00' : self::TRADE_DATE.' 16:00:00',
                    'completed_at' => self::TRADE_DATE.' 16:00:00',
                    'recorded_at' => $variant === 'calendar_recorded_at' ? '2026-09-29 00:25:00' : '2026-09-29 00:20:00',
                    'supersedes_revision_id' => self::local($alloc, 'calendar', 0)],
            ];
            foreach ($alloc['reverse'] ? array_reverse($rows) : $rows as $row) {
                $db->table('md_market_calendar_revisions')->insert($row);
            }
        };
        $steps[] = function () use ($db, $alloc, $o, $variant) {
            $db->table('md_trading_status_revisions')->insert([
                'status_revision_id' => $alloc['status'], 'listing_id' => $alloc['listing'], 'status_code' => 'ACTIVE',
                'bar_expectation_state' => 'EXPECTED', 'full_session_verified' => 1,
                'effective_from' => '2026-01-01 00:00:00',
                'effective_to' => $variant === 'status_effective_to' ? '2026-12-31 00:00:00' : null,
                'recorded_at' => $variant === 'status_recorded_at' ? '2026-09-22 11:00:00' : '2026-09-20 11:00:00',
                'source_observation_id' => self::local($alloc, 'observation', 7), 'board_code' => 'DEVELOPMENT', 'authority_class' => 'EXCHANGE',
                'source_ref' => 'https://www.idx.co.id/status/IKPM', 'verification_state' => 'AUTHORITATIVE_VERIFIED',
                'status_event_uid' => hash('sha256', 'status|'.$alloc['listing']), 'instrument_id' => $alloc['instrument'],
                'status_type_code' => 'NORMAL_TRADING', 'source_name' => 'IDX', 'source_payload_hash' => hash('sha256', 'status-snapshot'),
            ]);
        };
        $steps[] = function () use ($db, $alloc, $o, $variant) {
            $rows = [];
            foreach (['PRICE_BAND', 'MINIMUM_PRICE', 'TICK_SIZE'] as $index => $type) {
                $rows[] = [
                    'market_structure_revision_id' => self::local($alloc, 'rule', $index), 'rule_uid' => hash('sha256', 'rule-'.$type),
                    'revision_number' => 1, 'rule_type' => $type, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
                    'instrument_scope_code' => 'IDX_REGULAR_STANDARD_EQUITY', 'coverage_scope_json' => '{}',
                    'effective_from' => '2026-01-01', 'verification_state' => 'AUTHORITATIVE_VERIFIED',
                    'source_uid' => hash('sha256', 'source-'.$type), 'source_observation_id' => self::local($alloc, 'observation', 8),
                    'source_reference' => 'IDX-RULE-'.$type,
                    'content_hash' => hash('sha256', 'content-'.$type.($variant === 'rule_content' && $type === 'TICK_SIZE' ? '-changed' : '')),
                    'recorded_at' => $variant === 'rule_recorded_at' && $type === 'PRICE_BAND' ? '2026-09-22 09:00:00' : '2026-09-21 09:00:00',
                ];
            }
            foreach ($alloc['reverse'] ? array_reverse($rows) : $rows as $row) {
                $db->table('md_exchange_market_structure_revisions')->insert($row);
            }
        };
        $steps[] = function () use ($db, $alloc, $o, $variant) {
            $common = [
                'listing_id' => $alloc['listing'], 'action_type_code' => 'STOCK_SPLIT', 'lifecycle_state' => 'EFFECTIVE',
                'verification_state' => 'AUTHORITATIVE_VERIFIED', 'source_observation_id' => self::local($alloc, 'observation', 6),
            ];
            $rows = [
                $common + ['corporate_action_revision_id' => self::local($alloc, 'event', 0), 'event_uid' => hash('sha256', 'ksei|ID1000000001|DOC-1'),
                    'revision_number' => 1, 'ex_date' => '2026-09-25', 'terms_json' => '{"ratio":{"from":1,"to":2}}',
                    'recorded_at' => '2026-09-10 10:00:00', 'supersedes_revision_id' => null],
                $common + ['corporate_action_revision_id' => self::local($alloc, 'event', 1), 'event_uid' => hash('sha256', 'ksei|ID1000000001|DOC-1'),
                    'revision_number' => 2, 'ex_date' => '2026-09-25',
                    'terms_json' => $variant === 'event_terms' ? '{"ratio":{"from":1,"to":4}}' : '{"ratio":{"from":1,"to":5}}',
                    'recorded_at' => $variant === 'event_recorded_at' ? '2026-09-16 10:00:00' : '2026-09-15 10:00:00',
                    'supersedes_revision_id' => self::local($alloc, 'event', 0)],
                $common + ['corporate_action_revision_id' => self::local($alloc, 'event', 2), 'event_uid' => hash('sha256', 'ksei|ID1000000001|DOC-2'),
                    'revision_number' => 1, 'ex_date' => '2026-09-26', 'terms_json' => '{"ratio":{"from":2,"to":1}}',
                    'recorded_at' => '2026-09-18 10:00:00', 'supersedes_revision_id' => null],
            ];
            foreach ($alloc['reverse'] ? array_reverse($rows) : $rows as $row) {
                $db->table('md_corporate_action_revisions')->insert($row);
            }
        };
        $steps[] = function () use ($db, $alloc, $o, $configHash) {
            $db->table('eod_runs')->insert([
                'run_id' => $alloc['run'], 'trade_date_requested' => self::TRADE_DATE, 'trade_date_effective' => self::TRADE_DATE,
                'lifecycle_state' => 'RUNNING', 'stage' => 'HASH', 'source' => 'api', 'request_mode' => 'promote',
                'knowledge_cutoff_at' => self::CUTOFF, 'config_snapshot_id' => $alloc['config'], 'config_hash' => $configHash,
                'config_snapshot_ref' => hash('sha256', 'snapshot-'.$alloc['uid']), 'config_version' => 'v1',
                'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
                'publication_semantic_profile' => PublicationSemanticIdentityService::PROFILE_V2,
                'quality_gate_state' => 'PASS', 'coverage_gate_state' => 'PASS', 'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'coverage_ratio' => '1.0000', 'freshness_state' => 'FRESH', 'publishability_state' => 'NOT_READABLE',
                'bars_rows_written' => 1, 'indicators_rows_written' => 1, 'eligibility_rows_written' => 1,
                'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'structural_adjusted_v1',
                'started_at' => '2026-09-29 00:38:15', 'created_at' => '2026-09-29 00:38:15', 'updated_at' => '2026-09-29 00:38:15',
            ]);
            $db->table('eod_publications')->insert([
                'publication_id' => $alloc['publication'], 'trade_date' => self::TRADE_DATE, 'run_id' => $alloc['run'],
                'publication_version' => 1, 'is_current' => 0, 'seal_state' => 'UNSEALED', 'config_snapshot_id' => $alloc['config'],
                'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
                'publication_semantic_profile' => PublicationSemanticIdentityService::PROFILE_V2,
                'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'structural_adjusted_v1',
                'read_model_version' => 'market_data_read_product_v1', 'readiness_state' => 'BUILDING',
                'created_at' => '2026-09-29 00:38:15', 'updated_at' => '2026-09-29 00:38:15',
            ]);
            $db->table('eod_bars_history')->insert([
                'publication_id' => $alloc['publication'], 'trade_date' => self::TRADE_DATE, 'ticker_id' => $alloc['ticker'],
                'listing_id' => $alloc['listing'], 'source_observation_id' => self::local($alloc, 'observation', 2), 'open' => '100.0000', 'high' => '110.0000',
                'low' => '95.0000', 'close' => '105.0000', 'volume' => 1000, 'source' => 'YAHOO_FINANCE', 'run_id' => $alloc['run'],
                'board_code' => 'DEVELOPMENT', 'session_code' => 'REGULAR', 'canonicalization_version' => 'eod_canonical_v1',
                'price_product_code' => 'RAW', 'quality_state' => 'VALIDATED', 'config_snapshot_id' => $alloc['config'],
                'created_at' => '2026-09-29 00:37:00',
            ]);
            $db->table('eod_eligibility_history')->insert([
                'publication_id' => $alloc['publication'], 'trade_date' => self::TRADE_DATE, 'ticker_id' => $alloc['ticker'],
                'listing_id' => $alloc['listing'], 'eligible' => 1, 'run_id' => $alloc['run'], 'created_at' => '2026-09-29 00:37:30',
                'bar_expectation_state' => 'EXPECTED', 'temporal_status_state' => 'ACTIVE',
                'trading_status_revision_id' => $alloc['status'], 'trading_status_source_observation_id' => self::local($alloc, 'observation', 7),
            ]);
        };

        foreach ($alloc['reverse'] ? array_reverse($steps) : $steps as $step) {
            $step();
        }
    }

    private function observationRows(array $alloc, int $o, string $variant): array
    {
        $uid = static function (string $label) use ($alloc): string {
            return substr(hash('sha256', $alloc['uid'].'|'.$label), 0, 32);
        };
        $base = [
            'run_id' => $alloc['run'], 'requested_trade_date' => self::TRADE_DATE, 'source_name' => 'YAHOO_FINANCE',
            'source_mode' => 'api', 'provider' => 'YAHOO_FINANCE', 'provider_symbol' => 'IKPM.JK',
            'provider_mapping_id' => $alloc['mapping'], 'mapping_revision' => 'mapping-r1',
            'sanitized_request_identity' => 'chart:IKPM.JK:'.self::TRADE_DATE, 'response_status' => 200,
            'content_type' => 'application/json', 'source_timestamp' => '2026-09-29 00:30:00',
            'acquired_at' => $variant === 'observation_acquired_at' ? '2026-09-29 00:36:00' : '2026-09-29 00:35:00',
            'schema_fingerprint' => hash('sha256', 'yahoo-chart-schema'),
            'adapter_version' => $variant === 'observation_adapter' ? 'yahoo_chart_v3' : 'yahoo_chart_v2',
            'provider_schema_version' => 'yahoo_chart_schema_v1', 'bounded_payload_body' => '{}',
            'validation_state' => 'VALID', 'created_at' => '2026-09-29 00:35:30',
        ];
        $payload = static function (string $seed): array {
            $hash = hash('sha256', $seed);
            return ['payload_hash' => $hash, 'payload_ref' => 'sha256:'.$hash, 'payload_byte_length' => 2048];
        };
        $rows = [
            // Accepted bar capture and its outcome: the root that feeds bars and the assessment evidence.
            $base + $payload('bars-payload') + ['source_observation_id' => self::local($alloc, 'observation', 1), 'observation_uid' => $uid('capture-1'),
                'attempt_uid' => $uid('attempt-1'), 'outcome_state' => 'CAPTURED', 'reason_code' => null, 'parent_observation_id' => null],
            $base + $payload('bars-payload') + ['source_observation_id' => self::local($alloc, 'observation', 2), 'observation_uid' => $uid('outcome-1'),
                'attempt_uid' => $uid('attempt-1'), 'outcome_state' => 'ACCEPTED', 'reason_code' => null, 'parent_observation_id' => self::local($alloc, 'observation', 1)],
            // A rejected outcome of a second capture: listed by the manifest with its reason.
            $base + $payload('rejected-payload') + ['source_observation_id' => self::local($alloc, 'observation', 3), 'observation_uid' => $uid('capture-2'),
                'attempt_uid' => $uid('attempt-2'), 'outcome_state' => 'CAPTURED', 'reason_code' => null, 'parent_observation_id' => null],
            $base + $payload('rejected-payload') + ['source_observation_id' => self::local($alloc, 'observation', 4), 'observation_uid' => $uid('outcome-2'),
                'attempt_uid' => $uid('attempt-2'), 'outcome_state' => 'REJECTED',
                'reason_code' => $variant === 'observation_reason' ? 'SOURCE_SCHEMA_INVALID' : 'SOURCE_ROW_INVALID',
                'parent_observation_id' => self::local($alloc, 'observation', 3)],
            // Source documents of the governed revisions.
            $base + $payload('ksei-corporate-action-document') + ['source_observation_id' => self::local($alloc, 'observation', 6), 'observation_uid' => $uid('event-doc'),
                'attempt_uid' => $uid('attempt-6'), 'outcome_state' => 'ACCEPTED', 'reason_code' => null, 'parent_observation_id' => null],
            $base + $payload('status-snapshot') + ['source_observation_id' => self::local($alloc, 'observation', 7), 'observation_uid' => $uid('status-doc'),
                'attempt_uid' => $uid('attempt-7'), 'outcome_state' => 'ACCEPTED', 'reason_code' => null, 'parent_observation_id' => null],
            $base + $payload('idx-market-structure-rules') + ['source_observation_id' => self::local($alloc, 'observation', 8), 'observation_uid' => $uid('rule-doc'),
                'attempt_uid' => $uid('attempt-8'), 'outcome_state' => 'ACCEPTED', 'reason_code' => null, 'parent_observation_id' => null],
        ];

        return $alloc['reverse'] ? array_reverse($rows) : $rows;
    }
}
