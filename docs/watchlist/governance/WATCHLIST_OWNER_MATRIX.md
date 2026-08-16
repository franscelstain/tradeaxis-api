# Watchlist Owner Matrix

> **Status:** CANONICAL GOVERNANCE
> **Active product scope:** `watchlist / weekly_swing` only

## Purpose

Dokumen ini memetakan authority dan ownership dokumentasi Watchlist setelah pemisahan strategy, implementation, research, evidence, findings, decisions, dan history. Dokumen ini tidak membuat business rule Weekly Swing baru.

## Authority Precedence (LOCKED)

Saat terjadi konflik, gunakan urutan berikut:

1. **Documentation governance**
   - `WATCHLIST_DOCUMENT_AUTHORITY.md`
   - `DOCUMENTATION_ARCHITECTURE.md`
   - `DOCUMENT_CHANGE_POLICY.md`

2. **Canonical Weekly Swing strategy**
   - `../strategy/weekly_swing/`

3. **Implementation translation**
   - `../implementation/weekly_swing/`
   - `../implementation/shared/` untuk technical support yang dipakai Weekly Swing saat ini

4. **Research / evidence / findings / decisions / history** sesuai role masing-masing.

5. **Audit/tracker** hanya menilai dan mencatat; tidak menjadi business-rule owner.

## Authority Matrix

| Area | Role | Business-rule owner? | Mutability | Must defer to |
|---|---|---:|---|---|
| `governance/` | document authority/change control | Governance only | controlled | — |
| `strategy/weekly_swing/` | canonical Weekly Swing behavior & acceptance | **Yes** | stable by default | governance |
| `implementation/weekly_swing/` | technical translation | No | mutable | strategy + governance |
| `implementation/shared/` | shared technical support used by Weekly Swing | No | mutable | strategy + governance |
| `research/weekly_swing/` | hypothesis/preregistration/experiment | No | append/revise by research lifecycle | strategy boundary |
| `evidence/weekly_swing/` | actual outcomes/evidence | No | historical/append-oriented | strategy for interpretation |
| `findings/weekly_swing/` | discovered issue/insight | No | lifecycle record | evidence + strategy |
| `decisions/weekly_swing/` | explicit governance/product decision | No direct implicit rule change | controlled | governance change process |
| `history/weekly_swing/` | superseded/migration/campaign history | No | historical | current governance/strategy |
| `governance/audit/`, trackers | audit/status guardrail | No | append/review | governance + strategy |

## Current Canonical Weekly Swing Strategy Owner Set

Only files in this set may define current Weekly Swing behavioral/acceptance strategy:

- `../strategy/weekly_swing/00_WS_SCOPE_LOCK.md`
- `../strategy/weekly_swing/01_WS_OVERVIEW.md`
- `../strategy/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`
- `../strategy/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `../strategy/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `../strategy/weekly_swing/10_WS_CONFIRM_OVERLAY.md`
- `../strategy/weekly_swing/12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`
- `../strategy/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `../strategy/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`
- `../strategy/weekly_swing/validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`
- `../strategy/weekly_swing/validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`

`../strategy/weekly_swing/README.md` adalah canonical index/orientation, bukan tempat campaign result atau technical implementation rule.

## Implementation Translation Areas

Implementation artifacts are **not** normative policy owners. Current areas:

- `../implementation/weekly_swing/contracts/`
- `../implementation/weekly_swing/db/`
- `../implementation/weekly_swing/persistence/`
- `../implementation/weekly_swing/guidance/`
- `../implementation/weekly_swing/testing/`
- `../implementation/weekly_swing/verification/`
- `../implementation/weekly_swing/procedures/`
- `../implementation/weekly_swing/evidence_contracts/`
- `../implementation/weekly_swing/fixtures/`
- `../implementation/weekly_swing/examples/`
- `../implementation/weekly_swing/reference/`
- `../implementation/shared/`

Reason code, schema, DDL/SQL, DTO/API shape, persistence, fixture, test, command, serializer, hash transport, dan artifact layout berada di implementation kecuali semantics-nya secara eksplisit dikunci sebagai behavior oleh strategy owner.

## Research / Evidence / Decision Boundary

- Research PASS tidak otomatis menjadi canonical strategy.
- Evidence terbaru tidak otomatis mengganti strategy.
- Finding belum mengubah behavior.
- Decision yang meminta behavior change harus menjalankan `DOCUMENT_CHANGE_POLICY.md` dan memperbarui strategy secara eksplisit.
- History/superseded tidak boleh menjadi fallback current behavior.

## Audit Guardrail Set

Current audit/governance entry points:

- `audit/WATCHLIST_AUDIT_FOUNDATION.md`
- `audit/WATCHLIST_AUDIT_CHECKLIST_FINAL.md`
- `audit/WATCHLIST_AUDIT_PROMPT_STANDARD.md`
- `WATCHLIST_CHANGE_IMPACT_MATRIX.md`
- `trackers/LUMEN_CONTRACT_TRACKER.md`
- `../evidence/weekly_swing/ledgers/LUMEN_IMPLEMENTATION_STATUS.md`

Mereka tidak boleh menggantikan canonical strategy owner.

## Hard Rules

1. Active product strategy hanya `weekly_swing`.
2. Implementation tidak boleh menjadi business-rule owner hanya karena file lebih baru atau lebih detail.
3. Research/evidence/history tidak boleh mengubah current behavior secara implisit.
4. Strategy tidak boleh menerima progress, SHA/hash result, test output, campaign result, atau operator outcome sebagai owner content.
5. Supporting fixture/example/reference tidak boleh memperkenalkan rule baru.
6. Jika implementation menyimpang dari strategy tanpa approved strategy-change decision, implementation yang harus diperbaiki.
