# Market-Data Implementation Ledger

## Current-state role

Ledger ini adalah satu-satunya dashboard current-state untuk pelaksanaan work order market-data `W00`–`W22`.

Authorities:

- work order: `../book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`;
- document/deliverable/proof assignment: `../book/Market_Data_Implementation_Conformance_Matrix_LOCKED.md`;
- command lifecycle dan result format: `../book/Market_Data_Implementation_Command_Protocol_LOCKED.md`;
- behavior: owner contracts;
- documentation verdict: `reports/AUDIT_FINAL_STATE.md`.

Ledger mencatat state; ia tidak menciptakan behavior dan tidak boleh mengalahkan audit evidence. Update harus current-state only, bukan menumpuk round/history. Detailed executed output disimpan pada evidence bundle yang direferensikan.

Created: `2026-08-03`

## State interpretation

Semua work order dimulai `NOT_STARTED` terhadap **corrected strategy baseline**. Ini tidak berarti repository tidak memiliki legacy code; artinya work order tersebut belum dilaksanakan dan diaudit terhadap baseline baru.

Hanya satu work order boleh `IN_PROGRESS`. Successor tidak boleh dimulai sampai predecessor `CONFORMANT`.

## Current controller state

- documentation strategy: `DOCUMENTATION_STRATEGY_READY`
- implementation conformance: **`NOT_GRANTED`** — 24 P1 gap material tersisa (`W22`, 2026-08-06)
- operational validation: **`NOT_GRANTED`** — nol sesi teraktivasi (`W22`, 2026-08-06)
- final claim level: **`IMPLEMENTATION_READY`**, bukan `runtime-proven`
- open findings recorded by command protocol: **13 terbuka** (`F-006`, `F-007`, `F-010`, `F-011`, `F-013`, `F-015`, `F-016`, `F-017`, `F-018`, `F-019`, `F-020`, `F-021`, `F-023`); `F-022` ditutup pada remediasi W15
- known implementation backlog carried by the audit report: **24 terbuka dari 32 tercatat** — seluruh 4 P0 tertutup (`P0-02` pada W05; `P0-01`, `P0-03`, `P0-04` pada remediasi W21), 4 P1 tertutup (`P1-13`, `P1-16`, `P1-26`, `P1-40`), 24 P1 terbuka. Baris ini sebelumnya membaca 40 dari 44 dan basi sejak penutupan P0 pada W21; dikoreksi pada `MD-STATUS` 2026-08-06
- execution mode: `STAGE_BY_STAGE`
- active work order: `NONE`
- next permitted command: **`NONE` — seluruh work order W00–W22 telah dijalankan; lanjutan menuntut keputusan lingkup, bukan perintah berikutnya**

## Work-order ledger

| Work order | Scope | Dependency | Status | Latest audit verdict | Assigned docs | Evidence refs | Next action/state |
|---|---|---|---|---|---:|---|---|
| `W00` | Preflight and implementation ledger baseline | documentation ready | `CONFORMANT` | `PASS` | 142 dari 142 | baseline 2026-08-03 di bawah | closed |
| `W01` | Scope, boundary, dataset/activation semantics | `W00 CONFORMANT` | `CONFORMANT` | `PASS` | stages 1–2, 3 dokumen | `TerminologyOwnerVocabularyTest` 7/15; suite 1165/8786 | closed |
| `W02` | Yahoo bootstrap and provider-neutral ports | `W01 CONFORMANT` | `CONFORMANT` | `PASS` | stage 3, 1 dokumen | `ProviderNeutralBoundaryTest` 8/15; suite 1173/8801 | closed |
| `W03` | Migration/schema/repository/reason/test skeleton | `W02 CONFORMANT` | `CONFORMANT` | `PASS` | foundations stages 4–21 | `MigrationIntegrityAndDriftTest` 5/7; suite 1178/8808 | closed |
| `W04` | Immutable config snapshot and semantic bindings | `W03 CONFORMANT` | `CONFORMANT` | `PASS` | stage 16 foundation, 7 dokumen | `ConfigIdentityBindingTest` 6/25; suite 1184/8833 | closed |
| `W05` | Temporal identity and mappings | `W04 CONFORMANT` | `CONFORMANT` | `PASS` | stage 6, 2 dokumen | `TemporalIdentityFixturesTest` 6/14; suite 1190/8847 | closed |
| `W06` | Calendar/session/trading status | `W05 CONFORMANT` | `CONFORMANT` | `PASS` | stage 7, 2 dokumen | `CalendarProvenanceAndStatusTest` 8/16; suite 1198/8863 | closed |
| `W07` | Immutable observations and source adapters | `W06 CONFORMANT` | `CONFORMANT` | `PASS` | stage 4, 3 dokumen | `SourceObservationImmutabilityTest` 6/26; suite 1204/8889 | closed |
| `W08` | Resilience/manual recovery/failure taxonomy | `W07 CONFORMANT` | `CONFORMANT` | `PASS` | stage 5, 5 dokumen | `SourceFailureResilienceTest` 18/51 + `SourceCircuitBreakerTest` 6/9; 68.411 run produksi, 0 provider-failure shrink; suite 1228/8949 | closed |
| `W09` | Import-only and canonical RAW | `W08 CONFORMANT` | `CONFORMANT` | `PASS` | stage 8, 4 dokumen | `CanonicalRawImportBoundaryTest` 8/25; produksi 756.329 bar, 0 zero-price dan 0 OHLC-order violation; suite 1236/8974 | closed |
| `W10` | Publication/seal/pointer/correction lifecycle | `W09 CONFORMANT` | `CONFORMANT` | `PASS` | stage 9, 16 dokumen | `PublicationSealPointerLifecycleTest` 9/17; 6 trigger terpasang dan terbukti menolak sealed serta mengizinkan candidate; produksi 64.092 publikasi, 0 tanggal dengan lebih dari satu current; suite 1245/8991 | closed |
| `W11` | Corporate-action event/factor lifecycle | `W10 CONFORMANT` | `CONFORMANT` | `PASS` | stage 10, 5 dokumen | `CorporateActionCandidateBoundaryTest` 6/13; produksi 15 faktor turunan diblokir dari published output, 0 tersisa; suite 1251/9005 | closed |
| `W12` | Coherent analytical price products | `W11 CONFORMANT` | `CONFORMANT` | `PASS` | stage 11, 2 dokumen | `CoherentPriceProductBoundaryTest` 6/14; suite 1257/9020 | closed |
| `W13` | Actual/proxy daily metrics | `W12 CONFORMANT` | `CONFORMANT` | `PASS` | stage 14, 2 dokumen | `ActualVersusProxyMetricBoundaryTest` 5/10, terbukti diskriminatif (80.000 vs 100.000 tanpa perbaikan); suite 1262/9030 | closed |
| `W14` | Deterministic indicators/dependency graph | `W13 CONFORMANT` | `CONFORMANT` | `PASS` | stage 15, 7 dokumen | `IndicatorIndependentOracleTest` 7/12 (oracle dihitung tangan, bukan dibaca balik dari implementasi); probe produksi 120 ticker, p90 `1,62%` dan maks `72,9%`; suite 1269/9042 | closed |
| `W15` | Temporal coverage gate | `W14 CONFORMANT` | `CONFORMANT` | `PASS` (re-audit) | stage 12, 4 dokumen | `CoverageSilentImprovementBoundaryTest` 6/17 + `CoverageDormantUniverseTest` 8/16; produksi 91 dari 962 instrumen kembali ke penyebut; suite 1307/9162 | closed |
| `W16` | Explainable data usability | `W15 CONFORMANT` | `CONFORMANT` | `PASS` | stage 13, 3 dokumen | `EligibilityExplainabilityBoundaryTest` 7/20; suite 1282/9079 | closed |
| `W17` | Versioned atomic read product | `W16 CONFORMANT` | `CONFORMANT` | `PASS` | stage 17 core, 13 dokumen | `ConsumerReadProductAntiBypassTest` 7/36; suite 1289/9115 | closed |
| `W18` | Exact/as-known replay | `W17 CONFORMANT` | `CONFORMANT` | `PASS` | stage 18, 5 dokumen | `AsKnownReplayBoundaryTest` 4/13 (mode as-known sebelumnya tidak ada); DOC-71 BLOCKED ditegakkan; suite 1293/9128 | closed |
| `W19` | Operational lifecycle/commands/observability/evidence | `W18 CONFORMANT` | `CONFORMANT` | `PASS` | stage 19, 27 dokumen | `OperationalCommandSafetyBoundaryTest` 8/14; 33 command bertanda tangan dan ber-handler; suite 1301/9142 | closed |
| `W20` | Optional session snapshot decision/implementation | `W19 CONFORMANT` | `CONFORMANT` | `PASS` | stage 17/19 optional, 5 dokumen | `SessionSnapshotOptionalityBoundaryTest` 6/18; suite 1307/9160 | closed |
| `W21` | Global convergence/backfill/full semantic proof | `W20 CONFORMANT` | `IN_PROGRESS` | **`PARTIAL`** (re-audit) | stages 20–21, 30 dokumen | `GlobalConvergenceClosureTest` 7/204; 3 P0 ditutup; stage 21 tetap tidak terpenuhi — `PROVEN` menuntut production-path oracle dan activated operational evidence; suite 1314/9366 | `MD-RUN W22 market-data.` |
| `W22` | Independent audit/activation-aware validation/relock | `W21 PARTIAL` | `CONFORMANT` | `PASS` | stage 22, 30 dokumen | 154 klaim production-ready ditandai superseded di 13 dokumen; claim level ditetapkan `IMPLEMENTATION_READY`; suite 1314/9366 | closed |

## W00 baseline — direkam 2026-08-03

Exit gate `W00` menurut blueprint: *current code/schema/test/evidence baseline direkam; setiap dokumen aktif memiliki assignment di conformance matrix.* Keduanya terpenuhi dan tercatat di bawah. Baseline ini adalah titik banding untuk setiap work order berikutnya.

### Preflight environment

| Item | Nilai | Gate |
|---|---|---|
| PHP | `7.4.33` | `>= 7.3` dan `< 8.4` — **PASS** |
| Ekstensi wajib | `dom`, `mbstring`, `xml`, `xmlwriter` | lengkap |
| PHPUnit | `9.6.34` | dapat dijalankan |
| MariaDB | `10.4.27` | tersedia |

### Code

| Permukaan | Jumlah |
|---|---:|
| Application service | 35 |
| Persistence repository | 21 |
| Source adapter | 6 |
| Artisan command | 34 |

### Schema

| Sumber | Jumlah | Catatan |
|---|---:|---|
| Tabel MariaDB | 40 | keadaan terdeploy |
| Tabel mirror SQLite | 41 | keadaan yang dimaksud |
| Berkas migration | 47 | |
| Migration diterapkan | 56 | **dua berkas terakhir tidak tercatat** — `P1-26` |

### Data

| Tabel | Baris |
|---|---:|
| `eod_bars` | 756.329 |
| `eod_bars_history` | 56.138.923 |
| `eod_indicators` | 756.328 |
| `eod_publications` | 64.092 |
| `market_data_corporate_actions` | 530 |

### Test

| Item | Nilai |
|---|---|
| Suite penuh | `OK (1158 tests, 8774 assertions)` |
| Berkas test market-data | 129 |
| — behavioral | 95 |
| — static guard teks-sumber | 34 |

Proporsi dilaporkan terpisah sesuai `../tests/Contract_Tests_Specification.md`: angka gabungan melebihkan cakupan sebesar bagian yang tidak mengeksekusi apa pun.

### Evidence

| Item | Nilai |
|---|---|
| Evidence bundle di `storage/app/market-data/` | 20 |
| Berkas golden fixture / oracle | **0** — `DOC-81` |
| Admissibility bukti yang ada | lihat **Evidence admissibility ledger** pada `reports/AUDIT_FINAL_STATE.md` |

### Assignment coverage

142 dokumen aktif pada `book/`, `registry/`, `indicators/`, `backtest/`, `db/`, `tests/`, `ops/`, dan `session_snapshot/`. **Nol tanpa assignment** di conformance matrix.

### Batas yang berlaku atas baseline ini

Baseline ini merekam **apa yang ada**, bukan bahwa apa yang ada benar. Ia tidak menutup satu pun dari 31 temuan implementasi yang dibawa audit report, dan tidak boleh dikutip sebagai bukti kesesuaian. Perannya adalah titik banding: setiap perubahan pada work order berikutnya diukur terhadap angka-angka ini.

## W01 record — stage 1 dan 2, ditutup 2026-08-03

Exit gate stage 1: *constants/config/API vocabulary/schema dictionary/test names tidak menentang terminology owner dan tidak membuat pre-2023/freshness/watchlist-performance claim yang salah.*
Exit gate stage 2: *boundary static/architecture tests lulus dan compatibility `eligible` hanya berarti upstream `data_usable`.*

### Yang ditambahkan

`tests/Unit/MarketData/TerminologyOwnerVocabularyTest.php` — 7 test yang **mengeksekusi** aturan kosakata, bukan menegaskan sebuah dokumen memuat string: ia meresolusi config nyata, membaca schema dictionary nyata, dan menelusuri nama test nyata. Cakupannya price product, canonical scope, dataset start, `data_usable` versus `eligible`, kosakata policy pada config, kontradiksi dictionary terhadap `adj_close`, dan klaim readiness pada nama test.

### Finding dan remediasi

`F-001` — `AuditDocsSynchronizationStaticGuardTest::test_production_ready_claim_states_a_single_settled_decision` **menegakkan klaim yang sudah dicabut**: ia mewajibkan `MARKET_DATA_PRODUCTION_READY_LOCKED`, `Final source-state lock status: LOCKED`, dan `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT -> LOCKED` tetap ada di dokumen audit, padahal `README.md` mencabutnya untuk baseline data-readiness yang dikoreksi.

Suite hijau karena itu **mewajibkan platform terus menyatakan sesuatu yang owner document-nya sudah tarik**, dan itulah sebab empat belas dokumen pada `DOC-83` masih memuat klaim tersebut — mencabutnya akan memecahkan test ini.

Diremediasi pada W01 sesuai langkah 3 dan 7 blueprint: ketiga assertion dihapus, method dinamai ulang menjadi `test_archived_source_state_evidence_states_a_single_settled_decision`, dan alasan pencabutannya ditulis pada docblock. Fakta eksekusi arsip yang tidak mengklaim readiness — runtime parity, provider smoke, sinkronisasi dokumen audit — dipertahankan.

### Verifikasi exit gate

| Gate | Bukti |
|---|---|
| Kosakata config terhadap terminology owner | 7 test baru, seluruhnya lulus |
| Tidak ada klaim pre-2023/freshness/performance pada nama test | 129 berkas dipindai, satu pelanggaran ditemukan dan diremediasi |
| Boundary architecture tests | 16 test, 102 assertion, lulus |
| `eligible` hanya berarti `data_usable` | config kanonik `data_usable`; kolom bernama `eligible` hanya alias lama dan tabel `watchlist_*` hilir; satu-satunya filter bergantung config `scope_default` yang bernilai `eligibility_set` sehingga tidak menyala |

### Verifikasi ulang backlog

`P1-13` ditemukan **basi** dan ditutup: fallback `adj_close` ke `close` pada `PublicApiEodBarsAdapter` sudah hilang dan adapter kini membawa flag kapabilitas eksekutabel. Tujuh temuan lain diperiksa ulang terhadap keadaan nyata dan seluruhnya masih terbuka.

## W02 record — stage 3, ditutup 2026-08-03

Exit gate stage 3: *mengganti adapter tidak mengubah canonical/product/indicator/read contracts dan Yahoo tidak pernah dilabel official IDX source.* **Kedua kondisi lulus dan kini punya test yang mengeksekusinya.**

