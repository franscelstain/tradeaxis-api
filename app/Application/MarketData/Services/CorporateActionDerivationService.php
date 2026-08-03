<?php

namespace App\Application\MarketData\Services;

use Illuminate\Support\Facades\DB;

/**
 * Turn detected price-scale breaks into corporate action records carrying an adjustment factor.
 *
 * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
 *
 * Derivation is admissible because IDX auto-rejection caps a session at roughly 20 to 35
 * percent. A larger single-session move cannot come from trading, so a corporate action is
 * the only remaining explanation and the size of the gap is the ratio.
 *
 * It proves that an event happened and how large it was. It cannot prove the event type: a
 * split, a bonus issue and a rights issue all rescale price downward identically. Derived
 * rows therefore use PRICE_RESCALE_UNCLASSIFIED rather than guessing.
 */
class CorporateActionDerivationService
{
    private const DERIVED_ACTION_TYPE = 'PRICE_RESCALE_UNCLASSIFIED';
    private const DERIVED_SOURCE_NAME = 'derived_price_scale_break';

    /**
     * Largest single-session move the exchange permits. Anything beyond this is not a
     * market move.
     */
    private const MAX_EXCHANGE_SESSION_MOVE = 0.35;

    /**
     * Below this the window is continuous for practical purposes: the distortion moves a
     * 20-day average by about a tenth of a percent, and no corporate action mechanism
     * produces a rescale that small.
     */
    private const IMMATERIAL_GAP = 0.02;

    public function derive($apply = false): array
    {
        // Explained breaks are included.
        //
        // This used to select only UNEXPLAINED breaks, on the reasoning that an explained one was
        // already handled by the action explaining it. An action says why the scale moved; it does
        // not necessarily carry the ratio. FISH's 10:1 break on 2025-09-01 and MLPT's 25:1 on
        // 2026-07-15 are each matched to a recorded STOCK_SPLIT with no factor, so nothing
        // adjusted them and nothing derived one either.
        //
        // Widening this cannot resurrect small-gap false positives. MAX_EXCHANGE_SESSION_MOVE
        // below still refuses anything inside the exchange auto-rejection band, and no detected
        // break currently sits under it.
        $breaks = DB::table('market_data_price_scale_breaks')
            ->whereIn('review_status', ['DETECTED', 'CONFIRMED'])
            ->whereNull('repaired_at')
            ->orderBy('ticker_code')
            ->orderBy('trade_date')
            ->get();

        $derived = [];
        $skipped = [];

        foreach ($breaks as $break) {
            $previousClose = (float) $break->previous_close;
            $open = (float) $break->open_price;

            if ($previousClose <= 0 || $open <= 0) {
                $skipped[] = ['ticker_code' => $break->ticker_code, 'trade_date' => $break->trade_date, 'reason' => 'non-positive price'];
                continue;
            }

            $move = abs($open - $previousClose) / $previousClose;

            if ($move <= self::MAX_EXCHANGE_SESSION_MOVE) {
                $skipped[] = [
                    'ticker_code' => $break->ticker_code,
                    'trade_date' => $break->trade_date,
                    'reason' => 'within exchange session limit, could be a real move',
                ];
                continue;
            }

            // Multiply a pre-ex-date price by this to express it on the post-event scale.
            $priceFactor = $open / $previousClose;
            $volumeFactor = 1 / $priceFactor;

            // The question is whether the window is already adjusted, not whether a row exists.
            // A recorded action with no factor leaves the series exactly as unadjusted as no
            // record at all, and that is precisely the state FISH and MLPT were in.
            $alreadyAdjusted = DB::table('market_data_corporate_actions')
                ->where('ticker_id', $break->ticker_id)
                ->where(function ($query) use ($break) {
                    $query->where('ex_date', $break->trade_date)
                        ->orWhere(function ($fallback) use ($break) {
                            $fallback->whereNull('ex_date')->where('action_date', $break->trade_date);
                        });
                })
                ->whereNotNull('price_adjustment_factor')
                ->where('price_adjustment_factor', '>', 0)
                ->where('price_adjustment_factor', '<>', 1)
                ->exists();

            if ($alreadyAdjusted) {
                $skipped[] = [
                    'ticker_code' => $break->ticker_code,
                    'trade_date' => $break->trade_date,
                    'reason' => 'already adjusted on the break date',
                ];
                continue;
            }

            $row = [
                'ticker_id' => (int) $break->ticker_id,
                'ticker_code' => $break->ticker_code,
                'trade_date' => (string) $break->trade_date,
                'price_adjustment_factor' => round($priceFactor, 10),
                'volume_adjustment_factor' => round($volumeFactor, 10),
                'inferred_ratio' => $break->inferred_ratio,
                'implied_move_pct' => round($move * 100, 2),
                'break_id' => (int) $break->price_scale_break_id,
                // When a recorded action already names the real event, fill that record in rather
                // than adding a synthetic one beside it. STOCK_SPLIT is better information than
                // PRICE_RESCALE_UNCLASSIFIED, and two rows for one event would double-count.
                'existing_action_id' => $break->matched_corporate_action_id !== null
                    ? (int) $break->matched_corporate_action_id
                    : null,
                'existing_action_type' => $break->matched_action_type,
            ];

            $derived[] = $row;

            if ($apply) {
                $this->persist($row);
            }
        }

        return ['derived' => $derived, 'skipped' => $skipped];
    }

