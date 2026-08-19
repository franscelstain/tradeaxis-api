# Watchlist Implementation

Translation layer dari canonical strategy ke kontrak teknis, persistence, API guidance, procedure, test, fixtures, dan examples. Dokumen di sini boleh berubah mengikuti implementasi selama semantics strategy tidak berubah.

## Market Data Intake Entry

Sebelum mengimplementasikan PLAN/backtest yang membaca Market Data, baca `weekly_swing/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`. Producer-facing read contract adalah semantic authority; direct Market Data tables bukan normal Watchlist intake path.


## Legacy Field Compatibility

Beberapa aset implementasi lama masih memakai nama fisik/serialized `dv20_idr` dan `vol_ratio`. Untuk current semantic interpretation:

- `dv20_idr` hanya compatibility alias untuk `adv20_close_volume_proxy_idr`; nama ini MUST NOT ditafsirkan sebagai actual traded-value turnover.
- `vol_ratio` hanya compatibility alias untuk canonical `vol_ratio_20` apabila producer version menjamin formula yang sama.
- `adv20_traded_value_idr_actual` adalah fact berbeda. Mengganti liquidity selection dari proxy ke actual traded value mengubah strategy/proof identity dan membutuhkan keputusan + re-proof.

Physical rename atau migration terhadap field legacy adalah pekerjaan implementation alignment; itu bukan alasan mengubah canonical strategy.
