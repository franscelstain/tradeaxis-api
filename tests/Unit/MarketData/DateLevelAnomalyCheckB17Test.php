<?php

use App\Application\MarketData\Services\DateLevelAnomalyCheckService;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B17-A002` guard for the date-level anomaly checks owned by
 * `Run_Status_and_Quality_Gates_LOCKED.md`.
 *
 * The contract explains why they exist:
 *
 *   > Row-level validation cannot, by construction, see a pattern across rows. A defect affecting
 *   > many instruments on one acquisition date presents as many individually admissible rows, and
 *   > every per-row rule passes.
 *
 * It names three measures — zero-volume share, flat-bar share, cross-field contradiction count —
 * and none of them existed anywhere in the platform. Every bar in a broken acquisition could be
 * individually admissible and nothing would look at the shape of the date.
 *
 * The thresholds are loaded from the immutable configuration snapshot identified by the owning
 * run. Tests below prove both that binding and its fail-closed boundary.
 */
class DateLevelAnomalyCheckB17Test extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-07-28';

    /** @var array<string,mixed> */
    private array $configSnapshot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configSnapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);

        $date = strtotime(self::TRADE_DATE);
        $added = 0;
        while ($added < 20) {
            if ((int) date('N', $date) <= 5) {
                $this->seedVerifiedMarketCalendarDate(date('Y-m-d', $date));
                $added++;
            }
            $date = strtotime('-1 day', $date);
        }
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /**
     * @param  array<int,array<string,mixed>>  $shape  per-bar overrides applied in order
     */
    private function seedBars(string $date, int $count, array $shape = []): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $row = [
                'trade_date' => $date, 'ticker_id' => $i,
                'open' => 100, 'high' => 102, 'low' => 99, 'close' => 101,
                'volume' => 1000, 'adj_close' => 101,
                'source' => 'YAHOO_FINANCE', 'run_id' => 1, 'publication_id' => 1,
                'created_at' => $date.' 18:00:00',
            ];
            if (isset($shape[$i - 1])) {
                $row = array_merge($row, $shape[$i - 1]);
            }
            DB::table('eod_bars')->insert($row);
        }
    }

    private function service(): DateLevelAnomalyCheckService
    {
        return app(DateLevelAnomalyCheckService::class);
    }

    private function evaluateDate(string $date = self::TRADE_DATE): array
    {
        return $this->service()->evaluate(
            $date,
            null,
            null,
            (int) $this->configSnapshot['config_snapshot_id'],
            (string) $this->configSnapshot['config_hash']
        );
    }

    /** @param callable(array<string,mixed>):void $mutate */
    private function replaceSnapshotPayload(callable $mutate): string
    {
        $payload = json_decode((string) $this->configSnapshot['resolved_config_json'], true);
        $mutate($payload);
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        );
        $this->assertIsString($json);
        $hash = hash('sha256', $json);
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $this->configSnapshot['config_snapshot_id'])
            ->update(['resolved_config_json' => $json, 'config_hash' => $hash]);

        return $hash;
    }

    /** A clean date measures all three and finds nothing. */
    public function test_a_clean_date_measures_every_declared_dimension_and_reports_clean(): void
    {
        $this->seedBars(self::TRADE_DATE, 30);

        $result = $this->evaluateDate();

        $this->assertSame(DateLevelAnomalyCheckService::STATE_CLEAN, $result['date_level_anomaly_state']);
        $this->assertSame([], $result['date_level_anomaly_findings']);

        foreach (['zero_volume_share', 'flat_bar_share', 'cross_field_contradiction_count'] as $measure) {
            $this->assertArrayHasKey($measure, $result, $measure.' is not measured at all');
            $this->assertNotNull($result[$measure], $measure.' is measured as null on an evaluable date');
        }
        $this->assertSame(0.0, $result['zero_volume_share']);
        $this->assertSame(0.0, $result['flat_bar_share']);
        $this->assertSame(0, $result['cross_field_contradiction_count']);
        $this->assertSame(30, $result['date_level_anomaly_delivered_count']);
    }

    /**
     * A date where most bars carry zero volume is an acquisition-fault finding, not a market
     * observation about the instruments. Each bar on its own is admissible.
     */
    public function test_an_elevated_zero_volume_share_is_a_date_level_finding(): void
    {
        $shape = [];
        for ($i = 0; $i < 20; $i++) {
            $shape[$i] = ['volume' => 0];
        }
        $this->seedBars(self::TRADE_DATE, 30, $shape);

        $result = $this->evaluateDate();

        $this->assertSame(DateLevelAnomalyCheckService::STATE_FINDING, $result['date_level_anomaly_state']);
        $this->assertContains('ZERO_VOLUME_SHARE_ABOVE_THRESHOLD', $result['date_level_anomaly_findings']);
        $this->assertGreaterThan(
            $result['date_level_anomaly_thresholds']['zero_volume_share_max'],
            $result['zero_volume_share']
        );
    }

    /**
     * A flat bar suppresses true range for every dependent window, so a date full of them is a
     * finding even though each row passes every per-row rule.
     */
    public function test_an_elevated_flat_bar_share_is_a_date_level_finding(): void
    {
        $shape = [];
        for ($i = 0; $i < 15; $i++) {
            $shape[$i] = ['open' => 100, 'high' => 100, 'low' => 100, 'close' => 100];
        }
        $this->seedBars(self::TRADE_DATE, 30, $shape);

        $result = $this->evaluateDate();

        $this->assertSame(DateLevelAnomalyCheckService::STATE_FINDING, $result['date_level_anomaly_state']);
        $this->assertContains('FLAT_BAR_SHARE_ABOVE_THRESHOLD', $result['date_level_anomaly_findings']);
    }

    /** A single cross-field contradiction concentrated on one date is systematic, not incidental. */
    public function test_any_cross_field_contradiction_is_a_date_level_finding(): void
    {
        $this->seedBars(self::TRADE_DATE, 30, [0 => ['high' => 90, 'low' => 99]]);

        $result = $this->evaluateDate();

        $this->assertSame(1, $result['cross_field_contradiction_count']);
        $this->assertContains('CROSS_FIELD_CONTRADICTION_CONCENTRATED_ON_DATE', $result['date_level_anomaly_findings']);
    }

    /**
     * The neighbour baseline is drawn from governed trading days. A date whose own share is under
     * the absolute threshold is still a finding when it stands well above its neighbours, which is
     * the concentration the checks exist to catch.
     */
    public function test_the_neighbour_baseline_uses_governed_trading_days_and_detects_concentration(): void
    {
        // Neighbours: a low but non-zero baseline share.
        foreach (['2026-07-21', '2026-07-22', '2026-07-23', '2026-07-24', '2026-07-27'] as $n) {
            $shape = [0 => ['volume' => 0]];
            $this->seedBars($n, 30, $shape);
        }
        // The date under test: five times the neighbour share, still under the absolute threshold.
        $shape = [];
        for ($i = 0; $i < 5; $i++) {
            $shape[$i] = ['volume' => 0];
        }
        $this->seedBars(self::TRADE_DATE, 30, $shape);

        $result = $this->evaluateDate();

        $this->assertNotSame([], $result['date_level_anomaly_neighbour_dates'],
            'the neighbour comparison resolved no governed trading days, so it compared against nothing');
        $this->assertNotNull($result['zero_volume_neighbour_baseline']);
        $this->assertLessThanOrEqual(
            $result['date_level_anomaly_thresholds']['zero_volume_share_max'],
            $result['zero_volume_share'],
            'this case must sit under the absolute threshold, or it proves the wrong branch'
        );
        $this->assertContains('ZERO_VOLUME_SHARE_ELEVATED_AGAINST_NEIGHBOURS', $result['date_level_anomaly_findings']);
    }

    /**
     * A share over a handful of rows is noise. Saying so is not the same as saying the date is
     * clean, which is why the state is `NOT_EVALUABLE` and the measures are null rather than zero.
     */
    public function test_too_few_rows_is_not_evaluable_rather_than_clean(): void
    {
        $this->seedBars(self::TRADE_DATE, 3);

        $result = $this->evaluateDate();

        $this->assertSame(DateLevelAnomalyCheckService::STATE_NOT_EVALUABLE, $result['date_level_anomaly_state']);
        $this->assertSame('DELIVERED_ROWS_BELOW_MINIMUM', $result['date_level_anomaly_not_evaluable_reason']);
        $this->assertNull($result['zero_volume_share'], 'an unevaluable date must not report a measured share');
        $this->assertNotSame(DateLevelAnomalyCheckService::STATE_CLEAN, $result['date_level_anomaly_state']);
    }

    /** A finding is quality evidence: it records, it does not delete or alter rows. */
    public function test_a_finding_alters_no_row(): void
    {
        $shape = [];
        for ($i = 0; $i < 20; $i++) {
            $shape[$i] = ['volume' => 0];
        }
        $this->seedBars(self::TRADE_DATE, 30, $shape);

        $before = DB::table('eod_bars')->where('trade_date', self::TRADE_DATE)->orderBy('ticker_id')->get()->toArray();
        $result = $this->evaluateDate();
        $after = DB::table('eod_bars')->where('trade_date', self::TRADE_DATE)->orderBy('ticker_id')->get()->toArray();

        $this->assertSame(DateLevelAnomalyCheckService::STATE_FINDING, $result['date_level_anomaly_state']);
        $this->assertEquals($before, $after, 'a date-level finding must not delete or alter any row');
    }

    public function test_thresholds_are_loaded_from_the_owning_run_configuration_snapshot(): void
    {
        $this->seedBars(self::TRADE_DATE, 30);

        $result = $this->evaluateDate();
        $snapshotPayload = json_decode((string) $this->configSnapshot['resolved_config_json'], true);
        $snapshotThresholds = $snapshotPayload['resolved_config']['quality_gates']['date_level_anomaly'];

        $this->assertSame(
            DateLevelAnomalyCheckService::THRESHOLD_BINDING_STATE,
            $result['date_level_anomaly_threshold_binding'],
            'the result must state that its thresholds are bound to the run snapshot'
        );
        $this->assertSame($snapshotThresholds, $result['date_level_anomaly_thresholds']);
        $this->assertSame((int) $this->configSnapshot['config_snapshot_id'], $result['date_level_anomaly_config_snapshot_id']);
        $this->assertSame((string) $this->configSnapshot['config_hash'], $result['date_level_anomaly_config_hash']);

        $registry = (string) file_get_contents(
            dirname(__DIR__, 3).'/docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md'
        );
        foreach (array_keys($snapshotThresholds) as $key) {
            $this->assertStringContainsString('market_data.quality_gates.date_level_anomaly.'.$key, $registry);
        }
    }

    public function test_current_configuration_drift_cannot_change_a_historical_run_evaluation(): void
    {
        $shape = [];
        for ($i = 0; $i < 20; $i++) {
            $shape[$i] = ['volume' => 0];
        }
        $this->seedBars(self::TRADE_DATE, 30, $shape);

        config(['market_data.quality_gates.date_level_anomaly.zero_volume_share_max' => 1.0]);
        $result = $this->evaluateDate();

        $this->assertSame(0.30, $result['date_level_anomaly_thresholds']['zero_volume_share_max']);
        $this->assertContains('ZERO_VOLUME_SHARE_ABOVE_THRESHOLD', $result['date_level_anomaly_findings']);
    }

    public function test_missing_run_snapshot_binding_fails_closed_before_measurement(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DATE_LEVEL_ANOMALY_CONFIG_SNAPSHOT_BINDING_REQUIRED');

        $this->service()->evaluate(self::TRADE_DATE);
    }

    public function test_run_hash_that_does_not_identify_the_snapshot_fails_closed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DATE_LEVEL_ANOMALY_RUN_CONFIG_HASH_MISMATCH');

        $this->service()->evaluate(
            self::TRADE_DATE,
            null,
            null,
            (int) $this->configSnapshot['config_snapshot_id'],
            str_repeat('a', 64)
        );
    }

    public function test_snapshot_bytes_that_do_not_match_the_stored_hash_fail_closed(): void
    {
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $this->configSnapshot['config_snapshot_id'])
            ->update(['resolved_config_json' => '{}']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DATE_LEVEL_ANOMALY_CONFIG_SNAPSHOT_CONTENT_HASH_MISMATCH');

        $this->evaluateDate();
    }

    public function test_snapshot_without_registered_threshold_subtree_fails_closed(): void
    {
        $hash = $this->replaceSnapshotPayload(static function (array &$payload): void {
            unset($payload['resolved_config']['quality_gates']['date_level_anomaly']);
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DATE_LEVEL_ANOMALY_CONFIG_MISSING_FROM_SNAPSHOT');

        $this->service()->evaluate(
            self::TRADE_DATE,
            null,
            null,
            (int) $this->configSnapshot['config_snapshot_id'],
            $hash
        );
    }

    public function test_out_of_range_snapshot_threshold_fails_closed(): void
    {
        $hash = $this->replaceSnapshotPayload(static function (array &$payload): void {
            $payload['resolved_config']['quality_gates']['date_level_anomaly']['minimum_rows'] = 0;
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DATE_LEVEL_ANOMALY_CONFIG_RANGE_INVALID');

        $this->service()->evaluate(
            self::TRADE_DATE,
            null,
            null,
            (int) $this->configSnapshot['config_snapshot_id'],
            $hash
        );
    }

    public function test_wrong_type_snapshot_threshold_fails_closed(): void
    {
        $hash = $this->replaceSnapshotPayload(static function (array &$payload): void {
            $payload['resolved_config']['quality_gates']['date_level_anomaly']['zero_volume_share_max'] = '0.30';
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DATE_LEVEL_ANOMALY_CONFIG_TYPE_INVALID');

        $this->service()->evaluate(
            self::TRADE_DATE,
            null,
            null,
            (int) $this->configSnapshot['config_snapshot_id'],
            $hash
        );
    }
    /**
     * The measurement is only worth having if promote actually runs it. A service nobody calls is
     * indistinguishable, from the run's evidence, from a service that does not exist.
     *
     * This reads the promote call site rather than the whole pipeline, because the behavioural
     * integration path is proven separately by `MarketDataPipelineIntegrationTest`; what is asserted
     * here is that the call exists, is bound to the requested date, and that its result reaches the
     * audit-visible stage event rather than being computed and discarded.
     */
    public function test_promote_runs_the_check_and_puts_its_result_on_the_audit_visible_event(): void
    {
        $pipeline = (string) file_get_contents(
            dirname(__DIR__, 3).'/app/Application/MarketData/Services/MarketDataPipelineService.php'
        );

        $this->assertStringContainsString(
            'DateLevelAnomalyCheckService::class',
            $pipeline,
            'promote never invokes the date-level anomaly check'
        );
        $this->assertMatchesRegularExpression(
            '/DateLevelAnomalyCheckService::class\)->evaluate\(\s*\$input->requestedDate,/',
            $pipeline,
            'the check must be evaluated for the requested date, not for some other date'
        );
        $this->assertStringContainsString(
            '\'date_level_anomaly\' => $anomaly,',
            $pipeline,
            'the measurement is computed and then discarded rather than recorded on the stage event'
        );
        $this->assertStringContainsString('$run->config_snapshot_id', $pipeline);
        $this->assertStringContainsString('$run->config_hash', $pipeline);
    }
}
