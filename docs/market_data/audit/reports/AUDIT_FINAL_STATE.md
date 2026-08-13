# Audit Final State — Market-Data Data-Readiness Strategy

## Document role

Dokumen ini adalah **satu-satunya laporan audit kanonik** untuk state aktif `docs/market_data`. Scope penutupan saat ini adalah kesiapan **dokumentasi strategi** sebagai panduan pembangunan market-data IDX Regular-Market EOD yang valid dan stabil.

Audit dokumentasi memeriksa:

1. strategi terhadap praktik pasar nyata yang relevan untuk data saham IDX Regular-Market EOD;
2. konsistensi scope, terminology, invariants, data products, schema meaning, proof specification, dan operations contracts;
3. kecukupan urutan pembangunan agar implementasi berikutnya tidak perlu menebak keputusan domain.

Code, migration runtime, database state, executed tests, scheduler, dan operational evidence **bukan syarat kelulusan dokumentasi**. Temuan implementasi yang masih dicatat di bagian bawah adalah handoff backlog untuk pembangunan dan audit berikutnya, bukan penurun status dokumentasi.

Behavior normatif dimiliki owner contracts. Urutan kerja aktual `W00`–`W22` dimiliki `book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`; assignment seluruh dokumen/deliverable/proof dimiliki `book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`; command/result/remediation lifecycle dimiliki `book/Market_Data_Implementation_Command_Protocol_LOCKED.md`; current state disimpan di `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`. Audit ini tidak menciptakan behavior paralel.

Initial audit date: `2026-08-02`  
Latest documentation-strategy revalidation: `2026-08-08`

Package: `docs/market_data`

Initial consumer profile: watchlist dengan policy Weekly Swing — bukan acceptance authority market-data

Target horizon: EOD, holding period beberapa hari sampai beberapa minggu

Audit classification:

- dominant layer: **Layer A — strategy and owner contracts**;
- current closure coverage: **Layer A + normative Layer B guidance**;
- Layer C implementation readiness: **not claimed by documentation closure**;
- report role: canonical documentation-strategy verdict and implementation handoff boundary.

---

## Dataset time boundary and development-phase interpretation

### Intentional dataset start

`2023-01-02` adalah **intentional dataset start** untuk baseline awal aplikasi ini.

Artinya:

- tanggal tersebut dipilih sebagai awal historical data yang dibangun, bukan akibat source gagal menyediakan seluruh sejarah sebelum 2023;
- tidak adanya data sebelum `2023-01-02` bukan missing-data bug pada scope aktif;
- pipeline, replay, indicator, dan backtest hanya boleh mengklaim coverage mulai dari dataset boundary tersebut;
- indicator yang membutuhkan warm-up harus menghasilkan deterministic `NULL` pada tanggal-tanggal awal sampai history masing-masing cukup;
- ticker yang listing setelah dataset start mengumpulkan warm-up dari listed date-nya sendiri;
- historical expansion ke tanggal sebelum `2023-01-02` boleh dilakukan kelak bila ada manfaat nyata, tetapi bukan pekerjaan atau blocker sekarang.

Dataset start harus dibedakan dari archived proof window. Proof window `2023-01-02` sampai `2025-10-31` adalah rentang yang pernah dipakai untuk executed audit evidence, sedangkan `2023-01-02` sendiri adalah baseline awal data yang sengaja dipilih untuk pembangunan aplikasi.

### Current last-data date during development

Tanggal terakhir yang tersedia saat ini adalah **development data frontier**, bukan batas capability platform dan bukan bukti aplikasi gagal menjaga production freshness.

Aplikasi masih dibangun dari awal dan belum menjadi operational watchlist yang dipakai secara rutin. Karena itu:

- tidak wajib mempertahankan data selalu sampai trading date terbaru selama fase pembangunan;
- gap setelah last ingested date bukan current production incident;
- `daily_enabled=false` dapat diterima sebagai development-state choice;
- gap tersebut tidak menghalangi koreksi contracts, schema, historical integrity, corporate action, indicator, atau replay;
- last-data date tidak boleh dipakai sebagai alasan menurunkan penilaian correctness komponen yang tidak bergantung pada live freshness.

Freshness baru menjadi hard operational requirement ketika project menetapkan **operational activation date** untuk forward paper watchlist, user-facing watchlist, atau penggunaan rutin lainnya.

Sebelum activation:

1. tentukan `OPERATIONAL_START_DATE` atau governance marker ekuivalen;
2. catch up seluruh trading dates dari development frontier sampai activation boundary melalui controlled backfill;
3. aktifkan dan buktikan daily import/promote scheduling;
4. aktifkan freshness alerts dan stale-consumer protection;
5. mulai menghitung consecutive operational SLO hanya sejak activation boundary.

Dengan interpretasi ini, historical start dan current last-data date tidak menjadi penghalang fase pembangunan. Keduanya tetap harus transparan agar kelak tidak disalahartikan sebagai full-history claim atau production-fresh claim.

---

## Executive conclusion

Dokumentasi strategi sudah menetapkan baseline yang cukup kuat, sempit, dan konsisten untuk mengarahkan pembangunan market-data IDX Regular-Market EOD. Owner contracts telah mengunci market scope, intentional dataset boundary, Yahoo bootstrap decision, temporal identity, immutable observations/publications, verified adjustment lifecycle, coherent price products, exact indicators, coverage/data-usability separation, provenance, consumer contract, replay, operations, schema target, dan semantic proof.

Verdict aktif:

> **`DOCUMENTATION_STRATEGY_READY` — canonical implementation baseline approved**
>
> **Documentation strategy/synchronization: `PASS` — 22/22 strategy areas (revalidated 2026-08-08).**

PASS di atas hanya menutup **kelengkapan dan konsistensi strategi dokumentasi**: owner, dependency order, terminology, cross-reference, dan strategy-level contradiction. Ia tidak menyatakan sistem saat ini research-ready, implementation-conformant, operationally validated, atau production-ready.

**Pembaruan current claim, 2026-08-08.** W22 pada 2026-08-06 menetapkan `IMPLEMENTATION_READY`, bukan `IMPLEMENTATION_CONFORMANT` atau `runtime-proven`. Strict documentation re-audit 2026-08-08 mempertahankan claim level dokumentasi itu tetapi **membatalkan satu kesimpulan implementasi yang terlalu kuat**: `P0-04` dibuka kembali karena penghapusan provider-`adj_close` fallback belum membuktikan selected run-wide `STRUCTURAL_ADJUSTED` product. Karena itu `IMPLEMENTATION_CONFORMANT` tetap **tidak diberikan** dengan `P0-04` plus P1 material yang masih terbuka; `OPERATIONALLY_VALIDATED` tetap **tidak diberikan** (nol sesi teraktivasi), dan production relock tetap tidak diberikan. `IMPLEMENTATION_READY` di sini hanya berarti paket A+B cukup jelas untuk implementasi/remediasi tanpa menebak, **bukan** bahwa implementasinya sudah conformant. Rinciannya pada `../MARKET_DATA_IMPLEMENTATION_LEDGER.md`. Klaim lama `FULL GLOBAL MARKET-DATA PRODUCTION READY` tetap historical/superseded.

Sistem tidak perlu mengejar seluruh capability institutional market-data. Sistem harus sempit pada data product EOD yang dibutuhkan saat ini, tetapi sangat kuat pada:

- point-in-time historical truth;
- immutable and revisioned data;
- corporate-action correctness;
- coherent price basis;
- exact and reproducible indicators;
- separation of coverage, quality, liquidity, event facts, and data usability;
- daily freshness and fail-safe operation;
- source and configuration provenance;
- stable market-data consumer contract.

Kelulusan market-data tidak bergantung pada implementasi watchlist, jumlah kandidat, stabilitas ranking, signal quality, expectancy, drawdown, turnover, profitabilitas, atau penilaian usefulness strategi. Weekly Swing hanya menjadi initial consumer profile untuk memprioritaskan frequency dan factual fields.

Implementasi berikutnya wajib mengikuti work order `W00`–`W22` pada blueprint, menutup seluruh assignment pada conformance matrix, memakai command protocol, dan memperbarui current-state ledger hanya berdasarkan evidence, lalu diaudit kembali. Code lama tidak boleh dipakai untuk melemahkan atau menafsirkan ulang contract agar behavior lama terlihat benar.

Recommended execution entry point adalah `MD-RUN W00 market-data.`. Setiap `MD-RUN Wxx` menjalankan lifecycle implement/audit/remediate/re-audit hanya untuk satu work order sampai `PASS`, lalu memberikan exact successor command tanpa menjalankannya. Urutan berlanjut `W00` sampai `W22` dan tidak mengubah watchlist policy, paid-provider decision, deployment authority, atau production activation menjadi implicit implementation scope.

---

## Correct strategic target

Target yang disahkan untuk mengarahkan pembaruan dokumen adalah:

> Membangun EOD market-data platform yang menghasilkan data product saham IDX Regular Market yang valid, decision-grade, point-in-time, reproducible, auditable, stabil, dan aman dikonsumsi, dengan Yahoo Finance sebagai bootstrap source saat ini dan provider-neutral architecture untuk masa depan.

Istilah **kuat dan stabil** pada target ini berarti:

- hasil hari ini dapat ditelusuri sampai source observation, publication, adjustment, dan config yang membentuknya;
- hasil historis tidak berubah diam-diam;
- backtest atau replay memakai universe dan informasi yang benar-benar diketahui pada waktu tersebut;
- corporate action tidak menghasilkan false price movement;
- kegagalan source menghasilkan quarantine atau held state, bukan synthetic repair yang tidak terbukti;
- pergantian provider tidak memerlukan redesign domain, indikator, atau consumer contract;
- consumer menerima data yang konsisten dan alasan data usability yang dapat dijelaskan.

Target ini tidak menjanjikan profit. Market-data menjamin kualitas data product; kualitas alpha dan hasil trading tetap menjadi tanggung jawab policy watchlist dan evaluasi strategy di downstream domain, serta tidak memengaruhi status readiness market-data.

---

## Scope boundary

### In scope sekarang

- saham IDX;
- EOD Regular Market sebagai market scope utama;
- raw OHLCV dan field EOD relevan;
- ticker, instrument, listing, dan symbol lifecycle;
- market calendar dan point-in-time trading status;
- corporate action yang memengaruhi price continuity atau event risk;
- adjusted analytical series yang konsisten;
- versioned indicator profile yang dinyatakan sebagai bagian data product;
- coverage, quality, liquidity, event-risk, dan data-usability facts;
- publication, correction, replay, provenance, scheduling, dan operational evidence;
- bootstrap provider `yahoo_finance`.

### Explicitly deferred

- tick-by-tick data;
- full order book atau market depth;
- ultra-low-latency pipeline;
- execution routing;
- cash dan negotiated market analytics penuh;
- multi-exchange platform;
- ISO 20022 lifecycle penuh;
- full LEI, ISIN, dan MIC infrastructure;
- seluruh voluntary corporate-action automation;
- multi-provider majority voting;
- dual-feed production;
- pembelian atau integrasi vendor berbayar pada fase sekarang.

Deferred scope boleh dipertimbangkan kembali hanya bila kebutuhan nyata muncul. Ia tidak boleh mengalihkan perbaikan P0 dan P1 yang langsung menentukan kebenaran dan kestabilan data product EOD.

---

## Documentation-strategy audit result

Review ini memeriksa apakah strategi dokumen mewakili praktik data pasar nyata yang relevan dan cukup preskriptif untuk pembangunan, bukan apakah code lama sudah mengikuti strategi.

| Documentation area | Status | Conclusion |
|---|---|---|
| Market-data scope and downstream boundary | PASS | IDX Regular-Market EOD dikunci; Weekly Swing hanya initial consumer profile dan downstream ownership eksplisit |
| Dataset boundary and operating phase | PASS | `2023-01-02`, development frontier, dan operational activation dibedakan |
| Yahoo bootstrap and provider boundary | PASS | Yahoo sah untuk pembuktian manfaat; paid provider bukan current backlog |
| Source observation and resilience | PASS | Immutable envelope, provenance, validation, quarantine, retry, dan activation-aware freshness ditetapkan |
| Temporal identity/calendar/status/sector | PASS | Listing, symbol, provider mapping, session, trading status, dan IDX-IC membership memiliki as-of/known-time semantics |
| Canonical RAW and corrections | PASS | Raw/canonical meaning tunggal; zero placeholder dan in-place published rewrite dilarang |
| Corporate actions and price products | PASS | Verified revisioned events/factors dan coherent product boundaries ditetapkan |
| Indicators and liquidity metrics | PASS | Structural basis, stable Wilder ATR, nullability, actual-versus-proxy semantics ditetapkan |
| Coverage and data usability | PASS | Expectation/delivery dipisahkan dari quality/liquidity/status/event risk dan reason codes; policy tradability tetap downstream |
| Config, publication, and consumer read | PASS | Full snapshot binding, immutable publication, freshness, dan minimum DTO ditetapkan |
| Replay/backtest | PASS | Exact publication dan as-known/knowledge-cutoff modes ditetapkan |
| Operations | PASS | Import/promote, correction, stale/failure handling, and activation-aware SLO ditetapkan |
| Schema and test specifications | PASS | Target model families, dictionary meanings, semantic oracles, and negative fixtures ditetapkan |
| Implementation sequencing and audit handoff | PASS | Work order dikunci pada blueprint; assignment/exit gates pada matrix; start/audit/remediation/re-audit/advance dan result format pada command protocol; current next command pada ledger |

Dokumentasi tidak memakai skor numerik karena angka dapat mencampurkan completeness dokumen dengan readiness implementasi. Kelulusan diberikan berdasarkan tidak adanya keputusan market-data material yang ambigu atau saling bertentangan pada scope IDX Regular-Market EOD.

### Documentation strategy strict revalidation — 2026-08-08

Revalidasi ini hanya membaca corpus dokumentasi aktif. Hasilnya:

- `22/22` strategy area pada **Document-by-document strategy update order** memiliki owner/contract dan done criteria yang tidak ambigu;
- seluruh dokumen normative/proof-critical tetap memiliki conformance assignment;
- sector classification/membership dipindahkan dari dependency terlambat di `W16` menjadi temporal-reference prerequisite pada Stage 6 / `W05`, sehingga sector-relative indicators di `W14` tidak dapat mendahului point-in-time membership; Stage 13 tetap mengonsumsi/expose sector-reference state tanpa mengambil alih temporal foundation;
- global gate 13 kini mencakup lima root external-reconciliation domains: calendar, universe/identity, trading status, corporate action, dan sector classification/membership untuk sector-relative products;
- kelima owner tersebut sekarang menyatakan **authority, reconciliation cadence, scope, dan qualification**;
- stale Yahoo `adj_close` fallback note diubah menjadi historical closed defect; current strategy tetap `adj_close` diagnostic-only tanpa fallback;
- `system/` summary disinkronkan dengan temporal identity/reference facts, source observation, price products, coverage/data-usability separation, config binding, publication, correction, dan exact/as-known replay;
- wording `manual_file` diseragamkan sebagai explicit controlled **one-date operational rescue**, sementara multi-date manual input hanya boleh untuk planned historical fill/backfill, correction/republication, atau replay-oriented reconstruction;
- command-support docs `01/02/03/05/07/08` disinkronkan dengan immutable source observation, stable `listing_id`, correction/republication, run-wide `STRUCTURAL_ADJUSTED`, exact + AS_KNOWN replay, dan publication-bound session-snapshot scope;
- optional fetch-failure SQL support diubah dari `(trade_date,ticker_id,run_id)` sebagai key menjadi append-only evidence dengan canonical nullable `listing_id`/source-symbol context dan surrogate evidence identity;
- transitional `Database_Schema_MariaDB.sql` diberi boundary eksplisit bahwa physical `ticker_id` keys adalah legacy deployable baseline, bukan V2 identity target;
- audit `LUMEN_*`, inventory/proof-pack, dan dated implementation evidence yang membawa literal semantics lama diposisikan eksplisit sebagai historical/non-authoritative; current implementation checkpoint hanya ledger dan current audit verdict hanya file ini;
- remediation register documentation `DOC-01`–`DOC-84` seluruhnya `CLOSED`.

**Documentation strategy verdict: `PASS`.** Tidak ada finding dokumentasi strategy-level yang masih `OPEN` setelah revalidasi ini. Open `P0/P1` implementation/data/runtime findings di bagian historical handoff tetap dipertahankan dan tidak boleh ditutup oleh perubahan dokumen ini.

### Implementation handoff — not a documentation verdict

Inspection implementasi sebelumnya menemukan unsafe legacy behavior dan proof gaps. Temuan tersebut tetap menjadi backlog wajib, tetapi statusnya tidak mengubah `DOCUMENTATION_STRATEGY_READY`. Implementasi harus dibangun terhadap blueprint dan conformance matrix, lalu audit baru menilai `IMPLEMENTATION_CONFORMANT` dan `OPERATIONALLY_VALIDATED`.

---

## Strengths that must be preserved

Hal berikut sudah menjadi aset penting dan tidak boleh hilang saat remediation:

1. **Import dan promote separation**

   Acquisition tidak otomatis berarti publication readable.

2. **Publication-aware consumer model**

   Consumer diarahkan membaca sealed/current/readable publication, bukan `MAX(date)` atau raw table secara bebas.

3. **Coverage and publishability gates**

   Partial provider response tidak otomatis menjadi readable dataset.

4. **Correction, seal, pointer, replay, dan evidence concepts**

   Fondasi ini tepat untuk deterministic rebuild dan audit trail.

5. **Date-driven acquisition dan backfill**

   Provider window tidak dijadikan capability limit domain.

6. **Retry, throttle, partial-tolerant acquisition, dan failure taxonomy**

   Ini sesuai dengan risiko bootstrap provider yang dapat rate limit atau berubah.

7. **Provider abstraction direction**

   Yahoo-specific mapping berada pada adapter dan tidak seharusnya masuk ke indicator/watchlist contract.

8. **Broad automated test coverage**

   Full MarketData suite terakhir yang dijalankan pada audit ini lulus `1152 tests / 8455 assertions`. Bukti ini menunjukkan breadth implementasi, walaupun belum membuktikan seluruh semantic correctness.

9. **Current universe acquisition breadth**

   Snapshot database menunjukkan master dan latest bars mencakup sebagian besar saham aktif. Kekurangannya berada pada temporal semantics dan freshness, bukan sekadar jumlah ticker.

10. **Yahoo bootstrap decision**

    Pemakaian Yahoo Finance adalah keputusan biaya dan fase produk yang sah, selama quality bar tidak diturunkan dan provider tidak menjadi domain truth.

---

## Correct target architecture

```text
Yahoo Finance bootstrap / future provider adapter
                      ↓
Raw source observations + provenance
                      ↓
Validation, rejection, anomaly, and quarantine
                      ↓
Revisioned canonical Regular-Market EOD publication
                      ↓
Point-in-time instrument universe and trading status
                      ↓
Verified and revisioned corporate-action factors
                      ↓
Raw / structural-adjusted / total-return data products
                      ↓
Versioned and reproducible indicators
                      ↓
Coverage + quality + liquidity + event-risk classification
                      ↓
Stable consumer read model
                      ↓
Weekly Swing watchlist policy
```

Dependency direction harus tetap satu arah. Market-data menyediakan facts, quality state, dan eligibility inputs. Market-data tidak memilih saham berdasarkan alpha, entry, exit, position sizing, atau portfolio preference.

---

## Strategic invariants to lock in owner documents

### 1. Explicit market scope

- Canonical equity EOD mewakili IDX Regular Market.
- Cash/negotiated market tidak boleh diam-diam tercampur.
- `trade_date`, exchange timezone, session completion, dan board/status semantics harus eksplisit.

### 2. Temporal instrument identity

- Issuer, instrument, listing, dan provider symbol adalah konsep terpisah.
- Universe untuk tanggal T ditentukan berdasarkan state pada T.
- Current `is_active` tidak boleh menghapus saham yang secara historis aktif dari replay/backtest.
- Symbol change, listing, delisting, suspension, relisting, dan board movement harus effective-dated.

### 3. Source strategy

- Yahoo Finance sah sebagai bootstrap primary source sekarang.
- Yahoo tidak disebut official IDX source dan tidak diberi commercial SLA yang tidak ada.
- Provider limitations berhenti di adapter/import strategy.
- Paid-provider selection dan migration bukan current scope.
- Source upgrade kelak dilakukan dengan adapter baru dan publication lineage, bukan rewrite domain.

### 4. Immutable observation and publication history

- Raw observations dan sealed publication tidak boleh diubah in-place.
- Correction harus menghasilkan revision/publication baru.
- Anomaly detection tidak boleh menjadi izin untuk mengubah history.
- Seluruh repair harus fail-safe, traceable, reversible melalui lineage, dan publication-aware.

### 5. Corporate-action correctness

- Price discontinuity hanya menghasilkan anomaly candidate.
- Synthetic price break tidak boleh otomatis menjadi verified corporate action.
- Event membutuhkan source, event identity, type, status, dates, factor, revision, dan verification state.
- Adjustment factor yang sudah dipakai sealed publication tidak boleh dimutasi diam-diam.
- `ex_date` menjadi anchor price continuity dan event-risk ketika tersedia.

### 6. Separate price products

Canonical strategy harus membedakan:

- `RAW`: market-observed OHLCV;
- `STRUCTURAL_ADJUSTED`: coherent OHLC dan volume adjustment untuk split, reverse split, bonus, rights, atau structural action yang disahkan;
- `TOTAL_RETURN`: product terpisah untuk performance evaluation termasuk distribution effects bila datanya tersedia.

Default yang direkomendasikan untuk indicator teknikal Weekly Swing adalah `STRUCTURAL_ADJUSTED`, bukan per-row `adj_close` fallback.

Aturan keras:

- satu indicator run memakai satu basis yang dikunci;
- seluruh OHLC disesuaikan secara coherent;
- volume disesuaikan inversely bila action semantics mengharuskannya;
- tidak ada campuran `adj_close` dan `close` antar tanggal dalam satu vector;
- bila factor penting belum terverifikasi, affected range di-quarantine atau eligibility diblokir.

### 7. Coverage is not eligibility

- Coverage menjawab apakah expected market observations tersedia.
- Quality menjawab apakah observations dapat dipercaya.
- Liquidity menjawab apakah saham layak untuk policy Weekly Swing.
- Eligibility menyatukan facts yang dibutuhkan downstream tanpa menyatakan alpha approval.
- Dormancy dan zero-volume history tidak boleh menyembunyikan provider failure dari denominator coverage.
- Exclusion dari denominator hanya boleh berdasarkan point-in-time evidence bahwa bar memang tidak diharapkan, misalnya verified suspension/market status.

### 8. Exact indicators

- Formula, price basis, seed, warm-up, rounding, nullability, dan version harus terkunci.
- Wilder ATR harus menggunakan recursive state atau historical chain dengan seed stabil.
- Sliding load window tidak boleh diam-diam me-seed ulang ATR setiap run.
- Indicator tidak boleh memakai zero-placeholder atau invalid row sebagai harga nyata.
- Perubahan formula harus membuat indicator/config version baru.

### 9. Honest liquidity fields

- Provider-reported traded value adalah canonical turnover bila tersedia dan tervalidasi.
- `price × volume` hanya boleh diberi nama `turnover_proxy_idr` atau nama lain yang jujur.
- Proxy tidak boleh disebut actual traded value.
- Jika adjusted price dipakai, jangan mengalikannya dengan raw volume untuk mengklaim nominal market turnover.

### 10. Full reproducibility context

Setiap consumer-visible result minimal harus dapat ditelusuri ke:

- requested dan effective trade date;
- run dan publication identity;
- source mode, provider, symbol, dan observed/ingested time;
- raw payload/request identity atau content hash yang aman;
- canonicalization version;
- corporate-action/factor version;
- price-basis version;
- indicator set/version;
- complete output-affecting config hash dan snapshot;
- eligibility/reason-code version.

### 11. Daily operational truth

- Trading calendar menentukan tanggal yang wajib diproses.
- Daily pipeline harus otomatis, idempotent, retryable, dan observable.
- Import sukses bukan publish sukses.
- Late or missing date harus terlihat sebagai incident/degraded state.
- Watchlist tidak boleh mengonsumsi stale publication tanpa explicit freshness state.

### 12. Point-in-time replay

- Replay harus dapat memilih `AS_KNOWN` dan, bila kelak dibutuhkan, `LATEST_RESTATED` view.
- Minimum current requirement adalah survivorship-free universe dan publication-as-known input.
- Backtest tidak boleh melihat current ticker status, correction, corporate action, atau config yang belum diketahui pada tanggal evaluasi.

---

## Strict strategy synchronization re-audit — 2026-08-08

A second document-wide pass found and corrected residual legacy semantics in companion/schema/ops documents: canonical identity keys, suspension-vs-bar-expectation separation, session-snapshot identity, artifact/log identity, source-upsert immutability wording, correction terminology, and analytical price-product configuration. **Documentation strategy remains PASS 22/22 after these corrections.** Historical runtime/audit text may retain old behavior only when explicitly marked historical/superseded.

This pass also corrected an over-strong implementation claim: `P0-04` is **reopened as an implementation gap**, because eliminating provider `adj_close` fallback is not equivalent to implementing the selected `STRUCTURAL_ADJUSTED` product run-wide. This does not reopen the documentation strategy; it prevents documentation from claiming proof that the runtime evidence does not establish.

## Prioritized findings

### P0 — must be corrected before decision-grade use

