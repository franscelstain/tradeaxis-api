# Legacy Semantic Extract — LX-MD-0034-CTX-05

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L874-L1297`
- Extract body SHA1: `4868F907CB00B3972A404006C489B5F39B5C35FA`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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

## HISTORICAL EXECUTION RECORD — W11 corporate-action event dan factor lifecycle stage 10, ditutup 2026-08-06

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

### HISTORICAL, SUPERSEDED — `F-011` — band, floor, dan tick masih konstanta

`Exchange_Market_Structure_Facts_LOCKED.md:79` melarang konstanta tak bersumber dipakai untuk keputusan apa pun yang mencapai published output. Nyatanya tidak ada tabel tier sama sekali: `min_price_idr` konstanta config `50`, dan dokumen registry sendiri mencatat band sebagai skalar hardcoded `0.35` berstatus placeholder. Lima aksi berstatus `GAP_BEYOND_EXCHANGE_BAND` diputuskan memakai band itu.

Dampaknya terhadap published output berkurang drastis setelah `F-010` ditutup, karena verdict band-based tidak lagi menghasilkan faktor yang terpakai. Yang tersisa saat snapshot W11 ini dibuat adalah larangan menyebutnya exchange-verified. Snapshot tersebut disupersede oleh penutupan `F-011a` pada Tahap 7 tanggal 2026-08-13; current state berada pada register temuan dan blok Tahap 7 di akhir ledger.

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

### HISTORICAL, SUPERSEDED — `F-014` — fallback yang mengarang kode produk

Jalur baca lama pernah mengganti identitas kosong menjadi `RAW`. Baris yang tidak pernah mencatat produknya tidak menjadi `RAW` karena dibaca; karena seluruh 756.329 baris lama membawa `NULL`, fallback itu menegaskan klaim skala yang barisnya sendiri tidak pernah buat — bentuk yang sama dengan `PROJECTED` menjadi `EXPECTED` pada W06. Remediasi snapshot W12 pertama-tama menggantinya dengan penanda per-row. Tahap 5 kemudian **mencabut kompromi kedua itu juga**: state aktif sekarang menahan publication pada common gateway dan tidak lagi menyajikan harga atau field reason per-row.

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


<!-- LEGACY_EXTRACT_BODY_END -->
