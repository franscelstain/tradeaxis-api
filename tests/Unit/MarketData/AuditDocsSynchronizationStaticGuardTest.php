<?php

use PHPUnit\Framework\TestCase;

/**
 * Six tests were removed from this file, which held 143 string assertions.
 *
 * - The cross-reference roster was about a hundred assertions of the shape "status says X -> DONE"
 *   / "[RELATED_CONTRACT] Y" / "tracker says Y -> LOCKED" / "[RELATED_IMPLEMENTATION] X", written
 *   out by hand for roughly a dozen of the fifty entries. AuditCrossReferenceIntegrityTest derives
 *   the same rule and applies it to every entry in both documents, including entries not written
 *   yet.
 * - Registry-and-seed synchronization was checked by parsing both files with regexes. That is the
 *   exact check that passed for years while the seed carried a trailing comma and inserted
 *   nothing. ReasonCodeSeedExecutionTest runs the statement and compares the registry against the
 *   rows that actually landed in eod_reason_codes.
 * - Three tests asserted frozen historical tallies and runtime-proof identifiers — "OK (511 tests,
 *   7871 assertions)", run_id=33, replay_id=15, benchmark_rows_written=1, storage paths from a
 *   past operator run. They record what happened once. They cannot fail unless someone edits the
 *   audit history, and if someone does, the tallies are not what protects it.
 *
 * What remains are the rules that hold regardless of which entries exist.
 */
class AuditDocsSynchronizationStaticGuardTest extends TestCase
{
    private function read(string $path): string
    {
        $fullPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
        $this->assertFileExists($fullPath);

        return file_get_contents($fullPath);
    }

