# Decision — V2 revision knowledge time and publication-scope manifest reason set

- ID: `D-MD-B10-A002-003`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Work / Baseline: `MD-B10` / `MD-B10-A002` / `MD-B10-A002` / `MD-B10-A002-BL001`
- Finding / CI / dependencies: `F-MD-B18-A002-023` / `CI-MD-B10-A002-001` / `MD-DEP-0021`, `MD-DEP-0020`
- Supporting evidence: `E-MD-B10-A002-001` (its predicate review names the `nested_factor`, `nested_temporal` and `manifest_reasons` defects this decision disambiguates); read-only authority reconstruction presented in this attempt before this record (no evidence issued, as for `D-MD-B18-A002-008`)
- Rules whose membership this decision disambiguates: `MD-S005-R0024`, `MD-S005-R0043`, `MD-S005-R0069` (factor set and its nested source-scale and factor-decision sets), `MD-S005-R0068` (temporal revision sets), `MD-S005-R0071` (manifest reasons)
- Issued: `2026-10-01T06:47:04+07:00`
- Decision status: `APPROVED` — owner decision received in this attempt on 2026-10-01 ("I approve the following bounded semantic decisions.")
- Strategy impact: `NONE` — no strategy byte changes; freeze `MD-STRATEGY-FREEZE-20260925-001` unchanged
- Governance impact: `NONE` — no `CONTROLLED_REVISION` document changes; no `DOCUMENT_CHANGE_LOG.md` entry

## Questions

The remaining B10 semantic producers could not be implemented from authority alone.

1. **Knowledge time.** When no source-scale assessment is visible at a run's knowledge cutoff, the run derives one itself. Its `recorded_at` is the wall clock of that run, later than the run's cutoff. Authority defines `recorded_at` as the time the platform learned a fact (`Point_In_Time_Backtest_Input_Contract_LOCKED.md:38`) and uses it for as-known selection (`Replay_Verification_Contract_LOCKED.md:15`, `:54`). It also excludes execution timestamps from artifact hashes unless they alter semantics (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:46`, `:133`). No authority classifies the assessment as a learned fact or as a run output. No authority states whether a source fact's knowledge time is a member of a revision-set hash or only its as-known selection coordinate.
2. **Manifest reasons.** `Audit_Hash_and_Reproducibility_Contract_LOCKED.md:114` requires `publication_manifest_hash` to bind "sorted reasons", and `E-MD-B10-A002-001` (`MD-S005-R0071`) requires a separate frozen semantic reason binding. Neither says whether the set holds the publication's own reasons or every reason the publication carries.

## Authority review

1. `Reason_Codes_Registry.md:118-121` is the only authority that names the source-scale assessment. `Price_Adjustment_Contract_LOCKED.md:41-49` requires an applied factor revision to bind a "created/known timestamp". That is a recording duty, not a hash-membership rule.
2. Runtime behaviour is split, and it is evidence rather than authority. `AdjustmentFactorSetService::latestAssessment` filters `recorded_at <= knowledge_cutoff_at`. `recordUnknownAssessment` reuses a row by `assessment_uid` without that filter, and `ProducerEventFactorCapture` verifies the fallback the same way.
3. Two authority precedents point in opposite directions. Observations carry acquisition timestamps in their identity (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:99`, `:101`). A configuration snapshot keeps its recorded/known timestamps beside its content hash (`Platform_Config_Registry_LOCKED.md:15`, `:18`), and only the content hash enters artifact identity (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:42`).
4. For reasons, authority settles the exclusions (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:117`, `:128-136`; `Downstream_Data_Readiness_Guarantee_LOCKED.md:5`, `:54`), set semantics (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:30`, `:151`; `Run_Artifacts_Format_LOCKED.md:11`) and the reason families already bound in nested or artifact identities (`Audit_Hash_and_Reproducibility_Contract_LOCKED.md:62`, `:78`, `:92`, `:99`, `:115`). The manifest field lists carry no reason field (`Publication_Manifest_Contract_LOCKED.md:24-58`; `Run_Artifacts_Format_LOCKED.md:106-139`).
5. No contradiction was found. The texts are silent on these members, not opposed.

## Decision

### 1A — Platform-created records

For platform-created source-scale assessments, factor sets and factor decisions:

- `recorded_at` is provenance and visibility time. It is not semantic content identity.
- It must not change the V2 semantic content hash solely because wall-clock execution time differs.
- It still controls when later runs may observe the record.
- This does not authorize bypassing knowledge cutoffs.

If an existing fallback lets a later run consume such a row before its `recorded_at`, that behaviour is not normalized into the V2 contract. Its actual affected scope is recorded separately according to governance.

### 1B — External and source-fact revisions

Where current authority defines knowledge time for a source fact, knowledge/recorded time is part of the canonical semantic revision tuple. V2 revision-set hashing preserves:

- stable entity and content identity;
- effective-time semantics;
- knowledge/recorded-time semantics where governed.

Knowledge time is not the entity root ID. It is part of the temporal revision semantics that as-known reproducibility requires.

### 2 — Publication-scope manifest reason set

The publication manifest binds a frozen, explicitly deduplicated, canonically sorted set. It contains only reasons whose semantic ownership is the publication manifest itself. Reasons already bound through nested or artifact hashes are not duplicated.

| Disposition | Members |
|---|---|
| Include | coverage reason; future registered reasons explicitly governed as publication-level semantic reasons |
| Conditionally include | date/publication-level anomaly findings, only after they have registered codes and authority explicitly makes them publication-level semantic reasons |
| Do not duplicate | market-structure reasons (their semantic revision set); factor-decision and source-scale reasons (factor and revision semantic hashes); observation outcome reasons (observation-manifest identity); correction reasons (`correction_semantic_hash`); bars, indicators and eligibility reasons (their V2 artifact hashes) |
| Exclude | `final_reason_code` and readiness terminal execution codes where provenance-only; read-time fallback reasons; seal-verification reasons; pointer-integrity reasons; undefined freshness reasons; unregistered anomaly codes |

Reason order is not semantic. Duplicate elimination and canonical deterministic sorting are mandatory.

## Boundary

- Implementation continues in the same `MD-B10-A002` / `MD-B10-A002-BL001` / `CI-MD-B10-A002-001`. No new attempt or baseline.
- Existing V1 hashes and columns keep their meaning for every current consumer. V2 semantic identities are additive.
- All 56 affected B10 predicates remain `NOT_ASSESSED`. This decision proves nothing and promotes nothing.
- `MD-B18` / `MD-S003-R0025` and `F-MD-B18-A002-018` are not resumed. `MD-DEP-0021`, `MD-DEP-0020` and `MD-DEP-0019` remain `BLOCKING`.
- No reason code is registered, and no anomaly finding becomes a publication reason, by this decision.

## Review again

Review when authority registers publication-level reason codes (for example date-level anomaly findings), when a source-fact type gains or loses governed knowledge time, or when a platform-created record type becomes an externally sourced fact. Issue a successor decision; do not edit this record.
