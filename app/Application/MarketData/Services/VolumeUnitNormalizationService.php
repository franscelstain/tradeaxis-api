<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataSemanticBindings;
use RuntimeException;

/**
 * Provider volume-unit identity and the evidence that a stored volume is in share units.
 *
 * `Volume_and_Turnover_Normalization_LOCKED.md` defines raw volume as source-reported share units
 * "after verified unit normalization", and makes provider unit identity and normalization evidence
 * mandatory. The platform stored volume with neither: every row asserted share units on the
 * strength of nobody having said otherwise.
 *
 * Two rules do the work here, and they pull in opposite directions on purpose:
 *
 *  - a declared share unit normalizes with factor 1, because applying a lot multiplier to a source
 *    that already reports shares is the specific error the contract forbids;
 *  - an undeclared or non-share unit does not normalize at all. It fails closed rather than
 *    guessing a factor, because a guessed factor produces a clean, confident, wrong volume — and
 *    every metric downstream of it inherits that confidence.
 */
class VolumeUnitNormalizationService
{
    const STATE_VERIFIED = 'VERIFIED';

    const STATE_UNVERIFIED = 'UNVERIFIED';

    const UNIT_SHARES = MarketDataSemanticBindings::CANONICAL_VOLUME_UNIT_CODE;

    /**
     * Derive the unit identity for one acquisition from its adapter capability declaration.
     *
     * @param  array<string,mixed>  $capabilities
     * @return array<string,mixed>
     */
    public function identityFromCapabilities(array $capabilities): array
    {
        $unit = strtoupper(trim((string) ($capabilities['volume_unit'] ?? '')));
        $evidence = trim((string) ($capabilities['volume_unit_evidence_ref'] ?? ''));

        if ($unit === '' || $evidence === '') {
            return [
                'source_volume_unit_code' => $unit === '' ? null : $unit,
                'volume_unit_normalization_factor' => null,
                'volume_unit_normalization_state' => self::STATE_UNVERIFIED,
                'volume_unit_evidence_ref' => $evidence === '' ? null : $evidence,
            ];
        }

        if ($unit !== self::UNIT_SHARES) {
            // A non-share unit is not an error to swallow with a conversion. Market-data does not
            // own a lot-size configuration, so it has no authority to convert lots to shares.
            return [
                'source_volume_unit_code' => $unit,
                'volume_unit_normalization_factor' => null,
                'volume_unit_normalization_state' => self::STATE_UNVERIFIED,
                'volume_unit_evidence_ref' => $evidence,
            ];
        }

        return [
            'source_volume_unit_code' => self::UNIT_SHARES,
            'volume_unit_normalization_factor' => '1.00000000',
            'volume_unit_normalization_state' => self::STATE_VERIFIED,
            'volume_unit_evidence_ref' => $evidence,
        ];
    }

    /**
     * @param  array<string,mixed>  $identity
     */
    public function isVerified(array $identity): bool
    {
        return ($identity['volume_unit_normalization_state'] ?? null) === self::STATE_VERIFIED
            && ($identity['source_volume_unit_code'] ?? null) === self::UNIT_SHARES
            && trim((string) ($identity['volume_unit_evidence_ref'] ?? '')) !== ''
            && (float) ($identity['volume_unit_normalization_factor'] ?? 0) === 1.0;
    }

    /**
     * Volume must not be stored as canonical share units unless the unit identity is evidenced.
     *
     * @param  array<string,mixed>  $identity
     */
    public function assertStorable(array $identity, string $context = 'canonical volume'): void
    {
        if ($this->isVerified($identity)) {
            return;
        }

        throw new RuntimeException(
            'VOLUME_UNIT_NORMALIZATION_UNVERIFIED: '.$context.' cannot be stored as share units without a declared '
            .'provider unit identity and normalization evidence. Declared unit='
            .($identity['source_volume_unit_code'] ?? 'NONE')
            .', evidence='.(trim((string) ($identity['volume_unit_evidence_ref'] ?? '')) === '' ? 'NONE' : 'PRESENT').'.'
        );
    }

    /**
     * The normalized share volume for a source-reported figure.
     *
     * There is exactly one arithmetic path and its multiplier is always 1. That is not an oversight
     * waiting for a lot-size branch: the lot-size boundary places that conversion downstream, and a
     * multiplier reachable from here is the defect, not the feature.
     */
    public function normalizeShareVolume($sourceVolume, array $identity)
    {
        if ($sourceVolume === null) {
            return null;
        }

        $this->assertStorable($identity, 'source-reported volume');

        // Zero is a real source-backed value and is preserved as one. It is distinct from a missing
        // volume and from a missing bar, and it must not be coerced to null on the way through.
        return (int) $sourceVolume;
    }
}
