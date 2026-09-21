<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** C09 ancillary/contamination materialization at the indicator/eligibility producers. */
final class ProducerAncillaryCapture
{
    private const AUTHORITATIVE_SECTOR_CLASSES = ['EXCHANGE_AUTHORITATIVE', 'OPERATOR_ENTERED'];

    public const KEYS = [
        'market_benchmarks' => 'benchmark_id',
        'market_data_sectors' => 'sector_code',
        'ticker_sector_memberships' => 'membership_id',
        'market_data_corporate_action_types' => 'action_type_code',
        'md_corporate_action_revisions' => 'corporate_action_revision_id',
        'market_data_corporate_actions' => 'corporate_action_id',
        'market_data_trading_status_event_types' => 'event_type_code',
        'market_data_trading_status_events' => 'trading_status_id',
        'md_price_scale_break_candidates' => 'candidate_id',
        'md_price_scale_break_candidate_reviews' => 'candidate_review_id',
        'market_data_price_scale_breaks' => 'price_scale_break_id',
        'md_adjustment_factors' => 'adjustment_factor_id',
    ];

    /**
     * Wraps the already-computed indicator-stage ancillary bundle (benchmark, sector, event-risk,
     * corporate-action contamination, price-scale-break contamination). $produce returns that bundle
     * unchanged; this method retains the source populations behind it and independently re-derives
     * each consumed value from the captured population before admitting the capture.
     */
    public static function executeIndicatorDependencies($run, $publicationId, $date, array $tickerIds, array $tradingDates, array $factorsByTicker, array $heldEventsByTicker, array $engaged, callable $produce): void
    {
        $selection = ['operation' => 'ancillary-source-revisions/v1', 'domain' => 'indicator_dependencies',
            'publication_id' => (int) $publicationId, 'trade_date' => (string) $date,
            'known_at' => (string) ProducerInputScope::knownAt($run->knowledge_cutoff_at ?? null),
            'engaged' => ['benchmark' => (bool) ($engaged['benchmark'] ?? false), 'sector' => (bool) ($engaged['sector'] ?? false), 'event_risk' => (bool) ($engaged['event_risk'] ?? false)],
            'ticker_ids' => array_values(array_unique(array_map('intval', $tickerIds)))];
        sort($selection['ticker_ids'], SORT_NUMERIC);
        ProducerInputScope::inputRead('ancillary', $selection, function () use ($date, $tradingDates, $tickerIds, $factorsByTicker, $heldEventsByTicker, $produce, $selection) {
            if (ProducerInputScope::historical()) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_HISTORICAL_BINDING_REQUIRED');
            $tables = [];
            foreach (self::KEYS as $table => $key) $tables[$table] = self::rows($table, $key);
            $listingByTicker = self::listingByTicker($tickerIds);
            $benchmarkBars = [];
            foreach ($tables['market_benchmarks'] as $b) {
                if ((int) $b['is_active'] !== 1) continue;
                $benchmarkBars[$b['benchmark_code']] = self::rows('market_benchmark_bars', 'benchmark_bar_id',
                    DB::table('market_benchmark_bars')->where('benchmark_code', $b['benchmark_code']));
            }
            $benchmarkIndicatorRows = self::rows('market_benchmark_indicators', 'benchmark_indicator_id',
                DB::table('market_benchmark_indicators')->where('trade_date', $date));
            $consumed = $produce();
            $p = ['schema' => 'producer_ancillary_indicator_v1', 'tables' => $tables,
                'table_hashes' => array_map([ProducerRawInputLineage::class, 'hash'], $tables),
                'benchmark_bars' => $benchmarkBars, 'benchmark_bars_hash' => array_map([ProducerRawInputLineage::class, 'hash'], $benchmarkBars),
                'benchmark_indicator_rows' => $benchmarkIndicatorRows,
                'listing_by_ticker' => $listingByTicker, 'trading_dates' => array_values($tradingDates),
                'requested_ticker_ids' => array_values(array_unique(array_map('intval', $tickerIds))),
                'factors_by_ticker' => $factorsByTicker, 'held_events_by_ticker' => $heldEventsByTicker,
                'consumed' => $consumed];
            self::assertValid($p, $selection, $date);
            return [$p];
        });
    }

