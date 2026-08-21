# MD Change Impact Declaration — CI-MD-B01-A004-001

- ID: `CI-MD-B01-A004-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A004` / `MD-B01-A004-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21, before the matrix mutation, from the dry-run measurement that scoped it
- Remediates: `MD-DEP-0004`, `F-MD-B01-A001-001`

## Why this attempt is material

It mutates `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`, which governs every stage's proof obligation. `CHANGE_IMPACT_DECLARATION_STANDARD.md` section 1 names traceability/baseline/closure behaviour as material work.

## What is being corrected, and why the prior classification was defective

`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` section 2 states that headings, list-introduction lines, labels, descriptive context, examples, and introductory prose are not executable mandatory predicates, and that for a structured list the predicate-bearing child rules — not the introductory header — own the proof obligation.

The matrix violated this in both directions at once. It marked 214 non-predicates as `REQUIRED`, including 210 bare colon-terminated list introducers such as `Artinya sistem harus mampu:` and `Consumer downstream dilarang:`. At the same time it left 817 predicate-bearing children of those introducers as `REFERENCE_ONLY` — the clauses that carry the actual obligation, such as `- baca hanya dari publication (sealed + readable + current)` and `- pakai MAX(date)`.

The split was arbitrary within single homogeneous lists: under `Domain ini tetap menjadi owner untuk:` the first bullet was `REQUIRED` and its nine siblings were `REFERENCE_ONLY`, with no semantic difference between them. The pattern is consistent with a generator that classified by position rather than by meaning.

## Strategy IDs / rules affected

**No strategy byte changes.** `rule_text`, `rule_fingerprint_sha1`, `strategy_owner`, and `source_line` are not touched on any row, so source traceability to the strategy authority is preserved exactly and the documentation gate's fingerprint check remains the control on that.

Rows changed: 214 demoted to reference context, 817 promoted to required. Every affected `rule_id` retains its identity and its link to the strategy line it was extracted from.

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** Required mandatory/conditional set moves from 1407 to 2010, a net increase of 603. |
| Schema / migration / configuration / runtime / provider / backfill / replay / ops | **None.** No file under `app/`, `database/`, `config/`, or `tests/` is touched. |
| Tests / gates / generators | **None changed.** The documentation integrity gate and the generated `CURRENT_STATE` are rerun because their inputs changed. |
| Evidence | **Material.** Coverage figures cited by `E-MD-B01-A001-001`, `E-MD-B01-A002-001`, and `E-MD-B01-A003-001` were taken against the pre-correction denominator. Those records remain immutable and true as issued; the current denominator is restated here rather than by editing them. |
| Baseline / closure | **Material.** `MD-B01-A004-BL001` binds the pre-correction matrix `FCF73F4FC912537B1742AC9C57EA12956D576FBD` so the change is diffable against a fixed reference. |

## Compatibility risk

**Low, and deliberately in the strict direction.** The correction raises the requirement count by 43%. No stage becomes easier to close; several become measurably harder. Nothing that was proven becomes unproven.

## Residue / rework risk

**Low.** Zero of the 21 currently `SATISFIED` rows are demoted, so no proven work is hidden by the change and no coverage is manufactured by it. All 21 keep their `rule_text` and fingerprint unchanged, which is the condition section 5 sets for carrying a prior `SATISFIED` forward: demonstrably equivalent predicate identity with evidence that still proves that exact predicate.

The 817 promoted rows enter as `NOT_ASSESSED`. They are new obligations, not new failures.

## Affected dependencies and relationships

- `MD-DEP-0004` — the predicate-classification half is remediated by this attempt. The proof-owning-stage half of section 4 is **not** attempted here and the dependency stays open for it; see the scope boundary below.
- `F-MD-B01-A001-001` — its measured claim (210 non-predicate required rules across 17 of 18 stages) is the input to this correction.

## Strategy semantic change

`NO`.

## Scope boundary

This attempt corrects classification by predicate meaning only. It does **not** re-derive `primary_stage` by proof ownership under section 4. That requires a per-rule judgement across more than two thousand rows — for example whether `MD-S001-R0065`, a read-side rule extracted from the `MD-B01` baseline document, is provable only at `MD-B17`. Doing that mechanically would substitute a heuristic for the judgement the standard asks for, which is the same defect being corrected here. It is left open and named rather than guessed.
