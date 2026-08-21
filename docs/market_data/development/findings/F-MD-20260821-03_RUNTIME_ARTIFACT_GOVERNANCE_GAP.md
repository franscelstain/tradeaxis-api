# Finding — Runtime Artifact / Governed Evidence Boundary Gap

- ID: `F-MD-20260821-03`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Severity: `P1`
- State: `CLOSED`
- Scope: documentation/governance only
- Strategy impact: none

## Finding

The current architecture correctly separates `authority → development → records`, and current implementation proof criteria already mention evidence artifact paths/hashes. However, current governance did not explicitly own the operational boundary between governed evidence records and raw application `storage/**` artifacts.

The missing authority-level rules were:

- canonical start/resume must be docs/records-first rather than storage-first;
- when storage inspection is mandatory versus unnecessary;
- raw artifacts are not independent current truth;
- current proof requires Stage/Attempt/Baseline/Epoch correlation plus artifact linkage/integrity when external raw artifacts are material;
- missing/hash-mismatched artifacts invalidate the affected execution proof without rewriting immutable evidence;
- large/generated runtime output should remain outside docs.

A current supporting guide, `development/implementation/guides/system/SYSTEM_READ_ORDER.md`, also still described a pre-normalization read order, creating avoidable navigation ambiguity.

## Resolution

Closed by `DOC-CHG-20260821-003` and `D-MD-20260821-03`.

A dedicated governance standard now owns the runtime-artifact/governed-evidence boundary, and directly impacted navigation/proof guidance is aligned to it.

No strategy document or strategy semantic was changed.