### Yang ditambahkan

`tests/Unit/MarketData/ProviderNeutralBoundaryTest.php` — 6 test: kontrak hilir tidak menamai adapter konkret, provider, maupun aturan rendering simbol; application layer bergantung pada port dan tidak pernah pada adapter; source tidak pernah dilabel official exchange source; `manual_file` bukan default source mode; profil akses cocok dengan basis public bootstrap yang diklaim; dan tidak ada konfigurasi paid-provider yang masuk.

### Required implementation outcome — keadaan

| Outcome | Keadaan |
|---|---|
| provider port netral | terpenuhi — `ApiEodBarsSource`, `ManualEodBarsSource`, `SourceObservationRecorder` |
| Yahoo adapter terisolasi | terpenuhi — `implements ApiEodBarsSource`, nol referensi di application layer |
| manual file hanya controlled recovery | terpenuhi — `default_source_mode = api` |
| paid-provider work deferred | terpenuhi — nol config subscription/entitlement/vendor |
| **licensing disclosure eksplisit** | terpenuhi — deklarasi tercatat, `F-002` ditutup |

### `F-002` — ditutup 2026-08-03

`Yahoo_Finance_Bootstrap_Source_Strategy.md` section **Licensing basis (LOCKED)** mewajibkan empat hal dicatat dan dipelihara. Yang dapat diverifikasi dari resolver sudah diuji: profil akses tanpa autentikasi, `credential_profile = bootstrap-public-access`, `auth_header_name` dan `auth_token` kosong.

Empat butir sisanya adalah **deklarasi pemilik platform**, bukan fakta yang dapat diturunkan dari repository:

1. penggunaan aktual saat ini — internal atau tidak, komersial atau tidak, dan siapa yang mengaksesnya;
2. terms provider yang berlaku saat pengambilan, beserta tanggal pembacaannya;
3. batas yang diketahui, khususnya redistribusi, penyimpanan, dan pemakaian otomatis;
4. peristiwa yang akan mengubah dasar ini.

Keputusan bootstrap dibenarkan oleh penghematan biaya, dan kontraknya menyatakan penghematan itu hanya sah bila penggunaannya memang diizinkan. Menuliskan deklarasi ini tanpa otoritas pemiliknya akan menghasilkan klaim kepatuhan yang tidak dapat diverifikasi — persis yang dilarang butir kedua.

**Ditutup.** Pemilik platform menyatakan penggunaan **internal dan non-komersial dengan dirinya sebagai satu-satunya pihak yang mengakses**, dicatat 2026-08-03. Butir keempat diturunkan dari deklarasi itu: pemakaian komersial, penambahan pengakses, akses publik, redistribusi keluaran, atau perubahan terms membuat deklarasi ini kedaluwarsa.

Butir kedua dan ketiga dicatat apa adanya — **terms belum dibaca dan belum bertanggal, sehingga tidak ada klaim kepatuhan yang dibuat**. Kontrak melarang klaim kepatuhan tanpa tanggal, bukan mewajibkan terms sudah dibaca; ketiadaan klaim adalah keadaan yang sah. Dua test baru menegakkannya: deklarasi wajib ada, dan selama penanda belum-bertanggal masih terpasang, tidak ada berkas `app/`, `config/`, atau `tests/` yang boleh menyatakan kepatuhan terhadap terms provider.

## W03 record — skeleton fondasi stage 4-21, ditutup 2026-08-03

Exit gate: *clean-install/upgrade path tersedia untuk setiap feature berikut; belum ada nullable placeholder yang dianggap conformant.*

### Cacat yang ditemukan dan diperbaiki

Migration `2026_08_03_000001_harden_market_data_orders_1_to_4.php` mendeklarasikan class `HardenMarketDataOrdersOneToFour`, sementara migrator meresolusi nama dari berkasnya menjadi `HardenMarketDataOrders1To4`. Ketidakcocokan itu membuat pemeriksaan `class_exists` gagal, Laravel jatuh ke `getRequire()` yang memakai `require` biasa atas berkas yang sudah di-`require`, dan run mati dengan *Cannot declare class ... because the name is already in use* **sebelum satu pun statement dijalankan**.

Migration itu **tidak pernah bisa dijalankan sejak ditulis**. Yang tercatat sebagai *belum diterapkan* pada `P1-26` sebenarnya *tidak dapat diterapkan*.

### Yang dijalankan

| Migration | Durasi |
|---|---:|
| `2026_08_02_000001_add_market_data_strategy_v2_foundation` | `1.795,64ms` |
| `2026_08_03_000001_harden_market_data_orders_1_to_4` | `351,05ms` |

Durasi itu mengonfirmasi `ALGORITHM=INSTANT` dipakai atas tiga tabel history berukuran total 32 GB — `eod_indicators_history` 14,79 GB, `eod_bars_history` 12,54 GB, `eod_eligibility_history` 4,62 GB. Prasyaratnya terpenuhi: seluruh kolom nullable, nol index pada tabel besar, dan 112 penjaga `hasColumn` yang membuat migration idempoten.

### Dampak schema

| Sebelum | Sesudah |
|---|---|
| 40 tabel | 53 tabel; 13 tabel `md_*` fondasi V2 |
| `price_product_code` nihil | ada pada 6 tabel |
| `config_snapshot_id` nihil | ada pada 12 tabel |
| `eod_eligibility` 7 kolom | 17 kolom |

### Batas yang berlaku

Seluruh tabel baru **kosong** dan seluruh kolom baru **`NULL`** — termasuk 756.329 baris `eod_bars` yang `price_product_code`, `config_snapshot_id`, dan `listing_id`-nya belum terisi.

Sesuai blueprint, pembuatan skeleton pada W03 **tidak menutup contract area mana pun**. Lima temuan yang bergantung padanya — `P1-16`, `P1-20`, `P1-21`, `P1-23`, `P1-25` — tetap terbuka dengan sifat yang berubah: dari *tidak ada tempatnya* menjadi *tidak ada penulisnya*. Nullable placeholder tidak boleh dihitung conformant.

### Penjaga yang ditambahkan

`tests/Unit/MarketData/MigrationIntegrityAndDriftTest.php` — 5 test: kecocokan nama class terhadap nama berkas, ketiadaan class ganda, kelengkapan `up`/`down`, dan **drift detection dua arah** yang diwajibkan `DOC-79`, yaitu berkas migration yang belum diterapkan dan migration terterapkan yang kehilangan berkasnya. Test pertama akan menangkap cacat yang baru diperbaiki; test drift akan menangkap keadaan yang menghasilkan lima temuan dari satu batch.

Anonymous class migration dikecualikan secara sengaja — `return new class extends Migration` adalah bentuk sah dan tidak membawa nama untuk dicocokkan.

## W04 record — fondasi config snapshot stage 16, ditutup 2026-08-03

Exit gate blueprint: *semua writer berikut dapat menerima non-null config/reason/build identity sejak pertama kali dibuat.* Stage 16 memiliki dua work order — `W04` fondasi, `W21` penutup — sehingga W04 tidak menutup contract area-nya.

### Yang ternyata sudah ada

`MarketDataConfigSnapshotRepository` sudah lengkap: canonical sort rekursif, redaksi rahasia berbasis pola kunci, `SHA-256` atas JSON ternormalisasi, dedup berdasarkan hash/schema/effective/environment, dan seluruh field provenance. `EodRunRepository` sudah mengikatnya saat run dibuat, menulis `config_snapshot_id`, `config_hash`, dan `config_snapshot_ref`.

Ini mengoreksi pembacaan `P1-25`. Nol dari 71.917 run memiliki config hash **bukan** karena mekanismenya tidak ada, melainkan karena seluruh run itu **mendahului kodenya**. Keadaan `CONFIG_UNBOUND` pada `DOC-71` tetap berlaku untuk korpus lama dan tidak berubah oleh W04.

### Yang ditambahkan

`tests/Unit/MarketData/ConfigIdentityBindingTest.php` — 6 test yang menguji bagian yang justru disebut exit gate, bukan hanya objek snapshot-nya:

| Test | Yang dibuktikan |
|---|---|
| run menerima identitas non-null | ketiga field terisi dan meresolusi ke snapshot tersimpan |
| provenance lengkap | delapan field asal-usul terekam, bukan hanya hash |
| satu perubahan semantik menggeser identitas | `min_ratio` diubah, hash/id/uid ketiganya berubah |
| config tak berubah memakai ulang identitas | resolve dua kali menghasilkan satu baris |
| rahasia teredaksi dan terlihat | `[REDACTED:` hadir, hash cocok atas JSON teredaksi |
| rotasi rahasia tidak menggeser identitas | dua token berbeda menghasilkan hash sama |

Yang terakhir sengaja: kontrak menyatakan rotasi secret yang tidak memengaruhi konten adalah provenance-only. Bila hash bergeser, ia akan membocorkan keberadaan perubahan rahasia ke artefak yang mengaku tidak memuatnya.

### Keadaan adopsi

| Writer | Keadaan |
|---|---|
| `eod_runs` | terikat |
| `eod_bars` | menerima identitas |
| `eod_publications` | menerima identitas |
| `eod_indicators` | belum — milik `W14` |
| `eod_eligibility` | belum — milik `W16` |

Dua yang belum adalah writer **lama** yang akan dibangun ulang pada work order-nya sendiri. Exit gate W04 menuntut fondasinya tersedia bagi writer yang dibuat berikutnya, dan itu terpenuhi: run membawa identitasnya, repository dapat di-inject, dan kolomnya ada sejak W03.

Reason registry: 360 baris pada `eod_reason_codes`, tersedia bagi writer mana pun.

## W05 record — identitas temporal stage 6, ditutup 2026-08-03

Exit gate: *listing/delisting, rename, symbol reuse, provider mapping revision, dan inactive-now-active-then fixtures lulus tanpa survivorship leakage.*

### Bukti terhadap data produksi

Proyeksi temporal dijalankan atas master legacy dan menghasilkan **977 baris** pada masing-masing `md_issuers`, `md_instruments`, `md_listings`, dan `md_listing_symbols`. Proyeksinya lazy — dipanggil `universeAsOf()` — dan idempoten melalui find-or-insert berdasarkan UID, sehingga tabel kosong sebelumnya bukan karena tiadanya penulis melainkan karena belum ada yang memanggilnya sejak W03 membuat tabelnya.

Uji survivorship memakai emiten nyata, bukan fixture:

| | |
|---|---|
| Emiten | `legacy_ticker_id 939`, listing `1995-05-16`, delisting `2023-04-06` |
| Keadaan sekarang | `is_active = 0` |
| `universeAsOf('2023-03-01')` | 846 listing — **memuat 939** |
| `universeAsOf('2026-07-28')` | 962 listing — **tidak memuat 939** |

Itu tepat kasus *inactive-now-active-then*. Universe tumbuh 846 ke 962 sepanjang periode, yang benar karena listing bertambah seiring waktu.

### Enam fixture yang dikunci

`tests/Unit/MarketData/TemporalIdentityFixturesTest.php` menyemai tabel temporal secara langsung, bukan lewat proyeksi, karena kasus rename, reuse, dan revisi mapping **tidak dapat diungkapkan oleh master current-state sama sekali** — justru itu alasan model temporal ini ada.

| Fixture | Yang dibuktikan |
|---|---|
| delisting | hadir sebelum, absen sesudah tanggal delisting |
| listing belum berlaku | absen sebelum `listed_date`, hadir sejak tanggalnya |
| rename | tiap sisi meresolusi simbol yang berlaku saat itu, bukan yang sekarang |
| symbol reuse | teks simbol yang sama meresolusi ke listing yang memegangnya pada tanggal itu |
| symbol teretraksi | tidak meresolusi sama sekali; retraksi berbeda dari interval tertutup |
| revisi provider mapping | revisi kemudian tidak menulis ulang resolusi tanggal sebelumnya |

### Temuan yang ditutup

`P0-02` dan `P1-16`. Backlog turun ke **27 terbuka dari 31**.

## W06 record — kalender, sesi, dan trading status stage 7, ditutup 2026-08-03

Exit gate: *unknown tidak menjadi holiday/normal; current status tidak bocor ke historical date; long suspension tidak diubah menjadi dormancy exclusion.*

### Yang sudah ada

`MarketCalendarRepository` dan `TemporalTradingStatusRepository` sudah membaca tabel revisi, dengan proyeksi lazy dari kalender legacy dan revisi `COMPLETED` yang **di-append, bukan memutasi** yang lama. Keduanya gagal tertutup: kalender tanpa bukti melempar `MARKET_CALENDAR_EVIDENCE_MISSING`, status tanpa bukti mengembalikan `UNKNOWN` dengan `TRADING_STATUS_NO_EVIDENCE`, dan revisi status yang belum `VERIFIED` tidak meresolusi sama sekali.

### Yang ditambahkan — provenance tier

`DOC-42` menuntutnya sejak review order 7 dan belum ada implementasinya. Migration `2026_08_03_000002_add_calendar_provenance_tier` menambahkan `provenance_tier`, `reconciled_at`, dan `reconciliation_source_ref` ke `market_calendar` dan `md_market_calendar_revisions`.

Backfill-nya berbasis bukti, bukan tanggal potong: **tahun tanpa satu pun hari libur nasional tercatat tidak dapat menjadi tahun IDX nyata**, karena IDX tutup pada lima belas hari libur atau lebih setiap tahun.

| Tahun | Hari libur nasional | Tier |
|---|---:|---|
| 2023–2027 | 20–28 | `VERIFIED` |
| 2028–2030 | **0** | `PROJECTED` |

`assertCompletedRegularSession()` kini menolak baris non-`VERIFIED` dengan `MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED`. Diverifikasi terhadap produksi: `2026-07-28` diterima dengan session `COMPLETED`, `2028-06-15` ditolak.

Tanpa ini, tahun perdagangan pertama di luar horizon terbitan bursa akan menghasilkan expected bar pada **setiap** hari libur nasional, dan kegagalan coverage yang muncul akan terbaca sebagai gangguan provider.

### Dampak pada suite

Gerbang tier memecahkan 40 test yang menyemai kalender tanpa kolom baru. Sesuai langkah 3 dan 7 blueprint, yang diperbaiki adalah **fixture-nya**, bukan aturannya — sembilan berkas kini menyatakan `provenance_tier` secara eksplisit, karena fixture yang menyemai hari perdagangan memang menyatakan hari itu nyata.

### Delapan fixture exit gate

`tests/Unit/MarketData/CalendarProvenanceAndStatusTest.php`: tanggal `PROJECTED` tidak pernah expected; tier absen tidak menjadi default optimistis; sesi `VERIFIED` yang selesai meresolusi dan membawa tier-nya; hari libur `VERIFIED` ditolak dengan alasannya sendiri yang berbeda dari penolakan provenance; tanggal tanpa bukti gagal tertutup; suspensi kemudian tidak menjelaskan tanggal sebelumnya; bukti status absen meresolusi `UNKNOWN` bukan normal; dan suspensi panjang tetap suspensi, tidak menjadi dormancy.

### Yang masih terbuka

`DOC-43` semantik sesi pendek belum dapat ditegakkan: `session_close_time` legacy `NULL` pada seluruh 1979 hari perdagangan dan `is_half_day` tetap `0` karena sumber tidak menyediakannya. `P1-17` karena itu ditutup sebagian, bukan seluruhnya.

## W07 record — immutable source observations stage 4, ditutup 2026-08-03

Exit gate: *rerun tidak menimpa observation; secret tidak bocor; canonical rows dapat ditelusuri ke observation yang tepat.*

### Yang sudah ada

