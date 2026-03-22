<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodArtifactRepository
{
    public function replaceBars($tradeDate, $publicationId, $runId, array $validRows, array $invalidRows, $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $publicationId, $runId, $validRows, $invalidRows, $useHistory) {
            if ($useHistory) {
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
        });
    }

    public function loadBarsWindow($tradeDate, $lookbackDays, $requestedPublicationId = null)
    {
        $startDate = Carbon::parse($tradeDate)->subDays($lookbackDays + 10)->toDateString();
        $rows = DB::table('eod_bars')
            ->whereBetween('trade_date', [$startDate, $tradeDate])
            ->orderBy('ticker_id')
            ->orderBy('trade_date')
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();

        if ($requestedPublicationId) {
            $rows = array_values(array_filter($rows, function ($row) use ($tradeDate) {
                return (string) $row['trade_date'] !== (string) $tradeDate;
            }));

            $historyRows = DB::table('eod_bars_history')
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $requestedPublicationId)
                ->orderBy('ticker_id')
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
                    ->sortBy('trade_date')
                    ->values()
                    ->all();
            })
            ->all();
    }

    public function replaceIndicators($tradeDate, $runId, array $rows, $publicationId = null, $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $rows, $publicationId, $useHistory) {
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

    public function loadIndicatorsForTradeDate($tradeDate, $requestedPublicationId = null)
    {
        $table = $requestedPublicationId ? 'eod_indicators_history' : 'eod_indicators';
        $query = DB::table($table)->where('trade_date', $tradeDate);
        if ($requestedPublicationId) {
            $query->where('publication_id', $requestedPublicationId);
        }

        return $query->get()
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

        return $query->get()
            ->keyBy('ticker_id')
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }

    public function replaceEligibility($tradeDate, $runId, array $rows, $publicationId = null, $useHistory = false)
    {
        return DB::transaction(function () use ($tradeDate, $rows, $publicationId, $useHistory) {
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
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        if (! DB::table('eod_bars_history')->where('publication_id', $publicationId)->exists()) {
            $bars = DB::table('eod_bars')->where('trade_date', $tradeDate)->where('publication_id', $publicationId)->get();
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
                ];
            }
            if (! empty($insert)) {
                DB::table('eod_bars_history')->insert($insert);
            }
        }

        if (! DB::table('eod_indicators_history')->where('publication_id', $publicationId)->exists()) {
            $indicators = DB::table('eod_indicators')->where('trade_date', $tradeDate)->where('publication_id', $publicationId)->get();
            $insert = [];
            foreach ($indicators as $row) {
                $insert[] = [
                    'publication_id' => $publicationId,
                    'trade_date' => $row->trade_date,
                    'ticker_id' => $row->ticker_id,
                    'is_valid' => $row->is_valid,
                    'invalid_reason_code' => $row->invalid_reason_code,
                    'indicator_set_version' => $row->indicator_set_version,
                    'dv20_idr' => $row->dv20_idr,
                    'atr14_pct' => $row->atr14_pct,
                    'vol_ratio' => $row->vol_ratio,
                    'roc20' => $row->roc20,
                    'hh20' => $row->hh20,
                    'run_id' => $runId,
                    'created_at' => $now,
                ];
            }
            if (! empty($insert)) {
                DB::table('eod_indicators_history')->insert($insert);
            }
        }

        if (! DB::table('eod_eligibility_history')->where('publication_id', $publicationId)->exists()) {
            $eligibility = DB::table('eod_eligibility')->where('trade_date', $tradeDate)->where('publication_id', $publicationId)->get();
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
                ];
            }
            if (! empty($insert)) {
                DB::table('eod_eligibility_history')->insert($insert);
            }
        }
    }

    public function promotePublicationHistoryToCurrent($tradeDate, $publicationId, $runId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        DB::table('eod_bars')->where('trade_date', $tradeDate)->delete();
        $bars = DB::table('eod_bars_history')->where('trade_date', $tradeDate)->where('publication_id', $publicationId)->get();
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
            ];
        }
        if (! empty($insert)) {
            DB::table('eod_bars')->insert($insert);
        }

        DB::table('eod_indicators')->where('trade_date', $tradeDate)->delete();
        $indicators = DB::table('eod_indicators_history')->where('trade_date', $tradeDate)->where('publication_id', $publicationId)->get();
        $insert = [];
        foreach ($indicators as $row) {
            $insert[] = [
                'trade_date' => $row->trade_date,
                'ticker_id' => $row->ticker_id,
                'is_valid' => $row->is_valid,
                'invalid_reason_code' => $row->invalid_reason_code,
                'indicator_set_version' => $row->indicator_set_version,
                'dv20_idr' => $row->dv20_idr,
                'atr14_pct' => $row->atr14_pct,
                'vol_ratio' => $row->vol_ratio,
                'roc20' => $row->roc20,
                'hh20' => $row->hh20,
                'run_id' => $runId,
                'publication_id' => $publicationId,
                'created_at' => $now,
            ];
        }
        if (! empty($insert)) {
            DB::table('eod_indicators')->insert($insert);
        }

        DB::table('eod_eligibility')->where('trade_date', $tradeDate)->delete();
        $elig = DB::table('eod_eligibility_history')->where('trade_date', $tradeDate)->where('publication_id', $publicationId)->get();
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
            ];
        }
        if (! empty($insert)) {
            DB::table('eod_eligibility')->insert($insert);
        }
    }
}
