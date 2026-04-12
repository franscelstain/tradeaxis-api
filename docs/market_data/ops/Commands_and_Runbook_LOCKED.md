# COMMANDS AND RUNBOOK (LOCKED)

## Purpose
Mengunci surface command resmi dan decision flow operasional untuk arsitektur **IMPORT vs PROMOTE**.

---

## Official command surface
Command resmi domain ini:
- `market-data:daily`
- `market-data:backfill`
- `market-data:promote`

Tidak ada alias command promote lain.
Tidak ada command import yang otomatis menyatakan requested date readable.

---

## Command ownership split (LOCKED)

### `market-data:daily`
Owner untuk import satu requested trade date.

Wajib:
- resolve requested trade date eksplisit
- acquire source data
- map + dedup + validate
- persist canonical valid bars
- persist invalid rows
- write bars coverage evidence minimum
- write telemetry minimum

Dilarang:
- compute indicators
- build eligibility
- hash
- seal
- finalize readability
- switch current publication pointer

### `market-data:backfill`
Owner untuk import range trading date.

Wajib:
- iterasi per trading date yang diminta
- jalankan flow import per tanggal
- persist hasil per tanggal
- persist bars coverage summary minimum bila tersedia
- tetap menjaga requested date per tanggal sebagai identity domain

Dilarang:
- memaksa requested date menjadi readable hanya karena import range selesai
- fetch implicit recent-only bila operator meminta historical range eksplisit
- menjalankan promote secara diam-diam

### `market-data:promote`
Owner untuk requested date yang hasil import-nya sudah persisted.

Wajib:
- membaca persisted import result untuk tanggal target
- validasi coverage gate
- decision gate awal coverage
- hanya bila coverage `PASS`, lanjut indicators → eligibility → hash → seal → finalize
- menetapkan terminal status / publishability state
- menjaga effective-date readability contract

Dilarang:
- melakukan source fetch ulang
- menurunkan threshold coverage demi publish cepat
- membaca partial import sebagai readable success

Promote membaca hasil import yang sudah persisted dan tidak melakukan source fetch ulang.

---

## Deterministic operational priorities
Prioritas operasional resmi:
1. requested date identity harus tetap benar
2. source traceability harus lengkap
3. coverage/readability safety lebih penting daripada publish cepat
4. retry hanya untuk transient failures
5. import partial boleh selesai sebagai import evidence
6. requested date hanya boleh `READABLE` setelah promote penuh sukses

---

## Source execution order (LOCKED)
Urutan source resmi:
1. primary `api_free` / `yahoo_finance`
2. secondary controlled recovery `manual_file`

Keputusan final:
- retry primary dulu untuk transient failure
- jangan pindah ke source recovery di tengah run lalu merge hasilnya
- bila perlu source recovery, jalankan sebagai run eksplisit terpisah
- source conflict diselesaikan oleh source priority + validation + correction flow, bukan voting

---

## Coverage and quality decision rules (LOCKED)
Aturan promote:
- threshold coverage minimum = **95%**
- coverage ratio dihitung dari canonical valid bars / coverage universe
- coverage `PASS` hanya bila ratio >= 95% dan tidak ada hard integrity blocker
- coverage `FAIL` bila ratio < 95% namun masih evaluable
- coverage `BLOCKED` bila basis evaluasi tidak aman atau tidak lengkap

Aturan penting:
- partial import boleh terjadi
- partial readable publication tidak boleh terjadi
- sistem memilih **cukup lengkap lalu publish** dibanding **lebih cepat tetapi belum cukup lengkap**

---

## Import failure interpretation
Per-ticker failure saat import:
- masuk ke telemetry/evidence
- tidak otomatis membuat command import crash
- tidak otomatis membuat requested date readable
- serahkan penilaian akhirnya ke coverage/promote stage

Global integrity failure saat import:
- boleh menghentikan requested date sebagai candidate yang aman
- harus menghasilkan evidence failure yang jelas
- coverage gate dapat berakhir `BLOCKED`

---

## Promote outcome interpretation
- bila coverage gate tidak bisa dievaluasi aman → `BLOCKED`
- bila coverage gate gagal → `FAIL`
- bila promote gagal, requested date tidak boleh readable
- fallback prior readable date boleh tetap melayani consumer sesuai contract readability, tetapi requested date saat ini tetap `NOT_READABLE`

---

## Consumer safety enforcement (LOCKED)
Consumer resmi, termasuk watchlist, wajib:
- baca hanya dari publication yang `SEALED` + current + readable
- resolve tanggal melalui current publication pointer / effective-date contract
- pakai `trade_date_effective`, bukan `MAX(date)`

Consumer dilarang:
- query raw table langsung tanpa context publication
- hitung ulang indicator
- bypass coverage gate
- menentukan tanggal sendiri dengan recency guessing

Lihat juga: `../book/CONSUMER_READ_CONTRACT_LOCKED.md`.

---

## Minimum operator run sequence
### Daily date
1. jalankan `market-data:daily` untuk tanggal target
2. review import evidence minimum
3. pastikan source traceability jelas
4. cek bars coverage evidence minimum
5. jalankan `market-data:promote` untuk tanggal yang sama
6. verifikasi terminal status + publishability + effective date

### Historical range
1. jalankan `market-data:backfill` untuk range target
2. review hasil import per tanggal
3. identifikasi tanggal yang coverage/import evidence-nya lemah
4. untuk tiap tanggal yang siap, jalankan `market-data:promote`
5. bila perlu recovery source, jalankan run eksplisit terpisah

---

## Anti-ambiguity rules
Tidak boleh:
- menganggap command import sebagai command publish
- coverage dihitung dari successful HTTP request count
- import success = requested-date readable success
- promote fetch ulang source data
- merge source A dan source B secara diam-diam dalam satu publication candidate
- mempublish date dengan coverage di bawah 95%

---

## Cross-contract alignment
Harus sinkron dengan:
- `../book/Source_Data_Acquisition_Contract_LOCKED.md`
- `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
- `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `../book/Run_Status_and_Quality_Gates_LOCKED.md`
- `../book/CONSUMER_READ_CONTRACT_LOCKED.md`
