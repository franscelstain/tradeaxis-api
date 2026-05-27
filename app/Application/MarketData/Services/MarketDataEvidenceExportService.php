<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;

class MarketDataEvidenceExportService
{
    private function field($record, $name, $default = null)
    {
        return is_object($record) && property_exists($record, $name) ? $record->{$name} : $default;
    }

    private $evidence;
    private $publications;
    private $corrections;

    public function __construct(
        EodEvidenceRepository $evidence,
        EodPublicationRepository $publications,
        EodCorrectionRepository $corrections
    ) {
        $this->evidence = $evidence;
        $this->publications = $publications;
        $this->corrections = $corrections;
    }

    public function exportRunEvidence($runId, $outputDir = null)
    {
        $run = $this->evidence->findRunById($runId);
        if (! $run) {
            throw new \RuntimeException('Run not found for evidence export.');
        }


        $publication = $this->resolvePublicationForRun($run);
        $manifest = $publication ? (array) $this->publications->buildManifestByPublicationId($publication->publication_id) : null;
        $runSummary = $this->buildRunSummary($run, $manifest);
        $sourceAttemptTelemetry = $this->buildSourceAttemptTelemetry($run, $runSummary['source_context'] ?? []);
        $runSummary['source_context'] = $this->normalizeSourceContextPaths(
            $this->mergeSourceContextFromTelemetry(
                is_array($runSummary['source_context'] ?? null) ? $runSummary['source_context'] : [],
                $sourceAttemptTelemetry
            )
        );
        $sourceAttemptTelemetry = $this->normalizeSourceAttemptTelemetryPaths($sourceAttemptTelemetry);
        $runSummary['source_context']['source_summary'] = $this->buildSourceSummaryString($runSummary['source_context'] ?? []);
        $sourceSummary = $runSummary['source_context']['source_summary'];
        $eventSummary = ['run_id' => (int) $this->field($run, 'run_id'), 'trade_date_requested' => $run->trade_date_requested] + $this->evidence->summarizeRunEvents($run->run_id);
        $dominantReasonCodes = $publication
            ? $this->evidence->dominantReasonCodesForEvidencePublication($run->run_id, $this->resolvedTradeDate($run), $publication->publication_id, (bool) $this->field($publication, 'is_current'))
            : $this->dominantReasonCodesFromRunEvents($eventSummary);
        $eligibilityRows = $publication
            ? $this->evidence->exportEligibilityRowsForEvidencePublication($this->resolvedTradeDate($run), $publication->publication_id, (bool) $this->field($publication, 'is_current'))
            : [];
        $invalidBarsRows = $this->evidence->exportInvalidBarsRows($run->trade_date_requested, $run->run_id);
        $publicationContext = $this->buildPublicationContext($run, $publication, $manifest);
        $pointerContext = $this->buildPointerContext($run, $publication, $manifest);
        $fallbackContext = $this->buildFallbackContext($run, $pointerContext, $publicationContext);
        $artifactContext = $this->buildArtifactSealContext($run, $manifest);
        $correctionContext = $this->buildRunCorrectionContext($run);
        $lineage = $this->buildLineageContext($runSummary, $artifactContext, $publicationContext, $pointerContext, $fallbackContext, $correctionContext);
        $completeness = $this->buildEvidenceCompleteness($runSummary, $publicationContext, $pointerContext, $fallbackContext, $artifactContext, $lineage, $correctionContext);
        $admission = $this->buildEvidenceAdmission('run', (int) $this->field($run, 'run_id'), [
            'run_summary',
            'run_event_summary',
            'source_context',
            'coverage_context',
            'artifact_context',
            'publication_context',
            'pointer_context',
            'fallback_context',
            'correction_context',
            'lineage',
        ], $completeness['missing_sections'], $runSummary['evidence_export_created_at'] ?? null);
        $runSummary['evidence_completeness_state'] = $completeness['evidence_completeness_state'];
        $runSummary['evidence_admission_state'] = $admission['evidence_admission_state'];
        $runSummary['evidence_missing_sections'] = $completeness['missing_sections'];
        $anomalyReport = $this->buildAnomalyReport($runSummary, $dominantReasonCodes, $manifest, $completeness);

        $payload = [
            'evidence_admission' => $admission,
            'evidence_completeness' => $completeness,
            'run_summary' => $runSummary,
            'source_context' => $runSummary['source_context'],
            'coverage_context' => $runSummary['coverage'],
            'artifact_context' => $artifactContext,
            'publication_manifest' => $manifest,
            'publication_context' => $publicationContext,
            'pointer_context' => $pointerContext,
            'fallback_context' => $fallbackContext,
            'correction_context' => $correctionContext,
            'lineage' => $lineage,
            'run_event_summary' => $eventSummary,
            'dominant_reason_codes' => $dominantReasonCodes,
            'publication_resolution' => $publicationContext + [
                'pointer_context' => $pointerContext,
            ],
            'source_attempt_telemetry' => $sourceAttemptTelemetry,
        ];

        $dir = $outputDir ?: $this->defaultRunOutputDir($run->run_id);
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/run_summary.json', $runSummary);
        if ($manifest) {
            $this->writeJson($dir.'/publication_manifest.json', $manifest);
        }
        $this->writeJson($dir.'/run_event_summary.json', $eventSummary);
        if ($sourceAttemptTelemetry !== null) {
            $this->writeJson($dir.'/source_attempt_telemetry.json', $sourceAttemptTelemetry);
        }
        $this->writeCsv($dir.'/eligibility_export.csv', ['trade_date', 'ticker_id', 'eligible', 'reason_code'], $eligibilityRows);
        $this->writeCsv($dir.'/invalid_bars_export.csv', ['trade_date', 'ticker_id', 'source', 'source_row_ref', 'invalid_reason_code'], $invalidBarsRows);
        file_put_contents($dir.'/anomaly_report.md', $anomalyReport);
        $this->writeJson($dir.'/lineage.json', $lineage);
        $this->writeJson($dir.'/evidence_admission.json', $admission);
        $this->writeJson($dir.'/evidence_completeness.json', $completeness);
        $this->writeJson($dir.'/evidence_pack.json', $payload);

        $files = array_values(array_filter([
            'run_summary.json',
            $manifest ? 'publication_manifest.json' : null,
            'run_event_summary.json',
            $sourceAttemptTelemetry !== null ? 'source_attempt_telemetry.json' : null,
            'eligibility_export.csv',
            'invalid_bars_export.csv',
            'anomaly_report.md',
            'lineage.json',
            'evidence_admission.json',
            'evidence_completeness.json',
            'evidence_pack.json',
        ]));

        return [
            'selector' => ['type' => 'run', 'id' => (int) $run->run_id],
            'summary' => [
                'run_id' => (int) $this->field($run, 'run_id'),
                'trade_date_requested' => $runSummary['trade_date_requested'],
                'trade_date_effective' => $runSummary['trade_date_effective'],
                'terminal_status' => $runSummary['terminal_status'],
                'publishability_state' => $runSummary['publishability_state'],
                'coverage_gate_state' => $runSummary['coverage']['coverage_gate_state'] ?? null,
                'final_reason_code' => $runSummary['final_reason_code'] ?? null,
                'evidence_completeness_state' => $completeness['evidence_completeness_state'],
                'evidence_admission_state' => $admission['evidence_admission_state'],
                'publication_id' => $publicationContext['publication_id'] ?? null,
                'pointer_resolve_status' => $pointerContext['pointer_resolve_status'] ?? null,
                'fallback_used' => $fallbackContext['fallback_used'] ?? false,
                'source_name' => $runSummary['source_context']['source_name'] ?? null,
                'source_input_file' => isset($runSummary['source_context']['source_input_file'])
                    ? $this->normalizeOptionalPathForDisplay($runSummary['source_context']['source_input_file'])
                    : null,
                'source_summary' => $sourceSummary,
                'source_attempt_event_type' => $sourceAttemptTelemetry['event_type'] ?? null,
                'source_attempt_count' => $sourceAttemptTelemetry['attempt_count'] ?? null,
            ],
            'output_dir' => $dir,
            'file_count' => count($files),
            'files' => $files,
        ];
    }

    private function buildPublicationContext($run, $publication, $manifest)
    {
        $runCoverageGateState = CoverageGateStateNormalizer::normalize($this->field($run, 'coverage_gate_state'));
        $runLegacyCoverageGateStateRaw = CoverageGateStateNormalizer::legacyRaw($this->field($run, 'coverage_gate_state'));
        $publicationId = $manifest ? (int) $manifest['publication_id'] : ($this->field($publication, 'publication_id') !== null ? (int) $this->field($publication, 'publication_id') : ($this->field($run, 'publication_id') !== null ? (int) $this->field($run, 'publication_id') : null));
        $publicationRunId = $manifest ? (int) $manifest['run_id'] : ($this->field($publication, 'run_id') !== null ? (int) $this->field($publication, 'run_id') : null);
        $publicationVersion = $manifest ? (int) $manifest['publication_version'] : ($this->field($publication, 'publication_version') !== null ? (int) $this->field($publication, 'publication_version') : ($this->field($run, 'publication_version') !== null ? (int) $this->field($run, 'publication_version') : null));
        $isCurrent = $manifest ? (bool) $manifest['is_current'] : ($this->field($publication, 'is_current') !== null ? (bool) $this->field($publication, 'is_current') : false);
        $sealState = $manifest ? ($manifest['seal_state'] ?? null) : $this->field($publication, 'seal_state');
        $runPublicationMirrorValid = $publicationId !== null
            && $publicationVersion !== null
            && (string) $this->field($run, 'publication_id') === (string) $publicationId
            && (string) $this->field($run, 'publication_version') === (string) $publicationVersion;

        return [
            'trade_date_requested' => $this->field($run, 'trade_date_requested'),
            'trade_date_effective' => $this->field($run, 'trade_date_effective'),
            'terminal_status' => $this->field($run, 'terminal_status'),
            'publishability_state' => $this->field($run, 'publishability_state'),
            'coverage_gate_state' => $runCoverageGateState,
            'legacy_coverage_gate_state_raw' => $runLegacyCoverageGateStateRaw,
            'publication_id' => $publicationId,
            'publication_version' => $publicationVersion,
            'publication_run_id' => $publicationRunId,
            'publication_trade_date' => $manifest ? ($manifest['trade_date'] ?? null) : $this->field($publication, 'trade_date'),
            'publication_trade_date_effective' => $manifest ? ($manifest['trade_date_effective'] ?? null) : $this->field($run, 'trade_date_effective'),
            'publication_state' => $publicationId === null ? 'NOT_CREATED_OR_NOT_READABLE' : ($isCurrent ? 'CURRENT' : ($this->field($publication, 'evidence_resolution_mode') === 'HISTORICAL_PUBLICATION_AUDIT' ? 'HISTORICAL_SEALED_PUBLICATION' : 'NON_CURRENT')),
            'evidence_resolution_mode' => $this->field($publication, 'evidence_resolution_mode') ?: ($isCurrent ? 'CURRENT_READABLE_PUBLICATION_AUDIT' : null),
            'evidence_publication_scope' => $this->field($publication, 'evidence_publication_scope') ?: ($isCurrent ? 'CURRENT_POINTER_PUBLICATION' : null),
            'evidence_selector_type' => $this->field($publication, 'evidence_selector_type'),
            'evidence_selector_id' => $this->field($publication, 'evidence_selector_id') !== null ? (int) $this->field($publication, 'evidence_selector_id') : null,
            'historical_publication_allowed' => (bool) $this->field($publication, 'historical_publication_allowed', false),
            'current_pointer_required' => (bool) $this->field($publication, 'current_pointer_required', $isCurrent),
            'current_pointer_status' => $this->field($publication, 'current_pointer_status') ?: ($isCurrent ? 'RESOLVED_READABLE_CURRENT' : 'NOT_CURRENT_POINTER'),
            'artifact_scope' => $this->field($publication, 'artifact_scope') ?: ($publicationId !== null ? 'PUBLICATION_SCOPED' : null),
            'coverage_basis_publication_id' => $this->field($publication, 'coverage_basis_publication_id') !== null ? (int) $this->field($publication, 'coverage_basis_publication_id') : $publicationId,
            'coverage_basis_run_id' => $this->field($publication, 'coverage_basis_run_id') !== null ? (int) $this->field($publication, 'coverage_basis_run_id') : $publicationRunId,
            'lineage_verification_status' => $this->field($publication, 'lineage_verification_status') ?: ($publicationId !== null && $runPublicationMirrorValid ? 'LINEAGE_VERIFIED' : 'LINEAGE_NOT_VERIFIED'),
            'evidence_reason_code' => $this->field($publication, 'evidence_reason_code') ?: ($isCurrent ? 'CURRENT_READABLE_PUBLICATION_RESOLVED' : null),
            'publication_seal_state' => $sealState,
            'publication_publishability_state' => $this->field($run, 'publishability_state'),
            'publication_terminal_status' => $this->field($run, 'terminal_status'),
            'publication_is_current' => $isCurrent,
            'publication_created_at' => $manifest ? ($manifest['created_at'] ?? null) : $this->field($publication, 'created_at'),
            'publication_updated_at' => $manifest ? ($manifest['updated_at'] ?? null) : $this->field($publication, 'updated_at'),
            'publication_reason_code' => $this->field($run, 'final_reason_code') ?: $this->resolveCoverageReasonCodeFromState($this->field($run, 'coverage_gate_state')),
            'publication_reason_message' => $this->resolveReasonMessage($this->field($run, 'final_reason_code') ?: $this->resolveCoverageReasonCodeFromState($this->field($run, 'coverage_gate_state'))),
            'publication_sealed_at' => $manifest ? ($manifest['sealed_at'] ?? null) : $this->field($publication, 'sealed_at'),
            'is_current_publication' => $isCurrent,
            'run_publication_mirror_valid' => $runPublicationMirrorValid,
            'publication_artifact_lineage' => [
                'bars_batch_hash' => $manifest ? ($manifest['bars_batch_hash'] ?? null) : $this->field($run, 'bars_batch_hash'),
                'indicators_batch_hash' => $manifest ? ($manifest['indicators_batch_hash'] ?? null) : $this->field($run, 'indicators_batch_hash'),
                'eligibility_batch_hash' => $manifest ? ($manifest['eligibility_batch_hash'] ?? null) : $this->field($run, 'eligibility_batch_hash'),
                'source_file_hash' => $manifest ? ($manifest['source_file_hash'] ?? null) : $this->field($run, 'source_file_hash'),
            ],
        ];
    }

