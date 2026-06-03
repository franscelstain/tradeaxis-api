<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\FullRangeCurrentEvidenceReplayService;

class FullRangeCurrentEvidenceReplayCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:evidence-replay:full-range-current {start_date?} {end_date?} {--fixture_case=valid_case} {--output_dir=} {--continue_on_error} {--max_dates=}';

    protected $description = 'Export run evidence, generate replay fixtures, verify replay, and export replay evidence for every current readable publication in a date range.';

    public function handle()
    {
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date');

        if (($startDate && ! $endDate) || (! $startDate && $endDate)) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'start_date and end_date must be provided together, or both omitted to use the current publication range.', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return 1;
        }

        foreach (['start_date' => $startDate, 'end_date' => $endDate] as $name => $value) {
            if ($value && ! $this->validateDateString($value, $name)) {
                return 1;
            }
        }

        $fixtureCase = (string) ($this->option('fixture_case') ?: 'valid_case');
        if (! preg_match('/^[A-Za-z0-9_\-]+$/', $fixtureCase)) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'fixture_case must contain only letters, numbers, dash, or underscore.', [
                'fixture_case' => $fixtureCase,
            ]);

            return 1;
        }

        $maxDates = $this->option('max_dates');
        if ($maxDates !== null && $maxDates !== '' && (int) $maxDates <= 0) {
            $this->renderCommandBlocked('COMMAND_MISSING_REQUIRED_INPUT', 'max_dates must be a positive integer when provided.', [
                'max_dates' => $maxDates,
            ]);

            return 1;
        }

        try {
            $summary = app(FullRangeCurrentEvidenceReplayService::class)->execute(
                $startDate ?: null,
                $endDate ?: null,
                [
                    'fixture_case' => $fixtureCase,
                    'output_dir' => $this->option('output_dir') ?: null,
                    'continue_on_error' => (bool) $this->option('continue_on_error'),
                    'max_dates' => $maxDates,
                ]
            );
        } catch (\Throwable $e) {
            $this->renderCommandBlocked($this->reasonCodeFromException($e), $e->getMessage(), [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'fixture_case' => $fixtureCase,
                'output_dir' => $this->normalizePathForDisplay((string) ($this->option('output_dir') ?: '')),
            ]);

            return 1;
        }

        $this->info('suite='.($summary['suite'] ?? 'market_data_full_range_current_evidence_replay'));
        $this->line('start_date='.$summary['start_date']);
        $this->line('end_date='.$summary['end_date']);
        $this->line('fixture_case='.$summary['fixture_case']);
        $this->line('trading_date_count='.$summary['trading_date_count']);
        $this->line('processed_count='.$summary['processed_count']);
        $this->line('success_count='.$summary['success_count']);
        $this->line('failed_count='.$summary['failed_count']);
        $this->line('error_count='.$summary['error_count']);
        $this->line('all_passed='.(empty($summary['all_passed']) ? '0' : '1'));
        $this->line('output_dir='.$this->normalizePathForDisplay((string) $summary['output_dir']));
        $this->line('summary_artifact='.$this->normalizePathForDisplay((string) rtrim($summary['output_dir'], '/\\').'/market_data_full_range_current_evidence_replay_summary.json'));

        foreach ($summary['cases'] as $case) {
            $parts = [
                'trade_date='.$case['trade_date'],
                'status='.($case['status'] ?? 'ERROR'),
                'passed='.(empty($case['passed']) ? '0' : '1'),
            ];

            foreach (['run_id', 'publication_id', 'replay_id', 'comparison_result', 'replay_status', 'mismatch_count', 'reason_code'] as $key) {
                if (array_key_exists($key, $case) && $case[$key] !== null && $case[$key] !== '') {
                    $parts[] = $key.'='.$case[$key];
                }
            }

            if (isset($case['run_evidence_admission_state'])) {
                $parts[] = 'run_evidence='.$case['run_evidence_admission_state'].'/'.$case['run_evidence_completeness_state'];
            }

            if (isset($case['replay_evidence_admission_state'])) {
                $parts[] = 'replay_evidence='.$case['replay_evidence_admission_state'];
            }

            if (isset($case['error_message'])) {
                $parts[] = 'error='.$case['error_message'];
            }

            $this->line(implode(' | ', $parts));
        }

        return empty($summary['all_passed']) ? 1 : 0;
    }

    private function reasonCodeFromException(\Throwable $e)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'COMMAND_EXECUTION_FAILED';
    }
}
