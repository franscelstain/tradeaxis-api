# 📦 AUDIT UPDATE GOVERNANCE — FINAL HARDENED RULES (LUMEN AUDIT FILES)

Dokumen ini adalah **aturan final (HARD RULES)** untuk mengelola, menulis, memperbarui, dan memvalidasi dua file audit Lumen berikut:

- `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md`

Dokumen ini bersifat:

- **WAJIB diikuti**
- **ANTI-DRIFT**
- **ANTI-DUPLICATION**
- **ANTI-REPEAT SESSION**
- **ENFORCEABLE**
- **SOURCE OF TRUTH GOVERNANCE**

---

# 🎯 TUJUAN UTAMA

Menjadikan kedua file audit sebagai:

> **SOURCE OF TRUTH yang stabil, ringkas, konsisten, dan bisa ditelusuri lintas sesi tanpa kehilangan konteks**

Governance ini dibuat agar:

- setiap sesi tidak mengulang pembahasan lama
- setiap hasil pekerjaan tersimpan sebagai catatan yang cukup
- setiap contract dan implementasi tetap saling terhubung
- tidak ada duplicate entry untuk topik yang sama
- audit tetap readable walaupun proyek berjalan panjang
- perubahan penting bisa dilacak tanpa berubah menjadi log harian

---

# 1. 📌 PERAN FILE (TIDAK BOLEH TERTUKAR)

## 1.1 `LUMEN_IMPLEMENTATION_STATUS.md`

File ini berfungsi untuk mencatat:

> **APA yang sudah diimplementasikan, bagaimana behavior akhirnya, dan bukti validasinya**

WAJIB berisi:

- perubahan nyata di codebase
- behavior sistem yang sudah berjalan
- enforcement yang dilakukan
- hasil validasi
- hasil test
- constraint penting dari implementasi
- final behavior yang harus diingat sesi berikutnya

DILARANG berisi:

- definisi contract utama
- teori policy panjang
- catatan diskusi mentah
- duplicate contract wording dari tracker
- aktivitas harian tanpa dampak nyata

Fokus utama file ini:

> **implementation evidence, runtime behavior, dan hasil kerja nyata**

---

## 1.2 `LUMEN_CONTRACT_TRACKER.md`

File ini berfungsi untuk mencatat:

> **STATUS lifecycle dari setiap CONTRACT yang mengatur sistem**

WAJIB berisi:

- nama contract formal
- status lifecycle contract
- apakah contract sudah didefinisikan
- apakah sudah diimplementasikan
- apakah sudah enforced di runtime/code
- apakah sudah divalidasi melalui test/evidence
- apakah masih ada gap/blocker
- mapping ke implementation entry terkait

DILARANG berisi:

- detail teknis terlalu panjang
- log test detail
- catatan implementasi mentah
- duplicate isi `LUMEN_IMPLEMENTATION_STATUS.md`
- topik tanpa contract yang jelas

Fokus utama file ini:

> **contract lifecycle, enforcement status, dan traceability**

---

# 2. 📌 RELASI WAJIB ANTARA DUA FILE

Kedua file audit tidak boleh berdiri sendiri.

Setiap entry implementasi di `LUMEN_IMPLEMENTATION_STATUS.md` WAJIB punya contract terkait di `LUMEN_CONTRACT_TRACKER.md`.

Setiap contract di `LUMEN_CONTRACT_TRACKER.md` WAJIB punya implementasi nyata atau status gap yang jelas di `LUMEN_IMPLEMENTATION_STATUS.md`.

DILARANG:

- implementation tanpa contract
- contract tanpa implementasi/gap
- entry yang tidak bisa ditelusuri ke file pasangannya
- membuat status DONE hanya di satu file tetapi file lainnya tidak diperbarui

WAJIB:

- gunakan nama/topik yang konsisten
- sebutkan contract terkait di implementation entry
- sebutkan implementation terkait di contract entry

---

# 3. 📌 FORMAT PENULISAN WAJIB

## 3.1 Format umum entry

