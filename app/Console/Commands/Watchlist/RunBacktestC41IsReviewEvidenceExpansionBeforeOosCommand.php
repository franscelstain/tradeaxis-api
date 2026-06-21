<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService;
use Illuminate\Console\Command;

class RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand extends Command
{
    protected $signature = 'watchlist:backtest-c41-is-review-or-evidence-expansion-before-oos
        {--c40-artifact= : Locked C40 warning artifact path.}
        {--expected-c40-hash= : Expected locked C40 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--output= : Output C41 review/evidence expansion artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C41 IS review/evidence expansion gate before OOS without OOS proof or production promotion.';

    private WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService $service;

    public function __construct(WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C41 IS review/evidence expansion before OOS started');
        }

        $result = $this->service->execute(
            (string) ($this->option('c40-artifact') ?: WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService::DEFAULT_C40_ARTIFACT),
            (string) ($this->option('expected-c40-hash') ?: WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService::DEFAULT_EXPECTED_C40_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService::DEFAULT_OUTPUT_PATH),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c40_hash',
            'actual_c40_hash',
            'c40_hash_match',
            'c40_status',
            'c40_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c40_summary']) && is_array($result['source_c40_summary'])) {
            $summary = $result['source_c40_summary'];
            $this->line('source_c40_target_candidate='.$this->scalar($summary['target_candidate_code'] ?? null));
            $this->line('source_c40_overall_anti_overfit_result='.$this->scalar($summary['overall_anti_overfit_result'] ?? null));
            $this->line('source_c40_warning_layers='.$this->scalar($summary['warning_layers'] ?? null));
            $this->line('source_c40_failed_layers='.$this->scalar($summary['failed_layers'] ?? null));
            $this->line('source_c40_not_evaluable_layers='.$this->scalar($summary['not_evaluable_layers'] ?? null));
        }

        if (isset($result['warning_layer_review']) && is_array($result['warning_layer_review'])) {
            $review = $result['warning_layer_review'];
            $this->line('c41_warning_layer_count='.$this->scalar($review['warning_layer_count'] ?? null));
            $this->line('c41_rolling_warning_windows='.$this->scalar($review['rolling_warning_review']['warning_or_fail_window_count'] ?? null));
            $this->line('c41_non_bad_month_warning='.$this->scalar($review['non_bad_month_warning_review']['needs_review'] ?? null));
        }

        if (isset($result['review_decision_summary']) && is_array($result['review_decision_summary'])) {
            $decision = $result['review_decision_summary'];
            $this->line('c41_candidate_decision='.$this->scalar($decision['candidate_decision'] ?? null));
            $this->line('c41_direct_oos_proof_recommended='.$this->scalar($decision['direct_oos_proof_recommended'] ?? null));
            $this->line('c41_oos_proof_unlocked='.$this->scalar($decision['oos_proof_unlocked'] ?? null));
            $this->line('c41_evidence_requirements_count='.$this->scalar($decision['evidence_requirements_count'] ?? null));
        }

        if (($result['status'] ?? null) === 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C41 IS review/evidence expansion before OOS completed');
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
