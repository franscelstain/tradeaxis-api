<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Resolve status only from temporal, source-registered, authority-bearing revisions. */
class TemporalTradingStatusRepository
{
    private $capturedPopulation;
    private $omissions = [];
    private $authorityEvaluations = [];
    private $terminalIds = [];

    public function resolveForListing($listingId, $tradeDate, $knownAt = null)
    {
        $knownAt = ProducerInputScope::knownAt($knownAt);
        if (ProducerInputScope::active()) {
            return (new ProducerTradingStatusPopulation())->resolve((int) $listingId, (string) $tradeDate, (string) $knownAt);
        }
        return $this->resolveStatus($listingId, $tradeDate, $knownAt);
    }

    /** Pure evaluation of already captured inputs, also used for content verification. */
    public static function evaluateCaptured(array $population, int $listingId, string $tradeDate, string $knownAt): array
    {
        $resolver = new self(); $resolver->capturedPopulation = $population;
        $result = $resolver->resolveStatus($listingId, $tradeDate, $knownAt);
        $selected = array_map('intval', $result['status_revision_ids']);
        foreach ($population['tables']['md_trading_status_revisions'] as $row) {
            $id = (int) $row['status_revision_id'];
            if (in_array($id, $selected, true) || isset($resolver->omissions[$id])) continue;
            $evaluation = $resolver->authorityEvaluations[$id] ?? null;
            if ($evaluation && ! $evaluation['valid']) $reason = $evaluation['reason'];
            elseif ($result['reason_code'] !== null) $reason = $result['reason_code'];
            else {
                $priority = $evaluation['priority']; $selectedPriority = $priority;
                foreach ($population['tables']['md_trading_status_revisions'] as $candidate) {
                    if ($candidate['status_type_code'] === $row['status_type_code'] && in_array((int) $candidate['status_revision_id'], $selected, true)) {
                        $selectedPriority = $resolver->authorityEvaluations[(int) $candidate['status_revision_id']]['priority'];
                    }
                }
                $reason = $priority > $selectedPriority ? 'LOWER_AUTHORITY_PRIORITY' : 'EQUIVALENT_AUTHORITY_NOT_SELECTED';
            }
            $resolver->omissions[$id] = [$reason];
        }
        ksort($resolver->omissions, SORT_NUMERIC); ksort($resolver->authorityEvaluations, SORT_NUMERIC);
        $omissions = [];
        foreach ($resolver->omissions as $id => $reasons) $omissions[] = ['status_revision_id' => $id, 'reasons' => $reasons];
        $evaluations = [];
        foreach ($resolver->authorityEvaluations as $id => $evaluation) $evaluations[] = ['status_revision_id' => $id] + $evaluation;
        return ['selection_result' => $result, 'selected_revision_ids' => $selected,
            'terminal_revision_ids' => $resolver->terminalIds, 'authority_evaluations' => $evaluations,
            'omitted_revisions' => $omissions,
            'empty_basis' => $population['tables']['md_trading_status_revisions'] === [] ? 'NO_RECORDED_STATUS_REVISIONS_AT_KNOWLEDGE_CUTOFF' : null];
    }

