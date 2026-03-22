<?php

namespace App\Application\MarketData\Services;

class PublicationDiffService
{
    public function isUnchanged($priorCurrent, $candidatePublication)
    {
        if (! $priorCurrent || ! $candidatePublication) {
            return false;
        }

        return (string) $priorCurrent->bars_batch_hash === (string) $candidatePublication->bars_batch_hash
            && (string) $priorCurrent->indicators_batch_hash === (string) $candidatePublication->indicators_batch_hash
            && (string) $priorCurrent->eligibility_batch_hash === (string) $candidatePublication->eligibility_batch_hash;
    }
}
