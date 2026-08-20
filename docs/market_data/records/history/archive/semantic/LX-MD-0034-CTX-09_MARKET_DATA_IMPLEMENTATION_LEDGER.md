# Legacy Semantic Extract — LX-MD-0034-CTX-09

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L1915-L3183`
- Extract body SHA1: `5DC328BB5D8F457D1D6D80F2CDA10F5775DC513A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Tahap 2 — `F-045` ditutup, 2026-08-12

Tahap ini hanya menutup sisi ekspos bukti coverage. Ia tidak mengubah evaluator, jalur tulis
coverage, guard lineage tahap 3, maupun korpus yang menjadi target backfill tahap 5.

### Yang dikerjakan

`MarketDataEvidenceExportService::buildCoverageState()` kini membawa empat field yang sebelumnya
tersimpan tetapi hilang dari evidence pack:

- `coverage_expectation_unknown_count`;
- `coverage_bar_not_expected_count`;
- `coverage_universe_hash`;
- `coverage_excluded_sample_json`.

Nilai numerik diekspor sebagai integer. Hash dipertahankan sebagai string. Sampel excluded didekode
menjadi struktur JSON evidence; `NULL` tetap `NULL`, sehingga "belum pernah diukur" tidak berubah
menjadi sampel kosong yang tampak telah diukur.

Registry `RUN_COVERAGE_STORAGE_EXPORT_PATHS` menyatakan jalur payload bernama sama untuk setiap kolom
`coverage_*` pada `eod_runs`. Guard membaca skema MariaDB kanonis dan mirror SQLite, menuntut keduanya
identik, lalu membandingkan seluruh kolom coverage dengan registry tersebut. Alias tidak dapat lagi
menyamarkan kolom sumber yang tidak dibaca. Test ekspor fungsional memastikan setiap nilai tersimpan
mencapai key-nya sendiri pada `coverage_context`; alias kompatibilitas berada di luar registry.

### Surface konformansi Tahap 2

Klaim penutupan Tahap 2 dibatasi pada surface berikut:

- `MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS`;
- `MarketDataEvidenceExportService::buildCoverageState()` dan helper null-semantics
  `decodeNullableJsonArray()`;
- assertion empat field pada `MarketDataEvidenceExportServiceTest`;
- dua guard storage-to-export pada `EvidenceExportCompletenessStaticGuardTest`;
- guard current-state dan kemurnian scope pada `AuditDocsSynchronizationStaticGuardTest`.

Perubahan di luar surface tersebut tidak dihitung sebagai implementasi maupun exit evidence Tahap 2.

### Bukti penutupan awal — SUPERSEDED OLEH REVALIDASI FINAL DI BAWAH

Angka berikut dipertahankan sebagai bukti eksekusi awal, bukan baseline test current. Baseline
otoritatif Tahap 2 berada pada bagian "Penutupan residu oracle tahap 2".

- `MarketDataEvidenceExportServiceTest`: **5 test, 165 asersi**, lulus. Fixture bernilai membuktikan
  keempat field mencapai `run_summary.coverage` dan `coverage_context`; fixture legacy membuktikan
  empat nilai yang tidak pernah diukur tetap `NULL`.
- `EvidenceExportCompletenessStaticGuardTest`: **9 test, 140 asersi**, lulus. Guard baru
  membandingkan seluruh kolom coverage tersimpan dengan registry jalur ekspor.
- Filter seluruh kelas evidence-export: **18 test, 411 asersi**, lulus.
- Run yang sudah tersedia, `72922` untuk 2026-07-28, menyediakan bukti tersimpan: hash
  `d7e7a235852d9b9b1b366fe5c127cb07611f1a272e9be291af25a49d0a5380a7`, 25 item excluded,
  `coverage_bar_not_expected_count=83`, dan `coverage_expectation_unknown_count=0`.
- Aksi Tahap 2 atas run tersebut hanya `market-data:evidence:export --run_id=72922`; hasilnya
  `ADMITTED_COMPLETE` dan keempat nilai diekspor. Penciptaan, seal, dan lifecycle run bukan bagian
  Tahap 2 serta tidak dipakai sebagai exit evidence.
- Suite market-data penuh setelah pembersihan residu: **1.409 test, 9.801 asersi**, seluruhnya lulus.

### Penutupan residu oracle tahap 2, 2026-08-12

Audit ulang sebelum masuk tahap berikut menemukan dua kelemahan pada pembuktian, bukan pada jalur
runtime. Fixture bernilai memakai angka yang sama untuk `coverage_bar_not_expected_count` dan
`coverage_expectation_unknown_count`, sehingga pertukaran sumber kedua field itu dapat lolos.
Selain itu, guard telah membuktikan kesamaan schema, registry, dan nama key payload, tetapi belum
secara generik membuktikan bahwa ekspresi tiap key membaca kolom tersimpan dengan nama yang sama.

Fixture kini memakai sentinel berbeda (`104` dan `105`) dan memeriksa nilai persis pada
`run_summary.coverage` serta `coverage_context`. Guard kedua menginspeksi
`buildCoverageState()` untuk seluruh 17 jalur registry dan menolak ekspresi yang membaca nol, lebih
dari satu, atau field `coverage_*` lain. Dengan demikian penambahan key saja, alias silang, dan
pertukaran dua field numerik tidak dapat membuat Tahap 2 tampak lulus.

Revalidasi final: `MarketDataEvidenceExportServiceTest` **5 test, 169 asersi**;
`EvidenceExportCompletenessStaticGuardTest` **7 test, 170 asersi**; filter evidence-export
**16 test, 445 asersi**; filter sinkronisasi schema/migration **11 test, 555 asersi**; seluruhnya lulus.
Ekspor runtime sementara run `72922` kembali menghasilkan `ADMITTED_COMPLETE`, keempat key hadir,
dan nilai tetap `83`, `0`, hash
`d7e7a235852d9b9b1b366fe5c127cb07611f1a272e9be291af25a49d0a5380a7`, serta 25 item excluded.
Suite penuh final: **1.415 test, 9.875 asersi**, seluruhnya lulus. Putaran final memurnikan oracle
dan batas audit Tahap 2 tanpa mengubah perilaku produksi.

### Penutupan residu histori/current-state tahap 2, 2026-08-12

Sapuan seluruh `docs/market_data` membuktikan semua kemunculan `F-045` berada hanya di ledger
kanonis. Barisnya dikeluarkan dari tabel `Active findings` sesuai aturan current-state. Klaim awal
2026-08-11 dipertahankan, tetapi diberi marker `HISTORICAL, SUPERSEDED` tepat pada bloknya; peta akar
penyebab lama juga dinyatakan historical snapshot. Tabel urutan kini menandai Tahap 2 `SELESAI`,
dan bukti awal dengan hitungan test lama dinyatakan superseded oleh baseline final.

`AuditDocsSynchronizationStaticGuardTest` mengunci keadaan ini secara struktural: `F-045` dilarang
masuk daftar open, dilarang memiliki baris di tabel active findings, dilarang berada satu baris
dengan verdict `OPEN/PARTIAL`, marker historis wajib menempel pada klaim asal, status urutan wajib
`SELESAI`, verdict final wajib `CLOSED`, dan controller wajib menunjuk Tahap 3. Guard dokumentasi:
**5 test, 274 asersi**; gabungan filter audit-doc/evidence-export: **21 test, 719 asersi**; seluruhnya
lulus. Suite penuh final setelah guard histori: **1.415 test, 9.875 asersi**. Penutupan ini hanya
mengubah dokumentasi current-state dan guard dokumentasinya.

### Status

Exit criterion tahap 2 terpenuhi: keempat field muncul di payload ekspor dan guard menggagalkan
kolom coverage tersimpan tanpa jalur ekspor maupun alias silang. `F-045` **CLOSED**.

Tahap 2 **selesai**. Tahap berikut yang diizinkan adalah tahap 3 — guard jalur tulis
`F-007a`, `F-026a`, `F-017a`, dan `F-018a`. Backfill tahap 5 belum boleh dikerjakan.

## Tahap 3 — Bekukan populasi cacat — SELESAI 2026-08-13

Tahap ini hanya menutup subtemuan guard `F-007a`, `F-026a`, `F-017a`, dan `F-018a`.
Finding induk `F-007`, `F-026`, `F-017`, dan `F-018` tetap berada pada daftar open karena
subtemuan korpus `b` belum dikerjakan. Tidak ada klaim bahwa nilai `NULL` historis sudah berkurang.

### Koreksi catatan sebelum implementasi

Catatan 2026-08-12 yang menyebut “penulisnya sudah benar” hanya benar untuk sebagian jalur produsen.
Audit seluruh write topology menemukan batas persistensi belum menolak baris tak lengkap; copy,
snapshot, dan restore masih dapat membawa `NULL`; produsen eligibility belum menulis
`event_risk_state`, mengubah himpunan alasan kosong yang bermakna menjadi `NULL`, dan membiarkan
quality tidak eksplisit saat bar tidak tersedia. Koreksi ini dicatat agar audit berikutnya tidak
mengulang asumsi bahwa happy path produsen membuktikan semua jalur tulis.

### Field yang dibekukan

| Subtemuan | Artefak | Field yang wajib pada setiap write yang relevan |
| --- | --- | --- |
| `F-007a` + `F-026a` | `eod_bars`, `eod_bars_history` | `listing_id`, `source_observation_id`, `canonicalization_version`, `price_product_code`, `quality_state` |
| `F-017a` | bukti coverage pada `eod_runs` | `coverage_expected_count`, `coverage_bar_not_expected_count`, `coverage_expectation_unknown_count`, `coverage_delivered_count`, `coverage_delivered_valid_count` |
| `F-018a` | `eod_eligibility`, `eod_eligibility_history` | `universe_membership_state`, `bar_expectation_state`, `delivery_state`, `canonical_quality_state`, `liquidity_state`, `temporal_status_state`, `event_risk_state`, `eligibility_reasons_json` |

Nilai integer `0` adalah bukti coverage yang sah. Key yang tidak ada, `NULL`, atau string kosong
adalah bukti yang tidak pernah direkam dan ditolak; tidak ada `?? 0` yang menyamarkan ketiadaan
pengukuran. Pada universe kosong, evaluator mengekspor `coverage_expectation_unknown_count = 0`
karena nilai itu sudah dihitung dari set kosong, bukan karena default.

### Batas persistensi dan lifecycle

`EodArtifactRepository::REQUIRED_CANONICAL_BAR_WRITE_FIELDS` dan
`EodArtifactRepository::REQUIRED_ELIGIBILITY_WRITE_FIELDS` menjadi daftar positif tunggal.
`assertCompleteBarRows()` dipanggil oleh `replaceBars`, `upsertBarsPartial`,
`ensureBarsHistoryFromCurrentTradeDate`, `replaceBarsHistoryFromPublication`,
`snapshotPublicationFromCurrentTables`, dan `promotePublicationHistoryToCurrent`.
`assertCompleteEligibilityRows()` dipanggil oleh `replaceEligibility`, snapshot, dan promote.
Semua validasi berjalan sebelum mutasi, dan snapshot/promote dibungkus transaksi sehingga kegagalan
fact eligibility menggulung balik penyalinan bar yang sudah dimulai.

`EodRunRepository::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS` dan
`assertCompleteCoverageTelemetry()` menolak pembaruan coverage bila kelima bukti belum lengkap,
termasuk pembaruan parsial terhadap run legacy yang masih kehilangan bukti. Pembaruan telemetry
non-coverage tidak ikut digerbangi. `MarketDataPipelineService::measuredCoverageCount()`
mempertahankan nol terukur dan meneruskan ketiadaan sebagai `NULL` agar repository menolaknya.

`EodEligibilityBuildService` sekarang selalu menulis delapan fakta: quality menjadi `UNAVAILABLE`
ketika bar tidak ada dan `UNKNOWN` ketika bar legacy kehilangan quality; event risk diproyeksikan
sebagai `FLAGGED`, `CLEAR`, atau `UNKNOWN`; himpunan alasan kosong disimpan sebagai `[]`.
Fakta event dan liquidity tetap tidak dibaca oleh `EligibilityDecisionService`, sehingga tahap ini
tidak menambahkan kebijakan tradability atau selection.

### Bukti exit gate dan anti-bypass

