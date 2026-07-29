<?php

namespace App\Application\Watchlist\Services;

class WeeklySwingParamsetRuntimeAdapter
{
    private const AUDIT_KEYS = ['value', 'origin', 'status', 'bt_target', 'rationale', 'change_triggers'];

    public function adapt(array $canonicalPayload): array
    {
        $runtime = $this->unwrap($canonicalPayload);
        if (! is_array($runtime)) {
            throw new \RuntimeException('WS_C171_PARAMSET_RUNTIME_ADAPTER_INVALID: canonical payload did not resolve to an array.');
        }

        $runtime = $this->adaptScoringContract($runtime);
        $runtime = $this->adaptResearchExecutionContract($runtime);

        return $this->adaptGroupingContract($runtime);
    }

    private function adaptResearchExecutionContract(array $runtime): array
    {
        if (! array_key_exists('research_execution', $runtime)) {
            return $runtime;
        }
        $expected = WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();
        $s01LossContainment = WatchlistBacktestTailRiskS01ParamGridCatalog::lossContainmentExecution();
        $s01Remediation =
            WatchlistBacktestTailRiskS01RemediationParamGridCatalog::researchExecution();
        $p01Remediation =
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchExecution();
        $backtest = is_array($runtime['backtest'] ?? null) ? $runtime['backtest'] : [];
        if ($runtime['research_execution'] == $expected) {
            $backtest['exit_model'] = 'WS_R02_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN_TIME';
            $backtest['research_execution'] = $expected;
        } elseif ($runtime['research_execution'] == $s01LossContainment) {
            $backtest['exit_model'] = 'WS_S01_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEXT_OPEN_TIME';
            $backtest['research_execution'] = $s01LossContainment;
        } elseif ($runtime['research_execution'] == $s01Remediation) {
            $backtest['exit_model'] =
                'WS_S01M1_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEG1_NEXT_OPEN_TIME';
            $backtest['research_execution'] = $s01Remediation;
        } elseif ($runtime['research_execution'] == $p01Remediation) {
            $backtest['exit_model'] =
                'WS_S01M1_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEG1_NEXT_OPEN_TIME';
            $backtest['research_execution'] = $p01Remediation;
        } else {
            throw new \RuntimeException(
                'WS_NEW_STRATEGY_RESEARCH_EXECUTION_INVALID: the execution contract is not an approved immutable research rule.'
            );
        }
        $runtime['backtest'] = $backtest;

        return $runtime;
    }

    private function adaptScoringContract(array $runtime): array
    {
        $scoring = is_array($runtime['scoring'] ?? null) ? $runtime['scoring'] : [];
        $canonicalMode = (string) ($scoring['combine_mode'] ?? '');
        if ($canonicalMode !== 'NORM_WEIGHTED_SUM_CLAMP01') {
            throw new \RuntimeException(
                'WS_C171_PARAMSET_RUNTIME_ADAPTER_SCORING_MODE_INVALID: canonical scoring mode must be NORM_WEIGHTED_SUM_CLAMP01.'
            );
        }

        // The owner contract names the mathematical operation, while the
        // existing scoring service names its implementation WEIGHTED_MEAN.
        // The runtime computes weighted sum / sum(weights) and clamps to 0..1.
        // Canonical validation fixes sum(weights) to 1.0, so this label mapping
        // preserves the exact scoring formula and does not alter any weight.
        $scoring['combine_mode'] = 'WEIGHTED_MEAN';
        $runtime['scoring'] = $scoring;

        return $runtime;
    }

    private function adaptGroupingContract(array $runtime): array
    {
        $grouping = is_array($runtime['grouping'] ?? null) ? $runtime['grouping'] : [];
        $canonicalMode = (string) ($grouping['grouping_mode'] ?? '');
        if ($canonicalMode !== 'QUALIFIED_POOLS_QUANTILE_CUTOFF') {
            throw new \RuntimeException(
                'WS_C171_PARAMSET_RUNTIME_ADAPTER_GROUPING_MODE_INVALID: canonical grouping mode must be QUALIFIED_POOLS_QUANTILE_CUTOFF.'
            );
        }

        $topTarget = $this->positiveInteger($grouping['top_picks_target'] ?? null, 'grouping.top_picks_target');
        $secondaryTarget = $this->positiveInteger($grouping['secondary_target'] ?? null, 'grouping.secondary_target');
        $defaults = WatchlistPlanGroupingService::defaultParamset()['grouping'];

        // The owner JSON uses the external canonical name while the existing
        // PLAN service uses an internal deterministic execution mode. Quantile
        // values remain authoritative; only the internal mode label and target
        // shape are adapted for the already-existing runtime contract.
        $grouping['grouping_mode'] = 'PLAN_GROUPING_DETERMINISTIC';
        $topMaxScoreTotal = $this->optionalScore($grouping['top_max_score_total'] ?? null);
        $grouping['top_picks'] = [
            'min_score_total' => $defaults['top_picks']['min_score_total'],
            'max_score_total' => $topMaxScoreTotal ?? ($defaults['top_picks']['max_score_total'] ?? 1.0),
            'max_items' => $topTarget,
        ];
        unset($grouping['top_max_score_total']);
        $grouping['secondary'] = [
            'min_score_total' => $defaults['secondary']['min_score_total'],
            'max_items' => $secondaryTarget,
        ];
        $grouping['watch_only'] = $defaults['watch_only'];
        $grouping['avoid'] = $defaults['avoid'];
        $grouping['sort_keys'] = is_array($grouping['sort_keys'] ?? null)
            ? $grouping['sort_keys']
            : $defaults['sort_keys'];

        $runtime['grouping'] = $grouping;

        return $runtime;
    }

    private function optionalScore($value): ?float
    {
        if ($value === null) {
            return null;
        }
        if ((! is_int($value) && ! is_float($value)) || (float) $value < 0.0 || (float) $value > 1.0) {
            throw new \RuntimeException(
                'WS_C171_PARAMSET_RUNTIME_ADAPTER_TOP_MAX_SCORE_INVALID: grouping.top_max_score_total must be null or numeric within 0..1.'
            );
        }

        return (float) $value;
    }

    private function positiveInteger($value, string $path): int
    {
        if (! is_int($value) || $value <= 0) {
            throw new \RuntimeException(
                'WS_C171_PARAMSET_RUNTIME_ADAPTER_TARGET_INVALID: '.$path.' must be integer > 0.'
            );
        }

        return $value;
    }

    private function unwrap($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($this->isAuditNode($value)) {
            return $this->unwrap($value['value']);
        }
        $result = [];
        foreach ($value as $key => $item) {
            $result[$key] = $this->unwrap($item);
        }
        return $result;
    }

    private function isAuditNode(array $value): bool
    {
        if (! array_key_exists('value', $value)) {
            return false;
        }
        foreach (array_keys($value) as $key) {
            if (! in_array($key, self::AUDIT_KEYS, true)) {
                return false;
            }
        }
        return true;
    }
}
