# Legacy Semantic Extract — LX-MD-0034-CTX-03

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L97-L474`
- Extract body SHA1: `F0ABF98F468A9FB5F7613FCC811504D4FF0E28D8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## W18 remediation `F-037` — 2026-08-11

Kedua cabang `resolveForRun` kini memakai satu aturan pencocokan tanggal, dan **rentang dinyatakan normatif**: snapshot yang mengatur sebuah tanggal adalah yang terbaru berlaku pada atau sebelum tanggal itu. Satu helper `governingSnapshot()` melayani keduanya; `$knownAt` hanya mempersempitnya ke apa yang sudah tercatat pada suatu saat.

Alasan rentang yang dipilih, bukan pencocokan persis: platform ini bitemporal di seluruh akar lain — membership, kalender, status — dan snapshot config dibaca sebagai interval oleh cabang as-known sejak awal. Pencocokan persis adalah yang menyimpang.

**Perilaku yang ikut diperbaiki, dan ini yang paling berdampak.** Dengan pencocokan persis, konfigurasi yang tidak berubah tetap melahirkan baris baru untuk setiap tanggal yang diproses: satu recompute rentang penuh akan menulis hingga 844 snapshot ber-`config_hash` identik. `config_snapshot_id` karena itu bukan identitas sebuah konfigurasi melainkan surrogate per-run. Kini konfigurasi tak berubah dipakai ulang lintas tanggal, dan baris baru hanya ditulis ketika isinya benar-benar berbeda — berlaku sejak tanggal perubahan itu.

Urutannya juga dibetulkan: `effective_at` menentukan interval mana yang mengatur, `recorded_at` menentukan revisi terbaru dari interval itu, dan id hanya memutus seri sungguhan. Versi sebelumnya mengurutkan `recorded_at` lebih dulu, yang dapat memilih revisi interval lama di atas interval yang benar.

## W18 remediation `F-035`, `F-036` — 2026-08-11

**`F-036` — dokumen tugas disamakan, dan guard-nya dibuat.** `backtest/Replay_Results_Schema_MariaDB.sql` kini mendeklarasikan enum lima nilai dan menjelaskan `NOT_ADMISSIBLE` di blok `LOCKED SEMANTICS`. `ReplayResultsSchemaDocumentSyncTest` mengunci dua arah: DDL dokumen harus sama dengan kolom terdeploy, dan setiap anggota enum harus dijelaskan pada blok semantiknya. Arah kedua itu yang dulu terlewat — kosakata dokumen lebih sempit dari kenyataan adalah klaim basi tersendiri, dan tidak ada guard yang pernah membandingkan keduanya.

**`F-035` — akar config memperoleh cutoff, dan pendaftarannya dibalik arah.** `MarketDataConfigSnapshotRepository::resolveForRun` kini menerima `$knownAt`. Tanpa cutoff ia tetap resolve-or-create, yang benar untuk run nyata karena run berjalan di bawah config hari ini. Dengan cutoff ia menjadi **lookup murni dan tidak pernah menyisipkan**: find-or-create pada mode as-known akan menjawab dengan konfigurasi yang belum ada, yakni mengarang persis pengetahuan yang hendak ditahan cutoff. Bila tidak ada yang tercatat sampai saat itu, jawabannya `CONFIG_SNAPSHOT_NOT_KNOWN_AT_CUTOFF`, bukan snapshot baru.

Guard-nya ditambah dari arah kontrak: `contractRequiredRoots()` menurunkan lima jenis revisi yang disebut exit gate — identity, status, event, config, factor — dan menggagalkan bila salah satunya tidak memiliki akar ber-cutoff. Sapuan refleksi `F-029` hanya menangkap akar yang **punya** `knownAt` tanpa terdaftar; ia tidak dapat menangkap akar yang kontraknya wajibkan tetapi tidak pernah tumbuh cutoff — persis bagaimana `resolveForRun` lolos. Dua arah itu kini tertutup.

**Yang sengaja tidak diklaim.** Kapabilitas cutoff config ini **belum tersambung ke pemanggil mana pun**, dan itu bukan kelalaian seperti `F-031`: kedua pemanggil `resolveForRun` yang ada — `getOrCreateOwningRun` dan `createPromoteRunFromSeed` — membuat run baru dan memang harus memakai config hidup. Jalur recompute as-known belum ada, sehingga belum ada tempat yang sah untuk menyambungkannya. Dicatat di sini supaya audit berikutnya menemukan alasannya tertulis, bukan menemukannya sebagai kejutan.

## W18 remediation `F-034` — 2026-08-11

`ReplayVerificationService.php:696` mengambil `config_identity` dari `$run->config_version`, yang bernilai `'v1'` pada seluruh 72.765 run. Sumbernya kini `config_hash`, yang content-addressed atas config terselesaikan.

**Yang membuat perbaikan ini tidak kosmetik**, dibuktikan lewat resolver produksi dan bukan nilai buatan: `MarketDataConfigSnapshotRepository::resolveForRun` dipanggil dua kali dengan `roc_lookback_days` diubah di antaranya, menghasilkan **dua snapshot dengan `config_hash` berbeda** — sehingga identitas replay ikut berbeda. Itu persis pembedaan yang `config_version` tidak pernah bisa buat.

**Jebakan kedua yang ikut ditutup.** `compareField()` keluar lebih awal saat `$expected === null`, **sebelum** mencatat field itu ke `deterministicFieldsChecked`. Mengganti sumber ke `config_hash` begitu saja akan memindahkan kesenyapan: 72.764 run tanpa hash akan dilewati tanpa jejak, dan evidence-nya menunjukkan identitas config tidak dibandingkan sekaligus tidak dilaporkan hilang. Karena itu run tanpa identitas menghasilkan penanda eksplisit `CONFIG_IDENTITY_UNRECORDED`, bukan null. Penanda itu keadaan yang harus ditutup dengan mengikat identitas pada run-nya, bukan diisi karangan oleh pembaca.

Konsekuensi yang perlu diketahui: 20.635 baris fixture historis membawa `expected_config_identity = 'v1'`, sehingga replay ulang terhadapnya kini menghasilkan mismatch. Itu benar — fixture-nya basi — dan tidak mengurangi apa pun, karena seluruhnya sudah tidak admissible menurut `F-030`.

## W18 remediation `F-025` — 2026-08-11

`MD-REMEDIATE W18 findings F-025`. Cacatnya tunggal dan berada di kosakata penyimpanan, bukan di logika: `ReplayVerificationService.php:55` menulis `comparison_result = 'NOT_ADMISSIBLE'`, sedangkan kolomnya masih `enum('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED')` dari migrasi `2026_05_19_000002`. Sisi aplikasi sudah benar sejak awal — `ReplayResultRepository::replayStatusForComparison()` memetakan apa pun di luar keempat nilai itu ke `replay_status = BLOCKED`.

Migrasi `2026_08_11_000001_allow_not_admissible_replay_comparison_result` memperluas enum (48ms). `down()` menolak menyempitkan kembali selama masih ada baris `NOT_ADMISSIBLE`, karena di bawah `sql_mode` non-strict penyempitan akan mengosongkan sel itu — mengubah penolakan-menghakimi yang tercatat menjadi sel kosong yang terbaca seperti hasil bersih.

**Bukti sebelum/sesudah pada perintah yang sama.** `market-data:evidence-replay:full-range-current 2026-07-21 2026-07-28`:

| | Sebelum | Sesudah |
|---|---:|---:|
| `error_count` | 1 | **0** |
| `failed_count` | 0 | **1** |

Sebelumnya run itu **error** karena verdict-nya tidak dapat disimpan; sekarang ia **gagal** karena verdict-nya tercatat. Baris tersimpan dan terbaca: `comparison_result=NOT_ADMISSIBLE`, `replay_status=BLOCKED`, `comparison_note='Runtime-generated replay fixture expectation for run_id=72905.'` 20.635 baris historis tidak tersentuh.

