# Market-Data Implementation Ledger

## 2026-08-08 strict strategy-sync correction

The earlier W21 evidence is retained, but its P0-04 conclusion was too strong. Removing provider `adj_close` fallback and reporting per-vector RAW/STRUCTURAL_ADJUSTED output does **not** prove the owner strategy requiring one selected `STRUCTURAL_ADJUSTED` analytical product per indicator run. Therefore W12/P0-04 is documentation-complete but implementation-partial until run-wide product/factor/config binding and fresh recompute/replay proof exist. Historical sections below remain evidence of what was tested at that time, not current strategy authority.

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

- documentation strategy: `DOCUMENTATION_STRATEGY_READY`; documentation synchronization: **`PASS` (`22/22`, strict revalidation 2026-08-08)**
- implementation conformance: **`NOT_GRANTED`** — `P0-04` reopened on 2026-08-08 plus the still-open P1 backlog; historical W22 2026-08-06 counts are retained only as dated evidence
- operational validation: **`NOT_GRANTED`** — nol sesi teraktivasi (`W22`, 2026-08-06); ini adalah keadaan pembangunan/pre-activation yang sah, bukan blocker burn-down implementasi
- final claim level: **`IMPLEMENTATION_READY`**, bukan `runtime-proven`
- open findings recorded by command protocol: **15 terbuka** (`F-007`, `F-010`, `F-011`, `F-017`, `F-018`, `F-019`, `F-020`, `F-021`, `F-023`, `F-024`, `F-026`, `F-027`, `F-030`, `F-038`, `F-039`). `F-021` tetap terbuka tetapi berstatus **`PRE_ACTIVATION_DEFERRED`** dan tidak menjadi blocker pembangunan. `F-030` tetap terbuka: lubang aturannya ditutup, fixture independennya belum ada. `F-024` menyempit menjadi butir replay proof saja; `F-026` dan `F-027` dibuka oleh `MD-REAUDIT W12` karena audit ulang memeriksa seluruh scope stage 11.
- recently closed findings relevant to sequencing: `F-045` ditutup pada Tahap 2 tanggal 2026-08-12; subtemuan guard `F-007a`, `F-026a`, `F-017a`, dan `F-018a` ditutup pada Tahap 3 tanggal 2026-08-13. Finding induk tetap terbuka karena subtemuan backfill `b` belum dikerjakan. Detail temuan awal hanya berada pada blok bertanda `HISTORICAL, SUPERSEDED`. Finding tertutup lain tetap ditelusuri melalui work-order evidence masing-masing.
- known implementation backlog carried by the audit report: **P0-04 reopened on 2026-08-08 strict documentation re-audit** because W21 removed provider `adj_close` fallback but did not prove the selected run-wide `STRUCTURAL_ADJUSTED` product. `P0-01`, `P0-02`, and `P0-03` remain closed by their recorded proof; P1 states remain governed by the canonical audit report. Baris ini sebelumnya membaca 40 dari 44 dan basi sejak penutupan P0 pada W21; dikoreksi pada `MD-STATUS` 2026-08-06
- known data-authority state: **sector IDX-IC authority work diterapkan 2026-08-10** atas instruksi terpisah — 721 baris `EXCHANGE_AUTHORITATIVE` untuk 697 listing, 971 baris legacy diturunkan ke `DERIVED_REFERENCE`, 12 interval temporal ditutup pertama kalinya. Menurunkan `P1-27` ke `PARTIAL` dan membuka `P1-41`/`P1-42`/`P1-43`. **Tidak mengubah status W05/W14/W16**; lihat bagian bertanggal di bawah
- operasi produksi selesai: **recompute atas 843 tanggal (2023-01-02 … 2026-07-27) selesai 2026-08-11 01:56 dengan `success_count=843`, `failed_count=0`, `skipped_count=0`**, log `outputs/idxic-apply-20260810/recompute_full_range.log`. Karena dijalankan dengan `--continue-on-error`, angka itu dihitung dari 843 baris `terminal_status=SUCCESS` pada log per-tanggal dan bukan dari exit code. Menutup `P1-32`, `P1-33`, `P1-34` dengan bukti terukur, dan memberi `P1-27` angka sisa yang tepat — lihat bagian bertanggal di bawah
- execution mode: `STAGE_BY_STAGE`
- active work order: `NONE`
- **peringatan operasional DICABUT 2026-08-11**: gerbang integritas yang terbangun sempat menghentikan seal seluruh promote run; `F-033` menutupnya dengan menyepadankan tuntutan gerbang pada apa yang run itu benar-benar lakukan, dan pipeline seal berjalan kembali (publikasi 73586 tersegel `ANALYTICAL_ONLY`). Penolakan bersifat fail-closed dan tidak merusak apa pun — 844 publikasi current tetap 844 — tetapi pipeline recompute berhenti sampai keputusan diambil. Jangan melonggarkan gerbangnya sebagai jalan pintas
- next permitted implementation action: **Tahap 4 — keputusan pemilik tentang makna `RAW` dan perlakuan korpus provider-back-adjusted (`F-039a`), tanpa kode atau mutasi data.** Tahap 3 ditutup 2026-08-13 setelah guard per-field dan seluruh jalur lifecycle lulus tanpa menyentuh korpus. Aktivasi `F-021` dipindahkan ke gate operasional pascapembangunan; urutan Tahap 4–11 yang berlaku berada pada bagian `CURRENT AUTHORITATIVE SEQUENCE` di akhir ledger. Riwayat audit tetap berada pada blok bertanda `HISTORICAL` dan tidak menentukan current state.

## Documentation / implementation revalidation note — 2026-08-08

Revalidasi **dokumen-only** melakukan dua perubahan yang tidak boleh diterapkan retroaktif sebagai runtime proof. Pertama, temporal sector membership menjadi prerequisite Stage 6 / `W05`, sehingga tersedia sebelum sector-relative indicators pada `W14`; Stage 13 / `W16` hanya mengonsumsi/expose state tersebut. Kedua, strict pass 2026-08-08 menemukan bahwa W21 hanya menutup provider `adj_close` fallback, belum membuktikan selected run-wide `STRUCTURAL_ADJUSTED` product; `P0-04`/`F-024` karena itu dibuka kembali pada W12.

Status `CONFORMANT/PASS` W05 dan W13–W22 pada baris historis di bawah merekam eksekusi yang memang pernah dilakukan terhadap baseline saat itu, **bukan current dependency conformance setelah strategi dikoreksi**. Jika implementasi dilanjutkan, W12 harus diremediasi dan downstream proof yang bergantung pada price product harus direvalidasi. Final documentation claim tetap `IMPLEMENTATION_READY`; implementation conformance tetap `NOT_GRANTED`.

## W18 remediation `F-028`, `F-029`, `F-030` — 2026-08-11

`MD-REMEDIATE W18 findings F-028,F-029,F-030` setelah `MD-REAUDIT W18` memberi `FAIL`.

**`F-028` — akar event/factor kini punya koordinat as-known.** Migrasi `2026_08_11_000002` menambah `recorded_at` pada `market_data_corporate_actions` **dan** `market_data_trading_status_events`. Tabel kedua ikut karena `EventRiskSourceRepository` membacanya langsung tanpa cutoff, sementara `TemporalTradingStatusRepository` membaca `md_trading_status_revisions` dengan cutoff — memperbaiki separuh akan meninggalkan gate tetap dilanggar.

Backfill dari `created_at` bukan karangan: `created_at` adalah saat baris masuk ke platform, yang persis makna `recorded_at`. Buktinya **0 baris berbeda dari `created_at`** setelah backfill. Yang sengaja **tidak** diklaim: bahwa stempel itu adalah saat bursa mengumumkan aksinya. Itu saat platform mengetahuinya — koordinat yang tepat untuk replay as-known, tetapi bukan bukti waktu pengumuman.

Empat metode resolusi kini menerima `$knownAt`, difilter lewat satu helper. Baris ber-`recorded_at` NULL **dikecualikan** saat cutoff diberikan, bukan dianggap cukup tua — baris tak bertanggal tidak dapat ditempatkan pada garis waktu pengetahuan, dan menganggapnya lama akan membocorkannya ke setiap replay. `upsertCorporateAction` dan `upsertTradingStatusEvent` kini menulis koordinat itu agar baris baru tidak lahir tak terlihat.

