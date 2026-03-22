# Lumen Config and Env Baseline

## Purpose

Dokumen ini mengatur baseline pembuatan, perubahan, dan pembersihan `config` dan `.env` pada codebase berbasis Lumen agar implementasi tetap lengkap, tidak manual, tidak mengawang, dan tidak meninggalkan konfigurasi mati.

Dokumen ini berlaku untuk domain apa pun yang dibangun di atas framework Lumen, termasuk tetapi tidak terbatas pada:

- `market_data`
- `watchlist`
- domain lain yang ditambahkan di fase berikutnya

Dokumen ini tidak mengatur business rule domain.  
Dokumen ini hanya mengatur governance untuk:

- `config/*.php`
- `.env.example`
- penggunaan `env()`
- penggunaan `config()`
- sinkronisasi config dengan codebase
- pruning key yang tidak lagi dipakai

---

## Why this baseline exists

Codebase Lumen sering terlihat “selesai” secara struktur domain, tetapi belum benar-benar lengkap untuk dijalankan karena:

- file config belum dibuat
- `.env.example` belum diperbarui
- key config masih hardcoded
- key env berubah-ubah
- key lama tidak dibersihkan
- config namespace tidak konsisten
- implementasi butuh nilai tertentu tetapi tidak pernah didaftarkan secara resmi

Dokumen ini ada untuk mencegah semua itu.

---

## Scope

Dokumen ini berlaku untuk setiap perubahan yang menyentuh salah satu dari berikut:

- file baru di `config/*.php`
- perubahan file `config/*.php`
- penambahan atau penghapusan key di `.env.example`
- perubahan pembacaan `config()`
- penambahan provider, integration, timeout, retry, queue, batch, endpoint, feature flag, scheduler, atau dependency lain yang membutuhkan konfigurasi
- penghapusan fitur atau modul yang sebelumnya punya config/env

---

## Core Principles

### 1. No hidden configuration
Jika suatu perilaku runtime bergantung pada nilai yang bisa berubah antar environment, nilainya tidak boleh disembunyikan di dalam kode.

### 2. No hardcoded operational values
Nilai operasional penting tidak boleh di-hardcode di service, controller, domain compute, job, command, atau repository.

Contoh nilai operasional penting:
- base URL
- timeout
- retry count
- queue name
- schedule toggle
- feature flag
- batch size
- connection name
- log channel khusus
- cache TTL
- path eksternal
- credential reference name

### 3. `env()` is only for config files
`env()` hanya boleh dipakai di file `config/*.php`.

Kode aplikasi harus memakai:
- `config('...')`

bukan:
- `env('...')`

### 4. Every config key must have an owner
Setiap key config harus punya consumer aktif yang jelas di codebase.

### 5. Every env key must map to a real config need
Setiap key di `.env.example` harus:
- dipakai oleh file config,
- atau jelas dibutuhkan oleh bootstrap framework.

Tidak boleh ada env key yatim.

### 6. Config and env must stay pruned
Setiap perubahan pada config/env wajib diikuti pemeriksaan apakah ada key yang sudah tidak dipakai. Jika ada, key itu harus dihapus.

---

## Required Deliverables When Generating or Modifying Code

Setiap implementasi baru atau perubahan fitur yang membutuhkan konfigurasi dianggap belum selesai bila belum mencakup semua deliverable yang relevan berikut:

1. file `config/*.php` yang diperlukan
2. penambahan atau perubahan `.env.example`
3. penggunaan `config()` di codebase
4. penghapusan hardcoded value yang seharusnya menjadi config
5. penghapusan key config/env yang sudah tidak dipakai
6. default value yang aman dan masuk akal
7. namespace config yang konsisten
8. pendaftaran bootstrap/provider bila memang diperlukan oleh Lumen

---

## Config Categories

Untuk menjaga keteraturan, konfigurasi harus dipahami dalam kategori berikut.

### 1. Framework / App Runtime Config
Contoh:
- app name
- environment
- timezone
- debug mode
- log channel
- cache defaults

