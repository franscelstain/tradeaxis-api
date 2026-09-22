<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;

/**
 * C1 §6 step 2 — Binding.
 *
 * Locks the candidate publication and its owning run, verifies ownership/cutoff/config
 * consistency, validates the complete captured-input manifest, canonicalizes the producer-bound
 * V2 input context from the immutable captures already on `md_run_input_captures`, persists it on
 * the existing `md_publication_lineage_bindings` row, and independently re-derives the existing V1
 * compatibility component hashes from that same bound content -- comparing them rather than
 * maintaining a second competing computation. This never re-queries a mutable calendar/registry
 * root for the earlier stages; every derived value comes from what was actually captured.
 */
class PublicationInputBindingService
{
    const SCHEMA_VERSION = 'md_publication_inputs_v2';

    private $captures;
    private $manifest;

    public function __construct(RunInputCaptureRepository $captures = null, ProducerInputCompletionManifest $manifest = null)
    {
        $this->captures = $captures ?: new RunInputCaptureRepository();
        $this->manifest = $manifest ?: new ProducerInputCompletionManifest();
    }

    public function bind($run, $publicationId, $tradeDate): array
    {
        if ((string) ($run->request_mode ?? '') === 'replay_verify') {
            throw new \RuntimeException('INPUT_CAPTURE_BINDING_HISTORICAL_NOT_PERMITTED: a historical run cannot mint a new authoritative bound input context.');
        }
        $knownAt = (string) ($run->knowledge_cutoff_at ?? '');
        if ($knownAt === '') {
            throw new \RuntimeException('INPUT_CAPTURE_BINDING_KNOWLEDGE_CUTOFF_MISSING');
        }
        if (empty($run->config_snapshot_id)) {
            throw new \RuntimeException('INPUT_CAPTURE_BINDING_CONFIG_SNAPSHOT_MISSING');
        }

        return DB::transaction(function () use ($run, $publicationId, $tradeDate, $knownAt) {
            $publication = DB::table('eod_publications')->where('publication_id', (int) $publicationId)->lockForUpdate()->first();
            if (! $publication) throw new \RuntimeException('INPUT_CAPTURE_BINDING_PUBLICATION_NOT_FOUND');
            if ((int) $publication->run_id !== (int) $run->run_id) throw new \RuntimeException('INPUT_CAPTURE_BINDING_OWNERSHIP_MISMATCH');
            if ((string) $publication->trade_date !== (string) $tradeDate) throw new \RuntimeException('INPUT_CAPTURE_BINDING_TRADE_DATE_MISMATCH');

            $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', (int) $publicationId)->lockForUpdate()->first();
            if (! $lineage) throw new \RuntimeException('INPUT_CAPTURE_BINDING_LINEAGE_ROW_MISSING: V1 governance binding must run before V2 binding.');
            if ((int) $lineage->config_snapshot_id !== (int) $run->config_snapshot_id) throw new \RuntimeException('INPUT_CAPTURE_BINDING_CONFIG_SNAPSHOT_INCONSISTENT');

            $manifestResult = $this->manifest->inspect($run, $this->captures);
            if ($manifestResult['missing_paths'] !== []) {
                throw new \RuntimeException('INPUT_CAPTURE_BINDING_MANIFEST_INCOMPLETE: '.implode(',', $manifestResult['missing_paths']));
            }

            $rawCaptures = $this->captures->forRun((int) $run->run_id);
            $parsed = [];
            foreach ($rawCaptures as $row) {
                $parsed[] = ['row' => $row, 'payload' => $this->captures->verify($row)];
            }

            $components = [];
            foreach ($parsed as $p) {
                $operation = $p['payload']['selection_context']['operation'] ?? null;
                if ($operation === 'input-completion-manifest/v1') continue;
                $components[] = [
                    'stage_code' => $p['row']['stage_code'], 'component_key' => $p['row']['component_key'],
                    'slot_hash' => $p['row']['slot_hash'], 'payload_hash' => $p['row']['payload_hash'],
                    'member_count' => (int) $p['row']['member_count'], 'operation' => (string) $operation,
                    // Captured directly by this run, not inherited -- explicit even in the common
                    // case so a reader never has to infer ownership from absence of the field.
                    'source_run_id' => (int) $run->run_id,
                ];
            }

            // F-MD-B18-A002-020: components the manifest proved satisfied by immutable reference to
            // a seed run (provider_mapping/source_observations for a derived promote run only) are
            // included by reference, never by copying the seed run's rows into this run's own
            // md_run_input_captures -- their payload_hash/slot_hash identify the exact immutable
            // capture, and source_run_id names the run that actually produced it, so this bundle
            // can never be read as if this run captured them itself.
            $referencedBySourceRun = [];
            foreach (($manifestResult['referenced_components'] ?? []) as $slot) {
                $referencedBySourceRun[(int) $slot['source_run_id']][] = $slot;
            }
            foreach ($referencedBySourceRun as $sourceRunId => $slots) {
                $sourceRows = [];
                foreach ($this->captures->forRun($sourceRunId) as $sourceRow) {
                    $sourceRows[$sourceRow['stage_code'].'|'.$sourceRow['component_key'].'|'.$sourceRow['slot_hash']] = $sourceRow;
                }
                foreach ($slots as $slot) {
                    $key = $slot['stage_code'].'|'.$slot['component_key'].'|'.$slot['slot_hash'];
                    $sourceRow = $sourceRows[$key] ?? null;
                    if ($sourceRow === null || $sourceRow['payload_hash'] !== $slot['payload_hash']) {
                        // The seed capture named by the manifest can no longer be found or verified
                        // byte-for-byte against the exact same immutable row: the reference is not
                        // provable right now, so it cannot be bound as if it were -- fail closed
                        // rather than bind an unverified inheritance.
                        throw new \RuntimeException('INPUT_CAPTURE_BINDING_REFERENCED_CAPTURE_UNVERIFIABLE: '.$key.'@run:'.$sourceRunId);
                    }
                    $sourceOperation = $this->captures->verify($sourceRow)['selection_context']['operation'] ?? null;
                    $components[] = [
                        'stage_code' => $sourceRow['stage_code'], 'component_key' => $sourceRow['component_key'],
                        'slot_hash' => $sourceRow['slot_hash'], 'payload_hash' => $sourceRow['payload_hash'],
                        'member_count' => (int) $sourceRow['member_count'], 'operation' => (string) $sourceOperation,
                        'source_run_id' => $sourceRunId,
                    ];
                }
            }

            usort($components, static function ($a, $b) {
                return [$a['stage_code'], $a['component_key'], $a['slot_hash']] <=> [$b['stage_code'], $b['component_key'], $b['slot_hash']];
            });

            $bundle = [
                'schema_version' => self::SCHEMA_VERSION,
                'scope' => [
                    'market_code' => (string) config('market_data.scope.market_code', 'IDX'),
                    'market_segment' => (string) config('market_data.scope.market_segment', 'REGULAR'),
                    'requested_trade_date' => (string) $run->trade_date_requested,
                    'effective_trade_date' => (string) $tradeDate,
                    'dataset_start' => (string) config('market_data.scope.dataset_start'),
                    'knowledge_cutoff_at' => $knownAt,
                    'source' => (string) $run->source,
                    'request_mode' => (string) ($run->request_mode ?? ''),
                    'config_snapshot_id' => (int) $run->config_snapshot_id,
                ],
                'components' => $components,
                'component_manifest' => [
                    'required_operations' => $manifestResult['required_operations'],
                    'status' => $manifestResult['status'],
                    'actual_slot_count' => count($components),
                ],
            ];
            $boundInputContextJson = RunInputCaptureRepository::canonicalJson($bundle);
            $boundInputContextHash = hash('sha256', $boundInputContextJson);

            $derived = $this->deriveCompatibilityHashes($parsed, $tradeDate, $knownAt);
            $expected = [
                'identity_revision_set_hash' => $lineage->identity_revision_set_hash,
                'calendar_revision_set_hash' => $lineage->calendar_revision_set_hash,
                'status_revision_set_hash' => $lineage->status_revision_set_hash,
                'event_revision_set_hash' => $lineage->event_revision_set_hash,
                'source_scale_assessment_set_hash' => $lineage->source_scale_assessment_set_hash,
                'market_structure_revision_set_hash' => $lineage->market_structure_revision_set_hash,
                'factor_decision_set_hash' => $lineage->factor_decision_set_hash,
            ];
            $mismatches = [];
            foreach ($expected as $field => $existingValue) {
                if ($existingValue === null) continue; // nullable compatibility fields with no captured basis are not compared
                if (! array_key_exists($field, $derived)) continue; // not independently re-derived in this work unit
                if ((string) $existingValue !== (string) $derived[$field]) $mismatches[] = $field;
            }
            if ($mismatches !== []) {
                throw new \RuntimeException('INPUT_CAPTURE_BINDING_COMPATIBILITY_HASH_MISMATCH: '.implode(',', $mismatches));
            }

            $manifestPayload = [
                'required_slots' => $manifestResult['required_operations'],
                'actual_components' => array_map(static function ($c) { return $c['stage_code'].'|'.$c['component_key'].'|'.$c['slot_hash']; }, $components),
                'derived_compatibility_hashes' => $derived,
                'derivation_scope' => ['not_independently_rederived' => array_values(array_diff(array_keys($expected), array_keys($derived)))],
            ];
            $boundInputCaptureManifestJson = RunInputCaptureRepository::canonicalJson($manifestPayload);

            if ((string) ($publication->seal_state ?? '') === 'SEALED') {
                if ((string) ($lineage->bound_input_context_hash ?? '') === $boundInputContextHash) {
                    return $this->result($boundInputContextHash, $derived, true);
                }
                throw new \RuntimeException('INPUT_CAPTURE_BINDING_SEALED_PUBLICATION_IMMUTABLE');
            }

            if ($lineage->bound_input_context_hash !== null) {
                if ((string) $lineage->bound_input_context_hash === $boundInputContextHash) {
                    return $this->result($boundInputContextHash, $derived, true);
                }
                throw new \RuntimeException('INPUT_CAPTURE_BINDING_CONFLICT: a different bound input context is already recorded for this publication.');
            }

            DB::table('md_publication_lineage_bindings')->where('publication_id', (int) $publicationId)->update([
                'bound_input_schema_version' => self::SCHEMA_VERSION,
                'bound_input_context_json' => $boundInputContextJson,
                'bound_input_context_hash' => $boundInputContextHash,
                'bound_input_capture_manifest_json' => $boundInputCaptureManifestJson,
            ]);

            return $this->result($boundInputContextHash, $derived, false);
        });
    }

