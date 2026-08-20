# Legacy Semantic Extract — LX-MD-0047-CTX-01

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `CONTEXT`
- Source range: `L5-L40`
- Extract body SHA1: `2F8C7B3FC1F64391AC7C2F62809E282B0F8A7BBD`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
