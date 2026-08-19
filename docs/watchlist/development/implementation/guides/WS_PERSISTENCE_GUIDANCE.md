# 05 — WS Persistence Guidance

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Purpose

Dokumen ini memberi panduan persistence artifact watchlist agar implementasi aplikasi tetap sinkron dengan baseline docs Weekly Swing.

Dokumen ini adalah **implementation translation only**.  
Dokumen ini tidak boleh dipakai untuk mengubah authority rule dari owner docs.

Untuk CONFIRM, current alignment guard adalah `../CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`.

## Scope Lock

- watchlist only
- weekly_swing only
- bukan portfolio
- bukan execution
- bukan market-data internals

## Persistence Principles (LOCKED)

1. `PLAN` dan `RECOMMENDATION` adalah required core artifacts; `CONFIRM` adalah optional separate artifact hanya bila ada request/attempt/result
2. `RECOMMENDATION` adalah turunan dari `PLAN`, bukan update atas `PLAN`
3. `CONFIRM` adalah optional overlay terhadap final Top Pick + immutable PLAN/recommendation binding, bukan update atas `RECOMMENDATION`
4. artifact yang sudah dipublish tidak boleh dimutasi pada business fields inti
5. watchlist persistence tidak boleh bercampur dengan execution/order/broker persistence
6. watchlist persistence tidak boleh bercampur dengan portfolio/holding persistence
7. watchlist persistence tidak bertugas menyimpan raw market-data provider
8. nama key minimum di dokumen ini adalah **canonical persistence field set** untuk build layer B

## Persistence Objects

### PLAN Artifact Storage
Dipakai untuk:
- baseline immutable harian
- source bagi RECOMMENDATION
- source binding bagi downstream RECOMMENDATION; optional CONFIRM eligibility ditentukan oleh final Top Picks

Minimum metadata canonical:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `schema_version`
- `param_set_id`
- `created_at`
- `source_hash`

Aturan tambahan:
- `created_at` adalah canonical audit timestamp minimum untuk artifact PLAN
- `source_hash` wajib ada bila artifact memakai hash/replay trace; bila hash tidak dipakai, boleh bernilai `NULL` tetapi nama field tetap `source_hash`

### RECOMMENDATION Artifact Storage
Dipakai untuk:
- replay
- audit
- consumer reads
- cache/read optimization

Minimum metadata canonical:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `schema_version`
- `param_set_id`
- `source_plan_reference`
- `capital_mode`
- `created_at`

Aturan tambahan:
- `source_plan_reference` adalah field canonical reference ke PLAN
- `capital_mode` adalah field canonical; jangan ganti dengan alias lain di boundary persistence logis
- `created_at` adalah canonical audit timestamp minimum

### CONFIRM Artifact Storage (Conditional)
Dipakai hanya ketika ada CONFIRM request/attempt/result untuk:
- history confirm
- consumer reads
- audit trail confirm

Tidak adanya row/artifact CONFIRM adalah valid bila CONFIRM belum diminta atau decision-time data belum menghasilkan attempt yang dipersist. Absence of CONFIRM tidak membuat core PLAN/RECOMMENDATION incomplete.

Minimum metadata canonical:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `schema_version`
- `param_set_id`
- `source_plan_reference`
- `ticker`
- `snapshot_reference`
- `confirm_status`
- `created_at`

Aturan tambahan:
- `snapshot_reference` adalah field canonical untuk basis input confirm yang sah
- `confirm_status` adalah field canonical untuk hasil confirm
- `schema_version` dan `param_set_id` wajib ikut di logical storage confirm agar runtime keys artifact-level tetap sinkron dengan contract runtime Weekly Swing
- `created_at` adalah canonical audit timestamp minimum

### Optional Trace / Audit Storage
Boleh ada bila implementasi memerlukan:
- `validation_trace_id`
- `publish_log_id`
- `request_audit_ref`
- `response_audit_ref`
- `read_cache_updated_at`

Aturan tambahan:
- field optional ini bersifat non-authority
- field optional tidak boleh menggantikan field canonical minimum
- field optional tidak boleh dipakai untuk mengubah makna bisnis artifact

## Source Reference Rules

1. setiap `RECOMMENDATION` record **must reference** source `PLAN`
2. setiap `CONFIRM` record **must reference** source `PLAN`
3. `CONFIRM` boleh menyimpan reference ke `RECOMMENDATION` hanya sebagai context, **bukan authority**
4. business eligibility untuk `CONFIRM` berasal dari final Top Picks pada `RECOMMENDATION`; `PLAN` tetap direferensikan sebagai immutable source/trade-plan binding, bukan sebagai eligibility authority

