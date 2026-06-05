<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;

class EodEligibilityBuildService
{
    private $tickers;
    private $artifacts;
    private $publications;
    private $decisions;
    private $eventRiskSources;

    public function __construct(
        TickerMasterRepository $tickers,
        EodArtifactRepository $artifacts,
        EodPublicationRepository $publications,
        EligibilityDecisionService $decisions,
        EventRiskSourceRepository $eventRiskSources = null
    ) {
        $this->tickers = $tickers;
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->decisions = $decisions;
        $this->eventRiskSources = $eventRiskSources;
    }

    public function build($run, $requestedDate, $correctionMode = false)
    {
        $candidatePublication = $this->publications->getOrCreateCandidatePublication($run);
        $useHistory = $correctionMode
            || (int) ($candidatePublication->publication_version ?? 1) > 1
            || ! empty($candidatePublication->supersedes_publication_id)
            || ! empty($candidatePublication->previous_publication_id)
            || ! empty($candidatePublication->replaced_publication_id);

        $universe = $this->filterSuspendedUniverseRows(
            $this->tickers->getUniverseForTradeDate($requestedDate),
            $requestedDate
        );
        $bars = $this->artifacts->loadBarsForTradeDate($requestedDate, $useHistory ? $candidatePublication->publication_id : null);
        $indicators = $this->artifacts->loadIndicatorsForTradeDate($requestedDate, $useHistory ? $candidatePublication->publication_id : null);
        $rows = [];
        $blockedCount = 0;
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        foreach ($universe as $ticker) {
            $tickerId = $ticker['ticker_id'];
            $bar = isset($bars[$tickerId]) ? $bars[$tickerId] : null;
            $indicator = isset($indicators[$tickerId]) ? $indicators[$tickerId] : null;
            $decision = $this->decisions->decide($bar, $indicator);
            $reasonCode = $decision['reason_code'];
            $eligible = $decision['eligible'];

            if ($eligible === 0) {
                $blockedCount++;
            }

            $rows[] = [
                'trade_date' => $requestedDate,
                'ticker_id' => $tickerId,
                'eligible' => $eligible,
                'reason_code' => $reasonCode,
                'run_id' => $run->run_id,
                'publication_id' => $candidatePublication->publication_id,
                'created_at' => $now,
            ];
        }

        $this->artifacts->replaceEligibility($requestedDate, $run->run_id, $rows, $candidatePublication->publication_id, $useHistory);

        $eligibilityRowsWritten = count($rows);
        $eligibleRows = $eligibilityRowsWritten - $blockedCount;

        return [
            'publication_id' => (int) $candidatePublication->publication_id,
            'publication_version' => (int) $candidatePublication->publication_version,
            'eligibility_rows_written' => $eligibilityRowsWritten,
            'blocked_rows' => $blockedCount,
            'eligible_rows' => $eligibleRows,
            'eligibility_pass_ratio' => $eligibilityRowsWritten > 0 ? round($eligibleRows / $eligibilityRowsWritten, 4) : null,
            'storage_target' => $useHistory ? 'eod_eligibility_history' : 'eod_eligibility',
        ];
    }

    private function filterSuspendedUniverseRows(array $universeRows, $tradeDate): array
    {
        if (! $this->eventRiskSources instanceof EventRiskSourceRepository || $universeRows === []) {
            return $universeRows;
        }

        $tickerIds = array_values(array_filter(array_map(function ($row) {
            return (int) ($row['ticker_id'] ?? 0);
        }, $universeRows)));

        if ($tickerIds === []) {
            return $universeRows;
        }

        $suspendedIds = array_fill_keys($this->eventRiskSources->suspendedTickerIdsAsOf($tickerIds, $tradeDate), true);
        if ($suspendedIds === []) {
            return $universeRows;
        }

        return array_values(array_filter($universeRows, function ($row) use ($suspendedIds) {
            $tickerId = (int) ($row['ticker_id'] ?? 0);

            return $tickerId > 0 && ! isset($suspendedIds[$tickerId]);
        }));
    }
}
