# Market-Data Strategy Implementation Blueprint (LOCKED)

## Status and purpose

Status dokumentasi: **`DOCUMENTATION_STRATEGY_READY`**.

Documentation-strategy synchronization: **`PASS` (2026-08-07)** — seluruh 22 strategy area memiliki owner/assignment, dependency order eksplisit, dan tidak ada known cross-document contradiction pada baseline aktif.

Dokumen ini adalah owner untuk **urutan pembangunan** market-data setelah keputusan strategi dikunci. Ia menghubungkan owner contracts tanpa mengambil alih makna domain yang dimiliki masing-masing contract.

Status ini berarti strategi dokumentasi sudah cukup lengkap untuk menjadi panduan pembangunan. Status ini **tidak** berarti schema, migration, code, test, deployment, atau operasi saat ini sudah sesuai. Implementasi hanya boleh dinilai selesai setelah dibangun mengikuti blueprint ini dan diaudit kembali dengan executed evidence.

## Target yang dikunci

Platform menghasilkan data product EOD yang valid, decision-grade, point-in-time, reproducible, auditable, stabil, dan aman dibaca untuk saham IDX Regular Market.

Target ini memiliki batas berikut:

- market-data menyediakan fakta, lineage, quality state, data-usability state, dan versioned read product;
- Weekly Swing adalah initial consumer profile untuk prioritas field/frequency, bukan acceptance authority market-data;
- watchlist memiliki tradability thresholds, alpha, ranking, selection policy, entry/exit, dan keputusan trading;
- frequency utama adalah EOD; holding period consumer tidak mengubah kriteria kesiapan data;
- intentional dataset start adalah `2023-01-02`;
- last ingested date selama pembangunan adalah development data frontier, bukan dataset end;
- operational freshness baru wajib setelah operational activation marker ditetapkan;
- Yahoo Finance adalah bootstrap source yang sengaja dipilih untuk fase pembuktian manfaat;
- arsitektur tetap provider-neutral, tetapi proyek paid provider bukan current backlog;
- capability institutional yang tidak diperlukan untuk data product EOD awal bukan current scope.

Tidak satu pun state dokumentasi, implementasi, atau operasi market-data bergantung pada jumlah kandidat, hasil ranking, signal quality, expectancy, drawdown, turnover, profitabilitas, atau usefulness policy watchlist.

## Authority and conflict rule

Urutan authority:

1. owner contract yang paling spesifik di `book/`;
2. normative registry atau formula specification;
3. semantic DB/schema contract dan dictionary;
4. test contract dan golden fixture specification;
5. operational contract dan runbook;
6. implementation guide atau system companion;
7. audit, tracker, inventory, example, dan historical evidence.

Blueprint ini mengatur sequencing. Bila terdapat konflik makna, owner contract yang disebut pada setiap stage menang. Audit, code lama, migration lama, test lama, atau historical green proof tidak boleh mengalahkan corrected owner contract.

`Market_Data_Implementation_Conformance_Matrix_LOCKED.md` adalah companion wajib untuk memastikan setiap dokumen aktif, deliverable, test, dan evidence memiliki assignment. Matrix tersebut tidak menciptakan behavior baru.

`Market_Data_Implementation_Command_Protocol_LOCKED.md` mengontrol recommended stage command `MD-RUN Wxx` serta component commands `MD-STATUS`, `MD-EXEC`, `MD-AUDIT`, `MD-REMEDIATE`, `MD-REAUDIT`, dan `MD-CLOSE`. Current state-nya dicatat di `../audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`. Work order tidak boleh maju dengan command atau ledger state yang menentang protocol tersebut.

## Three completion states

Ketiga status berikut tidak boleh dicampur:

| State | Meaning | Required evidence |
|---|---|---|
| `DOCUMENTATION_STRATEGY_READY` | Scope, semantics, invariants, schema target, test oracle, dan implementation order sudah tidak ambigu | owner-contract consistency and cross-reference review |
| `IMPLEMENTATION_CONFORMANT` | Schema/config/code/tests mengikuti seluruh contract yang berlaku | code review, schema diff, targeted and full tests |
| `OPERATIONALLY_VALIDATED` | Implementasi conformant terbukti pada source, database, scheduler, failure, correction, replay, dan consumer path nyata | executed evidence and consecutive activated-session proof |

