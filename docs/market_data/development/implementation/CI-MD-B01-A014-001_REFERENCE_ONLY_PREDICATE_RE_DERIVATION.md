# MD Change Impact Declaration — CI-MD-B01-A014-001

- ID: `CI-MD-B01-A014-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A014` / `MD-B01-A014-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, after the A014 baseline lock and before any A014 traceability, test, or tooling mutation.

## Why this attempt is material, and why it is not the resume point A013 predicted

`CI-MD-B01-A013-001` closed with "the next attempt is not locally openable until an authorised strategy-change process resolves `F-MD-B01-A003-001`". That statement rests on a premise that this attempt found to be false: that the `MD-B01` denominator of 143 is `FINAL`, so that `MD-S020-R0067` is the only unproven row.

The premise fails. `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §2 lists the classes that may be classified reference/non-requirement: section headings, list-introduction lines, labels/titles, descriptive context, examples, introductory prose, and bare fragments whose obligation exists only through a parent. Grammatical mood is not among them, and §1 states that the unit of proof is a semantic predicate.

The current matrix classifies by mood. Measured at this baseline across `MD-B01`'s 473 active rows (143 `REQUIRED`, 330 `REFERENCE_ONLY`), **17 enumerated lists carry mixed classification**, holding **72 `REFERENCE_ONLY` members whose siblings in the same list are `REQUIRED`**. In every one of the 17, the discriminator is whether the line contains a deontic modal:

- `Domain_Boundary_Invariants_LOCKED.md` "Boundary invariants", 14 numbered items — items 2, 11, 12, 14 are `REQUIRED`; items 1, 3–10, 13 are `REFERENCE_ONLY`. Item 6, "Eligibility is not ranking, selection, tradability approval, or alpha approval", carries no proof obligation, while item 2, "Market-data facts may be inputs to watchlist policy, never outputs of it", does.
- `Terminology_and_Scope.md` "Locked interpretation rules", 19 numbered items — 18 are `REQUIRED`; only item 2, "Promote owns indicators, eligibility, hash, seal, and finalize", is `REFERENCE_ONLY`. It is the one item with no `must never`.
- `Terminology_and_Scope.md` "`decision-grade` (LOCKED)", 4 numbered conditions — only condition 3 is `REQUIRED`. Conditions 1 (as-known, no later revision leaking backward), 2 (single declared basis), and 4 (timely enough to be usable) carry no obligation, though `MD-S056-R0004` states that the word "must not stay undefined" and the platform target is built on all four.
- `MARKET_DATA_PLATFORM_EOD_BASELINE.md` "Anti-assumption rules", 19 forbidden claims — `R0127`–`R0141` are `REQUIRED`, `R0142`–`R0145` are not. The cut is positional rather than semantic: all nineteen are entries in one list of claims the domain may no longer make, and the last four differ from the first fifteen only in where the list was truncated. The forbidden claims are not restated here; the conflation guard correctly rejects an active document that repeats one.

Three of the five homogeneous-list splits that `F-MD-B01-A001-001` itself cited as evidence are **still present unchanged**: `MD-S001-R0099` (one of ten ownership bullets required), `MD-S056-R0053` (one of eight target-output bullets required), and `MD-S001-R0074` (one of four provider-limitation bullets required). `MD-B01-A004` promoted 817 children **of introducers it demoted**; these three introducers were already `REFERENCE_ONLY`, so their children were never in the promotion set. The A004 statement that non-predicate required rows reached 0 remains true — it measured the demotion direction only. The finding's other stated consequence, that the classification "excludes rules that both can and should be" required, was never measured and is not remediated.

A denominator that omits these rows cannot be `FINAL`, and `142/143` overstates coverage.

## Affected strategy rules

No strategy rule text, fingerprint, source line, or owner is touched. The affected **matrix rows** are `MD-B01`'s 330 active `REFERENCE_ONLY` rows, of which the adjudication set is:

- the 72 `REFERENCE_ONLY` members of the 17 mixed-classification enumerated runs;
- standalone `REFERENCE_ONLY` rows that are self-contained predicates, including `MD-S020-R0016`, `R0071`, `R0073`, `R0080`, `R0090`–`R0092`, `R0102`–`R0104`, `R0140`, `R0160`, `R0162`–`R0169`, `R0172`, and `MD-S056-R0002`, `R0003`, `R0029`, `R0036`, `R0066`, `R0097`, `R0102`, `R0103`, `R0111`.

