# Legacy Role Extract — LEGACY — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Authoritative Role:** `IMPLEMENTATION`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0671-IMP-02`
> **Legacy Source ID:** `LS-WS-0671`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/README.md`
> **Original SHA1:** `588366C6EA4AA9E1D65FC38E29F8007F4252FCC6`
> **Source Sections:** L47-L51 Implementation Guidance; L52-L58 Layer Guard; L74-L81 Preconditions Before Building Watchlist; L82-L84 Transition to Implementation Guidance
> **Extract Body SHA1:** `EF5690EE664035D2AD7337ABF6DBDC5C0037FFF6`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## Implementation Guidance

Panduan implementasi watchlist tersedia di `docs/watchlist/system/implementation/`. Guidance ini tidak menggantikan owner docs policy dan harus tunduk pada baseline `weekly_swing` yang sudah difreeze.

## Layer Guard

Folder `system/` boleh direferensikan oleh implementation guidance, examples, fixtures, SQL support, schema docs, atau sample payload.
Artefak-artefak itu membantu penerjemahan baseline, tetapi **tidak otomatis** mengubah paket dokumen menjadi audit Layer C.
Layer C baru relevan bila ada bukti code/app/runtime nyata yang cukup dan bisa ditelusuri.

## Preconditions Before Building Watchlist

Sebelum build watchlist dimulai, pembaca harus sudah memastikan:

- producer-facing intake dari `market_data` sudah jelas;
- current-state readiness lintas-domain sudah tidak membuka kontrak paralel;
- behavior owner docs watchlist sudah dibaca lebih dulu daripada implementation guidance.

## Transition to Implementation Guidance

Masuk ke `watchlist/system/implementation/` hanya boleh dilakukan setelah policy dan intake baseline stabil. Folder implementation bukan pintu awal untuk menebak kontrak sistem, melainkan jalur translation setelah owner docs selesai dikunci.