Dokumentasi saat ini hanya mengklaim state pertama. Dua state berikutnya harus ditentukan oleh audit implementasi yang terpisah.

## Mandatory data-product flow

```text
immutable configuration snapshot
  + temporal issuer/instrument/listing/provider-symbol mapping
  + verified market calendar/session/trading-status facts
  + temporal IDX-IC sector membership
  -> immutable source observation
  -> canonical RAW Regular-Market EOD
  -> verified revisioned corporate-action factors
  -> coherent STRUCTURAL_ADJUSTED / TOTAL_RETURN products
  -> actual/proxy daily market metrics
  -> versioned indicators, including sector-relative measures only when temporal sector inputs exist
  -> temporal coverage expectation/delivery facts
  -> quality/liquidity/status/event-risk/indicator-validity facts
  -> data-usability snapshot
  -> sealed readable publication
  -> versioned market-data read product
```

Dependency order is normative: temporal identity, calendar/status, and temporal sector membership must be available before acquisition/indicator consumers that depend on them. Configuration identity binds every output-affecting stage. Tidak ada stage yang boleh melompati observation provenance, temporal reference facts, publication binding, configuration snapshot, atau fail-safe quality gate.

## Executable work order (LOCKED)

Urutan `W00` sampai `W22` berikut adalah urutan kerja aktual ketika membangun sistem. Nomor stage pada bagian berikutnya adalah **contract-area ID** untuk traceability, bukan izin menunda fondasi bersama sampai nomor stage tersebut tercapai.

| Work order | Pekerjaan | Contract areas | Exit minimum |
|---|---|---|---|
| `W00` | Preflight dan implementation ledger | all | current code/schema/test/evidence baseline direkam; setiap dokumen aktif memiliki assignment di conformance matrix |
| `W01` | Kunci scope, boundary, dataset start, development frontier, activation semantics, dan non-goals | 1–2 | tidak ada ambiguity market, product, time boundary, atau kebocoran policy watchlist |
| `W02` | Kunci Yahoo bootstrap dan provider-neutral ports | 3 | Yahoo-specific behavior berhenti di adapter; current/future source decision eksplisit |
| `W03` | Bangun migration framework, additive schema skeleton, repository interfaces, reason registry, dan test harness skeleton | 4–21 foundations | clean-install/upgrade path tersedia untuk setiap feature berikut; belum ada nullable placeholder yang dianggap conformant |
| `W04` | Bangun immutable configuration snapshot dan semantic version bindings | 16 | semua writer berikut dapat menerima non-null config/reason/build identity sejak pertama kali dibuat |
| `W05` | Bangun temporal issuer/instrument/listing/symbol/provider mapping **serta temporal sector membership foundation** | 6 + 13 prerequisite | as-of/as-known identity, inactive-now-active-then, dan sector-reclassification fixture lulus sebelum indicator sector-relative dibangun |
| `W06` | Bangun calendar/session/status expectation | 7 | requested date dan expected-bar decision tidak memakai current-state guessing |
| `W07` | Bangun immutable source observations dan acquisition ports/adapters | 4 | setiap source outcome, termasuk empty/failure, memiliki immutable provenance |
| `W08` | Bangun resilience, retry/backoff/rate limit, manual recovery, quarantine, dan failure taxonomy | 5 | provider failure tidak menghasilkan synthetic data atau silent readable state |
| `W09` | Bangun import-only, canonical `RAW`, invalid-row, dedup/conflict, dan candidate persistence | 8 | source payload tidak langsung menjadi readable; canonical invariants dan lineage terbukti |
| `W10` | Bangun immutable publication state machine, manifest, seal, pointer, correction, supersession, dan no-in-place-rewrite | 9 | candidate/sealed/readable/superseded terpisah; failed correction tidak mengubah pointer |
| `W11` | Bangun verified corporate-action event/factor lifecycle dan anomaly-only detector | 10 | price break tidak dapat menjadi verified action/factor otomatis |
| `W12` | Bangun coherent `RAW`/`STRUCTURAL_ADJUSTED`/`TOTAL_RETURN` product engine | 11 | satu run memakai satu explicit factor-bound basis tanpa per-row fallback |
| `W13` | Bangun actual/proxy daily market metrics | 14 | actual field dan proxy berbeda nama, unit, basis, null state, lineage, dan hash |
| `W14` | Bangun deterministic indicator engine dan correction dependency graph | 15 | formula/seed/warm-up/nullability/precision exact; long-chain ATR dan correction propagation lulus |
| `W15` | Bangun temporal coverage expectation/delivery gate | 12 | denominator tidak menyusut karena provider absence, dormancy, zero volume, atau current active state |
| `W16` | Bangun explainable row-level data usability | 13 | quality/liquidity/status/event facts terpisah; compatibility `eligible` tidak memuat policy tradability/watchlist |
| `W17` | Bangun atomic versioned market-data read product dan freshness/readiness gateway | 17 | satu response terikat satu publication/config/factor/formula identity; no `MAX(date)`/mixed fallback |
| `W18` | Bangun exact-publication dan as-known replay | 18 | no future leakage; exact replay mereproduksi values, reasons, lineage, dan hashes |
| `W19` | Bangun daily/backfill/correction/replay operations, locking, observability, evidence export, dan recovery | 19 | command surface fail-safe, idempotent, resumable, observable, dan activation-aware |
| `W20` | Implementasikan supplemental session snapshot hanya bila feature state dinyatakan aktif | 17/19 optional | bila disabled, state/non-scope eksplisit; bila enabled, seluruh snapshot contract dan proof lulus |
| `W21` | Global schema/config/code/test/ops convergence, backfill, constraint hardening, dan full semantic proof | 20–21 | clean install + supported upgrade + backfill + MariaDB/test mirror + positive/negative suites lulus tanpa superseded oracle |
| `W22` | Independent implementation audit, pre-activation catch-up, operational validation, dan relock | 22 | tidak ada P0/P1 material; claim sesuai evidence dan activation state; watchlist performance bukan gate |

