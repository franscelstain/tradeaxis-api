# REPLAY HISTORICAL DETERMINISM HARDENING INVENTORY

[SESSION]
- Name: Replay Historical Determinism Hardening
- Status: READY_FOR_LOCAL_RUNTIME_VALIDATION
- Last Updated: 2026-05-17
- Scope: hardening edge case untuk replay actual-state historical publication setelah current pointer berpindah.

[BOUNDARY]
- Ini not replay determinism umum; Replay Determinism tetap menjadi kontrak existing untuk fixture schema, deterministic comparison, expected/actual context, reason-coded mismatch, volatile-field exclusion, dan replay artifact persistence.
- Ini bukan pelemahan read-side consumer resolver; consumer read resolver tetap current-pointer-only.
- Evidence Historical Lineage Completeness tetap dipakai sebagai source proof untuk selector-scoped historical publication audit, bukan sebagai consumer read path.

[RUNTIME_ENVIRONMENT]
- Container PHP version: PHP 8.4.16.
- Container PHPUnit status: BLOCKED_CONTAINER_RUNTIME_ENV karena extension `dom`, `mbstring`, `xml`, dan `xmlwriter` tidak tersedia.
- Operator-local PHP version: PHP 7.4.33 expected from prior runtime baseline.
- Operator-local PHPUnit version: PHPUnit 9.6.34 expected from prior runtime baseline.
- Required PHP extensions available locally: dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter.
- Runtime authority for DONE/LOCKED: operator-local PHPUnit output.

## Replay Actual Resolver Matrix

| Replay Surface | Entrypoint | Actual State Resolver Used | Current Pointer Required | Historical Sealed Allowed | Publication Context Source | Artifact Scope | Status |
|---|---|---|---:|---:|---|---|---|
| Verify replay | `market-data:replay:verify` -> `ReplayVerificationService::verifyRunAgainstFixture()` | `resolvePublicationForReplayActualState()` | Current context only | Yes | Expected fixture selector + evidence audit resolver | `publication:<id>` | PATCHED |
| Smoke replay | `market-data:replay:smoke` -> replay verification service | `resolvePublicationForReplayActualState()` | Current context only | Yes | Fixture expected context | Publication scoped | PATCHED |
| Backfill replay | `market-data:replay:backfill` -> replay verification service | `resolvePublicationForReplayActualState()` | Current context only | Yes | Fixture expected context | Publication scoped | PATCHED |
| Fixture generation | `generateFixtureFromRun()` | existing `resolvePublicationForRun()` | Yes | No | Current readable run publication | Current publication scoped | CURRENT_POINTER_DEPENDENT_BY_DESIGN |
| Replay evidence export | `market-data:evidence:export --replay_id` | stored replay actual/expected context + evidence audit context | No for historical replay result | Yes | replay result context | Publication scoped | AUDIT_HISTORICAL_SAFE |

## Current vs Historical Replay Context Matrix

| Resolver | File | Method | Used By | Current Pointer Required | Historical Sealed Allowed | Validation Required | Status |
|---|---|---|---|---:|---:|---|---|
| Replay current actual resolver | `ReplayVerificationService.php` | `resolvePublicationForReplayActualState()` current branch | Current replay verification | Yes | No | SUCCESS + READABLE + current pointer | ENFORCED |
| Replay historical actual resolver | `ReplayVerificationService.php` | `resolvePublicationForReplayActualState()` historical branch | Historical replay verification | No | Yes | explicit selector, lineage, sealed publication, artifact scope | PATCHED |
| Evidence audit resolver | `EodEvidenceRepository.php` | `resolvePublicationForEvidenceAudit()` | Replay historical wrapper and evidence export | No | Yes | run/publication mirror, trade date, sealed, coverage, hashes | REUSED |
| Consumer resolver | `EodPublicationRepository.php` | `resolveCurrentReadablePublicationForTradeDate()` / `findReadableCurrentPublicationForRun()` | Consumer/read-side path | Yes | No | current pointer readable only | UNCHANGED |

## Historical Replay Risk Matrix

| File | Method | Pattern | Replay Actual Path? | Current Pointer Dependency | Historical Risk | Action | Status |
|---|---|---|---:|---:|---|---|---|
| `ReplayVerificationService.php` | old `resolvePublicationForRun()` used by verify | `findReadableCurrentPublicationForRun()` | Yes | Yes | Historical publication A could fail or compare against current B after pointer moved | Added replay actual resolver with historical branch | PATCHED |
| `ReplayVerificationService.php` | `buildActualReplayState()` | current-only context fields | Yes | Partial | Actual output could not explicitly label historical scope | Added `actual_replay_resolution_context` and artifact scope | PATCHED |
| `ReplayVerificationService.php` | reason code mapping | generic replay mismatch | Yes | No | Historical/current mismatch could be ambiguous | Added historical-aware reason mapping | PATCHED |
| `EodPublicationRepository.php` | consumer resolver | current pointer read | No consumer only | Yes by design | Risk only if weakened | No consumer change | UNCHANGED |

## Artifact Scope Matrix

