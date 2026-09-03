<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B17-A001` standing guard on the two alias-retirement conditions.
 *
 * `Domain_Boundary_Invariants_LOCKED.md` and `Volume_and_Turnover_Normalization_LOCKED.md` each make
 * retirement of a compatibility alias conditional on the same thing, and both say it the same way:
 *
 *   > The alias is retired once no consumer outside this package reads it, which must be
 *   > demonstrated rather than assumed.
 *
 * No consumer surface is exposed, so no demonstration exists and the retirement obligation is not
 * applicable. That has two halves, and a guard that only checked one would be worth little:
 *
 *   - the retirement must not happen — the alias stays, and stays marked compatibility-only;
 *   - the condition must stay false — if a demonstration is ever recorded, this fails and the
 *     predicate returns to applicable with its own obligation to prove.
 *
 * A condition recorded as not applicable and never re-checked is how an obligation disappears.
 */
class ReadProductAliasRetirementBoundaryB17Test extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $this->assertFileExists($path, $relative.' must exist for this guard to mean anything');

        return (string) file_get_contents($path);
    }

    /**
     * MD-S020-R0069 and MD-S086-R0025: neither alias is retired, and neither has lost the
     * compatibility marking that says it is an alias rather than the canonical field.
     */
    public function test_neither_compatibility_alias_is_retired_without_the_demonstration_the_contract_requires(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        // ---- the aliases still exist. Retiring one without the demonstration is the thing the
        // condition forbids, and it would show up here first.
        // Each alias exists in both the current table and its history twin. Counting rather than
        // matching is deliberate: a probe that removed the alias from one table left an earlier
        // version of this assertion green, because the other occurrence still satisfied it. An
        // alias retired from history is retired.
        $this->assertSame(
            2,
            preg_match_all('/^\s+eligible TINYINT\(1\) NOT NULL,/m', $schema),
            'the `eligible` compatibility alias is no longer present in both the current and history '
                .'tables, which is retirement without the demonstration MD-S020-R0069 requires'
        );
        $this->assertSame(
            2,
            preg_match_all('/^\s+dv20_idr DECIMAL\(\d+,\d+\) NULL,/m', $schema),
            'the `dv20_idr` compatibility alias is no longer present in both the current and history '
                .'tables, which is retirement without the demonstration MD-S086-R0025 requires'
        );

        // ---- each is still marked as an alias rather than the canonical field. An alias that
        // quietly becomes the canonical name is retirement by another route.
        $this->assertStringContainsString(
            'eod_eligibility is a compatibility projection',
            $schema,
            'the schema no longer records that `eligible` is a compatibility projection'
        );

        $indicators = $this->read('app/Application/MarketData/Services/IndicatorVectorService.php');
        $this->assertStringContainsString(
            'dv20_idr',
            $indicators,
            'the indicator engine no longer publishes the legacy alias it is required to keep'
        );
        $this->assertStringContainsString(
            'adv20_close_volume_proxy_idr',
            $indicators,
            'the canonical proxy field must remain, or the alias has nothing to be an alias of'
        );

        // ---- the condition itself is still false. A recorded demonstration would make the
        // retirement obligation applicable, and this test is what notices.
        $demonstrations = glob($this->root().'/docs/market_data/records/decisions/*ALIAS_RETIREMENT*');
        $this->assertSame(
            [],
            is_array($demonstrations) ? $demonstrations : [],
            'an alias-retirement demonstration has been recorded, so MD-S020-R0069 and MD-S086-R0025 '
                .'are applicable again and must be proven rather than left NOT_APPLICABLE'
        );
    }

    /**
     * The canonical field each alias stands for is present and distinct. Without this the guard
     * above could pass on a package where the alias is the only field left, which is the opposite
     * of what the contracts intend.
     */
    public function test_each_alias_still_stands_for_a_distinct_canonical_field(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        // `eligible` is the compatibility name for data_usable; the canonical meaning lives in the
        // fact dimensions MD-B16 made first-class, not in the boolean alone.
        foreach (['bar_expectation_state', 'delivery_state', 'canonical_quality_state', 'indicator_state'] as $dimension) {
            $this->assertMatchesRegularExpression(
                '/^\s+'.preg_quote($dimension, '/').' VARCHAR/m',
                $schema,
                $dimension.' is missing, so `eligible` would be the only usability fact on the row'
            );
        }

        $this->assertMatchesRegularExpression(
            '/^\s+adv20_close_volume_proxy_idr DECIMAL\(\d+,\d+\) NULL,/m',
            $schema,
            '`dv20_idr` must alias an explicitly named proxy field, not stand alone'
        );
    }
}
