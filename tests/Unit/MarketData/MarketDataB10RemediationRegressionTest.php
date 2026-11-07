<?php

class MarketDataB10RemediationRegressionTest extends TestCase
{
    public function test_b10_migration_uses_short_explicit_reconciliation_index_names_and_repairs_partial_ddl_state()
    {
        $path = base_path('database/migrations/2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php');
        $source = file_get_contents($path);

        $this->assertStringContainsString("uq_md_pub_proj_recon_uid", $source);
        $this->assertStringContainsString("idx_md_pub_proj_recon_date_state", $source);
        $this->assertStringContainsString("idx_md_pub_proj_recon_pub_checked", $source);
        $this->assertStringContainsString("idx_md_pub_proj_recon_checked", $source);
        $this->assertStringNotContainsString('md_publication_projection_reconciliations_reconciliation_uid_unique', $source);
        $this->assertStringContainsString("Schema::hasTable(self::RECON_TABLE)", $source);
        $this->assertStringContainsString('ensureReconciliationIndexes', $source);
        $this->assertStringContainsString('DROP TRIGGER IF EXISTS', $source);
    }

    public function test_projection_reconciliation_scheduler_uses_only_registered_scheduler_config()
    {
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $config = file_get_contents(base_path('config/market_data.php'));

        $this->assertStringContainsString('market-data:reconcile:publication-projection --latest', $kernel);
        $this->assertStringContainsString('->hourly()', $kernel);
        $this->assertStringContainsString("market_data.scheduler.without_overlapping_minutes", $kernel);
        $this->assertStringNotContainsString('market_data.projection_reconciliation.', $kernel);
        $this->assertStringNotContainsString("'projection_reconciliation' =>", $config);
    }

    public function test_projection_repair_is_registered_but_not_automatically_scheduled()
    {
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $command = file_get_contents(base_path('app/Console/Commands/MarketData/RepairPublicationProjectionCommand.php'));

        $this->assertStringContainsString('RepairPublicationProjectionCommand::class', $kernel);
        $this->assertStringContainsString('market-data:repair:publication-projection', $command);
        $this->assertStringContainsString('{--dry-run}', $command);
        $this->assertStringNotContainsString("->command('market-data:repair:publication-projection", $kernel);
    }

    public function test_eligibility_quality_is_bound_before_hash_and_projection_repair_preserves_status_lineage()
    {
        $pipeline = file_get_contents(base_path('app/Application/MarketData/Services/MarketDataPipelineService.php'));
        $artifacts = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php'));

        // Ignore formatting, preserving operators/literals in both real producer methods.
        // MD-S051 date-level finding remains BLOCKED even when coverage passes.
        $expected = '$qualityGateState=$coverageGateState===\'PASS\'?($anomaly[\'date_level_anomaly_state\']===DateLevelAnomalyCheckService::STATE_FINDING?\'BLOCKED\':\'PASS\'):($coverageGateState===\'FAIL\'?\'FAIL\':\'BLOCKED\');';
        $tokens = static function (string $code): string {
            $out = '';
            foreach (token_get_all($code) as $token) {
                if (is_array($token)) {
                    if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
                    $out .= $token[1];
                } else $out .= $token;
            }
            return $out;
        };
        $lines = file(base_path('app/Application/MarketData/Services/MarketDataPipelineService.php'));
        foreach (['completeCoverageEvaluation', 'completeEligibility'] as $method) {
            $scope = new ReflectionMethod(\App\Application\MarketData\Services\MarketDataPipelineService::class, $method);
            $body = '<?php '.implode('', array_slice($lines, $scope->getStartLine()-1, $scope->getEndLine()-$scope->getStartLine()+1));
            $normalized = $tokens($body);
            $this->assertStringContainsString($expected, $normalized, $method.' must derive PASS only from coverage PASS');
            $this->assertStringContainsString("'quality_gate_state'=>\$qualityGateState", $normalized, $method.' must bind the derived quality state');
            $counterfactual = str_replace($expected, '$qualityGateState=$coverageGateState===\'PASS\'?\'PASS\':\'BLOCKED\';', $normalized);
            $this->assertStringNotContainsString($expected, $counterfactual, 'Omitted anomaly gate must be rejected');
            $wrongCoverage = str_replace($expected, str_replace("==='PASS'", "==='FAIL'", $expected), $normalized);
            $this->assertStringNotContainsString($expected, $wrongCoverage, 'Wrong coverage branch must be rejected');
        }
        // Insert whitespace without splitting the === operator or altering any literal.
        $formatted = str_replace('===', " /* comment */ ===\n", $expected);
        $this->assertStringContainsString($expected, $tokens('<?php '.$formatted));
        $this->assertStringContainsString("'trading_status_revision_id', 'trading_status_source_observation_id'", $artifacts);
    }
    public function test_deployed_projection_repair_probe_is_transactional_non_production_and_history_read_only()
    {
        $probe = file_get_contents(base_path('tools/market_data/MarketDataB10ProjectionRepairDeployedProbe.php'));
        $spec = file_get_contents(base_path('docs/market_data/development/implementation/tests/MarketDataPublicationLifecycleProofSpec.php'));

        $this->assertStringContainsString('PRODUCTION_ENVIRONMENT_REFUSED', $probe);
        $this->assertStringContainsString('beginTransaction()', $probe);
        $this->assertStringContainsString('transactionLevel()', $probe);
        $this->assertStringContainsString('rollBack()', $probe);
        $this->assertStringContainsString("DB::table('eod_eligibility')", $probe);
        $this->assertStringContainsString("DB::table('eod_eligibility_history')", $probe);
        $this->assertStringContainsString("repairTradeDate(\$tradeDate, 'Controlled deployed projection repair probe')", $probe);
        $this->assertStringContainsString('post_rollback_reconciliation_state', $probe);
        $this->assertDoesNotMatchRegularExpression('/eod_eligibility_history[^;]+->(?:update|delete)\s*\(/s', $probe);
        $this->assertStringContainsString('tools/market_data/MarketDataB10ProjectionRepairDeployedProbe.php', $spec);
    }

}
