<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** C08 materialization at the factor producer, before its context reaches an analytical vector. */
final class ProducerEventFactorCapture
{
    public const KEYS = [
        'md_corporate_action_revisions' => 'corporate_action_revision_id',
        'md_listings' => 'listing_id',
        'market_data_corporate_action_types' => 'action_type_code',
        'md_source_scale_assessments' => 'source_scale_assessment_id',
        'md_source_observations' => 'source_observation_id',
        'md_adjustment_factor_sets' => 'factor_set_id',
        'md_adjustment_factor_decisions' => 'factor_decision_id',
        'md_adjustment_factors' => 'adjustment_factor_id',
    ];

    public static function execute($run, $publicationId, $date, array $bars, callable $produce): array
    {
        if (! ProducerInputScope::active()) {
            $repository = new RunInputCaptureRepository();
            $owner = $repository->owningRun((int) $run->run_id);
            if ((string) $owner->knowledge_cutoff_at !== (string) ($run->knowledge_cutoff_at ?? '')
                || (int) $owner->config_snapshot_id !== (int) $run->config_snapshot_id) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_RUN_IDENTITY');
            return $repository->executeProducer($owner, 'INDICATORS', 'factor-context/v1:'.(int) $publicationId,
                static function () use ($owner, $publicationId, $date, $bars, $produce) { return self::execute($owner, $publicationId, $date, $bars, $produce); });
        }
        $selection = ['operation' => 'event-factor-revisions/v1', 'publication_id' => (int) $publicationId,
            'trade_date' => (string) $date, 'known_at' => (string) ProducerInputScope::knownAt($run->knowledge_cutoff_at),
            'config_snapshot_id' => (int) $run->config_snapshot_id, 'run_id' => (int) $run->run_id, 'provider' => 'YAHOO_FINANCE'];
        $captured = ProducerInputScope::inputRead('event_factor', $selection, static function () use ($selection, $bars, $produce) {
            if (ProducerInputScope::historical()) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_HISTORICAL_BINDING_REQUIRED');
            $publication = DB::table('eod_publications')->where('publication_id', $selection['publication_id'])->first();
            if (! $publication || (int) $publication->run_id !== $selection['run_id'] || $publication->trade_date !== $selection['trade_date']) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_PUBLICATION_IDENTITY');
            $identity = array_intersect_key((array) $publication, array_flip(['publication_id','run_id','trade_date','publication_version']));
            $tables = [];
            foreach (['md_corporate_action_revisions', 'md_listings', 'market_data_corporate_action_types'] as $table) $tables[$table] = self::rows($table);
            $result = $produce(); $trace = $result['_producer_trace']; unset($result['_producer_trace']);
            // UNKNOWN fallback is materialized by the producer and then consumed. Retain that immutable
            // row too, including its recorded time; never backdate it to the run's knowledge cutoff.
            $tables['md_source_scale_assessments'] = self::rows('md_source_scale_assessments');
            $ids = array_column($tables['md_corporate_action_revisions'], 'source_observation_id');
            foreach ($tables['md_source_scale_assessments'] as $assessment) {
                $evidence = json_decode((string) $assessment['evidence_json'], true);
                foreach ($evidence['observation_ids'] ?? [] as $id) $ids[] = $id;
            }
            $tables['md_source_observations'] = self::rows('md_source_observations', DB::table('md_source_observations')->whereIn('source_observation_id', array_values(array_unique(array_filter($ids)))));
            $sourcePopulation = ProducerSourceObservationPopulation::immutablePopulation(array_column($tables['md_source_observations'], 'source_observation_id'));
            $tables['md_source_observations'] = $sourcePopulation['tables']['md_source_observations'];
            foreach (['md_adjustment_factor_sets', 'md_adjustment_factor_decisions', 'md_adjustment_factors'] as $table) {
                $tables[$table] = self::rows($table, DB::table($table)->where('factor_set_id', $result['factor_set_id']));
            }
            $p = ['schema' => 'producer_event_factor_v1', 'publication_identity' => $identity, 'tables' => $tables, 'table_hashes' => array_map([ProducerRawInputLineage::class, 'hash'], $tables),
                'source_population' => $sourcePopulation, 'trace' => $trace, 'factor_payload_json' => json_encode($trace['canonical_payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'result' => $result, 'fallback_bars' => $bars,
                'omitted_revisions' => self::eventSelection($tables, $selection)['omitted'],
                'assessment_omissions' => self::assessmentOmissions($tables, $selection, $trace['assessments']),
                'factor_omissions' => self::factorOmissions($trace['terms']),
                'empty_basis' => $tables['md_corporate_action_revisions'] === [] ? 'NO_EVENT_REVISIONS_IN_PRODUCER_POPULATION' : null];
            self::assertValid($p, $selection);
            return [$p];
        });
        return $captured[0]['result'];
    }

    private static function rows(string $table, $query = null): array
    {
        return ($query ?: DB::table($table))->orderBy(self::KEYS[$table])->get()->map(static function ($r) { return (array) $r; })->all();
    }

    public static function eventSelection(array $tables, array $s): array
    {
        $listings = array_column($tables['md_listings'], null, 'listing_id'); $selected = []; $omitted = [];
        foreach ($tables['md_corporate_action_revisions'] as $r) {
            $reasons = [];
            if (! isset($listings[$r['listing_id']])) $reasons[] = 'NO_LISTING_JOIN';
            if (! in_array($r['verification_state'], ['AUTHORITATIVE_VERIFIED', 'MANUAL_VERIFIED'], true)) $reasons[] = 'NOT_VERIFIED';
            if ($r['lifecycle_state'] !== 'EFFECTIVE') $reasons[] = 'NOT_EFFECTIVE';
            if ($r['ex_date'] === null) $reasons[] = 'NO_EX_DATE';
            elseif ($r['ex_date'] > $s['trade_date']) $reasons[] = 'AFTER_REQUESTED_DATE';
            if ($r['recorded_at'] > $s['known_at']) $reasons[] = 'AFTER_KNOWLEDGE_CUTOFF';
            foreach ($tables['md_corporate_action_revisions'] as $n) if ((string) $n['supersedes_revision_id'] === (string) $r['corporate_action_revision_id'] && $n['recorded_at'] <= $s['known_at']) { $reasons[] = 'SUPERSEDED_AT_CUTOFF'; break; }
            if ($reasons) $omitted[] = ['revision_id' => (int) $r['corporate_action_revision_id'], 'basis' => $reasons];
            else { $r['legacy_ticker_id'] = $listings[$r['listing_id']]['legacy_ticker_id']; $selected[] = $r; }
        }
        usort($selected, static function ($a, $b) { return strcmp($a['ex_date'], $b['ex_date']) ?: $a['corporate_action_revision_id'] <=> $b['corporate_action_revision_id']; });
        return ['selected' => $selected, 'omitted' => $omitted];
    }

    public static function assertValid(array $p, array $s): void
    {
        if (($p['schema'] ?? null) !== 'producer_event_factor_v1') throw new \RuntimeException('INPUT_CAPTURE_FACTOR_SCHEMA');
        $identity = $p['publication_identity'] ?? [];
        if ((int) ($identity['publication_id'] ?? 0) !== $s['publication_id'] || (int) ($identity['run_id'] ?? 0) !== $s['run_id'] || ($identity['trade_date'] ?? null) !== $s['trade_date'] || empty($identity['publication_version'])) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_PUBLICATION_IDENTITY');
        $t = $p['tables'];
        $required = [
            'md_corporate_action_revisions' => ['event_uid','revision_number','listing_id','action_type_code','lifecycle_state','verification_state','ex_date','cum_date','record_date','payment_date','terms_json','source_observation_id','effective_at','recorded_at','supersedes_revision_id'],
            'md_source_scale_assessments' => ['assessment_uid','revision_number','provider','listing_id','corporate_action_revision_id','source_scale_state','scale_effective_from','assessment_version','evidence_observation_set_hash','evidence_json','recorded_at','supersedes_assessment_id','created_at'],
            'md_adjustment_factor_sets' => ['factor_set_uid','price_product_code','factor_formula_version','config_snapshot_id','state','content_hash','recorded_at','created_at'],
        ];
        foreach (self::KEYS as $table => $key) {
            if (! isset($t[$table]) || ($p['table_hashes'][$table] ?? null) !== ProducerRawInputLineage::hash($t[$table])) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_POPULATION_HASH: '.$table);
            $ids = [];
            foreach ($t[$table] as $r) {
                if (! isset($r[$key]) || isset($ids[(string) $r[$key]])) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_POPULATION_KEY: '.$table);
                foreach ($required[$table] ?? [] as $field) if (! array_key_exists($field, $r)) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_REQUIRED_FIELD: '.$table.'.'.$field);
                $ids[(string) $r[$key]] = true;
            }
        }
        ProducerSourceObservationPopulation::assertImmutablePopulation($p['source_population']);
        self::same($t['md_source_observations'], $p['source_population']['tables']['md_source_observations'], 'SOURCE_POPULATION');
        $selection = self::eventSelection($t, $s); $expected = $selection['selected'];
        $observations = array_column($t['md_source_observations'], null, 'source_observation_id');
        foreach ($expected as &$r) {
            $obs = $observations[$r['source_observation_id']] ?? [];
            if (($obs['outcome_state'] ?? null) !== 'ACCEPTED' || ! preg_match('/^[a-f0-9]{64}$/iD', (string) ($obs['payload_hash'] ?? ''))) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_PROVENANCE');
            $r['source_observation_hash'] = strtolower(trim($obs['payload_hash']));
        }
        unset($r);
        self::same($expected, $p['trace']['selected_events'], 'SELECTED_REVISIONS');
        self::same($selection['omitted'], $p['omitted_revisions'], 'OMISSION');
        if (($p['empty_basis'] ?? null) !== ($t['md_corporate_action_revisions'] === [] ? 'NO_EVENT_REVISIONS_IN_PRODUCER_POPULATION' : null)) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_EMPTY_BASIS');
        $sets = $t['md_adjustment_factor_sets'];
        if (count($sets) !== 1 || (int) $sets[0]['factor_set_id'] !== $p['result']['factor_set_id']
            || $sets[0]['content_hash'] !== $p['result']['factor_set_hash']
            || $sets[0]['factor_set_uid'] !== $sets[0]['content_hash'] || $sets[0]['state'] !== 'BOUND'
            || (int) $sets[0]['config_snapshot_id'] !== $s['config_snapshot_id']
            || $sets[0]['price_product_code'] !== \App\Domain\MarketData\MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT
            || $sets[0]['factor_formula_version'] !== \App\Application\MarketData\Services\AdjustmentFactorSetService::FACTOR_FORMULA_VERSION
            || hash('sha256', $p['factor_payload_json']) !== $sets[0]['content_hash']) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_SET_CONTENT');
        self::same(json_decode($p['factor_payload_json'], true), $p['trace']['canonical_payload'], 'FACTOR_PAYLOAD');
        $types = array_column($t['market_data_corporate_action_types'], null, 'action_type_code');
        $terms = []; $decisions = []; $assessments = [];
        foreach ($expected as $event) {
            $id = (int) $event['corporate_action_revision_id'];
            $term = self::terms($event, $types[$event['action_type_code']] ?? []); $terms[$id] = $term;
            if (! $term['factor_required']) continue;
            if ($term['price_factor'] === null) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_TERMS_INCOMPLETE');
            $actual = $p['trace']['assessments'][$id] ?? null;
            if (! is_array($actual)) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_ASSESSMENT_MISSING');
            $eligible = [];
            foreach ($t['md_source_scale_assessments'] as $a) {
                if ((int) $a['corporate_action_revision_id'] !== $id || $a['provider'] !== $s['provider'] || $a['recorded_at'] > $s['known_at']) continue;
                $superseded = false;
                foreach ($t['md_source_scale_assessments'] as $newer) if ((string) $newer['supersedes_assessment_id'] === (string) $a['source_scale_assessment_id'] && $newer['recorded_at'] <= $s['known_at']) $superseded = true;
                if (! $superseded) $eligible[] = $a;
            }
            if ($eligible) {
                $max = max(array_column($eligible, 'revision_number')); $allowed = [];
                foreach ($eligible as $a) if ($a['revision_number'] == $max) $allowed[] = $a;
            } else {
                $obsIds = [];
                foreach ($p['fallback_bars'][(int) $event['legacy_ticker_id']] ?? [] as $bar) if (! empty($bar['source_observation_id'])) $obsIds[] = (int) $bar['source_observation_id'];
                $obsIds = array_values(array_unique($obsIds)); sort($obsIds, SORT_NUMERIC);
                $evidenceHash = hash('sha256', json_encode($obsIds));
                $uid = hash('sha256', json_encode(['provider' => $s['provider'], 'event_revision_id' => $id, 'state' => 'UNKNOWN',
                    'evidence_hash' => $evidenceHash, 'assessment_version' => \App\Application\MarketData\Services\AdjustmentFactorSetService::ASSESSMENT_VERSION], JSON_UNESCAPED_SLASHES));
                $allowed = array_values(array_filter($t['md_source_scale_assessments'], static function ($a) use ($uid) { return $a['assessment_uid'] === $uid && $a['source_scale_state'] === 'UNKNOWN'; }));
            }
            $match = false;
            foreach ($allowed as $a) if (RunInputCaptureRepository::canonicalJson($a) === RunInputCaptureRepository::canonicalJson($actual)) $match = true;
            if (! $match || (int) $actual['listing_id'] !== (int) $event['listing_id']) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_ASSESSMENT_SELECTION');
            $assessments[$id] = $actual;
            $state = $actual['source_scale_state'];
            $decision = $state === 'AS_TRADED' ? 'APPLIED' : ($state === 'PROVIDER_BACK_ADJUSTED' ? 'HELD_PROVIDER_BACK_ADJUSTED' : 'HELD_SOURCE_SCALE_UNKNOWN');
            $reason = $state === 'AS_TRADED' ? 'FACTOR_APPLIED_SOURCE_AS_TRADED' : ($state === 'PROVIDER_BACK_ADJUSTED' ? 'FACTOR_HELD_PROVIDER_BACK_ADJUSTED' : 'FACTOR_HELD_SOURCE_SCALE_UNKNOWN');
            $decisions[] = ['listing_id' => (int) $event['listing_id'], 'ticker_id' => (int) $event['legacy_ticker_id'],
                'action_type_code' => $event['action_type_code'], 'corporate_action_revision_id' => $id,
                'source_observation_id' => (int) $event['source_observation_id'], 'source_observation_hash' => $event['source_observation_hash'],
                'source_scale_assessment_id' => (int) $actual['source_scale_assessment_id'], 'source_scale_state' => $state,
                'decision_state' => $decision, 'reason_code' => $reason, 'ex_date' => $event['ex_date'],
                'price_factor' => $term['price_factor'], 'volume_factor' => $term['volume_factor'],
                'volume_factor_required' => $term['volume_factor_required'], 'breaks_price_continuity' => $term['breaks_price_continuity'],
                'breaks_volume_continuity' => $term['breaks_volume_continuity']];
        }
        self::same($terms, $p['trace']['terms'], 'TERMS');
        self::same($assessments, $p['trace']['assessments'], 'ASSESSMENT_POPULATION');
        self::same(self::assessmentOmissions($t, $s, $assessments), $p['assessment_omissions'], 'ASSESSMENT_OMISSION');
        self::same(self::factorOmissions($terms), $p['factor_omissions'], 'NON_STRUCTURAL_OMISSION');
        self::same($decisions, $p['result']['decisions'], 'DECISIONS');
        $canonical = [];
        foreach ($decisions as $d) $canonical[] = ['listing_id' => $d['listing_id'], 'corporate_action_revision_id' => $d['corporate_action_revision_id'],
            'source_observation_id' => $d['source_observation_id'], 'source_observation_hash' => $d['source_observation_hash'],
            'source_scale_assessment_id' => $d['source_scale_assessment_id'], 'source_scale_state' => $d['source_scale_state'],
            'decision_state' => $d['decision_state'], 'reason_code' => $d['reason_code'], 'ex_date' => $d['ex_date'],
            'candidate_price_factor' => number_format($d['price_factor'], 12, '.', ''),
            'candidate_volume_factor' => $d['volume_factor'] === null ? null : number_format($d['volume_factor'], 12, '.', '')];
        self::same(['schema_version' => 'adjustment-factor-decision-set/v2', 'price_product_code' => \App\Domain\MarketData\MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT,
            'factor_formula_version' => \App\Application\MarketData\Services\AdjustmentFactorSetService::FACTOR_FORMULA_VERSION,
            'config_snapshot_id' => $s['config_snapshot_id'], 'window_start' => \App\Domain\MarketData\MarketDataScope::DATASET_START,
            'window_end' => $s['trade_date'], 'decisions' => $canonical], $p['trace']['canonical_payload'], 'CANONICAL_DECISIONS');
        if (count($t['md_adjustment_factor_decisions']) !== count($decisions)) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_DECISION_POPULATION');
        $stored = array_column($t['md_adjustment_factor_decisions'], null, 'corporate_action_revision_id');
        $factors = []; $held = []; $applied = [];
        foreach ($decisions as $d) {
            $r = $stored[$d['corporate_action_revision_id']] ?? [];
            foreach (['listing_id', 'source_scale_assessment_id', 'decision_state', 'reason_code'] as $field) if ((string) ($r[$field] ?? '') !== (string) $d[$field]) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_STORED_DECISION');
            foreach (['price', 'volume'] as $field) if (! self::decimalEqual($r['candidate_'.$field.'_factor'] ?? null, $d[$field.'_factor'])) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_STORED_TERMS');
            if ($d['decision_state'] === 'APPLIED') {
                $applied[$d['corporate_action_revision_id']] = $d;
                $factors[$d['ticker_id']][] = ['listing_id' => $d['listing_id'], 'corporate_action_revision_id' => $d['corporate_action_revision_id'],
                    'factor_revision_ref' => 'md-corporate-action-revision:'.$d['corporate_action_revision_id'], 'ex_date' => $d['ex_date'],
                    'price_factor' => $d['price_factor'], 'volume_factor' => $d['volume_factor'], 'volume_factor_required' => $d['volume_factor_required']];
            } else $held[$d['ticker_id']][] = ['action_type_code' => $d['action_type_code'], 'action_date' => $d['ex_date'],
                'breaks_price_continuity' => $d['breaks_price_continuity'], 'breaks_volume_continuity' => $d['breaks_volume_continuity'],
                'is_unmapped_type' => false, 'factor_hold_reason_code' => $d['reason_code']];
        }
        self::same($factors, $p['result']['factors_by_ticker'], 'FACTOR_PROJECTION');
        self::same($held, $p['result']['held_events_by_ticker'], 'HELD_PROJECTION');
        if (count($t['md_adjustment_factors']) !== count($applied)) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_APPLIED_POPULATION');
        foreach ($t['md_adjustment_factors'] as $r) {
            $d = $applied[$r['corporate_action_revision_id']] ?? null;
            if (! $d || (int) $r['listing_id'] !== $d['listing_id'] || $r['effective_from'] !== \App\Domain\MarketData\MarketDataScope::DATASET_START
                || $r['effective_to'] !== date('Y-m-d', strtotime($d['ex_date'].' -1 day'))
                || ! self::decimalEqual($r['price_factor'], $d['price_factor']) || ! self::decimalEqual($r['volume_factor'], $d['volume_factor'])) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_APPLIED_CONTENT');
        }
    }

    private static function assessmentOmissions(array $t, array $s, array $chosen): array
    {
        $out = [];
        foreach ($t['md_source_scale_assessments'] as $a) {
            $id = (int) $a['corporate_action_revision_id'];
            if (isset($chosen[$id]) && (string) $chosen[$id]['source_scale_assessment_id'] === (string) $a['source_scale_assessment_id']) continue;
            $basis = [];
            if (! isset($chosen[$id])) $basis[] = 'EVENT_NOT_CONSUMED_FOR_FACTOR';
            if ($a['provider'] !== $s['provider']) $basis[] = 'DIFFERENT_PROVIDER';
            if ($a['recorded_at'] > $s['known_at']) $basis[] = 'AFTER_KNOWLEDGE_CUTOFF';
            foreach ($t['md_source_scale_assessments'] as $n) if ((string) $n['supersedes_assessment_id'] === (string) $a['source_scale_assessment_id'] && $n['recorded_at'] <= $s['known_at']) { $basis[] = 'SUPERSEDED_AT_CUTOFF'; break; }
            if (! $basis) $basis[] = 'NOT_SELECTED_BY_REVISION_ORDER';
            $out[] = ['assessment_id' => (int) $a['source_scale_assessment_id'], 'basis' => $basis];
        }
        return $out;
    }

    private static function factorOmissions(array $terms): array
    {
        $out = [];
        foreach ($terms as $id => $term) if (! $term['factor_required']) $out[] = ['revision_id' => (int) $id, 'basis' => 'TYPE_REGISTRY_HAS_NO_SCALED_CONTINUITY'];
        return $out;
    }

    private static function decimalEqual($a, $b): bool
    {
        return $a === null || $b === null ? $a === $b : is_numeric($a) && is_numeric($b) && bccomp(number_format((float) $a, 12, '.', ''), number_format((float) $b, 12, '.', ''), 12) === 0;
    }

    private static function terms(array $event, array $type): array
    {
        if (! $type) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_TYPE_MISSING');
        $price = strtoupper($type['price_continuity_impact']); $volume = strtoupper($type['volume_continuity_impact']);
        $required = $price === 'SCALED' || $volume === 'SCALED';
        $p = null; $v = null; $vr = $required && $volume === 'SCALED';
        $json = json_decode((string) $event['terms_json'], true) ?: [];
        if ($required) {
            if (in_array($event['action_type_code'], ['STOCK_SPLIT', 'REVERSE_STOCK_SPLIT'], true)) {
                $from = (float) ($json['ratio']['from'] ?? 0); $to = (float) ($json['ratio']['to'] ?? 0);
                $vr = true; if ($from > 0 && $to > 0) { $p = $from / $to; $v = $to / $from; }
            } else {
                $adjust = $json['adjustment'] ?? []; $p = $price === 'SCALED' ? (float) ($adjust['price_factor'] ?? 0) : 1.0;
                $v = isset($adjust['volume_factor']) ? (float) $adjust['volume_factor'] : null;
                if ($p <= 0 || ($v !== null && $v <= 0) || ($vr && $v === null)) { $p = null; $v = null; }
            }
        }
        return ['factor_required' => $required, 'price_factor' => $p, 'volume_factor' => $v, 'volume_factor_required' => $vr,
            'breaks_price_continuity' => $price !== 'NONE', 'breaks_volume_continuity' => $volume !== 'NONE'];
    }

    private static function same($a, $b, string $part): void
    {
        if (RunInputCaptureRepository::canonicalJson($a) !== RunInputCaptureRepository::canonicalJson($b)) throw new \RuntimeException('INPUT_CAPTURE_FACTOR_'.$part.'_MISMATCH');
    }
}
