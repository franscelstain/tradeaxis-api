# Legacy Semantic Extract — LX-MD-0022-IMP-02

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `IMPLEMENTATION`
- Source range: `L79-L102`
- Extract body SHA1: `1D4355D7BF40E3CC33D3D1F658CB7F68FD24C8BC`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Write Path Integrity Matrix

| Write Path | Entrypoint | Tables Written | Publication Context Known | Run Context Known | FK Safe | Guard Exists | Status |
|---|---|---|---:|---:|---:|---:|---|
| Daily pipeline | `market-data:daily` | runs, bars, indicators, eligibility, publication, pointer | Yes after publication creation | Yes | Partial | Yes | Guarded by pipeline/finalize/pointer checks |
| Bars ingest | `market-data:eod-bars:ingest` | `eod_bars` or history + invalid bars | Candidate/live context passed by service | Yes | No for live | Yes | Implicit guard accepted |
| Indicator compute | `market-data:eod-indicators:compute` | `eod_indicators` or history | Candidate/live context passed by service | Yes | No for live | Yes | Implicit guard accepted |
| Eligibility build | `market-data:eod-eligibility:build` | `eod_eligibility` or history | Candidate/live context passed by service | Yes | No for live | Yes | Implicit guard accepted |
| Promote | `market-data:promote` | publication/history/live/pointer | Yes after candidate materialization | Yes | Partial | Yes | Candidate-scoped coverage must stay intact |
| Correction run | `market-data:correction:run` | correction, candidate/history/live/pointer | Yes after approved correction materialization | Yes | No for correction linkage | Yes | Phase-dependent implicit guard required |
| Seal/finalize | `market-data:dataset:seal`, `market-data:run:finalize` | runs/publications/pointer/history | Yes | Yes | Partial | Yes | Cross-table invariant guard required |

## Read Path Integrity Matrix

| Read Path | Resolver | Tables Read | Current Pointer Required | Publication Scoped | Risk | Status |
|---|---|---|---:|---:|---|---|
| Consumer/API/dashboard read | `resolveCurrentReadablePublicationForTradeDate` / pointer repo | pointer, publication, run, live artifacts | Yes | Current only | Raw/latest bypass | LOCKED by read-side contract |
| Session snapshot | session snapshot service/repository | pointer/current publication/live data | Yes | Current only | Capturing non-current data | Guarded |
| Evidence export current | evidence service/repository | run/publication/pointer/artifacts | Depends on selector | Yes | Incorrect current/historical label | Guarded |
| Evidence export historical | `resolvePublicationForEvidenceAudit` | historical publication + history/live scoped rows | No current fallback | Yes | Losing old publication context | Guarded |
| Replay verify current | replay actual-state resolver | pointer/publication/run/artifacts | Yes | Current expected context | False MATCH from stale data | Guarded |
| Replay verify historical | replay wrapper around evidence audit resolver | historical publication/history rows | No current fallback | Yes | Current-pointer drift | Guarded |
| Correction baseline/candidate | correction services/repositories | correction/publication/history/live | Baseline current required before correction | Yes | Replacing wrong baseline | Guarded |


<!-- LEGACY_EXTRACT_BODY_END -->
