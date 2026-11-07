<?php

namespace Tests\Support;

/**
 * Comparison-unit port adapter, NOT authentication or persistence proof.
 * Keep the existing metric/count expectations intact behind the new staging port.
 * Real issuer, fencing and atomicity are exercised by the native MariaDB tests.
 */
final class ReplayResultBoundaryDouble
{
    public static function adapt($repository): void
    {
        $repository->shouldReceive('duringVerifiedRequest')->andReturnUsing(function (...$arguments) {
            return $arguments[count($arguments) - 1]();
        });
        $repository->shouldReceive('stageResult')->andReturnUsing(function (array $metric, array $counts) use ($repository) {
            $repository->upsertMetric($metric);
            $repository->replaceReasonCodeCounts($metric['replay_id'], $metric['trade_date'], $counts);
        });
    }
}
