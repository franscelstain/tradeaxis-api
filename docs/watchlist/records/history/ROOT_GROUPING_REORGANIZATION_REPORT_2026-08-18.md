# Watchlist Root Documentation Architecture Reorganization Report

- **Date:** 2026-08-18
- **Scope:** `docs/watchlist/`
- **Finding:** `F-WS-20260818-05`
- **Decision:** `D-WS-20260818-05`
- **Change:** `DOC-CHG-20260818-005`
- **Validation Evidence:** `E-WS-20260818-04`
- **Verdict:** **PASS**

## Objective

Membuat root Watchlist langsung menunjukkan tiga fungsi yang berbeda tanpa membuat folder terlalu dalam:

```text
docs/watchlist/
├── README.md
├── START_HERE.md
├── authority/
├── development/
└── records/
```

Mental model: **Authority menentukan → Development mengerjakan → Records membuktikan/merekam.**

## Final Structure

```text
authority/
├── strategy/
└── governance/

development/
├── implementation/
├── research/
└── findings/

records/
├── evidence/
├── decisions/
└── history/
```

File counts setelah reorganisasi:

- authority: **48** files
- development: **227** files
- records: **639** files

## Role Corrections

Selain memindahkan delapan layer lama ke tiga root group, beberapa file ditempatkan ulang sesuai role sebenarnya:

- `LUMEN_CONTRACT_TRACKER.md` → `development/implementation/` karena tracker merupakan implementation status/contract working document, bukan governance authority.
- prior path-reorganization validation → `records/evidence/`.
- migration manifests/path-reorganization mappings → `records/history/`.

Canonical strategy tetap 14 owner files di `authority/strategy/`; strategy content tidak diubah oleh reorganisasi ini.

## Reference Alignment

Current references diperbarui pada root README/START_HERE, authority/governance, strategy index, implementation guidance, traceability matrix, cross-domain docs, and other active Markdown links. Historical archive payloads tidak ditulis ulang agar tetap merepresentasikan historical wording.

Current traceability matrix juga diperbarui sehingga `strategy_owner` menggunakan `authority/strategy/...`. Stable strategy rule IDs dan strategy semantics tidak berubah.

## Governance Trace

Prior authority/navigation snapshots disimpan sebagai:

- `H0179_DOC_ARCHITECTURE_PRE_ROOT_GROUPING.md`
- `H0180_OWNER_MATRIX_PRE_ROOT_GROUPING.md`
- `H0181_ROOT_README_PRE_ROOT_GROUPING.md`
- `H0182_START_HERE_PRE_ROOT_GROUPING.md`
- `H0183_GOVERNANCE_README_PRE_ROOT_GROUPING.md`

Old→new physical mapping: `records/history/ROOT_GROUPING_REORGANIZATION_2026-08-18.csv`.

## Validation

- root entries exactly expected: **PASS**
- old flat role directories remaining: **0**
- broken active Markdown links: **0**
- stale old absolute Watchlist path refs in active docs: **0**
- JSON parse errors: **0**
- CSV parse errors: **0**
- traceability rows: **1006**
- invalid traceability strategy-owner paths: **0**
- maximum relative path: **116 chars** (`docs/watchlist/development/research/C171_VERSIONED_OFFICIAL_BACKTEST_EVIDENCE_AND_EXECUTABLE_IS_STRAT_REMEDIATION.md`)
- Windows-safe target (`<=120` chars from repository docs root convention): **PASS**

## Result

Root Watchlist sekarang tidak lagi menampilkan delapan document roles sebagai peer. Programmer baru dapat mengetahui sifat folder sebelum membaca detail:

- **authority** — baca/patuhi;
- **development** — kerjakan/update secara traceable;
- **records** — simpan evidence/decision/history dan jangan gunakan sebagai working authority.

`README.md` dan `START_HERE.md` tetap single entry point dan seluruh current navigation menggunakan struktur baru.
