# Run Artifacts Format (LOCKED)

## Purpose
Define the minimum operator-facing artifact formats generated per requested trade date T so a run outcome can be audited, diagnosed, replay-verified, and compared against expectations without manual interpretation of raw tables alone.

These artifacts are operational evidence companions.
They do not replace the authoritative state stored in the main database contracts.

## Corrected-strategy binding rule (LOCKED)

Every artifact must expose the V2 semantic bindings applicable to its scope: immutable observation-manifest hash, full config snapshot ID/hash, temporal revision-set identity, factor-set ID/hash, price-product code, canonicalization/formula/registry/read-model versions, requested/effective dates, readiness/freshness states, and complete sorted reason sets. Legacy `config_version` or `config_identity` may remain as compatibility labels but never satisfies this rule by itself.

## Artifact categories
For one requested trade date T, the implementation should be able to produce artifact outputs for:
- normal readable success
- held or failed requested date
- historical correction execution
- replay mismatch or replay comparison output

## Minimum artifact set per requested-date run
At minimum, the following artifact shapes must be reconstructable:

1. `run_summary.json`
2. `publication_manifest.json` for each readable or superseded publication state being evidenced
3. `run_event_summary.json`
4. `eligibility_export.csv`
5. `invalid_bars_export.csv` or equivalent bounded sample
6. `anomaly_report.md` or equivalent machine-readable anomaly summary

Where applicable, the following should also be available:
7. `source_attempt_telemetry.json` when attempt-level source telemetry exists for the run
8. `correction_evidence.json`
9. `replay_result.json`
10. `replay_expected_state.json`
11. `replay_actual_state.json`

## 1. `run_summary.json`
### Purpose
Provide a compact authoritative run evidence summary for one requested date T.

### Minimum fields
A conforming summary should contain at minimum:

    {
      "run_id": 7001,
      "trade_date_requested": "2026-03-10",
      "trade_date_effective": "2026-03-09",
      "lifecycle_state": "COMPLETED",
      "terminal_status": "HELD",
      "quality_gate_state": "FAIL",
      "publishability_state": "NOT_READABLE",
      "stage": "FINALIZE",
      "source": "API_FREE",
      "source_context": {
        "source_name": "API_FREE",
        "source_input_file": null,
        "attempt_count": 3,
        "success_after_retry": "yes",
        "final_http_status": 200,
        "final_reason_code": null
      },
      "coverage_ratio": 0.8420,
      "bars_rows_written": 842,
      "indicators_rows_written": 830,
      "eligibility_rows_written": 1000,
      "invalid_bar_count": 18,
      "invalid_indicator_count": 170,
      "warning_count": 50,
      "hard_reject_count": 12,
      "bars_batch_hash": null,
      "indicators_batch_hash": null,
      "eligibility_batch_hash": null,
      "observation_manifest_hash": "OBS_HASH",
      "publication_manifest_hash": null,
      "sealed_at": null,
      "config_version": "cfg_2026_03",
      "config_snapshot_id": 9001,
      "config_snapshot_hash": "CONFIG_HASH",
      "temporal_revision_set_hash": "TEMPORAL_HASH",
      "factor_set_id": 7001,
      "factor_set_hash": "FACTOR_HASH",
      "price_product_code": "STRUCTURAL_ADJUSTED",
      "canonicalization_version": "canonical_v2",
      "formula_version": "weekly_swing_eod_v2",
      "read_model_version": "weekly_swing_read_v2",
      "freshness_state": "NOT_AVAILABLE",
      "publication_version": null,
      "is_current_publication": false,
      "supersedes_run_id": null,
      "started_at": "2026-03-10T15:01:00+07:00",
      "finished_at": "2026-03-10T15:09:30+07:00"
    }

