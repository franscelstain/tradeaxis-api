<?php

namespace Tests\Unit\MarketData;

use PHPUnit\Framework\TestCase;

/**
 * Policy alignment only. These checks do not claim that an operator performed a workflow.
 * Each row names the independently selected meaning on both sides of one required link.
 */
class B19CrossContractSemanticAlignmentTest extends TestCase
{
    private const OPS = 'authority/strategy/ops/';
    private const BOOK = 'authority/strategy/book/';
    private const DEV = 'development/implementation/';

    private static function cases(): array
    {
        $archive = self::OPS.'Archived_Actual_Execution_Evidence_Contract_LOCKED.md';
        $audit = self::OPS.'Audit_Evidence_Pack_Contract_LOCKED.md';
        $admission = self::OPS.'Executed_Run_Admission_Criteria_LOCKED.md';
        $operator = self::OPS.'Operator_Decision_Trees_LOCKED.md';
        $artifacts = self::OPS.'Run_Artifacts_Format_LOCKED.md';
        $pack = self::OPS.'Run_Execution_Evidence_Pack_Contract_LOCKED.md';
        $proof = self::DEV.'tests/specs/Executed_Proof_Admission_Criteria_LOCKED.md';
        $examples = self::DEV.'examples/legacy/README.md';
        $playbook = self::DEV.'ops/Failure_Playbook_LOCKED.md';
        $runbook = self::DEV.'ops/Historical_Correction_Runbook_LOCKED.md';
        $correction = self::BOOK.'Historical_Correction_and_Reseal_Contract_LOCKED.md';
        $replay = self::BOOK.'Replay_Verification_Contract_LOCKED.md';
        $readiness = self::BOOK.'Downstream_Data_Readiness_Guarantee_LOCKED.md';

        return [
            'MD-S062-R0056' => [$archive, $pack, 'one normal readable success run', 'normal readable success'],
            'MD-S062-R0057' => [$archive, $admission, 'real execution identifiers', 'real execution identity'],
            'MD-S062-R0058' => [$archive, $proof, 'real execution identifiers', 'real execution identity'],
            'MD-S062-R0059' => [$archive, $examples, 'Illustrative examples do not satisfy', 'non-authoritative illustrative/example artifacts'],
            'MD-S062-R0060' => [$archive, $audit, 'actual correction request reference', 'correction request identity'],
            'MD-S063-R0163' => [$audit, $artifacts, 'Evidence packs must not imply readability for an unsealed or non-current publication', 'summary must not imply requested date is readable'],
            'MD-S063-R0164' => [$audit, $playbook, 'whether prior safe state was preserved', 'Preserve evidence and the prior active publication'],
            'MD-S063-R0165' => [$audit, $correction, 'prior current publication reference', 'prior sealed publication'],
            'MD-S063-R0166' => [$audit, $replay, 'both expected and actual state', 'actual and expected contexts'],
            'MD-S063-R0167' => [$audit, $readiness, 'Evidence packs must not imply readability for an unsealed or non-current publication', 'one active, sealed'],
            'MD-S068-R0027' => [$admission, $pack, 'sourced from an actual run', 'real executed run'],
            'MD-S068-R0028' => [$admission, $examples, 'illustrative JSON shapes with placeholder hashes', 'non-authoritative illustrative/example artifacts'],
            'MD-S068-R0029' => [$admission, $proof, 'real execution identity', 'real execution identity'],
            'MD-S071-R0045' => [$operator, $playbook, 'preserve prior publication and hold/fail requested date', 'Preserve evidence and the prior active publication'],
            'MD-S071-R0046' => [$operator, self::OPS.'Incident_Classification_and_Response_Matrix_LOCKED.md', 'Preserve prior current publication', 'preserve last verified publication only if gateway integrity is certain'],
            'MD-S071-R0047' => [$operator, $runbook, 'Preserve prior current publication', 'prior current publication remains preserved'],
            'MD-S075-R0304' => [$artifacts, $audit, 'summary must not imply requested date is readable', 'Evidence packs must not imply readability for an unsealed or non-current publication'],
            'MD-S075-R0305' => [$artifacts, $playbook, 'Publication safety: requested date not readable', 'requested date remains non-readable'],
            'MD-S075-R0306' => [$artifacts, $correction, 'unchanged rerun must not claim a publication switch', 'material correction'],
            'MD-S075-R0307' => [$artifacts, $replay, 'replay-result status must be explicit', 'PASS`: all expected values'],
            'MD-S075-R0308' => [$artifacts, $readiness, 'readiness/freshness states', 'They are independent facts'],
            'MD-S076-R0049' => [$pack, $audit, 'real `run_summary.json`', 'run summary'],
            'MD-S076-R0050' => [$pack, $artifacts, 'real `run_summary.json`', 'run_summary.json'],
            'MD-S076-R0051' => [$pack, $proof, 'real test ID', 'actual `test_id`'],
            'MD-S076-R0052' => [$pack, $examples, 'Illustrative examples are useful', 'non-authoritative illustrative/example artifacts'],
        ];
    }

