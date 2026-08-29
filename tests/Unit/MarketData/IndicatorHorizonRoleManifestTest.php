<?php

use App\Application\MarketData\Services\IndicatorVectorService;

/**
 * Remediation guard for F-MD-B01-A008-001, open since MD-B01-A008.
 *
 * `Terminology_and_Scope.md` locks three horizon roles and makes declaring one mandatory:
 *
 *   > A window whose role is undeclared cannot be justified by the horizon and must not be added to
 *   > the baseline field set.
 *
 * The finding recorded that eleven dependency windows existed and none declared a role anywhere in
 * the repository, and that deriving a role from a span is not the same as declaring one. The
 * dependency manifest now declares it; this asserts the declaration is complete, drawn from the
 * locked vocabulary, and consistent with the horizon it is measured against.
 */
class IndicatorHorizonRoleManifestTest extends TestCase
{
    /** @return array<string,int|float> */
    private function config(): array
    {
        return [
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'hh_window_days' => 20,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'atr_contamination_horizon_days' => 60,
        ];
    }

    /** MD-S056-R0022 and MD-S056-R0129: no window enters the set without a declared role. */
    public function test_every_published_dependency_window_declares_a_horizon_role(): void
    {
        $manifest = (new IndicatorVectorService())->dependencyManifest($this->config());

        $this->assertGreaterThanOrEqual(
            20,
            count($manifest),
            'the manifest must cover the published field set, not a sample of it'
        );

        foreach ($manifest as $field => $entry) {
            $this->assertArrayHasKey('horizon_role', $entry, $field.' has no horizon role');
            $this->assertContains(
                $entry['horizon_role'],
                IndicatorVectorService::HORIZON_ROLES,
                $field.' declares a role outside the three locked by Terminology_and_Scope.md'
            );
        }
    }

    /**
     * MD-S056-R0019 to R0021: the roles are not interchangeable labels. A decision window spans at
     * most the horizon; a context window deliberately spans beyond it; a state window has no fixed
     * span because it carries recursive state.
     */
    public function test_each_role_matches_the_span_the_contract_defines_for_it(): void
    {
        $horizon = IndicatorVectorService::DECISION_HORIZON_TRADING_DAYS;
        $this->assertSame(5, $horizon, 'the Weekly Swing decision horizon is 5 IDX trading days');

        $manifest = (new IndicatorVectorService())->dependencyManifest($this->config());
        $seen = [];

        foreach ($manifest as $field => $entry) {
            $seen[$entry['horizon_role']] = true;
            if ($entry['horizon_role'] === IndicatorVectorService::HORIZON_ROLE_DECISION) {
                // roc5 reads D[-5]..D, so its row span is the horizon plus the anchor bar.
                $this->assertLessThanOrEqual(
                    $horizon + 1,
                    $entry['window_days'],
                    $field.' is declared a decision window but spans beyond the horizon'
                );
            }
            if ($entry['horizon_role'] === IndicatorVectorService::HORIZON_ROLE_CONTEXT) {
                $this->assertGreaterThan(
                    $horizon + 1,
                    $entry['window_days'],
                    $field.' is declared a context window but does not span beyond the horizon'
                );
            }
        }

        $this->assertArrayHasKey(IndicatorVectorService::HORIZON_ROLE_DECISION, $seen);
        $this->assertArrayHasKey(IndicatorVectorService::HORIZON_ROLE_CONTEXT, $seen);
        $this->assertArrayHasKey(IndicatorVectorService::HORIZON_ROLE_STATE, $seen);
    }

    /**
     * MD-S056-R0024: the contamination radius must be published as a number, and it equals the
     * longest dependency window in the published field set. MD-S081-R0034 states the same number
     * from the other side — impact for a correction at `T` covers at minimum `T` through `T+49`.
     * The implementation and the contract must agree, or one of them is wrong.
     */
    public function test_the_contamination_radius_is_published_and_matches_the_registry(): void
    {
        $service = new IndicatorVectorService();
        $radius = $service->fixedWindowContaminationRadius($this->config());

        $this->assertSame(50, $radius, 'T through T+49 is fifty sessions');

        $manifest = $service->dependencyManifest($this->config());
        $longestFixed = 0;
        foreach ($manifest as $entry) {
            if ($entry['horizon_role'] === IndicatorVectorService::HORIZON_ROLE_STATE) {
                continue;
            }
            $longestFixed = max($longestFixed, $entry['window_days']);
        }

        $this->assertSame($longestFixed, $radius, 'the radius is the longest fixed dependency window');
    }

    /**
     * The manifest fails closed rather than defaulting. A window with no declared role must stop
     * the manifest, because a silent default is exactly the state the finding recorded: a role that
     * could be derived by a reader but was never declared by the platform.
     */
    public function test_a_window_without_a_declared_role_fails_closed(): void
    {
        $roles = new ReflectionMethod(IndicatorVectorService::class, 'horizonRoles');
        $roles->setAccessible(true);
        $declared = $roles->invoke(null);

        $windows = new ReflectionMethod(IndicatorVectorService::class, 'contaminationHorizons');
        $windows->setAccessible(true);
        $published = $windows->invoke(new IndicatorVectorService(), $this->config());

        $this->assertSame(
            [],
            array_keys(array_diff_key($published, $declared)),
            'a published window with no declared role would reach the baseline field set'
        );
        $this->assertSame(
            [],
            array_keys(array_diff_key($declared, $published)),
            'a declared role for a window nobody publishes is a stale declaration'
        );
    }
}
