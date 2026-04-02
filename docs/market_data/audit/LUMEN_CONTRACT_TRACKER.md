# LUMEN_CONTRACT_TRACKER

## File Role
- Fungsi file ini: contract matrix aktif untuk memilih workstream berikutnya.
- Fokus file ini:
  - status setiap contract item;
  - evidence minimum yang masih relevan;
  - open gap yang masih hidup;
  - dependency order;
  - next required action.
- File ini adalah sumber utama untuk memilih area kerja berikutnya. Ringkasan operasional proyek ada di `LUMEN_IMPLEMENTATION_STATUS.md`.

## Writing Rules
- Gunakan **contract item** sebagai struktur utama file ini.
- Gunakan **workstream** sebagai label progres implementasi terakhir yang relevan untuk sebuah contract item.
- Jangan gunakan nomor sesi sebagai struktur utama tracker ini.
- `DONE` hanya boleh dipakai bila contract item benar-benar tertutup.
- `DONE` di level workstream tidak otomatis berarti parent contract `DONE`.
- Simpan evidence minimum yang masih relevan; jangan ulang seluruh histori bila tidak lagi menambah keputusan.

## How To Write Updates In This File
- Perbarui file ini bila ada perubahan status contract, evidence minimum yang relevan, open gap, atau next required action.
- Untuk setiap contract item, pastikan selalu ada bagian berikut dan tetap sinkron:
  - `STATUS`
  - `OWNER AREA`
  - `LAST UPDATED WORKSTREAM`
  - `EVIDENCE`
  - `OPEN GAP`
  - `NEXT REQUIRED ACTION`
- `EVIDENCE` hanya memuat bukti minimum yang masih membantu keputusan kerja berikutnya.
- `OPEN GAP` harus menjelaskan dengan tegas apa yang masih kurang pada contract item itu.
- `NEXT REQUIRED ACTION` harus menjelaskan langkah sah berikutnya, bukan histori yang sudah lewat.
- Bila gap item besar masih terlalu umum, pecah menjadi sub-family yang lebih konkret agar pemilihan workstream berikutnya tidak kabur.

## Resume Rule For New Chat
- Gunakan ZIP terbaru sebagai source of truth.
- Baca file ini dan `LUMEN_IMPLEMENTATION_STATUS.md` terlebih dahulu.
- Validasi status tracker terhadap source code/test terbaru.
- Ambil workstream prioritas dari item `PARTIAL`, `MISSING`, atau `BLOCKED` yang dependency-nya sudah rapat.
- Jangan membuka area baru bila parent contract yang menaungi area itu masih bolong.
- Workstream tidak harus satu task kecil. Boleh beberapa subtask sekaligus selama masih satu contract family dan proof-nya tetap rapat.

## Contract Selection Rule
- Prioritaskan contract `MISSING` yang menjadi dependency langsung pekerjaan berikutnya.
- Bila tidak ada `MISSING` yang sah dibuka, ambil `PARTIAL` dengan gap paling sempit namun paling load-bearing terhadap final readiness.
- Jangan memilih pekerjaan baru hanya karena menarik; pilih yang paling dekat menutup parent contract matrix.
- Jangan mencampur beberapa contract family hanya demi memperbesar scope.

## Closed Contract Items
- `CONTRACT ITEM 1 — Foundation / bootstrap / ownership`
  - STATUS: `DONE`
  - RINGKASAN: foundation / bootstrap / root ownership sudah tertutup minimum dan tidak lagi menjadi gap aktif.

## Active Contract Items

## CONTRACT ITEM 2 — Core runtime artifact lifecycle
- STATUS: `PARTIAL`
- OWNER AREA: bars / indicators / eligibility / publication
- LAST UPDATED WORKSTREAM: `db_backed_post_switch_fallback_missing_publication_row_guard_minimum`
- EVIDENCE:
  - canonical bars / indicators / eligibility runtime installed;
  - publication current-switch and pointer-sync runtime installed;
  - unchanged correction path proven;
  - changed-content correction publish/conflict minimum proof family installed;
  - baseline-resolution guards and pointer/publication/run integrity guards installed through the current minimum family;
  - artifact proof packaging pattern under `storage/app/market_data/evidence/local_phpunit/...` is already in use.
- OPEN GAP:
  - broader artifact/runtime conflict matrix is still not fully closed beyond the current minimum proof family.
- WHAT IS STILL MISSING:
  - varian artifact/runtime conflict lain di luar guard minimum yang sudah terbukti masih belum diberi proof minimum yang cukup untuk menutup parent contract ini.
- NEXT REQUIRED ACTION:
  - lanjut hanya bila artifact/runtime gap ini memang menjadi dependency langsung pekerjaan berikutnya;
  - jangan ulang varian minimum yang sudah tertutup.

## CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle
- STATUS: `PARTIAL`
- OWNER AREA: correction runtime and finalize outcomes
- LAST UPDATED WORKSTREAM: `db_backed_run_requested_trade_date_mismatch_guard_minimum`
- EVIDENCE:
  - correction request / approval / reseal / publish / cancel runtime installed;
  - unchanged correction rerun proven berakhir `CANCELLED`;
  - held/conflict operator proof installed;
  - reseal-failure and history-promotion-failure DB-backed minimum proof installed;
  - changed-content correction publish failure + promote/current-switch conflict proof family installed;
  - post-switch rollback proof family installed;
  - prior-readable fallback fail-safe proof family installed, termasuk:
    - malformed fallback pointer;
    - fallback publication-version mismatch;
    - fallback trade-date mismatch;
    - fallback missing-run-row;
    - fallback run-current-mirror mismatch;
    - fallback publication-current-mirror mismatch;
    - fallback unsealed-publication;
    - fallback missing-publication-row;
    - fallback missing-pointer-row.
  - manual runtime verification menegaskan jalur correction normal tetap sehat dan tidak regress;
  - fallback run readability mismatch minimum family sudah tertutup, termasuk:
    - fallback run terminal-status mismatch;
    - fallback run publishability mismatch.
  - mismatch `run.trade_date_requested` guard patch sudah masuk untuk resolver current/baseline/fallback, dengan test minimum yang menutup:
    - baseline correction pre-run rejection saat pointer/publication tampak benar tetapi run requested date mismatch;
    - post-switch fallback fail-safe saat prior-readable fallback run requested date mismatch.
  - executed local proof final untuk workstream aktif sudah tersinkron:
    - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` -> `No syntax errors detected`
    - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `No syntax errors detected`
    - `vendor\bin\phpunit --filter requested_trade_date_mismatch` -> `OK (2 tests, 53 assertions)`
    - `vendor\bin\phpunit --filter post_switch_resolution_mismatch` -> `OK (14 tests, 490 assertions)`
    - `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (34 tests, 909 assertions)`
- OPEN GAP:
  - broader correction conflict/error matrix masih belum fully closed di luar minimum proof family yang sudah ada.
- WHAT IS STILL MISSING:
  - varian correction/runtime lain yang masih load-bearing tetapi belum punya proof minimum;
  - sub-family conflict/error integration di luar fallback-integrity minimum yang sudah tertutup.
- NEXT REQUIRED ACTION:
  - pilih varian correction/runtime berikutnya yang masih satu family, paling load-bearing, dan benar-benar belum terbukti;
  - jangan buka area baru sebelum family correction/runtime ini makin rapat.

## CONTRACT ITEM 4 — Replay verification / evidence / smoke / backfill
- STATUS: `PARTIAL`
- OWNER AREA: replay verification and evidence
- LAST UPDATED WORKSTREAM: `db_backed_correction_requires_approval_integration_minimum`
- EVIDENCE:
  - replay verifier minimum exists;
  - reason-code alignment exists;
  - expected/actual evidence export exists;
  - replay smoke suite exists;
  - replay backfill minimum exists;
  - manual runtime verification confirms replay backfill valid case passes.
- OPEN GAP:
  - replay area masih bagian dari parent test/evidence matrix yang belum final-ready secara penuh.
- WHAT IS STILL MISSING:
  - closure proof yang cukup untuk menyatakan replay/evidence area tidak lagi menyisakan blocker load-bearing.
- NEXT REQUIRED ACTION:
  - revisit hanya bila replay menjadi next highest-priority remaining partial setelah correction/runtime matrix menyempit.

## CONTRACT ITEM 5 — Session snapshot runtime
- STATUS: `PARTIAL`
- OWNER AREA: session snapshot ops/runtime
- LAST UPDATED WORKSTREAM: `session-snapshot-minimum-runtime`
- EVIDENCE:
  - session snapshot minimum runtime exists;
  - purge runtime exists;
  - defaults synced to locked contract;
  - manual runtime verification confirms purge path sehat.
- OPEN GAP:
  - masih bagian dari broader ops matrix; belum final-ready sebagai parent area tertutup.
- WHAT IS STILL MISSING:
  - closure proof yang cukup untuk menyatakan session snapshot ops/runtime tidak lagi menyisakan blocker load-bearing.
- NEXT REQUIRED ACTION:
  - revisit hanya bila ops matrix menjadi next highest-priority remaining partial.

## CONTRACT ITEM 6 — Operator command surface / ops proof
- STATUS: `PARTIAL`
- OWNER AREA: command surface / operator summaries
- LAST UPDATED WORKSTREAM: `pointer_publication_version_mismatch_guard_minimum`
- EVIDENCE:
  - correction command proof added;
  - ops command proof added;
  - correction cancelled summary proof added;
  - correction held/resealed summary proof aligned;
  - lock-conflict summary now surfaces `reason_code` / `notes`;
  - manual runtime verification confirms command surface tetap sehat untuk normal publish dan cancel path.
- OPEN GAP:
  - broader ops failure / retry / scheduler matrix masih belum fully proven.
- WHAT IS STILL MISSING:
  - proof yang cukup untuk menutup ops failure / retry / scheduler matrix pada parent contract ini.
- NEXT REQUIRED ACTION:
  - lanjutkan area ini hanya bila dipilih sebagai workstream prioritas berikutnya.

## CONTRACT ITEM 7 — DB-backed integration proof
- STATUS: `PARTIAL`
- OWNER AREA: repository + pipeline integration
- LAST UPDATED WORKSTREAM: `db_backed_run_requested_trade_date_mismatch_guard_minimum`
- EVIDENCE:
  - repository integration proof added in historical workstream `db_backed_repository_proof`;
  - DB-backed pipeline integration minimum added in historical workstream `db_backed_pipeline_integration_minimum`;
  - executed local proof synced after executed local proof checkpoint sync;
  - correction requires-approval, reseal-failure, history-promotion-failure, dan baseline guard family sudah tertutup minimum;
  - pointer/publication/run integrity guard family sudah tertutup minimum untuk:
    - trade-date mismatch;
    - run-current-mirror mismatch;
    - pointer/publication run-id mismatch;
    - pointer/publication publication-version mismatch;
    - publication-current-mirror mismatch;
    - missing-run-row-behind-publication;
    - run requested-trade-date mismatch.
  - post-switch rollback + fallback-integrity minimum family sudah tertutup minimum untuk:
    - malformed fallback pointer;
    - fallback publication-version mismatch;
    - fallback trade-date mismatch;
    - fallback missing-run-row;
    - fallback run-current-mirror mismatch;
    - fallback publication-current-mirror mismatch;
    - fallback unsealed-publication;
    - fallback missing-publication-row;
    - fallback missing-pointer-row;
    - fallback run-id mismatch;
    - fallback run terminal-status mismatch;
    - fallback run publishability mismatch;
    - fallback run requested-trade-date mismatch.
  - executed local proof terkini sudah tersinkron:
    - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` -> `No syntax errors detected`
    - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `No syntax errors detected`
    - `vendor\bin\phpunit --filter requested_trade_date_mismatch` -> `OK (2 tests, 53 assertions)`
    - `vendor\bin\phpunit --filter post_switch_resolution_mismatch` -> `OK (14 tests, 490 assertions)`
    - `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (34 tests, 909 assertions)`
  - manual runtime verification tambahan menegaskan normal correction path tetap sehat dan bukan conflict dengan special-case proof workstream ini.
