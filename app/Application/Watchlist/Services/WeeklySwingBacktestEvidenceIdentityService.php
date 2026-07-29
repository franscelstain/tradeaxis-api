<?php

namespace App\Application\Watchlist\Services;

class WeeklySwingBacktestEvidenceIdentityService
{
    public const IMPLEMENTATION_VERSION = 'WS_CANONICAL_IS_C171_V1';
    public const IMPLEMENTATION_CONTRACT = 'PLAN_RECOMMENDATION_CONFIRM_REPLAY|PUBLISHED_EOD|NO_FUTURE_ROUTING';
    public const LEGACY_EVIDENCE_PIPELINE_VERSION = 'WS_C171_OFFICIAL_EVIDENCE_PIPELINE_V1';
    public const LEGACY_EVIDENCE_PIPELINE_CONTRACT = 'OFFICIAL_EVIDENCE_V1|PRE_TICK_RISK_PROPAGATION_AUDIT';
    public const LEGACY_EVIDENCE_PIPELINE_HASH = '331906bb7cd0cdb3586ff3493f14217d58abacfe';
    public const PREVIOUS_EVIDENCE_PIPELINE_VERSION = 'WS_C171_C01_TICK_RISK_EVIDENCE_PIPELINE_V2';
    public const PREVIOUS_EVIDENCE_PIPELINE_CONTRACT = 'OFFICIAL_EVIDENCE_V1|SCORED_TICK_RISK_PROPAGATED|ELIGIBLE_THRESHOLD_FAIL_CLOSED|FULL_REASON_AUDIT';
    public const PREVIOUS_EVIDENCE_PIPELINE_HASH = '53857a635f6662542f0dc80f08051bed25a7afb8';
    public const EVIDENCE_PIPELINE_VERSION = 'WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3';
    public const EVIDENCE_PIPELINE_CONTRACT = 'OFFICIAL_EVIDENCE_V1|SCORING_ADAPTER_PRESERVES_UNIVERSE_GUARDS|SCORED_TICK_RISK_PROPAGATED|ELIGIBLE_THRESHOLD_FAIL_CLOSED|FULL_REASON_AUDIT';

    public function identity(array $canonicalParamset, string $evalModel): array
    {
        $paramsHash = $this->stableHash($canonicalParamset);

        return [
            'paramset_hash' => $paramsHash,
            'eval_model' => $evalModel,
            'eval_model_hash' => sha1($evalModel),
            'implementation_version' => self::IMPLEMENTATION_VERSION,
            'implementation_hash' => self::implementationHash(),
            'evidence_pipeline_version' => self::EVIDENCE_PIPELINE_VERSION,
            'evidence_pipeline_hash' => self::evidencePipelineHash(),
        ];
    }

    public static function implementationHash(): string
    {
        return sha1(self::IMPLEMENTATION_VERSION.'|'.self::IMPLEMENTATION_CONTRACT);
    }

    public static function evidencePipelineHash(): string
    {
        return sha1(self::EVIDENCE_PIPELINE_VERSION.'|'.self::EVIDENCE_PIPELINE_CONTRACT);
    }

    public function stableHash($value): string
    {
        return sha1($this->canonicalJson($value));
    }

    public function canonicalJson($value): string
    {
        $json = json_encode($this->normalize($value), JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('WS_C171_CANONICAL_JSON_ENCODING_FAILED: '.json_last_error_msg());
        }

        return $json;
    }

    /**
     * Produce the same SHA1 as stableHash(array_values($rows)) without ever
     * materializing the complete normalized array or JSON string in memory.
     */
    public function stableListHash(iterable $rows): string
    {
        $context = hash_init('sha1');
        hash_update($context, '[');
        $first = true;
        foreach ($rows as $row) {
            if (! $first) {
                hash_update($context, ',');
            }
            hash_update($context, $this->canonicalJson($row));
            $first = false;
        }
        hash_update($context, ']');

        return hash_final($context);
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) { return $this->normalize($item); }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
    }
}
