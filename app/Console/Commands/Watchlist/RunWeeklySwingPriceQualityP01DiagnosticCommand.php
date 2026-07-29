<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01DiagnosticService;
use Illuminate\Console\Command;

class RunWeeklySwingPriceQualityP01DiagnosticCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-price-quality-p01-diagnostic
        {--source-artifact=storage/app/watchlist/backtest/ws-tail-risk-s01-official-is-paramset-20.json : Exact immutable S01 H1 source artifact}
        {--approval-reference= : Exact P01 read-only diagnostic approval reference}
        {--operator-approved : Confirm IS-only read-only diagnosis; no OOS or production action}
        {--output=storage/app/watchlist/backtest/ws-price-quality-p01-diagnostic.json : P01 diagnostic artifact}
        {--overwrite : Replace a differing local P01 artifact}';

    protected $description = 'Evaluate preregistered exact signal-price quality floors from immutable eval 212 IS evidence; OOS is never read.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingPriceQualityP01DiagnosticService::class)
            ->execute(
                $this->absolutePath(trim((string) $this->option('source-artifact'))),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'source_eval_id',
            'candidate_design_allowed_count', 'official_is_runtime_invoked',
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

        return ($result['status'] ?? '')
            === WeeklySwingPriceQualityP01DiagnosticService::SUCCESS_STATUS
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
