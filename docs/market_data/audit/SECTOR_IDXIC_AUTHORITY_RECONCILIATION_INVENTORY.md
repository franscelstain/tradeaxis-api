# Sector IDX-IC Authority Reconciliation Inventory

Direkam: `2026-08-10`. Sifat: **evidence companion**, bukan behavioral authority. Owner contract tetap `../book/Sector_Classification_Contract_LOCKED.md`.

## Tujuan

Mencatat rekonsiliasi dua arah antara artefak resmi IDX yang tersedia dan data membership sektor yang dipegang platform, sehingga keputusan impor berikutnya berangkat dari angka yang sudah diverifikasi, bukan dari perkiraan.

Berkas ini **tidak** memberi otoritas kepada data mana pun. Ia mencatat apa yang terbukti cocok, apa yang tidak, dan apa yang belum dimiliki.

## Artefak resmi yang dimiliki

Seluruhnya berada di luar repositori, pada `~/Downloads`. Checksum direkam agar identitasnya dapat diverifikasi ulang kapan saja.

| Artefak | SHA-256 |
|---|---|
| `idx-industrial-classification.zip` | `0b6b2e136e0e729fc80fb5bd97e73623aab7c461af89552d6e030837635bbcdd` |
| `Pengumuman Peluncuran IDXIC.pdf` | `ffdda51c1744125bf4b97e56c73f5482ec247b00171616a8525a00f9e84432bd` |
| `Klasifikasi Industri Perusahaan Tercatat.pdf` | `16d31e3ed82592828347ad047cea086300dc43b039518bfa1c1fd95069b7f12d` |
| `Panduan IDX Industrial Classification v1.1.pdf` | `113e1e41f3c0c5d6c4f08cac0b75872e9b833b310983a54cd43fde1bc0827182` |
| `idx_ic_saham_obligasi_baseline_2021.csv` | `948de9c32aa63c7d462e740f5ebf54b799f759a8671747e6177a8d16709c2ec4` |
| `idx_ic_saham_baseline_2021.csv` | `02118558867d4d71b53adbfce2a153b81fd36212cd35501486fed6bc8ab7ebb4` |
| `Perubahan Klasifikasi Industri/Klasifikasi Industri Peng-00171.pdf` | `6bc80039a80409e9becbb5273938becaaf04ca9699063ff54a0590858db74abf` |
| `Perubahan Klasifikasi Industri/Klasifikasi Peng-00370.pdf` | `b605ecd02c95512a7995e4e67b256f16257655abcc13818ebc2fa996e3d03120` |
| `Perubahan Klasifikasi Industri/PKIE Peng-00149.pdf` | `e5688258457510999766ab9e4c27fa71dbdef91753a0df946db2bc7946a5a5f5` |

Checksum `idx_ic_saham_obligasi_baseline_2021.csv` **cocok** dengan yang tercatat pada kolom `source_file_sha256` di `outputs/idxic-pending-effective-date-20260809/`. Rantai buktinya utuh.

## Yang ditetapkan pengumuman resmi

`Peng-00007/BEI.POP/01-2021`, bertanggal 13 Januari 2021:

1. **Tanggal efektif peluncuran IDX-IC: 25 Januari 2021.** Ini menggantikan JASICA yang dipakai sejak 1996.
2. **Kadens evaluasi: sekali setahun.** Evaluasi April–Mei, diumumkan akhir Juni, dan **berlaku efektif hari bursa pertama bulan Juli**.
3. **Perubahan diumumkan lewat kelas dokumen bernama:** *Pengumuman Perubahan Klasifikasi Industri Perusahaan Tercatat*.

Butir 2 dan 3 yang membuat Gate 13 dapat dijalankan. Rekonsiliasi eksternal tidak lagi berarti "cari sesuatu yang mungkin ada" — ia punya kadens tetap, tanggal efektif yang dapat dihitung, dan kelas dokumen yang dapat dicari.

Lampiran `Klasifikasi Industri Perusahaan Tercatat.pdf` bertanggal **per 19 Januari 2021** dan memuat **822 ticker unik** pada 39 halaman.

