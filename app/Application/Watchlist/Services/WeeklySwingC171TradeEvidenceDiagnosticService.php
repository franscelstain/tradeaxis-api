<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingC171TradeEvidenceDiagnosticService
{
    public const RUN_CODE = 'C171_TARGETED_EXECUTABLE_IS_STRATEGY_REMEDIATION_AND_TRADE_EVIDENCE_DIAGNOSTIC';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';

    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;
    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WatchlistBacktestPublishedPriceRuntimeService $runtime;
    private WatchlistBacktestOfficialEvidenceRepository $officialEvidence;

    public function __construct(
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null,
        WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WatchlistBacktestPublishedPriceRuntimeService $runtime = null,
        WatchlistBacktestOfficialEvidenceRepository $officialEvidence = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->runtimeAdapter = $runtimeAdapter ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->runtime = $runtime ?: new WatchlistBacktestPublishedPriceRuntimeService();
        $this->officialEvidence = $officialEvidence ?: new WatchlistBacktestOfficialEvidenceRepository();
    }

    public function execute(
        int $evalId,
        int $paramSetId,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || trim($approvalReference) === '') {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_OPERATOR_APPROVAL_MISSING');
        }
        if ($evalId < 1 || $paramSetId < 1) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_IDENTITY_INVALID');
        }
        foreach (['watchlist_bt_eval','watchlist_bt_picks_ws','watchlist_param_sets','watchlist_bt_oos_eval_ws'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('C171_TRADE_DIAGNOSTIC_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $eval = DB::table('watchlist_bt_eval')->where('eval_id', $evalId)->first();
        $draft = DB::table('watchlist_param_sets')->where('param_set_id', $paramSetId)->first();
        if (! $eval || ! $draft) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_OFFICIAL_IDENTITY_NOT_FOUND');
        }
        if ((string) $eval->policy_code !== 'WS'
            || (string) $eval->from_date !== self::CANONICAL_IS_FROM
            || (string) $eval->to_date !== self::CANONICAL_IS_TO
            || (string) $draft->policy_code !== 'WS'
            || (string) $draft->status !== 'DRAFT') {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_CANONICAL_BASELINE_MISMATCH');
        }

        $canonicalPayload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        if (! is_array($canonicalPayload) || ! is_array($provenance)) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_PARAMSET_PAYLOAD_INVALID');
        }
        $validation = $this->validator->validate($canonicalPayload);
        if (! ($validation['valid'] ?? false)
            || (string) ($validation['canonical_hash'] ?? '') !== (string) $draft->params_hash) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_PARAMSET_VALIDATION_FAILED', ['validation' => $validation]);
        }

        $binding = is_array($provenance['bt_binding'] ?? null) ? $provenance['bt_binding'] : [];
        $btParamId = (int) ($binding['bt_param_id'] ?? 0);
        $catalogCode = (string) ($binding['catalog_code'] ?? '');
        $bindingVerification = $this->bindingVerifier->verify($validation['canonical_payload'], $btParamId, $catalogCode);
        if (! ($bindingVerification['valid'] ?? false)
            || (int) $eval->param_id !== $btParamId) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_PARAMSET_BINDING_INVALID', ['binding_verification' => $bindingVerification]);
        }

        $runtimeParamset = $this->runtimeAdapter->adapt($validation['canonical_payload']);
        $expectedIdentity = $this->identity->identity(
            $validation['canonical_payload'],
            WatchlistBacktestStrategyService::canonicalEvalModel($runtimeParamset)
        );
        foreach ([
            'paramset_hash' => 'params_hash',
            'eval_model' => 'eval_model',
            'eval_model_hash' => 'eval_model_hash',
            'implementation_version' => 'implementation_version',
            'implementation_hash' => 'implementation_hash',
        ] as $evalField => $draftField) {
            if ((string) ($eval->{$evalField} ?? '') !== (string) ($draft->{$draftField} ?? '')) {
                return $this->blocked('C171_TRADE_DIAGNOSTIC_PERSISTED_IDENTITY_MISMATCH', ['identity_field' => $evalField]);
            }
        }
        foreach (['eval_model','eval_model_hash','implementation_version','implementation_hash'] as $field) {
            if ((string) ($draft->{$field} ?? '') !== (string) ($expectedIdentity[$field] ?? '')) {
                return $this->blocked('C171_TRADE_DIAGNOSTIC_EXECUTION_IDENTITY_MISMATCH', ['identity_field' => $field]);
            }
        }

        $expectedManifest = $this->manifestFromEval((array) $eval);
        $databaseManifest = $this->officialEvidence->databaseManifest($evalId);
        if ($expectedManifest !== $databaseManifest) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_OFFICIAL_MANIFEST_MISMATCH', [
                'expected_manifest' => $expectedManifest,
                'database_manifest' => $databaseManifest,
            ]);
        }
        if ($this->canonicalGatesFromEval((array) $eval)['pass']) {
            return $this->blocked('C171_TRADE_DIAGNOSTIC_REQUIRES_FAILED_IS_BASELINE');
        }

        $oosBefore = DB::table('watchlist_bt_oos_eval_ws')->count();
        $directory = dirname($outputPath);
        $spoolDirectory = $directory.DIRECTORY_SEPARATOR.'.c171-trade-evidence-diagnostic-spool';
        $spoolRunKey = 'c171-trade-diagnostic-'.$evalId.'-'.sha1((string) $draft->params_hash);
        $this->cleanupSpool($spoolDirectory, $spoolRunKey);

        try {
            $runtime = $this->runtime->evaluateWindow(self::CANONICAL_IS_FROM, self::CANONICAL_IS_TO, [
                'paramset' => $runtimeParamset,
                'hard_market_data_to_date' => self::CANONICAL_IS_TO,
                'official_evidence_spool' => [
                    'enabled' => true,
                    'directory' => $spoolDirectory,
                    'run_key' => $spoolRunKey,
                ],
                'compact_replay_items' => true,
                'executed_at' => (string) ($options['executed_at'] ?? self::CANONICAL_IS_TO.'T23:59:59+07:00'),
            ]);
            if (! ($runtime['is_ready'] ?? false)) {
                return $this->blocked('C171_TRADE_DIAGNOSTIC_RUNTIME_NOT_READY', [
                    'runtime_reason_code' => $runtime['reason_code'] ?? null,
                    'diagnostics' => $runtime['diagnostics'] ?? [],
                ]);
            }

            $artifact = is_array($runtime['artifact'] ?? null) ? $runtime['artifact'] : [];
            $evaluations = is_array($artifact['metrics']['evaluated_trades'] ?? null)
                ? $artifact['metrics']['evaluated_trades'] : [];
            $trades = is_array($runtime['backtest_payload']['trades'] ?? null)
                ? $runtime['backtest_payload']['trades'] : [];
            $officialPicks = DB::table('watchlist_bt_picks_ws')
                ->where('eval_id', $evalId)
                ->orderBy('asof_eod_date')
                ->orderBy('ticker_id')
                ->get()
                ->map(function ($row): array { return (array) $row; })
                ->all();

            $analysis = $this->analyzeTradeEvidence(
                (array) $eval,
                $officialPicks,
                $trades,
                $evaluations,
                $runtimeParamset
            );
            if (! ($analysis['official_pick_parity']['pass'] ?? false)) {
                return $this->blocked('C171_TRADE_DIAGNOSTIC_REPRODUCTION_PARITY_FAILED', $analysis);
            }

            $oosAfter = DB::table('watchlist_bt_oos_eval_ws')->count();
            if ($oosAfter !== $oosBefore) {
                throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_OOS_MUTATION_FORBIDDEN');
            }

            $paths = $this->derivedPaths($outputPath);
            $detailedCsv = $this->writeCsv($paths['trades_csv'], $analysis['detailed_trades'], (bool) ($options['overwrite'] ?? false));
            $segmentsCsv = $this->writeCsv($paths['segments_csv'], $analysis['segment_rows'], (bool) ($options['overwrite'] ?? false));
            $anomaliesCsv = $this->writeCsv($paths['anomalies_csv'], $analysis['anomaly_rows'], (bool) ($options['overwrite'] ?? false));

            $result = [
                'run_code' => self::RUN_CODE,
                'phase_label' => self::RUN_CODE,
                'status' => 'C171_TRADE_EVIDENCE_DIAGNOSTIC_COMPLETED',
                'reason_code' => (string) $analysis['remediation_classification'],
                'approval_reference' => $approvalReference,
                'eval_id' => $evalId,
                'param_set_id' => $paramSetId,
                'bt_param_id' => $btParamId,
                'catalog_code' => $catalogCode,
                'params_hash' => (string) $draft->params_hash,
                'eval_model_hash' => (string) $draft->eval_model_hash,
                'implementation_version' => (string) $draft->implementation_version,
                'implementation_hash' => (string) $draft->implementation_hash,
                'is_from' => self::CANONICAL_IS_FROM,
                'is_to' => self::CANONICAL_IS_TO,
                'official_evidence_manifest' => $databaseManifest,
                'official_pick_parity' => $analysis['official_pick_parity'],
                'baseline_metrics' => $analysis['baseline_metrics'],
                'reproduced_metrics' => $analysis['reproduced_metrics'],
                'metrics_without_flagged_anomalies' => $analysis['metrics_without_flagged_anomalies'],
                'canonical_gates' => $analysis['canonical_gates'],
                'canonical_gates_without_flagged_anomalies' => $analysis['canonical_gates_without_flagged_anomalies'],
                'anomaly_summary' => $analysis['anomaly_summary'],
                'segment_highlights' => $analysis['segment_highlights'],
                'remediation_classification' => $analysis['remediation_classification'],
                'remediation_reasons' => $analysis['remediation_reasons'],
                'new_draft_design_allowed' => $analysis['remediation_classification'] === 'STRATEGY_QUALITY_FAILURE_CONFIRMED',
                'draft_paramset_created' => false,
                'draft_paramset_mutated' => false,
                'oos_runtime_invoked' => false,
                'oos_repository_invoked' => false,
                'oos_rows_before' => $oosBefore,
                'oos_rows_after' => $oosAfter,
                'paramset_promoted' => false,
                'plan_run_created' => false,
                'recommendation_persisted' => false,
                'confirm_mutated' => false,
                'production_activation_executed' => false,
                'controlled_rollout_executed' => false,
                'production_ready' => false,
                'next_recommendation' => $this->nextRecommendation((string) $analysis['remediation_classification']),
                'outputs' => [
                    'detailed_trades_csv' => $detailedCsv,
                    'segments_csv' => $segmentsCsv,
                    'anomalies_csv' => $anomaliesCsv,
                ],
            ];
            $result['artifact_hash'] = $this->identity->stableHash($result);
            $result['write'] = $this->writeJson($outputPath, $result, (bool) ($options['overwrite'] ?? false));

            return $result;
        } finally {
            $this->cleanupSpool($spoolDirectory, $spoolRunKey);
        }
    }

    public function analyzeTradeEvidence(
        array $eval,
        array $officialPicks,
        array $trades,
        array $evaluations,
        array $runtimeParamset
    ): array {
        $tradeIndex = [];
        foreach ($trades as $trade) {
            if (! is_array($trade)) continue;
            $tradeIndex[$this->key($trade['trade_date'] ?? null, $trade['ticker_id'] ?? null)] = $trade;
        }
        $detailed = [];
        foreach ($evaluations as $evaluation) {
            if (! is_array($evaluation)
                || ($evaluation['metrics_ready'] ?? false) !== true
                || ! in_array(strtoupper((string) ($evaluation['bucket_code'] ?? '')), ['TOP','TOP_PICKS'], true)
                || ! is_numeric($evaluation['ret_net'] ?? null)) {
                continue;
            }
            $trade = $tradeIndex[$this->key($evaluation['trade_date'] ?? null, $evaluation['ticker_id'] ?? null)] ?? [];
            $detailed[] = $this->detailedTradeRow($evaluation, $trade);
        }
        usort($detailed, function (array $a, array $b): int {
            return [$a['trade_date'], (int) $a['ticker_id']] <=> [$b['trade_date'], (int) $b['ticker_id']];
        });

        $parity = $this->officialPickParity($officialPicks, $detailed);
        $thresholds = $this->thresholds($runtimeParamset);
        $reproduced = $this->metrics($detailed);
        $anomalies = array_values(array_filter($detailed, function (array $row): bool {
            return (bool) $row['requires_market_data_review'];
        }));
        $cleaned = array_values(array_filter($detailed, function (array $row): bool {
            return ! (bool) $row['requires_market_data_review'];
        }));
        $cleanedMetrics = $this->metrics($cleaned);
        $baselineMetrics = $this->metricsFromEval($eval);
        $baselineGates = $this->gates($baselineMetrics, $thresholds);
        $cleanedGates = $this->gates($cleanedMetrics, $thresholds);

        $classification = 'STRATEGY_QUALITY_FAILURE_CONFIRMED';
        $reasons = [];
        if ($anomalies !== [] && $cleanedGates['pass']) {
            $classification = 'DATA_QUALITY_REVIEW_REQUIRED';
            $reasons[] = 'Canonical performance gates pass only after removing price-discontinuity rows; Market Data/corporate-action review must precede strategy redesign.';
        } elseif ($anomalies !== [] && $this->materiallyImproved($baselineMetrics, $cleanedMetrics)) {
            $classification = 'MIXED_DATA_AND_STRATEGY_REMEDIATION_REQUIRED';
            $reasons[] = 'Flagged discontinuities materially affect results, but the cleaned population still fails one or more canonical performance gates.';
        } else {
            $reasons[] = 'Coverage and trade-count gates pass, while robust return, downside, and monthly-stability gates remain failed after anomaly isolation.';
        }
        if (! $parity['pass']) $reasons[] = 'Reproduced trades do not match immutable official picks.';

        $segments = $this->segments($detailed);

        return [
            'official_pick_parity' => $parity,
            'baseline_metrics' => $baselineMetrics,
            'reproduced_metrics' => $reproduced,
            'metrics_without_flagged_anomalies' => $cleanedMetrics,
            'canonical_gates' => $baselineGates,
            'canonical_gates_without_flagged_anomalies' => $cleanedGates,
            'anomaly_summary' => [
                'flagged_count' => count($anomalies),
                'extreme_loss_count' => count(array_filter($detailed, fn(array $r): bool => (bool) $r['extreme_loss_flag'])),
                'extreme_gain_count' => count(array_filter($detailed, fn(array $r): bool => (bool) $r['extreme_gain_flag'])),
                'price_discontinuity_count' => count(array_filter($detailed, fn(array $r): bool => (bool) $r['price_discontinuity_flag'])),
                'gap_detected_count' => count(array_filter($detailed, fn(array $r): bool => (bool) $r['gap_detected'])),
                'gap_through_count' => count(array_filter($detailed, fn(array $r): bool => (bool) $r['gap_through_flag'])),
            ],
            'segment_rows' => $segments,
            'segment_highlights' => $this->segmentHighlights($segments),
            'detailed_trades' => $detailed,
            'anomaly_rows' => $anomalies,
            'remediation_classification' => $classification,
            'remediation_reasons' => $reasons,
        ];
    }

    private function detailedTradeRow(array $evaluation, array $trade): array
    {
        $entry = $this->floatOrNull($evaluation['entry_price'] ?? null);
        $exit = $this->floatOrNull($evaluation['exit_price'] ?? $evaluation['executed_price'] ?? null);
        $ret = round((float) $evaluation['ret_net'], 6);
        $ratio = ($entry !== null && $entry > 0 && $exit !== null) ? $exit / $entry : null;
        $extremeLoss = $ret <= -0.50;
        $extremeGain = $ret >= 0.50;
        $discontinuity = $ratio !== null && ($ratio < 0.50 || $ratio > 1.50);
        $fillRule = (string) ($evaluation['fill_rule'] ?? '');
        $gapThrough = strpos($fillRule, 'GAP_THROUGH_') === 0;

        return [
            'trade_date' => (string) ($evaluation['trade_date'] ?? ''),
            'entry_trade_date' => (string) ($evaluation['entry_trade_date'] ?? ''),
            'exit_trade_date' => (string) ($evaluation['exit_trade_date'] ?? ''),
            'ticker_id' => (int) ($evaluation['ticker_id'] ?? 0),
            'ticker_code' => (string) ($evaluation['ticker'] ?? ''),
            'bucket_code' => (string) ($evaluation['bucket_code'] ?? ''),
            'score_total' => round((float) ($trade['score_total'] ?? 0), 6),
            'score_momentum' => $this->floatOrNull($trade['score_momentum'] ?? null),
            'score_volume' => $this->floatOrNull($trade['score_volume'] ?? null),
            'score_breakout' => $this->floatOrNull($trade['score_breakout'] ?? null),
            'score_risk' => $this->floatOrNull($trade['score_risk'] ?? null),
            'dv20_idr' => $this->intOrNull($trade['dv20_idr'] ?? null),
            'atr14_pct' => $this->floatOrNull($trade['atr14_pct'] ?? $evaluation['atr14_pct'] ?? null),
            'vol_ratio' => $this->floatOrNull($trade['vol_ratio'] ?? null),
            'roc20' => $this->floatOrNull($trade['roc20'] ?? null),
            'close_to_hh20_pct' => $this->floatOrNull($trade['close_to_hh20_pct'] ?? null),
            'sector_code' => (string) ($trade['sector_code'] ?? 'UNKNOWN'),
            'exit_reason_code' => (string) ($evaluation['exit_reason_code'] ?? ''),
            'fill_rule' => $fillRule,
            'gap_detected' => (bool) ($evaluation['gap_detected'] ?? false),
            'entry_price' => $entry,
            'exit_price' => $exit,
            'price_ratio' => $ratio !== null ? round($ratio, 6) : null,
            'entry_volume' => $this->intOrNull($evaluation['entry_volume'] ?? null),
            'exit_volume' => $this->intOrNull($evaluation['exit_volume'] ?? null),
            'ret_net' => $ret,
            'is_win' => $ret > 0,
            'net_pnl_idr' => $this->floatOrNull($evaluation['net_pnl_idr'] ?? null),
            'source_publication_id' => $this->intOrNull($evaluation['entry_publication_id'] ?? ($trade['source_reference']['publication_id'] ?? null)),
            'source_publication_version' => $this->intOrNull($evaluation['entry_publication_version'] ?? ($trade['source_reference']['publication_version'] ?? null)),
            'source_run_id' => $this->intOrNull($evaluation['entry_run_id'] ?? ($trade['source_reference']['run_id'] ?? null)),
            'extreme_loss_flag' => $extremeLoss,
            'extreme_gain_flag' => $extremeGain,
            'price_discontinuity_flag' => $discontinuity,
            'gap_through_flag' => $gapThrough,
            'requires_market_data_review' => $extremeLoss || $extremeGain || $discontinuity,
            'market_data_review_reason' => implode('|', array_keys(array_filter([
                'EXTREME_LOSS' => $extremeLoss,
                'EXTREME_GAIN' => $extremeGain,
                'PRICE_DISCONTINUITY' => $discontinuity,
            ]))),
        ];
    }

    private function officialPickParity(array $official, array $reproduced): array
    {
        $mismatches = [];
        if (count($official) !== count($reproduced)) {
            $mismatches[] = 'COUNT_MISMATCH';
        }
        $officialIndex = [];
        foreach ($official as $row) {
            $row = (array) $row;
            $officialIndex[$this->key($row['asof_eod_date'] ?? null, $row['ticker_id'] ?? null)] = $row;
        }
        foreach ($reproduced as $row) {
            $key = $this->key($row['trade_date'], $row['ticker_id']);
            $expected = $officialIndex[$key] ?? null;
            if ($expected === null) {
                $mismatches[] = 'MISSING_OFFICIAL:'.$key;
                continue;
            }
            foreach (['ticker_code','source_publication_id','source_publication_version','source_run_id'] as $field) {
                if ((string) ($expected[$field] ?? '') !== (string) ($row[$field] ?? '')) {
                    $mismatches[] = strtoupper($field).'_MISMATCH:'.$key;
                }
            }
            foreach (['ret_net','score_total'] as $field) {
                if (abs((float) ($expected[$field] ?? 0) - (float) ($row[$field] ?? 0)) > 0.000001) {
                    $mismatches[] = strtoupper($field).'_MISMATCH:'.$key;
                }
            }
        }
        $mismatches = array_values(array_unique($mismatches));
        return [
            'pass' => $mismatches === [],
            'official_count' => count($official),
            'reproduced_count' => count($reproduced),
            'mismatch_count' => count($mismatches),
            'mismatch_sample' => array_slice($mismatches, 0, 25),
        ];
    }

    private function segments(array $rows): array
    {
        $axes = [
            'month' => fn(array $r): string => substr($r['trade_date'], 0, 7),
            'ticker' => fn(array $r): string => $r['ticker_code'],
            'exit_reason' => fn(array $r): string => $r['exit_reason_code'],
            'gap_detected' => fn(array $r): string => $r['gap_detected'] ? 'YES' : 'NO',
            'entry_price_band' => fn(array $r): string => $this->priceBand($r['entry_price']),
            'score_decile' => fn(array $r): string => $this->scoreDecile($r['score_total'], $rows),
            'dv20_band' => fn(array $r): string => $this->dv20Band($r['dv20_idr']),
            'atr14_band' => fn(array $r): string => $this->atrBand($r['atr14_pct']),
            'vol_ratio_band' => fn(array $r): string => $this->volRatioBand($r['vol_ratio']),
            'roc20_band' => fn(array $r): string => $this->percentBand($r['roc20'], [-0.10,-0.05,0,0.02,0.05,0.10], ['LT_-10%','-10_TO_-5%','-5_TO_0%','0_TO_2%','2_TO_5%','5_TO_10%','GE_10%']),
            'close_to_hh20_band' => fn(array $r): string => $this->percentBand($r['close_to_hh20_pct'], [-0.10,-0.05,-0.02,0,0.02], ['LT_-10%','-10_TO_-5%','-5_TO_-2%','-2_TO_0%','0_TO_2%','GE_2%']),
            'sector' => fn(array $r): string => $r['sector_code'] !== '' ? $r['sector_code'] : 'UNKNOWN',
        ];
        $out = [];
        foreach ($axes as $axis => $resolver) {
            $groups = [];
            foreach ($rows as $row) $groups[$resolver($row)][] = $row;
            ksort($groups, SORT_STRING);
            foreach ($groups as $segment => $group) {
                $m = $this->metrics($group);
                $out[] = array_merge(['axis' => $axis, 'segment' => $segment], $m, [
                    'return_contribution' => round(array_sum(array_column($group, 'ret_net')), 6),
                    'anomaly_count' => count(array_filter($group, fn(array $r): bool => (bool) $r['requires_market_data_review'])),
                ]);
            }
        }
        return $out;
    }

    private function segmentHighlights(array $segments): array
    {
        $byAxis = [];
        foreach ($segments as $row) $byAxis[$row['axis']][] = $row;
        $result = [];
        foreach ($byAxis as $axis => $rows) {
            $eligible = array_values(array_filter($rows, fn(array $r): bool => (int) $r['trade_count'] >= 10));
            if ($eligible === []) continue;
            usort($eligible, fn(array $a, array $b): int => $a['avg_ret_net'] <=> $b['avg_ret_net']);
            $result[$axis] = [
                'worst' => array_intersect_key($eligible[0], array_flip(['segment','trade_count','avg_ret_net','median_ret_net','p25_ret_net','win_rate','anomaly_count'])),
                'best' => array_intersect_key($eligible[count($eligible)-1], array_flip(['segment','trade_count','avg_ret_net','median_ret_net','p25_ret_net','win_rate','anomaly_count'])),
            ];
        }
        return $result;
    }

    private function metrics(array $rows): array
    {
        $returns = array_map(fn(array $r): float => (float) $r['ret_net'], $rows);
        sort($returns, SORT_NUMERIC);
        $count = count($returns);
        $months = [];
        foreach ($rows as $row) $months[substr($row['trade_date'], 0, 7)][] = (float) $row['ret_net'];
        $monthWins = [];
        $monthAvgs = [];
        foreach ($months as $monthReturns) {
            $monthWins[] = count(array_filter($monthReturns, fn(float $v): bool => $v > 0)) / count($monthReturns);
            $monthAvgs[] = array_sum($monthReturns) / count($monthReturns);
        }
        return [
            'trade_count' => $count,
            'days_covered' => count(array_unique(array_column($rows, 'trade_date'))),
            'avg_ret_net' => $count ? array_sum($returns) / $count : null,
            'win_rate' => $count ? count(array_filter($returns, fn(float $v): bool => $v > 0)) / $count : null,
            'median_ret_net' => $this->percentile($returns, 0.50),
            'p25_ret_net' => $this->percentile($returns, 0.25),
            'p75_ret_net' => $this->percentile($returns, 0.75),
            'min_ret_net' => $count ? min($returns) : null,
            'max_ret_net' => $count ? max($returns) : null,
            'month_win_rate_min' => $monthWins ? min($monthWins) : null,
            'month_avg_ret_net_min' => $monthAvgs ? min($monthAvgs) : null,
            'periods_count' => count($months),
        ];
    }

    private function metricsFromEval(array $eval): array
    {
        return [
            'trade_count' => (int) ($eval['picks_count'] ?? 0),
            'days_covered' => (int) ($eval['days_covered'] ?? 0),
            'avg_ret_net' => (float) ($eval['avg_ret_net_top'] ?? 0),
            'win_rate' => (float) ($eval['win_rate_top'] ?? 0),
            'median_ret_net' => (float) ($eval['median_ret_net_top'] ?? 0),
            'p25_ret_net' => (float) ($eval['p25_ret_net_top'] ?? 0),
            'p75_ret_net' => (float) ($eval['p75_ret_net_top'] ?? 0),
            'min_ret_net' => (float) ($eval['min_ret_net_top'] ?? 0),
            'max_ret_net' => (float) ($eval['max_ret_net_top'] ?? 0),
            'month_win_rate_min' => (float) ($eval['month_win_rate_min'] ?? 0),
            'month_avg_ret_net_min' => (float) ($eval['month_avg_ret_net_min'] ?? 0),
            'periods_count' => (int) ($eval['periods_count'] ?? 0),
            'period_fail_count' => (int) ($eval['period_fail_count'] ?? 0),
        ];
    }

    private function thresholds(array $paramset): array
    {
        $eval = is_array($paramset['eval'] ?? null) ? $paramset['eval'] : [];
        return [
            'min_trades' => max(120, (int) ($eval['min_trades'] ?? 120)),
            'min_days_covered' => max(390, (int) ($eval['min_days_covered'] ?? 390)),
            'min_p25_ret_net' => (float) ($eval['min_p25_ret_net_top'] ?? -0.03),
            'min_month_win_rate' => (float) ($eval['min_month_win_rate_min'] ?? 0.45),
            'min_month_avg_ret_net' => (float) ($eval['min_month_avg_ret_net_min'] ?? -0.01),
        ];
    }

    private function gates(array $metrics, array $thresholds): array
    {
        $gates = [
            'minimum_trade_count' => (int) ($metrics['trade_count'] ?? 0) >= $thresholds['min_trades'],
            'minimum_coverage' => (int) ($metrics['days_covered'] ?? 0) >= $thresholds['min_days_covered'],
            'average_return_positive' => ($metrics['avg_ret_net'] ?? null) !== null && $metrics['avg_ret_net'] > 0,
            'median_return_non_negative' => ($metrics['median_ret_net'] ?? null) !== null && $metrics['median_ret_net'] >= 0,
            'p25_downside_bound' => ($metrics['p25_ret_net'] ?? null) !== null && $metrics['p25_ret_net'] >= $thresholds['min_p25_ret_net'],
            'monthly_win_rate_floor' => ($metrics['month_win_rate_min'] ?? null) !== null && $metrics['month_win_rate_min'] >= $thresholds['min_month_win_rate'],
            'monthly_average_floor' => ($metrics['month_avg_ret_net_min'] ?? null) !== null && $metrics['month_avg_ret_net_min'] >= $thresholds['min_month_avg_ret_net'],
        ];
        return ['pass' => ! in_array(false, $gates, true), 'gates' => $gates, 'thresholds' => $thresholds];
    }

    private function canonicalGatesFromEval(array $eval): array
    {
        return $this->gates($this->metricsFromEval($eval), [
            'min_trades' => 120,
            'min_days_covered' => 390,
            'min_p25_ret_net' => -0.03,
            'min_month_win_rate' => 0.45,
            'min_month_avg_ret_net' => -0.01,
        ]);
    }

    private function materiallyImproved(array $before, array $after): bool
    {
        return (($after['avg_ret_net'] ?? -INF) - ($before['avg_ret_net'] ?? 0)) >= 0.002
            || (($after['median_ret_net'] ?? -INF) - ($before['median_ret_net'] ?? 0)) >= 0.01
            || (($after['p25_ret_net'] ?? -INF) - ($before['p25_ret_net'] ?? 0)) >= 0.01;
    }

    private function manifestFromEval(array $eval): array
    {
        return [
            'schema_version' => 'WS_OFFICIAL_IS_EVIDENCE_C171_V1',
            'picks_count' => (int) ($eval['picks_count'] ?? 0),
            'picks_hash' => (string) ($eval['picks_hash'] ?? ''),
            'universe_count' => (int) ($eval['universe_count'] ?? 0),
            'universe_hash' => (string) ($eval['universe_hash'] ?? ''),
            'cutoff_count' => (int) ($eval['cutoff_count'] ?? 0),
            'cutoffs_hash' => (string) ($eval['cutoffs_hash'] ?? ''),
            'market_data_lineage_hash' => (string) ($eval['market_data_lineage_hash'] ?? ''),
            'evidence_manifest_hash' => (string) ($eval['evidence_manifest_hash'] ?? ''),
        ];
    }

    private function derivedPaths(string $outputPath): array
    {
        $base = preg_replace('/\.json$/i', '', $outputPath) ?: $outputPath;
        return [
            'trades_csv' => $base.'-trades.csv',
            'segments_csv' => $base.'-segments.csv',
            'anomalies_csv' => $base.'-anomalies.csv',
        ];
    }

    private function writeCsv(string $path, array $rows, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_OUTPUT_EXISTS_USE_OVERWRITE: '.$path);
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_OUTPUT_DIRECTORY_CREATE_FAILED');
        $temp = $path.'.tmp.'.getmypid();
        $handle = fopen($temp, 'wb');
        if ($handle === false) throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_CSV_OPEN_FAILED');
        try {
            if ($rows !== []) {
                fputcsv($handle, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, array_map(function ($value) {
                        if (is_bool($value)) return $value ? 1 : 0;
                        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_SLASHES);
                        return $value;
                    }, $row));
                }
            }
        } finally { fclose($handle); }
        if (! rename($temp, $path)) { @unlink($temp); throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_CSV_RENAME_FAILED'); }
        return ['status' => 'WRITTEN', 'path' => $path, 'row_count' => count($rows), 'file_sha1' => sha1_file($path)];
    }

    private function writeJson(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_OUTPUT_EXISTS_USE_OVERWRITE');
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_OUTPUT_DIRECTORY_CREATE_FAILED');
        $temp = $path.'.tmp.'.getmypid();
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        if ($json === false || file_put_contents($temp, $json, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp); throw new \RuntimeException('C171_TRADE_DIAGNOSTIC_ARTIFACT_WRITE_FAILED');
        }
        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1_file($path)];
    }

    private function cleanupSpool(string $directory, string $runKey): void
    {
        if (! is_dir($directory)) return;
        foreach (glob($directory.DIRECTORY_SEPARATOR.$runKey.'.*') ?: [] as $path) {
            if (is_file($path)) @unlink($path);
        }
    }

    private function nextRecommendation(string $classification): string
    {
        if ($classification === 'DATA_QUALITY_REVIEW_REQUIRED') return 'C171_MARKET_DATA_CORPORATE_ACTION_AND_PRICE_DISCONTINUITY_REMEDIATION';
        if ($classification === 'MIXED_DATA_AND_STRATEGY_REMEDIATION_REQUIRED') return 'C171_MARKET_DATA_REVIEW_THEN_IMMUTABLE_DRAFT_STRATEGY_REDESIGN';
        return 'C171_DESIGN_NEW_IMMUTABLE_DRAFT_PARAMSET_CANDIDATES_FROM_DIAGNOSTIC';
    }

    private function priceBand($value): string
    {
        if (! is_numeric($value)) return 'NULL';
        $v = (float) $value;
        if ($v < 200) return 'LT_200';
        if ($v < 500) return '200_TO_499';
        if ($v < 1000) return '500_TO_999';
        if ($v < 5000) return '1000_TO_4999';
        if ($v < 10000) return '5000_TO_9999';
        return 'GE_10000';
    }

    private function scoreDecile($value, array $rows): string
    {
        if (! is_numeric($value)) return 'NULL';
        $scores = array_values(array_filter(array_column($rows, 'score_total'), 'is_numeric'));
        sort($scores, SORT_NUMERIC);
        if ($scores === []) return 'NULL';
        $rank = 0;
        foreach ($scores as $score) { if ($score <= (float) $value) $rank++; else break; }
        $decile = min(10, max(1, (int) ceil(($rank / count($scores)) * 10)));
        return 'D'.str_pad((string) $decile, 2, '0', STR_PAD_LEFT);
    }

    private function dv20Band($value): string
    {
        if (! is_numeric($value)) return 'NULL';
        $v = (float) $value;
        if ($v < 1000000000) return 'LT_1B';
        if ($v < 2500000000) return '1B_TO_2_5B';
        if ($v < 5000000000) return '2_5B_TO_5B';
        if ($v < 10000000000) return '5B_TO_10B';
        if ($v < 50000000000) return '10B_TO_50B';
        return 'GE_50B';
    }

    private function atrBand($value): string
    {
        if (! is_numeric($value)) return 'NULL';
        $v = (float) $value;
        if ($v < 0.02) return 'LT_2%';
        if ($v < 0.04) return '2_TO_4%';
        if ($v < 0.06) return '4_TO_6%';
        if ($v < 0.10) return '6_TO_10%';
        return 'GE_10%';
    }

    private function volRatioBand($value): string
    {
        if (! is_numeric($value)) return 'NULL';
        $v = (float) $value;
        if ($v < 0.5) return 'LT_0_5';
        if ($v < 1) return '0_5_TO_1';
        if ($v < 1.5) return '1_TO_1_5';
        if ($v < 2) return '1_5_TO_2';
        if ($v < 3) return '2_TO_3';
        if ($v < 5) return '3_TO_5';
        return 'GE_5';
    }

    private function percentBand($value, array $bounds, array $labels): string
    {
        if (! is_numeric($value)) return 'NULL';
        $v = (float) $value;
        foreach ($bounds as $index => $bound) if ($v < $bound) return $labels[$index];
        return $labels[count($labels)-1];
    }

    private function percentile(array $sorted, float $p): ?float
    {
        $count = count($sorted);
        if ($count === 0) return null;
        if ($count === 1) return (float) $sorted[0];
        $position = ($count - 1) * $p;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) return (float) $sorted[$lower];
        $weight = $position - $lower;
        return ((float) $sorted[$lower] * (1 - $weight)) + ((float) $sorted[$upper] * $weight);
    }

    private function key($date, $tickerId): string { return (string) $date.'|'.(string) $tickerId; }
    private function floatOrNull($value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function intOrNull($value): ?int { return is_numeric($value) ? (int) $value : null; }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'draft_paramset_created' => false,
            'draft_paramset_mutated' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_activation_executed' => false,
            'controlled_rollout_executed' => false,
            'production_ready' => false,
        ], $context);
    }
}
