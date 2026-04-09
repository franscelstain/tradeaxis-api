<?php

namespace App\Application\MarketData\Services;

class ReplaySmokeSuiteService
{
    private $replays;
    private $evidence;

    public function __construct(
        ReplayVerificationService $replays,
        MarketDataEvidenceExportService $evidence
    ) {
        $this->replays = $replays;
        $this->evidence = $evidence;
    }

    public function execute($runId, $fixtureRoot = null, $outputDir = null)
    {
        $fixtureRoot = $fixtureRoot ?: storage_path('app/market_data/replay-fixtures');
        $outputDir = $outputDir ?: storage_path('app/market_data/evidence/replay_smoke_suites/run_'.$runId.'_'.date('Ymd_His'));

        $cases = [
            'valid_case' => 'MATCH',
            'reason_code_mismatch_case' => 'MISMATCH',
            'broken_manifest_case' => 'ERROR',
            'missing_file_case' => 'ERROR',
        ];

        if (! is_dir($fixtureRoot)) {
            throw new \RuntimeException('Replay smoke fixture root not found: '.$fixtureRoot);
        }

        if (! is_dir($outputDir) && ! mkdir($outputDir, 0777, true) && ! is_dir($outputDir)) {
            throw new \RuntimeException('Unable to create replay smoke output directory: '.$outputDir);
        }

        $results = [];
        $allPassed = true;

        foreach ($cases as $caseName => $expectedOutcome) {
            $fixturePath = rtrim($fixtureRoot, '/').'/'.$caseName;
            $caseOutputDir = rtrim($outputDir, '/').'/'.$caseName;
            $record = [
                'fixture_case' => $caseName,
                'fixture_path' => $this->normalizePathForDisplay($fixturePath),
                'expected_outcome' => $expectedOutcome,
                'passed' => false,
            ];

            try {
                $result = $this->replays->verifyRunAgainstFixture($runId, $fixturePath);
                $record['observed_outcome'] = $result['comparison_result'];
                $record['replay_id'] = $result['replay_id'];
                $record['trade_date'] = $result['trade_date'];
                $record['fixture_family'] = $result['fixture_family'];
                $record['comparison_note'] = $result['comparison_note'];
                $record['passed'] = $result['comparison_result'] === $expectedOutcome;

                if ($record['passed']) {
                    $export = $this->evidence->exportReplayEvidence($result['replay_id'], $result['trade_date'], $caseOutputDir);
                    $record['evidence_output_dir'] = $this->normalizePathForDisplay($export['output_dir']);
                    $record['evidence_files'] = $export['files'];
                }
            } catch (\Throwable $e) {
                $record['observed_outcome'] = 'ERROR';
                $record['error'] = $e->getMessage();
                $record['passed'] = $expectedOutcome === 'ERROR';
            }

            $allPassed = $allPassed && $record['passed'];
            $results[] = $record;
        }

        $summary = [
            'run_id' => (int) $runId,
            'fixture_root' => $this->normalizePathForDisplay($fixtureRoot),
            'suite' => 'replay_smoke_minimum',
            'all_passed' => $allPassed,
            'executed_at' => date(DATE_ATOM),
            'cases' => $results,
        ];

        file_put_contents(rtrim($outputDir, '/').'/replay_smoke_suite_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $summary + ['output_dir' => $outputDir];
    }

    private function normalizePathForDisplay($path)
    {
        return str_replace('\\', '/', (string) $path);
    }
}