Semua entry utama WAJIB mengikuti format berikut:

- NAME → STATUS

  [LAST_UPDATED] YYYY-MM-DD

  [RELATED_CONTRACT] CONTRACT_NAME
  [RELATED_IMPLEMENTATION] Implementation Entry Name

  [HISTORY]
  - YYYY-MM-DD → PERUBAHAN SIGNIFIKAN
  - YYYY-MM-DD → PERUBAHAN SIGNIFIKAN

  [SECTION UTAMA SESUAI FILE]

---

# 4. 📌 FORMAT `LUMEN_IMPLEMENTATION_STATUS.md`

Gunakan format berikut:

- Feature / Enforcement Name → STATUS

  [LAST_UPDATED] YYYY-MM-DD

  [RELATED_CONTRACT] CONTRACT_NAME

  [HISTORY]
  - YYYY-MM-DD → perubahan penting saja, bukan aktivitas harian

  [IMPLEMENTATION] ringkasan perubahan nyata di codebase

  [ENFORCEMENT] bagaimana sistem dipaksa mengikuti rule/contract

  [FINAL_BEHAVIOR] behavior akhir sistem setelah pekerjaan selesai

  [EVIDENCE] hasil validasi, test, assertion, command output, atau static proof

  [NEXT_ACTION] hanya diisi jika status belum DONE/LOCKED

---

## 4.1 Contoh `LUMEN_IMPLEMENTATION_STATUS.md`

- Finalize / Lock / Pointer Determinism → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] FINALIZE_LOCK_POINTER_DETERMINISM_CONTRACT

  [HISTORY]
  - 2026-04-27 → Initial finalize guard implemented
  - 2026-04-29 → Lock-conflict mutation blocked
  - 2026-05-01 → Final validation completed and behavior marked DONE

  [IMPLEMENTATION] Finalize flow now prevents unsafe mutation during lock conflict and validates pointer publication before updating current readable state.

  [ENFORCEMENT] Pointer update only occurs after valid publication state is proven. Re-run protection prevents invalid finalize mutation.

  [FINAL_BEHAVIOR] Finalize is deterministic, idempotent, and does not corrupt current pointer when lock conflict or invalid publication state occurs.

  [EVIDENCE] PHPUnit: 51 tests / 1173 assertions PASS.

---

# 5. 📌 FORMAT `LUMEN_CONTRACT_TRACKER.md`

Gunakan format berikut:

- CONTRACT_NAME_CONTRACT → STATUS

  [LAST_UPDATED] YYYY-MM-DD

  [RELATED_IMPLEMENTATION] Implementation Entry Name

  [HISTORY]
  - YYYY-MM-DD → perubahan penting lifecycle contract

  [DEFINED] lokasi/ruang lingkup contract

  [IMPLEMENTED] lokasi implementasi utama di codebase

  [ENFORCED] mekanisme enforcement di runtime/code/test

  [VALIDATED] bukti validasi

  [FINAL_RULE] aturan final yang tidak boleh dilanggar

  [NEXT_ACTION] hanya diisi jika status belum DONE/LOCKED

---

## 5.1 Contoh `LUMEN_CONTRACT_TRACKER.md`

- FINALIZE_LOCK_POINTER_DETERMINISM_CONTRACT → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] Finalize / Lock / Pointer Determinism

  [HISTORY]
  - 2026-04-27 → Contract behavior implemented partially
  - 2026-04-29 → Runtime enforcement added
  - 2026-05-01 → Contract validated and locked

  [DEFINED] Contract defines deterministic finalize, pointer safety, idempotent re-run behavior, and lock-conflict protection.

  [IMPLEMENTED] Enforced in MarketDataPipelineService and related publication repository flow.

  [ENFORCED] Unsafe pointer mutation is blocked unless publication state is valid and readable.

  [VALIDATED] Covered by finalize, pointer, and static guard tests.

  [FINAL_RULE] Current pointer must never be updated by unsafe, invalid, failed, held, or lock-conflicted finalize flow.

---

# 6. 📌 CONTRACT NAMING RULE

