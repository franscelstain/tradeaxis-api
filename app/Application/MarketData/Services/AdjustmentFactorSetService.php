<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Domain\MarketData\MarketDataSemanticBindings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;

/**
 * Builds the immutable factor decision set consumed by one analytical publication.
 *
 * A verified event is never silently treated as an active factor. Its source scale must be
 * assessed first; UNKNOWN is persisted and held, while PROVIDER_BACK_ADJUSTED is explicitly held
 * to prevent double adjustment.
 */
class AdjustmentFactorSetService
{
    const ASSESSMENT_VERSION = 'yahoo_source_scale_v1';
    const FACTOR_FORMULA_VERSION = MarketDataSemanticBindings::FACTOR_FORMULA_VERSION;

    private $artifacts;

    public function __construct(EodArtifactRepository $artifacts = null)
    {
        $this->artifacts = $artifacts ?: new EodArtifactRepository();
    }

    public function ensureForPublication($run, $publicationId, $requestedDate, array $barsByTicker): array
    {
        $events = $this->authoritativeEventsThrough($requestedDate, $run->started_at ?? $run->created_at ?? null);
        $decisions = [];

        foreach ($events as $event) {
            $terms = json_decode((string) $event->terms_json, true);
            $ratioFrom = (float) ($terms['ratio']['from'] ?? 0);
            $ratioTo = (float) ($terms['ratio']['to'] ?? 0);
            if ($ratioFrom <= 0 || $ratioTo <= 0) {
                throw new \RuntimeException('AUTHORITATIVE_FACTOR_TERMS_INCOMPLETE: ratio is missing for revision '.(int) $event->corporate_action_revision_id.'.');
            }

            $assessment = $this->latestAssessment((int) $event->corporate_action_revision_id, 'YAHOO_FINANCE');
            if (! $assessment) {
                $assessment = $this->recordUnknownAssessment($event, $barsByTicker[(int) $event->legacy_ticker_id] ?? []);
            }

            $sourceScaleState = (string) $assessment->source_scale_state;
            if ($sourceScaleState === 'AS_TRADED') {
                $decisionState = 'APPLIED';
                $reasonCode = 'FACTOR_APPLIED_SOURCE_AS_TRADED';
            } elseif ($sourceScaleState === 'PROVIDER_BACK_ADJUSTED') {
                $decisionState = 'HELD_PROVIDER_BACK_ADJUSTED';
                $reasonCode = 'FACTOR_HELD_PROVIDER_BACK_ADJUSTED';
            } else {
                $decisionState = 'HELD_SOURCE_SCALE_UNKNOWN';
                $reasonCode = 'FACTOR_HELD_SOURCE_SCALE_UNKNOWN';
            }

            $decisions[] = [
                'listing_id' => (int) $event->listing_id,
                'ticker_id' => (int) $event->legacy_ticker_id,
                'corporate_action_revision_id' => (int) $event->corporate_action_revision_id,
                'source_scale_assessment_id' => (int) $assessment->source_scale_assessment_id,
                'source_scale_state' => $sourceScaleState,
                'decision_state' => $decisionState,
                'reason_code' => $reasonCode,
                'ex_date' => (string) $event->ex_date,
                'price_factor' => $ratioFrom / $ratioTo,
                'volume_factor' => $ratioTo / $ratioFrom,
            ];
        }

        $payload = $this->canonicalPayload($run, $requestedDate, $decisions);
        $contentHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $factorSet = DB::table('md_adjustment_factor_sets')->where('factor_set_uid', $contentHash)->first();

        if (! $factorSet) {
            $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
            $factorSetId = DB::transaction(function () use ($run, $contentHash, $decisions, $now) {
                $existing = DB::table('md_adjustment_factor_sets')->where('factor_set_uid', $contentHash)->lockForUpdate()->first();
                if ($existing) {
                    return (int) $existing->factor_set_id;
                }

                $factorSetId = DB::table('md_adjustment_factor_sets')->insertGetId([
                    'factor_set_uid' => $contentHash,
                    'price_product_code' => MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT,
                    'factor_formula_version' => self::FACTOR_FORMULA_VERSION,
                    'config_snapshot_id' => (int) $run->config_snapshot_id,
                    'state' => 'BOUND',
                    'content_hash' => $contentHash,
                    'recorded_at' => $now,
                    'created_at' => $now,
                ]);

                foreach ($decisions as $decision) {
                    DB::table('md_adjustment_factor_decisions')->insert([
                        'factor_set_id' => $factorSetId,
                        'listing_id' => $decision['listing_id'],
                        'corporate_action_revision_id' => $decision['corporate_action_revision_id'],
                        'source_scale_assessment_id' => $decision['source_scale_assessment_id'],
                        'decision_state' => $decision['decision_state'],
                        'candidate_price_factor' => $this->decimal($decision['price_factor']),
                        'candidate_volume_factor' => $this->decimal($decision['volume_factor']),
                        'reason_code' => $decision['reason_code'],
                        'created_at' => $now,
                    ]);

                    if ($decision['decision_state'] === 'APPLIED') {
                        DB::table('md_adjustment_factors')->insert([
                            'factor_set_id' => $factorSetId,
                            'listing_id' => $decision['listing_id'],
                            'effective_from' => MarketDataScope::DATASET_START,
                            'effective_to' => Carbon::parse($decision['ex_date'])->subDay()->toDateString(),
                            'price_factor' => $this->decimal($decision['price_factor']),
                            'volume_factor' => $this->decimal($decision['volume_factor']),
                            'corporate_action_revision_id' => $decision['corporate_action_revision_id'],
                            'created_at' => $now,
                        ]);
                    }
                }

                return (int) $factorSetId;
            });

            $factorSet = DB::table('md_adjustment_factor_sets')->where('factor_set_id', $factorSetId)->first();
        }

        $this->bindScaleStateToCandidateBars((int) $publicationId, (string) $requestedDate, $decisions);

        $factorsByTicker = [];
        $heldByTicker = [];
        foreach ($decisions as $decision) {
            if ($decision['decision_state'] === 'APPLIED') {
                $factorsByTicker[$decision['ticker_id']][] = [
                    'listing_id' => $decision['listing_id'],
                    'factor_revision_ref' => 'md-corporate-action-revision:'.$decision['corporate_action_revision_id'],
                    'ex_date' => $decision['ex_date'],
                    'price_factor' => $decision['price_factor'],
                    'volume_factor' => $decision['volume_factor'],
                ];
            } else {
                $heldByTicker[$decision['ticker_id']][] = [
                    'action_type_code' => 'STOCK_SPLIT',
                    'action_date' => $decision['ex_date'],
                    'breaks_price_continuity' => true,
                    'breaks_volume_continuity' => true,
                    'is_unmapped_type' => false,
                    'factor_hold_reason_code' => $decision['reason_code'],
                ];
            }
        }

        ksort($factorsByTicker);
        ksort($heldByTicker);

        return [
            'factor_set_id' => (int) $factorSet->factor_set_id,
            'factor_set_hash' => (string) $factorSet->content_hash,
            'factors_by_ticker' => $factorsByTicker,
            'held_events_by_ticker' => $heldByTicker,
            'decisions' => $decisions,
        ];
    }

