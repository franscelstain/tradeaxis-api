<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC20RegimeTradeDateDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC20RegimeTradeDateDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c20-regime-trade-date-diagnose
        {--catalog-code= : Source immutable catalog code. Defaults to C17.}
        {--from= : IS window start date. Must be 2023-01-02.}
        {--to= : IS window end date. Must be 2025-05-21.}
        {--param-ids= : Optional comma-separated param ids.}
        {--profiles= : Optional comma-separated C20 regime profile codes.}
        {--profile-codes= : Alias for --profiles.}
        {--max-profiles= : Optional cap on the number of profiles to run.}
        {--max-params= : Optional cap on source rows after param filtering.}
        {--progress : Print progress before each profile execution.}
        {--output= : Output aggregate artifact path.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C20 IS-only regime and trade-date quality gate diagnostic without catalog, OOS, or production readiness.';

    private WatchlistBacktestC20RegimeTradeDateDiagnosticService $service;

    public function __construct(WatchlistBacktestC20RegimeTradeDateDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC20RegimeTradeDateDiagnosticService();
    }

    public function handle(): int
    {
        $catalogCode = (string) ($this->option('catalog-code') ?: WatchlistBacktestC20RegimeTradeDateDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE);
        $from = (string) ($this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $to = (string) ($this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC20RegimeTradeDateDiagnosticService::DEFAULT_OUTPUT_PATH);

        $profiles = (string) ($this->option('profiles') ?: '');
        $profileCodes = (string) ($this->option('profile-codes') ?: '');
        if ($profiles === '' && $profileCodes !== '') {
            $profiles = $profileCodes;
        }

        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute($catalogCode, $from, $to, $output, [
            'param_ids' => (string) ($this->option('param-ids') ?: ''),
            'profiles' => $profiles,
            'max_profiles' => $this->option('max-profiles'),
            'max_params' => $this->option('max-params'),
            'progress_callback' => $progress,
            'overwrite' => (bool) $this->option('overwrite'),
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'profile_count',
            'profile_scope',
            'best_any_sample_profile_code',
            'best_promising_sample_profile_code',
            'best_sample_qualified_profile_code',
            'best_quality_target_profile_code',
            'profiles_with_quality_improvement',
            'profiles_with_promising_continue',
            'profiles_with_quality_target_reached',
            'c20_catalog_implementation_deferred',
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
