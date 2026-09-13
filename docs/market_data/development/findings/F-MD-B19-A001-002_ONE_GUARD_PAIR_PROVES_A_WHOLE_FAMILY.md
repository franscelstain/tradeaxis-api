# Finding — `F-MD-B19-A001-002`

- ID: `F-MD-B19-A001-002`
- Raised by: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Owner stage: **`MD-B18`** (closed `DONE` under `SC-MD-B18-A001-001`)
- Raised at: 2026-09-09T16:30:00+07:00
- Severity: `P1`
- Status: `OPEN — REMEDIATION IN PROGRESS under `MD-B18-A002``
- Class: `PROOF_DOES_NOT_ESTABLISH_THE_PREDICATE`
- Blocks: the `DONE` verdict of `MD-B18`; the same proof shape is being built in `MD-B19`
- Blocks strategy change: `NO`

## Statement

`MD-B18` closed at `121/121` with **eleven positive guards and eleven negative guards** — exactly one
pair per proof family. A family is not a predicate. `as_known_isolation` holds **26 required
predicates** and names one positive guard; `bound_inputs` holds 21 and names one.

A single executed guard cannot establish 26 distinct obligations. What the binding recorded is that
each predicate belongs to a family whose subject a guard exercises, not that the guard establishes
that predicate.

`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 states the rule this violates:

> A required row whose `rule_text` is not a self-contained predicate MUST NOT be treated as
> proof-complete merely because the referenced object/field/value exists.

and requires that `SATISFIED` be invalidated where evidence proves something weaker than the
normalized predicate.

## How it was found

Not by re-reading the proof. By trying to satisfy a **different** obligation and finding it absent.

`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 requires every context-dependent required row
to record `predicate_context=` and `normalized_predicate=`, and §8 makes that a precondition of
closure; `STAGE_EXECUTION_AND_REWORK_STANDARD.md` §7 repeats it for stage entry. Measured across the
package:

| Stage | Denominator | Carrying a normalized predicate | Gap |
|---|---|---|---|
| `MD-B01`, `B02`, `B04`–`B08`, `B13`–`B17`, `B20`, `B21` | — | complete | **0** |
| `MD-B09` | 140 | 102 | 38 |
| `MD-B10` | 1072 | 1065 | 7 |
| `MD-B11` | 202 | 73 | 129 |
| `MD-B12` | 75 | 74 | 1 |
| **`MD-B18`** | **121** | **36** | **85** |
| **`MD-B19`** | **743** | **65** | **678** |
| `MD-B22` | 27 | 23 | 4 |

`MD-B00` and `MD-B03` carry no denominator rows. `MD-B22` is `NOT_STARTED`, so its four are not yet a
closure defect. **`MD-B09`, `MD-B10`, `MD-B11` and `MD-B12` are `DONE`** and carry 38, 7, 129 and 1 —
the same §8 gap in four already-closed stages. They are reported here and are not remediated by this
record; each needs its own governed handling and none is owned by `MD-B18` or `MD-B19`.

The stages that are complete on this axis have closure gates that enforce it —
`MarketDataCoverageGateClosureGate` carries a `context_binding_and_normalized_predicate` condition.
`MarketDataReplayVerificationClosureGate`, written for `MD-B18` in the closing attempt, **did not
carry that condition**, so the stage closed with 85 of 121 rows unbound and eight green conditions.

Composing those 85 predicates from their governing parents is what exposed the deeper defect. Once a
row states its whole obligation rather than its fragment, the question "does the bound guard prove
this?" becomes answerable — and for most rows the answer is no.

## Measurement

Each of the 85 newly composed predicates was checked against the guard body `MD-B18` bound it to —
the body, not the method name:

| Verdict | Count |
|---|---|
| `SUPPORTED` — the guard establishes the predicate as composed | **27** |
| `PARTIAL` — the guard establishes a proper subset | **20** |
| `UNSUPPORTED` — the guard establishes a different obligation | **38** |

**58 of 85 are not fully established.** Per family:

| Family | Rows checked | Supported | Partial | Unsupported |
|---|---|---|---|---|
| `admissibility_boundary` | 14 | 12 | 0 | 2 |
| `as_known_isolation` | 18 | 1 | 5 | 12 |
| `bound_inputs` | 11 | 1 | 5 | 5 |
| `exact_publication` | 11 | 2 | 3 | 6 |
| `result_and_evidence` | 7 | 2 | 5 | 0 |
| `source_observation` | 5 | 2 | 0 | 3 |
| `corporate_action_and_indicator` | 4 | 1 | 0 | 3 |
| `determinism_and_operations` | 4 | 0 | 1 | 3 |
| `independent_oracle` | 4 | 2 | 0 | 2 |
| `mode_admission` | 4 | 2 | 1 | 1 |
| `temporal_identity` | 3 | 2 | 0 | 1 |

