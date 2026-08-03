<?php

namespace App\Support;

/**
 * Decides whether a destructive migration command may run against a given database.
 *
 * The rule is one sentence: migrate:fresh, migrate:refresh, migrate:reset and db:wipe may only
 * ever target the testing database. Forward `migrate` is deliberately not on that list, so
 * production migrations are unaffected — there is no legitimate reason to drop or reset the
 * production schema through artisan.
 *
 * This used to be decided inline in `artisan`, and only when the environment was already
 * `testing`. That protected a misconfigured testing run and left the more likely accident
 * completely unguarded: running `php artisan migrate:fresh` with no --env at all resolves to
 * .env, which points at the production database. Both guards returned early and the command
 * proceeded.
 *
 * Keeping the decision here rather than in the bootstrap script is what makes it testable:
 * `artisan` cannot be required without booting the application and executing a command.
 */
class DestructiveMigrationGuard
{
    /**
     * Commands that drop, truncate or roll back schema.
     */
    const DESTRUCTIVE_COMMANDS = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
    ];

    /**
     * The only database these commands may target.
     */
    const TESTING_DATABASE = 'tradeaxis_testing';

    const REASON_CODE = 'BLOCKED_TESTING_DATABASE_ENV';

    public static function isDestructive($command): bool
    {
        return in_array((string) $command, self::DESTRUCTIVE_COMMANDS, true);
    }

    /**
     * Whether the command must be refused.
     *
     * A null or empty database means the target could not be resolved. That is refused too: an
     * unresolvable target is not a safe one, and the caller cannot prove it is the testing
     * database.
     */
    public static function shouldBlock($command, $database): bool
    {
        if (! self::isDestructive($command)) {
            return false;
        }

        return (string) $database !== self::TESTING_DATABASE;
    }

    /**
     * The operator-facing refusal, naming the command, what it resolved to, and how to proceed.
     */
    public static function blockMessage($command, $database, $connectionLabel = null): string
    {
        $resolved = ($database === null || (string) $database === '') ? '[unresolved]' : (string) $database;

        return sprintf(
            "%s: Refusing %s because %sresolves to '%s'; expected '%s'. "
            ."These commands drop or reset schema and may only target the testing database. "
            ."Run with --env=testing against %s, or use forward `migrate` if you meant to apply pending migrations.\n",
            self::REASON_CODE,
            (string) $command,
            $connectionLabel === null ? 'DB_DATABASE ' : "database connection '".$connectionLabel."' ",
            $resolved,
            self::TESTING_DATABASE,
            self::TESTING_DATABASE
        );
    }
}