### 2. Database / Storage Config
Contoh:
- connection name
- read/write connection
- schema-related runtime toggle
- storage disk atau path

### 3. Integration Config
Contoh:
- external base URL
- API timeout
- retry count
- authentication mode
- endpoint path
- callback URL template

### 4. Domain Config
Contoh:
- `market_data.*`
- `watchlist.*`
- threshold operasional domain
- batch size domain
- schedule enable/disable domain
- consumer artifact retention
- publication selection policy yang memang runtime-configurable

### 5. Safety / Reliability Config
Contoh:
- timeout
- retry limit
- circuit breaker toggle
- queue dead-letter behavior
- fail-fast toggle
- emergency disable flag

### 6. Job / Scheduler / Batch Config
Contoh:
- job enabled flag
- batch size
- chunk size
- queue name
- overlap lock timeout
- retry backoff

---

## Namespace Rule

Konfigurasi domain harus memakai namespace yang jelas dan konsisten.

Contoh:
- `market_data.publication.*`
- `market_data.pipeline.*`
- `watchlist.plan.*`
- `watchlist.runtime.*`

Tidak boleh:
- key generic yang sulit di-owner-kan
- namespace campur aduk
- key terlalu pendek yang tidak menjelaskan domain

---

## `.env.example` Rule

### Mandatory rule
Setiap env key yang dibutuhkan codebase wajib muncul di `.env.example`.

### Forbidden rule
Tidak boleh ada kebutuhan env yang hanya diketahui dari kode tetapi tidak didaftarkan di `.env.example`.

### Default rule
Default value di `.env.example` harus:
- aman,
- eksplisit,
- tidak menyesatkan,
- tidak memaksa user menebak struktur nilainya.

### Comment rule
Jika key punya format khusus atau hanya berlaku pada kondisi tertentu, boleh ditambah komentar singkat agar tidak membingungkan.

---

## `config()` Consumption Rule

Kode aplikasi harus membaca konfigurasi lewat `config()`.

Contoh benar:
- `config('market_data.pipeline.batch_size')`
- `config('watchlist.runtime.plan_enabled')`

Contoh salah:
- `env('MARKET_DATA_BATCH_SIZE')` di service
- hardcode `500` di job
- string URL langsung di repository

---

## Bootstrap Completeness Rule

Ketika AI atau developer diminta membuat codebase atau fitur baru di Lumen, hasilnya tidak boleh dianggap lengkap bila salah satu kondisi ini terjadi:

- code membutuhkan config tetapi file `config/*.php` tidak dibuat
- env key dibutuhkan tetapi `.env.example` tidak diperbarui
- config baru dibuat tetapi tidak punya consumer yang jelas
- code memakai `env()` langsung di luar file config
- nilai operasional penting masih hardcoded
- provider/bootstrap yang dibutuhkan tidak didaftarkan

---

## Config / Env Change Checklist

Setiap kali ada perubahan config atau env, wajib lakukan pemeriksaan berikut:

### A. Addition check
- key baru apa yang ditambahkan?
- key itu dipakai di mana?
- key itu milik domain apa?
- apakah sudah ada default yang aman?
- apakah `.env.example` sudah diperbarui?

### B. Change check
- key lama apa yang diubah?
- apakah nama key masih konsisten?
- apakah semua consumer lama masih cocok?
- apakah dokumentasi atau config namespace perlu disesuaikan?

### C. Pruning check
- apakah ada key config yang tidak lagi dipakai?
- apakah ada env key yang tidak lagi dipakai?
- apakah ada provider/bootstrap lama yang hanya mendukung config lama?
- apakah ada fallback/default yang sekarang sudah mati?

Jika jawabannya ya, maka item tersebut harus dihapus.

---

## Mandatory Pruning Rule

Setiap perubahan pada `.env.example` atau `config/*.php` wajib disertai evaluasi terhadap key yang tidak lagi dipakai.

### If unused, remove it
Jika sebuah key:
- tidak lagi dipakai codebase,
- tidak lagi dibaca oleh file config,
- tidak lagi dibutuhkan provider,
- atau fiturnya sudah dihapus,