Efeknya pada korpus produksi:

| Skenario | Aksi terlihat |
|---|---:|
| tanpa cutoff (perilaku lama) | 530 |
| cutoff 2024-01-01 | **0** |
| cutoff 2026-06-30 | 428 |
| cutoff 2026-08-11 | 530 |

**`F-029` — guard as-known mendaftar seluruh akar, dan akar baru gagal secara default.** `temporalRoots()` kini memuat **13 metode** di 6 repository, termasuk `SectorClassificationRepository` yang menjadi temporal pada 2026-08-10 dan tidak pernah didaftarkan. Test kedua menyapu seluruh `app/Infrastructure/Persistence/MarketData/` dengan refleksi: setiap metode publik ber-parameter `knownAt` yang tidak terdaftar membuat test gagal. Penambahan akar baru tanpa mendaftarkannya kini pecah, bukan lolos diam.

Dua test perilaku ditambahkan — aksi korporasi dan faktor penyesuaian yang dicatat setelah cutoff harus tak terlihat. Masing-masing membawa asersi pembanding tanpa cutoff, sehingga filter yang menyembunyikan segalanya tidak dapat menyamar sebagai lulus.

**`F-030` — admissibility memeriksa fakta, bukan label.** Aturan lama menolak fixture ber-`fixture_family` persis `runtime_generated_valid_case`, yang dapat dilewati hanya dengan menamainya lain. Kini `fixture_source` juga diperiksa: bila ia menyebut run yang sedang diverifikasi, fixture ditolak apa pun labelnya. Guard-nya memuat kasus ketiga yang sama pentingnya — fixture dari run **berbeda** tetap admissible, sehingga aturan ini tidak dapat dipenuhi dengan menolak segalanya.

Yang **tidak** ditutup oleh remediasi ini: `F-030` menghapus lubang pada aturannya, tetapi **fixture ber-ekspektasi independen tetap belum ada**. Gate exact-replay masih belum dapat disertifikasi, dan `F-024` masih menunggu hal yang sama.

**Koreksi dari `MD-REAUDIT W18` kedua, hari yang sama.** Remediasi di atas **tidak menutup future leakage pada jalur produksi**. Kolom, cutoff, dan guard-nya memang ada dan terbukti pada tingkat repository; yang tidak dikerjakan adalah menyambungkannya. `EodIndicatorsComputeService.php:87` sudah memegang `$knownAt` dan meneruskannya ke akar sektor pada baris 89, sementara ketiga pemanggil akar event/factor pada baris 91, 216, dan 260 tetap memanggil tanpa cutoff — dengan variabelnya berada dua baris di atas. Dicatat sebagai `F-031`; penutupan `F-028` karena itu berlaku untuk kapabilitasnya, bukan untuk hilangnya kebocoran. Audit kedua juga menemukan `md_config_snapshots` kosong dengan seluruh run dan publikasi ber-`config_snapshot_id` NULL (`F-032`), yang membuat klausa config pada exit gate tidak terpenuhi sekaligus memblokir `F-030` di belakangnya.

## `MD-REAUDIT W18` keenam — 2026-08-11, verdict `BLOCKED`

Pass ini memeriksa bagian required outcome yang belum pernah diaudit: **anti-survivorship**. Hasilnya positif dan perlu dicatat sebagai bukti, bukan hanya sebagai ketiadaan temuan.

`TemporalIdentityRepository::baseIdentityQuery` memasukkan listing selama `listed_date <= tanggal` dan `delisted_date IS NULL OR > tanggal`, sehingga instrumen yang delisting belakangan tetap ada pada tanggal lampau. Mekanismenya **terpakai pada data nyata**: 33 listing pernah delisting, 15 di antaranya di dalam rentang dataset.

Pemeriksaan lanjutan menemukan instrumen-instrumen itu kehilangan bar jauh sebelum tanggal delisting — sembilan di antaranya berhenti pada tanggal yang sama, 2024-05-14 — dan suspensi **tidak** menjelaskannya: FREN baru tercatat disuspensi April 2025, HDTX Juli 2025, MFIN September 2024, sedangkan KRAH/KPAS/MYRX/JKSW/TURI/RMBA tidak punya catatan status sama sekali. Sebelum menyebutnya survivorship tersembunyi, alternatifnya diuji: ternyata coverage **menghitung** mereka sebagai expected-and-missing dan evidence **menyebut namanya** — `["HDTX","MYRX","JKSW","MFIN","FREN","KRAH","KP…]` pada 2024-08-01 dan 2025-01-02 — dan **nol dari 844 run current** melaporkan `coverage_missing_count > 0` dengan sampel kosong. Kekurangan datanya nyata, tetapi terlihat dan berteratribusi, bukan senyap.

Sisa yang tidak dibuka sebagai temuan W18: alasan hilangnya bar tersebut tidak tercatat sebagai suspensi. Itu kondisi data pada lingkup coverage/eligibility, bukan replay, dan membuka finding W18 untuknya adalah perluasan scope. Dicatat di sini sebagai dampak lintas-kontrak.

**Kenapa `BLOCKED`, bukan `PARTIAL` lagi.** Sepuluh temuan W18 diremediasi dan diaudit ulang hari ini (`F-025`, `F-028`, `F-029`, `F-031`, `F-032`, `F-034`, `F-035`, `F-036`, `F-037`, ditambah `F-033` yang lahir dari salah satunya). Tidak ada lagi alternatif in-scope yang aman dan belum dicoba. Dua yang tersisa menuntut otoritas atau data pemilik:

- **`F-033`** — seal terblokir sampai diputuskan antara memproduksi observation manifest (akuisisi ulang sumber, lihat `P1-29`) atau membatasi klaim seal secara tertulis. Melonggarkan gerbangnya bukan alternatif yang sah: itu mengembalikannya ke keadaan tidur yang membuat 64.939 publikasi lolos tanpa diperiksa.
- **`F-030`** — menuntut fixture yang ekspektasinya disusun terpisah dari run yang diuji. Seluruh 20.635 perbandingan yang ada self-generated, dan menyusun fixture dari run yang sama akan melanggar aturan yang baru saja ditegakkan.

Unblock requirement karena itu konkret: satu keputusan pemilik untuk `F-033`, dan satu fixture ber-ekspektasi independen untuk `F-030`. Iterasi audit berikutnya tidak akan menggerakkan keduanya.

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

## W12 remediation `F-027` — 2026-08-11

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

## W12 remediation `F-026` — 2026-08-11

Temuan ini tidak dapat ditutup dengan backfill, dan itu bagian dari jawabannya. Menulis `RAW` ke 756.329 baris legacy akan menyatakan skala yang tidak pernah dicatat baris itu sendiri — fabrikasi yang sama yang audit ini tolak untuk tanggal efektif sektor. Yang bisa dikerjakan adalah menjaga celahnya tidak melebar dan tidak menjadi senyap.

**Yang diperiksa dan ternyata sudah benar**, dicatat supaya audit berikutnya tidak mengulanginya: jalur restore `EodArtifactRepository:662` membangun ulang `eod_bars` dari `eod_bars_history` dan **membawa** `price_product_code` lewat `barLineage()`, yang memuatnya di daftar field; `eod_bars_history` juga menyimpannya. Dugaan awal bahwa restore menjatuhkan identitas itu keliru.

**Yang benar-benar cacat.** `MarketDataPriceReadRepository:53` memancarkan `PRICE_PRODUCT_UNRECORDED` dan kode itu **tidak terdaftar** di registry maupun seed. `EmittedReasonCodeRegistrationTest` tidak menangkapnya karena keterbatasan yang didokumentasikan sendiri pada `:65` — argumen string posisional dan nilai array tidak dipindai. Kode itu kini terdaftar pada kategori `READ_SIDE` dengan severity `WARN`, bersebelahan dengan `NO_READABLE_PUBLICATION` yang dipancarkan di metode yang sama.

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

## Sector IDX-IC authority work — 2026-08-10

