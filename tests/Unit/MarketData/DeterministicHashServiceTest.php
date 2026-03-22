<?php

use App\Application\MarketData\Services\DeterministicHashService;
use PHPUnit\Framework\TestCase;

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
}
