# Strategy-to-Implementation Traceability Standard

## 1. Purpose

Current strategy is decomposed into stable rule/reference rows in `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`.

The matrix is a governed traceability artifact. It maps strategy semantics to proof obligations; it MUST NOT invent new strategy semantics.

## 2. Predicate rule

A row classified as a required/conditional implementation requirement MUST express a predicate that can be objectively proven true or false by implementation/evidence.

The following are not executable mandatory predicates by themselves and MUST be classified as reference/non-requirement context unless they independently express a testable obligation:

- section headings;
- list-introduction lines;
- labels/titles;
- descriptive context;
- examples;
- explanatory prose that merely introduces following requirements.

For a structured list, the predicate-bearing child rule(s), not the introductory header, own the implementation proof obligation.

## 3. Initial and current state

Required active rules begin `NOT_ASSESSED`; optional capability rules begin `OPTIONAL_NOT_REQUESTED` unless the applicable strategy/governance explicitly requires another state.

`SATISFIED` requires current correlation-first evidence tied to a valid `MD-Bxx` Attempt/Baseline/Epoch. Historical evidence may be supporting context only.

A rule MUST NOT be marked `SATISFIED` solely because code/tests exist or because an older work order previously passed.

## 4. Proof-owning stage

Each required predicate MUST be assigned to the stage that can own and close its current proof obligation. Stage assignment MUST follow the implementation/proof responsibility, not merely the physical document location or line position from which the strategy text was extracted.

Cross-stage supporting proof is allowed only through explicit registered relationships and must not create duplicate ownership of the same closure obligation.

## 5. Matrix mutability and correction

`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` is `MUTABLE_TRACEABLE` under `DOCUMENT_ROLE_REGISTRY.csv`. Correcting extraction/classification/stage-assignment defects in the matrix does not require changing strategy bytes when strategy meaning is unchanged.

Material matrix correction MUST:

1. preserve source traceability to the strategy authority;
2. document why prior classification/assignment was defective;
3. update affected coverage counts/state;
4. identify current attempts/baselines/evidence whose proof mapping is affected;
5. invalidate/rebaseline/revalidate affected current verification as required.

A prior `SATISFIED` state may be carried forward only when the corrected row has demonstrably equivalent predicate identity and the existing current evidence still proves that exact predicate under the current epoch/baseline rules. Otherwise it returns to `NOT_ASSESSED` until re-proven.

## 6. No pass-by-matrix manipulation

The matrix MUST NOT be edited merely to reduce required coverage or make a stage pass. Corrections are valid only when they restore faithful strategy semantics and proof ownership.
