# Watchlist Executable Documentation Integrity Gate Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** current Watchlist documentation authority, implementation orchestration, traceability, packaging, dan attempt closure  
> **Purpose:** mengubah aturan dokumentasi penting dari sekadar "patut dibaca" menjadi pemeriksaan executable yang berulang dan dapat dibuktikan.

## 1. Core Rule

Dokumentasi tidak boleh dianggap structurally conformant hanya karena dibaca manual.

Executable gate wajib dijalankan:

1. sebelum membuka implementation attempt (`PRE_ATTEMPT`);
2. setelah material documentation/contract update (`POST_CHANGE`);
3. sebelum attempt evidence diterbitkan (`PRE_ATTEMPT_CLOSE`);
4. sebelum implementation stage `DONE` (`PRE_STAGE_DONE`);
5. sebelum ZIP/handoff/source-of-truth package diterbitkan (`PRE_PACKAGE`).

Executable implementation:

[`../../development/implementation/tests/WatchlistDocumentationIntegrityGate.php`](../../development/implementation/tests/WatchlistDocumentationIntegrityGate.php)

## 2. Required Checks

Gate minimum memeriksa:

- root architecture `authority / development / records`;
- active Markdown local-link integrity;
- JSON parse validity;
- CSV structural validity;
- Windows-safe relative path length;
- duplicate numbered governance sections;
- stale active legacy path/filename references yang sudah diganti;
- duplicate material change IDs, dengan hanya narrow registered legacy exception yang diizinkan;
- canonical strategy traceability matrix:
  - unique `rule_id`;
  - strategy owner exists;
  - source line exists;
  - source clause matches;
  - SHA1 fingerprint matches `rule_text`;
- stage-register lifecycle vocabulary;
- optional baseline manifest structure/fingerprint ketika `--baseline` diberikan.

Gate dapat bertambah seiring finding nyata, tetapi tidak boleh diam-diam melemahkan check agar package terlihat PASS.

## 3. Gate Verdict

Allowed verdict:

- `PASS`
- `PASS_WITH_REGISTERED_LEGACY_EXCEPTION`
- `FAIL`

`FAIL` pada `PRE_ATTEMPT_CLOSE`, `PRE_STAGE_DONE`, atau `PRE_PACKAGE` memblokir closure/package claim sampai masalah diperbaiki atau mempunyai narrow governance exception yang sah.

## 4. Exception Rule

Exception hanya untuk **legacy integrity defect yang harus dipertahankan karena immutable/append-only history**, bukan untuk current strategy/implementation defect.

Canonical registry:

[`DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json`](DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json)

Setiap exception wajib mempunyai:

- stable exception ID;
- exact check name;
- exact target;
- alasan objektif;
- finding/decision/evidence lineage;
- explicit disposition.

Wildcard exception atau "ignore all" dilarang.

Exception tidak boleh digunakan untuk:

- harmful implementation residue;
- missing mandatory strategy coverage;
- invalid evidence;
- broken current authority;
- stage acceptance failure.

## 5. Baseline-aware Mode

Jika `--baseline=<path>` diberikan, gate juga memeriksa:

- baseline JSON valid;
- mandatory identity fields tersedia;
- baseline mode valid;
- authority lock entries mempunyai path + SHA1;
- current locked authority files masih cocok dengan SHA1 baseline;
- matrix SHA1 masih cocok;
- Baseline/Attempt/Stage IDs tersedia.

Mismatch menghasilkan `BASELINE_DRIFT` dan gate `FAIL` untuk closure sampai disposition/rebaseline dilakukan.

## 6. Command

Dari repository root:

```bash
php docs/watchlist/development/implementation/tests/WatchlistDocumentationIntegrityGate.php
```

Dengan baseline:

```bash
php docs/watchlist/development/implementation/tests/WatchlistDocumentationIntegrityGate.php \
  --baseline=docs/watchlist/records/evidence/runs/E-WS-YYYYMMDD-NN_WORK_BASELINE_LOCK.json
```

Optional JSON report:

```bash
php docs/watchlist/development/implementation/tests/WatchlistDocumentationIntegrityGate.php \
  --output=storage/app/watchlist/audit/watchlist-doc-integrity.json
```

Exit code:

- `0` = PASS / PASS_WITH_REGISTERED_LEGACY_EXCEPTION;
- non-zero = FAIL.

## 7. Recording

Final gate output yang dipakai untuk closure harus disimpan sebagai evidence atau menjadi bagian dari immutable attempt evidence. Jangan hanya menulis "gate PASS" tanpa output/command identity.

Stage register menyimpan summary/pointer saja.

## 8. Hard Rule

Manual review tetap penting untuk semantic correctness, tetapi manual review **tidak menggantikan executable gate** untuk checks yang sudah dapat diverifikasi otomatis.
