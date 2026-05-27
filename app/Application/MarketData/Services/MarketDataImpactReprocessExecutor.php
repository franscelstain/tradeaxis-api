<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;

class MarketDataImpactReprocessExecutor
{
    private $runs;
    private $publications;
    private $indicators;
    private $eligibility;

    public function __construct(
        EodRunRepository $runs,
        EodPublicationRepository $publications,
        EodIndicatorsComputeService $indicators,
        EodEligibilityBuildService $eligibility
    ) {
        $this->runs = $runs;
        $this->publications = $publications;
        $this->indicators = $indicators;
        $this->eligibility = $eligibility;
    }

    public function execute($originRun, $sourceMode, array $barMutationSummary, array $indicatorImpactSummary, array $publicationImpactSummary, array $context = [])
    {
        $changedBarCount = (int) ($barMutationSummary['changed_bar_count'] ?? 0);
        $affectedDates = $this->affectedTradeDates($indicatorImpactSummary);

        if ($changedBarCount <= 0) {
            return $this->noopSummary('NOOP_UNCHANGED_BARS');
        }

        if ($affectedDates === []) {
            return $this->noopSummary('NOOP_NO_AFFECTED_DATES');
        }

        $readableDates = array_fill_keys(array_map('strval', (array) ($publicationImpactSummary['impacted_readable_trade_dates'] ?? [])), true);
        $reprocessedDates = [];
        $readableCorrectionCandidateDates = [];
        $indicatorBlockedDates = [];
        $publicationBlockedDates = [];
        $failedDates = [];
        $indicatorBlockedReason = null;
        $publicationBlockedReason = null;
        $failureReason = null;

        foreach ($affectedDates as $tradeDate) {
            $tradeDate = (string) $tradeDate;

            $currentPublication = isset($readableDates[$tradeDate])
                ? (object) ['trade_date' => $tradeDate]
                : $this->publications->findCurrentPublicationForTradeDate($tradeDate);

            if ($currentPublication) {
                $readableCorrectionCandidateDates[] = $tradeDate;
                $indicatorBlockedDates[] = $tradeDate;
                $indicatorBlockedReason = 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION';
                continue;
            }

            $run = (string) ($originRun->trade_date_requested ?? '') === $tradeDate
                ? $originRun
                : $this->runs->findLatestForRequestedDate($tradeDate, $sourceMode);

            if (! $run) {
                $indicatorBlockedDates[] = $tradeDate;
                $publicationBlockedDates[] = $tradeDate;
                $indicatorBlockedReason = $indicatorBlockedReason ?: 'AFFECTED_DATE_RUN_NOT_FOUND';
                $publicationBlockedReason = $publicationBlockedReason ?: 'AFFECTED_DATE_RUN_NOT_FOUND';
                continue;
            }

            try {
                $this->indicators->compute($run, $tradeDate, false);
                $this->eligibility->build($run, $tradeDate, false);
                $reprocessedDates[] = $tradeDate;
            } catch (\Throwable $e) {
                $failedDates[] = $tradeDate;
                $failureReason = $this->reasonCodeFromThrowable($e, 'INDICATOR_REPROCESS_FAILED');
            }
        }

        $indicatorState = $this->executionState($reprocessedDates, $indicatorBlockedDates, $failedDates);
        $eligibilityState = $indicatorState;
        $publicationCandidateDates = $this->sortedUniqueList(array_merge($reprocessedDates, $readableCorrectionCandidateDates));
        $publicationState = 'NOOP';
        $republicationMode = 'NOT_REQUIRED';
        if ($failedDates !== []) {
            $publicationState = 'FAILED';
            $republicationMode = 'FAILED_BEFORE_PROMOTE';
        } elseif ($publicationCandidateDates !== []) {
            $publicationState = 'PENDING_PROMOTE';
            $republicationMode = $this->pendingRepublicationMode($reprocessedDates, $readableCorrectionCandidateDates);
        } elseif ($publicationBlockedDates !== []) {
            $publicationState = 'BLOCKED_REQUIRES_CORRECTION';
            $republicationMode = 'MANUAL_CORRECTION_REQUIRED';
        }

        $summary = [
            'indicator_reprocess_execution_summary' => [
                'execution_state' => $indicatorState,
                'reprocessed_trade_date_count' => count($reprocessedDates),
                'reprocessed_trade_dates' => $reprocessedDates,
                'reprocess_scope' => $reprocessedDates === [] ? 'NONE' : 'FULL_DATE',
                'blocked_trade_dates' => $this->sortedUniqueList($indicatorBlockedDates),
                'failed_trade_dates' => $failedDates,
                'blocked_reason_code' => $indicatorBlockedReason,
                'failure_reason_code' => $failureReason,
            ],
            'eligibility_reprocess_execution_summary' => [
                'execution_state' => $eligibilityState,
                'reprocessed_trade_date_count' => count($reprocessedDates),
                'reprocessed_trade_dates' => $reprocessedDates,
                'blocked_trade_dates' => $this->sortedUniqueList($indicatorBlockedDates),
                'failed_trade_dates' => $failedDates,
                'blocked_reason_code' => $indicatorBlockedReason,
                'failure_reason_code' => $failureReason,
            ],
            'publication_reprocess_summary' => [
                'execution_state' => $publicationState,
                'republished_trade_date_count' => 0,
                'republished_trade_dates' => [],
                'candidate_trade_dates' => $publicationCandidateDates,
                'readable_correction_candidate_trade_dates' => $this->sortedUniqueList($readableCorrectionCandidateDates),
                'blocked_trade_dates' => $this->sortedUniqueList($publicationBlockedDates),
                'failed_trade_dates' => $failedDates,
                'blocked_reason_code' => $publicationBlockedReason,
                'failure_reason_code' => $failureReason,
                'republication_mode' => $republicationMode,
                'correction_ids' => [],
                'correction_id' => null,
            ],
        ];

        $this->appendExecutionEvent($originRun, $summary, $context);

        return $summary;
    }

