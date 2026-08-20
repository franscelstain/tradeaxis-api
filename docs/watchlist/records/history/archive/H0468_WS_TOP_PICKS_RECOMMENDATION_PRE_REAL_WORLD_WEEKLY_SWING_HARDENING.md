# Watchlist Weekly Swing — Top Picks Recommendation

## Purpose

RECOMMENDATION adalah final decision-support selection layer Weekly Swing. Layer ini menentukan saham mana yang cukup layak untuk disebut **Top Picks** dan mengurutkannya dari kualitas tertinggi ke terendah.

## Lifecycle Position

- **Stage:** `WS-S04` — Final Recommendation and Ranked Top Picks.
- **Consumes:** immutable PLAN.
- **Produces:** semantic definition of final qualified `TOP_PICKS`, including valid empty set.
- **Next:** core proof may continue to `WS-S06`; optional `WS-S05` CONFIRM may run independently when valid decision-time data is available.

## Source

Recommendation hanya boleh membaca immutable PLAN output untuk `trade_date` yang sama.

Source candidate hanya `RECOMMENDATION_CANDIDATES`.

`WATCH_ONLY` dan `AVOID` tidak boleh menjadi final recommendation pada run yang sama.

## Qualified Recommendation Principle

Recommendation memakai **qualification**, bukan quota.

Satu ticker masuk final Top Picks hanya jika seluruh mandatory recommendation gate lulus. Bila 0 ticker lulus, output Top Picks harus kosong. Bila 12 ticker lulus, seluruh 12 ticker tetap qualified recommendations dan diurutkan `1..12`.

UI boleh menonjolkan sebagian rank untuk kenyamanan, tetapi presentation limit tidak boleh mengubah strategy membership.

## Top Picks Meaning

`TOP_PICKS` adalah nama final recommendation set dan tidak digunakan untuk PLAN candidate state.

Setiap Top Pick harus mempunyai:
- recommendation rank;
- canonical quality score;
- reason/explanation yang dapat diturunkan dari rule strategy;
- PLAN entry dan predeclared exit/risk-plan binding;
- optional current-actionability state bila CONFIRM tersedia; absence of CONFIRM does not reduce recommendation validity.

## Recommendation Publication Binding

Setiap final recommendation set harus mengikat minimal:

- `recommendation_trade_date`;
- `recommendation_available_at`;
- source EOD publication/read identity;
- intended canonical entry session;
- applicable `minimum_decision_lead_time_minutes`.

Canonical baseline minimum decision lead time adalah **30 minutes** sebelum earliest intended entry opportunity. Value yang lebih konservatif boleh ditetapkan melalui versioned strategy identity; value tidak boleh dipersingkat setelah melihat outcome.

Jika recommendation belum tersedia sebelum cutoff tersebut, system tidak boleh menganggap `open(D+1)` sebagai executable user price. Runtime/evaluation harus memakai later causal executable rule yang memang dibuktikan atau menyatakan entry opportunity non-executable untuk canonical proof.

## Recommendation Score Meaning

`recommendation_score` adalah ordered quality score dan bukan probability of profit. User-facing probability/confidence percentage memerlukan separate calibrated capability proof dan tidak boleh diturunkan langsung dari score.

## Capital Independence

Modal pengguna, affordability, atau jumlah lot tidak mengukur kualitas saham dan **tidak boleh**:
- menambah atau menghapus Top Pick;
- mengubah recommendation score;
- mengubah recommendation rank.

Optional capital/lot information boleh ditampilkan setelah Top Picks selesai sebagai informational enrichment saja.

## Relationship to CONFIRM

Recommendation dibentuk tanpa CONFIRM.

CONFIRM tidak mengubah historical recommendation membership atau rank. CONFIRM adalah optional current-actionability overlay. Missing/stale/incomplete CONFIRM data tidak boleh membuat Top Pick gagal atau menjadi `NOT_ACTIONABLE`; Top Pick tetap sah sebagai EOD recommendation dan CONFIRM dapat dicoba lagi bila valid data tersedia dalam entry window.

## Final Rules

1. Final Top Picks adalah seluruh dan hanya candidate yang lulus recommendation qualification.
2. Top Picks count tidak mempunyai fixed quota dan boleh nol.
3. Ranking Top Picks harus merepresentasikan canonical PLAN quality ordering setelah final qualification.
4. Capital tidak memengaruhi kualitas atau ranking recommendation.
5. Recommendation harus dapat dievaluasi langsung pada backtest/OOS; PLAN candidate state tidak boleh menjadi proxy untuk final recommendation proof.

## EOD Action-Intent Binding

Setiap current Top Pick set harus menyimpan recommendation truth terpisah dari current action intent.

Required temporal/action fields minimum:

- `requested_trade_date`;
- `effective_trade_date`;
- `recommendation_generated_at`;
- `intended_entry_session`;
- `canonical_entry_cutoff`;
- `action_window_status`;
- per-pick `action_intent`.

Canonical action-intent rules:

- qualified Top Pick + action window `OPEN` → `ENTRY_CANDIDATE_NEXT_TRADING_SESSION`;
- qualified EOD result + action window `EXPIRED` → `ACTION_WINDOW_EXPIRED`, not a current new-entry suggestion;
- zero final qualified candidates → `NO_QUALIFIED_TOP_PICKS`;
- no valid same-date EOD recommendation because upstream is not ready → no Top Pick set is created; runtime availability remains `MARKET_DATA_UNAVAILABLE_RETRYABLE`.

`ENTRY_CANDIDATE_NEXT_TRADING_SESSION` berarti **layak dipertimbangkan**, bukan guaranteed buy/fill. Optional CONFIRM dapat mengubah current interpretation menjadi `ACTIONABLE` atau `NOT_ACTIONABLE`, tetapi tidak mengubah membership/rank/recommendation truth.

Late/expired Top Pick record tidak boleh otomatis diteruskan sebagai entry candidate pada session setelah `intended_entry_session`; next opportunity harus dibentuk dari new governed EOD run.

## Temporal Ownership in Top-Picks Output

Top-Picks payload boleh membawa field dari dua domain untuk auditability, tetapi semantic ownership tetap terpisah:

- Producer provenance pada recommendation **MUST** memuat exact `effective_trade_date`, `market_data_published_at`, dan `market_data_revision_id` yang dikonsumsi; field tersebut tetap Market-Data-owned walaupun tersimpan pada record Watchlist.
- Recommendation lifecycle **MUST** memuat Watchlist-owned `requested_trade_date`, `recommendation_generated_at`, `intended_entry_session`, `canonical_entry_cutoff`, dan `action_window_status` tanpa mengklaim bahwa field tersebut diterbitkan oleh Market Data.
- Satu issued recommendation version harus immutable terhadap temporal provenance-nya; rerun karena Market Data revision atau retry yang menghasilkan result baru harus mempunyai explicit new recommendation/run lineage, bukan overwrite diam-diam terhadap record lama.

