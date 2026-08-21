# F-MD-B01-A003-001 — Ten frozen strategy contracts use `eligible` without repeating its data-usability meaning

- Status: `OPEN`
- Severity: `P2`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A003` / `MD-B01-A003-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: governance — the affected documents are frozen strategy and cannot be edited by an implementation attempt
- Blocks: `MD-S020-R0067` reaching `SATISFIED`

## Finding

`Domain_Boundary_Invariants_LOCKED.md` states the obligation and the reason in the same breath:

> `eligible` is the most policy-suggestive name on the entire market-data surface: read plainly, it says *permitted to trade*. Every contract that uses it must therefore repeat that it means `data_usable`, and that repetition is the only thing preventing the misreading.

27 active documents use the bare word `eligible`. Eleven of them do not repeat the data-usability meaning in any form — neither `data_usable` nor `data-usability`. Ten are frozen strategy contracts:

| Document | Role |
|---|---|
| `book/CONSUMER_READ_CONTRACT_LOCKED.md` | STRATEGY |
| `book/Dataset_Seal_and_Freeze_Contract_LOCKED.md` | STRATEGY |
| `book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` | STRATEGY |
| `book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md` | STRATEGY |
| `book/Finalize_Lock_And_Pointer_Behavior_LOCKED.md` | STRATEGY |
| `book/Manual_File_Publishability_Policy_LOCKED.md` | STRATEGY |
| `book/Publication_Lock_And_Replacement_Policy_LOCKED.md` | STRATEGY |
| `book/Source_Data_Acquisition_Contract_LOCKED.md` | STRATEGY |
| `ops/Commands_and_Runbook_LOCKED.md` | STRATEGY |
| `registry/Volume_and_Turnover_Normalization_LOCKED.md` | STRATEGY |

`CONSUMER_READ_CONTRACT_LOCKED.md` is the most consequential of the ten: it is the contract a downstream consumer reads to learn what the read product means, and it is exactly where the misreading the boundary contract warns about would occur.

The eleventh is `records/evidence/E-MD-B00-A001-001_BASELINE_INVENTORY.md`, an issued evidence record. Evidence is `IMMUTABLE_AFTER_ISSUE`, so it is correctly not editable; it is listed for completeness rather than as an action item.

## This is a governed pending state, not an ungoverned defect

The same owner contract anticipates it:

> Where a dependent document uses older wording that conflates eligibility with readability or policy, this owner boundary takes precedence until that dependent contract reaches its ordered strategy-update step.

So the boundary contract governs the meaning in the interim and nothing is currently ambiguous *in law*. What is missing is a record that ten ordered strategy-update steps are outstanding. Without one, "the owner boundary takes precedence until the update" quietly becomes "the update never happens", and the repetition the contract calls the only protection is never added.

## Why this attempt did not fix it

The ten documents are registered in `MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json` and verified byte-for-byte by the documentation integrity gate. Editing them would break the freeze, change the strategy fingerprint bound into every baseline lock, and constitute a strategy revision — which `DOCUMENT_CHANGE_POLICY.md` reserves for an explicitly authorised strategy change, not an implementation attempt.

`MD-S020-R0067` therefore stays `NOT_ASSESSED`. It is not satisfiable by implementation work at all: its subject is the wording of strategy contracts.

## Measurement note

An earlier pass of this scan reported eighteen documents by matching only the snake_case `data_usable`. That was wrong. `Terminology_and_Scope.md` and six others state the meaning in prose as `data-usability`, which satisfies the obligation as written — the contract requires the meaning to be repeated, not a specific spelling. The corrected pattern accepts `data_usable`, `data-usability`, and `data usability`, and yields eleven.

## Required outcome

A governance decision that either schedules the ten ordered strategy-update steps, or records explicitly that the owner-boundary precedence in `MD-S020-R0189` is the permanent arrangement and `MD-S020-R0067` is therefore satisfied by that precedence rather than by repetition. Either resolution is defensible; leaving the question open is what erodes the boundary.

## Related

- Independent of `F-MD-B01-A001-001` (`MD-DEP-0004`), which concerns requirement classification rather than document wording.
- The alias containment half of the boundary — that `eligible` is derived from `data_usable` and reaches no new surface — is proven and recorded under `MD-S020-R0173` in `E-MD-B01-A002-001`.

## Measurement corrected — MD-B01-A014

The count of ten frozen strategy contracts is wrong, and the correction is recorded here rather than by editing the table above, which stands as what this finding was published with.

The scan was taken on the bare English word `eligible`. `MD-S020-R0067` governs the compatibility **field** named `eligible` — the section it sits in is titled "Retirement of the `eligible` alias" and its sibling rules speak of columns, reason codes, config keys, and API fields. The subject of the obligation is therefore the identifier, not the word.

Re-measured on the identifier, across the same active corpus: **24 documents use the alias, and 5 do not repeat the meaning.** Of the ten frozen contracts named above, eight never use the alias at all:

| Document | What it actually contains |
|---|---|
| `book/Dataset_Seal_and_Freeze_Contract_LOCKED.md` | `success-eligible` runs; the eligibility snapshot artifact |
| `book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` | the domain noun `eligibility` as a gate category |
| `book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md` | `success-eligible` run state |
| `book/Finalize_Lock_And_Pointer_Behavior_LOCKED.md` | `promotion-eligible` candidates |
| `book/Manual_File_Publishability_Policy_LOCKED.md` | "coverage PASS → eligible for `READABLE`" |
| `book/Publication_Lock_And_Replacement_Policy_LOCKED.md` | "not eligible for pointer switch" |
| `book/Source_Data_Acquisition_Contract_LOCKED.md` | retry-diagnostic counts; the eligibility artifact |
| `ops/Commands_and_Runbook_LOCKED.md` | "eligible non-authoritative artifacts" for purge |

`registry/Volume_and_Turnover_Normalization_LOCKED.md` names `eligible` once, as a cross-reference to a prior alias while retiring `dv20_idr`. It exposes no such field, so it is not a contract that uses the alias either.

**One frozen strategy contract genuinely fails: `book/CONSUMER_READ_CONTRACT_LOCKED.md`.** It states `eligible = 1` as a signal that cannot establish freshness, and never repeats that the field means `data_usable`. That is the document this finding already identified as the most consequential — it is the contract a downstream consumer reads, which is exactly where the boundary contract warns the misreading occurs.

### What changes and what does not

`MD-S020-R0067` remains `NOT_ASSESSED` and this finding remains `OPEN`. One frozen contract failing is still a frozen contract failing, and implementation cannot remediate it.

What changes is the size of the required outcome: **one** ordered strategy-update step, not ten. Eight documents were named as defective that are not.

### Do-not-repeat

This finding already carried a measurement note correcting the *repetition* side of the pattern — `data_usable` alone missed the seven documents that state the meaning in prose. The subject side was never checked. Correcting one half of a two-sided scan and publishing the result reads as diligence and is not: both sides of a match have to be right before the number means anything.

The corrected measurement is now executable and pinned, so it cannot silently revert: `AliasNamingAndMeaningBoundaryTest::test_the_alias_meaning_repetition_gap_is_measured_on_the_identifier_not_the_word` asserts the exact five-document result, and `::test_the_word_sense_scan_would_flag_documents_that_never_use_the_alias` asserts that six of the wrongly-named documents contain the word and do not use the alias. Evidence `E-MD-B01-A014-001`.
