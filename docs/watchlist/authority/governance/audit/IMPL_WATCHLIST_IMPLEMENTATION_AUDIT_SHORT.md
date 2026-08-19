# Watchlist Implementation Audit Short

Gunakan `PASS`, `PARTIAL`, `FAIL`, `N/A`.

## Applicability Rule
- tanpa code/runtime nyata: code-specific item = `N/A`
- dengan Layer C aktif tetapi evidence incomplete: `PARTIAL`

| Area | Cek inti | Status | Catatan |
|---|---|---|---|
| Scope | weekly_swing only |  |  |
| PLAN | candidate tier semantics baru |  |  |
| Recommendation | final qualified Top Picks, zero allowed |  |  |
| Ranking | score_total-based, deterministic, capital-independent |  |  |
| Confirm | final Top Picks only, actionability only |  |  |
| Backtest | evaluates final recommendation |  |  |
| Costs | realistic cost + non-zero slippage + stress |  |  |
| OOS | no retuning + rank-quality metrics |  |  |
| Shadow | production-use gate tersedia |  |  |
| Evidence | test/runtime proof tersedia |  |  |

## Verdict Guard

Jangan beri PASS jika implementation masih mengikuti pre-revision semantics walaupun strategy docs sudah benar.
