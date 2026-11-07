<?php

use App\Application\MarketData\Services\MarketDataFreshnessEvaluator;

class B19FreshnessEvaluatorTest extends TestCase
{
    private function facts(): array
    {
        // Independently authored governed session sequence, including a weekend gap.
        $dates = ['2026-03-23', '2026-03-24', '2026-03-25', '2026-03-26',
            '2026-03-27', '2026-03-30', '2026-03-31'];
        $sessions = [];
        foreach ($dates as $index => $date) {
            $sessions[] = [
                'trade_date' => $date, 'completed_at' => $date.'T17:15:00+07:00',
                'cutoff_at' => $date.'T17:30:00+07:00',
                'calendar_state' => 'COMPLETED', 'provenance_tier' => 'VERIFIED',
                'calendar_revision_id' => $index + 101,
                'source_ref' => 'independent-calendar-'.$date,
            ];
        }
        return [
            'requested_date' => '2026-03-23', 'operational_start_date' => '2026-03-23',
            'expected_sessions' => $sessions, 'as_of' => '2026-03-31T18:00:00+07:00',
            'gateway_verified_readable_at' => '2026-03-23T17:20:00+07:00',
            'publication_verified' => true,
            'acquisition_outcomes' => array_fill_keys($dates, 'SUCCESS'),
        ];
    }

    private function evaluate(array $facts): array
    {
        return (new MarketDataFreshnessEvaluator())->evaluate($facts);
    }

    public function test_unversioned_horizon_or_threshold_change_requires_policy_rederivation(): void
    {
        foreach (['horizon_expected_sessions' => 10, 'acceptable_lag_expected_sessions' => 2,
            'breach_lag_expected_sessions' => 3, 'source_failure_expected_sessions' => 6] as $key => $value) {
            $facts = $this->facts(); $facts[$key] = $value;
            $result = $this->evaluate($facts);
            $this->assertSame('EVIDENCE_INCOMPLETE', $result['assessment'], 'Unversioned policy conflict: '.$key);
            $this->assertSame('HORIZON_POLICY_REDERIVATION_REQUIRED', $result['reason']);
            $this->assertNull($result['freshness_target']);
        }
        $normal = $this->evaluate($this->facts());
        $this->assertSame('ON_TARGET', $normal['freshness_target']);
        $this->assertSame(5, $normal['target_policy']['horizon_expected_sessions']);
        $this->assertSame(1, $normal['target_policy']['acceptable_lag_expected_sessions']);
        $this->assertSame(2, $normal['target_policy']['breach_lag_expected_sessions']);
        $this->assertSame(5, $normal['target_policy']['source_failure_expected_sessions']);
        $facts = $this->facts(); $facts['horizon_expected_sessions'] = 5;
        $this->assertSame('ON_TARGET', $this->evaluate($facts)['freshness_target']);
    }

    public function test_verified_expected_session_target_matrix_and_preactivation_boundary(): void
    {
        $facts = $this->facts();
        $same = $this->evaluate($facts);
        $this->assertSame('CONDITIONAL_EVALUATION', $same['assessment']);
        $this->assertSame('ON_TARGET', $same['freshness_target']);
        $this->assertSame(0, $same['expected_session_lag'], 'Readability in the requested session has zero expected-session lag regardless of assessment time.');
        $this->assertStringContainsString('no operational activation', $same['claim_limit']);

        $facts['gateway_verified_readable_at'] = '2026-03-24T17:20:00+07:00';
        $this->assertSame('ACCEPTABLE', $this->evaluate($facts)['freshness_target']);
        $facts['gateway_verified_readable_at'] = null;
        $this->assertSame('BREACH', $this->evaluate($facts)['freshness_target']);
        $facts['gateway_verified_readable_at'] = '2026-03-25T17:20:00+07:00';
        $this->assertSame('LATE_UNCLASSIFIED', $this->evaluate($facts)['freshness_target']);
        $facts['as_of'] = '2026-03-24T18:00:00+07:00';
        $facts['gateway_verified_readable_at'] = null;
        $this->assertSame('PENDING', $this->evaluate($facts)['freshness_target']);
        $facts['gateway_verified_readable_at'] = '2026-03-24T17:31:00+07:00';
        $this->assertSame('LATE_UNCLASSIFIED', $this->evaluate($facts)['freshness_target']);

        $facts['operational_start_date'] = null;
        $this->assertSame('NOT_APPLICABLE', $this->evaluate($facts)['assessment']);
        $this->assertNull($this->evaluate($facts)['freshness_target']);
        $facts['operational_start_date'] = '2026-03-24';
        $this->assertSame('NOT_APPLICABLE', $this->evaluate($facts)['assessment']);
    }

    public function test_readability_lag_is_fixed_at_first_verified_readability(): void
    {
        $facts = $this->facts();
        $facts['gateway_verified_readable_at'] = '2026-03-24T17:20:00+07:00';
        $facts['as_of'] = '2026-03-24T17:21:00+07:00';
        $this->assertSame(1, $this->evaluate($facts)['expected_session_lag']);
        $facts['as_of'] = '2026-03-31T18:00:00+07:00';
        $this->assertSame(1, $this->evaluate($facts)['expected_session_lag']);
        $facts['gateway_verified_readable_at'] = null;
        $this->assertSame(6, $this->evaluate($facts)['expected_session_lag']);
    }