    /**
     * A LOCKED contract is one nothing may quietly change. That status is only meaningful if the
     * document says what evidence locked it, so every LOCKED block must carry its validation
     * sections, cite operator-local validation, cite a passing result, and name the test scope.
     *
     * Derived over whichever contracts are LOCKED, so a contract locked tomorrow is held to the
     * same standard without editing this test.
     */
    public function test_locked_contracts_have_concrete_validation_evidence(): void
    {
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        preg_match_all(
            '/^- ([A-Z0-9_]+_CONTRACT) -> LOCKED\R(?P<body>.*?)(?=^- [A-Z0-9_]+_CONTRACT (?:->|→)|\z)/msu',
            $tracker,
            $matches,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($matches, 'The tracker must contain LOCKED contracts.');

        foreach ($matches as $match) {
            [$block, $contractName] = [$match[0], $match[1]];

            $this->assertStringContainsString('[VALIDATED]', $block, $contractName.' must have a VALIDATED section.');
            $this->assertStringContainsString('[FINAL_RULE]', $block, $contractName.' must have a FINAL_RULE section.');
            $this->assertMatchesRegularExpression(
                '/(Operator-local|Operator local|Local PHPUnit|PHPUnit\/artisan validation was supplied by operator|local validation)/i',
                $block,
                $contractName.' must cite local/operator validation.'
            );
            $this->assertMatchesRegularExpression('/(OK \(|PASS|passed)/i', $block, $contractName.' must cite a passing validation result.');
            $this->assertStringContainsString('tests/Unit/MarketData', $block, $contractName.' must cite MarketData validation scope.');
        }
    }

    /**
     * The working entry at the top of each document must belong to the session both documents
     * declare active. Otherwise the first thing a reader sees is work from a different session.
     */
    public function test_current_canonical_overrides_are_aligned_and_precede_history(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $statusDate = $this->canonicalOverrideDate($status);
        $trackerDate = $this->canonicalOverrideDate($tracker);

        $this->assertSame($statusDate, $trackerDate);
        $this->assertLessThan(strpos($status, '## HISTORICAL SESSION RECORD'), strpos($status, '## CURRENT CANONICAL OVERRIDE'));
        $this->assertLessThan(strpos($tracker, '## HISTORICAL SESSION RECORD'), strpos($tracker, '## CURRENT CANONICAL AUDIT OVERRIDE'));
        $this->assertStringContainsString('Implementation status: `NOT_GRANTED / NOT_PRODUCTION_RELOCKED`', $status);
        $this->assertStringContainsString('`[IMPLEMENTATION_RELOCK_STATUS] NOT_GRANTED / NOT_PRODUCTION_RELOCKED`', $tracker);
        $this->assertStringNotContainsString("\nACTIVE SESSION:\n", $status.$tracker);

        // The working contract line must be a well-formed canonical entry. The contract it names
        // is not asserted: pinning it freezes the documents to one session.
        $this->assertMatchesRegularExpression(
            '/^- [A-Z0-9_]+_CONTRACT (?:->|→) (DONE|LOCKED|ENFORCED|PARTIAL|BLOCKED|REVIEW_REQUIRED)$/',
            $this->firstNonEmptyLineAfter($tracker, '## CURRENT WORKING CONTRACT')
        );
    }

    /**
     * The governance document is what makes the audit trail trustworthy: append-only, no
     * duplicate canonical entries, evidence recorded against a named environment. These markers
     * are its structure, and nothing else asserts they survive.
     */
    public function test_audit_governance_enforces_append_only_anti_duplication_and_static_guard(): void
    {
        $documents = $this->read('docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md')
            .$this->read('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md')
            .$this->read('docs/market_data/audit/AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md');

        foreach ([
            'AUDIT DOCS SYNCHRONIZATION HARD RULE',
            'append-only',
            'anti-duplication',
            'ACTIVE SESSION',
            'targeted and full local PHPUnit evidence',
            'LOCKED_LOCAL_PHPUNIT_PASS',
            'AUDIT_DOCS_SYNCHRONIZATION_CONTRACT',
            'RUNTIME ENVIRONMENT BASELINE HARD RULE',
            'operator-local PHP version',
            'operator-local PHPUnit version',
        ] as $needle) {
            $this->assertStringContainsString($needle, $documents);
        }
    }

    /**
     * F-045 was originally recorded inside a dated W15 re-audit block. Keeping that history is
     * required, but allowing it to look active again would make a later partial read contradict
     * the current controller. This guard binds all three views: current open IDs, active-finding
     * rows, and the local superseded marker on the historical claim itself.
     */
    public function test_stage_two_f045_cannot_regress_to_active_or_ambiguous_audit_state(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');

        $matched = preg_match(
            '/^- open findings recorded by command protocol:.*?\((?<ids>[^\r\n)]*)\)\./m',
            $ledger,
            $openFindingMatch
        );
        $this->assertSame(1, $matched, 'The current open-finding roster could not be inspected.');
        preg_match_all('/`(?<id>F-\d+)`/', $openFindingMatch['ids'], $openIdMatches);
        $this->assertNotContains('F-045', $openIdMatches['id'], 'Closed F-045 returned to the current open-finding roster.');

        $matched = preg_match('/^## Active findings\R(?<table>.*?)(?=^## )/ms', $ledger, $activeFindingMatch);
        $this->assertSame(1, $matched, 'The active-finding table could not be inspected.');
        $this->assertStringNotContainsString(
            '| `F-045` |',
            $activeFindingMatch['table'],
            'Closed F-045 must not remain in the current-state active-finding table.'
        );

        $this->assertStringContainsString(
            '## `MD-REAUDIT W15` — 2026-08-11 keempat — HISTORICAL, SUPERSEDED UNTUK `F-045`',
            $ledger,
            'The original F-045 claim must carry its superseded marker at the claim location.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/^.*(?:\bOPEN\b|\bPARTIAL\b|open findings).*`F-045`.*$|^.*`F-045`.*(?:\bOPEN\b|\bPARTIAL\b|open findings).*$/mi',
            $ledger,
            'No line may textually mix F-045 with an open/partial roster or verdict.'
        );
        $this->assertMatchesRegularExpression(
            '/^## `MD-REAUDIT W15` — 2026-08-11 keempat — HISTORICAL, SUPERSEDED UNTUK `F-045`\R\R> \*\*STATUS HISTORIS — BUKAN CURRENT FINDING\.\*\*/m',
            $ledger,
            'The historical status marker must sit directly beside the original F-045 audit block.'
        );
        $this->assertStringContainsString(
            '| 2 | Ekspos bukti coverage — **SELESAI 2026-08-12** | `F-045` |',
            $ledger,
            'The ordered-stage table must state that Stage 2 is complete.'
        );
        $this->assertStringContainsString(
            'kolom coverage tersimpan tanpa jalur ekspor maupun alias silang. `F-045` **CLOSED**.',
            $ledger,
            'The authoritative Stage 2 closure verdict is missing.'
        );
        $matched = preg_match(
            '/next permitted implementation action: \*\*Tahap (?<stage>\d+)\b/',
            $ledger,
            $nextStageMatch
        );
        $this->assertSame(1, $matched, 'The next permitted stage could not be inspected.');
        $this->assertGreaterThan(2, (int) $nextStageMatch['stage'], 'The controller must not point back to Stage 2.');

        $matched = preg_match('/^## Tahap 2 .*?\R(?<stage>.*?)(?=^## |\z)/ms', $ledger, $stageMatch);
        $this->assertSame(1, $matched, 'The authoritative Stage 2 section could not be inspected.');
        foreach (['resolveKnowledgeCutoff', 'MarketDataPipelineService', 'ReplayVerificationService'] as $foreignOwner) {
            $this->assertStringNotContainsString(
                $foreignOwner,
                $stageMatch['stage'],
                'Stage 2 evidence must not claim work owned by '.$foreignOwner.'.'
            );
        }
        foreach ([
            'MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS',
            'MarketDataEvidenceExportService::buildCoverageState()',
            'decodeNullableJsonArray()',
        ] as $ownedSymbol) {
            $this->assertStringContainsString(
                $ownedSymbol,
                $stageMatch['stage'],
                'Stage 2 must explicitly bind its conformance claim to '.$ownedSymbol.'.'
            );
        }
        $this->assertStringContainsString(
            '## Impact review alignment kontrak sebelum Tahap 2 — BUKAN EXIT EVIDENCE',
            $ledger,
            'Required cross-contract alignment must be reviewed outside the Stage 2 evidence block.'
        );
    }

    public function test_stage_three_closes_only_write_guards_and_keeps_corpus_findings_open(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');

        $this->assertStringContainsString(
            '| 3 | Bekukan populasi cacat — **SELESAI 2026-08-13** | `F-007a` `F-026a` `F-017a` `F-018a` |',
            $ledger
        );
        $matched = preg_match(
            '/next permitted implementation action: \*\*Tahap (?<stage>\d+)\b/',
            $ledger,
            $nextStageMatch
        );
        $this->assertSame(1, $matched, 'The controller next stage could not be inspected.');
        $this->assertGreaterThan(3, (int) $nextStageMatch['stage'], 'The controller must not point back to Stage 3.');

        $matched = preg_match(
            '/^## Tahap 3 — Bekukan populasi cacat — SELESAI 2026-08-13\R(?<stage>.*?)(?=^## |\z)/ms',
            $ledger,
            $stageMatch
        );
        $this->assertSame(1, $matched, 'The authoritative Stage 3 section could not be inspected.');
        $stage = $stageMatch['stage'];

        foreach (['F-007a', 'F-026a', 'F-017a', 'F-018a'] as $closedHalf) {
            $this->assertMatchesRegularExpression(
                '/`'.preg_quote($closedHalf, '/').'`.*\*\*CLOSED\*\*/s',
                $stage,
                $closedHalf.' must have an explicit Stage 3 closure verdict.'
            );
        }

        foreach (['F-007', 'F-026', 'F-017', 'F-018'] as $openParent) {
            $this->assertMatchesRegularExpression(
                '/`'.preg_quote($openParent, '/').'`.*tetap \*\*OPEN\*\*/s',
                $stage,
                $openParent.' must remain open for its corpus half.'
            );
        }

        foreach ([
            'listing_id',
            'source_observation_id',
            'canonicalization_version',
            'price_product_code',
            'quality_state',
            'coverage_expected_count',
            'coverage_bar_not_expected_count',
            'coverage_expectation_unknown_count',
            'coverage_delivered_count',
            'coverage_delivered_valid_count',
            'universe_membership_state',
            'bar_expectation_state',
            'delivery_state',
            'canonical_quality_state',
            'liquidity_state',
            'temporal_status_state',
            'event_risk_state',
            'eligibility_reasons_json',
        ] as $protectedField) {
            $this->assertStringContainsString($protectedField, $stage, $protectedField.' is missing from Stage 3 evidence.');
        }

        foreach ([
            'EodArtifactRepository::REQUIRED_CANONICAL_BAR_WRITE_FIELDS',
            'EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS',
            'EodRunRepository::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS',
            'assertCompleteBarRows()',
            'assertCompleteEligibilityRows()',
            'assertCompleteCoverageTelemetry()',
            'StageThreeWriteCompletenessGuardTest',
            'StageThreeEligibilityProducerTest',
        ] as $ownedProof) {
            $this->assertStringContainsString($ownedProof, $stage, 'Stage 3 must bind its claim to '.$ownedProof.'.');
        }

        $this->assertStringContainsString('tidak melakukan backfill tahap 5', $stage);
        $this->assertStringContainsString('Tidak ada migrasi schema', $stage);
        $this->assertStringContainsString('Tidak ada klaim bahwa nilai `NULL` historis sudah berkurang.', $stage);
        $this->assertStringContainsString(
            '`operational_start_date`, fixture replay independen, terms IDX,',
            $stage,
            'Stage 3 scope exclusions must remain explicit.'
        );
    }

    public function test_revised_stage_four_onward_sequence_is_independent_and_keeps_activation_outside_build(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');

        $this->assertStringContainsString(
            '## 2026-08-12 — Urutan pengerjaan: tiap tahap dapat dinyatakan selesai sendiri — HISTORICAL, SUPERSEDED MULAI TAHAP 4',
            $ledger,
            'The replaced planning sequence must remain visibly historical.'
        );
        $this->assertMatchesRegularExpression(
            '/HISTORICAL, SUPERSEDED MULAI TAHAP 4\R\R> \*\*STATUS HISTORIS — BUKAN URUTAN AKTIF MULAI TAHAP 4\./',
            $ledger,
            'The superseded marker must sit beside the old sequence.'
        );

        $matched = preg_match(
            '/^## 2026-08-13 — CURRENT AUTHORITATIVE SEQUENCE mulai Tahap 4\R(?<sequence>.*)\z/ms',
            $ledger,
            $sequenceMatch
        );
        $this->assertSame(1, $matched, 'The current Stage 4 onward sequence could not be inspected.');
        $sequence = $sequenceMatch['sequence'];

        $this->assertStringContainsString(
            'next permitted implementation action: **Tahap 9 — author fixture replay independen.',
            $ledger
        );
        $this->assertStringContainsString('`F-021` tetap terbuka tetapi berstatus **`PRE_ACTIVATION_DEFERRED`**', $ledger);

        $matched = preg_match(
            '/^### Urutan pembangunan yang berlaku\R(?<table>.*?)(?=^### )/ms',
            $sequence,
            $buildTableMatch
        );
        $this->assertSame(1, $matched, 'The authoritative build-stage table could not be inspected.');
        $buildTable = $buildTableMatch['table'];

        preg_match_all('/^\| (?<stage>\d+) \|/m', $buildTable, $stageMatches);
        $this->assertSame(['4', '5', '6', '7', '8', '9', '10', '11'], $stageMatches['stage']);

        foreach ([
            '4' => ['F-039a', 'Keputusan makna `RAW`', 'SELESAI 2026-08-13'],
            '5' => ['F-038', 'Keputusan batas baca bar tak beridentitas', 'SELESAI 2026-08-13'],
            '6' => ['F-010a', 'F-027a', 'SELESAI 2026-08-13', 'immutable KSEI evidence'],
            '7' => ['F-011a', 'tier struktur pasar IDX', 'SELESAI 2026-08-13'],
            '8' => ['F-007b', 'F-026b', 'F-017b', 'F-018b', 'current-authoritative'],
            '9' => ['F-030a', 'replay target belum dijalankan'],
            '10' => ['F-030b', 'F-020', 'F-024', "bukan `'v1'`"],
            '11' => ['F-023a', 'PRE_ACTIVATION_DEFERRED'],
        ] as $stageNumber => $needles) {
            $matched = preg_match('/^\| '.preg_quote($stageNumber, '/').' \|(?<row>.*)$/m', $buildTable, $rowMatch);
            $this->assertSame(1, $matched, 'Stage '.$stageNumber.' is missing from the current sequence.');
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $rowMatch['row'], 'Stage '.$stageNumber.' is missing '.$needle.'.');
            }
            if (in_array((string) $stageNumber, ['4', '5', '6', '7', '8'], true)) {
                $this->assertStringNotContainsString('**BELUM DIMULAI**', $rowMatch['row']);
            } else {
                $this->assertStringContainsString('**BELUM DIMULAI**', $rowMatch['row']);
            }
            if ((string) $stageNumber === '8') {
                $this->assertStringContainsString('**SELESAI 2026-08-14**', $rowMatch['row']);
                $this->assertStringContainsString('15/15 current pointer', $rowMatch['row']);
            }
        }

