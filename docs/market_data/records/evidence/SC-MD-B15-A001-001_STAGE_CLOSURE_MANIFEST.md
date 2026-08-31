# MD Stage Closure Manifest — SC-MD-B15-A001-001

- ID: `SC-MD-B15-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B15` / `MD-B15-A001` / `MD-B15-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Change Impact Declaration: `CI-MD-B15-A001-001`
- Governed evidence: `E-MD-B15-A001-001`
- Predecessor stage closure: `SC-MD-B14-A001-001`
- Dependency: `MD-DEP-0004` discharged for `MD-B15` at stage entry; remains `OPEN_NON_BLOCKING` for the five unopened stages
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, immutable after issue
- Issued at: `2026-08-30T19:05:00+07:00`

## Terminal coverage

- Mandatory denominator: **221**
- Mandatory `SATISFIED`: **221/221**
- Conditional not applicable: **0**
- Conditional pending: **0**
- Optional capability: **2**
- Reference/context: **133**, every one carrying a recorded decision
- Transitional applicability: **0**
- `MD-B15` mixed-classification debt: **0**; unexplained-reference debt: **0**
- Stage rows: **356**
- Evidence binding: all 221 mandatory predicates are atomically bound to `E-MD-B15-A001-001` by `MarketDataCoverageGateProofBinder` across **29 proof families**, each carrying a positive guard and a distinct fail-closed guard

The Stage Register carried this stage as `0/89`. The real denominator is **221**. The 132-row
difference is not a re-scoping: 108 predicates arrived filed as reference context or as mixed-run
siblings while carrying real coverage obligations — the forbidden expectation-exclusion list, the
numerator exclusions, the three gate-state definitions, the reason-code mapping, the required
audit-visible fields and the forbidden fallback targets. A coverage rule filed as context is still a
coverage rule. The denominator was fixed by the stage-entry normalization before any material change
and has not moved since.

## Closure conditions, evaluated against the standard

`MarketDataCoverageGateClosureGate` evaluates each condition of
`STAGE_CLOSURE_MANIFEST_STANDARD.md` against live state and exits non-zero on any unmet condition.
It was **shown to fail on each condition independently** before being relied on — see
`MD-B15-A001-007_closure_condition_probes.txt`: eight probes, all caught, controls green before and
after, and the matrix byte-identical to its pre-probe copy afterwards.

| Condition | Result |
|---|---|
| Zero required rows with transitional `MANDATORY_OR_CONDITIONAL` | **MET** — 0 |
| Zero `CONDITIONAL_PENDING` / `APPLICABILITY_PENDING` rows | **MET** — 0 |
| All `MANDATORY` and `CONDITIONAL_APPLICABLE` rows `SATISFIED` | **MET** — 221/221 |
| Every `CONDITIONAL_NOT_APPLICABLE` row proves its condition false | **MET** — 0 such rows; the probe introduces one without a basis or guard and the gate refuses it |
| Deterministic parent/context binding and normalized predicate on every required fragment | **MET** — 221/221 |
| No proof invalidated by semantic-context or applicability correction still counted | **MET** — 0 foreign rows carry this stage's evidence |
| Raw-artifact integrity: present, readable, hashing to the recorded value | **MET** — 6 evidence artifacts, 0 mismatched, 0 unreadable |
| Governed evidence reachable and linking its raw-artifact manifest | **MET** |

## Executed proof

Admitted by `E-MD-B15-A001-001` (manifest
`storage/app/market-data/evidence/MD-B15-A001/MANIFEST.json`, sha256
`C26BE7DFBFA5D691E9A56562EEDBF2B2F5151A77A8F589729B0B9A5B4022A9DA`, 6 artifacts) and by this
closure's own manifest `MANIFEST-CLOSURE.json`, sha256
`DA66B986C5AA7E934794BC988AD0BC96F69732C22A18E389BFC3738CDC43C3B0`, 3 artifacts. The evidence
manifest hash was re-verified at closure and still matches the value recorded when it was issued.

- Targeted proof of every guard named by the proof map, executed by name: **PASS — 105 tests / 1119 assertions**, zero failures, errors or skips, exit 0.
- Remediation guards for the component-identity fix and the edge-case boundary: **PASS — 14 tests / 855 assertions**, exit 0.
- Post-binding full regression: **PASS — 1986 tests / 20172 assertions**, zero failures or errors, exit 0.
- Fail-closed mutation probes on the implementation guards: **PASS — 6 probes, 6 caught**, control green before and after.
- Closure-condition probes: **PASS — 8 probes, 8 caught**, controls green.
- Proof gate in `--bound` mode: **PASS — 221/221, 29 families, runtime pending 0**.
- Proof mutation self-test: **PASS — 11/11** in `BOUND_CLOSURE`, `control_bound` green before any mutation verdict was read.

Execution environment: PHP 7.4.33, PHPUnit 9.6.34, MariaDB 10.4.27, `tradeaxis` and
`tradeaxis_testing` both at 80 applied migrations, no drift. This attempt added no migration.

## Required semantics proven

The sixteen semantic claims enumerated in `E-MD-B15-A001-001`, covering: the `EXPECTED + UNKNOWN`
denominator and the single lawful exclusion path; the prohibition on dormancy, zero volume,
illiquidity, provider absence and current activity reducing it; `UNKNOWN` held fail-safe; the
delivery numerator and its five exclusions; the six separate dimensions; the locked `0.98` threshold
resolving identically through both config keys; a null ratio and `NOT_EVALUABLE` on an empty
denominator; legacy `BLOCKED` normalized; `FAIL` and `NOT_EVALUABLE` finalizing as `NOT_READABLE`
with fallback that never promotes the failed candidate; pointer enforcement; component identity on
every coverage evidence record; stale rows never counting; retry as acquisition rather than bypass;
the exclusion path exercised by a governed test rather than trusted for being rare; and the
prohibition on citing coverage `PASS` as evidence of correctness or completeness.

## Applicability outcomes

None. `MD-B15` carries no conditional row: every predicate in scope is unconditionally applicable,
and the two optional-capability rows are outside the denominator.

## Defects found during this attempt

Three, all recorded in `E-MD-B15-A001-001` or below with detection method and remediation.

**One shipped defect.** Coverage evidence bound only its own contract version. The contract requires
every record to bind the universe resolver version and the calendar and trading-status revision
identities, and generalises it: evidence binds the version of every component its correctness
depends on. The evaluator published three names for what the contract said and nothing about the
components that produced the numbers — the exact condition the contract warns of, where a stored
result cannot be distinguished from one produced by a resolver since found defective.

**One defect in this attempt's own classification.** `MD-S024-R0065` was filed as a routing
statement. It is the companion obligation to `MD-S024-R0064`, which the matrix already carried as
required. The classification consistency gate reported it as a surviving mixed-run member. The
matrix was restored to its pre-normalization state and the pass re-run once with the corrected
classification rather than patched in place; the denominator moved from 220 to 221.

**One defect in this attempt's own guard, which uncovered a second implementation defect.** The
first component-identity guard asserted only that `coverage_trading_status_revision_ids` was an
array, which an empty array satisfies. A probe that gutted the revision lookup left it green — a
clause of the contract nothing was holding. Strengthening the guard with a real suspension fixture
made it fail against the implementation: the identity was read from the universe *after* the
exclusion filter, so the status revisions worth binding — the ones that produced `NOT_EXPECTED` —
were exactly the ones already removed. On a date where every listing was excluded, the
`NOT_EVALUABLE` path bound no revisions at all, which is the case where the identity matters most.
The identity list is now computed once from the raw universe and carried into the non-evaluable
path, and a sixth probe guards that specific regression.

## Residue

**`CONFORMANT`.** Scope: coverage evaluation, expectation resolution, finalize decision,
publishability and pointer enforcement, evidence export and replay comparison.

- Coverage records produced before this attempt bind no resolver or revision identity. They are not back-stamped: writing an identity onto a record whose producer was never recorded would assert a fact that was never true. `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` already governs them — such evidence is either re-derived under current components or explicitly qualified wherever it is cited.
- `F-MD-B01-A014-001` remains open, owned by `MD-B19`. `F-MD-B14-A001-001` remains open, a reason-code vocabulary matter requiring a strategy change. Neither blocks `MD-B15`.

## Findings and dependencies

- Blocking `MD-B15` finding: **none**. No new finding was opened.
- `MD-DEP-0004` discharged for `MD-B15` by the stage-entry normalization.
- No predecessor closure, Baseline Lock, prior evidence or failed proof artifact was rewritten.

## Integrity / closure controls

- `MD-B15` bound proof gate: **PASS — 221/221, 29 proof families, runtime pending 0**.
- `MD-B15` proof mutation self-test: **PASS — 11/11** in `BOUND_CLOSURE` with a green control.
- `MD-B15` closure condition gate: **PASS — 8/8 conditions met**, each independently falsifiable.
- Classification consistency: **PASS — `MD-B15` mixed-run debt 0, unexplained-reference debt 0**.
- Traceability applicability gate: **PASS**. Scope boundary completion gate: **PASS**.
- Documentation integrity: **PASS — 1004 physical / 1004 role rows / 1004 Document IDs / 1004 current-verification rows**.
- Relationship integrity: **PASS — 186 work records / 339 relationships**, zero validity errors or completeness gaps.
- Relationship/document mutation self-test: **PASS**, controls and post-restore controls green.
- `CURRENT_STATE` deterministic generation: **PASS**, repeated generations byte-identical.
- Independent re-derivation of the binding scope against a pre-binding copy of the matrix: exactly **221 rows changed, all owned by `MD-B15`, zero foreign rows touched** — checked outside the binder rather than trusting its own assertion.

## Successor / exact resume

`MD-B15` is terminal **DONE / PASS**. `MD-B16` remains **NOT_STARTED** and is not opened by this
closure work unit.

Single exact resume point after this closure: begin **`MD-B16` stage-entry preflight**; rederive
current `MD-B16` applicability, ownership and classification from current authority — including its
17 mixed-classification members and 53 undecided reference rows — and issue the first valid `MD-B16`
Baseline Lock and Change Impact Declaration before any material mutation. No `MD-B15` predicate
proof is inheritable by `MD-B16`.