### Locked rules
- summary must reflect actual persisted run outcome, not speculative operator interpretation
- if `terminal_status` is readable success, seal/publication evidence must be compatible with readability
- if requested date is held or failed, summary must not imply requested date is readable
- run-summary fields that mirror persisted run state must use the persisted names from `eod_runs`
- derived publication-facing fields may appear only when clearly marked as derived companion evidence, not as replacement names for persisted columns
- `source_context` is allowed as derived companion evidence parsed from persisted run telemetry/notes; it may recover missing minimum operator-facing fields from persisted attempt telemetry when run notes are thin, but it must not invent source facts absent from persisted run context

## 2. `publication_manifest.json`
### Purpose
Provide the compact deterministic proof object for one readable or historical publication state.

### Minimum fields
A conforming manifest should contain at minimum:

    {
      "publication_id": 1201,
      "trade_date": "2026-03-05",
      "run_id": 5009,
      "publication_version": 2,
      "is_current": true,
      "supersedes_publication_id": 1188,
      "seal_state": "SEALED",
      "sealed_at": "2026-03-06T10:15:00+07:00",
      "observation_manifest_hash": "OBS_HASH",
      "config_snapshot_id": 9002,
      "config_snapshot_hash": "CONFIG_HASH",
      "temporal_revision_set_hash": "TEMPORAL_HASH",
      "factor_set_id": 7002,
      "factor_set_hash": "FACTOR_HASH",
      "price_product_code": "STRUCTURAL_ADJUSTED",
      "canonicalization_version": "canonical_v2",
      "formula_version": "weekly_swing_eod_v2",
      "read_model_version": "weekly_swing_read_v2",
      "bars_batch_hash": "H2B",
      "indicators_batch_hash": "H2I",
      "eligibility_batch_hash": "H2E",
      "publication_manifest_hash": "MANIFEST_HASH",
      "bars_rows_written": 1000,
      "indicators_rows_written": 1000,
      "eligibility_rows_written": 1000,
      "trade_date_requested": "2026-03-05",
      "trade_date_effective": "2026-03-05",
      "readiness_state": "READABLE",
      "freshness_state": "FRESH"
    }

### Locked rules
- manifest field names must align with the publication-manifest contract, even when values are assembled from multiple persisted tables
- `publication_manifest.json` is publication-shaped evidence, not a promise that every field lives in `eod_publications` itself
- `is_current` and `supersedes_publication_id` are publication fields and must not be replaced by run-mirror names such as `is_current_publication` or `supersedes_run_id`
- a superseded publication manifest remains audit-valid and must not be retroactively rewritten to look current

## 3. `run_event_summary.json`
### Purpose
Provide a compact summary of the append-only `eod_run_events` trail for one run without replacing the underlying event log.

### Minimum fields
A conforming summary should contain at minimum:

    {
      "run_id": 8124,
      "trade_date_requested": "2026-04-21",
      "event_count": 17,
      "first_event_time": "2026-04-21T17:31:00+07:00",
      "last_event_time": "2026-04-21T17:39:18+07:00",
      "first_event_type": "RUN_CREATED",
      "last_event_type": "FINAL_STATUS_COMMITTED",
      "highest_severity": "INFO",
      "stage_counts": {
        "INGEST": 5,
        "CANONICALIZE": 4,
        "INDICATORS": 3,
        "FINALIZE": 5
      },
      "reason_code_counts": {}
    }

### Locked rules
- summary must be derivable from `eod_run_events` and must not invent event history that is absent from the append-only trail
- `first_event_type`, `last_event_type`, `highest_severity`, `stage_counts`, and `reason_code_counts` are derived summary fields, not persisted column names
- if the underlying event trail contains `ERROR` severity, the summary must not present `highest_severity` as lower than `ERROR`
- `run_event_summary.json` summarizes the event trail; it does not replace row-level inspection of `eod_run_events` when detailed diagnosis is required

## 4. `eligibility_export.csv`
### Purpose
Provide a row-level readable/blocking view for the resolved trade date D.

### Minimum columns
- `trade_date`
- stable `listing_id`
- optional compatibility/display `ticker_id` / `ticker_code`
- `publication_id`
- `data_usable`
- complete deterministic `reason_codes` / reason-set representation
- optional legacy `eligible` and primary `reason_code` projection, never as the sole V2 meaning

