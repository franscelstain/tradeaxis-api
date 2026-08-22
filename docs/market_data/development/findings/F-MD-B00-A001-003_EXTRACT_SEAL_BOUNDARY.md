# F-MD-B00-A001-003 — Legacy extract seal does not cover content appended after the body-end marker

- Status: `CLOSED`
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

## Closed — MD-B00-A003

Fixed, in the cheapest form this finding prescribed: a structural assertion, not a wider hash.

The hole was reproduced before it was closed, because a finding that describes a gap is not the same as a gap that still exists. Against `LX-MD-0003-CTX-01`, with the check isolated from unrelated gate state: text inserted **inside** the sealed body produced `FAIL errors=2`; the same text appended **after** `LEGACY_EXTRACT_BODY_END` produced `PASS errors=0`. Exactly as recorded at `MD-B00-A001`.

`MarketDataDocumentationIntegrityGate` now carries a second check, `LEGACY_EXTRACT_STRUCTURE`, over every extract the split index references — **428 across all three legacy directories**, not only the 294 under `records/history/archive/semantic/`. For each it asserts exactly one body-start and one body-end marker, that nothing but an optional single newline follows the body-end marker, and that the file still opens with its `# Legacy Semantic Extract` header. The header assertion is the positive locator: a file that lost both its markers and its header would otherwise pass by having nothing left to check.

The reconstruction proof is untouched. The body hash still covers exactly the original source range, which is what makes the 43-source reconstruction meaningful; extending it would have broken that, as this finding warned.

Three mutations, each verified as landed, each turning the new check red: text appended after the sealed body, a duplicated body-end marker, and a renamed header. All restored; the check returns `PASS` over 428 extracts.

### Why it was fixed rather than deferred again

The deferral was legitimate — `MD-B00-A002` closed with the risk declared, not concealed. What changed is availability, not severity: `MD-B01` and `MD-B03` are both held by governance decisions with no implementation remediation path, and this was a governed `OPEN` finding whose remediation was already specified. Leaving a known hole open while there was nothing else to build would have been a choice, not a constraint.

A population floor was added with the check: the scan fails if it reaches fewer than 400 extracts. The finding's risk was that an unsealed edit passes silently; a scan that silently reached nothing would reproduce that risk in a new place.
