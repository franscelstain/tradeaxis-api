<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01DiagnosticService;
use TestCase;

class WeeklySwingPriceQualityP01DiagnosticTest extends TestCase
{
    public function test_it_uses_only_the_three_predeclared_price_thresholds(): void
    {
        $rows = [];
        for ($index = 0; $index < 180; $index++) {
            if ($index < 30) {
                $price = 20;
                $return = -0.10;
            } elseif ($index < 40) {
                $price = 75;
                $return = 0.01;
            } elseif ($index < 50) {
                $price = 150;
                $return = 0.01;
            } else {
                $price = 250;
                $return = 0.01;
            }
            $rows[] = [
                'trade_date' => (new \DateTimeImmutable('2023-01-02'))
                    ->modify('+'.$index.' days')
                    ->format('Y-m-d'),
                'ret_net' => $return,
                'signal_close_price' => $price,
            ];
        }

        $analysis = (new WeeklySwingPriceQualityP01DiagnosticService())
            ->analyzeEvidence($rows, 496);

        $this->assertSame(
            [50, 100, 200],
            array_column($analysis['candidate_diagnostics'], 'threshold')
        );
        $this->assertSame(
            [
                'P01_C1_MIN_SIGNAL_PRICE_50',
                'P01_C2_MIN_SIGNAL_PRICE_100',
                'P01_C3_MIN_SIGNAL_PRICE_200',
            ],
            array_column($analysis['candidate_diagnostics'], 'candidate_code')
        );
        $this->assertCount(3, $analysis['candidate_design_allowed']);
        $this->assertSame(496, $analysis['source_metrics']['days_covered']);
        $this->assertSame(
            'SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN',
            $analysis['pre_registered_hypothesis']['diagnostic_status']
        );
        foreach ($analysis['candidate_diagnostics'] as $candidate) {
            $this->assertTrue($candidate['candidate_design_authorized']);
            $this->assertTrue($candidate['one_primary_idea']);
            $this->assertFalse($candidate['future_return_as_runtime_input']);
            $this->assertFalse($candidate['entry_gap_as_runtime_input']);
            $this->assertFalse($candidate['ticker_blacklist_used']);
            $this->assertFalse($candidate['month_blacklist_used']);
            $this->assertFalse($candidate['oos_used']);
            $this->assertFalse($candidate['canonical_gates_changed']);
        }
    }

    public function test_low_sample_locked_threshold_is_not_authorized(): void
    {
        $rows = [];
        for ($index = 0; $index < 119; $index++) {
            $rows[] = [
                'trade_date' => '2024-01-'.str_pad((string) (($index % 28) + 1), 2, '0', STR_PAD_LEFT),
                'ret_net' => 0.01,
                'signal_close_price' => 250,
            ];
        }

        $analysis = (new WeeklySwingPriceQualityP01DiagnosticService())
            ->analyzeEvidence($rows, 496);

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
            'app/Application/Watchlist/Services/WeeklySwingPriceQualityP01DiagnosticService.php'
        ));
        $command = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/RunWeeklySwingPriceQualityP01DiagnosticCommand.php'
        ));

        $this->assertStringContainsString('WS_PRICE_QUALITY_P01', $service);
        $this->assertStringContainsString("'c171_reopened' => false", $service);
        $this->assertStringContainsString("'r02_reopened' => false", $service);
        $this->assertStringContainsString("'s01_reopened' => false", $service);
        $this->assertStringContainsString("'oos_table_read' => false", $service);
        $this->assertStringContainsString(
            "'official_is_runtime_invoked' => false",
            $service
        );
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOos', $service);
        $this->assertStringContainsString(
            'watchlist:weekly-swing-price-quality-p01-diagnostic',
            $command
        );
    }
}
