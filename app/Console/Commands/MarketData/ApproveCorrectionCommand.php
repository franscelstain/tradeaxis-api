<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
class ApproveCorrectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:correction:approve {correction_id} {--approved_by=system}';

    protected $description = 'Approve a historical correction request before execution.';

    public function handle()
    {
        $correctionId = (int) $this->argument('correction_id');
        if ($correctionId <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'correction_id must be a positive integer.', [
                'correction_id' => $this->argument('correction_id'),
            ]);
            return 1;
        }

        $correction = app(EodCorrectionRepository::class)->approve(
            $correctionId,
            $this->option('approved_by') ?: 'system'
        );

        $this->info('correction_id='.$correction->correction_id);
        $this->line('trade_date='.$correction->trade_date);
        $this->line('status='.$correction->status);

        return 0;
    }
}
