# Trading Status Source Contract (LOCKED)

## Purpose

Define point-in-time, source-backed trading-status facts used by Regular-Market bar expectation, quality, event risk, eligibility, and replay.

## Core rule (LOCKED)

Trading status is a temporal fact about a stable listing/instrument. It is not current ticker state, not an inference from missing Yahoo data, and not a liquidity/dormancy classification.

## Required status record

Each source-backed status event/state must include:

- immutable status observation/event identity
- stable `instrument_id` and `listing_id`
- market segment/board scope
- status/event type from a governed dictionary
- effective start/date and, when applicable, effective end/clear event
- whether it applies to the full Regular-Market session or only part of it
- source/provider and source reference/hash
- observed/announced timestamp and platform recorded timestamp
- revision and verification state
- as-known identity/cutoff support for replay

Examples include suspension, unsuspension, halt, relisting/resumption, UMA, board/status change, or other governed exchange states. Their exact expectation and risk semantics belong in the status-type registry/dictionary, not in free-text parsing.

## Source authority (LOCKED)

The contract already requires every status record to be source-backed. It must also say **which sources may act as authority**, otherwise "source-backed" is satisfied by any writer.

Every registered status source is classified into exactly one authority class:

| Class | May establish status | Requirement |
|---|---|---|
| `EXCHANGE_AUTHORITATIVE` | yes | published by the exchange or its official dissemination channel; reference and payload hash retained |
| `DERIVED_REFERENCE` | no | third-party restatement of an exchange fact; may corroborate or trigger review, never establish |
| `OPERATOR_ENTERED` | conditional | see manual import scope below |

A bar/price source is never a status authority. Absence of a bar carries no status meaning, as already stated in the core rule.

### Source priority

- Priority is declared per status type in the governed source registry, not decided per record at write time.
- When two sources of the same authority class disagree for the same instrument, date, and status type, the record is **held** and emits explicit conflicting-status evidence. It is not resolved by recency, by majority, or by field-level merge.
- A `DERIVED_REFERENCE` source never outranks an `EXCHANGE_AUTHORITATIVE` source, and never resolves a conflict between two authoritative sources.
- A disagreement that cannot be resolved leaves status `UNKNOWN/NO_EVIDENCE` and bar expectation `BAR_EXPECTATION_UNKNOWN`. It never defaults to normally trading.

### Manual and file-based import scope

Controlled manual import is **transport by default**. Importing a file does not grant authority to its contents.

- An imported record inherits the authority class of the **originating source it reproduces**, which must be declared at import time along with its reference and hash.
- Import cannot upgrade authority. A file reproducing a `DERIVED_REFERENCE` notice remains `DERIVED_REFERENCE` after import.
- `OPERATOR_ENTERED` may establish status only when the record carries an explicit authoritative source reference, a named operator, and a governed reason code. Operator judgement alone is never the evidence.
- An imported record without a declared originating source is quarantined, not stored as unknown-origin truth.

This keeps `market-data:events:import-trading-status` a transport path and prevents recovery tooling from silently becoming a status authority.

## Temporal state resolution (LOCKED)

- Status for T is resolved from events/effective intervals valid on T.
- A carried suspension requires an explicit stateful event type and remains active only until a governed clear/end condition.
- Exact-date notices that do not carry forward must not become indefinite status.
- Current status must not be projected backward.
- Late or corrected notices create revisions; sealed publication bindings remain traceable to the revision used.
- Absence of a status row means `UNKNOWN/NO_EVIDENCE`, not automatically active, unsuspended, or safe.

## Expected-bar interaction (LOCKED)

Status resolution must output an explicit bar-expectation effect:

- `BAR_EXPECTED`
- `BAR_NOT_EXPECTED` only with verified evidence covering the applicable Regular-Market session
- `BAR_EXPECTATION_UNKNOWN`

Only verified point-in-time `BAR_NOT_EXPECTED` evidence may remove a listing/date from the expected-bar denominator. Dormancy, zero volume, provider failure, current `is_active`, or inferred price behavior cannot do so.

A partial-session halt/suspension does not automatically prove that no EOD bar was expected; the effective time and full-session semantics must be evaluated.

## Quality, event-risk, and eligibility interaction

- Trading status remains a separate fact from coverage delivery.
- Unknown or conflicting status creates explicit quality/event-risk state.
- Suspension/UMA/event-risk facts may block upstream eligibility under governed reason codes.
- Status does not create ranking, alpha, or watchlist selection.

## Source and revision safety

- Raw notices/payloads follow immutable observation-envelope rules.
- Conflicting sources are not majority-voted or merged field by field.
- Synthetic dormancy or price-break inference cannot be promoted to verified trading status.
- Corrections create new status revisions and, when output-affecting, new publication lineage.

## Failure behavior

- Unknown type: quarantine the status record.
- Missing stable mapping: reject/quarantine; do not attach status by current ticker text.
- Overlapping contradictory carried states: hold affected expectation/eligibility and emit evidence.
- Source unavailable after activation: expose degraded state; do not silently treat all listings as normally trading.

## Acceptance criterion (LOCKED)

For trade date T, a historical suspension/unsuspension sequence must resolve using only temporal records valid and known under the requested replay mode. A current status lookup or missing provider bar must never substitute for that proof.

## Cross-contract alignment

- `Market_Calendar_Requirements_Contract.md`
- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Coverage_Universe_Definition_LOCKED.md`
- `EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `Replay_Verification_Contract_LOCKED.md`
