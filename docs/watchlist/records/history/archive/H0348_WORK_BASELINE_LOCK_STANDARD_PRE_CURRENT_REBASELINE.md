# Watchlist Work Baseline Lock Standard

> **Status:** CANONICAL GOVERNANCE  
> **Scope:** setiap current/future `WS-Bxx` implementation, rework, validation, dan proof attempt  
> **Purpose:** memastikan setiap attempt dapat dijawab dengan tepat: *dikerjakan terhadap strategy/governance/Market Data contract/source revision yang mana?*

## 1. Core Rule

Setiap attempt yang akan mengubah atau memvalidasi current implementation/proof **wajib mempunyai Work Baseline Lock yang diterbitkan sebelum perubahan code/contract dilakukan**.

`Baseline Lock` bukan status kelulusan. Ia adalah immutable provenance snapshot dari authority dan starting technical state yang dipakai oleh attempt.

Tanpa Baseline Lock yang valid:

- attempt boleh melakukan discovery/read-only inspection;
- attempt **tidak boleh** mengklaim implementation progress, conformance, `SATISFIED`, atau stage closure;
- evidence hasil perubahan tidak boleh dipakai sebagai closure evidence.

## 2. Identity

Gunakan identity:

```text
Baseline ID: WSBL-YYYYMMDD-NNN
Stage ID: WS-Bxx
Attempt ID: WS-Bxx-Ayyy
Evidence Record ID (current work): E-WS-Bxx-Ayyy-NNN
```

Satu Attempt ID hanya boleh terikat ke satu Baseline ID dan tepat satu `WORK_BASELINE_LOCK`. Satu Baseline ID juga tidak boleh dipakai oleh Attempt lain. Relationship gate wajib memverifikasi binding ini beserta identity metadata di file baseline JSON. Jika baseline harus diganti, tutup attempt lama dan buka attempt baru.

Attempt ID juga menjadi canonical Work/Correlation ID sesuai [`WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md`](WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md); baseline evidence wajib diregister pada Work Record Registry.

## 3. Mandatory Baseline Content

Baseline implementation minimum harus mengunci:

1. **Strategy authority**
   - seluruh current canonical strategy owner yang relevant;
   - path + SHA1 masing-masing.
2. **Governance authority**
   - recording, stage/rework, residue, traceability, baseline, integrity, change authority yang relevant;
   - path + SHA1.
3. **Market Data consumer boundary**
   - canonical Watchlist Market Data requirements;
   - producer-facing consumer read/readiness contracts yang relevant;
   - technical Market Data intake contract;
   - path + SHA1.
4. **Traceability state**
   - canonical matrix path + SHA1;
   - rule IDs / build stage yang menjadi scope attempt.
5. **Source-code starting revision**
   - Git commit SHA;
   - branch/ref bila ada;
   - working-tree state pada saat baseline dibuat.
6. **Dirty working tree**, bila disengaja
   - wajib `DIRTY_DECLARED`;
   - daftar/summary perubahan atau diff fingerprint;
   - tidak boleh disamarkan sebagai clean baseline.
7. **Dependency lock**
   - `composer.lock` SHA1 bila repository menggunakannya;
   - dependency lock lain yang material bila relevant.
8. **Schema/migration starting identity**
   - migration head / schema baseline / relevant DB revision bila stage menyentuh persistence.
9. **Toolchain/runtime identity** yang material terhadap hasil
   - PHP/runtime version;
   - database engine/version bila relevant;
   - OS/environment class bila behavior dapat berbeda.

Field yang benar-benar tidak applicable harus ditulis `N/A` dengan alasan, bukan dihilangkan diam-diam.

## 4. Baseline Modes

### `IMPLEMENTATION`

Digunakan untuk `WS-Bxx` yang mengubah code/contract/runtime/proof. Source revision wajib tersedia.

### `AUDIT_READONLY`

Digunakan untuk audit code tanpa perubahan. Source revision tetap wajib agar audit dapat direplay.

### `DOCUMENTATION_GOVERNANCE`

Hanya untuk pekerjaan documentation/governance yang tidak mengklaim implementation progress. Tidak boleh dipakai untuk mengisi strategy coverage sebagai `SATISFIED`.

## 5. Issuance and Storage

Baseline final adalah **EVIDENCE / IMMUTABLE_AFTER_ISSUE**.

Lokasi normal:

`docs/watchlist/records/evidence/runs/`

Gunakan template:

[`../../development/implementation/examples/WORK_BASELINE_LOCK_TEMPLATE.json`](../../development/implementation/examples/WORK_BASELINE_LOCK_TEMPLATE.json)

Generator helper:

[`../../development/implementation/tests/CreateWorkBaselineLock.php`](../../development/implementation/tests/CreateWorkBaselineLock.php)

Setelah diterbitkan, baseline tidak boleh diedit. Jika ada field salah, buat correction/new baseline sesuai `DOCUMENT_RECORDING_STANDARD.md`.

## 6. Authority Drift During an Attempt

Perubahan code yang memang menjadi scope attempt **bukan baseline drift**. Baseline merekam starting code revision.

Yang dianggap authority/input drift antara lain:

- canonical strategy owner berubah;
- governance rule relevant berubah;
- Market Data consumer contract relevant berubah;
- traceability rule meaning/scope berubah;
- dependency lock/upstream contract berubah di luar perubahan yang dideklarasikan attempt.

Jika drift ditemukan sebelum attempt closure:

1. jangan diam-diam melanjutkan terhadap authority baru;
2. catat `BASELINE_DRIFT_DETECTED` pada attempt;
3. tentukan impact;
4. tutup attempt lama dengan evidence;
5. buka attempt baru + Baseline ID baru jika perubahan material/relevant.

Tidak ada "rebase in-place" pada immutable baseline.

## 7. Stage Register Binding

Stage register wajib menyimpan Baseline ID untuk latest attempt.

`DONE` dilarang jika closure evidence tidak dapat ditelusuri ke:

`Stage ID -> Attempt ID -> Baseline ID -> implementation/test/residue/integrity evidence`.

## 8. Traceability Binding

Setiap row strategy yang dinaikkan menjadi `SATISFIED` harus dapat ditelusuri ke evidence yang menyebut Attempt ID + Baseline ID.

Baseline tidak membuktikan rule `SATISFIED`; baseline hanya membuktikan **authority dan starting state apa yang digunakan ketika bukti tersebut dihasilkan**.

## 9. Hard Prohibitions

Dilarang:

- memulai implementation change lalu membuat baseline secara retroaktif;
- mengganti SHA/path baseline agar cocok dengan hasil akhir;
- memakai baseline dari attempt lain;
- mengklaim clean tree ketika dirty;
- mengganti strategy/governance saat attempt berjalan tanpa drift handling;
- menggunakan `DOCUMENTATION_GOVERNANCE` baseline untuk mengklaim code conformance.

## 10. Closure Gate

Sebelum attempt closure:

- Baseline ID valid dan immutable;
- final attempt record menunjuk baseline;
- executable integrity gate memvalidasi baseline structure + locked authority fingerprints;
- setiap authority drift sudah mempunyai disposition yang sah.
