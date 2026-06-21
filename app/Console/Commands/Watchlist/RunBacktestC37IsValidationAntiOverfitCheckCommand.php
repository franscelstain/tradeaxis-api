<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC37IsValidationAntiOverfitCheckService;
use Illuminate\Console\Command;

class RunBacktestC37IsValidationAntiOverfitCheckCommand extends Command
{
    protected $signature = 'watchlist:backtest-c37-is-validation-anti-overfit-check
        {--c36-artifact= : Locked C36 IS-controlled redesign candidate formation artifact path.}
        {--expected-c36-hash= : Expected locked C36 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--is-evidence-artifact= : Optional C36-linked IS diagnostic artifact path override.}
        {--output= : Output C37 IS validation and anti-overfit artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C37 IS validation and anti-overfit checks for the locked C36 candidate without OOS proof or production promotion.';

    private WatchlistBacktestC37IsValidationAntiOverfitCheckService $service;

    public function __construct(WatchlistBacktestC37IsValidationAntiOverfitCheckService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC37IsValidationAntiOverfitCheckService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C37 IS validation and anti-overfit check started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('is-evidence-artifact')) {
            $options['is_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) ($this->option('c36-artifact') ?: WatchlistBacktestC37IsValidationAntiOverfitCheckService::DEFAULT_C36_ARTIFACT),
            (string) ($this->option('expected-c36-hash') ?: WatchlistBacktestC37IsValidationAntiOverfitCheckService::DEFAULT_EXPECTED_C36_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC37IsValidationAntiOverfitCheckService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC37IsValidationAntiOverfitCheckService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC37IsValidationAntiOverfitCheckService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c36_hash',
            'actual_c36_hash',
            'c36_hash_match',
            'c36_status',
            'c36_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c36_summary']) && is_array($result['source_c36_summary'])) {
            $summary = $result['source_c36_summary'];
            $this->line('source_c36_candidate_formed='.$this->scalar($summary['candidate_formed'] ?? null));
            $this->line('source_c36_best_is_candidate_code='.$this->scalar($summary['best_is_candidate_code'] ?? null));
            $this->line('source_c36_best_is_candidate_is_not_production='.$this->scalar($summary['best_is_candidate_is_not_production'] ?? null));
            $this->line('source_c36_evidence='.$this->scalar($summary['source_evidence'] ?? null));
        }

        if (isset($result['validation_target']) && is_array($result['validation_target'])) {
            $target = $result['validation_target'];
            $this->line('baseline_candidate_code='.$this->scalar($target['baseline_candidate_code'] ?? null));
            $this->line('target_candidate_code='.$this->scalar($target['target_candidate_code'] ?? null));
            $this->line('target_candidate_is_not_production='.$this->scalar($target['target_candidate_is_not_production'] ?? null));
        }

        if (isset($result['validation_summary']) && is_array($result['validation_summary'])) {
            $summary = $result['validation_summary'];
            $this->line('total_validation_layers='.$this->scalar($summary['total_validation_layers'] ?? null));
            $this->line('passed_layers='.$this->scalar($summary['passed_layers'] ?? null));
            $this->line('warning_layers='.$this->scalar($summary['warning_layers'] ?? null));
            $this->line('failed_layers='.$this->scalar($summary['failed_layers'] ?? null));
            $this->line('not_evaluable_layers='.$this->scalar($summary['not_evaluable_layers'] ?? null));
            $this->line('overall_anti_overfit_result='.$this->scalar($summary['overall_anti_overfit_result'] ?? null));
            $this->line('candidate_c37_decision='.$this->scalar($summary['candidate_c37_decision'] ?? null));
        }

        if (($result['status'] ?? null) === 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C37 IS validation and anti-overfit check completed');
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
