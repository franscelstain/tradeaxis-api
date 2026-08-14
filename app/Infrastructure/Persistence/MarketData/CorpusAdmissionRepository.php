<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CorpusAdmissionRepository
{
    public function activeDecision()
    {
        if (! Schema::hasTable('md_corpus_admission_decisions')) {
            return null;
        }

        $rows = DB::table('md_corpus_admission_decisions')
            ->where('state', 'ACTIVE')
            ->orderByDesc('admission_decision_id')
            ->get();
        if ($rows->count() > 1) {
            throw new \RuntimeException('STAGE8_ADMISSION_STATE_AMBIGUOUS: more than one corpus admission decision is active.');
        }

        return $rows->first();
    }

    public function decisionForTradeDate($tradeDate)
    {
        $decision = $this->activeDecision();

        return $decision && (string) $tradeDate >= (string) $decision->admitted_from
            ? $decision
            : null;
    }

    public function historyStartDateFor($tradeDate, $knownAt = null): ?string
    {
        $decision = $this->decisionForTradeDate($tradeDate);
        if (! $decision) {
            return null;
        }
        if ($knownAt !== null && $knownAt !== '' && (string) $decision->recorded_at > (string) $knownAt) {
            return null;
        }

        return (string) $decision->admitted_from;
    }

    public function isAdmitted($tradeDate, $decisionId = null): bool
    {
        $decision = $this->decisionForTradeDate($tradeDate);
        if (! $decision) {
            return false;
        }

        return $decisionId === null || (int) $decision->admission_decision_id === (int) $decisionId;
    }
}