    private function resolveStatus($listingId, $tradeDate, $knownAt)
    {
        $identity = $this->listingIdentity((int) $listingId, $tradeDate, $knownAt);
        if ($identity === null) {
            return $this->unknown('TRADING_STATUS_STABLE_MAPPING_MISSING', false);
        }

        $rows = $this->terminalRows((int) $listingId, $tradeDate, $knownAt);
        if ($rows->isEmpty()) {
            return $this->unknown('TRADING_STATUS_NO_EVIDENCE', false, $identity);
        }

        $valid = [];
        $invalidReason = 'TRADING_STATUS_NO_AUTHORITATIVE_EVIDENCE';
        foreach ($rows as $row) {
            $validation = $this->validateAuthorityRow($row, $identity);
            $this->authorityEvaluations[(int) $row->status_revision_id] = $validation;
            if ($validation['valid']) {
                $valid[] = ['row' => $row, 'priority' => $validation['priority']];
            } else {
                $invalidReason = $validation['reason'];
            }
        }
        if ($valid === []) {
            return $this->unknown($invalidReason, true, $identity);
        }

        $byType = [];
        foreach ($valid as $entry) {
            $type = (string) $entry['row']->status_type_code;
            $byType[$type][] = $entry;
        }

        $selected = [];
        foreach ($byType as $type => $entries) {
            $priority = min(array_column($entries, 'priority'));
            $atPriority = array_values(array_filter($entries, static function ($entry) use ($priority) {
                return $entry['priority'] === $priority;
            }));
            $states = [];
            foreach ($atPriority as $entry) {
                $row = $entry['row'];
                $states[(string) $row->status_code.'|'.(string) $row->bar_expectation_state.'|'.(int) $row->full_session_verified] = true;
            }
            if (count($states) !== 1) {
                return $this->unknown('TRADING_STATUS_CONFLICT', true, $identity);
            }
            $selected[] = $atPriority[0]['row'];
        }

        $effects = [];
        $statusCodes = [];
        $revisionIds = [];
        $sourceObservationIds = [];
        $sourceRefs = [];
        $eventUids = [];
        foreach ($selected as $row) {
            $effect = (string) $row->bar_expectation_state;
            if (! in_array($effect, ['BAR_EXPECTED', 'BAR_NOT_EXPECTED', 'BAR_EXPECTATION_UNKNOWN'], true)) {
                return $this->unknown('TRADING_STATUS_EXPECTATION_EFFECT_INVALID', true, $identity);
            }
            if ($effect === 'BAR_NOT_EXPECTED' && (int) $row->full_session_verified !== 1) {
                return $this->unknown('TRADING_STATUS_FULL_SESSION_NOT_VERIFIED', true, $identity);
            }
            $effects[$effect] = true;
            $statusCodes[] = (string) $row->status_code;
            $revisionIds[] = (int) $row->status_revision_id;
            $sourceObservationIds[] = (int) $row->source_observation_id;
            $sourceRefs[] = (string) $row->source_ref;
            $eventUids[] = (string) $row->status_event_uid;
        }

        if (isset($effects['BAR_EXPECTATION_UNKNOWN'])
            || (isset($effects['BAR_EXPECTED']) && isset($effects['BAR_NOT_EXPECTED']))) {
            return $this->unknown('TRADING_STATUS_EFFECT_CONFLICT', true, $identity);
        }
        $effect = isset($effects['BAR_NOT_EXPECTED']) ? 'BAR_NOT_EXPECTED' : 'BAR_EXPECTED';
        sort($statusCodes, SORT_STRING);
        sort($revisionIds, SORT_NUMERIC);
        sort($sourceObservationIds, SORT_NUMERIC);
        sort($sourceRefs, SORT_STRING);
        sort($eventUids, SORT_STRING);
        $primary = $selected[0];

        return [
            'instrument_id' => (int) $identity->instrument_id,
            'listing_id' => (int) $listingId,
            'status_code' => implode('+', array_values(array_unique($statusCodes))),
            'bar_expectation_state' => $effect,
            'authority_class' => (string) $primary->authority_class,
            'status_revision_id' => count($revisionIds) === 1 ? $revisionIds[0] : null,
            'status_revision_ids' => array_values(array_unique($revisionIds)),
            'source_observation_id' => count($sourceObservationIds) === 1 ? $sourceObservationIds[0] : null,
            'source_observation_ids' => array_values(array_unique($sourceObservationIds)),
            'source_refs' => array_values(array_unique($sourceRefs)),
            'status_event_uids' => array_values(array_unique($eventUids)),
            'full_session_verified' => $effect === 'BAR_NOT_EXPECTED',
            'reason_code' => null,
            'had_records' => true,
        ];
    }