Nama contract di `LUMEN_CONTRACT_TRACKER.md` WAJIB:

- uppercase
- menggunakan underscore
- diakhiri dengan `_CONTRACT`
- mewakili satu concern utama
- stabil lintas sesi

Contoh benar:

- `PUBLISHABILITY_STATE_INTEGRITY_CONTRACT`
- `FINALIZE_LOCK_POINTER_DETERMINISM_CONTRACT`
- `COVERAGE_GATE_ENFORCEMENT_CONTRACT`
- `PUBLICATION_POINTER_READINESS_CONTRACT`

Contoh salah:

- Publishability State Integrity
- Publishability Fix
- Publishability v2
- Pointer Update Improvement
- Coverage Gate New Logic

DILARANG membuat nama baru jika masih satu lingkup dengan contract lama.

Jika ada perkembangan baru dalam lingkup contract yang sama, update entry lama dan tambahkan HISTORY.

---

# 7. 📌 STATUS LIFECYCLE

Gunakan hanya status berikut:

- DRAFT
- IN_PROGRESS
- ENFORCED
- VERIFIED
- DONE
- LOCKED

DILARANG menggunakan status bebas di luar daftar ini.

---

## 7.1 Arti status

### DRAFT

Digunakan jika contract atau implementasi baru mulai dirumuskan, tetapi belum ada enforcement nyata.

### IN_PROGRESS

Digunakan jika sedang dikerjakan dan belum selesai.

### ENFORCED

Digunakan jika rule sudah masuk ke code/runtime, tetapi validasi penuh belum selesai.

### VERIFIED

Digunakan jika sudah tervalidasi melalui test/static proof/manual evidence, tetapi belum dinyatakan final.

### DONE

Digunakan jika implementasi sudah selesai dan behavior final sudah cukup dicatat.

### LOCKED

Digunakan jika contract sudah final dan tidak boleh diubah sembarangan tanpa sesi policy/contract update khusus.

---

## 7.2 Larangan status

DILARANG:

- langsung lompat ke DONE tanpa evidence
- menandai LOCKED tanpa final rule
- menandai DONE jika masih ada GAP/BLOCKER
- menggunakan DONE untuk pekerjaan yang baru partial
- membuat entry baru hanya karena status belum selesai

Jika status masih PARTIAL, gunakan `IN_PROGRESS`, bukan membuat nomor/topik baru.

---

# 8. 📌 HISTORY SYSTEM (PENGGANTI VERSIONING)

## 8.1 Prinsip

Governance ini TIDAK menggunakan versioning seperti:

- v1
- v2
- v3
- rev-1
- rev-2

Yang digunakan adalah:

> **HISTORY dalam satu entry**

Tujuannya:

- tetap bisa tahu kapan perubahan penting terjadi
- tidak membuat audit menjadi full log harian
- tidak membuat entry baru untuk topik yang sama
- tidak membuat audit membengkak tanpa arah

---

## 8.2 Aturan HISTORY

HISTORY WAJIB mencatat hanya perubahan signifikan, seperti:

- perubahan behavior sistem
- enforcement baru
- bug fix penting
- perubahan status lifecycle
- validasi final
- contract locked
- gap ditemukan
- gap diselesaikan
- final behavior berubah

HISTORY DILARANG mencatat:

- aktivitas harian biasa
- minor wording update
- perubahan kecil tanpa dampak
- “lanjut cek”
- “rapikan sedikit”
- “belum selesai hari ini”
- catatan progress mentah

---

## 8.3 Contoh HISTORY benar

- 2026-04-25 → Initial implementation added, status IN_PROGRESS
- 2026-04-28 → Runtime enforcement added, status ENFORCED
- 2026-04-30 → Full validation passed, status DONE
- 2026-05-01 → Final rule locked, status LOCKED

---

## 8.4 Contoh HISTORY salah

- 2026-04-29 → minor update
- 2026-04-30 → lanjut cek
- 2026-05-01 → edit wording
- 2026-05-02 → masih dibahas

