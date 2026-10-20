<?php

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class B19OperationalCalendarTest extends TestCase
{
    use UsesMarketDataSqlite;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); $this->seedVerifiedMarketCalendarRange('2026-03-20', '2026-03-25'); }
    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }

    public function test_latest_target_requires_cutoff_and_verified_completed_session_and_exposes_unknown_states(): void
    {
        config()->set('market_data.platform.cutoff_time', '17:20:00');
        $repository = new MarketCalendarRepository();
        $this->assertSame('2026-03-24', $repository->latestExpectedSession('2026-03-25 17:19:59')['latest_expected_trade_date']);
        $this->assertSame('2026-03-25', $repository->latestExpectedSession('2026-03-25 18:00:00')['latest_expected_trade_date']);
        foreach (['UNKNOWN', 'PARTIAL', 'CANCELLED'] as $state) {
            DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-25')->update(['session_state' => $state]);
            $result = $repository->latestExpectedSession('2026-03-25 18:00:00');
            $this->assertSame('2026-03-24', $result['latest_expected_trade_date']);
            $this->assertSame($state, $result['current_session_state']);
        }
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-25')->delete();
        $result = $repository->latestExpectedSession('2026-03-25 18:00:00');
        $this->assertSame('MISSING', $result['current_session_state']);
        $this->assertSame('2026-03-24', $result['latest_expected_trade_date']);
    }

    public function test_conflicting_or_unverified_calendar_cannot_authorize_a_latest_target(): void
    {
        $repository = new MarketCalendarRepository();
        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-25')->update(['provenance_tier' => 'UNVERIFIED']);
        try { $repository->latestExpectedSession('2026-03-25 18:00:00'); $this->fail('Unverified completion authorized a target.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('PROVENANCE_NOT_VERIFIED', $e->getMessage()); }
        $row = (array) DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-24')->first();
        unset($row['calendar_revision_id']); $row['revision_uid'] = hash('sha256', 'conflicting-completion');
        DB::table('md_market_calendar_revisions')->insert($row);
        try { $repository->latestExpectedSession('2026-03-24 18:00:00'); $this->fail('Ambiguous calendar authorized a target.'); }
        catch (RuntimeException $e) { $this->assertStringContainsString('REVISION_CONFLICT', $e->getMessage()); }
    }
}
