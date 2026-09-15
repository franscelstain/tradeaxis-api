# Decision — F-011 option 1: orphan subset correction, deployed corpus only through recovery

- ID: `D-MD-B18-A002-004`
- Stage / Attempt / Work / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Epoch: `MD-REBASELINE-20260820-001`; strategy freeze `MD-STRATEGY-FREEZE-20260903-001`
- Issued: 2026-09-14T11:06:43+07:00 (system clock, read before writing)
- Status: `ISSUED — USER_DECISION`
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Decides: `F-MD-B18-A002-011`
- Directed by: `CI-MD-B18-A002-001`, successor boundary 2026-09-14 11:06

## Authorization and its reading

The user wrote: "MariaDB sudah saya start ulang, gunakan opsi rekomendasi F-001, lanjutkan".

"F-001" is read as `F-MD-B18-A002-011`, for two reasons:
- It is the only finding for which a recommended option was pending when the user wrote.
- `F-MD-B18-A002-001` is already RESOLVED and carries no options.

This reading is recorded so that it can be disputed.

## Decision

Option 1 of F-011, in both halves:

**(a) The orphan assertion becomes a subset check.** In
`MigrationIntegrityAndDriftTest::test_no_applied_migration_has_lost_its_file`:
- the in-scope orphan set must be empty, as it already had to be;
- the out-of-scope orphan set must be a **subset** of the eleven declared watchlist migrations,
  rather than **equal** to them.

The guard's purpose — a new orphan must be classified deliberately — is kept and must be shown by
probes. A clean install with zero orphans is not drift.

**(b) The corpus oracle is left unchanged.** `ProductionCorpusInvariantOracleTest` is not modified.
It turns green only when a real deployed corpus is present in `tradeaxis`. The only sanctioned way
to get one there is the old-data recovery track, `MD-DEP-0016`: an isolated recovery instance on a
**copy** of `data_260914`, a logical export, then an import. **This decision does not authorize
that recovery.** It needs the user's separate authorization.

## Consequence, stated explicitly

- Once (a) is done, the only expected full-suite failures in a clean environment are the oracle's
  seven population controls.
- D-MD-B18-A002-003 makes a green full suite a resolution criterion for `MD-DEP-0015`, and MD-B18
  closure also needs a green full suite. So closure now depends on the MD-DEP-0016 recovery being
  authorized and executed, unless a later governance decision changes the oracle's status.
- This dependency is recorded, not hidden. The recovered data is still never used as MD-B18 runtime
  proof: the oracle reads `tradeaxis`, while B18 runtime proof runs on `tradeaxis_testing`.

## Scope limits

- No strategy byte and no issued BL/E/D/SC record is touched.
- No MariaDB start, stop, repair or configuration change is authorized.
- The clean instance the user restarted at 11:02:11 shows the future-LSN signature
  (`F-MD-B18-A002-012`). Runtime proof on MariaDB waits for a sound instance.