| ID | Finding | Evidence | Risk | Required correction |
|---|---|---|---|---|
| P0-01 | Historical price-scale repair mengubah data in-place | `app/Application/MarketData/Services/PriceScaleStretchRepairService.php` melakukan update terhadap `eod_bars` dan `eod_bars_history` | Sealed history, hashes, replay, dan prior watchlist dapat berubah tanpa publication revision | Hilangkan apply path langsung; detector hanya membuat anomaly; correction harus membuat publication baru — **`CLOSED` pada remediasi W21, 2026-08-06.** Nol pembaruan in-place `eod_bars` tersisa di seluruh `app/`; `PriceScaleStretchRepairService` dan `CorporateActionDerivationService` keduanya permukaan non-mutating ber-`capability_state = DETECTION_ONLY`; dan sejak W10 enam trigger database menolak mutasi snapshot yang sudah tersegel. **Residu terekam:** 18 dari 32 baris `market_data_price_scale_breaks` berstatus `REPAIRED` dengan `repaired_at` pada `2026-07-30 01:34`, yaitu perbaikan in-place yang benar-benar terjadi sebelum penjaga ini ada. Kolom repair-nya sengaja **tidak dihapus** — ia catatan bahwa operasi terlarang itu pernah dijalankan, dan menghapusnya akan memusnahkan bukti alih-alih memperbaikinya. Lihat `P1-14` |
| P0-02 | Historical universe memakai current active state | `TickerMasterRepository.php` memfilter `is_active=1` sebelum as-of listed/delisted dates | Survivorship bias dan replay universe salah | Universe resolver harus sepenuhnya as-of-date dan diuji dengan inactive-now-but-active-then fixture **Ditutup pada W05, 2026-08-03, dengan bukti terhadap data produksi.** Proyeksi temporal dijalankan atas 977 ticker menghasilkan 977 baris pada `md_issuers`, `md_instruments`, `md_listings`, dan `md_listing_symbols`. Uji survivorship memakai emiten nyata: `legacy_ticker_id 939`, listing 1995-05-16 dan delisting 2023-04-06, kini `is_active = 0`. Ia **muncul** pada `universeAsOf('2023-03-01')` yang berisi 846 listing dan **tidak muncul** pada `universeAsOf('2026-07-28')` yang berisi 962. Resolver tidak menyentuh `is_active` sama sekali. |
| P0-03 | Synthetic corporate action dan break linkage tidak fail-safe | Derivation dapat membuat action dari price anomaly tetapi `matched_corporate_action_id` tidak selalu terpasang; quarantine dapat tetap salah | False adjustment atau affected rows lolos tanpa factor yang benar | Price break hanya candidate; verified/manual/authoritative action wajib sebelum factor dipakai; linkage atomik — **`CLOSED` pada remediasi W21, 2026-08-06.** Dampaknya dinetralkan pada W11: faktor ber-`adjustment_source = DERIVED_FROM_PRICE_SERIES` tidak dapat mencapai jalur adjustment **maupun** menekan flag kontaminasi, terbukti pada produksi dengan 15 faktor terpakai menjadi 0. Penulisnya juga sudah pensiun menjadi permukaan non-mutating. **Residu terekam:** 28 dari 32 break tidak memiliki `matched_corporate_action_id`, tetapi linkage tidak lagi menggerbangi apa pun karena faktor turunan ditolak sepenuhnya, bukan disaring lewat linkage. Lihat `P1-31` |
| P0-04 | Analytical price product belum konsisten dengan selected strategy | Owner strategy kini benar: baseline indikator teknikal memilih satu versioned `STRUCTURAL_ADJUSTED` product per run dan provider `adj_close` dilarang sebagai basis. Runtime/remediation W21 berhasil menghilangkan provider `adj_close` fallback, tetapi masih memiliki legacy `price_basis_default = close` dan dapat melabeli baris tanpa faktor sebagai `RAW` sementara baris lain menjadi `STRUCTURAL_ADJUSTED` | MA, ROC, ATR, ranking, lineage, dan replay tidak memiliki one-product-per-run guarantee sampai selected product benar-benar dibinding secara run-wide | **`REOPENED / IMPLEMENTATION GAP` pada strict documentation re-audit 2026-08-08.** Dokumentasi target sudah `CLOSED`: `market_data.indicators.price_product_default = STRUCTURAL_ADJUSTED`; legacy `market_data.platform.price_basis_default = close` didefinisikan hanya sebagai RAW-field selector/deprecated analytical authority. Implementasi harus menghasilkan/bind `STRUCTURAL_ADJUSTED` sebagai selected product untuk seluruh indicator run (factor `1` tetap bagian dari selected product, bukan pergantian produk ke RAW), menyimpan factor/config/product identity, dan recompute korpus lama berbukti. 756.328 baris lama tanpa label tetap `P1-32`. |

### P1 — required for strong and stable Weekly Swing data

| ID | Finding | Risk | Required correction |
|---|---|---|---|
| P1-01 | Raw provider observations belum menjadi immutable first-class layer | Correction dan source investigation sulit direproduksi | Simpan bounded raw observation envelope atau immutable payload reference/hash sebelum canonicalization |
| P1-02 | Config provenance tidak lengkap | `config_version` hanya mewakili indicator set dan `config_hash/config_snapshot_ref` masih kosong | Replay menghasilkan output berbeda tanpa jejak | Snapshot seluruh output-affecting config dan isi hash/reference pada run/publication |
| P1-03 | Dormancy bercampur dengan coverage | Missing provider bars dapat tersembunyi sebagai dormant exclusion | Simpan dormancy/zero-volume sebagai fakta activity/liquidity tanpa menjadikannya data-usability atau coverage exclusion; denominator hanya memakai verified bar expectation |
| P1-04 | ATR direseed dari loaded sliding window | Nilai ATR dapat bergeser ketika run date maju | Persist recursive state atau hitung dari stable historical seed; tambahkan long-chain oracle |
| P1-05 | Event-risk memakai `action_date` pada jalur tertentu | Quarantine window dapat salah tanggal | Gunakan `ex_date` sebagai primary effective anchor dengan fallback hierarchy eksplisit |
| P1-06 | `dv20` bukan actual traded value | Liquidity ranking dapat salah, terutama bila price adjusted dikali raw volume | Rename menjadi explicit proxy; ingest actual traded value saat source memungkinkan |
| P1-07 | Canonical EOD fields terlalu tipis | Reconciliation, liquidity, board/status, dan quality diagnosis terbatas | Tambahkan nullable source fields untuk previous, traded value, trade count, board, dan status dengan provenance |
| P1-08 | Corporate-action factor fields dapat dimutasi | Sealed analytical history berubah secara implisit | Gunakan event/factor revisions dan publication binding; jangan mutate factor yang telah dipakai |
| P1-09 | Zero-OHLC policy kontradiktif | Satu contract melarang canonical zero bars, contract lain mengizinkan placeholder | Pilih satu model: recommended missing/invalid observation terpisah, bukan zero canonical price |
| P1-10 | Tests mengunci beberapa semantic lama yang salah | Green suite memberi false confidence | Tambahkan regression untuk point-in-time universe, no-history-rewrite, coherent basis, exact ATR, dan coverage/eligibility separation |
| P1-11 | Promote/freshness operation belum sepenuhnya otomatis | Bukan current development blocker, tetapi akan membuat consumer menerima state stale setelah operational activation | Sebelum activation, catch up development gap, jadwalkan promote/readiness, monitor locks/retries, dan alert latest effective date |
| P1-12 | Testing migration state tidak sepenuhnya sinkron | Test environment dapat tidak merepresentasikan schema runtime | Terapkan pending testing migrations dan tambahkan environment parity gate |

### P2 — future-strength improvements after core correctness

| ID | Improvement | Timing rule |
|---|---|---|
| P2-01 | Full `AS_KNOWN` versus `LATEST_RESTATED` bitemporal product | Setelah minimum point-in-time universe/publication semantics stabil |
| P2-02 | Secondary-source bounded reconciliation | Setelah Yahoo bootstrap menghasilkan manfaat dan anomaly cost dapat diukur |
| P2-03 | Paid/licensed provider adapter | Hanya setelah value, SLA, licensing, correction, field coverage, atau commercial trigger terpenuhi |
| P2-04 | Automated broader corporate-action workflow | Setelah price-continuity actions dan manual verification flow stabil |
| P2-05 | Static analysis and mutation/property testing | Setelah P0 behavior diperbaiki agar automation tidak mengunci behavior salah |
| P2-06 | Disaster rebuild and provider migration rehearsal | Sebelum commercial SLA atau higher-risk automation |

---

## Known bugs and future bug risks

| Risk | Type | Likely symptom | Prevention |
|---|---|---|---|
| In-place scale repair | Existing bug | Historical bars dan prior outputs berubah | Immutable correction publication |
| Current-active historical filter | Existing bug | Delisted/inactive securities hilang dari backtest | Point-in-time universe resolver |
| Missing corporate-action linkage | Existing bug | Break tetap quarantined atau factor tidak ditemukan | Transactional event-break-factor linkage |
| Event window anchored to wrong date | Existing bug | False positive/negative event risk | Explicit ex-date hierarchy |
| Sliding-window ATR reseed | Existing semantic bug | ATR berubah antar rerun/window | Stable recursive seed/state |
| Mixed close/adj-close fallback | High future risk | Indicator spike dan ranking drift | One coherent basis per run |
| Volume rounding during repair | High future risk | Historical liquidity berubah dan tidak reversible | Never rewrite raw bar/volume |
| Dormant exclusion hides provider outage | High future risk | Coverage terlihat tinggi saat data hilang | Separate expectation, delivery, factual activity/liquidity, and data usability |
| Adjusted price multiplied by raw volume | High future risk | Nominal liquidity secara dimensional salah | Actual value or explicitly named proxy |
| Provider schema or rate-limit change | Expected operational risk | Partial/malformed/late acquisition | Adapter isolation, retry budget, schema guard, quarantine |
| Yahoo revision without captured lineage | High future risk | Re-fetch memberi history berbeda | Immutable observation hash/reference and new publication |
| Symbol reuse/change | High future risk | Data melekat ke instrument yang salah | Temporal listing and provider-symbol mapping |
| Late corporate action | High future risk | Signal window memakai false price gap | Event revision, contamination window, republish workflow |
| Config drift | High future risk | Rerun berbeda walau code sama | Full config snapshot/hash |
| Stale calendar/status | High future risk | Wrong requested date atau wrong coverage exclusion | Effective-dated authoritative calendar/status controls |
| Test fixtures mirror flawed rules | Existing process risk | Tests green namun semantic salah | Independent real-market oracles and negative cases |

---

## Document-by-document strategy update order

Urutan ini telah dipindahkan menjadi work order normatif pada `book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`, dengan assignment lengkap pada `book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`. Ringkasan di bawah dipertahankan untuk traceability audit; implementer wajib memakai blueprint, matrix, dan owner contracts, bukan audit report ini sebagai behavioral authority.

Documentation remediation register di bawah dipertahankan untuk traceability. **Per revalidasi 2026-08-07 tidak ada item `DOC-xx` yang masih `OPEN`; seluruh `DOC-01`–`DOC-84` `CLOSED`.** Implementer tetap harus membaca owner contracts/current blueprint karena historical register tidak menjadi behavioral authority.

**Nomor order bukan urutan eksekusi.** Tabel di bawah menomori *area dokumen*; urutan pengerjaan ditentukan oleh sekuens `W00`–`W22` pada blueprint. Keduanya sengaja berbeda pada satu titik: temporal identity (order 6) dan calendar/status (order 7) dieksekusi sebagai `W05` dan `W06`, yaitu **sebelum** source acquisition (order 4) dan resilience (order 5) yang dieksekusi sebagai `W07` dan `W08`. Alasannya, kontrak akuisisi menentukan simbol apa yang diambil dari provider; bila pemetaan provider-symbol belum temporal saat itu, pengambilan data memakai simbol yang aktif sekarang dan survivorship masuk di sumber, sebelum resolver universe sempat berperan. Membaca tabel ini sebagai urutan kerja akan membalik keputusan tersebut.

| Order | Owner document area | Strategy change | Done criteria |
|---:|---|---|---|
| 1 | `docs/market_data/README.md` dan `book/Terminology_and_Scope.md` | Kunci tujuan Weekly Swing, IDX Regular-Market EOD, intentional dataset start `2023-01-02`, development frontier, operational activation, dan raw/adjusted/eligibility terminology | Tidak ada ambiguity tentang market, horizon, dataset boundary, atau product boundaries |
| 2 | `book/Domain_Boundary_Invariants_LOCKED.md` | Tegaskan market-data facts versus watchlist alpha/policy | Market-data tidak memiliki entry/exit/ranking strategy |
| 3 | `book/Yahoo_Finance_Bootstrap_Source_Strategy.md` | Pertahankan Yahoo as bootstrap dan paid-provider future boundary | Tidak ada paid-provider backlog tersirat untuk fase sekarang |
| 4 | `book/Source_Data_Acquisition_Contract_LOCKED.md` | Tambahkan immutable observation envelope, provenance, stale/schema validation | Source payload dapat ditelusuri tanpa provider-specific leakage |
| 5 | `book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` | Kunci development-versus-operational freshness boundary, degraded state, retry/quarantine, dan no auto-repair | Development gap tidak dianggap incident; setelah activation, source failure tidak dapat menghasilkan silent readable data |
| 6 | `book/Tickers_and_Identity_Dependency_Contract_LOCKED.md`, `book/Symbol_Lifecycle_and_Mapping_Contract.md`, dan `book/Sector_Classification_Contract_LOCKED.md` | Kunci issuer/instrument/listing/provider-symbol temporal model **serta temporal IDX-IC membership sebagai prerequisite sector-relative analytics** | Historical universe dan sector-relative context survivorship/current-state-leakage free |
| 7 | `book/Market_Calendar_Requirements_Contract.md` dan status source contracts | Kunci Regular-Market calendar, session completion, suspension/status as-of | Expected bar semantics dapat dibuktikan untuk T |
| 8 | `book/Canonicalization_Contract_EOD_Bars.md` dan `book/EOD_Bars_Contract.md` | Tambahkan source field mapping, missing/invalid model, no zero-price contradiction | Raw/canonical meaning tunggal dan testable |
| 9 | History, seal, retention, correction, and versioning contracts | Larang seluruh in-place published-history repair | Semua content change membuat publication/revision lineage baru |
| 10 | Corporate-action contracts dan `registry/Price_Adjustment_Contract_LOCKED.md` | Kunci event lifecycle, verification, ex-date, factor revision, contamination | Synthetic anomaly tidak dapat menjadi verified adjustment otomatis |
| 11 | Corporate-action selected defaults | Ganti mixed `ADJ_CLOSE` fallback dengan coherent products | Satu price basis per run; OHLC/volume consistent |
| 12 | Coverage contracts | Pisahkan bar expectation/delivery dari dormancy/liquidity | Provider failure tidak dapat tersembunyi di denominator |
| 13 | Data-usability contracts | Simpan quality, liquidity, status, event-risk, data-usability decision, dan reason codes secara terpisah | Consumer menerima explainable market-data facts tanpa policy tradability/watchlist |
| 14 | `registry/Volume_and_Turnover_Normalization_LOCKED.md` dan daily metrics | Bedakan actual traded value dari proxy | Tidak ada metric misleading atau dimension mismatch |
| 15 | Indicator contracts, formula specs, dan registry | Kunci structural-adjusted basis, exact ATR, warm-up, version, nullability | Golden long-chain calculation deterministic |
| 16 | `registry/Platform_Config_Registry_LOCKED.md` | Snapshot seluruh output-affecting keys | Run/publication memiliki non-null config hash/reference |
| 17 | Consumer read model and readiness contracts | Definisikan minimum versioned market-data read product dan freshness state | Consumer tidak perlu membaca internals atau menebak readiness; watchlist outcome bukan gate |
| 18 | Replay/backtest contracts | Tambahkan point-in-time/as-known rules dan anti-survivorship fixtures | Replay tidak memakai future master/action/config state |
| 19 | Ops scheduling, runbooks, SLO, and incident contracts | Otomatiskan daily import-promote, stale alert, retry/backfill | Operasi terbukti pada consecutive trading days |
| 20 | DB schema/dictionary/migrations | Implement temporal keys, provenance, actual EOD fields, factor revisions | Schema, dictionary, migrations, dan test mirror sinkron |
| 21 | Test contracts and fixtures | Tambahkan real-market semantic oracles dan negative cases | Green suite membuktikan meaning baru, bukan behavior lama |
| 22 | Audit claims, tracker, and proof pack | Re-audit dan baru relock production claim | Tidak ada P0/P1 critical dan executed proof tersedia |

Setiap langkah harus membaca contract owner lebih dulu, lalu membangun schema/config/code/tests/runbook/evidence yang bergantung padanya. Jangan memperbarui implementation tracker menjadi `CONFORMANT`, `VALIDATED`, atau `LOCKED` sebelum behavior dan executed proof benar-benar tersedia.

### Historical implementation probe — hasil per order 1→22 (snapshot 2026-08-03; bukan current documentation verdict)

Detail tiap order ada pada section `#### Order N` di bawah, yang tersusun menurun karena urutan penulisan. Tabel ini adalah jalan masuknya, berurutan naik, dengan status Done criteria, hasil probe, dan temuan yang masih terbuka.

Kosakata status: **`TERBUKTI EMPIRIS`** diuji terhadap data produksi · **`TERBUKTI MEKANIS`** diuji terhadap kode, schema, atau config · **`TERPENUHI KONTRAK`** dinyatakan kontrak dan belum diuji eksekusi · **`TIDAK TERUKUR`** tidak dapat dinilai karena artefaknya belum ada · **`TIDAK TERPENUHI`** terukur gagal · **`MENUNGGU ACTIVATION`** sah tertunda.

| Order | Area | Status Done criteria | Angka penentu | Terbuka |
|---:|---|---|---|---|
| 1 | Scope dan terminologi | `TERBUKTI MEKANIS` | 33 istilah, nol terdefinisi di luar owner | — |
| 2 | Domain boundary | `TERBUKTI MEKANIS` | nol kolom/config/command policy di luar `watchlist_*` | — |
| 3 | Yahoo bootstrap | `TERPENUHI KONTRAK` | nol kapabilitas `UNSUPPORTED` yang dipetakan adapter | `P1-13` |
| 4 | Source acquisition | `TERBUKTI MEKANIS` | nol kebocoran provider ke lima kontrak hilir | — |
| 5 | Source resilience | `TERPENUHI KONTRAK` | empat config key kontrak ada di config dan register | — |
| 6 | Identitas temporal | `TERPENUHI KONTRAK` | resolver bersih dari `is_active`; **4 tabel belum ada** | `P1-26` |
| 7 | Kalender dan status | `TERPENUHI KONTRAK` | **1 bar hari Sabtu**; **0 dari 6 kolom kalender wajib** | `P1-17` `P1-19` |
| 8 | Canonicalization | **`TERBUKTI EMPIRIS`** | **756.329 bar, nol pelanggaran aturan 1–9**; 26 pelanggaran cross-field | `P1-18` |
| 9 | History dan seal | **`TERBUKTI EMPIRIS`** | **56.138.923 baris, nol tanpa publikasi**; 1 baris yatim | `P1-19` |
| 10 | Corporate action | `TERPENUHI KONTRAK` | **15 factor aktif tanpa verifikasi otoritatif** | `P1-15` `P1-20` |
| 11 | Coherent price product | `TIDAK TERUKUR` | **nol artefak menyimpan identitas price-product** | `P1-21` `P0-04` |
| 12 | Coverage | **`TERBUKTI EMPIRIS`** | **2.317 run gagal-total, denominator 900 — tidak menyusut** | `P1-22` |
| 13 | Data usability | **`TIDAK TERPENUHI`** | **2 dari 19 fakta wajib tersimpan** | `P1-23` `P1-27` |
| 14 | Actual versus proxy | sebagian: dimensi `TERBUKTI EMPIRIS` | **rasio `dv20` tepat `1,0000`**; 1 dari 4+ field | `P1-24` |
| 15 | Indikator | sebagian: warm-up `TERBUKTI EMPIRIS` | **975 dari 975 `NULL`**; radius 50 sesi + tak terbatas | fixture absen |
| 16 | Config snapshot | **`TIDAK TERPENUHI`** | **0 dari 71.917 run punya config hash** | `P1-25` |
| 17 | Consumer read model | `TERBUKTI MEKANIS` | nol pola bypass; **nol route konsumen** | belum diuji pemakaian |
| 18 | Replay dan backtest | `TIDAK TERUKUR` | 20.635 `PASS`, **nol test as-known** | as-known absen |
| 19 | Operasi dan SLO | `MENUNGGU ACTIVATION` | ambang terbit: **2 sesi breach, 5 sesi trigger** | `P1-11` |
| 20 | Schema dan migration | **`TIDAK TERPENUHI`** | **2 migration terakhir tidak pernah diterapkan** | `P1-26` |
| 21 | Test dan fixture | sebagian | **95 behavioral : 34 teks**; nol golden fixture | `P1-10` |
| 22 | Audit dan proof pack | **`TIDAK TERPENUHI`** | **31 temuan terbuka** (31 sebelum 2026-08-10; naik ke 34 saat `P1-41`/`P1-42`/`P1-43` ditambahkan oleh sector IDX-IC authority work, lalu kembali ke 31 pada 2026-08-11 saat `P1-32`/`P1-33`/`P1-34` ditutup oleh recompute berbukti; `P1-27` turun `OPEN`→`PARTIAL` tanpa mengubah hitungan terbuka); 14 dokumen klaim usang | seluruhnya |

Cara membaca tabel ini untuk hasil optimal:

- **Empat order sudah terbukti empiris** — 8, 9, 12, dan sebagian 14 serta 15. Keduanya diuji terhadap ratusan ribu hingga puluhan juta baris nyata, bukan terhadap pembacaan.
- **Tiga order terukur gagal** — 13, 16, dan 20. Ketiganya cacat implementasi, bukan cacat kontrak, dan `P1-26` pada order 20 adalah **akar bersama** dari sebagian besar sisanya.
- **Dua order tidak terukur** — 11 dan 18. Keduanya bukan gagal melainkan tidak punya artefak untuk diukur, dan itu perbedaan yang menentukan urutan pengerjaan.
- **Urutan pengerjaan yang paling efisien mengikuti akar, bukan nomor order.** Menerapkan dua migration `P1-26` membuka jalan bagi `P1-16`, `P1-20`, `P1-21`, `P1-25`, dan sebagian `P1-17` sekaligus — lima temuan yang tampak terpisah.

### Documentation closure state of orders 1–22

Audit khusus dokumentasi menghasilkan status berikut:

| Order | Documentation state | Conclusion |
|---:|---|---|
| 1–3 | `DOCUMENTATION_READY` | Scope/terminology, domain boundary, data boundary, dan Yahoo bootstrap strategy selaras; kapabilitas negatif source aktif kini dinyatakan lewat capability matrix — `DOC-01` `CLOSED` |
| 4–8 | `DOCUMENTATION_READY` | Observation, resilience, temporal identity/calendar/status, canonicalization, dan RAW bar meaning eksplisit; status authority, source priority, dan kedudukan manual import kini dinyatakan — `DOC-04` `CLOSED` |
| 9–13 | `DOCUMENTATION_READY` | Immutability, correction, verified event/factor, coherent products, coverage, dan eligibility strategy terkunci; lantai deteksi dinyatakan dan fakta struktur bursa memperoleh owner — `DOC-02` dan `DOC-03` `CLOSED` |
| 14–15 | `DOCUMENTATION_READY` | Actual-versus-proxy, structural basis, stable recursive Wilder ATR, warm-up, and nullability terkunci |
| 16–19 | `DOCUMENTATION_READY` | Full config snapshot, minimum consumer product, exact/as-known replay, dan activation-aware operations terkunci |
| 20 | `DOCUMENTATION_READY` | Semantic schema contract, dictionary, migration policy, rollout/backfill/enforcement requirements, and test-mirror boundary lengkap sebagai target pembangunan |
| 21 | `DOCUMENTATION_READY` | Contract matrix, required real-market semantic oracles, negative cases, and fixture catalog lengkap sebagai proof specification |
| 22 | `DOCUMENTATION_READY_IMPLEMENTATION_REAUDIT_PENDING` | Audit/claim rules memisahkan documentation closure dari future implementation/operations relock |

### Known implementation handoff backlog — not documentation blockers

