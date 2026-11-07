<?php

use App\Application\MarketData\Services\MarketDataIncidentResponseService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19IncidentResponseMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;
    use \Tests\Support\RetainsB19ObservedEvidence;
    private $activation;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataMariaDb(); $this->activation = config('market_data.scope.operational_start_date'); }
    protected function tearDown(): void { config(['market_data.scope.operational_start_date' => $this->activation]); Carbon::setTestNow(); $this->tearDownMarketDataMariaDb(); parent::tearDown(); }

    private function observed(EodRun $run, string $reason): void
    {
        (new EodRunRepository())->recordObservation($run, 'detector', 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR',
            'Controlled observed condition; independent fault injection, no classifier output used as input.',
            DB::table('eod_reason_codes')->where('code', $reason)->exists() ? $reason : null,
            ['observed_condition' => $reason, 'source_observation_ids' => [(int) DB::table('eod_bars')->where('trade_date', $run->trade_date_requested)->value('source_observation_id')]]);
    }

    public function test_observed_integrity_security_and_semantic_response_enforces_gateway_retry_and_export_without_mutating_history(): void
    {
        $sourceDate = '2026-03-23';
        $world = R0025SyntheticV2World::build(['run_clock' => '2026-03-23 18:00:00', 'http_fetcher' => function () use (&$sourceDate) {
            $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
            $body['chart']['result'][0]['timestamp'] = [(new DateTimeImmutable($sourceDate.' 09:00:00', new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
            return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($body)];
        }]);
        $prior = (int) $world['publication_id'];
        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid' => hash('sha256', 'e049-incident-2026-03-24'), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
            'cal_date' => '2026-03-24', 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
            'session_state' => 'COMPLETED', 'session_open_at' => '2026-03-24 09:00:00', 'session_close_at' => '2026-03-24 16:00:00',
            'completed_at' => '2026-03-24 16:00:00', 'recorded_at' => '2026-03-24 17:00:00',
            'source_ref' => 'independent-e049-incident-next-session', 'source_version' => 'e049-test-only-v1', 'provenance_tier' => 'VERIFIED',
            'reconciled_at' => '2026-03-24 17:00:00', 'reconciliation_source_ref' => 'independent-e049-incident-next-session',
        ]);
        $sourceDate = '2026-03-24'; Carbon::setTestNow('2026-03-24 18:00:00');
        $run = $world['pipeline']->runDaily($sourceDate, 'api');
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame('market-data-semantic-hash/v2', $run->artifact_hash_profile);
        $service = new MarketDataIncidentResponseService();
        $history = DB::table('eod_bars_history')->orderBy('ticker_id')->get()->toJson();
        $canonical = DB::table('eod_bars')->orderBy('ticker_id')->get()->toJson();
        $pointer = DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson();
        $matrix = [
            'DATASET_HASH_MISMATCH' => ['integrity', 'CRITICAL', true, false, true],
            'RUN_SOURCE_AUTH_ERROR' => ['security/provenance', 'CRITICAL', true, true, true],
            'MIXED_PRICE_BASIS' => ['semantic', 'HIGH', true, false, true],
            'HISTORY_MUTATION_ATTEMPT' => ['integrity', 'CRITICAL', true, false, true],
            'CONFIG_HASH_MISMATCH' => ['integrity', 'CRITICAL', true, false, true],
            'SEAL_HASH_MISMATCH' => ['integrity', 'CRITICAL', true, false, true],
            'MIXED_PUBLICATION' => ['integrity', 'CRITICAL', true, false, true],
            'POINTER_ORPHAN_DETECTED' => ['integrity', 'CRITICAL', true, false, true],
            'SECRET_LEAKAGE' => ['security/provenance', 'CRITICAL', true, true, true],
            'UNAUTHENTICATED_PAYLOAD' => ['security/provenance', 'CRITICAL', true, true, true],
            'SOURCE_OBSERVATION_PROVENANCE_MISSING' => ['security/provenance', 'CRITICAL', true, true, true],
            'WRONG_IDENTITY' => ['semantic', 'HIGH', true, false, true],
            'WRONG_DATE' => ['semantic', 'HIGH', true, false, true],
            'WRONG_STATUS' => ['semantic', 'HIGH', true, false, true],
            'UNVERIFIED_FACTOR' => ['semantic', 'HIGH', true, false, true],
            'ATR_RESEED' => ['semantic', 'HIGH', true, false, true],
        ];
        foreach ($matrix as $reason => [$class, $severity, $readBlocked, $exportBlocked, $retryBlocked]) {
            DB::beginTransaction();
            try {
                $this->assertNotNull((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($run->trade_date_requested));
                $this->observed($run, $reason);
                $incident = $service->observeRun($run->run_id);
                $this->assertSame([$class], $incident['classes']);
                $this->assertSame([$reason], $incident['reasons']);
                $this->assertSame([(int) DB::table('eod_bars')->where('trade_date', $run->trade_date_requested)->value('source_observation_id')], $incident['observations'][0]['source_observation_ids']);
                $this->assertSame([], $incident['observations'][0]['unverified_source_observation_ids']);
                $this->assertTrue($incident['config_identity_verified']);
                $this->assertSame($severity, $incident['severity_floor']);
                $this->assertSame($readBlocked, $incident['protection']['block_requested_read']);
                $this->assertSame($exportBlocked, $incident['protection']['block_affected_evidence']);
                $this->assertSame($retryBlocked, $incident['protection']['block_ordinary_retry']);
                $this->assertSame('UNRESOLVED_F018', $incident['severity_escalation']);
                $this->assertFalse($incident['protection']['in_place_repair_authorized']);
                $this->assertFalse($incident['protection']['source_change_authorized']);
                try { (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($run->trade_date_requested); $this->fail('Incident must block the actual gateway: '.$reason); }
                catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); }
                try { $world['pipeline']->completeIngest(new \App\Application\MarketData\DTOs\MarketDataStageInput($run->trade_date_requested, 'api', $run->run_id, 'INGEST_BARS')); $this->fail('Unsafe ordinary retry'); }
                catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); }
                $this->assertSame($prior, (int) (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate('2026-03-23')->publication_id,
                    'Containment must preserve the independently verified prior date without relabeling it as the affected date.');
                // Even a different run identity cannot bypass date-scoped pointer
                // ambiguity. Other conditions remain scoped to implicated artifacts.
                if (in_array($reason, ['POINTER_ORPHAN_DETECTED', 'MIXED_PUBLICATION'], true)) {
                    try { $service->assertActionAllowed($run->trade_date_requested, 'consumer_read', $world['run_id']); $this->fail('Ambiguity bypassed through a different target run.'); }
                    catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); }
                }
                $fallbackRefused = false;
                try { (new EodPublicationRepository())->findLatestReadablePublicationBefore($run->trade_date_requested); }
                catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); $fallbackRefused = true; }
                $this->assertTrue($fallbackRefused, 'An affected requested date cannot evade containment by falling back to a prior date: '.$reason);
                if ($exportBlocked) {
                    try { app(MarketDataEvidenceExportService::class)->exportRunEvidence($run->run_id); $this->fail('Affected security evidence exported.'); }
                    catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); }
                } else $service->assertActionAllowed($run->trade_date_requested, 'evidence_export');
                $this->assertSame($incident, $service->observeRun($run->run_id), 'Duplicate observation is idempotent.');
                $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->count());
                $this->assertSame($history, DB::table('eod_bars_history')->orderBy('ticker_id')->get()->toJson());
                $this->assertSame($canonical, DB::table('eod_bars')->orderBy('ticker_id')->get()->toJson());
                $this->assertSame($pointer, DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toJson());
                $this->retainObservedWorld('incident_'.$reason, [$run->run_id], $incident);
            } finally { DB::rollBack(); }
        }
    }

    public function test_phase_boundary_all_observed_classes_continue_append_only_without_improvised_closure_or_health_claim(): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1']);
        $run = EodRun::findOrFail($world['run_id']);
        $service = new MarketDataIncidentResponseService();
        $this->observed($run, 'RUN_SOURCE_TIMEOUT');
        $development = $service->observeRun($run->run_id);
        $this->assertSame([], $development['classes']);
        $this->assertSame('NO_CLASSIFIED_OBSERVED_CONDITION', $development['state']);
        $this->assertNull($development['severity_floor']);
        $this->assertStringContainsString('no completeness, health', $development['claim_limit']);
        // A conditional marker exercises response logic, not approved live activation.
        config(['market_data.scope.operational_start_date' => $run->trade_date_requested]);
        $operational = $service->observeRun($run->run_id);
        $this->assertSame(['source/schema'], $operational['classes']);
        $this->assertSame('MEDIUM', $operational['severity_floor']);
        $this->assertFalse($operational['protection']['block_ordinary_retry']);
        $this->observed($run, 'RUN_LOCK_CONFLICT');
        $this->observed($run, 'RUN_REPLAY_BLOCKED');
        $continuing = $service->observeRun($run->run_id);
        $this->assertSame(['proof/monitoring', 'scheduler/lock', 'source/schema'], $continuing['classes']);
        $this->assertSame(['RUN_LOCK_CONFLICT', 'RUN_REPLAY_BLOCKED', 'RUN_SOURCE_TIMEOUT'], $continuing['reasons']);
        $this->assertCount(3, $continuing['observations']);
        $this->assertSame('HIGH', $continuing['severity_floor']);
        $this->assertTrue($continuing['protection']['block_release_claim']);
        $this->observed($run, 'DATASET_HASH_MISMATCH'); $this->observed($run, 'RUN_SOURCE_AUTH_ERROR');
        $critical = $service->observeRun($run->run_id);
        $this->assertSame(['integrity', 'proof/monitoring', 'scheduler/lock', 'security/provenance', 'source/schema'], $critical['classes']);
        $this->assertCount(5, $critical['reasons']);
        $this->assertSame('CRITICAL', $critical['severity_floor']);
        $this->assertTrue($critical['protection']['block_requested_read']);
        $this->assertTrue($critical['protection']['block_affected_evidence']);
        $this->assertFalse($critical['operational_activation_proven']);
        $first = DB::table('eod_run_events')->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->orderBy('event_id')->first();
        $this->assertSame($development, json_decode($first->event_payload_json, true));
        try { $service->requestClosure(); $this->fail('Closure authority invented.'); }
        catch (RuntimeException $error) { $this->assertSame('INCIDENT_CLOSURE_AUTHORITY_UNRESOLVED_F018', $error->getMessage()); }
        $this->assertSame('OPEN_OBSERVED_CONDITION', $service->observeRun($run->run_id)['state']);
        $this->retainObservedWorld('incident_phase_and_continuation', [$run->run_id], ['development' => $development, 'continuing' => $continuing, 'critical' => $critical]);
    }

    public function test_actual_failed_authentication_stage_produces_persisted_critical_containment(): void
    {
        $status = 401;
        $pipeline = null;
        try {
            R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1',
                'http_fetcher' => function () use (&$status) { return ['status' => $status, 'headers' => ['Content-Type: application/json'],
                    'body' => $status === 401 ? 'unauthorized' : file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json')]; },
                'run_workflow' => function ($actual) use (&$pipeline) { $pipeline = $actual; return $actual->runDaily('2026-03-23', 'api'); }]);
            $this->fail('Authentication failure unexpectedly published.');
        } catch (Throwable $error) {
            $this->assertNotInstanceOf(\PHPUnit\Framework\AssertionFailedError::class, $error);
        }
        $run = DB::table('eod_runs')->orderByDesc('run_id')->first();
        $this->assertSame('FAILED', $run->terminal_status);
        $incident = DB::table('eod_run_events')->where('run_id', $run->run_id)->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->orderByDesc('event_id')->first();
        $this->assertNotNull($incident, 'The actual failed stage must invoke the incident producer.');
        $payload = json_decode($incident->event_payload_json, true);
        $this->assertContains('security/provenance', $payload['classes']);
        $this->assertSame('CRITICAL', $payload['severity_floor']);
        $this->assertTrue($payload['protection']['block_affected_evidence']);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->exists());
        $failedObservations = DB::table('md_source_observations')->where('run_id', $run->run_id)->orderBy('source_observation_id')->get()->toJson();
        $status = 200; Carbon::setTestNow('2026-03-25 10:30:00');
        $new = $pipeline->runDaily('2026-03-23', 'api');
        $this->assertNotSame((int) $run->run_id, (int) $new->run_id);
        $this->assertSame('READABLE', $new->publishability_state, 'A fresh authenticated acquisition must be able to produce new immutable artifacts after containment.');
        $audit = DB::table('eod_run_events')->where('run_id', $new->run_id)->where('event_type', 'INCIDENT_FALLBACK_REFUSED')->first();
        $this->assertNotNull($audit, 'Authorized new acquisition must retain the refusal, not bypass it.');
        $this->assertSame('2026-03-23', json_decode($audit->event_payload_json, true)['requested_date']);
        $this->assertNull(json_decode($audit->event_payload_json, true)['fallback_publication_id']);
        $this->assertSame($failedObservations, DB::table('md_source_observations')->where('run_id', $run->run_id)->orderBy('source_observation_id')->get()->toJson());
        $this->assertSame($payload, json_decode(DB::table('eod_run_events')->where('event_id', $incident->event_id)->value('event_payload_json'), true));
        $newObservation = (int) DB::table('eod_bars')->where('trade_date', '2026-03-23')->value('source_observation_id');
        $this->assertFalse(DB::table('md_source_observations')->where('run_id', $run->run_id)->where('source_observation_id', $newObservation)->exists());
        $this->assertSame(hash('sha256', file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json')),
            DB::table('md_source_observations')->where('source_observation_id', $newObservation)->value('payload_hash'));
        try { app(MarketDataEvidenceExportService::class)->exportRunEvidence($run->run_id); $this->fail('Old affected security evidence must remain contained after a new acquisition succeeds.'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); }
        $retained = getenv('B19_E049_STATE_DIR');
        $directory = $retained ? $retained.'/new_authenticated_evidence' : sys_get_temp_dir().'/e049-auth-evidence-'.bin2hex(random_bytes(6));
        try {
            app(MarketDataEvidenceExportService::class)->exportRunEvidence($new->run_id, $directory);
            $pack = json_decode(file_get_contents($directory.'/evidence_pack.json'), true);
            $this->assertSame('SUCCESS', $pack['run_summary']['terminal_status']);
            $this->assertSame((int) $new->run_id, (int) $pack['run_summary']['run_id']);
        } finally {
            if (! $retained && is_dir($directory)) { foreach (glob($directory.'/*') as $file) if (is_file($file)) unlink($file); rmdir($directory); }
        }
        $this->retainObservedWorld('security_containment_and_new_authenticated_acquisition', [(int) $run->run_id, (int) $new->run_id], $payload);
    }

    public function test_approved_semantic_correction_creates_new_immutable_revision_while_affected_old_publication_stays_protected(): void
    {
        $body = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$body) {
            return ['status' => 200, 'headers' => ['Content-Type: application/json'], 'body' => $body];
        }]);
        $run = EodRun::findOrFail($world['run_id']);
        $history = DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson();
        $this->observed($run, 'MIXED_PRICE_BASIS');
        (new MarketDataIncidentResponseService())->observeRun($run->run_id);
        $repo = new \App\Infrastructure\Persistence\MarketData\EodCorrectionRepository();
        $correction = $repo->createRequest($run->trade_date_requested, 'DATASET_HASH_CREATED',
            'Independent observed semantic correction: source close 105 becomes 106.', 'e049-requester', $world['publication_id'], $run->run_id);
        try { $world['pipeline']->runDaily($run->trade_date_requested, 'api', $correction->correction_id); $this->fail('Unapproved correction allowed.'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('APPROVED', $error->getMessage()); }
        $repo->approve($correction->correction_id, 'e049-approver');
        $changed = json_decode($body, true); $changed['chart']['result'][0]['indicators']['quote'][0]['close'][0] = 106; $body = json_encode($changed);
        Carbon::setTestNow('2026-03-25 10:30:00');
        $replacement = $world['pipeline']->runDaily($run->trade_date_requested, 'api', $correction->correction_id);
        $this->assertSame('READABLE', $replacement->publishability_state);
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $replacement->run_id)->where('event_type', 'INCIDENT_FALLBACK_REFUSED')->count(), 'Immutable correction may proceed without using a refused prior-date fallback.');
        $this->assertNotSame((int) $world['publication_id'], (int) $replacement->publication_id);
        $this->assertSame($history, DB::table('eod_bars_history')->where('publication_id', $world['publication_id'])->get()->toJson());
        $this->assertEquals(106, DB::table('eod_bars_history')->where('publication_id', $replacement->publication_id)->value('close'));
        $this->assertSame((int) $replacement->publication_id,
            (int) (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($run->trade_date_requested)->publication_id);
        $this->assertSame('OPEN_OBSERVED_CONDITION', (new MarketDataIncidentResponseService())->observeRun($run->run_id)['state'], 'A new immutable correction does not invent incident closure.');
        $this->retainObservedWorld('semantic_correction', [$run->run_id, $replacement->run_id]);
    }

    public function test_native_seal_hash_failure_records_exact_condition_and_contains_retry_without_a_declared_signal(): void
    {
        $failure = null;
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1',
            'run_workflow' => function ($pipeline) use (&$failure) {
                $run = $pipeline->completeIngest(new \App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-23', 'api', null, 'INGEST_BARS', null, false, null, 'full_publish'));
                foreach (['COMPUTE_INDICATORS' => 'completeIndicators', 'BUILD_ELIGIBILITY' => 'completeEligibility', 'HASH' => 'completeHash'] as $stage => $method) {
                    $run = $pipeline->{$method}(new \App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-23', 'api', $run->run_id, $stage));
                }
                // Controlled corrupt unsealed candidate hash. The real seal recomputes
                // immutable rows; no incident/classifier output is injected as an input.
                DB::table('eod_runs')->where('run_id', $run->run_id)->update(['bars_batch_hash' => str_repeat('f', 64)]);
                try { $pipeline->completeSeal(new \App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-23', 'api', $run->run_id, 'SEAL')); }
                catch (RuntimeException $error) { $failure = $error->getMessage(); }
                return EodRun::findOrFail($run->run_id);
            }]);
        $this->assertStringContainsString('DATASET_HASH_MISMATCH', (string) $failure);
        $failed = DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'STAGE_FAILED')->orderByDesc('event_id')->first();
        $this->assertNotNull($failed);
        $event = DB::table('eod_run_events')->where('run_id', $world['run_id'])->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->orderByDesc('event_id')->first();
        $this->assertNotNull($event);
        $response = json_decode($event->event_payload_json, true);
        $this->assertSame(['integrity'], $response['classes'], 'A native seal hash failure must produce critical integrity containment, not an unclassified wrapper.');
        $this->assertContains('DATASET_HASH_MISMATCH', $response['reasons']);
        $this->assertSame('CRITICAL', $response['severity_floor']);
        $this->assertSame((int) $failed->event_id, $response['observations'][0]['event_id']);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->exists());
        $this->assertSame(0, DB::table('eod_publications')->where('run_id', $world['run_id'])->where('seal_state', 'SEALED')->count());
        $guarded = false;
        try { $world['pipeline']->completeIngest(new \App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-23', 'api', $world['run_id'], 'INGEST_BARS')); }
        catch (RuntimeException $error) { $this->assertStringContainsString('INCIDENT_RESPONSE_BLOCKED', $error->getMessage()); $guarded = true; }
        $this->assertTrue($guarded, 'Integrity retry must use verified recovery, not an ordinary rerun.');
        $this->retainObservedWorld('native_seal_hash_failure', [$world['run_id']], $response);
    }

    public function test_incident_scope_and_context_refuse_misattribution_without_suppressing_critical_containment(): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1']);
        $run = EodRun::findOrFail($world['run_id']); $service = new MarketDataIncidentResponseService();
        DB::beginTransaction();
        try {
            $wrong = clone $run; $wrong->trade_date_requested = '2026-03-24';
            // Deliberate persisted corruption, not an authorized wrong-date writer fixture.
            $this->observed($run, 'DATASET_HASH_MISMATCH');
            $corrupt = (array) DB::table('eod_run_events')->where('run_id', $run->run_id)
                ->where('event_type', 'OBSERVED_CONDITION')->orderByDesc('event_id')->first();
            unset($corrupt['event_id']); $corrupt['trade_date_requested'] = $wrong->trade_date_requested;
            DB::table('eod_run_events')->insert($corrupt);
            $refused = false;
            try { $service->observeRun($run->run_id); }
            catch (RuntimeException $error) { $this->assertSame('INCIDENT_OBSERVATION_SCOPE_MISMATCH', $error->getMessage()); $refused = true; }
            $this->assertTrue($refused, 'An event from a different requested date cannot be attributed to this run.');
            $this->assertSame(0, DB::table('eod_run_events')->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->count());
        } finally { DB::rollBack(); }
        DB::beginTransaction();
        try {
            DB::table('eod_runs')->where('run_id', $run->run_id)->update(['config_hash' => str_repeat('0', 64)]);
            $incomplete = $service->observeRun($run->run_id);
            $this->assertFalse($incomplete['config_identity_verified'] ?? null, 'An unverified context is not a clean incident proof.');
            $this->assertSame('EVIDENCE_INCOMPLETE_CONFIGURATION', $incomplete['state']);
            $this->assertTrue($incomplete['protection']['block_release_claim']);
            $this->observed($run, 'DATASET_HASH_MISMATCH');
            $critical = $service->observeRun($run->run_id);
            $this->assertContains('integrity', $critical['classes']);
            $this->assertSame('CRITICAL', $critical['severity_floor']);
            $this->assertTrue($critical['protection']['block_requested_read'], 'Unverified configuration must not suppress observed critical containment.');
            $this->assertFalse($critical['config_identity_verified']);
        } finally { DB::rollBack(); }
        $boundId = (int) DB::table('eod_bars')->where('trade_date', $run->trade_date_requested)->value('source_observation_id');
        $unbound = (array) DB::table('md_source_observations')->where('source_observation_id', $boundId)->first();
        unset($unbound['source_observation_id']); $unbound['observation_uid'] = 'e049-unadmitted-copy-'.bin2hex(random_bytes(8)); $unbound['run_id'] = null;
        $unboundId = (int) DB::table('md_source_observations')->insertGetId($unbound, 'source_observation_id');
        foreach (['nonexistent' => 999999999999, 'fractional alias' => $boundId.'.5', 'unrelated registered outcome' => $unboundId] as $case => $declared) {
            DB::beginTransaction();
            try {
                (new EodRunRepository())->recordObservation($run, 'detector', 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR',
                    'Adversarial declared observation association: '.$case, null,
                    ['observed_condition' => 'RUN_SOURCE_TIMEOUT', 'source_observation_ids' => [$declared]]);
                $provenance = $service->observeRun($run->run_id);
                $this->assertContains('security/provenance', $provenance['classes'], 'Unverified observation attribution must be contained: '.$case);
                $this->assertContains('SOURCE_OBSERVATION_PROVENANCE_MISSING', $provenance['reasons']);
                $this->assertSame('CRITICAL', $provenance['severity_floor']);
                $this->assertTrue($provenance['protection']['block_affected_evidence']);
                $this->assertSame([], $provenance['observations'][0]['source_observation_ids']);
                $this->assertSame([$declared], $provenance['observations'][0]['unverified_source_observation_ids']);
                $this->retainObservedWorld('incident_unverified_observation_'.str_replace(' ', '_', $case), [$run->run_id], $provenance);
            } finally { DB::rollBack(); }
        }
    }

    public function test_incident_record_binds_source_publication_and_observed_pointer_and_rejects_rehashed_cross_scope(): void
    {
        $world = R0025SyntheticV2World::build();
        $run = EodRun::findOrFail($world['run_id']); $service = new MarketDataIncidentResponseService();
        $this->observed($run, 'DATASET_HASH_MISMATCH'); $incident = $service->observeRun($run->run_id);
        $this->assertSame('api', $incident['source_mode'] ?? null, 'The response must bind the actual owning source, not infer it from a reason label.');
        $this->assertSame((int) $world['publication_id'], $incident['publication_id']);
        $this->assertSame((int) $run->publication_version, $incident['publication_version']);
        $this->assertSame($run->trade_date_requested, $incident['observed_pointer']['trade_date']);
        $this->assertSame((int) $world['publication_id'], $incident['observed_pointer']['publication_id']);
        $this->assertSame((int) $run->run_id, $incident['observed_pointer']['run_id']);
        $this->assertTrue($incident['observed_pointer_matches_artifact']);
        $before = DB::table('eod_current_publication_pointer')->get()->toJson();
        foreach (['source', 'publication', 'pointer_scope', 'pointer_substitution', 'missing_context'] as $case) {
            DB::beginTransaction();
            try {
                $bad = $incident; unset($bad['content_sha256']);
                if ($case === 'source') $bad['source_mode'] = 'manual_file';
                if ($case === 'publication') $bad['publication_id']++;
                if ($case === 'pointer_scope') $bad['observed_pointer']['trade_date'] = '2026-03-24';
                if ($case === 'pointer_substitution') $bad['observed_pointer']['publication_id']++;
                if ($case === 'missing_context') unset($bad['source_mode']);
                // Attack input recomputes the checksum deliberately. The expected
                // refusal is independent identity/scope binding, not a hash oracle.
                $bad['content_sha256'] = hash('sha256', \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::canonicalJson($bad));
                // Adversarial persisted row, not an authorized post-terminal producer.
                DB::table('eod_run_events')->insert([
                    'run_id'=>$run->run_id, 'trade_date_requested'=>$run->trade_date_requested,
                    'stage'=>'FINALIZE', 'event_type'=>'INCIDENT_RESPONSE_RECORDED', 'severity'=>'ERROR',
                    'event_time'=>Carbon::now()->toDateTimeString(), 'created_at'=>Carbon::now()->toDateTimeString(),
                    'message'=>'Controlled rehashed identity substitution.', 'event_payload_json'=>json_encode($bad),
                ]);
                $refused = false;
                try { $service->assertActionAllowed($run->trade_date_requested, 'evidence_export'); }
                catch (RuntimeException $error) { $this->assertSame('INCIDENT_EVIDENCE_IDENTITY_INVALID', $error->getMessage()); $refused = true; }
                $this->assertTrue($refused, 'Rehashed incident context must not substitute identities: '.$case);
                $this->assertSame($before, DB::table('eod_current_publication_pointer')->get()->toJson());
            } finally { DB::rollBack(); }
        }
        $this->retainObservedWorld('incident_bound_identity_and_rehashed_refusals', [$run->run_id], $incident);
    }

    public function test_unknown_observed_condition_is_incomplete_not_a_clean_success_and_supporting_text_cannot_invent_incidents(): void
    {
        $world = R0025SyntheticV2World::build(['profile' => 'market-data-row-hash/v1']);
        $run = EodRun::findOrFail($world['run_id']);
        $repo = new EodRunRepository();
        $repo->recordObservation($run, 'detector', 'FINALIZE', 'DIAGNOSTIC', 'WARN',
            'Supporting text says DATASET_HASH_MISMATCH; it is not an observed-condition detector.', 'DATASET_HASH_MISMATCH');
        $service = new MarketDataIncidentResponseService();
        $clean = $service->observeRun($run->run_id);
        $this->assertSame([], $clean['classes']);
        $this->assertSame('NO_CLASSIFIED_OBSERVED_CONDITION', $clean['state']);
        $this->assertSame('Observed conditions handled under the declared matrix only; no completeness, health, consumer consequence, solved-cause, incident closure, activation or release-readiness claim.', $clean['claim_limit'], 'An empty observed-condition population cannot become a no-incident or health claim.');
        $cleanCommand = new \App\Console\Commands\MarketData\ObserveIncidentCommand(); $cleanCommand->setLaravel($this->app);
        $cleanTester = new \Symfony\Component\Console\Tester\CommandTester($cleanCommand);
        $this->assertSame(0, $cleanTester->execute(['--run_id' => (string) $run->run_id]));
        $cleanOutput = json_decode(trim($cleanTester->getDisplay()), true);
        $this->assertSame('NO_CLASSIFIED_OBSERVED_CONDITION', $cleanOutput['state']);
        $this->assertSame($clean['claim_limit'], $cleanOutput['claim_limit']);
        $this->assertFalse($cleanOutput['operational_activation_proven']);
        $this->observed($run, 'UNKNOWN_OBSERVED_SIGNAL');
        $command = new \App\Console\Commands\MarketData\ObserveIncidentCommand(); $command->setLaravel($this->app);
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $this->assertSame(2, $tester->execute(['--run_id' => (string) $run->run_id]), 'Unclassified observed input must not yield a successful decision command.');
        $report = json_decode(trim($tester->getDisplay()), true);
        $this->assertSame('EVIDENCE_INCOMPLETE_UNCLASSIFIED_OBSERVATION', $report['state']);
        $this->assertSame([], $report['classes']);
        $this->assertNull($report['severity_floor'], 'Unknown input does not authorize an invented severity.');
        $this->assertCount(1, $report['unclassified_observations']);
        $this->assertSame(['UNKNOWN_OBSERVED_SIGNAL'], $report['unclassified_observations'][0]['reason_identities']);
        $this->assertTrue($report['protection']['block_release_claim']);
        $this->assertSame($clean['claim_limit'], $report['claim_limit'], 'Contradictory input must retain the same claim boundary rather than claiming incident absence.');
        $this->retainObservedWorld('unknown_observation_and_claim_boundary', [$run->run_id], ['clean' => $clean, 'incomplete' => $report]);
    }

    public function test_two_date_post_switch_refusal_keeps_owner_until_protected_cleanup_finishes(): void
    {
        $sourceDate = '2026-03-23';
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$sourceDate) {
            $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
            $body['chart']['result'][0]['timestamp'] = [(new DateTimeImmutable($sourceDate.' 09:00:00', new DateTimeZone('Asia/Jakarta')))->getTimestamp()];
            return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($body)];
        }]);
        $priorId = (int) $world['publication_id'];
        $priorPointer = DB::table('eod_current_publication_pointer')->where('trade_date', $sourceDate)->first();
        $priorHistory = DB::table('eod_bars_history')->where('publication_id', $priorId)->orderBy('ticker_id')->get()->toJson();
        $this->assertSame($priorId, (int) (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($sourceDate)->publication_id);

        DB::table('md_market_calendar_revisions')->insert([
            'revision_uid' => hash('sha256', 'e049-fenced-recovery-2026-03-24'), 'market_code' => 'IDX', 'market_segment' => 'REGULAR',
            'cal_date' => '2026-03-24', 'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
            'session_state' => 'COMPLETED', 'session_open_at' => '2026-03-24 09:00:00', 'session_close_at' => '2026-03-24 16:00:00',
            'completed_at' => '2026-03-24 16:00:00', 'recorded_at' => '2026-03-24 17:00:00',
            'source_ref' => 'independent-e049-fenced-recovery-calendar', 'source_version' => 'e049-test-only-v1',
            'provenance_tier' => 'VERIFIED', 'reconciled_at' => '2026-03-24 17:00:00',
            'reconciliation_source_ref' => 'independent-e049-fenced-recovery-calendar',
        ]);
        $sourceDate = '2026-03-24';
        Carbon::setTestNow('2026-03-25 10:30:00');
        $fault = new E049PostSwitchReadFaultPublicationRepository();
        $property = new ReflectionProperty($world['pipeline'], 'publications');
        $property->setAccessible(true);
        $property->setValue($world['pipeline'], $fault);

        $failure = null;
        try { $result = $world['pipeline']->runDaily($sourceDate, 'api'); }
        catch (Throwable $error) { $failure = $error->getMessage(); $result = null; }

        $this->assertTrue($fault->injected, 'A real promoted March24 candidate must reach final pointer verification. Failure='.(string) $failure
            .' run='.json_encode(DB::table('eod_runs')->where('trade_date_requested', $sourceDate)->orderByDesc('run_id')->first())
            .' pointer='.json_encode(DB::table('eod_current_publication_pointer')->where('trade_date', $sourceDate)->first()));
        $this->assertNotNull($fault->observed);
        $this->assertNotNull($fault->observed['actual_publication_id'], 'The fault must hide an actually promoted candidate, not fabricate promotion success.');
        $this->assertNull($fault->observed['released_at'], 'The date owner must remain fenced through post-switch verification and protected refusal cleanup.');
        $this->assertSame('FINALIZING', $fault->observed['lifecycle_state'], 'The promoted run must still be eligible for fenced state writes during verification.');
        $this->assertNull($failure, 'Post-switch refusal must complete under its original fence: '.(string) $failure);
        $this->assertSame('HELD', $result->terminal_status);
        $this->assertSame('NOT_READABLE', $result->publishability_state);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', $sourceDate)->exists());
        $this->assertSame($priorPointer->publication_id, DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id'));
        $this->assertSame($priorHistory, DB::table('eod_bars_history')->where('publication_id', $priorId)->orderBy('ticker_id')->get()->toJson());
        $this->assertSame($priorId, (int) (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate('2026-03-23')->publication_id);
        $fence = DB::table('eod_run_owner_fences')->where('trade_date_requested', $sourceDate)->first();
        $this->assertSame((int) $result->run_id, (int) $fence->owner_run_id);
        $this->assertNotNull($fence->released_at);
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $result->run_id)->where('event_type', 'RUN_OWNER_RELEASED')->count());
        $heldCandidate = DB::table('eod_publications')->where('run_id', $result->run_id)->first();
        $this->assertNotNull($heldCandidate, 'The sealed candidate stays attributable after protected refusal.');
        $this->assertSame('SEALED', $heldCandidate->seal_state);
        $this->assertSame(0, (int) $heldCandidate->is_current);
        $heldHistory = DB::table('eod_bars_history')->where('publication_id', $heldCandidate->publication_id)->orderBy('ticker_id')->get()->toJson();
        $this->assertNotSame('[]', $heldHistory);
        try {
            (new EodRunRepository())->updateTelemetry(EodRun::findOrFail($result->run_id), ['final_reason_code' => 'RUN_LOCK_CONFLICT']);
            $this->fail('Released stale owner modified a terminal run.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
        }

        $retry = $world['pipeline']->runDaily($sourceDate, 'api');
        $this->assertNotSame((int) $result->run_id, (int) $retry->run_id);
        $this->assertSame('SUCCESS', $retry->terminal_status);
        $this->assertSame('READABLE', $retry->publishability_state);
        $this->assertSame((int) $retry->publication_id, (int) (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($sourceDate)->publication_id);
        $this->assertSame($heldHistory, DB::table('eod_bars_history')->where('publication_id', $heldCandidate->publication_id)->orderBy('ticker_id')->get()->toJson());
        $this->assertSame($priorHistory, DB::table('eod_bars_history')->where('publication_id', $priorId)->orderBy('ticker_id')->get()->toJson());
        $this->assertSame($priorId, (int) (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate('2026-03-23')->publication_id);
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $retry->run_id)->where('event_type', 'RUN_OWNER_RELEASED')->count());
        $this->retainObservedWorld('two_date_post_switch_fenced_refusal', [$world['run_id'], $result->run_id, $retry->run_id], $fault->observed);
    }

    public function test_completed_pointer_mismatch_reacquires_an_owner_before_cleanup(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-completed-pointer-mismatch-20260325');
        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        $world = R0025SyntheticV2World::build(['http_fetcher' => function () use (&$response) {
            return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => $response];
        }]);
        $date = R0025SyntheticV2World::world()['trade_date'];
        $priorRunId = (int) $world['run_id'];
        $priorPublicationId = (int) $world['publication_id'];
        $priorHistory = DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson();
        $priorObservations = DB::table('md_source_observations')->where('run_id', $priorRunId)
            ->orderBy('source_observation_id')->get()->toJson();
        $this->assertSame('COMPLETED', DB::table('eod_runs')->where('run_id', $priorRunId)->value('lifecycle_state'));
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->value('released_at'));

        // Persist a contradictory pointer identity after legitimate completion.
        DB::table('eod_current_publication_pointer')->where('trade_date', $date)
            ->update(['publication_version' => 999]);
        $this->assertNull((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date));

        $observingPublications = new E049ObservingCompletedRecoveryPublicationRepository();
        $publicationsProperty = new ReflectionProperty($world['pipeline'], 'publications');
        $publicationsProperty->setAccessible(true);
        $publicationsProperty->setValue($world['pipeline'], $observingPublications);

        $recovery = $world['pipeline']->completeFinalize(new \App\Application\MarketData\DTOs\MarketDataStageInput(
            $date, 'api', $priorRunId, 'FINALIZE', null, false, null, 'full_publish'
        ));

        $this->assertSame((int) $recovery->run_id, $observingPublications->restoreObserved['owner_run_id']);
        $this->assertNull($observingPublications->restoreObserved['released_at']);
        $this->assertSame('PENDING', $observingPublications->restoreObserved['lifecycle_state']);
        $publicationsProperty->setValue($world['pipeline'], new EodPublicationRepository());

        $this->assertNotSame($priorRunId, (int) $recovery->run_id, 'A released completed run cannot recover under its stale token.');
        $this->assertSame($priorRunId, (int) $recovery->supersedes_run_id);
        $this->assertSame('HELD', $recovery->terminal_status);
        $this->assertSame('NOT_READABLE', $recovery->publishability_state);
        $this->assertSame('RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED', $recovery->final_reason_code);
        $this->assertSame('COMPLETED', $recovery->lifecycle_state);
        $this->assertSame($priorPublicationId, (int) DB::table('eod_current_publication_pointer')
            ->where('trade_date', $date)->value('publication_id'));
        $this->assertSame($priorPublicationId, (int) (new EodPublicationRepository())
            ->resolveCurrentReadablePublicationForTradeDate($date)->publication_id);
        $this->assertSame(1, DB::table('eod_publications')->where('publication_id', $priorPublicationId)->value('is_current'));
        $this->assertSame('SEALED', DB::table('eod_publications')->where('publication_id', $priorPublicationId)->value('seal_state'));
        $this->assertSame($priorHistory, DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson());
        $this->assertSame($priorObservations, DB::table('md_source_observations')->where('run_id', $priorRunId)
            ->orderBy('source_observation_id')->get()->toJson());
        $prior = DB::table('eod_runs')->where('run_id', $priorRunId)->first();
        $this->assertSame('SUCCESS', $prior->terminal_status);
        $this->assertSame('READABLE', $prior->publishability_state);
        $this->assertSame('COMPLETED', $prior->lifecycle_state);
        $fence = DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first();
        $this->assertSame((int) $recovery->run_id, (int) $fence->owner_run_id);
        $this->assertNotNull($fence->released_at);
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $priorRunId)
            ->where('event_type', 'RUN_OWNER_RELEASED')->count());
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $recovery->run_id)
            ->where('event_type', 'RUN_OWNER_RELEASED')->count());
        $this->assertTrue(DB::table('eod_run_events')->where('run_id', $recovery->run_id)
            ->where('event_type', 'RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID')->exists());
        try {
            (new EodRunRepository())->updateTelemetry(EodRun::findOrFail($priorRunId), ['final_reason_code' => 'RUN_LOCK_CONFLICT']);
            $this->fail('A completed stale owner changed telemetry after authenticated recovery.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
        }
        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest($date, 'DATASET_HASH_CREATED',
            'Approved correction after fenced pointer recovery', 'e049-requester', $priorPublicationId, $priorRunId);
        $corrections->approve($request->correction_id, 'e049-approver');
        $changed = json_decode($response, true);
        $changed['chart']['result'][0]['indicators']['quote'][0]['close'][0] = 106;
        $response = json_encode($changed);
        $retry = $world['pipeline']->runDaily($date, 'api', $request->correction_id);
        $this->assertNotSame((int) $recovery->run_id, (int) $retry->run_id);
        $this->assertSame('SUCCESS', $retry->terminal_status);
        $this->assertSame('READABLE', $retry->publishability_state);
        $this->assertNotSame($priorPublicationId, (int) $retry->publication_id);
        $this->assertSame((int) $retry->publication_id, (int) (new EodPublicationRepository())
            ->resolveCurrentReadablePublicationForTradeDate($date)->publication_id);
        $this->assertSame($priorHistory, DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson());
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $retry->run_id)
            ->where('event_type', 'RUN_OWNER_RELEASED')->count());
        $this->retainObservedWorld('completed_pointer_mismatch_new_owner_recovery', [$priorRunId, $recovery->run_id, $retry->run_id]);
    }

    public function test_completed_pointer_recovery_refuses_a_competing_live_owner_without_cleanup(): void
    {
        $world = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $priorRunId = (int) $world['run_id'];
        $priorPublicationId = (int) $world['publication_id'];
        $competing = (new EodRunRepository())->getOrCreateOwningRun($date, 'manual_file', 'INGEST_BARS', null, 'full_publish');
        DB::table('eod_current_publication_pointer')->where('trade_date', $date)->update(['publication_version' => 999]);
        $before = DB::table('eod_publications')->where('publication_id', $priorPublicationId)->first();

        try {
            $world['pipeline']->completeFinalize(new \App\Application\MarketData\DTOs\MarketDataStageInput(
                $date, 'api', $priorRunId, 'FINALIZE', null, false, null, 'full_publish'
            ));
            $this->fail('Completed-run recovery displaced a live competing date owner.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage());
        }

        $this->assertSame((int) $competing->run_id, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
        $this->assertNull(DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->value('released_at'));
        $this->assertSame(999, (int) DB::table('eod_current_publication_pointer')
            ->where('trade_date', $date)->value('publication_version'));
        $this->assertNull((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date));
        $this->assertEquals($before, DB::table('eod_publications')->where('publication_id', $priorPublicationId)->first());
        $this->assertSame('SUCCESS', DB::table('eod_runs')->where('run_id', $priorRunId)->value('terminal_status'));
        $this->assertSame('PENDING', DB::table('eod_runs')->where('run_id', $competing->run_id)->value('lifecycle_state'));
    }

    public function test_completed_pointer_recovery_clears_unsafe_prior_without_readable_fallback(): void
    {
        config()->set('market_data.governance.environment_profile', 'e049-completed-pointer-unsafe-20260325');
        $world = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $priorRunId = (int) $world['run_id'];
        $priorPublicationId = (int) $world['publication_id'];
        $priorHistory = DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson();

        // Persist two contradictions: the pointer version and the run's coverage proof.
        DB::table('eod_current_publication_pointer')->where('trade_date', $date)
            ->update(['publication_version' => 999]);
        DB::table('eod_runs')->where('run_id', $priorRunId)->update(['coverage_gate_state' => 'FAIL']);
        $this->assertNull((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date));

        $observingPublications = new E049ObservingCompletedRecoveryPublicationRepository();
        $publicationsProperty = new ReflectionProperty($world['pipeline'], 'publications');
        $publicationsProperty->setAccessible(true);
        $publicationsProperty->setValue($world['pipeline'], $observingPublications);

        $recovery = $world['pipeline']->completeFinalize(new \App\Application\MarketData\DTOs\MarketDataStageInput(
            $date, 'api', $priorRunId, 'FINALIZE', null, false, null, 'full_publish'
        ));

        $this->assertSame((int) $recovery->run_id, $observingPublications->clearObserved['owner_run_id']);
        $this->assertNull($observingPublications->clearObserved['released_at']);
        $this->assertSame('PENDING', $observingPublications->clearObserved['lifecycle_state']);

        $this->assertNotSame($priorRunId, (int) $recovery->run_id);
        $this->assertSame($priorRunId, (int) $recovery->supersedes_run_id);
        $this->assertSame('HELD', $recovery->terminal_status);
        $this->assertSame('NOT_READABLE', $recovery->publishability_state);
        $this->assertSame('RUN_LOCK_CONFLICT', $recovery->final_reason_code);
        $this->assertSame('COMPLETED', $recovery->lifecycle_state);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', $date)->exists());
        $this->assertSame(0, (int) DB::table('eod_publications')->where('publication_id', $priorPublicationId)->value('is_current'));
        $this->assertNull((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date));
        $this->assertSame($priorHistory, DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson());
        $this->assertSame('SUCCESS', DB::table('eod_runs')->where('run_id', $priorRunId)->value('terminal_status'));
        $this->assertSame('FAIL', DB::table('eod_runs')->where('run_id', $priorRunId)->value('coverage_gate_state'));
        $this->assertSame((int) $recovery->run_id, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->value('released_at'));
        $this->assertSame(1, DB::table('eod_run_events')->where('run_id', $recovery->run_id)
            ->where('event_type', 'RUN_OWNER_RELEASED')->count());
    }

    public function test_completed_pointer_recovery_rolls_back_failed_cleanup_and_can_retry(): void
    {
        $world = R0025SyntheticV2World::build();
        $date = R0025SyntheticV2World::world()['trade_date'];
        $priorRunId = (int) $world['run_id'];
        $priorPublicationId = (int) $world['publication_id'];
        $priorHistory = DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson();
        DB::table('eod_current_publication_pointer')->where('trade_date', $date)->update(['publication_version' => 999]);
        $runCount = DB::table('eod_runs')->where('trade_date_requested', $date)->count();
        $eventCount = DB::table('eod_run_events')->where('trade_date_requested', $date)->count();
        $fault = new E049ThrowingCompletedCleanupPublicationRepository();
        $property = new ReflectionProperty($world['pipeline'], 'publications');
        $property->setAccessible(true);
        $property->setValue($world['pipeline'], $fault);

        try {
            $world['pipeline']->completeFinalize(new \App\Application\MarketData\DTOs\MarketDataStageInput(
                $date, 'api', $priorRunId, 'FINALIZE', null, false, null, 'full_publish'
            ));
            $this->fail('Post-cleanup exception silently committed a new owner or pointer change.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('E049_CONTROLLED_CLEANUP_FAILURE', $error->getMessage());
        }
        $this->assertTrue($fault->afterMutationReached);
        $this->assertSame($runCount, DB::table('eod_runs')->where('trade_date_requested', $date)->count());
        $this->assertSame($eventCount, DB::table('eod_run_events')->where('trade_date_requested', $date)->count());
        $this->assertSame($priorRunId, (int) DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $date)->value('owner_run_id'));
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->value('released_at'));
        $this->assertSame(999, (int) DB::table('eod_current_publication_pointer')
            ->where('trade_date', $date)->value('publication_version'));
        $this->assertNull((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date));
        $this->assertSame(1, (int) DB::table('eod_publications')
            ->where('publication_id', $priorPublicationId)->value('is_current'));
        $this->assertSame($priorHistory, DB::table('eod_bars_history')->where('publication_id', $priorPublicationId)
            ->orderBy('ticker_id')->get()->toJson());

        $property->setValue($world['pipeline'], new EodPublicationRepository());
        $recovery = $world['pipeline']->completeFinalize(new \App\Application\MarketData\DTOs\MarketDataStageInput(
            $date, 'api', $priorRunId, 'FINALIZE', null, false, null, 'full_publish'
        ));
        $this->assertNotSame($priorRunId, (int) $recovery->run_id);
        $this->assertSame('HELD', $recovery->terminal_status);
        $this->assertSame($priorPublicationId, (int) DB::table('eod_current_publication_pointer')
            ->where('trade_date', $date)->value('publication_id'));
    }
}

