<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;

/**
 * Executes the AS_KNOWN canonical RAW assertion on the production canonicalizer without leaving
 * replay artifacts in production projection tables.
 *
 * The transaction is intentionally rolled back after output capture. The durable replay result is
 * written by ReplayVerificationService outside this isolation boundary. This service proves the
 * executed canonicalization path; it does not claim to be a sealed publication and never changes
 * the current publication pointer.
 */
final class AsKnownReplayExecutionService
{
    public const EXECUTION_SCOPE = 'CANONICAL_RAW';

    private const BAR_HASH_COLUMNS = [
        'trade_date', 'ticker_id', 'listing_id', 'open', 'high', 'low', 'close', 'volume',
        'adj_close', 'source', 'previous_close', 'traded_value_idr_actual', 'trade_count_actual',
        'board_code', 'session_code', 'canonicalization_version', 'price_product_code',
        'quality_state', 'config_snapshot_id', 'source_scale_state', 'source_scale_assessment_id',
    ];

    private $runs;
    private $bars;
    private $artifacts;
    private $observations;
    private $hashes;

    public function __construct(
        EodRunRepository $runs = null,
        EodBarsIngestService $bars = null,
        EodArtifactRepository $artifacts = null,
        SourceObservationRepository $observations = null,
        DeterministicHashService $hashes = null
    ) {
        $this->runs = $runs ?: app(EodRunRepository::class);
        $this->bars = $bars ?: app(EodBarsIngestService::class);
        $this->artifacts = $artifacts ?: app(EodArtifactRepository::class);
        $this->observations = $observations ?: new SourceObservationRepository();
        $this->hashes = $hashes ?: new DeterministicHashService();
    }

    public function execute($tradeDate, $knowledgeCutoff, array $boundSnapshot): array
    {
        $knowledgeCutoff = trim((string) $knowledgeCutoff);
        if ($knowledgeCutoff === '') {
            throw new \RuntimeException('REPLAY_KNOWLEDGE_CUTOFF_REQUIRED: AS_KNOWN execution requires knowledge_cutoff.');
        }

        $sourceRows = $this->observations->acquisitionRowsAsKnown($tradeDate, $knowledgeCutoff);
        $observations = (array) (($boundSnapshot['source_observation_manifest']['observations'] ?? []));
        $sourceMode = $this->resolveSourceMode($sourceRows, $observations);

        DB::beginTransaction();
        try {
            $run = $this->runs->createAsKnownReplayRun($tradeDate, $knowledgeCutoff, $sourceMode);
            try {
                $result = $this->bars->ingestAcquiredRows(
                    $run,
                    $tradeDate,
                    $sourceMode,
                    $sourceRows,
                    [
                        'source_mode' => $sourceMode,
                        'source_name' => 'IMMUTABLE_AS_KNOWN_OBSERVATIONS',
                        'returned_row_count' => count($sourceRows),
                        'replay_mode' => ReplayMode::AS_KNOWN,
                        'knowledge_cutoff' => $knowledgeCutoff,
                        'replay_isolation' => true,
                    ],
                    null,
                    true
                );
            } catch (SourceAcquisitionException $e) {
                return $this->blockedResult(
                    $tradeDate,
                    $knowledgeCutoff,
                    $boundSnapshot,
                    $sourceMode,
                    $e->reasonCode(),
                    $e->context()
                );
            }

            $canonicalRows = $this->artifacts->loadReplayIsolationBarsForRun(
                $tradeDate,
                (int) $run->run_id,
                self::BAR_HASH_COLUMNS
            );
            $reasonCounts = $this->artifacts->loadReplayIsolationInvalidReasonCountsForRun(
                $tradeDate,
                (int) $run->run_id
            );

            return [
                'execution_scope' => self::EXECUTION_SCOPE,
                'execution_state' => 'SUCCESS',
                'trade_date' => (string) $tradeDate,
                'knowledge_cutoff' => $knowledgeCutoff,
                'source_mode' => $sourceMode,
                'source_row_count' => count($sourceRows),
                'canonical_row_count' => count($canonicalRows),
                'invalid_row_count' => (int) ($result['invalid_bar_count'] ?? 0),
                'canonical_output_hash' => $this->hashes->hashRows($canonicalRows, self::BAR_HASH_COLUMNS),
                'canonical_rows' => $canonicalRows,
                'reason_code_counts' => $reasonCounts,
                'source_observation_manifest_hash' => (string) ($boundSnapshot['source_observation_manifest_hash'] ?? ''),
                'canonical_raw_input_hash' => (string) ($boundSnapshot['canonical_raw_input_hash'] ?? ''),
                'config_snapshot_id' => isset($boundSnapshot['config_snapshot_id']) ? (int) $boundSnapshot['config_snapshot_id'] : null,
                'config_snapshot_hash' => (string) ($boundSnapshot['config_snapshot_hash'] ?? ''),
                'replay_isolation' => true,
                'durable_projection_mutation' => false,
            ];
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
    }

    private function blockedResult($tradeDate, $knowledgeCutoff, array $boundSnapshot, $sourceMode, $reasonCode, array $context): array
    {
        return [
            'execution_scope' => self::EXECUTION_SCOPE,
            'execution_state' => 'BLOCKED',
            'trade_date' => (string) $tradeDate,
            'knowledge_cutoff' => (string) $knowledgeCutoff,
            'source_mode' => (string) $sourceMode,
            'source_row_count' => (int) ($context['returned_row_count'] ?? 0),
            'canonical_row_count' => 0,
            'invalid_row_count' => (int) ($context['invalid_row_count'] ?? 0),
            'canonical_output_hash' => null,
            'canonical_rows' => [],
            'reason_code_counts' => [$reasonCode => 1],
            'final_reason_code' => (string) $reasonCode,
            'source_observation_manifest_hash' => (string) ($boundSnapshot['source_observation_manifest_hash'] ?? ''),
            'canonical_raw_input_hash' => (string) ($boundSnapshot['canonical_raw_input_hash'] ?? ''),
            'config_snapshot_id' => isset($boundSnapshot['config_snapshot_id']) ? (int) $boundSnapshot['config_snapshot_id'] : null,
            'config_snapshot_hash' => (string) ($boundSnapshot['config_snapshot_hash'] ?? ''),
            'replay_isolation' => true,
            'durable_projection_mutation' => false,
        ];
    }

    private function resolveSourceMode(array $sourceRows, array $observationRows): string
    {
        $modes = [];
        foreach ($sourceRows as $row) {
            $mode = trim((string) ($row['source_mode'] ?? ''));
            if ($mode !== '') {
                $modes[$mode] = true;
            }
        }
        foreach ($observationRows as $row) {
            $mode = trim((string) ($row['source_mode'] ?? ''));
            if ($mode !== '') {
                $modes[$mode] = true;
            }
        }
        if (count($modes) > 1) {
            throw new \RuntimeException('REPLAY_SOURCE_MODE_AMBIGUOUS: AS_KNOWN fixture spans multiple source modes.');
        }

        return $modes === [] ? 'api' : (string) array_keys($modes)[0];
    }
}