## Hasil rekonsiliasi dua arah

Dibandingkan: himpunan ticker pada lampiran PDF versus baris CSV yang ber-`effective_from = 25/01/2021`.

| Arah | Jumlah |
|---|---:|
| Ticker unik pada lampiran PDF | `822` |
| Baris CSV efektif 25/01/2021 | `765` |
| **CSV yang tidak ada di PDF** | **`0`** |
| PDF yang tidak tercakup CSV | `57` |
| Irisan | `765` |

**Nol baris CSV yang tidak dapat ditemukan di dokumen resmi.** Ini arah yang paling menentukan: baseline yang dipegang platform tidak mengandung entri karangan.

### Membedah 57 yang tidak tercakup

| Kelompok | Jumlah | Ada di universe platform |
|---|---:|---:|
| Berawalan `X` (instrumen non-saham) | `49` | `0` |
| Lainnya | `8` | `1` |

Delapan lainnya: `BBKK`, `CNTB`, `DBTN`, `DGNS`, `DIPP`, `MGIA`, `MJAG`, `SMFP`.

**Celah material rekonsiliasi baseline: satu ticker, `DGNS`** — aktif di platform, listing `2021-01-15`, dan sudah tercatat sebagai baris pertama pada berkas audit `idx_ic_105_saham_tanpa_bukti_effective_from_2023-01-02.csv`. Konsisten dengan pelacakan yang sudah ada.

## Provenance CSV: lebih kuat dari yang diperkirakan

Pemeriksaan awal saya memakai kolom `source_document` dan menyimpulkan hanya 14 baris yang bersumber pengumuman. Itu **salah dan terlalu rendah**. Diskriminator yang benar adalah `source_announcement_no`, dan dengan itu **779 dari 1.022 baris** membawa nomor pengumuman resmi beserta tanggal dan nomor halaman.

| Nomor pengumuman | Baris | Efektif | Diumumkan | Halaman |
|---|---:|---|---|---|
| `Peng-00007/BEI.POP/01-2021` | `765` | `25/01/2021` | `13/01/2021` | 28–35+ |
| `Peng-00171_BEI.POP_06-2021` | `6` | `01/07/2021` | `24/06/2021` | 1–2 |
| `Peng-00370/BEI.POP/11-2021` | `1` | `25/11/2021` | `24/11/2021` | 1 |
| `Peng-00149/BEI.POP/06-2022` | `7` | `01/07/2022` | `24/06/2022` | 1–2 |
| *(tanpa nomor)* | `243` | *(kosong)* | — | — |

## Kadens terkonfirmasi dua kali

Dua siklus tahunan yang dimiliki mengikuti pola yang sama persis dengan yang dinyatakan pengumuman peluncuran:

| Diumumkan | Berlaku efektif | Selisih |
|---|---|---|
| `24/06/2021` | `01/07/2021` | akhir Juni → hari bursa pertama Juli |
| `24/06/2022` | `01/07/2022` | akhir Juni → hari bursa pertama Juli |

Ini menjadikan pola nomor dokumen dapat diprediksi untuk siklus yang belum dimiliki: **`Peng-XXXXX/BEI.POP/06-YYYY`**, diumumkan sekitar 24 Juni.

## Empat belas reklasifikasi yang dimiliki

Seluruhnya **mendahului** dataset start `2023-01-02`:

| Efektif | Ticker | Perubahan |
|---|---|---|
| `01/07/2021` | `EMTK`, `HKMU`, `KREN`, `PANI`, `POLA`, `TFAS` | sektor → I, B, I, D, G, I |
| `25/11/2021` | `ZBRA` | sektor → C |
| `01/07/2022` | `BIPI`, `IATA`, `MITI`, `RISE`, `TELE`, `WIFI`, `YELO` | lihat catatan di bawah |

**Catatan penting bagi implementasi impor.** Tidak semua reklasifikasi mengubah sektor:

| Ticker | Sebelum | Sesudah | Sifat |
|---|---|---|---|
| `IATA` | `K` / `K111` | `A` / `A121` | sektor **dan** sub-industri berubah |
| `BIPI` | `A` / `A111` | `A` / `A122` | **hanya sub-industri** |
| `MITI` | `A` / `A111` | `A` / `A112` | **hanya sub-industri** |

Platform menyimpan `sector_code`, sehingga revisi seperti `BIPI` dan `MITI` akan tampak "tidak berubah" bila hanya sektornya yang dibandingkan. Interval lamanya tetap harus ditutup dan interval baru dibuka, karena revisi klasifikasinya nyata dan bertanggal resmi. Memperlakukannya sebagai no-op akan menghapus jejak bahwa IDX pernah mengevaluasi instrumen itu.

## Verifikasi terhadap dokumen sumber reklasifikasi

Ketiga PDF pengumuman perubahan diperoleh dan diperiksa langsung. Jumlah emiten yang dinyatakan tiap dokumen dicocokkan dengan jumlah baris di CSV:

| Pengumuman | Dinyatakan dokumen | Baris CSV | Efektif menurut dokumen | Cocok |
|---|---:|---:|---|:--:|
| `Peng-00171/BEI.POP/06-2021` | *"6 (enam) Perusahaan Tercatat"* | `6` | 1 Juli 2021 | ya |
| `Peng-00370/BEI.POP/11-2021` | 1 emiten — `ZBRA`, `J` → `C` | `1` | 25 November 2021 | ya |
| `Peng-00149/BEI.POP/06-2022` | *"7 (tujuh) Perusahaan Tercatat"* | `7` | 1 Juli 2022 | ya |

**Nol selisih.** CSV menangkap seluruh isi ketiga pengumuman, tidak kurang dan tidak lebih. Rantai buktinya kini lengkap: dokumen sumber, checksum, jumlah, dan tanggal efektif semuanya terverifikasi.

`Peng-00149` juga memuat bagian B mengenai penyesuaian indeks sektoral; itu konsekuensi dari perubahan klasifikasi, bukan tambahan emiten yang direklasifikasi.

Dengan demikian keadaan klasifikasi IDX yang **terdokumentasi penuh** adalah baseline 25 Januari 2021 ditambah 14 perubahan pada tiga tanggal efektif: `2021-07-01`, `2021-11-25`, dan `2022-07-01`.

### Yang tetap tidak terjelaskan

Keempat tanggal efektif itu seluruhnya mendahului dataset start `2023-01-02`. Sementara perbandingan terhadap `idx_stock_screener` menunjukkan **17 emiten kini bersektor berbeda** dari keadaan terdokumentasi tersebut.

Ketujuh belas selisih itu **tidak dijelaskan oleh satu pun dokumen yang dimiliki**. Perubahannya nyata — sumbernya IDX sendiri — tetapi tanggal berlakunya tidak diketahui.

## Siklus yang hilang: seluruhnya di dalam jendela dataset

CSV berhenti pada Juli 2022. Dataset mulai `2023-01-02`. Artinya **tidak satu pun siklus reklasifikasi yang jatuh di dalam jendela dataset dimiliki.**

Dihitung dari `market_calendar` — hari bursa pertama bulan Juli:

| Siklus | Tanggal efektif | Dimiliki |
|---|---|---|
| Juli 2023 | `2023-07-03` | **tidak** |
| Juli 2024 | `2024-07-01` | **tidak** |
| Juli 2025 | `2025-07-01` | **tidak** |
| Juli 2026 | `2026-07-01` | **tidak** |

Keadaan database mengonfirmasinya dari sisi lain:

```
interval tertutup (effective_to)      0 dari 971
supersedes_membership_id terisi       0
baris efektif awal Juli               1  ← IPO (e_ipo), bukan reklasifikasi
```

Inilah bentuk kegagalan yang kontrak sebutkan secara harfiah: reklasifikasi terjadi di IDX, tidak pernah diimpor, interval lama tetap terbuka, dan sektor yang salah dikembalikan **tanpa error**. Dampaknya mengalir langsung ke `sector_roc20` dan `rs_20_vs_sector`.

