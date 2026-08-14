<?php

use App\Support\DestructiveMigrationGuard;
use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for the destructive migration guard.
 *
 * `TestingDatabaseIsolationStaticGuardTest` asserted that `artisan` contains the guard function
 * names, the four command strings, and that the call appears before `$kernel->handle(`. All of
 * that was true while the guard did not cover the case that matters.
 *
 * Both guards began by returning early unless the environment was already `testing`. So the
 * protection applied to a misconfigured testing run, and `php artisan migrate:fresh` with no
 * --env — which resolves to .env and therefore to the production database holding the full bar
 * history — passed straight through. The guard was named for isolating the testing database and
 * was, in effect, only able to refuse the one database it was safe to drop.
 *
 * The rule is now stated without reference to environment, and it is stated here rather than in
 * `artisan` because a bootstrap script cannot be required without booting the app and running a
 * command. That is also why this could not be proven end to end: the only honest end-to-end proof
 * is to run migrate:fresh against production and see it refused, and if the guard were wrong the
 * test would destroy the data it exists to protect.
 */
class DestructiveMigrationGuardTest extends TestCase
{
    /**
     * @dataProvider destructiveCommands
     */
    public function test_a_destructive_command_is_refused_for_the_production_database(string $command): void
    {
        $this->assertTrue(
            DestructiveMigrationGuard::shouldBlock($command, 'tradeaxis'),
            $command.' must be refused against the production database.'
        );
    }

    /**
     * The case the old guard missed: no environment is involved in the decision at all.
     *
     * @dataProvider destructiveCommands
     */
    public function test_a_destructive_command_is_refused_regardless_of_any_environment(string $command): void
    {
        foreach (['tradeaxis', 'tradeaxis_prod', 'tradeaxis_backup', 'mysql', ''] as $database) {
            $this->assertTrue(
                DestructiveMigrationGuard::shouldBlock($command, $database),
                $command.' must be refused against "'.$database.'".'
            );
        }
    }

    /**
     * @dataProvider destructiveCommands
     */
    public function test_a_destructive_command_is_allowed_for_the_testing_database(string $command): void
    {
        $this->assertFalse(DestructiveMigrationGuard::shouldBlock($command, 'tradeaxis_testing'));
    }

    public function destructiveCommands(): array
    {
        return [
            'migrate:fresh' => ['migrate:fresh'],
            'migrate:refresh' => ['migrate:refresh'],
            'migrate:reset' => ['migrate:reset'],
            'db:wipe' => ['db:wipe'],
        ];
    }

    /**
     * An unresolvable target is refused. The guard cannot prove it is the testing database, and
     * "could not tell" must not mean "proceed".
     *
     * @dataProvider unresolvableTargets
     */
    public function test_an_unresolvable_database_is_refused($database): void
    {
        $this->assertTrue(DestructiveMigrationGuard::shouldBlock('migrate:fresh', $database));
    }

    public function unresolvableTargets(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
        ];
    }

    /**
     * Forward migration is not destructive and must keep working against any database. Blocking
     * it would make the guard something an operator has to disable, and a guard that gets
     * disabled protects nothing.
     *
     * @dataProvider nonDestructiveCommands
     */
    public function test_non_destructive_commands_are_never_blocked(string $command): void
    {
        $this->assertFalse(DestructiveMigrationGuard::isDestructive($command));
        $this->assertFalse(DestructiveMigrationGuard::shouldBlock($command, 'tradeaxis'));
    }

    public function nonDestructiveCommands(): array
    {
        return [
            'forward migrate' => ['migrate'],
            'migrate status' => ['migrate:status'],
            'migrate rollback' => ['migrate:rollback'],
            'db seed' => ['db:seed'],
            'a market-data command' => ['market-data:daily'],
            'no command at all' => [''],
        ];
    }

    /**
     * `migrate` must not be caught by a prefix match on `migrate:`. A guard that blocked forward
     * migrations against production would be removed within a day.
     */
    public function test_forward_migrate_is_not_caught_by_a_prefix_match(): void
    {
        $this->assertFalse(DestructiveMigrationGuard::isDestructive('migrate'));
        $this->assertTrue(DestructiveMigrationGuard::isDestructive('migrate:fresh'));
    }

    /**
     * The refusal has to tell an operator what was refused, what it resolved to, and what to do
     * instead. A bare reason code sends them looking for a config problem that may not exist.
     */
    public function test_the_refusal_names_the_command_the_target_and_the_way_forward(): void
    {
        $message = DestructiveMigrationGuard::blockMessage('migrate:fresh', 'tradeaxis', 'mysql');

        $this->assertStringContainsString('BLOCKED_TESTING_DATABASE_ENV', $message);
        $this->assertStringContainsString('migrate:fresh', $message);
        $this->assertStringContainsString("resolves to 'tradeaxis'", $message);
        $this->assertStringContainsString('tradeaxis_testing', $message);
        $this->assertStringContainsString('--env=testing', $message);
        $this->assertStringContainsString('forward `migrate`', $message);
    }

    public function test_an_unresolved_target_is_described_as_unresolved_rather_than_blank(): void
    {
        $this->assertStringContainsString('[unresolved]', DestructiveMigrationGuard::blockMessage('db:wipe', null));
        $this->assertStringContainsString('[unresolved]', DestructiveMigrationGuard::blockMessage('db:wipe', ''));
    }
}
