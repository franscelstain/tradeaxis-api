<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodPublicationRepository
{
    public function findCurrentPublicationForTradeDate($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', $tradeDate)
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->where(function ($query) {
                $query->whereNull('run.run_id')
                    ->orWhere(function ($sub) {
                        $sub->where('run.terminal_status', 'SUCCESS')
                            ->where('run.publishability_state', 'READABLE');
                    });
            })
            ->select('pub.*', 'ptr.trade_date as pointer_trade_date', 'ptr.run_id as pointer_run_id', 'ptr.publication_version as pointer_publication_version', 'ptr.sealed_at as pointer_sealed_at')
            ->first();
    }

    public function getOrCreateCandidatePublication(EodRun $run, $supersedesPublicationId = null)
    {
        return DB::transaction(function () use ($run, $supersedesPublicationId) {
            $existing = DB::table('eod_publications')
                ->where('run_id', $run->run_id)
                ->where('trade_date', $run->trade_date_requested)
                ->orderByDesc('publication_id')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $currentMaxVersion = (int) DB::table('eod_publications')
                ->where('trade_date', $run->trade_date_requested)
                ->max('publication_version');

            $now = Carbon::now(config('market_data.platform.timezone'));
            $publicationId = DB::table('eod_publications')->insertGetId([
                'trade_date' => $run->trade_date_requested,
                'run_id' => $run->run_id,
                'publication_version' => $currentMaxVersion + 1,
                'is_current' => 0,
                'supersedes_publication_id' => $supersedesPublicationId,
                'seal_state' => 'UNSEALED',
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'sealed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        });
    }

    public function findByRunId($runId)
    {
        return DB::table('eod_publications')->where('run_id', $runId)->orderByDesc('publication_id')->first();
    }

    public function updateCandidateHashes($publicationId, array $hashes)
    {
        DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->update([
                'bars_batch_hash' => $hashes['bars_batch_hash'],
                'indicators_batch_hash' => $hashes['indicators_batch_hash'],
                'eligibility_batch_hash' => $hashes['eligibility_batch_hash'],
                'updated_at' => Carbon::now(config('market_data.platform.timezone')),
            ]);
    }

    public function sealCandidatePublication(EodRun $run, $sealedBy, $sealNote = null)
    {
        return DB::transaction(function () use ($run) {
            $candidate = $this->getOrCreateCandidatePublication($run, null);

            if (! $candidate->bars_batch_hash || ! $candidate->indicators_batch_hash || ! $candidate->eligibility_batch_hash) {
                throw new \RuntimeException('Cannot seal publication before all candidate hashes exist.');
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'seal_state' => 'SEALED',
                    'sealed_at' => $now,
                    'updated_at' => $now,
                ]);

            return DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        });
    }

    public function promoteCandidateToCurrent(EodRun $run, $priorPublicationId = null)
    {
        return DB::transaction(function () use ($run, $priorPublicationId) {
            $candidate = DB::table('eod_publications')
                ->where('run_id', $run->run_id)
                ->where('trade_date', $run->trade_date_requested)
                ->orderByDesc('publication_id')
                ->lockForUpdate()
                ->first();

            if (! $candidate) {
                throw new \RuntimeException('Candidate publication not found for finalize/current-switch.');
            }

            if ($candidate->seal_state !== 'SEALED') {
                throw new \RuntimeException('Candidate publication is not sealed.');
            }

            $current = DB::table('eod_current_publication_pointer as ptr')
                ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
                ->where('ptr.trade_date', $run->trade_date_requested)
                ->lockForUpdate()
                ->select('ptr.trade_date as pointer_trade_date', 'pub.*')
                ->first();

            if ($current && (int) $current->publication_id !== (int) $candidate->publication_id && ! $priorPublicationId) {
                throw new \RuntimeException('Current publication already exists for trade date '.$run->trade_date_requested.'. Correction/reseal is required before replacing it.');
            }

            if ($priorPublicationId && $current && (int) $current->publication_id !== (int) $priorPublicationId) {
                throw new \RuntimeException('Correction baseline no longer matches current publication pointer.');
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            DB::table('eod_publications')
                ->where('trade_date', $run->trade_date_requested)
                ->update([
                    'is_current' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'is_current' => 1,
                    'supersedes_publication_id' => $priorPublicationId,
                    'updated_at' => $now,
                ]);

            DB::table('eod_current_publication_pointer')->updateOrInsert(
                ['trade_date' => $run->trade_date_requested],
                [
                    'publication_id' => $candidate->publication_id,
                    'run_id' => $run->run_id,
                    'publication_version' => $candidate->publication_version,
                    'sealed_at' => $candidate->sealed_at,
                    'updated_at' => $now,
                ]
            );

            return DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        });
    }


    public function discardCandidatePublication($publicationId)
    {
        DB::transaction(function () use ($publicationId) {
            DB::table('eod_bars_history')->where('publication_id', $publicationId)->delete();
            DB::table('eod_indicators_history')->where('publication_id', $publicationId)->delete();
            DB::table('eod_eligibility_history')->where('publication_id', $publicationId)->delete();
            DB::table('eod_publications')->where('publication_id', $publicationId)->delete();
        });
    }

    public function findLatestReadablePublicationBefore($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', '<', $tradeDate)
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->orderByDesc('ptr.trade_date')
            ->select('ptr.trade_date as readable_trade_date', 'pub.publication_id', 'pub.publication_version', 'run.run_id')
            ->first();
    }

    public function buildManifestByPublicationId($publicationId)
    {
        return DB::table('eod_publications as pub')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('pub.publication_id', $publicationId)
            ->select(
                'pub.publication_id',
                'pub.trade_date',
                'pub.run_id',
                'pub.publication_version',
                'pub.is_current',
                'pub.supersedes_publication_id',
                'pub.seal_state',
                'pub.sealed_at',
                'run.config_version as config_identity',
                'pub.bars_batch_hash',
                'pub.indicators_batch_hash',
                'pub.eligibility_batch_hash',
                'run.bars_rows_written',
                'run.indicators_rows_written',
                'run.eligibility_rows_written',
                'run.trade_date_effective'
            )
            ->first();
    }
}