    private function result(string $hash, array $derived, bool $idempotent): array
    {
        return ['bound_input_context_hash' => $hash, 'derived_compatibility_hashes' => $derived, 'idempotent' => $idempotent];
    }

    /**
     * C1 §6 step 3 — Seal. Independently re-verifies the already-persisted V2 bound input context
     * is still authentic and complete before a publication may be sealed. Read-only: this never
     * writes to `md_publication_lineage_bindings` and never calls `bind()` -- Seal verifies an
     * existing Binding, it does not create or recompute one. Every check reads only the immutable
     * `md_run_input_captures` rows and the already-persisted bound context itself; nothing here
     * reads a mutable "current" table to stand in for historical evidence. Throws on the first
     * failure; the caller (`EodPublicationRepository::sealCandidatePublication`) fails the seal
     * closed exactly like its other preconditions.
     */
    public function verifyBeforeSeal($run, $publicationId, $tradeDate): void
    {
        if ((string) ($run->request_mode ?? '') === 'replay_verify') {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_HISTORICAL_NOT_PERMITTED: a historical run cannot seal a new authoritative publication.');
        }

        $publication = DB::table('eod_publications')->where('publication_id', (int) $publicationId)->first();
        if (! $publication) throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_PUBLICATION_NOT_FOUND');
        if ((int) $publication->run_id !== (int) $run->run_id) throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_OWNERSHIP_MISMATCH');
        if ((string) $publication->trade_date !== (string) $tradeDate) throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_TRADE_DATE_MISMATCH');

        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', (int) $publicationId)->first();

        $this->verifyBoundContext($run, $lineage);
    }

