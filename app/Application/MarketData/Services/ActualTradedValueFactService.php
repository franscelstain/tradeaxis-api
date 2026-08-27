<?php

namespace App\Application\MarketData\Services;

use RuntimeException;

/**
 * The required-field set for a source-backed actual traded value.
 *
 * `Volume_and_Turnover_Normalization_LOCKED.md` makes source, currency, market segment, observed
 * date and quality state required, and makes an unavailable value NULL rather than derived. The
 * platform satisfied the second half by never populating the field at all — the current adapter
 * declares `provides_actual_traded_value => false` — which meant the first half had no exercised
 * path. A required-field rule that only runs where the value is absent enforces nothing, and this
 * package has already recorded three defects of exactly that shape.
 *
 * This service is the enforced path. It runs when a value is present and does nothing when the
 * value is legitimately NULL, so the day a source begins reporting traded value, the requirement
 * is already load-bearing rather than newly discovered.
 */
class ActualTradedValueFactService
{
    const REQUIRED_FIELDS = ['source', 'currency_code', 'market_segment', 'observed_date', 'quality_state'];

    const CURRENCY = 'IDR';

    const MARKET_SEGMENT = 'IDX_REGULAR';

    /**
     * Normalize one actual traded-value fact for storage.
     *
     * @param  array<string,mixed>  $fact
     * @return array{traded_value_idr_actual: string|null, trade_count_actual: int|null}
     */
    public function normalize(array $fact): array
    {
        $value = array_key_exists('traded_value_idr_actual', $fact) ? $fact['traded_value_idr_actual'] : null;
        $tradeCount = array_key_exists('trade_count_actual', $fact) ? $fact['trade_count_actual'] : null;

        if ($value !== null) {
            $this->assertNotDerivedFromProxy($fact);
            $this->assertStorable($fact);
        }

        // Trade count is source-backed and separately nullable: it is not implied by the traded
        // value being present, and a present traded value does not license inventing one.
        if ($tradeCount !== null) {
            $this->assertStorable($fact, 'trade count');
            if ((int) $tradeCount < 0) {
                throw new RuntimeException('ACTUAL_TRADE_COUNT_INVALID: a negative trade count is not a source-backed fact.');
            }
        }

        return [
            'traded_value_idr_actual' => $value === null ? null : $this->decimalString($value),
            'trade_count_actual' => $tradeCount === null ? null : (int) $tradeCount,
        ];
    }

    /**
     * @param  array<string,mixed>  $fact
     */
    public function assertStorable(array $fact, string $subject = 'actual traded value'): void
    {
        $missing = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $fact) || trim((string) $fact[$field]) === '') {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'ACTUAL_TRADED_VALUE_PROVENANCE_INCOMPLETE: a populated '.$subject.' requires '
                .implode(', ', self::REQUIRED_FIELDS).'; missing '.implode(', ', $missing).'.'
            );
        }

        if (strtoupper(trim((string) $fact['currency_code'])) !== self::CURRENCY) {
            throw new RuntimeException(
                'ACTUAL_TRADED_VALUE_CURRENCY_UNSUPPORTED: '.$fact['currency_code']
                .' is not the declared currency of this market-data scope.'
            );
        }

        if (strtoupper(trim((string) $fact['market_segment'])) !== self::MARKET_SEGMENT) {
            throw new RuntimeException(
                'ACTUAL_TRADED_VALUE_SEGMENT_UNSUPPORTED: '.$fact['market_segment']
                .' is not the Regular-Market segment this value is defined for.'
            );
        }
    }

    /**
     * A proxy value may never be admitted through this path.
     *
     * The contract's whole subject is that the two must not be confused, and the most direct way
     * that confusion enters a database is a caller passing the proxy where the actual is expected.
     *
     * @param  array<string,mixed>  $fact
     */
    public function assertNotDerivedFromProxy(array $fact): void
    {
        $origin = strtoupper(trim((string) ($fact['value_origin'] ?? '')));

        if ($origin === '' || $origin === 'SOURCE_REPORTED') {
            return;
        }

        throw new RuntimeException(
            'ACTUAL_TRADED_VALUE_NOT_SOURCE_REPORTED: value_origin='.$origin
            .'; an unavailable actual traded value is NULL and is never derived from a proxy or any other computed value.'
        );
    }

    /**
     * Preserve source precision; round only at the locked storage boundary of two decimals.
     */
    private function decimalString($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
