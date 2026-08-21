<?php

use App\Application\MarketData\Services\EligibilityDecisionService;
use App\Domain\MarketData\MarketDataScope;
use PHPUnit\Framework\TestCase;

/**
 * MD-B01-A013 — completion proof for the remaining executable scope, boundary, terminology, and
 * orchestration predicates. Existing behavioural suites remain the proof owners for detailed
 * eligibility, actual/proxy, provider, and dependency-direction behaviour; this guard closes the
 * cross-surface predicates those suites cannot establish individually.
 */
class ScopeBoundaryAndOrchestrationCompletionTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $this->assertFileExists($path, $relative.' must resolve');

        return (string) file_get_contents($path);
    }

    /** @return array<int,string> */
    private function scopeErrors(string $summary, string $scope, string $readiness): array
    {
        $errors = [];
        foreach ([
            '/owner contracts prevail/i',
            '/IDX Regular-Market EOD on `Asia\/Jakarta`/',
            '/Watchlist Weekly Swing is the initial consumer profile/i',
            '/5 IDX trading days/',
            '/3 to 15 trading days/',
            '/never a market-data readiness or completion gate/i',
            '/Market-data facts may flow to watchlist policy as inputs/i',
            '/`eligible` is a compatibility alias for upstream `data_usable`/i',
        ] as $pattern) {
            if (! preg_match($pattern, $summary)) {
                $errors[] = 'summary missing '.$pattern;
            }
        }
        foreach ([
            "MARKET_CODE = 'IDX'", "MARKET_SEGMENT = 'REGULAR'", "FREQUENCY = 'EOD'",
            "scopeTimezone !== 'Asia/Jakarta'",
        ] as $needle) {
            if (strpos($scope, $needle) === false) {
                $errors[] = 'scope missing '.$needle;
            }
        }
        if (strpos($readiness, 'EodPublicationRepository') === false || strpos($readiness, 'MarketDataScope') === false) {
            $errors[] = 'readiness has no positive scope/publication dependency';
        }
        if (preg_match('/Watchlist|Ranking|Screening|Portfolio|Backtest|Signal/', $readiness)) {
            $errors[] = 'readiness imports or names downstream policy';
        }

        return $errors;
    }

    public function test_scope_summary_matches_the_executable_boundary_and_keeps_weekly_swing_downstream(): void
    {
        $summary = $this->read('docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md');
        $scope = $this->read('app/Domain/MarketData/MarketDataScope.php');
        $readiness = $this->read('app/Application/MarketData/Services/MarketDataReadinessService.php');

        $this->assertSame([], $this->scopeErrors($summary, $scope, $readiness));
        $this->assertSame('IDX', MarketDataScope::MARKET_CODE);
        $this->assertSame('REGULAR', MarketDataScope::MARKET_SEGMENT);
        $this->assertSame('EOD', MarketDataScope::FREQUENCY);
        $this->assertSame('Asia/Jakarta', (new MarketDataScope('Asia/Jakarta', '2023-01-02'))->timezone());
    }

    /** @return array<int,string> */
    private function providerErrors(string $summary, string $port, string $adapter): array
    {
        $errors = [];
        foreach ([
            '/Yahoo Finance dipakai sebagai bootstrap source/i',
            '/bukan klaim bahwa Yahoo adalah sumber resmi IDX atau provider final/i',
            '/provider-neutral canonical contracts/i',
        ] as $pattern) {
            if (! preg_match($pattern, $summary)) {
                $errors[] = 'provider summary missing '.$pattern;
            }
        }
        if (preg_match('/yahoo|range=|interval=|query1\.finance|period1|period2/i', $port)) {
            $errors[] = 'provider transport leaked into acquisition port';
        }
        foreach (['fetchOrLoadEodBarsRange', '$startDate', '$endDate'] as $needle) {
            if (strpos($port, $needle) === false) {
                $errors[] = 'provider-neutral port missing '.$needle;
            }
        }
        foreach (['query1.finance', 'provides_official_board_or_trading_status', 'provides_authoritative_corporate_actions', 'provides_actual_traded_value'] as $needle) {
            if (strpos($adapter, $needle) === false) {
                $errors[] = 'adapter missing explicit transport/capability '.$needle;
            }
        }

        return $errors;
    }

    public function test_yahoo_bootstrap_decision_matches_the_provider_neutral_port_and_explicit_adapter_limits(): void
    {
        $this->assertSame([], $this->providerErrors(
            $this->read('docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md'),
            $this->read('app/Application/MarketData/Ports/ApiEodBarsSource.php'),
            $this->read('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php')
        ));
    }

    /** @return array<int,string> */
    private function orchestrationErrors(string $build, string $register, string $current, string $roles): array
    {
        $errors = [];
        $expected = [];
        for ($i = 0; $i <= 22; $i++) {
            $expected[] = 'MD-B'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        }
        preg_match_all('/^\| `(MD-B\d{2})` \|/m', $build, $buildMatches);
        preg_match_all('/^\| `(MD-B\d{2})` \|/m', $register, $registerMatches);
        if (($buildMatches[1] ?? []) !== $expected) {
            $errors[] = 'build sequence is not exactly MD-B00..MD-B22';
        }
        if (($registerMatches[1] ?? []) !== $expected) {
            $errors[] = 'stage register is not exactly MD-B00..MD-B22';
        }
        preg_match_all('/\*\*Single exact next executable resume point:\*\*\s*([^\r\n]+)/', $register, $registerResume);
        preg_match_all('/^- Single exact next executable resume point:\s*([^\r\n]+)/m', $current, $currentResume);
        if (count($registerResume[1] ?? []) !== 1 || count($currentResume[1] ?? []) !== 1) {
            $errors[] = 'resume point is missing or ambiguous';
        } elseif (trim($registerResume[1][0]) !== trim($currentResume[1][0])) {
            $errors[] = 'generated resume differs from canonical orchestration';
        }
        foreach ([
            '/authority\/governance\/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX\.csv,REGISTRY,GOVERNANCE_REGISTRY,MUTABLE_TRACEABLE/',
            '/development\/implementation\/MD_IMPLEMENTATION_STAGE_REGISTER\.md,STATUS_LEDGER,CURRENT_STATUS_INDEX,MUTABLE_TRACEABLE/',
            '/Market_Data_Strategy_Implementation_Blueprint_LOCKED\.md,HISTORY,HISTORICAL_RECORD,IMMUTABLE_AFTER_ISSUE/',
            '/Market_Data_Implementation_Command_Protocol_LOCKED\.md,HISTORY,HISTORICAL_RECORD,IMMUTABLE_AFTER_ISSUE/',
            '/Market_Data_Implementation_Conformance_Matrix_LOCKED\.md,HISTORY,HISTORICAL_RECORD,IMMUTABLE_AFTER_ISSUE/',
        ] as $pattern) {
            if (! preg_match($pattern, $roles)) {
                $errors[] = 'document-role separation missing '.$pattern;
            }
        }

        return $errors;
    }

    public function test_governed_execution_is_sequential_with_one_resume_and_distinct_authoritative_roles(): void
    {
        $this->assertSame([], $this->orchestrationErrors(
            $this->read('docs/market_data/development/implementation/MD_IMPLEMENTATION_BUILD_SEQUENCE.md'),
            $this->read('docs/market_data/development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md'),
            $this->read('docs/market_data/development/implementation/CURRENT_STATE.md'),
            $this->read('docs/market_data/authority/governance/DOCUMENT_ROLE_REGISTRY.csv')
        ));
    }

    /** @return array<int,string> */
    private function decisionGradeErrors(string $terminology, string $systemMap, string $eligibility): array
    {
        $errors = [];
        foreach (['1. **As-known**', '2. **Single declared basis**', '3. **Correct or explicitly blocked**', '4. **Timely enough to be usable**', 'a bounded check that stays silent must not be read as satisfying it'] as $needle) {
            if (stripos($terminology, $needle) === false) {
                $errors[] = 'decision-grade owner missing '.$needle;
            }
        }
        if (! preg_match('/Terminology_and_Scope\.md.*bila ringkasan ini berbeda, definisi owner tersebut yang berlaku/is', $systemMap)) {
            $errors[] = 'system summary lacks owner pointer and precedence';
        }
        foreach (['ELIG_MISSING_BAR', 'ELIG_MISSING_INDICATORS', 'ELIG_INVALID_INDICATORS', 'ELIG_CORPORATE_ACTION_DISCONTINUITY', 'ELIG_PRICE_SCALE_DISCONTINUITY'] as $reason) {
            if (strpos($eligibility, $reason) === false) {
                $errors[] = 'explicit blocking reason missing '.$reason;
            }
        }

        return $errors;
    }

    public function test_headline_quality_term_has_one_owner_and_unusable_data_is_explicitly_reason_blocked(): void
    {
        $eligibilitySource = $this->read('app/Application/MarketData/Services/EligibilityDecisionService.php');
        $this->assertSame([], $this->decisionGradeErrors(
            $this->read('docs/market_data/authority/strategy/book/Terminology_and_Scope.md'),
            $this->read('docs/market_data/development/implementation/guides/system/SYSTEM_DATA_PRODUCT_MAP.md'),
            $eligibilitySource
        ));

        $service = new EligibilityDecisionService();
        $this->assertSame(['eligible' => 0, 'reason_code' => 'ELIG_MISSING_BAR'], $service->decide(null, null));
        $this->assertSame('ELIG_MISSING_INDICATORS', $service->decide(['close' => 1], null)['reason_code']);
        $this->assertSame('ELIG_INVALID_INDICATORS', $service->decide(['close' => 1], ['is_valid' => 0, 'invalid_reason_code' => 'UNKNOWN'])['reason_code']);
    }

    /** @return array<int,string> */
    private function horizonErrors(string $summary, string $config, string $readiness): array
    {
        $errors = [];
        foreach (['5 IDX trading days', '3 to 15 trading days', 'never a market-data readiness or completion gate'] as $needle) {
            if (stripos($summary, $needle) === false) {
                $errors[] = 'horizon summary missing '.$needle;
            }
        }
        foreach ([
            "/'dv_window_days'\s*=>[^\n]*20/",
            "/'atr_window_days'\s*=>[^\n]*14/",
            "/'roc_lookback_days'\s*=>[^\n]*20/",
            "/'hh_window_days'\s*=>[^\n]*20/",
        ] as $pattern) {
            if (! preg_match($pattern, $config)) {
                $errors[] = 'concrete dependency number missing '.$pattern;
            }
        }
        if (preg_match('/Weekly Swing|holding range|decision horizon/i', $readiness)) {
            $errors[] = 'consumer horizon leaked into readiness';
        }

        return $errors;
    }

    public function test_horizon_generates_concrete_trading_day_requirements_without_becoming_a_readiness_gate(): void
    {
        $this->assertSame([], $this->horizonErrors(
            $this->read('docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md'),
            $this->read('config/market_data.php'),
            $this->read('app/Application/MarketData/Services/MarketDataReadinessService.php')
        ));
    }

    /** @return array<int,string> */
    private function operationalOwnerErrors(string $terminology, string $resilience): array
    {
        $errors = [];
        if (strpos($terminology, 'operational gate list that must be satisfied before the marker may be set is owned by `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`') === false) {
            $errors[] = 'terminology does not point to the gate owner';
        }
        if (strpos($resilience, 'proof, bukan terminologi') === false) {
            $errors[] = 'operational contract does not preserve the owner split';
        }
        if (! preg_match('/### Operational activation gates(.*?)(?=\n## )/s', $resilience, $section)) {
            $errors[] = 'operational activation section missing';
        } else {
            preg_match_all('/^\d+\. /m', $section[1], $items);
            if (count($items[0]) !== 6) {
                $errors[] = 'operational gate owner must enumerate exactly six current gates';
            }
        }

        return $errors;
    }

    public function test_operational_activation_meaning_and_gate_requirements_have_distinct_owners(): void
    {
        $this->assertSame([], $this->operationalOwnerErrors(
            $this->read('docs/market_data/authority/strategy/book/Terminology_and_Scope.md'),
            $this->read('docs/market_data/authority/strategy/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md')
        ));

        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');
        $this->assertSame('DEVELOPMENT', $scope->stateFor('2026-01-02'));
        $activated = new MarketDataScope('Asia/Jakarta', '2023-01-02', '2026-01-02');
        $this->assertSame('DEVELOPMENT', $activated->stateFor('2026-01-01'));
        $this->assertSame('OPERATIONAL', $activated->stateFor('2026-01-02'));
    }

    private function executableReplaySurface(): string
    {
        $root = $this->root();
        $parts = [];
        foreach ([
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Application/MarketData/Services/ReplayBackfillService.php',
            'app/Application/MarketData/Services/FullRangeCurrentEvidenceReplayService.php',
            'app/Console/Commands/MarketData/VerifyReplayCommand.php',
            'app/Console/Commands/MarketData/ReplayBackfillCommand.php',
        ] as $relative) {
            $source = $this->read($relative);
            $code = '';
            foreach (token_get_all($source) as $token) {
                if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }
                $code .= is_array($token) ? $token[1] : $token;
            }
            $parts[] = $code;
        }

        return implode("\n", $parts);
    }

    /** @return array<int,string> */
    private function replayErrors(string $surface): array
    {
        $errors = [];
        foreach (['ReplayVerificationService', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $positive) {
            if (strpos($surface, $positive) === false) {
                $errors[] = 'replay fact/reproducibility surface missing '.$positive;
            }
        }
        if (preg_match('/\b(pnl|profitability|strategy_performance|backtest_return|trade_return|buy_signal|sell_signal|candidate_rank)\b/i', $surface, $match)) {
            $errors[] = 'replay emits downstream outcome '.$match[1];
        }

        return $errors;
    }

    public function test_market_data_replay_proves_reproducibility_and_emits_no_strategy_outcome(): void
    {
        $this->assertSame([], $this->replayErrors($this->executableReplaySurface()));
    }

    public function test_every_completion_guard_fails_closed_under_a_verified_semantic_mutation(): void
    {
        $summary = $this->read('docs/market_data/development/implementation/guides/system/SYSTEM_CONTEXT_AND_DEPENDENCIES.md');
        $scope = $this->read('app/Domain/MarketData/MarketDataScope.php');
        $readiness = $this->read('app/Application/MarketData/Services/MarketDataReadinessService.php');
        $port = $this->read('app/Application/MarketData/Ports/ApiEodBarsSource.php');
        $adapter = $this->read('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $build = $this->read('docs/market_data/development/implementation/MD_IMPLEMENTATION_BUILD_SEQUENCE.md');
        $register = $this->read('docs/market_data/development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md');
        $current = $this->read('docs/market_data/development/implementation/CURRENT_STATE.md');
        $roles = $this->read('docs/market_data/authority/governance/DOCUMENT_ROLE_REGISTRY.csv');
        $terminology = $this->read('docs/market_data/authority/strategy/book/Terminology_and_Scope.md');
        $systemMap = $this->read('docs/market_data/development/implementation/guides/system/SYSTEM_DATA_PRODUCT_MAP.md');
        $eligibility = $this->read('app/Application/MarketData/Services/EligibilityDecisionService.php');
        $config = $this->read('config/market_data.php');
        $resilience = $this->read('docs/market_data/authority/strategy/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md');

        $mutated = str_replace('5 IDX trading days', '5 calendar days', $summary, $count);
        $this->assertGreaterThan(0, $count, 'scope mutation must land');
        $this->assertNotSame([], $this->scopeErrors($mutated, $scope, $readiness));

        $mutated = $port."\nrange=10d\n";
        $this->assertStringContainsString('range=10d', $mutated, 'provider mutation must land');
        $this->assertNotSame([], $this->providerErrors($summary, $mutated, $adapter));

        $mutated = $register."\n**Single exact next executable resume point:** open `MD-B22-A999`.\n";
        $this->assertSame(2, substr_count($mutated, '**Single exact next executable resume point:**'), 'resume mutation must land');
        $this->assertNotSame([], $this->orchestrationErrors($build, $mutated, $current, $roles));

        $mutated = str_replace('ELIG_MISSING_BAR', 'ELIG_SILENT_BLOCK', $eligibility, $count);
        $this->assertGreaterThan(0, $count, 'reason mutation must land');
        $this->assertNotSame([], $this->decisionGradeErrors($terminology, $systemMap, $mutated));

        $mutated = str_replace("'roc_lookback_days' => (int) env('MARKET_DATA_ROC_LOOKBACK_DAYS', 20)", "'roc_lookback_days' => null", $config, $count);
        $this->assertGreaterThan(0, $count, 'horizon mutation must land');
        $this->assertNotSame([], $this->horizonErrors($summary, $mutated, $readiness));

        $mutated = preg_replace('/^6\. mulai menghitung consecutive operational SLO.*$/m', '', $resilience, 1, $count);
        $this->assertSame(1, $count, 'operational-owner mutation must land');
        $this->assertNotSame([], $this->operationalOwnerErrors($terminology, $mutated));

        $mutated = $this->executableReplaySurface()."\n<?php \$pnl = 1;\n";
        $this->assertStringContainsString('$pnl', $mutated, 'replay mutation must land');
        $this->assertNotSame([], $this->replayErrors($mutated));
    }
}