    /**
     * Fill a recorded action with the ratio and the date the price series actually shows.
     *
     * The action already names the event — a STOCK_SPLIT is more useful than the synthetic
     * PRICE_RESCALE_UNCLASSIFIED — so its type is left alone. What it lacks is the ratio and a
     * true ex_date, and both come from the series.
     *
     * `ex_date` is written from the detected break rather than the recorded `action_date`,
     * because the adjustment resolver treats ex_date as authoritative and the recorded date is
     * frequently an announcement: MLPT's split is recorded as 2026-07-21 against a series that
     * moved on 2026-07-15.
     *
     * The continuity verdict is corrected too. Both of these actions carried a verdict reached by
     * measuring the wrong day — MLPT `GAP_AMBIGUOUS`, FISH `NO_MATERIAL_GAP` across a tenfold
     * change — and leaving that in place would keep asserting something the series disproves.
     */
    private function fillRecordedAction(array $row, string $now): void
    {
        DB::table('market_data_corporate_actions')
            ->where('corporate_action_id', $row['existing_action_id'])
            ->update([
                'ex_date' => $row['trade_date'],
                'price_adjustment_factor' => $row['price_adjustment_factor'],
                'volume_adjustment_factor' => $row['volume_adjustment_factor'],
                'ratio_from' => $row['inferred_ratio'] !== null ? 1 : null,
                'ratio_to' => $row['inferred_ratio'],
                'adjustment_source' => 'DERIVED_FROM_PRICE_SERIES',
                'adjustment_note' => 'Ratio and ex_date derived from a '.$row['implied_move_pct']
                    .'% session move on '.$row['trade_date'].'. Recorded action type retained.',
                'continuity_check_status' => 'GAP_BEYOND_EXCHANGE_BAND',
                'observed_gap_pct' => $row['implied_move_pct'],
                'source_ref' => 'price_scale_break_id='.$row['break_id'],
                'updated_at' => $now,
            ]);

        DB::table('market_data_price_scale_breaks')
            ->where('price_scale_break_id', $row['break_id'])
            ->update([
                'match_status' => 'EXPLAINED',
                'matched_action_type' => $row['existing_action_type'],
                'updated_at' => $now,
            ]);
    }

