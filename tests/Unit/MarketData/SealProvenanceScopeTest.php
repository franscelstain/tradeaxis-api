<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Models\EodRun;

/**
 * F-033 — a seal states what it covers, and the gate demands only what the run could produce.
 *
 * Binding config identity woke an integrity check that had been wrapped in
 * `if (! empty($run->config_snapshot_id))` since it was written. Every run carried NULL, so it had
 * never executed for any of 64,939 publications; awake, it refused every new promote run because it
 * demands an acquisition manifest and none has ever existed.
 *
 * The demand was misdirected. `observation_manifest_hash` records which acquired observations
 * produced a candidate, and it is written by the ingest path. A recompute run acquires nothing — it
 * recomputes analytics over bars that already exist — so requiring it to present an acquisition
 * manifest asks it to attest to work it did not do.
 *
 * The three cases below are the whole rule. The last one is the one that must never soften: a run
 * that did acquire and cannot produce a manifest still fails, which is what keeps this a narrowed
 * claim rather than a re-gating into dormancy.
 */
class SealProvenanceScopeTest extends TestCase
{
    private function scopeFor(array $publication, array $run): string
    {
        $repository = (new ReflectionClass(EodPublicationRepository::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($repository, 'sealProvenanceScope');
        $method->setAccessible(true);

        return $method->invoke($repository, (object) $publication, new EodRun($run));
    }

    public function test_a_recompute_without_acquisition_provenance_is_sealed_analytically(): void
    {
        $scope = $this->scopeFor(
            ['observation_manifest_hash' => null],
            ['promote_mode' => 'analytical_remediation_current']
        );

        $this->assertSame('ANALYTICAL_ONLY', $scope);
    }

    /**
     * A carried-forward manifest outranks the mode. Provenance that is present and true does not
     * become weaker because a later run, rather than the acquiring one, recorded it.
     */
    public function test_a_recompute_that_inherits_a_manifest_is_sealed_fully(): void
    {
        $scope = $this->scopeFor(
            ['observation_manifest_hash' => str_repeat('a', 64)],
            ['promote_mode' => 'analytical_remediation_current']
        );

        $this->assertSame('FULL', $scope);
    }

    /**
     * The case that must keep failing. A run that acquired and cannot present a manifest is still
     * held to FULL, so the gate refuses it exactly as it does today.
     */
    public function test_an_acquiring_run_without_a_manifest_is_still_held_to_full_scope(): void
    {
        foreach (['full_publish', 'correction_current', null] as $promoteMode) {
            $scope = $this->scopeFor(
                ['observation_manifest_hash' => null],
                ['promote_mode' => $promoteMode]
            );

            $this->assertSame(
                'FULL',
                $scope,
                'promote_mode '.var_export($promoteMode, true).' acquires, so it may not claim a narrowed seal'
            );
        }
    }
}
