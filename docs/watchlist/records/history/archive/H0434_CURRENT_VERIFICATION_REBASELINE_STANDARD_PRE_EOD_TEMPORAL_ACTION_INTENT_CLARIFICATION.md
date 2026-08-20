# Current Verification Rebaseline Standard

> **Status:** CANONICAL GOVERNANCE  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> **Scope:** seluruh current/future Watchlist Weekly Swing implementation, proof, result reuse, and stage closure  
> **Purpose:** memastikan tidak ada PASS/FAIL/DONE/READY lama yang diwariskan sebagai current verification setelah strategy/governance/architecture berubah material.

## 1. Core Rule

Semua verdict/result/status yang diterbitkan **sebelum verification epoch `WS-REBASELINE-20260819-001`** tetap merupakan fakta historis, tetapi **kehilangan current verification effect**.

Historical record tidak dihapus dan tidak ditulis ulang. Current evaluation dimulai ulang dari canonical authority sekarang.

```text
historical PASS / FAIL / PARTIAL / DONE / READY
        -> historical fact only
        -> NOT current verification
        -> NOT current strategy coverage proof
        -> NOT current stage closure proof
```

## 2. Current Starting State

Untuk revalidation track saat epoch diterbitkan:

- mandatory/conditional strategy rules: `985`;
- current `SATISFIED`: `0`;
- current `NOT_ASSESSED`: `985`;
- optional CONFIRM remains `OPTIONAL_NOT_REQUESTED` unless explicitly requested;
- `WS-B00..WS-B12` keep their current lifecycle initialization from Stage Register;
- existing code/docs may be reused only after current revalidation.

`0% verified` **tidak berarti 0% code exists**. Itu berarti belum ada implementation/proof yang sah terhadap current authority + current Work Baseline.

## 3. Record Classes

### Pre-rebaseline evidence / decision / result

- historical body/verdict preserved exactly;
- `current_verification_status = HISTORICAL_ONLY`;
- `current_proof_eligible = NO`;
- may be used for diagnosis, comparison, do-not-repeat, or historical context;
- MUST NOT satisfy current Traceability Matrix or current Stage Closure.

### Existing implementation contract / guide / DB / test / fixture / example

- remains in its role-correct development location;
- initial current state = `NOT_ASSESSED_REVALIDATION_REQUIRED`;
- may be reused, partially reused, superseded, or replaced only after current stage assessment;
- historical PASS statements inside such documents do not grant current conformance.

### Strategy / governance authority

Current authority remains authoritative subject to controlled-revision governance. Rebaseline resets **verification of implementation**, not canonical business/process authority.

### Current process tooling / registries / navigation

May remain current support if they govern the new verification process. They do not count as implementation proof.

## 4. No Historical Inheritance

A current strategy row may become `SATISFIED` only from current evidence that is:

1. registered as a current `WS-Bxx` Work Record;
2. bound to one current Attempt ID + Baseline ID;
3. Baseline Lock carries verification epoch `WS-REBASELINE-20260819-001`;
4. evidence is current-proof eligible;
5. tests/residue/integrity requirements pass.

`LEGACY:*`, C/R/P/B/S/Q historical evidence, pre-epoch decision, old shadow/OOS result, or old status ledger entry may be referenced, but may not replace current proof.

## 5. Existing Implementation Revalidation Outcomes

When a current stage assesses an existing technical document/code path, use one of:

- `REVALIDATED_CURRENT`
- `PARTIALLY_CONFORMANT_REMEDIATION_REQUIRED`
- `NON_CONFORMANT`
- `SUPERSEDED`
- `REPLACED`

Until such evidence exists, status remains `NOT_ASSESSED_REVALIDATION_REQUIRED`.

## 6. Status Ledgers

Historical C-number/PR/rehearsal/rollout statuses in `LUMEN_IMPLEMENTATION_STATUS.md` and `LUMEN_CONTRACT_TRACKER.md` remain append-only historical facts. Current verification authority is:

`CURRENT_VERIFICATION_EPOCH.json -> Stage Register -> Traceability Matrix -> current Work Records`.

## 7. Current Findings

Pre-epoch findings do not automatically remain current blockers. They are historical finding lineage unless a current attempt reopens/reconfirms the issue with current evidence. Their Record ID may be cited as context.

## 8. Physical Placement and One-Role Rule

Rebaseline does not move documents merely because status changed. Placement remains determined by authoritative role according to `ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md`:

- implementation stays in `development/implementation/`;
- finding stays in `development/findings/`;
- evidence stays in `records/evidence/`;
- decision stays in `records/decisions/`;
- history/source stays in `records/history/`.

Status is carried by current verification registry, not by moving files between status folders.

## 9. Machine Enforcement

Documentation/relationship gates MUST reject:

- `SATISFIED` strategy row backed only by pre-epoch/unregistered evidence;
- Work Baseline whose verification epoch differs from current epoch;
- legacy evidence marked current-proof eligible;
- behavior-bearing implementation document treated current-conformant before revalidation;
- pre-epoch PASS/DONE/READY interpreted as current stage/proof status.

## 10. One-line Rule

> **Old verdicts remain true as history, but every current implementation/proof claim starts again from NOT_ASSESSED and must earn current status under the new authority.**


## High-trust strategy revision synchronization — 2026-08-19

After `D-WS-20260819-05`, active mandatory/conditional inventory is `886` and remains `0` current SATISFIED / `886` NOT_ASSESSED. The verification epoch remains `WS-REBASELINE-20260819-001`; exact Work Baseline strategy/matrix fingerprints prevent pre-revision proof inheritance.


## EOD core-boundary strategy revision synchronization — 2026-08-19

`D-WS-20260819-05` previously raised the active mandatory/conditional inventory to `886`. After `D-WS-20260819-06`, the current active mandatory/conditional inventory is `927` and remains `0` current SATISFIED / `927` NOT_ASSESSED. Optional capability required units are `120` and remain `OPTIONAL_NOT_REQUESTED` unless explicitly requested. The verification epoch remains `WS-REBASELINE-20260819-001`; exact Work Baseline strategy/matrix fingerprints prevent pre-revision proof inheritance.

## Market Data fact-ownership strategy revision synchronization — 2026-08-19

After `D-WS-20260819-07`, the current active mandatory/conditional inventory is `985` and remains `0` current SATISFIED / `985` NOT_ASSESSED. Optional capability required units are `123` and remain `OPTIONAL_NOT_REQUESTED` unless explicitly requested. The verification epoch remains `WS-REBASELINE-20260819-001`; no prior implementation/evidence inherits conformance for the strengthened Market Data ownership/no-local-substitution rules.
