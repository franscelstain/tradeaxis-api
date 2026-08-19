# Legacy Role Extract — LEGACY — CONTEXT

> **Document Type:** HISTORICAL_CONTEXT
> **Authoritative Role:** `HISTORY`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0521-CTX-03`
> **Legacy Source ID:** `LS-WS-0521`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/implementation/README.md`
> **Original SHA1:** `3AE4BBB0ED2B5F501273A658BFC3D53BE4997116`
> **Source Sections:** L1-L26 (preamble/title)
> **Extract Body SHA1:** `A1134AACFE8460099B0F8C33EFDE8577C0DFA078`
> **Current Authority:** NO

The body below is an exact preservation copy of the registered source sections. It is historical context only.

---

# Watchlist Implementation Guidance

Folder ini berisi panduan implementasi untuk menerjemahkan baseline [`../`](../) menjadi aplikasi watchlist.

Boundary utama:
- implementation guidance **tidak** menggantikan source of truth pada `system/policies/weekly_swing/`
- implementation guidance **harus** tunduk pada baseline `weekly_swing` yang sudah difreeze
- guidance ini tetap berada di domain **watchlist**, bukan portfolio, bukan execution, dan bukan market-data internals

Urutan baca:
1. `weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
2. `weekly_swing/02_WS_MODULE_MAPPING.md`
3. `weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`
4. `weekly_swing/04_WS_API_GUIDANCE.md`
5. `weekly_swing/05_WS_PERSISTENCE_GUIDANCE.md`
6. `weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
7. `weekly_swing/06_WS_TEST_IMPLEMENTATION_GUIDANCE.md`
8. `weekly_swing/07_WS_DELIVERY_CHECKLIST.md`


Audit implementasi watchlist berada di [`../../audit/implementation/`](../../audit/implementation/).


Guidance di folder ini default-nya dibaca sebagai **Layer B**. Ia membantu translasi build, tetapi tidak mengubah owner rule pada folder policy.
