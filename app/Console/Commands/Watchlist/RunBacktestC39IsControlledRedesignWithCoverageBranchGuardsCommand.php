<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService;
use Illuminate\Console\Command;

class RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand extends Command
{
    protected $signature = 'watchlist:backtest-c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards
        {--c38-artifact= : Locked C38 redesign/evidence expansion diagnostic artifact path.}
        {--expected-c38-hash= : Expected locked C38 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--is-evidence-artifact= : Optional C38-linked IS diagnostic artifact path override.}
        {--output= : Output C39 guarded IS candidate formation artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C39 IS-controlled redesign with month coverage and branch diversification guards without OOS proof or production promotion.';

    private WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService $service;

    public function __construct(WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C39 guarded IS candidate formation started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('is-evidence-artifact')) {
            $options['is_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) ($this->option('c38-artifact') ?: WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService::DEFAULT_C38_ARTIFACT),
            (string) ($this->option('expected-c38-hash') ?: WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService::DEFAULT_EXPECTED_C38_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c38_hash',
            'actual_c38_hash',
            'c38_hash_match',
            'c38_status',
            'c38_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c38_summary']) && is_array($result['source_c38_summary'])) {
            $summary = $result['source_c38_summary'];
            $this->line('source_c38_c37_anti_overfit_result='.$this->scalar($summary['c37_overall_anti_overfit_result'] ?? null));
            $this->line('source_c38_zero_pick_months='.implode(',', $summary['zero_pick_months'] ?? []));
            $this->line('source_c38_evidence='.$this->scalar($summary['source_evidence'] ?? null));
        }

        if (isset($result['candidate_summary']) && is_array($result['candidate_summary'])) {
            $summary = $result['candidate_summary'];
            $this->line('c39_candidate_formed='.$this->scalar($summary['candidate_formed'] ?? null));
            $this->line('c39_best_is_candidate_code='.$this->scalar($summary['best_is_candidate_code'] ?? null));
            $this->line('c39_best_candidate_requires_C40_validation='.$this->scalar($summary['best_candidate_requires_C40_validation'] ?? null));
        }

        if (isset($result['guard_validation_summary']) && is_array($result['guard_validation_summary'])) {
            $summary = $result['guard_validation_summary'];
            $this->line('c39_candidate_with_all_guards_count='.$this->scalar($summary['candidate_with_all_guards_count'] ?? null));
            $this->line('c39_best_candidate_top_branch_share='.$this->scalar($summary['best_candidate_top_branch_share'] ?? null));
            $this->line('c39_best_candidate_zero_pick_month_count='.$this->scalar($summary['best_candidate_zero_pick_month_count'] ?? null));
        }

        if (($result['status'] ?? null) === 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C39 guarded IS candidate formation completed');
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
