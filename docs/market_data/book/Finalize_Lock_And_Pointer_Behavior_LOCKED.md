# Finalize Lock And Pointer Behavior Contract LOCKED

Status: LOCKED  
Owner: Market Data / Publication Finalize  
Scope: finalize determinism, lock-conflict semantics, current pointer switch safety

---

## 1. Policy Decision

Final policy: **OPTION C — DETERMINISTIC LOCK**.

Finalize may finish as `SUCCESS` only when the candidate publication is valid and the current publication pointer resolves back to the same sealed publication identity. Pointer switching is allowed only when the candidate satisfies all publishability preconditions and the post-switch pointer integrity check confirms the same `publication_id`, `publication_version`, `run_id`, and `trade_date`.

`RUN_LOCK_CONFLICT` is not a generic database lock error. In finalize behavior, it means the attempted current-publication ownership transition could not be proven safe. The conflict is an expected fail-safe outcome when a candidate cannot become the authoritative current publication without violating pointer/current integrity.

---

## 2. Finalize Contract

Finalize MUST be deterministic.

For the same run, same publication rows, same coverage state, same correction context, and same pointer state:

- first finalize and re-finalize MUST return the same terminal status;
- re-finalize of an already completed finalize run MUST NOT create a new current pointer switch;
- re-finalize of an already completed finalize run MUST NOT append duplicate finalize events;
- re-finalize MUST NOT change `publication_id`, `publication_version`, `trade_date_effective`, `terminal_status`, `publishability_state`, or `final_reason_code`;
- finalize MUST NOT leave more than one current publication for one `trade_date`;
- finalize MUST NOT produce a state where `eod_runs.is_current_publication`, `eod_publications.is_current`, and `eod_current_publication_pointer` disagree materially.

A finalized run is considered already completed when:

- `stage = FINALIZE`;
- `lifecycle_state = COMPLETED`;
- `terminal_status IN (SUCCESS, HELD, FAILED)`.

Such a run is terminal from the perspective of finalize execution. Re-running finalize for that same `run_id` returns the persisted run state as-is.

---

## 3. Lock Behavior Contract

`RUN_LOCK_CONFLICT` occurs when finalize attempts or validates current-publication ownership and the system cannot prove that the requested current state is safe.

It can be produced by:

1. promotion failure while replacing current publication;
2. correction baseline mismatch;
3. post-switch pointer resolution mismatch;
4. pointer/publication/run/version/date mismatch after promotion;
5. unsafe current integrity discovered during replacement.

Mapping:

| Condition | Terminal Status | Publishability | Pointer Action |
|---|---:|---:|---|
| Valid sealed candidate + coverage PASS + pointer resolves to candidate | `SUCCESS` | `READABLE` | Switch pointer to candidate |
| Already completed finalize rerun | persisted status | persisted publishability | No change |
| Promotion conflict with prior current available | `HELD` | `NOT_READABLE` | Restore prior current pointer |
| Promotion conflict without prior current | `HELD` | `NOT_READABLE` | Clear unsafe pointer/current state |
| Coverage FAIL/BLOCKED | `HELD` or `FAILED` according to fallback availability | `NOT_READABLE` | No candidate pointer switch |
| Repair candidate / non-current publish target | `SUCCESS` or `HELD` according to existing policy | `NOT_READABLE` unless existing current is preserved | Current pointer preserved |

Conflict is not a successful publish. Conflict is also not automatically a system crash. It is a traceable finalize outcome that prevents unsafe publication ownership.

---

## 4. Pointer Switch Contract

Pointer may switch only when all conditions are true:

1. run terminal decision is eligible for promotion;
2. coverage gate is `PASS`;
3. candidate publication is `SEALED`;
4. candidate has sealed metadata and publication version;
5. current replacement does not violate correction baseline ownership;
6. post-switch resolution from `eod_current_publication_pointer` joins back to the candidate publication;
7. resolved pointer identity matches:
   - `publication_id`;
   - `publication_version`;
   - `run_id`;
   - `trade_date`.

Pointer MUST be held or restored when any of those checks fail.

Pointer MUST NOT switch for:

- coverage failure;
- non-readable publishability;
- partial manual file publish;
- repair-candidate publish target;
- unchanged correction content;
- correction baseline mismatch;
- unresolved or malformed current pointer state.

---

## 5. Relationship To Existing Contracts

This contract does not redefine coverage, publishability, manual-file behavior, correction behavior, or read-side behavior.

It depends on them:

- coverage gate decides whether a candidate can be promotion-eligible;
- publishability decides whether a finalized run is readable;
- current pointer integrity decides whether consumer read paths may resolve the publication;
- fail-safe read behavior requires pointer ambiguity to resolve to no unsafe readable publication;
- audit traceability requires lock conflicts to persist as reason/event context.

---

## 6. Evidence Requirements

Every implementation or test touching finalize lock behavior MUST prove:

- first finalize success switches pointer once;
- re-finalize of the same completed run makes no state change;
- conflict scenario preserves or clears pointer safely;
- partial/non-readable publish does not become current;
- unchanged correction preserves existing current pointer;
- `RUN_LOCK_CONFLICT` has a specific traceable message/event payload.
