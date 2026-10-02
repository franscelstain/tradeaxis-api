<?php

namespace App\Application\MarketData\Services;

use Illuminate\Support\Facades\DB;

class PublicationDiffService
{
    private $hashFields = [
        'bars_batch_hash' => 'bars',
        'indicators_batch_hash' => 'indicators',
        'eligibility_batch_hash' => 'eligibility',
    ];

    /**
     * V2 output-affecting bindings no artifact hash carries. Historical_Correction_and_Reseal_Contract_LOCKED.md:46
     * requires the comparison to cover every protected field of the seal contract
     * (Dataset_Seal_and_Freeze_Contract_LOCKED.md:47), and the calendar revision set is protected
     * content that no V2 artifact row binds. Keyed by the lineage column, valued by changed scope.
     */
    private $v2BindingFields = [
        'semantic_calendar_revision_set_hash' => 'calendar',
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

        $priorProfile = $this->artifactProfile($priorCurrent);
        $candidateProfile = $this->artifactProfile($candidatePublication);
        if ($priorProfile !== $candidateProfile) {
            return [
                'decision' => 'INVALID',
                'changed_scope' => [],
                'changed_fields' => [],
                // The governed HASH_INCOMPLETE code already means deterministic comparison cannot
                // be established. Mixed serializer profiles are exactly that condition.
                'reason_code' => 'CORRECTION_ARTIFACT_HASH_INCOMPLETE',
                'hash_context' => $this->hashContext($priorCurrent, $candidatePublication),
            ];
        }

        $bindings = $priorProfile === ArtifactSemanticHashService::PROFILE_V2
            ? $this->v2Bindings($priorCurrent, $candidatePublication)
            : ['prior' => [], 'candidate' => []];
        $missing = $this->missingMandatoryHashes($priorCurrent, $candidatePublication, $bindings);
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
        foreach ($this->v2BindingFields as $field => $scope) {
            if ($priorProfile === ArtifactSemanticHashService::PROFILE_V2
                && (string) $bindings['prior'][$field] !== (string) $bindings['candidate'][$field]
            ) {
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

    /**
     * Resolve the V2 bindings of both publications. The value comes from the record when the
     * caller already carries it, otherwise from the publication's own lineage row. A binding that
     * cannot be resolved stays null and makes the comparison INVALID, never UNCHANGED.
     */
    private function v2Bindings($priorCurrent, $candidatePublication): array
    {
        $resolved = ['prior' => [], 'candidate' => []];
        foreach (['prior' => $priorCurrent, 'candidate' => $candidatePublication] as $side => $record) {
            foreach (array_keys($this->v2BindingFields) as $field) {
                $resolved[$side][$field] = $this->bindingValue($record, $field);
            }
        }

        return $resolved;
    }

    private function bindingValue($record, string $field): ?string
    {
        if (is_object($record) && property_exists($record, $field)) {
            $value = $record->{$field};

            return $value === null || (string) $value === '' ? null : strtolower((string) $value);
        }
        $publicationId = $this->optionalInt($record, 'publication_id');
        if ($publicationId === null) {
            return null;
        }
        $value = DB::table('md_publication_lineage_bindings')
            ->where('publication_id', $publicationId)
            ->value($field);

        return $value === null || (string) $value === '' ? null : strtolower((string) $value);
    }

    private function missingMandatoryHashes($priorCurrent, $candidatePublication, array $bindings = ['prior' => [], 'candidate' => []])
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
        foreach ($bindings as $side => $values) {
            foreach ($values as $field => $value) {
                if ($value === null) {
                    $missing[] = $side.'.'.$field;
                }
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
            'artifact_hash_profile' => [
                'prior' => $this->artifactProfile($priorCurrent),
                'candidate' => $this->artifactProfile($candidatePublication),
            ],
        ];

        foreach (array_keys($this->hashFields) as $field) {
            $context['hashes'][$field] = [
                'prior' => is_object($priorCurrent) && property_exists($priorCurrent, $field) ? $priorCurrent->{$field} : null,
                'candidate' => is_object($candidatePublication) && property_exists($candidatePublication, $field) ? $candidatePublication->{$field} : null,
            ];
        }

        if ($context['artifact_hash_profile']['prior'] === ArtifactSemanticHashService::PROFILE_V2
            && $context['artifact_hash_profile']['candidate'] === ArtifactSemanticHashService::PROFILE_V2
        ) {
            $bindings = $this->v2Bindings($priorCurrent, $candidatePublication);
            $context['bindings'] = [];
            foreach (array_keys($this->v2BindingFields) as $field) {
                $context['bindings'][$field] = [
                    'prior' => $bindings['prior'][$field],
                    'candidate' => $bindings['candidate'][$field],
                ];
            }
        }

        return $context;
    }

    private function artifactProfile($record): string
    {
        $profile = is_object($record) && property_exists($record, 'artifact_hash_profile')
            ? trim((string) $record->artifact_hash_profile)
            : '';
        if ($profile === '') return ArtifactSemanticHashService::LEGACY_PROFILE_V1;
        if (!in_array($profile, [ArtifactSemanticHashService::LEGACY_PROFILE_V1, ArtifactSemanticHashService::PROFILE_V2], true)) {
            return 'UNSUPPORTED:'.$profile;
        }
        return $profile;
    }

    private function optionalInt($record, $field)
    {
        return is_object($record) && property_exists($record, $field) && $record->{$field} !== null
            ? (int) $record->{$field}
            : null;
    }
}
