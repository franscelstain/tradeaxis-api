<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;

class VerifyReplayCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:replay:verify {run_id} {fixture_path} {--replay_id=} {--output_dir=}';

    protected $description = 'Verify one executed market-data run against a replay fixture package and persist replay proof rows.';

    public function handle()
    {
        $runId = (int) $this->argument('run_id');
        if ($runId <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'run_id must be a positive integer.', [
                'replay_status' => 'BLOCKED',
                'run_id' => $this->argument('run_id'),
            ]);
            return 1;
        }

        try {
            $result = app(ReplayVerificationService::class)->verifyRunAgainstFixture(
                $runId,
                $this->argument('fixture_path'),
                $this->option('replay_id') ? (int) $this->option('replay_id') : null
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage(), [
                'replay_status' => 'BLOCKED',
                'run_id' => $runId,
                'fixture_path' => $this->normalizePathForDisplay((string) $this->argument('fixture_path')),
            ]);

            return 1;
        }

        $this->info('replay_id='.$result['replay_id']);
        $this->line('replay_suite='.(string) ($result['replay_suite'] ?? $result['fixture_family'] ?? ''));
        $this->line('replay_case='.(string) ($result['replay_case'] ?? $result['fixture_id'] ?? ''));
        $this->line('fixture_version='.(string) ($result['fixture_version'] ?? ''));
        $this->line('fixture_schema_version='.(string) ($result['fixture_schema_version'] ?? ''));
        $this->line('trade_date='.$result['trade_date']);
        $this->line('trade_date_effective='.(string) ($result['trade_date_effective'] ?? ''));
        $this->line('expected_final_state='.(string) ($result['expected_terminal_status'] ?? $result['expected_status'] ?? '').'|'.(string) ($result['expected_publishability_state'] ?? '').'|'.(string) (($result['expected_context']['expected_final_state']['final_reason_code'] ?? null) ?: $result['expected_coverage_reason_code'] ?? ''));
        $this->line('actual_final_state='.(string) ($result['status'] ?? '').'|'.(string) ($result['publishability_state'] ?? '').'|'.(string) ($result['actual_context']['actual_final_state']['final_reason_code'] ?? $result['final_reason_code'] ?? ''));
        $this->line('final_reason_code='.(string) ($result['final_reason_code'] ?? ''));
        $this->line('comparison_result='.$result['comparison_result']);
        $this->line('replay_status='.$result['replay_status']);
        $this->line('comparison_note='.(string) $result['comparison_note']);
        $this->line('mismatch_count='.(string) ($result['mismatch_count'] ?? count($result['mismatches'] ?? [])));
        $this->line('mismatch_reason_codes='.implode(',', $result['mismatch_reason_codes'] ?? []));
        $this->line('source_summary=expected:'.(string) ($result['expected_source_mode'] ?? '').'/'.(string) ($result['expected_source_provider'] ?? '').' actual:'.(string) ($result['source_mode'] ?? '').'/'.(string) ($result['source_provider'] ?? ''));
        $this->line('coverage_summary=expected:'.(string) ($result['expected_coverage_gate_state'] ?? '').'/'.(string) ($result['expected_coverage_ratio'] ?? '').' actual:'.(string) ($result['coverage_gate_state'] ?? '').'/'.(string) ($result['coverage_ratio'] ?? ''));
        $this->line('publication_summary=expected:'.(string) ($result['expected_publication_id'] ?? '').'/v'.(string) ($result['expected_publication_version'] ?? '').' actual:'.(string) ($result['publication_id'] ?? '').'/v'.(string) ($result['publication_version'] ?? ''));
        $this->line('pointer_summary=expected:'.(string) ($result['expected_publication_id'] ?? '').' actual:'.(string) ($result['publication_id'] ?? ''));
        $this->line('fallback_summary=expected:'.(string) (($result['expected_context']['expected_fallback_context']['fallback_used'] ?? null) ? 'used' : 'not_used').' actual:'.(string) (($result['actual_context']['actual_fallback_context']['fallback_used'] ?? null) ? 'used' : 'not_used'));
        $this->line('correction_summary=expected:'.(string) ($result['expected_correction_id'] ?? '').' actual:'.(string) ($result['correction_id'] ?? ''));
        $this->line('artifact_changed_scope='.(string) $result['artifact_changed_scope']);
        $this->line('fixture_family='.(string) $result['fixture_family']);
        $this->line('fixture_path='.$this->normalizePathForDisplay((string) $this->argument('fixture_path')));

        $outputDir = $this->option('output_dir');
        if ($outputDir !== null && $outputDir !== '') {
            app(MarketDataEvidenceExportService::class)->exportReplayEvidence(
                $result['replay_id'],
                $result['trade_date'],
                $outputDir
            );
            $this->line('evidence_output_dir='.$this->normalizePathForDisplay((string) $outputDir));
            $this->line('replay_artifact_path='.$this->normalizePathForDisplay(rtrim((string) $outputDir, '/\\').'/replay_result.json'));
        }

        return in_array($result['replay_status'], ['FAIL', 'BLOCKED'], true) ? 1 : 0;
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}
