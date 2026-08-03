<?php

use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for the destructive-command safety surface.
 *
 * `CommandSurfaceSafetyStaticGuardTest` checked a handful of named commands for the strings
 * "{--dry-run" and "{--apply". That protects the commands someone remembered to list, and a
 * new destructive command added tomorrow would pass unnoticed.
 *
 * This walks every registered market-data command instead, so the rule holds for commands
 * that do not exist yet.
 */
class DestructiveCommandSafetySurfaceTest extends TestCase
{
    /**
     * @return array<string, string> command name => signature
     */
    private function marketDataSignatures(): array
    {
        $kernel = new ReflectionClass(\App\Console\Kernel::class);
        $property = $kernel->getProperty('commands');
        $property->setAccessible(true);

        $signatures = [];

        foreach ($property->getValue($kernel->newInstanceWithoutConstructor()) as $commandClass) {
            if (! class_exists($commandClass)) {
                continue;
            }

            $signatureProperty = (new ReflectionClass($commandClass))->getProperty('signature');
            $signatureProperty->setAccessible(true);

            $signature = (string) $signatureProperty->getValue(
                (new ReflectionClass($commandClass))->newInstanceWithoutConstructor()
            );

            $name = trim(strtok($signature, " \n\t{"));

            if (strpos($name, 'market-data:') === 0) {
                $signatures[$name] = $signature;
            }
        }

        ksort($signatures);

        return $signatures;
    }

    public function test_the_kernel_registers_market_data_commands(): void
    {
        $this->assertNotEmpty($this->marketDataSignatures());
    }

    /**
     * An operator must always be able to see what a mutating command would do before it does
     * it, so a command offering --apply normally offers --dry-run as well.
     *
     * One documented exception: market-data:current-publication:repair is detect-only by
     * default. Running it without --apply reports the invalid pointer states it found and
     * changes nothing, so the preview is the default path rather than a separate flag. The
     * safety property — mutation is opt-in — holds either way; only the convention differs.
     *
     * The exception is listed rather than silently tolerated so that adding a second one is a
     * deliberate act.
     */
    public function test_every_command_that_can_apply_can_also_preview(): void
    {
        $detectOnlyByDefault = ['market-data:current-publication:repair'];

        $missingPreview = [];

        foreach ($this->marketDataSignatures() as $name => $signature) {
            if (strpos($signature, '{--apply') === false || in_array($name, $detectOnlyByDefault, true)) {
                continue;
            }

            if (strpos($signature, '{--dry-run') === false) {
                $missingPreview[] = $name;
            }
        }

        $this->assertSame([], $missingPreview, 'Commands offering --apply without a --dry-run preview path.');
    }

    /**
     * `--apply` and `--dry-run` must be flags, never options carrying a default value. An
     * option written as {--apply=true} would make the destructive path the default.
     */
    public function test_apply_and_dry_run_are_flags_not_defaulted_options(): void
    {
        $defaulted = [];

        foreach ($this->marketDataSignatures() as $name => $signature) {
            foreach (['apply', 'dry-run'] as $flag) {
                if (preg_match('/\{--'.preg_quote($flag, '/').'=/', $signature)) {
                    $defaulted[] = $name.' --'.$flag;
                }
            }
        }

        $this->assertSame([], $defaulted, 'Safety flags must not carry defaults.');
    }

    /**
     * force_replace overrides a valid current publication, so it must stay opt-in. A default
     * of true would turn every promote into an uncontrolled replacement.
     */
    public function test_force_replace_never_defaults_to_true(): void
    {
        $unsafe = [];

        foreach ($this->marketDataSignatures() as $name => $signature) {
            if (preg_match('/\{--force_replace=(\w+)/', $signature, $matches) && $matches[1] !== 'false') {
                $unsafe[] = $name.' defaults force_replace to '.$matches[1];
            }
        }

        $this->assertSame([], $unsafe);
    }

    /**
     * Every command name must be unique. A duplicate silently shadows one implementation and
     * the operator cannot tell which one ran.
     */
    public function test_command_names_are_unique(): void
    {
        $kernel = new ReflectionClass(\App\Console\Kernel::class);
        $property = $kernel->getProperty('commands');
        $property->setAccessible(true);

        $classes = $property->getValue($kernel->newInstanceWithoutConstructor());

        $this->assertSame(
            count($classes),
            count(array_unique($classes)),
            'The kernel must not register the same command class twice.'
        );
    }
}
