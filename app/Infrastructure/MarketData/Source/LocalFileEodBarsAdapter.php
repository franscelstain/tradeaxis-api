<?php

namespace App\Infrastructure\MarketData\Source;

use App\Application\MarketData\Ports\ManualEodBarsSource;
use App\Application\MarketData\Ports\SourceObservationRecorder;
use App\Application\MarketData\Services\ManualSourceInputContext;
use App\Infrastructure\MarketData\Observation\InMemorySourceObservationRecorder;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LocalFileEodBarsAdapter implements ManualEodBarsSource
{
    private $lastAcquisitionTelemetry = [];
    private $csvFileIndexCache = [];
    private $jsonFileIndexCache = [];
    private $observations;
    private $inputContext;

    public function __construct(SourceObservationRecorder $observations = null, ManualSourceInputContext $inputContext = null)
    {
        $this->observations = $observations ?: $this->defaultObservationRecorder();
        $this->inputContext = $inputContext ?: app(ManualSourceInputContext::class);
    }

    public function fetchOrLoadEodBars($tradeDate, $sourceMode, array $tickerCodes = [], array $context = [])
    {
        $this->lastAcquisitionTelemetry = [];

        if (! in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            throw $this->manualFileException(
                'Source mode '.$sourceMode.' belum diimplementasikan. Gunakan manual_file atau manual_entry.',
                'RUN_SOURCE_MODE_UNSUPPORTED',
                ['source_mode_requested' => $sourceMode]
            );
        }

        try {
            $explicitInputFile = $this->resolveExplicitInputFilePath();
        } catch (SourceAcquisitionException $e) {
            $this->observations->recordTransportFailure(
                $this->fileObservationEnvelope($this->inputContext->path(), $tradeDate, null, $context),
                $e->reasonCode()
            );
            throw $e;
        }
        if ($explicitInputFile !== null) {
            return $this->loadObservedFile($explicitInputFile, $tradeDate, $context);
        }

        $basePath = base_path(config('market_data.source.local_directory'));
        $jsonPath = $basePath.'/'.str_replace('{date}', $tradeDate, config('market_data.source.file_template_json'));
        $csvPath = $basePath.'/'.str_replace('{date}', $tradeDate, config('market_data.source.file_template_csv'));

        if (file_exists($jsonPath)) {
            return $this->loadObservedFile($jsonPath, $tradeDate, $context);
        }

        if (file_exists($csvPath)) {
            return $this->loadObservedFile($csvPath, $tradeDate, $context);
        }

        $this->observations->recordTransportFailure(
            $this->fileObservationEnvelope($jsonPath.'|'.$csvPath, $tradeDate, null, $context),
            'RUN_SOURCE_MANUAL_FILE_NOT_FOUND'
        );

        throw $this->manualFileException(
            'Sumber bars lokal untuk '.$tradeDate.' tidak ditemukan pada path JSON/CSV yang dikonfigurasi.',
            'RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
            [
                'trade_date' => $tradeDate,
                'json_path' => $jsonPath,
                'csv_path' => $csvPath,
            ]
        );
    }

    private function resolveExplicitInputFilePath()
    {
        $configured = trim((string) $this->inputContext->path());
        if ($configured == '') {
            return null;
        }

        $candidate = $this->isAbsolutePath($configured) ? $configured : base_path($configured);

        if (! file_exists($candidate)) {
            throw $this->manualFileException(
                'Explicit local input file not found: '.$configured,
                'RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
                [
                    'source_input_file' => $configured,
                    'resolved_source_input_file' => $candidate,
                ]
            );
        }

        return $candidate;
    }

    private function loadExplicitInputFile($path, $tradeDate)
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            return $this->loadJson($path, $tradeDate);
        }

        if ($extension === 'csv') {
            return $this->loadCsv($path, $tradeDate);
        }

        throw $this->manualFileException(
            'Explicit local input file must use .json or .csv extension.',
            'RUN_SOURCE_MANUAL_FILE_MALFORMED',
            ['source_input_file' => $path]
        );
    }

    private function loadObservedFile($path, $tradeDate, array $context)
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $payload = file_get_contents($path);
        if ($payload === false) {
            $this->observations->recordTransportFailure(
                $this->fileObservationEnvelope($path, $tradeDate, null, $context),
                'RUN_SOURCE_MANUAL_FILE_NOT_FOUND'
            );
            throw $this->manualFileException('Manual source file could not be read.', 'RUN_SOURCE_MANUAL_FILE_NOT_FOUND');
        }

        $capture = $this->persistCapture(
            $this->fileObservationEnvelope($path, $tradeDate, $payload, $context, $extension)
        );

        try {
            $rows = $this->loadExplicitInputFile($path, $tradeDate);
        } catch (SourceAcquisitionException $e) {
            $this->persistOutcome($capture, 'REJECTED', $e->reasonCode());
            throw $e;
        }

        $outcome = $this->persistAcceptedRows($capture, $rows);

        return array_map(function (array $row) use ($capture, $outcome) {
            return array_merge($row, [
                'source_observation_id' => $outcome['source_observation_id'],
                'source_capture_observation_id' => $capture['source_observation_id'],
                'source_payload_hash' => $capture['payload_hash'] ?? null,
                'source_schema_fingerprint' => $capture['schema_fingerprint'] ?? null,
                'source_observation_persisted' => ! empty($outcome['persisted']),
            ]);
        }, $rows);
    }

    private function fileObservationEnvelope($path, $tradeDate, $payload, array $context, $format = null)
    {
        $resolvedPath = (string) $path;

        return array_merge($context, [
            'requested_trade_date' => $tradeDate,
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_priority' => 'SECONDARY_CONTROLLED_RECOVERY',
            'active_source_decision' => 'manual_file',
            'retry_attempt_count' => 0,
            'provider' => null,
            'sanitized_request_identity' => 'local-file:'.hash('sha256', $resolvedPath),
            'content_type' => $format === 'csv' ? 'text/csv' : ($format === 'json' ? 'application/json' : null),
            'acquired_at' => Carbon::now(config('market_data.platform.timezone'))->toDateTimeString(),
            'adapter_version' => (string) config('market_data.source.manual.adapter_version', 'local_file_eod_v1'),
            'provider_schema_version' => (string) config('market_data.source.manual.schema_version', 'manual_file_schema_v1'),
            'payload' => $payload,
        ]);
    }

    public function consumeLastAcquisitionTelemetry()
    {
        $telemetry = $this->lastAcquisitionTelemetry;
        $this->lastAcquisitionTelemetry = [];

        return $telemetry;
    }

    private function rememberManualFileTelemetry($path, $tradeDate, $format, array $rows, array $context = [])
    {
        $sourceFileHash = is_file($path) ? hash_file('sha256', $path) : null;
        $sourceFileSize = is_file($path) ? filesize($path) : null;
        $rowCount = count($rows);

        $this->lastAcquisitionTelemetry = array_merge([
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_priority' => 'SECONDARY_CONTROLLED_RECOVERY',
            'active_source_decision' => 'manual_file',
            'retry_attempt_count' => 0,
            'provider' => null,
            'input_file' => $path,
            'source_input_file' => $path,
            'source_file_format' => $format,
            'source_file_hash' => $sourceFileHash,
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => $sourceFileSize,
            'source_file_row_count' => $rowCount,
            'returned_row_count' => $rowCount,
            'accepted_row_count' => $rowCount,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'trade_date' => $tradeDate,
            'source_final_status' => 'SUCCESS',
            'final_reason_code' => null,
            'failure_class_summary' => [],
        ], $context);
    }

    private function rememberManualFileFailureTelemetry($path, $tradeDate, $format, $reasonCode, array $context = [])
    {
        $sourceFileHash = is_file($path) ? hash_file('sha256', $path) : null;
        $sourceFileSize = is_file($path) ? filesize($path) : null;
        $rowCount = array_key_exists('source_file_row_count', $context) ? $context['source_file_row_count'] : null;

        $this->lastAcquisitionTelemetry = array_merge([
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_priority' => 'SECONDARY_CONTROLLED_RECOVERY',
            'active_source_decision' => 'manual_file',
            'retry_attempt_count' => 0,
            'provider' => null,
            'input_file' => $path,
            'source_input_file' => $path,
            'source_file_format' => $format,
            'source_file_hash' => $sourceFileHash,
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => $sourceFileSize,
            'source_file_row_count' => $rowCount,
            'returned_row_count' => 0,
            'accepted_row_count' => 0,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'trade_date' => $tradeDate,
            'source_final_status' => 'FAILED',
            'final_reason_code' => $reasonCode,
            'failure_class_summary' => ['NON_TRANSIENT' => 1],
            'manual_file_empty_blocked' => true,
        ], $context);
    }

    private function blockEmptyManualFile($path, $tradeDate, $format, $reasonCode, array $context = [])
    {
        $this->rememberManualFileFailureTelemetry($path, $tradeDate, $format, $reasonCode, $context);

        throw $this->manualFileException(
            'Manual file source contains no valid data rows and is blocked from import/promote.',
            $reasonCode,
            $this->lastAcquisitionTelemetry
        );
    }

    private function manualFileException($message, $reasonCode, array $context = [])
    {
        return new SourceAcquisitionException($message, $reasonCode, 0, null, array_merge([
            'source_mode' => 'manual_file',
            'source_name' => 'LOCAL_FILE',
            'source_priority' => 'SECONDARY_CONTROLLED_RECOVERY',
            'active_source_decision' => 'manual_file',
            'retry_attempt_count' => 0,
            'failure_class_summary' => ['NON_TRANSIENT' => 1],
            'provider' => null,
            'final_reason_code' => $reasonCode,
            'source_final_status' => 'FAILED',
        ], $context));
    }

    private function isAbsolutePath($path)
    {
        return Str::startsWith($path, ['/','\\'])
            || preg_match('~^[A-Za-z]:[\\/]~', $path) === 1;
    }

    private function loadJson($path, $tradeDate)
    {
        if (! is_readable($path)) {
            throw $this->manualFileException(
                'File JSON bars lokal tidak dapat dibaca.',
                'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
                ['source_input_file' => $path]
            );
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw $this->manualFileException(
                'File JSON bars lokal tidak dapat dibuka.',
                'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
                ['source_input_file' => $path]
            );
        }

        $index = $this->jsonFileIndex($path, $contents);
        $rawRows = $this->rowsForRequestedDate($index, $tradeDate);
        $rows = array_map(function ($entry) use ($tradeDate) {
            return $this->normalizeRow($entry['row'], $tradeDate, $entry['source_row_ref']);
        }, $rawRows);

        if (count($rows) === 0) {
            $this->blockEmptyManualFile($path, $tradeDate, 'json', 'RUN_SOURCE_MANUAL_FILE_EMPTY', [
                'source_file_row_count' => 0,
                'source_file_total_row_count' => $index['total_row_count'],
                'source_file_filtered_out_row_count' => $index['total_row_count'],
                'requested_trade_date' => $tradeDate,
            ]);
        }

        $this->rememberManualFileTelemetry($path, $tradeDate, 'json', $rows, $this->filterTelemetryContext($index, $rows, $tradeDate));

        return $rows;
    }

    private function loadCsv($path, $tradeDate)
    {
        $index = $this->csvFileIndex($path);
        $rawRows = $this->rowsForRequestedDate($index, $tradeDate);

        $rows = array_map(function ($entry) use ($tradeDate) {
            return $this->normalizeRow($entry['row'], $tradeDate, $entry['source_row_ref']);
        }, $rawRows);

        if (count($rows) === 0) {
            $this->blockEmptyManualFile($path, $tradeDate, 'csv', 'RUN_SOURCE_MANUAL_FILE_EMPTY', [
                'source_file_row_count' => 0,
                'source_file_total_row_count' => $index['total_row_count'],
                'source_file_filtered_out_row_count' => $index['total_row_count'],
                'requested_trade_date' => $tradeDate,
            ]);
        }

        $this->rememberManualFileTelemetry($path, $tradeDate, 'csv', $rows, $this->filterTelemetryContext($index, $rows, $tradeDate));

        return $rows;
    }

    private function jsonFileIndex($path, $contents)
    {
        if (isset($this->jsonFileIndexCache[$path])) {
            return $this->jsonFileIndexCache[$path];
        }

        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            throw $this->manualFileException(
                'File JSON bars lokal tidak valid.',
                'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                ['source_input_file' => $path]
            );
        }

        $index = $this->emptyFileIndex();
        foreach ($payload as $rowIndex => $row) {
            if (! is_array($row)) {
                throw $this->manualFileException(
                    'File JSON bars lokal berisi row yang bukan object/array.',
                    'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                    [
                        'source_input_file' => $path,
                        'source_row_ref' => 'json:'.($rowIndex + 1),
                    ]
                );
            }

            $this->addRowToFileIndex($index, $row, 'json:'.($rowIndex + 1));
        }

        $this->jsonFileIndexCache[$path] = $index;

        return $index;
    }

    private function csvFileIndex($path)
    {
        if (isset($this->csvFileIndexCache[$path])) {
            return $this->csvFileIndexCache[$path];
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw $this->manualFileException(
                'File CSV bars lokal tidak dapat dibuka.',
                'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
                ['source_input_file' => $path]
            );
        }

        $header = fgetcsv($handle);

        if (! is_array($header)) {
            fclose($handle);
            throw $this->manualFileException(
                'Header CSV bars lokal tidak ditemukan.',
                'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                ['source_input_file' => $path]
            );
        }

        $normalizedHeader = array_map(function ($item) {
            return Str::snake(trim($item));
        }, $header);

        $required = ['ticker_code', 'trade_date', 'open', 'high', 'low', 'close', 'volume'];
        foreach ($required as $column) {
            if (! in_array($column, $normalizedHeader, true)) {
                fclose($handle);
                throw $this->manualFileException(
                    'Header CSV bars lokal tidak lengkap. Kolom wajib hilang: '.$column,
                    'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                    [
                        'source_input_file' => $path,
                        'missing_column' => $column,
                    ]
                );
            }
        }

        $index = $this->emptyFileIndex();
        $line = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            if (count($data) !== count($normalizedHeader)) {
                fclose($handle);
                throw $this->manualFileException(
                    'Row CSV bars lokal memiliki jumlah kolom yang tidak cocok dengan header.',
                    'RUN_SOURCE_MANUAL_FILE_MALFORMED',
                    [
                        'source_input_file' => $path,
                        'source_row_ref' => 'csv:'.$line,
                    ]
                );
            }

            $this->addRowToFileIndex($index, array_combine($normalizedHeader, $data), 'csv:'.$line);
        }

        fclose($handle);

        $this->csvFileIndexCache[$path] = $index;

        return $index;
    }

    private function emptyFileIndex()
    {
        return [
            'rows_by_trade_date' => [],
            'fallback_rows' => [],
            'total_row_count' => 0,
        ];
    }

    private function addRowToFileIndex(array &$index, array $row, $sourceRowRef)
    {
        $index['total_row_count']++;
        $entry = [
            'row' => $row,
            'source_row_ref' => $sourceRowRef,
        ];
        $tradeDate = $this->sourceRowTradeDateKey($row);

        if ($tradeDate === null) {
            $index['fallback_rows'][] = $entry;
            return;
        }

        if (! isset($index['rows_by_trade_date'][$tradeDate])) {
            $index['rows_by_trade_date'][$tradeDate] = [];
        }

        $index['rows_by_trade_date'][$tradeDate][] = $entry;
    }

    private function sourceRowTradeDateKey(array $row)
    {
        if (! array_key_exists('trade_date', $row) || $row['trade_date'] === null) {
            return null;
        }

        $tradeDate = trim((string) $row['trade_date']);

        return $tradeDate === '' ? null : $tradeDate;
    }

    private function rowsForRequestedDate(array $index, $tradeDate)
    {
        return array_values(array_merge(
            $index['rows_by_trade_date'][(string) $tradeDate] ?? [],
            $index['fallback_rows'] ?? []
        ));
    }

    private function filterTelemetryContext(array $index, array $rows, $tradeDate)
    {
        return [
            'source_file_total_row_count' => (int) $index['total_row_count'],
            'source_file_filtered_out_row_count' => max(0, (int) $index['total_row_count'] - count($rows)),
            'requested_trade_date' => $tradeDate,
        ];
    }

    private function normalizeRow(array $row, $tradeDate, $fallbackRowRef)
    {
        $capturedAt = isset($row['captured_at']) && $row['captured_at']
            ? Carbon::parse($row['captured_at'])->toDateTimeString()
            : Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        return [
            'ticker_code' => isset($row['ticker_code']) ? Str::upper(trim($row['ticker_code'])) : null,
            'trade_date' => $row['trade_date'] ?? $tradeDate,
            'open' => array_key_exists('open', $row) ? $row['open'] : null,
            'high' => array_key_exists('high', $row) ? $row['high'] : null,
            'low' => array_key_exists('low', $row) ? $row['low'] : null,
            'close' => array_key_exists('close', $row) ? $row['close'] : null,
            'volume' => array_key_exists('volume', $row) ? $row['volume'] : null,
            'adj_close' => array_key_exists('adj_close', $row) ? $row['adj_close'] : null,
            'source_name' => 'LOCAL_FILE',
            'source_row_ref' => $row['source_row_ref'] ?? $fallbackRowRef,
            'captured_at' => $capturedAt,
        ];
    }

    private function defaultObservationRecorder()
    {
        try {
            if (Schema::hasTable('md_source_observations')) {
                return new SourceObservationRepository();
            }
        } catch (\Throwable $e) {
            // Isolated adapter tests intentionally have no persistence foundation.
        }

        return new InMemorySourceObservationRecorder();
    }

    private function persistCapture(array $envelope)
    {
        try {
            return $this->observations->capture($envelope);
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Manual source bytes could not be persisted before parsing.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }

    private function persistOutcome(array $capture, $state, $reasonCode = null)
    {
        try {
            return $this->observations->recordOutcome($capture, $state, $reasonCode);
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Manual source observation outcome could not be persisted immutably.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }

    private function persistAcceptedRows(array $capture, array $rows)
    {
        try {
            return $this->observations->recordAcceptedRows($capture, $rows);
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Manual normalized source rows could not be persisted and compared immutably.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }
}
