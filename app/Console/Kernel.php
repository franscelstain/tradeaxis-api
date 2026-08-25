<?php

namespace App\Console;

use App\Console\Commands\MarketData\AuditHashCommand;
use App\Console\Commands\MarketData\AdmitStageEightConformantSuffixCommand;
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
use App\Console\Commands\MarketData\RecomputeCurrentIndicatorsCommand;
use App\Console\Commands\MarketData\FinalizeRunCommand;
use App\Console\Commands\MarketData\ExportEvidenceCommand;
use App\Console\Commands\MarketData\FullRangeCurrentEvidenceReplayCommand;
use App\Console\Commands\MarketData\IngestEodBarsCommand;
use App\Console\Commands\MarketData\IngestSectorIndexBarsApiCommand;
use App\Console\Commands\MarketData\DetectPriceScaleBreaksCommand;
use App\Console\Commands\MarketData\DeriveCorporateActionsCommand;
use App\Console\Commands\MarketData\RepairPriceScaleStretchesCommand;
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
use App\Console\Commands\MarketData\RepairPublicationProjectionCommand;
use App\Console\Commands\MarketData\RecordAuthoritativeCorporateActionTermsCommand;
use App\Console\Commands\MarketData\RecordAuthoritativeExchangeMarketStructureCommand;
use App\Console\Commands\MarketData\RecordAuthoritativeTradingStatusSnapshotCommand;
use App\Console\Commands\MarketData\ReconstructCurrentCorpusCommand;
use App\Console\Commands\MarketData\ReconcilePublicationProjectionCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        IngestEodBarsCommand::class,
        AdmitStageEightConformantSuffixCommand::class,
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
        DetectPriceScaleBreaksCommand::class,
        DeriveCorporateActionsCommand::class,
        RepairPriceScaleStretchesCommand::class,
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
        RecomputeCurrentIndicatorsCommand::class,
        RequestCorrectionCommand::class,
        RunCorrectionCommand::class,
        ApproveCorrectionCommand::class,
        RepairCurrentPublicationIntegrityCommand::class,
        RepairPublicationProjectionCommand::class,
        RecordAuthoritativeCorporateActionTermsCommand::class,
        RecordAuthoritativeExchangeMarketStructureCommand::class,
        RecordAuthoritativeTradingStatusSnapshotCommand::class,
        ReconstructCurrentCorpusCommand::class,
        ReconcilePublicationProjectionCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $timezone = (string) config('market_data.platform.timezone', 'Asia/Jakarta');
        $outputPath = $this->scheduleOutputPath();

        // Internal publication/projection reconciliation has its own cadence and remains
        // independent of the daily pipeline. Scheduling policy reuses the canonical scheduler
        // overlap control instead of introducing unregistered semantic config keys.
        $reconciliation = $schedule->command('market-data:reconcile:publication-projection --latest')
            ->hourly()
            ->timezone($timezone)
            ->withoutOverlapping((int) config('market_data.scheduler.without_overlapping_minutes', 120));

        if ($outputPath !== '') {
            $reconciliation->appendOutputTo($outputPath);
        }

        if (! config('market_data.pipeline.daily_enabled')) {
            return;
        }

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
