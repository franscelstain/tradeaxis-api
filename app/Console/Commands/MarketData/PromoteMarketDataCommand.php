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

        if ($promoteMode !== null && ! in_array($promoteMode, ['full_publish', 'correction', 'incremental'], true)) {
            $this->error('error=Unsupported promote mode. Allowed values: full_publish, correction, incremental.');
            return 1;
        }

        if ($promoteMode === 'correction' && $correctionId === null) {
            $this->error('error=Promote mode correction requires --correction_id.');
            return 1;
        }

        try {
            $run = $this->pipeline()->promoteDaily($requestedDate, $sourceMode, $runId, $correctionId, $promoteMode);
        } catch (\Throwable $e) {
            $run = $this->runRepository()->findByRunId($runId);
            [$sourceTelemetryArtifactPath, $sourceAttemptTelemetry] = $run ? $this->writeSourceAttemptTelemetryArtifact($outputDir, $run) : [null, []];
            $sourceContext = $run ? $this->buildSourceContext($run, $sourceAttemptTelemetry) : null;
            $artifactPath = $run ? $this->writeRunSummaryArtifact(
                $outputDir,
                'market_data_promote_summary.json',
                $this->buildRunSummaryPayload($run, [
                    'command' => 'market-data:promote',
                    'request_mode' => 'promote',
                    'source_mode' => $sourceMode,
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
                'status' => ((string) ($run->publishability_state ?? '')) === 'READABLE' ? 'SUCCESS' : 'NOT_READABLE',
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

        return ((string) ($run->publishability_state ?? '')) === 'READABLE' ? 0 : 1;
    }
}