**Temuan yang baru terlihat setelah verdict dapat disimpan — dicatat sebagai bukti tambahan `F-024`, bukan temuan baru.** Seluruh **20.635 baris `MATCH`/`PASS`** pada `md_replay_daily_metrics` ber-`replay_suite = 'runtime_generated_valid_case'` dengan `fixture_source LIKE 'generated_from_run_%'` — 20.635 dari 20.635. Di bawah aturan admissibility W18, **seluruh korpus replay "lulus" yang ada tidak admissible**: ia hanya membuktikan tiap run sama dengan dirinya sendiri. Ini memperkuat, bukan melemahkan, alasan `F-024` tetap terbuka.

Perlu dicatat pula: sebelum remediasi ini **tidak ada satu pun test** yang menyebut `NOT_ADMISSIBLE`, `replayAdmissibility`, atau `SELF_GENERATED`. Aturan W18 nol tercakup test, dan itulah sebabnya verdict yang tidak pernah bisa disimpan tetap terkirim. Guard baru `ReplayAdmissibilityVerdictStorabilityTest` menutup keduanya: nilai harus muat di kolomnya, dan tidak boleh dihitung sebagai pass di ketiga tempat yang memutuskan kelulusan.

## W18 remediation `F-033` — 2026-08-11

Dikerjakan karena `F-033` berdiri persis di antara jalur impor faktor yang baru dibuka dan manfaatnya: faktor yang diimpor hanya berlaku setelah recompute, dan recompute lewat `createPromoteRunFromSeed` — jalur yang gerbang integritas tolak seal.

**Diagnosis pagi ini keliru dan dikoreksi di sini.** `observation_manifest_hash` diproduksi jalur ingest lewat `manifestHashForRun()`; ia mencatat observasi mana yang menghasilkan kandidat. Run recompute **tidak mengakuisisi apa pun** — ia menghitung ulang analitik di atas bar yang sudah ada. Menuntutnya menyerahkan manifest akuisisi berarti memintanya bersaksi atas pekerjaan yang tidak ia lakukan. Jadi `F-033` bukan menuntut 800.000 akuisisi ulang; ia menuntut tuntutan yang sepadan.

**Yang dikerjakan.** Kolom baru `eod_publications.seal_provenance_scope` (migrasi `2026_08_11_000003`) menyatakan cakupan seal sebagai field kelas satu — bukan baris log, sesuai prinsip yang `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md` tuntut untuk identitas. `FULL` berarti identitas config dan provenance akuisisi keduanya tercakup; `ANALYTICAL_ONLY` berarti run menghitung ulang analitik dan tidak ada provenance akuisisi untuk diwariskan.

Gerbang kini menuntut manifest hanya ketika cakupannya `FULL`. Manifest yang diwariskan mengalahkan mode: recompute yang mewarisi provenance nyata tetap `FULL`, karena provenance itu ada dan benar tak peduli run mana yang pertama mencatatnya.

**Ini bukan pengembalian gerbang ke keadaan tidur.** Bedanya tegas dan diuji: run yang **mengakuisisi** dan tidak dapat menyerahkan manifest tetap gagal persis seperti sebelumnya — `SealProvenanceScopeTest::test_an_acquiring_run_without_a_manifest_is_still_held_to_full_scope` menguji tiga mode akuisisi. Yang berubah hanya bahwa run yang tidak mengakuisisi berhenti dituntut menyerahkan sesuatu yang tidak mungkin ia punya, dan batasannya tertulis di publikasi.

**Bukti.** Recompute 2026-07-27 yang pagi ini gagal `DATASET_MANIFEST_INVALID` kini `status=SUCCESS`, publikasi 73586 versi 11 `SEALED` dan `is_current=1` dengan `seal_provenance_scope=ANALYTICAL_ONLY` serta `config_snapshot_id=1` — publikasi pertama di korpus yang membawa identitas config sekaligus menyatakan batas seal-nya. 844 publikasi current tetap 844; kandidat gagal 73585 tetap `UNSEALED`.

## `MD-REAUDIT W15` — 2026-08-11 keempat — HISTORICAL, SUPERSEDED UNTUK `F-045`

> **STATUS HISTORIS — BUKAN CURRENT FINDING.** Blok ini mempertahankan keadaan yang ditemukan
> pada 2026-08-11. Temuan ekspor `F-045` di dalam blok ini diselesaikan oleh Tahap 2 pada
> 2026-08-12; status otoritatifnya adalah `CLOSED` pada bagian Tahap 2 dan ia tidak boleh dibaca
> kembali sebagai `OPEN` atau `PARTIAL`.

**Koreksi klaim saya sendiri.** Remediasi `F-043` menulis *"terukur pada produksi: `aa7357061a66c757…`"*. Itu keluaran **evaluator** yang dijalankan atas data produksi, **bukan baris tersimpan**. Diukur sekarang: **0 dari 72.776 run** memiliki `coverage_universe_hash` maupun `coverage_excluded_sample_json`, karena tidak ada run produksi sejak migrasi. Jalur tulisnya benar menurut kode dan suite, tetapi **belum pernah dieksekusi**.

**Sisi ketiga ditemukan, dan lebih luas dari dugaan.** Empat field bukti coverage tersimpan tanpa jalur ekspor — dicatat sebagai `F-045`. Yang membuatnya layak disorot: ia menembus tiga remediasi berturut hari ini. Saya memperbaiki sisi penyimpanan pada `expectation_unknown`, lalu `not_expected`, lalu hash dan sampel excluded, dan tidak sekali pun memeriksa apakah nilai itu keluar ke evidence pack.

**Yang tetap lulus**: `UNKNOWN` terukur dan tak dapat keluar dari penyebut; hash terbukti membedakan dua keadaan; identitas yang dikecualikan tercatat; tidak ada pengecualian yang memperbaiki coverage. Suite 1.397/1.397.

### Strategi penanganan

1. **`F-045` dulu, dan kerjakan sekaligus keempat field** — bukan satu per satu, karena penyebabnya satu: sisi ekspor tidak pernah ikut diperiksa saat sisi simpan diubah. Guard yang menggagalkan field coverage tersimpan tanpa jalur ekspor akan menutup kelasnya, bukan anggotanya.
2. **Buktikan jalur tulis dengan satu run nyata** setelah `F-045`, sehingga hash dan sampel excluded terbukti mendarat — bukan hanya terbukti terhitung. Satu tanggal cukup.
3. **Kriteria baru yang dicatat**: setiap kali sebuah field bukti ditambahkan ke penyimpanan, periksa jalur eksposnya pada perubahan yang sama. Pola "disimpan tetapi tidak diekspos" kini terbukti tiga kali berturut.

## W15 remediation `F-043`, `F-044` — 2026-08-11, setelah `F-017` dipecah

**`F-017` dipecah lebih dulu, dan itu memperbaiki cara kerja bukan sekadar penomoran.** Temuan itu bukan satu cacat melainkan sebuah kategori: bukti aslinya menyebut empat field dan pasal `:52` menuntut lima butir bukti. Tiga remediasi berturut mengerjakan butir yang berbeda di bawah satu ID, sehingga setiap kemajuan terbaca sebagai pengulangan dan ID-nya tidak akan pernah bisa ditutup. Kini terpisah: `F-043` universe hash, `F-044` sampel excluded, `F-017` menyisakan bukti per-listing.

**Koreksi atas klaim saya sendiri.** Audit sebelumnya menyatakan universe hash "membuka `F-006`". Berlebihan. Hash membuat pergeseran denominator **terdeteksi**, bukan **tidak terjadi**; `F-006` menuntut determinisme, `:52` menuntut bukti. Bertetangga, bukan sama, dan `F-006` tetap terbuka.

**`F-043`.** `coverage_universe_hash` mengikuti konvensi `AnalyticalProductIdentityService::factorSetHash`: tag versi skema, basis universe, tanggal, dan daftar identitas listing yang **diurutkan eksplisit** sehingga urutan hasil query tidak dapat mengubah hash. Terukur pada produksi 2026-07-27: `aa7357061a66c757…`.

