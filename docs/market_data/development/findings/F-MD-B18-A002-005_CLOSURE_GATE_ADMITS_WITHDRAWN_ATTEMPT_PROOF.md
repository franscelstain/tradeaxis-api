# Finding — `F-MD-B18-A002-005`

- ID: `F-MD-B18-A002-005`
- Raised by: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Owner stage: **`MD-B18`**
- Raised at: 2026-09-13T10:25:23+07:00
- Severity: `P1`
- Status: `RESOLVED` — 2026-10-08 by `E-MD-B18-A002-122` (A002 proof pack `E-MD-B18-A002-001` exists; identity checks mutation-proven). Earlier status: `OPEN — IMPLEMENTATION CORRECTED, FAIL-CLOSED PROBE AND CURRENT A002 ARTIFACT/EVIDENCE STILL REQUIRED`
- Class: `CROSS_ATTEMPT_PROOF_ADMISSION`
- Blocks: `MD-B18-A002` closure
- Blocks strategy change: `NO`

## Statement

`MarketDataReplayVerificationClosureGate` identifies itself as attempt `MD-B18-A002` but retained
`E-MD-B18-A001-001` and `storage/app/market-data/evidence/MD-B18-A001` as its governed evidence and
raw-artifact constants. On 2026-09-13 the gate therefore reported both `raw_artifact_integrity` and
`governed_evidence_reachable` as met using the withdrawn predecessor attempt.

That result violates `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` §§6, 7, and 10 and the
no-inheritance rule of `F-MD-B19-A001-002`: A001 proof cannot satisfy A002 closure merely because
its files remain readable and internally hash-consistent.

## Observed reproduction

- command: `php docs/market_data/development/implementation/tests/MarketDataReplayVerificationClosureGate.php`
- gate identity: `attempt_id=MD-B18-A002`
- admitted evidence: `evidence_id=E-MD-B18-A001-001`
- `raw_artifact_integrity.met=true`, six A001 artifacts
- `governed_evidence_reachable.met=true`, linked to the A001 manifest

## Correction in progress

The gate now points only at prospective `E-MD-B18-A002-001` and the `MD-B18-A002` artifact
directory. Admission additionally verifies Stage/Attempt/Baseline/Epoch/verdict identity, the
manifest identity, artifact-path containment, and the evidence-recorded manifest hash.

The finding remains open. It closes only after the A002 proof pack exists and the identity checks
are mutation-proven with green controls before and after; a currently missing A002 pack is the
correct fail-closed pre-evidence state, not proof of the final positive branch.

## 2026-10-08 resolution — E-MD-B18-A002-122

The A002 proof pack exists (`E-MD-B18-A002-001`, manifest with 8 artifacts) and the closure gate PASSes on it. The identity checks were mutation-proven with green controls before and after every probe: 21 data scenarios (manifest and evidence Stage/Attempt/Baseline/Epoch/verdict identity, artifact count, path containment, missing and altered artifacts, missing manifest, absent and duplicate evidence, recorded manifest path and hash, and the withdrawn A001 pack substituted), 17 single-protection removals from the gate source (the corrupted pack was then not detected; the gate was restored byte-identical every time) and 9 scenarios for the matrix and proof-basis conditions. No gap was found and the gate was not changed.
