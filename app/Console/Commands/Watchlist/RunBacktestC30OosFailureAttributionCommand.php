<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC30OosFailureAttributionService;
use Illuminate\Console\Command;

class RunBacktestC30OosFailureAttributionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c30-oos-failure-attribution
        {--c29-artifact= : Locked C29 failed OOS proof artifact path.}
        {--expected-c29-hash= : Expected locked C29 artifact stable hash.}
        {--output= : Output C30 failure attribution artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C30 OOS failure attribution against the locked C29 failed artifact without retuning or production promotion.';

    private WatchlistBacktestC30OosFailureAttributionService $service;

    public function __construct(WatchlistBacktestC30OosFailureAttributionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC30OosFailureAttributionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C30 attribution started');
        }

        $result = $this->service->execute(
            (string) ($this->option('c29-artifact') ?: WatchlistBacktestC30OosFailureAttributionService::DEFAULT_C29_ARTIFACT),
            (string) ($this->option('expected-c29-hash') ?: WatchlistBacktestC30OosFailureAttributionService::DEFAULT_EXPECTED_C29_HASH),
            (string) ($this->option('output') ?: WatchlistBacktestC30OosFailureAttributionService::DEFAULT_OUTPUT_PATH),
            [
                'overwrite' => (bool) $this->option('overwrite'),
            ]
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'attribution_verdict',
            'expected_c29_hash',
            'actual_c29_hash',
            'c29_hash_match',
            'c29_status',
            'production_ready',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['classification_summary']) && is_array($result['classification_summary'])) {
            foreach ($result['classification_summary'] as $key => $value) {
                $this->line('classification_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C30_ATTRIBUTION_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C30 attribution completed');
            }
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
