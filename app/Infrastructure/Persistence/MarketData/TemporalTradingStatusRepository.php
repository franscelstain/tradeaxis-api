<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Resolve status only from temporal, source-registered, authority-bearing revisions. */
class TemporalTradingStatusRepository
{
    public function resolveForListing($listingId, $tradeDate, $knownAt = null)
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
        if ($observationId < 1 || ! Schema::hasTable('md_source_observations')) {
            return false;
        }
        $observation = DB::table('md_source_observations')
            ->where('source_observation_id', $observationId)->first();

        if (! $observation
            || (string) $observation->outcome_state !== 'ACCEPTED'
            || strtolower((string) $observation->payload_hash) !== strtolower((string) $row->source_payload_hash)) {
            return false;
        }
        if ((string) $row->authority_class === 'EXCHANGE_AUTHORITATIVE'
            && ((string) $observation->source_name !== config('market_data.scope.market_code', 'IDX')
                || (string) $observation->provider !== config('market_data.scope.market_code', 'IDX'))) {
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
