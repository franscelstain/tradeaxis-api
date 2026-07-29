<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01DraftCatalogService;
use Illuminate\Console\Command;

class PersistWeeklySwingPriceQualityP01DraftCatalogCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-price-quality-p01-persist-draft-catalog
        {--diagnostic=storage/app/watchlist/backtest/ws-price-quality-p01-diagnostic.json : Exact P01 diagnostic artifact}
        {--approval-reference= : Exact P01 DRAFT catalog approval reference}
        {--operator-approved : Confirm only two authorized DRAFTs; no OOS, promotion, PLAN, or production}
        {--output-dir=storage/app/watchlist/backtest/ws-price-quality-p01-draft-catalog : Candidate JSON directory}
        {--output=storage/app/watchlist/backtest/ws-price-quality-p01-draft-catalog.json : Catalog artifact}
        {--overwrite : Replace differing local artifacts; persisted identities remain immutable}';

    protected $description = 'Persist exactly two diagnostic-authorized P01 price-floor DRAFT candidates; Official IS and OOS are not run.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingPriceQualityP01DraftCatalogService::class)
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
            'catalog_row_count', 'official_is_runtime_invoked', 'oos_runtime_invoked',
            'oos_table_read', 'production_ready', 'next_recommendation', 'artifact_hash',
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
        foreach (($result['drafts'] ?? []) as $draft) {
            $this->line('draft='.(string) $draft['row_code']
                .'|min_signal_close_price='
                .(string) $draft['minimum_signal_close_price']
                .'|param_set_id='.(string) $draft['param_set_id']
                .'|bt_param_id='.(string) $draft['bt_param_id']
                .'|params_hash='.(string) $draft['params_hash']
                .'|persistence_status='.(string) $draft['persistence_status']);
        }

        return ($result['status'] ?? '')
            === 'WS_PRICE_QUALITY_P01_TWO_MINIMAL_DRAFTS_PERSISTED'
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
