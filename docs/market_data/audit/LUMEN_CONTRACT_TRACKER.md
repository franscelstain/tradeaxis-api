# LUMEN_CONTRACT_TRACKER

## File Role
- Fungsi file ini: contract matrix market-data yang menentukan apa yang sudah rapat dan apa yang belum.
- Fokus file ini:
  - contract item utama;
  - status item (`DONE`, `PARTIAL`, `MISSING`, `BLOCKED`, `CONFLICT`);
  - bukti implementasi/proof;
  - session terakhir yang mengubah status item;
  - next required action.
- File ini bukan timeline naratif utama. Timeline utama berada di `LUMEN_IMPLEMENTATION_STATUS.md`.

## Writing Rules
- Gunakan `CONTRACT ITEM` sebagai unit utama.
- Gunakan `SESSION` hanya sebagai referensi singkat:
  - `LAST UPDATED SESSION`
  - `CLOSED BY SESSION`
  - `EVIDENCE SESSION`
- Jangan menulis ulang cerita sesi panjang di file ini.
- Setiap contract item minimal harus punya:
  - `STATUS`
  - `OWNER AREA`
  - `LAST UPDATED SESSION`
  - `EVIDENCE`
  - `OPEN GAP`
  - `NEXT REQUIRED ACTION`
- Status yang boleh dipakai:
  - `DONE`
  - `PARTIAL`
  - `MISSING`
  - `BLOCKED`
  - `CONFLICT`
- `DONE` di level session tidak otomatis berarti parent contract `DONE`.

## Resume Rule For New Chat
- Gunakan ZIP terbaru sebagai source of truth.
- Baca file ini dan `LUMEN_IMPLEMENTATION_STATUS.md` terlebih dahulu.
- Validasi status tracker terhadap source code/test terbaru.
- Ambil batch prioritas dari item `PARTIAL`, `MISSING`, atau `BLOCKED` yang dependency-nya sudah rapat.
- Jangan membuka area baru bila parent contract yang menaungi area itu masih bolong.

## Contract Selection Rule
- Prioritaskan contract `MISSING` yang menjadi dependency langsung batch berikutnya.
- Bila tidak ada `MISSING` yang sah dibuka, ambil `PARTIAL` dengan gap paling sempit namun paling load-bearing terhadap final readiness.
- Jangan memilih batch baru hanya karena menarik; pilih yang paling dekat menutup parent contract matrix.

## Reference Note
- Canonical session batch IDs dikelola di `LUMEN_IMPLEMENTATION_STATUS.md`.
- Gunakan label session/batch di sana sebagai referensi resmi saat mengisi `LAST UPDATED SESSION` atau `EVIDENCE SESSION`.

## CONTRACT ITEM 1 — Foundation / bootstrap / ownership
- STATUS: DONE
- OWNER AREA: foundation / bootstrap / root ownership
- LAST UPDATED SESSION: `session1_batch1_market-data-foundation`
- EVIDENCE:
  - root ownership extracted from `docs/README.md`;
  - market-data scope locked;
  - baseline Lumen config/env wiring enabled.
- OPEN GAP: none
- NEXT REQUIRED ACTION: none