### Per-work-order implementation loop (LOCKED)

Setiap `Wxx` wajib menjalankan loop yang sama sebelum dapat ditutup:

1. baca owner contracts dan seluruh dokumen assignment pada conformance matrix;
2. catat affected schema, config, writer, reader, command, test, runbook, dan evidence;
3. **catat behavior lama yang ditolak stage ini**, lalu identifikasi setiap test, fixture, dan oracle yang masih menguncinya;
4. buat migration/constraint serta rollback/recovery plan yang aman;
5. implementasikan domain/service/repository/adapter tanpa legacy bypass;
6. lakukan required backfill atau translation untuk data lama;
7. **cabut atau ganti artifact pada langkah 3 dan tandai `SUPERSEDED` pada stage ini juga**, tidak ditunda ke stage 21;
8. **nyatakan capability boundary** setiap source, detector, resolver, dan validator yang disentuh stage ini pada owner contract-nya;
9. implementasikan positive, negative, failure, correction, concurrency, dan replay proof yang relevan;
10. perbarui command/runbook/observability/evidence format;
11. jalankan targeted tests, lalu supported full suite bila stage menyentuh shared path;
12. rekam evidence identity dan hasil pada implementation ledger;
13. lakukan contract-to-code review dan tandai stage hanya bila exit gate benar-benar terbukti.

### Langkah 3 dan 7 — retirement obligation (LOCKED)

Sebuah stage yang menolak suatu behavior mewarisi kewajiban mencabut proof yang masih menegaskan behavior tersebut. Kewajiban itu tidak boleh berpindah ke stage lain.

