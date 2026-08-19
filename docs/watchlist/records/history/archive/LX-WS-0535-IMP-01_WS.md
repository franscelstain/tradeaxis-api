# Legacy Role Extract — WS — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0535-IMP-01`
> **Legacy Source ID:** `LS-WS-0535`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/_shared/06_SCHEMA_PARITY_RULES.md`
> **Original SHA1:** `D9F016D38DA901FB7F8B824E54BFE25D628B4075`
> **Source Sections:** L75-L96 3) DDL vs Seed Separation (LOCKED)
> **Extract Body SHA1:** `D316BB73E4D1211E669011A7D71C4D49D8E49380`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## 3) DDL vs Seed Separation (LOCKED)
Pemisahan berikut wajib dijaga:

### DDL
File DDL hanya boleh berisi hal-hal struktural seperti:
- `CREATE TABLE`
- `ALTER TABLE`
- `CREATE INDEX`
- trigger / constraint / foreign key / check constraint

### Seed
File seed hanya boleh berisi data awal seperti:
- dictionary rows,
- reason codes,
- fail codes,
- policy seed resmi yang memang diizinkan.

### Forbidden
- Dilarang menaruh kolom baru di seed tanpa ada di DDL.
- Dilarang menaruh dictionary seed di file DDL.
- Dilarang memakai seed untuk “diam-diam” memperkenalkan contract baru.
