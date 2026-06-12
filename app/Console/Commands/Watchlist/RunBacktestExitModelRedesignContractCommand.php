<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestExitModelRedesignContractService;
use Illuminate\Console\Command;

class RunBacktestExitModelRedesignContractCommand extends Command
{
    protected $signature = 'watchlist:backtest-exit-model-redesign-contract
        {--c11-artifact= : Explicit C11 exit-model contract audit JSON artifact path}
        {--output= : Explicit C12 redesign-contract JSON artifact path}
        {--overwrite : Explicitly replace an existing output file}';

    protected $description = 'Produce a C12 exit-model redesign contract artifact without catalog creation, OOS read/write, or promotion.';

    public function handle(): int
    {
        $c11Artifact = trim((string) $this->option('c11-artifact'));
        $output = trim((string) $this->option('output'));
        if ($c11Artifact === '' || $output === '') {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit --c11-artifact and --output are required.');
        }

        $c11Path = $this->absolutePath($c11Artifact);
        $outputPath = $this->absolutePath($output);
        try {
            $service = $this->laravel
                ? $this->laravel->make(WatchlistBacktestExitModelRedesignContractService::class)
                : app(WatchlistBacktestExitModelRedesignContractService::class);
            $result = $service->execute($c11Path, $outputPath, [
                'overwrite' => (bool) $this->option('overwrite'),
            ]);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }

        if (! ($result['is_ready'] ?? false)) {
            return $this->blocked(
                (string) ($result['reason_code'] ?? 'WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_BLOCKED'),
                (string) ($result['message'] ?? 'Exit-model redesign contract failed.')
            );
        }

        $this->render($result, $c11Path, $outputPath);

        return 0;
    }

    private function render(array $result, string $c11Path, string $outputPath): void
    {
        $artifact = is_array($result['artifact'] ?? null) ? $result['artifact'] : [];
        $catalog = is_array($artifact['source_catalog'] ?? null) ? $artifact['source_catalog'] : [];
        $decision = is_array($artifact['decision'] ?? null) ? $artifact['decision'] : [];

        $this->line('status=PASS');
        $this->line('reason_code='.(string) ($result['reason_code'] ?? 'WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY'));
        $this->line('catalog_code='.(string) ($catalog['catalog_code'] ?? ''));
        $this->line('catalog_version='.(string) ($catalog['catalog_version'] ?? ''));
        $this->line('catalog_count='.(string) ($catalog['catalog_count'] ?? ''));
        $this->line('catalog_hash='.(string) ($catalog['catalog_hash'] ?? ''));
        $this->line('source_c11_artifact_path='.$c11Path);
        $this->line('source_c11_artifact_hash='.(string) ($artifact['meta']['source_c11_artifact_hash'] ?? ''));
        $this->line('design_contract_ready='.(! empty($decision['design_contract_ready']) ? '1' : '0'));
        $this->line('catalog_creation_authorized='.(! empty($decision['catalog_creation_authorized']) ? '1' : '0'));
        $this->line('exit_model_catalog_authorized='.(! empty($decision['exit_model_catalog_authorized']) ? '1' : '0'));
        $this->line('next_required_step='.(string) ($decision['next_required_step'] ?? ''));
        $this->line('strategy_catalog_created=0');
        $this->line('oos_service_invoked=0');
        $this->line('oos_repository_invoked=0');
        $this->line('oos_executed=0');
        $this->line('artifact_hash='.(string) ($result['artifact_hash'] ?? ''));
        $this->line('artifact_path='.(string) ($result['write']['path'] ?? $outputPath));
        $this->line('production_ready=0');
    }

    private function blocked(string $reasonCode, string $message): int
    {
        $this->error('status=BLOCKED');
        $this->line('reason_code='.$reasonCode);
        $this->line('error='.$message);
        $this->line('strategy_catalog_created=0');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 1;
    }

    private function absolutePath(string $path): string
    {
        $isUnixAbsolute = substr($path, 0, 1) === '/';
        $isUncAbsolute = substr($path, 0, 2) === '\\\\';
        $isWindowsAbsolute = strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && in_array($path[2], ['\\', '/'], true);

        if ($isUnixAbsolute || $isUncAbsolute || $isWindowsAbsolute) {
            return $path;
        }

        return base_path($path);
    }

    private function reasonCode(\Throwable $e): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_FAILED';
    }
}