Bila sebuah test gagal karena stage ini mengubah behavior, hanya ada dua penyelesaian yang sah: **implementasi diperbaiki** karena test-nya benar, atau **test ditandai `SUPERSEDED` dan diganti** karena ia mengunci behavior yang baru saja ditolak. Menyesuaikan assertion agar suite kembali hijau tanpa menetapkan mana dari keduanya yang berlaku adalah closure palsu, dan menghapus satu-satunya penjaga yang tersisa.

### Langkah 8 — capability boundary obligation (LOCKED)

Setiap source, detector, resolver, dan validator wajib menyatakan pada owner contract-nya:

- apa yang **dapat dibuktikannya**;
- apa yang hanya **diagnostic** dan tidak boleh menjadi bukti;
- apa yang **tidak dapat dilihatnya** — batas sensitivitas, wilayah buta, prasyarat yang bila tidak terpenuhi membuatnya diam;
- **fail-safe state** ketika evidence tidak tersedia.

Kewajiban ini berbeda dari negative proof pada langkah 9. Negative proof membuktikan komponen **menolak input buruk dengan benar**. Capability boundary menyatakan **wilayah tempat komponen tidak menghasilkan sinyal sama sekali**, sehingga diamnya tidak pernah terbaca sebagai bukti ketiadaan.

Sebuah komponen yang lulus seluruh negative proof tetap dapat menyesatkan bila wilayah butanya tidak dinyatakan. Prinsip ini sudah berlaku untuk factor pada `../registry/Price_Adjustment_Contract_LOCKED.md`; langkah 8 memberlakukannya untuk seluruh komponen.

Dalam recommended stage-run mode, `MD-RUN Wxx` menjalankan implement/audit/remediate/re-audit sampai `Wxx PASS`, lalu berhenti dan memberikan exact command `MD-RUN W(next)`. Component commands tetap tersedia untuk controlled manual operation. Pada kedua mode, successor hanya admitted setelah ledger menandai predecessor `CONFORMANT`.

Pembuatan table/column/config/test skeleton pada `W03` tidak menutup contract area terkait. Ia hanya menghilangkan dependency teknis. Conformance baru dapat diberikan setelah writer, reader, constraints, backfill, semantic tests, dan evidence ikut selesai.

## Contract-area closure requirements

Stage di bawah adalah ID cakupan strategi. Satu stage hanya boleh ditutup bila owner contract, schema/config impact, code, tests, runbook, dan evidence requirement untuk area tersebut sudah diselaraskan melalui work order di atas. Pekerjaan boleh dipersiapkan secara additive, tetapi tidak boleh dinyatakan complete hanya karena skeleton, migration, atau test name sudah tersedia.

### Stage 1 — scope, terminology, and time boundary

Owner:

- `../README.md`
- `Terminology_and_Scope.md`

Build outcome:

- hanya IDX Regular-Market EOD yang menjadi canonical market scope;
- `2023-01-02` diperlakukan sebagai intentional dataset start;
- development frontier dan operational activation dipisahkan;
- `RAW`, `STRUCTURAL_ADJUSTED`, `TOTAL_RETURN`, coverage, quality, liquidity, event risk, dan eligibility memiliki satu arti.

### Stage 2 — domain boundary

Owner: `Domain_Boundary_Invariants_LOCKED.md`.

Build outcome: market-data tidak mengimplementasikan atau menyamarkan watchlist alpha, ranking, entry/exit, sizing, portfolio action, atau execution behavior.

### Stage 3 — Yahoo bootstrap and provider boundary

Owner: `Yahoo_Finance_Bootstrap_Source_Strategy.md`.

Build outcome:

- Yahoo Finance dipakai sebagai bootstrap source secara eksplisit;
- canonical contract tidak bergantung pada provider-specific field names;
- limitation, provenance, stale/schema checks, licensing disclosure, dan failure state tetap terlihat;
- paid provider evaluation tetap deferred sampai evidence manfaat atau kebutuhan SLA/licensing/field authority membenarkannya.

### Stage 4 — immutable source observations

Owner:

- `Source_Data_Acquisition_Contract_LOCKED.md`
- `Source_Mapping_Contract_LOCKED.md`

