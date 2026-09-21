<?php

namespace App\Infrastructure\Persistence\MarketData;

/** Independent C1 contract inventory. A populated capture table is not a completion declaration. */
final class ProducerInputCompletionManifest
{
    // Full-revision obligations deliberately remain distinct from the older materialized projections.
    public const REQUIRED_OPERATIONS = [
        'run_config' => ['owning-run-config/v1'],
        'universe_identity' => ['temporal-identity-revisions/v1'],
        'provider_mapping' => ['provider-mapping-revisions/v1', 'provider-source-row-link/v1'],
        'status_expectation' => ['status-authority-revisions/v1'],
        'source_observations' => ['source-observation-outcomes/v1'],
        'raw_history' => ['raw-input-lineage/v1'],
        'event_factor' => ['event-factor-revisions/v1'],
        'ancillary' => ['ancillary-source-revisions/v1'],
        'registry_versions' => ['producer-registry-build/v1'],
        'market_structure' => ['market-structure-consumed-inputs/v1'],
    ];

    /** Component domains a derived promote run may satisfy by immutable reference to its seed run. */
    private const SEED_REFERENCEABLE_PREFIXES = ['provider_mapping.', 'source_observations.'];

    public function inspect($run, RunInputCaptureRepository $repository, array $visitedRunIds = []): array
    {
        $missing = []; $operations = []; $slots = []; $dates = []; $requiredDates = [(string) $run->trade_date_requested => true];
        $calendarScopes = []; $calendarManifests = []; $registry = null;
        $inputScopes = []; $inputManifests = [];
        $mappingSelections = []; $sourceLinks = [];
        $captures = $repository->forRun((int) $run->run_id); $populations = [];
        foreach ($captures as $capture) {
            $p = $repository->verify($capture);
            if (in_array($p['selection_context']['operation'], ['temporal-revision-population/v1', 'status-revision-population/v1'], true)) {
                $populations[$capture['stage_code'].'|'.$capture['slot_hash']] = [$capture, $p];
            }
        }
        foreach ($captures as $row) {
            $payload = $repository->verify($row); $component = $row['component_key'];
            $selection = $payload['selection_context']; $operation = $selection['operation'];
            if ($operation === 'input-completion-manifest/v1') continue; // no self-reference/hash cycle
            $operations[$component][$operation] = true;
            if ($operation === 'source-observation-journal/v1') {
                try {
                    if (count($payload['rows']) !== 1) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_POPULATION');
                    ProducerSourceObservationJournal::assertValid($payload['rows'][0], (int) ($selection['source_observation_id'] ?? 0));
                } catch (\RuntimeException $error) {
                    $missing[] = 'source_observations.'.$row['slot_hash'].'.'.$error->getMessage();
                }
            }
            $slots[] = ['stage_code' => $row['stage_code'], 'component_key' => $component, 'slot_hash' => $row['slot_hash'],
                'input_capture_id' => (int) $row['input_capture_id'], 'payload_hash' => $row['payload_hash'], 'member_count' => (int) $row['member_count']];
            if ($component === 'raw_history') foreach ($payload['rows'] as $bar) {
                if (isset($bar['trade_date'])) $requiredDates[(string) $bar['trade_date']] = true;
            }
            if ($component === 'calendar_session') {
                $scope = $row['stage_code'].'|'.($selection['producer_operation'] ?? '');
                $calendarScopes[$scope][] = $row['slot_hash'];
                if (($selection['known_at'] ?? null) !== (string) $run->knowledge_cutoff_at) $missing[] = 'calendar_session.'.$row['slot_hash'].'.knowledge_coordinate';
                foreach ($payload['rows'] as $revision) {
                    foreach (['calendar_revision_id', 'revision_uid', 'recorded_at', 'source_ref', 'source_version', 'session_state', 'supersedes_revision_id'] as $field) {
                        if (! array_key_exists($field, $revision)) $missing[] = 'calendar_session.'.$row['slot_hash'].'.'.$field;
                    }
                    if (isset($revision['cal_date'])) $dates[(string) $revision['cal_date']] = true;
                }
            }
            if ($operation === 'calendar-read-completion/v1') {
                $calendarManifests[$row['stage_code'].'|'.($selection['producer_operation'] ?? '')] = $payload['rows'][0];
            }
            if ($component === 'registry_versions') $registry = $payload['rows'][0];
            if (in_array($operation, ['temporal-identity-revisions/v1', 'provider-mapping-revisions/v1'], true)) {
                $prefix = $component.'.'.$row['slot_hash'];
                if (($selection['known_at'] ?? null) !== (string) $run->knowledge_cutoff_at) $missing[] = $prefix.'.knowledge_coordinate';
                if (($selection['dataset_start'] ?? null) !== (string) config('market_data.scope.dataset_start')) $missing[] = $prefix.'.dataset_boundary';
                $temporal = $payload['rows'][0] ?? []; $reference = $temporal['population_ref'] ?? [];
                $source = $populations[($reference['stage_code'] ?? '').'|'.($reference['slot_hash'] ?? '')] ?? null;
                if (! $source || (int) ($reference['run_id'] ?? 0) !== (int) $run->run_id
                    || ($reference['stage_code'] ?? null) !== $row['stage_code']
                    || ($reference['component_key'] ?? null) !== 'universe_identity'
                    || ($reference['payload_hash'] ?? null) !== $source[0]['payload_hash']
                    || ($selection['producer_operation'] ?? null) !== ($source[1]['selection_context']['producer_operation'] ?? null)
                    || ($source[1]['rows'][0]['population_schema'] ?? null) !== 'md_temporal_revision_population_v1') {
                    $missing[] = $prefix.'.population_reference';
                } else {
                    if (($temporal['population_counts'] ?? null) !== ($source[1]['rows'][0]['population_counts'] ?? null)) $missing[] = $prefix.'.population_reference.counts';
                    $temporal['tables'] = $source[1]['rows'][0]['tables'] ?? [];
                    $missing = array_merge($missing, $this->temporalMissing($temporal, $prefix, $selection));
                }
                if ($operation === 'provider-mapping-revisions/v1') {
                    $scope = $row['stage_code'].'|'.($selection['producer_operation'] ?? '');
                    foreach ($payload['rows'][0]['selected_rows'] ?? [] as $selected) $mappingSelections[$scope][] = $selected;
                }
            }
            if ($operation === 'event-factor-revisions/v1') {
                try { ProducerEventFactorCapture::assertValid($payload['rows'][0] ?? [], $selection); }
                catch (\Throwable $error) { $missing[] = 'event_factor.'.$row['slot_hash'].'.'.$error->getMessage(); }
            }
            if ($operation === 'raw-input-lineage/v1') {
                try { ProducerRawInputLineage::assertValid($payload['rows'][0] ?? [], $selection); }
                catch (\Throwable $error) { $missing[] = 'raw_history.'.$row['slot_hash'].'.'.$error->getMessage(); }
            }
            if ($operation === 'ancillary-source-revisions/v1') {
                try {
                    if (($selection['domain'] ?? null) === 'dormancy') {
                        ProducerAncillaryCapture::assertValidDormancy($payload['rows'][0] ?? [], $selection);
                    } elseif (($selection['domain'] ?? null) === 'indicator_dependencies') {
                        ProducerAncillaryCapture::assertValid($payload['rows'][0] ?? [], $selection, (string) $selection['trade_date']);
                    }
                } catch (\Throwable $error) { $missing[] = 'ancillary.'.$row['slot_hash'].'.'.$error->getMessage(); }
            }
            if ($operation === 'provider-source-row-link/v1') $sourceLinks[] = [$row, $payload];
            if ($operation === 'status-authority-revisions/v1') {
                $prefix = 'status_expectation.'.$row['slot_hash']; $status = $payload['rows'][0] ?? [];
                $ref = $status['population_ref'] ?? [];
                $source = $populations[($ref['stage_code'] ?? '').'|'.($ref['slot_hash'] ?? '')] ?? null;
                if (($selection['known_at'] ?? null) !== (string) $run->knowledge_cutoff_at) $missing[] = $prefix.'.knowledge_coordinate';
                if (! $source || (int) ($ref['run_id'] ?? 0) !== (int) $run->run_id
                    || ($ref['stage_code'] ?? null) !== $row['stage_code'] || ($ref['component_key'] ?? null) !== 'status_expectation'
                    || $source[0]['component_key'] !== 'status_expectation'
                    || ($ref['payload_hash'] ?? null) !== $source[0]['payload_hash']
                    || ($source[1]['selection_context']['operation'] ?? null) !== 'status-revision-population/v1'
                    || ($source[1]['selection_context']['known_at'] ?? null) !== ($selection['known_at'] ?? null)
                    || ($source[1]['selection_context']['producer_operation'] ?? null) !== ($selection['producer_operation'] ?? null)) $missing[] = $prefix.'.population_reference';
                else $missing = array_merge($missing, (new ProducerTradingStatusCaptureValidator())->missing($source[1]['rows'][0] ?? [], $status, $selection, $prefix));
            }
            if (in_array($component, ['universe_identity', 'provider_mapping', 'status_expectation', 'raw_history', 'event_factor', 'ancillary'], true) && isset($selection['producer_operation'])) {
                $inputScopes[$row['stage_code'].'|'.$selection['producer_operation']][$row['slot_hash']] = $row['payload_hash'];
            }
            if ($operation === 'producer-input-read-completion/v1') {
                $inputManifests[$row['stage_code'].'|'.$selection['producer_operation']] = $payload['rows'][0];
            }
        }
        foreach (self::REQUIRED_OPERATIONS as $component => $required) foreach ($required as $operation) {
            if (empty($operations[$component][$operation])) $missing[] = $component.'.'.$operation;
        }
        // The materialized-projection producers do not yet implement these full-revision
        // contracts. A caller cannot mint completion merely by inserting their operation names.
        $missing = array_merge($missing, (new ProducerSourceObservationCompleteness())->missing($captures, $repository));
        $missing = array_merge($missing, (new ProducerRawInputCompleteness())->missing($captures, $repository));
        foreach (array_keys($requiredDates) as $date) if (! isset($dates[$date])) $missing[] = 'calendar_session.required_date.'.$date;
        foreach ($calendarScopes as $scope => $actual) {
            sort($actual, SORT_STRING); $manifest = $calendarManifests[$scope] ?? [];
            if (($manifest['expected_slots'] ?? null) !== $actual || array_column($manifest['actual_slots'] ?? [], 'slot_hash') !== $actual) {
                $missing[] = 'completion.calendar_scopes.'.$scope;
            }
        }
        foreach (array_keys($calendarManifests) as $scope) if (! isset($calendarScopes[$scope])) $missing[] = 'completion.unexpected_calendar_scope.'.$scope;
        foreach (array_unique(array_merge(array_keys($inputScopes), array_keys($inputManifests))) as $scope) {
            $actual = $inputScopes[$scope] ?? []; ksort($actual, SORT_STRING);
            $manifest = $inputManifests[$scope] ?? [];
            $declared = array_column($manifest['actual_slots'] ?? [], 'payload_hash', 'slot_hash'); ksort($declared, SORT_STRING);
            if (($manifest['expected_slots'] ?? null) !== array_keys($actual) || $declared !== $actual) $missing[] = 'completion.producer_scope.'.$scope;
        }
        foreach ($sourceLinks as [$capture, $payload]) {
            $selection = $payload['selection_context']; $prefix = 'provider_mapping.'.$capture['slot_hash'].'.source_row_link';
            $link = $payload['rows'][0] ?? []; $binding = $link['identity_binding'] ?? [];
            $row = $link['observation_row'] ?? []; $identity = $link['resolved_identity'] ?? [];
            foreach (['source_observation_row_id', 'source_observation_id', 'listing_id', 'provider_mapping_id', 'mapping_revision', 'effective_trade_date', 'recorded_at'] as $field) {
                if (! isset($binding[$field]) || (string) $binding[$field] === '') $missing[] = $prefix.'.'.$field;
            }
            foreach (['source_observation_row_id', 'source_observation_id'] as $field) {
                if (! isset($row[$field], $binding[$field]) || (string) $row[$field] !== (string) $binding[$field]) $missing[] = $prefix.'.observation_identity';
            }
            if (($row['source_row_ref'] ?? null) !== ($selection['source_row_ref'] ?? null)) $missing[] = $prefix.'.source_row_ref';
            if (($binding['effective_trade_date'] ?? null) !== ($selection['trade_date'] ?? null)) $missing[] = $prefix.'.effective_trade_date';
            $scope = $capture['stage_code'].'|'.($selection['producer_operation'] ?? ''); $matched = false;
            foreach ($mappingSelections[$scope] ?? [] as $candidate) {
                if ((string) ($candidate['provider_mapping_id'] ?? '') !== (string) ($binding['provider_mapping_id'] ?? '')) continue;
                $matched = true;
                foreach (['listing_id', 'provider_mapping_id', 'mapping_revision'] as $field) {
                    if (! isset($identity[$field], $binding[$field], $candidate[$field])
                        || (string) $identity[$field] !== (string) $candidate[$field]
                        || (string) $binding[$field] !== (string) $candidate[$field]) $missing[] = $prefix.'.resolved_mapping.'.$field;
                }
            }
            if (! $matched) $missing[] = $prefix.'.consumed_mapping_missing';
        }
        if ($registry !== null) {
            $missing = array_merge($missing, $registry['missing_paths'] ?? ['registry_versions.missing_path_declaration']);
            if (empty($registry['reason_entries'])) $missing[] = 'registry_versions.reason_registry.entries';
            $build = $registry['executable_build'] ?? [];
            if (empty($build['files']) || empty($build['artifact_path']) || ! is_file(base_path($build['artifact_path']))
                || hash_file('sha256', base_path($build['artifact_path'])) !== ($build['artifact_hash'] ?? null)) {
                $missing[] = 'registry_versions.executable_build.artifact';
            }
        }
        $missing = array_values(array_unique($missing)); sort($missing, SORT_STRING);

        // F-MD-B18-A002-020: a derived promote run (EodRunRepository::createPromoteRunFromSeed)
        // never re-executes INGEST_BARS/ACQUISITION, so it can structurally never produce its own
        // provider_mapping/source_observations captures -- there is no new acquisition event to
        // recompute from. Per the C1 contract's own Sec3.1/Sec5 text ("immutable source references
        // sufficient to verify the content"; "carry contents or verifiable immutable references"),
        // these two domains only may be satisfied by an immutable reference to the recorded seed
        // run's own captures, never by copying/duplicating them under this run's own run_id and
        // never by recomputing or reading current/latest state. The reference is proven, not
        // assumed: it recurses into the seed run's own inspection (the exact same validation this
        // method already performs for a mainline run -- hash/population/ingress-provenance checks
        // included), so a missing, tampered, or wrong-domain seed capture still fails closed here.
        $referencedComponents = [];
        $referenceable = array_values(array_filter($missing, static function ($path) {
            foreach (self::SEED_REFERENCEABLE_PREFIXES as $prefix) if (strpos($path, $prefix) === 0) return true;
            return false;
        }));
        if ($referenceable !== [] && ! in_array((int) $run->run_id, $visitedRunIds, true)) {
            $seedRunId = $repository->resolveSeedRunId((int) $run->run_id);
            if ($seedRunId !== null) {
                try {
                    $seedRun = $repository->owningRun($seedRunId);
                    $seedResult = $this->inspect($seedRun, $repository, array_merge($visitedRunIds, [(int) $run->run_id]));
                    $seedMissingRelevant = array_values(array_filter($seedResult['missing_paths'], static function ($path) {
                        foreach (self::SEED_REFERENCEABLE_PREFIXES as $prefix) if (strpos($path, $prefix) === 0) return true;
                        return false;
                    }));
                    $satisfiedByReference = array_diff($referenceable, $seedMissingRelevant);
                    if ($satisfiedByReference !== []) {
                        $missing = array_values(array_diff($missing, $satisfiedByReference));
                        foreach ($seedResult['actual_slots'] as $slot) {
                            if (! in_array($slot['component_key'], ['provider_mapping', 'source_observations'], true)) continue;
                            $slot['source_run_id'] = $seedRunId;
                            $referencedComponents[] = $slot;
                        }
                        foreach (($seedResult['referenced_components'] ?? []) as $slot) {
                            if (in_array($slot['component_key'], ['provider_mapping', 'source_observations'], true)) {
                                $referencedComponents[] = $slot;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Seed run unresolvable, deleted, or its own captures fail verification: the
                    // reference cannot be proven, so $missing is left exactly as computed above --
                    // fail closed, never a silent pass.
                }
            }
        }

        $requiredDates = array_keys($requiredDates); sort($requiredDates, SORT_STRING);
        return ['schema_version' => 'producer_completion_manifest_v1', 'status' => $missing === [] ? 'COMPLETE' : 'BLOCKED',
            'required_operations' => self::REQUIRED_OPERATIONS, 'required_calendar_dates' => $requiredDates,
            'actual_slots' => $slots, 'missing_paths' => $missing, 'referenced_components' => $referencedComponents,
            'scope' => 'PRODUCER_CAPTURE_COMPLETENESS_ONLY_NOT_REPLAY_OR_STAGE_ACCEPTANCE'];
    }

    private function temporalMissing(array $payload, string $prefix, array $selection): array
    {
        $missing = [];
        foreach (['trade_date', 'known_at', 'ticker_code', 'provider', 'dataset_start', 'exchange_code', 'market_segment'] as $field) {
            if (! array_key_exists($field, $selection)) $missing[] = $prefix.'.selection_context.'.$field;
        }
        $required = [
            'md_issuers' => ['issuer_id', 'issuer_uid', 'legal_name', 'recorded_at', 'source_ref'],
            'md_instruments' => ['instrument_id', 'instrument_uid', 'issuer_id', 'instrument_type', 'currency_code', 'recorded_at', 'source_ref'],
            'md_listings' => ['listing_id', 'listing_uid', 'instrument_id', 'legacy_ticker_id', 'exchange_code', 'listed_date', 'delisted_date', 'delisted_recorded_at', 'recorded_at', 'source_ref'],
            'md_listing_symbols' => ['listing_symbol_id', 'listing_id', 'symbol', 'symbol_type', 'symbol_namespace', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'source_ref', 'source_observation_id'],
            'md_listing_boards' => ['listing_board_id', 'listing_id', 'board_code', 'market_segment', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'source_ref', 'source_observation_id'],
            'md_provider_symbol_mappings' => ['provider_mapping_id', 'listing_id', 'provider', 'provider_symbol', 'mapping_revision', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'source_ref', 'source_observation_id'],
        ];
        if (($payload['population_schema'] ?? null) !== 'md_temporal_producer_population_v1') $missing[] = $prefix.'.population_schema';
        foreach ($required as $table => $fields) {
            $rows = $payload['tables'][$table] ?? null;
            if (! is_array($rows)) { $missing[] = $prefix.'.'.$table.'.population'; continue; }
            if (($payload['population_counts'][$table] ?? null) !== count($rows)) $missing[] = $prefix.'.'.$table.'.population_count';
            foreach ($rows as $index => $row) {
                foreach ($fields as $field) if (! array_key_exists($field, $row)) $missing[] = $prefix.'.'.$table.'.'.$index.'.'.$field;
                if (isset($row['recorded_at']) && (string) $row['recorded_at'] > (string) ($selection['known_at'] ?? '')) $missing[] = $prefix.'.'.$table.'.'.$index.'.future_revision';
            }
            if (($payload['selection_basis']['source_primary_keys'][$table] ?? null) !== $fields[0]) $missing[] = $prefix.'.'.$table.'.revision_identity';
        }
        foreach (['knowledge', 'effective', 'retraction', 'revision_relationship'] as $field) if (empty($payload['selection_basis'][$field])) $missing[] = $prefix.'.selection_basis.'.$field;
        if (! isset($payload['selected_rows'], $payload['omitted_listings'])) $missing[] = $prefix.'.membership';
        else {
            $accounted = array_map('intval', array_column($payload['selected_rows'], 'listing_id'));
            foreach ($payload['omitted_listings'] as $omitted) {
                if (empty($omitted['reasons'])) $missing[] = $prefix.'.omission_basis';
                $accounted[] = (int) $omitted['listing_id'];
            }
            $accounted = array_values(array_unique($accounted)); sort($accounted);
            $population = array_map('intval', array_column($payload['tables']['md_listings'] ?? [], 'listing_id')); sort($population);
            if ($accounted !== $population) $missing[] = $prefix.'.population_membership';
            if ($accounted === []) $missing[] = $prefix.'.empty_authoritative_population';
        }
        if ($missing === []) $missing = array_merge($missing, $this->temporalSelectionMissing($payload, $prefix, $selection));
        return $missing;
    }

    /** Independent relational validation against captured sources, never a current database query. */
    private function temporalSelectionMissing(array $payload, string $prefix, array $selection): array
    {
        $tables = $payload['tables']; $date = (string) $selection['trade_date']; $cutoff = (string) $selection['known_at'];
        $provider = $selection['provider'] ?? null; $ticker = $selection['ticker_code'] ?? null;
        $issuers = array_column($tables['md_issuers'], null, 'issuer_id');
        $instruments = array_column($tables['md_instruments'], null, 'instrument_id');
        $effective = static function ($r) use ($date, $cutoff) {
            if ($r['effective_from'] > $date.' 23:59:59') return false;
            if ($r['effective_to'] !== null && $r['effective_to'] <= $date.' 00:00:00') return false;
            return $r['retracted_at'] === null || $r['retracted_at'] > $cutoff;
        };
        $expected = []; $omissions = [];
        foreach ($tables['md_listings'] as $l) {
            $id = (int) $l['listing_id']; $reasons = [];
            $i = $instruments[$l['instrument_id']] ?? null; $iss = $i ? ($issuers[$i['issuer_id']] ?? null) : null;
            if ($i === null) $reasons[] = 'INSTRUMENT_NOT_KNOWN';
            if ($iss === null) $reasons[] = 'ISSUER_NOT_KNOWN';
            if ($l['exchange_code'] !== $selection['exchange_code']) $reasons[] = 'EXCHANGE_OUT_OF_SCOPE';
            if ($l['listed_date'] > $date) $reasons[] = 'NOT_YET_LISTED';
            if ($l['delisted_date'] !== null && $l['delisted_date'] <= $date && $l['delisted_recorded_at'] !== null && $l['delisted_recorded_at'] <= $cutoff) $reasons[] = 'KNOWN_DELISTING';
            $symbols = []; $boards = []; $mappings = [];
            foreach ($tables['md_listing_symbols'] as $s) if ((int) $s['listing_id'] === $id && $s['symbol_type'] === 'EXCHANGE' && $effective($s)) $symbols[] = $s;
            foreach ($tables['md_listing_boards'] as $b) if ((int) $b['listing_id'] === $id && $b['market_segment'] === $selection['market_segment'] && $effective($b)) $boards[] = $b;
            if ($symbols === []) $reasons[] = 'NO_EFFECTIVE_KNOWN_EXCHANGE_SYMBOL';
            if ($boards === []) $reasons[] = 'NO_EFFECTIVE_KNOWN_BOARD';
            if ($ticker !== null) {
                $symbols = array_values(array_filter($symbols, static function ($s) use ($ticker) { return strtoupper(trim($s['symbol'])) === $ticker; }));
                if ($symbols === []) $reasons[] = 'REQUESTED_SYMBOL_NOT_SELECTED';
            }
            if ($provider === null) $mappings = [null];
            else foreach ($tables['md_provider_symbol_mappings'] as $p) if ((int) $p['listing_id'] === $id && $p['provider'] === $provider && $effective($p)) $mappings[] = $p;
            if ($mappings === []) $reasons[] = 'NO_EFFECTIVE_KNOWN_PROVIDER_MAPPING';
            if ($reasons !== []) { $omissions[$id] = $reasons; continue; }
            foreach ($symbols as $s) foreach ($boards as $b) foreach ($mappings as $p) {
                $key = $id.'|'.$s['listing_symbol_id'].'|'.$b['listing_board_id'].'|'.($p['provider_mapping_id'] ?? '');
                $expected[$key] = ['listing_id' => $id, 'ticker_id' => $l['legacy_ticker_id'], 'issuer_id' => $iss['issuer_id'],
                    'issuer_uid' => $iss['issuer_uid'], 'instrument_id' => $i['instrument_id'], 'instrument_uid' => $i['instrument_uid'],
                    'ticker_code' => $s['symbol'], 'exchange_code' => $l['exchange_code'], 'market_segment' => $b['market_segment'],
                    'board_code' => $b['board_code'], 'listing_board_id' => $b['listing_board_id'], 'listing_symbol_id' => $s['listing_symbol_id'],
                    'listed_date' => $l['listed_date'], 'delisted_date' => $l['delisted_date'], 'listing_recorded_at' => $l['recorded_at'],
                    'board_recorded_at' => $b['recorded_at'], 'symbol_recorded_at' => $s['recorded_at']];
                if ($p !== null) $expected[$key] += ['provider_mapping_id' => $p['provider_mapping_id'], 'provider' => $p['provider'],
                    'provider_symbol' => $p['provider_symbol'], 'mapping_revision' => $p['mapping_revision'], 'provider_mapping_recorded_at' => $p['recorded_at']];
            }
        }
        $missing = []; $actual = [];
        foreach ($payload['selected_rows'] as $r) {
            $key = ($r['listing_id'] ?? '').'|'.($r['listing_symbol_id'] ?? '').'|'.($r['listing_board_id'] ?? '').'|'.($r['provider_mapping_id'] ?? '');
            if (isset($actual[$key])) $missing[] = $prefix.'.duplicate_selected_revision';
            $actual[$key] = true;
            if (! isset($expected[$key])) { $missing[] = $prefix.'.unexpected_selected_revision'; continue; }
            foreach ($expected[$key] as $field => $value) if (! array_key_exists($field, $r)
                || ($value === null ? $r[$field] !== null : (string) $value !== (string) $r[$field])) $missing[] = $prefix.'.selection_result.'.$field;
        }
        $keys = array_keys($expected); $actualKeys = array_keys($actual); sort($keys); sort($actualKeys);
        if ($keys !== $actualKeys) $missing[] = $prefix.'.selected_revision_population';
        $recordedOmissions = [];
        foreach ($payload['omitted_listings'] as $o) $recordedOmissions[(int) $o['listing_id']] = $o['reasons'];
        ksort($omissions); ksort($recordedOmissions);
        if ($omissions !== $recordedOmissions) $missing[] = $prefix.'.omission_basis';
        return $missing;
    }
}
