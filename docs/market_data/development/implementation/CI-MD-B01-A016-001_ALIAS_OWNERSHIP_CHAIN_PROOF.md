# MD Change Impact Declaration — CI-MD-B01-A016-001

- ID: `CI-MD-B01-A016-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A016` / `MD-B01-A016-BL001` / `MD-REBASELINE-20260820-001`
- Governing decision: `D-MD-20260822-04`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-22, after the A016 baseline lock and before any A016 test or traceability mutation.

## Why this attempt

`D-MD-20260822-04` resolved `MD-DEP-0005`. It established that `MD-S020-R0067`'s obligation is discharged for `CONSUMER_READ_CONTRACT_LOCKED.md` through canonical semantic ownership plus that document's own explicit delegation of field semantics.

A decision settles how an obligation may be discharged. It is not proof that the discharge holds. This attempt supplies the proof and binds the row.

## Affected strategy rules

`MD-S020-R0067` only. It is the single `NOT_ASSESSED` row in a 207-row denominator. No other row is touched, promoted, demoted, or rebound.

## Planned proof method

1. Assert each link of the ownership chain separately — canonical owner, delegation, delegated contract, owner-boundary alignment, and the decision's continued presence and scope limit. A chain proven only end-to-end would stay green while an intermediate document dropped the sentence carrying it.
2. Assert that the reviewed premise still holds: the readiness contract still names the alias and still does not state the meaning itself. If that changes, the decision is revisited rather than silently outgrown.
3. Prove each link is load-bearing by removing it from a copy of its document text and confirming the removal lands.
4. Bind the row to this attempt's evidence with the governing decision recorded in the row note.
5. Invert the two gate locks that require the rule to stay `NOT_ASSESSED`, so a regression or a misbinding fails closed rather than passing.

## Affected areas

| Area | Impact |
|---|---|
| Strategy | **None.** No byte is written. Mutations used to prove the guard are restored and confirmed byte-identical by git. |
| Schema / migrations / configuration / application code / runtime | No mutation. |
| Tests / gates | Material: one new proof suite; two gate drift locks inverted; two gate self-tests updated. |
| Traceability | Material: exactly one row moves `NOT_ASSESSED` → `SATISFIED`. Denominator unchanged at 207. |
| Evidence | Additive A016 evidence and, if criteria hold, the `MD-B01` closure manifest. |
| Raw artifacts / storage | None. |

## Compatibility and residue risk

The dominant risk is binding the rule to the decision instead of to proof. A decision that unblocks is not a decision that proves; the row must point at the attempt that executed the check.

Second risk: duplicating `data_usable` into the readiness contract to make the traceability tidy. The decision explicitly rejected that, and `MD-S056-R0141` forbids a non-owner document from redefining a registered term.

Third risk: leaving a gate asserting the pre-decision state. Two gates required `MD-S020-R0067` to be `NOT_ASSESSED`; left unchanged they would have failed for the right reason and been "fixed" by weakening. They are inverted instead, so the resolved rule cannot regress unnoticed.

## Strategy semantic change

`NO`.

## Executed impact and result

- Strategy bytes changed: **0**, verified by `git status` after every mutation was restored. Schema, migrations, configuration, application code and runtime: unchanged.
- `MD-S020-R0067` bound to `E-MD-B01-A016-001` with `governing_decision=D-MD-20260822-04` in the row note. **`MD-B01` moves from 206/207 to 207/207.** Denominator unchanged; sha1 recomputed across all 6490 rows with 0 fingerprint mismatches.
- New suite `AliasMeaningOwnershipChainTest`: 6 tests / 28 assertions, PASS. Whole suite: **1686 tests, 11432 assertions, 0 failures**. All six gates PASS.
- Two mutations, each verified as landed, each turning the chain proof red: the delegated contract dropping the repetition, and the decision losing its scope limit. Both restored.
- Both gate locks inverted from "must stay `NOT_ASSESSED`" to "must stay `SATISFIED` and bound to `E-MD-B01-A016-001`", with their self-tests updated and re-executed.
- **A guard flagged the decision record itself** — it quoted the tradability misreading it exists to deny, and the alignment guard read the quotation as an assertion. The sentence was rewritten to refer to the misreading rather than reproduce it. No exemption was added; seventh occurrence, same handling.

## Current boundary

`MD-B01` has full required coverage with every row bound to executed proof. Closure follows in `SC-MD-B01-A016-001`.