        $this->assertStringNotContainsString('F-021', $buildTable, 'Operational activation must not block the build sequence.');
        $this->assertStringContainsString('bukan `UPDATE ... SET` atas fakta lama', $sequence);
        $this->assertStringContainsString('history lama tidak diubah', $buildTable);
        $this->assertMatchesRegularExpression('/Pelanggaran\s+harus nol untuk 18 field/', $sequence);
        $this->assertStringContainsString('history row lama tidak berubah hash maupun jumlahnya', $sequence);

        foreach ([
            '`F-039a` keputusan pada Tahap 4; `F-039b` penerapan pada Tahap 8',
            '`F-038` | keputusan dan enforcement fail-closed diselesaikan pada Tahap 5',
            '`a` perekaman authority pada Tahap 6; `b` penerapan pada Tahap 8',
            '`F-011a` perekaman tier pada Tahap 7; `F-011b` penerapan pada Tahap 8',
            '`F-030a` authoring fixture pada Tahap 9; `F-030b` eksekusi pada Tahap 10',
            '`F-023a` gate implementasi pada Tahap 11; `F-023b` evidence operasi pada `O3`',
            '`F-021a` deklarasi pada `O1`; `F-021b` propagasi pada `O2`',
        ] as $splitRule) {
            $this->assertStringContainsString($splitRule, $sequence, 'Missing independent-closure split: '.$splitRule);
        }

