# Watchlist Audit Update Governance

## Purpose

Dokumen ini mengunci cara update audit, status, dan contract tracker untuk domain `watchlist` agar pengembangan tidak berjalan tanpa jejak, tidak overclaim, dan tetap tunduk pada owner docs yang benar.

Dokumen ini adalah governance audit/update. Dokumen ini **bukan** owner business rule watchlist. Behavioral owner tetap berada pada `docs/watchlist/governance/WATCHLIST_DOCUMENT_AUTHORITY.md` dan dokumen normatif `docs/watchlist/strategy/weekly_swing/**`.

## Source of Truth Rule

- ZIP terbaru yang diberikan operator adalah source of truth sesi.
- Semua keputusan update harus membaca file aktual di ZIP, bukan asumsi dari sesi sebelumnya.
- Jika file target sudah ada, baca dulu lalu update append-only kecuali koreksi minor diperlukan untuk menghapus kontradiksi eksplisit.
- Dokumen audit tidak boleh menggantikan policy owner.
- Dokumen system/policy tetap menjadi behavioral owner.
- Dokumen `LUMEN_IMPLEMENTATION_STATUS.md` hanya mencatat progress, bukan membuat kontrak baru tanpa referensi.
- Dokumen `LUMEN_CONTRACT_TRACKER.md` mengunci kontrak berdasarkan policy/system docs dan dependency upstream yang sah.

## Append-Only Update Rule

Setiap sesi baru harus menambahkan catatan baru dengan:

- nama sesi;
- tanggal update;
- status sesi;
- file dibuat/diubah;
- bukti validasi;
- gap tersisa;
- next session recommendation.

Update boleh mengubah ringkasan paling atas hanya untuk mencerminkan status terbaru, tetapi riwayat sesi lama tidak boleh dihapus.

## Active Session Rule

Harus selalu ada satu section `ACTIVE SESSION` pada:

- `docs/watchlist/evidence/weekly_swing/ledgers/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/governance/trackers/LUMEN_CONTRACT_TRACKER.md`.

Nama active session pada kedua file harus sama. Jika tidak sama, docs sync dianggap gagal.

## Status Taxonomy

| Status | Meaning |
|---|---|
| `NOT_STARTED` | Belum ada implementasi, test, atau runtime proof. |
| `IN_PROGRESS` | Sedang dikerjakan dan belum selesai. |
| `PARTIAL` | Sebagian terpenuhi tetapi masih ada gap substantif. |
| `REVIEW_REQUIRED` | Perlu review ulang karena ada risiko, konflik, atau bukti belum cukup. |
| `BLOCKED` | Tidak bisa dilanjutkan karena dependency, file, runtime, atau keputusan belum tersedia. |
| `DONE` | Scope sesi spesifik selesai sesuai acceptance criteria. Bukan berarti seluruh watchlist siap produksi. |
| `LOCKED` | Kontrak sudah dikunci oleh code, test, runtime proof, artifact, dan docs sync. |
| `SUPERSEDED` | Digantikan oleh kontrak atau sesi yang lebih baru. |
| `FOUNDATION_STARTED` | Baseline dokumen/tracker dibuat, tetapi implementasi utama belum dimulai. |
| `NOT_IMPLEMENTED` | Fitur utama belum ada pada codebase. |
| `NOT_READY` | Belum layak diklaim siap produksi. |

## Severity Taxonomy

| Severity | Meaning |
|---|---|
| `BLOCKER` | Menghalangi klaim readiness atau next implementation. |
| `HIGH_RISK` | Bisa menyebabkan behavior salah, data salah, atau kontrak terbypass. |
| `MEDIUM_RISK` | Risiko nyata tetapi tidak langsung merusak invariant utama. |
| `MINOR` | Perbaikan kecil, wording, atau struktur non-kritis. |
| `DOCS_ONLY` | Perubahan dokumentasi tanpa perubahan runtime. |
| `RUNTIME_PROOF_MISSING` | Code/test mungkin ada, tetapi bukti runtime belum tersedia. |
| `ACCEPTABLE_LIMITATION` | Batasan sadar yang boleh diterima dan harus dicatat. |
| `EXTERNAL_DEPENDENCY` | Bergantung pada provider, upstream, environment, atau keputusan di luar modul watchlist. |

