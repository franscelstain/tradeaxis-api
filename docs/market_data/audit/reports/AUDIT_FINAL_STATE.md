# Audit Final State — Market-Data Data-Readiness Strategy

## Document role

Dokumen ini adalah **satu-satunya laporan audit kanonik** untuk state aktif `docs/market_data`. Scope penutupan saat ini adalah kesiapan **dokumentasi strategi** sebagai panduan pembangunan market-data IDX Regular-Market EOD yang valid dan stabil.

Audit dokumentasi memeriksa:

1. strategi terhadap praktik pasar nyata yang relevan untuk data saham IDX Regular-Market EOD;
2. konsistensi scope, terminology, invariants, data products, schema meaning, proof specification, dan operations contracts;
3. kecukupan urutan pembangunan agar implementasi berikutnya tidak perlu menebak keputusan domain.

Code, migration runtime, database state, executed tests, scheduler, dan operational evidence **bukan syarat kelulusan dokumentasi**. Temuan implementasi yang masih dicatat di bagian bawah adalah handoff backlog untuk pembangunan dan audit berikutnya, bukan penurun status dokumentasi.

Behavior normatif dimiliki owner contracts. Urutan kerja aktual `W00`–`W22` dimiliki `book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`; assignment seluruh dokumen/deliverable/proof dimiliki `book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`; command/result/remediation lifecycle dimiliki `book/Market_Data_Implementation_Command_Protocol_LOCKED.md`; current state disimpan di `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`. Audit ini tidak menciptakan behavior paralel.

Tanggal audit: `2026-08-02`

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

Verdict ini hanya menutup dokumentasi strategi. Ia tidak menyatakan sistem saat ini research-ready, implementation-conformant, operationally validated, atau production-ready. Klaim lama `FULL GLOBAL MARKET-DATA PRODUCTION READY` tetap historical dan tidak membuktikan kesesuaian terhadap baseline yang sekarang dikoreksi.

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
| Market-data scope and downstream boundary | READY | IDX Regular-Market EOD dikunci; Weekly Swing hanya initial consumer profile dan downstream ownership eksplisit |
| Dataset boundary and operating phase | READY | `2023-01-02`, development frontier, dan operational activation dibedakan |
| Yahoo bootstrap and provider boundary | READY | Yahoo sah untuk pembuktian manfaat; paid provider bukan current backlog |
| Source observation and resilience | READY | Immutable envelope, provenance, validation, quarantine, retry, dan activation-aware freshness ditetapkan |
| Temporal identity/calendar/status | READY | Listing, symbol, mapping, session, dan status memiliki as-of/known-time semantics |
| Canonical RAW and corrections | READY | Raw/canonical meaning tunggal; zero placeholder dan in-place published rewrite dilarang |
| Corporate actions and price products | READY | Verified revisioned events/factors dan coherent product boundaries ditetapkan |
| Indicators and liquidity metrics | READY | Structural basis, stable Wilder ATR, nullability, actual-versus-proxy semantics ditetapkan |
| Coverage and data usability | READY | Expectation/delivery dipisahkan dari quality/liquidity/status/event risk dan reason codes; policy tradability tetap downstream |
| Config, publication, and consumer read | READY | Full snapshot binding, immutable publication, freshness, dan minimum DTO ditetapkan |
| Replay/backtest | READY | Exact publication dan as-known/knowledge-cutoff modes ditetapkan |
| Operations | READY | Import/promote, correction, stale/failure handling, and activation-aware SLO ditetapkan |
| Schema and test specifications | READY | Target model families, dictionary meanings, semantic oracles, and negative fixtures ditetapkan |
| Implementation sequencing and audit handoff | READY | Work order dikunci pada blueprint; assignment/exit gates pada matrix; start/audit/remediation/re-audit/advance dan result format pada command protocol; current next command pada ledger |

Dokumentasi tidak memakai skor numerik karena angka dapat mencampurkan completeness dokumen dengan readiness implementasi. Kelulusan diberikan berdasarkan tidak adanya keputusan market-data material yang ambigu atau saling bertentangan pada scope IDX Regular-Market EOD.

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

## Prioritized findings

### P0 — must be corrected before decision-grade use

