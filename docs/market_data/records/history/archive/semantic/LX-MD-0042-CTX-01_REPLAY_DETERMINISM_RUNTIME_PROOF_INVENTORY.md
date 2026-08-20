# Legacy Semantic Extract — LX-MD-0042-CTX-01

- Source ID: `LS-MD-0042`
- Original path: `audit/REPLAY_DETERMINISM_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `7E5FB7DE9A03E174497EC8911DE7215EE2F3EEEC`
- Extract role: `CONTEXT`
- Source range: `L28-L69`
- Extract body SHA1: `200BC579F595C5C4ADD800A05DFB8F1CE118D4FF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Fixture Identity

- Fixture path: `storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2`
- Fixture id: `valid_case`
- Fixture family: `runtime_generated_valid_case`
- Fixture schema: `replay_fixture_v2`
- Fixture source: `generated_from_run_2`
- Trade date: `2026-02-18`
- Run id: `2`
- Publication id/version: `2` / `2`
- Source mode: `manual_file`
- Coverage: `PASS`, ratio `0.986857`, threshold `0.980000`, expected `913`, available `901`, missing `12`
- Hashes: bars `f1d70d360d14cc9c63bf56bb35c0c08e78706084311d86dea8d3ed0a2ba9d06d`, indicators `ce9e7b7ef48981c8ff24fb4ff6102f51b307ff845ff10232f546e3bb8837d7ff`, eligibility `1e9ba399d5e7428d106c656664028e9ad35aae83405601154b02d980a8ecad6a`

## Replay Area Mapping

| Replay Area | Contract Required | Code Produces/Checks | Test Proves | Runtime Proves | Gap |
|---|---|---|---|---|---|
| replay command surface | verify/smoke/backfill/fixture generation/evidence export exposed | `VerifyReplayCommand`, smoke/backfill/generate commands | `OpsCommandSurfaceTest`, command static guards | `php artisan list market-data` and help commands PASS | none |
| replay fixture generation | deterministic manifest and expected files | `ReplayVerificationService::generateFixtureFromRun` | replay fixture generation tests/static guard | generated fixture path above | none |
| replay manifest | identity, source, version, file list, assertion layers | `loadFixturePackage` validates manifest and files | `ReplayDeterminismStaticGuardTest` | `manifest.json` generated with required fields | none |
| current publication lookup | pointer -> publication -> readable/sealed/success/coverage-pass | `findReadableCurrentPublicationForRun` path | historical/current static guards | PASS proof resolved publication `2` through current pointer | none |
| historical publication lookup | explicit run/publication/trade-date context only | `resolvePublicationForEvidenceAudit` under historical context | `ReplayHistoricalDeterminismHardeningStaticGuardTest`; service historical unit tests | historical fixture `run-2-publication-2` verifies non-current `publication_id=2` after pointer moved to newer `publication_id=5` | none |
| expected context | fixture must declare complete expected context | `buildExpectedContext`, `validateExpectedProofCompleteness` | replay service tests | PASS fixture expected context exported | none |
| actual context | runtime must build actual run/publication/pointer/source/coverage/hash context | `buildActualReplayState` | replay service tests | PASS evidence contains actual context | none |
| coverage comparison | state, ratio, threshold, expected/available/missing counts | `compareExpectedAndActual` | replay/coverage tests | PASS and FAIL outputs include coverage summary | none |
| publishability comparison | terminal and publishability state | `compareExpectedAndActual` | replay tests | PASS output `SUCCESS|READABLE` expected/actual | none |
| reason code comparison | reason-code count distribution | `compareReasonCodeCounts` | mismatch tests | FAIL proof returns `REPLAY_FINAL_REASON_CODE_MISMATCH` | none |
| source/provider comparison | source mode/name/provider/file identity if present | source context compare and manual-file policy mismatch | replay tests/static guard | PASS proof compares manual file source context | none |
| hash comparison | bars/indicators/eligibility hashes | artifact context compare | replay tests/static guard | PASS proof includes all three hashes | none |
| seal comparison | seal state and sealed metadata | seal context compare | replay tests/static guard | PASS proof includes `SEALED` state | none |
| correction lineage comparison | correction fields if present | correction context and lineage compare | replay correction/historical tests | not present in current runtime fixture | conditional runtime only |
| manual file policy comparison | manual file cannot bypass coverage/readability | `appendManualFilePolicyMismatches` | replay tests/static guard | PASS proof preserves manual source context | none |
| PASS result | deterministic match maps to `PASS` | `replayStatusForComparison` | replay service/repository/evidence tests | `replay_id=2` and smoke `replay_id=4` PASS | none |
| FAIL result | mismatch maps to `FAIL` | `replayStatusForComparison` | replay service/command tests | `replay_id=3` and smoke `replay_id=5` FAIL | none |
| BLOCKED result | missing fixture/context/runtime prerequisite maps to blocked command output | command catch path and smoke blocked cases | command/static guards | invalid/missing/broken fixture cases BLOCKED | none |
| result persistence | replay metric stores result fields | `ReplayResultRepository` writes `replay_status` | repository integration test | replay rows persisted for ids `2`, `3`, `4`, `5` | none |
| evidence export linkage | replay evidence references result/context/summary/status | `MarketDataEvidenceExportService::exportReplayEvidence` | `ReplayEvidenceExportServiceTest` | evidence export for `replay_id=2`, file_count `6` | none |
| command output | operator-readable result and mismatch reasons | verify/smoke/backfill commands | `OpsCommandSurfaceTest` | output includes `replay_status`, summaries, reason codes | none |
| audit docs entry | implementation ledger records runtime proof | `LUMEN_IMPLEMENTATION_STATUS.md` | audit docs guard | current session entry added | none |
| contract tracker entry | contract tracker records final rule and lock condition | `LUMEN_CONTRACT_TRACKER.md` | audit docs guard | current contract entry added | none |


<!-- LEGACY_EXTRACT_BODY_END -->
