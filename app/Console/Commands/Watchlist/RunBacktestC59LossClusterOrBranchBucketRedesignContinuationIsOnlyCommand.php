<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only
        {--c58-artifact=storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json}
        {--expected-c58-hash=80d09de8053659bf01ce5b8b72d9e2d82cdf69dc}
        {--expected-c58-file-sha1=FA6FE27604F6CDA664DCF90A251AF41672670700}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C59 IS-only loss-cluster or branch/bucket redesign continuation from locked C58 evidence without OOS access.';

    private WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService $service;

    public function __construct(?WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C59 IS-only loss-cluster or branch/bucket redesign continuation started'); }
        $result = $this->service->execute(
            (string) $this->option('c58-artifact'),
            (string) $this->option('expected-c58-hash'),
            (string) $this->option('expected-c58-file-sha1'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c58_hash', 'actual_c58_hash', 'c58_hash_match', 'expected_c58_file_sha1', 'actual_c58_file_sha1', 'c58_file_sha1_match', 'c58_status', 'c58_diagnostic_conclusion', 'c58_next_step_recommendation', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); }
        }
        foreach ((array) ($result['c60_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) { $this->line('c60_'.$key.'='.$this->scalar($value)); }
        }
        if (($result['status'] ?? null) === 'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') {
            if ((bool) $this->option('progress')) { $this->line('C59 IS-only loss-cluster or branch/bucket redesign continuation completed'); }
            return 0;
        }
        if (($result['message'] ?? null) !== null) { $this->error((string) $result['message']); }
        return 1;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) { return $value ? '1' : '0'; }
        return $value === null ? '' : (string) $value;
    }
}
