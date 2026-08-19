# Watchlist Documentation

Watchlist saat ini hanya memiliki satu active strategy: **Weekly Swing**. Dokumentasi dipisahkan berdasarkan authority agar strategy, implementation, research, evidence, findings, decisions, dan history tidak bercampur.

## START HERE — Single Entry Point

Siapa pun yang baru ingin memahami, membangun, melanjutkan, atau mengaudit Watchlist **harus mulai dari**:

[`START_HERE.md`](START_HERE.md)

Dokumen tersebut adalah halaman pertama/daftar isi pembangunan Weekly Swing. Ia memberikan:
- urutan baca strategy seperti buku dari Chapter 1 sampai Chapter 14;
- authoritative lifecycle `WS-S00..WS-S11`;
- detailed implementation sequence `WS-B00..WS-B12`;
- titik core completion, proof completion, dan optional CONFIRM branch;
- aturan apa yang harus dilakukan ketika runtime data/evidence belum tersedia;
- aturan lokasi penulisan implementation, evidence, finding, decision, dan history.

Jangan menentukan build order dari prefix filename lama, C-number, tracker, research, evidence, atau history.

## Short Read Order

1. `START_HERE.md`
2. strategy chapters sesuai Part I di `START_HERE.md`
3. `implementation/STRATEGY_ALIGNMENT_REQUIRED.md`
4. `implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`
5. technical documents hanya pada build step yang relevan
6. `evidence/`, `findings/`, `decisions/`, dan `history/` hanya sesuai perannya

## Active Product Direction

Core product flow:

`trusted Market Data -> eligible candidates -> immutable PLAN -> qualified RECOMMENDATION/TOP PICKS -> manual buy decision support`

Optional non-blocking enhancement:

`qualified TOP PICKS -> D+1 CONFIRM (when valid decision-time data is available) -> ACTIONABLE / NOT_ACTIONABLE`

If CONFIRM data is not available, the core Top Picks output remains valid. The CONFIRM state is `NOT_REQUESTED`, `UNAVAILABLE_RETRYABLE`, or eventually `EXPIRED_UNCONFIRMED`; absence of CONFIRM data is not a core Watchlist failure.

- domain: `watchlist`
- active strategy: `weekly_swing`
- Top Picks adalah final qualified recommendations, bukan PLAN group
- jumlah Top Picks quality-driven dan boleh nol
- output adalah decision-support, bukan order execution atau portfolio lifecycle

## Canonical Build / Proof Order

Urutan strategis authoritative bukan prefix filename, tetapi lifecycle di `strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`. Core runtime adalah `WS-S00..WS-S04`; `WS-S05` adalah optional non-blocking CONFIRM branch; core proof adalah `WS-S06..WS-S11`. Technical implementation harus memetakan dependency ke lifecycle ini dan tidak boleh membuat core completion bergantung pada tersedianya data CONFIRM.

## Current Alignment State

Canonical strategy sudah direvisi untuk qualified Top Picks dan optional non-blocking CONFIRM. Technical implementation contracts/code/evidence belum boleh dianggap otomatis conformant dengan revision tersebut. Selama alignment berlangsung, `implementation/CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md` menjadi guard aktif untuk dependency CONFIRM.

Current handoff state:

`STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING`

Historical evidence tetap historical dan tidak ditulis ulang.

## Upstream Boundary

Watchlist adalah consumer `market_data`. Fakta market, publication/read model, readiness, OHLCV, indicators, corporate-action handling, status/sector semantics, dan producer-side point-in-time meaning tetap dimiliki `docs/market_data/`.

Current binding owner untuk Weekly Swing adalah `strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`, dengan technical translation di `implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`. Runtime Watchlist tidak boleh mengganti producer-facing read product dengan direct Market Data table/readiness reconstruction.


## Documentation Recording Discipline

Semua pencatatan Watchlist tunduk pada [`governance/DOCUMENT_RECORDING_STANDARD.md`](governance/DOCUMENT_RECORDING_STANDARD.md). Tidak ada semantic record yang bebas diubah tanpa trace.

- strategy/governance: controlled revision;
- implementation: mutable tetapi material change wajib traceable;
- locked research, final evidence, issued decision, dan history: immutable;
- finding: original observation tetap, lifecycle/resolution ditambahkan;
- ledger/status: append-oriented;
- material documentation event: append ke [`governance/DOCUMENT_CHANGE_LOG.md`](governance/DOCUMENT_CHANGE_LOG.md).


## Recurring Implementation Residue Gate

Setiap stage/rerun yang menyentuh current behavior atau proof wajib membaca [`governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md).

- path baru bekerja belum cukup untuk menyatakan conformance;
- reachable legacy path yang bertentangan adalah `HARMFUL_RESIDUE` dan memblokir implementation-stage `DONE`;
- valid compatibility residue boleh tetap hanya dengan exact mapping + isolation + tests + evidence;
- historical residue dipertahankan sebagai history/evidence dan tidak boleh dijadikan current fallback;
- klaim dead/unreachable membutuhkan evidence, bukan sekadar grep atau pendapat.

## Strategy Coverage / Traceability

Canonical rule-by-rule coverage berada di [`governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv), dengan lifecycle di [`governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md).

- stage `DONE` saja tidak membuktikan seluruh strategy terpenuhi;
- setiap active mandatory strategy rule harus berakhir `SATISFIED`;
- row wajib menunjuk implementation, test, immutable evidence, dan residue verdict;
- optional CONFIRM `OPTIONAL_NOT_REQUESTED` tidak memblokir core;
- 100% strategy coverage claim dilarang bila satu mandatory row saja belum satisfied.

## Stage Re-entry and Closure Discipline

Current implementation stage lifecycle is governed by [`governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md), with the current resume pointer in [`implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md).

- repeated failure is never a closure criterion;
- every attempt closes with evidence + convergence;
- a stage being rerun must read its prior lineage first;
- `DONE` only means the declared stage objective/exit criteria were achieved;
- a valid evaluation verdict may be `FAIL` while the evaluation stage itself is `DONE`;
- unresolved technical work remains active while remediation/dependency path is credible;
- terminal unresolved closure requires objective evidence + reviewed decision.

## Documentation Roles

- `strategy/` — canonical/stable behavior.
- `implementation/` — technical translation.
- `research/` — non-canonical experiments.
- `evidence/` — actual results.
- `findings/` — discovered issues/insights.
- `decisions/` — explicit decisions.
- `history/` — superseded/historical records.
- `governance/` — authority and change rules.


## Simplified Current Layout

Current Watchlist documentation sengaja dibatasi ke folder berikut:

- `strategy/` — canonical Weekly Swing behavior;
- `implementation/` — technical translation;
- `research/` — experiments;
- `evidence/` — actual evidence;
- `findings/` — discovered problems;
- `decisions/` — formal decisions;
- `governance/` — authority/audit/change rules;
- `history/` — flat Windows-safe archive.

Tidak ada lagi redundant `weekly_swing/` child folder di setiap layer karena current Watchlist scope memang Weekly Swing only.