The 36 rows that already carried a predicate from earlier stages were not re-checked individually,
but they sit in the same families under the same eleven guard pairs, so the defect is not confined to
the 85 measured.

### Worked examples

- `MD-S050-R0019`–`R0026` are the **eight named anti-survivorship fixtures** — a listing active at T
  but inactive today, a symbol change, symbol reuse, a calendar fact corrected after T, an action
  verified later, a config change after T, an original-versus-corrected publication, a provider
  outage surviving dormancy filtering. All eight are bound to
  `AsKnownReplayBoundaryTest::test_identity_recorded_after_the_cutoff_is_invisible`, which executes
  none of them as fixtures.
- `MD-S003-R0015` requires long-chain Wilder ATR to match an **independent oracle**. Its guard,
  `ReplayAdmissibilityVerdictStorabilityTest::test_a_relabelled_self_generated_fixture_is_still_refused`,
  proves that a self-generated fixture is refused. That is a well-built guard for a different
  predicate: fixture independence is a precondition of an oracle comparison, not the comparison.
- `MD-S050-R0008`–`R0012` require the fixture manifest to bind dataset boundary, temporal mappings,
  calendar/status revisions, observation IDs/hashes, and event/factor revisions. The bound guard
  refuses exactly six inputs: publication, cutoff, fixture-manifest hash, config-snapshot hash,
  serialization version, executable build identity. None of the five named above is among them.
- `MD-S002-R0004` requires deterministic output **across supported runtime/locale/concurrency
  conditions**. The guard runs one replay once.

## Scope: `MD-B18` is the extreme case, not the only one

Counting distinct positive guards against denominator predicates for every stage that records a
guard in its rows:

| Stage | Predicates | Families | Distinct positive guards | Predicates per guard |
|---|---|---|---|---|
| `MD-B16` | 75 | 24 | 19 | 3.9 |
| `MD-B14` | 147 | 25 | 25 | 5.9 |
| `MD-B15` | 221 | 29 | 29 | 7.6 |
| `MD-B17` | 246 | 28 | 27 | 9.1 |
| **`MD-B18`** | **121** | **11** | **11** | **11.0** |

Every one of these stages binds roughly one guard per family. `MD-B18` is the worst of them — the
fewest guards in absolute terms for a comparable obligation count, and the largest families — but the
shape is shared, so this finding must not be read as saying only `MD-B18` over-claimed.

What the ratio does **not** settle is whether a given guard establishes its family's members. A high
ratio can be legitimate: a guard that parses a contract's field list and asserts every named field
really does establish each field predicate. That is why the remedy below is a reviewed per-predicate
basis rather than a ratio threshold — a threshold would fail honest guards and pass dishonest ones.
`MD-B18` was measured predicate by predicate; `MD-B14`–`MD-B17` were not, and this record makes no
claim about their outcome.

## Relation to `F-MD-B18-A001-001`

This is the same error one level up, and the earlier finding's remedy is what made it visible.

`F-MD-B18-A001-001` measured that families were assigned by keyword accident and rebuilt the map as
an explicit reviewed 121-row table. That fixed **which family a predicate belongs to**. It did not
ask whether the family's guard pair proves each member, and the closure manifest's phrase — "every
one of the 121 predicates was proven by a guard executed in this attempt" — reads as if it had.
Eleven guards were executed. 121 predicates were not each proven.

## Why the gates did not catch it

`MarketDataReplayVerificationProofGate` checks that every predicate maps to a family, that families
declare expected counts, and that a behavioural family is not re-pointed at a text scan. Every one of
those checks passes when one guard is assigned to a family of 26. Nothing measured **predicates per
guard**, so the ratio was never a reportable quantity.

## Required remediation

Owned by `MD-B18`, and it needs a new attempt — `MD-B18-A002` — because the existing closure is
issued and immutable.

1. Return the 58 measured rows to `NOT_ASSESSED`; re-check the 36 carried rows on the same basis.
2. Record `predicate_context=` and `normalized_predicate=` for all 121, per §3.
3. Write guards that establish the predicates that lack one, or correct proof ownership where the
   obligation belongs to another stage (several `exact_publication` rows are publication-pointer
   lifecycle obligations that `MD-B17` owns).
4. Require a **reviewed per-predicate proof basis** in the proof gate: one short statement per
   denominator row saying how its guard establishes that predicate. Already implemented in
   `MarketDataOperationsProofGate` (`predicates_without_a_reviewed_basis`, both branches probed);
   `MarketDataReplayVerificationProofGate` needs the same. A ratio threshold was considered and
   rejected — it would fail an honest contract-parsing guard and pass a dishonest one.
5. Re-issue evidence and a new closure manifest. `SC-MD-B18-A001-001` is not edited.

## Impact on `MD-B19`

