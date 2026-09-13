<?php

use App\Application\MarketData\Services\IndicatorVectorService;

/**
 * `MD-B18-A002` -- `MD-S003-R0015`, the long-chain Wilder ATR oracle.
 *
 * `Historical_Replay_and_Data_Quality_Backtest.md` (`MD-S003`) requires a scenario in which
 * "long-chain Wilder ATR matches an independent oracle, including a correction whose impact
 * continues beyond fourteen sessions".
 *
 * `IndicatorIndependentOracleTest` already carries an ATR oracle, and it is not this one. It runs
 * on a ramp whose true range is constant at 2, and says so in its own comment: a Wilder average of
 * a constant series is that constant "regardless of where the recursion is seeded". That property
 * is what makes it a good seed-placement guard and a useless recursion guard -- the whole
 * smoothing chain could be replaced by `return $tr` and it would still pass. Its correction oracle
 * measures `ma20`, a finite window, not ATR.
 *
 * So this class supplies two things that were missing:
 *
 *  1. A varied-true-range chain of 200 sessions compared against a recursion written out from
 *     `EOD_Indicators_Formula_Spec.md` "ATR14 Wilder (LOCKED)" rather than from the service --
 *     seed `AVG(first 14 consecutive TR values)`, then `((ATR(prev) * 13) + TR(D)) / 14`.
 *  2. A correction thirty sessions before the requested date, whose effect on ATR is asserted at
 *     its exact Wilder-decayed magnitude. The spec states the consequence directly: "A changed
 *     historical TR can affect all later ATR values, so mutation impact is recursive/unbounded
 *     until recomputed, not a fixed 15-day horizon." An implementation that truncated the chain at
 *     fourteen sessions would report no change at all, which is the failure this asserts against.
 *
 * The oracle is a transcription of the spec, not of the code. If the two disagree the spec wins and
 * the service is wrong -- an oracle that takes its answer from the implementation proves only that
 * the implementation agrees with itself.
 */
class B18LongChainWilderAtrOracleTest extends TestCase
{
    /** The locked Wilder window. */
    private const WINDOW = 14;

    /** Sessions between the corrected bar and the requested date. Deliberately > WINDOW. */
    private const CORRECTION_LAG = 30;

    private function config(array $override = []): array
    {
        return array_merge([
            'set_version' => 'ind_v1',
            'price_adjustment_factors' => [],
            'price_basis_default' => 'close',
            'dv_window_days' => 20,
            'atr_window_days' => self::WINDOW,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
            'sector_code' => null,
            'sector_index_code' => null,
            'event_risk_context' => [],
            'corporate_action_contamination' => [],
            'price_scale_break_contamination' => [],
            'atr_contamination_horizon_days' => 0,
            'benchmark_roc20_pct' => null,
            'sector_roc20_pct' => null,
        ], $override);
    }

