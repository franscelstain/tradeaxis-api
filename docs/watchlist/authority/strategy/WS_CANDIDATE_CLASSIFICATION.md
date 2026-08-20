# Watchlist Weekly Swing — Candidate Classification

## Purpose

Dokumen ini menetapkan candidate-state classification setelah absolute Weekly Swing eligibility diterapkan. Classification tidak membuat quota recommendation dan tidak menjadi second ranking engine.

## Lifecycle Position

- **Stage:** `WS-S02` — Eligibility and Candidate Classification.
- **Consumes:** absolute eligibility/setup outcomes from `WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md`.
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
- `WATCH_ONLY` = ticker yang **tidak mempunyai hard safety/executability disqualifier**, tetapi belum recommendation-eligible karena quality/setup sufficiency belum lulus, required alpha/score feature belum lengkap, atau conditional quality rule aktif belum terpenuhi; ticker ini hanya monitoring/diagnostic dan tidak boleh dipromosikan pada run yang sama;
- `AVOID` = ticker yang mempunyai **hard disqualifying condition** seperti data unusable untuk candidate path, known non-executable/suspended state, hard liquidity/price/risk exclusion, atau explicit active safety/event rule yang melarang new entry.

Tidak ada `PRIMARY` atau `SECONDARY` fallback tier pada canonical strategy.

## WATCH_ONLY vs AVOID Deterministic Boundary

Classification wajib mengikuti severity berikut:

1. bila ada hard safety/data/executability/disqualifying condition → `AVOID`;
2. bila tidak ada hard disqualifier tetapi recommendation-quality/setup/feature sufficiency belum lengkap → `WATCH_ONLY`;
3. hanya bila seluruh hard gate dan recommendation-candidacy prerequisites lengkap → `RECOMMENDATION_CANDIDATES`.

`WATCH_ONLY` bukan near-buy tier, fallback queue, atau lower-confidence recommendation. `AVOID` bukan prediction bahwa harga pasti turun. Keduanya adalah deterministic eligibility states untuk run tersebut.

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

## Market-Data Gap Classification Consistency

Candidate state tidak boleh dipakai untuk menutupi upstream data gap.

- Hard upstream data/safety/executability gap yang membuat normal new-entry eligibility tidak dapat dibuktikan **MUST** memetakan affected ticker ke `AVOID`, bukan `WATCH_ONLY` atau recommendation fallback.
- Incomplete required quality/score fact dapat memetakan ticker ke `WATCH_ONLY` hanya bila upstream row usable dan tidak ada hard safety/data/executability disqualifier.
- Classification **MUST NOT** membuat, mengestimasi, atau mempromosikan missing market fact untuk memindahkan ticker ke state yang lebih tinggi.
