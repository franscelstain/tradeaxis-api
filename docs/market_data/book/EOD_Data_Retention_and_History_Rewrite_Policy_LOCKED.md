# EOD DATA RETENTION AND HISTORY REWRITE POLICY (LOCKED)

## Purpose
Mengunci kebijakan penyimpanan jangka panjang, purge, recomputation, correction scope, dan baseline partitioning.

---

## Retention classes (LOCKED)

### Class A — Authoritative long-term records
Data berikut adalah authoritative dan disimpan **tanpa TTL purge operasional biasa**:
- canonical `eod_bars`
- `eod_indicators`
- `eod_eligibility`
- `eod_runs`
- `eod_publications`
- current publication pointer
- correction request / approval / publication trail
- hash / seal metadata yang menjadi bagian dari publication proof

### Class B — Required audit evidence
Evidence run/correction/replay minimum harus disimpan minimal **180 hari** sejak execution selesai.
Evidence yang menjadi referensi correction aktif, insiden aktif, atau baseline pembuktian release tidak boleh dipurge walaupun melewati 180 hari.

### Class C — Supplemental / operator convenience artifacts
Artifact pendukung yang bukan authoritative record dan bukan minimum audit evidence boleh dipurge setelah **30 hari**.
Contoh: temporary local exports, transient helper snapshots, operator convenience dumps.

---

## Rewrite policy (LOCKED)
- silent history rewrite dilarang
- provider correction allowed only with new run_id + notes + recompute + new hashes + reseal/supersession trail bila consumer-visible content berubah
- overwrite in-place terhadap published readable state dilarang

---

## Re-computation policy (LOCKED)
Indicator boleh dihitung ulang hanya dalam dua kasus:
1. bagian dari promote normal untuk requested date yang belum readable
2. bagian dari correction/reseal flow resmi untuk trade date yang sedang dikoreksi

Yang dilarang:
- recompute ad hoc yang menimpa publication readable lama tanpa correction trail
- recompute lama hanya demi merapikan data tanpa run context baru

---

## Correction impact rule (LOCKED)
Jika satu trade date D dikoreksi:
- koreksi minimum selalu **isolated ke date D terlebih dahulu**
- requested date lain tidak boleh ikut berubah diam-diam
- date setelah D hanya boleh ikut dihitung ulang bila kontrak artifact memang punya dependency eksplisit terhadap D dan rerun tanggal tersebut dijalankan secara resmi

Pada baseline EOD ini, bars/indicators/eligibility/publication adalah **per trade date** sehingga correction default dianggap **isolated per date**.
Tidak ada cascading rewrite otomatis lintas tanggal.

---

## Partitioning baseline (LOCKED)
Guideline minimal physical storage:
- partisi/default sharding operasional utamanya **by trade_date**
- ticker adalah secondary access dimension, bukan baseline partition owner
- maintenance/purge/archive tidak boleh memutus traceability publication per trade date

---

## Evidence purge guardrail
Purge artifact tidak boleh menghapus:
- current publication proof yang masih diperlukan audit
- correction comparison evidence untuk publication yang masih aktif atau masih menjadi referensi insiden
- evidence minimum yang masih berada dalam retention window 180 hari

---

## Anti-ambiguity rules
Tidak boleh:
- menyamakan evidence purge dengan boleh hapus authoritative state
- melakukan override data lama secara diam-diam
- membiarkan retention policy tergantung selera implementasi
- melakukan cascade correction lintas tanggal tanpa run resmi per tanggal yang terdampak

---

## Cross-contract alignment
Harus sinkron dengan:
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `Audit_Hash_and_Reproducibility_Contract_LOCKED.md`
- `CONSUMER_READ_CONTRACT_LOCKED.md`
