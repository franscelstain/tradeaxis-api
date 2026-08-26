<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only persistence boundary for price-scale-break candidate evidence.
 *
 * Legacy market_data_price_scale_breaks remains a historical compatibility projection only. It
 * can add risk, never verify an event or clear quarantine.
 */
class PriceScaleBreakRepository
{
    private const REVIEW_STATES = ['CONFIRMED', 'DISMISSED', 'LINKED_VERIFIED_FACTOR'];

    public function recordCandidate(array $row): array
    {
        if (! Schema::hasTable('md_price_scale_break_candidates')) {
            throw new \RuntimeException('PRICE_SCALE_BREAK_CANDIDATE_FOUNDATION_MISSING.');
        }
        $uid = trim((string) ($row['candidate_uid'] ?? ''));
        if (! preg_match('/^[a-f0-9]{64}$/', $uid)) {
            throw new \InvalidArgumentException('PRICE_SCALE_BREAK_CANDIDATE_UID_INVALID.');
        }
        $existing = DB::table('md_price_scale_break_candidates')->where('candidate_uid', $uid)->first();
        if ($existing) {
            return ['state' => 'IDEMPOTENT_EXISTING', 'candidate' => (array) $existing];
        }

        $now = $row['created_at'] ?? Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $id = DB::table('md_price_scale_break_candidates')->insertGetId([
            'candidate_uid' => $uid,
            'listing_id' => (int) $row['listing_id'],
            'prior_trade_date' => $row['prior_trade_date'],
            'current_trade_date' => $row['current_trade_date'],
            'prior_publication_id' => $row['prior_publication_id'],
            'current_publication_id' => $row['current_publication_id'],
            'prior_source_observation_id' => $row['prior_source_observation_id'],
            'current_source_observation_id' => $row['current_source_observation_id'],
            'prior_close' => $row['prior_close'],
            'current_open' => $row['current_open'],
            'diagnostic_ratio' => $row['diagnostic_ratio'],
            'ratio_direction' => $row['ratio_direction'],
            'inferred_ratio' => $row['inferred_ratio'],
            'inferred_ratio_error_pct' => $row['inferred_ratio_error_pct'],
            'candidate_classification' => $row['candidate_classification'],
            'continuity_verdict' => $row['continuity_verdict'],
            'market_calendar_adjacent' => ! empty($row['market_calendar_adjacent']) ? 1 : 0,
            'detector_version' => $row['detector_version'],
            'config_snapshot_id' => $row['config_snapshot_id'],
            'linkage_state' => $row['linkage_state'] ?? 'NO_LINKAGE_CANDIDATE',
            'possible_corporate_action_revision_id' => $row['possible_corporate_action_revision_id'] ?? null,
            'review_state' => 'DETECTED',
            'detected_at' => $row['detected_at'] ?? $now,
            'supersedes_candidate_id' => $row['supersedes_candidate_id'] ?? null,
            'created_at' => $now,
        ]);
        $candidate = DB::table('md_price_scale_break_candidates')->where('candidate_id', $id)->first();

        return ['state' => 'APPENDED', 'candidate' => (array) $candidate];
    }

