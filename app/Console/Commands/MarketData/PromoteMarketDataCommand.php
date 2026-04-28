<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataPipelineService;

class PromoteMarketDataCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:promote {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--mode=} {--output_dir=} {--latest} {--force_replace=false} {--force_replace_reason=} {--force_reason=}';

    protected $description = 'Promote one requested trade date from persisted import through coverage gate and finalize readability.';

    public function handle()
    {
        $requestedDate = $this->requestedDate();
        $sourceMode = $this->sourceMode();
        $runId = $this->option('run_id') ?: null;

        if ($runId !== null && ! $this->option('source_mode')) {
            $existingRun = $this->runRepository()->findByRunId($runId);
            if ($existingRun) {
                $requestedDate = $existingRun->trade_date_requested ?: $requestedDate;
                $sourceMode = $existingRun->source ?: $sourceMode;
            }
        }
        $correctionId = $this->option('correction_id') ?: null;
        $outputDir = $this->option('output_dir') ?: null;
        $promoteMode = $this->normalizePromoteMode($this->option('mode') ?: null);
        $forceReplace = $this->normalizeForceReplace($this->option('force_replace'));
        $forceReplaceReason = $this->option('force_replace_reason') ?: $this->option('force_reason') ?: null;

        if ($promoteMode !== null && ! in_array($promoteMode, ['full_publish', 'correction_current', 'repair_candidate'], true)) {
            $this->error('error=Unsupported promote mode. Allowed values: full_publish, correction_current, repair_candidate. Aliases: correction, incremental.');
            return 1;
        }

        if ($promoteMode === 'correction_current' && $correctionId === null) {
            $this->error('error=Promote mode correction_current requires --correction_id.');
            return 1;
        }

        if ($forceReplace && ($forceReplaceReason === null || trim((string) $forceReplaceReason) === '')) {
            $this->error('error=--force_replace=true requires --force_replace_reason or --force_reason for audit trail.');
            return 1;
        }

        if ($runId === null) {
            $latestRun = $this->latestRunForRequestedDate($requestedDate, $sourceMode);
            $runId = $latestRun ? $latestRun->run_id : null;
        }

        try {
            $run = $this->pipeline()->promoteDaily($requestedDate, $sourceMode, $runId, $correctionId, $promoteMode, $forceReplace, $forceReplaceReason);
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
                    'force_replace' => $forceReplace,
                    'force_replace_reason' => $forceReplaceReason,
                    'status' => 'ERROR',
                    'error_message' => (string) $e->getMessage(),
                ], $sourceContext)
            ) : null;

            $this->renderRecoveredFailureSummary($run, $e, $sourceContext);
            $this->line('request_mode=promote');
            $this->line('force_replace='.($forceReplace ? 'true' : 'false'));
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
                'force_replace' => $forceReplace,
                'force_replace_reason' => $forceReplaceReason,
                'status' => ((string) ($run->publishability_state ?? '')) === 'READABLE' && ((string) ($run->coverage_gate_state ?? '')) === 'PASS' ? 'SUCCESS' : 'NOT_READABLE',
            ], $sourceContext)
        );

        $this->renderRunSummary($run, $sourceContext);
        $this->line('request_mode=promote');
        $this->line('force_replace='.($forceReplace ? 'true' : 'false'));
        if ($artifactPath !== null) {
            $this->line('output_dir='.$this->normalizePathForDisplay($outputDir));
            $this->line('summary_artifact='.$this->normalizePathForDisplay($artifactPath));
        }
        if ($sourceTelemetryArtifactPath !== null) {
            $this->line('source_attempt_telemetry_artifact='.$this->normalizePathForDisplay($sourceTelemetryArtifactPath));
        }

        return ((string) ($run->publishability_state ?? '')) === 'READABLE' && ((string) ($run->coverage_gate_state ?? '')) === 'PASS' ? 0 : 1;
    }

    private function normalizeForceReplace($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function normalizePromoteMode($promoteMode)
    {
        if ($promoteMode === null || $promoteMode === '') {
            return null;
        }

        $aliases = [
            'correction' => 'correction_current',
            'incremental' => 'repair_candidate',
        ];

        return $aliases[$promoteMode] ?? $promoteMode;
    }
}