## CONTRACT ITEM 2 — Core runtime artifact lifecycle
- STATUS: PARTIAL
- OWNER AREA: bars / indicators / eligibility / publication
- LAST UPDATED SESSION: `session48_batch48_db_backed_run_current_mirror_mismatch_guard_minimum`
- EVIDENCE:
  - canonical bars/indicators/eligibility runtime installed;
  - publication current-switch and pointer-sync runtime installed;
  - unchanged correction path now proven locally after removing `publication_id` from content-hash payload;
  - DB-backed/integration proof now covers changed-content correction that reaches finalize and then fails during promote/current-switch, producing `HELD/NOT_READABLE` while preserving prior current publication and fallback effective date;
  - DB-backed/integration proof now also covers changed-content correction where baseline/current-pointer mismatch emerges during promote/current-switch, preserving prior current publication and fallback effective date without silently switching readability;
  - baseline/current-pointer mismatch proof was initially invalidated by helper side effects, then repaired by patching `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` so the mismatch is raised without mutating pointer/current publication state before the exception path;
  - final local validation after the patch passes with `vendor\bin\phpunit --filter baseline_pointer_mismatch` -> `OK (1 test, 26 assertions)` and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (5 tests, 114 assertions)`;
  - DB-backed/integration proof now also covers correction request without approval, proving the pipeline rejects before owning run creation and preserves correction state plus current publication/pointer without creating a candidate publication;
  - DB-backed/integration proof now also covers correction reseal failure, proving seal-write failure leaves the prior current publication/pointer intact while the candidate publication stays `UNSEALED` and non-current and the run becomes `FAILED` / `NOT_READABLE` before finalize;
  - DB-backed/integration proof now also covers correction history-promotion failure during finalize, proving a `SEALED` candidate publication can remain non-current while prior current publication/pointer stay intact and finalize resolves as `HELD` / `NOT_READABLE` / `COMPLETED`;
  - session 42 history-promotion proof was initially mis-asserted as an exception/failed path, then repaired by patching `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` so the test matches the actual held finalize outcome (`RESEALED`, `RUN_FINALIZED`, `WARN`, `RUN_LOCK_CONFLICT`) without falsely expecting `CORRECTION_PUBLISHED`;
  - final local validation for the reseal-failure path passes with `vendor\bin\phpunit --filter reseal_failure` -> `OK (1 test, 30 assertions)`, `vendor\bin\phpunit --filter leaves_candidate_non_current` -> `OK (1 test, 30 assertions)`, and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (7 tests, 161 assertions)`;
  - final local validation for the history-promotion failure path passes with `vendor\bin\phpunit --filter history_promotion_failure` -> `OK (1 test, 29 assertions)`, `vendor\bin\phpunit --filter candidate_sealed_non_current` -> `OK (1 test, 29 assertions)`, and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (8 tests, 190 assertions)`;
  - DB-backed/integration proof now also covers approved correction without a current sealed publication baseline, proving the pipeline rejects before owning run creation and preserves correction state plus the absence of publication/pointer side effects for the target trade date;
  - DB-backed/integration proof now also covers approved correction with a malformed pointer that references an `UNSEALED` non-current publication, proving the pipeline rejects before owning run creation and preserves correction approval state while leaving the malformed pointer/publication rows untouched;
  - DB-backed/integration proof now also covers approved correction with a current pointer row that references a missing publication row, proving the pipeline rejects before owning run creation and preserves correction approval state plus the corrupted pointer state without creating a new publication or run/event side effects;
  - DB-backed/integration proof now also covers approved correction with a baseline publication that is `SEALED` and `is_current = 1` but whose originating run is `HELD` / `NOT_READABLE` and `is_current_publication = 0`, proving correction baseline resolution now rejects non-readable baseline runs before owning run creation and preserves correction approval state plus the incident baseline pointer/publication rows untouched;
  - DB-backed/integration proof now also covers approved correction with a current pointer row whose pointed publication belongs to a different trade date, proving correction baseline resolution now rejects pointer/publication trade-date mismatch before owning run creation and preserves correction approval state plus the incident pointer/publication rows untouched;
  - repository integration proof now also covers pointer/publication trade-date mismatch for general current/publication resolution, proving `findPointerResolvedPublicationForTradeDate`, `findCurrentPublicationForTradeDate`, and `findCorrectionBaselinePublicationForTradeDate` all fail safe to `null` when `pub.trade_date != ptr.trade_date`;
  - final local validation for session 47 required two follow-up repairs after the initial repo patch: `AbstractMarketDataCommand::renderRunSummary(...)` now renders `reason_code` / `notes` when present, and `MarketDataPipelineServiceTest::test_complete_finalize_keeps_resealed_when_publication_promotion_throws_lock_conflict()` now matches the `STAGE_STARTED` event payload safely;
  - final local full validation after those repairs passes with `vendor\bin\phpunit` -> `OK (75 tests, 533 assertions)`.
  - repository current/publication resolution now also treats run-current mirror mismatch as unsafe by requiring `run.is_current_publication = 1` for general current read resolution and latest-readable fallback resolution whenever an owning run row exists;
  - DB-backed/integration proof now also covers approved correction with a baseline publication/current pointer whose owning run is `SUCCESS` / `READABLE` but `is_current_publication = 0`, proving correction baseline resolution still rejects before owning run creation and preserves correction approval state plus incident rows untouched;
  - repository integration proof now also covers run-current mirror mismatch for `findPointerResolvedPublicationForTradeDate`, `findCurrentPublicationForTradeDate`, `findCorrectionBaselinePublicationForTradeDate`, and `findLatestReadablePublicationBefore`, all failing safe to `null` when pointer/publication mirror state disagrees materially with `eod_runs.is_current_publication`;
  - container syntax proof for session 48 passes with `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`, `php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`, and `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`.
- OPEN GAP:
  - broader conflict/error matrix at artifact/runtime level is still not fully closed beyond the current minimum changed-content promotion/conflict proof set, reseal-failure minimum, and the now-closed run-current-mirror mismatch guard minimum.
- NEXT REQUIRED ACTION:
  - continue strengthening broader correction conflict/error matrix without opening unrelated area, with priority on remaining DB-backed/integration negative paths outside the covered minimum;
  - for `SESI 49`, continue with the next load-bearing DB-backed correction/runtime gap now that `pointer/publication trade-date mismatch` and `run-current-mirror mismatch` are already closed and checkpointed.

## CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle
- STATUS: PARTIAL
- OWNER AREA: correction runtime and finalize outcomes
- LAST UPDATED SESSION: `session48_batch48_db_backed_run_current_mirror_mismatch_guard_minimum`
- EVIDENCE:
  - correction request / approval / reseal / publish runtime installed;
  - correction final outcome note installed;
  - correction finalize ordering fixed;
  - unchanged correction rerun now proven locally to end as `CANCELLED`;
  - held/conflict operator proof now proven locally;
  - explicit lock-conflict/promotion-error negative proof now added across outcome service, pipeline finalize, and correction command surface;
  - DB-backed/integration proof now covers changed-content correction that reaches publish path preconditions and then fails at promote/current-switch, preserving prior current publication and leaving correction in `RESEALED`;
  - DB-backed/integration proof now also covers correction baseline/current-pointer mismatch during promote/current-switch, preserving prior current publication and leaving correction in `RESEALED`;
  - baseline/current-pointer mismatch proof was initially invalidated by helper side effects, then repaired by patching `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` so the mismatch is raised without mutating pointer/current publication state before the exception path;
  - final local validation after the patch passes with `vendor\bin\phpunit --filter baseline_pointer_mismatch` -> `OK (1 test, 26 assertions)` and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (5 tests, 114 assertions)`;
  - DB-backed/integration proof now also covers correction request without approval, proving the pipeline rejects before owning run creation and preserves correction state plus current publication/pointer without creating a candidate publication;
  - DB-backed/integration proof now also covers correction reseal failure, proving seal-write failure aborts correction before safe publication, leaves the correction in `EXECUTING`, and preserves prior current publication/pointer while partial candidate state remains `UNSEALED` and non-current;
  - DB-backed/integration proof now also covers correction history-promotion failure during finalize, proving correction remains `RESEALED`, preserves prior current publication/pointer, leaves the candidate publication `SEALED` but non-current, and resolves through held finalize rather than silent publish;
  - DB-backed/integration proof now also covers approved correction without a current sealed publication baseline, proving the pipeline rejects before owning run creation and preserves the correction in `APPROVED` state without creating candidate publication, pointer state, or run/event side effects;
  - DB-backed/integration proof now also covers approved correction with a current pointer row whose pointed publication belongs to a different trade date, proving correction baseline resolution rejects the trade-date mismatch before owning run creation and leaves correction state plus incident rows untouched;
  - final local full validation after session 47 follow-up repairs passes with `vendor\bin\phpunit` -> `OK (75 tests, 533 assertions)`;
  - DB-backed/integration proof now also covers approved correction with a baseline publication/current pointer whose owning run is still `SUCCESS` / `READABLE` but `is_current_publication = 0`, proving correction lifecycle refuses to start a new owning run from a materially inconsistent baseline even when pointer + publication appear readable at first glance;
  - manual runtime verification after session 38 confirms changed-content publish path with `correction_id=24` and unchanged-content cancel path with `correction_id=25` -> `run_id=54` -> `CANCELLED` / `SUCCESS` / `READABLE`, while current publication pointer remains on `run_id=53`.
