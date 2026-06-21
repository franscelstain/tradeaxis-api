<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService;
use Illuminate\Console\Command;

class RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand extends Command
{
    protected $signature = 'watchlist:backtest-c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate
        {--c39-artifact= : Locked C39 guarded candidate artifact path.}
        {--expected-c39-hash= : Expected locked C39 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--is-evidence-artifact= : Optional C39-linked IS diagnostic artifact path override.}
        {--output= : Output C40 IS validation and anti-overfit artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C40 IS validation and anti-overfit check for the locked C39 guarded candidate without OOS proof or production promotion.';

    private WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService $service;

    public function __construct(WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C40 IS validation and anti-overfit check started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('is-evidence-artifact')) {
            $options['is_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) ($this->option('c39-artifact') ?: WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService::DEFAULT_C39_ARTIFACT),
            (string) ($this->option('expected-c39-hash') ?: WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService::DEFAULT_EXPECTED_C39_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c39_hash',
            'actual_c39_hash',
            'c39_hash_match',
            'c39_status',
            'c39_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c39_summary']) && is_array($result['source_c39_summary'])) {
            $summary = $result['source_c39_summary'];
            $this->line('source_c39_best_candidate='.$this->scalar($summary['best_is_candidate_code'] ?? null));
            $this->line('source_c39_evidence='.$this->scalar($summary['source_evidence'] ?? null));
        }

        if (isset($result['validation_summary']) && is_array($result['validation_summary'])) {
            $summary = $result['validation_summary'];
            $this->line('c40_overall_anti_overfit_result='.$this->scalar($summary['overall_anti_overfit_result'] ?? null));
            $this->line('c40_passed_layers='.$this->scalar($summary['passed_layers'] ?? null));
            $this->line('c40_warning_layers='.$this->scalar($summary['warning_layers'] ?? null));
            $this->line('c40_failed_layers='.$this->scalar($summary['failed_layers'] ?? null));
            $this->line('c40_not_evaluable_layers='.$this->scalar($summary['not_evaluable_layers'] ?? null));
            $this->line('c40_candidate_decision='.$this->scalar($summary['candidate_c40_decision'] ?? null));
        }

        if (($result['status'] ?? null) === 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C40 IS validation and anti-overfit check completed');
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
