# LUMEN_IMPLEMENTATION_STATUS

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

## Current Phase
- Phase: SESSION_28_CORRECTION_OUTCOME_NOTE_SYNC

## Current Batch
- Batch: BATCH_28_CORRECTION_OUTCOME_NOTE_SYNC

## Global Status
- Status: BELUM SELESAI

## Summary Progres Nyata
- Checkpoint sesi 2 dibaca ulang lebih dulu lalu divalidasi terhadap repo terbaru.
- Batch ketiga ditarik langsung dari gap prioritas berikutnya yang masih kosong: artifact runtime inti, bukan area baru.
- Perubahan nyata pada batch ketiga:
  - penambahan source adapter lokal `manual_file/manual_entry` untuk normalized source rows EOD bars;
  - penambahan resolver ticker master berbasis contract `docs/db/02_TICKERS_MASTER.md`;
  - penambahan repository candidate publication agar live artifact punya publication context minimum sebelum current-switch dibuka;
  - implement canonical bars ingest nyata ke `eod_bars` dan `eod_invalid_bars`;
  - implement indicator compute nyata ke `eod_indicators` untuk `dv20_idr`, `atr14_pct`, `vol_ratio`, `roc20`, `hh20`;
  - implement eligibility build nyata ke `eod_eligibility` satu row per universe ticker;
  - perbaikan hash stage agar memakai field-order deterministik minimum, bukan dump row mentah.

## Area yang Sudah Dikerjakan
- Repo discovery
- Authority/build-order extraction
- Checkpoint validation
- Initial contract extraction
- Initial codebase mapping
- Initial GAP table
- Batch 1 foundation implementation
- Batch 2 command surface / orchestration minimum
- Batch 3 artifact runtime foundation
- Batch 4 publication current switch / pointer sync / finalize guard
- Batch 5 correction request / approval / reseal / publish runtime
- Batch 26 ticker mapping reason-code sync
- Batch 27 market-data unit-test config harness sync
- Batch 28 correction outcome note sync

## Area yang Sedang Dikerjakan
- Batch ini menutup `DOC GAP` lama pada correction metadata agar `eod_dataset_corrections` menyimpan `final_outcome_note` terpisah, sinkron dengan contract owner dan correction evidence export.

## Kontrak DONE
- Root ownership rules extracted
- System assembly baseline extracted
- System readiness baseline extracted
- Translation baseline extracted
- Lumen bootstrap/config-env baseline enabled for market-data foundation
- Canonical bars runtime foundation installed
- Indicators runtime foundation installed
- Eligibility runtime foundation installed
- Publication current switch and pointer-sync runtime installed
- Finalize guard now depends on cutoff + sealed current publication + pointer resolution
- Historical correction request / approval / reseal / publish runtime installed
- Correction candidate artifacts for requested date now stage safely through immutable *_history snapshots before publish

## Kontrak PARTIAL
- Publication contract mapped and current-switch runtime installed
- Current publication pointer contract mapped and pointer-sync runtime installed
- Architecture compliance enforced
- Evidence/output shape support mapped
- Contract-test matrix expanded for indicator vectors, eligibility decision rules, finalize gating, and publication diff comparison
- Correction unchanged-rerun outcome support now has direct diff-test proof
- Orchestrated finalize/current-pointer outcome and correction publish-path outcome now have dedicated service-level proof
- Replay verification minimum now has fixture-aware runner/verifier proof path
- Replay reason-code counts now participate in fixture-vs-actual comparison when fixtures declare an expected distribution
- Replay evidence export now preserves explicit expected-vs-actual state, including expected config/hash/reason-code context persisted from fixture verification
- Replay smoke suite minimum now exists to execute built-in replay fixtures as one deterministic operator proof batch
- Replay backfill minimum now exists to execute replay verification across a trading-date range rooted in `market_calendar` and current readable publication pointers, writing a deterministic summary artifact
- Backfill minimum runtime now exists to execute `market-data:daily` across a trading-date range resolved from `market_calendar` and write a deterministic summary artifact
- Ticker mapping miss at ingest is now reason-coded deterministically as invalid-bar evidence instead of fail-fast runtime abort
- Session snapshot minimum runtime now exists to capture optional supplemental session snapshot rows aligned to readable effective trade date and to purge them according to retention policy

