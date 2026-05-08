<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;

class EodBarsIngestService
{
    private $localSourceAdapter;
    private $apiSourceAdapter;
    private $tickers;
    private $artifacts;
    private $publications;

    public function __construct(
        LocalFileEodBarsAdapter $localSourceAdapter,
        PublicApiEodBarsAdapter $apiSourceAdapter,
        TickerMasterRepository $tickers,
        EodArtifactRepository $artifacts,
        EodPublicationRepository $publications
    ) {
        $this->localSourceAdapter = $localSourceAdapter;
        $this->apiSourceAdapter = $apiSourceAdapter;
        $this->tickers = $tickers;
        $this->artifacts = $artifacts;
        $this->publications = $publications;
    }

    public function ingest($run, $requestedDate, $sourceMode, $priorCurrentPublication = null)
    {
        if ($priorCurrentPublication && (int) $priorCurrentPublication->publication_id === (int) ($run->publication_id ?? 0)) {
            throw new \RuntimeException('Correction candidate publication cannot equal prior current publication.');
        }

        if (! $priorCurrentPublication && $this->publications->findCurrentPublicationForTradeDate($requestedDate)) {
            throw new \RuntimeException('Trade date '.$requestedDate.' sudah punya current publication. Correction/reseal wajib dipakai.');
        }

        $sourceRows = $this->fetchSourceRows($requestedDate, $sourceMode);
        if ($sourceRows === []) {
            throw new SourceAcquisitionException(
                'Source returned zero rows for requested trade_date; empty source output is not a valid ingest artifact.',
                $this->noValidSourceDataReasonCode($sourceMode),
                0,
                null,
                $this->emptySourceAcquisitionContext($requestedDate, $sourceMode)
            );
        }

        $this->assertSingleDaySourceBoundary($requestedDate, $sourceMode, $sourceRows);
        $tickerMap = $this->tickers->resolveTickerIdsByCodes(array_column($sourceRows, 'ticker_code'));

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $deduped = [];
        $duplicateLosers = [];
        $invalidRows = [];
        foreach ($sourceRows as $row) {
            $tickerCode = (string) ($row['ticker_code'] ?? '');
            $tickerId = isset($tickerMap[$tickerCode]) ? $tickerMap[$tickerCode] : null;
            $row['ticker_id'] = $tickerId;

            if ($tickerId === null) {
                $invalidRows[] = $this->makeInvalidRow(
                    $run->run_id,
                    $row,
                    'BAR_TICKER_MAPPING_MISSING',
                    'ticker_code not found in ticker master: '.$tickerCode,
                    $now
                );
                continue;
            }

            $key = $row['trade_date'].'|'.$tickerId;

            if (! isset($deduped[$key])) {
                $deduped[$key] = $row;
                continue;
            }

            $winner = $this->choosePreferredRow($deduped[$key], $row);
            $loser = $winner === $row ? $deduped[$key] : $row;
            $deduped[$key] = $winner;
            $duplicateLosers[] = $loser + [
                'invalid_reason_code' => 'BAR_DUPLICATE_SOURCE_ROW',
                'invalid_note' => 'Deterministic duplicate loser during ingest.',
                'loser_of_trade_date' => $row['trade_date'],
                'loser_of_ticker_id' => $tickerId,
            ];
        }

        $validRows = [];
        $useHistory = $priorCurrentPublication !== null;

        foreach (array_values($deduped) as $row) {
            $validation = $this->validateCanonicalRow($row, $requestedDate);

            if ($validation['valid']) {
                $validRows[] = [
                    'trade_date' => $requestedDate,
                    'ticker_id' => $row['ticker_id'],
                    'open' => $row['open'],
                    'high' => $row['high'],
                    'low' => $row['low'],
                    'close' => $row['close'],
                    'volume' => $row['volume'],
                    'adj_close' => $row['adj_close'],
                    'source' => strtoupper($row['source_name']),
                    'run_id' => $run->run_id,
                    'created_at' => $now,
                ];
                continue;
            }

            $invalidRows[] = $this->makeInvalidRow($run->run_id, $row, $validation['reason_code'], $validation['note'], $now);
        }

        foreach ($duplicateLosers as $loser) {
            $invalidRows[] = $this->makeInvalidRow(
                $run->run_id,
                $loser,
                $loser['invalid_reason_code'],
                $loser['invalid_note'],
                $now,
                $loser['loser_of_trade_date'],
                $loser['loser_of_ticker_id']
            );
        }

        $sourceAcquisition = $this->consumeSourceAcquisitionTelemetry($sourceMode);

        if (count($validRows) === 0) {
            $context = array_merge($sourceAcquisition, [
                'source_mode' => $sourceMode,
                'trade_date' => $requestedDate,
                'returned_row_count' => count($sourceRows),
                'accepted_row_count' => 0,
                'rejected_row_count' => count($invalidRows),
                'invalid_row_count' => count($invalidRows),
                'source_final_status' => 'FAILED',
                'final_reason_code' => $this->noValidSourceDataReasonCode($sourceMode),
                'no_valid_data' => true,
                'empty_artifact_blocked' => true,
            ]);

            throw new SourceAcquisitionException(
                'Source rows produced zero valid canonical bars; empty bars artifact is blocked from publication.',
                $this->noValidSourceDataReasonCode($sourceMode),
                0,
                null,
                $context
            );
        }

        $candidatePublication = $this->publications->getOrCreateCandidatePublication(
            $run,
            $priorCurrentPublication ? $priorCurrentPublication->publication_id : null
        );

        $validRows = array_map(function (array $row) use ($candidatePublication) {
            $row['publication_id'] = $candidatePublication->publication_id;

            return $row;
        }, $validRows);

        $this->artifacts->replaceBars($requestedDate, $candidatePublication->publication_id, $run->run_id, $validRows, $invalidRows, $useHistory);

        $sourceAcquisition = array_merge($sourceAcquisition, [
            'source_mode' => $sourceMode,
            'accepted_row_count' => count($validRows),
            'rejected_row_count' => count($invalidRows),
            'invalid_row_count' => count($invalidRows),
            'returned_row_count' => count($sourceRows),
        ]);

        return [
            'publication_id' => (int) $candidatePublication->publication_id,
            'publication_version' => (int) $candidatePublication->publication_version,
            'bars_rows_written' => count($validRows),
            'invalid_bar_count' => count($invalidRows),
            'accepted_row_count' => count($validRows),
            'rejected_row_count' => count($invalidRows),
            'invalid_row_count' => count($invalidRows),
            'source_name' => strtoupper((string) ($sourceRows[0]['source_name'] ?? config('market_data.source.default_source_name'))),
            'storage_target' => $useHistory ? 'eod_bars_history' : 'eod_bars',
            'source_acquisition' => $sourceAcquisition,
        ];
    }