### Example
    trade_date,listing_id,ticker_code,publication_id,data_usable,reason_codes,eligible,reason_code
    2026-03-09,101,1,
    2026-03-09,102,0,ELIG_MISSING_BAR

### Locked rules
- export must represent one coherent publication context for D
- blocked rows must carry registered reason codes
- export must not mix current and superseded publication states

## 5. `invalid_bars_export.csv`
### Purpose
Provide row-level audit evidence for rejected source rows.

### Minimum columns
- `trade_date`
- stable `listing_id` when mapping resolved
- optional display/compatibility `ticker_id` / `ticker_code`
- immutable `source_observation_id` or observation reference/hash
- `source`
- `source_row_ref`
- `invalid_reason_code`

### Example
    trade_date,listing_id,ticker_code,source_observation_id,source,source_row_ref,invalid_reason_code
    2026-03-10,101,API_FREE,row_001,BAR_INVALID_OHLC_ORDER
    2026-03-10,205,API_FREE,row_019,BAR_NON_POSITIVE_PRICE

### Locked rules
- this export is audit-only
- invalid bars must not be confused with canonical readable bars
- bounded sampling is allowed only if the summary states that sampling was used

## 6. `anomaly_report.md`
### Purpose
Provide a short operator-facing summary of what went wrong or what materially changed.

### Minimum sections
- run identity
- requested/effective date
- dominant anomalies
- terminal status explanation
- fallback implication
- publish safety implication

### Example shape
    # Anomaly Report
    - Requested date: 2026-03-10
    - Effective date: 2026-03-09
    - Status: HELD
    - Dominant anomaly: coverage below threshold due to source timeout burst
    - Consumer effect: fallback to prior readable sealed publication
    - Publication safety: requested date not readable

### Locked rules
- anomaly report must be consistent with run summary
- narrative explanation must not contradict status/seal/publication facts


## 7. `source_attempt_telemetry.json`
### Purpose
Provide a compact bounded export of attempt-level source acquisition telemetry for one run when the append-only event trail already contains it.

### Minimum fields
A conforming source-attempt export should contain at minimum:

    {
      "event_id": 991,
      "event_time": "2026-04-21T17:04:00+07:00",
      "event_type": "STAGE_COMPLETED",
      "source_name": "API_FREE",
      "provider": "generic",
      "timeout_seconds": 15,
      "retry_max": 3,
      "attempt_count": 2,
      "success_after_retry": "yes",
      "final_http_status": 200,
      "final_reason_code": "RUN_SOURCE_TIMEOUT",
      "captured_at": "2026-04-21T17:04:00+07:00",
      "attempts": [
        {
          "attempt_number": 1,
          "reason_code": "RUN_SOURCE_TIMEOUT",
          "http_status": 504,
          "throttle_delay_ms": 1000,
          "backoff_delay_ms": 250,
          "will_retry": true
        },
        {
          "attempt_number": 2,
          "reason_code": null,
          "http_status": 200,
          "throttle_delay_ms": 1000,
          "backoff_delay_ms": 0,
          "will_retry": false
        }
      ]
    }

### Locked rules
- export must be derived from persisted `eod_run_events.event_payload_json`, not reconstructed from guesswork
- export is optional and only materialized when attempt-level telemetry actually exists in the event trail
- export may enrich missing top-level identity fields from persisted run notes, but it must not invent attempt rows that are absent from the event payload
- this artifact is a bounded operator companion; row-level `eod_run_events` remains authoritative for full forensic inspection

## 7. `correction_evidence.json`
### Purpose
Provide a compact before/after proof package for a historical correction event.

The JSON shape below is an operator-facing derived artifact.
Fields such as `prior_publication_is_current` and `new_publication_is_current` summarize publication state and are not persisted column names in `eod_publications`.

