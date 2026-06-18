<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC22ExitCaptureShadowDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC22ExitCaptureShadowDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c22-exit-capture-shadow-diagnose
        {--catalog-code= : Source immutable catalog code. Defaults to C17.}
        {--from= : IS window start date. Must be 2023-01-02.}
        {--to= : IS window end date. Must be 2025-05-21.}
        {--param-ids= : Optional comma-separated param ids.}
        {--shadow-profile-codes= : Optional comma-separated C22 shadow exit profile codes.}
        {--profiles= : Optional comma-separated C22 shadow exit profile codes.}
        {--profile-codes= : Alias for --shadow-profile-codes.}
        {--max-shadow-profiles= : Optional cap on the number of shadow profiles to run.}
        {--max-profiles= : Alias for --max-shadow-profiles.}
        {--max-params= : Optional cap on source rows after param filtering.}
        {--max-picks= : Optional cap on fixed recommendation rows after recommendation freeze for bounded diagnostics.}
        {--progress : Print progress before each param execution.}
        {--output= : Output aggregate artifact path.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C22 IS-only exit capture shadow diagnostic without catalog, OOS, or production readiness.';

    private WatchlistBacktestC22ExitCaptureShadowDiagnosticService $service;

    public function __construct(WatchlistBacktestC22ExitCaptureShadowDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC22ExitCaptureShadowDiagnosticService();
    }

    public function handle(): int
    {
        $catalogCode = (string) ($this->option('catalog-code') ?: WatchlistBacktestC22ExitCaptureShadowDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE);
        $from = (string) ($this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $to = (string) ($this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC22ExitCaptureShadowDiagnosticService::DEFAULT_OUTPUT_PATH);

        $shadowProfiles = (string) ($this->option('shadow-profile-codes') ?: '');
        $profiles = (string) ($this->option('profiles') ?: '');
        $profileCodes = (string) ($this->option('profile-codes') ?: '');
        if ($shadowProfiles === '' && $profiles !== '') {
            $shadowProfiles = $profiles;
        }
        if ($shadowProfiles === '' && $profileCodes !== '') {
            $shadowProfiles = $profileCodes;
        }

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($catalogCode, $from, $to, $output, [
            'param_ids' => (string) ($this->option('param-ids') ?: ''),
            'shadow_profiles' => $shadowProfiles,
            'max_shadow_profiles' => $this->option('max-shadow-profiles') ?: $this->option('max-profiles'),
            'max_params' => $this->option('max-params'),
            'max_picks' => $this->option('max-picks'),
            'progress_callback' => $progress,
            'overwrite' => (bool) $this->option('overwrite'),
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'shadow_profile_count',
            'profile_scope',
            'evaluated_picks_count',
            'path_missing_count',
            'canonical_avg_ret_net',
            'canonical_median_ret_net',
            'canonical_p25_ret_net',
            'canonical_win_rate',
            'canonical_gave_back_profit_rate',
            'best_shadow_profile_code_by_median',
            'best_shadow_profile_code_by_p25',
            'best_shadow_profile_code_by_giveback_reduction',
            'best_shadow_median_delta_vs_canonical',
            'best_shadow_p25_delta_vs_canonical',
            'best_giveback_reduction_vs_canonical',
            'exit_capture_signal_found',
            'early_exit_suspected_better',
            'profit_lock_suspected_better',
            'breakeven_suspected_better',
            'trailing_suspected_better',
            'target_distance_problem_suspected',
            'stop_distance_problem_suspected',
            'hold_compression_suspected_better',
            'c22_catalog_implementation_deferred',
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