    private function buildPointerContext($run, $publication, $manifest)
    {
        $rawPointer = null;
        if (! $publication) {
            try {
                $rawPointer = $this->publications->findRawCurrentPublicationStateForTradeDate($this->field($run, 'trade_date_requested'));
            } catch (\Throwable $e) {
                $rawPointer = null;
            }
        }

        $pointerPublicationId = $this->field($publication, 'pointer_publication_id')
            ?: ($manifest ? ($manifest['publication_id'] ?? null) : null)
            ?: $this->field($rawPointer, 'pointer_publication_id');
        $pointerRunId = $this->field($publication, 'pointer_run_id')
            ?: ($manifest ? ($manifest['run_id'] ?? null) : null)
            ?: $this->field($rawPointer, 'pointer_run_id');
        $pointerVersion = $this->field($publication, 'pointer_publication_version')
            ?: ($manifest ? ($manifest['publication_version'] ?? null) : null)
            ?: $this->field($rawPointer, 'pointer_publication_version');
        $isAuditResolvedPublication = $publication !== null && $this->field($publication, 'evidence_resolution_mode') !== null;
        $isPointerResolvedCurrent = $publication !== null
            && (! $isAuditResolvedPublication || (string) $this->field($publication, 'current_pointer_status') === 'RESOLVED_READABLE_CURRENT');
        $resolvedStatus = $publication
            ? ($isPointerResolvedCurrent ? 'RESOLVED_READABLE_CURRENT' : 'HISTORICAL_SEALED_PUBLICATION_RESOLVED')
            : ($rawPointer ? 'RAW_POINTER_NOT_READABLE' : 'MISSING');
        $mismatchReason = null;

        if ($publication && ! $isPointerResolvedCurrent) {
            $mismatchReason = 'HISTORICAL_PUBLICATION_NOT_CURRENT_POINTER';
        } elseif ($publication && $pointerPublicationId !== null && (string) $pointerPublicationId !== (string) $this->field($publication, 'publication_id')) {
            $mismatchReason = 'POINTER_PUBLICATION_ID_MISMATCH';
        } elseif ($publication && $pointerRunId !== null && (string) $pointerRunId !== (string) $this->field($publication, 'run_id')) {
            $mismatchReason = 'POINTER_RUN_ID_MISMATCH';
        } elseif (! $publication && $this->isReadableRun($run)) {
            $mismatchReason = 'READABLE_RUN_WITHOUT_POINTER_RESOLVED_PUBLICATION';
        } elseif (! $publication && ! $rawPointer) {
            $mismatchReason = 'CURRENT_POINTER_ROW_MISSING';
        }

        $readablePointerValidated = $publication !== null && $mismatchReason === null && $isPointerResolvedCurrent;

        return [
            'pointer_id' => null,
            'pointer_trade_date' => $this->field($publication, 'pointer_trade_date') ?: $this->field($rawPointer, 'pointer_trade_date') ?: $this->field($run, 'trade_date_requested'),
            'pointer_publication_id' => $pointerPublicationId !== null ? (int) $pointerPublicationId : null,
            'pointer_publication_version' => $pointerVersion !== null ? (int) $pointerVersion : null,
            'pointer_run_id' => $pointerRunId !== null ? (int) $pointerRunId : null,
            'pointer_state' => $publication ? ($isPointerResolvedCurrent ? 'CURRENT_READABLE' : 'CURRENT_POINTER_DIFFERENT_PUBLICATION') : ($rawPointer ? 'PRESENT_BUT_NOT_READABLE' : 'ABSENT'),
            'pointer_resolved_publication_id' => $isPointerResolvedCurrent ? (int) $this->field($publication, 'publication_id') : null,
            'pointer_resolved_run_id' => $isPointerResolvedCurrent ? (int) $this->field($publication, 'run_id') : null,
            'pointer_resolve_status' => $resolvedStatus,
            'pointer_switched' => $isPointerResolvedCurrent && $publication !== null && $manifest !== null && (bool) ($manifest['is_current'] ?? false),
            'pointer_switch_allowed' => $this->isReadableRun($run) && CoverageGateStateNormalizer::normalize($this->field($run, 'coverage_gate_state')) === 'PASS' && $publication !== null && $isPointerResolvedCurrent,
            'pointer_switch_reason_code' => $this->field($run, 'final_reason_code') ?: $this->resolveCoverageReasonCodeFromState($this->field($run, 'coverage_gate_state')),
            'pointer_previous_publication_id' => $manifest ? ($manifest['previous_publication_id'] ?? ($manifest['supersedes_publication_id'] ?? ($manifest['replaced_publication_id'] ?? null))) : null,
            'pointer_previous_run_id' => $this->field($run, 'supersedes_run_id') !== null ? (int) $this->field($run, 'supersedes_run_id') : null,
            'pointer_post_switch_validation' => $readablePointerValidated,
            'readable_pointer_validated' => $readablePointerValidated,
            'pointer_mismatch_reason' => $mismatchReason,
            'raw_pointer_context' => $rawPointer ? [
                'publication_id' => $this->field($rawPointer, 'publication_id') !== null ? (int) $this->field($rawPointer, 'publication_id') : null,
                'run_id' => $this->field($rawPointer, 'run_id') !== null ? (int) $this->field($rawPointer, 'run_id') : null,
                'publication_version' => $this->field($rawPointer, 'publication_version') !== null ? (int) $this->field($rawPointer, 'publication_version') : null,
                'seal_state' => $this->field($rawPointer, 'seal_state'),
                'terminal_status' => $this->field($rawPointer, 'terminal_status'),
                'publishability_state' => $this->field($rawPointer, 'publishability_state'),
                'coverage_gate_state' => CoverageGateStateNormalizer::normalize($this->field($rawPointer, 'coverage_gate_state')),
                'legacy_coverage_gate_state_raw' => CoverageGateStateNormalizer::legacyRaw($this->field($rawPointer, 'coverage_gate_state')),
            ] : null,
        ];
    }

    private function buildFallbackContext($run, array $pointerContext, array $publicationContext)
    {
        $requested = (string) $this->field($run, 'trade_date_requested');
        $effective = (string) $this->field($run, 'trade_date_effective');
        $fallbackUsed = ! $this->isReadableRun($run)
            && $effective !== ''
            && $requested !== ''
            && $effective !== $requested;
        $rawPointer = is_array($pointerContext['raw_pointer_context'] ?? null) ? $pointerContext['raw_pointer_context'] : [];

        return [
            'fallback_used' => $fallbackUsed,
            'fallback_reason_code' => $fallbackUsed
                ? ($this->field($run, 'final_reason_code') ?: $this->field($run, 'source_final_reason_code') ?: $this->resolveCoverageReasonCodeFromState($this->field($run, 'coverage_gate_state')))
                : ($this->isReadableRun($run) ? 'FALLBACK_NOT_REQUIRED' : 'NO_READABLE_FALLBACK_CLAIMED'),
            'fallback_reason_message' => $fallbackUsed ? 'Run is not readable and effective date differs from requested date; previous readable context must be used by consumers.' : 'No fallback publication is claimed by this evidence export.',
            'fallback_publication_id' => $fallbackUsed ? ($rawPointer['publication_id'] ?? null) : null,
            'fallback_run_id' => $fallbackUsed ? ($rawPointer['run_id'] ?? null) : null,
            'fallback_publication_version' => $fallbackUsed ? ($rawPointer['publication_version'] ?? null) : null,
            'fallback_trade_date_effective' => $fallbackUsed ? $effective : null,
            'fallback_pointer_resolved' => $fallbackUsed ? (($pointerContext['pointer_resolve_status'] ?? null) === 'RESOLVED_READABLE_CURRENT') : false,
            'fallback_publication_sealed' => $fallbackUsed ? (($rawPointer['seal_state'] ?? null) === 'SEALED') : false,
            'fallback_publication_readable' => $fallbackUsed ? (($rawPointer['publishability_state'] ?? null) === 'READABLE') : false,
            'fallback_publication_success' => $fallbackUsed ? (($rawPointer['terminal_status'] ?? null) === 'SUCCESS') : false,
            'fallback_coverage_pass' => $fallbackUsed ? (CoverageGateStateNormalizer::normalize($rawPointer['coverage_gate_state'] ?? null) === 'PASS') : false,
            'fallback_source_mode' => $fallbackUsed ? $this->field($run, 'source') : null,
            'fallback_lineage' => $fallbackUsed ? [
                'requested_run_id' => (int) $this->field($run, 'run_id'),
                'requested_trade_date' => $requested,
                'effective_trade_date' => $effective,
                'pointer_publication_id' => $pointerContext['pointer_publication_id'] ?? null,
            ] : null,
        ];
    }

    private function buildArtifactSealContext($run, $manifest)
    {
        $artifacts = [
            'bars' => [
                'artifact_id' => null,
                'artifact_type' => 'eod_bars',
                'artifact_path' => null,
                'artifact_name' => 'bars_batch_hash',
                'artifact_hash' => $manifest ? ($manifest['bars_batch_hash'] ?? null) : $this->field($run, 'bars_batch_hash'),
                'hash_algorithm' => (string) $this->configValue('market_data.hash.algorithm', 'SHA-256'),
                'artifact_row_count' => $manifest ? ($manifest['bars_rows_written'] ?? null) : $this->field($run, 'bars_rows_written'),
            ],
            'indicators' => [
                'artifact_id' => null,
                'artifact_type' => 'eod_indicators',
                'artifact_path' => null,
                'artifact_name' => 'indicators_batch_hash',
                'artifact_hash' => $manifest ? ($manifest['indicators_batch_hash'] ?? null) : $this->field($run, 'indicators_batch_hash'),
                'hash_algorithm' => (string) $this->configValue('market_data.hash.algorithm', 'SHA-256'),
                'artifact_row_count' => $manifest ? ($manifest['indicators_rows_written'] ?? null) : $this->field($run, 'indicators_rows_written'),
            ],
            'eligibility' => [
                'artifact_id' => null,
                'artifact_type' => 'eod_eligibility',
                'artifact_path' => null,
                'artifact_name' => 'eligibility_batch_hash',
                'artifact_hash' => $manifest ? ($manifest['eligibility_batch_hash'] ?? null) : $this->field($run, 'eligibility_batch_hash'),
                'hash_algorithm' => (string) $this->configValue('market_data.hash.algorithm', 'SHA-256'),
                'artifact_row_count' => $manifest ? ($manifest['eligibility_rows_written'] ?? null) : $this->field($run, 'eligibility_rows_written'),
            ],
        ];

        $missing = [];
        foreach ($artifacts as $name => $artifact) {
            if (($artifact['artifact_hash'] ?? null) === null || ($artifact['artifact_hash'] ?? '') === '') {
                $missing[] = $name;
            }
        }

        return [
            'artifacts' => $artifacts,
            'mandatory_artifact_presence' => $missing === [],
            'missing_artifact_reason' => $missing === [] ? null : 'MISSING_ARTIFACT_HASH: '.implode(',', $missing),
            'artifact_created_at' => $this->field($run, 'finished_at') ?: $this->field($run, 'updated_at'),
            'seal_state' => $manifest ? ($manifest['seal_state'] ?? null) : ($this->field($run, 'sealed_at') ? 'SEALED' : 'UNSEALED'),
            'seal_hash' => $manifest ? ($manifest['bars_batch_hash'] ?? null).'|'.($manifest['indicators_batch_hash'] ?? null).'|'.($manifest['eligibility_batch_hash'] ?? null) : $this->field($run, 'bars_batch_hash').'|'.$this->field($run, 'indicators_batch_hash').'|'.$this->field($run, 'eligibility_batch_hash'),
            'seal_manifest' => $manifest,
            'seal_reason' => $missing === [] ? null : 'Dataset seal cannot be proven because one or more mandatory artifact hashes are missing.',
            'dataset_seal_state' => $manifest ? ($manifest['seal_state'] ?? null) : ($this->field($run, 'sealed_at') ? 'SEALED' : 'UNSEALED'),
            'dataset_seal_timestamp' => $manifest ? ($manifest['sealed_at'] ?? null) : $this->field($run, 'sealed_at'),
        ];
    }

    private function buildRunCorrectionContext($run)
    {
        $correction = null;
        try {
            if ($this->field($run, 'correction_id') !== null) {
                $correction = $this->evidence->findCorrectionByRunId($this->field($run, 'run_id'));
            }
        } catch (\Throwable $e) {
            $correction = null;
        }

        return [
            'correction_related' => $correction !== null || $this->field($run, 'correction_id') !== null,
            'correction_id' => $correction && $this->field($correction, 'correction_id') !== null ? (int) $this->field($correction, 'correction_id') : ($this->field($run, 'correction_id') !== null ? (int) $this->field($run, 'correction_id') : null),
            'correction_status' => $this->field($correction, 'status'),
            'correction_reason_code' => $this->field($correction, 'correction_reason_code'),
            'correction_reason_message' => $this->resolveReasonMessage($this->field($correction, 'correction_reason_code')),
            'correction_baseline_publication_id' => $this->field($correction, 'baseline_publication_id') !== null ? (int) $this->field($correction, 'baseline_publication_id') : ($this->field($correction, 'prior_publication_id') !== null ? (int) $this->field($correction, 'prior_publication_id') : null),
            'correction_baseline_run_id' => $this->field($correction, 'prior_run_id') !== null ? (int) $this->field($correction, 'prior_run_id') : null,
            'correction_candidate_publication_id' => $this->field($correction, 'replacement_publication_id') !== null ? (int) $this->field($correction, 'replacement_publication_id') : ($this->field($correction, 'new_publication_id') !== null ? (int) $this->field($correction, 'new_publication_id') : null),
            'correction_candidate_run_id' => $this->field($correction, 'new_run_id') !== null ? (int) $this->field($correction, 'new_run_id') : null,
        ];
    }

    private function buildLineageContext(array $runSummary, array $artifactContext, array $publicationContext, array $pointerContext, array $fallbackContext, array $correctionContext)
    {
        return [
            'ingest_source_to_run' => [
                'source_mode' => $runSummary['source_context']['source_mode'] ?? $runSummary['source_mode'] ?? null,
                'source_name' => $runSummary['source_context']['source_name'] ?? null,
                'source_provider' => $runSummary['source_context']['source_provider'] ?? ($runSummary['source_context']['provider'] ?? null),
                'source_input_file' => $runSummary['source_context']['source_input_file'] ?? null,
                'source_file_hash' => $runSummary['source_context']['source_file_hash'] ?? null,
                'run_id' => $runSummary['run_id'],
            ],
            'run_to_artifacts' => [
                'run_id' => $runSummary['run_id'],
                'bars_batch_hash' => $runSummary['bars_batch_hash'] ?? null,
                'indicators_batch_hash' => $runSummary['indicators_batch_hash'] ?? null,
                'eligibility_batch_hash' => $runSummary['eligibility_batch_hash'] ?? null,
                'artifacts' => $artifactContext['artifacts'] ?? [],
            ],
            'run_to_coverage_decision' => [
                'run_id' => $runSummary['run_id'],
                'coverage_gate_state' => $runSummary['coverage']['coverage_gate_state'] ?? null,
                'coverage_reason_code' => $runSummary['coverage']['coverage_reason_code'] ?? null,
            ],
            'run_to_finalize_decision' => [
                'run_id' => $runSummary['run_id'],
                'terminal_status' => $runSummary['terminal_status'],
                'publishability_state' => $runSummary['publishability_state'],
                'final_reason_code' => $runSummary['final_reason_code'],
            ],
            'run_to_publication' => [
                'run_id' => $runSummary['run_id'],
                'publication_id' => $publicationContext['publication_id'] ?? null,
                'publication_version' => $publicationContext['publication_version'] ?? null,
                'run_publication_mirror_valid' => $publicationContext['run_publication_mirror_valid'] ?? false,
            ],
            'publication_to_pointer' => [
                'publication_id' => $publicationContext['publication_id'] ?? null,
                'pointer_publication_id' => $pointerContext['pointer_publication_id'] ?? null,
                'pointer_resolve_status' => $pointerContext['pointer_resolve_status'] ?? null,
                'pointer_post_switch_validation' => $pointerContext['pointer_post_switch_validation'] ?? false,
                'evidence_resolution_mode' => $publicationContext['evidence_resolution_mode'] ?? null,
                'evidence_publication_scope' => $publicationContext['evidence_publication_scope'] ?? null,
                'current_pointer_required' => $publicationContext['current_pointer_required'] ?? null,
                'historical_publication_allowed' => $publicationContext['historical_publication_allowed'] ?? null,
                'lineage_verification_status' => $publicationContext['lineage_verification_status'] ?? null,
            ],
            'correction_to_publication' => $correctionContext,
            'fallback_to_previous_readable_publication' => $fallbackContext,
            'replay_expected_actual_lifecycle' => null,
        ];
    }