## Dua berkas baseline: peran yang saling melengkapi

`idx_ic_saham_baseline_2021.csv` diperiksa terpisah. Ia **subset ketat** dari versi `_obligasi_` — nol kode yang tidak ada di sana — tetapi profil provenance-nya jauh lebih bersih.

| | `_saham_` | `_obligasi_` |
|---|---:|---:|
| Baris | `717` | `1.022` |
| Kode unik | `717` | `1.007` |
| Bersumber pengumuman | `715` (**99,7%**) | `779` (76%) |
| Memuat reklasifikasi | **tidak** | `14` |
| Tanpa sumber | `2` | `243` |

Selisih baseline `765` versus `715` terjelaskan sepenuhnya: **kelimapuluh kode tambahan seluruhnya berinstrumen `OBLIGASI`**. Jadi `_saham_` adalah irisan ekuitas dari pengumuman yang sama, bukan berkas yang berbeda otoritasnya.

Dua baris ganjil pada `_saham_` tanpa nomor pengumuman: `KETR` (`eff 11/01/2021`, mendahului peluncuran IDX-IC) dan `FLMC` (`eff 08/07/2021`, bukan siklus 1 Juli). Keduanya perlu sumber sebelum dapat diperlakukan otoritatif.

### Yang tidak ditutup berkas ini

Dua pemeriksaan dijalankan, keduanya negatif:

- **Celah `DGNS`** — tidak ada di `_saham_`. Delapan ticker celah baseline tidak muncul di kedua berkas.
- **243 baris tanpa sumber** — nol di antaranya bersumber pada `_saham_`. Ketiga ratus kode tambahan pada `_obligasi_` memuat seluruh 243 itu, dan `_saham_` tidak menyentuhnya sama sekali.

### Implikasi untuk impor

Keduanya dibutuhkan, dengan peran berbeda:

1. **`_saham_` sebagai baseline ekuitas** — 99,7% bersumber, dan lingkupnya cocok dengan universe platform yang nol berisi instrumen berawalan `X`.
2. **`_obligasi_` sebagai satu-satunya pembawa riwayat reklasifikasi** — 14 revisi 2021–2022 tidak ada di `_saham_`.

Ke-243 baris tanpa sumber tetap di luar impor sampai sumbernya ada.

## Celah kedua: 243 baris tanpa sumber

`243` dari `1.022` baris CSV tidak memiliki `effective_from` maupun `source_announcement_no` — `235` berinstrumen `SAHAM` dan `8` `SAHAM & OBLIGASI`. Baris-baris ini tidak dapat diimpor sebagai membership otoritatif tanpa melanggar aturan authority, karena tidak ada yang menyatakan sejak kapan sektornya berlaku.

Berkas `outputs/idxic-pending-effective-date-20260809/` sudah melacak sebagian di antaranya dengan `evidence_status = MISSING_EXPLICIT_EFFECTIVE_FROM_SINCE_LISTING`. Pelacakan itu benar dan harus dipertahankan; menetapkan `effective_from = 2023-01-02` untuk mereka akan mengarang tanggal berlaku.

## Hasil dry-run impor, `2026-08-10`

Dua berkas input dibangun dari artefak resmi dan disimpan di `outputs/idxic-dryrun-20260810/`. Identitasnya memakai `listing_uid`, sesuai yang diwajibkan command.

| Berkas | Sumber | Baris | Diterima | Error |
|---|---|---:|---:|---:|
| `01_baseline_saham_peng00007.csv` | `_saham_`, `Peng-00007` | `714` | `714` | `0` |
| `02_overlay_reklasifikasi.csv` | `_obligasi_`, non-`Peng-00007` | `14` | `14` | `0` |

Satu baris dilewati saat pembangunan: **`FINN`** tidak memiliki `listing_uid` di `md_listings`, sehingga tidak dapat diimpor lewat identitas first-class.

