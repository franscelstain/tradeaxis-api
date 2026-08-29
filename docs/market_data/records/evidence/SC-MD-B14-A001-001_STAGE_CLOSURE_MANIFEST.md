# MD Stage Closure Manifest — SC-MD-B14-A001-001

- ID: `SC-MD-B14-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B14` / `MD-B14-A001` / `MD-B14-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Change Impact Declaration: `CI-MD-B14-A001-001`
- Governed evidence: `E-MD-B14-A001-001`
- Predecessor stage closure: `SC-MD-B13-A001-001`
- Dependency: `MD-DEP-0004` discharged for `MD-B14` at stage entry; remains `OPEN_NON_BLOCKING` for the mixed-classification members of the six unopened stages
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, immutable after issue
- Issued at: `2026-08-29T14:35:00+07:00`

## Terminal coverage

- Mandatory denominator: **147**
- Mandatory `SATISFIED`: **147/147**
- Conditional not applicable: **1** — `MD-S038-R0028`, with an evidenced false condition and a standing guard on that condition
- Conditional pending: **0**
- Optional capability not requested: **6**
- Reference/context: **229**, every one carrying a recorded decision
- Transitional applicability: **0**
- `MD-B14` mixed-classification debt: **0**; unexplained-reference debt: **0**
- Stage rows: **383**
- Evidence binding: all 147 mandatory predicates are atomically bound to `E-MD-B14-A001-001` by `MarketDataIndicatorEngineProofBinder` across **25 proof families**, each family carrying a positive guard and a distinct fail-closed guard

The denominator is not provisional. It was fixed by the stage-entry normalization before any material
change, recorded in `MD-B14-A001-BL001`, and has not moved since: 383 rows examined, 59 already
mandatory or transitional, 61 promoted mixed-run siblings, 27 carried in as required, giving 147.

## Closure conditions, evaluated against the standard

`MarketDataIndicatorEngineClosureGate` evaluates each condition of
`STAGE_CLOSURE_MANIFEST_STANDARD.md` against live state and exits non-zero on any unmet condition.
It was **shown to fail on each condition independently** before being relied on — see
`MD-B14-A001-007_closure_condition_probes.txt`, eight probes, all caught, controls green before and
after.

| Condition | Result |
|---|---|
| Zero required rows with transitional `MANDATORY_OR_CONDITIONAL` | **MET** — 0 |
| Zero `CONDITIONAL_PENDING` / `APPLICABILITY_PENDING` rows | **MET** — 0 |
| All `MANDATORY` and `CONDITIONAL_APPLICABLE` rows `SATISFIED` | **MET** — 147/147 |
| Every `CONDITIONAL_NOT_APPLICABLE` row proves its condition false | **MET** — 1/1 |
| Deterministic parent/context binding and normalized predicate on every required fragment | **MET** — 147/147 |
| No proof invalidated by semantic-context or applicability correction still counted | **MET** — 0 foreign rows carry this stage's evidence |
| Raw-artifact integrity: present, readable, hashing to the recorded value | **MET** — 6 evidence artifacts, 0 mismatched, 0 unreadable |
| Governed evidence reachable and linking its raw-artifact manifest | **MET** |

## Executed proof

Admitted by `E-MD-B14-A001-001` (manifest `storage/app/market-data/evidence/MD-B14-A001/MANIFEST.json`,
sha256 `C5DC95DAE883BCD98DEAD5E9057632C70B990EA7EF877992310EBAC09305122A`, 6 artifacts) and by this
closure's own manifest `MANIFEST-CLOSURE.json`, sha256
`CAF638D4D43CFAEE96D9BAA2630D3387D9DBE79761E45B8A9711C008B5EA3EDE`, 3 artifacts.

- Targeted proof of every guard named by the proof map, executed by name: **PASS — 51 tests / 1026 assertions**, zero failures, errors or skips, exit 0.
- Remediation guards for the three closed gaps and the horizon-role manifest: **PASS — 19 tests / 1044 assertions**, exit 0.
- Post-binding full regression: **PASS — 1972 tests / 19303 assertions**, zero failures or errors, exit 0.
- Fail-closed mutation probes on the implementation guards: **PASS — 10 probes, 10 caught**, control green before and after the probe set.
- Closure-condition probes: **PASS — 8 probes, 8 caught**, controls green.
- Proof gate in `--bound` mode: **PASS — 147/147, 25 families, runtime pending 0**.
- Proof mutation self-test: **PASS — 11/11** in `BOUND_CLOSURE`, `control_bound` green before any mutation verdict was read.

Execution environment: PHP 7.4.33, PHPUnit 9.6.34, MariaDB 10.4.27, `tradeaxis` and
`tradeaxis_testing` both at 80 applied migrations, same head, no drift. This attempt added no
migration; all three remediations are computation and declaration changes over columns the schema
already carried.

## Required semantics proven

The sixteen semantic claims enumerated in `E-MD-B14-A001-001` under `required_semantics_proven`,
covering: recompute source/master read-only behaviour and command isolation; the versioned per-field
registry and its agreement with the deployed schema and hash serializer; per-field reason-coded
nullability with the compatibility primary reason retained beside it; the four distinct null causes;
the published ATR level and its stable Wilder seed; exact trading-date window dependencies; declared
horizon roles and the published fifty-session contamination radius; the prohibition on reading a
non-null value as evidence of a clean window; actual-versus-proxy separation; shortened-session
neutrality; one calendar identity across five window surfaces; and cross-contract format alignment.

## Applicability outcomes

`MD-S038-R0028` — technical-only recompute mode boundary — is `CONDITIONAL_NOT_APPLICABLE`.

- Condition: a technical-only recompute mode exists as an accepted production command.
- Condition state: **FALSE**. `Indicator_Recompute_Source_Scope_Contract.md` states the mode does not exist; the `market-data:eod-indicators:recompute-current` signature carries no such option; no command under `app/Console/Commands/MarketData` mentions one.
- Standing guard: `IndicatorEngineBoundaryB14Test::test_no_technical_only_recompute_mode_exists_to_carry_the_conditional_obligation`, proven to fire when the mode is introduced (probe 09).
- This is a terminal applicability outcome with an evidenced false condition and an executable guard on that condition. It is not a proof of the obligation and is not counted in the denominator.

## Defects found during this attempt

Five, all recorded in `E-MD-B14-A001-001` with detection method and remediation. Three were
executable gaps in the shipped engine — the registered baseline field `atr14` never computed,
`null_reasons_json` never written so the compatibility primary reason stood alone in the state the
nullability contract forbids, and no versioned per-field registry at all. Two were defects in this
attempt's own conduct: a probe helper that restored with `git checkout --` and discarded uncommitted
implementation work, and an invalid relationship row the relationship gate rejected.

A sixth was found by the closure gate itself, after binding and while verifying closure:
`MD-S023-R0063` was counted in the denominator carrying neither the parent/context binding nor the
normalized predicate section 3 requires, having been carried in as `MANDATORY` by `MD-B09-A001`
without them; and it had been filed under the wrong proof family, bound to a contamination-semantics
guard when its predicate is about the mutation-impact resolver. Both were corrected, the whole
denominator was returned to pristine, and the binding was re-run atomically rather than patched in
place. The closure gate found this; inspection had not.

## Residue

**`CONFORMANT_WITH_DECLARED_FINDING`.** Scope: the indicator computation, recompute command,
nullability and reason surface, dependency manifest and field registry.

- `F-MD-B14-A001-001` (P3, `OPEN`): the registered `INDICATOR` reason vocabulary has no code for a field nulled by an absent optional source fact. Five fields are null and absent from the reason map rather than null with a wrong reason. Remediation is a strategy change to `Reason_Codes_Registry.md`, reserved by `DOCUMENT_CHANGE_POLICY.md`. No predicate in the `MD-B14` denominator is left unprovable by it, so it does not block closure.
- Indicator rows published before this attempt carry no `atr14` value and no `null_reasons_json`. They are not back-stamped: writing a level or a reason set onto rows whose computation never produced one would assert a fact that was never true. A recompute under the correction-current lifecycle is the governed way to populate them.
- The absolute `atr14` now participates in the artifact hash where it previously contributed a constant `NULL`. Any republication of an affected date is an ordinary correction under the version rule, not a silent change.

## Findings and dependencies

- Blocking `MD-B14` finding: **none**.
- Discharged: **`F-MD-B01-A008-001`** (P2, open since `MD-B01-A008`). The dependency manifest declares a horizon role for all 21 published windows from the three locked roles and fails closed on an undeclared or orphaned one; probe 05 proves the guard turns red when a role is removed. `MD-S056-R0019`, `R0020`, `R0021`, `R0022`, `R0024` and `R0129` are bound by `E-MD-B14-A001-001`.
- Opened: **`F-MD-B14-A001-001`** (P3, non-blocking).
- `MD-DEP-0004` discharged for `MD-B14` by the stage-entry normalization.
- No predecessor closure, Baseline Lock, prior evidence or failed proof artifact was rewritten.

## Integrity / closure controls

- `MD-B14` bound proof gate: **PASS — 147/147, 25 proof families, runtime pending 0**.
- `MD-B14` proof mutation self-test: **PASS — 11/11** in `BOUND_CLOSURE` with a green control.
- `MD-B14` closure condition gate: **PASS — 8/8 conditions met**, each independently falsifiable.
- Classification consistency: **PASS — `MD-B14` mixed-run debt 0, unexplained-reference debt 0**; pending members remain across the six unopened stages `MD-B15` to `MD-B20` and are downstream of this closure.
- Traceability applicability gate: **PASS**.
- Documentation integrity: **PASS — 992 physical / 992 role rows / 992 Document IDs / 992 current-verification rows**.
- Relationship integrity: **PASS — 181 work records / 329 relationships**, zero validity errors or completeness gaps.
- Relationship/document mutation self-test: **PASS**, controls and post-restore controls green.
- `CURRENT_STATE` deterministic generation: **PASS**, repeated generations byte-identical.
- Independent re-derivation of the binding scope against a pre-binding copy of the matrix: exactly **147 rows changed, all owned by `MD-B14`, zero foreign rows touched**. This is checked outside the binder rather than trusting the binder's own assertion, because `MD-B07-A002` unbound thirty predicates owned by other stages while every gate stayed green.

## Successor / exact resume

`MD-B14` is terminal **DONE / PASS**. `MD-B15` remains **NOT_STARTED** and is not opened by this
closure work unit.

Single exact resume point after this closure: begin **`MD-B15` stage-entry preflight**; rederive
current `MD-B15` applicability, ownership and classification from current authority — including its
37 mixed-classification members and 131 undecided reference rows — and issue the first valid
`MD-B15` Baseline Lock and Change Impact Declaration before any material `MD-B15` mutation. No
`MD-B14` predicate proof is inheritable by `MD-B15`.
