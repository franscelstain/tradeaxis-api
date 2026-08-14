<?php

namespace App\Application\MarketData\DTOs;

class MarketDataStageInput
{
    public $requestedDate;
    public $sourceMode;
    public $runId;
    public $stage;
    public $correctionId;
    public $forceReplace;
    public $forceReplaceReason;
    public $requestMode;

    /**
     * The request modes a run may carry.
     *
     * This was previously a private static property that nothing read, while
     * MarketDataPipelineService held its own inline copy of the same list. The copy here is the
     * canonical one and the pipeline guard now reads it, so the vocabulary is defined once.
     */
    const ALLOWED_REQUEST_MODES = [
        'import_only',
        'promote',
        'full_publish',
        'correction',
        'repair_candidate',
        'corpus_reconstruction',
        'replay_verify',
        'evidence_export',
    ];

    public function __construct($requestedDate, $sourceMode, $runId, $stage, $correctionId = null, $forceReplace = false, $forceReplaceReason = null, $requestMode = null)
    {
        $this->requestedDate = $requestedDate;
        $this->sourceMode = $sourceMode;
        $this->runId = $runId;
        $this->stage = $stage;
        $this->correctionId = $correctionId;
        $this->forceReplace = (bool) $forceReplace;
        $this->forceReplaceReason = $forceReplaceReason;
        $this->requestMode = $this->normalizeRequestMode($requestMode, $stage, $correctionId);
    }

    private function normalizeRequestMode($requestMode, $stage, $correctionId)
    {
        if ($requestMode !== null && trim((string) $requestMode) !== '') {
            return (string) $requestMode;
        }

        if ($stage === 'INGEST_BARS') {
            return 'import_only';
        }

        if ($correctionId !== null) {
            return 'correction';
        }

        return 'full_publish';
    }
}