class E049PostSwitchReadFaultPublicationRepository extends EodPublicationRepository
{
    public bool $injected = false;
    public ?array $observed = null;

    public function findPointerResolvedPublicationForTradeDate($tradeDate)
    {
        $actual = parent::findPointerResolvedPublicationForTradeDate($tradeDate);
        if (! $this->injected && $tradeDate === '2026-03-24' && $actual) {
            $run = DB::table('eod_runs')->where('run_id', $actual->run_id)->first();
            if ($run && $run->terminal_status === 'SUCCESS') {
                $fence = DB::table('eod_run_owner_fences')->where('trade_date_requested', $tradeDate)->first();
                $this->observed = [
                    'actual_publication_id' => (int) $actual->publication_id,
                    'actual_run_id' => (int) $actual->run_id,
                    'fence_owner_run_id' => $fence ? (int) $fence->owner_run_id : null,
                    'released_at' => $fence->released_at ?? null,
                    'lifecycle_state' => $run->lifecycle_state,
                    'terminal_status' => $run->terminal_status,
                ];
                $this->injected = true;
                return null;
            }
        }
        return $actual;
    }
}

class E049ThrowingCompletedCleanupPublicationRepository extends EodPublicationRepository
{
    public bool $afterMutationReached = false;

    public function restorePriorCurrentPublication($tradeDate, $priorPublicationId, $priorRunId = null, ?\App\Models\EodRun $actingOwner = null)
    {
        return null;
    }

