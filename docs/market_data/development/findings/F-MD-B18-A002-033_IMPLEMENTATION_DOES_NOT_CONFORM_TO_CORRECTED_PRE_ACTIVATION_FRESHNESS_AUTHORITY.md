# Finding — implementation does not conform to the corrected pre-activation freshness authority

- ID: `F-MD-B18-A002-033`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-05T13:30:21+07:00
- Severity: `P1` for `MD-S003-R0025` (candidate-v4 needs the target to emit the corrected state); `P2` for the read surface
- Status: `OPEN` — raised by the impact trace of `DOC-CHG-20261005-001` (`E-MD-B18-A002-098`); no runtime change has been made
- Class: `IMPLEMENTATION_NONCONFORMANCE_TO_CORRECTED_AUTHORITY`
- Related: `E-MD-B18-A002-098`, `D-MD-B18-A002-015`, `F-MD-B18-A002-032`, `F-MD-B18-A002-025`, `E-MD-B10-A002-017`, `E-MD-B17-A002-001`
- Remediation owners: `MD-B10` (semantic hash and manifest producers; bounded successor attempt, first) and `MD-B17` (read surface; successor attempt)

## Observed

The corrected strategy (`Downstream_Data_Readiness_Guarantee_LOCKED.md`, section "Readability and operational freshness are independent (LOCKED)") adds the freshness state `NOT_APPLICABLE` for a `READABLE` publication whose requested trade date precedes the effective activation marker (or when no marker is effective). The implementation cannot represent it. This finding records where; it changes nothing.

| # | Surface | Fact observed in the working tree | Consequence under the corrected authority |
|---|---|---|---|
| 1 | `ArtifactSemanticHashService::FRESHNESS_STATES` (line 113) and `normalizeFreshnessState` (lines 248-253) | The vocabulary has four values; any other value, including the internal run labels `DEVELOPMENT_NOT_OPERATIONAL` and `NOT_EVALUATED`, is mapped to `NOT_AVAILABLE`. | A pre-activation readable publication is hashed as `NOT_AVAILABLE`, which the corrected authority forbids for a `READABLE` publication. |
| 2 | `ArtifactSemanticHashService` context guard (line 713) | A V2 context whose `freshness_state` is not one of the four values throws `ARTIFACT_SEMANTIC_CONTEXT_INCOMPLETE: freshness_state`. | The corrected state would be rejected, not hashed. |
| 3 | `EodPublicationRepository` (line 1038 in `publicationManifestSemanticPayloadV2`, line 973 in the V1 payload, line 2172 in `buildManifestByPublicationId`) | The same four-value check collapses everything else to `NOT_AVAILABLE`. | The V2 manifest member, `publication_manifest_hash` and the manifest view carry the wrong value. |
| 4 | `EodRunRepository` (lines 124, 212, 315) | A run stores `DEVELOPMENT_NOT_OPERATIONAL` when no marker is set and `NOT_EVALUATED` when a marker is set, decided by the presence of the marker and not by its relation to the requested trade date. | The applicability rule of the corrected authority (requested date against the effective marker) is not what the run records. |
| 5 | B10 tests that pin the superseded mapping | `ArtifactSemanticHashServiceTest::test_g4_an_unevaluated_or_unknown_freshness_can_never_be_hashed_as_fresh` asserts `DEVELOPMENT_NOT_OPERATIONAL` and `NOT_EVALUATED` normalise to `NOT_AVAILABLE`; `ArtifactSemanticIdentityOnMariaDbTest` (line 608) hashes `NOT_EVALUATED`. | Their intent (never `FRESH`) survives; the asserted mapping is superseded. |
| 6 | B17 read surface | `MarketDataReadinessService` and `MarketDataReadProductRepository` contain no `freshness_state`; the readiness result exposes `activation_state` (`DEVELOPMENT`/`OPERATIONAL`) and a comment. | The corrected `NOT_APPLICABLE` response, and the existing `FRESH`/`STALE`/`DEGRADED` states, are not emitted by the read surface. |
| 7 | Activated world | No code path produces `FRESH`, `STALE` or `DEGRADED`; a run in an activated world stores `NOT_EVALUATED`, which normalises to `NOT_AVAILABLE`. | Outside this correction's scope; recorded because activation will expose it (`MD-S056-R0045`, owned by `MD-B19`, is `NOT_ASSESSED`). |
| 8 | `MarketDataEvidenceExportService` (line 1126) | The run summary mirrors the raw run label. | The export would show an internal label that is not a freshness state; the example artifacts in `Run_Artifacts_Format_LOCKED.md` are unchanged and consistent. |

## Not in scope of this finding

V1-profile publications and their sealed digests are historical and are not rewritten. Whether the per-row `freshness_state` of the eligibility artifact equals the publication-level state is not decided by the correction; the V2 producer carries the run state into both, and that stays the case.

## Closure

Resolved when (1) a bounded `MD-B10` successor attempt makes the V2 semantic hash and manifest producers accept, canonicalise and hash `NOT_APPLICABLE`, derives the run's pre-activation state from the requested trade date and the effective marker alone, replaces the superseded test pins, and revalidates `MD-S005-R0056`, `MD-S005-R0071` and `MD-S045-R0058`; (2) an `MD-B17` successor attempt proves the thirteen not-assessed rows listed in `E-MD-B18-A002-098` and makes the read surface emit the freshness state; (3) the activated-world evaluator gap (row 7) is either owned by a stage attempt or recorded as a dependency.
