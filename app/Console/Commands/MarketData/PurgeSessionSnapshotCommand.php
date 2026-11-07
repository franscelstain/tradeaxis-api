<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\SessionSnapshotService;
class PurgeSessionSnapshotCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:session-snapshot:purge {--before_date=} {--output_dir=} {--dry-run : Preview purge only; do not delete rows.} {--apply : Execute purge deletion after explicit operator confirmation.}';
    protected $description = 'Purge session snapshot rows according to retention policy with dry-run/apply guard.';

    public function handle(SessionSnapshotService $service)
    {
        if (! $this->validateDateString($this->option('before_date'), 'before_date')) {
            return 1;
        }

        $apply = (bool) $this->option('apply');
        if ($apply) {
            $disclosed = $service->inspectPurge($this->option('before_date'));
            $this->line('operation_mode=PRE_APPLY_DISCLOSURE');
            $this->line('scope=session_snapshot_purge:captured_before='.$disclosed['cutoff_timestamp']);
            $this->line('resolved_immutable_ids=candidate_set_sha256='.$disclosed['pre_apply_fingerprint']);
            $this->line('expected_row_effects=delete exactly '.$disclosed['candidate_rows'].' eligible non-authoritative session snapshot rows');
            $this->line('config_hash='.$disclosed['config_hash']);
            $this->line('pointer_impact=UNCHANGED');
            $this->line('reason=retention_purge:'.$disclosed['cutoff_source']);
            try {
                $summary = $service->applyDisclosedPurge($disclosed, $this->option('output_dir'));
            } catch (\Throwable $e) {
                $message = (string) $e->getMessage();
                $reason = strpos($message, 'COMMAND_SESSION_SNAPSHOT_PURGE_WRITE_COUNT_MISMATCH') !== false
                    ? 'COMMAND_SESSION_SNAPSHOT_PURGE_WRITE_COUNT_MISMATCH'
                    : (strpos($message, 'COMMAND_SESSION_SNAPSHOT_PURGE_PREFLIGHT_CHANGED') !== false
                        ? 'COMMAND_SESSION_SNAPSHOT_PURGE_PREFLIGHT_CHANGED'
                        : 'COMMAND_EXECUTION_FAILED');
                $this->renderCommandBlocked($reason, $message, []);
                return $reason === 'COMMAND_EXECUTION_FAILED' ? 1 : 2;
            }
        } else {
            $summary = $service->purge($this->option('before_date'), $this->option('output_dir'), false);
        }

        $this->line('operation_mode='.$summary['operation_mode']);
        $this->line('reason_code='.$summary['reason_code']);
        $this->line('cutoff_timestamp='.$summary['cutoff_timestamp']);
        $this->line('cutoff_source='.$summary['cutoff_source']);
        if (isset($summary['before_date']) && $summary['before_date'] !== null) {
            $this->line('before_date='.$summary['before_date']);
        }
        if (isset($summary['retention_days']) && $summary['retention_days'] !== null) {
            $this->line('retention_days='.$summary['retention_days']);
        }
        $this->line('candidate_rows='.$summary['candidate_rows']);
        $this->line('deleted_rows='.$summary['deleted_rows']);
        $this->line('output_dir='.$this->normalizePathForDisplay($summary['output_dir']));

        if (! $apply) {
            $this->line('next_action=Re-run with --apply after reviewing candidate_rows and cutoff context.');
        }

        return 0;
    }
}