Port `SourceObservationRecorder` terpasang di kedua adapter dan di `EodBarsIngestService`, dan `AppServiceProvider` mengikatnya ke `SourceObservationRepository` — bukan ke perekam in-memory. `capture()` selalu `insertGetId`, tidak pernah `updateOrInsert`. Tabel kosong sebelumnya hanya karena belum ada akuisisi sejak W03 membuatnya.

### `F-005` — kebocoran rahasia ke envelope immutable

Uji redaksi menemukan **dua kebocoran**, dan keduanya berat karena tabel ini immutable: rahasia yang tertulis di sini tertulis permanen.

| Kebocoran | Sebab |
|---|---|
| `sanitized_request_identity` menyimpan `?token=super-secret-token` apa adanya | field-nya **bernama** sanitized, tetapi tidak ada yang menyanitasinya — pemanggil dipercaya sudah membersihkannya |
| `bounded_payload_body` menyimpan `{"crumb":"super-secret-token"}` | dua pola redaksi tidak sepakat: `crumb` teredaksi sebagai query parameter tetapi lolos sebagai field JSON |

`crumb` adalah kredensial sesi Yahoo. Kontrak `Source_Data_Acquisition_Contract_LOCKED.md` melarang credential, API key, cookie rahasia, authorization header, dan **sensitive query value** masuk envelope maupun diagnostic sample.

Diremediasi: satu daftar kata kunci dipakai untuk ketiga bentuk — query string, field JSON, dan pasangan kunci-nilai bebas — sehingga kedua pola tidak dapat berbeda lagi. `sanitized_request_identity` kini melewati redaktor di repository, bukan bergantung pada disiplin pemanggil.

### Enam fixture exit gate

`tests/Unit/MarketData/SourceObservationImmutabilityTest.php`: rerun menambah observation baru dan tidak menimpa; payload identik tetap menghasilkan observation berbeda dengan hash yang sama, karena dua akuisisi adalah dua peristiwa; outcome `EMPTY` dan kegagalan transport terekam dengan provenance lengkap; rahasia tidak mencapai envelope tersimpan; lineage canonical hanya menerima observation yang benar-benar ada dan diterima; dan manifest hash tingkat-run bergerak saat observation bertambah.

Yang ketiga menutup hal yang halus: tanpa merekam outcome kosong dan gagal, **ketiadaan bar tidak dapat dibedakan dari ketiadaan percobaan**.

## W08 record — resilience, manual recovery, dan failure taxonomy stage 5, ditutup 2026-08-06

Exit gate: *outage/partial/empty/wrong-date/schema-change fixtures tidak menghasilkan silent readable publication atau denominator shrink.*

### Yang sudah ada

Taksonomi kegagalan sudah lengkap dan terpakai: 15 reason code sumber di adapter, dan matriks state di `FinalizeDecisionService::enforceStateMatrix()` menolak kombinasi mustahil dengan `LogicException` — `READABLE` menuntut `SUCCESS` **dan** `coverage_gate_status` `PASS`, sedangkan `FAILED`/`HELD` dipaksa `NOT_READABLE`. Retry, backoff, dan throttle terkonfigurasi dan terpakai.

### `F-005b` — circuit breaker terkonfigurasi tanpa implementasi

`market_data.provider.circuit_breaker_error_rate` bernilai `0.5` sejak awal, dan bagian **Source access self-protection (LOCKED)** pada `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` mewajibkannya. Pencarian di `app/` menghasilkan **nol** kemunculan: konfigurasi itu tidak pernah dibaca siapa pun.

Yang dilindunginya bukan run, melainkan akses. Aturan retry sudah melindungi run; tidak ada yang melindungi sumber gratis non-resmi dari volume retry platform sendiri. Universe sebesar ~950 menerbitkan ratusan request per tanggal sebelum retry apa pun, sehingga berjalan terus menembus kegagalan menyeluruh justru melipatgandakan beban tepat ketika sumber sedang menolak.

Diremediasi di `PublicApiEodBarsAdapter`: breaker membuka pada rasio kegagalan melewati ambang, dengan lantai sampel `max(5, 5% universe)` agar satu galat transien di awal run tidak menghentikan tanggal yang sebenarnya sehat. Penghentian itu **wajib terlihat** — breaker menulis `RUN_SOURCE_CIRCUIT_BREAKER_OPEN` beserta ambang, jumlah percobaan, dan jumlah baris diterima ke telemetry, karena berhenti diam-diam akan tampak persis seperti universe yang memang kecil.

### Bukti exit gate

`tests/Unit/MarketData/SourceFailureResilienceTest.php` menjalankan kelima fixture kegagalan melalui `FinalizeDecisionService` dan menuntut tiga hal dari masing-masing: tidak pernah `READABLE`, tidak pernah tanpa reason code, dan tidak pernah menggeser denominator. Kontrol positif disertakan — tanpa itu seluruh assertion di atas akan lulus pada service yang menolak tanpa syarat, yang tidak membuktikan apa pun tentang penanganan kegagalan.

Denominator adalah besaran yang menanggung beban di sini. Coverage adalah available/expected, sehingga kegagalan provider yang **juga** mengecilkan expected akan menaikkan rasio persis ketika lebih sedikit instrumen berhasil diambil: gate akan terbaca paling sehat pada saat terburuknya.

Bukti produksi, 68.411 run:

| Probe | Hasil |
|---|---|
| run `FAILED`/`HELD` dengan `publishability_state = READABLE` | `0` |
| run `FAILED`/`HELD` tanpa `final_reason_code` | `0` |
| run `READABLE` dengan `coverage_available_count = 0` | `0` |
| tanggal dengan denominator provider-failure lebih kecil dari denominator sukses | `0` |
| rentang denominator, run gagal vs run sukses | `807–951` vs `807–951` |

Perbandingan pertama yang saya jalankan membandingkan `MIN(gagal)` dengan `MAX(sukses)` dan menghasilkan 750 tanggal "menyusut". Itu artefak kueri: mengambil agregat berlawanan dari dua kelompok memproduksi arah yang dicari. Setelah dikontrol terhadap hari eksekusi dan dibandingkan setara, arahnya berbalik — 104 tanggal justru berdenominator **lebih besar** saat gagal (arah yang aman, karena memperketat gate) melawan 1 tanggal lebih kecil, dan tanggal tunggal itu ber-reason `RUN_LOCK_CONFLICT`, sebuah hasil konkurensi internal, bukan kegagalan provider. Dicatat terpisah sebagai `F-006`.

## W09 record — import-only dan canonical RAW stage 8, ditutup 2026-08-06

Exit gate: *zero placeholder, provider `adj_close` sebagai RAW close, direct publish, dan untraceable row tidak mungkin masuk canonical readable path.*

### Yang sudah ada

Tiga dari empat larangan sudah tegak dan terbukti pada produksi. Atas 756.329 bar: nol harga nol atau negatif, nol pelanggaran urutan OHLC, nol volume negatif. Import juga sudah import-only — `EodBarsIngestService` tidak memanggil seal maupun promote, dan menolak tanggal yang sudah punya current publication dengan mengarahkan ke jalur correction/reseal.

### `F-007` — penjaga lineage yang menjaga dirinya sendiri

Larangan keempat tidak tegak sama sekali, dan bentuk kegagalannya patut dicatat utuh.

`EodBarsIngestService` menggerbangi seluruh penulisan lineage di balik satu bendera:

```php
$strictLineage = ! empty($run->config_snapshot_id);
```

Sementara `assertRequiredLineage()` yang dijaga bendera itu berisi, sebagai pemeriksaan terakhirnya:

```php
if (empty($run->config_snapshot_id)) { throw ...CONFIG_SNAPSHOT_REQUIRED... }
```

Pemeriksaan itu **hanya berjalan setelah prasyaratnya sendiri terpenuhi**, sehingga cabangnya tidak terjangkau secara konstruksi. Dan karena binding config tidak pernah terisi pada satu pun dari 71.917 run (`P1-25`), bendera itu tidak pernah bernilai benar: kewajiban lineage tidak pernah dieksekusi sekali pun, dan seluruh 756.329 baris canonical ditulis dengan `source_observation_id`, `listing_id`, `canonicalization_version`, `price_product_code`, `quality_state`, dan `config_snapshot_id` bernilai `NULL`.

Akarnya adalah dua kewajiban yang disatukan. Keterlacakan menjawab *dari mana baris ini berasal*; binding config menjawab *dengan konfigurasi apa ia dihasilkan*. `Canonicalization_Contract_EOD_Bars.md:138` melarang memancarkan untraceable row **tanpa syarat apa pun**, sedangkan gate konfigurasi punya subjek berbeda: `Platform_Config_Registry_LOCKED.md:31` mengikatnya pada *sealed publication*, yaitu sealing dan consumer readability, bukan import. Membuat yang pertama bergantung pada yang kedua adalah yang melubanginya.

Diremediasi: keterlacakan menjadi tak bersyarat, dan ketiadaan binding config dicatat sebagai `CONFIG_UNBOUND` — bukan membatalkan keterlacakan. Baris yang tidak dapat membuktikan asalnya **ditolak ke invalid store dengan reason code**, bukan dibatalkan seluruh tanggalnya: satu instrumen yang tak teresolusi bukan alasan membuang ~950 lainnya, dan pemisahan missing-versus-invalid justru tugas stage ini. Tiga reason code baru didaftarkan di registry dan seed.

Penulisan mentah `DB::table()` untuk manifest binding dipindahkan ke `EodPublicationRepository::bindCandidateAcquisitionProvenance()`. Application service tidak semestinya menulis tabel langsung, dan setelah dipindahkan manifest hash terikat pada setiap kandidat, bukan hanya ketika config ada.

Diverifikasi bahwa remediasi ini tidak mematikan produksi: resolver temporal mengembalikan `listing_id` untuk **500 dari 500** ticker aktif pada `2026-07-28`, dan kedua adapter sudah memasok `source_observation_id` sejak W07. Datanya selama ini tersedia dan dibuang.

### Koreksi terhadap keputusan saya sendiri

Saya sempat mempertahankan penyimpanan provider `adj_close` pada baris canonical dengan alasan `Canonicalization_Contract_EOD_Bars.md:29` mengizinkannya sebagai *nullable lineage field*. Suite menolaknya: `MarketDataPipelineIntegrationTest` sudah menegaskan *provider adjusted close remains only in raw observation evidence*. Keputusan platform lebih ketat dari pembacaan saya, dan `:16` memang melarangnya menjembatani layer raw dan adjusted secara implisit. Dikembalikan ke `null`.

### Bukti exit gate

`tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php` menguji keempat larangan pada titik satu-satunya tempat baris canonical dapat lahir, yaitu apa yang diserahkan service kepada artifact writer. Menguji baris tersimpan hanya akan membuktikan korpus sekarang bersih tanpa membuktikan baris berikutnya tidak dapat mengotorinya.

| Larangan | Fixture |
|---|---|
| zero placeholder | harga nol ditolak dengan `BAR_NON_POSITIVE_PRICE` |
| provider `adj_close` sebagai RAW close | `adj_close` `null` pada baris canonical, `close` mentah utuh |
| untraceable row | observation hilang, dan observation ada tetapi tidak accepted |
| direct publish | seal dan promote nol kali dipanggil pada import yang sukses |

Ditambah tiga yang menutup bentuk kegagalan aslinya: baris canonical membawa keterlacakan lengkap; keterlacakan tidak bergantung pada binding config; dan satu baris tak terlacak tidak membuang baris yang terlacak.

### Batas kapabilitas

**Yang diperbaiki adalah mekanismenya, bukan korpusnya.** 756.329 baris lama tetap tidak terlacak dan tetap berada di jalur readable. Mengisi kolomnya sekarang dilarang — itu akan melekatkan observation yang bukan penghasil baris tersebut, kebohongan yang lebih besar daripada lineage yang hilang, persis alasan `DOC-71` menolak reseal atas artefak `CONFIG_UNBOUND`. Penutupannya menuntut re-ingest berbukti. Dicatat sebagai `F-007` / `P1-29`, mengikuti aturan yang sama: gate yang tidak pernah ditegakkan tidak membuat korpusnya conformant lewat kesunyian.

## W10 record — publication, seal, pointer, dan correction lifecycle stage 9, ditutup 2026-08-06

Exit gate: *failed build/reseal/correction tidak mengubah current pointer; prior publication tetap repeatable; concurrent read melihat tepat satu publication.*

### Yang sudah ada

Integritas pointer sudah kuat dan terbukti pada produksi. Atas 64.092 publikasi: **844 current untuk tepat 844 tanggal berbeda, nol tanggal dengan lebih dari satu current**. Tidak satu pun current yang belum tersegel, tanpa `bars_batch_hash`, atau menunjuk run `FAILED`/`HELD`. State machine `FinalizeDecisionService` juga sudah menolak kombinasi mustahil sejak W08.

### `F-008` — berkas terketat di repository justru menegakkan nol

`../ops/History_Table_Immutability_Guards_LOCKED.sql` mendefinisikan enam trigger append-only untuk `eod_bars_history`, `eod_indicators_history`, dan `eod_eligibility_history`. `information_schema.TRIGGERS` memuat **nol** di antaranya, sementara domain watchlist memuat empat belas milik sendiri. **56.138.923 baris history tidak punya proteksi tingkat database sama sekali.**

Sebabnya bukan kelalaian murni: berkas itu **tidak dapat dipasang apa adanya**. Ia memblokir setiap `UPDATE` dan `DELETE` tanpa syarat, sedangkan rule 7 `Canonical_Row_History_and_Versioning_Policy_LOCKED.md` mensyaratkan snapshot set *"appended/frozen atomically with the seal/publication transition"* — artinya set itu dirakit selagi publikasinya masih candidate. Memasang guard tanpa syarat akan memblokir perakitan yang justru diwajibkan, dan mematikan seluruh alur correction. Rule 9 melarang update/delete atas konten snapshot **yang sudah tersegel**, dan hanya itu.

Jadi dokumen dan implementasi tidak sekadar terlambat disinkronkan; keduanya **berselisih tentang apa itu history**. Berkas guard diperbaiki menjadi seal-aware: menolak persis yang ditolak rule 9, mengizinkan persis yang diwajibkan rule 7. Ini penerapan aturan `LOCKED` yang sudah berlaku — `LOCKED` melindungi dari perubahan tak sengaja, bukan dari koreksi yang dibuktikan.

Enam trigger dipasang dan diuji langsung terhadap produksi di dalam transaksi yang di-`ROLLBACK`:

| Percobaan | Publikasi | Hasil |
|---|---|---|
| `UPDATE` history | `1` (SEALED) | ditolak — *snapshot of a sealed publication is immutable* |
| `DELETE` history | `1` (SEALED) | ditolak |
| `UPDATE` history | `4011` (UNSEALED) | 1 baris berubah |
| `DELETE` history | `4011` (UNSEALED) | 1 baris terhapus |

`eod_bars_history` tetap `56.138.923` baris sesudahnya, sama persis dengan baseline W00.

### `F-009` — tiga jalur aplikasi menulis history tanpa penjaga seal

Guard database saja tidak cukup, dan pemeriksaan lapisan aplikasi ternyata bolong di tiga tempat.

| Jalur | Keadaan sebelumnya |
|---|---|
| `discardCandidatePublication()` | menghapus ketiga snapshot set **dan** baris publikasinya, tanpa satu pun pemeriksaan seal — namanya candidate, tetapi hanya namanya yang membatasi |
| `replaceBars(..., useHistory: true)` | cabang live dijaga `assertLiveArtifactMutationAllowed`, cabang history tidak dijaga apa pun |
| `upsertBarsPartial(..., useHistory: true)` | bentuk yang sama |

Yang pertama paling berat: ia menghapus baris snapshot beserta baris publikasinya, sehingga sebuah publikasi tersegel dapat lenyap tanpa meninggalkan jejak bahwa ia pernah ada. `assertPublicationMutable()` sudah tersedia di repository yang sama dan tidak dipanggil.