- OPEN GAP:
  - broader DB-backed conflict/error integration matrix masih belum fully covered di luar guard minimum yang sudah tertutup;
  - masih mungkin ada varian sempit lain pada correction/runtime atau fallback-integrity yang belum diberi proof minimum.
- WHAT IS STILL MISSING:
  - remaining sub-family DB-backed correction/runtime atau fallback-integrity yang masih grounded di owner-docs tetapi belum diberi proof minimum;
  - closure yang cukup untuk menyatakan matrix integration parent item ini benar-benar rapat.
- NEXT REQUIRED ACTION:
  - lanjut ke varian DB-backed correction/runtime berikutnya yang masih grounded di owner-docs dan paling load-bearing;
  - jangan buka ulang varian yang sudah tertutup minimum.
- IN-PROGRESS NOTE:
  - workstream `db_backed_post_switch_fallback_run_id_mismatch_guard_minimum` sudah tertutup minimum melalui executed local proof dan checkpoint telah disinkronkan;
  - workstream `db_backed_post_switch_fallback_run_readability_mismatch_guard_minimum` sudah tertutup minimum melalui executed local proof final dan tidak lagi menjadi blocker aktif;
  - workstream `db_backed_run_requested_trade_date_mismatch_guard_minimum` sudah tertutup minimum melalui executed local proof final dan checkpoint ini menjadi resume point sah untuk memilih batch berikutnya.

## CONTRACT ITEM 8 — Final readiness gate
- STATUS: `MISSING`
- OWNER AREA: project-level readiness
- LAST UPDATED WORKSTREAM: `correction_conflict_held_operator_proof_minimum`
- EVIDENCE:
  - none yet for full project closure.
- OPEN GAP:
  - parent contracts masih memiliki `PARTIAL` status;
  - final done gate belum sah ditutup.
- WHAT IS STILL MISSING:
  - parent contract utama harus cukup rapat dulu sebelum evaluasi closure proyek bisa sah dilakukan.
- NEXT REQUIRED ACTION:
  - terus pilih highest-priority remaining `PARTIAL` contract sampai parent matrix cukup rapat untuk evaluasi closure.

## Next Workstream Candidates
- Prioritas tertinggi saat ini tetap:
  - lanjutkan `CONTRACT ITEM 7` atau `CONTRACT ITEM 3` pada varian correction/runtime berikutnya yang masih satu family.
- Kandidat workstream berikutnya harus:
  - masih grounded di owner-docs;
  - tidak membuka area baru;
  - menutup gap load-bearing yang benar-benar masih hidup;
  - boleh beberapa subtask sekaligus selama tetap satu contract family.
