<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ReplayVerificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_verify_replay_marks_match_for_unchanged_fixture()
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'trade_date_requested' => '2026-03-20',
                'trade_date_effective' => '2026-03-20',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'source_mode' => 'manual_file',
                'source_identity' => 'mode=manual_file',
                'publication_id' => 44,
                'publication_run_id' => 91,
                'publication_version' => 4,
                'publication_is_current' => true,
                'coverage_universe_count' => 10,
                'coverage_available_count' => 10,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'PASS',
                'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'bars_batch_hash' => 'A1',
                'indicators_batch_hash' => 'B1',
                'eligibility_batch_hash' => 'C1',
                'bars_rows_written' => 10,
                'indicators_rows_written' => 10,
                'eligibility_rows_written' => 10,
                'eligible_count' => 7,
                'invalid_bar_count' => 1,
                'invalid_indicator_count' => 2,
                'warning_count' => 0,
                'hard_reject_count' => 3,
            ]),
            'expected/expected_run_summary.json' => [
                'bars_rows_written' => 10,
                'indicators_rows_written' => 10,
                'eligibility_rows_written' => 10,
                'eligible_count' => 7,
                'invalid_bar_count' => 1,
                'invalid_indicator_count' => 2,
                'warning_count' => 0,
                'hard_reject_count' => 3,
            ],
            'expected/expected_hashes.json' => [
                'bars_batch_hash' => 'A1',
                'indicators_batch_hash' => 'B1',
                'eligibility_batch_hash' => 'C1',
            ],
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
            ],
        ], 'fixture_replay_unchanged_input'));

        $run = array_merge($this->successReadableRun(91, '2026-03-20'), [
            'invalid_bar_count' => 1,
            'invalid_indicator_count' => 2,
            'warning_count' => 0,
            'hard_reject_count' => 3,
        ]);
        $publication = (object) [
            'publication_id' => 44,
            'run_id' => 91,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(91)->andReturn((object) $run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 44
                && $selector['trade_date'] === '2026-03-20';
        }))->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(91, '2026-03-20', 44)->andReturn([
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
        ]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 44)->andReturn([
            ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 0], ['eligible' => 0], ['eligible' => 0],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3002);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['replay_id'] === 3002
                && $metric['comparison_result'] === 'MATCH'
                && $metric['replay_status'] === 'PASS'
                && $metric['mismatch_count'] === 0
                && $metric['fixture_schema_version'] === 'replay_fixture_v2'
                && $metric['artifact_changed_scope'] === 'none'
                && $metric['expected_status'] === 'SUCCESS'
                && $metric['expected_terminal_status'] === 'SUCCESS'
                && $metric['expected_publishability_state'] === 'READABLE'
                && $metric['expected_publication_id'] === 44
                && $metric['expected_publication_run_id'] === 91
                && $metric['expected_publication_version'] === 4
                && $metric['expected_is_current_publication'] === true
                && $metric['expected_coverage_gate_state'] === 'PASS'
                && $metric['expected_bars_batch_hash'] === 'A1'
                && $metric['final_reason_code'] === 'COVERAGE_THRESHOLD_MET'
                && json_decode($metric['mismatch_reason_codes_json'], true) === [];
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3002, '2026-03-20', [
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(91, $fixtureDir);

        $this->assertSame(3002, $result['replay_id']);
        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('PASS', $result['replay_status']);
        $this->assertSame(0, $result['mismatch_count']);
        $this->assertSame([], $result['mismatch_reason_codes']);
        $this->assertSame('fixture_replay_unchanged_input', $result['fixture_family']);
        $this->assertContains('lineage', $result['deterministic_fields_checked']);
    }

    /**
     * `F-MD-B18-A002-014` -- `MD-S019-R0073`, `MD-S005-R0095`, replay half of `MD-S019-R0009`.
     *
     * `B18ReplayRerunDeterminismTest` was found mistargeted: both its "runs" call
     * `MarketDataEvidenceExportService::exportReplayEvidence()` on one hand-fabricated
     * `md_replay_daily_metrics` row, twice -- nothing in it ever calls
     * `ReplayVerificationService::verifyRunAgainstFixture()`, the method that actually computes a
     * replay result. Re-exporting the same static object twice proves the exporter is
     * deterministic; it proves nothing about whether *replaying* is.
     *
     * This calls `verifyRunAgainstFixture()` itself, twice, each time through a genuinely separate
     * service instance and a genuinely separate set of mocks -- not the same object reused, and the
     * first call's return value is never fed into the second as its "actual". Each call
     * independently re-loads the fixture package from disk and re-derives the entire persisted
     * metric from scratch. The two mocks are configured to represent the same stable underlying
     * state (the point of a determinism claim), but each `nextReplayId()` mints its own identity,
     * matching how two real replay executions would each get their own `replay_id` in production.
     */
    public function test_replaying_the_same_unchanged_publication_twice_persists_byte_identical_results_except_execution_identity(): void
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'publication_id' => 61, 'publication_run_id' => 951, 'run_id' => 951,
            ]),
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
            ],
        ], 'fixture_replay_rerun_determinism'));

        $captured = [];
        foreach ([4801, 4802] as $replayId) {
            $run = (object) $this->successReadableRun(951, '2026-03-20');
            $publication = (object) [
                'publication_id' => 61, 'run_id' => 951, 'publication_version' => 4, 'is_current' => 1,
                'seal_state' => 'SEALED', 'sealed_at' => '2026-03-20 17:30:00',
            ];

            $evidence = m::mock(EodEvidenceRepository::class);
            $publications = m::mock(EodPublicationRepository::class);
            $replays = m::mock(ReplayResultRepository::class);

            $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
            $evidence->shouldReceive('findRunById')->once()->with(951)->andReturn($run);
            $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->andReturn($publication);
            $evidence->shouldReceive('dominantReasonCodes')->andReturn([
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
            ]);
            $evidence->shouldReceive('exportEligibilityRows')->andReturn(array_merge(
                array_fill(0, 7, ['eligible' => 1]), array_fill(0, 3, ['eligible' => 0])
            ));
            $replays->shouldReceive('nextReplayId')->once()->andReturn($replayId);
            $replays->shouldReceive('upsertMetric')->once()->andReturnUsing(function (array $metric) use (&$captured, $replayId) {
                $captured[$replayId] = $metric;

                return null;
            });
            $replays->shouldReceive('replaceReasonCodeCounts')->once();

            (new ReplayVerificationService($evidence, $publications, $replays))
                ->verifyRunAgainstFixture(951, $fixtureDir);
        }

        $this->assertCount(2, $captured, 'both independent replay executions must have persisted a metric');
        [$first, $second] = array_values($captured);

        $this->assertNotSame($first['replay_id'], $second['replay_id'],
            'two independent replay executions must each mint their own identity -- otherwise this is one execution compared with itself, not two');

        $diverged = [];
        foreach ($first as $field => $value) {
            if ($field === 'replay_id') {
                continue;
            }
            if ($value !== ($second[$field] ?? null)) {
                $diverged[] = $field;
            }
        }
        $this->assertSame([], $diverged,
            'these fields differed between two genuinely independent replays of the same unchanged publication and fixture, so replay is not reproducible');
    }

    /**
     * The mutation half `test_replaying_the_same_unchanged_publication_twice...` needs to be a
     * determinism claim rather than a constant-output implementation passing it by accident: a
     * genuine divergence in one real input between the two independent replay executions must move
     * the persisted result in exactly that field.
     */
    public function test_a_genuine_input_divergence_between_two_independent_replays_is_detected(): void
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'publication_id' => 62, 'publication_run_id' => 952, 'run_id' => 952,
            ]),
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_rerun_divergence_probe'));

        $captured = [];
        foreach ([['id' => 4901, 'bars' => 'A1'], ['id' => 4902, 'bars' => 'A1_DIVERGED']] as $round) {
            $run = (object) array_merge($this->successReadableRun(952, '2026-03-20'), ['bars_batch_hash' => $round['bars']]);
            $publication = (object) [
                'publication_id' => 62, 'run_id' => 952, 'publication_version' => 4, 'is_current' => 1,
                'seal_state' => 'SEALED', 'sealed_at' => '2026-03-20 17:30:00',
            ];

            $evidence = m::mock(EodEvidenceRepository::class);
            $publications = m::mock(EodPublicationRepository::class);
            $replays = m::mock(ReplayResultRepository::class);

            $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
            $evidence->shouldReceive('findRunById')->once()->with(952)->andReturn($run);
            $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->andReturn($publication);
            $evidence->shouldReceive('dominantReasonCodes')->andReturn([]);
            $evidence->shouldReceive('exportEligibilityRows')->andReturn([]);
            $replays->shouldReceive('nextReplayId')->once()->andReturn($round['id']);
            $replays->shouldReceive('upsertMetric')->once()->andReturnUsing(function (array $metric) use (&$captured, $round) {
                $captured[$round['id']] = $metric;

                return null;
            });
            $replays->shouldReceive('replaceReasonCodeCounts')->once();

            (new ReplayVerificationService($evidence, $publications, $replays))
                ->verifyRunAgainstFixture(952, $fixtureDir);
        }

        [$first, $second] = array_values($captured);
        $this->assertNotSame($first['bars_batch_hash'], $second['bars_batch_hash'],
            'a genuine input divergence between two independent replay executions did not move the persisted bars_batch_hash, so this determinism guard could not have detected real non-determinism either');
    }

    /**
     * C1 §6 step 5 (Admission): "Non-BLOCKED requires complete verifiable binding; unavailable
     * input is BLOCKED." A publication with no V2 bound context at all (Reader's
     * `V1_LEGACY_NO_V2_BOUND_CONTEXT`) must never reach a comparison verdict -- exact verification
     * is BLOCKED regardless of whether every other field would otherwise match.
     */
    public function test_admission_blocks_publication_exact_when_reader_reports_v1_legacy(): void
    {
        $result = $this->verifyWithBoundContextStatus('V1_LEGACY_NO_V2_BOUND_CONTEXT', null);

        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertSame('NOT_ADMISSIBLE', $result['comparison_result']);
        $this->assertSame('NOT_ADMISSIBLE', $result['admission_state']);
        $this->assertStringContainsString('REPLAY_BOUND_INPUT_CONTEXT_UNAVAILABLE', (string) $result['mismatch_summary']);
        $this->assertStringContainsString('V1_LEGACY_NO_V2_BOUND_CONTEXT', (string) $result['mismatch_summary']);
    }

    /**
     * The other BLOCKED path: a V2 bound context exists but fails Seal's own exact verification
     * clause (tampered/incomplete). Admission must not compare against a bound context it cannot
     * trust, and the specific reason Reader/Seal gave must survive into the stored record.
     */
    public function test_admission_blocks_publication_exact_when_reader_reports_bound_context_blocked(): void
    {
        $result = $this->verifyWithBoundContextStatus('BLOCKED', 'INPUT_CAPTURE_SEAL_VERIFICATION_COMPONENT_UNVERIFIABLE: universe_identity|x|y@run:991');

        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertSame('NOT_ADMISSIBLE', $result['comparison_result']);
        $this->assertSame('NOT_ADMISSIBLE', $result['admission_state']);
        $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_COMPONENT_UNVERIFIABLE', (string) $result['mismatch_summary']);
    }

    /**
     * The BLOCKED gate and the ordinary comparison are two different questions. A genuinely
     * `VERIFIED` bound context that still diverges on an unrelated field must fall through to the
     * normal comparison outcome (`FAIL`/`MISMATCH`) -- never `BLOCKED`, which would misreport a real
     * execution divergence as an availability problem.
     */
    public function test_admission_reports_fail_not_blocked_when_verified_context_still_diverges(): void
    {
        $result = $this->verifyWithBoundContextStatus('VERIFIED', null, ['bars_rows_written' => 999]);

        $this->assertSame('FAIL', $result['replay_status']);
        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertSame('ADMISSIBLE', $result['admission_state']);
        $this->assertContains('bars_rows_written', array_column($result['mismatches'], 'field'));
    }

    /**
     * `F-MD-B18-A002-015`, `MD-S050-R0031` -- the BLOCKED half of the C2 status pair.
     *
     * Before this, a fixture package missing a required `expected_*` field was folded into the
     * ordinary mismatch list by `compareExpectedAndActual()`, which made "the fixture's own required
     * proof was never available" indistinguishable from "the comparison executed and genuinely
     * diverged" -- both reached `MISMATCH`/`FAIL`. `Replay_Verification_Contract_LOCKED.md` "Result
     * and evidence" requires the two to stay structurally separate: `BLOCKED` is not a weaker `FAIL`,
     * it states the comparison did not execute at all.
     *
     * This fixture is genuinely missing `expected_coverage_context.coverage_reason_code` -- not
     * fabricated to look missing, actually absent from the JSON on disk
     * (`validateExpectedProofCompleteness()` walks the real decoded file). The bound context is
     * genuinely `VERIFIED` and every other field genuinely agrees, so the *only* thing making this
     * replay unusable is the missing required section -- isolating this from
     * `test_admission_reports_fail_not_blocked_when_verified_context_still_diverges` above, which is
     * the FAIL counterpart: complete proof, one deliberate field divergence.
     */
    public function test_admission_blocks_publication_exact_when_required_fixture_proof_is_missing(): void
    {
        $expected = $this->expectedReplayResult([
            'publication_id' => 944, 'publication_run_id' => 991, 'run_id' => 991,
        ]);
        unset($expected['expected_coverage_context']['coverage_reason_code']);

        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_missing_required_proof'));

        $run = (object) $this->successReadableRun(991, '2026-03-20');
        $publication = (object) [
            'publication_id' => 944, 'run_id' => 991, 'publication_version' => 4, 'is_current' => 1,
            'seal_state' => 'SEALED', 'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(991)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->andReturn([]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3910);
        $replays->shouldReceive('upsertMetric')->once();
        $replays->shouldReceive('replaceReasonCodeCounts')->once();

        $result = (new ReplayVerificationService($evidence, $publications, $replays))
            ->verifyRunAgainstFixture(991, $fixtureDir);

        $this->assertSame('BLOCKED', $result['replay_status'],
            'required proof was unavailable, not an executed comparison -- this must never read as FAIL');
        $this->assertSame('NOT_ADMISSIBLE', $result['comparison_result']);
        $this->assertSame('NOT_ADMISSIBLE', $result['admission_state']);
        $this->assertStringContainsString('REPLAY_EXPECTED_PROOF_INCOMPLETE', (string) $result['mismatch_summary']);
        $this->assertStringContainsString(
            'expected_coverage_context.coverage_reason_code',
            (string) $result['mismatch_summary'],
            'the summary must name which required section was missing, not just that something was'
        );

        // The exact missing path must still be visible in the underlying mismatch evidence, per C2:
        // BLOCKED does not mean the missing-proof fact disappears, only that it is not reported as
        // an executed divergence.
        $this->assertContains(
            'expected_proof.expected_coverage_context.coverage_reason_code',
            array_column($result['mismatches'], 'field')
        );
        $this->assertContains('REPLAY_EXPECTED_PROOF_INCOMPLETE', $result['mismatch_reason_codes']);
    }

    /**
     * `F-MD-B18-A002-021`: `temporal_identity_hash` previously read `identity_revision_set_hash`,
     * derived exclusively from market-structure/board content (C11) -- the wrong domain for
     * `MD-S050-R0008`/`MD-S019-R0067`. It now reads a `componentGroupHash()` over the bound
     * context's `universe_identity` (C02) component entries, and `event_factor_hash` gained a fifth
     * member the same way over `ancillary` (C09, which already carries contamination content).
     * Proves domain isolation directly: changing only the `universe_identity` component's
     * `payload_hash` changes `temporal_identity_hash` and nothing else; changing only `ancillary`'s
     * changes `event_factor_hash` and nothing else. A field that changed for both, or neither,
     * would mean the two domains are bleeding into each other or not read at all.
     */
    public function test_temporal_identity_and_event_factor_hash_are_domain_isolated_by_component(): void
    {
        $baseline = $this->actualBoundInputContextForComponents([
            ['stage_code' => 'COMPUTE_ELIGIBILITY', 'component_key' => 'universe_identity', 'slot_hash' => str_repeat('1', 64), 'payload_hash' => str_repeat('u', 64)],
            ['stage_code' => 'COMPUTE_INDICATORS', 'component_key' => 'ancillary', 'slot_hash' => str_repeat('2', 64), 'payload_hash' => str_repeat('n', 64)],
        ]);
        $changedUniverse = $this->actualBoundInputContextForComponents([
            ['stage_code' => 'COMPUTE_ELIGIBILITY', 'component_key' => 'universe_identity', 'slot_hash' => str_repeat('1', 64), 'payload_hash' => str_repeat('v', 64)],
            ['stage_code' => 'COMPUTE_INDICATORS', 'component_key' => 'ancillary', 'slot_hash' => str_repeat('2', 64), 'payload_hash' => str_repeat('n', 64)],
        ]);
        $changedAncillary = $this->actualBoundInputContextForComponents([
            ['stage_code' => 'COMPUTE_ELIGIBILITY', 'component_key' => 'universe_identity', 'slot_hash' => str_repeat('1', 64), 'payload_hash' => str_repeat('u', 64)],
            ['stage_code' => 'COMPUTE_INDICATORS', 'component_key' => 'ancillary', 'slot_hash' => str_repeat('2', 64), 'payload_hash' => str_repeat('m', 64)],
        ]);

        $this->assertNotSame('', $baseline['temporal_identity_hash'], 'universe_identity component must produce a real, non-empty hash');
        $this->assertNotSame('', $baseline['event_factor_hash'], 'ancillary component must contribute to a real, non-empty event_factor_hash');

        $this->assertNotSame($baseline['temporal_identity_hash'], $changedUniverse['temporal_identity_hash'],
            'changing the universe_identity component must change temporal_identity_hash');
        $this->assertSame($baseline['event_factor_hash'], $changedUniverse['event_factor_hash'],
            'changing only universe_identity must not bleed into event_factor_hash');
        $this->assertSame($baseline['calendar_status_hash'], $changedUniverse['calendar_status_hash'],
            'changing only universe_identity must not bleed into calendar_status_hash');

        $this->assertSame($baseline['temporal_identity_hash'], $changedAncillary['temporal_identity_hash'],
            'changing only ancillary must not bleed into temporal_identity_hash');
        $this->assertNotSame($baseline['event_factor_hash'], $changedAncillary['event_factor_hash'],
            'changing the ancillary component (which carries contamination content) must change event_factor_hash');
    }

    /**
     * `F-MD-B18-A002-021`: `read_model_version`/`serialization_version`/`executable_build_identity`
     * now come from Reader's decoded `registry_content` (the `registry_versions` capture's own
     * real, already-verified payload) instead of live config, but only when genuinely `VERIFIED` --
     * proving the fix does not merely swap one always-present value for another, and that an
     * unavailable decode still fails closed to empty rather than silently falling back to config.
     */
    public function test_registry_content_fields_come_from_decoded_capture_only_when_verified(): void
    {
        $withRegistryContent = $this->actualBoundInputContextForComponents([], [
            'read_model_version' => 'market_data_read_product_v1',
            'serialization_version' => 'canonical_json_v2_real',
            'executable_build' => ['build_id' => 'sha256:real_build'],
        ]);
        $this->assertSame('market_data_read_product_v1', $withRegistryContent['read_model_version']);
        $this->assertSame('canonical_json_v2_real', $withRegistryContent['serialization_version']);
        $this->assertSame('sha256:real_build', $withRegistryContent['executable_build_identity']);

        $withoutRegistryContent = $this->actualBoundInputContextForComponents([], null);
        $this->assertSame('', $withoutRegistryContent['read_model_version'],
            'a VERIFIED bound context with no decoded registry_content must report an honest empty value, never a live-config fallback');
        $this->assertSame('', $withoutRegistryContent['serialization_version']);
        $this->assertSame('', $withoutRegistryContent['executable_build_identity']);
    }

    /**
     * `F-MD-B18-A002-021` (closing review): `formula_registry_hash`/`reason_registry_hash` are the
     * `registry_versions` component's own `payload_hash` -- found by scanning `components` for
     * `component_key === 'registry_versions'`, not decoded from `registry_content` like the three
     * fields above. That scan had never been exercised by any test with a real matching component
     * present: the domain-isolation test above only supplies `universe_identity`/`ancillary`, and the
     * exhaustiveness perturbation suite's fixture supplies no `registry_versions` component either, so
     * both leave this specific extraction unproven and its baseline permanently empty. Proven directly
     * here, the same way `universe_identity`/`ancillary` are proven for their own fields: presence
     * yields the real payload_hash, changing it changes both fields, and absence is an honest empty
     * value rather than a stale one.
     */
    public function test_formula_and_reason_registry_hash_come_from_the_registry_versions_component(): void
    {
        $baseline = $this->actualBoundInputContextForComponents([
            ['stage_code' => 'RUN_CONTEXT', 'component_key' => 'registry_versions', 'slot_hash' => str_repeat('3', 64), 'payload_hash' => str_repeat('r', 64)],
        ]);
        $changed = $this->actualBoundInputContextForComponents([
            ['stage_code' => 'RUN_CONTEXT', 'component_key' => 'registry_versions', 'slot_hash' => str_repeat('3', 64), 'payload_hash' => str_repeat('s', 64)],
        ]);
        $absent = $this->actualBoundInputContextForComponents([]);

        $this->assertSame(str_repeat('r', 64), $baseline['formula_registry_hash']);
        $this->assertSame(str_repeat('r', 64), $baseline['reason_registry_hash']);
        $this->assertNotSame($baseline['formula_registry_hash'], $changed['formula_registry_hash'],
            'changing the registry_versions component payload_hash must change formula_registry_hash');
        $this->assertNotSame($baseline['reason_registry_hash'], $changed['reason_registry_hash'],
            'changing the registry_versions component payload_hash must change reason_registry_hash');
        $this->assertSame('', $absent['formula_registry_hash'],
            'with no registry_versions component at all this must be an honest empty value, never a stale or fallback one');
        $this->assertSame('', $absent['reason_registry_hash']);
    }

    private const REGISTRY_CONTENT_UNSET = '__unset__';

    /** @param array<int,array<string,string>> $components */
    private function actualBoundInputContextForComponents(array $components, $registryContent = self::REGISTRY_CONTENT_UNSET): array
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'publication_id' => 944, 'publication_run_id' => 991, 'run_id' => 991,
            ]),
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_domain_isolation_probe_'.md5(json_encode([$components, $registryContent]))));

        $run = (object) $this->successReadableRun(991, '2026-03-20');
        $publication = (object) [
            'publication_id' => 944, 'run_id' => 991, 'publication_version' => 4, 'is_current' => 1,
            'seal_state' => 'SEALED', 'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $manifest = $this->verifiedBoundContextManifest();
        $manifest->bound_input_context['components'] = $components;
        if ($registryContent !== self::REGISTRY_CONTENT_UNSET) {
            $manifest->bound_input_context['registry_content'] = $registryContent;
        }
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($manifest);
        $evidence->shouldReceive('findRunById')->once()->with(991)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->andReturn([]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3901);
        $replays->shouldReceive('upsertMetric')->once();
        $replays->shouldReceive('replaceReasonCodeCounts')->once();

        $result = (new ReplayVerificationService($evidence, $publications, $replays))
            ->verifyRunAgainstFixture(991, $fixtureDir);

        return $result['actual_context']['actual_bound_input_context'];
    }

    /**
     * @param array<string,mixed> $expectedOverride perturbs the fixture's expected run summary, to
     *   prove a VERIFIED bound context does not itself suppress an unrelated real divergence
     */
    private function verifyWithBoundContextStatus(string $status, ?string $reason, array $expectedOverride = []): array
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult(array_merge([
                'publication_id' => 944,
                'publication_run_id' => 991,
                'run_id' => 991,
            ], $expectedOverride)),
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_admission_bound_context_'.strtolower($status)));

        $run = (object) $this->successReadableRun(991, '2026-03-20');
        $publication = (object) [
            'publication_id' => 944,
            'run_id' => 991,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $manifest = $status === 'VERIFIED' ? $this->verifiedBoundContextManifest() : (object) [
            'bound_input_context' => ['available' => false, 'status' => $status, 'reason' => $reason],
        ];
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($manifest);
        $evidence->shouldReceive('findRunById')->once()->with(991)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->andReturn([]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3900);
        $replays->shouldReceive('upsertMetric')->once();
        $replays->shouldReceive('replaceReasonCodeCounts')->once();

        return (new ReplayVerificationService($evidence, $publications, $replays))
            ->verifyRunAgainstFixture(991, $fixtureDir);
    }

    public function test_verify_replay_handles_non_readable_run_as_reason_coded_expected_degrade()
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'comparison_result' => 'EXPECTED_DEGRADE',
                'trade_date_requested' => '2026-03-20',
                'trade_date_effective' => '2026-03-19',
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'final_reason_code' => 'RUN_SOURCE_RATE_LIMIT',
                'source_mode' => 'api',
                'source_name' => 'API_FREE',
                'source_provider' => 'yahoo',
                'source_final_reason_code' => 'RUN_SOURCE_RATE_LIMIT',
                'source_identity' => 'mode=api|name=API_FREE|provider=yahoo',
                'publication_id' => null,
                'publication_run_id' => null,
                'publication_version' => null,
                'publication_is_current' => false,
                'coverage_universe_count' => null,
                'coverage_available_count' => null,
                'coverage_missing_count' => null,
                'coverage_ratio' => null,
                'coverage_min_threshold' => null,
                'coverage_gate_state' => null,
                'coverage_reason_code' => null,
                'coverage_threshold_mode' => null,
                'coverage_universe_basis' => null,
                'coverage_contract_version' => null,
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'bars_rows_written' => null,
                'indicators_rows_written' => null,
                'eligibility_rows_written' => null,
                'eligible_count' => 0,
                'invalid_bar_count' => null,
                'invalid_indicator_count' => null,
                'warning_count' => null,
                'hard_reject_count' => null,
                'run_id' => 92,
            ]),
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'RUN_SOURCE_RATE_LIMIT', 'reason_count' => 1],
            ],
        ], 'fixture_replay_degraded_input'));

        $run = (object) [
            'run_id' => 92,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-19',
            'source' => 'api',
            'source_name' => 'API_FREE',
            'source_provider' => 'yahoo',
            'source_final_reason_code' => 'RUN_SOURCE_RATE_LIMIT',
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'config_version' => 'v1',
            // A replay is admissible evidence only when the configuration that produced the run
            // can be recovered, so a fixture asserting a replay verdict has to bind one.
            'config_snapshot_id' => 7001,
            'sealed_at' => null,
            'bars_batch_hash' => null,
            'indicators_batch_hash' => null,
            'eligibility_batch_hash' => null,
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $evidence->shouldReceive('findRunById')->once()->with(92)->andReturn($run);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(92)->andReturn([
            'reason_code_counts' => ['RUN_SOURCE_RATE_LIMIT' => 1],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3003);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['comparison_result'] === 'EXPECTED_DEGRADE'
                && $metric['replay_status'] === 'PASS'
                && $metric['status'] === 'HELD'
                && $metric['publishability_state'] === 'NOT_READABLE'
                && $metric['mismatch_count'] === 0;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3003, '2026-03-20', [
            ['reason_code' => 'RUN_SOURCE_RATE_LIMIT', 'reason_count' => 1],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(92, $fixtureDir);

        $this->assertSame('EXPECTED_DEGRADE', $result['comparison_result']);
        $this->assertSame('PASS', $result['replay_status']);
        $this->assertSame('HELD', $result['status']);
        $this->assertSame('NOT_READABLE', $result['publishability_state']);
    }

    public function test_verify_replay_marks_mismatch_with_reason_code_when_reason_code_counts_diverge()
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'trade_date_requested' => '2026-03-20',
                'trade_date_effective' => '2026-03-20',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'source_mode' => 'manual_file',
                'source_identity' => 'mode=manual_file',
                'publication_id' => 45,
                'publication_run_id' => 93,
                'publication_version' => 4,
                'publication_is_current' => true,
                'coverage_universe_count' => 10,
                'coverage_available_count' => 10,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'PASS',
                'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'bars_batch_hash' => 'A1',
                'indicators_batch_hash' => 'B1',
                'eligibility_batch_hash' => 'C1',
                'eligible_count' => 1,
            ]),
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 2],
            ],
        ], 'fixture_replay_reason_code_mismatch'));

        $run = (object) $this->successReadableRun(93, '2026-03-20');
        $publication = (object) [
            'publication_id' => 45,
            'run_id' => 93,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(93)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 45
                && $selector['trade_date'] === '2026-03-20';
        }))->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(93, '2026-03-20', 45)->andReturn([
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
        ]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 45)->andReturn([['eligible' => 1]]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3004);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $reasonCodes = json_decode($metric['mismatch_reason_codes_json'] ?? '[]', true);
            $summary = (string) ($metric['mismatch_summary'] ?? '');
            $mismatches = json_decode($metric['mismatches_json'] ?? '[]', true);

            return is_array($metric)
                && ($metric['replay_id'] ?? null) === 3004
                && ($metric['replay_suite'] ?? null) === 'fixture_replay_reason_code_mismatch'
                && ($metric['replay_case'] ?? null) === 'fixture_replay_reason_code_mismatch'
                && ($metric['comparison_result'] ?? null) === 'MISMATCH'
                && ($metric['replay_status'] ?? null) === 'FAIL'
                && (int) ($metric['mismatch_count'] ?? 0) > 0
                && is_array($reasonCodes)
                && in_array('REPLAY_FINAL_REASON_CODE_MISMATCH', $reasonCodes, true)
                && strpos($summary, 'REPLAY_FINAL_REASON_CODE_MISMATCH') !== false
                && strpos($summary, 'reason_code_counts') !== false
                && is_array($mismatches)
                && isset($metric['mismatches_json']);
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3004, '2026-03-20', [
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(93, $fixtureDir);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertSame('FAIL', $result['replay_status']);
        $this->assertContains('REPLAY_FINAL_REASON_CODE_MISMATCH', $result['mismatch_reason_codes']);
    }

    public function test_verify_replay_marks_mismatch_when_coverage_contract_fields_diverge()
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'trade_date_requested' => '2026-03-20',
                'trade_date_effective' => '2026-03-20',
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'source_mode' => 'manual_file',
                'source_identity' => 'mode=manual_file',
                'publication_id' => 46,
                'publication_run_id' => 94,
                'publication_version' => 4,
                'publication_is_current' => true,
                'coverage_universe_count' => 10,
                'coverage_available_count' => 10,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'PASS',
                'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
                'bars_batch_hash' => 'A1',
                'indicators_batch_hash' => 'B1',
                'eligibility_batch_hash' => 'C1',
                'eligible_count' => 1,
            ]),
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_coverage_mismatch'));

        $run = (object) array_merge($this->successReadableRun(94, '2026-03-20'), [
            'coverage_available_count' => 8,
            'coverage_missing_count' => 2,
            'coverage_ratio' => '0.8000',
            'coverage_gate_state' => 'FAIL',
            'coverage_reason_code' => 'RUN_COVERAGE_LOW',
            // MD-S040-R0077's guard gap (F-MD-B18-A002-015): only the PASS path was asserted to
            // preserve final_reason_code; a MISMATCH case was never exercised. Set explicitly here
            // (distinct from the fixture's expected COVERAGE_THRESHOLD_MET) so the assertion below
            // proves it survives a genuine divergence, not just an unchallenged echo.
            'final_reason_code' => 'RUN_COVERAGE_LOW',
            'coverage_missing_sample_json' => json_encode(['BBCA', 'BMRI']),
            'bars_rows_written' => 8,
            'indicators_rows_written' => 8,
            'eligibility_rows_written' => 8,
        ]);
        $publication = (object) [
            'publication_id' => 46,
            'run_id' => 94,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(94)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 46
                && $selector['trade_date'] === '2026-03-20';
        }))->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(94, '2026-03-20', 46)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 46)->andReturn([['eligible' => 1]]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3005);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $reasonCodes = json_decode($metric['mismatch_reason_codes_json'], true);
            return $metric['comparison_result'] === 'MISMATCH'
                && $metric['replay_status'] === 'FAIL'
                && in_array('REPLAY_COVERAGE_STATE_MISMATCH', $reasonCodes, true)
                && in_array('REPLAY_COVERAGE_RATIO_MISMATCH', $reasonCodes, true)
                && $metric['expected_coverage_gate_state'] === 'PASS';
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3005, '2026-03-20', []);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(94, $fixtureDir);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertSame('FAIL', $result['replay_status']);
        $this->assertContains('REPLAY_COVERAGE_STATE_MISMATCH', $result['mismatch_reason_codes']);
        // MD-S040-R0077 (F-MD-B18-A002-015 guard gap): the run's own final_reason_code must survive
        // in actual_context even though the comparison as a whole diverged -- a MISMATCH must not
        // blank it out or substitute the fixture's expected value.
        $this->assertSame('RUN_COVERAGE_LOW', $result['actual_context']['actual_run_context']['final_reason_code']);
        // MD-S040-R0071: the actual-side coverage_reason_code must be the run's own persisted value
        // (RUN_COVERAGE_LOW), never a reconstruction from coverage_gate_state=FAIL, which would
        // synthesize the different string COVERAGE_BELOW_THRESHOLD.
        $this->assertSame('RUN_COVERAGE_LOW', $result['actual_context']['actual_coverage_context']['coverage_reason_code']);
    }

    /**
     * `F-MD-B18-A002-015`, `MD-S050-R0031` -- corrected. This fixture's `expected_replay_result.json`
     * is missing nearly every required section (only `comparison_result`/`expected_status` are
     * present), so required proof was never available for this replay at all. Before the fix, that
     * state reached `MISMATCH`/`FAIL` -- indistinguishable from an executed comparison that genuinely
     * diverged. It must read as `BLOCKED`/`NOT_ADMISSIBLE`: the comparison never legitimately
     * executed, so nothing about it can be reported as a divergence. The missing-path fact itself is
     * still preserved (`REPLAY_EXPECTED_PROOF_INCOMPLETE` and the named path survive into the
     * summary and the raw mismatch list), just not reported as `FAIL`.
     */
    public function test_verify_replay_fails_safe_when_expected_proof_is_incomplete()
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'MATCH',
                'expected_status' => 'SUCCESS',
            ],
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_incomplete_expected'));

        $run = (object) $this->successReadableRun(95, '2026-03-20');
        $publication = (object) [
            'publication_id' => 47,
            'run_id' => 95,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(95)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 47
                && $selector['trade_date'] === '2026-03-20';
        }))->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(95, '2026-03-20', 47)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 47)->andReturn([['eligible' => 1]]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3006);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $reasonCodes = json_decode($metric['mismatch_reason_codes_json'], true);
            return $metric['comparison_result'] === 'NOT_ADMISSIBLE'
                && $metric['replay_status'] === 'BLOCKED'
                && in_array('REPLAY_EXPECTED_PROOF_INCOMPLETE', $reasonCodes, true)
                && strpos((string) $metric['mismatch_summary'], 'REPLAY_EXPECTED_PROOF_INCOMPLETE') !== false
                && strpos((string) $metric['mismatch_summary'], 'expected_run_context') !== false
                && strpos((string) $metric['mismatches_json'], 'expected_proof.expected_run_context') !== false;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3006, '2026-03-20', []);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(95, $fixtureDir, null, 47);

        $this->assertSame('NOT_ADMISSIBLE', $result['comparison_result']);
        $this->assertSame('BLOCKED', $result['replay_status']);
        $this->assertSame('NOT_ADMISSIBLE', $result['admission_state']);
        $this->assertContains('REPLAY_EXPECTED_PROOF_INCOMPLETE', $result['mismatch_reason_codes']);
        // The raw mismatch list -- distinct from the BLOCKED verdict above -- still names every
        // missing required path, so C2's "exact missing field/path" requirement is not lost.
        $this->assertContains('expected_proof.expected_run_context', array_column($result['mismatches'], 'field'));
    }

    public function test_verify_replay_throws_reason_coded_exception_when_manifest_declares_missing_file()
    {
        $fixtureDir = $this->makeFixture([
            'manifest' => $this->manifest('fixture_replay_missing_file', ['expected/expected_replay_result.json', 'expected/missing.json']),
            'expected/expected_replay_result.json' => $this->expectedReplayResult(),
            'expected/expected_reason_code_counts.json' => [],
        ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $service = new ReplayVerificationService($evidence, $publications, $replays);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_EXPECTED_PROOF_INCOMPLETE: Replay fixture file missing: expected/missing.json');

        $service->verifyRunAgainstFixture(1, $fixtureDir);
    }

    public function test_verify_replay_resolves_historical_publication_without_current_pointer_fallback()
    {
        $expected = $this->expectedReplayResult([
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'source_mode' => 'manual_file',
            'source_identity' => 'mode=manual_file',
            'publication_id' => 144,
            'publication_run_id' => 191,
            'publication_version' => 4,
            'publication_is_current' => false,
            'coverage_universe_count' => 10,
            'coverage_expected_count' => 10,
            'coverage_available_count' => 10,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'bars_rows_written' => 10,
            'indicators_rows_written' => 10,
            'eligibility_rows_written' => 10,
            'eligible_count' => 7,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
        ]);
        $expected['expected_pointer_context']['pointer_resolve_status'] = 'NOT_CURRENT_POINTER';

        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
            ],
        ], 'fixture_replay_historical_pointer_moved'));

        $run = (object) array_merge($this->successReadableRun(191, '2026-03-20'), [
            'bars_rows_written' => 10,
            'indicators_rows_written' => 10,
            'eligibility_rows_written' => 10,
            'eligible_count' => 7,
        ]);
        $historicalPublication = (object) [
            'publication_id' => 144,
            'run_id' => 191,
            'publication_version' => 4,
            'is_current' => 0,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
            'evidence_resolution_mode' => 'HISTORICAL_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'HISTORICAL_SEALED_PUBLICATION',
            'historical_publication_allowed' => true,
            'current_pointer_required' => false,
            'current_pointer_status' => 'NOT_CURRENT_POINTER',
            'artifact_scope' => 'publication:144',
            'lineage_verification_status' => 'LINEAGE_VERIFIED',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(191)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 144
                && $selector['trade_date'] === '2026-03-20';
        }))->andReturn($historicalPublication);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->once()->with(191, '2026-03-20', 144, false)->andReturn([
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
        ]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->once()->with('2026-03-20', 144, false)->andReturn([
            ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 0], ['eligible' => 0], ['eligible' => 0],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3101);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $actualContext = json_decode($metric['actual_context_json'], true);
            return $metric['comparison_result'] === 'MATCH'
                && $metric['publication_id'] === 144
                && $metric['current_publication_id'] === null
                && $metric['is_current_publication'] === false
                && ($actualContext['actual_replay_resolution_context']['replay_actual_resolution_mode'] ?? null) === 'HISTORICAL_PUBLICATION_AUDIT'
                && ($actualContext['actual_replay_resolution_context']['current_pointer_required'] ?? null) === false
                && ($actualContext['actual_replay_resolution_context']['historical_publication_allowed'] ?? null) === true
                && ($actualContext['actual_replay_resolution_context']['artifact_scope'] ?? null) === 'publication:144'
                && ($actualContext['actual_pointer_context']['pointer_resolve_status'] ?? null) === 'NOT_CURRENT_POINTER';
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3101, '2026-03-20', [
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(191, $fixtureDir);

        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('HISTORICAL_PUBLICATION_AUDIT', $result['actual_context']['actual_replay_resolution_context']['replay_actual_resolution_mode']);
        $this->assertFalse($result['actual_context']['actual_replay_resolution_context']['current_pointer_required']);
        $this->assertTrue($result['actual_context']['actual_replay_resolution_context']['historical_publication_allowed']);
    }

    /**
     * `MD-S003-R0002` -- "resolve an explicit immutable publication, not latest/current." The prior
     * guard for this predicate was a static source-text check
     * (`B18ReplayContractStaticGuardTest::test_exact_missing_publication_fails_closed`) asserting the
     * reason code string and the absence of one specific literal call pattern in the file -- it never
     * executed the refusal or proved anything about pointer/current lookup being genuinely bypassed.
     *
     * This test calls the real `verifyRunAgainstFixture` with a `READABLE`-expected run and no
     * explicit publication id anywhere (no 4th argument, no `manifest.publication_id`, no
     * `expected_publication_context.publication_id`), and proves two things behaviourally: the
     * refusal fires with the correct reason code, and it fires *before* any pointer/current lookup --
     * `findCurrentPublicationForTradeDate`, `findReadableCurrentPublicationForRun`, and
     * `resolvePublicationForEvidenceAudit` are none of them stubbed, so an unexpected call to any of
     * them fails the test with a Mockery exception rather than silently resolving a substitute
     * publication.
     */
    public function test_publication_exact_refuses_a_readable_expectation_with_no_explicit_publication_id_before_any_pointer_lookup(): void
    {
        $expected = $this->expectedReplayResult([
            'trade_date_requested' => '2026-04-01',
            'trade_date_effective' => '2026-04-01',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'publication_id' => null,
            'publication_run_id' => null,
            'publication_version' => null,
            'publication_is_current' => false,
        ]);

        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_no_explicit_publication_declared'));

        $run = (object) $this->successReadableRun(555, '2026-04-01');

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(555)->andReturn($run);
        $evidence->shouldNotReceive('resolvePublicationForEvidenceAudit');
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $publications->shouldNotReceive('findReadableCurrentPublicationForRun');

        $service = new ReplayVerificationService($evidence, $publications, $replays);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_EXPLICIT_PUBLICATION_REQUIRED');

        $service->verifyRunAgainstFixture(555, $fixtureDir);
    }

    /**
     * `MD-S003-R0002` positive control -- the same `READABLE`-expected run as above, but with a valid
     * explicit publication id supplied as the 4th argument, proving the refusal boundary is not
     * simply "reject every readable case": a genuinely explicit identity is accepted and resolved
     * through the explicit path, and no pointer/current lookup is consulted here either.
     */
    public function test_publication_exact_accepts_a_readable_expectation_with_a_genuinely_explicit_publication_id(): void
    {
        $expected = $this->expectedReplayResult([
            'trade_date_requested' => '2026-04-01',
            'trade_date_effective' => '2026-04-01',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'publication_id' => null,
            'publication_run_id' => null,
            'publication_version' => null,
            'publication_is_current' => false,
        ]);
        $expected['expected_pointer_context']['pointer_resolve_status'] = 'NOT_CURRENT_POINTER';

        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_genuinely_explicit_publication'));

        $run = (object) $this->successReadableRun(556, '2026-04-01');

        $explicitPublication = (object) [
            'publication_id' => 61,
            'run_id' => 556,
            'publication_version' => 4,
            'is_current' => 0,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-01 17:30:00',
            'evidence_resolution_mode' => 'HISTORICAL_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'HISTORICAL_SEALED_PUBLICATION',
            'historical_publication_allowed' => true,
            'current_pointer_required' => false,
            'current_pointer_status' => 'NOT_CURRENT_POINTER',
            'artifact_scope' => 'publication:61',
            'lineage_verification_status' => 'LINEAGE_VERIFIED',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $publications->shouldNotReceive('findReadableCurrentPublicationForRun');
        $evidence->shouldReceive('findRunById')->once()->with(556)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && $selector['publication_id'] === 61
                && $selector['trade_date'] === '2026-04-01';
        }))->andReturn($explicitPublication);
        $evidence->shouldReceive('dominantReasonCodesForEvidencePublication')->once()->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRowsForEvidencePublication')->once()->andReturn([]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3102);
        $replays->shouldReceive('upsertMetric')->once();
        $replays->shouldReceive('replaceReasonCodeCounts')->once();

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(556, $fixtureDir, null, 61);

        $this->assertSame(61, $result['publication_id'],
            'a genuinely explicit publication id must resolve through the explicit path, proving the refusal boundary is not reject-everything');
        $this->assertSame(61, $result['actual_context']['actual_publication_context']['publication_id']);
    }

    public function test_verify_replay_matches_unchanged_correction_preserved_baseline_publication()
    {
        $expected = $this->expectedReplayResult([
            'trade_date_requested' => '2026-02-18',
            'trade_date_effective' => '2026-02-18',
            'run_id' => 408,
            'request_mode' => 'correction',
            'publication_id' => 305,
            'publication_run_id' => 306,
            'publication_version' => 4,
            'publication_is_current' => true,
            'correction_id' => 55,
            'correction_status' => 'CONSUMED_CURRENT',
            'correction_outcome' => 'UNCHANGED',
            'correction_reseal_status' => 'NOT_RESEALED_UNCHANGED',
            'correction_publication_switch' => false,
            'baseline_publication_id' => 305,
            'candidate_publication_id' => 307,
            'eligible_count' => 7,
        ]);
        $expected['expected_run_context']['import_status'] = 'COMPLETED';
        $expected['expected_run_context']['promote_status'] = 'NOT_PROMOTED';
        $expected['expected_run_context']['promoted'] = false;
        $expected['expected_run_context']['pointer_switched'] = false;
        $expected['expected_coverage_context']['coverage_basis'] = 'CandidatePublication';
        $expected['expected_coverage_context']['coverage_basis_publication_id'] = 307;
        $expected['expected_coverage_context']['coverage_basis_artifact_scope'] = 'candidate_publication_artifact';
        $expected['expected_coverage_context']['candidate_publication_id'] = 307;
        $expected['expected_coverage_context']['baseline_publication_id'] = 305;
        $expected['expected_artifact_context']['artifact_scope'] = 'unchanged_correction_candidate_artifact:307';
        $expected['expected_pointer_context'] = [
            'pointer_publication_id' => 305,
            'pointer_run_id' => 306,
            'pointer_publication_version' => 4,
            'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'pointer_switched' => false,
            'current_pointer_required' => true,
            'historical_publication_allowed' => false,
        ];
        $expected['expected_replay_resolution_context'] = [
            'replay_actual_resolution_mode' => 'UNCHANGED_CORRECTION_BASELINE_PRESERVED_AUDIT',
            'replay_publication_scope' => 'UNCHANGED_CORRECTION_PRESERVED_CURRENT_POINTER',
            'replay_selector_type' => 'replay_unchanged_correction_actual_state',
            'replay_selector_id' => 305,
            'historical_publication_allowed' => false,
            'current_pointer_required' => true,
            'current_pointer_status' => 'RESOLVED_READABLE_CURRENT',
            'publication_id' => 305,
            'publication_version' => 4,
            'publication_run_id' => 306,
            'run_id' => 408,
            'run_publication_mirror_status' => 'UNCHANGED_CORRECTION_BASELINE_PRESERVED',
            'seal_state' => 'SEALED',
            'is_current_publication' => true,
            'artifact_scope' => 'unchanged_correction_candidate_artifact:307',
            'coverage_basis_publication_id' => 307,
            'coverage_basis_run_id' => 408,
            'lineage_verification_status' => 'UNCHANGED_CORRECTION_BASELINE_PRESERVED',
            'replay_reason_code' => 'CORRECTION_BASELINE_POINTER_PRESERVED',
        ];
        $expected['expected_lineage']['run_id'] = 408;
        $expected['expected_lineage']['publication_id'] = 305;
        $expected['expected_lineage']['current_publication_id'] = 305;
        $expected['expected_lineage']['publication_run_id'] = 306;
        $expected['expected_lineage']['correction_id'] = 55;

        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'CORRECTION_ARTIFACT_UNCHANGED', 'reason_count' => 2],
                ['reason_code' => 'CORRECTION_PROMOTE_REQUIRED', 'reason_count' => 1],
            ],
        ], 'fixture_replay_unchanged_correction_preserved_baseline'));

        $run = (object) array_merge($this->successReadableRun(408, '2026-02-18'), [
            'request_mode' => 'correction',
            'notes' => 'request_mode=correction; coverage_basis=CandidatePublication; coverage_basis_publication_id=307; candidate_publication_id=307; baseline_publication_id=305; coverage_basis_artifact_scope=candidate_publication_artifact',
        ]);
        $baselinePublication = (object) [
            'publication_id' => 305,
            'run_id' => 306,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-02-18 17:30:00',
            'evidence_resolution_mode' => 'CURRENT_READABLE_PUBLICATION_AUDIT',
            'evidence_publication_scope' => 'CURRENT_POINTER_PUBLICATION',
            'historical_publication_allowed' => false,
            'current_pointer_required' => true,
        ];
        $correction = (object) [
            'correction_id' => 55,
            'status' => 'CONSUMED_CURRENT',
            'baseline_publication_id' => 305,
            'prior_publication_id' => 305,
            'prior_run_id' => 306,
            'replacement_publication_id' => null,
            'new_publication_id' => 305,
            'new_publication_is_current' => 0,
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(408)->andReturn($run);
        $evidence->shouldReceive('findCorrectionByRunId')->once()->with(408)->andReturn($correction);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 305
                && $selector['trade_date'] === '2026-02-18';
        }))->andReturn($baselinePublication);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(408)->andReturn([
            'reason_code_counts' => [
                'CORRECTION_ARTIFACT_UNCHANGED' => 2,
                'CORRECTION_PROMOTE_REQUIRED' => 1,
            ],
        ]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-02-18', 305)->andReturn([
            ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 1], ['eligible' => 0], ['eligible' => 0], ['eligible' => 0],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3201);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $actualContext = json_decode($metric['actual_context_json'], true);

            return $metric['comparison_result'] === 'MATCH'
                && $metric['replay_status'] === 'PASS'
                && $metric['publication_id'] === 305
                && $metric['publication_run_id'] === 306
                && $metric['correction_publication_switch'] === false
                && $metric['candidate_publication_id'] === 307
                && ($actualContext['actual_replay_resolution_context']['run_publication_mirror_status'] ?? null) === 'UNCHANGED_CORRECTION_BASELINE_PRESERVED'
                && ($actualContext['actual_replay_resolution_context']['coverage_basis_publication_id'] ?? null) === 307
                && ($actualContext['actual_pointer_context']['pointer_switched'] ?? null) === false;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3201, '2026-02-18', [
            ['reason_code' => 'CORRECTION_ARTIFACT_UNCHANGED', 'reason_count' => 2],
            ['reason_code' => 'CORRECTION_PROMOTE_REQUIRED', 'reason_count' => 1],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(408, $fixtureDir);

        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('PASS', $result['replay_status']);
        $this->assertSame('UNCHANGED_CORRECTION_BASELINE_PRESERVED', $result['actual_context']['actual_replay_resolution_context']['run_publication_mirror_status']);
        $this->assertFalse($result['actual_context']['actual_pointer_context']['pointer_switched']);
    }

    public function test_verify_replay_maps_unsealed_historical_publication_to_reason_coded_failure()
    {
        $expected = $this->expectedReplayResult([
            'publication_id' => 145,
            'publication_run_id' => 192,
            'publication_is_current' => false,
            'run_id' => 192,
        ]);
        $expected['expected_pointer_context']['pointer_resolve_status'] = 'NOT_CURRENT_POINTER';
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_historical_unsealed'));

        $run = (object) $this->successReadableRun(192, '2026-03-20');
        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $evidence->shouldReceive('findRunById')->once()->with(192)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->andThrow(new RuntimeException('EVIDENCE_HISTORICAL_PUBLICATION_UNSEALED: Historical publication must be SEALED for audit evidence.'));

        $service = new ReplayVerificationService($evidence, $publications, $replays);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_HISTORICAL_PUBLICATION_UNSEALED');

        $service->verifyRunAgainstFixture(192, $fixtureDir);
    }

    public function test_verify_replay_normalizes_legacy_blocked_coverage_state_and_preserves_raw_trace(): void
    {
        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $this->expectedReplayResult([
                'comparison_result' => 'MATCH',
                'trade_date_requested' => '2026-03-21',
                'trade_date_effective' => '2026-03-21',
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'final_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE',
                'source_mode' => 'manual_file',
                'source_identity' => 'mode=manual_file',
                'publication_id' => null,
                'publication_run_id' => null,
                'publication_version' => null,
                'publication_is_current' => false,
                'coverage_universe_count' => 0,
                'coverage_available_count' => 0,
                'coverage_missing_count' => 0,
                'coverage_ratio' => null,
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'NOT_EVALUABLE',
                'coverage_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE',
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'bars_rows_written' => null,
                'indicators_rows_written' => null,
                'eligibility_rows_written' => null,
                'eligible_count' => 0,
                'invalid_bar_count' => null,
                'invalid_indicator_count' => null,
                'warning_count' => null,
                'hard_reject_count' => null,
                'seal_state' => 'UNSEALED',
                'run_id' => 196,
            ]),
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE', 'reason_count' => 1],
            ],
        ], 'fixture_replay_legacy_blocked_coverage_input'));

        $run = (object) [
            'run_id' => 196,
            // Bound for the same reason as the other run fixtures: this case asserts a replay
            // verdict, and a verdict over an unbindable run is not admissible evidence.
            'config_snapshot_id' => 7001,
            'trade_date_requested' => '2026-03-21',
            'trade_date_effective' => '2026-03-21',
            'source' => 'manual_file',
            'terminal_status' => 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'final_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE',
            'coverage_universe_count' => 0,
            'coverage_expected_count' => 0,
            'coverage_available_count' => 0,
            'coverage_missing_count' => 0,
            'coverage_ratio' => null,
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'BLOCKED',
            'coverage_reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'sealed_at' => null,
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(196)->andReturn($run);
        $evidence->shouldReceive('summarizeRunEvents')->once()->with(196)->andReturn([
            'reason_code_counts' => ['RUN_COVERAGE_NOT_EVALUABLE' => 1],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3102);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $actualContext = json_decode($metric['actual_context_json'], true);

            return $metric['comparison_result'] === 'MATCH'
                && $metric['coverage_gate_state'] === 'NOT_EVALUABLE'
                && $metric['expected_coverage_gate_state'] === 'NOT_EVALUABLE'
                && ($actualContext['actual_coverage_context']['coverage_gate_state'] ?? null) === 'NOT_EVALUABLE'
                && ($actualContext['actual_coverage_context']['legacy_coverage_gate_state_raw'] ?? null) === 'BLOCKED'
                && strpos($metric['actual_context_json'], '"coverage_gate_state":"BLOCKED"') === false;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3102, '2026-03-21', [
            ['reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE', 'reason_count' => 1],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(196, $fixtureDir);

        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('NOT_EVALUABLE', $result['coverage_gate_state']);
        $this->assertSame('BLOCKED', $result['actual_context']['actual_coverage_context']['legacy_coverage_gate_state_raw']);
    }

    public function test_replay_detects_analytical_factor_set_identity_drift(): void
    {
        $expectedFactorHash = str_repeat('a', 64);
        $actualFactorHash = str_repeat('b', 64);
        $expected = $this->expectedReplayResult([
            'publication_id' => 55,
            'publication_run_id' => 103,
            'run_id' => 103,
        ]);
        $expected['expected_publication_context'] += [
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_id' => null,
            'factor_set_hash' => $expectedFactorHash,
        ];
        $expected['expected_lineage'] += [
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_id' => null,
            'factor_set_hash' => $expectedFactorHash,
        ];

        $fixtureDir = $this->makeFixture($this->fixturePayload([
            'expected/expected_replay_result.json' => $expected,
            'expected/expected_reason_code_counts.json' => [],
        ], 'fixture_replay_analytical_identity_drift'));

        $run = (object) ($this->successReadableRun(103, '2026-03-20') + [
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $actualFactorHash,
        ]);
        $publication = (object) [
            'publication_id' => 55,
            'run_id' => 103,
            'publication_version' => 4,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_id' => null,
            'factor_set_hash' => $actualFactorHash,
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $publications->shouldReceive('buildManifestByPublicationId')->andReturn($this->verifiedBoundContextManifest());
        $evidence->shouldReceive('findRunById')->once()->with(103)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_fixture_explicit_publication'
                && ! array_key_exists('run_id', $selector)
                && $selector['publication_id'] === 55
                && $selector['trade_date'] === '2026-03-20';
        }))->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(103, '2026-03-20', 55)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 55)->andReturn([]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3301);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['comparison_result'] === 'MISMATCH'
                && in_array('REPLAY_LINEAGE_MISMATCH', json_decode($metric['mismatch_reason_codes_json'], true), true);
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3301, '2026-03-20', []);

        $result = (new ReplayVerificationService($evidence, $publications, $replays))
            ->verifyRunAgainstFixture(103, $fixtureDir);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertContains('REPLAY_LINEAGE_MISMATCH', $result['mismatch_reason_codes']);
        $this->assertSame($actualFactorHash, $result['actual_context']['actual_publication_context']['factor_set_hash']);
    }

    /**
     * C1 §6 step 5 (Admission): `replayAdmissibility()`/`actualBoundInputContext()` now read a
     * publication's producer-bound context through `buildManifestByPublicationId()`. These
     * pre-existing fixtures are not about that classification at all, so they stub a genuinely
     * `VERIFIED` projection -- the same shape Reader actually returns -- to keep exercising the
     * comparison behaviour they were written for, unaffected by the new gate.
     *
     * `MD-S050-R0016`: this stub used to carry no components and no `registry_content`, a VERIFIED
     * context `PublicationInputBindingService::bind()` cannot produce (`universe_identity` and
     * `registry_versions` are required slots), with every input they supply resolving empty. Once
     * an unavailable input is BLOCKED that state no longer reaches a comparison, so the stub now
     * binds them the way a real binding does.
     */
    private function verifiedBoundContextManifest(): object
    {
        return (object) [
            'bound_input_context' => [
                'available' => true,
                'status' => 'VERIFIED',
                'schema_version' => 'md_publication_inputs_v2',
                'reason' => null,
                'bound_input_context_hash' => str_repeat('f', 64),
                'components' => [
                    ['stage_code' => 'COMPUTE_ELIGIBILITY', 'component_key' => 'universe_identity', 'slot_hash' => str_repeat('1', 64), 'payload_hash' => str_repeat('u', 64)],
                    ['stage_code' => 'COMPUTE_INDICATORS', 'component_key' => 'ancillary', 'slot_hash' => str_repeat('2', 64), 'payload_hash' => str_repeat('n', 64)],
                    ['stage_code' => 'RUN_CONTEXT', 'component_key' => 'registry_versions', 'slot_hash' => str_repeat('3', 64), 'payload_hash' => str_repeat('r', 64)],
                ],
                'scope' => [],
                'component_manifest' => ['status' => 'COMPLETE'],
                'registry_content' => [
                    'read_model_version' => 'market_data_read_product_v1',
                    'serialization_version' => 'canonical_json_v1',
                    'executable_build' => ['build_id' => 'sha256:test_build_identity'],
                ],
            ],
            'identity_revision_set_hash' => str_repeat('1', 64),
            'calendar_revision_set_hash' => str_repeat('2', 64),
            'status_revision_set_hash' => str_repeat('3', 64),
            'event_revision_set_hash' => str_repeat('4', 64),
            'source_scale_assessment_set_hash' => str_repeat('5', 64),
            'factor_decision_set_hash' => str_repeat('6', 64),
            'factor_set_hash' => str_repeat('7', 64),
        ];
    }

    private function successReadableRun($runId, $tradeDate)
    {
        return [
            'run_id' => $runId,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'config_version' => 'v1',
            // A replay is admissible evidence only when the configuration that produced the run
            // can be recovered, so a fixture asserting a replay verdict has to bind one.
            'config_snapshot_id' => 7001,
            'publication_version' => 4,
            'coverage_universe_count' => 10,
            'coverage_expected_count' => 10,
            'coverage_available_count' => 10,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            // F-MD-B18-A002-015 G04, MD-S040-R0071: the actual/producer-side persisted value this
            // shared fixture's runs carry. Any test that overrides coverage_gate_state must override
            // this alongside it to keep the fixture internally consistent.
            'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'bars_rows_written' => 10,
            'indicators_rows_written' => 10,
            'eligibility_rows_written' => 10,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            // MD-S050-R0016: the source observation identity is a required bound input read off
            // the run; a run that acquired its observations carries one.
            'observation_manifest_hash' => str_repeat('9', 64),
            'sealed_at' => $tradeDate.' 17:30:00',
        ];
    }

    private function expectedReplayResult(array $overrides = [])
    {
        $v = array_merge([
            'comparison_result' => 'MATCH',
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'source_mode' => 'manual_file',
            'source_name' => null,
            'source_provider' => null,
            'source_identity' => 'mode=manual_file',
            'source_file_hash' => null,
            'source_final_reason_code' => null,
            'source_file_row_count' => null,
            'publication_id' => 44,
            'publication_run_id' => 91,
            'publication_version' => 4,
            'publication_is_current' => true,
            'coverage_universe_count' => 10,
            'coverage_expected_count' => 10,
            'coverage_available_count' => 10,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample' => [],
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'bars_rows_written' => 10,
            'indicators_rows_written' => 10,
            'eligibility_rows_written' => 10,
            'eligible_count' => null,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
        ], $overrides);
        if (! array_key_exists('coverage_expected_count', $overrides)) {
            $v['coverage_expected_count'] = $v['coverage_universe_count'];
        }
        if (! array_key_exists('run_id', $v) && array_key_exists('publication_run_id', $v)) {
            $v['run_id'] = $v['publication_run_id'];
        }

        return [
            'comparison_result' => $v['comparison_result'],
            'comparison_note' => 'deterministic replay fixture expectation',
            'expected_run_context' => [
                'run_id' => $v['run_id'] ?? null,
                'trade_date_requested' => $v['trade_date_requested'],
                'trade_date_effective' => $v['trade_date_effective'],
                'request_mode' => $v['request_mode'] ?? null,
                'promote_mode' => $v['promote_mode'] ?? null,
                'publish_target' => $v['publish_target'] ?? null,
                'terminal_status' => $v['terminal_status'],
                'publishability_state' => $v['publishability_state'],
                'final_reason_code' => $v['final_reason_code'],
            ],
            'expected_source_context' => [
                'source_mode' => $v['source_mode'],
                'source_name' => $v['source_name'],
                'source_provider' => $v['source_provider'],
                'provider' => $v['source_provider'],
                'source_identity' => $v['source_identity'],
                'source_file_hash' => $v['source_file_hash'],
                'source_final_reason_code' => $v['source_final_reason_code'],
                'source_file_row_count' => $v['source_file_row_count'],
                'accepted_row_count' => $v['accepted_row_count'] ?? $v['bars_rows_written'] ?? null,
                'rejected_row_count' => $v['rejected_row_count'] ?? $v['invalid_bar_count'] ?? null,
                'invalid_row_count' => $v['invalid_row_count'] ?? $v['invalid_bar_count'] ?? null,
            ],
            'expected_coverage_context' => [
                'coverage_universe_count' => $v['coverage_universe_count'],
                'coverage_expected_count' => $v['coverage_expected_count'],
                'coverage_available_count' => $v['coverage_available_count'],
                'coverage_missing_count' => $v['coverage_missing_count'],
                'expected_bar_count' => $v['coverage_expected_count'],
                'available_bar_count' => $v['coverage_available_count'],
                'missing_bar_count' => $v['coverage_missing_count'],
                'coverage_ratio' => $v['coverage_ratio'],
                'coverage_min_threshold' => $v['coverage_min_threshold'],
                'coverage_gate_state' => $v['coverage_gate_state'],
                'coverage_reason_code' => $v['coverage_reason_code'],
                'coverage_threshold_mode' => $v['coverage_threshold_mode'],
                'coverage_universe_basis' => $v['coverage_universe_basis'],
                'coverage_contract_version' => $v['coverage_contract_version'],
                'coverage_missing_sample' => $v['coverage_missing_sample'],
            ],
            'expected_artifact_context' => [
                'bars_rows_written' => $v['bars_rows_written'] ?? null,
                'indicators_rows_written' => $v['indicators_rows_written'] ?? null,
                'eligibility_rows_written' => $v['eligibility_rows_written'] ?? null,
                'eligible_count' => $v['eligible_count'] ?? null,
                'invalid_bar_count' => $v['invalid_bar_count'] ?? null,
                'invalid_indicator_count' => $v['invalid_indicator_count'] ?? null,
                'warning_count' => $v['warning_count'] ?? null,
                'hard_reject_count' => $v['hard_reject_count'] ?? null,
                'bars_batch_hash' => $v['bars_batch_hash'],
                'indicators_batch_hash' => $v['indicators_batch_hash'],
                'eligibility_batch_hash' => $v['eligibility_batch_hash'],
            ],
            'expected_seal_context' => [
                'seal_state' => $v['seal_state'] ?? ($v['terminal_status'] === 'SUCCESS' ? 'SEALED' : 'UNSEALED'),
            ],
            'expected_publication_context' => [
                'publication_id' => $v['publication_id'],
                'current_publication_id' => $v['publication_is_current'] ? $v['publication_id'] : null,
                'publication_run_id' => $v['publication_run_id'],
                'publication_version' => $v['publication_version'],
                'publication_terminal_status' => $v['terminal_status'],
                'publication_publishability_state' => $v['publishability_state'],
                'publication_is_current' => $v['publication_is_current'],
                'publication_seal_state' => $v['seal_state'] ?? ($v['terminal_status'] === 'SUCCESS' ? 'SEALED' : 'UNSEALED'),
            ],
            'expected_pointer_context' => [
                'pointer_publication_id' => $v['publication_id'],
                'pointer_run_id' => $v['publication_run_id'],
                'pointer_publication_version' => $v['publication_version'],
                'pointer_resolve_status' => $v['publishability_state'] === 'READABLE' && $v['publication_is_current'] ? 'RESOLVED_READABLE_CURRENT' : 'NOT_RESOLVED_READABLE_CURRENT',
                'pointer_switched' => $v['publication_is_current'],
            ],
            'expected_fallback_context' => [
                'fallback_used' => $v['fallback_used'] ?? false,
                'fallback_publication_id' => $v['fallback_publication_id'] ?? null,
                'fallback_run_id' => $v['fallback_run_id'] ?? null,
            ],
            'expected_correction_context' => [
                'correction_id' => $v['correction_id'] ?? null,
                'correction_status' => $v['correction_status'] ?? null,
                'correction_outcome' => $v['correction_outcome'] ?? null,
                'correction_reseal_status' => $v['correction_reseal_status'] ?? null,
                'correction_publication_switch' => $v['correction_publication_switch'] ?? null,
                'baseline_publication_id' => $v['baseline_publication_id'] ?? null,
                'candidate_publication_id' => $v['candidate_publication_id'] ?? null,
            ],
            'expected_final_state' => [
                'terminal_status' => $v['terminal_status'],
                'publishability_state' => $v['publishability_state'],
                'final_reason_code' => $v['final_reason_code'],
            ],
            'expected_reason_code' => $v['final_reason_code'],
            'expected_lineage' => [
                'run_id' => $v['run_id'] ?? $v['publication_run_id'],
                'publication_id' => $v['publication_id'],
                'current_publication_id' => $v['publication_is_current'] ? $v['publication_id'] : null,
                'publication_run_id' => $v['publication_run_id'],
                'correction_id' => $v['correction_id'] ?? null,
                'source_file_hash' => $v['source_file_hash'],
                'bars_batch_hash' => $v['bars_batch_hash'],
                'indicators_batch_hash' => $v['indicators_batch_hash'],
                'eligibility_batch_hash' => $v['eligibility_batch_hash'],
                'final_reason_code' => $v['final_reason_code'],
            ],
        ];
    }

    private function fixturePayload(array $files, $fixtureId)
    {
        $manifestFiles = array_values(array_filter(array_keys($files), function ($path) {
            return $path !== 'manifest';
        }));
        if (! in_array('expected/expected_reason_code_counts.json', $manifestFiles, true)) {
            $manifestFiles[] = 'expected/expected_reason_code_counts.json';
            $files['expected/expected_reason_code_counts.json'] = [];
        }
        $files['manifest'] = $this->manifest($fixtureId, $manifestFiles);
        $expectedReplay = $files['expected/expected_replay_result.json'] ?? null;
        $explicitPublicationId = is_array($expectedReplay)
            ? ($expectedReplay['expected_publication_context']['publication_id'] ?? null)
            : null;
        if ($explicitPublicationId !== null && $explicitPublicationId !== '') {
            $files['manifest']['publication_id'] = (int) $explicitPublicationId;
        }
        return $files;
    }

    private function manifest($fixtureId, array $files)
    {
        return [
            'fixture_id' => $fixtureId,
            'fixture_family' => $fixtureId,
            'fixture_version' => 'v2',
            'fixture_schema_version' => 'replay_fixture_v2',
            'fixture_created_at' => '2026-05-07T00:00:00+07:00',
            'fixture_source' => 'unit_test',
            'version' => 'v2',
            'contract_areas' => ['replay_verification', 'replay_determinism'],
            'files' => $files,
            'assertion_layers' => ['run', 'source', 'coverage', 'hash', 'publication', 'pointer', 'fallback', 'correction', 'lineage', 'replay'],
        ];
    }

    private function makeFixture(array $files)
    {
        $dir = sys_get_temp_dir().'/market_data_replay_fixture_'.uniqid();
        mkdir($dir, 0775, true);

        foreach ($files as $relativePath => $payload) {
            $path = $dir.'/'.$relativePath;
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0775, true);
            }
            file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        file_put_contents($dir.'/manifest.json', json_encode($files['manifest'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $dir;
    }
}
