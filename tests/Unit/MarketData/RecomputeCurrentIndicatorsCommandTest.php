<?php

use App\Console\Commands\MarketData\RecomputeCurrentIndicatorsCommand;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Behavioural cover for market-data:eod-indicators:recompute-current.
 *
 * The command had no test of its own.
 *
 * It also absorbed --max_dates from RepublishCurrentIndicatorsCommand. That command was withdrawn
 * from the console kernel after operator runtime proved it failed the seal/hash lifecycle — it
 * hand-rolled the stage sequence instead of going through promoteDaily, which is where hash and
 * seal are orchestrated — but its class file was left in the repository, unreachable and
 * untested, and has now been deleted. --max_dates was the one capability it had that the approved
 * command did not, and it matters here because this command rewrites every current publication in
 * the requested range.
 */
class RecomputeCurrentIndicatorsCommandTest extends TestCase
{
    private $outputDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tradeaxis_recompute_'.uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputDir)) {
            $this->removeDirectory($this->outputDir);
        }

        m::close();
        parent::tearDown();
    }

    private function removeDirectory(string $path): void
    {
        foreach (array_diff(scandir($path), ['.', '..']) as $entry) {
            $full = $path.DIRECTORY_SEPARATOR.$entry;
            is_dir($full) ? $this->removeDirectory($full) : @unlink($full);
        }

        @rmdir($path);
    }

    /**
     * @param string[] $tradingDates
     */
    private function bindCalendar(array $tradingDates): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $calendar->shouldReceive('tradingDatesBetween')->andReturn($tradingDates);

        $this->app->instance(MarketCalendarRepository::class, $calendar);
    }

    private function bindPublications($publication): void
    {
        $publications = m::mock(EodPublicationRepository::class);
        $publications->shouldReceive('findCurrentPublicationForAnalyticalRemediation')->andReturn($publication);

        $this->app->instance(EodPublicationRepository::class, $publications);
    }

    private function readablePublication()
    {
        return (object) [
            'publication_id' => 2501,
            'publication_version' => 2,
            'run_id' => 25,
        ];
    }

    private function runCommand(array $options): array
    {
        $command = new RecomputeCurrentIndicatorsCommand();
        $command->setLaravel($this->app);

        $tester = new CommandTester($command);
        $exitCode = $tester->execute($options);

        return [$exitCode, $tester->getDisplay()];
    }

    private function baseOptions(array $override = []): array
    {
        return array_merge([
            'start_date' => '2026-03-16',
            'end_date' => '2026-03-20',
            '--force_replace_reason' => 'operator approved indicator recompute',
            '--output_dir' => $this->outputDir,
            '--dry-run' => true,
        ], $override);
    }

    private function summary(): array
    {
        $path = $this->outputDir.DIRECTORY_SEPARATOR.'indicator_recompute_current_summary.json';
        $this->assertFileExists($path);

        return json_decode(file_get_contents($path), true);
    }

    public function test_without_a_limit_every_resolved_trading_date_is_processed(): void
    {
        $this->bindCalendar(['2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20']);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand($this->baseOptions());

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('trading_date_count=5', $display);
        $this->assertStringContainsString('processed_count=5', $display);
        $this->assertSame(5, $this->summary()['trading_date_count']);
        $this->assertNull($this->summary()['max_dates']);
    }

    /**
     * The limiter exists so an operator can try a destructive range operation on a few dates
     * before committing to hundreds.
     */
    public function test_max_dates_limits_how_many_dates_are_processed(): void
    {
        $this->bindCalendar(['2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20']);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand($this->baseOptions(['--max_dates' => '2']));

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('processed_count=2', $display);

        $summary = $this->summary();
        $this->assertCount(2, $summary['cases']);
        $this->assertSame('2026-03-16', $summary['cases'][0]['trade_date']);
        $this->assertSame('2026-03-17', $summary['cases'][1]['trade_date']);
    }

    /**
     * all_passed is measured against the number of dates this run set out to process. If the
     * limiter reduced the list but the target stayed at the full range, a completely successful
     * limited run would report failure.
     */
    public function test_a_limited_run_that_succeeds_reports_success(): void
    {
        $this->bindCalendar(['2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20']);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand($this->baseOptions(['--max_dates' => '2']));

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('all_passed=1', $display);
        $this->assertTrue($this->summary()['all_passed']);
    }

    /**
     * The artifact must still show that the requested range was larger than the slice, otherwise
     * a limited run and a full run of a short range are indistinguishable after the fact.
     */
    public function test_the_artifact_records_that_the_range_was_larger_than_the_slice(): void
    {
        $this->bindCalendar(['2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20']);
        $this->bindPublications($this->readablePublication());

        [, $display] = $this->runCommand($this->baseOptions(['--max_dates' => '2']));

        $this->assertStringContainsString('resolved_trading_date_count=5', $display);
        $this->assertStringContainsString('max_dates=2', $display);

        $summary = $this->summary();
        $this->assertSame(5, $summary['resolved_trading_date_count']);
        $this->assertSame(2, $summary['trading_date_count']);
        $this->assertSame(2, $summary['max_dates']);
    }

    /**
     * @dataProvider invalidLimits
     */
    public function test_a_non_positive_limit_is_blocked(string $limit): void
    {
        $this->bindCalendar(['2026-03-16']);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand($this->baseOptions(['--max_dates' => $limit]));

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('status=BLOCKED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_MISSING_REQUIRED_INPUT', $display);
    }

    public function invalidLimits(): array
    {
        return [
            'zero' => ['0'],
            'negative' => ['-3'],
            'not a number' => ['many'],
        ];
    }

    /**
     * A limit larger than the range is not an error; it simply does not bind.
     */
    public function test_a_limit_larger_than_the_range_processes_everything(): void
    {
        $this->bindCalendar(['2026-03-16', '2026-03-17']);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand($this->baseOptions(['--max_dates' => '99']));

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('processed_count=2', $display);
    }

    /**
     * The dry run is a preflight, not a date listing: it resolves the current publication for
     * each date and reports the ones that have none.
     */
    public function test_dry_run_reports_dates_with_no_current_readable_publication(): void
    {
        $this->bindCalendar(['2026-03-16']);
        $this->bindPublications(null);

        [$exitCode, $display] = $this->runCommand($this->baseOptions());

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('reason_code=NO_READABLE_PUBLICATION', $display);
    }

    /**
     * The command replaces current readable publications, so it must never run without an
     * auditable reason.
     */
    public function test_an_audit_reason_is_required(): void
    {
        $this->bindCalendar(['2026-03-16']);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand([
            'start_date' => '2026-03-16',
            'end_date' => '2026-03-16',
            '--output_dir' => $this->outputDir,
            '--dry-run' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('reason_code=COMMAND_DESTRUCTIVE_GUARD_REQUIRED', $display);
    }

    public function test_an_empty_trading_calendar_is_blocked_rather_than_treated_as_success(): void
    {
        $this->bindCalendar([]);
        $this->bindPublications($this->readablePublication());

        [$exitCode, $display] = $this->runCommand($this->baseOptions());

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('reason_code=MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE', $display);
    }
}
