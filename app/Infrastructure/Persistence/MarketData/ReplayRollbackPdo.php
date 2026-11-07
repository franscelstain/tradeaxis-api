<?php
namespace App\Infrastructure\Persistence\MarketData;

/** Rollback-only replay transport. Internal worker boundary, not an arbitrary-PHP sandbox. */
final class ReplayRollbackPdo extends \PDO
{
    private $inner;
    private $active = true;
    public function __construct(\PDO $inner)
    {
        if ($this->inner) throw new \RuntimeException('REPLAY_ISOLATION_BOUNDARY_LOST: transport identity is immutable.');
        $this->inner=$inner;
        $inner->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [ReplayRollbackStatement::class, [$this]]);
    }
    public function assertExecution(): void
    {
        if (!$this->active || !$this->inner->inTransaction()) {
            throw new \RuntimeException('REPLAY_ISOLATION_BOUNDARY_LOST: statement/transport lease is no longer active.');
        }
    }
    public function revoke(): void { $this->active=false; }
    private function assertSql($sql): void
    {
        $this->assertExecution();
        // No transaction control, DDL/implicit commit, stored procedure or multi-statement escape.
        if (preg_match('/;|\/\*|--|#/', $sql)
            || ! preg_match('/^\s*(select|insert|update|delete|replace|show|savepoint|release\s+savepoint|rollback\s+to\s+savepoint|pragma\s+(table_info|table_xinfo|index_list|index_info|foreign_key_list)\s*\()/i', $sql)) {
            throw new \RuntimeException('REPLAY_ISOLATION_SQL_FORBIDDEN: replay permits transactional projection DML and schema reads only.');
        }
    }
    public function commit() { throw new \RuntimeException('REPLAY_COMMIT_FORBIDDEN: replay root is rollback-only.'); }
    public function rollBack() { throw new \RuntimeException('REPLAY_BOUNDARY_CONTROL_FORBIDDEN: isolation scope owns root rollback.'); }
    public function beginTransaction() { if (!$this->active) throw new \RuntimeException('REPLAY_ISOLATION_BOUNDARY_LOST: transport is revoked.'); return $this->inner->beginTransaction(); }
    public function inTransaction() { return $this->inner->inTransaction(); }
    public function exec($sql) { $this->assertSql($sql); return $this->inner->exec($sql); }
    public function prepare($sql, $options = []) { $this->assertSql($sql); return $this->inner->prepare($sql, $options); }
    public function query($sql, $mode = null, ...$args) { $this->assertSql($sql); return $mode===null?$this->inner->query($sql):$this->inner->query($sql,$mode,...$args); }
    public function getAttribute($attribute) { return $this->inner->getAttribute($attribute); }
    public function setAttribute($attribute,$value)
    {
        if ($attribute===\PDO::ATTR_AUTOCOMMIT || $attribute===\PDO::ATTR_STATEMENT_CLASS) throw new \RuntimeException('REPLAY_COMMIT_FORBIDDEN: autocommit cannot change.');
        return $this->inner->setAttribute($attribute,$value);
    }
    public function lastInsertId($name = null) { return $this->inner->lastInsertId($name); }
    public function quote($value,$type = \PDO::PARAM_STR) { return $this->inner->quote($value,$type); }
    public function errorCode() { return $this->inner->errorCode(); }
    public function errorInfo() { return $this->inner->errorInfo(); }
}
