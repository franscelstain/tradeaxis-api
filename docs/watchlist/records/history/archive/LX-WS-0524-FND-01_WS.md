# Legacy Role Extract — WS — FINDING

> **Document Type:** FINDING
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0524-FND-01`
> **Legacy Source ID:** `LS-WS-0524`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`
> **Original SHA1:** `7A7BF3EE3865CDC60837AA1BD470FB865EAC7B99`
> **Source Sections:** L159-L172 Executable price and gap-fill boundary
> **Extract Body SHA1:** `FB70E139BBEC5574E3FD9AD1CD0D991B2547699B`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Executable price and gap-fill boundary

Published OHLC availability is not sufficient evidence of an executable fill. The pricing evaluator must:

- require raw tradable integer-rupiah OHLC and reject adjusted-looking fractional bars;
- preserve theoretical `stop_price` / `target_price` separately from normalized trigger levels;
- fill an opening gap through stop or target at the bar open;
- use conservative stop-floor / target-ceil normalization for intraday triggers;
- emit `trigger_price`, `executed_price`, `fill_rule`, `gap_detected`, and price-rule markers;
- fail closed on adjusted-looking/non-executable OHLC rather than fabricate a raw fill.
- force these semantics at runtime; caller/grid overrides are ignored so artifact labels cannot diverge from actual fill behavior.

Changing these semantics changes `eval_model` and requires fresh IS/OOS evaluation rows; historical rows remain immutable.