    private function noValidSourceDataReasonCode($sourceMode)
    {
        return in_array($sourceMode, ['manual_file', 'manual_entry'], true)
            ? 'RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS'
            : 'RUN_SOURCE_NO_VALID_DATA';
    }

    private function emptySourceAcquisitionContext($requestedDate, $sourceMode)
    {
        $telemetry = $this->consumeSourceAcquisitionTelemetry($sourceMode);

        return array_merge($telemetry, [
            'source_mode' => $sourceMode,
            'trade_date' => $requestedDate,
            'returned_row_count' => 0,
            'accepted_row_count' => 0,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'source_final_status' => 'FAILED',
            'final_reason_code' => $this->noValidSourceDataReasonCode($sourceMode),
            'no_valid_data' => true,
            'empty_response_blocked' => true,
        ]);
    }

    private function consumeSourceAcquisitionTelemetry($sourceMode)
    {
        if ($sourceMode === 'api') {
            $telemetry = $this->apiSourceAdapter->consumeLastAcquisitionTelemetry();

            return is_array($telemetry) ? $telemetry : [];
        }

        if (in_array($sourceMode, ['manual_file', 'manual_entry'], true)
            && method_exists($this->localSourceAdapter, 'consumeLastAcquisitionTelemetry')) {
            $telemetry = $this->localSourceAdapter->consumeLastAcquisitionTelemetry();

            return is_array($telemetry) ? $telemetry : [];
        }

        return [];
    }


    private function fetchSourceRows($requestedDate, $sourceMode)
    {
        if ($sourceMode === 'api') {
            $universe = $this->tickers->getUniverseForTradeDate($requestedDate);
            $tickerCodes = array_values(array_unique(array_filter(array_map(function ($row) {
                return isset($row['ticker_code']) ? $row['ticker_code'] : null;
            }, $universe))));

            return $this->apiSourceAdapter->fetchOrLoadEodBars($requestedDate, $sourceMode, $tickerCodes);
        }

        return $this->localSourceAdapter->fetchOrLoadEodBars($requestedDate, $sourceMode);
    }