Dikerjakan atas instruksi terpisah dan eksplisit dari pemilik (`apply 697 yang sudah siap`, `perbaiki tanggal 190 baris itu ke listing date`, `hitung ulang sector_roc20 dan rs_20_vs_sector untuk periode berdampak`). Mutasi production data hanya sah dengan perintah terpisah semacam ini; catatan lengkap beserta seluruh checksum artefak ada di `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`.

**Dasar bukti.** Sembilan artefak resmi IDX diverifikasi SHA-256. Pengumuman peluncuran `Peng-00007/BEI.POP/01-2021` menetapkan IDX-IC berlaku 2021-01-25. Rekonsiliasi dua arah antara PDF (822 ticker) dan CSV (765) menghasilkan **nol entri fabrikasi** dengan satu selisih material (`DGNS`). Empat tanggal berlaku terdokumentasi — 2021-01-25, 2021-07-01, 2021-11-25, 2022-07-01 — dan **keempatnya mendahului awal dataset 2023-01-02**.

**Keadaan tabel setelah apply** (`ticker_sector_memberships`, 1.692 baris):

| Ukuran | Nilai |
|---|---:|
| baris `EXCHANGE_AUTHORITATIVE` bersumber `idx_announcement` | 721 |
| listing berbeda yang dicakupnya | 697 |
| baris legacy diturunkan ke `DERIVED_REFERENCE` (12 `source_name`) | 971 |
| interval tertutup (`effective_to` + `supersedes_membership_id`) | 12 |
| `effective_from` dikoreksi ke listing date | 190 |
| `listing_id` NULL / `recorded_at` NULL | 0 / 0 |

Dua belas interval tertutup itu adalah **nilai bukan-nol pertama yang pernah dipegang `effective_to` dan `supersedes_membership_id`** sejak kolomnya ada — struktur temporal yang selama ini lengkap tetapi tidak pernah dipakai, persis bentuk cacat berulang sesi ini: aturan ditulis dengan benar, lalu tidak pernah dijalankan.

**Yang tidak tertutup.** 280 dari 977 listing masih tanpa membership otoritatif (91 tercatat sebelum 2022-07-01, termasuk 17 yang sengaja ditahan karena tidak ada dokumen bertanggal; 189 IPO setelahnya), 6 di antaranya tanpa membership sama sekali. Empat siklus evaluasi Juli 2023–2026 belum ditemukan. Satu baris (`membership_id 922`) masih mulai satu hari sebelum listing-nya dan sengaja tidak dimutasi karena berada di luar cakupan instruksi yang diberikan. Ketiganya tercatat sebagai `P1-41`, `P1-42`, `P1-43` pada `reports/AUDIT_FINAL_STATE.md`; `P1-27` turun `OPEN` → `PARTIAL`.

**Dampak pada work order.** Pekerjaan ini menyentuh W05 (temporal sector membership sebagai prerequisite Stage 6), W14 (sector-relative indicators), dan W16 (konsumsi/ekspos state). **Tidak ada satu pun dari ketiganya yang berubah status karenanya.** Alasannya seperti yang diakui pada sesi ini: exit gate work order tersebut berbicara tentang baris aktual, bukan tentang mekanisme.

**Pembaruan 2026-08-11.** Recompute selesai (843/843, 0 gagal) dan prasyarat bukti yang disebut di atas kini tersedia: jumlah tanggal gagal, cakupan kolom, dan probe pembanding independen semuanya terukur, serta `P1-32`/`P1-33`/`P1-34` ditutup karenanya. Status W05/W14/W16 **tetap tidak diubah di sini** — memutakhirkan status work order adalah keputusan controller yang terpisah dan tunduk pada urutan yang berlaku, sedangkan catatan ini hanya menyatakan bahwa alasan penahannya sudah tidak berlaku. Perlu dicatat pula bahwa `next permitted implementation action` masih menunjuk W12/`F-024`, sehingga peninjauan downstream tidak boleh mendahuluinya.

Reason code baru yang dipakai: `SECTOR_MEMBERSHIP_LEGACY_RECLASSED_DERIVED` (781 baris), `SECTOR_MEMBERSHIP_EFFECTIVE_FROM_CORRECTED_TO_LISTING` (190 baris). Keduanya terdaftar di `registry/Reason_Codes_Registry.md` dan `registry/Reason_Codes_Seed.sql`.

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

**Historical W21 entry-gate snapshot (superseded for current P0 counts; retained as execution history).**

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

