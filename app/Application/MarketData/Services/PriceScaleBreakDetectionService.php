<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Detect discontinuities in the canonical RAW price series.
 *
 * A detector finding is append-only candidate evidence. It never verifies an event, ex-date,
 * action type or factor, and it never rewrites a canonical bar.
 */
class PriceScaleBreakDetectionService
{
    private const CANDIDATE_RATIOS = [2, 2.5, 3, 4, 5, 8, 10, 20, 25, 40, 50, 100];

    private $breaks;
    private $calendar;

    public function __construct(PriceScaleBreakRepository $breaks = null, MarketCalendarRepository $calendar = null)
    {
        $this->breaks = $breaks ?: new PriceScaleBreakRepository();
        $this->calendar = $calendar ?: new MarketCalendarRepository();
    }

    public function detect($startDate = null, $endDate = null, $tickerCode = null, $apply = false): array
    {
        $config = $this->config();
        $tickerQuery = DB::table('tickers')->select(['ticker_id', 'ticker_code'])->orderBy('ticker_id');
        if ($tickerCode !== null && $tickerCode !== '') {
            $tickerQuery->where('ticker_code', strtoupper(trim($tickerCode)));
        }

        $detected = [];
        $scanned = 0;
        $skippedBelowMinPrice = 0;
        $skippedIdentity = 0;
        $skippedNonAdjacent = 0;

        foreach ($tickerQuery->get() as $ticker) {
            $tickerId = (int) $ticker->ticker_id;
            $listingId = $this->listingIdForTicker($tickerId);
            if ($listingId <= 0) {
                $skippedIdentity++;
                continue;
            }

            $series = DB::table('eod_bars')
                ->where('ticker_id', $tickerId)
                ->orderBy('trade_date')
                ->get(['trade_date', 'open', 'close', 'publication_id', 'source_observation_id', 'config_snapshot_id'])
                ->all();

            for ($i = 1, $count = count($series); $i < $count; $i++) {
                $previous = $series[$i - 1];
                $current = $series[$i];
                $scanned++;
                $currentDate = (string) $current->trade_date;
                $previousDate = (string) $previous->trade_date;

                if ($startDate !== null && $currentDate < (string) $startDate) continue;
                if ($endDate !== null && $currentDate > (string) $endDate) continue;

                if (! $this->isVerifiedAdjacent($previousDate, $currentDate)) {
                    $skippedNonAdjacent++;
                    continue;
                }

                $previousClose = (float) $previous->close;
                $open = (float) $current->open;
                if ($previousClose <= 0 || $open <= 0) continue;
                if ($previousClose < $config['min_price_idr'] || $open < $config['min_price_idr']) {
                    $skippedBelowMinPrice++;
                    continue;
                }

                $rawRatio = $previousClose / $open;
                $direction = $rawRatio >= 1 ? 'PRICE_DECREASED' : 'PRICE_INCREASED';
                $ratio = $rawRatio >= 1 ? $rawRatio : 1 / $rawRatio;
                if ($ratio < $config['min_ratio']) continue;

                $inferred = $this->inferRatio($ratio, $config['ratio_tolerance']);
                $classification = $this->classifyPersistence($series, $i, $ratio, $direction);
                $linkage = $this->possibleCorporateActionLinkage($listingId, $currentDate, $config);
                $now = date('Y-m-d H:i:s');
                $candidate = [
                    'listing_id' => $listingId,
                    'prior_trade_date' => $previousDate,
                    'current_trade_date' => $currentDate,
                    'prior_publication_id' => $previous->publication_id !== null ? (int) $previous->publication_id : null,
                    'current_publication_id' => $current->publication_id !== null ? (int) $current->publication_id : null,
                    'prior_source_observation_id' => $previous->source_observation_id !== null ? (int) $previous->source_observation_id : null,
                    'current_source_observation_id' => $current->source_observation_id !== null ? (int) $current->source_observation_id : null,
                    'prior_close' => round($previousClose, 8),
                    'current_open' => round($open, 8),
                    'diagnostic_ratio' => round($ratio, 12),
                    'ratio_direction' => $direction,
                    'inferred_ratio' => $inferred['ratio'],
                    'inferred_ratio_error_pct' => $inferred['error_pct'],
                    'candidate_classification' => $classification,
                    'continuity_verdict' => 'UNRESOLVED_SCALE_BREAK_CANDIDATE',
                    'market_calendar_adjacent' => true,
                    'detector_version' => $config['contract_version'],
                    'config_snapshot_id' => $current->config_snapshot_id !== null ? (int) $current->config_snapshot_id : null,
                    'linkage_state' => $linkage['state'],
                    'possible_corporate_action_revision_id' => $linkage['corporate_action_revision_id'],
                    'review_state' => 'DETECTED',
                    'detected_at' => $now,
                    'created_at' => $now,
                ];

                $candidate['candidate_uid'] = $this->candidateUid($candidate);
                $detected[] = $candidate + [
                    'ticker_id' => $tickerId,
                    'ticker_code' => strtoupper(trim((string) $ticker->ticker_code)),
                ];

                if ($apply) {
                    $this->breaks->recordCandidate($candidate);
                    // Historical compatibility projection. It is explicitly non-authoritative
                    // and is never allowed to verify linkage or release quarantine.
                    $this->breaks->upsert([
                        'ticker_id' => $tickerId,
                        'ticker_code' => strtoupper(trim((string) $ticker->ticker_code)),
                        'trade_date' => $currentDate,
                        'previous_close' => round($previousClose, 4),
                        'open_price' => round($open, 4),
                        'implied_ratio' => round($ratio, 10),
                        'ratio_direction' => $direction,
                        'inferred_ratio' => $inferred['ratio'],
                        'inferred_ratio_error_pct' => $inferred['error_pct'],
                        'break_type' => $classification,
                        'match_status' => $linkage['state'],
                        'matched_corporate_action_id' => null,
                        'matched_action_type' => $linkage['action_type_code'],
                        'detection_contract_version' => $config['contract_version'],
                        'detected_at' => $now,
                    ]);
                }
            }
        }

        return compact('detected', 'scanned', 'skippedBelowMinPrice', 'skippedIdentity', 'skippedNonAdjacent') + [
            'scanned_bars' => $scanned,
            'skipped_below_min_price' => $skippedBelowMinPrice,
            'skipped_identity' => $skippedIdentity,
            'skipped_non_adjacent' => $skippedNonAdjacent,
        ];
    }