| Finding | Current state | Evidence after strategy update |
|---|---|---|
| P0-01 direct historical scale repair | `CLOSED` | `PriceScaleStretchRepairService` dan command `market-data:repair-price-scale-stretches --apply` masih meng-update `eod_bars`/`eod_bars_history`; draft repair migration sudah dinetralkan tetapi runtime path belum dihapus **Ditutup pada remediasi W21, 2026-08-06.** Nol pembaruan in-place `eod_bars` tersisa di `app/`; repair dan derivation keduanya `DETECTION_ONLY` non-mutating; enam trigger database menolak mutasi snapshot tersegel sejak W10. Residu: 18 baris `REPAIRED` ber-`repaired_at 2026-07-30 01:34` adalah catatan perbaikan in-place yang benar-benar terjadi sebelum penjaga ada, dan kolomnya sengaja dipertahankan karena menghapusnya memusnahkan bukti. |
| P0-02 survivorship/current-active universe | `CLOSED` | **Ditutup pada W05, 2026-08-03, dengan bukti terhadap data produksi.** `TickerMasterRepository::getUniverseForTradeDate()` kini mendelegasikan ke `TemporalIdentityRepository::universeAsOf()` yang memfilter `listed_date <= T` dan `delisted_date > T` tanpa menyentuh `is_active`. Proyeksi temporal dijalankan atas 977 ticker menghasilkan 977 baris pada `md_issuers`, `md_instruments`, `md_listings`, dan `md_listing_symbols`. Uji survivorship memakai emiten nyata: `legacy_ticker_id 939`, listing `1995-05-16`, delisting `2023-04-06`, kini `is_active = 0`. Ia **muncul** pada `universeAsOf('2023-03-01')` yang berisi 846 listing dan **tidak muncul** pada `universeAsOf('2026-07-28')` yang berisi 962. Enam fixture exit gate terkunci pada `TemporalIdentityFixturesTest`. |
| P0-03 synthetic event/factor behavior | `CLOSED` | `CorporateActionDerivationService` masih membuat/mengubah `PRICE_RESCALE_UNCLASSIFIED` dengan `DERIVED_FROM_PRICE_SERIES`; seed migration baru sudah dihentikan tetapi service/repository/test lama belum diremediasi **Ditutup pada remediasi W21, 2026-08-06.** W11 memblokir faktor `DERIVED_FROM_PRICE_SERIES` dari jalur adjustment sekaligus dari menekan flag kontaminasi; produksi 15 faktor terpakai menjadi 0. Penulisnya pensiun menjadi permukaan non-mutating, dan `PRICE_RESCALE_UNCLASSIFIED` sengaja tidak di-seed sebagai tipe yang mengotorisasi adjustment. Residu: 28 dari 32 break tanpa linkage, tetapi linkage tidak lagi menggerbangi apa pun. |
| P0-04 mixed/incoherent analytical product | `REOPENED / IMPLEMENTATION GAP` | W21 menutup provider-`adj_close` fallback, tetapi **tidak** memenuhi selected strategy yang memilih satu `STRUCTURAL_ADJUSTED` product per indicator run. Legacy `price_basis_default=close` hanya boleh menjadi RAW-field selector; runtime harus mengikat selected product/factor set run-wide dan tidak mengganti label menjadi `RAW` hanya karena cumulative factor bernilai 1. Dokumentasi target sudah disinkronkan 2026-08-08; implementation proof baru tetap diperlukan. Residu korpus lama: 756.328 baris tanpa label — `P1-32`. |
| P1-01/P1-02 observation and config provenance | `PARTIAL` | schema foundation tersedia; writer, backfill, non-null seal enforcement, dan replay adoption belum ada |
| P1-03/P1-04/P1-05 coverage, ATR, ex-date | `OPEN` | owner rules sudah benar; dormancy exclusion code, indicator execution proof, dan event runtime behavior belum tertutup |
| P1-06/P1-07/P1-08 actual fields and revisions | `PARTIAL` | nullable schema target tersedia; source population, factor lifecycle, publication binding, dan correction behavior belum diimplementasikan |
| P1-09 zero-OHLC contradiction | `STRATEGY_CLOSED_IMPLEMENTATION_OPEN` | owner contracts konsisten menolak zero canonical price; end-to-end negative evidence belum lengkap |
| P1-10 semantic tests | `PARTIAL` | catalogs/matrix sudah dikoreksi dan schema guard ada; legacy repair/derived-action tests masih mengunci behavior yang ditolak |
| P1-11 activated operations | `PRE_ACTIVATION_OPEN` | development gap bukan incident, tetapi activation date, deployed schedule, alerts, dan consecutive-session proof belum ada |
| P1-12 schema/test parity | `PARTIAL` | SQLite V2 shape lulus; MariaDB clean-install/upgrade/backfill/enforcement evidence belum dijalankan |
| P1-13 `adj_close` close-fallback pada adapter | `CLOSED` | `PublicApiEodBarsAdapter.php:983` memakai `$adjclose[$position] ?? ($quote['close'][$position] ?? null)`, sedangkan `book/Source_Mapping_Contract_LOCKED.md:27` menyatakan provider `adj_close` "has no close fallback semantics". Nilai `adj_close` tersimpan karena itu belum tentu `adj_close` provider, dan tidak dapat dipakai sebagai diagnostic yang jujur sampai fallback dihapus. Ditemukan saat penyusunan capability matrix `DOC-01` **Ditutup dan diverifikasi ulang 2026-08-03 pada W00/W01.** `PublicApiEodBarsAdapter.php:1040` kini `'adj_close' => $adjclose[$position] ?? null` tanpa fallback ke `close`, dan adapter membawa flag kapabilitas eksekutabel `provides_actual_traded_value => false` serta `provides_official_board_or_trading_status => false`. Temuan ini dicatat pukul 10:56 dan diperbaiki pukul 14:05; saya membawanya sebagai terbuka tanpa memeriksa ulang sampai verifikasi W01. |
| P1-14 kolom repair-tracking terlarang tertinggal tanpa penulis | `OPEN` | `db/Database_Schema_Contracts_MariaDB.md:37` menyatakan schema **tidak boleh** menyediakan field yang mencatat in-place repair terhadap canonical/history rows. `market_data_price_scale_breaks` di MariaDB tetap memiliki `repaired_at`, `repaired_bar_count`, dan `repaired_history_row_count`. Setelah derivation dan repair service dipensiunkan menjadi compatibility surface non-mutating pada 2026-08-03, `grep repaired_at app/` mengembalikan nol — kolom itu kini yatim: dilarang kontrak, tidak ada di `database/migrations/` maupun mirror SQLite, dan tidak ditulis kode mana pun. **Dokumen benar; database yang tertinggal.** Bagian dari footprint P0-01 dan instance konkret P1-12 |
| P1-40 dormansi masih mengecilkan penyebut meski registry melarangnya | `CLOSED` | `Reason_Codes_Registry.md` menyatakan `COVERAGE_DORMANT_TICKERS_EXCLUDED` sebagai **deprecated legacy reason** dengan kalimat eksplisit: *"Dormancy must never remove a temporal-universe listing from the coverage denominator; any runtime emission is a V2 migration failure."* Namun `CoverageGateEvaluator` tetap memanggil `filterDormantUniverseRows()` yang membuang baris dari universe, dan tetap memancarkan kode itu pada `:167`. Jadi larangannya bukan soal pencatatan melainkan soal tindakannya: dormansi tidak boleh mengecilkan penyebut sama sekali. **Ini menjadikan verdict `CONFORMANT` pada W15 keliru** — saya menutup exit gate stage 12 dengan memperbaiki bukti pengecualian tanpa memeriksa apakah pengecualiannya sendiri diizinkan, padahal registry-nya melarang. Ditemukan saat verifikasi W20 ketika mendaftarkan reason code baru. **Ditutup pada remediasi W15, 2026-08-06.** `filterDormantUniverseRows()` dihapus dari evaluator beserta emisi reason code-nya; penyebut kembali menjadi universe temporal dikurangi `NOT_EXPECTED` terverifikasi saja. Dormansi dipindahkan ke `eod_eligibility.liquidity_state` sebagai observasi, tempat kontrak memang menempatkannya sebagai dimensi faktual terpisah, sehingga detektornya tidak menjadi kode tanpa pemanggil. Dua test yang menegaskan perilaku terlarang ditulis ulang. Dampak produksi terukur: **91 dari 962 instrumen aktif kembali ke penyebut**, sekitar 9,5% universe, menggeser batas lulus ambang 98% dari sekitar 854 ke sekitar 943 pengiriman |
| P1-41 280 listing tanpa sektor otoritatif | `OPEN` | Setelah IDX-IC authority work 2026-08-10, **280 dari 977 listing** tidak memiliki satu pun baris `EXCHANGE_AUTHORITATIVE`/`OPERATOR_ENTERED` pada `ticker_sector_memberships`, dan **6 di antaranya tidak punya membership sama sekali**. Pemecahannya menentukan sifat masalahnya: **91 listing tercatat sebelum 2022-07-01**, artinya seharusnya terjangkau baseline IDX-IC dan empat pengumuman yang ada, tetapi tidak berhasil dicocokkan — termasuk 17 ticker yang terbukti berpindah klasifikasi setelah Juli 2022 tanpa dokumen bertanggal, sehingga ditahan alih-alih diberi tanggal karangan. Sisanya **189 listing tercatat setelah 2022-07-01**, yaitu IPO yang memang belum tercakup pengumuman perubahan klasifikasi mana pun yang berhasil ditemukan. Konsekuensinya, `sector_roc20` dan `rs_20_vs_sector` untuk 280 emiten ini meresolusi ke `SECTOR_MEMBERSHIP_UNKNOWN`, bukan ke sektor yang salah — kegagalan yang terlihat, bukan senyap. Penutupan menuntut rekonsiliasi eksternal (Gate 13): pengumuman IDX 2023–2026 belum ditemukan, dan tanpa dokumen tidak ada dasar mendatangi `effective_from` |
| P1-42 pengumuman perubahan klasifikasi IDX 2023–2026 belum ditemukan | `OPEN` | Kadens resmi terkonfirmasi dua kali dari artefak ber-checksum: evaluasi tahunan diumumkan sekitar 24 Juni dan berlaku hari bursa pertama Juli, pola nomor `Peng-XXXXX/BEI.POP/06-YYYY`. Empat tanggal berlaku berhasil didokumentasikan — 2021-01-25, 2021-07-01, 2021-11-25, 2022-07-01 — dan **keempatnya mendahului awal dataset (2023-01-02)**, sehingga tidak satu pun reklasifikasi yang tercatat jatuh di dalam rentang yang dipakai indikator. Itu berarti **empat siklus Juli (2023, 2024, 2025, 2026) hilang**: perpindahan sektor yang terjadi di dalam rentang dataset tidak terwakili sama sekali, dan setiap emiten yang berpindah sejak Juli 2022 kini memakai sektor lamanya sepanjang periode. Ini akar-ekspektasi, bukan cacat kode: diamnya tabel bukan bukti tidak ada perubahan (Gate 12), dan satu-satunya obat adalah memperoleh empat pengumuman itu dari IDX |
| P1-43 satu membership masih mulai sebelum tanggal listing-nya | `OPEN` | `membership_id 922` memiliki `effective_from = 2024-01-17` sementara listing-nya `listed_date = 2024-01-18` — membership yang mulai satu hari sebelum instrumennya ada. Baris ini **bukan** bagian dari 190 yang dikoreksi pada 2026-08-10: koreksi itu menyasar baris yang memakai tanggal baseline karangan `2021-01-25`, sedangkan baris ini memakai tanggal yang tampak masuk akal sehingga lolos dari filter. Dampak resolusi nihil karena kelasnya `DERIVED_REFERENCE` dan `SectorClassificationRepository::AUTHORITATIVE_CLASSES` tidak memuatnya, jadi tidak ada indikator yang terpengaruh; yang tersisa adalah ketidakmungkinan temporal di dalam tabel. Sengaja **tidak** dimutasi saat ditemukan: instruksi yang ada bercakupan 190 baris, dan memperluasnya diam-diam akan menjadi mutasi produksi tanpa perintah terpisah |
| P1-39 pembeda activation ada dan tidak pernah dipanggil | `OPEN` | `MarketDataScope::isOperationallyActivatedFor()` dan `stateFor()` sudah benar dan **nol pemanggil di luar kelasnya sendiri**. Sementara itu `operational_start_date` tidak diset pada config dan `NULL` pada seluruh 71.917 run, sehingga setiap tanggal sebenarnya `DEVELOPMENT`. Akibatnya payload readiness melaporkan `is_ready => true` tanpa kualifikasi apa pun, dan frontier pengembangan tidak dapat dibedakan dari jaminan kesegaran operasional — persis yang dilarang klausa ketiga exit gate stage 19. Ditutup mekanismenya pada W19 dengan menyertakan `activation_state` pada payload readiness maupun consumer product; **yang belum ada tetap belum ada**, yaitu keputusan aktivasi itu sendiri: selama `operational_start_date` kosong, seluruh keluaran platform berstatus `DEVELOPMENT` dan tidak boleh dikutip sebagai kesegaran operasional. Ditemukan saat verifikasi exit gate W19 |
| P1-38 seluruh korpus replay adalah perbandingan diri sendiri | `OPEN` | 20.635 hasil replay tersimpan, **seluruhnya `MATCH`/`PASS`, nol `FAIL`, nol `BLOCKED`**, dan seluruhnya dari satu suite bernama `runtime_generated_valid_case`. Nama itu jujur menggambarkan cacatnya: `generateFixtureFromRun()` membangun state harapannya dengan memanggil `buildActualReplayState()` atas run yang sama, sehingga **oracle-nya adalah subjeknya**. Perbandingan seperti itu tidak dapat menghasilkan apa pun selain `MATCH`, dan itulah persis sebaran yang tercatat. Ditambah `config_identity` bernilai literal `'v1'` pada seluruh baris — satu nilai distinct — sehingga identitas config yang dibekukan menurut exit gate sebenarnya konstanta, dan membandingkannya tidak membuktikan apa pun. Di bawah `DOC-71` seluruh 20.635 hasil itu semestinya `BLOCKED` karena korpus publikasinya `CONFIG_UNBOUND`. Kedua aturan ditegakkan pada W18 sehingga replay baru menolak fixture yang dihasilkan sendiri maupun publikasi tanpa binding config; **20.635 hasil lama tetap tidak admissible dan tidak dihitung ulang**. Ditemukan saat verifikasi exit gate W18 |
| P1-37 read product benar tetapi tidak terpapar dan belum pernah dipakai | `OPEN` | `MarketDataReadProductService` memenuhi kontrak konsumen: publication-bound, run-bound, ber-versi, gagal tertutup ke payload kosong beserta reason, dan tanpa `MAX(date)` di seluruh jalur baca. Yang tidak ada adalah konsumennya. `routes/web.php` berisi 18 baris tanpa satu pun route market-data, dan `app/Application` maupun `app/Domain` hanya memuat `MarketData` — tidak ada domain hilir sama sekali di repositori ini. Akibatnya larangan bypass pada exit gate stage 17 **tidak dapat dilanggar sekaligus tidak dapat diamati**: ia terpenuhi oleh ketiadaan pihak yang dapat melanggarnya, yang di bawah gate 12 bukan bukti. W17 menutupnya dengan penjaga struktural agar larangan itu bernilai pada saat konsumen pertama muncul, bukan hanya menggambarkan keadaan hari ini. Yang belum terbukti tetap sama: tidak ada satu pun konsumen nyata yang pernah membaca produk ini. Ditemukan saat verifikasi exit gate W17 |
| P1-36 listing tersuspensi lenyap dari snapshot eligibility | `OPEN` | `EodEligibilityBuildService` menyaring listing tersuspensi keluar dari universe **sebelum** snapshot dibangun, sehingga instrumen yang terblokir tidak memperoleh baris sama sekali. Ini membalik fungsi snapshot itu sendiri: pembaca yang bertanya mengapa sebuah instrumen absen hari ini tidak memperoleh jawaban apa pun, dan absen-karena-tersuspensi menjadi tidak dapat dibedakan dari absen-karena-belum-pernah-tercatat. `EOD_Eligibility_Snapshot_Contract_LOCKED.md` mensyaratkan satu baris publication-bound per temporal listing dengan status tersimpan terpisah, yang justru kebalikan dari membuang barisnya. Ditambah, ketujuh kolom dimensi dan `eligibility_reasons_json` **nol terisi dari 749.685 baris**, dan 726.542 baris `eligible = 1` tidak membawa satu pun reason. Diperbaiki pada W16 untuk baris baru; **korpus lama tidak berubah** dan tetap tidak memuat baris untuk listing yang tersuspensi pada tanggalnya. Ditemukan saat verifikasi exit gate W16 |
| P1-35 bukti expectation/delivery tidak pernah tersimpan | `OPEN` | Kolom bukti coverage temporal ada sejak W03 dan hampir seluruhnya kosong: `coverage_expected_count` **0 dari 71.917**, `coverage_delivered_count` 0, `coverage_delivered_valid_count` 0, `coverage_expectation_unknown_count` 0, `coverage_bar_not_expected_count` hanya 84. Yang tersimpan hanya `coverage_universe_count`, dan namanya menyesatkan: `MarketDataPipelineService` menuliskan `expected_universe_count` **sesudah** penyaringan suspensi dan dormansi ke kolom itu, sehingga jumlah universe mentah tidak pernah tersimpan dan jumlah pengecualian suspensi tidak tersimpan sama sekali. Akibatnya rasio coverage tidak dapat direkonstruksi dari bukti tersimpan: run yang mengecualikan 40 instrumen dan run yang tidak mengecualikan satu pun menghasilkan catatan yang sama. Penulisnya dilengkapi pada W15 sehingga run baru menyimpan seluruh suku penyebut; **korpus lama tidak berubah** dan tetap tidak dapat diaudit terhadap exit gate stage 12. Ditemukan saat verifikasi exit gate W15 |
| P1-34 korpus ATR lama di-seed pada jendela geser | `CLOSED` | Wilder ATR adalah filter rekursif, sehingga nilainya bergantung pada tempat rekursi di-seed. `wilderAtr()` men-seed pada awal jendela muat 60 hari, bukan pada dataset/listing boundary yang diwajibkan blueprint stage 15, sehingga setiap tanggal memperoleh seed-nya sendiri dan deret yang dihasilkan bukan Wilder ATR melainkan rangkaian aproksimasi ber-seed independen. **Diukur pada 120 ticker produksi di `2026-07-28`**: selisih median terhadap nilai ber-seed boundary `0,34%`, persentil ke-90 `1,62%`, terburuk `72,9%`, dan `19 dari 120` melebihi `1%`. `atr14_pct` adalah masukan volatilitas untuk position sizing dan penempatan stop, sehingga kesalahan sebesar itu berpindah langsung ke ukuran posisi. Mekanismenya diperbaiki pada W14 dengan deret ATR ber-anchor boundary; **korpus lama tidak dihitung ulang**, dan selisih di atas adalah ukuran seberapa jauh nilai tersimpan menyimpang. Ditemukan saat verifikasi exit gate W14. **Ditutup 2026-08-11** setelah recompute 2023-01-02…2026-07-27 (843/843 tanggal, 0 gagal). Diukur dengan oracle independen yang membaca `eod_bars` dan menerapkan `EOD_Indicators_Formula_Spec.md` §"Stable seed" langsung, tanpa memanggil `IndicatorVectorService` — 40 ticker berhistori panjang, **32.332 titik: p50 dan p90 keduanya 0,000000%, maks 0,0094%, nol titik menyimpang ≥0,01%** (korpus lama: p90 1,62%, maks 72,9%). Argumen penutupnya bukan sekadar besaran selisih: seed jendela geser **tidak dapat konvergen**, karena setiap tanggal memperoleh seed sendiri dan menyimpang di sepanjang deret; konvergensi ke rekursi ber-anchor hanya mungkin bila seed-nya memang berjangkar. Dicatat untuk siapa pun yang mengulang pengukuran ini: jalannya yang pertama men-seed satu bar terlalu awal dan menghitung satu TR degenerate `high-low`, sehingga memunculkan penyimpangan warm-up semu hingga 10,26% yang seluruhnya milik oracle, bukan korpus; spec menyatakan bar pertama hanya memasok previous close sehingga seed jatuh pada indeks 14 |
| P1-33 korpus dv20 lama dihitung pada deret yang disesuaikan | `CLOSED` | `dv20_idr` terisi pada 735.719 dari 756.328 baris, seluruhnya dihitung memakai `averageTurnover()` atas bar yang **sudah disesuaikan**. Untuk split hal itu tidak berdampak karena faktor harga dan volume saling berkebalikan, tetapi untuk aksi yang menskalakan harga tanpa menskalakan volume — `RIGHTS_ISSUE` ber-`price_continuity_impact = SCALED` dan `volume_continuity_impact = NONE`, 68 di antaranya bersumber IDX — hasilnya adalah **adjusted price x raw volume**, persis yang dilarang exit gate stage 14. Terbukti terukur: fixture rights-issue faktor `0,8` menghasilkan `80.000` di bawah perilaku lama versus `100.000` yang benar, yaitu turnover dinyatakan 20% lebih rendah sepanjang jendela, tepat pada angka yang dipakai filter likuiditas. Rumusnya diperbaiki pada W13 sehingga baris baru memakai `RAW close x RAW volume`; **korpus lama tidak dihitung ulang**. Sejauh mana korpus itu terpengaruh bergantung pada berapa banyak jendela 20 hari yang melintasi aksi harga-saja, dan itu belum diukur. Ditemukan saat verifikasi exit gate W13. **Ditutup 2026-08-11** setelah recompute 2023-01-02…2026-07-27 (843/843 tanggal, 0 gagal). Yang dulu belum diukur kini terukur: oracle independen membaca `eod_bars` tanpa menerapkan faktor apa pun, mengikuti spec `CVP(X) = C_raw(X) * V_raw(X)`, dan **48.901 titik cocok persis — maks divergensi 0,000000%**. Termasuk di dalamnya **6.532 titik pada ticker `RIGHTS_ISSUE`/`NON_PREEMPTIVE_RIGHTS_ISSUE`**, yaitu justru kasus yang temuan ini namai (80.000 vs 100.000). `dv20_idr` NULL pada 20.796 baris dan seluruhnya berpenjelasan: **18.500 warm-up** (kurang dari 20 bar tersedia, jendela belum dapat dibentuk) dan **2.296 sisanya pasca-warm-up berimpit dengan aksi korporasi**, yakni kuarantina yang memang diwajibkan — bukan lubang diam |
| P1-32 vektor indikator lama tidak menyatakan price product-nya | `CLOSED` | **756.328 baris `eod_indicators`, nol memiliki `price_product_code`.** Kolomnya ada sejak W03 dan tidak pernah ditulis. Akibatnya baris berbasis `RAW` dan berbasis `STRUCTURAL_ADJUSTED` duduk dalam satu kolom tanpa dapat dibedakan, padahal keduanya tidak sebanding: jendela yang melintasi split 1:5 berbeda sebesar rasio split, bukan beberapa persen. Penulisnya ditambahkan pada W12 sehingga baris baru menyatakan produknya, tetapi **korpus lama tidak berubah**. Lebih jauh, sebagian baris lama dihitung memakai 15 faktor turunan yang diblokir pada W11, sehingga label yang benar untuknya pun belum tentu dapat direkonstruksi tanpa hitung ulang. Penutupannya menuntut recompute berbukti, bukan pengisian kolom retroaktif. Ditemukan saat verifikasi exit gate W12. **Ditutup 2026-08-11** oleh recompute berbukti itu, bukan oleh relabel: `market-data:eod-indicators:recompute-current 2023-01-02 2026-07-27` memproses **843 dari 843 tanggal dengan `failed_count=0` dan `skipped_count=0`**, dihitung dari log per-tanggal dan bukan dari exit code karena `--continue-on-error` melewati tanggal gagal tanpa menghentikan run. `price_product_code` kini terisi pada **756.328 dari 756.328 baris** di 844 tanggal, seragam `STRUCTURAL_ADJUSTED`, sehingga tidak ada lagi baris `RAW` dan `STRUCTURAL_ADJUSTED` yang duduk bercampur tanpa dapat dibedakan. Publikasi current 844 untuk 844 tanggal. Tanggal 2026-07-28 berada di luar rentang recompute dan diperiksa terpisah: barisnya ditulis 2026-08-10 22:55:40 sedangkan tulisan membership terakhir 22:43:36, jadi ia sudah memakai state otoritatif dan bukan sisa yang tertinggal |
| P1-31 tidak ada satu pun adjustment factor yang bersumber | `OPEN` | Setelah faktor turunan diblokir pada W11, **nol adjustment factor tersisa di seluruh platform**. Sebelum filter 15, sesudah filter 0. Ini bukan regresi melainkan pengungkapan: seluruh kapabilitas adjustment selama ini berjalan di atas rasio yang disimpulkan platform sendiri dari deret harga. Terpisah, 126 aksi ber-impact `SCALED` bersumber `idx_corporate_action` — 35 `STOCK_SPLIT`, 68 `RIGHTS_ISSUE`, 14 `BONUS_SHARE`, 8 `STOCK_DIVIDEND`, 6 `MERGER` — tidak memiliki faktor maupun terms yang dapat dipakai, dan hanya 2 dari 520 baris IDX membawa `ratio_from`/`ratio_to`. Konsekuensinya benar dan mahal: 126 jendela itu kini terkarantina sebagai terkontaminasi, bukan dihaluskan diam-diam. Penutupannya menuntut terms aksi korporasi otoritatif dari IDX, yaitu rekonsiliasi eksternal — tidak ada kode yang dapat menutupnya. Ditemukan saat verifikasi exit gate W11 |
| P1-30 band, floor, dan tick belum bersumber dan belum effective-dated | `OPEN` | `Exchange_Market_Structure_Facts_LOCKED.md:79` mensyaratkan setiap nilai band/floor/tick yang dipakai dalam keputusan market-data meresolusi dari baris ber-effective-date dan ber-source-reference, dan melarang konstanta tak bersumber dipakai untuk keputusan apa pun yang mencapai published output. Keadaan nyata: tidak ada tabel tier sama sekali, `min_price_idr` adalah konstanta config `50` tanpa sumber maupun effective date, dan dokumen registry sendiri mencatat band sebagai skalar hardcoded `0.35` yang berstatus placeholder. Lima aksi berstatus `GAP_BEYOND_EXCHANGE_BAND` diputuskan memakai band tak bersumber itu. Dampak langsungnya berkurang setelah W11 memblokir faktor turunan mencapai published output, tetapi verdict band-based tetap tidak boleh disebut exchange-verified. Penutupannya menuntut tabel tier band, floor, dan tick yang bersumber dan ber-effective-date dari IDX — rekonsiliasi eksternal, bukan pekerjaan kode. Ditemukan saat verifikasi exit gate W11 |
| P1-29 seluruh korpus canonical tidak dapat ditelusuri | `OPEN` | **756.329 baris `eod_bars`, nol memiliki `source_observation_id`**, dan sama untuk `listing_id`, `canonicalization_version`, `price_product_code`, `quality_state`, serta `config_snapshot_id`. Sebabnya bukan kolom yang hilang — W03 sudah membuatnya — melainkan penjaga yang tidak pernah berjalan: `EodBarsIngestService` menggerbangi seluruh penulisan lineage di balik `$strictLineage = ! empty($run->config_snapshot_id)`, sementara `assertRequiredLineage` yang dijaganya justru melempar ketika nilai yang sama kosong. Penjaga itu hanya bisa berjalan setelah prasyaratnya sendiri terpenuhi, sehingga cabang config-nya **tidak terjangkau secara konstruksi**, dan karena binding config tidak pernah terisi pada 71.917 run, kewajiban lineage tidak pernah dieksekusi satu kali pun. `Canonicalization_Contract_EOD_Bars.md:138` melarang memancarkan untraceable row tanpa syarat. **Mekanismenya diperbaiki pada W09; korpusnya tidak berubah.** Baris lama tetap tidak admissible sebagai bukti asal-usul, dan mengikuti aturan `DOC-71`: gate yang tidak pernah ditegakkan tidak membuat korpusnya conformant lewat kesunyian. Penutupannya menuntut re-ingest berbukti, bukan pengisian kolom secara retroaktif — mengisi lineage sekarang akan melekatkan observation yang bukan penghasil baris itu. Ditemukan saat verifikasi exit gate W09 |
| P1-28 denominator as-of tidak deterministik untuk tanggal tetap | `OPEN` (akar penyebab didiagnosis pada W15) | Basis `ACTIVE_LISTED_EQUITY_AS_OF_DATE` seharusnya menghasilkan angka yang sama untuk tanggal yang sama, kapan pun run dieksekusi. Pada `trade_date_requested = 2026-06-02`, tiga run di hari eksekusi yang sama (`2026-06-07`) menghasilkan `950` pukul 11:34, `949` pukul 12:42, lalu `950` kembali pukul 22:41. Selisihnya satu instrumen dan bergerak bolak-balik, sehingga bukan perubahan universe yang sah melainkan resolusi yang bergantung pada sesuatu yang berubah antar-eksekusi. Run tengah ber-`final_reason_code = RUN_LOCK_CONFLICT`. Dampaknya pada gate kecil tetapi arahnya salah: denominator adalah penyebut coverage, dan penyebut yang bergerak membuat rasio dua run atas tanggal yang sama tidak dapat dibandingkan. Satu-satunya kejadian dalam 68.411 run. Ditemukan saat verifikasi exit gate W08; diserahkan ke `W15` yang memiliki `Coverage_Gate_Contract` (`F-006` pada ledger) |
| P1-27 membership sector: sumber campur dan interval tak pernah ditutup | `PARTIAL` | Dua cacat pada `ticker_sector_memberships`. Pertama, seluruh baris berlabel `classification_system = 'IDX-IC'` tetapi sumbernya campur: `idx_stock_screener` 888, `ksei` 6, `idx_profile` 3, `investing` 2, `invesnesia` 1, dan `lembarsaham` 1 — empat baris terakhir bukan sumber IDX, sehingga di bawah `Sector_Classification_Contract_LOCKED.md` berkelas `DERIVED_REFERENCE` dan **bukan membership otoritatif**. Kedua, dari **971 membership untuk 971 emiten, nol memiliki `effective_to`**: struktur temporalnya lengkap dan tidak pernah dipakai, sehingga reklasifikasi IDX tidak pernah tercatat dan setiap tanggal historis meresolusi ke sektor hari ini. Dampaknya langsung ke `sector_roc20` pada 739.651 baris dan `rs_20_vs_sector` pada 734.645 baris indikator. Bentuknya sama dengan survivorship pada order 6. Ditemukan saat penutupan `DOC-15`. **Diturunkan ke `PARTIAL` pada 2026-08-10** oleh sector IDX-IC authority work (`SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`). Cacat pertama tertutup: seluruh 971 baris legacy kini berkelas eksplisit `DERIVED_REFERENCE` di 12 `source_name`, dan lapisan otoritatif dibangun terpisah dari 721 baris `EXCHANGE_AUTHORITATIVE` bersumber `idx_announcement` untuk 697 listing, seluruhnya berprovenans pengumuman IDX ber-checksum. Cacat kedua tertutup secara mekanisme: 12 interval kini punya `effective_to` dan `supersedes_membership_id` terisi — nilai bukan-nol pertama yang pernah dipegang kolom itu. Yang menahan penutupan penuh adalah **dampaknya**, bukan mekanismenya: 280 dari 977 listing masih tanpa membership otoritatif sama sekali (lihat `P1-41`), sehingga untuk emiten-emiten itu resolusi point-in-time tidak menghasilkan sektor apa pun. Recompute atas rentang terdampak **selesai 2026-08-11** (843/843 tanggal, 0 gagal) dan hasilnya mengukur sisa temuan ini dengan tepat: `sector_roc20` turun dari 739.651 ke **550.667** baris dan `rs_20_vs_sector` dari 734.645 ke **548.813**, sementara `sector_membership_id` terisi pada **564.567** baris — sama persis dengan 756.328 dikurangi 191.761 baris milik listing tanpa membership otoritatif. Penurunan itu bukan kehilangan data melainkan `NULL` yang jujur menggantikan sektor hari-ini yang sebelumnya dipakaikan mundur ke seluruh sejarah; temuan ini tetap `PARTIAL` justru karena 191.761 baris itu masih menunggu `P1-41` |
| P1-26 dua migration terakhir tidak pernah diterapkan | `CLOSED` | **Ditutup pada W03, 2026-08-03, dengan akar penyebab yang berbeda dari dugaan awal.** Migration `2026_08_03_000001_harden_market_data_orders_1_to_4.php` mendeklarasikan class `HardenMarketDataOrdersOneToFour`, sementara migrator meresolusi nama dari berkasnya menjadi `HardenMarketDataOrders1To4`. Karena pemeriksaan `class_exists` gagal, Laravel jatuh ke `getRequire()` yang memakai `require` biasa atas berkas yang sudah di-`require`, dan run mati dengan *Cannot declare class ... because the name is already in use* **sebelum satu pun statement dijalankan**. Migration itu karena itu **tidak pernah bisa dijalankan sejak ditulis** — bukan terlupakan. Nama class dikoreksi, kedua migration diterapkan: V2 foundation `1.795,64ms`, orders 1-4 `351,05ms`. `ALGORITHM=INSTANT` terkonfirmasi oleh durasi tersebut atas 32 GB tabel history. Penjaga `MigrationIntegrityAndDriftTest` ditambahkan agar cacat sejenis tertangkap: kecocokan nama class terhadap berkas, ketiadaan class ganda, kelengkapan `up`/`down`, dan drift dua arah antara berkas migration dan catatan penerapan. |
| P1-25 tidak ada satu pun config snapshot yang terikat | `OPEN` | `eod_runs` memiliki `config_version`, `config_hash`, dan `config_snapshot_ref`, tetapi dari **71.917 run tidak satu pun terisi** — nol hash, nol ref, dan hanya satu nilai `config_version` yang seragam. `eod_publications` tidak memiliki kolom config sama sekali, padahal `:21` mensyaratkan snapshot ID dan hash yang sama mengikat run, setiap output artifact, publication manifest, dan dataset seal. Tidak ada tabel yang menyimpan objek snapshot config sebagaimana dijelaskan `:11`-`:19`. Akibatnya 33.414 publikasi tersegel berstatus `CONFIG_UNBOUND` di bawah `DOC-71`, 844 di antaranya current, dan tidak ada satu pun yang dapat direplay sebagai bukti reproducibility. Ditemukan saat verifikasi order 16 **Dependency schema dihilangkan pada W03** — `md_config_snapshots` dan kolom `config_snapshot_id` pada dua belas tabel kini ada, seluruhnya kosong atau `NULL`. Keadaan `CONFIG_UNBOUND` pada 33.414 publikasi tersegel tidak berubah dan tetap tidak admissible sebagai bukti reproducibility. |
| P1-24 metrik likuiditas hanya satu kolom alias | `OPEN` | `eod_indicators` hanya memiliki `dv20_idr` sebagai kolom likuiditas. `Volume_and_Turnover_Normalization_LOCKED.md` mensyaratkan `traded_value_idr_actual` yang `NULL` ketika tidak source-backed, proxy bernama eksplisit `close_volume_proxy_idr`, kedua rata-rata 20 harinya, serta trade count yang nullable dan terpisah. Tidak ada field penanda actual-versus-proxy, formula version, basis, maupun window, sehingga kewajiban labelling pada `DOC-65` belum punya tempat. Dimensinya sendiri terbukti benar: `dv20_idr` sama persis dengan `AVG(close x volume)` atas 20 bar, rasio `1,0000`. Yang gagal adalah penamaan dan pemisahan, bukan aritmetikanya. Ditemukan saat verifikasi order 14 |
| P1-23 eligibility snapshot menyimpan 2 dari 19 fakta wajib | `OPEN` | `EOD_Eligibility_Snapshot_Contract_LOCKED.md:17` mewajibkan setiap baris menyimpan secara terpisah 19 fakta dalam lima kelompok. `eod_eligibility` nyata hanya memiliki `trade_date`, `ticker_id`, `eligible`, `reason_code`, `run_id`, `publication_id`, dan `created_at`. Seluruh dimensi quality, liquidity, status, dan event-risk **tidak ada sebagai kolom**. `reason_code` bertipe `varchar(64)` tunggal sehingga tidak dapat menampung ordered reason-code set yang disyaratkan, dan sekunder pasti terhapus. Dari 749.685 baris, hanya 23.143 membawa reason — seluruhnya baris tidak eligible — sehingga 726.542 baris eligible tidak dapat dijelaskan sama sekali. Ini kegagalan Done criteria order 13 sepenuhnya di sisi implementasi; kontraknya sudah benar dan mandatory. Ditemukan saat verifikasi order 13 **Dependency schema dihilangkan pada W03** — `eod_eligibility` naik dari 7 menjadi 17 kolom, memuat dimensi expectation, delivery, quality, liquidity, status, dan event risk. Seluruh kolom baru `NULL`; larangan merekonstruksi dimensi dari `reason_code` kini punya field tujuan, dan yang belum ada adalah penulisnya. |
| P1-22 seluruh evidence coverage berasal dari resolver pra-temporal | `OPEN` | 68.411 run coverage tersimpan seluruhnya dibuat antara `2026-05-28` dan `2026-08-01`; `TemporalIdentityRepository` yang menutup P0-02 baru ada `2026-08-03 13:29`. **Nol run coverage dihasilkan resolver saat ini.** Denominator pada seluruh evidence itu berasal dari universe yang berpotensi survivorship-biased, sehingga tidak admissible sebagai bukti kebenaran temporal di bawah `DOC-61`. Ia tetap sah sebagai catatan keputusan platform saat itu. Penutupannya menuntut derivasi ulang di bawah resolver saat ini, atau kualifikasi eksplisit di setiap tempat evidence itu dikutip — termasuk pada re-audit order 22. Ditemukan saat verifikasi order 12 |
| P1-21 identitas price-product tidak tersimpan di mana pun | `OPEN` | Pencarian kolom bernuansa `basis`, `factor`, `product`, atau `formula` pada `eod_runs`, `eod_publications`, `eod_indicators`, dan `eod_indicators_history` mengembalikan **nol**; satu-satunya kecocokan adalah `coverage_universe_basis` yang tentang universe, bukan harga. Akibatnya aturan one-basis-per-run tidak dapat ditegakkan maupun diaudit, dan Consumer boundary `:45` yang mewajibkan publikasi mengekspos price-basis identity tidak dapat diimplementasikan. Done criteria order 11 karena itu **tidak terukur**, bukan gagal. Bergandengan dengan P0-04, yang mencatat runtime default masih `close` alih-alih `STRUCTURAL_ADJUSTED`. Ditemukan saat verifikasi order 11 **Dependency schema dihilangkan pada W03** — kolom `price_product_code` kini ada pada enam tabel. Seluruh 756.329 baris `eod_bars` bernilai `NULL`, sehingga aturan one-basis-per-run masih belum dapat diverifikasi setelah run berakhir. |
| P1-20 factor revision model tidak terimplementasi | `OPEN` | `Price_Adjustment_Contract_LOCKED.md:15`–`:27` mewajibkan setiap applied factor revision mengikat event identity dan revisi, verified `ex_date`, price/volume factor, quantitative source terms dan formula turunan, action semantics dan **verification state**, source observation/reference/hash, factor version, created/known timestamp, serta superseded factor revision — dan menyatakan factor rows bersifat **append-only**. Kenyataannya tidak ada tabel factor revision sama sekali; faktor hidup sebagai kolom mutable `price_adjustment_factor` dan `volume_adjustment_factor` pada `market_data_corporate_actions`, lengkap dengan `updated_at`. Tidak ada kolom verification state, factor version, maupun superseded revision. Append-only karena itu mustahil ditegakkan. Ini P1-08 dalam bentuk konkret. Ditemukan saat verifikasi order 10 **Dependency schema dihilangkan pada W03** — `md_adjustment_factors`, `md_adjustment_factor_sets`, dan `md_corporate_action_revisions` kini ada, seluruhnya kosong. Append-only, verification state, dan factor version kini punya tempat; yang belum ada adalah writer dan migrasi 15 faktor lama dari kolom mutable. |
| P1-19 bar yatim pada tanggal non-perdagangan | `OPEN` | `eod_bars` memuat satu baris untuk `trade_date = 2024-12-14`, `ticker_id = 327`, dengan nilai nyata `close = 256` dan `volume = 1.112.200`. Tanggal itu adalah **hari Sabtu**: `market_calendar` mencatat `is_trading_day = 0`, `holiday_name = 'AKHIR PEKAN'`. Baris ini melanggar aturan validasi ke-7 `EOD_Bars_Contract` dan aturan trading-window kontrak kalender. Publikasinya (`64942`) ada tetapi `is_current = 0` — **gate publikasi menangkapnya dan tidak pernah menjadikannya current**, sehingga pertahanan berlapis bekerja. Yang tersisa adalah barisnya masih menetap di proyeksi live, membuat `eod_bars` tidak dapat dibangun ulang persis dari publikasi current. Satu-satunya kejadian di seluruh 756.329 bar; nol bar pada tanggal yang tidak ada di kalender. Remediasi melalui correction lifecycle, bukan penghapusan langsung. Ditemukan saat verifikasi order 9 |
| P1-18 cacat akuisisi volume pada `2023-05-24` | `OPEN` | 26 canonical bar memiliki `volume = 0` dengan `high > low`, melanggar aturan cross-field ke-10 yang baru dikunci `DOC-44`. **Seluruhnya pada satu tanggal**, `2023-05-24`, lintas banyak emiten. Tanggal itu memuat 863 bar dengan 123 di antaranya bervolume nol — 14,3% berbanding baseline 8,7% seluruh dataset — sehingga 97 sisanya patut dicurigai membawa cacat yang sama namun tersamar oleh OHLC yang kebetulan datar. Dampak weekly swing langsung: `dv20` untuk emiten terdampak tertekan selama 20 sesi sesudahnya. Remediasi mengikuti correction/republication lifecycle, bukan perbaikan in-place. Ditemukan saat verifikasi order 8 |
| P1-17 `market_calendar` jauh di bawah kontraknya | `OPEN` | Tabel nyata hanya memiliki `cal_date`, `is_trading_day`, `holiday_name`, `session_open_time`, `session_close_time`, `breaks_json`, `source`, dan dua timestamp. Kontrak mewajibkan pula identitas exchange/market-segment, session state (`scheduled`/`open`/`completed`/`cancelled`/`unknown`), penanda `is_half_day`, `prev_trading_day`/`next_trading_day`, serta calendar version/revision dan `recorded_at`/`known_at`. Konsekuensi langsung: session state selalu tidak diketahui, sehingga aturan `:41` — cutoff wall-clock tidak memadai ketika session state tidak diketahui — semestinya menahan setiap publikasi latest-date. Ditambah `session_close_time` `NULL` pada seluruh 1979 hari perdagangan dan tier provenance `DOC-42` yang belum memiliki kolom. Ditemukan saat verifikasi order 7 **Sebagian ditutup pada W06, 2026-08-03.** Provenance tier `DOC-42` kini terimplementasi: kolom `provenance_tier`, `reconciled_at`, dan `reconciliation_source_ref` ditambahkan ke `market_calendar` dan `md_market_calendar_revisions`, dengan backfill berbasis bukti — tahun tanpa satu pun hari libur nasional tercatat tidak dapat menjadi tahun IDX nyata. Hasilnya 2023–2027 `VERIFIED` dan 2028–2030 `PROJECTED`. `assertCompletedRegularSession()` kini menolak baris non-`VERIFIED` dengan `MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED`, sehingga ekspektasi bar dari periode proyeksi adalah `UNKNOWN` dan tidak pernah `EXPECTED`. Sisa yang masih terbuka: `session_state` sudah ada pada revisi tetapi `session_close_time` legacy tetap `NULL` pada seluruh 1979 hari perdagangan, dan `is_half_day` tetap `0` karena sumber tidak menyediakannya — `DOC-43` belum dapat ditegakkan. |
| P1-16 tabel identitas temporal belum ada di MariaDB | `CLOSED` | `TemporalIdentityRepository::universeAsOf()` menjoin `md_listings`, `md_instruments`, `md_issuers`, dan `md_listing_symbols`. Keempatnya ada di mirror SQLite dan pada migration `2026_08_02_000001_add_market_data_strategy_v2_foundation.php` serta `2026_08_03_000001_harden_market_data_orders_1_to_4.php`, tetapi `information_schema` MariaDB mengembalikan **nol** untuk keempatnya — migration belum dijalankan terhadap database aktif. Akibatnya jalur universe temporal lulus di test dan tidak dapat berjalan di produksi. Ditemukan saat verifikasi order 6 **Dependency schema dihilangkan pada W03** — keempat tabel `md_listings`, `md_instruments`, `md_issuers`, dan `md_listing_symbols` kini ada setelah `P1-26` ditutup. Seluruhnya **kosong**; writer, backfill, dan pembuktian resolver terhadap data nyata belum ada, sehingga temuan tetap terbuka dengan sifat yang berubah dari *tabel tidak ada* menjadi *tabel tanpa penulis*. **Ditutup pada W05.** Keempat tabel identitas temporal kini terisi 977 baris masing-masing melalui proyeksi dari master legacy, dan resolver terbukti as-of terhadap data nyata. Enam fixture exit gate terkunci pada `TemporalIdentityFixturesTest`: delisting, listing yang belum berlaku, rename, symbol reuse, symbol teretraksi, dan revisi provider mapping. |
| P1-15 factor turunan bertahan setelah jalur pembuatnya dipensiunkan | `OPEN` | `market_data_corporate_actions` masih memuat **15 baris** dengan `adjustment_source = 'DERIVED_FROM_PRICE_SERIES'`. Jalur yang membuatnya sudah dipensiunkan dan kini mengembalikan `capability_state = 'DETECTION_ONLY'`, tetapi datanya tetap hidup dan masih menjadi basis structural-adjusted product serta indicator yang sudah direcompute. P0-03 mensyaratkan *"verified/manual/authoritative action wajib sebelum factor dipakai"* — memensiunkan penulisnya tidak dengan sendirinya membuat tulisan lama admissible. Perlu keputusan eksplisit per baris: ditegakkan ulang dengan bukti otoritatif, atau dicabut beserta output yang bergantung padanya. Membiarkannya meninggalkan jejak yang tidak lagi tersambung ke kode mana pun |

