<?php

use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\IndicatorVectorService;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B14-A001` guards for two obligations the indicator engine carried in name only.
 *
 * `Indicator_Registry_Baseline_LOCKED.md` requires a versioned registry entry per registered field
 * declaring its status, dependencies and window rule, basis and unit, warm-up rule, precision and
 * serialization rule, allowed null reason codes, and formula and registry version. The
 * implementation declared none of them; precision in particular lived in three places at once — the
 * schema, the hash serializer and the migration — with nothing asserting they agreed.
 *
 * `Indicator_Nullability_And_OHLCV_Gap_Contract.md` requires nullability to be per field and
 * reason-coded, and forbids a compatibility primary reason from erasing the field-level sets. The
 * row carried `invalid_reason_code` and nothing else; `null_reasons_json` was a column the pipeline
 * carried and the read repository selected, that no code ever wrote a value into.
 */
class IndicatorFieldRegistryAndNullReasonsTest extends TestCase
{
    /** @return array<string,mixed> */
    private function config(): array
    {
        return [
            'set_version' => 'ind_v1',
            'formula_version' => 'formula_v1',
            'price_basis_default' => 'close',
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
            'atr_contamination_horizon_days' => 60,
            'benchmark_roc20_pct' => 5.0,
            'sector_roc20_pct' => 2.5,
            'sector_code' => 'G',
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function bars(int $days): array
    {
        $rows = [];
        for ($i = 1; $i <= $days; $i++) {
            $close = 100 + $i;
            $rows[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-04-01 +'.($i - 1).' days')),
                'open' => $close - 1,
                'high' => $close + 1,
                'low' => $close - 2,
                'close' => $close,
                'adj_close' => $close,
                'volume' => 1000 + ($i * 10),
            ];
        }

        return $rows;
    }

    private function project(string $relative): string
    {
        return (string) file_get_contents(dirname(__DIR__, 3).'/'.$relative);
    }

    /**
     * MD-S081-R0049 to R0054: every registered field carries every required declaration, and the
     * set of registered fields covers everything the engine publishes a window for.
     */
    public function test_every_registered_field_declares_the_whole_registry_entry(): void
    {
        $service = new IndicatorVectorService();
        $registry = $service->fieldRegistry($this->config());

        $this->assertGreaterThanOrEqual(
            24,
            count($registry),
            'the registry must cover the registered baseline field set, not a sample of it'
        );

        foreach ($registry as $field => $entry) {
            foreach (IndicatorVectorService::REGISTRY_ENTRY_KEYS as $key) {
                $this->assertArrayHasKey($key, $entry, $field.' does not declare '.$key);
            }
            $this->assertContains($entry['status'], ['required', 'optional'], $field.' has no required/optional status');
            $this->assertNotSame('', trim((string) $entry['window_rule']), $field.' declares no window or state rule');
            $this->assertNotSame('', trim((string) $entry['warm_up_rule']), $field.' declares no warm-up rule');
            $this->assertNotSame('', trim((string) $entry['unit']), $field.' declares no unit');
            $this->assertNotSame('', trim((string) $entry['basis']), $field.' declares no basis');
            $this->assertNotSame([], $entry['dependency_fields'], $field.' declares no dependencies');
            $this->assertSame('formula_v1', $entry['formula_version'], $field.' is not bound to the formula version');
            $this->assertSame('ind_v1', $entry['registry_version'], $field.' is not bound to the registry version');
            foreach (['scale', 'rounding', 'serialization'] as $part) {
                $this->assertArrayHasKey($part, $entry['precision'], $field.' declares no '.$part);
            }
        }

        // The registry names these explicitly as required baseline fields. `atr14` is the one that
        // had a column, a hash scale and a pipeline slot while nothing ever computed it.
        foreach (['ma20', 'ma50', 'roc5', 'roc10', 'roc20', 'hh20', 'll20', 'atr14', 'atr14_pct',
            'vol_ratio', 'adv20_traded_value_idr_actual', 'adv20_close_volume_proxy_idr'] as $field) {
            $this->assertArrayHasKey($field, $registry, $field.' is a registered baseline field with no registry entry');
        }
    }

    /**
     * The registry fails closed on a field it does not know, rather than publishing a window whose
     * precision, warm-up and reason codes nobody declared.
     */
    public function test_a_published_window_with_no_registry_entry_fails_closed(): void
    {
        $service = new IndicatorVectorService();

        $horizons = new ReflectionMethod(IndicatorVectorService::class, 'contaminationHorizons');
        $horizons->setAccessible(true);
        $published = array_keys($horizons->invoke($service, $this->config()));

        $registered = array_keys($service->fieldRegistry($this->config()));

        $this->assertSame(
            [],
            array_values(array_diff($published, $registered)),
            'a window the engine publishes has no registry entry, which is the state the guard exists to stop'
        );

        // The declaration must also be complete: a missing key is an error, not a default.
        $entry = $service->fieldRegistry($this->config())['ma20'];
        unset($entry['precision']);
        $this->assertNotEmpty(
            array_diff(IndicatorVectorService::REGISTRY_ENTRY_KEYS, array_keys($entry)),
            'the completeness check would not notice a dropped declaration'
        );
    }

    /**
     * MD-S060-R0013 and MD-S081-R0052: one precision rule per field. The schema, the hash
     * serializer and the registry must state the same scale, or a value rounds one way on write and
     * another way into the hash that gates its own correction.
     */
    public function test_declared_precision_matches_the_schema_and_the_hash_serializer(): void
    {
        $registry = (new IndicatorVectorService())->fieldRegistry($this->config());
        $schema = $this->project('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        $scale = new ReflectionMethod(DeterministicHashService::class, 'decimalScale');
        $scale->setAccessible(true);
        $hasher = new DeterministicHashService();

        $checked = 0;
        foreach ($registry as $field => $entry) {
            if ($entry['precision']['scale'] === null) {
                continue;
            }
            $declared = (int) $entry['precision']['scale'];

            $this->assertSame(
                $declared,
                (int) $scale->invoke($hasher, $field),
                $field.': the hash serializer rounds to a different scale than the registry declares'
            );

            if (preg_match('/^\s+'.preg_quote($field, '/').' DECIMAL\((\d+),(\d+)\)/m', $schema, $m) === 1) {
                $this->assertSame(
                    $declared,
                    (int) $m[2],
                    $field.': the deployed schema stores a different scale than the registry declares'
                );
                $checked++;
            }
        }

        $this->assertGreaterThanOrEqual(
            20,
            $checked,
            'the schema comparison must reach the field set, otherwise it passes by finding nothing'
        );
    }

    /** Every reason code the registry allows must be a code the reason-code registry owns. */
    public function test_every_declared_null_reason_code_is_a_registered_code(): void
    {
        $registry = (new IndicatorVectorService())->fieldRegistry($this->config());
        $codes = $this->project('docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md');

        $seen = 0;
        foreach ($registry as $field => $entry) {
            foreach ($entry['null_reason_codes'] as $code) {
                $this->assertMatchesRegularExpression(
                    '/\|\s*`'.preg_quote($code, '/').'`\s*\|/',
                    $codes,
                    $field.' allows '.$code.', which the reason-code registry does not own'
                );
                $seen++;
            }
        }

        $this->assertGreaterThan(0, $seen, 'no reason code was checked, so this proves nothing');
    }

    /** MD-S081: `atr14` is a required baseline field and must actually be published. */
    public function test_the_registered_atr_level_is_published_and_not_permanently_null(): void
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertArrayHasKey('atr14', $row, 'the registered ATR level is not emitted at all');
        $this->assertNotNull($row['atr14'], 'the registered ATR level is null on a fully warmed series');
        $this->assertIsFloat($row['atr14']);

        // The level and the ratio come from one recursion, so they must agree.
        $this->assertEqualsWithDelta(
            $row['atr14_pct'],
            $row['atr14'] / 155.0,
            1e-9,
            'the published level and ratio do not come from the same ATR'
        );
    }

    /**
     * MD-S037-R0002 and MD-S061-R0019: nullability is per field and reason-coded, emitted as a
     * field-level set rather than inferred from the single primary reason.
     */
    public function test_a_short_history_row_carries_field_level_reasons_not_only_the_primary_reason(): void
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(15), '2026-04-15', 15, 9001, '2026-04-15 18:00:00', $this->config());

        $this->assertSame('IND_INSUFFICIENT_HISTORY', $row['invalid_reason_code']);
        $this->assertNotNull($row['null_reasons_json'], 'the field-level reason set was not emitted');

        $reasons = json_decode($row['null_reasons_json'], true);
        $this->assertIsArray($reasons);

        // ma50 cannot exist on 21 bars, and its reason must say so by name.
        $this->assertArrayHasKey('ma50', $reasons, 'a null field carries no reason of its own');
        $this->assertContains('IND_INSUFFICIENT_HISTORY', $reasons['ma50']);

        // A field whose own warm-up is met is not listed, because it is not null.
        $this->assertArrayNotHasKey('roc5', $reasons, 'a populated field must not carry a null reason');
        $this->assertNotNull($row['roc5']);
    }

    /**
     * MD-S037-R0010: the compatibility primary reason must not erase the field-level sets. Both are
     * present on the same row, and the field-level set names the contamination cause per field.
     */
    public function test_a_contaminated_row_keeps_field_level_reasons_beside_the_primary_reason(): void
    {
        $config = $this->config();
        $config['corporate_action_contamination'] = [[
            'action_type_code' => 'STOCK_SPLIT',
            'action_date' => '2026-05-20',
            'depth' => 3,
            'breaks_price_continuity' => true,
            'breaks_volume_continuity' => false,
        ]];

        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $config);

        $this->assertSame('IND_CORPORATE_ACTION_DISCONTINUITY', $row['invalid_reason_code']);
        $this->assertNotNull($row['null_reasons_json'], 'the primary reason replaced the field-level set');

        $reasons = json_decode($row['null_reasons_json'], true);
        $this->assertNotSame([], $reasons);

        foreach (['ma20', 'ma50', 'roc20'] as $field) {
            $this->assertNull($row[$field], $field.' was not quarantined');
            $this->assertArrayHasKey($field, $reasons, $field.' is null with no recorded reason');
            $this->assertContains(
                'IND_CORPORATE_ACTION_DISCONTINUITY',
                $reasons[$field],
                $field.' does not name the contamination that nulled it'
            );
        }

        // A volume-only field is untouched by a price-only break, so it must not be listed.
        $this->assertNotNull($row['vol_ratio'], 'a price-only break nulled a volume-only field');
        $this->assertArrayNotHasKey('vol_ratio', $reasons);
    }

    /**
     * MD-S028-R0024: the causes stay distinct. An unexplained price-scale break must not be
     * reported under the corporate-action code, because the two are different facts about the
     * window and an operator filters on them differently.
     */
    public function test_a_price_scale_break_is_reason_coded_distinctly_from_a_corporate_action(): void
    {
        $config = $this->config();
        $config['price_scale_break_contamination'] = [[
            'trade_date' => '2026-05-20',
            'depth' => 3,
            'break_type' => 'SCALE_SHIFT',
            'matched_action_type' => null,
        ]];

        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $config);

        $reasons = json_decode((string) $row['null_reasons_json'], true);
        $this->assertIsArray($reasons);
        $this->assertArrayHasKey('ma20', $reasons);

        $this->assertContains('IND_PRICE_SCALE_DISCONTINUITY', $reasons['ma20']);
        $this->assertNotContains(
            'IND_CORPORATE_ACTION_DISCONTINUITY',
            $reasons['ma20'],
            'an unexplained break was reported as a recorded corporate action'
        );
    }

    /** A fully warmed, uncontaminated row has nothing to explain and must not invent reasons. */
    public function test_a_clean_row_carries_no_field_level_reasons(): void
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertSame(1, $row['is_valid']);
        $this->assertNull($row['invalid_reason_code']);
        $this->assertNull($row['null_reasons_json'], 'a clean row must not carry a reason set');
    }
}
