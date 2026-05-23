<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
class ApproveCorrectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:correction:approve {correction_id?} {--approved_by=system}';

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

        try {
            $correction = app(EodCorrectionRepository::class)->approve(
                $correctionId,
                $this->option('approved_by') ?: 'system'
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage(), [
                'correction_id' => $correctionId,
            ]);

            return 1;
        }

        $this->info('correction_id='.$correction->correction_id);
        $this->line('trade_date='.$correction->trade_date);
        $this->line('status='.$correction->status);

        return 0;
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        $message = (string) $e->getMessage();

        if (stripos($message, 'not found') !== false || stripos($message, 'No query results') !== false) {
            return 'COMMAND_CORRECTION_NOT_FOUND';
        }

        if (stripos($message, 'not approvable') !== false || stripos($message, 'COMMAND_CORRECTION_STATUS_NOT_APPROVABLE') !== false) {
            return 'COMMAND_CORRECTION_STATUS_NOT_APPROVABLE';
        }

        if (stripos($message, 'already consumed') !== false || stripos($message, 'cannot be approved') !== false) {
            return 'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE';
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}