`StageThreeWriteCompletenessGuardTest` memberi oracle negatif terpisah untuk kelima field bar,
kelima field coverage, dan delapan field eligibility. Setiap penghilangan key ditolak dan state
tersimpan sebelumnya tetap utuh. Test perilaku tambahan menutup partial upsert, current-to-history,
history-to-history, snapshot, dan promote; kegagalan snapshot/promote terbukti atomik.

`StageThreeEligibilityProducerTest` adalah unit test murni tanpa database, sedangkan seluruh bukti
persistensi memakai SQLite nyata tanpa mock repository. Pemisahan ini mengikuti
`LifecycleProofIsNotMockedTest`: state database tidak boleh dibuktikan oleh mock yang menulis state
itu sendiri. Targeted proof final sebelum penutupan dokumentasi: **34 test, 241 asersi**, seluruhnya
lulus (`StageThreeWriteCompletenessGuardTest`, `StageThreeEligibilityProducerTest`,
`CoverageGateEvaluatorTest`). Suite market-data final dicatat setelah guard dokumentasi di bawah.

Guard current-state/histori `AuditDocsSynchronizationStaticGuardTest`: **6 test, 317 asersi**,
seluruhnya lulus. Suite market-data final sesudah guard dokumentasi dan audit anti-mock:
**1.443 test, 10.096 asersi**, seluruhnya lulus dengan exit code 0.

### Kemurnian scope

Tidak ada migrasi schema, perubahan nullability, query backfill, re-ingest, update korpus produksi,
atau pengisian retroaktif. Tidak ada `operational_start_date`, fixture replay independen, terms IDX,
atau gate stage-21 yang dikerjakan. Secara khusus, tahap ini **tidak melakukan backfill tahap 5**:
baris legacy tetap merupakan bukti historis apa adanya, dan populasi cacat hanya dibekukan agar tidak
bertambah melalui jalur aplikasi yang tercatat.

### Status

Exit criterion tahap 3 terpenuhi per field dan per jalur lifecycle. `F-007a`, `F-026a`, `F-017a`,
dan `F-018a` **CLOSED**. `F-007`, `F-026`, `F-017`, dan `F-018` tetap **OPEN** untuk subtemuan `b`.

Tahap 3 **selesai**. Tahap berikut yang diizinkan adalah tahap 4, yang terlebih dahulu membutuhkan
keputusan pemilik tentang makna `RAW`; tidak ada kode atau mutasi data tahap 4 yang boleh dijalankan
sebelum keputusan itu tercatat.

## 2026-08-13 — CURRENT AUTHORITATIVE SEQUENCE mulai Tahap 4

Bagian ini menggantikan **hanya** placeholder Tahap 0 dan urutan Tahap 4–9 pada blok perencanaan
2026-08-12. Capaian Tahap 1–3 tidak diubah. Tujuan temuan juga tidak dihapus: yang dikoreksi adalah
penempatan, unit penyelesaian, dan bukti exit agar tidak ada tahap yang baru dapat disebut selesai
oleh keluaran tahap sesudahnya.

### Hasil analisis ulang tahap lama

| Tahap lama | Masalah urutan | Koreksi |
| --- | --- | --- |
| 4 — deklarasi operasi + backfill | activation checklist hanya mungkin setelah deployment siap; memaksanya selama pembangunan akan mengubah development frontier menjadi klaim operasional palsu | keluarkan `F-021` dari burn-down pembangunan dan pecah menjadi gate operasional `O1`/`O2` |
| 5 — backfill silsilah | empat keluarga field bukan nilai mekanis: lineage bar harus lahir dari re-ingest, coverage harus diukur ulang, eligibility harus dibangun ulang, dan identitas produk menunggu `F-039` | dahulukan keputusan/authority; lakukan satu kampanye rekonstruksi lifecycle pada Tahap 8, bukan `UPDATE ... SET` atas fakta lama |
| 6 lalu 7 — fixture/replay proof | Tahap 6 lama sudah meminta replay berjalan, sehingga Tahap 7 hanya menamai ulang keluarannya | Tahap 9 hanya meng-author fixture independen; Tahap 10 baru mengeksekusi dan menyimpan proof |
| 8 — terms IDX | corporate-action terms dan exchange tier adalah dua authority berbeda; merekam terms dan menerapkannya ke seri juga dua klaim berbeda | pisahkan akuisisi corporate-action pada Tahap 6, tier pada Tahap 7, dan penerapan keduanya pada rekonstruksi Tahap 8 |
| 9 — gate stage-21 | gate implementasi dicampur dengan consecutive activated-session proof yang hanya dapat lahir setelah activation | Tahap 11 menutup `F-023a` dengan evaluasi jujur; `F-023b` tetap gate operasional `O3` |

### Pemecahan finding yang berlaku

| Finding | Potongan | Aturan closure |
| --- | --- | --- |
| `F-039` | `F-039a` keputusan pada Tahap 4; `F-039b` penerapan pada Tahap 8 | parent tetap `OPEN` setelah Tahap 4 dan baru dapat ditutup setelah cabang keputusan terbukti pada korpus aktif |
| `F-038` | keputusan dan enforcement fail-closed diselesaikan pada Tahap 5 | `CLOSED`; implementasi cabang penolakan dan negative oracle selesai pada tahap yang sama |
| `F-010` + `F-027` | `a` perekaman authority pada Tahap 6; `b` penerapan pada Tahap 8 | parent tetap `OPEN/PARTIAL` setelah Tahap 6; bukti tersimpan tidak sama dengan bukti mencapai output |
| `F-011` | `F-011a` perekaman tier pada Tahap 7; `F-011b` penerapan pada Tahap 8 | parent tetap `OPEN/PARTIAL` setelah Tahap 7 |
| `F-007` `F-026` `F-017` `F-018` | `a` guard selesai pada Tahap 3; `b` rekonstruksi pada Tahap 8 | parent tetap `OPEN` sampai current-authoritative population lulus |
| `F-030` | `F-030a` authoring fixture pada Tahap 9; `F-030b` eksekusi pada Tahap 10 | authoring tidak boleh mengklaim replay proof; `F-020` dan `F-024` hanya ditutup oleh Tahap 10 |
| `F-023` | `F-023a` gate implementasi pada Tahap 11; `F-023b` evidence operasi pada `O3` | Tahap 11 tidak menunggu `O3` dan tidak mengubah pre-activation menjadi pass operasional |
| `F-021` | `F-021a` deklarasi pada `O1`; `F-021b` propagasi pada `O2` | keduanya di luar burn-down pembangunan dan baru dimulai setelah activation prerequisites terbukti |

### Aturan urutan aktif

1. Satu tahap hanya mengklaim fakta yang dihasilkannya sendiri. Dependensi boleh berasal dari tahap
   sebelumnya, tidak pernah dari tahap sesudahnya.
2. Tahap keputusan selesai ketika keputusan pemilik tercatat beserta konsekuensi cabangnya. Ia tidak
   mengklaim implementasi cabang tersebut sudah selesai.
3. Tahap authority selesai ketika fakta otoritatif tersimpan dan dapat diaudit. Ia tidak mengklaim
   fakta tersebut sudah mencapai output terbit.
4. Fakta lineage/evidence lama tidak boleh diisi dengan tebakan. Korpus conformant dibentuk melalui
   re-ingest/recompute/republication normal, bukan pembaruan in-place atas artefak lama.
5. Snapshot tersegel dan run lama tetap dipertahankan sebagai history. Nilai `NULL` legacy tidak
   menentukan current conformance; pengukuran current hanya mengikuti
   `eod_current_publication_pointer` menuju publikasi tersegel dan owning run yang aktif.
6. Hitungan populasi selalu diukur saat tahap berjalan. Angka historis seperti 72.776 atau 756.329
   tidak boleh dijadikan target tetap.
7. Tidak ada tahap berikut yang boleh dimulai sebelum seluruh exit criterion tahap aktif lulus.

### Urutan pembangunan yang berlaku

| # | Tahap | Menutup | Dinyatakan selesai bila |
| --- | --- | --- | --- |
| 4 | Keputusan makna `RAW` — **SELESAI 2026-08-13** | `F-039a` | pemilik mempertahankan Yahoo sebagai bootstrap primary EOD source; `RAW` ditetapkan sebagai quote OHLCV tervalidasi yang tidak ditransformasi secara ekonomi oleh platform, dengan keadaan skala source sebagai fakta terpisah; tidak ada kode atau mutasi data |
| 5 | Keputusan batas baca bar tak beridentitas — **SELESAI 2026-08-13** | `F-038` | owner contract canonical bar yang lebih spesifik menolak tafsir lama: setiap canonical bar wajib menyatakan `RAW` secara eksplisit; common read boundary menahan publikasi bila bar current/history beridentitas kosong atau bukan exact `RAW`, dan negative oracle lulus |
| 6 | Rekam terms corporate action otoritatif — **SELESAI 2026-08-13** | `F-010a` `F-027a` | tiga event revision dalam scope beku terikat ke immutable KSEI evidence, ex/effective date, quantitative terms, dan `AUTHORITATIVE_VERIFIED`; announcement time yang tidak diketahui tetap `NULL`; belum ada penerapan ke seri |
| 7 | Rekam tier struktur pasar IDX — **SELESAI 2026-08-13** | `F-011a` | 4 rezim band + floor + tick ladder standard-equity bersumber, ber-effective-date, ber-revisi, terikat immutable evidence, dan coverage scope-nya terekam; config detector lama tetap bukan exchange-verified dan tidak ada penerapan ke output |
| 8 | Rekonstruksi korpus aktif satu kali melalui lifecycle normal — **SELESAI 2026-08-14** | `F-007b` `F-026b` `F-017b` `F-018b` `F-039b` `F-010b` `F-027b` `F-011b` | admission terukur mengunci suffix conformant `2026-07-08`…`2026-07-28`; 15/15 current pointer menunjuk publikasi replacement tersegel/readable; owning run dan artefak membawa lineage bar, evidence coverage, fakta eligibility/status, identitas produk, keputusan factor, tier binding, dan admission identity; seluruh oracle current-authoritative nol; history lama tidak diubah dan tidak dibaca sebelum boundary |
| 9 | Author fixture replay independen — **BELUM DIMULAI** | `F-030a` | fixture, expected values/nulls/reasons/lineage/hashes, author provenance, dan hash paket dibuat tanpa mengambil expected output dari run target; admission independensi lulus; replay target belum dijalankan |
| 10 | Eksekusi replay independen dan simpan proof — **BELUM DIMULAI** | `F-030b` `F-020` `F-024` | fixture Tahap 9 dieksekusi terhadap run hasil rekonstruksi; verdict admissible tersimpan, seluruh perbandingan wajib cocok, dan `config_identity` tersimpan bukan `'v1'`; hasil self-generated lama tetap historical/non-admissible dan tidak diubah |
| 11 | Gate stage-21 implementasi — **BELUM DIMULAI** | `F-023a` | gate benar-benar dijalankan atas current-authoritative corpus dan melaporkan setiap invariant sebagai `PASS`, `FAIL`, `BLOCKED`, atau `PRE_ACTIVATION_DEFERRED` sesuai bukti; verdict jujur adalah exit tahap ini dan tidak mengklaim `F-023b` |

Oracle Tahap 8 memakai populasi yang terjangkau dari current pointer, bukan seluruh history. Pelanggaran
harus nol untuk 18 field yang sudah dibekukan guard Tahap 3:

- bar: `listing_id`, `source_observation_id`, `canonicalization_version`, `price_product_code`,
  `quality_state`;
- coverage owning run: `coverage_expected_count`, `coverage_bar_not_expected_count`,
  `coverage_expectation_unknown_count`, `coverage_delivered_count`, `coverage_delivered_valid_count`;
- eligibility: `universe_membership_state`, `bar_expectation_state`, `delivery_state`,
  `canonical_quality_state`, `liquidity_state`, `temporal_status_state`, `event_risk_state`,
  `eligibility_reasons_json`.

Oracle terpisah membuktikan tidak ada current pointer yang masih menunjuk publikasi sebelum kampanye,
setiap publication/run identity konsisten, dan history row lama tidak berubah hash maupun jumlahnya.

Tahap 8 sengaja menjadi satu kampanye lifecycle setelah seluruh keputusan dan authority tersedia. Memecah
eksekusi bars, indicators, coverage, eligibility, dan publication ke kampanye tanggal yang berbeda akan
mengulang akuisisi serta reseal atas rentang yang sama. Yang dipisah adalah prasyaratnya; eksekusi korpus
yang mahal tetap satu kali.

