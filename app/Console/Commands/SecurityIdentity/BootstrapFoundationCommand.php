<?php

namespace App\Console\Commands\SecurityIdentity;

use App\Application\SecurityIdentity\FoundationService;
use Illuminate\Console\Command;

final class BootstrapFoundationCommand extends Command
{
    protected $signature = 'security-identity:bootstrap {package : Frozen E005 source package directory}';
    protected $description = 'Admit bounded shared identity facts using retained registry assignments; unresolved listings remain held.';
    private FoundationService $foundation;

    public function __construct(FoundationService $foundation)
    {
        parent::__construct();
        $this->foundation = $foundation;
    }

    public function handle(): int
    {
        try {
            $result = $this->foundation->bootstrap((string)$this->argument('package'), base_path('resources/security_identity/foundation-source-basis-20260928-v1.registry.json'));
            $d = $result->document();
            $counts = array_count_values(array_column($d['entities'], 'entity_type'));
            $this->line(json_encode(['state' => 'BOUNDED_CORE_ADMITTED', 'entity_counts' => $counts, 'holds' => count($d['holds']), 'dependency_resolved' => false], JSON_THROW_ON_ERROR));
            return 0;
        } catch (\DomainException | \InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
