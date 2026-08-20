# Audit Reports README

Folder ini dipakai untuk menyimpan **output audit aktif** terhadap paket market-data yang sedang dibaca.

## Report state model
Audit report di paket aktif harus **state-only**, bukan **round-based** dan bukan **history-driven**.

Artinya:
- report audit harus menjelaskan **state paket saat ini**
- report audit tidak boleh dijadikan log revisi berantai (`R1`, `R2`, `R3`, dst.)
- report audit tidak boleh memaksa pembaca menyusun status akhir dari kumpulan report lama
- paket audit aktif tidak menyimpan history audit yang sudah lewat bila history itu tidak lagi dibutuhkan untuk menjalankan audit saat ini

## Canonical current-state rule
Untuk paket aktif, harus ada tepat satu report kanonik:
- `AUDIT_FINAL_STATE.md`

File itu adalah satu-satunya ringkasan status audit saat ini.

Current scope membedakan secara eksplisit:

- documentation strategy closure;
- implementation conformance;
- operational/production validation.

Kelulusan documentation strategy tidak boleh diturunkan hanya karena code belum dibangun, dan tidak boleh dipromosikan menjadi implementation/production claim tanpa audit serta executed evidence terpisah.

## What is allowed in this folder
Di paket aktif, isi folder ini harus seminimal mungkin:
- `README.md` sebagai aturan folder;
- `AUDIT_FINAL_STATE.md` sebagai satu-satunya **current-state verdict** kanonik;
- remediation report aktif **hanya jika** masih ada temuan PARTIAL / FAIL yang belum ditutup;
- dated implementation-evidence report boleh tetap ada hanya sebagai **non-verdict supporting evidence** bila masih direferensikan oleh audit aktif. File seperti itu wajib dibaca sebagai bukti historis/dated dan tidak boleh bersaing dengan `AUDIT_FINAL_STATE.md`.

## What is not allowed in the active package
Yang tidak boleh dijadikan pola di paket aktif:
- `AUDIT_FINAL_STATE_R1.md`
- `AUDIT_FINAL_STATE_R2.md`
- `AUDIT_FINAL_STATE_R3.md`
- `AUDIT_REMEDIATION_R1_*.md`
- folder `history/` untuk menyimpan jejak audit lama
- pointer README yang menunjuk file final-state berbasis revisi

## Minimum report contents
### Final-state report
- package identity
- dominant layer
- actual layer classification
- whether Layer C is genuinely active
- summary table PASS / PARTIAL / FAIL
- final verdict
- outstanding partial/fail items, if any

### Active remediation report
- exact files/folders changed
- issue still open
- target state
- concrete remediation action
- sync requirements

## Operational rule
Kalau semua PARTIAL / FAIL dokumentasi sudah selesai, remediation report dokumentasi aktif harus dihapus. Folder boleh tetap memuat `README.md`, satu current-state verdict `AUDIT_FINAL_STATE.md`, dan dated implementation-evidence report yang masih dibutuhkan sebagai supporting evidence; **tidak boleh ada current-state verdict kedua**.
