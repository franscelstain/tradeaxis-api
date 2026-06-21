<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c38-is-redesign-evidence-expansion-diagnostic
        {--c37-artifact= : Locked C37 IS validation and anti-overfit artifact path.}
        {--expected-c37-hash= : Expected locked C37 artifact stable hash.}
        {--from= : IS window start date.}
        {--to= : IS window end date.}
        {--is-evidence-artifact= : Optional C37-linked IS diagnostic artifact path override.}
        {--output= : Output C38 IS redesign/evidence expansion diagnostic artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C38 IS redesign/evidence expansion diagnostic from failed C37 anti-overfit evidence without OOS proof or production promotion.';

    private WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService $service;

    public function __construct(WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C38 IS redesign/evidence expansion diagnostic started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('is-evidence-artifact')) {
            $options['is_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) ($this->option('c37-artifact') ?: WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService::DEFAULT_C37_ARTIFACT),
            (string) ($this->option('expected-c37-hash') ?: WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService::DEFAULT_EXPECTED_C37_HASH),
            (string) ($this->option('from') ?: WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService::DEFAULT_FROM),
            (string) ($this->option('to') ?: WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService::DEFAULT_TO),
            (string) ($this->option('output') ?: WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c37_hash',
            'actual_c37_hash',
            'c37_hash_match',
            'c37_status',
            'c37_diagnostic_conclusion',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['source_c37_summary']) && is_array($result['source_c37_summary'])) {
            $summary = $result['source_c37_summary'];
            $this->line('source_c37_overall_anti_overfit_result='.$this->scalar($summary['overall_anti_overfit_result'] ?? null));
            $this->line('source_c37_candidate_decision='.$this->scalar($summary['candidate_c37_decision'] ?? null));
            $this->line('source_c37_evidence='.$this->scalar($summary['source_evidence'] ?? null));
        }

        if (isset($result['c38_decision_summary']) && is_array($result['c38_decision_summary'])) {
            $summary = $result['c38_decision_summary'];
            $this->line('c38_requirements_count='.$this->scalar($summary['requirements_count'] ?? null));
            $this->line('c38_candidate_decision='.$this->scalar($summary['candidate_c38_decision'] ?? null));
            $this->line('c38_direct_oos_proof_recommended='.$this->scalar($summary['direct_oos_proof_recommended'] ?? null));
            $this->line('c38_new_candidate_selected='.$this->scalar($summary['new_candidate_selected'] ?? null));
        }

        if (($result['status'] ?? null) === 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C38 IS redesign/evidence expansion diagnostic completed');
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
