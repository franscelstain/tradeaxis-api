# Canonicalization Contract — EOD Bars (LOCKED)

## Purpose

Define how immutable provider observations become provider-neutral canonical IDX Regular-Market EOD `RAW` bars and how missing, invalid, duplicate, stale, and unsupported fields are represented.

## Layer boundary (LOCKED)

The following layers are distinct:

1. **Raw source observation** — immutable provider/manual payload envelope and provenance.
2. **Normalized source row** — adapter output using provider-neutral names but still bound to the observation.
3. **Canonical `RAW` EOD bar** — validated Regular-Market OHLCV on market-observed scale.
4. **Analytical price product** — separately versioned `STRUCTURAL_ADJUSTED` or `TOTAL_RETURN` output.

Provider `adj_close` is not a canonical adjusted OHLCV product and must not bridge layers 3 and 4 implicitly.

## Provider field mapping (LOCKED)

For active `api_free/yahoo_finance`, the adapter maps the chart response conceptually as follows:

| Provider concept | Normalized/canonical target | Rule |
|---|---|---|
| response/meta symbol | provider symbol provenance | lineage only; resolve through effective-dated mapping |
| response/meta exchange timezone | timestamp normalization evidence | must be consistent with governed exchange/platform timezone mapping |
| timestamp | `trade_date`, provider observed timestamp | convert deterministically to `Asia/Jakarta`; requested-date membership required |
| quote open/high/low/close | `RAW` open/high/low/close | decimal-preserving validation; no zero/null substitution |
| quote volume | `RAW` volume | non-negative integer; zero is distinct from missing |
| adjclose | provider adjusted-close observation | optional lineage/source field only; never per-row analytical fallback |
| provider error/meta error | rejection/failure evidence | never parse as an empty successful dataset |

`manual_file` must declare an equivalent schema/version and map each column explicitly. Column position guessing, silent type coercion, and unmapped extra-field use are forbidden.

Future adapters map their payloads into the same normalized semantics and may not leak provider JSON paths, suffix rules, or proprietary status codes into consumer contracts.

## Canonical field model (LOCKED)

Required canonical `RAW` fields:

- stable `instrument_id` and `listing_id` semantics (`ticker_id` only as documented compatibility key)
- `trade_date`
- `open`, `high`, `low`, `close`
- `volume`
- price basis identity `RAW`
- source observation identity/hash/reference
- source/provider and selected mapping identity
- run/publication/revision context
- source observed timestamp and platform ingested timestamp

Nullable source fields must have explicit provenance and may include:

- previous/reference price
- actual traded value
- trade count/frequency
- board/market-segment code
- trading-status code
- provider adjusted-close observation

Unavailable optional facts are `NULL`/unknown, never fabricated as zero. Actual traded value and provider adjusted close must retain their own semantics and must not be derived or relabelled silently.

## Canonicalization pipeline (LOCKED)

1. Capture immutable observation envelope/reference and payload hash.
2. Validate response schema, timestamps, requested date/range, and stale state.
3. Resolve provider symbol through temporal listing/provider mapping valid on trade date.
4. Normalize types, units, timestamps, and provider codes without changing economic values.
5. Classify missing, invalid, duplicate, conflicting, and candidate rows.
6. Validate candidate rows against `EOD_Bars_Contract.md`.
7. Persist valid candidate canonical rows with observation linkage.
8. Persist invalid/rejected/missing evidence separately.
9. Hand off coverage/delivery evidence to promote; canonicalization itself does not create readability, indicators, eligibility, hash, seal, or current pointer.

## Missing versus invalid model (LOCKED)

- **Missing observation:** no provider observation exists for an expected listing/date after governed acquisition attempts. No canonical bar or zero placeholder is created.
- **Invalid observation:** a payload row exists but fails schema, identity, timestamp, OHLCV, or consistency rules. It is stored as rejection evidence linked to the observation.
- **Unknown expectation:** calendar/listing/status evidence cannot prove whether a bar was expected. It remains explicit and is not converted to missing, valid, or denominator exclusion silently.
- **Unavailable optional field:** canonical bar may remain valid when an explicitly nullable field is unavailable; the field is `NULL` with source capability/quality evidence.

Missing and invalid states have distinct reason codes and telemetry. Neither may appear as canonical zero-price rows.

## Duplicate/conflict rule (LOCKED)

- Byte/content-equivalent duplicate rows within one observation may be deterministically deduplicated with all source-row references preserved.
- Conflicting rows for the same stable listing/date within one observation or selected source run are ambiguous and must be rejected/quarantined unless an explicit provider sequence/revision field establishes a deterministic winner.
- Acquisition timestamp alone is not sufficient to overwrite a different observation or published value.
- A later re-fetch is a new observation and may replace published content only through revisioned correction/publication lineage.

## Economic-value preservation (LOCKED)

Canonicalization may normalize representation but must not repair economic content:

- no price/volume rounding beyond declared storage precision
- no forward fill, interpolation, zero fill, or prior-close candle
- no scale/stretch repair
- no automatic corporate-action adjustment
- no mixing provider `adj_close` with `RAW` close
- no deriving actual traded value from adjusted price multiplied by raw volume and naming it actual

## Rejection evidence

Each invalid/rejected candidate must retain:

- observation identity/hash/reference
- source row/path/index reference
- stable mapping result or mapping failure
- observed values without destructive normalization
- one or more governed reason codes
- run/checkpoint and recorded timestamp

Consumers must never use rejection storage as market-data input.

## Idempotency and publication safety (LOCKED)

Idempotent processing of the same observation/config produces the same candidate content/hash. It may not duplicate an immutable candidate identity.

Canonicalization must not update sealed publication content in-place. A changed historical observation creates a new correction run and publication revision; identical reprocessing is a no-op with evidence.

## Acceptance criterion (LOCKED)

For any provider row, the platform must deterministically prove one of: canonical `RAW`, invalid/rejected, missing expectation/delivery evidence, or held unknown state. No path may emit canonical zero price, mixed adjusted/raw scale, untraceable row, or provider-specific consumer field.

## Cross-contract alignment

- `Source_Data_Acquisition_Contract_LOCKED.md`
- `Source_Mapping_Contract_LOCKED.md`
- `EOD_Bars_Contract.md`
- `Invalid_Bar_Storage_Policy_LOCKED.md`
- `Market_Calendar_Requirements_Contract.md`
- `Symbol_Lifecycle_and_Mapping_Contract.md`
