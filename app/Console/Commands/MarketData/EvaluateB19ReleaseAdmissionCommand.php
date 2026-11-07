<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\B19ReleaseAdmissionEvaluator;
use Illuminate\Console\Command;

final class EvaluateB19ReleaseAdmissionCommand extends Command
{
    protected $signature = 'market-data:b19:release-admission {bundle} {--output_file=}';
    protected $description = 'Evaluate declared B19 release evidence without making a release-safety or deployment claim.';

    public function handle(B19ReleaseAdmissionEvaluator $evaluator): int
    {
        $bundlePath = realpath((string) $this->argument('bundle'));
        $root = realpath(base_path('storage/app/market-data/evidence/MD-B19-A001'));
        if ($bundlePath === false || $root === false
            || strpos($bundlePath, $root.DIRECTORY_SEPARATOR) !== 0 || ! is_file($bundlePath)) {
            $this->error('RELEASE_EVIDENCE_UNAVAILABLE: bundle must be a file inside the governed B19 evidence root.');
            return 1;
        }
        $bundle = json_decode((string) file_get_contents($bundlePath), true);
        if (! is_array($bundle)) {
            $this->error('RELEASE_EVIDENCE_INVALID: bundle is not a JSON object.');
            return 1;
        }
        $head = trim((string) shell_exec('git -C '.escapeshellarg(base_path()).' rev-parse HEAD 2>NUL'));
        if (! preg_match('/^[a-f0-9]{40}$/i', $head)) {
            $this->error('RELEASE_BUILD_IDENTITY_UNAVAILABLE');
            return 1;
        }
        $dirty = trim((string) shell_exec('git -C '.escapeshellarg(base_path()).' status --porcelain --untracked-files=normal 2>NUL'));
        if ($dirty !== '') {
            $bundle['unresolved_dependencies'] = array_values(array_unique(array_merge(
                (array) ($bundle['unresolved_dependencies'] ?? []), ['UNCOMMITTED_SOURCE_STATE']
            )));
        }
        $decision = $evaluator->evaluate($bundle, $head, $root);
        $json = json_encode($decision, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
        $this->line($json);
        $output = (string) $this->option('output_file');
        if ($output !== '') {
            $parent = realpath(dirname($output));
            if ($parent === false || strpos($parent.DIRECTORY_SEPARATOR, $root.DIRECTORY_SEPARATOR) !== 0) {
                $this->error('RELEASE_OUTPUT_PATH_INVALID: output must remain inside the governed B19 evidence root.');
                return 1;
            }
            if (file_exists($output)) {
                $this->error('RELEASE_OUTPUT_EXISTS: governed evidence must be append-only.');
                return 1;
            }
            file_put_contents($output, $json);
        }
        return $decision['state'] === 'DECLARED_CONDITIONS_MET' ? 0 : 1;
    }
}
