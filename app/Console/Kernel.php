<?php

namespace App\Console;

use App\Console\Commands\MarketData\AuditHashCommand;
use App\Console\Commands\MarketData\BuildEligibilityCommand;
use App\Console\Commands\MarketData\ComputeIndicatorsCommand;
use App\Console\Commands\MarketData\DailyPipelineCommand;
use App\Console\Commands\MarketData\FinalizeRunCommand;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Console\Commands\MarketData\IngestEodBarsCommand;
use App\Console\Commands\MarketData\SealDatasetCommand;
use App\Console\Commands\MarketData\RequestCorrectionCommand;
use App\Console\Commands\MarketData\ApproveCorrectionCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        IngestEodBarsCommand::class,
        ComputeIndicatorsCommand::class,
        BuildEligibilityCommand::class,
        AuditHashCommand::class,
        SealDatasetCommand::class,
        FinalizeRunCommand::class,
        ExportEvidenceCommand::class,
        DailyPipelineCommand::class,
        RequestCorrectionCommand::class,
        ApproveCorrectionCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        if (! config('market_data.pipeline.daily_enabled')) {
            return;
        }

        $schedule->command('market-data:daily --latest')->dailyAt(substr(config('market_data.platform.cutoff_time'), 0, 5));
    }
}