    private function buildEvidenceCompleteness(array $runSummary, array $publicationContext, array $pointerContext, array $fallbackContext, array $artifactContext, array $lineage, array $correctionContext)
    {
        $missing = [];
        $this->markMissingSection($missing, 'run_context', $runSummary['run_id'] ?? null);
        $this->markMissingSection($missing, 'source_context', $runSummary['source_context']['source_mode'] ?? null);
        $this->markMissingSection($missing, 'coverage_context', $runSummary['coverage']['coverage_gate_state'] ?? null);
        $this->markMissingSection($missing, 'reason_code_context', $runSummary['final_reason_code'] ?? ($runSummary['coverage']['coverage_reason_code'] ?? null));
        $this->markMissingSection($missing, 'artifact_hash_context', $artifactContext['mandatory_artifact_presence'] ?? false);

        if ((string) ($runSummary['publishability_state'] ?? '') === 'READABLE') {
            $this->markMissingSection($missing, 'publication_context', $publicationContext['publication_id'] ?? null);
            $pointerRequired = (bool) ($publicationContext['current_pointer_required'] ?? true);
            $historicalAuditResolved = ($publicationContext['evidence_resolution_mode'] ?? null) === 'HISTORICAL_PUBLICATION_AUDIT'
                && ($publicationContext['lineage_verification_status'] ?? null) === 'LINEAGE_VERIFIED';
            $this->markMissingSection($missing, 'pointer_context', $pointerRequired ? (($pointerContext['pointer_resolve_status'] ?? null) === 'RESOLVED_READABLE_CURRENT') : $historicalAuditResolved);
        }

        if (! array_key_exists('fallback_used', $fallbackContext)) {
            $missing[] = 'fallback_context';
        }

        $this->markMissingSection($missing, 'lineage_context', $lineage['run_to_finalize_decision']['run_id'] ?? null);

        if (($correctionContext['correction_related'] ?? false) && empty($correctionContext['correction_id'])) {
            $missing[] = 'correction_context';
        }

        $missing = array_values(array_unique($missing));

        return [
            'evidence_completeness_state' => $missing === [] ? 'COMPLETE' : 'INCOMPLETE',
            'evidence_completeness_reason_code' => $missing === [] ? 'EVIDENCE_COMPLETE' : 'EVIDENCE_INCOMPLETE',
            'missing_sections' => $missing,
            'proof_scope' => 'run/source/coverage/artifact/seal/publication/pointer/fallback/correction/lineage',
            'database_lookup_required_after_export' => false,
            'deterministic_export' => true,
        ];
    }

    private function buildEvidenceAdmission($selectorType, $selectorId, array $requiredSections, array $missingSections, $evidenceCreatedAt = null)
    {
        $missingSections = array_values(array_unique(array_filter($missingSections, function ($section) {
            return $section !== null && $section !== '';
        })));

        return [
            'selector_type' => $selectorType,
            'selector_id' => $selectorId !== null ? (int) $selectorId : null,
            'evidence_admission_state' => $missingSections === [] ? 'ADMITTED_COMPLETE' : 'ADMITTED_INCOMPLETE',
            'evidence_admission_reason_code' => $missingSections === [] ? 'EVIDENCE_ADMISSION_COMPLETE' : 'EVIDENCE_ADMISSION_INCOMPLETE',
            'required_sections' => array_values($requiredSections),
            'missing_sections' => $missingSections,
            'critical_missing_sections' => $missingSections,
            'evidence_created_at' => $evidenceCreatedAt,
            'evidence_timestamp_source' => $evidenceCreatedAt !== null ? 'source_record_timestamp' : 'not_available',
            'database_lookup_required_after_export' => false,
            'deterministic_export' => true,
            'silent_missing_metadata_allowed' => false,
        ];
    }

    private function markMissingSection(array &$missing, $section, $value)
    {
        if ($value === null || $value === '' || $value === false) {
            $missing[] = $section;
        }
    }

    private function dominantReasonCodesFromRunEvents(array $eventSummary)
    {
        $counts = $eventSummary['reason_code_counts'] ?? [];
        if (! is_array($counts)) {
            return [];
        }

        arsort($counts);
        $result = [];
        foreach ($counts as $reasonCode => $count) {
            $result[] = ['reason_code' => $reasonCode, 'count' => (int) $count];
        }

        return $result;
    }

    private function buildHistoricalPublicationAuditProof($publicationId, $runId = null, $scope = 'historical_publication')
    {
        if ($publicationId === null || $publicationId === '') {
            return [
                'scope' => $scope,
                'proof_status' => 'MISSING',
                'publication_id' => null,
                'run_id' => $runId !== null ? (int) $runId : null,
                'evidence_resolution_mode' => 'NO_PUBLICATION_CONTEXT',
                'evidence_publication_scope' => 'NO_PUBLICATION',
                'current_pointer_required' => false,
                'historical_publication_allowed' => false,
                'lineage_verification_status' => 'NO_PUBLICATION_CONTEXT',
                'artifact_scope' => null,
                'evidence_reason_code' => 'EVIDENCE_PUBLICATION_NOT_AVAILABLE',
            ];
        }

        $selector = [
            'type' => 'publication_id',
            'publication_id' => (int) $publicationId,
        ];

        if ($runId !== null && $runId !== '') {
            $selector['run_id'] = (int) $runId;
        }

        try {
            $publication = $this->evidence->resolvePublicationForEvidenceAudit($selector);

            return [
                'scope' => $scope,
                'proof_status' => 'RESOLVED',
                'publication_id' => (int) $this->field($publication, 'publication_id'),
                'run_id' => $this->field($publication, 'run_id') !== null ? (int) $this->field($publication, 'run_id') : null,
                'publication_version' => $this->field($publication, 'publication_version') !== null ? (int) $this->field($publication, 'publication_version') : null,
                'is_current_publication' => (bool) $this->field($publication, 'is_current', false),
                'seal_state' => $this->field($publication, 'seal_state'),
                'evidence_resolution_mode' => $this->field($publication, 'evidence_resolution_mode'),
                'evidence_publication_scope' => $this->field($publication, 'evidence_publication_scope'),
                'current_pointer_required' => (bool) $this->field($publication, 'current_pointer_required', false),
                'current_pointer_status' => $this->field($publication, 'current_pointer_status'),
                'historical_publication_allowed' => (bool) $this->field($publication, 'historical_publication_allowed', false),
                'lineage_verification_status' => $this->field($publication, 'lineage_verification_status'),
                'artifact_scope' => $this->field($publication, 'artifact_scope'),
                'coverage_basis_publication_id' => $this->field($publication, 'coverage_basis_publication_id') !== null ? (int) $this->field($publication, 'coverage_basis_publication_id') : null,
                'coverage_basis_run_id' => $this->field($publication, 'coverage_basis_run_id') !== null ? (int) $this->field($publication, 'coverage_basis_run_id') : null,
                'evidence_reason_code' => $this->field($publication, 'evidence_reason_code'),
            ];
        } catch (\Throwable $e) {
            return [
                'scope' => $scope,
                'proof_status' => 'FAILED',
                'publication_id' => (int) $publicationId,
                'run_id' => $runId !== null ? (int) $runId : null,
                'evidence_resolution_mode' => 'HISTORICAL_PUBLICATION_AUDIT',
                'evidence_publication_scope' => 'HISTORICAL_SEALED_PUBLICATION',
                'current_pointer_required' => false,
                'historical_publication_allowed' => true,
                'lineage_verification_status' => 'LINEAGE_NOT_VERIFIED',
                'artifact_scope' => 'PUBLICATION_SCOPED',
                'evidence_reason_code' => $this->reasonCodeFromExceptionMessage($e->getMessage()),
                'failure_message' => $e->getMessage(),
            ];
        }
    }

    private function reasonCodeFromExceptionMessage($message)
    {
        $parts = explode(':', (string) $message, 2);
        $reasonCode = trim($parts[0]);

        return $reasonCode !== '' ? $reasonCode : 'EVIDENCE_HISTORICAL_PUBLICATION_RESOLUTION_FAILED';
    }

    private function buildCorrectionCandidateHistoricalPublicationProof($correction, $publicationId, $runId, $changedDecision, $resealStatus, $discardedCandidatePublicationId = null)
    {
        if ($this->isUnchangedCorrectionCandidateDiscarded($correction, $publicationId, $runId, $changedDecision, $resealStatus)) {
            return $this->buildUnchangedCorrectionCandidateDiscardedProof($correction, $discardedCandidatePublicationId, $runId, $resealStatus);
        }

        return $this->buildHistoricalPublicationAuditProof($publicationId, $runId, 'correction_candidate');
    }

    private function isUnchangedCorrectionCandidateDiscarded($correction, $publicationId, $runId, $changedDecision, $resealStatus)
    {
        $status = strtoupper((string) $this->field($correction, 'status'));

        if ($changedDecision !== 'UNCHANGED') {
            return false;
        }

        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') {
            return true;
        }

