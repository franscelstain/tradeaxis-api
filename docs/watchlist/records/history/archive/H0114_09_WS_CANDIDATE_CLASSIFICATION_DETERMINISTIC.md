# 09 — WS Candidate Classification (Deterministic)

## Purpose

Dokumen ini menetapkan candidate-state classification setelah absolute Weekly Swing eligibility diterapkan. Classification tidak membuat quota recommendation dan tidak menjadi second ranking engine.

## Qualification Before Ranking

Urutan wajib:

1. authoritative data/readiness check;
2. mandatory absolute eligibility/setup/risk gates;
3. complete active scoring features;
4. deterministic scoring dan ordering;
5. candidate-state classification.

Relative ranking tidak boleh membuat ticker yang gagal absolute quality menjadi recommendation candidate hanya karena ticker tersebut lebih baik daripada market yang sedang buruk.

## Candidate-State Semantics

- `RECOMMENDATION_CANDIDATES` = ticker yang memenuhi seluruh candidate-level hard gate dan boleh dievaluasi oleh final recommendation algorithm;
- `WATCH_ONLY` = ticker yang masih layak dipantau/diagnostic tetapi tidak recommendation-eligible pada run tersebut;
- `AVOID` = ticker yang gagal mandatory Weekly Swing rule atau active disqualifying rule.

Tidak ada `PRIMARY` atau `SECONDARY` fallback tier pada canonical strategy.

## Count Rule

Tidak ada state yang wajib mempunyai minimum non-zero count.

Strategy harus menerima kondisi:
- recommendation candidate pool kosong;
- hanya sedikit recommendation candidates tersedia;
- WATCH_ONLY/AVOID mendominasi market;
- final Top Picks kosong.

Target count, minimum-count override, quantile quota, atau presentation cap tidak boleh memaksa ticker melewati absolute qualification floor.

## Relative Measures

Quantile/percentile boleh dipakai **di dalam versioned score transform** bila diperlukan, tetapi:
- hanya memakai same-date causal universe;
- tidak menggantikan absolute eligibility/setup floor;
- tidak menentukan jumlah Top Picks;
- deterministic pada replay.

## Relationship to Recommendation

`RECOMMENDATION_CANDIDATES` hanya menentukan source universe final recommendation.

Final Top Picks tetap membutuhkan seluruh final recommendation gates dan absolute recommendation quality floor.

## Final Rules

1. PLAN classification adalah candidate state, bukan final recommendation.
2. Tidak ada forced minimum candidate count.
3. Tidak ada PRIMARY/SECONDARY fallback path.
4. Relative measures tidak boleh menggantikan absolute quality floor.
5. Final Top Picks count boleh nol atau sebanyak candidate yang benar-benar lulus final qualification.
