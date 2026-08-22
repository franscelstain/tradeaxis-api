<?php

use PHPUnit\Framework\TestCase;

class ReplayHistoricalDeterminismHardeningStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }


    public function test_replay_actual_resolver_has_historical_selector_scoped_path(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        foreach ([
            'resolvePublicationForReplayActualState',
            'resolveExplicitFixturePublication',
            'expectsHistoricalReplayPublication',
            'resolvePublicationForEvidenceAudit',
            'replay_historical_actual_state',
            'replay_fixture_explicit_publication',
            'expected_replay_resolution_context',
            'actual_replay_resolution_context',
            'HISTORICAL_PUBLICATION_AUDIT',
            'HISTORICAL_SEALED_PUBLICATION',
            'historical_publication_allowed',
            'current_pointer_required',
            'current_pointer_status',
            'lineage_verification_status',
            'REPLAY_HISTORICAL_PUBLICATION_RESOLVED',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service, 'Replay historical actual resolver must keep '.$needle);
        }
    }

    public function test_replay_historical_artifacts_are_publication_scoped(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));
        $evidenceRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php'));
        $fixtureCommand = file_get_contents($this->projectPath('app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php'));

        foreach ([
            'dominantReasonCodesForEvidencePublication',
            'exportEligibilityRowsForEvidencePublication',
            'artifact_scope',
            'publication:',
            'coverage_basis_publication_id',
            'coverage_basis_run_id',
            'eod_eligibility_history',
            "where('elig.publication_id', \$publicationId)",
        ] as $needle) {
            $this->assertStringContainsString($needle, $service.$evidenceRepository, 'Historical replay artifact proof must keep '.$needle);
        }

        $this->assertStringContainsString('{--publication_id=}', $fixtureCommand, 'Fixture generation must expose explicit publication context for historical runtime proof.');
        $this->assertStringContainsString('generateFixtureFromRun(', $fixtureCommand);
    }

    public function test_historical_replay_does_not_weaken_consumer_current_pointer_resolver(): void
    {
        $publicationRepository = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate', $publicationRepository);
        $this->assertStringContainsString('Official read-side gateway for consumer paths', $publicationRepository);
        $this->assertStringContainsString('findReadableCurrentPublicationForRun($run->run_id, $run->trade_date_requested)', $service, 'Current replay/fixture path must still validate current pointer when current context is expected.');
        $this->assertStringContainsString('public function findReadableCurrentPublicationForRun($runId, $tradeDate)', $publicationRepository);
    }

    public function test_replay_historical_resolver_avoids_latest_max_and_current_pointer_fallback_in_historical_method(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));
        $start = strpos($service, 'private function resolvePublicationForReplayActualState');
        $end = strpos($service, 'private function expectsHistoricalReplayPublication', $start);
        $method = $start !== false && $end !== false ? substr($service, $start, $end - $start) : '';

        $this->assertStringContainsString('resolvePublicationForEvidenceAudit', $method);
        $this->assertStringContainsString('replay_historical_actual_state', $method);

        foreach (['MAX(trade_date)', "max('trade_date')", 'latest()', 'orderByDesc('] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $method, 'Historical replay resolver must not use '.$forbidden);
        }
    }

    public function test_replay_historical_reason_codes_are_registered_and_seeded(): void
    {
        $registry = file_get_contents($this->projectPath('docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md'));
        $seed = file_get_contents($this->projectPath('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql'));
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        foreach ([
            'REPLAY_HISTORICAL_PUBLICATION_RESOLVED',
            'REPLAY_CURRENT_PUBLICATION_RESOLVED',
            'REPLAY_NO_PUBLICATION_ACTUAL_STATE',
            'REPLAY_HISTORICAL_PUBLICATION_MISSING',
            'REPLAY_HISTORICAL_PUBLICATION_UNSEALED',
            'REPLAY_PUBLICATION_RUN_MISMATCH',
            'REPLAY_HISTORICAL_ARTIFACT_SCOPE_MISMATCH',
            'REPLAY_EXPECTED_HISTORICAL_ACTUAL_CURRENT_MISMATCH',
            'REPLAY_CURRENT_POINTER_MOVED_HISTORICAL_VALID',
        ] as $reasonCode) {
            $this->assertStringContainsString($reasonCode, $registry, 'Reason registry missing '.$reasonCode);
            $this->assertStringContainsString($reasonCode, $seed, 'Reason seed missing '.$reasonCode);
            $this->assertStringContainsString($reasonCode, $service.$registry, 'Replay code/docs must reference '.$reasonCode);
        }
    }
}
