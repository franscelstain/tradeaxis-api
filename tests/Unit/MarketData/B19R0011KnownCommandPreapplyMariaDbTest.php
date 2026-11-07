<?php

require_once __DIR__.'/B19OwnerFenceMariaDbTest.php';

use App\Application\MarketData\Services\CorporateActionExternalReconciliationService;
use App\Application\MarketData\Services\SessionSnapshotService;
use App\Console\Commands\MarketData\ApproveCorrectionCommand;
use App\Console\Commands\MarketData\RequestCorrectionCommand;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\R0025SyntheticV2World;

class B19R0011KnownCommandPreapplyMariaDbTest extends B19OwnerFenceMariaDbTest
{
    public function test_request_and_approve_disclose_before_persisted_correction_transitions(): void
    {
        $response = file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json');
        R0025SyntheticV2World::build(['http_fetcher' => static function () use ($response) {
            return ['status' => 200, 'body' => $response, 'headers' => ['Content-Type' => 'application/json']];
        }]);
        $date = R0025SyntheticV2World::world()['trade_date'];

        $request = new RequestCorrectionCommand();
        $request->setLaravel($this->app);
        $requestTester = new CommandTester($request);
        $this->assertSame(0, $requestTester->execute([
            '--trade_date' => $date,
            '--reason_code' => 'DATASET_HASH_CREATED',
            '--reason_note' => 'R0011 persisted disclosure proof',
            '--requested_by' => 'r0011-proof',
        ]), $requestTester->getDisplay());
        $requestOutput = $requestTester->getDisplay();
        $this->assertStringContainsString('operation_mode=PRE_APPLY_DISCLOSURE', $requestOutput);
        $this->assertLessThan(strpos($requestOutput, 'correction_id='), strpos($requestOutput, 'operation_mode=PRE_APPLY_DISCLOSURE'));
        $correction = DB::table('eod_dataset_corrections')->where('requested_by', 'r0011-proof')->first();
        $this->assertNotNull($correction);
        $this->assertSame('REQUESTED', (string) $correction->status);

        $approve = new ApproveCorrectionCommand();
        $approve->setLaravel($this->app);
        $approveTester = new CommandTester($approve);
        $this->assertSame(0, $approveTester->execute([
            'correction_id' => (int) $correction->correction_id,
            '--approved_by' => 'r0011-approver',
        ]), $approveTester->getDisplay());
        $approveOutput = $approveTester->getDisplay();
        $this->assertStringContainsString('operation_mode=PRE_APPLY_DISCLOSURE', $approveOutput);
        $this->assertLessThan(strpos($approveOutput, 'status=APPROVED'), strpos($approveOutput, 'operation_mode=PRE_APPLY_DISCLOSURE'));
        $this->assertSame('APPROVED', (string) DB::table('eod_dataset_corrections')
            ->where('correction_id', $correction->correction_id)->value('status'));
    }

    public function test_session_purge_refuses_changed_candidate_set_then_applies_exact_disclosure(): void
    {
        $row = [
            'trade_date' => '2026-03-01', 'snapshot_slot' => 'OPEN_CHECK', 'ticker_id' => 1,
            'captured_at' => '2026-03-01 09:10:00', 'source' => 'manual_file',
            'created_at' => '2026-03-01 09:10:00', 'updated_at' => '2026-03-01 09:10:00',
        ];
        DB::table('md_session_snapshots')->insert($row);
        $service = app(SessionSnapshotService::class);
        $disclosed = $service->inspectPurge('2026-03-02');
        DB::table('md_session_snapshots')->insert(array_merge($row, ['ticker_id' => 2]));
        try {
            $service->applyDisclosedPurge($disclosed, sys_get_temp_dir().'/r0011_purge_refusal_'.uniqid());
            $this->fail('Changed purge candidate set was accepted.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('COMMAND_SESSION_SNAPSHOT_PURGE_PREFLIGHT_CHANGED', $e->getMessage());
        }
        $this->assertSame(2, DB::table('md_session_snapshots')->count(), 'Refusal partially deleted persisted snapshots.');
        $current = $service->inspectPurge('2026-03-02');
        $result = $service->applyDisclosedPurge($current, sys_get_temp_dir().'/r0011_purge_green_'.uniqid());
        $this->assertSame(2, $result['deleted_rows']);
        $this->assertSame(0, DB::table('md_session_snapshots')->count());
    }

    public function test_corporate_action_manifest_change_refuses_without_partial_evidence_then_exact_input_applies(): void
    {
        $manifest = [
            'schema_version' => CorporateActionExternalReconciliationService::MANIFEST_SCHEMA,
            'authority_name' => 'IDX R0011 PROOF', 'authority_class' => 'EXCHANGE',
            'scope_start' => '2026-03-01', 'scope_end' => '2026-03-02', 'scope_complete' => false,
            'action_types' => ['STOCK_SPLIT'], 'events' => [],
        ];
        $path = tempnam(sys_get_temp_dir(), 'r0011_corp_');
        file_put_contents($path, json_encode($manifest, JSON_UNESCAPED_SLASHES));
        try {
            $service = new CorporateActionExternalReconciliationService();
            $disclosed = $service->inspectFile($path);
            $manifest['authority_name'] = 'IDX CHANGED AFTER DISCLOSURE';
            file_put_contents($path, json_encode($manifest, JSON_UNESCAPED_SLASHES));
            try {
                $service->applyDisclosedFile($path, $disclosed);
                $this->fail('Changed corporate-action manifest was accepted.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('COMMAND_CORPORATE_ACTION_RECONCILIATION_PREFLIGHT_CHANGED', $e->getMessage());
            }
            $this->assertSame(0, DB::table('md_corporate_action_reconciliations')->count());
            $current = $service->inspectFile($path);
            $result = $service->applyDisclosedFile($path, $current);
            $this->assertTrue($result['persisted']);
            $this->assertSame(1, DB::table('md_corporate_action_reconciliations')->count());
        } finally {
            @unlink($path);
        }
    }
}