## Active findings

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
| `F-027` | `W12` | P0 | PARTIAL — tiga faktor otoritatif diterapkan 2026-08-11 (MLPT 1:25, RAJA/RMKE 1:5) dan terbukti mencapai output terbit (`roc20` MLPT −95,7% → +7,0%); **174 dari 177 aksi masih tanpa faktor**, dan 32 split terkunci di belakang `F-039` | `Price_Adjustment_Contract_LOCKED.md` | **Nol faktor penyesuaian yang sah pernah dipakai di produksi.** Dari 530 aksi korporasi, 515 tanpa faktor dan 15 membawa faktor — tetapi ke-15 itu persis yang ber-`adjustment_source = 'DERIVED_FROM_PRICE_SERIES'` dan ditolak `EventRiskSourceRepository::isAdjustable()`. Query pembuktinya mengembalikan `faktor_sah_dan_bukan_derived = 0`. Akibatnya `STRUCTURAL_ADJUSTED` identik nilainya dengan `RAW` pada seluruh 756.328 baris, dan exit gate "satu vector tidak boleh mencampur skala RAW/adjusted" lulus secara hampa: tidak ada yang pernah disesuaikan. Jalur penyesuaian terbukti hanya oleh unit test, tidak pernah oleh data produksi — Gate 12: diamnya mekanisme berbatas bukan bukti | peroleh faktor korporasi dari sumber otoritatif sehingga minimal satu run produksi benar-benar menerapkan faktor, lalu buktikan skala sebelum/sesudah pada baris nyata |
| `F-026` | `W12` | P1 | PARTIAL — mekanisme ditutup 2026-08-11 (`PRICE_PRODUCT_UNRECORDED` didaftarkan; `BarPriceProductIdentityTest` mengunci penulisan, lineage, dan larangan default ke `RAW`); **korpus legacy tetap tanpa identitas produk dan tidak akan di-backfill**, sehingga separuh `RAW` stage 11 tetap tak terverifikasi untuk baris historis | `Price_Adjustment_Contract_LOCKED.md` (immutable `RAW`) | **756.329 dari 756.329 baris `eod_bars` ber-`price_product_code` NULL** — nol bar menyatakan produknya, sehingga separuh `RAW` dari required outcome stage 11 tidak dapat diverifikasi setelah run berakhir. `EodBarsIngestService.php:208` menulis kode produk untuk ingestion baru; korpus legacy mendahuluinya. `MarketDataPriceReadRepository` menanganinya jujur dengan `PRICE_PRODUCT_UNRECORDED` alih-alih mengarang `RAW`, sehingga ini gap yang tersurat, bukan senyap. Sejalan dengan `P1-21` | catat identitas produk pada bar tanpa mengarang skala untuk baris legacy; backfill `RAW` retroaktif justru klaim yang barisnya sendiri tidak pernah buat |
| `F-025` | `W18` | P1 | CLOSED (diremediasi 2026-08-11; enum diperluas, verdict tersimpan dan terbaca, `error_count` 1→0) | `Replay_Determinism_Contract` | `ReplayVerificationService.php:55` menulis `comparison_result = 'NOT_ADMISSIBLE'`, tetapi kolomnya `enum('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED')` dari migrasi `2026_05_19_000002` dan tidak pernah diperluas; setiap replay tidak-admissible gagal disimpan dengan `Warning: 1265 Data truncated` alih-alih tercatat. Ditemukan saat menjalankan replay proof `F-024` | perluas enum agar memuat `NOT_ADMISSIBLE`, lalu buktikan verdict tidak-admissible benar-benar tersimpan dan terbaca |
| `F-024` | `W12` | P0 | OPEN — 3 dari 4 tuntutan ditutup pada remediasi 2026-08-11; tersisa **replay proof**, terblokir oleh `F-025` dan oleh ketiadaan fixture yang tidak self-generated (`P0-04`, reopened 2026-08-08) | `Price_Adjustment_Contract` + `Indicator_Registry_Baseline` | provider `adj_close` fallback sudah hilang, tetapi selected `STRUCTURAL_ADJUSTED` belum dibind run-wide; vector tanpa applied factor masih dapat dilabeli `RAW`; legacy config masih memakai `price_basis_default=close` | implement selected product/factor/config binding run-wide; factor=1 tetap `STRUCTURAL_ADJUSTED`; fresh recompute + replay proof. **Sisa per 2026-08-11: hanya replay proof.** Binding run-wide terbukti (844/844 run terikat), `factor=1` terbukti, `price_basis_default` dicabut; replay proof menuntut fixture ber-ekspektasi independen yang belum ada |
| `F-023` | `W21` | P0 | OPEN (dipersempit dua kali) | `Test_Coverage_Closure_Contract` | Historical W21 snapshot had 4 P0 closed; strict 2026-08-08 re-audit reopened P0-04/F-024. Nine invariants had real-market production-path evidence; activated consecutive-session evidence remains absent and P1 corpus gaps remain | after F-024 remediation, re-evaluate stage-21 proof plus activated operational evidence |
| `F-021` | `W19` | P1 | OPEN / `PRE_ACTIVATION_DEFERRED` (`P1-39`) | `Release_Gates` | pengukuran ulang 2026-08-13: `operational_start_date` kosong dan `NULL` pada 72.777 dari 72.777 run; `MARKET_DATA_DAILY_ENABLED=false`; seluruh keluaran berstatus `DEVELOPMENT`, sesuai fase pembangunan | keputusan aktivasi adalah keputusan operator setelah activation checklist terbukti; bukan pekerjaan kode dan bukan blocker burn-down pembangunan |
| `F-020` | `W18` | P1 | OPEN (`P1-38`) | `Replay_Verification_Contract` | 20.635 hasil replay seluruhnya `PASS` dari fixture yang dihasilkan dari run yang diverifikasi; `config_identity` konstanta `'v1'` | korpus lama tidak admissible; menuntut fixture ber-author independen |
| `F-019` | `W17` | P1 | OPEN (`P1-37`) | `Read_Side_Enforcement_Anti_Bypass_Contract` | nol route market-data dan nol domain hilir; larangan bypass tidak dapat dilanggar sekaligus tidak dapat diamati | menuntut konsumen nyata sebelum kepatuhan read-side dapat dibuktikan |
| `F-018` | `W16` | P1 | OPEN (`P1-36`) | `EOD_Eligibility_Snapshot_Contract` | listing tersuspensi tidak memperoleh baris snapshot; 7 kolom dimensi dan reasons JSON nol terisi dari 749.685 | korpus lama menuntut pembangunan ulang snapshot |
| `F-043` | `W15` | P1 | CLOSED (diremediasi 2026-08-11: hash kanonik terukur `aa7357061a66c757…`, terbukti berubah saat universe berubah dan tetap saat tidak) | `Coverage_Universe_Definition_LOCKED.md:52` | **Universe coverage tidak punya version maupun hash.** Yang tersimpan hanya `coverage_universe_count` dan `coverage_universe_basis`; satu-satunya `universe_hash` di seluruh database milik `watchlist_bt_eval`, subsistem backtest yang berbeda. Dua run untuk tanggal yang sama karena itu dapat meresolusi universe berbeda tanpa satu pun catatan universe mana yang dipakai. Bertetangga dengan `F-006` tetapi bukan hal yang sama: hash membuat pergeseran denominator **terdeteksi**, bukan **tidak terjadi** | catat hash kanonik atas himpunan listing yang membentuk universe beserta basisnya, mengikuti konvensi `factorSetHash`; buktikan hash berubah ketika himpunannya berubah dan tetap ketika tidak |
| `F-044` | `W15` | P1 | CLOSED (diremediasi 2026-08-11: 25 identitas dikecualikan tercatat dari 81, dibatasi batas sampel yang sama) | `Coverage_Universe_Definition_LOCKED.md:52` | **Sampel listing yang dikecualikan tidak pernah disimpan.** `coverage_missing_sample_json` menamai listing yang hilang — 11 pada 2026-07-27 — tetapi 81 yang dikecualikan sebagai `NOT_EXPECTED` hanya dihitung. Pembaca evidence dapat melihat berapa yang keluar dari penyebut, tidak siapa, sehingga pengecualian tidak dapat diperiksa ulang terhadap sumbernya | simpan sampel terbatas berisi identitas listing yang dikecualikan, mengikuti mekanisme dan batas sampel missing yang sudah ada |
| `F-017` | `W15` | P1 | PARTIAL — 2026-08-11: tuduhan aritmetika **ditarik** (evidence konsisten; `962-81=881` diverifikasi langsung dari evaluator, rumus terdokumentasi berlaku 856/856). Cacat nyata yang tersisa diperbaiki: `coverage_expectation_unknown_count` tidak pernah diproduksi evaluator dan ditulis `?? 0`; kini NULL bila tidak dihitung, dengan guard pada ketiadaan kuncinya (`P1-35`) | `Coverage_Gate_Enforcement_Contract` | `coverage_expected_count`, `delivered`, `delivered_valid`, `expectation_unknown` seluruhnya 0 dari 71.917; jumlah pengecualian suspensi tidak pernah tersimpan | korpus lama tidak dapat diaudit terhadap exit gate; menuntut derivasi ulang |
| `F-016` | `W14` | P1 | CLOSED (basi; ditutup 2026-08-11 atas pengukuran ulang korpus sekarang: 32.008 titik ATR dibandingkan oracle ber-seed boundary menurut spec LOCKED, maks 0,009392%, nol titik >=0,01%. Kembar `P1-34` yang ditutup hari yang sama) (`P1-34`) | `Indicator_Registry_Baseline` | ATR tersimpan di-seed pada jendela geser; p90 `1,62%`, maks `72,9%` terhadap nilai ber-seed boundary | recompute berbukti atas korpus indikator |
| `F-015` | `W13` | P1 | CLOSED (basi; ditutup 2026-08-11 atas pengukuran ulang korpus sekarang: 32.559 titik dv20 dibandingkan oracle deret mentah, divergensi maks 0,000000%. Kembar `P1-33` yang ditutup hari yang sama) (`P1-33`) | `Volume_and_Turnover_Normalization` | 735.719 baris `dv20_idr` dihitung pada bar yang disesuaikan; aksi harga-saja menghasilkan adjusted price x raw volume | recompute berbukti; besaran dampak pada korpus lama belum diukur |
| `F-042` | `W12` | P0 | CLOSED (diremediasi 2026-08-11: `created_at` dan `recorded_at` dipertahankan pada kedua upsert; baris baru tetap terisi; penyebutan eksplisit dihormati) | `Replay_Verification_Contract_LOCKED.md`; `F-028` | **Impor ulang menggeser koordinat as-known.** `recorded_at` tetap berada di blok yang selalu ditulis pada kedua upsert, sehingga impor ulang tanpa menyebutkannya menimpanya dengan waktu sekarang. Dibuktikan pada baris MLPT produksi di transaksi yang di-rollback: `recorded_at` bergeser 15:08:07 → 16:12:27. Dampaknya membalik perlindungan yang `F-028` bangun: peristiwa yang benar-benar diketahui bulan Juni, bila diimpor ulang pada Agustus, menjadi **tak terlihat** oleh setiap cutoff sebelum Agustus. Arahnya berlawanan dengan kebocoran semula — ia menyembunyikan pengetahuan masa lalu alih-alih membocorkan masa depan — dan sama salahnya bagi replay. Ini sisi ketiga dari perbaikan `F-040`/`F-041`, yang memindahkan field opsional ke blok yang mempertahankan tetapi meninggalkan `recorded_at` di blok yang menimpa | `recorded_at` harus dipertahankan saat baris sudah ada dan pemanggil tidak menyebutnya, tetapi tetap terisi saat baris baru; `updateOrInsert` tidak membedakan keduanya sehingga keberadaan baris perlu diperiksa lebih dulu |
| `F-041` | `W12` | P0 | CLOSED (diremediasi 2026-08-11: aturan bersumber tunggal dipakai kedua upsert; sapuan membuktikan kelasnya hanya dua anggota; guard menutup kelas atas 9 situs) | `Price_Adjustment_Contract_LOCKED.md`; jalur event-risk | **Cacat `F-040` yang sama, di metode saudara yang tidak ikut diperbaiki.** `upsertTradingStatusEvent` masih menulis `source_ref` dan `notes` sebagai `$row[...] ?? null`, sehingga impor ulang tanpa kolom itu menghapusnya diam-diam. Dibuktikan pada baris ARCI produksi di dalam transaksi yang di-rollback: `source_ref` sebuah URL pengumuman IDX dan `notes` teks UMA-nya, keduanya menjadi NULL tanpa error. Paparannya total — **3.700 dari 3.700 baris** membawa keduanya. `F-040` memperbaiki `upsertCorporateAction` dan meninggalkan saudaranya, yang persis pola kesalahan yang dicatat pada sesi ini: satu sisi dari sesuatu yang bersisi dua | terapkan aturan yang sama — kunci tidak hadir mempertahankan, null eksplisit menghapus — dan periksa apakah masih ada metode upsert lain berbentuk serupa |
| `F-040` | `W12` | P0 | CLOSED (diremediasi 2026-08-11: ketiadaan kolom mempertahankan nilai, null eksplisit menghapus; diperbaiki di repository dan importer sekaligus) | `Price_Adjustment_Contract_LOCKED.md` | **Impor ulang menghapus faktor otoritatif tanpa suara.** `upsertCorporateAction` kini menulis kolom kuantitatif dengan `$row[...] ?? null`, sehingga `updateOrInsert` atas kunci (ticker, action_date, action_type, source_name) yang sama **menimpa faktor yang sudah ada dengan NULL** bila CSV berikutnya tidak memuat kolom itu. Diuji di transaksi yang di-rollback pada baris MLPT produksi: `price_adjustment_factor` 0,04 → NULL dan `adjustment_source` `EXCHANGE_ANNOUNCEMENT` → NULL, tanpa error maupun peringatan. Jalur pemicunya justru bentuk CSV minimal tiga kolom yang importer dokumentasikan sebagai sah. Akibatnya indikator akan kembali ke `roc20` −95,7% pada recompute berikutnya. Cacat ini **dibuat oleh remediasi hari ini** dan bersifat aktif: setiap impor berikutnya dapat memicunya | pertahankan nilai tersimpan ketika kolom tidak hadir di CSV, bukan menimpanya dengan NULL; penghapusan faktor otoritatif harus menjadi tindakan eksplisit, bukan efek samping kolom yang tidak diisi |
| `F-039` | `W12` | P0 | OPEN (dibuka 2026-08-11 saat menyiapkan impor faktor split) | `Price_Adjustment_Contract_LOCKED.md` (immutable `RAW`) | **Korpus `eod_bars` sudah disesuaikan provider dan karena itu bukan `RAW`.** Dari 35 stock split dengan rasio terverifikasi IDX, hanya 5 memperlihatkan patahan harga di seri; 30 sisanya tidak memperlihatkan patahan sama sekali, termasuk BMRI 1:2 pada 2023-04-04 yang harga sekitarnya bergerak mulus 5262,5 -> 5200 -> 5225. Bukti langsungnya aritmetis: **13.800 bar (1,82%) pada 89 ticker memiliki `close` pecahan** sementara IDX memperdagangkan saham dalam rupiah bulat — nilai seperti 5112,5 adalah sisa pembagian oleh 2. Provider (`yahoo_finance`) mengembalikan histori yang sudah di-back-adjust untuk split lama, sedangkan split sangat baru (MLPT/RAJA/RMKE, Juli 2026) belum sempat disesuaikan dan masih memperlihatkan patahan. Korpusnya karena itu **campuran**: sebagian skala teradjust, sebagian mentah, di dalam satu kolom yang sama — persis pencampuran yang exit gate stage 11 larang, dan tak terlihat sampai faktor otoritatif pertama hendak diterapkan. Konsekuensi langsung: menerapkan faktor pada baris yang sudah disesuaikan akan **menyesuaikan ganda** | tetapkan status penyesuaian per instrumen/peristiwa sebelum menerapkan faktor apa pun; impor hanya peristiwa yang terbukti belum disesuaikan, dan putuskan apakah `RAW` perlu diakuisisi ulang dari sumber yang benar-benar as-traded |
| `F-038` | `W12` | P2 | OPEN (dibuka 2026-08-11 oleh `MD-REAUDIT W12`) | `Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md` | **Remediasi `F-026` bersandar pada tafsir yang tidak pernah dinyatakan.** Dokumen itu berbunyi: *"An analytical row whose price-product identity is `NULL` is not weakly identified; it is **unidentified** and must not be readable."* Remediasi `F-026` justru memilih **menyajikan** bar tak berlabel disertai penanda `PRICE_PRODUCT_UNRECORDED`, dengan alasan nilainya nyata dan yang hilang hanyalah klaim skala. Pilihan itu bersandar pada tafsir bahwa bar mentah bukan "analytical row" — `eod_indicators` yang analytical dan patuh penuh, sedangkan `eod_bars` adalah artefak RAW. Menurut bunyi harfiahnya tafsir itu berdiri, tetapi ia dibuat di dalam remediasi tanpa dinyatakan, sehingga tidak pernah dapat ditolak pemilik. Bila tafsirnya salah, 756.329 bar seharusnya **tidak dapat dibaca** sampai berlabel, dan itu keputusan yang jauh lebih besar daripada yang saya ambil sendiri | pemilik menegaskan atau menolak tafsir "bar mentah bukan analytical row"; bila ditolak, sisi baca harus menahan bar tak berlabel, bukan menyajikannya dengan penanda |
| `F-013` | `W12` | P1 | CLOSED (basi; ditutup 2026-08-11 oleh `MD-REAUDIT W12` atas bukti yang sama dengan `P1-32`: `eod_indicators` 756.328 baris, **nol** tanpa `price_product_code`) | `Price_Adjustment_Contract` | 756.328 baris indikator tanpa `price_product_code`; baris RAW dan STRUCTURAL_ADJUSTED tidak dapat dibedakan | recompute berbukti; pengisian retroaktif dilarang karena label yang benar belum tentu dapat direkonstruksi |
| `F-010` | `W11` | P1 | OPEN (`P1-31`) | `Corporate_Action_and_Adjustment_Policy` | nol adjustment factor bersumber tersisa setelah faktor turunan diblokir; 126 aksi ber-impact `SCALED` dari IDX tanpa terms | terms aksi korporasi otoritatif dari IDX; rekonsiliasi eksternal, tidak dapat ditutup oleh kode |
| `F-011` | `W11` | P1 | OPEN (`P1-30`) | `Exchange_Market_Structure_Facts` | tidak ada tabel tier band/floor/tick; `min_price_idr` konstanta `50`, band skalar `0.35`, keduanya tanpa sumber dan effective date | tabel tier bersumber dan ber-effective-date dari IDX; rekonsiliasi eksternal |
| `F-007` | `W09` | P1 | OPEN (`P1-29`) | `Canonicalization_Contract_EOD_Bars` | 756.329 baris canonical, nol membawa `source_observation_id`, `listing_id`, `canonicalization_version`, `price_product_code`, maupun `quality_state` | korpus lama menuntut re-ingest berbukti; pengisian retroaktif dilarang karena akan melekatkan observation yang bukan penghasilnya |
| `F-006` | `W08` | P1 | CLOSED (diremediasi 2026-08-12: run memperoleh kolom koordinat pengetahuan sendiri `eod_runs.knowledge_cutoff_at`, distempel sekali setelah proyeksi; penyebut terbukti deterministik pada korpus produksi — 3 evaluasi identik pada tiap cutoff, dan berubah 913→881 saat cutoff bergeser melewati 64 listing yang terekam belakangan) | `Coverage_Gate_Contract` | `2026-06-02` menghasilkan denominator 950 → 949 → 950 pada tiga run di hari eksekusi yang sama (`2026-06-07`), basis `ACTIVE_LISTED_EQUITY_AS_OF_DATE` | denominator as-of harus deterministik untuk tanggal tetap; diserahkan ke `W15` (temporal coverage gate) yang memiliki kontrak ini |

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

