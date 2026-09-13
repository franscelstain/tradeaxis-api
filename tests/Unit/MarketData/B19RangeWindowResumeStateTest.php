<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B19` — the two resume-only-failed outcome states nothing else executed.
 *
 * `Source_Data_Acquisition_Contract_LOCKED.md` (`MD-S053`) names five outcome states for a
 * resume-only-failed acquisition. `ApiBackfillRangeAcquisitionServiceTest` already executes
 * `RETRY_SUCCESS`, `PARTIAL_RETRY_SUCCESS` and `FAILED_RETRY_BLOCKED`. The remaining two were
 * enforced by code no test reached:
 *
 *   > - `NO_FAILED_CHECKPOINT`
 *   > - `SYSTEMIC_FAILED` only for true global/provider/config acquisition failure
 *
 * The second is the one worth guarding. "Only for true global/provider/config failure" is a
 * restriction, and a restriction is invisible to a test that never supplies the case it excludes:
 * an implementation that escalated every repeated ticker failure to `SYSTEMIC_FAILED` would satisfy
 * a guard that only ever checked the systemic path. So both sides are asserted — a per-ticker
 * transport failure must stay `FAILED_RETRY_BLOCKED`, and an authentication failure must escalate.
 */
class B19RangeWindowResumeStateTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    /**
     * `MD-S053`: `NO_FAILED_CHECKPOINT`.
     *
     * A resume that finds nothing to retry reports that it found nothing. It does not report a
     * success it did not perform, and it does not report a failure that did not occur.
     */
    public function test_a_resume_with_nothing_to_retry_reports_no_failed_checkpoint(): void
    {
        $this->bindMarketDataConfig($this->config());

        $adapter = $this->adapter(function () {
            $this->fail('resume --only-failed must not fetch when no checkpoint failed');
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => [
                '2026-05-01|2026-05-07|BBCA' => ['state' => 'SUCCESS'],
            ],
        ]);

        $this->assertSame('NO_FAILED_CHECKPOINT', $result['source_acquisition_state']);
        $this->assertSame(0, $result['failed_checkpoint_total']);
        $this->assertSame(0, $result['failed_checkpoint_retried']);
        $this->assertSame(0, $result['retry_success_count']);
        $this->assertSame(0, $result['retry_failed_count']);
    }

    /**
     * `MD-S053`: `SYSTEMIC_FAILED` only for true global/provider/config acquisition failure.
     *
     * The fail-closed half of this family. A transport failure against one ticker is a ticker
     * failure however many times it repeats; calling it systemic would tell an operator the
     * provider or configuration is broken when it is not, and would mask the actual scope.
     */
    public function test_repeated_ticker_failures_are_not_escalated_to_systemic(): void
    {
        $this->bindMarketDataConfig($this->config());

        $adapter = $this->adapter(function () {
            return ['status' => 504, 'body' => ''];
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BBRI'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => [
                '2026-05-01|2026-05-07|BBCA' => ['state' => 'FAILED'],
                '2026-05-01|2026-05-07|BBRI' => ['state' => 'FAILED'],
            ],
        ]);

        $this->assertNotSame('SYSTEMIC_FAILED', $result['source_acquisition_state'],
            'repeated per-ticker transport failures were escalated to a systemic verdict');
        $this->assertSame('FAILED_RETRY_BLOCKED', $result['source_acquisition_state']);
        $this->assertSame(2, $result['failed_checkpoint_total']);
        $this->assertSame(2, $result['retry_failed_count']);
    }

    /**
     * The other side: an authentication failure is a config/provider fault and must escalate.
     *
     * Without this, the guard above would be satisfied by an implementation that never reports
     * `SYSTEMIC_FAILED` at all, which loses the distinction the contract draws.
     */
    public function test_an_authentication_failure_does_escalate_to_systemic(): void
    {
        $this->bindMarketDataConfig($this->config());

        $adapter = $this->adapter(function () {
            return ['status' => 401, 'body' => ''];
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => [
                '2026-05-01|2026-05-07|BBCA' => ['state' => 'FAILED'],
            ],
        ]);

        $this->assertSame('SYSTEMIC_FAILED', $result['source_acquisition_state'],
            'an authentication failure is a provider/config fault and must be reported as systemic');
    }

    private function adapter(callable $fetcher): PublicApiEodBarsAdapter
    {
        $identities = $this->getMockBuilder(TemporalIdentityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveProviderContext'])
            ->getMock();
        $identities->method('resolveProviderContext')
            ->willReturnCallback(function ($tickerCode, $provider, $tradeDate) {
                $code = strtoupper(trim((string) $tickerCode));
                $stableId = (int) sprintf('%u', crc32($code));

                return [
                    'listing_id' => $stableId,
                    'ticker_id' => $stableId,
                    'ticker_code' => $code,
                    'provider' => $provider,
                    'provider_symbol' => $code.'.JK',
                    'provider_mapping_id' => $stableId,
                    'mapping_revision' => 'TEST-'.$code.'-'.$tradeDate,
                    'listing_symbol_id' => $stableId,
                    'identity_recorded_at' => $tradeDate.' 00:00:00',
                ];
            });

        return new PublicApiEodBarsAdapter(
            $fetcher,
            new EquityProviderSymbolResolver($identities)
        );
    }

    /** @return array<string,mixed> */
    private function config(): array
    {
        return [
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'provider' => [
                    'api_retry_max' => 0,
                    'api_backoff_ms' => 0,
                    'api_throttle_qps' => 1000,
                ],
                'source' => [
                    'default_source_name' => 'YAHOO_FINANCE',
                    'api_backfill' => [
                        'window_days' => 90,
                        'warmup_days' => 120,
                    ],
                ],
            ],
        ];
    }
}