Build outcome: setiap request/file/response outcome memiliki immutable observation envelope, timestamps, provider/mapping identity, sanitized request identity, schema fingerprint, adapter version, payload hash/reference, outcome, reason, dan supersession lineage.

### Stage 5 — source resilience and freshness boundary

Owner: `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`.

Build outcome: retry, backoff, rate limiting, stale/wrong-date/schema quarantine, degraded state, and no-silent-readable-data behavior mengikuti development/activation boundary. Source failure tidak boleh memicu synthetic repair.

### Stage 6 — temporal identity, symbol/provider mapping, and sector-reference foundation

Owner:

- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Symbol_Lifecycle_and_Mapping_Contract.md`
- `Sector_Classification_Contract_LOCKED.md` — cross-cutting prerequisite for sector-relative analytical products; consumed again by Stage 13/read-product facts

Build outcome: issuer, instrument, listing, exchange symbol, dan provider symbol adalah identity yang berbeda dan temporal. Historical universe tidak membaca current `is_active`, current symbol, atau future mapping. `IDX-IC` sector membership juga temporal/as-known: reclassification menutup interval lama dan membuka interval baru, dan current sector tidak boleh bocor ke historical indicator window.

### Stage 7 — calendar, session, and temporal trading status

Owner:

- `Market_Calendar_Requirements_Contract.md`
- `Trading_Status_Source_Contract_LOCKED.md`

Build outcome: expected-bar decision untuk T berasal dari completed Regular-Market session, listing membership pada T, dan status evidence as-of. Unknown status tetap unknown dan tidak boleh menghilang dari denominator.

### Stage 8 — canonical RAW bars and invalid observations

Owner:

- `Canonicalization_Contract_EOD_Bars.md`
- `EOD_Bars_Contract.md`
- `Invalid_Bar_Storage_Policy_LOCKED.md`

Build outcome: canonical `RAW` OHLCV memiliki mapping tunggal, positive-price invariants, duplicate/conflict policy, missing-versus-invalid separation, source lineage, dan tidak memakai zero placeholder atau provider `adj_close` sebagai RAW close.

### Stage 9 — immutable publications, history, and correction

Owner:

- `Canonical_Row_History_and_Versioning_Policy_LOCKED.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `Correction_Lifecycle_Safety_Contract.md`
- publication, manifest, pointer, and replacement contracts in this folder

Build outcome: observation, canonical snapshot, artifact, factor, manifest, seal, dan published history tidak berubah in-place. Correction selalu membuat revision/publication baru dan pointer switch yang atomik.

### Stage 10 — corporate-action event and factor lifecycle

Owner:

- `Corporate_Action_and_Adjustment_Policy.md`
- `Corporate_Action_Impact_Flags_Contract.md`
- `../registry/Corporate_Action_Type_Registry_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
- `../registry/Price_Scale_Break_Detection_LOCKED.md`

Build outcome: event identity/revision, verification state, known/effective time, ex/cum/record/payment dates, source evidence, factor set, dan contamination range eksplisit. Price anomaly hanya candidate; tidak boleh menjadi verified event/factor atau izin repair otomatis.

### Stage 11 — coherent analytical price products

Owner:

- `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`

Build outcome:

- `RAW` tetap immutable;
- profil indicator teknikal EOD awal memakai satu versioned `STRUCTURAL_ADJUSTED` OHLC product per run;
- volume disesuaikan inversely hanya ketika action semantics mengharuskannya;
- `TOTAL_RETURN` adalah produk terpisah;
- tidak ada per-row fallback ke `close` atau provider `adj_close`.

### Stage 12 — coverage expectation and delivery

Owner:

- `Coverage_Universe_Definition_LOCKED.md`
- `Coverage_Gate_Enforcement_Contract_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Coverage_Edge_Cases_Contract_LOCKED.md`

Build outcome: denominator berasal dari temporal listing/session/status expectation. Provider absence, dormancy, zero volume, liquidity, dan current active state tidak boleh menyusutkan denominator. Unknown expectation tetap terlihat dan fail-safe.

### Stage 13 — explainable data-usability facts

Owner:

- `EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `Eligibility_Partial_Data_Behavior_LOCKED.md`

