<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B18-A002` — the identities a replay fixture/manifest must bind, item by item.
 *
 * `Replay_Verification_Contract_LOCKED.md` (`MD-S050`) opens "Every fixture/manifest binds at
 * minimum:" and lists nine kinds of bound input. `MD-S050-R0007`..`R0015` are those nine members,
 * and `MD-S019-R0066`..`R0072` restate seven of them as the antecedent of the reproducibility
 * conditional in `MD-S019`.
 *
 * `F-MD-B19-A001-002` recorded all of these as `UNSUPPORTED` or `PARTIAL`, because the guard they
 * were bound to refuses exactly six inputs — publication, cutoff, fixture-manifest hash,
 * config-snapshot hash, serialization version, executable build identity — and says nothing about
 * the other identities. That verdict was about the guard, not the platform: the export does emit a
 * `bound_inputs` identity block carrying all of them. The obligation was met and unproven.
 *
 * Two directions are asserted, because "binds at minimum" is not established by a single run that
 * happens to carry values:
 *
 *  - every identity the contract names is present in the exported block and carries the value the
 *    fixture bound, so dropping one from the exporter turns this red;
 *  - the identities are read from the replayed record rather than defaulted, so a fixture that
 *    binds different identities produces a different block. A block populated from constants would
 *    satisfy the first assertion and fail the second.
 */
class B18ReplayBoundInputIdentityContractTest extends TestCase
{
    private const CONTRACT = 'docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md';

    private const INTRODUCER = 'Every fixture/manifest binds at minimum:';

    /**
     * Reviewed mapping: the contract's own wording => the `bound_inputs` keys that carry it.
     *
     * `replay mode, fixture ID/version, requested/effective date` and `expected publication/pointer/
     * seal state` are carried outside the `bound_inputs` block, at the replay-result top level, so
     * their paths are written relative to the result rather than to the block.
     *
     * @return array<string,array<int,string>>
     */
    private function identityMap(): array
    {
        return [
            'replay mode, fixture ID/version, requested/effective date, and knowledge cutoff where applicable;' => [
                'replay_mode', 'fixture_id', 'fixture_version', 'trade_date', 'trade_date_effective',
                'knowledge_cutoff_at',
            ],
            'intentional dataset boundary and temporal universe/listing/symbol/provider mappings;' => [
                'bound_inputs.temporal_identity_hash',
            ],
            'Regular-Market calendar/session and trading-status revisions;' => [
                'bound_inputs.calendar_status_hash',
            ],
            'immutable source observation IDs/hashes and adapter/schema/normalization versions;' => [
                'bound_inputs.source_observation_manifest_hash',
            ],
            'canonical `RAW` publication/input set;' => [
                'bound_inputs.canonical_raw_input_hash',
            ],
            'corporate-action event revisions, verification states, factor-set revisions, and contamination decisions;' => [
                'bound_inputs.event_factor_hash',
            ],
            'full configuration snapshot ID/hash;' => [
                'bound_inputs.config_snapshot_id', 'bound_inputs.config_snapshot_hash',
            ],
            'formula, indicator registry, reason registry, price-product, coverage, eligibility, read-model, hash/serialization, and build versions;' => [
                'bound_inputs.formula_registry_hash', 'bound_inputs.reason_registry_hash',
                'bound_inputs.read_model_version', 'bound_inputs.serialization_version',
                'bound_inputs.executable_build_identity',
            ],
            'expected publication/pointer/seal state and deterministic output/hash assertions.' => [
                'expected_seal_state', 'expected_publication_version',
                'expected_pointer_context.pointer_publication_id',
                'expected_pointer_context.pointer_publication_version',
                'expected_bars_batch_hash', 'expected_indicators_batch_hash',
            ],
        ];
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /** @return array<int,string> the nine list members, read from the frozen contract */
    private function contractItems(): array
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $lines = preg_split('/\R/', (string) file_get_contents($path));

        $start = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === self::INTRODUCER) {
                $start = $i;
                break;
            }
        }
        $this->assertNotNull($start, 'the bound-input introducer is no longer in MD-S050; this guard '
            .'is bound to text that moved and must be re-read, not relaxed');

        $items = [];
        for ($i = $start + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            if (strpos($line, '- ') !== 0) {
                break;
            }
            $items[] = trim(substr($line, 2));
        }

        return $items;
    }

    public function test_the_contract_list_and_the_reviewed_mapping_cover_exactly_the_same_items(): void
    {
        $items = $this->contractItems();
        $mapped = array_keys($this->identityMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S050 and the reviewed identity mapping disagree; a bound input added to the contract '
                .'would otherwise be silently unproven');

        // Matching keys is not enough. An item mapped to an empty path list satisfies the key check
        // and contributes nothing to the presence assertion below, which is the same shape as a
        // guard that scans an empty set and reports success. A probe that emptied one item's paths
        // passed this test before this assertion existed.
        $empty = [];
        foreach ($this->identityMap() as $item => $paths) {
            if ($paths === []) {
                $empty[] = $item;
            }
        }
        $this->assertSame([], $empty, 'these contract items are mapped to no path at all');
    }

    public function test_every_named_identity_is_bound_into_the_exported_replay_result(): void
    {
        $result = $this->exportedResult($this->identities('A'));

        $absent = [];
        foreach ($this->identityMap() as $item => $paths) {
            foreach ($paths as $path) {
                $value = $this->dig($result, $path);
                if ($value === null || $value === '') {
                    $absent[] = $path;
                }
            }
        }

        $this->assertSame([], $absent,
            'MD-S050 requires every fixture/manifest to bind these, and the exported replay result '
                .'carries no value for them');

        // Population assertion: say how many paths were actually inspected, so a mapping that
        // quietly shrinks cannot leave this test green on a smaller check than it claims.
        $checked = 0;
        foreach ($this->identityMap() as $paths) {
            $checked += count($paths);
        }
        $this->assertSame(24, $checked,
            'the number of identity paths this guard checks has changed; update the count '
                .'deliberately after reviewing what moved, rather than letting it drift');
    }

    /**
     * The half that makes the first meaningful: the block must come from the replayed record.
     *
     * A `bound_inputs` block filled from constants, or from the current environment rather than the
     * fixture, would satisfy a presence check and destroy the identity the contract is asking for.
     */
    public function test_a_fixture_binding_different_identities_produces_a_different_block(): void
    {
        $first = $this->exportedResult($this->identities('A'))['bound_inputs'];
        $second = $this->exportedResult($this->identities('B'))['bound_inputs'];

        $shared = [];
        foreach ($first as $key => $value) {
            if ($key === 'context') {
                continue;
            }
            if ($value !== null && $value === ($second[$key] ?? null)) {
                $shared[] = $key;
            }
        }

        $this->assertSame([], $shared,
            'these bound inputs did not change when the replayed record bound different identities, '
                .'so they are not being read from the record');
    }

    /** @return array<string,mixed> */
    private function identities(string $tag): array
    {
        return [
            'source_observation_manifest_hash' => 'OBS_'.$tag,
            'canonical_raw_input_hash' => 'RAW_'.$tag,
            'temporal_identity_hash' => 'TEMPORAL_'.$tag,
            'calendar_status_hash' => 'CALENDAR_'.$tag,
            'event_factor_hash' => 'EVENT_'.$tag,
            'config_snapshot_id' => 'CFGID_'.$tag,
            'config_snapshot_hash' => 'CFGHASH_'.$tag,
            'formula_registry_hash' => 'FORMULA_'.$tag,
            'reason_registry_hash' => 'REASON_'.$tag,
            'read_model_version' => 'READMODEL_'.$tag,
            'serialization_version' => 'SER_'.$tag,
            'executable_build_identity' => 'BUILD_'.$tag,
        ];
    }

    private function dig(array $data, string $path)
    {
        $cursor = $data;
        foreach (explode('.', $path) as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    /** @return array<string,mixed> */
    private function exportedResult(array $identities): array
    {
        $metric = (object) array_merge($this->baseMetric(), $identities);

        $evidence = m::mock(EodEvidenceRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $evidence->shouldReceive('findReplayMetric')->once()->with(4102, '2026-02-11')->andReturn($metric);
        $evidence->shouldReceive('replayReasonCodeCounts')->once()->with(4102, '2026-02-11')->andReturn([]);

        $service = new MarketDataEvidenceExportService($evidence, $publications, $corrections);
        $dir = sys_get_temp_dir().'/md_b18_bound_inputs_'.uniqid();
        $service->exportReplayEvidence(4102, '2026-02-11', $dir);

        $decoded = json_decode((string) file_get_contents($dir.'/replay_result.json'), true);
        $this->assertIsArray($decoded, 'replay_result.json is not readable JSON');

        return $decoded;
    }

    /** @return array<string,mixed> */
    private function baseMetric(): array
    {
        return [
            'replay_id' => 4102,
            'replay_mode' => 'EXACT_PUBLICATION',
            'trade_date' => '2026-02-11',
            'trade_date_effective' => '2026-02-11',
            'knowledge_cutoff_at' => '2026-02-11 18:00:00',
            'fixture_id' => 'fixture_exact_publication_v3',
            'fixture_version' => '3',
            'fixture_schema_version' => '1',
            'fixture_manifest_hash' => 'FIXTURE_MANIFEST_HASH',
            'replay_suite' => 'exact_publication',
            'replay_case' => 'reproduces_sealed_publication',
            'source' => 'api',
            'source_mode' => 'api',
            'source_name' => 'YAHOO_FINANCE',
            'source_provider' => 'yahoo',
            'status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'final_reason_code' => null,
            'publication_id' => 8801,
            'publication_run_id' => 551,
            'publication_version' => 2,
            'is_current_publication' => 1,
            'comparison_result' => 'MATCH',
            'replay_status' => 'PASS',
            'comparison_note' => null,
            'artifact_changed_scope' => null,
            'config_identity' => 'cfg_2026_02_v1',
            'coverage_universe_count' => 800,
            'coverage_expected_count' => 800,
            'coverage_available_count' => 800,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.0000',
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'PASS',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_missing_sample_json' => json_encode([]),
            'bars_rows_written' => 800,
            'indicators_rows_written' => 800,
            'eligibility_rows_written' => 800,
            'eligible_count' => 700,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'warning_count' => 0,
            'hard_reject_count' => 0,
            'bars_batch_hash' => 'BARS_HASH',
            'indicators_batch_hash' => 'IND_HASH',
            'eligibility_batch_hash' => 'ELIG_HASH',
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-02-11 17:30:00',
            'bound_input_context_json' => json_encode(['scenario' => 'exact']),
            'expected_status' => 'SUCCESS',
            'expected_terminal_status' => 'SUCCESS',
            'expected_publishability_state' => 'READABLE',
            'expected_source_mode' => 'api',
            'expected_source_name' => 'YAHOO_FINANCE',
            'expected_source_provider' => 'yahoo',
            'expected_publication_id' => 8801,
            'expected_publication_run_id' => 551,
            'expected_is_current_publication' => 1,
            'expected_trade_date_effective' => '2026-02-11',
            'expected_seal_state' => 'SEALED',
            'expected_config_identity' => 'cfg_2026_02_v1',
            'expected_publication_version' => 2,
            'expected_coverage_universe_count' => 800,
            'expected_coverage_expected_count' => 800,
            'expected_coverage_available_count' => 800,
            'expected_coverage_missing_count' => 0,
            'expected_coverage_ratio' => '1.0000',
            'expected_coverage_min_threshold' => '0.9800',
            'expected_coverage_gate_state' => 'PASS',
            'expected_coverage_threshold_mode' => 'MIN_RATIO',
            'expected_coverage_universe_basis' => 'active_equity_universe_asof_trade_date',
            'expected_coverage_contract_version' => 'coverage_gate_v1',
            'expected_coverage_missing_sample_json' => json_encode([]),
            'expected_bars_batch_hash' => 'BARS_HASH',
            'expected_indicators_batch_hash' => 'IND_HASH',
            'expected_eligibility_batch_hash' => 'ELIG_HASH',
            'expected_reason_code_counts_json' => json_encode([]),
            'mismatch_summary' => null,
            'created_at' => '2026-02-11T18:15:00+07:00',
        ];
    }
}