    private function terminalRows(int $listingId, $tradeDate, $knownAt)
    {
        if ($this->capturedPopulation !== null) {
            $all = $this->capturedPopulation['tables']['md_trading_status_revisions']; $rows = [];
            $superseded = array_filter(array_column($all, 'supersedes_revision_id'));
            foreach ($all as $row) {
                $id = (int) $row['status_revision_id']; $reasons = [];
                if ((int) $row['listing_id'] !== $listingId) $reasons[] = 'OTHER_LISTING';
                if (in_array($id, array_map('intval', $superseded), true)) $reasons[] = 'SUPERSEDED_AT_KNOWLEDGE_CUTOFF';
                if ($row['effective_from'] > $tradeDate.' 23:59:59'
                    || ($row['effective_to'] !== null && $row['effective_to'] <= $tradeDate.' 00:00:00')) $reasons[] = 'OUTSIDE_EFFECTIVE_INTERVAL';
                if ($row['verification_state'] !== 'VERIFIED') $reasons[] = 'NOT_VERIFIED';
                if ($row['retracted_at'] !== null && $row['retracted_at'] <= $knownAt) $reasons[] = 'RETRACTED_AT_KNOWLEDGE_CUTOFF';
                if ($reasons !== []) { $this->omissions[$id] = $reasons; continue; }
                $rows[] = (object) $row;
            }
            usort($rows, static function ($a, $b) { return strcmp($a->status_type_code, $b->status_type_code) ?: ($a->status_revision_id <=> $b->status_revision_id); });
            $this->terminalIds = array_map(static function ($r) { return (int) $r->status_revision_id; }, $rows);
            return collect($rows);
        }
        if (! Schema::hasTable('md_trading_status_revisions')) {
            return collect();
        }
        $query = DB::table('md_trading_status_revisions as revision')
            ->leftJoin('md_trading_status_revisions as newer', function ($join) use ($knownAt) {
                $join->on('newer.supersedes_revision_id', '=', 'revision.status_revision_id');
                if ($knownAt !== null && $knownAt !== '') {
                    $join->where('newer.recorded_at', '<=', $knownAt);
                }
            })
            ->whereNull('newer.status_revision_id')
            ->where('revision.listing_id', $listingId)
            ->where('revision.effective_from', '<=', $tradeDate.' 23:59:59')
            ->where(function ($query) use ($tradeDate) {
                $query->whereNull('revision.effective_to')
                    ->orWhere('revision.effective_to', '>', $tradeDate.' 00:00:00');
            })
            ->where('revision.verification_state', 'VERIFIED');
        if ($knownAt !== null && $knownAt !== '') {
            $query->where('revision.recorded_at', '<=', $knownAt)
                ->where(function ($retraction) use ($knownAt) {
                    $retraction->whereNull('revision.retracted_at')
                        ->orWhere('revision.retracted_at', '>', $knownAt);
                });
        } else {
            $query->whereNull('revision.retracted_at');
        }

        return $query->orderBy('revision.status_type_code')
            ->orderBy('revision.status_revision_id')->get(['revision.*']);
    }

