<?php

use PHPUnit\Framework\TestCase;
use Tests\Support\MarketData\ReadsMarketDataSchema;

/**
 * MD-B01 — what this domain owns, and where that ownership physically lives.
 *
 * Owner contracts: `MARKET_DATA_PLATFORM_EOD_BASELINE.md` "What this domain owns",
 * `Domain_Boundary_Invariants_LOCKED.md` "Market-data ownership (LOCKED)", and
 * `Terminology_and_Scope.md` "Scope of Market Data Platform (LOCKED)". All three are enumerated
 * lists under a governing introducer, so the predicate under proof is the composed one — "this
 * domain remains the owner for X" — not "a table named after X exists".
 *
 * `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 forbids treating such a row as proof-complete
 * because the referenced target exists, and `MD-B01-A012` invalidated two rows for exactly that. So
 * each artifact is proven three ways:
 *
 *   1. the canonical surface exists, located by a distinguishing column rather than by table name —
 *      an empty namesake table would otherwise pass;
 *   2. a market-data class actually produces or serves it, located inside the market-data tree;
 *   3. nothing outside that tree touches the surface, asserted once across the whole application.
 *
 * Point 3 is what makes this ownership rather than existence. It is asserted against a pinned set,
 * so a new writer appearing outside the domain fails here instead of quietly becoming normal.
 */
class DomainOwnershipSurfaceTest extends TestCase
{
    use ReadsMarketDataSchema;

    /**
     * The three market-data Eloquent models that live in the generic `app/Models` namespace rather
     * than under `app/**\/MarketData`. They are market-data owned; their location is historical.
     * Pinning the set is what lets the boundary assertion below be exact.
     */
    private const KNOWN_OUTSIDE_TREE_OWNERS = [
        'app/Models/EodDatasetCorrection.php',
        'app/Models/EodRun.php',
        'app/Models/EodRunEvent.php',
    ];

