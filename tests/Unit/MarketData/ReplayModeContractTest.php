<?php

use App\Application\MarketData\Services\ReplayMode;
use PHPUnit\Framework\TestCase;

class ReplayModeContractTest extends TestCase
{
    public function test_only_the_two_locked_replay_modes_are_accepted(): void
    {
        $this->assertSame('PUBLICATION_EXACT', ReplayMode::normalize('publication_exact'));
        $this->assertSame('AS_KNOWN', ReplayMode::normalize('AS_KNOWN'));
    }

    public function test_unmoded_replay_fails_closed_instead_of_defaulting_to_publication(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_MODE_REQUIRED');
        ReplayMode::normalize(null);
    }

    public function test_unknown_mode_fails_closed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_MODE_UNSUPPORTED');
        ReplayMode::normalize('LATEST');
    }
}
