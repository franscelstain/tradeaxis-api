<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService;
use Illuminate\Console\Command;

class RunBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution
        {--c165-boundary-artifact=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review.json}
        {--expected-c165-boundary-hash=11eca01c5c5cc071c9d61dcf04b2004923f4772f}
        {--expected-c165-boundary-file-sha1=4391205D3732CC475FB37E518678EAB607F5CAB0}
        {--activated-catalog-artifact=storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json}
        {--expected-activated-catalog-hash=54145854758e22115e4b65a297e4c157d94c638d}
        {--expected-activated-catalog-file-sha1=209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7}
        {--controlled-completion-artifact=storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json}
        {--expected-controlled-completion-hash=e9862d9e7738d0558f107d978f329f97f14b3520}
        {--expected-controlled-completion-file-sha1=AB9FC9F714339B78D68132222AC8C398BE7EE1B3}
        {--runtime-activation-state=storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json}
        {--expected-runtime-activation-state-hash=00cb935a8252efe340d5f6ec6ea6966d9645cff7}
        {--expected-runtime-activation-state-file-sha1=17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution.json}
        {--rollout-state-output=storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json}
        {--operator-approved}
        {--controlled-rollout-execution-confirmed}
        {--c165-boundary-locked-confirmed}
        {--activated-catalog-read-confirmed}
        {--plan-confirm-controlled-mutation-confirmed}
        {--controlled-rollout-only-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Execute the C165 PLAN/CONFIRM controlled rollout from locked boundary, catalog, completion, and runtime state artifacts.';

    private WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService $service;

    public function __construct(?WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C165 PLAN/CONFIRM controlled rollout execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c165-boundary-artifact'),
            (string) $this->option('expected-c165-boundary-hash'),
            (string) $this->option('expected-c165-boundary-file-sha1'),
            (string) $this->option('activated-catalog-artifact'),
            (string) $this->option('expected-activated-catalog-hash'),
            (string) $this->option('expected-activated-catalog-file-sha1'),
            (string) $this->option('controlled-completion-artifact'),
            (string) $this->option('expected-controlled-completion-hash'),
            (string) $this->option('expected-controlled-completion-file-sha1'),
            (string) $this->option('runtime-activation-state'),
            (string) $this->option('expected-runtime-activation-state-hash'),
            (string) $this->option('expected-runtime-activation-state-file-sha1'),
            (string) $this->option('output'),
            (string) $this->option('rollout-state-output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'controlled_rollout_execution_confirmed' => (bool) $this->option('controlled-rollout-execution-confirmed'),
                'c165_boundary_locked_confirmed' => (bool) $this->option('c165-boundary-locked-confirmed'),
                'activated_catalog_read_confirmed' => (bool) $this->option('activated-catalog-read-confirmed'),
                'plan_confirm_controlled_mutation_confirmed' => (bool) $this->option('plan-confirm-controlled-mutation-confirmed'),
                'controlled_rollout_only_confirmed' => (bool) $this->option('controlled-rollout-only-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_pass',
            'production_live_runtime_plan_confirm_controlled_rollout_execution_pass',
            'controlled_rollout_execution_confirmed', 'controlled_rollout_executed', 'controlled_rollout_active',
            'controlled_rollout_only', 'unrestricted_rollout_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'production_config_mutated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_invoked',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed',
            'watchlist_function_comparator_candidate_observed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'kill_switch_confirmed', 'rollback_confirmed', 'free_publication_locked_confirmed',
            'c165_boundary_lock_valid', 'activated_catalog_lock_valid', 'controlled_completion_lock_valid',
            'runtime_activation_state_lock_valid', 'rollout_state_path', 'rollout_state_hash', 'rollout_state_record_count',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review',
            'operator_approved', 'approval_reference', 'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (str_starts_with((string) ($result['status'] ?? ''), 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_PASSED')) {
            if ((bool) $this->option('progress')) {
                $this->line('C165 PLAN/CONFIRM controlled rollout execution completed');
            }

            return 0;
        }

        $this->error((string) ($result['message'] ?? 'C165 controlled rollout execution failed.'));

        return 1;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value === null ? '' : (string) $value;
    }
}
