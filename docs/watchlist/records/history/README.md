# Watchlist History Archive

> **Physical role:** `docs/watchlist/records/history/` — immutable historical/superseded/migration records; never current fallback authority.

Folder ini menyimpan material **superseded, migration snapshot, campaign addendum, historical governance, dan reorganization source**. Arsip dibuat datar di `archive/` untuk mencegah path terlalu panjang di Windows.

Gunakan [`ARCHIVE_INDEX.csv`](ARCHIVE_INDEX.csv) untuk menemukan archive ID, tipe record, path lama, dan path archive Windows-safe.

## Recording / Mutability Rule

History/archive adalah **IMMUTABLE_AFTER_ISSUE**:

- jangan mengubah isi arsip agar cocok current strategy/path;
- current correction/interpretation dibuat pada record current baru, bukan dengan rewriting archive;
- history tidak authoritative untuk current Weekly Swing behavior.

Universal rule: [`../../authority/governance/DOCUMENT_RECORDING_STANDARD.md`](../../authority/governance/DOCUMENT_RECORDING_STANDARD.md).


## Current Architecture Migration Records

- [`ROOT_GROUPING_REORGANIZATION_REPORT_2026-08-18.md`](ROOT_GROUPING_REORGANIZATION_REPORT_2026-08-18.md) — report current `authority / development / records` grouping.
- [`ROOT_GROUPING_REORGANIZATION_2026-08-18.csv`](ROOT_GROUPING_REORGANIZATION_2026-08-18.csv) — old path → new path mapping for this reorganization.
- [`ARCHIVE_INDEX.csv`](ARCHIVE_INDEX.csv) — current physical index for immutable archive snapshots.

Older reorganization reports remain historical records and may describe prior folder layouts. They are not current navigation authority.

## Legacy source provenance

Every pre-reorganization Watchlist source keeps a stable `LS-*` identity, original path, and original SHA1 in `LEGACY_SOURCE_INDEX.csv`. A second physical copy is **not** required when the role-specific `final_primary_path` is byte-identical to that source. `original_sources/` therefore stores only legacy sources for which a separate physical source copy is still required. Use `LEGACY_EXACT_DUPLICATE_COMPACTION_INDEX.csv` to trace exact-deduplicated sources.

## Fully split source policy

Composite legacy sources that reach `FULL_100_PERCENT_SEALED` are not stored again as duplicate physical originals. Use:

- `LEGACY_SOURCE_INDEX.csv` for source identity/path/SHA1/disposition;
- `LEGACY_SPLIT_SOURCE_CATALOG.md` for human navigation;
- `LEGACY_SPLIT_INDEX.csv` for role-pure extract records;
- `LEGACY_SPLIT_RECONSTRUCTION_INDEX.csv` for complete source-line coverage.

`original_sources/` therefore contains only sources that still require an independent physical source copy. Fully split sources and exact duplicates whose role-specific primary file already carries the registered original SHA1 are not stored twice.
