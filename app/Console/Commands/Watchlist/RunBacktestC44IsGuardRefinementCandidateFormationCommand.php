<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC44IsGuardRefinementCandidateFormationService;
use Illuminate\Console\Command;

class RunBacktestC44IsGuardRefinementCandidateFormationCommand extends Command
{
    protected $signature = 'watchlist:backtest-c44-is-guard-refinement-candidate-formation
        {--c43-artifact=storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json}
        {--expected-c43-hash=41a91ba0447dcf6c0493e1bb27bce6df08fd3490}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--is-evidence-artifact=}
        {--output=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Form C44 IS-only fixed-quota guard refinement candidates; never runs OOS proof or production promotion.';

    private WatchlistBacktestC44IsGuardRefinementCandidateFormationService $service;

    public function __construct(WatchlistBacktestC44IsGuardRefinementCandidateFormationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC44IsGuardRefinementCandidateFormationService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C44 IS guard refinement candidate formation started'); }
        $options = ['overwrite' => (bool) $this->option('overwrite')];
        if ($this->option('is-evidence-artifact')) { $options['source_evidence_artifact'] = (string) $this->option('is-evidence-artifact'); }
        $result = $this->service->execute((string) $this->option('c43-artifact'), (string) $this->option('expected-c43-hash'), (string) $this->option('from'), (string) $this->option('to'), (string) $this->option('output'), $options);
        foreach (['status','reason_code','artifact_path','artifact_hash','production_ready','expected_c43_hash','actual_c43_hash','c43_hash_match','c43_status','c43_diagnostic_conclusion','diagnostic_conclusion','next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); }
        }
        foreach (($result['candidate_summary'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line($key.'='.$this->scalar($value)); } }
        foreach (($result['guard_preservation_summary'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line($key.'='.$this->scalar($value)); } }
        if (($result['status'] ?? null) === 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED') {
            if ((bool) $this->option('progress')) { $this->line('C44 IS guard refinement candidate formation completed'); }
            return 0;
        }
        if (($result['message'] ?? null) !== null) { $this->error((string) $result['message']); }
        return 1;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) { return $value ? '1' : '0'; }
        return $value === null ? '' : (string) $value;
    }
}
