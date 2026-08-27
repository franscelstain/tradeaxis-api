<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\LiquidityMetricLabelRegistry;
use App\Domain\MarketData\MarketDataSemanticBindings;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Resolution and enforcement of the persisted actual-versus-proxy labelling.
 *
 * Three things are deliberately separate here, because collapsing any two of them reintroduces the
 * defect the contract describes:
 *
 *  - `resolve()` reads the persisted label. It never falls back to the declared registry. A label
 *    that exists in code but not in the deployed database is exactly the case the contract calls
 *    carrying the properties "nowhere a consumer can read".
 *  - `assertPublishable()` refuses to publish a populated liquidity metric with no persisted label.
 *    Absent is unlabelled, not proxy.
 *  - `driftAgainstDeclared()` compares the deployed rows to the declaration, in both directions.
 */
class LiquidityMetricLabelService
{
    const TABLE = 'md_liquidity_metric_labels';

    /** @var array<string,array<string,mixed>|null>|null */
    private $cache;

    /**
     * Persist the declared label set. Existing rows are updated in place because a label is a
     * description of a metric, not an immutable record of an event: correcting a mis-declared
     * window would otherwise be impossible without orphaning the metric it describes.
     */
    public function syncDeclared($now = null): int
    {
        $now = $now ?: date('Y-m-d H:i:s');
        $written = 0;

        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            $key = [
                'metric_field' => $label['metric_field'],
                'formula_version' => $label['formula_version'],
            ];
            $values = [
                'metric_scope' => $label['metric_scope'],
                'metric_kind' => $label['metric_kind'],
                'price_basis' => $label['price_basis'],
                'window_sessions' => $label['window_sessions'],
                'unit_code' => $label['unit_code'],
                'market_scope' => $label['market_scope'],
                'quality_state_field' => $label['quality_state_field'],
                'is_compatibility_alias' => $label['is_compatibility_alias'] ? 1 : 0,
                'aliases_metric_field' => $label['aliases_metric_field'],
                'retirement_condition' => $label['retirement_condition'],
            ];

            $existing = DB::table(self::TABLE)->where($key)->first();
            if ($existing === null) {
                DB::table(self::TABLE)->insert($key + $values + ['created_at' => $now]);
            } else {
                DB::table(self::TABLE)->where($key)->update($values);
            }
            $written++;
        }

        $this->cache = null;

