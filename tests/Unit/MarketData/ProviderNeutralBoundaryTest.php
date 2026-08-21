<?php

use PHPUnit\Framework\TestCase;

/**
 * Stage 3 exit gate — W02.
 *
 * "mengganti adapter tidak mengubah canonical/product/indicator/read contracts dan Yahoo tidak
 *  pernah dilabel official IDX source."
 *
 * Owner contract: docs/market_data/authority/strategy/book/Yahoo_Finance_Bootstrap_Source_Strategy.md
 *
 * These execute the boundary rather than asserting a document says so: they resolve the real
 * config, load the real port and adapter, and walk the real downstream source tree.
 */
class ProviderNeutralBoundaryTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function config(): array
    {
        if (! function_exists('env')) {
            eval('function env($key, $default = null) { return $default; }');
        }

        return require $this->root().'/config/market_data.php';
    }

    /**
     * Downstream contracts must not name the concrete adapter or the provider. If they did,
     * swapping the adapter would require editing canonical, product, indicator, or read code —
     * which is exactly the coupling this stage exists to prevent.
     */
    public function test_downstream_contracts_never_name_the_concrete_adapter_or_provider(): void
    {
        $downstream = [
            'app/Application/MarketData/Services/MarketDataReadProductService.php',
            'app/Application/MarketData/Services/MarketDataPriceReadService.php',
            'app/Application/MarketData/Services/IndicatorVectorService.php',
            'app/Application/MarketData/Services/EodIndicatorsComputeService.php',
            'app/Application/MarketData/Services/EodEligibilityBuildService.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
        ];

        $violations = [];
        foreach ($downstream as $relative) {
            $path = $this->root().'/'.$relative;
            if (! is_file($path)) {
                continue;
            }
            $source = file_get_contents($path);
            if (stripos($source, 'yahoo') !== false) {
                $violations[] = $relative.' names the provider';
            }
            if (strpos($source, 'PublicApiEodBarsAdapter') !== false) {
                $violations[] = $relative.' names the concrete adapter';
            }
            if (strpos($source, '.JK') !== false) {
                $violations[] = $relative.' carries a provider symbol-rendering rule';
            }
        }

        $this->assertSame([], $violations, 'downstream contracts must survive an adapter swap unchanged');
    }

    /**
     * The adapter is reachable only through the neutral port. A concrete type-hint anywhere in
     * the application layer would make the port decorative.
     */
    public function test_application_layer_type_hints_the_port_and_never_the_adapter(): void
    {
        $adapter = file_get_contents($this->root().'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $this->assertStringContainsString('implements ApiEodBarsSource', $adapter);

        $violations = [];
        foreach (glob($this->root().'/app/Application/MarketData/**/*.php') as $file) {
            $source = file_get_contents($file);
            if (strpos($source, 'PublicApiEodBarsAdapter') !== false) {
                $violations[] = str_replace($this->root().'/', '', $file);
            }
        }

        $this->assertSame([], $violations, 'application layer must depend on the port, never the adapter');
    }

    /**
     * The bootstrap source is deliberately unofficial. Any wording that presents it as an
     * exchange source would misrepresent provenance to every downstream reader.
     */
    public function test_provider_is_never_labelled_an_official_exchange_source(): void
    {
        $forbidden = '/(official[^.\n]{0,20}(idx|exchange)|(idx|exchange)[^.\n]{0,20}official\s+(source|feed)|authoritative\s+idx\s+source)/i';

        $violations = [];
        foreach (['app', 'config'] as $dir) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/'.$dir));
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                if (stripos($source, 'yahoo') === false && stripos($source, 'api_free') === false) {
                    continue;
                }
                if (preg_match($forbidden, $source)) {
                    $violations[] = str_replace($this->root().'/', '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $violations, 'the bootstrap source must never be labelled an official exchange source');
    }

    /**
     * Manual file is controlled recovery, not a second live feed. Its source mode must exist and
     * must not be the configured default, because a default recovery path is not a recovery path.
     */
    public function test_manual_file_is_recovery_and_never_the_default_source_mode(): void
    {
        $config = $this->config();

        $this->assertSame('api', $config['pipeline']['default_source_mode']);
        $this->assertFileExists($this->root().'/app/Application/MarketData/Ports/ManualEodBarsSource.php');
    }

    /**
     * Licensing basis: the parts determinable from the resolver must be recorded and consistent.
     * The bootstrap decision is justified by cost avoidance, and that justification only holds if
     * the access profile is the unauthenticated public one it claims to be.
     */
    public function test_access_profile_matches_the_declared_public_bootstrap_basis(): void
    {
        $config = $this->config();

        $this->assertSame('bootstrap-public-access', $config['governance']['credential_profile']);
        $this->assertSame('', trim((string) $config['source']['api']['auth_header_name']));
        $this->assertSame('', trim((string) $config['source']['api']['auth_token']));
    }

    /**
     * The licensing basis must be a recorded declaration, not an assumption. All four required
     * items must appear, including the ones whose honest current value is "not established".
     */
    public function test_licensing_basis_declaration_is_recorded(): void
    {
        $strategy = file_get_contents($this->root().'/docs/market_data/authority/strategy/book/Yahoo_Finance_Bootstrap_Source_Strategy.md');

        $this->assertStringContainsString('### Deklarasi berlaku', $strategy);
        $this->assertStringContainsString('Internal dan non-komersial', $strategy);
        $this->assertStringContainsString('Peristiwa yang mengubah dasar ini', $strategy);
        $this->assertStringContainsString('Batas yang diketahui', $strategy);
    }

    /**
     * The contract forbids an undated compliance claim, not an unread set of terms. While the
     * terms carry no read date, nothing in the platform may assert compliance with them —
     * absence of a claim is a valid state, an unverifiable claim is not.
     */
    public function test_no_compliance_claim_exists_while_provider_terms_are_undated(): void
    {
        $strategy = file_get_contents($this->root().'/docs/market_data/authority/strategy/book/Yahoo_Finance_Bootstrap_Source_Strategy.md');
        $termsAreUndated = strpos($strategy, 'Belum dibaca dan belum bertanggal') !== false;

        if (! $termsAreUndated) {
            $this->assertMatchesRegularExpression(
                '/terms .{0,40}(dibaca|read).{0,40}\d{4}-\d{2}-\d{2}/iu',
                $strategy,
                'once the undated marker is removed, a read date must be present'
            );

            return;
        }

        $claim = '/(comply|complies|compliant|mematuhi|kepatuhan)\s+(with\s+|terhadap\s+|dengan\s+)?(provider\s+)?terms/iu';

        $violations = [];
        foreach (['app', 'config', 'tests'] as $dir) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/'.$dir));
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                if (preg_match($claim, $source) && stripos($source, 'tidak ada klaim') === false) {
                    $violations[] = str_replace($this->root().'/', '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $violations, 'no compliance claim may exist while provider terms carry no read date');
    }

    /**
     * Paid-provider work stays deferred: no credential, entitlement, or vendor configuration may
     * appear while the bootstrap phase is active.
     */
    public function test_no_paid_provider_configuration_has_been_introduced(): void
    {
        $forbidden = '/(^|_)(subscription|entitlement|vendor|license_key|api_secret|billing)($|_)/i';

        $violations = [];
        $walk = function (array $node, string $prefix) use (&$walk, &$violations, $forbidden) {
            foreach ($node as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                if (preg_match($forbidden, (string) $key)) {
                    $violations[] = $path;
                }
                if (is_array($value)) {
                    $walk($value, $path);
                }
            }
        };
        $walk($this->config(), '');

        $this->assertSame([], $violations, 'paid-provider configuration must remain deferred');
    }
}
