# Book Index — Market Data Platform (EOD)

## Core scope, terminology, and boundary invariants
- Terminology_and_Scope.md
- Market_Data_Strategy_Implementation_Blueprint_LOCKED.md
- Domain_Boundary_Invariants_LOCKED.md
- Market_Data_Implementation_Conformance_Matrix_LOCKED.md
- Market_Data_Implementation_Command_Protocol_LOCKED.md

`Market_Data_Strategy_Implementation_Blueprint_LOCKED.md` adalah owner urutan pembangunan `W00`–`W22` dan handoff audit. `Market_Data_Implementation_Conformance_Matrix_LOCKED.md` memastikan seluruh dokumen/deliverable/proof memiliki assignment. `Market_Data_Implementation_Command_Protocol_LOCKED.md` mengatur start/audit/remediation/re-audit/advance dan result contract. Ketiganya tidak menggantikan owner behavior yang dirujuk pada setiap stage.

## Consumer readability, publication, and determinism
- Downstream_Consumer_Read_Model_Contract_LOCKED.md
- Consumer_Readability_Decision_Table_LOCKED.md
- Downstream_Data_Readiness_Guarantee_LOCKED.md
- Determinism_Invariants_LOCKED.md

## Publication identity, pointer integrity, switch safety, and correction integrity
- Publication_Manifest_Contract_LOCKED.md
- Publication_Current_Pointer_Integrity_Contract_LOCKED.md
- Historical_Correction_and_Reseal_Contract_LOCKED.md
- Canonical_Row_History_and_Versioning_Policy_LOCKED.md

## Core dependencies and universe foundations
- Market_Calendar_Requirements_Contract.md
- Trading_Status_Source_Contract_LOCKED.md
- Tickers_and_Identity_Dependency_Contract_LOCKED.md
- Coverage_Universe_Definition_LOCKED.md
- Coverage_Gate_Enforcement_Contract_LOCKED.md
- Symbol_Lifecycle_and_Mapping_Contract.md

## Source acquisition and canonical data
- Yahoo_Finance_Bootstrap_Source_Strategy.md
- Source_Data_Acquisition_Contract_LOCKED.md
- EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md
- Source_Mapping_Contract_LOCKED.md
- Import_Promote_Separation_Contract.md
- Canonicalization_Contract_EOD_Bars.md
- EOD_Bars_Contract.md
- Invalid_Bar_Storage_Policy_LOCKED.md
- EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md

## Indicators and adjustments
- EOD_Indicators_Contract.md
- Indicator_Nullability_And_OHLCV_Gap_Contract.md
- Indicator_Recompute_Source_Scope_Contract.md
- ../indicators/EOD_Indicators_Formula_Spec.md
- Corporate_Action_and_Adjustment_Policy.md
- Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md
- Corporate_Action_Impact_Flags_Contract.md
- ../registry/Price_Adjustment_Contract_LOCKED.md
- ../registry/Price_Scale_Break_Detection_LOCKED.md
- ../registry/Volume_and_Turnover_Normalization_LOCKED.md
- ../registry/Indicator_Registry_Baseline_LOCKED.md
- ../registry/Platform_Config_Registry_LOCKED.md

## Run readiness, effective date, and consumer safety
- Run_Status_and_Quality_Gates_LOCKED.md
- Effective_Trade_Date_Contract_LOCKED.md
- EOD_Cutoff_and_Finalization_Contract_LOCKED.md
- Finalize_Lock_And_Pointer_Behavior_LOCKED.md
- Force_Replace_Operator_Control_Policy_LOCKED.md
- Publication_Lock_And_Replacement_Policy_LOCKED.md
- EOD_Eligibility_Snapshot_Contract_LOCKED.md
- Eligibility_Partial_Data_Behavior_LOCKED.md
- Dataset_Seal_and_Freeze_Contract_LOCKED.md
- Audit_Hash_and_Reproducibility_Contract_LOCKED.md
- Hash_Number_Formatting_LOCKED.md


## Supporting datasets
- Market_Daily_Metrics_Contract.md

## Replay and semantic proof
- Replay_Verification_Contract_LOCKED.md
- ../backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md
- ../tests/Contract_Test_Matrix_LOCKED.md
- ../tests/Golden_Fixture_Catalog_LOCKED.md
- ../tests/Negative_Test_Catalog_LOCKED.md

## Normative companion folders
The following folders are normative companions to this book and must be treated as part of the same source of truth:

These folders extend implementation detail, persistence, proof, and operations. They do not replace book-level ownership for domain scope, boundary, publication semantics, or other core behavioral contracts unless a book contract explicitly delegates that narrower subject.
- `../db/`
- `../ops/`
- `../tests/`
- `../registry/`
- `../backtest/`
- `../indicators/`
- `../session_snapshot/`

## Companion review folders
The following folders are companion material for review and illustration. They do not define new behavior beyond the normative contracts above:
- `../examples/`
- `../evidence/`

## Freeze note
This index maps the primary book-level contracts and the companion normative folders that complete the same source of truth. It must not be read as a list of `LOCKED` files only, because some owned contracts in this book are intentionally non-LOCKED and several companion folders are authoritative support layers. Companion review folders contain illustration and archived review material only.

## Evidence note
Archived actual execution evidence is part of the normative proof ecosystem, but is expected to live outside the book folder in an evidence archive area or equivalent official repository.
- [Coverage Edge Cases Contract LOCKED](Coverage_Edge_Cases_Contract_LOCKED.md)

- [Publishability State Integrity Contract LOCKED](Publishability_State_Integrity_Contract_LOCKED.md)

- [Publishability Coverage Fallback Cross-Consistency Contract LOCKED](Publishability_Coverage_Fallback_Cross_Consistency_Contract_LOCKED.md)

- [Correction Lifecycle Safety Contract](Correction_Lifecycle_Safety_Contract.md) — LOCKED contract for correction baseline safety, unchanged artifacts, failed pointer preservation, reseal, linkage, pointer switch, evidence, replay, and command output.

- [Current Indicator Recompute Command Contract](Current_Indicator_Recompute_Command_Contract.md)
