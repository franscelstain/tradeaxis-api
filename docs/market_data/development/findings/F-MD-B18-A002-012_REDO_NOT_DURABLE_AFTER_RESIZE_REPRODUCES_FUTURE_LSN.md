# Finding — redo not durable after a log resize reproduces future-LSN damage

- ID: `F-MD-B18-A002-012`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T11:06:43+07:00 (system clock)
- Severity: `P1` for runtime proof
- Status: `RESOLVED — OWNER_REBUILT_INSTANCE_VERIFIED; HISTORICAL_TRIGGER_NOT_CERTIFIED`
- Class: `RUNTIME_DATABASE_DURABILITY`
- Dependency: `MD-DEP-0015` (BLOCKING)
- Related: `F-MD-B18-A002-007`, `F-MD-B18-A002-010`, `F-MD-B18-A002-011`

## Observation

This is read-only evidence from the clean XAMPP-template instance built on 2026-09-14.

1. **First start, 10:08:21.** The template ships 5 MB redo files; `my.ini` sets
   `innodb_log_file_size=512M`. InnoDB resized the redo log at 300306 and created new
   `ib_logfile0/1`. Migrations, seeding and the test runs followed until about 10:35.
2. **The redo file did not move.** When checked at 10:56, `ib_logfile0` still carried its 10:08:21
   write time, while `ibdata1` had been written at 10:35:08.
3. **The server was stopped between 10:35 and about 10:51.** No shutdown line was logged, no crash
   event was recorded, and `mysql.pid` was left behind — the pattern of XAMPP's
   `killprocess.bat "mysqld.exe"`. This work did not stop it.
4. **The user restarted it at 11:02:11.** Crash recovery reported:
   - the system tablespace at LSN 139827 against the redo log at 300556;
   - page LSNs up to about 2.36 million "in the future" of system LSN 300565;
   - 112 doublewrite copies ignored.

   The affected spaces are the system tablespace and 11 `tradeaxis_testing` tables: `eod_runs`,
   `md_source_observations`, `md_issuers`, `md_instruments`, `md_listings`, `md_listing_symbols`,
   `md_corporate_action_revisions`, `md_adjustment_factor_sets`, `md_adjustment_factors`,
   `md_publication_lineage_bindings` and `md_source_observation_rejected_rows`. The Application log
   has 2530 future-LSN or corruption events since 10:56.
5. **The server is now running at LSN 301294.** Migrations are 72/72, `CHECK TABLE` is clean, and
   `ib_logfile0` does advance now (11:03:55).

## Working explanation — not proven

Nearly all the redo between LSN 300306 and about 2.4 million was missing when recovery ran. The
system tablespace and data pages carry it, but the redo log does not. That fits redo written after
the startup resize never becoming durable in the file recovery reads, followed by a forced stop.
The old instance's first incident (`F-MD-B18-A002-007`: page LSN 52524230841 against a system LSN of
456145) has the same shape. So the old damage may come from the same environment mechanism rather
than from the disk.

`CHECK TABLE` checks page structure, not whether redo and pages agree. The instance is available, but
its integrity no longer meets the `D-MD-B18-A002-003` criterion. Pages stamped with a future LSN can
make later recovery skip redo records, which risks silent loss.

## Consequence

- Runtime proof on MariaDB is suspended: the R0056 aggregate, `MD-S003-R0025` and the full suite.
- Review of guards that use SQLite or no database continues, since they do not touch this instance.

## Decision required from the database owner

Recommended:
1. Leave the current data directory as it is.
2. Make another data directory from the template.
3. Before any data is written, make the first start with the resize end in a **graceful** shutdown
   (`mysqladmin -u root shutdown`, not XAMPP Stop), then start again. Confirm that `ib_logfile0` is
   now 512 MB and that its write time advances under load.
4. Only then rebuild the databases with migrations and the seeder.
5. From then on, stop MariaDB only with a graceful shutdown.

Alternative: align `innodb_log_file_size` in `my.ini` to the template's 5 MB so that no resize
happens. That is a system configuration change and is the user's to make.

This work changes no configuration, starts or stops nothing, and modifies no data directory.

## Resolution — 2026-09-14 (evidence `E-MD-B18-A002-010`)

**What the database owner did.** The owner handled recovery separately. The report is
`D:\xampp\mysql\health_audit_20260914\XAMPP_MARIADB_HEALTH_REPORT.md` (sha256 2B76096D…5481). In
short:
- The failed data directories were preserved, then removed after the final lifecycle passed.
- `D:\xampp\mysql\data` was rebuilt from scratch with the 10.4.27 `mysql_install_db.exe`. No raw
  InnoDB or Aria file was copied.
- The rebuilt instance passed repeated start → graceful `mysqladmin` shutdown → restart cycles,
  both direct and through `mysql_start.bat`.
- `data_260914` was untouched.

**What this attempt verified, read-only, before relying on it:**
- Runtime: PID 7648 since 13:24:15, `datadir` `D:\xampp\mysql\data\`, LSN 61497 consistent, no
  dirty pages.
- Log and Windows events: no fail-safe pattern since that start. The last errors were the
  13:00:06–13:18:04 incidents the report documents.

**Runtime proof after rebuilding the application databases:**
- 72/72 migrations and `CHECK TABLE` clean on both databases.
- Targeted 373/1686 green, including the R0056 aggregate and the MariaDB scenario families.
- Full suite 2258/21556 with only the seven F-011(b) corpus-oracle controls failing.
- No new fail-safe pattern was logged.

**Correction to this finding's working explanation.** The owner's experiments show that the 5M→512M
resize alone did not reproduce the damage. There is strong evidence that XAMPP's Stop button calls
Win32 `TerminateProcess`, which is a forced termination. The later incident was an Aria checkpoint
`Bad file descriptor` on `aria_log_control`, followed by a corrupt `mysql.db` index. That matches
the upstream `MDEV-29117` / `MDEV-32615` family. The exact historical trigger is **not certified**.

**Binding operating conditions for further runtime proof:**
- Start with `D:\xampp\mysql_start.bat`, keeping its console open.
- Stop only with `mysqladmin --protocol=tcp --host=127.0.0.1 --port=3306 -u root shutdown`.
- Never use XAMPP Stop, task kill or a forced termination.
- Any fail-safe pattern stops runtime proof.

Large data loads are still unvalidated.