`MD-B19` is building the same shape: 743 predicates across 37 families, three families proven so far
by one guard pair each. `artifact_run_summary` alone carries **52 predicates** against one positive
and one negative guard.

`MD-B19` must not continue assigning one guard pair per family. Its remaining work is re-scoped: a
family is a grouping for review, and each predicate inside it needs proof that establishes it.

## Remediation progress under `MD-B18-A002`

Opened 2026-09-09 with `CI-MD-B18-A002-001` and `MD-B18-A002-BL001` issued before any material
mutation, under blocking dependency `MD-DEP-0009`.

**Phase 1 — complete.** All 121 denominator rows now carry `predicate_context=` and
`normalized_predicate=`, and all 121 were returned to `NOT_ASSESSED` with their `MD-B18-A001`
evidence reference cleared. That last step was applied to all 121 rather than only to the 88
measured, because the 33 `SUPPORTED` rows were proven under a withdrawn closure and carrying their
`SATISFIED` forward would inherit a historical pass. The binder changed **exactly 121 lines, all
`MD-B18`, with zero untouched lines rewritten** — the measurement `MD-B18-A001`'s binder could not
make. Re-running it is refused rather than double-binding.

The full 121-row re-check completed here too: the 36 rows that already carried a predicate were
assessed on the same basis as the 85. Across all 121 — **33 `SUPPORTED`, 29 `PARTIAL`, 59
`UNSUPPORTED`; 88 of 121 not established.**

**Phase 2 — 26 of 121 established.** Two guards, each parsing its owner contract's own list and
asserting every member, so an item added to the strategy fails the guard rather than passing
unnoticed:

- `B18ReplayEvidencePreservationContractTest` — the ten `MD-S040` preserved items
  (`R0070`..`R0079`). 4/4 fail-closed probes caught, controls green either side.
- `B18ReplayBoundInputIdentityContractTest` — the nine `MD-S050` bound-input items
  (`R0007`..`R0015`) and the seven `MD-S019` antecedents (`R0066`..`R0072`). It asserts all 24
  identity paths **and** that a fixture binding different identities produces a different block, so
  a constant-filled block fails. 4/4 probes caught.

Two things were learned while writing them, both worth recording because they are the same shape as
the defect this finding is about:

- **Twice the implementation was right and the expectation was wrong.** `publication_id` is exported
  in the publication audit context rather than at the top level, and the coverage reason code is
  derived from the normalized gate state because no such column is persisted. Both were corrected in
  the guard, not in the code.
- **One of these new guards had the defect it was written to prevent.** A contract item mapped to an
  empty path list satisfied the key-matching check and asserted nothing. A probe caught it; the
  guard now carries a population assertion naming how many paths it inspects.

`MarketDataReplayVerificationProofBasis` accumulates the established predicates and
`MarketDataReplayVerificationClosureGate` gained condition 7, which reports the shortfall by name.
The gate currently reads `0/121 satisfied` and `26/121 with a reviewed basis`, which is the honest
state — nothing is bound and no closure is claimed.

**Phase 2 continued — 55 of 121 established.** The 29 predicates whose `MD-B18-A001` guard does
establish them were re-verified rather than carried over: the nine guard pairs were re-executed in
this attempt (149 tests, 807 assertions green) and re-probed, 11/11 fail-closed probes caught with
controls green either side. Thirteen of the 29 are citation prohibitions carried by the declared
corpus guard, which legitimately covers many predicates because it scans every active surface for
the claims they forbid — that is a guard whose breadth matches its subject, not a family standing
in for its members.

**Outstanding: 66**, and the cheap tranche is now exhausted — every remaining row needs a new or
extended guard. They split 23 `PARTIAL` and 43 `UNSUPPORTED`. The largest clusters are the eight
named anti-survivorship fixtures, the identical-input reproducibility conditional, the `MD-S003`
scenario families still open, and the before-seal validation rows.

**Correction to this record's own remediation note.** An earlier reading of this finding suggested
several `exact_publication` and `MD-S003` predicates be reassigned to `MD-B17` under §7 as
publication-pointer lifecycle obligations. That was wrong: those rows sit under `MD-S003`'s
`## Required scenario families` heading, so the obligation is that the **replay suite executes a
scenario proving it**, which `MD-B18` owns. Acting on it would have moved obligations into a closed
stage on a misread parent heading — the same error class this finding is about. Six rows remain
genuine §7 questions (`MD-S004-R0007`, `MD-S065-R0003`, `MD-S082-R0216`, `R0217`, `R0224`,
`R0225`), none of them a clean wholesale move.

## What was already changed, and under which attempt

These were made under `MD-B19-A001`, the attempt that raised this finding. All three either expose a
defect or refuse a claim; none of them makes anything pass:

- `MarketDataReplayVerificationClosureGate` gained the
  `context_binding_and_normalized_predicate` condition its peer gates carry. It now returns `FAIL`
  with exactly that one condition unmet and 85 named offenders. This is the change that surfaced the
  finding.