    /**
     * C1 §6 step 4 -- Reader. Read-only, version-aware projection of a publication's already-bound
     * input context, by explicit publication_id only -- it never consults a current/latest pointer
     * to stand in for the identity requested. Reuses exactly the same verification clause Seal
     * performs (`verifyBoundContext` below), so Reader can never diverge from Seal on what "valid"
     * means; it never writes, creates or repairs a Binding. A publication with no V2 bound context
     * at all (pre-C1 / V1 legacy) is reported as `V1_LEGACY_NO_V2_BOUND_CONTEXT`, not silently
     * treated as verified and not treated as an error -- V1 ordinary reads are unaffected by this
     * method; only exact verification requires V2 evidence, which is why a missing bound context is
     * a distinct classification from a present-but-invalid one (`BLOCKED`, with the exact reason).
     */
    public function readBoundContext($publicationId): array
    {
        $publication = DB::table('eod_publications')->where('publication_id', (int) $publicationId)->first();
        if (! $publication) {
            return ['available' => false, 'status' => 'BLOCKED', 'schema_version' => null, 'reason' => 'INPUT_CAPTURE_READ_PUBLICATION_NOT_FOUND', 'bound_input_context_hash' => null];
        }

        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', (int) $publicationId)->first();
        if (! $lineage || $lineage->bound_input_context_hash === null || $lineage->bound_input_context_json === null) {
            return ['available' => false, 'status' => 'V1_LEGACY_NO_V2_BOUND_CONTEXT', 'schema_version' => null, 'reason' => null, 'bound_input_context_hash' => null];
        }

        $run = DB::table('eod_runs')->where('run_id', (int) $publication->run_id)->first();
        if (! $run) {
            return ['available' => false, 'status' => 'BLOCKED', 'schema_version' => (string) $lineage->bound_input_schema_version, 'reason' => 'INPUT_CAPTURE_READ_OWNING_RUN_NOT_FOUND', 'bound_input_context_hash' => (string) $lineage->bound_input_context_hash];
        }

        try {
            $bundle = $this->verifyBoundContext($run, $lineage);
        } catch (\RuntimeException $e) {
            return ['available' => false, 'status' => 'BLOCKED', 'schema_version' => (string) $lineage->bound_input_schema_version, 'reason' => $e->getMessage(), 'bound_input_context_hash' => (string) $lineage->bound_input_context_hash];
        }

        return [
            'available' => true,
            'status' => 'VERIFIED',
            'schema_version' => (string) $lineage->bound_input_schema_version,
            'reason' => null,
            'bound_input_context_hash' => (string) $lineage->bound_input_context_hash,
            'scope' => $bundle['scope'],
            'components' => $bundle['components'],
            'component_manifest' => $bundle['component_manifest'],
            // F-MD-B18-A002-021 (item 5, partial): the registry_versions component's own decoded
            // content (serialization_version, executable_build.build_id, ...), already re-verified
            // against its immutable capture above -- not just its opaque combined payload_hash.
            'registry_content' => $bundle['_registry_versions_content'] ?? null,
        ];
    }