    /** Additional independent facts prevent one shared keyword from admitting a whole link. */
    private static function dimensions(string $id): array
    {
        $groups = [
            'MD-S062-R0056' => [['held or failed requested-date run', 'held or failed requested-date outcome'], ['historical correction publication', 'historical correction publication'], ['one replay execution', 'replay match or replay mismatch'], ['one executed contract-test result', 'executed contract-test run summary']],
            'MD-S062-R0057' => [['real produced values', 'actual produced values'], ['actual execution context', 'real execution context'], ['Illustrative examples do not satisfy', 'illustrative JSON shapes with placeholder hashes']],
            'MD-S062-R0058' => [['actual test ID', 'actual `test_id`'], ['actual pass/fail result', 'actual execution result'], ['actual assertion evidence', 'actual observed values or summaries']],
            'MD-S062-R0059' => [['archived actual execution evidence should exist separately', 'If an example conflicts with current strategy, the example is stale']],
            'MD-S062-R0060' => [['actual prior publication reference', 'prior current publication reference'], ['actual old/new hash values', 'old hash set'], ['actual publication switch result', 'final publication switch result']],
            'MD-S063-R0163' => [['run-event summary', 'run_event_summary.json'], ['both expected and actual state', 'expected and actual paths are independent'], ['final publication switch result', 'unchanged rerun must not claim a publication switch']],
            'MD-S063-R0164' => [['whether prior safe state was preserved', 'prior active publication'], ['incident, correction, or replay mismatch', 'Close only when root cause']],
            'MD-S063-R0165' => [['old hash set', 'old hash set'], ['new hash set', 'new hash set'], ['final publication switch result', 'switch']],
            'MD-S063-R0166' => [['explicit replay result status', 'comparison'], ['mismatch summary', 'mismatch paths']],
            'MD-S063-R0167' => [['current publication resolution', 'active sealed publication'], ['freshness context', 'freshness_state']],
            'MD-S068-R0027' => [['real execution identity', 'real config identity'], ['actual produced values', 'real hash values'], ['not composed solely of placeholders', 'not manually fabricated placeholders']],
            'MD-S068-R0028' => [['manually fabricated example outputs', 'If an example conflicts with current strategy, the example is stale']],
            'MD-S068-R0029' => [['actual produced values', 'real produced outputs'], ['traced back to a real execution context', 'traceable origin']],
            'MD-S071-R0045' => [['requested date is not readable', 'requested date remains non-readable'], ['Keep candidate non-readable', 'do not mutate facts']],
            'MD-S071-R0046' => [['critical incident', 'critical'], ['requested date is not readable', 'fail closed']],
            'MD-S071-R0047' => [['candidate correction non-current', 'candidate correction is non-current'], ['correction evidence', 'correction evidence pack']],
            'MD-S075-R0304' => [['correction_evidence.json', 'correction evidence'], ['replay_result.json', 'replay result evidence pack']],
            'MD-S075-R0305' => [['narrative explanation must not contradict status', 'evidence and the prior active publication'], ['requested date not readable', 'requested date remains non-readable']],
            'MD-S075-R0306' => [['prior_publication_version', 'prior'], ['new_publication_version', 'new'], ['publication switch', 'controlled correction flow']],
            'MD-S075-R0307' => [['expected comparison context must remain explicit', 'actual and expected contexts'], ['BLOCKED', 'BLOCKED']],
            'MD-S075-R0308' => [['requested/effective dates', 'requested'], ['readiness/freshness states', 'readiness_state']],
            'MD-S076-R0049' => [['real correction request reference', 'correction request identity'], ['real `replay_result.json` object', 'replay result evidence pack']],
            'MD-S076-R0050' => [['real `publication_manifest.json`', '`publication_manifest.json`'], ['real `run_event_summary.json`', '`run_event_summary.json`']],
            'MD-S076-R0051' => [['real fixture family', 'actual fixture family'], ['real pass/fail result', 'actual execution result']],
            'MD-S076-R0052' => [['do not count as executed evidence unless', 'non-authoritative illustrative/example artifacts']],
        ];
        return $groups[$id];
    }

    public static function alignmentProvider(): array
    {
        $result = [];
        foreach (self::cases() as $id => $case) {
            $result[$id] = array_merge([$id], $case);
        }
        return $result;
    }

