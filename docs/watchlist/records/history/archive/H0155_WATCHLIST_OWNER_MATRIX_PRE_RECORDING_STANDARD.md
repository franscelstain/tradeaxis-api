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
   - `../strategy/`

3. **Implementation translation**
   - `../implementation/`
   - `../implementation/shared/` untuk technical support yang dipakai Weekly Swing saat ini

4. **Research / evidence / findings / decisions / history** sesuai role masing-masing.

5. **Audit/tracker** hanya menilai dan mencatat; tidak menjadi business-rule owner.

## Authority Matrix

| Area | Role | Business-rule owner? | Mutability | Must defer to |
|---|---|---:|---|---|
| `governance/` | document authority/change control | Governance only | controlled | — |
| `strategy/` | canonical Weekly Swing behavior & acceptance | **Yes** | stable by default | governance |
| `implementation/` | technical translation | No | mutable | strategy + governance |
| `implementation/shared/` | shared technical support used by Weekly Swing | No | mutable | strategy + governance |
| `research/` | hypothesis/preregistration/experiment | No | append/revise by research lifecycle | strategy boundary |
| `evidence/` | actual outcomes/evidence | No | historical/append-oriented | strategy for interpretation |
| `findings/` | discovered issue/insight | No | lifecycle record | evidence + strategy |
| `decisions/` | explicit governance/product decision | No direct implicit rule change | controlled | governance change process |
| `history/` | superseded/migration/campaign history | No | historical | current governance/strategy |
| `governance/audit/`, trackers | audit/status guardrail | No | append/review | governance + strategy |

## Current Canonical Weekly Swing Strategy Owner Set

Only files in this set may define current Weekly Swing behavioral/acceptance strategy:

- `../strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`
- `../strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`
- `../strategy/WS_RUNTIME_FLOW.md`
- `../strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`
- `../strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`
- `../strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`
- `../strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`
- `../strategy/WS_CANDIDATE_CLASSIFICATION.md`
- `../strategy/WS_D1_CONFIRM_ACTIONABILITY.md`
- `../strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`
- `../strategy/WS_TOP_PICKS_RECOMMENDATION.md`
- `../strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`
- `../strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`
- `../strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`

`../strategy/README.md` adalah canonical index/orientation, bukan tempat campaign result atau technical implementation rule.

`../START_HERE.md` adalah repository-level reading/build navigation entry point dan **bukan business-rule owner**. `../implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md` adalah implementation orchestration guide dan **bukan strategy/technical contract owner**; ia harus defer ke lifecycle strategy dan technical contract pada setiap stage.

## Implementation Translation Areas

Implementation artifacts are **not** normative policy owners. Current areas:

- `../implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md` — non-owner build orchestration
- `../implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`
- `../implementation/contracts/`
- `../implementation/db/`
- `../implementation/persistence/`
- `../implementation/guidance/`
- `../implementation/testing/`
- `../implementation/verification/`
- `../implementation/procedures/`
- `../implementation/evidence_contracts/`
- `../implementation/fixtures/`
- `../implementation/examples/`
- `../implementation/reference/`
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
- `../evidence/ledgers/LUMEN_IMPLEMENTATION_STATUS.md`

Mereka tidak boleh menggantikan canonical strategy owner.

## Hard Rules

1. Active product strategy hanya `weekly_swing`.
2. Implementation tidak boleh menjadi business-rule owner hanya karena file lebih baru atau lebih detail.
3. Research/evidence/history tidak boleh mengubah current behavior secara implisit.
4. Strategy tidak boleh menerima progress, SHA/hash result, test output, campaign result, atau operator outcome sebagai owner content.
5. Supporting fixture/example/reference tidak boleh memperkenalkan rule baru.
6. Jika implementation menyimpang dari strategy tanpa approved strategy-change decision, implementation yang harus diperbaiki.