Diremediasi dengan `assertHistorySnapshotMutable()` pada ketiga jalur artifact dan `assertPublicationMutable()` pada discard. Kedua lapisan disengaja tidak redundan: guard aplikasi tidak menolong terhadap sesi SQL langsung, dan guard database tidak menolong bila schema dipulihkan tanpa trigger.

### Bukti exit gate

`tests/Unit/MarketData/PublicationSealPointerLifecycleTest.php`, sembilan fixture. Setiap larangan diuji dua sisi — menolak yang tersegel **dan** mengizinkan yang masih candidate — karena penjaga yang tidak dapat membedakan keduanya bukan memblokir operasi normal, ya tidak melindungi apa pun.

| Bagian exit gate | Fixture |
|---|---|
| failed build tidak mengubah pointer | discard candidate gagal, pointer tetap di publikasi 12 |
| prior publication repeatable | snapshot lama tetap melaporkan `108` setelah disupersede oleh `112` |
| tepat satu publication terlihat | satu current per tanggal; tabel pointer menolak baris kedua secara struktural |
| no in-place rewrite | sealed ditolak pada discard dan pada artifact writer; candidate tetap dapat ditulis ulang |

## W11 record — corporate-action event dan factor lifecycle stage 10, ditutup 2026-08-06

Exit gate: *price jump/proximity/provider adjusted field tidak dapat membuat verified action/factor atau mengubah history. Tidak ada keputusan yang mencapai published output memakai band/floor/tick tanpa sumber dan effective date.*

### Yang sudah ada

Sisi penulis sudah ditutup pada sesi sebelumnya. `CorporateActionDerivationService` kini permukaan kompatibilitas non-mutating: `derive(true)` dan `checkRecordedActions(true)` sama-sama melaporkan `DETECTION_ONLY` dan `mutation_performed => false`, sehingga tidak ada peristiwa baru yang dapat disintesis dari anomali harga. Taksonomi tipe aksi juga fail-safe: tipe tak terpetakan jatuh ke `SCALED`, bukan ke `NONE`.

### `F-010` — pembacanya tidak pernah ikut ditutup

Penulis berhenti; pembaca tidak. `EventRiskSourceRepository::resolveAdjustmentFactorsForTickerIds()` menyaring hanya pada nilai faktornya — bukan `NULL`, lebih besar dari nol, tidak sama dengan satu — tanpa satu pun pemeriksaan asal-usul. Lima belas baris ber-`adjustment_source = DERIVED_FROM_PRICE_SERIES` karena itu mengalir utuh ke `price_adjustment_factors`, lalu ke `EodIndicatorsComputeService`, lalu ke indikator terpublikasi.

Lingkaran yang tertutup di situ perlu dinamai: deret diperhalus memakai angka yang **diturunkan dari celah yang sedang dijelaskan**. Sebuah diskontinuitas membuktikan ada sesuatu yang terjadi; ia tidak dapat menetapkan apa, dengan terms apa, atau berlaku kapan. Setelah rasio itu dipakai sebagai faktor, hasilnya tidak lagi dapat dibedakan dari adjustment bersumber.

Cacatnya berlapis dua, dan lapis kedua lebih halus. `isAdjustable()` juga dipakai untuk memutuskan karantina: baris yang membawa faktor "dapat dipakai" dianggap sudah teradjust, sehingga **flag kontaminasinya ditekan**. Jadi faktor turunan tidak sekadar menghaluskan deret — ia sekaligus menghapus penanda bahwa ada yang perlu dicurigai. Jendela terpublikasi bersih sambil membawa diskontinuitas tak terjelaskan, keadaan yang lebih buruk daripada menyesuaikan maupun mengarantina secara jujur.

Diremediasi dengan satu aturan yang diterapkan pada kedua jalur: faktor turunan tidak adjustable dan tidak masuk kueri adjustment, sehingga barisnya jatuh ke karantina sebagaimana mestinya.

Diskriminatornya sengaja `adjustment_source`, bukan `source_name`, dan produksi membuktikan pilihan itu penting: **5 baris ber-`source_name = idx_corporate_action` ternyata membawa `adjustment_source = DERIVED_FROM_PRICE_SERIES`** — identitas peristiwanya bersumber IDX, tetapi faktornya disimpulkan dari deret harga dan dituliskan ke baris IDX itu. Peristiwa yang bersumber tidak membuat faktornya bersumber.

### Yang terungkap saat filter dipasang

| Probe | Hasil |
|---|---|
| faktor dipakai sebelum filter | `15` |
| faktor dipakai sesudah filter | **`0`** |
| aksi `SCALED` bersumber IDX tanpa terms dapat pakai | `126` |
| baris IDX membawa `ratio_from`/`ratio_to` | `2` dari `520` |

**Seluruh kapabilitas adjustment platform selama ini berjalan di atas rasio yang disimpulkan sendiri.** Nol faktor bersumber tersisa. Ini bukan regresi yang diperkenalkan W11 melainkan keadaan yang selama ini tertutupi oleh faktor turunan: 126 jendela yang seharusnya terkarantina tampak bersih karena dihaluskan oleh tebakan. Konsekuensi sesudahnya benar dan mahal — jendela-jendela itu kini terkarantina sebagai terkontaminasi.

Dicatat sebagai `P1-31`. Penutupannya menuntut terms aksi korporasi otoritatif dari IDX; tidak ada kode yang dapat menutupnya.

### `F-011` — band, floor, dan tick masih konstanta

`Exchange_Market_Structure_Facts_LOCKED.md:79` melarang konstanta tak bersumber dipakai untuk keputusan apa pun yang mencapai published output. Nyatanya tidak ada tabel tier sama sekali: `min_price_idr` konstanta config `50`, dan dokumen registry sendiri mencatat band sebagai skalar hardcoded `0.35` berstatus placeholder. Lima aksi berstatus `GAP_BEYOND_EXCHANGE_BAND` diputuskan memakai band itu.

Dampaknya terhadap published output berkurang drastis setelah `F-010` ditutup, karena verdict band-based tidak lagi menghasilkan faktor yang terpakai. Yang tersisa adalah larangan menyebutnya exchange-verified. Dicatat sebagai `P1-30`; penutupannya menuntut tabel tier bersumber dan ber-effective-date dari IDX — rekonsiliasi eksternal, bukan pekerjaan kode.

### Koreksi terhadap perubahan saya sendiri

Saya sempat menambahkan `PRICE_RESCALE_UNCLASSIFIED` ke seed mirror SQLite dengan anggapan itu drift terhadap produksi yang memuatnya. Suite menolak: `MarketDataSqliteSchemaSyncTest` menegaskan tipe itu **tidak boleh** di-seed sebagai action type yang mengotorisasi adjustment. Ketiadaannya disengaja, dan alasannya sama persis dengan yang ditutup work order ini — men-seed-nya akan membiarkan rescale turunan mengotorisasi penyesuaiannya sendiri. Perubahan mirror dibatalkan.

### Bukti exit gate

`tests/Unit/MarketData/CorporateActionCandidateBoundaryTest.php`, enam fixture: faktor turunan tidak pernah mencapai jalur adjustment; faktor bersumber tetap menyesuaikan; aksi turunan tidak menekan kontaminasi; faktor netral tidak dianggap adjustment; service derivasi menolak `apply` yang sungguh-sungguh diminta; dan tipe tak terpetakan absen alih-alih diam-diam aman.

## W12 record — coherent analytical price products stage 11, ditutup 2026-08-06

Exit gate: *one run/field vector cannot mix `close`, provider `adj_close`, factor sets, atau RAW/adjusted scales; unresolved factor contaminates/nulls rather than falls back.*

### Yang sudah ada

Bentuk dasarnya benar dan patut dicatat. `RAW` tetap immutable: penyesuaian terjadi **di memori** pada `IndicatorVectorService::applyPriceAdjustment()`, sehingga deret as-traded tidak pernah dirusak dan tidak ada baris historis yang ditulis ulang. Kompensasi faktor juga sudah benar secara aritmetika — sebuah bar dikalikan hasil kali setiap faktor yang `ex_date`-nya sesudah bar itu, sehingga dua split dalam satu jendela ter-compound dengan tepat, dan bar sesudah `ex_date` terakhir dibiarkan karena sudah berada pada skala kini. `MarketDataPriceReadRepository` pun sudah memisahkan penamaan dengan hati-hati: `raw_close` versus `provider_adjusted_close_evidence`, yang kedua diberi nama *evidence* dan bukan harga.

### `F-012` — provider `adj_close` ikut dikalikan faktor platform

Loop penyesuaian mencakup `adj_close` bersama `open`, `high`, `low`, dan `close`. Provider `adj_close` adalah **observasi**, bukan produk platform; mengalikannya dengan faktor struktural platform menghasilkan angka yang bukan keduanya — bukan yang dilaporkan provider, dan bukan produk yang platform definisikan. Hibrida itu lalu duduk dalam satu vektor bersama `close`, yang persis bentuk pencampuran yang dilarang exit gate.

W09 sudah menghentikan `adj_close` masuk baris canonical baru, jadi ke depan tidak ada yang dirusak. Tetapi 756.329 baris lama memuatnya, sehingga pencampuran ini hidup di atas data historis. Diremediasi dengan mengeluarkan `adj_close` dari loop; ia dibiarkan sebagaimana diobservasi.

### `F-013` — vektor tidak dapat menyatakan skalanya sendiri

`eod_indicators` memiliki kolom `price_product_code`, dan **756.328 baris seluruhnya `NULL`** — tidak ada penulis sejak kolom itu dibuat pada W03.

Konsekuensinya bukan sekadar dokumentasi yang kurang. Baris berbasis `RAW` dan baris berbasis `STRUCTURAL_ADJUSTED` duduk dalam satu kolom tanpa dapat dibedakan, padahal keduanya **tidak sebanding**: jendela yang melintasi split 1:5 berbeda dari kembarannya yang tak disesuaikan sebesar rasio split, bukan beberapa persen. Konsumen yang membandingkan keduanya membandingkan dua besaran berbeda tanpa cara mengetahuinya.

Diremediasi: `applyPriceAdjustment()` kini melaporkan produk yang benar-benar dihasilkannya, dan `buildRow()` menuliskannya ke vektor. Labelnya menggambarkan **output, bukan input** — faktor yang ada tetapi tidak mengubah satu bar pun tetap menghasilkan `RAW`, karena melaporkan `STRUCTURAL_ADJUSTED` hanya karena sebuah faktor ada di suatu tempat akan membuat label berbohong tentang apa yang terjadi.

### `F-014` — fallback yang mengarang kode produk

`MarketDataPriceReadRepository` memakai `$row->price_product_code ?: 'RAW'`. Baris yang tidak pernah mencatat produknya tidak menjadi `RAW` karena dibaca. Karena seluruh 756.329 baris lama membawa `NULL`, fallback itu menegaskan klaim skala yang barisnya sendiri tidak pernah buat — bentuk yang sama dengan `PROJECTED` menjadi `EXPECTED` pada W06. Diganti `null` disertai `price_product_reason_code => PRICE_PRODUCT_UNRECORDED`.

### Koreksi terhadap perubahan saya sendiri

Implementasi pertama saya membaca kode produk lewat helper `config()` di dalam `applyPriceAdjustment()`. Suite langsung menolak dengan *Target class [config] does not exist* pada dua puluh test: service ini dirancang dapat dipanggil tanpa container yang di-boot, dan saya baru saja merusaknya. Diganti konstanta domain `MarketDataScope::RAW_PRODUCT` dan `::STRUCTURAL_ADJUSTED_PRODUCT`.

### Bukti exit gate

`tests/Unit/MarketData/CoherentPriceProductBoundaryTest.php`, enam fixture: provider `adj_close` tidak diskalakan faktor platform; vektor yang disesuaikan menyatakan `STRUCTURAL_ADJUSTED`; vektor tanpa faktor menyatakan `RAW`; faktor yang tidak mengubah apa pun tetap `RAW`; label sampai ke vektor terpersist; dan seluruh field OHLC bergerak pada satu skala sementara volume bergerak berlawanan.

### Batas kapabilitas

Yang diperbaiki mekanismenya. **756.328 baris indikator lama tetap tanpa label**, dan sebagian di antaranya dihitung memakai 15 faktor turunan yang baru diblokir pada W11 — sehingga label yang benar untuk baris-baris itu belum tentu dapat direkonstruksi tanpa hitung ulang. Pengisian retroaktif dilarang atas alasan yang sama seperti `P1-29`. Dicatat sebagai `P1-32`.

## W13 record — actual dan proxy daily market metrics stage 14, ditutup 2026-08-06

Exit gate: *actual dan proxy tidak dapat berbagi misleading name/meaning; adjusted price × raw volume tidak dipakai sebagai proxy.*

### Keadaan awal

Kedua kolom bernama eksplisit sudah ada sejak W03 — `adv20_traded_value_idr_actual` dan `adv20_close_volume_proxy_idr` — dan `MarketDataReadProductRepository` sudah memilih keduanya untuk konsumen. Yang tidak ada adalah penulisnya:

| Kolom | Terisi |
|---|---:|
| `dv20_idr` | `735.719` |
| `adv20_traded_value_idr_actual` | `0` |
| `adv20_close_volume_proxy_idr` | `0` |

Ini bentuk paling tepat dari pelanggaran bagian pertama exit gate: **satu-satunya field yang terisi adalah yang namanya ambigu**, sementara dua field yang justru membedakan actual dari proxy kosong. Konsumen menerima angka turnover tanpa cara mengetahui apakah ia nilai transaksi sebenarnya atau sebuah pendekatan.

### `F-015` — proxy dihitung pada deret yang disesuaikan

`averageTurnover()` dipanggil atas `$bars`, dan sejak W12 diketahui bahwa `$bars` adalah hasil `applyPriceAdjustment()`. Jadi proxy dihitung sebagai harga tersesuaikan × volume tersesuaikan.

Untuk split hal itu tidak berdampak: faktor harga dan volume saling berkebalikan, sehingga hasil kalinya invarian. Bahayanya muncul pada aksi yang **menskalakan harga tanpa menskalakan volume**. Registry menetapkan `RIGHTS_ISSUE` ber-`price_continuity_impact = SCALED` dan `volume_continuity_impact = NONE`, dan ada 68 di antaranya bersumber IDX. Di sana volume factor bernilai `1,0` sementara price factor tidak, sehingga hasilnya persis **adjusted price × raw volume** — yang dilarang exit gate dengan kata-kata itu juga.

Besarannya terukur, bukan teoretis. Fixture rights-issue dengan faktor `0,8` menghasilkan `80.000` di bawah perilaku lama versus `100.000` yang benar: turnover dinyatakan **20% lebih rendah sepanjang seluruh jendela 20 hari**, tepat pada angka yang dipakai filter likuiditas untuk memutuskan sebuah instrumen layak diperdagangkan. Untuk weekly swing IDX, memfilter berdasarkan likuiditas yang understated adalah cara diam-diam membuang kandidat yang sebenarnya memenuhi syarat.

Diremediasi: deret as-traded dipertahankan berdampingan dengan yang disesuaikan, dan proxy dihitung atas deret mentah sesuai `Volume_and_Turnover_Normalization_LOCKED.md:27` — `RAW close × RAW volume`. Indikator lain tetap berjalan di atas deret tersesuaikan sebagaimana mestinya.

Test-nya diverifikasi diskriminatif: dijalankan terhadap kode lama ia gagal dengan `80.000` melawan `100.000`. Fixture yang lulus pada kedua sisi perbaikan tidak membuktikan apa pun.

### Penamaan actual versus proxy

Kedua field kini ditulis. `adv20_close_volume_proxy_idr` membawa nilai proxy, dan `dv20_idr` dipertahankan sebagai alias legacy dengan nilai yang sama.

