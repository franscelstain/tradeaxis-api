<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\CommandPreApplyDisclosureService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;

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

        $guard = app(CommandPreApplyDisclosureService::class);
        $disclosed = [
            'trade_date' => (string) $tradeDate,
            'baseline_publication_id' => (int) $baseline->publication_id,
            'baseline_run_id' => (int) $baseline->run_id,
            'config_hash' => $guard->currentConfigHash(),
            'reason_code' => (string) $reasonCode,
            'reason_note' => (string) ($this->option('reason_note') ?: ''),
            'requested_by' => (string) ($this->option('requested_by') ?: 'system'),
        ];
        $this->line('operation_mode=PRE_APPLY_DISCLOSURE');
        $this->line('scope=correction_request:trade_date='.$tradeDate);
        $this->line('resolved_immutable_ids=baseline_publication_id='.(int) $baseline->publication_id.';baseline_run_id='.(int) $baseline->run_id);
        $this->line('expected_row_effects=create one immutable correction request; no publication or pointer mutation');
        $this->line('config_hash='.$disclosed['config_hash']);
        $this->line('pointer_impact=UNCHANGED');
        $this->line('reason='.$reasonCode.':'.($this->option('reason_note') ?: 'UNSPECIFIED'));

        try {
            $correction = DB::transaction(function () use ($tradeDate, $reasonCode, $baseline, $guard, $disclosed) {
                $locked = app(EodPublicationRepository::class)->findCorrectionBaselinePublicationForTradeDate($tradeDate, true);
                $actual = $disclosed;
                $actual['baseline_publication_id'] = $locked ? (int) $locked->publication_id : 0;
                $actual['baseline_run_id'] = $locked ? (int) $locked->run_id : 0;
                $actual['config_hash'] = $guard->currentConfigHash();
                $guard->assertSame($disclosed, $actual, 'COMMAND_CORRECTION_REQUEST_PREFLIGHT_CHANGED');
                return app(EodCorrectionRepository::class)->createRequest(
                    $tradeDate,
                    $reasonCode,
                    $this->option('reason_note') ?: null,
                    $this->option('requested_by') ?: 'system',
                    $baseline->publication_id,
                    $baseline->run_id
                );
            });
        } catch (\Throwable $e) {
            $preflightChanged = strpos((string) $e->getMessage(), 'COMMAND_CORRECTION_REQUEST_PREFLIGHT_CHANGED') !== false;
            $this->renderCommandBlocked(
                $preflightChanged ? 'COMMAND_CORRECTION_REQUEST_PREFLIGHT_CHANGED' : 'COMMAND_EXECUTION_FAILED',
                $e->getMessage(),
                ['trade_date' => $tradeDate]
            );
            return $preflightChanged ? 2 : 1;
        }

        $this->info('correction_id='.$correction->correction_id);
        $this->line('trade_date='.$correction->trade_date);
        $this->line('status='.$correction->status);
        $this->line('request_reason_code='.$reasonCode);
        $this->line('baseline_publication_id='.$baseline->publication_id);
        $this->line('baseline_run_id='.$baseline->run_id);

        return 0;
    }
}
