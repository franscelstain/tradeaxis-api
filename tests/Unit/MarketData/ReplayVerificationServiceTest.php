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
        $fixtureDir = $this->makeFixture([
            'manifest' => [
                'fixture_family' => 'fixture_replay_unchanged_input',
                'version' => 'v1',
                'contract_areas' => ['replay'],
                'files' => [
                    'expected/expected_replay_result.json',
                    'expected/expected_run_summary.json',
                    'expected/expected_hashes.json',
                    'expected/expected_reason_code_counts.json',
                ],
                'assertion_layers' => ['run', 'hash', 'replay'],
            ],
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'MATCH',
                'expected_status' => 'SUCCESS',
                'expected_terminal_status' => 'SUCCESS',
                'expected_publishability_state' => 'READABLE',
                'expected_trade_date_effective' => '2026-03-20',
                'expected_seal_state' => 'SEALED',
                'config_identity' => 'v1',
                'publication_id' => 44,
                'publication_run_id' => 91,
                'publication_version' => 4,
                'is_current_publication' => true,
                'coverage_universe_count' => 10,
                'coverage_available_count' => 10,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'PASS',
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'coverage_missing_sample' => [],
                'comparison_note' => 'all artifact hashes matched expected fixture outcome',
            ],
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
        ]);

        $run = (object) [
            'run_id' => 91,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'config_version' => 'v1',
            'publication_version' => 4,
            'coverage_universe_count' => 10,
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
            'invalid_bar_count' => 1,
            'invalid_indicator_count' => 2,
            'warning_count' => 0,
            'hard_reject_count' => 3,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'sealed_at' => '2026-03-20 17:30:00',
        ];
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

        $evidence->shouldReceive('findRunById')->once()->with(91)->andReturn($run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(91, '2026-03-20')->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(91, '2026-03-20', 44)->andReturn([
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
        ]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 44)->andReturn([
            ['eligible' => 1],
            ['eligible' => 1],
            ['eligible' => 1],
            ['eligible' => 1],
            ['eligible' => 1],
            ['eligible' => 1],
            ['eligible' => 1],
            ['eligible' => 0],
            ['eligible' => 0],
            ['eligible' => 0],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3002);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['replay_id'] === 3002
                && $metric['comparison_result'] === 'MATCH'
                && $metric['artifact_changed_scope'] === 'none'
                && $metric['expected_status'] === 'SUCCESS'
                && $metric['expected_terminal_status'] === 'SUCCESS'
                && $metric['expected_publishability_state'] === 'READABLE'
                && $metric['expected_config_identity'] === 'v1'
                && $metric['expected_publication_id'] === 44
                && $metric['expected_publication_run_id'] === 91
                && $metric['expected_publication_version'] === 4
                && $metric['expected_is_current_publication'] === true
                && $metric['expected_coverage_gate_state'] === 'PASS'
                && $metric['expected_coverage_universe_count'] === 10
                && $metric['expected_bars_batch_hash'] === 'A1'
                && $metric['expected_indicators_batch_hash'] === 'B1'
                && $metric['expected_eligibility_batch_hash'] === 'C1'
                && $metric['expected_reason_code_counts_json'] === json_encode([
                    ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
                ], JSON_UNESCAPED_SLASHES);
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3002, '2026-03-20', [
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(91, $fixtureDir);

        $this->assertSame(3002, $result['replay_id']);
        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertSame('PASS', $result['coverage_gate_state']);
        $this->assertSame(10, $result['coverage_universe_count']);
        $this->assertNull($result['mismatch_summary']);
        $this->assertSame('fixture_replay_unchanged_input', $result['fixture_family']);
    }

    public function test_verify_replay_fails_when_run_is_not_readable()
    {
        $fixtureDir = $this->makeFixture([
            'manifest' => [
                'fixture_family' => 'fixture_replay_degraded_input',
                'version' => 'v1',
                'contract_areas' => ['replay'],
                'files' => [
                    'expected/expected_replay_result.json',
                ],
                'assertion_layers' => ['replay'],
            ],
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'EXPECTED_DEGRADE',
            ],
        ]);

        $run = (object) [
            'run_id' => 92,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-19',
            'source' => 'api',
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(92)->andReturn($run);

        $service = new ReplayVerificationService($evidence, $publications, $replays);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Replay verification requires a SUCCESS + READABLE run; non-readable runs cannot be consumed through publication read path.');

        $service->verifyRunAgainstFixture(92, $fixtureDir);
    }

    public function test_verify_replay_marks_mismatch_when_reason_code_counts_diverge()
    {
        $fixtureDir = $this->makeFixture([
            'manifest' => [
                'fixture_family' => 'fixture_replay_reason_code_mismatch',
                'version' => 'v1',
                'contract_areas' => ['replay'],
                'files' => [
                    'expected/expected_replay_result.json',
                    'expected/expected_reason_code_counts.json',
                ],
                'assertion_layers' => ['replay'],
            ],
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'MATCH',
                'expected_status' => 'SUCCESS',
                'expected_trade_date_effective' => '2026-03-20',
                'expected_seal_state' => 'SEALED',
                'coverage_universe_count' => 10,
                'coverage_available_count' => 10,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'PASS',
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'coverage_missing_sample' => [],
            ],
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 2],
            ],
        ]);

        $run = (object) [
            'run_id' => 93,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'config_version' => 'v1',
            'publication_version' => 4,
            'coverage_universe_count' => 10,
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
            'sealed_at' => '2026-03-20 17:30:00',
        ];
        $publication = (object) [
            'publication_id' => 45,
            'publication_version' => 4,
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
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 45)->andReturn([
            ['eligible' => 1],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3004);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['comparison_result'] === 'MISMATCH'
                && $metric['expected_reason_code_counts_json'] === json_encode([
                    ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 2],
                ], JSON_UNESCAPED_SLASHES)
                && strpos((string) $metric['mismatch_summary'], 'reason_code_counts') !== false;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3004, '2026-03-20', [
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(93, $fixtureDir);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertStringContainsString('reason_code_counts', (string) $result['mismatch_summary']);
    }


    public function test_verify_replay_marks_mismatch_when_coverage_contract_fields_diverge()
    {
        $fixtureDir = $this->makeFixture([
            'manifest' => [
                'fixture_family' => 'fixture_replay_coverage_mismatch',
                'version' => 'v1',
                'contract_areas' => ['replay', 'coverage_gate'],
                'files' => [
                    'expected/expected_replay_result.json',
                ],
                'assertion_layers' => ['replay'],
            ],
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'MATCH',
                'expected_status' => 'SUCCESS',
                'expected_trade_date_effective' => '2026-03-20',
                'expected_seal_state' => 'SEALED',
                'coverage_universe_count' => 10,
                'coverage_available_count' => 10,
                'coverage_missing_count' => 0,
                'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800',
                'coverage_gate_state' => 'PASS',
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
                'coverage_contract_version' => 'coverage_gate_v1',
                'coverage_missing_sample' => [],
            ],
        ]);

        $run = (object) [
            'run_id' => 94,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'config_version' => 'v1',
            'publication_version' => 4,
            'coverage_universe_count' => 10,
            'coverage_available_count' => 8,
            'coverage_missing_count' => 2,
            'coverage_ratio' => '0.8000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'FAIL',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode(['BBCA', 'BMRI']),
            'bars_rows_written' => 8,
            'indicators_rows_written' => 8,
            'eligibility_rows_written' => 8,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'A1',
            'indicators_batch_hash' => 'B1',
            'eligibility_batch_hash' => 'C1',
            'sealed_at' => '2026-03-20 17:30:00',
        ];
        $publication = (object) [
            'publication_id' => 46,
            'publication_version' => 4,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(94)->andReturn($run);
        $publications->shouldReceive('findReadableCurrentPublicationForRun')->once()->with(94, '2026-03-20')->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(94, '2026-03-20', 46)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 46)->andReturn([
            ['eligible' => 1],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3005);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['comparison_result'] === 'MISMATCH'
                && $metric['expected_coverage_gate_state'] === 'PASS'
                && (string) $metric['expected_coverage_ratio'] === '1.0000'
                && strpos((string) $metric['mismatch_summary'], 'coverage_gate_state') !== false;
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3005, '2026-03-20', []);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(94, $fixtureDir);

        $this->assertSame('MISMATCH', $result['comparison_result']);
        $this->assertSame('FAIL', $result['coverage_gate_state']);
        $this->assertStringContainsString('coverage_gate_state', (string) $result['mismatch_summary']);
    }

    public function test_verify_replay_throws_when_manifest_declares_missing_file()
    {
        $fixtureDir = $this->makeFixture([
            'manifest' => [
                'fixture_family' => 'fixture_replay_unchanged_input',
                'version' => 'v1',
                'contract_areas' => ['replay'],
                'files' => ['expected/expected_replay_result.json', 'expected/missing.json'],
                'assertion_layers' => ['replay'],
            ],
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'MATCH',
            ],
        ]);

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);
        $service = new ReplayVerificationService($evidence, $publications, $replays);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Replay fixture file missing: expected/missing.json');

        $service->verifyRunAgainstFixture(1, $fixtureDir);
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
