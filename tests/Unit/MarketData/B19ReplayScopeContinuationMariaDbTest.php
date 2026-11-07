<?php

require_once __DIR__.'/B19ReplayIdentityMariaDbPipelineTest.php';
use Illuminate\Support\Facades\DB;

/** Diagnostic evidence only; no changed oracle, target publication or new fixture admission. */
class B19ReplayScopeContinuationMariaDbTest extends B19ReplayIdentityMariaDbPipelineTest
{
    private $diagnosticSequence = 0;
    private $readingDiagnostic = false;
    protected function setUp(): void
    {
        parent::setUp();
        DB::listen(function ($query) {
            if ($this->readingDiagnostic || ! preg_match('/^(insert into|update) `md_replay_daily_metrics`/i', $query->sql)) return;
            $this->readingDiagnostic = true;
            try {
                $this->retainDiagnostic('metric_'.(++$this->diagnosticSequence), [
                    'database' => DB::connection()->getDatabaseName(),
                    'connection_id' => DB::selectOne('SELECT CONNECTION_ID() AS id')->id,
                    'engine' => DB::selectOne('SELECT VERSION() AS version')->version,
                    'metrics' => DB::table('md_replay_daily_metrics')->orderBy('replay_id')->get(),
                ]);
            } finally { $this->readingDiagnostic = false; }
        });
    }
    private function retainDiagnostic(string $name, array $data): void
    {
        $dir = getenv('B19_E049_STATE_DIR');
        if (! $dir) return;
        if (! is_dir($dir)) mkdir($dir, 0775, true);
        file_put_contents($dir.'/REPLAY_DIAG_'.$name.'.json', json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
    protected function identityWorld(): array
    {
        $world = parent::identityWorld();
        $this->retainDiagnostic('target_before_verification', [
            'publication' => DB::table('eod_publications')->where('publication_id', $world['publication_id'])->first(),
            'run' => DB::table('eod_runs')->where('run_id', $world['run_id'])->first(),
            'pointer' => DB::table('eod_current_publication_pointer')->get(),
            'captures' => DB::table('md_run_input_captures')->where('run_id', $world['run_id'])->get(),
        ]);
        return $world;
    }
    protected function continueIdentityScope(): bool { return false; }
    public function test_current_publication_replay_prerequisite_retains_exact_mismatches(): void
    {
        parent::test_all_identity_paths_follow_independent_inputs_and_persisted_publication_binding();
    }
}
