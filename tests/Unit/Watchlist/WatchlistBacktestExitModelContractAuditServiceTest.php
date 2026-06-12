<?php

use App\Application\Watchlist\Services\WatchlistBacktestExitModelContractAuditService;

class WatchlistBacktestExitModelContractAuditServiceTest extends TestCase
{
    public function test_it_blocks_exit_model_catalog_authorization_from_failed_c10_summary(): void
    {
        $summary = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c11-exit-contract-summary.csv';
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c11-exit-contract-audit.json';
        @unlink($summary);
        @unlink($output);
        $this->writeSummary($summary);

        $result = (new WatchlistBacktestExitModelContractAuditService())->execute($summary, $output, [
            'overwrite' => true,
            'generated_at' => '2026-06-12T00:00:00+07:00',
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_READY', $result['reason_code']);
        $this->assertFileExists($output);

        $artifact = $result['artifact'];
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06', $artifact['source_catalog']['catalog_code']);
        $this->assertFalse($artifact['decision']['exit_model_catalog_authorized']);
        $this->assertFalse($artifact['decision']['strategy_catalog_created']);
        $this->assertSame('NEXT_CATALOG_NOT_DESIGNED', $artifact['decision']['next_decision']);
        $this->assertContains('C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT', $artifact['decision']['blocking_reasons']);
        $this->assertContains('PUBLISHED_RUNTIME_FORCES_HOLD_5', $artifact['decision']['blocking_reasons']);
        $this->assertContains('PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS', $artifact['decision']['blocking_reasons']);
        $this->assertContains('C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES', $artifact['decision']['blocking_reasons']);
        $this->assertContains('C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET', $artifact['decision']['blocking_reasons']);
        $this->assertSame(30, $artifact['c10_summary']['exit_totals']['hit_target_total']);
        $this->assertSame(70, $artifact['c10_summary']['exit_totals']['hit_stop_total']);
        $this->assertSame(90, $artifact['c10_summary']['exit_totals']['timeout_hold_expired_total']);
        $this->assertFalse($artifact['no_oos_leakage_summary']['oos_executed']);
        $this->assertFalse($artifact['meta']['production_ready']);

        @unlink($summary);
        @unlink($output);
    }

    public function test_it_requires_real_c10_summary_artifact(): void
    {
        $missing = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c11-missing-summary.csv';
        @unlink($missing);

        $result = (new WatchlistBacktestExitModelContractAuditService())->execute(
            $missing,
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c11-unused.json',
            ['overwrite' => true]
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('OPERATOR_ARTIFACT_REQUIRED', $result['reason_code']);
        $this->assertFalse($result['oos_executed']);
        $this->assertFalse($result['production_ready']);
    }

    private function writeSummary(string $path): void
    {
        $headers = [
            'scope',
            'catalog_code',
            'catalog_version',
            'catalog_count',
            'catalog_hash',
            'param_id',
            'row_code',
            'status',
            'reason_code',
            'picks_count',
            'median_ret_net_top',
            'p25_ret_net_top',
            'month_win_rate_min',
            'hit_target_count',
            'hit_stop_count',
            'timeout_hold_expired_count',
            'missing_runtime_evidence_fields',
            'nullable_runtime_no_positive_evidence_fields',
            'next_focus',
            'next_decision',
            'oos_executed',
            'production_ready',
        ];
        $rows = [
            [
                'IS_ONLY_BATCHED_FAILURE_DRILLDOWN',
                'WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06',
                'C07',
                '2',
                'hash-c07',
                '1',
                'A',
                'DONE',
                'WS_BT_IS_FAILURE_DRILLDOWN_READY',
                '120',
                '-0.010',
                '-0.040',
                '0.20',
                '10',
                '30',
                '40',
                '',
                'corporate_action_flag|corporate_action_types|event_risk_reasons',
                'STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG',
                'NEXT_CATALOG_NOT_DESIGNED',
                '0',
                '0',
            ],
            [
                'IS_ONLY_BATCHED_FAILURE_DRILLDOWN',
                'WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06',
                'C07',
                '2',
                'hash-c07',
                '2',
                'B',
                'DONE',
                'WS_BT_IS_FAILURE_DRILLDOWN_READY',
                '130',
                '-0.005',
                '-0.035',
                '0.25',
                '20',
                '40',
                '50',
                '',
                'corporate_action_flag|corporate_action_types|event_risk_reasons',
                'STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG',
                'NEXT_CATALOG_NOT_DESIGNED',
                '0',
                '0',
            ],
        ];

        $handle = fopen($path, 'wb');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}
