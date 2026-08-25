<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Independent internal reconciliation between rebuildable current projections and the immutable
 * history set selected by the governed current-publication pointer.
 *
 * This service is intentionally verification-only: it never repairs/deletes either side. A FAIL
 * result is persisted as evidence and must be resolved through the governed projection-repair or
 * publication/correction lifecycle. Reconciliation itself never mutates either side.
 */
class PublicationProjectionReconciliationService
{
    private const SAMPLE_LIMIT = 25;

    private $publications;
    private $hashes;

    public function __construct(EodPublicationRepository $publications, DeterministicHashService $hashes)
    {
        $this->publications = $publications;
        $this->hashes = $hashes;
    }

    public function reconcileRange(string $startDate, string $endDate): array
    {
        if (! $this->isDate($startDate) || ! $this->isDate($endDate) || $startDate > $endDate) {
            throw new \InvalidArgumentException('RECONCILIATION_DATE_RANGE_INVALID');
        }

        $dates = $this->relevantTradeDates($startDate, $endDate);
        $results = [];
        foreach ($dates as $tradeDate) {
            $results[] = $this->reconcileTradeDate($tradeDate);
        }

        return $results;
    }

    public function reconcileLatest(): array
    {
        $date = $this->latestRelevantTradeDate();
        if ($date === null) {
            return [];
        }

        return [$this->reconcileTradeDate($date)];
    }

