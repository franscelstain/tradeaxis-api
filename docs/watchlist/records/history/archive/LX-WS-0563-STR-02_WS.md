# Legacy Role Extract — WS — STRATEGY

> **Document Type:** STRATEGY
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0563-STR-02`
> **Legacy Source ID:** `LS-WS-0563`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`
> **Original SHA1:** `5504640D24541B50598E6C82A9EB3E482070FCC8`
> **Source Sections:** L3-L6 Purpose; L19-L28 A1. PLAN Grouping Reason-Code Boundary Reference; L29-L39 C. Acceptance Minima (LOCKED); L40-L43 D. Determinism Acceptance (LOCKED); L44-L49 E. Cross-Layer Acceptance (LOCKED); L50-L56 Final Rule; L57-L68 B1. CONFIRM Overlay Boundary Reason-Code Reference; L69-L77 B2. Backtest Evaluation Reason-Code Boundary Reference
> **Extract Body SHA1:** `E35F3D8C1A7AC2E842B19FC9867FB58D697C0B7D`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Purpose

Dokumen ini menetapkan reason codes normatif dan acceptance minimum untuk layer **RECOMMENDATION** pada Weekly Swing.

## A1. PLAN Grouping Reason-Code Boundary Reference

Reason code berikut boleh muncul pada PLAN grouping output sebelum layer RECOMMENDATION berjalan. Kode ini bukan reason code recommendation final dan tidak boleh dipakai untuk menyimpulkan `recommended_flag`, `recommendation_label`, atau hasil CONFIRM.

- `WS_PLAN_TOP_PICK`
- `WS_PLAN_SECONDARY`
- `WS_PLAN_WATCH_ONLY`
- `WS_PLAN_AVOID_LOW_SCORE`
- `WS_PLAN_AVOID_EXCLUDED`

## C. Acceptance Minima (LOCKED)

Acceptance minimum recommendation **MUST** mencakup seluruh kasus berikut:
- recommendation tersedia dari PLAN yang valid;
- recommendation tersedia tanpa CONFIRM;
- recommendation set boleh kosong;
- recommendation dapat berjalan tanpa capital input;
- recommendation dapat berjalan dengan capital input;
- recommendation tidak membaca CONFIRM;
- recommendation tidak berubah sebelum vs sesudah CONFIRM.

## D. Determinism Acceptance (LOCKED)

Recommendation **MUST** lolos replay deterministik.

## E. Cross-Layer Acceptance (LOCKED)

1. ticker recommended lalu confirmed tetap mempertahankan recommendation score/rank/label yang sama;
2. ticker non-recommended yang masih valid sebagai PLAN candidate tetap boleh memiliki hasil CONFIRM;
3. CONFIRM tidak boleh menjadi alasan recommendation baru muncul atau hilang.

## Final Rule

1. setiap item recommendation harus explainable melalui `reason_codes[]`;
2. recommendation harus lolos acceptance minimum untuk empty set, capital-free, capital-aware, dan replay deterministik;
3. recommendation dan confirm harus terbukti terpisah boundary-nya;
4. hasil CONFIRM tidak boleh menjadi sumber perubahan recommendation.

## B1. CONFIRM Overlay Boundary Reason-Code Reference

Reason code berikut boleh muncul pada output CONFIRM overlay. Kode ini bukan reason code recommendation final dan tidak boleh dipakai untuk mengubah `recommended_flag`, `recommendation_rank`, `recommendation_score`, `recommendation_label`, atau hash recommendation:

- `WS_CONFIRM_ELIGIBLE_RECOMMENDED`
- `WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED`
- `WS_CONFIRM_APPLIED`
- `WS_CONFIRM_NOT_APPLIED`
- `WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE`
- `WS_CONFIRM_REJECTED_NOT_PLAN_CANDIDATE`
- `WS_CONFIRM_NO_DATA`

## B2. Backtest Evaluation Reason-Code Boundary Reference

Reason code berikut boleh muncul setelah recommendation trade candidate dibekukan dan published price series dievaluasi. Kode ini bukan reason code recommendation dan tidak boleh mengubah `recommended_flag`, rank, score, label, atau hash recommendation:

- `BT_SKIP_NO_TRADABLE_ENTRY`
- `BT_SKIP_NO_TRADABLE_EXIT`

Keduanya hanya menjelaskan bahwa published bar tidak executable karena volume tidak positif. Recommendation tetap dipertahankan sebagai source candidate; backtest evaluation-lah yang di-skip dengan `ret_net = NULL`.
