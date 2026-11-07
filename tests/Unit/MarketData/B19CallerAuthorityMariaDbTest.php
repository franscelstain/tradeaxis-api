<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

class B19CallerAuthorityMariaDbTest extends B19OwnerFenceMariaDbTest
{
    public function test_incumbent_model_is_not_a_competing_callers_execution_credential(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $this->primeUnsafeCurrentPointer($owner, $candidate);
        foreach ($this->protectedOperations($owner, $candidate->publication_id) as $family => $operation) {
            $before = $this->fencedState();
            $error = $this->onIndependentOwnerConnection(function () use ($operation) {
                try { $operation(); return null; }
                catch (RuntimeException $error) { return $error->getMessage(); }
            });
            $this->assertSame($before, $this->fencedState(), 'Impersonating caller persisted '.$family);
            $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error, $family);
        }
        $before = $this->fencedState();
        $error = $this->onIndependentOwnerConnection(function () use ($date, $owner) {
            try {
                (new EodRunRepository())->getOrCreateOwningRun($date, $owner->source, $owner->stage, null, $owner->request_mode);
                return null;
            } catch (RuntimeException $error) { return $error->getMessage(); }
        });
        $this->assertSame($before, $this->fencedState(), 'Same-scope acquisition borrowed the incumbent execution');
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
    }
    public function test_observer_has_separate_append_only_authority_and_cannot_borrow_owner_rights(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $this->primeUnsafeCurrentPointer($owner, $candidate);
        $original = $this->fencedState();
        $this->onIndependentOwnerConnection(function () use ($old, $owner, $candidate) {
            \App\Infrastructure\Persistence\MarketData\RunWriterContext::observe('detector', $old, function () use ($old, $owner, $candidate) {
                foreach ($this->protectedOperations($owner, $candidate->publication_id) as $family => $operation) {
                    $before = $this->fencedState();
                    try { $operation(); $this->fail('Observer borrowed publication authority: '.$family); }
                    catch (RuntimeException $error) { $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage()); }
                    $this->assertSame($before, $this->fencedState(), 'Observer mutated '.$family);
                }
                $repo = new EodRunRepository();
                $event = $repo->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR',
                    'Authenticated observed condition', null, ['observed_condition' => 'DATASET_HASH_MISMATCH']);
                $payload = json_decode($event->event_payload_json, true);
                $this->assertSame('OBSERVATION_ONLY', $event->writer_kind);
                $this->assertSame((int) $old->run_id, (int) $event->run_id);
                $this->assertNotSame((string) $owner->run_id, $event->writer_actor_id);
                $this->assertStringNotContainsString('credential_hash', $event->event_payload_json);
                $before = $this->fencedState();
                try { $repo->appendObservation($owner, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Wrong target'); $this->fail('Wrong observer target accepted'); }
                catch (RuntimeException $error) { $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID', $error->getMessage()); }
                try { $repo->appendObservation($old, 'FINALIZE', 'RUN_FINALIZED', 'INFO', 'Wrong scope'); $this->fail('Observer forged producer finalization'); }
                catch (RuntimeException $error) { $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID', $error->getMessage()); }
                $this->assertSame($before, $this->fencedState());
            });
        });
        $before = $this->fencedState();
        try { (new EodRunRepository())->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Released observer'); $this->fail('Released observer accepted'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID', $error->getMessage()); }
        $this->assertSame($before, $this->fencedState());
        $before = json_decode($original, true); $after = json_decode($this->fencedState(), true);
        $this->assertCount(count($before['eod_run_events']) + 1, $after['eod_run_events']);
        unset($before['eod_run_events'], $after['eod_run_events']);
        $this->assertSame($before, $after, 'Observation changed protected owner/publication state');
        $lease = DB::table('eod_observer_fences')->where('scope_key', 'detector|'.$date)->first();
        $this->assertNotNull($lease->released_at);
        $this->assertSame((int) $old->run_id, (int) $lease->target_run_id);
    }

    public function test_observer_fence_serializes_independent_connections_and_rolls_back_atomically(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $before = $this->fencedState();
        $leases = DB::table('eod_observer_fences')->get()->toJson();
        try {
            \App\Infrastructure\Persistence\MarketData\RunWriterContext::observe('detector', $old, function () use ($old) {
                (new EodRunRepository())->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Rollback probe');
                throw new RuntimeException('OBSERVER_AFTER_APPEND_FAULT');
            });
            $this->fail('Observer rollback fault did not execute');
        } catch (RuntimeException $error) { $this->assertSame('OBSERVER_AFTER_APPEND_FAULT', $error->getMessage()); }
        $this->assertSame($before, $this->fencedState());
        $this->assertSame($leases, DB::table('eod_observer_fences')->get()->toJson());
        \App\Infrastructure\Persistence\MarketData\RunWriterContext::observe('detector', $old, function () use ($old) {
            $error = $this->onIndependentOwnerConnection(function () use ($old) {
                DB::statement('SET SESSION innodb_lock_wait_timeout = 1');
                try {
                    (new EodRunRepository())->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Borrowed context');
                    $this->fail('Observer connection stole credential');
                } catch (RuntimeException $error) { $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID', $error->getMessage()); }
                try {
                    (new EodRunRepository())->recordObservation($old, 'detector', 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Contending observer');
                    return null;
                } catch (\Illuminate\Database\QueryException $error) { return $error->getMessage(); }
            });
            $this->assertStringContainsString('Lock wait timeout exceeded', (string) $error);
            (new EodRunRepository())->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Legitimate serial observer');
        });
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'OBSERVED_CONDITION')->count());
        (new EodRunRepository())->recordObservation($old, 'detector', 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Authorized retry');
        $this->assertSame(2, DB::table('eod_run_events')->where('event_type', 'OBSERVED_CONDITION')->count());
        $this->assertSame(2, (int) DB::table('eod_observer_fences')->where('scope_key', 'detector|'.$date)->value('token'));
    }

    public function test_import_and_replay_credentials_are_isolated_and_cannot_publish(): void
    {
        $repo = new EodRunRepository(); $pub = new EodPublicationRepository(); $date = '2026-03-21';
        $import = $repo->getOrCreateOwningRun($date, 'api', 'INGEST_BARS', null, 'import_only');
        $candidate = $pub->getOrCreateCandidatePublication($import);
        $repo->appendEvent($import, 'INGEST_BARS', 'STAGE_COMPLETED', 'INFO', 'Authorized isolated import');
        foreach (['sealCandidatePublication', 'promoteCandidateToCurrent'] as $method) {
            $before = $this->fencedState();
            try { $pub->$method($import, null); $this->fail('Import credential gained publication rights'); }
            catch (RuntimeException $error) { $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage()); }
            $this->assertSame($before, $this->fencedState());
        }
        $import = $repo->completeImportOnly($import, 'INGEST_BARS');
        $this->assertSame('COMPLETED', $import->lifecycle_state);
        $this->assertSame('NOT_READABLE', $import->publishability_state);
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->value('released_at'));
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
        $before = $this->fencedState();
        $replay = null;
        \App\Infrastructure\Persistence\MarketData\ReplayIsolation::run(function () use (&$replay, $repo, $pub, $date, $import) {
            $replay = $repo->createAsKnownReplayRun($date, $import->knowledge_cutoff_at, 'api');
            $this->assertSame('replay_verify', $replay->request_mode);
            $pub->getOrCreateCandidatePublication($replay);
            $repo->appendEvent($replay, 'INGEST_BARS', 'STAGE_COMPLETED', 'INFO', 'Isolated replay event');
            try { $pub->sealCandidatePublication($replay, null); $this->fail('Replay credential gained seal rights'); }
            catch (RuntimeException $error) { $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage()); }
            $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
        });
        $this->assertSame($before, $this->fencedState(), 'Replay survived enforced isolation rollback');
        $this->assertSame(0, DB::table('eod_runs')->where('request_mode', 'replay_verify')->count());
        try { $repo->appendEvent($replay, 'INGEST_BARS', 'STAGE_COMPLETED', 'INFO', 'Rolled-back credential'); $this->fail('Rolled-back replay credential accepted'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage()); }
        $this->assertSame($before, $this->fencedState());
    }

    public function test_public_fence_fields_and_forged_secret_do_not_authenticate_a_competing_caller(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $lease = DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first();
        $this->onIndependentOwnerConnection(function () use ($owner, $candidate, $lease) {
            \App\Infrastructure\Persistence\MarketData\RunWriterContext::remember((int) $owner->run_id,
                (string) $lease->execution_id, str_repeat('0', 64), ['date' => (string) $owner->trade_date_requested,
                    'source' => (string) $owner->source, 'mode' => (string) $owner->request_mode]);
            foreach ($this->protectedOperations($owner, $candidate->publication_id) as $family => $operation) {
                $before = $this->fencedState();
                try { $operation(); $this->fail('Forged secret authenticated '.$family); }
                catch (RuntimeException $error) { $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage()); }
                $this->assertSame($before, $this->fencedState(), 'Forged credential persisted '.$family);
            }
        });
        (new EodRunRepository())->appendEvent($owner, 'FINALIZE', 'AUTHORIZED_AFTER_FORGERY', 'INFO', 'Original credential remains valid');
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'AUTHORIZED_AFTER_FORGERY')->count());
    }

    public function test_capture_and_artifact_entry_points_cannot_reload_a_run_to_gain_authority(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $capture = new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();
        $authorized = $capture->capture((int) $owner->run_id, 'INGEST_BARS', 'ancillary',
            ['operation' => 'authority-control/authorized'], [['independent_control' => 'authorized']]);
        $this->assertSame((int) $owner->run_id, (int) $authorized['run_id']);
        $before = DB::table('md_run_input_captures')->orderBy('input_capture_id')->get()->toJson();
        $this->onIndependentOwnerConnection(function () use ($date, $owner, $candidate, $capture) {
            foreach ([
                'capture' => fn () => $capture->capture((int) $owner->run_id, 'INGEST_BARS', 'ancillary',
                    ['operation' => 'authority-control/unauthorized'], [['independent_control' => 'must_not_persist']]),
                'artifact' => fn () => (new \App\Infrastructure\Persistence\MarketData\EodArtifactRepository())
                    ->replaceBars($date, $candidate->publication_id, $owner->run_id, [], [], true),
            ] as $family => $write) {
                $state = $this->fencedState();
                try { $write(); $this->fail('Reloaded target admitted '.$family.' writer'); }
                catch (RuntimeException $error) { $this->assertStringContainsString('RUN_OWNER_CONFLICT', $error->getMessage()); }
                $this->assertSame($state, $this->fencedState());
            }
        });
        $this->assertSame($before, DB::table('md_run_input_captures')->orderBy('input_capture_id')->get()->toJson());
    }

    public function test_isolated_execution_cannot_change_its_acquired_scope_into_publication_authority(): void
    {
        $repo = new EodRunRepository();
        $run = $repo->getOrCreateOwningRun('2026-03-21', 'api', 'INGEST_BARS', null, 'import_only');
        foreach ([
            'mode' => fn () => $repo->touchStage($run, 'INGEST_BARS', ['request_mode' => 'full_publish']),
            'readability' => fn () => $repo->finalize($run, ['lifecycle_state' => 'COMPLETED', 'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE']),
            'producer_event' => fn () => $repo->appendEvent($run, 'FINALIZE', 'RUN_FINALIZED', 'INFO', 'Forbidden publication claim'),
        ] as $family => $write) {
            $before = $this->fencedState(); $error = null;
            try { $write(); } catch (RuntimeException $caught) { $error = $caught->getMessage(); }
            $this->assertSame($before, $this->fencedState(), 'Isolated execution escalated '.$family);
            $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
        }
    }

    public function test_promote_acquisition_cannot_displace_an_active_import_scope(): void
    {
        $repo = new EodRunRepository();
        $run = $repo->getOrCreateOwningRun('2026-03-21', 'api', 'INGEST_BARS', null, 'import_only');
        $before = $this->fencedState(); $error = null;
        try { $repo->createPromoteRunFromSeed($run, 'PUBLISH_BARS'); }
        catch (RuntimeException $caught) { $error = $caught->getMessage(); }
        $this->assertSame($before, $this->fencedState(), 'Promotion displaced a live isolated import execution');
        $this->assertStringContainsString('RUN_OWNER_CONFLICT', (string) $error);
    }

    public function test_replay_acquisition_requires_its_callers_outer_rollback_transaction(): void
    {
        $repo = new EodRunRepository();
        $run = $repo->getOrCreateOwningRun('2026-03-21', 'api', 'INGEST_BARS', null, 'import_only');
        $run = $repo->completeImportOnly($run, 'INGEST_BARS');
        $before = $this->fencedState(); $error = null;
        try { $repo->createAsKnownReplayRun('2026-03-21', $run->knowledge_cutoff_at, 'api'); }
        catch (RuntimeException $caught) { $error = $caught->getMessage(); }
        $this->assertSame($before, $this->fencedState(), 'Replay acquisition escaped the required rollback boundary');
        $this->assertStringContainsString('REPLAY_ISOLATION_TRANSACTION_REQUIRED', (string) $error);
    }

    public function test_observer_validates_the_actual_persisted_actor_token_target_and_release_at_each_append(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        \App\Infrastructure\Persistence\MarketData\RunWriterContext::observe('detector', $old, function () use ($date, $old, $owner) {
            foreach ([['actor_id' => str_repeat('0', 32)], ['token' => 999999], ['target_run_id' => $owner->run_id],
                ['credential_hash' => str_repeat('0', 64)], ['released_at' => '2026-03-21 19:00:00']] as $revocation) {
                DB::beginTransaction();
                try {
                    DB::table('eod_observer_fences')->where('scope_key', 'detector|'.$date)->update($revocation);
                    $before = $this->fencedState(); $error = null;
                    try { (new EodRunRepository())->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Revoked observer must refuse'); }
                    catch (RuntimeException $caught) { $error = $caught->getMessage(); }
                    $this->assertSame($before, $this->fencedState(), 'Revoked observer persisted an event');
                    $this->assertStringContainsString('OBSERVER_AUTHORITY_INVALID', (string) $error);
                } finally { DB::rollBack(); }
            }
            (new EodRunRepository())->appendObservation($old, 'FINALIZE', 'OBSERVED_CONDITION', 'ERROR', 'Original observer remains authorized');
        });
        $this->assertSame(1, DB::table('eod_run_events')->where('event_type', 'OBSERVED_CONDITION')->count());
    }

    public function test_post_terminal_reprocess_observations_preserve_history_deduplicate_and_reject_corruption(): void
    {
        [$date, $old, $owner] = $this->completedOwnerWithLiveSuccessor();
        $service = new \App\Application\MarketData\Services\PublicationReprocessObservationService();
        $before = DB::table('eod_runs')->orderBy('run_id')->get()->toJson();
        $notes = ['publication_reprocess_state'=>'NOOP','publication_reprocess_republished_trade_date_count'=>0,
            'publication_reprocess_republication_mode'=>'NOT_REQUIRED', 'source_attempt_count'=>99];
        $service->record($old, $notes); $service->record($old, $notes);
        $observed = $service->notesFor($old);
        $this->assertSame('NOOP', $observed['notes']['publication_reprocess_state']);
        $this->assertSame('0', $observed['notes']['publication_reprocess_republished_trade_date_count']);
        $this->assertArrayNotHasKey('source_attempt_count', $observed['notes']);
        $this->assertCount(1, $observed['event_ids']);
        $this->assertSame($before, DB::table('eod_runs')->orderBy('run_id')->get()->toJson());
        $event = (array) DB::table('eod_run_events')->where('event_id', $observed['event_ids'][0])->first();
        $this->assertSame('OBSERVATION_ONLY', $event['writer_kind']);
        foreach (['content_hash', 'rehashed_source'] as $case) {
            DB::beginTransaction();
            try {
                $bad = $event; unset($bad['event_id']); $payload = json_decode($bad['event_payload_json'], true);
                if ($case === 'content_hash') $payload['notes_delta']['publication_reprocess_republished_trade_date_count'] = '1';
                else {
                    $payload['source_mode'] = 'manual_file'; unset($payload['content_sha256']);
                    $payload['content_sha256'] = hash('sha256', \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::canonicalJson($payload));
                }
                $bad['event_payload_json'] = json_encode($payload); DB::table('eod_run_events')->insert($bad);
                $error = null;
                try { $service->notesFor($old); } catch (RuntimeException $caught) { $error = $caught->getMessage(); }
                $this->assertSame('IMPACT_OBSERVATION_IDENTITY_INVALID', $error, 'Unverified impact observation accepted: '.$case);
            } finally { DB::rollBack(); }
        }
        $this->assertSame($observed, $service->notesFor($old));
    }

}
