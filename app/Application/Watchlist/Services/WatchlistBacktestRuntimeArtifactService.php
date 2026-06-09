<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestRuntimeArtifactService
{
    private const OUTPUT_SECTIONS = [
        'meta',
        'source_contract',
        'backtest_contract',
        'paramset_snapshot',
        'replay_window',
        'input_manifest',
        'items',
        'trades',
        'evaluations',
        'metrics',
        'summary',
        'diagnostics',
        'artifact_manifest',
        'validation',
    ];

    private WatchlistBacktestMetricsService $metricsService;

    public function __construct(WatchlistBacktestMetricsService $metricsService = null)
    {
        $this->metricsService = $metricsService ?: new WatchlistBacktestMetricsService();
    }

    public function buildArtifact(
        array $backtestPayload,
        array $publishedPriceSeriesByTicker = [],
        array $tradingCalendar = [],
        array $options = []
    ): array {
        $payload = $this->normalizeBacktestPayload($backtestPayload);
        $metrics = $this->metricsService->buildMetrics($payload, $publishedPriceSeriesByTicker, $tradingCalendar);
        $inputManifest = $this->inputManifest($payload, $publishedPriceSeriesByTicker, $tradingCalendar);
        $diagnostics = $this->sortDiagnostics(array_merge($payload['diagnostics'] ?? [], $metrics['diagnostics'] ?? []));
        $artifactReady = $this->hasRequiredSections($payload) && ! $this->hasFatalDiagnostic($diagnostics);

        $artifact = [
            'ready' => $artifactReady,
            'is_ready' => $artifactReady,
            'reason_code' => $this->artifactReasonCode($artifactReady, $metrics),
            'meta' => array_merge($payload['meta'] ?? [], [
                'artifact_service' => 'WatchlistBacktestRuntimeArtifactService',
                'artifact_version' => 'WATCHLIST_BT_RUNTIME_ARTIFACT_V1',
                'generated_at' => $options['generated_at'] ?? null,
                'deterministic_generated_at_required' => ! isset($options['generated_at']),
                'not_production_ready' => true,
            ]),
            'source_contract' => $payload['source_contract'] ?? [],
            'backtest_contract' => array_merge($payload['backtest_contract'] ?? [], [
                'runtime_artifact_created' => true,
                'metrics_ready' => (bool) ($metrics['ready'] ?? false),
                'not_production_ready' => true,
            ]),
            'paramset_snapshot' => $payload['paramset_snapshot'] ?? [],
            'replay_window' => $payload['replay_window'] ?? [],
            'input_manifest' => $inputManifest,
            'items' => $payload['items'] ?? [],
            'trades' => $payload['trades'] ?? [],
            'evaluations' => $payload['evaluations'] ?? [],
            'metrics' => $metrics,
            'summary' => $this->summary($payload, $metrics, $artifactReady),
            'diagnostics' => $diagnostics,
            'artifact_manifest' => $this->artifactManifest($payload),
            'validation' => [],
        ];

        $artifact['validation'] = $this->validation($artifact, $payload, $metrics);
        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];

        return $artifact;
    }

    public function encodeArtifact(array $artifact): string
    {
        return json_encode($this->normalizeForHash($artifact), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    }

    public function writeJsonArtifact(array $artifact, string $targetPath): array
    {
        $targetPath = trim($targetPath);
        if ($targetPath === '' || is_dir($targetPath)) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID',
                'path' => $targetPath,
            ];
        }

        $directory = dirname($targetPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_DIRECTORY_UNAVAILABLE',
                'path' => $targetPath,
            ];
        }

        $encoded = $this->encodeArtifact($artifact);
        $temporaryPath = $targetPath.'.tmp';
        if (file_put_contents($temporaryPath, $encoded, LOCK_EX) === false) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'path' => $targetPath,
            ];
        }

        if (! rename($temporaryPath, $targetPath)) {
            @unlink($temporaryPath);

            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_RENAME_FAILED',
                'path' => $targetPath,
            ];
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITTEN',
            'path' => $targetPath,
            'bytes' => strlen($encoded),
            'sha1' => sha1($encoded),
        ];
    }

    private function normalizeBacktestPayload(array $payload): array
    {
        foreach (['meta', 'source_contract', 'backtest_contract', 'paramset_snapshot', 'replay_window', 'summary', 'artifact_manifest'] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                $payload[$key] = [];
            }
        }
        foreach (['items', 'trades', 'evaluations', 'diagnostics'] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                $payload[$key] = [];
            }
        }

        return $payload;
    }

    private function inputManifest(array $payload, array $publishedPriceSeriesByTicker, array $tradingCalendar): array
    {
        return [
            'source_payload_hash' => $this->stableHash($payload),
            'source_payload_contract' => 'WatchlistBacktestStrategyService output',
            'replay_dates' => $payload['replay_window']['trade_dates'] ?? [],
            'items_count' => count($payload['items'] ?? []),
            'trades_count' => count($payload['trades'] ?? []),
            'evaluations_count' => count($payload['evaluations'] ?? []),
            'price_series_contract' => $publishedPriceSeriesByTicker === [] ? 'UNAVAILABLE' : 'PUBLISHED_EOD_PRICE_SERIES_INPUT',
            'price_series_ticker_count' => count($publishedPriceSeriesByTicker),
            'calendar_contract' => $tradingCalendar === [] ? 'UNAVAILABLE' : 'EXPLICIT_TRADING_CALENDAR_INPUT',
            'calendar_trade_date_count' => count($tradingCalendar),
            'no_raw_market_data_input' => true,
            'no_latest_shortcut_input' => true,
            'no_max_trade_date_shortcut_input' => true,
        ];
    }

    private function summary(array $payload, array $metrics, bool $artifactReady): array
    {
        $summary = $payload['summary'] ?? [];
        $summary['runtime_artifact_created'] = true;
        $summary['runtime_artifact_ready'] = $artifactReady;
        $summary['metrics_ready'] = (bool) ($metrics['ready'] ?? false);
        $summary['metrics_reason_code'] = $metrics['reason_code'] ?? null;
        $summary['total_replay_dates'] = $metrics['counts']['total_replay_dates'] ?? 0;
        $summary['total_recommendations'] = $metrics['counts']['total_recommendations'] ?? 0;
        $summary['total_evaluated_trades'] = $metrics['counts']['total_evaluated_trades'] ?? 0;
        $summary['production_ready'] = false;
        $summary['reason_codes'] = $this->uniqueReasonCodes(array_merge(
            $summary['reason_codes'] ?? [],
            [$metrics['reason_code'] ?? null]
        ));

        return $summary;
    }

    private function artifactManifest(array $payload): array
    {
        $manifest = $payload['artifact_manifest'] ?? [];
        $manifest['official_backtest_tables'] = $manifest['official_backtest_tables'] ?? [
            'watchlist_bt_param_grid',
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_cutoffs_ws',
            'watchlist_bt_oos_eval_ws',
        ];
        $manifest['production_proof_artifacts'] = $manifest['production_proof_artifacts'] ?? [
            'PLAN_UNIVERSE_SNAPSHOT_SCHEMA',
        ];
        $manifest['runtime_artifact_created'] = true;
        $manifest['runtime_persistence_created'] = false;
        $manifest['runtime_persistence_surface'] = 'JSON_EXPORT_FOUNDATION_ONLY';
        $manifest['official_manifest_source'] = 'docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md';
        $manifest['output_sections'] = self::OUTPUT_SECTIONS;
        $manifest['reason_codes'] = $this->uniqueReasonCodes(array_merge($manifest['reason_codes'] ?? [], [
            'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY',
        ]));

        return $manifest;
    }

    private function validation(array $artifact, array $payload, array $metrics): array
    {
        return [
            'required_sections_present' => $this->hasRequiredSections($artifact),
            'official_manifest_referenced' => in_array('watchlist_bt_eval', $artifact['artifact_manifest']['official_backtest_tables'] ?? [], true),
            'source_payload_hash_matches_input_manifest' => ($artifact['input_manifest']['source_payload_hash'] ?? null) === $this->stableHash($payload),
            'metrics_ready' => (bool) ($metrics['ready'] ?? false),
            'metrics_reason_code' => $metrics['reason_code'] ?? null,
            'no_lookahead' => (bool) ($artifact['backtest_contract']['no_lookahead'] ?? false),
            'deterministic_replay' => (bool) ($artifact['backtest_contract']['deterministic_replay'] ?? false),
            'publication_aware_replay' => (bool) ($artifact['backtest_contract']['publication_aware_replay'] ?? false),
            'no_raw_market_data' => (bool) ($artifact['source_contract']['no_raw_market_data'] ?? false),
            'no_latest_shortcut' => (bool) ($artifact['source_contract']['no_latest_shortcut'] ?? false),
            'no_max_trade_date_shortcut' => (bool) ($artifact['source_contract']['no_max_trade_date_shortcut'] ?? false),
            'no_plan_mutation' => (bool) ($artifact['source_contract']['no_plan_mutation'] ?? false),
            'no_recommendation_mutation' => (bool) ($artifact['source_contract']['no_recommendation_mutation'] ?? false),
            'no_confirm_mutation' => (bool) ($artifact['source_contract']['no_confirm_mutation'] ?? false),
            'not_production_ready' => true,
        ];
    }

    private function artifactReasonCode(bool $artifactReady, array $metrics): string
    {
        if (! $artifactReady) {
            return 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_NOT_READY';
        }
        if (($metrics['ready'] ?? false) !== true) {
            return 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY_WITH_EVALUATION_SKIPPED';
        }

        return 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY';
    }

    private function hasRequiredSections(array $payload): bool
    {
        foreach (self::OUTPUT_SECTIONS as $section) {
            if (! array_key_exists($section, $payload) && $section !== 'input_manifest' && $section !== 'metrics' && $section !== 'validation') {
                return false;
            }
        }

        return true;
    }

    private function hasFatalDiagnostic(array $diagnostics): bool
    {
        foreach ($diagnostics as $diagnostic) {
            if (($diagnostic['fatal'] ?? false) === true) {
                return true;
            }
        }

        return false;
    }

    private function sortDiagnostics(array $diagnostics): array
    {
        usort($diagnostics, function (array $left, array $right): int {
            foreach (['trade_date', 'ticker_id', 'ticker', 'reason_code'] as $key) {
                $leftValue = $left[$key] ?? null;
                $rightValue = $right[$key] ?? null;
                if ($leftValue === $rightValue) {
                    continue;
                }
                if ($leftValue === null) {
                    return 1;
                }
                if ($rightValue === null) {
                    return -1;
                }

                return strcmp((string) $leftValue, (string) $rightValue);
            }

            return 0;
        });

        return $diagnostics;
    }

    private function artifactForHash(array $artifact): array
    {
        unset($artifact['validation']['artifact_hash'], $artifact['meta']['artifact_hash']);

        return $artifact;
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalizeForHash($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalizeForHash($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($this->isList($value)) {
            return array_map(function ($item) {
                return $this->normalizeForHash($item);
            }, $value);
        }

        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeForHash($item);
        }

        return $value;
    }

    private function isList(array $value): bool
    {
        $index = 0;
        foreach (array_keys($value) as $key) {
            if ($key !== $index) {
                return false;
            }
            $index++;
        }

        return true;
    }

    private function uniqueReasonCodes(array $reasonCodes): array
    {
        $normalized = [];
        foreach ($reasonCodes as $reasonCode) {
            if (! is_scalar($reasonCode)) {
                continue;
            }
            $value = trim((string) $reasonCode);
            if ($value !== '') {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }
}
