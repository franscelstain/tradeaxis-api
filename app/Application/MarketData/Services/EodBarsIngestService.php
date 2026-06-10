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
    private $impactResolver;

    public function __construct(
        LocalFileEodBarsAdapter $localSourceAdapter,
        PublicApiEodBarsAdapter $apiSourceAdapter,
        TickerMasterRepository $tickers,
        EodArtifactRepository $artifacts,
        EodPublicationRepository $publications,
        EodBarsMutationImpactResolver $impactResolver = null
    ) {
        $this->localSourceAdapter = $localSourceAdapter;
        $this->apiSourceAdapter = $apiSourceAdapter;
        $this->tickers = $tickers;
        $this->artifacts = $artifacts;
        $this->publications = $publications;
        $this->impactResolver = $impactResolver;
    }

    public function ingest($run, $requestedDate, $sourceMode, $priorCurrentPublication = null)
    {
        $sourceRows = $this->acquireSourceRows($requestedDate, $sourceMode);
        $sourceAcquisition = $this->consumeSourceAcquisitionTelemetry($sourceMode);

        return $this->ingestAcquiredRows($run, $requestedDate, $sourceMode, $sourceRows, $sourceAcquisition, $priorCurrentPublication);
    }

    public function acquireSourceRows($requestedDate, $sourceMode, array $tickerCodes = null)
    {
        return $this->fetchSourceRows($requestedDate, $sourceMode, $tickerCodes);
    }

    public function ingestAcquiredRows($run, $requestedDate, $sourceMode, array $sourceRows, array $sourceAcquisition = null, $priorCurrentPublication = null)
    {
        if ($priorCurrentPublication && (int) $priorCurrentPublication->publication_id === (int) ($run->publication_id ?? 0)) {
            throw new \RuntimeException('Correction candidate publication cannot equal prior current publication.');
        }

        if (! $priorCurrentPublication && $this->publications->findCurrentPublicationForTradeDate($requestedDate)) {
            throw new \RuntimeException('Trade date '.$requestedDate.' sudah punya current publication. Correction/reseal wajib dipakai.');
        }

        if ($sourceRows === []) {
            $emptyContext = array_merge(
                $this->emptySourceAcquisitionContext($requestedDate, $sourceMode),
                $sourceAcquisition ?: []
            );
            $emptyContext['source_mode'] = $sourceMode;
            $emptyContext['trade_date'] = $requestedDate;
            $emptyContext['returned_row_count'] = 0;
            $emptyContext['accepted_row_count'] = 0;
            $emptyContext['rejected_row_count'] = 0;
            $emptyContext['invalid_row_count'] = 0;
            $emptyContext['source_final_status'] = 'FAILED';
            $emptyContext['final_reason_code'] = $this->noValidSourceDataReasonCode($sourceMode);
            $emptyContext['no_valid_data'] = true;
            $emptyContext['empty_response_blocked'] = true;

            throw new SourceAcquisitionException(
                'Source returned zero rows for requested trade_date; empty source output is not a valid ingest artifact.',
                $this->noValidSourceDataReasonCode($sourceMode),
                0,
                null,
                $emptyContext
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
                    'source' => $this->canonicalSourceForRow($row),
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

        $sourceAcquisition = $sourceAcquisition !== null
            ? $sourceAcquisition
            : $this->consumeSourceAcquisitionTelemetry($sourceMode);

        if (count($validRows) === 0) {
            $context = array_merge($sourceAcquisition, [
                'source_mode' => $sourceMode,
                'trade_date' => $requestedDate,
                'returned_row_count' => count($sourceRows),
                'accepted_row_count' => 0,
                'rejected_row_count' => count($invalidRows),
                'invalid_row_count' => count($invalidRows),
                'invalid_reason_summary' => $this->summarizeInvalidReasons($invalidRows),
                'invalid_rows_sample' => $this->invalidRowsSample($invalidRows),
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

        $barMutationSummary = $this->artifacts->replaceBars($requestedDate, $candidatePublication->publication_id, $run->run_id, $validRows, $invalidRows, $useHistory);
        if (! is_array($barMutationSummary)) {
            $barMutationSummary = $this->defaultBarMutationSummary($requestedDate, $candidatePublication->publication_id, $validRows, $useHistory);
        }

        $impact = $this->impactResolver
            ? $this->impactResolver->resolve($barMutationSummary, $requestedDate)
            : $this->defaultImpactSummary($barMutationSummary);

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
            'bar_mutation_summary' => $impact['bar_mutation_summary'],
            'indicator_impact_summary' => $impact['indicator_impact_summary'],
            'publication_impact_summary' => $impact['publication_impact_summary'],
        ];
    }

    public function ingestRecoveredRowsPartial($run, $requestedDate, $sourceMode, array $sourceRows, array $sourceAcquisition = null, $priorCurrentPublication = null)
    {
        if ($priorCurrentPublication && (int) $priorCurrentPublication->publication_id === (int) ($run->publication_id ?? 0)) {
            throw new \RuntimeException('Correction candidate publication cannot equal prior current publication.');
        }

        if (! $priorCurrentPublication && $this->publications->findCurrentPublicationForTradeDate($requestedDate)) {
            throw new \RuntimeException('AFFECTED_PUBLICATION_REQUIRES_CORRECTION: Trade date '.$requestedDate.' already has a current publication. Use correction/reseal before recovered row apply.');
        }

        if ($sourceRows === []) {
            throw new SourceAcquisitionException(
                'Recovered checkpoint retry returned zero rows; partial recovered apply cannot proceed.',
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
                'invalid_note' => 'Deterministic duplicate loser during recovered row apply.',
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
                    'source' => $this->canonicalSourceForRow($row),
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

        $sourceAcquisition = $sourceAcquisition !== null
            ? $sourceAcquisition
            : $this->consumeSourceAcquisitionTelemetry($sourceMode);

        if (count($validRows) === 0) {
            $context = array_merge($sourceAcquisition, [
                'source_mode' => $sourceMode,
                'trade_date' => $requestedDate,
                'returned_row_count' => count($sourceRows),
                'accepted_row_count' => 0,
                'rejected_row_count' => count($invalidRows),
                'invalid_row_count' => count($invalidRows),
                'invalid_reason_summary' => $this->summarizeInvalidReasons($invalidRows),
                'invalid_rows_sample' => $this->invalidRowsSample($invalidRows),
                'source_final_status' => 'FAILED',
                'final_reason_code' => $this->noValidSourceDataReasonCode($sourceMode),
                'no_valid_data' => true,
                'empty_artifact_blocked' => true,
            ]);

            throw new SourceAcquisitionException(
                'Recovered rows produced zero valid canonical bars; partial recovered apply is blocked.',
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

        $barMutationSummary = $this->artifacts->upsertBarsPartial($requestedDate, $candidatePublication->publication_id, $run->run_id, $validRows, $invalidRows, $useHistory);
        if (! is_array($barMutationSummary)) {
            $barMutationSummary = $this->defaultBarMutationSummary($requestedDate, $candidatePublication->publication_id, $validRows, $useHistory);
        }

        $impact = $this->impactResolver
            ? $this->impactResolver->resolve($barMutationSummary, $requestedDate)
            : $this->defaultImpactSummary($barMutationSummary);

        $applyState = (int) ($impact['bar_mutation_summary']['changed_bar_count'] ?? 0) > 0 ? 'APPLIED' : 'UNCHANGED';
        $sourceAcquisition = array_merge($sourceAcquisition, [
            'source_mode' => $sourceMode,
            'accepted_row_count' => count($validRows),
            'rejected_row_count' => count($invalidRows),
            'invalid_row_count' => count($invalidRows),
            'returned_row_count' => count($sourceRows),
            'recovered_row_count' => count($validRows),
            'recovered_row_apply_state' => $applyState,
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
            'bar_mutation_summary' => $impact['bar_mutation_summary'],
            'indicator_impact_summary' => $impact['indicator_impact_summary'],
            'publication_impact_summary' => $impact['publication_impact_summary'],
            'recovered_row_count' => count($validRows),
            'recovered_row_apply_state' => $applyState,
            'resume_recovered_apply_summary' => [
                'retried_failed_checkpoint_count' => (int) ($sourceAcquisition['failed_checkpoint_retried'] ?? $sourceAcquisition['retried_failed_checkpoint_count'] ?? 0),
                'retry_success_count' => (int) ($sourceAcquisition['retry_success_count'] ?? 0),
                'recovered_row_count' => count($validRows),
                'changed_bar_count' => (int) ($impact['bar_mutation_summary']['changed_bar_count'] ?? 0),
                'apply_state' => $applyState,
            ],
        ];
    }

    private function defaultBarMutationSummary($requestedDate, $publicationId, array $validRows, $useHistory)
    {
        $tickerIds = array_values(array_unique(array_map(function ($row) {
            return (int) ($row['ticker_id'] ?? 0);
        }, $validRows)));
        $tickerIds = array_values(array_filter($tickerIds));
        sort($tickerIds);

        return [
            'changed_bar_count' => count($tickerIds),
            'inserted_bar_count' => count($tickerIds),
            'updated_bar_count' => 0,
            'unchanged_bar_count' => 0,
            'removed_bar_count' => 0,
            'changed_ticker_count' => count($tickerIds),
            'changed_trade_date_count' => count($tickerIds) > 0 ? 1 : 0,
            'changed_ticker_ids' => $tickerIds,
            'changed_trade_dates' => count($tickerIds) > 0 ? [(string) $requestedDate] : [],
            'storage_target' => $useHistory ? 'eod_bars_history' : 'eod_bars',
            'trade_date' => (string) $requestedDate,
            'publication_id' => $publicationId !== null ? (int) $publicationId : null,
            'mutation_detection_version' => 'eod_bar_mutation_v1_default',
        ];
    }

    private function defaultImpactSummary(array $barMutationSummary)
    {
        $changedCount = (int) ($barMutationSummary['changed_bar_count'] ?? 0);

        return [
            'bar_mutation_summary' => $barMutationSummary,
            'indicator_impact_summary' => [
                'affected_ticker_count' => (int) ($barMutationSummary['changed_ticker_count'] ?? 0),
                'affected_trade_date_count' => (int) ($barMutationSummary['changed_trade_date_count'] ?? 0),
                'affected_start_date' => ($barMutationSummary['changed_trade_dates'][0] ?? null),
                'affected_end_date' => ($barMutationSummary['changed_trade_dates'][0] ?? null),
                'affected_trade_dates' => $barMutationSummary['changed_trade_dates'] ?? [],
                'affected_ticker_ids' => $barMutationSummary['changed_ticker_ids'] ?? [],
                'max_dependency_trading_days' => 0,
                'impact_reason' => $changedCount > 0 ? 'IMPACT_RESOLVER_NOT_BOUND' : 'UNCHANGED_BARS',
                'indicator_reprocess_state' => $changedCount > 0 ? 'PENDING_IMPACT_RESOLVER' : 'NOOP_UNCHANGED_BARS',
            ],
            'publication_impact_summary' => [
                'readable_publication_impacted' => false,
                'impacted_readable_trade_dates' => [],
                'republication_required' => false,
                'publication_impact_state' => 'NOOP',
            ],
        ];
    }



    private function summarizeInvalidReasons(array $invalidRows)
    {
        $summary = [];
        foreach ($invalidRows as $row) {
            $reasonCode = (string) ($row['invalid_reason_code'] ?? 'UNKNOWN_INVALID_ROW');
            $summary[$reasonCode] = ($summary[$reasonCode] ?? 0) + 1;
        }

        ksort($summary);
        return $summary;
    }

    private function invalidRowsSample(array $invalidRows)
    {
        return array_slice(array_map(function (array $row) {
            return [
                'ticker_id' => $row['ticker_id'] ?? null,
                'trade_date' => $row['trade_date'] ?? null,
                'source_row_ref' => $row['source_row_ref'] ?? null,
                'invalid_reason_code' => $row['invalid_reason_code'] ?? null,
                'invalid_note' => $row['invalid_note'] ?? null,
            ];
        }, $invalidRows), 0, 10);
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

    public function consumeSourceAcquisitionTelemetry($sourceMode)
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


    private function fetchSourceRows($requestedDate, $sourceMode, array $tickerCodes = null)
    {
        if ($sourceMode === 'api') {
            if ($tickerCodes === null) {
                $universe = $this->tickers->getUniverseForTradeDate($requestedDate);
                $tickerCodes = array_values(array_unique(array_filter(array_map(function ($row) {
                    return isset($row['ticker_code']) ? $row['ticker_code'] : null;
                }, $universe))));
            }

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
            'source' => $this->canonicalSourceForRow($row),
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

    private function canonicalSourceForRow(array $row)
    {
        $source = strtoupper(trim((string) ($row['canonical_source'] ?? $row['source_name'] ?? config('market_data.source.default_source_name'))));

        if ($source === '') {
            $source = strtoupper((string) config('market_data.source.default_source_name'));
        }

        return $this->canonicalSourceStorageCode($source);
    }

    private function canonicalSourceStorageCode($source)
    {
        $source = strtoupper(trim((string) $source));
        $source = preg_replace('/[^A-Z0-9_]+/', '_', $source);
        $source = trim((string) $source, '_');

        if ($source === '') {
            $source = strtoupper((string) config('market_data.source.default_source_name'));
        }

        $aliases = [
            'IDX_STOCK_SUMMARY_NO_TRADE_CLOSE_CARRY_FORWARD' => 'IDX_NO_TRADE_CARRY_FORWARD',
        ];

        if (isset($aliases[$source])) {
            return $aliases[$source];
        }

        if (strlen($source) <= 32) {
            return $source;
        }

        return substr($source, 0, 23).'_'.substr(hash('crc32b', $source), 0, 8);
    }
}
