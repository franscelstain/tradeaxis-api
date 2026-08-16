# 01 — WS Overview

> **Doc Role:** CANONICAL WEEKLY SWING STRATEGY
> **Change rule:** Stable by default; revision requires material finding + evidence + decision per `../../governance/DOCUMENT_CHANGE_POLICY.md`.

## Purpose

Dokumen ini memberikan gambaran tingkat tinggi mengenai Weekly Swing watchlist, termasuk tujuan strategy, boundary antar output, urutan baca strategy, dan peta owner normatif antar dokumen strategy.

Weekly Swing watchlist memiliki tiga lapisan output yang berbeda:

1. **PLAN**
2. **RECOMMENDATION**
3. **CONFIRM**

Dokumen ini bukan owner formula detail PLAN, RECOMMENDATION, atau CONFIRM, tetapi menetapkan posisi ketiga lapisan tersebut secara tegas dalam arsitektur Weekly Swing.

## Scope

Dokumen ini mencakup:
- gambaran strategy Weekly Swing sebagai domain watchlist;
- boundary antara PLAN, RECOMMENDATION, dan CONFIRM;
- daftar owner strategy normatif;
- core strategy reading order.

Dokumen ini tidak mencakup:
- data model, schema, DDL/SQL, reason code, test, fixture, command, atau procedure implementation;
- hasil eksperimen, hasil backtest/OOS/shadow/runtime, atau operator evidence;
- histori campaign atau superseded material;
- execution order placement, transaksi aktual, atau portfolio lifecycle setelah beli.

## Weekly Swing Architecture (High Level)

Weekly Swing bekerja dalam urutan konseptual berikut:

1. **PLAN** dibentuk dari EOD input yang sah;
2. **RECOMMENDATION** dibentuk dari PLAN immutable;
3. **CONFIRM** dapat dijalankan terhadap ticker yang masih valid sebagai candidate PLAN.

Boundary canonical:
- RECOMMENDATION hanya membaca PLAN;
- RECOMMENDATION tidak membaca CONFIRM;
- CONFIRM membaca PLAN candidate binding;
- CONFIRM tidak membentuk recommendation;
- CONFIRM tidak mengubah recommendation.

## Recommendation in Weekly Swing

RECOMMENDATION adalah lapisan watchlist, bukan lapisan execution.

Peran RECOMMENDATION adalah:
- memilih subset ticker paling layak disarankan dari hasil PLAN;
- memberi ranking recommendation;
- memberi label recommendation;
- secara opsional memberi suggestion lot dalam mode capital-aware.

RECOMMENDATION dapat tersedia walaupun CONFIRM belum ada.

RECOMMENDATION dapat kosong walaupun `TOP_PICKS` dan/atau `SECONDARY` pada PLAN tidak kosong.

## Canonical Strategy Ownership Map

### Scope and orientation
- `00_WS_SCOPE_LOCK.md`
- `01_WS_OVERVIEW.md`

### Canonical flow
- `02_WS_CANONICAL_RUNTIME_FLOW.md` — owner urutan `PLAN -> RECOMMENDATION -> CONFIRM`

### PLAN
- `08_WS_PLAN_ALGORITHM.md`
- `09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`

### RECOMMENDATION
- `22_WS_RECOMMENDATION_OVERVIEW.md`
- `24_WS_RECOMMENDATION_ALGORITHM.md`

### CONFIRM
- `10_WS_CONFIRM_OVERLAY.md`

### Backtest / calibration strategy and acceptance
- `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`
- `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
- `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

Technical translation untuk schema, persistence, reason code, verification contract, test, procedure, fixture, artifact, dan module/API mapping berada di `../../implementation/` dan bukan owner strategy.

## Core Strategy Reading Order

1. `00_WS_SCOPE_LOCK.md`
2. `01_WS_OVERVIEW.md`
3. `02_WS_CANONICAL_RUNTIME_FLOW.md`
4. `08_WS_PLAN_ALGORITHM.md`
5. `09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
6. `22_WS_RECOMMENDATION_OVERVIEW.md`
7. `24_WS_RECOMMENDATION_ALGORITHM.md`
8. `10_WS_CONFIRM_OVERLAY.md`
9. `12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`
10. `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
11. `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

## Final Rules

1. Weekly Swing memiliki tiga lapisan output utama: PLAN, RECOMMENDATION, dan CONFIRM.
2. RECOMMENDATION adalah lapisan watchlist yang dibentuk dari PLAN immutable.
3. RECOMMENDATION tidak membutuhkan CONFIRM agar dapat tersedia.
4. RECOMMENDATION dapat kosong walaupun group prioritas PLAN tidak kosong.
5. CONFIRM berlaku terhadap candidate PLAN yang sah, bukan hanya terhadap ticker recommended.
6. CONFIRM tidak membentuk dan tidak mengubah recommendation.