    /**
     * Shared by Seal (`verifyBeforeSeal`, which fails closed on any exception) and Reader
     * (`readBoundContext`, which catches and classifies). Verifies the bound context already
     * persisted on `$lineage` is internally self-consistent and that every listed component still
     * traces to its exact immutable capture -- never creates, recomputes or repairs the binding.
     * Returns the decoded bundle on success.
     */
    private function verifyBoundContext($run, $lineage): array
    {
        if (! $lineage || $lineage->bound_input_context_hash === null || $lineage->bound_input_context_json === null) {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_BINDING_MISSING: no bound input context exists to verify.');
        }
        if ((int) $lineage->config_snapshot_id !== (int) $run->config_snapshot_id) {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_CONFIG_SNAPSHOT_INCONSISTENT');
        }

        // The persisted bound context bytes must still hash to the persisted digest -- proves the
        // stored JSON itself has not been altered independently of its hash.
        if (! hash_equals((string) $lineage->bound_input_context_hash, hash('sha256', (string) $lineage->bound_input_context_json))) {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_DIGEST_MISMATCH: persisted bound_input_context_hash does not match the persisted bound_input_context_json.');
        }

        $bundle = json_decode((string) $lineage->bound_input_context_json, true);
        if (! is_array($bundle) || ($bundle['schema_version'] ?? null) !== self::SCHEMA_VERSION
            || ! isset($bundle['components']) || ! is_array($bundle['components']) || ! isset($bundle['scope']) || ! is_array($bundle['scope'])
            || ! isset($bundle['component_manifest']) || ! is_array($bundle['component_manifest'])) {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_BUNDLE_UNREADABLE: canonical bound context could not be read back.');
        }
        if ((int) ($bundle['scope']['config_snapshot_id'] ?? -1) !== (int) $run->config_snapshot_id) {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_CONFIG_SNAPSHOT_INCONSISTENT');
        }

        // Binding is the authoritative source of whole-manifest completeness (it refuses to
        // persist a bound context at all when incomplete); Seal trusts that already-proven,
        // already-immutable verdict rather than re-deriving it from scratch against potentially
        // different data, and instead verifies the bound context itself was not corrupted since --
        // every listed component below is individually re-verified against its immutable source.
        if (($bundle['component_manifest']['status'] ?? null) !== 'COMPLETE') {
            throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_MANIFEST_INCOMPLETE: bound component_manifest.status is not COMPLETE.');
        }

        $seedRunId = $this->captures->resolveSeedRunId((int) $run->run_id);
        $sourceCaptureCache = [];
        $registryVersionsContent = null;
        foreach ($bundle['components'] as $component) {
            foreach (['stage_code', 'component_key', 'slot_hash', 'payload_hash', 'source_run_id'] as $field) {
                if (! array_key_exists($field, $component)) {
                    throw new \RuntimeException('INPUT_CAPTURE_SEAL_VERIFICATION_COMPONENT_MALFORMED: missing '.$field);
                }
            }
            $sourceRunId = (int) $component['source_run_id'];

            // A component this run did not capture itself must trace to exactly this run's own
            // recorded seed -- never an arbitrary or stale other run, and never satisfied merely
            // because *some* source_run_id value is present.
            if ($sourceRunId !== (int) $run->run_id && ($seedRunId === null || $sourceRunId !== $seedRunId)) {
                throw new \RuntimeException(
                    'INPUT_CAPTURE_SEAL_VERIFICATION_PROVENANCE_INVALID: '.$component['stage_code'].'|'.$component['component_key'].'|'.$component['slot_hash']
                );
            }

            if (! array_key_exists($sourceRunId, $sourceCaptureCache)) {
                $rows = [];
                foreach ($this->captures->forRun($sourceRunId) as $row) {
                    $rows[$row['stage_code'].'|'.$row['component_key'].'|'.$row['slot_hash']] = $row;
                }
                $sourceCaptureCache[$sourceRunId] = $rows;
            }
            $key = $component['stage_code'].'|'.$component['component_key'].'|'.$component['slot_hash'];
            $actualRow = $sourceCaptureCache[$sourceRunId][$key] ?? null;
            if ($actualRow === null || ! hash_equals((string) $actualRow['payload_hash'], (string) $component['payload_hash'])) {
                throw new \RuntimeException(
                    'INPUT_CAPTURE_SEAL_VERIFICATION_COMPONENT_UNVERIFIABLE: '.$key.'@run:'.$sourceRunId
                );
            }

            // F-MD-B18-A002-021 (item 5, partial): `registry_versions` is the one component whose
            // raw content Reader/Admission need precisely, not only as one opaque combined
            // payload_hash -- `serialization_version` and `executable_build.build_id` are real,
            // already-captured fields (confirmed by reading ProducerRegistrySnapshot::capture()),
            // just never decoded past this point before. Decoding here, once, after the exact same
            // payload_hash check every other component already passed, adds no new trust: it reads
            // only content already proven immutable and unaltered.
            if ($registryVersionsContent === null && $component['component_key'] === 'registry_versions') {
                $decoded = $this->captures->verify($actualRow);
                $registryVersionsContent = $decoded['rows'][0] ?? [];
            }
        }

        $bundle['_registry_versions_content'] = $registryVersionsContent;

        return $bundle;
    }

