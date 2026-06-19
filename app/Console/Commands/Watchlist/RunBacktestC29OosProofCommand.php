<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC29OosProofService;
use Illuminate\Console\Command;

class RunBacktestC29OosProofCommand extends Command
{
    protected $signature = 'watchlist:backtest-c29-oos-proof
        {--c28-artifact= : Locked C28 all-param artifact path.}
        {--expected-c28-hash= : Expected locked C28 artifact stable hash.}
        {--candidate-profile-code= : Locked C28 candidate profile code.}
        {--from= : Reserved OOS start date.}
        {--to= : Reserved OOS end date.}
        {--output= : Output C29 OOS proof artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C29 OOS proof for the locked C28 G05 candidate without retuning, promotion, or production readiness.';

    private WatchlistBacktestC29OosProofService $service;

    public function __construct(WatchlistBacktestC29OosProofService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC29OosProofService();
    }

    public function handle(): int
    {
        $progress = null;
        if ((bool) $this->option('progress')) {
            $progress = function (string $message): void {
                $this->line($message);
            };
        }

        $result = $this->service->execute(
            (string) ($this->option('c28-artifact') ?: WatchlistBacktestC29OosProofService::DEFAULT_C28_ARTIFACT),
            (string) ($this->option('expected-c28-hash') ?: WatchlistBacktestC29OosProofService::DEFAULT_EXPECTED_C28_HASH),
            (string) ($this->option('candidate-profile-code') ?: WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE),
            (string) ($this->option('from') ?: WatchlistBacktestC29OosProofService::OOS_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC29OosProofService::OOS_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC29OosProofService::DEFAULT_OUTPUT_PATH),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'progress_callback' => $progress,
            ]
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'expected_c28_hash',
            'actual_c28_hash',
            'c28_hash_match',
            'evaluated_picks_count',
            'avg_ret_net',
            'median_ret_net',
            'p25_ret_net',
            'win_rate',
            'month_win_rate_min',
            'month_avg_ret_net_min',
            'lookahead_violation_count',
            'production_ready',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (($result['status'] ?? null) === 'C29_OOS_PROOF_PASSED_NOT_PRODUCTION_READY') {
            return 0;
        }

        if (isset($result['message'])) {
            $this->error((string) $result['message']);
        }
        return 1;
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
