# Finding — F-MD-B19-A001-013

- ID: `F-MD-B19-A001-013`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-19T01:10:08+07:00
- Severity: `P2`
- Status: `RESOLVED`
- Class: `REPLAY_IDENTITY_ADMISSION_AND_SHARED_ARTIFACT_BOUNDARIES`

## Demonstrated defects and bounded correction

Historical persisted MATCH/PASS without schema/envelopes could receive complete admission. Preserve comparison/readability, separately report identity UNAVAILABLE and admission INCOMPLETE. Independent expected coverage expected-count was omitted; use its own persisted context only, malformed values refuse, absent values incomplete. FAIL without mismatch diagnostics is incomplete. Structured/string credentials/tokens are refused at shared JSON/CSV/Markdown sinks; do not expose offending values in diagnostics. Stale/mixed publication outputs and different-byte rewrites are refused; exact idempotent bytes remain readable.

D007 authorizes companion implementation of existing semantic exposure: exact member hashes, explicit timestamp context, self-contained authoritative bindings and applicability. Capture replay actual snapshot before persistence; independently versioned expected snapshot. V2 semantic observation/factor members are distinct from retained allocation-bound manifest values. Verified publication digest required; no live replay repair, historical backfill or actual-to-expected defaults.

## Executed proof / limits

E022 contains genuine pre-correction identity/count/secret/stale/diagnostic assertion reds, focused greens, twenty fast and two real MariaDB mutant assertion reds with exact byte restoration, real source/persistence/export and correction lifecycle. Setup errors and two initial mixed error/failed assertions retained diagnostic only; corrected clean probes accepted. No new golden candidate or B18 reopening. Existing F010/F011 remain resolved, F003/MD-DEP-0025 remain open/ACTIVE.
