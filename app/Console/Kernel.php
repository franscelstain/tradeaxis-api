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
use App\Console\Commands\Watchlist\RunBacktestC28RuleRevisionTiebreakDiagnoseCommand;
use App\Console\Commands\Watchlist\RunBacktestC29OosProofCommand;
use App\Console\Commands\Watchlist\RunBacktestC30OosFailureAttributionCommand;
use App\Console\Commands\Watchlist\RunBacktestC31ControlledGateReclassificationCommand;
use App\Console\Commands\Watchlist\RunBacktestC32DataPathAndBadMonthDiagnosticCommand;
use App\Console\Commands\Watchlist\RunBacktestC33DataPathReplayProofCommand;
use App\Console\Commands\Watchlist\RunBacktestC34BadMonthRobustnessDiagnosticCommand;
use App\Console\Commands\Watchlist\RunBacktestC35IsRobustnessRedesignDiagnosticCommand;
use App\Console\Commands\Watchlist\RunBacktestC36IsControlledRedesignCandidateFormationCommand;
use App\Console\Commands\Watchlist\RunBacktestC37IsValidationAntiOverfitCheckCommand;
use App\Console\Commands\Watchlist\RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand;
use App\Console\Commands\Watchlist\RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand;
use App\Console\Commands\Watchlist\RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand;
use App\Console\Commands\Watchlist\RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand;
use App\Console\Commands\Watchlist\RunBacktestC42IsRollingNormalMonthEvidenceExpansionCommand;
use App\Console\Commands\Watchlist\RunBacktestC43PreTradeFieldExpansionDiagnosticCommand;
use App\Console\Commands\Watchlist\RunBacktestC44IsGuardRefinementCandidateFormationCommand;
use App\Console\Commands\Watchlist\RunBacktestC45IsValidationAntiOverfitCheckForC44RefinementCommand;
use App\Console\Commands\Watchlist\RunBacktestC46IsReviewEvidenceExpansionBeforeOosCommand;
use App\Console\Commands\Watchlist\RunBacktestC47OosProofWithLockedC44RefinementCommand;
use App\Console\Commands\Watchlist\RunBacktestC48OosFailureAttributionCommand;
use App\Console\Commands\Watchlist\RunBacktestC49BroaderStrategyRedesignCommand;
use App\Console\Commands\Watchlist\RunBacktestC50IsValidationAntiOverfitCheckCommand;
use App\Console\Commands\Watchlist\RunBacktestC51ConcentrationDependencyRedesignReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC52ConcentrationDependencyRedesignContinuationCommand;
use App\Console\Commands\Watchlist\RunBacktestC53IsEvidenceExpansionForC52RedesignCommand;
use App\Console\Commands\Watchlist\RunBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC55RollingStabilityRedesignContinuationIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC56RollingStabilityRedesignContinuationIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC57RegimeFieldReconstructionContinuationIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC63PreOosUnlockReviewIsOnlyCommand;
use App\Console\Commands\Watchlist\RunBacktestC64PreOosOrOosProofExecutionCommand;
use App\Console\Commands\Watchlist\RunBacktestC65ProductionPreLockReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC66ProductionLockReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC67ProductionCatalogActivationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC68ProductionCatalogActivationExecutionReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC69ProductionDeploymentPrepOrBridgeReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC70ProductionDeploymentExecutionReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC71ShadowReadOrDryRunRuntimeValidationCommand;
use App\Console\Commands\Watchlist\RunBacktestC72ControlledOptInRuntimeBridgeValidationCommand;
use App\Console\Commands\Watchlist\RunBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationCommand;
use App\Console\Commands\Watchlist\RunBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand;
use App\Console\Commands\Watchlist\RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand;
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
        RunBacktestC28RuleRevisionTiebreakDiagnoseCommand::class,
        RunBacktestC29OosProofCommand::class,
        RunBacktestC30OosFailureAttributionCommand::class,
        RunBacktestC31ControlledGateReclassificationCommand::class,
        RunBacktestC32DataPathAndBadMonthDiagnosticCommand::class,
        RunBacktestC33DataPathReplayProofCommand::class,
        RunBacktestC34BadMonthRobustnessDiagnosticCommand::class,
        RunBacktestC35IsRobustnessRedesignDiagnosticCommand::class,
        RunBacktestC36IsControlledRedesignCandidateFormationCommand::class,
        RunBacktestC37IsValidationAntiOverfitCheckCommand::class,
        RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand::class,
        RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand::class,
        RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand::class,
        RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand::class,
        RunBacktestC42IsRollingNormalMonthEvidenceExpansionCommand::class,
        RunBacktestC43PreTradeFieldExpansionDiagnosticCommand::class,
        RunBacktestC44IsGuardRefinementCandidateFormationCommand::class,
        RunBacktestC45IsValidationAntiOverfitCheckForC44RefinementCommand::class,
        RunBacktestC46IsReviewEvidenceExpansionBeforeOosCommand::class,
        RunBacktestC47OosProofWithLockedC44RefinementCommand::class,
        RunBacktestC48OosFailureAttributionCommand::class,
        RunBacktestC49BroaderStrategyRedesignCommand::class,
        RunBacktestC50IsValidationAntiOverfitCheckCommand::class,
        RunBacktestC51ConcentrationDependencyRedesignReviewCommand::class,
        RunBacktestC52ConcentrationDependencyRedesignContinuationCommand::class,
        RunBacktestC53IsEvidenceExpansionForC52RedesignCommand::class,
        RunBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyCommand::class,
        RunBacktestC55RollingStabilityRedesignContinuationIsOnlyCommand::class,
        RunBacktestC56RollingStabilityRedesignContinuationIsOnlyCommand::class,
        RunBacktestC57RegimeFieldReconstructionContinuationIsOnlyCommand::class,
        RunBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyCommand::class,
        RunBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyCommand::class,
        RunBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyCommand::class,
        RunBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyCommand::class,
        RunBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyCommand::class,
        RunBacktestC63PreOosUnlockReviewIsOnlyCommand::class,
        RunBacktestC64PreOosOrOosProofExecutionCommand::class,
        RunBacktestC65ProductionPreLockReviewCommand::class,
        RunBacktestC66ProductionLockReviewCommand::class,
        RunBacktestC67ProductionCatalogActivationReviewCommand::class,
        RunBacktestC68ProductionCatalogActivationExecutionReviewCommand::class,
        RunBacktestC69ProductionDeploymentPrepOrBridgeReviewCommand::class,
        RunBacktestC70ProductionDeploymentExecutionReviewCommand::class,
        RunBacktestC71ShadowReadOrDryRunRuntimeValidationCommand::class,
        RunBacktestC72ControlledOptInRuntimeBridgeValidationCommand::class,
        RunBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationCommand::class,
        RunBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewCommand::class,
        RunBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewCommand::class,
        RunBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewCommand::class,
        RunBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewCommand::class,
        RunBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewCommand::class,
        RunBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewCommand::class,
        RunBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewCommand::class,
        RunBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewCommand::class,
        RunBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewCommand::class,
        RunBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewCommand::class,
        RunBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewCommand::class,
        RunBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewCommand::class,
        RunBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewCommand::class,
        RunBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewCommand::class,
        RunBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewCommand::class,
        RunBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewCommand::class,
        RunBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewCommand::class,
        RunBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewCommand::class,
        RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand::class,
        RunBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewCommand::class,
        RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand::class,
        RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand::class,
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
