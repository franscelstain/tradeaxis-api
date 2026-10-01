<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * V2 nested semantic identities of one candidate publication (D-MD-B10-A002-003).
 *
 * The V1 nested hashes serialize allocated listing, revision, observation and assessment keys and
 * remain untouched for their existing consumers. This service derives the V2 counterparts from the
 * same persisted governance state:
 *
 * - per-listing sets (identity board, trading status, market structure) carry the retained
 *   foundation roots resolved exactly as V2 artifact rows resolve them;
 * - source-fact revisions (calendar, trading status, market-structure rules, corporate-action
 *   events, board identity) carry their stable identity, content, effective time and their
 *   governed knowledge time (decision 1B);
 * - platform-created records (source-scale assessments, factor decisions, factor sets) carry
 *   content only; their `recorded_at`/`created_at` is provenance and visibility time and never
 *   enters the identity (decision 1A);
 * - corporate-action events are identified by `event_uid` (content-addressed from KSEI, ISIN and
 *   document number) plus `revision_number`.
 *
 * Every set is a canonical document whose lists are sorted by the canonical hasher, so neither
 * insertion order nor any local key can reach an identity.
 */
class SemanticNestedIdentityService
{
    public const VERSION = 'market-data-semantic-nested/v2';

    /** Semantic member name => md_publication_lineage_bindings column. */
    public const LINEAGE_COLUMNS = [
        'observation_manifest_hash' => 'semantic_observation_manifest_hash',
        'identity_revision_set_hash' => 'semantic_identity_revision_set_hash',
        'calendar_revision_set_hash' => 'semantic_calendar_revision_set_hash',
        'status_revision_set_hash' => 'semantic_status_revision_set_hash',
        'event_revision_set_hash' => 'semantic_event_revision_set_hash',
        'source_scale_assessment_set_hash' => 'semantic_source_scale_assessment_set_hash',
        'market_structure_revision_set_hash' => 'semantic_market_structure_revision_set_hash',
        'factor_decision_set_hash' => 'semantic_factor_decision_set_hash',
        'factor_set_hash' => 'semantic_factor_set_hash',
    ];

    private const LOCAL_KEY_NAMES = ['observation_uid', 'assessment_uid', 'status_event_uid', 'attempt_uid'];

    private DeterministicHashService $hashes;
    private ArtifactSemanticHashService $artifactHashes;
    private EodArtifactRepository $artifacts;
    private PublicationGovernanceBindingService $governance;
    private SemanticObservationIdentityService $observations;

    private array $eventKeys = [];
    private array $eventTuples = [];
    private array $assessmentHashes = [];
    private array $statusHashes = [];
    private array $payloadHashes = [];

    public function __construct(
        ?DeterministicHashService $hashes = null,
        ?ArtifactSemanticHashService $artifactHashes = null,
        ?EodArtifactRepository $artifacts = null,
        ?PublicationGovernanceBindingService $governance = null,
        ?SemanticObservationIdentityService $observations = null
    ) {
        $this->hashes = $hashes ?: new DeterministicHashService();
        $this->artifactHashes = $artifactHashes ?: app(ArtifactSemanticHashService::class);
        $this->artifacts = $artifacts ?: new EodArtifactRepository();
        $this->governance = $governance ?: new PublicationGovernanceBindingService($this->artifacts);
        $this->observations = $observations ?: new SemanticObservationIdentityService($this->hashes);
    }

