<?php

use PHPUnit\Framework\TestCase;

class ProductionSchedulerCronStaticGuardTest extends TestCase
{
    private function read($path)
    {
        $fullPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

        $this->assertFileExists($fullPath, $path.' must exist.');

        return file_get_contents($fullPath);
    }

    // The twelve Kernel source fragments are gone. DailyScheduleRegistrationTest builds the real
    // schedule and reads the registered event back: the command, the cron expression derived
    // from the configured cutoff, the exchange timezone, the overlap window, the append-mode
    // output path, and that both outcomes register a callback. Those are the resolved values,
    // which is what runs unattended — a source fragment says only that a method was called.





    public function test_operational_runbook_documents_cron_operator_contract()
    {
        $runbook = $this->read('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'Scheduler / cron deployment flow',
            'php artisan schedule:run',
            'MARKET_DATA_DAILY_ENABLED=true',
            'MARKET_DATA_PLATFORM_TIMEZONE=Asia/Jakarta',
            'MARKET_DATA_PLATFORM_EOD_CUTOFF_TIME=HH:MM:SS',
            'MARKET_DATA_SCHEDULER_OUTPUT_PATH',
            'MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES',
            'market-data:daily --latest',
            'scheduler_status=SUCCESS',
            'scheduler_status=FAILURE',
            'pointer_switched=false',
            'Scheduler proof is not live provider proof.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }
}