<?php

use PHPUnit\Framework\TestCase;

/**
 * Stage 1 exit gate — W01.
 *
 * "constants/config/API vocabulary/schema dictionary/test names tidak menentang terminology
 *  owner dan tidak membuat pre-2023/freshness/watchlist-performance claim yang salah."
 *
 * Owner contract: docs/market_data/book/Terminology_and_Scope.md
 *
 * This executes the vocabulary rules rather than asserting that a document contains a string:
 * it resolves the real config, reads the real schema dictionary, and enumerates real test names.
 */
class TerminologyOwnerVocabularyTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function config(): array
    {
        if (! function_exists('env')) {
            eval('function env($key, $default = null) { return $default; }');
        }

        return require $this->root().'/config/market_data.php';
    }

    /**
     * `RAW`, `STRUCTURAL_ADJUSTED`, and `TOTAL_RETURN` are price products owned by the
     * terminology contract. Config must name them exactly, because a consumer that reads a
     * product code cannot recover from a renamed one.
     */
    public function test_config_price_product_vocabulary_matches_the_terminology_owner(): void
    {
        $scope = $this->config()['scope'];

        $this->assertSame('RAW', $scope['raw_product_code']);
        $this->assertSame('STRUCTURAL_ADJUSTED', $scope['structural_adjusted_product_code']);
        $this->assertSame('TOTAL_RETURN', $scope['total_return_product_code']);
    }

    /**
     * The canonical scope is IDX Regular-Market EOD. Widening it in config is the quiet way a
     * platform stops being what its terminology says it is.
     */
    public function test_config_canonical_scope_cannot_drift_from_idx_regular_market_eod(): void
    {
        $scope = $this->config()['scope'];

        $this->assertSame('IDX', $scope['market_code']);
        $this->assertSame('REGULAR', $scope['market_segment']);
        $this->assertSame('EOD', $scope['frequency']);
        $this->assertSame('Asia/Jakarta', $scope['timezone']);
    }

    /**
     * `2023-01-02` is the intentional dataset start. A different default would make every
     * pre-boundary absence read as a defect instead of a scope decision.
     */
    public function test_config_dataset_start_is_the_intentional_boundary(): void
    {
        $this->assertSame('2023-01-02', $this->config()['scope']['dataset_start']);
    }

    /**
     * Data usability is the canonical field; `eligible` survives only as a compatibility alias.
     * The alias may be preserved, never propagated.
     */
    public function test_config_keeps_data_usable_canonical_and_eligible_only_as_compatibility(): void
    {
        $scope = $this->config()['scope'];

        $this->assertSame('data_usable', $scope['data_usability_field']);
        $this->assertSame('eligible', $scope['compatibility_eligibility_field']);
    }

    /**
     * Watchlist policy vocabulary must not exist as market-data configuration. The forbidden
     * list targets meanings, so overloaded upstream words are excluded deliberately.
     */
    public function test_no_config_key_carries_watchlist_policy_vocabulary(): void
    {
        $forbidden = '/(^|_)(rank|ranking|score|alpha|conviction|entry|exit|stoploss|stop_loss|takeprofit|take_profit|position_size|sizing|screening|tradability)($|_)/i';

        $violations = [];
        $walk = function (array $node, string $prefix) use (&$walk, &$violations, $forbidden) {
            foreach ($node as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                if (preg_match($forbidden, (string) $key)) {
                    $violations[] = $path;
                }
                if (is_array($value)) {
                    $walk($value, $path);
                }
            }
        };
        $walk($this->config(), '');

        $this->assertSame([], $violations, 'market-data config must not carry watchlist policy vocabulary');
    }

    /**
     * The schema dictionary is read by implementers before code. A dictionary that renames a
     * product or reintroduces `adj_close` as a price basis contradicts the owner in the one
     * place people trust for field meaning.
     */
    public function test_schema_dictionary_does_not_contradict_price_product_terminology(): void
    {
        $dictionary = file_get_contents($this->root().'/docs/market_data/db/MARKET_DATA_DICTIONARY.md');

        $this->assertNotFalse($dictionary);
        $this->assertStringContainsString('STRUCTURAL_ADJUSTED', $dictionary);
        $this->assertDoesNotMatchRegularExpression(
            '/adj_close\s+(is|as)\s+(the\s+)?(analytical|canonical|adjusted)\s+(price\s+)?(basis|product)/i',
            $dictionary,
            'provider adj_close must never be described as an analytical or canonical price basis'
        );
    }

    /**
     * Test names are read as a description of what the platform guarantees. A name asserting
     * production readiness, freshness, or strategy performance makes a claim the suite cannot
     * support, and order 22 owns those claims.
     */
    public function test_market_data_test_names_make_no_readiness_or_performance_claim(): void
    {
        $forbidden = '/(production_ready|fully_ready|operationally_validated|is_fresh|guarantees_freshness|profitab|outperform|alpha_proven|strategy_works)/i';

        $violations = [];
        foreach (glob($this->root().'/tests/Unit/MarketData/*.php') as $file) {
            if (preg_match($forbidden, basename($file))) {
                $violations[] = basename($file);
            }
            foreach (preg_split('/\R/', (string) file_get_contents($file)) as $line) {
                if (preg_match('/function\s+(test_[A-Za-z0-9_]+)/', $line, $m) && preg_match($forbidden, $m[1])) {
                    $violations[] = basename($file).'::'.$m[1];
                }
            }
        }

        $this->assertSame([], $violations, 'test names must not claim readiness, freshness, or strategy performance');
    }
}
