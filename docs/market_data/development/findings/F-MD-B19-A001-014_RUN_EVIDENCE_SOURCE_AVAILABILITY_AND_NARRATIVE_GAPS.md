# Finding — F-MD-B19-A001-014

- ID: `F-MD-B19-A001-014`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-19T09:32:16+07:00
- Severity: `P2`
- Status: `RESOLVED`
- Class: `RUN_EVIDENCE_SOURCE_AVAILABILITY_AND_NARRATIVE_GAPS`

## Demonstrated gaps

Summary-only payload materialized an attempt artifact; latest unrelated stage could replace acquisition evidence. Invalid rows became invented empty attempts, explicit no became yes, unknown capture time used event time, total count was inferred from bounded rows and invalid calendar timestamps normalized. Yahoo aggregates discarded the actual request capture. Missing/malformed/contradictory source identities or missing event trail could coexist with complete binding admission. Current-pointer lookup was skipped when a historical publication was selected, hiding the actual current publication. Anomaly report omitted run identity and explicit status explanation; fallback/historical safety needed factual availability wording.

## Correction and proof

E023 records clean assertion reds/greens,51 new mutation probes with exact source-byte restoration, real source/pipeline/MariaDB persistence/export, separately authored source literals and immutable accepted semantic expectations. Current acquisition stage INGEST_BARS and retained IMPORT stage are scoped to own run; full trail is preserved. Optional artifact requires actual rows; malformed rows are not fabricated. Typed/availability metadata and explicit enrichment provenance keep unavailable facts incomplete. Configured logical run source and resolved provider source labels remain distinct. Actual selected superseded publication and current pointer are independently exposed; no historical rewrite. Auth error persists FAILED and propagates, transient timeout remains HELD per actual producer policy.

An initial compatibility regression on nullable legacy telemetry was caught by existing guards and corrected; setup/oracle-path/jitter/status-expectation errors remain diagnostic, never claimed production defects. F010/F011/F013 and D005-D007 retained; no authority revision, B18 reopening, candidate-v5 change or candidate-v6. MD-DEP-0025 remains ACTIVE.
