<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\ReplayVerificationService;

class GenerateReplayFixtureCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:replay:fixture:generate {run_id} {--case=valid_case} {--output_dir=}';

    protected $description = 'Generate a deterministic replay fixture package from one executed run for runtime MATCH proof.';

    public function handle()
    {
        $runId = (int) $this->argument('run_id');
        if ($runId <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'run_id must be a positive integer.', [
                'run_id' => $this->argument('run_id'),
            ]);
            return 1;
        }

        $caseName = (string) ($this->option('case') ?: 'valid_case');
        if (! preg_match('/^[A-Za-z0-9_\-]+$/', $caseName)) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'case must contain only letters, numbers, dash, or underscore.', [
                'case' => $caseName,
            ]);
            return 1;
        }

        $outputDir = $this->option('output_dir') ?: storage_path('app/market_data/replay-fixtures/generated-run-'.$runId.'/'.$caseName);

        try {
            $result = app(ReplayVerificationService::class)->generateFixtureFromRun(
                $runId,
                $outputDir,
                $caseName
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage(), [
                'run_id' => $runId,
                'case' => $caseName,
                'output_dir' => $this->normalizePathForDisplay((string) $outputDir),
            ]);

            return 1;
        }

        $this->info('fixture_generated=1');
        $this->line('run_id='.$result['run_id']);
        $this->line('fixture_id='.$result['fixture_id']);
        $this->line('fixture_family='.$result['fixture_family']);
        $this->line('expected_result='.$result['expected_result']);
        $this->line('trade_date='.$result['trade_date']);
        $this->line('trade_date_effective='.(string) $result['trade_date_effective']);
        $this->line('source_mode='.(string) $result['source_mode']);
        $this->line('coverage_gate_state='.(string) $result['coverage_gate_state']);
        $this->line('coverage_ratio='.(string) $result['coverage_ratio']);
        $this->line('publication_id='.(string) $result['publication_id']);
        $this->line('publication_run_id='.(string) $result['publication_run_id']);
        $this->line('pointer_publication_id='.(string) $result['pointer_publication_id']);
        $this->line('pointer_run_id='.(string) $result['pointer_run_id']);
        $this->line('bars_batch_hash='.(string) $result['bars_batch_hash']);
        $this->line('indicators_batch_hash='.(string) $result['indicators_batch_hash']);
        $this->line('eligibility_batch_hash='.(string) $result['eligibility_batch_hash']);
        $this->line('fixture_path='.$this->normalizePathForDisplay($result['fixture_path']));
        $this->line('manifest_path='.$this->normalizePathForDisplay($result['manifest_path']));
        $this->line('expected_replay_result_path='.$this->normalizePathForDisplay($result['expected_replay_result_path']));
        $this->line('expected_reason_code_counts_path='.$this->normalizePathForDisplay($result['expected_reason_code_counts_path']));
        $this->line('next_command=php artisan market-data:replay:verify '.$runId.' '.$this->normalizePathForDisplay($result['fixture_path']).' --output_dir=storage/app/market-data/replay');

        return 0;
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}
