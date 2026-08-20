# Market Data — Strategy Authority

> **Navigation only.** Current strategy semantics are the registered source documents below.

This architecture refactor does **not** rewrite strategy content. IDs, role, current authority state, and original/current path mapping are held in `../governance/MARKET_DATA_STRATEGY_AUTHORITY_REGISTRY.csv`.

Legacy path strings inside frozen strategy files may refer to the pre-refactor layout. Use the authority registry for the canonical current physical path; do not edit strategy wording merely to cosmetically update those strings.

## Strategy groups

### Platform baseline

- `MD-S001` — [`MARKET_DATA_PLATFORM_EOD_BASELINE.md`](MARKET_DATA_PLATFORM_EOD_BASELINE.md)

### Book

- `MD-S005` — [`Audit_Hash_and_Reproducibility_Contract_LOCKED.md`](book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md)
- `MD-S006` — [`CONSUMER_READ_CONTRACT_LOCKED.md`](book/CONSUMER_READ_CONTRACT_LOCKED.md)
- `MD-S007` — [`Canonical_Row_History_and_Versioning_Policy_LOCKED.md`](book/Canonical_Row_History_and_Versioning_Policy_LOCKED.md)
- `MD-S008` — [`Canonicalization_Contract_EOD_Bars.md`](book/Canonicalization_Contract_EOD_Bars.md)
- `MD-S009` — [`Consumer_Readability_Decision_Table_LOCKED.md`](book/Consumer_Readability_Decision_Table_LOCKED.md)
- `MD-S010` — [`Corporate_Action_Impact_Flags_Contract.md`](book/Corporate_Action_Impact_Flags_Contract.md)
- `MD-S011` — [`Corporate_Action_and_Adjustment_Policy.md`](book/Corporate_Action_and_Adjustment_Policy.md)
- `MD-S012` — [`Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`](book/Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md)
- `MD-S013` — [`Correction_Lifecycle_Safety_Contract.md`](book/Correction_Lifecycle_Safety_Contract.md)
- `MD-S014` — [`Coverage_Edge_Cases_Contract_LOCKED.md`](book/Coverage_Edge_Cases_Contract_LOCKED.md)
- `MD-S015` — [`Coverage_Gate_Enforcement_Contract_LOCKED.md`](book/Coverage_Gate_Enforcement_Contract_LOCKED.md)
- `MD-S016` — [`Coverage_Universe_Definition_LOCKED.md`](book/Coverage_Universe_Definition_LOCKED.md)
- `MD-S017` — [`Current_Indicator_Recompute_Command_Contract.md`](book/Current_Indicator_Recompute_Command_Contract.md)
- `MD-S018` — [`Dataset_Seal_and_Freeze_Contract_LOCKED.md`](book/Dataset_Seal_and_Freeze_Contract_LOCKED.md)
- `MD-S019` — [`Determinism_Invariants_LOCKED.md`](book/Determinism_Invariants_LOCKED.md)
- `MD-S020` — [`Domain_Boundary_Invariants_LOCKED.md`](book/Domain_Boundary_Invariants_LOCKED.md)
- `MD-S021` — [`Downstream_Consumer_Read_Model_Contract_LOCKED.md`](book/Downstream_Consumer_Read_Model_Contract_LOCKED.md)
- `MD-S022` — [`Downstream_Data_Readiness_Guarantee_LOCKED.md`](book/Downstream_Data_Readiness_Guarantee_LOCKED.md)
- `MD-S023` — [`EOD_Bars_Contract.md`](book/EOD_Bars_Contract.md)
- `MD-S024` — [`EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`](book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md)
- `MD-S025` — [`EOD_Cutoff_and_Finalization_Contract_LOCKED.md`](book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md)
- `MD-S026` — [`EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md`](book/EOD_Data_Retention_and_History_Rewrite_Policy_LOCKED.md)
- `MD-S027` — [`EOD_Eligibility_Snapshot_Contract_LOCKED.md`](book/EOD_Eligibility_Snapshot_Contract_LOCKED.md)
- `MD-S028` — [`EOD_Indicators_Contract.md`](book/EOD_Indicators_Contract.md)
- `MD-S029` — [`EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`](book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md)
- `MD-S030` — [`Effective_Trade_Date_Contract_LOCKED.md`](book/Effective_Trade_Date_Contract_LOCKED.md)
- `MD-S031` — [`Eligibility_Partial_Data_Behavior_LOCKED.md`](book/Eligibility_Partial_Data_Behavior_LOCKED.md)
- `MD-S032` — [`Finalize_Lock_And_Pointer_Behavior_LOCKED.md`](book/Finalize_Lock_And_Pointer_Behavior_LOCKED.md)
- `MD-S033` — [`Force_Replace_Operator_Control_Policy_LOCKED.md`](book/Force_Replace_Operator_Control_Policy_LOCKED.md)
- `MD-S034` — [`Hash_Number_Formatting_LOCKED.md`](book/Hash_Number_Formatting_LOCKED.md)
- `MD-S035` — [`Historical_Correction_and_Reseal_Contract_LOCKED.md`](book/Historical_Correction_and_Reseal_Contract_LOCKED.md)
- `MD-S036` — [`Import_Promote_Separation_Contract.md`](book/Import_Promote_Separation_Contract.md)
- `MD-S037` — [`Indicator_Nullability_And_OHLCV_Gap_Contract.md`](book/Indicator_Nullability_And_OHLCV_Gap_Contract.md)
- `MD-S038` — [`Indicator_Recompute_Source_Scope_Contract.md`](book/Indicator_Recompute_Source_Scope_Contract.md)
- `MD-S039` — [`Invalid_Bar_Storage_Policy_LOCKED.md`](book/Invalid_Bar_Storage_Policy_LOCKED.md)
- `MD-S040` — [`Manual_File_Publishability_Policy_LOCKED.md`](book/Manual_File_Publishability_Policy_LOCKED.md)
- `MD-S041` — [`Market_Calendar_Requirements_Contract.md`](book/Market_Calendar_Requirements_Contract.md)
- `MD-S042` — [`Market_Daily_Metrics_Contract.md`](book/Market_Daily_Metrics_Contract.md)
- `MD-S043` — [`Publication_Current_Pointer_Integrity_Contract_LOCKED.md`](book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md)
- `MD-S044` — [`Publication_Lock_And_Replacement_Policy_LOCKED.md`](book/Publication_Lock_And_Replacement_Policy_LOCKED.md)
- `MD-S045` — [`Publication_Manifest_Contract_LOCKED.md`](book/Publication_Manifest_Contract_LOCKED.md)
- `MD-S046` — [`Publication_Traceability_Immutability_Lineage_LOCKED.md`](book/Publication_Traceability_Immutability_Lineage_LOCKED.md)
- `MD-S047` — [`Publishability_Coverage_Fallback_Cross_Consistency_Contract_LOCKED.md`](book/Publishability_Coverage_Fallback_Cross_Consistency_Contract_LOCKED.md)
- `MD-S048` — [`Publishability_State_Integrity_Contract_LOCKED.md`](book/Publishability_State_Integrity_Contract_LOCKED.md)
- `MD-S049` — [`Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md`](book/Read_Side_Enforcement_Anti_Bypass_Contract_LOCKED.md)
- `MD-S050` — [`Replay_Verification_Contract_LOCKED.md`](book/Replay_Verification_Contract_LOCKED.md)
- `MD-S051` — [`Run_Status_and_Quality_Gates_LOCKED.md`](book/Run_Status_and_Quality_Gates_LOCKED.md)
- `MD-S052` — [`Sector_Classification_Contract_LOCKED.md`](book/Sector_Classification_Contract_LOCKED.md)
- `MD-S053` — [`Source_Data_Acquisition_Contract_LOCKED.md`](book/Source_Data_Acquisition_Contract_LOCKED.md)
- `MD-S054` — [`Source_Mapping_Contract_LOCKED.md`](book/Source_Mapping_Contract_LOCKED.md)
- `MD-S055` — [`Symbol_Lifecycle_and_Mapping_Contract.md`](book/Symbol_Lifecycle_and_Mapping_Contract.md)
- `MD-S056` — [`Terminology_and_Scope.md`](book/Terminology_and_Scope.md)
- `MD-S057` — [`Tickers_and_Identity_Dependency_Contract_LOCKED.md`](book/Tickers_and_Identity_Dependency_Contract_LOCKED.md)
- `MD-S058` — [`Trading_Status_Source_Contract_LOCKED.md`](book/Trading_Status_Source_Contract_LOCKED.md)
- `MD-S059` — [`Yahoo_Finance_Bootstrap_Source_Strategy.md`](book/Yahoo_Finance_Bootstrap_Source_Strategy.md)

