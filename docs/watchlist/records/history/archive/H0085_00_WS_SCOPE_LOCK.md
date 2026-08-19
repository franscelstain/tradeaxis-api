# Watchlist Scope Lock

## Current Active Scope

- domain: `watchlist`
- active policy: `weekly_swing`
- active layers: `PLAN`, `RECOMMENDATION`, `CONFIRM`

## In Scope

- Weekly Swing watchlist behavior;
- PLAN / RECOMMENDATION / CONFIRM sebagai lapisan watchlist;
- deterministic watchlist suggestions;
- recommendation berbasis PLAN;
- confirm berbasis candidate PLAN;

## Out of Scope

- portfolio;
- execution;
- order lifecycle;
- position management;
- holdings / PnL / trade journal;
- market-data internals;
- provider acquisition / fetch / retry / scheduler;
- policy lain di luar `weekly_swing`.

## Hard Boundary Rules

1. `watchlist` hanya menampilkan saran, bukan transaksi nyata.
2. `weekly_swing` adalah satu-satunya policy aktif dalam scope Watchlist saat ini.
3. Watchlist tidak boleh mendefinisikan atau mengubah behavior internal Market Data.
4. Watchlist tidak boleh menjalankan behavior portfolio, position management, atau execution.

## Data Reality Lock

Weekly Swing harus tetap realistis terhadap sumber data yang tersedia melalui:
- provider gratis;
- input manual.

Weekly Swing tidak boleh secara normatif bergantung pada sumber data yang berada di luar constraint tersebut.
