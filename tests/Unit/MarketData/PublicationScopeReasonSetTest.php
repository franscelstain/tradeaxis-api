<?php

use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;

/**
 * The manifest reason set as D-MD-B10-A002-003 decision 2 defines it: a set of registry-shaped
 * codes whose order is not semantic and in which each code appears once.
 */
class PublicationScopeReasonSetTest extends TestCase
{
    private function service(): PublicationSemanticIdentityService
    {
        return new PublicationSemanticIdentityService(new DeterministicHashService());
    }

    public function test_members_are_deduplicated_and_canonically_sorted(): void
    {
        $this->assertSame(
            ['COVERAGE_THRESHOLD_MET', 'RUN_COVERAGE_LOW'],
            $this->service()->canonicalReasonSet(['RUN_COVERAGE_LOW', 'COVERAGE_THRESHOLD_MET', ' coverage_threshold_met ', 'RUN_COVERAGE_LOW'])
        );
        $this->assertSame([], $this->service()->canonicalReasonSet([]));
    }

    public function test_order_and_repetition_do_not_change_the_publication_identity_but_membership_does(): void
    {
        $service = $this->service();
        $hash = function (array $reasons) use ($service): string {
            return $service->publicationHash(['trade_date' => '2026-09-29', 'semantic_reasons' => $service->canonicalReasonSet($reasons)]);
        };

        $baseline = $hash(['COVERAGE_THRESHOLD_MET', 'RUN_COVERAGE_LOW']);
        $this->assertSame($baseline, $hash(['RUN_COVERAGE_LOW', 'COVERAGE_THRESHOLD_MET']));
        $this->assertSame($baseline, $hash(['RUN_COVERAGE_LOW', 'COVERAGE_THRESHOLD_MET', 'RUN_COVERAGE_LOW']));
        $this->assertNotSame($baseline, $hash(['COVERAGE_THRESHOLD_MET']));
        $this->assertNotSame($hash(['COVERAGE_THRESHOLD_MET']), $hash([]));
    }

    /**
     * @dataProvider invalidCodes
     */
    public function test_a_member_that_is_not_a_registry_shaped_code_is_refused(string $code): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SEMANTIC_REASON_CODE_INVALID');
        $this->service()->canonicalReasonSet(['COVERAGE_THRESHOLD_MET', $code]);
    }

    public function invalidCodes(): array
    {
        return [
            'empty' => [''],
            'free text' => ['coverage met'],
            'delimiter' => ['RUN|LOW'],
            'leading digit' => ['1_REASON'],
        ];
    }
}
