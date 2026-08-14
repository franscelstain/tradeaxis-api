<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01DiagnosticService;
use Illuminate\Console\Command;

class RunWeeklySwingBreakoutIntegrityB01DiagnosticCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-breakout-integrity-b01-diagnostic
        {--source-artifact=storage/app/watchlist/backtest/ws-price-quality-p01-c1-official-is.json : Exact immutable P01 C1 source artifact}
        {--approval-reference= : Exact B01 read-only diagnostic approval reference}
        {--operator-approved : Confirm IS-only read-only diagnosis; no OOS or production action}
        {--output=storage/app/watchlist/backtest/ws-breakout-integrity-b01-diagnostic.json : B01 diagnostic artifact}
        {--overwrite : Replace a differing local B01 artifact}';

    protected $description = 'Evaluate three preregistered breakout-integrity rules from immutable eval 216 IS evidence; OOS is never read.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingBreakoutIntegrityB01DiagnosticService::class)
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
            === WeeklySwingBreakoutIntegrityB01DiagnosticService::SUCCESS_STATUS
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
