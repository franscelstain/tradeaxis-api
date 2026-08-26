<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;

/**
 * Pure analytical price-product builder. Canonical RAW bars are never mutated in persistence.
 */
class AnalyticalPriceProductService
{
    private $identity;

    public function __construct(AnalyticalProductIdentityService $identity = null)
    {
        $this->identity = $identity ?: new AnalyticalProductIdentityService();
    }

    public function build(array $canonicalBars, $productCode, array $context = []): array
    {
        $productCode = strtoupper(trim((string) $productCode));
        $this->identity->assertKnownProductCode($productCode);

        if ($productCode === MarketDataScope::TOTAL_RETURN_PRODUCT) {
            throw new \RuntimeException(
                'TOTAL_RETURN_PRODUCT_UNAVAILABLE: no governed total-return distribution formula is registered; verified distribution/reference terms must not be approximated from price gaps or provider adj_close.'
            );
        }

        $asOfDate = $this->asOfDate($canonicalBars, $context);
        $bars = $productCode === MarketDataScope::RAW_PRODUCT
            ? $canonicalBars
            : $this->structuralAdjustedBars($canonicalBars, $asOfDate, $context);

        $identity = [
            'price_product_code' => $productCode,
            'price_product_version' => $this->identity->productVersion($productCode),
            'analytical_as_of_date' => $asOfDate,
            'factor_set_id' => $productCode === MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT ? $this->positiveIntOrNull($context['factor_set_id'] ?? null) : null,
            'factor_set_hash' => $productCode === MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT ? $this->hashOrNull($context['factor_set_hash'] ?? null) : null,
            'factor_formula_version' => $productCode === MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT ? $this->identity->factorFormulaVersion() : null,
            'formula_version' => $this->stringOrNull($context['formula_version'] ?? null),
            'config_snapshot_id' => $this->positiveIntOrNull($context['config_snapshot_id'] ?? null),
        ];

        if (! empty($context['require_persisted_identity'])) {
            $this->assertCompletePersistedIdentity($identity, $productCode);
        }

        return $identity + [
            'bars' => $bars,
            'content_hash' => $this->contentHash($identity, $bars, $context),
        ];
    }

    private function structuralAdjustedBars(array $bars, string $asOfDate, array $context): array
    {
        $factors = isset($context['price_adjustment_factors']) && is_array($context['price_adjustment_factors'])
            ? $context['price_adjustment_factors'] : [];
        $factors = $this->canonicalFactors($factors, $asOfDate, ! empty($context['require_factor_lineage']));

        foreach ($bars as $index => $bar) {
            $barDate = (string) ($bar['trade_date'] ?? '');
            if (! $this->isIsoDate($barDate)) {
                throw new \RuntimeException('ANALYTICAL_BAR_DATE_INVALID: every analytical input bar needs an ISO trade_date.');
            }
            if ($barDate > $asOfDate) {
                throw new \RuntimeException('ANALYTICAL_BAR_AFTER_AS_OF: analytical input contains future bar data.');
            }

            $priceFactor = 1.0;
            $volumeFactor = 1.0;
            foreach ($factors as $factor) {
                if ($barDate < $factor['ex_date'] && $factor['ex_date'] <= $asOfDate) {
                    $priceFactor *= $factor['price_factor'];
                    if ($factor['volume_factor'] !== null) {
                        $volumeFactor *= $factor['volume_factor'];
                    }
                }
            }

            foreach (['open', 'high', 'low', 'close'] as $field) {
                if (array_key_exists($field, $bars[$index]) && $bars[$index][$field] !== null) {
                    $bars[$index][$field] = (float) $bars[$index][$field] * $priceFactor;
                }
            }
            if (array_key_exists('volume', $bars[$index]) && $bars[$index]['volume'] !== null) {
                $bars[$index]['volume'] = (float) $bars[$index]['volume'] * $volumeFactor;
            }
            // Provider adj_close remains the immutable observation supplied to this in-memory copy.
        }

        return $bars;
    }

