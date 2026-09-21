<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** Explicit, bounded execution context for nested producer reads; never inferred from latest run. */
final class ProducerInputScope
{
    private static $current;
    private $run;
    private $stage;
    private $operation;
    private $repository;
    private $expected = [];
    private $expectedPayloads = [];
    private $failure;
    private $materialized = [];
    private $expectedInputs = [];
    private $journalInputs = [];
    private $rawReads = [];

    public static function rawRead(array $selection, array $rows): void
    {
        $s = self::$current; if (! $s) return;
        $key = RunInputCaptureRepository::canonicalJson($selection);
        try {
            if (isset($s->rawReads[$key])) ProducerRawInputCompleteness::assertSamePopulation($s->rawReads[$key]['rows'], $rows, 'READ_RETRY');
        } catch (\Throwable $e) { $s->failure = $e; throw $e; }
        $s->rawReads[$key] = ['rows' => $rows, 'complete' => false];
        self::inputRead('raw_history', ['operation' => 'raw-input-read-population/v1'] + $selection, static function () use ($rows) { return [['raw_rows' => $rows, 'empty_basis' => $rows === [] ? 'NO_RAW_ROWS_IN_EXPLICIT_READ_BOUNDARY' : null]]; });
    }

    public static function rawRetained(array $selection, array $rows): void
    {
        $s = self::$current; if (! $s) return;
        try {
            $key = RunInputCaptureRepository::canonicalJson($selection);
            if (! isset($s->rawReads[$key])) throw new \RuntimeException('INPUT_CAPTURE_RAW_READ_REQUIRED');
            ProducerRawInputCompleteness::assertSamePopulation($s->rawReads[$key]['rows'], $rows, 'RETAINED');
        } catch (\Throwable $e) { $s->failure = $e; throw $e; }
    }

    public static function rawProjection(array $selection, array $projection): array
    {
        $s = self::$current; if (! $s) return $projection;
        try {
            $key = RunInputCaptureRepository::canonicalJson($selection);
            if (! isset($s->rawReads[$key])) throw new \RuntimeException('INPUT_CAPTURE_RAW_READ_REQUIRED');
            $expected = ProducerRawInputCompleteness::projection($s->rawReads[$key]['rows'], $selection);
            $actual = ProducerRawInputCompleteness::observed($projection, $selection);
            ProducerRawInputCompleteness::assertSamePopulation($expected, $actual, 'PROJECTION');
            $receipt = ['read_ref' => self::inputReference('raw_history', ['operation' => 'raw-input-read-population/v1'] + $selection),
                'lineage_ref' => self::inputReference('raw_history', ['operation' => 'raw-input-lineage/v1'] + $selection),
                'projection_hash' => ProducerRawInputLineage::hash($actual)];
            // Copy destination digest is an execution completeness audit, never an input semantic payload.
            $s->repository->capture((int) $s->run->run_id, $s->stage, 'completion',
                ['operation' => 'raw-input-projection-audit/v1', 'producer_operation' => $s->operation] + $selection, [$receipt]);
            $s->rawReads[$key]['complete'] = true;
            return $projection;
        } catch (\Throwable $e) { $s->failure = $e; throw $e; }
    }

    /** Acquisition journals are append-only across failed attempts, not a reusable read population. */
    public static function observationJournal(array $selection, callable $read): array
    {
        $values = self::inputRead('source_observations', $selection, $read);
        $scope = self::$current;
        if ($scope) {
            $slot = RunInputCaptureRepository::slotHash('source_observations', ['producer_operation' => $scope->operation] + $selection);
            $scope->journalInputs[$slot] = $scope->expectedInputs[$slot]['payload_hash'];
            unset($scope->expectedInputs[$slot]);
        }
        return $values;
    }

    public static function active(): bool { return self::$current !== null; }

    public static function historical(): bool
    {
        return self::$current && (string) (self::$current->run->request_mode ?? '') === 'replay_verify';
    }

    public static function materialize(string $key, callable $read)
    {
        if (! self::$current) return $read();
        if (! array_key_exists($key, self::$current->materialized)) self::$current->materialized[$key] = $read();
        return self::$current->materialized[$key];
    }

    public static function inputReference(string $component, array $selection): array
    {
        $scope = self::$current;
        if (! $scope) throw new \RuntimeException('INPUT_CAPTURE_SCOPE_REQUIRED');
        $selection = ['producer_operation' => $scope->operation] + $selection;
        $slot = RunInputCaptureRepository::slotHash($component, $selection);
        if (empty($scope->expectedInputs[$slot]['payload_hash'])) throw new \RuntimeException('INPUT_CAPTURE_REFERENCE_NOT_CONSUMED');
        return ['run_id' => (int) $scope->run->run_id, 'stage_code' => $scope->stage, 'component_key' => $component,
            'slot_hash' => $slot, 'payload_hash' => $scope->expectedInputs[$slot]['payload_hash']];
    }