`MD-S020-R0067` stays `NOT_ASSESSED` and blocked by `F-MD-B01-A003-001`. No row currently `SATISFIED` is demoted.

## Planned proof method

1. Adjudicate every one of the 330 rows against §2 by structural class, never by grammatical mood, and record the basis on the row itself so the decision is auditable rather than asserted.
2. For a promoted child fragment, bind `predicate_context` to its governing parent and compose the `normalized_predicate` per §3, exactly as `MD-B01-A012` did for 84 fragments.
3. Re-derive `primary_stage` from proof ownership per §4 for every promoted row; a row whose evidence belongs to a later stage moves there with `MD-B01` retained as supporting stage.
4. Set every promoted row to `NOT_ASSESSED`. Promotion is a statement that proof is owed, never that proof exists.
5. Add a governance gate that fails closed on mixed-classification runs and on unadjudicated `REFERENCE_ONLY` rows, so this defect class becomes machine-detectable for all 18 stages instead of depending on a manual scan.
6. Verify by recomputation that `rule_text`, `rule_fingerprint_sha1`, `strategy_owner`, and `source_line` are byte-identical on all rows after the mutation.

## Affected areas

| Area | Impact |
|---|---|
| Strategy | None; immutable authority remains byte-identical, verified by fingerprint recomputation across all rows. |
| Schema / configuration / runtime / provider behavior | No mutation planned. |
| Backfill / replay / operations | No execution and no behavioral mutation planned. |
| Tests / gates | Material: a new classification gate plus its fail-closed self-test; the hardcoded `143` drift constants in `MarketDataScopeBoundaryCompletionGate` and `MarketDataTraceabilityApplicabilityGate` must move to the corrected denominator or they will report `PASS` against a number this attempt proves wrong. |
| Traceability | Material: `MD-B01`'s denominator increases and verified coverage falls. The 142 `SATISFIED` rows are unaffected; each holds its own evidence. |
| Evidence | Additive A014 evidence. A012 and A013 evidence remain unedited; their figures stay true as issued under the invariant then in force. |
| Raw artifacts / storage | None. This is authority interpretation and traceability classification under §5 of the runtime-artifact standard; no runtime proof is required or claimed. |

## Compatibility and residue risk

The dominant risk is repeating the defect while correcting it. The scan that first surfaced this used a deontic-modal regex, which would have missed `MD-S056-R0006` ("every input resolves from facts recorded and effective by `T`") precisely because that line states its obligation without a modal — the same blindness being corrected. The adjudication must therefore be structural, and the gate must not encode mood.

Second risk: over-promotion. A glossary entry that defines a term without constraining implementation is genuinely reference context, and promoting it manufactures an unprovable obligation — which is the defect `F-MD-B01-A001-001` was raised for, in the opposite direction.

Third risk: a stale gate. Both existing gates assert `denominator === 143`. After this correction they must fail, then be updated to the derived value; a gate that keeps passing while the number it guards has been proven wrong is the "cannot fail" shape already recorded three times in this codebase.

Harmful residue includes any promoted row left without predicate context, any row promoted into a stage that cannot evidence it, a stale `143` constant, an unadjudicated `REFERENCE_ONLY` row, or a `SATISFIED` state carried forward without its own evidence.

## Dependencies and relationships

- Successor to `MD-B01-A013`; predecessor baseline `MD-B01-A013-BL001`.
- Extends `F-MD-B01-A001-001` with the unmeasured half of the same defect. No parallel finding is created; that finding already owns this subject.
- `MD-DEP-0004` remains `OPEN_NON_BLOCKING` and gains the classification obligation at stage entry alongside the ownership obligation.
- `F-MD-B01-A003-001` remains `OPEN`. This attempt corrects its measurement, which was taken on the bare word `eligible` rather than on the alias identifier. `eligible` is the compatibility alias for `data_usable` and carries only that upstream data-usability meaning; it never means permitted to trade.

## Strategy semantic change

`NO`.

## Executed impact and result