    private function listingIdForTicker(int $tickerId): int
    {
        if (! Schema::hasTable('md_listings') || ! Schema::hasColumn('md_listings', 'legacy_ticker_id')) return 0;
        return (int) (DB::table('md_listings')->where('legacy_ticker_id', $tickerId)->value('listing_id') ?: 0);
    }

    private function isVerifiedAdjacent(string $previousDate, string $currentDate): bool
    {
        try {
            $context = $this->calendar->sessionContext($currentDate);
        } catch (\Throwable $e) {
            return false;
        }
        return ($context['provenance_tier'] ?? null) === 'VERIFIED'
            && ($context['is_trading_day'] ?? false) === true
            && (string) ($context['prev_trading_day'] ?? '') === $previousDate;
    }

    private function possibleCorporateActionLinkage(int $listingId, string $tradeDate, array $config): array
    {
        if (! Schema::hasTable('md_corporate_action_revisions')) {
            return ['state' => 'NO_LINKAGE_CANDIDATE', 'corporate_action_revision_id' => null, 'action_type_code' => null];
        }

        $windowStart = date('Y-m-d', strtotime($tradeDate.' -14 days'));
        $windowEnd = date('Y-m-d', strtotime($tradeDate.' +14 days'));
        try {
            $dates = $this->calendar->tradingDatesBetween($windowStart, $windowEnd);
            $position = array_search($tradeDate, $dates, true);
            if ($position !== false) {
                $low = max(0, $position - $config['action_match_trading_days']);
                $high = min(count($dates) - 1, $position + $config['action_match_trading_days']);
                $windowStart = $dates[$low];
                $windowEnd = $dates[$high];
            }
        } catch (\Throwable $e) {
            // Failure to resolve calendar scope cannot verify linkage. The candidate remains
            // quarantining and the date-only fallback is diagnostic at most.
        }

        $row = DB::table('md_corporate_action_revisions as revision')
            ->leftJoin('md_corporate_action_revisions as newer', 'newer.supersedes_revision_id', '=', 'revision.corporate_action_revision_id')
            ->whereNull('newer.corporate_action_revision_id')
            ->where('revision.listing_id', $listingId)
            ->whereNotNull('revision.ex_date')
            ->whereBetween('revision.ex_date', [$windowStart, $windowEnd])
            ->where('revision.lifecycle_state', '<>', 'CANCELLED')
            ->orderByRaw("CASE WHEN revision.verification_state IN ('AUTHORITATIVE_VERIFIED','MANUAL_VERIFIED') THEN 0 ELSE 1 END")
            ->orderBy('revision.ex_date')
            ->orderByDesc('revision.revision_number')
            ->first(['revision.corporate_action_revision_id', 'revision.action_type_code', 'revision.verification_state']);

        if (! $row) {
            return ['state' => 'NO_LINKAGE_CANDIDATE', 'corporate_action_revision_id' => null, 'action_type_code' => null];
        }

        $verified = in_array((string) $row->verification_state, ['AUTHORITATIVE_VERIFIED', 'MANUAL_VERIFIED'], true);
        return [
            'state' => $verified ? 'VERIFIED_REVISION_LINKAGE_CANDIDATE' : 'REVISION_LINKAGE_CANDIDATE',
            'corporate_action_revision_id' => (int) $row->corporate_action_revision_id,
            'action_type_code' => (string) $row->action_type_code,
        ];
    }

