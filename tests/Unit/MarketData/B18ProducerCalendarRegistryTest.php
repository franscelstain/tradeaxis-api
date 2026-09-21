<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\ProducerRegistrySnapshot;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B18ProducerCalendarRegistryTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp(); $this->bootMarketDataSqlite();
        \Carbon\Carbon::setTestNow('2026-03-25 10:30:00');
        foreach (['2026-03-19', '2026-03-20', '2026-03-23', '2026-03-24'] as $date) $this->seedVerifiedMarketCalendarDate($date);
        $this->seedVerifiedMarketCalendarDate('2026-03-21', false);
        DB::table('eod_reason_codes')->insert([
            'code' => 'C1_TEST_REASON', 'category' => 'TEST', 'description' => 'Explicit fixture reason registry',
            'severity' => 'INFO', 'is_active' => 1,
        ]);
    }

    private function runContext()
    {
        return (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INDICATORS', null, 'c1-calendar');
    }

    public function test_target_dependency_closed_day_and_completion_retain_exact_revision_bytes(): void
    {
        $run = $this->runContext(); $calendar = new MarketCalendarRepository(); $captures = new RunInputCaptureRepository();
        $read = function () use ($calendar) {
            $session = $calendar->sessionContext('2026-03-24');
            $start = $calendar->tradingDateWindowStart('2026-03-24', 3);
            return [$session, $calendar->tradingDatesBetween($start, '2026-03-24')];
        };
        $value = ProducerInputScope::during($run, 'INDICATORS', 'calendar-fixture/v1', $read);
        $this->assertSame(['2026-03-20', '2026-03-23', '2026-03-24'], $value[1]);
        $before = $captures->forRun($run->run_id); $calendarRows = $manifests = [];
        foreach ($before as $row) {
            $payload = $captures->verify($row);
            if ($row['component_key'] === 'calendar_session') {
                $this->assertSame((string) $run->knowledge_cutoff_at, $payload['selection_context']['known_at']);
                foreach ($payload['rows'] as $revision) $calendarRows[$revision['cal_date']] = $revision;
            }
            if ($row['component_key'] === 'completion') $manifests[] = $payload['rows'][0];
        }
        $this->assertCount(5, $calendarRows, 'Closed dates and dependencies cannot disappear from the consumed revision set.');
        foreach ($calendarRows as $date => $row) {
            $this->assertSame(RunInputCaptureRepository::canonicalJson((array) DB::table('md_market_calendar_revisions')->where('cal_date', $date)->first()), RunInputCaptureRepository::canonicalJson($row));
        }
        $this->assertCount(1, $manifests);
        $this->assertGreaterThan(1, count($manifests[0]['expected_slots']));
        $this->assertSame($manifests[0]['expected_slots'], array_column($manifests[0]['actual_slots'], 'slot_hash'));
        $this->assertSame($value, ProducerInputScope::during($run, 'INDICATORS', 'calendar-fixture/v1', $read));
        $this->assertSame($before, $captures->forRun($run->run_id), 'Identical retry must preserve every capture byte.');
        $this->assertSame('unscoped', ProducerInputScope::cacheIdentity());
    }

    public function test_one_changed_dependency_conflicts_even_if_consumer_catches_it_and_writes(): void
    {
        $run = $this->runContext(); $calendar = new MarketCalendarRepository();
        ProducerInputScope::during($run, 'INDICATORS', 'range/v1', function () use ($calendar) {
            return $calendar->tradingDatesBetween('2026-03-19', '2026-03-24');
        });
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-20')->update(['source_version' => 'changed-one-dependency']);
        $before = (new RunInputCaptureRepository())->forRun($run->run_id);
        try {
            ProducerInputScope::during($run, 'INDICATORS', 'range/v1', function () use ($calendar) {
                try { $calendar->tradingDatesBetween('2026-03-19', '2026-03-24'); } catch (RuntimeException $ignored) {}
                DB::table('eod_reason_codes')->where('code', 'C1_TEST_REASON')->update(['severity' => 'ERROR']);
            });
            $this->fail('Swallowed capture conflict cannot commit dependent output.');
        } catch (RuntimeException $e) { $this->assertStringContainsString('INPUT_CAPTURE_CONFLICT', $e->getMessage()); }
        $this->assertSame('INFO', DB::table('eod_reason_codes')->where('code', 'C1_TEST_REASON')->value('severity'));
        $this->assertSame($before, (new RunInputCaptureRepository())->forRun($run->run_id));
        $this->assertSame('unscoped', ProducerInputScope::cacheIdentity());
    }

    public function test_registry_content_and_build_are_verifiable_and_one_changed_reason_conflicts(): void
    {
        $run = $this->runContext(); $captures = new RunInputCaptureRepository();
        $row = $captures->captureRegistryVersions($run); $payload = $captures->verify($row)['rows'][0];
        $this->assertSame([], $payload['missing_paths']);
        $this->assertCount(1, $payload['reason_entries']);
        $this->assertCount(6, $payload['implementation_identities']);
        $build = $payload['executable_build'];
        $this->assertGreaterThan(1000, count($build['files']));
        $bytes = file_get_contents(base_path($build['artifact_path']));
        $this->assertSame($build['artifact_hash'], hash('sha256', $bytes));
        $this->assertSame($build['content_hash'], hash('sha256', gzdecode($bytes)));
        $archive = json_decode(gzdecode($bytes), true);
        $path = 'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php';
        $this->assertSame(file_get_contents(base_path($path)), base64_decode($archive['files_base64'][$path], true));
        $this->assertSame('sha256:'.$build['content_hash'], $build['build_id']);
        DB::table('eod_reason_codes')->where('code', 'C1_TEST_REASON')->update(['description' => 'Changed actual meaning']);
        $this->expectExceptionMessage('INPUT_CAPTURE_CONFLICT'); $captures->captureRegistryVersions($run);
    }

    public function test_historical_reason_registry_is_not_reconstructed_from_current_entries(): void
    {
        $run = $this->runContext(); $run->request_mode = 'replay_verify';
        $captures = new RunInputCaptureRepository(); $payload = $captures->verify($captures->captureRegistryVersions($run))['rows'][0];
        $this->assertNull($payload['reason_entries']);
        $this->assertNull($payload['reason_registry_hash']);
        $this->assertSame(['registry_versions.reason_registry.authoritative_known_at_cutoff'], $payload['missing_paths']);
    }

    public function test_omitting_one_declared_calendar_read_cannot_mint_completion(): void
    {
        $run = $this->runContext();
        $this->expectExceptionMessage('INPUT_CAPTURE_CALENDAR_COMPLETION_MISMATCH');
        ProducerInputScope::during($run, 'INDICATORS', 'missing-slot/v1', function () use ($run) {
            // Lose one real persisted input through a consumer savepoint rollback. The scope's
            // declared read survives in memory and must not be allowed to mint completion.
            DB::statement('SAVEPOINT c1_missing_calendar_input');
            $dates = (new MarketCalendarRepository())->tradingDatesBetween('2026-03-19', '2026-03-24');
            $query = DB::table('md_run_input_captures')->where('run_id', $run->run_id)->where('component_key', 'calendar_session');
            $this->assertSame(1, $query->count());
            DB::statement('ROLLBACK TO SAVEPOINT c1_missing_calendar_input');
            DB::statement('RELEASE SAVEPOINT c1_missing_calendar_input');
            $this->assertSame(0, $query->count());
            return $dates;
        });
    }

    public function test_expectation_cache_cannot_hide_reads_in_a_second_producer_stage(): void
    {
        $run = $this->runContext(); $decision = new \App\Application\MarketData\Services\ExpectedBarDecisionService();
        $method = new ReflectionMethod($decision, 'calendarContext'); $method->setAccessible(true);
        foreach (['COVERAGE', 'ELIGIBILITY'] as $stage) {
            ProducerInputScope::during($run, $stage, 'cache-fixture/v1', function () use ($method, $decision, $run) {
                return $method->invoke($decision, '2026-03-24', (string) $run->knowledge_cutoff_at);
            });
            $this->assertSame(3, DB::table('md_run_input_captures')->where('run_id', $run->run_id)
                ->where('stage_code', $stage)->where('component_key', 'calendar_session')->count());
        }
    }

    public function test_completion_names_one_uncaptured_history_date_and_missing_full_revision_contracts(): void
    {
        $run = $this->runContext(); $repository = new RunInputCaptureRepository();
        ProducerInputScope::during($run, 'INDICATORS', 'range/v1', static function () {
            return (new MarketCalendarRepository())->tradingDatesBetween('2026-03-19', '2026-03-24');
        });
        $repository->captureForProducer($run, 'INDICATORS', 'raw_history', 'indicator-consumed-atr-series/v1', [
            ['trade_date' => '2026-03-18', 'ticker_id' => 1, 'close' => '100.0000'],
        ]);
        $manifest = (new \App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest())->inspect($run, $repository);
        $this->assertSame('BLOCKED', $manifest['status']);
        $this->assertContains('calendar_session.required_date.2026-03-18', $manifest['missing_paths']);
        $this->assertContains('source_observations.no_producer_ingress_population', $manifest['missing_paths']);
        $this->assertCount(10, $manifest['required_operations']);
        $this->assertGreaterThan(0, count($manifest['actual_slots']));
    }

    public function test_failed_acquisition_retains_its_observed_input_audit_without_holding_a_transaction(): void
    {
        $run = $this->runContext(); $repository = new RunInputCaptureRepository();
        try {
            $repository->executeProducer($run, 'ACQUISITION', 'failed-acquisition/v1', function () {
                $this->assertSame(0, DB::connection()->transactionLevel(), 'Provider I/O must not hold a database transaction.');
                (new MarketCalendarRepository())->sessionContext('2026-03-24');
                throw new RuntimeException('FIXTURE_PROVIDER_FAILURE');
            }, false);
            $this->fail('Failed acquisition must remain failed.');
        } catch (RuntimeException $e) { $this->assertSame('FIXTURE_PROVIDER_FAILURE', $e->getMessage()); }
        $this->assertSame(3, DB::table('md_run_input_captures')->where('run_id', $run->run_id)
            ->where('stage_code', 'ACQUISITION')->where('component_key', 'calendar_session')->count());
        $this->assertSame(0, DB::table('md_run_input_captures')->where('run_id', $run->run_id)
            ->where('stage_code', 'ACQUISITION')->where('component_key', 'completion')->count());
        $this->assertSame('unscoped', ProducerInputScope::cacheIdentity());
    }
}
