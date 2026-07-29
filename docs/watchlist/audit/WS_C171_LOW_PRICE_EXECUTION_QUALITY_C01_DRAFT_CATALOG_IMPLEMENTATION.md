# C171 Low Price Execution Quality C01 Draft Catalog

## Scope

This stage implements and persists an immutable five-row DRAFT catalog derived from the completed comparative official-IS diagnostic. It does not run official IS, read or run OOS, promote a paramset, create PLAN/CONFIRM data, or activate production.

## Locked source evidence

- Anchor evaluation: `eval_id=192`, `param_set_id=5`
- Anchor params hash: `e49b47449be1bc59659455d315bb6aaf5f4f9491`
- Comparative diagnostic artifact hash: `f548a75e62ab954a3d35034b3b4452279693059e`
- Hypothesis-lock artifact hash: `84a699996dc8ac2eeea2bd921936a2d866f216ad`
- Primary focus: `LOW_PRICE_EXECUTION_QUALITY`

## Catalog

`WS_BT_GRID_LOW_PRICE_EXECUTION_QUALITY_C01_2026_07`, version `C01`, contains five immutable candidates:

1. mild signal-date tick-risk expansion cap (`1.50%`);
2. balanced signal-date tick-risk expansion cap (`1.00%`);
3. strict signal-date tick-risk expansion cap (`0.50%`);
4. isolated balanced score recalibration;
5. isolated risk-forward score recalibration.

The tick-risk guard is computed only from signal-date close, ATR14, stop multiplier, and IDX price fractions. Future entry prices and future returns are forbidden as selection inputs.

## Command

`watchlist:backtest-c171-persist-low-price-execution-quality-c01-draft-catalog`

Expected successful status:

`C171_IMMUTABLE_LOW_PRICE_EXECUTION_QUALITY_C01_DRAFT_CATALOG_PERSISTED`

## Boundary

- exactly five DRAFT paramsets;
- no ACTIVE paramset;
- no official IS runtime;
- no OOS repository/table read;
- no promotion;
- no PLAN/CONFIRM mutation;
- `production_ready=false`.
