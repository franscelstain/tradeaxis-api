# Weekly Swing Strategy Decision — Optional Non-Blocking CONFIRM

## Decision

D+1 CONFIRM adalah **optional non-blocking capability**. Core Weekly Swing adalah valid dan runtime-complete setelah final qualified ranked Top Picks terbentuk pada `WS-S04`.

Canonical flow menjadi:

`trusted Market Data → eligibility/classification → immutable PLAN → qualified TOP PICKS`

Dari final Top Picks terdapat optional branch:

`TOP PICK → optional WS-S05 CONFIRM → ACTIONABLE / NOT_ACTIONABLE`

Proof core berjalan independen dari availability CONFIRM:

`WS-S06 historical evaluation → WS-S07 IS → WS-S08 OOS → WS-S09 friction stress → WS-S10 core forward shadow → WS-S11 production-use review`

## CONFIRM Availability Semantics

Product-level state minimum:

- `NOT_REQUESTED` — CONFIRM belum diminta/dijalankan;
- `UNAVAILABLE_RETRYABLE` — valid decision-time data belum tersedia; bukan failure dan boleh dicoba lagi selama entry window;
- `ACTIONABLE` — valid current data tersedia dan seluruh active gate lulus;
- `NOT_ACTIONABLE` — valid current data tersedia dan sedikitnya satu active actionability gate gagal;
- `EXPIRED_UNCONFIRMED` — entry window selesai sebelum valid CONFIRM dapat dievaluasi; tidak mengubah historical Top Pick dan bukan core strategy failure.

Missing, stale, incomplete, atau temporarily unavailable current data tidak boleh dipetakan menjadi `NOT_ACTIONABLE` karena tidak ada valid evidence untuk keputusan negatif.

Technical implementation error tetap boleh dicatat sebagai technical error, tetapi tidak boleh menghapus, menggagalkan, atau mererank EOD Top Picks dan tidak boleh mengubah core Weekly Swing proof verdict.

## Proof Boundary

Core production-use review membutuhkan:

`IS PASS → OOS PASS → adverse-friction PASS → core forward-shadow PASS`

CONFIRM proof adalah capability-specific:

- bila decision-time data tersedia cukup, optional CONFIRM shadow dapat menghasilkan `CONFIRM_PROVEN`;
- bila data tidak tersedia/cukup, status adalah `CONFIRM_UNPROVEN` atau `CONFIRM_EVIDENCE_INSUFFICIENT` tanpa menurunkan core Top Picks menjadi FAIL/NOT READY;
- user-facing label `ACTIONABLE` hanya boleh diberikan dari valid evaluated CONFIRM, bukan dari synthetic/default PASS.

## Rationale

Tujuan utama Watchlist adalah menghasilkan qualified ranked Top Picks dari trusted EOD Market Data. CONFIRM meningkatkan decision support ketika current-entry data tersedia, tetapi ketergantungan wajib terhadap optional data akan membuat core product rapuh dan dapat memblokir pembangunan sistem tanpa alasan strategis.

## Affected Strategy Owners

- `../../authority/strategy/README.md`;
- `../../authority/strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`;
- `../../authority/strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`;
- `../../authority/strategy/WS_RUNTIME_FLOW.md`;
- `../../authority/strategy/WS_D1_CONFIRM_ACTIONABILITY.md`;
- `../../authority/strategy/WS_END_TO_END_STRATEGY_LIFECYCLE.md`;
- `../../authority/strategy/WS_TOP_PICKS_RECOMMENDATION.md`;
- `../../authority/strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`;
- `../../authority/strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`;
- `../../authority/strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`.

Prior canonical snapshot is preserved at:

`../history/superseded/2026-08-17_pre-confirm-optional-nonblocking-alignment/`

## Implementation Consequence

Implementation harus dapat menyelesaikan PLAN + RECOMMENDATION/TOP PICKS tanpa membuat CONFIRM artifact/output sebagai prerequisite. CONFIRM boleh diimplementasikan kemudian atau menghasilkan availability state non-terminal sampai data valid tersedia.

Overall implementation remains **STRATEGY_REVISED_IMPLEMENTATION_ALIGNMENT_PENDING** until technical contracts/code/tests are aligned and evidenced.
