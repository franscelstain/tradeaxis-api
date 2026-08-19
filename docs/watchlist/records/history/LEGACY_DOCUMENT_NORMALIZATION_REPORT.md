# Watchlist Legacy Corpus Full Semantic Audit & Normalization Report

## Verdict

**PASS — FULL LEGACY CORPUS NORMALIZED WITH IMMUTABLE PROVENANCE**

Audit ini menggunakan dua baseline:

- original pre-reorganization corpus: `docs.zip`;
- current architecture sebelum audit: relationship-integrity 9/9 package.

Tidak ada pemindahan berdasarkan filename saja. Semua file original dibaca, dan Markdown diaudit lagi per section sebelum final placement/disposition ditentukan.

## Coverage

- Original Watchlist files: **671 / 671**
- Original bytes: **7,344,991**
- Markdown files: **563**
- Markdown lines processed: **139,157**
- Markdown section records: **5,171**
- JSON files parsed: **83**
- CSV files parsed: **16**
- SQL files read: **9**
- Original read/parse errors: **0**
- Original source SHA mismatches: **0**

## Composite documents

- Material composite legacy documents: **118**
- Exact role-pure extracts created: **334**
- Intentional composite/bundle exceptions: **327**

Extract role distribution:

```text
DECISION=35
EVIDENCE=95
FINDING=69
GOVERNANCE=7
IMPLEMENTATION=49
RESEARCH=63
STRATEGY=16
```

Large composite examples:

- `LS-WS-0550` `system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md` — 1799 lines — roles: `DECISION;EVIDENCE;IMPLEMENTATION;RESEARCH;STRATEGY`
- `LS-WS-0350` `audit/WS_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC.md` — 466 lines — roles: `EVIDENCE;FINDING;IMPLEMENTATION`
- `LS-WS-0372` `audit/WS_C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION.md` — 466 lines — roles: `EVIDENCE;FINDING;RESEARCH`
- `LS-WS-0423` `audit/WS_C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY.md` — 456 lines — roles: `EVIDENCE;FINDING;IMPLEMENTATION;RESEARCH;STRATEGY`
- `LS-WS-0577` `system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md` — 456 lines — roles: `EVIDENCE;FINDING;IMPLEMENTATION;RESEARCH;STRATEGY`
- `LS-WS-0415` `audit/WS_C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY.md` — 450 lines — roles: `EVIDENCE;FINDING;IMPLEMENTATION;RESEARCH`
- `LS-WS-0342` `audit/WS_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC.md` — 442 lines — roles: `DECISION;EVIDENCE;FINDING;IMPLEMENTATION`
- `LS-WS-0400` `audit/WS_C50_IS_VALIDATION_ANTI_OVERFIT_CHECK.md` — 435 lines — roles: `DECISION;EVIDENCE;FINDING`

## Placement normalization

Originals are preserved byte-for-byte under `records/history/original_sources/` with stable `LS-WS-NNNN` IDs.

For materially mixed Markdown, exact original sections are represented by `LX-WS-<source>-<role>-NN` records with source line ranges and extract body SHA1.

Physical rule after audit:

- current strategy/governance remain under `authority/`;
- current technical work remains under `development/implementation/`;
- current/open findings remain under `development/findings/`;
- completed legacy C/R/P/B/S/Q research/findings are no longer left in active development;
- historical evidence stays in `records/evidence/`;
- issued decisions stay in `records/decisions/`;
- old research/finding/implementation/strategy/governance extracts stay in `records/history/archive/`;
- append-oriented LUMEN implementation/status ledgers remain under `development/implementation/`, with their old session text treated as history rather than current authority.

Legacy final physical-role distribution:

```text
DECISION=81
EVIDENCE=348
GOVERNANCE=20
HISTORY=112
IMPLEMENTATION=98
NAVIGATION=1
STRATEGY=11
```

Historical correlation keys are also indexed (`C171`, `B01`, `R2`, etc.) without pretending they are current `WS-Bxx-Axxx` Attempt IDs. Unique historical work keys indexed: **190**.

## Current authority safety

- 15 files in `authority/strategy/` (14 strategy owners + README) changed by this legacy audit: **0**.
- Legacy original records whose final primary placement remains in active `development/research` or `development/findings`: **0**.
- Campaign-specific C/R/P/S headings left in current generic implementation guidance, excluding append-only ledgers and current WS-B stage IDs: **0**.

So the legacy audit did **not** rewrite current strategy to fit old files. Old content was normalized around current authority, not the reverse.

## Machine validation

- Documentation Integrity Gate: **PASS_WITH_REGISTERED_LEGACY_EXCEPTION**, failed checks: **0**.
- Relationship Integrity Gate: **PASS**.
- Relationship mutation/self-tests: **PASS 9/9 + authorized cross-baseline positive case**.
- Broad active Markdown links: **0 broken**.
- JSON errors: **0**.
- CSV errors: **0**.
- Legacy source/extract integrity: **671 sources + 334 extracts PASS**.
- Max relative path: **113 chars** (`docs/watchlist/records/history/archive/H0043_strategy_weekly_swing_validation_16_WS_EVAL_METRICS_SUFF_DDF1B526.md`).

`PASS_WITH_REGISTERED_LEGACY_EXCEPTION` on the document gate remains only for the already-registered historical duplicate `DOC-CHG-20260818-003`; it is not a legacy-placement or current-authority failure.

## Canonical audit indexes

- `records/history/LEGACY_SOURCE_INDEX.csv`
- `records/history/LEGACY_DOCUMENT_ROLE_AUDIT.csv`
- `records/history/LEGACY_SECTION_ROLE_AUDIT.csv`
- `records/history/LEGACY_SPLIT_INDEX.csv`
- `records/history/LEGACY_BUNDLE_EXCEPTION_INDEX.csv`
- `records/history/LEGACY_WORK_CORRELATION_INDEX.csv`

These indexes make it possible to search an original source ID, historical campaign key, exact source section, semantic role, current/final placement, and derived extract without guessing from filenames.


## Post-normalization compaction

Fully split composite sources are no longer retained as duplicate physical originals. See `LEGACY_SPLIT_SOURCE_CATALOG.md` and `LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv`.