    /**
     * Compute and persist the V2 nested identities of a candidate. V1 governance binding must have
     * run first: it owns the lineage row and the market-structure bindings this reads. Before seal
     * the identities follow the candidate's current governance state, as the V1 columns do; once
     * sealed they can only be confirmed, never changed.
     */
    public function bind($run, $publication, string $tradeDate, bool $useHistory): array
    {
        $publicationId = (int) $this->value($publication, 'publication_id');
        $current = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        if (! $current) {
            throw new \RuntimeException('SEMANTIC_NESTED_PUBLICATION_MISSING: '.$publicationId);
        }
        if ((int) $current->run_id !== (int) $this->value($run, 'run_id')) {
            throw new \RuntimeException('SEMANTIC_NESTED_OWNERSHIP_MISMATCH: publication '.$publicationId);
        }
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->first();
        if (! $lineage) {
            throw new \RuntimeException('SEMANTIC_NESTED_LINEAGE_MISSING: governance binding must precede nested identity.');
        }

        $computed = $this->compute($run, $current, $tradeDate, $useHistory);
        $payload = ['semantic_nested_identity_version' => self::VERSION];
        foreach (self::LINEAGE_COLUMNS as $member => $column) {
            $payload[$column] = $computed[$member];
        }

        $unchanged = true;
        foreach ($payload as $column => $value) {
            if ((string) ($lineage->{$column} ?? '') !== (string) $value) {
                $unchanged = false;
            }
        }
        if ((string) ($current->seal_state ?? '') === 'SEALED') {
            if (! $unchanged) {
                throw new \RuntimeException('SEMANTIC_NESTED_SEALED_PUBLICATION_IMMUTABLE: publication '.$publicationId);
            }

            return $computed;
        }
        if (! $unchanged) {
            DB::table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->update($payload);
        }

        return $computed;
    }

    /** Read-only derivation of every V2 nested identity of one candidate publication. */
    public function compute($run, $publication, string $tradeDate, bool $useHistory): array
    {
        $this->eventKeys = $this->eventTuples = $this->assessmentHashes = $this->statusHashes = $this->payloadHashes = [];
        $publicationId = (int) $this->value($publication, 'publication_id');
        $cutoff = $this->knowledgeCutoff($run);

        $observationManifestHash = strtolower(trim((string) $this->value($publication, 'semantic_observation_manifest_hash')));
        if (preg_match('/^[a-f0-9]{64}$/', $observationManifestHash) !== 1) {
            throw new \RuntimeException('SEMANTIC_OBSERVATION_MANIFEST_MISSING: publication '.$publicationId);
        }

        $eligibility = $this->artifacts->loadCandidateEligibilityForGovernance($publicationId, $tradeDate);
        $bindings = DB::table('md_publication_market_structure_bindings')
            ->where('publication_id', $publicationId)
            ->get()
            ->all();
        $listingIds = array_merge(
            array_map(static function ($row) { return (int) $row->listing_id; }, $eligibility),
            array_map(static function ($row) { return (int) $row->listing_id; }, $bindings)
        );
        $roots = $this->artifactHashes->resolveListingRoots(
            $useHistory ? 'eod_bars_history' : 'eod_bars',
            $tradeDate,
            array_values(array_unique($listingIds)),
            $run,
            $useHistory ? ['publication_id' => $publicationId] : []
        );

        $identityRows = [];
        $marketStructureRows = [];
        foreach ($bindings as $binding) {
            $listing = $roots[(int) $binding->listing_id];
            $identityRows[] = [
                'listing' => $listing,
                'normalized_board_code' => $this->text($binding->normalized_board_code ?? null),
                'board_identity_recorded_at' => $this->timestamp($binding->board_identity_recorded_at ?? null),
                'resolution_state' => $this->text($binding->resolution_state ?? null),
            ];
            $marketStructureRows[] = [
                'listing' => $listing,
                'resolution_state' => $this->text($binding->resolution_state ?? null),
                'normalized_board_code' => $this->text($binding->normalized_board_code ?? null),
                'board_identity_recorded_at' => $this->timestamp($binding->board_identity_recorded_at ?? null),
                'reason_code' => $this->text($binding->reason_code ?? null),
                'price_band' => $this->marketStructureRule($binding->price_band_revision_id ?? null),
                'minimum_price' => $this->marketStructureRule($binding->minimum_price_revision_id ?? null),
                'tick_size' => $this->marketStructureRule($binding->tick_size_revision_id ?? null),
            ];
        }

        $statusRows = [];
        foreach ($eligibility as $row) {
            $statusRows[] = [
                'listing' => $roots[(int) $row->listing_id],
                'bar_expectation_state' => $this->text($row->bar_expectation_state ?? null),
                'temporal_status_state' => $this->text($row->temporal_status_state ?? null),
                'status_revision_hash' => $this->statusRevisionHash($row->trading_status_revision_id ?? null, (int) $row->listing_id),
                'status_source_payload_hash' => $this->observationPayloadHash($row->trading_status_source_observation_id ?? null),
            ];
        }

        $calendarRows = array_map(function ($row) {
            return $this->calendarRevision($row);
        }, $this->governance->calendarRevisionsForTradeDate($tradeDate, $cutoff));

        [$eventRows, $sourceScaleRows, $factorDecisionRows, $factorSet] = $this->factorContext($publication, $tradeDate);

        return [
            'observation_manifest_hash' => $observationManifestHash,
            'identity_revision_set_hash' => $this->setHash('identity-board-resolution-set/v2', $identityRows),
            'calendar_revision_set_hash' => $this->setHash('calendar-revision-set/v2', $calendarRows),
            'status_revision_set_hash' => $this->setHash('status-resolution-set/v2', $statusRows),
            'event_revision_set_hash' => $this->setHash('event-revision-set/v2', $eventRows),
            'source_scale_assessment_set_hash' => $this->setHash('source-scale-assessment-set/v2', $sourceScaleRows),
            'market_structure_revision_set_hash' => $this->setHash('market-structure-resolution-set/v2', $marketStructureRows),
            'factor_decision_set_hash' => $this->setHash('factor-decision-set/v2', $factorDecisionRows),
            'factor_set_hash' => $this->document('market-data-adjustment-factor-set/v2', $factorSet),
        ];
    }

