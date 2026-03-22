<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use Illuminate\Console\Command;

class ApproveCorrectionCommand extends Command
{
    protected $signature = 'market-data:correction:approve {correction_id} {--approved_by=system}';

    protected $description = 'Approve a historical correction request before execution.';

    public function handle()
    {
        $correction = app(EodCorrectionRepository::class)->approve(
            (int) $this->argument('correction_id'),
            $this->option('approved_by') ?: 'system'
        );

        $this->info('correction_id='.$correction->correction_id);
        $this->line('trade_date='.$correction->trade_date);
        $this->line('status='.$correction->status);

        return 0;
    }
}
