<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestEvaluationRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistParamsetRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesWatchlistRuntimeSqlite;
use TestCase;

class WeeklySwingC171EvidenceIdentityTest extends TestCase
{
    use UsesWatchlistRuntimeSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootWatchlistRuntimeSqlite();
        $this->seedR1BaselineParamGrid();
    }

    protected function tearDown(): void
    {
        $this->tearDownWatchlistRuntimeSqlite();
        parent::tearDown();
    }

    public function testCanonicalAuditParamsetAdaptsToExecutionValuesWithoutChangingSourcePayload(): void
    {
        $payload = $this->payload();
        $copy = $payload;
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt($payload);

        $this->assertSame($copy, $payload);
        $this->assertSame(1000000000, $runtime['liquidity']['min_dv20_idr']);
        $this->assertSame(0.12, $runtime['risk']['max_atr14_pct']);
        $this->assertSame(0.3, $runtime['scoring']['weights']['momentum']);
        $this->assertSame('WEIGHTED_MEAN', $runtime['scoring']['combine_mode']);
        $this->assertSame('PLAN_GROUPING_DETERMINISTIC', $runtime['grouping']['grouping_mode']);
        $this->assertSame(0.8, $runtime['grouping']['top_min_score_q']);
        $this->assertSame(0.65, $runtime['grouping']['secondary_min_score_q']);
        $this->assertSame(5, $runtime['grouping']['top_picks']['max_items']);
        $this->assertSame(10, $runtime['grouping']['secondary']['max_items']);
        $this->assertSame(['score_total_desc','score_breakout_desc','score_momentum_desc','dv20_idr_desc','atr14_pct_asc','ticker_id_asc'], $runtime['grouping']['sort_keys']);

        $grouped = (new \App\Application\Watchlist\Services\WatchlistPlanGroupingService())
            ->groupScoredOutput([
                'ready' => true,
                'is_ready' => true,
                'trade_date' => '2025-01-02',
                'items' => [],
                'excluded' => [],
            ], $runtime, '2025-01-02');
        $this->assertSame('WATCHLIST_PLAN_GROUPING_READY', $grouped['reason_code']);
        $this->assertSame([], $grouped['paramset_errors']);

        $scored = (new \App\Application\Watchlist\Services\WatchlistScoringService())
            ->scoreCandidateUniverse([
                'ready' => true,
                'is_ready' => true,
                'trade_date' => '2025-01-02',
                'eligible_candidates' => [],
                'rejected_candidates' => [],
            ], $runtime, '2025-01-02');
        $this->assertSame('WATCHLIST_SCORING_EMPTY', $scored['reason_code']);
        $this->assertSame('WEIGHTED_MEAN', $scored['score_contract']['combine_mode']);
        $this->assertSame([], $scored['paramset_errors']);
    }

    public function testParamsetAndImplementationIdentityIsDeterministicAndKeyOrderIndependent(): void
    {
        $service = new WeeklySwingBacktestEvidenceIdentityService();
        $left = ['b' => 2, 'a' => ['y' => 2, 'x' => 1]];
        $right = ['a' => ['x' => 1, 'y' => 2], 'b' => 2];

        $this->assertSame($service->stableHash($left), $service->stableHash($right));
        $this->assertSame(40, strlen(WeeklySwingBacktestEvidenceIdentityService::implementationHash()));
        $this->assertSame(
            'WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3',
            WeeklySwingBacktestEvidenceIdentityService::EVIDENCE_PIPELINE_VERSION
        );
        $this->assertSame(40, strlen(WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash()));
        $this->assertSame(
            sha1(
                WeeklySwingBacktestEvidenceIdentityService::LEGACY_EVIDENCE_PIPELINE_VERSION.'|'.
                WeeklySwingBacktestEvidenceIdentityService::LEGACY_EVIDENCE_PIPELINE_CONTRACT
            ),
            WeeklySwingBacktestEvidenceIdentityService::LEGACY_EVIDENCE_PIPELINE_HASH
        );
        $this->assertSame(
            sha1(
                WeeklySwingBacktestEvidenceIdentityService::PREVIOUS_EVIDENCE_PIPELINE_VERSION.'|'.
                WeeklySwingBacktestEvidenceIdentityService::PREVIOUS_EVIDENCE_PIPELINE_CONTRACT
            ),
            WeeklySwingBacktestEvidenceIdentityService::PREVIOUS_EVIDENCE_PIPELINE_HASH
        );
        $this->assertSame(
            '9e9933b363026623b7ab5629f3281fa680a53a2e',
            WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash()
        );
        $identity = $service->identity($left, 'MODEL');
        $this->assertSame(
            WeeklySwingBacktestEvidenceIdentityService::EVIDENCE_PIPELINE_VERSION,
            $identity['evidence_pipeline_version']
        );
        $this->assertSame(
            WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash(),
            $identity['evidence_pipeline_hash']
        );
    }


    public function testStreamingListHashMatchesCanonicalInMemoryHash(): void
    {
        $service = new WeeklySwingBacktestEvidenceIdentityService();
        $rows = [
            ['b' => 2, 'a' => 1],
            ['nested' => ['y' => 2, 'x' => 1]],
        ];

        $this->assertSame($service->stableHash($rows), $service->stableListHash($rows));
    }

    public function testStreamingOfficialEvidenceManifestMatchesInMemoryContract(): void
    {
        $repository = new WatchlistBacktestOfficialEvidenceRepository();
        $universe = [
            'asof_eod_date' => '2025-01-02',
            'ticker_id' => 7,
            'ticker_code' => 'TEST',
            'required_ok' => true,
            'guard_ok' => true,
            'eligible_ok' => true,
            'dv20_idr' => 5000000000,
            'atr14_pct' => 0.05,
            'vol_ratio' => 1.5,
            'source_publication_id' => 10,
            'source_publication_version' => 2,
            'source_run_id' => 9,
        ];
        $cutoff = [
            'asof_eod_date' => '2025-01-02',
            'top_cutoff_score' => 0.8,
            'secondary_cutoff_score' => 0.65,
            'source_publication_id' => 10,
            'source_publication_version' => 2,
            'source_run_id' => 9,
        ];
        $trades = [[
            'trade_date' => '2025-01-02',
            'ticker_id' => 7,
            'score_total' => 0.91,
            'source_reference' => ['publication_id' => 10, 'publication_version' => 2, 'run_id' => 9],
        ]];
        $evaluations = [[
            'trade_date' => '2025-01-02',
            'ticker_id' => 7,
            'ticker' => 'TEST',
            'bucket_code' => 'TOP_PICKS',
            'metrics_ready' => true,
            'ret_net' => 0.03,
            'entry_publication_id' => 10,
            'entry_publication_version' => 2,
            'entry_run_id' => 9,
        ]];
        $inMemory = $repository->buildManifest('WS', 1, [
            'trades' => $trades,
            'official_evidence' => ['universe' => [$universe], 'cutoffs' => [$cutoff]],
        ], $evaluations);

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'c171-streaming-test-'.uniqid('', true);
        mkdir($directory, 0775, true);
        $universePath = $directory.DIRECTORY_SEPARATOR.'universe.jsonl';
        $cutoffsPath = $directory.DIRECTORY_SEPARATOR.'cutoffs.jsonl';
        file_put_contents($universePath, json_encode($universe, JSON_UNESCAPED_SLASHES).PHP_EOL);
        file_put_contents($cutoffsPath, json_encode($cutoff, JSON_UNESCAPED_SLASHES).PHP_EOL);

        try {
            $streaming = $repository->buildManifest('WS', 1, [
                'trades' => $trades,
                'official_evidence' => [
                    'storage_mode' => 'JSONL_SPOOL',
                    'finalized' => true,
                    'universe_spool_path' => $universePath,
                    'cutoffs_spool_path' => $cutoffsPath,
                    'universe_count' => 1,
                    'cutoff_count' => 1,
                ],
            ], $evaluations);

            $this->assertSame('JSONL_SPOOL', $streaming['storage_mode']);
            $this->assertSame($inMemory['manifest'], $streaming['manifest']);
            $this->assertFileExists($streaming['universe_spool_path']);
            $this->assertFileExists($streaming['cutoffs_spool_path']);
        } finally {
            foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $path) {
                @unlink($path);
            }
            @rmdir($directory);
        }
    }


    public function testEvaluationRepositoryPersistsEvidenceHashesAsStringsNotNumericZero(): void
    {
        $hashes = [
            'picks_hash' => sha1('picks'),
            'universe_hash' => sha1('universe'),
            'cutoffs_hash' => sha1('cutoffs'),
            'evidence_manifest_hash' => sha1('manifest'),
            'market_data_lineage_hash' => sha1('lineage'),
        ];
        $row = array_merge([
            'policy_code' => 'WS',
            'catalog_code' => 'WS_BT_GRID_BOOTSTRAP_2026_06',
            'catalog_version' => 'R1',
            'catalog_hash' => '9da8b0983c57bde1ce0a1fbf1c119756f8af431c',
            'param_id' => 1,
            'eval_model' => 'MODEL',
            'eval_model_hash' => sha1('MODEL'),
            'implementation_version' => WeeklySwingBacktestEvidenceIdentityService::IMPLEMENTATION_VERSION,
            'implementation_hash' => WeeklySwingBacktestEvidenceIdentityService::implementationHash(),
            'paramset_hash' => sha1('PARAMSET'),
            'from_date' => '2025-01-01',
            'to_date' => '2025-01-31',
            'days_covered' => 20,
            'picks_count' => 1,
            'universe_count' => 1,
            'cutoff_count' => 1,
            'avg_ret_net_top' => 0.03,
            'win_rate_top' => 1.0,
            'median_ret_net_top' => 0.03,
            'p25_ret_net_top' => 0.03,
            'p75_ret_net_top' => 0.03,
            'min_ret_net_top' => 0.03,
            'max_ret_net_top' => 0.03,
            'periods_count' => 1,
            'period_fail_count' => 0,
            'month_win_rate_min' => 1.0,
            'month_avg_ret_net_min' => 0.03,
        ], $hashes);

        $persisted = (new WatchlistBacktestEvaluationRepository())->persist($row);
        $database = (array) DB::table('watchlist_bt_eval')->where('eval_id', $persisted['eval_id'])->first();

        foreach ($hashes as $field => $hash) {
            $this->assertSame($hash, $database[$field]);
        }
        $this->assertSame(
            WeeklySwingBacktestEvidenceIdentityService::EVIDENCE_PIPELINE_VERSION,
            $database['evidence_pipeline_version']
        );
        $this->assertSame(
            WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash(),
            $database['evidence_pipeline_hash']
        );
    }

    public function testEvidencePipelineVersionCreatesNewEvalWithoutMutatingLegacyIdentity(): void
    {
        $base = [
            'policy_code' => 'WS',
            'catalog_code' => 'WS_BT_GRID_BOOTSTRAP_2026_06',
            'catalog_version' => 'R1',
            'catalog_hash' => '9da8b0983c57bde1ce0a1fbf1c119756f8af431c',
            'param_id' => 1,
            'eval_model' => 'MODEL',
            'eval_model_hash' => sha1('MODEL'),
            'implementation_version' => WeeklySwingBacktestEvidenceIdentityService::IMPLEMENTATION_VERSION,
            'implementation_hash' => WeeklySwingBacktestEvidenceIdentityService::implementationHash(),
            'paramset_hash' => sha1('PARAMSET'),
            'from_date' => '2025-01-01',
            'to_date' => '2025-01-31',
            'days_covered' => 20,
            'picks_count' => 1,
            'universe_count' => 1,
            'cutoff_count' => 1,
            'avg_ret_net_top' => 0.03,
            'win_rate_top' => 1.0,
            'median_ret_net_top' => 0.03,
            'p25_ret_net_top' => 0.03,
            'p75_ret_net_top' => 0.03,
            'min_ret_net_top' => 0.03,
            'max_ret_net_top' => 0.03,
            'periods_count' => 1,
            'period_fail_count' => 0,
            'month_win_rate_min' => 1.0,
            'month_avg_ret_net_min' => 0.03,
        ];
        $repository = new WatchlistBacktestEvaluationRepository();
        $legacy = $repository->persist(array_merge($base, [
            'evidence_pipeline_version' => WeeklySwingBacktestEvidenceIdentityService::LEGACY_EVIDENCE_PIPELINE_VERSION,
            'evidence_pipeline_hash' => WeeklySwingBacktestEvidenceIdentityService::LEGACY_EVIDENCE_PIPELINE_HASH,
        ]));
        $corrected = $repository->persist($base);
        $correctedRerun = $repository->persist($base);

        $this->assertSame('INSERTED', $legacy['status']);
        $this->assertSame('INSERTED', $corrected['status']);
        $this->assertNotSame($legacy['eval_id'], $corrected['eval_id']);
        $this->assertSame('IDEMPOTENT', $correctedRerun['status']);
        $this->assertSame($corrected['eval_id'], $correctedRerun['eval_id']);
        $this->assertSame(2, DB::table('watchlist_bt_eval')->count());
    }

    public function testExistingParamsetHashRejectsDifferentImmutableProvenance(): void
    {
        $repository = new WatchlistParamsetRepository();
        $repository->persistDraft($this->payload(), ['source' => 'C169', 'bt_binding' => ['bt_param_id' => 1]]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WS_C171_PARAMSET_IDENTITY_CONFLICT');
        $repository->persistDraft($this->payload(), ['source' => 'DIFFERENT_SOURCE', 'bt_binding' => ['bt_param_id' => 1]]);
    }

    public function testOfficialEvidenceIsPersistedUnderExactEvalIdAndRerunIsIdempotent(): void
    {
        $repository = new WatchlistBacktestOfficialEvidenceRepository();
        $evidence = $repository->buildManifest('WS', 1, [
            'trades' => [[
                'trade_date' => '2025-01-02',
                'ticker_id' => 7,
                'score_total' => 0.91,
                'source_reference' => ['publication_id' => 10, 'publication_version' => 2, 'run_id' => 9],
            ]],
            'official_evidence' => [
                'universe' => [[
                    'asof_eod_date' => '2025-01-02', 'ticker_id' => 7, 'ticker_code' => 'TEST',
                    'required_ok' => true, 'guard_ok' => true, 'eligible_ok' => true,
                    'dv20_idr' => 5000000000, 'atr14_pct' => 0.05, 'vol_ratio' => 1.5,
                    'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
                ]],
                'cutoffs' => [[
                    'asof_eod_date' => '2025-01-02', 'top_cutoff_score' => 0.8, 'secondary_cutoff_score' => 0.65,
                    'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
                ]],
            ],
        ], [[
            'trade_date' => '2025-01-02', 'ticker_id' => 7, 'ticker' => 'TEST', 'bucket_code' => 'TOP_PICKS',
            'metrics_ready' => true, 'ret_net' => 0.03, 'entry_publication_id' => 10, 'entry_publication_version' => 2, 'entry_run_id' => 9,
        ]]);
        $manifest = $evidence['manifest'];
        DB::table('watchlist_bt_eval')->insert([
            'eval_id' => 99, 'policy_code' => 'WS', 'param_id' => 1,
            'eval_model' => 'MODEL', 'eval_model_hash' => sha1('MODEL'),
            'implementation_version' => WeeklySwingBacktestEvidenceIdentityService::IMPLEMENTATION_VERSION,
            'implementation_hash' => WeeklySwingBacktestEvidenceIdentityService::implementationHash(),
            'paramset_hash' => sha1('PARAMSET'), 'from_date' => '2025-01-01', 'to_date' => '2025-01-31',
            'days_covered' => 20, 'picks_count' => 1, 'picks_hash' => $manifest['picks_hash'],
            'universe_count' => 1, 'universe_hash' => $manifest['universe_hash'],
            'cutoff_count' => 1, 'cutoffs_hash' => $manifest['cutoffs_hash'],
            'evidence_manifest_hash' => $manifest['evidence_manifest_hash'],
            'market_data_lineage_hash' => $manifest['market_data_lineage_hash'],
            'avg_ret_net_top' => 0.03, 'median_ret_net_top' => 0.03, 'p25_ret_net_top' => 0.03,
            'month_win_rate_min' => 1.0, 'month_avg_ret_net_min' => 0.03,
        ]);

        $first = $repository->persist(99, $evidence);
        $second = $repository->persist(99, $evidence);

        $this->assertSame('INSERTED', $first['status']);
        $this->assertSame('IDEMPOTENT', $second['status']);
        $this->assertSame($manifest, $repository->databaseManifest(99));
        $this->assertSame(1, DB::table('watchlist_bt_picks_ws')->where('eval_id', 99)->count());
    }

    public function testOfficialPicksUseTheSameTopMetricsReadyPopulationAsCanonicalMetrics(): void
    {
        $repository = new WatchlistBacktestOfficialEvidenceRepository();
        $source = ['publication_id' => 10, 'publication_version' => 2, 'run_id' => 9];
        $evidence = $repository->buildManifest('WS', 1, [
            'trades' => [
                ['trade_date' => '2025-01-02', 'ticker_id' => 7, 'score_total' => 0.91, 'source_reference' => $source],
                ['trade_date' => '2025-01-02', 'ticker_id' => 8, 'score_total' => 0.80, 'source_reference' => $source],
                ['trade_date' => '2025-01-02', 'ticker_id' => 9, 'score_total' => 0.75, 'source_reference' => $source],
            ],
            'official_evidence' => [
                'universe' => [[
                    'asof_eod_date' => '2025-01-02', 'ticker_id' => 7, 'ticker_code' => 'TOP',
                    'required_ok' => true, 'guard_ok' => true, 'eligible_ok' => true,
                    'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
                ]],
                'cutoffs' => [[
                    'asof_eod_date' => '2025-01-02', 'top_cutoff_score' => 0.8, 'secondary_cutoff_score' => 0.65,
                    'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
                ]],
            ],
        ], [
            ['trade_date' => '2025-01-02', 'ticker_id' => 7, 'ticker' => 'TOP', 'bucket_code' => 'TOP_PICKS', 'metrics_ready' => true, 'ret_net' => 0.03],
            ['trade_date' => '2025-01-02', 'ticker_id' => 8, 'ticker' => 'SECONDARY', 'bucket_code' => 'SECONDARY', 'metrics_ready' => true, 'ret_net' => 0.02],
            ['trade_date' => '2025-01-02', 'ticker_id' => 9, 'ticker' => 'NOT_READY', 'bucket_code' => 'TOP_PICKS', 'metrics_ready' => false, 'ret_net' => 0.01],
        ]);

        $this->assertSame(1, $evidence['manifest']['picks_count']);
        $this->assertSame('TOP', $evidence['picks'][0]['ticker_code']);
    }


    public function testOfficialEvidenceRejectsMissingMarketDataLineage(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WS_C171_OFFICIAL_UNIVERSE_LINEAGE_MISSING');

        (new WatchlistBacktestOfficialEvidenceRepository())->buildManifest('WS', 1, [
            'trades' => [],
            'official_evidence' => [
                'universe' => [[
                    'asof_eod_date' => '2025-01-02', 'ticker_id' => 7, 'ticker_code' => 'TEST',
                    'required_ok' => true, 'guard_ok' => true, 'eligible_ok' => true,
                ]],
                'cutoffs' => [[
                    'asof_eod_date' => '2025-01-02', 'top_cutoff_score' => 0.8, 'secondary_cutoff_score' => 0.65,
                    'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
                ]],
            ],
        ], []);
    }

    public function testOfficialEvidenceRejectsUniverseAndCutoffDateCoverageMismatch(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WS_C171_OFFICIAL_EVIDENCE_DATE_COVERAGE_MISMATCH');

        (new WatchlistBacktestOfficialEvidenceRepository())->buildManifest('WS', 1, [
            'trades' => [],
            'official_evidence' => [
                'universe' => [[
                    'asof_eod_date' => '2025-01-02', 'ticker_id' => 7, 'ticker_code' => 'TEST',
                    'required_ok' => true, 'guard_ok' => true, 'eligible_ok' => true,
                    'source_publication_id' => 10, 'source_publication_version' => 2, 'source_run_id' => 9,
                ]],
                'cutoffs' => [[
                    'asof_eod_date' => '2025-01-03', 'top_cutoff_score' => 0.8, 'secondary_cutoff_score' => 0.65,
                    'source_publication_id' => 11, 'source_publication_version' => 2, 'source_run_id' => 10,
                ]],
            ],
        ], []);
    }

    public function testC171RejectsNonCanonicalIsWindowBeforeDatabaseExecution(): void
    {
        $result = (new \App\Application\Watchlist\Services\WeeklySwingC171VersionedOfficialIsEvidenceService())
            ->execute(1, '2023-01-03', '2025-05-21', 'C171_TEST', true, 'unused.json');

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('C171_REJECTED_CANONICAL_IS_WINDOW_MISMATCH', $result['reason_code']);
        $this->assertFalse($result['oos_runtime_invoked']);
    }

    private function payload(): array
    {
        return json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
    }
}