    /** Re-derive the existing V1 compatibility component hashes from the immutable captured content only. */
    private function deriveCompatibilityHashes(array $parsed, string $tradeDate, string $knownAt): array
    {
        $derived = [];

        $marketStructure = null;
        foreach ($parsed as $p) {
            if (($p['payload']['selection_context']['operation'] ?? null) === 'market-structure-consumed-inputs/v1') { $marketStructure = $p['payload']['rows'][0]; break; }
        }
        if ($marketStructure !== null) {
            $bindings = $this->deriveMarketStructureBindings($marketStructure, $tradeDate, $knownAt);
            $derived['market_structure_revision_set_hash'] = $this->hashRows('market-structure-resolution-set/v1', $bindings);
            $derived['identity_revision_set_hash'] = $this->hashRows('identity-board-resolution-set/v1', array_map(static function ($b) {
                return ['listing_id' => $b['listing_id'], 'normalized_board_code' => $b['normalized_board_code'],
                    'board_identity_recorded_at' => $b['board_identity_recorded_at'], 'resolution_state' => $b['resolution_state']];
            }, $bindings));
        }

        $calendarRows = [];
        foreach ($parsed as $p) {
            if ($p['row']['component_key'] !== 'calendar_session') continue;
            foreach ($p['payload']['rows'] as $r) {
                if (($r['cal_date'] ?? null) === $tradeDate) $calendarRows[(int) $r['calendar_revision_id']] = $r;
            }
        }
        if ($calendarRows !== []) {
            $rows = array_values($calendarRows);
            $active = array_values(array_filter($rows, static function ($r) use ($rows, $knownAt) {
                if ((string) $r['recorded_at'] > $knownAt) return false;
                foreach ($rows as $n) if ((string) ($n['supersedes_revision_id'] ?? '') === (string) $r['calendar_revision_id'] && (string) $n['recorded_at'] <= $knownAt) return false;
                return true;
            }));
            usort($active, static function ($a, $b) { return $a['calendar_revision_id'] <=> $b['calendar_revision_id']; });
            $derived['calendar_revision_set_hash'] = $this->hashRows('calendar-revision-set/v1', array_map(static function ($r) {
                return ['calendar_revision_id' => (int) $r['calendar_revision_id'], 'revision_uid' => (string) $r['revision_uid'], 'session_state' => (string) $r['session_state']];
            }, $active));
        }

        $statusRows = [];
        foreach ($parsed as $p) {
            if (($p['payload']['selection_context']['operation'] ?? null) !== 'eligibility-expectation/v1') continue;
            $listingId = (int) ($p['payload']['selection_context']['listing_id'] ?? 0);
            if ($listingId <= 0) continue;
            $expectation = $p['payload']['rows'][0];
            $revisionIds = $expectation['trading_status_revision_ids'] ?? [];
            $observationIds = $expectation['trading_status_source_observation_ids'] ?? [];
            $statusRows[$listingId] = [
                'listing_id' => $listingId,
                'bar_expectation_state' => (string) $expectation['bar_expectation_state'],
                'temporal_status_state' => (string) ($expectation['trading_status_code'] ?? 'UNKNOWN'),
                'trading_status_revision_id' => count($revisionIds) === 1 ? (int) $revisionIds[0] : null,
                'trading_status_source_observation_id' => count($observationIds) === 1 ? (int) $observationIds[0] : null,
            ];
        }
        if ($statusRows !== []) {
            ksort($statusRows, SORT_NUMERIC);
            $derived['status_revision_set_hash'] = $this->hashRows('status-resolution-set/v1', array_values($statusRows));
        }

        $factorDecisions = null;
        foreach ($parsed as $p) {
            if (($p['payload']['selection_context']['operation'] ?? null) === 'event-factor-revisions/v1') { $factorDecisions = $p['payload']['rows'][0]['tables']['md_adjustment_factor_decisions'] ?? []; break; }
        }
        if ($factorDecisions !== null) {
            $decisions = $factorDecisions;
            usort($decisions, static function ($a, $b) { return (int) $a['corporate_action_revision_id'] <=> (int) $b['corporate_action_revision_id']; });
            $derived['event_revision_set_hash'] = $this->hashRows('event-revision-set/v1', array_map(static function ($d) { return (int) $d['corporate_action_revision_id']; }, $decisions));
            $derived['source_scale_assessment_set_hash'] = $this->hashRows('source-scale-assessment-set/v1', array_map(static function ($d) {
                return ['corporate_action_revision_id' => (int) $d['corporate_action_revision_id'],
                    'source_scale_assessment_id' => $d['source_scale_assessment_id'] === null ? null : (int) $d['source_scale_assessment_id'],
                    'decision_state' => (string) $d['decision_state']];
            }, $decisions));
            $derived['factor_decision_set_hash'] = $this->hashRows('factor-decision-set/v1', array_map(static function ($d) {
                return ['corporate_action_revision_id' => (int) $d['corporate_action_revision_id'], 'decision_state' => (string) $d['decision_state'],
                    'candidate_price_factor' => $d['candidate_price_factor'] === null ? null : (string) $d['candidate_price_factor'],
                    'candidate_volume_factor' => $d['candidate_volume_factor'] === null ? null : (string) $d['candidate_volume_factor'],
                    'reason_code' => (string) $d['reason_code']];
            }, $decisions));
        }

        return $derived;
    }

