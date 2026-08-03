<?php

use App\Application\MarketData\Services\MarketDataReadinessService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

/**
 * The readiness surface must diagnose a blocked trading day the same way the platform does.
 *
 * MarketDataReadinessService is the question a consumer asks before using a trading day: is this
 * date safe to read? When the answer is no, the reason it gives is the only thing the consumer
 * has to act on.
 *
 * That reason was derived by a private copy of the pointer-integrity diagnosis that already
 * exists in EodPublicationRepository. Copies of this particular judgement have a history in this
 * codebase — the correction baseline duplicated the read query, the repair command duplicated the
 * reason derivation and lost five reasons, and the coverage null-checks are written out four
 * times. Each copy stays correct until it doesn't.
 *
 * These tests hold the readiness diagnosis against the canonical one over every state, so the two
 * cannot drift apart quietly.
 */
class ReadinessDiagnosisAgreementTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    private const TRADE_DATE = '2026-05-19';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function readiness(): array
    {
        return (new MarketDataReadinessService())->readinessForTradeDate(self::TRADE_DATE);
    }

    private function canonicalReasons(): array
    {
        $repository = new EodPublicationRepository();

        return $repository->determineCurrentIntegrityViolationReasons(
            $repository->findRawCurrentPublicationStateForTradeDate(self::TRADE_DATE)
        );
    }

    public function test_a_healthy_day_is_ready_and_has_no_integrity_reasons(): void
    {
        $this->seedReadablePublication(self::TRADE_DATE, 3, 2);

        $this->assertTrue($this->readiness()['is_ready']);
        $this->assertSame([], $this->canonicalReasons());
    }

    /**
     * The consumer-facing reason must be one the platform actually recognises, and it must be the
     * platform's own first finding rather than a separately reasoned guess.
     *
     * @dataProvider blockedStates
     */
    public function test_the_readiness_reason_matches_the_canonical_diagnosis(array $runOverride, string $why): void
    {
        $this->seedReadablePublication(self::TRADE_DATE, 3, 2, 1, $runOverride);

        $readiness = $this->readiness();
        $canonical = $this->canonicalReasons();

        $this->assertFalse($readiness['is_ready'], 'must not be ready: '.$why);
        $this->assertNotEmpty($canonical, 'the platform must diagnose: '.$why);

        $this->assertContains(
            $readiness['reason_code'],
            $canonical,
            'readiness reported "'.$readiness['reason_code'].'" but the platform found: '
            .implode(', ', $canonical).' — for: '.$why
        );
    }

    public function blockedStates(): array
    {
        return [
            'publication not sealed' => [['seal_state' => 'UNSEALED'], 'the publication is mutable'],
            'run did not succeed' => [['terminal_status' => 'HELD'], 'the run behind the publication did not succeed'],
            'run not readable' => [['publishability_state' => 'NOT_READABLE'], 'the run is not readable'],
            'coverage gate failed' => [['coverage_gate_state' => 'FAIL'], 'the coverage gate did not pass'],
            'run seal timestamp missing' => [['sealed_at' => null], 'the run carries no proof of when it was sealed'],
            'run coverage telemetry incomplete' => [['coverage_ratio' => null], 'the run cannot prove the coverage it claims'],
            'run current mirror not set' => [['is_current_publication' => 0], 'the run does not mirror the pointer naming it'],
        ];
    }

    /**
     * A blocked day must never be reported with the generic "nothing was published" code when the
     * platform can say something more specific. The two are different situations for a consumer:
     * a date that was never published is expected on a holiday, while a date whose publication
     * exists but cannot prove its coverage is a fault someone has to look at.
     *
     * @dataProvider blockedStates
     */
    public function test_a_specific_fault_is_not_reported_as_nothing_published(array $runOverride, string $why): void
    {
        $this->seedReadablePublication(self::TRADE_DATE, 3, 2, 1, $runOverride);

        $this->assertNotSame(
            'NO_READABLE_PUBLICATION',
            $this->readiness()['reason_code'],
            'a publication exists for this date, so the fault is not "nothing published" — '.$why
        );
    }

    /**
     * The generic code stays correct for the case it actually describes.
     */
    public function test_a_date_that_was_never_published_reports_nothing_published(): void
    {
        $readiness = $this->readiness();

        $this->assertFalse($readiness['is_ready']);
        $this->assertSame('NO_READABLE_PUBLICATION', $readiness['reason_code']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $readiness['pointer_resolve_status']);
    }
}
