<?php

namespace App\Infrastructure\Persistence\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WatchlistBacktestStrategyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WatchlistParamsetRepository
{
    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;

    public function __construct(
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null
    ) {
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->runtimeAdapter = $runtimeAdapter ?: new WeeklySwingParamsetRuntimeAdapter();
    }

    public function persistDraft(array $payload, array $provenance): array
    {
        if (! Schema::hasTable('watchlist_param_sets')) {
            throw new \RuntimeException('WS_PARAMSET_RUNTIME_SCHEMA_MISSING: watchlist_param_sets is not available.');
        }
        foreach (['params_hash','eval_model','eval_model_hash','implementation_version','implementation_hash'] as $column) {
            if (! Schema::hasColumn('watchlist_param_sets', $column)) {
                throw new \RuntimeException('WS_C171_PARAMSET_IDENTITY_SCHEMA_MISSING: watchlist_param_sets.'.$column);
            }
        }

        $paramsJson = $this->identity->canonicalJson($payload);
        $paramsHash = sha1($paramsJson);
        $hashContractJson = $this->identity->canonicalJson($payload['hash_contract'] ?? []);
        $provenanceJson = $this->identity->canonicalJson($provenance);
        $runtimeParamset = $this->runtimeAdapter->adapt($payload);
        $evalModel = WatchlistBacktestStrategyService::canonicalEvalModel($runtimeParamset);
        $identity = $this->identity->identity($payload, $evalModel);

        return DB::transaction(function () use (
            $payload, $paramsJson, $paramsHash, $hashContractJson, $provenanceJson, $identity
        ): array {
            $identityQuery = function () use ($payload, $paramsHash) {
                return DB::table('watchlist_param_sets')
                    ->where('policy_code', (string) $payload['policy_code'])
                    ->where('policy_version', (string) $payload['policy_version'])
                    ->where('schema_version', (string) $payload['schema_version'])
                    ->where('params_hash', $paramsHash);
            };
            $existing = $identityQuery()->lockForUpdate()->first();
            if ($existing) {
                return $this->existingIdentityResult($existing, $paramsJson, $hashContractJson, $provenanceJson, $identity, $paramsHash);
            }

            $now = date('Y-m-d H:i:s');
            $inserted = DB::table('watchlist_param_sets')->insertOrIgnore([
                'policy_code' => (string) $payload['policy_code'],
                'policy_version' => (string) $payload['policy_version'],
                'schema_version' => (string) $payload['schema_version'],
                'hash_contract' => $hashContractJson,
                'provenance_json' => $provenanceJson,
                'status' => 'DRAFT',
                'params_json' => $paramsJson,
                'params_hash' => $paramsHash,
                'eval_model' => $identity['eval_model'],
                'eval_model_hash' => $identity['eval_model_hash'],
                'implementation_version' => $identity['implementation_version'],
                'implementation_hash' => $identity['implementation_hash'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $persisted = $identityQuery()->lockForUpdate()->first();
            if (! $persisted) {
                throw new \RuntimeException('WS_C171_PARAMSET_DRAFT_WRITE_FAILED: unique identity was not persisted.');
            }
            $result = $this->existingIdentityResult($persisted, $paramsJson, $hashContractJson, $provenanceJson, $identity, $paramsHash);
            $result['status'] = $inserted === 1 ? 'INSERTED' : 'IDEMPOTENT';

            return $result;
        });
    }

    private function existingIdentityResult(
        $existing,
        string $paramsJson,
        string $hashContractJson,
        string $provenanceJson,
        array $identity,
        string $paramsHash
    ): array {
        if ((string) $existing->params_json !== $paramsJson
            || (string) $existing->hash_contract !== $hashContractJson
            || (string) $existing->provenance_json !== $provenanceJson
            || (string) $existing->eval_model !== (string) $identity['eval_model']
            || (string) $existing->eval_model_hash !== (string) $identity['eval_model_hash']
            || (string) $existing->implementation_version !== (string) $identity['implementation_version']
            || (string) $existing->implementation_hash !== (string) $identity['implementation_hash']) {
            throw new \RuntimeException('WS_C171_PARAMSET_IDENTITY_CONFLICT: params_hash exists with different immutable payload, provenance, or execution identity.');
        }

        return [
            'status' => 'IDEMPOTENT',
            'param_set_id' => (int) $existing->param_set_id,
            'paramset_status' => (string) $existing->status,
            'params_hash' => $paramsHash,
            'eval_model_hash' => (string) $existing->eval_model_hash,
            'implementation_hash' => (string) $existing->implementation_hash,
        ];
    }

}
