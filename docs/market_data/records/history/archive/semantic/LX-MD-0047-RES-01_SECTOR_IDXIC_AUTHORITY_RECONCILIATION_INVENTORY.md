# Legacy Semantic Extract — LX-MD-0047-RES-01

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `RESEARCH`
- Source range: `L41-L65`
- Extract body SHA1: `8DA540C4E2CC2F9F5F2321EA1F3118C4202D99F3`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