### Registry

- `MD-S079` — [`Corporate_Action_Type_Registry_LOCKED.md`](registry/Corporate_Action_Type_Registry_LOCKED.md)
- `MD-S080` — [`Exchange_Market_Structure_Facts_LOCKED.md`](registry/Exchange_Market_Structure_Facts_LOCKED.md)
- `MD-S081` — [`Indicator_Registry_Baseline_LOCKED.md`](registry/Indicator_Registry_Baseline_LOCKED.md)
- `MD-S082` — [`Platform_Config_Registry_LOCKED.md`](registry/Platform_Config_Registry_LOCKED.md)
- `MD-S083` — [`Price_Adjustment_Contract_LOCKED.md`](registry/Price_Adjustment_Contract_LOCKED.md)
- `MD-S084` — [`Price_Scale_Break_Detection_LOCKED.md`](registry/Price_Scale_Break_Detection_LOCKED.md)
- `MD-S085` — [`Reason_Codes_Registry.md`](registry/Reason_Codes_Registry.md)
- `MD-S086` — [`Volume_and_Turnover_Normalization_LOCKED.md`](registry/Volume_and_Turnover_Normalization_LOCKED.md)

### Indicators

- `MD-S060` — [`EOD_Indicators_Formula_Spec.md`](indicators/EOD_Indicators_Formula_Spec.md)
- `MD-S061` — [`Indicator_Computation_Specification.md`](indicators/Indicator_Computation_Specification.md)

