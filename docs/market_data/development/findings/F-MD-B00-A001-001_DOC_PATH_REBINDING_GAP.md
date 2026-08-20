# F-MD-B00-A001-001 — Documentation architecture normalization left runtime and test path bindings unmigrated

- Status: `OPEN`
- Severity: `P0`
- Stage / Attempt / Baseline / Epoch: `MD-B00` / `MD-B00-A001` / `MD-B00-A001-BL001` / `MD-REBASELINE-20260820-001`
- Detected at source revision: `dd6ca2a2e56ad4b1bef30467209d6c592eb572f9`
- Owning stages for remediation: `MD-B03` (schema/seed/test harness), `MD-B19` (ops command surface), `MD-B21` (convergence)

## Finding

`D-MD-20260820-01` and `D-MD-20260820-02` relocated and split every pre-refactor Market Data document. The documentation side was completed and sealed, but no side of the change was applied to the PHP that binds to those documents by path. Every one of the 45 distinct `docs/market_data/...` paths still referenced from executable code is now dead — zero resolve.

Both decisions were issued without a `CI-MD-*` change impact declaration, so the `tests` / `runtime` / `schema` impact required by `CHANGE_IMPACT_DECLARATION_STANDARD.md` was never declared. That omission is the mechanism by which a documentation-only refactor reached production code.

## Reachable consequences (verified, not inferred)

1. **Clean install is broken.** `database/migrations/2026_03_22_000003_create_market_data_core_schema.php:11` reads `base_path('docs/market_data/db/Database_Schema_MariaDB.sql')` and throws `RuntimeException` when absent. The file now lives at `development/implementation/db/Database_Schema_MariaDB.sql`. `php artisan migrate` on an empty database cannot pass migration 3 of 62.
2. **Reason-code seeding is broken.** `database/seeders/MarketData/MarketDataReasonCodesSeeder.php:12` reads `docs/market_data/registry/Reason_Codes_Seed.sql` and throws. `eod_reason_codes` is never populated.
3. **Three operator commands have a dead default manifest**: `market-data:market-structure:record-authoritative-rules`, and the corporate-action-terms and trading-status equivalents, default to `docs/market_data/evidence/...` manifests now under `records/evidence/legacy/...`. The argument is overridable, so these are degraded rather than unusable.
4. **40 of 169 test files cannot execute their assertions**, producing 26 errors and 108 failures out of 1488 tests.
5. **The schema drift detector is down.** `tests/Support/UsesMarketDataSqlite.php` builds a hand-written 1523-line SQLite mirror rather than deriving it from the canonical schema document. The only tests that cross-check mirror against canonical schema — `MarketDataSqliteSchemaSyncTest`, `PublishedColumnHashCoverageTest`, `ArtifactIntegrityPolicyTest` — are themselves disabled by this same dead path. There is currently no enforcement that the test schema matches the authoritative schema, which means the 1354 passing tests run against an unverified schema.

## Two distinct remediation classes

Repointing is only valid for one of them.

**Class R — relocated (19 paths).** The target document still exists as a live document. A path rebind is sufficient and legitimate.

