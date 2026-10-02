# Decision — local MariaDB environment boundary for B18 proof

- ID: `D-MD-B18-A002-012`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Dependency / finding: `MD-DEP-0019` / `F-MD-B18-A002-024`
- Supporting evidence: `E-MD-B18-A002-089` (observations), `E-MD-B18-A002-090` (authority derivation and scope correction)
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-02T11:08:27+07:00
- Status: `APPROVED` — owner clarification in the 2026-10-02 instruction
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20260925-001` unchanged

## Owner clarification

MariaDB under XAMPP on the development machine is local development infrastructure. It is not a governed business or domain artifact that must be restored or repaired indefinitely. What `MD-B18` needs from it is a usable MariaDB execution environment in which the governed proof runs.

## Decision

1. **Minimum environment.** For B18 proof the environment is the one `D-MD-B18-A002-003` already defines as the actual production path for proof: the MariaDB engine, the production repositories under `App\Infrastructure\Persistence\MarketData`, and the schema produced by the repository migrations. It does not mean production deployment or production data. In concrete terms: MariaDB is reachable; the intended application databases are reachable; the required migrations are applied; the application tables pass an integrity check; and the database-backed B18 members run on MariaDB with no member skipped because the environment is unavailable.
2. **Replaceability.** The database owner may repair, restart or replace the local database server, including installing a clean compatible XAMPP/MariaDB instance, provided the application database state that governed proof needs is preserved or restored and then revalidated: migrations, schema, application-table integrity, and a rerun of the proof that needs them.
3. **No repair-provenance requirement.** The internal repair history of the local MariaDB installation (system schema, Aria files, `.BAK` files, server log completeness) is not reconstructed or governed, unless a current authority requirement names it. This is not a production HA or disaster-recovery audit.
4. **Limits kept.** `D-MD-B18-A002-003` still forbids touching, repairing, copying from or using `D:/xampp/mysql/data_260914` as proof. Application databases are never dropped, wiped or rebuilt to make a test pass. A skip is never a pass.
5. **No action authorized.** This decision does not authorize reinstalling, restarting or repairing anything, and `B18` does not do so. It states what a later owner action may be.

## What this does not change

`D-MD-B18-A002-003` and `MD-DEP-0015` stand as written. `MD-S003-R0025` is not decided here: it stays `INCOMPLETE` and `REPLAY_FIXTURE_SELF_GENERATED` remains an R0025 proof matter under `D-MD-B18-A002-011`, not an environment matter. `F-MD-B10-A002-003` and `F-MD-B10-A002-004` are untouched.
