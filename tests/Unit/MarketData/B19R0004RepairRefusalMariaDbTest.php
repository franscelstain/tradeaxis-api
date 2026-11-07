<?php

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Application\MarketData\Services\CurrentPublicationRepairService;
use Illuminate\Support\Facades\DB;

/** Reuses the existing migrated two-connection world's isolated database lifecycle. */
class B19R0004RepairRefusalMariaDbTest extends B19OwnerFenceMariaDbTest
{
    public function test_operator_reason_does_not_grant_pointer_write_ownership(): void
    {
        $owner=(new EodRunRepository())->getOrCreateOwningRun('2026-03-21','api','FINALIZE',null,'repair_candidate');
        $candidate=(new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date'=>'2026-03-21','publication_id'=>$candidate->publication_id,
            'run_id'=>$owner->run_id,'publication_version'=>$candidate->publication_version,
            'sealed_at'=>'2026-03-21 18:00:00','updated_at'=>'2026-03-21 18:00:00',
        ]);
        $before=$this->state();
        $status=$this->artisan('market-data:current-publication:repair',[
            '--trade_date'=>'2026-03-21','--apply'=>true,'--reason'=>'Operator request with no acting owner token',
        ]);
        $output=app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertSame(2,$status);
        $this->assertStringContainsString('status=BLOCKED',$output);
        $this->assertStringContainsString('RUN_OWNER_CONFLICT',$output);
        $this->assertStringNotContainsString('operation_mode=APPLIED',$output);
        $this->assertSame($before,$this->state(),'Operator command mutated pointer/publication/run without a token');
    }

    public function test_released_fence_cannot_be_revived_by_assertion_or_alternate_publication_entry(): void
    {
        $runs=new EodRunRepository();
        $owner=$runs->getOrCreateOwningRun('2026-03-21','api','FINALIZE',null,'repair_candidate');
        $candidate=(new EodPublicationRepository())->getOrCreateCandidatePublication($owner);
        $owner=$runs->finalize($owner,['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD']);
        // A stale active row cannot turn a released fence into acquisition authority.
        DB::table('eod_runs')->where('run_id',$owner->run_id)->update(['lifecycle_state'=>'RUNNING']);
        $owner=$owner->fresh();
        $operations=[
            static fn ()=>$runs->assertPublishCriticalOwner($owner),
            static fn ()=>(new EodPublicationRepository())->sealCandidatePublication($owner,$candidate),
            static fn ()=>(new EodPublicationRepository())->promoteCandidateToCurrent($owner,$candidate),
        ];
        foreach($operations as $operation){
            $before=$this->state();
            try{$operation();$this->fail('Released fence was revived');}
            catch(RuntimeException $e){$this->assertStringContainsString('RUN_OWNER_CONFLICT',$e->getMessage());}
            $this->assertSame($before,$this->state());
        }
    }

    public function test_operator_apply_acquires_a_new_linked_recovery_owner_before_atomic_cleanup(): void
    {
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun('2026-03-21', 'api', 'FINALIZE', null, 'repair_candidate');
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($old);
        $this->primeUnsafeCurrentPointer($old, $candidate);
        $old = $runs->finalize($old, ['lifecycle_state' => 'COMPLETED', 'terminal_status' => 'HELD', 'publishability_state' => 'NOT_READABLE']);
        $before = DB::table('eod_runs')->where('run_id', $old->run_id)->first();
        $status = $this->artisan('market-data:current-publication:repair', [
            '--trade_date' => '2026-03-21', '--apply' => true, '--reason' => 'Explicit bounded linked recovery',
        ]);
        $output = app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertSame(0, $status, $output);
        $this->assertStringContainsString('operation_mode=APPLIED', $output);
        $this->assertStringContainsString('prospective_recovery_config_hash=', $output);
        $this->assertStringContainsString('historical_config_hash=', $output);
        $this->assertLessThan(strpos($output, 'operation_mode=APPLIED'), strpos($output, 'prospective_recovery_config_hash='));
        $this->assertStringContainsString('reason_code=COMMAND_APPLY_CONFIRMED', $output);
        $this->assertStringContainsString('repair_action=CLEARED_INVALID_CURRENT_STATE', $output);
        $this->assertStringContainsString('pointer_after_state=CLEARED', $output);
        $this->assertStringContainsString('repair_reason=Explicit bounded linked recovery', $output);
        $this->assertStringContainsString('pointer_before_publication_id='.(int) $candidate->publication_id, $output);
        $recovery = DB::table('eod_runs')->where('supersedes_run_id', $old->run_id)->first();
        $this->assertNotNull($recovery);
        $this->assertNotSame((int) $old->run_id, (int) $recovery->run_id);
        $this->assertSame('COMPLETED', $recovery->lifecycle_state);
        $this->assertSame('NOT_READABLE', $recovery->publishability_state);
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
        $this->assertSame(0, DB::table('eod_publications')->where('is_current', 1)->count());
        $this->assertSame(0, DB::table('eod_runs')->where('is_current_publication', 1)->count());
        $after = DB::table('eod_runs')->where('run_id', $old->run_id)->first();
        foreach (['lifecycle_state','terminal_status','publishability_state','sealed_at','bars_batch_hash','indicators_batch_hash','eligibility_batch_hash'] as $field) {
            $this->assertSame($before->$field, $after->$field, 'Repair rewrote historical '.$field);
        }
        $events = DB::table('eod_run_events')->where('run_id', $recovery->run_id)->orderBy('event_id')->get();
        $this->assertContains('OPERATOR_POINTER_REPAIR_COMPLETED', $events->pluck('event_type')->all());
        $this->assertSame('RUN_OWNER', $events->first()->writer_kind);
        $this->assertNotNull(DB::table('eod_run_owner_fences')->where('trade_date_requested','2026-03-21')->value('released_at'));
    }

    public function test_operator_recovery_cleanup_fault_rolls_back_owner_audit_and_pointer_and_allows_retry(): void
    {
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun('2026-03-21', 'api', 'FINALIZE', null, 'repair_candidate');
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($old);
        $this->primeUnsafeCurrentPointer($old, $candidate);
        $runs->finalize($old, ['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $before = $this->state(); $armed = true; $injected = false;
        DB::listen(function ($query) use (&$armed, &$injected) {
            if ($armed && preg_match('/^delete from `eod_current_publication_pointer`/i', $query->sql)) {
                $armed = false; $injected = true; throw new RuntimeException('OPERATOR_CLEANUP_AFTER_POINTER_FAULT');
            }
        });
        $options = ['--trade_date'=>'2026-03-21','--apply'=>true,'--reason'=>'Authorized recovery rollback control'];
        $status = $this->artisan('market-data:current-publication:repair', $options);
        $output = app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertTrue($injected, $output);
        $this->assertSame(2, $status, $output);
        $this->assertSame($before, $this->state(), 'Failed operator recovery left partial owner/audit/cleanup state');
        $this->assertSame(0, $this->artisan('market-data:current-publication:repair', $options));
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
    }

    public function test_operator_scan_to_write_race_refuses_after_an_independent_authorized_recovery_wins(): void
    {
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun('2026-03-21', 'api', 'FINALIZE', null, 'repair_candidate');
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($old);
        $this->primeUnsafeCurrentPointer($old, $candidate);
        $runs->finalize($old, ['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $expected = null;
        $repo = new E049RepairScanRacePublicationRepository();
        $repo->afterScan = function () use ($old, &$expected) {
            $this->onIndependentOwnerConnection(function () use ($old, &$expected) {
                $other = new EodRunRepository();
                $owner = $other->getOrCreateOwningRun('2026-03-21', 'api', 'FINALIZE', (int) $old->run_id, 'repair_candidate');
                (new EodPublicationRepository())->clearCurrentPublicationState('2026-03-21', $owner);
                $other->finalize($owner, ['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
                $expected = $this->state();
            });
        };
        app()->instance(EodPublicationRepository::class, $repo);
        $status = $this->artisan('market-data:current-publication:repair', [
            '--trade_date'=>'2026-03-21','--apply'=>true,'--reason'=>'Scan-to-write authorized competitor control',
        ]);
        $output = app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertSame(2, $status, $output);
        $this->assertStringContainsString('COMMAND_REPAIR_SCAN_CHANGED', $output);
        $this->assertNotNull($expected);
        $this->assertSame($expected, $this->state(), 'Stale operator scan left an extra owner/audit/mutation');
        $this->assertSame(2, DB::table('eod_runs')->count(), 'Only the original and independently acquired recovery may persist');
    }

    public function test_disclosed_recovery_config_source_and_target_mismatch_roll_back_before_owner_allocation(): void
    {
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun('2026-03-21', 'api', 'FINALIZE', null, 'repair_candidate');
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($old);
        $this->primeUnsafeCurrentPointer($old, $candidate);
        $runs->finalize($old, ['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        $scanned = (array) (new EodPublicationRepository())->findInvalidCurrentPublicationStates('2026-03-21')->first();
        $service = app(CurrentPublicationRepairService::class);
        $valid = $service->preflight('2026-03-21', $scanned);
        $before = $this->state();
        foreach ([
            ['prospective_recovery_config_hash', str_repeat('0', 64)],
            ['recovery_source_mode', 'manual_file'],
            ['pointer_publication_id', -1],
        ] as [$field, $wrong]) {
            $tampered = $valid;
            $tampered[$field] = $wrong;
            try {
                $service->apply('2026-03-21', $scanned, 'Rejected undisclosed change', $tampered);
                $this->fail('Disclosure mismatch was accepted: '.$field);
            } catch (RuntimeException $error) {
                $this->assertStringContainsString('COMMAND_REPAIR_PREFLIGHT_CHANGED', $error->getMessage());
            }
            $this->assertSame($before, $this->state(), 'Disclosure mismatch left partial state: '.$field);
        }
    }

    public function test_unverifiable_historical_config_is_labeled_separately_from_recovery_config(): void
    {
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun('2026-03-21', 'api', 'FINALIZE', null, 'repair_candidate');
        $candidate = (new EodPublicationRepository())->getOrCreateCandidatePublication($old);
        $this->primeUnsafeCurrentPointer($old, $candidate);
        $runs->finalize($old, ['lifecycle_state'=>'COMPLETED','terminal_status'=>'HELD','publishability_state'=>'NOT_READABLE']);
        DB::table('eod_runs')->where('run_id', $old->run_id)->update(['config_snapshot_id' => null]);
        $status = $this->artisan('market-data:current-publication:repair', [
            '--trade_date'=>'2026-03-21','--apply'=>true,'--reason'=>'No authentic historical snapshot',
        ]);
        $output = app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertSame(0, $status, $output);
        $this->assertStringContainsString('historical_config_hash=HISTORICAL_CONFIG_HASH_UNAVAILABLE', $output);
        $this->assertStringContainsString('historical_config_unavailable_reason=HISTORICAL_CONFIG_SNAPSHOT_UNVERIFIABLE', $output);
        $this->assertMatchesRegularExpression('/prospective_recovery_config_hash=[a-f0-9]{64}/', $output);
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
    }

    public function test_orphan_pointer_without_authentic_source_refuses_before_recovery_allocation(): void
    {
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-21', 'publication_id' => 987654,
            'run_id' => 987654, 'publication_version' => 1,
            'sealed_at' => '2026-03-21 18:00:00', 'updated_at' => '2026-03-21 18:00:00',
        ]);
        $before = $this->state();
        $status = $this->artisan('market-data:current-publication:repair', [
            '--trade_date' => '2026-03-21', '--apply' => true,
            '--reason' => 'Orphan pointer has no authenticated historical source',
        ]);
        $output = app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertSame(2, $status, $output);
        $this->assertStringContainsString('COMMAND_REPAIR_SOURCE_UNVERIFIABLE', $output);
        $this->assertSame($before, $this->state(), 'Orphan pointer without source created recovery state.');
    }

    public function test_missing_publication_row_can_recover_from_authenticated_pointer_run_source(): void
    {
        $runs = new EodRunRepository();
        $old = $runs->getOrCreateOwningRun('2026-03-21', 'manual_file', 'FINALIZE', null, 'repair_candidate');
        $runs->finalize($old, ['lifecycle_state' => 'COMPLETED', 'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE']);
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-21', 'publication_id' => 987654,
            'run_id' => $old->run_id, 'publication_version' => 1,
            'sealed_at' => '2026-03-21 18:00:00', 'updated_at' => '2026-03-21 18:00:00',
        ]);
        $status = $this->artisan('market-data:current-publication:repair', [
            '--trade_date' => '2026-03-21', '--apply' => true,
            '--reason' => 'Authenticated historical run source despite missing publication row',
        ]);
        $output = app(\Illuminate\Contracts\Console\Kernel::class)->output();
        $this->assertSame(0, $status, $output);
        $this->assertStringContainsString('historical_run_id='.$old->run_id, $output);
        $this->assertStringContainsString('recovery_source_mode=manual_file', $output);
        $recovery = DB::table('eod_runs')->where('supersedes_run_id', $old->run_id)->first();
        $this->assertNotNull($recovery);
        $this->assertSame('manual_file', $recovery->source);
        $this->assertSame(0, DB::table('eod_current_publication_pointer')->count());
        $this->assertSame('COMPLETED', DB::table('eod_runs')->where('run_id', $old->run_id)->value('lifecycle_state'));
    }

    private function state(): string
    {
        $state=[];
        foreach(['eod_run_owner_fences'=>'trade_date_requested','eod_runs'=>'run_id','eod_run_events'=>'event_id',
            'eod_publications'=>'publication_id','eod_current_publication_pointer'=>'trade_date'] as $table=>$key){
            $state[$table]=DB::table($table)->orderBy($key)->get()->all();
        }
        return json_encode($state);
    }
}

class E049RepairScanRacePublicationRepository extends EodPublicationRepository
{
    public $afterScan;
    public function findInvalidCurrentPublicationStates($tradeDate = null)
    {
        $rows = parent::findInvalidCurrentPublicationStates($tradeDate);
        if ($this->afterScan) { $callback = $this->afterScan; $this->afterScan = null; $callback(); }
        return $rows;
    }
}