**`F-044`.** `coverage_excluded_sample_json` menamai listing yang keluar dari penyebut, dibatasi oleh batas sampel yang sama dengan sampel missing. Pada 2026-07-27: 25 identitas tercatat dari 81 yang dikecualikan — cukup untuk memeriksa ulang terhadap sumbernya, yang justru tujuannya.

**Keduanya ditulis NULL bila evaluator tidak memproduksinya**, konsisten dengan aturan yang ditetapkan pada perbaikan `expectation_unknown`: "tidak diukur" dan "diukur sebagai kosong" harus tetap dapat dibedakan.

**Guard membuktikan hash membedakan dua keadaan, bukan sekadar ada.** Dua evaluasi atas universe yang sama menghasilkan hash identik; satu listing masuk dan hash berubah. Tanpa asersi kedua itu, hash konstan akan lulus dan tidak mencatat apa pun — pola `config_version = v1` yang sudah dua kali ditemukan sesi ini.

### Strategi penanganan

1. **`F-017` kini satu butir saja**: bukti ekspektasi per-listing (reason/source/version). Itu **tabel bukti baru**, bukan kolom, dan layak diputuskan terpisah — apakah bukti per-listing per-tanggal sepadan biayanya pada 962 listing x 844 tanggal.
2. **`F-006` tetap terbuka dan tidak tersentuh** oleh perbaikan ini. Yang berubah: pergeserannya kini terdeteksi lewat perbandingan hash, sehingga bila keputusan koordinat pengetahuan ditunda, dampaknya setidaknya terlihat.
3. **Pelajaran pencatatan**: temuan yang menampung satu kategori akan selalu tampak berulang. Kriteria baru — bila remediasi kedua atas satu ID mengerjakan butir yang berbeda, pecah ID-nya sebelum melanjutkan.

## `MD-REAUDIT W15` — 2026-08-11 ketiga, penutup siklus W15

**Sisi ketiga dari perbaikan `UNKNOWN` diperiksa lebih dulu**, karena tiga putaran sebelumnya gagal pada sisi yang tak diperiksa. `suspendedTickerIdsAsOf` kini melewati listing `UNKNOWN`, dan ia punya **tiga pemanggil**: `CoverageGateEvaluator`, `EodEligibilityBuildService`, dan `BackfillLifecycleOrchestrator`. Ketiganya mewarisi perubahan itu ke **arah fail-safe** — listing yang bukti ekspektasinya tak terbaca tidak dianotasi tersuspensi dan tidak dikeluarkan dari penyebut. Tidak ada sisi ketiga yang merusak.

**Verifikasi ulang**: universe 962, expected 881, not_expected 81, unknown 0 — masih terukur. Suite 1.395/1.395.

**Dua celah `:52` yang belum pernah diuji, dan keduanya satu tema.** `Coverage_Universe_Definition_LOCKED.md:52` menuntut lima butir bukti; tiga sudah ada (hitungan universe, ketiga hitungan ekspektasi, dan delivered/missing/invalid terpisah). Dua tidak:

- **Universe tanpa version/hash.** Yang tersimpan hanya `coverage_universe_count` dan `coverage_universe_basis`. Satu-satunya `universe_hash` di seluruh database milik `watchlist_bt_eval`, subsistem backtest yang berbeda. Akibatnya dua run untuk tanggal yang sama dapat meresolusi universe berbeda tanpa satu pun catatan universe mana yang dipakai — **ini `F-006` dilihat dari sisi bukti**. Hash universe akan membuat perbedaan itu terlihat bahkan tanpa cutoff pengetahuan, dan karena itu lebih murah daripada koordinat pengetahuan eksplisit yang `F-006` tuntut.
- **Sampel listing yang dikecualikan tidak ada.** `coverage_missing_sample_json` menamai 11 ticker yang hilang; ke-81 yang dikecualikan sebagai `NOT_EXPECTED` dihitung tetapi tidak pernah disebut. Pembaca evidence dapat melihat berapa yang keluar, tidak siapa.

Ketiganya — per-listing reason/source/version, universe hash, sampel excluded — adalah satu celah yang sama dilihat dari tiga sisi: **bukti coverage mencatat agregat, bukan identitas maupun versi di baliknya.**

### Strategi penanganan

1. **Kerjakan universe hash lebih dulu.** Ia paling murah, tidak menuntut skema baru per-listing, dan **membuka `F-006` dari sisi yang tidak buntu** — determinisme dapat dibuktikan dengan membandingkan hash, tanpa menunggu keputusan lifecycle tentang koordinat pengetahuan run.
2. **Sampel excluded menyusul** pada perubahan yang sama; mekanismenya identik dengan sampel missing yang sudah ada dan terbatas.
3. **Per-listing reason/source/version terakhir** — itu tabel bukti baru, bukan kolom, dan layak diputuskan terpisah.
4. **Jangan mengulang**: tuduhan aritmetika (ditarik), penyambungan `started_at` (terukur merusak), dugaan gate tidak independen (terukur salah).

## W15 remediation `F-017` — 2026-08-11, `UNKNOWN` menjadi terukur

**Langkah pertama bukan kode melainkan membaca kontraknya.** `Coverage_Universe_Definition_LOCKED.md` menyatakan `NOT_EXPECTED` hanya sah bila suspensi punya *"dictionary semantics explicitly state no bar expected"*, dan status sesi-parsial tidak otomatis mengecualikan. Kamusnya memang ada: `market_data_trading_status_event_types.expected_bar_policy` bernilai `BAR_REQUIRED`, `BAR_REQUIRED_WITH_RISK`, atau `BAR_NOT_REQUIRED`, dan seluruh 3.700 event produksi terpetakan.

**Sumber `UNKNOWN` yang konkret ditemukan di kode, bukan diteorikan.** `EventRiskSourceRepository:223` menjalankan `continue` ketika tipe event tidak ada di kamus — bukti yang platform pegang dan tidak dapat ia tafsirkan, dibuang diam-diam, sehingga listing-nya tampak `EXPECTED` polos. Jalur aksi korporasi sudah memperlakukan tipe tak terpetakan sebagai fail-safe dan menandainya `is_unmapped_type`; jalur status justru menjatuhkannya. `:21` melarang persis arah kesenyapan itu.

**Yang dikerjakan.** Tipe tak terpetakan kini menandai konteksnya `expectation_unknown` dengan alasan `TRADING_STATUS_TYPE_UNMAPPED:<kode>`. `suspendedTickerIdsAsOf` melewati listing ber-`UNKNOWN` sehingga ia **tidak dapat keluar dari penyebut** meski suatu flag suspensi ikut terderivasi — hanya `NOT_EXPECTED` terverifikasi yang boleh keluar. `expectationUnknownTickerIdsAsOf` ditambahkan dan didaftarkan pada registry akar temporal. `CoverageGateEvaluator` menghitungnya dari himpunan terfilter yang sama yang membentuk penyebut, sehingga identitas `EXPECTED + UNKNOWN = penyebut` berlaku secara konstruksi, bukan kebetulan aritmetika.

**Terukur pada produksi**: universe 962, expected 881, not_expected 81, unknown **0** — nol yang diproduksi, bukan nol default `?? 0` yang sebelumnya ditulis pada setiap run.

**Kehampaannya diuji, karena nol terukur bisa juga berarti pengukuran rusak.** Test menyemai status bertipe `SOME_FUTURE_IDX_STATUS` dan menegaskan tiga hal: listing itu dihitung `UNKNOWN`, penyebut **tetap** 10 sehingga ia tidak keluar, dan `not_expected` tetap 0 karena bukti itu tidak membuktikan tiadanya bar. Test kedua menegaskan universe bersih melaporkan nol yang diproduksi.

