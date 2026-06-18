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
use App\Console\Commands\MarketData\RecomputeCurrentIndicatorsCommand;
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
use App\Console\Commands\Watchlist\RunBacktestPublishedPriceProofCommand;
use App\Console\Commands\Watchlist\RunBacktestExitModelContractAuditCommand;
use App\Console\Commands\Watchlist\RunBacktestExitModelRedesignContractCommand;
use App\Console\Commands\Watchlist\RunBacktestExitAxisSupportAuditCommand;
use App\Console\Commands\Watchlist\RunBacktestOosProofCommand;
use App\Console\Commands\Watchlist\RunBacktestIsCalibrationCommand;
use App\Console\Commands\Watchlist\RunBacktestIsDiagnoseBatchCommand;
use App\Console\Commands\Watchlist\RunBacktestIsDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC18FunnelDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC19SelectionDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC19ProposedSelectionPriceDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC19QualityRecoveryDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC20RegimeTradeDateDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC21EntryExitBehaviorDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC22ExitCaptureShadowDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC23FirstProfitCaptureRuleDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC24C22ShadowGapBridgeDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC25NoSignalFallbackDelayDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC26CatalogCandidateDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC27CatalogCandidateRawOhlcValidateCommand;
use App\Console\Commands\Watchlist\SeedBacktestC01ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC02ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC03ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC04ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC05ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC06ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC07ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC14ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC15ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC16ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestC17ParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestParamGridCommand;
use App\Console\Commands\Watchlist\SeedBacktestR2ParamGridCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        RunBacktestPublishedPriceProofCommand::class,
        RunBacktestExitModelContractAuditCommand::class,
        RunBacktestExitModelRedesignContractCommand::class,
        RunBacktestExitAxisSupportAuditCommand::class,
        RunBacktestOosProofCommand::class,
        RunBacktestIsCalibrationCommand::class,
        RunBacktestIsDiagnoseBatchCommand::class,
        RunBacktestIsDiagnoseCommand::class,
        RunBacktestC18FunnelDiagnoseCommand::class,
        RunBacktestC19SelectionDiagnoseCommand::class,
        RunBacktestC19ProposedSelectionPriceDiagnoseCommand::class,
        RunBacktestC19QualityRecoveryDiagnoseCommand::class,
        RunBacktestC20RegimeTradeDateDiagnoseCommand::class,
        RunBacktestC21EntryExitBehaviorDiagnoseCommand::class,
        RunBacktestC22ExitCaptureShadowDiagnoseCommand::class,
        RunBacktestC23FirstProfitCaptureRuleDiagnoseCommand::class,
        RunBacktestC24C22ShadowGapBridgeDiagnoseCommand::class,
        RunBacktestC25NoSignalFallbackDelayDiagnoseCommand::class,
        RunBacktestC26CatalogCandidateDiagnoseCommand::class,
        RunBacktestC27CatalogCandidateRawOhlcValidateCommand::class,
        SeedBacktestParamGridCommand::class,
        SeedBacktestR2ParamGridCommand::class,
        SeedBacktestC01ParamGridCommand::class,
        SeedBacktestC02ParamGridCommand::class,
        SeedBacktestC03ParamGridCommand::class,
        SeedBacktestC04ParamGridCommand::class,
        SeedBacktestC05ParamGridCommand::class,
        SeedBacktestC06ParamGridCommand::class,
        SeedBacktestC07ParamGridCommand::class,
        SeedBacktestC14ParamGridCommand::class,
        SeedBacktestC15ParamGridCommand::class,
        SeedBacktestC16ParamGridCommand::class,
        SeedBacktestC17ParamGridCommand::class,
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
        RecomputeCurrentIndicatorsCommand::class,
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