maka key itu wajib dihapus dari:
- `.env.example`
- `config/*.php`
- referensi code terkait
- komentar dokumentasi terkait bila ada

### No dead config
Tidak boleh mempertahankan key hanya karena:
- “siapa tahu nanti dipakai lagi”
- “biar aman jangan dihapus dulu”
- “sudah terlanjur ada”

Jika nanti dibutuhkan lagi, buat ulang secara eksplisit.

---

## Dead Config Detection Guidance

Saat melakukan pruning, periksa minimal:

1. apakah key env masih direferensikan oleh file config
2. apakah key config masih dibaca oleh codebase
3. apakah fitur/modul consumer dari key tersebut masih ada
4. apakah provider/service/job yang memerlukan key itu masih aktif
5. apakah default/fallback tersebut masih relevan secara runtime

Jika semua tidak lagi relevan, key harus dihapus.

---

## Domain-Specific Config Rule

Jika domain baru ditambahkan, domain tersebut harus:

1. punya namespace config sendiri bila memang membutuhkan konfigurasi
2. tidak numpang ke namespace domain lain tanpa alasan kuat
3. tidak menaruh config penting di file generic kalau sebenarnya domain-specific
4. tidak menciptakan env key liar tanpa owner domain yang jelas

---

## Minimal Expected Output for AI-Generated Codebase

Jika AI diminta membuat atau memodifikasi codebase Lumen, output minimal yang diharapkan adalah:

- file kode utama
- file `config/*.php` yang relevan
- update `.env.example`
- daftar key env baru / berubah / dihapus
- daftar key config baru / berubah / dihapus
- pembersihan key yang mati
- penggunaan `config()` yang konsisten
- tidak ada `env()` di luar config file
- tidak ada hardcoded operational values yang seharusnya configurable

---

## Forbidden Shortcuts

Hal-hal berikut dilarang:

- menyerahkan kebutuhan `.env` ke user tanpa membuat struktur awal
- membuat fitur yang diam-diam membutuhkan env key yang tidak didaftarkan
- memakai `env()` di controller/service/repository/domain/job
- menyimpan nilai base URL/timeout/retry secara hardcoded
- menambahkan config baru tanpa mengecek config lama yang mati
- mempertahankan key `.env` atau `config` yang sudah tidak punya consumer aktif
- mencampur config domain ke namespace acak hanya demi cepat jadi

---

## Review Rule Before Finalizing Any Patch

Sebelum patch atau code generation dianggap selesai, wajib jawab pertanyaan ini:

1. apakah perubahan ini membutuhkan config baru?
2. apakah `.env.example` sudah diperbarui?
3. apakah semua nilai operasional penting sudah dibaca lewat `config()`?
4. apakah ada key lama yang sekarang mati?
5. apakah key mati itu sudah dihapus?
6. apakah namespace config tetap konsisten?
7. apakah provider/bootstrap yang relevan sudah sinkron?
8. apakah hasil akhir bisa dipakai tanpa user harus menebak config sendiri?

Jika salah satu jawaban masih “belum”, maka implementasi belum dianggap selesai.

---

## Relation to Other Documents

Dokumen ini harus dibaca bersama:
- `docs/api_architecture/README.md`
- `docs/api_architecture/repository.md`
- `docs/api_architecture/application-service.md`
- `docs/api_architecture/transport-boundary.md`
- `docs/system_audit/SYSTEM_TRANSLATION_BASELINE.md`

Dokumen ini tidak menggantikan aturan layer architecture.  
Dokumen ini melengkapi aspek bootstrap dan runtime configuration discipline pada implementasi Lumen.

---

## Final Rule

Codebase Lumen tidak boleh dinilai lengkap hanya karena domain logic dan struktur layer sudah benar.

Selama config dan `.env` belum:
- dibuat,
- disinkronkan,
- dipakai dengan benar,
- dan dipruning dari key yang mati,

maka codebase masih dianggap belum rapat dan masih berisiko mengawang saat dijalankan.