**Dua guard menagih janjinya dan dipenuhi, bukan dilonggarkan.** Sapuan akar temporal `F-029` menangkap metode baru yang menerima `knownAt` tanpa terdaftar. Dan guard `F-017` kemarin — yang sengaja menegaskan **ketiadaan** kunci `coverage_expectation_unknown_count` — gagal begitu evaluator mulai memproduksinya, persis seperti dirancang; ia diganti dengan dua test perilaku di atas.

### Strategi penanganan

1. **`F-017` tersisa satu butir**: `:52` juga menuntut *per-listing expectation reason/source/version*, sementara yang tersimpan baru hitungan agregat plus sampel kode ticker. Itu perubahan skema bukti per-listing, bukan penyambungan.
2. **`UNKNOWN` kini dapat membedakan dua keadaan** dan terbukti demikian, sehingga bila nanti IDX memperkenalkan kode status baru, coverage akan menandainya alih-alih menelannya.
3. **Jangan mengulang**: tuduhan aritmetika (ditarik), penyambungan `started_at` (terukur merusak), dugaan gate tidak independen (terukur salah, 2.471 kasus divergen).

## `MD-REAUDIT W15` — 2026-08-11 kedua, diaudit terhadap rencana

**Koreksi cara kerja.** Beberapa audit terakhir memeriksa diff terakhir saya alih-alih exit gate dan required outcome work order-nya. Pass ini membaca `Market_Data_Implementation_Conformance_Matrix_LOCKED.md` stage 12 lebih dulu dan menguji keenam butir required outcome-nya.

**Yang diukur dan lulus** — dicatat sebagai bukti, bukan ketiadaan temuan:

- **Ambang 98%**: satu nilai `0,980000` di seluruh 69.269 run ber-coverage. Konsisten.
- **Gate independen**: dugaan bahwa quality hanyalah turunan coverage **keliru dan terukur keliru**. Dari 69.269 run, **2.471 ber-quality `FAIL` sementara coverage `PASS`**, ditambah 18 kombinasi `BLOCKED`. Independensinya nyata, bukan hampa.
- **Rumus missing terdokumentasi** berlaku 856/856.
- **Exit gate**: tidak ada pengecualian yang memperbaiki coverage; penyebut penuh dipakai.

**Yang tidak terpenuhi, dan kini bersitasi kontrak.** `Coverage_Universe_Definition_LOCKED.md` menyatakan pada `:19` bahwa `UNKNOWN` berarti bukti ekspektasi tidak lengkap atau berkonflik; pada `:21` bahwa `UNKNOWN` **tetap masuk penyebut, fail-safe, dan terlihat**, serta tidak boleh diperlakukan diam-diam sebagai `NOT_EXPECTED`; pada `:52` bahwa ketiga hitungan `EXPECTED`, `NOT_EXPECTED`, `UNKNOWN` wajib dicatat; dan pada `:57` bahwa penyebut adalah `EXPECTED + UNKNOWN`.

Platform melipat `UNKNOWN` ke dalam `EXPECTED`. Rasionya **aman** — `UNKNOWN` tetap di penyebut sehingga coverage tidak membaik — tetapi ia **tidak terlihat**, tidak dapat dibedakan dari `EXPECTED`, dan itu persis yang `:21` larang dan `:52` wajibkan. Required outcome "unknown state" karena itu tidak terpenuhi, sementara exit gate-nya terpenuhi.

### Strategi penanganan

1. **`F-017` tetap terbuka dengan target bersitasi**: hitung `UNKNOWN` di `CoverageGateEvaluator` menurut `:19`, catat ketiganya terpisah menurut `:52`, dan pertahankan penyebut `EXPECTED + UNKNOWN` menurut `:57`. Guard `test_the_evaluator_does_not_supply_an_unknown_expectation_count` akan **gagal** begitu evaluator mulai memproduksinya, memaksa penulis pipeline menyusul — itu memang perannya.
2. **Pertanyaan yang harus dijawab lebih dulu**: apa yang membuat sebuah listing `UNKNOWN` pada model data sekarang. Suspensi tercatat atau tidak tercatat; tidak ada keadaan ketiga. Bila memang tidak ada sumber ketidakpastian, `UNKNOWN` selalu nol **dan itu harus dibuktikan**, bukan diasumsikan dari nilai default. Ini keputusan kontrak, bukan pekerjaan kode.
3. **Jangan mengulang**: tuduhan aritmetika (ditarik, evidence konsisten), penyambungan `started_at` (terukur merusak), dan dugaan gate tidak independen (terukur salah, 2.471 kasus divergen).

## W15 remediation `F-017` — 2026-08-11, dengan dua koreksi atas diri sendiri

### Koreksi pertama: penutupan `F-017` pagi ini tidak sah

Ditutup atas dasar "kelima kolom terisi pada 856/856 run" — bukan-null, bukan benar. Terisi tidak berarti bermakna.

### Koreksi kedua: pembukaan kembalinya juga salah

`MD-REAUDIT W15` membuka `F-017` dengan tuduhan aritmetika tak konsisten pada 749 dari 856 run, memakai persamaan `expected = universe - not_expected`. **Catatan ini merekam aturan lama pada saat audit dan bukan aturan aktif.** Saat itu `DB_FIELDS_AND_METADATA.md:17` menyebut `coverage_universe_count` sebagai penyebut dan sekitar 40 konsumen membacanya demikian. Pembersihan pascatutup tahap 1–2 pada 2026-08-12 mencabut aturan lama itu: kontrak aktif kini membedakan raw `coverage_universe_count`, denominator `coverage_expected_count`, dan numerator `coverage_delivered_count`; alias silang antarkolom dilarang oleh guard.

Menjalankan evaluator langsung menyelesaikannya: universe mentah **962**, expected **881**, not_expected **81** — `962 - 81 = 881`, dan rasio `870/881 = 0,987514`. Rumus terdokumentasi `missing = universe - available` berlaku pada **856/856**. Evidence-nya konsisten; pembacaan sayalah yang keliru dua kali.

### Cacat yang benar-benar ada

`CoverageGateEvaluator` **tidak pernah mengembalikan** `coverage_expectation_unknown_count` — nol kemunculan di seluruh berkas. Pipeline menulisnya sebagai `$coverage[...] ?? 0`, sehingga kolom itu memuat nol keras pada setiap run sambil tampak sebagai pengukuran. Satu nilai berbeda di seluruh korpus: bentuk yang sama dengan `config_version = v1` pada `F-034`.

**Perbaikannya menulis NULL ketika evaluator tidak memproduksi nilainya**, bukan 0. Itu mempertahankan pembedaan yang justru menjadi alasan hitungan sebelahnya ditulis sebagai integer: "tidak ada yang unknown" harus dapat dibedakan dari "tidak ada yang memeriksa". Guard barunya menegaskan **ketiadaan** kunci itu, sehingga bila evaluator kelak menghitungnya, test gagal dan memaksa penulisnya ditinjau — bukan membiarkan default bertahan.

### Strategi penanganan

1. **`F-017` menyempit ke satu butir**: unknown-expectation kini jujur (NULL bila tidak dihitung). Yang tersisa adalah keputusan apakah kontrak benar-benar menuntut keadaan `UNKNOWN` diukur; bila ya, evaluator harus menghitungnya, dan guard di atas akan memaksa penulisnya menyusul.
2. **Jangan mengulang tuduhan aritmetika.** Sudah diuji terhadap evaluator langsung dan dokumentasi kolom; evidence-nya konsisten. Mengulanginya akan menjadi perulangan tanpa dampak.
3. **Pelajaran yang dicatat**: dua kesalahan berturut pada temuan yang sama, keduanya karena menyimpulkan dari nama kolom alih-alih dari definisinya. Kriteria sebelum menuduh inkonsistensi: baca definisi field di `DB_FIELDS_AND_METADATA.md` dan jalankan produsernya langsung, sebelum menghitung selisih di SQL.

## `MD-REAUDIT W15` — 2026-08-11, penutupan `F-017` dibatalkan

**Penutupan `F-017` beberapa jam lalu tidak sah, dan pembatalannya berasal dari audit ini.** Ia ditutup atas dasar "kelima kolom terisi pada 856/856 run" — yaitu bukan-null, bukan benar. Terisi bukan berarti bermakna.

