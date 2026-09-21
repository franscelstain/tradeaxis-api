<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * C1 contract Sec4.2, closing the Binding-scoped gap E035 identified: sealed V2 bound_input_*
 * columns on md_publication_lineage_bindings must be rejected through both the repository path
 * (PublicationInputBindingService, E034) and tested database protections. This proves the database
 * protection directly via raw SQL, inside UsesMarketDataMariaDb's rolled-back transaction.
 */
class PublicationLineageBindingSealImmutabilityMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    private function seedPublicationAndLineage(string $sealState = 'UNSEALED', ?string $boundInputContextHash = null): array
    {
        $run = (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c1-lineage-seal-probe');
        $now = '2026-03-24 12:00:00';
        $publicationId = $this->marketDataMariaDb()->table('eod_publications')->insertGetId([
            'trade_date' => '2026-03-24', 'run_id' => $run->run_id, 'publication_version' => 1,
            'seal_state' => $sealState, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->insert([
            'publication_id' => $publicationId, 'config_snapshot_id' => (int) $run->config_snapshot_id,
            'observation_manifest_hash' => str_repeat('0', 64), 'identity_revision_set_hash' => str_repeat('1', 64),
            'calendar_revision_set_hash' => str_repeat('2', 64), 'status_revision_set_hash' => str_repeat('3', 64),
            'event_revision_set_hash' => str_repeat('4', 64), 'formula_version' => 'test-v1',
            'build_id' => 'test-build', 'read_model_version' => 'market_data_read_product_v1', 'created_at' => $now,
            'bound_input_schema_version' => $boundInputContextHash ? 'md_publication_inputs_v2' : null,
            'bound_input_context_json' => $boundInputContextHash ? '{"schema_version":"md_publication_inputs_v2"}' : null,
            'bound_input_context_hash' => $boundInputContextHash,
            'bound_input_capture_manifest_json' => $boundInputContextHash ? '{"required_slots":[]}' : null,
        ]);

        return [$run, $publicationId];
    }

    public function test_schema_has_the_exact_sealed_immutability_triggers_on_the_real_lineage_table(): void
    {
        $database = $this->marketDataMariaDb()->getDatabaseName();
        $this->assertSame('tradeaxis_testing', $database);
        $triggers = $this->marketDataMariaDb()->table('information_schema.TRIGGERS')
            ->where('TRIGGER_SCHEMA', $database)->where('EVENT_OBJECT_TABLE', 'md_publication_lineage_bindings')
            ->get()->keyBy('TRIGGER_NAME');
        $this->assertArrayHasKey('trg_md_lineage_bound_input_no_update_sealed', $triggers);
        $this->assertArrayHasKey('trg_md_lineage_no_delete_sealed_bound', $triggers);
        $this->assertSame('BEFORE', $triggers['trg_md_lineage_bound_input_no_update_sealed']->ACTION_TIMING);
        $this->assertSame('UPDATE', $triggers['trg_md_lineage_bound_input_no_update_sealed']->EVENT_MANIPULATION);
        $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', $triggers['trg_md_lineage_bound_input_no_update_sealed']->ACTION_STATEMENT);
        $this->assertSame('BEFORE', $triggers['trg_md_lineage_no_delete_sealed_bound']->ACTION_TIMING);
        $this->assertSame('DELETE', $triggers['trg_md_lineage_no_delete_sealed_bound']->EVENT_MANIPULATION);
        $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', $triggers['trg_md_lineage_no_delete_sealed_bound']->ACTION_STATEMENT);
    }

    public function test_lawful_pre_seal_update_of_bound_input_columns_still_works(): void
    {
        [, $publicationId] = $this->seedPublicationAndLineage('UNSEALED', null);
        $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)
            ->update(['bound_input_context_hash' => str_repeat('a', 64), 'bound_input_schema_version' => 'md_publication_inputs_v2']);
        $this->assertSame(str_repeat('a', 64), $this->marketDataMariaDb()->table('md_publication_lineage_bindings')
            ->where('publication_id', $publicationId)->value('bound_input_context_hash'));
    }

    public function test_direct_sql_update_of_bound_input_columns_is_rejected_once_sealed(): void
    {
        [, $publicationId] = $this->seedPublicationAndLineage('SEALED', str_repeat('a', 64));
        try {
            $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)
                ->update(['bound_input_context_hash' => str_repeat('b', 64)]);
            $this->fail('Direct SQL update of a sealed bound_input_context_hash was accepted.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', $e->getMessage());
        }
        $this->assertSame(str_repeat('a', 64), $this->marketDataMariaDb()->table('md_publication_lineage_bindings')
            ->where('publication_id', $publicationId)->value('bound_input_context_hash'));
    }

    public function test_direct_sql_update_of_an_unrelated_column_is_not_blocked_once_sealed(): void
    {
        [, $publicationId] = $this->seedPublicationAndLineage('SEALED', str_repeat('a', 64));
        // Scope is deliberately narrow to the four V2 columns; the pre-existing V1 columns are a
        // separate, not-yet-authorized concern and must remain unaffected by this migration.
        $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)
            ->update(['read_model_version' => 'market_data_read_product_v2']);
        $this->assertSame('market_data_read_product_v2', $this->marketDataMariaDb()->table('md_publication_lineage_bindings')
            ->where('publication_id', $publicationId)->value('read_model_version'));
    }

    public function test_direct_sql_delete_of_a_sealed_row_with_bound_v2_content_is_rejected(): void
    {
        [, $publicationId] = $this->seedPublicationAndLineage('SEALED', str_repeat('a', 64));
        try {
            $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->delete();
            $this->fail('Direct SQL delete of a sealed row with bound V2 content was accepted.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', $e->getMessage());
        }
        $this->assertSame(1, $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->count());
    }

    public function test_direct_sql_delete_of_a_sealed_row_with_no_bound_v2_content_is_not_blocked_by_this_trigger(): void
    {
        [, $publicationId] = $this->seedPublicationAndLineage('SEALED', null);
        $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->delete();
        $this->assertSame(0, $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->count());
    }

    public function test_direct_sql_delete_of_an_unsealed_row_is_not_blocked(): void
    {
        [, $publicationId] = $this->seedPublicationAndLineage('UNSEALED', str_repeat('a', 64));
        $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->delete();
        $this->assertSame(0, $this->marketDataMariaDb()->table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->count());
    }
}