Database terbukti tidak berubah sesudahnya: `971` baris, `0` ber-`source_authority_class`.

### `recorded_at` sengaja diisi waktu impor, bukan tanggal pengumuman

Pengumuman terbit 2021; platform baru mengetahuinya sekarang. Mengisi `recorded_at` dengan tanggal pengumuman akan mengklaim pengetahuan yang platform tidak miliki, dan as-known replay untuk 2021–2026 akan melihat sektor yang saat itu belum pernah tercatat.

Konsekuensinya harus disadari: **as-known replay untuk tanggal sebelum impor akan meresolusi tanpa sektor otoritatif**, dan itu jawaban yang benar.

### Dua hal yang dry-run ini **tidak** buktikan

**Pertama — perencanaan overlap tidak melihat baris legacy.** `SectorClassificationRepository::appendMembership()` memuat baris pembanding dengan filter `whereIn('source_authority_class', AUTHORITATIVE_CLASSES)`. Ke-971 baris legacy ber-`NULL` karena itu **tidak terlihat** oleh logika supersession, dan tidak ada indeks unik pada `(listing_id, effective_from)` yang akan menolaknya.

Artinya apply akan menghasilkan `971 + 714 = 1.685` baris: satu himpunan legacy yang tetap terbuka dan tak terlihat resolver, dan satu himpunan otoritatif yang terlihat. Resolver akan mengembalikan yang benar, tetapi baris legacy menjadi residu yatim — tidak ditandai superseded, tidak terhubung ke penggantinya.

Nol error pada dry-run **bukan** berarti tidak ada tumpang tindih; berarti tumpang tindihnya berada di luar jangkauan pemeriksaan.

**Kedua — overlay diuji terhadap database tanpa satu pun baris otoritatif.** Saat dry-run overlay dijalankan, `$knownRows` kosong karena belum ada baris `EXCHANGE_AUTHORITATIVE`. Jadi `14 planned revisions` hanyalah penyisipan biasa; **penutupan interval tidak pernah diuji**. Perilaku overlay yang sesungguhnya baru terlihat setelah baseline diterapkan.

Urutan yang benar karena itu bukan "dry-run keduanya lalu apply keduanya", melainkan: apply baseline, **dry-run ulang overlay**, baru apply overlay.

## Penandaan baris legacy, `2026-08-10`

Atas instruksi eksplisit, ke-971 baris legacy dinyatakan kelasnya sebelum impor otoritatif dijalankan.

### Kelas yang dipilih dan alasannya

Kontrak memiliki kosakata **tertutup** berisi tiga kelas. `LEGACY_SUPERSEDED` tidak ada di dalamnya, dan mengarangnya akan melanggar kontrak yang sedang ditegakkan. Ketiganya diuji:

| Kelas | Syarat | Terpenuhi |
|---|---|---|
| `OPERATOR_ENTERED` | menuntut *named operator* | **tidak** — `operator_name` kosong pada 971/971 |
| `EXCHANGE_AUTHORITATIVE` | publikasi IDX ber-referensi | **tidak** — referensinya NLI dan halaman profil, bukan pengumuman klasifikasi IDX-IC ber-tanggal efektif |
| `DERIVED_REFERENCE` | *"may corroborate or trigger review, never establish"* | **ya** — persis peran mereka sekarang |

Perlu ditekankan: sebagian `source_ref` memang berdomain `idx.co.id`, tetapi berupa *New Listing Information*. Dokumen itu dapat menyebut sektor, namun tidak menetapkan kapan keanggotaan IDX-IC berlaku. Domain resmi tidak dengan sendirinya menjadikan sebuah rujukan sebagai publikasi klasifikasi.

### Yang dijalankan

```sql
UPDATE ticker_sector_memberships
   SET source_authority_class = 'DERIVED_REFERENCE',
       reason_code            = 'SECTOR_MEMBERSHIP_LEGACY_RECLASSED_DERIVED',
       operator_name          = 'md-session-20260810'
 WHERE source_authority_class IS NULL;
```