### Minimum fields
    {
      "correction_id": 9001,
      "trade_date": "2026-03-05",
      "prior_run_id": 5001,
      "new_run_id": 5009,
      "prior_publication_version": 1,
      "new_publication_version": 2,
      "old_hashes": {
        "bars_batch_hash": "H1B",
        "indicators_batch_hash": "H1I",
        "eligibility_batch_hash": "H1E"
      },
      "new_hashes": {
        "bars_batch_hash": "H2B",
        "indicators_batch_hash": "H2I",
        "eligibility_batch_hash": "H2E"
      },
      "publication_switch": true,
      "prior_publication_is_current": false,
      "new_publication_is_current": true
    }

### Locked rules
- old and new publication evidence must coexist
- unchanged rerun must not claim a publication switch
- evidence must not imply silent overwrite of prior publication

## 8. `replay_result.json`
9. `replay_expected_state.json`
10. `replay_actual_state.json`
### Purpose
Provide a machine-readable summary when replay comparison does not fully match expectation.

### Minimum fields (persisted JSON paths)

| Path | Example |
| --- | --- |
| `replay_id` | `3001` |
| `trade_date` | `"2025-12-10"` |
| `trade_date_effective` | `"2025-12-10"` |
| `status` | `"SUCCESS"` |
| `comparison_result` | `"MISMATCH"` |
| `replay_status` | `"FAIL"` |
| `comparison_note` | `"eligibility output diverged"` |
| `artifact_changed_scope` | `"eligibility_only"` |
| `replay_mode` | `"PUBLICATION_EXACT"` |
| `knowledge_cutoff_at` | `null` |
| `bound_inputs.source_observation_manifest_hash` | `"OBS_HASH"` |
| `bound_inputs.config_snapshot_id` | `9001` |
| `bound_inputs.config_snapshot_hash` | `"CONFIG_HASH"` |
| `actual_context.actual_publication_manifest_identity.temporal_revision_set_hash` | `"TEMPORAL_HASH"` |
| `actual_context.actual_publication_context.factor_set_id` | `7001` |
| `actual_context.actual_publication_context.factor_set_hash` | `"FACTOR_HASH"` |
| `actual_context.actual_publication_context.price_product_code` | `"STRUCTURAL_ADJUSTED"` |
| `actual_context.actual_publication_manifest_identity.formula_version` | `"weekly_swing_eod_v2"` |
| `bound_inputs.read_model_version` | `"weekly_swing_read_v2"` |
| `publication_version` | `1` |
| `bars_batch_hash` | `"A1"` |
| `indicators_batch_hash` | `"B1"` |
| `eligibility_batch_hash` | `"C2"` |
| `seal_state` | `"SEALED"` |
| `expected_status` | `"SUCCESS"` |
| `expected_trade_date_effective` | `"2025-12-10"` |
| `expected_seal_state` | `"SEALED"` |
| `mismatch_summary` | `"eligibility hash changed while bars hash remained unchanged"` |

### Locked rules
- Scalar mirrors of `md_replay_daily_metrics` retain exact persisted column names. Named persisted JSON members retain the paths specified here; `bound_inputs` mirrors persisted bound-input scalars, `actual_context` mirrors `actual_context_json`, and `expected_context` mirrors independently persisted `expected_context_json`. No implicit aliases between different semantic identities are allowed. This is replay artifact identity schema `replay_artifact_identity_v1`.
- replay-result status must be explicit: `PASS` for deterministic match/expected degrade, `FAIL` for mismatch/unexpected divergence, and `BLOCKED` only when replay cannot run because fixture/context/runtime prerequisites are missing
- expected comparison context must remain explicit through replay-expected fields such as `expected_status`, `expected_trade_date_effective`, and `expected_seal_state`
- mismatch summary must not replace detailed evidence, only summarize it

## 9. `replay_expected_state.json`
### Purpose
Preserve the replay fixture expectation as exported evidence, including expected hashes and expected reason-code distribution when declared.

### Minimum fields (persisted JSON paths)

