# Decision — Adopt Universal Document Recording and Lifecycle Standard

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** watchlist / documentation governance
- **Record ID:** `D-WS-20260818-01`
- **Created:** 2026-08-18
- **Related Finding:** `../../development/findings/WS_DOCUMENT_RECORDING_GOVERNANCE_GAP_2026-08-18.md`
- **Related Evidence:** `../history/archive/H0159_DOCUMENT_RECORDING_STANDARD_VALIDATION_2026-08-18.json`

## Decision

Adopt `../../authority/governance/DOCUMENT_RECORDING_STANDARD.md` as the canonical universal recording/lifecycle rule for all Watchlist documentation.

## Required Semantics

1. Evidence final, issued decision, locked research, dan history/archive adalah immutable.
2. Strategy/governance menggunakan controlled revision.
3. Implementation, open finding lifecycle, README/index, tracker/ledger, dan audit docs boleh berubah tetapi semantic/material update harus traceable.
4. Evidence correction dibuat sebagai record baru, bukan rewrite.
5. Decision change dibuat sebagai superseding decision baru.
6. Locked research change dibuat sebagai research identity/version baru.
7. Historical ledger/session entry tidak boleh dihapus/ditulis ulang; correction dibuat sebagai append entry.
8. Material documentation event dicatat di `DOCUMENT_CHANGE_LOG.md`.
9. Existing historical/campaign corpus tidak wajib di-rename massal; standard berlaku untuk record baru dan material revision sejak adoption.

## Strategy Impact

Tidak ada perubahan behavior Weekly Swing. Decision ini hanya mengubah documentation governance dan recording discipline.

## Supersession

Decision ini tetap immutable setelah `ISSUED`. Jika recording policy perlu berubah secara material, buat decision baru yang secara eksplisit supersede decision ini.
