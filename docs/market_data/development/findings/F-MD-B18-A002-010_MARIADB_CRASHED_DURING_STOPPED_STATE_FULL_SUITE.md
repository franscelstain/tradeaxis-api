# Finding — `F-MD-B18-A002-010`

- ID: `F-MD-B18-A002-010`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Owner stage: `MD-B18` (database recovery requires the database owner)
- Raised at: 2026-09-14T08:30:00+07:00
- Severity: `P1`
- Status: `RESOLVED — RUNTIME_RESTORED_ON_OWNER_REBUILT_INSTANCE; FULL-SUITE_CRITERION_CARRIED_BY_MD-DEP-0015`
- Class: `RUNTIME_DATABASE_CRASH_RECURRENCE`
- Dependency: `MD-DEP-0015` (BLOCKING runtime proof); `MD-DEP-0014` remains the first decision
- Evidence: `E-MD-B18-A002-007`
- Related, not reopened: `F-MD-B18-A002-007` (RESOLVED for availability only; cause and page integrity never certified)

## Observation

The stopped-state validation on 2026-09-14 ran with MariaDB reachable: both `tradeaxis` and
`tradeaxis_testing` answered as 10.4.27-MariaDB with all 72 current repository migrations applied
and zero pending, and the targeted run passed 351 tests / 1522 assertions with zero skips, the
11 production-path MariaDB fixtures included.

The subsequent full suite finished 2256 tests / 21347 assertions / 3 errors / 1 failure /
16 skipped, exit 2. The first error is `SQLSTATE[HY000] 2006 MySQL server has gone away`
(`LocalFileEodBarsAdapterTest`); the other two are `[2002] … actively refused`
(`OpsCommandSurfaceTest`, `StageThreeEligibilityProducerTest`). At 08:24 and 08:25 both databases
refused connections; no `mysqld` process and no port-3306 listener existed.

Read-only sources establish a crash, not a shutdown:

- `D:/xampp/mysql/data/mysql_error.log` — last entry 07:45:48 (server socket created). The 07:45:46–48
  startup repeats F007's signature: many pages in spaces 0, 233, 316 and 318 carry a log sequence
  number in the future of the system LSN (for example page LSN 52524230841 against 701721), with the
  engine's possible-corruption warning. The startup did not abort. No later line was written.
- Windows Application log, provider `MariaDB` — 08:18:16 the same future-LSN errors on space 172
  pages 8–11 (system LSN 812315); 08:19:08 `Tried to read 16384 bytes at offset 32768, but was only
  able to read 0`, OS error 203, `'read' returned OS error 403. Cannot continue operation`.
- 08:19:09 `Application Error` 1000: `mysqld.exe` 10.4.27.0 faulted, exception `0x80000003`;
  08:19:13 Windows Error Reporting APPCRASH.

The single failure is `FindingRecordConsistencyTest::test_every_open_finding_reaches_the_canonical_current_state`
reporting `F-MD-B18-A002-009 (OPEN)` absent from generated navigation — a navigation defect from
the interruption, independent of the database and repaired by the generator.

## What is and is not established

Established: a physical read of an InnoDB data file returned zero bytes and the server aborted; the
future-LSN mismatch F007 recorded on 2026-09-13 is still present on 2026-09-14 and now spans more
tablespaces. Not established: which table space 172 holds, the underlying cause of the mismatch, or
whether any stored page is intact. A later successful connection would restore availability only,
exactly as E-MD-B18-A002-004 did; it would not certify page integrity.

Nothing was started, stopped, repaired or deleted by this work: no process action, no
`innodb_force_recovery`, no tablespace or log-file change, no backup action, no migration.

## Consequence for the attempt

Every retained B18 predicate that needs MariaDB — including the R0056 production-path corpus under
either owner choice — cannot produce current runtime proof while this holds, and the full-suite step
of stopped-state validation is not PASS. The owner/relock decision under `MD-DEP-0014` does not need
the database and remains the single exact resume point; `MD-DEP-0015` must be resolved before any
database-backed proof, full-suite PASS, E001 or SC. No attempt reset or stage switch follows from it.

## Decision required from the database owner

Recommended: preserve `D:/xampp/mysql/data` as-is (a byte copy before anything else), then restore
from a trusted backup or rebuild both databases by clean install plus migration, and only then
restart. Force-recovery on the damaged files risks a working server over pages that are still
wrong. Alternative: restart the existing instance unchanged; that restores availability at best and
must be recorded as availability only. After any choice, verify identity and zero pending migrations
on both databases, re-run the three errored tests, the skipped MariaDB tests and the full suite.

## Validation journal

Post-issue results — regeneration, governance gates and navigation tests — are appended here.

2026-09-14 08:31 — after E007 and registration: CURRENT_STATE regenerated through the generator;
navigation and classification/applicability tests 42 / 205 OK; documentation gate PASS; relationship
gate PASS 231 records / 463 relationships / 0 gaps; relationship self-test 4 controls OK and 14 of
14 mutations fail closed; normalization PASS; proof gate and closure gate fail only on R0056 and the
absent A002 pack, as recorded.

