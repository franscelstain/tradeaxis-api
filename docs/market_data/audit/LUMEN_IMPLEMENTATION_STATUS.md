# LUMEN_IMPLEMENTATION_STATUS

## File Role
- Fungsi file ini: checkpoint implementasi aktif untuk melanjutkan pekerjaan berikutnya.
- Fokus file ini:
  - status implementasi market-data saat ini;
  - workstream terakhir yang selesai;
  - proof terakhir yang sah;
  - remaining work yang masih sah dibuka;
  - next recommended workstream.
- File ini bukan contract matrix utama. Status contract detail berada di `LUMEN_CONTRACT_TRACKER.md`.

## Writing Rules
- Gunakan **workstream** sebagai unit progres aktif. Jangan jadikan nomor sesi sebagai struktur utama file ini.
- Gunakan `WORKSTREAM ID` sebagai label resmi pekerjaan yang sudah dilakukan.
- Setiap record progres hanya boleh memakai salah satu status berikut:
  - `DONE`
  - `PARTIAL`
  - `BLOCKED`
- `DONE` hanya boleh dipakai jika target workstream dan proof minimum yang dituju benar-benar tertutup.
- `PARTIAL` dipakai bila ada progres nyata tetapi gap inti parent contract belum tertutup.
- `BLOCKED` dipakai bila pekerjaan tidak dapat lanjut karena dependency/fakta eksternal belum tersedia.
- Gunakan `DOC GAP`, `DOC CONFLICT`, `DOC SYNC ISSUE`, dan `CONFLICT` hanya untuk issue yang masih aktif.
- File ini harus ringkas dan operasional. Jangan menulis ulang histori panjang yang sudah dipindahkan ke arsip.

## How To Write Updates In This File
- Perbarui file ini hanya saat ada perubahan state implementasi, proof, atau next workstream yang sah.
- Tulis ringkas dalam bahasa status operasional, bukan narasi sesi panjang.
- Saat sebuah workstream selesai, isi minimal yang wajib diperbarui:
  - `Current Project Status`
  - `Validated Completed Work`
  - `Last Completed Work Record`
  - `Remaining Work`
  - `Next Recommended Work`
- Jangan ulang semua histori lama. Simpan hanya proof minimum yang masih relevan untuk keputusan berikutnya.
- Jika ada mismatch antara code, proof, dan checkpoint, tandai eksplisit sebagai `CONFLICT`, `DOC GAP`, `DOC CONFLICT`, atau `DOC SYNC ISSUE`.

## Resume Rule For New Chat
- Gunakan ZIP terbaru sebagai source of truth.
- Baca file ini dan `LUMEN_CONTRACT_TRACKER.md` terlebih dahulu.
- Validasi checkpoint terhadap repo terbaru; jangan percaya dokumen secara buta jika source code/test terbaru berkata lain.
- Pilih workstream berikutnya dari contract item berstatus `PARTIAL`, `MISSING`, atau `BLOCKED` yang dependency-nya sudah rapat.
- Workstream tidak harus satu task kecil. Boleh beberapa subtask sekaligus selama:
  - masih dalam 1 contract family / 1 parent contract item;
  - jalur implementasinya berdekatan;
  - proof/test masih bisa dijalankan sebagai satu family;
  - checkpoint tetap bisa diupdate dengan jelas.
- Jangan membuka area baru di luar market-data bila parent dependency lama belum rapat.

## Project Scope
- Project type: Lumen
- Domain focus: market-data only
- Root ownership:
  - `docs/README.md`
- Assembly/readiness baseline:
  - `docs/system_audit/**`
- Domain owner:
  - `docs/market_data/**`
- Architecture translation guide:
  - `docs/api_architecture/**`
- Non-scope:
  - watchlist logic
  - strategy scoring / ranking / picks
  - execution / broker / order routing
  - portfolio / investor / position management

## Current Project Status
- Project status: `BELUM SELESAI`
- Current active workstream: belum dipilih; SESI 3 sudah sah ditutup.
- Last completed workstream id: `db_backed_run_requested_trade_date_mismatch_guard_minimum`
- Next recommended workstream: pilih varian correction/runtime DB-backed berikutnya yang masih satu family, paling load-bearing, dan belum punya proof minimum yang cukup.

