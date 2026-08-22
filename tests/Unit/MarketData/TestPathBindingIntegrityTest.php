<?php

use PHPUnit\Framework\TestCase;

/**
 * Every documentation path a test binds to must resolve, and no test may read a sealed historical
 * extract as if it were current proof.
 *
 * `D-MD-20260820-02` decomposed 26 composite audit documents and removed the physical originals.
 * 24 test files kept asserting against the removed paths and stayed red for an entire verification
 * epoch — 67 failures and 4 errors that everyone learned to scroll past. `MD-B03-A002` removed
 * those 71 bindings; this guard is what stops the class returning.
 *
 * Two invariants, and they are not the same:
 *
 *   RESOLVES     a `docs/market_data/**` path written into a test must exist. A dead path makes a
 *                guard fail for a reason that has nothing to do with what it guards.
 *   NOT_SEALED   no test may read an `LX-MD-*` extract under `records/history/archive/semantic/`.
 *                `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` gives pre-epoch statements zero
 *                current-verification effect, and `F-MD-B00-A001-001` rules that repointing a test
 *                at those extracts smuggles inherited PASS into the current epoch.
 *
 * Mentioning `records/history/` is not reading it. Seven suites name that prefix to *exclude* it
 * from a corpus scan, which is the contract being honoured. The second invariant therefore targets
 * the extract-file pattern, which exists only to be read, rather than the directory name.
 */
class TestPathBindingIntegrityTest extends TestCase
{
    /** Assembled at runtime so the pattern is not itself a literal in the scanned corpus. */
    private function pathPattern(): string
    {
        return '#[\'"](docs/'.'market_data/[A-Za-z0-9_/.-]+\.[a-z]{2,4})[\'"]#';
    }

    /** Likewise: the sealed-extract pattern must not be readable as a sealed-extract reference. */
    private function sealedPattern(): string
    {
        return '#records/history/'.'archive/semantic/|[\'"]LX-'.'MD-\d+#';
    }

    /**
     * Executable source only. A docblock naming a removed path is describing the defect, not binding
     * to it, and reading comments as bindings is the mistake this stage has made six times.
     */
    private function stripComments(string $source): string
    {
        $out = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    continue;
                }
                $out .= $token[1];

                continue;
            }
            $out .= $token;
        }

        return $out;
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * Every executable tree that may bind to a documentation path.
     *
     * `tests/` alone was not enough. `MD-B03-A001` rebound 79 dead paths across `app/`, `database/`,
     * `config/`, and `tests/`; only the last was guarded, so three of the four surfaces it repaired
     * could have regressed without anything noticing. `MD-DEP-0006` turns on whether the risk that
     * attempt created is controlled, so the guard now covers what that attempt actually touched.
     *
     * @return array<int,string>
     */
    private function executableRoots(): array
    {
        return ['/tests', '/app', '/config', '/database'];
    }

    /** @return array<int,string> */
    private function testFiles(): array
    {
        $files = [];
        foreach ($this->executableRoots() as $root) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().$root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                $files[] = $file->getPathname();
            }
        }
        }
        sort($files);

        return $files;
    }

    /**
     * Quoted `docs/market_data/**` literals, with the file they appear in.
     *
     * @return array<int,array{0:string,1:string}>
     */
    private function boundPaths(): array
    {
        $found = [];
        foreach ($this->testFiles() as $file) {
            $source = $this->stripComments((string) file_get_contents($file));
            if (preg_match_all($this->pathPattern(), $source, $m)) {
                foreach ($m[1] as $path) {
                    $found[] = [str_replace($this->root().DIRECTORY_SEPARATOR, '', $file), $path];
                }
            }
        }

        return $found;
    }

    public function test_every_documentation_path_a_test_binds_to_resolves(): void
    {
        $bound = $this->boundPaths();

        $this->assertGreaterThan(15, count($bound), 'the path scan must reach the test corpus');
        $this->assertGreaterThan(100, count($this->testFiles()), 'the file scan must reach the test corpus');

        $dead = [];
        foreach ($bound as [$file, $path]) {
            if (! file_exists($this->root().'/'.$path)) {
                $dead[] = $file.' :: '.$path;
            }
        }
        sort($dead);

        $this->assertSame([], array_values(array_unique($dead)), 'a test binds to a documentation path that does not exist');
    }

    public function test_no_test_reads_a_sealed_historical_extract_as_current_proof(): void
    {
        $offenders = [];
        $scanned = 0;
        foreach ($this->testFiles() as $file) {
            $scanned++;
            $source = $this->stripComments((string) file_get_contents($file));
            if (preg_match($this->sealedPattern(), $source, $m)) {
                $offenders[] = str_replace($this->root().DIRECTORY_SEPARATOR, '', $file).' :: '.$m[0];
            }
        }

        $this->assertGreaterThan(100, $scanned, 'the sealed-extract scan must reach the test corpus');
        $this->assertSame([], $offenders, 'a sealed historical extract may not be read as current proof');
    }

    /**
     * Both patterns must be able to fire, or a clean corpus is indistinguishable from a broken scan.
     */
    public function test_the_guard_can_detect_the_defect_it_exists_for(): void
    {
        // Every probe is assembled at runtime. A literal dead path or a literal extract name written
        // here would be collected by the scans above and reported as a violation of the rule this
        // method exists to verify — the sixth time in this stage that a guard has flagged the
        // surface obeying it. Assembling the probes is the fix; a self-exemption is not, because a
        // carve-out weakens the scan for every other file.
        $deadPath = 'docs/market_'.'data/audit/PRODUCTION_VALIDATION_'.'INVENTORY.md';
        $this->assertFileDoesNotExist($this->root().'/'.$deadPath, 'the removed inventory must stay removed');
        $this->assertSame(
            1,
            preg_match($this->pathPattern(), "'".$deadPath."'"),
            'the path pattern must match a quoted documentation literal'
        );

        $extract = 'records/history/'.'archive/semantic/'.'LX-MD'.'-0031-CTX-01_X.md';
        $this->assertSame(
            1,
            preg_match($this->sealedPattern(), "'".$extract."'"),
            'the sealed-extract pattern must match an extract path'
        );
        $this->assertSame(
            0,
            preg_match($this->sealedPattern(), "if (strpos(\$rel, 'records/history/') !== false) { continue; }"),
            'excluding the history directory is honouring the contract, not breaking it'
        );
    }
}
