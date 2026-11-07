<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\FreshnessState;
use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;

/**
 * Evaluate the locked five-session decision rules over an explicitly verified
 * calendar timeline. The result is a conditional assessment of supplied facts,
 * not an operational activation record or a publication freshness label.
 */
final class MarketDataFreshnessEvaluator
{
    public const HORIZON_EXPECTED_SESSIONS = 5;
    public const SOURCE_EVALUATION_FAILURES = self::HORIZON_EXPECTED_SESSIONS;
    public const ACCEPTABLE_EXPECTED_SESSION_LAG = 1;
    public const BREACH_EXPECTED_SESSION_LAG = 2;

    /**
     * Resolve the session sequence from the governed calendar revision set.
     * This remains a conditional assessment: the supplied gateway fact is not
     * itself authenticated and a configured marker is not activation proof.
     */
    public function evaluateFromGovernedCalendar(array $facts): array
    {
        if (array_key_exists('expected_sessions', $facts)
            || array_key_exists('operational_start_date', $facts)) {
            return $this->unavailable('GOVERNED_INPUT_OVERRIDE_PROHIBITED');
        }
        $requested = $this->date($facts['requested_date'] ?? null);
        $asOf = $this->instant($facts['as_of'] ?? null);
        if ($requested === null) return $this->unavailable('REQUESTED_DATE_INVALID');
        if ($asOf === null) return $this->unavailable('MEASUREMENT_TIME_INVALID');
        $localAsOf = $asOf->setTimezone(new \DateTimeZone('Asia/Jakarta'));
        if ($localAsOf->format('Y-m-d') < $requested) {
            return $this->unavailable('MEASUREMENT_BEFORE_REQUESTED_DATE');
        }
        $facts['operational_start_date'] = MarketDataScope::fromConfig()->operationalStartDate();
        if (! FreshnessState::isInForce($facts['operational_start_date'], $requested)) {
            return $this->evaluate($facts);
        }

        $calendar = new MarketCalendarRepository();
        $knownAt = $localAsOf->format('Y-m-d H:i:s');
        try {
            // The acquisition-failure horizon may begin before the requested
            // publication date. Resolve from activation so earlier failed
            // expected sessions are not silently discarded.
            $contexts = $calendar->completedExpectedSessionsBetween(
                min($requested, $facts['operational_start_date']), $localAsOf->format('Y-m-d'), $knownAt
            );
            $sessions = [];
            foreach ($contexts as $context) {
                $date = $context['trade_date'];
                $sessions[] = [
                    'trade_date' => $date,
                    'completed_at' => \Carbon\Carbon::parse($context['completed_at'], 'Asia/Jakarta')->toIso8601String(),
                    'cutoff_at' => \Carbon\Carbon::parse($date.' '.config('market_data.platform.cutoff_time'), 'Asia/Jakarta')->toIso8601String(),
                    'calendar_state' => $context['session_state'],
                    'provenance_tier' => $context['provenance_tier'],
                    'calendar_revision_id' => $context['calendar_revision_id'],
                    'source_ref' => $context['source_ref'],
                ];
            }
        } catch (\Throwable $error) {
            return $this->unavailable('GOVERNED_EXPECTED_SESSIONS_UNVERIFIED');
        }
        $facts['expected_sessions'] = $sessions;
        $result = $this->evaluate($facts);
        $result['calendar_revision_ids'] = array_column($sessions, 'calendar_revision_id', 'trade_date');
        $result['calendar_source_refs'] = array_column($sessions, 'source_ref', 'trade_date');
        $result['calendar_binding'] = 'GOVERNED_CURRENT_REVISION_AS_KNOWN_AT_ASSESSMENT';
        return $result;
    }

