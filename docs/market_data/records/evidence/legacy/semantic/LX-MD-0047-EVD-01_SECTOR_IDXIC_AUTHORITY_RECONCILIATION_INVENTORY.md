# Legacy Semantic Extract — LX-MD-0047-EVD-01

- Source ID: `LS-MD-0047`
- Original path: `audit/SECTOR_IDXIC_AUTHORITY_RECONCILIATION_INVENTORY.md`
- Original SHA1: `405198AE0F209FD905AFE44C54D94E86B5512783`
- Extract role: `EVIDENCE`
- Source range: `L192-L222`
- Extract body SHA1: `B4B02F541458F067CDC3DEFA9A40A3CBAD257181`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