| ID | Finding | Evidence | Risk | Required correction |
|---|---|---|---|---|
| P0-01 | Historical price-scale repair mengubah data in-place | `app/Application/MarketData/Services/PriceScaleStretchRepairService.php` melakukan update terhadap `eod_bars` dan `eod_bars_history` | Sealed history, hashes, replay, dan prior watchlist dapat berubah tanpa publication revision | Hilangkan apply path langsung; detector hanya membuat anomaly; correction harus membuat publication baru |
| P0-02 | Historical universe memakai current active state | `TickerMasterRepository.php` memfilter `is_active=1` sebelum as-of listed/delisted dates | Survivorship bias dan replay universe salah | Universe resolver harus sepenuhnya as-of-date dan diuji dengan inactive-now-but-active-then fixture |
| P0-03 | Synthetic corporate action dan break linkage tidak fail-safe | Derivation dapat membuat action dari price anomaly tetapi `matched_corporate_action_id` tidak selalu terpasang; quarantine dapat tetap salah | False adjustment atau affected rows lolos tanpa factor yang benar | Price break hanya candidate; verified/manual/authoritative action wajib sebelum factor dipakai; linkage atomik |
| P0-04 | Price basis kontradiktif dan dapat mencampur scale | Docs memilih `ADJ_CLOSE`, runtime memakai `close`, selected default membolehkan per-date fallback | MA, ROC, ATR, dan ranking dapat mengalami discontinuity palsu | Kunci `STRUCTURAL_ADJUSTED` sebagai signal basis atau basis lain yang eksplisit; hilangkan per-row fallback |

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

Kekurangan pernyataan yang masih terbuka pada dokumen-dokumen di bawah dicatat per file pada **Documentation remediation register — per dokumen**. Sebelum menjalankan sebuah order, periksa apakah order tersebut memiliki item `DOC-xx` yang masih `OPEN`.

**Nomor order bukan urutan eksekusi.** Tabel di bawah menomori *area dokumen*; urutan pengerjaan ditentukan oleh sekuens `W00`–`W22` pada blueprint. Keduanya sengaja berbeda pada satu titik: temporal identity (order 6) dan calendar/status (order 7) dieksekusi sebagai `W05` dan `W06`, yaitu **sebelum** source acquisition (order 4) dan resilience (order 5) yang dieksekusi sebagai `W07` dan `W08`. Alasannya, kontrak akuisisi menentukan simbol apa yang diambil dari provider; bila pemetaan provider-symbol belum temporal saat itu, pengambilan data memakai simbol yang aktif sekarang dan survivorship masuk di sumber, sebelum resolver universe sempat berperan. Membaca tabel ini sebagai urutan kerja akan membalik keputusan tersebut.

| Order | Owner document area | Strategy change | Done criteria |
|---:|---|---|---|
| 1 | `docs/market_data/README.md` dan `book/Terminology_and_Scope.md` | Kunci tujuan Weekly Swing, IDX Regular-Market EOD, intentional dataset start `2023-01-02`, development frontier, operational activation, dan raw/adjusted/eligibility terminology | Tidak ada ambiguity tentang market, horizon, dataset boundary, atau product boundaries |
| 2 | `book/Domain_Boundary_Invariants_LOCKED.md` | Tegaskan market-data facts versus watchlist alpha/policy | Market-data tidak memiliki entry/exit/ranking strategy |
| 3 | `book/Yahoo_Finance_Bootstrap_Source_Strategy.md` | Pertahankan Yahoo as bootstrap dan paid-provider future boundary | Tidak ada paid-provider backlog tersirat untuk fase sekarang |
| 4 | `book/Source_Data_Acquisition_Contract_LOCKED.md` | Tambahkan immutable observation envelope, provenance, stale/schema validation | Source payload dapat ditelusuri tanpa provider-specific leakage |
| 5 | `book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` | Kunci development-versus-operational freshness boundary, degraded state, retry/quarantine, dan no auto-repair | Development gap tidak dianggap incident; setelah activation, source failure tidak dapat menghasilkan silent readable data |
| 6 | `book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` dan `book/Symbol_Lifecycle_and_Mapping_Contract.md` | Kunci issuer/instrument/listing/provider-symbol temporal model | Historical universe survivorship-free |
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
| P0-01 direct historical scale repair | `OPEN` | `PriceScaleStretchRepairService` dan command `market-data:repair-price-scale-stretches --apply` masih meng-update `eod_bars`/`eod_bars_history`; draft repair migration sudah dinetralkan tetapi runtime path belum dihapus |
| P0-02 survivorship/current-active universe | `OPEN` | `TickerMasterRepository::getUniverseForTradeDate()` masih memfilter current active state sebelum interval listed/delisted |
| P0-03 synthetic event/factor behavior | `OPEN` | `CorporateActionDerivationService` masih membuat/mengubah `PRICE_RESCALE_UNCLASSIFIED` dengan `DERIVED_FROM_PRICE_SERIES`; seed migration baru sudah dihentikan tetapi service/repository/test lama belum diremediasi |
| P0-04 mixed/incoherent price basis | `OPEN` | runtime default masih `close`, `IndicatorVectorService` masih memiliki `adj_close` selection path, dan belum ada structural-adjusted factor-bound product builder |
| P1-01/P1-02 observation and config provenance | `PARTIAL` | schema foundation tersedia; writer, backfill, non-null seal enforcement, dan replay adoption belum ada |
| P1-03/P1-04/P1-05 coverage, ATR, ex-date | `OPEN` | owner rules sudah benar; dormancy exclusion code, indicator execution proof, dan event runtime behavior belum tertutup |
| P1-06/P1-07/P1-08 actual fields and revisions | `PARTIAL` | nullable schema target tersedia; source population, factor lifecycle, publication binding, dan correction behavior belum diimplementasikan |
| P1-09 zero-OHLC contradiction | `STRATEGY_CLOSED_IMPLEMENTATION_OPEN` | owner contracts konsisten menolak zero canonical price; end-to-end negative evidence belum lengkap |
| P1-10 semantic tests | `PARTIAL` | catalogs/matrix sudah dikoreksi dan schema guard ada; legacy repair/derived-action tests masih mengunci behavior yang ditolak |
| P1-11 activated operations | `PRE_ACTIVATION_OPEN` | development gap bukan incident, tetapi activation date, deployed schedule, alerts, dan consecutive-session proof belum ada |
| P1-12 schema/test parity | `PARTIAL` | SQLite V2 shape lulus; MariaDB clean-install/upgrade/backfill/enforcement evidence belum dijalankan |
| P1-13 `adj_close` close-fallback pada adapter | `OPEN` | `PublicApiEodBarsAdapter.php:983` memakai `$adjclose[$position] ?? ($quote['close'][$position] ?? null)`, sedangkan `book/Source_Mapping_Contract_LOCKED.md:27` menyatakan provider `adj_close` "has no close fallback semantics". Nilai `adj_close` tersimpan karena itu belum tentu `adj_close` provider, dan tidak dapat dipakai sebagai diagnostic yang jujur sampai fallback dihapus. Ditemukan saat penyusunan capability matrix `DOC-01` |
| P1-14 kolom `repaired_at` di luar kendali migration | `OPEN` | `market_data_price_scale_breaks.repaired_at`, `repaired_bar_count`, dan `repaired_history_row_count` ada di MariaDB dan dipakai oleh `CorporateActionDerivationService.php:53` serta `PriceScaleStretchRepairService.php:25`/`:217`/`:219`, tetapi tidak ada di `database/migrations/` maupun di mirror SQLite `tests/Support/UsesMarketDataSqlite.php`. Akibatnya 14 test gagal pada suite: `DerivationFillsRecordedActionTest` (9), `PriceScaleStretchRepairTest` (4), `PriceAdjustmentTest::test_derivation_records_a_factor_but_never_guesses_the_action_type` (1). Ini instance konkret dari P1-12 sekaligus pelanggaran governance: clean install tidak akan menghasilkan kolom yang dipakai runtime |

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

