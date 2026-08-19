# Watchlist Link Integrity Check

> **Status:** CANONICAL GOVERNANCE

## Purpose

Semua referensi file internal pada `docs/watchlist/` harus resolve setelah perubahan path atau role. Architecture separation tidak boleh meninggalkan ghost link.

## Rules

1. Gunakan Markdown link untuk referensi dokumen yang harus dapat dinavigasi.
2. Link lokal harus tetap berada di dalam root `docs/`.
3. Rename/move wajib disertai pembaruan seluruh referensi yang authoritative.
4. Historical text boleh menyebut legacy path bila konteksnya jelas sebagai historical record, tetapi legacy path tidak boleh dipakai sebagai current authority pointer.
5. Folder reference harus menunjuk ke `README.md` bila yang dimaksud adalah entry point folder.

## Minimum Validation

Setiap refactor dokumentasi wajib:

- scan seluruh Markdown;
- resolve seluruh local Markdown links;
- fail bila target tidak ada;
- validasi JSON/CSV supporting artifacts setelah move;
- simpan migration manifest untuk old-path -> new-path traceability.

Migration manifest architecture saat ini: `WATCHLIST_DOCUMENT_MIGRATION_MANIFEST.csv`.