    /**
     * Event, source-scale, factor-decision and factor-set content of the publication's factor set.
     * The persisted rows are first proved to be exactly the content the V1 factor-set hash binds.
     */
    private function factorContext($publication, string $tradeDate): array
    {
        $factorSetId = (int) $this->value($publication, 'factor_set_id');
        $factorSet = $factorSetId > 0
            ? DB::table('md_adjustment_factor_sets')->where('factor_set_id', $factorSetId)->first()
            : null;
        if (! $factorSet) {
            throw new \RuntimeException('SEMANTIC_FACTOR_SET_MISSING: publication '.(int) $this->value($publication, 'publication_id'));
        }
        $publicationFactorHash = strtolower(trim((string) $this->value($publication, 'factor_set_hash')));
        if (! hash_equals(strtolower((string) $factorSet->content_hash), $publicationFactorHash)) {
            throw new \RuntimeException('SEMANTIC_FACTOR_SET_BINDING_MISMATCH: publication and factor set differ.');
        }
        $config = DB::table('md_config_snapshots')->where('config_snapshot_id', (int) $factorSet->config_snapshot_id)->first();
        $configHash = strtolower(trim((string) ($config->config_hash ?? '')));
        if (preg_match('/^[a-f0-9]{64}$/', $configHash) !== 1) {
            throw new \RuntimeException('SEMANTIC_FACTOR_SET_CONFIG_CONTENT_MISSING: factor set config snapshot.');
        }

        $decisions = DB::table('md_adjustment_factor_decisions')->where('factor_set_id', $factorSetId)->get()->all();
        $this->assertLegacyFactorSetContent($factorSet, $decisions, $tradeDate);

        $eventRows = [];
        $sourceScaleRows = [];
        $factorDecisionRows = [];
        $decisionContent = [];
        foreach ($decisions as $decision) {
            $revisionId = (int) $decision->corporate_action_revision_id;
            $event = $this->eventKey($revisionId);
            $assessmentHash = $this->assessmentHash($decision->source_scale_assessment_id ?? null);
            $eventRows[] = $this->eventTuple($revisionId);
            $sourceScaleRows[] = [
                'event' => $event,
                'decision_state' => $this->text($decision->decision_state),
                'assessment_hash' => $assessmentHash,
            ];
            $factorDecisionRows[] = [
                'event' => $event,
                'decision_state' => $this->text($decision->decision_state),
                'candidate_price_factor' => $this->decimal12($decision->candidate_price_factor),
                'candidate_volume_factor' => $this->decimal12($decision->candidate_volume_factor),
                'reason_code' => $this->text($decision->reason_code),
                'assessment_hash' => $assessmentHash,
            ];
            $revision = $this->eventRevision($revisionId);
            $decisionContent[] = [
                'event' => $event,
                'source_payload_hash' => $this->observationPayloadHash($revision->source_observation_id ?? null),
                'assessment_hash' => $assessmentHash,
                'source_scale_state' => $this->assessmentState($decision->source_scale_assessment_id ?? null),
                'decision_state' => $this->text($decision->decision_state),
                'reason_code' => $this->text($decision->reason_code),
                'ex_date' => $this->date($revision->ex_date ?? null),
                'candidate_price_factor' => $this->decimal12($decision->candidate_price_factor),
                'candidate_volume_factor' => $this->decimal12($decision->candidate_volume_factor),
            ];
        }

        $factors = [];
        foreach (DB::table('md_adjustment_factors')->where('factor_set_id', $factorSetId)->get()->all() as $factor) {
            $factors[] = [
                'event' => $this->eventKey((int) $factor->corporate_action_revision_id),
                'effective_from' => $this->date($factor->effective_from ?? null),
                'effective_to' => $this->date($factor->effective_to ?? null),
                'price_factor' => $this->decimal12($factor->price_factor),
                'volume_factor' => $this->decimal12($factor->volume_factor),
            ];
        }

        return [$eventRows, $sourceScaleRows, $factorDecisionRows, [
            'price_product_code' => $this->text($factorSet->price_product_code),
            'factor_formula_version' => $this->text($factorSet->factor_formula_version),
            'state' => $this->text($factorSet->state),
            'config_content_hash' => $configHash,
            'window_start' => MarketDataScope::DATASET_START,
            'window_end' => $tradeDate,
            'decisions' => $decisionContent,
            'factors' => $factors,
        ]];
    }