    private static function document(string $relative): string
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/'.$relative;
        self::assertFileExists($path);
        return (string) file_get_contents($path);
    }

    private static function registeredRole(string $relative): array
    {
        $registry = dirname(__DIR__, 3).'/docs/market_data/authority/governance/DOCUMENT_ROLE_REGISTRY.csv';
        $handle = fopen($registry, 'rb');
        self::assertNotFalse($handle);
        $header = fgetcsv($handle);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $found = [];
        while (($row = fgetcsv($handle)) !== false) {
            $entry = array_combine($header, $row);
            if ($entry['document_path'] === $relative) {
                $found[] = $entry;
            }
        }
        fclose($handle);
        self::assertCount(1, $found, $relative.' must resolve to one registered document');
        return $found[0];
    }

    private static function aligned(string $source, string $target, string $sourceFact, string $targetFact): bool
    {
        return stripos($source, $sourceFact) !== false && stripos($target, $targetFact) !== false;
    }

    /** The matrix identifies the governing line; the document itself must still contain that reference. */
    private static function governingReference(string $id): array
    {
        static $rules = null;
        if ($rules !== null) {
            self::assertArrayHasKey($id, $rules);
            return $rules[$id];
        }
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
        $handle = fopen($path, 'rb');
        self::assertNotFalse($handle);
        $header = fgetcsv($handle);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $rules = [];
        while (($row = fgetcsv($handle)) !== false) {
            $entry = array_combine($header, $row);
            if (isset($rules[$entry['rule_id']])) {
                self::fail($entry['rule_id'].' has duplicate governing rules');
            }
            $rules[$entry['rule_id']] = $entry;
        }
        fclose($handle);
        self::assertArrayHasKey($id, $rules);
        return $rules[$id];
    }

    private static function registeredPaths(): array
    {
        static $result = null;
        if ($result !== null) {
            return $result;
        }
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/DOCUMENT_ROLE_REGISTRY.csv';
        $handle = fopen($path, 'rb');
        self::assertNotFalse($handle);
        $header = fgetcsv($handle);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $result = [];
        while (($row = fgetcsv($handle)) !== false) {
            $entry = array_combine($header, $row);
            if (isset($result[$entry['document_path']])) {
                self::fail($entry['document_path'].' has duplicate document roles');
            }
            $result[$entry['document_path']] = $entry;
        }
        fclose($handle);
        return $result;
    }

    private static function registeredIdentities(): array
    {
        static $result = null;
        if ($result !== null) {
            return $result;
        }
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/governance/DOCUMENT_ID_REGISTRY.csv';
        $handle = fopen($path, 'rb');
        self::assertNotFalse($handle);
        $header = fgetcsv($handle);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $result = [];
        while (($row = fgetcsv($handle)) !== false) {
            $entry = array_combine($header, $row);
            if (isset($result[$entry['document_path']])) {
                self::fail($entry['document_path'].' has duplicate document identities');
            }
            $result[$entry['document_path']] = $entry;
        }
        fclose($handle);
        return $result;
    }

    /** Generic references name a registered document family, not an invented mandatory filename. */
    private static function resolvesToTarget(string $reference, string $target, array $registry): bool
    {
        $identities = self::registeredIdentities();
        if (!isset($registry[$target], $identities[$target])
            || $registry[$target]['document_role'] !== $identities[$target]['document_role']) {
            return false;
        }
        if (preg_match('/^`(?:\.\.\/[^`]+\/)?([^`\/]+\.md)`$/', $reference, $match)) {
            $matches = array_keys(array_filter($registry, static fn ($row, $path) => basename($path) === $match[1], ARRAY_FILTER_USE_BOTH));
            return count($matches) === 1 && $matches[0] === $target;
        }
        $families = [
            'executed evidence examples' => static fn ($path, $role) => str_starts_with($path, 'development/implementation/examples/') && $role === 'IMPLEMENTATION_EXAMPLE',
            'examples folder artifacts' => static fn ($path, $role) => str_starts_with($path, 'development/implementation/examples/') && $role === 'IMPLEMENTATION_EXAMPLE',
            'examples folder contracts/examples' => static fn ($path, $role) => str_starts_with($path, 'development/implementation/examples/') && $role === 'IMPLEMENTATION_EXAMPLE',
            'audit evidence pack contracts' => static fn ($path, $role) => str_starts_with($path, 'authority/strategy/ops/Audit_Evidence_Pack_Contract_') && $role === 'STRATEGY',
            'replay schema/contracts' => static fn ($path, $role) => str_starts_with($path, 'authority/strategy/book/Replay_Verification_Contract_') && $role === 'STRATEGY',
            'replay contracts' => static fn ($path, $role) => str_starts_with($path, 'authority/strategy/book/Replay_Verification_Contract_') && $role === 'STRATEGY',
            'publication/readiness contracts' => static fn ($path, $role) => str_starts_with($path, 'authority/strategy/book/') && $role === 'STRATEGY' && (str_contains($path, 'Publication_') || str_contains($path, 'Readiness_')),
            'testing proof contracts' => static fn ($path, $role) => str_starts_with($path, 'development/implementation/tests/specs/Executed_Proof_') && $role === 'IMPLEMENTATION_TEST',
        ];
        return isset($families[$reference]) && $families[$reference]($target, $registry[$target]['document_role']);
    }

    private static function referenceAligned(string $id, string $sourceText, string $target): bool
    {
        $governing = self::governingReference($id);
        $source = self::cases()[$id][0];
        $identities = self::registeredIdentities();
        if ($governing['strategy_owner'] !== $source
            || ! isset($identities[$source])
            || $identities[$source]['strategy_authority_id'] !== $governing['strategy_document_id']
            || $identities[$source]['document_role'] !== 'STRATEGY') {
            return false;
        }
        $lines = preg_split('/\r\n|\n|\r/', $sourceText);
        $line = $lines[(int) $governing['source_line'] - 1] ?? null;
        return $line === $governing['rule_text']
            && str_starts_with($line, '- ')
            && self::resolvesToTarget(substr($line, 2), $target, self::registeredPaths());
    }

    /** @dataProvider alignmentProvider */
    public function test_each_pair_preserves_its_independently_named_semantic_fact(string $id, string $source, string $target, string $sourceFact, string $targetFact): void
    {
        self::assertSame('YES', self::registeredRole($source)['current_authority']);
        self::assertContains(self::registeredRole($target)['document_role'], ['STRATEGY', 'IMPLEMENTATION_GUIDANCE', 'IMPLEMENTATION_EXAMPLE', 'IMPLEMENTATION_TEST']);
        self::assertTrue(self::referenceAligned($id, self::document($source), $target), $id.' governing reference does not resolve to registered target');
        foreach (array_merge([[$sourceFact, $targetFact]], self::dimensions($id)) as [$left, $right]) {
            self::assertTrue(self::aligned(self::document($source), self::document($target), $left, $right), $id.' '.json_encode([$left, $right]));
        }
    }

    /** @dataProvider alignmentProvider */
    public function test_each_pair_rejects_removed_retargeted_or_unregistered_reference(string $id, string $source, string $target): void
    {
        $sourceText = self::document($source);
        $rule = self::governingReference($id);
        $reference = $rule['rule_text'];
        self::assertTrue(self::referenceAligned($id, $sourceText, $target), $id.' control');
        $lines = preg_split('/\r\n|\n|\r/', $sourceText);
        $index = (int) $rule['source_line'] - 1;
        $lines[$index] = '- removed governing reference';
        self::assertFalse(self::referenceAligned($id, implode("\n", $lines), $target), $id.' removed');
        $lines[$index] = '- `Unrelated_Contract_LOCKED.md`';
        self::assertFalse(self::referenceAligned($id, implode("\n", $lines), $target), $id.' retargeted');
        $otherRegisteredTarget = $target === self::OPS.'Audit_Evidence_Pack_Contract_LOCKED.md'
            ? self::OPS.'Run_Artifacts_Format_LOCKED.md'
            : self::OPS.'Audit_Evidence_Pack_Contract_LOCKED.md';
        self::assertFalse(self::referenceAligned($id, $sourceText, $otherRegisteredTarget), $id.' wrong registered target');
        self::assertFalse(self::resolvesToTarget(substr($reference, 2), 'unregistered/Wrong_Target.md', self::registeredPaths()), $id.' unregistered');
    }

    /** @dataProvider alignmentProvider */
    public function test_each_pair_rejects_missing_or_inverted_meaning(string $id, string $source, string $target, string $sourceFact, string $targetFact): void
    {
        $sourceText = self::document($source);
        $targetText = self::document($target);
        foreach (array_merge([[$sourceFact, $targetFact]], self::dimensions($id)) as [$left, $right]) {
            self::assertFalse(self::aligned(str_ireplace($left, 'CONTRADICTED_SOURCE', $sourceText), $targetText, $left, $right), $id.' source '.$left);
            self::assertFalse(self::aligned($sourceText, str_ireplace($right, 'CONTRADICTED_TARGET', $targetText), $left, $right), $id.' target '.$right);
        }
    }
}
