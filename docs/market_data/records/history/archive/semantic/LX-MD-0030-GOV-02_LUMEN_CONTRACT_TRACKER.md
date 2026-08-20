# Legacy Semantic Extract — LX-MD-0030-GOV-02

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `GOVERNANCE`
- Source range: `L3971-L4005`
- Extract body SHA1: `F9CBBCB7880BA2A52BB3D5168DE07026FF55F167`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL RANGE LIFECYCLE CONTRACT UPDATE

[CONTRACT_STATUS]
- Historical interim status was not accepted as locked proof before runtime lifecycle command evidence was captured; later lifecycle/full-global proof supersedes this status.

[NEW CONTRACT]
- `source_mode=api` range backfill may acquire multiple trading dates in one provider window, but pipeline ownership remains date-scoped.
- `source_acquisition_mode=range_window` is acquisition context only; it must not collapse multiple requested dates into one `run_id`.
- Lifecycle publication proof remains per requested `trade_date`: import, promote/coverage, indicators, eligibility, hash, seal, finalize, evidence, fixture, replay.

[COMMAND SURFACE]
- Existing `market-data:backfill` remains import-only.
- New `market-data:backfill:lifecycle` owns full lifecycle range orchestration.
- Supported options include `--plan`, `--resume`, `--only-failed`, `--continue-on-error`, `--stop-on-error`, `--collect-all-errors`, `--max-dates-per-run`, `--with-evidence`, `--with-replay`, and `--no-replay`.

[FAILURE_POLICY]
- Ticker-level API failures are represented as `PARTIAL_SUCCESS` / `RUN_SOURCE_PARTIAL_RESPONSE` and are left for coverage gate to decide readability.
- Systemic range acquisition failures are reason-coded and must stop strict lifecycle execution.
- Replay is eligible only after `SUCCESS` + `READABLE` + coverage `PASS` + sealed run + evidence `EXPORTED`.
- Evidence export is allowed for held/failed dates to preserve failure context; replay fixture/verify is skipped for non-readable dates.

[RUN_MUTABILITY]
- Active runs before downstream stages may still be completed through the same run path.
- Terminal/import recovery remains a new run or promote-derived run through existing repository lifecycle.
- Sealed/readable mutation remains correction lifecycle only; this session does not add direct mutation paths to sealed/readable publications.

[SOURCE ACQUISITION BATCH CONTEXT]
- Required context now includes `source_acquisition_batch_id`, `source_acquisition_mode`, `source_window_start`, `source_window_end`, `warmup_start`, `requested_start`, `requested_end`, expected/success/failed ticker counts, and acquisition state.
- These fields are carried in run notes/event payload/evidence source context rather than changing core run/publication identity.

[GUARDS]
- New static guards assert lifecycle command separation, range-window source path, replay eligibility gate, and no `MAX(trade_date)` / raw/latest fallback reintroduction in the new code path.

---


<!-- LEGACY_EXTRACT_BODY_END -->
