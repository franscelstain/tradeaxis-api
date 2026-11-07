<?php

use App\Application\MarketData\Services\B19ReleaseAdmissionEvaluator;

class B19ReleaseAdmissionEvaluatorTest extends TestCase
{
    private $directory;
    private $build = 'e2ba36bfe573b4147f1978f10972d9cde0ed43e0';

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = base_path('storage/app/market-data/evidence/MD-B19-A001/E044_runtime/release_gate_fixture_'.bin2hex(random_bytes(4)));
        mkdir($this->directory, 0775, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $path) if (is_file($path)) unlink($path);
        rmdir($this->directory);
        parent::tearDown();
    }

    private function bundle(): array
    {
        $checks = [];
        foreach (B19ReleaseAdmissionEvaluator::CHECKS as $name) {
            $raw = $name.'.stdout.txt';
            file_put_contents($this->directory.'/'.$raw, 'Executed '.$name.' check; PASS; 1 assertion.' . "\n");
            $checks[$name] = [
                'stage' => 'MD-B19-A001', 'attempt' => 'MD-B19-A001', 'build_sha' => $this->build,
                'result' => 'PASS', 'exit_code' => 0, 'command' => 'focused-'.$name,
                'executed_at' => '2026-10-21T12:00:00+07:00',
                'source_file' => 'tests/Unit/MarketData/B19ReleaseAdmissionEvaluatorTest.php',
                'source_sha256' => hash_file('sha256', __FILE__),
                'raw_file' => $raw,
                'raw_sha256' => hash_file('sha256', $this->directory.'/'.$raw),
            ];
        }
        $checks['golden_fixtures']['fixture_decision'] = 'MATCH';
        $checks['replay_data_quality']['review_record'] = 'reviewed-fixture';
        $checks['config_registry']['registry_record'] = 'registry-fixture';
        $checks['downstream_safety']['invariants'] = ['effective_date' => 'PASS', 'seal' => 'PASS', 'fallback' => 'PASS'];
        return [
            'stage' => 'MD-B19-A001', 'attempt' => 'MD-B19-A001', 'build_sha' => $this->build,
            'unresolved_dependencies' => [], 'failed_verifications' => [], 'checks' => $checks,
        ];
    }

    private function evaluate(array $bundle): array
    {
        return (new B19ReleaseAdmissionEvaluator())->evaluate($bundle, $this->build, $this->directory);
    }

    public function test_all_declared_check_evidence_is_required_and_cannot_claim_release_safety(): void
    {
        $bundle = $this->bundle();
        $good = $this->evaluate($bundle);
        $this->assertSame('DECLARED_CONDITIONS_MET', $good['state']);
        $this->assertCount(5, $good['checks']);
        foreach (['gate-set sufficiency', 'meaningful exercise', 'superseded source/build identity', 'release safety'] as $caveat) {
            $this->assertStringContainsString($caveat, $good['claim_limit']);
        }
        foreach (B19ReleaseAdmissionEvaluator::CHECKS as $name) {
            $bad = $bundle;
            unset($bad['checks'][$name]);
            $result = $this->evaluate($bad);
            $this->assertSame('BLOCKED', $result['state'], $name);
            $this->assertContains($name.':CHECK_MISSING', $result['issues']);

            $bad = $bundle;
            $bad['checks'][$name]['result'] = 'FAIL';
            $result = $this->evaluate($bad);
            $this->assertContains($name.':CHECK_NOT_PASSED', $result['issues']);

            $bad = $bundle;
            $bad['checks'][$name]['raw_sha256'] = str_repeat('0', 64);
            $result = $this->evaluate($bad);
            $this->assertContains($name.':RAW_EXECUTION_HASH_MISMATCH', $result['issues']);
        }
        $bad = $bundle;
        $bad['checks']['golden_fixtures']['fixture_decision'] = 'INTENTIONAL_VERSION_BUMP';
        $this->assertContains('golden_fixtures:VERSION_BUMP_AUTHORITY_MISSING', $this->evaluate($bad)['issues']);
        $bad = $bundle;
        $bad['checks']['downstream_safety']['invariants']['seal'] = 'FAIL';
        $this->assertContains('downstream_safety:DOWNSTREAM_SEAL_UNPROVEN', $this->evaluate($bad)['issues']);
    }

    public function test_build_attempt_stage_dependencies_failure_and_execution_identity_are_fail_closed(): void
    {
        $bundle = $this->bundle();
        $changes = [
            ['stage', 'MD-B18-A002', 'STAGE_IDENTITY_MISMATCH'],
            ['attempt', 'MD-B19-A000', 'ATTEMPT_IDENTITY_MISMATCH'],
            ['build_sha', str_repeat('a', 40), 'BUILD_IDENTITY_MISMATCH'],
            ['unresolved_dependencies', ['MD-DEP-0025'], 'UNRESOLVED_DEPENDENCIES'],
            ['failed_verifications', ['B18_REPLAY_COMPARISON'], 'FAILED_VERIFICATIONS'],
        ];
        foreach ($changes as [$field, $value, $reason]) {
            $bad = $bundle;
            $bad[$field] = $value;
            $result = $this->evaluate($bad);
            $this->assertSame('BLOCKED', $result['state'], $field);
            $this->assertContains($reason, $result['issues']);
        }
        $bad = $bundle;
        $bad['checks']['contract_tests']['build_sha'] = str_repeat('b', 40);
        $this->assertContains('contract_tests:CHECK_IDENTITY_MISMATCH', $this->evaluate($bad)['issues']);
        $bad = $bundle;
        $bad['checks']['contract_tests']['command'] = '';
        $this->assertContains('contract_tests:EXECUTION_IDENTITY_MISSING', $this->evaluate($bad)['issues']);
        $bad = $bundle;
        $bad['checks']['contract_tests']['source_sha256'] = str_repeat('0', 64);
        $this->assertContains('contract_tests:SOURCE_HASH_MISMATCH', $this->evaluate($bad)['issues']);
        $bad = $bundle;
        $bad['checks']['contract_tests']['raw_file'] = '../E043_runtime/schema_drift_command_green.stdout.txt';
        $this->assertContains('contract_tests:RAW_EXECUTION_UNAVAILABLE', $this->evaluate($bad)['issues']);
        $bad = $bundle;
        $bad['checks']['config_registry']['raw_file'] = $bad['checks']['contract_tests']['raw_file'];
        $this->assertSame('BLOCKED', $this->evaluate($bad)['state']);
    }
}