## Kontrak MISSING


## Kontrak CONFLICT
- Tidak ada conflict baru yang dibuka pada sesi 28.

## DOC GAP / DOC CONFLICT / DOC SYNC ISSUE
- `DOC SYNC ISSUE`: runtime lokal membuktikan mismatch schema/runtime pada `eod_publications.sealed_at` dan truncation pada `eod_run_events.message`; checkpoint dinaikkan agar status sesi 11 sinkron dengan bugfix nyata.
- `DOC SYNC ISSUE`: checkpoint dinaikkan agar state repo sesi 6 sinkron dengan contract-test minimum dan evidence-export runtime yang kini sudah nyata di code.
- `DOC SYNC ISSUE`: checkpoint dinaikkan agar state repo sesi 7 sinkron dengan replay evidence export runtime yang kini sudah nyata di code.
- `DOC SYNC ISSUE`: checkpoint dinaikkan agar state repo sesi 9 sinkron dengan proof orchestrated finalize/current-pointer outcome dan correction publish-path outcome yang kini sudah nyata di code.
- `DOC SYNC ISSUE`: checkpoint dinaikkan agar state repo sesi 10 sinkron dengan source mode `api` yang kini sudah nyata di code.
- `DOC SYNC ISSUE`: runtime lokal membuktikan code masih menulis `lifecycle_state = FINALIZED` padahal schema owner `eod_runs` hanya mengizinkan `PENDING/RUNNING/FINALIZING/COMPLETED/FAILED/CANCELLED`; checkpoint dinaikkan agar status sesi 12 sinkron dengan bugfix state-machine yang sah.
- `DOC SYNC ISSUE`: runtime lokal membuktikan finalize verification memakai resolver current publication yang terlalu ketat karena mensyaratkan `eod_runs` sudah `SUCCESS/READABLE` sebelum verifikasi final selesai; checkpoint dinaikkan agar status sesi 13 sinkron dengan bugfix pointer-resolution yang memakai current pointer + sealed publication sebagai sumber kebenaran saat post-promote verification.

- `DOC SYNC ISSUE`: correction/reseal replacement path kini memiliki command eksekusi eksplisit dan event outcome correction publish/cancel agar pembuktian runtime dapat dilakukan tanpa memakai rerun normal yang memang harus tertahan.
- `DOC SYNC ISSUE`: repo kini punya replay verifier minimum yang membaca fixture package dan menulis `md_replay_*`, sehingga checkpoint harus dinaikkan agar status replay tidak lagi tertulis sebagai export-only.
- `DOC SYNC ISSUE`: replay smoke fixtures sekarang dikomit di `storage/app/market_data/replay-fixtures/**` agar proof runtime tidak bergantung pada fixture buatan manual yang rawan salah. `missing_file_case` wajib gagal dengan `Replay fixture file missing: expected/missing.json`; bila tidak, sesi replay belum boleh dianggap rapat.
- `DOC SYNC ISSUE`: evidence pack replay sebelumnya hanya mengekspor ringkasan flat + actual reason-code counts, padahal kontrak audit mewajibkan preservation of both expected and actual state. Sesi 19 menutup gap ini dengan expected-context persistence + explicit replay_expected_state/replay_actual_state export.
- `DOC SYNC ISSUE`: ZIP source-of-truth sesi 19 masih kehilangan wiring runtime dari `ReplayVerificationService` ke `ReplayResultRepository` untuk expected replay context, sehingga `expected_reason_code_counts_json` tetap `NULL` walau checkpoint sesi 19 sudah dinaikkan. Sesi 20 menutup mismatch ini dan menambahkan guard test agar regress tidak lolos diam-diam.
- `DOC SYNC ISSUE`: docs mengunci `market-data:backfill` sebagai minimum command, tetapi source repo sesi 21 belum memiliki command/runtime minimumnya. Sesi 22 menutup mismatch ini dengan backfill range runner minimum berbasis `market_calendar` + summary artifact.
- `DOC SYNC ISSUE`: docs mengunci `market-data:session-snapshot` dan `market-data:session-snapshot:purge`, tetapi source repo sesi 22 belum memiliki command/runtime minimumnya. Sesi 23 menutup mismatch ini dengan session snapshot capture minimum berbasis readable current publication + eligibility scope + retention purge summary.
- `DOC SYNC ISSUE`: runtime/source sesi 23 masih memakai default session-snapshot yang melenceng dari kontrak LOCKED (retention 7 hari, scope label `universe_only`, tolerance 5 menit). Sesi 24 menutup mismatch ini dengan default resmi 30 hari, `eligibility_set`, tolerance 3 menit, dan purge summary yang menuliskan apakah cutoff berasal dari `before_date` eksplisit atau retention default.
- `DOC SYNC ISSUE`: kontrak backtest/replay masih menuntut historical replay lintas trading-date yang resumable, tetapi source repo sesi 24 belum punya runtime minimumnya. Sesi 25 menutup mismatch ini dengan `market-data:replay:backfill` berbasis `market_calendar` + current readable publication + summary artifact.