## 2026-08-12 — Konsolidasi akar penyebab atas 17 temuan terbuka — HISTORICAL SNAPSHOT

> Snapshot ini menjelaskan alasan urutan sebelum eksekusi. Ia bukan register status aktif.
> `F-006` dan `F-045` yang tercantum dalam analisis awal di bawah telah ditutup masing-masing oleh
> Tahap 1 dan Tahap 2; current state berada pada controller di atas dan bagian penutupan tahap.

Pemeriksaan ulang atas seluruh temuan terbuka untuk mencari akar yang sama, sehingga satu aksi
menutup beberapa temuan. Empat dugaan diuji dengan pengukuran, satu di antaranya gugur.

### Terbukti: dua temuan yang memang satu substansi

| Gabungan | Bukti pengujian | Akibat |
| --- | --- | --- |
| `F-007` + `F-026` | `EodBarsIngestService` menulis keempat kolom (`:198` `listing_id`, `:199` `source_observation_id`, `:207` `canonicalization_version`, `:208` `price_product_code`); keempatnya NULL pada **756.329 dari 756.329** baris | satu temuan tentang empat kolom di satu tabel, bukan dua temuan |
| `F-010` + `F-027` | keduanya berbunyi "nol faktor otoritatif"; `F-010` menyebut 126 aksi `SCALED` tanpa terms, `F-027` menyebut 515 dari 530 aksi tanpa faktor — satu kampanye perolehan terms IDX menutup keduanya | satu upaya perolehan, bukan dua |

