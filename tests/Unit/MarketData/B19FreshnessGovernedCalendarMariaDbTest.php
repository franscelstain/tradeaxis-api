<?php

use App\Application\MarketData\Services\MarketDataFreshnessEvaluator;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;

/** Conditional evaluator proof over actual migrated MariaDB calendar revisions. */
class B19FreshnessGovernedCalendarMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private $previousActivation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
        $this->previousActivation = config('market_data.scope.operational_start_date');
        foreach (['2026-03-23', '2026-03-24', '2026-03-25', '2026-03-26',
            '2026-03-27', '2026-03-30', '2026-03-31'] as $index => $date) {
            DB::table('md_market_calendar_revisions')->insert([
                'revision_uid' => hash('sha256', 'e047-independent-calendar-'.$date),
                'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date,
                'timezone' => 'Asia/Jakarta', 'is_trading_day' => 1, 'is_half_day' => 0,
                'session_state' => 'COMPLETED', 'session_open_at' => $date.' 09:00:00',
                'session_close_at' => $date.' 16:00:00', 'completed_at' => $date.' 16:00:00',
                'recorded_at' => $date.' 17:00:00', 'source_observation_id' => null,
                'supersedes_revision_id' => null,
                'source_ref' => 'independent-idx-calendar-'.$date,
                'source_version' => 'independent-e047-v1', 'provenance_tier' => 'VERIFIED',
                'reconciled_at' => $date.' 17:00:00',
                'reconciliation_source_ref' => 'independent-idx-calendar-'.$date,
            ]);
        }
        // Independent schedule fixture explicitly closes both weekend dates.
        // Their absence must never be interpreted as proof of a non-trading day.
        foreach (['2026-03-28', '2026-03-29'] as $date) {
            DB::table('md_market_calendar_revisions')->insert([
                'revision_uid' => hash('sha256', 'e048-independent-closed-'.$date),
                'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date,
                'timezone' => 'Asia/Jakarta', 'is_trading_day' => 0, 'is_half_day' => 0,
                'session_state' => 'CLOSED', 'recorded_at' => '2026-03-01 17:00:00',
                'source_ref' => 'independent-idx-calendar-'.$date,
                'source_version' => 'independent-e048-v1', 'provenance_tier' => 'VERIFIED',
                'reconciled_at' => '2026-03-01 17:00:00',
                'reconciliation_source_ref' => 'independent-idx-calendar-'.$date,
            ]);
        }
    }

    protected function tearDown(): void
    {
        config(['market_data.scope.operational_start_date' => $this->previousActivation]);
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    private function facts(string $asOf, ?string $readable): array
    {
        return [
            'requested_date' => '2026-03-27', 'as_of' => $asOf,
            'gateway_verified_readable_at' => $readable,
            'publication_verified' => $readable !== null,
            'acquisition_outcomes' => [
                '2026-03-23' => 'SUCCESS', '2026-03-24' => 'SUCCESS',
                '2026-03-25' => 'SUCCESS', '2026-03-26' => 'SUCCESS',
                '2026-03-27' => 'SUCCESS', '2026-03-30' => 'SUCCESS',
                '2026-03-31' => 'SUCCESS',
            ],
        ];
    }

    public function test_five_failed_governed_sessions_across_weekend_open_evaluation_without_migration(): void
    {
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $evaluator = new MarketDataFreshnessEvaluator();
        $facts = $this->facts('2026-03-30T17:16:00+07:00', null);
        $facts['requested_date'] = '2026-03-30';
        foreach (['2026-03-24', '2026-03-25', '2026-03-26', '2026-03-27', '2026-03-30'] as $date) {
            $facts['acquisition_outcomes'][$date] = 'FAILED';
        }
        $result = $evaluator->evaluateFromGovernedCalendar($facts);
        $this->assertSame('OPEN_FUTURE_SOURCE_EVALUATION', $result['source_evaluation']);
        $this->assertSame(5, $result['failed_expected_session_streak']);
        $this->assertFalse($result['source_change_authorized']);

        $facts['as_of'] = '2026-03-27T17:16:00+07:00';
        $facts['requested_date'] = '2026-03-27';
        $result = $evaluator->evaluateFromGovernedCalendar($facts);
        $this->assertSame('NOT_TRIGGERED', $result['source_evaluation']);
        $this->assertSame(4, $result['failed_expected_session_streak']);
    }

    public function test_governed_mariadb_sessions_fix_lag_and_second_cutoff_without_wall_clock_guessing(): void
    {
        $evaluator = new MarketDataFreshnessEvaluator();
        $preactivation = $evaluator->evaluateFromGovernedCalendar(
            $this->facts('2026-03-31T17:16:00+07:00', null)
        );
        $this->assertSame('NOT_APPLICABLE', $preactivation['assessment']);
        $this->assertNull($preactivation['freshness_target']);

        // The marker is conditional fixture input, not evidence of owner-approved activation.
        config(['market_data.scope.operational_start_date' => '2026-03-27']);
        $same = $evaluator->evaluateFromGovernedCalendar(
            $this->facts('2026-03-31T17:16:00+07:00', '2026-03-27T17:10:00+07:00')
        );
        $this->assertSame('ON_TARGET', $same['freshness_target']);
        $this->assertSame(0, $same['expected_session_lag']);
        $calendarRows = DB::table('md_market_calendar_revisions')->where('cal_date', '>=', '2026-03-27')->where('is_trading_day', 1)->orderBy('cal_date')
            ->get(['cal_date', 'calendar_revision_id', 'source_ref']);
        $expectedRevisionIds = [];
        $expectedSourceRefs = [];
        foreach ($calendarRows as $row) {
            $expectedRevisionIds[$row->cal_date] = (int) $row->calendar_revision_id;
            $expectedSourceRefs[$row->cal_date] = (string) $row->source_ref;
        }
        $this->assertSame($expectedRevisionIds, $same['calendar_revision_ids']);
        $this->assertSame($expectedSourceRefs, $same['calendar_source_refs']);
        $this->assertSame('GOVERNED_CURRENT_REVISION_AS_KNOWN_AT_ASSESSMENT', $same['calendar_binding']);
        $next = $evaluator->evaluateFromGovernedCalendar(
            $this->facts('2026-03-31T17:16:00+07:00', '2026-03-30T17:10:00+07:00')
        );
        $this->assertSame('ACCEPTABLE', $next['freshness_target']);
        $this->assertSame(1, $next['expected_session_lag']);
        $before = $evaluator->evaluateFromGovernedCalendar($this->facts('2026-03-31T17:14:59+07:00', null));
        $this->assertSame('PENDING', $before['freshness_target']);
        $at = $evaluator->evaluateFromGovernedCalendar($this->facts('2026-03-31T17:15:00+07:00', null));
        $this->assertSame('BREACH', $at['freshness_target']);
        $this->assertSame(2, $at['expected_session_lag']);
        $readable = $evaluator->evaluateFromGovernedCalendar(
            $this->facts('2026-03-31T17:16:00+07:00', '2026-03-31T17:10:00+07:00')
        );
        $this->assertSame('LATE_UNCLASSIFIED', $readable['freshness_target']);
        $this->assertSame(2, $readable['expected_session_lag']);
    }

    public function test_inferred_or_conflicting_calendar_and_caller_overrides_fail_closed(): void
    {
        config(['market_data.scope.operational_start_date' => '2026-03-27']);
        $evaluator = new MarketDataFreshnessEvaluator();
        $facts = $this->facts('2026-03-31T17:16:00+07:00', null);
        $facts['expected_sessions'] = [];
        $this->assertSame('GOVERNED_INPUT_OVERRIDE_PROHIBITED',
            $evaluator->evaluateFromGovernedCalendar($facts)['reason']);
        unset($facts['expected_sessions']);
        $facts['operational_start_date'] = '2026-03-01';
        $this->assertSame('GOVERNED_INPUT_OVERRIDE_PROHIBITED',
            $evaluator->evaluateFromGovernedCalendar($facts)['reason']);
        unset($facts['operational_start_date']);
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-30')
            ->update(['provenance_tier' => 'INFERRED']);
        $result = $evaluator->evaluateFromGovernedCalendar($facts);
        $this->assertSame('EVIDENCE_INCOMPLETE', $result['assessment']);
        $this->assertSame('GOVERNED_EXPECTED_SESSIONS_UNVERIFIED', $result['reason']);
        $this->assertNull($result['freshness_target']);
    }

    public function test_missing_calendar_date_cannot_shorten_lag_or_failure_horizon(): void
    {
        config(['market_data.scope.operational_start_date' => '2026-03-23']);
        $evaluator = new MarketDataFreshnessEvaluator();
        $facts = $this->facts('2026-03-31T17:16:00+07:00', null);
        $facts['acquisition_outcomes'] = array_fill_keys(
            ['2026-03-24', '2026-03-25', '2026-03-26', '2026-03-27', '2026-03-30', '2026-03-31'], 'FAILED'
        );
        $complete = $evaluator->evaluateFromGovernedCalendar($facts);
        $this->assertSame(2, $complete['expected_session_lag']);
        $this->assertSame(5, $complete['failed_expected_session_streak']);
        $this->assertSame('OPEN_FUTURE_SOURCE_EVALUATION', $complete['source_evaluation']);
        $this->assertSame(9, DB::table('md_market_calendar_revisions')->count());
        $this->assertCount(7, $complete['calendar_revision_ids']);
        foreach (['2026-03-30', '2026-03-28'] as $missingDate) {
            $saved = (array) DB::table('md_market_calendar_revisions')->where('cal_date', $missingDate)->first();
            DB::table('md_market_calendar_revisions')->where('cal_date', $missingDate)->delete();
            try {
                $result = $evaluator->evaluateFromGovernedCalendar($facts);
                $this->assertSame('EVIDENCE_INCOMPLETE', $result['assessment'], 'Missing horizon date '.$missingDate);
                $this->assertSame('GOVERNED_EXPECTED_SESSIONS_UNVERIFIED', $result['reason']);
                $this->assertNull($result['expected_session_lag']);
                $this->assertNull($result['source_evaluation']);
            } finally {
                DB::table('md_market_calendar_revisions')->insert($saved);
            }
        }
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-28')->update(['provenance_tier' => 'PROJECTED']);
        $this->assertSame('EVIDENCE_INCOMPLETE', $evaluator->evaluateFromGovernedCalendar($facts)['assessment'],
            'A projected holiday cannot certify that no session is missing.');
    }
    public function test_activation_boundary_excludes_older_failed_sessions_without_backdating_measurement(): void
    {
        config(['market_data.scope.operational_start_date' => '2026-03-27']);
        $evaluator = new MarketDataFreshnessEvaluator();
        $facts = $this->facts('2026-03-27T18:00:00+07:00', null);
        foreach (['2026-03-23', '2026-03-24', '2026-03-25', '2026-03-26', '2026-03-27'] as $date) $facts['acquisition_outcomes'][$date] = 'FAILED';
        $before = DB::table('md_market_calendar_revisions')->orderBy('calendar_revision_id')->get()->toJson();
        $governed = $evaluator->evaluateFromGovernedCalendar($facts);
        $this->assertSame('CONDITIONAL_EVALUATION', $governed['assessment']);
        $this->assertSame(1, $governed['failed_expected_session_streak'], 'Pre-activation failures must not enter the operational consecutive-session population.');
        $this->assertSame(['2026-03-27'], $governed['failed_expected_session_dates']);
        $this->assertSame('NOT_TRIGGERED', $governed['source_evaluation']);
        $this->assertSame(['2026-03-27'], array_keys($governed['calendar_revision_ids']));
        // A conditional caller supplies the full historical sequence. Its older
        // failed observations must not be counted merely because they exist.
        $sessions = [];
        foreach (['2026-03-23', '2026-03-24', '2026-03-25', '2026-03-26', '2026-03-27'] as $date) {
            $sessions[] = ['trade_date' => $date, 'completed_at' => $date.'T16:00:00+07:00',
                'cutoff_at' => $date.'T17:15:00+07:00', 'calendar_state' => 'COMPLETED', 'provenance_tier' => 'VERIFIED',
                'calendar_revision_id' => (int) DB::table('md_market_calendar_revisions')->where('cal_date', $date)->value('calendar_revision_id'),
                'source_ref' => 'independent-idx-calendar-'.$date];
        }
        $conditional = $evaluator->evaluate($facts + ['operational_start_date' => '2026-03-27', 'expected_sessions' => $sessions]);
        $this->assertSame(1, $conditional['failed_expected_session_streak'], 'Historical observations cannot backdate the source-failure threshold.');
        $this->assertSame(['2026-03-27'], $conditional['failed_expected_session_dates']);
        $this->assertSame('NOT_TRIGGERED', $conditional['source_evaluation']);
        $facts['requested_date'] = '2026-03-26';
        $facts['as_of'] = '2026-03-31T18:00:00+07:00';
        $pre = $evaluator->evaluateFromGovernedCalendar($facts);
        $this->assertSame('NOT_APPLICABLE', $pre['assessment']);
        $this->assertNull($pre['freshness_target']); $this->assertNull($pre['source_evaluation']);
        $this->assertSame($before, DB::table('md_market_calendar_revisions')->orderBy('calendar_revision_id')->get()->toJson());
        $this->assertStringContainsString('no operational activation', $governed['claim_limit']);
    }

}
