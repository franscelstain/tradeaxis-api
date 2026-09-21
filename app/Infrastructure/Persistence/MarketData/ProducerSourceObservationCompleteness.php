<?php

namespace App\Infrastructure\Persistence\MarketData;

/** C06 content and cross-capture obligations, independent of total capture-slot count. */
final class ProducerSourceObservationCompleteness
{
    public function missing(array $captures, RunInputCaptureRepository $repository): array
    {
        $missing = []; $populations = []; $journals = []; $declared = []; $ingress = []; $outcomes = [];
        $immutableMembers = [];
        foreach ($captures as $row) {
            $p = $repository->verify($row); $s = $p['selection_context']; $op = $s['operation'];
            $scope = $row['stage_code'].'|'.($s['producer_operation'] ?? '');
            if ($op === 'source-observation-journal-completion/v1') {
                $hashes = $p['rows'][0]['journal_hashes'] ?? []; $keys = array_keys($hashes); sort($keys, SORT_STRING);
                if ($keys !== ($s['declared_slots'] ?? null)) $missing[] = 'source_observations.journal_declaration.'.$row['slot_hash'];
                foreach ($hashes as $slot => $hash) $declared[$scope][$slot] = $hash;
            }
            if ($op === 'source-observation-outcomes/v1') {
                try {
                    if (count($p['rows']) !== 1) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_POPULATION');
                    ProducerSourceObservationPopulation::assertValid($p['rows'][0], $s);
                    foreach (ProducerSourceObservationPopulation::TABLE_KEYS as $table => $key) foreach ($p['rows'][0]['population']['tables'][$table] as $member) {
                        $identity = $table.'|'.$member[$key]; $bytes = RunInputCaptureRepository::canonicalJson($member);
                        if (isset($immutableMembers[$identity]) && $immutableMembers[$identity] !== $bytes) $missing[] = 'source_observations.immutable_member_changed.'.$identity;
                        $immutableMembers[$identity] = $bytes;
                    }
                } catch (\Throwable $e) { $missing[] = 'source_observations.'.$row['slot_hash'].'.'.$e->getMessage(); }
                $populations[$scope][$row['slot_hash']] = [$row, $p];
                if (($s['read_kind'] ?? null) === 'outcomeRows') foreach ($s['observation_ids'] as $id) $outcomes[$scope][$id] = true;
            }
            if ($op === 'source-observation-journal/v1') $journals[$scope][$row['slot_hash']] = [$row, $p];
            if ($op === 'ingest-source-rows/v1') $ingress[] = [$row, $p];
        }
        if ($ingress === []) $missing[] = 'source_observations.no_producer_ingress_population';
        foreach ($populations as $scope => $members) foreach ($members as $slot => [$row, $p]) {
            if (($declared[$scope][$slot] ?? null) !== $row['payload_hash']) $missing[] = 'source_observations.undeclared_population.'.$slot;
        }
        foreach ($declared as $scope => $members) foreach ($members as $slot => $hash) {
            $source = $populations[$scope][$slot] ?? $journals[$scope][$slot] ?? null;
            if (! $source || $source[0]['payload_hash'] !== $hash) $missing[] = 'source_observations.dangling_population_reference.'.$slot;
        }
        foreach ($journals as $scope => $members) foreach ($members as [$row, $p]) {
            $o = $p['rows'][0]['observation'];
            if ($row['stage_code'] === 'ACQUISITION' && in_array($o['outcome_state'], ['ACCEPTED','NORMALIZED'], true)
                && empty($outcomes[$scope][(int) $o['source_observation_id']])) $missing[] = 'source_observations.accepted_row_population.'.$o['source_observation_id'];
        }
        foreach ($ingress as [$row, $p]) {
            $scope = $row['stage_code'].'|'.$p['selection_context']['input_route'].'/v1'; $matched = false; $selection = null; $guards = []; $manifest = false;
            foreach ($populations[$scope] ?? [] as [$source, $capsule]) {
                $context = $capsule['selection_context']; $kind = $context['read_kind'] ?? null;
                if ($kind === 'incomingRows' && RunInputCaptureRepository::canonicalJson($capsule['rows'][0]['evaluation']['result']) === RunInputCaptureRepository::canonicalJson($p['rows'])) $matched = true;
                if ($kind === 'ingestSelection' && RunInputCaptureRepository::canonicalJson($context['input_rows']) === RunInputCaptureRepository::canonicalJson($p['rows'])) $selection = $capsule['rows'][0]['evaluation']['result'];
                if ($kind === 'existsAccepted') $guards[(string) $context['observation_ids'][0].'|'.(string) $context['source_row_ref']] = $capsule['rows'][0]['evaluation']['result'];
                if ($kind === 'manifestIds') {
                    $expected = array_values(array_unique(array_filter(array_map('intval', array_column($p['rows'], 'source_observation_id'))))); sort($expected, SORT_NUMERIC);
                    if ($context['observation_ids'] === $expected) $manifest = true;
                }
            }
            if (! $matched) $missing[] = 'source_observations.ingress_provenance.'.$row['slot_hash'];
            if ($selection === null) $missing[] = 'source_observations.ingest_selection.'.$row['slot_hash'];
            else foreach ($selection['selected_rows'] as $selected) {
                $key = (string) $selected['source_observation_id'].'|'.(string) $selected['source_row_ref'];
                if (($guards[$key] ?? null) !== true) $missing[] = 'source_observations.acceptance_read.'.$key;
            }
            if ($p['selection_context']['input_route'] === 'ingestAcquiredRows' && ! $manifest) $missing[] = 'source_observations.manifest_provenance.'.$row['slot_hash'];
        }
        $missing = array_values(array_unique($missing)); sort($missing, SORT_STRING); return $missing;
    }
}
