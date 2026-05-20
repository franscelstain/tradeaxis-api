<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;

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

        $baseline = app(EodPublicationRepository::class)->findCorrectionBaselinePublicationForTradeDate($tradeDate);

        if (! $baseline) {
            $this->renderCommandBlocked('CORRECTION_BASELINE_LINK_MISSING', 'Correction request requires a current sealed readable coverage-PASS baseline publication for the target trade_date.', [
                'trade_date' => $tradeDate,
                'request_reason_code' => $reasonCode,
            ]);
            return 1;
        }

        $correction = app(EodCorrectionRepository::class)->createRequest(
            $tradeDate,
            $reasonCode,
            $this->option('reason_note') ?: null,
            $this->option('requested_by') ?: 'system',
            $baseline->publication_id,
            $baseline->run_id
        );

        $this->info('correction_id='.$correction->correction_id);
        $this->line('trade_date='.$correction->trade_date);
        $this->line('status='.$correction->status);
        $this->line('request_reason_code='.$reasonCode);
        $this->line('baseline_publication_id='.$baseline->publication_id);
        $this->line('baseline_run_id='.$baseline->run_id);

        return 0;
    }
}
