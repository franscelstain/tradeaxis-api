# Change Impact Declaration — `MD-B10-A003`

- ID: `CI-MD-B10-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B10` / `MD-B10-A003` / `MD-B10-A003-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20261005-001`
- Predecessor: `MD-B10-A002`, closure `SC-MD-B10-A002-001` — **retained as immutable history, not edited**; its claim (`1072/1072` under `MD-STRATEGY-FREEZE-20260925-001`) is not rewritten as if it had failed
- Remediates: `F-MD-B18-A002-033` (`P1` for `MD-S003-R0025`)
- Blocking dependency: `MD-DEP-0022` — blocked logical stage `MD-B18` (`MD-B18-A002`, resume at candidate-v4); active remediation stage `MD-B10`; return-to `MD-B18-A002` only after `SC-MD-B10-A003-001`
- Authority: `DOC-CHG-20261005-001` (owner decision `D-MD-B18-A002-015`, evidence `E-MD-B18-A002-098`), issued under `MD-B18-A002` before this attempt
- Status: `CLOSED — REMEDIATION COMPLETE` (`SC-MD-B10-A003-001`)
- Strategy meaning change: `NO` in this attempt. The correction was made and recorded under `DOC-CHG-20261005-001`; this attempt makes the implementation conform to it and adds no semantic rule.
- Governance authority change: `NO`. Governed tooling change declared below (the B10 successor binder and its gates), not a change of a standard.

Issued after `MD-B10-A003-BL001` and before any runtime mutation of this attempt, so that it directs the attempt rather than describing it afterwards.

## Objective and exact rule impact

Make the V2 semantic-hash and publication-manifest producers, and the run's pre-activation freshness state, conform to the corrected authority so that a `READABLE` publication whose requested trade date precedes the effective activation marker carries `freshness_state = NOT_APPLICABLE` and is hashed as that exact value, so that `MD-B18` candidate-v4 can derive the expectation independently and compare it with a legitimate target.

Rules revalidated (exactly three, `NOT_ASSESSED` at entry by `E-MD-B10-A003-001`): `MD-S005-R0056` (eligibility row binds the canonical freshness state), `MD-S005-R0071` (the publication manifest binds the freshness state), `MD-S045-R0058` (the manifest freshness is the truthful consumer state at the evaluated context). Their rule text is unchanged. `E-MD-B18-A002-098` named all three as proven by `E-MD-B10-A002-017`; the matrix shows `MD-S045-R0058` bound to `E-MD-B10-A001-001`, which this declaration records as a correction of that attribution.

The other 1069 mandatory predicates keep their bindings. They are not re-proven mechanically; the producers they run through are covered by impact-driven regression (below). If a predicate claimed unaffected fails because of this attempt, that is new impact evidence, recorded and not forced green.

## Affected surfaces and proof boundary

- Strategy: none changed here.
- Runtime (`app/`): a new domain class for the freshness vocabulary and the applicability rule; `EodRunRepository` (three run creators: new run, as-known replay run, promote run from seed); `ArtifactSemanticHashService` (vocabulary, V2 context guard); `EodPublicationRepository` (V2 manifest payload and the manifest view). The V1 manifest payload and V1 profile are not changed. No migration: `eod_runs.freshness_state` is a nullable string of 32.
- Tests: the B10 tests that pin the superseded mapping are replaced by tests that state the corrected one; new unit and DB tests for the applicability rule, determinism against the wall clock, the vocabulary, hash sensitivity and non-relabelling of sealed history; mutation probes.
- Governed tooling: the B10 successor binder and the B10 proof gate assume one successor layer; this attempt re-owns three predicates already owned by earlier layers (two by `MD-B10-A002`, one by `MD-B10-A001`). Declared as `F-MD-B10-A003-001`; the change is limited to pattern acceptance for earlier layers and evidence validation against each profile's own scope.
- Backfill/replay: none. Sealed publications are not rewritten, re-hashed or relabelled. A run created after this attempt carries the corrected state; a correction or republication of an old date builds a distinct publication.
- Operations/config: none. `market_data.scope.operational_start_date` is unchanged and still `null` in every environment.
- Evidence mechanics: a raw proof package under `storage/app/market_data/evidence/MD-B10-A003/`, linked from governed evidence.

## Compatibility and residue risk

- A candidate package frozen to an executable build (`MD-B18` candidates v1–v3) is invalidated by any `app/` edit (owner decision Q5=B). Candidate-v3 is immutable reviewed history; its fixture tests are expected to turn red against the changed target because of the build identity and the freshness member. This is recorded, not repaired here; candidate-v4 is a separate work unit.
- A publication sealed before this attempt keeps `NOT_AVAILABLE` with `READABLE`, audit-valid under its own sealed identity. A run created before this attempt and sealed after it keeps its legacy label and is hashed as before.
- Unchanged-rerun detection of a date sealed before this attempt: the freshness member now differs, so a rerun builds a distinct publication; that is a correction, not a fake version.
- The activated world has no freshness evaluator (`F-MD-B18-A002-033` row 7); this attempt leaves an activated run's pending label and its normalisation as they were.

## Dependencies and relationships

`MD-DEP-0022` BLOCKING for `MD-B18`; `MD-DEP-0017` unchanged. `MD-B17` (`244/257`) is a separate bounded work unit; this attempt records any shared-producer relationship and does not repair `MD-B17`.

## Result (2026-10-05)

The three predicates are `PROVEN_CURRENT` (`E-MD-B10-A003-002`) and promoted through the governed successor binder (`E-MD-B10-A003-003`); `MD-B10` is closed `1072/1072` under `MD-B10-A003` (`SC-MD-B10-A003-001`). Full MarketData suite on the final tree: 2847 tests, 40132 assertions, 17 failures, all classified (8 governed expected states, 9 expected invalidations of the immutable candidate-v3 fixture tests). The 43 governance gates are identical to their state before the attempt. One entry decision was corrected (`MD-S022-R0052` no longer lists `MD-B10` as a supporting stage; the B10 closure gate counts such rows as moved). `MD-DEP-0022` is resolved. `MD-B17` (244/257) is untouched.
