<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC23FirstProfitCaptureRuleDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c23-first-profit-capture-rule-diagnose
        {--catalog-code= : Source immutable catalog code. Defaults to C17.}
        {--from= : IS window start date. Must be 2023-01-02.}
        {--to= : IS window end date. Must be 2025-05-21.}
        {--param-ids= : Optional comma-separated param ids.}
        {--rule-profile-codes= : Optional comma-separated C23 rule candidate profile codes.}
        {--profiles= : Alias for --rule-profile-codes.}
        {--profile-codes= : Alias for --rule-profile-codes.}
        {--max-rule-profiles= : Optional cap on the number of rule profiles to run.}
        {--max-profiles= : Alias for --max-rule-profiles.}
        {--max-params= : Optional cap on source rows after param filtering.}
        {--max-picks= : Optional cap on fixed recommendation rows after recommendation freeze for bounded diagnostics.}
        {--progress : Print progress before each param execution.}
        {--selection-output= : Optional C19 selection diagnostic artifact path. Defaults to output.c19-selection-analysis.json.}
        {--reuse-selection-artifact : Reuse --selection-output without recomputing C19 selection diagnostic.}
        {--output= : Output aggregate artifact path.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C23 IS-only first profit capture rule diagnostic without catalog, OOS, or production readiness.';

    private WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService $service;

    public function __construct(WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService();
    }

    public function handle(): int
    {
        $catalogCode = (string) ($this->option('catalog-code') ?: WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE);
        $from = (string) ($this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $to = (string) ($this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService::DEFAULT_OUTPUT_PATH);

        $ruleProfiles = (string) ($this->option('rule-profile-codes') ?: '');
        $profiles = (string) ($this->option('profiles') ?: '');
        $profileCodes = (string) ($this->option('profile-codes') ?: '');
        if ($ruleProfiles === '' && $profiles !== '') {
            $ruleProfiles = $profiles;
        }
        if ($ruleProfiles === '' && $profileCodes !== '') {
            $ruleProfiles = $profileCodes;
        }

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($catalogCode, $from, $to, $output, [
            'param_ids' => (string) ($this->option('param-ids') ?: ''),
            'rule_profiles' => $ruleProfiles,
            'max_rule_profiles' => $this->option('max-rule-profiles') ?: $this->option('max-profiles'),
            'max_params' => $this->option('max-params'),
            'max_picks' => $this->option('max-picks'),
            'selection_output_path' => $this->option('selection-output') ?: null,
            'reuse_selection_artifact' => (bool) $this->option('reuse-selection-artifact'),
            'progress_callback' => $progress,
            'overwrite' => (bool) $this->option('overwrite'),
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'rule_profile_count',
            'profile_scope',
            'evaluated_picks_count',
            'path_missing_count',
            'canonical_avg_ret_net',
            'canonical_median_ret_net',
            'canonical_p25_ret_net',
            'canonical_win_rate',
            'canonical_gave_back_profit_rate',
            'c22_shadow_s06_avg_ret_net',
            'c22_shadow_s06_median_ret_net',
            'c22_shadow_s06_p25_ret_net',
            'c22_shadow_s06_win_rate',
            'best_rule_profile_code_by_avg',
            'best_rule_profile_code_by_median',
            'best_rule_profile_code_by_p25',
            'best_rule_profile_code_by_win_rate',
            'best_rule_profile_code_by_giveback_reduction',
            'best_rule_profile_code_by_closest_to_c22_s06',
            'best_rule_median_delta_vs_canonical',
            'best_rule_p25_delta_vs_canonical',
            'best_rule_giveback_reduction_vs_canonical',
            'best_rule_profit_capture_gap_vs_c22_s06',
            'first_profit_capture_rule_signal_found',
            'c22_shadow_gap_acceptable',
            'non_lookahead_rule_candidate_found',
            'damage_control_candidate_found',
            'combo_rule_candidate_found',
            'param_consistency_found',
            'month_stability_sufficient',
            'c23_catalog_implementation_deferred',
            'c23_catalog_code',
            'oos_service_invoked',
            'oos_repository_invoked',
            'oos_executed',
            'production_ready',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (($result['status'] ?? null) !== 'PASS') {
            if (isset($result['message'])) {
                $this->error((string) $result['message']);
            }
            return 1;
        }

        return 0;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value === null) {
            return '';
        }
        return (string) $value;
    }
}
