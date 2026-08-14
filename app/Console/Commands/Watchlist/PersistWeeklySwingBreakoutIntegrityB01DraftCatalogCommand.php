<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01DraftCatalogService;
use Illuminate\Console\Command;

class PersistWeeklySwingBreakoutIntegrityB01DraftCatalogCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-breakout-integrity-b01-persist-draft-catalog
        {--diagnostic=storage/app/watchlist/backtest/ws-breakout-integrity-b01-diagnostic.json : Exact B01 diagnostic artifact}
        {--approval-reference= : Exact B01 DRAFT catalog approval reference}
        {--operator-approved : Confirm only one authorized DRAFT; no OOS, promotion, PLAN, or production}
        {--output-dir=storage/app/watchlist/backtest/ws-breakout-integrity-b01-draft-catalog : Candidate JSON directory}
        {--output=storage/app/watchlist/backtest/ws-breakout-integrity-b01-draft-catalog.json : Catalog artifact}
        {--overwrite : Replace differing local artifacts; persisted identities remain immutable}';

    protected $description = 'Persist exactly one diagnostic-authorized B01 breakout-floor DRAFT; Official IS and OOS are not run.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingBreakoutIntegrityB01DraftCatalogService::class)
            ->execute(
                $this->absolutePath(trim((string) $this->option('diagnostic'))),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('output-dir'))),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'catalog_code', 'catalog_hash',
            'catalog_row_count', 'official_is_runtime_invoked',
            'oos_runtime_invoked', 'oos_table_read', 'production_ready',
            'next_recommendation', 'artifact_hash',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key])
                    ? ($result[$key] ? '1' : '0')
                    : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }
        if (isset($result['error'])) {
            $this->line('error='.(string) $result['error']);
        }
        $draft = $result['draft'] ?? null;
        if (is_array($draft)) {
            $this->line('draft='.(string) $draft['row_code']
                .'|param_set_id='.(string) $draft['param_set_id']
                .'|bt_param_id='.(string) $draft['bt_param_id']
                .'|params_hash='.(string) $draft['params_hash']
                .'|persistence_status='.(string) $draft['persistence_status']);
        }

        return ($result['status'] ?? '')
            === 'WS_BREAKOUT_INTEGRITY_B01_ONE_MINIMAL_DRAFT_PERSISTED'
            ? 0
            : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/'
            || substr($path, 0, 2) === '\\\\'
            || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':'))) {
            return $path;
        }

        return base_path($path);
    }
}
