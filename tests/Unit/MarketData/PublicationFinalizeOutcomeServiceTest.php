<?php

use App\Application\MarketData\Services\PublicationFinalizeOutcomeService;
use PHPUnit\Framework\TestCase;

class PublicationFinalizeOutcomeServiceTest extends TestCase
{
    public function test_finalize_success_when_current_pointer_resolves_to_candidate_publication()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('PASS', 'SUCCESS', 'READABLE', null, true), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 77,
            'candidate_publication_version' => 3,
            'resolved_current_publication_id' => 77,
            'resolved_current_publication_version' => 3,
        ]);

        $this->assertSame('SUCCESS', $state['terminal_status']);
        $this->assertSame('READABLE', $state['publishability_state']);
        $this->assertSame('2026-04-21', $state['trade_date_effective']);
        $this->assertSame(77, $state['current_publication_id']);
        $this->assertNull($state['reason_code']);
    }

    public function test_outcome_keeps_held_not_readable_when_coverage_fail_has_fallback()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('FAIL', 'HELD', 'NOT_READABLE', 'RUN_COVERAGE_LOW', false), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
        ]);

        $this->assertSame('HELD', $state['terminal_status']);
        $this->assertSame('NOT_READABLE', $state['publishability_state']);
        $this->assertSame('2026-04-20', $state['trade_date_effective']);
        $this->assertSame('RUN_COVERAGE_LOW', $state['reason_code']);
    }

    public function test_outcome_keeps_failed_not_readable_when_coverage_fail_has_no_fallback()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('FAIL', 'FAILED', 'NOT_READABLE', 'RUN_COVERAGE_LOW', false, null), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => null,
        ]);

        $this->assertSame('FAILED', $state['terminal_status']);
        $this->assertSame('NOT_READABLE', $state['publishability_state']);
        $this->assertNull($state['trade_date_effective']);
        $this->assertSame('RUN_COVERAGE_LOW', $state['reason_code']);
    }

    public function test_outcome_keeps_blocked_non_readable_and_never_promotes()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('NOT_EVALUABLE', 'HELD', 'NOT_READABLE', 'RUN_COVERAGE_NOT_EVALUABLE', false), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 77,
            'candidate_publication_version' => 3,
            'resolved_current_publication_id' => 77,
            'resolved_current_publication_version' => 3,
        ]);

        $this->assertSame('HELD', $state['terminal_status']);
        $this->assertSame('NOT_READABLE', $state['publishability_state']);
        $this->assertSame('2026-04-20', $state['trade_date_effective']);
        $this->assertNull($state['current_publication_id']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $state['reason_code']);
    }

    public function test_finalize_held_when_current_pointer_resolution_does_not_match_candidate()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('PASS', 'SUCCESS', 'READABLE', null, true), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 77,
            'candidate_publication_version' => 3,
            'resolved_current_publication_id' => 70,
            'resolved_current_publication_version' => 2,
        ]);

        $this->assertSame('HELD', $state['terminal_status']);
        $this->assertSame('NOT_READABLE', $state['publishability_state']);
        $this->assertSame('2026-04-20', $state['trade_date_effective']);
        $this->assertSame('RUN_LOCK_CONFLICT', $state['reason_code']);
    }

    public function test_finalize_held_when_promotion_errors_after_candidate_seal()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('PASS', 'SUCCESS', 'READABLE', null, true), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 77,
            'candidate_publication_version' => 3,
            'resolved_current_publication_id' => null,
            'resolved_current_publication_version' => null,
            'promotion_error' => 'Promotion lost run ownership while switching current publication.',
        ]);

        $this->assertSame('HELD', $state['terminal_status']);
        $this->assertSame('NOT_READABLE', $state['publishability_state']);
        $this->assertSame('2026-04-20', $state['trade_date_effective']);
        $this->assertSame('RUN_LOCK_CONFLICT', $state['reason_code']);
        $this->assertSame('Promotion lost run ownership while switching current publication.', $state['message']);
        $this->assertNull($state['correction_outcome']);
    }

    public function test_correction_unchanged_keeps_prior_current_publication_and_marks_cancelled()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('PASS', 'SUCCESS', 'READABLE', null, true), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 88,
            'candidate_publication_version' => 4,
            'resolved_current_publication_id' => 55,
            'resolved_current_publication_version' => 2,
            'correction_id' => 9001,
            'prior_publication_id' => 55,
            'prior_publication_version' => 2,
            'unchanged_correction' => true,
        ]);

        $this->assertSame('SUCCESS', $state['terminal_status']);
        $this->assertSame('READABLE', $state['publishability_state']);
        $this->assertSame(55, $state['current_publication_id']);
        $this->assertSame('CANCELLED', $state['correction_outcome']);
        $this->assertStringContainsString('unchanged content', $state['message']);
        $this->assertStringContainsString('unchanged content', $state['correction_outcome_note']);
    }

    public function test_correction_changed_marks_published_after_current_pointer_matches_candidate()
    {
        $service = new PublicationFinalizeOutcomeService();
        $state = $service->resolve($this->preDecision('PASS', 'SUCCESS', 'READABLE', null, true), [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 99,
            'candidate_publication_version' => 5,
            'resolved_current_publication_id' => 99,
            'resolved_current_publication_version' => 5,
            'correction_id' => 9002,
            'prior_publication_id' => 55,
            'prior_publication_version' => 2,
            'unchanged_correction' => false,
        ]);

        $this->assertSame('SUCCESS', $state['terminal_status']);
        $this->assertSame(99, $state['current_publication_id']);
        $this->assertSame('PUBLISHED', $state['correction_outcome']);
        $this->assertStringContainsString('Historical correction published safely', $state['message']);
        $this->assertStringContainsString('Historical correction published safely', $state['correction_outcome_note']);
    }

    public function test_readable_outcome_requires_coverage_pass(): void
    {
        $service = new PublicationFinalizeOutcomeService();
        $preDecision = $this->preDecision('FAIL', 'SUCCESS', 'READABLE', null, true);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires coverage_gate_status PASS');

        $service->resolve($preDecision, [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 77,
            'candidate_publication_version' => 3,
            'resolved_current_publication_id' => 77,
            'resolved_current_publication_version' => 3,
        ]);
    }

    public function test_readable_outcome_requires_complete_coverage_summary(): void
    {
        $service = new PublicationFinalizeOutcomeService();
        $preDecision = $this->preDecision('PASS', 'SUCCESS', 'READABLE', null, true);
        unset($preDecision['coverage_summary']['expected_universe_count']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires expected_universe_count > 0');

        $service->resolve($preDecision, [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => '2026-04-20',
            'candidate_publication_id' => 77,
            'candidate_publication_version' => 3,
            'resolved_current_publication_id' => 77,
            'resolved_current_publication_version' => 3,
        ]);
    }

    private function preDecision(string $coverageState, string $terminalStatus, string $publishabilityState, $reasonCode, bool $promotionAllowed, $effectiveDate = '2026-04-20'): array
    {
        $coverageSummary = [
            'coverage_gate_status' => $coverageState,
            'coverage_gate_state' => $coverageState,
            'expected_universe_count' => 100,
            'available_eod_count' => $coverageState === 'PASS' ? 99 : 80,
            'missing_eod_count' => $coverageState === 'PASS' ? 1 : 20,
            'coverage_ratio' => $coverageState === 'PASS' ? 0.99 : 0.80,
            'coverage_threshold_value' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'coverage_reason_code' => $coverageState === 'PASS' ? 'COVERAGE_THRESHOLD_MET' : 'RUN_COVERAGE_LOW',
        ];

        if ($coverageState === 'NOT_EVALUABLE') {
            $coverageSummary['expected_universe_count'] = 0;
            $coverageSummary['available_eod_count'] = 0;
            $coverageSummary['missing_eod_count'] = 0;
            $coverageSummary['coverage_ratio'] = null;
            $coverageSummary['coverage_reason_code'] = 'RUN_COVERAGE_NOT_EVALUABLE';
        }

        return [
            'coverage_gate_status' => $coverageState,
            'coverage_gate_state' => $coverageState,
            'coverage_summary' => $coverageSummary,
            'quality_gate_state' => $coverageState === 'PASS' ? 'PASS' : ($coverageState === 'FAIL' ? 'FAIL' : 'BLOCKED'),
            'terminal_status' => $terminalStatus,
            'publishability_state' => $publishabilityState,
            'trade_date_effective' => $effectiveDate,
            'reason_code' => $reasonCode,
            'message' => 'promotion pending',
            'promotion_allowed' => $promotionAllowed,
        ];
    }
}