## File Code yang Dibuat/Diubah pada Batch Terakhir
- `app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php`
- `app/Application/MarketData/Services/MarketDataPipelineService.php`
- `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
- `app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php`
- `database/migrations/2026_03_24_000003_add_final_outcome_note_to_eod_dataset_corrections.php`
- `tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php`
- `tests/Unit/MarketData/CorrectionEvidenceExportServiceTest.php`
- `docs/market_data/book/Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `docs/market_data/ops/Audit_Query_Cookbook_LOCKED.md`
- `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md`

## Keputusan Desain Penting
- Finalize post-promote verification kini tidak lagi membaca current publication melalui resolver yang mensyaratkan `eod_runs` sudah `SUCCESS/READABLE`; verification sekarang memakai pointer current + publication current + seal state yang nyata agar outcome `eod_runs` tidak deadlock menahan dirinya sendiri.
- Candidate publication `UNSEALED` kini sah memiliki `sealed_at = NULL`; `sealed_at` baru diisi saat stage seal berhasil.
- Lifecycle state `eod_runs` kini diselaraskan dengan schema owner: `FINALIZING` dipakai saat stage finalize dimulai, dan state akhir run memakai `COMPLETED`/`FAILED`/`CANCELLED` alih-alih nilai liar `FINALIZED`.
- Failure event logging kini memotong `message` ke ringkasan aman dan mendorong detail exception ke `event_payload_json` agar observability tidak pecah karena truncation kolom.
- Jika stage gagal keras setelah owning run terbentuk, run kini ditutup ke `FAILED/NOT_READABLE` alih-alih dibiarkan menggantung di `RUNNING`.
- Current publication sekarang dipromosikan melalui `eod_current_publication_pointer` sebagai owner utama; `eod_publications.is_current` dan `eod_runs.is_current_publication` hanya diperlakukan sebagai mirror state.
- Final `SUCCESS` sekarang tidak boleh terjadi hanya karena seal/hashes ada; harus lolos cutoff policy, publication candidate sudah `SEALED`, dan pointer current tersinkron dengan benar.
- Jika finalize dijalankan sebelum cutoff atau pointer current tidak bisa dibuktikan aman, run tetap `HELD` dengan `publishability_state = NOT_READABLE` dan fallback tetap ke prior readable date bila ada.
- Correction/reseal kini dibuka hanya lewat flow terkontrol: request -> approval -> executing -> resealed -> published/cancelled, dengan baseline current publication disnapshot dulu ke immutable history sebelum publish replacement diizinkan.
- Untuk correction, artifact candidate requested-date tidak lagi menimpa current-state table selama eksekusi; candidate ditulis ke `*_history` publication-bound snapshot lalu baru dipromosikan ke current-state saat publish aman.
- Unchanged-content correction rerun tidak melakukan publication switch; current publication lama dipertahankan dan correction ditutup sebagai non-published audit rerun.
- Evidence export runtime sekarang tersedia untuk run evidence pack dan correction evidence pack minimum melalui command khusus, tanpa mengubah owner contract atau memutihkan state yang belum readable.
- Penambahan adapter `api` untuk public/free API pull dengan retry/backoff/throttle+jitter dasar, configurable field mapping, dan klasifikasi error akuisisi yang tetap reason-coded di event trail.
- Hash determinism kini dipusatkan pada service khusus agar serialization proof bisa diuji langsung tanpa bergantung pada query builder atau event trail.

