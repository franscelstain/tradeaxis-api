<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;

class RunCorrectionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:correction:run {correction_id} {--requested_date=} {--source_mode=} {--latest}';

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
        $run = $this->pipeline()->runDaily($requestedDate, $this->sourceMode(), $correctionId);

        $this->renderRunSummary($run);
        $correctionLifecycle = $this->loadCorrectionLifecycle($correctionId);
        $this->line('correction_id='.$correctionId);
        $this->line('correction_status='.(string) optional($correctionLifecycle)->status);
        $this->line('correction_outcome='.$this->resolveCorrectionOutcome($correctionLifecycle));
        $this->line('correction_reseal_status='.$this->resolveResealStatus($correctionLifecycle));
        $baselinePublicationId = $this->optionalScalar($correctionLifecycle, 'baseline_publication_id') ?: $this->optionalScalar($correctionLifecycle, 'prior_publication_id');
        $candidatePublicationId = $this->optionalScalar($correctionLifecycle, 'replacement_publication_id') ?: $this->optionalScalar($correctionLifecycle, 'new_publication_id');
        $this->line('baseline_publication_id='.$baselinePublicationId);
        $this->line('candidate_publication_id='.$candidatePublicationId);
        $this->line('candidate_publication_switch='.$this->optionalBoolString($correctionLifecycle, 'new_publication_is_current'));
        $this->line('final_outcome_note='.$this->optionalScalar($correctionLifecycle, 'final_outcome_note'));

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

        return '';
    }

    private function optionalScalar($record, $field)
    {
        return is_object($record) && property_exists($record, $field) && $record->{$field} !== null
            ? (string) $record->{$field}
            : '';
    }

    private function optionalBoolString($record, $field)
    {
        if (! is_object($record) || ! property_exists($record, $field) || $record->{$field} === null) {
            return '';
        }

        return $record->{$field} ? 'true' : 'false';
    }
}
