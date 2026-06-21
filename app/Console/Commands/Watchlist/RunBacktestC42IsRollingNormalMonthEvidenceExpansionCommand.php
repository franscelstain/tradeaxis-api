<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService;
use Illuminate\Console\Command;

class RunBacktestC42IsRollingNormalMonthEvidenceExpansionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c42-is-rolling-normal-month-evidence-expansion
        {--c41-artifact= : Locked C41 review artifact path.}
        {--expected-c41-hash= : Expected locked C41 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--output= : Output C42 rolling/normal-month evidence expansion artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C42 IS rolling and normal-month warning evidence expansion without OOS proof or production promotion.';

    private WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService $service;

    public function __construct(WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C42 IS rolling/normal-month evidence expansion started');
        }

        $result = $this->service->execute(
            (string) ($this->option('c41-artifact') ?: WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_C41_ARTIFACT),
            (string) ($this->option('expected-c41-hash') ?: WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_EXPECTED_C41_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_OUTPUT_PATH),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c41_hash',
            'actual_c41_hash',
            'c41_hash_match',
            'c41_status',
            'c41_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c41_summary']) && is_array($result['source_c41_summary'])) {
            $summary = $result['source_c41_summary'];
            $this->line('source_c41_target_candidate='.$this->scalar($summary['target_candidate_code'] ?? null));
            $this->line('source_c41_overall_anti_overfit_result='.$this->scalar($summary['overall_anti_overfit_result'] ?? null));
            $this->line('source_c41_warning_layers_count='.$this->scalar($summary['warning_layers_count'] ?? null));
            $this->line('source_c41_failed_layers_count='.$this->scalar($summary['failed_layers_count'] ?? null));
            $this->line('source_c41_rolling_warning_windows='.$this->scalar($summary['rolling_warning_windows'] ?? null));
            $this->line('source_c41_non_bad_month_warning='.$this->scalar($summary['non_bad_month_warning'] ?? null));
        }

        if (isset($result['warning_explanation_summary']) && is_array($result['warning_explanation_summary'])) {
            $summary = $result['warning_explanation_summary'];
            $this->line('rolling_warning_explanation_result='.$this->scalar($summary['rolling_warning_explanation_result'] ?? null));
            $this->line('normal_month_warning_explanation_result='.$this->scalar($summary['normal_month_warning_explanation_result'] ?? null));
            $this->line('candidate_warning_explained='.$this->scalar($summary['candidate_warning_explained'] ?? null));
        }

        if (isset($result['guard_preservation_audit']) && is_array($result['guard_preservation_audit'])) {
            $guard = $result['guard_preservation_audit'];
            $this->line('c39_guard_preservation_result='.$this->scalar($guard['c39_guard_preservation_result'] ?? null));
            $this->line('coverage_guard_preserved='.$this->scalar($guard['coverage_guard_preserved'] ?? null));
            $this->line('branch_guard_preserved='.$this->scalar($guard['branch_guard_preserved'] ?? null));
        }

        if (isset($result['guard_refinement_feasibility']) && is_array($result['guard_refinement_feasibility'])) {
            $feasibility = $result['guard_refinement_feasibility'];
            $this->line('guard_refinement_feasibility_result='.$this->scalar($feasibility['feasibility_result'] ?? null));
            $this->line('safe_refinement_field_available='.$this->scalar($feasibility['safe_refinement_field_available'] ?? null));
            $this->line('safe_refinement_candidate_formed='.$this->scalar($feasibility['safe_refinement_candidate_formed'] ?? null));
        }

        if (isset($result['c42_decision_summary']) && is_array($result['c42_decision_summary'])) {
            $decision = $result['c42_decision_summary'];
            $this->line('c42_candidate_decision='.$this->scalar($decision['c42_candidate_decision'] ?? null));
            $this->line('direct_oos_proof_recommended='.$this->scalar($decision['direct_oos_proof_recommended'] ?? null));
            $this->line('oos_proof_unlocked='.$this->scalar($decision['oos_proof_unlocked'] ?? null));
            $this->line('requires_c43_evidence_expansion='.$this->scalar($decision['requires_c43_evidence_expansion'] ?? null));
        }

        if (($result['status'] ?? null) === 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C42 IS rolling/normal-month evidence expansion completed');
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
