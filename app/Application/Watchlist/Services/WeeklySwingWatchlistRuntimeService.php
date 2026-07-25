<?php

namespace App\Application\Watchlist\Services;

use DateTimeImmutable;

class WeeklySwingWatchlistRuntimeService
{
    public const RUN_CODE = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION';
    public const RUNTIME_MODE = 'CONTROLLED_RUNTIME_INTEGRATION_PROOF';

    private const PASS_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_PASSED_REAL_TICKER_WATCHLIST_GENERATED';
    private const PASS_EMPTY_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_PASSED_VALID_EMPTY_WATCHLIST';
    private const INVALID_TRADE_DATE_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_INVALID_TRADE_DATE';
    private const MARKET_DATA_NOT_READY_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_MARKET_DATA_NOT_READY';
    private const UNIVERSE_NOT_READY_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_CANDIDATE_UNIVERSE_NOT_READY';
    private const SCORING_NOT_READY_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_SCORING_NOT_READY';
    private const PLAN_NOT_READY_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_PLAN_GROUPING_NOT_READY';
    private const RECOMMENDATION_NOT_READY_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_RECOMMENDATION_NOT_READY';
    private const INVALID_OUTPUT_ROW_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_INVALID_TICKER_OUTPUT';
    private const OUTPUT_CONFLICT_STATUS = 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_BLOCKED_OUTPUT_CONFLICT';

    private WatchlistMarketDataConsumerReadService $marketData;
    private WatchlistCandidateUniverseService $candidateUniverse;
    private WatchlistScoringService $scoring;
    private WatchlistPlanGroupingService $planGrouping;
    private WatchlistRecommendationService $recommendation;

    public function __construct(
        ?WatchlistMarketDataConsumerReadService $marketData = null,
        ?WatchlistCandidateUniverseService $candidateUniverse = null,
        ?WatchlistScoringService $scoring = null,
        ?WatchlistPlanGroupingService $planGrouping = null,
        ?WatchlistRecommendationService $recommendation = null
    ) {
        $this->marketData = $marketData ?: new WatchlistMarketDataConsumerReadService();
        $this->candidateUniverse = $candidateUniverse ?: new WatchlistCandidateUniverseService($this->marketData);
        $this->scoring = $scoring ?: new WatchlistScoringService($this->candidateUniverse);
        $this->planGrouping = $planGrouping ?: new WatchlistPlanGroupingService($this->scoring);
        $this->recommendation = $recommendation ?: new WatchlistRecommendationService($this->planGrouping);
    }

