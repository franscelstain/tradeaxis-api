# Decision — project owner authorizes the MD-DEP-0016 Option 1 recovery, subject to separate database-owner authorization

- ID: `D-MD-B18-A002-020`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Applies to: `MD-DEP-0015`, `MD-DEP-0016`, `F-MD-B18-A002-011` part (b)
- Decisions relied on: `D-MD-B18-A002-003`, `D-MD-B18-A002-004`
- Evidence relied on: `E-MD-B18-A002-001`, `E-MD-B18-A002-117`, `E-MD-B18-A002-118` (preflight, issued with this decision)
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-08T01:46:46+07:00
- Status: `APPROVED` — project owner, in the 2026-10-08 instruction "RECORD MD-DEP-0016 PROJECT-OWNER RECOVERY DECISION AND PREPARE DB-OWNER AUTHORIZATION"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Project-owner decision (as supplied)

Project owner authorizes MD-DEP-0016 Option 1 recovery under D-003/D-004, subject to separate database-owner authorization before execution.

Normative terms:

1. Recovery may use ONLY a byte-copy of data_260914.
2. Original data_260914 must never be: opened by recovery mysqld; repaired; modified; migrated; used as an import source directly.
3. The isolated recovery MariaDB must start without innodb_force_recovery.
4. If any innodb_force_recovery setting is required: STOP. Return for a new owner decision.
5. Recovery must be logical export/import only for governed repository tables.
6. Raw InnoDB file import / copying .ibd files into the clean instance is prohibited.
7. Exported tables must: be readable; pass applicable integrity checks; have row counts recorded.
8. Export/import row counts must reconcile.
9. Clean tradeaxis must remain forward migrated 79/79.
10. Available historical row counts/date ranges/metrics must be compared against the recovered corpus.
11. Unexplained corpus loss/divergence is not acceptable recovery.
12. Recovered corpus is only environment/deployed-corpus recovery for: MD-DEP-0015; F-MD-B18-A002-011(b).
13. Recovered corpus must NOT become: a new B18 proof source; a replacement for candidate-v5; a reason to rewrite any of the 113 proof bases.
14. Closure requires: post-recovery full MarketData suite GREEN; zero skips; unchanged candidate-v5; unchanged frozen build; unchanged 113-row binding; unchanged binder evidence; unchanged matrix identity except governed closure metadata where explicitly allowed.
15. The historical pre-binding full suite must NOT be represented as green.
16. E-MD-B18-A002-001 and E-MD-B18-A002-117 remain truthful historical evidence that seven corpus-dependent failures existed at binding time.
17. The "full suite green before and after binding" requirement is handled by a controlled successor interpretation: closure may proceed only after post-recovery green/zero-skip execution and unchanged-binding verification, without rewriting historical evidence.

## Boundary of this decision

* It is the **project-owner** authorization only. **No database-owner authorization exists** in the repository (checked: no record, no decision and no dependency note carries one; `MD-DEP-0016` still reads "no recovery authorized"). None is inferred. Execution is not authorized until the database owner gives the separate authorization, whose prepared text and parameters are below and in `E-MD-B18-A002-118`.
* Nothing was started, stopped, opened, copied, dumped or imported by recording it. `MD-DEP-0015` stays `BLOCKING`, `MD-DEP-0016` stays `OPEN_NON_BLOCKING`, `F-MD-B18-A002-011` part (b) stays waiting, the B18 Stage Register stays `NOT_READY_FOR_CLOSURE`.
* It changes no proof, no candidate, no build file and no matrix row: B18 stays 113/113 reviewed and 113/113 `SATISFIED` (matrix sha256 `8b5bd9b1cb2d47022e6f003fef251d9160ef660b35e2813b92c297e7708e0695`).
* Term 17 is a recorded interpretation for closure only; it does not edit D-MD-B18-A002-003/004, the consolidated package, E-MD-B18-A002-001 or E-MD-B18-A002-117.

## Prepared database-owner authorization text (not issued; to be given by the database owner)

```
DATABASE-OWNER AUTHORIZATION — MD-DEP-0016 Option 1 recovery (PREPARED TEXT, NOT YET GIVEN)

I, the database owner of the TradeAxis MariaDB environment, authorize the following, and only the following, under D-MD-B18-A002-003, D-MD-B18-A002-004 and the project-owner decision D-MD-B18-A002-020:

1. CREATE a byte-copy of D:/xampp/mysql/data_260914 at <COPY_PATH>. The original is read only as the source of that copy and is never opened by any mysqld.
2. START an isolated recovery MariaDB instance on the byte-copy only, with its own defaults file, its own datadir (the copy), its own port <RECOVERY_PORT> (not 3306) and its own pid/socket, WITHOUT innodb_force_recovery of any value. If the instance cannot start without it: STOP and return to the project owner; do not set it.
3. EXPORT the governed repository tables of schema tradeaxis (the 62 repository tables; not the 11 old-only watchlist_* tables) with a logical dump (SQL or CSV) to <DUMP_PATH>. No raw InnoDB / .ibd file is ever copied or imported anywhere.
4. IMPORT that logical export into the CLEAN tradeaxis on the clean instance (port 3306, datadir D:/xampp/mysql/data), which must remain forward-migrated 79/79. tradeaxis_testing is not touched by the import.
5. RECONCILE: per-table row counts and date ranges (export versus import, and versus the historical counts recorded in the repository), CHECK TABLE on the exported source tables and on both clean databases, and record every difference. Unexplained loss or divergence is not an acceptable recovery.
6. This authorization does NOT cover: modifying, repairing, migrating or deleting the original data_260914 or the byte-copy's source; innodb_force_recovery; raw file import; deleting the byte-copy or the dump (separate authorization); using the recovered corpus as B18 proof or to rewrite any of the 113 proof bases or candidate-v5; any application, config, bootstrap, vendor or composer change.
7. Storage: the layout and abort thresholds of E-MD-B18-A002-118 apply; I confirm <COPY_PATH>, <DUMP_PATH> and <RECOVERY_PORT>.

Signed: <DATABASE_OWNER_NAME / IDENTITY AS SUPPLIED BY THE OWNER>   Date: <DATE>
```
