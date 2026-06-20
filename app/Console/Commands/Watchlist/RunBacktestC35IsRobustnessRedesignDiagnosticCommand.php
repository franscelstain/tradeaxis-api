<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC35IsRobustnessRedesignDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC35IsRobustnessRedesignDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c35-is-robustness-redesign-diagnostic
        {--c34-artifact= : Locked C34 bad-month robustness diagnostic artifact path.}
        {--expected-c34-hash= : Expected locked C34 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--is-evidence-artifact= : Optional IS diagnostic artifact path override.}
        {--output= : Output C35 IS robustness redesign diagnostic artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C35 IS-only robustness redesign diagnostic for G21/G16 without OOS tuning, OOS proof, or production promotion.';

    private WatchlistBacktestC35IsRobustnessRedesignDiagnosticService $service;

    public function __construct(WatchlistBacktestC35IsRobustnessRedesignDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C35 IS-only robustness redesign diagnostic started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('is-evidence-artifact')) {
            $options['is_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) ($this->option('c34-artifact') ?: WatchlistBacktestC35IsRobustnessRedesignDiagnosticService::DEFAULT_C34_ARTIFACT),
            (string) ($this->option('expected-c34-hash') ?: WatchlistBacktestC35IsRobustnessRedesignDiagnosticService::DEFAULT_EXPECTED_C34_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC35IsRobustnessRedesignDiagnosticService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC35IsRobustnessRedesignDiagnosticService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC35IsRobustnessRedesignDiagnosticService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c34_hash',
            'actual_c34_hash',
            'c34_hash_match',
            'c34_status',
            'c34_final_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['is_evidence_summary']) && is_array($result['is_evidence_summary'])) {
            $summary = $result['is_evidence_summary'];
            $this->line('is_evidence_total_rows='.$this->scalar($summary['total_rows'] ?? null));
            $this->line('is_evidence_g21_rows='.$this->scalar($summary['g21_rows'] ?? null));
            $this->line('is_evidence_g16_rows='.$this->scalar($summary['g16_rows'] ?? null));
        }

        if (($result['status'] ?? null) === 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C35 IS-only robustness redesign diagnostic completed');
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