    public function appendReview($candidateId, $reviewState, $reviewer, $reviewNote, $evidenceSourceObservationId = null, $corporateActionRevisionId = null): array
    {
        $candidateId = (int) $candidateId;
        $reviewState = strtoupper(trim((string) $reviewState));
        $reviewer = trim((string) $reviewer);
        $reviewNote = trim((string) $reviewNote);
        if (! in_array($reviewState, self::REVIEW_STATES, true)) {
            throw new \InvalidArgumentException('PRICE_SCALE_BREAK_REVIEW_STATE_INVALID.');
        }
        if ($candidateId <= 0 || $reviewer === '' || $reviewNote === '') {
            throw new \InvalidArgumentException('PRICE_SCALE_BREAK_REVIEW_EVIDENCE_INCOMPLETE.');
        }
        if ($reviewState === 'DISMISSED' && (int) $evidenceSourceObservationId <= 0) {
            throw new \InvalidArgumentException('PRICE_SCALE_BREAK_DISMISSAL_REQUIRES_POSITIVE_EVIDENCE.');
        }
        if ($reviewState === 'LINKED_VERIFIED_FACTOR' && (int) $corporateActionRevisionId <= 0) {
            throw new \InvalidArgumentException('PRICE_SCALE_BREAK_LINKED_FACTOR_REVISION_REQUIRED.');
        }

        return DB::transaction(function () use ($candidateId, $reviewState, $reviewer, $reviewNote, $evidenceSourceObservationId, $corporateActionRevisionId) {
            $candidate = DB::table('md_price_scale_break_candidates')->where('candidate_id', $candidateId)->lockForUpdate()->first();
            if (! $candidate) throw new \InvalidArgumentException('PRICE_SCALE_BREAK_CANDIDATE_NOT_FOUND.');
            $latest = DB::table('md_price_scale_break_candidate_reviews')->where('candidate_id', $candidateId)->orderByDesc('revision_number')->lockForUpdate()->first();
            $revision = $latest ? (int) $latest->revision_number + 1 : 1;
            $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
            $id = DB::table('md_price_scale_break_candidate_reviews')->insertGetId([
                'candidate_id' => $candidateId,
                'revision_number' => $revision,
                'review_state' => $reviewState,
                'evidence_source_observation_id' => (int) $evidenceSourceObservationId > 0 ? (int) $evidenceSourceObservationId : null,
                'corporate_action_revision_id' => (int) $corporateActionRevisionId > 0 ? (int) $corporateActionRevisionId : null,
                'reviewer' => $reviewer,
                'review_note' => $reviewNote,
                'recorded_at' => $now,
                'supersedes_review_id' => $latest ? (int) $latest->candidate_review_id : null,
                'created_at' => $now,
            ]);
            DB::table('md_price_scale_break_candidates')->where('candidate_id', $candidateId)->update(['review_state' => $reviewState]);
            $row = DB::table('md_price_scale_break_candidate_reviews')->where('candidate_review_id', $id)->first();
            return ['state' => 'APPENDED', 'review' => (array) $row];
        });
    }

    /** Historical compatibility projection only. */
    public function upsert(array $row): void
    {
        $now = $row['detected_at'] ?? date('Y-m-d H:i:s');
        DB::table($this->table())->updateOrInsert(
            ['ticker_id' => (int) $row['ticker_id'], 'trade_date' => $row['trade_date']],
            [
                'ticker_code' => $row['ticker_code'], 'previous_close' => $row['previous_close'],
                'open_price' => $row['open_price'], 'implied_ratio' => $row['implied_ratio'],
                'ratio_direction' => $row['ratio_direction'], 'inferred_ratio' => $row['inferred_ratio'],
                'inferred_ratio_error_pct' => $row['inferred_ratio_error_pct'], 'break_type' => $row['break_type'],
                'match_status' => $row['match_status'], 'matched_corporate_action_id' => null,
                'matched_action_type' => $row['matched_action_type'], 'detection_contract_version' => $row['detection_contract_version'],
                'detected_at' => $now, 'updated_at' => $now,
            ]
        );
    }