- Strategy, schema, configuration, provider behaviour, backfill/replay behaviour, operator behaviour, and runtime code changed: `NO`. `rule_text`, `rule_fingerprint_sha1`, `strategy_owner`, and `source_line` are byte-identical on all 6490 rows, verified by recomputing sha1 across the whole matrix with 0 mismatches. CSV round-trip fidelity was proven byte-identical before the file was written, so unchanged rows are unchanged bytes.
- Traceability effect: **72 rows promoted** from `REFERENCE_ONLY` to `REQUIRED` — 59 with a governing parent bound and a composed normalized predicate, 13 self-contained. 70 `MANDATORY`, 2 `CONDITIONAL_APPLICABLE`. 8 moved to their proof-owning stages under §4 with `MD-B01` retained as supporting stage: `MD-S001-R0060`→`MD-B15`, `R0063`→`MD-B02`, `R0064`→`MD-B09`, `MD-S020-R0009`→`MD-B12`, `R0010`→`MD-B07`, `R0011`→`MD-B05`, `R0012`→`MD-B15`, `R0013`→`MD-B14`. Zero structural exclusions were promoted; zero `SATISFIED` rows were demoted.
- **Denominator 143 → 207. Verified coverage 99.30% → 69.57%.** The correction increases the obligation, as at A004, where the required set grew 43%.
- Rules proven, not merely promoted: `MD-S020-R0068` and `MD-S020-R0071` reached `SATISFIED` with evidence `E-MD-B01-A014-001`. `MD-S020-R0071` is the one rule in the alias cluster that enumerates the surfaces the alias may not reach; it had carried no proof obligation at all while its weaker siblings did. Coverage is `144/207`, and 63 rows remain `NOT_ASSESSED`.
- Tests/gates changed: one classification gate and its 8-test/27-assertion fail-closed self-test added; one 5-test/176-assertion alias-cluster proof suite added; the applicability gate extended to accept A014-normalized rows while recomputing their predicate context from the matrix rather than reading it from the note under test; both denominator drift locks moved from 143 to 207.
- Test execution: 132 tests / 1126 assertions across 11 suites, zero failures and zero errors.
- Negative proof: seven in-suite mutations and five on-disk mutations, each asserted to have landed before the guard was judged. One on-disk mutation initially reported "guard did not react" while its replacement string had not matched at all — the second instance of the shape recorded at `MD-B01-A002`. It was re-run with a landing assertion and the guard then failed correctly. All on-disk mutations were restored and the suite re-executed green.
- Guard self-correction: the terminology conflation guard rejected this declaration for quoting a forbidden claim as an example. The document was rewritten rather than exempted, following the `MD-B01-A003` rule that a carve-out weakens the guard for every other file. The same happened with the alias-repetition guard, which flagged this declaration for naming the alias without repeating its meaning; the sentence now repeats it.
- Compatibility result: no runtime surface changed. `F-MD-B01-A014-001` was raised for an executable non-conformance found while proving the alias cluster — `eligibility_export.csv` ships only the optional legacy projection and omits `listing_id`, `publication_id`, `data_usable`, and the reason set, which the contract calls out as "never as the sole V2 meaning". It is owned by `MD-B19` and is deliberately not fixed here: this declaration excludes runtime mutation and `MD-B19` has no baseline, so fixing it here would be the ungoverned kind of change this track exists to prevent.
- Raw artifacts/storage: no `storage/**` artifact was required, inspected, mutated, exported, or claimed.
- Findings/dependencies: `F-MD-B01-A001-001` extended with the previously unmeasured exclusion half and reopened as blocking 63 rows; `F-MD-B01-A003-001` measurement corrected from ten frozen contracts to one, still `OPEN`; `F-MD-B01-A014-001` raised; `MD-DEP-0004` extended so that each stage resolves classification as well as ownership and applicability at entry, with 630 reference-only members reported per stage.
- Downstream-stage effect: entry to `MD-B02` remains prohibited. The corrected denominator makes `MD-B01` further from closure than the prior record showed, not closer.

## Current boundary

The A014 scope is complete. The next attempt is locally openable and does not wait on a strategy-change process: 62 of the 63 remaining rows are ordinary implementation proof work. The single re-entry point is `MD-B01-A015`.