`adv20_traded_value_idr_actual` **tetap `NULL`, dan itu jawaban yang benar** — bukan pekerjaan yang belum selesai. Provider tidak memasok traded value sama sekali; adapter sudah mendeklarasikannya sejak W02 lewat `provides_actual_traded_value => false`. Kontrak mensyaratkan `NULL` ketika actual tidak tersedia, dan menuliskan proxy ke field actual bukan pendekatan melainkan pernyataan keliru.

Field proxy juga ditambahkan ke peta karantina. Ia membawa nilai yang sama atas jendela yang sama, sehingga terkontaminasi oleh peristiwa yang sama; melewatkannya akan menyisakan jendela terkarantina tetap terbaca lewat namanya yang lebih jelas.

### Bukti exit gate

`tests/Unit/MarketData/ActualVersusProxyMetricBoundaryTest.php`, lima fixture: aksi harga-saja tidak mendistorsi proxy; split berkebalikan membiarkan proxy tidak berubah — dipasangkan agar terlihat perbaikannya soal memakai deret as-traded, bukan soal satu jenis aksi kebetulan saling meniadakan; proxy adalah `RAW close × RAW volume`; actual `NULL` alih-alih diisi proxy; dan actual serta proxy menempati field terpisah.

### Batas kapabilitas

**735.719 baris `dv20_idr` lama tetap dihitung pada deret tersesuaikan.** Berapa banyak yang benar-benar salah bergantung pada berapa banyak jendela 20 hari yang melintasi aksi harga-saja, dan **itu belum saya ukur**. Dicatat sebagai `P1-33`; penutupannya menuntut recompute berbukti.

## W14 record — deterministic indicators dan dependency graph stage 15, ditutup 2026-08-06

Exit gate: *independent short/long/gap/action/correction oracles lulus; ATR state stabil sejak dataset/listing boundary dan correction impact tidak dipotong secara salah.*

### `F-016` — ATR di-seed pada jendela geser

Blueprint stage 15 menyatakannya langsung: *"Wilder ATR memakai stable seed dan recursive state dari dataset/listing boundary, bukan sliding-window reseed."*

`wilderAtr()` men-seed pada rerata `window` nilai true range pertama **di dalam array bar yang dimuat**, dan `EodIndicatorsComputeService` memuat jendela geser 60 hari perdagangan yang berakhir pada tanggal yang diminta. Jadi setiap tanggal memperoleh titik seed-nya sendiri. Akibatnya bukan sekadar penyimpangan kecil: deret yang dihasilkan **bukan Wilder ATR sama sekali**, melainkan rangkaian aproksimasi 60-bar yang di-seed secara independen, karena ATR(D) dan ATR(D+1) tidak berbagi satu recursive state.

Saya mengukurnya sebelum memutuskan, terhadap 120 ticker produksi pada `2026-07-28`:

| Statistik | Selisih terhadap nilai ber-seed boundary |
|---|---:|
| median | `0,34%` |
| persentil ke-90 | `1,62%` |
| maksimum | `72,9%` |
| melebihi `1%` | `19` dari `120` |
| melebihi `5%` | `2` dari `120` |

Median yang kecil bukan alasan membiarkannya. `atr14_pct` adalah masukan volatilitas untuk position sizing dan penempatan stop, sehingga kesalahan `72,9%` pada satu instrumen berpindah utuh ke ukuran posisi pada instrumen itu — dan yang salah bukan instrumen yang bisa diprediksi sebelumnya.

Diremediasi dengan deret ATR terpisah ber-anchor `MarketDataScope::DATASET_START`. Deret itu sengaja **hanya memuat empat kolom** yang dibutuhkan true range: memuat baris bar penuh berisi ~25 kolom untuk ~756.000 baris demi satu indikator akan menukar perbaikan kebenaran dengan masalah memori. Indikator lain tetap berjalan di atas jendela 60 hari yang sudah ada.

### Oracle independen

Ini yang sebelumnya nol — `DOC-81` mencatat nol golden fixture. `tests/Unit/MarketData/IndicatorIndependentOracleTest.php` menyediakan kelima kelas yang diminta exit gate, dan **nilai harapannya dihitung dari definisi formula, bukan dibaca balik dari implementasi**. Oracle yang mengambil jawabannya dari kode yang diuji hanya membuktikan kode itu konsisten dengan dirinya sendiri, yang persis bentuk kegagalan yang dijaga tulang punggung audit ini.

| Kelas oracle | Nilai yang dihitung tangan |
|---|---|
| short | `roc20 = 159/139 − 1`; `ma20 = 149,5`; `ma50 = 134,5` |
| long | `hh20 = 160`; `ll20 = 139`; `dv20 = 149,5 × 1000` |
| ATR | true range konstan `2` pada deret ramp, sehingga ATR = `2` berapa pun tempat seed-nya — satu-satunya kasus di mana penempatan seed tidak dapat menyembunyikan galat pada rekursinya sendiri |
| seed stability | muat 60 bar ber-anchor boundary harus sama persis dengan perhitungan riwayat penuh |
| gap | `NULL` di dalam jendela wajib menghasilkan indikator `NULL`, bukan jendela yang diam-diam diperpendek |
| action | jendela yang melintasi aksi kontaminatif terkarantina, termasuk nama proxy yang lebih jelas |
| correction | koreksi `+20` di dalam rerata 20 hari menggeser `ma20` tepat `1` |

### Koreksi terhadap kesalahan saya sendiri

Ekspektasi `dv20` yang pertama saya tulis adalah ekspresi aritmetika yang tidak masuk akal dan menghasilkan `249.500`; implementasinya benar dengan `149.500`. Oracle-nya yang salah, bukan kodenya. Dan oracle action pertama saya memanggil `calculateIndicators()` langsung sehingga melewati karantina yang justru diterapkan `buildRow()` — dialihkan ke jalur yang ditempuh run sebenarnya.

### Batas kapabilitas

Yang diperbaiki mekanismenya. **Korpus ATR lama tidak dihitung ulang**, dan tabel selisih di atas adalah ukuran seberapa jauh nilai tersimpan menyimpang. Dicatat sebagai `P1-34`. Perlu dinyatakan juga bahwa oracle di atas berjalan pada deret sintetis yang dipilih agar dapat dihitung tangan; ia membuktikan formulanya, bukan perilaku terhadap data pasar sebenarnya.

## W15 record — temporal coverage expectation dan delivery stage 12, ditutup 2026-08-06

Exit gate: *provider absence, dormancy, zero volume, illiquidity, current active state, atau missing status tidak dapat secara diam-diam memperbaiki coverage.*

### Yang sudah ada

`CoverageGateEvaluator` sudah menghitung seluruh suku penyebut dengan benar: universe mentah, pengecualian suspensi, pengecualian dormansi, penyebut yang tersisa, jumlah terkirim, dan jumlah hilang. Universe kosong juga sudah `NOT_EVALUABLE`, bukan `PASS` — membagi dengan nol instrumen akan menjadi cara termurah melewati gate.

### `F-017` — perhitungannya benar, penyimpanannya tidak

Kata yang menanggung beban pada exit gate adalah **diam-diam**. Mengecualikan instrumen yang benar-benar dorman itu sah; mengecualikannya tanpa mencatat bahwa pengecualian terjadi tidak. Keduanya menghasilkan angka penyebut yang sama, dan hanya catatan pengecualian yang membedakannya.

Catatan itu tidak ada. Kolomnya dibuat pada W03 dan hampir seluruhnya kosong:

| Kolom | Terisi dari 71.917 |
|---|---:|
| `coverage_expected_count` | `0` |
| `coverage_delivered_count` | `0` |
| `coverage_delivered_valid_count` | `0` |
| `coverage_expectation_unknown_count` | `0` |
| `coverage_bar_not_expected_count` | `84` |

Yang tersimpan hanya `coverage_universe_count`, dan namanya menyesatkan: `MarketDataPipelineService` menuliskan `expected_universe_count` — nilai **sesudah** penyaringan suspensi dan dormansi — ke kolom bernama *universe*. Jadi universe mentah tidak pernah tersimpan, dan **jumlah pengecualian suspensi tidak tersimpan sama sekali**.

Konsekuensinya tepat sebagaimana dirumuskan exit gate: rasio coverage tidak dapat direkonstruksi dari bukti tersimpan. Run yang mengecualikan 40 instrumen dan run yang tidak mengecualikan satu pun meninggalkan catatan yang sama. Dan karena kolom-kolom itu `NULL` alih-alih `0`, "tidak ada yang dikecualikan" tidak dapat dibedakan dari "tidak ada yang mencatat".

Diremediasi: pipeline kini menyimpan seluruh rangkaian bukti — expected, delivered, delivered_valid, expectation_unknown, dan gabungan not-expected dari suspensi maupun dormansi — sebagai integer, bukan null.

### `F-006` — akar penyebab didiagnosis, belum ditutup

Temuan W08 tentang penyebut `950 → 949 → 950` pada satu hari eksekusi ditelusuri ke sini. Akarnya: **`universeAsOf()` melakukan penulisan sebagai efek samping pembacaan.** Ia memanggil `ensureLegacyProjection()`, yang memproyeksikan seluruh ~977 ticker legacy ke `md_*` sebelum kueri dijalankan. Kuerinya sendiri deterministik untuk `$tradeDate` dan `$knownAt` tetap — tidak ada suku `now()` di dalam filter — sehingga sumber variasinya adalah proyeksi yang berjalan bersamaan, dan run yang menyimpang memang ber-`final_reason_code = RUN_LOCK_CONFLICT`.

Saya tidak menutupnya. Membuktikan perbaikan konkurensi menuntut dua proses bersamaan terhadap MariaDB, sedangkan fixture di sini berjalan serial di atas SQLite — perbaikan yang "lulus" pada harness serial tidak akan membuktikan apa pun tentang kondisi yang memunculkannya. Diagnosisnya dicatat agar pekerjaan berikutnya berangkat dari sebab, bukan dari gejala.

### Bukti exit gate

`tests/Unit/MarketData/CoverageSilentImprovementBoundaryTest.php` berjalan di atas universe 10 instrumen dengan 8 membawa bar, bukan di atas universe kosong — versi pertama saya berjalan tanpa seed sama sekali, dan atas universe kosong seluruh assertion terpenuhi oleh nol tanpa membuktikan apa pun. Satu fixture juga sempat saya tulis dengan cabang `if/else` yang lulus pada kedua arah; itu bukan bukti, dan diganti.

Enam fixture: seluruh suku penyebut dilaporkan; jumlah pengecualian berupa integer bukan null; universe mentah dan penyebut dilaporkan terpisah; rasio dapat direkonstruksi dari suku-sukunya; setiap instrumen yang meninggalkan universe terhitung di suatu tempat; dan universe kosong `NOT_EVALUABLE`, bukan lulus.

### Batas kapabilitas

Bukti baru mulai tersimpan sejak sekarang. **68.411 run coverage yang sudah ada tetap tidak dapat diaudit terhadap exit gate ini**, karena suku-suku yang dibutuhkan tidak pernah ditulis — bergabung dengan `P1-22`, yang sudah mencatat bahwa seluruh evidence coverage itu dihasilkan resolver pra-temporal.

## W16 record — explainable data usability stage 13, ditutup 2026-08-06

Exit gate: *blocked row tidak hilang; `true` tidak berarti tradable/selected; liquidity/event preference watchlist tidak masuk upstream decision.*

### Bagian ketiga sudah benar, dan itu perlu dinyatakan

`EligibilityDecisionService` membaca **hanya** tiga hal: ada tidaknya bar, ada tidaknya indikator, dan validitas indikator. Tidak ada ambang likuiditas, tidak ada preferensi event, tidak ada apa pun dari watchlist. Instrumen yang tipis perdagangannya tetap `eligible` selama datanya utuh, dan itu memang benar: likuiditas adalah preferensi milik pihak yang memilih instrumen, bukan fakta tentang apakah datanya dapat dipakai — dan screen yang tidak dapat melihat baris tipis itu tidak dapat memutuskan untuk menyertakannya.

`eligible` juga tidak membawa vonis apa pun selain itu. Keputusannya hanya mengembalikan `eligible` dan `reason_code`; tidak ada `tradable`, tidak ada `selected`.

### `F-018` — baris yang terblokir justru yang lenyap

`EodEligibilityBuildService` menyaring listing tersuspensi **keluar dari universe sebelum snapshot dibangun**. Instrumen yang terblokir karena itu tidak memperoleh baris sama sekali.

Ini membalik fungsi snapshot. Pembaca yang bertanya mengapa sebuah instrumen absen hari ini tidak memperoleh jawaban apa pun, dan **absen-karena-tersuspensi menjadi tidak dapat dibedakan dari absen-karena-belum-pernah-tercatat**. Kontrak mensyaratkan satu baris publication-bound per temporal listing dengan status tersimpan terpisah — persis kebalikan dari membuang barisnya dan tidak menyimpan apa pun.

Lapis keduanya sama dengan yang ditemukan pada W15: ketujuh kolom dimensi ada sejak W03 dan **nol terisi dari 749.685 baris** — `universe_membership_state`, `bar_expectation_state`, `delivery_state`, `canonical_quality_state`, `liquidity_state`, `temporal_status_state`, `event_risk_state` — beserta `eligibility_reasons_json`. Yang tersimpan hanya satu `reason_code` skalar, sehingga hanya alasan pertama yang selamat, dan **726.542 baris `eligible = 1` tidak membawa penjelasan apa pun**.

Diremediasi: suspensi kini dipakai sebagai lookup untuk menganotasi, bukan sebagai filter untuk membuang. Listing tersuspensi memperoleh barisnya dengan `eligible = 0`, `temporal_status_state = SUSPENSION`, `bar_expectation_state = BAR_NOT_EXPECTED`, dan alasan suspensi memimpin himpunan berurutan — ia menjelaskan ketiadaan bar, bukan sekadar mengulanginya. Dimensi delivery dan quality ikut ditulis, dan himpunan alasan disimpan sebagai JSON berurutan alih-alih satu skalar yang menghapus sisanya.

### Bukti exit gate

`tests/Unit/MarketData/EligibilityExplainabilityBoundaryTest.php`, tujuh fixture. Yang paling menjaga ke depan adalah yang memeriksa **sumber** `EligibilityDecisionService` tidak menyebut `dv20_idr`, `liquidity`, `watchlist`, `min_turnover`, maupun `event_risk_flag`: preferensi tidak bocor hari ini, dan penjaga itu membuat penambahannya kelak tidak dapat lolos tanpa terlihat.

Sisanya: bar hilang terblokir dengan alasan; indikator hilang dilaporkan terpisah dari bar hilang; penyebab spesifik indikator diteruskan alih-alih diratakan menjadi kegagalan generik; likuiditas rendah tidak memblokir; volume nol tidak memblokir; dan `eligible` tidak membawa vonis seleksi.

### Batas kapabilitas

Perbaikan berlaku untuk baris baru. **Korpus 749.685 baris lama tetap tidak memuat baris untuk listing yang tersuspensi pada tanggalnya**, dan ketujuh dimensinya tetap kosong — jumlah instrumen yang hilang dari snapshot historis belum saya ukur. Dicatat sebagai `P1-36`, dan `P1-23` yang sudah ada tetap berlaku untuk kelengkapan 19 fakta wajib.

## W17 record — versioned market-data read product stage 17 core, ditutup 2026-08-06

Exit gate: *no raw/current/master/`MAX(date)`/mixed-publication read; optional snapshot tidak dapat menjadi strategy engine dan, ketika dinonaktifkan, tidak menciptakan implied missing feature.*

### Yang sudah benar, dan bagian tersulitnya termasuk