    /**
     * The V1 factor-set identity is a SHA-256 over AdjustmentFactorSetService::canonicalPayload.
     * Rebuilding that payload from the persisted rows proves the V2 content below describes exactly
     * the decisions the V1 hash bound, and refuses rows changed after the set was recorded.
     */
    private function assertLegacyFactorSetContent($factorSet, array $decisions, string $tradeDate): void
    {
        $ordered = [];
        foreach ($decisions as $decision) {
            $revision = $this->eventRevision((int) $decision->corporate_action_revision_id);
            $observation = DB::table('md_source_observations')
                ->where('source_observation_id', (int) $revision->source_observation_id)
                ->first();
            $assessment = $decision->source_scale_assessment_id === null
                ? null
                : DB::table('md_source_scale_assessments')
                    ->where('source_scale_assessment_id', (int) $decision->source_scale_assessment_id)
                    ->first();
            $ordered[] = [
                'sort' => [(string) $revision->ex_date, (int) $decision->corporate_action_revision_id],
                'value' => [
                    'listing_id' => (int) $decision->listing_id,
                    'corporate_action_revision_id' => (int) $decision->corporate_action_revision_id,
                    'source_observation_id' => (int) $revision->source_observation_id,
                    'source_observation_hash' => strtolower(trim((string) ($observation->payload_hash ?? ''))),
                    'source_scale_assessment_id' => (int) $decision->source_scale_assessment_id,
                    'source_scale_state' => (string) ($assessment->source_scale_state ?? ''),
                    'decision_state' => (string) $decision->decision_state,
                    'reason_code' => (string) $decision->reason_code,
                    'ex_date' => (string) $revision->ex_date,
                    'candidate_price_factor' => $this->legacyDecimal($decision->candidate_price_factor),
                    'candidate_volume_factor' => $decision->candidate_volume_factor === null
                        ? null
                        : $this->legacyDecimal($decision->candidate_volume_factor),
                ],
            ];
        }
        usort($ordered, static function ($left, $right) {
            return strcmp($left['sort'][0], $right['sort'][0]) ?: ($left['sort'][1] <=> $right['sort'][1]);
        });

        $payload = [
            'schema_version' => 'adjustment-factor-decision-set/v2',
            'price_product_code' => (string) $factorSet->price_product_code,
            'factor_formula_version' => (string) $factorSet->factor_formula_version,
            'config_snapshot_id' => (int) $factorSet->config_snapshot_id,
            'window_start' => MarketDataScope::DATASET_START,
            'window_end' => $tradeDate,
            'decisions' => array_column($ordered, 'value'),
        ];
        $legacy = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if (! hash_equals(strtolower((string) $factorSet->content_hash), $legacy)) {
            throw new \RuntimeException('SEMANTIC_FACTOR_SET_V1_CONTENT_MISMATCH: persisted decisions differ from the bound factor set.');
        }
    }

