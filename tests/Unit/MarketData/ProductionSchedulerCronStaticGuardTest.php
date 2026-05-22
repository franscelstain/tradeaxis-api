<?php

use PHPUnit\Framework\TestCase;

class ProductionSchedulerCronStaticGuardTest extends TestCase
{
    private function read($path)
    {
        $fullPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

        $this->assertFileExists($fullPath, $path.' must exist.');

        return file_get_contents($fullPath);
    }

    public function test_scheduler_registers_daily_command_with_timezone_output_and_failure_markers()
    {
        $kernel = $this->read('app/Console/Kernel.php');

        foreach ([
            "config('market_data.pipeline.daily_enabled')",
            "command('market-data:daily --latest')",
            "dailyAt(substr(config('market_data.platform.cutoff_time'), 0, 5))",
            "config('market_data.platform.timezone', 'Asia/Jakarta')",
            '->timezone($timezone)',
            'withoutOverlapping',
            'appendOutputTo',
            'onSuccess',
            'onFailure',
            'scheduler_status=%s',
            'scheduleOutputPath',
            'appendSchedulerStatusLine',
        ] as $needle) {
            $this->assertStringContainsString($needle, $kernel);
        }
    }

    public function test_scheduler_config_and_env_surface_are_documented()
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');
        $inventory = $this->read('docs/market_data/audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md');

        foreach ([$config, $envExample, $envTesting, $inventory] as $document) {
            $this->assertStringContainsString('MARKET_DATA_SCHEDULER_OUTPUT_PATH', $document);
            $this->assertStringContainsString('MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES', $document);
            $this->assertStringContainsString('storage/logs/market-data-scheduler.log', $document);
            $this->assertStringContainsString('120', $document);
        }
    }

    public function test_audit_docs_record_scheduler_cron_deployment_proof()
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->read('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');
        $proofPack = $this->read('docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md');
        $opsBaseline = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        foreach ([$status, $tracker, $inventory, $proofPack, $opsBaseline] as $document) {
            $this->assertStringContainsString('PRODUCTION_SCHEDULER_CRON_DEPLOYMENT_PROOF_CONTRACT', $document);
            $this->assertStringContainsString('SCHEDULER_RUNTIME_LOG_PRODUCED', $document);
            $this->assertStringContainsString('scheduler_status=FAILURE', $document);
            $this->assertStringContainsString('OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED', $document);
            $this->assertStringContainsString('artifact-presence-audit.txt', $document);
            $this->assertStringContainsString('negative-db-override-proof-gap.txt', $document);
            $this->assertStringContainsString('provider-smoke-gap.txt', $document);
        }
    }


    public function test_scheduler_runtime_proof_claim_is_backed_by_current_artifacts()
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $proofPack = $this->read('docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md');

        $this->assertStringContainsString('PROVIDER_RATE_LIMIT_SCHEDULER_DUE_RUN_RECONCILIATION', $status.$tracker.$proofPack);
        $this->assertStringContainsString('SCHEDULER_RUNTIME_LOG_PRODUCED', $status.$tracker.$proofPack);
        $this->assertStringContainsString('OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED', $status.$tracker.$proofPack);
        if (strpos($status.$tracker.$proofPack, '[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED') !== false) {
            $providerArtifact = $this->read('storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt');
            $this->assertStringContainsString('provider_smoke_status=PASS', $providerArtifact);
            $this->assertStringContainsString('reason_code=PROVIDER_SMOKE_OK', $providerArtifact);
        }

        foreach ([
            'storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase3-schedule-run-enabled-due.txt' => 'Running scheduled command',
            'storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase4-scheduler-output-log.txt' => 'SCHEDULER_RUNTIME_LOG_PRODUCED',
            'storage/app/market-data/production-scheduler-cron-deployment-proof/runtime/market-data-scheduler-proof.log' => 'scheduler_status=FAILURE',
            'storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/artifact-presence-audit.txt' => 'status=PASS_WITH_AUXILIARY_REFRESH_RECOMMENDED',
            'storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/negative-db-override-proof-gap.txt' => 'status=PASS',
            'storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/provider-smoke-gap.txt' => 'status=BLOCKED_PROVIDER_RATE_LIMITED',
        ] as $path => $needle) {
            $contents = $this->read($path);
            $this->assertStringContainsString($needle, $contents);
        }
    }

    public function test_operational_runbook_documents_cron_operator_contract()
    {
        $runbook = $this->read('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'Scheduler / cron deployment flow',
            'php artisan schedule:run',
            'MARKET_DATA_DAILY_ENABLED=true',
            'MARKET_DATA_PLATFORM_TIMEZONE=Asia/Jakarta',
            'MARKET_DATA_PLATFORM_EOD_CUTOFF_TIME=HH:MM:SS',
            'MARKET_DATA_SCHEDULER_OUTPUT_PATH',
            'MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES',
            'market-data:daily --latest',
            'scheduler_status=SUCCESS',
            'scheduler_status=FAILURE',
            'pointer_switched=false',
            'Scheduler proof is not live provider proof.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }
}