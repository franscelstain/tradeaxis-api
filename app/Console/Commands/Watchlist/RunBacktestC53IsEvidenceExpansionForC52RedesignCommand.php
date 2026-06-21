<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService;
use Illuminate\Console\Command;

class RunBacktestC53IsEvidenceExpansionForC52RedesignCommand extends Command
{
    protected $signature = 'watchlist:backtest-c53-is-evidence-expansion-for-c52-redesign
        {--c52-artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json}
        {--expected-c52-hash=5dbe51c9d18b175e65cddb60336baf43d6833b72}
        {--expected-c52-file-sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Expand locked C52 IS evidence across rolling, LOO, adverse-month, and regime layers without OOS proof or candidate reselection.';

    private WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService $service;

    public function __construct(WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C53 IS evidence expansion for C52 redesign started'); }
        $result = $this->service->execute(
            (string) $this->option('c52-artifact'), (string) $this->option('expected-c52-hash'), (string) $this->option('expected-c52-file-sha1'),
            (string) $this->option('from'), (string) $this->option('to'), (string) $this->option('output'), ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c52_hash', 'actual_c52_hash', 'c52_hash_match', 'expected_c52_file_sha1', 'actual_c52_file_sha1', 'c52_file_sha1_match', 'c52_status', 'c52_diagnostic_conclusion', 'c52_next_step_recommendation', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) { if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); } }
        foreach ((array) ($result['rolling_evidence_expansion_summary'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('rolling_'.$key.'='.$this->scalar($value)); } }
        foreach ((array) ($result['c54_readiness_decision'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('c54_'.$key.'='.$this->scalar($value)); } }
        if (in_array($result['status'] ?? null, ['C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED', 'C53_EVIDENCE_COHORT_NOT_EVALUABLE'], true)) {
            if ((bool) $this->option('progress')) { $this->line('C53 IS evidence expansion for C52 redesign completed'); }
            return 0;
        }
        if (($result['message'] ?? null) !== null) { $this->error((string) $result['message']); }
        return 1;
    }

    private function scalar($value): string { if (is_bool($value)) { return $value ? '1' : '0'; } return $value === null ? '' : (string) $value; }
}
