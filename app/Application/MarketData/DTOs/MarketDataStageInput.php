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

    public function __construct($requestedDate, $sourceMode, $runId, $stage, $correctionId = null, $forceReplace = false, $forceReplaceReason = null)
    {
        $this->requestedDate = $requestedDate;
        $this->sourceMode = $sourceMode;
        $this->runId = $runId;
        $this->stage = $stage;
        $this->correctionId = $correctionId;
        $this->forceReplace = (bool) $forceReplace;
        $this->forceReplaceReason = $forceReplaceReason;
    }
}