- `MarketDataOperationsProofGate` gained the reviewed per-predicate basis requirement. It reports
  `predicates_without_a_reviewed_basis: 743`. Both branches were probed — a temporary two-row basis
  map moved the count to 2 and back, and a basis naming an `MD-B17` row raised
  `PROOF_BASIS_FOR_FOREIGN_ROW` — so the check is not permanently red.
- The `MD-B19` register and `CURRENT_STATE` entries that called three families "proven" now say
  "guarded", with the verdicts withdrawn.

`MarketDataReplayVerificationProofGate` still needs the same basis requirement and still returns
`PASS --bound`, because it measures map structure rather than proof sufficiency. That change belongs
to `MD-B18-A002` and was deliberately **not** made here: `MD-B18` has no open attempt, and
`CHANGE_IMPACT_DECLARATION_STANDARD.md` §3 requires the declaration to exist early enough to direct
the work rather than describe it afterwards.

## Not done

No predicate state was changed. Nothing is remediated by this record. No matrix row was edited, no coverage state changed, and
`MD-B18` still reads `DONE` in the stage register pending the orchestration decision recorded
alongside this finding.

## Appendix — the 85-row measurement

Each row shows the predicate produced by composing the fragment with its governing parent, the family whose
guard pair `MD-B18` bound it to, and whether that guard establishes the predicate. `predicate_context` is the
governing parent rule ID, or `SECTION:` where the governing construct is the heading and carries no rule row.


### `admissibility_boundary`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S002-R0009` | **SUPPORTED** | `MD-S002-R0002` | A release candidate requires: `BLOCKED` treated as missing proof, never converted to pass. | BLOCKED-never-a-pass is exactly a citation prohibition the corpus guard scans for |
| `MD-S002-R0010` | **SUPPORTED** | `SELF_CONTAINED` | Pass rates or row-count similarity cannot compensate for a semantic mismatch in a required invariant. | pass-rate-cannot-compensate is a citation prohibition in scope of the corpus guard |
| `MD-S002-R0016` | **SUPPORTED** | `SELF_CONTAINED` | Consequently a metric set may be cited as evidence that **a study ran over an identified data version**, never as evidence about **market-data quality… | citation boundary for a metric set |
| `MD-S003-R0031` | **SUPPORTED** | `SELF_CONTAINED` | Consequently a clean historical quality replay may be cited as evidence that **recorded decisions were stable and rule-bound**, never as evidence that… | citation boundary for a clean quality replay |
| `MD-S004-R0007` | **UNSUPPORTED** | `SELF_CONTAINED` | Signal features use the declared coherent analytical product. Simulated execution must separately choose realistic executable prices/times from allowe… | input-construction obligation (analytical product, execution price choice), not a citation rule; the corpus guard never evaluates feature construction |
| `MD-S004-R0011` | **SUPPORTED** | `MD-S004-R0010` | Capability boundary (LOCKED) - what a point-in-time input set cannot prove: **That the as-known state was complete at that cutoff.** The set contains … | capability-boundary statement about what may be claimed |
| `MD-S050-R0038` | **SUPPORTED** | `MD-S050-R0037` | **Publication-replay results carry no information about anti-survivorship or future-state leakage.** They compare an artifact against inputs frozen wi… | citation boundary for publication-replay results |
| `MD-S050-R0039` | **SUPPORTED** | `MD-S050-R0037` | No volume of publication-replay `PASS` results substitutes for a single as-known fixture. Accumulating them raises confidence in determinism only. | citation boundary: volume of PASS does not substitute |
| `MD-S050-R0040` | **UNSUPPORTED** | `MD-S050-R0037` | The eight anti-survivorship fixtures required below are as-known fixtures. Until as-known replay exists, their absence is not a gap in coverage — it i… | classifies the eight fixtures as as-known and governs coverage accounting; the corpus guard does not evaluate fixture classification or coverage |
| `MD-S050-R0045` | **SUPPORTED** | `MD-S050-R0044` | What replay cannot prove: **That the values are correct.** Replay compares an output against itself under fixed inputs. A publication computed from a … | capability boundary: replay cannot prove value correctness |
| `MD-S050-R0050` | **SUPPORTED** | `SECTION:Admissibility of a PASS (LOCKED)` | A replay `PASS` may be cited as evidence of **reproducibility and determinism**. It may never be cited as evidence of **data correctness, event comple… | explicit admissibility rule for a replay PASS |
| `MD-S050-R0051` | **SUPPORTED** | `SECTION:Admissibility of a PASS (LOCKED)` | A replay `PASS` may not close a data-quality finding, release a quarantine, dismiss a corporate-action candidate, or satisfy a continuity check. | explicit prohibition on what a PASS may close |
| `MD-S050-R0052` | **SUPPORTED** | `SECTION:Admissibility of a PASS (LOCKED)` | Where an audit claim requires correctness, the admissible evidence is independent — verified event terms, source reconciliation, or exchange-published… | names the admissible alternative evidence; the corpus guard forbids the substitution |
| `MD-S050-R0053` | **SUPPORTED** | `SECTION:Admissibility of a PASS (LOCKED)` | Capability boundary (LOCKED) / Admissibility of a PASS (LOCKED): `BLOCKED` is not a weaker `PASS`. It states that the comparison did not execute. | BLOCKED is not a weaker PASS - a citation rule |

