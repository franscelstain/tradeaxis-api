<?php

use PHPUnit\Framework\TestCase;

class RunPublicationPointerLinkageStaticGuardTest extends TestCase
{
    public function test_correction_schema_persists_baseline_and_replacement_publication_lineage(): void
    {
        $sql = $this->read('docs/market_data/db/Database_Schema_MariaDB.sql');
        $sqlite = $this->read('tests/Support/UsesMarketDataSqlite.php');
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php');
        $pipeline = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');

        foreach (['baseline_publication_id', 'replacement_publication_id'] as $column) {
            $this->assertStringContainsString($column, $sql);
            $this->assertStringContainsString($column, $sqlite);
            $this->assertStringContainsString($column, $repository);
            $this->assertStringContainsString($column, $pipeline);
        }

        foreach (['idx_corr_baseline_publication', 'idx_corr_replacement_publication', 'idx_corr_baseline_replacement_publication'] as $index) {
            $this->assertStringContainsString($index, $sql.$sqlite);
        }
    }

    public function test_pointer_switch_validates_run_publication_pointer_target_and_post_switch_resolver(): void
    {
        $publicationRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $invariantGuard = $this->read('app/Application/MarketData/Services/MarketDataInvariantGuard.php');

        foreach ([
            'assertValidPointerTarget',
            'assertRunPublicationMirror',
            'assertCurrentPointerResolvedAfterSwitch',
            'RUN_PUBLICATION_MIRROR_MISMATCH',
            'POINTER_PUBLICATION_STATE_INVALID',
            'POINTER_PUBLICATION_SEAL_INVALID',
            'CURRENT_PUBLICATION_REPLACE_BLOCKED',
            'CORRECTION_BASELINE_LINK_INVALID',
        ] as $needle) {
            $this->assertStringContainsString($needle, $publicationRepository.$invariantGuard);
        }

        foreach ([
            'whereColumn(\'pub.trade_date\', \'ptr.trade_date\')',
            'whereColumn(\'ptr.run_id\', \'pub.run_id\')',
            'whereColumn(\'run.publication_id\', \'ptr.publication_id\')',
            'whereColumn(\'run.publication_version\', \'ptr.publication_version\')',
        ] as $needle) {
            $this->assertStringContainsString($needle, $publicationRepository);
        }
    }

    public function test_replay_evidence_and_command_output_include_lineage_context(): void
    {
        $evidence = $this->read('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $replay = $this->read('app/Application/MarketData/Services/ReplayVerificationService.php');
        $command = $this->read('app/Console/Commands/MarketData/AbstractMarketDataCommand.php');

        foreach ([
            'buildLineageContext',
            'correction_baseline_publication_id',
            'correction_candidate_publication_id',
            'baseline_publication_id',
            'replacement_publication_id',
        ] as $needle) {
            $this->assertStringContainsString($needle, $evidence.$replay);
        }

        foreach (['renderLineageSummary', 'lineage_summary=', 'lineage_verification_status'] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }
    }

    public function test_linkage_reason_codes_are_registered_and_seeded(): void
    {
        $registry = $this->read('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->read('docs/market_data/registry/Reason_Codes_Seed.sql');

        foreach ([
            'RUN_PUBLICATION_LINK_CREATED',
            'RUN_PUBLICATION_LINK_VERIFIED',
            'RUN_PUBLICATION_LINK_MISSING',
            'RUN_PUBLICATION_LINK_INVALID',
            'RUN_PUBLICATION_MIRROR_MISMATCH',
            'PUBLICATION_RUN_NOT_FOUND',
            'PUBLICATION_RUN_STATE_INVALID',
            'POINTER_PUBLICATION_LINK_CREATED',
            'POINTER_PUBLICATION_LINK_VERIFIED',
            'POINTER_PUBLICATION_LINK_MISSING',
            'POINTER_PUBLICATION_LINK_INVALID',
            'POINTER_PUBLICATION_NOT_FOUND',
            'POINTER_PUBLICATION_TRADE_DATE_MISMATCH',
            'POINTER_PUBLICATION_STATE_INVALID',
            'POINTER_PUBLICATION_SEAL_INVALID',
            'POINTER_PUBLICATION_HASH_INVALID',
            'POINTER_ORPHAN_DETECTED',
            'POINTER_SWITCH_STARTED',
            'POINTER_SWITCH_COMPLETED',
            'POINTER_SWITCH_FAILED',
            'POINTER_SWITCH_ROLLED_BACK',
            'POINTER_POST_SWITCH_VERIFIED',
            'POINTER_POST_SWITCH_MISMATCH',
            'CURRENT_PUBLICATION_DEMOTED',
            'CURRENT_PUBLICATION_PROMOTED',
            'CURRENT_PUBLICATION_REPLACE_BLOCKED',
            'CURRENT_PUBLICATION_FORCE_REPLACED',
            'CORRECTION_BASELINE_LINK_VERIFIED',
            'CORRECTION_BASELINE_LINK_MISSING',
            'CORRECTION_BASELINE_LINK_INVALID',
            'CORRECTION_REPLACEMENT_LINK_CREATED',
            'CORRECTION_REPLACEMENT_LINK_VERIFIED',
            'CORRECTION_REPLACEMENT_LINK_INVALID',
            'CORRECTION_POINTER_SWITCH_CREATED',
            'CORRECTION_POINTER_SWITCH_BLOCKED',
            'CORRECTION_LINEAGE_INCOMPLETE',
            'CORRECTION_BASELINE_POINTER_PRESERVED',
            'REPLAY_LINEAGE_MATCHED',
            'REPLAY_RUN_PUBLICATION_MISMATCH',
            'REPLAY_POINTER_PUBLICATION_MISMATCH',
            'REPLAY_CORRECTION_LINEAGE_MISMATCH',
            'EVIDENCE_LINEAGE_CONTEXT_INCLUDED',
            'EVIDENCE_RUN_PUBLICATION_CONTEXT_INCLUDED',
            'EVIDENCE_POINTER_CONTEXT_INCLUDED',
            'EVIDENCE_CORRECTION_LINEAGE_CONTEXT_INCLUDED',
            'EVIDENCE_LINEAGE_CONTEXT_MISSING',
        ] as $code) {
            $this->assertStringContainsString('`'.$code.'`', $registry, $code.' must be registered.');
            $this->assertStringContainsString("('".$code."'", $seed, $code.' must be seeded.');
        }
    }

    // The latest-trade-date prohibition previously checked four named paths here.
    // ReadPathShortcutProhibitionTest applies it to the whole runtime.
    public function test_linkage_inventory_covers_every_stage_of_the_run_publication_chain(): void
    {
        $inventory = $this->read('docs/market_data/ops/RUN_PUBLICATION_POINTER_LINKAGE_INVENTORY.md');

        foreach (['run creation', 'publication candidate', 'current pointer', 'pointer switch', 'correction baseline', 'correction replacement', 'replay', 'evidence', 'command output', 'static guard'] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }
    }

    private function read(string $relativePath): string
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }
}
