<?php

use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupportAuditService;

class WatchlistBacktestExitAxisSupportAuditServiceTest extends TestCase
{
    public function test_it_produces_support_audit_without_creating_catalog_or_oos(): void
    {
        $input = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c13-c12-input.json';
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c13-exit-axis-support.json';
        @unlink($input);
        @unlink($output);
        file_put_contents($input, json_encode($this->c12Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $result = (new WatchlistBacktestExitAxisSupportAuditService())->execute($input, $output, [
            'overwrite' => true,
            'generated_at' => '2026-06-12T00:00:00+07:00',
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WS_BT_C13_EXIT_AXIS_SUPPORT_READY', $result['reason_code']);
        $this->assertFileExists($output);

        $artifact = $result['artifact'];
        $this->assertSame('EXIT_AXIS_SUPPORT_READY_FOR_FUTURE_CATALOG_DEFINITION', $artifact['decision']['status']);
        $this->assertTrue($artifact['decision']['support_ready']);
        $this->assertFalse($artifact['decision']['catalog_creation_authorized']);
        $this->assertTrue($artifact['decision']['future_catalog_definition_work_authorized']);
        $this->assertFalse($artifact['decision']['strategy_catalog_created']);
        $this->assertFalse($artifact['decision']['exit_model_catalog_authorized']);
        $this->assertSame('CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY', $artifact['decision']['next_required_step']);
        $this->assertFalse($artifact['no_oos_leakage_summary']['oos_executed']);
        $this->assertFalse($artifact['meta']['production_ready']);
        $this->assertTrue($artifact['support_probe']['fixed_guard_rejects_drift']);
        $this->assertTrue($artifact['support_probe']['variable_policy_accepts_risk_axes']);
        $this->assertTrue($artifact['support_probe']['variable_policy_blocks_holding_days']);
        $this->assertTrue($artifact['support_probe']['variable_policy_blocks_target_stop_pct']);

        @unlink($input);
        @unlink($output);
    }

    public function test_it_requires_c12_contract_artifact(): void
    {
        $missing = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c13-missing-c12.json';
        @unlink($missing);

        $result = (new WatchlistBacktestExitAxisSupportAuditService())->execute(
            $missing,
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c13-unused.json',
            ['overwrite' => true]
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('OPERATOR_ARTIFACT_REQUIRED', $result['reason_code']);
        $this->assertFalse($result['oos_executed']);
        $this->assertFalse($result['production_ready']);
    }

    private function c12Artifact(): array
    {
        return [
            'meta' => [
                'artifact_hash' => 'hash-c12',
            ],
            'source_catalog' => [
                'catalog_code' => 'WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06',
                'catalog_version' => 'C07',
                'catalog_count' => '12',
                'catalog_hash' => 'hash-c07',
            ],
            'redesign_contract' => [
                'allowed_first_phase_axis_policy' => [
                    ['axis' => 'risk.min_rr'],
                    ['axis' => 'risk.stop_atr_mult'],
                ],
            ],
            'decision' => [
                'status' => 'EXIT_MODEL_REDESIGN_CONTRACT_READY',
                'reason_code' => 'WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY',
                'design_contract_ready' => true,
                'catalog_creation_authorized' => false,
                'exit_model_catalog_authorized' => false,
                'next_required_step' => 'IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG',
            ],
        ];
    }
}
