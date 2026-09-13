<?php

use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class ReplayResultRepositoryIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    public function test_replay_result_repository_persists_metric_and_reason_code_counts(): void
    {
        $repo = new ReplayResultRepository();

        $repo->upsertMetric([
            'replay_id' => 3001,
            'replay_mode' => 'PUBLICATION_EXACT',
            'fixture_manifest_hash' => str_repeat('a', 64),
            'config_snapshot_hash' => str_repeat('b', 64),
            'serialization_version' => 'replay-v2',
            'executable_build_identity' => 'test-build',
            'bound_input_context_json' => json_encode(['publication_id' => 44]),
            'trade_date' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'fixture',
            'status' => 'COMPLETED',
            'comparison_result' => 'MATCH',
            'comparison_note' => 'matched all artifacts',
            'config_identity' => 'cfg-1',
            'publication_version' => 2,
            'publishability_state' => 'READABLE',
            'publication_id' => 44,
            'publication_run_id' => 91,
            'is_current_publication' => true,
            'bars_batch_hash' => 'bars-hash',
            'indicators_batch_hash' => 'ind-hash',
            'eligibility_batch_hash' => 'elig-hash',
            'seal_state' => 'SEALED',
            'expected_status' => 'COMPLETED',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_publication_id' => 44,
            'expected_publication_run_id' => 91,
            'expected_is_current_publication' => true,
            'expected_trade_date_effective' => '2026-03-20',
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'cfg-1',
            'expected_publication_version' => 2,
            'expected_bars_batch_hash' => 'bars-hash',
            'expected_indicators_batch_hash' => 'ind-hash',
            'expected_eligibility_batch_hash' => 'elig-hash',
            'expected_reason_code_counts_json' => json_encode([['reason_code' => 'NONE', 'reason_count' => 0]]),
            'mismatch_summary' => null,
        ]);

        $repo->replaceReasonCodeCounts(3001, '2026-03-20', [
            ['reason_code' => 'BAR_TICKER_MAPPING_MISSING', 'reason_count' => 2],
            ['reason_code' => 'NONE', 'reason_count' => 0],
        ]);

        $metric = DB::table('md_replay_daily_metrics')->where('replay_id', 3001)->where('trade_date', '2026-03-20')->first();
        $this->assertSame('PUBLICATION_EXACT', $metric->replay_mode);
        $this->assertSame('MATCH', $metric->comparison_result);
        $this->assertSame('PASS', $metric->replay_status);
        $this->assertSame('READABLE', $metric->publishability_state);
        $this->assertSame(44, (int) $metric->publication_id);
        $this->assertSame(91, (int) $metric->publication_run_id);
        $this->assertSame(1, (int) $metric->is_current_publication);
        $this->assertSame('cfg-1', $metric->expected_config_identity);
        $this->assertSame('SUCCESS', $metric->expected_terminal_status);
        $this->assertSame('READABLE', $metric->expected_publishability_state);
        $this->assertSame(44, (int) $metric->expected_publication_id);
        $this->assertSame(91, (int) $metric->expected_publication_run_id);
        $this->assertSame(1, (int) $metric->expected_is_current_publication);

        $counts = DB::table('md_replay_reason_code_counts')
            ->where('replay_id', 3001)
            ->where('trade_date', '2026-03-20')
            ->orderBy('reason_code')
            ->get();

        $this->assertCount(2, $counts);
        $this->assertSame('BAR_TICKER_MAPPING_MISSING', $counts[0]->reason_code);
        $this->assertSame(2, (int) $counts[0]->reason_count);
    }
    /**
     * `MD-B18` bound inputs: an incomplete set is refused, not persisted.
     *
     * `MD-S050-R0016` says a missing input is `BLOCKED`, never permission to fall back to current
     * state, and `ReplayResultRepository::assertModeInputs()` is where that refusal lives. Until
     * now nothing executed it: the persistence test above supplies a complete metric, so a probe
     * that deleted the whole guard body left the suite green. A rule enforced by code no test
     * reaches is indistinguishable from a rule that was written down.
     *
     * Each case removes exactly one required input and expects the refusal that names it. The
     * `BLOCKED` case is the deliberate exception in the contract: a comparison that did not execute
     * is allowed to carry less, and it must still be storable.
     *
     * @dataProvider incompleteBoundInputSets
     *
     * @param  array<string,mixed>  $overrides
     */
    public function test_an_incomplete_bound_input_set_is_refused_rather_than_persisted(
        array $overrides,
        string $reasonCode
    ): void {
        $repo = new ReplayResultRepository();

        $before = DB::table('md_replay_daily_metrics')->count();

        $thrown = null;
        try {
            $repo->upsertMetric(array_merge($this->completeMetric(), $overrides));
        } catch (\RuntimeException $e) {
            $thrown = $e;
        }

        $this->assertNotNull($thrown, 'an incomplete bound-input set was accepted');
        $this->assertStringStartsWith($reasonCode, $thrown->getMessage());
        $this->assertSame(
            $before,
            DB::table('md_replay_daily_metrics')->count(),
            'the refused result was still written; a refusal that persists is not a refusal'
        );
    }

    /** @return array<string,array{0:array<string,mixed>,1:string}> */
    public function incompleteBoundInputSets(): array
    {
        return [
            'no replay mode' => [
                ['replay_mode' => null],
                'REPLAY_MODE_REQUIRED',
            ],
            'unsupported replay mode' => [
                ['replay_mode' => 'HISTORICAL_DEFAULT'],
                'REPLAY_MODE_UNSUPPORTED',
            ],
            'exact without a publication' => [
                ['publication_id' => null],
                'REPLAY_EXPLICIT_PUBLICATION_REQUIRED',
            ],
            'as-known without a cutoff' => [
                ['replay_mode' => 'AS_KNOWN', 'knowledge_cutoff_at' => null],
                'REPLAY_KNOWLEDGE_CUTOFF_REQUIRED',
            ],
            'no fixture manifest hash' => [
                ['fixture_manifest_hash' => null],
                'REPLAY_BOUND_INPUT_INCOMPLETE',
            ],
            'no config snapshot hash' => [
                ['config_snapshot_hash' => null],
                'REPLAY_BOUND_INPUT_INCOMPLETE',
            ],
            'no serialization version' => [
                ['serialization_version' => null],
                'REPLAY_BOUND_INPUT_INCOMPLETE',
            ],
            'no executable build identity' => [
                ['executable_build_identity' => null],
                'REPLAY_BOUND_INPUT_INCOMPLETE',
            ],
        ];
    }

    /**
     * A `BLOCKED` result carries less by design -- the comparison did not execute -- and must still
     * be storable. Without this the refusal above could be tightened into one that rejects the very
     * outcome `MD-S050-R0031` requires the platform to record.
     */
    public function test_a_blocked_result_is_storable_with_the_reduced_input_set(): void
    {
        $repo = new ReplayResultRepository();

        $repo->upsertMetric(array_merge($this->completeMetric(), [
            'replay_id' => 3099,
            'replay_status' => 'BLOCKED',
            'comparison_result' => 'NOT_ADMISSIBLE',
            'fixture_manifest_hash' => null,
            'config_snapshot_hash' => null,
            'serialization_version' => null,
            'executable_build_identity' => null,
        ]));

        $this->assertSame(
            1,
            DB::table('md_replay_daily_metrics')->where('replay_id', 3099)->count(),
            'a BLOCKED result must remain recordable; refusing it would erase the outcome that says '
                .'the comparison never ran'
        );
    }

    /** @return array<string,mixed> */
    private function completeMetric(): array
    {
        return [
            'replay_id' => 3098,
            'replay_mode' => 'PUBLICATION_EXACT',
            'fixture_manifest_hash' => str_repeat('a', 64),
            'config_snapshot_hash' => str_repeat('b', 64),
            'serialization_version' => 'replay-v2',
            'executable_build_identity' => 'test-build',
            'knowledge_cutoff_at' => '2026-03-20 18:00:00',
            'source_observation_manifest_hash' => str_repeat('c', 64),
            'canonical_raw_input_hash' => str_repeat('d', 64),
            'temporal_identity_hash' => str_repeat('e', 64),
            'calendar_status_hash' => str_repeat('f', 64),
            'event_factor_hash' => str_repeat('0', 64),
            'formula_registry_hash' => str_repeat('1', 64),
            'reason_registry_hash' => str_repeat('2', 64),
            'bound_input_context_json' => json_encode(['publication_id' => 44]),
            'trade_date' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'source' => 'fixture',
            'status' => 'COMPLETED',
            'comparison_result' => 'MATCH',
            'comparison_note' => 'matched all artifacts',
            'config_identity' => 'cfg-1',
            'publication_version' => 2,
            'publishability_state' => 'READABLE',
            'publication_id' => 44,
            'publication_run_id' => 91,
            'is_current_publication' => true,
            'bars_batch_hash' => 'bars-hash',
            'indicators_batch_hash' => 'ind-hash',
            'eligibility_batch_hash' => 'elig-hash',
            'seal_state' => 'SEALED',
            'expected_status' => 'COMPLETED',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_publication_id' => 44,
            'expected_publication_run_id' => 91,
            'expected_is_current_publication' => true,
            'expected_trade_date_effective' => '2026-03-20',
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'cfg-1',
            'expected_publication_version' => 2,
            'expected_bars_batch_hash' => 'bars-hash',
            'expected_indicators_batch_hash' => 'ind-hash',
            'expected_eligibility_batch_hash' => 'elig-hash',
            'expected_reason_code_counts_json' => json_encode([['reason_code' => 'NONE', 'reason_count' => 0]]),
            'mismatch_summary' => null,
        ];
    }
}