    /**
     * Check every recorded corporate action that still lacks a verdict against the price
     * series, and record what the series actually shows.
     *
     * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
     */
    public function checkRecordedActions($apply = false, $maxGapPct = null): array
    {
        $maxGapPct = ($maxGapPct === null || $maxGapPct === '') ? null : (float) $maxGapPct;

        $typesTable = config('market_data.event_risk.corporate_action_types_table', 'market_data_corporate_action_types');

        $actions = DB::table('market_data_corporate_actions as ca')
            ->leftJoin($typesTable.' as t', 't.action_type_code', '=', 'ca.action_type')
            ->whereNull('ca.price_adjustment_factor')
            ->where(function ($query) use ($maxGapPct) {
                // Actions with no verdict yet, plus ambiguous ones when an explicit gap ceiling
                // is given. GAP_AMBIGUOUS is re-checkable because its name says the question was
                // left open; settled verdicts are not revisited.
                //
                // Re-checking currently changes nothing on its own — the verdict rules are
                // unchanged — so the ceiling exists for a future rule that can actually decide
                // these, applied in tranches rather than all at once.
                $query->whereNull('ca.continuity_check_status')
                    ->orWhere(function ($ambiguous) use ($maxGapPct) {
                        if ($maxGapPct === null) {
                            // Match nothing rather than sweeping every ambiguous action into a
                            // plain re-check.
                            $ambiguous->whereRaw('1 = 0');

                            return;
                        }

                        $ambiguous->where('ca.continuity_check_status', 'GAP_AMBIGUOUS')
                            ->where('ca.observed_gap_pct', '<=', $maxGapPct);
                    });
            })
            ->where(function ($query) {
                // Only actions expected to break continuity are worth checking. The rest
                // never quarantine anything.
                $query->whereNull('t.action_type_code')
                    ->orWhere('t.price_continuity_impact', '<>', 'NONE')
                    ->orWhere('t.volume_continuity_impact', '<>', 'NONE');
            })
            ->orderBy('ca.ticker_code')
            ->orderBy('ca.action_date')
            ->select(['ca.corporate_action_id', 'ca.ticker_id', 'ca.ticker_code', 'ca.action_date', 'ca.action_type'])
            ->get();

        $tally = [
            'GAP_BEYOND_EXCHANGE_BAND' => 0,
            'GAP_AMBIGUOUS' => 0,
            'NO_MATERIAL_GAP' => 0,
            'NO_SERIES' => 0,
        ];
        $samples = [];

        foreach ($actions as $action) {
            $observed = $this->observeGap((int) $action->ticker_id, (string) $action->action_date);

            if ($observed === null) {
                $tally['NO_SERIES']++;
                continue;
            }

            $gap = $observed['gap'];

            // A release keyed on "no detected scale break in the window" was written here and
            // reverted before any indicator was recomputed.
            //
            // The reasoning was that a redenomination large enough to matter leaves a break the
            // detector finds, so an action with no nearby break is an ordinary market move. The
            // detector has a floor: min_ratio 1.7, about a 41% move. Every ambiguous action in
            // this data has a gap of 24.5% or less, entirely below it. "No break detected" there
            // means the detector never looked, not that nothing happened.
            //
            // The case that makes it concrete is the rights issue. Thirty-eight of the ambiguous
            // actions are rights issues, whose ex-date drop is a genuine discontinuity in return
            // terms and is routinely 10 to 30 percent — real, and permanently invisible to a
            // detector with a 41% floor.
            if ($gap > self::MAX_EXCHANGE_SESSION_MOVE) {
                $status = 'GAP_BEYOND_EXCHANGE_BAND';
            } elseif ($gap < self::IMMATERIAL_GAP) {
                $status = 'NO_MATERIAL_GAP';
            } else {
                $status = 'GAP_AMBIGUOUS';
            }

            $tally[$status]++;

            if (count($samples) < 12) {
                $samples[] = [
                    'ticker_code' => $action->ticker_code,
                    'action_type' => $action->action_type,
                    'action_date' => $action->action_date,
                    'ex_date' => $observed['ex_date'],
                    'gap_pct' => round($gap * 100, 2),
                    'status' => $status,
                ];
            }

            if (! $apply) {
                continue;
            }

            $update = [
                'continuity_check_status' => $status,
                'observed_gap_pct' => round($gap * 100, 6),
                'continuity_checked_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Only an unambiguous gap yields a trustworthy factor. In the ambiguous band the
            // move could be ordinary trading, and absorbing it into an adjustment would
            // corrupt genuine returns.
            if ($status === 'GAP_BEYOND_EXCHANGE_BAND') {
                $update['ex_date'] = $observed['ex_date'];
                $update['price_adjustment_factor'] = round($observed['factor'], 10);
                $update['volume_adjustment_factor'] = round(1 / $observed['factor'], 10);
                $update['adjustment_source'] = 'DERIVED_FROM_PRICE_SERIES';
                $update['adjustment_note'] = 'Derived from a '.round($gap * 100, 2).'% session move at the recorded action.';
            }

            DB::table('market_data_corporate_actions')
                ->where('corporate_action_id', $action->corporate_action_id)
                ->update($update);
        }

        return ['checked' => count($actions), 'tally' => $tally, 'samples' => $samples];
    }

    /**
     * Largest open-versus-previous-close gap in the trading days around the action date.
     *
     * A window is used because the recorded action_date does not reliably equal the ex-date;
     * RMKE's split is recorded two trading days after the price actually moved.
     */
    private function observeGap(int $tickerId, string $actionDate): ?array
    {
        $bars = DB::table('eod_bars')
            ->where('ticker_id', $tickerId)
            ->whereBetween('trade_date', [
                date('Y-m-d', strtotime($actionDate.' -6 days')),
                date('Y-m-d', strtotime($actionDate.' +6 days')),
            ])
            ->orderBy('trade_date')
            ->get(['trade_date', 'open', 'close']);

        if ($bars->count() < 2) {
            return null;
        }

        $best = null;

        for ($i = 1; $i < $bars->count(); $i++) {
            $previousClose = (float) $bars[$i - 1]->close;
            $open = (float) $bars[$i]->open;

            if ($previousClose <= 0 || $open <= 0) {
                continue;
            }

            $gap = abs($open - $previousClose) / $previousClose;

            if ($best === null || $gap > $best['gap']) {
                $best = [
                    'gap' => $gap,
                    'ex_date' => (string) $bars[$i]->trade_date,
                    'factor' => $open / $previousClose,
                ];
            }
        }

        return $best;
    }

    private function persist(array $row): void
    {
        $now = date('Y-m-d H:i:s');

        DB::transaction(function () use ($row, $now) {
            if ($row['existing_action_id'] !== null) {
                $this->fillRecordedAction($row, $now);

                return;
            }

            DB::table('market_data_corporate_actions')->updateOrInsert(
                [
                    'ticker_id' => $row['ticker_id'],
                    'action_date' => $row['trade_date'],
                    'action_type' => self::DERIVED_ACTION_TYPE,
                    'source_name' => self::DERIVED_SOURCE_NAME,
                ],
                [
                    'ticker_code' => $row['ticker_code'],
                    'ex_date' => $row['trade_date'],
                    'price_adjustment_factor' => $row['price_adjustment_factor'],
                    'volume_adjustment_factor' => $row['volume_adjustment_factor'],
                    'ratio_from' => $row['inferred_ratio'] !== null ? 1 : null,
                    'ratio_to' => $row['inferred_ratio'],
                    'adjustment_source' => 'DERIVED_FROM_PRICE_SERIES',
                    'adjustment_note' => 'Derived from a '.$row['implied_move_pct'].'% session move, beyond the exchange auto-rejection band. Type unconfirmed.',
                    'source_ref' => 'price_scale_break_id='.$row['break_id'],
                    'notes' => 'Auto-derived; operator should refine the action type.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // The break is now explained by a recorded action carrying a factor.
            DB::table('market_data_price_scale_breaks')
                ->where('price_scale_break_id', $row['break_id'])
                ->update([
                    'match_status' => 'EXPLAINED',
                    'matched_action_type' => self::DERIVED_ACTION_TYPE,
                    'updated_at' => $now,
                ]);
        });
    }
}