## Validated Completed Work
- Foundation, orchestration, artifact runtime, publication current-switch, correction reseal runtime, replay/evidence runtime, session snapshot runtime, dan command surface minimum sudah terpasang pada level dasar dan tidak lagi menjadi fokus awal.
- Workstream berikut sudah pernah dikerjakan dan tetap relevan sebagai validated completed work:
  - `market-data-foundation`
  - `commands-run-orchestration`
  - `artifact-runtime`
  - `publication-current-switch`
  - `correction-reseal-runtime`
  - `tests-and-evidence-export-runtime`
  - `replay-evidence-runtime`
  - `db_backed_repository_proof`
  - `db_backed_pipeline_integration_minimum`
  - `db_backed_correction_requires_approval_integration_minimum`
  - `db_backed_correction_reseal_failure_integration_minimum`
  - `db_backed_correction_history_promotion_failure_integration_minimum`
  - `db_backed_post_switch_fallback_missing_publication_row_guard_minimum`
  - `db_backed_post_switch_fallback_missing_pointer_row_guard_minimum`
  - `db_backed_post_switch_fallback_run_readability_mismatch_guard_minimum`
- Proof lokal penting yang sudah tersinkron:
  - full local suite hijau pada historical reference `db_backed_post_switch_fallback_trade_date_mismatch_guard_minimum` dengan `vendor\bin\phpunit` -> `OK (92 tests, 848 assertions)`;
  - full local suite hijau pada historical reference `db_backed_post_switch_fallback_run_current_mirror_mismatch_guard_minimum` dengan `vendor\bin\phpunit` -> `OK (93 tests, 886 assertions)`;
  - focused local proof hijau untuk fallback unsealed-publication, fallback missing-publication-row, dan fallback missing-pointer-row;
  - focused local proof hijau untuk fallback run terminal-status mismatch dan fallback run publishability mismatch;
  - post-switch resolution mismatch family terkini hijau dengan coverage yang sudah memasukkan dua varian readability mismatch;
  - file `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` terkini hijau penuh.
- Manual runtime verification yang tersinkron:
  - correction publish path normal tetap sehat;
  - unchanged-content cancel path tetap sehat;
  - replay/backfill minimum tetap sehat;
  - session-snapshot purge minimum tetap sehat;
  - correction normal `REQUESTED -> APPROVED -> PUBLISHED` tetap sehat dan bukan conflict dengan special-case fallback proof karena precondition insiden khusus tidak dibentuk.

## Current Active Work Record
- Tidak ada workstream aktif yang masih berjalan dari SESI 3.
- Resume point sah saat ini:
  - `CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle`
  - `CONTRACT ITEM 7 — DB-backed integration proof`
- Batch berikutnya harus tetap satu family correction/runtime DB-backed dan tidak membuka area baru di luar dependency itu.

## Last Completed Work Record
- WORKSTREAM ID: `db_backed_run_requested_trade_date_mismatch_guard_minimum`
- STATUS: `DONE`
- SCOPE:
  - menambah guard repository agar pointer/publication resolution gagal-aman bila `eod_runs.trade_date_requested` tidak sama dengan trade date pointer/publication;
  - menambah proof minimum untuk dua jalur yang masih satu family:
    - baseline correction pre-run rejection;
    - post-switch fallback effective-date fail-safe.
