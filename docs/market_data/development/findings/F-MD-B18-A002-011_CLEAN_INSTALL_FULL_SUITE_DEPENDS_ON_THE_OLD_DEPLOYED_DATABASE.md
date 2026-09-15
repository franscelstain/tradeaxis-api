# Finding — clean-install full suite depends on the old deployed database

- ID: `F-MD-B18-A002-011`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-09-14T10:45:00+07:00
- Severity: `P1` for closure. It is not an application defect.
- Status: `PARTIALLY_RESOLVED — OPTION_1_(a)_DONE; (b)_AWAITS_MD-DEP-0016_RECOVERY`
- Class: `ENVIRONMENT_COUPLED_GUARDS`
- Blocks: the full-suite criterion of `MD-DEP-0015`, as D-MD-B18-A002-003 sets it, and therefore the
  full-suite condition of MD-B18 closure
- Evidence: `E-MD-B18-A002-009`
- Related: `F-MD-B18-A002-010`, `MD-DEP-0016` (old-data recovery)

## Observation

The full suite ran on the clean XAMPP-template instance, with both databases rebuilt by the 72
repository migrations and `DatabaseSeeder`. Result: 2258 tests / 21529 assertions, 0 errors,
0 skipped, 8 failures. Targeted tests passed 364 / 1665 on the same instance. All eight failures
come from two guards that read the state of the old deployed database rather than the repository.

1. `MigrationIntegrityAndDriftTest::test_no_applied_migration_has_lost_its_file`. After asserting
   that no in-scope market-data migration is orphaned, which holds here, it asserts that the
   out-of-scope orphan set equals exactly the eleven watchlist migrations the old `tradeaxis` ledger
   carried: `2026_06_09_000001` through `2026_07_28_000002`. Their files live in another package,
   and no code or migration in this repository references a `watchlist_*` table. A clean install
   has zero orphans, so the exact-set assertion fails even though nothing drifted.
2. `ProductionCorpusInvariantOracleTest`, seven methods. It reads the deployed corpus in
   `tradeaxis`; its docblock cites 756,329 bars, 64,092 publications and 71,917 runs. Each
   invariant is paired with a positive-population control (for example, "the detector must be
   reading real rows"), and the class skips only when the database is unreachable. On an empty
   `tradeaxis`, the invariants hold vacuously and the population controls fail, which is exactly
   what they were written to do. No matrix row and no current evidence cites this oracle.

## What is and is not established

- Established: no failure is an application defect. The drift test's in-scope assertion passes. The
  oracle's violation queries are not reached with data. The old database held the corpus these tests
  expect: 62 repository tables, about 37.7 GB (`eod_bars_history` 14.7 GB,
  `eod_indicators_history` 14.4 GB, `eod_eligibility_history` 5.1 GB), plus 11 old-only
  `watchlist_*` tables of about 3.85 GB.
- Not established: whether that corpus is intact. Its files carry future-LSN damage
  (F-MD-B18-A002-007 and -010).

Nothing was changed to make the suite pass: no test, no expectation, no data import.

## Options

1. **Recommended.** Split the two causes.
   - (a) Drift test: correct the exact-set assertion to "the out-of-scope orphans are a subset of
     the declared watchlist set, and the in-scope set is empty". The guard's own purpose — a *new*
     orphan must be classified — is unchanged, and it is shown by a probe adding an unknown orphan.
     This is a test-tooling correction under the CI, with a probe; it is not a proof claim.
   - (b) Corpus oracle: it stays as it is. It becomes green only against a real corpus. Restoring
     one belongs to the old-data recovery track, MD-DEP-0016: an isolated recovery instance on a
     copy of `data_260914`, then a logical export, then import into the clean `tradeaxis`. That
     needs your separate authorization.
2. Declare the corpus oracle deployed-environment evidence rather than clean-install evidence, and
   let it skip, with a named reason, when the corpus is absent. A skip is not a PASS, so the
   full-suite criterion would then have to be re-read under a governance decision.
3. Rebuild a corpus by running the market-data pipelines against the provider. The result is a
   different corpus, with new knowledge times; it is not the deployed one, and it is expensive.

Until you decide, `MD-DEP-0015` stays BLOCKING against its D003 criterion. Clean availability and
integrity are established and recorded separately, and per-predicate B18 review can continue.

## Decision and partial resolution — 2026-09-14

`D-MD-B18-A002-004` records the user's choice of option 1. The user wrote "F-001"; that is read as
this finding, the only one with a pending recommendation.

**(a) Done.** `MigrationIntegrityAndDriftTest::test_no_applied_migration_has_lost_its_file` now
asserts two things:
- the in-scope orphan set is empty;
- the out-of-scope orphan set is a subset of `DECLARED_OUT_OF_SCOPE_ORPHANS`, the eleven watchlist
  migrations, which are unchanged.

The CI amendment was issued first. On the clean-instance ledger the control ran at 2 tests /
3 assertions. Three probes, injected into the applied-migration list, each landed once and was
restored hash-equal (file sha256 432BC2CC…950F), with controls green around each:

| Probe | Injected row | Result |
|---|---|---|
| P-a1 | an undeclared watchlist-like orphan | red — "an undeclared out-of-scope orphan appeared" |
| P-a2 | an in-scope market-data orphan | red — "schema drift" |
| P-a3 | a declared watchlist orphan | green, as subset semantics require |

Artifacts are in `storage/app/market-data/evidence/MD-B18-A002/f011a-orphan-subset-20260914/`.

**(b) Unchanged by decision.** The seven deployed-corpus population controls stay red on a clean
database. They turn green only after the `MD-DEP-0016` recovery, which is not yet authorized.
`MD-DEP-0015` and MD-B18 closure's full-suite condition therefore still depend on that recovery.
`F-MD-B18-A002-012` separately suspends MariaDB runtime proof on the current instance.
