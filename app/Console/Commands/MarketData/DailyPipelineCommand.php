<?php

namespace App\Console\Commands\MarketData;

class DailyPipelineCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:daily {--requested_date=} {--source_mode=} {--input_file=} {--output_dir=} {--correction_id=} {--latest}';

    protected $description = 'Run the minimum daily market-data import-only sequence for one requested trade date.';

    public function handle()
    {
        $previousInputFile = config('market_data.source.local_input_file');
        $configuredOverride = false;

        if ($this->sourceMode() === 'manual_file' && $this->option('input_file')) {
            config()->set('market_data.source.local_input_file', $this->option('input_file'));
            $configuredOverride = true;
        }

        $requestedDate = $this->requestedDate();
        $sourceMode = $this->sourceMode();
        $correctionId = $this->option('correction_id') ?: null;

        $outputDir = $this->option('output_dir') ?: null;

        try {
            $run = $this->pipeline()->runDaily($requestedDate, $sourceMode, $correctionId);
        } catch (\Throwable $e) {
            $run = $this->latestRunForRequestedDate($requestedDate, $sourceMode);
            [$sourceTelemetryArtifactPath, $sourceAttemptTelemetry] = $run ? $this->writeSourceAttemptTelemetryArtifact($outputDir, $run) : [null, []];
            $sourceContext = $run ? $this->buildSourceContext($run, $sourceAttemptTelemetry) : null;
            $artifactPath = $run ? $this->writeRunSummaryArtifact(
                $outputDir,
                'market_data_daily_summary.json',
                $this->buildRunSummaryPayload($run, [
                    'command' => 'market-data:daily',
                    'source_mode' => $sourceMode,
                    'status' => 'ERROR',
                    'error_message' => (string) $e->getMessage(),
                ], $sourceContext)
            ) : null;

            $this->renderRecoveredFailureSummary($run, $e, $sourceContext);
            if ($artifactPath !== null) {
                $this->line('output_dir='.$this->normalizePathForDisplay($outputDir));
                $this->line('summary_artifact='.$this->normalizePathForDisplay($artifactPath));
            }
            if ($sourceTelemetryArtifactPath !== null) {
                $this->line('source_attempt_telemetry_artifact='.$this->normalizePathForDisplay($sourceTelemetryArtifactPath));
            }

            return 1;
        } finally {
            if ($configuredOverride) {
                config()->set('market_data.source.local_input_file', $previousInputFile);
            }
        }

        [$sourceTelemetryArtifactPath, $sourceAttemptTelemetry] = $this->writeSourceAttemptTelemetryArtifact($outputDir, $run);
        $sourceContext = $this->buildSourceContext($run, $sourceAttemptTelemetry);

        $artifactPath = $this->writeRunSummaryArtifact(
            $outputDir,
            'market_data_daily_summary.json',
            $this->buildRunSummaryPayload($run, [
                'command' => 'market-data:daily',
                'source_mode' => $sourceMode,
                'status' => 'SUCCESS',
                'input_file' => $configuredOverride ? $this->normalizeOptionalPathForDisplay((string) $this->option('input_file')) : null,
            ], $sourceContext)
        );

        $this->renderRunSummary($run, $sourceContext);
        if ($configuredOverride) {
            $this->line('input_file='.(string) $this->normalizeOptionalPathForDisplay($this->option('input_file')));
        }
        if ($artifactPath !== null) {
            $this->line('output_dir='.$this->normalizePathForDisplay($outputDir));
            $this->line('summary_artifact='.$this->normalizePathForDisplay($artifactPath));
        }
        if ($sourceTelemetryArtifactPath !== null) {
            $this->line('source_attempt_telemetry_artifact='.$this->normalizePathForDisplay($sourceTelemetryArtifactPath));
        }

        return 0;
    }
}
