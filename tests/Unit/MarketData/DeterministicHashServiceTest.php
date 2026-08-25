<?php

use App\Application\MarketData\Services\DeterministicHashService;

class DeterministicHashServiceTest extends TestCase
{
    public function test_same_rows_with_numeric_shape_variation_produce_same_hash()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'close', 'volume', 'publication_id'];

        $rowsA = [
            ['trade_date' => '2026-03-10', 'ticker_id' => 101, 'close' => '100.00', 'volume' => 1000, 'publication_id' => 55],
        ];
        $rowsB = [
            (object) ['trade_date' => '2026-03-10', 'ticker_id' => '101', 'close' => 100, 'volume' => '1000.0000', 'publication_id' => '55'],
        ];

        $this->assertSame($service->hashRows($rowsA, $columns), $service->hashRows($rowsB, $columns));
    }

    public function test_different_publication_context_produces_different_hash()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'close', 'publication_id'];

        $rowsA = [['trade_date' => '2026-03-10', 'ticker_id' => 101, 'close' => 100, 'publication_id' => 1]];
        $rowsB = [['trade_date' => '2026-03-10', 'ticker_id' => 101, 'close' => 100, 'publication_id' => 2]];

        $this->assertNotSame($service->hashRows($rowsA, $columns), $service->hashRows($rowsB, $columns));
    }

    public function test_input_order_does_not_change_hash()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'close'];

        $rowsA = [
            ['trade_date' => '2026-03-10', 'ticker_id' => 101, 'close' => 100],
            ['trade_date' => '2026-03-10', 'ticker_id' => 102, 'close' => 200],
        ];
        $rowsB = array_reverse($rowsA);

        $this->assertSame($service->hashRows($rowsA, $columns), $service->hashRows($rowsB, $columns));
    }

    public function test_null_uses_the_contractually_locked_empty_token()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'note'];

        $nullHash = $service->hashRows([['trade_date' => '2026-03-10', 'ticker_id' => 101, 'note' => null]], $columns);
        $emptyHash = $service->hashRows([['trade_date' => '2026-03-10', 'ticker_id' => 101, 'note' => '']], $columns);

        $this->assertSame($nullHash, $emptyHash);
    }

    public function test_non_empty_config_cannot_override_the_canonical_null_token()
    {
        config()->set('market_data.hash.null_token', '[empty]');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HASH_NULL_TOKEN_NOT_LOCKED_EMPTY');

        (new DeterministicHashService())->serializeRows([
            ['trade_date' => '2026-03-10', 'ticker_id' => 101, 'note' => null],
        ], ['trade_date', 'ticker_id', 'note']);
    }

    public function test_changed_value_changes_hash()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'ticker_id', 'close'];

        $rowsA = [['trade_date' => '2026-03-10', 'ticker_id' => 101, 'close' => 100]];
        $rowsB = [['trade_date' => '2026-03-10', 'ticker_id' => 101, 'close' => 101]];

        $this->assertNotSame($service->hashRows($rowsA, $columns), $service->hashRows($rowsB, $columns));
    }

    public function test_locked_number_formats_keep_trailing_zeroes_and_never_use_scientific_notation()
    {
        $service = new DeterministicHashService();

        $serialized = $service->serializeRows([[
            'trade_date' => '2026-03-10',
            'listing_id' => 101,
            'close' => 123.4,
            'traded_value_idr_actual' => 7,
            'atr14' => 0.0000000001,
            'price_factor' => 1,
            'volume' => 1000,
        ]], ['trade_date', 'listing_id', 'close', 'traded_value_idr_actual', 'atr14', 'price_factor', 'volume']);

        $this->assertSame(
            '2026-03-10|101|123.4000|7.00|0.0000000001|1.000000000000|1000',
            $serialized
        );
        $this->assertStringNotContainsString('E', strtoupper($serialized));
    }

    public function test_json_objects_and_sets_are_canonicalized_before_hashing()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'listing_id', 'eligibility_reasons_json'];
        $left = [['trade_date' => '2026-03-10', 'listing_id' => 101, 'eligibility_reasons_json' => '{"b":["Z","A"],"coverage_ratio":1}']];
        $right = [['trade_date' => '2026-03-10', 'listing_id' => 101, 'eligibility_reasons_json' => '{"coverage_ratio":1,"b":["A","Z"]}']];

        $this->assertSame($service->hashRows($left, $columns), $service->hashRows($right, $columns));
        $this->assertSame('2026-03-10|101|{"b":["A","Z"],"coverage_ratio":"1.0000"}', $service->serializeRows($left, $columns));
    }

    public function test_a_legacy_single_reason_scalar_is_canonicalized_as_a_one_member_set()
    {
        $service = new DeterministicHashService();

        $this->assertSame(
            '2026-03-10|101|["ELIG_INSUFFICIENT_HISTORY"]',
            $service->serializeRows([[
                'trade_date' => '2026-03-10',
                'listing_id' => 101,
                'eligibility_reasons_json' => 'ELIG_INSUFFICIENT_HISTORY',
            ]], ['trade_date', 'listing_id', 'eligibility_reasons_json'])
        );
    }


    public function test_namespaced_event_risk_reasons_are_canonicalized_as_a_set()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'listing_id', 'event_risk_reasons'];

        $left = [[
            'trade_date' => '2026-03-10',
            'listing_id' => 101,
            'event_risk_reasons' => 'TRADING_STATUS:SPECIAL_MONITORING_START,UMA',
        ]];
        $right = [[
            'trade_date' => '2026-03-10',
            'listing_id' => 101,
            'event_risk_reasons' => 'UMA,TRADING_STATUS:SPECIAL_MONITORING_START',
        ]];

        $this->assertSame($service->hashRows($left, $columns), $service->hashRows($right, $columns));
        $this->assertSame(
            '2026-03-10|101|["TRADING_STATUS:SPECIAL_MONITORING_START","UMA"]',
            $service->serializeRows($left, $columns)
        );
    }

    public function test_dated_corporate_action_reason_tokens_are_canonicalized_as_a_set()
    {
        $service = new DeterministicHashService();
        $columns = ['trade_date', 'listing_id', 'corporate_action_window_reasons'];

        $left = [[
            'trade_date' => '2026-07-22',
            'listing_id' => 101,
            'corporate_action_window_reasons' => 'RIGHTS_ISSUE@2026-07-22,STOCK_SPLIT@2026-07-15',
        ]];
        $right = [[
            'trade_date' => '2026-07-22',
            'listing_id' => 101,
            'corporate_action_window_reasons' => 'STOCK_SPLIT@2026-07-15,RIGHTS_ISSUE@2026-07-22',
        ]];

        $this->assertSame($service->hashRows($left, $columns), $service->hashRows($right, $columns));
        $this->assertSame(
            '2026-07-22|101|["RIGHTS_ISSUE@2026-07-22","STOCK_SPLIT@2026-07-15"]',
            $service->serializeRows($left, $columns)
        );
    }

    public function test_canonical_document_allows_exact_integer_leaves_without_weakening_row_numeric_ownership()
    {
        $service = new DeterministicHashService();

        $this->assertSame(
            $service->hashCanonicalDocument(['publication_version' => 2, 'counts' => ['bars' => 10]]),
            $service->hashCanonicalDocument(['counts' => ['bars' => 10], 'publication_version' => 2])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HASH_NUMBER_FORMAT_UNOWNED_FIELD');
        $service->hashRows([['unknown_metric' => 2]], ['unknown_metric']);
    }

    public function test_locked_hash_controls_fail_closed_instead_of_silently_falling_back()
    {
        config()->set('market_data.hash.algorithm', 'md5');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HASH_ALGORITHM_NOT_LOCKED_SHA256');

        (new DeterministicHashService())->hashRows([
            ['trade_date' => '2026-03-10', 'listing_id' => 101],
        ], ['trade_date', 'listing_id']);
    }

    public function test_unowned_numeric_fields_fail_closed_instead_of_guessing_a_scale()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HASH_NUMBER_FORMAT_UNOWNED_FIELD');

        (new DeterministicHashService())->hashRows([
            ['trade_date' => '2026-03-10', 'listing_id' => 101, 'unknown_metric' => 1.2],
        ], ['trade_date', 'listing_id', 'unknown_metric']);
    }

    public function test_hash_output_is_lowercase_sha256_and_text_must_be_utf8()
    {
        $service = new DeterministicHashService();
        $hash = $service->hashRows([
            ['trade_date' => '2026-03-10', 'listing_id' => 101, 'note' => 'valid'],
        ], ['trade_date', 'listing_id', 'note']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HASH_TEXT_NOT_UTF8');
        $service->serializeRows([
            ['trade_date' => '2026-03-10', 'listing_id' => 101, 'note' => "\xC3\x28"],
        ], ['trade_date', 'listing_id', 'note']);
    }

    public function test_decimal_normalization_uses_decimal_text_and_locked_rounding_without_binary_float_drift()
    {
        $service = new DeterministicHashService();

        $this->assertSame('1.2346', $service->normalizeValue('1.23455', 'close'));
        $this->assertSame('100000000000000000000.0000', $service->normalizeValue('1e20', 'close'));
        $this->assertSame('0.0000000001', $service->normalizeValue('1e-10', 'atr14'));
        $this->assertSame('0.0000', $service->normalizeValue('-0.00001', 'close'));
    }

    public function test_dates_timestamps_and_embedded_content_hashes_fail_closed_on_noncanonical_values()
    {
        $service = new DeterministicHashService();
        foreach ([
            ['2026-02-30', 'trade_date', 'HASH_DATE_INVALID'],
            ['2026-03-10T17:00:00Z', 'recorded_at', 'HASH_TIMESTAMP_INVALID'],
            [str_repeat('A', 64), 'factor_set_hash', 'HASH_CONTENT_HASH_INVALID'],
        ] as $case) {
            try {
                $service->normalizeValue($case[0], $case[1]);
                $this->fail($case[1].' mutation should fail closed');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString($case[2], $exception->getMessage());
            }
        }
    }

}
