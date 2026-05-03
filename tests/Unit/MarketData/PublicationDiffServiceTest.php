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

    public function test_compare_returns_changed_scope_when_hash_differs()
    {
        $service = new PublicationDiffService();
        $prior = (object) ['publication_id' => 1, 'publication_version' => 1, 'run_id' => 10, 'bars_batch_hash' => 'HB', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];
        $candidate = (object) ['publication_id' => 2, 'publication_version' => 2, 'run_id' => 11, 'bars_batch_hash' => 'HB2', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];

        $comparison = $service->compare($prior, $candidate);

        $this->assertSame('CHANGED', $comparison['decision']);
        $this->assertSame(['bars'], $comparison['changed_scope']);
        $this->assertSame('CORRECTION_ARTIFACT_CHANGED', $comparison['reason_code']);
    }

    public function test_compare_rejects_incomplete_hashes_as_invalid()
    {
        $service = new PublicationDiffService();
        $prior = (object) ['bars_batch_hash' => 'HB', 'indicators_batch_hash' => 'HI', 'eligibility_batch_hash' => 'HE'];
        $candidate = (object) ['bars_batch_hash' => 'HB', 'indicators_batch_hash' => null, 'eligibility_batch_hash' => 'HE'];

        $comparison = $service->compare($prior, $candidate);

        $this->assertSame('INVALID', $comparison['decision']);
        $this->assertSame('CORRECTION_ARTIFACT_HASH_INCOMPLETE', $comparison['reason_code']);
        $this->assertFalse($service->isUnchanged($prior, $candidate));
    }

}
