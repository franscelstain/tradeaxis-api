<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Behavioural cover for the unattended daily schedule.
 *
 * `ProductionSchedulerCronStaticGuardTest` asserts that twelve fragments appear in the Kernel —
 * `->timezone($timezone)`, `withoutOverlapping`, `dailyAt(substr(...))`. Each is a real property
 * written as source text, and none of them survives a refactor or shows what the schedule
 * actually resolves to.
 *
 * The schedule is where the platform runs without anyone watching, so the resolved values are
 * the thing that matters.
 *
 * Timezone above all. IDX closes at the configured cutoff in Asia/Jakarta, and Laravel schedules
 * in the application timezone unless told otherwise. A schedule that lost its timezone would fire
 * seven hours early on a server running UTC — before the exchange has closed — and `--latest`
 * would resolve to the previous trading day. The run would succeed, publish, and be a day stale,
 * every day, with nothing in the output saying so.
 *
 * These tests build the real schedule and read the registered event back.
 */
class DailyScheduleRegistrationTest extends TestCase
{
    private function buildSchedule(array $configOverride = []): Schedule
    {
        foreach ($configOverride as $key => $value) {
            config()->set($key, $value);
        }

        $schedule = new Schedule();

        $kernel = $this->app->make(Illuminate\Contracts\Console\Kernel::class);

        $method = new ReflectionMethod(get_class($kernel), 'schedule');
        $method->setAccessible(true);
        $method->invoke($kernel, $schedule);

        return $schedule;
    }

    /**
     * @return Event[]
     */
    private function marketDataEvents(Schedule $schedule): array
    {
        return array_values(array_filter($schedule->events(), function (Event $event) {
            return strpos((string) $event->command, 'market-data:') !== false;
        }));
    }

    /**
     * The daily schedule is switched off in .env.testing, which is correct — a test run must not
     * register a production import. Every test about the shape of the schedule therefore has to
     * turn it on explicitly.
     */
    private function dailyEvent(array $configOverride = []): Event
    {
        $events = array_values(array_filter(
            $this->marketDataEvents($this->buildSchedule(array_merge([
                'market_data.pipeline.daily_enabled' => true,
            ], $configOverride))),
            function (Event $event) {
                return strpos((string) $event->command, 'market-data:daily --latest') !== false;
            }
        ));

        $this->assertCount(1, $events, 'Exactly one daily market-data import must be scheduled.');

        return $events[0];
    }

    public function test_the_daily_import_is_scheduled(): void
    {
        $this->assertStringContainsString('market-data:daily --latest', $this->dailyEvent()->command);
    }

    /**
     * The schedule must carry the exchange timezone explicitly. Without it the cron expression is
     * interpreted in the application timezone, and the run fires before the exchange has closed.
     */
    public function test_the_schedule_runs_in_the_configured_exchange_timezone(): void
    {
        $event = $this->dailyEvent();

        $this->assertSame(
            (string) config('market_data.platform.timezone', 'Asia/Jakarta'),
            (string) $event->timezone,
            'The daily schedule must be pinned to the exchange timezone.'
        );

        $this->assertNotEmpty($event->timezone, 'A schedule with no timezone runs in server time.');
    }

    /**
     * The fire time is derived from the configured cutoff, not written twice. A cutoff moved in
     * config while the schedule kept its own hour would run against a market still open.
     *
     * @dataProvider cutoffTimes
     */
    public function test_the_fire_time_follows_the_configured_cutoff(string $cutoff, string $expectedExpression): void
    {
        $event = $this->dailyEvent(['market_data.platform.cutoff_time' => $cutoff]);

        $this->assertSame($expectedExpression, $event->expression);
    }

    public function cutoffTimes(): array
    {
        return [
            'evening cutoff' => ['17:20:00', '20 17 * * *'],
            'earlier cutoff' => ['16:05:00', '5 16 * * *'],
            'on the hour' => ['18:00:00', '0 18 * * *'],
        ];
    }

