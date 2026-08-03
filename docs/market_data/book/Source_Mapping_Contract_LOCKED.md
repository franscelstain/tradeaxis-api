# Source Mapping Contract — Provider Adapters (STRATEGY LOCKED)

## Boundary

An adapter converts one immutable provider observation into provider-neutral normalized candidates. It does not decide canonical winners, historical identity, adjustment factors, indicators, coverage, eligibility, or readability.

## Common normalized fields

- observation/run/attempt identity and requested trade date;
- provider, provider symbol, temporal mapping revision;
- provider row/timestamp and acquired-at time;
- mapped listing identity when unambiguous;
- Regular-Market trade date/session/board context where supplied;
- raw open, high, low, close, volume;
- nullable source-backed previous close, actual traded value, trade count, status;
- nullable provider adjustment field preserved as provider lineage only;
- schema/adapter/canonicalization versions and validation outcome/reasons.

Yahoo-specific field paths and `.JK` routing remain inside its adapter configuration. Normalized/canonical consumers do not depend on provider vocabulary.

## Mapping rules

- source timestamp/date must resolve to the requested completed IDX Regular-Market session in `Asia/Jakarta`;
- provider symbol must resolve through an effective-and-known mapping to stable listing identity;
- raw OHLC must be present, positive, and internally consistent; volume must be present and non-negative;
- actual traded value/trade count are populated only when directly source-backed and unit-validated;
- provider `adj_close` may remain nullable observation metadata but is never canonical RAW close, never the `STRUCTURAL_ADJUSTED` product, and has no close fallback semantics;
- missing required fields, wrong/stale date, schema mismatch, mapping ambiguity, and conflicting duplicates become reason-coded rejected/quarantined candidates linked to the observation;
- no placeholder zero, forward fill, latest-wins conflict resolution, or synthetic price correction is allowed.

## Missing versus not expected

Provider absence says only “not delivered.” Verified temporal calendar/status evidence separately decides whether a bar was expected. Suspension/no-trade status cannot be inferred from an empty provider response.

## Correction

Refetch/manual recovery creates a new immutable observation and candidate lineage. It never edits a prior observation, canonical snapshot, factor, or sealed publication. Accepted corrections create a new publication through the normal correction/reseal flow.
