<?php

namespace Tests\Unit\MarketData;

use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;

class LocalFileEodBarsAdapterTest extends \TestCase
{
    protected function tearDown(): void
    {
        @unlink(base_path('storage/framework/testing/manual-source-explicit.csv'));
        @unlink(base_path('storage/framework/testing/manual-source-explicit.txt'));
        config()->set('market_data.source.local_input_file', null);

        parent::tearDown();
    }

    public function test_fetch_or_load_eod_bars_prefers_explicit_manual_input_file_override(): void
    {
        $path = base_path('storage/framework/testing/manual-source-explicit.csv');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, implode("\n", [
            'ticker_code,trade_date,open,high,low,close,volume,adj_close,source_name,source_row_ref,captured_at',
            'BBCA,2026-03-24,9000,9100,8900,9050,1000000,9050,MANUAL_RECOVERY,row-1,2026-03-24 17:00:00',
        ]));

        config()->set('market_data.source.local_input_file', 'storage/framework/testing/manual-source-explicit.csv');

        $rows = (new LocalFileEodBarsAdapter())->fetchOrLoadEodBars('2026-03-24', 'manual_file');

        $this->assertCount(1, $rows);
        $this->assertSame('BBCA', $rows[0]['ticker_code']);
        $this->assertSame('2026-03-24', $rows[0]['trade_date']);
        $this->assertSame('LOCAL_FILE', $rows[0]['source_name']);
        $this->assertSame('row-1', $rows[0]['source_row_ref']);
    }

    public function test_fetch_or_load_eod_bars_rejects_explicit_input_file_with_unsupported_extension(): void
    {
        $path = base_path('storage/framework/testing/manual-source-explicit.txt');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, 'not-supported');
        config()->set('market_data.source.local_input_file', 'storage/framework/testing/manual-source-explicit.txt');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Explicit local input file must use .json or .csv extension.');

        (new LocalFileEodBarsAdapter())->fetchOrLoadEodBars('2026-03-24', 'manual_file');
    }
}