    private function canonicalFactors(array $factors, string $asOfDate, bool $requireLineage): array
    {
        $out = [];
        foreach ($factors as $factor) {
            if (! is_array($factor)) {
                throw new \RuntimeException('ANALYTICAL_FACTOR_INVALID: factor rows must be structured arrays.');
            }
            $exDate = (string) ($factor['ex_date'] ?? '');
            if (! $this->isIsoDate($exDate)) {
                throw new \RuntimeException('ANALYTICAL_FACTOR_DATE_INVALID: factor ex_date must be verified ISO date.');
            }
            // Later-known factor rows cannot change admissibility of an earlier analytical as-of.
            if ($exDate > $asOfDate) {
                continue;
            }

            $price = $this->positiveFiniteFactor($factor['price_factor'] ?? null, 'price_factor');
            $volume = array_key_exists('volume_factor', $factor) && $factor['volume_factor'] !== null
                ? $this->positiveFiniteFactor($factor['volume_factor'], 'volume_factor') : null;
            $volumeRequired = ! empty($factor['volume_factor_required']);
            if ($volumeRequired && $volume === null) {
                throw new \RuntimeException('ANALYTICAL_VOLUME_FACTOR_REQUIRED: verified action semantics require an explicit volume factor.');
            }

            $revisionId = $this->positiveIntOrNull($factor['corporate_action_revision_id'] ?? null);
            $revisionRef = trim((string) ($factor['factor_revision_ref'] ?? ''));
            if ($requireLineage) {
                $expected = $revisionId === null ? '' : 'md-corporate-action-revision:'.$revisionId;
                if ($revisionId === null || $revisionRef === '' || $revisionRef !== $expected) {
                    throw new \RuntimeException('ANALYTICAL_FACTOR_LINEAGE_INCOMPLETE: factor revision identity must match its verified corporate-action revision.');
                }
            }

            $out[] = [
                'factor_revision_ref' => $revisionRef,
                'corporate_action_revision_id' => $revisionId,
                'ex_date' => $exDate,
                'price_factor' => $price,
                'volume_factor' => $volume,
                'volume_factor_required' => $volumeRequired,
            ];
        }
        usort($out, function (array $a, array $b) {
            return strcmp($a['ex_date'].'|'.$a['factor_revision_ref'].'|'.($a['corporate_action_revision_id'] ?? ''), $b['ex_date'].'|'.$b['factor_revision_ref'].'|'.($b['corporate_action_revision_id'] ?? ''));
        });
        return $out;
    }

    private function asOfDate(array $bars, array $context): string
    {
        $date = trim((string) ($context['analytical_as_of_date'] ?? $context['requested_date'] ?? ''));
        if ($date === '' && ! empty($bars)) {
            $last = end($bars); $date = (string) ($last['trade_date'] ?? ''); reset($bars);
        }
        if (! $this->isIsoDate($date)) {
            throw new \RuntimeException('ANALYTICAL_AS_OF_DATE_INVALID: analytical product needs an explicit ISO as-of date.');
        }
        return $date;
    }

    private function assertCompletePersistedIdentity(array $identity, string $productCode): void
    {
        if ($productCode === MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT
            && ($identity['factor_set_id'] === null || $identity['factor_set_hash'] === null)) {
            throw new \RuntimeException('ANALYTICAL_FACTOR_SET_IDENTITY_INCOMPLETE: structural product requires factor-set id and hash.');
        }
        if ($identity['formula_version'] === null || $identity['config_snapshot_id'] === null) {
            throw new \RuntimeException('ANALYTICAL_SEMANTIC_IDENTITY_INCOMPLETE: formula version and config snapshot are required.');
        }
    }

    private function contentHash(array $identity, array $bars, array $context): string
    {
        $factorVector = $identity['price_product_code'] === MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT
            ? $this->canonicalFactors((array) ($context['price_adjustment_factors'] ?? []), $identity['analytical_as_of_date'], ! empty($context['require_factor_lineage'])) : [];
        $canonicalBars = [];
        foreach ($bars as $bar) {
            $canonicalBars[] = [
                'trade_date' => (string) ($bar['trade_date'] ?? ''),
                'open' => $this->canonicalDecimal($bar['open'] ?? null),
                'high' => $this->canonicalDecimal($bar['high'] ?? null),
                'low' => $this->canonicalDecimal($bar['low'] ?? null),
                'close' => $this->canonicalDecimal($bar['close'] ?? null),
                'adj_close' => $this->canonicalDecimal($bar['adj_close'] ?? null),
                'volume' => $this->canonicalDecimal($bar['volume'] ?? null),
            ];
        }
        return hash('sha256', json_encode(['schema'=>'analytical-price-product/v1','identity'=>$identity,'factor_vector'=>$factorVector,'bars'=>$canonicalBars], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function positiveFiniteFactor($value, string $field): float
    {
        if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value <= 0) {
            throw new \RuntimeException('ANALYTICAL_FACTOR_INVALID: '.$field.' must be finite and positive.');
        }
        return (float) $value;
    }
    private function isIsoDate(string $value): bool { $d=\DateTime::createFromFormat('!Y-m-d',$value); return $d && $d->format('Y-m-d')===$value; }
    private function positiveIntOrNull($value) { return is_numeric($value) && (int)$value>0 ? (int)$value : null; }
    private function hashOrNull($value) { $v=strtolower(trim((string)$value)); return preg_match('/^[a-f0-9]{64}$/',$v) ? $v : null; }
    private function stringOrNull($value) { $v=trim((string)$value); return $v==='' ? null : $v; }
    private function canonicalDecimal($value) { return $value===null ? null : number_format((float)$value,12,'.',''); }
}