**Aritmetika antar-field tidak konsisten pada 749 dari 856 run.** Contoh 2026-07-27: `coverage_universe_count` 881, `coverage_expected_count` 881, `coverage_bar_not_expected_count` 81. Bila 81 benar-benar dikecualikan, `expected` seharusnya 800. `delivered` 870 + `missing` 11 = 881 pula, sehingga rasio dihitung terhadap penyebut penuh.

**Perilakunya aman; buktinya yang berbohong.** Memakai penyebut penuh tidak memperbaiki rasio, jadi exit gate stage 12 tidak dilanggar — justru dipatuhi secara konservatif. Yang salah adalah `coverage_bar_not_expected_count` melaporkan 81 baris diperlakukan `NOT_EXPECTED` padahal nol yang benar-benar keluar dari perhitungan. Pembaca evidence akan menyimpulkan pengecualian yang tidak terjadi.

**`coverage_expectation_unknown_count` konstan 0** pada seluruh 856 run — satu nilai, tak pernah bervariasi. Bentuk yang sama dengan `config_version = v1` pada `F-034`: field yang terisi, tampak diukur, dan tidak pernah membedakan dua keadaan.

### Strategi penanganan

1. **`F-017` dibuka kembali** dengan target yang tajam: bukan "isi kolomnya" melainkan **selaraskan aritmetikanya** — `expected` harus sama dengan `universe` dikurangi yang benar-benar dikeluarkan dari perhitungan, atau `not_expected` harus berhenti mengklaim pengecualian yang tidak diterapkan. Salah satu, dan dinyatakan mana yang normatif.
2. **Periksa `expectation_unknown` bersamaan**, karena keduanya bagian dari kuartet bukti yang sama. Bila memang tidak pernah ada unknown, itu perlu dibuktikan, bukan diasumsikan dari nilai konstan.
3. **Pelajaran yang dicatat**: penutupan atas dasar bukan-null adalah false proof. Kriteria penutupan untuk field bukti harus mencakup konsistensi antar-field, bukan keberadaannya saja. Ini sekelas dengan `F-013` dan `F-017` versi pertama — temuan basi ditutup tanpa memeriksa substansinya, hanya kali ini saya sendiri pelakunya.

## W15 remediation `F-006`, `F-017` — 2026-08-11

### `F-017` basi, ditutup atas bukti

Buktinya berbunyi `coverage_expected_count`, `delivered`, `delivered_valid`, `expectation_unknown` **seluruhnya 0 dari 71.917**. Diukur ulang: kelima kolom itu **terisi pada 856 dari 856 run** yang dibuat 2026-08-10/11. Mekanismenya sudah bekerja pada kode sekarang; yang kosong hanya korpus legacy, dan itu tidak dapat di-backfill jujur — sama seperti separuh legacy `F-026`. Bentuknya identik `F-013`: temuan yang menyatakan kondisi yang sudah tidak ada, dan tidak seorang pun memeriksanya sampai sekarang.

### `F-006` tetap terbuka, tetapi diagnosisnya jauh lebih dalam

**Yang diukur.** 202 tanggal memiliki denominator tak deterministik dalam satu hari eksekusi. Di antara run hari ini, nol — dan itu **tidak hampa**: 10 tanggal memiliki run berulang dan seluruhnya konsisten.

**Akar penyebabnya ditemukan.** Denominator punya dua masukan dan keduanya buta cutoff: `TickerMasterRepository::getUniverseForTradeDate` memanggil `universeAsOf($tradeDate)` tanpa `knownAt`, dan `filterSuspendedUniverseRows` memanggil `suspendedTickerIdsAsOf($tickerIds, $tradeDate)` tanpa `knownAt`. Keduanya menjawab "sebagaimana sekarang", sehingga apa pun yang dicatat di antara dua run menggeser denominator.

**Koreksi atas pembacaan awal.** Sempat diduga `filterSuspendedUniverseRows` melanggar exit gate karena mengecilkan penyebut. Keliru: `Coverage_Universe_Definition_LOCKED.md:21` mengizinkan pengecualian suspensi penuh-sesi terverifikasi sebagai `NOT_EXPECTED`, dan dormansi tetap di dalam. Filternya patuh kontrak.

**Perbaikan yang jelas dicoba, dan ditolak karena terbukti merusak.** `$knownAt = $run->started_at` disambungkan ke coverage dan eligibility. Hasilnya **31 error dan 5 kegagalan**: universe menjadi kosong dan coverage jatuh ke `NOT_EVALUABLE`. Sebabnya bukan fixture melainkan urutan nyata — `started_at` ditetapkan saat run dibuat, sedangkan identitas listing diproyeksikan setelahnya, sehingga `recorded_at > started_at` dan seluruh universe tersaring habis. Setiap identitas yang tercatat setelah run mulai akan tak terlihat oleh run itu. Perubahan dikembalikan; suite kembali 1.393/1.393.

**Syarat perbaikan yang sebenarnya.** `F-006` menuntut run memiliki **koordinat pengetahuan eksplisit tersendiri**, ditetapkan sekali dan tidak dipinjam dari `started_at`, karena `started_at` berurutan salah terhadap proyeksi identitas. Itu perubahan skema plus lifecycle, bukan penyambungan parameter.

**Yang tetap ada dan sengaja belum tersambung.** Parameter `$knownAt` pada `getUniverseForTradeDate`, `CoverageGateEvaluator::evaluate`, `filterSuspendedUniverseRows`, dan `suspendedTickerIdSet` dipertahankan, dan `getUniverseForTradeDate` didaftarkan pada registry akar temporal. Ini **bukan** pengulangan `F-031`: di sana ada nilai benar yang lupa diteruskan; di sini belum ada nilai yang benar untuk diteruskan. Dicatat di sini supaya audit berikutnya menemukan keputusan tertulis, bukan kejutan.

### Strategi penanganan

1. **`F-017` ditutup** — mekanisme terbukti pada 856/856; batasan korpus legacy dinyatakan, bukan di-backfill.
2. **`F-006` tetap terbuka dengan target yang dipersempit**: bukan lagi "buat denominator deterministik" melainkan "beri run koordinat pengetahuan eksplisit". Ia kini bergantung pada keputusan lifecycle, sehingga sekelas dengan `F-039` dan `F-038` — menunggu keputusan pemilik, bukan pekerjaan kode berikutnya.
3. **Jangan mengulang penyambungan `started_at`.** Sudah dicoba, terukur merusak, dan dikembalikan. Mengulanginya akan menjadi perulangan tanpa dampak.

## `MD-REAUDIT W12` — 2026-08-11, rantai remediasi berhenti

**Audit W12 pertama hari ini yang tidak menemukan cacat baru.** Rantai `F-040` → `F-041` → `F-042`, yang tiap putarannya melahirkan anggota berikutnya, berakhir di sini.

**Sapuan lintas-korpus atas koordinat as-known.** Tiga belas tabel memiliki `recorded_at`. Yang berbahaya bukan penulisnya melainkan pemutakhirnya, karena tabel revisi temporal seharusnya append-only. Diperiksa: `TemporalIdentityRepository` (`insertGetId`, hanya bila baris belum ada), `SectorClassificationRepository::insertRevision` (`insertGetId`), dan `MarketCalendarRepository` (`insert`) — **seluruhnya insert-only**. `MarketDataConfigSnapshotRepository` menyisipkan hanya pada cabang tanpa cutoff. Satu-satunya jalur yang menimpa adalah dua upsert event-risk, dan keduanya sudah ditutup pada `F-042`. Kelasnya karena itu tertutup di seluruh korpus, bukan hanya pada berkas yang disentuh.

**Biaya perbaikan diukur, bukan diabaikan.** Pemeriksaan keberadaan menambah satu SELECT per baris. Diukur 3,7 ms/baris pada 200 upsert berturut, memproyeksikan ~14 detik untuk impor terbesar yang realistis (3.700 status event). Wajar untuk operasi manual; dicatat sebagai biaya yang diketahui, bukan temuan.

