# System Runtime Flow

## Purpose
Dokumen ini menjelaskan alur runtime tingkat atas dan menunjuk ke owner docs yang mengatur tiap tahap. Ia mengikuti dependency order strategi aktif dan tidak menetapkan behavior baru.

## High-level flow
1. resolve immutable output-affecting configuration identity
2. resolve temporal issuer/instrument/listing/provider-symbol mapping, calendar/session/trading-status facts, and temporal IDX-IC sector membership
3. acquire immutable source observations with provenance/capability metadata
4. validate, map, deduplicate, and build candidate canonical `RAW` EOD bars; invalid/missing remain explicit
5. resolve verified corporate-action revisions/factors and construct coherent analytical price products
6. compute actual/proxy daily market metrics and deterministic indicators; sector-relative fields require point-in-time sector inputs
7. evaluate temporal coverage expectation/delivery independently from quality/liquidity/status/event/indicator-validity facts
8. build explainable publication-bound data-usability/readiness facts
9. seal manifest/artifacts/publication and atomically switch current pointer only when all independent gates pass
10. expose one versioned market-data read product bound to publication/config/factor/formula/reference identity
11. handle corrections through new revision/publication lineage; support exact and as-known replay without future leakage
12. archive run/evidence/proof artifacts under admission rules

## Owner pointers by stage

### Configuration and temporal reference facts
- `registry/Platform_Config_Registry_LOCKED.md`
- `book/Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `book/Symbol_Lifecycle_and_Mapping_Contract.md`
- `book/Market_Calendar_Requirements_Contract.md`
- `book/Trading_Status_Source_Contract_LOCKED.md`
- `book/Sector_Classification_Contract_LOCKED.md`

### Acquisition and source capability
- `book/Yahoo_Finance_Bootstrap_Source_Strategy.md`
- `book/Source_Data_Acquisition_Contract_LOCKED.md`
- `book/Source_Mapping_Contract_LOCKED.md`
- `book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`

`manual_file` is an explicit controlled one-date rescue/correction path, not a multi-day continuity source.

### Validation / canonical RAW
- `book/EOD_Bars_Contract.md`
- `book/Canonicalization_Contract_EOD_Bars.md`
- `book/Invalid_Bar_Storage_Policy_LOCKED.md`

### Corporate action and analytical price products
- `book/Corporate_Action_and_Adjustment_Policy.md`
- `book/Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `registry/Price_Adjustment_Contract_LOCKED.md`

### Market metrics and indicator computation
- `book/Market_Daily_Metrics_Contract.md`
- `registry/Volume_and_Turnover_Normalization_LOCKED.md`
- `book/EOD_Indicators_Contract.md`
- `indicators/EOD_Indicators_Formula_Spec.md`
- `indicators/Indicator_Computation_Specification.md`
- `registry/Indicator_Registry_Baseline_LOCKED.md`

### Coverage, data usability, and readability
- `book/Coverage_Universe_Definition_LOCKED.md`
- `book/Coverage_Gate_Enforcement_Contract_LOCKED.md`
- `book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `book/Eligibility_Partial_Data_Behavior_LOCKED.md`
- `book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`

### Publication and pointer switching
- `book/Publication_Manifest_Contract_LOCKED.md`
- `book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`
- `db/Publication_Switch_Procedure_LOCKED.sql`
- `db/Publication_Current_Pointer_Switch_Procedure_LOCKED.sql`

### Correction / exact-and-as-known replay / reseal
- `book/Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `book/Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `book/Replay_Verification_Contract_LOCKED.md`
- `backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md`
- `backtest/Historical_Replay_and_Data_Quality_Backtest.md`
- `ops/Historical_Correction_Runbook_LOCKED.md`

### Operational execution and evidence
- `ops/Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
- `ops/Run_Execution_Evidence_Pack_Contract_LOCKED.md`
- `ops/Archived_Actual_Execution_Evidence_Contract_LOCKED.md`
- `tests/Executed_Proof_Admission_Criteria_LOCKED.md`

## Reminder
`SYSTEM_RUNTIME_FLOW.md` hanya summary. Bila ada konflik, owner contract/blueprint/conformance matrix menang.
