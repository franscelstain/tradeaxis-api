<?php
require_once __DIR__.'/MarketDataCanonicalRawImportTraceabilitySpec.php';
final class MarketDataCanonicalRawImportProofSpec
{
    public static function familyFor(array $row): string
    {
        if ($row['rule_id'] === 'MD-S023-R0044') return 'zero_volume_movement';
        return [
            'MD-S001'=>'scope_boundary','MD-S008'=>'canonical_raw','MD-S023'=>'bar_validity','MD-S029'=>'ingest_boundary',
            'MD-S034'=>'config_identity','MD-S036'=>'import_only','MD-S039'=>'invalid_separation','MD-S053'=>'provenance'
        ][$row['strategy_document_id']] ?? 'canonical_raw';
    }
    public static function families(): array
    {
        return [
          'scope_boundary'=>['tests/Unit/MarketData/MarketDataOrdersOneToFourArchitectureTest.php','test_application_services_depend_on_provider_neutral_source_ports'],
          'canonical_raw'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','test_a_canonical_row_carries_complete_traceability'],
          'bar_validity'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','test_a_zero_price_placeholder_cannot_become_canonical'],
          'zero_volume_movement'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','test_zero_volume_with_price_movement_is_rejected_with_dedicated_reason'],
          'ingest_boundary'=>['tests/Unit/MarketData/EodBarsIngestServiceTest.php','test_conflicting_duplicate_rows_are_quarantined_and_never_resolved_by_latest_capture_time'],
          'config_identity'=>['tests/Unit/MarketData/ConfigIdentityBindingTest.php','test_a_created_run_receives_non_null_config_identity'],
          'import_only'=>['tests/Unit/MarketData/CanonicalRawImportBoundaryTest.php','test_import_creates_a_candidate_without_sealing_or_switching_the_pointer'],
          'invalid_separation'=>['tests/Unit/MarketData/EodBarsIngestServiceTest.php','test_zero_canonical_rows_preserve_acquisition_telemetry_and_expose_invalid_reason_summary'],
          'provenance'=>['tests/Unit/MarketData/SourceObservationImmutabilityTest.php','test_canonical_lineage_only_accepts_a_real_accepted_observation'],
        ];
    }
    public static function map(string $root): array
    {
        $out=[]; foreach(MarketDataCanonicalRawImportTraceabilitySpec::denominator($root) as $r){$out[$r['rule_id']]=self::familyFor($r);} ksort($out); return $out;
    }
}