- IMPLEMENTATION:
  - `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` kini mewajibkan `run.trade_date_requested = ptr.trade_date` pada resolver berikut:
    - `findCurrentPublicationForTradeDate()`
    - `findPointerResolvedPublicationForTradeDate()`
    - `findCorrectionBaselinePublicationForTradeDate()`
    - `findLatestReadablePublicationBefore()`
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` ditambah test:
    - `test_run_daily_approved_correction_with_pointer_to_publication_whose_run_requested_trade_date_mismatches_rejects_before_run_creation_and_preserves_approval_state()`
    - `test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_run_requested_trade_date_mismatch_does_not_invent_effective_trade_date()`
  - helper fixture baru ditambahkan:
    - `seedBaselinePointerToPublicationWithRunRequestedTradeDateMismatch()`
- PROOF:
  - syntax lint:
    - `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` -> `No syntax errors detected`
    - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `No syntax errors detected`
  - executed local proof:
    - `vendor\bin\phpunit --filter requested_trade_date_mismatch` -> `OK (2 tests, 53 assertions)`
    - `vendor\bin\phpunit --filter post_switch_resolution_mismatch` -> `OK (14 tests, 490 assertions)`
    - `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (34 tests, 909 assertions)`
- RESULT:
  - workstream ini tertutup;
  - blocker environment pada checkpoint sementara sudah hilang karena executed local proof final telah tersedia;
  - sesi 3 sah ditutup dan checkpoint siap menjadi resume point untuk sesi 4.

## Remaining Work
- Parent contract yang masih paling load-bearing dan belum rapat:
  - `CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle`
  - `CONTRACT ITEM 7 — DB-backed integration proof`
- Parent contract lain yang masih `PARTIAL` tetapi bukan prioritas tertinggi saat ini:
  - `CONTRACT ITEM 2 — Core runtime artifact lifecycle`
  - `CONTRACT ITEM 4 — Replay verification / evidence / smoke / backfill`
  - `CONTRACT ITEM 5 — Session snapshot runtime`
  - `CONTRACT ITEM 6 — Operator command surface / ops proof`
- Parent contract `8` tetap `MISSING` karena done gate proyek belum sah ditutup.
- Remaining gap yang masih sah dibuka sekarang:
  - varian correction/runtime atau fallback-integrity berikutnya yang masih load-bearing dan belum punya proof minimum;
  - broader DB-backed conflict/error integration matrix yang belum cukup rapat untuk menutup parent item `3` dan `7`;
  - jangan buka ulang varian yang sudah tertutup minimum.

## Next Recommended Work
- Prioritas berikutnya tetap satu family: pilih varian correction/runtime DB-backed berikutnya yang masih grounded di owner-docs dan belum punya proof minimum.
- Fokus utama tetap pada parent contract:
  - `CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle`
  - `CONTRACT ITEM 7 — DB-backed integration proof`
- Jangan buka replay, ops, atau final readiness gate sebelum correction/runtime DB-backed family makin rapat.

## Active Issues
- Tidak ada `CONFLICT` aktif saat ini.
- Tidak ada `DOC GAP` aktif saat ini.
- Tidak ada `DOC CONFLICT` aktif saat ini.
- Tidak ada `DOC SYNC ISSUE` aktif saat ini.
- Catatan operasional:
  - checkpoint sesi 2 tetap tersinkron ke executed local proof final.
  - checkpoint sesi 3 kini sudah tersinkron ke executed local proof final dan sah menjadi resume point berikutnya.

## Historical References
- Jangan gunakan daftar ini sebagai struktur utama checkpoint aktif.
- Referensi resmi yang masih mungkin muncul di arsip/audit lama:
  - `market-data-foundation`
  - `commands-run-orchestration`
  - `artifact-runtime`
  - `publication-current-switch`
  - `correction-reseal-runtime`
  - `tests-and-evidence-export-runtime`
  - `replay-evidence-runtime`
  - `replay-verification-minimum`
  - `session-snapshot-minimum-runtime`
  - `db_backed_repository_proof`
  - `db_backed_pipeline_integration_minimum`
  - `correction_cancel_matrix_proof_minimum`
  - `correction_conflict_held_operator_proof_minimum`
  - `db_backed_changed_content_promotion_failure_integration_minimum`
  - `db_backed_correction_requires_approval_integration_minimum`
  - `db_backed_correction_reseal_failure_integration_minimum`
  - `db_backed_correction_history_promotion_failure_integration_minimum`
  - `db_backed_correction_missing_baseline_guard_integration_minimum`
  - `db_backed_correction_malformed_baseline_pointer_guard_integration_minimum`
  - `db_backed_correction_missing_publication_pointer_guard_integration_minimum`
  - `db_backed_correction_non_readable_baseline_run_guard_integration_minimum`
  - `db_backed_pointer_trade_date_mismatch_guard_minimum`
  - `db_backed_run_current_mirror_mismatch_guard_minimum`
  - `db_backed_pointer_run_id_mismatch_guard_minimum`
  - `db_backed_pointer_publication_version_mismatch_guard_minimum`
  - `db_backed_publication_current_mirror_mismatch_guard_minimum`
  - `db_backed_missing_run_row_guard_minimum`
  - `db_backed_post_switch_fallback_malformed_guard_minimum`
  - `db_backed_post_switch_fallback_publication_version_mismatch_guard_minimum`
  - `db_backed_post_switch_fallback_missing_run_row_guard_minimum`
  - `db_backed_post_switch_fallback_run_id_mismatch_guard_minimum`
  - `db_backed_post_switch_fallback_trade_date_mismatch_guard_minimum`
  - `db_backed_post_switch_fallback_publication_current_mirror_mismatch_guard_minimum`
  - `db_backed_post_switch_fallback_run_current_mirror_mismatch_guard_minimum`
  - `db_backed_post_switch_fallback_unsealed_publication_guard_minimum`
  - `db_backed_post_switch_fallback_missing_publication_row_guard_minimum`
  - `db_backed_post_switch_fallback_missing_pointer_row_guard_minimum`