    /** Wraps the already-computed dormancy read at the eligibility producer. */
    public static function executeDormancy($run, $date, array $tickerIds, $lookbackTradingDays, callable $produce): array
    {
        if (! ProducerInputScope::active()) return $produce();
        $selection = ['operation' => 'ancillary-source-revisions/v1', 'domain' => 'dormancy',
            'trade_date' => (string) $date, 'known_at' => (string) ProducerInputScope::knownAt($run->knowledge_cutoff_at ?? null),
            'lookback_trading_days' => (int) $lookbackTradingDays,
            'ticker_ids' => array_values(array_unique(array_map('intval', $tickerIds)))];
        sort($selection['ticker_ids'], SORT_NUMERIC);
        $captured = ProducerInputScope::inputRead('ancillary', $selection, function () use ($tickerIds, $lookbackTradingDays, $date, $produce, $selection) {
            if (ProducerInputScope::historical()) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_HISTORICAL_BINDING_REQUIRED');
            if (empty($tickerIds) || (int) $lookbackTradingDays < 1) {
                return [['schema' => 'producer_ancillary_dormancy_v1', 'window_start' => null, 'bars' => [],
                    'empty_basis' => 'NO_TICKER_OR_LOOKBACK', 'dormant_ticker_ids' => [], 'consumed' => $produce()]];
            }
            $windowStart = (new MarketCalendarRepository())->tradingDateWindowStart($date, (int) $lookbackTradingDays);
            $bars = self::rows('eod_bars', 'trade_date', DB::table('eod_bars')
                ->whereIn('ticker_id', array_values(array_unique(array_map('intval', $tickerIds))))
                ->where('trade_date', '>=', $windowStart)->where('trade_date', '<=', $date)
                ->select(['ticker_id', 'trade_date', 'volume']));
            $consumed = $produce();
            $p = ['schema' => 'producer_ancillary_dormancy_v1', 'window_start' => $windowStart,
                'bars' => $bars, 'bars_hash' => ProducerRawInputLineage::hash($bars),
                'empty_basis' => null, 'consumed' => $consumed];
            self::assertValidDormancy($p, $selection);
            return [$p];
        });
        return $captured[0]['consumed'];
    }

    private static function listingByTicker(array $tickerIds): array
    {
        $tickerIds = array_values(array_unique(array_map('intval', $tickerIds)));
        if (! $tickerIds) return [];
        $map = DB::table('md_listings')->whereIn('legacy_ticker_id', $tickerIds)->pluck('listing_id', 'legacy_ticker_id')->all();
        $out = [];
        foreach ($map as $ticker => $listing) $out[(int) $ticker] = (int) $listing;
        ksort($out, SORT_NUMERIC);
        return $out;
    }

    private static function rows(string $table, string $key, $query = null): array
    {
        return ($query ?: DB::table($table))->orderBy($key)->get()->map(static function ($r) { return (array) $r; })->all();
    }

    // ---------------------------------------------------------------- sector

    private static function sectorGovernanceSatisfied(array $row): bool
    {
        if ((string) $row['source_authority_class'] !== 'OPERATOR_ENTERED') return true;
        foreach (['operator_name', 'reason_code', 'source_ref'] as $field) if (trim((string) ($row[$field] ?? '')) === '') return false;
        return true;
    }

    private static function sectorCovers(array $row, string $tradeDate): bool
    {
        return (string) $row['effective_from'] <= $tradeDate && ($row['effective_to'] === null || (string) $row['effective_to'] >= $tradeDate);
    }

    private static function sectorActiveRevisions(array $rows): array
    {
        $superseded = [];
        foreach ($rows as $r) if (! empty($r['supersedes_membership_id'])) $superseded[(int) $r['supersedes_membership_id']] = true;
        return array_values(array_filter($rows, static function ($r) use ($superseded) { return ! isset($superseded[(int) $r['membership_id']]); }));
    }

    private static function deriveSectorContexts(array $sectors, array $memberships, array $listingByTicker, string $tradeDate, string $knownAt): array
    {
        $classificationSystem = strtoupper(trim((string) config('market_data.sectors.classification_system', 'IDX-IC')));
        $sectorsByCode = [];
        foreach ($sectors as $s) if ($s['classification_system'] === $classificationSystem) $sectorsByCode[$s['sector_code']] = $s;
        $byListing = [];
        foreach ($memberships as $m) {
            if ($m['classification_system'] !== $classificationSystem) continue;
            if ((string) $m['recorded_at'] > $knownAt) continue;
            if (! in_array($m['source_authority_class'], self::AUTHORITATIVE_SECTOR_CLASSES, true)) continue;
            $byListing[(int) $m['listing_id']][] = $m;
        }
        $contexts = [];
        foreach ($listingByTicker as $tickerId => $listingId) {
            $stored = $byListing[$listingId] ?? [];
            $governed = array_values(array_filter($stored, [self::class, 'sectorGovernanceSatisfied']));
            $refused = array_values(array_filter($stored, static function ($r) use ($tradeDate) {
                return ! self::sectorGovernanceSatisfied($r) && self::sectorCovers($r, $tradeDate);
            }));
            $active = self::sectorActiveRevisions($governed);
            $covering = array_values(array_filter($active, static function ($r) use ($tradeDate) { return self::sectorCovers($r, $tradeDate); }));
            if (count($covering) !== 1) {
                $reason = count($covering) > 1 ? 'SECTOR_MEMBERSHIP_OVERLAP_INVALID' : ($refused === [] ? 'SECTOR_MEMBERSHIP_UNKNOWN' : 'SECTOR_OPERATOR_GOVERNANCE_INCOMPLETE');
                $contexts[$tickerId] = ['sector_code' => 'UNKNOWN', 'sector_index_code' => null, 'sector_membership_id' => null,
                    'listing_id' => null, 'source_authority_class' => null, 'membership_recorded_at' => null,
                    'resolution_state' => 'UNKNOWN', 'resolution_reason_code' => $reason];
                continue;
            }
            $row = $covering[0];
            $sector = $sectorsByCode[$row['sector_code']] ?? null;
            $sectorIndexCode = $sector && $sector['sector_index_code'] !== null ? strtoupper(trim((string) $sector['sector_index_code'])) : null;
            $contexts[$tickerId] = ['sector_code' => strtoupper(trim((string) $row['sector_code'])),
                'sector_index_code' => $sectorIndexCode !== '' ? $sectorIndexCode : null,
                'sector_membership_id' => (int) $row['membership_id'], 'listing_id' => (int) $row['listing_id'],
                'source_authority_class' => (string) $row['source_authority_class'], 'membership_recorded_at' => (string) $row['recorded_at'],
                'resolution_state' => 'RESOLVED_AUTHORITATIVE', 'resolution_reason_code' => null];
        }
        ksort($contexts, SORT_NUMERIC);
        return $contexts;
    }

