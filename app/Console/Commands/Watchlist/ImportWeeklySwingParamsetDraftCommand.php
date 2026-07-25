<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingParamsetDraftImportService;
use Illuminate\Console\Command;

class ImportWeeklySwingParamsetDraftCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-paramset-import-draft
        {--input= : Paramset JSON path}
        {--bt-param-id= : Exact watchlist_bt_param_grid.param_id binding}
        {--catalog-code= : Exact immutable backtest catalog code}
        {--source-note= : Optional operator provenance note}';

    protected $description = 'Validate and persist a Weekly Swing paramset as DRAFT with an exact backtest-grid binding; never promotes it.';

    public function handle(): int
    {
        $input = trim((string) $this->option('input'));
        $path = $this->absolutePath($input);
        $btParamId = filter_var($this->option('bt-param-id'), FILTER_VALIDATE_INT);
        $catalogCode = trim((string) $this->option('catalog-code'));
        if ($input === '' || ! is_file($path) || $btParamId === false || $btParamId < 1 || $catalogCode === '') {
            return $this->blocked('WS_PARAMSET_IMPORT_ARGUMENT_INVALID');
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return $this->blocked('WS_PARAMSET_IMPORT_JSON_INVALID');
        }

        $service = $this->laravel->make(WeeklySwingParamsetDraftImportService::class);
        $result = $service->execute($payload, (int) $btParamId, $catalogCode, [
            'source_path' => $input,
            'source_file_sha1' => sha1_file($path),
            'source_note' => trim((string) $this->option('source-note')),
        ]);
        $this->render($result);

        return ($result['status'] ?? null) === 'DRAFT_PERSISTED' ? 0 : 1;
    }

    private function render(array $result): void
    {
        foreach (['status', 'reason_code', 'param_set_id', 'paramset_status', 'production_ready'] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }
        $binding = $result['binding'] ?? [];
        foreach (['bt_param_id', 'catalog_code', 'catalog_version', 'catalog_hash', 'row_code', 'row_hash'] as $key) {
            if (array_key_exists($key, $binding)) {
                $this->line($key.'='.(string) $binding[$key]);
            }
        }
    }

    private function blocked(string $reasonCode): int
    {
        $this->error('status=BLOCKED');
        $this->line('reason_code='.$reasonCode);
        $this->line('production_ready=0');
        return 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (
            substr($path, 0, 1) === '/' ||
            substr($path, 0, 2) === '\\\\' ||
            (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':')
        )) {
            return $path;
        }

        return base_path($path);
    }
}
