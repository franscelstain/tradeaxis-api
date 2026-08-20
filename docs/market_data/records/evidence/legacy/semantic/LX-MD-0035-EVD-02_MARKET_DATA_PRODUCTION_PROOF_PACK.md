# Legacy Semantic Extract — LX-MD-0035-EVD-02

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `EVIDENCE`
- Source range: `L38-L136`
- Extract body SHA1: `9CAC54B5DC45DC4210C03651085EAF874C4E8BF9`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-06-10 Backfill Lifecycle Benchmark Recovery Proof

The API backfill lifecycle for `2026-06-09` exposed a pipeline-order defect: benchmark ingest could return `RUN_SOURCE_NO_VALID_DATA` and hold the run before 948 available equity rows reached equity ingest. The pipeline was corrected so equity ingest precedes benchmark ingest and benchmark-source unavailability is non-blocking for equity publication.

Final operator proof:
- command: `php artisan market-data:backfill:lifecycle 2026-06-09 2026-06-09 --source_mode=api --with-evidence --with-replay -vvv`
- run: `run_id=37919`
- result: `import=SUCCESS`, `coverage=PASS`, `promote=SUCCESS`, `evidence=EXPORTED`, `fixture=GENERATED`, `replay=VERIFIED`, `readable=YES`
- equity rows: 948 accepted/written, zero invalid
- indicators: 948 equity indicator rows; benchmark indicator stage wrote 12 rows with zero invalid
- eligibility: 948 rows
- effective date: `2026-06-09`, no fallback
- current publication pointer: `publication_id=38186`, `run_id=37919`, version 1, sealed at `2026-06-10 21:07:07`
- regression suite: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (641 tests, 9554 assertions)

The command-level source acquisition remained `PARTIAL_SUCCESS` because one warmup/window request failed, but target-date acquisition and canonical ingest were complete for 948/948 equity tickers. This did not weaken target-date coverage or publication correctness.

Decision: `VALIDATED_RUNTIME_PROVEN_PUBLICATION_POINTER_CONFIRMED`. No further manual test is required for this defect.

## 1. Environment Baseline

| Item | Value |
|---|---|
| Audit date | 2026-05-22 |
| Runtime PHP | PHP 7.4.33 |
| Runtime PHPUnit | PHPUnit 9.6.34 |
| Runtime artisan | Laravel Framework Lumen (8.3.4) |
| Supported operator proof PHP | PHP 7.4.33 |
| Supported operator proof PHPUnit | PHPUnit 9.6.34 |
| Required supported extensions | `dom`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `xml`, `xmlreader`, `xmlwriter` |
| Lumen context | Lumen 8.3.4 according to runtime command output |
| Source artifact root | `storage/app/market-data/**` |
| Runtime limitation | Yahoo/PublicApi remains upstream-dependent; current embedded safe provider smoke is PASSED with HTTP 200 / `PROVIDER_SMOKE_OK` while all non-destructive flags remain false. |

## 2. Production Validation Matrix

| Area | Contract | Code | Test | Runtime | Audit Ledger | Status | Blocker? |
|---|---|---|---|---|---|---|---|
| coverage policy | LOCKED | Enforced by coverage gate and candidate scope | Static/unit proof recorded | PASS/FAIL/NOT_EVALUABLE behavior recorded | Synced | PASS | no |
| DB schema | LOCKED | Migration + SQLite/schema mirror present | Schema/static proof recorded | Migration proof recorded operator-local | Synced | PASS | no |
| read-side | LOCKED | Pointer/current resolver contract enforced | Anti-bypass proof recorded | No-readable/fallback behavior recorded | Synced | PASS | no |
| evidence export | LOCKED | Run/correction/replay selectors implemented | Evidence filters recorded | `run_id=33`, `correction_id=3`, replay proof admitted | Synced | PASS | no |
| replay | LOCKED | Current + historical audit resolution implemented | Replay filters recorded | `replay_id=15`, smoke/backfill PASS, historical replay `replay_id=8` | Synced | PASS | no |
| correction | LOCKED | Request/approve/run/failed/unchanged lifecycle guarded | Correction filters recorded | correction `3`, failed correction `4`, correction replay MATCH | Synced | PASS | no |
| ops commands | LOCKED + provider-smoke overlay | 21 public commands registered/help-renderable | Command/Ops/StaticGuard proof recorded | Fresh success/held/failed/conflict/repair/snapshot/evidence/replay matrix plus provider-smoke safe-mode PASS proof | Synced | PASS | no |
| hash/seal | LOCKED | SHA-256 hashes + seal state enforced | Hash/seal static proof recorded | Stage chain and promote proof include hashes + SEALED state | Synced | PASS | no |
| audit docs | LOCKED for final sync | Governance guarded | AuditDocs proof recorded and static guard expectations synchronized | Current proof pack consumed by final lock | Synced | PASS | no |

## 3. Test Proof

Current source-state operator-local proof recorded in `LUMEN_IMPLEMENTATION_STATUS.md` and `LUMEN_CONTRACT_TRACKER.md`:

| Command | Result |
|---|---|
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` | OK (57 tests, 341 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php` | OK (11 tests, 60 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php` | OK (5 tests, 89 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` | OK (10 tests, 204 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | OK (8 tests, 107 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` | OK (6 tests, 114 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` | OK (97 tests, 1009 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Ops"` | OK (74 tests, 616 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Operational"` | OK (11 tests, 211 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "RuntimeProof"` | OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | OK (10 tests, 404 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (176 tests, 4124 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (511 tests, 7871 assertions) |

Sandbox validation result for this audit: `BLOCKED_CONTAINER_RUNTIME_ENV`, not counted as runtime PASS.

## 4. Command Registry Proof

Command registry artifact: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/command-output/final-list-market-data.txt`.

Total registered market-data commands proven in current runtime: 21.

- `market-data:audit:hash`
- `market-data:backfill`
- `market-data:correction:approve`
- `market-data:correction:request`
- `market-data:correction:run`
- `market-data:current-publication:repair`
- `market-data:daily`
- `market-data:dataset:seal`
- `market-data:eod-bars:ingest`
- `market-data:eod-eligibility:build`
- `market-data:eod-indicators:compute`
- `market-data:evidence:export`
- `market-data:promote`
- `market-data:provider:smoke`
- `market-data:replay:backfill`
- `market-data:replay:fixture:generate`
- `market-data:replay:smoke`
- `market-data:replay:verify`
- `market-data:run:finalize`
- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`


<!-- LEGACY_EXTRACT_BODY_END -->
