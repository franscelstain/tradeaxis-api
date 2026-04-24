# Manual File Publishability Policy LOCKED

Status: LOCKED  
Contract Version: manual_file_publishability_policy_v1  
Locked Date: 2026-04-24

---

## 1. Final Policy

Manual file is a source/acquisition mode, not a coverage-bypass mode.

Final policy: **HYBRID STRICT**.

A `manual_file` run may become `READABLE` only when the normal coverage gate passes and the sealed publication passes finalize/current-pointer integrity checks.

A partial `manual_file` dataset does not create a readable current publication by default.

---

## 2. Policy Options Reviewed

### OPTION A — STRICT

Manual file remains fully subject to the coverage gate.

Result:
- coverage PASS → eligible for `READABLE`
- coverage FAIL/BLOCKED → `NOT_READABLE`

Strength:
- safest integrity model
- no operator-driven accidental partial publication

Weakness:
- less usable when an operator intentionally uploads a partial repair file

### OPTION B — OVERRIDE

Manual file could bypass coverage with an explicit flag such as `--allow-partial` and a state such as `READABLE_WITH_OVERRIDE`.

Result:
- coverage FAIL could still become readable by operator override

Rejected for now because:
- it weakens read-side safety
- it requires a new consumer contract
- it requires evidence/replay/read adapters to understand partial-readable semantics
- current codebase does not yet have a safe consumer-side partial-read contract

### OPTION C — HYBRID STRICT / RECOMMENDED

Manual file remains coverage-gated, but failure outcome depends on fallback availability.

Result:
- coverage PASS → candidate may promote to current `READABLE`
- coverage FAIL + prior readable current/fallback exists → terminal `HELD`, publishability `NOT_READABLE`, effective date remains fallback
- coverage FAIL + no readable fallback exists → terminal `FAILED`, publishability `NOT_READABLE`

Selected because:
- preserves current publication integrity
- preserves fail-safe read behavior
- gives operators a safe result instead of corrupting current pointer ownership
- keeps audit traceability clear

---

## 3. Publishability State Contract

Allowed publishability states remain:

- `READABLE`
- `NOT_READABLE`

`HELD` is a `terminal_status`, not a `publishability_state`.

`READABLE_WITH_OVERRIDE` is not introduced in this contract version.

---

## 4. Coverage Interaction

Coverage gate is blocking for `current_replace` publication.

### Blocking cases

Coverage `FAIL` or `BLOCKED` blocks readable promotion for:
- `source_mode=manual_file`
- `source_mode=api`
- correction current publish paths
- full publish paths

### Non-current exception

A repair candidate may seal as non-current metadata, but it must not replace current publication ownership and must not become reader-authoritative.

---

## 5. Manual File Behavior

### Import

`market-data:daily --source_mode=manual_file --input_file=...` imports the supplied file only. Import success does not mean publishability success.

### Promote

`market-data:promote --source_mode=manual_file` evaluates coverage before publishability.

Partial file example:

- available: `5`
- expected: `901`
- coverage ratio: `0.0055`
- threshold: `0.9800`
- coverage gate: `FAIL`

Expected result:

- `coverage_gate_state=FAIL`
- `coverage_reason_code=COVERAGE_BELOW_THRESHOLD`
- `publishability_state=NOT_READABLE`
- `terminal_status=HELD` when fallback/current exists
- `terminal_status=FAILED` when no fallback/current exists

### Finalize

Finalize may promote the candidate only when:
- cutoff policy is satisfied
- candidate publication is sealed
- coverage gate is `PASS`
- current pointer sync succeeds
- post-finalize integrity check passes

---

## 6. Current Publication Integrity

A non-readable manual-file run must not remain current.

If a non-readable run accidentally owns current state, finalize integrity repair must:
- restore prior current publication when available
- clear current state when no prior current exists
- emit `RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED`

---

## 7. Evidence and Replay Contract

Evidence export and replay verification must preserve:
- coverage gate state
- coverage reason code
- terminal status
- publishability state
- final reason code
- effective trade date/fallback date
- current publication id/version when available

No evidence or replay flow may treat `manual_file` as readable merely because import succeeded.

---

## 8. Done Criteria

This policy is satisfied when tests prove:
- partial manual file without fallback is `FAILED` + `NOT_READABLE`
- partial manual file with fallback is `HELD` + `NOT_READABLE`
- no `READABLE_WITH_OVERRIDE` state is created
- current publication integrity is not changed by non-readable manual files
