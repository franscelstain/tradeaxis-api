<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC26CatalogCandidateDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC26CatalogCandidateDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c26-catalog-candidate-diagnose
        {--catalog-code= : Source catalog code for traceability.}
        {--from= : Frozen IS start date.}
        {--to= : Frozen IS end date.}
        {--param-ids= : Optional comma-separated param IDs to filter fixed C25 artifact rows.}
        {--candidate-profile-code= : Primary C25 candidate profile code.}
        {--defensive-comparator-code= : C25 defensive comparator profile code.}
        {--next-open-delay-comparator-code= : C25 next-open-delay comparator profile code.}
        {--diagnostic-profile-codes= : Optional comma-separated C26 diagnostic profile codes.}
        {--profile-codes= : Alias for --diagnostic-profile-codes.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}
        {--max-params= : Optional maximum params to retain from the C25 artifact when --param-ids is omitted.}
        {--max-picks= : Optional maximum fixed R09 picks to evaluate.}
        {--max-diagnostic-profiles= : Optional maximum C26 diagnostic profiles to evaluate.}
        {--input-c21-artifact= : Optional C21 path artifact for derived MFE/MAE dependency audit.}
        {--input-c23-artifact= : Optional C23 first-profit-capture artifact path.}
        {--input-c24-artifact= : Optional C24 gap bridge artifact path.}
        {--input-c25-artifact= : C25 no-signal fallback/delay diagnostic artifact path.}
        {--output= : Output C26 catalog-candidate diagnostic artifact path.}';

    protected $description = 'Run C26 IS-only catalog-candidate diagnostic from C25/C23/C24/C21 artifacts without catalog, OOS, or production readiness.';

    private WatchlistBacktestC26CatalogCandidateDiagnosticService $service;

    public function __construct(WatchlistBacktestC26CatalogCandidateDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC26CatalogCandidateDiagnosticService();
    }

    public function handle(): int
    {
        $c25Input = (string) ($this->option('input-c25-artifact') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::DEFAULT_C25_INPUT_PATH);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::DEFAULT_OUTPUT_PATH);
        $profileCodes = $this->option('diagnostic-profile-codes') ?: $this->option('profile-codes');

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($c25Input, $output, [
            'catalog_code' => $this->option('catalog-code') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE,
            'from' => $this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            'to' => $this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            'param_ids' => $this->option('param-ids'),
            'candidate_profile_code' => $this->option('candidate-profile-code') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G21,
            'defensive_comparator_code' => $this->option('defensive-comparator-code') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G13,
            'next_open_delay_comparator_code' => $this->option('next-open-delay-comparator-code') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G16,
            'diagnostic_profile_codes' => $profileCodes,
            'max_params' => $this->option('max-params'),
            'max_picks' => $this->option('max-picks'),
            'max_diagnostic_profiles' => $this->option('max-diagnostic-profiles'),
            'input_c21_artifact' => $this->option('input-c21-artifact') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::DEFAULT_C21_INPUT_PATH,
            'input_c23_artifact' => $this->option('input-c23-artifact') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::DEFAULT_C23_INPUT_PATH,
            'input_c24_artifact' => $this->option('input-c24-artifact') ?: WatchlistBacktestC26CatalogCandidateDiagnosticService::DEFAULT_C24_INPUT_PATH,
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
            'c21_input_artifact_hash',
            'c23_input_artifact_hash',
            'c24_input_artifact_hash',
            'c25_input_artifact_hash',
            'primary_candidate',
            'defensive_comparator',
            'next_open_delay_comparator',
            'r09_avg_ret_net',
            'r09_median_ret_net',
            'r09_p25_ret_net',
            'r09_win_rate',
            'g21_avg_ret_net',
            'g21_median_ret_net',
            'g21_p25_ret_net',
            'g21_win_rate',
            'g13_avg_ret_net',
            'g13_median_ret_net',
            'g13_p25_ret_net',
            'g13_win_rate',
            'g16_avg_ret_net',
            'g16_median_ret_net',
            'g16_p25_ret_net',
            'g16_win_rate',
            'g21_param_pass_count',
            'g21_param_fail_count',
            'g21_month_pass_count',
            'g21_month_fail_count',
            'g21_bucket_pass_count',
            'g21_bucket_fail_count',
            'raw_ohlc_validation_required',
            'derived_mfe_mae_dependency_detected',
            'lookahead_violation_count',
            'ambiguous_intraday_sequence_count',
            'g21_primary_candidate_ready',
            'g13_defensive_candidate_ready',
            'g16_next_open_delay_component_ready',
            'c27_catalog_candidate_implementation_recommended',
            'c27_requires_raw_ohlc_validation_first',
            'exit_rule_path_still_viable',
            'selection_quality_revisit_needed',
            'c26_catalog_implementation_deferred',
            'c26_catalog_code',
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
