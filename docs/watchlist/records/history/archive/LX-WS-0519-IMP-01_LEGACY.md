# Legacy Role Extract — LEGACY — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Authoritative Role:** `IMPLEMENTATION`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0519-IMP-01`
> **Legacy Source ID:** `LS-WS-0519`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/db/README.md`
> **Original SHA1:** `C1FB267A6F7FDC4D195B2929C871146FE4D0ECD8`
> **Source Sections:** L11-L21 Reading Order; L26-L29 What This Folder Must Not Do
> **Extract Body SHA1:** `3DE639EEB3216DA86A6B7ABF04D325AE91557329`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## Reading Order

1. `01_DB_OVERVIEW.md`
2. `02_DB_SCHEMA_MARIADB.md`
3. `03_DB_INDEXES_AND_CONSTRAINTS.md`

Artefak executable:
- `04_DB_SEED_GLOBAL.sql`
- `05_DB_DDL_MARIADB.sql`
- `MIGRATIONS.sql`

## What This Folder Must Not Do

Folder `db/` tidak boleh menjadi tempat lahirnya business rule baru yang tidak hidup dulu pada dokumen normatif markdown. SQL artifact hanya merealisasikan rule yang sudah dikunci oleh dokumen owner.
