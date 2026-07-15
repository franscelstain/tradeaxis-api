<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review
        {--c134-artifact=storage/app/watchlist/backtest/c134-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json}
        {--expected-c134-hash=ada066cc599d749e050b5efd61073ccad1e64b74}
        {--expected-c134-file-sha1=AE7C013A1B5CC0DFC5968C4FC99B2E1DDFF88F3E}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-go-decision-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C135 weekly swing watchlist production/live runtime activation operator go/no-go review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C135 weekly swing watchlist production/live runtime activation operator go/no-go review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c134-artifact'),
            (string) $this->option('expected-c134-hash'),
            (string) $this->option('expected-c134-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_go_decision_confirmed' => (bool) $this->option('operator-go-decision-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass',
            'production_live_runtime_activation_operator_go_no_go_review_pass',
            'operator_go_decision', 'operator_go_decision_confirmed',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'production_live_runtime_activation_operator_go_no_go_manifest_created',
            'production_live_runtime_activation_go_decision_finalization_review_allowed_next',
            'production_live_runtime_activation_executed',
            'c134_lock_valid', 'c134_activation_observation_result_review_valid', 'c134_convert_from_json_pass',
            'c133_activation_observation_review_valid', 'c132_activation_execution_review_valid',
            'c131_activation_approval_valid', 'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c134_observation_result_review_only', 'c134_not_live_runtime_state_change',
            'c135_operator_go_no_go_review_only', 'c135_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c134_hash', 'actual_c134_hash', 'c134_hash_match',
            'expected_c134_file_sha1', 'actual_c134_file_sha1', 'c134_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_go_decision_finalization_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C135 weekly swing watchlist production/live runtime activation operator go/no-go review completed');
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
