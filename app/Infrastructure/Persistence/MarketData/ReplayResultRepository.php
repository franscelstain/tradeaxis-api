<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Application\MarketData\Services\CoverageGateStateNormalizer;
use App\Application\MarketData\Services\ReplayMode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReplayResultRepository
{
    public function nextReplayId()
    {
        return (int) DB::table('md_replay_daily_metrics')->max('replay_id') + 1;
    }

    public function upsertMetric(array $metric)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $metric['replay_mode'] = ReplayMode::normalize($metric['replay_mode'] ?? null);
        $this->assertModeInputs($metric);

        $payload = [
            'replay_suite' => $metric['replay_suite'] ?? null,
            'replay_case' => $metric['replay_case'] ?? null,
            'fixture_id' => $metric['fixture_id'] ?? null,
            'fixture_version' => $metric['fixture_version'] ?? null,
            'fixture_schema_version' => $metric['fixture_schema_version'] ?? null,
            'fixture_source' => $metric['fixture_source'] ?? null,
            'fixture_created_at' => $metric['fixture_created_at'] ?? null,
            'replay_mode' => $metric['replay_mode'],
            'knowledge_cutoff_at' => $metric['knowledge_cutoff_at'] ?? null,
            'fixture_manifest_hash' => $metric['fixture_manifest_hash'] ?? null,
            'source_observation_manifest_hash' => $metric['source_observation_manifest_hash'] ?? null,
            'canonical_raw_input_hash' => $metric['canonical_raw_input_hash'] ?? null,
            'temporal_identity_hash' => $metric['temporal_identity_hash'] ?? null,
            'calendar_status_hash' => $metric['calendar_status_hash'] ?? null,
            'event_factor_hash' => $metric['event_factor_hash'] ?? null,
            'config_snapshot_id' => $metric['config_snapshot_id'] ?? null,
            'config_snapshot_hash' => $metric['config_snapshot_hash'] ?? null,
            'formula_registry_hash' => $metric['formula_registry_hash'] ?? null,
            'reason_registry_hash' => $metric['reason_registry_hash'] ?? null,
            'read_model_version' => $metric['read_model_version'] ?? null,
            'serialization_version' => $metric['serialization_version'] ?? null,
            'executable_build_identity' => $metric['executable_build_identity'] ?? null,
            'admission_state' => $metric['admission_state'] ?? null,
            'bound_input_context_json' => $metric['bound_input_context_json'] ?? null,
            'trade_date_effective' => $metric['trade_date_effective'] ?? null,
            'source' => $metric['source'],
            'source_mode' => $metric['source_mode'] ?? ($metric['source'] ?? null),
            'source_name' => $metric['source_name'] ?? null,
            'source_provider' => $metric['source_provider'] ?? null,
            'source_timeout_seconds' => $metric['source_timeout_seconds'] ?? null,
            'source_retry_max' => $metric['source_retry_max'] ?? null,
            'source_attempt_count' => $metric['source_attempt_count'] ?? null,
            'source_success_after_retry' => array_key_exists('source_success_after_retry', $metric) ? $metric['source_success_after_retry'] : null,
            'source_retry_exhausted' => array_key_exists('source_retry_exhausted', $metric) ? $metric['source_retry_exhausted'] : null,
            'source_final_http_status' => $metric['source_final_http_status'] ?? null,
            'source_final_reason_code' => $metric['source_final_reason_code'] ?? null,
            'source_input_file' => $metric['source_input_file'] ?? null,
            'status' => $metric['status'],
            'publishability_state' => $metric['publishability_state'] ?? null,
            'publication_id' => $metric['publication_id'] ?? null,
            'publication_run_id' => $metric['publication_run_id'] ?? null,
            'comparison_result' => $metric['comparison_result'],
            'replay_status' => $metric['replay_status'] ?? $this->replayStatusForComparison($metric['comparison_result'] ?? null),
            'comparison_note' => $metric['comparison_note'] ?? null,
            'artifact_changed_scope' => $metric['artifact_changed_scope'] ?? null,
            'config_identity' => $metric['config_identity'] ?? null,
            'publication_version' => $metric['publication_version'] ?? null,
            'is_current_publication' => array_key_exists('is_current_publication', $metric) ? (bool) $metric['is_current_publication'] : null,
            'correction_id' => $metric['correction_id'] ?? null,
            'correction_status' => $metric['correction_status'] ?? null,
            'correction_outcome' => $metric['correction_outcome'] ?? null,
            'correction_reseal_status' => $metric['correction_reseal_status'] ?? null,
            'correction_publication_switch' => array_key_exists('correction_publication_switch', $metric) ? $metric['correction_publication_switch'] : null,
            'baseline_publication_id' => $metric['baseline_publication_id'] ?? null,
            'candidate_publication_id' => $metric['candidate_publication_id'] ?? null,
            'expected_correction_id' => $metric['expected_correction_id'] ?? null,
            'expected_correction_status' => $metric['expected_correction_status'] ?? null,
            'expected_correction_outcome' => $metric['expected_correction_outcome'] ?? null,
            'expected_correction_reseal_status' => $metric['expected_correction_reseal_status'] ?? null,
            'expected_correction_publication_switch' => array_key_exists('expected_correction_publication_switch', $metric) ? $metric['expected_correction_publication_switch'] : null,
            'expected_baseline_publication_id' => $metric['expected_baseline_publication_id'] ?? null,
            'expected_candidate_publication_id' => $metric['expected_candidate_publication_id'] ?? null,
            'coverage_universe_count' => $metric['coverage_universe_count'] ?? null,
            'coverage_available_count' => $metric['coverage_available_count'] ?? null,
            'coverage_missing_count' => $metric['coverage_missing_count'] ?? null,
            'coverage_ratio' => $metric['coverage_ratio'] ?? null,
            'coverage_min_threshold' => $metric['coverage_min_threshold'] ?? null,
            'coverage_gate_state' => CoverageGateStateNormalizer::normalize($metric['coverage_gate_state'] ?? null),
            'coverage_threshold_mode' => $metric['coverage_threshold_mode'] ?? null,
            'coverage_universe_basis' => $metric['coverage_universe_basis'] ?? null,
            'coverage_contract_version' => $metric['coverage_contract_version'] ?? null,
            'coverage_missing_sample_json' => $metric['coverage_missing_sample_json'] ?? null,
            'bars_rows_written' => $metric['bars_rows_written'] ?? null,
            'indicators_rows_written' => $metric['indicators_rows_written'] ?? null,
            'eligibility_rows_written' => $metric['eligibility_rows_written'] ?? null,
            'eligible_count' => $metric['eligible_count'] ?? null,
            'invalid_bar_count' => $metric['invalid_bar_count'] ?? null,
            'invalid_indicator_count' => $metric['invalid_indicator_count'] ?? null,
            'warning_count' => $metric['warning_count'] ?? null,
            'hard_reject_count' => $metric['hard_reject_count'] ?? null,
            'bars_batch_hash' => $metric['bars_batch_hash'] ?? null,
            'indicators_batch_hash' => $metric['indicators_batch_hash'] ?? null,
            'eligibility_batch_hash' => $metric['eligibility_batch_hash'] ?? null,
            'seal_state' => $metric['seal_state'],
            'sealed_at' => $metric['sealed_at'] ?? null,
            'expected_status' => $metric['expected_status'] ?? null,
            'expected_terminal_status' => $metric['expected_terminal_status'] ?? null,
            'expected_publishability_state' => $metric['expected_publishability_state'] ?? null,
            'expected_trade_date_effective' => $metric['expected_trade_date_effective'] ?? null,
            'expected_seal_state' => $metric['expected_seal_state'] ?? null,
            'expected_source_mode' => $metric['expected_source_mode'] ?? null,
            'expected_source_name' => $metric['expected_source_name'] ?? null,
            'expected_source_provider' => $metric['expected_source_provider'] ?? null,
            'expected_source_timeout_seconds' => $metric['expected_source_timeout_seconds'] ?? null,
            'expected_source_retry_max' => $metric['expected_source_retry_max'] ?? null,
            'expected_source_attempt_count' => $metric['expected_source_attempt_count'] ?? null,
            'expected_source_success_after_retry' => array_key_exists('expected_source_success_after_retry', $metric) ? $metric['expected_source_success_after_retry'] : null,
            'expected_source_retry_exhausted' => array_key_exists('expected_source_retry_exhausted', $metric) ? $metric['expected_source_retry_exhausted'] : null,
            'expected_source_final_http_status' => $metric['expected_source_final_http_status'] ?? null,
            'expected_source_final_reason_code' => $metric['expected_source_final_reason_code'] ?? null,
            'expected_source_input_file' => $metric['expected_source_input_file'] ?? null,
            'expected_source_file_hash' => $metric['expected_source_file_hash'] ?? null,
            'expected_source_file_hash_algorithm' => $metric['expected_source_file_hash_algorithm'] ?? null,
            'expected_source_file_size_bytes' => $metric['expected_source_file_size_bytes'] ?? null,
            'expected_source_file_row_count' => $metric['expected_source_file_row_count'] ?? null,
            'expected_config_identity' => $metric['expected_config_identity'] ?? null,
            'expected_publication_id' => $metric['expected_publication_id'] ?? null,
            'expected_publication_run_id' => $metric['expected_publication_run_id'] ?? null,
            'expected_publication_version' => $metric['expected_publication_version'] ?? null,
            'expected_is_current_publication' => array_key_exists('expected_is_current_publication', $metric) ? (bool) $metric['expected_is_current_publication'] : null,
            'expected_coverage_universe_count' => $metric['expected_coverage_universe_count'] ?? null,
            'expected_coverage_available_count' => $metric['expected_coverage_available_count'] ?? null,
            'expected_coverage_missing_count' => $metric['expected_coverage_missing_count'] ?? null,
            'expected_coverage_ratio' => $metric['expected_coverage_ratio'] ?? null,
            'expected_coverage_min_threshold' => $metric['expected_coverage_min_threshold'] ?? null,
            'expected_coverage_gate_state' => CoverageGateStateNormalizer::normalize($metric['expected_coverage_gate_state'] ?? null),
            'expected_coverage_threshold_mode' => $metric['expected_coverage_threshold_mode'] ?? null,
            'expected_coverage_universe_basis' => $metric['expected_coverage_universe_basis'] ?? null,
            'expected_coverage_contract_version' => $metric['expected_coverage_contract_version'] ?? null,
            'expected_coverage_missing_sample_json' => $metric['expected_coverage_missing_sample_json'] ?? null,
            'expected_bars_batch_hash' => $metric['expected_bars_batch_hash'] ?? null,
            'expected_indicators_batch_hash' => $metric['expected_indicators_batch_hash'] ?? null,
            'expected_eligibility_batch_hash' => $metric['expected_eligibility_batch_hash'] ?? null,
            'expected_reason_code_counts_json' => $metric['expected_reason_code_counts_json'] ?? null,
            'mismatch_summary' => $metric['mismatch_summary'] ?? null,
            'mismatch_count' => $metric['mismatch_count'] ?? null,
            'mismatch_reason_codes_json' => $metric['mismatch_reason_codes_json'] ?? null,
            'mismatches_json' => $metric['mismatches_json'] ?? null,
            'expected_context_json' => $metric['expected_context_json'] ?? null,
            'actual_context_json' => $metric['actual_context_json'] ?? null,
            'ignored_volatile_fields_json' => $metric['ignored_volatile_fields_json'] ?? null,
            'deterministic_fields_checked_json' => $metric['deterministic_fields_checked_json'] ?? null,
            'final_reason_code' => $metric['final_reason_code'] ?? null,
            'created_at' => $metric['created_at'] ?? $now,
        ];

        DB::table('md_replay_daily_metrics')->updateOrInsert(
            [
                'replay_id' => $metric['replay_id'],
                'trade_date' => $metric['trade_date'],
            ],
            $payload
        );
    }

    private function assertModeInputs(array $metric): void
    {
        $mode = $metric['replay_mode'];
        if ($mode === ReplayMode::PUBLICATION_EXACT && empty($metric['publication_id'])) {
            throw new \RuntimeException('REPLAY_EXPLICIT_PUBLICATION_REQUIRED: PUBLICATION_EXACT result requires publication_id.');
        }
        if ($mode === ReplayMode::AS_KNOWN && empty($metric['knowledge_cutoff_at'])) {
            throw new \RuntimeException('REPLAY_KNOWLEDGE_CUTOFF_REQUIRED: AS_KNOWN result requires knowledge_cutoff_at.');
        }

        $comparisonResult = array_key_exists('comparison_result', $metric) ? $metric['comparison_result'] : null;
        $status = array_key_exists('replay_status', $metric) ? $metric['replay_status'] : $this->replayStatusForComparison($comparisonResult);
        if ($status === 'BLOCKED') return;

        foreach (['fixture_manifest_hash', 'config_snapshot_hash', 'serialization_version', 'executable_build_identity'] as $field) {
            if (empty($metric[$field])) {
                throw new \RuntimeException('REPLAY_BOUND_INPUT_INCOMPLETE: non-BLOCKED replay result is missing '.$field.'.');
            }
        }
        if ($mode === ReplayMode::AS_KNOWN) {
            foreach (['source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash', 'calendar_status_hash', 'event_factor_hash', 'formula_registry_hash', 'reason_registry_hash'] as $field) {
                if (empty($metric[$field])) {
                    throw new \RuntimeException('REPLAY_BOUND_INPUT_INCOMPLETE: AS_KNOWN result is missing '.$field.'.');
                }
            }
        }
    }

    private function replayStatusForComparison($comparisonResult)
    {
        if (in_array((string) $comparisonResult, ['MATCH', 'EXPECTED_DEGRADE'], true)) {
            return 'PASS';
        }

        if (in_array((string) $comparisonResult, ['MISMATCH', 'UNEXPECTED'], true)) {
            return 'FAIL';
        }

        return 'BLOCKED';
    }

    public function replaceReasonCodeCounts($replayId, $tradeDate, array $reasonCounts)
    {
        DB::transaction(function () use ($replayId, $tradeDate, $reasonCounts) {
            DB::table('md_replay_reason_code_counts')
                ->where('replay_id', $replayId)
                ->where('trade_date', $tradeDate)
                ->delete();

            if (empty($reasonCounts)) {
                return;
            }

            $rows = [];
            foreach ($reasonCounts as $row) {
                $rows[] = [
                    'replay_id' => $replayId,
                    'trade_date' => $tradeDate,
                    'reason_code' => $row['reason_code'],
                    'reason_count' => (int) $row['reason_count'],
                ];
            }

            DB::table('md_replay_reason_code_counts')->insert($rows);
        });
    }
}