#### Kontrak yang sudah diverifikasi memadai — jangan dibuka ulang

Tercatat agar remediasi berikutnya tidak menulis ulang aturan yang sudah benar, dan tidak menciptakan pemilik kedua untuk satu aturan.

| Dokumen | Sudah menyatakan | Lokasi |
|---|---|---|
| `../../registry/Price_Adjustment_Contract_LOCKED.md` | `adj_close` bukan analytical product dan bukan per-row fallback; faktor aktif tidak boleh diturunkan dari `open`/`previous close`/`close`/besaran gap saja; faktor `1` atau faktor absen bukan bukti window bersih | `:13`, `:79`, `:85` |
| `../../book/Source_Mapping_Contract_LOCKED.md` | `adj_close` hanya nullable observation metadata tanpa semantik fallback; status tidak dapat disimpulkan dari provider response kosong | `:27`, `:33` |
| `../../registry/Volume_and_Turnover_Normalization_LOCKED.md` | actual traded value `NULL` ketika tidak source-backed; proxy `RAW close * RAW volume` bernama terpisah dan tidak pernah dikoalesikan dengan actual | `:15`–`:39` |
| `../../book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md` | `schema_config_impact`, `backfill_impact`, dan `test_ids` wajib per assignment row dengan `NONE` eksplisit — kewajiban kontribusi config dan schema sudah enforceable | `:20`–`:23` |

**Status register: `CLOSED` per 2026-08-03.** Seluruh `DOC-01`–`DOC-07` tertutup dengan kolom penutupan di atas. Satu temuan implementasi yang muncul selama penyusunan register dipindahkan ke backlog sebagai `P1-13`; ia bukan kekurangan dokumen.

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
- controlled recovery: `manual_file`;
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

> **`DOCUMENTATION_STRATEGY_READY` — owner contracts and implementation sequencing are ready; implementation conformance and operational validation are not claimed.**

Audit berikutnya harus menilai implementasi terhadap baseline ini. `AUDIT_FINAL_STATE.md` hanya boleh memberikan implementation/production relock setelah P0/P1 critical ditutup oleh schema/config/code/tests dan executed evidence yang konsisten.

Watchlist implementation dan performance proof tidak termasuk admission evidence untuk relock tersebut.