    public function evaluate(array $facts): array
    {
        $requested = $this->date($facts['requested_date'] ?? null);
        $activation = $this->date($facts['operational_start_date'] ?? null, true);
        if ($requested === null) {
            return $this->unavailable('REQUESTED_DATE_INVALID');
        }
        if ($activation === false) {
            return $this->unavailable('OPERATIONAL_START_DATE_INVALID');
        }
        if (! FreshnessState::isInForce($activation, $requested)) {
            return [
                'assessment' => 'NOT_APPLICABLE', 'freshness_target' => null,
                'source_evaluation' => null, 'expected_session_lag' => null,
                'reason' => 'PRE_ACTIVATION_FRONTIER',
                'claim_limit' => $this->claimLimit(),
            ];
        }

        // These are one locked, horizon-derived policy. Supplied facts cannot
        // redefine one threshold independently or silently enlarge the horizon.
        $policy = [
            'horizon_expected_sessions' => self::HORIZON_EXPECTED_SESSIONS,
            'acceptable_lag_expected_sessions' => self::ACCEPTABLE_EXPECTED_SESSION_LAG,
            'breach_lag_expected_sessions' => self::BREACH_EXPECTED_SESSION_LAG,
            'source_failure_expected_sessions' => self::SOURCE_EVALUATION_FAILURES,
        ];
        if (array_values($policy) !== [5, 1, 2, 5]) return $this->unavailable('HORIZON_POLICY_REDERIVATION_REQUIRED');
        foreach ($policy as $key => $value) {
            if (array_key_exists($key, $facts) && $facts[$key] !== $value) {
                return $this->unavailable('HORIZON_POLICY_REDERIVATION_REQUIRED');
            }
        }

        $sessions = $facts['expected_sessions'] ?? null;
        if (! is_array($sessions) || $sessions === []) {
            return $this->unavailable('VERIFIED_EXPECTED_SESSIONS_MISSING');
        }
        $dates = [];
        $cutoffs = [];
        $completed = [];
        foreach ($sessions as $session) {
            if (! is_array($session)) return $this->unavailable('EXPECTED_SESSION_INVALID');
            $date = $this->date($session['trade_date'] ?? null);
            $cutoff = $this->instant($session['cutoff_at'] ?? null);
            $completion = $this->instant($session['completed_at'] ?? null);
            if ($date === null || $cutoff === null
                || $completion === null || $completion > $cutoff
                || ($session['calendar_state'] ?? null) !== 'COMPLETED'
                || ($session['provenance_tier'] ?? null) !== 'VERIFIED'
                || (int) ($session['calendar_revision_id'] ?? 0) <= 0
                || trim((string) ($session['source_ref'] ?? '')) === ''
                || $cutoff->format('Y-m-d') !== $date
                || $completion->format('Y-m-d') !== $date
                || isset($cutoffs[$date])) {
                return $this->unavailable('EXPECTED_SESSION_NOT_VERIFIED');
            }
            $dates[] = $date;
            $cutoffs[$date] = $cutoff;
            $completed[$date] = $completion;
        }
        if ($dates !== array_values(array_unique($dates)) || $dates !== $this->sorted($dates)) {
            return $this->unavailable('EXPECTED_SESSION_ORDER_INVALID');
        }
        $targetIndex = array_search($requested, $dates, true);
        if ($targetIndex === false) return $this->unavailable('REQUESTED_SESSION_NOT_EXPECTED');

        $asOf = $this->instant($facts['as_of'] ?? null);
        if ($asOf === null) return $this->unavailable('MEASUREMENT_TIME_INVALID');
        $readable = $this->instant($facts['gateway_verified_readable_at'] ?? null, true);
        if ($readable === false) return $this->unavailable('GATEWAY_READABILITY_TIME_INVALID');
        if ($readable !== null && ($facts['publication_verified'] ?? null) !== true) {
            return $this->unavailable('PUBLICATION_GATEWAY_IDENTITY_UNVERIFIED');
        }
        if ($readable !== null && $readable > $asOf) return $this->unavailable('READABILITY_AFTER_MEASUREMENT');
        if ($readable !== null && $readable < $completed[$requested]) {
            return $this->unavailable('READABILITY_BEFORE_SESSION_COMPLETION');
        }

        // Once the publication is readable, its acquisition-to-readability lag
        // is fixed. A later assessment must not turn that lag into publication age.
        $lagEndpoint = $readable ?? $asOf;
        $lag = 0;
        foreach ($dates as $index => $date) {
            if ($index > $targetIndex && $completed[$date] <= $lagEndpoint) {
                $lag = $index - $targetIndex;
            }
        }
        $target = 'PENDING';
        if ($readable !== null && $readable <= $cutoffs[$requested]) {
            $target = 'ON_TARGET';
        } elseif (isset($dates[$targetIndex + self::ACCEPTABLE_EXPECTED_SESSION_LAG])
            && $readable !== null && $readable <= $cutoffs[$dates[$targetIndex + self::ACCEPTABLE_EXPECTED_SESSION_LAG]]) {
            $target = 'ACCEPTABLE';
        } elseif (isset($dates[$targetIndex + self::BREACH_EXPECTED_SESSION_LAG])
            && $cutoffs[$dates[$targetIndex + self::BREACH_EXPECTED_SESSION_LAG]] <= $asOf
            && ($readable === null || $readable > $cutoffs[$dates[$targetIndex + self::BREACH_EXPECTED_SESSION_LAG]])) {
            $target = 'BREACH';
        } elseif ($readable !== null) {
            // The locked table does not assign an ON_TARGET/ACCEPTABLE label to
            // a publication after the next session cutoff but before the second.
            $target = 'LATE_UNCLASSIFIED';
        }

        $outcomes = is_array($facts['acquisition_outcomes'] ?? null) ? $facts['acquisition_outcomes'] : [];
        $failureStreak = 0; $failedDates = [];
        $sourceEvaluation = 'NOT_TRIGGERED';
        for ($index = count($dates) - 1; $index >= 0; $index--) {
            $date = $dates[$index];
            if ($date < $activation || $cutoffs[$date] > $asOf) continue;
            $outcome = $outcomes[$date] ?? null;
            if ($outcome === 'SUCCESS') break;
            if ($outcome !== 'FAILED') {
                $sourceEvaluation = 'EVIDENCE_INCOMPLETE';
                break;
            }
            $failureStreak++; $failedDates[] = $date;
            if ($failureStreak >= self::SOURCE_EVALUATION_FAILURES) {
                $sourceEvaluation = 'OPEN_FUTURE_SOURCE_EVALUATION';
                break;
            }
        }
        return [
            'assessment' => 'CONDITIONAL_EVALUATION', 'freshness_target' => $target,
            'source_evaluation' => $sourceEvaluation,
            'source_change_authorized' => false,
            'failed_expected_session_streak' => $failureStreak,
            'failed_expected_session_dates' => array_reverse($failedDates),
            'expected_session_lag' => $lag,
            'target_policy' => $policy + ['authority' => 'Performance_SLO_and_Limits_LOCKED.md',
                'change_rule' => 'OUTPUT_AFFECTING_REDERIVATION_REQUIRED'],
            'requested_date' => $requested, 'operational_start_date' => $activation,
            'claim_limit' => $this->claimLimit(),
        ];
    }

    private function unavailable(string $reason): array
    {
        return ['assessment' => 'EVIDENCE_INCOMPLETE', 'freshness_target' => null,
            'source_evaluation' => null, 'expected_session_lag' => null,
            'reason' => $reason, 'claim_limit' => $this->claimLimit()];
    }

    private function claimLimit(): string
    {
        return 'Conditional evaluator behavior only; no operational activation, live target attainment, publication correctness, deployed alert delivery or release readiness is asserted.';
    }

    private function date($value, bool $nullable = false)
    {
        if ($nullable && ($value === null || $value === '')) return null;
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) return $nullable ? false : null;
        [$year, $month, $day] = array_map('intval', explode('-', $value));
        return checkdate($month, $day, $year) ? $value : ($nullable ? false : null);
    }

    private function instant($value, bool $nullable = false)
    {
        if ($nullable && ($value === null || $value === '')) return null;
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $value)) {
            return $nullable ? false : null;
        }
        try { return new \DateTimeImmutable($value); }
        catch (\Throwable $exception) { return $nullable ? false : null; }
    }

    private function sorted(array $dates): array
    {
        $sorted = $dates;
        sort($sorted, SORT_STRING);
        return $sorted;
    }
}