Ini salah karena berubah menjadi jurnal harian, bukan audit.

---

# 9. 📌 LAST_UPDATED RULE

Setiap entry WAJIB memiliki:

`[LAST_UPDATED] YYYY-MM-DD`

LAST_UPDATED hanya berubah jika:

- ada perubahan signifikan
- HISTORY bertambah
- status berubah
- final behavior berubah
- gap ditambahkan/diselesaikan
- evidence penting diperbarui

DILARANG mengubah LAST_UPDATED hanya karena edit kecil tanpa dampak.

---

# 10. 📌 ACTIVE SESSION RULE

Setiap file audit WAJIB memiliki bagian:

## ACTIVE SESSION

Format:

ACTIVE SESSION:
- <nama sesi aktif>

[SESSION_STATUS] ACTIVE / COMPLETED

[SESSION_SCOPE]
- ruang lingkup sesi saat ini

[SESSION_GOAL]
- tujuan utama sesi saat ini

[SESSION_NOTES]
- catatan penting jika ada

---

## 10.1 Aturan ACTIVE SESSION

WAJIB:

- hanya ada satu ACTIVE SESSION
- setiap awal sesi menulis sesi yang sedang atau akan dikerjakan
- setiap pindah sesi update ACTIVE SESSION
- setiap sesi selesai ubah `[SESSION_STATUS]` menjadi `COMPLETED`
- hasil sesi yang selesai harus dipindahkan ke entry terkait

DILARANG:

- membiarkan active session lama menggantung
- membuka sesi baru tanpa menutup sesi sebelumnya
- membahas topik baru tanpa memperbarui ACTIVE SESSION
- memakai ACTIVE SESSION sebagai log harian

---

## 10.2 Contoh ACTIVE SESSION

ACTIVE SESSION:
- Coverage Edge Cases Hardening

[SESSION_STATUS] ACTIVE

[SESSION_SCOPE]
- Multi-source fallback
- Delayed provider data
- Retry window
- Coverage decision consistency

[SESSION_GOAL]
- Harden coverage edge behavior without changing locked pointer contract.

[SESSION_NOTES]
- Existing coverage gate entry must be updated. Do not create duplicate coverage entry.

---

# 11. 📌 SESSION TRANSITION RULE

Saat mulai sesi baru:

WAJIB:

1. cek ACTIVE SESSION lama
2. jika masih ACTIVE, tentukan apakah:
   - dilanjutkan
   - ditutup sebagai COMPLETED
   - ditandai memiliki GAP
3. update ACTIVE SESSION
4. baru lanjut pekerjaan

Saat melanjutkan sesi lama:

WAJIB:

- gunakan entry yang sama
- update HISTORY jika ada perubahan signifikan
- jangan buat entry baru

Saat sesi selesai:

WAJIB:

- ubah `[SESSION_STATUS]` menjadi `COMPLETED`
- update `LUMEN_IMPLEMENTATION_STATUS.md`
- update `LUMEN_CONTRACT_TRACKER.md`
- tulis hasil final yang penting
- hapus/bersihkan catatan sementara yang tidak perlu

---

# 12. 📌 ANTI-DUPLICATION RULE

DILARANG:

- membuat entry baru untuk topik yang sama
- membuat variasi nama dari topik lama
- membuat nomor baru hanya karena sesi baru
- memecah satu concern menjadi beberapa entry kecil
- menulis ulang hal sama dengan istilah berbeda

WAJIB:

- cari entry lama terlebih dahulu
- update entry lama jika masih satu scope
- tambahkan HISTORY jika ada perubahan signifikan
- hanya buat entry baru jika benar-benar concern baru

---

## 12.1 Contoh salah

- Coverage Gate Enforcement
- Coverage Evaluation Logic
- Coverage Final Decision
- Coverage Runtime Guard

Jika semuanya masih membahas coverage gate, maka ini salah.

---

## 12.2 Contoh benar

- Coverage Gate Enforcement → satu entry, terus dikembangkan

---

# 13. 📌 ATURAN MEMBUAT ENTRY BARU

