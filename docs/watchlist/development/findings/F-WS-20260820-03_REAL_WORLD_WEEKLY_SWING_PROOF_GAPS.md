# F-WS-20260820-03 — Real-World Weekly Swing Proof Gaps

> **Document role:** FINDING
> **Status:** RESOLVED_BY_D-WS-20260820-03
> **Date:** 2026-08-20

## Finding

Canonical EOD Weekly Swing strategy sudah kuat pada data ownership, temporal action intent, IS/OOS, friction, tail risk, dan production health, tetapi belum mengunci secara cukup deterministic delapan real-world concerns: repeated same-listing recommendation versus followability, corporate-action economic return, supported execution mechanism, condition-dependent EOD slippage, follower replay Top-1/3/5, edge-concentration fragility, Market Data correction sensitivity, dan shortened/half-day session comparability.

## Risk

Tanpa penguatan ini, backtest dapat terlihat lebih baik daripada pengalaman pengguna nyata melalui double-counting repeated signals, price-only return yang mengabaikan entitlement, asumsi execution mode yang terlalu luas, friction yang terlalu seragam, edge yang terkonsentrasi pada sedikit outlier, sensitivity tinggi terhadap correction, atau penggunaan session-sensitive feature pada shortened session tanpa comparability proof.

## Boundary

Remediation harus tetap **EOD Weekly Swing decision support**. Runtime tidak boleh bergantung pada portfolio pengguna, realtime, intraday, orderbook, atau broker state; semua market facts tetap Market Data-owned.

## Resolution

Resolved by `D-WS-20260820-03` melalui controlled strategy revision dan rule-level traceability reset untuk seluruh clause baru.
