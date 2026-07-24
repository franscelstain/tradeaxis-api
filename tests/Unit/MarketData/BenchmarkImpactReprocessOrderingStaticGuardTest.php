<?php

namespace Tests\Unit\MarketData;

use PHPUnit\Framework\TestCase;

class BenchmarkImpactReprocessOrderingStaticGuardTest extends TestCase
{
    public function test_benchmark_ingest_precedes_bar_mutation_impact_reprocess(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 3).'/app/Application/MarketData/Services/MarketDataPipelineService.php'
        );

        $this->assertIsString($source);
        $this->assertSame(2, preg_match_all(
            '/\\$benchmarkResult = \\$this->ingestBenchmarkNonBlocking\\([^;]+;\\s*'
            .'\\$result = \\$this->withImpactReprocessExecution\\([^;]+;/s',
            $source
        ));
        $this->assertDoesNotMatchRegularExpression(
            '/\\$result = \\$this->withImpactReprocessExecution\\([^;]+;\\s*'
            .'\\$benchmarkResult = \\$this->ingestBenchmarkNonBlocking\\([^;]+;/s',
            $source
        );
    }
}