Backlog di atas menjelaskan mengapa implementation/production relock belum boleh diberikan. Backlog tersebut justru menunjukkan bahwa owner strategy sekarang cukup preskriptif untuk membedakan conforming dan non-conforming behavior. Ia tidak membatalkan documentation closure.

### Documentation remediation register — per dokumen

Backlog di atas disusun **per temuan**. Register di bawah disusun **per dokumen**: file mana yang masih kurang pernyataan, dan pernyataan apa. Keduanya melengkapi, tidak menggantikan.

Batas peran terhadap `../MARKET_DATA_IMPLEMENTATION_LEDGER.md`: ledger mencatat **apa yang sudah dijalankan** (`W00`–`W22`, status, evidence, next command); register ini mencatat **apa yang masih kurang ditulis**. Ledger tidak boleh menyalin register ini, dan register ini tidak boleh mencatat execution state.

Sebuah item hanya masuk register bila kekurangannya sudah diverifikasi dengan pembacaan file, hasil query, atau hasil grep — bukan dugaan.

| ID | Order | Dokumen | Pernyataan yang belum ada | Bukti verifikasi | State | Penutupan |
|---|---:|---|---|---|---|---|
| `DOC-01` | 3 | `../../book/Yahoo_Finance_Bootstrap_Source_Strategy.md` | Capability matrix untuk adapter/endpoint aktif: kapabilitas mana `SUPPORTED`, mana `UNSUPPORTED`, mana `DIAGNOSTIC_METADATA_ONLY`, mana `UNSUPPORTED_AS_AUTHORITY`. Setiap baris membawa bukti penentuan dan terikat pada phase/endpoint/adapter/mapping revision, bukan "permanen". Konsekuensi pemakaian tetap milik owner contract; matrix hanya menyatakan fakta provider dan menunjuk ownernya | Acceptance criteria pada `:155`–`:165` hanya menegakkan disiplin lingkup paid-provider; tidak ada satu pun pernyataan kapabilitas negatif | `CLOSED` | Section **Source capability matrix (LOCKED)** ditambahkan dengan 8 baris kapabilitas, binding revision, batas kekuatan bukti, dan catatan defect adapter |
| `DOC-02` | 10 | `../../registry/Price_Scale_Break_Detection_LOCKED.md` | Lantai deteksi `min_ratio = 1.7` (≈41,6% gerakan satu sesi) dan wilayah buta di bawahnya. Kontrak berargumen menolak menaikkan ambang rasio tanpa pernah menyatakan bahwa ambang rasio itu ada | 74 corporate action `GAP_AMBIGUOUS` bergap ≤ 24,50%, seluruhnya di bawah lantai; 38 di antaranya rights issue yang penurunan ex-date-nya 10–30% dan permanen tak terlihat oleh detektor ini | `CLOSED` | Section **Detection sensitivity boundary (LOCKED)** ditambahkan, memuat ratio floor, min-price guard, adjacency, wilayah buta, aturan *silence is not evidence*, dan kewajiban disclosure saat ambang diubah |
| `DOC-03` | 10 | *belum ada owner* | Auto-rejection band bursa (ARA/ARB) sebagai fakta pasar bernama, dengan pemisahan tegas antara perannya sebagai pembeda gerakan pasar versus perubahan skala (market-data) dan perannya sebagai batas keterlaksanaan order (di luar lingkup) | Grep `auto-reject\|ARA\|ARB\|0.35\|session move` ke seluruh dokumen market-data: nol hasil, padahal `MAX_EXCHANGE_SESSION_MOVE = 0.35` dipakai sebagai batas keputusan di runtime | `CLOSED` | Owner baru `../../registry/Exchange_Market_Structure_Facts_LOCKED.md`; band, minimum price, dan tick ladder wajib tiered dan effective-dated; konstanta `0.35` tercatat eksplisit sebagai placeholder tak bersumber |
| `DOC-04` | 7 | `../../book/Trading_Status_Source_Contract_LOCKED.md` | Sumber mana yang memegang status authority; source priority ketika sumber berbeda pendapat; apakah controlled manual import hanya transport atau membawa authority tertentu | Pembacaan file: `UNKNOWN/NO_EVIDENCE` saat baris status absen sudah benar di `:35`, degraded state saat sumber mati sudah benar di `:68`. Hanya ketiga hal di atas yang kosong | `CLOSED` | Section **Source authority (LOCKED)** ditambahkan: tiga authority class, aturan priority dan konflik, serta manual import sebagai transport yang tidak dapat menaikkan authority |
| `DOC-05` | 4–15 | `../../book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md` | Dua langkah tambahan pada per-work-order loop `:106`: (a) test yang mengunci behavior yang ditolak stage ini ditandai `SUPERSEDED` atau diganti **pada stage yang sama**; (b) setiap source/detector/resolver/validator menyatakan apa yang dapat dibuktikannya, apa yang hanya diagnostic, apa yang tidak dapat dilihatnya, dan fail-safe state ketika evidence tidak tersedia | Loop langkah 1–10 sudah mewajibkan **kontribusi** schema/config/test, tetapi tidak pernah mewajibkan **pencabutan** maupun deklarasi wilayah buta | `CLOSED` | Loop menjadi 13 langkah; langkah 3 dan 7 memikul retirement obligation, langkah 8 memikul capability boundary obligation, masing-masing dengan section penjelas |
| `DOC-06` | 16, 20, 21 | `../../book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md` | Kondisi pemicu `SUPERSEDED` — statusnya sudah terdefinisi tetapi tidak ada yang menyatakan kapan ia wajib dipakai. Global no-omission gates belum punya gate untuk wilayah buta detektor; gate 6 (positive/negative proof) membuktikan penolakan input buruk, bukan wilayah tempat detektor diam | Pembacaan file `:37` dan `:42`–`:55` | `CLOSED` | Pemicu wajib ditambahkan pada definisi `SUPERSEDED`; global no-omission gates bertambah menjadi 12, dengan gate 11 (capability boundary) dan gate 12 (larangan memakai ketiadaan sinyal sebagai bukti) |
| `DOC-07` | tabel urutan | dokumen ini, `:442` | Tabel urutan tidak menyatakan bahwa nomor order bukan urutan eksekusi, sehingga dapat dibaca seolah source acquisition dikerjakan sebelum temporal identity | **Temuan awal terkoreksi.** Penilaian semula menyatakan order 6 perlu dinaikkan ke atas order 4. Blueprint ternyata sudah melakukannya: `W05` = stage 6, `W06` = stage 7, `W07` = stage 4, `W08` = stage 5. Yang keliru hanya tabel ringkasan audit yang tidak menampilkan sekuens tersebut | `CLOSED` | Catatan **nomor order bukan urutan eksekusi** ditambahkan di bawah `:442`, memuat alasan survivorship-di-sumber |

#### Capability boundary program — pemenuhan gate 11

`DOC-05` dan `DOC-06` menciptakan kewajiban baru: blueprint langkah 8 dan global gate 11 mensyaratkan setiap source, detector, resolver, dan validator menyatakan batas kemampuannya. Kewajiban itu awalnya tidak dipenuhi satu owner contract pun selain yang menciptakannya — gate yang tidak dipenuhi siapa pun akan diabaikan pada `W00`.

Item berikut memenuhi gate tersebut pada komponen yang diamnya paling menentukan untuk weekly swing.

