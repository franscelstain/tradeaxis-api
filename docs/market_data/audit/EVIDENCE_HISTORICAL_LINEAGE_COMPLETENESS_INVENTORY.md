# EVIDENCE HISTORICAL LINEAGE COMPLETENESS INVENTORY

[SESSION] Evidence Historical Lineage Completeness
[STATUS] DONE / LOCKED_LOCAL_PHPUNIT_PASS
[LAST_UPDATED] 2026-05-14

## Scope

This inventory records the hardening that separates the consumer read resolver from the evidence audit resolver. The consumer read resolver tetap current-pointer-only. The evidence audit resolver is selector-scoped and lineage-validated so a historical sealed publication can be proven after it is no longer the current pointer.

This session does not weaken the read-side consumer contract. Consumer/API/dashboard/session snapshot paths must still use the current readable publication pointer through `resolveCurrentReadablePublicationForTradeDate()` / `findReadableCurrentPublicationForRun()` and must not read historical non-current publication data as consumer data.

## Existing contract owner

| Existing Contract / Test / Doc | Role | Current Status | Relevance | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `READ_SIDE_CONSUMER_CURRENT_POINTER_CONTRACT` | Consumer read source of truth | LOCKED prior session | Must remain current-pointer-only | Do not weaken |
| `RUN_PUBLICATION_POINTER_LINKAGE_CONTRACT` | Run → publication → pointer linkage | LOCKED prior session | Defines mirror/linkage invariants | Extend evidence proof only |
| `PRODUCTION_VALIDATION_CONTRACT` | Runtime proof authority | LOCKED prior session | DONE/LOCKED requires operator-local proof | Preserve status rules |
| `EvidenceExportCompletenessStaticGuardTest` | Existing evidence completeness guard | ENFORCED prior session | Existing evidence output sections | Extend with historical lineage guard |
| `MarketDataEvidenceExportService` | Evidence export surface | PATCHED this session | Needed historical resolver usage | Patch evidence path only |
| `EodEvidenceRepository` | Evidence repository | PATCHED this session | Owns audit resolver | Extend safely |
| `EodPublicationRepository` | Consumer/current pointer resolver | UNCHANGED consumer contract | Must stay current-pointer-only | Do not change consumer resolver |

## Evidence selector resolver matrix

| Selector | Entrypoint | Resolver Used | Current Pointer Required | Historical Sealed Allowed | Lineage Validation | Artifact Scope | Status |
|---|---|---:|---:|---:|---|---|---|
| `run_id` | `market-data:evidence:export --run_id=...` | `EodEvidenceRepository::resolvePublicationForEvidenceAudit` | false for historical, true only when current pointer matches | yes | run/publication mirror, trade date, seal, coverage, artifact hash | `PUBLICATION_SCOPED` | PATCHED |
| `correction_id` | `market-data:evidence:export --correction_id=...` | correction record + `buildHistoricalPublicationAuditProof()` | false for baseline historical proof | yes | baseline/candidate publication proof reason-coded | `PUBLICATION_SCOPED` | PATCHED |
| `replay_id` + explicit `trade_date` | `market-data:evidence:export --replay_id=... --trade_date=...` | replay metric context | false when metric publication is non-current | yes as replay audit context | expected/actual context carries historical lineage fields | `PUBLICATION_SCOPED` | PATCHED |
| `publication_id` | not exposed as command selector in this ZIP | repository audit resolver supports explicit publication_id | false for historical | yes | selector/publication/run validation | `PUBLICATION_SCOPED` | REPOSITORY_READY |

## Consumer vs evidence resolver matrix

| Resolver | File | Method | Used By | Current Pointer Required | Historical Sealed Allowed | Validation Required | Status |
|---|---|---|---|---:|---:|---|---|
| Consumer resolver | `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` | `resolveCurrentReadablePublicationForTradeDate()` | consumer/API/session snapshot/read paths | yes | no | pointer row, SEALED, SUCCESS, READABLE, coverage PASS, run mirror | UNCHANGED |
| Consumer run resolver | `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` | `findReadableCurrentPublicationForRun()` | consumer-like current run resolution | yes | no | pointer row and current mirrors | UNCHANGED |
| Evidence audit resolver | `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | `resolvePublicationForEvidenceAudit()` | evidence export, correction proof helper | selector-scoped; not required for historical | yes | publication exists, selector matches, run/publication mirror, trade date, seal, coverage telemetry, artifact hashes | PATCHED |
| Evidence artifact export | `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | `exportEligibilityRowsForEvidencePublication()` | evidence CSV export | no | yes | `publication_id` scoped query; history table for non-current | PATCHED |

## Historical publication risk matrix

| File | Method | Pattern | Evidence Path? | Current Pointer Dependency | Historical Risk | Action | Status |
|---|---|---|---:|---:|---|---|---|
| `MarketDataEvidenceExportService.php` | `resolvePublicationForRun()` | previously current-readable resolver | yes | yes before patch | historical sealed run could not be proven after replacement | switched to evidence audit resolver | PATCHED |
| `EodEvidenceRepository.php` | `dominantReasonCodes()` / `exportEligibilityRows()` | current pointer readable context | yes before patch | yes before patch | historical evidence CSV/reason codes could be empty due current pointer dependency | added evidence-specific publication-scoped methods | PATCHED |
| `MarketDataEvidenceExportService.php` | `buildPointerContext()` | assumed any publication means current pointer | yes | implicit before patch | historical evidence could be labeled as current | added historical pointer status/reason | PATCHED |
| `exportReplayEvidence()` | replay output | replay metric only | yes | no current lookup | historical context fields incomplete | added replay publication audit context fields | PATCHED |
| `exportCorrectionEvidence()` | correction output | baseline/candidate ids | yes | no current lookup | proof not lineage-validated | added historical baseline/candidate proof | PATCHED |

