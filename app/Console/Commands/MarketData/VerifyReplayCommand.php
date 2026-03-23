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
        $result = app(ReplayVerificationService::class)->verifyRunAgainstFixture(
            (int) $this->argument('run_id'),
            $this->argument('fixture_path'),
            $this->option('replay_id') ? (int) $this->option('replay_id') : null
        );

        $this->info('replay_id='.$result['replay_id']);
        $this->line('trade_date='.$result['trade_date']);
        $this->line('comparison_result='.$result['comparison_result']);
        $this->line('comparison_note='.(string) $result['comparison_note']);
        $this->line('artifact_changed_scope='.(string) $result['artifact_changed_scope']);
        $this->line('fixture_family='.(string) $result['fixture_family']);

        $outputDir = $this->option('output_dir');
        if ($outputDir !== '') {
            app(MarketDataEvidenceExportService::class)->exportReplayEvidence(
                $result['replay_id'],
                $result['trade_date'],
                $outputDir ?: null
            );
            $this->line('evidence_output_dir='.(string) ($outputDir ?: 'default'));
        }

        return $result['comparison_result'] === 'UNEXPECTED' || $result['comparison_result'] === 'MISMATCH' ? 1 : 0;
    }
}
