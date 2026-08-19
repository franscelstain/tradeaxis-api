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
