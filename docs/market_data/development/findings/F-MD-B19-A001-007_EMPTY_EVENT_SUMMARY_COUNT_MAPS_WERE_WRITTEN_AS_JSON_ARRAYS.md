# Finding — `F-MD-B19-A001-007`

- ID: `F-MD-B19-A001-007`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-09T00:48:22+07:00
- Severity: `P3`
- Status: `RESOLVED`
- Class: `ARTIFACT_CONTRACT_NOT_SATISFIED`
- Blocked: nothing beyond the `artifact_run_event_summary` family (18 predicates), proven in the attempt that
  raised it.
- Blocks strategy change: `NO`

## Statement

`run_event_summary.json` (`MD-S075` section 3) shows `"reason_code_counts": {}` in its locked minimum shape and
names `stage_counts` and `reason_code_counts` as keyed maps. The exporter wrote an **empty** map as a JSON
**array** (`[]`): `json_encode` of an empty PHP array is `[]`, and `exportRunEvidence()` handed the repository's
plain arrays to `writeJson()` unchanged.

A run whose events carry no reason code is the ordinary case, so the artifact for it contradicted the shape its own
contract shows, and a consumer reading the field as an object (a map from name to count) received a list.

Measured by exporting, through the real `MarketDataEvidenceExportService`, a run whose three events carry no
reason code, and a run with no events at all, and decoding the written file *without* the associative flag:
both maps decoded as arrays.

## Remediation

`exportRunEvidence()` converts `stage_counts` and `reason_code_counts` to objects after the dominant reason codes
have been derived from them, and before the summary is written and embedded in `evidence_pack.json`. The
repository's `summarizeRunEvents()` is unchanged and still returns plain arrays, and nothing else reads the
exported object (the method's return value does not carry the summary). Non-empty maps are byte-identical.

## Proof

`B19RunEventSummaryRealRunProvenanceTest::test_a_trail_without_reason_codes_writes_an_empty_json_object` and
`test_a_run_with_no_events_writes_no_invented_history` were run on a real MariaDB world **before** the change
(both red: `reason_code_counts must serialize as {} when empty`) and **after** it (green).
`X03` removes the conversion again and turns both red.

## Not decided here

Whether `highest_severity` of a run with **no** events should be `INFO` (what the repository reports) or absent is
not stated by `MD-S075`; the guards do not assert it either way. The timestamps are exported as the stored
database values (platform time zone, no offset) where the contract example shows an ISO form with an offset; that
is a format, not one of the 18 predicates, and the same convention as the other artifacts.
