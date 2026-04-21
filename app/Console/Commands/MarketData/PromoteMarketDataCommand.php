<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataPipelineService;

class PromoteMarketDataCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:promote {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--mode=} {--output_dir=} {--latest}';

    protected $description = 'Promote one requested trade date from persisted import through coverage gate and finalize readability.';

    public function handle()
    {
        $requestedDate = $this->requestedDate();
        $sourceMode = $this->sourceMode();
        $runId = $this->option('run_id') ?: null;
        $correctionId = $this->option('correction_id') ?: null;
        $outputDir = $this->option('output_dir') ?: null;
        $promoteMode = $this->option('mode') ?: null;

        if ($runId === null) {
            $latestRun = $this->latestRunForRequestedDate($requestedDate, $sourceMode);
            if (! $latestRun) {
                $this->error('error=No persisted import run found for requested_date/source_mode.');
                return 1;
            }

            $runId = (int) $latestRun->run_id;
            $sourceMode = (string) $latestRun->source;
        }

        $requestedPromoteMode = $promoteMode ?: ($correctionId ? 'correction' : 'full_publish');
        $requestedPublishTarget = $requestedPromoteMode === 'full_publish' ? 'current_replace' : 'non_current_correction';

        try {
            $run = $this->pipeline()->promoteDaily($requestedDate, $sourceMode, $runId, $correctionId, $promoteMode);
        } catch (\Throwable $e) {
            $run = $this->runRepository()->findByRunId($runId);
            if ($run) {
                $overlay = (array) $run;
                $overlay['trade_date_requested'] = $overlay['trade_date_requested'] ?? $requestedDate;
                $overlay['stage'] = $overlay['stage'] ?? 'PROMOTE_VALIDATION';
                $overlay['lifecycle_state'] = $overlay['lifecycle_state'] ?? 'FAILED';
                $overlay['terminal_status'] = $overlay['terminal_status'] ?? 'FAILED';
                $overlay['publishability_state'] = $overlay['publishability_state'] ?? 'NOT_READABLE';
                $overlay['promote_mode'] = $requestedPromoteMode;
                $overlay['publish_target'] = $requestedPublishTarget;
                $overlay['reason_code'] = $overlay['reason_code'] ?? $this->resolveFastFailReasonCode($e);
                $overlay['source_name'] = $overlay['source_name'] ?? $this->resolveFastFailSourceName($sourceMode);
                if ($correctionId) {
                    $overlay['correction_id'] = (int) $correctionId;
                }
                $run = (object) $overlay;
            } else {
                $run = (object) [
                    'run_id' => null,
                    'trade_date_requested' => $requestedDate,
                    'stage' => 'PROMOTE_VALIDATION',
                    'lifecycle_state' => 'FAILED',
                    'terminal_status' => 'FAILED',
                    'publishability_state' => 'NOT_READABLE',
                    'promote_mode' => $requestedPromoteMode,
                    'publish_target' => $requestedPublishTarget,
                    'reason_code' => $this->resolveFastFailReasonCode($e),
                    'source_name' => $this->resolveFastFailSourceName($sourceMode),
                    'correction_id' => $correctionId ? (int) $correctionId : null,
                    'notes' => null,
                ];
            }
            [$sourceTelemetryArtifactPath, $sourceAttemptTelemetry] = $run ? $this->writeSourceAttemptTelemetryArtifact($outputDir, $run) : [null, []];
            $sourceContext = $run ? $this->buildSourceContext($run, $sourceAttemptTelemetry) : null;
            $artifactPath = $run ? $this->writeRunSummaryArtifact(
                $outputDir,
                'market_data_promote_summary.json',
                $this->buildRunSummaryPayload($run, [
                    'command' => 'market-data:promote',
                    'request_mode' => 'promote',
                    'source_mode' => $sourceMode,
                    'promote_mode' => $requestedPromoteMode,
                    'status' => 'ERROR',
                    'error_message' => (string) $e->getMessage(),
                ], $sourceContext)
            ) : null;

            $this->renderRecoveredFailureSummary($run, $e, $sourceContext);
            $this->line('request_mode=promote');
            if ($artifactPath !== null) {
                $this->line('output_dir='.$this->normalizePathForDisplay($outputDir));
                $this->line('summary_artifact='.$this->normalizePathForDisplay($artifactPath));
            }
            if ($sourceTelemetryArtifactPath !== null) {
                $this->line('source_attempt_telemetry_artifact='.$this->normalizePathForDisplay($sourceTelemetryArtifactPath));
            }

            return 1;
        }

        [$sourceTelemetryArtifactPath, $sourceAttemptTelemetry] = $this->writeSourceAttemptTelemetryArtifact($outputDir, $run);
        $sourceContext = $this->buildSourceContext($run, $sourceAttemptTelemetry);

        $artifactPath = $this->writeRunSummaryArtifact(
            $outputDir,
            'market_data_promote_summary.json',
            $this->buildRunSummaryPayload($run, [
                'command' => 'market-data:promote',
                'request_mode' => 'promote',
                'source_mode' => $sourceMode,
                'promote_mode' => (string) ($run->promote_mode ?? $requestedPromoteMode),
                'publish_target' => (string) ($run->publish_target ?? $requestedPublishTarget),
                'status' => ((string) ($run->terminal_status ?? '')) === 'SUCCESS' ? 'SUCCESS' : (((string) ($run->publishability_state ?? '')) === 'READABLE' ? 'READABLE' : 'NOT_READABLE'),
            ], $sourceContext)
        );

        $this->renderRunSummary($run, $sourceContext);
        $this->line('request_mode=promote');
        if ($artifactPath !== null) {
            $this->line('output_dir='.$this->normalizePathForDisplay($outputDir));
            $this->line('summary_artifact='.$this->normalizePathForDisplay($artifactPath));
        }
        if ($sourceTelemetryArtifactPath !== null) {
            $this->line('source_attempt_telemetry_artifact='.$this->normalizePathForDisplay($sourceTelemetryArtifactPath));
        }

        return ((string) ($run->terminal_status ?? '')) === 'SUCCESS' ? 0 : ((((string) ($run->publishability_state ?? '')) === 'READABLE') ? 0 : 1);
    }

    private function resolveFastFailReasonCode(\Throwable $e)
    {
        $message = (string) $e->getMessage();

        if (strpos($message, 'already PUBLISHED') !== false) {
            return 'CORRECTION_ALREADY_PUBLISHED';
        }

        if (strpos($message, 'must be APPROVED') !== false) {
            return 'CORRECTION_NOT_APPROVED';
        }

        if (strpos($message, 'trade_date mismatch') !== false) {
            return 'CORRECTION_TRADE_DATE_MISMATCH';
        }

        if (strpos($message, 'not found') !== false) {
            return 'CORRECTION_NOT_FOUND';
        }

        if (strpos($message, 'requires correction_id') !== false) {
            return 'PROMOTE_CORRECTION_ID_REQUIRED';
        }

        return 'PROMOTE_PRECHECK_FAILED';
    }

    private function resolveFastFailSourceName($sourceMode)
    {
        if ((string) $sourceMode === 'manual_file') {
            return 'LOCAL_FILE';
        }

        if ($sourceMode === null || $sourceMode === '') {
            return null;
        }

        return strtoupper((string) $sourceMode);
    }

}
