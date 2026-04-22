<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Console\Command;

class RepairCurrentPublicationIntegrityCommand extends Command
{
    protected $signature = 'market-data:current-publication:repair {--trade_date=} {--apply}';

    protected $description = 'Detect and optionally clear invalid current publication pointer/current mirror states.';

    public function handle()
    {
        /** @var EodPublicationRepository $repo */
        $repo = app(EodPublicationRepository::class);
        $tradeDate = $this->option('trade_date') ?: null;
        $apply = (bool) $this->option('apply');

        $invalidRows = $repo->findInvalidCurrentPublicationStates($tradeDate);

        if ($invalidRows->isEmpty()) {
            $this->info('status=OK');
            $this->line('message=No invalid current publication pointer state detected.');
            if ($tradeDate !== null) {
                $this->line('trade_date='.$tradeDate);
            }

            return 0;
        }

        foreach ($invalidRows as $row) {
            $reasons = [];

            if ((int) ($row->is_current ?? 0) !== 1) {
                $reasons[] = 'PUBLICATION_NOT_MARKED_CURRENT';
            }
            if ((string) ($row->seal_state ?? '') !== 'SEALED') {
                $reasons[] = 'PUBLICATION_NOT_SEALED';
            }
            if (empty($row->pointer_sealed_at)) {
                $reasons[] = 'POINTER_SEALED_AT_MISSING';
            }
            if (empty($row->sealed_at)) {
                $reasons[] = 'PUBLICATION_SEALED_AT_MISSING';
            }
            if (empty($row->run_id)) {
                $reasons[] = 'RUN_ROW_MISSING';
            } else {
                if (empty($row->run_sealed_at)) {
                    $reasons[] = 'RUN_SEALED_AT_MISSING';
                }
                if ((string) ($row->terminal_status ?? '') !== 'SUCCESS') {
                    $reasons[] = 'RUN_TERMINAL_STATUS_NOT_SUCCESS';
                }
                if ((string) ($row->publishability_state ?? '') !== 'READABLE') {
                    $reasons[] = 'RUN_PUBLISHABILITY_NOT_READABLE';
                }
                if ((int) ($row->is_current_publication ?? 0) !== 1) {
                    $reasons[] = 'RUN_CURRENT_MIRROR_NOT_SET';
                }
            }
            if ((string) ($row->run_id ?? '') !== (string) ($row->pointer_run_id ?? '')) {
                $reasons[] = 'POINTER_RUN_ID_MISMATCH';
            }
            if ((string) ($row->publication_version ?? '') !== (string) ($row->pointer_publication_version ?? '')) {
                $reasons[] = 'POINTER_PUBLICATION_VERSION_MISMATCH';
            }
            if ((string) ($row->trade_date ?? $row->pointer_trade_date ?? '') !== (string) ($row->pointer_trade_date ?? '')) {
                $reasons[] = 'PUBLICATION_TRADE_DATE_MISMATCH';
            }

            $reasons = array_values(array_unique($reasons));

            $this->warn('status=INVALID_CURRENT_PUBLICATION');
            $this->line('trade_date='.$row->pointer_trade_date);
            $this->line('publication_id='.$row->publication_id);
            $this->line('run_id='.(string) ($row->run_id ?? ''));
            $this->line('terminal_status='.(string) ($row->terminal_status ?? ''));
            $this->line('publishability_state='.(string) ($row->publishability_state ?? ''));
            $this->line('is_current='.(string) ($row->is_current ?? ''));
            $this->line('is_current_publication='.(string) ($row->is_current_publication ?? ''));
            $this->line('integrity_reasons='.implode(',', $reasons));

            if ($apply) {
                $repo->clearCurrentPublicationState($row->pointer_trade_date);
                $this->info('repair_action=CLEARED_INVALID_CURRENT_STATE');
            }
        }

        return $apply ? 0 : 1;
    }
}
