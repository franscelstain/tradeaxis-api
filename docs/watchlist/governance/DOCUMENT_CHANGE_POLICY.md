# Watchlist Document Change Policy

> **Status:** CANONICAL GOVERNANCE

## Core Rule

Canonical Weekly Swing strategy **tidak boleh diubah hanya karena implementasi sedang berjalan**. Perubahan code, refactor, test result, command result, catalog number, audit session, atau progress status harus dicatat pada implementation/evidence/history sesuai perannya.

## Strategy Change Is Allowed Only When

Semua kondisi berikut tersedia:

1. ada finding yang material atau evidence baru yang relevan;
2. evidence dapat ditelusuri;
3. dampak terhadap tujuan Weekly Swing dijelaskan;
4. ada decision record yang menerima perubahan;
5. strategy document yang terdampak disebut eksplisit;
6. jika behavior berubah, versi sebelumnya dipindahkan/diarsipkan sebagai superseded.

## Changes That Do Not Require Strategy Revision

- refactor class/service/repository;
- perubahan nama command internal;
- optimasi query tanpa behavior change;
- penambahan unit/integration test;
- perbaikan bug implementation agar kembali sesuai strategy;
- penambahan evidence IS/OOS/shadow/runtime;
- audit/session status;
- SHA1/artifact path/operator output.

## Changes That May Require Strategy Revision

- evidence menunjukkan acceptance/gate canonical tidak lagi memadai;
- real-world execution assumptions membuat expected edge tidak valid;
- data contract upstream berubah secara material;
- OOS/forward evidence mengungkap kelemahan fundamental rule;
- finding membuktikan ranking/risk/entry/exit semantics tidak mencapai objective Weekly Swing.

## Status Semantics

Strategy lifecycle: `DRAFT -> CANONICAL -> SUPERSEDED`.

`PASS`, `FAILED`, `IMPLEMENTED`, `TESTED`, `PRODUCTION_READY`, dan status session lain adalah status implementation/evidence/decision, bukan lifecycle strategy.

## Supersession Rule

Strategy lama tidak dihapus. Jika diganti karena keputusan material, pindahkan salinan authoritative sebelumnya ke `history/weekly_swing/superseded/` dan hubungkan ke decision record yang menggantikannya.