`971` baris diubah, di dalam transaksi. **Jumlah baris tetap `971`** — tidak ada yang dihapus atau ditambah; hanya keadaannya yang kini dinyatakan.

Snapshot pra-mutasi tersimpan di `outputs/idxic-legacy-supersede-20260810/snapshot_before.tsv`, SHA-256 `906af05aff2b7f9dad6fc7203d5ff927f9b021055fdacf18e73e590a706f3aa6`, sehingga perubahan ini reversibel. Reason code didaftarkan lebih dulu di registry dan seed.

### Apa yang berubah, dan apa yang tidak

**Berubah:** keadaan ke-971 baris kini **dinyatakan**, bukan absen. Sebelumnya `NULL` tidak dapat dibedakan dari "belum ada yang mencatat". Sekarang barisnya menyatakan dirinya sebagai bukti pendukung yang tidak pernah menetapkan — bentuk masalah yang sama dengan `NULL` versus `0` pada W15, W16, dan W20.

**Tidak berubah:** perilaku perencana overlap. `appendMembership()` memuat pembanding dengan `whereIn(AUTHORITATIVE_CLASSES)`, dan `DERIVED_REFERENCE` **juga** berada di luar daftar itu. Dry-run baseline yang dijalankan ulang sesudah penandaan menghasilkan keluaran **identik**: `714` baris, `714` diterima, `0` error.

Artinya konsekuensi struktural yang dicatat sebelumnya tetap berlaku: apply baseline akan menghasilkan `971 + 714 = 1.685` baris, dengan himpunan legacy tetap ada di sampingnya. Penandaan ini membuat himpunan tersebut **dapat dikenali**, bukan membuatnya hilang.

## Koreksi tanggal berlaku 190 baris, `2026-08-10`

### Cacat yang ditemukan

Perbandingan terhadap pengumuman resmi menyingkap `190` baris ber-`effective_from = 2021-01-25` yang **tidak ada di pengumuman peluncuran**. Seluruhnya bersumber `idx_stock_screener` — halaman yang menampilkan sektor hari ini dan tidak pernah menyatakan tanggal berlaku. Tanggalnya disimpulkan, bukan bersumber.

Pemeriksaan tanggal listing menjelaskan sebabnya sekaligus membuktikan tanggal itu keliru:

```
listed SEBELUM 2021-01-25 :   0
listed SESUDAH 2021-01-25 : 190
```

Ke-190 emiten itu IPO sesudah peluncuran IDX-IC. Mereka tidak ada di pengumuman karena belum tercatat — dan justru karena itu, sektornya tidak mungkin berlaku sejak `2021-01-25`. **81 di antaranya bahkan baru listing sesudah dataset start**, sehingga mengklaim sektor untuk periode ketika perusahaannya belum ada.

### Yang dijalankan

```sql
UPDATE ticker_sector_memberships m
  JOIN tickers t ON t.ticker_id = m.ticker_id
   SET m.effective_from = t.listed_date,
       m.reason_code    = 'SECTOR_MEMBERSHIP_EFFECTIVE_FROM_CORRECTED_TO_LISTING'
 WHERE m.source_name    = 'idx_stock_screener'
   AND m.effective_from = '2021-01-25'
   AND t.listed_date    > '2021-01-25';
```

`190` baris diperbaiki di dalam transaksi. Snapshot pra-mutasi: `outputs/idxic-effective-from-fix-20260810/snapshot_before.tsv`, SHA-256 `99634899035677e74d4a4e49b9242bc118967eb401c3cb156d90e1d384b08b52`.

Sebaran tahun berlaku sesudahnya: `2021` → `768`, `2022` → `57`, `2023` → `78`, `2024` → `41`, `2025` → `26`, `2026` → `1`. Total tetap `971`; tidak ada baris yang ditambah atau dihapus.

Verifikasi: **nol** baris tersisa yang mengklaim `2021-01-25` tanpa dasar.

### Batas koreksi ini

