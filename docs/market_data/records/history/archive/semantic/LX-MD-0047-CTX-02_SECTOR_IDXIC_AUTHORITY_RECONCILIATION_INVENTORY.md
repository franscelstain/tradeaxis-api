# Legacy Semantic Extract — LX-MD-0047-CTX-02

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `CONTEXT`
- Source range: `L66-L108`
- Extract body SHA1: `D41CCDFA333998B5611841988A5D7E6813BAF77A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
