# Legacy Semantic Extract — LX-MD-0043-CTX-01

- Source ID: `LS-MD-0043`
- Original path: `audit/REPLAY_HISTORICAL_DETERMINISM_HARDENING_INVENTORY.md`
- Original SHA1: `6831E28FEFD55DC99E3BEA0B303AC2A439016C86`
- Extract role: `CONTEXT`
- Source range: `L26-L53`
- Extract body SHA1: `649FBAF8C8B08957E75BC13B5E76C53EB219C0C4`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Replay Actual Resolver Matrix

| Replay Surface | Entrypoint | Actual State Resolver Used | Current Pointer Required | Historical Sealed Allowed | Publication Context Source | Artifact Scope | Status |
|---|---|---|---:|---:|---|---|---|
| Verify replay | `market-data:replay:verify` -> `ReplayVerificationService::verifyRunAgainstFixture()` | `resolvePublicationForReplayActualState()` | Current context only | Yes | Expected fixture selector + evidence audit resolver | `publication:<id>` | PATCHED |
| Smoke replay | `market-data:replay:smoke` -> replay verification service | `resolvePublicationForReplayActualState()` | Current context only | Yes | Fixture expected context | Publication scoped | PATCHED |
| Backfill replay | `market-data:replay:backfill` -> replay verification service | `resolvePublicationForReplayActualState()` | Current context only | Yes | Fixture expected context | Publication scoped | PATCHED |
| Fixture generation | `generateFixtureFromRun()` | existing `resolvePublicationForRun()` | Yes | No | Current readable run publication | Current publication scoped | CURRENT_POINTER_DEPENDENT_BY_DESIGN |
| Replay evidence export | `market-data:evidence:export --replay_id` | stored replay actual/expected context + evidence audit context | No for historical replay result | Yes | replay result context | Publication scoped | AUDIT_HISTORICAL_SAFE |

## Current vs Historical Replay Context Matrix

| Resolver | File | Method | Used By | Current Pointer Required | Historical Sealed Allowed | Validation Required | Status |
|---|---|---|---|---:|---:|---|---|
| Replay current actual resolver | `ReplayVerificationService.php` | `resolvePublicationForReplayActualState()` current branch | Current replay verification | Yes | No | SUCCESS + READABLE + current pointer | ENFORCED |
| Replay historical actual resolver | `ReplayVerificationService.php` | `resolvePublicationForReplayActualState()` historical branch | Historical replay verification | No | Yes | explicit selector, lineage, sealed publication, artifact scope | PATCHED |
| Evidence audit resolver | `EodEvidenceRepository.php` | `resolvePublicationForEvidenceAudit()` | Replay historical wrapper and evidence export | No | Yes | run/publication mirror, trade date, sealed, coverage, hashes | REUSED |
| Consumer resolver | `EodPublicationRepository.php` | `resolveCurrentReadablePublicationForTradeDate()` / `findReadableCurrentPublicationForRun()` | Consumer/read-side path | Yes | No | current pointer readable only | UNCHANGED |

## Historical Replay Risk Matrix

| File | Method | Pattern | Replay Actual Path? | Current Pointer Dependency | Historical Risk | Action | Status |
|---|---|---|---:|---:|---|---|---|
| `ReplayVerificationService.php` | old `resolvePublicationForRun()` used by verify | `findReadableCurrentPublicationForRun()` | Yes | Yes | Historical publication A could fail or compare against current B after pointer moved | Added replay actual resolver with historical branch | PATCHED |
| `ReplayVerificationService.php` | `buildActualReplayState()` | current-only context fields | Yes | Partial | Actual output could not explicitly label historical scope | Added `actual_replay_resolution_context` and artifact scope | PATCHED |
| `ReplayVerificationService.php` | reason code mapping | generic replay mismatch | Yes | No | Historical/current mismatch could be ambiguous | Added historical-aware reason mapping | PATCHED |
| `EodPublicationRepository.php` | consumer resolver | current pointer read | No consumer only | Yes by design | Risk only if weakened | No consumer change | UNCHANGED |


<!-- LEGACY_EXTRACT_BODY_END -->
