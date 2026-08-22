<?php

namespace App\Infrastructure\MarketData\Config;

/**
 * Runtime admission check for the strategy-owned resolved-key register.
 *
 * The Markdown registry remains the single authoritative key list. This parser deliberately fails
 * closed when that authority is unavailable, malformed, or out of sync with resolved runtime
 * configuration; it does not carry a second hand-maintained list.
 */
final class PlatformConfigRegistry
{
    private $registryPath;

    public function __construct($registryPath = null)
    {
        $this->registryPath = $registryPath ?: base_path(
            'docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md'
        );
    }

    public function assertResolvedConfiguration(array $marketDataConfig): void
    {
        $definitions = $this->definitions();
        $resolved = $this->flatten($marketDataConfig, 'market_data');

        $registeredKeys = array_keys($definitions);
        $resolvedKeys = array_keys($resolved);
        sort($registeredKeys, SORT_STRING);
        sort($resolvedKeys, SORT_STRING);

        $missing = array_values(array_diff($registeredKeys, $resolvedKeys));
        $unregistered = array_values(array_diff($resolvedKeys, $registeredKeys));
        if ($missing !== [] || $unregistered !== []) {
            throw new \RuntimeException(
                'CONFIG_REGISTRY_KEY_MISMATCH: missing='.implode(',', $missing)
                .'; unregistered='.implode(',', $unregistered)
            );
        }

        foreach ($definitions as $key => $definition) {
            if (! $this->matchesType($resolved[$key], $definition['type'])) {
                throw new \RuntimeException(
                    'CONFIG_REGISTRY_TYPE_MISMATCH: '.$key.' expected='.$definition['type']
                    .' actual='.gettype($resolved[$key])
                );
            }
        }

        $this->assertHashNullTokenBinding($definitions, $resolved);
    }

    /** @return array<string,array{type:string,default?:string,environment_input?:string,owner_contract?:string}> */
    public function definitions(): array
    {
        $source = @file_get_contents($this->registryPath);
        if ($source === false) {
            throw new \RuntimeException('CONFIG_REGISTRY_UNAVAILABLE: '.$this->registryPath);
        }

        $definitions = [];
        foreach (preg_split('/\R/', $source) as $line) {
            if (! preg_match('/^\| `(?<key>market_data\.[^`]+)` \| (?<type>[^|]+) \|/', $line, $match)) {
                continue;
            }

            $key = trim($match['key']);
            $type = strtolower(trim($match['type']));
            if (isset($definitions[$key])) {
                throw new \RuntimeException('CONFIG_REGISTRY_DUPLICATE_KEY: '.$key);
            }
            if (! in_array($type, ['bool', 'float', 'int', 'list', 'null', 'string'], true)) {
                throw new \RuntimeException('CONFIG_REGISTRY_UNKNOWN_TYPE: '.$key.'='.$type);
            }
            $definitions[$key] = ['type' => $type];
        }

        $nullTokenKey = 'market_data.hash.null_token';
        if (! preg_match(
            '/^\| `market_data\.hash\.null_token` \| (?<type>[^|]+) \| (?<default>[^|]*) \| (?<environment>[^|]*) \| (?<owner>[^|]*) \|$/mu',
            $source,
            $nullToken
        )) {
            throw new \RuntimeException('CONFIG_REGISTRY_HASH_NULL_TOKEN_ROW_MALFORMED');
        }
        $definitions[$nullTokenKey] = [
            'type' => strtolower(trim($nullToken['type'])),
            'default' => trim($nullToken['default']),
            'environment_input' => trim($nullToken['environment']),
            'owner_contract' => trim($nullToken['owner'], " `\t"),
        ];

        if (count($definitions) < 100) {
            throw new \RuntimeException(
                'CONFIG_REGISTRY_MALFORMED: expected a non-vacuous resolved-key register, got '
                .count($definitions).' keys.'
            );
        }

        ksort($definitions, SORT_STRING);

        return $definitions;
    }

    /**
     * The resolved-key register delegates value semantics to Audit_Hash. Its corrected metadata is
     * nevertheless executable: the resolver must expose the owner's zero-byte token and cannot
     * expose an environment input capable of selecting different canonical bytes.
     */
    private function assertHashNullTokenBinding(array $definitions, array $resolved): void
    {
        $key = 'market_data.hash.null_token';
        $definition = $definitions[$key];
        if (($definition['default'] ?? null) !== 'empty string (zero bytes)'
            || ($definition['environment_input'] ?? null) !== '—'
            || ($definition['owner_contract'] ?? null) !== '../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md') {
            throw new \RuntimeException('CONFIG_REGISTRY_HASH_NULL_TOKEN_AUTHORITY_MISMATCH');
        }
        if ($resolved[$key] !== '') {
            throw new \RuntimeException(
                'CONFIG_HASH_NULL_TOKEN_NOT_EMPTY: actual='.json_encode($resolved[$key])
            );
        }
    }

    /** @return array<string,mixed> */
    private function flatten(array $value, string $prefix): array
    {
        $flat = [];
        foreach ($value as $key => $child) {
            $path = $prefix.'.'.$key;
            if (is_array($child) && $child !== []) {
                $flat += $this->flatten($child, $path);
            } else {
                // An empty list is a resolved typed value, not a missing subtree.
                $flat[$path] = $child;
            }
        }

        return $flat;
    }

    private function matchesType($value, string $type): bool
    {
        if ($type === 'bool') {
            return is_bool($value);
        }
        if ($type === 'float') {
            return is_float($value) || is_int($value);
        }
        if ($type === 'int') {
            return is_int($value);
        }
        if ($type === 'list') {
            return is_array($value) && ($value === [] || array_keys($value) === range(0, count($value) - 1));
        }
        if ($type === 'null') {
            return $value === null;
        }

        return is_string($value);
    }
}