2026-09-14 08:33 — availability returned without action by this work. `xampp-control.exe`
(PID 6644, created 07:45:06) restarted `mysqld` as PID 22696 at 08:26:21. The startup ran InnoDB
crash recovery from checkpoint LSN 815169 and logged 245 future-LSN warnings, including doublewrite
copies ignored for future LSNs, across spaces 0, 25, 155, 233, 246, 316 and 318 — more spaces than
the 07:45 startup named. Both databases answer as 10.4.27-MariaDB. Raw material, not covered by the
E007 manifest: `storage/app/market-data/evidence/MD-B18-A002/post-crash-restart-20260914/`
`mariadb-log-20260914-0826-restart.txt` (D8AB1DA80F55F8B9DCCA390D77B4F8E497290CCF73B1D0DCADEEF5F2AE5F2EEF)
and `restart-observation.txt` (8207A3475CB3648DFF04496B9D78A0D62F70B9233FAE6A0A9548A19D04DA2F01).

The full suite was deliberately not re-run. Unlike the 2026-09-13 outage, this one ended in an
abort on a zero-byte data-file read, and the shared system tablespace carries future-LSN pages;
the suite writes to `tradeaxis_testing` on that engine. Writing to files known to be damaged before
the database owner has preserved them is not a fail-safe way to obtain a green run, and a green run
would certify availability only. `MD-DEP-0015` stays BLOCKING until the owner decides.

2026-09-14, from 10:08: the clean-instance path in `D-MD-B18-A002-003` was executed. Evidence:
`E-MD-B18-A002-009`.

**Setup.** The user isolated the old directory as `D:/xampp/mysql/data_260914` and started a data
directory made from the XAMPP template. This work did not start, stop, repair or copy either
directory. The only access to `data_260914` was a read-only listing.

The three results are reported separately:

**Availability.**
- Server 10.4.27-MariaDB, datadir `D:/xampp/mysql/data`, `innodb_force_recovery=0`.
- The clean startup log shows no future-LSN or corruption warning; the redo log was resized from
  5 MB to 512 MB, as `my.ini` specifies.

**Integrity.**
- The file tree is identical to the `backup` template: 137 files, with 59 system files
  byte-identical and 28 unhashable only because the running server locks them.
- No application database existed before `CREATE DATABASE`.
- Both databases are `utf8mb4_unicode_ci`, with 72/72 migrations and 0 pending, 62 tables,
  9 triggers and 437 reason codes.
- `CHECK TABLE` reports 0 non-OK results.

**Runtime proof.**
- Targeted tests: 364 / 1665 OK, zero skips.
- The R0056 aggregate passed, with six probes caught.
- The E005 re-execution produced E008.
- The full suite ran 2258 tests / 21529 assertions: 0 errors, 0 skips, 8 failures. Every failure
  comes from a guard coupled to the old deployed database, recorded as `F-MD-B18-A002-011`.

D003 makes a green full suite a resolution criterion, so `MD-DEP-0015` stays BLOCKING until F011 is
decided. Recovering the old data is tracked separately under `MD-DEP-0016`.

2026-09-14 10:56 — **the clean instance stopped too, and this is recorded as observed.**

**When.** The final full suite (#2) started about 10:50:31 and ran for 05:13. The last database
activity before it was the E008 re-execution; `ibdata1` was last written at 10:35:08. The first
database skip came at test 233 of 2258, so the server stopped between 10:35 and roughly 10:51.

**How it looks.**
- The clean `mysql_error.log` ends at the 10:08:22 startup line, with no shutdown or crash line.
- The Windows Application and System logs have no MariaDB, Application Error or Windows Error
  Reporting event after 10:45.
- `mysql.pid` was left behind, and `xampp-control.exe` (PID 6644) is still running.

This fits a forced termination. XAMPP's `mysql_stop.bat` calls `killprocess.bat "mysqld.exe"`,
which would explain it, but that is not proven. This work ran no start, stop or kill command, so
who stopped the server and why is not established. Unlike the old instance, no InnoDB error was
logged.

**Suite #2 result.** 2258 tests / 21278 assertions, 6 errors, 1 failure, 37 skipped.
- All six errors are `SQLSTATE[HY000] [2002]` refused connections.
- The one failure is the R0056 aggregate reporting "cannot count the production-path corpus as
  executed". That is the P6 behaviour, confirmed live, and not a regression.

This run does not validate the final file state. The last validation that did run against a
reachable instance covers every file change up to E008 (full suite #1 plus targeted tests). The
changes after it — the stale-entry withdrawal, E008/E009 and the registrations — were validated by
file-based gates and navigation tests only. Before any further runtime proof, the user restarts the
clean instance, and availability is verified again: identity, 72/72 migrations, `CHECK TABLE`.
Then the full suite is re-run.

**Timestamp disclosure** (the records are immutable or already registered, so none is edited):

| Record | Stamped | Actually written |
|---|---|---|
| `E-MD-B18-A002-009` | 10:58 | 10:47:01 |
| `F-MD-B18-A002-011` | 10:45 | 10:39:56 |

`D-MD-B18-A002-003` and `E-MD-B18-A002-008` are disclosed in E009 itself.

## Resolution — 2026-09-14 14:02 (evidence `E-MD-B18-A002-010`)

This finding recorded the crash of the old instance, which blocked MariaDB-backed proof. That
consequence no longer holds. The database owner rebuilt a sound instance, recorded in
`F-MD-B18-A002-012`'s resolution. On it, both application databases were rebuilt (72/72 migrations,
`CHECK TABLE` clean), the targeted B18 surface ran green on MariaDB, and the full suite fails only
on the F-011(b) corpus-oracle controls.

- The old data stays isolated and uncertified under `MD-DEP-0016`.
- The remaining full-suite criterion belongs to `MD-DEP-0015` and `F-MD-B18-A002-011`, not to this
  finding.
- No recovery action was performed on `data_260914`.
