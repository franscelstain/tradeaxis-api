# Finding — `F-MD-B19-A001-011`

- ID: `F-MD-B19-A001-011`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-10T15:20:06+07:00
- Severity: `P2`
- Status: `OPEN — PROJECT OWNER AUTHORITY DECISION REQUIRED`
- Class: `LOCKED_ARTIFACT_FIELD_NAME_VERSUS_PERSISTED_REPLAY_IDENTITY_CONFLICT`
- Affected: `MD-S075-R0240`–`R0249` and `R0259` in `artifact_replay_result`; no basis admitted for these 11 predicates.

## Statement

`Run_Artifacts_Format_LOCKED.md` section 8 lists top-level replay-result minimum fields including `knowledge_cutoff`, `observation_manifest_hash`, `temporal_revision_set_hash`, `factor_set_id`, `factor_set_hash`, `price_product_code`, and `formula_version`. Its locked rule also requires fields mirroring `md_replay_daily_metrics` to use the **persisted replay names**. The metric and current exporter instead use `knowledge_cutoff_at`, `source_observation_manifest_hash`, `temporal_identity_hash`, `event_factor_hash` and nested `bound_inputs`; several listed fields have no same-name persisted metric column. The current `replay_result.json` therefore lacks multiple literal top-level minimum names, while adding aliases without a binding interpretation could violate the persisted-name rule or invent factor/product semantics.

The 32-row preflight in the governed raw evidence for `E-MD-B19-A001-017` names each affected predicate, source, consumer and gap. This finding does not invalidate the independently established exact-row identity and stored-field export bases. It prevents admitting the eleven field-name/identity rules by equating different names, matching NULLs, or copying values from a self-generated fixture.

## Decision required

The Project Owner should choose the binding section 8 field mapping: either approve an explicit semantic mapping and top-level projection for every locked minimum field, including how the persisted-name rule applies, or revise the locked artifact contract to use the persisted replay field names and actual nested structure. For factor/product fields without a persisted metric source, identify the authoritative bound source and its required provenance. After the decision, implement only the necessary projection/persistence changes and prove each affected predicate with independent expected values and mutations. No strategy or production semantic change is made by this finding.

## Related

`E-MD-B19-A001-017`, `CI-MD-B19-A001-001`, `F-MD-B19-A001-010`, `MD-DEP-0025`. `R0260` and `R0261` remain separate semantic proof gaps, not owner decisions made here.