## Artifact scope matrix

| Artifact Type | Current Evidence Source | Historical Evidence Source | Publication Scoped? | Missing Artifact Behavior | Status |
|---|---|---|---:|---|---|
| eligibility CSV | `eod_eligibility` when current and history absent | `eod_eligibility_history` for non-current | yes | empty export; no current fallback for historical | PATCHED |
| dominant reason codes | run events + current publication-scoped eligibility | run events + historical publication-scoped eligibility | yes | run event reason codes still exported | PATCHED |
| manifest/hash proof | `buildManifestByPublicationId(publication_id)` | same explicit publication id | yes | audit resolver blocks missing hashes | PATCHED |
| correction baseline/candidate | publication ids from correction lineage | `resolvePublicationForEvidenceAudit(publication_id/run_id)` | yes | reason-coded failed proof | PATCHED |
| replay expected/actual | replay metric context | replay metric context | yes | context marked no publication if missing | PATCHED |

## Correction / replay lineage matrix

| Surface | Required Historical Lineage Fields | Present? | Gap | Action |
|---|---|---:|---|---|
| correction evidence | baseline publication/run, candidate publication/run, seal state, current flag, scope, reason code | yes | prior output lacked resolver proof | added `baseline_historical_publication_proof` and `candidate_historical_publication_proof` |
| replay evidence | actual/expected publication id, run id, version, current flag, resolution mode, pointer requirement, artifact scope | yes | replay context did not label historical publication | added `buildReplayPublicationAuditContext()` |
| run evidence | resolution mode, selector, current pointer status, artifact scope, coverage basis, lineage status | yes | current resolver dependency | added evidence audit resolver output fields |

## Patch matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| Run evidence depended on current pointer resolver | `MarketDataEvidenceExportService.php` | `resolvePublicationForRun()` now uses `EodEvidenceRepository::resolvePublicationForEvidenceAudit()` | consumer resolver unchanged | unit test + static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Historical artifact export was current-readable scoped | `EodEvidenceRepository.php` | added `dominantReasonCodesForEvidencePublication()`, `exportEligibilityRowsForEvidencePublication()`, `evidenceEligibilityQuery()` | explicit `publication_id`; history table for non-current | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Historical publication output could look like current | `MarketDataEvidenceExportService.php` | added resolution mode/current pointer status/historical flags | explicit audit labels | unit test added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Correction proof lacked resolver validation | `MarketDataEvidenceExportService.php` | added `buildHistoricalPublicationAuditProof()` | reason-coded failure, no fallback | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Replay proof lacked historical lineage labels | `MarketDataEvidenceExportService.php` | added replay publication audit context fields | output-only, no consumer impact | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Audit docs lacked session inventory | `docs/market_data/audit/**` | added inventory and audit entries | append-only | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |

## Validation matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php -l tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php -l tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php vendor/bin/phpunit --version` | blocked by missing `dom`, `mbstring`, `xml`, `xmlwriter` | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |
| `vendor/bin/phpunit tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php` | OK | 5 | 51 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK | 52 | 906 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK | 45 | 743 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | OK | 68 | 1336 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` | OK | 103 | 1252 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` | OK | 79 | 1147 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"` | OK | 57 | 426 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"` | OK | 13 | 258 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` | OK | 49 | 359 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` | OK | 91 | 1450 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK | 135 | 2952 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 403 | 5542 | PASS_OPERATOR_LOCAL |

## Runtime environment

- Container PHP version: PHP 8.4.16.
- Container PHPUnit status: `BLOCKED_CONTAINER_RUNTIME_ENV` because PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are missing.
- Operator-local runtime authority: completed and used for DONE/LOCKED.
- Expected operator-local baseline from prior sessions: PHP 7.4.33, PHPUnit 9.6.34, with `dom`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `xml`, `xmlreader`, `xmlwriter` available.

## Remaining risk

Status is DONE/LOCKED by operator-local runtime validation. Container PHPUnit remains blocked by missing PHP extensions, but operator-local PHP/PHPUnit is the runtime authority. READY_FOR_LOCAL_RUNTIME_VALIDATION is retained in this inventory only as the prior transition state before local proof.

## Local commands completed

```text
vendor/bin/phpunit tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"
vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"
vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"
vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"
vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"
vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"
vendor/bin/phpunit tests/Unit/MarketData
```

## Final closure

Operator-local validation completed after the audit-doc synchronization fix:

- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> `OK (135 tests, 2952 assertions)`.
- `vendor/bin/phpunit tests/Unit/MarketData` -> `OK (403 tests, 5542 assertions)`.

## Final rule

Evidence export may resolve historical sealed publication only through explicit selector-scoped audit resolution. It must never use current pointer fallback, latest publication fallback, raw/staging shortcut, or `MAX(date)` style resolution for historical proof. Consumer read resolver tetap current-pointer-only.