    private function eventRevision(int $revisionId)
    {
        $revision = DB::table('md_corporate_action_revisions')->where('corporate_action_revision_id', $revisionId)->first();
        if (! $revision) {
            throw new \RuntimeException('SEMANTIC_EVENT_REVISION_MISSING: '.$revisionId);
        }

        return $revision;
    }

    /** Stable identity of one corporate-action event revision. */
    private function eventKey(int $revisionId): array
    {
        if (! isset($this->eventKeys[$revisionId])) {
            $revision = $this->eventRevision($revisionId);
            $eventUid = $this->text($revision->event_uid ?? null);
            if ($eventUid === null) {
                throw new \RuntimeException('SEMANTIC_EVENT_IDENTITY_MISSING: '.$revisionId);
            }
            $this->eventKeys[$revisionId] = [
                'event_uid' => $eventUid,
                'revision_number' => (int) $revision->revision_number,
            ];
        }

        return $this->eventKeys[$revisionId];
    }

    /** Full semantic revision tuple of one event, including its governed knowledge time (1B). */
    private function eventTuple(int $revisionId): array
    {
        if (! isset($this->eventTuples[$revisionId])) {
            $revision = $this->eventRevision($revisionId);
            $this->eventTuples[$revisionId] = $this->eventKey($revisionId) + [
                'action_type_code' => $this->text($revision->action_type_code ?? null),
                'lifecycle_state' => $this->text($revision->lifecycle_state ?? null),
                'verification_state' => $this->text($revision->verification_state ?? null),
                'ex_date' => $this->date($revision->ex_date ?? null),
                'cum_date' => $this->date($revision->cum_date ?? null),
                'record_date' => $this->date($revision->record_date ?? null),
                'payment_date' => $this->date($revision->payment_date ?? null),
                'terms_content_hash' => $this->jsonContentHash($revision->terms_json ?? null),
                'effective_at' => $this->timestamp($revision->effective_at ?? null),
                'recorded_at' => $this->timestamp($revision->recorded_at ?? null),
                'source_payload_hash' => $this->observationPayloadHash($revision->source_observation_id ?? null),
                'supersedes' => empty($revision->supersedes_revision_id)
                    ? null
                    : $this->eventKey((int) $revision->supersedes_revision_id),
            ];
        }

        return $this->eventTuples[$revisionId];
    }

