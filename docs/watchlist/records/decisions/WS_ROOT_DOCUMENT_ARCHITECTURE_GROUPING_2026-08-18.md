# D-WS-20260818-05 — Adopt Authority / Development / Records Root Grouping

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** watchlist documentation architecture
- **Record ID:** D-WS-20260818-05
- **Created:** 2026-08-18
- **Related Finding:** F-WS-20260818-05

## Decision

Adopt tiga root group di bawah `docs/watchlist/`:

1. `authority/` — canonical authority yang stabil secara default (`../../authority/strategy`, `../../authority/governance`);
2. `development/` — working layers yang aktif berkembang secara traceable (`../../development/implementation`, `../../development/research`, `../../development/findings`);
3. `records/` — factual/issued/historical records (`../evidence`, `.`, `../history`).

`README.md` dan `START_HERE.md` tetap berada langsung di `docs/watchlist/` sebagai single entry/navigation.

## Constraints

- tidak mengubah Weekly Swing business strategy;
- tidak mengubah evidence/decision/history semantics;
- tidak membuat `active/`, `inactive/`, atau status-based folders yang mengharuskan file sering dipindah;
- seluruh current link/path reference harus mengikuti lokasi baru;
- historical content boleh mempertahankan historical path wording bila merupakan bagian dari record masa lalu;
- path harus tetap Windows-safe dan struktur tidak boleh menambah child folder tanpa kebutuhan fungsional.

## Outcome

Current documentation architecture menggunakan `authority -> development -> records` sebagai visual lifecycle: authority menentukan, development mengerjakan, records membuktikan/merekam.
