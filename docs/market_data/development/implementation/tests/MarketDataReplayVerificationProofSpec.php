<?php
require_once __DIR__.'/MarketDataReplayVerificationTraceabilitySpec.php';

final class MarketDataReplayVerificationProofSpec
{
    public const STAGE='MD-B18';
    public const ATTEMPT='MD-B18-A001';
    public const BASELINE='MD-B18-A001-BL001';
    public const CI='CI-MD-B18-A001-001';
    public const EXPECTED_DENOMINATOR=121;

    public static function families(): array
    {
        $guard='tests/Unit/MarketData/B18ReplayContractStaticGuardTest.php';
        return [
            'exact_publication'=>['owner'=>'MD-B18:exact-publication','implementation'=>['app/Application/MarketData/Services/ReplayVerificationService.php','app/Console/Commands/MarketData/VerifyReplayCommand.php'],'positive'=>[$guard,'test_exact_requires_explicit_publication_and_never_latest'],'negative'=>[$guard,'test_exact_missing_publication_fails_closed']],
            'as_known'=>['owner'=>'MD-B18:as-known','implementation'=>['app/Application/MarketData/Services/AsKnownReplaySnapshotService.php','app/Application/MarketData/Services/AsKnownReplayExecutionService.php','app/Application/MarketData/Services/EodBarsIngestService.php','app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php'],'positive'=>['tests/Unit/MarketData/AsKnownReplayExecutionServiceTest.php','test_as_known_executes_production_canonicalizer_and_rolls_back_projection_mutation'],'negative'=>[$guard,'test_as_known_cannot_impersonate_a_historical_publication']],
            'bound_inputs'=>['owner'=>'MD-B18:bound-inputs','implementation'=>['app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php','database/migrations/2026_09_04_000001_add_replay_v2_bound_input_context.php'],'positive'=>[$guard,'test_bound_inputs_are_first_class_persisted_fields'],'negative'=>[$guard,'test_missing_bound_input_is_not_silently_admitted']],
            'independent_oracle'=>['owner'=>'MD-B18:independent-oracle','implementation'=>['app/Application/MarketData/Services/ReplayVerificationService.php','app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php'],'positive'=>[$guard,'test_self_generated_fixture_is_diagnostic_only'],'negative'=>[$guard,'test_same_run_oracle_is_rejected_as_positive_proof']],
            'temporal_identity'=>['owner'=>'MD-B18:temporal-identity','implementation'=>['app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php','tests/Unit/MarketData/TemporalIdentityLayerContractTest.php'],'positive'=>[$guard,'test_temporal_retraction_is_cutoff_aware'],'negative'=>[$guard,'test_current_identity_still_excludes_retracted_rows']],
            'source_observation'=>['owner'=>'MD-B18:source-observation','implementation'=>['app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php','tests/Unit/MarketData/SourceObservationAsKnownBoundaryTest.php'],'positive'=>[$guard,'test_source_rows_bind_observation_and_identity_known_time'],'negative'=>[$guard,'test_source_manifest_does_not_filter_by_current_universe']],
            'evidence'=>['owner'=>'MD-B18:evidence','implementation'=>['app/Application/MarketData/Services/MarketDataEvidenceExportService.php','app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php'],'positive'=>[$guard,'test_evidence_exports_mode_cutoff_and_bound_inputs'],'negative'=>[$guard,'test_unclassified_historical_result_cannot_be_complete_b18_evidence']],
            'operations'=>['owner'=>'MD-B18:operations','implementation'=>['app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php','app/Application/MarketData/Services/FullRangeCurrentEvidenceReplayService.php','app/Application/MarketData/Services/ReplayBackfillService.php'],'positive'=>[$guard,'test_lifecycle_replay_requires_independent_fixture_root'],'negative'=>[$guard,'test_lifecycle_positive_replay_never_generates_same_run_fixture']],
            'mode_admission'=>['owner'=>'MD-B18:mode-admission','implementation'=>['app/Application/MarketData/Services/ReplayMode.php','app/Console/Commands/MarketData/VerifyReplayCommand.php'],'positive'=>[$guard,'test_replay_modes_are_first_class_and_exhaustive'],'negative'=>[$guard,'test_unknown_or_missing_mode_fails_closed']],
        ];
    }

    public static function familyFor(array $row): string
    {
        $id=$row['rule_id']; $doc=$row['strategy_document_id']; $text=strtolower(($row['section']??'').' '.($row['rule_text']??''));
        if (strpos($text,'source observation') !== false || strpos($text,'provider outage') !== false) return 'source_observation';
        if (strpos($text,'fixture') !== false && (strpos($text,'independent') !== false || strpos($text,'oracle') !== false)) return 'independent_oracle';
        if (strpos($text,'evidence') !== false || strpos($text,'pass') !== false || strpos($text,'fail') !== false || strpos($text,'blocked') !== false || strpos($text,'mismatch') !== false) return 'evidence';
        if (strpos($text,'bound input') !== false || strpos($text,'config') !== false || strpos($text,'formula') !== false || strpos($text,'hash') !== false || strpos($text,'factor') !== false) return 'bound_inputs';
        if (strpos($text,'as-known') !== false || strpos($text,'knowledge') !== false || strpos($text,'survivorship') !== false || strpos($text,'future') !== false || strpos($text,'recorded') !== false) return 'as_known';
        if (strpos($text,'symbol') !== false || strpos($text,'listing') !== false || strpos($text,'calendar') !== false || strpos($text,'status') !== false) return 'temporal_identity';
        if (strpos($text,'backfill') !== false || strpos($text,'production path') !== false || strpos($text,'runtime') !== false || strpos($text,'environment') !== false || strpos($text,'concurrency') !== false) return 'operations';
        if (strpos($text,'mode') !== false || $id==='MD-S050-R0036') return 'mode_admission';
        if ($doc==='MD-S050' || strpos($text,'publication') !== false || strpos($text,'correction') !== false || strpos($text,'pointer') !== false || strpos($text,'replay') !== false) return 'exact_publication';
        return 'mode_admission';
    }

    public static function entries(string $root): array
    {
        return array_map(function($row){ return ['rule_id'=>$row['rule_id'],'family'=>self::familyFor($row)]; }, MarketDataReplayVerificationTraceabilitySpec::required($root));
    }
}
