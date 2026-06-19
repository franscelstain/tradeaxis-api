# Watchlist Audit Update Governance

## Purpose

Dokumen ini mengunci cara update audit, status, dan contract tracker untuk domain `watchlist` agar pengembangan tidak berjalan tanpa jejak, tidak overclaim, dan tetap tunduk pada owner docs yang benar.

Dokumen ini adalah governance audit/update. Dokumen ini **bukan** owner business rule watchlist. Behavioral owner tetap berada pada `docs/watchlist/system/policy.md` dan dokumen normatif `docs/watchlist/system/policies/weekly_swing/**`.

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

- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`.

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

## C29 OOS Proof Governance Addendum

C29 is governed as an OOS proof session only.

Source artifact lock:

```text
INPUT_C28_ARTIFACT=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
EXPECTED_C28_HASH=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
ACTUAL_C28_HASH=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
CANDIDATE_PROFILE_CODE=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
```

Validation command:

```text
watchlist:backtest-c29-oos-proof
```

Output artifact:

```text
storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
artifact_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
status=C29_OOS_PROOF_FAILED
```

Final C29 evidence rules:

- C29 may claim OOS failure because the operator ran the runtime command against the locked C28 artifact and produced the C29 artifact.
- C29 may not claim production readiness because the runtime result is `C29_OOS_PROOF_FAILED` and `production_ready=0`.
- C29 may not use OOS metrics to retune, reselect a profile, or create a best-of-OOS binding.
- C29 may not create or promote a production catalog.
- C29 may not mutate C01-C28, R1/R2, or PLAN/CONFIRM behavior.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.
- The four C29 rows counted by the lookahead gate are documented as missing D1-D5 raw OHLC path rows; they must not be overclaimed as proven future-return selection leakage unless C30 proves it.

Operator validation evidence:

```text
PHPUNIT_C29=PASS: OK (13 tests, 132 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (448 tests, 10900 assertions)
C29_RUNTIME=FAIL: status=C29_OOS_PROOF_FAILED
C29_ARTIFACT_HASH=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
```

C30 governance requirement:

```text
NEXT_STEP=C30_OOS_FAILURE_ATTRIBUTION_AND_DATA_COMPLETENESS_DIAGNOSTIC
DO_NOT_TUNE_FROM_OOS=true
DO_NOT_CREATE_BEST_OF_OOS=true
DO_NOT_PROMOTE_PRODUCTION_CATALOG=true
SPLIT_ACTUAL_LOOKAHEAD_LEAK_FROM_MISSING_PATH_ROWS=true
SPLIT_EVALUABLE_FROM_NON_EVALUABLE_ROWS=true
```

Operator validation rule:

```text
PHPUNIT_C29_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C29_RUNTIME_PASS_REQUIRES_ACTUAL_ARTIFACT=true
C29_RUNTIME_FAIL_REQUIRES_ACTUAL_ARTIFACT=true
C29_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

## C30 OOS Failure Attribution Governance Addendum

C30 is governed as failure attribution only. It must not retune, reselect, promote, create best-of-OOS, or change PLAN/CONFIRM production behavior.

Source artifact lock:

```text
INPUT_C29_ARTIFACT=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
EXPECTED_C29_HASH=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
EXPECTED_C29_STATUS=C29_OOS_PROOF_FAILED
```

Validation command:

```text
watchlist:backtest-c30-oos-failure-attribution
```

Output artifact:

```text
storage/app/watchlist/backtest/c30-oos-failure-attribution.json
```

Evidence rules:

- C30 may claim completed attribution only from the official C30 command output or a committed artifact generated by that command.
- C30 may not claim PHPUnit PASS unless the C30 PHPUnit command is actually run.
- C30 may not claim full Watchlist PHPUnit PASS unless the full command is actually run.
- C30 may not infer artifact hash; hash must be read from the created C30 artifact or file hash command.
- C30 may not use C29 OOS returns to tune or select a profile.
- `production_ready` must remain `false/0` in source, artifact, docs, and final response.

Operator validation rule:

```text
PHPUNIT_C30_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
FULL_WATCHLIST_PASS_REQUIRES_ACTUAL_COMMAND_OUTPUT=true
C30_RUNTIME_COMPLETED_REQUIRES_ACTUAL_ARTIFACT=true
C30_RUNTIME_BLOCKED_REQUIRES_EXPLICIT_DIAGNOSTIC=true
```

Missing-path vs actual lookahead classification rule:

```text
MISSING_PATH_ROWS=missing_path_data_flag=true OR raw_ohlc_validated_flag=false OR missing_path_reason_code is not null
SELECTION_LEAK_ROWS=future_path_price_used_for_selection=true OR profile_ret_net_used_for_selection=true OR derived_mfe_mae_used_for_execution=true
ACTUAL_LOOKAHEAD_ROWS=lookahead_safe=false AND NOT missing_path OR explicit future-data leak reason
MISSING_PATH_ROWS_MUST_NOT_BE_OVERCLAIMED_AS_ACTUAL_LOOKAHEAD_LEAK=true
```

C30 final operator status:

```text
PHPUNIT_C30=PASS
PHPUNIT_C30_RESULT=OK (16 tests, 104 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (464 tests, 11004 assertions)
C30_RUNTIME=COMPLETED
C30_FINAL_STATUS=C30_ATTRIBUTION_COMPLETED
C30_ARTIFACT_PATH=storage/app/watchlist/backtest/c30-oos-failure-attribution.json
C30_ARTIFACT_HASH=667b639951d6b566cc9b0fa6cf7dc278db92a8f0
C30_ATTRIBUTION_VERDICT=MIXED_DATA_AND_STRATEGY_FAILURE
ACTUAL_LOOKAHEAD_VIOLATION_COUNT=0
SELECTION_LEAK_COUNT=0
MISSING_PATH_COUNT=4
NON_EVALUABLE_PICK_COUNT=4
CLEAN_EVALUABLE_PICK_COUNT=128
production_ready=0
```

C30 governance decision: C29's reported lookahead violations must be treated as missing-path/data-completeness rows unless a later controlled proof shows an explicit actual leak. C31 must split lookahead gate from data-completeness gate and must not tune from OOS.


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

- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`;
- owner docs terkait di `docs/watchlist/system/**` bila behavior normatif berubah;
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

Session: `WATCHLIST — AUDIT GOVERNANCE + LUMEN TRACKER FOUNDATION`

Status: `DONE` for governance foundation only.

Scope completed:

- audit update governance created;
- Lumen implementation status tracker created;
- Lumen contract tracker created;
- owner hierarchy baseline recorded;
- market-data dependency rule recorded;
- anti-overclaim rule recorded.

Watchlist implementation status remains `FOUNDATION_STARTED / NOT_IMPLEMENTED / NOT_READY`.