    private function candidateUid(array $candidate): string
    {
        $identity = [];
        foreach (['listing_id','prior_trade_date','current_trade_date','prior_publication_id','current_publication_id','prior_source_observation_id','current_source_observation_id','diagnostic_ratio','ratio_direction','detector_version','config_snapshot_id'] as $key) {
            $identity[$key] = $candidate[$key] ?? null;
        }
        return hash('sha256', json_encode($identity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function classifyPersistence(array $series, int $index, float $normalizedRatio, string $direction): string
    {
        if (! isset($series[$index + 1])) return 'SCALE_SHIFT';
        $current = (float) $series[$index]->close;
        $next = (float) $series[$index + 1]->open;
        if ($current <= 0 || $next <= 0) return 'SCALE_SHIFT';
        $revertRatio = $direction === 'PRICE_DECREASED' ? $next / $current : $current / $next;
        return $revertRatio >= max(1.5, $normalizedRatio * 0.75) ? 'ISOLATED_ANOMALY' : 'SCALE_SHIFT';
    }

    private function inferRatio(float $ratio, float $tolerance): array
    {
        $best = null; $bestError = null;
        foreach (self::CANDIDATE_RATIOS as $candidate) {
            $error = abs($ratio - $candidate) / $candidate;
            if ($bestError === null || $error < $bestError) { $bestError = $error; $best = $candidate; }
        }
        if ($bestError === null || $bestError > $tolerance) return ['ratio' => null, 'error_pct' => null];
        return ['ratio' => $best, 'error_pct' => round($bestError * 100, 6)];
    }

    private function config(): array
    {
        return [
            'contract_version' => (string) config('market_data.price_scale_break.contract_version', 'price_scale_break_v1'),
            'min_ratio' => (float) config('market_data.price_scale_break.min_ratio', 1.7),
            'min_price_idr' => (float) config('market_data.price_scale_break.min_price_idr', 50),
            'action_match_trading_days' => (int) config('market_data.price_scale_break.action_match_trading_days', 5),
            'ratio_tolerance' => (float) config('market_data.price_scale_break.ratio_tolerance', 0.08),
        ];
    }
}
