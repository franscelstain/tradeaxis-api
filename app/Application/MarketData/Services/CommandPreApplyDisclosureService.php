<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;

/** Shared canonical identity for R0011 scan-to-write disclosures. */
class CommandPreApplyDisclosureService
{
    private $snapshots;

    public function __construct(MarketDataConfigSnapshotRepository $snapshots = null)
    {
        $this->snapshots = $snapshots ?: new MarketDataConfigSnapshotRepository();
    }

    public function currentConfigHash(): string
    {
        return hash('sha256', $this->snapshots->currentContent());
    }

    public function fingerprint(array $value): string
    {
        return hash('sha256', json_encode($this->sortRecursive($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function assertSame(array $disclosed, array $actual, string $reasonCode): void
    {
        if (! hash_equals($this->fingerprint($disclosed), $this->fingerprint($actual))) {
            throw new \RuntimeException($reasonCode);
        }
    }

    private function sortRecursive(array $value): array
    {
        foreach ($value as $key => $child) {
            if (is_array($child)) {
                $value[$key] = $this->sortRecursive($child);
            }
        }
        if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
            ksort($value, SORT_STRING);
        }
        return $value;
    }
}
