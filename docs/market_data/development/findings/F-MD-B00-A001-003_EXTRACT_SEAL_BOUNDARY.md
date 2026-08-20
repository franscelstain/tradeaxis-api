# F-MD-B00-A001-003 — Legacy extract seal does not cover content appended after the body-end marker

- Status: `OPEN`
- Severity: `P3`
- Stage / Attempt / Baseline / Epoch: `MD-B00` / `MD-B00-A001` / `MD-B00-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: `MD-B00`
- Artifact: `development/implementation/tests/MarketDataDocumentationIntegrityGate.php`, check `LEGACY_SEMANTIC_SPLIT_INTEGRITY`

## Finding

The check hashes only the region between `LEGACY_EXTRACT_BODY_START` and `LEGACY_EXTRACT_BODY_END`. Text inserted inside that region is caught: `MD-B00-A001` verified this, and the gate reported both a body hash error and a reconstruction hash error for `LS-MD-0003`.

Text appended after the body-end marker is not caught. The same test with the insertion moved past the marker returned exit 0 and no failing check.

## Assessment

Restricting the hash to the extracted source range is defensible and probably intentional — the seal exists to prove the split reconstructs the original source exactly, and trailing editorial matter is outside the original range by construction.

The residual risk is narrow but real. `DOCUMENT_RECORDING_STANDARD.md` states that historical records are never rewritten to look current. Appending an unsealed paragraph to a `HISTORICAL_ONLY` extract is precisely that rewrite, and today it passes every gate. No occurrence exists at this baseline; all 43 split sources reconstruct exactly.

## Required outcome

Low priority, and acceptable to defer with the risk recorded rather than fixed. If addressed, the cheapest form is a structural assertion that an extract file contains nothing but its header, the sealed body, and an optional trailing newline — not an extension of the hash, which would break the reconstruction proof.