## Evidence Rule

Setiap klaim status wajib punya evidence yang jelas:

- file path aktual;
- ringkasan perubahan;
- test command dan hasil;
- runtime command dan hasil jika sudah ada runtime;
- artifact/log path jika ada;
- alasan jika validasi hanya static/docs.

Tanpa evidence, klaim maksimal hanya `PARTIAL` atau `REVIEW_REQUIRED`.

## Code / Test / Docs / Runtime Proof Rule

- Docs selesai tidak sama dengan fitur selesai.
- Test static guard tidak sama dengan runtime proof.
- PHPUnit PASS untuk unit test tidak sama dengan production readiness jika runtime command belum dibuktikan.
- Runtime proof harus berasal dari command/API nyata milik watchlist saat fitur tersebut sudah dibuat.
- Jika hanya docs yang berubah, validasi minimal adalah file existence check, grep/check anti-overclaim, dan static guard docs bila tersedia.

## Anti-Overclaim Rule

Watchlist tidak boleh diklaim production-ready hanya karena dokumen selesai.

Watchlist tidak boleh diklaim production-ready tanpa seluruh bukti berikut:

- code utama tersedia;
- database/schema runtime tersedia bila diperlukan;
- test unit/integration/static guard tersedia;
- runtime command/API proof tersedia;
- artifact/log audit tersedia;
- docs sync selesai;
- dependency market-data terbukti valid melalui consumer read contract.

Klaim `DONE` hanya boleh merujuk pada scope sesi spesifik. Klaim `LOCKED` hanya boleh dipakai untuk kontrak yang benar-benar sudah punya code, test, runtime proof, dan docs sync.

## Docs Synchronization Rule

Setiap perubahan pada code, config, schema, test, behavior, command, API, atau artifact watchlist wajib disinkronkan ke:

- `docs/watchlist/evidence/weekly_swing/ledgers/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/governance/trackers/LUMEN_CONTRACT_TRACKER.md`;
- owner docs terkait di `docs/watchlist/strategy/**` bila behavior normatif berubah;
- audit docs/checklist terkait bila acceptance atau governance berubah.

Jika perubahan hanya docs foundation, status harus tetap jujur sebagai foundation, bukan implementation readiness.

## Market-Data Dependency Rule

Watchlist depends on market-data as its official data source.

Watchlist must consume:

- sealed publication;
- `SUCCESS` run;
- `READABLE` publication;
- coverage `PASS`;
- valid current publication pointer;
- valid publication/run mirror;
- valid indicator rows;
- valid eligibility rows.

Watchlist must not consume:

- raw provider response;
- raw staging table;
- unsealed `eod_bars`;
- unsealed `eod_indicators`;
- unsealed `eod_eligibility`;
- `MAX(trade_date)` shortcut;
- latest available row without publication pointer;
- indicator rows with required null values;
- invalid indicator rows.

Market-data production-ready does not automatically make watchlist production-ready. Watchlist must prove its own read contract, scoring contract, backtest contract, artifact contract, and runtime behavior.

## Consumer Read Safety Rule

Watchlist read-side code must be publication-aware and fail-safe.

Forbidden patterns for watchlist consumer reads:

- raw/latest/MIN/MAX shortcuts for trade date selection;
- direct raw table reads as an authoritative source;
- fallback to staging/provider rows;
- silently accepting missing required indicators;
- silently accepting invalid eligibility;
- inventing an effective date when pointer resolution fails;
- using market-data internals as a replacement for producer-facing consumer contract.