    // ------------------------------------------------------------ event risk

    private static function emptyEventRiskContext(): array
    {
        return ['corporate_action_flag' => null, 'corporate_action_types' => null, 'trading_status_code' => null,
            'bar_expectation_state' => null, 'trading_status_revision_id' => null, 'trading_status_source_observation_id' => null,
            'trading_status_authority_class' => null, 'is_suspended' => null, 'is_uma' => null, 'expectation_unknown' => null,
            'event_risk_flag' => null, 'event_risk_reasons' => null,
            '_corporate_action_types' => [], '_corporate_action_revision_ids' => [], '_corporate_action_verification_states' => [],
            '_trading_status_codes' => [], '_trading_status_exact_codes' => [], '_event_risk_reasons' => []];
    }

    /** Faithful port of EventRiskSourceRepository::resolveEventRiskContextForTickerIds, reading captured arrays. */
    private static function deriveEventRiskContexts(array $revisions, array $types, array $legacyActions, array $legacyStatuses, array $statusTypes, array $listingByTicker, array $requestedTickerIds, string $tradeDate, string $knownAt): array
    {
        $requested = array_fill_keys($requestedTickerIds, true);
        $listingToTicker = array_flip($listingByTicker);
        $contexts = [];
        $v2Ticker = [];
        foreach ($revisions as $r) {
            $tickerId = $listingToTicker[(int) $r['listing_id']] ?? null;
            if ($tickerId === null) continue;
            if ($r['ex_date'] !== $tradeDate || (string) $r['lifecycle_state'] === 'CANCELLED') continue;
            if ((string) $r['recorded_at'] > $knownAt) continue;
            $superseded = false;
            foreach ($revisions as $n) if ((string) $n['supersedes_revision_id'] === (string) $r['corporate_action_revision_id'] && (string) $n['recorded_at'] <= $knownAt) { $superseded = true; break; }
            if ($superseded) continue;
            $v2Ticker[$tickerId] = true;
            $c = $contexts[$tickerId] ?? self::emptyEventRiskContext();
            $actionType = strtoupper(trim((string) $r['action_type_code']));
            if ($actionType !== '') {
                $c['corporate_action_flag'] = 1;
                $c['_corporate_action_types'][$actionType] = true;
                $c['_event_risk_reasons']['CORPORATE_ACTION_REVISION:'.$actionType.':'.(int) $r['corporate_action_revision_id']] = true;
                $c['event_risk_flag'] = 1;
                $c['_corporate_action_revision_ids'][(int) $r['corporate_action_revision_id']] = true;
                $c['_corporate_action_verification_states'][(string) $r['verification_state']] = true;
            }
            $contexts[$tickerId] = $c;
        }
        foreach ($legacyActions as $r) {
            $tickerId = (int) $r['ticker_id'];
            if (! isset($requested[$tickerId]) || isset($v2Ticker[$tickerId])) continue;
            if ($r['recorded_at'] === null || (string) $r['recorded_at'] > $knownAt) continue;
            $matches = $r['ex_date'] === $tradeDate || ($r['ex_date'] === null && $r['action_date'] === $tradeDate);
            if (! $matches) continue;
            $c = $contexts[$tickerId] ?? self::emptyEventRiskContext();
            $actionType = strtoupper(trim((string) $r['action_type']));
            if ($actionType !== '') {
                $c['corporate_action_flag'] = 1;
                $c['_corporate_action_types'][$actionType] = true;
                $c['_event_risk_reasons']['CORPORATE_ACTION_LEGACY_UNVERIFIED:'.$actionType] = true;
                $c['_corporate_action_verification_states']['LEGACY_UNVERIFIED'] = true;
                $c['event_risk_flag'] = 1;
            }
            $contexts[$tickerId] = $c;
        }
        $verifiedTicker = [];
        foreach ($listingByTicker as $tickerId => $listingId) {
            $resolved = (new TemporalTradingStatusRepository())->resolveForListing($listingId, $tradeDate, $knownAt);
            if (! ($resolved['had_records'] ?? false)) continue;
            $verifiedTicker[$tickerId] = true;
        }
        $carry = [];
        usort($legacyStatuses, static function ($a, $b) {
            return [$a['ticker_id'], $a['trade_date'], $a['event_type_code']] <=> [$b['ticker_id'], $b['trade_date'], $b['event_type_code']];
        });
        foreach ($legacyStatuses as $r) {
            $tickerId = (int) $r['ticker_id'];
            if (! isset($requested[$tickerId]) || isset($verifiedTicker[$tickerId])) continue;
            if ($r['recorded_at'] === null || (string) $r['recorded_at'] > $knownAt) continue;
            if ((string) $r['trade_date'] > $tradeDate) continue;
            $code = strtoupper(trim((string) $r['event_type_code']));
            $type = null;
            foreach ($statusTypes as $t) if ($t['event_type_code'] === $code) { $type = $t; break; }
            if ($type === null) {
                $c = $contexts[$tickerId] ?? self::emptyEventRiskContext();
                $c['expectation_unknown'] = 1; $c['event_risk_flag'] = 1;
                $c['_event_risk_reasons']['TRADING_STATUS_TYPE_UNMAPPED:'.$code] = true;
                $contexts[$tickerId] = $c;
                continue;
            }
            if ((string) $r['trade_date'] === $tradeDate) {
                $c = $contexts[$tickerId] ?? self::emptyEventRiskContext();
                $c = self::applyTradingStatusEvent($c, $code, $type, true);
                $contexts[$tickerId] = $c;
            }
            $carry[$tickerId] = self::applyCarryForward($carry[$tickerId] ?? [], $code, $type);
        }
        foreach ($carry as $tickerId => $state) {
            $c = $contexts[$tickerId] ?? self::emptyEventRiskContext();
            foreach ($state as $code => $type) $c = self::applyTradingStatusEvent($c, $code, $type, false);
            if (! empty($c['_trading_status_codes']) || $c['is_suspended'] !== null || $c['is_uma'] !== null) $contexts[$tickerId] = $c;
        }
        foreach ($listingByTicker as $tickerId => $listingId) {
            $resolved = $verifiedTicker[$tickerId] ?? null;
            if ($resolved === null) continue;
            $status = (new TemporalTradingStatusRepository())->resolveForListing($listingId, $tradeDate, $knownAt);
            $expectation = (string) $status['bar_expectation_state'];
            $knownExpectation = in_array($expectation, ['BAR_EXPECTED', 'BAR_NOT_EXPECTED'], true);
            $statusCode = strtoupper(trim((string) $status['status_code']));
            $isVerifiedNotExpected = $expectation === 'BAR_NOT_EXPECTED' && (bool) ($status['full_session_verified'] ?? false);
            $c = $contexts[$tickerId] ?? self::emptyEventRiskContext();
            $c['trading_status_code'] = $statusCode; $c['bar_expectation_state'] = $expectation;
            $c['trading_status_revision_id'] = $knownExpectation ? $status['status_revision_id'] : null;
            $c['trading_status_source_observation_id'] = $knownExpectation ? $status['source_observation_id'] : null;
            $c['trading_status_authority_class'] = $knownExpectation ? (string) $status['authority_class'] : null;
            $c['is_suspended'] = $isVerifiedNotExpected && strpos($statusCode, 'SUSPENS') !== false ? 1 : 0;
            $c['expectation_unknown'] = $knownExpectation ? 0 : 1;
            if ((int) $c['is_suspended'] === 1) {
                $c['_trading_status_codes']['SUSPENSION_OBSERVED'] = true;
                $c['_event_risk_reasons']['TRADING_STATUS:SUSPENSION_OBSERVED'] = true;
                $c['_event_risk_reasons']['SUSPENDED'] = true;
                $c['event_risk_flag'] = 1;
            }
            $contexts[$tickerId] = $c;
        }
        foreach ($contexts as $tickerId => $c) $contexts[$tickerId] = self::finalizeEventRiskContext($c);
        ksort($contexts, SORT_NUMERIC);
        return $contexts;
    }

