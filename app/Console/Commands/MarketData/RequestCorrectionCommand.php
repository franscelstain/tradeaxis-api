<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
class RequestCorrectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:correction:request {--trade_date=} {--reason_code=} {--reason_note=} {--requested_by=system}';

    protected $description = 'Register a historical correction request for one trade date.';

    public function handle()
    {
        $tradeDate = $this->option('trade_date');
        $reasonCode = $this->option('reason_code');

        if (! $tradeDate || ! $reasonCode) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'trade_date and reason_code are required.', [
                'trade_date' => $tradeDate,
                'request_reason_code' => $reasonCode,
            ]);
            return 1;
        }

        if (! $this->validateDateString($tradeDate, 'trade_date')) {
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
        $this->line('request_reason_code='.$reasonCode);

        return 0;
    }
}
