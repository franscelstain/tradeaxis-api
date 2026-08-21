# System Data Product Map

## Purpose
Dokumen ini memetakan produk/fact family utama yang dihasilkan atau dikelola platform. Ia tidak menggantikan contract detail.

## Product-class rule
Setiap item pada peta ini harus dibaca dalam salah satu kelas berikut:
- `consumer-facing` = boleh dipakai downstream consumer sebagai intake upstream yang sah bila owner contract mengizinkan
- `internal-only` = tidak boleh dipakai downstream consumer sebagai kontrak intake langsung
- `implementation-support only` = artefak teknis/operasional yang membantu implementasi atau audit, tetapi bukan intake contract downstream

Kelas ini tidak menggantikan owner contract.

## Quick intake matrix

| Product / fact family | Owner area | Consumer-facing? | Publication-aware intake? | Direct watchlist intake? |
|---|---|---:|---:|---:|
| Temporal identity/listing/provider-symbol facts | `market_data` | No, except fields projected through read product | No | No |
| Calendar/session/trading-status facts | `market_data` | No, except governed state projected through read product | No | No |
| Temporal IDX-IC sector membership | `market_data` | No, except publication-bound sector state projected through read product | No | No |
| Immutable source observations | `market_data` | No | No | No |
| Canonical `RAW` EOD bars | `market_data` | No, unless owner contract explicitly exposes them | No | No |
| `STRUCTURAL_ADJUSTED` / `TOTAL_RETURN` analytical products | `market_data` | No, except values exposed by versioned read product | No | No |
| Daily actual/proxy market metrics | `market_data` | Only through read product where contracted | Yes when exposed | Yes only through read product |
| Deterministic EOD indicators | `market_data` | Only through read product where contracted | Yes when exposed | Yes only through read product |
| Coverage / quality / data-usability artifacts | `market_data` | Yes through the versioned read product | Yes | Yes |
| Publication and current-pointer semantics | `market_data` | Yes where owner contract defines readability | Yes | Yes, semantics only |
| Correction / replay / reseal artifacts | `market_data` | No | No | No |
| Session snapshot artifacts | `market_data` | No, unless a downstream-facing contract later states otherwise | No | No |

## Main fact and product families

### Temporal reference facts
Classification: `internal-only`, with selected publication-bound projections allowed only through the consumer read contract.

Owner pointers:
- `book/Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `book/Symbol_Lifecycle_and_Mapping_Contract.md`
- `book/Market_Calendar_Requirements_Contract.md`
- `book/Trading_Status_Source_Contract_LOCKED.md`
- `book/Sector_Classification_Contract_LOCKED.md`

These are point-in-time/as-known inputs. Current master state must never substitute for historical resolution.

### Immutable source observations and canonical RAW
Classification: `internal-only`.

Owner pointers:
- `book/Source_Data_Acquisition_Contract_LOCKED.md`
- `book/Source_Mapping_Contract_LOCKED.md`
- `book/EOD_Bars_Contract.md`
- `book/Canonicalization_Contract_EOD_Bars.md`
- `book/Canonical_Row_History_and_Versioning_Policy_LOCKED.md`

### Corporate-action facts and analytical price products
Classification: `internal-only` unless projected through an owner-defined read product field.

Owner pointers:
- `book/Corporate_Action_and_Adjustment_Policy.md`
- `book/Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `registry/Price_Adjustment_Contract_LOCKED.md`

Provider `adj_close` is not an analytical product. `RAW`, `STRUCTURAL_ADJUSTED`, and `TOTAL_RETURN` remain explicitly distinct.

### Daily metrics and deterministic EOD indicators
Classification: `internal-only` as tables/artifacts; selected versioned fields may be exposed only through the consumer read product.

Owner pointers:
- `book/Market_Daily_Metrics_Contract.md`
- `registry/Volume_and_Turnover_Normalization_LOCKED.md`
- `book/EOD_Indicators_Contract.md`
- `indicators/EOD_Indicators_Formula_Spec.md`
- `indicators/Indicator_Computation_Specification.md`
- `registry/Indicator_Registry_Baseline_LOCKED.md`

Sector-relative fields depend on temporal sector membership; missing/unknown membership never falls back to current sector.

### Coverage, eligibility, and readability facts
Classification: `consumer-facing` only through the publication-bound intake contract.

Owner pointers:
- `book/Coverage_Universe_Definition_LOCKED.md`
- `book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `book/Eligibility_Partial_Data_Behavior_LOCKED.md`
- `book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`

Downstream consumers should anchor intake here/read-product gateway first. `eligible` means upstream data usability, not tradability or selection.

### Publication and current-pointer artifacts
Classification: `consumer-facing` only where owner contracts define publication-aware readability; technical switching procedures remain `implementation-support only`.

Owner pointers:
- `book/Publication_Manifest_Contract_LOCKED.md`
- `book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`
- `db/EOD_Publications_Table.sql`
- `db/EOD_Current_Publication_Pointer_Table.sql`
- `db/Publication_Switch_Procedure_LOCKED.sql`
- `db/Publication_Current_Pointer_Switch_Procedure_LOCKED.sql`

### Correction, replay, and reseal artifacts
Classification: `implementation-support only`.

Owner pointers:
- `book/Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `book/Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md`
- `backtest/Historical_Replay_and_Data_Quality_Backtest.md`
- `backtest/Replay_Results_Schema_MariaDB.sql`

### Optional supplemental session snapshots
Classification: `internal-only` unless a downstream-facing contract later states otherwise.

Owner pointers:
- `session_snapshot/Session_Snapshot_Contract_LOCKED.md`
- `session_snapshot/Session_Snapshot_Scope_Selection_and_Dependencies_LOCKED.md`
- `session_snapshot/Session_Snapshot_Date_Alignment_with_Effective_Date_LOCKED.md`

## Downstream intake note
Untuk active scope, `watchlist` tidak boleh mengganti consumer-facing read-product path dengan pembacaan langsung terhadap reference/master, raw bars, indicator internals, correction artifacts, atau session snapshots.

## Readability rule
System map ini hanya menunjukkan produk/fact family utama dan pointer owner-nya. Behavior rinci tetap harus dibaca dari file owner yang dirujuk.

## Current-state interpretation

Bagian ini hanya ringkasan current implementation state. Semantic owner tetap [`authority/strategy/book/Terminology_and_Scope.md`](../../../../authority/strategy/book/Terminology_and_Scope.md); bila ringkasan ini berbeda, definisi owner tersebut yang berlaku.

- `2023-01-02` sampai `2025-10-31` adalah archived proof window, bukan dataset end atau current-freshness proof.
- Historical source-state/internal conformance tidak membuktikan official IDX authority, commercial data SLA, redistribution right, atau achieved `decision-grade` correctness.