    public function test_second_expected_session_breach_cutoff_distinguishes_missing_and_readable(): void
    {
        $facts = $this->facts();
        $facts['gateway_verified_readable_at'] = null;
        $facts['as_of'] = '2026-03-25T17:29:59+07:00';
        $this->assertSame('PENDING', $this->evaluate($facts)['freshness_target']);
        $facts['as_of'] = '2026-03-25T17:30:00+07:00';
        $this->assertSame('BREACH', $this->evaluate($facts)['freshness_target']);
        $facts['as_of'] = '2026-03-25T17:30:01+07:00';
        $this->assertSame('BREACH', $this->evaluate($facts)['freshness_target']);

        $facts['gateway_verified_readable_at'] = '2026-03-25T17:20:00+07:00';
        $this->assertSame('LATE_UNCLASSIFIED', $this->evaluate($facts)['freshness_target']);
        $facts['gateway_verified_readable_at'] = '2026-03-25T17:30:00+07:00';
        $this->assertSame('LATE_UNCLASSIFIED', $this->evaluate($facts)['freshness_target']);
        $facts['gateway_verified_readable_at'] = '2026-03-25T17:30:01+07:00';
        $this->assertSame('BREACH', $this->evaluate($facts)['freshness_target']);
    }

    public function test_five_consecutive_verified_expected_session_failures_trigger_evaluation_only(): void
    {
        $facts = $this->facts();
        $facts['requested_date'] = '2026-03-30';
        $facts['gateway_verified_readable_at'] = null;
        $facts['acquisition_outcomes'] = [
            '2026-03-23' => 'SUCCESS', '2026-03-24' => 'FAILED',
            '2026-03-25' => 'FAILED', '2026-03-26' => 'FAILED',
            '2026-03-27' => 'FAILED', '2026-03-30' => 'FAILED',
            '2026-03-31' => 'SUCCESS',
        ];
        $result = $this->evaluate($facts);
        $this->assertSame('NOT_TRIGGERED', $result['source_evaluation'],
            'A later successful expected session breaks the consecutive failure chain.');
        $facts['as_of'] = '2026-03-27T18:00:00+07:00';
        $result = $this->evaluate($facts);
        $this->assertSame('NOT_TRIGGERED', $result['source_evaluation'],
            'Four failed expected sessions do not exhaust the five-session horizon.');
        $this->assertSame(4, $result['failed_expected_session_streak']);
        $facts['as_of'] = '2026-03-30T18:00:00+07:00';
        $result = $this->evaluate($facts);
        $this->assertSame('OPEN_FUTURE_SOURCE_EVALUATION', $result['source_evaluation']);
        $this->assertSame(5, $result['failed_expected_session_streak']);
        $this->assertFalse($result['source_change_authorized']);
        $facts['acquisition_outcomes']['2026-03-26'] = 'SUCCESS';
        $result = $this->evaluate($facts);
        $this->assertSame('NOT_TRIGGERED', $result['source_evaluation']);
        $this->assertSame(2, $result['failed_expected_session_streak']);
        unset($facts['acquisition_outcomes']['2026-03-27']);
        $this->assertSame('EVIDENCE_INCOMPLETE', $this->evaluate($facts)['source_evaluation']);
    }

    public function test_unverified_calendar_publication_or_measurement_is_never_a_pass(): void
    {
        $facts = $this->facts();
        $facts['expected_sessions'][0]['provenance_tier'] = 'INFERRED';
        $this->assertSame('EXPECTED_SESSION_NOT_VERIFIED', $this->evaluate($facts)['reason']);
        $facts = $this->facts();
        $facts['expected_sessions'][1]['trade_date'] = '2026-03-23';
        $this->assertSame('EXPECTED_SESSION_NOT_VERIFIED', $this->evaluate($facts)['reason']);
        $facts = $this->facts();
        $facts['publication_verified'] = false;
        $this->assertSame('PUBLICATION_GATEWAY_IDENTITY_UNVERIFIED', $this->evaluate($facts)['reason']);
        $facts = $this->facts();
        $facts['gateway_verified_readable_at'] = '2026-04-01T00:00:00+07:00';
        $this->assertSame('READABILITY_AFTER_MEASUREMENT', $this->evaluate($facts)['reason']);
        $facts = $this->facts();
        $facts['gateway_verified_readable_at'] = '2026-03-23T17:14:00+07:00';
        $this->assertSame('READABILITY_BEFORE_SESSION_COMPLETION', $this->evaluate($facts)['reason']);
        $facts = $this->facts();
        $facts['expected_sessions'][0]['completed_at'] = '2026-03-23T17:31:00+07:00';
        $this->assertSame('EXPECTED_SESSION_NOT_VERIFIED', $this->evaluate($facts)['reason']);
        $facts = $this->facts();
        $facts['operational_start_date'] = 'invalid';
        $this->assertSame('OPERATIONAL_START_DATE_INVALID', $this->evaluate($facts)['reason']);
    }
}
