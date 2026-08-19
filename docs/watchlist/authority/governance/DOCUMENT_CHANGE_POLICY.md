# Watchlist Document Change Policy

> **Status:** CANONICAL GOVERNANCE

## Scope

Dokumen ini khusus mengatur **canonical Weekly Swing strategy change**. Universal mutability, correction, decision, evidence, research, implementation, ledger, README, dan history lifecycle diatur oleh [`DOCUMENT_RECORDING_STANDARD.md`](DOCUMENT_RECORDING_STANDARD.md).

## Core Rule

Canonical Weekly Swing strategy **tidak boleh diubah hanya karena implementasi sedang berjalan**. Perubahan code, refactor, test result, command result, catalog number, audit session, atau progress status harus dicatat pada implementation/evidence/history sesuai perannya.

## Strategy Change Is Allowed Only When

Semua kondisi berikut tersedia:

1. ada finding yang material atau evidence baru yang relevan;
2. evidence dapat ditelusuri;
3. dampak terhadap tujuan Weekly Swing dijelaskan;
4. ada issued decision record yang menerima perubahan;
5. strategy document yang terdampak disebut eksplisit;
6. jika behavior berubah, authoritative version sebelumnya dipertahankan sebagai superseded/history;
7. material document event dicatat pada `DOCUMENT_CHANGE_LOG.md`.

## Changes That Do Not Require Strategy Revision

- refactor class/service/repository;
- perubahan nama command internal;
- optimasi query tanpa behavior change;
- penambahan unit/integration test;
- perbaikan bug implementation agar kembali sesuai strategy;
- penambahan evidence IS/OOS/shadow/runtime;
- audit/session status;
- SHA1/artifact path/operator output.

Perubahan tersebut tetap tunduk pada recording rule layer masing-masing. Material implementation contract change tetap harus traceable walaupun strategy tidak berubah.

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

Strategy lama tidak dihapus. Jika diganti karena keputusan material, pertahankan salinan authoritative sebelumnya di history/archive dan hubungkan ke decision record yang menggantikannya.

Issued decision tidak boleh diedit untuk mengubah hasil keputusan; gunakan decision baru yang supersede keputusan sebelumnya.

## Strategy Parameter / Paramset Rule

Canonical strategy documents own **behavioral semantics and allowed parameter meaning**, not every promoted numeric value.

A numeric threshold/weight/freshness/drift/cost setting may change without rewriting canonical strategy prose only when:
- parameter key and semantics already exist in canonical strategy;
- change is preregistered/researched and supported by evidence;
- exact value is frozen in a new versioned strategy/paramset identity;
- required IS/OOS/stress/shadow proof is repeated according to impact;
- promotion/activation has an explicit issued decision record.

Such a paramset change is still a **strategy-identity change for evaluation**, even when it is not a **strategy-document semantic revision**.

If a change introduces a new feature family, changes score meaning, changes entry/exit behavior, changes qualification semantics, changes proof object, or removes/adds a canonical gate type, the canonical strategy owner documents must be revised through the full strategy-change process.
