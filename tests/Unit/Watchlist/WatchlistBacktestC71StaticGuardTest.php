<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC71StaticGuardTest extends TestCase
{
    public function test_c71_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC71ShadowReadOrDryRunRuntimeValidationCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC71ShadowReadOrDryRunRuntimeValidationCommand;', $kernel);
    }

    public function test_c71_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC71ShadowReadOrDryRunRuntimeValidationCommand.php');
        $combined = $service."\n".$command;

        foreach ([
            "latest('trade_date')",
            'latest("trade_date")',
            "orderByDesc('trade_date')",
            'orderByDesc("trade_date")',
            'MAX(trade_date)',
            "max('trade_date')",
            'returnFieldsUsedForSelection',
            'futurePathUsedForSelection',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $combined, $forbidden);
        }
    }

    public function test_c71_docs_state_shadow_read_dry_run_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C71_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C71 is shadow-read / dry-run runtime validation',
            'C71 starts from locked C70 final evidence',
            'C70 controlled deployment execution review passed primary + backup',
            'E02 is primary shadow-read/dry-run runtime validation candidate',
            'B01 is backup shadow-read/dry-run runtime validation candidate',
            'A01 is comparator-only and cannot be promoted',
            'C71 validates C70 artifact hash and file SHA1',
            'C71 validates C70 readiness through nested `c71_readiness_decision.*` path',
            'C71 validates C70 → C60 lineage',
            'C71 does not redesign',
            'C71 does not retune',
            'C71 does not run parameter search',
            'C71 does not use OOS to rerank',
            'C71 does not change candidate scope',
            'C71 may create isolated shadow-read proof',
            'C71 may create isolated dry-run proof',
            'C71 may create baseline PLAN/CONFIRM non-mutation proof',
            'C71 may create fallback behavior proof',
            'C71 does not wire activated catalog to PLAN/CONFIRM live',
            'C71 does not deploy live production',
            'C71 does not mutate PLAN/CONFIRM',
            'C71 does not change PLAN/CONFIRM output',
            'C71 keeps `production_catalog_runtime_wired=false`',
            'C71 keeps `shadow_read_runtime_active=false`',
            'C71 keeps `dry_run_runtime_active=false`',
            'C71 keeps `production_deployment_allowed=false`',
            'C71 keeps `production_deployment_executed=false`',
            'C71 keeps `plan_confirm_mutation_allowed=false`',
            'C71 keeps `plan_confirm_mutated=false`',
            'C71 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C71 keeps `live_plan_confirm_rollout_allowed=false`',
            'C71 keeps `live_plan_confirm_rollout_executed=false`',
            'C71 carries bad-month risk as documented risk',
            'C71 carries weak-regime risk as documented risk',
            'C71 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C71 may only recommend C72 controlled opt-in runtime bridge validation if all shadow/dry-run gates pass',
            'C71 pass is not full production deployment',
            'C71 pass is not PLAN/CONFIRM rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c71_config_defaults_are_off_and_contracts_are_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $shadow = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogShadowReadRuntimeValidationContract.php');
        $dryRun = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogDryRunRuntimeValidationContract.php');

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_shadow_read_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_dry_run_enabled' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $shadow);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $dryRun);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $shadow);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $dryRun);
    }

    public function test_c71_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION.md');

        foreach ([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ] as $candidate) {
            $this->assertStringContainsString($candidate, $service);
            $this->assertStringContainsString($candidate, $doc);
        }
    }
}
