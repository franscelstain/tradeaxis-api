<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Internal execution credentials. Run models and public fencing numbers contain no secret. */
final class RunWriterContext
{
    private static $credentials;
    private static $observers;

    private static function store(): \SplObjectStorage
    {
        if (! self::$credentials) self::$credentials = new \SplObjectStorage();
        return self::$credentials;
    }

    /** Retain a secret issued by the repository's private acquisition boundary. No database grant. */
    public static function remember(int $runId, string $execution, string $secret, array $scope = []): void
    {
        $connection = DB::connection();
        $entries = self::store()->contains($connection) ? self::store()[$connection] : [];
        $entries[$runId] = ['execution' => $execution, 'secret' => $secret, 'scope' => $scope];
        self::store()[$connection] = $entries;
    }

    public static function forget(int $runId): void
    {
        $connection = DB::connection();
        if (! self::store()->contains($connection)) {
            return;
        }
        $entries = self::store()[$connection];
        unset($entries[$runId]);
        self::store()[$connection] = $entries;
    }

    public static function authenticate(EodRun $run, $fence): void
    {
        $connection = DB::connection();
        $entries = self::store()->contains($connection) ? self::store()[$connection] : [];
        $credential = $entries[(int) $run->run_id] ?? null;
        if (! $credential || ! $fence || empty($fence->credential_hash)
            || ($credential['scope'] ?? []) !== ['date' => (string) $run->trade_date_requested,
                'source' => (string) $run->source, 'mode' => (string) $run->request_mode]
            || ! hash_equals((string) $fence->execution_id, $credential['execution'])
            || ! hash_equals((string) $fence->credential_hash, hash('sha256', $credential['secret']))) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: caller lacks this execution credential; run identity is not authority.');
        }
    }

    private const OBSERVATIONS = [
        'freshness' => ['FRESHNESS_MEASURED', 'FRESHNESS_ALERT_OPENED', 'FRESHNESS_ALERT_ESCALATED',
            'FRESHNESS_ALERT_RECOVERED', 'SOURCE_CAPABILITY_EVALUATION_OPENED'],
        'incident' => ['INCIDENT_RESPONSE_RECORDED'],
        'impact' => ['PUBLICATION_REPROCESS_OBSERVED', 'IMPACT_PUBLICATION_REPROCESS_COMPLETED'],
        'session' => ['ASSESSMENT', 'ALERT_OPENED', 'ALERT_ESCALATED', 'ALERT_RECOVERED'],
        'session_incident' => ['INCIDENT_RESPONSE'],
        'scheduler' => ['SCHEDULER_OUTCOME'],
        'projection' => ['PROJECTION_RECONCILED'],
        'detector' => ['OBSERVED_CONDITION', 'DIAGNOSTIC', 'DIAGNOSTIC_WARNING'],
        'conflict' => ['RUN_OWNER_CONFLICT', 'RUN_OWNER_AMBIGUOUS', 'STALE_OWNER_TAKEOVER_BLOCKED', 'STALE_ACTIVE_RUN_CANCELLED'],
    ];

    /** Explicit trusted domain-entry acquisition, distinct from publication ownership. */
    public static function observe(string $role, EodRun $target, callable $work)
    {
        $connection = DB::connection();
        $prior = self::$observers && self::$observers->contains($connection) ? self::$observers[$connection] : null;
        // Acquisition owns the date-scoped conflict lease before locking any predecessor.
        if ($prior && $prior['role'] === $role && $role === 'conflict'
            && $prior['date'] === (string) $target->trade_date_requested && $prior['target_run_id'] === null) return $work();
        return self::observeDate($role, (string) $target->trade_date_requested, (int) $target->run_id, $work);
    }

    public static function observeDate(string $role, string $date, ?int $targetRunId, callable $work)
    {
        if (! isset(self::OBSERVATIONS[$role]) || ($targetRunId === null
            && ! in_array($role, ['session', 'session_incident', 'conflict', 'projection'], true))) {
            throw new \RuntimeException('OBSERVER_AUTHORITY_INVALID: registered role and explicit historical scope required.');
        }
        \App\Domain\MarketData\MarketDataScope::fromConfig()->assertRequestedDate($date);
        return DB::transaction(function () use ($role, $date, $targetRunId, $work) {
            $key = $role.'|'.$date;
            DB::table('eod_observer_fences')->insertOrIgnore(['scope_key' => $key, 'token' => 0]);
            $prior = DB::table('eod_observer_fences')->where('scope_key', $key)->lockForUpdate()->first();
            $secret = bin2hex(random_bytes(32)); $actor = bin2hex(random_bytes(16));
            $token = (int) $prior->token + 1;
            DB::table('eod_observer_fences')->where('scope_key', $key)->update([
                'token' => $token, 'actor_id' => $actor, 'credential_hash' => hash('sha256', $secret),
                'target_run_id' => $targetRunId, 'acquired_at' => Carbon::now(), 'released_at' => null,
            ]);
            if (! self::$observers) self::$observers = new \SplObjectStorage();
            $connection = DB::connection();
            $previous = self::$observers->contains($connection) ? self::$observers[$connection] : null;
            self::$observers[$connection] = compact('key', 'role', 'actor', 'secret', 'token') + [
                'target_run_id' => $targetRunId, 'date' => $date,
            ];
            try {
                $result = $work();
                DB::table('eod_observer_fences')->where('scope_key', $key)->where('token', $token)
                    ->where('actor_id', $actor)->update(['released_at' => Carbon::now()]);
                return $result;
            } finally {
                if ($previous === null) self::$observers->detach($connection);
                else self::$observers[$connection] = $previous;
            }
        });
    }

    public static function observation(EodRun $target, string $type): array
    {
        return self::dateObservation((string) $target->trade_date_requested, (int) $target->run_id, $type);
    }

    public static function dateObservation(string $date, ?int $targetRunId, string $type): array
    {
        $connection = DB::connection();
        $context = self::$observers && self::$observers->contains($connection) ? self::$observers[$connection] : null;
        if (DB::transactionLevel() < 1 || ! $context
            || ($context['target_run_id'] !== $targetRunId && ! ($context['target_run_id'] === null
                && in_array($context['role'], ['session','session_incident','conflict'], true)))
            || $context['date'] !== $date
            || ! in_array($type, self::OBSERVATIONS[$context['role']], true)) {
            throw new \RuntimeException('OBSERVER_AUTHORITY_INVALID: explicit scoped observer acquisition required.');
        }
        $fence = DB::table('eod_observer_fences')->where('scope_key', $context['key'])->lockForUpdate()->first();
        if (! $fence || $fence->released_at !== null || (int) $fence->token !== $context['token']
            || (string) $fence->actor_id !== $context['actor']
            || ($fence->target_run_id === null ? null : (int) $fence->target_run_id) !== $context['target_run_id']
            || ! hash_equals((string) $fence->credential_hash, hash('sha256', $context['secret']))) {
            throw new \RuntimeException('OBSERVER_AUTHORITY_INVALID: released or superseded observation credential.');
        }
        $persisted = $targetRunId === null ? null : DB::table('eod_runs')->where('run_id', $targetRunId)->lockForUpdate()->first();
        if ($targetRunId !== null && (! $persisted || (string) $persisted->trade_date_requested !== $context['date'])) {
            throw new \RuntimeException('OBSERVER_AUTHORITY_INVALID: historical target scope mismatch.');
        }
        return ['kind' => 'OBSERVATION_ONLY', 'actor_id' => $context['actor'], 'role' => $context['role'],
            'scope_key' => $context['key'], 'fencing_token' => $context['token'],
            'target_run_id' => $targetRunId, 'requested_date' => $context['date']];
    }
}
