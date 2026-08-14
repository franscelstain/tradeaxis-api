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

        $evidence->shouldReceive('findRunById')->once()->with(91)->andReturn((object) $run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(91, '2026-03-20')->andReturn($publication);
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
        $evidence->shouldReceive('findRunById')->once()->with(93)->andReturn($run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(93, '2026-03-20')->andReturn($publication);
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
        $evidence->shouldReceive('findRunById')->once()->with(94)->andReturn($run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(94, '2026-03-20')->andReturn($publication);
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
    }

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
        $evidence->shouldReceive('findRunById')->once()->with(95)->andReturn($run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(95, '2026-03-20')->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(95, '2026-03-20', 47)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 47)->andReturn([['eligible' => 1]]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3006);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            $reasonCodes = json_decode($metric['mismatch_reason_codes_json'], true);
            return $metric['comparison_result'] === 'MISMATCH'
                && in_array('REPLAY_EXPECTED_PROOF_INCOMPLETE', $reasonCodes, true)
                && strpos((string) $metric['mismatch_summary'], 'expected_proof.expected_run_context') !== false;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3006, '2026-03-20', []);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(95, $fixtureDir);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertContains('REPLAY_EXPECTED_PROOF_INCOMPLETE', $result['mismatch_reason_codes']);
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

        $evidence->shouldReceive('findRunById')->once()->with(191)->andReturn($run);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_historical_actual_state'
                && $selector['run_id'] === 191
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

        $evidence->shouldReceive('findRunById')->once()->with(408)->andReturn($run);
        $evidence->shouldReceive('findCorrectionByRunId')->once()->with(408)->andReturn($correction);
        $evidence->shouldReceive('resolvePublicationForEvidenceAudit')->once()->with(m::on(function ($selector) {
            return $selector['type'] === 'replay_unchanged_correction_actual_state'
                && $selector['run_id'] === 306
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
        $evidence->shouldReceive('findRunById')->once()->with(103)->andReturn($run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(103, '2026-03-20')->andReturn($publication);
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
