# Strategy-to-Implementation Traceability Standard

## 1. Purpose

Current strategy is decomposed into stable rule/reference rows in `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`.

The matrix is a governed traceability artifact. It maps strategy semantics to proof obligations; it MUST NOT invent new strategy semantics.

The unit of proof is a **semantic predicate**, not merely one physical Markdown line.

## 2. Predicate rule

A row classified as a required implementation requirement MUST participate in a predicate that can be objectively proven true or false by implementation/evidence.

The following are not executable mandatory predicates by themselves and MUST be classified as reference/non-requirement context unless they independently express a testable obligation:

- section headings;
- list-introduction lines;
- labels/titles;
- descriptive context;
- examples;
- explanatory prose that merely introduces following requirements;
- bare document/file references, field names, enum/value fragments, defaults, or metadata labels whose obligation exists only because of surrounding parent text.

For a structured list, table, definition block, or other multi-line construct, the physical child line may carry the stable source identity while the **proof predicate** may require semantic context from its governing parent line(s).

## 3. Parent/child semantic-context binding

A required row whose `rule_text` is not a self-contained predicate MUST NOT be treated as proof-complete merely because the referenced object/field/value exists.

Before such a row may become `SATISFIED`, current traceability MUST deterministically identify:

1. the governing parent/context rule ID(s);
2. the normalized semantic predicate produced by composing the parent obligation with the child fragment;
3. proof that tests/evidence establish that normalized predicate, not only the child token or target existence.

`rule_text`, `source_line`, and `rule_fingerprint_sha1` remain source-integrity fields and SHOULD remain verbatim to the frozen strategy source. Context composition MUST therefore be recorded without rewriting strategy bytes or pretending the child line itself contains words it does not contain.

Until a dedicated matrix field is introduced, the canonical transition representation is a structured note in the affected row containing at least:

- `predicate_context=<rule_id[;rule_id...]>`
- `normalized_predicate=<complete testable statement>`

A source-line fingerprint proves source identity only. It does **not** prove that the extracted row is a complete semantic predicate.

If previously issued evidence proved only existence/registration of a referenced target while the normalized predicate requires alignment, ownership, prohibition, required-field membership, default semantics, or another stronger relation, that `SATISFIED` state MUST be invalidated and re-proven. Immutable evidence is not edited.

## 4. Applicability classes and lifecycle

Required rules MUST distinguish an always-applicable obligation from a rule whose **obligation itself exists only when an external condition is true**.

Conditional logic inside an always-applicable rule does not make the rule conditionally applicable. For example, "if input is invalid, reject it" is normally a mandatory behavioral rule because every implementation must implement the branch. A rule is conditionally applicable only when the strategy says the obligation is absent when the stated condition does not exist.

The `applicability` field uses these current values:

- `MANDATORY` — always part of the stage proof obligation;
- `CONDITIONAL_PENDING` — a conditional rule whose applicability condition has not yet been resolved for the current verification scope;
- `CONDITIONAL_APPLICABLE` — the condition is true; the predicate must be proven;
- `CONDITIONAL_NOT_APPLICABLE` — the condition is false for the current verification scope and that false condition is itself evidenced;
- `OPTIONAL_CAPABILITY` — optional capability not requested by current strategy scope;
- `REFERENCE_ONLY` — no executable proof obligation.

`MANDATORY_OR_CONDITIONAL` is a **legacy transitional value**. It may remain on unopened/unrevalidated stages during migration, but an active stage MUST resolve every one of its required rows to an explicit applicability class before claiming closure.

The corresponding coverage lifecycle is:

- `MANDATORY` → `NOT_ASSESSED` or `SATISFIED`;
- `CONDITIONAL_PENDING` → `APPLICABILITY_PENDING`;
- `CONDITIONAL_APPLICABLE` → `NOT_ASSESSED` or `SATISFIED`;
- `CONDITIONAL_NOT_APPLICABLE` → `NOT_APPLICABLE`;
- `OPTIONAL_CAPABILITY` → `OPTIONAL_NOT_REQUESTED` unless separately activated;
- `REFERENCE_ONLY` → `REFERENCE_ONLY`.

