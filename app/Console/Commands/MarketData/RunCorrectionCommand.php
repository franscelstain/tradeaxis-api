<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Application\MarketData\Services\CorrectionCommandPreflightService;

class RunCorrectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:correction:run {correction_id?} {--requested_date=} {--source_mode=} {--latest}';

    protected $description = 'Execute the market-data daily pipeline for an approved correction request.';

    public function handle()
    {
        if (! $this->validateDateString($this->option('requested_date'), 'requested_date') || ! $this->validateSourceModeString($this->option('source_mode'))) {
            return 1;
        }

        $correctionId = (int) $this->argument('correction_id');
        if ($correctionId <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'correction_id must be a positive integer.', [
                'correction_id' => $this->argument('correction_id'),
            ]);
            return 1;
        }

        $correction = app(EodCorrectionRepository::class)->findById($correctionId);

        if (! $correction) {
            $this->renderCommandBlocked('COMMAND_CORRECTION_NOT_FOUND', 'Correction request not found: '.$correctionId, [
                'correction_id' => $correctionId,
            ]);
            return 1;
        }

        if (! in_array($correction->status, ['APPROVED', 'EXECUTING', 'RESEALED'], true)) {
            $this->renderCommandBlocked('COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE', 'Correction request must be APPROVED/EXECUTING/RESEALED before execution. Current status='.$correction->status, [
                'correction_id' => $correctionId,
                'correction_status' => $correction->status,
            ]);
            return 1;
        }

        $requestedDate = $this->option('requested_date') ?: (string) $correction->trade_date;
        $sourceMode = $this->sourceMode();
        try {
            $preflight = app(CorrectionCommandPreflightService::class)->inspect($correctionId, $requestedDate, $sourceMode);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_CORRECTION_PREFLIGHT_FAILED', $e->getMessage(), [
                'correction_id' => $correctionId, 'requested_date' => $requestedDate,
            ]);
            return 1;
        }
        $this->line('operation_mode=PRE_APPLY_DISCLOSURE');
        foreach (['correction_id','requested_date','source_mode','source_identity','correction_status',
            'reason_code','reason_note','baseline_publication_id','baseline_run_id',
            'candidate_config_content_hash','new_run_id','new_config_snapshot_id'] as $field) {
            $this->line($field.'='.(string) $preflight[$field]);
        }
        $this->line('expected_row_effects=new or resumed run; source observations, candidate artifacts, hashes and seal if valid');
        $this->line('pointer_impact=CONDITIONAL_NEW_SEALED_PUBLICATION_OR_PRIOR_CURRENT_PRESERVED');
        try {
            $run = $this->pipeline()->runDaily($requestedDate, $sourceMode, $correctionId, $preflight);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'COMMAND_CORRECTION_') === 0) {
                $this->renderCommandBlocked('COMMAND_CORRECTION_PREFLIGHT_CHANGED', $e->getMessage(), [
                    'correction_id' => $correctionId, 'requested_date' => $requestedDate,
                ]);
                return 2;
            }
            $failed = $this->markCorrectionFailed($correctionId, $correction, $e);
            $reasonCode = $this->reasonCodeFromThrowable($e);
            $this->renderCommandBlocked('CORRECTION_FAILED', 'Correction execution failed before safe publication; baseline current pointer preserved.', [
                'correction_id' => $correctionId,
                'correction_status' => $this->optionalScalar($failed, 'status'),
                'failure_reason_code' => $reasonCode,
                'baseline_publication_id' => $this->optionalScalar($failed, 'baseline_publication_id') ?: $this->optionalScalar($failed, 'prior_publication_id'),
                'candidate_publication_switch' => false,
                'exception_class' => get_class($e),
            ]);
            $this->line('correction_id='.$correctionId);
            $this->line('correction_status='.$this->optionalScalar($failed, 'status'));
            $this->line('correction_outcome=FAILED');
            $this->line('correction_reseal_status=NOT_RESEALED');
            $this->line('baseline_publication_id='.($this->optionalScalar($failed, 'baseline_publication_id') ?: $this->optionalScalar($failed, 'prior_publication_id')));
            $this->line('candidate_publication_id=');
            $this->line('candidate_publication_switch=false');
            $this->line('failure_reason_code='.$reasonCode);
            $this->line('final_outcome_note='.$this->optionalScalar($failed, 'final_outcome_note'));

            return 1;
        }

        $this->renderRunSummary($run);
        $this->line('actual_run_id='.(string) $this->runField($run, 'run_id', ''));
        $this->line('actual_config_snapshot_id='.(string) $this->runField($run, 'config_snapshot_id', ''));
        $this->line('actual_config_hash='.(string) $this->runField($run, 'config_hash', ''));
        $correctionLifecycle = $this->loadCorrectionLifecycle($correctionId);
        $correctionOutcome = $this->resolveCorrectionOutcome($correctionLifecycle);
        $this->line('correction_id='.$correctionId);
        $this->line('correction_status='.(string) optional($correctionLifecycle)->status);
        $this->line('correction_outcome='.$correctionOutcome);
        $this->line('correction_reseal_status='.$this->resolveResealStatus($correctionLifecycle));
        $baselinePublicationId = $this->optionalScalar($correctionLifecycle, 'baseline_publication_id') ?: $this->optionalScalar($correctionLifecycle, 'prior_publication_id');
        $candidatePublicationId = $this->optionalScalar($correctionLifecycle, 'replacement_publication_id');
        if ($candidatePublicationId === '' && $correctionOutcome !== 'UNCHANGED') {
            $candidatePublicationId = $this->optionalScalar($correctionLifecycle, 'new_publication_id');
        }
        $this->line('baseline_publication_id='.$baselinePublicationId);
        $this->line('candidate_publication_id='.$candidatePublicationId);
        $this->line('candidate_publication_switch='.$this->resolveCandidatePublicationSwitch($correctionLifecycle, $run));
        $this->line('final_outcome_note='.$this->optionalScalar($correctionLifecycle, 'final_outcome_note'));

        if ((string) $this->runField($run, 'terminal_status') === 'HELD'
            || (string) $this->runField($run, 'publishability_state') === 'NOT_READABLE') {
            $this->line('command_outcome=HELD_NOT_READABLE');
            return 2;
        }

        return 0;
    }

    private function loadCorrectionLifecycle($correctionId)
    {
        try {
            $lifecycle = app(EodEvidenceRepository::class)->findCorrectionById($correctionId);
            if ($lifecycle) {
                return $lifecycle;
            }
        } catch (\Throwable $e) {
            // Unit tests and dry command surfaces may not have a DB-backed evidence repository.
        }

        return app(EodCorrectionRepository::class)->findById($correctionId);
    }

    private function resolveCorrectionOutcome($correction)
    {
        if (! $correction || ! isset($correction->status)) {
            return '';
        }

        $status = strtoupper((string) $correction->status);
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') {
            return 'UNCHANGED';
        }

        if ($status === 'PUBLISHED') {
            return 'PUBLISHED';
        }

        if ($status === 'RESEALED' || $status === 'REPAIR_EXECUTED') {
            return 'RESEALED';
        }

        return (string) $correction->status;
    }

    private function resolveResealStatus($correction)
    {
        if (! $correction || ! isset($correction->status)) {
            return '';
        }

        $status = strtoupper((string) $correction->status);
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') {
            return 'NOT_RESEALED_UNCHANGED';
        }

        if (in_array($status, ['PUBLISHED', 'RESEALED', 'REPAIR_EXECUTED'], true)) {
            return 'RESEALED';
        }

        if ($status === 'FAILED') {
            return 'NOT_RESEALED';
        }

        return '';
    }

    private function markCorrectionFailed($correctionId, $originalCorrection, \Throwable $e)
    {
        $repo = app(EodCorrectionRepository::class);
        $current = $repo->findById($correctionId) ?: $originalCorrection;
        $reasonCode = $this->reasonCodeFromThrowable($e);
        $note = 'Correction execution failed before safe publication; baseline current pointer preserved. failure_reason_code='.$reasonCode;

        return $repo->markFailed(
            $correctionId,
            $this->optionalInt($current, 'new_run_id'),
            $this->optionalInt($current, 'prior_run_id'),
            $note,
            $this->optionalInt($current, 'baseline_publication_id') ?: $this->optionalInt($originalCorrection, 'baseline_publication_id'),
            null
        );
    }

    private function reasonCodeFromThrowable(\Throwable $e)
    {
        if (method_exists($e, 'reasonCode')) {
            $reasonCode = $e->reasonCode();
            if ($reasonCode !== null && $reasonCode !== '') {
                return (string) $reasonCode;
            }
        }

        return 'CORRECTION_FAILED';
    }

    private function optionalScalar($record, $field)
    {
        return is_object($record) && isset($record->{$field}) && $record->{$field} !== null
            ? (string) $record->{$field}
            : '';
    }

    private function optionalInt($record, $field)
    {
        return is_object($record) && isset($record->{$field}) && $record->{$field} !== ''
            ? (int) $record->{$field}
            : null;
    }

    private function optionalBoolString($record, $field)
    {
        if (! is_object($record) || ! isset($record->{$field}) || $record->{$field} === null) {
            return '';
        }

        return $record->{$field} ? 'true' : 'false';
    }

    private function resolveCandidatePublicationSwitch($correction, $run)
    {
        if ($this->resolveCorrectionOutcome($correction) === 'UNCHANGED'
            || (string) $this->runField($run, 'terminal_status') !== 'SUCCESS'
            || (string) $this->runField($run, 'publishability_state') !== 'READABLE') {
            return 'false';
        }

        return $this->optionalBoolString($correction, 'new_publication_is_current');
    }
}