    /**
     * Content identity of a platform-created source-scale assessment (decision 1A): its
     * `recorded_at`, `created_at`, `assessment_uid` and evidence-id hash are excluded.
     */
    private function assessmentHash($assessmentId, array $path = []): ?string
    {
        if ($assessmentId === null || $assessmentId === '') {
            return null;
        }
        $assessmentId = (int) $assessmentId;
        if (isset($this->assessmentHashes[$assessmentId])) {
            return $this->assessmentHashes[$assessmentId];
        }
        if (isset($path[$assessmentId])) {
            throw new \RuntimeException('SEMANTIC_ASSESSMENT_SUPERSESSION_CYCLE: '.$assessmentId);
        }
        $assessment = DB::table('md_source_scale_assessments')->where('source_scale_assessment_id', $assessmentId)->first();
        if (! $assessment) {
            throw new \RuntimeException('SEMANTIC_ASSESSMENT_MISSING: '.$assessmentId);
        }
        $evidence = json_decode((string) $assessment->evidence_json, true);
        if (! is_array($evidence)) {
            throw new \RuntimeException('SEMANTIC_ASSESSMENT_EVIDENCE_INVALID: '.$assessmentId);
        }
        $evidenceIds = array_values(array_filter(array_map('intval', (array) ($evidence['observation_ids'] ?? []))));
        $evidenceEntries = array_values($this->observations->entryHashes($evidenceIds));

        $this->assessmentHashes[$assessmentId] = $this->document('market-data-source-scale-assessment/v2', [
            'provider' => $this->text($assessment->provider ?? null),
            'event' => $this->eventKey((int) $assessment->corporate_action_revision_id),
            'source_scale_state' => $this->text($assessment->source_scale_state ?? null),
            'scale_effective_from' => $this->date($assessment->scale_effective_from ?? null),
            'assessment_version' => $this->text($assessment->assessment_version ?? null),
            'revision_number' => (int) $assessment->revision_number,
            'evidence_schema_version' => $this->text($evidence['schema_version'] ?? null),
            'evidence_classification' => $this->text($evidence['classification'] ?? null),
            'evidence_reason_code' => $this->text($evidence['reason_code'] ?? null),
            'evidence_observation_entries' => $evidenceEntries,
            'supersedes_assessment_hash' => $this->assessmentHash(
                $assessment->supersedes_assessment_id ?? null,
                $path + [$assessmentId => true]
            ),
        ]);

        return $this->assessmentHashes[$assessmentId];
    }

    private function assessmentState($assessmentId): ?string
    {
        if ($assessmentId === null || $assessmentId === '') {
            return null;
        }

        return $this->text(DB::table('md_source_scale_assessments')
            ->where('source_scale_assessment_id', (int) $assessmentId)
            ->value('source_scale_state'));
    }

    /** Trading-status revision tuple with its governed knowledge time (1B). */
    private function statusRevisionHash($revisionId, int $listingId, array $path = []): ?string
    {
        if ($revisionId === null || $revisionId === '') {
            return null;
        }
        $revisionId = (int) $revisionId;
        if (isset($this->statusHashes[$revisionId])) {
            return $this->statusHashes[$revisionId];
        }
        if (isset($path[$revisionId])) {
            throw new \RuntimeException('SEMANTIC_STATUS_SUPERSESSION_CYCLE: '.$revisionId);
        }
        $revision = DB::table('md_trading_status_revisions')->where('status_revision_id', $revisionId)->first();
        if (! $revision) {
            throw new \RuntimeException('SEMANTIC_STATUS_REVISION_MISSING: '.$revisionId);
        }
        if ((int) $revision->listing_id !== $listingId) {
            throw new \RuntimeException('SEMANTIC_STATUS_REVISION_LISTING_MISMATCH: '.$revisionId);
        }

        $this->statusHashes[$revisionId] = $this->document('market-data-trading-status-revision/v2', [
            'status_code' => $this->text($revision->status_code ?? null),
            'status_type_code' => $this->text($revision->status_type_code ?? null),
            'bar_expectation_state' => $this->text($revision->bar_expectation_state ?? null),
            'full_session_verified' => $revision->full_session_verified === null ? null : (int) $revision->full_session_verified,
            'effective_from' => $this->timestamp($revision->effective_from ?? null),
            'effective_to' => $this->timestamp($revision->effective_to ?? null),
            'board_code' => $this->text($revision->board_code ?? null),
            'authority_class' => $this->text($revision->authority_class ?? null),
            'verification_state' => $this->text($revision->verification_state ?? null),
            'source_name' => $this->text($revision->source_name ?? null),
            'source_ref' => $this->text($revision->source_ref ?? null),
            'authoritative_source_ref' => $this->text($revision->authoritative_source_ref ?? null),
            'source_payload_hash' => $this->lower($revision->source_payload_hash ?? null),
            'source_observation_payload_hash' => $this->observationPayloadHash($revision->source_observation_id ?? null),
            'announced_at' => $this->timestamp($revision->announced_at ?? null),
            'observed_at' => $this->timestamp($revision->observed_at ?? null),
            'governed_reason_code' => $this->text($revision->governed_reason_code ?? null),
            'recorded_at' => $this->timestamp($revision->recorded_at ?? null),
            'retracted_at' => $this->timestamp($revision->retracted_at ?? null),
            'supersedes_status_hash' => $this->statusRevisionHash(
                $revision->supersedes_revision_id ?? null,
                $listingId,
                $path + [$revisionId => true]
            ),
        ]);

        return $this->statusHashes[$revisionId];
    }

