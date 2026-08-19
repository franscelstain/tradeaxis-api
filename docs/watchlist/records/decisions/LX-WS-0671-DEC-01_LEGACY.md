# Legacy Role Extract — LEGACY — DECISION

> **Document Type:** DECISION
> **Authoritative Role:** `DECISION`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0671-DEC-01`
> **Legacy Source ID:** `LS-WS-0671`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/README.md`
> **Original SHA1:** `588366C6EA4AA9E1D65FC38E29F8007F4252FCC6`
> **Source Sections:** L64-L73 Watchlist Position in System Assembly
> **Extract Body SHA1:** `80AB96470C693BAB2E47B3C675A12C0AA280AF29`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## Watchlist Position in System Assembly

`watchlist/system/` bukan titik awal sistem. Folder ini harus dibaca sebagai node consumer dalam jalur assembly yang lebih besar:

- `market_data` lebih dulu mengunci producer-facing upstream contract;
- `system_audit` mengunci readiness lintas-domain dan forbidden shortcuts;
- `watchlist` kemudian mengunci consumer behavior yang berjalan di atas intake upstream yang sah.

Artinya, watchlist tidak boleh diperlakukan sebagai subsystem yang bebas menentukan urutan build sendiri.
