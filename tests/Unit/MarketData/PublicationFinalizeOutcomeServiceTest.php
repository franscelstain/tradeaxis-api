<?php

use App\Application\MarketData\Services\PublicationFinalizeOutcomeService;
use PHPUnit\Framework\TestCase;

class PublicationFinalizeOutcomeServiceTest extends TestCase
{
    public function test_finalize_success_when_current_pointer_resolves_to_candidate_publication()
    {
        $service = new PublicationFinalizeOutcomeService();
        $preDecision = [
            'coverage_gate_status' => 'PASS',
            'quality_gate_state' => 'PASS',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => null,
            'reason_code' => null,
            'message' => 'promotion pending',
            'promotion_allowed' => true,
        ];

        $state = $service->resolve($preDecision, [
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
        $preDecision = [
            'coverage_gate_status' => 'FAIL',
            'quality_gate_state' => 'FAIL',
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => '2026-04-20',
            'reason_code' => 'RUN_COVERAGE_LOW',
            'message' => 'Finalize held because coverage gate failed and fallback readable publication remains available.',
            'promotion_allowed' => false,
        ];

        $state = $service->resolve($preDecision, [
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
        $preDecision = [
            'coverage_gate_status' => 'FAIL',
            'quality_gate_state' => 'FAIL',
            'terminal_status' => 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => null,
            'reason_code' => 'RUN_COVERAGE_LOW',
            'message' => 'Finalize failed because coverage gate failed and no readable fallback publication exists.',
            'promotion_allowed' => false,
        ];

        $state = $service->resolve($preDecision, [
            'requested_date' => '2026-04-21',
            'fallback_trade_date' => null,
        ]);

        $this->assertSame('FAILED', $state['terminal_status']);
        $this->assertSame('NOT_READABLE', $state['publishability_state']);
        $this->assertNull($state['trade_date_effective']);
        $this->assertSame('RUN_COVERAGE_LOW', $state['reason_code']);
    }

    public function test_outcome_keeps_not_evaluable_non_readable_and_never_promotes()
    {
        $service = new PublicationFinalizeOutcomeService();
        $preDecision = [
            'coverage_gate_status' => 'NOT_EVALUABLE',
            'quality_gate_state' => 'BLOCKED',
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => '2026-04-20',
            'reason_code' => 'RUN_COVERAGE_NOT_EVALUABLE',
            'message' => 'Finalize held because coverage gate could not be evaluated safely and fallback readable publication remains available.',
            'promotion_allowed' => false,
        ];

        $state = $service->resolve($preDecision, [
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
        $preDecision = [
            'coverage_gate_status' => 'PASS',
            'quality_gate_state' => 'PASS',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => null,
            'reason_code' => null,
            'message' => 'promotion pending',
            'promotion_allowed' => true,
        ];

        $state = $service->resolve($preDecision, [
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
        $preDecision = [
            'coverage_gate_status' => 'PASS',
            'quality_gate_state' => 'PASS',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => null,
            'reason_code' => null,
            'message' => 'promotion pending',
            'promotion_allowed' => true,
        ];

        $state = $service->resolve($preDecision, [
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
        $preDecision = [
            'coverage_gate_status' => 'PASS',
            'quality_gate_state' => 'PASS',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => null,
            'reason_code' => null,
            'message' => 'promotion pending',
            'promotion_allowed' => true,
        ];

        $state = $service->resolve($preDecision, [
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
        $preDecision = [
            'coverage_gate_status' => 'PASS',
            'quality_gate_state' => 'PASS',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => null,
            'reason_code' => null,
            'message' => 'promotion pending',
            'promotion_allowed' => true,
        ];

        $state = $service->resolve($preDecision, [
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
}