| Path | Example |
| --- | --- |
| `status` | `"SUCCESS"` |
| `trade_date_effective` | `"2025-12-10"` |
| `seal_state` | `"SEALED"` |
| `replay_mode` | `"PUBLICATION_EXACT"` |
| `expected_context.expected_bound_input_context.source_observation_manifest_hash` | `"OBS_HASH"` |
| `expected_context.expected_bound_input_context.config_snapshot_hash` | `"CONFIG_HASH"` |
| `expected_context.expected_publication_manifest_identity.temporal_revision_set_hash` | `"TEMPORAL_HASH"` |
| `expected_context.expected_publication_context.factor_set_hash` | `"FACTOR_HASH"` |
| `expected_context.expected_publication_context.price_product_code` | `"STRUCTURAL_ADJUSTED"` |
| `expected_context.expected_publication_manifest_identity.formula_version` | `"weekly_swing_eod_v2"` |
| `expected_context.expected_bound_input_context.read_model_version` | `"weekly_swing_read_v2"` |
| `publication_version` | `1` |
| `bars_batch_hash` | `"A1"` |
| `indicators_batch_hash` | `"B1"` |
| `eligibility_batch_hash` | `"C1"` |
| `reason_code_counts` | `[]` |

## 10. `replay_actual_state.json`
### Purpose
Preserve the actual replay proof state exported from `md_replay_daily_metrics` and `md_replay_reason_code_counts`.

### Minimum fields (persisted JSON paths)

| Path | Example |
| --- | --- |
| `status` | `"SUCCESS"` |
| `trade_date_effective` | `"2025-12-10"` |
| `seal_state` | `"SEALED"` |
| `replay_mode` | `"PUBLICATION_EXACT"` |
| `actual_context.actual_bound_input_context.source_observation_manifest_hash` | `"OBS_HASH"` |
| `actual_context.actual_bound_input_context.config_snapshot_hash` | `"CONFIG_HASH"` |
| `actual_context.actual_publication_manifest_identity.temporal_revision_set_hash` | `"TEMPORAL_HASH"` |
| `actual_context.actual_publication_context.factor_set_hash` | `"FACTOR_HASH"` |
| `actual_context.actual_publication_context.price_product_code` | `"STRUCTURAL_ADJUSTED"` |
| `actual_context.actual_publication_manifest_identity.formula_version` | `"weekly_swing_eod_v2"` |
| `actual_context.actual_bound_input_context.read_model_version` | `"weekly_swing_read_v2"` |
| `publication_version` | `1` |
| `bars_batch_hash` | `"A1"` |
| `indicators_batch_hash` | `"B1"` |
| `eligibility_batch_hash` | `"C1"` |
| `reason_code_counts` | `[]` |

## Replay identity applicability and retention (LOCKED)

These rules qualify the minimum paths in sections 8–10; a nested path is a JSON path, never a dotted literal property or a top-level alias. Expected and actual paths are independent. Existing status, effective date, seal state, publication version, batch-hash and reason-count scalar paths are unchanged.

`knowledge_cutoff_at` is explicitly NULL for PUBLICATION_EXACT and is the normalized declared cutoff for AS_KNOWN. Observation identity comes from the verified publication's V2 semantic lineage member or retained V1 run observation manifest in PUBLICATION_EXACT, and selected immutable snapshot observations in AS_KNOWN. Config snapshot ID/hash must identify the same retained or cutoff-resolved snapshot; an unavailable marker or a configuration reference string is not a content digest. Read-model identity comes from the verified frozen registry capture or AS_KNOWN snapshot, never the current code/default.

At replay production time, capture `actual_context.actual_publication_manifest_identity` from the manifest of the explicitly selected, verified publication, including superseded publications. Persist it inside `actual_context_json` before export. The envelope has `schema_version = replay_publication_manifest_identity_v1`, `applicability`, `publication_id`, `publication_manifest_hash`, `temporal_revision_set_hash` and `formula_version`. Its publication ID and verified manifest hash bind both captured members to that exact publication. Do not query live/current state during export to repair missing old data, backfill legacy rows, recompute missing members or substitute current publication pointers.

