# Legacy Semantic Extract — LX-MD-0034-CTX-04

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L502-L839`
- Extract body SHA1: `2E85C2214668457DF13F985A397A8508D756B529`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Work-order ledger — executed history with current revalidation overrides

| Work order | Scope | Dependency | Status | Latest audit verdict | Assigned docs | Evidence refs | Next action/state |
|---|---|---|---|---|---:|---|---|
| `W00` | Preflight and implementation ledger baseline | documentation ready | `CONFORMANT` | `PASS` | 142 dari 142 | baseline 2026-08-03 di bawah | closed |
| `W01` | Scope, boundary, dataset/activation semantics | `W00 CONFORMANT` | `CONFORMANT` | `PASS` | stages 1–2, 3 dokumen | `TerminologyOwnerVocabularyTest` 7/15; suite 1165/8786 | closed |
| `W02` | Yahoo bootstrap and provider-neutral ports | `W01 CONFORMANT` | `CONFORMANT` | `PASS` | stage 3, 1 dokumen | `ProviderNeutralBoundaryTest` 8/15; suite 1173/8801 | closed |
| `W03` | Migration/schema/repository/reason/test skeleton | `W02 CONFORMANT` | `CONFORMANT` | `PASS` | foundations stages 4–21 | `MigrationIntegrityAndDriftTest` 5/7; suite 1178/8808 | closed |
| `W04` | Immutable config snapshot and semantic bindings | `W03 CONFORMANT` | `CONFORMANT` | `PASS` | stage 16 foundation, 7 dokumen | `ConfigIdentityBindingTest` 6/25; suite 1184/8833 | closed |
| `W05` | Temporal identity and mappings | `W04 CONFORMANT` | `CONFORMANT` | `PASS` | stage 6, 3 dokumen pada baseline dokumen 2026-08-07; sector temporal membership ditambahkan sebagai prerequisite lintas-cutting | `TemporalIdentityFixturesTest` 6/14; suite 1190/8847 | historical executed status retained; sector prerequisite baru tidak dianggap terbukti oleh evidence lama |
| `W06` | Calendar/session/trading status | `W05 CONFORMANT` | `CONFORMANT` | `PASS` | stage 7, 2 dokumen | `CalendarProvenanceAndStatusTest` 8/16; suite 1198/8863 | closed |
| `W07` | Immutable observations and source adapters | `W06 CONFORMANT` | `CONFORMANT` | `PASS` | stage 4, 3 dokumen | `SourceObservationImmutabilityTest` 6/26; suite 1204/8889 | closed |
| `W08` | Resilience/manual recovery/failure taxonomy | `W07 CONFORMANT` | `CONFORMANT` | `PASS` | stage 5, 5 dokumen | `SourceFailureResilienceTest` 18/51 + `SourceCircuitBreakerTest` 6/9; 68.411 run produksi, 0 provider-failure shrink; suite 1228/8949 | closed |
| `W09` | Import-only and canonical RAW | `W08 CONFORMANT` | `CONFORMANT` | `PASS` | stage 8, 4 dokumen | `CanonicalRawImportBoundaryTest` 8/25; produksi 756.329 bar, 0 zero-price dan 0 OHLC-order violation; suite 1236/8974 | closed |
| `W10` | Publication/seal/pointer/correction lifecycle | `W09 CONFORMANT` | `CONFORMANT` | `PASS` | stage 9, 16 dokumen | `PublicationSealPointerLifecycleTest` 9/17; 6 trigger terpasang dan terbukti menolak sealed serta mengizinkan candidate; produksi 64.092 publikasi, 0 tanggal dengan lebih dari satu current; suite 1245/8991 | closed |
| `W11` | Corporate-action event/factor lifecycle | `W10 CONFORMANT` | `CONFORMANT` | `PASS` | stage 10, 5 dokumen | `CorporateActionCandidateBoundaryTest` 6/13; produksi 15 faktor turunan diblokir dari published output, 0 tersisa; suite 1251/9005 | closed |
| `W12` | Coherent analytical price products | `W11 CONFORMANT` | `BLOCKED` (`MD-REAUDIT` 2026-08-11) | `DOC PASS / IMPLEMENTATION REOPENED` | stage 11, 2 dokumen | historical W21 proof removed provider `adj_close` fallback, but strict 2026-08-08 re-audit shows selected `STRUCTURAL_ADJUSTED` is not yet bound run-wide; see P0-04 | open implementation gap |
| `W13` | Actual/proxy daily metrics | `W12 CONFORMANT` | `CONFORMANT` | `PASS` | stage 14, 2 dokumen | `ActualVersusProxyMetricBoundaryTest` 5/10, terbukti diskriminatif (80.000 vs 100.000 tanpa perbaikan); suite 1262/9030 | closed |
| `W14` | Deterministic indicators/dependency graph | `W13 CONFORMANT` | `CONFORMANT` | `PASS` | stage 15, 7 dokumen | `IndicatorIndependentOracleTest` 7/12 (oracle dihitung tangan, bukan dibaca balik dari implementasi); probe produksi 120 ticker, p90 `1,62%` dan maks `72,9%`; suite 1269/9042 | closed |
| `W15` | Temporal coverage gate | `W14 CONFORMANT` | `CONFORMANT` | `PASS` (re-audit) | stage 12, 4 dokumen | `CoverageSilentImprovementBoundaryTest` 6/17 + `CoverageDormantUniverseTest` 8/16; produksi 91 dari 962 instrumen kembali ke penyebut; suite 1307/9162 | closed |
| `W16` | Explainable data usability | `W15 CONFORMANT` | `CONFORMANT` | `PASS` | stage 13, 2 owner dokumen + `Sector_Classification_Contract_LOCKED.md` sebagai prerequisite/input dari stage 6 | `EligibilityExplainabilityBoundaryTest` 7/20; suite 1282/9079 | historical executed status retained; tidak menjadi bukti temporal-sector reconciliation baru |
| `W17` | Versioned atomic read product | `W16 CONFORMANT` | `CONFORMANT` | `PASS` | stage 17 core, 13 dokumen | `ConsumerReadProductAntiBypassTest` 7/36; suite 1289/9115 | closed |
| `W18` | Exact/as-known replay | `W17 CONFORMANT` | `BLOCKED` | **`BLOCKED`** (`MD-REAUDIT` keenam 2026-08-11; sebelumnya `FAIL` dua kali dan `PARTIAL` dua kali pada hari yang sama) | stage 18, 5 dokumen | `AsKnownReplayBoundaryTest` 4/13 (mode as-known sebelumnya tidak ada); DOC-71 BLOCKED ditegakkan; suite 1293/9128. **Dibuka kembali 2026-08-11**: audit ulang pasca-`F-025` menemukan future leakage pada akar event/factor (`F-028`), guard as-known yang hanya menyebut 3 dari 9 metode ber-cutoff (`F-029`), dan gate exact-replay yang tidak dapat disertifikasi karena seluruh 20.635 perbandingan self-generated (`F-030`) | `MD-REMEDIATE W18 findings F-028,F-029,F-030` |
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
| manual file bukan default source | terpenuhi pada evidence historis — `default_source_mode = api`; boundary baru **one-date operational rescue** tidak dianggap terbukti oleh evidence W02 lama dan memerlukan implementation proof terpisah |
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


<!-- LEGACY_EXTRACT_BODY_END -->
