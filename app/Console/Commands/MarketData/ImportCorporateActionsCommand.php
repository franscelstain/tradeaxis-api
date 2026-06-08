<?php

namespace App\Console\Commands\MarketData;

use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Illuminate\Support\Facades\DB;

class ImportCorporateActionsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:events:import-corporate-actions {input_file?} {--source_name=} {--dry-run} {--apply}';

    protected $description = 'Validate or import source-backed corporate action events from CSV.';

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

        try {
            $parsed = $this->parseCsv($inputFile);
            $validated = $this->validateRows($parsed['rows']);
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
            $this->renderSummary($inputFile, $parsed['row_count'], count($validated['valid_rows']), 0, $errorCount, $apply, $validated['action_types']);
            foreach (array_slice($validated['errors'], 0, 25) as $error) {
                $this->line('validation_error='.$error);
            }

            return 1;
        }

        $upsertedCount = 0;
        if ($apply) {
            DB::transaction(function () use ($validated, &$upsertedCount) {
                $events = app(EventRiskSourceRepository::class);
                foreach ($validated['valid_rows'] as $row) {
                    $events->upsertCorporateAction($row);
                    $upsertedCount++;
                }
            });
        }

        $this->info('status='.($apply ? 'APPLIED' : 'DRY_RUN'));
        $this->line('reason_code='.($apply ? 'COMMAND_APPLY_CONFIRMED' : 'COMMAND_DRY_RUN_ONLY'));
        $this->renderSummary($inputFile, $parsed['row_count'], count($validated['valid_rows']), $upsertedCount, 0, $apply, $validated['action_types']);
        $this->line('next_action='.($apply ? 'Run existing lifecycle/promote flow for affected trade dates to stamp event-risk context into current publications.' : 'Re-run with --apply after reviewing validation output.'));

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

        foreach (['ticker_code', 'action_date', 'action_type'] as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw new \RuntimeException('CSV header must include '.$requiredHeader.'.');
            }
        }

        return [
            'row_count' => count($rows),
            'rows' => $rows,
        ];
    }

    private function validateRows(array $rows)
    {
        $errors = [];
        $validRows = [];
        $sourceNameDefault = trim((string) ($this->option('source_name') ?: config('market_data.event_risk.corporate_action_source_name', 'manual_corporate_action_csv')));
        $tickerCodes = array_values(array_unique(array_filter(array_map(function ($row) {
            return $this->normalizeCode($row['ticker_code'] ?? '');
        }, $rows))));
        $tickerIdsByCode = app(TickerMasterRepository::class)->resolveTickerIdsByCodes($tickerCodes);
        $now = date('Y-m-d H:i:s');
        $seen = [];

        foreach ($rows as $row) {
            $line = (int) ($row['_line'] ?? 0);
            $tickerCode = $this->normalizeCode($row['ticker_code'] ?? '');
            $actionDate = trim((string) ($row['action_date'] ?? ''));
            $actionType = $this->normalizeCode($row['action_type'] ?? '');
            $sourceName = trim((string) ($row['source_name'] ?? $sourceNameDefault));
            $sourceRef = trim((string) ($row['source_ref'] ?? ''));
            $notes = trim((string) ($row['notes'] ?? ''));

            if ($tickerCode === '') {
                $errors[] = 'line '.$line.': ticker_code is required.';
                continue;
            }

            if (! isset($tickerIdsByCode[$tickerCode])) {
                $errors[] = 'line '.$line.': ticker_code '.$tickerCode.' does not exist in ticker master.';
                continue;
            }

            if (! $this->isIsoDate($actionDate)) {
                $errors[] = 'line '.$line.': action_date must use YYYY-MM-DD.';
                continue;
            }

            if ($actionType === '') {
                $errors[] = 'line '.$line.': action_type is required.';
                continue;
            }

            if ($sourceName === '') {
                $errors[] = 'line '.$line.': source_name is required.';
                continue;
            }

            $identity = $tickerCode.'|'.$actionDate.'|'.$actionType.'|'.$sourceName;
            if (isset($seen[$identity])) {
                $errors[] = 'line '.$line.': duplicate ticker_code/action_date/action_type/source_name in CSV.';
                continue;
            }
            $seen[$identity] = true;

            $validRows[] = [
                'ticker_id' => (int) $tickerIdsByCode[$tickerCode],
                'ticker_code' => $tickerCode,
                'action_date' => $actionDate,
                'action_type' => $actionType,
                'source_name' => $sourceName,
                'source_ref' => $sourceRef !== '' ? substr($sourceRef, 0, 255) : null,
                'notes' => $notes !== '' ? substr($notes, 0, 255) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
            'action_types' => array_values(array_unique(array_map(function ($row) {
                return $row['action_type'];
            }, $validRows))),
        ];
    }

    private function renderSummary($inputFile, $rowCount, $validCount, $upsertedCount, $errorCount, $apply, array $actionTypes)
    {
        sort($actionTypes);

        $this->line('input_file='.$this->normalizeOptionalPathForDisplay($inputFile));
        $this->line('source_name='.(string) ($this->option('source_name') ?: config('market_data.event_risk.corporate_action_source_name', 'manual_corporate_action_csv')));
        $this->line('operation_mode='.($apply ? 'APPLY' : 'DRY_RUN'));
        $this->line('row_count='.(int) $rowCount);
        $this->line('valid_row_count='.(int) $validCount);
        $this->line('upserted_count='.(int) $upsertedCount);
        $this->line('error_count='.(int) $errorCount);
        $this->line('action_types='.implode(',', $actionTypes));
    }

    private function normalizeHeader($header)
    {
        return strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
    }

    private function normalizeCode($value)
    {
        $code = strtoupper(trim((string) $value));
        $code = preg_replace('/[^A-Z0-9]+/', '_', $code);

        return trim((string) $code, '_');
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
