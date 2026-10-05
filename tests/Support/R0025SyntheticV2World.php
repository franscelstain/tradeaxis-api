<?php

namespace Tests\Support;

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\EligibilityDecisionService;
use App\Application\MarketData\Services\EodBarsIngestService;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Application\MarketData\Services\EodIndicatorsComputeService;
use App\Application\MarketData\Services\FinalizeDecisionService;
use App\Application\MarketData\Services\IndicatorVectorService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\PublicationDiffService;
use App\Application\MarketData\Services\PublicationFinalizeOutcomeService;
use App\Application\SecurityIdentity\Contracts\IdentityResolver;
use App\Application\SecurityIdentity\FoundationService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The synthetic V2 world of the first independent MD-S003-R0025 golden fixture candidate.
 *
 * SYNTHETIC / TEST ONLY. Every input is read from the candidate package (`inputs/`), never generated from a run, a database id or a
 * publication. The retained roots are the fixed literals of `inputs/foundation_registry.json`, admitted through the foundation's own
 * restore path (`FoundationService::restore`, the canonical test admission for a fingerprinted registry). Local database rows
 * (ticker, listing, run, publication, snapshot ids) are allocated by MariaDB and may differ between builds; `$layout` varies them on
 * purpose. The ACTUAL side is the real production path: provider response -> `PublicApiEodBarsAdapter` -> pipeline -> artifacts ->
 * publication -> seal. Only the HTTP transport is substituted, and it returns the frozen response bytes unchanged.
 */
final class R0025SyntheticV2World
{
    /** The candidate package whose frozen inputs the world is built from. Candidate-v1, -v2 and -v3 were reviewed CHANGES REQUIRED and are retained untouched. */
    public const PACKAGE = 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v4';
    public const PACKAGE_V3 = 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v3';
    public const PACKAGE_V2 = 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v2';
    public const PACKAGE_V1 = 'tests/fixtures/replay/r0025-synthetic-v2-candidate-v1';

    public static function packagePath(): string
    {
        return base_path(self::PACKAGE);
    }

    /** @return array<string,mixed> */
    public static function world(): array
    {
        return json_decode((string) file_get_contents(self::packagePath().'/inputs/synthetic_world.json'), true);
    }