    private function assertSingleDaySourceBoundary($requestedDate, $sourceMode, array $sourceRows)
    {
        if ($sourceRows === []) {
            return;
        }

        $seenTradeDates = [];
        $seenSourceNames = [];

        foreach ($sourceRows as $row) {
            $rowTradeDate = isset($row['trade_date']) ? (string) $row['trade_date'] : null;
            if ($rowTradeDate !== null && $rowTradeDate !== '') {
                $seenTradeDates[$rowTradeDate] = true;
            }

            $rowSourceName = isset($row['source_name']) ? strtoupper(trim((string) $row['source_name'])) : null;
            if ($rowSourceName !== null && $rowSourceName !== '') {
                $seenSourceNames[$rowSourceName] = true;
            }
        }

        if (count($seenTradeDates) > 1 || (count($seenTradeDates) === 1 && ! isset($seenTradeDates[$requestedDate]))) {
            throw new SourceAcquisitionException(
                'Single-day ingest received source rows outside the requested trade_date boundary.',
                'RUN_STALE_DATA',
                0,
                null,
                [
                    'requested_date' => $requestedDate,
                    'seen_trade_dates' => array_keys($seenTradeDates),
                ]
            );
        }

        if (count($seenSourceNames) > 1) {
            throw new SourceAcquisitionException(
                'Single-day ingest received mixed source_name rows within one run boundary.',
                'RUN_SOURCE_RESPONSE_CHANGED',
                0,
                null,
                [
                    'requested_date' => $requestedDate,
                    'seen_source_names' => array_keys($seenSourceNames),
                ]
            );
        }

        if (in_array($sourceMode, ['manual_file', 'manual_entry'], true) && count($seenSourceNames) === 1 && ! isset($seenSourceNames['MANUAL_FILE']) && ! isset($seenSourceNames['LOCAL_FILE'])) {
            throw new \RuntimeException('Manual single-day ingest received unexpected source_name outside the manual boundary.');
        }
    }

    private function choosePreferredRow(array $left, array $right)
    {
        $leftCaptured = Carbon::parse($left['captured_at'])->timestamp;
        $rightCaptured = Carbon::parse($right['captured_at'])->timestamp;

        if ($leftCaptured !== $rightCaptured) {
            return $leftCaptured > $rightCaptured ? $left : $right;
        }

        $leftRef = (string) ($left['source_row_ref'] ?? '');
        $rightRef = (string) ($right['source_row_ref'] ?? '');

        return strcmp($leftRef, $rightRef) >= 0 ? $left : $right;
    }

    private function validateCanonicalRow(array $row, $requestedDate)
    {
        foreach (['ticker_code', 'trade_date', 'open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! isset($row[$field]) || $row[$field] === '' || $row[$field] === null) {
                return ['valid' => false, 'reason_code' => 'BAR_MISSING_REQUIRED_FIELD', 'note' => 'Missing required field: '.$field];
            }
        }

        if ($row['trade_date'] !== $requestedDate) {
            return ['valid' => false, 'reason_code' => 'BAR_MISSING_REQUIRED_FIELD', 'note' => 'trade_date mismatch against requested_date'];
        }

        foreach (['open', 'high', 'low', 'close'] as $field) {
            if ((float) $row[$field] <= 0) {
                return ['valid' => false, 'reason_code' => 'BAR_NON_POSITIVE_PRICE', 'note' => 'Non-positive price at '.$field];
            }
        }

        if ((int) $row['volume'] < 0) {
            return ['valid' => false, 'reason_code' => 'BAR_NEGATIVE_VOLUME', 'note' => 'Negative volume'];
        }

        if ((float) $row['high'] < (float) $row['low']
            || (float) $row['high'] < (float) $row['open']
            || (float) $row['high'] < (float) $row['close']
            || (float) $row['low'] > (float) $row['open']
            || (float) $row['low'] > (float) $row['close']) {
            return ['valid' => false, 'reason_code' => 'BAR_INVALID_OHLC_ORDER', 'note' => 'OHLC ordering invalid'];
        }

        return ['valid' => true, 'reason_code' => null, 'note' => null];
    }

    private function makeInvalidRow($runId, array $row, $reasonCode, $note, $now, $winnerTradeDate = null, $winnerTickerId = null)
    {
        return [
            'trade_date' => $row['trade_date'] ?? null,
            'ticker_id' => $row['ticker_id'] ?? null,
            'run_id' => $runId,
            'source' => strtoupper((string) ($row['source_name'] ?? config('market_data.source.default_source_name'))),
            'source_row_ref' => (string) ($row['source_row_ref'] ?? ''),
            'open' => isset($row['open']) ? (float) $row['open'] : null,
            'high' => isset($row['high']) ? (float) $row['high'] : null,
            'low' => isset($row['low']) ? (float) $row['low'] : null,
            'close' => isset($row['close']) ? (float) $row['close'] : null,
            'volume' => isset($row['volume']) ? (int) $row['volume'] : null,
            'adj_close' => isset($row['adj_close']) && $row['adj_close'] !== '' ? (float) $row['adj_close'] : null,
            'invalid_reason_code' => $reasonCode,
            'invalid_note' => $note,
            'loser_of_trade_date' => $winnerTradeDate,
            'loser_of_ticker_id' => $winnerTickerId,
            'created_at' => $now,
        ];
    }
}