## Backtest Integrity Rule

Backtest must be deterministic, reproducible, and free from lookahead bias.

Forbidden backtest behavior:

- using future publication;
- using future indicator;
- using future price;
- using future eligibility;
- using final known dataset as if it existed historically;
- mixing paramset/code labels without traceable artifact;
- omitting universe, date range, or dataset identity from evidence.

## Watchlist Implementation Update Rule

Every watchlist implementation session must update:

1. implementation status;
2. contract tracker;
3. relevant owner docs if behavior changed;
4. validation log;
5. gap/next-session section.

Implementation work must not start by creating scoring/recommendation/backtest logic before the market-data consumer read model contract is created and guarded.

## `LUMEN_IMPLEMENTATION_STATUS.md` Update Rule

This file must record:

- current active session;
- current implementation status;
- existing docs discovered;
- file/code/test/runtime changes;
- validation evidence;
- active gaps;
- next required session;
- production readiness status.

It must never claim the whole watchlist is ready while core code remains `NOT_STARTED` or runtime proof is missing.

## `LUMEN_CONTRACT_TRACKER.md` Update Rule

This file must record for each contract:

- contract ID;
- title;
- status;
- owner docs;
- implementation files;
- tests;
- runtime proof;
- current gaps;
- acceptance criteria;
- last update.

Contract status may only move to `LOCKED` after implementation, tests, runtime proof, artifacts, and docs sync are all valid.

## Final Readiness Claim Rule

Final readiness may only be claimed after all of the following are true:

- market-data consumer read contract is locked;
- no raw/latest/`MAX(date)` bypass exists;
- required indicators and eligibility guards are enforced;
- scoring is deterministic and explainable;
- paramset/code traceability is recorded;
- recommendation output has acceptance tests;
- backtest is no-lookahead and reproducible;
- risk/liquidity/volatility gates exist;
- artifacts/logs are generated;
- full watchlist test suite passes;
- runtime command/API proof passes;
- docs are synchronized;
- contract tracker marks all readiness-critical contracts as `LOCKED`.

## Initial Governance Baseline

Session: `WATCHLIST â€” AUDIT GOVERNANCE + LUMEN TRACKER FOUNDATION`

Status: `DONE` for governance foundation only.

Scope completed:

- audit update governance created;
- Lumen implementation status tracker created;
- Lumen contract tracker created;
- owner hierarchy baseline recorded;
- market-data dependency rule recorded;
- anti-overclaim rule recorded.

Watchlist implementation status remains `FOUNDATION_STARTED / NOT_IMPLEMENTED / NOT_READY`.

---

## Database Dictionary Required Rule

Every Watchlist session that touches database-connected data must read the database dictionary before implementation:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md
```

This rule applies to PLAN, CONFIRM, backtest, audit diagnostics, source reconstruction, regime reconstruction, sector metadata, market-index data, eligibility, and future features.

Mandatory behavior:

- Identify all tables touched.
- Confirm date key and identifier key.
- Confirm whether fields are pre-trade safe, evaluation-only, or forbidden for selection.
- Never infer DB column names from memory.
- Never use unbounded `MAX(trade_date)` for as-of lookup.
- Never use OOS rows/returns/bad months for IS tuning or candidate selection.
- If dictionary coverage is missing, update dictionary/governance or mark session blocked.

C57-proven mapping:

```text
market_index_roc20 => market_benchmark_indicators.roc_20 where benchmark_code='IHSG'
market_index_ma20_slope_pct => market_benchmark_indicators.ma20_slope_pct where benchmark_code='IHSG'
market_calendar date key => cal_date
```


## Historical campaign governance
All C/R/S/P/B01 campaign-specific governance appendices were moved to `../../history/weekly_swing/governance_addenda/AUDIT_UPDATE_GOVERNANCE_CAMPAIGN_HISTORY.md`. The historical file is evidence/governance history and does not create current strategy.
