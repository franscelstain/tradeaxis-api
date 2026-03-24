<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplaySmokeSuiteService;
use App\Application\MarketData\Services\ReplayVerificationService;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ReplaySmokeSuiteServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_execute_runs_builtin_smoke_cases_and_writes_summary()
    {
        $fixtureRoot = sys_get_temp_dir().'/replay_smoke_root_'.uniqid();
        foreach (['valid_case', 'reason_code_mismatch_case', 'broken_manifest_case', 'missing_file_case'] as $dir) {
            mkdir($fixtureRoot.'/'.$dir, 0777, true);
        }

        $outputDir = sys_get_temp_dir().'/replay_smoke_output_'.uniqid();

        $verify = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);

        $verify->shouldReceive('verifyRunAgainstFixture')->once()->with(28, $fixtureRoot.'/valid_case')->andReturn([
            'replay_id' => 101,
            'trade_date' => '2026-03-20',
            'comparison_result' => 'MATCH',
            'comparison_note' => 'matched',
            'fixture_family' => 'replay_smoke_case',
        ]);
        $evidence->shouldReceive('exportReplayEvidence')->once()->with(101, '2026-03-20', $outputDir.'/valid_case')->andReturn([
            'output_dir' => $outputDir.'/valid_case',
            'files' => ['replay_result.json'],
        ]);

        $verify->shouldReceive('verifyRunAgainstFixture')->once()->with(28, $fixtureRoot.'/reason_code_mismatch_case')->andReturn([
            'replay_id' => 102,
            'trade_date' => '2026-03-20',
            'comparison_result' => 'MISMATCH',
            'comparison_note' => 'mismatch',
            'fixture_family' => 'reason_code_mismatch_case',
        ]);
        $evidence->shouldReceive('exportReplayEvidence')->once()->with(102, '2026-03-20', $outputDir.'/reason_code_mismatch_case')->andReturn([
            'output_dir' => $outputDir.'/reason_code_mismatch_case',
            'files' => ['replay_result.json'],
        ]);

        $verify->shouldReceive('verifyRunAgainstFixture')->once()->with(28, $fixtureRoot.'/broken_manifest_case')->andThrow(new RuntimeException('manifest missing field'));
        $verify->shouldReceive('verifyRunAgainstFixture')->once()->with(28, $fixtureRoot.'/missing_file_case')->andThrow(new RuntimeException('missing file'));

        $service = new ReplaySmokeSuiteService($verify, $evidence);
        $summary = $service->execute(28, $fixtureRoot, $outputDir);

        $this->assertTrue($summary['all_passed']);
        $this->assertSame('replay_smoke_minimum', $summary['suite']);
        $this->assertCount(4, $summary['cases']);
        $this->assertFileExists($outputDir.'/replay_smoke_suite_summary.json');

        $payload = json_decode(file_get_contents($outputDir.'/replay_smoke_suite_summary.json'), true);
        $this->assertTrue($payload['all_passed']);
        $this->assertSame('MATCH', $payload['cases'][0]['observed_outcome']);
        $this->assertSame('ERROR', $payload['cases'][2]['observed_outcome']);
    }
}
