<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingSignalQualityQ01DiagnosticService;
use TestCase;

class WeeklySwingSignalQualityQ01DiagnosticTest extends TestCase
{
    public function test_it_uses_only_the_three_predeclared_decision_time_rules(): void
    {
        $rows = [];
        for ($index = 0; $index < 180; $index++) {
            $weak = $index < 30;
            $rows[] = [
                'trade_date' => (new \DateTimeImmutable('2023-01-02'))
                    ->modify('+'.$index.' days')
                    ->format('Y-m-d'),
                'ret_net' => $weak ? -0.10 : 0.01,
                'dv20_idr' => $weak ? 1000000000 : 6000000000,
                'vol_ratio' => $weak ? 1.2 : 3.0,
            ];
        }

        $analysis = (new WeeklySwingSignalQualityQ01DiagnosticService())
            ->analyzeEvidence($rows, 498);

        $this->assertSame([
            'Q01_C1_DV20_STRONG_5B',
            'Q01_C2_VOL_RATIO_MODERATE_1P5',
            'Q01_C3_VOL_RATIO_STRONG_2P5',
        ], array_column($analysis['candidate_diagnostics'], 'candidate_code'));
        $this->assertCount(3, $analysis['candidate_design_allowed']);
        $this->assertSame(498, $analysis['source_metrics']['days_covered']);
        $this->assertCount(2, $analysis['pre_registered_hypotheses']);
        foreach ($analysis['candidate_diagnostics'] as $candidate) {
            $this->assertTrue($candidate['candidate_design_authorized']);
            $this->assertTrue($candidate['one_primary_idea']);
            $this->assertTrue($candidate['decision_time_fields_only']);
            $this->assertFalse($candidate['future_return_as_runtime_input']);
            $this->assertFalse($candidate['ticker_blacklist_used']);
            $this->assertFalse($candidate['month_blacklist_used']);
            $this->assertFalse($candidate['oos_used']);
            $this->assertFalse($candidate['canonical_gates_changed']);
        }
    }

    public function test_unsupported_or_low_sample_rules_are_not_authorized(): void
    {
        $rows = [];
        for ($index = 0; $index < 119; $index++) {
            $rows[] = [
                'trade_date' => '2024-01-'.str_pad(
                    (string) (($index % 28) + 1),
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'ret_net' => 0.01,
                'dv20_idr' => 6000000000,
                'vol_ratio' => 3.0,
            ];
        }

        $analysis = (new WeeklySwingSignalQualityQ01DiagnosticService())
            ->analyzeEvidence($rows, 498);

        $this->assertSame([], $analysis['candidate_design_allowed']);
        foreach ($analysis['candidate_diagnostics'] as $candidate) {
            $this->assertFalse($candidate['candidate_design_authorized']);
            $this->assertFalse(
                $candidate['authorization_gates']['minimum_trade_count']
            );
        }
    }

    public function test_service_never_routes_oos_or_mutates_closed_scopes(): void
    {
        $service = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/WeeklySwingSignalQualityQ01DiagnosticService.php'
        ));
        $command = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/RunWeeklySwingSignalQualityQ01DiagnosticCommand.php'
        ));

        $this->assertStringContainsString('WS_SIGNAL_QUALITY_Q01', $service);
        $this->assertStringContainsString("'p01_reopened' => false", $service);
        $this->assertStringContainsString("'oos_table_read' => false", $service);
        $this->assertStringContainsString(
            "'official_is_runtime_invoked' => false",
            $service
        );
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOos', $service);
        $this->assertStringContainsString(
            'watchlist:weekly-swing-signal-quality-q01-diagnostic',
            $command
        );
    }
}
