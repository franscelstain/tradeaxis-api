<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC133WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC133WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c133-weekly-swing-watchlist-production-live-runtime-activation-observation-review
        {--c132-artifact=storage/app/watchlist/backtest/c132-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json}
        {--expected-c132-hash=b25941d82b4affd0a48141f51b7e4fa13d9bc9b7}
        {--expected-c132-file-sha1=1391EC55779C113F762707FFB707F2F06D02197E}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c133-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C133 weekly swing watchlist production/live runtime activation observation review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC133WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService $service;

    public function __construct(?WatchlistBacktestC133WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC133WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C133 weekly swing watchlist production/live runtime activation observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c132-artifact'),
            (string) $this->option('expected-c132-hash'),
            (string) $this->option('expected-c132-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_pass',
            'production_live_runtime_activation_observation_review_pass',
            'ready_for_production_live_runtime_activation_observation_result_review',
            'production_live_runtime_activation_observation_result_review_allowed_next',
            'production_live_runtime_activation_observation_review_manifest_created',
            'production_live_runtime_activation_executed',
            'c132_lock_valid', 'c132_activation_execution_review_valid', 'c132_convert_from_json_pass',
            'c131_activation_approval_valid', 'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c132_execution_review_only', 'c132_not_live_runtime_state_change',
            'c133_observation_review_only', 'c133_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_observation_result_review',
            'backup_candidate_ready_for_production_live_runtime_activation_observation_result_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_observation_result_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c132_hash', 'actual_c132_hash', 'c132_hash_match',
            'expected_c132_file_sha1', 'actual_c132_file_sha1', 'c132_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_observation_result_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C133_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C133 weekly swing watchlist production/live runtime activation observation review completed');
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
