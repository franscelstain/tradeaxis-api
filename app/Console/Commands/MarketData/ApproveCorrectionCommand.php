<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\CommandPreApplyDisclosureService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use Illuminate\Support\Facades\DB;
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
            $repository = app(EodCorrectionRepository::class);
            $pending = $repository->findById($correctionId);
            if (! $pending) {
                throw new \RuntimeException('Correction request not found: '.$correctionId);
            }
            $guard = app(CommandPreApplyDisclosureService::class);
            $disclosed = [
                'correction_id' => (int) $pending->correction_id,
                'trade_date' => (string) $pending->trade_date,
                'status' => (string) $pending->status,
                'baseline_publication_id' => (int) $pending->baseline_publication_id,
                'baseline_run_id' => (int) $pending->prior_run_id,
                'config_hash' => $guard->currentConfigHash(),
                'approved_by' => (string) ($this->option('approved_by') ?: 'system'),
            ];
            $this->line('operation_mode=PRE_APPLY_DISCLOSURE');
            $this->line('scope=correction_approval:correction_id='.$correctionId.';trade_date='.$disclosed['trade_date']);
            $this->line('resolved_immutable_ids=correction_id='.$correctionId.';baseline_publication_id='.$disclosed['baseline_publication_id'].';baseline_run_id='.$disclosed['baseline_run_id']);
            $this->line('expected_row_effects=transition one correction request from REQUESTED to APPROVED; no publication or pointer mutation');
            $this->line('config_hash='.$disclosed['config_hash']);
            $this->line('pointer_impact=UNCHANGED');
            $this->line('reason=operator_approval_by:'.$disclosed['approved_by']);
            $correction = DB::transaction(function () use ($repository, $correctionId, $guard, $disclosed) {
                $locked = $repository->findByIdForUpdate($correctionId);
                $actual = $disclosed;
                if (! $locked) {
                    $actual['correction_id'] = 0;
                } else {
                    $actual['correction_id'] = (int) $locked->correction_id;
                    $actual['trade_date'] = (string) $locked->trade_date;
                    $actual['status'] = (string) $locked->status;
                    $actual['baseline_publication_id'] = (int) $locked->baseline_publication_id;
                    $actual['baseline_run_id'] = (int) $locked->prior_run_id;
                }
                $actual['config_hash'] = $guard->currentConfigHash();
                $guard->assertSame($disclosed, $actual, 'COMMAND_CORRECTION_APPROVAL_PREFLIGHT_CHANGED');
                return $repository->approve($correctionId, $disclosed['approved_by']);
            });
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

        if (stripos($message, 'COMMAND_CORRECTION_APPROVAL_PREFLIGHT_CHANGED') !== false) {
            return 'COMMAND_CORRECTION_APPROVAL_PREFLIGHT_CHANGED';
        }

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