    private function listingIdentity(int $listingId, $tradeDate, $knownAt)
    {
        if ($this->capturedPopulation !== null) {
            $tables = $this->capturedPopulation['tables']; $context = $this->capturedPopulation['domain_context'];
            $listing = null;
            foreach ($tables['md_listings'] as $row) if ((int) $row['listing_id'] === $listingId
                && $row['exchange_code'] === $context['market_code']
                && ($row['listed_date'] === null || $row['listed_date'] <= $tradeDate)
                && ($row['delisted_date'] === null || $row['delisted_date'] >= $tradeDate)) $listing = $row;
            if ($listing === null) return null;
            $boards = array_values(array_filter($tables['md_listing_boards'], static function ($r) use ($listingId, $tradeDate, $knownAt) {
                return (int) $r['listing_id'] === $listingId && $r['effective_from'] <= $tradeDate.' 23:59:59'
                    && ($r['effective_to'] === null || $r['effective_to'] > $tradeDate.' 00:00:00')
                    && ($r['retracted_at'] === null || $r['retracted_at'] > $knownAt);
            }));
            if (count($boards) !== 1 || $boards[0]['market_segment'] !== $context['market_segment'] || trim((string) $boards[0]['board_code']) === '') return null;
            return (object) ['listing_id' => $listing['listing_id'], 'instrument_id' => $listing['instrument_id'],
                'market_segment' => $boards[0]['market_segment'], 'board_code' => $boards[0]['board_code'], 'listing_board_id' => (int) $boards[0]['listing_board_id']];
        }
        if (! Schema::hasTable('md_listings') || ! Schema::hasTable('md_listing_boards')) {
            return null;
        }
        $query = DB::table('md_listings')
            ->where('listing_id', $listingId)
            ->where('exchange_code', config('market_data.scope.market_code', 'IDX'))
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('listed_date')->orWhere('listed_date', '<=', $tradeDate);
            })
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('delisted_date')->orWhere('delisted_date', '>=', $tradeDate);
            });
        if ($knownAt !== null && $knownAt !== '' && Schema::hasColumn('md_listings', 'recorded_at')) {
            $query->where('recorded_at', '<=', $knownAt);
        }

        $identity = $query->first(['listing_id', 'instrument_id']);
        if ($identity === null) {
            return null;
        }

        $boards = DB::table('md_listing_boards')
            ->where('listing_id', $listingId)
            ->where('effective_from', '<=', $tradeDate.' 23:59:59')
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>', $tradeDate.' 00:00:00');
            });
        if ($knownAt !== null && $knownAt !== '') {
            $boards->where('recorded_at', '<=', $knownAt)
                ->where(function ($q) use ($knownAt) {
                    $q->whereNull('retracted_at')->orWhere('retracted_at', '>', $knownAt);
                });
        } else {
            $boards->whereNull('retracted_at');
        }
        $rows = $boards->orderBy('listing_board_id')->get();
        if ($rows->count() !== 1) {
            return null;
        }
        $board = $rows->first();
        if ((string) $board->market_segment !== config('market_data.scope.market_segment', 'REGULAR')
            || trim((string) $board->board_code) === '') {
            return null;
        }
        $identity->market_segment = (string) $board->market_segment;
        $identity->board_code = (string) $board->board_code;
        $identity->listing_board_id = (int) $board->listing_board_id;

        return $identity;
    }

    private function validateAuthorityRow($row, $identity): array
    {
        foreach (['status_event_uid', 'status_type_code', 'source_name', 'source_payload_hash',
            'source_ref', 'observed_at', 'announced_at', 'recorded_at'] as $field) {
            if (trim((string) ($row->{$field} ?? '')) === '') {
                return ['valid' => false, 'reason' => 'TRADING_STATUS_AUTHORITY_METADATA_INCOMPLETE', 'priority' => null];
            }
        }
        if ((int) ($row->instrument_id ?? 0) !== (int) $identity->instrument_id
            || ! preg_match('/^[a-f0-9]{64}$/', strtolower((string) $row->source_payload_hash))) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_STABLE_ID_OR_HASH_INVALID', 'priority' => null];
        }
        if (trim((string) ($row->board_code ?? '')) === ''
            || (string) $row->board_code !== (string) $identity->board_code) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_BOARD_SCOPE_MISMATCH', 'priority' => null];
        }
        if (! $this->isGovernedStatusType((string) $row->status_type_code, $row)) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_TYPE_UNGOVERNED', 'priority' => null];
        }

        $registry = $this->sourceRegistry((string) $row->source_name, (string) $row->status_type_code);
        if ($registry === null || (string) $registry->authority_class !== (string) $row->authority_class) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_SOURCE_NOT_REGISTERED', 'priority' => null];
        }
        if ($registry->source_ref_pattern !== null
            && strpos(strtolower((string) $row->source_ref), strtolower((string) $registry->source_ref_pattern)) === false) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_SOURCE_REFERENCE_MISMATCH', 'priority' => null];
        }
        if ((string) $row->authority_class === 'DERIVED_REFERENCE') {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_DERIVED_REFERENCE_NON_AUTHORITATIVE', 'priority' => null];
        }
        if ((string) $row->authority_class === 'OPERATOR_ENTERED'
            && (trim((string) ($row->operator_name ?? '')) === ''
                || trim((string) ($row->governed_reason_code ?? '')) === ''
                || trim((string) ($row->authoritative_source_ref ?? '')) === '')) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_OPERATOR_AUTHORITY_INCOMPLETE', 'priority' => null];
        }
        if (! in_array((string) $row->authority_class, ['EXCHANGE_AUTHORITATIVE', 'OPERATOR_ENTERED'], true)
            || ! $this->sourceObservationMatches($row)) {
            return ['valid' => false, 'reason' => 'TRADING_STATUS_SOURCE_OBSERVATION_INVALID', 'priority' => null];
        }

        return ['valid' => true, 'reason' => null, 'priority' => (int) $registry->priority];
    }

    private function isGovernedStatusType(string $type, $row): bool
    {
        if ($this->capturedPopulation !== null) {
            foreach ($this->capturedPopulation['tables']['market_data_trading_status_event_types'] as $definition) {
                if ($definition['event_type_code'] === $type) return (int) $definition['carries_forward'] === 1 || $row->effective_to !== null;
            }
            return false;
        }
        if (! Schema::hasTable('market_data_trading_status_event_types')) {
            return false;
        }
        $definition = DB::table('market_data_trading_status_event_types')
            ->where('event_type_code', $type)->first();
        if (! $definition) {
            return false;
        }

        return (int) $definition->carries_forward === 1 || $row->effective_to !== null;
    }

    private function sourceRegistry(string $sourceName, string $statusType)
    {
        if ($this->capturedPopulation !== null) {
            $rows = array_values(array_filter($this->capturedPopulation['tables']['md_trading_status_source_registry'], static function ($r) use ($sourceName, $statusType) {
                return $r['source_name'] === $sourceName && (int) $r['active'] === 1 && in_array($r['status_type_code'], [$statusType, '*'], true);
            }));
            usort($rows, static function ($a, $b) use ($statusType) { return ($a['status_type_code'] === $statusType ? 0 : 1) <=> ($b['status_type_code'] === $statusType ? 0 : 1); });
            return $rows === [] ? null : (object) $rows[0];
        }
        if (! Schema::hasTable('md_trading_status_source_registry')) {
            return null;
        }

        return DB::table('md_trading_status_source_registry')
            ->where('source_name', $sourceName)
            ->where('active', 1)
            ->whereIn('status_type_code', [$statusType, '*'])
            ->orderByRaw("CASE WHEN status_type_code = ? THEN 0 ELSE 1 END", [$statusType])
            ->first();
    }

    private function sourceObservationMatches($row): bool
    {
        $observationId = (int) ($row->source_observation_id ?? 0);
        if ($observationId < 1 || ($this->capturedPopulation === null && ! Schema::hasTable('md_source_observations'))) {
            return false;
        }
        if ($this->capturedPopulation !== null) {
            $observation = null;
            foreach ($this->capturedPopulation['tables']['md_source_observations'] as $r) {
                if ((int) $r['source_observation_id'] === $observationId) $observation = (object) $r;
            }
        } else $observation = DB::table('md_source_observations')->where('source_observation_id', $observationId)->first();

        if (! $observation
            || (string) $observation->outcome_state !== 'ACCEPTED'
            || strtolower((string) $observation->payload_hash) !== strtolower((string) $row->source_payload_hash)) {
            return false;
        }
        $market = $this->capturedPopulation['domain_context']['market_code'] ?? config('market_data.scope.market_code', 'IDX');
        if ((string) $row->authority_class === 'EXCHANGE_AUTHORITATIVE'
            && ((string) $observation->source_name !== $market || (string) $observation->provider !== $market)) {
            return false;
        }

        return true;
    }

    private function unknown(string $reason, bool $hadRecords, $identity = null): array
    {
        return [
            'instrument_id' => $identity ? (int) $identity->instrument_id : null,
            'listing_id' => $identity ? (int) $identity->listing_id : null,
            'status_code' => $reason === 'TRADING_STATUS_CONFLICT' ? 'CONFLICTING' : 'UNKNOWN',
            'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
            'authority_class' => null,
            'status_revision_id' => null,
            'status_revision_ids' => [],
            'source_observation_id' => null,
            'source_observation_ids' => [],
            'source_refs' => [],
            'status_event_uids' => [],
            'full_session_verified' => false,
            'reason_code' => $reason,
            'had_records' => $hadRecords,
        ];
    }
}
