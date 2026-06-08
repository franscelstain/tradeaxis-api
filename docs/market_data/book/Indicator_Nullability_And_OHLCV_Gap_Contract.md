# Indicator Nullability and OHLCV Gap Contract

Status: LOCKED as target behavior for EOD indicator computation and publication semantics.

## Purpose
This contract separates data completeness, publication completeness, and indicator availability. It prevents normal data states from becoming global runtime errors.

## Locked rules

1. `market_calendar` is the authority for whether a date is a trading day.
2. Active/listed ticker universe is evaluated as-of the trade date.
3. Indicator outputs are nullable per field. A missing dependency for one formula must not nullify unrelated formulas on the same row.
4. Dataset-start insufficient history is normal. If the database begins on 2023-01-02, early dates may have no MA20, MA50, ROC20, ATR14, or volume-derived fields until each formula has enough valid bars.
5. Ticker-listed-date insufficient history is normal. A ticker listed later than the dataset start accumulates indicators gradually according to its own available bars.
6. Missing sector-index benchmark bars leave sector rotation fields NULL. They must not be fabricated.
7. Missing event-risk source rows leave event-risk fields NULL unless a source-backed carry-forward state exists.
8. A missing provider OHLCV row for an active/listed ticker on a valid trading day may be represented as an OHLCV zero-placeholder in the publication artifact to preserve universe coverage.
9. Zero-placeholder OHLCV rows are not valid price inputs. Any price, turnover, range, relative-strength, or moving-average formula depending on a zero-placeholder must return NULL/invalid for that field.
10. Publication must fail for invalid calendar, broken artifact/hash/seal invariants, or malformed source data. It must not fail solely because an indicator field is NULL due to insufficient history or a zero-placeholder dependency.

## Examples

- A ticker with 22 valid bars may have `ma20`, `roc20`, and some range fields, while `ma50` remains NULL.
- A newly listed ticker with 10 valid bars may have close/volume context but no MA20/MA50/ROC20.
- A sector benchmark missing for 2026-06-04 leaves `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` NULL for that date while equity-only indicators still compute.
- A provider-missing row represented by OHLCV zeros keeps the ticker in the publication universe, but those zeros must not enter MA/ROC/ATR/range math as real market prices.



## Source/master vs publication-bound indicator scope (LOCKED)
"Without updating sector, corporate action, trading status, or master data" means no writes to source/master tables and no source import commands.

It does **not** mean that publication-bound context columns inside a new `eod_indicators` publication must stay frozen. A valid recompute publication may recalculate `sector_code`, sector-rotation fields, corporate-action fields, trading-status fields, and event-risk fields from the existing source/master data already present in the database.

If the intended behavior is to recompute only technical numeric fields while preserving prior publication context columns unchanged, that must be an explicit future `technical-only` mode. No such production-safe command is currently approved.

See `Indicator_Recompute_Source_Scope_Contract.md` for the locked source-read-only vs publication-recompute boundary.

## Forbidden

- Treating insufficient history as a global publication error.
- Treating `close=0` placeholder as a real traded price.
- Forward-filling sector rotation, event-risk, or indicator values without a source-backed rule.
- Keeping unproven commands or runbook instructions that claim indicator-only republish is production-ready when runtime proof failed.
