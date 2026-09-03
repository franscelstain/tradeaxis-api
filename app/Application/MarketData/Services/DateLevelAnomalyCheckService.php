<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;

/**
 * Date-level anomaly checks owned by `Run_Status_and_Quality_Gates_LOCKED.md`.
 *
 * The contract states the reason these exist better than a summary could:
 *
 *   > Row-level validation cannot, by construction, see a pattern across rows. A defect affecting
 *   > many instruments on one acquisition date presents as many individually admissible rows, and
 *   > every per-row rule passes.
 *
 * It then names three measures and binds their thresholds to the run's configuration snapshot
 * rather than to judgement: zero-volume share, flat-bar share, and cross-field contradiction count.
 * The three measures and their current controlled configuration are executed here.
 *
 * Two rules shape the implementation:
 *
 *   - comparison against neighbouring dates uses governed trading days, never calendar days, so the
 *     baseline is resolved through the market calendar and not by date arithmetic;
 *   - a finding is quality evidence. It does not delete or alter rows.
 *
 * And one rule shapes how the result may be read: absence of a finding is not evidence the date is
 * clean. These checks detect concentration, and a defect spread evenly across dates shifts the
 * baseline it would have been compared against.
 */
class DateLevelAnomalyCheckService
{
    public const STATE_CLEAN = 'CLEAN';

    public const STATE_FINDING = 'FINDING';

    public const STATE_NOT_EVALUABLE = 'NOT_EVALUABLE';

    public const CONTRACT_VERSION = 'date_level_anomaly_v1';

    public const THRESHOLD_BINDING_STATE = 'CONFIG_SNAPSHOT_BOUND';

    private const THRESHOLD_KEYS = [
        'zero_volume_share_max',
        'flat_bar_share_max',
        'cross_field_contradiction_max',
        'neighbour_trading_days',
        'neighbour_elevation_factor',
        'minimum_rows',
    ];

    protected EodArtifactRepository $artifacts;

    protected ?MarketCalendarRepository $calendar;

    protected MarketDataConfigSnapshotRepository $configSnapshots;

    public function __construct(
        EodArtifactRepository $artifacts,
        MarketCalendarRepository $calendar = null,
        MarketDataConfigSnapshotRepository $configSnapshots = null
    ) {
        $this->artifacts = $artifacts;
        $this->calendar = $calendar;
        $this->configSnapshots = $configSnapshots ?: new MarketDataConfigSnapshotRepository();
    }

    /**
     * @param  string  $tradeDate
     * @param  int|null  $publicationId
     * @param  string|null  $knownAt
     * @param  int|null  $configSnapshotId
     * @param  string|null  $expectedConfigHash
     * @return array<string,mixed>
     */
    public function evaluate(
        $tradeDate,
        $publicationId = null,
        $knownAt = null,
        $configSnapshotId = null,
        $expectedConfigHash = null
    ): array
    {
        [$config, $snapshotId, $configHash] = $this->thresholdsFromRunSnapshot(
            $configSnapshotId,
            $expectedConfigHash
        );

        $bars = $this->artifacts->loadBarsForTradeDate($tradeDate, $publicationId);
        $delivered = count($bars);

        $base = [
            'date_level_anomaly_contract_version' => self::CONTRACT_VERSION,
            'date_level_anomaly_thresholds' => $config,
            'date_level_anomaly_threshold_binding' => self::THRESHOLD_BINDING_STATE,
            'date_level_anomaly_config_snapshot_id' => $snapshotId,
            'date_level_anomaly_config_hash' => $configHash,
            'date_level_anomaly_delivered_count' => $delivered,
        ];

        if ($delivered < $config['minimum_rows']) {
            /*
             * A share computed over a handful of rows is noise, not concentration. Saying so is not
             * the same as saying the date is clean, which is why the state is NOT_EVALUABLE rather
             * than CLEAN.
             */
            return $base + [
                'date_level_anomaly_state' => self::STATE_NOT_EVALUABLE,
                'date_level_anomaly_findings' => [],
                'date_level_anomaly_not_evaluable_reason' => 'DELIVERED_ROWS_BELOW_MINIMUM',
                'zero_volume_share' => null,
                'flat_bar_share' => null,
                'cross_field_contradiction_count' => null,
                'zero_volume_neighbour_baseline' => null,
                'flat_bar_neighbour_baseline' => null,
                'date_level_anomaly_neighbour_dates' => [],
            ];
        }

        $measured = $this->measure($bars);
        $neighbours = $this->neighbourDates($tradeDate, $config['neighbour_trading_days'], $knownAt);
        $baseline = $this->neighbourBaseline($neighbours, $publicationId, $config['minimum_rows']);

        $findings = [];

        if ($measured['zero_volume_share'] > $config['zero_volume_share_max']) {
            $findings[] = 'ZERO_VOLUME_SHARE_ABOVE_THRESHOLD';
        }
        if ($baseline['zero_volume_share'] !== null
            && $baseline['zero_volume_share'] > 0.0
            && $measured['zero_volume_share'] >= $baseline['zero_volume_share'] * $config['neighbour_elevation_factor']) {
            $findings[] = 'ZERO_VOLUME_SHARE_ELEVATED_AGAINST_NEIGHBOURS';
        }

        if ($measured['flat_bar_share'] > $config['flat_bar_share_max']) {
            $findings[] = 'FLAT_BAR_SHARE_ABOVE_THRESHOLD';
        }
        if ($baseline['flat_bar_share'] !== null
            && $baseline['flat_bar_share'] > 0.0
            && $measured['flat_bar_share'] >= $baseline['flat_bar_share'] * $config['neighbour_elevation_factor']) {
            $findings[] = 'FLAT_BAR_SHARE_ELEVATED_AGAINST_NEIGHBOURS';
        }

        if ($measured['cross_field_contradiction_count'] > $config['cross_field_contradiction_max']) {
            $findings[] = 'CROSS_FIELD_CONTRADICTION_CONCENTRATED_ON_DATE';
        }

        return $base + $measured + [
            'date_level_anomaly_state' => $findings === [] ? self::STATE_CLEAN : self::STATE_FINDING,
            'date_level_anomaly_findings' => $findings,
            'date_level_anomaly_not_evaluable_reason' => null,
            'zero_volume_neighbour_baseline' => $baseline['zero_volume_share'],
            'flat_bar_neighbour_baseline' => $baseline['flat_bar_share'],
            'date_level_anomaly_neighbour_dates' => $neighbours,
        ];
    }