        return $resealStatus === 'NOT_RESEALED_UNCHANGED'
            && $publicationId !== null
            && $runId !== null
            && $this->field($correction, 'prior_run_id') !== null
            && (string) $runId !== (string) $this->field($correction, 'prior_run_id');
    }

    private function buildUnchangedCorrectionCandidateDiscardedProof($correction, $discardedCandidatePublicationId, $runId, $resealStatus)
    {
        $baselinePublicationId = $this->field($correction, 'baseline_publication_id') !== null
            ? $this->field($correction, 'baseline_publication_id')
            : $this->field($correction, 'prior_publication_id');
        $discardedCandidatePublicationId = $discardedCandidatePublicationId !== null
            ? (int) $discardedCandidatePublicationId
            : null;

        return [
            'scope' => 'correction_candidate',
            'proof_status' => $discardedCandidatePublicationId !== null ? 'DISCARDED_CANDIDATE_RECORDED' : 'MISSING',
            'publication_id' => $discardedCandidatePublicationId,
            'run_id' => $runId !== null ? (int) $runId : null,
            'evidence_resolution_mode' => 'UNCHANGED_CORRECTION_AUDIT',
            'evidence_publication_scope' => 'UNCHANGED_CORRECTION_DISCARDED_CANDIDATE',
            'current_pointer_required' => false,
            'historical_publication_allowed' => false,
            'lineage_verification_status' => $discardedCandidatePublicationId !== null ? 'UNCHANGED_CORRECTION_CANDIDATE_DISCARDED' : 'CORRECTION_DISCARDED_CANDIDATE_PUBLICATION_MISSING',
            'artifact_scope' => 'DISCARDED_CANDIDATE_ARTIFACT',
            'coverage_basis_publication_id' => null,
            'coverage_basis_run_id' => null,
            'evidence_reason_code' => $discardedCandidatePublicationId !== null ? 'UNCHANGED_CORRECTION_CANDIDATE_DISCARDED' : 'CORRECTION_DISCARDED_CANDIDATE_PUBLICATION_MISSING',
            'changed_decision' => 'UNCHANGED',
            'reseal_status' => $resealStatus,
            'preserved_publication_id' => $baselinePublicationId !== null ? (int) $baselinePublicationId : null,
            'discarded_candidate_publication_id' => $discardedCandidatePublicationId,
            'discarded_candidate_run_id' => $runId !== null ? (int) $runId : null,
            'final_outcome_note' => $this->field($correction, 'final_outcome_note'),
        ];
    }

    private function resolveDiscardedCandidatePublicationId($correction, $linkedCandidatePublicationId, $changedDecision, $resealStatus)
    {
        if (! $this->isUnchangedCorrectionCandidateDiscarded($correction, $linkedCandidatePublicationId, $this->field($correction, 'new_run_id'), $changedDecision, $resealStatus)) {
            return null;
        }

        $baselinePublicationId = $this->field($correction, 'baseline_publication_id') !== null
            ? $this->field($correction, 'baseline_publication_id')
            : $this->field($correction, 'prior_publication_id');
        $notesMap = $this->parseRunNotes((string) ($this->field($correction, 'new_run_notes') ?? ''));

        foreach (['discarded_candidate_publication_id', 'candidate_publication_id'] as $key) {
            if (! isset($notesMap[$key]) || $notesMap[$key] === '') {
                continue;
            }

            $candidateId = (int) $notesMap[$key];
            if ($candidateId > 0 && ($baselinePublicationId === null || $candidateId !== (int) $baselinePublicationId)) {
                return $candidateId;
            }
        }

        if ($linkedCandidatePublicationId !== null
            && ($baselinePublicationId === null || (int) $linkedCandidatePublicationId !== (int) $baselinePublicationId)) {
            return (int) $linkedCandidatePublicationId;
        }

        return null;
    }

    public function exportCorrectionEvidence($correctionId, $outputDir = null)
    {
        $correction = $this->evidence->findCorrectionById($correctionId);
        if (! $correction) {
            throw new \RuntimeException('Correction not found for evidence export.');
        }

        $priorPublicationId = $this->field($correction, 'baseline_publication_id') !== null ? $this->field($correction, 'baseline_publication_id') : $this->field($correction, 'prior_publication_id');
        $linkedNewPublicationId = $this->field($correction, 'replacement_publication_id') !== null ? $this->field($correction, 'replacement_publication_id') : $this->field($correction, 'new_publication_id');
        $priorPublication = $priorPublicationId ? $this->evidence->findPublicationById($priorPublicationId) : null;
        $linkedNewPublication = $linkedNewPublicationId ? $this->evidence->findPublicationById($linkedNewPublicationId) : null;
        $changedDecision = $this->resolveCorrectionChangedDecision($correction, $priorPublication, $linkedNewPublication);
        $resealStatus = $this->resolveCorrectionResealStatus($correction, $changedDecision, $linkedNewPublication);
        $discardedCandidatePublicationId = $this->resolveDiscardedCandidatePublicationId($correction, $linkedNewPublicationId, $changedDecision, $resealStatus);
        $replacementPublicationId = $changedDecision === 'UNCHANGED' ? null : $linkedNewPublicationId;
        $candidatePublicationId = $changedDecision === 'UNCHANGED' ? $discardedCandidatePublicationId : $replacementPublicationId;
        $newPublication = $replacementPublicationId !== null ? $linkedNewPublication : null;
        $baselineHistoricalProof = $this->buildHistoricalPublicationAuditProof($priorPublicationId, $this->field($correction, 'prior_run_id'), 'correction_baseline');
        $candidateHistoricalProof = $this->buildCorrectionCandidateHistoricalPublicationProof(
            $correction,
            $candidatePublicationId,
            $this->field($correction, 'new_run_id'),
            $changedDecision,
            $resealStatus,
            $discardedCandidatePublicationId
        );
        $publicationSwitch = $changedDecision !== 'UNCHANGED' && $newPublication ? (bool) $newPublication->is_current : false;
        $correctionAdmissionMissing = [];
        $this->markMissingSection($correctionAdmissionMissing, 'correction_context', $this->field($correction, 'correction_id'));
        $this->markMissingSection($correctionAdmissionMissing, 'correction_status', $this->field($correction, 'status'));
        $this->markMissingSection($correctionAdmissionMissing, 'correction_trade_date', $this->field($correction, 'trade_date'));
        $admission = $this->buildEvidenceAdmission('correction', (int) $this->field($correction, 'correction_id'), [
            'correction_context',
            'correction_lifecycle',
            'baseline_historical_publication_proof',
            'candidate_historical_publication_proof',
            'publication_switch',
            'comparison_summary',
        ], $correctionAdmissionMissing, $this->evidenceCreatedAtFromRecord($correction));

        $payload = [
            'evidence_admission' => $admission,
            'correction_id' => (int) $this->field($correction, 'correction_id'),
            'trade_date' => $this->field($correction, 'trade_date'),
            'approval' => [
                'approved_by' => $this->field($correction, 'approved_by'),
                'approved_at' => $this->field($correction, 'approved_at'),
            ],
            'correction_lifecycle' => [
                'correction_id' => (int) $this->field($correction, 'correction_id'),
                'status' => $this->field($correction, 'status'),
                'reason_code' => $this->field($correction, 'correction_reason_code'),
                'reason_note' => $this->field($correction, 'correction_reason_note'),
                'final_outcome_note' => $this->field($correction, 'final_outcome_note'),
                'baseline_run_id' => $this->field($correction, 'prior_run_id') !== null ? (int) $this->field($correction, 'prior_run_id') : null,
                'baseline_publication_id' => $priorPublicationId !== null ? (int) $priorPublicationId : null,
                'baseline_publication_version' => $this->field($correction, 'prior_publication_version') !== null ? (int) $this->field($correction, 'prior_publication_version') : null,
                'candidate_run_id' => $this->field($correction, 'new_run_id') !== null ? (int) $this->field($correction, 'new_run_id') : null,
                'candidate_publication_id' => $candidatePublicationId !== null ? (int) $candidatePublicationId : null,
                'candidate_publication_version' => $replacementPublicationId !== null && $this->field($correction, 'new_publication_version') !== null ? (int) $this->field($correction, 'new_publication_version') : null,
                'preserved_publication_id' => $changedDecision === 'UNCHANGED' && $priorPublicationId !== null ? (int) $priorPublicationId : null,
                'discarded_candidate_publication_id' => $discardedCandidatePublicationId !== null ? (int) $discardedCandidatePublicationId : null,
                'replacement_publication_id' => $replacementPublicationId !== null ? (int) $replacementPublicationId : null,
                'changed_decision' => $changedDecision,
                'reseal_status' => $resealStatus,
                'publication_switch' => $publicationSwitch,
                'historical_lineage_proof' => [
                    'baseline_publication_proof' => $baselineHistoricalProof,
                    'candidate_publication_proof' => $candidateHistoricalProof,
                ],
                'pointer_current_state' => [
                    'baseline_was_current' => $this->field($correction, 'prior_publication_is_current') !== null ? (bool) $this->field($correction, 'prior_publication_is_current') : null,
                    'candidate_is_current' => $changedDecision === 'UNCHANGED' ? false : ($this->field($correction, 'new_publication_is_current') !== null ? (bool) $this->field($correction, 'new_publication_is_current') : null),
                ],
                'run_state' => [
                    'baseline_terminal_status' => $this->field($correction, 'prior_run_terminal_status'),
                    'baseline_publishability_state' => $this->field($correction, 'prior_run_publishability_state'),
                    'baseline_coverage_gate_state' => CoverageGateStateNormalizer::normalize($this->field($correction, 'prior_run_coverage_gate_state')),
                    'legacy_baseline_coverage_gate_state_raw' => CoverageGateStateNormalizer::legacyRaw($this->field($correction, 'prior_run_coverage_gate_state')),
                    'candidate_terminal_status' => $this->field($correction, 'new_run_terminal_status'),
                    'candidate_publishability_state' => $this->field($correction, 'new_run_publishability_state'),
                    'candidate_coverage_gate_state' => CoverageGateStateNormalizer::normalize($this->field($correction, 'new_run_coverage_gate_state')),
                    'legacy_candidate_coverage_gate_state_raw' => CoverageGateStateNormalizer::legacyRaw($this->field($correction, 'new_run_coverage_gate_state')),
                ],
                'publication_state' => [
                    'baseline_seal_state' => $this->field($correction, 'prior_publication_seal_state'),
                    'candidate_seal_state' => $changedDecision === 'UNCHANGED' ? null : $this->field($correction, 'new_publication_seal_state'),
                ],
            ],
            'prior_publication' => $priorPublication ? [
                'publication_id' => (int) $priorPublication->publication_id,
                'run_id' => (int) $priorPublication->run_id,
                'publication_version' => (int) $priorPublication->publication_version,
                'is_current' => (bool) $priorPublication->is_current,
            ] : null,
            'new_publication' => $newPublication ? [
                'publication_id' => (int) $newPublication->publication_id,
                'run_id' => (int) $newPublication->run_id,
                'publication_version' => (int) $newPublication->publication_version,
                'is_current' => (bool) $newPublication->is_current,
            ] : null,
            'baseline_historical_publication_proof' => $baselineHistoricalProof,
            'candidate_historical_publication_proof' => $candidateHistoricalProof,
            'old_hashes' => $priorPublication ? [
                'bars_batch_hash' => $priorPublication->bars_batch_hash,
                'indicators_batch_hash' => $priorPublication->indicators_batch_hash,
                'eligibility_batch_hash' => $priorPublication->eligibility_batch_hash,
            ] : null,
            'new_hashes' => $newPublication ? [
                'bars_batch_hash' => $newPublication->bars_batch_hash,
                'indicators_batch_hash' => $newPublication->indicators_batch_hash,
                'eligibility_batch_hash' => $newPublication->eligibility_batch_hash,
            ] : null,
            'publication_switch' => $publicationSwitch,
            'status' => $this->field($correction, 'status'),
            'final_outcome_note' => $this->field($correction, 'final_outcome_note'),
            'changed_decision' => $changedDecision,
            'reseal_status' => $resealStatus,
            'comparison_summary' => $this->buildCorrectionComparisonSummary($priorPublication, $newPublication, $changedDecision),
        ];

        $dir = $outputDir ?: $this->defaultCorrectionOutputDir($this->field($correction, 'correction_id'));
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/correction_evidence.json', $payload);
        $this->writeJson($dir.'/evidence_admission.json', $admission);

        $files = ['correction_evidence.json', 'evidence_admission.json'];

        return [
            'selector' => ['type' => 'correction', 'id' => (int) $this->field($correction, 'correction_id')],
            'summary' => [
                'correction_id' => (int) $this->field($correction, 'correction_id'),
                'trade_date' => $this->field($correction, 'trade_date'),
                'status' => $this->field($correction, 'status'),
                'evidence_admission_state' => $admission['evidence_admission_state'],
                'changed_decision' => $changedDecision,
                'reseal_status' => $resealStatus,
                'publication_switch' => $payload['publication_switch'],
            ],
            'output_dir' => $dir,
            'file_count' => count($files),
            'files' => $files,
        ];
    }

    public function exportReplayEvidence($replayId, $tradeDate = null, $outputDir = null)
    {
        if ($tradeDate === null || $tradeDate === '') {
            throw new \RuntimeException('Replay evidence export requires explicit trade_date; latest-row resolution is not allowed on consumer read path.');
        }

        $metric = $this->evidence->findReplayMetric($replayId, $tradeDate);
        if (! $metric) {
            throw new \RuntimeException('Replay result not found for evidence export.');
        }

        $reasonCodes = $this->evidence->replayReasonCodeCounts($metric->replay_id, $metric->trade_date);
        $expectedReasonCodeCounts = $this->decodeExpectedReasonCodeCounts($metric->expected_reason_code_counts_json ?? null);
        $replayResult = $this->buildReplayResult($metric);
        $expectedState = $this->buildReplayExpectedState($metric, $expectedReasonCodeCounts);
        $actualState = $this->buildReplayActualState($metric, $reasonCodes);
        $replayStatus = $this->field($metric, 'replay_status') ?: $this->replayStatusForComparison($metric->comparison_result ?? null);
        $summary = [
            'replay_id' => (int) $metric->replay_id,
            'trade_date' => $metric->trade_date,
            'comparison_result' => $metric->comparison_result,
            'replay_status' => $replayStatus,
            'status' => $metric->status,
            'config_identity' => $metric->config_identity,
            'reason_code_count' => count($reasonCodes),
        ];
        $replayAdmissionMissing = [];
        $this->markMissingSection($replayAdmissionMissing, 'replay_result', $metric->replay_id ?? null);
        $this->markMissingSection($replayAdmissionMissing, 'expected_state', $expectedState['status'] ?? ($expectedState['publication_context']['publication_state'] ?? null));
        $this->markMissingSection($replayAdmissionMissing, 'actual_state', $actualState['status'] ?? ($actualState['publication_context']['publication_state'] ?? null));
        $admission = $this->buildEvidenceAdmission('replay', (int) $metric->replay_id, [
            'replay_result',
            'expected_state',
            'actual_state',
            'reason_code_counts',
            'publication_context',
            'pointer_context',
            'coverage_comparison',
            'hash_seal_comparison',
        ], $replayAdmissionMissing, $this->evidenceCreatedAtFromRecord($metric));
        $summary['evidence_admission_state'] = $admission['evidence_admission_state'];
        $payload = [
            'evidence_admission' => $admission,
            'replay_result' => $replayResult,
            'expected_state' => $expectedState,
            'actual_state' => $actualState,
            'reason_code_counts' => $reasonCodes,
            'summary' => $summary,
        ];

        $dir = $outputDir ?: $this->defaultReplayOutputDir($metric->replay_id, $metric->trade_date);
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/replay_result.json', $replayResult);
        $this->writeJson($dir.'/replay_expected_state.json', $expectedState);
        $this->writeJson($dir.'/replay_actual_state.json', $actualState);
        $this->writeJson($dir.'/replay_reason_code_counts.json', $reasonCodes);
        $this->writeJson($dir.'/evidence_admission.json', $admission);
        $this->writeJson($dir.'/replay_evidence_pack.json', $payload);

        $files = [
            'replay_result.json',
            'replay_expected_state.json',
            'replay_actual_state.json',
            'replay_reason_code_counts.json',
            'evidence_admission.json',
            'replay_evidence_pack.json',
        ];

        return [
            'selector' => ['type' => 'replay', 'id' => (int) $metric->replay_id],
            'summary' => [
                'replay_id' => (int) $metric->replay_id,
                'trade_date' => $metric->trade_date,
                'comparison_result' => $metric->comparison_result,
                'replay_status' => $replayStatus,
                'status' => $metric->status,
                'evidence_admission_state' => $admission['evidence_admission_state'],
            ],
            'output_dir' => $dir,
            'file_count' => count($files),
            'files' => $files,
        ];
    }

    private function resolvePublicationForRun($run)
    {
        if (! $this->isReadableRun($run)) {
            return null;
        }

        return $this->evidence->resolvePublicationForEvidenceAudit([
            'type' => 'run_id',
            'run_id' => $run->run_id,
            'trade_date' => $run->trade_date_requested,
        ]);
    }

    private function buildRunSummary($run, $manifest = null)
    {
        $sourceContext = $this->buildSourceContext($run);
        $sourceContext = $this->normalizeSourceContextPaths($sourceContext);
        $notesMap = $this->parseRunNotes((string) ($this->field($run, 'notes') ?? ''));
        $coverage = $this->buildCoverageState($run);
        $coverageReasonCode = $coverage['coverage_reason_code'] ?? null;
        $finalReasonCode = $this->field($run, 'final_reason_code')
            ?: ($sourceContext['final_reason_code'] ?? null)
            ?: $coverageReasonCode;
        $requestMode = $this->field($run, 'request_mode') ?: ($notesMap['request_mode'] ?? null);
        $isCurrentPublication = $manifest ? (bool) $manifest['is_current'] : (bool) $this->field($run, 'is_current_publication', false);
        $promoted = (string) $this->field($run, 'terminal_status') === 'SUCCESS'
            && (string) $this->field($run, 'publishability_state') === 'READABLE'
            && $isCurrentPublication;
        $importStatus = $this->deriveImportStatus($run, $requestMode);
        $promoteStatus = $this->derivePromoteStatus($run, $requestMode, $promoted);
        $pointerSwitched = $isCurrentPublication;
        $publicationId = $this->field($run, 'publication_id') !== null ? (int) $this->field($run, 'publication_id') : ($manifest ? (int) $manifest['publication_id'] : null);
        $publicationVersion = $manifest ? (int) $manifest['publication_version'] : ($this->field($run, 'publication_version') !== null ? (int) $this->field($run, 'publication_version') : null);
        $sourceMode = $this->field($run, 'source');

        return [
            'run_id' => (int) $this->field($run, 'run_id'),
            'run_uuid' => $this->field($run, 'run_uuid'),
            'requested_date' => $this->field($run, 'requested_date') ?: $this->field($run, 'trade_date_requested'),
            'trade_date_requested' => $this->field($run, 'trade_date_requested'),
            'trade_date_effective' => $this->field($run, 'trade_date_effective'),
            'platform_timezone' => (string) $this->configValue('market_data.platform.timezone', 'Asia/Jakarta'),
            'request_mode' => $requestMode,
            'promote_mode' => $this->field($run, 'promote_mode') ?: ($notesMap['promote_mode'] ?? null),
            'publish_target' => $this->field($run, 'publish_target') ?: ($notesMap['publish_target'] ?? null),
            'lifecycle_state' => $this->field($run, 'lifecycle_state'),
            'terminal_status' => $this->field($run, 'terminal_status'),
            'quality_gate_state' => $this->field($run, 'quality_gate_state'),
            'publishability_state' => $this->field($run, 'publishability_state'),
            'stage' => $this->field($run, 'stage'),
            'stage_reached' => $this->field($run, 'stage'),
            'source' => $sourceMode,
            'source_mode' => $sourceMode,
            'import_status' => $importStatus,
            'promote_status' => $promoteStatus,
            'promoted' => $promoted,
            'pointer_switched' => $pointerSwitched,
            'current_publication_id' => $pointerSwitched ? $publicationId : null,
            'import_promote_boundary' => [
                'request_mode' => $requestMode,
                'source_mode' => $sourceMode,
                'import_status' => $importStatus,
                'promote_status' => $promoteStatus,
                'promoted' => $promoted,
                'pointer_switched' => $pointerSwitched,
                'boundary_rule' => $requestMode === 'import_only'
                    ? 'import_only must not create READABLE publication or switch current pointer'
                    : 'promote must pass coverage/hash/seal/finalize before pointer switch',
            ],
            'final_reason_code' => $finalReasonCode,
            'final_reason_message' => $this->resolveReasonMessage($finalReasonCode),
            'final_outcome_note' => $this->field($run, 'final_outcome_note') ?: ($notesMap['final_outcome_note'] ?? $this->deriveFinalOutcomeNote($run, $finalReasonCode)),
            'source_context' => $sourceContext,
            'coverage' => $coverage,
            'coverage_summary' => $coverage,
            'coverage_reason_code' => $coverageReasonCode,
            'coverage_ratio' => $this->field($run, 'coverage_ratio') !== null ? (float) $this->field($run, 'coverage_ratio') : null,
            'bars_rows_written' => $this->field($run, 'bars_rows_written') !== null ? (int) $this->field($run, 'bars_rows_written') : null,
            'accepted_row_count' => $this->field($run, 'bars_rows_written') !== null ? (int) $this->field($run, 'bars_rows_written') : null,
            'rejected_row_count' => $this->field($run, 'invalid_bar_count') !== null ? (int) $this->field($run, 'invalid_bar_count') : null,
            'invalid_row_count' => $this->field($run, 'invalid_bar_count') !== null ? (int) $this->field($run, 'invalid_bar_count') : null,
            'indicators_rows_written' => $this->field($run, 'indicators_rows_written') !== null ? (int) $this->field($run, 'indicators_rows_written') : null,
            'eligibility_rows_written' => $this->field($run, 'eligibility_rows_written') !== null ? (int) $this->field($run, 'eligibility_rows_written') : null,
            'invalid_bar_count' => $this->field($run, 'invalid_bar_count') !== null ? (int) $this->field($run, 'invalid_bar_count') : null,
            'invalid_indicator_count' => $this->field($run, 'invalid_indicator_count') !== null ? (int) $this->field($run, 'invalid_indicator_count') : null,
            'warning_count' => $this->field($run, 'warning_count') !== null ? (int) $this->field($run, 'warning_count') : null,
            'hard_reject_count' => $this->field($run, 'hard_reject_count') !== null ? (int) $this->field($run, 'hard_reject_count') : null,
            'bars_batch_hash' => $this->field($run, 'bars_batch_hash'),
            'indicators_batch_hash' => $this->field($run, 'indicators_batch_hash'),
            'eligibility_batch_hash' => $this->field($run, 'eligibility_batch_hash'),
            'sealed_at' => $this->field($run, 'sealed_at'),
            'config_version' => $this->field($run, 'config_version'),
            'config_hash' => $this->field($run, 'config_hash'),
            'config_snapshot_ref' => $this->field($run, 'config_snapshot_ref'),
            'publication_id' => $publicationId,
            'publication_version' => $publicationVersion,
            'is_current_publication' => $isCurrentPublication,
            'supersedes_run_id' => $this->field($run, 'supersedes_run_id') !== null ? (int) $this->field($run, 'supersedes_run_id') : null,
            'correction_id' => $this->field($run, 'correction_id') !== null ? (int) $this->field($run, 'correction_id') : null,
            'started_at' => $this->field($run, 'started_at'),
            'completed_at' => $this->field($run, 'completed_at') ?: $this->field($run, 'finished_at'),
            'finished_at' => $this->field($run, 'finished_at'),
            'duration_ms' => $this->durationMillis($this->field($run, 'started_at'), $this->field($run, 'finished_at') ?: $this->field($run, 'completed_at')),
            'created_at' => $this->field($run, 'created_at'),
            'updated_at' => $this->field($run, 'updated_at'),
            'evidence_export_created_at' => $this->evidenceCreatedAtFromRecord($run),
            'evidence_export_timestamp_source' => $this->evidenceTimestampSourceFromRecord($run),
        ] + $this->mutationImpactPayloadFromNotes($notesMap);
    }

    private function mutationImpactPayloadFromNotes(array $notesMap)
    {
        $fields = [
            'bar_mutation_changed_count',
            'bar_mutation_inserted_count',
            'bar_mutation_updated_count',
            'bar_mutation_unchanged_count',
            'bar_mutation_removed_count',
            'affected_ticker_count',
            'affected_trade_date_count',
            'affected_trade_dates',
            'affected_start_date',
            'affected_end_date',
            'max_indicator_dependency_trading_days',
            'indicator_reprocess_state',
            'publication_impact_state',
            'readable_publication_impacted',
            'republication_required',
            'publication_impact_reason_code',
            'indicator_reprocess_execution_state',
            'indicator_reprocessed_trade_date_count',
            'indicator_reprocessed_trade_dates',
            'indicator_reprocess_scope',
            'indicator_reprocess_blocked_reason_code',
            'indicator_reprocess_failure_reason_code',
            'eligibility_reprocess_execution_state',
            'eligibility_reprocessed_trade_date_count',
            'eligibility_reprocessed_trade_dates',
            'eligibility_reprocess_blocked_reason_code',
            'eligibility_reprocess_failure_reason_code',
            'publication_reprocess_state',
            'publication_reprocess_republished_trade_date_count',
            'publication_reprocess_republished_trade_dates',
            'publication_reprocess_candidate_trade_dates',
            'publication_reprocess_blocked_trade_dates',
            'publication_reprocess_failed_trade_dates',
            'publication_reprocess_blocked_reason_code',
            'publication_reprocess_failure_reason_code',
            'publication_reprocess_republication_mode',
            'publication_reprocess_correction_ids',
            'publication_reprocess_correction_id',
            'recovered_row_apply_state',
            'recovered_row_count',
            'resume_recovered_apply_state',
            'resume_recovered_row_count',
        ];

        $payload = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $notesMap) && $notesMap[$field] !== '') {
                $payload[$field] = $notesMap[$field];
            }
        }

        if ($payload === []) {
            return [];
        }

        $payload['bar_mutation_summary'] = [
            'changed_bar_count' => (int) ($payload['bar_mutation_changed_count'] ?? 0),
            'inserted_bar_count' => (int) ($payload['bar_mutation_inserted_count'] ?? 0),
            'updated_bar_count' => (int) ($payload['bar_mutation_updated_count'] ?? 0),
            'unchanged_bar_count' => (int) ($payload['bar_mutation_unchanged_count'] ?? 0),
            'removed_bar_count' => (int) ($payload['bar_mutation_removed_count'] ?? 0),
        ];
        $payload['indicator_impact_summary'] = [
            'affected_ticker_count' => (int) ($payload['affected_ticker_count'] ?? 0),
            'affected_trade_date_count' => (int) ($payload['affected_trade_date_count'] ?? 0),
            'affected_trade_dates' => $this->parseCsvList($payload['affected_trade_dates'] ?? ''),
            'affected_start_date' => $payload['affected_start_date'] ?? null,
            'affected_end_date' => $payload['affected_end_date'] ?? null,
            'max_dependency_trading_days' => (int) ($payload['max_indicator_dependency_trading_days'] ?? 0),
            'indicator_reprocess_state' => $payload['indicator_reprocess_state'] ?? null,
        ];
        $payload['publication_impact_summary'] = [
            'readable_publication_impacted' => ($payload['readable_publication_impacted'] ?? 'false') === 'true',
            'republication_required' => ($payload['republication_required'] ?? 'false') === 'true',
            'publication_impact_state' => $payload['publication_impact_state'] ?? 'NOOP',
            'reason_code' => $payload['publication_impact_reason_code'] ?? null,
        ];
        $payload['indicator_reprocess_execution_summary'] = [
            'execution_state' => $payload['indicator_reprocess_execution_state'] ?? 'NOOP',
            'reprocessed_trade_date_count' => (int) ($payload['indicator_reprocessed_trade_date_count'] ?? 0),
            'reprocessed_trade_dates' => $this->parseCsvList($payload['indicator_reprocessed_trade_dates'] ?? ''),
            'reprocess_scope' => $payload['indicator_reprocess_scope'] ?? 'NONE',
            'blocked_reason_code' => $payload['indicator_reprocess_blocked_reason_code'] ?? null,
            'failure_reason_code' => $payload['indicator_reprocess_failure_reason_code'] ?? null,
        ];
        $payload['eligibility_reprocess_execution_summary'] = [
            'execution_state' => $payload['eligibility_reprocess_execution_state'] ?? 'NOOP',
            'reprocessed_trade_date_count' => (int) ($payload['eligibility_reprocessed_trade_date_count'] ?? 0),
            'reprocessed_trade_dates' => $this->parseCsvList($payload['eligibility_reprocessed_trade_dates'] ?? ''),
            'blocked_reason_code' => $payload['eligibility_reprocess_blocked_reason_code'] ?? null,
            'failure_reason_code' => $payload['eligibility_reprocess_failure_reason_code'] ?? null,
        ];
        $publicationReprocessCorrectionIds = $this->intList($this->parseCsvList($payload['publication_reprocess_correction_ids'] ?? ''));
        $publicationReprocessCorrectionId = isset($payload['publication_reprocess_correction_id'])
            ? (int) $payload['publication_reprocess_correction_id']
            : (count($publicationReprocessCorrectionIds) === 1 ? $publicationReprocessCorrectionIds[0] : null);

        $payload['publication_reprocess_summary'] = [
            'execution_state' => $payload['publication_reprocess_state'] ?? 'NOOP',
            'republished_trade_date_count' => (int) ($payload['publication_reprocess_republished_trade_date_count'] ?? 0),
            'republished_trade_dates' => $this->parseCsvList($payload['publication_reprocess_republished_trade_dates'] ?? ''),
            'candidate_trade_dates' => $this->parseCsvList($payload['publication_reprocess_candidate_trade_dates'] ?? ''),
            'blocked_trade_dates' => $this->parseCsvList($payload['publication_reprocess_blocked_trade_dates'] ?? ''),
            'failed_trade_dates' => $this->parseCsvList($payload['publication_reprocess_failed_trade_dates'] ?? ''),
            'blocked_reason_code' => $payload['publication_reprocess_blocked_reason_code'] ?? null,
            'failure_reason_code' => $payload['publication_reprocess_failure_reason_code'] ?? null,
            'republication_mode' => $payload['publication_reprocess_republication_mode'] ?? 'NOT_REQUIRED',
            'correction_ids' => $publicationReprocessCorrectionIds,
            'correction_id' => $publicationReprocessCorrectionId,
        ];
        $payload['resume_recovered_apply_summary'] = [
            'recovered_row_count' => (int) ($payload['resume_recovered_row_count'] ?? $payload['recovered_row_count'] ?? 0),
            'apply_state' => $payload['resume_recovered_apply_state'] ?? $payload['recovered_row_apply_state'] ?? 'NOOP',
        ];

        return $payload;
    }


    private function evidenceCreatedAtFromRecord($record)
    {
        foreach (['finished_at', 'completed_at', 'updated_at', 'created_at'] as $field) {
            $value = $this->field($record, $field);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function evidenceTimestampSourceFromRecord($record)
    {
        foreach (['finished_at', 'completed_at', 'updated_at', 'created_at'] as $field) {
            $value = $this->field($record, $field);
            if ($value !== null && $value !== '') {
                return $field;
            }
        }

        return 'not_available';
    }

    private function deriveImportStatus($run, $requestMode)
    {
        if ((string) $requestMode === 'import_only') {
            return $this->field($run, 'bars_rows_written') !== null ? 'COMPLETED' : 'PENDING';
        }

        return $this->field($run, 'bars_rows_written') !== null ? 'COMPLETED' : 'NOT_APPLICABLE';
    }

    private function derivePromoteStatus($run, $requestMode, $promoted)
    {
        if ($promoted) {
            return 'PROMOTED';
        }

        if ((string) $requestMode === 'import_only') {
            return 'NOT_PROMOTED';
        }

        $terminalStatus = (string) $this->field($run, 'terminal_status', '');
        if (in_array($terminalStatus, ['HELD', 'FAILED', 'BLOCKED'], true)) {
            return $terminalStatus;
        }

        return 'NOT_PROMOTED';
    }

    private function isReadableRun($run)
    {
        return (string) $this->field($run, 'terminal_status') === 'SUCCESS'
            && (string) $this->field($run, 'publishability_state') === 'READABLE'
            && CoverageGateStateNormalizer::normalize($this->field($run, 'coverage_gate_state')) === 'PASS';
    }

    private function durationMillis($startedAt, $finishedAt)
    {
        if ($startedAt === null || $startedAt === '' || $finishedAt === null || $finishedAt === '') {
            return null;
        }

        try {
            $start = new \DateTime((string) $startedAt);
            $finish = new \DateTime((string) $finishedAt);
            return max(0, (int) round(($finish->getTimestamp() - $start->getTimestamp()) * 1000));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function deriveFinalOutcomeNote($run, $finalReasonCode = null)
    {
        if ($this->isReadableRun($run)) {
            return 'Run is SUCCESS + READABLE; readable proof still requires coverage PASS, SEALED publication, and pointer-resolved current publication.';
        }

        $reason = $finalReasonCode ?: $this->field($run, 'source_final_reason_code') ?: $this->resolveCoverageReasonCodeFromState($this->field($run, 'coverage_gate_state'));

        return 'Run is not readable; evidence export records failure context and does not claim consumer-readable publication. reason_code='.(string) ($reason ?: 'UNKNOWN');
    }

    private function resolveReasonMessage($reasonCode)
    {
        $messages = [
            'COVERAGE_THRESHOLD_MET' => 'Coverage gate met the configured minimum threshold.',
            'COVERAGE_BELOW_THRESHOLD' => 'Coverage gate failed because available data is below the configured threshold.',
            'RUN_COVERAGE_NOT_EVALUABLE' => 'Coverage gate could not be evaluated for this run.',
            'RUN_SOURCE_TIMEOUT' => 'Source acquisition timed out.',
            'RUN_SOURCE_RATE_LIMIT' => 'Source acquisition was rate limited.',
            'RUN_SOURCE_MANUAL_FILE_NOT_FOUND' => 'Manual source file was not found.',
            'RUN_SOURCE_MANUAL_FILE_NOT_READABLE' => 'Manual source file was not readable.',
            'RUN_SOURCE_MANUAL_FILE_MALFORMED' => 'Manual source file was malformed.',
            'RUN_SOURCE_PARTIAL_RESPONSE' => 'Source returned a partial response.',
            'RUN_LOCK_CONFLICT' => 'Run finalization hit a lock conflict and was held safely.',
        ];

        return $reasonCode && isset($messages[$reasonCode]) ? $messages[$reasonCode] : null;
    }

    private function buildSourceAttemptTelemetry($run, array $sourceContext)
    {
        $telemetry = $this->evidence->exportRunSourceAttemptTelemetry($run->run_id);
        if ($telemetry === []) {
            return null;
        }

        if (($telemetry['source_mode'] ?? null) === null && ($sourceContext['source_mode'] ?? null) !== null) {
            $telemetry['source_mode'] = $sourceContext['source_mode'];
        }

        if (($telemetry['source_name'] ?? null) === null && ($sourceContext['source_name'] ?? null) !== null) {
            $telemetry['source_name'] = $sourceContext['source_name'];
        }

        if (($telemetry['source_input_file'] ?? null) === null && ($sourceContext['source_input_file'] ?? null) !== null) {
            $telemetry['source_input_file'] = $sourceContext['source_input_file'];
        }

        if (($telemetry['provider'] ?? null) === null && ($sourceContext['provider'] ?? null) !== null) {
            $telemetry['provider'] = $sourceContext['provider'];
        }

        if (($telemetry['timeout_seconds'] ?? null) === null && array_key_exists('timeout_seconds', $sourceContext)) {
            $telemetry['timeout_seconds'] = $sourceContext['timeout_seconds'];
        }

        if (($telemetry['retry_max'] ?? null) === null && array_key_exists('retry_max', $sourceContext)) {
            $telemetry['retry_max'] = $sourceContext['retry_max'];
        }

        if (($telemetry['attempt_count'] ?? null) === null && array_key_exists('attempt_count', $sourceContext)) {
            $telemetry['attempt_count'] = $sourceContext['attempt_count'];
        }

        if (($telemetry['success_after_retry'] ?? null) === null && ($sourceContext['success_after_retry'] ?? null) !== null) {
            $telemetry['success_after_retry'] = $sourceContext['success_after_retry'];
        }

        if (($telemetry['final_http_status'] ?? null) === null && array_key_exists('final_http_status', $sourceContext)) {
            $telemetry['final_http_status'] = $sourceContext['final_http_status'];
        }

        if (($telemetry['final_reason_code'] ?? null) === null && ($sourceContext['final_reason_code'] ?? null) !== null) {
            $telemetry['final_reason_code'] = $sourceContext['final_reason_code'];
        }

        if (($telemetry['retry_exhausted'] ?? null) === null && ($sourceContext['retry_exhausted'] ?? null) !== null) {
            $telemetry['retry_exhausted'] = $sourceContext['retry_exhausted'];
        }

        if (($telemetry['source_final_status'] ?? null) === null && ($sourceContext['source_final_status'] ?? null) !== null) {
            $telemetry['source_final_status'] = $sourceContext['source_final_status'];
        }

        return $this->normalizeSourceAttemptTelemetryPaths($telemetry);
    }

    private function buildSourceContext($record)
    {
        $notesMap = $this->parseRunNotes((string) ($record->notes ?? ''));
        $sourceMode = $record->source ?? ($notesMap['source_mode'] ?? null);
        $sourceName = $record->source_name ?? ($notesMap['source_name'] ?? null);
        $provider = $record->source_provider ?? ($notesMap['source_provider'] ?? null);
        $sourceInputFile = $record->source_input_file ?? ($notesMap['source_input_file'] ?? null);
        $sourceFileHash = $record->source_file_hash ?? null;
        $sourceIdentity = implode('|', array_filter([
            $sourceMode ? 'mode='.$sourceMode : null,
            $sourceName ? 'name='.$sourceName : null,
            $provider ? 'provider='.$provider : null,
            $sourceInputFile ? 'input='.basename(str_replace('\\', '/', (string) $sourceInputFile)) : null,
            $sourceFileHash ? 'hash='.$sourceFileHash : null,
        ]));
        $attemptCount = $this->normalizeNullableInt($record->source_attempt_count ?? (isset($notesMap['source_attempt_count']) && $notesMap['source_attempt_count'] !== '' ? $notesMap['source_attempt_count'] : null));
        $successAfterRetry = property_exists($record, 'source_success_after_retry') && $record->source_success_after_retry !== null ? ($record->source_success_after_retry ? 'yes' : 'no') : ($notesMap['source_success_after_retry'] ?? null);
        $retryExhausted = property_exists($record, 'source_retry_exhausted') && $record->source_retry_exhausted !== null ? ($record->source_retry_exhausted ? 'yes' : 'no') : ($notesMap['source_retry_exhausted'] ?? null);
        $finalHttpStatus = $this->normalizeNullableInt($record->source_final_http_status ?? (isset($notesMap['source_final_http_status']) && $notesMap['source_final_http_status'] !== '' ? $notesMap['source_final_http_status'] : null));
        $finalReasonCode = $record->source_final_reason_code ?? ($notesMap['source_final_reason_code'] ?? null);

        return [
            'source_mode' => $sourceMode,
            'source_name' => $sourceName,
            'source_identity' => $sourceIdentity !== '' ? $sourceIdentity : null,
            'source_provider' => $provider,
            'provider' => $provider,
            'source_input_file' => $sourceInputFile,
            'timeout_seconds' => $this->normalizeNullableInt($record->source_timeout_seconds ?? (isset($notesMap['source_timeout_seconds']) && $notesMap['source_timeout_seconds'] !== '' ? $notesMap['source_timeout_seconds'] : null)),
            'source_timeout_seconds' => $this->normalizeNullableInt($record->source_timeout_seconds ?? (isset($notesMap['source_timeout_seconds']) && $notesMap['source_timeout_seconds'] !== '' ? $notesMap['source_timeout_seconds'] : null)),
            'retry_max' => $this->normalizeNullableInt($record->source_retry_max ?? (isset($notesMap['source_retry_max']) && $notesMap['source_retry_max'] !== '' ? $notesMap['source_retry_max'] : null)),
            'source_retry_max' => $this->normalizeNullableInt($record->source_retry_max ?? (isset($notesMap['source_retry_max']) && $notesMap['source_retry_max'] !== '' ? $notesMap['source_retry_max'] : null)),
            'attempt_count' => $attemptCount,
            'source_attempt_count' => $attemptCount,
            'success_after_retry' => $successAfterRetry,
            'source_success_after_retry' => $successAfterRetry,
            'final_http_status' => $finalHttpStatus,
            'source_final_http_status' => $finalHttpStatus,
            'final_reason_code' => $finalReasonCode,
            'source_final_reason_code' => $finalReasonCode,
            'source_final_reason_message' => $this->resolveReasonMessage($finalReasonCode),
            'source_file_hash' => $sourceFileHash,
            'source_file_hash_algorithm' => $record->source_file_hash_algorithm ?? null,
            'source_file_size_bytes' => isset($record->source_file_size_bytes) && $record->source_file_size_bytes !== null ? (int) $record->source_file_size_bytes : null,
            'source_file_row_count' => isset($record->source_file_row_count) && $record->source_file_row_count !== null ? (int) $record->source_file_row_count : null,
            'accepted_row_count' => isset($record->bars_rows_written) && $record->bars_rows_written !== null ? (int) $record->bars_rows_written : (isset($notesMap['accepted_row_count']) && $notesMap['accepted_row_count'] !== '' ? (int) $notesMap['accepted_row_count'] : null),
            'rejected_row_count' => isset($record->invalid_bar_count) && $record->invalid_bar_count !== null ? (int) $record->invalid_bar_count : (isset($notesMap['rejected_row_count']) && $notesMap['rejected_row_count'] !== '' ? (int) $notesMap['rejected_row_count'] : null),
            'invalid_row_count' => isset($record->invalid_bar_count) && $record->invalid_bar_count !== null ? (int) $record->invalid_bar_count : (isset($notesMap['invalid_row_count']) && $notesMap['invalid_row_count'] !== '' ? (int) $notesMap['invalid_row_count'] : null),
            'retry_exhausted' => $retryExhausted,
            'source_retry_exhausted' => $retryExhausted,
            'source_final_status' => $notesMap['source_final_status'] ?? $this->deriveSourceFinalStatus($record),
            'source_acquisition_state' => $notesMap['source_acquisition_state'] ?? null,
            'source_acquisition_mode' => $notesMap['source_acquisition_mode'] ?? null,
            'source_acquisition_batch_id' => $notesMap['source_acquisition_batch_id'] ?? null,
            'source_window_start' => $notesMap['source_window_start'] ?? null,
            'source_window_end' => $notesMap['source_window_end'] ?? null,
            'warmup_start' => $notesMap['warmup_start'] ?? null,
            'requested_start' => $notesMap['requested_start'] ?? null,
            'requested_end' => $notesMap['requested_end'] ?? null,
            'expected_ticker_count' => isset($notesMap['expected_ticker_count']) && $notesMap['expected_ticker_count'] !== '' ? (int) $notesMap['expected_ticker_count'] : null,
            'success_ticker_count' => isset($notesMap['success_ticker_count']) && $notesMap['success_ticker_count'] !== '' ? (int) $notesMap['success_ticker_count'] : null,
            'failed_ticker_count' => isset($notesMap['failed_ticker_count']) && $notesMap['failed_ticker_count'] !== '' ? (int) $notesMap['failed_ticker_count'] : null,
            'max_failed_allowed_for_coverage' => isset($notesMap['max_failed_allowed_for_coverage']) && $notesMap['max_failed_allowed_for_coverage'] !== '' ? (int) $notesMap['max_failed_allowed_for_coverage'] : null,
            'coverage_impossible' => isset($notesMap['coverage_impossible']) && $notesMap['coverage_impossible'] !== '' ? (bool) $notesMap['coverage_impossible'] : null,
        ];
    }

    private function deriveSourceFinalStatus($record)
    {
        $sourceReason = $record->source_final_reason_code ?? null;
        if ($sourceReason === 'RUN_SOURCE_PARTIAL_RESPONSE') {
            return 'PARTIAL';
        }

        if ($sourceReason !== null && $sourceReason !== '') {
            return 'FAILED';
        }

        return $this->field($record, 'terminal_status') === 'SUCCESS' ? 'SUCCESS' : null;
    }

    private function mergeSourceContextFromTelemetry(array $sourceContext, $sourceAttemptTelemetry)
    {
        if (! is_array($sourceAttemptTelemetry)) {
            return $sourceContext;
        }

        $merged = $sourceContext;
        $fieldMap = [
            'source_mode' => 'source_mode',
            'source_name' => 'source_name',
            'source_input_file' => 'source_input_file',
            'provider' => 'provider',
            'source_provider' => 'provider',
            'source_timeout_seconds' => 'timeout_seconds',
            'source_retry_max' => 'retry_max',
            'source_attempt_count' => 'attempt_count',
            'source_success_after_retry' => 'success_after_retry',
            'source_retry_exhausted' => 'retry_exhausted',
            'source_final_http_status' => 'final_http_status',
            'source_final_reason_code' => 'final_reason_code',
            'timeout_seconds' => 'timeout_seconds',
            'retry_max' => 'retry_max',
            'attempt_count' => 'attempt_count',
            'source_acquisition_state' => 'source_acquisition_state',
            'source_acquisition_mode' => 'source_acquisition_mode',
            'source_acquisition_batch_id' => 'source_acquisition_batch_id',
            'source_window_start' => 'source_window_start',
            'source_window_end' => 'source_window_end',
            'warmup_start' => 'warmup_start',
            'requested_start' => 'requested_start',
            'requested_end' => 'requested_end',
            'expected_ticker_count' => 'expected_ticker_count',
            'success_ticker_count' => 'success_ticker_count',
            'failed_ticker_count' => 'failed_ticker_count',
            'max_failed_allowed_for_coverage' => 'max_failed_allowed_for_coverage',
            'coverage_impossible' => 'coverage_impossible',
            'success_after_retry' => 'success_after_retry',
            'final_http_status' => 'final_http_status',
            'final_reason_code' => 'final_reason_code',
            'retry_exhausted' => 'retry_exhausted',
            'source_final_status' => 'source_final_status',
        ];

        foreach ($fieldMap as $contextKey => $telemetryKey) {
            $contextHasValue = array_key_exists($contextKey, $merged) && $merged[$contextKey] !== null && $merged[$contextKey] !== '';
            $telemetryHasValue = array_key_exists($telemetryKey, $sourceAttemptTelemetry) && $sourceAttemptTelemetry[$telemetryKey] !== null && $sourceAttemptTelemetry[$telemetryKey] !== '';

            if (! $contextHasValue && $telemetryHasValue) {
                $merged[$contextKey] = $sourceAttemptTelemetry[$telemetryKey];
            }
        }

        return $merged;
    }


    private function normalizeSourceContextPaths(array $sourceContext)
    {
        if (array_key_exists('source_input_file', $sourceContext) && $sourceContext['source_input_file'] !== null && $sourceContext['source_input_file'] !== '') {
            $sourceContext['source_input_file'] = $this->normalizeOptionalPathForDisplay($sourceContext['source_input_file']);
        }

        return $sourceContext;
    }

    private function normalizeSourceAttemptTelemetryPaths($telemetry)
    {
        if (! is_array($telemetry)) {
            return $telemetry;
        }

        if (array_key_exists('source_input_file', $telemetry) && $telemetry['source_input_file'] !== null && $telemetry['source_input_file'] !== '') {
            $telemetry['source_input_file'] = $this->normalizeOptionalPathForDisplay($telemetry['source_input_file']);
        }

        return $telemetry;
    }

    private function normalizeOptionalPathForDisplay($path)
    {
        if ($path === null || $path === '') {
            return $path;
        }

        $normalized = str_replace('\\', '/', (string) $path);

        if ($this->looksLikeRelativeProjectPath($normalized)) {
            return basename($normalized);
        }

        return $normalized;
    }

    private function looksLikeRelativeProjectPath($path)
    {
        if ($path === null || $path === '') {
            return false;
        }

        $path = (string) $path;

        if (preg_match('~^[A-Za-z]:/~', $path) === 1) {
            return false;
        }

        if (strpos($path, '//') === 0 || strpos($path, '/') === 0 || strpos($path, '\\') === 0) {
            return false;
        }

        return true;
    }

    private function normalizeNullableInt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function buildSourceSummaryString(array $sourceContext)
    {
        $summaryParts = [];

        if (($sourceContext['provider'] ?? '') !== '') {
            $summaryParts[] = 'provider='.(string) $sourceContext['provider'];
        }

        if (array_key_exists('timeout_seconds', $sourceContext) && $sourceContext['timeout_seconds'] !== null) {
            $summaryParts[] = 'timeout_seconds='.(string) $sourceContext['timeout_seconds'];
        }

        if (array_key_exists('retry_max', $sourceContext) && $sourceContext['retry_max'] !== null) {
            $summaryParts[] = 'retry_max='.(string) $sourceContext['retry_max'];
        }

        if (array_key_exists('attempt_count', $sourceContext) && $sourceContext['attempt_count'] !== null) {
            $summaryParts[] = 'attempt_count='.(string) $sourceContext['attempt_count'];
        }

        if (isset($sourceContext['success_after_retry']) && $sourceContext['success_after_retry'] !== '') {
            $summaryParts[] = 'success_after_retry='.(string) $sourceContext['success_after_retry'];
        }

        if (array_key_exists('final_http_status', $sourceContext) && $sourceContext['final_http_status'] !== null) {
            $summaryParts[] = 'final_http_status='.(string) $sourceContext['final_http_status'];
        }

        if (($sourceContext['final_reason_code'] ?? '') !== '') {
            $summaryParts[] = 'final_reason_code='.(string) $sourceContext['final_reason_code'];
        }

        return $summaryParts === [] ? null : implode(' | ', $summaryParts);
    }

    private function parseRunNotes($notes)
    {
        if ($notes === '') {
            return [];
        }

        $segments = preg_split('/\s*;\s*/', $notes);
        if (! is_array($segments)) {
            return [];
        }

        $parsed = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '' || strpos($segment, '=') === false) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $key = trim((string) $key);
            $value = trim((string) $value);

            if ($key === '') {
                continue;
            }

            $parsed[$key] = $value;
        }

        return $parsed;
    }

    private function parseCsvList($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }

        $items = array_values(array_unique(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, explode(',', (string) $value)), function ($item) {
            return $item !== '';
        })));

        sort($items);

        return $items;
    }

    private function intList(array $values)
    {
        $values = array_values(array_unique(array_filter(array_map('intval', $values), function ($value) {
            return $value > 0;
        })));
        sort($values);

        return $values;
    }

    private function buildReplayResult($metric)
    {
        return [
            'replay_id' => (int) $metric->replay_id,
            'replay_suite' => $this->field($metric, 'replay_suite'),
            'replay_case' => $this->field($metric, 'replay_case'),
            'fixture_id' => $this->field($metric, 'fixture_id'),
            'fixture_version' => $this->field($metric, 'fixture_version'),
            'fixture_schema_version' => $this->field($metric, 'fixture_schema_version'),
            'fixture_source' => $this->field($metric, 'fixture_source'),
            'fixture_created_at' => $this->field($metric, 'fixture_created_at'),
            'trade_date' => $metric->trade_date,
            'trade_date_effective' => $metric->trade_date_effective,
            'source' => $metric->source,
            'source_context' => $this->buildReplayActualSourceContext($metric),
            'expected_source_context' => $this->buildReplayExpectedSourceContext($metric),
            'publication_context' => $this->buildReplayActualPublicationContext($metric),
            'expected_publication_context' => $this->buildReplayExpectedPublicationContext($metric),
            'pointer_context' => $this->buildReplayActualPointerContext($metric),
            'expected_pointer_context' => $this->buildReplayExpectedPointerContext($metric),
            'status' => $metric->status,
            'terminal_status' => $metric->status,
            'publishability_state' => $this->field($metric, 'publishability_state'),
            'comparison_result' => $metric->comparison_result,
            'replay_status' => $this->field($metric, 'replay_status') ?: $this->replayStatusForComparison($metric->comparison_result ?? null),
            'comparison_note' => $metric->comparison_note,
            'artifact_changed_scope' => $metric->artifact_changed_scope,
            'config_identity' => $metric->config_identity,
            'publication_version' => $this->field($metric, 'publication_version') !== null ? (int) $this->field($metric, 'publication_version') : null,
            'correction_lifecycle' => $this->buildReplayCorrectionLifecycle($metric),
            'coverage' => $this->buildCoverageState($metric),
            'expected_coverage' => $this->buildExpectedCoverageState($metric),
            'coverage_ratio' => $metric->coverage_ratio !== null ? (float) $metric->coverage_ratio : null,
            'bars_rows_written' => $metric->bars_rows_written !== null ? (int) $metric->bars_rows_written : null,
            'indicators_rows_written' => $metric->indicators_rows_written !== null ? (int) $metric->indicators_rows_written : null,
            'eligibility_rows_written' => $metric->eligibility_rows_written !== null ? (int) $metric->eligibility_rows_written : null,
            'eligible_count' => $metric->eligible_count !== null ? (int) $metric->eligible_count : null,
            'invalid_bar_count' => $metric->invalid_bar_count !== null ? (int) $metric->invalid_bar_count : null,
            'invalid_indicator_count' => $metric->invalid_indicator_count !== null ? (int) $metric->invalid_indicator_count : null,
            'warning_count' => $metric->warning_count !== null ? (int) $metric->warning_count : null,
            'hard_reject_count' => $metric->hard_reject_count !== null ? (int) $metric->hard_reject_count : null,
            'bars_batch_hash' => $metric->bars_batch_hash,
            'indicators_batch_hash' => $metric->indicators_batch_hash,
            'eligibility_batch_hash' => $metric->eligibility_batch_hash,
            'seal_state' => $metric->seal_state,
            'sealed_at' => $metric->sealed_at,
            'expected_status' => $metric->expected_status,
            'expected_trade_date_effective' => $metric->expected_trade_date_effective,
            'expected_seal_state' => $metric->expected_seal_state,
            'expected_config_identity' => $metric->expected_config_identity ?? null,
            'expected_publication_version' => $metric->expected_publication_version !== null ? (int) $metric->expected_publication_version : null,
            'expected_bars_batch_hash' => $metric->expected_bars_batch_hash ?? null,
            'expected_indicators_batch_hash' => $metric->expected_indicators_batch_hash ?? null,
            'expected_eligibility_batch_hash' => $metric->expected_eligibility_batch_hash ?? null,
            'expected_correction_lifecycle' => $this->buildReplayExpectedCorrectionLifecycle($metric),
            'actual_correction_lifecycle' => $this->buildReplayActualCorrectionLifecycle($metric),
            'mismatch_summary' => $metric->mismatch_summary,
            'mismatch_count' => $this->field($metric, 'mismatch_count') !== null ? (int) $this->field($metric, 'mismatch_count') : null,
            'mismatch_reason_codes' => $this->decodeJsonArray($this->field($metric, 'mismatch_reason_codes_json')),
            'mismatches' => $this->decodeJsonArray($this->field($metric, 'mismatches_json')),
            'expected_context' => $this->decodeJsonObject($this->field($metric, 'expected_context_json')),
            'actual_context' => $this->decodeJsonObject($this->field($metric, 'actual_context_json')),
            'ignored_volatile_fields' => $this->decodeJsonArray($this->field($metric, 'ignored_volatile_fields_json')),
            'deterministic_fields_checked' => $this->decodeJsonArray($this->field($metric, 'deterministic_fields_checked_json')),
            'final_reason_code' => $this->field($metric, 'final_reason_code'),
            'created_at' => $metric->created_at,
        ];
    }

    private function buildReplayExpectedState($metric, array $expectedReasonCodeCounts)
    {
        return [
            'fixture_id' => $this->field($metric, 'fixture_id'),
            'fixture_version' => $this->field($metric, 'fixture_version'),
            'fixture_schema_version' => $this->field($metric, 'fixture_schema_version'),
            'expected_context' => $this->decodeJsonObject($this->field($metric, 'expected_context_json')),
            'status' => $metric->expected_status,
            'terminal_status' => $this->field($metric, 'expected_terminal_status') ?: $metric->expected_status,
            'publishability_state' => $this->field($metric, 'expected_publishability_state'),
            'trade_date_effective' => $metric->expected_trade_date_effective,
            'seal_state' => $metric->expected_seal_state,
            'config_identity' => $metric->expected_config_identity ?? null,
            'publication_version' => $metric->expected_publication_version !== null ? (int) $metric->expected_publication_version : null,
            'publication_context' => $this->buildReplayExpectedPublicationContext($metric),
            'pointer_context' => $this->buildReplayExpectedPointerContext($metric),
            'coverage' => $this->buildExpectedCoverageState($metric),
            'bars_batch_hash' => $metric->expected_bars_batch_hash ?? null,
            'indicators_batch_hash' => $metric->expected_indicators_batch_hash ?? null,
            'eligibility_batch_hash' => $metric->expected_eligibility_batch_hash ?? null,
            'reason_code_counts' => $expectedReasonCodeCounts,
            'correction_lifecycle' => $this->buildReplayExpectedCorrectionLifecycle($metric),
            'source_context' => $this->buildReplayExpectedSourceContext($metric),
        ];
    }

    private function buildReplayActualState($metric, array $reasonCodes)
    {
        return [
            'actual_context' => $this->decodeJsonObject($this->field($metric, 'actual_context_json')),
            'status' => $metric->status,
            'terminal_status' => $metric->status,
            'publishability_state' => $this->field($metric, 'publishability_state'),
            'trade_date_effective' => $metric->trade_date_effective,
            'seal_state' => $metric->seal_state,
            'config_identity' => $metric->config_identity,
            'publication_version' => $metric->publication_version !== null ? (int) $metric->publication_version : null,
            'publication_context' => $this->buildReplayActualPublicationContext($metric),
            'pointer_context' => $this->buildReplayActualPointerContext($metric),
            'coverage' => $this->buildCoverageState($metric),
            'bars_batch_hash' => $metric->bars_batch_hash,
            'indicators_batch_hash' => $metric->indicators_batch_hash,
            'eligibility_batch_hash' => $metric->eligibility_batch_hash,
            'reason_code_counts' => $reasonCodes,
            'correction_lifecycle' => $this->buildReplayActualCorrectionLifecycle($metric),
            'source_context' => $this->buildReplayActualSourceContext($metric),
        ];
    }


    private function buildReplayActualSourceContext($metric)
    {
        return [
            'source_mode' => $this->field($metric, 'source_mode') ?? $this->field($metric, 'source'),
            'source_name' => $this->field($metric, 'source_name'),
            'provider' => $this->field($metric, 'source_provider'),
            'timeout_seconds' => $this->field($metric, 'source_timeout_seconds') !== null ? (int) $this->field($metric, 'source_timeout_seconds') : null,
            'retry_max' => $this->field($metric, 'source_retry_max') !== null ? (int) $this->field($metric, 'source_retry_max') : null,
            'attempt_count' => $this->field($metric, 'source_attempt_count') !== null ? (int) $this->field($metric, 'source_attempt_count') : null,
            'success_after_retry' => $this->field($metric, 'source_success_after_retry') !== null ? (bool) $this->field($metric, 'source_success_after_retry') : null,
            'retry_exhausted' => $this->field($metric, 'source_retry_exhausted') !== null ? (bool) $this->field($metric, 'source_retry_exhausted') : null,
            'final_http_status' => $this->field($metric, 'source_final_http_status') !== null ? (int) $this->field($metric, 'source_final_http_status') : null,
            'final_reason_code' => $this->field($metric, 'source_final_reason_code'),
            'source_input_file' => $this->field($metric, 'source_input_file'),
            'source_file_hash' => $this->field($metric, 'source_file_hash'),
            'source_file_hash_algorithm' => $this->field($metric, 'source_file_hash_algorithm'),
            'source_file_size_bytes' => $this->field($metric, 'source_file_size_bytes') !== null ? (int) $this->field($metric, 'source_file_size_bytes') : null,
            'source_file_row_count' => $this->field($metric, 'source_file_row_count') !== null ? (int) $this->field($metric, 'source_file_row_count') : null,
            'accepted_row_count' => $this->field($metric, 'bars_rows_written') !== null ? (int) $this->field($metric, 'bars_rows_written') : null,
            'rejected_row_count' => $this->field($metric, 'invalid_bar_count') !== null ? (int) $this->field($metric, 'invalid_bar_count') : null,
            'invalid_row_count' => $this->field($metric, 'invalid_bar_count') !== null ? (int) $this->field($metric, 'invalid_bar_count') : null,
        ];
    }

    private function buildReplayExpectedSourceContext($metric)
    {
        return [
            'source_mode' => $this->field($metric, 'expected_source_mode') ?? $this->field($metric, 'expected_source'),
            'source_name' => $this->field($metric, 'expected_source_name'),
            'provider' => $this->field($metric, 'expected_source_provider'),
            'timeout_seconds' => $this->field($metric, 'expected_source_timeout_seconds') !== null ? (int) $this->field($metric, 'expected_source_timeout_seconds') : null,
            'retry_max' => $this->field($metric, 'expected_source_retry_max') !== null ? (int) $this->field($metric, 'expected_source_retry_max') : null,
            'attempt_count' => $this->field($metric, 'expected_source_attempt_count') !== null ? (int) $this->field($metric, 'expected_source_attempt_count') : null,
            'success_after_retry' => $this->field($metric, 'expected_source_success_after_retry') !== null ? (bool) $this->field($metric, 'expected_source_success_after_retry') : null,
            'retry_exhausted' => $this->field($metric, 'expected_source_retry_exhausted') !== null ? (bool) $this->field($metric, 'expected_source_retry_exhausted') : null,
            'final_http_status' => $this->field($metric, 'expected_source_final_http_status') !== null ? (int) $this->field($metric, 'expected_source_final_http_status') : null,
            'final_reason_code' => $this->field($metric, 'expected_source_final_reason_code'),
            'source_input_file' => $this->field($metric, 'expected_source_input_file'),
            'source_file_hash' => $this->field($metric, 'expected_source_file_hash'),
            'source_file_hash_algorithm' => $this->field($metric, 'expected_source_file_hash_algorithm'),
            'source_file_size_bytes' => $this->field($metric, 'expected_source_file_size_bytes') !== null ? (int) $this->field($metric, 'expected_source_file_size_bytes') : null,
            'source_file_row_count' => $this->field($metric, 'expected_source_file_row_count') !== null ? (int) $this->field($metric, 'expected_source_file_row_count') : null,
            'accepted_row_count' => null,
            'rejected_row_count' => null,
            'invalid_row_count' => null,
        ];
    }

    private function buildReplayActualPublicationContext($metric)
    {
        return $this->buildReplayPublicationAuditContext(
            $this->field($metric, 'publication_id'),
            $this->field($metric, 'publication_run_id'),
            $this->field($metric, 'publication_version'),
            $this->field($metric, 'status'),
            $this->field($metric, 'publishability_state'),
            $this->field($metric, 'is_current_publication'),
            $this->field($metric, 'seal_state'),
            $this->field($metric, 'bars_batch_hash'),
            $this->field($metric, 'indicators_batch_hash'),
            $this->field($metric, 'eligibility_batch_hash')
        );
    }

    private function buildReplayExpectedPublicationContext($metric)
    {
        return $this->buildReplayPublicationAuditContext(
            $this->field($metric, 'expected_publication_id'),
            $this->field($metric, 'expected_publication_run_id'),
            $this->field($metric, 'expected_publication_version'),
            $this->field($metric, 'expected_terminal_status') ?: $this->field($metric, 'expected_status'),
            $this->field($metric, 'expected_publishability_state'),
            $this->field($metric, 'expected_is_current_publication'),
            $this->field($metric, 'expected_seal_state'),
            $this->field($metric, 'expected_bars_batch_hash'),
            $this->field($metric, 'expected_indicators_batch_hash'),
            $this->field($metric, 'expected_eligibility_batch_hash')
        );
    }

    private function buildReplayPublicationAuditContext($publicationId, $publicationRunId, $publicationVersion, $terminalStatus, $publishabilityState, $isCurrentPublication, $sealState, $barsHash, $indicatorsHash, $eligibilityHash)
    {
        $hasPublication = $publicationId !== null && $publicationId !== '';
        $isCurrent = $isCurrentPublication !== null ? (bool) $isCurrentPublication : false;
        $resolutionMode = $hasPublication ? ($isCurrent ? 'CURRENT_READABLE_PUBLICATION_AUDIT' : 'HISTORICAL_PUBLICATION_AUDIT') : 'NO_PUBLICATION_CONTEXT';

        return [
            'publication_id' => $hasPublication ? (int) $publicationId : null,
            'publication_run_id' => $publicationRunId !== null ? (int) $publicationRunId : null,
            'publication_version' => $publicationVersion !== null ? (int) $publicationVersion : null,
            'publication_terminal_status' => $terminalStatus,
            'publication_publishability_state' => $publishabilityState,
            'publication_is_current' => $isCurrentPublication !== null ? (bool) $isCurrentPublication : null,
            'publication_seal_state' => $sealState,
            'evidence_resolution_mode' => $resolutionMode,
            'evidence_publication_scope' => $hasPublication ? ($isCurrent ? 'CURRENT_POINTER_PUBLICATION' : 'HISTORICAL_SEALED_PUBLICATION') : 'NO_PUBLICATION',
            'current_pointer_required' => $hasPublication && $isCurrent,
            'current_pointer_status' => $hasPublication && $isCurrent ? 'RESOLVED_READABLE_CURRENT' : 'NOT_CURRENT_POINTER',
            'historical_publication_allowed' => $hasPublication && ! $isCurrent,
            'artifact_scope' => $hasPublication ? 'PUBLICATION_SCOPED' : null,
            'coverage_basis_publication_id' => $hasPublication ? (int) $publicationId : null,
            'coverage_basis_run_id' => $publicationRunId !== null ? (int) $publicationRunId : null,
            'lineage_verification_status' => $hasPublication ? 'LINEAGE_CONTEXT_FROM_REPLAY_METRIC' : 'NO_PUBLICATION_CONTEXT',
            'evidence_reason_code' => $hasPublication ? ($isCurrent ? 'CURRENT_READABLE_PUBLICATION_RESOLVED' : 'HISTORICAL_SEALED_PUBLICATION_RESOLVED') : 'EVIDENCE_PUBLICATION_NOT_AVAILABLE',
            'publication_artifact_lineage' => [
                'bars_batch_hash' => $barsHash,
                'indicators_batch_hash' => $indicatorsHash,
                'eligibility_batch_hash' => $eligibilityHash,
            ],
        ];
    }

    private function buildReplayActualPointerContext($metric)
    {
        $actualContext = $this->decodeJsonObject($this->field($metric, 'actual_context_json'));
        $actualPointer = is_array($actualContext['actual_pointer_context'] ?? null) ? $actualContext['actual_pointer_context'] : [];

        return [
            'pointer_publication_id' => $this->field($metric, 'publication_id') !== null ? (int) $this->field($metric, 'publication_id') : null,
            'pointer_run_id' => $this->field($metric, 'publication_run_id') !== null ? (int) $this->field($metric, 'publication_run_id') : null,
            'pointer_publication_version' => $this->field($metric, 'publication_version') !== null ? (int) $this->field($metric, 'publication_version') : null,
            'pointer_resolve_status' => $actualPointer['pointer_resolve_status'] ?? (($this->field($metric, 'publishability_state') === 'READABLE' && $this->field($metric, 'is_current_publication') !== null && (bool) $this->field($metric, 'is_current_publication')) ? 'RESOLVED_READABLE_CURRENT' : 'NOT_RESOLVED_READABLE_CURRENT'),
            'pointer_switched' => array_key_exists('pointer_switched', $actualPointer) ? (bool) $actualPointer['pointer_switched'] : ($this->field($metric, 'is_current_publication') !== null ? (bool) $this->field($metric, 'is_current_publication') : null),
        ];
    }

    private function buildReplayExpectedPointerContext($metric)
    {
        $expectedContext = $this->decodeJsonObject($this->field($metric, 'expected_context_json'));
        $expectedPointer = is_array($expectedContext['expected_pointer_context'] ?? null) ? $expectedContext['expected_pointer_context'] : [];

        return [
            'pointer_publication_id' => $this->field($metric, 'expected_publication_id') !== null ? (int) $this->field($metric, 'expected_publication_id') : null,
            'pointer_run_id' => $this->field($metric, 'expected_publication_run_id') !== null ? (int) $this->field($metric, 'expected_publication_run_id') : null,
            'pointer_publication_version' => $this->field($metric, 'expected_publication_version') !== null ? (int) $this->field($metric, 'expected_publication_version') : null,
            'pointer_resolve_status' => $expectedPointer['pointer_resolve_status'] ?? (($this->field($metric, 'expected_publishability_state') === 'READABLE' && $this->field($metric, 'expected_is_current_publication') !== null && (bool) $this->field($metric, 'expected_is_current_publication')) ? 'RESOLVED_READABLE_CURRENT' : 'NOT_RESOLVED_READABLE_CURRENT'),
            'pointer_switched' => array_key_exists('pointer_switched', $expectedPointer) ? (bool) $expectedPointer['pointer_switched'] : ($this->field($metric, 'expected_is_current_publication') !== null ? (bool) $this->field($metric, 'expected_is_current_publication') : null),
        ];
    }

    private function buildReplayCorrectionLifecycle($metric)
    {
        return [
            'actual' => $this->buildReplayActualCorrectionLifecycle($metric),
            'expected' => $this->buildReplayExpectedCorrectionLifecycle($metric),
        ];
    }

    private function buildReplayActualCorrectionLifecycle($metric)
    {
        return [
            'correction_id' => $this->field($metric, 'correction_id') !== null ? (int) $this->field($metric, 'correction_id') : null,
            'correction_status' => $this->field($metric, 'correction_status'),
            'correction_outcome' => $this->field($metric, 'correction_outcome'),
            'correction_reseal_status' => $this->field($metric, 'correction_reseal_status'),
            'correction_publication_switch' => $this->field($metric, 'correction_publication_switch') !== null ? (bool) $this->field($metric, 'correction_publication_switch') : null,
            'baseline_publication_id' => $this->field($metric, 'baseline_publication_id') !== null ? (int) $this->field($metric, 'baseline_publication_id') : null,
            'candidate_publication_id' => $this->field($metric, 'candidate_publication_id') !== null ? (int) $this->field($metric, 'candidate_publication_id') : null,
        ];
    }

    private function buildReplayExpectedCorrectionLifecycle($metric)
    {
        return [
            'correction_id' => $this->field($metric, 'expected_correction_id') !== null ? (int) $this->field($metric, 'expected_correction_id') : null,
            'correction_status' => $this->field($metric, 'expected_correction_status'),
            'correction_outcome' => $this->field($metric, 'expected_correction_outcome'),
            'correction_reseal_status' => $this->field($metric, 'expected_correction_reseal_status'),
            'correction_publication_switch' => $this->field($metric, 'expected_correction_publication_switch') !== null ? (bool) $this->field($metric, 'expected_correction_publication_switch') : null,
            'baseline_publication_id' => $this->field($metric, 'expected_baseline_publication_id') !== null ? (int) $this->field($metric, 'expected_baseline_publication_id') : null,
            'candidate_publication_id' => $this->field($metric, 'expected_candidate_publication_id') !== null ? (int) $this->field($metric, 'expected_candidate_publication_id') : null,
        ];
    }


    private function buildCoverageState($record)
    {
        $coverageGateState = CoverageGateStateNormalizer::normalize($record->coverage_gate_state ?? null);
        $legacyCoverageGateStateRaw = CoverageGateStateNormalizer::legacyRaw($record->coverage_gate_state ?? null);
        $reasonCode = $this->resolveCoverageReasonCodeFromState($coverageGateState);
        $missingSample = $this->decodeJsonArray($record->coverage_missing_sample_json ?? null);
        $notesMap = $this->parseRunNotes((string) ($this->field($record, 'notes') ?? ''));

        return [
            'coverage_universe_count' => isset($record->coverage_universe_count) && $record->coverage_universe_count !== null ? (int) $record->coverage_universe_count : null,
            'coverage_expected_count' => isset($record->coverage_universe_count) && $record->coverage_universe_count !== null ? (int) $record->coverage_universe_count : null,
            'coverage_available_count' => isset($record->coverage_available_count) && $record->coverage_available_count !== null ? (int) $record->coverage_available_count : null,
            'coverage_missing_count' => isset($record->coverage_missing_count) && $record->coverage_missing_count !== null ? (int) $record->coverage_missing_count : null,
            'expected_bar_count' => isset($record->coverage_universe_count) && $record->coverage_universe_count !== null ? (int) $record->coverage_universe_count : null,
            'available_bar_count' => isset($record->coverage_available_count) && $record->coverage_available_count !== null ? (int) $record->coverage_available_count : null,
            'missing_bar_count' => isset($record->coverage_missing_count) && $record->coverage_missing_count !== null ? (int) $record->coverage_missing_count : null,
            'coverage_ratio' => isset($record->coverage_ratio) && $record->coverage_ratio !== null ? (float) $record->coverage_ratio : null,
            'coverage_min_threshold' => isset($record->coverage_min_threshold) && $record->coverage_min_threshold !== null ? (float) $record->coverage_min_threshold : null,
            'coverage_gate_state' => $coverageGateState,
            'legacy_coverage_gate_state_raw' => $legacyCoverageGateStateRaw,
            'coverage_passed' => $coverageGateState === 'PASS',
            'coverage_threshold_mode' => $record->coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $record->coverage_universe_basis ?? null,
            'coverage_contract_version' => $record->coverage_contract_version ?? null,
            'coverage_policy_version' => $record->coverage_contract_version ?? null,
            'coverage_missing_sample' => $missingSample,
            'coverage_failed_symbols' => $missingSample,
            'coverage_evaluated_at' => $this->field($record, 'finished_at') ?: $this->field($record, 'updated_at'),
            'coverage_reason_code' => $reasonCode,
            'coverage_reason_message' => $this->resolveReasonMessage($reasonCode),
            'coverage_basis' => $notesMap['coverage_basis'] ?? null,
            'coverage_basis_publication_id' => isset($notesMap['coverage_basis_publication_id']) && $notesMap['coverage_basis_publication_id'] !== '' ? (int) $notesMap['coverage_basis_publication_id'] : null,
            'coverage_basis_artifact_scope' => $notesMap['coverage_basis_artifact_scope'] ?? null,
            'candidate_publication_id' => isset($notesMap['candidate_publication_id']) && $notesMap['candidate_publication_id'] !== '' ? (int) $notesMap['candidate_publication_id'] : null,
            'baseline_publication_id' => isset($notesMap['baseline_publication_id']) && $notesMap['baseline_publication_id'] !== '' ? (int) $notesMap['baseline_publication_id'] : null,
            'candidate_available_count' => isset($notesMap['candidate_available_count']) && $notesMap['candidate_available_count'] !== '' ? (int) $notesMap['candidate_available_count'] : null,
            'candidate_missing_count' => isset($notesMap['candidate_missing_count']) && $notesMap['candidate_missing_count'] !== '' ? (int) $notesMap['candidate_missing_count'] : null,
            'candidate_coverage_ratio' => isset($notesMap['candidate_coverage_ratio']) && $notesMap['candidate_coverage_ratio'] !== '' ? (float) $notesMap['candidate_coverage_ratio'] : null,
        ];
    }

    private function resolveCoverageReasonCodeFromState($coverageGateState)
    {
        $state = CoverageGateStateNormalizer::normalize($coverageGateState);

        if ($state === 'PASS') {
            return 'COVERAGE_THRESHOLD_MET';
        }

        if ($state === 'FAIL') {
            return 'COVERAGE_BELOW_THRESHOLD';
        }

        if ($state === 'NOT_EVALUABLE') {
            return 'RUN_COVERAGE_NOT_EVALUABLE';
        }

        return null;
    }

    private function buildExpectedCoverageState($record)
    {
        $coverageGateState = CoverageGateStateNormalizer::normalize($record->expected_coverage_gate_state ?? null);
        $legacyCoverageGateStateRaw = CoverageGateStateNormalizer::legacyRaw($record->expected_coverage_gate_state ?? null);
        $reasonCode = $this->resolveCoverageReasonCodeFromState($coverageGateState);
        $missingSample = $this->decodeJsonArray($record->expected_coverage_missing_sample_json ?? null);

        return [
            'coverage_universe_count' => isset($record->expected_coverage_universe_count) && $record->expected_coverage_universe_count !== null ? (int) $record->expected_coverage_universe_count : null,
            'coverage_expected_count' => isset($record->expected_coverage_universe_count) && $record->expected_coverage_universe_count !== null ? (int) $record->expected_coverage_universe_count : null,
            'coverage_available_count' => isset($record->expected_coverage_available_count) && $record->expected_coverage_available_count !== null ? (int) $record->expected_coverage_available_count : null,
            'coverage_missing_count' => isset($record->expected_coverage_missing_count) && $record->expected_coverage_missing_count !== null ? (int) $record->expected_coverage_missing_count : null,
            'expected_bar_count' => isset($record->expected_coverage_universe_count) && $record->expected_coverage_universe_count !== null ? (int) $record->expected_coverage_universe_count : null,
            'available_bar_count' => isset($record->expected_coverage_available_count) && $record->expected_coverage_available_count !== null ? (int) $record->expected_coverage_available_count : null,
            'missing_bar_count' => isset($record->expected_coverage_missing_count) && $record->expected_coverage_missing_count !== null ? (int) $record->expected_coverage_missing_count : null,
            'coverage_ratio' => isset($record->expected_coverage_ratio) && $record->expected_coverage_ratio !== null ? (float) $record->expected_coverage_ratio : null,
            'coverage_min_threshold' => isset($record->expected_coverage_min_threshold) && $record->expected_coverage_min_threshold !== null ? (float) $record->expected_coverage_min_threshold : null,
            'coverage_gate_state' => $coverageGateState,
            'legacy_coverage_gate_state_raw' => $legacyCoverageGateStateRaw,
            'coverage_passed' => $coverageGateState === 'PASS',
            'coverage_threshold_mode' => $record->expected_coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $record->expected_coverage_universe_basis ?? null,
            'coverage_contract_version' => $record->expected_coverage_contract_version ?? null,
            'coverage_policy_version' => $record->expected_coverage_contract_version ?? null,
            'coverage_missing_sample' => $missingSample,
            'coverage_failed_symbols' => $missingSample,
            'coverage_evaluated_at' => $this->field($record, 'created_at'),
            'coverage_reason_code' => $reasonCode,
            'coverage_reason_message' => $this->resolveReasonMessage($reasonCode),
        ];
    }

    private function decodeJsonArray($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function decodeJsonObject($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function replayStatusForComparison($comparisonResult)
    {
        if (in_array((string) $comparisonResult, ['MATCH', 'EXPECTED_DEGRADE'], true)) {
            return 'PASS';
        }

        if (in_array((string) $comparisonResult, ['MISMATCH', 'UNEXPECTED'], true)) {
            return 'FAIL';
        }

        return 'BLOCKED';
    }

    private function decodeExpectedReasonCodeCounts($json)
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function buildAnomalyReport(array $runSummary, array $dominantReasonCodes, $manifest = null)
    {
        $dominant = count($dominantReasonCodes) ? $dominantReasonCodes[0]['reason_code'] : 'NONE';
        $lines = [
            '# Anomaly Report',
            '- Requested date: '.$runSummary['trade_date_requested'],
            '- Effective date: '.($runSummary['trade_date_effective'] ?: 'null'),
            '- Status: '.$runSummary['terminal_status'],
            '- Dominant anomaly: '.$dominant,
            '- Consumer effect: '.($runSummary['publishability_state'] === 'READABLE' ? 'requested date readable' : 'fallback or no readable state'),
            '- Publication safety: '.($manifest && $manifest['seal_state'] === 'SEALED' && $manifest['is_current'] ? 'current sealed publication present' : 'requested date not proven readable as current sealed publication'),
        ];

        return implode("\n", $lines)."\n";
    }

    private function resolveCorrectionChangedDecision($correction, $priorPublication, $newPublication)
    {
        $status = strtoupper((string) $this->field($correction, 'status'));
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') {
            return 'UNCHANGED';
        }

        if ($priorPublication && $newPublication
            && (string) $this->field($priorPublication, 'bars_batch_hash') === (string) $this->field($newPublication, 'bars_batch_hash')
            && (string) $this->field($priorPublication, 'indicators_batch_hash') === (string) $this->field($newPublication, 'indicators_batch_hash')
            && (string) $this->field($priorPublication, 'eligibility_batch_hash') === (string) $this->field($newPublication, 'eligibility_batch_hash')) {
            return 'UNCHANGED';
        }

        if ($priorPublication && $newPublication) {
            return 'CHANGED';
        }

        return 'UNKNOWN';
    }

    private function resolveCorrectionResealStatus($correction, $changedDecision, $newPublication)
    {
        $status = strtoupper((string) $this->field($correction, 'status'));

        if ($changedDecision === 'UNCHANGED') {
            return 'NOT_RESEALED_UNCHANGED';
        }

        if ($changedDecision === 'CHANGED' && $newPublication && (string) $this->field($newPublication, 'seal_state') === 'SEALED') {
            return 'RESEALED';
        }

        if (in_array($status, ['RESEALED', 'PUBLISHED'], true)) {
            return 'RESEAL_EXPECTED_BUT_NOT_PROVEN';
        }

        return 'NOT_RESEALED';
    }

    private function buildCorrectionComparisonSummary($priorPublication, $newPublication, $changedDecision = null)
    {
        if ($changedDecision === 'UNCHANGED') {
            return 'No consumer-visible hash change detected.';
        }

        if (! $priorPublication && ! $newPublication) {
            return 'No prior or new publication found.';
        }
        if ($priorPublication && $newPublication
            && (string) $this->field($priorPublication, 'bars_batch_hash') === (string) $this->field($newPublication, 'bars_batch_hash')
            && (string) $this->field($priorPublication, 'indicators_batch_hash') === (string) $this->field($newPublication, 'indicators_batch_hash')
            && (string) $this->field($priorPublication, 'eligibility_batch_hash') === (string) $this->field($newPublication, 'eligibility_batch_hash')) {
            return 'No consumer-visible hash change detected.';
        }

        return 'Publication hash set changed and requires audit comparison.';
    }

    private function resolvedTradeDate($run)
    {
        return $run->trade_date_effective ?: $run->trade_date_requested;
    }

    private function writeJson($path, array $payload)
    {
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function writeCsv($path, array $headers, array $rows)
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = array_key_exists($header, $row) ? $row[$header] : null;
            }
            fputcsv($handle, $line);
        }
        fclose($handle);
    }

    private function ensureDirectory($dir)
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }


    private function defaultEvidenceOutputDir()
    {
        return rtrim((string) $this->configValue('market_data.evidence.output_directory', $this->storagePathValue('app/market_data/evidence')), '/');
    }

    private function configValue($key, $default = null)
    {
        try {
            if (function_exists('app')) {
                $app = app();
                if (is_object($app) && method_exists($app, 'bound') && $app->bound('config')) {
                    return config($key, $default);
                }
            }
        } catch (\Throwable $exception) {
            return $default;
        }

        return $default;
    }

    private function storagePathValue($path)
    {
        try {
            if (function_exists('storage_path')) {
                return storage_path($path);
            }
        } catch (\Throwable $exception) {
            // Fall through to deterministic local path for isolated unit tests.
        }

        return rtrim(getcwd(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    private function defaultRunOutputDir($runId)
    {
        return $this->defaultEvidenceOutputDir().'/runs/run_'.$runId;
    }

    private function defaultCorrectionOutputDir($correctionId)
    {
        return $this->defaultEvidenceOutputDir().'/corrections/correction_'.$correctionId;
    }

    private function defaultReplayOutputDir($replayId, $tradeDate)
    {
        return $this->defaultEvidenceOutputDir().'/replays/replay_'.$replayId.'_'.$tradeDate;
    }
}