### Strategi penanganan sisa W12

Lima temuan tersisa, **tidak satu pun berupa pekerjaan kode**, dan urutannya ditentukan oleh ketergantungan bukan severity:

1. **`F-039` lebih dulu — ia membuka atau menutup yang lain.** Keputusan arti `RAW`: apakah as-traded dan korpus perlu diakuisisi ulang, atau histori teradjust provider diterima dan faktor hanya diterapkan pada peristiwa yang terbukti belum disesuaikan. Jawaban ini menentukan nasib 32 split tersisa dan karenanya menentukan `F-027` juga.
2. **`F-038` paling murah** — satu penegasan apakah bar mentah termasuk "analytical row". Menentukan apakah 756.329 bar boleh disajikan dengan penanda atau harus ditahan.
3. **`F-027` mengikuti `F-039`** — menyempit dari 174 aksi tanpa faktor menjadi berapa pun yang tersisa setelah keputusan `RAW`.
4. **`F-026` mengikuti `F-039`** — separuh legacy hanya tertutup oleh re-ingest atau penerimaan tertulis, dan keduanya adalah konsekuensi dari keputusan yang sama.
5. **`F-024` terakhir** — tertahan `F-030` yang menuntut fixture ber-ekspektasi independen.

Iterasi audit berikutnya pada W12 **tidak akan menggerakkan satu pun** dari kelimanya. Menjalankannya lagi sebelum salah satu keputusan diambil akan menjadi pengulangan tanpa dampak.

## W12 remediation `F-042` — 2026-08-11

**Kelasnya diukur lebih dulu, bukan diasumsikan satu field.** `F-042` menamai `recorded_at`, tetapi `created_at` berada di blok yang sama dengan bentuk yang sama. Diukur pada baris MLPT produksi: **keduanya bergeser** 15:08:07 → 16:20:41. Memperbaiki hanya yang disebut temuan akan melahirkan `F-043` esok hari. Kelas sebenarnya karena itu empat: dua field pada dua metode, dan seluruhnya diperbaiki dalam satu perubahan.

**Aturannya.** `withDurableCreationTimestamps()` memeriksa keberadaan baris lebih dulu, karena `updateOrInsert` tidak memberi tahu pemanggilnya cabang mana yang diambil. Baris baru tetap memperoleh `created_at` dan `recorded_at`; baris yang sudah ada mempertahankannya kecuali pemanggil menyebutkannya. `updated_at` sengaja **tidak** ikut — artinya "kapan baris ini terakhir ditulis", sehingga menulisnya di setiap upsert memang benar.

**Kenapa ini penting melampaui kerapian.** `recorded_at` adalah koordinat yang `F-028` bangun agar replay as-known dapat menyembunyikan peristiwa yang dicatat setelah cutoff. Menggesernya membalik perlindungan itu: peristiwa yang benar-benar diketahui Juni, diimpor ulang Agustus, lenyap dari setiap cutoff sebelum Agustus. Platform akan **meremehkan** apa yang ia ketahui — sama salahnya bagi replay dengan kebocoran semula yang melebih-lebihkannya.

**Empat sifat dibuktikan pada kedua metode**, transaksi di-rollback: impor ulang mempertahankan `created_at` dan `recorded_at`; `source_ref` tetap utuh; baris baru tetap memperoleh keduanya sehingga jalur insert tidak rusak oleh perbaikan ini; dan pemanggil yang menyebut `recorded_at` eksplisit tetap dihormati sehingga koreksi tetap mungkin. `updated_at` tetap bergerak.

Satu kesalahan di test ditemukan dan diperbaiki sebelum dikirim: operator `+` PHP tidak menimpa kunci yang sudah ada, sehingga kasus "baris baru" sebenarnya meng-upsert baris yang sama dan tidak menguji apa pun. Diganti `array_merge`.

## W12 remediation `F-041` — 2026-08-11

Strategi putaran ini bukan memperbaiki satu metode lagi, melainkan **menghabiskan kelasnya**. Karena itu langkah pertama adalah sapuan, bukan suntingan.

**Sapuan.** Sembilan situs `updateOrInsert` di enam repository MarketData. Diskriminatornya bukan `updateOrInsert` melainkan nilai opsional yang ditulis `?? null`. Hasil: **hanya satu** yang membawanya — `upsertTradingStatusEvent`. Tujuh lainnya nol. Kelasnya karena itu persis dua anggota, `upsertCorporateAction` (ditutup pada `F-040`) dan yang ini.

**Aturannya kini bersumber tunggal.** `withPreservedOptionalFields()` dipakai kedua upsert: kunci tidak hadir mempertahankan nilai tersimpan, kunci hadir bernilai null menghapusnya. `F-040` memperbaiki satu metode dan meninggalkan saudaranya sehingga biayanya satu siklus audit penuh; sumber tunggal membuat upsert ketiga tidak dapat menyimpang dengan ditulis sendiri-sendiri.

**Bukti pada baris produksi**, transaksi di-rollback: impor ulang tanpa kolom → `source_ref` dan `notes` **utuh**; null eksplisit → keduanya NULL.

**Guard penutup kelas.** `test_no_upsert_writes_an_optional_field_as_null` menyapu seluruh repository MarketData dan menggagalkan setiap `updateOrInsert` yang membangun nilai dengan `?? null`.

Versi pertama guard itu **salah tangkap**: ia mencocokkan prosa di dalam docblock yang justru memperingatkan pola tersebut, dan melaporkan tiga pelanggar di kode yang tidak punya satu pun. Diperbaiki dengan membuang komentar lewat `token_get_all` lebih dulu. Kehampaannya juga diukur, karena guard yang tidak memeriksa apa pun akan lulus selamanya: setelah komentar dibuang ia masih memeriksa **9 situs di 6 berkas**, sama dengan hasil sapuan manual.

## W12 remediation `F-040` — 2026-08-11

Dikerjakan dengan disiplin yang diminta pemilik dan dimulai dari langkahnya: **mengenumerasi setiap penulis dan penimpa kolom faktor sebelum mengubah satu baris pun**. Hasil enumerasi: satu-satunya jalur tulis adalah `EventRiskSourceRepository::upsertCorporateAction`; `PriceScaleBreakDetectionService:195` hanya membaca; baris `DERIVED_FROM_PRICE_SERIES` memakai `source_name` berbeda sehingga tidak pernah bertabrakan dengan baris impor.

**Cacatnya.** `updateOrInsert` menulis setiap nilai yang diberikan, sehingga payload kuantitatif berbentuk `$row[...] ?? null` menghapus faktor tersimpan ketika CSV berikutnya tidak memuat kolomnya. Pemicunya justru CSV tiga kolom yang importer dokumentasikan sebagai sah.

**Perbaikannya memisahkan ketiadaan dari kekosongan.** Kunci yang tidak hadir membiarkan nilai tersimpan; kunci yang hadir bernilai null menghapusnya. Menghapus faktor beratribusi tetap mungkin, tetapi sebagai tindakan yang diminta pemanggil, bukan akibat kolom yang lupa diisi.

**Sisi kedua yang biasanya terlewat, dan kali ini tidak.** Importer sendiri selalu menyertakan `source_ref` dan `notes` dengan null saat kosong — cacat yang sama satu lapis di atas, yang akan menghapus provenance dokumen pada impor ulang. Keduanya kini memakai aturan yang sama.

**Bukti pada baris produksi**, di dalam transaksi yang di-rollback:

| Tindakan | `price_adjustment_factor` | `adjustment_source` |
|---|---|---|
| sebelum | 0,04 | `EXCHANGE_ANNOUNCEMENT` |
| impor ulang tanpa kolom | **0,04 (utuh)** | **`EXCHANGE_ANNOUNCEMENT` (utuh)** |
| null eksplisit | NULL | NULL |