### `as_known_isolation`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S002-R0005` | **PARTIAL** | `MD-S002-R0002` | A release candidate requires: all anti-survivorship and as-known isolation fixtures passing; | requires ALL anti-survivorship and as-known fixtures passing; the guard executes identity-cutoff invisibility only |
| `MD-S003-R0021` | **SUPPORTED** | `SECTION:As-known isolation` | Required scenario families / As-known isolation: later master, event, status, calendar, config, formula, and factor revisions are invisible before the… | later revisions invisible before their recorded time is what the cutoff-invisibility guard asserts |
| `MD-S003-R0022` | **UNSUPPORTED** | `SECTION:As-known isolation` | Required scenario families / As-known isolation: a declared later cutoff can expose them without rewriting earlier replay evidence. | requires a declared later cutoff to expose facts without rewriting earlier evidence; no guard executes a second cutoff |
| `MD-S004-R0002` | **PARTIAL** | `SELF_CONTAINED` | Each decision timestamp declares a `knowledge_cutoff`. Inputs contain only observations and identity, calendar, status, event, factor, config, and for… | cutoff-bounded inputs asserted for identity only; calendar/status/event/factor/config bounding not executed |
| `MD-S004-R0003` | **PARTIAL** | `SELF_CONTAINED` | Today's universe, symbol, sector, action verification, or current publication may not be backfilled into an earlier decision. | no-backfill asserted for identity; universe/sector/action-verification/current-publication backfill not executed |
| `MD-S004-R0005` | **UNSUPPORTED** | `SELF_CONTAINED` | Inactive/delisted securities remain present when they were in the temporal universe. Symbol changes/reuse use listing IDs. Late corrections/actions pr… | inactive/delisted presence and listing-ID symbol resolution are temporal-identity obligations; this guard asserts cutoff invisibility |
| `MD-S004-R0008` | **UNSUPPORTED** | `SELF_CONTAINED` | At minimum prove inactive-now/active-then membership, symbol transition/reuse, late action verification, late config/calendar/status correction, unava… | names seven acceptance fixtures to prove; the guard executes none of them as fixtures |
| `MD-S050-R0005` | **UNSUPPORTED** | `SECTION:As-known replay` | Two replay modes / As-known replay: It may differ from a historical publication when the selected cutoff, approved as-known configuration, or declared… | as-known may differ from a historical publication and creates new artifacts without impersonating the original; not asserted |
| `MD-S050-R0017` | **PARTIAL** | `SELF_CONTAINED` | Replay must not use today's `is_active`, current symbol, current sector, current suspension/status, latest calendar correction, later corporate-action… | prohibits using the current is_active/symbol/sector/status/calendar/action/factor/config; guard covers identity recency only |
| `MD-S050-R0019` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: a listing active at historical T but inactive today; | required fixture: listing active at T but inactive today - no such fixture is executed by this guard |
| `MD-S050-R0020` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: a symbol change and provider-symbol mapping transition; | required fixture: symbol change and provider-symbol mapping transition |
| `MD-S050-R0021` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: symbol text reused by another listing; | required fixture: symbol text reused by another listing |
| `MD-S050-R0022` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: a calendar/status fact corrected after T; | required fixture: calendar/status fact corrected after T |
| `MD-S050-R0023` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: a corporate action learned or verified later; | required fixture: corporate action learned or verified later |
| `MD-S050-R0024` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: a configuration/formula change after T; | required fixture: configuration/formula change after T |
| `MD-S050-R0025` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: an original and corrected immutable publication; and | required fixture: original and corrected immutable publication |
| `MD-S050-R0026` | **UNSUPPORTED** | `MD-S050-R0018` | Required fixtures include: a provider outage that cannot disappear through dormancy/current-universe filtering. | required fixture: provider outage surviving dormancy/current-universe filtering |
| `MD-S050-R0028` | **PARTIAL** | `SELF_CONTAINED` | As-known replay performs bitemporal resolution with `effective_at <= target context` and `recorded_at <= knowledge_cutoff`; ties and corrections use v… | bitemporal resolution asserted; tie-breaking and correction ordering not executed |

