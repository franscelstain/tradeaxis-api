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
- LAST UPDATED SESSION: `session40_batch40_db_backed_correction_requires_approval_integration_minimum`
- EVIDENCE:
  - canonical bars/indicators/eligibility runtime installed;
  - publication current-switch and pointer-sync runtime installed;
  - unchanged correction path now proven locally after removing `publication_id` from content-hash payload;
  - DB-backed/integration proof now covers changed-content correction that reaches finalize and then fails during promote/current-switch, producing `HELD/NOT_READABLE` while preserving prior current publication and fallback effective date;
  - DB-backed/integration proof now also covers changed-content correction where baseline/current-pointer mismatch emerges during promote/current-switch, preserving prior current publication and fallback effective date without silently switching readability;
  - baseline/current-pointer mismatch proof was initially invalidated by helper side effects, then repaired by patching `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` so the mismatch is raised without mutating pointer/current publication state before the exception path;
  - final local validation after the patch passes with `vendor\bin\phpunit --filter baseline_pointer_mismatch` -> `OK (1 test, 26 assertions)` and `vendor\bin\phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> `OK (5 tests, 114 assertions)`;
  - DB-backed/integration proof now also covers correction request without approval, proving the pipeline rejects before owning run creation and preserves correction state plus current publication/pointer without creating a candidate publication;
  - manual runtime verification after session 38 confirms changed-content correction publish path with `correction_id=24` -> `run_id=53` -> `PUBLISHED` / `SUCCESS` / `READABLE`;
  - local full PHPUnit proof after session 35: `60 tests / 306 assertions`.
- OPEN GAP:
  - broader conflict/error matrix at artifact/runtime level is still not fully closed beyond the current minimum changed-content promotion/conflict proof set.
- NEXT REQUIRED ACTION:
  - continue strengthening broader correction conflict/error matrix without opening unrelated area, with priority on remaining DB-backed/integration negative paths outside the covered minimum.

## CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle
- STATUS: PARTIAL
- OWNER AREA: correction runtime and finalize outcomes
- LAST UPDATED SESSION: `session40_batch40_db_backed_correction_requires_approval_integration_minimum`
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
  - manual runtime verification after session 38 confirms changed-content publish path with `correction_id=24` and unchanged-content cancel path with `correction_id=25` -> `run_id=54` -> `CANCELLED` / `SUCCESS` / `READABLE`, while current publication pointer remains on `run_id=53`;
  - full local PHPUnit after session 36: `61 tests / 313 assertions`;
  - session 37-39 repo ZIPs did not include `vendor/`, so container-side proof for changed files stayed at PHP syntax lint, while final source of truth for session 39 is the locally validated post-patch repo state.
- OPEN GAP:
  - broader correction conflict/error matrix still not fully closed beyond the current minimum proof set, especially additional DB-backed/integration variants outside the currently covered approval-gate minimum plus the two promote/current-switch conflict modes, and broader non-promotion failure modes.
- NEXT REQUIRED ACTION:
  - expand matrix for additional conflict/error scenarios that still remain uncovered.

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
- LAST UPDATED SESSION: `session37_batch37_correction_lock_conflict_negative_proof_minimum`
- EVIDENCE:
  - correction command proof added in session 30;
  - ops command proof added in session 31;
  - correction cancelled summary proof added in session 35;
  - correction held/resealed summary proof executed locally in session 36;
  - explicit correction lock-conflict operator summary proof added in session 37;
  - manual runtime verification after session 38 confirms command surface remains healthy for `PUBLISHED` and `CANCELLED` finalize outcomes;
  - last fully executed local proof remains session 36: `CorrectionCommandsTest.php` `6 tests / 31 assertions`; session 37 adds syntax-linted test coverage in repo.
- OPEN GAP:
  - broader ops failure/retry/scheduler matrix is still not fully proven.
- NEXT REQUIRED ACTION:
  - continue into broader scheduler/retry/failure operator proof only when selected as the next highest-priority batch.

## CONTRACT ITEM 7 — DB-backed integration proof
- STATUS: PARTIAL
- OWNER AREA: repository + pipeline integration
- LAST UPDATED SESSION: `session40_batch40_db_backed_correction_requires_approval_integration_minimum`
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
  - `vendor\bin\phpunit --filter requires_approval` returns `No tests executed!` because the filter string does not match the test method name, not because of a runtime failure;
  - manual runtime verification after session 38 confirms local DB behavior still aligns for publish path, unchanged cancel path, purge, and replay-backfill minimum.
- OPEN GAP:
  - broader DB-backed conflict/error integration matrix is still not fully covered outside the current minimum approval-gate path and changed-content promote/current-switch conflict paths.
- NEXT REQUIRED ACTION:
  - extend only into remaining load-bearing DB-backed matrix gaps.

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
- Next batch must be selected from the highest-priority remaining `PARTIAL` or `MISSING` contract item.