Dua guard permanen: `EventRiskSourceRepositoryTest::test_an_omitted_column_preserves_a_stored_factor_while_an_explicit_null_clears_it` pada tingkat repository, dan `ImportCorporateActionsCommandTest::test_reimporting_a_minimal_csv_preserves_the_stored_factor` pada tingkat perintah. Keduanya menguji sisi mempertahankan **dan** sisi menghapus, karena perbaikan yang hanya mempertahankan akan menghilangkan kemampuan mengoreksi faktor yang keliru.

## HISTORICAL, SUPERSEDED — W12 remediation `F-027` — 2026-08-11

> **STATUS HISTORIS — BUKAN AUTHORITY EVENT AKTIF.** Recompute dan perubahan output di bawah memang
> terjadi, tetapi kesimpulan bahwa ketiga row legacy sudah otoritatif serta pemilihan `2026-07-15`
> sebagai ex-date telah dicabut oleh Tahap 6. Source ref legacy adalah domain pihak ketiga dan anchor
> tersebut berasal dari deret Yahoo; current authority adalah tiga revision KSEI append-only pada
> bagian Tahap 6. Blok ini tidak boleh mengaktifkan factor atau mengalahkan ex-date resmi.

Target remediasi `F-027` berbunyi: *"peroleh faktor korporasi dari sumber otoritatif sehingga minimal satu run produksi benar-benar menerapkan faktor, lalu buktikan skala sebelum/sesudah pada baris nyata."* Terpenuhi untuk tiga peristiwa, dan **hanya tiga**.

**Mutasi produksi yang dicatat di sini.** Pemilik menyediakan rasio dari halaman *Stock Splits and Reverse Stocks* IDX, diverifikasi silang terhadap perubahan nilai nominal pada catatan pengumuman: **35 dari 35 rasio konsisten**, nol tidak cocok. Tiga di antaranya lolos verifikasi kedua terhadap seri harga dan diimpor:

| Ticker | ex_date | Rasio | `price_factor` | `volume_factor` |
|---|---|---|---:|---:|
| MLPT | 2026-07-15 | 1:25 | 0,04 | 25 |
| RAJA | 2026-07-15 | 1:5 | 0,2 | 5 |
| RMKE | 2026-07-15 | 1:5 | 0,2 | 5 |

`adjustment_source = EXCHANGE_ANNOUNCEMENT`. Faktor otoritatif pertama dalam sejarah korpus; sebelumnya nol. Diikuti recompute 2026-07-15 s.d. 2026-07-28, **10 dari 10 tanggal, 0 gagal**, seluruhnya `seal_provenance_scope = ANALYTICAL_ONLY`. 844 publikasi current tetap 844.

**Ex-date yang diperbaiki sebelum impor, dan bagaimana ketahuannya.** Ex-date awal dari agregator pihak ketiga meleset untuk ketiganya — MLPT 21 Juli, RAJA 16 Juli, RMKE 17 Juli — sedangkan seri harga menunjukkan ketiganya berubah skala pada **2026-07-15** (18.725→826, 4.590→920, 2.260→496). `Listing Date` di halaman IDX (16, 17, 29 Juli) juga bukan ex-date melainkan tanggal pencatatan saham tambahan. Mengimpor tanggal yang salah akan menerapkan faktor pada bar yang sudah pasca-split.

**Bukti sebelum/sesudah pada baris nyata**, MLPT 2026-07-15:

| | Sebelum | Sesudah |
|---|---:|---:|
| `ma20` | 17.197,55 | 727,55 |
| `roc20` | **−95,7%** | **+7,0%** |
| `atr14_pct` | 285,5% | 6,5% |

`roc20` −95,7% bukan penurunan harga melainkan pemecahan nominal — sinyal jual palsu yang sepenuhnya artefak. Tanggal 21 dan 28 Juli yang sebelumnya kosong seluruhnya kini terisi.

**Cacat yang ditemukan saat verifikasi dan ikut diperbaiki.** Setelah impor, faktornya resolve benar tetapi jendelanya tetap terkuarantina: baris hipotesis detektor lama masih ada untuk peristiwa yang sama, tidak adjustable, dan terus memicu kontaminasi — jendela akan diskalakan lalu di-null-kan. Hipotesis atas sebuah peristiwa terjawab begitu terms peristiwa itu diketahui, sehingga baris yang tidak dapat menyesuaikan kini dikesampingkan bila ada baris lain untuk instrumen, jenis, dan tanggal efektif yang sama yang dapat.

**Invarian baru yang membaca efeknya dari korpus, bukan dari kode.** `ProductionCorpusInvariantOracleTest::test_an_authoritative_factor_reaches_published_output`: bila faktor tercatat tetapi tidak pernah diterapkan, indikator pada ex-date akan tetap membawa terjun split mentah dan `roc20` duduk di sekitar `factor − 1` — −0,96 untuk 1:25, −0,80 untuk 1:5. Jarak terukur 0,91 s.d. 1,03. Disertai asersi non-kehampaan, karena invarian ini akan lulus atas korpus kosong — persis bagaimana ia lulus selama tiga tahun sementara tak satu faktor pun pernah diterapkan.

**Yang tetap terbuka.** Tiga dari 177 aksi yang membutuhkan faktor. Tiga puluh dua split sisanya terkunci di belakang `F-039`: korpus bar sudah disesuaikan provider untuk split lama, sehingga menerapkan faktor akan menyesuaikan ganda. `F-027` karena itu **menyempit, bukan tertutup**.

## Jalur masuk faktor korporasi otoritatif — 2026-08-11

Dikerjakan atas instruksi langsung pemilik, bukan lewat command protocol. Menyiapkan prasyarat `F-027`; **tidak menutupnya**, karena `F-027` menuntut faktor yang benar-benar terpakai pada data produksi dan itu masih menunggu data dari luar.

**Temuan yang memicunya.** `ImportCorporateActionsCommand` hanya memetakan `ticker_code`, `action_date`, `action_type`, `source_ref`, `notes`. `upsertCorporateAction` juga tidak menulis kolom kuantitatif. Satu-satunya penulis `price_adjustment_factor` adalah `DeriveCorporateActionsCommand`, yang menandainya `DERIVED_FROM_PRICE_SERIES` — dan `isAdjustable()` menolak persis nilai itu. Platform ini gelung tertutup: **satu-satunya faktor yang dapat ia hasilkan adalah yang ia tolak pakai**, dan faktor otoritatif tidak punya jalan masuk sama sekali.

**Kosakata.** `adjustment_source` kini daftar tertutup di `EventRiskSourceRepository::ADJUSTMENT_SOURCES` dan didokumentasikan pada `Price_Adjustment_Contract_LOCKED.md`: `EXCHANGE_ANNOUNCEMENT`, `DEPOSITORY_SCHEDULE`, `OPERATOR_ENTERED` boleh menyesuaikan; `DERIVED_FROM_PRICE_SERIES` terdaftar sebagai anggota yang dikenal justru supaya dapat ditolak secara eksplisit, bukan supaya dipakai.

**Allowlist dibalik menjadi positif.** `isAdjustable()` dan query `resolveAdjustmentFactorsForTickerIds` dulu hanya mengecualikan satu nilai buruk yang dikenal, sehingga faktor ber-`adjustment_source` NULL — atau bernilai apa pun yang tak seorang pun deklarasikan — tetap menskalakan harga terbit sementara platform tidak bisa menyebut asalnya. Keduanya kini memakai daftar positif yang sama. **Nol baris produksi berubah adjustability**: 0 sebelum, 0 sesudah, karena seluruh 15 baris berfaktor memang derived.

**Importer.** Menerima `ex_date`, `cum_date`, `ratio_from`, `ratio_to`, `price_adjustment_factor`, `volume_adjustment_factor`, `dividend_per_share`, `adjustment_source`, `adjustment_note`. Empat penolakan tegas, masing-masing dengan pesan sendiri dan memblokir seluruh impor alih-alih menerapkan sebagian: sumber derived, sumber tak dideklarasikan, faktor tanpa sumber, dan faktor tanpa `ex_date` — yang terakhir karena `ex_date` adalah yang menempatkan faktor pada garis waktu, dan tanpanya baris baru diam-diam mewarisi fallback `action_date` yang disediakan hanya untuk baris lama.

