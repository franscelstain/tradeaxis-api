<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService;
use Illuminate\Console\Command;

class RunBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution
        {--c149-artifact=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json}
        {--expected-c149-hash=311898597454a6a1984f4ed84473ad52ba6859fb}
        {--expected-c149-file-sha1=3B14776D36FBC922782B332BDC55CE90B50188E5}
        {--activation-reference=}
        {--output=storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json}
        {--runtime-state=storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json}
        {--operator-approved}
        {--enable-runtime-bridge}
        {--enable-live-output}
        {--confirm-rollback}
        {--confirm-kill-switch}
        {--overwrite}
        {--overwrite-runtime-state}
        {--progress}';

    protected $description = 'Run C150 weekly swing watchlist production/live runtime activation final execution with explicit runtime bridge and live output enablement.';

    private WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService $service;

    public function __construct(?WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C150 weekly swing watchlist production/live runtime activation final execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c149-artifact'),
            (string) $this->option('expected-c149-hash'),
            (string) $this->option('expected-c149-file-sha1'),
            (string) $this->option('output'),
            (string) $this->option('runtime-state'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'overwrite_runtime_state' => (bool) $this->option('overwrite-runtime-state'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'activation_reference' => (string) $this->option('activation-reference'),
                'enable_runtime_bridge' => (bool) $this->option('enable-runtime-bridge'),
                'enable_live_output' => (bool) $this->option('enable-live-output'),
                'confirm_rollback' => (bool) $this->option('confirm-rollback'),
                'confirm_kill_switch' => (bool) $this->option('confirm-kill-switch'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'runtime_state_path', 'runtime_state_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_pass',
            'production_live_runtime_activation_final_execution_pass',
            'production_live_runtime_activation_executed',
            'production_ready', 'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'c149_lock_valid', 'c149_operator_go_no_go_valid', 'c149_convert_from_json_pass',
            'c148_activation_observation_result_review_valid',
            'c147_activation_observation_review_valid',
            'c146_activation_execution_review_valid',
            'c145_activation_authorization_valid',
            'c144_pre_activation_boundary_valid',
            'c143_go_decision_finalization_valid',
            'c142_activation_operator_go_no_go_valid',
            'c141_activation_observation_result_review_valid',
            'activation_authorized', 'primary_candidate_activation_authorized',
            'backup_candidate_activation_authorized', 'comparator_candidate_activation_authorized',
            'primary_candidate_live_runtime_active',
            'backup_candidate_live_runtime_standby_active',
            'comparator_candidate_live_runtime_active',
            'a01_remains_comparator_only',
            'operator_approved', 'activation_reference',
            'runtime_bridge_enablement_confirmed', 'live_output_enablement_confirmed',
            'rollback_confirmed', 'kill_switch_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c149_hash', 'actual_c149_hash', 'c149_hash_match',
            'expected_c149_file_sha1', 'actual_c149_file_sha1', 'c149_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C150 weekly swing watchlist production/live runtime activation final execution completed');
            }

            return 0;
        }

        if (($result['message'] ?? null) !== null) {
            $this->error((string) $result['message']);
        }

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
