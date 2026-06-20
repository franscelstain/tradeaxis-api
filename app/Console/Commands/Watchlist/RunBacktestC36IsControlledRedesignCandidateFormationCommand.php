<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC36IsControlledRedesignCandidateFormationService;
use Illuminate\Console\Command;

class RunBacktestC36IsControlledRedesignCandidateFormationCommand extends Command
{
    protected $signature = 'watchlist:backtest-c36-is-controlled-redesign-candidate-formation
        {--c35-artifact= : Locked C35 IS robustness redesign diagnostic artifact path.}
        {--expected-c35-hash= : Expected locked C35 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--is-evidence-artifact= : Optional C35-linked IS diagnostic artifact path override.}
        {--output= : Output C36 IS-controlled redesign candidate formation artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C36 IS-controlled redesign candidate formation from C35 hypotheses without OOS tuning, OOS proof, or production promotion.';

    private WatchlistBacktestC36IsControlledRedesignCandidateFormationService $service;

    public function __construct(WatchlistBacktestC36IsControlledRedesignCandidateFormationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC36IsControlledRedesignCandidateFormationService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C36 IS-controlled redesign candidate formation started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('is-evidence-artifact')) {
            $options['is_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) ($this->option('c35-artifact') ?: WatchlistBacktestC36IsControlledRedesignCandidateFormationService::DEFAULT_C35_ARTIFACT),
            (string) ($this->option('expected-c35-hash') ?: WatchlistBacktestC36IsControlledRedesignCandidateFormationService::DEFAULT_EXPECTED_C35_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC36IsControlledRedesignCandidateFormationService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC36IsControlledRedesignCandidateFormationService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC36IsControlledRedesignCandidateFormationService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c35_hash',
            'actual_c35_hash',
            'c35_hash_match',
            'c35_status',
            'c35_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c35_summary']) && is_array($result['source_c35_summary'])) {
            $summary = $result['source_c35_summary'];
            $this->line('source_c35_g21_rows='.$this->scalar($summary['g21_rows'] ?? null));
            $this->line('source_c35_g16_rows='.$this->scalar($summary['g16_rows'] ?? null));
            $this->line('source_c35_evidence='.$this->scalar($summary['source_evidence'] ?? null));
        }

        if (isset($result['candidate_summary']) && is_array($result['candidate_summary'])) {
            $summary = $result['candidate_summary'];
            $this->line('candidate_total='.$this->scalar($summary['total_candidates'] ?? null));
            $this->line('candidate_evaluated='.$this->scalar($summary['evaluated_candidates'] ?? null));
            $this->line('candidate_not_evaluable='.$this->scalar($summary['not_evaluable_candidates'] ?? null));
            $this->line('candidate_formed='.$this->scalar($summary['candidate_formed'] ?? null));
            $this->line('best_is_candidate_code='.$this->scalar($summary['best_is_candidate_code'] ?? null));
        }

        if (($result['status'] ?? null) === 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C36 IS-controlled redesign candidate formation completed');
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
