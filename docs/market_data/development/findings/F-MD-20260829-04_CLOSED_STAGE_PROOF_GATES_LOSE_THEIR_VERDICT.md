# F-MD-20260829-04 — Three closed-stage proof gates could no longer return a meaningful verdict

- Status: `RESOLVED`
- Severity: `P2`
- Scope: package-level tooling; not owned by a stage attempt
- Raised: 2026-08-29, by the post-closure gate sweep of `MD-B14-A001`
- Resolved: 2026-08-29, in the same work unit
- Affected stages: `MD-B08` (proof gate), `MD-B11` and `MD-B12` (proof self-tests)
- Stage records rewritten: **none**. No traceability row, evidence record or closure manifest was altered.

## Finding

Three governed artifacts for already-closed stages had lost the ability to report anything but one
fixed verdict. All three share one root shape: **the artifact could only describe a stage state that
no longer exists, and had no way to describe the state the stage is actually in.**

### 1. `MarketDataSourceResilienceProofGate` — always `FAIL`

The gate has two modes. `validateReadiness()` asserts every `MD-B08` predicate is still
`NOT_ASSESSED` with no evidence, which is correct *before* the stage returns runtime proof.
`validateBound()` asserts the closed state instead.

Two defects made the second mode unreachable:

- **The entrypoint never read `$argv`.** It called `validateReadiness()` unconditionally. `--bound`
  was accepted by the shell and silently ignored, so both invocations produced byte-identical output.
- **`validateBound()` was dead code**, called from nowhere in the repository, and it took a single
  `string $evidenceId`. That signature cannot express the real closed state: `MD-B08-A002` re-proved
  one predicate, so 138 rows are bound to `E-MD-B08-A001-001` and one — `MD-S067-R0010` — to
  `E-MD-B08-A002-001`. Even if it had been wired up, it would have failed on that row.

The result: from the moment `MD-B08` closed, the gate reported `premature_satisfied: 139` on every
run. A gate with one reachable verdict detects no regression. Nothing had been watching the `MD-B08`
invariants since closure.

### 2. `MarketDataCorporateActionProofSelfTest` and `MarketDataAnalyticalPriceProductProofSelfTest` — always `FAIL`

Both decide which mode to run by scanning the matrix for their stage's evidence, and both matched a
single attempt only — `/^E-MD-B11-A001-\d{3}$/` and `/^E-MD-B12-A001-\d{3}$/`. Neither stage is bound
that way:

| Stage | Bindings |
|---|---|
| `MD-B11` | 138 to `A001-001`, 34 to `A002-001`, 30 to `A003-001` |
| `MD-B12` | 45 to `A001-001`, 15 to `A002-001`, 15 to `A003-001` |

Finding a binding they did not recognise, both silently fell back to pre-binding mode and reported
`control_prebinding: false`. The control was red, which — under
`F-MD-B00-A001-002` and the recorded control-failure discipline — makes every mutation verdict
after it meaningless. Both files were reporting a list of "fails closed" results that proved nothing,
because the gate was already failing before any mutation was applied.

## Why this was not caught earlier

Each stage's closure ran its gate at the moment the gate was still meaningful, and recorded a true
statement scoped to that moment. `SC-MD-B08-A001-001` says the readiness cycle was green **"before
runtime binding"** — accurate, and not a claim about any later run. No closure claimed a
post-binding gate result that was false.

What no stage did was run its own gate *again* after closing. These artifacts are only executed
during the stage that owns them, so a verdict that becomes permanently wrong afterwards is invisible
until something sweeps the whole set. The `MD-B14-A001` post-closure sweep was the first to do that.

## Remediation

**`MarketDataSourceResilienceProofGate`**

- The entrypoint dispatches on `--bound` and reports which mode ran.
- `validateBound()` takes a map of rule id to required evidence id, derived from
  `MarketDataSourceResilienceTraceabilitySpec::REMEDIATED_RULES` rather than read back from the
  matrix, so the expectation cannot agree with whatever it happens to find. It rejects a
  non-`SATISFIED` predicate, a missing binding, a binding to another stage's evidence, a binding to
  an evidence id no governed record exists for, and either rule bound to the other attempt's
  evidence.
- Bound mode also re-runs the reason-code scope check, the implementation invariants and the
  proof-surface existence check. A closed predicate whose named guard no longer exists is a false
  claim, not a historical one.

**The two self-tests** now recognise any governed evidence id for their stage
(`/^E-MD-B11-A\d{3}-\d{3}$/`, `/^E-MD-B12-A\d{3}-\d{3}$/`), so both run in `BOUND_CLOSURE` mode with
a green control.

**`MarketDataSourceResilienceProofSelfTest`** carried the matching defect and was repaired with the
gate: its `baseline_proof_readiness` control ran readiness against the current closed rows, so the
control was red and the two readiness mutations below it passed vacuously. Readiness is now
exercised against a reconstruction of the pre-proof state. The file grew from 16 checks to **32 — 4
controls and 28 fail-closed mutations** — adding the bound-mode control and twelve bound-mode
mutations, including both attempt-attribution cases.

## Verification

- `MD-B08` bound mode: **PASS** — 139/139 satisfied, 2 governed evidence records, 0 unbound, 0 wrong evidence.
- `MD-B08` readiness mode against the closed state: **FAIL**, as it must be. The two modes now disagree about the same state, which is asserted as its own check.
- `MD-B08` runtime guards, all 38 methods named by the proof map: **PASS — 46 tests / 474 assertions**.
- `MD-B08` self-test: **PASS — 32 checks, 4 controls green, 28 fail-closed**.
- `MD-B11` and `MD-B12` self-tests: **PASS** in `BOUND_CLOSURE`.
- Traceability rows altered: **0**. Verified by diffing every `MD-B08` row against `HEAD`: 233 rows, zero changed. The 139 legitimate bindings were not touched to make anything green.

## Boundary

No stage was reopened. The defect was in `DEVELOPMENT` / `CURRENT_PROCESS_SUPPORT` tooling, not in
any stage's semantic invariant, governed runtime evidence, or traceability binding, and no closure
manifest made a claim that these repairs contradict. `STAGE_EXECUTION_AND_REWORK_STANDARD.md` drives
re-entry from a need to change a stage's proof, coverage or evidence; none of those changed, so
`MD-B08`, `MD-B11` and `MD-B12` remain terminal `DONE` / `PASS` on their existing closures.

Related: [[F-MD-B00-A001-002]] recorded the same family — a self-test whose assertions could not
fail — at the start of this package.