## Self-Audit Result
- Domain drift check: PASS
- Root ownership check: PASS
- System readiness / assembly check: PASS
- Docs vs code sync check: PARTIAL
- Architecture compliance check: PARTIAL
- Cross-folder sync check: PARTIAL
- Findings classification:
  - defect:
        - full DB-backed integration matrix masih belum penuh
  - editorial:
    - checkpoint sesi 26 dinaikkan agar repo state terbaru untuk registry/ingest bars tercermin akurat
  - claim-boundary yang sah:
    - correction replacement kini sudah punya flow request/approval/reseal/publish yang aman terhadap current-state, sesi 6 menambah run/correction evidence export serta test minimum, sesi 7 menambah replay evidence export runtime, sesi 8 menambah direct proof logic inti, dan sesi 9 menambah proof orchestrated finalize/current-pointer outcome serta correction publish-path outcome, dan sesi 10 menambah source mode `api` berbasis adapter public/free API pull. Namun full DB-backed integration matrix dan final outcome note column khusus masih belum boleh di-claim selesai

## Next Recommended Batch
- Lengkapi full DB-backed integration matrix untuk current-pointer/finalize/correction publish path
- Tutup gap correction final outcome note bila memang tetap diwajibkan oleh owner contract atau tandai `DOC GAP` permanen bila schema owner sengaja tidak menyediakannya
- Audit ulang sinkronisasi docs/code setelah partial contracts yang tersisa dikerjakan

## Blocker Nyata
- Tidak ada blocker desain untuk melanjutkan sesi 27.
- Validasi eksekusi command di container ini tetap terbatas karena `vendor/` tidak disertakan pada ZIP terbaru.

## Artifact History
- ZIP latest: `tradeaxis-api_session25_batch25_replay-backfill-minimum.zip`
- ZIP status: checkpoint source-of-truth terbaru sebelum sesi 26

## Update Log
### Entry 0
- Timestamp: INIT
- Action: file checkpoint dibuat
- Notes: baseline awal sebelum discovery

### Entry 1
- Timestamp: SESSION_1
- Action: authority/readiness/owner contract discovery selesai
- Notes: repo terbukti masih starter Lumen, belum ada runtime market-data

### Entry 2
- Timestamp: SESSION_1_BATCH_01
- Action: bootstrap + config/env + official schema foundation diterapkan
- Notes: batch pertama selesai tanpa drift ke watchlist/execution/portfolio

### Entry 3
- Timestamp: SESSION_2_BATCH_02
- Action: command surface + application orchestration + run/event persistence minimum diterapkan
- Notes: repo kini punya jalur resmi command market-data dan `run_id` context, tetapi compute artifact inti masih belum terisi penuh

### Entry 4
- Timestamp: SESSION_3_BATCH_03
- Action: canonical bars + indicators + eligibility runtime foundation diterapkan
- Notes: repo kini benar-benar menulis artifact inti, namun publication current switch, correction/reseal, tests, dan evidence runtime masih belum ada

### Entry 5
- Timestamp: SESSION_4_BATCH_04
- Action: current publication switch + pointer sync + finalize guard diterapkan
- Notes: final `SUCCESS` kini bergantung pada cutoff policy, sealed candidate publication, dan pointer current yang sinkron; correction/reseal tetap belum dibuka


### Entry 6
- Timestamp: SESSION_5_BATCH_05
- Action: correction request/approval/reseal/publish runtime diterapkan
- Notes: correction candidate requested-date kini staged melalui immutable history snapshot, prior current publication dipreserve sebelum replacement, dan unchanged rerun tidak memaksa publication version switch


### Entry 7
- Timestamp: SESSION_6_BATCH_06
- Action: contract-test minimum + evidence export runtime diterapkan
- Notes: hash determinism kini diuji langsung lewat service khusus; run evidence pack dan correction evidence pack minimum kini bisa diekspor via command tanpa mengklaim replay evidence atau mode API sudah selesai


