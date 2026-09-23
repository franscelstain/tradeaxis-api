<?php

use PHPUnit\Framework\TestCase;

/** D005/Q6: execute the actual gates and observe all document writes, regardless of write API. */
class GovernanceGateReadOnlyExecutionTest extends TestCase
{
    private static $fixtureRoot;
    private static $md;

    public static function setUpBeforeClass(): void
    {
        self::$fixtureRoot = sys_get_temp_dir().'/md_read_only_'.uniqid('', true);
        self::$md = self::$fixtureRoot.'/docs/market_data';
        mkdir(self::$md, 0777, true);
        $source = dirname(__DIR__, 3).'/docs/market_data';
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($it as $file) {
            $target = self::$md.'/'.$it->getSubPathName();
            $file->isDir() ? mkdir($target, 0777, true) : copy($file->getPathname(), $target);
        }
        // Sentinels remain valid JSON and distinguish an overwrite from the old report's content.
        foreach (['MD_DOCUMENTATION_INTEGRITY_GATE_LATEST.json', 'MD_RELATIONSHIP_INTEGRITY_GATE_LATEST.json'] as $name) {
            $path = self::$md.'/records/evidence/'.$name;
            $data = json_decode(file_get_contents($path), true);
            $data['immutable_write_sentinel'] = 'issued-record-must-survive-'.$name;
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT).PHP_EOL);
        }
    }

    public static function tearDownAfterClass(): void
    {
        $root = realpath(self::$fixtureRoot);
        if ($root === false || strpos(str_replace('\\', '/', $root), str_replace('\\', '/', realpath(sys_get_temp_dir())).'/md_read_only_') !== 0) {
            throw new RuntimeException('UNSAFE_TEST_CLEANUP_PATH');
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($root);
    }

    public function gateCases(): array
    {
        return [
            'documentation pass' => ['MarketDataDocumentationIntegrityGate', [], 'PASS', 0, null],
            'documentation fail' => ['MarketDataDocumentationIntegrityGate', [], 'FAIL', 1, 'unregistered'],
            'relationship pass' => ['MarketDataRelationshipIntegrityGate', [], 'PASS', 0, null],
            'relationship fail' => ['MarketDataRelationshipIntegrityGate', [], 'FAIL', 1, 'work-id'],
            'classification check' => ['MarketDataClassificationConsistencyGate', [], 'PASS', 0, null],
            'applicability check' => ['MarketDataTraceabilityApplicabilityGate', ['--check'], 'PASS', 0, null],
            'check versus normalization' => ['MarketDataTraceabilityApplicabilityGate', ['--check', '--apply-normalization'], 'FAIL', 2, null],
            'check versus binding' => ['MarketDataTraceabilityApplicabilityGate', ['--check', '--bind-a012-evidence'], 'FAIL', 2, null],
        ];
    }

    /** @dataProvider gateCases */
    public function test_gate_execution_preserves_every_document(string $gate, array $flags, string $status, int $exit, ?string $defect): void
    {
        $restore = [];
        if ($defect === 'unregistered') {
            $path = self::$md.'/records/evidence/UNREGISTERED_READ_ONLY_CONTROL.txt';
            file_put_contents($path, 'the documentation gate must reject this file');
            $restore[$path] = null;
        } elseif ($defect === 'work-id') {
            $path = self::$md.'/records/WORK_RECORD_REGISTRY.csv';
            $bytes = file_get_contents($path);
            $anchor = 'MD-B18-A002-BL001,BASELINE_LOCK,MD-B18,MD-B18-A002,MD-B18-A002,';
            $this->assertSame(1, substr_count($bytes, $anchor));
            $restore[$path] = $bytes;
            file_put_contents($path, str_replace($anchor, 'MD-B18-A002-BL001,BASELINE_LOCK,MD-B18,MD-B18-A002,WRONG_WORK_ID,', $bytes));
        }
        try {
            $this->assertImmutablePopulation();
            $before = $this->snapshot();
            [$code, $result] = $this->runGate($gate, $flags);
            $after = $this->snapshot();
            $this->assertSame($before, $after, 'READ_ONLY_DOCUMENT_TREE_CHANGED: '.$gate);
            $this->assertSame($exit, $code, json_encode($result));
            $this->assertSame($status, $result['status'] ?? null);
            if ($status === 'PASS' && isset($result['checks'])) {
                $this->assertNotEmpty($result['checks']);
                foreach ($result['checks'] as $check) {
                    $this->assertSame('PASS', is_array($check) ? $check['status'] : $check);
                }
            }
            if ($exit === 2) {
                $this->assertSame(['READ_ONLY_MODE_CONFLICT'], $result['errors']);
            } elseif ($defect === 'unregistered') {
                $checks = array_column($result['checks'], 'status', 'check');
                $this->assertSame('FAIL', $checks['ONE_DOCUMENT_ONE_ROLE']);
            } elseif ($defect === 'work-id') {
                $this->assertSame('FAIL', $result['checks']['validity']);
                $this->assertContains('work identity mismatch MD-B18-A002-BL001', $result['validity_errors']);
            }
        } finally {
            foreach ($restore as $path => $bytes) {
                $bytes === null ? unlink($path) : file_put_contents($path, $bytes);
            }
        }
    }

    public function test_governance_self_test_remains_fail_closed_without_changing_documents(): void
    {
        $before = $this->snapshot();
        [$code, $result] = $this->runGate('MarketDataRelationshipIntegrityGateSelfTest', []);
        $this->assertSame($before, $this->snapshot(), 'SELF_TEST_CHANGED_ITS_SOURCE_DOCUMENTS');
        $this->assertSame(0, $code, json_encode($result));
        $this->assertSame('PASS', $result['status']);
        $caught = $controls = $checkScoped = 0;
        foreach ($result['mutations'] as $mutation) {
            $this->assertTrue($mutation['mutation_applied']);
            $this->assertSame($mutation['expected'], $mutation['observed']);
            $caught += $mutation['verdict'] === 'FAILS_CLOSED' ? 1 : 0;
            $controls += $mutation['verdict'] === 'CONTROL_OK' ? 1 : 0;
            // DOC-CHG-20260923-001: integrity-exception probes must fail exactly the checks they name.
            if (isset($mutation['expected_failing_checks']) && $mutation['verdict'] === 'FAILS_CLOSED') {
                $this->assertSame($mutation['expected_failing_checks'], $mutation['observed_failing_checks'], $mutation['mutation']);
                $checkScoped++;
            }
        }
        $this->assertGreaterThanOrEqual(29, $caught);
        $this->assertGreaterThanOrEqual(15, $checkScoped, 'the integrity-exception probes are missing from the self-test');
        $this->assertSame(4, $controls);
    }

    private function assertImmutablePopulation(): void
    {
        $f = fopen(self::$md.'/authority/governance/DOCUMENT_ROLE_REGISTRY.csv', 'r');
        $header = fgetcsv($f);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $count = 0;
        while (($values = fgetcsv($f)) !== false) {
            $r = array_combine($header, $values);
            if ($r['mutability'] === 'IMMUTABLE_AFTER_ISSUE') {
                $this->assertFileExists(self::$md.'/'.$r['document_path']);
                $count++;
            }
        }
        fclose($f);
        $this->assertGreaterThan(500, $count, 'immutable-record population must be real, not an empty scan');
    }

    private function snapshot(): array
    {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$md, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $files[str_replace('\\', '/', $it->getSubPathName())] = hash_file('sha256', $file->getPathname());
            }
        }
        ksort($files);
        $this->assertGreaterThan(1000, count($files), 'whole document tree must be scanned');
        return $files;
    }

    private function runGate(string $name, array $flags): array
    {
        $args = [PHP_BINARY, self::$md.'/development/implementation/tests/'.$name.'.php'];
        $command = implode(' ', array_map('escapeshellarg', array_merge($args, $flags)));
        exec($command.' 2>&1', $lines, $code);
        $text = implode("\n", $lines);
        $result = json_decode($text, true);
        $this->assertIsArray($result, 'gate must emit its structured result: '.$text);
        return [$code, $result];
    }
}
