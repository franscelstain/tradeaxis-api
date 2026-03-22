<?php

use App\Application\MarketData\Services\PublicationDiffService;
use PHPUnit\Framework\TestCase;

class PublicationDiffServiceTest extends TestCase
{
    public function test_is_unchanged_returns_true_when_all_batch_hashes_match()
    {
        $service = new PublicationDiffService();
        $prior = (object) ['bars_batch_hash' => 'HB', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];
        $candidate = (object) ['bars_batch_hash' => 'HB', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];

        $this->assertTrue($service->isUnchanged($prior, $candidate));
    }

    public function test_is_unchanged_returns_false_when_any_hash_changes()
    {
        $service = new PublicationDiffService();
        $prior = (object) ['bars_batch_hash' => 'HB', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];
        $candidate = (object) ['bars_batch_hash' => 'HB2', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];

        $this->assertFalse($service->isUnchanged($prior, $candidate));
    }
}
