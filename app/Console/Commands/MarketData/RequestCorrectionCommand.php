<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use Illuminate\Console\Command;

class RequestCorrectionCommand extends Command
{
    protected $signature = 'market-data:correction:request {--trade_date=} {--reason_code=} {--reason_note=} {--requested_by=system}';

    protected $description = 'Register a historical correction request for one trade date.';

    public function handle()
    {
        $tradeDate = $this->option('trade_date');
        $reasonCode = $this->option('reason_code');

        if (! $tradeDate || ! $reasonCode) {
            $this->error('trade_date and reason_code are required.');
            return 1;
        }

        $correction = app(EodCorrectionRepository::class)->createRequest(
            $tradeDate,
            $reasonCode,
            $this->option('reason_note') ?: null,
            $this->option('requested_by') ?: 'system'
        );

        $this->info('correction_id='.$correction->correction_id);
        $this->line('trade_date='.$correction->trade_date);
        $this->line('status='.$correction->status);

        return 0;
    }
}