    /** Calendar revision tuple with its governed knowledge time (1B). */
    private function calendarRevision($revision): array
    {
        $superseded = empty($revision->supersedes_revision_id)
            ? null
            : DB::table('md_market_calendar_revisions')
                ->where('calendar_revision_id', (int) $revision->supersedes_revision_id)
                ->value('revision_uid');

        return [
            'revision_uid' => $this->text($revision->revision_uid ?? null),
            'market_code' => $this->text($revision->market_code ?? null),
            'market_segment' => $this->text($revision->market_segment ?? null),
            'cal_date' => $this->date($revision->cal_date ?? null),
            'timezone' => $this->text($revision->timezone ?? null),
            'session_state' => $this->text($revision->session_state ?? null),
            'is_trading_day' => $revision->is_trading_day === null ? null : (int) $revision->is_trading_day,
            'is_half_day' => $revision->is_half_day === null ? null : (int) $revision->is_half_day,
            'session_open_at' => $this->timestamp($revision->session_open_at ?? null),
            'session_close_at' => $this->timestamp($revision->session_close_at ?? null),
            'completed_at' => $this->timestamp($revision->completed_at ?? null),
            'source_ref' => $this->text($revision->source_ref ?? null),
            'source_version' => $this->text($revision->source_version ?? null),
            'provenance_tier' => $this->text($revision->provenance_tier ?? null),
            'source_payload_hash' => $this->observationPayloadHash($revision->source_observation_id ?? null),
            'recorded_at' => $this->timestamp($revision->recorded_at ?? null),
            'supersedes_revision_uid' => $this->text($superseded),
        ];
    }

    /** Market-structure rule revision tuple with its governed knowledge time (1B). */
    private function marketStructureRule($revisionId): ?array
    {
        if ($revisionId === null || $revisionId === '') {
            return null;
        }
        $revision = DB::table('md_exchange_market_structure_revisions')
            ->where('market_structure_revision_id', (int) $revisionId)
            ->first();
        if (! $revision) {
            throw new \RuntimeException('SEMANTIC_MARKET_STRUCTURE_REVISION_MISSING: '.(int) $revisionId);
        }
        $superseded = empty($revision->supersedes_revision_id)
            ? null
            : DB::table('md_exchange_market_structure_revisions')
                ->where('market_structure_revision_id', (int) $revision->supersedes_revision_id)
                ->first();

        return [
            'rule_uid' => $this->text($revision->rule_uid ?? null),
            'revision_number' => (int) $revision->revision_number,
            'rule_type' => $this->text($revision->rule_type ?? null),
            'exchange_code' => $this->text($revision->exchange_code ?? null),
            'market_segment' => $this->text($revision->market_segment ?? null),
            'instrument_scope_code' => $this->text($revision->instrument_scope_code ?? null),
            'effective_from' => $this->date($revision->effective_from ?? null),
            'effective_to' => $this->date($revision->effective_to ?? null),
            'verification_state' => $this->text($revision->verification_state ?? null),
            'source_uid' => $this->lower($revision->source_uid ?? null),
            'source_reference' => $this->text($revision->source_reference ?? null),
            'source_payload_hash' => $this->observationPayloadHash($revision->source_observation_id ?? null),
            'content_hash' => $this->lower($revision->content_hash ?? null),
            'recorded_at' => $this->timestamp($revision->recorded_at ?? null),
            'supersedes' => $superseded === null ? null : [
                'rule_uid' => $this->text($superseded->rule_uid ?? null),
                'revision_number' => (int) $superseded->revision_number,
            ],
        ];
    }

