<?php

use PHPUnit\Framework\TestCase;

class OpsCommandSurfaceRuntimeMatrixStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path, $relativePath.' must exist.');

        return file_get_contents($path);
    }





    public function test_command_owned_missing_input_guards_are_present_in_code(): void
    {
        $files = [
            'app/Console/Commands/MarketData/BackfillMarketDataCommand.php' => ['{start_date?} {end_date?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/VerifyReplayCommand.php' => ['{run_id?} {fixture_path?}', 'COMMAND_MISSING_REQUIRED_INPUT', 'replay_status'],
            'app/Console/Commands/MarketData/ReplaySmokeSuiteCommand.php' => ['{run_id?}', 'COMMAND_MISSING_REQUIRED_INPUT', 'COMMAND_EXECUTION_FAILED', 'replay_status'],
            'app/Console/Commands/MarketData/ReplayBackfillCommand.php' => ['{start_date?} {end_date?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php' => ['{run_id?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/ApproveCorrectionCommand.php' => ['{correction_id?}', 'COMMAND_CORRECTION_NOT_FOUND', 'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE'],
            'app/Console/Commands/MarketData/RunCorrectionCommand.php' => ['{correction_id?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/CaptureSessionSnapshotCommand.php' => ['{trade_date?} {snapshot_slot?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/IngestEodBarsCommand.php' => ['--request_mode=', 'validateStageRequestModeString'],
        ];

        foreach ($files as $path => $needles) {
            $contents = $this->read($path);
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $contents, $needle.' must be present in '.$path);
            }
        }
    }

    public function test_stage_hash_service_method_is_public_for_command_runtime(): void
    {
        $method = new ReflectionMethod(
            App\Application\MarketData\Services\MarketDataPipelineService::class,
            'completeHash'
        );

        $this->assertTrue($method->isPublic(), 'market-data:audit:hash must be able to invoke completeHash at runtime.');
    }
}