    /**
     * Builds the world inside the caller's transaction and runs the real pipeline once.
     *
     * `run_freshness_label` (TEST SUPPORT, candidate-v4 sensitivity): forces the freshness label the real run creator would write, through a model event, so that the
     * real pipeline seals a publication whose freshness is deliberately wrong. It changes nothing in application code.
     *
     * @param array{ticker_id?:int,preconsume?:int,calendar_order?:string,retained_registry?:bool,tamper_response?:string,profile?:string,run_clock?:string,run_freshness_label?:string} $layout
     *
     * @return array<string,mixed>
     */
    public static function build(array $layout = []): array
    {
        $world = self::world();
        $listing = $world['listing'];
        $date = $world['trade_date'];
        $db = DB::connection();
        $tickerId = (int) ($layout['ticker_id'] ?? 975100);

        // Allocation layout: consume auto-increment values so that every local id of this build differs from another layout's.
        $preconsume = (int) ($layout['preconsume'] ?? 0);
        foreach ($preconsume > 0 ? ['md_source_observations', 'md_source_observation_rows', 'md_config_snapshots', 'eod_runs', 'eod_publications'] : [] as $table) {
            for ($i = 0; $i < $preconsume; $i++) {
                self::consume($table);
            }
        }

        Carbon::setTestNow((string) ($layout['run_clock'] ?? $world['run_clock']));
        $profile = (string) ($layout['profile'] ?? ArtifactSemanticHashService::PROFILE_V2);
        config()->set('market_data_runtime.artifact_hash_profile', $profile);

        $db->table('tickers')->insert([
            'ticker_id' => $tickerId, 'ticker_code' => $listing['ticker_code'], 'company_name' => $listing['company_name'], 'is_active' => 1,
            'listed_date' => $listing['listed_date'], 'delisted_date' => null, 'created_at' => '2020-01-01 00:00:00', 'updated_at' => '2020-01-01 00:00:00',
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection();

        $calendar = $world['calendar'];
        $days = [];
        for ($day = new \DateTimeImmutable($calendar['first_date']); $day <= new \DateTimeImmutable($calendar['last_date']); $day = $day->modify('+1 day')) {
            $days[] = $day->format('Y-m-d');
        }
        if (($layout['calendar_order'] ?? 'asc') === 'desc') {
            $days = array_reverse($days);
        }
        foreach ($days as $d) {
            $trading = (int) (new \DateTimeImmutable($d))->format('N') <= 5;
            $ref = str_replace('{date}', $d, $calendar['source_ref_template']);
            $db->table('md_market_calendar_revisions')->updateOrInsert(
                ['market_code' => $calendar['market_code'], 'market_segment' => $calendar['market_segment'], 'cal_date' => $d],
                [
                    'revision_uid' => hash('sha256', 'r25-calendar|'.$d), 'timezone' => $calendar['timezone'], 'is_trading_day' => $trading ? 1 : 0, 'is_half_day' => 0,
                    'session_state' => $trading ? 'COMPLETED' : 'CLOSED', 'session_open_at' => $trading ? $d.' '.$calendar['session_open'] : null,
                    'session_close_at' => $trading ? $d.' '.$calendar['session_close'] : null, 'completed_at' => $trading ? $d.' '.$calendar['session_close'] : null,
                    'recorded_at' => $d.' '.$calendar['recorded_at_time'], 'source_observation_id' => null, 'supersedes_revision_id' => null, 'source_ref' => $ref,
                    'source_version' => $calendar['source_version'], 'provenance_tier' => $calendar['provenance_tier'], 'reconciled_at' => $d, 'reconciliation_source_ref' => $ref,
                ]
            );
        }

        $registryPath = self::packagePath().'/inputs/foundation_registry.json';
        $service = new FoundationService(new FoundationRepository($db));
        if ($profile === ArtifactSemanticHashService::PROFILE_V2 && ($layout['retained_registry'] ?? true) === true) {
            $service->restore($registryPath, hash_file('sha256', $registryPath));
        }
        app()->instance(IdentityResolver::class, $service);

        $body = (string) ($layout['tamper_response'] ?? file_get_contents(self::packagePath().'/inputs/provider_response.json'));
        $runs = new EodRunRepository();
        $tickers = new TickerMasterRepository();
        $publications = new EodPublicationRepository();
        $artifacts = new EodArtifactRepository();
        $bars = new EodBarsIngestService(
            new LocalFileEodBarsAdapter(),
            new PublicApiEodBarsAdapter(static function () use ($body) {
                return ['status' => 200, 'body' => $body, 'headers' => ['Content-Type: application/json']];
            }),
            $tickers,
            $artifacts,
            $publications
        );
        $pipeline = new MarketDataPipelineService(
            $runs,
            $bars,
            new EodIndicatorsComputeService($artifacts, $publications, new IndicatorVectorService()),
            new EodEligibilityBuildService($tickers, $artifacts, $publications, new EligibilityDecisionService()),
            $publications,
            new EodCorrectionRepository(),
            $artifacts,
            new DeterministicHashService(),
            new FinalizeDecisionService(),
            new PublicationDiffService(),
            new PublicationFinalizeOutcomeService(),
            new CoverageGateEvaluator(new TickerMasterRepository(), $artifacts)
        );

        $forcedLabel = $layout['run_freshness_label'] ?? null;
        if ($forcedLabel !== null) {
            \App\Models\EodRun::creating(static function ($model) use ($forcedLabel) {
                $model->freshness_state = $forcedLabel;
            });
        }
        try {
            $run = $pipeline->runDaily($date, 'api');
        } finally {
            if ($forcedLabel !== null) {
                \App\Models\EodRun::getEventDispatcher()->forget('eloquent.creating: '.\App\Models\EodRun::class);
            }
        }
        $publication = $db->table('eod_publications')->where('run_id', $run->run_id)->first();

        return [
            'run_id' => (int) $run->run_id,
            'publication_id' => $publication ? (int) $publication->publication_id : null,
            'publication' => $publication,
            'foundation' => $service,
            'publications' => $publications,
            'ticker_id' => $tickerId,
        ];
    }

    private static function consume(string $table): void
    {
        // An insert that is removed again still advances the table's auto-increment counter.
        $db = DB::connection();
        try {
            $columns = array_map(static function ($c) { return $c->Field; }, $db->select('SHOW COLUMNS FROM `'.$table.'`'));
            $key = $columns[0];
            $required = [];
            foreach ($db->select('SHOW COLUMNS FROM `'.$table.'`') as $c) {
                if ($c->Null === 'NO' && $c->Default === null && stripos((string) $c->Extra, 'auto_increment') === false) {
                    $required[$c->Field] = self::placeholder((string) $c->Type);
                }
            }
            $id = $db->table($table)->insertGetId($required, $key);
            $db->table($table)->where($key, $id)->delete();
        } catch (\Throwable $e) {
            // A table whose placeholder row cannot be inserted keeps its natural allocation; the layout then differs less.
        }
    }

    private static function placeholder(string $type)
    {
        if (stripos($type, 'int') !== false || stripos($type, 'decimal') !== false) {
            return 0;
        }
        if (stripos($type, 'date') !== false || stripos($type, 'timestamp') !== false) {
            return '2000-01-01 00:00:00';
        }

        return 'x';
    }
}
