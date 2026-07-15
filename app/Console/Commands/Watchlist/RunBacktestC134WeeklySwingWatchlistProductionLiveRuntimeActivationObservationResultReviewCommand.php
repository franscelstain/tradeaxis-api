<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC134WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC134WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c134-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review
        {--c133-artifact=storage/app/watchlist/backtest/c133-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json}
        {--expected-c133-hash=225cdb28fecb555d87897b3dad0638a3aea562b3}
        {--expected-c133-file-sha1=C8A2E1BEB7EA86C9280A42F1D617D5DACB78ADD8}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c134-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C134 weekly swing watchlist production/live runtime activation observation result review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC134WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService $service;

    public function __construct(?WatchlistBacktestC134WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC134WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C134 weekly swing watchlist production/live runtime activation observation result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c133-artifact'),
            (string) $this->option('expected-c133-hash'),
            (string) $this->option('expected-c133-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass',
            'production_live_runtime_activation_observation_result_review_pass',
            'ready_for_production_live_runtime_activation_operator_go_no_go_review',
            'production_live_runtime_activation_operator_go_no_go_review_allowed_next',
            'production_live_runtime_activation_observation_result_review_manifest_created',
            'production_live_runtime_activation_executed',
            'c133_lock_valid', 'c133_activation_observation_review_valid', 'c133_convert_from_json_pass',
            'c132_activation_execution_review_valid', 'c131_activation_approval_valid', 'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c133_observation_review_only', 'c133_not_live_runtime_state_change',
            'c134_observation_result_review_only', 'c134_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
            'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c133_hash', 'actual_c133_hash', 'c133_hash_match',
            'expected_c133_file_sha1', 'actual_c133_file_sha1', 'c133_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_operator_go_no_go_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C134_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C134 weekly swing watchlist production/live runtime activation observation result review completed');
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