Dry-run atas CSV uji berisi satu baris sah dan tiga yang harus ditolak menghasilkan `valid_row_count=1`, `error_count=3`, `status=BLOCKED`. Test permanen menguncinya termasuk round-trip: faktor terimpor benar-benar resolve untuk penyesuaian.

**Dua fixture test lama diperbarui**, dan itu perlu dinyatakan supaya tidak terbaca sebagai melonggarkan kontrak agar kode lulus. `CorporateActionCandidateBoundaryTest::action()` menyetel `adjustment_source => null` sementara test yang memakainya bernama `test_a_source_backed_factor_still_adjusts`; fixture itu selalu bermaksud faktor bersumber tetapi tidak dapat menyatakannya sebelum kosakatanya ada. Hal yang sama pada `PriceAdjustmentTest`. Ditambahkan pula `test_an_unattributed_factor_does_not_adjust` yang menguji dua kasus yang dulu lolos: sumber NULL dan sumber tak dikenal.

## HISTORICAL, SUPERSEDED — W12 remediation `F-026` — 2026-08-11

Temuan ini tidak dapat ditutup dengan backfill, dan itu bagian dari jawabannya. Menulis `RAW` ke 756.329 baris legacy akan menyatakan skala yang tidak pernah dicatat baris itu sendiri — fabrikasi yang sama yang audit ini tolak untuk tanggal efektif sektor. Yang bisa dikerjakan adalah menjaga celahnya tidak melebar dan tidak menjadi senyap.

**Yang diperiksa dan ternyata sudah benar**, dicatat supaya audit berikutnya tidak mengulanginya: jalur restore `EodArtifactRepository:662` membangun ulang `eod_bars` dari `eod_bars_history` dan **membawa** `price_product_code` lewat `barLineage()`, yang memuatnya di daftar field; `eod_bars_history` juga menyimpannya. Dugaan awal bahwa restore menjatuhkan identitas itu keliru.

**Keadaan snapshot saat itu.** `MarketDataPriceReadRepository:53` memancarkan `PRICE_PRODUCT_UNRECORDED` dan kode itu **tidak terdaftar** di registry maupun seed. `EmittedReasonCodeRegistrationTest` tidak menangkapnya karena keterbatasan yang didokumentasikan sendiri pada `:65` — argumen string posisional dan nilai array tidak dipindai. Snapshot 2026-08-11 memperlakukannya sebagai keadaan non-blocking dan tetap menyajikan baris; keputusan itu **dicabut oleh Tahap 5** karena bertentangan dengan kontrak canonical bar yang lebih spesifik. State aktif sekarang menahan seluruh publikasi tersebut dengan severity `HARD`.

`BarPriceProductIdentityTest` mengunci empat properti: jalur ingest menulis identitas dari scope `raw_product_code` dan bukan literal; `barLineage` membawanya melewati restore; sisi baca melaporkan ketiadaannya alih-alih mendefaultkan ke `RAW`; dan kodenya terdaftar. Properti ketiga yang paling menahan — default ke `RAW` adalah perbaikan termurah yang tersedia dan justru yang salah, karena membuat 756.329 baris legacy mengklaim produk analitis yang tak seorang pun pilih untuk mereka, dan klaim itu tidak dapat dibedakan dari yang benar-benar tercatat.

Batasan yang dinyatakan terbuka: korpus legacy tetap tanpa identitas produk, sehingga separuh `RAW` dari required outcome stage 11 tetap tidak terverifikasi untuk baris historis. Itu tercatat sebagai batasan, bukan sebagai sesuatu yang telah ditutup.

## W12 remediation `F-024` — 2026-08-11

`MD-REMEDIATE W12 findings F-024`. Empat tuntutan remediasi pada finding tersebut, dengan hasil terpisah:

| Tuntutan | Hasil | Bukti |
|---|---|---|
| selected product dibind run-wide | **terpenuhi** | `bindCandidateAnalyticalProduct()` menulis identitas ke publication dan run **sebelum** iterasi baris, sehingga run nol-baris pun tercatat. Seluruh **844 run** yang dibuat 2026-08-10/11 membawa `price_product_code`: nol tanpa binding. Angka historis 845/72.764 murni sisa run yang mendahului mekanisme ini |
| `factor=1` tetap `STRUCTURAL_ADJUSTED` | **terpenuhi** | `AnalyticalProductIdentityService::selectedProductCode()` melempar `ANALYTICAL_PRICE_PRODUCT_INVALID` kecuali `STRUCTURAL_ADJUSTED`; `applyPriceAdjustment()` mengembalikan produk terpilih tanpa memandang faktor. Satu-satunya kode berbeda di seluruh `eod_runs` adalah `STRUCTURAL_ADJUSTED` |
| legacy config `price_basis_default=close` | **terpenuhi (dicabut)** | Kunci itu ditulis ke vector config dan **tidak dibaca oleh apa pun**. Entri registry-nya mengizinkannya hanya "while compatibility code exists" — syarat yang sudah gugur. Dicabut dari `config/market_data.php`, `.env.example`, `.env.testing`, dan `vectorConfig()`; entri registry ditandai `PRUNED`; guard baru `ConfigEnvGovernanceCleanupStaticGuardTest::test_legacy_price_basis_selector_is_pruned_not_left_as_active_config` |
| fresh recompute + **replay proof** | **TIDAK terpenuhi** | Recompute ada (843/843, 0 gagal). Replay proof **tidak dapat diselesaikan** — lihat di bawah |

**Kenapa replay proof tidak dapat diselesaikan.** `market-data:evidence-replay:full-range-current 2026-07-21 2026-07-28` dijalankan terhadap run yang sudah terikat. Hasilnya `comparison_result=NOT_ADMISSIBLE`, `replay_status=BLOCKED`, dengan catatan `REPLAY_FIXTURE_SELF_GENERATED: expectation was derived from the run under verification; a match proves only that the run equals itself.` Itu **guard W18 bekerja benar**, bukan kegagalan: satu-satunya mekanisme fixture yang tersedia menurunkan ekspektasinya dari run yang sedang diverifikasi, dan W18 sudah memutuskan bahwa kecocokan semacam itu tidak membuktikan apa pun. Perlu dicatat bahwa `price_product_code`, `price_product_version`, dan `factor_set_hash` **identik** antara `expected_lineage` dan `actual_lineage` pada perbandingan yang diblokir itu — indikasi mendukung, tetapi tidak sah dihitung sebagai bukti justru karena aturan yang sama.

Menutup butir ini menuntut fixture yang ekspektasinya disusun terpisah dari run yang diuji. Fixture semacam itu belum ada; `storage/app/market_data/replay-fixtures` tidak memuat `manifest.json` untuk `valid_case`, dan `market-data:replay:smoke 72909` karena itu menghasilkan `all_passed=0` dengan dua kasus "lulus" yang lulus semu — keduanya mengharapkan ERROR dan memperolehnya hanya karena fixture-nya hilang.

**Temuan baru `F-025` (W18, P1).** `ReplayVerificationService.php:55` menulis `comparison_result = 'NOT_ADMISSIBLE'`, sedangkan kolomnya `enum('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED')` dari migrasi `2026_05_19_000002` dan tidak pernah diperluas. Akibatnya setiap replay yang tidak admissible **gagal saat disimpan** (`Warning: 1265 Data truncated for column 'comparison_result'`) alih-alih tercatat sebagai tidak admissible. Bentuknya sama dengan cacat berulang sesi ini: aturannya ditulis dengan benar pada W18, lalu tidak pernah dapat dijalankan. Tidak diperbaiki di sini karena berada di luar scope `F-024` dan milik W18.


<!-- LEGACY_EXTRACT_BODY_END -->
