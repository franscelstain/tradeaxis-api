<?php

namespace Tests\Unit\MarketData;

use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;

class LocalFileEodBarsAdapterTest extends \TestCase
{
    protected function tearDown(): void
    {
        @unlink(base_path('storage/framework/testing/manual-source-explicit.csv'));
        @unlink(base_path('storage/framework/testing/manual-source-explicit.txt'));
        app(\App\Application\MarketData\Services\ManualSourceInputContext::class)->set(null);

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

        app(\App\Application\MarketData\Services\ManualSourceInputContext::class)->set('storage/framework/testing/manual-source-explicit.csv');

        $rows = (new LocalFileEodBarsAdapter())->fetchOrLoadEodBars('2026-03-24', 'manual_file');

        $this->assertCount(1, $rows);
        $this->assertSame('BBCA', $rows[0]['ticker_code']);
        $this->assertSame('2026-03-24', $rows[0]['trade_date']);
        $this->assertSame('LOCAL_FILE', $rows[0]['source_name']);
        $this->assertSame('row-1', $rows[0]['source_row_ref']);
    }

    public function test_explicit_manual_input_file_can_filter_multi_date_csv_by_requested_date(): void
    {
        $path = base_path('storage/framework/testing/manual-source-explicit.csv');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, implode("\n", [
            'ticker_code,trade_date,open,high,low,close,volume,adj_close,source_row_ref,captured_at',
            'BBCA,2026-03-24,9000,9100,8900,9050,1000000,9050,row-1,2026-03-24 17:00:00',
            'BBRI,2026-03-25,4100,4150,4050,4120,2000000,4120,row-2,2026-03-25 17:00:00',
        ]));

        app(\App\Application\MarketData\Services\ManualSourceInputContext::class)->set('storage/framework/testing/manual-source-explicit.csv');

        $adapter = new LocalFileEodBarsAdapter();
        $rows = $adapter->fetchOrLoadEodBars('2026-03-25', 'manual_file');
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();

        $this->assertCount(1, $rows);
        $this->assertSame('BBRI', $rows[0]['ticker_code']);
        $this->assertSame('2026-03-25', $rows[0]['trade_date']);
        $this->assertSame('row-2', $rows[0]['source_row_ref']);
        $this->assertSame(1, $telemetry['source_file_row_count']);
        $this->assertSame(2, $telemetry['source_file_total_row_count']);
        $this->assertSame(1, $telemetry['source_file_filtered_out_row_count']);
        $this->assertSame('2026-03-25', $telemetry['requested_trade_date']);
    }

    public function test_fetch_or_load_eod_bars_rejects_explicit_input_file_with_unsupported_extension(): void
    {
        $path = base_path('storage/framework/testing/manual-source-explicit.txt');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, 'not-supported');
        app(\App\Application\MarketData\Services\ManualSourceInputContext::class)->set('storage/framework/testing/manual-source-explicit.txt');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Explicit local input file must use .json or .csv extension.');

        (new LocalFileEodBarsAdapter())->fetchOrLoadEodBars('2026-03-24', 'manual_file');
    }

    public function test_empty_manual_csv_is_blocked_with_reason_code(): void
    {
        $path = base_path('storage/framework/testing/manual-source-explicit.csv');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, 'ticker_code,trade_date,open,high,low,close,volume,adj_close,source_name,source_row_ref,captured_at'."
");
        app(\App\Application\MarketData\Services\ManualSourceInputContext::class)->set('storage/framework/testing/manual-source-explicit.csv');

        try {
            (new LocalFileEodBarsAdapter())->fetchOrLoadEodBars('2026-03-24', 'manual_file');
            $this->fail('Expected empty manual CSV to be blocked.');
        } catch (\App\Infrastructure\MarketData\Source\SourceAcquisitionException $e) {
            $context = $e->context();

            $this->assertSame('RUN_SOURCE_MANUAL_FILE_EMPTY', $e->reasonCode());
            $this->assertSame('FAILED', $context['source_final_status']);
            $this->assertSame(0, $context['accepted_row_count']);
            $this->assertTrue($context['manual_file_empty_blocked']);
        }
    }

}
