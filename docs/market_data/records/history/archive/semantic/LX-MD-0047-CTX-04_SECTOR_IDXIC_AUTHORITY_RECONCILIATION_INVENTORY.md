# Legacy Semantic Extract — LX-MD-0047-CTX-04

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `CONTEXT`
- Source range: `L261-L339`
- Extract body SHA1: `26B10C765C19284A92D215628CAE3A437FB335D5`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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

<!-- LEGACY_EXTRACT_BODY_END -->