### Gugur: `F-020` bukan duplikat `F-030`

Dugaan awal keliru. Bukti dan target keduanya memang berbunyi sama, tetapi `F-020` membawa paruh
yang `F-030` tidak punya: `config_identity` tersimpan masih `'v1'` pada **20.636 dari 20.636** baris.
Perbaikan `F-034` mengubah penulisnya, bukan korpusnya. `F-020` karena itu tidak dipensiunkan —
ia sekadar tinggal di aksi yang sama dengan `F-030`.

### Salah klasifikasi: `F-021` bukan pekerjaan kode

Penulisnya berfungsi (`EodRunRepository:51`, `:111`, membaca `$scope->operationalStartDate()`).
Kolom kosong karena `config/market_data.php:10` berbunyi `env('MARKET_DATA_OPERATIONAL_START_DATE', null)`
dan variabel itu tidak pernah diisi. Yang dibutuhkan satu deklarasi pemilik, bukan remediasi.

### Peta kerja: 17 temuan, 5 alur

| Alur | Temuan | Aksi tunggal yang menutup seluruhnya |
| --- | --- | --- |
| **A. Korpus lama tertinggal dari penulisnya** | `F-007`, `F-026`, `F-017`, `F-018` | penulisnya sudah benar untuk keempatnya; hanya penurunan ulang korpus yang mengisi. Satu kampanye backfill + satu set guard |
| **B. Rujukan otoritatif dari IDX** | `F-010`, `F-027`, `F-011` | data yang kode tidak boleh mengarang. Kanal dan pola impor-lalu-atestasi sudah dibangun untuk split pada 2026-08-11; `F-011` memakai pola yang sama untuk tabel tier |
| **C. Replay tidak independen** | `F-030`, `F-020`, `F-024`, `F-023` | satu replay run di atas fixture ber-author independen menulis ulang `config_identity` sekaligus memutus ketergantungan fixture, dan menghasilkan bukti yang `F-024` tunggu serta gate yang `F-023` tunggu |
| **D. Deklarasi pemilik, bukan perbaikan** | `F-039`, `F-021` | dua kalimat keputusan: apa arti `RAW` bagi korpus yang sudah back-adjusted penyedia, dan kapan platform dinyatakan mulai beroperasi |
| **E. Berdiri sendiri** | `F-006`, `F-019`, `F-038` | tidak berbagi akar; anggota yang sudah ditutup tetap merupakan fakta snapshot, bukan finding aktif |

### Urutan yang mengerucut

Alur **D** digarap lebih dulu karena biayanya menit dan ia menggerbangi yang lain: keputusan `F-039`
menentukan apakah faktor alur **B** boleh diterapkan pada bar yang ada, dan menentukan arti historis
`price_product_code` di alur **A**. Tanpa keputusan itu, alur A dan B berisiko dikerjakan ke arah
yang kemudian dibatalkan — persis pola perulangan sia-sia yang hendak dihindari.

## 2026-08-12 — Urutan pengerjaan: tiap tahap dapat dinyatakan selesai sendiri — HISTORICAL, SUPERSEDED MULAI TAHAP 4

> **STATUS HISTORIS — BUKAN URUTAN AKTIF MULAI TAHAP 4.** Baris Tahap 1–3 tetap merekam
> penyelesaian yang sah. Placeholder Tahap 0 dan baris Tahap 4–9 di bawah dipertahankan sebagai
> alasan penyusunan awal, tetapi tidak lagi menggerakkan controller. Urutan aktif penggantinya berada
> pada bagian `CURRENT AUTHORITATIVE SEQUENCE` tanggal 2026-08-13 di akhir ledger.

Syarat urutan ini: **tidak ada tahap yang menunggu tahap sesudahnya untuk dapat dinyatakan selesai.**
Temuan yang menyandera dua tahap dipecah, sehingga tiap potong menutup dengan bukti sendiri dan
meninggalkan keadaan yang lebih baik bagi tahap berikutnya, bukan utang baru.

### Pemecahan yang diperlukan

| Temuan | Dipecah menjadi | Alasan |
| --- | --- | --- |
| `F-007` `F-026` `F-017` `F-018` | **(a) guard jalur tulis** lalu **(b) backfill korpus** | (a) dapat dibuktikan hari ini tanpa menyentuh korpus, dan setelahnya populasi cacat membeku — hanya bisa menyusut. Tanpa (a), (b) berlomba dengan baris baru yang cacat |
| `F-021` | **(a) deklarasi tanggal mulai operasi** lalu **(b) backfill 72.776 run** | (a) milik pemilik, (b) mekanis. Menggabungkannya membuat pekerjaan mekanis menunggu keputusan |
| `F-010`+`F-027`, `F-011` | **(a) rekam terms otoritatif** lalu **(b) terapkan ke seri** | perekaman terbukti benar tanpa menyentuh harga; penerapan menuntut keputusan `F-039` lebih dulu |
| `F-023` | **(a) gate stage-21 dievaluasi ulang dan melapor jujur** lalu **(b) bukti sesi berturut-turut teraktivasi** | (a) selesai bila gate berjalan dan menolak dengan alasan benar — gate yang menolak dengan tepat adalah gate yang bekerja. (b) menuntut waktu operasional, bukan kode |

### Urutan

| # | Tahap | Menutup | Dinyatakan selesai bila |
| --- | --- | --- | --- |
| 0 | Dua keputusan pemilik | `F-039`, `F-038` | keputusan tercatat beserta alasannya; tidak ada kode |
| 1 | Determinisme denominator coverage | `F-006` | tanggal tetap + `knownAt` tetap menghasilkan `coverage_universe_hash` identik pada evaluasi berulang |
| 2 | Ekspos bukti coverage — **SELESAI 2026-08-12** | `F-045` | keempat field muncul di payload ekspor, dan guard menggagalkan bila sebuah field tersimpan tanpa jalur ekspor |
| 3 | Bekukan populasi cacat — **SELESAI 2026-08-13** | `F-007a` `F-026a` `F-017a` `F-018a` | guard menolak baris yang kehilangan tiap field, dibuktikan per field |
| 4 | Deklarasi operasi + backfill | `F-021a` `F-021b` | `operational_start_date` tidak lagi NULL pada 72.776 run |
| 5 | Backfill silsilah korpus | `F-007b` `F-026b` `F-017b` `F-018b` | hitungan NULL menuju nol per field, terukur |
| 6 | Fixture replay independen | `F-030`, `F-020` | replay berjalan di atas fixture ber-author independen; `config_identity` tersimpan bukan lagi `'v1'` |
| 7 | Replay proof | `F-024` | sisa satu-satunya butir `F-024` terpenuhi oleh keluaran tahap 6 |
| 8 | Perolehan terms IDX | `F-010`+`F-027`, `F-011` | terms terekam otoritatif; penerapan mengikuti keputusan tahap 0 |
| 9 | Gate stage-21 | `F-023a` | gate berjalan dan melaporkan status tiap invariant dengan jujur |

