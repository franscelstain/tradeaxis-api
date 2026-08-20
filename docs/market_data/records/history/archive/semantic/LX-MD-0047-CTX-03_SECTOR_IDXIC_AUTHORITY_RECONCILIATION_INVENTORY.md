# Legacy Semantic Extract — LX-MD-0047-CTX-03

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `CONTEXT`
- Source range: `L154-L191`
- Extract body SHA1: `0043E33ABF30C0315AF72404EF5AEE26A9DA3907`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
