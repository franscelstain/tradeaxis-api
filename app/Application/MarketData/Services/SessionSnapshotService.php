<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\MarketData\Source\LocalFileSessionSnapshotAdapter;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EligibilitySnapshotScopeRepository;
use App\Infrastructure\Persistence\MarketData\SessionSnapshotRepository;
use Carbon\Carbon;

class SessionSnapshotService
{
    private $publications;
    private $runs;
    private $scope;
    private $snapshots;
    private $adapter;

    public function __construct(
        EodPublicationRepository $publications,
        EodRunRepository $runs,
        EligibilitySnapshotScopeRepository $scope,
        SessionSnapshotRepository $snapshots,
        LocalFileSessionSnapshotAdapter $adapter
    ) {
        $this->publications = $publications;
        $this->runs = $runs;
        $this->scope = $scope;
        $this->snapshots = $snapshots;
        $this->adapter = $adapter;
    }

    public function capture($tradeDate, $snapshotSlot, $sourceMode, $inputFile, $outputDir = null)
    {
        if ($sourceMode !== 'manual_file') {
            throw new \RuntimeException('Session snapshot minimum runtime currently supports source_mode=manual_file only.');
        }

        $publication = $this->publications->findCurrentPublicationForTradeDate($tradeDate);
        if (! $publication) {
            throw new \RuntimeException('Session snapshot requires a readable current publication for trade date '.$tradeDate.'.');
        }

        $run = $this->runs->findByRunId($publication->run_id);
        if (! $run) {
            throw new \RuntimeException('Owning run not found for session snapshot publication context.');
        }

        $scope = $this->scope->getScopeForTradeDate($tradeDate);
        if (empty($scope)) {
            throw new \RuntimeException('Session snapshot scope is empty for trade date '.$tradeDate.'.');
        }

        $rows = $this->adapter->loadRows($inputFile);
        $rowsByCode = [];
        foreach ($rows as $row) {
            $rowsByCode[$row['ticker_code']] = $row;
        }

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $insert = [];
        $captured = 0;
        $skipped = 0;
        foreach ($scope as $item) {
            if (! isset($rowsByCode[$item['ticker_code']])) {
                $skipped++;
                continue;
            }

            $sourceRow = $rowsByCode[$item['ticker_code']];
            $insert[] = [
                'trade_date' => $tradeDate,
                'snapshot_slot' => $snapshotSlot,
                'ticker_id' => $item['ticker_id'],
                'captured_at' => $sourceRow['captured_at'],
                'last_price' => $sourceRow['last_price'],
                'prev_close' => $sourceRow['prev_close'],
                'chg_pct' => $sourceRow['chg_pct'],
                'volume' => $sourceRow['volume'],
                'day_high' => $sourceRow['day_high'],
                'day_low' => $sourceRow['day_low'],
                'source' => $sourceMode,
                'run_id' => $run->run_id,
                'reason_code' => null,
                'error_note' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $captured++;
        }

        $this->snapshots->replaceSlotRows($tradeDate, $snapshotSlot, $insert);

        $payload = [
            'trade_date' => $tradeDate,
            'snapshot_slot' => $snapshotSlot,
            'publication_id' => (int) $publication->publication_id,
            'run_id' => (int) $run->run_id,
            'scope_count' => count($scope),
            'captured_count' => $captured,
            'skipped_count' => $skipped,
            'source_mode' => $sourceMode,
            'input_file' => $inputFile,
        ];

        if ($skipped > 0) {
            $this->runs->appendEvent($run, 'SESSION_SNAPSHOT', 'SNAPSHOT_CAPTURED', 'WARN', 'Session snapshot captured only partial scope.', 'SNAP_PARTIAL_SCOPE', $payload);
        } else {
            $this->runs->appendEvent($run, 'SESSION_SNAPSHOT', 'SNAPSHOT_CAPTURED', 'INFO', 'Session snapshot captured successfully.', null, $payload);
        }

        $summary = [
            'trade_date' => $tradeDate,
            'trade_date_effective' => $publication->trade_date,
            'snapshot_slot' => $snapshotSlot,
            'source_mode' => $sourceMode,
            'publication_id' => (int) $publication->publication_id,
            'run_id' => (int) $run->run_id,
            'scope_count' => count($scope),
            'captured_count' => $captured,
            'skipped_count' => $skipped,
            'all_captured' => $skipped === 0,
            'input_file' => $inputFile,
        ];

        $dir = $outputDir ?: rtrim(config('market_data.evidence.output_directory'), '/').'/session_snapshots/'.$tradeDate.'_'.$snapshotSlot;
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir.'/market_data_session_snapshot_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $summary['output_dir'] = $dir;
        return $summary;
    }

    public function purge($beforeDate = null, $outputDir = null)
    {
        $timezone = config('market_data.platform.timezone');
        $retentionDays = (int) config('market_data.session_snapshot.retention_days', 30);
        $cutoff = $beforeDate
            ? Carbon::parse($beforeDate, $timezone)->endOfDay()
            : Carbon::now($timezone)->subDays($retentionDays);

        $deleted = $this->snapshots->purgeBefore($cutoff->toDateTimeString());
        $summary = [
            'cutoff_timestamp' => $cutoff->toDateTimeString(),
            'cutoff_source' => $beforeDate ? 'explicit_before_date' : 'default_retention_days',
            'deleted_rows' => (int) $deleted,
            'retention_days' => $beforeDate ? null : $retentionDays,
            'before_date' => $beforeDate,
        ];
        $dir = $outputDir ?: rtrim(config('market_data.evidence.output_directory'), '/').'/session_snapshots/purge_'.Carbon::now($timezone)->format('Ymd_His');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir.'/market_data_session_snapshot_purge_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $summary['output_dir'] = $dir;
        return $summary;
    }
}
