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
- LAST UPDATED SESSION: `session38_batch38_db_backed_changed_content_promotion_failure_integration_minimum`
- EVIDENCE:
  - canonical bars/indicators/eligibility runtime installed;
  - publication current-switch and pointer-sync runtime installed;
  - unchanged correction path now proven locally after removing `publication_id` from content-hash payload;
  - DB-backed/integration proof now covers changed-content correction that reaches finalize and then fails during promote/current-switch, producing `HELD/NOT_READABLE` while preserving prior current publication and fallback effective date;
  - local full PHPUnit proof after session 35: `60 tests / 306 assertions`.
- OPEN GAP:
  - broader conflict/error matrix at artifact/runtime level is still not fully closed beyond the current minimum changed-content promotion-failure proof.
- NEXT REQUIRED ACTION:
  - continue strengthening broader correction conflict/error matrix without opening unrelated area, with priority on remaining DB-backed/integration negative paths outside the covered minimum.

## CONTRACT ITEM 3 — Correction / reseal / publish / cancel lifecycle
- STATUS: PARTIAL
- OWNER AREA: correction runtime and finalize outcomes
- LAST UPDATED SESSION: `session38_batch38_db_backed_changed_content_promotion_failure_integration_minimum`
- EVIDENCE:
  - correction request / approval / reseal / publish runtime installed;
  - correction final outcome note installed;
  - correction finalize ordering fixed;
  - unchanged correction rerun now proven locally to end as `CANCELLED`;
  - held/conflict operator proof now proven locally;
  - explicit lock-conflict/promotion-error negative proof now added across outcome service, pipeline finalize, and correction command surface;
  - DB-backed/integration proof now covers changed-content correction that reaches publish path preconditions and then fails at promote/current-switch, preserving prior current publication and leaving correction in `RESEALED`;
  - full local PHPUnit after session 36: `61 tests / 313 assertions`;
  - session 37-38 repo ZIPs did not include `vendor/`, so only PHP syntax lint could be executed for changed files in these sessions.
- OPEN GAP:
  - broader correction conflict/error matrix still not fully closed beyond the current minimum proof set, especially additional DB-backed/integration variants and non-promotion failure modes.
- NEXT REQUIRED ACTION:
  - expand matrix for additional conflict/error scenarios that still remain uncovered.

## CONTRACT ITEM 4 — Replay verification / evidence / smoke / backfill
- STATUS: PARTIAL
- OWNER AREA: replay verification and evidence
- LAST UPDATED SESSION: `session25_batch25_replay-backfill-minimum`
- EVIDENCE:
  - replay verifier minimum exists;
  - reason-code alignment exists;
  - expected/actual evidence export exists;
  - replay smoke suite exists;
  - replay backfill minimum exists.
- OPEN GAP:
  - replay area still belongs to the broader parent test/evidence matrix that is not fully closed at project level.
- NEXT REQUIRED ACTION:
  - revisit only if replay becomes the next highest-priority remaining partial after correction/tests/ops matrix narrows further.

## CONTRACT ITEM 5 — Session snapshot runtime
- STATUS: PARTIAL
- OWNER AREA: session snapshot ops/runtime
- LAST UPDATED SESSION: `session24_batch24_session-snapshot-defaults-sync`
- EVIDENCE:
  - session snapshot minimum runtime exists;
  - purge runtime exists;
  - defaults synced to locked contract.
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
  - last fully executed local proof remains session 36: `CorrectionCommandsTest.php` `6 tests / 31 assertions`; session 37 adds syntax-linted test coverage in repo.
- OPEN GAP:
  - broader ops failure/retry/scheduler matrix is still not fully proven.
- NEXT REQUIRED ACTION:
  - continue into broader scheduler/retry/failure operator proof only when selected as the next highest-priority batch.

## CONTRACT ITEM 7 — DB-backed integration proof
- STATUS: PARTIAL
- OWNER AREA: repository + pipeline integration
- LAST UPDATED SESSION: `session38_batch38_db_backed_changed_content_promotion_failure_integration_minimum`
- EVIDENCE:
  - repository integration proof added in session 32;
  - DB-backed pipeline integration minimum added in session 33;
  - executed local proof synced in session 34;
  - unchanged correction DB-backed path closed in session 35;
  - changed-content correction + promotion-failure DB-backed integration minimum added in session 38.
- OPEN GAP:
  - broader DB-backed conflict/error integration matrix is still not fully covered outside the current minimum changed-content promotion-failure path.
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
- Sessions 15–38 are reconstructed from canonical ZIP batch names plus checkpoint evidence.
- If older ZIP/source details become available later, enrich evidence without changing canonical session labels.

## Tracker Summary
- Session 35 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 36 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 37 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Session 38 is DONE at session level, but parent correction/tests/ops contracts remain `PARTIAL`.
- Next batch must be selected from the highest-priority remaining `PARTIAL` or `MISSING` contract item.