    private static function applyTradingStatusEvent(array $context, string $code, array $type, bool $isExact): array
    {
        $context['_trading_status_codes'][$code] = true;
        if ($isExact) $context['_trading_status_exact_codes'][$code] = true;
        if ($type['risk_family'] === 'SUSPENSION') $context['is_suspended'] = in_array($type['transition_type'], ['START', 'OBSERVED'], true) ? 1 : 0;
        if ($code === 'UMA') $context['is_uma'] = 1;
        if (in_array($type['expected_bar_policy'] ?? 'BAR_REQUIRED', ['BAR_NOT_REQUIRED', 'BAR_REQUIRED_WITH_RISK'], true)) {
            $context['event_risk_flag'] = 1;
            $context['_event_risk_reasons']['TRADING_STATUS:'.$code] = true;
            if ($type['risk_family'] === 'SUSPENSION') $context['_event_risk_reasons']['SUSPENDED'] = true;
            if ($code === 'UMA') $context['_event_risk_reasons']['UMA'] = true;
        }
        return $context;
    }

    private static function applyCarryForward(array $state, string $code, array $type): array
    {
        if ($type['transition_type'] === 'END' && $type['clears_risk_family'] !== null) {
            foreach ($state as $activeCode => $active) if ($active['risk_family'] === $type['clears_risk_family']) unset($state[$activeCode]);
            return $state;
        }
        if ((int) $type['carries_forward'] === 1) {
            foreach ($state as $activeCode => $active) if ($active['risk_family'] === $type['risk_family']) unset($state[$activeCode]);
            $state[$code] = $type;
        }
        ksort($state);
        return $state;
    }

