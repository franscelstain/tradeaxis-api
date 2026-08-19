# Legacy Role Extract — LEGACY — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Authoritative Role:** `IMPLEMENTATION`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0521-IMP-01`
> **Legacy Source ID:** `LS-WS-0521`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/implementation/README.md`
> **Original SHA1:** `3AE4BBB0ED2B5F501273A658BFC3D53BE4997116`
> **Source Sections:** L38-L46 Preconditions for Implementation Translation; L47-L54 What Must Already Be Locked Before Implementation Starts
> **Extract Body SHA1:** `E7796728B694AE33E1E3DE10F7B8F3406156D07C`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## Preconditions for Implementation Translation

Sebelum implementation translation dimulai, hal berikut harus sudah locked secara meaning:

- input upstream yang sah;
- posisi watchlist sebagai consumer, bukan producer;
- forbidden shortcuts lintas-domain;
- build order global dan phase transition ke architecture guidance.

## What Must Already Be Locked Before Implementation Starts

Implementer dilarang memulai coding dari folder ini bila masih ada ketidakjelasan pada:

- producer contract;
- consumer behavior contract;
- system assembly order;
- placement `api_architecture` sebagai translation guardrail.
