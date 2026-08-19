# Watchlist Document Recording Standard — Adoption Report

**Date:** 2026-08-18  
**Scope:** `docs/watchlist/`  
**Verdict:** **PASS — UNIVERSAL RECORDING / NO-SILENT-UPDATE GOVERNANCE ACTIVE**

## Objective

Menutup gap governance di mana protection sebelumnya paling kuat pada canonical strategy, tetapi belum ada satu standard universal yang secara eksplisit menentukan lifecycle, mutability, correction, supersession, dan traceability untuk seluruh jenis pencatatan Watchlist.

## Canonical Standard Added

- `docs/watchlist/governance/DOCUMENT_RECORDING_STANDARD.md`
- `docs/watchlist/governance/DOCUMENT_CHANGE_LOG.md`

Standard sekarang membagi dokumen ke tiga kelas:

1. `IMMUTABLE_AFTER_ISSUE`
   - final evidence/result;
   - issued decision;
   - locked research/preregistration;
   - history/archive.

2. `CONTROLLED_REVISION`
   - strategy;
   - governance.

3. `MUTABLE_TRACEABLE`
   - implementation contract/guidance;
   - status/ledger/tracker;
   - open finding lifecycle;
   - README/index;
   - audit docs;
   - research draft sebelum lock.

Tidak ada semantic document class yang boleh diedit bebas tanpa trace.

## Lifecycle Rules Locked

| Type | Lifecycle / rule |
|---|---|
| Strategy | `DRAFT -> CANONICAL -> SUPERSEDED`; material revision via finding + evidence + decision |
| Governance | controlled revision; prior authority archived for material change |
| Implementation | mutable but material contract change requires change-log + test/evidence/status sync |
| Research | `DRAFT -> LOCKED -> COMPLETED/CANCELLED`; locked identity immutable |
| Evidence | final immutable; correction is a new evidence record |
| Finding | original observation immutable; lifecycle/resolution may be appended |
| Decision | `DRAFT -> ISSUED -> SUPERSEDED`; issued decision immutable |
| Ledger/tracker | append-oriented; historical entry never rewritten |
| History | immutable archive |
| README/index | navigation only, no new business/technical rule |

## Traceability Added for This Governance Change

Finding:
- `docs/watchlist/findings/WS_DOCUMENT_RECORDING_GOVERNANCE_GAP_2026-08-18.md`
- Record ID `F-WS-20260818-01`

Decision:
- `docs/watchlist/decisions/WS_DOCUMENT_RECORDING_STANDARD_ADOPTION_2026-08-18.md`
- Record ID `D-WS-20260818-01`

Change-log entry:
- `DOC-CHG-20260818-001`

Prior governance authority snapshots were preserved in `docs/watchlist/history/archive/` and indexed as `governance_snapshot` in `history/ARCHIVE_INDEX.csv`.

## Documents Aligned

Current governance/entry-point documents updated:

- `governance/README.md`
- `governance/DOCUMENTATION_ARCHITECTURE.md`
- `governance/DOCUMENT_CHANGE_POLICY.md`
- `governance/WATCHLIST_DOCUMENT_AUTHORITY.md`
- `governance/WATCHLIST_OWNER_MATRIX.md`
- `README.md`
- `START_HERE.md`

Layer recording rules updated:

- `strategy/README.md`
- `implementation/README.md`
- `research/README.md`
- `evidence/README.md`
- `findings/README.md`
- `decisions/README.md`
- `history/README.md`

Implementation/audit enforcement updated:

- `implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`
- `governance/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `governance/audit/WATCHLIST_AUDIT_CHECKLIST_FINAL.md`
- `governance/audit/WATCHLIST_AUDIT_PROMPT_STANDARD.md`
- `governance/audit/IMPL_WATCHLIST_IMPLEMENTATION_PROMPT_STANDARD.md`
- `governance/audit/IMPL_WATCHLIST_IMPLEMENTATION_CHECKLIST_FINAL.md`

`WATCHLIST_OWNER_MATRIX.md` was also aligned with the current Windows-safe implementation layout (`contracts/`, `db/`, `guides/`, `tests/`, `examples/`) and no longer lists removed legacy implementation folders as current areas.

## New Recording Behavior During Implementation

A normal material implementation session now follows:

```text
read governance + strategy
        ↓
update implementation
        ↓
test / validate
        ↓
new evidence or append status
        ↓
material doc/contract change → DOCUMENT_CHANGE_LOG
        ↓
finding/decision only when required by impact
```

Examples:

### Implementation bug, strategy already correct

- create/update finding if material;
- fix implementation;
- update tests;
- create validation evidence;
- append status/contract tracker;
- log material technical-contract change;
- **do not change strategy**.

### Evidence was wrong

- keep original evidence intact;
- create correction evidence record;
- reference original evidence and impact;
- **do not rewrite original result**.

### Issued decision changes

- keep old decision intact;
- create new decision with `Supersedes`;
- **do not rewrite GO into NO-GO or vice versa**.

### Locked research needs a new threshold

- keep locked preregistration intact;
- create new research identity/version;
- new result goes to evidence;
- **do not retune locked research in-place**.

## Validation

- semantic governance checks: **28/28 PASS**
- active Markdown files checked: **853**
- broken active Markdown links: **0**
- JSON files checked: **116**, parse errors **0**
- CSV files checked: **20**, parse errors **0**
- current owner matrix stale legacy implementation-folder references: **0**
- prior governance snapshots preserved: **6**
- longest relative path from `docs/`: **100 characters**
- target Windows-safe maximum: `<=120`

Historical archive links were intentionally not rewritten because archive content is immutable historical material. Active documentation link validation excludes `history/archive/` content.

## Final State

Documentation recording governance can now be summarized as:

> **Every fact has a role. Every material change has a trace. Immutable records are corrected or superseded, never rewritten. Implementation may evolve, but it cannot silently redefine strategy or its own material contract.**
