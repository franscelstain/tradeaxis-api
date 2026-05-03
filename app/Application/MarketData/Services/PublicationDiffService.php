<?php

namespace App\Application\MarketData\Services;

class PublicationDiffService
{
    private $hashFields = [
        'bars_batch_hash' => 'bars',
        'indicators_batch_hash' => 'indicators',
        'eligibility_batch_hash' => 'eligibility',
    ];

    public function isUnchanged($priorCurrent, $candidatePublication)
    {
        $comparison = $this->compare($priorCurrent, $candidatePublication);

        return $comparison['decision'] === 'UNCHANGED';
    }

    public function compare($priorCurrent, $candidatePublication)
    {
        if (! $priorCurrent || ! $candidatePublication) {
            return [
                'decision' => 'INVALID',
                'changed_scope' => [],
                'changed_fields' => [],
                'reason_code' => 'CORRECTION_ARTIFACT_BASELINE_OR_CANDIDATE_MISSING',
                'hash_context' => $this->hashContext($priorCurrent, $candidatePublication),
            ];
        }

        $missing = $this->missingMandatoryHashes($priorCurrent, $candidatePublication);
        if (! empty($missing)) {
            return [
                'decision' => 'INVALID',
                'changed_scope' => [],
                'changed_fields' => [],
                'reason_code' => 'CORRECTION_ARTIFACT_HASH_INCOMPLETE',
                'missing_fields' => $missing,
                'hash_context' => $this->hashContext($priorCurrent, $candidatePublication),
            ];
        }

        $changedFields = [];
        $changedScope = [];
        foreach ($this->hashFields as $field => $scope) {
            if ((string) $priorCurrent->{$field} !== (string) $candidatePublication->{$field}) {
                $changedFields[] = $field;
                $changedScope[] = $scope;
            }
        }

        if (empty($changedFields)) {
            return [
                'decision' => 'UNCHANGED',
                'changed_scope' => [],
                'changed_fields' => [],
                'reason_code' => 'CORRECTION_ARTIFACT_UNCHANGED',
                'hash_context' => $this->hashContext($priorCurrent, $candidatePublication),
            ];
        }

        return [
            'decision' => 'CHANGED',
            'changed_scope' => $changedScope,
            'changed_fields' => $changedFields,
            'reason_code' => 'CORRECTION_ARTIFACT_CHANGED',
            'hash_context' => $this->hashContext($priorCurrent, $candidatePublication),
        ];
    }

    private function missingMandatoryHashes($priorCurrent, $candidatePublication)
    {
        $missing = [];
        foreach (array_keys($this->hashFields) as $field) {
            if (! $this->hasNonEmptyField($priorCurrent, $field)) {
                $missing[] = 'prior.'.$field;
            }

            if (! $this->hasNonEmptyField($candidatePublication, $field)) {
                $missing[] = 'candidate.'.$field;
            }
        }

        return $missing;
    }

    private function hasNonEmptyField($record, $field)
    {
        return is_object($record)
            && property_exists($record, $field)
            && $record->{$field} !== null
            && (string) $record->{$field} !== '';
    }

    private function hashContext($priorCurrent, $candidatePublication)
    {
        $context = [
            'prior_publication_id' => $this->optionalInt($priorCurrent, 'publication_id'),
            'prior_publication_version' => $this->optionalInt($priorCurrent, 'publication_version'),
            'prior_run_id' => $this->optionalInt($priorCurrent, 'run_id'),
            'candidate_publication_id' => $this->optionalInt($candidatePublication, 'publication_id'),
            'candidate_publication_version' => $this->optionalInt($candidatePublication, 'publication_version'),
            'candidate_run_id' => $this->optionalInt($candidatePublication, 'run_id'),
            'hashes' => [],
        ];

        foreach (array_keys($this->hashFields) as $field) {
            $context['hashes'][$field] = [
                'prior' => is_object($priorCurrent) && property_exists($priorCurrent, $field) ? $priorCurrent->{$field} : null,
                'candidate' => is_object($candidatePublication) && property_exists($candidatePublication, $field) ? $candidatePublication->{$field} : null,
            ];
        }

        return $context;
    }

    private function optionalInt($record, $field)
    {
        return is_object($record) && property_exists($record, $field) && $record->{$field} !== null
            ? (int) $record->{$field}
            : null;
    }
}
