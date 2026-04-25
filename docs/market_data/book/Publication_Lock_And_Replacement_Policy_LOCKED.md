# Publication Lock & Replacement Policy LOCKED

Status: LOCKED  
Policy: DETERMINISTIC LOCK + EXPLICIT REPLACEMENT  
Scope: Market-data EOD publication ownership, current pointer switch, replacement conflict, and manual-file replacement behavior.

---

## 1. Purpose

This contract locks the runtime meaning of publication lock and replacement behavior for EOD market-data publications.

The goal is to ensure that publication replacement is deterministic, auditable, and safe for downstream consumers. A readable current publication is authoritative state and cannot be overwritten accidentally by a later promote run.

This contract does not redefine:
- coverage gate policy;
- manual-file publishability threshold policy;
- correction request lifecycle;
- read-side current-publication enforcement;
- schema structure.

---

## 2. Source-of-truth relationship

This contract extends and narrows the publication switch behavior already governed by:

- `Finalize_Lock_And_Pointer_Behavior_LOCKED.md`
- `Publication_Current_Pointer_Integrity_Contract_LOCKED.md`
- `Run_Status_and_Quality_Gates_LOCKED.md`
- `Manual_File_Publishability_Policy_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`

If there is ambiguity, this contract controls only publication replacement lock semantics. It must not be used to weaken coverage, correction, or read-side safety contracts.

---

## 3. Final policy selection

Selected policy:

> OPTION C — DETERMINISTIC LOCK + EXPLICIT REPLACEMENT

Rejected policies:

### OPTION A — STRICT NO REPLACE

Rejected because it blocks legitimate controlled replacement flows such as correction/current replacement and repair workflows.

### OPTION B — AUTO REPLACE

Rejected because it allows a later promote to overwrite an already readable current publication without explicit ownership proof.

---

## 4. Lock definition

`RUN_LOCK_CONFLICT` is not a generic database lock error.

In publication replacement context, `RUN_LOCK_CONFLICT` means:

> the system detected an unsafe or unauthorized attempt to change authoritative current-publication ownership for a trade date.

A valid current publication is defined as a publication state where all of the following are true:

- `eod_current_publication_pointer` exists for the trade date;
- pointer publication/run/version matches the publication and run rows;
- `eod_publications.is_current = 1`;
- `eod_publications.seal_state = SEALED`;
- publication has `sealed_at`;
- owning `eod_runs.terminal_status = SUCCESS`;
- owning `eod_runs.publishability_state = READABLE`;
- owning `eod_runs.is_current_publication = 1`.

A valid current publication represents authoritative consumer-readable state.

---

## 5. Conflict decision table

| Condition | Conflict? | Required behavior |
|---|---:|---|
| No current pointer exists | No | Candidate may be promoted if all publishability gates pass. |
| Current pointer exists but does not resolve to a valid readable publication | No replacement allowed until repaired | Treat as integrity issue; repair or clear invalid current state before replacement. |
| Valid current publication exists and promote is not correction/current replacement | Yes | Block with `RUN_LOCK_CONFLICT`; pointer must not change. |
| Valid current publication exists and promote supplies matching prior publication baseline | No | Replacement may proceed as controlled correction/current replacement. |
| Pointer baseline no longer matches supplied prior publication | Yes | Block with `RUN_LOCK_CONFLICT`; pointer must not change. |
| Candidate is not sealed | Yes | Block; candidate is not eligible for pointer switch. |
| Candidate is sealed but missing `sealed_at` | Yes | Block; sealed candidate state is incomplete. |
| Coverage gate fails | Not a lock conflict | Final outcome is governed by coverage/publishability contract; pointer must not change. |
| Manual file full publish tries to overwrite valid current without controlled baseline | Yes | Block with `RUN_LOCK_CONFLICT`; manual file is not implicit override. |

---

## 6. Replacement rule

A candidate publication may become current only when all of the following are true:

1. candidate publication is sealed;
2. candidate publication has `sealed_at`;
3. finalize decision is `SUCCESS` + `READABLE`;
4. coverage gate is `PASS` where coverage applies;
5. either:
   - no valid current publication exists for the trade date; or
   - replacement is a controlled correction/current replacement using the expected prior current baseline;
6. post-switch pointer validation confirms publication id, run id, publication version, and trade date match.

If a valid current publication already exists and the promote path is not a controlled replacement, the system must return or persist `RUN_LOCK_CONFLICT` and must not switch the pointer.

---

## 7. Manual-file rule

`manual_file` is not an implicit override mode.

Manual-file promote must still obey:

- coverage gate;
- publishability decision;
- current-publication lock;
- pointer switch validation.

A manual-file run may create a candidate publication and may become current only if it passes all normal current-publication rules.

A manual-file run must not overwrite an existing valid current publication merely because the source is manually supplied.

Any future force/override behavior requires a separate explicit operator contract and must define:

- command flag name;
- required audit reason;
- DB telemetry field or event payload;
- whether replacement is allowed against a valid current publication;
- how downstream evidence distinguishes normal replacement from forced replacement.

Until such a contract exists, `force_replace` is not part of the locked runtime behavior.

---

## 8. Pointer rule

The current pointer may change only when the final promoted run is:

- `terminal_status = SUCCESS`;
- `publishability_state = READABLE`;
- coverage gate is `PASS` where evaluated;
- candidate publication is `SEALED` and has `sealed_at`;
- lock conflict checks pass;
- post-switch pointer resolver returns the expected publication/run/version.

The current pointer must not change when final outcome is:

- `HELD`;
- `FAILED`;
- `NOT_READABLE`;
- `RUN_LOCK_CONFLICT`;
- coverage failure;
- non-current repair candidate publication.

---

## 9. Idempotency rule

Re-finalizing an already completed FINALIZE run must be idempotent:

- no duplicate publication;
- no duplicate pointer switch;
- no change to current pointer;
- existing terminal state is returned or preserved.

Re-promote must not silently overwrite an existing valid current publication unless the run is operating under a controlled replacement path with valid baseline ownership.

---

## 10. Multi-run rule

Multiple runs may exist for the same `requested_date` / trade date.

Only one publication may be authoritative current publication for a trade date.

Historical candidate and prior publications may remain in `eod_publications`, but only the pointer-resolved valid current publication is consumer-readable authoritative state.

---

## 11. Required evidence

A complete implementation or validation session must prove:

- first clean promote can create a readable current publication;
- second uncontrolled promote against that valid current publication is blocked with `RUN_LOCK_CONFLICT`;
- pointer remains unchanged on conflict;
- controlled correction replacement still works only with matching prior baseline;
- re-finalize does not duplicate publication or pointer writes;
- partial or not-readable manual-file publish does not change pointer;
- audit docs record the proof append-only.

---

## 12. Current implementation note

The current runtime implementation enforces the locked behavior primarily in:

- `EodPublicationRepository::promoteCandidateToCurrent(...)`
- `MarketDataPipelineService::completeFinalize(...)`
- `PublicationFinalizeOutcomeService`
- current-pointer resolver methods in `EodPublicationRepository`

The repository blocks uncontrolled replacement when a different current pointer already exists and no prior baseline is supplied. Finalize converts unsafe promotion failures into `RUN_LOCK_CONFLICT` / non-readable outcome and protects pointer state.

---

## 13. Done criteria

This policy is considered enforced only when:

- `RUN_LOCK_CONFLICT` is traceable as ownership conflict;
- valid current publication cannot be overwritten by ordinary promote;
- pointer changes only on `SUCCESS` + `READABLE` eligible runs;
- manual file does not bypass current-publication lock;
- audit files are updated append-only with runtime/test proof.
