# 06 — WS Test Implementation Guidance

## Purpose

Dokumen ini menerjemahkan baseline contract Weekly Swing menjadi target testing implementasi aplikasi watchlist.

Dokumen ini menetapkan **minimum mandatory test matrix**.  
Implementasi tidak boleh dianggap patuh bila core-rule tests gagal.

## Scope Lock

- watchlist only
- weekly_swing only
- bukan portfolio
- bukan execution
- bukan market-data internals

## Mandatory Test Classes

1. contract tests
2. negative tests
3. immutability tests
4. persistence separation tests
5. API conformance tests
6. fixture conformance tests
7. deterministic behavior tests

## Mandatory Test Matrix

| Test ID | Rule under test | Input fixture / setup | Expected result | Layer |
|---|---|---|---|---|
| `plan_build_is_deterministic` | PLAN deterministic | same PLAN inputs twice | same PLAN output semantics | unit/service |
| `recommendation_build_is_plan_only` | recommendation source only from PLAN | immutable PLAN artifact | recommendation built only from PLAN | unit/service |
| `recommendation_can_be_empty` | empty recommendation valid | PLAN with no valid recommendation result | empty recommendation accepted | contract |
| `confirm_requires_plan_candidate` | confirm requires candidate PLAN | ticker outside PLAN candidate set | request rejected | contract/API |
| `confirm_can_accept_non_recommended_candidate` | non-recommended candidate can confirm | candidate PLAN not in recommendation | confirm accepted if eligible | cross-layer |
| `confirm_does_not_mutate_recommendation` | confirm immutability | recommendation + confirm on same ticker | recommendation unchanged | immutability |
| `composite_view_preserves_source_semantics` | composite must not merge semantics | PLAN + recommendation + confirm | composite preserves source sections | API/read |
| `unknown_top_level_field_is_rejected` | unknown field rejection | invalid confirm payload | request rejected | negative/API |
| `invalid_confirm_snapshot_is_rejected` | invalid confirm input | malformed snapshot/manual input | request rejected | negative |
| `source_plan_reference_is_required` | source PLAN ref required | recommendation/confirm without source reference | invalid | persistence/contract |
| `recommendation_membership_is_not_back_mutated` | recommendation membership immutable | confirm after recommendation publish | membership unchanged | persistence |
| `recommendation_rank_is_not_back_mutated` | recommendation rank immutable | confirm after recommendation publish | rank unchanged | persistence |
| `recommendation_score_is_not_back_mutated` | recommendation score immutable | confirm after recommendation publish | score unchanged | persistence |
| `no_trade_behavior_remains_valid` | no-trade handling | no-trade PLAN fixture | behavior remains valid per contract | contract |
| `hash_contract_is_stable` | hash contract stability | hash fixtures | expected hash behavior preserved | fixture/contract |
| `reason_codes_contract_is_stable` | reason code stability | reason-code fixtures | expected reason codes preserved | fixture/contract |
| `deterministic_ties_are_stable` | deterministic tie handling | tie fixtures | stable output ordering/selection | unit/contract |

## Core Rules That Must Be Explicitly Covered

1. recommendation hanya dari PLAN
2. recommendation bisa kosong walau prioritized groups tidak kosong
3. non-recommended candidate tetap bisa confirm
4. confirm tidak mengubah recommendation
5. unknown top-level field ditolak
6. invalid candidate for confirm ditolak
7. deterministic ties stabil
8. no-trade behavior tetap valid
9. hash / reason-code contract stabil
10. source PLAN reference wajib ada

## Contract Tests

Minimal wajib memverifikasi:
- payload `PLAN` sesuai owner contract
- payload `RECOMMENDATION` sesuai owner contract
- payload `CONFIRM` sesuai owner contract
- composite read tidak mengubah source semantics

## Negative Tests

Minimal wajib memverifikasi:
- unknown top-level field ditolak
- ticker di luar candidate PLAN ditolak untuk confirm
- source PLAN missing ditolak
- confirm input malformed ditolak
- unsupported capital mode ditolak pada endpoint/flow yang memakai `capital_mode`

## Immutability Tests

Minimal wajib memverifikasi:
- confirm tidak mengubah recommendation membership
- confirm tidak mengubah recommendation rank
- confirm tidak mengubah recommendation score
- confirm tidak mengubah recommendation label/group semantics

## Persistence Separation Tests

Minimal wajib memverifikasi:
- PLAN, RECOMMENDATION, CONFIRM tersimpan sebagai artifact terpisah
- RECOMMENDATION merefer ke source PLAN
- CONFIRM merefer ke source PLAN
- watchlist persistence tidak bercampur dengan portfolio/execution state

## API Conformance Tests

Minimal wajib memverifikasi:
- endpoint recommendation tidak bergantung pada confirm
- endpoint confirm menolak ticker non-candidate
- composite endpoint memisahkan `plan`, `recommendation`, dan `confirm`
- recommendation kosong tetap valid
- confirm tetap bisa valid untuk candidate PLAN walau recommendation kosong

## Fixture Conformance Tests

Minimal wajib memverifikasi:
- fixtures locked masih sinkron dengan owner docs
- path/reference guards tidak drift
- field/schema guards tidak drift
- cross-artifact guards tetap lolos

## Pass Criteria (LOCKED)

Implementasi **tidak boleh** dianggap patuh bila salah satu core-rule test di atas gagal.

Contoh manual, worked example, atau screenshot **tidak cukup**.  
Harus ada automated validation minimal untuk contract, immutability, dan boundary rules inti.

## Final Rule

Testing Weekly Swing yang sah bukan hanya membuktikan “fitur jalan”, tetapi membuktikan:
- rule baseline tetap utuh
- artifact separation tetap utuh
- confirm tidak memutasi recommendation
- implementation tidak bocor ke domain lain

## OOS closure validation focus

Validation must cover catalog constraints, seed idempotency, eval identity versioning, targeted exact-pair reads, in-memory calibration, ATR/RR target-stop fallback, extreme trade evidence, no OOS selection input, and deterministic two-run hashes.

## Post-Deployment Regression Guards

Mandatory guards for the OOS runtime closure:

- catalog cardinality checks derive from `WatchlistBacktestParamGridCatalog::CATALOG_COUNT`, currently `24`, instead of a duplicated literal;
- the SQL seed contains exactly one `INSERT ... WHERE NOT EXISTS` pair per canonical catalog row;
- missing `risk.stop_atr_mult` and `risk.min_rr` resolve to the bootstrap defaults `1.5` and `1.5` in strategy trade candidates;
- published-price runtime metadata is rebound before the frozen strategy hash, including when a test double or legacy strategy payload omits `pricing_model` / `price_read_mode`;
- runtime metadata binding does not fabricate missing evaluation thresholds;
- persisted WS grid rows must match the canonical catalog exactly; extra or missing rows fail closed.


## Canonical grid cross-field compatibility guard

Tests must construct a runtime paramset for every canonical catalog row and assert:

```text
min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct
risk_band_rule = CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR
```

At least one baseline row must preserve the default ideal ATR band, and at least one strict row with `max_atr14_pct < default atr_ideal_high` must prove deterministic clamping. Static guards must prove IS calibration delegates to the canonical paramset factory rather than recreating row mapping locally.
