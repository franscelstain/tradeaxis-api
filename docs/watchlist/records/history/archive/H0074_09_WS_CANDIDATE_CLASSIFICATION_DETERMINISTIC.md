# 09 — WS Candidate Classification (Deterministic)

## Purpose

Dokumen ini menetapkan candidate-state classification setelah absolute Weekly Swing eligibility diterapkan. Classification tidak membuat quota recommendation dan tidak menjadi second ranking engine.

## Lifecycle Position

- **Stage:** `WS-S02` — Eligibility and Candidate Classification.
- **Consumes:** absolute eligibility/setup outcomes from `03_WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`.
- **Produces:** exactly one state per ticker: `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, or `AVOID`.
- **Next:** only `RECOMMENDATION_CANDIDATES` enter `WS-S03` PLAN scoring.

## Qualification and Classification Order

Urutan wajib:

1. authoritative data/readiness check;
2. mandatory absolute eligibility/setup/risk gates;
3. verify complete active features yang diwajibkan candidate/scoring path;
4. deterministic candidate-state classification;
5. hanya `RECOMMENDATION_CANDIDATES` diteruskan ke deterministic scoring dan ordering di PLAN.

Candidate-state classification terjadi setelah seluruh absolute candidacy requirement lengkap tetapi **mendahului scoring**. Relative ranking tidak boleh membuat ticker yang gagal absolute quality menjadi recommendation candidate hanya karena ticker tersebut lebih baik daripada market yang sedang buruk.

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

## Relationship to PLAN and Recommendation

`RECOMMENDATION_CANDIDATES` menentukan ticker yang boleh menerima canonical PLAN score dan menjadi source universe final recommendation.

`WATCH_ONLY` dan `AVOID` tidak menerima jalur scoring untuk memperebutkan final Top Picks pada run yang sama.

Final Top Picks tetap membutuhkan seluruh final recommendation gates dan absolute recommendation quality floor.

## Final Rules

1. Candidate-state classification terjadi setelah absolute eligibility dan sebelum PLAN scoring.
2. PLAN classification adalah candidate state, bukan final recommendation.
3. Hanya `RECOMMENDATION_CANDIDATES` masuk canonical scoring/recommendation path.
4. Tidak ada forced minimum candidate count.
5. Tidak ada PRIMARY/SECONDARY fallback path.
6. Relative measures tidak boleh menggantikan absolute quality floor.
7. Final Top Picks count boleh nol atau sebanyak candidate yang benar-benar lulus final qualification.
