# Legacy Role Extract — LEGACY — CONTEXT

> **Document Type:** HISTORICAL_CONTEXT
> **Authoritative Role:** `HISTORY`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0671-CTX-03`
> **Legacy Source ID:** `LS-WS-0671`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/README.md`
> **Original SHA1:** `588366C6EA4AA9E1D65FC38E29F8007F4252FCC6`
> **Source Sections:** L1-L4 (preamble/title); L5-L13 Scope Lock; L14-L19 Current Active Policy; L20-L31 Upstream Intake Read First; L32-L46 Read First; L59-L63 Layer Activation Reference
> **Extract Body SHA1:** `8B236784500011615F197AFFE8517E2507E44360`
> **Current Authority:** NO

The body below is an exact preservation copy of the registered source sections. It is historical context only.

---

# Watchlist System Docs

Folder ini adalah source of truth untuk membangun sistem watchlist.

## Scope Lock

System docs watchlist:
- hanya membahas watchlist
- hanya membahas saran / recommendation / confirm sebagai bagian watchlist
- tidak membahas portfolio
- tidak membahas execution nyata
- tidak membahas market-data internals

## Current Active Policy

Policy aktif yang dibahas saat ini hanya:
- `policies/weekly_swing/`

## Upstream Intake Read First
Sebelum membaca owner docs Weekly Swing, pembaca yang ingin membangun sistem watchlist harus lebih dulu mengikat intake upstream dari `market-data` ke kontrak producer-facing yang sah.

Minimum anchor:
1. `../../market_data/README.md`
2. `../../market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
3. `../../market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
4. `../../market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
5. `../../market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Setelah jalur intake upstream ini jelas, baru lanjut ke owner docs Weekly Swing di bawah.

## Read First

1. `policies/weekly_swing/01_WS_OVERVIEW.md`
2. `policies/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md` — canonical runtime flow (`PLAN -> RECOMMENDATION -> CONFIRM`)
3. `policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
4. `policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
5. `policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
6. `policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
7. `policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
8. `policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`
9. `policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md`
10. `policies/weekly_swing/13_WS_CONTRACT_TEST_CHECKLIST.md`
11. `policies/weekly_swing/21_WS_IMPLEMENTATION_BLUEPRINT.md`

## Layer Activation Reference

Gunakan [`LAYER_ACTIVATION_RULE.md`](../LAYER_ACTIVATION_RULE.md) untuk menentukan apakah paket harus dibaca sebagai Layer A, B, atau C.