## Minimal Storage Keys — Canonical Logical Set

Minimum key/logical fields yang wajib bisa dilacak:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `schema_version`
- `param_set_id` untuk PLAN, RECOMMENDATION, dan CONFIRM
- `source_plan_reference` untuk RECOMMENDATION dan CONFIRM
- `ticker` untuk item-level artifact
- `created_at`

Aturan tambahan:
- jangan memakai `paramset_version` sebagai pengganti `param_set_id`
- jangan memakai alias lain untuk `source_plan_reference` pada boundary persistence logis
- bila physical column name di database berbeda, mapping ke logical canonical field harus terdokumentasi eksplisit di code/app docs

## Canonical Logical Keys per Artifact

### PLAN
Logical uniqueness minimum harus bisa dibuktikan dari kombinasi:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `param_set_id`

### RECOMMENDATION
Logical uniqueness minimum harus bisa dibuktikan dari kombinasi:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `param_set_id`
- `source_plan_reference`
- `capital_mode`

### CONFIRM
Logical uniqueness minimum harus bisa dibuktikan dari kombinasi:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `source_plan_reference`
- `ticker`
- `snapshot_reference`

## Immutability Rule

### Business Fields That Must Not Be Back-Mutated
- PLAN score/rank/group semantics
- RECOMMENDATION membership
- RECOMMENDATION ranking
- RECOMMENDATION scoring/label
- CONFIRM source reference authority
- CONFIRM `confirm_status` yang sudah dipublish sebagai artifact final

### Allowed Operational Metadata Update
Boleh hanya untuk metadata operasional berikut:
- `published_at`
- `read_cache_updated_at`
- `last_read_at`

Operational metadata update tidak boleh mengubah makna bisnis artifact.

## Write Sequence Minimum

1. publish/freeze `PLAN`
2. derive dan persist `RECOMMENDATION` dari `PLAN` — core persistence selesai di sini
3. bila CONFIRM diminta, bind final Top Pick; missing/stale/incomplete data menghasilkan non-blocking availability state dan tidak membatalkan langkah 1–2
4. persist `CONFIRM` bila ada request/attempt/result sesuai policy
5. jangan pernah back-mutate business fields `PLAN` atau `RECOMMENDATION`

## Forbidden Persistence Patterns (LOCKED)

1. menulis confirm status ke row recommendation sehingga recommendation terlihat berubah
2. meng-overwrite ranking/grouping recommendation setelah confirm
3. menyisipkan ticker non-PLAN ke storage recommendation
4. mencampur watchlist persistence dengan broker/order/execution persistence
5. mencampur watchlist persistence dengan holdings/portfolio persistence
6. mengizinkan CONFIRM untuk ticker yang bukan final Top Pick, atau menjadikan CONFIRM prerequisite core recommendation
7. menyimpan raw market-data provider sebagai tanggung jawab watchlist persistence
8. menghapus field canonical minimum dari logical model hanya karena physical table memakai alias internal

## Note on Source Data

Watchlist hanya menyimpan artifact hasil domain watchlist dan reference yang diperlukan untuk audit/traceability.  
Watchlist bukan owner raw market-data provider.

## Anti-Ambiguity Guard

- simpan `param_set_id` pada artifact runtime Weekly Swing untuk menunjuk instance paramset aktif yang dipakai saat generate output
- simpan `policy_version` untuk menunjukkan versi rule/business contract
- simpan `schema_version` untuk menunjukkan versi schema/contract yang dipakai
- gunakan `created_at` sebagai canonical audit timestamp minimum
- jangan memakai `paramset_version` sebagai nama field persistence bila maknanya ambigu
- jangan mengganti `source_plan_reference`, `capital_mode`, `snapshot_reference`, atau `confirm_status` dengan alias lain pada logical contract

## Final Rule

Model persistence yang sah untuk Weekly Swing adalah:
- artifact terpisah
- source reference jelas
- canonical logical fields jelas
- immutable business fields
- no back-mutation from confirm to recommendation
- no leakage ke execution atau portfolio

## Evaluation identity and seed deployment

Use the migrations or synchronized DDL/closure SQL before seeding the grid. `watchlist_bt_eval` must use the versioned identity `(policy_code, param_id, eval_model, paramset_hash, from_date, to_date)`. This allows historical legacy evidence and current canonical semantics to coexist without overwrite. Seed the canonical grid with `watchlist:backtest-param-grid-seed`; reruns must be idempotent.