| ID | Order | Dokumen | Pernyataan yang ditambahkan | State |
|---|---:|---|---|---|
| `DOC-08` | 7 | `../../book/Market_Calendar_Requirements_Contract.md` | Kalender adalah akar ekspektasi, sehingga tidak ada gate hilir yang dapat mendeteksi kalender yang salah. Sesi yang tidak pernah tercatat hilang dari numerator dan denominator sekaligus. Kelengkapan karena itu direkonsiliasi ke jadwal resmi bursa, dua arah, di luar pipeline harian | `CLOSED` |
| `DOC-09` | 8 | `../../book/Canonicalization_Contract_EOD_Bars.md` | Konsistensi internal adalah relasi antar angka, bukan relasi terhadap pasar. Baris bertanggal benar yang mengulang sesi sebelumnya lolos seluruh validasi. `canonical-valid` berarti layak sebagai bar, bukan terverifikasi terhadap pasar | `CLOSED` |
| `DOC-10` | 12 | `../../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` | Coverage menghitung observasi, bukan harga, dan bersifat self-consistent di bawah kalender yang salah — rasio tetap bersih ketika satu sesi nyata hilang seluruhnya. Beban pembuktian kalender dialihkan eksplisit ke `DOC-08` | `CLOSED` |
| `DOC-11` | 13 | `../../book/EOD_Eligibility_Snapshot_Contract_LOCKED.md` | Batas terhadap policy sudah ada; yang ditambahkan adalah batas terhadap correctness. `data_usable = true` berarti tidak ada gate yang keberatan, bukan data terbukti benar; wilayah buta tiap gate saling menumpuk, tidak saling meniadakan | `CLOSED` |
| `DOC-12` | 15 | `../../registry/Indicator_Registry_Baseline_LOCKED.md` | Nilai non-null bukan bukti window bersih; reason set kosong mencatat apa yang terdeteksi, bukan apa yang terjadi. `ROC20` sekitar −90% melintasi split 10:1 yang belum disesuaikan tidak dapat dibedakan secara numerik dari penurunan nyata | `CLOSED` |
| `DOC-13` | 18 | `../../book/Replay_Verification_Contract_LOCKED.md` | Replay membandingkan output terhadap dirinya sendiri di bawah input tetap. Publikasi yang dihitung dari aksi korporasi yang terlewat direproduksi persis dan menghasilkan `PASS`. Verdict berarti pipeline sepakat dengan dirinya sendiri, tidak pernah berarti datanya benar; `PASS` tidak boleh menutup temuan kualitas atau melepas karantina | `CLOSED` |

Prinsip yang sama berulang pada keenamnya, dan pada `DOC-02` sebelumnya: **mekanisme berbatas yang sepakat dengan dirinya sendiri bukan bukti tentang dunia.** Itu pola kesalahan yang paling mahal pada sesi ini, dan sekarang dinyatakan di titik-titik tempat ia paling mungkin terulang.

#### Audit kualitas lintas-dokumen 2026-08-03

Pemeriksaan presence membuktikan setiap order punya pemilik. Pemeriksaan berikut menguji hal yang tidak dapat ditangkap probe kata kunci: rujukan menggantung, dokumen tanpa assignment, dan kewajiban yang dinyatakan sebuah kontrak tetapi tidak dipenuhi di mana pun.

| ID | Order | Temuan | Bukti | State | Penutupan |
|---|---:|---|---|---|---|
| `DOC-14` | 16 | `Platform_Config_Registry_LOCKED.md` mewajibkan definisi per-key dengan delapan field metadata dan menyatakan *"A key present in runtime code but absent from the registry is a sealing error"*, tetapi dokumen itu hanya memuat famili key — tidak ada satu pun definisi key. `W04` karena itu tidak punya daftar target | 128 key resolve di `config/market_data.php`; hanya 2 dokumen menyebut key konkret, keduanya kontrak domain yang menyebut ambangnya sendiri sambil lalu | `CLOSED` | Section **Resolved key register (LOCKED)** ditambahkan ke dokumen yang sama — bukan dokumen baru, agar tidak lahir pemilik kedua. Berisi 128 baris: key, tipe, default resolved, ENV input, owner contract. Makna dan allowed values tetap milik owner contract, tidak diulang |
| `DOC-15` | — | **Sector tidak memiliki owner contract.** Ia dikonfigurasi, dijalankan, dan dirujuk sebagai dependensi, tetapi tidak ada dokumen yang memiliki semantiknya | 7 config key `market_data.sectors.*`; 3 command terdaftar (`sector-indexes:ingest-api`, `sector-indexes:import-bars`, `sectors:import-memberships`); 10 dokumen merujuk sector sebagai dependensi termasuk `Indicator_Registry_Baseline_LOCKED.md` dan `Downstream_Consumer_Read_Model_Contract_LOCKED.md`; `find -iname "*sector*"` di seluruh `docs/market_data` mengembalikan nol | `CLOSED` | **Ditutup 2026-08-03 setelah keputusan kepemilikan diberikan.** Sector dinyatakan **masuk lingkup market-data**, dengan **`IDX-IC` otoritatif bersumber IDX**. Dua pertanyaan sisanya terjawab oleh keduanya: membership wajib temporal karena seluruh platform ini point-in-time, dan sector index terpisah menjadi **observasi** yang diakuisisi versus **produk turunan** yang dihitung. Owner baru `../../book/Sector_Classification_Contract_LOCKED.md`. Pada sinkronisasi 2026-08-07 temporal membership ditetapkan sebagai prerequisite Stage 6/`W05` sebelum sector-relative indicator `W14`; Stage 13/`W16` hanya mengonsumsi/expose sector-reference state. Contract tetap dirujuk oleh tujuh baris `market_data.sectors.*` pada config register Menulis kontraknya memerlukan keputusan yang tidak boleh saya karang: apakah `IDX-IC` otoritatif dan dari sumber mana; apakah membership berversi temporal seperti listing; apakah sector index adalah fakta market-data atau produk turunan; dan apakah sector termasuk lingkup market-data atau milik consumer. Empat keputusan itu milik Anda |

Satu dokumen kontrak dari 136 tidak memiliki assignment di conformance matrix, yaitu `Exchange_Market_Structure_Facts_LOCKED.md` yang dibuat pada sesi ini. Sudah didaftarkan pada stage 10. Pemeriksaan rujukan silang tidak menemukan satu pun tautan ke berkas yang tidak ada; yang ditemukan adalah gaya kutip nama-berkas-telanjang lintas direktori, yaitu kelemahan navigasi, bukan cacat kebenaran.

#### Penguatan per order — hasil review berurutan

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-16` | 1 | Horizon adalah pernyataan paling kabur di dokumen paling presisi — *"several days to several weeks"* — sementara jendela indikator sudah tetap di MA20/MA50/ROC20/ATR14/DV20 tanpa satu pun tautan kembali ke horizon | Horizon dikunci **5 hari perdagangan IDX**, rentang tahan **3–15 hari perdagangan**, wajib dalam hari perdagangan bukan kalender. Ditambah **horizon roles of a dependency window**: setiap jendela wajib menyatakan perannya sebagai decision, context, atau state window. MA50 karena itu sah sebagai context window, bukan anomali tak terjelaskan | `CLOSED` |
| `DOC-17` | 1 | `decision-grade` menanggung klaim kualitas utama di README dan Terminology, tetapi tidak pernah didefinisikan — dan `README:275` justru menyangkal bahwa ia diklaim, sehingga istilahnya sekaligus tujuan dan disclaimer | Section **`decision-grade` (LOCKED)** dengan empat kondisi terukur: as-known, single declared basis, correct-or-explicitly-blocked, dan timely enough. Kondisi 3 mengikat capability-boundary rules yang sudah ada. Claim state dinyatakan tegas: target property, bukan keadaan tercapai, sampai re-audit order 22 | `CLOSED` |
| `DOC-18` | 1 | Horizon tidak melahirkan satu pun kewajiban terukur; radius kontaminasi dan toleransi keterlambatan tidak dinyatakan di tempat horizon dimiliki | Section **obligations derived from the horizon**: radius kontaminasi sebesar jendela dependensi terpanjang, toleransi keterlambatan yang tidak boleh menghabiskan horizon, dan biaya warm-up. Angkanya tetap milik order 15 dan 19; kewajiban menyatakannya milik order 1 | `CLOSED` |
| `DOC-19` | 1 | README dan Terminology memuat **dua salinan penuh** aturan terkunci yang sama dan sudah mulai menyimpang: Terminology:41 menyebut empat hal yang bukan archived proof window, README:90 hanya tiga — `dataset end` hilang | Empat konsep waktu dan terminologi price product di README diringkas menjadi tabel orientasi dengan pointer owner eksplisit dan aturan presedensi: bila berbeda, Terminology yang berlaku. Penyimpangan `dataset end` ditutup | `CLOSED` |

Aturan interpretasi terkunci pada `Terminology_and_Scope.md` bertambah dari 15 menjadi 19, menutup salah-baca yang baru mungkin muncul: horizon dalam hari kalender, jendela tanpa peran, `decision-grade` sebagai keadaan tercapai, dan nilai non-null tanpa reason dianggap membuktikan kebenaran.

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-20` | 2 | Batas digambar per artefak — eligibility, indikator, likuiditas, publikasi, replay, snapshot — tetapi tidak ada aturan umum untuk **fakta dwiguna**, yaitu fakta yang sah dibutuhkan kedua sisi. Aturan likuiditas menangani satu instansnya; auto-rejection band, tick ladder, dan trading status tidak tertangani | Section **dual-use fact rule (LOCKED)** sebagai kelas: market-data memiliki faktanya, downstream memiliki preferensi yang diturunkan darinya, dan tidak ada sisi yang boleh memiliki separuh milik sisi lain. Disertai tabel lima fakta dwiguna beserta pembagiannya, termasuk lot size yang eksplisit **tidak** dimiliki market-data | `CLOSED` |
| `DOC-21` | 2 | Daftar *forbidden active semantics* mencantumkan `pick or candidate selection`, sementara `candidate` dipakai **104 dokumen** market-data dalam arti sah — kandidat publikasi, kandidat bar, kandidat detektor — termasuk 8 kali di dokumen batas itu sendiri. Guard yang menegakkan daftar itu secara harfiah akan menandai hampir seluruh package | Frasa dipertajam menjadi `instrument candidacy or pick selection for a trade`, ditambah section **overloaded vocabulary (LOCKED)** yang memisahkan makna sah dan terlarang untuk `candidate`, `target`, dan `policy`. Guard wajib memeriksa kontrak sekitarnya, bukan token | `CLOSED` |
| `DOC-22` | 2 | `eligible` disebut *compatibility field* di 13 dokumen tanpa satu pun jalur pensiun. Ia nama paling menyerupai policy di seluruh permukaan market-data — dibaca polos berarti *boleh ditransaksikan* — sehingga setiap kontrak harus mengulang koreksinya, dan pengulangan itu menjadi permanen | Section **retirement of the `eligible` alias (LOCKED)**: `data_usable` kanonik, alias pensiun setelah dibuktikan tidak ada consumer luar yang membacanya, pensiunnya lewat versioned read-model change, dan **tidak boleh ada surface baru** memakai nama itu. Alias boleh dipertahankan, tidak boleh disebarkan | `CLOSED` |
| `DOC-23` | 2 | `Exchange_Market_Structure_Facts_LOCKED.md` tidak terdaftar di cross-contract alignment order 2, padahal ia memiliki fakta dwiguna paling jelas di sistem | Didaftarkan bersama `Volume_and_Turnover_Normalization_LOCKED.md`, keduanya dengan keterangan pembagian yang dimilikinya | `CLOSED` |

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-24` | 3 | Kontrak resilience memiliki kegagalan **per-run** dengan baik, tetapi tidak ada kontrak mana pun yang memiliki kegagalan sebagai **kondisi berkelanjutan**. Dokumen ini memiliki keputusan menerima risiko source tanpa pernah menyatakan besar risikonya | Section **source continuity exposure (LOCKED)**: ketiadaan SLA/support/eskalasi, perubahan tanpa pemberitahuan, dan ketiadaan authoritative correction dinyatakan sebagai paparan yang diterima sadar | `CLOSED` |
| `DOC-25` | 3 | `manual_file` disebut sebagai jalur pemulihan di dua dokumen tanpa satu pun menyatakan batasnya. Universe aktif berjumlah **962 emiten**; memulihkan satu hari perdagangan secara manual berarti menyiapkan bar untuk seluruhnya dengan provenance dan validasi yang sama ketat | Dinyatakan sebagai **rescue satu tanggal, bukan jalur kelangsungan operasi**. Menyebutnya jalur pemulihan tanpa batas ini membuat rencana kelangsungan terlihat ada padahal tidak | `CLOSED` |
| `DOC-26` | 3 | Rentang antara intentional dataset start `2023-01-02` dan development frontier yang belum di-backfill hanya ada di sisi provider. Bila akses hilang lebih dulu, sejarah itu tidak dapat dipulihkan dengan biaya berapa pun — dan tidak ada dokumen yang menyatakannya | Section **risiko kehilangan permanen (LOCKED)**: kelengkapan backfill diangkat menjadi **mitigasi risiko**, bukan kelengkapan opsional. Ditegaskan bukan pekerjaan provider berbayar, sehingga tidak melanggar Done criteria order 3 | `CLOSED` |
| `DOC-27` | 3 | Trigger *availability* pada daftar future decision tidak memiliki ambang, sehingga tidak pernah terpicu — selalu ada alasan menunggu satu hari lagi | Kontrak operasi wajib menetapkan berapa hari perdagangan berturut-turut kegagalan yang mengubah insiden menjadi bukti penghambat kapabilitas | `CLOSED` |
| `DOC-28` | 3 | Dasar lisensi dinyatakan empat kali sebagai kewajiban *"terms harus dipatuhi"*, tanpa satu kali pun menyatakan **penggunaan seperti apa yang sedang dilakukan**. Keputusan bootstrap dibenarkan oleh penghematan biaya, dan penghematan itu hanya sah bila penggunaannya diizinkan | Section **licensing basis (LOCKED)** mewajibkan pencatatan: penggunaan aktual saat ini, terms bertanggal saat pengambilan, batas yang diketahui, dan peristiwa yang mengubah dasar itu. Kewajiban tanpa deklarasi tidak dapat diaudit | `CLOSED` |

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-29` | 4 | Kontrak mengatur **keamanan** revisi — payload provider yang berubah tidak boleh mengubah canonical atau sealed history — tetapi tidak mengatur **keterlihatan**-nya. Order 3 sudah menetapkan bootstrap source tidak memberi authoritative correction, sehingga perbandingan saat re-fetch adalah satu-satunya cara mengetahui nilai historis berubah. Dua observation berbeda untuk instrument/date yang sama dapat tersimpan dengan lineage benar tanpa satu pun temuan | Section **observation revision visibility (LOCKED)**: setiap observation baru untuk kombinasi yang sudah pernah ada wajib dibandingkan; sama menghasilkan konfirmasi, berbeda menghasilkan **explicit divergence finding** yang mengikat kedua identity, nilai, dan selisihnya. Divergence adalah temuan, bukan izin — ia tidak memilih pemenang. Menyimpan dua observation berbeda tanpa temuan kini masuk anti-ambiguity rules | `CLOSED` |
| `DOC-30` | 4 | Kontrak memiliki empat pemeriksa — schema/payload, stale/requested-date, row acceptance, dan dedup — tanpa satu pun pernyataan batas kemampuan, melanggar gate 11 yang baru berlaku | Section **capability boundary (LOCKED)**: acquisition membuktikan *kami menerima ini*, bukan *ini benar*. Empat wilayah buta dinyatakan — nilai yang salah tapi berbentuk sempurna, revisi pada tanggal yang tak pernah di-refetch, response kosong ketika ekspektasinya sendiri salah, dan drift semantik di balik schema fingerprint yang cocok. `SUCCESS` dilarang dikutip sebagai bukti kualitas data | `CLOSED` |
| `DOC-31` | 4 | Addendum menyebut provider dan nama parameternya secara eksplisit di kontrak domain, padahal capability matrix order 3 kini memilikinya. Pergantian adapter akan menyisakan pernyataan usang | Pernyataan diganti menjadi provider-neutral dengan pointer ke owner-nya | `CLOSED` |

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-32` | 5 | Section *provider context* menyebut nama provider, format simbol, kode status HTTP, dan jendela default secara eksplisit di kontrak domain — melanggar aturan dokumen ini sendiri pada `:152` (*"provider limitation harus diserap import strategy, bukan diwariskan ke domain contract"*) dan anti-ambiguity `:287` | Diganti menjadi **acquisition shape locked for active path**: yang mengikat adalah bentuk akuisisi — fan-out per instrument, pembatasan laju, jendela default yang lebih sempit dari kebutuhan domain — dengan fakta provider ditunjuk ke capability matrix order 3 | `CLOSED` |
| `DOC-33` | 5 | `market_data.provider.circuit_breaker_error_rate` ada di config dan ditugaskan ke kontrak ini oleh config register, tetapi **nol** disebut di kontrak. Begitu pula `api_retry_max` dan `api_throttle_qps`. Retry budget hanya dinyatakan *"dibatasi secara aman oleh implementasi/operator policy"* — tanpa batas, tanpa kewajiban mendeklarasikan | Keempat config key dinyatakan dimiliki kontrak ini dan dirujuk ke config register. Retry budget yang tidak terdeklarasi **tidak boleh dianggap ada**. Temuan ini ditemukan oleh register `DOC-14`, bukti pertama register itu bekerja | `CLOSED` |
| `DOC-34` | 5 | Aturan retry melindungi **run**, tidak ada yang melindungi **source**. Universe ratusan instrument menghasilkan ratusan permintaan per tanggal sebelum retry apa pun; kegagalan menyeluruh yang direspons retry penuh melipatgandakan beban tepat saat source menolak — cara tercepat kehilangan akses permanen, yaitu risiko kelangsungan yang baru dinyatakan order 3 | Section **source access self-protection (LOCKED)**: throttle dan concurrency ceiling pada seluruh jalur akuisisi, circuit breaker yang menghentikan run ketika rasio kegagalan melewati ambang, backoff meningkat, dan retry budget terdeklarasi. Menghentikan akuisisi demi melindungi akses dinyatakan sebagai **outcome sah yang wajib terlihat**, bukan kegagalan yang disamarkan | `CLOSED` |
| `DOC-35` | 5 | Model lima keadaan tidak menyatakan batas kemampuannya, padahal `complete/healthy` adalah pernyataan paling menenangkan yang dihasilkan kontrak ini | Section **capability boundary (LOCKED)**: `complete/healthy` mengukur penyelesaian akuisisi, bukan kebenaran nilai; ketiadaan response hanya berarti tidak terkirim; klasifikasi transient berasal dari sinyal transport yang dapat menyesatkan; dan retry yang berhasil hanya membuktikan percobaan kedua berhasil. Model ini akan melaporkan `complete/healthy` untuk sesi yang seharusnya diharapkan tetapi tidak pernah tercatat di kalender | `CLOSED` |

Done criteria order 5 terpenuhi tegas pada `:292` dan `:294`. Anti-ambiguity rules bertambah tiga butir.

#### Sector — penutupan `DOC-15`
| `DOC-84` | 13 | Keputusan kepemilikan sector menyingkap dua cacat data yang tidak tertangkap gate mana pun. Pertama, sistem klasifikasi seragam `IDX-IC` tetapi **sumbernya campur**: `idx_stock_screener` 888, `ksei` 6, `idx_profile` 3, lalu `investing` 2, `invesnesia` 1, dan `lembarsaham` 1 — empat baris terakhir bukan sumber IDX namun tersimpan di bawah label otoritatif yang sama. Kedua, dari **971 membership untuk 971 emiten, nol memiliki `effective_to`** — struktur temporalnya ada dan tidak pernah dipakai, sehingga reklasifikasi yang nyata terjadi di IDX tidak pernah tercatat dan setiap tanggal historis meresolusi ke sektor hari ini | Kontrak baru menetapkan **authority class** yang sama seperti trading status: `EXCHANGE_AUTHORITATIVE` boleh menetapkan, `DERIVED_REFERENCE` hanya boleh menguatkan, dan **baris bersumber `DERIVED_REFERENCE` bukan membership otoritatif betapapun sistem klasifikasinya benar**. Reklasifikasi wajib **menutup interval lama dan membuka yang baru** — tidak pernah menyunting baris lama, karena itu menulis ulang setiap nilai sector-relative historis. Interval terbuka dinyatakan **tidak membuktikan tidak ada perubahan**, hanya bahwa tidak ada yang tercatat | `CLOSED` |

#### Order 22 — audit claims, tracker, dan proof pack

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-82` | 22 | Re-audit akan menghadapi korpus bukti yang tampak berlimpah — 68.411 run coverage, 33.414 publikasi tersegel, 20.635 replay `PASS`, suite hijau — sementara temuan `DOC-61`, `DOC-71`, `DOC-75`, `DOC-79`, `DOC-80`, dan `DOC-81` tersebar di enam tempat berbeda. Tidak ada satu pun yang menyatukan **apa yang setiap kelas bukti dapat dan tidak dapat dukung**, sehingga sangat mungkin dikutip keliru | Section **evidence admissibility ledger (LOCKED)** menyatukan kelimanya dalam satu tabel dengan volume, klaim yang didukung, klaim yang **tidak** didukung, dan sebabnya. Ditutup empat aturan: klaim menyebut kelas bukti dan batasnya bukan hanya volumenya; **volume bukan kekuatan**; menutup temuan dengan bukti dari kelas yang tidak dapat mendukungnya **tidak menutup temuan itu** melainkan memindahkan cacatnya ke catatan audit; dan ledger diperbarui saat sebuah kelas berubah status | `CLOSED` |
| `DOC-83` | 22 | `README.md:268` mencabut klaim `FULL GLOBAL MARKET-DATA PRODUCTION READY` dan `MARKET_DATA_PRODUCTION_READY_LOCKED`, tetapi **14 dokumen di `audit/` masih memuat klaim production-ready tanpa satu pun penanda superseded**. `AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md:213` berbunyi `FULL_MARKET_DATA_PRODUCTION_READY=YES, with no remaining blocker` dan hanya menampilkan `[LAST_UPDATED] 2026-05-18` | Bagian **28. Pencabutan klaim dilakukan di tempat (LOCKED)** ditambahkan ke `AUDIT_UPDATE_GOVERNANCE.md`: penanda superseded diletakkan **pada klaimnya sendiri, di dokumen tempat ia ditulis**, dekat dengan klaim bukan hanya di kepala dokumen karena dokumen panjang dibaca sebagian. Dokumennya tidak dihapus — riwayat audit tetap bernilai. Sesi yang mencabut bertanggung jawab menandai **seluruh** kemunculan, dan pencarian teks atas nama klaim adalah bagian dari pekerjaan pencabutan | `CLOSED` |

**Done criteria order 22 tidak terpenuhi, dan itu memang keadaan yang benar.** Tiga puluh temuan terbuka — `P0-01` sampai `P0-04` dan `P1-01` sampai `P1-26` — sehingga syarat *"tidak ada P0/P1 critical"* jelas belum terpenuhi. Syarat *"executed proof tersedia"* gagal dengan cara yang lebih halus dan lebih penting: **proof-nya tersedia dalam jumlah besar, dan hampir tidak ada yang admissible untuk klaim yang tampak ditopangnya.**

Ini order yang menilai apakah seluruh sisanya boleh diklaim, dan penilaiannya jelas: belum. Yang berubah setelah sesi ini adalah **ketidakbolehan itu kini terukur dan beralasan**, bukan sekadar dinyatakan.

#### Order 21 — test contracts dan fixtures

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-80` | 21 | **34 dari 129 berkas test adalah static guard, dan seluruh 34 membaca teks sumber** lewat `file_get_contents` atau `assertStringContainsString`. Dua puluh enam persen suite membuktikan bahwa kode memuat string tertentu — bukan makna lama, bukan makna baru. Tidak ada satu pun pernyataan yang membatasi apa yang boleh disimpulkan darinya, sehingga keduanya masuk ke satu angka lulus yang sama | Section **what a source-text assertion proves (LOCKED)**: ia membuktikan **teksnya ada**, dan itu saja. Ia bertahan terhadap penulisan ulang yang mempertahankan string dan mengubah perilaku, dan gagal terhadap penulisan ulang yang mengubah string dan mempertahankan perilaku. Boleh menjaga reintroduksi konstruk terlarang bernama — tujuan sah yang sempit — tetapi **tidak boleh berdiri sebagai bukti sebuah aturan kontrak berlaku**. Setiap aturan wajib punya minimal satu assertion yang **mengeksekusi jalur yang diaturnya**; yang hanya punya static guard dinyatakan **belum terbukti**. Suite hijau dilaporkan dengan proporsi behavioral dan teks yang terpisah, karena angka gabungan melebihkan cakupan tepat sebesar bagian yang tidak pernah mengeksekusi apa pun | `CLOSED` |
| `DOC-81` | 15, 21 | Enam dokumen menspesifikasikan golden fixture, oracle, dan test vector — **nol berkasnya ada**. Pencarian ke seluruh proyek di luar `docs/` hanya menemukan satu command generator replay fixture, dua helper seeding, dan satu manifest runtime-matrix di storage. Akibatnya utang dari order 15, yaitu determinisme rantai panjang, tidak punya test yang menjalankannya: **nol test menyentuh rantai Wilder ATR** | Section **fixtures must exist as artifacts (LOCKED)** pada spesifikasi test, plus **artifact existence status (LOCKED)** pada katalognya. Spesifikasi tanpa artefak **tidak membuktikan apa pun** dan tidak boleh dikutip sebagai cakupan; pembacaan yang benar atas fixture set yang terspesifikasi penuh namun belum dibangun adalah **kapabilitas absen, bukan cakupan tertunda**. Kriteria yang bergantung padanya **tidak terpenuhi**, bukan terpenuhi sebagian karena spesifikasinya menyeluruh | `CLOSED` |

**Done criteria order 21 terpenuhi sebagian, dan bagian yang gagal adalah bagian yang paling kuat.** Tujuh puluh empat persen berkas test bersifat behavioral, dan sisa test yang mengunci behavior lama sudah dipensiunkan lebih awal pada sesi ini. Tetapi bukti terkuat yang disyaratkan strategi — golden fixture, expected-output oracle, dan rantai panjang deterministik — **belum ada satu pun sebagai artefak**.

Satu hal dari order 20 memperberatnya: suite berjalan di atas mirror SQLite yang memuat tiga belas tabel yang tidak dimiliki MariaDB. Suite hijau karena itu membuktikan makna terhadap **schema yang dimaksud**, bukan schema yang terdeploy.

#### Order 20 — schema, dictionary, migration

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-79` | 20 | Kontrak menyatakan target deployment adalah *"base plus all later migrations"*, tetapi tidak mewajibkan siapa pun memeriksa bahwa sebuah database mencapainya. Kegagalan yang dicegah bukan korupsi, melainkan database yang sekadar **tertinggal** — dan itu tidak menghasilkan error di mana pun. Kode yang dikompilasi terhadap schema yang dimaksud lulus test-nya, karena test mirror ditulis tangan mengikuti schema yang dimaksud. Jalur runtime yang menyentuh tabel belum-teraplikasi hanya gagal ketika dijalankan, dan jalur yang belum tersambung tidak pernah gagal sama sekali | Section **drift detection is required (LOCKED)**: perbandingan migration tersedia versus terterapkan, perbandingan tabel dan kolom deployed versus mirror **dua arah** karena keduanya sudah terjadi, dan hasil eksplisit dengan environment tak terverifikasi yang dinyatakan. Ditutup tiga aturan: klaim yang menyebut sebuah database wajib menyatakan posisi migration-nya karena **test hijau bukan bukti keadaan schema terdeploy**; drift ditutup dengan menerapkan migration maju, tidak pernah dengan menyunting database agar cocok maupun menyunting mirror agar cocok dengan deployment basi; dan dependensi runtime atas tabel yang tidak ada di database itu **tidak dapat dijalankan di environment tersebut**, sehingga mencatatnya sebagai terimplementasi adalah klaim palsu sampai drift-nya ditutup | `CLOSED` |