        return $written;
    }

    /**
     * The persisted label for one metric field, or null when the metric is unlabelled.
     */
    public function resolve(string $metricField, string $formulaVersion = null): ?array
    {
        $formulaVersion = $formulaVersion ?: $this->versionFor($metricField);
        $cacheKey = $metricField.'@'.$formulaVersion;

        if (isset($this->cache) && array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $row = DB::table(self::TABLE)
            ->where('metric_field', $metricField)
            ->where('formula_version', $formulaVersion)
            ->first();

        $label = $row === null ? null : $this->normalizeRow($row);
        $this->cache[$cacheKey] = $label;

        return $label;
    }

    /**
     * Every persisted label for the metrics carried by one stored scope, keyed by metric field.
     *
     * This is the consumer-facing shape: a read product exposes it beside the values so the
     * actual/proxy distinction travels with the numbers instead of depending on column names.
     */
    public function labelsForScope(string $metricScope): array
    {
        $out = [];
        foreach (LiquidityMetricLabelRegistry::declared() as $declared) {
            if ($declared['metric_scope'] !== $metricScope) {
                continue;
            }
            $label = $this->resolve($declared['metric_field'], $declared['formula_version']);
            if ($label !== null) {
                $out[$declared['metric_field']] = $label;
            }
        }

        return $out;
    }

    /**
     * Fail closed before publication.
     *
     * Only populated metrics are checked. A NULL actual traded value is the contract's required
     * value when the source does not supply one, and requiring a label for a value that is not
     * there would turn a correct null into a publication failure.
     *
     * @param  array<string,mixed>  $row
     * @param  array<int,string>  $metricFields
     */
    public function assertPublishable(array $row, array $metricFields, string $formulaVersion = null): void
    {
        $unlabelled = $this->unlabelledMetrics($row, $metricFields, $formulaVersion);

        if ($unlabelled !== []) {
            throw new RuntimeException(
                'UNLABELLED_LIQUIDITY_METRIC: '.implode(', ', $unlabelled)
                .' carry a value with no persisted actual-versus-proxy marker. An unlabelled liquidity metric may not be published,'
                .' and an absent marker does not mean proxy.'
            );
        }
    }

    /**
     * @param  array<string,mixed>  $row
     * @param  array<int,string>  $metricFields
     * @return array<int,string>
     */
    public function unlabelledMetrics(array $row, array $metricFields, string $formulaVersion = null): array
    {
        $unlabelled = [];
        foreach ($metricFields as $field) {
            if (! array_key_exists($field, $row) || $row[$field] === null) {
                continue;
            }
            if ($this->resolve($field, $formulaVersion) === null) {
                $unlabelled[] = $field;
            }
        }

        return $unlabelled;
    }

    /**
     * Populated liquidity metrics in a stored artifact that carry no persisted label.
     *
     * The scan is by version rather than by row: a stored table holds one label-relevant version
     * per row, so checking each distinct version that actually carries a populated metric costs a
     * handful of queries instead of one per row, and reaches exactly the same rows.
     *
     * `$versionColumn` is null for artifacts the source supplies directly, which have no platform
     * formula version of their own.
     *
     * @return array<int,string> "metric_field@formula_version" for each unlabelled metric
     */
    public function unlabelledPublishedMetrics(string $table, string $tradeDateColumn, string $tradeDate, string $metricScope, string $versionColumn = null, array $scope = []): array
    {
        $fields = [];
        foreach (LiquidityMetricLabelRegistry::declared() as $declared) {
            if ($declared['metric_scope'] === $metricScope) {
                $fields[] = $declared['metric_field'];
            }
        }
        if ($fields === []) {
            return [];
        }

        $unlabelled = [];

        foreach ($fields as $field) {
            $query = DB::table($table)
                ->where($tradeDateColumn, $tradeDate)
                ->whereNotNull($field);

            // The prohibition is on what this publication makes readable. Rows belonging to an
            // earlier publication of the same date were published under whatever rules applied
            // then, and blocking a new seal on their account would make the guard unrunnable
            // rather than strict.
            foreach ($scope as $column => $value) {
                $query->where($column, $value);
            }

            if ($versionColumn === null) {
                if (! $query->exists()) {
                    continue;
                }
                $versions = [MarketDataSemanticBindings::SOURCE_REPORTED_LIQUIDITY_VERSION];
            } else {
                $versions = $query->distinct()->pluck($versionColumn)->all();
            }

            foreach ($versions as $version) {
                $version = (string) $version;
                // A populated metric on a row that states no version cannot resolve a label at all.
                // That is unlabelled in the strongest sense, and it is reported as such rather than
                // silently defaulted to the current version.
                if (trim($version) === '') {
                    $unlabelled[] = $field.'@<no-version>';

                    continue;
                }
                if ($this->resolve($field, $version) === null) {
                    $unlabelled[] = $field.'@'.$version;
                }
            }
        }

        return array_values(array_unique($unlabelled));
    }

    /**
     * Deployed-versus-declared comparison, in both directions.
     *
     * @return array<string,array<int,string>>
     */
    public function driftAgainstDeclared(): array
    {
        $declared = [];
        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            $declared[$label['metric_field'].'@'.$label['formula_version']] = $label;
        }

        $deployed = [];
        foreach (DB::table(self::TABLE)->get() as $row) {
            $normalized = $this->normalizeRow($row);
            $deployed[$normalized['metric_field'].'@'.$normalized['formula_version']] = $normalized;
        }

        $missing = array_values(array_diff(array_keys($declared), array_keys($deployed)));
        $unexpected = array_values(array_diff(array_keys($deployed), array_keys($declared)));
        $mismatched = [];

        foreach ($declared as $key => $label) {
            if (! isset($deployed[$key])) {
                continue;
            }
            foreach (['metric_scope', 'metric_kind', 'price_basis', 'window_sessions', 'unit_code', 'market_scope', 'is_compatibility_alias', 'aliases_metric_field'] as $field) {
                if ($this->comparable($label[$field]) !== $this->comparable($deployed[$key][$field])) {
                    $mismatched[] = $key.'.'.$field;
                }
            }
            // An alias without a retirement condition is the failure mode the contract names by
            // name: it becomes permanent, and its misleading reading becomes the default meaning.
            if ($label['is_compatibility_alias'] && trim((string) $deployed[$key]['retirement_condition']) === '') {
                $mismatched[] = $key.'.retirement_condition_absent';
            }
        }

        return [
            'missing' => $missing,
            'unexpected' => $unexpected,
            'mismatched' => array_values(array_unique($mismatched)),
        ];
    }

    private function comparable($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private function versionFor(string $metricField): string
    {
        $declared = LiquidityMetricLabelRegistry::declaredFor($metricField);

        return $declared === null
            ? MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION
            : $declared['formula_version'];
    }

    private function normalizeRow($row): array
    {
        $row = (array) $row;

        return [
            'metric_field' => (string) $row['metric_field'],
            'metric_scope' => (string) $row['metric_scope'],
            'formula_version' => (string) $row['formula_version'],
            'metric_kind' => (string) $row['metric_kind'],
            'price_basis' => (string) $row['price_basis'],
            'window_sessions' => $row['window_sessions'] === null ? null : (int) $row['window_sessions'],
            'unit_code' => (string) $row['unit_code'],
            'market_scope' => (string) $row['market_scope'],
            'quality_state_field' => $row['quality_state_field'] === null ? null : (string) $row['quality_state_field'],
            'is_compatibility_alias' => (bool) $row['is_compatibility_alias'],
            'aliases_metric_field' => $row['aliases_metric_field'] === null ? null : (string) $row['aliases_metric_field'],
            'retirement_condition' => $row['retirement_condition'] === null ? null : (string) $row['retirement_condition'],
        ];
    }
}
