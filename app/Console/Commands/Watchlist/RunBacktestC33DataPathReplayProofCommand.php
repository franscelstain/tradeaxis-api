<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC33DataPathReplayProofService;
use Illuminate\Console\Command;

class RunBacktestC33DataPathReplayProofCommand extends Command
{
    protected $signature = 'watchlist:backtest-c33-data-path-replay-proof
        {--c32-artifact= : Locked C32 data-path and bad-month diagnostic artifact path.}
        {--expected-c32-hash= : Expected locked C32 artifact stable hash.}
        {--output= : Output C33 data-path replay proof artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C33 read-only data-path replay proof for C32 missing D1-D5 raw OHLC rows without tuning or production promotion.';

    private WatchlistBacktestC33DataPathReplayProofService $service;

    public function __construct(WatchlistBacktestC33DataPathReplayProofService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC33DataPathReplayProofService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C33 data-path replay proof started');
        }

        $result = $this->service->execute(
            (string) ($this->option('c32-artifact') ?: WatchlistBacktestC33DataPathReplayProofService::DEFAULT_C32_ARTIFACT),
            (string) ($this->option('expected-c32-hash') ?: WatchlistBacktestC33DataPathReplayProofService::DEFAULT_EXPECTED_C32_HASH),
            (string) ($this->option('output') ?: WatchlistBacktestC33DataPathReplayProofService::DEFAULT_OUTPUT_PATH),
            [
                'overwrite' => (bool) $this->option('overwrite'),
            ]
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c32_hash',
            'actual_c32_hash',
            'c32_hash_match',
            'c32_status',
            'c32_diagnostic_conclusion',
            'c32_data_path_remediation_status',
            'data_path_replay_status',
            'data_completeness_gate_after_replay',
            'replay_row_count',
            'replay_pass_count',
            'replay_fail_count',
            'replay_blocked_count',
            'diagnostic_conclusion',
            'next_step',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (($result['status'] ?? null) === 'C33_DATA_PATH_REPLAY_PROOF_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C33 data-path replay proof completed');
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