### Gate di luar burn-down pembangunan

| Gate | Temuan | Baru boleh dimulai bila | Exit mandiri |
| --- | --- | --- | --- |
| `O1` — deklarasi activation | `F-021a` | aplikasi dan deployment siap serta activation checklist owner contract sudah memiliki executed evidence | pemilik menyetujui environment dan `OPERATIONAL_START_DATE`; marker tercatat tanpa menyimpulkan tanggal dari backfill/frontier |
| `O2` — propagasi marker | `F-021b` | `O1` selesai | `COUNT(*) WHERE operational_start_date IS NULL = 0` pada `eod_runs` saat transaksi ditutup dan jalur run baru menulis marker yang sama; marker adalah konteks boundary, sehingga state historis sebelum boundary tidak diubah menjadi klaim fresh |
| `O3` — consecutive activated sessions | `F-023b` | activation efektif dan operasi harian nyata sudah berjalan | bukti sesi IDX berturut-turut, termasuk failure/recovery dan consumer-gateway state, memenuhi owner contract |

`F-019` tetap di luar burn-down karena membutuhkan konsumen domain nyata. `F-021` tetap
**OPEN / `PRE_ACTIVATION_DEFERRED`** selama pembangunan; `operational_start_date = NULL` dan
`MARKET_DATA_DAILY_ENABLED=false` adalah state yang benar, bukan residu yang harus “dibersihkan”.

### Current next action

Tahap berikut yang diizinkan adalah **Tahap 9 — author fixture replay independen**. Tahap 8 selesai
dan tidak menjalankan replay. Tahap 9, gate stage-21, dan activation tetap belum dikerjakan.

### HISTORICAL, SUPERSEDED — Evidence penyesuaian urutan sebelum Tahap 4 — 2026-08-13

Penyesuaian strategi ini **PASS** sebagai snapshot prapelaksanaan. Status prapelaksanaan itu bukan
current state dan telah **SUPERSEDED** oleh penutupan Tahap 4 di bawah. Perubahannya saat itu terbatas
pada ledger dan guard sinkronisasi dokumentasi. Tidak ada kode produksi, `.env`, migration, schema,
`operational_start_date`, run, publication, artifact, fixture replay, terms IDX, maupun korpus yang
diubah.

- `AuditDocsSynchronizationStaticGuardTest`: 7 test, 385 assertion — PASS;
- `AuditCrossReferenceIntegrityTest`: 6 test, 24 assertion — PASS;
- `LifecycleProofIsNotMockedTest`: 5 test, 8 assertion — PASS;
- full MarketData suite: 1.444 test, 10.164 assertion — PASS.

Guard mengunci controller Tahap 4, urutan tepat Tahap 4–11, pemecahan parent/subfinding, pemisahan
`O1`–`O3`, populasi current-authoritative, larangan mengubah history lama, serta scope tanpa perubahan
kode/data. Dengan demikian blok 2026-08-12 tetap dapat dibaca sebagai sejarah tetapi tidak dapat
kembali menjadi urutan aktif secara diam-diam.

## Tahap 4 — Keputusan makna `RAW` — SELESAI 2026-08-13

### Input keputusan yang dibaca

Tahap ini membaca kembali catatan pembukaan `F-039`, owner kontrak canonicalization dan price product,
serta seluruh `Yahoo_Finance_Bootstrap_Source_Strategy.md`. Koreksi penting terhadap usulan pertama:
Tahap 4 tidak boleh mengubah kekurangan provider berbayar menjadi defect atau backlog. Strategi aktif
secara eksplisit memilih `api_free/yahoo_finance` untuk membuktikan manfaat Weekly Swing dan melarang
migrasi provider dibuat sebagai kewajiban fase sekarang.

Pengukuran ulang read-only sebelum keputusan:

- `eod_bars`: 756.329 baris, rentang 2023-01-02 sampai 2026-07-28;
- `price_product_code IS NULL`: 756.329; non-`NULL`: 0;
- close pecahan: 13.800 baris pada 89 ticker;
- current publication pointer: 844.

Angka ini adalah evidence keputusan, bukan target mutasi Tahap 4.

### Deklarasi pemilik

Pemilik menyetujui keputusan berikut pada 2026-08-13:

1. `api_free/yahoo_finance` **tetap bootstrap primary EOD source** untuk fase pembangunan saat ini.
   Tidak ada keputusan membeli, memilih, atau bermigrasi ke provider lain.
2. `RAW` adalah canonical, validated Regular-Market EOD OHLCV yang nilai ekonominya berasal dari
   field `indicators.quote.0` pada immutable source observation dan **tidak di-adjust, diperbaiki,
   dibulatkan secara destruktif, atau diubah skalanya oleh platform**.
3. `RAW` bukan sinonim provider payload: schema/date/identity validation, canonical mapping,
   provenance, quality, coverage, publication, dan readability gate tetap wajib. Ia juga bukan klaim
   bahwa Yahoo adalah source resmi IDX, bahwa nilainya official as-traded, atau bahwa provider tidak
   pernah merestatement skala historisnya.
4. Keadaan skala source adalah fakta yang terpisah dari `price_product_code`. Minimum state untuk
   penerapan keputusan ini adalah `AS_TRADED`, `PROVIDER_BACK_ADJUSTED`, dan `UNKNOWN`. State tersebut
   harus ditentukan per instrumen/peristiwa dari evidence yang dapat diaudit; nama state ini belum
   membuat kolom atau data pada Tahap 4.
5. Provider `adj_close` tetap diagnostic observation metadata. Ia bukan canonical `RAW`, bukan
   `STRUCTURAL_ADJUSTED`, dan tidak boleh menjadi fallback.
6. Verified corporate-action factor hanya boleh diterapkan ketika evidence menunjukkan faktor itu
   belum tertanam pada skala source. `PROVIDER_BACK_ADJUSTED` tidak boleh di-adjust ulang; `UNKNOWN`
   harus fail-safe sebagai held/quarantined, bukan dipaksa menjadi salah satu state.
7. Re-fetch Yahoo tetap menghasilkan observation baru dan correction/publication lineage sesuai
   source contract. Snapshot tersegel dan korpus lama tidak direlabel atau ditulis ulang in-place.
8. Akuisisi ulang dari source as-traded atau provider berbayar **tidak diwajibkan pada fase sekarang**.
   Evaluasi provider masa depan hanya mengikuti trigger yang sudah dimiliki strategi Yahoo.

Keputusan ini memilih cabang “terima Yahoo sebagai source aktif dan cegah double adjustment”, bukan
cabang “ganti Yahoo”. Ia mempertahankan tujuan strategi bootstrap sekaligus tidak mengubah provider
menjadi domain authority.

### Konsekuensi yang sengaja ditunda

Tahap 4 hanya menutup keputusan `F-039a`. Penerapan berikut tetap dilarang pada tahap ini dan menjadi
`F-039b` di Tahap 8:

- persistensi state skala source dan binding-nya ke observation/bar/publication;
- perbaikan ingest yang saat ini selalu memilih `RAW` walau adapter menyatakan kandidat
  `RAW`/`SPLIT_ADJUSTED`;
- klasifikasi per instrumen/peristiwa;
- rekonstruksi current-authoritative corpus;
- penerapan atau penahanan factor revisions;
- perubahan indicator, eligibility, publication, atau consumer output.

`F-039` karena itu tetap **OPEN/PARTIAL**. Menutup parent finding hanya dari deklarasi akan mengubah
keputusan menjadi bukti implementasi palsu.

### Kemurnian scope dan histori

Tidak ada kode produksi, `.env`, config runtime, migration, schema, database row, source observation,
bar, run, publication, factor, terms IDX, fixture replay, atau activation marker yang diubah. Tahap 5
dan seterusnya belum dikerjakan. Histori `F-039` tetap disimpan sebagai asal keputusan; current-state
table dan controller sekarang menyatakan `F-039a` CLOSED serta `F-039b` OPEN sehingga histori lama
tidak dapat menghidupkan kembali cabang “ganti Yahoo” secara diam-diam.

### Verdict

Exit criterion Tahap 4 terpenuhi: perlakuan korpus provider-back-adjusted, identitas `RAW`, status
Yahoo, kebutuhan akuisisi ulang, dan konsekuensi cabangnya tercatat beserta alasan tanpa kode atau
mutasi data. `F-039a` **CLOSED**. Tahap 4 **SELESAI**. Tahap berikut yang diizinkan adalah Tahap 5 —
keputusan batas baca canonical bar tak beridentitas (`F-038`).

### Evidence penutupan Tahap 4

- `AuditDocsSynchronizationStaticGuardTest`: 8 test, 413 assertion — PASS;
- `AuditCrossReferenceIntegrityTest`: 6 test, 24 assertion — PASS;
- `LifecycleProofIsNotMockedTest`: 5 test, 8 assertion — PASS;
- full MarketData suite: 1.445 test, 10.192 assertion — PASS;
- `git diff --check` dengan `cr-at-eol`: PASS; line ending ledger tetap CRLF dan test tetap LF;
- `git status --short`: hanya ledger ini dan guard sinkronisasi dokumentasinya yang berubah.

## Tahap 5 — Keputusan batas baca bar tak beridentitas — SELESAI 2026-08-13

### Keputusan dari authority yang sudah berlaku

Tahap ini tidak menambahkan tafsir baru. Pembacaan ulang owner contract menemukan aturan yang lebih
spesifik daripada kalimat umum yang membuka `F-038`:

- `Canonicalization_Contract_EOD_Bars.md` menetapkan produk canonical `RAW` dan menahan identity yang
  unknown;
- `EOD_Bars_Contract.md` mewajibkan `price_basis=RAW` serta referensi non-null untuk konten readable;
- `CONSUMER_READ_CONTRACT_LOCKED.md`, `Consumer_Readability_Decision_Table_LOCKED.md`,
  `Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`, dan
  `Downstream_Data_Readiness_Guarantee_LOCKED.md` mewajibkan gateway tunggal yang fail-closed.

Karena itu tafsir historis “bar mentah bukan analytical row sehingga bar tanpa produk boleh tetap
disajikan dengan warning” **ditolak**. Canonical bar adalah bagian wajib dari data product terbit dan
harus menyatakan identitas `RAW` secara eksplisit. `NULL`, string kosong, whitespace, perbedaan case,
atau produk lain tidak boleh dibaca sebagai `RAW`.

### Enforcement yang diselesaikan pada tahap yang sama

`EodPublicationRepository` sekarang menjadi sumber keputusan tunggal untuk keadaan ini:

1. resolver strict dan current-integrity scan memeriksa `eod_bars` serta `eod_bars_history` milik
   publication yang sama;
2. `NULL`/blank menghasilkan `PRICE_PRODUCT_UNRECORDED`;
3. nilai nonblank yang bukan exact-byte configured `RAW` menghasilkan
   `CANONICAL_BAR_PRICE_PRODUCT_INVALID`;
4. kedua keadaan membuat resolver mengembalikan `null`, sehingga seluruh publication ditahan sebelum
   bar, indicator, eligibility, watchlist, atau portfolio price keluar;
5. price/read-product repository mengulang filter exact `RAW` sebagai defense in depth, bukan sebagai
   pengganti common gateway.

Pencocokan memakai `HEX(...)` agar collation MariaDB yang case-insensitive tidak menganggap `raw`
sebagai `RAW`. Tidak ada fallback, default, trimming menjadi valid, atau reason per-row yang tetap
menyajikan harga.

### Negative oracle dan dampak korpus

Oracle integrasi menolak enam bentuk bypass pada bar current/history: `NULL`,
`STRUCTURAL_ADJUSTED`, lowercase `raw`, dan padded ` RAW `. Watchlist serta portfolio price juga
membuktikan payload kosong dengan reason `PRICE_PRODUCT_UNRECORDED`; kontrol positif memakai bar
beridentitas `RAW` dan tetap readable.

Pengukuran read-only sebelum enforcement mencatat 844 current pointer dan 756.328 bar
current-authoritative; seluruh 756.328 ber-`price_product_code=NULL`. Akibat yang benar dan disengaja
adalah seluruh 844 publication tersebut ditahan sampai Tahap 8 membentuk publikasi pengganti melalui
lifecycle normal. Ini bukan backfill Tahap 5 dan bukan kegagalan enforcement: menyajikannya akan
melanggar keputusan fail-closed yang baru dibuktikan.

