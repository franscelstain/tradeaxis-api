<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Domain\MarketData\MarketDataSemanticBindings;
use Illuminate\Support\Facades\DB;

/** Actual registry content and reproducible source artifact, not an operator-supplied build label. */
final class ProducerRegistrySnapshot
{
    private static $build;
    public function capture($run, RunInputCaptureRepository $captures): array
    {
        $historical = (string) ($run->request_mode ?? '') === 'replay_verify';
        $reasons = $historical ? null : DB::table('eod_reason_codes')->orderBy('code')->get()->all();
        $entries = $reasons === null ? null : array_map(static function ($row) {
            return ['code' => (string) $row->code, 'category' => (string) $row->category,
                'description' => (string) $row->description, 'severity' => (string) $row->severity,
                'is_active' => (bool) $row->is_active];
        }, $reasons);
        $audit = $reasons === null ? [] : array_map(static function ($row) {
            return ['code' => $row->code, 'created_at' => $row->created_at, 'updated_at' => $row->updated_at];
        }, $reasons);
        $build = $this->build();
        $payload = [
            'registry_contract' => 'producer_registry_content_v1',
            'reason_registry_source' => 'eod_reason_codes', 'reason_entries' => $entries,
            'reason_registry_hash' => $entries === null ? null : hash('sha256', RunInputCaptureRepository::canonicalJson($entries)),
            'missing_paths' => $historical ? ['registry_versions.reason_registry.authoritative_known_at_cutoff']
                : ($entries === [] ? ['registry_versions.reason_registry.entries'] : []),
            'semantic_versions' => MarketDataSemanticBindings::snapshot(),
            'indicator_set_version' => (string) config('market_data.indicators.set_version'),
            'coverage_contract_version' => (string) config('market_data.coverage_gate.contract_version'),
            // F-MD-B18-A002-021 owner decision (Option B, config-driven, symmetric with
            // coverage_contract_version above): the eligibility decision contract's own version,
            // distinct from coverage -- EOD_Eligibility_Snapshot_Contract_LOCKED.md governs a
            // broader upstream data-usability decision that coverage is only one input to.
            'eligibility_contract_version' => (string) config('market_data.eligibility.contract_version'),
            'config_registry_revision' => (string) config('market_data.governance.config_registry_revision'),
            'config_resolver_version' => (string) config('market_data.governance.config_resolver_version'),
            'serialization_version' => (string) config('market_data.governance.config_serialization_version'),
            // F-MD-B18-A002-021 (last remaining item, partial): the one already-established,
            // already-reused read-model identity in this codebase -- MarketDataReadProductService
            // and EodPublicationRepository's real (non-default) manifest construction both already
            // set exactly this literal; it is not invented here, only captured for the first time.
            // No config key exists for it (confirmed by review) because it never varied -- like
            // registry_contract above, its exact bytes are the identity.
            'read_model_version' => 'market_data_read_product_v1',
            // These implementations have no separate nominal version; their exact bytes are the identity.
            'implementation_identities' => array_intersect_key($build['files'], array_flip([
                'app/Application/MarketData/Services/IndicatorVectorService.php',
                'app/Application/MarketData/Services/EligibilityDecisionService.php',
                'app/Application/MarketData/Services/CoverageGateEvaluator.php',
                'app/Application/MarketData/Services/DeterministicHashService.php',
                'app/Infrastructure/Persistence/MarketData/MarketDataPriceReadRepository.php',
                'app/Infrastructure/MarketData/Config/PlatformConfigRegistry.php',
            ])),
            'executable_build' => $build,
        ];
        foreach (['indicator_set_version', 'coverage_contract_version', 'eligibility_contract_version', 'config_registry_revision', 'config_resolver_version', 'serialization_version'] as $field) {
            if ($payload[$field] === '') throw new \RuntimeException('INPUT_CAPTURE_REGISTRY_VERSION_MISSING: '.$field);
        }
        if (count($payload['implementation_identities']) !== 6) throw new \RuntimeException('INPUT_CAPTURE_BUILD_COMPONENT_MISSING');
        return $captures->capture((int) $run->run_id, 'RUN_CONTEXT', 'registry_versions', [
            'operation' => 'producer-registry-build/v1', 'knowledge_cutoff_at' => (string) $run->knowledge_cutoff_at,
        ], [$payload], null, ['reason_registry_audit' => $audit]);
    }

