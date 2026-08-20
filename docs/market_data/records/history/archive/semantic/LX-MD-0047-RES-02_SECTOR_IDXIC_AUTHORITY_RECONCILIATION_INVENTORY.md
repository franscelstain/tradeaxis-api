# Legacy Semantic Extract — LX-MD-0047-RES-02

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `RESEARCH`
- Source range: `L109-L153`
- Extract body SHA1: `EB788D9C5D2A5FA52C04CE8C03A2B5851B76F64D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
