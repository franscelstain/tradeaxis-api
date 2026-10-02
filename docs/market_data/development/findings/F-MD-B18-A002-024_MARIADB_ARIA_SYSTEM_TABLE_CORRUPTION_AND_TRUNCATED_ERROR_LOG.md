# Finding — MariaDB Aria system table corruption and an error log that stops at startup

- ID: `F-MD-B18-A002-024`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-02T09:19:37+07:00
- Severity: `P1` — blocks `MD-DEP-0019`; no effect on any application table
- Status: `RESOLVED` - superseded by scope correction (`E-MD-B18-A002-090`, `D-MD-B18-A002-012`); the observations stay recorded
- Class: `ENVIRONMENT_DEFECT`
- Related: `E-MD-B18-A002-089`, `MD-DEP-0019`, `MD-DEP-0015`, `F-MD-B18-A002-012`, `F-MD-B18-A002-017`
- Remediation owner: database owner (the same owner as `MD-DEP-0019`). B18 only re-verifies.

## Observed

`E-MD-B18-A002-089` verified the running MariaDB instance (10.4.27, XAMPP `D:\xampp\mysql\data`, up since 2026-09-28 23:29:01) against `MD-DEP-0019`.

**Application data is intact.** `tradeaxis` and `tradeaxis_testing` hold 67 InnoDB tables each, all `CHECK TABLE` OK. Both have 79 applied migrations, equal to the 79 migration files, with none missing or unknown. The six `MD-S003` family members ran on MariaDB with no skip.

**The instance is not intact.**

1. `mysql.db` (an Aria privilege table) was corrupt when first checked: `Size of datafile is: 8505 Expected: 8192`, `Page 0: Got error: 176 when reading datafile`, `Corrupt`. The server repaired it itself on that first access, which created `db.MAD-261002091504.BAK`, so the repair happened during the verification. Later checks of all 164 tables, in `tradeaxis`, `tradeaxis_testing` and `mysql`, report OK.
2. The corrupt file held the server's own log text, not table data. The 8505-byte backup contains the startup lines of 2026-09-28 11:38:17 and 23:29:03 (`ready for connections`). The earlier `db.MAD-260924064836.BAK` (16697 bytes) holds the `%test` default rows plus the log lines of 2026-09-21 07:38:16. Startup output reaches a table data file instead of the log.
3. `mysql_error.log` stops at `Server socket created` on every start since 2026-09-14 13:24:15. There are 28 such lines and only 5 `ready for connections`, all on 2026-09-14. Its last write was 2026-09-28 23:29:02. A log read cannot disclose an Aria repair, a recovery warning or an error that comes after socket creation. `MD-DEP-0019` requires that log read before any write-capable test.
4. The system schema has three unrecorded repairs: the 2026-09-24 repair of `mysql.db`, the 2026-09-28 11:36 repair of eleven Aria system tables with `.BAK` files (`backup_mysql_system_20260928` also exists), and the repair on 2026-10-02. None is recorded in a governed record.
5. `mysql.db` now has 0 rows. The `%test` default grants are gone. The application connects as `root` with global privileges, so nothing the application uses is affected.

The 2026-09-14 health audit (`XAMPP_MARIADB_HEALTH_REPORT.md`) already tied Aria `Bad file descriptor` errors to a detached or hidden launch on Windows (MDEV-29117). It did not prove the cause for InnoDB. The current instance shows the same family of symptoms.

## Not caused by this finding

`MD-S003-R0025` is not decided here. The aggregate that ran with zero skips had one governed failure, `REPLAY_FIXTURE_SELF_GENERATED`, which is the first-independent-fixture condition of `F-MD-B18-A002-017` and `D-MD-B18-A002-011`. No fixture, test or production file changed.

## Remediation conditions

Owner action. B18 does not start, stop, repair or restart the instance.

1. Restart `mysqld` the way the 2026-09-14 audit verified (foreground or graceful, not detached and hidden), and keep the data directory otherwise as it is. Nothing in the old data directory `data_260914` is touched.
2. The error log must now reach `ready for connections` after `Server socket created`, so a log read is informative again.
3. After a clean start, no `.BAK` file appears in `data\mysql` and `CHECK TABLE` is OK for all tables. This is the `MD-DEP-0015` integrity criterion plus the system schema.
4. A governed record states the system-table repairs (2026-09-24, 2026-09-28, 2026-10-02) and the lost `%test` default grants, or states that they were rebuilt.
5. The `E-MD-B18-A002-089` verification is repeated (164-table `CHECK TABLE`, migration state, zero-skip `MD-S003-R0025` aggregate) with the log read first.

## Orchestration

`MD-DEP-0019` stays `BLOCKING` until this finding is resolved and its own condition is met. This is an environment finding, not a B10 or B18 proof finding. `F-MD-B10-A002-003` and `F-MD-B10-A002-004` are not affected.

## Scope correction - 2026-10-02 - E-MD-B18-A002-090

`E-MD-B18-A002-089` read "intact MariaDB instance" as covering the `mysql` system schema, `mysql.db`, the Aria repair history, `.BAK` files and a log that reaches `ready for connections`, and this finding kept `MD-DEP-0019` blocked on them. A reassessment against current authority found none of those required: the dependency's "integrity check per `MD-DEP-0015`" means the tables of the two application databases (`D-MD-B18-A002-003`), and its log requirement is a log read. `E-MD-B18-A002-010` accepted the rebuilt instance with "CHECK TABLE on system tables deliberately not run".

**Observation (unchanged):** `mysql.db` was corrupt and held server log text, the server repaired it on first access, the error log stops at startup, and the system schema has repairs no governed record states.

**Governed impact:** none on B18. The application tables (134 of 134 `CHECK TABLE` OK), the migrations (79 of 79), the connection and the MariaDB-backed members (zero skipped) were verified directly. The remediation conditions above are withdrawn as Market Data requirements. They remain what a database owner may do to the local server, under `D-MD-B18-A002-012`; if the server is replaced, the application state is restored and revalidated and the zero-skip aggregate is repeated.

E089's own `CHECK TABLE` of `mysql.*` caused the auto-repair of `mysql.db`; the precedent E010 did not run it. That was a verification-induced change to a system table, with no effect on any application table.