    public function clearCurrentPublicationState($tradeDate, ?\App\Models\EodRun $actingOwner = null)
    {
        parent::clearCurrentPublicationState($tradeDate, $actingOwner);
        $this->afterMutationReached = true;
        throw new RuntimeException('E049_CONTROLLED_CLEANUP_FAILURE');
    }
}

class E049ObservingCompletedRecoveryPublicationRepository extends EodPublicationRepository
{
    public ?array $restoreObserved = null;
    public ?array $clearObserved = null;

    public function restorePriorCurrentPublication($tradeDate, $priorPublicationId, $priorRunId = null, ?\App\Models\EodRun $actingOwner = null)
    {
        $this->restoreObserved = $this->observeOwner($tradeDate);
        return parent::restorePriorCurrentPublication($tradeDate, $priorPublicationId, $priorRunId, $actingOwner);
    }

    public function clearCurrentPublicationState($tradeDate, ?\App\Models\EodRun $actingOwner = null)
    {
        $this->clearObserved = $this->observeOwner($tradeDate);
        return parent::clearCurrentPublicationState($tradeDate, $actingOwner);
    }

    private function observeOwner($tradeDate): array
    {
        $fence = DB::table('eod_run_owner_fences')->where('trade_date_requested', $tradeDate)->first();
        $run = $fence && $fence->owner_run_id
            ? DB::table('eod_runs')->where('run_id', $fence->owner_run_id)->first() : null;
        return [
            'owner_run_id' => $fence && $fence->owner_run_id ? (int) $fence->owner_run_id : null,
            'released_at' => $fence->released_at ?? null,
            'lifecycle_state' => $run->lifecycle_state ?? null,
        ];
    }
}