- OPEN GAP:
  - broader correction conflict/error matrix still not fully closed beyond the current minimum proof set, especially additional DB-backed/integration variants outside the currently covered approval-gate minimum, missing-baseline guard minimum, malformed-baseline-pointer guard minimum, missing-publication-pointer guard minimum, non-readable-baseline-run minimum, pointer/publication trade-date mismatch minimum, run-current-mirror mismatch minimum, reseal-failure minimum, history-promotion failure minimum, the two promote/current-switch conflict modes, and any remaining broader non-promotion failure modes.
- NEXT REQUIRED ACTION:
  - expand matrix for additional conflict/error scenarios that still remain uncovered;
  - for `SESI 49`, prefer the next remaining correction/runtime DB-backed scenario whose reject/hold behavior is already explicit in owner-doc/runtime evidence, without reopening the now-closed trade-date mismatch or run-current-mirror guard.

## CONTRACT ITEM 4 — Replay verification / evidence / smoke / backfill
- STATUS: PARTIAL
- OWNER AREA: replay verification and evidence
- LAST UPDATED SESSION: `session40_batch40_db_backed_correction_requires_approval_integration_minimum`
- EVIDENCE:
  - replay verifier minimum exists;
  - reason-code alignment exists;
  - expected/actual evidence export exists;
  - replay smoke suite exists;
  - replay backfill minimum exists;
  - manual runtime verification after session 38 confirms `market-data:replay:backfill 2026-03-17 2026-03-17 --fixture_case=valid_case -vvv` with `all_passed=1`, `expected=MATCH`, `observed=MATCH`, `passed=1`.