    public function reconcileTradeDate(string $tradeDate): array
    {
        if (! $this->isDate($tradeDate)) {
            throw new \InvalidArgumentException('RECONCILIATION_TRADE_DATE_INVALID');
        }

        $projectionCounts = $this->projectionCounts($tradeDate);
        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
        $rawPointer = $this->publications->findRawCurrentPublicationStateForTradeDate($tradeDate);
        $checkedAt = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        if (! $publication) {
            $pointerState = $rawPointer ? 'INVALID' : 'MISSING';
            $orphanCount = array_sum($projectionCounts);
            $sample = [[
                'type' => 'CURRENT_PUBLICATION_UNRESOLVED',
                'trade_date' => $tradeDate,
                'pointer_state' => $pointerState,
                'projection_row_count' => $orphanCount,
            ]];

            $result = $this->baseResult($tradeDate, null, $pointerState, 'FAIL', $checkedAt);
            foreach (['bars', 'indicators', 'eligibility'] as $artifact) {
                $result[$artifact.'_projection_count'] = $projectionCounts[$artifact];
                $result[$artifact.'_history_count'] = 0;
                $result[$artifact.'_missing_history_count'] = $projectionCounts[$artifact];
                $result[$artifact.'_missing_projection_count'] = 0;
                $result[$artifact.'_value_mismatch_count'] = 0;
            }
            $result['orphan_projection_row_count'] = $orphanCount;
            $result['mismatch_count'] = $orphanCount;
            $result['mismatch_sample_json'] = json_encode($sample, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $this->persist($result);
        }

        $artifactSpecs = [
            'bars' => ['eod_bars', 'eod_bars_history', MarketDataPipelineService::BARS_HASH_COLUMNS],
            'indicators' => ['eod_indicators', 'eod_indicators_history', MarketDataPipelineService::INDICATORS_HASH_COLUMNS],
            'eligibility' => ['eod_eligibility', 'eod_eligibility_history', MarketDataPipelineService::ELIGIBILITY_HASH_COLUMNS],
        ];

        $result = $this->baseResult($tradeDate, $publication, 'RESOLVED', 'PASS', $checkedAt);
        $sample = [];
        $totalMismatch = 0;

        foreach ($artifactSpecs as $artifact => $spec) {
            [$projectionTable, $historyTable, $columns] = $spec;
            $comparison = $this->compareArtifact(
                $artifact,
                $tradeDate,
                (int) $publication->publication_id,
                (int) $publication->run_id,
                $projectionTable,
                $historyTable,
                $columns,
                $sample
            );

            foreach ($comparison as $field => $value) {
                $result[$artifact.'_'.$field] = $value;
            }
            $totalMismatch += $comparison['missing_history_count']
                + $comparison['missing_projection_count']
                + $comparison['value_mismatch_count'];
        }

        $result['orphan_projection_row_count'] = 0;
        $result['mismatch_count'] = $totalMismatch;
        $result['reconciliation_state'] = $totalMismatch === 0 ? 'PASS' : 'FAIL';
        $result['mismatch_sample_json'] = $sample === []
            ? null
            : json_encode($sample, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->persist($result);
    }

    public function latestRelevantTradeDate()
    {
        $dates = [];
        foreach (['eod_current_publication_pointer', 'eod_bars', 'eod_indicators', 'eod_eligibility'] as $table) {
            $value = DB::table($table)->max('trade_date');
            if ($value !== null && $value !== '') {
                $dates[] = (string) $value;
            }
        }

        return $dates === [] ? null : max($dates);
    }

    private function relevantTradeDates(string $startDate, string $endDate): array
    {
        $dates = [];
        foreach (['eod_current_publication_pointer', 'eod_bars', 'eod_indicators', 'eod_eligibility'] as $table) {
            foreach (DB::table($table)->whereBetween('trade_date', [$startDate, $endDate])->distinct()->pluck('trade_date')->all() as $date) {
                $dates[(string) $date] = true;
            }
        }
        $dates = array_keys($dates);
        sort($dates, SORT_STRING);

        return $dates;
    }

    private function projectionCounts(string $tradeDate): array
    {
        return [
            'bars' => (int) DB::table('eod_bars')->where('trade_date', $tradeDate)->count(),
            'indicators' => (int) DB::table('eod_indicators')->where('trade_date', $tradeDate)->count(),
            'eligibility' => (int) DB::table('eod_eligibility')->where('trade_date', $tradeDate)->count(),
        ];
    }

    private function compareArtifact(
        string $artifact,
        string $tradeDate,
        int $publicationId,
        int $runId,
        string $projectionTable,
        string $historyTable,
        array $canonicalColumns,
        array &$sample
    ): array {
        $projectionRows = DB::table($projectionTable)->where('trade_date', $tradeDate)->get();
        $historyRows = DB::table($historyTable)
            ->where('trade_date', $tradeDate)
            ->where('publication_id', $publicationId)
            ->get();

        $projection = $this->indexByTicker($projectionRows);
        $history = $this->indexByTicker($historyRows);
        $projectionKeys = array_keys($projection);
        $historyKeys = array_keys($history);
        $missingHistory = array_values(array_diff($projectionKeys, $historyKeys));
        $missingProjection = array_values(array_diff($historyKeys, $projectionKeys));

        foreach ($missingHistory as $tickerId) {
            $this->appendSample($sample, [
                'artifact' => $artifact,
                'type' => 'MISSING_HISTORY',
                'trade_date' => $tradeDate,
                'ticker_id' => $tickerId,
            ]);
        }
        foreach ($missingProjection as $tickerId) {
            $this->appendSample($sample, [
                'artifact' => $artifact,
                'type' => 'MISSING_PROJECTION',
                'trade_date' => $tradeDate,
                'ticker_id' => $tickerId,
            ]);
        }

        $valueMismatches = 0;
        $common = array_values(array_intersect($projectionKeys, $historyKeys));
        sort($common, SORT_NUMERIC);
        $columns = array_values(array_unique(array_merge($canonicalColumns, ['publication_id', 'run_id'])));
        foreach ($common as $tickerId) {
            $different = [];
            foreach ($columns as $column) {
                $left = $this->value($projection[$tickerId], $column);
                $right = $this->value($history[$tickerId], $column);
                if ($this->hashes->normalizeValue($left, $column) !== $this->hashes->normalizeValue($right, $column)) {
                    $different[] = $column;
                }
            }

            // Equality between two wrong bindings is still wrong: both sides must bind to the
            // pointer-resolved publication and its owning run.
            if ((int) $this->value($projection[$tickerId], 'publication_id') !== $publicationId
                || (int) $this->value($history[$tickerId], 'publication_id') !== $publicationId) {
                $different[] = 'publication_id_binding';
            }
            if ((int) $this->value($projection[$tickerId], 'run_id') !== $runId
                || (int) $this->value($history[$tickerId], 'run_id') !== $runId) {
                $different[] = 'run_id_binding';
            }
            $different = array_values(array_unique($different));

            if ($different !== []) {
                $valueMismatches++;
                $this->appendSample($sample, [
                    'artifact' => $artifact,
                    'type' => 'VALUE_MISMATCH',
                    'trade_date' => $tradeDate,
                    'ticker_id' => $tickerId,
                    'fields' => $different,
                ]);
            }
        }

        return [
            'projection_count' => count($projection),
            'history_count' => count($history),
            'missing_history_count' => count($missingHistory),
            'missing_projection_count' => count($missingProjection),
            'value_mismatch_count' => $valueMismatches,
        ];
    }

    private function indexByTicker($rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $key = (int) $row->ticker_id;
            if (isset($out[$key])) {
                throw new \RuntimeException('PROJECTION_RECONCILIATION_DUPLICATE_TICKER_KEY');
            }
            $out[$key] = $row;
        }
        ksort($out, SORT_NUMERIC);

        return $out;
    }

    private function value($row, string $field)
    {
        return isset($row->{$field}) || property_exists($row, $field) ? $row->{$field} : null;
    }

    private function appendSample(array &$sample, array $entry): void
    {
        if (count($sample) < self::SAMPLE_LIMIT) {
            $sample[] = $entry;
        }
    }

    private function baseResult(string $tradeDate, $publication, string $pointerState, string $state, string $checkedAt): array
    {
        return [
            'trade_date' => $tradeDate,
            'publication_id' => $publication ? (int) $publication->publication_id : null,
            'run_id' => $publication ? (int) $publication->run_id : null,
            'publication_version' => $publication ? (int) $publication->publication_version : null,
            'pointer_state' => $pointerState,
            'reconciliation_state' => $state,
            'checked_at' => $checkedAt,
        ];
    }

    private function persist(array $result): array
    {
        $hashPayload = $result;
        unset($hashPayload['checked_at'], $hashPayload['mismatch_sample_json']);
        if (! empty($result['mismatch_sample_json'])) {
            $hashPayload['mismatch_sample'] = json_decode($result['mismatch_sample_json'], true);
        }
        $reconciliationHash = $this->hashes->hashCanonicalDocument($hashPayload);
        $uid = hash('sha256', implode('|', [
            $result['trade_date'],
            (string) ($result['publication_id'] ?? 'none'),
            $reconciliationHash,
            $result['checked_at'],
            bin2hex(random_bytes(8)),
        ]));

        $row = $result + [
            'reconciliation_uid' => $uid,
            'reconciliation_hash' => $reconciliationHash,
            'created_at' => $result['checked_at'],
        ];

        DB::table('md_publication_projection_reconciliations')->insert($row);

        return $row;
    }

    private function isDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