### `bound_inputs`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S004-R0004` | **PARTIAL** | `SELF_CONTAINED` | Every row/export binds listing identity, requested/effective trade date, knowledge cutoff, as-known replay ID/publication-like artifact ID, read-model… | guard enforces publication/cutoff/fixture-hash/config-hash/serialization/build; listing identity, effective date and read-model binding not enforced |
| `MD-S050-R0007` | **PARTIAL** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: replay mode, fixture ID/version, requested/effective date, and knowledge cutoff where applicable; | replay mode and knowledge cutoff enforced; fixture ID/version and requested/effective date not enforced |
| `MD-S050-R0008` | **UNSUPPORTED** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: intentional dataset boundary and temporal universe/listing/symbol/provider mappings; | dataset boundary and temporal universe/listing/symbol/provider mappings are not among the six enforced inputs |
| `MD-S050-R0009` | **UNSUPPORTED** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: Regular-Market calendar/session and trading-status revisions; | calendar/session and trading-status revisions are not among the six enforced inputs |
| `MD-S050-R0010` | **UNSUPPORTED** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: immutable source observation IDs/hashes and adapter/schema/normalization versions; | observation IDs/hashes and adapter/schema/normalization versions are not among the six enforced inputs |
| `MD-S050-R0011` | **UNSUPPORTED** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: canonical `RAW` publication/input set; | canonical RAW publication/input set is not among the six enforced inputs |
| `MD-S050-R0012` | **UNSUPPORTED** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: corporate-action event revisions, verification states, factor-set revisions, and contamination decisions; | event revisions, verification states, factor-set revisions and contamination decisions are not enforced |
| `MD-S050-R0013` | **SUPPORTED** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: full configuration snapshot ID/hash; | config_snapshot_hash absence is refused, which is this predicate exactly |
| `MD-S050-R0014` | **PARTIAL** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: formula, indicator registry, reason registry, price-product, coverage, eligibility, read-model, hash/serializ… | serialization version enforced; formula, indicator/reason registry, price-product, coverage, eligibility, read-model versions not enforced |
| `MD-S050-R0015` | **PARTIAL** | `MD-S050-R0006` | Every fixture/manifest binds at minimum: expected publication/pointer/seal state and deterministic output/hash assertions. | publication identity enforced for exact mode; pointer/seal state and deterministic output/hash assertions not enforced here |
| `MD-S050-R0016` | **PARTIAL** | `SELF_CONTAINED` | Missing input is `BLOCKED`, not permission to query current/latest state. | refusal is proven; that the refused state is recorded as BLOCKED rather than a failure is not asserted |

### `corporate_action_and_indicator`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S003-R0012` | **UNSUPPORTED** | `SECTION:Corporate actions and indicators` | Required scenario families / Corporate actions and indicators: synthetic price-break candidates never activate factors; | synthetic price-break candidates never activating factors is a corporate-action rule; the guard detects factor-set identity drift |
| `MD-S003-R0013` | **UNSUPPORTED** | `SECTION:Corporate actions and indicators` | Required scenario families / Corporate actions and indicators: verified event/factor revision produces coherent structural OHLC/volume; | coherent structural OHLC/volume from a verified revision is not asserted by a drift-detection guard |
| `MD-S003-R0014` | **SUPPORTED** | `SECTION:Corporate actions and indicators` | Required scenario families / Corporate actions and indicators: provider adjusted-close fallback is impossible; | the negative guard proves provider adjusted close never reaches the canonical row |
| `MD-S003-R0016` | **UNSUPPORTED** | `SECTION:Corporate actions and indicators` | Required scenario families / Corporate actions and indicators: actual traded value and close-volume proxy never share meaning or field identity. | traded value versus close-volume proxy field identity is not asserted |

### `determinism_and_operations`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S002-R0004` | **UNSUPPORTED** | `MD-S002-R0002` | A release candidate requires: deterministic output across supported runtime/locale/concurrency conditions; | deterministic output across runtime/locale/concurrency requires varying those conditions; the guard runs one replay once |
| `MD-S002-R0006` | **PARTIAL** | `MD-S002-R0002` | A release candidate requires: all degraded/negative fixtures producing their expected held/failed/unavailable states without silent repair or denomina… | the negative guard proves one degraded case stops with an error; held/failed/unavailable states across the degraded corpus are not executed |
| `MD-S003-R0025` | **UNSUPPORTED** | `SELF_CONTAINED` | All required scenario families pass on MariaDB production semantics and the supported test mirror. Any missing family remains an open proof gap; histo… | requires all scenario families passing on MariaDB and the mirror; a single service test does not establish suite-wide family coverage |
| `MD-S050-R0056` | **UNSUPPORTED** | `SELF_CONTAINED` | Production relock requires executed publication and as-known fixtures, including all anti-survivorship cases above, on the actual production path. | production relock on the actual production path with all anti-survivorship cases is not executed |