    public function build(): array
    {
        if (function_exists('opcache_get_status') && ($status = opcache_get_status(false)) && ! empty($status['opcache_enabled'])) {
            throw new \RuntimeException('INPUT_CAPTURE_BUILD_OPCACHE_UNVERIFIED: source bytes alone cannot attest cached executable bytecode');
        }
        if (self::$build !== null) {
            // Loaded code is fixed for this PHP process. Verify each consumed source and the
            // retained archive on every scope boundary, including newly autoloaded dependencies.
            foreach (get_included_files() as $path) {
                if (strpos(str_replace('\\', '/', $path), str_replace('\\', '/', base_path()).'/') !== 0 || ! is_file($path)) continue;
                $relative = str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
                if (! preg_match('#^(?:(?:app|config|vendor)/|bootstrap/(?!cache/))#', $relative)) continue;
                if (! isset(self::$build['files'][$relative]) || hash_file('sha256', $path) !== self::$build['files'][$relative]) {
                    throw new \RuntimeException('INPUT_CAPTURE_BUILD_EXECUTABLE_DRIFT: '.$relative);
                }
            }
            if (hash_file('sha256', base_path(self::$build['artifact_path'])) !== self::$build['artifact_hash']) {
                throw new \RuntimeException('INPUT_CAPTURE_BUILD_ARTIFACT_CONFLICT');
            }
            return self::$build;
        }
        $files = [];
        foreach (['app', 'config', 'bootstrap', 'vendor'] as $root) {
            $count = 0;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($root), \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (! $file->isFile() || ($root !== 'vendor' && $file->getExtension() !== 'php')) continue;
                // Framework-generated caches describe an invocation, not executable source.
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(base_path()) + 1));
                if (strpos($relative, 'bootstrap/cache/') === 0) continue;
                $files[$relative] = base64_encode(file_get_contents($file->getPathname())); $count++;
            }
            if ($count === 0) throw new \RuntimeException('INPUT_CAPTURE_BUILD_EMPTY: '.$root);
        }
        foreach (['composer.json', 'composer.lock'] as $file) {
            if (! is_file(base_path($file))) throw new \RuntimeException('INPUT_CAPTURE_BUILD_FILE_MISSING: '.$file);
            $files[$file] = base64_encode(file_get_contents(base_path($file)));
        }
        ksort($files, SORT_STRING);
        $hashes = array_map(static function ($bytes) { return hash('sha256', base64_decode($bytes, true)); }, $files);
        $content = RunInputCaptureRepository::canonicalJson(['schema_version' => 'php_source_build_v1', 'files_base64' => $files]);
        $identity = hash('sha256', $content);
        $relative = 'app/market-data/input-builds/'.$identity.'.json.gz';
        $path = storage_path($relative);
        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0775, true) && ! is_dir(dirname($path))) {
            throw new \RuntimeException('INPUT_CAPTURE_BUILD_STORAGE_UNAVAILABLE');
        }
        if (! is_file($path)) {
            $handle = @fopen($path, 'x+b');
            if ($handle !== false) {
                try {
                    $compressed = gzencode($content, 6);
                    if (fwrite($handle, $compressed) !== strlen($compressed)) throw new \RuntimeException('INPUT_CAPTURE_BUILD_WRITE_FAILED');
                    fflush($handle);
                } finally { fclose($handle); }
            }
        }
        $compressed = file_get_contents($path);
        if ($compressed === false || gzdecode($compressed) !== $content) throw new \RuntimeException('INPUT_CAPTURE_BUILD_ARTIFACT_CONFLICT');
        return self::$build = ['schema_version' => 'php_source_build_v1', 'build_id' => 'sha256:'.$identity,
            'content_hash' => $identity, 'artifact_path' => 'storage/'.$relative,
            'artifact_hash' => hash('sha256', $compressed), 'files' => $hashes,
            'php_version' => PHP_VERSION, 'php_sapi' => PHP_SAPI];
    }
}