**Done criteria order 20 gagal pada tepat satu sumbu, dan itu kabar baik.** MariaDB memuat 40 tabel, test mirror 41, dan migration mendefinisikan 26 lewat `Schema::create` di atas base clean-install. Otoritas schema-nya sendiri **sudah dinyatakan koheren**: `Database_Schema_MariaDB.sql` adalah base yang dieksekusi core migration melalui `DB::unprepared`, lalu berkembang hanya lewat migration maju.

Yang menyimpang adalah **dua migration terakhir tidak pernah dijalankan**. Tabel `migrations` berhenti di `2026_07_30_000005`, sementara `2026_08_02_000001_add_market_data_strategy_v2_foundation` dan `2026_08_03_000001_harden_market_data_orders_1_to_4` ada sebagai berkas dan tidak tercatat.

Migration pertama itu membuat tiga belas tabel: `md_config_snapshots`, `md_source_observations`, `md_issuers`, `md_instruments`, `md_listings`, `md_listing_symbols`, `md_provider_symbol_mappings`, `md_market_calendar_revisions`, `md_trading_status_revisions`, `md_corporate_action_revisions`, `md_adjustment_factor_sets`, `md_adjustment_factors`, dan `md_publication_lineage_bindings`.

**Satu batch yang belum diterapkan itu adalah akar bersama dari lima temuan yang sebelumnya tercatat terpisah**: `P1-16` identitas temporal, `P1-20` factor revision model, `P1-21` identitas price-product, `P1-25` config snapshot, dan sebagian `P1-17` revisi kalender. Pekerjaannya bukan membangun lima hal, melainkan menerapkan dua migration lalu menyambungkan writer-nya.

#### Order 19 — operasi, SLO, dan insiden

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-77` | 1, 3, 19 | Dua kewajiban yang dititipkan order lain belum dipenuhi: **target keterlambatan** yang tidak menghabiskan horizon dari `DOC-18`, dan **ambang hari kegagalan berturut-turut** untuk trigger availability dari `DOC-27`. Keduanya menggantung, sehingga penguatan order 1 dan 3 masih berupa kalimat | Section **horizon-derived targets (LOCKED)** menerbitkan keduanya, **diturunkan dari horizon dan bukan dipilih**, sehingga perubahan horizon memaksa perubahan di sini alih-alih diam-diam membatalkannya. Freshness: terbaca pada sesi yang sama `ON_TARGET`, pada sesi berikutnya `ACCEPTABLE` karena satu sesi menghabiskan seperlima horizon, dan **tidak terbaca pada sesi kedua adalah `BREACH`** karena dua sesi menghabiskan dua perlima dan sisanya bukan lagi horizon yang dideklarasikan profil konsumen. Availability: kegagalan akuisisi **5 sesi berturut-turut — satu horizon penuh** — membuka evaluasi sumber, karena sumber yang tidak dapat mengirim selama satu horizon utuh telah gagal pada hal yang membuat horizon itu ada | `CLOSED` |
| `DOC-78` | 19 | 34 dokumen stage 19 tanpa capability boundary. Sebagian besar runbook, dokumen command, format, dan inventaris yang tidak menghasilkan verdict — menuliskan boundary generik pada masing-masing akan memenuhi pemeriksaan mekanis tanpa mengajarkan apa pun, yang justru dilarang gate itu sendiri | Cakupan dinyatakan **sebagai kelas** pada `OPERATIONAL_RUNBOOK.md`, bukan 29 deklarasi terpisah, dengan daftar yang di luar dan di dalam cakupan. Lima dokumen penghasil verdict ditutup masing-masing: SLO, incident matrix, run admission, release gates, dan scheduling/locking. Ditambah aturan lanjutan: dokumen yang berpindah dari menjelaskan prosedur menjadi menghasilkan verdict masuk cakupan dan wajib menyatakan batasnya saat itu | `CLOSED` |

**Done criteria order 19 belum terpenuhi secara sah, bukan gagal.** `daily_enabled = false` dan `operational_start_date = null` — activation memang belum pernah ditetapkan, dan kontrak secara eksplisit mengizinkan keadaan ini sebelum activation. Riwayat run memperlihatkan batch backfill besar, misalnya 5.619 run pada 2026-07-21 dan 3.970 pada 2026-07-30, bukan operasi harian berturut-turut. Ini P1-11 `PRE_ACTIVATION_OPEN` yang sudah tercatat, kini dengan dua ambang yang membuatnya dapat diukur ketika activation tiba.

#### Order 18 — replay dan backtest

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-74` | 18 | Kontrak menyatakan *"replay mode is mandatory and explicit"*, tetapi `md_replay_daily_metrics` **tidak memiliki kolom mode sama sekali**. Kewajiban itu diperlakukan sebagai properti permintaan, bukan properti hasil, sehingga hasil publication dan as-known tidak akan terbedakan sekalipun keduanya ada | Mode wajib menjadi field first-class pada **hasil**, bukan hanya pada permintaan. Hasil tanpa mode **bukan publication-replay secara default; ia tidak terklasifikasi**, dan yang tidak terklasifikasi tidak boleh dikutip sebagai mode mana pun | `CLOSED` |
| `DOC-75` | 18 | As-known replay tidak terimplementasi — **nol file test menyentuh `knowledge_cutoff`, `as_known`, atau `asKnown`** — sementara 20.635 hasil publication replay berstatus `PASS` tersedia untuk dikutip. Tidak ada satu pun pernyataan bahwa hasil itu tidak membawa informasi apa pun tentang kebocoran future state | Section **while only one mode exists (LOCKED)**: publication replay membandingkan artefak terhadap input yang dibekukan bersamanya, sehingga master masa depan, revisi aksi yang lebih baru, atau konfigurasi yang lebih baru **absen dari kedua sisi perbandingan dan karenanya tidak dapat menghasilkan mismatch**. Berapa pun banyaknya `PASS` tidak menggantikan satu fixture as-known. Kedelapan fixture anti-survivorship adalah fixture as-known, sehingga ketiadaannya **bukan celah cakupan melainkan celah kapabilitas** — klaim yang bersandar padanya tidak tersedia, bukan belum diuji | `CLOSED` |
| `DOC-76` | 18 | Ketiga kontrak backtest tidak menyatakan batas kemampuannya | Tiga capability boundary. Yang paling tajam pada point-in-time input: **kontrak ini mengatur input, bukan studi** — backtest yang memilih instrumen berdasarkan pengetahuan hari ini menerima baris point-in-time yang bersih dan tetap bocor | `CLOSED` |

**Done criteria order 18 tidak dapat dinilai saat ini, dan alasannya berlapis.** Replay publikasi terimplementasi dan teruji dengan baik — 11 file test, 20.635 baris metrik, seluruhnya `PASS`. Tetapi seluruh 20.635 hasil itu berada di atas publikasi `CONFIG_UNBOUND`, sehingga di bawah `DOC-71` semestinya `BLOCKED`, bukan `PASS`: **nol `BLOCKED` dan nol `FAIL` tercatat**. Dan mode yang memang menguji kebocoran future state, yaitu as-known, belum ada sama sekali.

#### Order 17 — consumer read model dan readiness

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-72` | 17 | Kelima owner document sisi baca tidak menyatakan batas kemampuannya, padahal kelimanya menghasilkan verdict: readiness state, keputusan readability, isi read model, dan penegakan anti-bypass | Lima capability boundary yang berbeda satu sama lain. Dua yang paling menentukan: **`READABLE` menyatakan sebuah publikasi tersegel meresolusi untuk effective date yang disebutkan — bukan bahwa datanya benar, dan bukan bahwa tanggalnya adalah tanggal yang diminta**, karena fallback tanggal efektif tetap menghasilkan respons yang conformant; dan **penegakan anti-bypass hidup di kode aplikasi**, sehingga konsumen dengan akses database langsung, alat pelaporan yang diarahkan ke schema, atau query dadakan mencapai tabel yang sama tanpa melewatinya — batas yang sama persis dengan guard immutability pada `DOC-47` | `CLOSED` |
| `DOC-73` | 17 | Kontrak menyatakan freshness sebagai fakta platform tanpa menyatakan bahwa ia mewarisi kebenaran kalender | Dinyatakan pada readiness guarantee: **fresh mengukur pengetahuan platform terhadap sesi yang diharapkan** — bila ekspektasi kalendernya salah, platform melaporkan fresh untuk sesi yang tidak pernah terjadi, atau stale untuk sesi yang tidak pernah ada. Ditambah: readiness tidak tahu berapa besar biaya keterlambatan bagi horizon konsumennya | `CLOSED` |

**Sisi arsitektur Done criteria order 17 bersih.** Jalur baca konsumen tidak memuat satu pun pola bypass — nol `MAX(trade_date)`, nol `latest()`, nol pengurutan-terbaru — dan `MarketDataReadProductRepository` mengikat publication context. Tidak ada service sisi-baca yang membaca `eod_bars` langsung; keempat pembaca langsung yang ada adalah jalur tulis, pipeline, evidence export, dan detektor.

Satu hal harus dinyatakan terus terang: **tidak ada route konsumen sama sekali**. Read product belum memiliki permukaan HTTP, sehingga Done criteria ini secara struktur terpenuhi tetapi **belum pernah diuji satu konsumen pun**. Sama seperti determinisme rantai panjang pada order 15, ia benar secara rancangan dan belum terbukti oleh pemakaian.

#### Order 16 — config snapshot

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-71` | 16 | `:23` menyatakan binding config yang null **mencegah sealing dan consumer readability**. Aturan itu tidak pernah ditegakkan, dan kontrak tidak mengatakan apa pun tentang artefak yang sudah tersegel tanpa binding — sehingga seluruh korpus yang ada berada dalam keadaan tak terdefinisi, dan artefak non-conformant tidak dapat dibedakan dari yang conformant. Buktinya: **71.917 run, nol memiliki `config_hash` maupun `config_snapshot_ref`**, sementara **33.414 publikasi berstatus `SEALED` dan 844 di antaranya current serta terbaca konsumen** | Section **status of artifacts sealed before this gate was enforced (LOCKED)** memperkenalkan keadaan **`CONFIG_UNBOUND`**: bukan tidak sah sebagai catatan apa yang pernah diterbitkan, tetapi **tidak admissible sebagai bukti reproducibility**, karena konfigurasi yang menghasilkannya tidak dapat dipulihkan. Publication replay atasnya `BLOCKED`, bukan `PASS`. Tidak dihapus dan **tidak di-reseal** — reseal akan melekatkan konfigurasi yang bukan penghasil artefak itu, kebohongan yang lebih besar daripada binding yang hilang. Jumlahnya dilaporkan sampai nol. Ditutup dengan aturan umum: **gate yang tidak pernah ditegakkan tidak membuat korpusnya conformant lewat kesunyian** | `CLOSED` |

**Done criteria order 16 tidak terpenuhi, dan ini kegagalan paling tegas dari seluruh order yang sudah diperiksa.** Kriteria berbunyi run dan publication memiliki config hash atau reference yang non-null. Kenyataannya nol dari 71.917 run memilikinya, dan `eod_publications` tidak memiliki kolom config sama sekali. Tidak ada tabel snapshot config; `md_session_snapshots` adalah session snapshot yang tidak berhubungan. Menurut aturan kontraknya sendiri, seluruh 33.414 publikasi itu semestinya terhalang dari sealing.

#### Order 15 — indikator

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-68` | 1, 15 | `Terminology_and_Scope.md:50` menugaskan kontrak indikator menerbitkan **radius kontaminasi sebagai angka**. Kewajiban itu belum dipenuhi, sehingga penguatan order 1 masih berupa kalimat | Section **contamination radius (LOCKED)** menerbitkan **dua** radius, karena menyatukannya jadi satu angka akan meremehkan kasus terburuk. **Radius jendela tetap: 50 sesi perdagangan**, dari `ma50` yang didefinisikan atas `D[-49]..D` — terhadap horizon lima hari, itu kira-kira **sepuluh siklus keputusan berturut-turut**, dan cacatnya tidak diencerkan oleh perata-rataan melainkan dibawa olehnya. **Radius rekursif: tak terbatas ke depan**, karena `ATR14` memakai rekurensi Wilder dari satu seed stabil. Konsekuensinya mengikat: karantina yang hanya menutup tanggal peristiwa **kurang lima puluh kali lipat** untuk field jendela tetap, dan kurang tak terhingga untuk field rekursif | `CLOSED` |
| `DOC-69` | 15 | Formula spec, computation spec, dan kontrak nullability tidak menyatakan batas kemampuannya | Tiga capability boundary yang berbeda satu sama lain. Yang paling tajam ada di formula spec: **jendela dihitung dalam sesi, bukan waktu berlalu** — dua puluh sesi yang melintasi rangkaian libur panjang, suspensi, atau penutupan pasar yang tak tercatat menggambarkan rentang kalender yang jauh lebih panjang, sementara keduanya diberi label sama. Sebuah angka momentum karena itu mengukur periode yang panjang sebenarnya berubah-ubah | `CLOSED` |
| `DOC-70` | 15 | `EOD_Indicators_Contract.md` tidak menyatakan cakupan gate 11 | Dinyatakan tidak berlaku dengan pointer ke tiga pemilik batas sebenarnya, plus satu konsekuensi langsung: **lineage lengkap membuktikan sebuah baris dapat ditelusuri, bukan bahwa nilainya benar** — baris dengan publication, run, factor-set, formula set, config, dan universe identity terisi seluruhnya tetap dapat memuat angka salah, dan seluruh pengikatan itu akan menelusurinya dengan setia sampai ke sumbernya | `CLOSED` |

**Warm-up nullability terbukti sempurna terhadap data produksi.** Pada baris paling awal untuk setiap emiten, `ma50`, `ma20`, dan `atr14_pct` seluruhnya `NULL` — **975 dari 975** untuk ketiganya. Tidak ada nilai yang diterbitkan sebelum warm-up-nya terpenuhi.

#### Order 14 — actual versus proxy

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-65` | 14 | Proxy diwajibkan membawa formula version, basis `RAW`, window, dan label proxy — tetapi keempatnya adalah properti **artefak tersimpan**, bukan kalimat di kontrak. Metrik yang membawanya hanya di dokumentasi tidak membawanya ke tempat mana pun yang dapat dibaca konsumen. Pola yang sama dengan `DOC-58` pada order 11 | Section **proxy labelling must be persisted (LOCKED)**: keempatnya menjadi field queryable dalam publication context yang sama. Konsumen wajib dapat membedakan actual dari proxy **tanpa membuka dokumen dan tanpa mengurai nama kolom** — konvensi penamaan membantu pembaca, ia bukan kontrak yang dapat dibaca mesin. Metrik tanpa penanda **tidak diasumsikan proxy; ia tak berlabel, dan metrik likuiditas tak berlabel tidak boleh dipublikasikan** | `CLOSED` |
| `DOC-66` | 14 | `dv20_idr` tidak menyebut basis maupun sifat proxy-nya. Dibaca polos, `dv` berarti daily value dan `_idr` menegaskan jumlah rupiah — persis pembacaan yang dilarang kontrak ini. Mendokumentasikannya sebagai alias memperbaiki pembacaan bagi yang membaca dokumen; kolomnya tetap menyatakan hal yang salah kepada semua orang lain. Dan seperti `eligible` serta `ticker_id`, ia tidak punya jalur pensiun | Section **retirement of the `dv20_idr` alias (LOCKED)** dengan larangan surface baru bernama `dv*` atau yang menyiratkan traded value tanpa menyebut basisnya, plus satu aturan yang lahir dari keadaan sekarang: **alias tidak boleh menggantikan field yang dialiasnya** — di mana field proxy bernama eksplisit belum ada, aliasnya adalah celah yang harus ditutup, bukan pengganti yang memenuhi kontrak. Pola aliasnya kini dinyatakan terbuka: alias tanpa kondisi akhir menjadi permanen, dan pembacaan menyesatkannya menjadi makna default platform | `CLOSED` |
| `DOC-67` | 14 | Kontrak tidak menyatakan batas kemampuan metriknya | Section **capability boundary (LOCKED)** dengan empat wilayah buta, yang paling tajam: proxy menilai seluruh saham pada harga penutupan, sehingga sesi yang transaksinya jauh dari close menghasilkan angka yang **dimensinya benar dan materialnya berbeda** dari traded value yang diwakilinya — dan selisih itu terbesar justru pada instrumen volatil dan tipis, tempat ia paling menentukan | `CLOSED` |

**Sisi dimensi dari Done criteria order 14 terbukti benar.** Pengujian langsung atas satu kasus konkret — `ticker_id 968` pada `2026-07-28` — menghasilkan `dv20_idr` tersimpan `467.099.775,00` berbanding `AVG(close × volume)` atas 20 bar terakhir `467.099.775,00`, **rasio tepat `1,0000`**. Tidak ada pengali lot; jejak bug 100× yang dihapus pada 2026-07-30 memang bersih dari data.

Sisi *"tidak misleading"* belum terpenuhi: `eod_indicators` hanya memiliki **satu** kolom likuiditas, `dv20_idr`, sementara kontrak mensyaratkan actual, proxy bernama eksplisit, kedua rata-rata 20 harinya, dan trade count sebagai field terpisah.

#### Order 13 — data usability

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-63` | 13 | Larangan *"no dimension may be reconstructed from a single overloaded `reason_code`"* dibatasi klausa **"when first-class facts are available"** — dan klausa itu menonaktifkan larangannya persis pada keadaan yang hendak dicegahnya. Dibaca apa adanya, snapshot yang tidak menyimpan satu pun dimensi justru conformant, karena tidak ada fakta first-class untuk diutamakan. Itu membuat kewajiban *"each row must persist separately"* di halaman yang sama tidak dapat ditegakkan | Klausa dihapus; larangan menjadi tanpa syarat, dengan konsekuensi dinyatakan: ketiadaan fakta first-class adalah **cacat terhadap kontrak ini, bukan izin** membebani `reason_code`. Satu nilai reason dapat menyatakan satu verdict terpilih, tidak dapat menyatakan empat dimensi independen — memaksakannya menghasilkan nilai yang hanya produsernya bisa membaca. Ditambah: baris `eligible = true` juga membawa fakta dimensinya, karena penjelasan bukan hak istimewa baris yang diblokir | `CLOSED` |
| `DOC-64` | 13 | `Eligibility_Partial_Data_Behavior_LOCKED.md` tidak menyatakan batas kemampuannya | Section **capability boundary (LOCKED)** dengan satu wilayah buta yang menjelaskan mengapa daftar dimensi wajib bersifat load-bearing: **dimensi yang tidak pernah dideklarasikan tidak akan pernah terdeteksi hilang**, karena tidak ada yang mengharapkannya | `CLOSED` |

**Done criteria order 13 tidak terpenuhi di implementasi, dan kontraknya tidak bersalah.** Kontrak mewajibkan setiap baris menyimpan **19 fakta terpisah** dalam lima kelompok — expectation/delivery, quality, liquidity, status/event-risk, dan decision/explanation. `eod_eligibility` nyata memiliki tujuh kolom: `trade_date`, `ticker_id`, `eligible`, `reason_code`, `run_id`, `publication_id`, `created_at`. Hanya **2 dari 19** fakta wajib ada, dan salah satunya — `reason_code` sebagai `varchar(64)` tunggal — secara struktur tidak sanggup memenuhi syaratnya sendiri, yaitu *"complete ordered reason-code set"*. Dari 749.685 baris, 23.143 membawa reason dan seluruhnya adalah baris tidak eligible; **726.542 baris eligible tidak membawa penjelasan apa pun**.

#### Order 12 — coverage

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-61` | 12 | Evidence coverage mencatat `coverage_contract_version` — aturan coverage mana yang berlaku — tetapi **tidak mencatat resolver universe** yang menghasilkan denominatornya, padahal denominator adalah bagian coverage yang paling bergantung pada sesuatu di luar kontrak ini. Buktinya konkret: seluruh 68.411 run coverage berkisar `2026-05-28` sampai `2026-08-01`, sementara resolver temporal baru ada `2026-08-03 13:29` — **nol run sesudahnya**. Seratus persen evidence tersimpan dihasilkan resolver pra-temporal yang persis dituduh P0-02, dan tidak ada yang membedakannya dari evidence pasca-perbaikan | Section **evidence validity boundary (LOCKED)**: setiap record coverage wajib mengikat versi resolver identitas/universe, revisi kalender, dan revisi status — bukan hanya versi kontraknya sendiri. Evidence dari komponen yang sudah digantikan **tidak admissible** sebagai bukti kebenaran temporal; ia tetap sah sebagai bukti apa yang diputuskan platform saat itu, yang merupakan klaim berbeda. Digeneralisasi: **evidence mengikat versi setiap komponen yang menopang kebenarannya, bukan hanya versi kontrak yang menghasilkannya** | `CLOSED` |
| `DOC-62` | 12 | `bar_not_expected` adalah satu-satunya mekanisme yang boleh menyusutkan denominator, sehingga satu-satunya jalur yang dapat menyembunyikan kegagalan provider. Ia terpakai pada **84 dari 68.411 run — 0,12%** — dan tidak ada aturan yang menyatakan kelangkaan itu bukan jaminan keamanan | Section **denominator exclusion path (LOCKED)**: jalur ini menuntut bukti positif dari test yang memang menjalankannya, terlepas dari seberapa jarang produksi memicunya. Pemakaian rendah tidak pernah dikutip sebagai jaminan — jalur yang terpakai sepersekian persen praktis belum teruji trafik produksi, dan pemakaian material pertamanya bisa jadi juga eksekusi nyata pertamanya. Kenaikan laju eksklusi menjadi temuan tersendiri bagi quality gates | `CLOSED` |

**Done criteria order 12 terbukti empiris dan kuat.** Pada **2.317 run dengan `coverage_ratio = 0`** — kegagalan pengiriman total — rata-rata `coverage_universe_count` adalah **900**, tertinggi dari seluruh kelompok rasio, dengan minimum 850. Denominator tidak menyusut mengikuti kegagalan. Ini bukan sekadar ketiadaan pelanggaran: mode kegagalannya terpicu 2.317 kali dan penjaganya bertahan setiap kali.

