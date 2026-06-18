<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC25NoSignalFallbackDelayDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c25-no-signal-fallback-delay-diagnose
        {--catalog-code= : Source catalog code for traceability.}
        {--from= : Frozen IS start date.}
        {--to= : Frozen IS end date.}
        {--param-ids= : Optional comma-separated param IDs to filter fixed C23 artifact rows.}
        {--diagnostic-profile-codes= : Optional comma-separated C25 diagnostic profile codes.}
        {--profile-codes= : Alias for --diagnostic-profile-codes.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}
        {--max-params= : Accepted for operator compatibility; C25 artifact-mode filtering uses --param-ids.}
        {--max-picks= : Optional maximum fixed R09 picks to evaluate.}
        {--max-diagnostic-profiles= : Optional maximum C25 diagnostic profiles to evaluate.}
        {--input-c23-artifact= : C23 all-param first-profit-capture artifact path.}
        {--input-c24-artifact= : C24 gap bridge artifact path.}
        {--input-c21-artifact= : Optional C21 path artifact for derived MFE/MAE intraday diagnostics.}
        {--output= : Output C25 no-signal fallback/delay diagnostic artifact path.}';

    protected $description = 'Run C25 IS-only no-signal fallback and next-open delay diagnostic from C23/C24 artifacts without catalog, OOS, or production readiness.';

    private WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService $service;

    public function __construct(WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService();
    }

    public function handle(): int
    {
        $c23Input = (string) ($this->option('input-c23-artifact') ?: WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::DEFAULT_C23_INPUT_PATH);
        $c24Input = (string) ($this->option('input-c24-artifact') ?: WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::DEFAULT_C24_INPUT_PATH);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::DEFAULT_OUTPUT_PATH);
        $profileCodes = $this->option('diagnostic-profile-codes') ?: $this->option('profile-codes');

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($c23Input, $c24Input, $output, [
            'catalog_code' => $this->option('catalog-code') ?: WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE,
            'from' => $this->option('from') ?: \App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            'to' => $this->option('to') ?: \App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            'param_ids' => $this->option('param-ids'),
            'diagnostic_profile_codes' => $profileCodes,
            'max_params' => $this->option('max-params'),
            'max_picks' => $this->option('max-picks'),
            'max_diagnostic_profiles' => $this->option('max-diagnostic-profiles'),
            'input_c21_artifact' => $this->option('input-c21-artifact') ?: WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::DEFAULT_C21_INPUT_PATH,
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
            'evaluated_picks_count',
            'path_missing_count',
            'c23_input_artifact_hash',
            'c24_input_artifact_hash',
            'canonical_avg_ret_net',
            'c22_s06_avg_ret_net',
            'c23_r09_avg_ret_net',
            'c23_r15_p25_ret_net',
            'c23_r16_p25_ret_net',
            'no_signal_fallback_count',
            'next_open_delay_count',
            'best_profile_code_by_avg',
            'best_profile_code_by_median',
            'best_profile_code_by_p25',
            'best_profile_code_by_distribution_balance',
            'best_no_signal_fallback_profile',
            'best_next_open_delay_profile',
            'no_signal_fallback_fix_found',
            'next_open_delay_fix_found',
            'distribution_balance_candidate_found',
            'intraday_preplanned_order_candidate_found',
            'exit_rule_path_still_viable',
            'selection_quality_revisit_needed',
            'c26_catalog_candidate_diagnostic_recommended',
            'c25_catalog_implementation_deferred',
            'c25_catalog_code',
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