`NOT_APPLICABLE` is a valid terminal applicability outcome, not a synonym for `SATISFIED` and not a hidden PASS. It requires current evidence/rationale proving the condition false.

`APPLICABILITY_PENDING` blocks stage closure because the stage has not yet determined whether it owns a current proof obligation.

## 5. Coverage denominator

For current stage coverage:

- denominator = active `MANDATORY` rows + active `CONDITIONAL_APPLICABLE` rows;
- numerator = those denominator rows with `SATISFIED`;
- `CONDITIONAL_NOT_APPLICABLE` rows are reported separately and excluded from the denominator;
- `CONDITIONAL_PENDING` rows are reported separately, excluded from the percentage denominator, and **block closure**;
- rows still carrying transitional `MANDATORY_OR_CONDITIONAL` make the stage denominator **provisional** and block closure until applicability normalization is complete.

A coverage percentage MUST NOT hide pending applicability. Current summaries and closure records must report denominator, satisfied, not-assessed, conditional-not-applicable, conditional-pending, and transitional-unclassified counts when any such rows exist.

## 6. Initial and current state

Mandatory active rules begin `NOT_ASSESSED`. Conditional rules begin `APPLICABILITY_PENDING` until their condition is evaluated. Optional capability rules begin `OPTIONAL_NOT_REQUESTED` unless the applicable strategy/governance explicitly requires another state.

`SATISFIED` requires current correlation-first evidence tied to a valid `MD-Bxx` Attempt/Baseline/Epoch. Historical evidence may be supporting context only.

A rule MUST NOT be marked `SATISFIED` solely because code/tests exist or because an older work order previously passed.

## 7. Proof-owning stage

Each required predicate MUST be assigned to the stage that can own and close its current proof obligation. Stage assignment MUST follow the implementation/proof responsibility, not merely the physical document location or line position from which the strategy text was extracted.

Cross-stage supporting proof is allowed only through explicit registered relationships and must not create duplicate ownership of the same closure obligation.

## 8. Stage-entry semantic revalidation

Before an active stage can rely on its traceability denominator or close:

1. every required row in that stage must have explicit applicability (no unresolved legacy `MANDATORY_OR_CONDITIONAL`);
2. every context-dependent fragment must have deterministic parent/context binding and a normalized predicate;
3. existing `SATISFIED` rows affected by either correction must be revalidated against the normalized predicate;
4. proof-owning-stage assignment must be confirmed for the stage;
5. coverage counts must be recomputed from the corrected current matrix.

This may be performed stage-by-stage. Unopened stages do not need to be bulk-rewritten merely to keep a global percentage aesthetically complete, but they cannot claim closure on transitional classifications.

## 9. Matrix mutability and correction

`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` is `MUTABLE_TRACEABLE` under `DOCUMENT_ROLE_REGISTRY.csv`. Correcting extraction/classification/context/applicability/stage-assignment defects in the matrix does not require changing strategy bytes when strategy meaning is unchanged.

Material matrix correction MUST:

1. preserve source traceability to the strategy authority;
2. document why prior classification/context/applicability/assignment was defective;
3. update affected coverage counts/state;
4. identify current attempts/baselines/evidence whose proof mapping is affected;
5. invalidate/rebaseline/revalidate affected current verification as required.

A prior `SATISFIED` state may be carried forward only when the corrected semantic predicate is demonstrably equivalent and the existing current evidence still proves that exact predicate under the current epoch/baseline rules. Otherwise it returns to `NOT_ASSESSED` until re-proven.

## 10. No pass-by-matrix manipulation

The matrix MUST NOT be edited merely to reduce required coverage, mark a conditional rule non-applicable without evidence, or make a stage pass. Corrections are valid only when they restore faithful strategy semantics, applicability, and proof ownership.
