<?php

use App\Models\EodRun;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class PublicationRepositoryIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        DB::table('eod_runs')->insert([
            'run_id' => 25,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'config_version' => 'cfg-old',
            'publication_id' => 10,
            'publication_version' => 1,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 100,
            'coverage_available_count' => 100,
            'coverage_missing_count' => 0,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_runs')->insert([
            'run_id' => 27,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'config_version' => 'cfg-new',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 100,
            'coverage_available_count' => 100,
            'coverage_missing_count' => 0,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:21:00',
            'started_at' => '2026-03-20 17:01:00',
            'created_at' => '2026-03-20 17:01:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => '2026-03-20',
            'run_id' => 25,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-20',
            'publication_id' => 10,
            'run_id' => 25,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
    }





    public function test_pointer_resolution_returns_null_when_pointed_publication_run_terminal_status_is_not_success(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'terminal_status' => 'HELD',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_pointed_publication_run_publishability_is_not_readable(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'publishability_state' => 'NOT_READABLE',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_pointed_publication_run_row_is_missing(): void
    {
        DB::table('eod_runs')->where('run_id', 25)->delete();

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }


    public function test_pointer_resolution_returns_null_when_pointed_publication_run_requested_trade_date_mismatches_pointer_trade_date(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'trade_date_requested' => '2026-03-19',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_pointed_publication_sealed_at_is_missing(): void
    {
        DB::table('eod_publications')
            ->where('publication_id', 10)
            ->update([
                'sealed_at' => null,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_pointed_publication_run_sealed_at_is_missing(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'sealed_at' => null,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }


    public function test_pointer_resolution_returns_null_when_run_current_mirror_disagrees_with_pointer_and_publication(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'is_current_publication' => 0,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_publication_trade_date_mismatches_pointer_trade_date(): void
    {
        DB::table('eod_publications')
            ->where('publication_id', 10)
            ->update([
                'trade_date' => '2026-03-19',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
    }


    public function test_pointer_resolution_returns_null_when_pointer_publication_version_mismatches_pointed_publication(): void
    {
        $this->seedPointerToPublicationWithDifferentVersion();

        $repository = new EodPublicationRepository();

        $this->assertNull($repository->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repository->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repository->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repository->findLatestReadablePublicationBefore('2026-03-21'));
    }



    public function test_pointer_resolution_returns_null_when_publication_current_mirror_disagrees_with_pointer(): void
    {
        DB::table('eod_publications')
            ->where('publication_id', 10)
            ->update([
                'is_current' => 0,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }


    public function test_pointer_resolution_returns_null_when_pointer_publication_version_mismatches_pointed_publication_version(): void
    {
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->update([
                'publication_version' => 2,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_pointer_resolution_returns_null_when_pointer_run_id_mismatches_pointed_publication_run(): void
    {
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->update([
                'run_id' => 27,
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_candidate_seal_and_promote_updates_current_pointer_and_prior_publication(): void
    {
        $repo = new EodPublicationRepository();
        $run = App\Models\EodRun::query()->findOrFail(27);

        $candidate = $repo->getOrCreateCandidatePublication($run, 10);
        $this->assertSame(2, (int) $candidate->publication_version);
        $this->assertSame(0, (int) $candidate->is_current);

        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => 'bars-new',
            'indicators_batch_hash' => 'ind-new',
            'eligibility_batch_hash' => 'elig-new',
        ]);

        $sealed = $repo->sealCandidatePublication($run, 'system');
        $this->assertSame('SEALED', $sealed->seal_state);
        $this->assertNotNull($sealed->sealed_at);

        $promoted = $repo->promoteCandidateToCurrent($run, 10);
        $this->assertSame(1, (int) $promoted->is_current);
        $this->assertSame(10, (int) $promoted->supersedes_publication_id);

        // Pakai method yang memang select alias run_terminal_status.
        $current = $repo->findPointerResolvedPublicationForTradeDate('2026-03-20');

        $this->assertNotNull($current);
        $this->assertSame((int) $candidate->publication_id, (int) $current->publication_id);
        $this->assertSame(27, (int) $current->pointer_run_id);
        $this->assertSame('SUCCESS', $current->run_terminal_status);
        $this->assertSame('READABLE', $current->run_publishability_state);

        $newRun = DB::table('eod_runs')->where('run_id', 27)->first();
        $this->assertSame((int) $candidate->publication_id, (int) $newRun->publication_id);
        $this->assertSame((int) $candidate->publication_version, (int) $newRun->publication_version);
        $this->assertSame(1, (int) $newRun->is_current_publication);

        $oldRun = DB::table('eod_runs')->where('run_id', 25)->first();
        $this->assertSame(0, (int) $oldRun->is_current_publication);

        $old = DB::table('eod_publications')->where('publication_id', 10)->first();
        $this->assertNotNull($old);
        $this->assertSame(0, (int) $old->is_current);
    }


    public function test_promote_candidate_to_current_blocks_uncontrolled_replace_when_valid_current_exists(): void
    {
        $repo = new EodPublicationRepository();
        $run = App\Models\EodRun::query()->findOrFail(27);

        $candidate = $repo->getOrCreateCandidatePublication($run, null);

        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => 'bars-new',
            'indicators_batch_hash' => 'ind-new',
            'eligibility_batch_hash' => 'elig-new',
        ]);

        $repo->sealCandidatePublication($run, 'system');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Current publication already exists for trade date 2026-03-20. Use --force_replace=true with an audit reason to replace it via operator-controlled switch.');

        $repo->promoteCandidateToCurrent($run);
    }




    public function test_promote_candidate_to_current_allows_operator_force_replace_when_valid_current_exists(): void
    {
        $repo = new EodPublicationRepository();
        $run = App\Models\EodRun::query()->findOrFail(27);

        $candidate = $repo->getOrCreateCandidatePublication($run, null);

        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => 'bars-force',
            'indicators_batch_hash' => 'ind-force',
            'eligibility_batch_hash' => 'elig-force',
        ]);

        $repo->sealCandidatePublication($run, 'system');

        $promoted = $repo->promoteCandidateToCurrent($run, null, true);

        $this->assertSame((int) $candidate->publication_id, (int) $promoted->publication_id);
        $this->assertSame(1, (int) $promoted->is_current);
        $this->assertSame(10, (int) $promoted->supersedes_publication_id);
        $this->assertSame(10, (int) $promoted->previous_publication_id);
        $this->assertSame(10, (int) $promoted->replaced_publication_id);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame((int) $candidate->publication_id, (int) $pointer->publication_id);
        $this->assertSame(27, (int) $pointer->run_id);

        $oldPublication = DB::table('eod_publications')->where('publication_id', 10)->first();
        $this->assertSame(0, (int) $oldPublication->is_current);

        $oldRun = DB::table('eod_runs')->where('run_id', 25)->first();
        $newRun = DB::table('eod_runs')->where('run_id', 27)->first();

        $this->assertSame(0, (int) $oldRun->is_current_publication);
        $this->assertSame(1, (int) $newRun->is_current_publication);
    }

    public function test_find_invalid_current_publication_states_detects_failed_not_readable_current_pointer(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();
        $invalid = $repo->findInvalidCurrentPublicationStates('2026-03-20');

        $this->assertCount(1, $invalid);
        $this->assertSame('2026-03-20', (string) $invalid[0]->pointer_trade_date);
        $this->assertSame('FAILED', (string) $invalid[0]->terminal_status);
        $this->assertSame('NOT_READABLE', (string) $invalid[0]->publishability_state);
    }

    public function test_promote_candidate_to_current_rejects_when_existing_current_pointer_integrity_is_invalid(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'terminal_status' => 'FAILED',
                'publishability_state' => 'NOT_READABLE',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 11,
            'trade_date' => '2026-03-20',
            'run_id' => 27,
            'publication_version' => 2,
            'is_current' => 0,
            'supersedes_publication_id' => 10,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-new',
            'indicators_batch_hash' => 'ind-new',
            'eligibility_batch_hash' => 'elig-new',
            'sealed_at' => '2026-03-20 17:21:00',
            'created_at' => '2026-03-20 17:21:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);

        $run = \App\Models\EodRun::query()->where('run_id', 27)->firstOrFail();
        $repo = new EodPublicationRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid current publication integrity detected for trade date 2026-03-20.');

        $repo->promoteCandidateToCurrent($run);
    }


    public function test_candidate_publication_persists_source_identity_and_lineage_fields(): void
    {
        DB::table('eod_runs')->where('run_id', 27)->update([
            'source_file_hash' => str_repeat('a', 64),
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => 128,
            'source_file_row_count' => 2,
            'updated_at' => '2026-03-20 17:22:00',
        ]);

        $run = EodRun::query()->where('run_id', 27)->first();
        $repo = new EodPublicationRepository();

        $candidate = $repo->getOrCreateCandidatePublication($run, 10);

        $this->assertSame(2, (int) $candidate->publication_version);
        $this->assertSame(10, (int) $candidate->supersedes_publication_id);
        $this->assertSame(10, (int) $candidate->previous_publication_id);
        $this->assertSame(10, (int) $candidate->replaced_publication_id);
        $this->assertSame(str_repeat('a', 64), $candidate->source_file_hash);
        $this->assertSame('SHA-256', $candidate->source_file_hash_algorithm);
        $this->assertSame(128, (int) $candidate->source_file_size_bytes);
        $this->assertSame(2, (int) $candidate->source_file_row_count);
    }

    public function test_sealed_publication_rejects_hash_mutation_with_immutable_reason_code(): void
    {
        $repo = new EodPublicationRepository();
        $run = EodRun::query()->where('run_id', 27)->first();
        $candidate = $repo->getOrCreateCandidatePublication($run, 10);

        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => 'bars-new',
            'indicators_batch_hash' => 'ind-new',
            'eligibility_batch_hash' => 'elig-new',
        ]);
        $repo->sealCandidatePublication($run, 'system', 'test seal');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SEALED_PUBLICATION_IMMUTABLE');

        $repo->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => 'bars-mutated',
            'indicators_batch_hash' => 'ind-mutated',
            'eligibility_batch_hash' => 'elig-mutated',
        ]);
    }

    protected function seedPointerToPublicationWithDifferentVersion()
    {
        DB::table('eod_runs')->insert([
            'run_id' => 125,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => 1,
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 100,
            'coverage_available_count' => 100,
            'coverage_missing_count' => 0,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'pointer-publication-version-mismatch-incident',
            'bars_batch_hash' => 'bars-version-mismatch',
            'indicators_batch_hash' => 'ind-version-mismatch',
            'eligibility_batch_hash' => 'elig-version-mismatch',
            'config_version' => 'v1',
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:21:00',
            'sealed_by' => 'system',
            'seal_note' => 'pointer-publication-version-mismatch-incident',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:21:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 110,
            'trade_date' => '2026-03-20',
            'publication_version' => 1,
            'run_id' => 125,
            'seal_state' => 'SEALED',
            'is_current' => 1,
            'sealed_at' => '2026-03-20 17:21:00',
            'created_at' => '2026-03-20 17:21:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);

        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->update([
                'publication_id' => 110,
                'publication_version' => 999,
                'run_id' => 125,
                'sealed_at' => '2026-03-20 17:21:00',
                'updated_at' => '2026-03-20 17:21:00',
            ]);
    }
    public function test_restore_prior_current_publication_rejects_not_readable_fallback_run(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Current publication integrity violation: current pointer requires run terminal_status SUCCESS.');

        $repo->restorePriorCurrentPublication('2026-03-20', 10, 25);
    }
    public function test_pointer_resolution_returns_null_when_pointed_publication_coverage_gate_is_not_pass(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'coverage_gate_state' => 'FAIL',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $repo = new EodPublicationRepository();

        $this->assertNull($repo->findPointerResolvedPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findCorrectionBaselinePublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findLatestReadablePublicationBefore('2026-03-21'));
    }

    public function test_restore_prior_current_publication_rejects_readable_run_without_coverage_pass(): void
    {
        DB::table('eod_runs')
            ->where('run_id', 25)
            ->update([
                'coverage_gate_state' => 'FAIL',
                'updated_at' => '2026-03-20 17:25:00',
            ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('current pointer requires run coverage_gate_state PASS');

        (new EodPublicationRepository())->restorePriorCurrentPublication('2026-03-20', 10, 25);
    }


}