    /**
     * Resolve the exact immutable configuration used by the owning run. Current process config is
     * deliberately never consulted here: a replay must continue to apply the bytes recorded for
     * that run even after an operator changes environment input.
     *
     * @return array{0:array<string,int|float>,1:int,2:string}
     */
    private function thresholdsFromRunSnapshot($configSnapshotId, $expectedConfigHash): array
    {
        if (! is_int($configSnapshotId) && ! (is_string($configSnapshotId) && ctype_digit($configSnapshotId))) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_SNAPSHOT_BINDING_REQUIRED');
        }

        $snapshotId = (int) $configSnapshotId;
        $expectedHash = strtolower(trim((string) $expectedConfigHash));
        if ($snapshotId <= 0 || preg_match('/^[a-f0-9]{64}$/', $expectedHash) !== 1) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_SNAPSHOT_BINDING_REQUIRED');
        }

        $snapshot = $this->configSnapshots->find($snapshotId);
        if ($snapshot === null) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_SNAPSHOT_NOT_FOUND: '.$snapshotId);
        }

        $storedHash = strtolower(trim((string) ($snapshot['config_hash'] ?? '')));
        if (! hash_equals($expectedHash, $storedHash)) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_RUN_CONFIG_HASH_MISMATCH: '.$snapshotId);
        }

        $json = $snapshot['resolved_config_json'] ?? null;
        if (! is_string($json) || ! hash_equals($storedHash, hash('sha256', $json))) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_SNAPSHOT_CONTENT_HASH_MISMATCH: '.$snapshotId);
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)
            || ! isset($decoded['resolved_config'])
            || ! is_array($decoded['resolved_config'])
            || ! isset($decoded['resolved_config']['quality_gates'])
            || ! is_array($decoded['resolved_config']['quality_gates'])
            || ! isset($decoded['resolved_config']['quality_gates']['date_level_anomaly'])
            || ! is_array($decoded['resolved_config']['quality_gates']['date_level_anomaly'])) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_MISSING_FROM_SNAPSHOT: '.$snapshotId);
        }

        $config = $decoded['resolved_config']['quality_gates']['date_level_anomaly'];
        $actualKeys = array_keys($config);
        sort($actualKeys, SORT_STRING);
        $expectedKeys = self::THRESHOLD_KEYS;
        sort($expectedKeys, SORT_STRING);
        if ($actualKeys !== $expectedKeys) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_KEYS_INVALID: '.$snapshotId);
        }

        $this->assertThresholdTypesAndRanges($config, $snapshotId);

        return [$config, $snapshotId, $storedHash];
    }

    /** @param array<string,mixed> $config */
    private function assertThresholdTypesAndRanges(array $config, int $snapshotId): void
    {
        foreach (['zero_volume_share_max', 'flat_bar_share_max', 'neighbour_elevation_factor'] as $key) {
            if (! is_float($config[$key]) && ! is_int($config[$key])) {
                throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_TYPE_INVALID: '.$snapshotId.':'.$key);
            }
            if (! is_finite((float) $config[$key])) {
                throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_RANGE_INVALID: '.$snapshotId.':'.$key);
            }
        }
        foreach (['cross_field_contradiction_max', 'neighbour_trading_days', 'minimum_rows'] as $key) {
            if (! is_int($config[$key])) {
                throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_TYPE_INVALID: '.$snapshotId.':'.$key);
            }
        }

        if ($config['zero_volume_share_max'] < 0 || $config['zero_volume_share_max'] > 1
            || $config['flat_bar_share_max'] < 0 || $config['flat_bar_share_max'] > 1
            || $config['cross_field_contradiction_max'] < 0
            || $config['neighbour_trading_days'] < 1
            || $config['neighbour_elevation_factor'] <= 1
            || $config['minimum_rows'] < 1) {
            throw new \RuntimeException('DATE_LEVEL_ANOMALY_CONFIG_RANGE_INVALID: '.$snapshotId);
        }
    }

    /**
     * @param  array<int,array<string,mixed>>  $bars
     * @return array<string,mixed>
     */
    private function measure(array $bars): array
    {
        $total = count($bars);
        $zeroVolume = 0;
        $flat = 0;
        $contradictions = 0;

        foreach ($bars as $bar) {
            $open = $this->numeric($bar, 'open');
            $high = $this->numeric($bar, 'high');
            $low = $this->numeric($bar, 'low');
            $close = $this->numeric($bar, 'close');
            $volume = $this->numeric($bar, 'volume');

            if ($volume !== null && $volume == 0.0) {
                $zeroVolume++;
            }

            if ($open !== null && $high !== null && $low !== null && $close !== null
                && $open == $high && $high == $low && $low == $close) {
                $flat++;
            }

            /*
             * The cross-field consistency rule the bars contract states: the high bounds every
             * other price and the low is bounded by them. A row that contradicts it should never
             * have been admitted, so a non-zero count here is a systematic admission failure rather
             * than a market observation.
             */
            if ($open !== null && $high !== null && $low !== null && $close !== null) {
                if ($high < $low || $high < $open || $high < $close || $low > $open || $low > $close) {
                    $contradictions++;
                }
            }
        }

        return [
            'zero_volume_share' => $total > 0 ? round($zeroVolume / $total, 6) : 0.0,
            'flat_bar_share' => $total > 0 ? round($flat / $total, 6) : 0.0,
            'cross_field_contradiction_count' => $contradictions,
        ];
    }

    /**
     * Governed trading days, never calendar days. Without the calendar this returns nothing and the
     * neighbour comparison is simply absent, rather than silently falling back to date arithmetic
     * and reporting a baseline drawn from days the market was closed.
     *
     * @return array<int,string>
     */
    private function neighbourDates($tradeDate, int $count, $knownAt = null): array
    {
        if ($count <= 0 || ! $this->calendar instanceof MarketCalendarRepository) {
            return [];
        }

        try {
            $start = $this->calendar->tradingDateWindowStart($tradeDate, $count + 1, true, $knownAt);
            $dates = $this->calendar->tradingDatesBetween($start, $tradeDate, $knownAt);
        } catch (\Throwable $unresolved) {
            return [];
        }

        $out = [];
        foreach ($dates as $date) {
            $value = is_array($date) ? ($date['cal_date'] ?? null) : (is_object($date) ? ($date->cal_date ?? null) : $date);
            $value = (string) $value;
            if ($value !== '' && $value !== (string) $tradeDate) {
                $out[] = $value;
            }
        }

        return array_values($out);
    }

    /**
     * @param  array<int,string>  $dates
     * @return array{zero_volume_share:float|null,flat_bar_share:float|null}
     */
    private function neighbourBaseline(array $dates, $publicationId, int $minimumRows): array
    {
        $zero = [];
        $flat = [];

        foreach ($dates as $date) {
            // The neighbour baseline reads the trade-date artifact, not this run's candidate: a
            // neighbour is only useful as a comparison if it is an independently settled date.
            $bars = $this->artifacts->loadBarsForTradeDate($date, null);
            if (count($bars) < $minimumRows) {
                continue;
            }
            $m = $this->measure($bars);
            $zero[] = $m['zero_volume_share'];
            $flat[] = $m['flat_bar_share'];
        }

        return [
            'zero_volume_share' => $zero === [] ? null : round(array_sum($zero) / count($zero), 6),
            'flat_bar_share' => $flat === [] ? null : round(array_sum($flat) / count($flat), 6),
        ];
    }

    /**
     * @param  array<string,mixed>  $bar
     */
    private function numeric(array $bar, string $field): ?float
    {
        if (! array_key_exists($field, $bar) || $bar[$field] === null || $bar[$field] === '') {
            return null;
        }

        return (float) $bar[$field];
    }
}
