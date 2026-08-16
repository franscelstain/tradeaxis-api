# 01 — WS Overview

## Purpose

Dokumen ini memberikan gambaran tingkat tinggi mengenai Weekly Swing watchlist, termasuk tujuan strategy dan boundary antar output.

Weekly Swing watchlist memiliki tiga lapisan output yang berbeda:

1. **PLAN**
2. **RECOMMENDATION**
3. **CONFIRM**

Dokumen ini menetapkan posisi PLAN, RECOMMENDATION, dan CONFIRM secara tegas dalam arsitektur Weekly Swing.

## Scope

Dokumen ini mencakup:
- gambaran strategy Weekly Swing sebagai domain watchlist;
- boundary antara PLAN, RECOMMENDATION, dan CONFIRM;
- posisi recommendation sebagai hasil prioritisasi kandidat Weekly Swing.

Dokumen ini tidak mencakup:
- execution order placement atau transaksi aktual;
- portfolio lifecycle setelah beli;
- pengelolaan internal Market Data.

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

## Final Rules

1. Weekly Swing memiliki tiga lapisan output utama: PLAN, RECOMMENDATION, dan CONFIRM.
2. RECOMMENDATION adalah lapisan watchlist yang dibentuk dari PLAN immutable.
3. RECOMMENDATION tidak membutuhkan CONFIRM agar dapat tersedia.
4. RECOMMENDATION dapat kosong walaupun group prioritas PLAN tidak kosong.
5. CONFIRM berlaku terhadap candidate PLAN yang sah, bukan hanya terhadap ticker recommended.
6. CONFIRM tidak membentuk dan tidak mengubah recommendation.
