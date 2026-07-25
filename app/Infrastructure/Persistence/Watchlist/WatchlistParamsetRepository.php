<?php

namespace App\Infrastructure\Persistence\Watchlist;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WatchlistParamsetRepository
{
    public function persistDraft(array $payload, array $provenance): array
    {
        if (! Schema::hasTable('watchlist_param_sets')) {
            throw new \RuntimeException('WS_PARAMSET_RUNTIME_SCHEMA_MISSING: watchlist_param_sets is not available.');
        }

        $paramsJson = $this->canonicalJson($payload);
        $hashContractJson = $this->canonicalJson($payload['hash_contract'] ?? []);
        $provenanceJson = $this->canonicalJson($provenance);

        $existing = DB::table('watchlist_param_sets')
            ->where('policy_code', (string) $payload['policy_code'])
            ->where('policy_version', (string) $payload['policy_version'])
            ->where('schema_version', (string) $payload['schema_version'])
            ->where('params_json', $paramsJson)
            ->first();
        if ($existing) {
            return [
                'status' => 'IDEMPOTENT',
                'param_set_id' => (int) $existing->param_set_id,
                'paramset_status' => (string) $existing->status,
                'params_hash' => sha1($paramsJson),
            ];
        }

        $now = date('Y-m-d H:i:s');
        $id = (int) DB::table('watchlist_param_sets')->insertGetId([
            'policy_code' => (string) $payload['policy_code'],
            'policy_version' => (string) $payload['policy_version'],
            'schema_version' => (string) $payload['schema_version'],
            'hash_contract' => $hashContractJson,
            'provenance_json' => $provenanceJson,
            'status' => 'DRAFT',
            'params_json' => $paramsJson,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'status' => 'INSERTED',
            'param_set_id' => $id,
            'paramset_status' => 'DRAFT',
            'params_hash' => sha1($paramsJson),
        ];
    }

    private function canonicalJson(array $payload): string
    {
        return json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES);
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalize($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
