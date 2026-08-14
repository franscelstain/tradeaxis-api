<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService;
use Illuminate\Console\Command;

class RunWeeklySwingBreakoutIntegrityB01PromotionReadinessReviewCommand extends Command
{
    protected $signature =
        'watchlist:weekly-swing-breakout-integrity-b01-promotion-readiness-review
        {--oos-artifact=storage/app/watchlist/backtest/ws-breakout-integrity-b01-official-oos.json : Exact passing Official OOS artifact}
        {--approval-reference= : Exact promotion-readiness review approval reference}
        {--operator-approved : Confirm read-only review; does not execute promotion}
        {--output=storage/app/watchlist/backtest/ws-breakout-integrity-b01-promotion-readiness-review.json : Output artifact}
        {--overwrite : Replace an existing local review artifact}';

    protected $description =
        'Verify exact B01 IS/OOS identity and gates before canonical DRAFT-to-ACTIVE promotion.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(
                WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
                    ::class
            )
            ->execute(
                $this->absolutePath(
                    trim((string) $this->option('oos-artifact'))
                ),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'param_set_id', 'bt_param_id',
            'is_eval_id', 'oos_id', 'params_hash',
            'is_evidence_manifest_hash', 'promotion_readiness_review_pass',
            'canonical_promotion_authorized', 'promotion_executed',
            'paramset_promoted', 'plan_run_created', 'production_ready',
            'next_recommendation', 'artifact_hash',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key])
                    ? ($result[$key] ? '1' : '0')
                    : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }

        return ($result['promotion_readiness_review_pass'] ?? false) === true
            ? 0
            : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/'
            || substr($path, 0, 2) === '\\\\'
            || (strlen($path) >= 3
                && ctype_alpha($path[0])
                && $path[1] === ':'))) {
            return $path;
        }

        return base_path($path);
    }
}
