# Legacy Semantic Extract — LX-MD-0034-EVD-01

- Source ID: `LS-MD-0034`
- Original path: `audit/MARKET_DATA_IMPLEMENTATION_LEDGER.md`
- Original SHA1: `53F8C0BDBA19E2AB4F630A31731FDB2210E19CEF`
- Extract role: `EVIDENCE`
- Source range: `L1298-L1335`
- Extract body SHA1: `8843C2D6EDF7C7385313CA87C4C2A60699A9B1D7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## W19 record — operational lifecycle, commands, observability, dan evidence stage 19, ditutup 2026-08-06

Exit gate: *setiap command memiliki bukti success/failure/concurrency/retry; operator tidak dapat mem-bypass publication safety; development frontier tidak dilaporkan keliru sebagai activated freshness.*

Assignment terbesar kedua setelah W10: 27 dokumen operasional.

### Klausa kedua sudah kuat

Operator tidak dapat mem-bypass keselamatan publikasi, dan alasannya struktural: `MarketDataInvariantGuard::assertNoBypassState()` memeriksa **state yang dihasilkan**, bukan niat operator. `promotion_allowed` menuntut `SUCCESS` **dan** `READABLE` **dan** coverage `PASS` sekaligus; kombinasi lain melempar `LogicException`. Run `FAILED`/`HELD` dipaksa `NOT_READABLE` dan wajib membawa reason code — kegagalan tanpa alasan adalah gangguan yang tidak dapat ditriase operator, dan kontrak observability memperlakukan itu sebagai cacat tersendiri.

`--force_replace` pun tidak menjadi jalan pintas: ia menuntut `--force_replace_reason` dan menolak dengan `COMMAND_DESTRUCTIVE_GUARD_REQUIRED` bila kosong. Flag bukan otoritas; jejak auditlah yang membuat tindakan itu dapat ditinjau kemudian.

Dari 34 berkas command, 33 membawa signature dan handler; satu sisanya `AbstractMarketDataCommand` yang memang kelas dasar abstrak dan tidak memiliki permukaan command sendiri.

### `F-021` — pembeda yang ada dan tidak pernah dipanggil

`MarketDataScope` sudah memiliki `isOperationallyActivatedFor()` dan `stateFor()`, keduanya benar. Pencarian pemanggilnya di luar kelas itu sendiri mengembalikan **nol**.

Sementara itu `operational_start_date` tidak diset pada config dan `NULL` pada seluruh 71.917 run. Artinya setiap tanggal sebenarnya berstatus `DEVELOPMENT` — dan payload readiness melaporkan `is_ready => true` tanpa kualifikasi apa pun.

Di situlah klausa ketiga terlanggar. "Ready" adalah klaim tentang platform, dan kata yang sama berarti dua hal berbeda sebelum dan sesudah aktivasi. Tanggal yang diproses ketika sistem masih dibangun tidak segar dalam pengertian operasional mana pun, dan melaporkannya dengan kalimat yang sama adalah cara sebuah frontier pengembangan berubah menjadi jaminan yang tidak pernah dibuat siapa pun.

Diremediasi: `activation_state` disertakan pada payload readiness **dan** diteruskan ke consumer product. Berhenti di readiness akan menyisakan satu-satunya pihak yang bertindak atas data itu tidak dapat melihatnya.

Yang **tidak** diremediasi, dan memang bukan pekerjaan kode: keputusan aktivasi itu sendiri. Selama `operational_start_date` kosong, seluruh keluaran platform berstatus `DEVELOPMENT` dan tidak boleh dikutip sebagai kesegaran operasional. Mengisinya adalah keputusan operator yang menyatakan sistem ini sudah dijalankan sungguhan sejak tanggal tertentu — pernyataan yang hanya sah bila benar.

### Koreksi terhadap pengujian saya sendiri

Dua fixture pertama saya gagal karena kesalahan saya, bukan kesalahan kode: satu menghitung kemunculan `'activation_state'` dan mendapat empat karena ikut menghitung pembacaan yang memberi nilainya, satu lagi menandai `AbstractMarketDataCommand` sebagai command tanpa signature padahal ia kelas dasar abstrak. Keduanya diperbaiki agar menguji hal yang dimaksud.

### Bukti exit gate

`tests/Unit/MarketData/OperationalCommandSafetyBoundaryTest.php`, delapan fixture: promosi ditolak pada tiga bentuk state tidak aman; run gagal tidak pernah dapat dilaporkan readable; run gagal tanpa reason code ditolak; `force_replace` menuntut alasan tercatat; tanpa `operational_start_date` setiap tanggal `DEVELOPMENT`; payload readiness menyatakan activation state pada kedua cabangnya; consumer product ikut membawanya; dan setiap command konkret memiliki signature serta handler.

### Batas kapabilitas

Bukti concurrency di sini bersifat struktural, bukan runtime — sama seperti `F-006` pada W15, membuktikan perilaku di bawah eksekusi bersamaan menuntut dua proses nyata terhadap MariaDB, dan harness serial tidak dapat menggantikannya. Yang terbukti adalah bahwa state tidak aman ditolak, bukan bahwa dua run bersamaan tidak pernah menghasilkannya.


<!-- LEGACY_EXTRACT_BODY_END -->
