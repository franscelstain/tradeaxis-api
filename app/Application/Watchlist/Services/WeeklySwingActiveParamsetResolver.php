<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingActiveParamsetResolver
{
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;
    private WeeklySwingBacktestEvidenceIdentityService $identity;

    public function __construct(
        ?WeeklySwingParamsetValidator $validator = null,
        ?WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null,
        ?WeeklySwingBacktestEvidenceIdentityService $identity = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->runtimeAdapter = $runtimeAdapter ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
    }

    public function resolve(string $policyCode = 'WS'): array
    {
        if (! Schema::hasTable('watchlist_param_sets')) {
            return $this->failure(
                'WS_ACTIVE_PARAMSET_SCHEMA_MISSING',
                'watchlist_param_sets is not available.'
            );
        }

        $rows = DB::table('watchlist_param_sets')
            ->where('policy_code', $policyCode)
            ->where('status', 'ACTIVE')
            ->orderBy('param_set_id')
            ->get();

        if ($rows->count() !== 1) {
            return $this->failure(
                'WS_ACTIVE_PARAMSET_CARDINALITY_INVALID',
                'Exactly one ACTIVE '.$policyCode.' paramset is required; found '.$rows->count().'.'
            );
        }

        $row = $rows->first();
        $payload = json_decode((string) $row->params_json, true);
        if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            return $this->failure(
                'WS_ACTIVE_PARAMSET_JSON_INVALID',
                'The ACTIVE paramset params_json is not a valid JSON object.'
            );
        }

        $validation = $this->validator->validate($payload);
        if (($validation['valid'] ?? false) !== true) {
            return $this->failure(
                'WS_ACTIVE_PARAMSET_CONTRACT_INVALID',
                'The ACTIVE paramset does not satisfy the canonical Watchlist contract.',
                ['validation' => $validation]
            );
        }

        $canonicalPayload = $validation['canonical_payload'];
        $canonicalHash = $this->identity->stableHash($canonicalPayload);
        if (Schema::hasColumn('watchlist_param_sets', 'params_hash')
            && trim((string) ($row->params_hash ?? '')) !== ''
            && (string) $row->params_hash !== $canonicalHash) {
            return $this->failure(
                'WS_ACTIVE_PARAMSET_HASH_MISMATCH',
                'The ACTIVE paramset hash does not match its canonical payload.'
            );
        }

        try {
            $runtimeParamset = $this->runtimeAdapter->adapt($canonicalPayload);
        } catch (\Throwable $exception) {
            return $this->failure(
                'WS_ACTIVE_PARAMSET_RUNTIME_ADAPTATION_FAILED',
                $exception->getMessage()
            );
        }

        $paramSetId = (int) $row->param_set_id;

        return [
            'valid' => true,
            'reason_code' => 'WS_ACTIVE_PARAMSET_RESOLVED',
            'message' => null,
            'param_set_id' => $paramSetId,
            'canonical_hash' => $canonicalHash,
            'paramset' => $runtimeParamset,
            'paramset_source' => 'watchlist_param_sets:'.$paramSetId.':ACTIVE',
        ];
    }

    private function failure(string $reasonCode, string $message, array $context = []): array
    {
        return array_merge([
            'valid' => false,
            'reason_code' => $reasonCode,
            'message' => $message,
            'param_set_id' => null,
            'canonical_hash' => null,
            'paramset' => [],
            'paramset_source' => null,
        ], $context);
    }
}
