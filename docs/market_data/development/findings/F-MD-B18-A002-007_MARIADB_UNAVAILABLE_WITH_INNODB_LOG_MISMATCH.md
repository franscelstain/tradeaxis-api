# Finding — `F-MD-B18-A002-007`

- ID: `F-MD-B18-A002-007`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Owner stage: `MD-B18` (environment restoration requires database owner)
- Raised at: 2026-09-13T14:48:00+07:00
- Severity: `P1`
- Status: `RESOLVED — RUNTIME_AVAILABILITY_REVALIDATED`
- Class: `RUNTIME_DATABASE_UNAVAILABLE`
- Dependency: `MD-DEP-0013`
- Evidence: `E-MD-B18-A002-003`; successor `E-MD-B18-A002-004`

## Observed cause, not an inferred repair

Current full-suite execution finished with 2248 tests / 21249 assertions / 6 errors / 2 failures /
16 skips, exit 2. All six errors contain SQLSTATE[HY000] [2002], actively refused connection.
No process mysqld/mariadbd and no port-3306 listener were found by read-only checks. An earlier
targeted run had 24/24 tests and 81 assertions passing, including the MariaDB corpus, but that
does not establish availability now. The environment changed between those executions.

The existing XAMPP configuration at D:/xampp/mysql/bin/my.ini names port 3306 and data directory
D:/xampp/mysql/data. Its mysql_error.log records at 2026-09-13 14:46:33:

- page LSN 52524230841 exceeds current system LSN 456145;
- the engine warns of possible corruption or mismatched tablespace/log files;
- mysql.plugin could not be opened, plugins failed to initialize, then Aborting.

The underlying cause of the database-file mismatch is NOT established. No process was started or
stopped by this work, and no database/configuration/log file was changed. No force-recovery,
tablespace/log deletion, backup overwrite, or schema reset is authorized by the implementation
task. Retained excerpt and command output are hashed through E-MD-B18-A002-003.

## Independent navigation failures

The two assertion failures were FindingRecordConsistencyTest (new F006 absent from generated
navigation) and ScopeBoundaryAndOrchestrationCompletionTest (generated resume differs from the
register). They require GenerateMarketDataCurrentState.php, not authority or test changes. They
are separate from the database outage and must be rechecked after regeneration, including F007.

## Safe continuation and decision

## Resolution by observation and execution — 2026-09-13

MariaDB became reachable again without any process or database-recovery action by this work.
Read-only identity/migrate-status checks prove both tradeaxis and tradeaxis_testing use MariaDB
10.4.27 and have 72 applied migrations, zero pending. No migration was needed or executed.
After regeneration the navigation tests pass 11/86. The complete rerun passes 2248/21399, zero
errors, failures or skips, in 05:32.972. E-MD-B18-A002-004 retains these exact outputs and resolves
MD-DEP-0013's runtime-availability block; MD-DEP-0012 remains independently BLOCKING.

The log advanced during capture: E003's retained tail is the later 14:46:57 startup, not the
14:46:33 abort initially diagnosed. E004 supplies the exact four timestamp-selected source lines;
E003 and its manifest stay byte-identical. This is a corrected evidence link, not a changed
observation. Neither test success nor restored reachability certifies every stored page or the
underlying recovery cause. The database owner should retain the original LSN/plugin incident.

### Original safe-stop instruction (availability now resolved)

MD-DEP-0012 remains the first decision on the SAME MD-B18-A002 resume point. MD-DEP-0013 additionally
blocks any new database-backed proof. User/database owner must choose a recovery scope and trusted
backup or restore the existing instance externally. Recommendation: preserve the data directory
and choose a backup-based recovery plan before any data-modifying action. Leaving the instance
untouched is safe but leaves runtime proof blocked. No recovery choice is taken here.

After restoration, verify both tradeaxis and tradeaxis_testing identity and migration status;
apply required migrations to both only if needed under the governed scope. Re-run the six errored
tests, skipped MariaDB tests and full suite. An unchanged failing full suite or skip is not PASS.
Keep every prior failed execution; no attempt reset or stage switch is warranted by these tests.