### Kemurnian scope, registry, dan pemulihan residu

Tidak ada bar, history row, run, publication, pointer, indicator, eligibility, source observation,
factor, terms, tier, fixture replay, schema, migration, config, `.env`, atau activation marker yang
diubah. Tahap 5 hanya mengubah batas baca, kamus dua reason code, fixture/test terkait, ledger, dan
guard auditnya. Korpus legacy tidak diberi label `RAW` dan tidak disentuh.

Saat sinkronisasi runtime, full canonical seeder sempat memasukkan 31 kode yang sebelumnya belum ada.
Efek samping itu tidak disembunyikan atau dibiarkan: baseline direkonstruksi dari dump 2026-07-30,
seed Git 2026-08-03, dan bukti sesi Tahap 1 yang mencatat exact missing set. Pemulihan transaksional
mengubah registry runtime dari 392 menjadi 363 baris: 360 baseline, satu kode Tahap 1
`RUN_KNOWLEDGE_CUTOFF_MISSING`, serta tepat dua kode Tahap 5. Dua puluh sembilan kode di luar scope
dikembalikan ke keadaan belum-terpasang. Pembacaan ulang membuktikan kedua kode Tahap 5 exact
`READ_SIDE/HARD`, aktif, dan tidak ada kode di luar scope yang tertinggal akibat seeder tersebut.

### Verdict

Keputusan, enforcement, negative oracle, runtime reason registry, dan pembersihan residu berada pada
tahap yang sama. `F-038` **CLOSED**. Tahap 5 **SELESAI**. Tahap berikut yang diizinkan adalah Tahap 6 —
rekam terms corporate action otoritatif (`F-010a`, `F-027a`). Tahap 6 belum dimulai.

### Evidence penutupan Tahap 5

- focused Stage 5 + audit guard: 110 test, 697 assertion — PASS;
- full MarketData suite: 1.454 test, 10.255 assertion — PASS;
- PHP syntax pada seluruh berkas PHP yang diubah: PASS;
- production read-only oracle: 844 current pointer; 756.328 bar current-authoritative, seluruhnya
  tanpa identitas; 844 publication terdeteksi invalid; resolver 2026-07-28 menghasilkan `WITHHELD`;
- runtime reason registry: 363 baris = 360 baseline + 1 Tahap 1 + 2 Tahap 5;
- korpus: nol mutasi; 844 publication current ditahan jujur sampai rekonstruksi Tahap 8.

## Tahap 6 — Rekam terms corporate action otoritatif — SELESAI 2026-08-13

### Authority, scope, dan batas klaim

Tahap ini mengikuti `Corporate_Action_and_Adjustment_Policy.md`,
`Corporate_Action_Type_Registry_LOCKED.md`, dan `Price_Adjustment_Contract_LOCKED.md`. Scope dibekukan
sebelum penulisan sebagai **tiga stock split 2026 yang proyeksi legacy-nya telah mengklaim faktor**:
MLPT, RAJA, dan RMKE. Scope ini sengaja kecil dan terukur; ia bukan klaim bahwa 533 aksi legacy,
126 aksi ber-impact `SCALED`, atau periode sejak intentional dataset start telah direkonsiliasi penuh.

Authority yang dipakai adalah KSEI dalam peran CSD. Tiga PDF resmi diunduh ulang sebelum apply dan
hash serta ukuran byte-nya cocok persis:

| Ticker | Dokumen KSEI | SHA-256 | Byte |
|---|---|---|---:|
| MLPT | `KSEI-18691/JKU/0726` | `3d98ae958b06fa191ed21e5e2bc89ad4695631aaaad345e2c814d60252c25b11` | 38.882 |
| RAJA | `KSEI-18423/JKU/0726` | `0d2766536492aa68bb530f79e07013c800da4763cdcd8985c74f2e5c311078eb` | 38.882 |
| RMKE | `KSEI-18420/JKU/0726` | `15dc12b800ea8957de87d4ba72239296255d76b2635389586b38c3cc54705a1e` | 38.872 |

Manifest yang menjadi scope declaration adalah
`evidence/corporate_actions/stage_6_ksei_stock_split_terms_v1.json`. Ia membawa `record_only=true`
dan schema exact-key; field tambahan seperti `price_factor` ditolak. Faktor sengaja tidak direkam
sebagai aktif pada tahap ini karena penerapan menunggu source-scale state dan lifecycle Tahap 8.
Pada insert baru, verifier HTTPS mengunduh byte dokumen dari exact host/path KSEI tanpa redirect,
memerlukan HTTP 200 dan `application/pdf`, lalu mencocokkan panjang serta SHA-256 sebelum transaksi
boleh dimulai/menulis revision, sehingga tidak ada lock database yang ditahan selama network I/O.
Dry-run tetap tanpa jaringan; re-apply immutable yang sudah tersimpan juga
tidak bergantung pada availability situs KSEI.

### Revisi dan terms yang terekam

`market-data:events:record-authoritative-terms` default ke dry-run dan hanya menulis saat `--apply`
eksplisit. Satu transaksi menambah tiga `md_corporate_action_revisions` dan enam
`md_source_observations` immutable (capture + accepted), dengan hasil berikut:

| Ticker / listing | Cum | Ex/effective | Record | Distribution | Ratio | Nominal IDR |
|---|---|---|---|---|---|---|
| MLPT / 564 | 2026-07-20 | 2026-07-21 | 2026-07-22 | 2026-07-23 | 1:25 | 100 → 4 |
| RAJA / 706 | 2026-07-15 | 2026-07-16 | 2026-07-17 | 2026-07-20 | 1:5 | 25 → 5 |
| RMKE / 721 | 2026-07-16 | 2026-07-17 | 2026-07-20 | 2026-07-21 | 1:5 | 100 → 20 |

Ketiganya `action_type_code=STOCK_SPLIT`, `lifecycle_state=EFFECTIVE`, dan
`verification_state=AUTHORITATIVE_VERIFIED`. Event UID content-addressed atas KSEI, ISIN, dan nomor
dokumen; revision pertama tidak mengarang supersession. Waktu pengumuman yang tidak tersedia dari
surat tetap `announcement_at=NULL`; tanggal dokumen disimpan terpisah dan tidak disalin menjadi
announcement time. `recorded_at=2026-08-13 14:25:21` adalah saat platform mengetahui revisi, bukan
klaim saat bursa mengumumkannya.

Tanggal legacy yang sebelumnya berasal dari anchor deret provider tidak dipakai sebagai authority.
Secara khusus, ex-date KSEI RAJA adalah 2026-07-16, RMKE 2026-07-17, dan MLPT 2026-07-21. Baris
legacy tidak ditimpa agar history tetap jujur; current authority untuk scope ini berada pada revision
append-only yang terikat bukti KSEI.

### Guard append-only, anti-bypass, dan idempotensi

`AuthoritativeCorporateActionTermsService` menolak secara atomik:

- URL non-HTTPS, host selain exact `web.ksei.co.id`, atau path bukan announcement file KSEI;
- authority selain `KSEI/CSD`, verification selain `AUTHORITATIVE_VERIFIED`, type di luar
  `STOCK_SPLIT`, listing yang tidak tunggal pada ex-date, atau semantics registry yang konflik;
- date order yang salah, effective date yang bukan ex-date, ratio/nominal yang tidak konsisten,
  unknown yang difabrikasi, hash/byte/content type yang tidak valid, dan schema drift;
- duplicate revision yang isinya berbeda. Koreksi harus menjadi revisi baru; overwrite dilarang.
- response authority yang gagal diambil, redirect/non-200, bukan PDF, berbeda panjang, atau berbeda
  SHA-256 sebelum insert baru.

Re-apply manifest yang sama menghasilkan `inserted_revision_count=0`,
`unchanged_revision_count=3`, dan `source_observation_insert_count=0`; `recorded_at` serta source
observation lama tidak bergeser. Oracle statik juga memastikan writer baru tidak memiliki surface
ke `market_data_corporate_actions`, factor tables, run, publication, bar/history, indicator,
eligibility, atau lineage binding.

### Kemurnian scope, korpus, dan pembersihan residu

Snapshot pra/post menunjukkan hanya dua tabel milik Tahap 6 yang bertambah:

| Tabel/fakta | Sebelum | Sesudah |
|---|---:|---:|
| `md_corporate_action_revisions` | 0 | 3 |
| `md_source_observations` | 0 | 6 |
| `market_data_corporate_actions` | 533 | 533 |
| `md_adjustment_factor_sets` | 0 | 0 |
| `md_adjustment_factors` | 0 | 0 |
| `eod_runs` | 72.777 | 72.777 |
| `eod_publications` | 64.951 | 64.951 |
| `eod_bars` | 756.329 | 756.329 |
| `eod_indicators` | 756.328 | 756.328 |
| `eod_eligibility` | 779.402 | 779.402 |

Hash canonical tiga row legacy tetap
`ba8c24bc787876481807679c130d5662472c4d14e005436c59967e89d4690b61`. Production evidence scan
atas revision → accepted observation → capture mengembalikan tiga revision dan nol pelanggaran:
outcome `ACCEPTED/PASSED`, source `KSEI/authority_document`, capture/accepted payload hash identik,
unknown tetap `NULL`, dan payload tidak memuat factor aktif.

Reason code info `AUTHORITATIVE_TERMS_VALIDATED` ditambahkan sinkron ke registry/seed. Saat memasang
baris itu, full seeder sempat mengulang efek samping yang telah dicatat Tahap 5 dan menaikkan runtime
registry 363→393. Efek itu tidak disembunyikan atau dibiarkan. Exact 29 kode di luar scope yang sama
dengan daftar pemulihan Tahap 5 divalidasi lebih dulu lalu dihapus transaksional; state final adalah
**364 = 360 baseline + 1 Tahap 1 + 2 Tahap 5 + 1 Tahap 6**. Empat kode wajib dibaca kembali exact:
`RUN_KNOWLEDGE_CUTOFF_MISSING`, `PRICE_PRODUCT_UNRECORDED`,
`CANONICAL_BAR_PRICE_PRODUCT_INVALID`, dan `AUTHORITATIVE_TERMS_VALIDATED`.

Tidak ada tier/band/floor/tick Tahap 7, source-scale classification/factor application/republication
Tahap 8, fixture/replay Tahap 9–10, gate Tahap 11, atau activation marker yang dibuat.

### Verdict

Exit criterion Tahap 6 terpenuhi untuk scope yang dideklarasikan: event revision, immutable source
evidence, ex/effective dan lifecycle dates, quantitative terms, verification state, as-known
coordinate, unknown preservation, append-only conflict behavior, dan nol penerapan ke seri semuanya
terbukti. `F-010a` dan `F-027a` **CLOSED untuk scope yang dideklarasikan**. Parent `F-010` dan
`F-027` tetap **OPEN/PARTIAL** karena perekaman tiga event bukan rekonsiliasi full-range dan bukan
bukti terms mencapai output. Tahap 6 **SELESAI**. Tahap berikut yang diizinkan adalah Tahap 7 —
rekam tier struktur pasar IDX (`F-011a`).

### Evidence penutupan Tahap 6

- focused command/repository/schema/reason-code/audit/ops suite: 88 test, 1.602 assertion — PASS;
- full MarketData suite: 1.463 test, 10.422 assertion — PASS;
- PHP syntax pada seluruh berkas PHP Tahap 6: PASS;
- tiga PDF KSEI: verifier runtime HTTP 200/application-PDF dan hash/byte length 3/3 cocok;
- production apply: 3 revision, 6 observations; re-apply true no-op;
- production residue oracle: hanya revision/observation bertambah; korpus, history, factor, run,
  publication, pointer-dependent artifacts, dan output tidak berubah.

## Tahap 7 — Rekam tier struktur pasar IDX — SELESAI 2026-08-13

### Authority, scope, dan batas klaim

Tahap ini mengikuti `Exchange_Market_Structure_Facts_LOCKED.md`. Scope dibekukan sebelum penulisan
sebagai `IDX_REGULAR_STANDARD_EQUITY`: saham Papan Utama, Pengembangan, dan Ekonomi Baru di Pasar
Reguler, dengan batas dataset `2023-01-02`. Papan Akselerasi dan Pemantauan Khusus dikecualikan
secara eksplisit karena aturan exchange-nya berbeda; listing dengan board point-in-time yang kosong
atau tidak dikenal wajib `FAIL_CLOSED`, bukan mewarisi tier standar secara diam-diam.

