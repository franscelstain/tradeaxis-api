<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnforceMarketDataDbIntegrityIndexes extends Migration
{
    public function up()
    {
        foreach ($this->integrityIndexes() as $table => $indexes) {
            foreach ($indexes as $index => $definition) {
                $this->createIndexIfMissing($table, $index, $definition);
            }
        }
    }

    public function down()
    {
        foreach (array_reverse($this->integrityIndexes()) as $table => $indexes) {
            foreach (array_reverse(array_keys($indexes)) as $index) {
                $this->dropIndexIfExists($table, $index);
            }
        }
    }

    private function integrityIndexes(): array
    {
        return [
            'eod_bars' => [
                'idx_eod_bars_publication_date_ticker' => 'CREATE INDEX idx_eod_bars_publication_date_ticker ON eod_bars (publication_id, trade_date, ticker_id)',
            ],
            'eod_indicators' => [
                'idx_eod_indicators_publication_date_ticker' => 'CREATE INDEX idx_eod_indicators_publication_date_ticker ON eod_indicators (publication_id, trade_date, ticker_id)',
            ],
            'eod_eligibility' => [
                'idx_eod_eligibility_publication_date_ticker' => 'CREATE INDEX idx_eod_eligibility_publication_date_ticker ON eod_eligibility (publication_id, trade_date, ticker_id)',
            ],
            'eod_runs' => [
                'idx_runs_effective_readable_contract' => 'CREATE INDEX idx_runs_effective_readable_contract ON eod_runs (trade_date_effective, terminal_status, publishability_state, coverage_gate_state, is_current_publication)',
                'idx_runs_source_identity' => 'CREATE INDEX idx_runs_source_identity ON eod_runs (source, source_name, source_provider, source_file_hash)',
            ],
            'eod_publications' => [
                'idx_publication_readable_lookup' => 'CREATE INDEX idx_publication_readable_lookup ON eod_publications (trade_date, is_current, seal_state, publication_version, run_id)',
                'idx_publication_run_trade_date' => 'CREATE INDEX idx_publication_run_trade_date ON eod_publications (run_id, trade_date, publication_id)',
            ],
            'eod_current_publication_pointer' => [
                'idx_current_publication_pointer_run_version' => 'CREATE INDEX idx_current_publication_pointer_run_version ON eod_current_publication_pointer (run_id, publication_version)',
            ],
            'eod_dataset_corrections' => [
                'idx_corr_trade_date_status_execution' => 'CREATE INDEX idx_corr_trade_date_status_execution ON eod_dataset_corrections (trade_date, status, execution_count)',
                'idx_corr_prior_new_run' => 'CREATE INDEX idx_corr_prior_new_run ON eod_dataset_corrections (prior_run_id, new_run_id)',
                'idx_corr_baseline_publication' => 'CREATE INDEX idx_corr_baseline_publication ON eod_dataset_corrections (baseline_publication_id)',
                'idx_corr_replacement_publication' => 'CREATE INDEX idx_corr_replacement_publication ON eod_dataset_corrections (replacement_publication_id)',
                'idx_corr_baseline_replacement_publication' => 'CREATE INDEX idx_corr_baseline_replacement_publication ON eod_dataset_corrections (baseline_publication_id, replacement_publication_id)',
            ],
        ];
    }

    private function createIndexIfMissing(string $table, string $index, string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            // The canonical SQL bootstrap may already include this index.
            // Keep the migration idempotent across MariaDB versions and restored test databases.
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        try {
            DB::statement('DROP INDEX '.$index.' ON '.$table);
        } catch (\Throwable $e) {
            try {
                DB::statement('DROP INDEX '.$index);
            } catch (\Throwable $ignored) {
            }
        }
    }
}