- OPEN GAP:
  - replay area still belongs to the broader parent test/evidence matrix that is not fully closed at project level.
- NEXT REQUIRED ACTION:
  - revisit only if replay becomes the next highest-priority remaining partial after correction/tests/ops matrix narrows further.

## CONTRACT ITEM 5 — Session snapshot runtime
- STATUS: PARTIAL
- OWNER AREA: session snapshot ops/runtime
- LAST UPDATED SESSION: `session38_batch38_db_backed_changed_content_promotion_failure_integration_minimum`
- EVIDENCE:
  - session snapshot minimum runtime exists;
  - purge runtime exists;
  - defaults synced to locked contract;
  - manual runtime verification after session 38 confirms `market-data:session-snapshot:purge --before_date=2026-03-18 -vvv` with `deleted_rows=0` and no abnormal side effects.
- OPEN GAP:
  - still part of the broader ops matrix; not yet final-ready as a fully closed parent area.
- NEXT REQUIRED ACTION:
  - revisit only if ops matrix becomes the next highest-priority remaining partial.

## CONTRACT ITEM 6 — Operator command surface / ops proof
- STATUS: PARTIAL
- OWNER AREA: command surface / operator summaries
- LAST UPDATED SESSION: `session48_batch48_db_backed_run_current_mirror_mismatch_guard_minimum`
- EVIDENCE:
  - correction command proof added in session 30;
  - ops command proof added in session 31;
  - correction cancelled summary proof added in session 35;
  - correction held/resealed summary proof executed locally in session 36;
  - explicit correction lock-conflict operator summary proof added in session 37;
  - session 47 follow-up repair now makes `AbstractMarketDataCommand::renderRunSummary(...)` surface `reason_code` and `notes` when present, so held/resealed correction command output stays aligned with lock-conflict proof expectations;
  - final local full validation after the follow-up repair passes with `vendor\bin\phpunit` -> `OK (75 tests, 533 assertions)`;
  - manual runtime verification after session 38 confirms command surface remains healthy for `PUBLISHED` and `CANCELLED` finalize outcomes.
- OPEN GAP:
  - broader ops failure/retry/scheduler matrix is still not fully proven.