For applicable PUBLICATION_EXACT analytical publications (`STRUCTURAL_ADJUSTED` or `TOTAL_RETURN`), applicability is `APPLICABLE`; require a valid nonempty publication binding, temporal revision hash and formula version, plus the independently retained publication context's factor-set ID/hash and price-product code/version. For AS_KNOWN CANONICAL_RAW execution publication-only analytical identities are `NOT_APPLICABLE` with explicit NULL members and no fabricated publication identity. A missing/malformed applicable member is `UNAVAILABLE`, not `NOT_APPLICABLE`, and cannot support non-BLOCKED identity proof. For PUBLICATION_EXACT RAW, immutable RAW input observations alone do not establish a sealed, verified RAW publication. Applicable RAW publication identity must come from an existing supported, selected immutable publication whose binding is verified. Unsupported or unavailable mandatory identity is UNAVAILABLE/BLOCKED, never PASS or NOT_APPLICABLE. Analytical-only members are NOT_APPLICABLE only where the governing frozen RAW contract establishes that applicability, never merely because values are absent. Event-factor replay binding remains distinct from analytical factor-set identity. Expected context remains independently derived from the versioned fixture. This correction creates no RAW publication capability.

`temporal_revision_set_hash` is the selected publication manifest's temporal revision-set identity, not replay `temporal_identity_hash`. `factor_set_hash` is the publication's factor identity, not composite `event_factor_hash`; `formula_version` is a version label, not `formula_registry_hash`. Keep every existing identity independently visible. Product version is retained beside product code; no live default may supply it.

`replay_expected_state.json` retains fixture ID/version/schema and independently authored `expected_context`; its expected publication envelope is `expected_context.expected_publication_manifest_identity` with the same explicit schema and applicability vocabulary, derived from versioned fixture inputs before target execution. Missing expectations remain unavailable; they must not be reconstructed from actual context. `replay_actual_state.json` retains the persisted actual envelope. The state artifacts must expose their exact mode as `replay_mode`; the expected mode is independently declared and persisted in `expected_context.expected_replay_mode`, not copied from actual mode. Declared reason distributions retain their own expected/actual counts, never a success-only substitute. Matching NULLs cannot establish an applicable identity.

File and pack hashes bind the changed serialized artifacts. Existing publication manifests, canonical output hashes, historical replay rows, immutable evidence and approved fixture versions are not rewritten. A changed expected fixture requires a new version and its applicable independent review/admission; candidate-v5 remains immutable.

## Artifact timestamp rule
All operator-facing artifact timestamps must be represented consistently in platform timezone or in an explicitly stated alternative representation.
Timestamp inconsistency across artifact families is forbidden.

## Artifact secrecy rule
Artifacts must not expose:
- credentials
- access tokens
- secrets
- internal-only sensitive tokens unrelated to audit/proof

## Cross-contract alignment
These formats must remain aligned with:
- `Audit_Evidence_Pack_Contract_LOCKED.md`
- `Failure_Playbook_LOCKED.md`
- `../book/Historical_Correction_and_Reseal_Contract_LOCKED.md`
- replay contracts
- publication/readiness contracts

## Anti-ambiguity rule (LOCKED)
If two artifacts for the same requested date tell conflicting stories about status, seal state, or publication resolution, the artifact set is invalid and must be corrected.

## See also
- `Audit_Evidence_Pack_Contract_LOCKED.md`
- `Failure_Playbook_LOCKED.md`
- `Historical_Correction_Runbook_LOCKED.md`
- `../backtest/Historical_Replay_and_Data_Quality_Backtest.md`

---

## Amendment 2026-05-26 - Import mutation and impact fields

Run summary, lifecycle summary, and evidence run-summary artifacts may include these impact blocks:

```json
{
  "bar_mutation_summary": {
    "changed_bar_count": 0,
    "inserted_bar_count": 0,
    "updated_bar_count": 0,
    "unchanged_bar_count": 0,
    "removed_bar_count": 0
  },
  "indicator_impact_summary": {
    "affected_ticker_count": 0,
    "affected_trade_date_count": 0,
    "affected_start_date": null,
    "affected_end_date": null,
    "max_dependency_trading_days": 50,
    "indicator_reprocess_state": "NOOP_UNCHANGED_BARS"
  },
  "publication_impact_summary": {
    "readable_publication_impacted": false,
    "republication_required": false,
    "publication_impact_state": "NOOP",
    "reason_code": null
  }
}
```

Artifacts must not store raw provider payloads to support these summaries. If `publication_impact_state=REQUIRES_REPUBLICATION`, replay verification for a replacement publication is only valid after the correction/republication lifecycle produces a sealed readable replacement.

---

## Amendment 2026-05-27 - Impact execution artifact fields

Run summary, lifecycle summary, and evidence artifacts may include:

```json
{
  "indicator_reprocess_execution_summary": {
    "execution_state": "NOOP",
    "reprocessed_trade_date_count": 0,
    "reprocessed_trade_dates": [],
    "reprocess_scope": "NONE",
    "blocked_reason_code": null,
    "failure_reason_code": null
  },
  "eligibility_reprocess_execution_summary": {
    "execution_state": "NOOP",
    "reprocessed_trade_date_count": 0,
    "reprocessed_trade_dates": [],
    "blocked_reason_code": null,
    "failure_reason_code": null
  },
  "publication_reprocess_summary": {
    "execution_state": "NOOP",
    "republished_trade_date_count": 0,
    "blocked_trade_dates": [],
    "blocked_reason_code": null
  },
  "resume_recovered_apply_summary": {
    "recovered_row_count": 0,
    "apply_state": "NOOP"
  }
}
```

These are bounded summaries. They must not include raw provider payloads, credentials, or full checkpoint bodies.

---

## Amendment 2026-05-27 - Publication reprocess execution fields

When affected non-readable downstream dates are promoted through the lifecycle/full-publish reprocess path, artifacts may include:

```json
{
  "publication_reprocess_summary": {
    "execution_state": "REPUBLISHED",
    "republished_trade_date_count": 1,
    "republished_trade_dates": ["2026-05-08"],
    "candidate_trade_dates": ["2026-05-08"],
    "blocked_trade_dates": [],
    "failed_trade_dates": [],
    "blocked_reason_code": null,
    "failure_reason_code": null,
    "evidence_exported_count": 1,
    "fixtures_generated_count": 1,
    "replay_verified_count": 1,
    "republication_mode": "AUTOMATED_NON_READABLE_DATES",
    "correction_ids": [],
    "correction_id": null
  }
}
```

`REPUBLISHED` here is valid only after the existing promote flow or correction-current flow produces sealed/readable replacement publications. Already-readable affected dates must carry `AUTOMATED_READABLE_CORRECTION` or `AUTOMATED_MIXED_IMPACT_REPUBLICATION` plus correction id fields; otherwise they remain blocked/failed without pointer mutation.


## Amendment 2026-05-27 - Final publication reprocess execution proof fields

Publication reprocess artifacts may now represent automated correction-current republication for already-readable affected dates when the correction lifecycle completes successfully. The artifact must keep the distinction between:

- `AUTOMATED_NON_READABLE_DATES` for affected downstream dates promoted through normal lifecycle because they were not already readable/current;
- `AUTOMATED_READABLE_CORRECTION` for already-readable affected dates promoted through correction-current mode;
- `BLOCKED_REQUIRES_CORRECTION` when the correction-current path cannot complete safely.

Final runtime proof: `BackfillLifecyclePublicationReprocess` OK (4 tests, 19 assertions), `OutOfOrderImportImpact` OK (7 tests, 107 assertions), `Backfill` OK (49 tests, 339 assertions), and full MarketData suite OK (585 tests, 8713 assertions).