#### Order 11 — coherent price products

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-58` | 11 | Aturan one-basis-per-run mewajibkan setiap run **mengikat** identitas price-product, versi produk, factor-set hash, versi formula, dan config snapshot — tetapi tidak pernah menyebut **di mana ikatan itu tersimpan**. Akibatnya tidak pernah dibangun: pencarian kolom basis/factor/product pada `eod_runs`, `eod_publications`, `eod_indicators`, dan `eod_indicators_history` mengembalikan **nol**. Satu-satunya kolom berakhiran `basis` adalah `coverage_universe_basis`, yang tentang universe | Section **persistence of the binding (LOCKED)**: identitas price-product, factor-set reference, dan versi formula wajib menjadi field first-class yang queryable — bukan baris log, bukan catatan, bukan disimpulkan dari nama kolom. Setiap baris analitik meresolusi basisnya tanpa join ke luar publication context-nya, dan baris dengan identitas `NULL` bukan teridentifikasi lemah melainkan **tidak teridentifikasi**, sehingga tidak boleh readable | `CLOSED` |
| `DOC-59` | 11 | Aturan itu hanya dapat ditegaskan saat komputasi berjalan dan tidak pernah dapat diperiksa setelahnya. Aturan yang tidak dapat diperiksa setelah run berakhir tidak memberi jaminan apa pun, karena tidak ada yang membedakan run yang patuh dari yang tidak | Section **verifiability (LOCKED)** menetapkan tiga pemeriksaan atas artefak tersimpan: himpunan price-product identity per publikasi tepat beranggota satu; factor-set reference meresolusi ke revisi yang rentang efektifnya menutupi dependency window baris itu; dan run tanpa baris analitik tetap mencatat basis terpilihnya, sehingga hasil kosong dapat dibedakan dari basis yang tidak dipilih | `CLOSED` |
| `DOC-60` | 11 | Kontrak tidak menyatakan cakupan gate 11, dan tidak menyatakan bahwa satu basis per run menjamin ketiadaan pencampuran tetapi **bukan** kelengkapan | Cakupan gate 11 dinyatakan tidak berlaku dengan pointer ke `Price_Adjustment_Contract_LOCKED.md`, ditambah satu konsekuensi yang berlaku langsung: run yang mengikat `STRUCTURAL_ADJUSTED` dengan factor set yang kehilangan satu peristiwa **memenuhi aturan one-basis-per-run sepenuhnya**, dan hasilnya tetap salah secara diam-diam. Acceptance criterion juga dikualifikasi: selama identitas price-product tidak ada sebagai field, kriteria itu **tidak terukur**, bukan gagal | `CLOSED` |

### Evidence admissibility ledger (LOCKED) — untuk re-audit order 22

Re-audit order 22 akan menghadapi korpus bukti yang tampak berlimpah: puluhan ribu run coverage, puluhan ribu publikasi tersegel, dua puluh ribu hasil replay `PASS`, dan suite yang hijau. **Hampir tidak ada di antaranya yang menopang klaim yang tampak ditopangnya.** Ledger ini menyatakan apa yang setiap kelas bukti dapat dan tidak dapat dukung, supaya re-audit tidak mengutipnya secara keliru.

| Kelas bukti | Volume | Dapat mendukung | **Tidak** dapat mendukung | Sebab |
|---|---:|---|---|---|
| Run coverage | 68.411 | Apa yang diputuskan platform saat itu; denominator tidak menyusut saat pengiriman gagal | Kebenaran temporal universe | Seluruhnya dihasilkan resolver pra-temporal; nol run pasca-perbaikan — `DOC-61`, `P1-22` |
| Publikasi tersegel | 33.414 | Fixity — isi yang tersegel tidak berubah | Reproducibility | Seluruhnya `CONFIG_UNBOUND`; konfigurasi penghasilnya tidak dapat dipulihkan — `DOC-71`, `P1-25` |
| Hasil replay `PASS` | 20.635 | Determinisme pipeline | **Ketiadaan kebocoran future state** | Publication replay membandingkan artefak terhadap input yang dibekukan bersamanya, sehingga state masa depan absen dari kedua sisi; mode as-known belum ada — `DOC-74`, `DOC-75` |
| Suite test | 1.158 test | Perilaku yang benar-benar dieksekusi | Kesesuaian schema terdeploy; aturan yang hanya dijaga teks | 34 dari 129 berkas hanya membaca teks sumber; suite berjalan di atas mirror yang memuat 13 tabel yang tidak dimiliki MariaDB — `DOC-79`, `DOC-80` |
| Golden fixture dan oracle | 0 | — | Determinisme rantai panjang | Tidak ada satu pun artefak; spesifikasinya lengkap, kapabilitasnya absen — `DOC-81` |

Aturan yang mengikat re-audit:

- Setiap klaim conformance menyebut **kelas bukti dan batasnya**, bukan hanya volumenya. Mengutip dua puluh ribu `PASS` tanpa menyebut modenya adalah pelanggaran governance, bukan kelalaian penulisan.
- Volume bukan kekuatan. Menumpuk bukti dari satu kelas menaikkan keyakinan hanya pada properti yang memang diuji kelas itu.
- Menutup sebuah temuan dengan bukti dari kelas yang tidak dapat mendukungnya **tidak menutup temuan itu**; ia memindahkan cacatnya ke catatan audit.
- Ledger ini diperbarui ketika sebuah kelas berubah status, misalnya setelah `P1-26` ditutup dan run baru dihasilkan komponen saat ini. Kolom volume yang menyusut sementara kolom batas tetap adalah tanda bahwa penutupannya belum nyata.

### Klaim lama yang masih berdiri di tempatnya (LOCKED)

`../../README.md` menyatakan klaim `FULL GLOBAL MARKET-DATA PRODUCTION READY` dan `MARKET_DATA_PRODUCTION_READY_LOCKED` tidak berlaku. **Pencabutan itu diumumkan di satu dokumen sementara klaimnya tetap tertulis di dokumen lain.**

Empat belas dokumen di `audit/` masih memuat pernyataan production-ready, termasuk `AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md:213` yang berbunyi `FULL_MARKET_DATA_PRODUCTION_READY=YES, with no remaining blocker` tanpa satu pun penanda bahwa ia sudah digantikan.

Pembaca yang mendarat di salah satu dari empat belas dokumen itu melihat klaim yang berlaku. Pencabutan yang hanya hidup di dokumen lain bukan pencabutan; ia harapan bahwa setiap pembaca membaca urutan yang benar.

Aturannya: **klaim dicabut di tempat ia ditulis.** Dokumen yang memuat klaim yang sudah tidak berlaku membawa penanda superseded pada klaim itu sendiri, dengan rujukan ke keputusan yang menggantikannya. Menghapus dokumennya tidak diperlukan dan tidak diinginkan — riwayat audit tetap bernilai — tetapi klaimnya tidak boleh terbaca sebagai berlaku.

#### Order conformance probes — baseline terukur 2026-08-03

Sebuah order tidak menjadi kuat karena kalimatnya baik. Ia menjadi kuat ketika kepatuhannya **dapat diukur sekarang**, sehingga jarak dokumen–implementasi berupa angka yang menyusut, bukan deskripsi yang melebar.

Setiap probe di bawah dijalankan terhadap sistem nyata. Nilai `sekarang` adalah baseline yang harus ditutup work order terkait; nilai itu **bukan** klaim bahwa strateginya lemah, melainkan ukuran seberapa jauh implementasi tertinggal dari strategi yang sudah dikunci.

| Order | Probe | Target | Sekarang | Terkait |
|---:|---|---:|---:|---|
| 1 | Istilah pada term ownership register yang terdefinisi di luar ownernya | 0 | **0** | `DOC-36` |
| 2 | Kolom bernuansa policy di luar tabel `watchlist_*` | 0 | **0** | `DOC-20` |
| 2 | Config key bernuansa policy pada `config/market_data.php` | 0 | **0** | `DOC-20` |
| 3 | Kapabilitas bertanda `UNSUPPORTED` yang ternyata dipetakan adapter | 0 | **0** | `DOC-01` |
| 4 | Token spesifik provider pada lima kontrak hilir | 0 | **0** | `DOC-31` |
| 5 | Config key yang diklaim kontrak tetapi tidak ada di config register | 0 | **0** | `DOC-33` |
| 6 | Resolver universe menyentuh `is_active` | 0 | **0** | P0-02 |
| 6 | Tabel identitas temporal yang belum ada di MariaDB | 0 | **4** | `P1-16` |
| 7 | Bar pada tanggal yang kalender tandai bukan hari perdagangan | 0 | **1** | `P1-19` |
| 7 | Kolom kalender wajib yang sudah tersedia | 6 | **0** | `P1-17` |
| 8 | Pelanggaran aturan validasi 1–9 atas 756.329 bar | 0 | **0** | `DOC-44` |
| 8 | Pelanggaran aturan cross-field ke-10 | 0 | **26** | `P1-18` |
| 9 | Baris `eod_bars_history` tanpa `publication_id` dari 56.138.923 | 0 | **0** | `DOC-46` |
| 9 | Baris proyeksi tanpa publikasi current | 0 | **1** | `P1-19` |
| 10 | Factor aktif tanpa verifikasi otoritatif atau manual | 0 | **15** | `P1-15` |
| 10 | Kolom verification state dan factor revision yang tersedia | 3 | **0** | `P1-20` |
| 11 | Artefak yang menyimpan identitas price-product agar basis dapat diperiksa setelah run | ≥1 | **0** | `P1-21` |
| 11 | Runtime default price basis sesuai default terpilih `STRUCTURAL_ADJUSTED` | ya | **`close`** | P0-04 |
| 12 | Denominator pada run gagal-total dibanding run sehat — menyusut berarti kegagalan tersembunyi | tidak menyusut | **900 vs 893, tidak menyusut** | `DOC-10` |
| 12 | Run coverage yang dihasilkan resolver universe temporal saat ini | seluruhnya | **0 dari 68.411** | `DOC-61` |
| 13 | Fakta wajib yang tersimpan terpisah pada `eod_eligibility` | 19 | **2** | `P1-23` |
| 13 | Kolom bernuansa tradability atau ranking pada `eod_eligibility` | 0 | **0** | `DOC-11` |
| 14 | `dv20_idr` sebagai `AVG(close x volume)` 20 bar tanpa pengali lot | rasio 1,0 | **1,0000** | `DOC-67` |
| 14 | Field likuiditas terpisah: actual, proxy bernama, rata-rata, trade count | 4+ | **1** | `P1-24` |
| 15 | Warm-up `NULL` pada baris paling awal tiap emiten (`ma50`/`ma20`/`atr14_pct`) | 975 | **975** | `DOC-69` |
| 15 | Radius kontaminasi diterbitkan sebagai angka oleh kontrak indikator | ya | **50 sesi + tak terbatas** | `DOC-68` |
| 16 | Run dengan `config_hash` non-null | 71.917 | **0** | `P1-25` |
| 16 | Publikasi `SEALED` tanpa binding config, yaitu `CONFIG_UNBOUND` | 0 | **33.414** | `DOC-71` |
| 17 | Pola bypass recency pada jalur baca konsumen | 0 | **0** | `DOC-72` |
| 17 | Route konsumen yang mengeksekusi read product | >=1 | **0** | belum diuji pemakaian |
| 18 | Hasil replay yang mencatat mode-nya | 20.635 | **0** | `DOC-74` |
| 18 | File test yang menjalankan as-known replay | >=8 | **0** | `DOC-75` |
| 18 | Hasil replay `BLOCKED` atas publikasi `CONFIG_UNBOUND` | 20.635 | **0** | `DOC-71` |
| 19 | Target freshness dan ambang availability diturunkan dari horizon | ada | **ada: 2 sesi / 5 sesi** | `DOC-77` |
| 19 | Activation marker ditetapkan dan scheduler aktif | ya | **`null` / `false`** | P1-11 |
| 20 | Migration tersedia yang sudah diterapkan ke MariaDB | 47 berkas | **45, dua tertinggal** | `P1-26` |
| 20 | Tabel `md_*` V2 yang ada di MariaDB | 13 | **0** | `P1-26` |
| 21 | Berkas test behavioral dibanding static guard teks-sumber | 129 : 0 | **95 : 34** | `DOC-80` |
| 21 | Berkas golden fixture / oracle yang benar-benar ada | >=1 | **0** | `DOC-81` |
| 21 | Test yang mengeksekusi rantai Wilder ATR panjang | >=1 | **0** | `DOC-81` |
| 22 | Temuan P0 dan P1 yang masih terbuka | 0 | **30** | `DOC-82` |
| 22 | Dokumen audit yang memuat klaim production-ready tanpa penanda superseded | 0 | **14** | `DOC-83` |
| 13 | Membership sector bersumber `DERIVED_REFERENCE` di bawah label `IDX-IC` | 0 | **4** | `DOC-84` |
| 13 | Reklasifikasi sector yang tercatat sebagai interval tertutup | >=1 | **0 dari 971** | `DOC-84` |

**Delapan dari enam belas probe sudah pada target.** Lima sisanya memetakan tepat ke temuan implementasi yang sudah tercatat — tidak ada kejutan di luar backlog, dan tidak ada temuan backlog tanpa probe yang mengukurnya.

Aturan yang mengikat probe ini:

- Sebuah work order tidak boleh ditandai `CONFORMANT` selama probe order-nya belum mencapai target. Kesesuaian dokumen tidak menggantikan angka.
- Probe yang sudah pada target wajib dijalankan ulang pada `W21` dan `W22`; nilai nol hari ini tidak menjamin nol setelah implementasi menyentuh jalurnya.
- Menambahkan persyaratan baru pada owner contract mewajibkan probe yang mengukurnya, agar penguatan dokumen tidak pernah lagi melebarkan jarak tanpa alat ukur.
- Probe adalah **pengukur konsentrasi dan kontradiksi**, bukan pembuktian kebenaran. Nilai nol pada seluruhnya tetap tunduk pada capability boundary masing-masing kontrak dan pada rekonsiliasi eksternal gate 13.

#### Sapuan penutup order 6–10

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-51` | 6–10 | Gate 11 mewajibkan **setiap** source, detector, resolver, dan validator menyatakan capability boundary. Gate itu diterapkan hanya di tempat kelemahan spesifik kebetulan ditemukan, tanpa sapuan. Enam owner document order 6–10 masih kosong: `Trading_Status_Source_Contract`, `EOD_Bars_Contract`, `Correction_Lifecycle_Safety_Contract`, `Dataset_Seal_and_Freeze_Contract`, `Historical_Correction_and_Reseal_Contract`, dan `Corporate_Action_and_Adjustment_Policy`; `Price_Adjustment_Contract` hanya memiliki bentuk miniaturnya di `:85` | Keenamnya ditutup, masing-masing dengan wilayah buta yang khas domainnya. Yang paling menentukan ada di `Corporate_Action_and_Adjustment_Policy`: hierarki verifikasi mengklasifikasikan peristiwa yang **diketahui** platform, sehingga aksi yang tidak dilaporkan sumber mana pun bukan `PROVIDER_REPORTED`, bukan `SYNTHETIC_CANDIDATE`, dan bukan terkarantina — ia sekadar absen, meninggalkan diskontinuitas tanpa satu pun penanda | `CLOSED` |
| `DOC-52` | 2, 10 | Gate 11 tidak dapat diperiksa mesin karena tidak menetapkan bentuk heading. Section `DOC-02` yang ditulis pertama bernama *"Detection sensitivity boundary"* dan section `DOC-46` bernomor *"5. Capability Boundary"* — keduanya memenuhi gate secara substansi tetapi lolos dari pemeriksaannya sendiri | Gate 11 kini menetapkan judul heading wajib dimulai dengan `Capability boundary` setelah nomor section opsional, dengan tiga bentuk sah dicontohkan. Heading `DOC-02` diseragamkan menjadi `Capability boundary — detection sensitivity`. Seluruh 13 owner document order 6–10 kini lolos pemeriksaan mekanis | `CLOSED` |

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-53` | 6, 7, 10 | Penutupan `DOC-39`, `DOC-42`, `DOC-51`, dan capability boundary status menciptakan **empat kewajiban rekonsiliasi eksternal terpisah** — universe, kalender, corporate action, dan trading status — tanpa satu pun memiliki konsepnya. Dua di antaranya sudah menyatakan aturan bersama dengan kata-kata berbeda dan **sudah berbeda isinya**: versi universe membatasi cakupan dari intentional dataset start, versi kalender tidak. Ini pola duplicate-ownership yang sama yang ditutup di order 2 | **Global gate 13** ditambahkan ke conformance matrix sebagai pemilik aturan bersama: dua arah, cadence independen dari pipeline harian, periode tak terekonsiliasi dinyatakan eksplisit, dan pipeline hijau bukan bukti rekonsiliasi. Keempat owner contract dipangkas menjadi **parameter domainnya sendiri** — sumber otoritatif, cakupan, dan kualifikasi klaim. Gate 13 diposisikan sebagai penawar gate 11 dan 12: keduanya menamai wilayah buta, gate 13 menetapkan satu-satunya cara mengisinya | `CLOSED` |
| `DOC-54` | 8 | `DOC-44` mewajibkan anomali volume nol tingkat-tanggal muncul sebagai quality finding **tanpa menyebut pemiliknya** — kewajiban menggantung yang tidak akan pernah dikerjakan siapa pun | Kepemilikan dipisah tegas: `EOD_Bars_Contract` memiliki aturan per-baris, `Run_Status_and_Quality_Gates_LOCKED.md` memiliki pemeriksaan tingkat-tanggal beserta ambangnya, karena **aturan per-baris tidak dapat melihat pola antar-baris secara konstruksi**. Section **date-level anomaly checks (LOCKED)** menetapkan tiga pemeriksaan minimum — proporsi volume nol, proporsi bar datar, dan jumlah kontradiksi cross-field — dengan ambang terikat config snapshot, pembanding memakai hari perdagangan, dan satu batas jujur: ketiadaan temuan bukan bukti tanggalnya bersih, karena pemeriksaan ini mendeteksi **konsentrasi**, dan cacat yang tersebar merata tidak menghasilkan konsentrasi | `CLOSED` |

| `DOC-55` | 8, 13 | `DOC-54` menambahkan mekanisme deteksi baru — date-level anomaly checks — ke `Run_Status_and_Quality_Gates_LOCKED.md` **tanpa capability boundary berjudul standar**, melanggar gate yang baru diperketat `DOC-52`. Pernyataan batasnya ada tetapi hanya sebagai bullet di dalam section, sehingga lolos pemeriksaan mekanis | Section **capability boundary (LOCKED)** ditambahkan dengan empat wilayah buta: cacat tanpa gate yang dideklarasikan lolos seluruhnya; cacat yang tersebar merata menggeser baseline pembandingnya sendiri sehingga tidak menghasilkan sinyal; ambang yang terlewat tipis bukan berarti tanggalnya sehat; dan diamnya sistem terhadap failure mode yang belum dikenal adalah keadaan default, bukan temuan | `CLOSED` |

| `DOC-56` | 6–10 | **Kesalahan metode sapuan, bukan sekadar kekurangan cakupan.** Enam sapuan pertama memeriksa daftar dokumen yang disusun sendiri, bukan daftar otoritatif. Ketika pemeriksaan digerakkan oleh **assignment stage 6–10 pada conformance matrix** — dokumen yang memang memiliki peran sebagai owner assignment — muncul 14 owner contract tanpa capability boundary yang tidak pernah masuk daftar periksa | Gate 11 diberi **cakupan eksplisit**: berlaku bagi dokumen yang memiliki mekanisme penghasil verdict, state, flag, atau signal — bukan setiap dokumen yang ter-assign. Kontrak yang hanya menetapkan pemisahan tanggung jawab, lokasi penyimpanan, kewenangan operator, atau bentuk artefak berada di luar cakupan, karena boundary generik pada dokumen semacam itu memenuhi pemeriksaan mekanis tanpa mengajarkan apa pun dan justru melemahkan gate. Enam dokumen penghasil verdict dari 14 itu ditutup: impact flags, cutoff/finalization, publishability state, finalize lock/pointer, pointer integrity, dan cross-consistency | `CLOSED` |

| `DOC-57` | 6–10 | Setelah cakupan gate 11 dipertegas, delapan dokumen di luar cakupan tetap muncul sebagai temuan pada pemeriksaan mekanis. Pemeriksa tidak dapat membedakan **"belum ada"** dari **"tidak berlaku"**, sehingga gate itu kembali tidak terverifikasi — dengan cara baru | Ketidakberlakuan dinyatakan eksplisit, disiplin yang sama seperti `NONE` wajib eksplisit pada kolom ledger. Delapan dokumen memperoleh section **capability boundary scope (LOCKED)** yang menyatakan `Gate 11: not applicable` beserta alasan domainnya, dan menunjuk ke owner contract yang memang memiliki mekanismenya. Stage 6–10 kini tuntas terhadap assignment matrix: setiap dokumen memiliki boundary atau menyatakan ketidakberlakuannya | `CLOSED` |

Pola yang berulang pada keenam boundary baru: setiap mekanisme membuktikan sesuatu tentang **apa yang tercatat**, dan tidak satu pun membuktikan sesuatu tentang **apa yang tidak pernah tercatat**. Seal melestarikan nilai salah dengan setia; correction memvalidasi proses bukan hasil; adjustment yang koheren tetap koheren meski faktornya keliru; status yang bersih hanya berarti status tercatat diterapkan dengan benar.

#### Order 10 — corporate action dan price adjustment

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-48` | 10 | Empat verdict continuity check — `NO_SERIES`, `NO_MATERIAL_GAP`, `GAP_AMBIGUOUS`, `GAP_BEYOND_EXCHANGE_BAND` — dipakai runtime dan mengarantina output, tetapi **tidak muncul di satu dokumen pun**. `GAP_AMBIGUOUS` sendiri menahan 74 aksi di produksi tanpa definisi maupun jalur penyelesaian | Section **continuity verdicts (LOCKED)** mendefinisikan keempatnya, dengan `GAP_BEYOND_EXCHANGE_BAND` diikat ke exchange band milik `Exchange_Market_Structure_Facts_LOCKED.md` | `CLOSED` |
| `DOC-49` | 10 | Verdict kontinuitas dan verification state adalah dua sumbu ortogonal yang tidak pernah dinyatakan terpisah. Buktinya di data: **kelima belas** aksi berfaktor `DERIVED_FROM_PRICE_SERIES` membawa `continuity_check_status = 'GAP_BEYOND_EXCHANGE_BAND'` — verdict aritmetika atas harga dipakai seolah membenarkan faktor, padahal `:42` hanya mengizinkan `AUTHORITATIVE_VERIFIED` atau `MANUAL_VERIFIED` menjadi adjustment-active | Section **a continuity verdict is not a verification state (LOCKED)**: verification menjawab *apakah peristiwanya diketahui dan dengan terms apa*; continuity menjawab *apa yang ditunjukkan deret harga*. `GAP_BEYOND_EXCHANGE_BAND` paling banter menghasilkan `SYNTHETIC_CANDIDATE`, dan verdict yang diukur pada tanggal yang salah **mewarisi kesalahan tanggal itu** | `CLOSED` |
| `DOC-50` | 10 | `GAP_AMBIGUOUS` tidak memiliki jalur keluar terdokumentasi. Itu persis yang menghentikan pelepasan 74 karantina pada sesi ini | Section **resolving `GAP_AMBIGUOUS` (LOCKED)**: hanya bukti **independen dari deret harga** yang menutupnya — verifikasi terms, atau bukti otoritatif bahwa tidak ada aksi pada anchor itu. Dinyatakan eksplisit tidak memadai: ketiadaan break terdeteksi (detektor punya lantai sensitivitas), gap yang kecil (justru itu yang membuatnya ambigu), dan berlalunya waktu. Karantina yang tidak terselesaikan **bertahan tanpa batas sebagai fail-safe yang disengaja**, bukan cacat backlog | `CLOSED` |

Forbidden behavior bertambah empat butir, termasuk larangan membersihkan karantina demi mengurangi jumlah baris terkarantina.

**Done criteria order 10 sudah dipenuhi kontraknya** melalui `Corporate_Action_and_Adjustment_Policy.md:42` — *"Synthetic candidates always quarantine and cannot become verified merely because their ratio resembles a common split."* Hierarki verifikasi lima state dan effective-date hierarchy yang menempatkan `ex_date` sebagai anchor primer juga sudah lengkap dan sejalan dengan P1-05.

Keadaan datanya berbeda dari kontraknya: dari 530 corporate action, **515 aksi otoritatif tidak memiliki faktor sama sekali**, sementara **15 aksi berfaktor seluruhnya bersumber `DERIVED_FROM_PRICE_SERIES`**. Satu-satunya sumber faktor aktif di sistem saat ini adalah yang diturunkan dari deret harga — persis yang dilarang kontrak. Itu tercatat sebagai `P1-15` dan menunggu keputusan Anda.

#### Order 9 — history, seal, correction, dan lineage

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-46` | 9 | Kesembilan kontrak melindungi **history**, tidak satu pun memeriksa apakah **proyeksi live** masih bersesuaian dengannya. `EOD_Bars_Contract:31` menyatakan `eod_bars` non-otoritatif dan dapat dibangun ulang dari baris publication-bound — klaim tanpa kewajiban verifikasi. Bukti nyata: satu baris di `eod_bars` tidak tercakup publikasi current mana pun | Section **projection reconciliation contract (LOCKED)**: rekonsiliasi dua arah antara proyeksi dan publikasi current, kecocokan nilai per field, hasil eksplisit dengan periode tak terekonsiliasi yang dinyatakan. Baris yatim **dilaporkan, bukan dihapus diam-diam** karena penghapusan memusnahkan bukti asal-usulnya. Dan bila terjadi divergensi, **publikasi yang otoritatif — proyeksi yang dibangun ulang** | `CLOSED` |
| `DOC-47` | 9 | Guard immutability tidak menyatakan batasnya. Ia bersifat application-level, sementara `:68` mengklaim berlaku pada jalur repository, service, maintenance, repair, migration, dan operator | Section **capability boundary (LOCKED)**: guard membuktikan jalur tulis **terjaga** aman, bukan bahwa konten tersegel utuh atau benar. Sesi database langsung, migration di luar jalur, atau restore dari backup yang diubah tidak terlihat olehnya — verifikasi hash mendeteksinya hanya bila seseorang menjalankannya terhadap hash yang disimpan terpisah. Immutability juga melestarikan nilai salah yang tersegel dengan setia, dan `previous_publication_id` mencatat publikasi mana yang diganti, bukan apakah penggantian itu benar | `CLOSED` |

**Done criteria order 9 terbukti kuat terhadap data produksi.** `eod_bars_history` memuat 56.138.923 baris dengan **nol** tanpa `publication_id`. 844 tanggal perdagangan masing-masing memiliki lebih dari satu publikasi, sehingga revision lineage memang terbentuk. Perbandingan nilai antara `eod_bars` dan publikasi current menghasilkan **nol** baris berbeda — tidak ada jejak perbaikan in-place yang tersisa, termasuk dari jalur P0-01 yang pernah berjalan.

#### Order 8 — canonicalization dan EOD bars

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-44` | 8 | Kesembilan aturan validasi memeriksa setiap field terhadap domainnya sendiri. Tidak satu pun memeriksa **kombinasi yang mustahil**. Bukti nyata: 26 bar memiliki `volume = 0` sekaligus `high > low` — harga bergerak tanpa satu pun transaksi, yang tidak dihasilkan mekanisme pasar mana pun — dan **seluruhnya lolos kesembilan aturan** | Aturan ke-10 **cross-field consistency (LOCKED)**: `volume = 0` mewajibkan OHLC identik; volume nol dengan pergerakan harga adalah invalid, ditolak dengan reason code sendiri. Dinyatakan sebagai saudara sisi-volume dari aturan zero-price — harga nol mustahil sendirian, volume nol mustahil hanya dalam kombinasi. Ditambah kewajiban: cacat semacam ini mengelompok per tanggal akuisisi, sehingga tanggal dengan proporsi volume nol jauh di atas tetangganya wajib muncul sebagai quality finding | `CLOSED` |
| `DOC-45` | 8 | Section *provider field mapping* memuat tabel pemetaan lengkap yang menamai provider aktif, padahal `Source_Mapping_Contract_LOCKED.md` sudah memiliki aturan adapter dan capability matrix order 3 sudah memiliki kapabilitasnya. Tiga dokumen bersinggungan pada satu aturan | Diubah menjadi **source field mapping targets**: yang dimiliki di sini adalah **target kanonik** tiap konsep ternormalisasi, dinyatakan tanpa kosakata provider. Aturan adapter dan kapabilitas ditunjuk ke ownernya | `CLOSED` |

**Done criteria order 8 terbukti terhadap data produksi**, bukan hanya terbaca. Dari 756.329 canonical bar: nol OHLC bernilai nol atau negatif, nol OHLC null, nol pelanggaran `high >= max(open,close)`, nol pelanggaran `low <= min(open,close)`, nol volume negatif, dan nol volume null. 66.104 bar bervolume nol tetap sah menurut kontrak sebagai observasi no-trade yang source-backed. Ini order pertama yang acceptance criterion-nya diverifikasi empiris.