### Entry 8
- Timestamp: SESSION_7_BATCH_07
- Action: replay evidence export runtime diterapkan
- Notes: repo kini bisa mengekspor replay_result dan replay reason-code summary dari replay proof storage, tetapi runner replay, mode API, dan full contract-test matrix masih belum ada

### Entry 8
- Timestamp: SESSION_8_BATCH_08
- Action: contract-test matrix expanded for indicator vectors, eligibility decisions, finalize gating, and publication diff proof
- Notes: batch ini menutup proof logic inti yang sebelumnya masih tersebar di private method tanpa test langsung; full matrix tetap belum penuh karena orchestrated integration path dan replay/fixture runner belum ada

### Entry 9
- Timestamp: SESSION_9_BATCH_09
- Action: orchestrated finalize/current-pointer outcome dan correction publish-path outcome diekstrak ke service khusus lalu diberi direct unit-test proof
- Notes: batch ini belum menjadi integration test DB penuh; replay runner/verifier dan source mode `api` masih terbuka

### Entry 10
- Timestamp: SESSION_10_BATCH_10
- Action: public/free API source adapter diterapkan
- Notes: mode `api` kini tersedia melalui adapter khusus dengan retry/backoff/throttle+jitter dasar, configurable field mapping, dan klasifikasi error akuisisi yang tetap reason-coded


## Session 15 Update
- Runtime bugfix: correction baseline publication resolver now resolves from `eod_current_publication_pointer` + `eod_publications.is_current=1` + `seal_state=SEALED` without requiring `eod_runs` summary state.
- This closes the false precondition failure where correction run reported that no current sealed publication existed even though current publication + pointer were already present in DB.


## Session 16 correction tracking sync

- Correction publish path now updates `eod_dataset_corrections.status`, `prior_run_id`, `new_run_id`, and `published_at` when a correction is published.
- Correction unchanged path now updates `eod_dataset_corrections.status`, `prior_run_id`, and `new_run_id` when a correction is cancelled due to unchanged artifacts.


## Session 17 replay verification minimum

- Repo terbaru divalidasi ulang terhadap checkpoint sesi 16; correction tracking sync tetap sah dan tidak regress.
- Gap prioritas berikutnya yang benar-benar masih terbuka adalah replay verification minimum: repo sudah bisa export replay evidence dari tabel replay, tetapi belum bisa membentuk proof replay dari run aktual vs fixture package.
- Batch sesi 17 menutup gap ini dengan command `market-data:replay:verify`, service pembanding fixture-aware, dan repository persistence replay result.
- Evidence strength naik dari export-only replay support menjadi replay verification minimum yang dapat menulis `md_replay_daily_metrics` dan `md_replay_reason_code_counts` secara eksplisit.
- Belum ada klaim palsu untuk full historical replay orchestration lintas date-range; yang ditutup pada sesi ini hanya minimum verifier/proof writer yang sah terhadap kontrak replay.

## Session 18 Update
- Replay verifier kini membandingkan `md_replay_reason_code_counts` aktual terhadap fixture-declared expected distribution bila fixture menyediakan `expected/expected_reason_code_counts.json`.
- Empty expected reason-code set kini menjadi kontrak eksplisit, bukan asumsi implisit; `valid_case` smoke fixture dikomit ulang untuk membuktikan jalur kosong yang sah.
- Unit-test replay kini mencakup mismatch khusus reason-code counts agar replay proof tidak hanya berhenti di status/date/seal/hash/row-count saja.


## Session 19 Update
- Replay verifier kini mempersist expected config/hash/reason-code context ke `md_replay_daily_metrics` agar evidence export tidak kehilangan fixture expectation setelah verify selesai.
- Replay evidence export kini menghasilkan `replay_expected_state.json` dan `replay_actual_state.json` selain `replay_result.json`, sehingga kontrak evidence pack replay preserving both expected and actual state tertutup lebih rapat.