Manifest `evidence/market_structure/stage_7_idx_regular_market_structure_v1.json` adalah deklarasi
scope exact-key dan membawa `record_only=true`. Ia merekam lima transport evidence yang byte-nya
diverifikasi sebelum transaksi:

| Authority / dokumen | Peran | SHA-256 byte yang diverifikasi | Byte |
|---|---|---|---:|
| OJK `29/SEOJK.04/2021` | origin regulator; rezim ARB 7% | `6251db009403322782d26b3585bd478e769f5d1939831f434243d8bb36364809` | 109.505 |
| IDX `PR-043/BEI.SPR/05-2023` | origin exchange; rezim ARB 15% | `5b67ec6ad53545d31466689ccd0361534736cc4cddcf36b7eabfb58a3de12a90` | 4.648 |
| IDX `S-07234/BEI.POP/08-2023` | dokumen exchange pada mirror terpin; rezim simetris | `6f210eaad92746cab54da39fe5d7cb1b1a1e48041225bdee96bf0da5102879b8` | 167.032 |
| IDX `Kep-00003/BEI/04-2025` | origin exchange; rezim current 15% | `309d8c8ad96b82008ea8a4327037fc1e0cee115fce82724024f8cb7756731aad` | 346.533 |
| IDX `S-02774/BEI.PPG/04-2016` | dokumen exchange pada redirect mirror terpin; floor/tick | `a9e14d67f8d927c0bf797ed9bb717e8e0babc3a2be2e09a67669a2ab99e1a9d6` | 368.940 |

Mirror hanya berfungsi sebagai transport byte dokumen beridentitas IDX, bukan sebagai authority
baru. Verifier membatasi HTTPS host/transport role, status 200, content type yang diizinkan,
redirect Dropbox tetap di transport Dropbox HTTPS, panjang, dan SHA-256. Dry-run tidak memakai
jaringan. Re-apply atas revision/evidence yang sudah tersimpan juga tidak bergantung pada situs.

### Revisi efektif dan tier yang terekam

Apply awal menambah enam `md_exchange_market_structure_revisions`, dua belas
`md_exchange_price_band_tiers`, lima `md_exchange_tick_size_tiers`, dan sepuluh
`md_source_observations` immutable (5 capture + 5 accepted):

| Rule | Effective interval | Tier/value |
|---|---|---|
| `PRICE_BAND` | 2021-12-01 … 2023-06-04 | upper 35/25/20%; lower 7/7/7% |
| `PRICE_BAND` | 2023-06-05 … 2023-09-03 | upper 35/25/20%; lower 15/15/15% |
| `PRICE_BAND` | 2023-09-04 … 2025-04-07 | symmetric 35/25/20% |
| `PRICE_BAND` | 2025-04-08 … open | upper 35/25/20%; lower 15/15/15% |
| `MINIMUM_PRICE` | 2016-05-02 … open | IDR 50 |
| `TICK_SIZE` | 2016-05-02 … open | `<200`:1/10; `200..<500`:2/20; `500..<2000`:5/50; `2000..<5000`:10/100; `>=5000`:25/250 (tick/max step, IDR) |

Band memakai batas tier `50..200`, `>200..5000`, dan `>5000`. Semua revision berstatus
`AUTHORITATIVE_VERIFIED`, memiliki content-addressed rule/source UID, `content_hash`, source
observation accepted, effective interval, `recorded_at=2026-08-13 15:51:26`, dan
`supersedes_revision_id=NULL` untuk revision pertama. Oracle coverage menunjukkan interval band
kontinu dari tanggal pertama dataset, floor/tick sudah berlaku sebelum boundary itu, satu exact
coverage-scope JSON, dan nol orphan revision.

### Koreksi append-only atas identity response — selesai 2026-08-13

Audit residu atas apply awal menemukan satu blind spot: verifier telah membuktikan response aktual,
tetapi hasil `HTTP status`, content type, document SHA-256, byte length, schema fingerprint, dan
bounded response sample dibuang sebelum persistence. Sepuluh observation awal akibatnya merekam
identity metadata manifest, bukan identity response yang benar-benar diterima. Semantik enam rule
dan 17 tier tidak salah; yang tidak admissible adalah evidence binding revision pertamanya.

Koreksi tidak mengubah atau menghapus history tersebut. Writer mendeteksi evidence lama yang tidak
memenuhi kontrak response, memverifikasi ulang kelima dokumen sebelum transaction, kemudian
menambahkan enam revision nomor 2 dengan `supersedes_revision_id` ke revision nomor 1 dan sepuluh
observation baru dengan `supersedes_observation_id` ke evidence lama. Revision aktif tetap tepat
enam dengan 12 band tier dan 5 tick tier. Kelima pasangan evidence aktif kini membawa status 200,
content type response aktual, exact document SHA-256/ref/byte length, schema fingerprint, serta
self-consistent bounded sample pada row `CAPTURED`; row `ACCEPTED/PASSED` menunjuk capture itu tanpa
menduplikasi body. Oracle current-state menghasilkan **0 evidence violation**.

Ini merupakan koreksi evidence `F-011a`, bukan perubahan fakta pasar atau penerapan `F-011b`.
Apply koreksi mencatat `evidence_correction_revision_count=6`; apply identik segera sesudahnya
kembali menjadi true no-op dengan `inserted_revision_count=0`, `unchanged_revision_count=6`,
`evidence_correction_revision_count=0`, dan `source_observation_insert_count=0`.

### Guard, idempotensi, dan kemurnian tahap

Command `market-data:market-structure:record-authoritative-rules` default ke dry-run dan hanya
menulis saat `--apply`. Ia menolak schema drift, record-only false, scope/board drift, gap atau
overlap rezim yang tidak cocok deklarasi, tier/range/inclusivity yang berubah, authority/transport
yang tidak diizinkan, byte evidence berbeda, duplicate immutable revision yang konflik, dan source
observation yang bukan `ACCEPTED/PASSED`. Enam revision disimpan dalam satu transaction setelah
seluruh remote verification selesai, sehingga kegagalan tidak menghasilkan subset data.

Re-apply produksi menghasilkan `inserted_revision_count=0`, `unchanged_revision_count=6`, semua
tier insert `0`, dan `source_observation_insert_count=0`. Perbandingan idempoten menormalisasi bentuk
DECIMAL MariaDB (`50` dan `50.0000`) sebagai nilai numerik yang sama tanpa melonggarkan string,
scope, identity, hash, atau evidence comparison.

Static guard atas `AuthoritativeExchangeMarketStructureService` memastikan tidak ada surface
`DB::table(...)` ke run, publication, pointer,
bar/history, indicator, eligibility, factor, event legacy, atau lineage binding. Stage 7 tidak
membuat resolver dan tidak menyentuh `PriceScaleBreakDetectionService`; config
`market_data.price_scale_break.min_price_idr=50` tetap input detector legacy yang **bukan**
exchange-verified. Dengan demikian tidak ada penerapan terselubung ke Stage 8.

Snapshot apply awal, dipertahankan sebagai riwayat eksekusi:

| Tabel/fakta | Sebelum | Sesudah |
|---|---:|---:|
| `md_exchange_market_structure_revisions` | 0 | 6 |
| `md_exchange_price_band_tiers` | 0 | 12 |
| `md_exchange_tick_size_tiers` | 0 | 5 |
| `md_source_observations` | 6 | 16 |
| `md_corporate_action_revisions` | 3 | 3 |
| `md_adjustment_factor_sets` | 0 | 0 |
| `md_adjustment_factors` | 0 | 0 |
| `eod_runs` | 72.777 | 72.777 |
| `eod_publications` | 64.951 | 64.951 |
| `eod_bars` | 756.329 | 756.329 |
| `eod_bars_history` | 56.908.318 | 56.908.318 |
| `eod_indicators` | 756.328 | 756.328 |
| `eod_eligibility` | 779.402 | 779.402 |

Current state sesudah koreksi evidence append-only:

| Tabel/fakta | Total fisik | Current/latest |
|---|---:|---:|
| `md_exchange_market_structure_revisions` | 12 | 6 revision nomor 2 |
| `md_exchange_price_band_tiers` | 24 | 12 |
| `md_exchange_tick_size_tiers` | 10 | 5 |
| `md_source_observations` | 26 | 10 observation / 5 pasangan evidence aktif |
| revision aktif tanpa `supersedes_revision_id` | — | 0 |
| evidence aktif yang tidak cocok response identity | — | 0 |

Enam revision nomor 1 dan sepuluh observation awal tetap immutable sebagai history yang secara
eksplisit disupersede; angka fisik 12/24/10/26 tidak boleh disalahartikan sebagai duplikasi current
authority. Tidak ada row lama yang diedit atau dihapus oleh koreksi.

Reason code `AUTHORITATIVE_MARKET_STRUCTURE_VALIDATED` dipasang melalui migration sempit yang
memvalidasi conflict, bukan menjalankan full seed. Runtime registry berubah tepat **364→365**;
tidak ada pengulangan efek samping 29 kode di luar scope yang pernah terjadi pada Tahap 5/6.

### Verdict

Exit criterion Tahap 7 terpenuhi untuk scope owner yang dikunci: band/floor/tick bersumber,
effective-dated, revisioned, coverage scope serta unknown policy terekam, immutable evidence dapat
diaudit, dan tidak ada penerapan ke output. `F-011a` **CLOSED**. Parent `F-011` tetap
**OPEN/PARTIAL** karena point-in-time board resolution dan pengikatan revision ke korpus baru adalah
`F-011b` pada Tahap 8. Tahap 7 **SELESAI**. Tahap berikut yang diizinkan adalah Tahap 8 —
rekonstruksi korpus aktif melalui lifecycle normal; pekerjaan itu belum dimulai di sini.

### Evidence penutupan Tahap 7

- verifier runtime: 5/5 HTTP 200; exact content-type policy, byte length, dan SHA-256 cocok;
- response-persistence oracle: 5/5 evidence aktif membawa exact response identity dan bounded
  capture sample; 6/6 revision aktif adalah revision nomor 2 dengan supersession; 0 violation;
- command unit/negative/idempotency/correction suite: 8 test, 194 assertion — PASS;
- audit synchronization guard: 11 test, 553 assertion — PASS;
- SQLite/schema mirror guard: 6 test, 587 assertion — PASS;
- full MarketData suite: 1.472 test, 10.749 assertion — PASS;
- PHP syntax seluruh berkas PHP Stage 7: PASS;
- repository diff check dengan kebijakan `cr-at-eol`: PASS; owned-file trailing-whitespace scan: PASS;
- production initial apply: 6 revision, 12 band tier, 5 tick tier, 10 observations;
- production evidence correction: append-only 6 revision + 12 band tier + 5 tick tier + 10
  observations; current authority tetap 6/12/5 dan re-apply true no-op;
- production residue oracle: 0 orphan/linkage/response-identity violation, 1 exact coverage scope,
  seluruh 5 evidence aktif memiliki pasangan `CAPTURED/PENDING` → `ACCEPTED/PASSED`,
  output/history/factor/event tetap pada baseline, dan registry hanya bertambah satu exact Stage 7
  reason code.

## Tahap 8 — Rekonstruksi korpus aktif satu kali — SELESAI 2026-08-14

### Jalur kedua yang disahkan

Jalur kedua mempertahankan dua boundary yang berbeda dan tidak menulis ulang salah satunya:

- `2023-01-02` tetap **intentional dataset start** pembangunan;
- `2026-07-08` adalah **conformant-corpus admission boundary** pertama yang terukur terhadap source,
  status perdagangan, coverage, dan quality gate yang dikunci.

Keputusan admission id 1/UID
`c791f200c11d6f98013e6b53d2797591d3f2f315e8e86b4fc348e7a1a5bc3a70` mengikat snapshot
long-suspension IDX per `2026-06-30` (observation 1984), transition search sampai `2026-07-28`
(observation 1986), status-revision-set hash, source-cache hash, measurement hash, threshold
`0,980000`, dan algoritma `stage8-conformant-suffix-admission/v1`. Apply ulang adalah true no-op.