- NEXT REQUIRED ACTION:
  - continue into broader scheduler/retry/failure operator proof only when selected as the next highest-priority batch.

## CONTRACT ITEM 7 — DB-backed integration proof
- STATUS: PARTIAL
- OWNER AREA: repository + pipeline integration
- LAST UPDATED SESSION: `session48_batch48_db_backed_run_current_mirror_mismatch_guard_minimum`
- EVIDENCE:
  - repository integration proof added in session 32;
  - DB-backed pipeline integration minimum added in session 33;
  - executed local proof synced in session 34;
  - unchanged correction DB-backed path closed in session 35;
  - changed-content correction + promotion-failure DB-backed integration minimum added in session 38;
  - changed-content correction + baseline/current-pointer mismatch DB-backed integration minimum added in session 39;
  - baseline/current-pointer mismatch proof was initially invalidated by helper side effects, then repaired by patching `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`;
  - final local validation after the patch passes with `vendor\bin\phpunit --filter baseline_pointer_mismatch` -> `OK (1 test, 26 assertions)` and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (5 tests, 114 assertions)`;
  - DB-backed/integration proof now also covers correction request without approval, proving the pipeline rejects before owning run creation and preserves correction state plus current publication/pointer without creating a candidate publication;
  - final local validation for the approval-gate path passes with `vendor\bin\phpunit --filter without_approval` -> `OK (1 test, 17 assertions)`, `vendor\bin\phpunit --filter rejects_before_run_creation` -> `OK (1 test, 17 assertions)`, and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (6 tests, 131 assertions)`;
  - DB-backed/integration proof now also covers correction reseal failure, proving seal-write failure aborts correction before finalize, leaves the correction in `EXECUTING`, keeps prior current publication/pointer intact, and leaves candidate publication `UNSEALED` plus non-current;
  - DB-backed/integration proof now also covers correction history-promotion failure during finalize, proving failure after reseal but before safe publish/current-switch leaves the prior current publication/pointer intact while the candidate publication stays `SEALED` and non-current and finalize resolves as `HELD` / `NOT_READABLE` / `COMPLETED`;
  - session 42 history-promotion proof was initially invalidated by stale assertion expectations, then repaired by patching `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` to assert the actual held finalize outcome instead of an exception/failure path;
  - final local validation for the history-promotion failure path passes with `vendor\bin\phpunit --filter history_promotion_failure` -> `OK (1 test, 29 assertions)`, `vendor\bin\phpunit --filter candidate_sealed_non_current` -> `OK (1 test, 29 assertions)`, and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (8 tests, 190 assertions)`;
  - DB-backed/integration proof now also covers approved correction without a current sealed publication baseline, proving the pipeline rejects before owning run creation and preserves correction state plus the absence of publication/pointer side effects for the target trade date;
  - DB-backed/integration proof now also covers approved correction with a malformed pointer that references an `UNSEALED` non-current publication, proving the pipeline rejects before owning run creation and preserves correction approval state while leaving the malformed pointer/publication rows untouched;
  - DB-backed/integration proof now also covers approved correction with a current pointer row that references a missing publication row, proving the pipeline rejects before owning run creation and preserves correction approval state plus the corrupted pointer state without creating a new publication or run/event side effects;
  - DB-backed/integration proof now also covers approved correction with a baseline publication that is `SEALED` and `is_current = 1` but whose originating run is `HELD` / `NOT_READABLE` and `is_current_publication = 0`, proving correction baseline resolution now rejects non-readable baseline runs before owning run creation and preserves correction approval state plus the incident baseline pointer/publication rows untouched;
  - DB-backed/integration proof now also covers approved correction with a current pointer row whose pointed publication belongs to a different trade date, proving correction baseline resolution now rejects pointer/publication trade-date mismatch before owning run creation and preserves correction approval state plus the incident pointer/publication rows untouched;
  - repository integration proof now also covers pointer/publication trade-date mismatch for general current/publication resolution, proving `findPointerResolvedPublicationForTradeDate`, `findCurrentPublicationForTradeDate`, and `findCorrectionBaselinePublicationForTradeDate` all fail safe to `null` when `pub.trade_date != ptr.trade_date`;
  - final local validation after session 47 follow-up repairs passes with `vendor\bin\phpunit` -> `OK (75 tests, 533 assertions)`;
  - DB-backed/integration proof now also covers approved correction with a baseline publication/current pointer whose owning run is `SUCCESS` / `READABLE` but `is_current_publication = 0`, proving baseline resolution rejects materially inconsistent run mirror state before owning run creation and preserves correction approval state;
  - repository integration proof now also covers run-current mirror mismatch for `findPointerResolvedPublicationForTradeDate`, `findCurrentPublicationForTradeDate`, `findCorrectionBaselinePublicationForTradeDate`, and `findLatestReadablePublicationBefore`, proving all four resolver paths now fail safe to `null`;
  - session 48 container proof passes with `php -l app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php`, `php -l tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php`, and `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`;
  - final local validation for the missing-baseline guard path passes with `vendor\bin\phpunit --filter without_current_baseline` -> `OK (1 test, 11 assertions)`, `vendor\bin\phpunit --filter preserves_approval_state` -> `OK (1 test, 11 assertions)`, and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (9 tests, 201 assertions)`;
  - final local validation for the missing-publication-pointer guard path passes with `vendor\bin\phpunit --filter missing_publication` -> `OK (1 test, 13 assertions)`, `vendor\bin\phpunit --filter preserves_approval_state` -> `OK (3 tests, 40 assertions)`, and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (11 tests, 230 assertions)`;
  - `vendor\bin\phpunit --filter requires_approval` returns `No tests executed!` because the filter string does not match the test method name, not because of a runtime failure;
  - manual runtime verification after session 38 confirms local DB behavior still aligns for publish path, unchanged cancel path, purge, and replay-backfill minimum.
- OPEN GAP:
  - broader DB-backed conflict/error integration matrix is still not fully covered outside the current minimum approval-gate path, missing-baseline guard path, malformed-baseline-pointer guard path, missing-publication-pointer guard path, non-readable-baseline-run guard path, pointer/publication trade-date mismatch guard path, run-current-mirror mismatch guard path, reseal-failure path, history-promotion failure path, and changed-content promote/current-switch conflict paths.
- NEXT REQUIRED ACTION:
  - extend only into remaining load-bearing DB-backed matrix gaps after the now-closed pointer/publication trade-date mismatch and run-current-mirror mismatch guard minimums.

## CONTRACT ITEM 8 — Final readiness gate
- STATUS: MISSING
- OWNER AREA: project-level readiness
- LAST UPDATED SESSION: `session36_batch36_correction_conflict_held_operator_proof_minimum`
- EVIDENCE:
  - none yet for full project closure.
- OPEN GAP:
  - parent contracts still have `PARTIAL` status;
  - final done gate is not yet satisfied.
- NEXT REQUIRED ACTION:
  - keep selecting the highest-priority remaining `PARTIAL` contract until parent matrix is sufficiently closed.

## Reconstruction Note
- Sessions 1–14 are reconstructed minimum from canonical ZIP batch names and later checkpoint sync.
- Sessions 15–39 are reconstructed from canonical ZIP batch names plus checkpoint evidence.
- If older ZIP/source details become available later, enrich evidence without changing canonical session labels.

## Tracker Summary
- Session 35 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 36 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 37 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 38 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 39 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 40 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 41 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 42 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 43 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 44 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 45 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 46 is DONE at session level for the grounded non-readable-baseline-run guard batch.
- Session 47 is DONE at session level for the grounded pointer/publication trade-date mismatch guard batch; owner-doc `LOCKED` wording was explicit, repository pointer resolution is now synchronized to it, final local follow-up repairs are reflected, and the batch is now checkpoint material.
- Session 48 is DONE at session level for the grounded run-current-mirror mismatch guard batch; current/publication resolution and latest-readable fallback resolution now treat `eod_runs.is_current_publication` disagreement as unsafe, and correction baseline proof now covers this variant before owning run creation.
- Next batch must be selected from the highest-priority remaining `PARTIAL` or `MISSING` contract item.