Gateway-nya memang sudah sesuai kontrak. `MarketDataReadProductService` meresolusi satu publikasi current yang readable, mengikat setiap baris ke `publication_id`, `publication_version`, dan `run_id`, menyatakan `product_code` serta `read_model_version` miliknya sendiri, dan **gagal tertutup**: tanggal yang belum siap menghasilkan payload kosong eksplisit beserta reason code, bukan baris sesi sebelumnya. Fallback implisit akan lebih buruk daripada tidak menjawab, karena tidak dapat dibedakan dari jawaban yang benar.

Bagian tersulit exit gate juga sudah terpenuhi secara struktural. Setiap join artifact pada `MarketDataReadProductRepository` mengikat **`publication_id` dan `run_id` sekaligus**, bukan hanya tanggal. Mengikat tanggal saja akan membiarkan bar dari satu publikasi bertemu indikator dari publikasi lain di dalam satu baris, dan baris itu akan tampak lengkap sambil menggambarkan dua dataset berbeda. Pencarian `MAX(`, `max(trade_date`, dan `orderByDesc('trade_date')` di seluruh jalur baca mengembalikan nol.

### `F-019` — gate yang tidak dapat dilanggar karena tidak ada yang dapat melanggarnya

`routes/web.php` berisi 18 baris tanpa satu pun route market-data. `app/Application` dan `app/Domain` hanya memuat `MarketData`; tidak ada domain hilir di repositori ini. Pencarian pembaca langsung tabel artifact di luar lapisan persistence market-data mengembalikan **satu berkas**, `PriceScaleBreakDetectionService`, dan itu produsen internal, bukan konsumen hilir.

Jadi larangan bypass terpenuhi — tetapi terpenuhi **oleh ketiadaan pihak yang dapat melanggarnya**. Di bawah gate 12 itu bukan bukti: kesunyian dari mekanisme berbatas tidak mengatakan apa pun tentang dunia. Menandai stage ini lulus atas dasar itu saja akan mengulang persis kesalahan yang ditolak audit ini sejak awal.

Karena itu W17 tidak melaporkan pengamatan, melainkan memasang **penjaga struktural** yang baru bernilai ketika konsumen pertama muncul: pemindaian seluruh `app/` menolak pembacaan langsung `eod_bars`, `eod_indicators`, dan `eod_eligibility` dari mana pun di luar lapisan persistence, dengan satu pengecualian bernama untuk produsen internal itu. Ditambah penjaga bahwa gateway memaparkan tepat satu entry point publik — metode baca kedua akan menjadi bypass yang justru dilarang kontrak ini.

### Bukti exit gate

`tests/Unit/MarketData/ConsumerReadProductAntiBypassTest.php`, tujuh fixture: setiap join artifact terikat publikasi dan run; jalur baca tidak pernah meresolusi tanggal dengan mengambil yang terbaru; tidak ada kode di luar lapisan persistence menyentuh tabel artifact; tanggal yang belum siap mengembalikan payload kosong eksplisit; payload menyatakan produk dan versi read model-nya; gateway memaparkan tepat satu entry point; dan bentuk barisnya mencakup setiap kelompok field yang disyaratkan — identitas, fakta RAW, identitas produk analitis, indikator, alasan data-usability, dan lineage.

### Batas kapabilitas

Yang terbukti adalah bentuk dan pengikatan gateway, ditambah keutuhan batas di sekelilingnya. **Nol konsumen nyata pernah membaca produk ini**, sehingga kepatuhan read-side belum pernah diuji oleh pemakaian. Snapshot opsional stage 17 adalah lingkup `W20` dan tidak disentuh di sini. Dicatat sebagai `P1-37`.

## W18 record — exact dan as-known replay stage 18, ditutup 2026-08-06

Exit gate: *exact replay matches values/nulls/reasons/lineage/hashes; as-known replay tidak dapat melihat later identity/status/event/config/factor revisions; tidak ada strategy P&L metric menjadi market-data acceptance.*

### Klausa ketiga sudah bersih

Pencarian `pnl`, `profit`, `sharpe`, `win_rate`, dan `return_pct` pada `Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md` mengembalikan nol. Tidak ada metrik P&L strategi yang menjadi kriteria penerimaan market-data.

### `F-020` — 20.635 hasil replay adalah perbandingan diri sendiri

Sebarannya sudah cukup mencurigakan sebelum kode dibaca: **20.635 hasil, seluruhnya `MATCH`/`PASS`, nol `FAIL`, nol `BLOCKED`**, seluruhnya dari satu suite bernama `runtime_generated_valid_case`.

Nama itu ternyata menggambarkan cacatnya dengan jujur. `generateFixtureFromRun()` membangun state harapannya dengan memanggil `buildActualReplayState()` atas run yang sama. **Oracle-nya adalah subjeknya.** Perbandingan seperti itu tidak dapat menghasilkan apa pun selain `MATCH`, dan sebaran nol-FAIL itu bukan tanda kesehatan melainkan tanda bahwa tidak ada yang pernah diuji.

Lapis keduanya: `config_identity` bernilai literal `'v1'` pada seluruh 20.635 baris — satu nilai distinct. Identitas config yang menurut exit gate harus dibekukan sebenarnya konstanta, sehingga membandingkannya antara expected dan actual selalu cocok tanpa mengatakan apa pun.

Dan lapis ketiga sudah tercatat sejak `DOC-71`: replay atas publikasi `CONFIG_UNBOUND` semestinya `BLOCKED`. Seluruh korpus publikasi memang `CONFIG_UNBOUND` (`P1-25`), sehingga seharusnya nol dari 20.635 hasil itu berstatus `PASS`.

Diremediasi dengan satu gerbang admissibility yang berjalan **sebelum** pertanyaan apakah hasilnya cocok: fixture ber-`fixture_family = runtime_generated_valid_case` ditolak sebagai `REPLAY_FIXTURE_SELF_GENERATED`, dan run tanpa binding config ditolak sebagai `REPLAY_CONFIG_UNBOUND`. Keduanya menghasilkan `BLOCKED`, bukan `PASS`.

Delapan test yang sudah ada langsung gagal karena run fixture-nya tidak membawa binding config. Mengikuti aturan yang saya tetapkan pada W06, yang diperbaiki fixture-nya, bukan aturannya: sebuah fixture yang menegaskan verdict replay sedang menegaskan bahwa replay itu admissible, dan verdict atas run yang tidak dapat diikat bukan bukti.

### As-known replay: sebelumnya tidak ada sama sekali

Pencarian `as_known`, `asKnown`, dan `AS_KNOWN` di seluruh `ReplayVerificationService` mengembalikan nol. Klausa kedua exit gate tidak memiliki implementasi apa pun.

Primitifnya ternyata sudah lengkap dari W05 dan W06 — ketiga akar kebenaran temporal menerima `$knownAt`. Yang belum ada adalah bukti bahwa cutoff itu benar-benar dihormati, dan itu yang dibangun.

Exact replay dan as-known replay menjawab pertanyaan berbeda. Exact bertanya apakah platform masih menghasilkan apa yang pernah dihasilkannya. As-known bertanya **apa yang dapat diketahui platform pada suatu saat** — dan satu-satunya cara itu salah adalah membiarkan fakta yang dicatat kemudian bocor mundur, yang di dalam backtest tidak dapat dibedakan dari kemampuan meramal.

| Akar temporal | Fixture |
|---|---|
| identitas | listing yang dicatat Juni tidak terlihat pada cutoff April, tetapi terlihat tanpa cutoff |
| trading status | suspensi ber-`effective_from` Maret tetapi `recorded_at` Mei meresolusi `UNKNOWN` pada cutoff April |
| kalender | revisi kalender yang dicatat sesudah cutoff membuat bukti kalender hilang, bukan diam-diam terpakai |

Ketiganya diuji terpisah karena cutoff yang dihormati dua dari tiga akar tetap bocor.

### Batas kapabilitas

Yang ditegakkan adalah gerbangnya. **20.635 hasil replay lama tetap tidak admissible dan tidak dihitung ulang**; menandainya ulang tanpa fixture ber-author independen hanya akan memindahkan masalahnya. As-known replay terbukti pada ketiga akar temporal, tetapi **belum** pada revisi event dan factor — keduanya belum memiliki tabel revisi berisi data (`md_corporate_action_revisions` dan `md_adjustment_factors` keduanya kosong), sehingga tidak ada yang dapat bocor dan tidak ada yang dapat dibuktikan. Dicatat sebagai `P1-38`.

## W19 record — operational lifecycle, commands, observability, dan evidence stage 19, ditutup 2026-08-06

Exit gate: *setiap command memiliki bukti success/failure/concurrency/retry; operator tidak dapat mem-bypass publication safety; development frontier tidak dilaporkan keliru sebagai activated freshness.*

Assignment terbesar kedua setelah W10: 27 dokumen operasional.

### Klausa kedua sudah kuat

Operator tidak dapat mem-bypass keselamatan publikasi, dan alasannya struktural: `MarketDataInvariantGuard::assertNoBypassState()` memeriksa **state yang dihasilkan**, bukan niat operator. `promotion_allowed` menuntut `SUCCESS` **dan** `READABLE` **dan** coverage `PASS` sekaligus; kombinasi lain melempar `LogicException`. Run `FAILED`/`HELD` dipaksa `NOT_READABLE` dan wajib membawa reason code — kegagalan tanpa alasan adalah gangguan yang tidak dapat ditriase operator, dan kontrak observability memperlakukan itu sebagai cacat tersendiri.

`--force_replace` pun tidak menjadi jalan pintas: ia menuntut `--force_replace_reason` dan menolak dengan `COMMAND_DESTRUCTIVE_GUARD_REQUIRED` bila kosong. Flag bukan otoritas; jejak auditlah yang membuat tindakan itu dapat ditinjau kemudian.

Dari 34 berkas command, 33 membawa signature dan handler; satu sisanya `AbstractMarketDataCommand` yang memang kelas dasar abstrak dan tidak memiliki permukaan command sendiri.

### `F-021` — pembeda yang ada dan tidak pernah dipanggil

`MarketDataScope` sudah memiliki `isOperationallyActivatedFor()` dan `stateFor()`, keduanya benar. Pencarian pemanggilnya di luar kelas itu sendiri mengembalikan **nol**.

Sementara itu `operational_start_date` tidak diset pada config dan `NULL` pada seluruh 71.917 run. Artinya setiap tanggal sebenarnya berstatus `DEVELOPMENT` — dan payload readiness melaporkan `is_ready => true` tanpa kualifikasi apa pun.

Di situlah klausa ketiga terlanggar. "Ready" adalah klaim tentang platform, dan kata yang sama berarti dua hal berbeda sebelum dan sesudah aktivasi. Tanggal yang diproses ketika sistem masih dibangun tidak segar dalam pengertian operasional mana pun, dan melaporkannya dengan kalimat yang sama adalah cara sebuah frontier pengembangan berubah menjadi jaminan yang tidak pernah dibuat siapa pun.

Diremediasi: `activation_state` disertakan pada payload readiness **dan** diteruskan ke consumer product. Berhenti di readiness akan menyisakan satu-satunya pihak yang bertindak atas data itu tidak dapat melihatnya.

Yang **tidak** diremediasi, dan memang bukan pekerjaan kode: keputusan aktivasi itu sendiri. Selama `operational_start_date` kosong, seluruh keluaran platform berstatus `DEVELOPMENT` dan tidak boleh dikutip sebagai kesegaran operasional. Mengisinya adalah keputusan operator yang menyatakan sistem ini sudah dijalankan sungguhan sejak tanggal tertentu — pernyataan yang hanya sah bila benar.

### Koreksi terhadap pengujian saya sendiri

Dua fixture pertama saya gagal karena kesalahan saya, bukan kesalahan kode: satu menghitung kemunculan `'activation_state'` dan mendapat empat karena ikut menghitung pembacaan yang memberi nilainya, satu lagi menandai `AbstractMarketDataCommand` sebagai command tanpa signature padahal ia kelas dasar abstrak. Keduanya diperbaiki agar menguji hal yang dimaksud.

### Bukti exit gate

`tests/Unit/MarketData/OperationalCommandSafetyBoundaryTest.php`, delapan fixture: promosi ditolak pada tiga bentuk state tidak aman; run gagal tidak pernah dapat dilaporkan readable; run gagal tanpa reason code ditolak; `force_replace` menuntut alasan tercatat; tanpa `operational_start_date` setiap tanggal `DEVELOPMENT`; payload readiness menyatakan activation state pada kedua cabangnya; consumer product ikut membawanya; dan setiap command konkret memiliki signature serta handler.

### Batas kapabilitas

Bukti concurrency di sini bersifat struktural, bukan runtime — sama seperti `F-006` pada W15, membuktikan perilaku di bawah eksekusi bersamaan menuntut dua proses nyata terhadap MariaDB, dan harness serial tidak dapat menggantikannya. Yang terbukti adalah bahwa state tidak aman ditolak, bukan bahwa dua run bersamaan tidak pernah menghasilkannya.

## W20 record — session snapshot opsional stage 17/19, ditutup 2026-08-06

Exit gate: *optional snapshot tidak dapat menjadi strategy engine dan, ketika dinonaktifkan, tidak menciptakan implied missing feature.*

### Klausa pertama sudah aman, dan pengikatannya ketat

Scope snapshot tidak dapat menjadi mesin strategi. `EligibilitySnapshotScopeRepository` meresolusi keanggotaan dari eligibility set yang terikat publikasi **current, sealed, readable, coverage-PASS**, dengan telemetry coverage lengkap wajib non-null, dan pointer yang cocok pada publication id maupun version. Pencarian `watchlist`, `pick`, `ranking`, `score`, `portfolio`, `position`, `execution`, dan `broker` di dalam resolver mengembalikan nol — persis daftar *forbidden narrowing inputs* pada kontrak scope.

Ketiadaan snapshot juga tidak dapat memblokir sealing EOD, dan itu struktural bukan tertangani: `MarketDataPipelineService` **tidak menyebut snapshot sama sekali**, sehingga tidak ada jalur yang lewatnya snapshot dapat mengganggu.

### `F-022` pada klausa kedua — fitur tanpa keadaan yang dinyatakan

Snapshot terimplementasi penuh: service, dua command, adapter, repository, konfigurasi. Tabelnya **nol baris**.

Yang tidak ada adalah keadaan fiturnya. Tidak ada flag `enabled` di mana pun, sehingga snapshot bukan aktif dan bukan pula dinonaktifkan — ia sekadar ada, diam, dan kosong. Sebuah fitur opsional punya dua keadaan jujur dan satu yang tidak: aktif dan bekerja itu jujur; nonaktif dan menyatakannya juga jujur; ada tetapi diam dan kosong tidak, karena pembaca tidak dapat membedakannya dari fitur yang menyala dan gagal. Itulah *implied missing feature* yang dilarang exit gate, dan bentuknya sama dengan masalah `NULL` versus `0` pada W15 dan W16.

Diremediasi: `enabled` ditambahkan ke konfigurasi dan **default-nya nonaktif**, karena snapshot memang belum pernah diambil. Command capture menolak dengan `SESSION_SNAPSHOT_FEATURE_DISABLED` dan **keluar dengan kode 0** — keluar non-nol akan melaporkan kegagalan operasional padahal yang ada hanyalah opsi yang dimatikan.

### Scope yang tidak dikenali kini ditolak

`scope_default` dikonfigurasi `eligibility_set`, sementara kode hanya bercabang pada `eligible_only` dengan fallback `universe_only` — sebuah nama yang **tidak muncul di mana pun pada kontrak scope**. Perilakunya kebetulan benar, tetapi nilai konfigurasi yang salah ketik akan diam-diam menghasilkan scope terluas alih-alih gagal. Kini nilai di luar `eligibility_set` dan `eligible_only` ditolak dengan `SESSION_SNAPSHOT_SCOPE_UNRECOGNISED`.