Yang diperbaiki adalah **tanggal mulai**, bukan nilai sektornya. Tanggal listing adalah fakta terdokumentasi IDX, sehingga awal interval kini bersumber. Sektornya sendiri tetap berasal dari screener — keadaan hari ini, bukan keadaan saat listing. Apakah sebuah emiten sempat direklasifikasi antara listing dan sekarang tetap tidak diketahui, dan karena itu barisnya tetap `DERIVED_REFERENCE`.

### Anomali pra-ada yang ditemukan saat verifikasi

Satu baris memiliki `effective_from` mendahului `listed_date`: **`GRPH`**, berlaku `2024-01-17` sementara listing `2024-01-18`. Sumbernya `idx`, bukan screener, sehingga **tidak tersentuh koreksi ini** — snapshot membuktikan cacatnya sudah ada sebelumnya. Tampaknya tanggal pengumuman dipakai sebagai tanggal berlaku, padahal klasifikasi berlaku saat emiten tercatat.

Dampak praktisnya nihil: pada `2024-01-17` emiten itu belum tercatat sehingga tidak ada bar. Dibiarkan apa adanya dan dicatat di sini, karena memperbaikinya menuntut keputusan terpisah mengenai baris bersumber pengumuman.

## Keadaan database saat ini

```
total membership                    971
source_authority_class terisi       971   ← DERIVED_REFERENCE sejak 2026-08-10
listing_id terisi                   971
recorded_at terisi                  971
effective_from = 2021-01-25         906
interval terbuka                    971
```

Resolver menyaring pada `['EXCHANGE_AUTHORITATIVE','OPERATOR_ENTERED']`, sehingga dengan `source_authority_class` NULL pada seluruh 971 baris, **tidak ada satu pun membership yang dapat meresolusi sebagai sektor otoritatif**. Itu perilaku fail-closed yang benar, bukan cacat.

Perlu dicatat bahwa `effective_from = 2021-01-25` pada 906 baris **bukan tanggal karangan** — ia tanggal efektif resmi dari pengumuman peluncuran. Baseline temporalnya sudah benar; yang belum ada adalah kelas otoritasnya.

## Yang harus diperoleh dari IDX

Empat dokumen, dengan kelas dan pola nomor yang sudah terbukti:

> *Pengumuman Perubahan Klasifikasi Industri Perusahaan Tercatat*, pola nomor **`Peng-XXXXX/BEI.POP/06-YYYY`**, diumumkan sekitar 24 Juni, efektif `2023-07-03`, `2024-07-01`, `2025-07-01`, dan `2026-07-01`

Tanpa keempatnya, sektor untuk seluruh jendela dataset bertumpu pada baseline berumur dua sampai lima tahun.

## Urutan yang sah setelah dokumen diperoleh

1. Rekam artefak beserta SHA-256, sebagaimana pola pada tabel di atas.
2. Jalankan impor `--dry-run` dan rekonsiliasi dua arah terhadap temporal universe pada tiap tanggal efektif.
3. Terapkan dengan `source_authority_class = EXCHANGE_AUTHORITATIVE` hanya untuk baris yang benar-benar bersumber pengumuman.
4. Tutup interval lama melalui `effective_to` dan `supersedes_membership_id`, bukan dengan menimpa baris.
5. Hitung ulang indikator sektor untuk periode terdampak.

## Batas kapabilitas

Berkas ini mencatat rekonsiliasi **himpunan ticker**, bukan rekonsiliasi **sektor per ticker**. Pasangan ticker↔sektor tidak diambil dari lampiran PDF karena ekstraksi teksnya tidak dapat diandalkan: sel tabelnya membungkus antar-baris secara independen, sehingga hanya `498` dari `824` baris ticker membawa kode sub-industri pada baris yang sama, dan pasangan yang tampak sejajar dapat salah secara sistematis.

Karena itu klaim yang berhak dibuat dari berkas ini adalah: **765 ticker pada baseline CSV seluruhnya hadir pada dokumen resmi IDX**. Bukan: sektor pada CSV cocok dengan sektor pada PDF. Yang kedua menuntut ekstraksi tabel yang benar dan belum dilakukan.
