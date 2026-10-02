<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\PublicationDiffService;
use App\Application\MarketData\Services\SemanticNestedIdentityService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * F-MD-B10-A002-005 G5. The correction comparison decides UNCHANGED or CHANGED from the three
 * artifact hashes. No V2 artifact row binds the calendar revision set, yet the seal contract
 * protects it (Dataset_Seal_and_Freeze_Contract_LOCKED.md:47) and the correction contract requires
 * the comparison to cover every protected field (Historical_Correction_and_Reseal_Contract_LOCKED.md:46).
 * E-MD-B10-A002-016 demonstrated a calendar-only difference compared UNCHANGED.
 *
 * The publications below are real eod_publications rows and real lineage bindings, read by the
 * service exactly as the pipeline reads them.
 */
class PublicationCorrectionCalendarBindingTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const DATE = '2026-03-20';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function publication(int $id, int $version, string $profile, ?string $calendarSeed, array $hashes = []): object
    {
        DB::table('eod_publications')->insert([
            'publication_id' => $id,
            'trade_date' => self::DATE,
            'run_id' => 100 + $id,
            'publication_version' => $version,
            'seal_state' => 'SEALED',
            'artifact_hash_profile' => $profile,
            'bars_batch_hash' => $hashes['bars'] ?? hash('sha256', 'bars'),
            'indicators_batch_hash' => $hashes['indicators'] ?? hash('sha256', 'indicators'),
            'eligibility_batch_hash' => $hashes['eligibility'] ?? hash('sha256', 'eligibility'),
            'sealed_at' => self::DATE.' 17:20:00',
            'created_at' => self::DATE.' 17:20:00',
            'updated_at' => self::DATE.' 17:20:00',
        ]);
        if ($calendarSeed !== null) {
            $nested = [];
            foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $member => $column) {
                $nested[$column] = hash('sha256', 'nested-'.$member);
            }
            $nested['semantic_calendar_revision_set_hash'] = hash('sha256', $calendarSeed);
            DB::table('md_publication_lineage_bindings')->insert([
                'publication_id' => $id,
                'semantic_nested_identity_version' => SemanticNestedIdentityService::VERSION,
                'created_at' => self::DATE.' 17:10:00',
            ] + $nested + $this->legacyLineage());
        }

        return DB::table('eod_publications')->where('publication_id', $id)->first();
    }

    private function legacyLineage(): array
    {
        return [
            'config_snapshot_id' => 1,
            'factor_set_id' => 1,
            'observation_manifest_hash' => hash('sha256', 'o'),
            'identity_revision_set_hash' => hash('sha256', 'i'),
            'calendar_revision_set_hash' => hash('sha256', 'c'),
            'status_revision_set_hash' => hash('sha256', 's'),
            'event_revision_set_hash' => hash('sha256', 'e'),
            'source_scale_assessment_set_hash' => hash('sha256', 'ss'),
            'market_structure_revision_set_hash' => hash('sha256', 'ms'),
            'factor_decision_set_hash' => hash('sha256', 'fd'),
            'formula_version' => 'eod_indicators_v1',
            'build_id' => 'test-build',
            'read_model_version' => 'market_data_read_product_v1',
            'bound_input_schema_version' => 'md_publication_inputs_v2',
            'bound_input_context_json' => '{}',
            'bound_input_context_hash' => hash('sha256', '{}'),
            'bound_input_capture_manifest_json' => '{}',
        ];
    }

    public function test_a_calendar_only_difference_is_a_change_not_an_unchanged_republication(): void
    {
        $prior = $this->publication(10, 1, ArtifactSemanticHashService::PROFILE_V2, 'calendar-a');
        $candidate = $this->publication(11, 2, ArtifactSemanticHashService::PROFILE_V2, 'calendar-b');
        $this->assertSame($prior->bars_batch_hash, $candidate->bars_batch_hash, 'precondition: every artifact hash is equal');
        $this->assertSame($prior->indicators_batch_hash, $candidate->indicators_batch_hash);
        $this->assertSame($prior->eligibility_batch_hash, $candidate->eligibility_batch_hash);

        $service = new PublicationDiffService();
        $comparison = $service->compare($prior, $candidate);

        $this->assertSame('CHANGED', $comparison['decision']);
        $this->assertSame(['calendar'], $comparison['changed_scope']);
        $this->assertSame(['semantic_calendar_revision_set_hash'], $comparison['changed_fields']);
        $this->assertFalse($service->isUnchanged($prior, $candidate));
        $this->assertSame(
            hash('sha256', 'calendar-a'),
            $comparison['hash_context']['bindings']['semantic_calendar_revision_set_hash']['prior']
        );
    }

    public function test_the_same_calendar_under_different_allocation_is_unchanged(): void
    {
        $prior = $this->publication(10, 1, ArtifactSemanticHashService::PROFILE_V2, 'calendar-a');
        $candidate = $this->publication(8800, 7, ArtifactSemanticHashService::PROFILE_V2, 'calendar-a');

        $comparison = (new PublicationDiffService())->compare($prior, $candidate);

        $this->assertSame('UNCHANGED', $comparison['decision']);
        $this->assertSame([], $comparison['changed_fields']);
    }

    public function test_an_artifact_difference_and_a_calendar_difference_are_both_reported(): void
    {
        $prior = $this->publication(10, 1, ArtifactSemanticHashService::PROFILE_V2, 'calendar-a');
        $candidate = $this->publication(11, 2, ArtifactSemanticHashService::PROFILE_V2, 'calendar-b', ['indicators' => hash('sha256', 'moved')]);

        $comparison = (new PublicationDiffService())->compare($prior, $candidate);

        $this->assertSame('CHANGED', $comparison['decision']);
        $this->assertSame(['indicators_batch_hash', 'semantic_calendar_revision_set_hash'], $comparison['changed_fields']);
        $this->assertSame(['indicators', 'calendar'], $comparison['changed_scope']);
    }

    public function test_an_unresolvable_calendar_binding_is_invalid_never_unchanged(): void
    {
        $prior = $this->publication(10, 1, ArtifactSemanticHashService::PROFILE_V2, 'calendar-a');
        $candidate = $this->publication(11, 2, ArtifactSemanticHashService::PROFILE_V2, null);

        $service = new PublicationDiffService();
        $comparison = $service->compare($prior, $candidate);

        $this->assertSame('INVALID', $comparison['decision']);
        $this->assertSame('CORRECTION_ARTIFACT_HASH_INCOMPLETE', $comparison['reason_code']);
        $this->assertSame(['candidate.semantic_calendar_revision_set_hash'], $comparison['missing_fields']);
        $this->assertFalse($service->isUnchanged($prior, $candidate));

        // A blank binding is as unresolved as an absent row.
        DB::table('md_publication_lineage_bindings')->where('publication_id', 10)->update(['semantic_calendar_revision_set_hash' => null]);
        $both = $service->compare($this->publicationRow(10), $candidate);
        $this->assertSame('INVALID', $both['decision']);
        $this->assertSame(
            ['prior.semantic_calendar_revision_set_hash', 'candidate.semantic_calendar_revision_set_hash'],
            $both['missing_fields']
        );
    }

    private function publicationRow(int $id): object
    {
        return DB::table('eod_publications')->where('publication_id', $id)->first();
    }

    public function test_legacy_v1_publications_keep_their_historical_interpretation(): void
    {
        // V1 has no calendar binding in its artifacts and its comparison never had one. Even if a
        // V1 publication happens to carry V2 lineage, the V1 decision is unchanged.
        $prior = $this->publication(10, 1, ArtifactSemanticHashService::LEGACY_PROFILE_V1, 'calendar-a');
        $candidate = $this->publication(11, 2, ArtifactSemanticHashService::LEGACY_PROFILE_V1, 'calendar-b');

        $comparison = (new PublicationDiffService())->compare($prior, $candidate);

        $this->assertSame('UNCHANGED', $comparison['decision']);
        $this->assertArrayNotHasKey('bindings', $comparison['hash_context']);

        $noLineage = $this->publication(12, 3, ArtifactSemanticHashService::LEGACY_PROFILE_V1, null);
        $this->assertSame('UNCHANGED', (new PublicationDiffService())->compare($prior, $noLineage)['decision']);
    }

    public function test_a_malformed_v2_publication_never_falls_back_to_the_v1_comparison(): void
    {
        $prior = $this->publication(10, 1, ArtifactSemanticHashService::LEGACY_PROFILE_V1, 'calendar-a');
        $candidate = $this->publication(11, 2, ArtifactSemanticHashService::PROFILE_V2, 'calendar-a');

        $comparison = (new PublicationDiffService())->compare($prior, $candidate);

        $this->assertSame('INVALID', $comparison['decision'], 'mixed profiles stay INVALID');
        $this->assertSame('CORRECTION_ARTIFACT_HASH_INCOMPLETE', $comparison['reason_code']);

        $unknown = $this->publication(13, 4, 'market-data-semantic-hash/v9', 'calendar-a');
        $this->assertSame('INVALID', (new PublicationDiffService())->compare($candidate, $unknown)['decision']);
    }
}