    /**
     * A daily import that is still running must not be started again.
     *
     * The pipeline writes bars, indicators and eligibility for a trade date; two concurrent runs
     * would race on the same rows. The overlap window has to outlast a slow run, so it is
     * configurable rather than the framework's one-minute default.
     */
    public function test_a_second_run_cannot_start_while_the_first_is_still_going(): void
    {
        $event = $this->dailyEvent();

        $this->assertTrue($event->withoutOverlapping, 'Concurrent daily runs would race on the same trade date.');
        $this->assertSame(
            (int) config('market_data.scheduler.without_overlapping_minutes', 120),
            $event->expiresAt,
            'The overlap window must come from config so it can outlast a slow run.'
        );
    }

    /**
     * The schedule is a switch an operator can turn off without editing code. When it is off,
     * nothing is registered at all — a disabled schedule that still registers a no-op event would
     * report as scheduled.
     */
    public function test_daily_pipeline_can_be_disabled_without_disabling_independent_reconciliation(): void
    {
        $events = $this->marketDataEvents($this->buildSchedule([
            'market_data.pipeline.daily_enabled' => false,
        ]));

        $this->assertCount(1, $events);
        $this->assertStringContainsString('market-data:reconcile:publication-projection --latest', $events[0]->command);
        $this->assertStringNotContainsString('market-data:daily --latest', $events[0]->command);
    }

    public function test_daily_and_reconciliation_schedules_are_both_registered_when_daily_is_enabled(): void
    {
        $events = $this->marketDataEvents($this->buildSchedule([
            'market_data.pipeline.daily_enabled' => true,
        ]));

        $this->assertCount(2, $events);
    }

    public function test_reconciliation_has_independent_hourly_cadence_and_overlap_guard(): void
    {
        $events = array_values(array_filter(
            $this->marketDataEvents($this->buildSchedule([
                'market_data.pipeline.daily_enabled' => false,
            ])),
            function (Event $event) {
                return strpos((string) $event->command, 'market-data:reconcile:publication-projection --latest') !== false;
            }
        ));

        $this->assertCount(1, $events);
        $this->assertSame('0 * * * *', $events[0]->expression);
        $this->assertSame((string) config('market_data.platform.timezone', 'Asia/Jakarta'), (string) $events[0]->timezone);
        $this->assertTrue($events[0]->withoutOverlapping);
        $this->assertSame(
            (int) config('market_data.scheduler.without_overlapping_minutes', 120),
            $events[0]->expiresAt
        );
    }

    /**
     * An unattended run leaves no operator watching the terminal, so its output has to land
     * somewhere. Without an output path there is no record that the schedule fired at all.
     */
    public function test_output_is_appended_to_the_configured_log(): void
    {
        $event = $this->dailyEvent();

        $this->assertNotEmpty($event->output, 'Unattended output must be recorded.');
        $this->assertTrue($event->shouldAppendOutput, 'Each run must append rather than replace the previous record.');
        $this->assertStringContainsString(
            basename((string) config('market_data.scheduler.output_path')),
            (string) $event->output
        );
    }

    /**
     * Both outcomes must be recorded. A log that only marks success cannot be used to notice a
     * run that failed — the absence of a line and the absence of a run look identical.
     */
    public function test_both_success_and_failure_are_recorded(): void
    {
        $event = $this->dailyEvent();

        $callbacks = new ReflectionProperty(Event::class, 'afterCallbacks');
        $callbacks->setAccessible(true);

        // onSuccess and onFailure both register an after-callback, so two of them is the shape
        // that records both outcomes. One would mean only one outcome leaves a trace.
        $this->assertCount(
            2,
            $callbacks->getValue($event),
            'Both a successful and a failed run must record their outcome.'
        );
    }

    private function read(string $path): string
    {
        $this->assertFileExists($path);

        return file_get_contents($path);
    }
}
