<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StageEightCorpusAdmissionService
{
    const ALGORITHM_VERSION = 'stage8-conformant-suffix-admission/v1';

    private $tickers;
    private $status;

    public function __construct(TickerMasterRepository $tickers, EventRiskSourceRepository $status)
    {
        $this->tickers = $tickers;
        $this->status = $status;
    }

    public function evaluate($campaignId = null, $apply = false): array
    {
        $campaign = $this->blockedCampaign($campaignId);
        $cacheRoot = storage_path('app/market-data/stage8/'.$campaign->campaign_uid.'/acquisition');
        $manifestPath = $cacheRoot.'/manifest.json';
        $manifestBytes = is_file($manifestPath) ? file_get_contents($manifestPath) : false;
        $manifest = $manifestBytes === false ? null : json_decode($manifestBytes, true);
        if (! is_array($manifest)
            || ($manifest['state'] ?? null) !== 'COMPLETE'
            || ($manifest['campaign_uid'] ?? null) !== (string) $campaign->campaign_uid) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: blocked campaign acquisition cache is not complete or does not match.');
        }

        $statusEvidence = $this->statusEvidence();
        $measuredStart = $statusEvidence['observed_as_of'];
        $measuredThrough = min((string) $campaign->scope_end, $statusEvidence['transition_search_end']);
        $dates = array_values(array_filter(array_keys($manifest['ticker_counts'] ?? []), function ($date) use ($measuredStart, $measuredThrough) {
            return $date >= $measuredStart && $date <= $measuredThrough;
        }));
        sort($dates, SORT_STRING);
        if ($dates === [] || $dates[count($dates) - 1] !== $measuredThrough) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: measured interval is absent from the frozen campaign cache.');
        }

        $knownAt = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
        $threshold = (float) config('market_data.coverage_gate.min_ratio', 0.98);
        if (abs($threshold - 0.98) > 0.0000001) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: coverage threshold differs from the locked 0.98.');
        }

        $measurements = [];
        foreach ($dates as $date) {
            $measurements[$date] = $this->measureDate($cacheRoot, $date, $knownAt, $threshold);
        }

        $admittedFrom = null;
        $suffixPass = true;
        foreach (array_reverse($measurements, true) as $date => $measurement) {
            $suffixPass = $suffixPass && $measurement['gate_pass'];
            if ($suffixPass) {
                $admittedFrom = $date;
            }
        }
        if ($admittedFrom === null) {
            throw new \RuntimeException('STAGE8_ADMISSION_SUFFIX_NOT_FOUND: no continuous suffix passes the locked gates.');
        }

        $dateIndex = array_search($admittedFrom, $dates, true);
        $predecessor = $dateIndex > 0 ? $dates[$dateIndex - 1] : null;
        if ($predecessor !== null && $measurements[$predecessor]['gate_pass']) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: admitted boundary is not the earliest continuous passing suffix.');
        }

        $statusRevisionSetHash = $this->statusRevisionSetHash($statusEvidence['snapshot_observation_id']);
        $measurementPayload = [
            'schema_version' => self::ALGORITHM_VERSION,
            'source_campaign_id' => (int) $campaign->campaign_id,
            'source_campaign_uid' => (string) $campaign->campaign_uid,
            'source_cache_manifest_sha256' => hash('sha256', $manifestBytes),
            'measured_start' => $measuredStart,
            'measured_through' => $measuredThrough,
            'admitted_from' => $admittedFrom,
            'boundary_predecessor' => $predecessor,
            'coverage_threshold' => $threshold,
            'status_snapshot_observation_id' => $statusEvidence['snapshot_observation_id'],
            'transition_search_observation_id' => $statusEvidence['transition_observation_id'],
            'status_revision_set_hash' => $statusRevisionSetHash,
            'dates' => $measurements,
        ];
        $measurementJson = $this->canonicalJson($measurementPayload);
        $measurementHash = hash('sha256', $measurementJson);
        $decisionIdentity = [
            'market_code' => config('market_data.scope.market_code', 'IDX'),
            'market_segment' => config('market_data.scope.market_segment', 'REGULAR'),
            'canonical_price_product' => config('market_data.scope.raw_product_code', 'RAW'),
            'intentional_dataset_start' => MarketDataScope::DATASET_START,
            'admitted_from' => $admittedFrom,
            'measured_through' => $measuredThrough,
            'coverage_threshold' => $threshold,
            'source_mode' => 'api',
            'measurement_campaign_id' => (int) $campaign->campaign_id,
            'measurement_input_hash' => $measurementHash,
            'status_revision_set_hash' => $statusRevisionSetHash,
            'algorithm_version' => self::ALGORITHM_VERSION,
        ];
        $decisionUid = hash('sha256', $this->canonicalJson($decisionIdentity));

        $decisionId = null;
        $inserted = false;
        if ($apply) {
            [$decisionId, $inserted] = DB::transaction(function () use (
                $decisionUid,
                $decisionIdentity,
                $measurementJson,
                $statusEvidence
            ) {
                $existing = DB::table('md_corpus_admission_decisions')->where('decision_uid', $decisionUid)->first();
                if ($existing) {
                    $this->assertExistingDecisionMatches($existing, $decisionIdentity, $statusEvidence, $measurementJson);

                    return [(int) $existing->admission_decision_id, false];
                }
                $active = DB::table('md_corpus_admission_decisions')->where('state', 'ACTIVE')->first();
                if ($active) {
                    throw new \RuntimeException('STAGE8_ADMISSION_STATE_CONFLICT: a different corpus admission decision is already active.');
                }
                $now = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
                $id = DB::table('md_corpus_admission_decisions')->insertGetId(array_merge($decisionIdentity, [
                    'decision_uid' => $decisionUid,
                    'status_snapshot_observation_id' => $statusEvidence['snapshot_observation_id'],
                    'transition_search_observation_id' => $statusEvidence['transition_observation_id'],
                    'measurement_json' => $measurementJson,
                    'state' => 'ACTIVE',
                    'reason_code' => 'STAGE8_CONFORMANT_SUFFIX_ADMITTED',
                    'supersedes_decision_id' => null,
                    'recorded_at' => $now,
                    'created_at' => $now,
                ]));

                return [(int) $id, true];
            });
        }

        return [
            'status' => $apply ? ($inserted ? 'ADMITTED' : 'ALREADY_ADMITTED') : 'PLAN_ONLY',
            'admission_decision_id' => $decisionId,
            'decision_uid' => $decisionUid,
            'source_campaign_id' => (int) $campaign->campaign_id,
            'measured_start' => $measuredStart,
            'measured_through' => $measuredThrough,
            'measured_date_count' => count($measurements),
            'admitted_from' => $admittedFrom,
            'admitted_date_count' => count(array_filter(array_keys($measurements), function ($date) use ($admittedFrom) {
                return $date >= $admittedFrom;
            })),
            'boundary_predecessor' => $predecessor,
            'boundary_predecessor_pass' => $predecessor === null ? null : $measurements[$predecessor]['gate_pass'],
            'frontier_ratio' => $measurements[$measuredThrough]['delivery_ratio'],
            'measurement_input_hash' => $measurementHash,
            'status_revision_set_hash' => $statusRevisionSetHash,
            'stage_9_replay' => 'NOT_EXECUTED',
        ];
    }

    private function blockedCampaign($campaignId)
    {
        $query = DB::table('md_stage8_reconstruction_campaigns')->where('state', 'BLOCKED');
        if ($campaignId !== null && $campaignId !== '') {
            $query->where('campaign_id', (int) $campaignId);
        }
        $campaign = $query->orderByDesc('campaign_id')->first();
        if (! $campaign) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: a blocked source campaign was not found.');
        }

        return $campaign;
    }

    private function statusEvidence(): array
    {
        $rows = DB::table('md_trading_status_revisions')
            ->where('status_code', 'SUSPENSION_OBSERVED')
            ->where('bar_expectation_state', 'BAR_NOT_EXPECTED')
            ->where('authority_class', 'EXCHANGE_AUTHORITATIVE')
            ->where('full_session_verified', 1)
            ->where('verification_state', 'VERIFIED')
            ->whereNull('retracted_at')
            ->whereNotNull('source_observation_id')
            ->orderBy('listing_id')
            ->get();
        if ($rows->count() !== 59) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: expected exactly 59 verified snapshot revisions.');
        }
        $snapshotIds = $rows->pluck('source_observation_id')->unique()->values();
        $effectiveDates = $rows->map(function ($row) {
            return substr((string) $row->effective_from, 0, 10);
        })->unique()->values();
        if ($snapshotIds->count() !== 1 || $effectiveDates->count() !== 1) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: snapshot revision set is not one bounded authority observation.');
        }
        $snapshotId = (int) $snapshotIds->first();
        $snapshot = $this->acceptedObservation($snapshotId, 'AUTHORITATIVE_TRADING_STATUS_VALIDATED');
        $transition = DB::table('md_source_observations')
            ->where('source_name', 'IDX')
            ->where('provider', 'IDX')
            ->where('adapter_version', AuthoritativeTradingStatusSnapshotService::SOURCE_ADAPTER_VERSION)
            ->where('outcome_state', 'ACCEPTED')
            ->where('validation_state', 'PASSED')
            ->where('reason_code', 'AUTHORITATIVE_TRADING_STATUS_TRANSITIONS_VALIDATED')
            ->orderByDesc('source_observation_id')
            ->first();
        if (! $transition || ! preg_match('/^[a-f0-9]{64}$/', (string) $transition->payload_hash)) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: transition search evidence is missing.');
        }

        return [
            'observed_as_of' => (string) $effectiveDates->first(),
            'transition_search_end' => (string) $transition->requested_end_date,
            'snapshot_observation_id' => $snapshotId,
            'transition_observation_id' => (int) $transition->source_observation_id,
        ];
    }

    private function acceptedObservation($id, $reasonCode)
    {
        $row = DB::table('md_source_observations')->where('source_observation_id', $id)->first();
        if (! $row
            || $row->outcome_state !== 'ACCEPTED'
            || $row->validation_state !== 'PASSED'
            || $row->reason_code !== $reasonCode
            || ! preg_match('/^[a-f0-9]{64}$/', (string) $row->payload_hash)
            || $row->parent_observation_id === null) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: accepted authority observation is invalid.');
        }

        return $row;
    }

    private function measureDate($cacheRoot, $date, $knownAt, $threshold): array
    {
        $universe = $this->tickers->getProjectedUniverseForTradeDate($date, $knownAt);
        $tickerIds = array_column($universe, 'ticker_id');
        $notExpected = array_fill_keys($this->status->suspendedTickerIdsAsOf($tickerIds, $date, $knownAt), true);
        $expectedByCode = [];
        foreach ($universe as $row) {
            if (! isset($notExpected[(int) $row['ticker_id']])) {
                $expectedByCode[strtoupper(trim((string) $row['ticker_code']))] = (int) $row['ticker_id'];
            }
        }

        $path = $cacheRoot.'/rows/'.$date.'.jsonl';
        if (! is_file($path)) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: cache date '.$date.' is missing.');
        }
        $rows = [];
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: cache date '.$date.' is unreadable.');
        }
        try {
            $batches = [];
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if (! is_array($entry) || ! is_array($entry['rows'] ?? null) || ! isset($entry['batch_number'])) {
                    throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: cache date '.$date.' is malformed.');
                }
                $batches[(int) $entry['batch_number']] = $entry['rows'];
            }
            ksort($batches, SORT_NUMERIC);
            foreach ($batches as $batchRows) {
                foreach ($batchRows as $row) {
                    $code = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
                    if ($code !== '') {
                        $rows[$code] = $row;
                    }
                }
            }
        } finally {
            fclose($handle);
        }

        $observationIds = [];
        foreach (array_intersect_key($rows, $expectedByCode) as $row) {
            if ((int) ($row['source_observation_id'] ?? 0) > 0) {
                $observationIds[] = (int) $row['source_observation_id'];
            }
        }
        $observationIds = array_values(array_unique($observationIds));
        $acceptedIds = DB::table('md_source_observations')
            ->whereIn('source_observation_id', $observationIds)
            ->whereIn('outcome_state', ['ACCEPTED', 'NORMALIZED'])
            ->pluck('source_observation_id')
            ->map(function ($id) { return (int) $id; })
            ->all();
        $accepted = array_fill_keys($acceptedIds, true);

        $delivered = 0;
        $valid = 0;
        $invalidCodes = [];
        $deliveredObservationIds = [];
        foreach ($expectedByCode as $code => $tickerId) {
            $row = $rows[$code] ?? null;
            $observationId = (int) ($row['source_observation_id'] ?? 0);
            if (! $row || ! isset($accepted[$observationId])) {
                continue;
            }
            $delivered++;
            $deliveredObservationIds[] = $observationId;
            if ($this->validOhlcv($row)) {
                $valid++;
            } else {
                $invalidCodes[] = $code;
            }
        }
        sort($invalidCodes, SORT_STRING);
        sort($deliveredObservationIds, SORT_NUMERIC);
        $denominator = count($expectedByCode);
        $ratio = $denominator === 0 ? 0.0 : $delivered / $denominator;

        return [
            'universe_count' => count($universe),
            'verified_not_expected_count' => count($notExpected),
            'denominator_count' => $denominator,
            'delivered_count' => $delivered,
            'canonical_valid_count' => $valid,
            'missing_count' => max(0, $denominator - $delivered),
            'invalid_count' => count($invalidCodes),
            'delivery_ratio' => round($ratio, 8),
            'gate_pass' => $denominator > 0 && $ratio >= $threshold && $invalidCodes === [],
            'invalid_codes' => $invalidCodes,
            'observation_set_hash' => hash('sha256', json_encode($deliveredObservationIds)),
        ];
    }

    private function validOhlcv(array $row): bool
    {
        foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! array_key_exists($field, $row) || ! is_numeric($row[$field])) {
                return false;
            }
        }
        $open = (float) $row['open'];
        $high = (float) $row['high'];
        $low = (float) $row['low'];
        $close = (float) $row['close'];
        $volume = (float) $row['volume'];

        return $open > 0 && $high > 0 && $low > 0 && $close > 0 && $volume >= 0
            && $high >= max($open, $close, $low)
            && $low <= min($open, $close, $high);
    }

    private function statusRevisionSetHash($snapshotObservationId): string
    {
        $rows = DB::table('md_trading_status_revisions')
            ->where('source_observation_id', $snapshotObservationId)
            ->whereNull('retracted_at')
            ->orderBy('listing_id')
            ->get()
            ->map(function ($row) {
                return [
                    'status_revision_id' => (int) $row->status_revision_id,
                    'listing_id' => (int) $row->listing_id,
                    'status_code' => (string) $row->status_code,
                    'bar_expectation_state' => (string) $row->bar_expectation_state,
                    'full_session_verified' => (int) $row->full_session_verified,
                    'effective_from' => (string) $row->effective_from,
                    'source_observation_id' => (int) $row->source_observation_id,
                ];
            })->all();
        if (count($rows) !== 59) {
            throw new \RuntimeException('STAGE8_ADMISSION_EVIDENCE_INVALID: status revision hash set is incomplete.');
        }

        return hash('sha256', $this->canonicalJson($rows));
    }

    private function assertExistingDecisionMatches($existing, array $identity, array $statusEvidence, $measurementJson): void
    {
        foreach ($identity as $field => $expected) {
            if ($field === 'coverage_threshold') {
                if (abs((float) $existing->{$field} - (float) $expected) > 0.0000005) {
                    throw new \RuntimeException('STAGE8_ADMISSION_DECISION_CONFLICT: immutable decision differs at '.$field.'.');
                }

                continue;
            }
            if ((string) $existing->{$field} !== (string) $expected) {
                throw new \RuntimeException('STAGE8_ADMISSION_DECISION_CONFLICT: immutable decision differs at '.$field.'.');
            }
        }
        if ((int) $existing->status_snapshot_observation_id !== (int) $statusEvidence['snapshot_observation_id']
            || (int) $existing->transition_search_observation_id !== (int) $statusEvidence['transition_observation_id']
            || (string) $existing->measurement_json !== (string) $measurementJson
            || (string) $existing->state !== 'ACTIVE'
            || (string) $existing->reason_code !== 'STAGE8_CONFORMANT_SUFFIX_ADMITTED') {
            throw new \RuntimeException('STAGE8_ADMISSION_DECISION_CONFLICT: immutable decision evidence differs.');
        }
    }

    private function canonicalJson($value): string
    {
        $normalize = function ($item) use (&$normalize) {
            if (! is_array($item)) {
                return $item;
            }
            $isList = $item === [] || array_keys($item) === range(0, count($item) - 1);
            if (! $isList) {
                ksort($item, SORT_STRING);
            }
            foreach ($item as $key => $child) {
                $item[$key] = $normalize($child);
            }

            return $item;
        };

        return json_encode($normalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}
