# Finding — the provider's per-bar timestamp is not carried, and the acquisition clock is stored as `source_timestamp`

- ID: `F-MD-B18-A002-027`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-03T08:45:44+07:00
- Severity: `P2` — no wrong value is published; a provider-supplied timestamp is dropped and one platform time is mislabelled
- Status: `OPEN` — owner decision required before any implementation (`E-MD-B18-A002-093`, Q6)
- Class: `IMPLEMENTATION_DEFECT`
- Related: `E-MD-B18-A002-093`, `E-MD-B18-A002-092`, `F-MD-B18-A002-025`, `F-MD-B18-A002-017`, `D-MD-B18-A002-013`
- Remediation owner: `MD-B18-A002` for the decision; the producers touched are acquisition (`MD-B07`), canonical import (`MD-B09`) and the V2 bar artifact (`MD-B10`), none of which is reopened by this record

## Observed

The independent review of candidate-v1 asked why the frozen provider response carries a timestamp while the oracle expects `source_timestamp = NULL`. The trace below was run on the real production path (synthetic V2 world, `diagnostics/provider_timestamp_trace.json`).

| Step | What is there |
|---|---|
| Frozen response (`inputs/provider_response.json`) | `timestamp: [1774231200]` — one element of the chart series: `2026-03-23 02:00:00` UTC, `2026-03-23 09:00:00` `Asia/Jakarta`. The payload has no response-level observation time (no `regularMarketTime`, no generated-at). |
| What it is | The bar's own instant (the start of its trading day), not the time the provider produced or served the response. |
| Adapter (`PublicApiEodBarsAdapter`, chart parser) | Uses it only to derive `trade_date` in the exchange timezone and to check array alignment. The row it hands on has no `source_timestamp`; it has `captured_at`, the platform acquisition clock. |
| Immutable envelope (`md_source_observations`, capture and accepted rows) | `source_timestamp` NULL. The raw timestamp survives only inside the payload bytes, bound by `payload_hash` and `payload_ref`. |
| Normalized row (`md_source_observation_rows`) | `source_timestamp = 2026-03-25 10:30:00` — the run clock. `SourceObservationRepository` stores `$row['source_timestamp'] ?? $row['captured_at'] ?? …`, so the acquisition clock is written under the provider-timestamp name. |
| Canonical bar (`eod_bars`) | `source_timestamp` NULL, `acquired_at = 2026-03-25 10:30:00`. |
| V2 observation manifest | Entries are built from the envelope rows, so `source_timestamp` is NULL; the provider time is bound only indirectly, through `payload_hash`. |
| V2 bars artifact | The `source_timestamp` column is NULL for this adapter. |

## Answers to the five review questions

A. The timestamp present is the chart-series bar timestamp, `1774231200`.
B. It represents the bar's own instant, not provider observation time.
C. `Source_Data_Acquisition_Contract_LOCKED.md:114` requires the envelope to bind a provider observation timestamp when one is available, and `:162` requires the provider timestamp, trade date and exchange timezone to be consistent. `Audit_Hash_and_Reproducibility_Contract_LOCKED.md:59` and `Downstream_Consumer_Read_Model_Contract_LOCKED.md:27` require the source timestamp and the acquired-at timestamp where consumer-visible availability needs them. No document says which instant is "the source timestamp" for a series-only payload, nor which field carries it.
D. Production retains it only inside the payload bytes. No timestamp field carries it, and one platform time is stored under its name.
E. The candidate-v1 oracle used NULL because the author transcribed what production produced for the envelope and the bar. `inputs/synthetic_world.json` froze `provider_timestamp_unix`, but the oracle never used it and never asked what authority requires. On this point the oracle was not independent. That is an author defect in candidate-v1, and the reason it must not be approved.

## Classification

**OUTCOME B — production implementation defect, with an authority gap that stops the fix.** Two parts:

1. *Defect, no owner choice needed to see it:* a normalized row records the acquisition clock as `source_timestamp`, and the provider's per-bar instant is dropped by the adapter while a consumer-visible `source_timestamp` is part of the V2 bars hash.
2. *Owner choice needed to fix it:* which instant is the source timestamp for a series-only payload, which field carries it (normalized row and bar, envelope, or both), and in which timezone and format. Each choice changes `source_timestamp` in the V2 bars artifact for every bar this adapter produces. That is not a local test fix.

The candidate cannot assert a literal for the bars hash or the observation manifest until this is decided, because either answer can move them.

## B10 and predicate impact (assessment, not implementation)

No production code was changed for this finding. Expected classification of the options in `E-MD-B18-A002-093` Q6: any option that changes what the adapter, `SourceObservationRepository` or `EodBarsIngestService` write is `B10_PROOF_REVALIDATION_REQUIRED` until shown otherwise, because it feeds the V2 bars artifact and the producer source-observation capture. B10's own proofs build rows directly and supply their own `source_timestamp`, so they are expected to be unaffected; that must be proven when the change is made, not assumed. `MD-S053-R0068` (`MD-B07`, `SATISFIED`, `E-MD-B07-A001-001`) is not reassessed here; whether its closure covers a series-only payload is for the owner of that stage. `MD-B07`, `MD-B09` and `MD-B10` stay closed.

## Not in scope here

No change to the adapter, repositories, hashing or any closed stage. Candidate-v1 is not edited.
