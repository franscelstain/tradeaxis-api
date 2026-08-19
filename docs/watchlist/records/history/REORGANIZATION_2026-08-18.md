# Watchlist Documentation Architecture Cleanup Report

**Date:** 2026-08-18  
**Source:** `docs-watchlist-weekly-swing-start-here-build-sequence.zip`  
**Scope:** documentation architecture, folder/file naming, placement, path safety, and reference alignment.  
**Strategy behavior change:** none.

## 1. Problem

The previous ZIP was logically organized but operationally too deep for Windows Explorer. The maximum relative path reached **196 characters** before the destination extraction path was added. Repeated `weekly_swing/` folders, nested migration snapshots, deep audit folders, and very long campaign filenames caused Windows error `0x80010135: Path too long`.

The same structure also made future reading harder because a developer had to traverse repeated folders that did not add authority or domain meaning.

## 2. Architecture principles applied

1. Watchlist currently has **one active strategy only: Weekly Swing**, so `weekly_swing/` is not repeated inside every documentation layer.
2. Keep top-level authority layers explicit: `strategy`, `implementation`, `research`, `evidence`, `findings`, `decisions`, `governance`, `history`.
3. Add child folders only when they materially help navigation.
4. Keep campaign IDs such as `C171` because they are historical identities; remove redundant filename boilerplate where safe.
5. Current strategy filenames remain semantic and flat.
6. Historical snapshots are archived **flat** and looked up through an index rather than recreated as nested directory trees.
7. All active links and path references are updated to the new locations.
8. Market Data architecture is not reorganized; only cross-domain Markdown references are updated where they point to moved Watchlist documents.

## 3. Final Watchlist structure

```text
docs/watchlist/
├── README.md
├── START_HERE.md
├── strategy/
├── implementation/
│   ├── contracts/
│   ├── db/
│   ├── guides/
│   ├── tests/
│   └── examples/
├── research/
├── evidence/
│   ├── runs/
│   ├── artifacts/
│   └── backtest/
├── findings/
├── decisions/
├── governance/
│   └── audit/
└── history/
    └── archive/
```

### Strategy

All canonical Weekly Swing strategy documents are directly under `strategy/`. The old extra `strategy/weekly_swing/` and `strategy/weekly_swing/validation/` levels are removed.

### Implementation

The previous technical folders were consolidated:

- contracts + shared/evidence-contract/persistence-contract material → `implementation/contracts/`
- DB/persistence schema and SQL → `implementation/db/`
- guidance + procedures + reference material → `implementation/guides/`
- fixtures + testing + verification → `implementation/tests/`
- examples → `implementation/examples/`

The authoritative build entry remains `implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`.

### Research / Findings / Decisions

These are flat current-record folders. Campaign IDs remain sortable and searchable without unnecessary `weekly_swing/`, `records/`, `strategy/`, `open/`, or `resolved/` nesting.

### Evidence

Large evidence remains separated only where the distinction is useful:

- `runs/` — execution/review/operator-command records
- `artifacts/` — JSON/CSV evidence artifacts
- `backtest/` — dedicated backtest proof

Small locks/ledgers stay directly in `evidence/`.

### History

Deep migration/superseded snapshots are no longer recreated as folder trees. They are stored as compact files under `history/archive/`.

Use:

- `history/ARCHIVE_INDEX.csv` — archive ID, record type, original path, archive path
- `governance/PATH_REORGANIZATION_2026-08-18.csv` — old current path → new current path

Original README/navigation files that were replaced by cleaner current indexes are also preserved as `reorganization_source` history records.

## 4. Naming rules

- Current strategy: semantic filename, no arbitrary ordinal prefix.
- Current implementation: legacy order prefixes such as `01_`, `05_`, `23_` removed where they were merely old reading order.
- Campaign/history files: retain meaningful `C###`, `R#`, `B##`, etc.
- Very long campaign boilerplate is compacted while campaign identity remains visible.
- No current filename depends on a path longer than Windows-friendly limits.

## 5. Windows path result

| Check | Before | After |
|---|---:|---:|
| Maximum relative file path | 196 chars | **105 chars** |
| Repeated `weekly_swing/` layer folders | many | **0** |
| Windows-invalid filename components | — | **0** |
| Case-insensitive filename collisions | — | **0** |
| Broken active Markdown links | — | **0** |
| JSON parse errors | — | **0** |
| CSV parse errors | — | **0** |
| Stale current references to removed `*/weekly_swing/` paths | — | **0** |

At 105 characters from ZIP root, there is a large safety margin for extraction into a normal repository path such as `D:\Laravel\watchlist\tradeaxis-api\...`.

## 6. Navigation for a new developer

The official route remains:

```text
docs/README.md
→ docs/watchlist/START_HERE.md
→ strategy Chapter 1–14
→ implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md
→ WS-B00 ... WS-B12
```

A developer should not browse history/evidence to discover current behavior. Current authority is discoverable from `START_HERE.md` and the flat strategy/implementation indexes.

## 7. Validation verdict

`PASS_WINDOWS_SAFE_AND_ARCHITECTURE_SIMPLIFIED`

The cleanup changes documentation placement, naming, navigation, and references. It does **not** change the Weekly Swing strategy behavior established in the prior source of truth.
