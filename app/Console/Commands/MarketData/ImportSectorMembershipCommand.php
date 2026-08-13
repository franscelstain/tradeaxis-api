<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use Illuminate\Support\Facades\DB;

class ImportSectorMembershipCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:sectors:import-memberships
        {input_file?}
        {--classification_system=IDX-IC}
        {--source_name=}
        {--source_authority_class=}
        {--operator_name=}
        {--reason_code=SECTOR_MEMBERSHIP_IMPORT}
        {--dry-run}
        {--apply}';

    protected $description = 'Validate or append authoritative IDX-IC membership revisions using stable listing identity.';

    public function handle()
    {
        $inputFile = $this->argument('input_file');

        if (! $inputFile) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'input_file is required.', [
                'input_file' => $inputFile,
            ]);

            return 1;
        }

        if ((bool) $this->option('dry-run') && (bool) $this->option('apply')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', '--dry-run and --apply cannot be used together.', [
                'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
            ]);

            return 1;
        }

        if (! is_file($inputFile)) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'input_file must point to an existing CSV file.', [
                'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
            ]);

            return 1;
        }

        $classificationSystem = strtoupper(trim((string) $this->option('classification_system')));
        if ($classificationSystem !== 'IDX-IC') {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', 'Only the documented IDX-IC classification system is supported.', [
                'classification_system' => $this->option('classification_system'),
            ]);

            return 1;
        }

        try {
            $parsed = $this->parseCsv($inputFile);
            $validated = $this->validateRows($parsed['rows'], $classificationSystem);
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), [
                'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
            ]);

            return 1;
        }

        $apply = (bool) $this->option('apply');
        $sectors = app(SectorClassificationRepository::class);

        // Held across preflight, apply and postflight so all three read one world. A dry-run writes
        // nothing and deliberately does not take the lock; a read-only report must never be able to
        // block a real import.
        $lockHeld = $apply && ! $validated['errors'];
        if ($lockHeld && ! $sectors->acquireImportLock()) {
            $this->renderCommandBlocked(
                'SECTOR_MEMBERSHIP_IMPORT_LOCK_UNAVAILABLE',
                'Another sector membership import holds the import lock. Nothing was written.',
                [
                    'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
                    'classification_system' => $classificationSystem,
                ]
            );

            return 1;
        }

        try {
            return $this->runImport(
                $inputFile,
                $classificationSystem,
                $parsed,
                $validated,
                $apply,
                $sectors
            );
        } finally {
            if ($lockHeld) {
                $sectors->releaseImportLock();
            }
        }
    }

    /**
     * The import body, extracted so the lock in handle() can wrap every exit path — including the
     * validation-failure returns, which must still release it.
     */
    private function runImport($inputFile, $classificationSystem, array $parsed, array $validated, $apply, SectorClassificationRepository $sectors)
    {
        $preflight = ['errors' => [], 'planned_revision_count' => 0];
        if (! $validated['errors']) {
            $preflight = $sectors
                ->validateAuthoritativeImportBatch($validated['valid_rows'], $classificationSystem);
        }
        $errors = array_merge($validated['errors'], $preflight['errors']);
        $errorCount = count($errors);

        if ($errorCount > 0) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=COMMAND_EXECUTION_FAILED');
            $this->line('error=CSV validation failed.');
            $this->renderSummary($inputFile, $classificationSystem, $parsed['row_count'], 0, $preflight['planned_revision_count'], 0, $errorCount, $apply);
            foreach (array_slice($errors, 0, 25) as $error) {
                $this->line('validation_error='.$error);
            }

            return 1;
        }

        $appendedRevisionCount = 0;
        if ($apply) {
            try {
                DB::transaction(function () use ($validated, $classificationSystem, &$appendedRevisionCount, $sectors) {
                    $membershipTable = config('market_data.sectors.membership_table', 'ticker_sector_memberships');
                    $beforeCount = DB::table($membershipTable)->count();

                    foreach ($validated['valid_rows'] as $row) {
                        $sectors->appendMembership(
                            $row['listing_id'],
                            $row['ticker_id'],
                            $row['sector_code'],
                            $row['effective_from'],
                            $row['effective_to'],
                            $row['source_name'],
                            $row['source_ref'],
                            $classificationSystem,
                            $row['source_authority_class'],
                            $row['recorded_at'],
                            $row['operator_name'],
                            $row['reason_code']
                        );
                    }

                    $postflight = $sectors->validateAuthoritativeImportBatch(
                        $validated['valid_rows'],
                        $classificationSystem
                    );
                    if ($postflight['errors']) {
                        throw new \RuntimeException(implode(' | ', $postflight['errors']));
                    }

                    $appendedRevisionCount = DB::table($membershipTable)->count() - $beforeCount;
                });
            } catch (\Throwable $e) {
                $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage(), [
                    'input_file' => $this->normalizeOptionalPathForDisplay($inputFile),
                    'classification_system' => $classificationSystem,
                ]);

                return 1;
            }
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->renderSummary(
            $inputFile,
            $classificationSystem,
            $parsed['row_count'],
            count($validated['valid_rows']),
            $preflight['planned_revision_count'],
            $appendedRevisionCount,
            0,
            $apply
        );
        $this->line('next_action='.($apply ? 'Run existing lifecycle/promote flow for affected trade dates.' : 'Re-run with --apply after reviewing validation output.'));

        return 0;
    }

    private function parseCsv($inputFile)
    {
        $handle = fopen($inputFile, 'r');
        if (! $handle) {
            throw new \RuntimeException('Unable to read input CSV.');
        }

        $headers = null;
        $rows = [];
        $lineNumber = 0;

        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($lineNumber === 1) {
                $headers = array_map([$this, 'normalizeHeader'], $line);
                continue;
            }

            if ($this->isEmptyCsvLine($line)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $row[$header] = isset($line[$index]) ? trim((string) $line[$index]) : '';
            }
            $row['_line'] = $lineNumber;
            $rows[] = $row;
        }

        fclose($handle);

        if ($headers === null) {
            throw new \RuntimeException('CSV file is empty.');
        }

        foreach (['listing_uid', 'sector_code', 'effective_from', 'source_name', 'source_ref', 'source_authority_class', 'recorded_at'] as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw new \RuntimeException('CSV header must include '.$requiredHeader.'.');
            }
        }

        return [
            'row_count' => count($rows),
            'rows' => $rows,
        ];
    }

    private function validateRows(array $rows, $classificationSystem)
    {
        $errors = [];
        $validRows = [];
        $sourceNameDefault = trim((string) $this->option('source_name'));
        $authorityDefault = strtoupper(trim((string) $this->option('source_authority_class')));
        $operatorDefault = trim((string) $this->option('operator_name'));
        $reasonDefault = strtoupper(trim((string) ($this->option('reason_code') ?: 'SECTOR_MEMBERSHIP_IMPORT')));
        $sectorCodes = array_fill_keys(app(SectorClassificationRepository::class)->activeSectorCodes($classificationSystem), true);
        $listingUids = array_values(array_unique(array_filter(array_map(function ($row) {
            return trim((string) ($row['listing_uid'] ?? ''));
        }, $rows))));
        $listingsByUid = DB::table('md_listings as listing')
            ->leftJoin('tickers as ticker', 'ticker.ticker_id', '=', 'listing.legacy_ticker_id')
            ->whereIn('listing.listing_uid', $listingUids)
            ->select([
                'listing.listing_id', 'listing.listing_uid', 'listing.legacy_ticker_id',
                'listing.exchange_code', 'ticker.ticker_code',
            ])
            ->get()
            ->keyBy('listing_uid');

        foreach ($rows as $row) {
            $line = (int) ($row['_line'] ?? 0);
            $listingUid = trim((string) ($row['listing_uid'] ?? ''));
            $tickerCode = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            $sectorCode = strtoupper(trim((string) ($row['sector_code'] ?? '')));
            $effectiveFrom = trim((string) ($row['effective_from'] ?? ''));
            $effectiveTo = trim((string) ($row['effective_to'] ?? ''));
            $sourceName = trim((string) ($row['source_name'] ?? ''));
            $sourceName = $sourceName !== '' ? $sourceName : $sourceNameDefault;
            $sourceRef = trim((string) ($row['source_ref'] ?? ''));
            $sourceAuthorityClass = strtoupper(trim((string) ($row['source_authority_class'] ?? '')));
            $sourceAuthorityClass = $sourceAuthorityClass !== '' ? $sourceAuthorityClass : $authorityDefault;
            $operatorName = trim((string) ($row['operator_name'] ?? ''));
            if ($operatorName === '' && $sourceAuthorityClass === 'OPERATOR_ENTERED') {
                $operatorName = $operatorDefault;
            }
            $reasonCode = strtoupper(trim((string) ($row['reason_code'] ?? '')));
            if ($reasonCode === '' && $sourceAuthorityClass === 'OPERATOR_ENTERED') {
                $reasonCode = $reasonDefault;
            }
            $recordedAt = trim((string) ($row['recorded_at'] ?? ''));

            if ($listingUid === '') {
                $errors[] = 'line '.$line.': listing_uid is required; ticker text alone is not a stable membership identity.';
                continue;
            }

            if (! isset($listingsByUid[$listingUid])) {
                $errors[] = 'line '.$line.': listing_uid '.$listingUid.' does not exist in the temporal listing master.';
                continue;
            }

            $listing = $listingsByUid[$listingUid];
            if (strtoupper(trim((string) $listing->exchange_code)) !== 'IDX' || $listing->legacy_ticker_id === null) {
                $errors[] = 'line '.$line.': listing_uid '.$listingUid.' is not an importable IDX listing bound to ticker master.';
                continue;
            }

            $masterTickerCode = strtoupper(trim((string) $listing->ticker_code));
            if ($tickerCode !== '' && $tickerCode !== $masterTickerCode) {
                $errors[] = 'line '.$line.': ticker_code '.$tickerCode.' does not match listing_uid '.$listingUid.'.';
                continue;
            }

            if (! isset($sectorCodes[$sectorCode])) {
                $errors[] = 'line '.$line.': sector_code '.$sectorCode.' is not active for '.$classificationSystem.'.';
                continue;
            }

            if (! $this->isIsoDate($effectiveFrom)) {
                $errors[] = 'line '.$line.': effective_from must use YYYY-MM-DD.';
                continue;
            }

            if ($effectiveTo !== '' && ! $this->isIsoDate($effectiveTo)) {
                $errors[] = 'line '.$line.': effective_to must use YYYY-MM-DD when provided.';
                continue;
            }

            if ($effectiveTo !== '' && strcmp($effectiveTo, $effectiveFrom) < 0) {
                $errors[] = 'line '.$line.': effective_to cannot be before effective_from.';
                continue;
            }

            if ($sourceAuthorityClass === 'DERIVED_REFERENCE') {
                $errors[] = 'line '.$line.': DERIVED_REFERENCE cannot establish sector membership.';
                continue;
            }

            if (! in_array($sourceAuthorityClass, ['EXCHANGE_AUTHORITATIVE', 'OPERATOR_ENTERED'], true)) {
                $errors[] = 'line '.$line.': source_authority_class must be EXCHANGE_AUTHORITATIVE or governed OPERATOR_ENTERED.';
                continue;
            }

            if ($sourceName === '' || $sourceRef === '') {
                $errors[] = 'line '.$line.': source_name and source_ref are required.';
                continue;
            }

            if ($sourceAuthorityClass === 'OPERATOR_ENTERED' && ($operatorName === '' || $reasonCode === '')) {
                $errors[] = 'line '.$line.': OPERATOR_ENTERED requires operator_name and reason_code.';
                continue;
            }

            if (! $this->isDateTime($recordedAt)) {
                $errors[] = 'line '.$line.': recorded_at must use YYYY-MM-DD HH:MM:SS.';
                continue;
            }

            $validRows[] = [
                'line' => $line,
                'listing_id' => (int) $listing->listing_id,
                'listing_uid' => $listingUid,
                'ticker_id' => (int) $listing->legacy_ticker_id,
                'ticker_code' => $masterTickerCode,
                'sector_code' => $sectorCode,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo !== '' ? $effectiveTo : null,
                'source_name' => $sourceName !== '' ? $sourceName : $sourceNameDefault,
                'source_ref' => $sourceRef !== '' ? $sourceRef : null,
                'source_authority_class' => $sourceAuthorityClass,
                'operator_name' => $operatorName !== '' ? $operatorName : null,
                'reason_code' => $reasonCode !== '' ? $reasonCode : null,
                'recorded_at' => $recordedAt,
            ];
        }

        usort($validRows, function ($left, $right) {
            foreach (['recorded_at', 'listing_id', 'effective_from', 'line'] as $field) {
                $comparison = strcmp((string) $left[$field], (string) $right[$field]);
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        });

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
        ];
    }

    private function renderSummary($inputFile, $classificationSystem, $rowCount, $acceptedCount, $plannedRevisionCount, $appendedRevisionCount, $errorCount, $apply)
    {
        $this->line('input_file='.$this->normalizeOptionalPathForDisplay($inputFile));
        $this->line('classification_system='.$classificationSystem);
        $this->line('operation_mode='.($apply ? 'APPLY' : 'DRY_RUN'));
        $this->line('row_count='.(int) $rowCount);
        $this->line('accepted_row_count='.(int) $acceptedCount);
        $this->line('planned_revision_count='.(int) $plannedRevisionCount);
        $this->line('appended_revision_count='.(int) $appendedRevisionCount);
        $this->line('error_count='.(int) $errorCount);
    }

    private function normalizeHeader($header)
    {
        return strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
    }

    private function isEmptyCsvLine(array $line)
    {
        foreach ($line as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isIsoDate($value)
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date !== false && $date->format('Y-m-d') === (string) $value;
    }

    private function isDateTime($value)
    {
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', (string) $value);

        return $dateTime !== false && $dateTime->format('Y-m-d H:i:s') === (string) $value;
    }
}