### `exact_publication`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S002-R0003` | **PARTIAL** | `MD-S002-R0002` | A release candidate requires: zero unexplained value, null-reason, lineage, config, factor, hash, seal, or publication mismatches in exact publication… | the guard resolves a publication and reason-codes an unsealed one; zero unexplained mismatches across value/null-reason/lineage/config/factor/hash/seal is broader |
| `MD-S002-R0008` | **UNSUPPORTED** | `MD-S002-R0002` | A release candidate requires: corrected publications preserving their predecessors and switching atomically; and | predecessor preservation and atomic switching is a publication-lifecycle obligation not executed by this guard |
| `MD-S003-R0002` | **SUPPORTED** | `SECTION:Exact publication verification` | Required scenario families / Exact publication verification: resolve an explicit immutable publication, not latest/current; | resolving an explicit immutable publication without current-pointer fallback is the subject of the guard |
| `MD-S003-R0003` | **PARTIAL** | `SECTION:Exact publication verification` | Required scenario families / Exact publication verification: verify frozen observations, temporal revisions, config, factors, formulas, artifacts, has… | the guard verifies publication resolution and seal state; frozen observations, factors, formulas and artifact hashes are not all compared here |
| `MD-S003-R0004` | **UNSUPPORTED** | `SECTION:Exact publication verification` | Required scenario families / Exact publication verification: prove an unchanged rerun is byte-identical and does not create a fake correction. | byte-identical unchanged rerun producing no fake correction is not executed |
| `MD-S003-R0017` | **UNSUPPORTED** | `SECTION:Correction and read path` | Required scenario families / Correction and read path: prior immutable publication remains auditable; | prior immutable publication remaining auditable is a correction/read-path obligation |
| `MD-S003-R0018` | **UNSUPPORTED** | `SECTION:Correction and read path` | Required scenario families / Correction and read path: a distinct corrected candidate becomes active only after complete validation and reseal; | corrected candidate activation after validation and reseal is not executed |
| `MD-S003-R0019` | **UNSUPPORTED** | `SECTION:Correction and read path` | Required scenario families / Correction and read path: concurrent consumers read exactly one publication; | concurrent consumers reading exactly one publication is a pointer-lifecycle obligation |
| `MD-S003-R0020` | **UNSUPPORTED** | `SECTION:Correction and read path` | Required scenario families / Correction and read path: explicit fallback retains prior effective date and stale/degraded state. | explicit fallback retaining prior effective date and stale/degraded state is not executed |
| `MD-S050-R0002` | **PARTIAL** | `SECTION:Publication replay` | Two replay modes / Publication replay: Reproduces or verifies one historical immutable publication using exactly the observations, temporal master rev… | publication replay reproduces one historical publication from frozen inputs; the guard resolves it but does not compare the full frozen input set |
| `MD-S050-R0027` | **SUPPORTED** | `SELF_CONTAINED` | Publication replay starts from explicit publication identity, never latest/current. Current-read verification is a separate assertion that the pointer… | starting from explicit publication identity and never latest/current is exactly what the guard asserts |

### `independent_oracle`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S002-R0007` | **UNSUPPORTED** | `MD-S002-R0002` | A release candidate requires: long-chain ATR and corporate-action results matching independent oracles; | long-chain ATR and corporate-action results matching independent oracles requires an executed oracle comparison |
| `MD-S003-R0015` | **UNSUPPORTED** | `SECTION:Corporate actions and indicators` | Required scenario families / Corporate actions and indicators: long-chain Wilder ATR matches an independent oracle, including a correction whose impac… | long-chain Wilder ATR matching an independent oracle is not executed; the guard proves fixture independence |
| `MD-S003-R0024` | **SUPPORTED** | `SELF_CONTAINED` | Fixtures must be independently reviewed semantic oracles. Copying current implementation output into “expected” files without independent derivation i… | fixtures must be independent oracles and self-generated expectations are refused - exactly the subject of the guard |
| `MD-S050-R0033` | **SUPPORTED** | `SELF_CONTAINED` | “Command exited successfully” or matching row counts alone is not replay proof. | exit status or row counts alone is not replay proof; the inadmissible verdict is never counted as a pass |

### `mode_admission`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S050-R0001` | **SUPPORTED** | `SELF_CONTAINED` | Replay mode is mandatory and explicit. | only the two locked modes accepted is the subject of the guard |
| `MD-S050-R0035` | **PARTIAL** | `MD-S050-R0034` | Every replay result records its mode as a first-class field. A result set in which publication and as-known outcomes are indistinguishable does not sa… | mode is validated on admission; that every persisted result records mode as a first-class field is not asserted by this guard |
| `MD-S050-R0036` | **SUPPORTED** | `MD-S050-R0034` | A result carrying no mode is not a publication-replay result by default. It is **unclassified**, and an unclassified result may not be cited as either… | an unmoded result is refused rather than defaulted, which the mode guard establishes |
| `MD-S050-R0041` | **UNSUPPORTED** | `MD-S050-R0037` | A conformance or activation claim that cites replay evidence names which mode produced it. Citing an unmoded or publication-only corpus in support of … | a citation rule about naming the mode in a conformance claim; belongs to the corpus admissibility guard, not mode admission |