Pengukuran mencakup 21 trading date `2026-06-30`…`2026-07-28`. Earliest continuous passing
suffix dimulai `2026-07-08`: predecessor `2026-07-07` gagal (`0,979978`), boundary lulus
(`0,980022`), dan setiap tanggal sesudahnya sampai frontier lulus. Bukti status menyatakan 59 listing
`NOT_EXPECTED` berdasarkan authority IDX, bukan berdasarkan absence/zero-volume Yahoo. Tanggal status
yang lebih lama hanya dipakai sesuai effective/known-time evidence; tidak ada proyeksi current state
ke masa lalu.

### Rekonstruksi dan hasil current-authoritative

Kampanye pertama id 1/full-range tetap immutable sebagai bukti jalur yang gagal dan berstatus
`SUPERSEDED`, bukan dihapus atau diubah menjadi pass. Kampanye kedua id 2/UID
`bbeaa5f28935742e9944b4d16c44d900fc301cfb17874fa22254e78b35a4eaaa` mengikat admission id 1,
mensupersede kampanye 1, dan membekukan tepat 15 trading date `2026-07-08`…`2026-07-28`.

Akuisisi Yahoo dilakukan fresh dan bounded untuk 962 ticker dalam 49 batch. Hasil lifecycle:

- 15/15 target `COMPLETE`; 15 correction `PUBLISHED`;
- 15 owning run `SUCCESS/READABLE` dan 15 current pointer beralih lewat finalize normal;
- minimum coverage `0,980022`, maksimum `1,000000`, threshold tetap `0,980000`;
- 13.860 bar replacement membawa listing, source observation, canonicalization version, exact
  product `RAW`, quality state, dan source-scale state;
- 14.427 market-structure binding tersimpan: 1.446 `RESOLVED_STANDARD_BOARD`, sedangkan 12.981
  sisanya eksplisit fail-closed karena board unknown/not-point-in-time/non-standard;
- 23 corporate-action factor decision berstatus `HELD_SOURCE_SCALE_UNKNOWN`; tidak ada source scale
  yang ditebak menjadi `AS_TRADED` dan tidak ada factor tidak-admissible yang diterapkan;
- first admitted date mempunyai 919 indicator row, seluruhnya deterministic invalid/NULL dan nol
  warm-up metric terisi karena history pra-admission dilarang menjadi seed.

State `UNKNOWN` dan fail-closed di atas adalah outcome yang diwajibkan owner contract saat evidence
tidak cukup, bukan residu untuk “dibersihkan” lewat asumsi. Yang wajib nol adalah binding yang hilang,
ambiguity yang disamarkan, factor yang diterapkan tanpa keputusan, atau output yang lolos tanpa
lineage; seluruhnya nol.

### Oracle, history, dan batas baca

Oracle final kampanye 2 menghasilkan `violation_count=0`. Masing-masing 16 kelompok berikut bernilai
nol: target incomplete; admission binding; pointer/identity; bar field; coverage field; eligibility
field; trading-status binding; publication lineage; market-structure binding dan cardinality;
factor decision, application, dan missing-applied-factor; baseline metadata dan snapshot immutability;
serta replacement artifact hash.

Audit hash sempat melaporkan 45 mismatch (tiga artefak × 15 tanggal). Publication dan artefaknya
tidak rusak: oracle mengurutkan nama kolom replacement secara alfabet, sedangkan hash publication
memakai urutan kolom kontrak. Oracle diperbaiki untuk mempertahankan urutan
`MarketDataPipelineService::*_HASH_COLUMNS`; baseline private snapshot tetap memakai urutan alfabet
karena menambah provenance di luar hash publication. Behavioral regression dan audit ulang atas
artefak produksi yang sama membuktikan 45→0 tanpa reissue, relabel, atau perubahan data.

Empat correction gagal dari percobaan lifecycle sebelumnya tetap terminal `FAILED`; tiga di
antaranya merekam perbaikan denominator/finalize invariant yang ditemukan saat eksekusi. Semuanya
dipertahankan sebagai history append-only. Tidak ada correction Tahap 8 aktif. Pre-admission current
pointer tidak menerima admission binding (hitungan nol), resolver mengembalikan non-readable untuk
`2026-07-07`, dan readable untuk `2026-07-08` serta `2026-07-28`.

Replay tidak dipanggil. `md_replay_daily_metrics` terakhir berubah `2026-08-11 08:47:28`, sebelum
kampanye Tahap 8; oracle kampanye menyimpan `stage_9_replay=NOT_EXECUTED`. Dengan demikian Tahap 8
tidak mengambil keluaran Tahap 9 untuk mengklaim selesai.

### Verdict

Tahap 8 **PASS dan selesai** untuk korpus conformant admitted. `F-007b`, `F-026b`, `F-017b`,
`F-018b`, `F-039b`, `F-010b`, `F-027b`, dan `F-011b` telah dieksekusi melalui lifecycle normal;
parent `F-007`, `F-026`, `F-017`, `F-018`, `F-039`, `F-027`, dan `F-011` ditutup. `F-010` tetap
parsial hanya karena scope tiga event KSEI bukan authority full-range, bukan karena penerapan tiga
event itu tertinggal. Intentional dataset start tidak digeser, history lama tidak direlabel, dan
Tahap 9 belum dimulai.

### Evidence penutupan Tahap 8

- final command re-audit: `status=ALREADY_COMPLETE`, campaign 2, 15 target,
  `oracle_violation_count=0`, dan `stage_9_replay=NOT_EXECUTED`;
- full MarketData suite: **1.484 test, 10.991 assertion — PASS**;
- audit-doc synchronization: 11 test, 555 assertion — PASS;
- SQLite/schema mirror: 6 test, 683 assertion — PASS;
- Stage 8 reconstruction/hash guards: 6 test, 41 assertion — PASS;
- seluruh migration Stage 8 sampai `2026_08_14_000003` applied;
- PHP syntax seluruh berkas PHP changed/untracked: PASS;
- `git diff --check` dengan kebijakan repository `cr-at-eol`: PASS;
- tidak ada proses PHP tersisa dan kedua probe audit sementara telah dihapus.

### HISTORICAL, SUPERSEDED — jalur pertama full-range yang fail-safe

Blok berikut mempertahankan bukti kampanye id 1 sebagai history. Klaim `BLOCKED`, `0/844`, dan
“Tahap 8 tidak PASS” di dalam blok ini hanya verdict kampanye pertama sebelum admission decision;
ia bukan current controller state dan tidak boleh mengalahkan penutupan di atas.

#### Implementasi yang tersedia

Tahap 8 hanya mengimplementasikan lifecycle yang dimiliki urutan aktif: command
`market-data:corpus:reconstruct-current` default dry-run, kampanye/tanggal baseline yang dibekukan,
akuisisi satu range Yahoo dalam batch 20 ticker dengan cache JSONL resumable, correction per tanggal,
re-ingest/recompute/republication normal, binding source-scale/factor/market-structure, serta oracle
current-authoritative dan hash snapshot baseline. Tahap ini tidak menjalankan fixture atau replay
Tahap 9/10.

Plan produksi membekukan scope `2023-01-02` sampai `2026-07-28`: 844 trading date, 977 ticker
temporal, 844 baseline current pointer, dan `baseline_max_publication_id=73596`. Seluruh 49 batch
akuisisi selesai; memori tetap bounded karena row diproses per tanggal dari cache, bukan sebagai satu
array korpus.

#### Executed fail-safe outcome

Kampanye `campaign_id=1`, UID
`e2fae5eb8ac99db0c80ea259b47cf5a61018a74979cdc47f2a099277ba07343a`, berhenti pada target pertama
`2023-01-02` dengan `STAGE8_DATE_NOT_READABLE`. Owning run 72923 mengukur:

| Bukti coverage | Nilai |
|---|---:|
| temporal universe / denominator | 825 |
| observation delivered | 779 |
| canonical valid | 777 |
| invalid OHLC | 2 |
| missing delivery | 46 |
| ratio / minimum | 0,944242 / 0,980000 |
| verified `NOT_EXPECTED` | 0 |

Yahoo mengembalikan 973 accepted response dan 5 explicit invalid-symbol response untuk range penuh;
pada tanggal target 46 listing tidak memiliki observation. Untuk sebagian besar simbol lama, payload
Yahoo aktif hanya memuat interval baru atau tidak lagi memiliki simbol. Empat puluh empat dari 46
listing itu mempunyai bar pada publikasi baseline, tetapi seluruhnya tanpa
`source_observation_id`; mayoritas adalah harga datar bervolume nol dan tiga berasal dari provider
legacy selain Yahoo. Bar tersebut tidak dapat dipakai sebagai observation baru dan tidak boleh
dianggap bukti `NOT_EXPECTED`.

Kontrak coverage, source acquisition, dan trading status melarang tiga jalan pintas: provider absence
atau zero volume tidak mengurangi denominator, bar canonical tidak boleh ditulis tanpa immutable
observation, dan status penuh-sesi tidak boleh disimpulkan dari data harga/current state. Karena itu
coverage tidak diturunkan, denominator tidak diubah, bar legacy tidak disalin, dan source lain tidak
disisipkan ke run Yahoo.

#### Residu dan batas state saat kampanye pertama

- current pointer `2023-01-02` tetap publication 72742/run 72067; jumlah pointer yang beralih dalam
  kampanye adalah **0**;
- correction 63963 diterminalkan `FAILED`, run 72923 `FAILED/NOT_READABLE`, dan candidate publication
  73598 tetap `UNSEALED/is_current=0`; tidak ada correction Stage 8 yang masih aktif;
- tabel projection `eod_bars`, `eod_indicators`, dan `eod_eligibility` telah kembali menunjuk owning
  baseline run, sementara candidate history tetap immutable sebagai bukti percobaan gagal;
- baseline publication 72742 tetap 823 bar dan snapshot/hash baseline kampanye tidak diubah;
- kampanye `BLOCKED` menyimpan `blocked_trade_date`, reason, complete count 0, dan
  `stage_9_replay=NOT_EXECUTED`;
- impact reprocess biasa kini dilarang untuk `request_mode=corpus_reconstruction`; kampanye sendiri
  adalah owner rebuild kronologis sehingga percobaan berikut tidak membuat fan-out lintas tanggal;
- resume setelah attempt terminal membuat correction baru dan mempertahankan attempt gagal, bukan
  membuka kembali atau menimpanya.

#### Verdict historis kampanye pertama

Mekanisme Tahap 8 dan perilaku fail-safe telah terbukti, tetapi exit criterion korpus belum terpenuhi:
0 dari 844 target selesai dan current-authoritative violations belum nol. Karena itu Tahap 8 **tidak
PASS dan tidak ditutup**; seluruh finding `b` tetap `OPEN`. Tahap 9 tidak boleh dimulai.

Melanjutkan target yang sama memerlukan salah satu evidence yang belum tersedia dan tidak boleh
dipilih diam-diam oleh implementasi: observation historis yang sah untuk source mode yang
dideklarasikan, atau authority status IDX point-in-time/full-session yang benar-benar membuktikan
`NOT_EXPECTED`. Mengubah intentional dataset start, coverage minimum, denominator, atau menamai bar
legacy sebagai observation bukan remediation yang diizinkan.

#### Evidence implementasi dan blocker historis

- seluruh migration sampai `2026_08_14_000001` berstatus applied;
- full MarketData suite: 1.481 test, 10.929 assertion — PASS;
- focused Stage 8, governance, audit-doc, schema, reason-code, migration-drift, dan anti-bypass
  guards — PASS;
- PHP syntax seluruh berkas PHP yang berubah dan `git diff --check` dengan kebijakan `cr-at-eol` —
  PASS;
- production frozen-baseline oracle: 844 target, pointer drift 0, baseline metadata violation 0,
  active Stage 8 correction 0, current replacement 0, replay row baru 0;
- blocker yang tetap terbuka: target pertama hanya 779 delivered observation dari denominator 825;
  tidak ada evidence sah di workspace untuk mengubah minimal 30 missing menjadi delivery atau
  verified full-session `NOT_EXPECTED`.

## Tahap 1 direvisi pemilik — analisis ulang, 2026-08-17

Tahap 1 diperbarui pemilik setelah tahap 4 versi lama terbukti tidak dapat dikerjakan. Revisinya
bukan penyesuaian kecil: ia mengubah kapan koordinat ditetapkan, dan menetapkan aturan umum yang
berlaku jauh melampaui `F-006`.

