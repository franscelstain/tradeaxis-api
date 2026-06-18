<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC21EntryExitBehaviorDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC21EntryExitBehaviorDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c21-entry-exit-behavior-diagnose
        {--catalog-code= : Source immutable catalog code. Defaults to C17.}
        {--from= : IS window start date. Must be 2023-01-02.}
        {--to= : IS window end date. Must be 2025-05-21.}
        {--param-ids= : Optional comma-separated param ids.}
        {--profiles= : Optional comma-separated C21 diagnostic profile codes.}
        {--profile-codes= : Alias for --profiles.}
        {--max-profiles= : Optional cap on the number of profiles to run.}
        {--max-params= : Optional cap on source rows after param filtering.}
        {--max-picks= : Optional cap on fixed recommendation rows after recommendation freeze for bounded diagnostics.}
        {--progress : Print progress before each profile execution.}
        {--output= : Output aggregate artifact path.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C21 IS-only entry/exit behavior diagnostic without catalog, OOS, or production readiness.';

    private WatchlistBacktestC21EntryExitBehaviorDiagnosticService $service;

    public function __construct(WatchlistBacktestC21EntryExitBehaviorDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC21EntryExitBehaviorDiagnosticService();
    }

    public function handle(): int
    {
        $catalogCode = (string) ($this->option('catalog-code') ?: WatchlistBacktestC21EntryExitBehaviorDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE);
        $from = (string) ($this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $to = (string) ($this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC21EntryExitBehaviorDiagnosticService::DEFAULT_OUTPUT_PATH);

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
            'profile_count',
            'profile_scope',
            'evaluated_picks_count',
            'path_missing_count',
            'avg_entry_gap_pct',
            'median_entry_gap_pct',
            'never_profitable_rate',
            'gave_back_profit_rate',
            'gap_open_loss_rate',
            'exit_stop_count',
            'exit_target_count',
            'exit_hold_count',
            'median_mfe_5d',
            'median_mae_5d',
            'diagnostic_signal_found',
            'entry_problem_suspected',
            'exit_problem_suspected',
            'stop_problem_suspected',
            'hold_period_problem_suspected',
            'regime_explains_execution_problem',
            'c21_catalog_implementation_deferred',
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
