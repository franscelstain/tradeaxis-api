<?php

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestEvaluationRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestR2PersistenceTest extends TestCase
{
    public function test_evaluation_exact_duplicate_is_idempotent_and_conflict_fails_closed(): void
    {
        $repository = new class extends WatchlistBacktestEvaluationRepository {
            public array $rows = [];
            protected function findExisting(array $payload): ?array
            {
                $key = $this->key($payload);
                return $this->rows[$key] ?? null;
            }
            protected function insertRow(array $payload): int
            {
                $id = count($this->rows) + 1;
                $payload['eval_id'] = $id;
                $this->rows[$this->key($payload)] = $payload;
                return $id;
            }
            private function key(array $payload): string
            {
                return implode('|', [
                    $payload['policy_code'], $payload['catalog_code'], $payload['catalog_version'],
                    $payload['param_id'], $payload['eval_model'], $payload['eval_model_hash'],
                    $payload['implementation_version'], $payload['implementation_hash'], $payload['paramset_hash'],
                    $payload['from_date'], $payload['to_date'],
                ]);
            }
        };

        $row = $this->evaluationRow();
        $first = $repository->persist($row);
        $second = $repository->persist($row);

        $this->assertSame('INSERTED', $first['status']);
        $this->assertSame('IDEMPOTENT', $second['status']);
        $this->assertSame($first['eval_id'], $second['eval_id']);

        $conflict = $row;
        $conflict['avg_ret_net_top'] = 0.99;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_EVAL_IDENTITY_CONFLICT');
        $repository->persist($conflict);
    }

    public function test_repository_validator_rejects_duplicate_parameter_payload_and_invalid_invariants(): void
    {
        $repository = new WatchlistBacktestParamGridRepository();
        $method = new ReflectionMethod($repository, 'validateCatalog');
        $method->setAccessible(true);

        $duplicate = WatchlistBacktestR2ParamGridCatalog::rows();
        $duplicate[1] = $duplicate[0];
        $duplicate[1]['row_code'] = 'DUPLICATE_ROW';
        $duplicate[1]['row_hash'] = sha1($duplicate[1]['catalog_code'].'|DUPLICATE_ROW');

        try {
            $method->invoke($repository, $duplicate);
            $this->fail('Duplicate canonical parameter combination should fail closed.');
        } catch (ReflectionException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->assertStringContainsString('duplicate canonical parameter combination', $e->getMessage());
        }

        $invalid = WatchlistBacktestR2ParamGridCatalog::rows();
        $invalid[1]['w_risk'] += 0.01;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('scoring weights must sum to 1.0');
        $method->invoke($repository, $invalid);
    }

    public function test_catalog_identity_is_part_of_eval_identity(): void
    {
        $source = file_get_contents(base_path(
            'app/Infrastructure/Persistence/Watchlist/WatchlistBacktestEvaluationRepository.php'
        ));

        $this->assertStringContainsString(
            "'policy_code', 'catalog_code', 'catalog_version', 'param_id'",
            $source
        );
        $this->assertStringContainsString("'eval_model', 'eval_model_hash', 'implementation_version', 'implementation_hash'", $source);
        $this->assertStringContainsString("'paramset_hash', 'from_date', 'to_date'", $source);
        $this->assertStringNotContainsString('updateOrInsert', $source);
    }

    private function evaluationRow(): array
    {
        return [
            'policy_code' => 'WS',
            'catalog_code' => WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE,
            'catalog_version' => WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestR2ParamGridCatalog::hash(),
            'param_id' => 100,
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            'eval_model_hash' => sha1('ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS'),
            'implementation_version' => 'WS_CANONICAL_IS_C171_V1',
            'implementation_hash' => sha1('WS_CANONICAL_IS_C171_V1|PLAN_RECOMMENDATION_CONFIRM_REPLAY|PUBLISHED_EOD|NO_FUTURE_ROUTING'),
            'paramset_hash' => sha1('paramset'),
            'from_date' => '2023-01-02',
            'to_date' => '2025-05-21',
            'days_covered' => 500,
            'picks_count' => 130,
            'picks_hash' => sha1('picks'),
            'universe_count' => 500,
            'universe_hash' => sha1('universe'),
            'cutoff_count' => 500,
            'cutoffs_hash' => sha1('cutoffs'),
            'evidence_manifest_hash' => sha1('manifest'),
            'market_data_lineage_hash' => sha1('lineage'),
            'avg_ret_net_top' => 0.01,
            'win_rate_top' => 0.55,
            'median_ret_net_top' => 0.005,
            'p25_ret_net_top' => -0.02,
            'p75_ret_net_top' => 0.03,
            'min_ret_net_top' => -0.05,
            'max_ret_net_top' => 0.08,
            'periods_count' => 24,
            'period_fail_count' => 0,
            'month_win_rate_min' => 0.45,
            'month_avg_ret_net_min' => -0.01,
            'avg_ret_net_all' => null,
            'win_rate_all' => null,
            'median_ret_net_all' => null,
            'p25_ret_net_all' => null,
            'p75_ret_net_all' => null,
            'min_ret_net_all' => null,
            'max_ret_net_all' => null,
        ];
    }
}