Entry baru hanya boleh dibuat jika:

- concern benar-benar baru
- tidak ada entry lama yang relevan
- contract baru memang dibutuhkan
- scope berbeda dari contract yang sudah ada
- tidak bisa digabung tanpa membuat entry lama ambigu

Sebelum membuat entry baru WAJIB cek:

- apakah sudah ada entry mirip?
- apakah ini hanya extension dari contract lama?
- apakah ini hanya sesi lanjutan?
- apakah ini hanya bug fix dari behavior lama?
- apakah ini cukup ditambahkan ke HISTORY entry lama?

Jika jawabannya masih satu lingkup, DILARANG membuat entry baru.

---

# 14. 📌 UPDATE RULE SETIAP SESI

Setiap sesi yang mengubah code, docs, test, policy, atau contract WAJIB memperbarui dua file audit.

WAJIB update:

- ACTIVE SESSION
- STATUS jika berubah
- LAST_UPDATED jika ada perubahan signifikan
- HISTORY jika ada perubahan penting
- IMPLEMENTATION / ENFORCEMENT / EVIDENCE
- DEFINED / IMPLEMENTED / ENFORCED / VALIDATED
- FINAL_BEHAVIOR atau FINAL_RULE jika sudah DONE/LOCKED
- GAP / IMPACT / NEXT_ACTION jika belum selesai

DILARANG:

- copy paste isi lama tanpa update nyata
- hanya menambah test count tanpa menjelaskan behavior
- hanya menulis DONE tanpa evidence
- hanya update satu file audit
- mengubah code tetapi audit tidak berubah
- mengubah contract tetapi implementation status tidak berubah

---

# 15. 📌 GAP / PROBLEM HANDLING

Jika ada masalah, audit WAJIB jujur.

Gunakan format:

[GAP] deskripsi masalah

[IMPACT] dampak ke sistem

[NEXT_ACTION] langkah berikutnya

[STATUS] tetap IN_PROGRESS / ENFORCED / VERIFIED sesuai kondisi nyata

---

## 15.1 Aturan GAP

WAJIB menulis GAP jika:

- behavior belum sesuai contract
- test belum cukup
- code belum enforce rule
- docs dan code berbeda
- contract belum lengkap
- runtime behavior belum terbukti
- ada regression
- ada blocker

DILARANG:

- menyembunyikan GAP
- menandai DONE jika GAP masih terbuka
- membuat entry baru untuk menghindari GAP lama
- menghapus GAP tanpa evidence

---

# 16. 📌 FINALIZATION RULE SAAT DONE

Saat suatu sesi atau entry dianggap DONE:

WAJIB menambahkan:

- hasil final implementasi
- final behavior sistem
- constraint penting
- rule yang tidak boleh dilanggar
- evidence terakhir
- test result penting
- catatan agar sesi berikutnya tidak mengulang pembahasan

DONE bukan hanya status.

DONE harus berarti:

> informasi penting sudah cukup tersimpan untuk sesi berikutnya.

---

## 16.1 Format final untuk IMPLEMENTATION STATUS

[FINAL_BEHAVIOR]
- behavior akhir yang berlaku

[FINAL_CONSTRAINT]
- batasan yang tidak boleh dilanggar

[EVIDENCE]
- bukti akhir

---

## 16.2 Format final untuk CONTRACT TRACKER

[FINAL_RULE]
- rule final contract

[VALIDATED]
- bukti validasi

[LOCK_CONDITION]
- kapan contract dianggap tidak boleh diubah sembarangan

---

# 17. 📌 LOCKED RULE

Status LOCKED hanya boleh digunakan jika:

- contract sudah jelas
- implementasi sudah enforce
- test/evidence sudah cukup
- final rule sudah ditulis
- tidak ada GAP terbuka
- behavior tidak boleh berubah tanpa sesi contract/policy khusus

Jika contract LOCKED perlu diubah:

WAJIB:

- tulis alasan perubahan
- tulis impact
- tulis migration/compatibility concern jika ada
- update HISTORY
- jangan hapus history lama