### Session Snapshot

- `MD-S087` — [`Session_Snapshot_Contract_LOCKED.md`](session_snapshot/Session_Snapshot_Contract_LOCKED.md)
- `MD-S088` — [`Session_Snapshot_Date_Alignment_with_Effective_Date_LOCKED.md`](session_snapshot/Session_Snapshot_Date_Alignment_with_Effective_Date_LOCKED.md)
- `MD-S089` — [`Session_Snapshot_Retention_Defaults_LOCKED.md`](session_snapshot/Session_Snapshot_Retention_Defaults_LOCKED.md)
- `MD-S090` — [`Session_Snapshot_Scope_Selection_and_Dependencies_LOCKED.md`](session_snapshot/Session_Snapshot_Scope_Selection_and_Dependencies_LOCKED.md)
- `MD-S091` — [`Snapshot_Slot_Tolerances_and_Session_Rules_LOCKED.md`](session_snapshot/Snapshot_Slot_Tolerances_and_Session_Rules_LOCKED.md)

### Backtest

- `MD-S002` — [`Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md`](backtest/Backtest_Metrics_and_Acceptance_Criteria_LOCKED.md)
- `MD-S003` — [`Historical_Replay_and_Data_Quality_Backtest.md`](backtest/Historical_Replay_and_Data_Quality_Backtest.md)
- `MD-S004` — [`Point_In_Time_Backtest_Input_Contract_LOCKED.md`](backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md)

### Ops

- `MD-S062` — [`Archived_Actual_Execution_Evidence_Contract_LOCKED.md`](ops/Archived_Actual_Execution_Evidence_Contract_LOCKED.md)
- `MD-S063` — [`Audit_Evidence_Pack_Contract_LOCKED.md`](ops/Audit_Evidence_Pack_Contract_LOCKED.md)
- `MD-S064` — [`Commands_and_Runbook_LOCKED.md`](ops/Commands_and_Runbook_LOCKED.md)
- `MD-S065` — [`Config_Change_Protocol_LOCKED.md`](ops/Config_Change_Protocol_LOCKED.md)
- `MD-S066` — [`Credentials_and_Secrets_Contract.md`](ops/Credentials_and_Secrets_Contract.md)
- `MD-S067` — [`Error_Taxonomy_and_Run_Status_Decision_Table_LOCKED.md`](ops/Error_Taxonomy_and_Run_Status_Decision_Table_LOCKED.md)
- `MD-S068` — [`Executed_Run_Admission_Criteria_LOCKED.md`](ops/Executed_Run_Admission_Criteria_LOCKED.md)
- `MD-S069` — [`Incident_Classification_and_Response_Matrix_LOCKED.md`](ops/Incident_Classification_and_Response_Matrix_LOCKED.md)
- `MD-S070` — [`Observability_Minimum_Contract_LOCKED.md`](ops/Observability_Minimum_Contract_LOCKED.md)
- `MD-S071` — [`Operator_Decision_Trees_LOCKED.md`](ops/Operator_Decision_Trees_LOCKED.md)
- `MD-S072` — [`Performance_SLO_and_Limits_LOCKED.md`](ops/Performance_SLO_and_Limits_LOCKED.md)
- `MD-S073` — [`Release_Gates_LOCKED.md`](ops/Release_Gates_LOCKED.md)
- `MD-S074` — [`Resumable_Backfill_Contract_LOCKED.md`](ops/Resumable_Backfill_Contract_LOCKED.md)
- `MD-S075` — [`Run_Artifacts_Format_LOCKED.md`](ops/Run_Artifacts_Format_LOCKED.md)
- `MD-S076` — [`Run_Execution_Evidence_Pack_Contract_LOCKED.md`](ops/Run_Execution_Evidence_Pack_Contract_LOCKED.md)
- `MD-S077` — [`Run_Ownership_and_Recovery_LOCKED.md`](ops/Run_Ownership_and_Recovery_LOCKED.md)
- `MD-S078` — [`Scheduling_and_Locking_Contract_LOCKED.md`](ops/Scheduling_and_Locking_Contract_LOCKED.md)