    private function authoritativeEventsThrough($requestedDate, $knownAt)
    {
        $query = DB::table('md_corporate_action_revisions as revision')
            ->join('md_listings as listing', 'listing.listing_id', '=', 'revision.listing_id')
            ->where('revision.verification_state', 'AUTHORITATIVE_VERIFIED')
            ->where('revision.lifecycle_state', 'EFFECTIVE')
            ->where('revision.action_type_code', 'STOCK_SPLIT')
            ->whereNotNull('revision.ex_date')
            ->where('revision.ex_date', '<=', $requestedDate)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('md_corporate_action_revisions as newer')
                    ->whereColumn('newer.supersedes_revision_id', 'revision.corporate_action_revision_id');
            })
            ->select('revision.*', 'listing.legacy_ticker_id')
            ->orderBy('revision.ex_date')
            ->orderBy('revision.corporate_action_revision_id');

        if ($knownAt !== null && $knownAt !== '') {
            $query->where('revision.recorded_at', '<=', $knownAt);
        }

        return $query->get();
    }

    private function latestAssessment($corporateActionRevisionId, $provider)
    {
        return DB::table('md_source_scale_assessments')
            ->where('corporate_action_revision_id', $corporateActionRevisionId)
            ->where('provider', $provider)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('md_source_scale_assessments as newer')
                    ->whereColumn('newer.supersedes_assessment_id', 'md_source_scale_assessments.source_scale_assessment_id');
            })
            ->orderByDesc('revision_number')
            ->first();
    }

    private function recordUnknownAssessment($event, array $bars)
    {
        $observationIds = [];
        foreach ($bars as $bar) {
            if (! empty($bar['source_observation_id'])) {
                $observationIds[] = (int) $bar['source_observation_id'];
            }
        }
        $observationIds = array_values(array_unique($observationIds));
        sort($observationIds, SORT_NUMERIC);
        $evidenceHash = hash('sha256', json_encode($observationIds));
        $evidence = [
            'schema_version' => 'source-scale-assessment-evidence/v1',
            'provider' => 'YAHOO_FINANCE',
            'corporate_action_revision_id' => (int) $event->corporate_action_revision_id,
            'observation_ids' => $observationIds,
            'classification' => 'UNKNOWN',
            'reason_code' => 'SOURCE_SCALE_MARKET_STRUCTURE_UNRESOLVED',
            'capability_boundary' => 'No point-in-time standard-board revision was admissible for a price-scale decision; no factor may be activated.',
        ];
        $uid = hash('sha256', json_encode([
            'provider' => 'YAHOO_FINANCE',
            'event_revision_id' => (int) $event->corporate_action_revision_id,
            'state' => 'UNKNOWN',
            'evidence_hash' => $evidenceHash,
            'assessment_version' => self::ASSESSMENT_VERSION,
        ], JSON_UNESCAPED_SLASHES));

        $existing = DB::table('md_source_scale_assessments')->where('assessment_uid', $uid)->first();
        if ($existing) {
            return $existing;
        }

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $id = DB::table('md_source_scale_assessments')->insertGetId([
            'assessment_uid' => $uid,
            'revision_number' => 1,
            'provider' => 'YAHOO_FINANCE',
            'listing_id' => (int) $event->listing_id,
            'corporate_action_revision_id' => (int) $event->corporate_action_revision_id,
            'source_scale_state' => 'UNKNOWN',
            'scale_effective_from' => null,
            'assessment_version' => self::ASSESSMENT_VERSION,
            'evidence_observation_set_hash' => $evidenceHash,
            'evidence_json' => json_encode($evidence, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'recorded_at' => $now,
            'supersedes_assessment_id' => null,
            'created_at' => $now,
        ]);

        return DB::table('md_source_scale_assessments')->where('source_scale_assessment_id', $id)->first();
    }

    private function bindScaleStateToCandidateBars($publicationId, $requestedDate, array $decisions): void
    {
        foreach ($decisions as $decision) {
            $payload = [
                'source_scale_state' => $decision['source_scale_state'],
                'source_scale_assessment_id' => $decision['source_scale_assessment_id'],
            ];

            $this->artifacts->bindCandidateBarSourceScale(
                $publicationId,
                $requestedDate,
                $decision['listing_id'],
                $payload
            );
        }
    }

    private function canonicalPayload($run, $requestedDate, array $decisions): array
    {
        $canonical = array_map(function ($decision) {
            return [
                'listing_id' => $decision['listing_id'],
                'corporate_action_revision_id' => $decision['corporate_action_revision_id'],
                'source_scale_assessment_id' => $decision['source_scale_assessment_id'],
                'source_scale_state' => $decision['source_scale_state'],
                'decision_state' => $decision['decision_state'],
                'reason_code' => $decision['reason_code'],
                'ex_date' => $decision['ex_date'],
                'candidate_price_factor' => $this->decimal($decision['price_factor']),
                'candidate_volume_factor' => $this->decimal($decision['volume_factor']),
            ];
        }, $decisions);

        return [
            'schema_version' => 'adjustment-factor-decision-set/v1',
            'price_product_code' => MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT,
            'factor_formula_version' => self::FACTOR_FORMULA_VERSION,
            'config_snapshot_id' => (int) $run->config_snapshot_id,
            'window_start' => MarketDataScope::DATASET_START,
            'window_end' => (string) $requestedDate,
            'decisions' => $canonical,
        ];
    }

    private function decimal($value): string
    {
        return number_format((float) $value, 12, '.', '');
    }
}
