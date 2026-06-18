<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC24C22ShadowGapBridgeDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose
        {--input= : C23 first-profit-capture all-param artifact path.}
        {--output= : Output C24 gap bridge artifact path.}
        {--candidate-profile-code= : C23 rule profile to compare against C22 S06. Defaults to C23_R09.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C24 IS-only C22 shadow gap bridge diagnostic from the C23 artifact without catalog, OOS, or production readiness.';

    private WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService $service;

    public function __construct(WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService();
    }

    public function handle(): int
    {
        $input = (string) ($this->option('input') ?: WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService::DEFAULT_INPUT_PATH);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService::DEFAULT_OUTPUT_PATH);

        $result = $this->service->execute($input, $output, [
            'candidate_profile_code' => $this->option('candidate-profile-code') ?: WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService::DEFAULT_CANDIDATE_PROFILE,
            'overwrite' => (bool) $this->option('overwrite'),
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'c23_artifact_hash',
            'candidate_profile_code',
            'evaluated_picks_count',
            'candidate_avg_ret_net',
            'candidate_median_ret_net',
            'candidate_p25_ret_net',
            'candidate_win_rate',
            'c22_shadow_s06_avg_ret_net',
            'c22_shadow_s06_median_ret_net',
            'c22_shadow_s06_p25_ret_net',
            'c22_shadow_s06_win_rate',
            'avg_gap_vs_c22_s06',
            'median_gap_vs_c22_s06',
            'p25_gap_vs_c22_s06',
            'win_rate_gap_vs_c22_s06',
            'avg_capture_ratio_vs_c22_s06',
            'median_capture_ratio_vs_c22_s06',
            'p25_capture_ratio_vs_c22_s06',
            'win_rate_capture_ratio_vs_c22_s06',
            'rows_where_c22_beats_candidate_rate',
            'dominant_gap_component',
            'c24_gap_bridge_explained',
            'c24_catalog_implementation_deferred',
            'c24_catalog_code',
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