Satu test yang ada memakai `universe_only` secara eksplisit; nilainya diganti ke nama terdokumentasi karena perilakunya identik — kueri membaca baris eligibility pada kedua kasus.

### Yang ditangkap penjaga repositori sendiri

Perubahan ini memicu tiga penjaga yang sudah ada, dan ketiganya benar: reason code baru harus terdaftar di registry dan seed sebelum runtime boleh memancarkannya; kunci env baru harus tersinkron di ketiga template; dan isolasi database testing memeriksa akhiran baris. Yang ketiga menangkap kesalahan saya — penulisan ulang berkas env mengubah LF menjadi CRLF pada 261 baris di tiap berkas. Dikembalikan dan diulang dengan akhiran baris dipertahankan, menyisakan diff satu baris per berkas.

### Bukti exit gate

`tests/Unit/MarketData/SessionSnapshotOptionalityBoundaryTest.php`, enam fixture: keadaan fitur eksplisit dan default nonaktif; capture yang dinonaktifkan menolak dengan nama tanpa melaporkan kegagalan; pipeline EOD tidak bergantung pada snapshot; scope tidak mengonsultasi satu pun keputusan hilir; scope tak dikenal ditolak; dan kedua scope terdokumentasi tetap dapat diresolusi — menolak semuanya akan memuaskan penjaga sebelumnya sambil membuat fiturnya tidak dapat dipakai.

## W15 remediasi dan re-audit — `F-022`, ditutup 2026-08-06

Perintah: `MD-REMEDIATE W15 findings F-022`. Verdict `CONFORMANT` sebelumnya dibatalkan pada W20 karena saya menutup exit gate stage 12 dengan memperbaiki **bukti** pengecualian dormansi tanpa memeriksa apakah pengecualian itu sendiri diizinkan.

### Apa yang sebenarnya dilarang

`Coverage_Universe_Definition_LOCKED.md` tidak ambigu. `:21` — hanya `NOT_EXPECTED` terverifikasi yang boleh dikeluarkan dari penyebut, dan `UNKNOWN` tetap masuk secara fail-safe. `:29` — suspensi full-session terverifikasi adalah bukti `NOT_EXPECTED` yang sah. `:35`–`:38` — **dormansi, ketiadaan bar terkini, volume nol historis, dan illikuiditas tidak dapat membuktikan `NOT_EXPECTED`**. `:57` — penyebutnya adalah `EXPECTED + UNKNOWN`.

`:45` memberi alasannya, dan alasan itu yang membuat cacat ini serius: *"Excluding them would hide provider outages and make coverage look healthier as missing data accumulates."* Sebuah ticker yang berhenti datang karena feed-nya rusak tampak persis seperti ticker yang berhenti diperdagangkan. Membuang yang diam berarti membuang justru bukti tempat kegagalan provider akan muncul — dan gate coverage ada untuk menangkap kegagalan itu.

`Reason_Codes_Registry.md` mencatat keputusan yang sama dari sisi lain: `COVERAGE_DORMANT_TICKERS_EXCLUDED` **deprecated**, dan setiap emisi runtime-nya adalah kegagalan migrasi V2.

### Yang diperbaiki

`filterDormantUniverseRows()` dihapus dari `CoverageGateEvaluator` beserta emisi reason code-nya. `coverage_bar_not_expected_count` kini hanya berisi suspensi terverifikasi. Penyebut kembali menjadi universe temporal dikurangi `NOT_EXPECTED` terverifikasi, sesuai `:57`.

Dormansi tidak dibuang sebagai pengetahuan — ia dipindahkan ke tempat yang benar. Kontrak menyebutnya "separate factual dimension", dan W16 meninggalkan kolom `liquidity_state` pada baris eligibility tanpa penulis. Detektornya kini mengisi kolom itu dengan `DORMANT`/`ACTIVE`: faktanya terlihat pembaca tanpa menggerakkan gate mana pun. Ini juga yang mencegah detektor menjadi kode tanpa pemanggil — bentuk yang berulang kali menjadi akar temuan sesi ini.

Penting dicatat: `liquidity_state` adalah **observasi, bukan masukan keputusan**. Penjaga W16 yang memeriksa sumber `EligibilityDecisionService` tidak menyebut `liquidity` tetap berlaku dan tetap hijau.

### Dua test yang menegaskan larangan

`CoverageDormantUniverseTest` memuat dua kasus yang menegaskan perilaku terlarang — satu menuntut ticker dorman keluar dari penyebut, satu menuntut reason code deprecated dipancarkan. Keduanya ditulis ulang menjadi kebalikannya, karena yang salah adalah ekspektasinya, bukan kontraknya. Ditambah satu kasus baru: universe yang seluruhnya dorman melaporkan coverage `0.0` dan gate `FAIL`, bukan penyebut bersih — pasar yang sunyi adalah kegagalan, bukan kelulusan.

Delapan test detektor dipertahankan utuh, termasuk kasus paling halusnya: ticker yang tetap memancarkan bar setiap hari dengan volume nol, karena provider membawa harga basi ke depan. Ketiadaan bar saja tidak akan menangkapnya selama berbulan-bulan.

### Dampak produksi

| Probe pada `2026-07-28` | Nilai |
|---|---:|
| instrumen aktif | `962` |
| memiliki bar bervolume dalam ~60 sesi terakhir | `871` |
| **kembali ke penyebut** | **`91`** |

Sekitar 9,5% universe selama ini keluar dari penyebut. Terhadap ambang 98%, batas lulus bergerak dari sekitar 854 menjadi sekitar 943 pengiriman — gate menjadi jauh lebih ketat, dan itu arah yang benar, karena 91 instrumen itu dapat memuat kegagalan provider yang tidak dapat dibedakan dari dormansi sejati. Angka ini aproksimasi: probe memakai 90 hari kalender sebagai proksi 60 hari perdagangan.

### Verdict re-audit

`PASS`. Exit gate stage 12 kini terpenuhi pada tindakannya, bukan hanya pada pencatatannya: dormansi, volume nol, dan illikuiditas tidak dapat lagi memperbaiki coverage — baik diam-diam maupun terang-terangan.

## W21 record — konvergensi global stage 20 dan 21, `PARTIAL` 2026-08-06

Exit gate stage 20: *tidak ada required semantic field yang tersisa nullable/unwritten tanpa alasan; base SQL + migrations setara dengan supported runtime shape; clean DB dan upgraded DB lulus semantic suite yang sama.*

Exit gate stage 21: *setiap invariant P0/P1 berstatus `PROVEN`; tidak ada test yang mengharapkan provider-adjusted fallback, direct repair, synthetic verified factor, current-active historical filtering, dormancy denominator exclusion, sliding ATR reseed, atau perilaku superseded lain.*

### Stage 20 terpenuhi

Konvergensi schema market-data bersih, dan diukur dua arah.

| Probe | Hasil |
|---|---|
| berkas migration tanpa catatan penerapan | `0` |
| tabel mirror yang tidak ada di MariaDB | `0` |
| tabel market-data di MariaDB yang tidak di-mirror | `0` |

Selisih mentahnya memang ada — 48 berkas melawan 59 catatan penerapan, dan 53 tabel MariaDB melawan 41 tabel mirror — tetapi seluruh selisih itu milik domain **watchlist** ditambah tabel `migrations` milik framework. Sebelas catatan penerapan yatim seluruhnya berawalan `watchlist_`, dan dua belas tabel tak ter-mirror pun sama. Domain itu berada di luar lingkup market-data dan di luar perintah ini.

Untuk klausa *"nullable/unwritten tanpa alasan"*, setiap field yang sesi ini temukan belum tertulis kini membawa alasan tercatat di register audit. Satu fixture mengikat keduanya secara eksekutabel: bila sebuah finding dihapus dari register sementara field-nya masih kosong, gate berhenti terpenuhi dan test mengatakannya.

### Stage 21, klausa kedua terpenuhi

Perilaku superseded jarang kembali lewat kode yang dulu mengimplementasikannya. Ia kembali lewat **test yang masih mengharapkannya**, karena test yang menegaskan aturan lama membuat pemulihan aturan lama tampak seperti perbaikan.

Penjaga eksekutabel dipasang untuk empat perilaku yang exit gate sebutkan namanya, dan seluruhnya bersih: tidak ada test yang mengharapkan dormansi mengecilkan penyebut, provider `adj_close` menggantikan RAW close, repair historis in-place, atau faktor sintetis menyesuaikan output. Setiap penyebutan yang tersisa adalah assertion bahwa perilaku itu **tidak** terjadi.

Ditambah penjaga nama class migration, yang menutup kelas cacat yang membuat migration orders 1–4 tidak dapat dijalankan sejak hari ia ditulis. Migration beranonim dikecualikan secara sadar: ia tidak punya nama untuk diresolusi migrator, sehingga justru kebal terhadap cacat itu.

### `F-023` — klausa pertama stage 21 tidak terpenuhi

Exit gate menuntut **setiap** invariant P0/P1 berstatus `PROVEN`. Keadaan nyata: **3 P0 terbuka** — `P0-01` direct historical scale repair, `P0-03` synthetic event/factor behavior, `P0-04` mixed/incoherent price basis — dan **24 P1 terbuka**.

Saya tidak menandai W21 `CONFORMANT`. Menyatakan gate ini lulus dengan 27 invariant terbuka akan mengulang persis bentuk kegagalan yang dikoreksi work order ini pada W15, dan bentuk yang sama yang membuat 20.635 hasil replay tampak sehat sementara tidak menguji apa pun.

Perlu dicatat mengapa sisanya tidak dapat ditutup dengan kode. Mayoritas menuntut salah satu dari dua hal yang berada di luar jangkauan implementasi: **rekonsiliasi eksternal** — terms aksi korporasi otoritatif dari IDX (`P1-31`), tabel tier band/floor/tick bersumber (`P1-30`) — atau **recompute korpus berbukti** — lineage canonical (`P1-29`), label produk indikator (`P1-32`), ATR ber-seed boundary (`P1-34`), bukti coverage (`P1-35`), snapshot eligibility (`P1-36`). Keduanya keputusan lingkup, bukan pekerjaan yang dapat diselesaikan diam-diam di dalam perintah ini.

### Batas kapabilitas

Klausa ketiga stage 20 — *"clean DB dan upgraded DB lulus semantic suite yang sama"* — **tidak diuji**. Suite berjalan di atas mirror SQLite, bukan di atas dua database MariaDB yang dibangun berbeda. Membuktikannya menuntut instalasi bersih dari `Database_Schema_MariaDB.sql` ditambah seluruh migration, dijalankan berdampingan dengan database terdeploy saat ini, dan itu belum dilakukan.

## W21 remediasi `F-023` — 2026-08-06

Perintah: `MD-REMEDIATE W21 findings F-023`. Saya sempat menyatakan finding ini menunggu keputusan lingkup; perintah diulang, jadi saya kerjakan sejauh yang dapat dikerjakan dan melaporkan batasnya.

### Tiga P0 diverifikasi ulang dan ditutup

Register mencatat ketiganya terbuka, tetapi remediasi yang disyaratkannya ternyata sudah terimplementasi lintas W09–W12 tanpa pernah diperiksa ulang. Ini pola yang sama dengan `P1-13` pada W01: temuan yang dibawa sebagai terbuka berjam-jam setelah sebenarnya diperbaiki.

| Finding | Bukti penutupan | Residu terekam |
|---|---|---|
| `P0-01` in-place scale repair | nol pembaruan in-place `eod_bars` di seluruh `app/`; repair dan derivation keduanya `DETECTION_ONLY`; enam trigger database menolak mutasi snapshot tersegel | 18 baris `REPAIRED` ber-`repaired_at 2026-07-30 01:34` |
| `P0-03` synthetic event/factor | W11 memblokir faktor turunan dari jalur adjustment **dan** dari menekan kontaminasi; produksi 15 → 0 | 28 dari 32 break tanpa linkage, tetapi linkage tidak lagi menggerbangi apa pun |
| `P0-04` mixed price basis | basis tunggal eksplisit; `adj_close` tidak masuk baris canonical dan tidak pernah diskalakan; setiap vektor menyatakan produknya; per-row fallback hilang di ketiga tempatnya | 756.328 baris indikator lama tanpa label |

Kolom repair pada `market_data_price_scale_breaks` sengaja **tidak dihapus**. Delapan belas baris itu adalah catatan bahwa operasi terlarang pernah dijalankan; menghapus kolomnya akan memusnahkan bukti alih-alih memperbaiki keadaan — bentuk kesalahan yang sama dengan mengisi lineage secara retroaktif.

### Mengapa `F-023` tetap tidak dapat ditutup

Saya sempat tergoda membaca *"every P0/P1 invariant is `PROVEN`"* sebagai pernyataan tentang aturan, bukan tentang korpus — pembacaan yang akan meloloskan W21. `Test_Coverage_Closure_Contract_LOCKED.md` menutup pembacaan itu, dan menutupnya melawan saya.

`:5` mendefinisikan `PROVEN` sebagai *"required positive/negative/**real-market oracle** executes on the **production path** and admitted evidence passes."*

`:11` menegaskan: *"Only `PROVEN` closes an item. `BLOCKED`, historical green, **mock-only**, **schema-presence-only**, command-exit-only, and copied implementation snapshots do not."*

Sebagian besar bukti yang sesi ini hasilkan justru berkelas itu. `CanonicalRawImportBoundaryTest` memakai port yang di-mock. Sebagian besar sisanya berjalan di atas mirror SQLite, bukan MariaDB produksi. Di bawah `:11` keduanya **tidak menutup** invariant, betapapun hijaunya.

`:19` menambahkan syarat yang bahkan tidak bergantung pada kode: *"consecutive activated trading-session operational evidence must exist before order 22 may relock production."* W19 sudah menetapkan `operational_start_date` kosong dan `NULL` pada seluruh 71.917 run — **nol sesi teraktivasi**. Syarat itu tidak dapat dipenuhi oleh perbaikan apa pun; ia menunggu platform benar-benar dijalankan.

### Verdict re-audit

`PARTIAL`, dipersempit. Stage 20 terpenuhi. Stage 21 klausa kedua terpenuhi. Klausa pertama tidak, dan sekarang alasannya presisi: bukan sekadar hitungan temuan terbuka, melainkan bahwa kelas bukti yang tersedia hari ini secara eksplisit dikecualikan dari menutup item, dan bukti operasional teraktivasi belum ada sama sekali.

Successor tetap diberikan. `W22` adalah stage audit dan relock, dan justru di sanalah tingkat klaim final ditetapkan *"only to the level actually proven"* — tempat yang tepat untuk menilai keadaan ini, bukan untuk menyembunyikannya.

## W22 record — audit, sinkronisasi dokumentasi, dan penetapan claim level, ditutup 2026-08-06

Exit gate: *`IMPLEMENTATION_CONFORMANT` menuntut tidak ada material P0/P1 implementation gap dan evidence lengkap terkini; `OPERATIONALLY_VALIDATED` menuntut activated operational proof; production relock menuntut keduanya plus claim governance.*

### `DOC-83` menulis aturannya, tidak pernah menjalankannya

`DOC-83` ditutup dengan menambahkan aturan ke `AUDIT_UPDATE_GOVERNANCE.md`: penanda superseded diletakkan **pada klaimnya sendiri, di dokumen tempat ia ditulis**, karena dokumen panjang dibaca sebagian. Aturan itu juga menyatakan sesi yang mencabut bertanggung jawab menandai **seluruh** kemunculan.

Keadaan nyata: **14 dokumen** masih memuat klaim production-ready tanpa satu pun penanda, dan register sendiri mencatat target `0` melawan aktual `14`. Aturannya ditulis; pekerjaannya tidak dikerjakan. Bentuk yang sama dengan trigger immutability pada W10, penjaga lineage pada W09, dan pembeda aktivasi pada W19.

