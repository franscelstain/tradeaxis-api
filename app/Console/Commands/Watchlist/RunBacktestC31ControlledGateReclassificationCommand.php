<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC31ControlledGateReclassificationService;
use Illuminate\Console\Command;

class RunBacktestC31ControlledGateReclassificationCommand extends Command
{
    protected $signature = 'watchlist:backtest-c31-controlled-gate-reclassification
        {--c29-artifact= : Locked C29 failed OOS proof artifact path.}
        {--expected-c29-hash= : Expected locked C29 artifact stable hash.}
        {--c30-artifact= : Locked C30 attribution artifact path.}
        {--expected-c30-hash= : Expected locked C30 artifact stable hash.}
        {--output= : Output C31 controlled gate reclassification artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C31 controlled C29 gate reclassification against locked C29 and C30 artifacts without retuning or production promotion.';

    private WatchlistBacktestC31ControlledGateReclassificationService $service;

    public function __construct(WatchlistBacktestC31ControlledGateReclassificationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC31ControlledGateReclassificationService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C31 controlled gate reclassification started');
        }

        $result = $this->service->execute(
            (string) ($this->option('c29-artifact') ?: WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_C29_ARTIFACT),
            (string) ($this->option('expected-c29-hash') ?: WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C29_HASH),
            (string) ($this->option('c30-artifact') ?: WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_C30_ARTIFACT),
            (string) ($this->option('expected-c30-hash') ?: WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C30_HASH),
            (string) ($this->option('output') ?: WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_OUTPUT_PATH),
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
            'expected_c29_hash',
            'actual_c29_hash',
            'c29_hash_match',
            'c29_status',
            'expected_c30_hash',
            'actual_c30_hash',
            'c30_hash_match',
            'c30_status',
            'c30_attribution_verdict',
            'reclassification_conclusion',
            'controlled_proof_status',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c30_classification_summary']) && is_array($result['source_c30_classification_summary'])) {
            foreach ($result['source_c30_classification_summary'] as $key => $value) {
                $this->line('classification_'.$key.'='.$this->scalar($value));
            }
        }

        if (isset($result['separated_gate_summary']) && is_array($result['separated_gate_summary'])) {
            foreach ($result['separated_gate_summary'] as $key => $gate) {
                if (is_array($gate)) {
                    $this->line($key.'='.$this->scalar($gate['status'] ?? null));
                }
            }
        }

        if (($result['status'] ?? null) === 'C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C31 controlled gate reclassification completed');
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