**Peringatan atas bagian rencana pada catatan ini.** Analisis di bawah ditulis sebelum saya membaca
struktur tahap yang berlaku, dan bagian yang menyangkut rencana keliru; hanya bagian mekanismenya
yang sahih. Urutan yang berlaku bukan rencana 9-tahap 2026-08-12 melainkan Tahap 1–8 yang tercatat
di atas, dengan Tahap 3–8 selesai 2026-08-13/14. Koreksi dinyatakan di tempatnya masing-masing.

### Yang berubah

| Aspek | Versi saya | Revisi pemilik |
| --- | --- | --- |
| Kapan distempel | malas, saat pembacaan coverage pertama | **saat run dibuat** (`getOrCreateOwningRun`, `createPromoteRunFromSeed`) |
| Dapat diubah | ya, diam-diam | **tidak** — `RUN_KNOWLEDGE_CUTOFF_IMMUTABLE` pada hook `updating` model |
| Run lama tanpa koordinat | distempel saat dibaca | **ditolak** — `RUN_KNOWLEDGE_CUTOFF_MISSING`, terdaftar di registry dan seed |
| Membaca run lama | memanufaktur koordinat | mengembalikan `null`, tidak menulis apa pun |
| Masukan penyebut yang diuji | identitas saja | identitas **dan** suspensi |

### Lubang yang ditutupnya

Versi saya menstempel koordinat pada pembacaan pertama. Artinya setiap jalur yang membaca run lama
akan **memanufaktur** koordinat pada saat audit, lalu menulis ulang klaim historis dari "tanpa batas"
menjadi "sebagaimana diketahui pada tanggal audit". Itu bentuk cacat yang berulang di proyek ini:
memperbaiki catatan alih-alih mencatat kebenaran. Uji
`test_a_legacy_run_without_a_coordinate_remains_historically_unbounded_on_read` mengunci lubang itu.

Menstempel saat pembuatan juga menutup hal kedua: pada versi saya, ingest berjalan sebelum koordinat
ada, sehingga dua stage dalam satu run dapat membaca dunia yang berbeda.

### Aturan umum yang ditetapkan revisi ini

Sebuah kolom yang menyatakan **apa yang dihormati oleh sebuah eksekusi** tidak dapat diisi mundur,
karena eksekusi itu sudah lewat. Ini yang membuat tahap 4 tidak dapat dikerjakan: `operational_start_date`
sejenis dengan `knowledge_cutoff_at` — ia menyatakan bahwa run berjalan dalam keadaan operasional.
Mengisinya mundur pada 72.776 run akan mengklaim sesuatu yang tidak pernah terjadi. Uji pemilik
mengunci ini juga: run lama yang ditolak harus tetap ber-`operational_start_date` NULL dan
`config_snapshot_id` NULL — blok peningkatan oportunistik di `EodRunRepository:57-64` kini tidak
terjangkau bagi mereka karena `assertKnowledgeCutoffForExecution()` melempar lebih dulu.

### Pemilahan yang tetap sahih — tetapi bukan usulan rencana

**Koreksi.** Bagian ini semula berjudul "akibatnya bagi rencana" dan mengusulkan pemilahan ulang
alur A. Usul itu sudah didahului kenyataan: **Tahap 8 menutup `F-007`, `F-011`, `F-017`, `F-018`,
dan `F-026` pada 2026-08-14**. Yang tersisa sahih dari bagian ini hanyalah pemilahannya sebagai
prinsip — jenis kolom mana yang boleh diisi mundur dan mana yang tidak — bukan sebagai pekerjaan
yang masih perlu dijadwalkan.

Aturan "klaim tentang eksekusi lampau tidak dapat diisi mundur" memilah kolom menjadi tiga jenis
yang berbeda nasib:

| Jenis | Anggota | Nasib |
| --- | --- | --- |
| Klaim tentang eksekusi lampau | `F-017` (bukti coverage run lama), `F-018` (dimensi snapshot run lama), `F-021` (`operational_start_date`) | **tidak dapat diisi mundur**; selesai lewat batas runtime + pernyataan batasan, persis pola yang baru dibangun |
| Fakta tentang datanya sendiri | `listing_id`, `price_product_code` (`F-026`, sebagian `F-007`) | dapat diturunkan ulang, **wajib berlabel turunan**, bukan seolah teramati sejak awal |
| Provenance observasi asli | `source_observation_id`, `canonicalization_version` (sebagian besar `F-007`) | **tidak dapat dikarang**; hanya ingest ulang yang mengisinya jujur |

Dua kalimat yang semula menutup bagian ini — bahwa "Tahap 4 menyusut menjadi deklarasi env" dan
"Tahap 5 harus ditulis ulang" — **dicabut**. Keduanya menempelkan kesimpulan pada rencana 9-tahap
2026-08-12 yang sudah tidak berlaku. Tahap 4 yang sebenarnya adalah keputusan makna `RAW` dan Tahap 5
adalah keputusan batas baca bar tak beridentitas; keduanya selesai 2026-08-13. `F-021` sendiri
berstatus `PRE_ACTIVATION_DEFERRED` dan bukan blocker pembangunan.

### Dua hal terukur yang menuntut keputusan

**45 run `RUNNING` lama akan ditolak permanen.** Seluruh 45 tidak berkoordinat, dan koordinat kini
tidak dapat distempel belakangan, sehingga run-run itu tidak dapat dilanjutkan maupun diperbaiki.
Masing-masing hanya memblokir triple `(trade_date, source, request_mode)`-nya sendiri; aktivitas
terakhir 2026-07-30, jadi seluruhnya terbengkalai, bukan pekerjaan berjalan. Menutupnya menuntut
`CANCELLED` eksplisit — mutasi data produksi, karena itu menunggu instruksi terpisah.

**Dua dari empat pembacaan universe masih tanpa cutoff.** `EodEligibilityBuildService:54` sudah
meneruskan `$knownAt`; `EodBarsIngestService:643` dan `BackfillLifecycleOrchestrator:1687`/`:1713`
belum. Prinsip yang dinyatakan revisi ini sendiri — setiap stage satu run membaca dunia yang sama —
belum berlaku penuh. Jendela nyatanya bukan detik melainkan hari: menjalankan ulang run lama membuat
coverage membaca pada koordinat lamanya sementara ingest membaca pada saat ini.

### Status terukur

1.485 tes, 10.992 asersi, lulus seluruhnya. **20 run produksi kini menyimpan koordinat** (19
`COMPLETED`, 1 `FAILED`) — jalur simpan terbukti pada baris produksi, bukan lagi hanya pada uji,
memperbaiki keterbatasan yang dinyatakan pada laporan Tahap 1 pertama.

### Posisi sebenarnya per 2026-08-17

Tahap 1–8 tercatat; Tahap 3–8 selesai 2026-08-13/14. Tahap 8 **PASS**; blok "tidak PASS" di dalam
catatannya adalah verdict historis kampanye pertama, bukan status berjalan. Temuan terbuka **7**:
`F-010`, `F-019`, `F-020`, `F-021`, `F-023`, `F-024`, `F-030`. Tahap berikut yang diizinkan adalah
**Tahap 9 — fixture replay independen**, belum dimulai.

Dua hal terukur di atas berdiri di luar rantai itu dan tidak menggerbangi Tahap 9: 45 run `RUNNING`
tanpa koordinat menunggu keputusan `CANCELLED`, dan dua pembacaan universe tanpa cutoff menunggu
keputusan apakah prinsip "satu run satu dunia" diberlakukan penuh. Keduanya diukur 2026-08-17 pada
korpus berjalan, bukan warisan rencana lama.

## Audit independen Tahap 1–8 — 2026-08-17

Diminta pemilik: memeriksa apakah Tahap 1–8 benar-benar selesai, diuji terhadap bukti berjalan dan
bukan terhadap klaim catatannya sendiri. Seluruh pengukuran dilakukan 2026-08-17 pada korpus dan kode
yang sedang berjalan.

### Aturan pengukuran yang dipakai

Aturan milik urutan ini sendiri: conformance current diukur mengikuti `eod_current_publication_pointer`
menuju publikasi tersegel, dan **nilai `NULL` legacy tidak menentukan conformance**. Audit ini
mematuhi aturan itu; angka historis 756.329 tidak diperlakukan sebagai target.

### Hasil per tahap

| Tahap | Vonis | Bukti berjalan |
| --- | --- | --- |
| 1 `F-006` | **PASS, dengan satu celah** | 13 test spesifik lulus; 20 run produksi menyimpan koordinat; determinisme terbukti pada korpus produksi (3 evaluasi identik per cutoff; penyebut bergerak 913→881 saat cutoff melewati 64 listing) |
| 2 `F-045` | **PASS** | keempat field coverage hadir di `MarketDataEvidenceExportService`; guard statis `EvidenceExportCompletenessStaticGuardTest` dan `AuditDocsSynchronizationStaticGuardTest` menolak field tersimpan tanpa jalur ekspor |
| 3 guard beku | **PASS** | catatannya sendiri menyatakan batasnya secara eksplisit: hanya subtemuan `a`, dan tanpa klaim bahwa `NULL` historis berkurang |
| 4 makna `RAW` | **PASS** | `config/market_data.php:12` `raw_product_code => 'RAW'`; penegakan exact-byte pada resolver |
| 5 batas baca | **PASS, terbukti fungsional** | resolver diuji langsung: `2026-07-15` dan `2026-07-28` **terbaca**; `2026-07-07`, `2026-06-30`, `2023-01-02` **ditahan** |
| 6 terms corporate action | **PASS** | 18 aksi berfaktor, 23 `md_adjustment_factor_decisions` |
| 7 tier IDX | **PASS** | 24 band tier, 10 tick tier, 12 revisi struktur, 17.307 binding publikasi |
| 8 rekonstruksi korpus | **PASS untuk cakupan yang dideklarasikannya** | 18 publikasi teradmisi (15 current), 13.860 bar bersilsilah penuh; batas baca membuktikan suffix `2026-07-08`…`2026-07-28` terbaca dan sebelumnya ditahan |

Tahap 8 sempat saya nilai terlalu keras. Exit criterion-nya **menyatakan sendiri** cakupannya —
suffix `2026-07-08`…`2026-07-28` dan 15/15 pointer, bukan 844 — sehingga 743.264 bar legacy yang
masih `NULL` memang di luar target dan bukan kegagalan. Cakupan itu dideklarasikan sebelum eksekusi,
bukan disempitkan saat penutupan.

### Temuan baru: `readiness_state` tidak dapat menyatakan kebenaran

`Downstream_Consumer_Read_Model_Contract_LOCKED.md:54` menetapkan kosakata tertutup untuk
`readiness_state`: `READABLE`, `HELD`, `FAILED`, `BUILDING`, `SUPERSEDED`, `NOT_AVAILABLE`.

Terukur pada kode dan korpus berjalan:

- kolom itu ditulis **hanya di dua tempat**, `EodPublicationRepository:606` dan `:693`, keduanya
  dengan nilai `'NOT_READY'`;
- `'NOT_READY'` **bukan anggota kosakata kontrak**;
- **tidak ada satu pun kode di `app/` yang membacanya**;
- seluruh 928 baris ber-`readiness_state` bernilai `NOT_READY`; 64.092 sisanya `NULL`.

Akibatnya konkret dan dapat ditunjuk: publikasi 73657 dan 73666 **terbukti terbaca** lewat resolver
pada audit ini, tetapi tercatat `NOT_READY` — dalam nilai yang kontraknya tidak mengenal. Produk data
karena itu tidak dapat menyatakan kesiapannya sendiri, dan seorang pembaca yang mempercayai kolom itu
akan menyimpulkan kebalikan dari keadaan sebenarnya.

Ini bentuk cacat yang berulang di proyek ini: aturan ditulis dengan benar, lalu tidak pernah
dijalankan. Ia tidak membatalkan satu pun vonis PASS di atas, karena tidak ada tahap yang mengklaim
mengimplementasikannya — tetapi ia menyentuh kontrak read-side yang dimiliki Tahap 5.

### Celah Tahap 1 yang tetap terbuka

Dua dari empat pembacaan universe belum membawa cutoff: `EodBarsIngestService:643` dan
`BackfillLifecycleOrchestrator:1687`/`:1713`. `EodEligibilityBuildService:54` sudah. Prinsip yang
dinyatakan revisi Tahap 1 sendiri — setiap stage satu run membaca dunia yang sama — belum berlaku
penuh; jendela nyatanya hari, bukan detik, ketika run lama dijalankan ulang.

