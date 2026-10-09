# F-MD-B01-A014-001 — `eligibility_export.csv` ships only the optional legacy projection

- Status: `RESOLVED`
- Severity: `P2`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A014` / `MD-B01-A014-BL001` / `MD-REBASELINE-20260820-001`
- Owning stage for remediation: `MD-B19` (`W19` — operations, observability, evidence export)
- Blocks: `MD-S075-R0014` and the `eligibility_export.csv` column rules at `MD-B19` entry. Does not block `MD-B01`.

## Finding

`ops/Run_Artifacts_Format_LOCKED.md` §4 states the minimum columns for `eligibility_export.csv`:

> `trade_date`, stable `listing_id`, optional compatibility/display `ticker_id` / `ticker_code`, `publication_id`, `data_usable`, complete deterministic `reason_codes` / reason-set representation, optional legacy `eligible` and primary `reason_code` projection, never as the sole V2 meaning.

The implementation writes four columns:

```
app/Application/MarketData/Services/MarketDataEvidenceExportService.php:141
$this->writeCsv($dir.'/eligibility_export.csv', ['trade_date', 'ticker_id', 'eligible', 'reason_code'], $eligibilityRows);
```

| Contract column | Status | Note |
|---|---|---|
| `trade_date` | present | |
| `listing_id` (stable) | **absent** | mandatory |
| `ticker_id` / `ticker_code` | `ticker_id` present | optional compatibility/display |
| `publication_id` | **absent** | mandatory |
| `data_usable` | **absent** | mandatory |
| `reason_codes` (complete set) | **absent** | mandatory; only the singular `reason_code` ships |
| `eligible`, `reason_code` | present | the optional legacy projection |

So the artifact ships the optional half and drops the mandatory half. The contract's own words for the failing state are "never as the sole V2 meaning", and `eligible` is currently the sole meaning the file carries.

## Why this is more than a missing column

`Domain_Boundary_Invariants_LOCKED.md` states that `eligible` "is the most policy-suggestive name on the entire market-data surface" and that every contract using it must repeat that it means `data_usable`, "and that repetition is the only thing preventing the misreading". An evidence export whose only usability column is `eligible` is exactly the artifact that misreading is made from — and unlike the frozen strategy document in `F-MD-B01-A003-001`, this one is editable today.

## The data is already there

Every missing column exists on `eod_eligibility`: `listing_id`, `publication_id`, and `eligibility_reasons_json` are all persisted columns, and the export query at `EodEvidenceRepository::exportEligibilityRows` simply does not select them. `data_usable` has no stored column and is derived from `eligible`, exactly as `MarketDataReadProductRepository` already derives it for the read product.

Remediation is a projection change plus a test, not a schema or semantic change.

## Why this attempt did not fix it

`CI-MD-B01-A014-001` declares no runtime mutation, and this attempt's baseline binds a scope of traceability classification and alias-naming proof. `MD-S075` is owned by `MD-B19`, which has never been opened: it has no baseline, no Change Impact Declaration, and no current revalidation. Changing an operations artifact under another stage's attempt, outside its declared change impact and without its baseline, would be the ungoverned kind of change this track exists to prevent.

The correct handling is to record the measurement now so `MD-B19` opens with it, rather than to widen an attempt past its own declared impact.

## Required outcome

At `MD-B19` entry: extend `exportEligibilityRows` to select `listing_id`, `publication_id`, and the reason set, derive `data_usable` from `eligible`, emit the contract's column order, and prove the header and a populated row by execution rather than by asserting the file exists — which is all `MarketDataEvidenceExportServiceTest` currently checks for this artifact.

## Related

- Independent of `F-MD-B01-A003-001`, which concerns frozen strategy wording and cannot be remediated by implementation. This one can.
- `MD-S020-R0071` (no new surface named with `eligible`) is `SATISFIED` at `MD-B01-A014` and is not contradicted here: the export column is pre-existing, not a new surface.
- Raised by `E-MD-B01-A014-001`.

## 2026-10-09T12:55:37+07:00 Remediated under `MD-B19-A001` (`E-MD-B19-A001-011`)

`EodEvidenceRepository::exportEligibilityRowsForEvidencePublication()` and `exportEligibilityRows()` now select `listing_id`, `publication_id` and `eligibility_reasons_json` and project one row as `trade_date, listing_id, ticker_id, publication_id, data_usable, reason_codes, eligible, reason_code`; `MarketDataEvidenceExportService` writes that header. `data_usable` is the persisted `eligible` as 1/0; `reason_codes` is the persisted set verbatim in persisted order as a JSON array (an unrecorded set stays an empty cell and is never rebuilt from `reason_code`); `listing_id` keeps NULL as NULL. Proved by execution, not by file existence: a seeded history of superseded, current, unsealed and neighbouring publications with literal expected rows, and the file of a real run against an independent read of the tables with a superseded neighbour added (byte-identical). 28 mutations red. The only reader of the repository rows (replay) reads `eligible` and is unaffected. The edited files were already in the set differing from candidate-v5 (`MD-DEP-0025` updated, still ACTIVE). `MD-S075-R0133..R0138` and `R0143..R0145` are admitted.
