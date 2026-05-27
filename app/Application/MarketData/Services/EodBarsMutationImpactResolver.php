<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;

class EodBarsMutationImpactResolver
{
    private $calendar;
    private $artifacts;
    private $publications;

    public function __construct(
        MarketCalendarRepository $calendar,
        EodArtifactRepository $artifacts,
        EodPublicationRepository $publications
    ) {
        $this->calendar = $calendar;
        $this->artifacts = $artifacts;
        $this->publications = $publications;
    }

    public function resolve(array $barMutationSummary, $requestedDate = null)
    {
        $changedBarCount = (int) ($barMutationSummary['changed_bar_count'] ?? 0);
        $maxDependencyTradingDays = $this->maxDependencyTradingDays();

        if ($changedBarCount <= 0) {
            return [
                'bar_mutation_summary' => $this->normalizeBarMutationSummary($barMutationSummary),
                'indicator_impact_summary' => [
                    'affected_ticker_count' => 0,
                    'affected_trade_date_count' => 0,
                    'affected_start_date' => null,
                    'affected_end_date' => null,
                    'affected_trade_dates' => [],
                    'affected_ticker_ids' => [],
                    'max_dependency_trading_days' => $maxDependencyTradingDays,
                    'impact_reason' => 'UNCHANGED_BARS',
                    'indicator_reprocess_state' => 'NOOP_UNCHANGED_BARS',
                ],
                'publication_impact_summary' => [
                    'readable_publication_impacted' => false,
                    'impacted_readable_trade_dates' => [],
                    'republication_required' => false,
                    'publication_impact_state' => 'NOOP',
                ],
            ];
        }

        $changedDates = $this->normalizeDateList($barMutationSummary['changed_trade_dates'] ?? []);
        if ($changedDates === [] && $requestedDate !== null && $requestedDate !== '') {
            $changedDates = [(string) $requestedDate];
        }

        sort($changedDates);
        $changedTickerIds = $this->normalizeIntList($barMutationSummary['changed_ticker_ids'] ?? []);
        $affectedDates = $this->affectedTradingDates($changedDates, $maxDependencyTradingDays);
        $readableDates = $this->readablePublicationDates($affectedDates);
        $changedDateSet = array_fill_keys($changedDates, true);
        $hasDownstreamImpact = count(array_filter($affectedDates, function ($date) use ($changedDateSet) {
            return ! isset($changedDateSet[$date]);
        })) > 0;

        $indicatorState = $hasDownstreamImpact ? 'REPROCESS_REQUIRED_WITH_DOWNSTREAM_IMPACT' : 'REPROCESS_REQUIRED_REQUESTED_DATES_ONLY';
        $publicationState = $readableDates === [] ? 'NOOP' : 'REQUIRES_REPUBLICATION';

        return [
            'bar_mutation_summary' => $this->normalizeBarMutationSummary($barMutationSummary),
            'indicator_impact_summary' => [
                'affected_ticker_count' => count($changedTickerIds),
                'affected_trade_date_count' => count($affectedDates),
                'affected_start_date' => $affectedDates[0] ?? null,
                'affected_end_date' => $affectedDates === [] ? null : $affectedDates[count($affectedDates) - 1],
                'affected_trade_dates' => $affectedDates,
                'affected_ticker_ids' => $changedTickerIds,
                'max_dependency_trading_days' => $maxDependencyTradingDays,
                'impact_reason' => $hasDownstreamImpact ? 'HISTORICAL_BAR_CHANGED_DOWNSTREAM_INDICATOR_DEPENDENCY' : 'BAR_CHANGED_REQUESTED_DATE_ONLY',
                'indicator_reprocess_state' => $indicatorState,
            ],
            'publication_impact_summary' => [
                'readable_publication_impacted' => $readableDates !== [],
                'impacted_readable_trade_dates' => $readableDates,
                'republication_required' => $readableDates !== [],
                'publication_impact_state' => $publicationState,
                'reason_code' => $readableDates !== [] ? 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION' : null,
            ],
        ];
    }

    public function maxDependencyTradingDays()
    {
        $config = (array) config('market_data.indicators', []);

        return max(
            (int) ($config['dv_window_days'] ?? 20),
            (int) ($config['atr_window_days'] ?? 14) + 1,
            (int) ($config['vol_ratio_lookback_days'] ?? 20) + 1,
            (int) ($config['roc_lookback_days'] ?? 20) + 1,
            (int) ($config['hh_window_days'] ?? 20),
            50
        );
    }

    private function affectedTradingDates(array $changedDates, $maxDependencyTradingDays)
    {
        if ($changedDates === []) {
            return [];
        }

        $startDate = $changedDates[0];
        $availableDates = $this->artifacts->loadAvailableBarTradeDatesOnOrAfter($startDate);
        if ($availableDates === []) {
            $availableDates = $changedDates;
        }

        $lastAvailableDate = $availableDates[count($availableDates) - 1];
        $tradingDates = $this->calendar->tradingDatesBetween($startDate, $lastAvailableDate);
        if ($tradingDates === []) {
            return [];
        }

        $indexByDate = [];
        foreach ($tradingDates as $index => $date) {
            $indexByDate[(string) $date] = $index;
        }

        $endIndex = null;
        foreach ($changedDates as $changedDate) {
            if (! array_key_exists($changedDate, $indexByDate)) {
                continue;
            }

            $candidateEnd = min(count($tradingDates) - 1, $indexByDate[$changedDate] + (int) $maxDependencyTradingDays);
            $endIndex = $endIndex === null ? $candidateEnd : max($endIndex, $candidateEnd);
        }

        if ($endIndex === null) {
            return [];
        }

        return array_values(array_slice($tradingDates, 0, $endIndex + 1));
    }

    private function readablePublicationDates(array $affectedDates)
    {
        $readableDates = [];
        foreach ($affectedDates as $date) {
            $publication = $this->publications->findCurrentPublicationForTradeDate($date);
            if ($publication) {
                $readableDates[] = (string) $date;
            }
        }

        return $readableDates;
    }

    private function normalizeBarMutationSummary(array $summary)
    {
        foreach ([
            'changed_bar_count',
            'inserted_bar_count',
            'updated_bar_count',
            'unchanged_bar_count',
            'removed_bar_count',
            'changed_ticker_count',
            'changed_trade_date_count',
        ] as $field) {
            $summary[$field] = (int) ($summary[$field] ?? 0);
        }

        $summary['changed_ticker_ids'] = $this->normalizeIntList($summary['changed_ticker_ids'] ?? []);
        $summary['changed_trade_dates'] = $this->normalizeDateList($summary['changed_trade_dates'] ?? []);

        return $summary;
    }

    private function normalizeDateList(array $dates)
    {
        $normalized = [];
        foreach ($dates as $date) {
            $date = trim((string) $date);
            if ($date !== '') {
                $normalized[$date] = true;
            }
        }

        $dates = array_keys($normalized);
        sort($dates);

        return $dates;
    }

    private function normalizeIntList(array $values)
    {
        $normalized = [];
        foreach ($values as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $normalized[(int) $value] = true;
        }

        $values = array_keys($normalized);
        sort($values);

        return $values;
    }
}
