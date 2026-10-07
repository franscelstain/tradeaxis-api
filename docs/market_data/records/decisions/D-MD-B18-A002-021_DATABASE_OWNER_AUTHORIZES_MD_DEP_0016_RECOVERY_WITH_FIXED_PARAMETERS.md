# Decision — database owner authorizes the MD-DEP-0016 recovery with fixed parameters

- ID: `D-MD-B18-A002-021`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Applies to: `MD-DEP-0015`, `MD-DEP-0016`, `F-MD-B18-A002-011` part (b)
- Decisions relied on: `D-MD-B18-A002-020` (project-owner authorization and the prepared database-owner text), `D-MD-B18-A002-004`, `D-MD-B18-A002-003`
- Evidence relied on: `E-MD-B18-A002-118`, `E-MD-B18-A002-119` (issued with this decision)
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-08T02:11:53+07:00
- Status: `APPROVED` — database owner, in the 2026-10-08 instruction "RECORD MD-DEP-0016 DATABASE-OWNER AUTHORIZATION AND FINAL EXECUTION PREFLIGHT"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`

## Database-owner authorization (as supplied)

DATABASE_OWNER = Fransiscus
AUTHORIZATION_DATE = 2026-10-08

Authorized parameters:

COPY_PATH = D:\tradeaxis_recovery\data_260914_copy\
DUMP_PATH = D:\tradeaxis_recovery\dump\tradeaxis_260914.sql.gz
RECOVERY_PORT = 3307

The database owner authorizes MD-DEP-0016 recovery according to D-MD-B18-A002-020 and E-MD-B18-A002-118. All previously stated database-owner authorization terms remain binding.

Key restrictions:

1. Original data_260914 must never be opened by recovery mysqld.
2. Recovery uses only a byte-copy.
3. No mutation/repair/migration of the original.
4. Recovery MariaDB must start WITHOUT innodb_force_recovery.
5. If innodb_force_recovery is required: STOP and return for a new decision.
6. Logical export/import only.
7. No raw .ibd/InnoDB import.
8. Export only the 62 governed repository tables.
9. Exclude watchlist_* tables.
10. Import only into clean tradeaxis.
11. tradeaxis_testing must not be touched.
12. Full E118 acceptance checklist A-O remains mandatory.
13. Recovered corpus is environment/deployed-corpus evidence only, never new B18 proof.

## Boundary

* The authorization is not broadened. The prepared text of `D-MD-B18-A002-020` (items 1-7) applies with these parameters; it does not authorize deleting the byte-copy or the dump, using `innodb_force_recovery`, `--skip-grant-tables`, a different port or path, any change to the original, `tradeaxis_testing`, or any application, config, bootstrap, vendor or composer file.
* Recording it executes nothing: no copy, no start, no dump, no import. `MD-DEP-0015` stays `BLOCKING`, `MD-DEP-0016` stays `OPEN_NON_BLOCKING`, `F-MD-B18-A002-011` part (b) stays waiting, and B18 stays 113/113 `SATISFIED` and `NOT_READY_FOR_CLOSURE`.
* Following `D-MD-B18-A002-020`, this authorization and the recovery are environment evidence for `MD-DEP-0015` and F-011(b) only; the recovered corpus is not B18 proof.
