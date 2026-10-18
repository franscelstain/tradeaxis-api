<?php

use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;

class B19ConflictReasonMariaDbSeedTest extends TestCase
{
    use UsesMarketDataMariaDb;
    protected function setUp(): void {parent::setUp();$this->bootMarketDataMariaDb();}
    protected function tearDown(): void {$this->tearDownMarketDataMariaDb();parent::tearDown();}
    public function test_actual_mariadb_seed_registers_conflict_bar_hard_without_reclassifying_duplicates(): void
    {
        DB::unprepared(file_get_contents(base_path('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql')));
        $r=DB::table('eod_reason_codes')->where('code','BAR_CONFLICTING_SOURCE_ROWS')->first();
        $this->assertNotNull($r);$this->assertSame('BAR',$r->category);$this->assertSame('HARD',$r->severity);$this->assertSame(1,(int)$r->is_active);
        $this->assertSame('WARN',DB::table('eod_reason_codes')->where('code','BAR_DUPLICATE_SOURCE_ROW')->value('severity'));
        if(getenv('B19_COMPLETION_ARTIFACT_DIR'))file_put_contents(getenv('B19_COMPLETION_ARTIFACT_DIR').'/mariadb_conflict_registry.json',json_encode($r,JSON_PRETTY_PRINT));
    }
}