    private static function finalizeEventRiskContext(array $context): array
    {
        $corporateActionTypes = array_keys($context['_corporate_action_types']);
        $corporateActionRevisionIds = array_keys($context['_corporate_action_revision_ids']);
        $corporateActionVerificationStates = array_keys($context['_corporate_action_verification_states']);
        $tradingStatusCodes = array_keys($context['_trading_status_codes']);
        $exactTradingStatusCodes = array_keys($context['_trading_status_exact_codes']);
        $eventRiskReasons = array_keys($context['_event_risk_reasons']);
        sort($corporateActionTypes); sort($corporateActionRevisionIds, SORT_NUMERIC); sort($corporateActionVerificationStates);
        sort($tradingStatusCodes); sort($exactTradingStatusCodes); sort($eventRiskReasons);
        $context['corporate_action_types'] = ! empty($corporateActionTypes) ? implode(',', $corporateActionTypes) : null;
        $context['corporate_action_revision_ids'] = ! empty($corporateActionRevisionIds) ? implode(',', $corporateActionRevisionIds) : null;
        $context['corporate_action_verification_states'] = ! empty($corporateActionVerificationStates) ? implode(',', $corporateActionVerificationStates) : null;
        $context['trading_status_code'] = self::primaryStatusCode($tradingStatusCodes, $exactTradingStatusCodes);
        $context['event_risk_reasons'] = ! empty($eventRiskReasons) ? implode(',', $eventRiskReasons) : null;
        if (! empty($tradingStatusCodes)) {
            $context['is_suspended'] = $context['is_suspended'] !== null ? $context['is_suspended'] : 0;
            $context['is_uma'] = $context['is_uma'] !== null ? $context['is_uma'] : 0;
        }
        if ($context['event_risk_flag'] === null && (! empty($tradingStatusCodes) || $context['is_suspended'] !== null || $context['is_uma'] !== null)) $context['event_risk_flag'] = 0;
        unset($context['_corporate_action_types'], $context['_corporate_action_revision_ids'], $context['_corporate_action_verification_states'],
            $context['_trading_status_codes'], $context['_trading_status_exact_codes'], $context['_event_risk_reasons']);
        return $context;
    }

    private static function primaryStatusCode(array $codes, array $exact): ?string
    {
        $priority = ['SUSPENDED', 'SUSPENSION_OBSERVED', 'UNSUSPENDED', 'SPECIAL_MONITORING_START', 'SPECIAL_MONITORING_END', 'UMA'];
        $exactSet = array_fill_keys($exact, true);
        foreach ($priority as $code) if (isset($exactSet[$code])) return $code;
        $set = array_fill_keys($codes, true);
        foreach ($priority as $code) if (isset($set[$code])) return $code;
        sort($codes);
        return $codes[0] ?? null;
    }

    // ------------------------------------------------------- contamination