    public function execute(string $tradeDate, string $outputPath = '', array $options = []): array
    {
        $tradeDate = trim($tradeDate);
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : $this->defaultOutputPath($tradeDate);
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $paramset = array_replace_recursive($this->defaultParamset(), (array) ($options['paramset'] ?? []));
        $capitalInput = (array) ($options['capital_input'] ?? []);
        $artifact = $this->baseArtifact(
            $tradeDate,
            $createdAt,
            $outputPath,
            $paramset,
            $capitalInput,
            (string) ($options['paramset_source'] ?? 'canonical_executable_watchlist_service_defaults')
        );

        if (! $this->validTradeDate($tradeDate)) {
            return $this->finish(
                $artifact,
                self::INVALID_TRADE_DATE_STATUS,
                'C168 requires an explicit valid trade date in YYYY-MM-DD format.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $marketData = $this->marketData->getCandidateUniverseForTradeDate($tradeDate);
        $artifact['pipeline_stages']['market_data_consumer_read'] = $this->marketDataStage($marketData);
        $artifact = $this->withLineage($artifact, $marketData);
        if (! ($marketData['is_ready'] ?? false)) {
            return $this->finish(
                $artifact,
                self::MARKET_DATA_NOT_READY_STATUS,
                'Published Market Data is not ready for the requested trade date.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $universe = $this->candidateUniverse->buildCandidateUniverseFromConsumerPayload($marketData, $tradeDate, $paramset);
        $artifact['pipeline_stages']['candidate_universe'] = $this->universeStage($universe);
        if (! ($universe['is_ready'] ?? false)) {
            return $this->finish(
                $artifact,
                self::UNIVERSE_NOT_READY_STATUS,
                'Candidate universe rejected the published Market Data payload.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $scored = $this->scoring->scoreCandidateUniverse($universe, $paramset, $tradeDate);
        $artifact['pipeline_stages']['scoring'] = $this->scoringStage($scored);
        if (! ($scored['is_ready'] ?? false)) {
            return $this->finish(
                $artifact,
                self::SCORING_NOT_READY_STATUS,
                'Scoring did not produce a ready deterministic payload.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $plan = $this->planGrouping->groupScoredOutput($scored, $paramset, $tradeDate);
        $artifact['pipeline_stages']['plan_grouping'] = $this->planStage($plan);
        if (! ($plan['is_ready'] ?? false)) {
            return $this->finish(
                $artifact,
                self::PLAN_NOT_READY_STATUS,
                'PLAN grouping did not produce a ready deterministic payload.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $recommendation = $this->recommendation->recommendFromPlanOutput($plan, $paramset, $capitalInput);
        $artifact['pipeline_stages']['recommendation'] = $this->recommendationStage($recommendation);
        if (! ($recommendation['is_ready'] ?? false)) {
            return $this->finish(
                $artifact,
                self::RECOMMENDATION_NOT_READY_STATUS,
                'Recommendation did not produce a ready deterministic payload.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $watchlistRows = $this->buildWatchlistRows($recommendation, $marketData, $scored);
        $invalidRows = $this->invalidOutputRows($watchlistRows, $artifact['source_lineage']);
        $artifact['watchlist_rows'] = $watchlistRows;
        $artifact['watchlist_tickers'] = array_values(array_map(function (array $row): string {
            return $row['ticker_code'];
        }, $watchlistRows));
        $artifact['invalid_output_rows'] = $invalidRows;
        $artifact['summary'] = $this->summary($marketData, $universe, $scored, $plan, $recommendation, $watchlistRows);

        if ($invalidRows !== []) {
            return $this->finish(
                $artifact,
                self::INVALID_OUTPUT_ROW_STATUS,
                'C168 rejected one or more output rows because ticker identity or Market Data lineage is invalid.',
                $outputPath,
                $overwrite,
                false
            );
        }

        $artifact['idempotency_key'] = $this->idempotencyKey($artifact, $capitalInput);
        $artifact['real_runtime_integration_executed'] = true;
        $artifact['real_market_data_consumed'] = true;
        $artifact['real_stock_output_generated'] = $watchlistRows !== [];
        $artifact['controlled_runtime_output_generated'] = true;
        $artifact['diagnostic_conclusion'] = $watchlistRows === []
            ? 'C168_REAL_PIPELINE_EXECUTED_VALID_EMPTY_WATCHLIST'
            : 'C168_REAL_MARKET_DATA_TO_TICKER_WATCHLIST_PIPELINE_EXECUTED';

        return $this->finish(
            $artifact,
            $watchlistRows === [] ? self::PASS_EMPTY_STATUS : self::PASS_STATUS,
            $watchlistRows === []
                ? 'C168 executed the real Market Data-to-Watchlist pipeline; no stock met the deterministic recommendation gate.'
                : 'C168 executed the real Market Data-to-Watchlist pipeline and generated validated stock ticker rows.',
            $outputPath,
            $overwrite,
            true
        );
    }

    public function defaultParamset(): array
    {
        return array_replace_recursive(
            WatchlistCandidateUniverseService::defaultParamset(),
            WatchlistScoringService::defaultParamset(),
            WatchlistPlanGroupingService::defaultParamset(),
            WatchlistRecommendationService::defaultParamset()
        );
    }

    private function baseArtifact(
        string $tradeDate,
        string $createdAt,
        string $outputPath,
        array $paramset,
        array $capitalInput,
        string $paramsetSource
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'artifact_type' => 'WEEKLY_SWING_WATCHLIST_RUNTIME_OUTPUT',
            'runtime_mode' => self::RUNTIME_MODE,
            'status' => 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_NOT_RUN',
            'reason_code' => 'C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_NOT_RUN',
            'message' => null,
            'created_at' => $createdAt,
            'trade_date' => $tradeDate,
            'trade_date_effective' => null,
            'output_path' => $outputPath,
            'output_hash' => null,
            'output_hash_algorithm' => 'stable_sha1_json_payload',
            'idempotency_key' => null,
            'policy_code' => (string) ($paramset['policy_code'] ?? ''),
            'policy_version' => (string) ($paramset['policy_version'] ?? ''),
            'paramset_code' => (string) ($paramset['paramset_code'] ?? ''),
            'paramset_hash' => $this->stableHash($paramset),
            'paramset_source' => $paramsetSource,
            'paramset_snapshot' => $paramset,
            'capital_input' => $capitalInput,
            'source_lineage' => [
                'trade_date_requested' => $tradeDate,
                'trade_date_effective' => null,
                'publication_id' => null,
                'publication_version' => null,
                'run_id' => null,
                'pointer_resolve_status' => null,
            ],
            'pipeline_contract' => [
                'market_data_source' => WatchlistMarketDataConsumerReadService::class,
                'candidate_universe_service' => WatchlistCandidateUniverseService::class,
                'scoring_service' => WatchlistScoringService::class,
                'plan_grouping_service' => WatchlistPlanGroupingService::class,
                'recommendation_service' => WatchlistRecommendationService::class,
                'requires_current_readable_publication_pointer' => true,
                'forbids_raw_staging_latest_shortcut' => true,
                'requires_real_ticker_identity' => true,
                'strategy_candidate_codes_are_not_stock_rows' => true,
            ],
            'pipeline_stages' => [],
            'watchlist_rows' => [],
            'watchlist_tickers' => [],
            'invalid_output_rows' => [],
            'summary' => [],
            'real_runtime_integration_executed' => false,
            'real_market_data_consumed' => false,
            'real_stock_output_generated' => false,
            'controlled_runtime_output_generated' => false,
            'production_runtime_activated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'controlled_rollout_allowed' => false,
            'controlled_rollout_executed' => false,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'production_catalog_strategy_binding_state' => 'NOT_CLAIMED_BY_C168_RUNTIME_INTEGRATION_PROOF',
            'diagnostic_conclusion' => 'C168_NOT_EVALUATED',
            'next_step_recommendation' => 'VALIDATE_C168_EXECUTED_TICKER_OUTPUT_BEFORE_ANY_ACTIVATION_PLAN_CONFIRM_OR_ROLLOUT',
            'write_skipped_existing_output' => false,
        ];
    }

    private function withLineage(array $artifact, array $marketData): array
    {
        $artifact['trade_date_effective'] = $marketData['trade_date_effective'] ?? null;
        $artifact['source_lineage'] = [
            'trade_date_requested' => $artifact['trade_date'],
            'trade_date_effective' => $marketData['trade_date_effective'] ?? null,
            'publication_id' => $marketData['publication_id'] ?? null,
            'publication_version' => $marketData['publication_version'] ?? null,
            'run_id' => $marketData['run_id'] ?? null,
            'pointer_resolve_status' => $marketData['pointer_resolve_status'] ?? null,
        ];

        return $artifact;
    }

    private function marketDataStage(array $payload): array
    {
        return [
            'service' => WatchlistMarketDataConsumerReadService::class,
            'invoked' => true,
            'is_ready' => (bool) ($payload['is_ready'] ?? false),
            'reason_code' => $payload['reason_code'] ?? null,
            'candidate_count' => (int) ($payload['candidate_count'] ?? 0),
            'excluded_count' => (int) ($payload['excluded_count'] ?? 0),
            'pointer_resolve_status' => $payload['pointer_resolve_status'] ?? null,
        ];
    }

    private function universeStage(array $payload): array
    {
        return [
            'service' => WatchlistCandidateUniverseService::class,
            'invoked' => true,
            'is_ready' => (bool) ($payload['is_ready'] ?? false),
            'reason_code' => $payload['reason_code'] ?? null,
            'input_candidate_count' => (int) ($payload['input_candidate_count'] ?? 0),
            'eligible_count' => (int) ($payload['eligible_count'] ?? 0),
            'rejected_count' => (int) ($payload['rejected_count'] ?? 0),
        ];
    }

    private function scoringStage(array $payload): array
    {
        return [
            'service' => WatchlistScoringService::class,
            'invoked' => true,
            'is_ready' => (bool) ($payload['is_ready'] ?? false),
            'reason_code' => $payload['reason_code'] ?? null,
            'input_count' => (int) ($payload['summary']['input_count'] ?? 0),
            'scored_count' => (int) ($payload['summary']['scored_count'] ?? 0),
            'excluded_count' => (int) ($payload['summary']['excluded_count'] ?? 0),
        ];
    }

    private function planStage(array $payload): array
    {
        return [
            'service' => WatchlistPlanGroupingService::class,
            'invoked' => true,
            'is_ready' => (bool) ($payload['is_ready'] ?? false),
            'reason_code' => $payload['reason_code'] ?? null,
            'input_count' => (int) ($payload['summary']['input_count'] ?? 0),
            'top_picks_count' => (int) ($payload['summary']['top_picks_count'] ?? 0),
            'secondary_count' => (int) ($payload['summary']['secondary_count'] ?? 0),
            'watch_only_count' => (int) ($payload['summary']['watch_only_count'] ?? 0),
            'avoid_count' => (int) ($payload['summary']['avoid_count'] ?? 0),
        ];
    }

    private function recommendationStage(array $payload): array
    {
        return [
            'service' => WatchlistRecommendationService::class,
            'invoked' => true,
            'is_ready' => (bool) ($payload['is_ready'] ?? false),
            'reason_code' => $payload['reason_code'] ?? null,
            'evaluated_count' => (int) ($payload['summary']['evaluated_count'] ?? 0),
            'recommended_count' => (int) ($payload['summary']['recommended_count'] ?? 0),
            'recommended_tickers' => array_values((array) ($payload['summary']['recommended_tickers'] ?? [])),
        ];
    }

    private function buildWatchlistRows(array $recommendation, array $marketData, array $scored): array
    {
        $sourceIndex = $this->indexByTickerCode((array) ($marketData['candidates'] ?? []), 'ticker_code');
        $scoreIndex = $this->indexByTickerCode((array) ($scored['items'] ?? []), 'ticker_code');
        $rows = [];

        foreach ((array) ($recommendation['items'] ?? []) as $item) {
            if (! is_array($item) || ($item['recommended_flag'] ?? false) !== true) {
                continue;
            }

            $tickerCode = strtoupper(trim((string) ($item['ticker'] ?? '')));
            $source = $sourceIndex[$tickerCode] ?? [];
            $score = $scoreIndex[$tickerCode] ?? [];
            $indicators = (array) ($source['indicators'] ?? []);
            $rows[] = [
                'ticker_code' => $tickerCode,
                'ticker_id' => isset($item['ticker_id']) ? (int) $item['ticker_id'] : null,
                'ticker_name' => $source['ticker_name'] ?? null,
                'sector_code' => $source['sector_code'] ?? ($score['score_metrics']['sector_code'] ?? null),
                'trade_date' => $source['trade_date'] ?? ($recommendation['meta']['trade_date'] ?? null),
                'trade_date_effective' => $source['trade_date_effective'] ?? ($recommendation['meta']['trade_date_effective'] ?? null),
                'publication_id' => $source['publication_id'] ?? ($recommendation['meta']['publication_id'] ?? null),
                'publication_version' => $source['publication_version'] ?? ($recommendation['meta']['publication_version'] ?? null),
                'run_id' => $source['run_id'] ?? ($recommendation['meta']['run_id'] ?? null),
                'close_price' => $source['close_price'] ?? null,
                'indicator_set_version' => $source['indicator_set_version'] ?? null,
                'recommendation_rank' => isset($item['recommendation_rank']) ? (int) $item['recommendation_rank'] : null,
                'recommendation_score' => $item['recommendation_score'] ?? null,
                'recommendation_label' => $item['recommendation_label'] ?? null,
                'plan_rank' => $item['plan_rank'] ?? null,
                'plan_group' => $item['plan_group_semantic'] ?? null,
                'score_total' => $item['plan_reference']['score_total'] ?? ($score['score_total'] ?? null),
                'score_components' => $score['score_components'] ?? [],
                'market_metrics' => [
                    'dv20_idr' => $indicators['dv20idr'] ?? ($score['score_metrics']['dv20_idr'] ?? null),
                    'atr14_pct' => $indicators['atr14_pct'] ?? ($score['score_metrics']['atr14_pct'] ?? null),
                    'vol_ratio' => $indicators['vol_ratio'] ?? ($score['score_metrics']['vol_ratio'] ?? null),
                    'roc_20' => $indicators['roc_20'] ?? ($score['score_metrics']['roc20'] ?? null),
                    'close_to_hh20_pct' => $indicators['close_to_hh20_pct'] ?? ($score['score_metrics']['close_to_hh20_pct'] ?? null),
                    'ma20_slope_pct' => $indicators['ma20_slope_pct'] ?? ($score['score_metrics']['ma20_slope_pct'] ?? null),
                    'rs_20_vs_ihsg' => $indicators['rs_20_vs_ihsg'] ?? ($score['score_metrics']['rs_20_vs_ihsg'] ?? null),
                ],
                'reason_codes' => array_values(array_unique(array_merge(
                    (array) ($score['reason_codes'] ?? []),
                    (array) ($item['reason_codes'] ?? [])
                ))),
            ];
        }

        usort($rows, function (array $left, array $right): int {
            $rankCompare = ((int) ($left['recommendation_rank'] ?? PHP_INT_MAX))
                <=> ((int) ($right['recommendation_rank'] ?? PHP_INT_MAX));

            return $rankCompare !== 0
                ? $rankCompare
                : strcmp((string) ($left['ticker_code'] ?? ''), (string) ($right['ticker_code'] ?? ''));
        });

        return $rows;
    }

    private function invalidOutputRows(array $rows, array $lineage): array
    {
        $invalid = [];

        foreach ($rows as $index => $row) {
            $failures = [];
            $tickerCode = (string) ($row['ticker_code'] ?? '');
            if (! preg_match('/^[A-Z0-9][A-Z0-9.\-]{0,19}$/D', $tickerCode)) {
                $failures[] = 'C168_INVALID_TICKER_CODE';
            }
            if (strpos($tickerCode, 'C61_') === 0 || substr($tickerCode, -10) === '_CANDIDATE') {
                $failures[] = 'C168_STRATEGY_CODE_USED_AS_TICKER';
            }
            if (! is_int($row['ticker_id'] ?? null) || (int) $row['ticker_id'] <= 0) {
                $failures[] = 'C168_INVALID_TICKER_ID';
            }
            foreach (['publication_id', 'publication_version', 'run_id', 'trade_date_effective'] as $field) {
                if (($row[$field] ?? null) !== ($lineage[$field] ?? null)) {
                    $failures[] = 'C168_LINEAGE_MISMATCH:'.$field;
                }
            }
            if (! is_numeric($row['close_price'] ?? null) || (float) $row['close_price'] <= 0) {
                $failures[] = 'C168_INVALID_CLOSE_PRICE';
            }
            if (! is_numeric($row['recommendation_score'] ?? null)) {
                $failures[] = 'C168_INVALID_RECOMMENDATION_SCORE';
            }

            if ($failures !== []) {
                $invalid[] = [
                    'row_index' => $index,
                    'ticker_code' => $tickerCode,
                    'reason_codes' => $failures,
                ];
            }
        }

        return $invalid;
    }

    private function summary(
        array $marketData,
        array $universe,
        array $scored,
        array $plan,
        array $recommendation,
        array $watchlistRows
    ): array {
        return [
            'market_data_candidate_count' => (int) ($marketData['candidate_count'] ?? 0),
            'eligible_candidate_count' => (int) ($universe['eligible_count'] ?? 0),
            'rejected_candidate_count' => (int) ($universe['rejected_count'] ?? 0),
            'scored_candidate_count' => (int) ($scored['summary']['scored_count'] ?? 0),
            'plan_top_picks_count' => (int) ($plan['summary']['top_picks_count'] ?? 0),
            'plan_secondary_count' => (int) ($plan['summary']['secondary_count'] ?? 0),
            'recommendation_evaluated_count' => (int) ($recommendation['summary']['evaluated_count'] ?? 0),
            'recommended_stock_count' => count($watchlistRows),
            'recommended_tickers' => array_values(array_map(function (array $row): string {
                return $row['ticker_code'];
            }, $watchlistRows)),
            'valid_empty_watchlist' => $watchlistRows === [],
        ];
    }

    private function indexByTickerCode(array $rows, string $field): array
    {
        $index = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $tickerCode = strtoupper(trim((string) ($row[$field] ?? '')));
            if ($tickerCode !== '') {
                $index[$tickerCode] = $row;
            }
        }

        return $index;
    }

    private function idempotencyKey(array $artifact, array $capitalInput): string
    {
        return sha1(json_encode([
            'run_code' => self::RUN_CODE,
            'runtime_mode' => self::RUNTIME_MODE,
            'trade_date' => $artifact['trade_date'],
            'trade_date_effective' => $artifact['trade_date_effective'],
            'source_lineage' => $artifact['source_lineage'],
            'paramset_hash' => $artifact['paramset_hash'],
            'capital_input_hash' => $this->stableHash($capitalInput),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function validTradeDate(string $tradeDate): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $tradeDate);

        return $date !== false && $date->format('Y-m-d') === $tradeDate;
    }

    private function defaultOutputPath(string $tradeDate): string
    {
        $safeDate = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $tradeDate) ? $tradeDate : 'invalid-date';

        return 'storage/app/watchlist/runtime/c168-weekly-swing-watchlist-'.$safeDate.'.json';
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode(
            $this->canonicalize($payload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }

    private function canonicalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $keys = array_keys($value);
        $isList = $keys === ($keys === [] ? [] : range(0, count($keys) - 1));
        if (! $isList) {
            ksort($value, SORT_STRING);
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }

    private function finish(
        array $artifact,
        string $status,
        string $message,
        string $outputPath,
        bool $overwrite,
        bool $pass
    ): array {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['pipeline_pass'] = $pass;
        $artifact['production_runtime_activated'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['controlled_rollout_allowed'] = false;
        $artifact['controlled_rollout_executed'] = false;
        $artifact['official_output_published'] = false;
        $artifact['free_publication_allowed'] = false;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $raw = (string) file_get_contents($outputPath);
            $existing = json_decode($raw, true);
            if (is_array($existing)
                && ($artifact['idempotency_key'] ?? null) !== null
                && ($existing['idempotency_key'] ?? null) === $artifact['idempotency_key']) {
                $existing['write_skipped_existing_output'] = true;

                return $existing;
            }

            $artifact['status'] = self::OUTPUT_CONFLICT_STATUS;
            $artifact['reason_code'] = self::OUTPUT_CONFLICT_STATUS;
            $artifact['message'] = 'Output already exists for a different runtime identity; use a date-specific path or explicit overwrite.';
            $artifact['pipeline_pass'] = false;
            $artifact['write_skipped_existing_output'] = true;

            return $artifact;
        }

        $directory = dirname($outputPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $hashPayload = $artifact;
        $hashPayload['output_hash'] = null;
        unset($hashPayload['output_path'], $hashPayload['write_skipped_existing_output']);
        $artifact['output_hash'] = $this->stableHash($hashPayload);
        $artifact['output_path'] = $outputPath;
        $artifact['write_skipped_existing_output'] = false;
        file_put_contents(
            $outputPath,
            json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL,
            LOCK_EX
        );

        return $artifact;
    }
}
