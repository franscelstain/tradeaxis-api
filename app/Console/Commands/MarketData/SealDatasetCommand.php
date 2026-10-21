<?php

namespace App\Console\Commands\MarketData;

class SealDatasetCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:dataset:seal {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Seal a coherent consumer-readable dataset for the requested date.';

    public function handle()
    {
        if (! $this->validateDateString($this->option('requested_date'), 'requested_date') || ! $this->validateSourceModeString($this->option('source_mode'))) {
            return 1;
        }

        try {
            $run = $this->pipeline()->completeSeal($this->makeStageInput('SEAL'));
        } catch (\RuntimeException $error) {
            $message = $error->getMessage();
            $incompleteSnapshot = strpos($message, 'DATASET_MANIFEST_INVALID: publication snapshot incomplete') === 0;
            $missingHashes = strpos($message, 'RUN_SEAL_PRECONDITION_FAILED: cannot seal dataset before all mandatory hashes') === 0
                || strpos($message, 'DATASET_HASH_MISSING:') === 0;
            $ownerConflict = strpos($message, 'RUN_OWNER_CONFLICT:') === 0;
            if (! $incompleteSnapshot && ! $missingHashes && ! $ownerConflict) {
                throw $error;
            }

            $this->renderCommandBlocked(
                $incompleteSnapshot ? 'DATASET_MANIFEST_INVALID' : ($ownerConflict ? 'RUN_OWNER_CONFLICT' :
                    (strpos($message, 'RUN_SEAL_PRECONDITION_FAILED:') === 0 ? 'RUN_SEAL_PRECONDITION_FAILED' : 'DATASET_HASH_MISSING')),
                $message,
                [
                    'requested_date' => $this->option('requested_date'),
                    'run_id' => $this->option('run_id'),
                    'next_action' => $ownerConflict
                        ? 'Verify the date owner and its evidence before governed recovery.'
                        : 'Repair upstream artifact completeness and revalidate before seal.',
                ]
            );
            return 1;
        }
        $this->renderRunSummary($run);

        return 0;
    }
}
