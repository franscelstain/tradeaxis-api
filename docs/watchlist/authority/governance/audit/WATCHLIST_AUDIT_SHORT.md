# Watchlist Audit Short

Gunakan audit cepat ini sebelum audit penuh.

## Quick Pass / Fail

- [ ] scope masih watchlist only / weekly_swing only
- [ ] PLAN candidate states bukan final recommendation
- [ ] `TOP_PICKS` hanya final qualified RECOMMENDATION
- [ ] recommendation boleh kosong dan tidak dipaksa quota
- [ ] complete active scoring features wajib; missing active feature fail-closed
- [ ] `score_total` normalized weighted-sum dan `recommendation_score` terikat ke `score_total`
- [ ] capital tidak mengubah membership/rank
- [ ] CONFIRM hanya final Top Picks, D+1 entry window, dan hanya current actionability
- [ ] backtest/OOS mengukur final Top Picks
- [ ] production proof memakai realistic friction + non-zero slippage + stress
- [ ] ranking usefulness ikut dibuktikan
- [ ] OOS tanpa retuning
- [ ] full-flow forward shadow termasuk CONFIRM sebelum production-use approval
- [ ] implementation translation sinkron dengan strategy

## Quick Verdict

- semua centang aman -> lanjut audit penuh
- ada 1 boundary/product-proof besar gagal -> jangan nilai tinggi
- ada scope leak atau proof target salah -> turunkan nilai keras