    private static function deriveContamination(array $revisions, array $types, array $legacyActions, array $listingByTicker, array $tradingDates, string $knownAt): array
    {
        if (empty($tradingDates)) return [];
        $start = $tradingDates[0]; $end = $tradingDates[count($tradingDates) - 1];
        $typesByCode = []; foreach ($types as $t) $typesByCode[$t['action_type_code']] = $t;
        $listingToTicker = array_flip($listingByTicker);
        $contamination = []; $covered = [];
        $revisions = array_values($revisions);
        usort($revisions, static function ($a, $b) use ($listingToTicker) {
            $ta = $listingToTicker[(int) $a['listing_id']] ?? PHP_INT_MAX; $tb = $listingToTicker[(int) $b['listing_id']] ?? PHP_INT_MAX;
            return [$ta, (string) $a['ex_date']] <=> [$tb, (string) $b['ex_date']];
        });
        foreach ($revisions as $r) {
            $tickerId = $listingToTicker[(int) $r['listing_id']] ?? null;
            if ($tickerId === null || $r['ex_date'] === null || (string) $r['lifecycle_state'] === 'CANCELLED') continue;
            $anchor = (string) $r['ex_date'];
            if ($anchor < $start || $anchor > $end) continue;
            if ($r['recorded_at'] === null || (string) $r['recorded_at'] > $knownAt) continue;
            $superseded = false;
            foreach ($revisions as $n) if ((string) $n['supersedes_revision_id'] === (string) $r['corporate_action_revision_id'] && (string) $n['recorded_at'] <= $knownAt) { $superseded = true; break; }
            if ($superseded) continue;
            $depth = self::tradingDayDepth($tradingDates, $anchor);
            if ($depth === null) continue;
            $type = $typesByCode[$r['action_type_code']] ?? ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'];
            $breaksPrice = $type['price_continuity_impact'] !== 'NONE'; $breaksVolume = $type['volume_continuity_impact'] !== 'NONE';
            if (! $breaksPrice && ! $breaksVolume) continue;
            $covered[$tickerId.'|'.$anchor.'|'.$r['action_type_code']] = true;
            $contamination[$tickerId][] = ['corporate_action_revision_id' => (int) $r['corporate_action_revision_id'],
                'action_type_code' => $r['action_type_code'], 'verification_state' => $r['verification_state'],
                'ex_date' => $anchor, 'action_date' => $anchor, 'anchor_state' => 'VERIFIED_EX_DATE_REVISION', 'depth' => $depth,
                'breaks_price_continuity' => $breaksPrice, 'breaks_volume_continuity' => $breaksVolume, 'is_unmapped_type' => ! isset($typesByCode[$r['action_type_code']])];
        }
        $legacyActions = array_values($legacyActions);
        usort($legacyActions, static function ($a, $b) {
            return [(int) $a['ticker_id'], (string) $a['action_date']] <=> [(int) $b['ticker_id'], (string) $b['action_date']];
        });
        foreach ($legacyActions as $r) {
            $tickerId = (int) $r['ticker_id'];
            if ($r['recorded_at'] === null || (string) $r['recorded_at'] > $knownAt) continue;
            $anchor = (string) (($r['ex_date'] ?? null) ?: $r['action_date']);
            if ($anchor < $start || $anchor > $end) continue;
            if (isset($covered[$tickerId.'|'.$anchor.'|'.$r['action_type']])) continue;
            $depth = self::tradingDayDepth($tradingDates, $anchor);
            if ($depth === null) continue;
            $type = $typesByCode[$r['action_type']] ?? ['price_continuity_impact' => 'SCALED', 'volume_continuity_impact' => 'SCALED'];
            $breaksPrice = $type['price_continuity_impact'] !== 'NONE'; $breaksVolume = $type['volume_continuity_impact'] !== 'NONE';
            if (! $breaksPrice && ! $breaksVolume) continue;
            $contamination[$tickerId][] = ['corporate_action_revision_id' => null, 'action_type_code' => $r['action_type'],
                'verification_state' => 'LEGACY_UNVERIFIED', 'ex_date' => $r['ex_date'] ?? null, 'action_date' => $anchor,
                'anchor_state' => $r['ex_date'] ? 'LEGACY_EXPLICIT_EX_DATE' : 'LEGACY_ACTION_DATE_RISK_ONLY', 'depth' => $depth,
                'breaks_price_continuity' => $breaksPrice, 'breaks_volume_continuity' => $breaksVolume, 'is_unmapped_type' => ! isset($typesByCode[$r['action_type']])];
        }
        // Apply the same post-processing the real producer applies: an event whose revision was
        // actually applied as a factor is removed from contamination, and held events are merged in.
        return $contamination;
    }

    public static function applyFactorPostProcessing(array $contaminationByTicker, array $factorsByTicker, array $heldEventsByTicker, array $tradingDates): array
    {
        foreach ($factorsByTicker as $tickerId => $factorRows) {
            $applied = array_fill_keys(array_map(static function (array $f) { return (int) ($f['corporate_action_revision_id'] ?? 0); }, $factorRows), true);
            if (! isset($contaminationByTicker[(int) $tickerId])) continue;
            $contaminationByTicker[(int) $tickerId] = array_values(array_filter($contaminationByTicker[(int) $tickerId], static function (array $e) use ($applied) {
                $id = (int) ($e['corporate_action_revision_id'] ?? 0);
                return $id <= 0 || ! isset($applied[$id]);
            }));
        }
        foreach ($heldEventsByTicker as $tickerId => $heldEvents) {
            $held = array_values(array_filter(array_map(static function (array $e) use ($tradingDates) {
                $depth = self::tradingDayDepth($tradingDates, (string) ($e['action_date'] ?? ''));
                if ($depth === null) return null;
                $e['depth'] = $depth;
                return $e;
            }, $heldEvents)));
            $contaminationByTicker[(int) $tickerId] = array_merge($contaminationByTicker[(int) $tickerId] ?? [], $held);
        }
        return $contaminationByTicker;
    }

