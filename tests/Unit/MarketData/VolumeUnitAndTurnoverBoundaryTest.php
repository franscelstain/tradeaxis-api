<?php

use App\Application\MarketData\Services\VolumeUnitNormalizationService;
use App\Domain\MarketData\MarketDataSemanticBindings;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;

/**
 * B13: canonical volume, unit identity, and the boundaries the turnover proxy must not cross.
 */
class VolumeUnitAndTurnoverBoundaryTest extends TestCase
{
    private function service(): VolumeUnitNormalizationService
    {
        return new VolumeUnitNormalizationService();
    }

    private function declaredShareCapabilities(): array
    {
        return [
            'volume_unit' => MarketDataSemanticBindings::CANONICAL_VOLUME_UNIT_CODE,
            'volume_unit_evidence_ref' => 'test:provider_doc:volume=shares',
        ];
    }

    public function test_a_declared_share_unit_normalizes_with_no_multiplier(): void
    {
        $identity = $this->service()->identityFromCapabilities($this->declaredShareCapabilities());

        $this->assertSame('SHARES', $identity['source_volume_unit_code']);
        $this->assertSame('1.00000000', $identity['volume_unit_normalization_factor']);
        $this->assertSame('VERIFIED', $identity['volume_unit_normalization_state']);
        $this->assertTrue($this->service()->isVerified($identity));
    }

    public function test_an_undeclared_unit_is_unverified_rather_than_assumed_to_be_shares(): void
    {
        $identity = $this->service()->identityFromCapabilities([]);

        $this->assertNull($identity['source_volume_unit_code']);
        $this->assertNull($identity['volume_unit_normalization_factor']);
        $this->assertSame('UNVERIFIED', $identity['volume_unit_normalization_state']);
        $this->assertFalse($this->service()->isVerified($identity));
    }

    public function test_a_declared_unit_without_evidence_does_not_normalize(): void
    {
        $identity = $this->service()->identityFromCapabilities(['volume_unit' => 'SHARES']);

        $this->assertSame('UNVERIFIED', $identity['volume_unit_normalization_state']);
        $this->assertFalse($this->service()->isVerified($identity));
    }

    public function test_a_lot_reporting_source_fails_closed_instead_of_being_converted(): void
    {
        $identity = $this->service()->identityFromCapabilities([
            'volume_unit' => 'LOTS',
            'volume_unit_evidence_ref' => 'test:provider_doc:volume=lots',
        ]);

        $this->assertSame('LOTS', $identity['source_volume_unit_code']);
        $this->assertNull($identity['volume_unit_normalization_factor']);
        $this->assertSame('UNVERIFIED', $identity['volume_unit_normalization_state']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/VOLUME_UNIT_NORMALIZATION_UNVERIFIED/');
        $this->service()->normalizeShareVolume(500, $identity);
    }

    public function test_volume_cannot_be_stored_without_verified_unit_normalization(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/VOLUME_UNIT_NORMALIZATION_UNVERIFIED/');
        $this->service()->assertStorable($this->service()->identityFromCapabilities([]));
    }

    public function test_zero_volume_is_preserved_as_a_source_backed_value(): void
    {
        $identity = $this->service()->identityFromCapabilities($this->declaredShareCapabilities());

        $zero = $this->service()->normalizeShareVolume(0, $identity);

        $this->assertNotNull($zero, 'Zero volume is a real value and is distinct from a missing one.');
        $this->assertSame(0, $zero);
    }

    public function test_a_missing_volume_stays_missing_and_never_becomes_zero(): void
    {
        $identity = $this->service()->identityFromCapabilities($this->declaredShareCapabilities());

        $this->assertNull($this->service()->normalizeShareVolume(null, $identity));
    }

    public function test_the_current_adapter_declares_its_volume_unit_and_evidence(): void
    {
        $capabilities = (new PublicApiEodBarsAdapter())->capabilities();

        $this->assertArrayHasKey('volume_unit', $capabilities);
        $this->assertArrayHasKey('volume_unit_evidence_ref', $capabilities);
        $this->assertTrue($this->service()->isVerified($this->service()->identityFromCapabilities($capabilities)));
    }

    public function test_the_adapter_still_declares_that_it_supplies_no_actual_traded_value(): void
    {
        $capabilities = (new PublicApiEodBarsAdapter())->capabilities();

        // The actual traded value stays NULL because the source does not report it. This assertion
        // exists so that a provider change makes the NULL a decision again rather than an
        // inherited default nobody re-examined.
        $this->assertFalse($capabilities['provides_actual_traded_value']);
    }

    public function test_market_data_declares_only_one_canonical_volume_unit(): void
    {
        $this->assertSame('SHARES', MarketDataSemanticBindings::CANONICAL_VOLUME_UNIT_CODE);
        $this->assertSame(
            'SHARES',
            VolumeUnitNormalizationService::UNIT_SHARES,
            'Lot-based units belong to downstream position sizing, not to market-data normalization.'
        );
    }
}