## Session 20 Update
- Source-of-truth ZIP sesi 19 divalidasi ulang terhadap runtime manual dan terbukti masih kehilangan wiring payload expected replay context dari `ReplayVerificationService` ke `ReplayResultRepository`.
- Sesi 20 menutup bug itu dengan mengirim expected config/hash/reason-code payload penuh ke proof storage, menormalkan reason-code fixture `count`/`reason_count` secara konsisten, dan memperketat unit-test replay agar expected context persistence benar-benar terjaga.
- Batch ini sengaja tidak membuka replay range runner baru karena dependency sesi 19 belum rapat pada source repo terbaru.


## Session 21 Update
- Added `market-data:replay:smoke` as the minimum built-in replay suite runner for the committed smoke fixtures under `storage/app/market_data/replay-fixtures/**`.
- The smoke suite records per-case expected vs observed outcomes and writes `replay_smoke_suite_summary.json`, while exporting replay evidence for successful positive cases.
- This closes the operational gap where replay fixture proof depended on manual one-by-one command execution even though the fixture family set was already committed in-repo.


## Session 22 Update
- Added `market-data:backfill {start_date} {end_date}` so the locked minimum command surface is no longer missing from runtime.
- Backfill minimum resolves trading dates from `market_calendar`, runs `market-data:daily` semantics per trading day, writes `market_data_backfill_summary.json`, and returns non-zero when the range does not pass.


## Session 23 Update
- Added `market-data:session-snapshot` as the minimum optional supplemental snapshot command aligned to readable effective trade date, scoped from `eod_eligibility`, and persisted into `md_session_snapshots` with deterministic summary evidence.
- Added `market-data:session-snapshot:purge` as the minimum retention purge command for supplemental snapshot storage, with summary artifact output.


## Session 24 Update
- Session snapshot defaults kini sinkron dengan kontrak LOCKED: retention default 30 hari, scope label default `eligibility_set`, dan slot tolerance default 3 menit.
- Purge summary kini menuliskan `before_date` eksplisit vs `retention_days` default agar operator proof tidak ambigu ketika purge dijalankan tanpa cutoff manual.


## Session 25 Update
- Added `market-data:replay:backfill` as the minimum historical replay range runner rooted in `market_calendar` and current readable publication pointers.
- Replay backfill minimum runs fixture-aware replay verification per trading date, exports replay evidence for successful cases, writes `market_data_replay_backfill_summary.json`, and exits non-zero when the range does not pass.


## Session 26 Update
- Source-of-truth ZIP sesi 25 dibaca ulang dan divalidasi terhadap checkpoint; replay backfill minimum tetap sah dan tidak regress.
- Batch prioritas berikutnya yang benar-benar belum rapat adalah gap registry/ingest bars lama untuk `ticker_code` yang gagal dipetakan ke ticker master.
- Sesi 26 menutup gap ini dengan reason code resmi `BAR_TICKER_MAPPING_MISSING`, sinkronisasi registry/seed/error taxonomy/source mapping contract, serta perubahan ingest agar source row unmapped dicatat ke `eod_invalid_bars` tanpa mematikan seluruh ingest.
- Added unit-test minimum untuk membuktikan satu row valid tetap dipersist sementara row unmapped menjadi invalid evidence deterministik.


## Session 27 Update
- Manual local proof from the user showed the new session-26 unit test passed only after binding Lumen `config` into pure PHPUnit test container, while adjacent market-data unit tests with the same runtime dependency still failed before reaching business assertions.
- Sesi 27 menutup gap bootstrap test itu dengan helper bersama `tests/Support/InteractsWithMarketDataConfig.php` dan menyinkronkan market-data unit tests yang memanggil service/adapter berbasis `config()` agar tidak lagi pecah pada `Target class [config] does not exist`.
- Scope tetap sempit: ini bukan fitur market-data baru, melainkan hardening proof layer supaya unit-test minimum untuk backfill/replay/session-snapshot/source adapter bisa benar-benar mengeksekusi logika yang diklaim.


## Session 28 Update
- Menutup `DOC GAP` lama pada correction minimum metadata dengan menambahkan kolom schema owner `eod_dataset_corrections.final_outcome_note` beserta migration incremental untuk repo existing.
- Correction publish/cancel runtime kini menulis operator-readable outcome note terpisah dari status, evidence export correction kini mengekspor note itu, dan direct proof layer diperketat agar note tidak hilang diam-diam.