### Di luar jangkauan fase ini

| Temuan | Sebab |
| --- | --- |
| `F-019` | menuntut konsumen nyata; menutupnya berarti membangun domain hilir, yaitu menambah lingkup baru |
| `F-023b` | menuntut bukti sesi berturut-turut teraktivasi; hanya waktu operasional yang dapat menyediakannya |

Keduanya tidak dihitung dalam burn-down fase ini. Bila tetap dihitung, hitungan tidak akan pernah
mencapai nol dan kemajuan nyata akan terbaca sebagai kemacetan.

### Mengapa urutannya begini

Tahap 0 lebih dulu karena ia menggerbangi tahap 5 dan 8 sekaligus: arti `RAW` menentukan makna
historis `price_product_code` dan menentukan apakah faktor boleh diterapkan pada bar yang ada.
Tahap 1 mendahului tahap 2 karena angka diperbaiki sebelum diterbitkan, bukan sesudah.
Tahap 3 mendahului tahap 5 karena backfill di atas populasi yang masih bertambah tidak pernah selesai.
Tahap 6 mendahului tahap 7 karena sisa `F-024` justru keluaran tahap 6.

## Tahap 1 — `F-006` ditutup, 2026-08-12

Tahap pertama dari urutan 2026-08-12. Dikerjakan tanpa menunggu tahap 0, karena determinisme
penyebut tidak bersinggungan dengan arti `RAW`.

### Yang dikerjakan

Run kini memiliki **koordinat pengetahuannya sendiri**, `eod_runs.knowledge_cutoff_at`, ditetapkan
pada jalur penciptaan run lalu dipakai ulang oleh setiap stage berikutnya. Ia sengaja **bukan** `started_at`: proyeksi
identitas berjalan sebagai efek samping pembacaan universe yang pertama, sehingga setiap baris yang
ia tulis membawa `recorded_at` lebih lambat daripada awal run — memotong di `started_at` mengosongkan
universe seluruhnya (31 error, 2026-08-11, dikembalikan). Kedua jalur penciptaan run karena itu
mendorong proyeksi sampai selesai lebih dulu, baru menstempel koordinatnya. Reader
`resolveKnowledgeCutoff` hanya membaca; ia tidak lagi dapat menulis ulang makna run lama.

Nullable dengan sengaja. Koordinat null berarti "tanpa batas", dan itulah yang sebenarnya dilakukan
setiap run sebelum kolom ini ada; mengisinya secara mundur akan mengklaim sebuah cutoff dihormati
padahal tidak.

### Bukti pada korpus produksi

Tanggal dagang `2026-07-15`, tiga evaluasi per cutoff:

| cutoff | penyebut | hash universe | 3 evaluasi |
| --- | --- | --- | --- |
| `2026-01-01` | 913 | `c046ef5dced80bca…` | IDENTIK |
| `2026-04-01` | 913 | `c046ef5dced80bca…` | IDENTIK |
| `2026-08-12` | 881 | `991f9506f5ffae81…` | IDENTIK |

Januari dan April sengaja dilaporkan meski identik: sebaran `md_listings.recorded_at` berbunyi 913
baris pada 2025-12, 58 pada 2026-06, 6 pada 2026-07 — tidak ada listing terekam antara Januari dan
April, sehingga jawaban yang sama justru bukti cutoff bekerja. Yang membuktikan ia tidak sekadar
diabaikan adalah pasangan ketiga: koordinat bergeser melewati 64 listing yang terekam belakangan dan
penyebut ikut bergerak.

Uji `CoverageDenominatorKnowledgeCutoffTest` memasangkan tiap klaim dengan sanggahannya — korpus yang
sama dibaca tanpa cutoff **harus** bergerak, dan cutoff yang lebih lambat **harus** menerima listing
yang lebih baru. Cutoff yang menolak segalanya juga menghasilkan jawaban stabil; tanpa pasangan itu
kestabilan tidak membuktikan apa pun.

### Cacat yang ditemukan saat mengerjakannya

Mirror uji memberi `tickers.created_at` default `CURRENT_TIMESTAMP`, yaitu jam **nyata**, sementara
suite membekukan Carbon berbulan-bulan lebih awal. `projectTicker` memakai kolom itu sebagai
koordinat pengetahuan setiap listing yang diproyeksikannya, sehingga fixture memodelkan run yang
membaca data yang belum terekam. Tidak berbahaya selama tidak ada yang membaca koordinat itu; begitu
`F-006` membuatnya dibaca, 32 tes pipeline gagal sekaligus.

Produksi tidak membawa baris seperti itu: seluruh 977 ticker dibuat antara 2025-12-15 dan 2026-07-14,
nol mendahului jam. Fixture-lah yang keliru, bukan rancangannya — karena itu defaultnya diperbaiki,
bukan cutoffnya dilonggarkan. Tujuh tabel mirror lain memakai default jam-nyata yang sama, tetapi
tidak satu pun dibaca sebagai koordinat pengetahuan.

**Perilaku gagal-amannya terbukti sekaligus.** Saat cutoff mengosongkan universe, pipeline
memblokir dengan `RUN_COVERAGE_NOT_EVALUABLE`; ia tidak diam-diam mengecilkan penyebut. Itu persis
yang dituntut `Coverage_Universe_Definition_LOCKED.md:57`.

### Yang tidak ditutup oleh tahap ini

**202 pasangan tanggal/hari lama tetap tidak dapat dijelaskan.** Run-run itu tidak membawa koordinat
dan tidak akan pernah membawanya, jadi penyebut mereka tidak dapat direproduksi secara mundur. Itu
bukan sisa `F-006` melainkan anggota alur A — korpus lama tertinggal dari penulisnya — dan akan
selesai bersama alur itu.

**Run legacy tetap tidak diisi mundur.** Setelah bukti tahap 2, run baru `72922` menyimpan
`knowledge_cutoff_at=2026-08-12 03:38:07`; semua run sebelumnya tetap `NULL`. Jalur baru distempel
pada saat penciptaan, sedangkan reader mengembalikan `NULL` tanpa mutasi bagi run legacy.

### Revalidasi tahap 1 setelah dibuka ulang, 2026-08-12

Pembukaan ulang menemukan dua kekurangan yang masih murni berada dalam batas `F-006`.

Pertama, bukti awal hanya menggerakkan akar identity. Denominator memiliki akar temporal kedua,
yaitu status suspensi. `CoverageDenominatorKnowledgeCutoffTest` kini memasukkan suspensi yang
`recorded_at`-nya lebih lambat daripada cutoff: evaluasi ulang pada cutoff tetap mempertahankan
denominator dan hitungan `NOT_EXPECTED`, sedangkan cutoff yang digeser melewati waktu pencatatan
harus menerima suspensi dan mengubah denominator. Dengan demikian cutoff dibuktikan pada kedua
masukan denominator, bukan hanya pada hash identity.

Kedua, klaim "ditetapkan sekali" sebelumnya hanya merupakan konvensi reader. Override pada
`createPromoteRunFromSeed()` masih dapat mengganti koordinat sebelum insert, dan update Eloquent
masih dapat mengubah atau menstempel `knowledge_cutoff_at` sesudah penciptaan. Override tersebut
kini ditolak sebelum mempunyai efek samping, sedangkan model `EodRun` menolak setiap update yang
mengotori field itu. Negative test membuktikan tiga jalur: override promote ditolak tanpa membuat
run, koordinat run baru tidak dapat diubah, dan `NULL` legacy tidak dapat distempel kemudian.

Revalidasi terfokus: `CoverageDenominatorKnowledgeCutoffTest` **11 test, 29 asersi**; regresi
coverage/run terkait **43 test, 154 asersi**; integrasi pipeline **56 test, 1.252 asersi**. Suite
penuh: **1.414 test, 9.814 asersi**, seluruhnya lulus dengan exit code 0. Tidak ada perubahan pada
ekspor evidence tahap 2, semantik field coverage, replay, backfill, atau guard tahap 3.

