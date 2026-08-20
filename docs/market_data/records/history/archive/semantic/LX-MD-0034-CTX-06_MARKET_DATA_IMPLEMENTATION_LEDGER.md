# Legacy Semantic Extract — LX-MD-0034-CTX-06

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L1336-L1610`
- Extract body SHA1: `ED33F8871ADFC86078AE26906AF3218840268927`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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

**Historical W21 entry-gate snapshot (superseded for current P0 counts; retained as execution history).**

Exit gate menuntut **setiap** invariant P0/P1 berstatus `PROVEN`. Keadaan nyata: **3 P0 terbuka** — `P0-01` direct historical scale repair, `P0-03` synthetic event/factor behavior, `P0-04` mixed/incoherent price basis — dan **24 P1 terbuka**.

Saya tidak menandai W21 `CONFORMANT`. Menyatakan gate ini lulus dengan 27 invariant terbuka akan mengulang persis bentuk kegagalan yang dikoreksi work order ini pada W15, dan bentuk yang sama yang membuat 20.635 hasil replay tampak sehat sementara tidak menguji apa pun.

Perlu dicatat mengapa sisanya tidak dapat ditutup dengan kode pada snapshot 2026-08-06 ini. Mayoritas ketika itu menuntut salah satu dari dua hal yang berada di luar jangkauan implementasi: **rekonsiliasi eksternal** — terms aksi korporasi otoritatif dari IDX (`P1-31`), tabel tier band/floor/tick bersumber (`P1-30`) — atau **recompute korpus berbukti** — lineage canonical (`P1-29`), label produk indikator (`P1-32`), ATR ber-seed boundary (`P1-34`), bukti coverage (`P1-35`), snapshot eligibility (`P1-36`). State masing-masing sesudah tanggal itu ditentukan oleh current register; khusus `P1-30`, sisi perekaman diselesaikan oleh Tahap 7 pada 2026-08-13 dan sisi penerapan tetap menunggu Tahap 8.

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
| `P0-04` mixed price basis | **Historical W21 proof:** provider `adj_close` fallback hilang dan vektor mulai menyatakan produk. **2026-08-08 correction:** this is insufficient for the selected run-wide `STRUCTURAL_ADJUSTED` strategy because vectors may still alternate RAW/STRUCTURAL_ADJUSTED based on whether a factor changed a bar | selected-product run binding not proven; plus 756.328 legacy indicator rows without label |

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

> **Historical snapshot boundary:** this W22 record captures the 2026-08-06 verdict. Its `P0 terbuka = 0` count and “seluruh P0 ditutup” wording were **superseded on 2026-08-08** when P0-04/F-024 was reopened. Keep the numbers below as dated evidence; do not quote them as current state.

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

## HISTORICAL finding register — superseded by the current table below

| Finding ID | Work order | Severity | Status | Owner contract | Evidence | Required remediation |
|---|---|---|---|---|---|---|
| `F-028` | `W18` | P0 | CLOSED (diremediasi 2026-08-11: `recorded_at` pada kedua tabel, cutoff pada 4 metode, aksi terlihat pada cutoff 2024-01-01 turun 530→0) | `Replay_Verification_Contract_LOCKED.md`; `Point_In_Time_Backtest_Input_Contract_LOCKED.md` | **Future leakage pada akar event dan factor.** `EventRiskSourceRepository` mengandung **nol** kemunculan `knownAt`: `resolveEventRiskContextForTickerIds`, `resolveCorporateActionContaminationForTickerIds`, `resolveAdjustmentFactorsForTickerIds`, dan `suspendedTickerIdsAsOf` semuanya hanya menerima tanggal efektif, bukan cutoff pengetahuan. Lebih dalam lagi, `market_data_corporate_actions` **tidak memiliki kolom `recorded_at`** — hanya `created_at` — sehingga tabelnya tidak punya koordinat as-known untuk difilter. Paparannya bukan hipotetis: seluruh **530 aksi dibuat 2026-06-07 s.d. 2026-07-30** sementara dataset mulai 2023-01-02, jadi replay as-known dengan cutoff kapan pun sebelum Juni 2026 tetap melihat semuanya. Exit gate stage 18 melarang persis ini | beri akar event/factor koordinat as-known dan cutoff, lalu buktikan revisi yang dicatat setelah cutoff tidak terlihat |
| `F-034` | `W18` | P0 | CLOSED (diremediasi 2026-08-11: identitas bersumber `config_hash`, terbukti berubah saat konfigurasi berubah; run tanpa identitas ditandai `CONFIG_IDENTITY_UNRECORDED` alih-alih dilewati diam) | `Replay_Verification_Contract_LOCKED.md` (frozen config identity) | **Replay membandingkan konstanta sebagai identitas config.** `ReplayVerificationService.php:696` mengambil `config_identity` dari `$run->config_version`, dan kolom itu bernilai **`'v1'` pada seluruh 72.765 run — satu nilai berbeda, tanpa kecuali**. Perbandingannya karena itu selalu `'v1'` vs `'v1'`, sehingga `REPLAY_CONFIG_IDENTITY_MISMATCH` (`:1393`) tidak dapat menyala apa pun yang terjadi pada konfigurasi. Field yang benar-benar berubah bersama config — `config_hash`, kini terisi lewat `F-032` — tidak ikut dibandingkan. Ini false proof menurut aturan verdict protokol: replay melaporkan identitas config terverifikasi padahal tidak ada yang diverifikasi | bandingkan `config_hash`/`config_snapshot_id` yang bervariasi, bukan `config_version` yang konstan; lalu buktikan perubahan config menghasilkan mismatch pada replay |
| `F-036` | `W18` | P1 | CLOSED (diremediasi 2026-08-11: DDL dan daftar makna disamakan; `ReplayResultsSchemaDocumentSyncTest` mengunci dokumen terhadap kolom terdeploy dua arah) | `../backtest/Replay_Results_Schema_MariaDB.sql` (dokumen tugas W18) | **Dokumen owner evidence schema tidak tersinkron dengan skema terdeploy.** Barisnya masih `comparison_result ENUM('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED')` (`:13`), dan blok `LOCKED SEMANTICS` (`:83`) masih mendaftar empat makna tanpa `NOT_ADMISSIBLE`. Remediasi `F-025` memperbarui `db/Database_Schema_MariaDB.sql` dan melewatkan dokumen ini, padahal ia salah satu dari lima dokumen tugas stage 18. Pembaca kontrak karena itu memperoleh kosakata yang lebih sempit daripada yang benar-benar berlaku. Ditemukan pada audit ulang keempat, bukan oleh guard mana pun — tidak ada test yang membandingkan DDL dokumen backtest dengan skema terdeploy | samakan DDL dan daftar makna, lalu tambahkan guard yang menggagalkan bila keduanya menyimpang |
| `F-035` | `W18` | P2 | CLOSED (diremediasi 2026-08-11: `resolveForRun` menerima cutoff dan menjadi lookup murni; `contractRequiredRoots()` menggagalkan jenis revisi tanpa akar ber-cutoff) | `Replay_Verification_Contract_LOCKED.md` | Guard `AsKnownReplayBoundaryTest::test_a_new_temporal_root_cannot_be_added_without_being_registered` yang ditulis untuk `F-029` menyapu metode yang **memiliki** `knownAt` tetapi belum terdaftar. Ia tidak dapat menemukan akar yang **seharusnya** punya cutoff tetapi tidak punya — contohnya `MarketDataConfigSnapshotRepository::resolveForRun($requestedDate)`, yang menyelesaikan config hidup tanpa koordinat pengetahuan. Klaim "fail by default" pada remediasi `F-029` karena itu berlaku untuk penambahan tak terdaftar saja, bukan untuk kelalaian; catatan itu dikoreksi di sini | daftarkan akar temporal secara positif dari kontrak (identity/status/event/config/factor), lalu gagalkan bila salah satu tidak memiliki cutoff |
| `F-037` | `W18` | P2 | CLOSED (diremediasi 2026-08-11: satu helper `governingSnapshot()` melayani kedua cabang, rentang dinyatakan normatif; konfigurasi tak berubah kini satu snapshot lintas tanggal, bukan satu per tanggal) | `Replay_Verification_Contract_LOCKED.md` | **`resolveForRun` menjawab "snapshot mana milik tanggal ini" dengan dua cara berbeda.** Cabang tanpa cutoff mencocokkan `effective_at` persis pada `<tanggal> 00:00:00` (`MarketDataConfigSnapshotRepository.php:47`); cabang as-known memakai rentang `effective_at <= '<tanggal> 23:59:59'` (`:90`). Untuk tanggal yang sama kedua cabang karena itu dapat mengembalikan baris ber-`effective_at` berbeda — mode as-known bisa mengembalikan snapshot bertanggal efektif lebih awal. Asimetri ini dibuat oleh remediasi `F-035` pada hari yang sama. Dampak produksi nihil karena cabang as-known belum tersambung ke pemanggil mana pun, tetapi itu tepatnya kondisi yang membuatnya menjadi jawaban salah yang senyap begitu disambungkan. Tercatat juga sebagai konsekuensi: karena cabang create memakai `effective_at` per tanggal, satu konfigurasi yang tidak berubah tetap melahirkan satu baris snapshot per tanggal — sehingga `config_snapshot_id` bukan identitas stabil sebuah konfigurasi, hanya `config_hash` yang demikian | satukan aturan pencocokan tanggal di kedua cabang dan nyatakan mana yang normatif; bila rentang yang benar, cabang create harus mengikutinya |
| `F-033` | `W18` | P0 | CLOSED (diremediasi 2026-08-11: `seal_provenance_scope` mencatat cakupan seal; gerbang menuntut manifest hanya pada cakupan `FULL`; recompute 2026-07-27 tersegel `ANALYTICAL_ONLY`, run yang mengakuisisi tetap gagal tanpa manifest) | `Dataset_Integrity_Contract`; `EodPublicationRepository::assertCandidateIntegrityContext` | **Gerbang integritas dataset digerbangi oleh kondisi yang tidak pernah benar.** `EodPublicationRepository.php:1049` membungkus pemeriksaan `config_snapshot_id` dan `observation_manifest_hash` di dalam `if (! empty($run->config_snapshot_id))`. Karena setiap run ber-`config_snapshot_id` NULL, blok itu **tidak pernah dieksekusi untuk satu pun dari 64.939 publikasi** — termasuk 843 run recompute 2026-08-10/11 yang seal-nya berhasil justru karena config-unbound. Mengikat identitas config pada `F-032` mengaktifkannya, dan gerbang itu langsung menolak: `observation_manifest_hash` NULL pada **seluruh 64.939 publikasi dan 72.765 run**, karena `md_source_observations` kosong (bergandengan dengan `P1-29`). **Konsekuensi operasional: promote run baru tidak dapat di-seal sampai observation manifest ada.** Penolakannya benar dan fail-closed — produksi utuh, 844 publikasi current tetap 844, kandidat gagal tertinggal `UNSEALED` dengan `is_current=0` | putuskan antara memproduksi observation manifest (menuntut akuisisi ulang sumber, lihat `P1-29`) atau membatasi klaim seal secara eksplisit; **jangan** melonggarkan gerbangnya kembali menjadi tidur |
| `F-031` | `W18` | P0 | CLOSED (diremediasi 2026-08-11: `$knownAt` diteruskan ke keempat akar; guard `AsKnownWiringAndConfigIdentityTest`) | `Replay_Verification_Contract_LOCKED.md` | **Cutoff yang ditambahkan `F-028` tidak tersambung di jalur produksi.** `EodIndicatorsComputeService.php:87` sudah menghitung `$knownAt = $run->started_at ?? $run->created_at` dan meneruskannya ke akar sektor pada baris 89 — lalu baris 91 memanggil `resolveEventRiskContextForTickerIds($tickerIds, $requestedDate)` **tanpa** `$knownAt`, dengan variabelnya dua baris di atas. Sama pada `resolveCorporateActionContaminationForTickerIds` (`:216`) dan `resolveAdjustmentFactorsForTickerIds` (`:260`). Remediasi `F-028` menambah parameternya dan tidak menyambungkannya, sehingga future leakage tetap hidup di jalur compute yang justru sudah memegang cutoff. Bentuk cacat yang sama dengan yang berulang sesi ini, kali ini dibuat oleh remediasinya sendiri | teruskan `$knownAt` pada ketiga pemanggil, lalu buktikan dengan pengukuran pada run nyata bahwa aksi yang dicatat setelah `started_at` tidak masuk ke vector |
| `F-032` | `W18` | P0 | CLOSED sebagai binding (diremediasi 2026-08-11: `createPromoteRunFromSeed` mengikat snapshot; run 72910 ber-`config_snapshot_id=1`, `md_config_snapshots` 0→1 baris). Klaim replay-tidak-lagi-`REPLAY_CONFIG_UNBOUND` **belum dapat dibuktikan** karena tertahan `F-030` dan `F-033` | `Replay_Verification_Contract_LOCKED.md` (frozen config identity) | **Identitas config tidak pernah dibekukan.** `md_config_snapshots` **kosong (0 baris)**, dan `config_snapshot_id` NULL pada **72.764 dari 72.764** `eod_runs` serta **64.938 dari 64.938** `eod_publications`. Klausa "config revisions" pada exit gate stage 18 dan required outcome "frozen revision/config/factor/formula/product/read-model identity" keduanya tidak terpenuhi. Konsekuensi kedua yang lebih tajam: `ReplayVerificationService::replayAdmissibility()` memblokir setiap replay tanpa config snapshot sebagai `REPLAY_CONFIG_UNBOUND`, sehingga `F-030` **tidak dapat ditutup bahkan dengan fixture independen** selama tidak ada run yang terikat snapshot | tulis config snapshot per run dan ikat `config_snapshot_id`, lalu buktikan replay tidak lagi diblokir `REPLAY_CONFIG_UNBOUND` |
| `F-030` | `W18` | P1 | OPEN — lubang aturan ditutup 2026-08-11 (`fixture_source` kini diperiksa, bukan hanya label family), tetapi **fixture ber-ekspektasi independen tetap belum ada**, sehingga gate exact-replay masih belum dapat disertifikasi | `Replay_Verification_Contract_LOCKED.md` | Gate exact-replay ("matches values/nulls/reasons/lineage/hashes") **tidak dapat disertifikasi**: seluruh 20.635 baris `MATCH`/`PASS` pada `md_replay_daily_metrics` ber-`replay_suite = 'runtime_generated_valid_case'` dan `fixture_source LIKE 'generated_from_run_%'` — 20.635 dari 20.635 tidak admissible menurut aturan W18 sendiri. Baru terlihat setelah `F-025` membuat verdict dapat disimpan. `PASS` historis W18 sebagian bersandar pada korpus ini | susun fixture yang ekspektasinya independen dari run yang diuji; bergandengan dengan `F-024` |
| `F-029` | `W18` | P1 | CLOSED (diremediasi 2026-08-11: 13 akar terdaftar di 6 repository, plus sapuan refleksi yang menggagalkan akar tak terdaftar) | `Replay_Verification_Contract_LOCKED.md` | `AsKnownReplayBoundaryTest::test_every_temporal_root_accepts_a_knowledge_cutoff` menyebut **3 akar** (identity, status, calendar), sedangkan **9 metode repository** sudah menerima `knownAt` dan exit gate menamai **5 jenis revisi** (identity/status/event/config/factor). Yang tidak terjaga termasuk `SectorClassificationRepository::resolveSectorContextForTickerIds`, akar temporal yang baru lahir pada 2026-08-10 saat sector membership menjadi ber-interval; guard-nya tidak ikut diperluas | daftarkan setiap akar temporal pada guard, dan jadikan penambahan akar baru gagal secara default alih-alih lolos diam |
| `F-027` | `W12` | P0 | CLOSED 2026-08-14 untuk scope MLPT/RAJA/RMKE: `F-027a` merekam tiga event revision `AUTHORITATIVE_VERIFIED`; `F-027b` membentuk keputusan factor per publication pada korpus admitted dan menahan seluruh event ketika source scale tetap `UNKNOWN` | `Price_Adjustment_Contract_LOCKED.md` | Klaim legacy 2026-08-11 bukan authority yang sah. Tahap 6 tidak menimpa history itu. Tahap 8 juga tidak mengarang source scale: 23 keputusan event/publication tersimpan sebagai `HELD_SOURCE_SCALE_UNKNOWN`, nol factor tidak-admissible diterapkan, dan oracle decision/application bernilai nol | Tidak ada sisa untuk tiga event bernama ini. Rekonsiliasi corporate-action full-range tetap `F-010`, bukan alasan membuka kembali `F-027` |
| `F-026` | `W12` | P1 | CLOSED 2026-08-14 untuk korpus conformant admitted | `Price_Adjustment_Contract_LOCKED.md` (immutable `RAW`) | Publikasi replacement `2026-07-08`…`2026-07-28` dibentuk lewat lifecycle normal dan seluruh bar aktif menyatakan exact `RAW`; history legacy sebelum admission tetap tanpa relabel dan common resolver menolaknya | Memperluas admission ke tanggal lebih awal menuntut kampanye/evidence baru dan tidak mengubah closure suffix saat ini |
| `F-025` | `W18` | P1 | CLOSED (diremediasi 2026-08-11; enum diperluas, verdict tersimpan dan terbaca, `error_count` 1→0) | `Replay_Determinism_Contract` | `ReplayVerificationService.php:55` menulis `comparison_result = 'NOT_ADMISSIBLE'`, tetapi kolomnya `enum('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED')` dari migrasi `2026_05_19_000002` dan tidak pernah diperluas; setiap replay tidak-admissible gagal disimpan dengan `Warning: 1265 Data truncated` alih-alih tercatat. Ditemukan saat menjalankan replay proof `F-024` | perluas enum agar memuat `NOT_ADMISSIBLE`, lalu buktikan verdict tidak-admissible benar-benar tersimpan dan terbaca |
| `F-024` | `W12` | P0 | OPEN — 3 dari 4 tuntutan ditutup pada remediasi 2026-08-11; tersisa **replay proof**, terblokir oleh `F-025` dan oleh ketiadaan fixture yang tidak self-generated (`P0-04`, reopened 2026-08-08) | `Price_Adjustment_Contract` + `Indicator_Registry_Baseline` | provider `adj_close` fallback sudah hilang, tetapi selected `STRUCTURAL_ADJUSTED` belum dibind run-wide; vector tanpa applied factor masih dapat dilabeli `RAW`; legacy config masih memakai `price_basis_default=close` | implement selected product/factor/config binding run-wide; factor=1 tetap `STRUCTURAL_ADJUSTED`; fresh recompute + replay proof. **Sisa per 2026-08-11: hanya replay proof.** Binding run-wide terbukti (844/844 run terikat), `factor=1` terbukti, `price_basis_default` dicabut; replay proof menuntut fixture ber-ekspektasi independen yang belum ada |
| `F-023` | `W21` | P0 | OPEN (dipersempit dua kali) | `Test_Coverage_Closure_Contract` | Historical W21 snapshot had 4 P0 closed; strict 2026-08-08 re-audit reopened P0-04/F-024. Nine invariants had real-market production-path evidence; activated consecutive-session evidence remains absent and P1 corpus gaps remain | after F-024 remediation, re-evaluate stage-21 proof plus activated operational evidence |
| `F-021` | `W19` | P1 | OPEN / `PRE_ACTIVATION_DEFERRED` (`P1-39`) | `Release_Gates` | pengukuran ulang 2026-08-13: `operational_start_date` kosong dan `NULL` pada 72.777 dari 72.777 run; `MARKET_DATA_DAILY_ENABLED=false`; seluruh keluaran berstatus `DEVELOPMENT`, sesuai fase pembangunan | keputusan aktivasi adalah keputusan operator setelah activation checklist terbukti; bukan pekerjaan kode dan bukan blocker burn-down pembangunan |
| `F-020` | `W18` | P1 | OPEN (`P1-38`) | `Replay_Verification_Contract` | 20.635 hasil replay seluruhnya `PASS` dari fixture yang dihasilkan dari run yang diverifikasi; `config_identity` konstanta `'v1'` | korpus lama tidak admissible; menuntut fixture ber-author independen |
| `F-019` | `W17` | P1 | OPEN (`P1-37`) | `Read_Side_Enforcement_Anti_Bypass_Contract` | nol route market-data dan nol domain hilir; larangan bypass tidak dapat dilanggar sekaligus tidak dapat diamati | menuntut konsumen nyata sebelum kepatuhan read-side dapat dibuktikan |
| `F-018` | `W16` | P1 | CLOSED 2026-08-14 untuk korpus conformant admitted (`P1-36`) | `EOD_Eligibility_Snapshot_Contract` | 15 publikasi replacement membawa satu snapshot per temporal listing beserta delapan dimensi/reasons; status penuh-sesi yang dikecualikan mengikat revision dan source observation IDX | History legacy tidak diisi ulang dan tetap non-readable sebelum admission |
| `F-043` | `W15` | P1 | CLOSED (diremediasi 2026-08-11: hash kanonik terukur `aa7357061a66c757…`, terbukti berubah saat universe berubah dan tetap saat tidak) | `Coverage_Universe_Definition_LOCKED.md:52` | **Universe coverage tidak punya version maupun hash.** Yang tersimpan hanya `coverage_universe_count` dan `coverage_universe_basis`; satu-satunya `universe_hash` di seluruh database milik `watchlist_bt_eval`, subsistem backtest yang berbeda. Dua run untuk tanggal yang sama karena itu dapat meresolusi universe berbeda tanpa satu pun catatan universe mana yang dipakai. Bertetangga dengan `F-006` tetapi bukan hal yang sama: hash membuat pergeseran denominator **terdeteksi**, bukan **tidak terjadi** | catat hash kanonik atas himpunan listing yang membentuk universe beserta basisnya, mengikuti konvensi `factorSetHash`; buktikan hash berubah ketika himpunannya berubah dan tetap ketika tidak |
| `F-044` | `W15` | P1 | CLOSED (diremediasi 2026-08-11: 25 identitas dikecualikan tercatat dari 81, dibatasi batas sampel yang sama) | `Coverage_Universe_Definition_LOCKED.md:52` | **Sampel listing yang dikecualikan tidak pernah disimpan.** `coverage_missing_sample_json` menamai listing yang hilang — 11 pada 2026-07-27 — tetapi 81 yang dikecualikan sebagai `NOT_EXPECTED` hanya dihitung. Pembaca evidence dapat melihat berapa yang keluar dari penyebut, tidak siapa, sehingga pengecualian tidak dapat diperiksa ulang terhadap sumbernya | simpan sampel terbatas berisi identitas listing yang dikecualikan, mengikuti mekanisme dan batas sampel missing yang sudah ada |
| `F-017` | `W15` | P1 | CLOSED 2026-08-14 untuk korpus conformant admitted (`P1-35`) | `Coverage_Gate_Enforcement_Contract` | Seluruh 15 owning run menyimpan raw universe, expected, delivered, canonical-valid, unknown, not-expected, hash, sample, ratio, threshold, dan contract identity; minimum ratio `0,980022` terhadap threshold `0,980000` | Run legacy sebelum admission tidak diubah dan tidak dipakai sebagai bukti current |
| `F-016` | `W14` | P1 | CLOSED (basi; ditutup 2026-08-11 atas pengukuran ulang korpus sekarang: 32.008 titik ATR dibandingkan oracle ber-seed boundary menurut spec LOCKED, maks 0,009392%, nol titik >=0,01%. Kembar `P1-34` yang ditutup hari yang sama) (`P1-34`) | `Indicator_Registry_Baseline` | ATR tersimpan di-seed pada jendela geser; p90 `1,62%`, maks `72,9%` terhadap nilai ber-seed boundary | recompute berbukti atas korpus indikator |
| `F-015` | `W13` | P1 | CLOSED (basi; ditutup 2026-08-11 atas pengukuran ulang korpus sekarang: 32.559 titik dv20 dibandingkan oracle deret mentah, divergensi maks 0,000000%. Kembar `P1-33` yang ditutup hari yang sama) (`P1-33`) | `Volume_and_Turnover_Normalization` | 735.719 baris `dv20_idr` dihitung pada bar yang disesuaikan; aksi harga-saja menghasilkan adjusted price x raw volume | recompute berbukti; besaran dampak pada korpus lama belum diukur |
| `F-042` | `W12` | P0 | CLOSED (diremediasi 2026-08-11: `created_at` dan `recorded_at` dipertahankan pada kedua upsert; baris baru tetap terisi; penyebutan eksplisit dihormati) | `Replay_Verification_Contract_LOCKED.md`; `F-028` | **Impor ulang menggeser koordinat as-known.** `recorded_at` tetap berada di blok yang selalu ditulis pada kedua upsert, sehingga impor ulang tanpa menyebutkannya menimpanya dengan waktu sekarang. Dibuktikan pada baris MLPT produksi di transaksi yang di-rollback: `recorded_at` bergeser 15:08:07 → 16:12:27. Dampaknya membalik perlindungan yang `F-028` bangun: peristiwa yang benar-benar diketahui bulan Juni, bila diimpor ulang pada Agustus, menjadi **tak terlihat** oleh setiap cutoff sebelum Agustus. Arahnya berlawanan dengan kebocoran semula — ia menyembunyikan pengetahuan masa lalu alih-alih membocorkan masa depan — dan sama salahnya bagi replay. Ini sisi ketiga dari perbaikan `F-040`/`F-041`, yang memindahkan field opsional ke blok yang mempertahankan tetapi meninggalkan `recorded_at` di blok yang menimpa | `recorded_at` harus dipertahankan saat baris sudah ada dan pemanggil tidak menyebutnya, tetapi tetap terisi saat baris baru; `updateOrInsert` tidak membedakan keduanya sehingga keberadaan baris perlu diperiksa lebih dulu |
| `F-041` | `W12` | P0 | CLOSED (diremediasi 2026-08-11: aturan bersumber tunggal dipakai kedua upsert; sapuan membuktikan kelasnya hanya dua anggota; guard menutup kelas atas 9 situs) | `Price_Adjustment_Contract_LOCKED.md`; jalur event-risk | **Cacat `F-040` yang sama, di metode saudara yang tidak ikut diperbaiki.** `upsertTradingStatusEvent` masih menulis `source_ref` dan `notes` sebagai `$row[...] ?? null`, sehingga impor ulang tanpa kolom itu menghapusnya diam-diam. Dibuktikan pada baris ARCI produksi di dalam transaksi yang di-rollback: `source_ref` sebuah URL pengumuman IDX dan `notes` teks UMA-nya, keduanya menjadi NULL tanpa error. Paparannya total — **3.700 dari 3.700 baris** membawa keduanya. `F-040` memperbaiki `upsertCorporateAction` dan meninggalkan saudaranya, yang persis pola kesalahan yang dicatat pada sesi ini: satu sisi dari sesuatu yang bersisi dua | terapkan aturan yang sama — kunci tidak hadir mempertahankan, null eksplisit menghapus — dan periksa apakah masih ada metode upsert lain berbentuk serupa |
| `F-040` | `W12` | P0 | CLOSED (diremediasi 2026-08-11: ketiadaan kolom mempertahankan nilai, null eksplisit menghapus; diperbaiki di repository dan importer sekaligus) | `Price_Adjustment_Contract_LOCKED.md` | **Impor ulang menghapus faktor legacy yang mengklaim `EXCHANGE_ANNOUNCEMENT` tanpa suara.** `upsertCorporateAction` kini menulis kolom kuantitatif dengan `$row[...] ?? null`, sehingga `updateOrInsert` atas kunci (ticker, action_date, action_type, source_name) yang sama **menimpa faktor yang sudah ada dengan NULL** bila CSV berikutnya tidak memuat kolom itu. Diuji di transaksi yang di-rollback pada baris MLPT produksi: `price_adjustment_factor` 0,04 → NULL dan `adjustment_source` `EXCHANGE_ANNOUNCEMENT` → NULL, tanpa error maupun peringatan. Tahap 6 kemudian membuktikan source ref row itu bukan authority KSEI; fakta tersebut tidak mengubah tujuan guard preservasi ini. | pertahankan nilai tersimpan ketika kolom tidak hadir di CSV, bukan menimpanya dengan NULL; koreksi terms/provenance harus append-only pada revision authority, bukan efek samping kolom yang tidak diisi |
| `F-039` | `W12` | P0 | CLOSED 2026-08-14: `F-039a` keputusan pemilik; `F-039b` penerapan fail-closed pada korpus admitted | `Price_Adjustment_Contract_LOCKED.md` (immutable `RAW`) + `Yahoo_Finance_Bootstrap_Source_Strategy.md` | Yahoo tetap bootstrap primary EOD source dan bar canonical menyatakan `RAW`. Seluruh 13.860 bar replacement mencatat source-scale state terpisah sebagai `UNKNOWN`; 23 keputusan faktor terkait event ditahan, bukan diterapkan dua kali atau diasumsikan aman | History sebelum admission tidak direlabel. `UNKNOWN` yang jujur adalah outcome kontrak, bukan residu yang boleh diubah menjadi `AS_TRADED` tanpa evidence |
| `F-013` | `W12` | P1 | CLOSED (basi; ditutup 2026-08-11 oleh `MD-REAUDIT W12` atas bukti yang sama dengan `P1-32`: `eod_indicators` 756.328 baris, **nol** tanpa `price_product_code`) | `Price_Adjustment_Contract` | 756.328 baris indikator tanpa `price_product_code`; baris RAW dan STRUCTURAL_ADJUSTED tidak dapat dibedakan | recompute berbukti; pengisian retroaktif dilarang karena label yang benar belum tentu dapat direkonstruksi |
| `F-010` | `W11` | P1 | PARTIAL — `F-010a` CLOSED pada Tahap 6 untuk scope tiga stock split KSEI; parent tetap terbuka karena scope itu bukan klaim event-complete/full-range dan 123 dari 126 aksi legacy ber-impact `SCALED` masih belum memiliki revision terms otoritatif | `Corporate_Action_and_Adjustment_Policy` | Tiga event revision menyimpan listing identity, event/revision identity, lifecycle/verification state, cum/ex/record/distribution/effective dates, ratio, nominal lama/baru, ISIN, nomor/URL/hash/ukuran dokumen KSEI, dan `recorded_at`; announcement time yang tidak diketahui tetap `NULL` | lanjutkan rekonsiliasi eksternal corporate action dari intentional dataset start sebelum parent atau korpus disebut action-complete; penerapan scope tercatat tetap milik Tahap 8 |
| `F-011` | `W11` | P1 | CLOSED 2026-08-14: `F-011a` authority tercatat; `F-011b` resolver/binding diterapkan pada korpus admitted | `Exchange_Market_Structure_Facts` | 1.446 publication/listing binding berhasil `RESOLVED_STANDARD_BOARD`; 12.981 binding lain tersimpan dalam state fail-closed (`BOARD_UNKNOWN`, `BOARD_NOT_POINT_IN_TIME`, atau `NON_STANDARD_BOARD`). Oracle revision/cardinality/lineage bernilai nol | Tier tidak diproyeksikan ke history atau board yang tidak terbukti. Fail-closed adalah penerapan aturan, bukan tier yang hilang diam-diam |
| `F-007` | `W09` | P1 | CLOSED 2026-08-14 untuk korpus conformant admitted (`P1-29`) | `Canonicalization_Contract_EOD_Bars` | 13.860 bar replacement lahir dari observation Yahoo baru dan seluruhnya membawa listing, source observation, canonicalization version, exact `RAW`, quality, dan source-scale state | 756.329 bar legacy tetap history apa adanya; tidak direlabel dan tidak consumer-readable sebelum admission |
| `F-006` | `W08` | P1 | CLOSED (diremediasi 2026-08-12: run memperoleh kolom koordinat pengetahuan sendiri `eod_runs.knowledge_cutoff_at`, distempel sekali setelah proyeksi; penyebut terbukti deterministik pada korpus produksi — 3 evaluasi identik pada tiap cutoff, dan berubah 913→881 saat cutoff bergeser melewati 64 listing yang terekam belakangan) | `Coverage_Gate_Contract` | `2026-06-02` menghasilkan denominator 950 → 949 → 950 pada tiga run di hari eksekusi yang sama (`2026-06-07`), basis `ACTIVE_LISTED_EQUITY_AS_OF_DATE` | denominator as-of harus deterministik untuk tanggal tetap; diserahkan ke `W15` (temporal coverage gate) yang memiliki kontrak ini |

Blok di atas mempertahankan register audit sebelum penutupan bertahap. Status pada setiap row adalah
snapshot/override yang tercatat ketika row terakhir disentuh; blok ini bukan roster current dan
tidak boleh dipakai untuk menghitung finding terbuka.


<!-- LEGACY_EXTRACT_BODY_END -->
