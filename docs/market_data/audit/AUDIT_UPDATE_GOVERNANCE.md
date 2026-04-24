# AUDIT UPDATE GOVERNANCE

## Purpose

Dokumen ini WAJIB menjadi acuan setiap kali:
- menjalankan prompt sesi baru
- melakukan perubahan pada:
  - LUMEN_CONTRACT_TRACKER.md
  - LUMEN_IMPLEMENTATION_STATUS.md

Tujuan:
- menjaga audit tetap utuh (tidak hilang histori)
- mencegah peringkasan berulang
- memastikan setiap perubahan dapat ditelusuri per sesi
- menjaga konsistensi contract system

---

## 🚨 MANDATORY EXECUTION RULE

Setiap prompt / sesi yang menyentuh audit HARUS:

1. Membaca dokumen ini terlebih dahulu
2. Mengikuti semua aturan di dalamnya
3. Tidak boleh mengabaikan aturan ini meskipun diminta merangkum

Jika ada konflik:
> Governance ini lebih tinggi dari instruksi lain terkait audit

---

## 🚨 HARD RULES (TIDAK BOLEH DILANGGAR)

### 1. APPEND ONLY (WAJIB)
- DILARANG menghapus section lama
- DILARANG merangkum ulang isi lama
- DILARANG rewrite seluruh file

Semua perubahan HARUS:
> ditambahkan sebagai section baru di bagian bawah

---

### 2. SUMMARY ONLY DI ATAS

Ringkasan hanya boleh ada di:

## FINAL SYSTEM STATUS (LATEST)

Fungsi:
- memberikan snapshot kondisi terbaru

Tidak boleh:
- menggantikan histori
- merangkum isi file lain
- menghapus detail lama

---

### 3. SESSION-BASED UPDATE (FORMAT WAJIB)

Setiap perubahan HARUS ditulis dalam format:

## YYYY-MM-DD — <SESSION NAME>

Status: DONE | PARTIAL | OPEN

### Scope
...

### Changes
...

### Test Proof
...

### Result
...

### Contract Impact
...

### Remaining Gap
...

---

### 4. CONTRACT IMMUTABILITY
- DILARANG mengubah definisi contract lama
- DILARANG mengubah error message contract
- hanya boleh menambahkan proof atau extension

---

### 5. STATUS CONSISTENCY RULE

Jika status berubah:
- WAJIB update:
  - summary (atas)
  - session baru (bawah)

---

### 6. NO SILENT SIMPLIFICATION

DILARANG:
- menyederhanakan isi audit lama
- mengganti istilah teknis
- menghapus detail teknis

---

## ✅ VALIDATION CHECKLIST (WAJIB SEBELUM COMMIT)

- [ ] governance sudah dibaca sebelum update
- [ ] tidak ada section lama yang hilang
- [ ] tidak ada isi yang diringkas
- [ ] ada session baru di bawah
- [ ] summary di atas sudah sesuai kondisi terbaru
- [ ] status DONE/PARTIAL/OPEN jelas

---

## 🔒 ENFORCEMENT PRINCIPLE

Audit file adalah:
> source of truth + historical contract evolution

Bukan:
- dokumentasi biasa
- ringkasan
- catatan sementara

Setiap pelanggaran terhadap aturan ini:
- akan merusak traceability
- menyulitkan debugging
- menghilangkan konteks perubahan

---

## FINAL RULE

Jika ragu:
> jangan ubah isi lama — tambahkan saja session baru
