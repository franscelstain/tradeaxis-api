# Watchlist Audit Prompt Standard

Gunakan prompt berikut saat audit ZIP baru.

```text
Audit ZIP ini secara ketat sebagai dokumen `watchlist`, bukan portfolio, bukan execution, dan bukan market-data internals.

Kunci arah sejak awal:
- domain aktif hanya `watchlist`
- active policy hanya `weekly_swing`
- watchlist hanya menampilkan saran
- data source realistis tetap dari provider gratis atau input manual

Rule inti yang wajib dicek:
- recommendation berasal dari PLAN only
- recommendation bisa tersedia tanpa confirm
- recommendation bisa kosong walau TOP_PICKS/SECONDARY ada
- confirm eligibility berasal dari candidate PLAN
- non-recommended candidate tetap bisa confirm
- confirm tidak mengubah recommendation
- recommended + confirmed hanya berarti confirm menguatkan

Audit file owner inti lebih dahulu:
- 01, 02, 03, 08, 09, 10, 13, 21, 22, 23, 24, 25

Lalu cek support docs:
- 07, _refs, examples, fixtures, db

Output wajib:
1. nilai akhir yang ketat
2. verdict singkat
3. tabel status PASS/PARTIAL/FAIL/N/A
4. temuan utama
5. file yang masih drift/conflict
6. patch prioritas berikutnya

Turunkan nilai jika:
- scope bocor ke portfolio/execution/market-data internals
- recommendation boundary tidak terkunci
- confirm boundary tidak terkunci
- support docs bertentangan dengan owner docs
```


## Baseline Rule

Gunakan audit baseline ini sebagai baseline tetap. Penguatan ZIP baru hanya boleh memperjelas, memperkaya, atau menutup gap kecil. Penguatan tidak boleh mengubah scope inti watchlist, boundary PLAN/RECOMMENDATION/CONFIRM, atau owner map aktif tanpa perubahan eksplisit pada audit foundation.




## Runtime Validation Claim Guardrail

Saat membuat prompt audit/implementation atau menutup hasil sesi, validasi runtime harus diperlakukan sebagai evidence nyata, bukan asumsi.

Rules:

- DILARANG klaim PHPUnit, Artisan command, migration, seed, calibration, backfill, replay, atau runtime proof sudah dijalankan bila memang belum dijalankan atau tidak bisa dijalankan pada environment sesi tersebut.
- Jika tool/environment tidak dapat menjalankan PHPUnit/Artisan, tulis status apa adanya sebagai `BLOCKED`, `NOT_RUN`, atau `OPERATOR_VALIDATION_REQUIRED`; jangan mengganti dengan klaim PASS.
- Jika ada test/command yang dibutuhkan tetapi harus dijalankan manual oleh operator, output wajib mencantumkan:
  1. manual test command lengkap;
  2. expected output atau minimal expected marker yang harus muncul;
  3. pass/fail criteria yang tegas;
  4. exit code yang diharapkan bila relevan;
  5. larangan klaim final PASS sampai output operator diberikan.
- Jika operator kemudian memberikan output runtime, gunakan hanya output tersebut sebagai evidence. Jangan membuat ulang angka, assertion count, artifact hash, atau database count yang tidak muncul di output/operator evidence.
- Jika runtime gagal karena environment, dependency, database, permission, atau missing extension, bedakan dengan jelas antara implementation gap dan validation blocked.
- Jika command menghasilkan exit code non-zero yang memang domain-valid, misalnya grid quality failed, jelaskan pass/fail criteria berdasarkan `status` dan `reason_code`, bukan hanya berdasarkan exit code.
- Semua klaim seperti `LOCAL_RUNTIME_PROOF_PASS`, `LOCAL_R2_IS_CALIBRATION_EXECUTED`, `OOS_NOT_READ`, `R2_GRID_FAILED_IS_QUALITY`, atau `PRODUCTION_READY` hanya boleh ditulis jika evidence command/artifact/DB mendukung secara eksplisit.

Minimum manual validation format:

```text
Manual command:
<command>

Expected output:
<expected lines / markers>

Pass criteria:
- <criteria 1>
- <criteria 2>

Fail criteria:
- <criteria 1>
- <criteria 2>
```


## Calibration Catalog Naming Guardrail

For Weekly Swing calibration/backtest prompt generation, do not continue numeric R-series naming for future catalogs.

Rules:

- `R1` and `R2` may be referenced only as historical aliases/backward-compatible evidence labels.
- Do not create or recommend `R3`, `R4`, `R5`, or later catalog identity.
- Future catalog identity must use semantic focus + catalog attempt:
  `WS_BT_GRID_<FOCUS>_C##_YYYY_MM`.
- `C##` means Catalog attempt within a named focus/campaign, not system revision. It must never stand alone without the focus/campaign name.
- Future run evidence may use:
  `WS_BT_IS_<FOCUS>_C##_RUN_##` and `WS_BT_OOS_<FOCUS>_C##_RUN_##`.
- If a previous catalog already has runtime evidence, do not rename, mutate, or reinterpret it to improve the result.
- If no IS row passes canonical gates, OOS is not eligible and the next session must be diagnostic/design-first, not OOS.

Current historical aliases/evidence labels:

```text
R1 = WS_BT_GRID_BOOTSTRAP_2026_06
R2 = WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
C01 = WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```

Current post-C01 next-session naming pattern:

```text
WATCHLIST — WEEKLY SWING C01 FAILURE DIAGNOSTIC AND NEXT SEMANTIC CATALOG DESIGN SESSION
```

Next catalog naming rule:

```text
If the same DOWNSIDE_STABILITY focus continues:
WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06

If diagnosis chooses a new focus:
WS_BT_GRID_<NEW_FOCUS>_C01_YYYY_MM
```

Never mutate `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06` after its failed-IS runtime evidence.
