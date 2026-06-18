<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC28RuleRevisionTiebreakDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c28-rule-revision-tiebreak-diagnose
        {--catalog-code= : Source catalog code for traceability.}
        {--from= : Frozen IS start date.}
        {--to= : Frozen IS end date.}
        {--param-ids= : Optional comma-separated param IDs to filter C27 raw rows.}
        {--candidate-profile-code= : Primary C28 candidate profile code.}
        {--diagnostic-profile-codes= : Optional comma-separated C28 diagnostic profile codes.}
        {--profile-codes= : Alias for --diagnostic-profile-codes.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}
        {--max-params= : Optional maximum params to retain from the C27 artifact when --param-ids is omitted.}
        {--max-picks= : Optional maximum C27 raw picks to evaluate.}
        {--max-diagnostic-profiles= : Optional maximum C28 diagnostic profiles to output.}
        {--input-c27-artifact= : C27 raw OHLC validation artifact path.}
        {--output= : Output C28 rule revision/tiebreak diagnostic artifact path.}';

    protected $description = 'Run C28 IS-only rule revision/tiebreak diagnostic from C27 raw OHLC artifact without catalog, OOS, or production readiness.';

    private WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService $service;

    public function __construct(WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService();
    }

    public function handle(): int
    {
        $c27Input = (string) ($this->option('input-c27-artifact') ?: WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService::DEFAULT_C27_INPUT_PATH);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService::DEFAULT_OUTPUT_PATH);
        $profileCodes = $this->option('diagnostic-profile-codes') ?: $this->option('profile-codes');

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($c27Input, $output, [
            'catalog_code' => $this->option('catalog-code') ?: WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE,
            'from' => $this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            'to' => $this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            'param_ids' => $this->option('param-ids'),
            'candidate_profile_code' => $this->option('candidate-profile-code') ?: WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService::PRIMARY_PROFILE,
            'diagnostic_profile_codes' => $profileCodes,
            'max_params' => $this->option('max-params'),
            'max_picks' => $this->option('max-picks'),
            'max_diagnostic_profiles' => $this->option('max-diagnostic-profiles'),
            'overwrite' => (bool) $this->option('overwrite'),
            'progress_callback' => $progress,
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'diagnostic_profile_count',
            'profile_scope',
            'candidate_profile_code',
            'evaluated_picks_count',
            'c27_input_artifact_hash',
            'raw_ohlc_validation_pass',
            'raw_r09_avg_ret_net',
            'raw_r09_median_ret_net',
            'raw_r09_p25_ret_net',
            'raw_g21_avg_ret_net',
            'raw_g21_median_ret_net',
            'raw_g21_p25_ret_net',
            'candidate_avg_ret_net',
            'candidate_median_ret_net',
            'candidate_p25_ret_net',
            'candidate_win_rate',
            'candidate_avg_delta_vs_r09',
            'candidate_median_delta_vs_r09',
            'candidate_p25_delta_vs_r09',
            'candidate_param_pass_count',
            'candidate_param_fail_count',
            'candidate_month_pass_count',
            'candidate_month_fail_count',
            'candidate_bucket_pass_count',
            'candidate_bucket_fail_count',
            'lookahead_violation_count',
            'c28_revised_candidate_ready',
            'c29_oos_proof_recommended',
            'c28_catalog_implementation_deferred',
            'c28_catalog_code',
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
