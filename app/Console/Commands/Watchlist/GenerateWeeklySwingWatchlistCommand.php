<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingWatchlistRuntimeService;
use Illuminate\Console\Command;

class GenerateWeeklySwingWatchlistCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-generate
        {--trade-date= : Explicit Market Data trade date in YYYY-MM-DD format}
        {--output= : Date-specific JSON output path}
        {--paramset= : Optional executable Watchlist paramset JSON path}
        {--capital= : Optional input capital in IDR}
        {--overwrite : Replace an existing output only when explicitly requested}
        {--progress : Print runtime progress}';

    protected $description = 'Run the C168 real published-Market-Data to Weekly Swing stock-ticker Watchlist pipeline.';

    private WeeklySwingWatchlistRuntimeService $service;

    public function __construct(?WeeklySwingWatchlistRuntimeService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WeeklySwingWatchlistRuntimeService();
    }

    public function handle(): int
    {
        $tradeDate = trim((string) $this->option('trade-date'));
        $outputPath = trim((string) $this->option('output'));
        $paramsetPath = trim((string) $this->option('paramset'));
        $capital = trim((string) $this->option('capital'));

        if ($tradeDate === '') {
            $this->error('C168 requires --trade-date=YYYY-MM-DD.');

            return 1;
        }

        $paramset = [];
        $paramsetSource = 'canonical_executable_watchlist_service_defaults';
        if ($paramsetPath !== '') {
            $loaded = $this->loadParamset($paramsetPath);
            if (! $loaded['valid']) {
                $this->error($loaded['message']);

                return 1;
            }
            $paramset = $loaded['payload'];
            $paramsetSource = $loaded['path'];
        }

        $capitalInput = [];
        if ($capital !== '') {
            if (! is_numeric($capital) || (float) $capital < 0) {
                $this->error('C168 --capital must be numeric and non-negative.');

                return 1;
            }
            $capitalInput = ['input_capital' => (float) $capital];
        }

        if ((bool) $this->option('progress')) {
            $this->line('C168 real Market Data-to-Watchlist runtime integration started');
        }

        $result = $this->service->execute($tradeDate, $outputPath, [
            'overwrite' => (bool) $this->option('overwrite'),
            'paramset' => $paramset,
            'paramset_source' => $paramsetSource,
            'capital_input' => $capitalInput,
        ]);

        foreach ([
            'run_code',
            'runtime_mode',
            'status',
            'reason_code',
            'trade_date',
            'trade_date_effective',
            'policy_code',
            'policy_version',
            'paramset_code',
            'paramset_hash',
            'paramset_source',
            'real_runtime_integration_executed',
            'real_market_data_consumed',
            'real_stock_output_generated',
            'controlled_runtime_output_generated',
            'production_runtime_activated',
            'plan_confirm_mutated',
            'controlled_rollout_executed',
            'official_output_published',
            'watchlist_tickers',
            'summary',
            'source_lineage',
            'output_path',
            'output_hash',
            'idempotency_key',
            'write_skipped_existing_output',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C168 real Market Data-to-Watchlist runtime integration completed');
            }

            return 0;
        }

        $this->error((string) ($result['message'] ?? 'C168 runtime integration failed.'));

        return 1;
    }

    private function loadParamset(string $path): array
    {
        $resolvedPath = is_file($path) ? $path : base_path($path);
        if (! is_file($resolvedPath)) {
            return [
                'valid' => false,
                'message' => 'C168 paramset file does not exist: '.$path,
                'payload' => [],
                'path' => $resolvedPath,
            ];
        }

        $raw = (string) file_get_contents($resolvedPath);
        $payload = json_decode($raw, true);
        if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'message' => 'C168 paramset file must contain one valid JSON object.',
                'payload' => [],
                'path' => $resolvedPath,
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'payload' => $payload,
            'path' => $resolvedPath,
        ];
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value === null ? '' : (string) $value;
    }
}
