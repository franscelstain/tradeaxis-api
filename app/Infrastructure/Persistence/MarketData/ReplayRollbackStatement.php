<?php
namespace App\Infrastructure\Persistence\MarketData;
/** A prepared statement never outlives the rollback-only execution lease that issued it. */
final class ReplayRollbackStatement extends \PDOStatement
{
    private $authority;
    protected function __construct(ReplayRollbackPdo $authority) { $this->authority=$authority; }
    public function execute($parameters = null)
    {
        $this->authority->assertExecution();
        return $parameters===null ? parent::execute() : parent::execute($parameters);
    }
}
