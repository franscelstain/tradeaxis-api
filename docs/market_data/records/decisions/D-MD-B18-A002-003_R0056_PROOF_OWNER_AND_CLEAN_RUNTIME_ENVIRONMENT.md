# Decision — R0056 proof owner and clean runtime environment

- ID: `D-MD-B18-A002-003`
- Stage / Attempt / Work / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Epoch: `MD-REBASELINE-20260820-001`; strategy freeze `MD-STRATEGY-FREEZE-20260903-001`
- Issued: 2026-09-14T10:25:00+07:00
- Status: `ISSUED — USER_DECISION`
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Decides: `MD-DEP-0014`, the decision only. `F-MD-B18-A002-008` stays open until R0056 is proven.
- Governs: the recovery path for `MD-DEP-0015` / `F-MD-B18-A002-010`
- Inputs: `E-MD-B18-A002-006`, `E-MD-B18-A002-007`; directed by `CI-MD-B18-A002-001`

## Authorization

On 2026-09-14 the user chose Option B for `MD-DEP-0014` ("Gunakan Opsi B."). For `MD-DEP-0015`,
the user isolated the old data directory as `D:/xampp/mysql/data_260914` and directed that current
MD-B18 work use only a clean MariaDB instance. Neither choice revises strategy, waives proof,
changes any other owner, or treats a desired result as evidence.

## Decision 1 — who owns proof of `MD-S050-R0056`

- `MD-B18` stays **primary** proof owner. `MD-B22` becomes **supporting**.
- The predicate keeps its full wording and is not narrowed. Its executable responsibility does not
  move to B22.
- The final production relock act follows the relock semantics B22 already owns, through
  `MD-S059-R0037` and `MD-S029-R0002`.
- For this proof, "actual production path" means three things together:
  - the MariaDB production engine;
  - the production repositories under `App\Infrastructure\Persistence\MarketData`;
  - the schema produced by the repository's migrations.

  It does not mean the production deployment, and it does not mean production data.
- The row stays `MANDATORY` in B18, so the denominator stays at **115**. B22's primary population
  stays at 28. The matrix changes in one row: `supporting_stages` becomes `MD-B22`, and a note is
  added.

### Guard design, reviewed before implementation

**Positive guard: `test_the_whole_production_path_corpus_executes_and_passes_on_mariadb`**

1. **Required set.** The set is derived, never listed by hand. The eight cases come from MD-S050's
   `Required fixtures include:` list, mapped through `productionPathMap()`. The publication fixture
   is added to them, giving exactly nine. A contract case with no mapping, or a mapped method that
   does not exist, is reported as `MISSING`.
2. **Execution.** The guard runs each fixture body in-process, on the guard's own MariaDB
   connection. Each run happens inside a savepoint that is rolled back afterwards, so fixtures that
   share seed identities cannot collide. Each outcome is classified as one of: `PASS`, `FAILED`,
   `SKIPPED`, `INCOMPLETE`, `THREW`, `NO_ASSERTIONS`, `MISSING`. Only `PASS` with at least one
   assertion counts.
3. **No skipping.** If the engine is unreachable, or the schema lags behind the migrations, the
   aggregate **fails**. The individual fixtures keep the suite's convention of skipping in that
   case; the aggregate does not, because a corpus that never ran is not an executed prerequisite.
   The aggregate also asserts all of the following:
   - the driver is `mysql`;
   - the version string contains `MariaDB`;
   - it is connected to the expected database;
   - no repository migration is pending on that connection.
4. **Failure message.** A red result names every fixture that did not pass, and why.

**Negative guard: `test_the_corpus_harness_refuses_every_way_a_fixture_can_fail_to_count`**

This drives the same harness with probe bodies defined in the class: one that throws, one whose
assertion fails, one that skips, one marked incomplete, one that makes no assertions, and one
method name that does not exist. It also runs a passing control. It asserts that every non-passing
outcome is classified as non-passing, and that the control counts.

**Why this is sufficient.**
- F-008's counterexample (an exception thrown at the entry of the publication fixture) turns the
  positive guard red by construction.
- Each of "skipped", "not executed" and "missing" has its own named branch, and each branch is
  checked both by the negative guard and by an on-disk probe.
- An out-of-process JUnit gate was rejected: the proof gate requires in-suite guard methods, and an
  in-process harness runs the same bodies on the same engine, under its own probes.

**Limits.** The guard does not perform or authorize a relock, and it does not touch production data.
The claim that the fixtures go through production repositories rests on reading their bodies: each
one calls an `App\Infrastructure\Persistence\MarketData` repository.

**Probes required before `PROVEN`:**

| Probe | Mutation | Expected |
|---|---|---|
| P1 | F-008's exception at the entry of the publication fixture | aggregate red |
| P2 | a skip inside one anti-survivorship fixture | aggregate red |
| P3 | an early return in one fixture, so it makes no assertions | aggregate red |
| P4 | one contract case removed from `productionPathMap()` | aggregate red |
| P5 | one fixture expectation falsified | aggregate red |
| P6 | `MARKET_DATA_MARIADB_TEST_DATABASE` pointed at a database that does not exist | aggregate fails, and does not skip |

Each probe must land exactly once, with controls green before and after, and each file restored by
byte copy.

## Decision 2 — clean MariaDB, and the old data

`D:/xampp/mysql/data_260914` is the isolated old data directory, which may be damaged. It is a
recovery source only and is not disposable. The following are forbidden:

- deleting or modifying it;
- repairing it in place;
- copying raw InnoDB or system files from it into any clean instance;
- using the old instance as runtime proof.

Current B18 runtime proof uses only the clean instance made from the XAMPP template. The steps, in
order:

1. Verify the engine, version and configuration.
2. Verify that no application database remains.
3. Create `tradeaxis` and `tradeaxis_testing`.
4. Apply the repository's migrations to both.
5. Verify the schema.
6. Run the targeted tests, then the full suite.

`MD-DEP-0015` resolves only when all of the following hold:

- the instance's clean provenance is verified;
- there is no application residue;
- both databases are created and fully migrated;
- targeted tests and the full suite pass with zero skips;
- an integrity check of both databases' tables reports clean.

Availability, integrity and runtime proof are reported separately. A server that starts, or a
connection that succeeds, is not resolution.

Recovering the old data is a separate track, begun only once the runtime is stable:

1. Inventory which data can be rebuilt from the repository (migrations, seeders, importers,
   fixtures, `storage/**`), and which exists only in the old databases.
2. Never import raw database files.
3. If old data must be saved, recover it through a separate isolated recovery instance and export
   it logically, as SQL or CSV.

That track is registered as its own non-blocking dependency, and never mixed with MD-B18 proof.

## Impact

- Strategy bytes, source identities and all issued BL/E/D/SC records are untouched.
- Counted changes: one matrix row, MD-DEP-0014 resolved as a decision, one new dependency for old
  data recovery, and the record registrations.
- MD-B18-A002 continues. The return to MD-B19-A001 still requires valid B18 closure and resolution
  of MD-DEP-0009.
- No commit or PR, no production relock, and no B21/B22 entry is authorized by this decision.
