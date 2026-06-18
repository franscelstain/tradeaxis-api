<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC27CatalogCandidateRawOhlcValidationService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC27CatalogCandidateRawOhlcValidateCommand extends Command
{
    protected $signature = 'watchlist:backtest-c27-catalog-candidate-raw-ohlc-validate
        {--catalog-code= : Source catalog code for traceability.}
        {--from= : Frozen IS start date.}
        {--to= : Frozen IS end date.}
        {--param-ids= : Optional comma-separated param IDs to filter fixed C26 G21 rows.}
        {--validation-profile-codes= : Optional comma-separated C27 validation profile codes.}
        {--profile-codes= : Alias for --validation-profile-codes.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}
        {--max-params= : Optional maximum params to retain from the C26 artifact when --param-ids is omitted.}
        {--max-picks= : Optional maximum fixed C26 G21 picks to validate.}
        {--max-validation-profiles= : Optional maximum C27 validation profiles to output.}
        {--input-c26-artifact= : C26 catalog-candidate diagnostic artifact path.}
        {--input-c21-artifact= : C21 canonical path artifact path for stop/target level traceability.}
        {--output= : Output C27 raw OHLC validation artifact path.}';

    protected $description = 'Run C27 IS-only raw OHLC validation for the C26 G21 catalog candidate without catalog creation, OOS, or production readiness.';

    private WatchlistBacktestC27CatalogCandidateRawOhlcValidationService $service;

    public function __construct(WatchlistBacktestC27CatalogCandidateRawOhlcValidationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC27CatalogCandidateRawOhlcValidationService();
    }

    public function handle(): int
    {
        $c26Input = (string) ($this->option('input-c26-artifact') ?: WatchlistBacktestC27CatalogCandidateRawOhlcValidationService::DEFAULT_C26_INPUT_PATH);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC27CatalogCandidateRawOhlcValidationService::DEFAULT_OUTPUT_PATH);
        $profileCodes = $this->option('validation-profile-codes') ?: $this->option('profile-codes');

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($c26Input, $output, [
            'catalog_code' => $this->option('catalog-code') ?: WatchlistBacktestC27CatalogCandidateRawOhlcValidationService::DEFAULT_SOURCE_CATALOG_CODE,
            'from' => $this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            'to' => $this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            'param_ids' => $this->option('param-ids'),
            'validation_profile_codes' => $profileCodes,
            'max_params' => $this->option('max-params'),
            'max_picks' => $this->option('max-picks'),
            'max_validation_profiles' => $this->option('max-validation-profiles'),
            'input_c21_artifact' => $this->option('input-c21-artifact') ?: WatchlistBacktestC27CatalogCandidateRawOhlcValidationService::DEFAULT_C21_INPUT_PATH,
            'overwrite' => (bool) $this->option('overwrite'),
            'progress_callback' => $progress,
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'validation_profile_count',
            'profile_scope',
            'evaluated_picks_count',
            'raw_ohlc_validated_count',
            'raw_ohlc_missing_count',
            'raw_ohlc_validation_pass',
            'c21_input_artifact_hash',
            'c26_input_artifact_hash',
            'raw_r09_avg_ret_net',
            'raw_r09_median_ret_net',
            'raw_r09_p25_ret_net',
            'raw_g21_avg_ret_net',
            'raw_g21_median_ret_net',
            'raw_g21_p25_ret_net',
            'raw_g13_avg_ret_net',
            'raw_g16_avg_ret_net',
            'g21_raw_beats_r09',
            'g21_raw_catalog_candidate_ready',
            'c28_oos_proof_recommended',
            'c27_catalog_implementation_deferred',
            'c27_catalog_code',
            'derived_mfe_mae_used_for_execution',
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
