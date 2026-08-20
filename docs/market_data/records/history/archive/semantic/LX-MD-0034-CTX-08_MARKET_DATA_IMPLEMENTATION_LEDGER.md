# Legacy Semantic Extract — LX-MD-0034-CTX-08

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `CONTEXT`
- Source range: `L1658-L1890`
- Extract body SHA1: `A2EFC2EFA1342D96CE9DC55D5E56A749A7DD40EC`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