    /** Faithful port of PublicationGovernanceBindingService::bindMarketStructure's resolution, over captured arrays. */
    private function deriveMarketStructureBindings(array $marketStructure, string $tradeDate, string $knownAt): array
    {
        $revisions = $marketStructure['rule_revisions'] ?? [];
        $canonical = [];
        foreach ($marketStructure['board_inputs'] ?? [] as $row) {
            $board = $this->normalizeBoard($row['board_code'] ?? null);
            $recordedDate = ! empty($row['board_identity_recorded_at']) ? substr((string) $row['board_identity_recorded_at'], 0, 10) : null;
            $bandId = null; $floorId = null; $tickId = null;
            if ($board === null) { $state = 'FAIL_CLOSED_BOARD_UNKNOWN'; $reason = 'MARKET_STRUCTURE_BOARD_UNKNOWN'; }
            elseif ($recordedDate === null || $recordedDate > $tradeDate || ((string) $row['board_identity_recorded_at'] > $knownAt)) {
                $state = 'FAIL_CLOSED_BOARD_NOT_POINT_IN_TIME'; $reason = 'MARKET_STRUCTURE_BOARD_NOT_POINT_IN_TIME';
            } elseif (in_array($board, ['ACCELERATION', 'SPECIAL_MONITORING'], true)) { $state = 'FAIL_CLOSED_NON_STANDARD_BOARD'; $reason = 'MARKET_STRUCTURE_SCOPE_EXCLUDED'; }
            elseif (! in_array($board, ['MAIN', 'DEVELOPMENT', 'NEW_ECONOMY'], true)) { $state = 'FAIL_CLOSED_BOARD_UNRECOGNIZED'; $reason = 'MARKET_STRUCTURE_BOARD_UNRECOGNIZED'; }
            elseif (! isset($revisions['PRICE_BAND'], $revisions['MINIMUM_PRICE'], $revisions['TICK_SIZE'])) { $state = 'FAIL_CLOSED_REVISION_MISSING'; $reason = 'MARKET_STRUCTURE_REVISION_MISSING'; }
            else {
                $state = 'RESOLVED_STANDARD_BOARD'; $reason = null;
                $bandId = (int) $revisions['PRICE_BAND']['market_structure_revision_id'];
                $floorId = (int) $revisions['MINIMUM_PRICE']['market_structure_revision_id'];
                $tickId = (int) $revisions['TICK_SIZE']['market_structure_revision_id'];
            }
            $canonical[] = ['listing_id' => (int) $row['listing_id'], 'resolution_state' => $state, 'normalized_board_code' => $board,
                'board_identity_recorded_at' => $row['board_identity_recorded_at'], 'price_band_revision_id' => $bandId,
                'minimum_price_revision_id' => $floorId, 'tick_size_revision_id' => $tickId, 'reason_code' => $reason];
        }
        return $canonical;
    }

    private function normalizeBoard($board)
    {
        $board = strtoupper(trim((string) $board));
        $aliases = ['MB' => 'MAIN', 'DB' => 'DEVELOPMENT', 'DEVELOPMEN' => 'DEVELOPMENT', 'ACCELERATI' => 'ACCELERATION', 'WATCHLIST' => 'SPECIAL_MONITORING'];
        $board = $aliases[$board] ?? $board;
        return $board === '' ? null : $board;
    }

    private function hashRows($schemaVersion, array $rows): string
    {
        return hash('sha256', json_encode(['schema_version' => $schemaVersion, 'rows' => array_values($rows)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