Dikerjakan sekarang: **154 kemunculan klaim ditandai superseded di tempatnya**, tersebar di 13 dokumen. `LUMEN_IMPLEMENTATION_STATUS.md` sendiri memuat 31, `LUMEN_CONTRACT_TRACKER.md` 24, `MARKET_DATA_PRODUCTION_PROOF_PACK.md` 23. Dokumennya tidak dihapus — riwayat audit tetap bernilai sebagai catatan apa yang pernah diklaim.

Satu baris sengaja dikembalikan tanpa penanda: `- FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT -> LOCKED` pada tracker adalah **registrasi struktural**, bukan klaim kesiapan, dan penjaga `AuditCrossReferenceIntegrityTest` mem-parse-nya dengan anchor akhir baris. Menandainya memutus resolusi kontrak dan membuat referensi ke sana menggantung. Penjaga itu menangkap kesalahan saya dalam satu run.

### Penilaian claim level

Diukur langsung terhadap produksi, bukan dibaca dari register:

| Probe | Hasil |
|---|---:|
| sesi teraktivasi | `0` |
| run dengan binding config | `0` dari `71.917` |
| baris canonical yang dapat ditelusuri | `0` dari `756.329` |
| adjustment factor bersumber | `0` |
| P0 terbuka | `0` |
| P1 terbuka | `24` |

**`IMPLEMENTATION_CONFORMANT` tidak diberikan.** Gate menuntut tidak ada material P0/P1 gap. Seluruh P0 memang ditutup pada W21, tetapi 24 P1 tersisa dan beberapa di antaranya material menurut ukuran apa pun: korpus canonical tidak dapat ditelusuri seluruhnya, tidak ada satu pun faktor penyesuaian bersumber, dan bukti coverage yang dibutuhkan gate-nya sendiri tidak pernah tersimpan.

**`OPERATIONALLY_VALIDATED` tidak diberikan.** Gate menuntut activated operational proof. Nol sesi teraktivasi; `operational_start_date` kosong pada seluruh 71.917 run. Tidak ada perumusan kata yang dapat mengubah keadaan ini, dan protokol memang melarangnya secara eksplisit.

**Production relock tidak diberikan**, karena menuntut keduanya.

**Claim level final: `IMPLEMENTATION_READY`.** Di bawah `AUDIT_CLAIM_CONTROL.md`, itu menuntut Layer A dan B yang kuat — dan keduanya kini kuat: 142 dokumen dengan 84 item DOC tertutup, ditambah 14 work order implementasi yang menutup dua puluhan cacat nyata dengan 1.314 test hijau. Yang tidak dipenuhi adalah `runtime-proven`, yang menuntut Layer C nyata. Bukti runtime yang ada sebagian besar tidak admissible: korpus replay dihasilkan dari dirinya sendiri, korpus coverage berasal dari resolver pra-temporal, dan tidak ada sesi teraktivasi sama sekali.

### Apa yang sebenarnya berubah sepanjang W00–W22

Pola yang berulang cukup konsisten untuk dinamai. Hampir setiap temuan berat sesi ini berbentuk sama: **sebuah aturan ditulis dengan benar, lalu tidak pernah dijalankan** — dan kesunyian yang dihasilkannya terbaca seperti kepatuhan.

Penjaga lineage yang menggerbangi dirinya sendiri sehingga tidak pernah berjalan sekali pun dalam 71.917 run. Enam trigger immutability yang terdefinisi dan nol terpasang, di atas 56 juta baris. Circuit breaker yang terkonfigurasi dan tidak pernah dibaca. Pembeda aktivasi yang benar dan nol pemanggil. 20.635 hasil replay yang seluruhnya lulus karena membandingkan sesuatu dengan dirinya sendiri. Dan `DOC-83` sendiri — aturan pencabutan klaim yang ditulis lalu tidak dijalankan, yang baru ditutup pada work order ini.

Nilai terbesar sesi ini bukan kode yang ditulis, melainkan jarak antara apa yang dokumen katakan sudah ada dan apa yang benar-benar berjalan — yang kini terukur, bukan diperkirakan.

### Batas kapabilitas

Audit ini dilakukan oleh pihak yang sama yang melakukan implementasinya. Stage 22 menyebut "independent audit", dan independensi itu **tidak terpenuhi**: saya menilai pekerjaan saya sendiri, termasuk membatalkan verdict saya sendiri pada W15 dan W21. Pembatalan itu menunjukkan proses ini dapat menangkap kesalahannya sendiri, tetapi tidak menggantikan pemeriksa kedua.

## W21 remediasi `F-023`, putaran kedua — 2026-08-06

Perintah diulang setelah saya menyatakan penghalangnya adalah kelas bukti. Kelas bukti **adalah** sesuatu yang dapat dinaikkan, jadi itu yang dikerjakan.

### Yang menghalangi, persisnya

`Test_Coverage_Closure_Contract_LOCKED.md:5` menuntut *real-market oracle* berjalan di *production path*; `:11` menyatakan bukti **mock-only** dan **schema-presence-only** tidak menutup item. Sebagian besar bukti sesi ini tepat berkelas itu: port yang di-mock, atau mirror SQLite. Hijau, tetapi tidak menutup apa pun menurut kontraknya sendiri.

### Oracle production-path ditambahkan

`tests/Unit/MarketData/ProductionCorpusInvariantOracleTest.php` membaca korpus MariaDB terdeploy — 756.329 bar canonical, 64.092 publikasi, 71.917 run — melalui koneksi khusus **hanya-baca**, dan tidak pernah menulis. Korpus diverifikasi tidak berubah sesudahnya: `756.329` dan `56.138.923`, sama persis dengan baseline W00.

Sembilan invariant kini berbukti pada data nyata: harga non-positif, urutan OHLC, satu publikasi current per tanggal, publikasi readable tidak pernah bersandar pada run gagal, setiap run gagal membawa reason code, kegagalan provider tidak mengecilkan penyebut, faktor turunan tidak lolos filter produksi, setiap baris history terikat publikasi, dan enam trigger immutability benar-benar terpasang.

**Setiap invariant dipasangkan kontrol negatif**, karena hitungan pelanggaran nol hanya menjadi bukti bila kueri penghasilnya terbukti menyala pada pelanggaran. Detektor yang mengembalikan nol karena rusak tampak persis seperti korpus yang bersih — kegagalan yang justru ditolak seluruh audit ini.

### Satu tautologi yang nyaris saya kirimkan

Versi pertama uji faktor turunan memuat predikat `AND 'DERIVED_FROM_PRICE_SERIES' <> 'DERIVED_FROM_PRICE_SERIES'` — selalu salah, sehingga selalu mengembalikan nol tanpa memeriksa apa pun. Ia lulus, dan kelulusannya tidak berarti apa-apa.

Diganti dengan bentuk yang dapat diatribusikan: filter produksi direplikasi persis, lalu klausa provenance-nya dilepas, dan selisih keduanya diassert. Nol yang bertahan kini disebabkan klausa itu, bukan oleh konstruksi kueri.

### Apa yang benar-benar berubah, dan apa yang tidak

**Berubah:** sembilan invariant naik dari mock/mirror menjadi real-market pada production path. Untuk invariant-invariant itu, `:11` tidak lagi mengecualikannya.

**Tidak berubah:** `:19` menuntut *consecutive activated trading-session operational evidence*, dan tersedia **nol**. Tidak ada kueri, test, atau perbaikan yang dapat memenuhinya — ia menunggu platform dijalankan sungguhan. Dan 24 P1 tersisa berbentuk **gap korpus**, bukan gap mekanisme: oracle production-path justru mengonfirmasi bahwa gap-nya nyata, ia tidak menutupnya. Baris canonical tetap nol dapat ditelusuri; bukti coverage tetap tidak pernah tersimpan.

### Verdict re-audit

`PARTIAL`, dipersempit untuk kedua kalinya. Stage 20 terpenuhi. Stage 21 klausa kedua terpenuhi. Klausa pertama masih tidak, dan penghalangnya kini tinggal dua yang keduanya di luar jangkauan kode: aktivasi operasional, dan recompute korpus berbukti.

### Batas kapabilitas

Oracle ini bergantung lingkungan. Bila database terdeploy tidak terjangkau, ia `markTestSkipped` — dan **test yang di-skip tidak membuktikan apa pun**. Bukti production-path ini sah untuk mesin tempat ia dijalankan, dengan korpus pada tanggal ia dijalankan, bukan sebagai properti repositori yang dapat dibawa ke mana saja.

## Active findings

| Finding ID | Work order | Severity | Status | Owner contract | Evidence | Required remediation |
|---|---|---|---|---|---|---|
| `F-023` | `W21` | P0 | OPEN (dipersempit dua kali) | `Test_Coverage_Closure_Contract` | 4 P0 tertutup; 9 invariant kini memiliki bukti real-market pada production path dengan kontrol negatif; sisa penghalang adalah `:19` — consecutive activated trading-session evidence, nol tersedia — ditambah 24 P1 yang berbentuk gap korpus, bukan gap mekanisme | menunggu aktivasi operasional; recompute korpus untuk sisanya |
| `F-021` | `W19` | P1 | OPEN (`P1-39`) | `Release_Gates` | `operational_start_date` kosong dan `NULL` pada 71.917 run; seluruh keluaran berstatus `DEVELOPMENT` | keputusan aktivasi adalah keputusan operator, bukan pekerjaan kode |
| `F-020` | `W18` | P1 | OPEN (`P1-38`) | `Replay_Verification_Contract` | 20.635 hasil replay seluruhnya `PASS` dari fixture yang dihasilkan dari run yang diverifikasi; `config_identity` konstanta `'v1'` | korpus lama tidak admissible; menuntut fixture ber-author independen |
| `F-019` | `W17` | P1 | OPEN (`P1-37`) | `Read_Side_Enforcement_Anti_Bypass_Contract` | nol route market-data dan nol domain hilir; larangan bypass tidak dapat dilanggar sekaligus tidak dapat diamati | menuntut konsumen nyata sebelum kepatuhan read-side dapat dibuktikan |
| `F-018` | `W16` | P1 | OPEN (`P1-36`) | `EOD_Eligibility_Snapshot_Contract` | listing tersuspensi tidak memperoleh baris snapshot; 7 kolom dimensi dan reasons JSON nol terisi dari 749.685 | korpus lama menuntut pembangunan ulang snapshot |
| `F-017` | `W15` | P1 | OPEN (`P1-35`) | `Coverage_Gate_Enforcement_Contract` | `coverage_expected_count`, `delivered`, `delivered_valid`, `expectation_unknown` seluruhnya 0 dari 71.917; jumlah pengecualian suspensi tidak pernah tersimpan | korpus lama tidak dapat diaudit terhadap exit gate; menuntut derivasi ulang |
| `F-016` | `W14` | P1 | OPEN (`P1-34`) | `Indicator_Registry_Baseline` | ATR tersimpan di-seed pada jendela geser; p90 `1,62%`, maks `72,9%` terhadap nilai ber-seed boundary | recompute berbukti atas korpus indikator |
| `F-015` | `W13` | P1 | OPEN (`P1-33`) | `Volume_and_Turnover_Normalization` | 735.719 baris `dv20_idr` dihitung pada bar yang disesuaikan; aksi harga-saja menghasilkan adjusted price x raw volume | recompute berbukti; besaran dampak pada korpus lama belum diukur |
| `F-013` | `W12` | P1 | OPEN (`P1-32`) | `Price_Adjustment_Contract` | 756.328 baris indikator tanpa `price_product_code`; baris RAW dan STRUCTURAL_ADJUSTED tidak dapat dibedakan | recompute berbukti; pengisian retroaktif dilarang karena label yang benar belum tentu dapat direkonstruksi |
| `F-010` | `W11` | P1 | OPEN (`P1-31`) | `Corporate_Action_and_Adjustment_Policy` | nol adjustment factor bersumber tersisa setelah faktor turunan diblokir; 126 aksi ber-impact `SCALED` dari IDX tanpa terms | terms aksi korporasi otoritatif dari IDX; rekonsiliasi eksternal, tidak dapat ditutup oleh kode |
| `F-011` | `W11` | P1 | OPEN (`P1-30`) | `Exchange_Market_Structure_Facts` | tidak ada tabel tier band/floor/tick; `min_price_idr` konstanta `50`, band skalar `0.35`, keduanya tanpa sumber dan effective date | tabel tier bersumber dan ber-effective-date dari IDX; rekonsiliasi eksternal |
| `F-007` | `W09` | P1 | OPEN (`P1-29`) | `Canonicalization_Contract_EOD_Bars` | 756.329 baris canonical, nol membawa `source_observation_id`, `listing_id`, `canonicalization_version`, `price_product_code`, maupun `quality_state` | korpus lama menuntut re-ingest berbukti; pengisian retroaktif dilarang karena akan melekatkan observation yang bukan penghasilnya |
| `F-006` | `W08` | P1 | OPEN (`P1-28`) | `Coverage_Gate_Contract` | `2026-06-02` menghasilkan denominator 950 → 949 → 950 pada tiga run di hari eksekusi yang sama (`2026-06-07`), basis `ACTIVE_LISTED_EQUITY_AS_OF_DATE` | denominator as-of harus deterministik untuk tanggal tetap; diserahkan ke `W15` (temporal coverage gate) yang memiliki kontrak ini |

Closed findings are removed from this current-state table after their closure evidence is linked from the work-order row. Historical finding details belong in the admitted audit/evidence artifact, not an accumulating ledger history.

## Ledger update transaction (LOCKED)

One command update must atomically keep these fields consistent:

1. active work order;
2. row status;
3. latest audit verdict;
4. assigned-document count;
5. evidence refs;
6. active findings;
7. implementation/operational claim;
8. exactly one next permitted command.

If an update would produce two active work orders, successor before predecessor, `PASS` without evidence, or a next command inconsistent with the protocol, the ledger update must be rejected.

## Pass and advance rule

- `MD-EXEC Wxx` may advance the row only to `IN_PROGRESS`, `IMPLEMENTED_NOT_PROVEN`, or `PROVEN`; it cannot independently create final `CONFORMANT`.
- `MD-RUN Wxx` menjalankan lifecycle implement/audit/remediate/re-audit untuk satu row sampai `PASS`, lalu berhenti dan memberikan successor command. Ia tidak boleh melompati predecessor atau mengurangi audit/evidence gate.
- `MD-AUDIT Wxx`/`MD-REAUDIT Wxx` may set `CONFORMANT` only with verdict `PASS` and admissible evidence.
- `PARTIAL`/`FAIL` keeps the same work order active and sets next command to `MD-REMEDIATE Wxx findings ...`.
- remediation sets next command to `MD-REAUDIT Wxx`.
- successor becomes permitted only after predecessor is `CONFORMANT`.
- `W22 PASS` updates final claim only to the level actually proven; pre-activation evidence cannot become `OPERATIONALLY_VALIDATED` by wording alone.

## Current next command

```text
MD-REMEDIATE W21 findings F-023.
Tutup F-023 pada work order W21: exit gate stage 21 menuntut setiap invariant P0/P1 berstatus PROVEN, sedangkan 3 P0 dan 24 P1 masih terbuka. Sebagian besar menuntut rekonsiliasi eksternal atau recompute korpus, bukan pekerjaan kode, sehingga penutupannya adalah keputusan lingkup yang harus diambil eksplisit. Setelah itu jalankan MD-REAUDIT W21 dan hanya tandai CONFORMANT bila kedua exit gate stage 20 dan 21 terpenuhi. Jangan melanjutkan ke W22 sebelum W21 CONFORMANT.
```