### Penutupan residu eksekusi run legacy, 2026-08-12

Audit baca-saja setelah revalidasi menemukan **45 run aktif** masih membawa
`knowledge_cutoff_at=NULL`; seluruh run aktif ber-cutoff `NULL` berada pada populasi itu. Dua pintu
masih dapat melanjutkannya: `getOrCreateOwningRun()` dapat memakai ulang run aktif tersebut, dan
`startStage()` dengan `run_id` eksplisit dapat melewati jalur penciptaan run. Imutabilitas mencegah
cutoff palsu ditulis kemudian, tetapi sendirian belum mencegah evaluasi baru berjalan tanpa cutoff.
Ini residu `F-006`, bukan pekerjaan backfill.

Run tanpa cutoff kini ditolak pada kedua pintu sebelum mutasi: repository memeriksa kandidat aktif
sebelum memperbarui config snapshot, sedangkan pipeline memeriksa `run_id` eksplisit segera setelah
lookup dan memeriksa ulang owning run sebelum pemeriksaan konteks, `touchStage`, atau penulisan
event. Penolakan **tidak** menstempel cutoff, membuat run pengganti, mengubah lifecycle, atau
menyentuh korpus. Run legacy terminal tetap terbaca dengan `NULL` sebagai sejarah yang jujur dan
dapat menjadi seed bagi promote run baru; run turunannya memperoleh koordinat penciptaannya sendiri.
Yang dilarang hanya melanjutkan eksekusi pada identitas run lama yang tidak pernah mempunyai batas
pengetahuan.

Dua negative test membuktikan kedua bypass: kandidat aktif ber-cutoff `NULL` tidak dipakai ulang dan
tidak dimutasi, sedangkan `run_id` eksplisit ditolak sebelum `getOrCreateOwningRun`, `touchStage`, dan
`appendEvent`. Fixture integrasi yang memodelkan run pascamigrasi diberi cutoff eksplisit; fixture
legacy negatif tetap `NULL`. Uji terfokus: **30 test, 60 asersi**. Suite penuh saat penutupan
eksekusi: **1.416 test, 9.821 asersi**, seluruhnya lulus. Tidak ada perubahan pada ekspor evidence tahap 2,
semantik coverage, replay, backfill, maupun guard tahap 3.

Sapuan governance terakhir menemukan kode penolakan `RUN_KNOWLEDGE_CUTOFF_MISSING` dapat diteruskan
oleh orchestrator sebagai `reason_code`, tetapi belum ada di kamus otoritatif. Kode tersebut kini
terdaftar sinkron sebagai `RUN/HARD` pada `Reason_Codes_Registry.md` dan `Reason_Codes_Seed.sql`.
Guard Tahap 1 memeriksa kedua lokasi secara eksplisit, sedangkan suite sinkronisasi registry/seed
tetap memeriksa keseluruhan kamus. Perubahan ini hanya membuat kegagalan determinisme yang sudah ada
dapat ditafsirkan secara otoritatif; tidak mengubah jalur eksekusi atau menambah kebijakan baru.
Kamus runtime diperbarui hanya untuk baris ini secara idempotent—bukan dengan menjalankan seluruh
seed—dan jumlahnya bergerak **360→361**; baris hasil baca kembali persis `RUN/HARD`, aktif, dengan
deskripsi yang sama seperti registry/seed. Verifikasi reason-code terkait: **26 test, 154 asersi**.
Suite penuh final: **1.417 test, 9.823 asersi**, seluruhnya lulus.

### Status

Verifikasi akhir: 1.417 tes, 9.823 asersi, seluruhnya lulus. Migrasi
`2026_08_12_000001_add_knowledge_cutoff_at_to_eod_runs` diterapkan.

Tahap 1 **selesai**. Tahap 2 (`F-045`) ditutup pada bagian berikutnya.

## Impact review alignment kontrak sebelum Tahap 2 — BUKAN EXIT EVIDENCE

Review ini memenuhi change-control blueprint tanpa menciptakan kebijakan baru. Owner contract yang
sudah berlaku membedakan temporal universe, `EXPECTED`/`NOT_EXPECTED`/`UNKNOWN`, delivery, dan
canonical-valid; ia juga mewajibkan field coverage menjadi replay-comparable. Perubahan mapping
berikut karena itu merupakan alignment terhadap aturan yang sudah tertulis, bukan perluasan Tahap 2:

- writer menyimpan raw universe, expected denominator, delivered numerator, dan canonical-valid
  pada field masing-masing;
- reader replay tidak lagi mengambil expected dari raw universe;
- metadata schema/dictionary menjelaskan mapping yang sama.

Impact menurut `Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`:

1. owner contract diperbarui untuk menyebut mapping persisted/evidence secara eksplisit;
2. urutan tidak berubah: alignment ini tidak menutup guard Tahap 3, backfill Tahap 5, fixture
   independen Tahap 6, maupun replay proof Tahap 7;
3. schema mirror, dictionary, test, dan evidence specification diselaraskan; tidak ada perubahan
   command/ops behavior yang diperlukan;
4. implementation conformance global tetap `NOT_GRANTED` seperti controller state.

Alignment ini tidak dihitung untuk verdict `F-045`. Ia dicatat terpisah agar audit tidak
mengatribusikan writer/replay/denominator work sebagai implementasi diam-diam Tahap 2.

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
| `F-038` | keputusan dan, bila diperlukan, enforcement pada Tahap 5 | tidak boleh meninggalkan implementasi cabang penolakan untuk tahap berikutnya |
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
| 4 | Keputusan makna `RAW` — **BELUM DIMULAI** | `F-039a` | pemilik menetapkan perlakuan korpus provider-back-adjusted, identitas produk yang sah, dan apakah akuisisi ulang as-traded diperlukan; keputusan dan alasannya tercatat, tanpa kode atau mutasi data |
| 5 | Keputusan batas baca bar tak beridentitas — **BELUM DIMULAI** | `F-038` | pemilik menegaskan apakah canonical bar termasuk “analytical row”; bila menerima tafsir sekarang, keputusan menutup gap tafsir tanpa kode, dan bila menolak, read boundary beserta negative oracle diselesaikan pada tahap ini |
| 6 | Rekam terms corporate action otoritatif — **BELUM DIMULAI** | `F-010a` `F-027a` | event revision, source evidence, ex/effective date, quantitative terms, dan verification state terekam untuk scope yang dideklarasikan; unknown tetap `NULL/UNKNOWN`; belum ada penerapan ke seri |
| 7 | Rekam tier struktur pasar IDX — **BELUM DIMULAI** | `F-011a` | tier band/floor/tick bersumber, ber-effective-date, ber-revisi, dan coverage scope-nya terekam; konstanta lama belum boleh disebut exchange-verified dan belum ada penerapan ke output |
| 8 | Rekonstruksi korpus aktif satu kali melalui lifecycle normal — **BELUM DIMULAI** | `F-007b` `F-026b` `F-017b` `F-018b` `F-039b` `F-010b` `F-027b` `F-011b` | untuk setiap tanggal dalam scope beku, current pointer menunjuk publikasi baru yang tersegel; owning run dan artefak aktif membawa lineage bar, evidence coverage, fakta eligibility, satu identitas produk, factor revision, dan tier revision yang lengkap; hitungan pelanggaran pada populasi **current-authoritative** menuju nol; history lama tidak diubah |
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

Tahap berikut yang diizinkan adalah **Tahap 4 — keputusan makna `RAW` (`F-039a`)**. Tahap ini hanya
merekam keputusan pemilik dan alasannya. Tahap 5, authority acquisition, rekonstruksi korpus, replay,
activation, serta perubahan kode/data apa pun belum boleh dikerjakan bersamaan dengannya.

### Evidence penyesuaian urutan — 2026-08-13

Penyesuaian strategi ini **PASS**, tetapi Tahap 4 tetap **BELUM DIMULAI**. Perubahannya terbatas pada
ledger dan guard sinkronisasi dokumentasi. Tidak ada kode produksi, `.env`, migration, schema,
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