| Old path in code | Current primary path | Current verification effect |
|---|---|---|
| `db/Database_Schema_MariaDB.sql` | `development/implementation/db/Database_Schema_MariaDB.sql` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `db/DB_FIELDS_AND_METADATA.md` | `development/implementation/db/DB_FIELDS_AND_METADATA.md` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `db/MARKET_DATA_DICTIONARY.md` | `development/implementation/db/MARKET_DATA_DICTIONARY.md` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `registry/Reason_Codes_Seed.sql` | `development/implementation/db/registry/Reason_Codes_Seed.sql` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `backtest/Replay_Results_Schema_MariaDB.sql` | `development/implementation/db/backtest/Replay_Results_Schema_MariaDB.sql` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `ops/OPERATIONAL_RUNBOOK.md` | `development/implementation/ops/OPERATIONAL_RUNBOOK.md` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `ops/commands/README.md` | `development/implementation/ops/commands/README.md` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `tests/Test_Implementation_Guidance_LOCKED.md` | `development/implementation/tests/specs/Test_Implementation_Guidance_LOCKED.md` | `NOT_ASSESSED_REVALIDATION_REQUIRED` |
| `registry/Reason_Codes_Registry.md` | `authority/strategy/registry/Reason_Codes_Registry.md` | `CURRENT_AUTHORITY` |
| `registry/Platform_Config_Registry_LOCKED.md` | `authority/strategy/registry/Platform_Config_Registry_LOCKED.md` | `CURRENT_AUTHORITY` |
| `registry/Exchange_Market_Structure_Facts_LOCKED.md` | `authority/strategy/registry/Exchange_Market_Structure_Facts_LOCKED.md` | `CURRENT_AUTHORITY` |
| `book/Coverage_Edge_Cases_Contract_LOCKED.md` | `authority/strategy/book/Coverage_Edge_Cases_Contract_LOCKED.md` | `CURRENT_AUTHORITY` |
| `book/Coverage_Gate_Enforcement_Contract_LOCKED.md` | `authority/strategy/book/Coverage_Gate_Enforcement_Contract_LOCKED.md` | `CURRENT_AUTHORITY` |
| `book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` | `authority/strategy/book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` | `CURRENT_AUTHORITY` |
| `book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md` | `authority/strategy/book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md` | `CURRENT_AUTHORITY` |
| `book/Yahoo_Finance_Bootstrap_Source_Strategy.md` | `authority/strategy/book/Yahoo_Finance_Bootstrap_Source_Strategy.md` | `CURRENT_AUTHORITY` |
| `ops/Audit_Evidence_Pack_Contract_LOCKED.md` | `authority/strategy/ops/Audit_Evidence_Pack_Contract_LOCKED.md` | `CURRENT_AUTHORITY` |
| `audit/AUDIT_UPDATE_GOVERNANCE.md` | `records/history/archive/audit/AUDIT_UPDATE_GOVERNANCE.md` | `HISTORICAL_ONLY` |
| `evidence/market_structure/stage_7_idx_regular_market_structure_v1.json` | `records/evidence/legacy/market_structure/stage_7_idx_regular_market_structure_v1.json` | `HISTORICAL_ONLY` |

**Class S — split and sealed (26 paths).** The composite source was decomposed into role-pure `LX-MD-*` extracts under `D-MD-20260820-02` and the physical original was removed. There is no single repoint target: `audit/LUMEN_IMPLEMENTATION_STATUS.md` became 24 extracts, `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md` 24, `audit/LUMEN_CONTRACT_TRACKER.md` 23, `audit/PRODUCTION_VALIDATION_INVENTORY.md` 23, and so on across `LS-MD-0003/0004/0019/0021/0022/0025/0027/0030/0031/0033/0034/0035/0036/0037/0038/0039/0040/0043/0044/0174/0197/0201/0208/0239/0243/0252`.

Every one of those extracts is registered `HISTORICAL_ONLY` with `current_proof_eligible=NO`.

## Required outcome

- Class R: rebind the path in code, under the owning stage, with a test that fails when the path is dead.
- Class S: **do not repoint.** A test that asserts the content of a `HISTORICAL_ONLY` extract is asserting a legacy verdict, which `CURRENT_VERIFICATION_REBASELINE_STANDARD.md` forbids as current proof. These tests must be retired or rewritten against current authority under their owning stage. Repointing them at `records/evidence/legacy/semantic/` or `records/history/archive/semantic/` would smuggle inherited PASS into the current epoch and must be rejected in review.
- Prevent recurrence: an executable check that no path under `docs/market_data/` referenced from `app/`, `database/`, `config/`, or `tests/` is dead. Absent that check, the next relocation reintroduces this finding silently.
- Class R rebinding of `Database_Schema_MariaDB.sql` must land before the schema drift detector can be trusted again.

## Related

- Supersedes nothing. Caused by `D-MD-20260820-01` and `D-MD-20260820-02`.
- Interacts with `F-MD-B00-A001-002` (governance tooling that reports PASS without executing).