    /**
     * rule id => [label, table => distinguishing columns, owning market-data classes]
     *
     * A distinguishing column is one that carries the artifact's meaning. `eod_indicators` existing
     * does not show versioned indicators; `indicator_set_version` and `formula_version` do.
     *
     * @return array<string,array{label:string,surface:array<string,array<int,string>>,owners:array<int,string>}>
     */
    private function ownership(): array
    {
        return [
            // MARKET_DATA_PLATFORM_EOD_BASELINE.md — "Domain ini tetap menjadi owner untuk:"
            'MD-S001-R0101' => [
                'label' => 'canonical EOD bars',
                'surface' => ['eod_bars' => ['trade_date', 'open', 'high', 'low', 'close', 'volume'], 'eod_bars_history' => ['trade_date']],
                'owners' => ['app/Application/MarketData/Services/EodBarsIngestService.php'],
            ],
            'MD-S001-R0102' => [
                'label' => 'price-product identity and adjustment lineage',
                'surface' => [
                    'md_adjustment_factor_sets' => ['factor_set_uid', 'price_product_code', 'content_hash'],
                    'md_adjustment_factors' => ['factor_set_id'],
                    'md_adjustment_factor_decisions' => ['factor_set_id'],
                ],
                'owners' => [
                    'app/Application/MarketData/Services/AnalyticalProductIdentityService.php',
                    'app/Application/MarketData/Services/AdjustmentFactorSetService.php',
                ],
            ],
            'MD-S001-R0103' => [
                'label' => 'versioned indicators',
                'surface' => ['eod_indicators' => ['indicator_set_version', 'formula_version'], 'eod_indicators_history' => ['trade_date']],
                'owners' => ['app/Application/MarketData/Services/EodIndicatorsComputeService.php'],
            ],
            'MD-S001-R0104' => [
                'label' => 'coverage, quality, liquidity, event-risk, and data-usability/readiness facts',
                'surface' => ['eod_eligibility' => [
                    'delivery_state', 'canonical_quality_state', 'liquidity_state', 'event_risk_state', 'eligible',
                ]],
                'owners' => [
                    'app/Application/MarketData/Services/EligibilityDecisionService.php',
                    'app/Application/MarketData/Services/MarketDataReadinessService.php',
                ],
            ],
            'MD-S001-R0105' => [
                'label' => 'coverage gate semantics',
                'surface' => ['eod_runs' => ['coverage_gate_state']],
                'owners' => [
                    'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                    'app/Application/MarketData/Services/CoverageGateStateNormalizer.php',
                ],
            ],
            'MD-S001-R0106' => [
                'label' => 'seal/publication/readability behavior',
                'surface' => [
                    'eod_publications' => ['seal_state', 'sealed_at', 'is_current'],
                    'eod_current_publication_pointer' => ['publication_id'],
                ],
                'owners' => ['app/Application/MarketData/Services/MarketDataReadinessService.php'],
            ],
            'MD-S001-R0107' => [
                'label' => 'replay and correction behavior',
                'surface' => [
                    'md_replay_daily_metrics' => ['trade_date'],
                    'eod_dataset_corrections' => ['status'],
                ],
                'owners' => [
                    'app/Application/MarketData/Services/ReplayVerificationService.php',
                    'app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php',
                ],
            ],
            'MD-S001-R0108' => [
                'label' => 'upstream audit evidence',
                'surface' => ['eod_run_events' => ['severity']],
                'owners' => ['app/Application/MarketData/Services/MarketDataEvidenceExportService.php'],
            ],
            'MD-S001-R0109' => [
                'label' => 'date-driven import contract',
                'surface' => ['eod_runs' => ['trade_date_requested']],
                'owners' => [
                    'app/Domain/MarketData/MarketDataScope.php',
                    'app/Console/Commands/MarketData/IngestEodBarsCommand.php',
                ],
            ],

            // Domain_Boundary_Invariants_LOCKED.md — "Market Data Platform may own and publish:"
            'MD-S020-R0019' => [
                'label' => 'point-in-time issuer, instrument, listing, provider-symbol, calendar, board, and trading-status facts',
                'surface' => [
                    'md_issuers' => ['issuer_id'],
                    'md_instruments' => ['instrument_id'],
                    'md_listings' => ['listing_id'],
                    'md_provider_symbol_mappings' => ['provider', 'provider_symbol'],
                    'md_market_calendar_revisions' => ['recorded_at'],
                    'md_exchange_market_structure_revisions' => ['recorded_at'],
                    'md_trading_status_revisions' => ['recorded_at'],
                ],
                'owners' => [
                    'app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php',
                    'app/Application/MarketData/Services/AuthoritativeTradingStatusSnapshotService.php',
                ],
            ],
            'MD-S020-R0020' => [
                'label' => 'canonical IDX Regular-Market EOD observations',
                'surface' => ['md_source_observations' => ['source_observation_id'], 'eod_bars' => ['source']],
                'owners' => ['app/Domain/MarketData/MarketDataScope.php', 'app/Application/MarketData/Services/EodBarsIngestService.php'],
            ],
            'MD-S020-R0021' => [
                'label' => 'explicit RAW, STRUCTURAL_ADJUSTED, and TOTAL_RETURN data products',
                'surface' => ['eod_indicators' => ['price_product_code', 'price_product_version']],
                'owners' => ['app/Domain/MarketData/MarketDataScope.php', 'app/Application/MarketData/Services/AnalyticalProductIdentityService.php'],
            ],
            'MD-S020-R0022' => [
                'label' => 'verified and versioned corporate-action factors and contamination state',
                'surface' => [
                    'market_data_corporate_actions' => ['action_type'],
                    'md_corporate_action_revisions' => ['recorded_at'],
                    'eod_indicators' => ['corporate_action_flag'],
                ],
                'owners' => ['app/Application/MarketData/Services/CorporateActionDerivationService.php'],
            ],
            'MD-S020-R0024' => [
                'label' => 'separate coverage, quality, liquidity-measure, trading-status, and event-risk facts',
                'surface' => ['eod_eligibility' => [
                    'delivery_state', 'canonical_quality_state', 'liquidity_state', 'temporal_status_state', 'event_risk_state',
                ]],
                'owners' => ['app/Application/MarketData/Services/EligibilityDecisionService.php'],
            ],
            'MD-S020-R0025' => [
                'label' => 'a row-level data-usability decision with explicit factual reasons',
                'surface' => ['eod_eligibility' => ['eligible', 'reason_code', 'eligibility_reasons_json'], 'eod_reason_codes' => ['code']],
                'owners' => ['app/Application/MarketData/Services/EligibilityDecisionService.php'],
            ],
            'MD-S020-R0026' => [
                'label' => 'run, configuration, hash, seal, publication, correction, and supersession identity',
                'surface' => [
                    'eod_runs' => ['run_id'],
                    'md_config_snapshots' => ['config_snapshot_id'],
                    'eod_publications' => ['publication_id', 'publication_version', 'seal_state'],
                    'eod_dataset_corrections' => ['correction_id'],
                ],
                'owners' => [
                    'app/Application/MarketData/Services/DeterministicHashService.php',
                    'app/Application/MarketData/Services/PublicationGovernanceBindingService.php',
                ],
            ],
            'MD-S020-R0027' => [
                'label' => 'replay and reproducibility evidence',
                'surface' => ['md_replay_daily_metrics' => ['trade_date'], 'md_replay_reason_code_counts' => ['reason_code']],
                'owners' => ['app/Application/MarketData/Services/ReplayVerificationService.php'],
            ],
            'MD-S020-R0028' => [
                'label' => 'optional non-streaming supplemental session snapshots',
                'surface' => ['md_session_snapshots' => ['trade_date']],
                'owners' => ['app/Application/MarketData/Services/SessionSnapshotService.php'],
            ],

            // Terminology_and_Scope.md — "Its target minimum outputs are:"
            'MD-S056-R0055' => [
                'label' => 'canonical Regular-Market EOD bars',
                'surface' => ['eod_bars' => ['trade_date', 'close']],
                'owners' => ['app/Application/MarketData/Services/EodBarsIngestService.php'],
            ],
            'MD-S056-R0056' => [
                'label' => 'explicit analytical price products and adjustment lineage',
                'surface' => ['eod_indicators' => ['price_product_code', 'factor_set_hash'], 'md_adjustment_factors' => ['factor_set_id']],
                'owners' => ['app/Application/MarketData/Services/AnalyticalProductIdentityService.php'],
            ],
            'MD-S056-R0057' => [
                'label' => 'versioned EOD indicators',
                'surface' => ['eod_indicators' => ['indicator_set_version', 'formula_version']],
                'owners' => ['app/Application/MarketData/Services/EodIndicatorsComputeService.php'],
            ],
            'MD-S056-R0058' => [
                'label' => 'separate coverage, quality, liquidity, event-risk, and data-usability facts',
                'surface' => ['eod_eligibility' => [
                    'delivery_state', 'canonical_quality_state', 'liquidity_state', 'event_risk_state', 'eligible',
                ]],
                'owners' => ['app/Application/MarketData/Services/EligibilityDecisionService.php'],
            ],
            'MD-S056-R0059' => [
                'label' => 'run and configuration identity',
                'surface' => ['eod_runs' => ['run_id'], 'md_config_snapshots' => ['config_snapshot_id']],
                'owners' => ['app/Application/MarketData/Services/MarketDataPipelineService.php'],
            ],
            'MD-S056-R0060' => [
                'label' => 'content hashes',
                'surface' => ['eod_publications' => ['bars_batch_hash', 'indicators_batch_hash']],
                'owners' => ['app/Application/MarketData/Services/DeterministicHashService.php'],
            ],
            'MD-S056-R0061' => [
                'label' => 'versioned seal/publication metadata',
                'surface' => ['eod_publications' => ['publication_version', 'seal_state', 'sealed_at']],
                'owners' => ['app/Application/MarketData/Services/PublicationGovernanceBindingService.php'],
            ],
        ];
    }

