<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReplayResultRepository
{
    public function nextReplayId()
    {
        return (int) DB::table('md_replay_daily_metrics')->max('replay_id') + 1;
    }

    public function upsertMetric(array $metric)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $payload = [
            'trade_date_effective' => $metric['trade_date_effective'] ?? null,
            'source' => $metric['source'],
            'status' => $metric['status'],
            'comparison_result' => $metric['comparison_result'],
            'comparison_note' => $metric['comparison_note'] ?? null,
            'artifact_changed_scope' => $metric['artifact_changed_scope'] ?? null,
            'config_identity' => $metric['config_identity'] ?? null,
            'publication_version' => $metric['publication_version'] ?? null,
            'coverage_ratio' => $metric['coverage_ratio'] ?? null,
            'bars_rows_written' => $metric['bars_rows_written'] ?? null,
            'indicators_rows_written' => $metric['indicators_rows_written'] ?? null,
            'eligibility_rows_written' => $metric['eligibility_rows_written'] ?? null,
            'eligible_count' => $metric['eligible_count'] ?? null,
            'invalid_bar_count' => $metric['invalid_bar_count'] ?? null,
            'invalid_indicator_count' => $metric['invalid_indicator_count'] ?? null,
            'warning_count' => $metric['warning_count'] ?? null,
            'hard_reject_count' => $metric['hard_reject_count'] ?? null,
            'bars_batch_hash' => $metric['bars_batch_hash'] ?? null,
            'indicators_batch_hash' => $metric['indicators_batch_hash'] ?? null,
            'eligibility_batch_hash' => $metric['eligibility_batch_hash'] ?? null,
            'seal_state' => $metric['seal_state'],
            'sealed_at' => $metric['sealed_at'] ?? null,
            'expected_status' => $metric['expected_status'] ?? null,
            'expected_trade_date_effective' => $metric['expected_trade_date_effective'] ?? null,
            'expected_seal_state' => $metric['expected_seal_state'] ?? null,
            'mismatch_summary' => $metric['mismatch_summary'] ?? null,
            'created_at' => $metric['created_at'] ?? $now,
        ];

        DB::table('md_replay_daily_metrics')->updateOrInsert(
            [
                'replay_id' => $metric['replay_id'],
                'trade_date' => $metric['trade_date'],
            ],
            $payload
        );
    }

    public function replaceReasonCodeCounts($replayId, $tradeDate, array $reasonCounts)
    {
        DB::transaction(function () use ($replayId, $tradeDate, $reasonCounts) {
            DB::table('md_replay_reason_code_counts')
                ->where('replay_id', $replayId)
                ->where('trade_date', $tradeDate)
                ->delete();

            if (empty($reasonCounts)) {
                return;
            }

            $rows = [];
            foreach ($reasonCounts as $row) {
                $rows[] = [
                    'replay_id' => $replayId,
                    'trade_date' => $tradeDate,
                    'reason_code' => $row['reason_code'],
                    'reason_count' => (int) $row['reason_count'],
                ];
            }

            DB::table('md_replay_reason_code_counts')->insert($rows);
        });
    }
}
