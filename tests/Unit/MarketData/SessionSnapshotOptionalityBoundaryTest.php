<?php

use App\Domain\MarketData\MarketDataSemanticBindings;
use App\Infrastructure\Persistence\MarketData\EligibilitySnapshotScopeRepository;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W20 — optional session snapshot decision and implementation, stage 17/19 optional.
 *
 * Exit gate: "optional snapshot cannot become strategy engine and, when disabled, does not create
 * an implied missing feature."
 *
 * Owner contracts:
 *   docs/market_data/session_snapshot/Session_Snapshot_Contract_LOCKED.md
 *   docs/market_data/session_snapshot/Session_Snapshot_Scope_Selection_and_Dependencies_LOCKED.md
 *
 * An optional feature has two honest states and one dishonest one. Enabled and working is honest.
 * Disabled and saying so is honest. Present, silent, and empty is not — a reader cannot tell it
 * from a feature that is switched on and failing, which is the implied missing feature the exit
 * gate names.
 */
class SessionSnapshotOptionalityBoundaryTest extends TestCase
{
    use UsesMarketDataSqlite;

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

    /**
     * The feature state is declared rather than inferred from an empty table, and it defaults to
     * disabled because the snapshot has never been captured.
     */
    public function test_the_feature_state_is_explicit_and_defaults_to_disabled(): void
    {
        $config = require __DIR__.'/../../../config/market_data.php';

        $this->assertArrayNotHasKey('enabled', $config['session_snapshot'], 'unregistered runtime activation keys are forbidden');
        $this->assertSame('DISABLED', MarketDataSemanticBindings::SESSION_SNAPSHOT_FEATURE_STATE);
    }

    /**
     * A disabled feature declines by name and exits successfully. Exiting non-zero would report an
     * operational failure where there is only a switched-off option.
     */
    public function test_a_disabled_capture_declines_by_name_without_reporting_failure(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Console/Commands/MarketData/CaptureSessionSnapshotCommand.php'
        );

        $this->assertStringContainsString('SESSION_SNAPSHOT_FEATURE_DISABLED', $source);
        $this->assertStringContainsString("'feature_state' => \$featureState->state()", $source);
        $this->assertMatchesRegularExpression(
            '/SESSION_SNAPSHOT_FEATURE_DISABLED[\s\S]{0,400}?return 0;/',
            $source,
            'a disabled optional feature is not an error'
        );
    }

    /**
     * Snapshot absence cannot block EOD sealing, and here that is structural rather than handled:
     * the pipeline has no reference to the snapshot at all, so there is no path by which it could
     * interfere.
     */
    public function test_the_eod_pipeline_has_no_dependency_on_the_snapshot(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Application/MarketData/Services/MarketDataPipelineService.php'
        );

        $this->assertStringNotContainsString('SessionSnapshot', $source);
        $this->assertStringNotContainsString('session_snapshot', $source);
    }

    /**
     * Scope resolves from upstream dataset membership only. The forbidden narrowing inputs are
     * named in the contract, and their absence from the resolver is what keeps an optional
     * reference layer from quietly becoming a strategy engine.
     */
    public function test_scope_selection_consults_no_downstream_decision(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php'
        );

        foreach (['watchlist', 'pick', 'ranking', 'score', 'portfolio', 'position', 'execution', 'broker'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source, 'snapshot scope must not derive from '.$forbidden);
        }
    }

    /**
     * An unrecognised scope is refused rather than silently widened. The previous fallback was
     * `universe_only`, a name absent from the scope contract, so a mistyped configuration produced
     * the widest scope instead of failing.
     */
    public function test_an_unrecognised_scope_is_refused_rather_than_guessed(): void
    {
        config()->set('market_data.session_snapshot.scope_default', 'top_ranked_only');

        $this->expectExceptionMessageMatches('/SESSION_SNAPSHOT_SCOPE_UNRECOGNISED/');
        (new EligibilitySnapshotScopeRepository())->getScopeForTradeDate('2026-03-24');
    }

    /**
     * Both documented scopes resolve. Refusing everything would satisfy the guard above while
     * making the feature unusable.
     */
    public function test_both_documented_scopes_resolve(): void
    {
        foreach (['eligibility_set', 'eligible_only'] as $scope) {
            config()->set('market_data.session_snapshot.scope_default', $scope);

            $this->assertIsArray(
                (new EligibilitySnapshotScopeRepository())->getScopeForTradeDate('2026-03-24'),
                $scope.' is a documented upstream-safe scope'
            );
        }
    }
}