| Artifact Type | Current Replay Actual Source | Historical Replay Actual Source | Publication Scoped? | Missing Artifact Behavior | Status |
|---|---|---|---:|---|---|
| Reason code counts | `dominantReasonCodes()` | `dominantReasonCodesForEvidencePublication()` | Yes | reason-coded mismatch/failure | PATCHED |
| Eligibility rows | `exportEligibilityRows()` | `exportEligibilityRowsForEvidencePublication()` via historical evidence path | Yes | no current fallback | PATCHED |
| Hash/manifest context | run/publication hash fields | selected publication/run evidence context | Yes | reason-coded mismatch | PATCHED |
| Coverage context | run/publication coverage fields | selected run/publication coverage basis | Yes | reason-coded mismatch | PATCHED |

## Evidence Historical Reuse Matrix

| Evidence Historical Component | Reused By Replay? | Replay-Specific Wrapper Needed? | Risk | Action |
|---|---:|---:|---|---|
| `resolvePublicationForEvidenceAudit()` | Yes | Yes | Direct use could blur replay vs evidence wording | Wrapped by `resolvePublicationForReplayActualState()` |
| `dominantReasonCodesForEvidencePublication()` | Yes | No | None if publication_id passed explicitly | Used for historical replay reason codes |
| `exportEligibilityRowsForEvidencePublication()` | Yes | No | None if publication_id passed explicitly | Used for historical replay eligibility count |
| Evidence output labels | Partially | Yes | Evidence field names are not replay field names | Replay context maps to `replay_*` fields |

## Reason Code Matrix

| Reason Code | Existing / New | Registry Updated | Seed Updated | Used By | Status |
|---|---|---:|---:|---|---|
| `REPLAY_HISTORICAL_PUBLICATION_RESOLVED` | New | Yes | Yes | historical actual context | ADDED |
| `REPLAY_CURRENT_PUBLICATION_RESOLVED` | New | Yes | Yes | current actual context | ADDED |
| `REPLAY_NO_PUBLICATION_ACTUAL_STATE` | New | Yes | Yes | no-publication actual context | ADDED |
| `REPLAY_HISTORICAL_PUBLICATION_MISSING` | New | Yes | Yes | evidence exception mapping | ADDED |
| `REPLAY_HISTORICAL_PUBLICATION_UNSEALED` | New | Yes | Yes | evidence exception mapping | ADDED |
| `REPLAY_PUBLICATION_RUN_MISMATCH` | New | Yes | Yes | lineage/mirror mismatch | ADDED |
| `REPLAY_HISTORICAL_ARTIFACT_SCOPE_MISMATCH` | New | Yes | Yes | artifact scope mismatch | ADDED |
| `REPLAY_EXPECTED_HISTORICAL_ACTUAL_CURRENT_MISMATCH` | New | Yes | Yes | current vs historical mode mismatch | ADDED |
| `REPLAY_CURRENT_POINTER_MOVED_HISTORICAL_VALID` | New | Yes | Yes | historical proof semantics | ADDED |

## Patch Matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| Replay verify was current-pointer dependent | `ReplayVerificationService.php` | Added `resolvePublicationForReplayActualState()` with historical selector branch | Consumer resolver unchanged; historical proof uses evidence audit resolver | `ReplayVerificationServiceTest`, static guard | PATCHED |
| Replay actual output did not clearly label historical mode | `ReplayVerificationService.php` | Added expected/actual replay resolution contexts | Deterministic comparison extended, not loosened | `ReplayHistoricalDeterminismHardeningStaticGuardTest` | PATCHED |
| Historical artifacts needed publication-scoped path | `ReplayVerificationService.php` | Uses evidence publication-scoped reason/eligibility methods | No raw/current/latest fallback | static guard | PATCHED |
| Missing historical reason code registry | registry docs/seed | Added replay historical reason codes | Registry/seed synchronized | static guard | PATCHED |

## Validation Matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l app/Application/MarketData/Services/ReplayVerificationService.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l tests/Unit/MarketData/ReplayVerificationServiceTest.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l tests/Unit/MarketData/ReplayHistoricalDeterminismHardeningStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS |
| `php vendor/bin/phpunit --version` | blocked by missing extensions | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |

[FINAL_STATUS]
- READY_FOR_LOCAL_RUNTIME_VALIDATION.
- Tidak boleh DONE/LOCKED sampai operator-local targeted PHPUnit dan full `tests/Unit/MarketData` PASS.


## Local Feedback Follow-up Patch - 2026-05-17

| Finding | Source | Patch | Status |
|---|---|---|---|
| `ReplayHistoricalDeterminismHardeningStaticGuardTest::test_historical_replay_does_not_weaken_consumer_current_pointer_resolver` asserted the service-call token against `EodPublicationRepository.php` instead of checking the repository method signature. | Operator-local PHPUnit feedback | Guard now checks `public function findReadableCurrentPublicationForRun($runId, $tradeDate)` in the repository and keeps the service-call assertion inside `ReplayVerificationService.php`. | PATCHED |
| `AuditDocsSynchronizationStaticGuardTest::test_reason_code_registry_and_seed_are_synchronized` still expected 315 reason codes after replay historical hardening added 9 synchronized codes. | Operator-local PHPUnit feedback | Expected synchronized reason-code count updated to 324. Registry and seed sets remain identical. | PATCHED |

[POST_PATCH_STATIC_VALIDATION]
- `php -l tests/Unit/MarketData/ReplayHistoricalDeterminismHardeningStaticGuardTest.php` -> No syntax errors detected.
- `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> No syntax errors detected.
- Full operator-local PHPUnit rerun is still required before DONE/LOCKED.
