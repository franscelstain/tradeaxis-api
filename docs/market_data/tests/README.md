# Market-Data Test and Fixture Specifications

## Role

Folder ini menetapkan proof yang harus dibangun untuk membuktikan corrected market-data data-readiness strategy. Weekly Swing adalah initial consumer compatibility profile, bukan sumber oracle dan bukan acceptance gate. Folder ini tidak mendefinisikan behavior baru dan tidak dapat mengalahkan owner contracts.

Documentation specification status: **`DOCUMENTATION_READY`**.

Executed implementation proof status: **not implied by this status**. `required`, `partial`, `not implemented`, `superseded`, atau historical green proof tetap harus ditutup pada stage 21 sebelum implementation/production relock.

## Authority and reading order

1. `../book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`
2. `../book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`
3. owner contract untuk behavior yang diuji
4. `Contract_Test_Matrix_LOCKED.md`
5. `Golden_Fixture_Catalog_LOCKED.md`
6. `Negative_Test_Catalog_LOCKED.md`
7. `Golden_Fixtures_Specification.md`
8. `Indicator_Test_Vectors_LOCKED.md` dan `Indicator_Expected_Output_Oracle_LOCKED.md`
9. `Fixture_Package_Manifest_LOCKED.md`
10. `Test_Implementation_Guidance_LOCKED.md`
11. `Executed_Proof_Admission_Criteria_LOCKED.md` dan `Test_Coverage_Closure_Contract_LOCKED.md`

## Required semantic families

- immutable observations, stale/wrong-date/schema/provider failure;
- temporal identity/symbol/calendar/status and inactive-now-active-then universe;
- canonical RAW validation, zero/duplicate/conflict rejection;
- verified corporate-action revisions, candidate-only unexplained breaks, coherent factors/products;
- coverage denominator that cannot shrink through dormancy/provider absence;
- explainable eligibility dimensions and complete reasons;
- actual-versus-proxy metrics;
- stable >100-session Wilder ATR plus gap and old-correction propagation;
- full config snapshot and deterministic semantic hashing;
- atomic publication-bound market-data read DTO and freshness states;
- exact publication replay and as-known anti-survivorship replay;
- activation-aware scheduler/failure/consecutive-session evidence;
- MariaDB clean install/upgrade/backfill/enforcement plus SQLite mirror parity.

Watchlist candidate count, ranking, signal quality, backtest expectancy, drawdown, turnover, atau profitabilitas tidak termasuk semantic proof market-data. Hanya shape, meaning, lineage, versioning, dan atomic consumer binding yang diuji pada boundary tersebut.

## Historical/superseded companions

The following files contain narrower legacy inventories or case matrices and cannot close corrected V2 semantics by themselves:

- `Behavioral_Test_Coverage_Inventory.md`
- `Db_Integrity_Constraint_Inventory.md`
- `Correction_Lifecycle_Safety_Test_Matrix.md`
- `PHPUNIT_TEST_MATRIX.md`
- historical test names/counts inside audit or ops inventories

They remain useful for regression coverage that does not conflict with the new contracts. Any test expecting provider adjusted-close fallback, current-active historical filtering, dormancy denominator exclusion, sliding ATR reseed, direct bar repair, or price-derived verified factors is `SUPERSEDED` and must be replaced.

## Closure rule

A semantic family is closed only when its positive and negative oracle executes through the actual production path, expected output is independently derived, database/runtime/build/config identity is recorded, evidence is admitted, and the full supported suite contains no superseded expectation.