    private function observationPayloadHash($observationId): ?string
    {
        if ($observationId === null || $observationId === '' || (int) $observationId <= 0) {
            return null;
        }
        $observationId = (int) $observationId;
        if (! array_key_exists($observationId, $this->payloadHashes)) {
            $hash = strtolower(trim((string) DB::table('md_source_observations')
                ->where('source_observation_id', $observationId)
                ->value('payload_hash')));
            if (preg_match('/^[a-f0-9]{64}$/', $hash) !== 1) {
                throw new \RuntimeException('SEMANTIC_SOURCE_OBSERVATION_UNRESOLVED: '.$observationId);
            }
            $this->payloadHashes[$observationId] = $hash;
        }

        return $this->payloadHashes[$observationId];
    }

    private function setHash(string $schema, array $rows): string
    {
        return $this->document($schema, ['rows' => array_values($rows)]);
    }

    private function document(string $schema, array $content): string
    {
        $this->assertNoLocalKeys($content);

        return $this->hashes->hashCanonicalDocument(['schema_version' => $schema, 'content' => $content]);
    }

    private function assertNoLocalKeys(array $document, string $path = ''): void
    {
        foreach ($document as $key => $value) {
            $member = $path === '' ? (string) $key : $path.'.'.$key;
            if (is_string($key) && (preg_match('/(^|_)id$/', $key) === 1 || in_array($key, self::LOCAL_KEY_NAMES, true))) {
                throw new \RuntimeException('SEMANTIC_NESTED_LOCAL_KEY: '.$member);
            }
            if (is_array($value)) {
                $this->assertNoLocalKeys($value, $member);
            }
        }
    }

    private function jsonContentHash($json): ?string
    {
        if ($json === null || trim((string) $json) === '') {
            return null;
        }
        $decoded = json_decode((string) $json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('SEMANTIC_EVENT_TERMS_INVALID');
        }

        return hash('sha256', json_encode($this->sortKeys($decoded), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    private function sortKeys($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        foreach ($value as $key => $child) {
            $value[$key] = $this->sortKeys($child);
        }
        if (! $isList) {
            ksort($value, SORT_STRING);
        }

        return $value;
    }

    private function decimal12($value): ?string
    {
        return $value === null || $value === '' ? null : number_format((float) $value, 12, '.', '');
    }

    private function legacyDecimal($value): string
    {
        return number_format((float) $value, 12, '.', '');
    }

    private function knowledgeCutoff($run): string
    {
        $value = null;
        if (is_object($run) && method_exists($run, 'getRawOriginal')) {
            $value = $run->getRawOriginal('knowledge_cutoff_at');
        }
        if ($value === null || trim((string) $value) === '') {
            $value = $this->value($run, 'knowledge_cutoff_at');
        }
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }
        $value = trim((string) $value);
        if ($value === '') {
            throw new \RuntimeException('SEMANTIC_NESTED_KNOWLEDGE_CUTOFF_REQUIRED');
        }

        return $value;
    }

    private function text($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function lower($value): ?string
    {
        $value = $this->text($value);

        return $value === null ? null : strtolower($value);
    }

    private function date($value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        $value = $this->text($value);

        return $value === null ? null : substr($value, 0, 10);
    }

    private function timestamp($value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d H:i:s');
        }
        $value = $this->text($value);

        return $value === null ? null : substr($value, 0, 19);
    }

    private function value($source, string $field)
    {
        if (is_array($source)) {
            return $source[$field] ?? null;
        }
        if (is_object($source)) {
            return $source->{$field} ?? null;
        }

        return null;
    }
}
