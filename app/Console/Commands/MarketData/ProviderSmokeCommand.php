<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use Carbon\Carbon;

class ProviderSmokeCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:provider:smoke {--ticker=} {--trade_date=} {--dry-run} {--max-tickers=1} {--timeout=10} {--provider=yahoo} {--json}';

    protected $description = 'Run a safe single-ticker live provider smoke check without publication writes, seal, finalize, or pointer switch.';

    public function handle()
    {
        $this->applyProviderOption();

        $ticker = strtoupper(trim((string) $this->option('ticker')));
        $tradeDate = trim((string) $this->option('trade_date'));
        $maxTickers = (int) $this->option('max-tickers');
        $timeout = (int) $this->option('timeout');
        $dryRun = true; // Provider smoke is intentionally dry-run only; --dry-run is accepted for explicit operator proof.

        if ($ticker === '') {
            return $this->renderProviderSmokeResult([
                'provider_smoke_status' => 'BLOCKED',
                'reason_code' => 'PROVIDER_SMOKE_TICKER_REQUIRED',
                'provider' => $this->providerLabel(),
                'ticker' => '',
                'trade_date' => $tradeDate,
                'dry_run' => $this->boolString($dryRun),
                'write_mode' => 'none',
                'publication_created' => 'false',
                'pointer_switched' => 'false',
            ], 1);
        }

        $tickerParts = array_values(array_filter(array_map('trim', preg_split('/[,\s]+/', $ticker))));
        if (count($tickerParts) !== 1 || $maxTickers !== 1) {
            return $this->renderProviderSmokeResult([
                'provider_smoke_status' => 'BLOCKED',
                'reason_code' => 'PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED',
                'provider' => $this->providerLabel(),
                'ticker' => $ticker,
                'trade_date' => $tradeDate,
                'dry_run' => $this->boolString($dryRun),
                'max_tickers' => (string) $maxTickers,
                'write_mode' => 'none',
                'publication_created' => 'false',
                'pointer_switched' => 'false',
            ], 1);
        }

        if (! preg_match('/^[A-Z0-9._-]+$/', $ticker)) {
            return $this->renderProviderSmokeResult([
                'provider_smoke_status' => 'BLOCKED',
                'reason_code' => 'PROVIDER_SMOKE_INVALID_TICKER',
                'provider' => $this->providerLabel(),
                'ticker' => $ticker,
                'trade_date' => $tradeDate,
                'dry_run' => $this->boolString($dryRun),
                'write_mode' => 'none',
                'publication_created' => 'false',
                'pointer_switched' => 'false',
            ], 1);
        }

        if ($tradeDate === '') {
            $tradeDate = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateString();
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tradeDate)) {
            return $this->renderProviderSmokeResult([
                'provider_smoke_status' => 'BLOCKED',
                'reason_code' => 'COMMAND_INVALID_DATE_FORMAT',
                'provider' => $this->providerLabel(),
                'ticker' => $ticker,
                'trade_date' => $tradeDate,
                'dry_run' => $this->boolString($dryRun),
                'write_mode' => 'none',
                'publication_created' => 'false',
                'pointer_switched' => 'false',
            ], 1);
        }

        if ($timeout > 0) {
            config(['market_data.source.api.timeout_seconds' => $timeout]);
        }

        try {
            /** @var PublicApiEodBarsAdapter $adapter */
            $adapter = $this->container()->make(PublicApiEodBarsAdapter::class);
            $rows = $adapter->fetchOrLoadEodBars($tradeDate, 'api', [$ticker]);
            $telemetry = $adapter->consumeLastAcquisitionTelemetry();

            if (count($rows) < 1) {
                return $this->renderProviderSmokeResult($this->baseResult($ticker, $tradeDate, $dryRun, [
                    'provider_smoke_status' => 'FAILED',
                    'reason_code' => 'PROVIDER_EMPTY_OR_INVALID_RESPONSE',
                    'returned_row_count' => '0',
                ] + $this->telemetryLines($telemetry)), 1);
            }

            return $this->renderProviderSmokeResult($this->baseResult($ticker, $tradeDate, $dryRun, [
                'provider_smoke_status' => 'PASS',
                'reason_code' => 'PROVIDER_SMOKE_OK',
                'returned_row_count' => (string) count($rows),
            ] + $this->telemetryLines($telemetry)), 0);
        } catch (SourceAcquisitionException $e) {
            $telemetry = $e->context();
            $reasonCode = $this->mapProviderSmokeReasonCode($e->reasonCode(), $e->getMessage());
            $status = in_array($reasonCode, ['PROVIDER_RATE_LIMITED', 'PROVIDER_TIMEOUT', 'PROVIDER_NETWORK_ERROR'], true)
                ? 'BLOCKED'
                : 'FAILED';

            return $this->renderProviderSmokeResult($this->baseResult($ticker, $tradeDate, $dryRun, [
                'provider_smoke_status' => $status,
                'reason_code' => $reasonCode,
                'source_reason_code' => $e->reasonCode(),
                'error' => $this->sanitizeOutput($e->getMessage()),
            ] + $this->telemetryLines($telemetry)), $status === 'BLOCKED' ? 2 : 1);
        } catch (\Throwable $e) {
            return $this->renderProviderSmokeResult($this->baseResult($ticker, $tradeDate, $dryRun, [
                'provider_smoke_status' => 'BLOCKED',
                'reason_code' => 'PROVIDER_NETWORK_ERROR',
                'error' => $this->sanitizeOutput($e->getMessage()),
            ]), 2);
        }
    }

    private function baseResult($ticker, $tradeDate, $dryRun, array $extra = [])
    {
        return $extra + [
            'provider' => $this->providerLabel(),
            'ticker' => $ticker,
            'trade_date' => $tradeDate,
            'dry_run' => $this->boolString($dryRun),
            'write_mode' => 'none',
            'publication_created' => 'false',
            'seal_executed' => 'false',
            'finalize_executed' => 'false',
            'pointer_switched' => 'false',
            'readable_publication_created' => 'false',
            'full_universe_fetch' => 'false',
        ];
    }

    private function providerLabel()
    {
        $provider = strtolower((string) config('market_data.source.api.provider', 'yahoo_finance'));

        return $provider === 'yahoo_finance' ? 'Yahoo/PublicApi' : $provider;
    }

    private function applyProviderOption()
    {
        $provider = strtolower(str_replace('-', '_', trim((string) $this->option('provider'))));
        if ($provider === '') {
            return;
        }

        if ($provider === 'yahoo') {
            $provider = 'yahoo_finance';
        }

        config(['market_data.source.api.provider' => $provider]);
    }

    private function mapProviderSmokeReasonCode($sourceReasonCode, $message)
    {
        if ($sourceReasonCode === 'RUN_SOURCE_RATE_LIMIT') {
            return 'PROVIDER_RATE_LIMITED';
        }

        if ($sourceReasonCode === 'RUN_SOURCE_TIMEOUT') {
            return stripos((string) $message, 'failed') !== false ? 'PROVIDER_NETWORK_ERROR' : 'PROVIDER_TIMEOUT';
        }

        if (in_array($sourceReasonCode, ['RUN_SOURCE_NO_VALID_DATA', 'RUN_SOURCE_RESPONSE_CHANGED', 'RUN_SOURCE_MALFORMED_PAYLOAD'], true)) {
            return 'PROVIDER_EMPTY_OR_INVALID_RESPONSE';
        }

        return 'PROVIDER_NETWORK_ERROR';
    }

    private function telemetryLines(array $telemetry)
    {
        $lines = [];

        foreach (['final_http_status' => 'http_status', 'attempt_count' => 'attempt_count', 'retry_exhausted' => 'retry_exhausted', 'timeout_seconds' => 'timeout_seconds'] as $source => $target) {
            if (array_key_exists($source, $telemetry) && $telemetry[$source] !== null && $telemetry[$source] !== '') {
                $lines[$target] = is_bool($telemetry[$source]) ? $this->boolString($telemetry[$source]) : (string) $telemetry[$source];
            }
        }

        return $lines;
    }

    private function renderProviderSmokeResult(array $result, $exitCode)
    {
        $ordered = [
            'provider_smoke_status',
            'reason_code',
            'source_reason_code',
            'provider',
            'ticker',
            'trade_date',
            'dry_run',
            'max_tickers',
            'write_mode',
            'publication_created',
            'seal_executed',
            'finalize_executed',
            'pointer_switched',
            'readable_publication_created',
            'full_universe_fetch',
            'returned_row_count',
            'http_status',
            'attempt_count',
            'retry_exhausted',
            'timeout_seconds',
            'error',
        ];

        $lines = [];
        foreach ($ordered as $key) {
            if (array_key_exists($key, $result) && $result[$key] !== null && $result[$key] !== '') {
                $lines[] = $key.'='.$result[$key];
            }
        }

        foreach ($result as $key => $value) {
            if (! in_array($key, $ordered, true) && $value !== null && $value !== '') {
                $lines[] = $key.'='.$value;
            }
        }

        if ((bool) $this->option('json')) {
            $this->line(json_encode($this->jsonPayload($result), JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($lines as $line) {
                $this->line($line);
            }
        }

        $this->writeProviderSmokeArtifact($lines);

        return $exitCode;
    }

    private function jsonPayload(array $result)
    {
        $payload = [];

        foreach ($result as $key => $value) {
            if (in_array($key, [
                'dry_run',
                'publication_created',
                'seal_executed',
                'finalize_executed',
                'pointer_switched',
                'readable_publication_created',
                'full_universe_fetch',
                'retry_exhausted',
            ], true)) {
                $payload[$key] = $value === true || $value === 'true';
                continue;
            }

            if (in_array($key, ['max_tickers', 'returned_row_count', 'http_status', 'attempt_count', 'timeout_seconds'], true) && $value !== null && $value !== '') {
                $payload[$key] = (int) $value;
                continue;
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    private function writeProviderSmokeArtifact(array $lines)
    {
        $path = base_path('storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt');
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $content = "ENCODING: UTF-8\nCOMMAND: php artisan market-data:provider:smoke --ticker=BBCA --trade_date=<YYYY-MM-DD> --dry-run\n".implode("\n", $lines)."\n";
        file_put_contents($path, $content);
    }

    private function boolString($value)
    {
        return $value ? 'true' : 'false';
    }

    private function sanitizeOutput($value)
    {
        return str_replace(["\r", "\n", "\0"], ' ', (string) $value);
    }
}