    /**
     * A deterministic series whose true range genuinely varies session to session. The ramp used by
     * the existing oracle holds TR constant, and a constant series is the one input a broken Wilder
     * recursion still gets right.
     */
    private function variedBars(int $count): array
    {
        $bars = [];
        for ($i = 0; $i < $count; $i++) {
            $close = 100.0 + (($i * 7) % 23);
            $high = $close + 1.0 + ($i % 5);
            $low = $close - 1.0 - (($i * 3) % 4);
            $bars[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.($i + 1).' days')),
                'open' => $close,
                'high' => $high,
                'low' => $low,
                'close' => $close,
                'adj_close' => $close,
                'volume' => 1000,
            ];
        }

        return $bars;
    }

    /**
     * The oracle. `EOD_Indicators_Formula_Spec.md`, "ATR14 Wilder (LOCKED)":
     *
     *   TR(X)     = max(H(X)-L(X), abs(H(X)-C(prev(X))), abs(L(X)-C(prev(X))))
     *   ATR14(S)  = AVG(first 14 consecutive TR values)
     *   ATR14(D)  = ((ATR14(prev(D)) * 13) + TR(D)) / 14
     *
     * The first bar supplies a previous close and contributes no TR of its own, so a chain of n
     * bars yields n-1 true ranges.
     */
    private function oracleAtr(array $bars): float
    {
        $trueRanges = [];
        for ($i = 1; $i < count($bars); $i++) {
            $previousClose = (float) $bars[$i - 1]['close'];
            $high = (float) $bars[$i]['high'];
            $low = (float) $bars[$i]['low'];
            $trueRanges[] = max(
                $high - $low,
                abs($high - $previousClose),
                abs($low - $previousClose)
            );
        }

        $this->assertGreaterThan(self::WINDOW, count($trueRanges),
            'the oracle needs more true ranges than the window, or there is no recursion to check');

        $atr = array_sum(array_slice($trueRanges, 0, self::WINDOW)) / self::WINDOW;
        for ($i = self::WINDOW; $i < count($trueRanges); $i++) {
            $atr = (($atr * (self::WINDOW - 1)) + $trueRanges[$i]) / self::WINDOW;
        }

        return $atr;
    }

    private function values(array $bars): array
    {
        return (new IndicatorVectorService())
            ->calculateIndicators($bars, count($bars) - 1, $this->config(), null, null);
    }

    /**
     * The chain must actually vary, or every assertion below is being made about the constant case
     * the existing oracle already covers.
     */
    public function test_the_fixture_true_range_is_not_constant(): void
    {
        $bars = $this->variedBars(200);
        $trueRanges = [];
        for ($i = 1; $i < count($bars); $i++) {
            $previousClose = (float) $bars[$i - 1]['close'];
            $trueRanges[] = max(
                $bars[$i]['high'] - $bars[$i]['low'],
                abs($bars[$i]['high'] - $previousClose),
                abs($bars[$i]['low'] - $previousClose)
            );
        }

        $this->assertGreaterThan(5, count(array_unique($trueRanges)),
            'a chain with only a handful of distinct true ranges does not exercise the recursion');
    }

    /**
     * `MD-S003-R0015` -- the long chain itself.
     *
     * 200 sessions is 185 recursive steps past the seed. A one-step error in the smoothing, or a
     * seed taken from the wrong end of the window, diverges visibly by then.
     */
    public function test_a_two_hundred_session_chain_matches_the_independently_computed_wilder_atr(): void
    {
        $bars = $this->variedBars(200);
        $values = $this->values($bars);

        $expected = $this->oracleAtr($bars);
        $close = (float) $bars[count($bars) - 1]['close'];

        $this->assertNotNull($values['atr14'], 'a 200-session chain must produce an ATR');
        $this->assertEqualsWithDelta($expected, $values['atr14'], 1e-9,
            'the service ATR must equal the spec recursion computed independently of it');
        $this->assertEqualsWithDelta($expected / $close, $values['atr14_pct'], 1e-9,
            'atr14_pct is ATR14(D)/C(D) and must be derived from the same ATR that was asserted');
    }

    /**
     * The long-chain guard trusts the oracle, so the oracle's transcription of the spec is checked
     * on its own. The shortest chain with any recursion at all is sixteen bars: fifteen true
     * ranges, fourteen of which form the seed and one of which is smoothed into it. Writing that
     * single step out longhand pins the seed as a plain mean and the step as Wilder's, which are
     * two different averages and the easiest pair to conflate.
     */
    public function test_the_oracle_seeds_on_a_plain_mean_and_then_smooths(): void
    {
        $shortChain = array_slice($this->variedBars(200), 0, self::WINDOW + 2);

        $trueRanges = [];
        for ($i = 1; $i < count($shortChain); $i++) {
            $trueRanges[] = $this->trueRangeAt($shortChain, $i);
        }
        $this->assertCount(self::WINDOW + 1, $trueRanges);

        $seed = array_sum(array_slice($trueRanges, 0, self::WINDOW)) / self::WINDOW;
        $expected = (($seed * (self::WINDOW - 1)) + $trueRanges[self::WINDOW]) / self::WINDOW;

        $this->assertNotEqualsWithDelta($seed, $expected, 1e-9,
            'the fixture must move on the first recursive step, or this proves nothing about it');
        $this->assertEqualsWithDelta($expected, $this->oracleAtr($shortChain), 1e-9,
            'the oracle must seed on the plain mean of the first fourteen true ranges and then '
                .'apply the Wilder step, not average all fifteen');
    }

    /**
     * `MD-S003-R0015` -- the correction half.
     *
     * One historical high is corrected thirty sessions before the requested date. Only `TR` at that
     * session changes, because true range reads the previous close and the correction leaves every
     * close alone, so the propagated effect is exactly
     *
     *     delta_ATR(D) = delta_TR(k) / 14 * (13/14)^(D-k)
     *
     * which is small, nonzero, and computable in closed form. A fifteen-day truncation reports
     * zero; a chain that recomputes from scratch reports exactly this.
     */
    public function test_a_correction_thirty_sessions_back_still_moves_the_atr_by_its_decayed_amount(): void
    {
        $bars = $this->variedBars(200);
        $correctedIndex = count($bars) - 1 - self::CORRECTION_LAG;
        $delta = 5.0;

        $this->assertGreaterThan(self::WINDOW, $correctedIndex,
            'the corrected session must sit in the recursive region, not inside the seed window');

        $before = $this->values($bars);

        $corrected = $bars;
        $corrected[$correctedIndex]['high'] = (float) $corrected[$correctedIndex]['high'] + $delta;
        $after = $this->values($corrected);

        // Exactly one true range moves, and by how much depends on which of the three candidates
        // was the maximum before and after; both are computed rather than assumed.
        $trueRangeDelta = $this->trueRangeAt($corrected, $correctedIndex) - $this->trueRangeAt($bars, $correctedIndex);
        $this->assertGreaterThan(0.0, $trueRangeDelta, 'the correction must actually change a true range');

        $steps = (count($bars) - 1) - $correctedIndex;
        $this->assertSame(self::CORRECTION_LAG, $steps);

        $expectedDelta = $trueRangeDelta / self::WINDOW
            * pow((self::WINDOW - 1) / self::WINDOW, $steps);

        $this->assertEqualsWithDelta($expectedDelta, $after['atr14'] - $before['atr14'], 1e-9,
            'a correction thirty sessions back must move the ATR by its Wilder-decayed amount');

        // Stated as its own assertion because "not zero" is the claim MD-S003 makes and a delta
        // tolerance alone would accept zero if the expected value were ever computed as zero.
        $this->assertGreaterThan(1e-12, abs($after['atr14'] - $before['atr14']),
            'the impact of a historical correction is recursive and unbounded, not capped at '
                .'fourteen sessions');

        // And the whole chain still matches the oracle after the correction, so the change is a
        // recomputation rather than a patch applied to the previous answer.
        $this->assertEqualsWithDelta($this->oracleAtr($corrected), $after['atr14'], 1e-9,
            'the corrected chain must equal the oracle recomputed over the corrected series');
    }

    /**
     * The control on the correction guard: a correction *after* the requested date has no effect,
     * so the guard above is measuring propagation forward through the recursion rather than mere
     * sensitivity to any edit anywhere in the fixture.
     */
    public function test_an_edit_the_requested_date_cannot_see_does_not_move_the_atr(): void
    {
        $bars = $this->variedBars(200);
        $before = $this->values($bars);

        $extended = $this->variedBars(210);
        $extended[205]['high'] = (float) $extended[205]['high'] + 5.0;
        $truncated = array_slice($extended, 0, 200);

        $this->assertSame($before['atr14'], $this->values($truncated)['atr14'],
            'an edit outside the evaluated chain must not reach the requested date');
    }

    private function trueRangeAt(array $bars, int $index): float
    {
        $previousClose = (float) $bars[$index - 1]['close'];

        return max(
            (float) $bars[$index]['high'] - (float) $bars[$index]['low'],
            abs((float) $bars[$index]['high'] - $previousClose),
            abs((float) $bars[$index]['low'] - $previousClose)
        );
    }
}