    public function ownershipProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->ownership() as $ruleId => $spec) {
            $out[$ruleId] = [$ruleId, $spec];
        }

        return $out;
    }

    /**
     * @dataProvider ownershipProvider
     *
     * @param  array{label:string,surface:array<string,array<int,string>>,owners:array<int,string>}  $spec
     */
    public function test_the_owned_artifact_has_a_canonical_surface_and_a_market_data_owner(string $ruleId, array $spec): void
    {
        $schema = $this->schemaColumnMap();
        $this->assertGreaterThan(45, count($schema), 'the schema parse must reach the full table surface');

        foreach ($spec['surface'] as $table => $columns) {
            $this->assertArrayHasKey($table, $schema, $ruleId.': '.$spec['label'].' has no canonical table '.$table);
            foreach ($columns as $column) {
                $this->assertArrayHasKey(
                    $column,
                    $schema[$table],
                    $ruleId.': '.$table.' carries no '.$column.', so the table name alone would be the only evidence'
                );
            }
        }

        foreach ($spec['owners'] as $owner) {
            $path = $this->repositoryRoot().'/'.$owner;
            $this->assertFileExists($path, $ruleId.': the declared owning surface is missing');
            $this->assertStringContainsString(
                '/MarketData',
                '/'.$owner,
                $ruleId.': an owning class must live inside the market-data tree'
            );
        }
    }

    /**
     * The ownership half. Every reference to a market-data table from outside the market-data tree
     * is enumerated; the set is exactly the three legacy Eloquent models. A new writer elsewhere in
     * the application fails here rather than establishing a second owner by habit.
     */
    public function test_no_surface_outside_the_market_data_tree_touches_a_market_data_table(): void
    {
        $tables = array_keys($this->schemaColumnMap());
        $this->assertGreaterThan(45, count($tables), 'the table list must be populated before it is searched for');

        $pattern = '/\b('.implode('|', array_map('preg_quote', $tables)).')\b/';

        $offenders = [];
        $scanned = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->repositoryRoot().'/app', FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($this->repositoryRoot()) + 1));
            if (strpos($relative, '/MarketData/') !== false) {
                continue;
            }
            $scanned++;
            if (preg_match($pattern, $this->stripPhpComments((string) file_get_contents($file->getPathname())))) {
                $offenders[] = $relative;
            }
        }
        sort($offenders);

        $this->assertGreaterThan(10, $scanned, 'the outside-the-tree scan must actually reach files');
        $this->assertSame(
            self::KNOWN_OUTSIDE_TREE_OWNERS,
            $offenders,
            'only the pinned legacy market-data models may reference a market-data table from outside the domain tree'
        );
    }

    /**
     * `MD-S056-R0114` — Promote owns indicators, eligibility, hash, seal, and finalize.
     *
     * This is an affirmative ownership rule, so absence proves nothing: it needs the promote path to
     * actually run those five stages and the import path to actually not. Both halves are read from
     * the real stage sequences in `MarketDataPipelineService`.
     */
    public function test_promote_owns_the_five_stages_and_import_owns_none_of_them(): void
    {
        $pipeline = $this->stripPhpComments(
            (string) file_get_contents($this->repositoryRoot().'/app/Application/MarketData/Services/MarketDataPipelineService.php')
        );

        $this->assertSame(
            1,
            preg_match('/function promoteSingleDay\b.*?\n    \}/s', $pipeline, $promote),
            'the promote entry point must be locatable before its stages are read'
        );
        $this->assertSame(
            1,
            preg_match('/function importSingleDay\b.*?\n    \}/s', $pipeline, $import),
            'the import entry point must be locatable before its stages are read'
        );

        foreach (['COMPUTE_INDICATORS', 'BUILD_ELIGIBILITY', 'HASH', 'SEAL', 'FINALIZE'] as $stage) {
            $this->assertStringContainsString($stage, $promote[0], 'promote must own the '.$stage.' stage');
            $this->assertStringNotContainsString($stage, $import[0], 'import must not run the '.$stage.' stage');
        }

        $this->assertStringContainsString('INGEST_BARS', $import[0], 'import must still own ingestion');
        $this->assertStringContainsString("'import_only'", $import[0], 'the import request mode must be import-only');
    }
}