    private function noopSummary($reasonCode)
    {
        return [
            'indicator_reprocess_execution_summary' => [
                'execution_state' => 'NOOP',
                'reprocessed_trade_date_count' => 0,
                'reprocessed_trade_dates' => [],
                'reprocess_scope' => 'NONE',
                'blocked_trade_dates' => [],
                'failed_trade_dates' => [],
                'blocked_reason_code' => $reasonCode,
                'failure_reason_code' => null,
            ],
            'eligibility_reprocess_execution_summary' => [
                'execution_state' => 'NOOP',
                'reprocessed_trade_date_count' => 0,
                'reprocessed_trade_dates' => [],
                'blocked_trade_dates' => [],
                'failed_trade_dates' => [],
                'blocked_reason_code' => $reasonCode,
                'failure_reason_code' => null,
            ],
            'publication_reprocess_summary' => [
                'execution_state' => 'NOOP',
                'republished_trade_date_count' => 0,
                'blocked_trade_dates' => [],
                'blocked_reason_code' => null,
                'failure_reason_code' => null,
                'republication_mode' => 'NOT_REQUIRED',
            ],
        ];
    }

    private function affectedTradeDates(array $indicatorImpactSummary)
    {
        $dates = array_values(array_filter(array_map('strval', (array) ($indicatorImpactSummary['affected_trade_dates'] ?? []))));

        if ($dates === []) {
            foreach (['affected_start_date', 'affected_end_date'] as $field) {
                if (! empty($indicatorImpactSummary[$field])) {
                    $dates[] = (string) $indicatorImpactSummary[$field];
                }
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        return $dates;
    }

    private function executionState(array $reprocessedDates, array $blockedDates, array $failedDates)
    {
        if ($failedDates !== []) {
            return 'FAILED';
        }

        if ($blockedDates !== []) {
            return 'BLOCKED';
        }

        return $reprocessedDates === [] ? 'NOOP' : 'EXECUTED';
    }

    private function pendingRepublicationMode(array $nonReadableDates, array $readableCorrectionDates)
    {
        if ($nonReadableDates !== [] && $readableCorrectionDates !== []) {
            return 'PENDING_MIXED_IMPACT_REPUBLICATION';
        }

        if ($readableCorrectionDates !== []) {
            return 'PENDING_READABLE_CORRECTION';
        }

        return 'PENDING_LIFECYCLE_PROMOTE';
    }

    private function sortedUniqueList(array $values)
    {
        $values = array_values(array_unique(array_filter(array_map('strval', $values), function ($value) {
            return trim($value) !== '';
        })));
        sort($values);

        return $values;
    }

    private function appendExecutionEvent($originRun, array $summary, array $context)
    {
        try {
            $state = (string) ($summary['indicator_reprocess_execution_summary']['execution_state'] ?? 'NOOP');
            $this->runs->appendEvent(
                $originRun,
                'INGEST_BARS',
                'IMPACT_REPROCESS_EXECUTED',
                $state === 'EXECUTED' ? 'INFO' : 'WARNING',
                'Affected market-data derived artifacts were reprocessed or blocked after EOD bar mutation.',
                $summary['publication_reprocess_summary']['blocked_reason_code'] ?? $summary['indicator_reprocess_execution_summary']['failure_reason_code'] ?? null,
                $summary + [
                    'source_mode' => $context['source_mode'] ?? null,
                    'requested_date' => $context['requested_date'] ?? null,
                    'origin_run_id' => (int) ($originRun->run_id ?? 0),
                ]
            );
        } catch (\Throwable $e) {
            // Reprocess execution has already returned explicit state; event logging must not
            // mask the import outcome in environments that use lightweight repository fakes.
        }
    }

    private function reasonCodeFromThrowable(\Throwable $e, $fallback)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return $fallback;
    }
}