    private static function tradingDayDepth(array $tradingDates, string $actionDate): ?int
    {
        $last = count($tradingDates) - 1;
        for ($i = 0; $i <= $last; $i++) if ($tradingDates[$i] >= $actionDate) return $last - $i;
        return null;
    }

    // ---------------------------------------------------- price scale break

    private static function derivePriceScaleBreaks(array $candidates, array $reviews, array $legacy, array $appliedFactorRevisionIds, array $listingByTicker, array $tradingDates): array
    {
        if (empty($tradingDates)) return [];
        $last = count($tradingDates) - 1; $depthByDate = [];
        foreach ($tradingDates as $i => $d) $depthByDate[$d] = $last - $i;
        $listingToTicker = array_flip($listingByTicker);
        $latestReview = [];
        foreach ($reviews as $r) {
            $isLatest = true;
            foreach ($reviews as $n) if ((string) ($n['supersedes_review_id'] ?? '') === (string) $r['candidate_review_id']) { $isLatest = false; break; }
            if ($isLatest) $latestReview[(int) $r['candidate_id']] = $r;
        }
        $contamination = []; $covered = [];
        foreach ($candidates as $c) {
            $tickerId = $listingToTicker[(int) $c['listing_id']] ?? null;
            if ($tickerId === null) continue;
            $date = (string) $c['current_trade_date'];
            if (! isset($depthByDate[$date])) continue;
            $covered[$tickerId.'|'.$date] = true;
            $review = $latestReview[(int) $c['candidate_id']]['review_state'] ?? ($c['review_state'] ?: 'DETECTED');
            if ($review === 'DISMISSED') continue;
            $reviewedRevisionId = (int) ($latestReview[(int) $c['candidate_id']]['corporate_action_revision_id'] ?? 0);
            if ($review === 'LINKED_VERIFIED_FACTOR' && isset($appliedFactorRevisionIds[$reviewedRevisionId])) continue;
            $contamination[$tickerId][] = ['break_type' => $c['candidate_classification'], 'trade_date' => $date, 'depth' => $depthByDate[$date],
                'implied_ratio' => $c['diagnostic_ratio'], 'inferred_ratio' => $c['inferred_ratio'], 'match_status' => $c['linkage_state'],
                'matched_action_type' => null, 'candidate_uid' => $c['candidate_uid'], 'continuity_verdict' => $c['continuity_verdict']];
        }
        foreach ($legacy as $r) {
            $tickerId = (int) $r['ticker_id']; $date = (string) $r['trade_date'];
            if (! isset($depthByDate[$date]) || isset($covered[$tickerId.'|'.$date])) continue;
            if (! in_array($r['review_status'], ['DETECTED', 'CONFIRMED'], true)) continue;
            $contamination[$tickerId][] = ['break_type' => $r['break_type'], 'trade_date' => $date, 'depth' => $depthByDate[$date],
                'implied_ratio' => $r['implied_ratio'], 'inferred_ratio' => $r['inferred_ratio'], 'match_status' => 'LEGACY_UNVERIFIED',
                'matched_action_type' => $r['matched_action_type'], 'continuity_verdict' => 'LEGACY_UNVERIFIED_QUARANTINE'];
        }
        ksort($contamination, SORT_NUMERIC);
        return $contamination;
    }

    // --------------------------------------------------------------- assert