DILARANG:

- mengubah contract LOCKED diam-diam
- mengganti rule final tanpa mencatat alasan
- membuat contract baru untuk menggantikan contract lama tanpa recovery/merge

---

# 18. 📌 ENTRY SIZE CONTROL

Jika entry menjadi terlalu panjang:

WAJIB:

- tetap pertahankan satu entry
- ringkas HISTORY lama
- pertahankan perubahan penting
- hapus noise
- jangan pecah menjadi entry baru

DILARANG:

- memecah entry hanya karena panjang
- menghapus final rule penting
- menghapus evidence final
- menghapus history perubahan status penting

---

## 18.1 Format ringkasan HISTORY panjang

[HISTORY]
- 2026-04-20 to 2026-04-27 → Initial implementation and enforcement hardening completed
- 2026-04-29 → Runtime validation added
- 2026-05-01 → Final behavior validated and marked DONE

---

# 19. 📌 AUDIT CORRUPTION RECOVERY

Jika audit sudah tidak konsisten, duplicate, atau rusak formatnya:

WAJIB lakukan recovery:

1. identifikasi duplicate entry
2. pilih satu entry utama
3. gabungkan informasi penting ke entry utama
4. pindahkan tanggal penting ke HISTORY
5. hapus entry duplikat
6. perbaiki status lifecycle
7. perbaiki mapping implementation ↔ contract
8. update LAST_UPDATED
9. tandai recovery di HISTORY

DILARANG:

- membiarkan duplicate tetap ada
- membuat entry baru untuk recovery
- menghapus informasi penting tanpa dipindahkan
- menganggap audit valid jika format rusak

---

## 19.1 Contoh recovery history

[HISTORY]
- 2026-05-01 → Audit duplicate entries merged into this canonical entry; old duplicate coverage entries removed.

---

# 20. 📌 ENFORCEMENT HARD STOP

Jika aturan governance ini dilanggar:

- update audit dianggap INVALID
- sesi tidak boleh dianggap DONE
- pekerjaan berikutnya tidak boleh dilanjutkan sebagai final
- audit harus diperbaiki terlebih dahulu

Pelanggaran termasuk:

- duplicate entry
- missing ACTIVE SESSION
- missing LAST_UPDATED
- missing HISTORY untuk perubahan penting
- missing contract mapping
- DONE tanpa evidence
- LOCKED tanpa final rule
- GAP disembunyikan
- update hanya satu file audit
- membuat nomor/topik baru untuk scope lama

---

# 21. 📌 VALIDATION CHECKLIST SEBELUM UPDATE AUDIT

Sebelum menyimpan update audit, WAJIB cek:

- apakah ACTIVE SESSION sudah benar?
- apakah SESSION_STATUS sudah sesuai?
- apakah entry lama sudah dicek sebelum membuat entry baru?
- apakah tidak ada duplicate entry?
- apakah topik ini masih satu scope dengan entry lama?
- apakah LAST_UPDATED diperbarui hanya jika perlu?
- apakah HISTORY hanya berisi perubahan penting?
- apakah STATUS sesuai kondisi nyata?
- apakah DONE punya evidence?
- apakah LOCKED punya final rule?
- apakah GAP ditulis jika ada?
- apakah implementation punya related contract?
- apakah contract punya related implementation?
- apakah final behavior/rule cukup untuk sesi berikutnya?
- apakah isi tidak berubah menjadi log harian?

Jika salah satu gagal:

> AUDIT UPDATE INVALID dan harus diperbaiki sebelum lanjut.

---

# 22. 📌 ATURAN PENULISAN EVIDENCE

Evidence harus spesifik.

Evidence boleh berupa:

- PHPUnit command result
- jumlah tests/assertions
- static trace
- php -l
- artisan output
- manual command result dari user
- file/path yang berubah
- runtime behavior yang terbukti

DILARANG menulis evidence kosong seperti:

- tested
- validated
- seems ok
- should work
- already checked

Contoh benar:

[EVIDENCE] PHPUnit `tests/Unit/MarketData --filter pointer`: 47 tests / 533 assertions PASS.

Contoh salah:

[EVIDENCE] sudah dites.

---

# 23. 📌 ATURAN JIKA TEST BELUM BISA DIJALANKAN

Jika test belum bisa dijalankan oleh ChatGPT atau environment tidak lengkap:

WAJIB tulis jujur:

- test belum dijalankan
- validasi yang dilakukan hanya static/code trace
- manual test command yang harus dijalankan user
- expected result

DILARANG:

- mengklaim PHPUnit/artisan sudah dijalankan jika tidak dijalankan
- menulis PASS tanpa bukti
- menandai DONE hanya berdasarkan asumsi

---

# 24. 📌 ATURAN AGAR SESI BERIKUTNYA TIDAK MENGULANG PEMBAHASAN

Saat sesi selesai, entry final WAJIB menjawab:

- apa yang sudah diputuskan?
- apa behavior finalnya?
- apa yang tidak boleh diubah?
- apa evidence-nya?
- apa gap yang masih tersisa?
- apa next action jika belum selesai?

Tujuannya:

> sesi berikutnya bisa langsung lanjut tanpa membahas ulang dari nol.

---

# 25. 📌 PENEGASAN PERBEDAAN AUDIT VS LOG

Audit bukan log harian.

Audit hanya menyimpan:

- keputusan penting
- perubahan penting
- final behavior
- contract lifecycle
- evidence penting
- gap penting
- next action penting

Audit tidak menyimpan:

- aktivitas kecil harian
- catatan proses mentah
- percobaan yang tidak berdampak
- wording change kecil
- progress tanpa keputusan

---

# 26. 📌 PENEGASAN AKHIR

Jika governance ini diikuti:

- audit stabil
- sesi saling terhubung
- duplicate dapat dicegah
- pembahasan lama tidak diulang
- contract dan implementasi tetap sinkron
- sistem lebih mudah dirawat jangka panjang

Jika governance ini dilanggar:

- audit akan drift
- entry akan duplikat
- contract akan ambigu
- sesi akan berulang
- bug lama akan muncul kembali
- status DONE/LOCKED menjadi tidak bisa dipercaya

---

# 27. 📌 CROSS-SESSION CONTINUITY & REGRESSION RULE

Setiap sesi baru WAJIB menjaga hasil sesi sebelumnya yang sudah berstatus DONE atau LOCKED.

Prinsip utama:

> Sesi baru tidak boleh membuat entry lama yang sudah DONE/LOCKED menjadi rusak, tidak valid, atau bertentangan.

---

## 27.1 Aturan utama

Sebelum sesi baru dianggap DONE, WAJIB cek apakah perubahan sesi ini berdampak pada:

- contract lama
- implementation lama
- final behavior lama
- final rule lama
- test/evidence lama
- runtime behavior yang sudah pernah dinyatakan DONE/LOCKED

Jika ada dampak, maka sesi baru belum boleh dianggap DONE sampai dampak tersebut dibereskan.

---

## 27.2 Jika perubahan sesi ini merusak hasil sesi sebelumnya

Jika perubahan sesi ini membuat sesi sebelumnya tidak lagi valid, maka WAJIB:

1. identifikasi entry lama yang terdampak
2. update entry lama di `LUMEN_IMPLEMENTATION_STATUS.md`
3. update contract terkait di `LUMEN_CONTRACT_TRACKER.md`
4. tambahkan HISTORY pada entry lama
5. jelaskan impact perubahan baru terhadap behavior lama
6. sesuaikan implementation agar behavior lama tetap valid
7. jalankan/siapkan evidence ulang untuk memastikan sesi lama dan sesi baru sama-sama valid

DILARANG:

- membiarkan entry lama tetap DONE jika behavior-nya sudah rusak
- menandai sesi baru DONE jika menyebabkan sesi lama gagal
- membuat entry baru untuk menutupi regression dari entry lama
- menghapus history lama tanpa mencatat impact perubahan

---

## 27.3 Status entry lama jika terdampak