    public function resolveContaminationForTickerIds(array $tickerIds, array $tradingDates): array
    {
        $tickerIds = array_values(array_filter(array_unique(array_map('intval', $tickerIds))));
        $tradingDates = array_values(array_map('strval', $tradingDates));
        if ($tickerIds === [] || $tradingDates === []) return [];
        $last = count($tradingDates) - 1;
        $depthByDate = [];
        foreach ($tradingDates as $i => $date) $depthByDate[$date] = $last - $i;
        $contamination = [];
        $covered = [];

        if (Schema::hasTable('md_price_scale_break_candidates') && Schema::hasColumn('md_listings', 'legacy_ticker_id')) {
            $rows = DB::table('md_price_scale_break_candidates as candidate')
                ->join('md_listings as listing', 'listing.listing_id', '=', 'candidate.listing_id')
                ->leftJoin('md_price_scale_break_candidate_reviews as review', function ($join) {
                    $join->on('review.candidate_id', '=', 'candidate.candidate_id')
                        ->whereNotExists(function ($sub) {
                            $sub->select(DB::raw(1))->from('md_price_scale_break_candidate_reviews as newer')
                                ->whereColumn('newer.supersedes_review_id', 'review.candidate_review_id');
                        });
                })
                ->whereIn('listing.legacy_ticker_id', $tickerIds)
                ->whereBetween('candidate.current_trade_date', [$tradingDates[0], $tradingDates[$last]])
                ->get(['candidate.*', 'listing.legacy_ticker_id', 'review.review_state as latest_review_state', 'review.corporate_action_revision_id as reviewed_action_revision_id']);

            foreach ($rows as $row) {
                $date = (string) $row->current_trade_date;
                if (! isset($depthByDate[$date])) continue;
                $tickerId = (int) $row->legacy_ticker_id;
                $covered[$tickerId.'|'.$date] = true;
                $review = (string) ($row->latest_review_state ?: 'DETECTED');
                if ($review === 'DISMISSED') continue;
                if ($review === 'LINKED_VERIFIED_FACTOR' && $this->hasAppliedRevisionFactor((int) $row->reviewed_action_revision_id)) continue;
                $contamination[$tickerId][] = [
                    'break_type' => (string) $row->candidate_classification,
                    'trade_date' => $date,
                    'depth' => $depthByDate[$date],
                    'implied_ratio' => $row->diagnostic_ratio,
                    'inferred_ratio' => $row->inferred_ratio,
                    'match_status' => (string) $row->linkage_state,
                    'matched_action_type' => null,
                    'candidate_uid' => (string) $row->candidate_uid,
                    'continuity_verdict' => (string) $row->continuity_verdict,
                ];
            }
        }

        // Historical compatibility findings are risk-only. A legacy price_adjustment_factor can
        // never release quarantine because it has no verified V2 revision/factor lineage.
        if (Schema::hasTable($this->table())) {
            $legacy = DB::table($this->table().' as b')
                ->whereIn('b.ticker_id', $tickerIds)
                ->whereBetween('b.trade_date', [$tradingDates[0], $tradingDates[$last]])
                ->whereIn('b.review_status', ['DETECTED', 'CONFIRMED'])
                ->orderBy('b.ticker_id')->orderBy('b.trade_date')->get(['b.*']);
            foreach ($legacy as $row) {
                $date = (string) $row->trade_date; $tickerId = (int) $row->ticker_id;
                if (! isset($depthByDate[$date]) || isset($covered[$tickerId.'|'.$date])) continue;
                $contamination[$tickerId][] = [
                    'break_type' => (string) $row->break_type, 'trade_date' => $date,
                    'depth' => $depthByDate[$date], 'implied_ratio' => $row->implied_ratio,
                    'inferred_ratio' => $row->inferred_ratio, 'match_status' => 'LEGACY_UNVERIFIED',
                    'matched_action_type' => $row->matched_action_type,
                    'continuity_verdict' => 'LEGACY_UNVERIFIED_QUARANTINE',
                ];
            }
        }

        ksort($contamination);
        return $contamination;
    }

    public function summary(): array
    {
        if (Schema::hasTable('md_price_scale_break_candidates')) {
            return DB::table('md_price_scale_break_candidates')
                ->selectRaw('candidate_classification, linkage_state, review_state, COUNT(*) AS total')
                ->groupBy('candidate_classification', 'linkage_state', 'review_state')
                ->orderBy('candidate_classification')->get()->map(function ($row) { return (array) $row; })->all();
        }
        return DB::table($this->table())->selectRaw('break_type, match_status, review_status, COUNT(*) AS total')
            ->groupBy('break_type', 'match_status', 'review_status')->orderBy('break_type')->get()
            ->map(function ($row) { return (array) $row; })->all();
    }

    private function hasAppliedRevisionFactor(int $revisionId): bool
    {
        return $revisionId > 0 && Schema::hasTable('md_adjustment_factors')
            && DB::table('md_adjustment_factors')->where('corporate_action_revision_id', $revisionId)->exists();
    }

    private function table(): string
    {
        return config('market_data.event_risk.price_scale_breaks_table', 'market_data_price_scale_breaks');
    }
}