    public static function assertValid(array $p, array $selection, string $date): void
    {
        if (($p['schema'] ?? null) !== 'producer_ancillary_indicator_v1') throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_SCHEMA');
        foreach (self::KEYS as $table => $key) {
            if (($p['table_hashes'][$table] ?? null) !== ProducerRawInputLineage::hash($p['tables'][$table] ?? [])) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_POPULATION_HASH: '.$table);
        }
        foreach ($p['benchmark_bars'] as $code => $rows) {
            if (($p['benchmark_bars_hash'][$code] ?? null) !== ProducerRawInputLineage::hash($rows)) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_BENCHMARK_BARS_HASH: '.$code);
        }
        $knownAt = (string) $selection['known_at'];
        $listingByTicker = $p['listing_by_ticker'];
        $consumed = $p['consumed'];
        $engaged = $selection['engaged'] ?? ['benchmark' => true, 'sector' => true, 'event_risk' => true];

        // benchmark: active set + roc values must trace to a retained market_benchmark_indicators row
        if ($engaged['benchmark']) {
            $activeBenchmarks = [];
            foreach ($p['tables']['market_benchmarks'] as $b) if ((int) $b['is_active'] === 1) $activeBenchmarks[$b['benchmark_code']] = true;
            $indicatorRows = [];
            foreach ($p['benchmark_indicator_rows'] as $row) $indicatorRows[$row['benchmark_code']] = $row;
            if (array_key_exists('benchmark_roc20', $consumed) && $consumed['benchmark_roc20'] !== null) {
                $row = $indicatorRows['IHSG'] ?? null;
                if (! isset($activeBenchmarks['IHSG']) || $row === null || $row['roc_20'] === null || (float) $row['roc_20'] !== (float) $consumed['benchmark_roc20']) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_BENCHMARK_ROC_MISMATCH: IHSG');
            }
            foreach ($consumed['sector_benchmark_roc20s'] ?? [] as $code => $value) {
                $row = $indicatorRows[$code] ?? null;
                if (! isset($activeBenchmarks[$code]) || $row === null || $row['roc_20'] === null || (float) $row['roc_20'] !== (float) $value) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_BENCHMARK_ROC_MISMATCH: '.$code);
            }
        } elseif (($consumed['benchmark_roc20'] ?? null) !== null || ($consumed['sector_benchmark_roc20s'] ?? []) !== []) {
            throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_BENCHMARK_UNEXPECTED_WHEN_DISENGAGED');
        }

        // sector
        if ($engaged['sector']) {
            $expectedSector = self::deriveSectorContexts($p['tables']['market_data_sectors'], $p['tables']['ticker_sector_memberships'], $listingByTicker, $date, $knownAt);
            self::same($expectedSector, $consumed['sector_contexts'] ?? [], 'SECTOR_CONTEXTS');
        } elseif (($consumed['sector_contexts'] ?? []) !== []) {
            throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_SECTOR_UNEXPECTED_WHEN_DISENGAGED');
        }

        // event risk + contamination (both gated by the same $this->eventRisks null check in production)
        if ($engaged['event_risk']) {
            $expectedEventRisk = self::deriveEventRiskContexts($p['tables']['md_corporate_action_revisions'], $p['tables']['market_data_corporate_action_types'],
                $p['tables']['market_data_corporate_actions'], $p['tables']['market_data_trading_status_events'], $p['tables']['market_data_trading_status_event_types'],
                $listingByTicker, $p['requested_ticker_ids'], $date, $knownAt);
            self::same($expectedEventRisk, $consumed['event_risk_contexts'] ?? [], 'EVENT_RISK_CONTEXTS');

            $expectedContamination = self::deriveContamination($p['tables']['md_corporate_action_revisions'], $p['tables']['market_data_corporate_action_types'],
                $p['tables']['market_data_corporate_actions'], $listingByTicker, $p['trading_dates'], $knownAt);
            $expectedContamination = self::applyFactorPostProcessing($expectedContamination, $p['factors_by_ticker'], $p['held_events_by_ticker'], $p['trading_dates']);
            self::same($expectedContamination, $consumed['contamination'] ?? [], 'CONTAMINATION');
        } elseif (($consumed['event_risk_contexts'] ?? []) !== [] || ($consumed['contamination'] ?? []) !== []) {
            throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_EVENT_RISK_UNEXPECTED_WHEN_DISENGAGED');
        }

        // price scale break
        $appliedFactorRevisionIds = [];
        foreach ($p['tables']['md_adjustment_factors'] as $f) $appliedFactorRevisionIds[(int) $f['corporate_action_revision_id']] = true;
        $expectedBreaks = self::derivePriceScaleBreaks($p['tables']['md_price_scale_break_candidates'], $p['tables']['md_price_scale_break_candidate_reviews'],
            $p['tables']['market_data_price_scale_breaks'], $appliedFactorRevisionIds, $listingByTicker, $p['trading_dates']);
        self::same($expectedBreaks, $consumed['price_scale_breaks'] ?? [], 'PRICE_SCALE_BREAKS');
    }

    public static function assertValidDormancy(array $p, array $selection): void
    {
        if (($p['schema'] ?? null) !== 'producer_ancillary_dormancy_v1') throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_SCHEMA');
        if (($p['empty_basis'] ?? null) === 'NO_TICKER_OR_LOOKBACK') return;
        if (($p['bars_hash'] ?? null) !== ProducerRawInputLineage::hash($p['bars'])) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_DORMANCY_HASH');
        $active = [];
        foreach ($p['bars'] as $row) if ((float) $row['volume'] > 0) $active[(int) $row['ticker_id']] = true;
        $dormant = array_values(array_filter($selection['ticker_ids'], static function ($id) use ($active) { return ! isset($active[$id]); }));
        sort($dormant, SORT_NUMERIC);
        $actual = $p['consumed'];
        sort($actual, SORT_NUMERIC);
        if ($dormant !== $actual) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_DORMANCY_MISMATCH');
    }

    private static function same($a, $b, string $part): void
    {
        if (RunInputCaptureRepository::canonicalJson($a) !== RunInputCaptureRepository::canonicalJson($b)) throw new \RuntimeException('INPUT_CAPTURE_ANCILLARY_'.$part.'_MISMATCH');
    }
}