#### Order 7 — kalender dan trading status

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-42` | 7 | Kontrak tidak membedakan baris kalender yang **terverifikasi** dari yang merupakan **proyeksi**. Bursa menerbitkan jadwal libur hanya sejauh horizon tertentu; baris di luar itu adalah asumsi tentang tanggal, bukan sesi terkelola. Tanpa pembedaan ini, tahun perdagangan pertama di luar horizon menghasilkan expected bar pada **setiap** hari libur nasional, dan kegagalan coverage yang muncul terlihat seperti gangguan provider alih-alih asumsi kalender | Section **calendar provenance tiers (LOCKED)**: setiap baris membawa tier, sumber rekonsiliasi, dan tanggalnya. `PROJECTED` meresolusi ekspektasi ke `UNKNOWN`, **tidak pernah** `EXPECTED`, dan tetap berada di denominator fail-safe. Transisi tier adalah revisi berbukti, bukan edit in-place. Ditegaskan pula: **luas rentang bukan bukti otoritas** | `CLOSED` |
| `DOC-43` | 7 | `is_half_day` diwajibkan sebagai field dan dirujuk aturan snapshot, tetapi **tidak ada dokumen mana pun** yang menyatakan artinya bagi semantik EOD. IDX menjalankan beberapa sesi pendek tiap tahun; sesi pendek menekan volume karena alasan yang tidak ada hubungannya dengan emitennya, dan `dv20` adalah masukan likuiditas untuk weekly swing | Section **shortened-session semantics (LOCKED)**: sesi pendek tetap sesi lengkap dan bar normal — coverage memperlakukannya sama. Yang berubah dan wajib terlihat adalah volume beserta seluruh turunannya, dan rentang harga yang punya lebih sedikit kesempatan melebar. Konteks panjang sesi wajib dapat diambil bersama bar-nya, normalisasi apa pun menjadi keputusan berversi milik kontrak ukurannya, dan sesi pendek **tidak boleh disimpulkan dari volume rendah** | `CLOSED` |

Bukti data yang mendasari `DOC-42`: `market_calendar` memuat 2922 baris dari `2023-01-01` sampai `2030-12-31`, seluruhnya `source = manual`. Jumlah hari perdagangan per tahun adalah 239, 237, 236, dan 239 untuk 2023–2026 — realistis untuk IDX — lalu 246, 260, 261, dan 261 untuk 2027–2030. Angka 261 persis sama dengan 365 dikurangi 104 hari akhir pekan, artinya tahun-tahun itu tidak memuat satu pun hari libur.

Bukti yang mendasari `DOC-43`: `session_close_time` bernilai `NULL` untuk seluruh 1979 hari perdagangan, sehingga aturan pada `session_snapshot/Snapshot_Slot_Tolerances_and_Session_Rules_LOCKED.md:4` — *"if market calendar marks half-day and close time is known"* — tidak pernah dapat menyala.

`DOC-04` dan `DOC-08` sebelumnya sudah menutup status authority, source priority, kedudukan manual import, dan rekonsiliasi kelengkapan kalender dua arah. Done criteria order 7 sendiri sudah dapat difalsifikasi sejak awal melalui acceptance criterion-nya, yang menuntut calendar version, session evidence, temporal listing state, status evidence, hasil ekspektasi, dan alasannya dapat ditunjukkan untuk setiap keputusan.

#### Order 6 — identitas temporal dan survivorship

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-39` | 6 | Kontrak membuat **resolver** bebas survivorship, tetapi tidak dapat membuat **master** lengkap — dan keduanya guarantee yang berbeda. Emiten yang listing lalu delisting tanpa pernah tercatat tidak muncul di universe, tidak muncul di denominator coverage, dan tidak muncul di kedua sisi perbandingan replay. Tidak ada gate yang menyala, karena setiap gate menurunkan ekspektasinya dari master yang sama | Section **capability boundary (LOCKED)** plus **universe completeness is verified externally**: rekonsiliasi dua arah terhadap catatan listing/delisting resmi bursa, di luar pipeline harian. Acceptance criterion dipertegas — memenuhinya menghasilkan **survivorship-free resolver**, sedangkan **survivorship-free universe** menuntut rekonsiliasi eksternal; klaim wajib menyebut yang mana | `CLOSED` |
| `DOC-40` | 6 | `Symbol_Lifecycle_and_Mapping_Contract.md` tidak menyatakan batasnya. Rename atau reuse yang tidak pernah tercatat sebagai interval mapping akan resolve ke satu identitas melintasi dua era dan **memenuhi seluruh uniqueness rule** — tak terbedakan dari simbol yang memang tidak pernah berubah | Section **capability boundary (LOCKED)** dengan tiga wilayah buta: rename tak tercatat, boundary efektif yang meleset satu sesi, dan provider yang menyajikan sekuritas berbeda untuk simbol yang sama. Kelengkapan mapping mewarisi rekonsiliasi eksternal `DOC-39` | `CLOSED` |
| `DOC-41` | 6 | `ticker_id` diizinkan sebagai compatibility identity dengan syarat ekuivalensinya terdokumentasi dan invarian — syarat **pemakaian**, bukan kapan ia berakhir. Pola yang sama dengan alias `eligible` pada `DOC-22` | Section **legacy `ticker_id` retirement (LOCKED)**: stable identity kanonik, alias pensiun setelah dibuktikan tidak ada pembaca luar, pensiunnya lewat versioned schema/read-model change, dan **tidak boleh ada surface baru** yang mengunci pada `ticker_id`. Ditambah aturan tegas: `ticker_id` tanpa ekuivalensi terdokumentasi bukan alias, melainkan identitas belum terselesaikan yang wajib fail closed | `CLOSED` |

Bukti data yang mendasari `DOC-39`: master memuat 977 ticker, 962 aktif, 33 dengan `delisted_date`. Delisting paling awal `2023-04-06` dan **nol** sebelum intentional dataset start — benar secara desain. 15 delisting berada dalam rentang dataset, 18 sisanya bertanggal masa depan hingga `2026-11-10`, yang menjelaskan baris `is_active=1` yang tetap memiliki `delisted_date`. Apakah kelima belas itu lengkap tidak dapat dijawab dari dalam sistem, dan justru itulah isi temuannya.

P0-02 sendiri tampak sudah ditutup di kode: `TickerMasterRepository::getUniverseForTradeDate()` kini mendelegasikan ke `TemporalIdentityRepository::universeAsOf()` yang memfilter `listed_date <= T` dan `delisted_date > T` tanpa menyentuh `is_active`. Verifikasi akhirnya menunggu `W05`.

#### Sapuan penutup order 1–5

Pemeriksaan ulang lima order yang sudah ditutup, terhadap suntingan yang dibuat pada order-order itu sendiri.

| ID | Order | Kelemahan | Penutupan | State |
|---|---:|---|---|---|
| `DOC-36` | 1 | *Done criteria* order 1 berbunyi *"tidak ada ambiguity"* — sebuah penilaian yang tidak dapat difalsifikasi. Ia satu-satunya dari lima order yang tidak memiliki bukti mekanis | Section **term ownership register (LOCKED)**: 33 istilah dinyatakan dimiliki dokumen ini secara eksklusif, dokumen lain boleh merujuk tetapi tidak mendefinisikan ulang. *Done criteria* menjadi uji konkret — setiap istilah memiliki **tepat satu** tempat definisi | `CLOSED` |
| `DOC-37` | 1, 5 | Uji dari `DOC-36` dijalankan dan **langsung menemukan divergensi**: `Operational activation` memiliki heading definisi di order 1 dan order 5, dan daftar prasyaratnya sudah berbeda — Terminology menyebut lima butir, kontrak resilience menyebut enam dengan tambahan *"membuktikan idempotent retry/backfill dan recovery dari partial failure"* | Pembagian kepemilikan ditegaskan: order 1 memiliki **arti** operational activation dan dua konsekuensi yang lahir dari istilahnya; order 5 memiliki **prasyarat operasional**-nya karena itu proof, bukan terminologi. README juga berhenti menyebut jumlah butir | `CLOSED` |
| `DOC-38` | 3, 5 | Batas kapasitas `manual_file` ditulis di order 3, dokumen strategi. Order 5 adalah kontrak yang dibuka operator ketika akuisisi gagal berhari-hari, dan di sana `manual_file` masih tampak sebagai jalur pemulihan tanpa batas | Section **batas kapasitas jalur pemulihan (LOCKED)** ditambahkan ke order 5: rescue satu tanggal, bukan jalur kelangsungan. Kegagalan berhari-hari dinyatakan **tidak memiliki jalur pemulihan setara** dan wajib dieskalasi, bukan ditambal recovery manual berulang | `CLOSED` |

Verifikasi silang lain pada sapuan ini bersih: keempat config key yang dirujuk order 5 ada di config dan di register; tidak ada pernyataan horizon yang bertentangan tersisa di seluruh dokumen.

Seluruh 33 istilah diuji. Delapan kemunculan heading di luar owner diperiksa satu per satu dan seluruhnya positif palsu: subsection field pada kontrak eligibility, section index per tabel pada kontrak constraint, judul dokumen command, serta `### Import`/`### Promote` pada kebijakan manual-file yang menjelaskan perilaku command per fase, bukan mendefinisikan ulang istilahnya.

**Batas uji ini harus dinyatakan.** Ia mencocokkan heading, sehingga kuat untuk istilah yang khas — divergensi `operational activation` tertangkap pada eksekusi pertama — dan berisik untuk istilah umum seperti `import`, `quality`, atau `indicators`, yang menuntut penilaian manusia. Uji ini membuktikan tidak ada definisi tandingan yang ditemukan, bukan bahwa tidak mungkin ada. Guard test otomatis pada order 21 perlu kriteria yang lebih tajam daripada kecocokan heading.

Done criteria order 4 terbukti bersih: pencarian `yahoo`, `.JK`, `period1`, `period2`, `chart api`, `query1`, dan `adjclose` ke kontrak indikator, read model, eligibility, registry, dan coverage mengembalikan **nol** — tidak ada provider-specific leakage ke hilir.

Strategy acceptance criteria order 3 bertambah dari 7 menjadi 11 butir. Uji kontradiksi kapabilitas bersih: tidak ada kontrak yang mewajibkan kapabilitas yang capability matrix sebut `UNSUPPORTED` — conformance matrix `:271` memakai *"bila tersedia"* dan kontrak volume memperlakukan `dv20_idr` sebagai alias proxy dengan benar.

Boundary invariants bertambah dari 11 menjadi 14. Penegakan batasnya sendiri diuji terhadap sistem nyata dan **tertahan**: seluruh kolom berbau policy berada di tabel `watchlist_*`, tidak ada config key policy, dan tidak ada command policy. Tiga kandidat pelanggaran di sisi market-data — `eod_runs.publish_target`, `md_replay_daily_metrics.candidate_publication_id`, dan `default_error_policy` — seluruhnya positif palsu dari kosakata bertumpuk yang kini didokumentasikan oleh `DOC-21`.

#### Kontrak yang sudah diverifikasi memadai — jangan dibuka ulang

Tercatat agar remediasi berikutnya tidak menulis ulang aturan yang sudah benar, dan tidak menciptakan pemilik kedua untuk satu aturan.

| Dokumen | Sudah menyatakan | Lokasi |
|---|---|---|
| `../../registry/Price_Adjustment_Contract_LOCKED.md` | `adj_close` bukan analytical product dan bukan per-row fallback; faktor aktif tidak boleh diturunkan dari `open`/`previous close`/`close`/besaran gap saja; faktor `1` atau faktor absen bukan bukti window bersih | `:13`, `:79`, `:85` |
| `../../book/Source_Mapping_Contract_LOCKED.md` | `adj_close` hanya nullable observation metadata tanpa semantik fallback; status tidak dapat disimpulkan dari provider response kosong | `:27`, `:33` |
| `../../registry/Volume_and_Turnover_Normalization_LOCKED.md` | actual traded value `NULL` ketika tidak source-backed; proxy `RAW close * RAW volume` bernama terpisah dan tidak pernah dikoalesikan dengan actual | `:15`–`:39` |
| `../../book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md` | `schema_config_impact`, `backfill_impact`, dan `test_ids` wajib per assignment row dengan `NONE` eksplisit — kewajiban kontribusi config dan schema sudah enforceable | `:20`–`:23` |

**Status register revalidated 2026-08-08: seluruh `DOC-01`–`DOC-84` `CLOSED`.** `DOC-84` adalah item terakhir yang ditambahkan setelah keputusan kepemilikan sector; tidak ada remediation item dokumentasi yang masih terbuka. Dua temuan implementasi yang muncul selama penyusunan register dipindahkan ke backlog sebagai `P1-13` dan `P1-14`; keduanya bukan kekurangan dokumen — pada `P1-14` justru dokumennya yang benar dan database yang menyimpang.

Register dibuka kembali bila audit berikutnya menemukan pernyataan yang masih hilang. Penambahan item baru wajib mengikuti aturan yang sama: kekurangan diverifikasi lebih dulu dengan pembacaan file, query, atau grep, dan bukti verifikasinya dicantumkan.

---

## Implementation roadmap

### Phase 0 — strategy correction

Tujuan: menyelesaikan keputusan semantik sebelum code remediation.

Status dokumentasi: **COMPLETE**. Daftar berikut menjadi baseline yang tidak boleh dibuka kembali hanya untuk menyesuaikan legacy code.

- tetapkan Regular-Market EOD scope;
- tetapkan intentional dataset start `2023-01-02` dan operational activation semantics;
- tetapkan temporal universe semantics;
- tetapkan `STRUCTURAL_ADJUSTED` signal basis;
- tetapkan corporate-action verification hierarchy;
- tetapkan coverage versus eligibility separation;
- tetapkan no-history-rewrite invariant;
- tetapkan Yahoo bootstrap boundary;
- perbarui owner dan supporting contracts stage 1 sampai 21, kunci work order `W00`–`W22`, dan assign seluruh dokumen/deliverable/proof pada conformance matrix.

Exit gate:

- tidak ada contradiction antar owner docs;
- semua istilah memiliki satu owner;
- tidak ada keputusan P0 yang masih ambigu.

Exit gate Phase 0 telah dipenuhi pada level dokumentasi. Phase 1 dan seterusnya adalah pekerjaan implementasi dan pembuktian.

### Phase 1 — stop unsafe behavior

Tujuan: mencegah kerusakan historis baru.

- disable/remove direct price-scale apply path;
- ubah price break menjadi anomaly-only;
- blokir unverified derived corporate action dari adjustment;
- perbaiki point-in-time universe resolver;
- tambahkan regression tests untuk keempat area tersebut.

Exit gate:

- zero code path dapat mengubah sealed bars/history in-place;
- inactive-now-but-active-then ticker muncul pada historical universe;
- unresolved break selalu menghasilkan quarantine/non-eligibility.

### Phase 2 — correct analytical data products

Tujuan: memastikan analytical data product coherent.

- buat raw, structural-adjusted, dan total-return product semantics;
- implement versioned factor application;
- perbaiki exact ATR seed/state;
- rename turnover proxy dan siapkan actual value fields;
- perbaiki ex-date event windows;
- hilangkan zero-price placeholder contradiction.

Exit gate:

- split/reverse/rights fixture menghasilkan continuous adjusted OHLC;
- indicator rerun menghasilkan byte/number-equivalent outputs untuk publication/config sama;
- liquidity field tidak misleading.

### Phase 3 — provenance and operations

Tujuan: menjadikan pipeline stabil untuk forward validation.

- isi config hash dan snapshot reference;
- tambahkan immutable source observation identity;
- aktifkan daily scheduling dan promote/readiness path;
- tambahkan freshness state dan alerts;
- sinkronkan testing migrations;
- jalankan backfill/replay determinism proof.

Exit gate:

- daily pipeline berjalan tanpa manual intervention normal;
- stale date tidak dapat dibaca sebagai fresh;
- setiap consumer-facing market-data row dapat dilacak ke source/publication/config/factor.

### Independent downstream proof period — outside market-data readiness

Tujuan downstream: membuktikan manfaat use case awal tanpa membeli data berbayar terlebih dahulu.

- jalankan forward paper watchlist;
- rekam quality incidents terpisah dari strategy outcomes;
- ukur coverage, freshness, anomaly, correction, dan replay stability;
- downstream watchlist mengukur expectancy, drawdown, turnover, dan usefulness secara out-of-sample setelah biaya asumsi yang realistis;
- kumpulkan user feedback dan operational cost.

Exit gate:

- manfaat cukup stabil untuk membuat keputusan produk;
- kualitas source limitation dapat diukur, bukan diasumsikan;
- keputusan paid provider dapat dibuat berdasarkan evidence bila kelak diperlukan.

Seluruh kegiatan dan exit gate pada bagian ini milik validasi produk/watchlist downstream. Kegiatan ini tidak diperlukan untuk menutup dokumentasi, implementation conformance, atau operational validation market-data dan tidak boleh mengubah fakta historis agar outcome strategi tampak lebih baik.

### Future provider decision

Bagian ini **bukan pekerjaan sekarang**.

Ia hanya dibuka bila value, SLA, licensing, correction authority, richer fields, redistribution, atau commercial use menjadi kebutuhan nyata. Jika dibuka, lakukan provider adapter evaluation, bounded parallel reconciliation, controlled priority switch, dan lineage-preserving publication transition.

---

## Minimum versioned market-data read product (initial consumer profile)

Consumer-facing publication minimal perlu menyediakan:

Naming/ownership rule: schema yang dimiliki package ini harus bernama strategy-neutral, misalnya `Market_Data_Read_Product_Schema_V1_LOCKED.md`. Dokumen bernama `Weekly_Swing_Read_DTO_Schema_V1_LOCKED.md` tidak boleh menjadi owner di `docs/market_data`; DTO policy semacam itu harus berada pada domain watchlist dan hanya mengonsumsi read product market-data.

### Identity and publication

- `instrument_id` atau stable identity equivalent;
- ticker/listing identity as-of trade date;
- requested dan effective trade date;
- run/publication/version identity;
- freshness/readability state.

### Market observations

- raw open, high, low, close, volume;
- traded value dan trade count bila source menyediakan;
- board/trading status bila tersedia;
- source provider dan observed/ingested timestamp;
- data-quality state.

### Analytical price product

- price-basis identity;
- coherent structural-adjusted OHLC/volume;
- adjustment/factor version;
- contamination or unresolved-event flag.

### Indicators

- indicator-set version;
- ATR and registered indicators required by the declared read-product version;
- nullability/warm-up state;
- market benchmark context yang dideklarasikan sebagai factual field pada read-product version.

### Eligibility facts

- coverage state;
- quality state;
- liquidity measures and proxy labels;
- suspension/trading-status state;
- event-risk state;
- eligibility boolean and reason codes.

Market-data tidak boleh mengeluarkan buy/sell signal, ranking alpha, entry price, stop loss, target, position sizing, ataupun threshold tradability. Consumer profile hanya memengaruhi factual field contract, bukan market-data readiness criteria.

---

## Future implementation-audit gates before any production relock

Bagian ini adalah acceptance specification untuk audit setelah coding selesai. Tidak satu pun executed gate di bagian ini diperlukan untuk menyatakan dokumentasi strategi ready; seluruhnya diperlukan untuk implementation/operational claim.

### Data integrity gates

- zero in-place mutation terhadap sealed/current historical bars;
- all corrections create new revision/publication lineage;
- historical universe passes survivorship and symbol-lifecycle fixtures;
- corporate-action adjustments require verified factor/version;
- no mixed price basis in one indicator vector.

### Analytical gates

- exact ATR passes short, long-chain, gap, and corporate-action fixtures;
- all indicators reproduce identically from the same publication and config;
- invalid/missing/zero-placeholder rows never enter price math;
- liquidity metric naming matches its actual semantics.

### Operational gates

- requested latest trading date is processed automatically;
- stale, missing, or partial states are visible and fail-safe;
- import/promote/replay/backfill are idempotent under retry;
- config hash/snapshot and source identity are non-null for published data;
- migration/schema parity passes in production-like and testing environments.

### Consumer-interface gates

- every downstream consumer reads only the publication-aware contract;
- every exclusion has persisted reason codes;
- coverage, quality, liquidity, event risk, data usability, and downstream strategy filtering remain distinguishable;
- consumer cannot silently fall back to stale or raw data.

### Proof gates

- targeted regression tests pass;
- full MarketData suite passes;
- executed daily, correction, replay, and failure evidence is captured;
- consecutive trading-day forward operation demonstrates stable freshness and fail-safe behavior;
- audit report is updated only after evidence, not before.

---

## Yahoo Finance decision within this roadmap

Yahoo Finance is **accepted**, not tolerated as an accidental defect.

Current decision:

- primary bootstrap EOD source: `api_free/yahoo_finance`;
- controlled **one-date operational rescue**: `manual_file`; bukan continuity source untuk outage multi-hari;
- purpose: prove the market-data product can deliver useful, governed data for an initial use case before paid-data spending;
- quality posture: full validation, provenance, quarantine, correction, and readability gates remain mandatory;
- disclosure: never label Yahoo data as official IDX data;
- licensing posture: usage and redistribution remain subject to applicable provider terms;
- paid-provider project: deferred and not part of current remediation.

Yahoo dependency changes the implementation of acquisition resilience, but it does not lower the target quality of canonical data. The future-safe investment now is provider-neutral contracts and immutable lineage, not early vendor procurement.

---

## Previous implementation evidence snapshot — non-normative handoff

Read-only inspection sebelum documentation closure observed hal-hal berikut. Snapshot ini tidak menciptakan behavior dan dapat menjadi stale setelah implementasi dimulai:

- ticker master: `977` total, `962` current active, `33` rows with delisted date;
- latest canonical bar date: `2026-07-28`, `870` rows;
- calendar marked `2026-07-29`, `2026-07-30`, and `2026-07-31` as trading days without equivalent latest bars/runs at inspection time;
- latest inspected publication for `2026-07-28`: run `72062`, publication `72738`, version `8`, sealed;
- source provider: `yahoo_finance`;
- coverage reported `866/866 = 100%` after excluding `83` suspended and `13` dormant securities;
- output-affecting `config_hash` and `config_snapshot_ref` were null;
- daily scheduling config was disabled, consistent with the stated development phase but not sufficient after operational activation;
- `530` corporate-action rows existed, including `10` synthetic `derived_price_scale_break` rows;
- `18` price-scale break endpoint rows were marked repaired;
- several testing-environment migrations were pending while the default database had the structures;
- full MarketData PHPUnit sebelum koreksi strategi: `1152 tests / 8455 assertions`, pass.
- full MarketData rerun terakhir pada documentation-closure audit mengeksekusi `1153 tests / 8649 assertions` dan menghasilkan `3 errors / 11 failures`; seluruh outcome tersisa berada pada legacy semantic expectations di `DerivationFillsRecordedActionTest`, `PriceAdjustmentTest`, dan `PriceScaleStretchRepairTest` yang masih menuntut synthetic derivation/direct repair yang sekarang dilarang owner strategy.
- targeted re-verification terakhir juga lulus untuk audit synchronization (`4 tests / 253 assertions`), SQLite schema sync (`6 tests / 528 assertions`), dan published-column hash coverage (`16 tests / 24 assertions`).
- setelah strategy update order 20–21, targeted SQLite V2 schema/anti-repair guard lulus `6 tests / 528 assertions`; hasil ini hanya membuktikan mirror shape dan tidak menggantikan full semantic/runtime proof.

Interpretation:

- breadth dan automated coverage sudah kuat;
- reported `100%` coverage belum cukup membuktikan delivery completeness karena dormancy semantics perlu dikoreksi;
- the gap after `2026-07-28` is a development frontier, not a current production incident or architecture blocker;
- provenance, temporal universe, and correction safety remain material correctness gaps independent of the development frontier;
- green tests tidak membatalkan findings ketika tests mengunci behavior yang perlu dikoreksi.
- additive V2 schema mengurangi implementation gap, tetapi nullable rollout fields dan test-mirror pass tidak menutup P0 service/command behavior atau P1 writer/enforcement gaps.

---

## External real-market references used by the audit

- [IDX Trading Hours and Mechanism](https://www.idx.id/en/products-services/trading-hours-and-mechanism/) — market/session/board context.
- [IDX Data Services](https://testing3.idx.id/en/products/idx-data-services/) — EOD fields including prices, volume, value, trades, board, and status, plus data-service/licensing context.
- [IDX Stock List](https://www.idx.id/en/market-data/stocks-data/stock-list) — current listed-equity reference, not a substitute for historical point-in-time universe.
- [KSEI Corporate Action](https://web.ksei.co.id/services/types/corporate-action?setLocale=en-US) — mandatory/voluntary corporate-action types and event context.
- [OJK POJK 15/2022](https://www.ojk.go.id/id/regulasi/Documents/Pages/Pemecahan-Saham-dan-Penggabungan-Saham-oleh-Perusahaan-Terbuka/POJK%2015%20-%2004%20-%202022.pdf) — Indonesian split/reverse-split regulatory context.

Institutional standards such as ISO identifiers and full securities-event messaging were reviewed only as future architectural references. They are not current EOD data-readiness requirements.

---

## Final prioritized conclusion

Urutan handoff yang benar adalah:

1. documentation strategy correction — **complete**;
2. implement work order `W00`–`W21` sesuai blueprint dan tutup seluruh ledger assignment pada conformance matrix tanpa menyesuaikan contract kepada legacy behavior;
3. jalankan audit implementation conformance terhadap schema/config/code/tests;
4. sebelum activation, catch up development frontier, aktifkan scheduling/freshness protection, dan jalankan operational validation;
5. secara terpisah, downstream boleh menjalankan forward Weekly Swing proof dengan Yahoo bootstrap tanpa menjadi gate market-data;
6. ukur manfaat use case dan source limitations secara nyata;
7. pertimbangkan provider berbayar hanya jika evidence kemudian membenarkannya.

Final documentation status:

> **`DOCUMENTATION_STRATEGY_READY` — documentation strategy/synchronization `PASS` (`22/22`, revalidated 2026-08-08); implementation conformance and operational validation are not claimed.**

Audit berikutnya harus menilai implementasi terhadap baseline ini. `AUDIT_FINAL_STATE.md` hanya boleh memberikan implementation/production relock setelah P0/P1 critical ditutup oleh schema/config/code/tests dan executed evidence yang konsisten.

Watchlist implementation dan performance proof tidak termasuk admission evidence untuk relock tersebut.
