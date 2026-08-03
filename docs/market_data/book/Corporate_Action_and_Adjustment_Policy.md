# Corporate Action Event and Adjustment Policy (LOCKED)

## Purpose

Define the verified, temporal, revisioned corporate-action lifecycle that may affect price continuity, volume continuity, event risk, analytical products, indicators, and eligibility.

This file owns event lifecycle and verification. `../registry/Price_Adjustment_Contract_LOCKED.md` owns factor application and product semantics. `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md` owns selected analytical defaults.

## Core safety rule (LOCKED)

A price discontinuity or anomaly is evidence of a possible event, not proof of a corporate action, action type, ex-date, or adjustment factor.

Only a verified, revisioned corporate-action event with sufficient source evidence and quantitative terms may supply an adjustment factor. Unknown/unverified events quarantine or block affected output; they never trigger synthetic adjustment or in-place history repair.

## Required event identity and lifecycle

Each event/revision must bind:

- immutable `corporate_action_id` and event revision identity
- stable issuer/instrument/listing identity
- governed action type
- authoritative/provider source and immutable source observation/reference/hash
- provider/exchange event identifier when available
- announcement date/time and `recorded_at`/`known_at`
- effective lifecycle dates when applicable: cum date, ex-date, record date, distribution/payment date, listing/effective date
- quantitative terms: ratio, subscription/exercise price, cash amount/currency, or factor inputs as applicable
- verification state, verifier/evidence, and superseded revision reference
- price/volume continuity semantics and factor revision identity

Unknown dates/terms are `NULL/UNKNOWN`; they must not be copied from another field merely to complete a row.

## Verification hierarchy (LOCKED)

Minimum verification states:

- `AUTHORITATIVE_VERIFIED` — event identity and terms confirmed from governed authoritative/exchange/CSD evidence
- `MANUAL_VERIFIED` — operator-approved terms with attached traceable evidence
- `PROVIDER_REPORTED` — provider reports an event, but required authoritative/manual verification is incomplete
- `SYNTHETIC_CANDIDATE` — anomaly/detector suggests an event or scale break
- `REJECTED/DISMISSED` — evidence shows candidate should not be used

Only `AUTHORITATIVE_VERIFIED` or governed `MANUAL_VERIFIED` revisions with complete applicable terms may be adjustment-active. Provider-reported events may be used for conservative event risk but not for an unproven factor. Synthetic candidates always quarantine and cannot become verified merely because their ratio resembles a common split.

## Effective-date hierarchy (LOCKED)

- `ex_date` is the primary anchor for price continuity and event-risk effects.
- A separately named verified effective date may be used only when action semantics prove it is the correct continuity boundary.
- `action_date`, announcement date, import date, detected break date, record date, and payment date are not interchangeable with ex-date.
- If no verified continuity anchor exists, adjustment is forbidden and the affected range remains quarantined/unknown.

A detector break date may bound contamination conservatively, but it does not overwrite the event ex-date.

## Event revision and publication binding

- Event/factor revisions are append-only.
- Terms or dates used by a sealed publication cannot be mutated.
- A late/corrected event creates a new event revision and, if output-affecting, correction/republication for every contaminated dependent date.
- Each analytical publication binds the exact event/factor revisions used.
- Reverting to prior terms is still a new revision/publication event.

## Candidate-break linkage (LOCKED)

Linkage from a detected break to a verified action must be explicit and atomic with the factor/verification selection used by a computation. A proximity match by ticker/date alone is diagnostic, not authoritative linkage.

If linkage, event terms, or factor verification is missing/conflicting, the break remains quarantining. No command may mark it repaired merely because bars were rewritten.

## Product boundary

- Canonical bars remain immutable `RAW` as-observed values.
- `STRUCTURAL_ADJUSTED` is a separate coherent OHLC/volume analytical product from verified structural factors.
- `TOTAL_RETURN` is a separate product for distribution effects when sufficient verified data exists.
- Provider `adj_close` is lineage/source context, not an adjustment product.

## Event-risk boundary

Event risk is distinct from adjustment eligibility. An event may be non-adjusting but still risky, or adjusting and still subject to disclosure/contamination until verified. Flags must include event/revision identity, anchor date, window, verification state, and reason codes.

## Forbidden behavior (LOCKED)

- deriving a verified event or factor from price movement alone
- converting `SYNTHETIC_CANDIDATE` to verified automatically
- using `action_date` silently as ex-date
- mutating factor fields already used by a sealed publication
- rewriting raw/current/history bars to apply or repair adjustment
- adjusting only close while leaving OHLC/volume incoherent
- clearing quarantine before verified event-factor-break linkage exists
- treating missing event evidence as no event

## Acceptance criterion (LOCKED)

No synthetic anomaly can become an active adjustment without a verified event revision and complete terms; every applied factor and contamination window must trace to event identity, source evidence, ex/effective date, revision, publication, and configuration.

## Cross-contract alignment

- `Corporate_Action_Impact_Flags_Contract.md`
- `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `../registry/Corporate_Action_Type_Registry_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
- `../registry/Price_Scale_Break_Detection_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
