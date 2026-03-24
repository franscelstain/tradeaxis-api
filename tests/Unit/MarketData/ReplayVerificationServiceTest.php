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
                'expected_trade_date_effective' => '2026-03-20',
                'expected_seal_state' => 'SEALED',
                'config_identity' => 'v1',
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
            'config_version' => 'v1',
            'publication_version' => 4,
            'coverage_ratio' => '1.0000',
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
            'publication_version' => 4,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:30:00',
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(91)->andReturn($run);
        $evidence->shouldReceive('findPublicationForRun')->once()->with(91)->andReturn($publication);
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
                && $metric['expected_status'] === 'SUCCESS';
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3002, '2026-03-20', [
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 3],
        ]);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(91, $fixtureDir);

        $this->assertSame(3002, $result['replay_id']);
        $this->assertSame('MATCH', $result['comparison_result']);
        $this->assertNull($result['mismatch_summary']);
        $this->assertSame('fixture_replay_unchanged_input', $result['fixture_family']);
    }

    public function test_verify_replay_marks_expected_degrade_when_fixture_and_actual_hold_match()
    {
        $fixtureDir = $this->makeFixture([
            'manifest' => [
                'fixture_family' => 'fixture_replay_degraded_input',
                'version' => 'v1',
                'contract_areas' => ['replay'],
                'files' => [
                    'expected/expected_replay_result.json',
                    'expected/expected_run_summary.json',
                    'expected/expected_reason_code_counts.json',
                ],
                'assertion_layers' => ['run', 'replay'],
            ],
            'expected/expected_replay_result.json' => [
                'comparison_result' => 'EXPECTED_DEGRADE',
                'expected_status' => 'HELD',
                'expected_trade_date_effective' => '2026-03-19',
                'expected_seal_state' => 'UNSEALED',
                'comparison_note' => 'coverage intentionally degraded',
            ],
            'expected/expected_run_summary.json' => [
                'warning_count' => 5,
                'hard_reject_count' => 2,
                'eligible_count' => 0,
            ],
            'expected/expected_reason_code_counts.json' => [],
        ]);

        $run = (object) [
            'run_id' => 92,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-19',
            'source' => 'api',
            'terminal_status' => 'HELD',
            'config_version' => 'v1',
            'publication_version' => null,
            'coverage_ratio' => '0.7200',
            'bars_rows_written' => 7,
            'indicators_rows_written' => 5,
            'eligibility_rows_written' => 10,
            'invalid_bar_count' => 3,
            'invalid_indicator_count' => 5,
            'warning_count' => 5,
            'hard_reject_count' => 2,
            'bars_batch_hash' => 'X1',
            'indicators_batch_hash' => 'Y1',
            'eligibility_batch_hash' => 'Z1',
            'sealed_at' => null,
        ];

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayResultRepository::class);

        $evidence->shouldReceive('findRunById')->once()->with(92)->andReturn($run);
        $evidence->shouldReceive('findPublicationForRun')->once()->with(92)->andReturn(null);
        $publications->shouldReceive('findPointerResolvedPublicationForTradeDate')->once()->with('2026-03-19')->andReturn(null);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(92, '2026-03-19', null)->andReturn([]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-19', null)->andReturn([
            ['eligible' => 0],
            ['eligible' => 0],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3003);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['comparison_result'] === 'EXPECTED_DEGRADE'
                && $metric['status'] === 'HELD'
                && $metric['expected_seal_state'] === 'UNSEALED';
        }));
        $replays->shouldReceive('replaceReasonCodeCounts')->once()->with(3003, '2026-03-20', []);

        $service = new ReplayVerificationService($evidence, $publications, $replays);
        $result = $service->verifyRunAgainstFixture(92, $fixtureDir);

        $this->assertSame('EXPECTED_DEGRADE', $result['comparison_result']);
        $this->assertSame('none', $result['artifact_changed_scope']);
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
            ],
            'expected/expected_reason_code_counts.json' => [
                ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'reason_count' => 2],
            ],
        ]);

        $run = (object) [
            'run_id' => 93,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'manual_file',
            'terminal_status' => 'SUCCESS',
            'config_version' => 'v1',
            'publication_version' => 4,
            'coverage_ratio' => '1.0000',
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
        $evidence->shouldReceive('findPublicationForRun')->once()->with(93)->andReturn($publication);
        $evidence->shouldReceive('dominantReasonCodes')->once()->with(93, '2026-03-20', 45)->andReturn([
            ['reason_code' => 'ELIG_NOT_ENOUGH_HISTORY', 'count' => 3],
        ]);
        $evidence->shouldReceive('exportEligibilityRows')->once()->with('2026-03-20', 45)->andReturn([
            ['eligible' => 1],
        ]);
        $replays->shouldReceive('nextReplayId')->once()->andReturn(3004);
        $replays->shouldReceive('upsertMetric')->once()->with(m::on(function ($metric) {
            return $metric['comparison_result'] === 'MISMATCH'
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
