# Legacy Semantic Extract — LX-MD-0034-GOV-01

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `GOVERNANCE`
- Source range: `L475-L501`
- Extract body SHA1: `756BDBFCB2BB50725C3B41223125E09ED9428FC0`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