### `result_and_evidence`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S003-R0023` | **PARTIAL** | `SELF_CONTAINED` | Record replay mode, fixture/manifest hash, requested/effective dates, knowledge cutoff, all frozen revision/snapshot IDs, expected/actual readiness an… | the export writes result and reason-code summary; the full frozen revision/snapshot ID set is not asserted present |
| `MD-S036-R0007` | **PARTIAL** | `SECTION:Runtime ownership` | Evidence and replay must record and compare request mode, import status, promote status, source mode, pointer switch status, and publication state. | request mode and publication context are exported; import/promote/source-mode/pointer-switch comparison is not asserted |
| `MD-S036-R0031` | **PARTIAL** | `SELF_CONTAINED` | Evidence export must show whether a run is import-only or promoted without requiring direct DB inspection. Replay must compare expected vs actual requ… | export without DB inspection is shown; expected-versus-actual comparison of all named statuses is not |
| `MD-S050-R0029` | **PARTIAL** | `SECTION:Result and evidence` | Result and evidence: `PASS`: all expected values, null reasons, states, lineages, content hashes, manifest, and seal assertions match. | a PASS is produced on match; that PASS requires ALL of values, null reasons, states, lineages, hashes, manifest and seal to match is not exhaustively asserted |
| `MD-S050-R0030` | **SUPPORTED** | `SECTION:Result and evidence` | Result and evidence: `FAIL`: comparison executed and diverged. | the divergence guard executes a comparison that diverges and reports FAIL |
| `MD-S050-R0031` | **SUPPORTED** | `SECTION:Result and evidence` | Result and evidence: `BLOCKED`: required fixture/runtime/input proof was unavailable. | missing expected proof is reported rather than ignored, which is the BLOCKED semantics |
| `MD-S050-R0032` | **PARTIAL** | `SELF_CONTAINED` | Evidence preserves fixture/manifest hashes, actual and expected contexts, mismatch paths, reason distributions, publication/pointer context, executabl… | hashes and contexts are preserved; mismatch paths and reason distributions are not all asserted |

### `source_observation`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S003-R0005` | **SUPPORTED** | `SECTION:Degraded acquisition and expectation` | Required scenario families / Degraded acquisition and expectation: provider outage remains missing delivery and cannot shrink the denominator; | the negative guard keeps a zero-row provider outage in the manifest, so the denominator cannot shrink |
| `MD-S003-R0006` | **UNSUPPORTED** | `SECTION:Degraded acquisition and expectation` | Required scenario families / Degraded acquisition and expectation: unknown expectation does not become holiday/dormancy; | unknown expectation not becoming holiday/dormancy is a calendar-expectation rule not executed here |
| `MD-S003-R0007` | **UNSUPPORTED** | `SECTION:Degraded acquisition and expectation` | Required scenario families / Degraded acquisition and expectation: stale/schema-invalid/wrong-date/zero-price observations quarantine or hold; | quarantine/hold for stale/schema-invalid/wrong-date/zero-price observations is not executed |
| `MD-S003-R0008` | **UNSUPPORTED** | `SECTION:Degraded acquisition and expectation` | Required scenario families / Degraded acquisition and expectation: no prior-date result masquerades as requested-date fresh data. | no prior-date result masquerading as requested-date fresh data is not executed |
| `MD-S050-R0046` | **SUPPORTED** | `MD-S050-R0044` | What replay cannot prove: **That the source observation was faithful.** Provider error inside an immutable observation is frozen by the same mechanism… | that replay cannot prove source faithfulness is a capability boundary the observation guard framing establishes |

### `temporal_identity`

| Rule | Verdict | Context | Normalized predicate | Basis for the verdict |
|---|---|---|---|---|
| `MD-S003-R0009` | **SUPPORTED** | `SECTION:Temporal identity and status` | Required scenario families / Temporal identity and status: inactive-now/active-then listing remains in the historical universe; | inactive-now/active-then listing remaining in the historical universe is asserted |
| `MD-S003-R0010` | **SUPPORTED** | `SECTION:Temporal identity and status` | Required scenario families / Temporal identity and status: symbol change and symbol reuse resolve through stable listing identity; | symbol change and reuse resolving through stable listing identity is asserted |
| `MD-S003-R0011` | **UNSUPPORTED** | `SECTION:Temporal identity and status` | Required scenario families / Temporal identity and status: calendar/session/status revisions respect effective and knowledge time. | calendar/session/status revisions respecting effective and knowledge time is not asserted by either identity guard |