Selain itu 45 run `RUNNING` tanpa koordinat kini ditolak permanen dan tidak dapat dilanjutkan maupun
distempel. Menutupnya menuntut `CANCELLED` eksplisit, yaitu mutasi produksi, dan menunggu instruksi
terpisah.

### Kesimpulan

Delapan tahap **benar-benar selesai** untuk cakupan yang masing-masing deklarasikan, dan bukan atas
klaim melainkan atas perilaku yang diuji. Yang tersisa bukan tahap yang gagal, melainkan tiga hal di
luar rantai: `readiness_state` yang tidak dapat menyatakan kebenaran, dua pembacaan universe tanpa
cutoff, dan 45 run terbengkalai. Tahap berikut yang diizinkan tetap **Tahap 9 — author fixture replay
independen**, belum dimulai.

### `F-046` — blok "Readiness and freshness" kontrak read model tidak terimplementasi

Ditemukan pada audit Tahap 1–8, 2026-08-17, saat menyapu kelas cacat dari `readiness_state`.
Ternyata bukan satu kolom melainkan satu blok kontrak utuh.

`Downstream_Consumer_Read_Model_Contract_LOCKED.md:52-58` menuntut tujuh hal. Terukur pada korpus
berjalan (72.796 run, 928 publikasi ber-readiness):

| Tuntutan kontrak | Keadaan terukur |
| --- | --- |
| `readiness_state` ∈ {`READABLE`,`HELD`,`FAILED`,`BUILDING`,`SUPERSEDED`,`NOT_AVAILABLE`} | ditulis hanya `NOT_READY` di `EodPublicationRepository:606`/`:693` — **di luar kosakata**; **nol pembaca di `app/`** |
| `freshness_state` ∈ {`FRESH`,`STALE`,`DEGRADED`,`NOT_AVAILABLE`} | ditulis `NOT_EVALUATED`/`DEVELOPMENT_NOT_OPERATIONAL` — **keduanya di luar kosakata**; 19 baris terisi, 72.777 `NULL` |
| latest expected trade date | **72.796 dari 72.796 `NULL`** |
| latest acquired trade date | **72.796 dari 72.796 `NULL`** |
| latest canonicalized trade date | **72.796 dari 72.796 `NULL`** |
| latest readable trade date | **72.796 dari 72.796 `NULL`** |
| operational activation context | `operational_start_date` **72.796 dari 72.796 `NULL`** — satu-satunya butir yang sudah punya temuan (`F-021`, `PRE_ACTIVATION_DEFERRED`) |

**Enam dari tujuh butir tidak dimiliki temuan mana pun.** `F-019` menyebut ketiadaan konsumen hilir,
bukan nilai ilegal pada kolom yang sudah ada.

**Bahayanya bukan kolom kosong, melainkan kolom yang berbohong.** Kolom kosong menyatakan "belum
dibangun". Kolom berisi `NOT_READY` menyatakan "sudah dibangun dan jawabannya tidak siap". Audit ini
membuktikan publikasi 73657 dan 73666 **benar-benar terbaca** lewat resolver, sementara keduanya
tercatat `NOT_READY` dalam nilai yang kontraknya tidak mengenal. Pembaca yang mempercayai kolom itu
menyimpulkan kebalikan dari kenyataan.

**Tidak membatalkan vonis PASS Tahap 1–8.** Tidak satu pun tahap mengklaim mengimplementasikan blok
ini; residu ini berada di antara tahap, bukan di dalamnya. Tetapi ia menyentuh kontrak read-side yang
dimiliki Tahap 5, sehingga tidak dapat disebut di luar lingkup market-data.

Target: nyatakan blok ini sebagai belum dibangun secara eksplisit dan hentikan penulisan nilai di
luar kosakata, atau bangun proyeksinya. Menulis nilai ilegal adalah keadaan terburuk dari keduanya.

### Catatan cakupan uji

16 test melewat diam-diam ketika MariaDB tidak terjangkau (drift migrasi, oracle korpus produksi,
storabilitas verdict replay, advisory lock). Dengan database hidup: **1.485 test, 10.992 assertion,
nol skip**. Suite yang dijalankan tanpa database tetap melaporkan hijau sambil tidak menguji empat
hal itu — bukan temuan, tetapi perlu diketahui sebelum hijau dijadikan bukti.

### `F-047` — bukti expectation per-listing tersimpan tetapi tidak diekspos

`Coverage_Universe_Definition_LOCKED.md:49-55` menuntut setiap run/publication **mengekspos** lima
hal, salah satunya **per-listing expectation reason/source/version**.

Tahap 2 menutup `F-045` untuk butir *hitungan*. Butir *per-listing* tidak tersentuh. Terukur pada
`MarketDataEvidenceExportService`: **nol kemunculan** untuk kelima field pembawanya —
`bar_expectation_state`, `universe_membership_state`, `eligibility_reasons_json`,
`trading_status_revision_id`, `trading_status_source_observation_id`.

Ini defect yang persis sama dengan `F-045` — kontrak berbunyi *expose*, bukan *store* — hanya pada
separuh yang belum dikerjakan. Tahap 2 karena itu belum 100% terhadap kontrak pemiliknya.

### `F-048` — pengecualian `NOT_EXPECTED` tanpa bukti terikat, pada korpus teradmisi

`Coverage_Universe_Definition_LOCKED.md:21` menetapkan hanya `NOT_EXPECTED` **terverifikasi** yang
boleh keluar dari penyebut, dan `:23-29` menuntut buktinya **bersumber dan ber-effective-date**.

Terukur pada korpus yang Tahap 8 nyatakan conformant (14.427 baris eligibility pada 18 publikasi
teradmisi):

- **885 baris** ber-`bar_expectation_state = BAR_NOT_EXPECTED`, seluruhnya beralasan
  `ELIG_TRADING_SUSPENDED` — jadi berbasis status, bukan kalender, sehingga menuntut bukti status;
- `trading_status_revision_id`: **14.427 dari 14.427 `NULL`**;
- `trading_status_source_observation_id`: **14.427 dari 14.427 `NULL`**;
- sementara `md_trading_status_revisions` memuat **59 revisi** yang berlaku dalam jendela itu.

Buktinya ada di basis data, kolom pengikatnya ada, tetapi tidak satu pun baris mengikat. 885 listing
karena itu meninggalkan penyebut tanpa penunjuk ke revisi maupun observasi yang membuktikannya.

**Akar penyebabnya ada di Tahap 3.** Daftar 18 field yang dibekukan memuat delapan field *state*
eligibility, tetapi **tidak memuat kedua kolom penunjuk bukti itu**. Guard membekukan pernyataannya
dan membiarkan pembuktiannya kosong, sehingga Tahap 8 dapat melewati oracle-nya sendiri sambil
melanggar kontrak coverage. Tahap 3 dan Tahap 8 keduanya belum 100%.

### Rekapitulasi kepatuhan Tahap 1–8 terhadap `docs/market_data`

| Tahap | 100% terhadap kontrak pemilik? | Residu |
| --- | --- | --- |
| 1 | **belum** | 2 dari 4 pembacaan universe tanpa cutoff; 45 run `RUNNING` terbengkalai; 202 pasangan lama tak terjelaskan |
| 2 | **belum** | `F-047` — bukti expectation per-listing tidak diekspos |
| 3 | **belum** | `F-048` — daftar beku melewatkan kedua kolom penunjuk bukti |
| 4 | **ya** | `raw_product_code` terpasang dan ditegakkan exact-byte |
| 5 | **belum** | `F-046` — blok readiness/freshness kontrak read model tidak terimplementasi |
| 6 | sebagian | `F-010a` tertutup; parent `F-010` terbuka karena batas otoritas eksternal |
| 7 | **ya** | tier bersumber, ber-effective-date, ber-revisi, 17.307 binding |
| 8 | **belum** | `F-048` pada korpus yang justru dinyatakan conformant |

Dua tahap bersih: **4 dan 7**. Enam sisanya membawa residu yang dapat ditunjuk.

## Tahap 1 — penutupan celah Invariant 14, 2026-08-17

Dikerjakan atas instruksi menuntaskan Tahap 1 sampai 100% terhadap standar `docs/market_data`.

### Standar yang dipakai

`Determinism_Invariants_LOCKED.md:122`, Invariant 14: *"As-known replay resolves only revisions known
by the declared knowledge cutoff. **Current state must not leak into either mode.**"*

Ini standar yang lebih keras daripada exit criterion Tahap 1 sendiri, yang hanya menuntut penyebut
deterministik. Invariant 14 menuntut **seluruh** pembacaan revisi di dalam run patuh pada koordinat.

### Cacat yang ditemukan, lebih besar dari catatan sebelumnya

Catatan 2026-08-17 sebelumnya menyebut "dua dari empat pembacaan universe tanpa cutoff". Penelusuran
menemukan akarnya bukan pada pembacaan universe saja: konteks akuisisi membawa kunci `known_at` yang
**dibaca di dua tempat dan tidak pernah ditulis di mana pun**.

- `EodBarsIngestService:58` — assertion sesi kalender membaca `$context['known_at']`;
- `EquityProviderSymbolResolver:45` — resolusi simbol provider membacanya;
- `EodBarsIngestService:643` — pembacaan universe akuisisi bahkan tidak menanyakannya.

Karena tidak ada penulis, ketiganya menjawab "sebagaimana keadaan sekarang" di dalam run yang sudah
punya koordinat sendiri. Bentuk yang sama seperti biasa: pipa terpasang lengkap, tidak pernah dialiri.

### Yang dikerjakan

`MarketDataPipelineService` kini mengisi `known_at` pada konteks akuisisi dari
`resolveKnowledgeCutoff($run)`, dan pembacaan universe akuisisi meneruskannya. Satu penulis menutup
ketiga pembaca sekaligus.

### Bukti

`AcquisitionKnowledgeCutoffTest`, tiga uji berpasangan dengan sanggahannya: listing yang terekam
setelah cutoff **tidak** diminta ke sumber; korpus sama tanpa cutoff **harus** memintanya; cutoff
yang lebih lambat **harus** menerimanya.

Uji ini diverifikasi tidak hampa — perbaikannya dicabut sementara dan uji pertama **gagal** dengan
`AAA4` bocor masuk, lalu lulus kembali setelah dikembalikan.

Uji mula-mula menyalahi `LifecycleProofIsNotMockedTest` karena berdiri di atas port, bukan adapter.
Aturan proyek hanya mengizinkan penggantian `App\Infrastructure\MarketData\Source`; uji disesuaikan
mengikuti aturan itu, bukan aturannya yang dilonggarkan.

Verifikasi: **1.488 test, 10.996 assertion, PASS**; `git diff --check` dengan `cr-at-eol` bersih.

### Tahap 1 belum 100% — dua butir menunggu keputusan pemilik

Keduanya sengaja tidak dikerjakan karena berada di luar strategi dokumen.

**1. Dua pembacaan universe pada perencanaan backfill.** `BackfillLifecycleOrchestrator:1687` dan
`:1713` berada di `resolveTickerUniverse` dan `resolveMissingTickerPlan` — fungsi perencanaan yang
berjalan **sebelum run ada**, sehingga belum ada koordinat untuk dibaca. Invariant 14 mengatur mode
replay, bukan perencanaan. Menetapkan cutoff bagi sebuah rencana adalah keputusan rancangan yang
tidak dinyatakan dokumen mana pun. Risikonya nyata tetapi terbatas: rencana disusun pada keadaan
sekarang sementara run yang lahir darinya membaca pada koordinatnya sendiri, sehingga keduanya dapat
berselisih tentang ticker mana yang ada.

**2. 45 run `RUNNING` tanpa koordinat.** Ditolak permanen oleh guard Tahap 1, tidak dapat dilanjutkan
maupun distempel karena koordinat bersifat immutable. Menutupnya menuntut `CANCELLED` eksplisit —
mutasi data produksi, yang protokol larang tanpa instruksi terpisah.

Selain keduanya, **202 pasangan tanggal/hari lama** tetap tidak dapat dijelaskan, tetapi itu sudah
dikecualikan aturan pengukuran urutan ini sendiri: nilai legacy tidak menentukan conformance current.

<!-- LEGACY_EXTRACT_BODY_END -->
