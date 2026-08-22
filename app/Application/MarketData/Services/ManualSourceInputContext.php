<?php

namespace App\Application\MarketData\Services;

/**
 * Per-process request context for an explicitly supplied manual source file.
 *
 * The path is operational input/provenance, not platform configuration. Keeping it out of the
 * config repository prevents request data from entering the immutable config-key namespace.
 */
class ManualSourceInputContext
{
    private $path;

    public function path()
    {
        return $this->path;
    }

    public function set($path): void
    {
        $this->path = $path === null || trim((string) $path) === '' ? null : (string) $path;
    }
}
