<?php

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestEvaluationRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOosEvaluationRepository;

class WatchlistBacktestOosPersistenceTest extends TestCase
{
    public function test_is_eval_exact_duplicate_is_idempotent_and_conflict_fails_closed(): void
    {
        $repository = new class extends WatchlistBacktestEvaluationRepository {
            public ?array $stored = null;
            protected function findExisting(array $payload): ?array
            {
                return $this->stored;
            }
            protected function insertRow(array $payload): int
            {
                $this->stored = array_merge(['eval_id' => 10], $payload);
                return 10;
            }
        };
        $row = $this->evalRow();

        $first = $repository->persist($row);
        $second = $repository->persist($row);

        $this->assertSame('INSERTED', $first['status']);
        $this->assertSame('IDEMPOTENT', $second['status']);
        $this->assertSame(10, $second['eval_id']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_EVAL_IDENTITY_CONFLICT');
        $changed = $row;
        $changed['avg_ret_net_top'] = 0.99;
        $repository->persist($changed);
    }

    public function test_oos_exact_duplicate_is_idempotent_and_conflict_fails_closed(): void
    {
        $repository = new class extends WatchlistBacktestOosEvaluationRepository {
            public ?array $stored = null;
            protected function findExisting(array $payload): ?array
            {
                return $this->stored;
            }
            protected function insertRow(array $payload): int
            {
                $this->stored = array_merge(['oos_id' => 20], $payload);
                return 20;
            }
        };
        $row = $this->oosRow();

        $first = $repository->persist($row);
        $second = $repository->persist($row);

        $this->assertSame('INSERTED', $first['status']);
        $this->assertSame('IDEMPOTENT', $second['status']);
        $this->assertSame(20, $second['oos_id']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_OOS_PROOF_MISSING');
        $changed = $row;
        $changed['picks_count_oos'] = 99;
        $repository->persist($changed);
    }


    public function test_oos_identity_includes_frozen_is_eval_id(): void
    {
        $repository = new class extends WatchlistBacktestOosEvaluationRepository {
            public array $stored = [];
            protected function findExisting(array $payload): ?array
            {
                foreach ($this->stored as $row) {
                    $same = true;
                    foreach ([
                        'policy_code', 'policy_version', 'eval_model', 'param_id_best_is', 'is_eval_id',
                        'from_date_is', 'to_date_is', 'from_date_oos', 'to_date_oos',
                    ] as $field) {
                        if ($row[$field] !== $payload[$field]) {
                            $same = false;
                            break;
                        }
                    }
                    if ($same) {
                        return $row;
                    }
                }

                return null;
            }
            protected function insertRow(array $payload): int
            {
                $id = count($this->stored) + 1;
                $this->stored[] = array_merge(['oos_id' => $id], $payload);

                return $id;
            }
        };

        $first = $this->oosRow();
        $second = $first;
        $second['is_eval_id'] = 11;

        $this->assertSame('INSERTED', $repository->persist($first)['status']);
        $this->assertSame('INSERTED', $repository->persist($second)['status']);
        $this->assertCount(2, $repository->stored);
    }

    private function evalRow(): array
    {
        return [
            'policy_code' => 'WS', 'param_id' => 1,
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            'paramset_hash' => sha1('paramset-1'),
            'from_date' => '2025-01-01', 'to_date' => '2025-12-31',
            'days_covered' => 200, 'picks_count' => 130, 'avg_ret_net_top' => 0.02, 'win_rate_top' => 0.55,
            'median_ret_net_top' => 0.01, 'p25_ret_net_top' => -0.02, 'p75_ret_net_top' => 0.04,
            'min_ret_net_top' => -0.05, 'max_ret_net_top' => 0.10, 'periods_count' => 12,
            'period_fail_count' => 1, 'month_win_rate_min' => 0.46, 'month_avg_ret_net_min' => -0.005,
            'avg_ret_net_all' => null, 'win_rate_all' => null, 'median_ret_net_all' => null,
            'p25_ret_net_all' => null, 'p75_ret_net_all' => null, 'min_ret_net_all' => null, 'max_ret_net_all' => null,
        ];
    }

    private function oosRow(): array
    {
        return [
            'policy_code' => 'WS', 'policy_version' => 'WS_EOD_RUNTIME',
            'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            'param_id_best_is' => 1, 'is_eval_id' => 10,
            'from_date_is' => '2025-01-01', 'to_date_is' => '2025-09-30',
            'from_date_oos' => '2025-10-01', 'to_date_oos' => '2025-12-31',
            'days_covered_oos' => 60, 'picks_count_oos' => 50, 'avg_ret_net_top_oos' => 0.02,
            'win_rate_top_oos' => 0.54, 'median_ret_net_top_oos' => 0.01,
            'p25_ret_net_top_oos' => -0.02, 'month_win_rate_min_oos' => 0.46,
        ];
    }
}