Jika entry lama terdampak tetapi berhasil disesuaikan dan kembali valid:

- status lama boleh tetap DONE / LOCKED
- HISTORY wajib ditambahkan
- evidence baru wajib dicatat

Contoh:

[HISTORY]
- 2026-05-01 → Adjusted due to new coverage/session changes; previous final behavior revalidated and remains DONE.

Jika entry lama terdampak dan belum berhasil disesuaikan:

- status tidak boleh tetap DONE tanpa catatan
- WAJIB tambahkan GAP
- status harus disesuaikan menjadi IN_PROGRESS / ENFORCED / VERIFIED sesuai kondisi nyata

---

## 27.4 DONE baru harus kompatibel dengan DONE lama

Sesi baru hanya boleh dinyatakan DONE jika:

- behavior baru valid
- behavior lama yang terkait tetap valid
- contract lama tidak dilanggar
- tidak ada regression terbuka
- evidence lama atau evidence pengganti sudah cukup
- dua file audit sudah diperbarui

Dengan kata lain:

> DONE sesi baru harus mempertahankan DONE sesi sebelumnya.

---

# 28. 📌 REGRESSION IMPACT CHECKLIST

Sebelum menandai sesi sebagai DONE, WAJIB cek:

- apakah perubahan ini menyentuh flow yang pernah DONE?
- apakah ada contract lama yang berubah efeknya?
- apakah ada final rule lama yang sekarang dilanggar?
- apakah ada test lama yang perlu disesuaikan?
- apakah evidence lama masih valid?
- apakah entry lama perlu HISTORY baru?
- apakah status lama tetap pantas DONE/LOCKED?
- apakah sesi baru dan sesi lama bisa sama-sama valid?

Jika ada satu saja jawaban bermasalah:

> sesi baru BELUM BOLEH DONE.

---

# 29. 📌 BACKWARD COMPATIBILITY RULE

Setiap perubahan baru WAJIB menjaga backward compatibility terhadap contract dan behavior lama yang sudah DONE/LOCKED.

Jika backward compatibility tidak bisa dijaga, maka perubahan tersebut WAJIB diperlakukan sebagai:

- contract change
- policy change
- breaking change

Breaking change tidak boleh dilakukan diam-diam.

WAJIB:

- tulis alasan perubahan
- tulis impact terhadap entry lama
- update contract lama
- update implementation lama
- update HISTORY
- update evidence
- pastikan final state baru tetap konsisten

DILARANG:

- mengubah behavior lama tanpa mencatatnya
- menghapus rule lama tanpa alasan
- menganggap perubahan baru valid jika merusak contract lama

---

# 30. 📌 DONE RECONCILIATION RULE

Jika sesi baru mengubah area yang pernah DONE, maka wajib dilakukan reconciliation.

Reconciliation berarti:

- membandingkan final behavior lama dengan behavior baru
- memastikan tidak ada konflik
- menyesuaikan code/test/docs bila perlu
- memperbarui audit lama dan audit baru
- memastikan semua status akhir tetap valid

Format catatan reconciliation:

[RECONCILIATION]
- Previous DONE behavior reviewed
- New change impact checked
- Affected old entry updated if needed
- Existing DONE/LOCKED contract remains valid
- New session can be marked DONE

DILARANG menandai sesi baru DONE tanpa reconciliation jika menyentuh area yang sudah pernah DONE.

---

# 🧠 RINGKASAN INTI

- satu topik = satu entry
- satu contract = satu canonical tracker entry
- tidak ada versioning formal
- gunakan HISTORY dalam satu entry
- HISTORY bukan log harian
- ACTIVE SESSION wajib ada
- SESSION_STATUS wajib jelas
- setiap update harus mengubah informasi penting
- implementation dan contract harus saling terhubung
- DONE harus menyimpan final behavior
- LOCKED harus menyimpan final rule
- GAP tidak boleh disembunyikan
- evidence harus spesifik
- duplicate entry membuat audit INVALID
- pelanggaran governance adalah HARD STOP