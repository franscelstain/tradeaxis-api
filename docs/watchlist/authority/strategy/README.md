# Watchlist Weekly Swing — Strategy

> **Physical role:** `docs/watchlist/authority/strategy/` — canonical behavior; controlled revision; bukan working implementation area.

> **Mulai dari:** [`../../START_HERE.md`](../../START_HERE.md). Folder ini adalah current canonical strategy authority.

Dokumen di sini hanya menjelaskan **apa yang harus dilakukan Weekly Swing**, bukan cara coding, hasil eksperimen, atau histori implementasi. Semua strategy file sengaja ditempatkan langsung di satu folder agar urutan baca mudah terlihat dari `../../START_HERE.md`; lifecycle `WS-S00..WS-S11` tetap menjadi urutan authoritative.

## Core direction

`trusted Market Data EOD → eligible candidates → immutable PLAN → qualified ranked TOP PICKS + EOD action intent → manual buy decision support`

Optional: `TOP PICK → next-trading-session CONFIRM`, non-blocking.

Canonical timing uses `effective_trade_date → NEXT_TRADING_SESSION`; `D+1` is not calendar arithmetic.

## Reading order

Ikuti Chapter 1–14 di [`../../START_HERE.md`](../../START_HERE.md). Orchestration owner: [`WS_END_TO_END_STRATEGY_LIFECYCLE.md`](WS_END_TO_END_STRATEGY_LIFECYCLE.md).

## Boundaries

- technical translation → `../../development/implementation/`
- research → `../../development/research/`
- actual evidence → `../../records/evidence/`
- findings → `../../development/findings/`
- decisions → `../../records/decisions/`
- archived/superseded material → `../../records/history/`
- governance → `../governance/`


## Recording / Mutability Rule

Canonical strategy menggunakan **CONTROLLED_REVISION**. Implementation progress/result tidak boleh dimasukkan ke strategy. Semantic revision hanya melalui material finding + traceable evidence + issued decision sesuai [`../governance/DOCUMENT_CHANGE_POLICY.md`](../governance/DOCUMENT_CHANGE_POLICY.md) dan [`../governance/DOCUMENT_RECORDING_STANDARD.md`](../governance/DOCUMENT_RECORDING_STANDARD.md).

## Implementation Coverage Navigation

Strategy files remain the business-rule owners. Rule-by-rule implementation/proof coverage is tracked outside strategy in governance:

- [`../governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](../governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md);
- [`../governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](../governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv).

Do not add implementation status/evidence back into strategy files merely to show coverage.
