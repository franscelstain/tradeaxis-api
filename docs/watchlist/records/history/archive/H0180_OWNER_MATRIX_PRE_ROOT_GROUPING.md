# Watchlist Owner Matrix

> **Status:** CANONICAL GOVERNANCE  
> **Active product scope:** `watchlist / weekly_swing` only

## Purpose

Memetakan authority, ownership, dan mutability setelah dokumentasi dipisahkan per role. Dokumen ini tidak membuat business rule Weekly Swing baru.

## Authority Precedence

1. **Documentation governance**
   - `WATCHLIST_DOCUMENT_AUTHORITY.md`
   - `DOCUMENTATION_ARCHITECTURE.md`
   - `DOCUMENT_RECORDING_STANDARD.md`
   - `STAGE_EXECUTION_AND_REWORK_STANDARD.md`
   - `IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`
   - `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`
   - `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`
   - `DOCUMENT_CHANGE_POLICY.md`
2. **Canonical Weekly Swing strategy** — `../strategy/`
3. **Implementation translation** — `../implementation/`
4. **Research / evidence / findings / decisions / history** sesuai role.
5. **Audit/tracker** hanya menilai/mencatat; tidak menjadi business-rule owner.

## Authority + Mutability Matrix

| Area | Role | Business-rule owner? | Mutability | Must defer to |
|---|---|---:|---|---|
| `governance/` | authority/change/recording/traceability lifecycle | Governance only | controlled revision / current matrix mutable-traceable | — |
| `strategy/` | canonical Weekly Swing behavior & acceptance | **Yes** | controlled revision | governance |
| `implementation/` | technical translation | No | mutable traceable | strategy + governance |
| `research/` | hypothesis/preregistration/experiment | No | draft mutable; locked immutable | strategy boundary + recording standard |
| `evidence/` | actual outcomes/evidence/status ledger | No | final immutable / ledger append-oriented | recording standard |
| `findings/` | discovered issue/insight | No | lifecycle-update only | evidence + strategy |
| `decisions/` | explicit issued decision | No implicit rule change | issued immutable | governance change process |
| `history/` | superseded/migration/archive | No | immutable | current governance/strategy |
| `governance/audit/` | audit/status guardrail | No | mutable traceable | governance + strategy |

## Current Canonical Weekly Swing Strategy Owner Set

Only files in `../strategy/` listed by `../strategy/README.md` may define current Weekly Swing behavioral/acceptance strategy. `../START_HERE.md` dan README adalah navigation, bukan owner.

## Current Implementation Translation Areas

Current Windows-safe implementation layout:

- `../implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md` — non-owner build orchestration;
- `../implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md` — current resume/status index;
- `../implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`;
- `../implementation/CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`;
- `../implementation/STRATEGY_ALIGNMENT_REQUIRED.md`;
- `../implementation/contracts/`;
- `../implementation/db/`;
- `../implementation/guides/`;
- `../implementation/tests/`;
- `../implementation/examples/`.

Reason code, schema, DDL/SQL, DTO/API shape, persistence, fixture, test, command, serializer, hash transport, dan artifact layout berada di implementation kecuali semantics-nya eksplisit dikunci strategy.

## Research / Evidence / Decision Boundary

- Research PASS tidak otomatis canonical.
- Research `LOCKED` tidak boleh diubah; experiment baru menggunakan identity baru.
- Final evidence tidak boleh diubah; correction menggunakan evidence record baru.
- Finding belum mengubah behavior; original observation tetap.
- Issued decision tidak boleh diubah; perubahan menggunakan superseding decision.
- History/superseded tidak boleh menjadi fallback current behavior.

## Audit Guardrail Set

Current audit/governance entry points:

- `audit/WATCHLIST_AUDIT_FOUNDATION.md`
- `audit/WATCHLIST_AUDIT_CHECKLIST_FINAL.md`
- `audit/WATCHLIST_AUDIT_PROMPT_STANDARD.md`
- `WATCHLIST_CHANGE_IMPACT_MATRIX.md`
- `LUMEN_CONTRACT_TRACKER.md`
- `../evidence/LUMEN_IMPLEMENTATION_STATUS.md`
- `DOCUMENT_CHANGE_LOG.md`

Mereka tidak boleh menggantikan canonical strategy owner.

## Hard Rules

1. Active product strategy hanya Weekly Swing.
2. Implementation tidak menjadi business-rule owner hanya karena file lebih baru/detail.
3. Research/evidence/history tidak mengubah current behavior secara implisit.
4. Strategy tidak menerima progress/hash/test/operator outcome sebagai owner content.
5. Supporting fixture/example/reference tidak memperkenalkan rule baru.
6. Jika implementation menyimpang tanpa approved strategy-change decision, implementation yang diperbaiki.
7. Tidak ada semantic record yang bebas silent update; ikuti `DOCUMENT_RECORDING_STANDARD.md`.
8. Current `WS-Bxx` stage lifecycle/rework/closure mengikuti `STAGE_EXECUTION_AND_REWORK_STANDARD.md`; historical campaign status bukan lifecycle authority baru.
9. Setiap current implementation/proof path tunduk pada recurring residue/conformance gate; unresolved harmful residue memblokir implementation-stage `DONE`, sementara valid compatibility/history tidak boleh dihapus secara buta.

10. Strategy completeness tidak boleh disimpulkan dari stage summary saja; setiap mandatory strategy requirement wajib memiliki row `SATISFIED` pada canonical traceability matrix sebelum 100% coverage claim.
