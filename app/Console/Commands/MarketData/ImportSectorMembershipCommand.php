<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Illuminate\Support\Facades\DB;

class ImportSectorMembershipCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:sectors:import-memberships {input_file?} {--classification_system=IDX-IC} {--source_name=manual_sector_csv} {--dry-run} {--apply}';

    protected $description = 'Validate or import ticker sector memberships from a source-backed CSV file.';

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
        if ($classificationSystem === '') {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'classification_system is required.', [
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
        $errorCount = count($validated['errors']);

        if ($errorCount > 0) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=COMMAND_EXECUTION_FAILED');
            $this->line('error=CSV validation failed.');
            $this->renderSummary($inputFile, $classificationSystem, $parsed['row_count'], count($validated['valid_rows']), 0, $errorCount, $apply);
            foreach (array_slice($validated['errors'], 0, 25) as $error) {
                $this->line('validation_error='.$error);
            }

            return 1;
        }

        $upsertedCount = 0;
        if ($apply) {
            DB::transaction(function () use ($validated, $classificationSystem, &$upsertedCount) {
                $sectors = app(SectorClassificationRepository::class);
                foreach ($validated['valid_rows'] as $row) {
                    $sectors->upsertMembership(
                        $row['ticker_id'],
                        $row['sector_code'],
                        $row['effective_from'],
                        $row['effective_to'],
                        $row['source_name'],
                        $row['source_ref'],
                        $classificationSystem
                    );
                    $upsertedCount++;
                }
            });
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->renderSummary($inputFile, $classificationSystem, $parsed['row_count'], count($validated['valid_rows']), $upsertedCount, 0, $apply);
        $this->line('next_action='.($apply ? 'Run indicator recompute/promote for affected trade dates.' : 'Re-run with --apply after reviewing validation output.'));

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

        foreach (['ticker_code', 'sector_code', 'effective_from'] as $requiredHeader) {
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
        $sourceNameDefault = trim((string) ($this->option('source_name') ?: 'manual_sector_csv'));
        $sectorCodes = array_fill_keys(app(SectorClassificationRepository::class)->activeSectorCodes($classificationSystem), true);
        $tickerCodes = array_values(array_unique(array_filter(array_map(function ($row) {
            return strtoupper(trim((string) ($row['ticker_code'] ?? '')));
        }, $rows))));
        $tickerIdsByCode = app(TickerMasterRepository::class)->resolveTickerIdsByCodes($tickerCodes);

        foreach ($rows as $row) {
            $line = (int) ($row['_line'] ?? 0);
            $tickerCode = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
            $sectorCode = strtoupper(trim((string) ($row['sector_code'] ?? '')));
            $effectiveFrom = trim((string) ($row['effective_from'] ?? ''));
            $effectiveTo = trim((string) ($row['effective_to'] ?? ''));
            $sourceName = trim((string) ($row['source_name'] ?? $sourceNameDefault));
            $sourceRef = trim((string) ($row['source_ref'] ?? ''));

            if ($tickerCode === '') {
                $errors[] = 'line '.$line.': ticker_code is required.';
                continue;
            }

            if (! isset($tickerIdsByCode[$tickerCode])) {
                $errors[] = 'line '.$line.': ticker_code '.$tickerCode.' does not exist in ticker master.';
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

            $validRows[] = [
                'line' => $line,
                'ticker_id' => (int) $tickerIdsByCode[$tickerCode],
                'ticker_code' => $tickerCode,
                'sector_code' => $sectorCode,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo !== '' ? $effectiveTo : null,
                'source_name' => $sourceName !== '' ? $sourceName : $sourceNameDefault,
                'source_ref' => $sourceRef !== '' ? $sourceRef : null,
            ];
        }

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
        ];
    }

    private function renderSummary($inputFile, $classificationSystem, $rowCount, $validCount, $upsertedCount, $errorCount, $apply)
    {
        $this->line('input_file='.$this->normalizeOptionalPathForDisplay($inputFile));
        $this->line('classification_system='.$classificationSystem);
        $this->line('operation_mode='.($apply ? 'APPLY' : 'DRY_RUN'));
        $this->line('row_count='.(int) $rowCount);
        $this->line('valid_row_count='.(int) $validCount);
        $this->line('upserted_count='.(int) $upsertedCount);
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
}
