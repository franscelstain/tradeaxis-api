<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/docs/market_data/development/implementation/tests/MarketDataTraceabilityApplicabilityGate.php';

class B19TraceabilityCompatibilityTest extends TestCase
{
    private function rows(): array
    {
        return MarketDataTraceabilityApplicabilityGate::readMatrix(dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv')['rows'];
    }

    public function test_b19_validates_its_own_reviewed_applicability_without_b01_note_or_conditional_semantics(): void
    {
        $result = MarketDataTraceabilityApplicabilityGate::validate($this->rows(), 'MD-B19');
        $this->assertSame([], $result['errors'], 'B19 current canonical scope must not be subjected to B01 normalization notes.');
        $this->assertSame(750, $result['counts']['required_rows']);
        $this->assertSame(714, $result['counts']['mandatory']);
        $this->assertSame(36, $result['counts']['conditional_applicable']);
        $this->assertSame(0, $result['counts']['satisfied']);
        $this->assertSame(750, $result['counts']['not_assessed']);
    }

    public function test_b19_refuses_scope_demotion_duplicate_and_applicability_changes(): void
    {
        foreach (['demotion', 'duplicate', 'applicability'] as $case) {
            $rows = $this->rows();
            foreach ($rows as $index => $row) if ($row['rule_id'] === 'MD-S075-R0018') break;
            if ($case === 'demotion') $rows[$index]['coverage_requirement'] = 'REFERENCE_ONLY';
            if ($case === 'duplicate') $rows[] = $rows[$index];
            if ($case === 'applicability') $rows[$index]['applicability'] = 'MANDATORY';
            $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows, 'MD-B19')['errors'], $case);
        }
    }

    public function test_b19_refuses_fabricated_source_even_when_the_row_fingerprint_is_recomputed_and_missing_review_or_evidence(): void
    {
        foreach (['source', 'note', 'formal_admission', 'wrong_registered_owner', 'unregistered_owner'] as $case) {
            $rows = $this->rows();
            foreach ($rows as $index => $row) if ($row['rule_id'] === 'MD-S072-R0021') break;
            if ($case === 'source') {
                $rows[$index]['rule_text'] = 'A forged obligation is not authority even when the attacker recomputes its SHA1.';
                $rows[$index]['rule_fingerprint_sha1'] = strtoupper(sha1($rows[$index]['rule_text']));
            }
            if ($case === 'note') $rows[$index]['notes'] = 'B01 note cannot authorize B19 applicability.';
            if ($case === 'wrong_registered_owner') $rows[$index]['strategy_document_id'] = 'MD-S069';
            if ($case === 'unregistered_owner') $rows[$index]['strategy_document_id'] = 'UNREGISTERED';
            if ($case === 'formal_admission') { $rows[$index]['coverage_status'] = 'SATISFIED'; $rows[$index]['current_evidence_ids'] = ''; }
            $this->assertNotSame([], MarketDataTraceabilityApplicabilityGate::validate($rows, 'MD-B19')['errors'], $case);
        }
    }
    public function test_explicit_b19_cli_is_read_only_and_cannot_normalize_the_b01_matrix(): void
    {
        $root = dirname(__DIR__, 3);
        $matrix = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $before = hash_file('sha256', $matrix);
        $gate = $root.'/docs/market_data/development/implementation/tests/MarketDataTraceabilityApplicabilityGate.php';
        foreach ([['--check', '--stage=MD-B19'], ['--stage=MD-B19', '--apply-normalization'], ['--check', '--stage=UNKNOWN']] as $index => $arguments) {
            $process = new \Symfony\Component\Process\Process(array_merge([PHP_BINARY, $gate], $arguments), $root);
            $process->run(); $output = json_decode($process->getOutput(), true);
            $this->assertSame($index === 0 ? 0 : 2, $process->getExitCode());
            $this->assertSame($index === 0 ? 'PASS' : 'FAIL', $output['status']);
            if ($index === 0) { $this->assertSame('MD-B19', $output['stage_id']); $this->assertSame('MD-B19-A001', $output['attempt_id']); $this->assertSame(750, $output['counts']['required_rows']); }
            $this->assertSame($before, hash_file('sha256', $matrix));
            if ($directory = getenv('B19_E049_STATE_DIR')) {
                if (! is_dir($directory)) mkdir($directory, 0775, true);
                file_put_contents($directory.'/b19_traceability_cli_'.$index.'.json', json_encode([
                    'command' => $process->getCommandLine(), 'exit_code' => $process->getExitCode(), 'stdout' => $process->getOutput(),
                    'stderr' => $process->getErrorOutput(), 'matrix_before_sha256' => $before, 'matrix_after_sha256' => hash_file('sha256', $matrix),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        }
    }

}
