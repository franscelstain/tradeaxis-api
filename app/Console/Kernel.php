<?php

namespace App\Console;

use App\Console\Commands\MarketData\AuditHashCommand;
use App\Console\Commands\MarketData\BuildEligibilityCommand;
use App\Console\Commands\MarketData\ComputeIndicatorsCommand;
use App\Console\Commands\MarketData\DailyPipelineCommand;
use App\Console\Commands\MarketData\BackfillMarketDataCommand;
use App\Console\Commands\MarketData\BackfillLifecycleCommand;
use App\Console\Commands\MarketData\BackfillMissingTickersCommand;
use App\Console\Commands\MarketData\CaptureSessionSnapshotCommand;
use App\Console\Commands\MarketData\PurgeSessionSnapshotCommand;
use App\Console\Commands\MarketData\PromoteMarketDataCommand;
use App\Console\Commands\MarketData\ProviderSmokeCommand;
use App\Console\Commands\MarketData\FinalizeRunCommand;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Console\Commands\MarketData\FullRangeCurrentEvidenceReplayCommand;
use App\Console\Commands\MarketData\IngestEodBarsCommand;
use App\Console\Commands\MarketData\IngestSectorIndexBarsApiCommand;
use App\Console\Commands\MarketData\ImportCorporateActionsCommand;
use App\Console\Commands\MarketData\ImportSectorIndexBarsCommand;
use App\Console\Commands\MarketData\ImportSectorMembershipCommand;
use App\Console\Commands\MarketData\ImportTradingStatusEventsCommand;
use App\Console\Commands\MarketData\SealDatasetCommand;
use App\Console\Commands\MarketData\VerifyReplayCommand;
use App\Console\Commands\MarketData\ReplaySmokeSuiteCommand;
use App\Console\Commands\MarketData\ReplayBackfillCommand;
use App\Console\Commands\MarketData\GenerateReplayFixtureCommand;
use App\Console\Commands\MarketData\RequestCorrectionCommand;
use App\Console\Commands\MarketData\RunCorrectionCommand;
use App\Console\Commands\MarketData\ApproveCorrectionCommand;
use App\Console\Commands\MarketData\RepairCurrentPublicationIntegrityCommand;
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
        VerifyReplayCommand::class,
        ReplaySmokeSuiteCommand::class,
        ReplayBackfillCommand::class,
        GenerateReplayFixtureCommand::class,
        FinalizeRunCommand::class,
        ExportEvidenceCommand::class,
        FullRangeCurrentEvidenceReplayCommand::class,
        IngestSectorIndexBarsApiCommand::class,
        ImportCorporateActionsCommand::class,
        ImportSectorIndexBarsCommand::class,
        ImportSectorMembershipCommand::class,
        ImportTradingStatusEventsCommand::class,
        DailyPipelineCommand::class,
        BackfillMarketDataCommand::class,
        BackfillLifecycleCommand::class,
        BackfillMissingTickersCommand::class,
        CaptureSessionSnapshotCommand::class,
        PurgeSessionSnapshotCommand::class,
        PromoteMarketDataCommand::class,
        ProviderSmokeCommand::class,
        RequestCorrectionCommand::class,
        RunCorrectionCommand::class,
        ApproveCorrectionCommand::class,
        RepairCurrentPublicationIntegrityCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        if (! config('market_data.pipeline.daily_enabled')) {
            return;
        }

        $timezone = (string) config('market_data.platform.timezone', 'Asia/Jakarta');
        $outputPath = $this->scheduleOutputPath();

        $event = $schedule->command('market-data:daily --latest')
            ->dailyAt(substr(config('market_data.platform.cutoff_time'), 0, 5))
            ->timezone($timezone)
            ->withoutOverlapping((int) config('market_data.scheduler.without_overlapping_minutes', 120));

        if ($outputPath !== '') {
            $event->appendOutputTo($outputPath)
                ->onSuccess(function () use ($outputPath, $timezone) {
                    $this->appendSchedulerStatusLine($outputPath, $timezone, 'SUCCESS');
                })
                ->onFailure(function () use ($outputPath, $timezone) {
                    $this->appendSchedulerStatusLine($outputPath, $timezone, 'FAILURE');
                });
        }
    }

    private function scheduleOutputPath()
    {
        $path = (string) config('market_data.scheduler.output_path', '');

        if ($path === '') {
            return '';
        }

        if (! preg_match('/^(?:[A-Za-z]:[\/\\\\]|\/|\\\\\\\\)/', $path)) {
            $path = base_path($path);
        }

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $path;
    }

    private function appendSchedulerStatusLine($outputPath, $timezone, $status)
    {
        $timestamp = (new \DateTimeImmutable('now', new \DateTimeZone($timezone)))->format('Y-m-d H:i:s T');

        file_put_contents(
            $outputPath,
            sprintf("[%s] scheduler_status=%s command=\"market-data:daily --latest\"\n", $timestamp, $status),
            FILE_APPEND
        );
    }
}