    /** Declare independently of persistence, then capture the same materialized payload returned. */
    public static function inputRead(string $component, array $selection, callable $read): array
    {
        $scope = self::$current;
        if (! $scope) return $read();
        $selection = ['producer_operation' => $scope->operation] + $selection;
        $key = RunInputCaptureRepository::slotHash($component, $selection);
        $scope->expectedInputs[$key] = ['component' => $component, 'payload_hash' => null];
        try {
            $values = $read();
            $scope->expectedInputs[$key]['payload_hash'] = hash('sha256', RunInputCaptureRepository::canonicalJson([
                'schema_version' => RunInputCaptureRepository::VERSION, 'component_key' => $component,
                'selection_context' => $selection, 'rows' => $values, 'empty_basis' => null,
            ]));
            $scope->repository->capture((int) $scope->run->run_id, $scope->stage, $component, $selection, $values);
            return $values;
        } catch (\Throwable $error) {
            if (strpos($error->getMessage(), 'INPUT_CAPTURE_') !== false) $scope->failure = $error;
            throw $error;
        }
    }

    public static function during($run, string $stage, string $operation, callable $producer, RunInputCaptureRepository $repository = null, bool $atomic = true)
    {
        if (! $atomic && $stage !== 'ACQUISITION') throw new \InvalidArgumentException('INPUT_CAPTURE_NONATOMIC_STAGE_FORBIDDEN');
        $parent = self::$current;
        if ($parent && (int) $parent->run->run_id !== (int) $run->run_id) {
            throw new \RuntimeException('INPUT_CAPTURE_CROSS_RUN_SCOPE');
        }
        $scope = new self();
        $scope->run = $run;
        $scope->stage = $stage;
        $scope->operation = $operation;
        $scope->repository = $repository ?: new RunInputCaptureRepository();
        self::$current = $scope;
        try {
            $execute = function () use ($scope, $producer) {
                $scope->repository->captureRegistryVersions($scope->run);
                try {
                    $result = $producer();
                } catch (\Throwable $error) {
                    if ($scope->failure) throw $scope->failure;
                    throw $error;
                }
                if ($scope->failure) throw $scope->failure;
                $scope->repository->captureRegistryVersions($scope->run);
                $scope->completeReads();
                $scope->completeJournals();
                foreach ($scope->rawReads as $read) if (! $read['complete']) throw new \RuntimeException('INPUT_CAPTURE_RAW_PROJECTION_REQUIRED');
                if ($scope->rawReads !== []) {
                    $captures = array_values(array_filter($scope->repository->forRun((int) $scope->run->run_id), function ($row) use ($scope) {
                        $p = $scope->repository->verify($row);
                        return $row['stage_code'] === $scope->stage && ($p['selection_context']['producer_operation'] ?? null) === $scope->operation;
                    }));
                    if ((new ProducerRawInputCompleteness())->missing($captures, $scope->repository) !== []) throw new \RuntimeException('INPUT_CAPTURE_RAW_SCOPE_COMPLETENESS');
                }
                $scope->completeInputs();
                return $result;
            };
            return $atomic ? DB::transaction($execute) : $execute();
        } finally {
            self::$current = $parent;
        }
    }

    public static function cacheIdentity(): string
    {
        $s = self::$current;
        return $s ? $s->run->run_id.'|'.$s->stage.'|'.$s->operation : 'unscoped';
    }

    public static function knownAt($knownAt)
    {
        if (! self::$current) return $knownAt;
        $cutoff = (string) self::$current->run->knowledge_cutoff_at;
        if ($cutoff === '') throw new \RuntimeException('INPUT_CAPTURE_KNOWLEDGE_CUTOFF_MISSING');
        if ($knownAt !== null && $knownAt !== '' && (string) $knownAt !== $cutoff) {
            throw new \RuntimeException('INPUT_CAPTURE_KNOWLEDGE_CUTOFF_CONFLICT');
        }
        return $cutoff;
    }

    /** Declare a read before executing it, then retain exactly its returned revision population. */
    public static function calendarRead(string $operation, array $selection, callable $read)
    {
        $scope = self::$current;
        if (! $scope) return $read();
        $selection = ['operation' => $operation, 'producer_operation' => $scope->operation] + $selection;
        $key = RunInputCaptureRepository::slotHash('calendar_session', $selection);
        $scope->expected[$key] = $selection;
        try {
            $rows = $read();
            $values = array_map(static function ($row) { return (array) $row; }, $rows->all());
            $empty = $values === [] ? ['reason' => 'NO_TERMINAL_REVISIONS_IN_SELECTED_RANGE',
                'source' => 'md_market_calendar_revisions', 'evaluated_population' => 0] : null;
            $scope->expectedPayloads[$key] = hash('sha256', RunInputCaptureRepository::canonicalJson([
                'schema_version' => RunInputCaptureRepository::VERSION, 'component_key' => 'calendar_session',
                'selection_context' => $selection, 'rows' => $values, 'empty_basis' => $empty,
            ]));
            $scope->repository->capture((int) $scope->run->run_id, $scope->stage, 'calendar_session', $selection, $values, $empty);
            return $rows;
        } catch (\Throwable $error) {
            // A consumer may classify an unavailable calendar as UNKNOWN, but must not swallow
            // a capture integrity/conflict error and commit dependent output.
            if (strpos($error->getMessage(), 'INPUT_CAPTURE_') !== false) $scope->failure = $error;
            throw $error;
        }
    }