        $matched = preg_match('/^### Gate di luar burn-down pembangunan\R(?<gates>.*?)(?=^### )/ms', $sequence, $gateMatch);
        $this->assertSame(1, $matched, 'The external operational gate table could not be inspected.');
        foreach (['`O1` — deklarasi activation', '`O2` — propagasi marker', '`O3` — consecutive activated sessions'] as $gate) {
            $this->assertStringContainsString($gate, $gateMatch['gates']);
        }
        foreach (['F-021a', 'F-021b', 'F-023b'] as $externalFinding) {
            $this->assertStringContainsString($externalFinding, $gateMatch['gates']);
        }
        $this->assertStringContainsString(
            'COUNT(*) WHERE operational_start_date IS NULL = 0',
            $gateMatch['gates'],
            'The deferred F-021 propagation gate must preserve its original measurable purpose.'
        );
        $this->assertStringContainsString(
            'state historis sebelum boundary tidak diubah menjadi klaim fresh',
            $gateMatch['gates']
        );

        $this->assertStringContainsString(
            'Tahap berikut yang diizinkan adalah **Tahap 9 — author fixture replay independen**.',
            $sequence
        );
        $this->assertMatchesRegularExpression(
            '/Tahap 8 selesai\s+dan tidak menjalankan replay\./',
            $sequence
        );
    }

    public function test_stage_four_records_only_the_owner_raw_decision_and_keeps_implementation_open(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');

        $matched = preg_match(
            '/^## Tahap 4 — Keputusan makna `RAW` — SELESAI 2026-08-13\R(?<stage>.*?)(?=^## Tahap 5 )/ms',
            $ledger,
            $stageMatch
        );
        $this->assertSame(1, $matched, 'The authoritative Stage 4 decision section could not be inspected.');
        $stage = $stageMatch['stage'];

        foreach ([
            '`api_free/yahoo_finance` **tetap bootstrap primary EOD source**',
            'field `indicators.quote.0`',
            'tidak di-adjust, diperbaiki,',
            '/bukan klaim\s+bahwa Yahoo adalah source resmi IDX/',
            '`AS_TRADED`, `PROVIDER_BACK_ADJUSTED`, dan `UNKNOWN`',
            'Provider `adj_close` tetap diagnostic observation metadata',
            '`PROVIDER_BACK_ADJUSTED` tidak boleh di-adjust ulang',
            '/`UNKNOWN`\s+harus fail-safe sebagai held\/quarantined/',
            'provider berbayar **tidak diwajibkan pada fase sekarang**',
        ] as $decisionPart) {
            if (str_starts_with($decisionPart, '/')) {
                $this->assertMatchesRegularExpression($decisionPart, $stage, 'Stage 4 is missing owner decision part: '.$decisionPart);

                continue;
            }

            $this->assertStringContainsString($decisionPart, $stage, 'Stage 4 is missing owner decision part: '.$decisionPart);
        }

        $this->assertStringContainsString('`F-039a` **CLOSED**', $stage);
        $this->assertStringContainsString('`F-039b` di Tahap 8', $stage);
        $this->assertStringContainsString('`F-039` karena itu tetap **OPEN/PARTIAL**', $stage);
        $this->assertStringContainsString('Tahap berikut yang diizinkan adalah Tahap 5', $stage);
        $this->assertStringContainsString(
            '### HISTORICAL, SUPERSEDED — Evidence penyesuaian urutan sebelum Tahap 4 — 2026-08-13',
            $ledger
        );
        $this->assertStringNotContainsString(
            'Tahap 4 tetap **BELUM DIMULAI**',
            $ledger,
            'A superseded pre-execution status must not remain readable as the current Stage 4 state.'
        );

        foreach ([
            'Tidak ada kode produksi',
            'migration',
            'database row',
            'terms IDX',
            'fixture replay',
            'activation marker',
            '/Tahap 5\s+dan seterusnya belum dikerjakan/',
        ] as $scopeBoundary) {
            if (str_starts_with($scopeBoundary, '/')) {
                $this->assertMatchesRegularExpression($scopeBoundary, $stage, 'Stage 4 scope boundary is missing '.$scopeBoundary.'.');

                continue;
            }

            $this->assertStringContainsString($scopeBoundary, $stage, 'Stage 4 scope boundary is missing '.$scopeBoundary.'.');
        }

        $this->assertStringContainsString(
            '| `F-039` | `W12` | P0 | CLOSED 2026-08-14:',
            $ledger
        );
        $this->assertStringContainsString(
            '| 4 | Keputusan makna `RAW` — **SELESAI 2026-08-13** | `F-039a` |',
            $ledger
        );
    }

    public function test_stage_five_is_fail_closed_complete_and_cannot_be_reopened_by_history(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');

        $matched = preg_match(
            '/^## Tahap 5 — Keputusan batas baca bar tak beridentitas — SELESAI 2026-08-13\R(?<stage>.*?)(?=^## Tahap 6 )/ms',
            $ledger,
            $stageMatch
        );
        $this->assertSame(1, $matched, 'The authoritative Stage 5 section could not be inspected.');
        $stage = $stageMatch['stage'];

        foreach ([
            'tafsir historis “bar mentah bukan analytical row',
            '`NULL`, string kosong, whitespace, perbedaan case,',
            '`EodPublicationRepository` sekarang menjadi sumber keputusan tunggal',
            '`eod_bars` serta `eod_bars_history`',
            '`PRICE_PRODUCT_UNRECORDED`',
            '`CANONICAL_BAR_PRICE_PRODUCT_INVALID`',
            'Pencocokan memakai `HEX(...)`',
            'lowercase `raw`, dan padded ` RAW `',
            'seluruh 844 publication tersebut ditahan',
            'Korpus legacy tidak diberi label `RAW` dan tidak disentuh.',
            'registry runtime dari 392 menjadi 363 baris',
            'Dua puluh sembilan kode di luar scope',
            '`F-038` **CLOSED**',
            'Tahap berikut yang diizinkan adalah Tahap 6',
            'Tahap 6 belum dimulai.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $stage, 'Stage 5 closure is missing '.$needle.'.');
        }

        $matched = preg_match(
            '/^- open findings recorded by command protocol: \*\*(?<count>\d+) terbuka\*\* \((?<ids>[^\r\n)]*)\)\./m',
            $ledger,
            $openFindingMatch
        );
        $this->assertSame(1, $matched, 'The current open-finding roster could not be inspected.');
        $this->assertSame('7', $openFindingMatch['count']);
        preg_match_all('/`(?<id>F-\d+)`/', $openFindingMatch['ids'], $openIdMatches);
        $this->assertCount(7, $openIdMatches['id']);
        $this->assertNotContains('F-038', $openIdMatches['id']);

        $matched = preg_match('/^## Active findings\R(?<table>.*?)(?=^## )/ms', $ledger, $activeFindingMatch);
        $this->assertSame(1, $matched, 'The active-finding table could not be inspected.');
        $this->assertStringNotContainsString('| `F-038` |', $activeFindingMatch['table']);

        $this->assertStringContainsString(
            '## HISTORICAL, SUPERSEDED — W12 remediation `F-026` — 2026-08-11',
            $ledger
        );
        $this->assertStringContainsString(
            '| 5 | Keputusan batas baca bar tak beridentitas — **SELESAI 2026-08-13** | `F-038` |',
            $ledger
        );
        $this->assertStringContainsString(
            'next permitted implementation action: **Tahap 9 — author fixture replay independen.',
            $ledger
        );
    }

    public function test_stage_six_records_only_declared_authoritative_terms_and_leaves_application_open(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');

        $matched = preg_match(
            '/^## Tahap 6 — Rekam terms corporate action otoritatif — SELESAI 2026-08-13\R(?<stage>.*?)(?=^## Tahap 7 )/ms',
            $ledger,
            $stageMatch
        );
        $this->assertSame(1, $matched, 'The authoritative Stage 6 closure section could not be inspected.');
        $stage = $stageMatch['stage'];

        foreach ([
            'MLPT, RAJA, dan RMKE',
            'Scope ini sengaja kecil dan terukur',
            'bukan klaim bahwa 533 aksi legacy',
            '`record_only=true`',
            '`md_corporate_action_revisions`',
            '`md_source_observations`',
            '`verification_state=AUTHORITATIVE_VERIFIED`',
            '`announcement_at=NULL`',
            'URL non-HTTPS',
            'overwrite dilarang',
            '`inserted_revision_count=0`',
            '`source_observation_insert_count=0`',
            '`md_adjustment_factor_sets` | 0 | 0',
            '`md_adjustment_factors` | 0 | 0',
            '`eod_runs` | 72.777 | 72.777',
            '`eod_publications` | 64.951 | 64.951',
            '`eod_bars` | 756.329 | 756.329',
            '`eod_indicators` | 756.328 | 756.328',
            '`eod_eligibility` | 779.402 | 779.402',
            '`ba8c24bc787876481807679c130d5662472c4d14e005436c59967e89d4690b61`',
            '363→393',
            'Exact 29 kode di luar scope',
            '**364 = 360 baseline + 1 Tahap 1 + 2 Tahap 5 + 1 Tahap 6**',
            '`F-010a` dan `F-027a` **CLOSED untuk scope yang dideklarasikan**',
            'Parent `F-010` dan',
            '`F-027` tetap **OPEN/PARTIAL**',
            'Tahap berikut yang diizinkan adalah Tahap 7',
        ] as $needle) {
            $this->assertStringContainsString($needle, $stage, 'Stage 6 closure is missing '.$needle.'.');
        }

        foreach ([
            'stage_6_ksei_stock_split_terms_v1.json',
            'AuthoritativeCorporateActionTermsService',
            'verifier runtime HTTP 200/application-PDF',
            'market-data:events:record-authoritative-terms',
            'AUTHORITATIVE_TERMS_VALIDATED',
        ] as $ownedArtifact) {
            $this->assertStringContainsString($ownedArtifact, $stage, 'Stage 6 is missing owned artifact '.$ownedArtifact.'.');
        }

        $this->assertStringContainsString(
            '| 6 | Rekam terms corporate action otoritatif — **SELESAI 2026-08-13** | `F-010a` `F-027a` |',
            $ledger
        );
        $this->assertStringNotContainsString(
            '| 6 | Rekam terms corporate action otoritatif — **BELUM DIMULAI** |',
            $ledger
        );
        $this->assertStringContainsString(
            '| `F-010` | `W11` | P1 | PARTIAL — `F-010a` CLOSED pada Tahap 6',
            $ledger
        );
        $this->assertStringContainsString(
            '| `F-027` | `W12` | P0 | CLOSED 2026-08-14',
            $ledger
        );
        $this->assertStringContainsString(
            '## HISTORICAL, SUPERSEDED — W12 remediation `F-027` — 2026-08-11',
            $ledger
        );
        $this->assertMatchesRegularExpression(
            '/HISTORICAL, SUPERSEDED — W12 remediation `F-027` — 2026-08-11\R\R> \*\*STATUS HISTORIS — BUKAN AUTHORITY EVENT AKTIF\.\*\*/',
            $ledger
        );

        foreach (['tier/band/floor/tick Tahap 7', 'Tahap 8', 'fixture/replay Tahap 9–10', 'gate Tahap 11', 'activation marker'] as $deferred) {
            $this->assertStringContainsString($deferred, $stage, 'Stage 6 must explicitly defer '.$deferred.'.');
        }
    }

    public function test_stage_seven_closure_is_record_only_source_backed_and_history_clean(): void
    {
        $ledger = $this->read('docs/market_data/audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md');
        preg_match(
            '/^## Tahap 7 — Rekam tier struktur pasar IDX — SELESAI 2026-08-13\R(?<stage>.*)\z/ms',
            $ledger,
            $stageMatch
        );
        $this->assertArrayHasKey('stage', $stageMatch, 'Stage 7 closure block must exist.');
        $stage = $stageMatch['stage'];

        foreach ([
            '`IDX_REGULAR_STANDARD_EQUITY`',
            '`2023-01-02`',
            'Papan Utama, Pengembangan, dan Ekonomi Baru',
            'Papan Akselerasi dan Pemantauan Khusus dikecualikan',
            '`FAIL_CLOSED`',
            '`record_only=true`',
            'stage_7_idx_regular_market_structure_v1.json',
            '`md_exchange_market_structure_revisions`',
            '`md_exchange_price_band_tiers`',
            '`md_exchange_tick_size_tiers`',
            '`AUTHORITATIVE_VERIFIED`',
            '`AUTHORITATIVE_MARKET_STRUCTURE_VALIDATED`',
            '`inserted_revision_count=0`',
            '`unchanged_revision_count=6`',
            '`evidence_correction_revision_count=6`',
            '`source_observation_insert_count=0`',
            '`eod_runs` | 72.777 | 72.777',
            '`eod_publications` | 64.951 | 64.951',
            '`eod_bars` | 756.329 | 756.329',
            '`eod_bars_history` | 56.908.318 | 56.908.318',
            '`md_adjustment_factor_sets` | 0 | 0',
            '`md_adjustment_factors` | 0 | 0',
            '**364→365**',
            '`F-011a` **CLOSED**',
            'Parent `F-011` tetap',
            '**OPEN/PARTIAL**',
            'Tahap berikut yang diizinkan adalah Tahap 8',
            'pekerjaan itu belum dimulai di sini',
            'identity metadata manifest, bukan identity response yang benar-benar diterima',
            'enam revision nomor 2 dengan `supersedes_revision_id`',
            'observation baru dengan `supersedes_observation_id` ke evidence lama',
            '**0 evidence violation**',
            '`md_exchange_market_structure_revisions` | 12 | 6 revision nomor 2',
            '`md_source_observations` | 26 | 10 observation / 5 pasangan evidence aktif',
            'Tidak ada row lama yang diedit atau dihapus oleh koreksi.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $stage, 'Stage 7 closure is missing '.$needle.'.');
        }

        foreach ([
            'AuthoritativeExchangeMarketStructureService',
            'market-data:market-structure:record-authoritative-rules',
            '5/5 HTTP 200',
            '6 revision, 12 band tier, 5 tick tier, 10 observations',
            '5/5 evidence aktif membawa exact response identity',
            'production evidence correction: append-only 6 revision + 12 band tier + 5 tick tier + 10',
        ] as $ownedArtifact) {
            $this->assertStringContainsString($ownedArtifact, $stage, 'Stage 7 is missing owned proof '.$ownedArtifact.'.');
        }

        $this->assertStringContainsString(
            '| 7 | Rekam tier struktur pasar IDX — **SELESAI 2026-08-13** | `F-011a` |',
            $ledger
        );
        $this->assertStringNotContainsString(
            '| 7 | Rekam tier struktur pasar IDX — **BELUM DIMULAI** |',
            $ledger
        );
        $this->assertStringContainsString(
            '| `F-011` | `W11` | P1 | CLOSED 2026-08-14:',
            $ledger
        );
        $auditReport = $this->read('docs/market_data/audit/reports/AUDIT_FINAL_STATE.md');
        $this->assertStringContainsString(
            '| P1-30/F-011 band, floor, dan tick otoritatif | `CLOSED — F-011a/F-011b` |',
            $auditReport
        );
        $this->assertStringContainsString(
            'Tahap 7 merekam authority: enam revision current',
            $auditReport
        );
        $this->assertStringContainsString(
            '1.446 publication/listing binding `RESOLVED_STANDARD_BOARD`',
            $auditReport
        );
        $this->assertStringNotContainsString(
            '| P1-30 band, floor, dan tick belum bersumber dan belum effective-dated | `OPEN` |',
            $auditReport
        );
        $this->assertStringContainsString(
            '### HISTORICAL, SUPERSEDED — `F-011` — band, floor, dan tick masih konstanta',
            $ledger
        );
        $marketStructureOwner = $this->read(
            'docs/market_data/registry/Exchange_Market_Structure_Facts_LOCKED.md'
        );
        $this->assertStringNotContainsString(
            'The current in-code single scalar is recorded above as exactly that.',
            $marketStructureOwner
        );
        $this->assertStringContainsString(
            'The Stage 7 authority rows carry source references,',
            $marketStructureOwner
        );
        $this->assertStringContainsString(
            'non-authoritative and cannot inherit the recorded rows\' verification state.',
            $marketStructureOwner
        );
        $this->assertStringContainsString(
            'The current authority set is revision 2 for all six rules.',
            $marketStructureOwner
        );
        $runbook = $this->read('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');
        $this->assertStringContainsString(
            'must append an evidence-correction revision and observation pair with',
            $runbook
        );
        $this->assertStringContainsString(
            'It must never update or delete the old revision/evidence.',
            $runbook
        );
        $safetyInventory = $this->read('docs/market_data/ops/COMMAND_SURFACE_SAFETY_INVENTORY.md');
        $this->assertStringContainsString(
            'legacy evidence mismatch appends a superseding correction revision/observation pair',
            $safetyInventory
        );
        $fieldDictionary = $this->read('docs/market_data/db/DB_FIELDS_AND_METADATA.md');
        $this->assertStringContainsString(
            'A legacy observation containing manifest metadata alone cannot be',
            $fieldDictionary
        );
        $this->assertStringContainsString(
            'next permitted implementation action: **Tahap 9 — author fixture replay independen.**',
            $ledger
        );
    }

    /**
     * The production-ready claim is a decision, not a measurement, and the proof pack must state
     * the decision it actually reached. The rejected outcomes are asserted absent so a downgrade
     * cannot be left sitting next to the claim it contradicts.
     */
    /**
     * Archived source-state evidence records one settled decision per artifact.
     *
     * SUPERSEDED at W01 — stage 1: this method previously asserted that the retired claims
     * `MARKET_DATA_PRODUCTION_READY_LOCKED`, `Final source-state lock status: LOCKED`, and
     * `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT -> LOCKED` remained present in the audit
     * documents. `docs/market_data/README.md` retires those claims for the corrected
     * data-readiness baseline, so a green suite was requiring the platform to keep asserting
     * something the owner document had already withdrawn — and removing the claims from the
     * fourteen documents that carry them would have broken this test.
     *
     * Those three assertions are removed rather than adjusted, per the retirement obligation in
     * `Market_Data_Strategy_Implementation_Blueprint_LOCKED.md` steps 3 and 7: a stage that
     * rejects a behaviour retires the proof that locks it, in the same stage.
     *
     * What remains are archived execution facts — runtime parity, provider smoke, and audit-doc
     * synchronisation — which record what was executed and claim nothing about current readiness.
     */
    public function test_archived_source_state_evidence_states_a_single_settled_decision(): void
    {
        $proofPack = $this->read('docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->read('docs/market_data/audit/FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md');

        $this->assertStringContainsString('Decision: `OPS_RUNTIME_PARITY_PASSED`', $proofPack);
        $this->assertStringNotContainsString('Decision: `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED`', $proofPack);
        $this->assertStringContainsString('FINAL_PROVIDER_SMOKE=PASSED', $proofPack);
        $this->assertStringContainsString('FINAL_AUDIT_DOCS_SYNCHRONIZED', $tracker.$proofPack);

        // A provisional claim scoped to one delivered archive must not survive the final lock.
        $this->assertStringNotContainsString('Full market-data production-ready: `CLAIMED_FOR_THIS_SOURCE_ZIP`', $inventory.$proofPack);
    }

    private function firstNonEmptyLineAfter(string $document, string $heading): string
    {
        $position = strpos($document, $heading);
        $this->assertNotFalse($position, $heading.' heading must exist.');

        foreach (preg_split('/\R/', substr($document, $position + strlen($heading))) as $line) {
            if (trim($line) !== '') {
                return trim($line);
            }
        }

        $this->fail('No non-empty line found after '.$heading.'.');
    }

    private function canonicalOverrideDate(string $document): string
    {
        $pattern = '/^## CURRENT CANONICAL(?: AUDIT)? OVERRIDE .* (?P<date>\d{4}-\d{2}-\d{2})$/mu';
        $this->assertMatchesRegularExpression($pattern, $document);
        preg_match($pattern, $document, $match);

        return trim($match['date']);
    }
}
