<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataOperationalStatusService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;

class ScheduledWorkflowCommand extends AbstractMarketDataCommand
{
    const EXIT_READABLE = 0;
    const EXIT_INVALID_INPUT = 1;
    const EXIT_HELD = 2;
    const EXIT_RETRYABLE_FAILURE = 3;
    const EXIT_INTEGRITY_FAILURE = 4;
    const EXIT_LOCK_CONFLICT = 5;
    const EXIT_BLOCKED_PROOF = 6;

    protected $signature = 'market-data:scheduled {--source_mode=}';
    protected $description = 'Run the governed latest completed session through import, promotion and verified operational status.';

    public function handle()
    {
        $date = null;
        $run = null;
        $result = ['scheduler_outcome' => 'FAILED', 'requested_date' => null, 'run_id' => null, 'publication_id' => null,
            'latest_expected_trade_date' => null, 'latest_acquired_trade_date' => null,
            'latest_canonicalized_trade_date' => null, 'latest_readable_trade_date' => null,
            'reason_codes' => [], 'observability_defects' => [], 'activation_state' => null,
            'next_action' => 'Inspect retained run evidence.'];
        if (! $this->validateSourceModeString($this->option('source_mode'))) return self::EXIT_INVALID_INPUT;
        try {
            $target = app(MarketCalendarRepository::class)->latestExpectedSession();
            $date = $target['latest_expected_trade_date'];
            if ($date === null) throw new \RuntimeException('MARKET_CALENDAR_EXPECTED_DATE_UNAVAILABLE');
            $result['requested_date'] = $date;
            $result['latest_expected_trade_date'] = $date;
            $result['current_session_state'] = $target['current_session_state'];
            $current = app(EodPublicationRepository::class)->resolveCurrentReadablePublicationForTradeDate($date);
            $status = $current ? app(MarketDataOperationalStatusService::class)->forDate($date, (int) $current->run_id) : null;
            if ($status && $status['operationally_healthy']) {
                $run = $this->runRepository()->findByRunId($current->run_id);
                $result['scheduler_outcome'] = 'SKIPPED_ALREADY_READABLE';
            } else {
                $import = $this->pipeline()->importDaily($date, $this->sourceMode());
                $run = $this->pipeline()->promoteDaily($date, $this->sourceMode(), $import->run_id);
                $status = app(MarketDataOperationalStatusService::class)->forDate($date, (int) $run->run_id);
                $result['scheduler_outcome'] = $status['operationally_healthy'] ? 'SUCCESS_READABLE'
                    : ((string) $run->terminal_status === 'HELD' ? 'SUCCESS_HELD' : 'FAILED');
            }
            $result = array_merge($result, [
                'run_id' => (int) $run->run_id, 'publication_id' => $status['publication_id'],
                'latest_acquired_trade_date' => $status['latest_acquired_trade_date'],
                'latest_canonicalized_trade_date' => $status['latest_canonicalized_trade_date'],
                'latest_readable_trade_date' => $status['latest_readable_trade_date'],
                'reason_codes' => $status['reason_codes'], 'observability_defects' => $status['observability_defects'],
                'activation_state' => $status['activation_state'],
                'next_action' => $status['operationally_healthy'] ? 'No publication action required.' : 'Inspect requested-date run and observability defects.',
            ]);
        } catch (\Throwable $e) {
            $run = $date ? $this->runRepository()->findLatestForRequestedDate($date, $this->sourceMode()) : null;
            $locked = strpos($e->getMessage(), 'RUN_OWNER_') !== false;
            $result['scheduler_outcome'] = $locked ? 'SKIPPED_LOCKED' : 'FAILED';
            $result['run_id'] = $run ? (int) $run->run_id : null;
            $result['reason_codes'] = [$locked ? 'RUN_LOCK_CONFLICT' : ($run->final_reason_code ?? 'COMMAND_EXECUTION_FAILED')];
            $result['error'] = $e->getMessage();
        }
        if ($run) $this->runRepository()->recordObservation($run, 'scheduler', 'FINALIZE', 'SCHEDULER_OUTCOME',
            in_array($result['scheduler_outcome'], ['SUCCESS_READABLE', 'SKIPPED_ALREADY_READABLE'], true) ? 'INFO' : 'WARN',
            'Scheduled workflow outcome recorded from run and verified publication facts.', null, $result);
        $result['process_exit_code'] = $this->processExitCode($result);
        $this->line(json_encode($result, JSON_UNESCAPED_SLASHES));
        return $result['process_exit_code'];
    }

    /**
     * Keep the operating-system result distinct from the persisted run taxonomy.
     * The numeric values are stable command-interface categories; machine output
     * continues to carry the full scheduler outcome and reason-code set.
     */
    private function processExitCode(array $result): int
    {
        $outcome = (string) ($result['scheduler_outcome'] ?? 'FAILED');
        if (in_array($outcome, ['SUCCESS_READABLE', 'SKIPPED_ALREADY_READABLE'], true)) {
            return self::EXIT_READABLE;
        }
        if ($outcome === 'SKIPPED_LOCKED' || in_array('RUN_LOCK_CONFLICT', $result['reason_codes'] ?? [], true)) {
            return self::EXIT_LOCK_CONFLICT;
        }

        $reasonCodes = array_map('strtoupper', $result['reason_codes'] ?? []);
        if (array_intersect($reasonCodes, ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT']) !== []) {
            return self::EXIT_RETRYABLE_FAILURE;
        }
        foreach ($reasonCodes as $reasonCode) {
            if (preg_match('/(?:INTEGRITY|_HASH_MISMATCH|_MANIFEST_INVALID|_PROOF_INCOMPLETE)/', $reasonCode)) {
                return self::EXIT_INTEGRITY_FAILURE;
            }
        }
        if ($outcome === 'SUCCESS_HELD') {
            return self::EXIT_HELD;
        }

        return self::EXIT_BLOCKED_PROOF;
    }
}
