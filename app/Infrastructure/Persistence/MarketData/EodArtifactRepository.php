<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class EodArtifactRepository
{
    public const REQUIRED_CANONICAL_BAR_WRITE_FIELDS = [
        'listing_id',
        'source_observation_id',
        'canonicalization_version',
        'price_product_code',
        'quality_state',
    ];

    public const REQUIRED_ELIGIBILITY_WRITE_FIELDS = [
        'universe_membership_state',
        'bar_expectation_state',
        'delivery_state',
        'canonical_quality_state',
        'liquidity_state',
        'temporal_status_state',
        'event_risk_state',
        'eligibility_reasons_json',
    ];

    private $calendar;

    public function __construct(MarketCalendarRepository $calendar = null)
    {
        $this->calendar = $calendar ?: new MarketCalendarRepository();
    }

    public function bindCandidateBarSourceScale($publicationId, $tradeDate, $listingId, array $payload): void
    {
        $query = DB::table('eod_bars_history')
            ->where('publication_id', $publicationId)
            ->where('trade_date', $tradeDate)
            ->where('listing_id', $listingId);

        if ($query->exists()) {
            $query->update($payload);
            return;
        }

        DB::table('eod_bars')
            ->where('publication_id', $publicationId)
            ->where('trade_date', $tradeDate)
            ->where('listing_id', $listingId)
            ->update($payload);
    }

    public function loadCandidateEligibilityForGovernance($publicationId, $tradeDate): array
    {
        $table = $this->candidateArtifactTable('eod_eligibility', $publicationId, $tradeDate);

        return DB::table($table.' as eligibility')
            ->leftJoin('md_listings as listing', 'listing.listing_id', '=', 'eligibility.listing_id')
            ->where('eligibility.publication_id', $publicationId)
            ->where('eligibility.trade_date', $tradeDate)
            ->select([
                'eligibility.listing_id', 'eligibility.bar_expectation_state',
                'eligibility.temporal_status_state', 'listing.board_code',
                'eligibility.trading_status_revision_id',
                'eligibility.trading_status_source_observation_id',
                'listing.recorded_at as board_identity_recorded_at',
            ])
            ->orderBy('eligibility.listing_id')
            ->get()
            ->all();
    }

    public function bindCandidateEligibilityMarketStructure($publicationId, $tradeDate, $listingId, array $payload): void
    {
        $table = $this->candidateArtifactTable('eod_eligibility', $publicationId, $tradeDate);
        DB::table($table)
            ->where('publication_id', $publicationId)
            ->where('trade_date', $tradeDate)
            ->where('listing_id', $listingId)
            ->update($payload);
    }

    public function loadPublicationArtifactSnapshot($publicationId, $tradeDate, string $artifact): array
    {
        if (! in_array($artifact, ['bars', 'indicators', 'eligibility'], true)) {
            throw new \InvalidArgumentException('Unknown market-data artifact: '.$artifact);
        }

        $table = $this->candidateArtifactTable('eod_'.$artifact, $publicationId, $tradeDate);

        return [
            'columns' => DB::connection()->getSchemaBuilder()->getColumnListing($table),
            'rows' => DB::table($table)
                ->where('publication_id', $publicationId)
                ->where('trade_date', $tradeDate)
                ->orderBy('ticker_id')
                ->get(),
        ];
    }

    private function candidateArtifactTable(string $baseTable, $publicationId, $tradeDate): string
    {
        $historyTable = $baseTable.'_history';

        return DB::table($historyTable)
            ->where('publication_id', $publicationId)
            ->where('trade_date', $tradeDate)
            ->exists()
                ? $historyTable
                : $baseTable;
    }

    public function replaceBars($tradeDate, $publicationId, $runId, array $validRows, array $invalidRows, $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $publicationId, $runId, $validRows, $invalidRows, $useHistory) {
            if (! $useHistory) {
                $this->assertLiveArtifactMutationAllowed($tradeDate, $publicationId, 'eod_bars');
            } else {
                $this->assertHistorySnapshotMutable($publicationId);
            }

            $this->assertCompleteBarRows($validRows, 'replaceBars');
            $mutationSummary = $this->buildBarsMutationSummary($tradeDate, $publicationId, $validRows, $useHistory);

            if ($useHistory) {
                // The live branch has been guarded all along; this one was not. A snapshot set is
                // assembled while its publication is still a candidate and frozen at the seal
                // transition, so rewriting it before that point is legitimate and rewriting it
                // after is exactly what rule 9 of the history policy forbids.
                DB::table('eod_bars_history')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $publicationId)
                    ->delete();
            } else {
                DB::table('eod_bars')
                    ->where('trade_date', $tradeDate)
                    ->delete();
            }

            DB::table('eod_invalid_bars')
                ->where('trade_date', $tradeDate)
                ->where('run_id', $runId)
                ->delete();

            if (! empty($validRows)) {
                DB::table($useHistory ? 'eod_bars_history' : 'eod_bars')->insert($validRows);
            }

            if (! empty($invalidRows)) {
                DB::table('eod_invalid_bars')->insert($invalidRows);
            }

            return $mutationSummary;
        });
    }

    public function persistInvalidBars($tradeDate, $runId, array $invalidRows)
    {
        DB::transaction(function () use ($tradeDate, $runId, $invalidRows) {
            DB::table('eod_invalid_bars')
                ->where('trade_date', $tradeDate)
                ->where('run_id', $runId)
                ->delete();

            if ($invalidRows !== []) {
                DB::table('eod_invalid_bars')->insert($invalidRows);
            }
        });
    }

    public function upsertBarsPartial($tradeDate, $publicationId, $runId, array $validRows, array $invalidRows = [], $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $publicationId, $runId, $validRows, $invalidRows, $useHistory) {
            if (! $useHistory) {
                $this->assertLiveArtifactMutationAllowed($tradeDate, $publicationId, 'eod_bars');
            } else {
                $this->assertHistorySnapshotMutable($publicationId);
            }

            $this->assertCompleteBarRows($validRows, 'upsertBarsPartial');
            $mutationSummary = $this->buildBarsMutationSummary($tradeDate, $publicationId, $validRows, $useHistory, false);
            $table = $useHistory ? 'eod_bars_history' : 'eod_bars';
            $writeTickerIds = array_fill_keys(array_merge(
                (array) ($mutationSummary['inserted_ticker_ids'] ?? []),
                (array) ($mutationSummary['updated_ticker_ids'] ?? [])
            ), true);

            foreach ($validRows as $row) {
                if (! isset($row['ticker_id'])) {
                    continue;
                }

                if (! isset($writeTickerIds[(int) $row['ticker_id']])) {
                    continue;
                }

                $keys = [
                    'trade_date' => $tradeDate,
                    'ticker_id' => (int) $row['ticker_id'],
                ];

                if ($useHistory) {
                    $keys['publication_id'] = $publicationId;
                }

                DB::table($table)->updateOrInsert($keys, [
                    'publication_id' => $publicationId,
                    'open' => $row['open'],
                    'high' => $row['high'],
                    'low' => $row['low'],
                    'close' => $row['close'],
                    'volume' => $row['volume'],
                    'adj_close' => $row['adj_close'],
                    'source' => $row['source'],
                    'run_id' => $runId,
                    'created_at' => $row['created_at'],
                ] + $this->barLineage($row));
            }

            DB::table('eod_invalid_bars')
                ->where('trade_date', $tradeDate)
                ->where('run_id', $runId)
                ->delete();

            if (! empty($invalidRows)) {
                DB::table('eod_invalid_bars')->insert($invalidRows);
            }

            return $mutationSummary;
        });
    }

    public function loadAvailableBarTradeDatesOnOrAfter($startDate)
    {
        return DB::table('eod_bars')
            ->select('trade_date')
            ->where('trade_date', '>=', $startDate)
            ->distinct()
            ->orderBy('trade_date')
            ->pluck('trade_date')
            ->map(function ($value) {
                return (string) $value;
            })
            ->values()
            ->all();
    }


    public function ensureBarsHistoryFromCurrentTradeDate($tradeDate, $publicationId, $runId)
    {
        if ($publicationId === null || $publicationId === '') {
            return;
        }

        if (DB::table('eod_bars_history')
            ->where('trade_date', $tradeDate)
            ->where('publication_id', $publicationId)
            ->exists()) {
            return;
        }

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $bars = $this->applyStableArtifactOrder(
            DB::table('eod_bars')->where('trade_date', $tradeDate)
        )->get();

        $insert = [];
        foreach ($bars as $row) {
            $insert[] = [
                'publication_id' => $publicationId,
                'trade_date' => $row->trade_date,
                'ticker_id' => $row->ticker_id,
                'open' => $row->open,
                'high' => $row->high,
                'low' => $row->low,
                'close' => $row->close,
                'volume' => $row->volume,
                'adj_close' => $row->adj_close,
                'source' => $row->source,
                'run_id' => $runId,
                'created_at' => $now,
            ] + $this->barLineage($row);
        }

        $this->assertCompleteBarRows($insert, 'ensureBarsHistoryFromCurrentTradeDate');

        if (! empty($insert)) {
            DB::table('eod_bars_history')->insert($insert);
        }
    }

    public function replaceBarsHistoryFromPublication($tradeDate, $sourcePublicationId, $targetPublicationId, $runId)
    {
        if ($sourcePublicationId === null || $sourcePublicationId === '' || $targetPublicationId === null || $targetPublicationId === '') {
            return;
        }

        $sourcePublicationId = (int) $sourcePublicationId;
        $targetPublicationId = (int) $targetPublicationId;

        if ($sourcePublicationId === $targetPublicationId) {
            return;
        }

        DB::transaction(function () use ($tradeDate, $sourcePublicationId, $targetPublicationId, $runId) {
            $sourceRows = $this->applyStableArtifactOrder(
                DB::table('eod_bars_history')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $sourcePublicationId)
            )->get();

            if ($sourceRows->isEmpty()) {
                $sourceRows = $this->applyStableArtifactOrder(
                    DB::table('eod_bars')
                        ->where('trade_date', $tradeDate)
                        ->where('publication_id', $sourcePublicationId)
                )->get();
            }

            if ($sourceRows->isEmpty()) {
                return;
            }

            $this->assertHistorySnapshotMutable($targetPublicationId);

            $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
            $insert = [];
            foreach ($sourceRows as $row) {
                $insert[] = [
                    'publication_id' => $targetPublicationId,
                    'trade_date' => $row->trade_date,
                    'ticker_id' => $row->ticker_id,
                    'open' => $row->open,
                    'high' => $row->high,
                    'low' => $row->low,
                    'close' => $row->close,
                    'volume' => $row->volume,
                    'adj_close' => $row->adj_close,
                    'source' => $row->source,
                    'run_id' => $runId,
                    'created_at' => $now,
                ] + $this->barLineage($row);
            }

            $this->assertCompleteBarRows($insert, 'replaceBarsHistoryFromPublication');

            DB::table('eod_bars_history')
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $targetPublicationId)
                ->delete();

            DB::table('eod_bars_history')->insert($insert);
        });
    }

    /**
     * Load one ticker's true-range inputs from the dataset boundary to the requested date.
     *
     * Wilder ATR is a recursive filter, so its value depends on where the recursion was seeded.
     * Seeding it at the start of a sliding load window gives each date its own seed and produces a
     * series that is not a Wilder ATR at all, but a sequence of independently seeded approximations
     * — measured on 120 production tickers at 2026-07-28, the median divergence from the
     * boundary-seeded value was 0.34%, but the 90th percentile was 1.62% and the worst was 72.9%.
     *
     * Only the four fields true range needs are selected. Scoping the query to one ticker keeps
     * the worker's peak memory bounded even when the complete canonical corpus is large.
     */
    public function loadAtrSeriesForTickerFromBoundary($tickerId, $tradeDate, $boundaryDate, $requestedPublicationId = null)
    {
        $query = DB::table('eod_bars')
            ->select(['trade_date', 'high', 'low', 'close'])
            ->where('ticker_id', (int) $tickerId)
            ->whereBetween('trade_date', [$boundaryDate, $tradeDate]);

        // Correction candidates read their requested-date bar from immutable candidate history,
        // matching loadBarsWindow(). Earlier dates remain current canonical inputs.
        if ($requestedPublicationId !== null && $requestedPublicationId !== '') {
            $query->where('trade_date', '<>', $tradeDate);
        }

        $rows = $query
            ->orderBy('trade_date')
            ->get();

        if ($requestedPublicationId !== null && $requestedPublicationId !== '') {
            $historyRow = DB::table('eod_bars_history')
                ->select(['trade_date', 'high', 'low', 'close'])
                ->where('publication_id', (int) $requestedPublicationId)
                ->where('ticker_id', (int) $tickerId)
                ->where('trade_date', $tradeDate)
                ->first();

            if ($historyRow !== null) {
                $rows->push($historyRow);
            }
        }

        return $rows
            ->sortBy('trade_date')
            ->values()
            ->map(function ($row) {
                return [
                    'trade_date' => (string) $row->trade_date,
                    'high' => $row->high,
                    'low' => $row->low,
                    'close' => $row->close,
                ];
            })
            ->all();
    }

    public function loadBarsWindow($tradeDate, $lookbackDays, $requestedPublicationId = null, $historyStartDate = null)
    {
        $startDate = $this->calendar->tradingDateWindowStart($tradeDate, $lookbackDays);
        if ($historyStartDate !== null && $startDate < $historyStartDate) {
            $startDate = $historyStartDate;
        }

        $rows = $this->applyStableArtifactOrder(
            DB::table('eod_bars')->whereBetween('trade_date', [$startDate, $tradeDate])
        )
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();

        if ($requestedPublicationId) {
            $rows = array_values(array_filter($rows, function ($row) use ($tradeDate) {
                return (string) $row['trade_date'] !== (string) $tradeDate;
            }));

            $historyRows = $this->applyStableArtifactOrder(
                DB::table('eod_bars_history')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $requestedPublicationId)
            )
                ->get()
                ->map(function ($row) {
                    return (array) $row;
                })
                ->all();

            $rows = array_merge($rows, $historyRows);
        }

        return collect($rows)
            ->groupBy('ticker_id')
            ->map(function ($group) {
                return collect($group)
                    ->sortBy(function ($row) {
                        return sprintf('%s|%010d', (string) $row['trade_date'], (int) $row['ticker_id']);
                    })
                    ->values()
                    ->all();
            })
            ->all();
    }

    public function replaceIndicators($tradeDate, $runId, array $rows, $publicationId = null, $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $rows, $publicationId, $useHistory) {
            if (! $useHistory) {
                $this->assertLiveArtifactMutationAllowed($tradeDate, $publicationId, 'eod_indicators');
            }

            $table = $useHistory ? 'eod_indicators_history' : 'eod_indicators';
            $query = DB::table($table)->where('trade_date', $tradeDate);

            if ($useHistory) {
                $query->where('publication_id', $publicationId);
            }

            $query->delete();

            if (! empty($rows)) {
                DB::table($table)->insert($rows);
            }
        });
    }

    /**
     * Ticker ids that produced no *traded* canonical bar in the window ending at $tradeDate.
     *
     * A ticker counts as active only if it has at least one bar with positive volume. Bars
     * with zero volume record a price nobody transacted at, which is worth nothing to a
     * trading system and cannot be bought or sold.
     *
     * Owner contract: docs/market_data/book/Coverage_Universe_Definition_LOCKED.md
     *
     * These are no longer expected to produce a bar, so counting them as missing measures
     * nothing and permanently erodes the coverage ratio.
     *
     * @return array<int, int>
     */
    public function loadDormantTickerIds(array $tickerIds, $tradeDate, $lookbackTradingDays)
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        $tickerIds = array_values(array_filter($tickerIds, function ($tickerId) {
            return $tickerId > 0;
        }));

        if (empty($tickerIds) || (int) $lookbackTradingDays < 1) {
            return [];
        }

        $windowStart = $this->calendar->tradingDateWindowStart($tradeDate, (int) $lookbackTradingDays);

        // Positive volume, not mere presence. A provider may carry a stale price forward with
        // volume 0 for months after a ticker stops trading; the eleven tickers that vanished
        // on 2026-07-17 had zero volume on all 120 of their final bars. Traded volume is a
        // market fact, while the existence of a bar is a provider behaviour.
        $active = DB::table('eod_bars')
            ->whereIn('ticker_id', $tickerIds)
            ->where('trade_date', '>=', $windowStart)
            ->where('trade_date', '<=', $tradeDate)
            ->where('volume', '>', 0)
            ->distinct()
            ->pluck('ticker_id')
            ->map(function ($value) {
                return (int) $value;
            })
            ->all();

        $activeSet = array_fill_keys($active, true);

        $dormant = array_values(array_filter($tickerIds, function ($tickerId) use ($activeSet) {
            return ! isset($activeSet[$tickerId]);
        }));

        sort($dormant);

        return $dormant;
    }

    public function loadIndicatorsForTradeDate($tradeDate, $requestedPublicationId = null)
    {
        $table = $requestedPublicationId ? 'eod_indicators_history' : 'eod_indicators';
        $query = DB::table($table)->where('trade_date', $tradeDate);

        if ($requestedPublicationId) {
            $query->where('publication_id', $requestedPublicationId);
        }

        return $this->applyStableArtifactOrder($query)
            ->get()
            ->keyBy('ticker_id')
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }

    public function loadBarsForTradeDate($tradeDate, $requestedPublicationId = null)
    {
        $table = $requestedPublicationId ? 'eod_bars_history' : 'eod_bars';
        $query = DB::table($table)->where('trade_date', $tradeDate);

        if ($requestedPublicationId) {
            $query->where('publication_id', $requestedPublicationId);
        }

        return $this->applyStableArtifactOrder($query)
            ->get()
            ->keyBy('ticker_id')
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }


    public function loadCanonicalBarTickerIdsForTradeDate($tradeDate, $requestedPublicationId = null)
    {
        if ($requestedPublicationId !== null && $requestedPublicationId !== '') {
            return $this->loadCandidateScopedBarTickerIdsForTradeDate($tradeDate, $requestedPublicationId);
        }

        return array_map('intval', array_keys($this->loadBarsForTradeDate($tradeDate, null)));
    }

    public function loadDeliveredObservationTickerIdsForTradeDate($tradeDate, $runId, $requestedPublicationId = null)
    {
        $tickerIds = array_fill_keys(
            $this->loadCanonicalBarTickerIdsForTradeDate($tradeDate, $requestedPublicationId),
            true
        );

        $invalid = DB::table('eod_invalid_bars as invalid')
            ->join('md_source_observations as observation', 'observation.source_observation_id', '=', 'invalid.source_observation_id')
            ->where('invalid.trade_date', $tradeDate)
            ->where('invalid.run_id', (int) $runId)
            ->whereNotNull('invalid.ticker_id')
            ->whereIn('observation.outcome_state', ['ACCEPTED', 'NORMALIZED'])
            ->pluck('invalid.ticker_id')
            ->all();

        foreach ($invalid as $tickerId) {
            $tickerIds[(int) $tickerId] = true;
        }

        ksort($tickerIds);

        return array_keys($tickerIds);
    }

    private function loadCandidateScopedBarTickerIdsForTradeDate($tradeDate, $publicationId)
    {
        $publicationId = (int) $publicationId;
        $tickerIds = [];

        foreach (['eod_bars_history', 'eod_bars'] as $table) {
            $rows = DB::table($table)
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $publicationId)
                ->pluck('ticker_id')
                ->all();

            foreach ($rows as $tickerId) {
                $tickerIds[(int) $tickerId] = true;
            }
        }

        ksort($tickerIds);

        return array_keys($tickerIds);
    }

    public function replaceEligibility($tradeDate, $runId, array $rows, $publicationId = null, $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $rows, $publicationId, $useHistory) {
            if (! $useHistory) {
                $this->assertLiveArtifactMutationAllowed($tradeDate, $publicationId, 'eod_eligibility');
            }

            $this->assertCompleteEligibilityRows($rows, 'replaceEligibility');

            $table = $useHistory ? 'eod_eligibility_history' : 'eod_eligibility';
            $query = DB::table($table)->where('trade_date', $tradeDate);

            if ($useHistory) {
                $query->where('publication_id', $publicationId);
            }

            $query->delete();

            if (! empty($rows)) {
                DB::table($table)->insert($rows);
            }
        });
    }

    public function historySnapshotExists($publicationId)
    {
        return DB::table('eod_bars_history')->where('publication_id', $publicationId)->exists()
            && DB::table('eod_indicators_history')->where('publication_id', $publicationId)->exists()
            && DB::table('eod_eligibility_history')->where('publication_id', $publicationId)->exists();
    }

    public function snapshotPublicationFromCurrentTables($tradeDate, $publicationId, $runId)
    {
        return DB::transaction(function () use ($tradeDate, $publicationId, $runId) {
            $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        if (! DB::table('eod_bars_history')->where('publication_id', $publicationId)->exists()) {
            $bars = $this->applyStableArtifactOrder(
                DB::table('eod_bars')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $publicationId)
            )->get();

            $insert = [];
            foreach ($bars as $row) {
                $insert[] = [
                    'publication_id' => $publicationId,
                    'trade_date' => $row->trade_date,
                    'ticker_id' => $row->ticker_id,
                    'open' => $row->open,
                    'high' => $row->high,
                    'low' => $row->low,
                    'close' => $row->close,
                    'volume' => $row->volume,
                    'adj_close' => $row->adj_close,
                    'source' => $row->source,
                    'run_id' => $runId,
                    'created_at' => $now,
                ] + $this->barLineage($row);
            }

            $this->assertCompleteBarRows($insert, 'snapshotPublicationFromCurrentTables');

            if (! empty($insert)) {
                DB::table('eod_bars_history')->insert($insert);
            }
        }

        if (! DB::table('eod_indicators_history')->where('publication_id', $publicationId)->exists()) {
            $indicators = $this->applyStableArtifactOrder(
                DB::table('eod_indicators')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $publicationId)
            )->get();

            $insert = [];
            foreach ($indicators as $row) {
                $insert[] = [
                    'publication_id' => $publicationId,
                    'trade_date' => $row->trade_date,
                    'ticker_id' => $row->ticker_id,
                    'is_valid' => $row->is_valid,
                    'invalid_reason_code' => $row->invalid_reason_code,
                    'indicator_set_version' => $row->indicator_set_version,
                    'sector_code' => $row->sector_code,
                    'dv20_idr' => $row->dv20_idr,
                    'atr14_pct' => $row->atr14_pct,
                    'vol_ratio' => $row->vol_ratio,
                    'roc5' => $row->roc5,
                    'roc10' => $row->roc10,
                    'roc20' => $row->roc20,
                    'hh20' => $row->hh20,
                    'll20' => $row->ll20,
                    'ma20' => $row->ma20,
                    'ma50' => $row->ma50,
                    'close_to_hh20_pct' => $row->close_to_hh20_pct,
                    'close_to_ll20_pct' => $row->close_to_ll20_pct,
                    'range_20_pct' => $row->range_20_pct,
                    'range_position_20_pct' => $row->range_position_20_pct,
                    'close_vs_ma20_pct' => $row->close_vs_ma20_pct,
                    'close_vs_ma50_pct' => $row->close_vs_ma50_pct,
                    'ma20_slope_pct' => $row->ma20_slope_pct,
                    'rs_20_vs_ihsg' => $row->rs_20_vs_ihsg,
                    'sector_roc20' => $row->sector_roc20,
                    'rs_20_vs_sector' => $row->rs_20_vs_sector,
                    'sector_rs_20_vs_ihsg' => $row->sector_rs_20_vs_ihsg,
                    'corporate_action_flag' => $row->corporate_action_flag,
                    'corporate_action_types' => $row->corporate_action_types,
                    'trading_status_code' => $row->trading_status_code,
                    'is_suspended' => $row->is_suspended,
                    'is_uma' => $row->is_uma,
                    'event_risk_flag' => $row->event_risk_flag,
                    'event_risk_reasons' => $row->event_risk_reasons,
                    'corporate_action_window_reasons' => $row->corporate_action_window_reasons,
                    'run_id' => $runId,
                    'created_at' => $now,
                ] + $this->indicatorLineage($row);
            }

            if (! empty($insert)) {
                DB::table('eod_indicators_history')->insert($insert);
            }
        }

        if (! DB::table('eod_eligibility_history')->where('publication_id', $publicationId)->exists()) {
            $eligibility = $this->applyStableArtifactOrder(
                DB::table('eod_eligibility')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $publicationId)
            )->get();

            $insert = [];
            foreach ($eligibility as $row) {
                $insert[] = [
                    'publication_id' => $publicationId,
                    'trade_date' => $row->trade_date,
                    'ticker_id' => $row->ticker_id,
                    'eligible' => $row->eligible,
                    'reason_code' => $row->reason_code,
                    'run_id' => $runId,
                    'created_at' => $now,
                ] + $this->eligibilityFacts($row);
            }

            $this->assertCompleteEligibilityRows($insert, 'snapshotPublicationFromCurrentTables');

            if (! empty($insert)) {
                DB::table('eod_eligibility_history')->insert($insert);
            }
            }
        });
    }

    public function promotePublicationHistoryToCurrent($tradeDate, $publicationId, $runId)
    {
        return DB::transaction(function () use ($tradeDate, $publicationId, $runId) {
            $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

            $bars = $this->applyStableArtifactOrder(
            DB::table('eod_bars_history')
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $publicationId)
        )->get();

            $insert = [];
            foreach ($bars as $row) {
                $insert[] = [
                'trade_date' => $row->trade_date,
                'ticker_id' => $row->ticker_id,
                'open' => $row->open,
                'high' => $row->high,
                'low' => $row->low,
                'close' => $row->close,
                'volume' => $row->volume,
                'adj_close' => $row->adj_close,
                'source' => $row->source,
                'run_id' => $runId,
                'publication_id' => $publicationId,
                'created_at' => $now,
                ] + $this->barLineage($row);
            }

            $this->assertCompleteBarRows($insert, 'promotePublicationHistoryToCurrent');

            DB::table('eod_bars')->where('trade_date', $tradeDate)->delete();

            if (! empty($insert)) {
                DB::table('eod_bars')->insert($insert);
            }

        DB::table('eod_indicators')->where('trade_date', $tradeDate)->delete();

        $indicators = $this->applyStableArtifactOrder(
            DB::table('eod_indicators_history')
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $publicationId)
        )->get();

        $insert = [];
        foreach ($indicators as $row) {
            $insert[] = [
                'trade_date' => $row->trade_date,
                'ticker_id' => $row->ticker_id,
                'is_valid' => $row->is_valid,
                'invalid_reason_code' => $row->invalid_reason_code,
                'indicator_set_version' => $row->indicator_set_version,
                'sector_code' => $row->sector_code,
                'dv20_idr' => $row->dv20_idr,
                'atr14_pct' => $row->atr14_pct,
                'vol_ratio' => $row->vol_ratio,
                'roc5' => $row->roc5,
                'roc10' => $row->roc10,
                'roc20' => $row->roc20,
                'hh20' => $row->hh20,
                'll20' => $row->ll20,
                'ma20' => $row->ma20,
                'ma50' => $row->ma50,
                'close_to_hh20_pct' => $row->close_to_hh20_pct,
                'close_to_ll20_pct' => $row->close_to_ll20_pct,
                'range_20_pct' => $row->range_20_pct,
                'range_position_20_pct' => $row->range_position_20_pct,
                'close_vs_ma20_pct' => $row->close_vs_ma20_pct,
                'close_vs_ma50_pct' => $row->close_vs_ma50_pct,
                'ma20_slope_pct' => $row->ma20_slope_pct,
                'rs_20_vs_ihsg' => $row->rs_20_vs_ihsg,
                'sector_roc20' => $row->sector_roc20,
                'rs_20_vs_sector' => $row->rs_20_vs_sector,
                'sector_rs_20_vs_ihsg' => $row->sector_rs_20_vs_ihsg,
                'corporate_action_flag' => $row->corporate_action_flag,
                'corporate_action_types' => $row->corporate_action_types,
                'trading_status_code' => $row->trading_status_code,
                'is_suspended' => $row->is_suspended,
                'is_uma' => $row->is_uma,
                'event_risk_flag' => $row->event_risk_flag,
                'event_risk_reasons' => $row->event_risk_reasons,
                'corporate_action_window_reasons' => $row->corporate_action_window_reasons,
                'run_id' => $runId,
                'publication_id' => $publicationId,
                'created_at' => $now,
            ] + $this->indicatorLineage($row);
        }

        if (! empty($insert)) {
            DB::table('eod_indicators')->insert($insert);
        }

            $elig = $this->applyStableArtifactOrder(
            DB::table('eod_eligibility_history')
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $publicationId)
        )->get();

            $insert = [];
            foreach ($elig as $row) {
                $insert[] = [
                'trade_date' => $row->trade_date,
                'ticker_id' => $row->ticker_id,
                'eligible' => $row->eligible,
                'reason_code' => $row->reason_code,
                'run_id' => $runId,
                'publication_id' => $publicationId,
                    'created_at' => $now,
                ] + $this->eligibilityFacts($row);
            }

            $this->assertCompleteEligibilityRows($insert, 'promotePublicationHistoryToCurrent');

            DB::table('eod_eligibility')->where('trade_date', $tradeDate)->delete();

            if (! empty($insert)) {
                DB::table('eod_eligibility')->insert($insert);
            }
        });
    }

    /**
     * Refuse to rewrite a snapshot set whose publication has been sealed.
     *
     * A publication that does not exist yet is not an error here: the candidate row is created in
     * the same flow, and a missing publication cannot be sealed. Only a confirmed seal blocks.
     */
    protected function assertHistorySnapshotMutable($publicationId)
    {
        if ($publicationId === null || $publicationId === '') {
            return;
        }

        $sealState = DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->value('seal_state');

        if ((string) $sealState === 'SEALED') {
            throw new \RuntimeException(
                'SEALED_SNAPSHOT_REWRITE_BLOCKED: publication '.$publicationId.' is sealed; its history snapshot set is frozen. '
                .'Produce a new corrected publication instead of rewriting this one.'
            );
        }
    }

    protected function assertLiveArtifactMutationAllowed($tradeDate, $publicationId, $artifactTable)
    {
        $query = DB::table($artifactTable.' as artifact')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'artifact.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('artifact.trade_date', $tradeDate)
            ->where('pub.seal_state', 'SEALED')
            ->where(function ($query) {
                $query->where('pub.is_current', 1)
                    ->orWhere(function ($query) {
                        $query->where('run.terminal_status', 'SUCCESS')
                            ->where('run.publishability_state', 'READABLE');
                    });
            });

        if ($publicationId !== null && $publicationId !== '') {
            $query->where('artifact.publication_id', '<>', $publicationId);
        }

        if ($query->exists()) {
            throw new \RuntimeException('SEALED_DATASET_MUTATION_BLOCKED: '.$artifactTable.' for trade date '.$tradeDate.' is already sealed/finalized/readable. Use correction history flow instead of mutating the baseline dataset.');
        }
    }

    protected function applyStableArtifactOrder(Builder $query): Builder
    {
        return $query
            ->orderBy('trade_date')
            ->orderBy('ticker_id');
    }

    private function buildBarsMutationSummary($tradeDate, $publicationId, array $validRows, $useHistory, $includeRemoved = true)
    {
        $existing = [];
        foreach ($this->existingBarsForMutationSummary($tradeDate, $publicationId, $useHistory) as $row) {
            $row = (array) $row;
            $existing[(int) $row['ticker_id']] = $row;
        }

        $incoming = [];
        foreach ($validRows as $row) {
            if (! isset($row['ticker_id'])) {
                continue;
            }

            $incoming[(int) $row['ticker_id']] = $row;
        }

        $inserted = [];
        $updated = [];
        $unchanged = [];

        foreach ($incoming as $tickerId => $row) {
            if (! isset($existing[$tickerId])) {
                $inserted[] = $tickerId;
                continue;
            }

            if ($this->canonicalBarHash($existing[$tickerId]) !== $this->canonicalBarHash($row)) {
                $updated[] = $tickerId;
                continue;
            }

            $unchanged[] = $tickerId;
        }

        $removed = $includeRemoved ? array_values(array_diff(array_keys($existing), array_keys($incoming))) : [];
        $changedTickerIds = array_values(array_unique(array_merge($inserted, $updated, $removed)));
        sort($changedTickerIds);
        sort($inserted);
        sort($updated);
        sort($unchanged);
        sort($removed);

        $changedTradeDates = $changedTickerIds === [] ? [] : [(string) $tradeDate];

        return [
            'changed_bar_count' => count($changedTickerIds),
            'inserted_bar_count' => count($inserted),
            'updated_bar_count' => count($updated),
            'unchanged_bar_count' => count($unchanged),
            'removed_bar_count' => count($removed),
            'changed_ticker_count' => count($changedTickerIds),
            'changed_trade_date_count' => count($changedTradeDates),
            'changed_ticker_ids' => $changedTickerIds,
            'inserted_ticker_ids' => $inserted,
            'updated_ticker_ids' => $updated,
            'removed_ticker_ids' => $removed,
            'changed_trade_dates' => $changedTradeDates,
            'storage_target' => $useHistory ? 'eod_bars_history' : 'eod_bars',
            'trade_date' => (string) $tradeDate,
            'publication_id' => $publicationId !== null ? (int) $publicationId : null,
            'mutation_detection_version' => 'eod_bar_mutation_v1',
        ];
    }

    private function existingBarsForMutationSummary($tradeDate, $publicationId, $useHistory)
    {
        if (! $useHistory) {
            return $this->applyStableArtifactOrder(
                DB::table('eod_bars')->where('trade_date', $tradeDate)
            )->get();
        }

        $supersedesPublicationId = $this->supersededPublicationIdForCandidate($publicationId);
        if ($supersedesPublicationId !== null) {
            $historyBaseline = $this->applyStableArtifactOrder(
                DB::table('eod_bars_history')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $supersedesPublicationId)
            )->get();

            if ($historyBaseline->count() > 0) {
                return $historyBaseline;
            }

            $liveBaseline = $this->applyStableArtifactOrder(
                DB::table('eod_bars')
                    ->where('trade_date', $tradeDate)
                    ->where('publication_id', $supersedesPublicationId)
            )->get();

            if ($liveBaseline->count() > 0) {
                return $liveBaseline;
            }
        }

        return $this->applyStableArtifactOrder(
            DB::table('eod_bars_history')
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $publicationId)
        )->get();
    }

    private function supersededPublicationIdForCandidate($publicationId)
    {
        if ($publicationId === null || $publicationId === '') {
            return null;
        }

        $publication = DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->first();

        if (! $publication || empty($publication->supersedes_publication_id)) {
            return null;
        }

        return (int) $publication->supersedes_publication_id;
    }

    private function canonicalBarHash(array $row)
    {
        return hash('sha256', json_encode([
            'open' => $this->normalizeBarNumber($row['open'] ?? null),
            'high' => $this->normalizeBarNumber($row['high'] ?? null),
            'low' => $this->normalizeBarNumber($row['low'] ?? null),
            'close' => $this->normalizeBarNumber($row['close'] ?? null),
            'volume' => $this->normalizeBarNumber($row['volume'] ?? null),
            'adj_close' => $this->normalizeBarNumber($row['adj_close'] ?? null),
            'source' => strtoupper((string) ($row['source'] ?? '')),
        ]));
    }

    private function normalizeBarNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = rtrim(rtrim(number_format((float) $value, 10, '.', ''), '0'), '.');

        return $normalized === '-0' ? '0' : $normalized;
    }

    private function barLineage($row): array
    {
        $source = is_array($row) ? $row : (array) $row;
        $lineage = [];
        foreach ([
            'listing_id', 'source_observation_id', 'previous_close', 'traded_value_idr_actual',
            'trade_count_actual', 'board_code', 'session_code', 'source_timestamp', 'acquired_at',
            'canonicalization_version', 'price_product_code', 'quality_state', 'config_snapshot_id',
            'source_scale_state', 'source_scale_assessment_id',
        ] as $field) {
            $lineage[$field] = array_key_exists($field, $source) ? $source[$field] : null;
        }

        return $lineage;
    }

    private function eligibilityFacts($row): array
    {
        $source = is_array($row) ? $row : (array) $row;
        $facts = [];

        foreach (array_merge(
            ['listing_id'],
            self::REQUIRED_ELIGIBILITY_WRITE_FIELDS,
            [
                'config_snapshot_id', 'market_structure_resolution_state',
                'price_band_revision_id', 'minimum_price_revision_id', 'tick_size_revision_id',
            ]
        ) as $field) {
            $facts[$field] = array_key_exists($field, $source) ? $source[$field] : null;
        }

        return $facts;
    }

    private function assertCompleteBarRows(array $rows, string $context): void
    {
        $this->assertRequiredRowValues(
            $rows,
            self::REQUIRED_CANONICAL_BAR_WRITE_FIELDS,
            'CANONICAL_BAR_WRITE_INCOMPLETE',
            $context
        );
    }

    private function assertCompleteEligibilityRows(array $rows, string $context): void
    {
        $this->assertRequiredRowValues(
            $rows,
            self::REQUIRED_ELIGIBILITY_WRITE_FIELDS,
            'ELIGIBILITY_WRITE_INCOMPLETE',
            $context
        );
    }

    private function assertRequiredRowValues(array $rows, array $requiredFields, string $reasonCode, string $context): void
    {
        foreach ($rows as $index => $row) {
            $source = is_array($row) ? $row : (array) $row;
            $missing = [];

            foreach ($requiredFields as $field) {
                if (! array_key_exists($field, $source)
                    || $source[$field] === null
                    || (is_string($source[$field]) && trim($source[$field]) === '')) {
                    $missing[] = $field;
                }
            }

            if ($missing !== []) {
                throw new \LogicException(
                    $reasonCode.': '.$context.' row '.$index.' is missing required field(s): '.implode(', ', $missing).'.'
                );
            }
        }
    }

    private function indicatorLineage($row): array
    {
        $source = is_array($row) ? $row : (array) $row;
        $lineage = [];
        foreach ([
            'listing_id', 'formula_version', 'config_snapshot_id', 'factor_set_id',
            'factor_set_hash', 'price_product_code', 'price_product_version',
            'sector_membership_id', 'adv20_traded_value_idr_actual',
            'adv20_close_volume_proxy_idr', 'atr14', 'atr_state_ref', 'null_reasons_json',
        ] as $field) {
            $lineage[$field] = array_key_exists($field, $source) ? $source[$field] : null;
        }

        return $lineage;
    }
}
