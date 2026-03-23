<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;

class RunCorrectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:correction:run {correction_id} {--requested_date=} {--source_mode=} {--latest}';

    protected $description = 'Execute the market-data daily pipeline for an approved correction request.';

    public function handle()
    {
        $correctionId = (int) $this->argument('correction_id');
        $correction = app(EodCorrectionRepository::class)->findById($correctionId);

        if (! $correction) {
            $this->error('Correction request not found: '.$correctionId);
            return 1;
        }

        if (! in_array($correction->status, ['APPROVED', 'EXECUTING', 'RESEALED'], true)) {
            $this->error('Correction request must be APPROVED/EXECUTING/RESEALED before execution. Current status='.$correction->status);
            return 1;
        }

        $requestedDate = $this->option('requested_date') ?: (string) $correction->trade_date;
        $run = $this->pipeline()->runDaily($requestedDate, $this->sourceMode(), $correctionId);

        $this->renderRunSummary($run);
        $this->line('correction_id='.$correctionId);
        $this->line('correction_status='.(string) optional(app(EodCorrectionRepository::class)->findById($correctionId))->status);

        return 0;
    }
}