    private function completeReads(): void
    {
        if ($this->expected === []) return;
        $actual = [];
        foreach ($this->repository->forRun((int) $this->run->run_id) as $row) {
            if ($row['stage_code'] !== $this->stage || $row['component_key'] !== 'calendar_session') continue;
            $payload = $this->repository->verify($row);
            if (($payload['selection_context']['producer_operation'] ?? null) !== $this->operation) continue;
            if (isset($this->expectedPayloads[$row['slot_hash']])
                && $this->expectedPayloads[$row['slot_hash']] !== $row['payload_hash']) {
                throw new \RuntimeException('INPUT_CAPTURE_CONSUMED_PAYLOAD_MISMATCH');
            }
            $actual[$row['slot_hash']] = ['slot_hash' => $row['slot_hash'], 'payload_hash' => $row['payload_hash'],
                'member_count' => (int) $row['member_count'], 'input_capture_id' => (int) $row['input_capture_id']];
        }
        $expected = array_keys($this->expected); $observed = array_keys($actual);
        sort($expected, SORT_STRING); sort($observed, SORT_STRING); ksort($actual, SORT_STRING);
        if ($expected !== $observed) throw new \RuntimeException('INPUT_CAPTURE_CALENDAR_COMPLETION_MISMATCH');
        $this->repository->capture((int) $this->run->run_id, $this->stage, 'completion', [
            'operation' => 'calendar-read-completion/v1', 'producer_operation' => $this->operation,
        ], [['scope' => 'CALENDAR_READS_ONLY', 'expected_slots' => $expected, 'actual_slots' => array_values($actual)]]);
    }

    private function completeInputs(): void
    {
        if ($this->expectedInputs === []) return;
        $actual = [];
        foreach ($this->repository->forRun((int) $this->run->run_id) as $row) {
            if ($row['stage_code'] !== $this->stage || ! in_array($row['component_key'], ['universe_identity', 'provider_mapping', 'status_expectation', 'raw_history', 'event_factor', 'ancillary'], true)) continue;
            $payload = $this->repository->verify($row);
            if (($payload['selection_context']['producer_operation'] ?? null) !== $this->operation) continue;
            if (! isset($this->expectedInputs[$row['slot_hash']])
                || $this->expectedInputs[$row['slot_hash']]['payload_hash'] !== $row['payload_hash']) {
                throw new \RuntimeException('INPUT_CAPTURE_CONSUMED_INPUT_MISMATCH');
            }
            $actual[$row['slot_hash']] = ['slot_hash' => $row['slot_hash'], 'payload_hash' => $row['payload_hash'],
                'component' => $row['component_key'], 'input_capture_id' => (int) $row['input_capture_id']];
        }
        $expected = array_keys($this->expectedInputs); $observed = array_keys($actual);
        sort($expected, SORT_STRING); sort($observed, SORT_STRING); ksort($actual, SORT_STRING);
        if ($expected !== $observed) throw new \RuntimeException('INPUT_CAPTURE_PRODUCER_COMPLETION_MISMATCH');
        $this->repository->capture((int) $this->run->run_id, $this->stage, 'completion', [
            'operation' => 'producer-input-read-completion/v1', 'producer_operation' => $this->operation,
        ], [['scope' => 'DECLARED_PRODUCER_INPUT_READS', 'expected_slots' => $expected, 'actual_slots' => array_values($actual)]]);
    }

    private function completeJournals(): void
    {
        if ($this->journalInputs === []) return;
        $actual = [];
        foreach ($this->repository->forRun((int) $this->run->run_id) as $row) {
            if ($row['stage_code'] !== $this->stage || $row['component_key'] !== 'source_observations'
                || ! isset($this->journalInputs[$row['slot_hash']])) continue;
            $this->repository->verify($row);
            $actual[$row['slot_hash']] = $row['payload_hash'];
        }
        ksort($actual, SORT_STRING); ksort($this->journalInputs, SORT_STRING);
        if ($actual !== $this->journalInputs) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_JOURNAL_COMPLETION_MISMATCH');
        $this->repository->capture((int) $this->run->run_id, $this->stage, 'completion', [
            'operation' => 'source-observation-journal-completion/v1', 'producer_operation' => $this->operation,
            'declared_slots' => array_keys($actual),
        ], [['scope' => 'THIS_INVOCATION_JOURNAL_ONLY_NOT_C06_COMPLETENESS', 'journal_hashes' => $actual]]);
    }
}
