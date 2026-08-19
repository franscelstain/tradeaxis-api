# Legacy Role Extract — LEGACY — CONTEXT

> **Document Type:** HISTORICAL_CONTEXT
> **Authoritative Role:** `HISTORY`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0538-CTX-03`
> **Legacy Source ID:** `LS-WS-0538`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/system/policies/README.md`
> **Original SHA1:** `9949DDDA638D5631A0BB95AA22A4E9F8F2FDBE1B`
> **Source Sections:** L1-L5 (preamble/title); L20-L29 Policy Layer Structure; L30-L41 Reading Guidance; L42-L45 Catalog; L46-L49 Minimum Rule for Strategy Policies
> **Extract Body SHA1:** `187A735A8392A9EFAB791431A3428EEB5D940DF1`
> **Current Authority:** NO

The body below is an exact preservation copy of the registered source sections. It is historical context only.

---

# Policies — Index

> **Status:** LOCKED (Normative)
> **Doc Role:** Policy layer catalog

## Policy Layer Structure

Layer policy di domain watchlist terdiri dari:

- [`_shared/`](_shared/README.md)  
  Baseline lintas strategy yang berlaku bersama.

- folder strategy seperti [`weekly_swing/`](weekly_swing/README.md)  
  Kontrak strategy-specific yang berdiri di atas baseline shared.

## Reading Guidance

Urutan baca minimum yang dianjurkan adalah:

1. [`../policy.md`](../policy.md)
2. [`../README.md`](../README.md)
3. dokumen pada `_shared/` yang relevan
4. README strategy yang relevan
5. file normatif bernomor pada strategy tersebut

Dokumen ini hanya memetakan letak policy. Urutan baca detail strategy tidak diulang panjang di sini dan tetap dipimpin oleh README strategy masing-masing.

## Catalog

- [`weekly_swing/`](weekly_swing/README.md) — Weekly Swing

## Minimum Rule for Strategy Policies

Setiap policy strategy harus dibangun di atas baseline `_shared/`. Dokumen strategy-specific tidak boleh mendefinisikan ulang kontrak minimum global yang sudah hidup di `_shared/`; strategy-specific hanya boleh menambahkan semantics, constraints, procedures, dan acceptance areas yang memang khusus untuk strategy tersebut.
