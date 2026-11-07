# Decision — D-MD-B19-A001-012

- ID: `D-MD-B19-A001-012`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Status: `APPROVED`
- Source: actual Project Owner answer “OWNER DECISION — E049 / MD-S064-R0011 PRE-APPLY DISCLOSURE” in the current conversation; transcribed at `storage/app/market-data/evidence/MD-B19-A001/E049_OWNER_R0011_PREAPPLY_CLARIFICATION_20261106.md`, SHA-256 `53b6947372be7099283928f63eb5cad72acec9cb212e5adcc247dce6337eeb56`. Recorded by Codex without invented signature or reviewer credential.

## Approved bounded interpretation

1. For a command that allocates a new run/configuration snapshot only during execution, disclose already resolved immutable correction/baseline IDs before apply. Display the new IDs as `PENDING_ALLOCATION`, explicitly **not** as resolved immutable IDs. Display the candidate configuration content hash, source identity, scope, expected row/artifact effects, pointer impact and reasons.
2. Before the first mutation, revalidate configuration, source, target and authorization transactionally against the exact disclosure. Allocation and later writes remain bound to that validation. A mismatch refuses the operation and rolls back without partial persisted effects. The actual issued IDs are reported in outcome and auditable evidence.
3. For invalid-pointer cleanup, `HISTORICAL_CONFIG_HASH_UNAVAILABLE` is permitted only if an authentic historical run/snapshot cannot be identified. Give the reason and separately disclose the prospective recovery configuration hash. Never represent the recovery hash as the historical publication hash. Fail closed when cleanup target, required source identity, pointer condition or recovery authority cannot be verified.
4. This is a narrow clarification for the affected pre-apply paths. It does not grant operator pointer authority, change sealed history, relax the requested-date fence or scan-to-write revalidation, or extend an exception to every command.

## Authority and status

This applies `MD-S064-R0011` under `Commands_and_Runbook_LOCKED.md` and the existing D010 recovery direction. It changes no LOCKED source bytes or strategy meaning beyond this expressly approved bounded interpretation. The source-bound MariaDB A→B→A correction execution passes its historical-publication assertions; a separate output audit finds missing pre-apply fields for both successful corrections. Neither author execution nor this decision registers a new predicate basis or independent admission. R0011 remains INCOMPLETE pending all applicable command paths and proof. B19 accounting remains 669 independently accepted / 678 repository proposed / 72 without basis / formal SATISFIED 0. Four E049 dependency HOLDs, R0004, F03/F018, MD-DEP-0025, operations FAIL and the inherited consumer FAIL remain open. No deployment, activation or stage closure is authorized.

## Correlation

- Decision request: `storage/app/market-data/evidence/MD-B19-A001/E049_R0011_PREAPPLY_AUTHORITY_DECISION_REQUEST_20261106.md` (pre-approval author finding; retain its original limitations).
- Current source-bound command inventory: `storage/app/market-data/evidence/MD-B19-A001/E049_R0011_COMMAND_DISCLOSURE_INVENTORY_20261106.json` and `E049_R0011_BOUNDARY_GROUPS_20261106.json`.
- Persisted output RED: `storage/app/market-data/evidence/MD-B19-A001/E049_R0011_PREAPPLY_PERSISTED_RED_20261106.json`.
- Existing impact declaration: `CI-MD-B19-A001-001`.
