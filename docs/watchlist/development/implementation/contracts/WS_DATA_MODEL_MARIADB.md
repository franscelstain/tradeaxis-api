# 03 — WS Data Model (MariaDB)

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Purpose

Dokumen ini menetapkan model data dan runtime output shape normatif untuk Weekly Swing watchlist.

Dokumen ini mencakup dua required core artifacts dan satu optional artifact:
1. PLAN runtime output
2. RECOMMENDATION runtime output
3. optional CONFIRM runtime output

Current CONFIRM translation tunduk pada `../CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`.

## Scope

Dokumen ini mencakup:
- runtime output shape untuk PLAN;
- runtime output shape untuk RECOMMENDATION;
- runtime output shape untuk CONFIRM;
- relationship PLAN / RECOMMENDATION / CONFIRM;
- boundary field antar lapisan.

## A. PLAN Runtime Output Shape

PLAN runtime output **MUST** terdiri dari `meta`, `items`, dan `summary`.

## B. RECOMMENDATION Runtime Output Shape

RECOMMENDATION runtime output **MUST** terdiri dari `meta`, `items`, dan `summary`.

### B1. meta (RECOMMENDATION)
`meta` minimal **MUST** memuat:
- `strategy_code`
- `trade_date`
- `policy_code`
- `param_set_id`
- `policy_version`
- `schema_version`
- `capital_mode`
- `input_capital` (nullable)
- `generated_at`
- `recommendation_hash` (optional)

### B2. items (RECOMMENDATION)
Setiap item minimal **MUST** memuat:
- `ticker`
- `plan_rank`
- `plan_group_semantic`
- `recommendation_score`
- `recommendation_rank`
- `recommendation_label`
- `recommended_flag`
- `reason_codes[]`

### B3. summary (RECOMMENDATION)
`summary` minimal **MUST** memuat:
- `recommended_count`
- `capital_mode`
- `empty_recommendation_flag`
- `recommended_tickers[]`

## C. CONFIRM Runtime Output Shape

CONFIRM runtime output hanya berlaku untuk final Top Pick yang berhasil di-bind ke immutable PLAN/recommendation identity pada `trade_date` yang sama. Absence of CONFIRM output adalah valid core state.

CONFIRM runtime output **MUST NOT** diperlakukan sebagai input pembentuk RECOMMENDATION.

## D. PLAN / RECOMMENDATION / CONFIRM Relationship

### D1. PLAN as Root Artifact
PLAN adalah artefak akar untuk `trade_date` yang sama.

### D2. RECOMMENDATION Relationship
RECOMMENDATION item **MUST** berasal dari PLAN item yang sah dan **MUST NOT** membaca field hasil CONFIRM.

### D3. CONFIRM Relationship
CONFIRM item **MUST** berasal dari final Top Pick + immutable PLAN/recommendation binding yang sah. Ticker di luar recommendation set **MUST NOT** menghasilkan valid business CONFIRM.

### D4. Valid Combined States
Kombinasi state berikut valid:
- `PLAN only`
- `PLAN + RECOMMENDATION`
- `PLAN + RECOMMENDATION`
- `PLAN + RECOMMENDATION + CONFIRM` (optional)

Kombinasi state berikut tidak valid:
- `RECOMMENDATION without PLAN`
- `CONFIRM without final Top Pick + source PLAN/recommendation binding`

### D5. Boundary Rule
Field hasil CONFIRM **MUST NOT** hidup sebagai input field recommendation.
Recommendation membership **IS** the business eligibility boundary for optional CONFIRM: only final Top Picks may be evaluated. CONFIRM still must not mutate recommendation fields.

## Final Data Model Rules (LOCKED)

1. Weekly Swing core runtime memiliki PLAN dan RECOMMENDATION; CONFIRM adalah optional artifact.
2. RECOMMENDATION **MUST** berasal dari PLAN dan tidak boleh dibentuk dari CONFIRM.
3. CONFIRM **MUST** berasal dari final Top Pick + immutable source binding.
4. Ticker non-recommended **MUST NOT** memiliki valid business CONFIRM.
5. Missing/stale/incomplete CONFIRM data **MUST NOT** fail core runtime atau menjadi synthetic `NOT_ACTIONABLE`.
6. Ticker Top Pick lalu confirmed **MAY** tampil dengan kedua state sekaligus.


## Terminology Lock

- `param_set_id` menunjuk row/instance paramset aktif yang dipakai untuk membentuk artifact.
- `policy_version` menunjuk label kontrak Weekly Swing yang sedang berlaku; nama field ini bukan klaim bahwa aplikasi sudah memiliki release/versioning runtime.
- `schema_version` menunjuk label schema paramset yang tervalidasi; nama field ini bukan klaim versioning aplikasi.
- Nama `paramset_version` tidak lagi normatif dan tidak boleh dipakai sebagai field meta runtime karena berpotensi mencampur tiga makna di atas.