Cross-cutting input:

- `Sector_Classification_Contract_LOCKED.md` — temporal membership foundation sudah harus conformant pada `W05`; Stage 13 hanya membawa sector fact/unknown state ke publication-bound explainability/read product dan **tidak** menunda pembentukan membership sampai `W16`.

Build outcome: universe membership, expectation, delivery, canonical quality, liquidity, status, event risk, sector-reference state bila relevan, data-usability decision, dan seluruh reason codes disimpan terpisah. Compatibility `eligible` hanya berarti data usability. Liquidity/tradability preference dan event-avoidance policy tidak boleh menjadi upstream decision.

### Stage 14 — actual and proxy market metrics

Owner:

- `Market_Daily_Metrics_Contract.md`
- `../registry/Volume_and_Turnover_Normalization_LOCKED.md`

Build outcome: source-backed actual traded value/trade count tidak dicampur dengan proxy. Proxy diberi nama dan unit eksplisit serta menggunakan `RAW close × RAW volume`.

### Stage 15 — deterministic indicators

Owner:

- `EOD_Indicators_Contract.md`
- `Indicator_Nullability_And_OHLCV_Gap_Contract.md`
- `Indicator_Recompute_Source_Scope_Contract.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
- `../indicators/Indicator_Computation_Specification.md`
- `../registry/Indicator_Registry_Baseline_LOCKED.md`

Build outcome: formula, basis, precision, warm-up, nullability, registry/formula version, and correction impact eksplisit. Wilder ATR memakai stable seed dan recursive state dari dataset/listing boundary, bukan sliding-window reseed.

### Stage 16 — full immutable configuration snapshot

Owner:

- `../registry/Platform_Config_Registry_LOCKED.md`
- `../ops/Config_Change_Protocol_LOCKED.md`

Build outcome: seluruh output-affecting keys diserialisasi secara deterministic dan diikat dengan non-null snapshot ID/hash yang sama pada run, artifacts, publication, manifest, seal, dan replay.

### Stage 17 — versioned market-data consumer read product

Owner:

- `Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
- `Consumer_Readability_Decision_Table_LOCKED.md`
- `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`

Build outcome: consumer menerima satu publication-bound, versioned market-data DTO berisi identity, dates/freshness, RAW market facts, analytical product identity, indicators, data-usability dimensions/reasons, and lineage references. Consumer tidak membaca internals, `MAX(date)`, mixed publication, atau implicit fallback. Contract test membuktikan shape/semantics/binding, bukan hasil policy watchlist.

### Stage 18 — exact and as-known replay

Owner:

- `Replay_Verification_Contract_LOCKED.md`
- `../backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md`
- `../backtest/Historical_Replay_and_Data_Quality_Backtest.md`

Build outcome:

- exact publication replay mereproduksi publication/config/factor yang dibekukan;
- as-known replay memakai knowledge cutoff untuk identity, calendar/status, observations, actions/factors, config, dan publication state;
- future/current master state tidak boleh bocor ke historical result.

### Stage 19 — activation-aware operations

Owner:

- `../ops/Scheduling_and_Locking_Contract_LOCKED.md`
- `../ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
- `../ops/Performance_SLO_and_Limits_LOCKED.md`
- `../ops/Failure_Playbook_LOCKED.md`
- `../ops/Incident_Classification_and_Response_Matrix_LOCKED.md`

Build outcome: import/promote separation, idempotent retry/backfill, atomic locks, stale alerts, held/degraded states, and consecutive-session SLO berlaku sesuai activation marker. Pre-activation development gap bukan incident.

### Stage 20 — global schema and migration convergence

Owner:

- `../db/Database_Schema_Contracts_MariaDB.md`
- `../db/MARKET_DATA_DICTIONARY.md`
- `../db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md`
- `../db/Migration_Policy_LOCKED.md`

Build outcome: schema yang dibangun secara additive sejak `W03` mencapai global convergence. MariaDB clean install plus forward migrations, supported upgrade, backfill, enforcement, test mirror, repositories, and dictionary memiliki meaning yang sama untuk observation, temporal identity, revisions, actual fields, factors, snapshots, lineage, readiness, dan artifact bindings. Stage ini bukan pertama kali schema dibuat; ini adalah closure lintas-feature yang menghapus transitional null/bypass dan membuktikan upgrade nyata.

### Stage 21 — semantic proof implementation and closure

Owner:

- `../tests/Contract_Test_Matrix_LOCKED.md`
- `../tests/Golden_Fixture_Catalog_LOCKED.md`
- `../tests/Negative_Test_Catalog_LOCKED.md`
- `../tests/Test_Coverage_Closure_Contract_LOCKED.md`

Build outcome: test specifications yang disiapkan sejak `W03` dan dikembangkan pada setiap work order dijalankan melalui production path untuk membuktikan corrected market semantics, termasuk inactive-now-active-then identity, provider outage without denominator shrink, candidate-only unexplained breaks, coherent factors/products, actual-versus-proxy metrics, >100-session ATR chain, historical correction propagation, config-as-known isolation, fresh/stale atomic reads, dan anti-survivorship replay. Legacy test yang mengharapkan behavior terlarang harus dihapus atau diganti.

### Stage 22 — implementation audit and relock

Owner:

- `../audit/reports/AUDIT_FINAL_STATE.md`
- audit tracker/proof pack hanya sebagai evidence/index, bukan behavioral authority

Build outcome: audit baru memeriksa schema/config/code/tests/runtime terhadap seluruh stage 1–21. `IMPLEMENTATION_CONFORMANT` atau production relock hanya boleh diberikan bila tidak ada P0/P1 market-data material, full semantic suite hijau, migration proof tersedia, dan operational evidence sesuai activation state. Watchlist implementation atau performance proof bukan admission gate.

## Documentation closure checklist

Dokumentasi strategi dinyatakan ready karena telah menetapkan:

- satu scope market, initial consumer profile, dan dataset boundary tanpa menjadikan consumer policy sebagai readiness gate;
- satu source bootstrap decision dan future-provider boundary;
- temporal identity, calendar, status, dan point-in-time replay;
- immutable observation, publication, correction, and seal semantics;
- verified corporate-action/factor lifecycle;
- coherent price products dan deterministic indicators;
- pemisahan coverage, quality, liquidity, event risk, dan eligibility;
- actual-versus-proxy metric semantics;
- full config and source provenance;
- stable consumer read contract;
- activation-aware operations;
- target schema families dan semantic fixtures;
- urutan pembangunan serta evidence gate untuk audit berikutnya.

## Implementation handoff rule

Mulai dari baseline ini:

1. code tidak boleh dijadikan alasan untuk mengubah strategi agar legacy behavior terlihat benar;
2. bila implementasi menemukan ambiguity, perbaiki owner contract terlebih dahulu dan lakukan impact review;
3. setiap pull request harus menyebut stage yang dikerjakan dan owner contracts yang dipenuhi;
4. implementation tracker melaporkan `NOT_STARTED`, `IN_PROGRESS`, `BLOCKED`, atau `CONFORMANT`, bukan mengubah meaning contract;
5. green test hanya diterima bila oracle-nya sesuai corrected strategy;
6. production-ready claim menunggu audit stage 22 dan tidak ikut terbit bersama dokumentasi ini.

## Change control

Perubahan terhadap scope, price-product meaning, temporal identity, corporate-action authority, coverage denominator, indicator formula/state, configuration binding, consumer DTO, atau activation semantics adalah strategy change. Perubahan tersebut wajib:

1. mengubah owner contract;
2. memperbarui blueprint impact bila sequencing berubah;
3. memperbarui schema/test/ops specifications yang terdampak;
4. mencabut implementation conformance lama sampai audit ulang selesai.

Provider replacement yang hanya mengubah adapter dan tetap memenuhi source observation contract bukan strategy change. Pembelian provider, licensing agreement, atau multi-feed operation tetap future decision dan bukan kewajiban fase sekarang.
