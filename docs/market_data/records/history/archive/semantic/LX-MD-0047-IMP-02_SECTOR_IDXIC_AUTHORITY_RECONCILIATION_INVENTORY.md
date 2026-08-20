# Legacy Semantic Extract — LX-MD-0047-IMP-02

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `IMPLEMENTATION`
- Source range: `L223-L260`
- Extract body SHA1: `A21ED90E1E3BA4D8B285C42E401777313EBA11BE`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
