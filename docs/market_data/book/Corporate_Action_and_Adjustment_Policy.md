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

## Continuity verdicts (LOCKED)

A continuity check compares the price series across a candidate anchor and records what the series shows. Its verdicts are runtime facts that quarantine dependent output, so they must be named and bounded here rather than left to implementation.

| Verdict | Meaning |
|---|---|
| `NO_SERIES` | Insufficient adjacent observations to measure continuity at the anchor |
| `NO_MATERIAL_GAP` | Measured discontinuity is below the materiality floor |
| `GAP_AMBIGUOUS` | A discontinuity exists but is within the range an ordinary session move could produce |
| `GAP_BEYOND_EXCHANGE_BAND` | Measured move exceeds what one session can produce under the exchange band owned by `../registry/Exchange_Market_Structure_Facts_LOCKED.md` |

### A continuity verdict is not a verification state (LOCKED)

The two axes are orthogonal and must never substitute for one another:

- **Verification state** answers *do we know this event happened, and on what terms* — it comes from authoritative or manual evidence.
- **Continuity verdict** answers *what does the price series show around this anchor* — it comes from arithmetic on observed prices.

Therefore:

- `GAP_BEYOND_EXCHANGE_BAND` establishes that a move is too large for an ordinary session. It does **not** establish the event, its type, its terms, or its factor. On its own it is a `SYNTHETIC_CANDIDATE` at best, and by the hierarchy above it quarantines rather than adjusts.
- `NO_MATERIAL_GAP` measured at the wrong anchor date proves nothing about the correct anchor. A verdict inherits the correctness of the date it was measured on.
- No verdict may be recorded as, converted into, or read as a verification state. A factor whose only justification is a continuity verdict fails the adjustment-active rule regardless of how large the measured move was.

### Resolving `GAP_AMBIGUOUS` (LOCKED)

An ambiguous verdict states that the series cannot distinguish an event from an ordinary move. It is resolved only by evidence **independent of the price series**:

- authoritative or manual verification of the event terms, which supplies the factor directly and makes the continuity question moot; or
- authoritative evidence that no adjusting event occurred at the anchor, which dismisses the candidate under the hierarchy above.

Explicitly insufficient to resolve it:

- absence of a detected price-scale break, because detection has a stated sensitivity floor and its silence is not evidence;
- the measured gap being small, since smallness is what made the verdict ambiguous;
- the passage of time, re-running the check, or the absence of complaints.

An unresolved `GAP_AMBIGUOUS` keeps its quarantine indefinitely. That is the intended fail-safe, not a backlog defect, and it may not be cleared to reduce the count of quarantined rows.

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
- recording a continuity verdict as, or reading it as, a verification state
- treating `GAP_BEYOND_EXCHANGE_BAND` as sufficient justification for an adjustment-active factor
- clearing `GAP_AMBIGUOUS` using evidence derived from the price series, including the absence of a detected break
- clearing quarantine in order to reduce the count of quarantined rows

## Capability boundary (LOCKED)

The verification hierarchy is the strongest gate in this package, which makes an unstated limit here especially costly.

**What the hierarchy proves.** That a factor becomes adjustment-active only from authoritative or governed manual evidence with complete applicable terms; that a detector candidate can never promote itself; that an unresolved anchor keeps its quarantine.

**What it cannot prove.**

- **That every event was recorded.** The hierarchy classifies events the platform knows about. An adjusting action that no source reported is not `PROVIDER_REPORTED`, not `SYNTHETIC_CANDIDATE`, and not quarantined — it is simply absent, and the affected series carries an uncorrected discontinuity with no flag on it.
- **That `AUTHORITATIVE_VERIFIED` terms are right.** The state records the class of evidence, not its accuracy. A transcription error in a verified ratio produces a confidently wrong factor.
- **That an absent factor means no adjustment was needed.** Absence records that no verified factor exists, which is equally consistent with an event nobody verified.
- **That quarantine coverage equals contamination coverage.** Quarantine follows recorded anchors. A contaminated window whose cause was never recorded is not quarantined.

Consequently a fully `AUTHORITATIVE_VERIFIED` event set may be cited as evidence that **recorded events were handled correctly**, never as evidence that **the series is free of unadjusted corporate actions**.

### Event completeness is verified externally (LOCKED)

The corporate-action record is a root of expectation, so it falls under the shared external-reconciliation rules owned by global gate 13 in `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`. Those rules are not repeated here.

Domain parameters owned by this contract:

- **Authority:** an authoritative exchange or CSD corporate-action record.
- **Scope:** from the intentional dataset start onward, covering every action type the type registry marks as adjusting or event-risk bearing.
- **Qualification:** an unadjusted-series claim covering an unreconciled period must name that period. The second reconciliation direction — an action that occurred but was never recorded — is the one that produces silently uncorrected discontinuities, and is therefore the direction that matters most here.

## Acceptance criterion (LOCKED)

No synthetic anomaly can become an active adjustment without a verified event revision and complete terms; every applied factor and contamination window must trace to event identity, source evidence, ex/effective date, revision, publication, and configuration.

## Cross-contract alignment

- `Corporate_Action_Impact_Flags_Contract.md`
- `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `../registry/Corporate_Action_Type_Registry_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
- `../registry/Price_Scale_Break_Detection_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
